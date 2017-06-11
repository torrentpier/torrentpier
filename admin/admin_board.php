<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!empty($setmodules)) {
    $module['GENERAL']['CONFIGURATION'] = basename(__FILE__) . '?mode=config';
    $module['MODS']['CONFIGURATION'] = basename(__FILE__) . '?mode=config_mods';
    return;
}
require __DIR__ . '/pagestart.php';
require INC_DIR . '/functions_selects.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

$return_links = array(
    'index' => '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'),
    'config' => '<br /><br />' . sprintf($lang['CLICK_RETURN_CONFIG'], '<a href="admin_board.php?mode=config">', '</a>'),
    'config_mods' => '<br /><br />' . sprintf($lang['CLICK_RETURN_CONFIG_MODS'], '<a href="admin_board.php?mode=config_mods">', '</a>')
);

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

        $new[$config_name] = isset($_POST[$config_name]) ? $_POST[$config_name] : $default_config[$config_name];

        if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name]) {
            if ($config_name == 'seed_bonus_points' ||
                $config_name == 'seed_bonus_release' ||
                $config_name == 'bonus_upload' ||
                $config_name == 'bonus_upload_price'
            ) {
                $new[$config_name] = serialize(str_replace(',', '.', $new[$config_name]));
            }
            bb_update_config(array($config_name => $new[$config_name]));
        }
    }

    if (isset($_POST['submit'])) {
        bb_die($lang['CONFIG_UPDATED'] . $return_links[$mode] . $return_links['index']);
    }
}

switch ($mode) {
    case 'config_mods':
        $template->assign_vars(array(
            'S_CONFIG_ACTION' => 'admin_board.php?mode=config_mods',
            'CONFIG_MODS' => true,

            'MAGNET_LINKS_ENABLED' => $new['magnet_links_enabled'],
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
            'BIRTHDAY_ENABLED' => $new['birthday_enabled'],
            'BIRTHDAY_MAX_AGE' => $new['birthday_max_age'],
            'BIRTHDAY_MIN_AGE' => $new['birthday_min_age'],
            'BIRTHDAY_CHECK_DAY' => $new['birthday_check_day'],
            'PREMOD' => $new['premod'],
            'TOR_COMMENT' => $new['tor_comment'],
            'SEED_BONUS_ENABLED' => $new['seed_bonus_enabled'],
            'SEED_BONUS_TOR_SIZE' => $new['seed_bonus_tor_size'],
            'SEED_BONUS_USER_REGDATE' => $new['seed_bonus_user_regdate'],
        ));

        if ($new['seed_bonus_points'] && $new['seed_bonus_release']) {
            $seed_bonus = unserialize($new['seed_bonus_points']);
            $seed_release = unserialize($new['seed_bonus_release']);

            foreach ($seed_bonus as $i => $row) {
                if (!$row || !$seed_release[$i]) {
                    continue;
                }

                $template->assign_block_vars('seed_bonus', array(
                    'RELEASE' => $seed_release[$i],
                    'POINTS' => $row,
                ));
            }
        }

        if ($new['bonus_upload'] && $new['bonus_upload_price']) {
            $upload_row = unserialize($new['bonus_upload']);
            $price_row = unserialize($new['bonus_upload_price']);

            foreach ($upload_row as $i => $row) {
                if (!$row || !$price_row[$i]) {
                    continue;
                }

                $template->assign_block_vars('bonus_upload', array(
                    'UP' => $row,
                    'PRICE' => $price_row[$i],
                ));
            }
        }
        break;

    default:
        $template->assign_vars(array(
            'S_CONFIG_ACTION' => 'admin_board.php?mode=config',
            'CONFIG' => true,

            'SITENAME' => htmlCHR($new['sitename']),
            'CONFIG_SITE_DESCRIPTION' => htmlCHR($new['site_desc']),
            'DISABLE_BOARD' => $new['board_disable'] ? true : false,
            'ALLOW_AUTOLOGIN' => $new['allow_autologin'] ? true : false,
            'AUTOLOGIN_TIME' => (int)$new['max_autologin_time'],
            'MAX_POLL_OPTIONS' => $new['max_poll_options'],
            'FLOOD_INTERVAL' => $new['flood_interval'],
            'TOPICS_PER_PAGE' => $new['topics_per_page'],
            'POSTS_PER_PAGE' => $new['posts_per_page'],
            'HOT_TOPIC' => $new['hot_threshold'],
            'DEFAULT_DATEFORMAT' => $new['default_dateformat'],
            'LANG_SELECT' => language_select($new['default_lang'], 'default_lang'),
            'TIMEZONE_SELECT' => tz_select($new['board_timezone'], 'board_timezone'),
            'MAX_LOGIN_ATTEMPTS' => $new['max_login_attempts'],
            'LOGIN_RESET_TIME' => $new['login_reset_time'],
            'PRUNE_ENABLE' => $new['prune_enable'] ? true : false,
            'ALLOW_BBCODE' => $new['allow_bbcode'] ? true : false,
            'ALLOW_SMILIES' => $new['allow_smilies'] ? true : false,
            'ALLOW_SIG' => $new['allow_sig'] ? true : false,
            'SIG_SIZE' => $new['max_sig_chars'],
            'ALLOW_NAMECHANGE' => $new['allow_namechange'] ? true : false,
            'SMILIES_PATH' => $new['smilies_path'],
        ));
        break;
}

print_page('admin_board.tpl', 'admin');
