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
use TorrentPier\Helpers\CronHelper;
use TorrentPier\Legacy\Admin\Common;
use TorrentPier\Legacy\Group;

/**
 * Manage Admin Controller
 *
 * Handles various admin operations like cache clearing, statistics, etc.
 */
class ManageAdminController
{
    use AjaxResponse;

    protected string $action = 'manage_admin';

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

        $responseData = ['mode' => $mode];

        switch ($mode) {
            case 'clear_cache':
                foreach (config()->get('cache.engines') as $cacheName => $cacheVal) {
                    CACHE($cacheName)->rm();
                }
                $responseData['cache_html'] = '<span class="seed bold">' . __('ALL_CACHE_CLEARED') . '</span>';
                break;

            case 'clear_datastore':
                datastore()->clean();
                $responseData['datastore_html'] = '<span class="seed bold">' . __('DATASTORE_CLEARED') . '</span>';
                break;

            case 'clear_template_cache':
                $twigCacheDir = template()->getCacheDir() . 'twig';
                if (files()->isDirectory($twigCacheDir)) {
                    files()->cleanDirectory($twigCacheDir);
                }
                $responseData['template_cache_html'] = '<span class="seed bold">' . __('ALL_TEMPLATE_CLEARED') . '</span>';
                break;

            case 'indexer':
                $manticore = manticore();
                if ($manticore->initialLoad()) {
                    $responseData['indexer_html'] = '<span class="seed bold">' . __('INDEXER_SUCCESS') . '</span>';
                } else {
                    $responseData['indexer_html'] = '<span class="leech bold">' . __('ERROR') . '</span>';
                }
                break;

            case 'update_user_level':
                Group::update_user_level('all');
                $responseData['update_user_level_html'] = '<span class="seed bold">' . __('USER_LEVELS_UPDATED') . '</span>';
                break;

            case 'sync_topics':
                Common::sync('topic', 'all');
                Common::sync_all_forums();
                $responseData['sync_topics_html'] = '<span class="seed bold">' . __('TOPICS_DATA_SYNCHRONIZED') . '</span>';
                break;

            case 'sync_user_posts':
                Common::sync('user_posts', 'all');
                $responseData['sync_user_posts_html'] = '<span class="seed bold">' . __('USER_POSTS_COUNT_SYNCHRONIZED') . '</span>';
                break;

            case 'unlock_cron':
                CronHelper::enableBoard();
                $responseData['unlock_cron_html'] = '<span class="seed bold">' . __('ADMIN_UNLOCKED') . '</span>';
                break;

            case 'tr_stats':
                $responseData['tr_stats_html'] = $this->getTorrentStats();
                break;

            case 'tracker_stats':
                $responseData['tracker_stats_html'] = $this->getTrackerStats();
                break;

            default:
                return $this->error('Invalid mode: ' . $mode);
        }

        return $this->response($responseData);
    }

    /**
     * @throws BindingResolutionException
     */
    private function getTorrentStats(): string
    {
        $excludedUsers = array_map('intval', explode(',', EXCLUDED_USERS));

        $stats = [
            DB()->table(BB_USERS)
                ->where('user_lastvisit < ?', TIMENOW - 2592000)
                ->where('user_id NOT IN (?)', $excludedUsers)
                ->count('*'),

            DB()->table(BB_USERS)
                ->where('user_lastvisit < ?', TIMENOW - 7776000)
                ->where('user_id NOT IN (?)', $excludedUsers)
                ->count('*'),

            humn_size((int)DB()->table(BB_BT_TORRENTS)->aggregation('ROUND(AVG(size))')),

            DB()->table(BB_BT_TORRENTS)->count('*'),

            (int)DB()->table(BB_BT_TRACKER_SNAP)
                ->where('seeders > 0')
                ->aggregation('COUNT(DISTINCT topic_id)'),

            (int)DB()->table(BB_BT_TRACKER_SNAP)
                ->where('seeders > 5')
                ->aggregation('COUNT(DISTINCT topic_id)'),

            (int)DB()->table(BB_BT_TORRENTS)->aggregation('COUNT(DISTINCT poster_id)'),

            (int)DB()->table(BB_BT_TORRENTS)
                ->where('reg_time >= ?', TIMENOW - 2592000)
                ->aggregation('COUNT(DISTINCT poster_id)'),
        ];

        $html = '<table class="forumline"><tr><th colspan="2">' . __('TORRENT_STATS_TITLE') . '</th></tr>';
        foreach ($stats as $i => $value) {
            $label = __('TR_STATS')[$i] ?? "Stat $i";
            $html .= '<tr><td class="row1">' . $label . '</td><td class="row2"><b>' . $value . '</b></td></tr>';
        }
        $html .= '</table><br/>';

        return $html;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getTrackerStats(): string
    {
        $announceInterval = (int)config()->get('announce_interval');

        DB()->query('DROP TEMPORARY TABLE IF EXISTS tmp_tracker_stats');
        DB()->query(
            '
            CREATE TEMPORARY TABLE tmp_tracker_stats (
                `topic_id` mediumint(8) unsigned NOT NULL default 0,
                `user_id` mediumint(9) NOT NULL default 0,
                `peer_id` char(20) binary default NULL,
                `seeder` tinyint(1) NOT NULL default 0,
                `speed_up` mediumint(8) unsigned NOT NULL default 0,
                `speed_down` mediumint(8) unsigned NOT NULL default 0,
                `update_time` int(11) NOT NULL default 0
            )
            SELECT topic_id, user_id, peer_id, seeder, speed_up, speed_down, update_time
            FROM ' . BB_BT_TRACKER,
        );

        $pWithinAnn = (int)DB()->fetch_row(
            'SELECT COUNT(*) AS cnt FROM tmp_tracker_stats WHERE update_time >= ' . (TIMENOW - $announceInterval),
        )['cnt'];

        $allPeersData = DB()->fetch_row('
            SELECT COUNT(*) AS p_all,
                   SUM(speed_up) as speed_up,
                   SUM(speed_down) as speed_down,
                   UNIX_TIMESTAMP() - MIN(update_time) AS max_peer_time
            FROM tmp_tracker_stats
        ');

        $activeUsers = (int)DB()->fetch_row('SELECT COUNT(DISTINCT user_id) AS cnt FROM tmp_tracker_stats')['cnt'];
        $allBtUsers = DB()->table(BB_BT_USERS)->count('*');
        $allBbUsers = DB()->table(BB_USERS)->where('user_id != ?', BOT_UID)->count('*');

        $activeTorrents = (int)DB()->fetch_row('SELECT COUNT(DISTINCT topic_id) AS cnt FROM tmp_tracker_stats')['cnt'];
        $torrentsWithSeeder = (int)DB()->fetch_row('SELECT COUNT(DISTINCT topic_id) AS cnt FROM tmp_tracker_stats WHERE seeder = 1')['cnt'];

        $torrentsTotals = DB()->fetch_row('SELECT COUNT(*) AS tor_all, SUM(size) AS torrents_size FROM ' . BB_BT_TORRENTS);

        $clientList = '';
        if (!$clientsPercentage = CACHE('tr_cache')->get('tracker_clients_stats')) {
            $rowset = DB()->fetch_rowset('SELECT peer_id FROM tmp_tracker_stats');
            if (!empty($rowset)) {
                $clients = [];
                $clientCount = \count($rowset);
                foreach ($rowset as $row) {
                    $clientKey = substr($row['peer_id'], 0, 3);
                    $clients[$clientKey] = ($clients[$clientKey] ?? 0) + 1;
                }
                arsort($clients, SORT_NUMERIC);
                $clientsPercentage = [];
                foreach ($clients as $client => $count) {
                    $percentage = number_format(($count / $clientCount) * 100, 2);
                    $clientsPercentage[$client] = "[$count] => $percentage%";
                }
                CACHE('tr_cache')->set('tracker_clients_stats', $clientsPercentage, 3600);
            }
        }
        $clientsPercentage = $clientsPercentage ?: [];

        $numwant = 10;
        $n = 1;
        foreach (\array_slice($clientsPercentage, 0, $numwant) as $client => $value) {
            $clientList .= "$n. " . get_user_torrent_client($client) . " $value<br/>";
            $n++;
        }

        DB()->query('DROP TEMPORARY TABLE IF EXISTS tmp_tracker_stats');

        $html = '<table class="forumline"><tr><th colspan="2">' . __('TRACKER_STATS_TITLE') . '</th></tr>';
        $html .= '<tr><td class="row1">' . __('USERS') . ': bb-all / bt-all / bt-active</td>';
        $html .= '<td class="row2"><b>' . $allBbUsers . ' / ' . $allBtUsers . ' / ' . $activeUsers . '</b></td></tr>';
        $html .= '<tr><td class="row1">' . __('TORRENTS') . ': all / active / with seeder</td>';
        $html .= '<td class="row2"><b>' . $torrentsTotals['tor_all'] . ' / ' . $activeTorrents . ' / ' . $torrentsWithSeeder;
        $html .= '</b> [' . humn_size($torrentsTotals['torrents_size']) . ']</td></tr>';
        $html .= '<tr><td class="row1">' . __('PEERS') . ': all (' . $allPeersData['max_peer_time'] . 's) / in announce interval (' . $announceInterval . 's)</td>';
        $html .= '<td class="row2"><b>' . $allPeersData['p_all'] . ' / ' . $pWithinAnn . '</b>';
        $html .= ' [up: ' . humn_size($allPeersData['speed_up']) . '/s, down: ' . humn_size($allPeersData['speed_down']) . '/s]</td></tr>';

        if ($clientList) {
            $html .= '<tr><td class="row1">' . __('CLIENTS') . '</td>';
            $html .= '<td class="row2">' . $clientList . '</td></tr>';
        }

        $html .= '</table><br/>';

        return $html;
    }
}
