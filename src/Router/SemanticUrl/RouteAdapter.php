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
 * Adapter for handling SEO-friendly semantic URLs
 *
 * Handles URLs in the format: /type/slug.id/
 * Parses the slug and ID, then delegates to the legacy controller.
 */
class RouteAdapter
{
    use ResponseTrait;

    /**
     * @param string $type Entity type (threads, forums, members, groups, groups_edit, categories)
     * @param array $options Additional options (action, LegacyAdapter options)
     */
    public function __construct(
        private readonly string $type,
        private readonly array  $options = []
    ) {}

    /**
     * Get the action/mode for this route
     */
    private function getAction(): ?string
    {
        return $this->options['action'] ?? null;
    }

    /**
     * Handle the semantic URL request
     * @throws Throwable
     */
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $config = EntityConfig::get($this->type);
        if ($config === null) {
            return $this->notFoundResponse('Invalid route type');
        }

        // Get the params segment (slug.id or just id)
        $params = $args['params'] ?? '';

        // Parse slug and ID from the URL segment
        $parsed = UrlBuilder::parseParams($params);

        // If no slug.id format, try bare ID
        if ($parsed === null) {
            if (ctype_digit($params) && (int) $params > 0) {
                $id = (int) $params;
                // Try to redirect to canonical URL if the entity exists
                $title = EntityConfig::fetchTitle($this->type, $id);
                if ($title !== null) {
                    return $this->redirectToCanonical($id, $title, $request);
                }
                // Entity doesn't exist - let controller show proper error
                $parsed = ['slug' => '', 'id' => $id];
            } else {
                return $this->notFoundResponse('Invalid URL format');
            }
        }

        // Store the slug for canonical URL checking
        $args['slug'] = $parsed['slug'];
        $args['id'] = $parsed['id'];

        // Set the ID via Request singleton
        request()->query->set($config['param'], (string) $parsed['id']);

        // Set any extra parameters (e.g., mode=viewprofile for profile)
        // An action option can override the default mode (e.g., action=email for /profile/slug.id/email/)
        $action = $this->getAction();
        $extraParams = EntityConfig::getExtraParams($this->type);
        foreach ($extraParams as $key => $value) {
            // Override mode if action is specified
            if ($key === 'mode' && $action !== null) {
                $value = $action;
            }
            request()->query->set($key, $value);
        }

        // Store semantic route info in request attributes (replaces global constants)
        request()->attributes->set('semantic_route', true);
        request()->attributes->set('semantic_route_type', $this->type);
        request()->attributes->set('semantic_route_slug', $parsed['slug']);

        // Build controller path
        $controllerPath = EntityConfig::getControllerPath($this->type);

        // Create legacy adapter and delegate
        $adapter = new LegacyAdapter(
            $controllerPath,
            $config['script'],
            $this->options
        );

        return $adapter($request, $args);
    }

    /**
     * Get the entity type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Redirect to canonical URL with slug
     * e.g., /threads/5/ → /threads/some-title.5/
     */
    private function redirectToCanonical(int $id, string $title, ServerRequestInterface $request): ResponseInterface
    {
        $canonicalUrl = EntityConfig::buildUrl($this->type, $id, $title);
        $targetUrl = make_url($canonicalUrl);

        $requestUri = (string) $request->getUri();
        RedirectLogger::canonical($requestUri, $targetUrl, "RouteAdapter::{$this->type}");

        return $this->permanentRedirect($targetUrl);
    }
}
