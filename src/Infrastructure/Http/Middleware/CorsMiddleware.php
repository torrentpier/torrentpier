<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware extends BaseMiddleware
{
    private array $allowedOrigins;
    private array $allowedHeaders;
    private array $allowedMethods;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
    )
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowedMethods = $allowedMethods;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createPreflightResponse();
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response, $request);
    }

    private function createPreflightResponse(): ResponseInterface
    {
        $response = new \GuzzleHttp\Psr7\Response(200);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(ResponseInterface $response, ?ServerRequestInterface $request = null): ResponseInterface
    {
        $origin = $request ? $request->getHeaderLine('Origin') : '';

        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin ?: '*');
        }

        $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response = $response->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response = $response->withHeader('Access-Control-Max-Age', '86400');

        return $response;
    }
}
