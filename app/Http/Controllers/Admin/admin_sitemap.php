<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MODS']['SITEMAP'] = basename(__FILE__);

    return;
}

require __DIR__ . '/pagestart.php';

$sql = 'SELECT * FROM ' . BB_CONFIG . " WHERE config_name IN('sitemap_time', 'static_sitemap')";

if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not query config information in admin_sitemap');
} else {
    $new_params = [];

    while ($row = DB()->sql_fetchrow($result)) {
        $config_name = $row['config_name'];
        $config_value = $row['config_value'];
        $default_config[$config_name] = $config_value;
        $new[$config_name] = request()->post->get($config_name, $default_config[$config_name]);

        if (request()->post->has('submit') && $row['config_value'] != $new[$config_name]) {
            $new_params[$config_name] = $new[$config_name];
        }
    }

    if (request()->post->has('submit')) {
        // Check for demo mode
        if (IN_DEMO_MODE) {
            bb_die(__('CANT_EDIT_IN_DEMO_MODE'));
        }
        if (!empty($new_params)) {
            bb_update_config($new_params);
        }
    }
}

$s_mess = __('SITEMAP_CREATED') . ': <b>' . bb_date($new['sitemap_time'], config()->get('post_date_format')) . '</b> ' . __('SITEMAP_AVAILABLE') . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
$message = is_file(SITEMAP_DIR . '/sitemap.xml') ? $s_mess : __('SITEMAP_NOT_CREATED');

template()->assign_vars([
    'STATIC_SITEMAP' => $new['static_sitemap'],
    'MESSAGE' => $message,
]);

print_page('admin_sitemap.tpl', 'admin');
