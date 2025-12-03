<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!config()->get('tor_thank')) {
    $this->ajax_die(__('MODULE_OFF'));
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

if (!$topic_id = (int)$this->request['topic_id']) {
    $this->ajax_die(__('INVALID_TOPIC_ID'));
}

if (!$poster_id = (int)$this->request['poster_id']) {
    $this->ajax_die(__('NO_USER_ID_SPECIFIED'));
}

$cache_lifetime = 3600;
$thanks_cache_key = 'topic_thanks_' . $topic_id;

/**
 * Get thanks by topic id
 *
 * @param $topic_id
 * @param string $thanks_cache_key
 * @param int $cache_lifetime
 *
 * @return array
 */
function get_thanks_list($topic_id, string $thanks_cache_key, int $cache_lifetime): array
{
    if (!$cached_thanks = CACHE('bb_cache')->get($thanks_cache_key)) {
        $cached_thanks = [];
        $sql = DB()->fetch_rowset('SELECT u.username, u.user_rank, u.user_id, thx.* FROM ' . BB_THX . ' thx, ' . BB_USERS . " u WHERE thx.topic_id = $topic_id AND thx.user_id = u.user_id");

        foreach ($sql as $row) {
            $cached_thanks[$row['user_id']] = [
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'user_rank' => $row['user_rank'],
                'time' => $row['time']
            ];
        }

        if (!empty($cached_thanks)) {
            CACHE('bb_cache')->set($thanks_cache_key, $cached_thanks, $cache_lifetime);
        }
    }

    return $cached_thanks;
}

switch ($mode) {
    case 'add':
        if (IS_GUEST) {
            $this->ajax_die(__('NEED_TO_LOGIN_FIRST'));
        }

        if ($poster_id == userdata('user_id')) {
            $this->ajax_die(__('LIKE_OWN_POST'));
        }

        $cached_thanks = get_thanks_list($topic_id, $thanks_cache_key, $cache_lifetime);
        if (isset($cached_thanks[userdata('user_id')])) {
            $this->ajax_die(__('LIKE_ALREADY'));
        }

        $columns = 'topic_id, user_id, time';
        $values = "$topic_id, " . userdata('user_id') . ", " . TIMENOW;
        DB()->query('INSERT IGNORE INTO ' . BB_THX . " ($columns) VALUES ($values)");

        $cached_thanks[userdata('user_id')] = [
            'user_id' => userdata('user_id'),
            'username' => userdata('username'),
            'user_rank' => userdata('user_rank'),
            'time' => TIMENOW
        ];

        // Limit voters per topic
        $tor_thank_limit_per_topic = (int)config()->get('tor_thank_limit_per_topic');
        if ($tor_thank_limit_per_topic > 0) {
            $thanks_count = count($cached_thanks);
            if ($thanks_count > $tor_thank_limit_per_topic) {
                $oldest_user_id = null;
                foreach ($cached_thanks as $user_id => $thanks_data) {
                    // First value
                    $oldest_user_id = $thanks_data['user_id'];
                    break;
                }

                if ($oldest_user_id) {
                    DB()->query('DELETE FROM ' . BB_THX . " WHERE topic_id = $topic_id AND user_id = $oldest_user_id LIMIT 1");
                    unset($cached_thanks[$oldest_user_id]);
                }
            }
        }

        if (!empty($cached_thanks)) {
            CACHE('bb_cache')->set($thanks_cache_key, $cached_thanks, $cache_lifetime);
        }
        break;
    case 'get':
        if (IS_GUEST && !config()->get('tor_thanks_list_guests')) {
            $this->ajax_die(__('NEED_TO_LOGIN_FIRST'));
        }

        $cached_thanks = get_thanks_list($topic_id, $thanks_cache_key, $cache_lifetime);
        $user_list = [];
        foreach ($cached_thanks as $row) {
            $user_list[] = '<b>' . profile_url($row) . ' <i>(' . bb_date($row['time']) . ')</i></b>';
        }

        $this->response['html'] = implode(', ', $user_list) ?: __('NO_LIKES');
        break;
    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}

$this->response['mode'] = $mode;
