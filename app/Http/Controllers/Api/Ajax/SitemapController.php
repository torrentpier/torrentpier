<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ajax;

use App\Http\Controllers\Api\Ajax\Concerns\AjaxResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Sitemap;

/**
 * Sitemap Controller
 *
 * Handles sitemap generation.
 */
class SitemapController
{
    use AjaxResponse;

    protected string $action = 'sitemap';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $map = new Sitemap;

        $html = '';
        switch ($mode) {
            case 'create':
                $map->createSitemap();
                if (files()->isFile(SITEMAP_DIR . '/sitemap.xml')) {
                    $html .= __('SITEMAP_CREATED') . ': <b>' . bb_date(TIMENOW, config()->get('post_date_format')) . '</b> '
                        . __('SITEMAP_AVAILABLE') . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">'
                        . make_url('sitemap/sitemap.xml') . '</a>';
                } else {
                    $html .= __('SITEMAP_NOT_CREATED');
                }
                break;

            default:
                return $this->error("Invalid mode: $mode");
        }

        return $this->response([
            'html' => $html,
            'mode' => $mode,
        ]);
    }
}
