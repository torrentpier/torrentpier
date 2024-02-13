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

$announce_urls = $additional_announce_urls = [];

// ==============================================================================================================================
// Allowed Announcer URLs
// ------------------------------------------------------------------------------------------------------------------------------
// Examples:
// $announce_urls[] = 'http://demo.torrentpier.com/bt/announce.php';
// $announce_urls[] = 'http://tracker.openbittorrent.com:80/announce';
// $announce_urls[] = 'udp://tracker.openbittorrent.com:6969/announce';
// ------------------------------------------------------------------------------------------------------------------------------
// Note:
// - Add URLs without GET parameters at the end
// - For this file to work, you need to enable the "Check announce url" option in the admin panel in "Forum Settings"
// ==============================================================================================================================
// Additional announcer URLs that will be added to your giveaways
// ------------------------------------------------------------------------------------------------------------------------------
// Examples:
// $additional_announce_urls[] = 'http://tracker.openbittorrent.com:80/announce';
// $additional_announce_urls[] = 'udp://tracker.openbittorrent.com:6969/announce';
// ------------------------------------------------------------------------------------------------------------------------------
// Note:
// - It is better not to add advertisers with GET parameters (for example passkey or another access authenticator)
// - For this file to work, you need to disable the option “Delete all additional announce urls” in the admin panel in “Forum Settings”
// ==============================================================================================================================
