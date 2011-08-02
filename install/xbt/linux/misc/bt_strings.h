#pragma once

enum
{
	bti_choke ,
	bti_unchoke,
	bti_interested,
	bti_uninterested,
	bti_have,
	bti_bitfield,
	bti_request,
	bti_piece,
	bti_cancel,

	bti_get_info,
	bti_info,
	bti_get_peers,
	bti_peers,

	bti_extended = 20,

	bti_bvalue = 0x40,
};

enum
{
	bti_extended_handshake,
	bti_extended_ut_pex,
};

enum
{
	bti_none,
	bti_completed,
	bti_started,
	bti_stopped,
};

const std::string bts_action = "action";
const std::string bts_admin_port = "admin port";
const std::string bts_admin_user = "admin user";
const std::string bts_admin_pass = "admin pass";
const std::string bts_announce = "announce";
const std::string bts_announce_list = "announce-list";
const std::string bts_banned_client = "access denied, banned client";
const std::string bts_can_not_leech = "access denied, leeching forbidden, you are only allowed to seed";
const std::string bts_close_torrent = "close torrent";
const std::string bts_complete = "complete";
const std::string bts_complete_total = "complete total";
const std::string bts_completed_at = "completed at";
const std::string bts_completes_dir = "completes dir";
const std::string bts_down_rate = "down rate";
const std::string bts_downloaded = "downloaded";
const std::string bts_erase_torrent = "erase torrent";
const std::string bts_events = "events";
const std::string bts_failure_reason = "failure reason";
const std::string bts_files = "files";
const std::string bts_flags = "flags";
const std::string bts_get_options = "get options";
const std::string bts_get_status = "get status";
const std::string bts_hash = "hash";
const std::string bts_incomplete = "incomplete";
const std::string bts_incomplete_total = "incomplete total";
const std::string bts_incompletes_dir = "incompletes dir";
const std::string bts_info = "info";
const std::string bts_interval = "interval";
const std::string bts_ipa = "ip";
const std::string bts_left = "left";
const std::string bts_length = "length";
const std::string bts_login = "login";
const std::string bts_merkle_hash = "merkle hash";
const std::string bts_message = "message";
const std::string bts_min_interval = "min interval";
const std::string bts_min_request_interval = "min_request_interval";
const std::string bts_name = "name";
const std::string bts_open_torrent = "open torrent";
const std::string bts_pass = "pass";
const std::string bts_path = "path";
const std::string bts_peer_id = "peer id";
const std::string bts_peer_limit = "peer limit";
const std::string bts_peer_port = "peer port";
const std::string bts_peers = "peers";
const std::string bts_peers_limit_reached = "access denied, peers limit reached";
const std::string bts_piece_length = "piece length";
const std::string bts_pieces = "pieces";
const std::string bts_private = "private";
const std::string bts_port = "port";
const std::string bts_priority = "priority";
const std::string bts_seeding_ratio = "seeding ratio";
const std::string bts_set_options = "set options";
const std::string bts_set_priority = "set priority";
const std::string bts_set_state = "set state";
const std::string bts_size = "size";
const std::string bts_started_at = "started at";
const std::string bts_state = "state";
const std::string bts_time = "time";
const std::string bts_torrent = "torrent";
const std::string bts_torrent_limit = "torrent limit";
const std::string bts_torrents_dir = "torrents dir";
const std::string bts_torrents_limit_reached = "access denied, torrents limit reached";
const std::string bts_total_downloaded = "total downloaded";
const std::string bts_total_uploaded = "total uploaded";
const std::string bts_tracker_port = "tracker port";
const std::string bts_unregistered_ipa = "unregistered IP address";
const std::string bts_unregistered_torrent = "unregistered torrent";
const std::string bts_unregistered_torrent_pass = "unregistered torrent pass";
const std::string bts_unsupported_tracker_protocol = "unsupported tracker protocol, please upgrade your client";
const std::string bts_up_rate = "up rate";
const std::string bts_upload_rate = "upload rate";
const std::string bts_upload_slots = "upload slots";
const std::string bts_user_agent = "user agent";
const std::string bts_version = "version";
const std::string bts_wait_time = "access denied, wait time in effect";
const std::string bts_disabled = "access denied, account disabled";