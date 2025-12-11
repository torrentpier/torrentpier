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
 * Adapter for handling SEO-friendly semantic URLs
 *
 * Handles URLs in the format: /type/slug.id/
 * Parses the slug and ID, then delegates to the legacy controller.
 */
class RouteAdapter
{
    private const array TYPE_MAP = [
        'threads' => [
            'controller' => 'viewtopic.php',
            'script' => 'topic',
            'param' => 't',  // POST_TOPIC_URL
        ],
        'forum' => [
            'controller' => 'viewforum.php',
            'script' => 'forum',
            'param' => 'f',  // POST_FORUM_URL
        ],
        'members' => [
            'controller' => 'profile.php',
            'script' => 'profile',
            'param' => 'u',  // POST_USERS_URL
            'extra' => ['mode' => 'viewprofile'],
        ],
        'groups' => [
            'controller' => 'group.php',
            'script' => 'group',
            'param' => 'g',  // POST_GROUPS_URL
        ],
        'groups_edit' => [
            'controller' => 'group_edit.php',
            'script' => 'group_edit',
            'param' => 'g',  // POST_GROUPS_URL
        ],
        'category' => [
            'controller' => 'index.php',
            'script' => 'index',
            'param' => 'c',  // POST_CAT_URL
        ],
    ];

    /**
     * @param string $type Entity type (topic, forum, profile)
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
        $config = self::TYPE_MAP[$this->type] ?? null;
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
                $title = $this->fetchTitle($id, $config);
                if ($title !== null) {
                    return $this->redirectToCanonical($id, $title);
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
        if (isset($config['extra'])) {
            foreach ($config['extra'] as $key => $value) {
                // Override mode if action is specified
                if ($key === 'mode' && $action !== null) {
                    $value = $action;
                }
                request()->query->set($key, $value);
            }
        }

        // Define constant to signal a semantic route is active (for canonical checking)
        if (!defined('SEMANTIC_ROUTE')) {
            define('SEMANTIC_ROUTE', true);
            define('SEMANTIC_ROUTE_TYPE', $this->type);
            define('SEMANTIC_ROUTE_SLUG', $parsed['slug']);
        }

        // Build controller path
        $basePath = dirname(__DIR__, 3);
        $controllerPath = $basePath . '/src/Controllers/' . $config['controller'];

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
    private function redirectToCanonical(int $id, string $title): ResponseInterface
    {
        $canonicalUrl = match ($this->type) {
            'threads' => UrlBuilder::topic($id, $title),
            'forum' => UrlBuilder::forum($id, $title),
            'members' => UrlBuilder::member($id, $title),
            'groups' => UrlBuilder::group($id, $title),
            'groups_edit' => UrlBuilder::groupEdit($id, $title),
            'category' => UrlBuilder::category($id, $title),
            default => '/',
        };

        $response = new Response();
        return $response
            ->withStatus(301)
            ->withHeader('Location', make_url($canonicalUrl));
    }

    /**
     * Fetch the title/name from the database
     * Returns null if the entity doesn't exist
     */
    private function fetchTitle(int $id, array $config): ?string
    {
        [$table, $titleCol] = match ($this->type) {
            'threads' => ['bb_topics', 'topic_title'],
            'forum' => ['bb_forums', 'forum_name'],
            'members' => ['bb_users', 'username'],
            'groups', 'groups_edit' => ['bb_groups', 'group_name'],
            'category' => ['bb_categories', 'cat_title'],
            default => [null, null],
        };

        if ($table === null) {
            return null;
        }

        $row = DB()->table($table)->get($id);

        return $row ? ($row->$titleCol ?? '') : null;
    }

    /**
     * Create a PSR-7 not found response
     */
    private function notFoundResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($message);
        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/plain');
    }
}
