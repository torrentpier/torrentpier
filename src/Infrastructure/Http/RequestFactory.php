<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\Http;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class RequestFactory
{
    public static function fromGlobals(): ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }

    public static function create(
        string $method = 'GET',
        string $uri = '/',
        array  $headers = [],
               $body = null,
        string $protocolVersion = '1.1'
    ): ServerRequestInterface
    {
        $request = new ServerRequest($method, $uri, $headers, $body, $protocolVersion);
        return $request;
    }
}
