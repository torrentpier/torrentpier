<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module[APP_NAME]['FORUM_CONFIG'] = basename(__FILE__);

    return;
}

require __DIR__ . '/pagestart.php';

$max_forum_name_len = 30;
$max_forum_rows = 25;

$submit = request()->post->has('submit');
$confirm = request()->getBool('confirm');

$cfg = [];

/**
 * All config names with default values
 */
$default_cfg_str = [
    'bt_announce_url' => 'https://torrentpier.duckdns.org/bt/',
];

$default_cfg_bool = [
    'bt_disable_dht' => 1,
    'bt_show_peers' => 1,
    'bt_add_auth_key' => 1,
    'bt_show_dl_list' => 0,
    'bt_dl_list_only_1st_page' => 1,
    'bt_dl_list_only_count' => 1,
    'bt_replace_ann_url' => 1,
    'bt_show_ip_only_moder' => 1,
    'bt_show_port_only_moder' => 1,
    'bt_check_announce_url' => 0,
    'bt_show_dl_list_buttons' => 1,
    'bt_show_dl_but_will' => 1,
    'bt_show_dl_but_down' => 0,
    'bt_show_dl_but_compl' => 1,
    'bt_show_dl_but_cancel' => 1,
    'bt_show_dl_stat_on_index' => 1,
    'bt_newtopic_auto_reg' => 1,
    'bt_search_bool_mode' => 1,
    'bt_allow_spmode_change' => 1,
    'bt_del_addit_ann_urls' => 1,
    'bt_set_dltype_on_tor_reg' => 1,
    'bt_unset_dltype_on_tor_unreg' => 1,
];

$default_cfg_num = [
    'bt_show_peers_mode' => SHOW_PEERS_COUNT,
];

$default_cfg = array_merge($default_cfg_str, $default_cfg_bool, $default_cfg_num);

$db_fields_bool = [
    'allow_reg_tracker' => 0,  // Allowed forums for registering torrents on tracker
    'allow_porno_topic' => 0,  // Allowed forums for porno topics
    'self_moderated' => 0,  // Users can move theirs topic to another forum
];

/**
 * Get config
 */
$cfg = bb_get_config(BB_CONFIG, true, false);

/**
 * Submit new config
 */
if ($submit && $confirm) {
    foreach ($db_fields_bool as $field_name => $field_def_val) {
        TorrentPier\Legacy\Admin\Torrent::update_table_bool(BB_FORUMS, 'forum_id', $field_name, $field_def_val);
    }

    TorrentPier\Legacy\Admin\Torrent::update_config_table(BB_CONFIG, $default_cfg_str, $cfg, 'str');
    TorrentPier\Legacy\Admin\Torrent::update_config_table(BB_CONFIG, $default_cfg_bool, $cfg, 'bool');
    TorrentPier\Legacy\Admin\Torrent::update_config_table(BB_CONFIG, $default_cfg_num, $cfg, 'num');

    forum_tree(refresh: true);

    bb_die(__('CONFIG_UPD') . '<br /><br />' . sprintf(__('RETURN_CONFIG'), '<a href="admin_bt_forum_cfg.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));
}

// Set template vars
TorrentPier\Legacy\Admin\Torrent::set_tpl_vars($default_cfg_str, $cfg);
TorrentPier\Legacy\Admin\Torrent::set_tpl_vars_bool($default_cfg_bool, $cfg);
TorrentPier\Legacy\Admin\Torrent::set_tpl_vars($default_cfg_num, $cfg);

// Get Forums list
$sql = 'SELECT f.*
	FROM ' . BB_CATEGORIES . ' c, ' . BB_FORUMS . ' f
	WHERE f.cat_id = c.cat_id
	ORDER BY c.cat_order, f.forum_order';

if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not obtain forum names');
}

$rowset = DB()->sql_fetchrowset($result);
$forum_rows = min($max_forum_rows, count($rowset));

foreach ($db_fields_bool as $field_name => $field_def_val) {
    ${$field_name} = '';
}

foreach ($rowset as $rid => $forum) {
    foreach ($db_fields_bool as $field_name => $field_def_val) {
        $forum_name = $forum['forum_name'];
        $selected = $forum[$field_name] ? ' selected' : '';

        $forum_name = str_short($forum_name, $max_forum_name_len);

        ${$field_name} .= '<option value="' . $forum['forum_id'] . '" ' . $selected . '>&nbsp;' . ($forum['forum_parent'] ? HTML_SF_SPACER : '') . htmlCHR($forum_name) . "</option>\n";
    }
}

foreach ($db_fields_bool as $field_name => $field_def_val) {
    ${$field_name} = '<select name="' . $field_name . "[]\" multiple size=\"{$forum_rows}\">" . ${$field_name} . '</select>';
    template()->assign_vars(['S_' . strtoupper($field_name) => ${$field_name}]);
}

template()->assign_vars([
    'L_BT_SHOW_PEERS_MODE_COUNT' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_COUNT) ? '<u>' . __('BT_SHOW_PEERS_MODE_COUNT') . '</u>' : __('BT_SHOW_PEERS_MODE_COUNT'),
    'L_BT_SHOW_PEERS_MODE_NAMES' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_NAMES) ? '<u>' . __('BT_SHOW_PEERS_MODE_NAMES') . '</u>' : __('BT_SHOW_PEERS_MODE_NAMES'),
    'L_BT_SHOW_PEERS_MODE_FULL' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_FULL) ? '<u>' . __('BT_SHOW_PEERS_MODE_FULL') . '</u>' : __('BT_SHOW_PEERS_MODE_FULL'),

    'BT_SHOW_PEERS_MODE_COUNT_VAL' => SHOW_PEERS_COUNT,
    'BT_SHOW_PEERS_MODE_NAMES_VAL' => SHOW_PEERS_NAMES,
    'BT_SHOW_PEERS_MODE_FULL_VAL' => SHOW_PEERS_FULL,

    'BT_SHOW_PEERS_MODE_COUNT_SEL' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_COUNT) ? HTML_CHECKED : '',
    'BT_SHOW_PEERS_MODE_NAMES_SEL' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_NAMES) ? HTML_CHECKED : '',
    'BT_SHOW_PEERS_MODE_FULL_SEL' => ($cfg['bt_show_peers_mode'] == SHOW_PEERS_FULL) ? HTML_CHECKED : '',

    'S_HIDDEN_FIELDS' => '',
    'S_CONFIG_ACTION' => 'admin_bt_forum_cfg.php',
]);

print_page('admin_bt_forum_cfg.tpl', 'admin');
