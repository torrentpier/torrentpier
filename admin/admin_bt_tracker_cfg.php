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
    if (IS_SUPER_ADMIN) {
        $module['TP']['TRACKER_CONFIG'] = basename(__FILE__);
    }
    return;
}
require('./pagestart.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

if (!IS_SUPER_ADMIN) {
    bb_die($lang['NOT_ADMIN']);
}

require(INC_DIR . 'functions_admin_torrent.php');

$submit = isset($_POST['submit']);
$confirmed = isset($_POST['confirm']);

/**
 * All config names with default values
 */
$default_cfg_str = array(
    'off_reason' => 'Tracker is disabled',
    'browser_redirect_url' => 'http://demo.torrentpier.me/',
);

$default_cfg_bool = array(
    'autoclean' => 1,
    'off' => 0,
    'compact_mode' => 1,
    'update_dlstat' => 1,
    'limit_active_tor' => 0,
    'limit_concurrent_ips' => 0,
    'retracker' => 1,
);

$default_cfg_num = array(
    'numwant' => 50,
    'expire_factor' => 4,
    'limit_seed_count' => 20,
    'limit_leech_count' => 4,
    'leech_expire_factor' => 60,
    'limit_seed_ips' => 0,
    'limit_leech_ips' => 0,
);

/**
 * Set template vars
 */
set_tpl_vars($default_cfg_str, $tr_cfg);
set_tpl_vars_lang($default_cfg_str);

set_tpl_vars_bool($default_cfg_bool, $tr_cfg);
set_tpl_vars_lang($default_cfg_bool);

set_tpl_vars($default_cfg_num, $tr_cfg);
set_tpl_vars_lang($default_cfg_num);

$template->assign_vars(array(
    'IGNORE_REPORTED_IP' => $di->config->get('ignore_reported_ip'),
    'ANNOUNCE_INTERVAL' => $di->config->get('announce_interval'),
    'PASSKEY_KEY' => $di->config->get('passkey_key'),
    'GOLD_SILVER_ENABLED' => $tr_cfg['gold_silver_enabled'],
    'DISABLE_SUBMIT' => true,

    'S_HIDDEN_FIELDS' => '',
    'S_CONFIG_ACTION' => 'admin_bt_tracker_cfg.php',
));

print_page('admin_bt_tracker_cfg.tpl', 'admin');
