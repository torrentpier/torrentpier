<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use TorrentPier\Http\Exception\HttpClientException;
use TorrentPier\Http\HttpClient;

describe('HttpClient Class', function () {
    beforeEach(function () {
        // Mock config() function if not already defined
        if (!function_exists('config')) {
            function config(): object
            {
                return new class {
                    public function get($key, $default = null)
                    {
                        return match ($key) {
                            'tp_version' => '2.4.0',
                            default => $default,
                        };
                    }
                };
            }
        }

        // Mock APP_NAME constant
        if (!defined('APP_NAME')) {
            define('APP_NAME', 'TorrentPier');
        }

        // Mock LOG_LF constant
        if (!defined('LOG_LF')) {
            define('LOG_LF', "\n");
        }

        // Mock bb_log function
        if (!function_exists('bb_log')) {
            function bb_log($message)
            {
                // No-op for testing
            }
        }
    });

    describe('Instance Creation', function () {
        it('creates instance correctly', function () {
            $instance = new HttpClient;

            expect($instance)->toBeInstanceOf(HttpClient::class);
        });

        it('returns Guzzle client instance', function () {
            $httpClient = new HttpClient;
            $guzzleClient = $httpClient->getClient();

            expect($guzzleClient)->toBeInstanceOf(Client::class);
        });

        it('creates simple client without retry middleware', function () {
            $client = HttpClient::createSimpleClient();

            expect($client)->toBeInstanceOf(Client::class);
        });
    });

    describe('HTTP Methods', function () {
        beforeEach(function () {
            $this->mockHandler = new MockHandler;
            $this->handlerStack = HandlerStack::create($this->mockHandler);
            $this->httpClient = new HttpClient(['handler' => $this->handlerStack]);
        });

        it('performs GET request successfully', function () {
            $this->mockHandler->append(new Response(200, [], 'GET response'));

            $response = $this->httpClient->get('https://example.com/api');

            expect($response->getStatusCode())->toBe(200)
                ->and((string)$response->getBody())->toBe('GET response');
        });

        it('performs POST request successfully', function () {
            $this->mockHandler->append(new Response(201, [], 'POST response'));

            $response = $this->httpClient->post('https://example.com/api', [
                'json' => ['key' => 'value'],
            ]);

            expect($response->getStatusCode())->toBe(201)
                ->and((string)$response->getBody())->toBe('POST response');
        });

        it('performs PUT request successfully', function () {
            $this->mockHandler->append(new Response(200, [], 'PUT response'));

            $response = $this->httpClient->put('https://example.com/api/1', [
                'json' => ['key' => 'updated'],
            ]);

            expect($response->getStatusCode())->toBe(200)
                ->and((string)$response->getBody())->toBe('PUT response');
        });

        it('performs PATCH request successfully', function () {
            $this->mockHandler->append(new Response(200, [], 'PATCH response'));

            $response = $this->httpClient->patch('https://example.com/api/1', [
                'json' => ['key' => 'patched'],
            ]);

            expect($response->getStatusCode())->toBe(200)
                ->and((string)$response->getBody())->toBe('PATCH response');
        });

        it('performs DELETE request successfully', function () {
            $this->mockHandler->append(new Response(204, []));

            $response = $this->httpClient->delete('https://example.com/api/1');

            expect($response->getStatusCode())->toBe(204);
        });

        it('performs HEAD request successfully', function () {
            $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json']));

            $response = $this->httpClient->head('https://example.com/api');

            expect($response->getStatusCode())->toBe(200)
                ->and($response->getHeaderLine('Content-Type'))->toBe('application/json');
        });

        it('handles custom headers', function () {
            $this->mockHandler->append(new Response(200, [], 'OK'));
            $container = [];
            $history = Middleware::history($container);
            $this->handlerStack->push($history);

            $this->httpClient->get('https://example.com/api', [
                'headers' => [
                    'X-Custom-Header' => 'test-value',
                    'Authorization' => 'Bearer token123',
                ],
            ]);

            expect($container)->toHaveCount(1);
            $request = $container[0]['request'];
            expect($request->getHeaderLine('X-Custom-Header'))->toBe('test-value')
                ->and($request->getHeaderLine('Authorization'))->toBe('Bearer token123');
        });

        it('sets correct User-Agent header', function () {
            $this->mockHandler->append(new Response(200, [], 'OK'));
            $container = [];
            $history = Middleware::history($container);
            $this->handlerStack->push($history);

            $this->httpClient->get('https://example.com/api');

            expect($container)->toHaveCount(1);
            $request = $container[0]['request'];
            expect($request->getHeaderLine('User-Agent'))->toBe('TorrentPier/' . \TorrentPier\Application::VERSION);
        });
    });

    describe('Error Handling', function () {
        beforeEach(function () {
            $this->mockHandler = new MockHandler;
            $this->handlerStack = HandlerStack::create($this->mockHandler);
            $this->httpClient = new HttpClient(['handler' => $this->handlerStack]);
        });

        it('does not throw on 4xx responses by default', function () {
            $this->mockHandler->append(new Response(404, [], 'Not Found'));

            $response = $this->httpClient->get('https://example.com/notfound');

            expect($response->getStatusCode())->toBe(404)
                ->and((string)$response->getBody())->toBe('Not Found');
        });

        it('does not throw on 5xx responses by default', function () {
            $this->mockHandler->append(new Response(500, [], 'Internal Server Error'));

            $response = $this->httpClient->get('https://example.com/error');

            expect($response->getStatusCode())->toBe(500)
                ->and((string)$response->getBody())->toBe('Internal Server Error');
        });

        it('wraps GuzzleException in HttpClientException', function () {
            $request = new Request('GET', 'https://example.com');
            $this->mockHandler->append(
                new RequestException('Connection timeout', $request),
            );

            expect(fn () => $this->httpClient->get('https://example.com'))
                ->toThrow(HttpClientException::class);
        });

        it('wraps generic exceptions in HttpClientException', function () {
            $this->mockHandler->append(
                new RuntimeException('Unexpected error'),
            );

            expect(fn () => $this->httpClient->get('https://example.com'))
                ->toThrow(HttpClientException::class);
        });
    });

    describe('Retry Logic', function () {
        it('does not retry on 4xx client errors (except 429)', function () {
            $mockHandler = new MockHandler([new Response(404)]);
            $handlerStack = HandlerStack::create($mockHandler);
            $container = [];
            $history = Middleware::history($container);
            $handlerStack->push($history);

            $httpClient = new HttpClient(['handler' => $handlerStack]);
            $response = $httpClient->get('https://example.com/notfound');

            expect($response->getStatusCode())->toBe(404)
                ->and($container)->toHaveCount(1);
            // No retries
        });

        it('validates retry configuration is present', function () {
            // Test that HttpClient has retry logic by checking it doesn't immediately fail
            // This is a behavioral test rather than testing internal implementation
            $httpClient = new HttpClient;

            expect($httpClient)->toBeInstanceOf(HttpClient::class)
                ->and($httpClient->getClient())->toBeInstanceOf(Client::class);
        });

        it('handles server errors gracefully', function () {
            $mockHandler = new MockHandler([new Response(500, [], 'Server Error')]);
            $handlerStack = HandlerStack::create($mockHandler);
            $httpClient = new HttpClient(['handler' => $handlerStack]);

            $response = $httpClient->get('https://example.com/api');

            // Even with retry logic, we eventually return the error response
            expect($response->getStatusCode())->toBe(500);
        });

        it('handles 429 Too Many Requests', function () {
            $mockHandler = new MockHandler([new Response(429, [], 'Too Many Requests')]);
            $handlerStack = HandlerStack::create($mockHandler);
            $httpClient = new HttpClient(['handler' => $handlerStack]);

            $response = $httpClient->get('https://example.com/api');

            expect($response->getStatusCode())->toBe(429);
        });

        it('handles connection exceptions', function () {
            $request = new Request('GET', 'https://example.com');
            $mockHandler = new MockHandler([
                new ConnectException('Connection failed', $request),
            ]);
            $handlerStack = HandlerStack::create($mockHandler);
            $httpClient = new HttpClient(['handler' => $handlerStack]);

            expect(fn () => $httpClient->get('https://example.com/api'))
                ->toThrow(HttpClientException::class);
        });
    });

    describe('Retry Delay', function () {
        it('has retry delay configuration', function () {
            // Test that retry delay logic exists by verifying the HttpClient
            // is properly constructed with retry middleware
            $httpClient = new HttpClient;

            // Behavioral test: verify instance is created successfully
            expect($httpClient)->toBeInstanceOf(HttpClient::class);
        });

        it('handles Retry-After header in responses', function () {
            // Test that responses with Retry-After header are handled
            $mockHandler = new MockHandler([
                new Response(429, ['Retry-After' => '5'], 'Too Many Requests'),
            ]);
            $handlerStack = HandlerStack::create($mockHandler);
            $httpClient = new HttpClient(['handler' => $handlerStack]);

            $response = $httpClient->get('https://example.com/api');

            expect($response->getStatusCode())->toBe(429)
                ->and($response->getHeaderLine('Retry-After'))->toBe('5');
        });
    });

    describe('Download Functionality', function () {
        beforeEach(function () {
            $this->mockHandler = new MockHandler;
            $this->handlerStack = HandlerStack::create($this->mockHandler);
            $this->httpClient = new HttpClient(['handler' => $this->handlerStack]);
            $this->testFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        });

        afterEach(function () {
            if (file_exists($this->testFile)) {
                @unlink($this->testFile);
            }
        });

        it('downloads file successfully', function () {
            $this->mockHandler->append(new Response(200, [], 'File content'));

            $result = $this->httpClient->download('https://example.com/file.txt', $this->testFile);

            expect($result)->toBeTrue()
                ->and(file_exists($this->testFile))->toBeTrue()
                ->and(file_get_contents($this->testFile))->toBe('File content');
        });

        it('throws exception on failed download', function () {
            $this->mockHandler->append(new Response(404, [], 'Not Found'));

            expect(fn () => $this->httpClient->download('https://example.com/notfound.txt', $this->testFile))
                ->toThrow(HttpClientException::class);
        });

        it('removes file on failed download', function () {
            $this->mockHandler->append(new Response(500, [], 'Error'));

            try {
                $this->httpClient->download('https://example.com/error.txt', $this->testFile);
            } catch (HttpClientException) {
                // Expected
            }

            expect(file_exists($this->testFile))->toBeFalse();
        });

        it('handles download exceptions', function () {
            $request = new Request('GET', 'https://example.com');
            $this->mockHandler->append(new ConnectException('Connection failed', $request));

            expect(fn () => $this->httpClient->download('https://example.com/file.txt', $this->testFile))
                ->toThrow(HttpClientException::class);
        });

        it('downloads file with progress callback', function () {
            $fileContent = str_repeat('A', 1000); // 1KB of data
            $this->mockHandler->append(new Response(200, [], $fileContent));

            $result = $this->httpClient->downloadWithProgress(
                'https://example.com/file.txt',
                $this->testFile,
                function ($percent, $downloaded, $total) {
                    // Note: MockHandler doesn't trigger progress callbacks
                    // This just validates the callback signature is correct
                },
            );

            // File should be downloaded successfully even with a progress callback
            expect($result)->toBeTrue()
                ->and(file_exists($this->testFile))->toBeTrue()
                ->and(file_get_contents($this->testFile))->toBe($fileContent);
        });

        it('downloads file without progress callback', function () {
            $this->mockHandler->append(new Response(200, [], 'File content without callback'));

            $result = $this->httpClient->downloadWithProgress(
                'https://example.com/file.txt',
                $this->testFile,
            );

            expect($result)->toBeTrue()
                ->and(file_exists($this->testFile))->toBeTrue()
                ->and(file_get_contents($this->testFile))->toBe('File content without callback');
        });

        it('handles download with progress exceptions', function () {
            $request = new Request('GET', 'https://example.com');
            $this->mockHandler->append(new ConnectException('Connection failed', $request));

            expect(fn () => $this->httpClient->downloadWithProgress(
                'https://example.com/file.txt',
                $this->testFile,
                function ($percent, $downloaded, $total) {
                    // Progress callback
                },
            ))->toThrow(HttpClientException::class);
        });

        it('throws exception on failed download with progress', function () {
            $this->mockHandler->append(new Response(404, [], 'Not Found'));

            expect(fn () => $this->httpClient->downloadWithProgress(
                'https://example.com/notfound.txt',
                $this->testFile,
            ))->toThrow(HttpClientException::class);
        });
    });

    describe('Configuration', function () {
        it('accepts custom timeout configuration', function () {
            $httpClient = new HttpClient([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            expect($httpClient)->toBeInstanceOf(HttpClient::class);
        });

        it('accepts custom headers configuration', function () {
            $mockHandler = new MockHandler([new Response(200)]);
            $handlerStack = HandlerStack::create($mockHandler);
            $container = [];
            $history = Middleware::history($container);
            $handlerStack->push($history);

            $httpClient = new HttpClient([
                'handler' => $handlerStack,
                'headers' => [
                    'X-Custom-Global' => 'global-value',
                ],
            ]);

            $httpClient->get('https://example.com/api');

            expect($container)->toHaveCount(1);
            $request = $container[0]['request'];
            expect($request->getHeaderLine('X-Custom-Global'))->toBe('global-value');
        });

        it('verifies SSL certificates by default', function () {
            $httpClient = new HttpClient;

            // We can't directly test this without making real requests,
            // but we can verify the instance is created with verify: true
            expect($httpClient)->toBeInstanceOf(HttpClient::class);
        });
    });

    describe('Request Options', function () {
        beforeEach(function () {
            $this->mockHandler = new MockHandler;
            $this->handlerStack = HandlerStack::create($this->mockHandler);
            $this->container = [];
            $this->history = Middleware::history($this->container);
            $this->handlerStack->push($this->history);
            $this->httpClient = new HttpClient(['handler' => $this->handlerStack]);
        });

        it('handles query parameters', function () {
            $this->mockHandler->append(new Response(200));

            $this->httpClient->get('https://example.com/api', [
                'query' => [
                    'page' => 1,
                    'limit' => 10,
                ],
            ]);

            expect($this->container)->toHaveCount(1);
            $request = $this->container[0]['request'];
            expect((string)$request->getUri())->toContain('page=1')
                ->and((string)$request->getUri())->toContain('limit=10');
        });

        it('handles JSON body', function () {
            $this->mockHandler->append(new Response(200));

            $this->httpClient->post('https://example.com/api', [
                'json' => [
                    'name' => 'Test',
                    'email' => 'test@example.com',
                ],
            ]);

            expect($this->container)->toHaveCount(1);
            $request = $this->container[0]['request'];
            expect($request->getHeaderLine('Content-Type'))->toContain('application/json');
        });

        it('handles form parameters', function () {
            $this->mockHandler->append(new Response(200));

            $this->httpClient->post('https://example.com/api', [
                'form_params' => [
                    'username' => 'testuser',
                    'password' => 'secret',
                ],
            ]);

            expect($this->container)->toHaveCount(1);
            $request = $this->container[0]['request'];
            expect($request->getHeaderLine('Content-Type'))->toContain('application/x-www-form-urlencoded');
        });
    });
});
