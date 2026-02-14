<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\StopForumSpamProvider;

/**
 * Inject a mock Guzzle client into a provider via reflection.
 *
 * AbstractProvider stores the client in a private $httpClient property.
 * We set it directly to avoid real HTTP calls.
 */
function injectMockClient(object $provider, MockHandler $mockHandler): void
{
    $client = new Client(['handler' => HandlerStack::create($mockHandler)]);
    $reflection = new ReflectionClass($provider);

    // The httpClient property is on AbstractProvider
    $parentReflection = $reflection->getParentClass();
    $property = $parentReflection->getProperty('httpClient');
    $property->setAccessible(true);
    $property->setValue($provider, $client);
}

describe('StopForumSpamProvider', function () {
    describe('getName()', function () {
        it('returns stop_forum_spam', function () {
            $provider = new StopForumSpamProvider;

            expect($provider->getName())->toBe('stop_forum_spam');
        });
    });

    describe('isEnabled()', function () {
        it('is disabled by default', function () {
            $provider = new StopForumSpamProvider;

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is enabled when config says so', function () {
            $provider = new StopForumSpamProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeTrue();
        });
    });

    describe('checkUser() - Response Parsing', function () {
        it('returns Allowed when no fields appear in SFS response', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 0],
                    'email' => ['appears' => 0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            $provider = new StopForumSpamProvider(['enabled' => true]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('cleanuser', 'clean@example.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('not listed');
        });

        it('returns Denied when confidence exceeds deny threshold', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 1, 'confidence' => 95.5],
                    'email' => ['appears' => 0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            $provider = new StopForumSpamProvider([
                'enabled' => true,
                'deny_threshold' => 90.0,
                'confidence_threshold' => 65.0,
            ]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('spammer', 'spam@test.com', '5.6.7.8');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->confidence)->toBe(95.5)
                ->and($result->reason)->toContain('ip')
                ->and($result->reason)->toContain('95.5');
        });

        it('returns Moderated when confidence is between thresholds', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 0],
                    'email' => ['appears' => 1, 'confidence' => 75.0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            $provider = new StopForumSpamProvider([
                'enabled' => true,
                'deny_threshold' => 90.0,
                'confidence_threshold' => 65.0,
            ]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'iffy@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Moderated)
                ->and($result->confidence)->toBe(75.0)
                ->and($result->reason)->toContain('email');
        });

        it('returns Allowed when confidence is below both thresholds', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 1, 'confidence' => 30.0],
                    'email' => ['appears' => 0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            $provider = new StopForumSpamProvider([
                'enabled' => true,
                'deny_threshold' => 90.0,
                'confidence_threshold' => 65.0,
            ]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'test@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->confidence)->toBe(30.0);
        });

        it('uses the maximum confidence across all fields', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 1, 'confidence' => 20.0],
                    'email' => ['appears' => 1, 'confidence' => 95.0],
                    'username' => ['appears' => 1, 'confidence' => 50.0],
                ])),
            ]);

            $provider = new StopForumSpamProvider([
                'enabled' => true,
                'deny_threshold' => 90.0,
                'confidence_threshold' => 65.0,
            ]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'spam@test.com', '1.2.3.4');

            // email has the highest confidence (95.0), so it should be Denied
            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->confidence)->toBe(95.0)
                ->and($result->reason)->toContain('email');
        });

        it('uses default thresholds when not configured', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 1, 'confidence' => 91.0],
                    'email' => ['appears' => 0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            // Default: deny_threshold=90, confidence_threshold=65
            $provider = new StopForumSpamProvider(['enabled' => true]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'test@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied);
        });
    });

    describe('checkUser() - Error Handling (safeExecute)', function () {
        it('returns Allowed when API call fails', function () {
            $mockHandler = new MockHandler([
                new GuzzleHttp\Exception\ConnectException(
                    'Connection timeout',
                    new GuzzleHttp\Psr7\Request('GET', 'https://api.stopforumspam.org/api'),
                ),
            ]);

            $provider = new StopForumSpamProvider(['enabled' => true]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'test@test.com', '1.2.3.4');

            // safeExecute catches exceptions and returns Allowed
            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Provider error');
        });
    });

    describe('Response Time Tracking', function () {
        it('includes response time', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], json_encode([
                    'success' => 1,
                    'ip' => ['appears' => 0],
                    'email' => ['appears' => 0],
                    'username' => ['appears' => 0],
                ])),
            ]);

            $provider = new StopForumSpamProvider(['enabled' => true]);
            injectMockClient($provider, $mockHandler);

            $result = $provider->checkUser('user', 'test@test.com', '1.2.3.4');

            expect($result->responseTimeMs)->toBeGreaterThanOrEqual(0.0);
        });
    });
});
