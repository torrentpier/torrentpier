<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Tracker;

use App\Http\Controllers\Tracker\Concerns\TrackerResponses;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Helpers\IPHelper;
use TorrentPier\Http\Response\BencodeResponse;

/**
 * BitTorrent Announce Controller
 *
 * Handles announce requests from BitTorrent clients.
 */
class AnnounceController
{
    use TrackerResponses;

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // Check User-Agent for existence
        $userAgent = (string)request()->server->get('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            return new BencodeResponse(['failure reason' => 'No User-Agent provided'], 200);
        }

        $announce_interval = config()->get('tracker.announce_interval');
        $passkey_key = config()->get('tracker.passkey_key');

        // Recover info_hash
        if (request()->query->has('?info_hash') && !request()->query->has('info_hash')) {
            request()->query->set('info_hash', request()->query->get('?info_hash'));
        }

        // Initial request verification
        if (str_contains(request()->server->get('REQUEST_URI'), 'scrape')) {
            return $this->msgDie('Please disable SCRAPE!');
        }

        if (!request()->query->has($passkey_key) || !\is_string(request()->query->get($passkey_key))) {
            return $this->msgDie('Please LOG IN and RE-DOWNLOAD this torrent (passkey not found)');
        }

        // Input var names
        // String
        $input_vars_str = ['info_hash', 'peer_id', 'event', $passkey_key];
        // Numeric
        $input_vars_num = ['port', 'uploaded', 'downloaded', 'left', 'numwant', 'compact'];

        // Init received data
        // String
        $info_hash = $peer_id = $event = $passkey = null;
        foreach ($input_vars_str as $var_name) {
            ${$var_name} = request()->query->has($var_name) ? (string)request()->query->get($var_name) : null;
        }

        // Numeric
        $port = $uploaded = $downloaded = $left = $numwant = $compact = null;
        foreach ($input_vars_num as $var_name) {
            ${$var_name} = request()->query->has($var_name) ? (float)request()->query->get($var_name) : null;
        }

        // Passkey
        $passkey = ${$passkey_key} ?? null;

        // Verify request
        // Required params (info_hash, peer_id, port, uploaded, downloaded, left, passkey)
        if (!isset($peer_id)) {
            return $this->msgDie('peer_id was not provided');
        }
        if (\strlen($peer_id) !== 20) {
            return $this->msgDie('Invalid peer_id: ' . $peer_id);
        }

        // Check for a client ban
        if (config()->get('client_ban.enabled')) {
            $targetClient = [];

            foreach (config()->get('client_ban.clients') as $clientId => $banReason) {
                if (str_starts_with($peer_id, $clientId)) {
                    $targetClient = [
                        'peer_id' => $clientId,
                        'ban_reason' => $banReason,
                    ];
                    break;
                }
            }

            if (config()->get('client_ban.only_allow_mode')) {
                if (empty($targetClient['peer_id'])) {
                    return $this->msgDie('Your BitTorrent client has been banned!');
                }
            } else {
                if (!empty($targetClient['peer_id'])) {
                    return $this->msgDie(empty($targetClient['ban_reason']) ? 'Your BitTorrent client has been banned!' : $targetClient['ban_reason']);
                }
            }
        }

        // Verify info_hash
        if (!isset($info_hash)) {
            return $this->msgDie('info_hash was not provided');
        }

        $event = strtolower((string)$event);
        if (!\in_array($event, ['started', 'completed', 'stopped', 'paused', ''])) {
            return $this->msgDie('Invalid event: ' . $event);
        }

        // Store info hash in hex format
        $info_hash_hex = bin2hex($info_hash);

        // Store peer id
        $peer_id_sql = preg_replace('/[^a-zA-Z0-9\-_]/', '', $peer_id);

        // Stopped event
        $stopped = ($event === 'stopped');

        // Check info_hash length
        if (\strlen($info_hash) !== 20) {
            return $this->msgDie('Invalid info_hash: ' . (mb_check_encoding($info_hash, DEFAULT_CHARSET) ? $info_hash : $info_hash_hex));
        }

        /**
         * Block system-reserved ports since 99.9% of the time they're fake and thus not connectable
         * Some clients will send port of 0 on 'stopped' events. Let them through as they won't receive peers anyway.
         */
        if (
            !isset($port)
            || !is_numeric($port)
            || ($port < 1024 && !$stopped)
            || $port > 0xFFFF
            || (!empty(config()->get('tracker.disallowed_ports')) && \in_array($port, config()->get('tracker.disallowed_ports')))
        ) {
            return $this->msgDie('Invalid port: ' . $port);
        }

        if (!isset($uploaded) || !is_numeric($uploaded) || $uploaded < 0) {
            return $this->msgDie('Invalid uploaded value: ' . $uploaded);
        }

        if (!isset($downloaded) || !is_numeric($downloaded) || $downloaded < 0) {
            return $this->msgDie('Invalid downloaded value: ' . $downloaded);
        }

        if (!isset($left) || !is_numeric($left) || $left < 0) {
            return $this->msgDie('Invalid left value: ' . $left);
        }

        if (\strlen($userAgent) > 64) {
            return $this->msgDie('User-Agent must be less than 64 characters long');
        }

        if (preg_match('/(Mozilla|Browser|Chrome|Safari|AppleWebKit|Opera|Links|Lynx|Bot|Unknown)/i', $userAgent)) {
            return $this->msgDie('Browser disallowed');
        }

        // IP
        $ip = request()->server->get('REMOTE_ADDR');

        // 'ip' query handling
        if (!config()->get('tracker.ignore_reported_ip') && request()->query->has('ip') && $ip !== request()->query->get('ip')) {
            if (!config()->get('tracker.verify_reported_ip') && request()->server->has('HTTP_X_FORWARDED_FOR')) {
                $x_ip = request()->server->get('HTTP_X_FORWARDED_FOR');

                if ($x_ip === request()->query->get('ip')) {
                    $filteredIp = filter_var($x_ip, FILTER_VALIDATE_IP);
                    if ($filteredIp !== false && (config()->get('tracker.allow_internal_ip') || !filter_var($filteredIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
                        $ip = $filteredIp;
                    }
                }
            }
        }

        // Check that IP format is valid
        if (!IPHelper::isValid($ip)) {
            return $this->msgDie("Invalid IP: {$ip}");
        }

        // Convert IP to long format
        $ip_sql = IPHelper::ip2long($ip);

        // Detect IP version
        $ipv4 = $ipv6 = null;
        $ip_version = IPHelper::isValidv6($ip) ? 'ipv6' : 'ip';
        if ($ip_version === 'ipv6') {
            $ipv6 = $ip_sql;
        } else {
            $ipv4 = $ip_sql;
        }

        // Peer unique id
        $peer_hash = hash('xxh128', $passkey . $info_hash_hex . $port);

        // Set seeder and complete
        $complete = $seeder = ($left == 0) ? 1 : 0;

        // Get cached peer info from previous announce (last peer info)
        $lp_info = CACHE('tr_cache')->get(PEER_HASH_PREFIX . $peer_hash);

        // Stopped event, slice peer's cache life to 30 seconds
        if ($stopped && $lp_info) {
            CACHE('tr_cache')->set(PEER_HASH_PREFIX . $peer_hash, $lp_info, 30);
        }

        // Drop fast announce
        if ($lp_info && (!isset($event) || !$stopped)) {
            if ($lp_info['ip_ver4'] === $ipv4 || $lp_info['ip_ver6'] === $ipv6 || isset($lp_info['ip_ver4'], $lp_info['ip_ver6'])) {
                if ($lp_cached_peers = CACHE('tr_cache')->get(PEERS_LIST_PREFIX . $lp_info['topic_id'])) {
                    return $this->dropFastAnnounce($lp_info, $lp_cached_peers);
                }
            }
        }

        // Get last peer info from DB
        if (!CACHE('tr_cache')->used && !$lp_info) {
            $lp_info = DB()->fetch_row('
                SELECT * FROM ' . BB_BT_TRACKER . " WHERE peer_hash = '{$peer_hash}' LIMIT 1
            ");
        }

        $user_id = $topic_id = $releaser = $tor_type = null;
        $hybrid_unrecord = false;

        if ($lp_info) {
            $user_id = $lp_info['user_id'];
            $topic_id = $lp_info['topic_id'];
            $releaser = $lp_info['releaser'];
            $tor_type = $lp_info['tor_type'];
            $hybrid_unrecord = $lp_info['hybrid_unrecord'] ?? false;
        } else {
            $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');

            /**
             * Currently torrent clients send truncated v2 hashes (the design raises questions).
             * @see https://github.com/bittorrent/bittorrent.org/issues/145#issuecomment-1720040343
             */
            $info_hash_where = "WHERE tor.info_hash = '{$info_hash_sql}' OR SUBSTRING(tor.info_hash_v2, 1, 20) = '{$info_hash_sql}'";

            $passkey_sql = DB()->escape($passkey);

            $sql = '
                SELECT tor.topic_id, tor.poster_id, tor.tor_type, tor.tor_status, tor.info_hash, tor.info_hash_v2, bt.*, u.user_level
                FROM ' . BB_BT_TORRENTS . ' tor
                LEFT JOIN ' . BB_BT_USERS . " bt ON bt.auth_key = '{$passkey_sql}'
                LEFT JOIN " . BB_USERS . " u ON u.user_id = bt.user_id
                {$info_hash_where}
                LIMIT 1
            ";
            $row = DB()->fetch_row($sql);

            // Verify if torrent registered on tracker and user authorized
            if (empty($row['topic_id'])) {
                return $this->msgDie('Torrent not registered, info_hash = ' . (mb_check_encoding($info_hash, DEFAULT_CHARSET) ? $info_hash : $info_hash_hex));
            }
            if (empty($row['user_id'])) {
                return $this->msgDie('Please LOG IN and RE-DOWNLOAD this torrent (user not found)');
            }

            // Assign variables
            $user_id = $row['user_id'];
            if (!\defined('IS_GUEST')) {
                \define('IS_GUEST', (int)$user_id === GUEST_UID);
            }
            if (!\defined('IS_ADMIN')) {
                \define('IS_ADMIN', !IS_GUEST && (int)$row['user_level'] === ADMIN);
            }
            if (!\defined('IS_MOD')) {
                \define('IS_MOD', !IS_GUEST && (int)$row['user_level'] === MOD);
            }
            if (!\defined('IS_GROUP_MEMBER')) {
                \define('IS_GROUP_MEMBER', !IS_GUEST && (int)$row['user_level'] === GROUP_MEMBER);
            }
            if (!\defined('IS_USER')) {
                \define('IS_USER', !IS_GUEST && (int)$row['user_level'] === USER);
            }
            if (!\defined('IS_SUPER_ADMIN')) {
                \define('IS_SUPER_ADMIN', IS_ADMIN && isset(config()->get('auth.super_admins')[$user_id]));
            }
            if (!\defined('IS_AM')) {
                \define('IS_AM', IS_ADMIN || IS_MOD);
            }
            $topic_id = $row['topic_id'];
            $releaser = (int)($user_id == $row['poster_id']);
            $tor_type = $row['tor_type'];
            $tor_status = $row['tor_status'];

            // Check tor status
            if (!IS_AM && isset(config()->get('tracker.tor_frozen')[$tor_status]) && !(isset(config()->get('tracker.tor_frozen_author_download')[$tor_status]) && $releaser)) {
                return $this->msgDie('Torrent frozen and cannot be downloaded');
            }

            // Check hybrid status
            if (!empty($row['info_hash']) && !empty($row['info_hash_v2'])) {
                $stat_protocol = match ((int)config()->get('tracker.hybrid_stat_protocol')) {
                    2 => substr($row['info_hash_v2'], 0, 20),
                    default => $row['info_hash'] // 1
                };
                if ($info_hash !== $stat_protocol) {
                    $hybrid_unrecord = true; // This allows us to announce only for one info-hash
                }
            }

            // Ratio limits
            if ((RATIO_ENABLED || config()->get('tracker.limit_concurrent_ips')) && !$stopped) {
                $user_ratio = get_bt_ratio($row);
                if ($user_ratio === null) {
                    $user_ratio = 1;
                }
                $rating_msg = '';

                if (!$seeder) {
                    foreach (config()->get('tracker.rating') as $ratio => $limit) {
                        if ($user_ratio < $ratio) {
                            config()->set('tracker.limit_active_tor', 1);
                            config()->set('tracker.limit_leech_count', $limit);
                            $rating_msg = " (ratio < {$ratio})";
                            break;
                        }
                    }
                }

                // Limit active torrents
                if (!isset(config()->get('auth.unlimited_users')[$user_id]) && config()->get('tracker.limit_active_tor') && ((config()->get('tracker.limit_seed_count') && $seeder) || (config()->get('tracker.limit_leech_count') && !$seeder))) {
                    $sql = 'SELECT COUNT(DISTINCT topic_id) AS active_torrents
                        FROM ' . BB_BT_TRACKER . "
                        WHERE user_id = {$user_id}
                            AND seeder = {$seeder}
                            AND topic_id != {$topic_id}";

                    if (!$seeder && config()->get('tracker.leech_expire_factor') && $user_ratio < 0.5) {
                        $sql .= ' AND update_time > ' . (TIMENOW - 60 * config()->get('tracker.leech_expire_factor'));
                    }
                    $sql .= ' GROUP BY user_id';

                    if ($row = DB()->fetch_row($sql)) {
                        if ($seeder && config()->get('tracker.limit_seed_count') && $row['active_torrents'] >= config()->get('tracker.limit_seed_count')) {
                            return $this->msgDie('Only ' . config()->get('tracker.limit_seed_count') . ' torrent(s) allowed for seeding');
                        }
                        if (!$seeder && config()->get('tracker.limit_leech_count') && $row['active_torrents'] >= config()->get('tracker.limit_leech_count')) {
                            return $this->msgDie('Only ' . config()->get('tracker.limit_leech_count') . ' torrent(s) allowed for leeching' . $rating_msg);
                        }
                    }
                }

                // Limit concurrent IPs
                if (config()->get('tracker.limit_concurrent_ips') && ((config()->get('tracker.limit_seed_ips') && $seeder) || (config()->get('tracker.limit_leech_ips') && !$seeder))) {
                    $sql = 'SELECT COUNT(DISTINCT ip) AS ips
                        FROM ' . BB_BT_TRACKER . "
                        WHERE topic_id = {$topic_id}
                            AND user_id = {$user_id}
                            AND seeder = {$seeder}
                            AND {$ip_version} != '{$ip_sql}'";

                    if (!$seeder && config()->get('tracker.leech_expire_factor')) {
                        $sql .= ' AND update_time > ' . (TIMENOW - 60 * config()->get('tracker.leech_expire_factor'));
                    }
                    $sql .= ' GROUP BY topic_id';

                    if ($row = DB()->fetch_row($sql)) {
                        if ($seeder && config()->get('tracker.limit_seed_ips') && $row['ips'] >= config()->get('tracker.limit_seed_ips')) {
                            return $this->msgDie('You can seed only from ' . config()->get('tracker.limit_seed_ips') . " IP's");
                        }
                        if (!$seeder && config()->get('tracker.limit_leech_ips') && $row['ips'] >= config()->get('tracker.limit_leech_ips')) {
                            return $this->msgDie('You can leech only from ' . config()->get('tracker.limit_leech_ips') . " IP's");
                        }
                    }
                }
            }
        }

        // Up/Down speed
        $speed_up = $speed_down = 0;

        if ($lp_info && $lp_info['update_time'] < TIMENOW) {
            if ($uploaded > $lp_info['uploaded']) {
                $speed_up = ceil(($uploaded - $lp_info['uploaded']) / (TIMENOW - $lp_info['update_time']));
            }
            if ($downloaded > $lp_info['downloaded']) {
                $speed_down = ceil(($downloaded - $lp_info['downloaded']) / (TIMENOW - $lp_info['update_time']));
            }
        }

        // Up/Down addition
        $up_add = ($lp_info && $uploaded > $lp_info['uploaded']) ? $uploaded - $lp_info['uploaded'] : 0;
        $down_add = ($lp_info && $downloaded > $lp_info['downloaded']) ? $downloaded - $lp_info['downloaded'] : 0;

        // Gold/Silver releases
        if (config()->get('tracker.gold_silver_enabled') && $down_add) {
            if ($tor_type == TOR_TYPE_GOLD) {
                $down_add = 0;
            } // Silver releases
            elseif ($tor_type == TOR_TYPE_SILVER) {
                $down_add = ceil($down_add / 2);
            }
        }

        // Freeleech
        if (config()->get('tracker.freeleech') && $down_add) {
            $down_add = 0;
        }

        // Insert / update peer info
        $peer_info_updated = false;
        $update_time = ($stopped) ? 0 : TIMENOW;

        if ($lp_info && empty($hybrid_unrecord)) {
            $sql = 'UPDATE ' . BB_BT_TRACKER . " SET update_time = {$update_time}";

            $sql .= ", {$ip_version} = '{$ip_sql}'";
            $sql .= ", port = '{$port}'";
            $sql .= ", seeder = {$seeder}";
            $sql .= ($releaser != $lp_info['releaser']) ? ", releaser = {$releaser}" : '';

            $sql .= ($tor_type != $lp_info['tor_type']) ? ", tor_type = {$tor_type}" : '';

            $sql .= ($uploaded != $lp_info['uploaded']) ? ", uploaded = {$uploaded}" : '';
            $sql .= ($downloaded != $lp_info['downloaded']) ? ", downloaded = {$downloaded}" : '';
            $sql .= ", remain = {$left}";

            $sql .= $up_add ? ", up_add = up_add + {$up_add}" : '';
            $sql .= $down_add ? ", down_add = down_add + {$down_add}" : '';

            $sql .= ", speed_up = {$speed_up}";
            $sql .= ", speed_down = {$speed_down}";

            $sql .= ", complete = {$complete}";
            $sql .= ", peer_id = '{$peer_id_sql}'";

            $sql .= " WHERE peer_hash = '{$peer_hash}'";
            $sql .= ' LIMIT 1';

            DB()->query($sql);

            $peer_info_updated = DB()->affected_rows();
        }

        if ((!$lp_info || !$peer_info_updated) && !$stopped && empty($hybrid_unrecord)) {
            $columns = "peer_hash, topic_id, user_id, {$ip_version}, port, seeder, releaser, tor_type, uploaded, downloaded, remain, speed_up, speed_down, up_add, down_add, update_time, complete, peer_id";
            $values = "'{$peer_hash}', {$topic_id}, {$user_id}, '{$ip_sql}', {$port}, {$seeder}, {$releaser}, {$tor_type}, {$uploaded}, {$downloaded}, {$left}, {$speed_up}, {$speed_down}, {$up_add}, {$down_add}, {$update_time}, {$complete}, '{$peer_id_sql}'";

            DB()->query('REPLACE INTO ' . BB_BT_TRACKER . " ({$columns}) VALUES ({$values})");
        }

        // Exit if stopped
        if ($stopped) {
            return $this->dummyExit();
        }

        // Store peer info in cache
        $lp_info_new = [
            'downloaded' => (float)$downloaded,
            'releaser' => (int)$releaser,
            'seeder' => (int)$seeder,
            'topic_id' => (int)$topic_id,
            'update_time' => (int)TIMENOW,
            'uploaded' => (float)$uploaded,
            'user_id' => (int)$user_id,
            'tor_type' => (int)$tor_type,
            'complete' => (int)$complete,
            'ip_ver4' => $lp_info['ip_ver4'] ?? $ipv4,
            'ip_ver6' => $lp_info['ip_ver6'] ?? $ipv6,
        ];

        if (!empty($hybrid_unrecord)) {
            $lp_info_new['hybrid_unrecord'] = $hybrid_unrecord;
        }

        // Cache new list with peer hash
        CACHE('tr_cache')->set(PEER_HASH_PREFIX . $peer_hash, $lp_info_new, PEER_HASH_EXPIRE);

        // Get cached output
        $output = CACHE('tr_cache')->get(PEERS_LIST_PREFIX . $topic_id);

        if (!$output) {
            // Retrieve peers
            $numwant = (int)config()->get('tracker.numwant');
            $compact_mode = (config()->get('tracker.compact_mode') || !empty($compact));

            $rowset = DB()->fetch_rowset('
                SELECT ip, ipv6, port
                FROM ' . BB_BT_TRACKER . "
                WHERE topic_id = {$topic_id}
                ORDER BY seeder, RAND()
                LIMIT {$numwant}
            ");

            if (empty($rowset)) {
                $rowset[] = ['ip' => $ip_sql, 'port' => (int)$port];
            }

            $peers = '';
            $peers6 = '';

            if ($compact_mode) {
                foreach ($rowset as $peer) {
                    if (!empty($peer['ip'])) {
                        $peer_ipv4 = IPHelper::decode($peer['ip']);
                        $peers .= inet_pton($peer_ipv4) . pack('n', $peer['port']);
                    }
                    if (!empty($peer['ipv6'])) {
                        $peer_ipv6 = IPHelper::decode($peer['ipv6']);
                        $peers6 .= inet_pton($peer_ipv6) . pack('n', $peer['port']);
                    }
                }
            } else {
                $peers = [];

                foreach ($rowset as $peer) {
                    if (!empty($peer['ip'])) {
                        $peers[] = ['ip' => IPHelper::decode($peer['ip']), 'port' => (int)$peer['port']];
                    }
                    if (!empty($peer['ipv6'])) {
                        $peers[] = ['ip' => IPHelper::decode($peer['ipv6']), 'port' => (int)$peer['port']];
                    }
                }
            }

            $seeders = $leechers = $client_completed = 0;

            if (config()->get('tracker.scrape')) {
                $row = DB()->fetch_row('
                    SELECT seeders, leechers, completed
                    FROM ' . BB_BT_TRACKER_SNAP . "
                    WHERE topic_id = {$topic_id}
                    LIMIT 1
                ");

                $seeders = $row['seeders'] ?? ($seeder ? 1 : 0);
                $leechers = $row['leechers'] ?? (!$seeder ? 1 : 0);
                $client_completed = $row['completed'] ?? 0;
            }

            $output = [
                'interval' => (int)$announce_interval,
                'complete' => (int)$seeders,
                'incomplete' => (int)$leechers,
                'downloaded' => (int)$client_completed,
            ];

            if (!empty($peers)) {
                $output['peers'] = $peers;
            }

            if (!empty($peers6)) {
                $output['peers6'] = $peers6;
            }

            CACHE('tr_cache')->set(PEERS_LIST_PREFIX . $topic_id, $output, PEERS_LIST_EXPIRE);
        }

        $output['external ip'] = inet_pton($ip);

        // Return data to client
        return new BencodeResponse($output);
    }
}
