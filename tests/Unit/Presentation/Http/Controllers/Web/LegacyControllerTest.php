<?php

use GuzzleHttp\Psr7\ServerRequest;
use TorrentPier\Config;
use TorrentPier\Presentation\Http\Controllers\Web\LegacyController;

describe('LegacyController', function () {
    beforeEach(function () {
        // Create a mock config for testing that doesn't require actual files
        $mockConfig = Mockery::mock(Config::class);
        $mockConfig->shouldReceive('all')->andReturn([]);
        $mockConfig->shouldReceive('get')->andReturn('/fake/path/');

        // Create controller with mock config
        $this->controller = new LegacyController($mockConfig);
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('basic functionality', function () {
        it('can be instantiated', function () {
            expect($this->controller)->toBeInstanceOf(LegacyController::class);
        });

        it('implements proper method signature', function () {
            $reflection = new ReflectionClass($this->controller);
            $method = $reflection->getMethod('handle');

            expect($method->getParameters())->toHaveCount(1);
            expect($method->getParameters()[0]->getType()->getName())->toBe('Psr\Http\Message\ServerRequestInterface');
        });

        it('returns PSR-7 response interface', function () {
            $request = new ServerRequest('GET', 'http://example.com/nonexistent');

            $response = $this->controller->handle($request);
            expect($response)->toBeInstanceOf(\Psr\Http\Message\ResponseInterface::class);
        });

        it('returns 404 for non-existent files', function () {
            $request = new ServerRequest('GET', 'http://example.com/nonexistent');

            $response = $this->controller->handle($request);
            expect($response->getStatusCode())->toBe(404);
        });
    });

    describe('security', function () {
        it('prevents directory traversal', function () {
            $request = new ServerRequest('GET', 'http://example.com/../../../etc/passwd');

            $response = $this->controller->handle($request);
            expect($response->getStatusCode())->toBe(404);
        });
    });
});
