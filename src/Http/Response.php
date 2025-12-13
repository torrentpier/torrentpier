<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HTTP Response Factory class for creating various HTTP responses
 *
 * Usage:
 *   Response::html('<h1>Hello</h1>')->send();
 *   Response::json(['status' => 'ok'])->send();
 *   Response::redirect('/login')->send();
 *   Response::download('/path/to/file.pdf', 'document.pdf')->send();
 */
final class Response
{
    /**
     * Create an HTML response
     *
     * @param string $content HTML content
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     */
    public static function html(string $content, int $status = 200, array $headers = []): SymfonyResponse
    {
        $headers['Content-Type'] ??= 'text/html; charset=UTF-8';

        return new SymfonyResponse($content, $status, $headers);
    }

    /**
     * Create a plain text response
     *
     * @param string $content Text content
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     */
    public static function text(string $content, int $status = 200, array $headers = []): SymfonyResponse
    {
        $headers['Content-Type'] ??= 'text/plain; charset=UTF-8';

        return new SymfonyResponse($content, $status, $headers);
    }

    /**
     * Create a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     * @param int $encodingOptions JSON encoding options
     */
    public static function json(mixed $data, int $status = 200, array $headers = [], int $encodingOptions = 0): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        if ($encodingOptions !== 0) {
            $response->setEncodingOptions($encodingOptions);
        }

        return $response;
    }

    /**
     * Create a redirect response
     *
     * @param string $url URL to redirect to
     * @param int $status HTTP status code (301 permanent, 302 temporary, 303 see other)
     * @param array<string, string> $headers Additional headers
     */
    public static function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * Create a permanent redirect (301)
     */
    public static function permanentRedirect(string $url, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, 301, $headers);
    }

    /**
     * Create a file download response
     *
     * @param string $path Path to file
     * @param string|null $filename Download filename (null = use original)
     * @param string $disposition 'attachment' or 'inline'
     * @param bool $deleteFile Delete a file after sending
     */
    public static function download(
        string  $path,
        ?string $filename = null,
        string  $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        bool    $deleteFile = false,
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($path);

        if ($filename !== null) {
            // Create ASCII fallback for non-ASCII filenames
            $fallback = preg_replace('/[^\x20-\x7E]/', '_', $filename);
            if ($fallback === '' || $fallback === null) {
                $fallback = 'download';
            }
            $response->setContentDisposition($disposition, $filename, $fallback);
        } else {
            $response->setContentDisposition($disposition);
        }

        if ($deleteFile) {
            $response->deleteFileAfterSend();
        }

        return $response;
    }

    /**
     * Create a file inline response (for viewing in browser)
     */
    public static function file(string $path, ?string $filename = null): BinaryFileResponse
    {
        return self::download($path, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * Create a streamed response (for large files or real-time output)
     *
     * @param callable $callback Function that outputs content
     * @param int $status HTTP status code
     * @param array<string, string> $headers Additional headers
     */
    public static function stream(callable $callback, int $status = 200, array $headers = []): StreamedResponse
    {
        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * Create a "no content" response (204)
     */
    public static function noContent(array $headers = []): SymfonyResponse
    {
        return new SymfonyResponse('', 204, $headers);
    }

    /**
     * Create an error response with plain text
     *
     * @param string $message Error message
     * @param int $status HTTP error status code
     */
    public static function error(string $message, int $status = 500): SymfonyResponse
    {
        return self::text($message, $status);
    }

    /**
     * Create a "not found" response (404)
     */
    public static function notFound(string $message = 'Not Found'): SymfonyResponse
    {
        return self::text($message, 404);
    }

    /**
     * Create a "forbidden" response (403)
     */
    public static function forbidden(string $message = 'Forbidden'): SymfonyResponse
    {
        return self::text($message, 403);
    }

    /**
     * Create an "unauthorized" response (401)
     */
    public static function unauthorized(string $message = 'Unauthorized'): SymfonyResponse
    {
        return self::text($message, 401);
    }

    /**
     * Create a "bad request" response (400)
     */
    public static function badRequest(string $message = 'Bad Request'): SymfonyResponse
    {
        return self::text($message, 400);
    }

    // ========================================
    // Response modifiers (return modified response)
    // ========================================

    /**
     * Add no-cache headers to a response
     */
    public static function noCache(SymfonyResponse $response): SymfonyResponse
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');

        return $response;
    }

    /**
     * Add cache headers to a response
     *
     * @param SymfonyResponse $response Response to modify
     * @param int $maxAge Cache lifetime in seconds
     * @param bool $public Whether the response can be cached publicly
     */
    public static function cache(SymfonyResponse $response, int $maxAge, bool $public = true): SymfonyResponse
    {
        $response->setMaxAge($maxAge);
        if ($public) {
            $response->setPublic();
        } else {
            $response->setPrivate();
        }
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');

        return $response;
    }

    /**
     * Add security headers to a response
     */
    public static function secure(SymfonyResponse $response): SymfonyResponse
    {
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    /**
     * Add CORS headers to a response
     *
     * @param SymfonyResponse $response Response to modify
     * @param string $origin Allowed origin (* for all)
     * @param array<string> $methods Allowed methods
     * @param array<string> $headers Allowed headers
     */
    public static function cors(
        SymfonyResponse $response,
        string          $origin = '*',
        array           $methods = ['GET', 'POST', 'OPTIONS'],
        array           $headers = ['Content-Type', 'Authorization'],
    ): SymfonyResponse {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $methods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $headers));

        return $response;
    }

    // ========================================
    // Specialized responses for TorrentPier
    // ========================================

    /**
     * Create an Atom feed response
     *
     * @param string $content Atom XML content
     * @param int $cacheTtl Cache lifetime in seconds
     */
    public static function atom(string $content, int $cacheTtl = 900): SymfonyResponse
    {
        $response = new SymfonyResponse($content, 200, [
            'Content-Type' => 'application/atom+xml; charset=UTF-8',
        ]);

        return self::cache($response, $cacheTtl);
    }

    /**
     * Create a torrent file download response
     *
     * @param string $path Path to the torrent file
     * @param string $filename Download filename
     */
    public static function torrent(string $path, string $filename): BinaryFileResponse
    {
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'application/x-bittorrent');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }

    /**
     * Create a torrent content download response (for dynamically generated torrents)
     *
     * @param string $content Torrent file content (bencoded)
     * @param string $filename Download filename
     */
    public static function torrentContent(string $content, string $filename): SymfonyResponse
    {
        $response = new SymfonyResponse($content, 200, [
            'Content-Type' => 'application/x-bittorrent',
        ]);

        // Create ASCII fallback for non-ASCII filenames
        $fallback = preg_replace('/[^\x20-\x7E]/', '_', $filename);
        if ($fallback === '' || $fallback === null) {
            $fallback = 'download.torrent';
        }

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename, $fallback),
        );

        return $response;
    }
}
