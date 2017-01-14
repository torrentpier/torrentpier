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

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require(BB_ROOT . 'common.php');

if (!$tr_cfg['scrape']) {
    msg_die('Please disable SCRAPE!');
}

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash'])) {
    $_GET['info_hash'] = $_GET['?info_hash'];
}

if (!isset($_GET['info_hash']) || strlen($_GET['info_hash']) != 20) {
    msg_die('Invalid info_hash');
}

$info_hash = $_GET['info_hash'];

function msg_die($msg)
{
    if (DBG_LOG) {
        dbg_log(' ', '!die-' . clean_filename($msg));
    }

    $output = \Rych\Bencode\Bencode::encode([
        'min interval' => (int)1800,
        'failure reason' => (string)$msg,
        'warning message' => (string)$msg,
    ]);

    die($output);
}

define('TR_ROOT', './');
require(TR_ROOT . 'includes/init_tr.php');

$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');

$row = DB()->fetch_row("
		SELECT tor.complete_count, snap.seeders, snap.leechers
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
");

$output['files'][$info_hash] = array(
    'complete' => (int)$row['seeders'],
    'downloaded' => (int)$row['complete_count'],
    'incomplete' => (int)$row['leechers'],
);

echo \Rych\Bencode\Bencode::encode($output);

tracker_exit();
exit;
