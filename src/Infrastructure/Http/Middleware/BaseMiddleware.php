<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->before($request);
        $response = $handler->handle($request);
        return $this->after($request, $response);
    }

    protected function before(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request;
    }

    protected function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
