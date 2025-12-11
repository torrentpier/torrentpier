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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use TorrentPier\Router\LegacyAdapter;
use TorrentPier\Router\RedirectLogger;
use TorrentPier\Router\ResponseTrait;

/**
 * Redirect handler for legacy URLs
 *
 * Redirects old-style URLs (e.g., /viewtopic?t=5) to new semantic URLs (/threads/slug.5/)
 *
 * GET requests: 301 redirect to semantic URL
 * POST requests: Process normally (can't redirect POST without losing data)
 */
class LegacyRedirect
{
    use ResponseTrait;

    /**
     * Member mode redirects mapping
     * Modes with user ID: redirect to /members/slug.id/ or /members/slug.id/action/
     * Modes without user ID: redirect to standalone URLs
     */
    private const array MEMBER_MODE_REDIRECTS = [
        // Modes that require user ID → /members/slug.id/[action/]
        'viewprofile' => ['needs_id' => true, 'path' => null],      // /members/slug.id/
        'email' => ['needs_id' => true, 'path' => 'email'],   // /members/slug.id/email/

        // Standalone modes → fixed URLs
        'register' => ['needs_id' => false, 'url' => '/register/'],
        'editprofile' => ['needs_id' => false, 'url' => '/settings/'],
        'sendpassword' => ['needs_id' => false, 'url' => '/password-recovery/'],
        'bonus' => ['needs_id' => false, 'url' => '/profile/bonus/'],
        'watch' => ['needs_id' => false, 'url' => '/profile/watchlist/'],
        'activate' => ['needs_id' => false, 'url' => '/activate/', 'key_param' => 'act_key'],
    ];

    /**
     * @param string $type Entity type (threads, forums, members, groups, groups_edit, categories)
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
        $config = EntityConfig::get($this->type);
        if ($config === null) {
            return $this->notFoundResponse('Invalid route type');
        }

        // Get query parameters from the PSR-7 request
        $queryParams = $request->getQueryParams();

        // Handle member modes specially
        if ($this->type === 'members') {
            return $this->handleMemberRedirect($request, $args, $config, $queryParams);
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
            return $this->processWithCanonicalHeader($request, $args, $id);
        }

        // Fetch the title from the database to generate the slug
        $title = EntityConfig::fetchTitle($this->type, $id) ?? '';

        // Build the semantic URL
        $semanticUrl = EntityConfig::buildUrl($this->type, $id, $title);

        // Preserve additional query parameters (except the ID param and mode for profile)
        $extraParams = $this->getExtraQueryParams($config, $queryParams);
        if (!empty($extraParams)) {
            $semanticUrl .= '?' . http_build_query($extraParams, '', '&');
        }

        // Log and 301 Permanent redirect
        $targetUrl = make_url($semanticUrl);
        RedirectLogger::legacy($request->getUri()->getPath(), $targetUrl, "LegacyRedirect::{$this->type}");
        return $this->permanentRedirect($targetUrl);
    }

    /**
     * Handle member mode redirects
     * @throws Throwable
     */
    private function handleMemberRedirect(
        ServerRequestInterface $request,
        array                  $args,
        array                  $config,
        array                  $queryParams
    ): ResponseInterface {
        $mode = $queryParams['mode'] ?? null;

        // Check if this mode has a redirect defined
        $modeConfig = self::MEMBER_MODE_REDIRECTS[$mode] ?? null;
        if ($modeConfig === null) {
            // Unknown mode, use fallback controller
            return $this->fallbackToLegacy($request, $args);
        }

        // Only redirect GET requests
        if ($request->getMethod() !== 'GET') {
            $id = (int) ($queryParams[$config['param']] ?? 0);
            if ($id > 0) {
                return $this->processWithCanonicalHeader($request, $args, $id);
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

            $targetUrl = make_url($url);
            RedirectLogger::legacy($request->getUri()->getPath(), $targetUrl, "LegacyRedirect::members::{$mode}");
            return $this->permanentRedirect($targetUrl);
        }

        // Handle modes that require user ID
        $id = (int) ($queryParams[$config['param']] ?? 0);
        if ($id <= 0) {
            return $this->fallbackToLegacy($request, $args);
        }

        // Fetch username and build URL
        $title = EntityConfig::fetchTitle($this->type, $id) ?? '';
        $semanticUrl = UrlBuilder::member($id, $title);

        // Append an action path if needed (e.g., /email/)
        if (!empty($modeConfig['path'])) {
            $semanticUrl = rtrim($semanticUrl, '/') . '/' . $modeConfig['path'] . '/';
        }

        // Preserve extra query parameters (except u and mode)
        $extraParams = $this->getExtraQueryParams($config, $queryParams);
        if (!empty($extraParams)) {
            $semanticUrl .= '?' . http_build_query($extraParams, '', '&');
        }

        $targetUrl = make_url($semanticUrl);
        RedirectLogger::legacy($request->getUri()->getPath(), $targetUrl, "LegacyRedirect::members::{$mode}");
        return $this->permanentRedirect($targetUrl);
    }

    /**
     * Get extra query parameters (excluding the ID param and mode for members)
     */
    private function getExtraQueryParams(array $config, array $queryParams): array
    {
        $params = $queryParams;

        // Remove the main ID parameter
        unset($params[$config['param']]);

        // For members, also remove the mode parameter
        if ($this->type === 'members') {
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
     * Process request normally but add a Link header with canonical URL
     *
     * Used for POST requests that can't be redirected
     * @throws Throwable
     */
    private function processWithCanonicalHeader(
        ServerRequestInterface $request,
        array                  $args,
        int                    $id
    ): ResponseInterface {
        // Fetch title and build canonical URL
        $title = EntityConfig::fetchTitle($this->type, $id) ?? '';
        $canonicalUrl = make_url(EntityConfig::buildUrl($this->type, $id, $title));

        // Process the request with the fallback controller
        $response = $this->fallbackToLegacy($request, $args);

        // Add Link header for canonical URL
        return $response->withHeader('Link', '<' . $canonicalUrl . '>; rel="canonical"');
    }
}
