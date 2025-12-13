<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!empty($setmodules)) {
    $module['GENERAL']['CONFIGURATION'] = basename(__FILE__) . '?mode=config';
    $module['MODS']['CONFIGURATION'] = basename(__FILE__) . '?mode=config_mods';

    return;
}

require __DIR__ . '/pagestart.php';

$mode = request()->query->get('mode', '');

$return_links = [
    'index' => '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'),
    'config' => '<br /><br />' . sprintf(__('CLICK_RETURN_CONFIG'), '<a href="admin_board.php?mode=config">', '</a>'),
    'config_mods' => '<br /><br />' . sprintf(__('CLICK_RETURN_CONFIG_MODS'), '<a href="admin_board.php?mode=config_mods">', '</a>'),
];

/**
 * Pull all config data
 */
$sql = 'SELECT * FROM ' . BB_CONFIG;
if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not query config information in admin_board');
} else {
    while ($row = DB()->sql_fetchrow($result)) {
        $config_name = $row['config_name'];
        $config_value = $row['config_value'];
        $default_config[$config_name] = $config_value;

        // These config values are arrays in the form (stored serialized in DB)
        $arrayConfigs = ['seed_bonus_points', 'seed_bonus_release', 'bonus_upload', 'bonus_upload_price'];

        if (in_array($config_name, $arrayConfigs)) {
            // Unserialize DB value for display, use POST array on submit
            $dbValue = [];
            if ($config_value !== '' && is_string($config_value) && str_starts_with($config_value, 'a:')) {
                $unserialized = unserialize($config_value, ['allowed_classes' => false]);
                if (is_array($unserialized)) {
                    $dbValue = $unserialized;
                }
            }
            $new[$config_name] = request()->post->has('submit')
                ? request()->getArray($config_name, $dbValue)
                : $dbValue;
        } else {
            $new[$config_name] = request()->post->get($config_name, $default_config[$config_name]);
        }

        if (request()->post->has('submit') && $row['config_value'] != $new[$config_name]) {
            $valueToSave = $new[$config_name];
            if (in_array($config_name, $arrayConfigs)) {
                $valueToSave = serialize(str_replace(',', '.', $new[$config_name]));
            }
            bb_update_config([$config_name => $valueToSave]);
        }
    }

    if (request()->post->has('submit')) {
        bb_die(__('CONFIG_UPDATED') . $return_links[$mode] . $return_links['index']);
    }
}

switch ($mode) {
    case 'config_mods':
        template()->assign_vars([
            'S_CONFIG_ACTION' => 'admin_board.php?mode=config_mods',
            'CONFIG_MODS' => true,

            'MAGNET_LINKS_ENABLED' => $new['magnet_links_enabled'],
            'MAGNET_LINKS_FOR_GUESTS' => $new['magnet_links_for_guests'],
            'GENDER' => $new['gender'],
            'CALLSEED' => $new['callseed'],
            'TOR_STATS' => $new['tor_stats'],
            'SHOW_LATEST_NEWS' => $new['show_latest_news'],
            'MAX_NEWS_TITLE' => $new['max_news_title'],
            'LATEST_NEWS_COUNT' => $new['latest_news_count'],
            'LATEST_NEWS_FORUM_ID' => $new['latest_news_forum_id'],
            'SHOW_NETWORK_NEWS' => $new['show_network_news'],
            'MAX_NET_TITLE' => $new['max_net_title'],
            'NETWORK_NEWS_COUNT' => $new['network_news_count'],
            'NETWORK_NEWS_FORUM_ID' => $new['network_news_forum_id'],
            'WHOIS_INFO' => $new['whois_info'],
            'SHOW_MOD_INDEX' => $new['show_mod_index'],
            'SHOW_BOARD_START_INDEX' => $new['show_board_start_index'],
            'BIRTHDAY_ENABLED' => $new['birthday_enabled'],
            'BIRTHDAY_MAX_AGE' => $new['birthday_max_age'],
            'BIRTHDAY_MIN_AGE' => $new['birthday_min_age'],
            'BIRTHDAY_CHECK_DAY' => $new['birthday_check_day'],
            'PREMOD' => $new['premod'],
            'TOR_COMMENT' => $new['tor_comment'],
            'SEED_BONUS_ENABLED' => $new['seed_bonus_enabled'],
            'SEED_BONUS_TOR_SIZE' => $new['seed_bonus_tor_size'],
            'SEED_BONUS_USER_REGDATE' => $new['seed_bonus_user_regdate'],
        ]);

        if ($new['seed_bonus_points'] && $new['seed_bonus_release']) {
            foreach ($new['seed_bonus_points'] as $i => $row) {
                if (!$row || empty($new['seed_bonus_release'][$i])) {
                    continue;
                }

                template()->assign_block_vars('seed_bonus', [
                    'RELEASE' => $new['seed_bonus_release'][$i],
                    'POINTS' => $row,
                ]);
            }
        }

        if ($new['bonus_upload'] && $new['bonus_upload_price']) {
            foreach ($new['bonus_upload'] as $i => $row) {
                if (!$row || empty($new['bonus_upload_price'][$i])) {
                    continue;
                }

                template()->assign_block_vars('bonus_upload', [
                    'UP' => $row,
                    'PRICE' => $new['bonus_upload_price'][$i],
                ]);
            }
        }
        break;

    default:
        template()->assign_vars([
            'S_CONFIG_ACTION' => 'admin_board.php?mode=config',
            'CONFIG' => true,

            'SITENAME' => htmlCHR($new['sitename']),
            'CONFIG_SITE_DESCRIPTION' => htmlCHR($new['site_desc']),
            'DISABLE_BOARD' => (bool)$new['board_disable'],
            'ALLOW_AUTOLOGIN' => (bool)$new['allow_autologin'],
            'AUTOLOGIN_TIME' => (int)$new['max_autologin_time'],
            'MAX_POLL_OPTIONS' => $new['max_poll_options'],
            'FLOOD_INTERVAL' => $new['flood_interval'],
            'TOPICS_PER_PAGE' => $new['topics_per_page'],
            'POSTS_PER_PAGE' => $new['posts_per_page'],
            'HOT_TOPIC' => $new['hot_threshold'],
            'DEFAULT_DATEFORMAT' => $new['default_dateformat'],
            'LANG_SELECT' => TorrentPier\Legacy\Common\Select::language($new['default_lang'], 'default_lang'),
            'TIMEZONE_SELECT' => TorrentPier\Legacy\Common\Select::timezone($new['board_timezone'], 'board_timezone'),
            'MAX_LOGIN_ATTEMPTS' => $new['max_login_attempts'],
            'LOGIN_RESET_TIME' => $new['login_reset_time'],
            'PRUNE_ENABLE' => (bool)$new['prune_enable'],
            'ALLOW_BBCODE' => (bool)$new['allow_bbcode'],
            'ALLOW_SMILIES' => (bool)$new['allow_smilies'],
            'ALLOW_SIG' => (bool)$new['allow_sig'],
            'SIG_SIZE' => $new['max_sig_chars'],
            'ALLOW_NAMECHANGE' => (bool)$new['allow_namechange'],
            'SMILIES_PATH' => $new['smilies_path'],
        ]);
        break;
}

print_page('admin_board.tpl', 'admin');
