<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MODS']['SITEMAP'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';

$sql = 'SELECT * FROM ' . BB_CONFIG;

if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not query config information in admin_sitemap');
} else {
    $new_params = array();

    while ($row = DB()->sql_fetchrow($result)) {
        $config_name = $row['config_name'];
        $config_value = $row['config_value'];
        $default_config[$config_name] = $config_value;
        $new[$config_name] = $_POST[$config_name] ?? $default_config[$config_name];

        if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name]) {
            $new_params[$config_name] = $new[$config_name];
        }
    }

    if (isset($_POST['submit'])) {
        if (!empty($new_params)) {
            bb_update_config($new_params);
        }
    }
}

$s_mess = $lang['SITEMAP_CREATED'] . ': <b>' . bb_date($new['sitemap_time'], $bb_cfg['post_date_format']) . '</b> ' . $lang['SITEMAP_AVAILABLE'] . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
$message = file_exists(SITEMAP_DIR . '/sitemap.xml') ? $s_mess : $lang['SITEMAP_NOT_CREATED'];

$template->assign_vars(array(
    'STATIC_SITEMAP' => $new['static_sitemap'],
    'MESSAGE' => $message,
));

print_page('admin_sitemap.tpl', 'admin');
