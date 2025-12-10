<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\SemanticUrl;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use TorrentPier\Router\LegacyAdapter;

/**
 * Redirect handler for legacy URLs
 *
 * Redirects old-style URLs (e.g., /viewtopic?t=5) to new semantic URLs (/topic/slug.5/)
 *
 * GET requests: 301 redirect to semantic URL
 * POST requests: Process normally (can't redirect POST without losing data)
 */
class LegacyRedirect
{
    private const array TYPE_CONFIG = [
        'topic' => [
            'param' => 't',
            'table' => 'bb_topics',
            'id_col' => 'topic_id',
            'title_col' => 'topic_title',
        ],
        'forum' => [
            'param' => 'f',
            'table' => 'bb_forums',
            'id_col' => 'forum_id',
            'title_col' => 'forum_name',
        ],
        'profile' => [
            'param' => 'u',
            'table' => 'bb_users',
            'id_col' => 'user_id',
            'title_col' => 'username',
        ],
    ];

    /**
     * Profile mode redirects mapping
     * Modes with user ID: redirect to /profile/slug.id/ or /profile/slug.id/action/
     * Modes without user ID: redirect to standalone URLs
     */
    private const array PROFILE_MODE_REDIRECTS = [
        // Modes that require user ID → /profile/slug.id/[action/]
        'viewprofile' => ['needs_id' => true, 'path' => null],      // /profile/slug.id/
        'email' => ['needs_id' => true, 'path' => 'email'],   // /profile/slug.id/email/

        // Standalone modes → fixed URLs
        'register' => ['needs_id' => false, 'url' => '/register/'],
        'editprofile' => ['needs_id' => false, 'url' => '/settings/'],
        'sendpassword' => ['needs_id' => false, 'url' => '/password-recovery/'],
        'bonus' => ['needs_id' => false, 'url' => '/profile/bonus/'],
        'watch' => ['needs_id' => false, 'url' => '/profile/watchlist/'],
        'activate' => ['needs_id' => false, 'url' => '/activate/', 'key_param' => 'act_key'],
    ];

    /**
     * @param string $type Entity type (topic, forum, profile)
     * @param string|null $fallbackController Path to fallback controller (for non-redirectable requests)
     * @param array $options Options for the fallback adapter
     */
    public function __construct(
        private readonly string  $type,
        private readonly ?string $fallbackController = null,
        private readonly array   $options = []
    ) {}

    /**
     * Handle the legacy URL request
     * @throws Throwable
     */
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $config = self::TYPE_CONFIG[$this->type] ?? null;
        if ($config === null) {
            return $this->notFoundResponse('Invalid route type');
        }

        // Get query parameters from the PSR-7 request
        $queryParams = $request->getQueryParams();

        // Handle profile modes specially
        if ($this->type === 'profile') {
            return $this->handleProfileRedirect($request, $args, $config, $queryParams);
        }

        // Get the entity ID from query parameters
        $id = (int) ($queryParams[$config['param']] ?? 0);
        if ($id <= 0) {
            // No valid ID, use fallback controller
            return $this->fallbackToLegacy($request, $args);
        }

        // Only redirect GET requests (POST would lose form data)
        if ($request->getMethod() !== 'GET') {
            // For POST requests, add a Link header with canonical URL and process normally
            return $this->processWithCanonicalHeader($request, $args, $id, $config);
        }

        // Fetch the title from the database to generate the slug
        $title = $this->fetchTitle($id, $config);

        // Build the semantic URL
        $semanticUrl = $this->buildSemanticUrl($id, $title);

        // Preserve additional query parameters (except the ID param and mode for profile)
        $extraParams = $this->getExtraQueryParams($config, $queryParams);
        if (!empty($extraParams)) {
            $semanticUrl .= '?' . http_build_query($extraParams, '', '&');
        }

        // 301 Permanent Redirect
        return $this->redirectResponse(make_url($semanticUrl));
    }

    /**
     * Handle profile mode redirects
     * @throws Throwable
     */
    private function handleProfileRedirect(
        ServerRequestInterface $request,
        array                  $args,
        array                  $config,
        array                  $queryParams
    ): ResponseInterface {
        $mode = $queryParams['mode'] ?? null;

        // Check if this mode has a redirect defined
        $modeConfig = self::PROFILE_MODE_REDIRECTS[$mode] ?? null;
        if ($modeConfig === null) {
            // Unknown mode, use fallback controller
            return $this->fallbackToLegacy($request, $args);
        }

        // Only redirect GET requests
        if ($request->getMethod() !== 'GET') {
            $id = (int) ($queryParams[$config['param']] ?? 0);
            if ($id > 0) {
                return $this->processWithCanonicalHeader($request, $args, $id, $config);
            }
            return $this->fallbackToLegacy($request, $args);
        }

        // Handle standalone modes (no user ID needed)
        if (!$modeConfig['needs_id']) {
            $url = $modeConfig['url'];

            // To activate mode, append the activation key
            if (isset($modeConfig['key_param'])) {
                $key = $queryParams[$modeConfig['key_param']] ?? '';
                if ($key !== '') {
                    $url = rtrim($url, '/') . '/' . urlencode($key) . '/';
                }
            }

            return $this->redirectResponse(make_url($url));
        }

        // Handle modes that require user ID
        $id = (int) ($queryParams[$config['param']] ?? 0);
        if ($id <= 0) {
            return $this->fallbackToLegacy($request, $args);
        }

        // Fetch username and build URL
        $title = $this->fetchTitle($id, $config);
        $semanticUrl = UrlBuilder::profile($id, $title);

        // Append an action path if needed (e.g., /email/)
        if (!empty($modeConfig['path'])) {
            $semanticUrl = rtrim($semanticUrl, '/') . '/' . $modeConfig['path'] . '/';
        }

        // Preserve extra query parameters (except u and mode)
        $extraParams = $this->getExtraQueryParams($config, $queryParams);
        if (!empty($extraParams)) {
            $semanticUrl .= '?' . http_build_query($extraParams, '', '&');
        }

        return $this->redirectResponse(make_url($semanticUrl));
    }

    /**
     * Fetch the title/name from the database
     */
    private function fetchTitle(int $id, array $config): string
    {
        $row = DB()->table($config['table'])->get($id);

        return $row ? ($row->{$config['title_col']} ?? '') : '';
    }

    /**
     * Build the semantic URL for this entity type
     */
    private function buildSemanticUrl(int $id, string $title): string
    {
        return match ($this->type) {
            'topic' => UrlBuilder::topic($id, $title),
            'forum' => UrlBuilder::forum($id, $title),
            'profile' => UrlBuilder::profile($id, $title),
            default => '/',
        };
    }

    /**
     * Get extra query parameters (excluding the ID param and mode for profile)
     */
    private function getExtraQueryParams(array $config, array $queryParams): array
    {
        $params = $queryParams;

        // Remove the main ID parameter
        unset($params[$config['param']]);

        // For profile, also remove the mode parameter
        if ($this->type === 'profile') {
            unset($params['mode']);
        }

        return $params;
    }

    /**
     * Fallback to legacy controller (for non-redirectable requests)
     * @throws Throwable
     */
    private function fallbackToLegacy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if ($this->fallbackController === null) {
            return $this->notFoundResponse('Resource not found');
        }

        $adapter = new LegacyAdapter(
            $this->fallbackController,
            $this->type,
            $this->options
        );

        return $adapter($request, $args);
    }

    /**
     * Create a PSR-7 redirect response (301 Permanent)
     */
    private function redirectResponse(string $url): ResponseInterface
    {
        $response = new Response();
        return $response
            ->withStatus(301)
            ->withHeader('Location', $url);
    }

    /**
     * Create a PSR-7 not found response (404)
     */
    private function notFoundResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $body = $response->getBody();
        $body->write($message);

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/plain');
    }

    /**
     * Process request normally but add a Link header with canonical URL
     *
     * Used for POST requests that can't be redirected
     * @throws Throwable
     */
    private function processWithCanonicalHeader(
        ServerRequestInterface $request,
        array                  $args,
        int                    $id,
        array                  $config
    ): ResponseInterface {
        // Fetch title and build canonical URL
        $title = $this->fetchTitle($id, $config);
        $canonicalUrl = make_url($this->buildSemanticUrl($id, $title));

        // Process the request with the fallback controller
        $response = $this->fallbackToLegacy($request, $args);

        // Add Link header for canonical URL
        return $response->withHeader('Link', '<' . $canonicalUrl . '>; rel="canonical"');
    }
}
