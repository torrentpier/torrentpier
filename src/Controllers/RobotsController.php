<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Controllers;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Robots.txt controller
 *
 * Serves robots.txt content from database configuration.
 * Fallback to allow-all if config is empty.
 */
class RobotsController
{
    private const string DEFAULT_CONTENT = "User-agent: *\nAllow: /\n";

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $content = config()->get('robots_txt') ?: self::DEFAULT_CONTENT;

        return new TextResponse($content);
    }
}
