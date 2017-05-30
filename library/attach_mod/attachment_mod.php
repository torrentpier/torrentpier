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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

require ATTACH_DIR . '/includes/functions_includes.php';
require ATTACH_DIR . '/includes/functions_attach.php';
require ATTACH_DIR . '/includes/functions_delete.php';
require ATTACH_DIR . '/includes/functions_thumbs.php';
require ATTACH_DIR . '/includes/functions_filetypes.php';

if (defined('ATTACH_INSTALL')) {
    return;
}

/**
 * wrapper function for determining the correct language directory
 */
function attach_mod_get_lang($language_file)
{
    global $attach_config, $bb_cfg;

    $file = LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'] . '/' . $language_file . '.php';
    if (file_exists($file)) {
        return $bb_cfg['default_lang'];
    } else {
        $file = LANG_ROOT_DIR . '/' . $attach_config['board_lang'] . '/' . $language_file . '.php';
        if (file_exists($file)) {
            return $attach_config['board_lang'];
        }
    }

    bb_die('Attachment mod language file does not exist: language/' . $attach_config['board_lang'] . '/' . $language_file . '.php');
}

/**
 * Get attachment mod configuration
 */
function get_config()
{
    global $bb_cfg;

    $attach_config = array();

    $sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query attachment information');
    }

    while ($row = DB()->sql_fetchrow($result)) {
        $attach_config[$row['config_name']] = trim($row['config_value']);
    }

    // We assign the original default board language here, because it gets overwritten later with the users default language
    $attach_config['board_lang'] = trim($bb_cfg['default_lang']);

    return $attach_config;
}

// Get Attachment Config
$attach_config = array();

if (!$attach_config = CACHE('bb_cache')->get('attach_config')) {
    $attach_config = get_config();
    CACHE('bb_cache')->set('attach_config', $attach_config, 86400);
}

include ATTACH_DIR . '/displaying.php';
include ATTACH_DIR . '/posting_attachments.php';

$upload_dir = $attach_config['upload_dir'];
