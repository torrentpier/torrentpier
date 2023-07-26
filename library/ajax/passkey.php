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
$mode = (string)$this->request['mode'];

if ($req_uid == $userdata['user_id'] || IS_ADMIN) {
    switch ($mode) {
        case 'create':
            $first_creation = !\TorrentPier\Legacy\Torrent::getPasskey($req_uid);

            if (empty($this->request['confirmed']) && !$first_creation) {
                $this->prompt_for_confirm($lang['BT_GEN_PASSKEY_NEW']);
            }

            if (!$passkey = \TorrentPier\Legacy\Torrent::generate_passkey($req_uid, IS_ADMIN)) {
                $this->ajax_die('Could not insert passkey');
            }

            \TorrentPier\Legacy\Torrent::tracker_rm_user($req_uid);

            $this->response['first_creation'] = $first_creation;
            $this->response['passkey'] = $passkey;
            break;
        case 'remove':
            if (empty($this->request['confirmed'])) {
                $this->prompt_for_confirm($lang['QUESTION']);
            }

            if (!$delete = \TorrentPier\Legacy\Torrent::deletePasskey($req_uid)) {
                $this->ajax_die('Could not remove passkey');
            }

            $this->response['passkey_removed'] = $delete;
            break;
        default:
            $this->ajax_die('Invalid mode');
    }
} else {
    $this->ajax_die($lang['NOT_AUTHORISED']);
}
