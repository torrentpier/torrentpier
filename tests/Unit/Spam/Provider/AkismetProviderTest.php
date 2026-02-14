<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\AkismetProvider;

/**
 * Inject a mock Guzzle client into a provider via reflection on AbstractProvider.
 */
function injectAkismetMockClient(object $provider, MockHandler $mockHandler): void
{
    $client = new Client(['handler' => HandlerStack::create($mockHandler)]);
    $reflection = new ReflectionClass($provider);

    // Walk up to AbstractProvider which holds the httpClient property
    $parent = $reflection->getParentClass();
    $property = $parent->getProperty('httpClient');
    $property->setAccessible(true);
    $property->setValue($provider, $client);
}

// config() is needed by AkismetProvider::checkContent for config()->get('app.url')
beforeAll(function () {
    if (!function_exists('config')) {
        function config(): object
        {
            return new class {
                public function get($key, $default = null)
                {
                    return match ($key) {
                        'app.url' => 'https://test-forum.example.com',
                        'spam.logging.enabled' => false,
                        default => $default,
                    };
                }
            };
        }
    }
});

describe('AkismetProvider', function () {
    describe('getName()', function () {
        it('returns akismet', function () {
            $provider = new AkismetProvider;

            expect($provider->getName())->toBe('akismet');
        });
    });

    describe('isEnabled()', function () {
        it('is disabled by default', function () {
            $provider = new AkismetProvider;

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is disabled when enabled=true but no api_key', function () {
            $provider = new AkismetProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is disabled when enabled=true and api_key is empty', function () {
            $provider = new AkismetProvider(['enabled' => true, 'api_key' => '']);

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is enabled when both enabled=true and api_key is set', function () {
            $provider = new AkismetProvider(['enabled' => true, 'api_key' => 'test123']);

            expect($provider->isEnabled())->toBeTrue();
        });

        it('is disabled when enabled=false even with api_key', function () {
            $provider = new AkismetProvider(['enabled' => false, 'api_key' => 'test123']);

            expect($provider->isEnabled())->toBeFalse();
        });
    });

    describe('checkContent() - Spam Detection', function () {
        it('returns Moderated when Akismet says true (spam)', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], 'true'),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(42, 'Buy cheap pills online!', ['ip' => '1.2.3.4']);

            expect($result->decision)->toBe(Decision::Moderated) // NOT Denied
                ->and($result->reason)->toContain('Akismet')
                ->and($result->reason)->toContain('spam')
                ->and($result->confidence)->toBe(80.0)
                ->and($result->providerName)->toBe('akismet');
        });

        it('returns Allowed when Akismet says false (not spam)', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], 'false'),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(42, 'This is a normal forum post.', ['ip' => '1.2.3.4']);

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('not spam')
                ->and($result->confidence)->toBe(0.0);
        });
    });

    describe('checkContent() - Error Handling', function () {
        it('returns Allowed when Akismet API fails (safeExecute)', function () {
            $mockHandler = new MockHandler([
                new GuzzleHttp\Exception\ConnectException(
                    'Connection refused',
                    new GuzzleHttp\Psr7\Request('POST', 'https://test.rest.akismet.com/1.1/comment-check'),
                ),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(42, 'some message', ['ip' => '1.2.3.4']);

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Provider error');
        });
    });

    describe('checkContent() - Extra Parameters', function () {
        it('works without extra params', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], 'false'),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(1, 'test message');

            expect($result->decision)->toBe(Decision::Allowed);
        });

        it('works with type in extra', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], 'false'),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(1, 'test', ['ip' => '10.0.0.1', 'type' => 'blog-post']);

            expect($result->decision)->toBe(Decision::Allowed);
        });
    });

    describe('Response Timing', function () {
        it('includes response time', function () {
            $mockHandler = new MockHandler([
                new Response(200, [], 'false'),
            ]);

            $provider = new AkismetProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);
            injectAkismetMockClient($provider, $mockHandler);

            $result = $provider->checkContent(1, 'test');

            expect($result->responseTimeMs)->toBeGreaterThanOrEqual(0.0);
        });
    });
});
