<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $lang;

$req_uid = (int)$this->request['user_id'];

if ($req_uid == $userdata['user_id'] || IS_ADMIN) {
    if (empty($this->request['confirmed']) && \TorrentPier\Legacy\Torrent::getPasskey($req_uid)) {
        $this->prompt_for_confirm($lang['BT_GEN_PASSKEY_NEW']);
    }

    if (!$passkey = \TorrentPier\Legacy\Torrent::generate_passkey($req_uid, IS_ADMIN)) {
        $this->ajax_die('Could not insert passkey');
    }

    \TorrentPier\Legacy\Torrent::tracker_rm_user($req_uid);

    $this->response['passkey'] = $passkey;
} else {
    $this->ajax_die($lang['NOT_AUTHORISED']);
}
