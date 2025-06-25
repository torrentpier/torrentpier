<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('IN_AJAX')) {
    exit(basename(__FILE__));
}

global $userdata, $lang;

if (!$mode = (string) $this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

if (!$req_uid = (int) $this->request['user_id']) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!IS_ADMIN && $req_uid != $userdata['user_id']) {
    $this->ajax_die($lang['NOT_AUTHORISED']);
}

switch ($mode) {
    case 'generate':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['BT_GEN_PASSKEY_NEW']);
        }

        if (!$passkey = \TorrentPier\Legacy\Torrent::generate_passkey($req_uid, IS_ADMIN)) {
            $this->ajax_die('Could not insert passkey');
        }

        \TorrentPier\Legacy\Torrent::tracker_rm_user($req_uid);
        $this->response['passkey'] = $passkey;
        break;
    default:
        $this->ajax_die('Invalid mode: '.$mode);
}
