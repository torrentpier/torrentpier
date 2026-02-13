<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['USERS']['SPAM_LOG'] = basename(__FILE__);

    return;
}

datastore()->enqueue(['ranks']);

$per_page = 50;
$row_class_1 = 'row1';
$row_class_2 = 'row2';

$url = basename(__FILE__);

// Filter keys
$decision_key = 'decision';
$type_key = 'type';
$provider_key = 'provider';
$ip_key = 'ip';
$sort_key = 'sort';

// Defaults
$sort_asc = 'ASC';
$sort_desc = 'DESC';
$def_sort = $sort_desc;

// Start
$start = request()->getInt('start');
if ($start < 0) {
    $start = abs($start);
}

$db = eloquent();

// Decision filter
$decision_val = '';
$decision_options = [
    __('SPAM_LOG_ALL') => '',
    'Denied' => 'Denied',
    'Moderated' => 'Moderated',
];

if ($var = request()->get($decision_key)) {
    if (in_array($var, ['Denied', 'Moderated'], true)) {
        $decision_val = $var;
        $url = url_arg($url, $decision_key, $decision_val);
    }
}

// Type filter
$type_val = '';
$type_options = [
    __('SPAM_LOG_ALL') => '',
    'registration' => 'registration',
    'content' => 'content',
];

if ($var = request()->get($type_key)) {
    if (in_array($var, ['registration', 'content'], true)) {
        $type_val = $var;
        $url = url_arg($url, $type_key, $type_val);
    }
}

// Provider filter
$provider_val = '';
$provider_names = $db->table('spam_log')
    ->where('provider_name', '!=', '')
    ->distinct()
    ->pluck('provider_name')
    ->sort()
    ->all();

$provider_options = [__('SPAM_LOG_ALL') => ''];
foreach ($provider_names as $name) {
    $provider_options[$name] = $name;
}

if ($var = request()->get($provider_key)) {
    if (isset($provider_options[$var])) {
        $provider_val = $var;
        $url = url_arg($url, $provider_key, $provider_val);
    }
}

// IP filter
$ip_val = '';

if ($var = request()->get($ip_key)) {
    $ip_val = trim($var);
    if ($ip_val !== '') {
        $url = url_arg($url, $ip_key, $ip_val);
    }
}

// Sort
$sort_val = $def_sort;

if (($var = request()->get($sort_key)) && $var != $def_sort) {
    $sort_val = ($var == $sort_asc) ? $sort_asc : $sort_desc;
    $url = url_arg($url, $sort_key, $sort_val);
}

// Query
$query = $db->table('spam_log');

if ($decision_val) {
    $query->where('decision', $decision_val);
}
if ($type_val) {
    $query->where('check_type', $type_val);
}
if ($provider_val) {
    $query->where('provider_name', $provider_val);
}
if ($ip_val !== '') {
    $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $ip_val);
    $query->where('check_ip', 'LIKE', "%{$escaped}%");
}

$query->orderBy('check_time', $sort_val)
    ->offset($start)
    ->limit($per_page + 1);

$log_rowset = $query->get()->toArray();
$log_count = count($log_rowset);

if ($log_count == $per_page + 1) {
    $items_count = $start + ($per_page * 2);
    array_pop($log_rowset);
} else {
    $items_count = $start + $log_count;
}

generate_pagination($url, $items_count, $per_page, $start);

// Summary stats
$stats = $db->table('spam_log')
    ->selectRaw('COUNT(*) AS total, SUM(decision = ?) AS denied, SUM(decision = ?) AS moderated', ['Denied', 'Moderated'])
    ->first();

$colspan = 9;

// Preload user ranks for profile_url coloring
$user_ranks = [];
if ($log_rowset) {
    $user_ids = array_filter(array_unique(array_column($log_rowset, 'user_id')));
    if ($user_ids) {
        $user_ranks = $db->table('users')
            ->whereIn('user_id', $user_ids)
            ->pluck('user_rank', 'user_id')
            ->all();
    }
}

if ($log_rowset) {
    foreach ($log_rowset as $row_num => $row) {
        $row = (array)$row;

        $decision_class = '';
        if ($row['decision'] === 'Denied') {
            $decision_class = 'leechmed';
        } elseif ($row['decision'] === 'Moderated') {
            $decision_class = 'seedmed';
        }

        $details_html = '';
        if (!empty($row['details'])) {
            $decoded = json_decode($row['details'], true);
            if ($decoded !== null) {
                $details_html = htmlCHR(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        $row_class = !($row_num & 1) ? $row_class_1 : $row_class_2;

        $post_link = '';
        if (!empty($row['post_id'])) {
            $post_link = POST_URL . $row['post_id'] . '#' . $row['post_id'];
        }

        $user_profile = '';
        if (!empty($row['user_id'])) {
            $user_profile = profile_url([
                'user_id' => $row['user_id'],
                'username' => $row['check_username'],
                'user_rank' => $user_ranks[(int)$row['user_id']] ?? 0,
            ], true);
        }

        template()->assign_block_vars('log', [
            'ROW_CLASS' => $row_class,
            'CHECK_TYPE' => htmlCHR($row['check_type']),
            'CHECK_IP' => htmlCHR($row['check_ip']),
            'CHECK_EMAIL' => htmlCHR($row['check_email']),
            'CHECK_USERNAME' => htmlCHR($row['check_username']),
            'DECISION' => htmlCHR($row['decision']),
            'DECISION_CLASS' => $decision_class,
            'PROVIDER_NAME' => htmlCHR($row['provider_name']),
            'REASON' => htmlCHR($row['reason']),
            'TOTAL_TIME_MS' => $row['total_time_ms'],
            'CHECK_TIME' => bb_date($row['check_time'], 'd-M-y H:i'),
            'DETAILS' => $details_html,
            'ROW_ID' => $row_num,
            'POST_ID' => (int)$row['post_id'],
            'POST_LINK' => $post_link,
            'USER_PROFILE' => $user_profile,
        ]);
    }
} else {
    template()->assign_block_vars('log_not_found', []);
}

template()->assign_vars([
    'LOG_COLSPAN' => $colspan,

    'SORT_NAME' => $sort_key,
    'SORT_ASC' => $sort_asc,
    'SORT_DESC' => $sort_desc,
    'SORT_ASC_CHECKED' => ($sort_val == $sort_asc) ? HTML_CHECKED : '',
    'SORT_DESC_CHECKED' => ($sort_val == $sort_desc) ? HTML_CHECKED : '',

    'SEL_DECISION' => build_select($decision_key, $decision_options, $decision_val ?: ''),
    'SEL_TYPE' => build_select($type_key, $type_options, $type_val ?: ''),
    'SEL_PROVIDER' => build_select($provider_key, $provider_options, $provider_val ?: ''),

    'IP_NAME' => $ip_key,
    'IP_VAL' => htmlCHR($ip_val),

    'S_SPAM_LOG_ACTION' => basename(__FILE__),

    'STATS_TOTAL' => (int)$stats->total,
    'STATS_DENIED' => (int)$stats->denied,
    'STATS_MODERATED' => (int)$stats->moderated,
]);

print_page('admin_spam_log.tpl', 'admin');
