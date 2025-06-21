<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\Http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory
{
    public static function create(
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $protocolVersion = '1.1'
    ): ResponseInterface
    {
        $response = new Response($status, $headers, $body);
        return $response->withProtocolVersion($protocolVersion);
    }

    public static function json(
        array $data,
        int   $status = 200,
        array $headers = [],
        int   $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ): ResponseInterface
    {
        $json = json_encode($data, $encodingOptions);
        $headers['content-type'] = 'application/json; charset=utf-8';
        return new Response($status, $headers, $json);
    }

    public static function html(
        string $html,
        int    $status = 200,
        array  $headers = []
    ): ResponseInterface
    {
        $headers['content-type'] = 'text/html; charset=utf-8';
        return new Response($status, $headers, $html);
    }

    public static function redirect(
        string $uri,
        int    $status = 302,
        array  $headers = []
    ): ResponseInterface
    {
        $headers['location'] = $uri;
        return new Response($status, $headers);
    }
}
