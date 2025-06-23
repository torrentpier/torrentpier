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

$smilies = [];

$rowset = DB()->fetch_rowset("SELECT * FROM " . BB_SMILIES);
sort($rowset);

foreach ($rowset as $smile) {
    $smilies['orig'][] = '#(?<=^|\W)' . preg_quote($smile['code'], '#') . '(?=$|\W)#';
    $smilies['repl'][] = ' <img class="smile" src="' . tp_config()->get('smilies_path') . '/' . $smile['smile_url'] . '" alt="' . $smile['code'] . '" title="' . $smile['emoticon'] . '" align="absmiddle" border="0" />';
    $smilies['smile'][] = $smile;
}

$this->store('smile_replacements', $smilies);
