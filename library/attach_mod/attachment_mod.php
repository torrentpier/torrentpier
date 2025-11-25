<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

require ATTACH_DIR . '/includes/functions_attach.php';
require ATTACH_DIR . '/includes/functions_delete.php';

if (defined('ATTACH_INSTALL')) {
    return;
}

/**
 * Get attachment mod configuration
 */
function get_config()
{
    $attach_config = [];

    $sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query attachment information');
    }

    while ($row = DB()->sql_fetchrow($result)) {
        $attach_config[$row['config_name']] = trim($row['config_value']);
    }

    // We assign the original default board language here, because it gets overwritten later with the users default language
    $attach_config['board_lang'] = trim(config()->get('default_lang'));

    return $attach_config;
}

// Get Attachment Config
$attach_config = [];

if (!$attach_config = CACHE('bb_cache')->get('attach_config')) {
    $attach_config = get_config();
    CACHE('bb_cache')->set('attach_config', $attach_config, 86400);
}

include ATTACH_DIR . '/displaying.php';
include ATTACH_DIR . '/posting_attachments.php';

$upload_dir = $attach_config['upload_dir'];
