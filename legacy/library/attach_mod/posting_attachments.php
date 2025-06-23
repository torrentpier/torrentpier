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

/**
 * Entry Point
 */
function execute_posting_attachment_handling()
{
    global $attachment_mod;

    $attachment_mod['posting'] = new TorrentPier\Legacy\AttachPosting();
    $attachment_mod['posting']->posting_attachment_mod();
}
