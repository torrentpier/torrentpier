<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $datastore, $lang;

$ranks = $datastore->get('ranks');
$rank_id = (int)$this->request['rank_id'];

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

if ($rank_id != 0 && !isset($ranks[$rank_id])) {
    $this->ajax_die("invalid rank_id: $rank_id");
}

DB()->query("UPDATE " . BB_USERS . " SET user_rank = $rank_id WHERE user_id = $user_id");

\TorrentPier\Sessions::cache_rm_user_sessions($user_id);

$user_rank = ($rank_id) ? '<span class="' . $ranks[$rank_id]['rank_style'] . '">' . $ranks[$rank_id]['rank_title'] . '</span>' : '';

$this->response['html'] = ($rank_id) ? $lang['AWARDED_RANK'] . "<b> $user_rank </b>" : $lang['SHOT_RANK'];
$this->response['rank_name'] = ($rank_id) ? $user_rank : $lang['USER'];
