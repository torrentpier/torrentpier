<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

if (!$req_uid = (int)$this->request['user_id']) {
    $this->ajax_die(__('NO_USER_ID_SPECIFIED'));
}

if (!IS_ADMIN && $req_uid != userdata('user_id')) {
    $this->ajax_die(__('NOT_AUTHORISED'));
}

switch ($mode) {
    case 'generate':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('BT_GEN_PASSKEY_NEW'));
        }

        if (!$passkey = TorrentPier\Torrent\Passkey::generate($req_uid, IS_ADMIN)) {
            $this->ajax_die('Could not insert passkey');
        }

        TorrentPier\Tracker\Peers::removeByUser($req_uid);
        $this->response['passkey'] = $passkey;
        break;
    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}
