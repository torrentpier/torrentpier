<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Don't count on forbidden extensions table, because it is not allowed to allow forbidden extensions at all
$extensions = DB()->fetch_rowset("
	SELECT
	  e.extension, g.cat_id, g.download_mode, g.upload_icon
	FROM
	  " . BB_EXTENSIONS . " e,
	  " . BB_EXTENSION_GROUPS . " g
	WHERE
	      e.group_id = g.group_id
	  AND g.allow_group = 1
");

$this->store('attach_extensions', $extensions);
