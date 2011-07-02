<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

function get_torrent_info ($attach_id)
{
	$attach_id = intval($attach_id);

	$sql = "
		SELECT
			a.post_id, d.physical_filename, d.extension, d.tracker_status,
			t.topic_first_post_id,
			p.poster_id, p.topic_id, p.forum_id,
			f.allow_reg_tracker
		FROM
			". BB_ATTACHMENTS      ." a,
			". BB_ATTACHMENTS_DESC ." d,
			". BB_POSTS            ." p,
			". BB_TOPICS           ." t,
			". BB_FORUMS           ." f
		WHERE
			    a.attach_id = $attach_id
			AND d.attach_id = $attach_id
			AND p.post_id = a.post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = p.forum_id
		LIMIT 1
	";

	if (!$torrent = DB()->fetch_row($sql))
	{
		message_die(GENERAL_ERROR, 'Invalid attach_id');
	}

	return $torrent;
}

function torrent_auth_check ($forum_id, $poster_id)
{
	global $userdata, $lang, $attach_config;

	if (IS_ADMIN) return true;

	$is_auth = auth(AUTH_ALL, $forum_id, $userdata);

	if ($poster_id != $userdata['user_id'] && !$is_auth['auth_mod'])
	{
		message_die(GENERAL_MESSAGE, $lang['NOT_MODERATOR'], $lang['NOT_AUTHORISED']);
	}
	else if (!$is_auth['auth_view'] || !$is_auth['auth_attachments'] || $attach_config['disable_mod'])
	{
		$message = sprintf($lang['SORRY_AUTH_READ'], $is_auth['auth_read_type']);
		message_die(GENERAL_MESSAGE, $message);
	}
	return $is_auth;
}

function tracker_unregister ($attach_id, $mode = '')
{
	global $lang, $bb_cfg;

	$attach_id = (int) $attach_id;
	$post_id = $topic_id = $forum_id = null;

	// Get torrent info
	if ($torrent = get_torrent_info($attach_id))
	{
		$post_id  = $torrent['post_id'];
		$topic_id = $torrent['topic_id'];
		$forum_id = $torrent['forum_id'];
	}

	if ($mode == 'request')
	{
		if (!$torrent)
		{
			message_die(GENERAL_ERROR, 'Torrent not found');
		}
		if (!$torrent['tracker_status'])
		{
			message_die(GENERAL_ERROR, 'Torrent already unregistered');
		}
		torrent_auth_check($forum_id, $torrent['poster_id']);
	}

	if (!$topic_id)
	{
		$sql = "SELECT topic_id
			FROM ". BB_BT_TORRENTS ."
			WHERE attach_id = $attach_id";

		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not query torrent information', '', __LINE__, __FILE__, $sql);
		}
		if ($row = DB()->sql_fetchrow($result))
		{
			$topic_id = $row['topic_id'];
		}
	}

	// Unset DL-Type for topic
	if ($bb_cfg['bt_unset_dltype_on_tor_unreg'] && $topic_id)
	{
		$sql = "UPDATE ". BB_TOPICS ." SET
				topic_dl_type = ". TOPIC_DL_TYPE_NORMAL ."
			WHERE topic_id = $topic_id
			LIMIT 1";

		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not update topics table', '', __LINE__, __FILE__, $sql);
		}
	}

    // XBTT
    if($bb_cfg['announce_type'] == 'xbt')
    {
		$sql = 'INSERT INTO '. BB_BT_TORRENTS ."_del(topic_id, info_hash)
			SELECT topic_id, info_hash
			FROM ". BB_BT_TORRENTS ."
			WHERE attach_id = $attach_id ON DUPLICATE KEY UPDATE is_del=1";

		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not delete torrent from torrents table', '', __LINE__, __FILE__, $sql);
		}
	}
	// XBTT end.

	// Remove peers from tracker
	$sql = "DELETE FROM ". BB_BT_TRACKER ."
		WHERE topic_id = $topic_id";

	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not delete peers', '', __LINE__, __FILE__, $sql);
	}

	// Delete torrent
	$sql = "DELETE FROM ". BB_BT_TORRENTS ."
		WHERE attach_id = $attach_id";

	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not delete torrent from torrents table', '', __LINE__, __FILE__, $sql);
	}

	// Update tracker_status
	$sql = "UPDATE ". BB_ATTACHMENTS_DESC ." SET
			tracker_status = 0
		WHERE attach_id = $attach_id
		LIMIT 1";

	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not update torrent status', '', __LINE__, __FILE__, $sql);
	}

	if ($mode == 'request')
	{
		exit_redirect($lang['BT_UNREGISTERED'], $post_id, $forum_id);
	}
}

function delete_torrent ($attach_id, $mode = '')
{
	global $lang, $userdata;
	global $reg_mode, $topic_id;

	$attach_id = intval($attach_id);
	$reg_mode = $mode;

	if (!$torrent = get_torrent_info($attach_id))
	{
		message_die(GENERAL_ERROR, 'Torrent not found');
	}

	$post_id   = $torrent['post_id'];
	$topic_id  = $torrent['topic_id'];
	$forum_id  = $torrent['forum_id'];
	$poster_id = $torrent['poster_id'];

	if ($torrent['extension'] !== TORRENT_EXT)
	{
		message_die(GENERAL_ERROR, $lang['NOT_TORRENT']);
	}

	torrent_auth_check($forum_id, $torrent['poster_id']);
	tracker_unregister($attach_id);
	delete_attachment(0, $attach_id);

	return;
}

function change_tor_status ($attach_id, $new_tor_status)
{
	global $topic_id, $userdata;

	$attach_id = (int) $attach_id;
	$new_tor_status = (int) $new_tor_status;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die('Torrent not found');
	}

	$topic_id = $torrent['topic_id'];

	torrent_auth_check($torrent['forum_id'], $torrent['poster_id']);

	DB()->query("
		UPDATE ". BB_BT_TORRENTS ." SET
			tor_status = $new_tor_status,
			checked_user_id = {$userdata['user_id']},
			checked_time = '". TIMENOW ."'
		WHERE attach_id = $attach_id
		LIMIT 1
	");
}

// Set gold/silver type for torrent
function change_tor_type ($attach_id, $tor_status_gold)
{
	global $topic_id, $lang, $bb_cfg;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die('Torrent not found');
	}

	if (!(IS_MOD || IS_ADMIN))
	{
		bb_die($lang['ONLY_FOR_MOD']);
	}

	$topic_id = $torrent['topic_id'];
	$tor_status_gold = intval($tor_status_gold);
	DB()->query("UPDATE ". BB_BT_TORRENTS ." SET tor_type = $tor_status_gold WHERE topic_id = $topic_id LIMIT 1");

    // XBTT
    if($bb_cfg['announce_type'] == 'xbt')
    {
		$sql = "SELECT CASE tor_type WHEN 1 THEN 0 WHEN 2 THEN 50 ELSE 100 END AS dl_percent, topic_id, info_hash FROM ". BB_BT_TORRENTS ." WHERE topic_id = $topic_id";
		$result = DB()->query($sql);
		$row = DB()->sql_fetchrow($result);
		$sql = "INSERT INTO ". BB_BT_TORRENTS ."_del(is_del, topic_id,info_hash, dl_percent)
			VALUES (0, " .$row['topic_id'] .", '". $row['info_hash'] ."', ". $row['dl_percent'] .") ON DUPLICATE KEY UPDATE dl_percent = values(dl_percent)";
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Error delete_torrents", '', __LINE__, __FILE__, $sql);
		}
	}
}

function tracker_register ($attach_id, $mode = '')
{
	global $template, $attach_config, $bb_cfg, $lang, $return_message;
	global $reg_mode;

	$attach_id = intval($attach_id);
	$reg_mode = $mode;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die('Torrent not found');
	}

	$post_id   = $torrent['post_id'];
	$topic_id  = $torrent['topic_id'];
	$forum_id  = $torrent['forum_id'];
	$poster_id = $torrent['poster_id'];

	if ($torrent['extension'] !== TORRENT_EXT)
	{
		torrent_error_exit($lang['NOT_TORRENT']);
	}

	if (!$torrent['allow_reg_tracker'])
	{
		torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
	}

	if ($post_id != $torrent['topic_first_post_id'])
	{
		torrent_error_exit($lang['ALLOWED_ONLY_1ST_POST_REG']);
	}

	if ($torrent['tracker_status'])
	{
		torrent_error_exit($lang['ALREADY_REG']);
	}

	if ($this_topic_torrents = get_registered_torrents($topic_id, 'topic'))
	{
		torrent_error_exit($lang['ONLY_1_TOR_PER_TOPIC']);
	}

	torrent_auth_check($forum_id, $torrent['poster_id']);

	$filename = get_attachments_dir() .'/'. $torrent['physical_filename'];

	if (!is_file($filename))
	{
		torrent_error_exit('File name error');
	}

	if (!file_exists($filename))
	{
		torrent_error_exit('File not exists');
	}

	if (!$tor = bdecode_file($filename))
	{
		torrent_error_exit('This is not a bencoded file');
	}

	if ($bb_cfg['bt_disable_dht'])
	{
		$tor['info']['private'] = (int) 1;
		//$tor['info']['source']  = (string) make_url('index.php');

		$fp = fopen($filename, 'w+');
		fwrite ($fp, bencode($tor));
		fclose ($fp);
	}

	if ($bb_cfg['bt_check_announce_url'])
	{
		include(INC_DIR .'torrent_announce_urls.php');

		$ann = (@$tor['announce']) ? $tor['announce'] : '';
		$announce_urls['main_url'] = $bb_cfg['bt_announce_url'];

		if (!$ann || !in_array($ann, $announce_urls))
		{
			$msg = sprintf($lang['INVALID_ANN_URL'], htmlspecialchars($ann), $announce_urls['main_url']);
			torrent_error_exit($msg);
		}
	}

	$info = (@$tor['info']) ? $tor['info'] : array();

	if (!@$info['name'] || !@$info['piece length'] || !@$info['pieces'] || strlen($info['pieces']) % 20 != 0)
	{
		torrent_error_exit('Invalid torrent file');
	}

	$info_hash     = pack('H*', sha1(bencode($info)));
	$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
	$info_hash_md5 = md5($info_hash);

	$sql = "SELECT topic_id
		FROM ". BB_BT_TORRENTS ."
		WHERE info_hash = '$info_hash_sql'
		LIMIT 1";

	if (!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not obtain torrent info', '', __LINE__, __FILE__, $sql);
	}
	if ($row = DB()->sql_fetchrow($result))
	{
		$msg = sprintf($lang['BT_REG_FAIL_SAME_HASH'], append_sid(TOPIC_URL . $row['topic_id']));
		torrent_error_exit($msg);
	}

	$totallen = 0;

	if (@$info['length'])
	{
		$totallen = (float) $info['length'];
	}
	else if (@$info['files'] && is_array($info['files']))
	{
		foreach ($info['files'] as $fn => $f)
		{
			$totallen += (float) $f['length'];
		}
	}
	else
	{
		torrent_error_exit('Invalid torrent file');
	}

	$reg_time = TIMENOW;
	$size = sprintf('%.0f', (float) $totallen);

	$columns = ' info_hash,       post_id,  poster_id,  topic_id,  forum_id,  attach_id,   size,   reg_time';
	$values = "'$info_hash_sql', $post_id, $poster_id, $topic_id, $forum_id, $attach_id, '$size', $reg_time";

	$sql = "INSERT INTO ". BB_BT_TORRENTS ." ($columns) VALUES ($values)";

	if (!DB()->sql_query($sql))
	{
		$sql_error = DB()->sql_error();

		if ($sql_error['code'] == 1062) // Duplicate entry
		{
			torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
		}
		message_die(GENERAL_ERROR, 'Could not register torrent on tracker', '', __LINE__, __FILE__, $sql);
	}

	// update tracker status for this attachment
	$sql = 'UPDATE '. BB_ATTACHMENTS_DESC ." SET
			tracker_status = 1
		WHERE attach_id = $attach_id
		LIMIT 1";

	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not update torrent status', '', __LINE__, __FILE__, $sql);
	}

	// set DL-Type for topic
	if ($bb_cfg['bt_set_dltype_on_tor_reg'])
	{
		$sql = 'UPDATE '. BB_TOPICS .' SET
				topic_dl_type = '. TOPIC_DL_TYPE_DL ."
			WHERE topic_id = $topic_id
			LIMIT 1";

		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not update topics table', '', __LINE__, __FILE__, $sql);
		}
	}

	if ($reg_mode == 'request' || $reg_mode == 'newtopic')
	{
		$mess = sprintf($lang['BT_REGISTERED'], append_sid("download.php?id=$attach_id"));
		exit_redirect($mess, $post_id, $forum_id);
	}

	return;
}

function send_torrent_with_passkey ($filename)
{
	global $attachment, $auth_pages, $userdata, $bb_cfg, $lang;

	if (!$bb_cfg['bt_add_auth_key'] || $attachment['extension'] !== TORRENT_EXT || !$size = @filesize($filename))
	{
		return;
	}

	$post_id = $poster_id = $passkey_val = '';
	$user_id = $userdata['user_id'];
	$attach_id = $attachment['attach_id'];

	if (!$passkey_key = $bb_cfg['passkey_key'])
	{
		message_die(GENERAL_ERROR, 'Could not add passkey (wrong config $bb_cfg[\'passkey_key\'])');
	}

	// Get $post_id & $poster_id
	foreach ($auth_pages as $rid => $row)
	{
		if ($row['attach_id'] == $attach_id)
		{
			$post_id = $row['post_id'];
			$poster_id = $row['user_id_1'];
			break;
		}
	}

	if (!$attachment['tracker_status'])
	{
		message_die(GENERAL_ERROR, $lang['PASSKEY_ERR_TOR_NOT_REG']);
	}

	if (bf($userdata['user_opt'], 'user_opt', 'allow_passkey'))
	{
		message_die(GENERAL_ERROR, 'Could not add passkey');
	}

	if ($bt_userdata = get_bt_userdata($user_id))
	{
		$passkey_val = $bt_userdata['auth_key'];
	}

	if (!$passkey_val)
	{
		if ($bb_cfg['bt_gen_passkey_on_reg'])
		{
			if (!$passkey_val = generate_passkey($user_id))
			{
				message_die(GENERAL_ERROR, 'Could not insert passkey', '', __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			$mess = sprintf($lang['PASSKEY_ERR_EMPTY'], append_sid("profile.php?mode=editprofile#bittorrent"));
			message_die(GENERAL_ERROR, $mess);
		}
	}

	// Ratio limits
	$min_ratio = $bb_cfg['bt_min_ratio_allow_dl_tor'];

	if ($min_ratio && $user_id != $poster_id && ($user_ratio = get_bt_ratio($bt_userdata)) !== null)
	{
		if ($user_ratio < $min_ratio && $post_id)
		{
			$dl = DB()->fetch_row("
				SELECT dl.user_status
				FROM ". BB_POSTS ." p
				LEFT JOIN ". BB_BT_DLSTATUS ." dl ON dl.topic_id = p.topic_id AND dl.user_id = $user_id
				WHERE p.post_id = $post_id
				LIMIT 1
			");

			if (!isset($dl['user_status']) || $dl['user_status'] != DL_STATUS_COMPLETE)
			{
				$mess = sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$user_id&amp;dlc=1");
				message_die(GENERAL_ERROR, $mess);
			}
		}
	}

	// Seeding torrents limit
	if ($bb_cfg['max_seeding_torrents'] && IS_USER)
	{
		$seeding = DB()->fetch_row("
			SELECT COUNT(DISTINCT topic_id) AS torrents, SUM(speed_up) AS sum_up
			FROM ". BB_BT_TRACKER ."
			WHERE user_id = $user_id
				AND seeder = 1
		");

		if ($seeding && $seeding['torrents'] > $bb_cfg['max_seeding_torrents'] && $bt_userdata['u_up_total'] < 200*1024*1024*1024)
		{
			if ($seeding['sum_up'] < ($seeding['torrents'] * $bb_cfg['min_up_speed_per_torrent']))
			{
				$msg = array();
				$msg[] = date('m-d H:i:s');
				$msg[] = sprintf('%-30s', html_entity_decode($userdata['username'])." ($user_id)");
				$msg[] = sprintf('%-3s', $seeding['torrents']);
				$msg[] = sprintf('%.2f', @$user_ratio);
				$msg[] = sprintf('%-9s', humn_size($bt_userdata['u_up_total'], '', '', ' '));
				$msg[] = humn_size($seeding['sum_up'], '', '', ' ');
				$msg = join(LOG_SEPR, $msg) . LOG_LF;
				bb_log($msg, 'overseed/current');

				redirect($bb_cfg['too_many_seeding_redirect_url']);
			}
		}
	}

	// Announce URL
	$ann_url = $bb_cfg['bt_announce_url'];

	if (!$tor = bdecode_file($filename))
	{
		message_die(GENERAL_ERROR, 'This is not a bencoded file');
	}

	// XBTT unique passkey
	if($bb_cfg['announce_type'] == 'xbt')
	{
		$info_hash = pack('H*', sha1(bencode($tor['info'])));
		$passkey = substr('00000000'. dechex($userdata['user_id']), -8) . substr(sha1($bb_cfg['torrent_pass_private_key'] .' '. $passkey_val .' '. $userdata['user_id'] .' '. $info_hash), 0, 24);
		$announce = preg_replace('@/a[^/]*$@i', "/$passkey$0", $ann_url);
	}
	else
	{
		$announce = strval($ann_url . "?$passkey_key=$passkey_val&");
	}

	// Replace original announce url with tracker default
	if ($bb_cfg['bt_replace_ann_url'] || !@$tor['announce'])
	{
		$tor['announce'] = $announce;
	}

	// Delete all additional urls
	if ($bb_cfg['bt_del_addit_ann_urls'] || $bb_cfg['bt_disable_dht'])
	{
		unset($tor['announce-list']);
	}
	elseif(@$tor['announce-list'])
	{
		$tor['announce-list'] = array_merge($tor['announce-list'], array(array($announce)));
	}

	// Add publisher & topic url
	$publisher = $bb_cfg['bt_add_publisher'];
	$publisher_url = ($post_id) ? make_url(POST_URL . $post_id) : '';

	if ($publisher)
	{
		$tor['publisher'] = strval($publisher);
		unset($tor['publisher.utf-8']);

		if ($publisher_url)
		{
			$tor['publisher-url'] = strval($publisher_url);
			unset($tor['publisher-url.utf-8']);
		}
	}

	// Add comment
	$comment = '';

	$orig_com = (@$tor['comment']) ? $tor['comment'] : '';

	if ($bb_cfg['bt_add_comment'])
	{
		$comment = $bb_cfg['bt_add_comment'];
	}
	else
	{
		$comment = ($publisher_url) ? $publisher_url : '';
	}

	if ($comment = trim($comment))
	{
		$tor['comment'] = strval($comment);
		unset($tor['comment.utf-8']);
	}

	//Azureus DHT disable
	$tor['azureus_properties'] = array('dht_backup_enable' => intval(0));

	// Send torrent
	$output   = bencode($tor);
	$filename = clean_filename(basename($attachment['real_filename']));
	$mimetype = 'application/x-bittorrent;';
	$charset  = (strpos(USER_AGENT, 'pera') && @$lang['CONTENT_ENCODING']) ? "charset={$lang['CONTENT_ENCODING']};" : '';

#	header("Content-length: ". strlen($output));
	header("Content-Type: $mimetype $charset name=\"$filename\"");
	header("Content-Disposition: attachment; filename=\"$filename\"");

	##### LOG #####
	global $log_ip_resp;

	if (isset($log_ip_resp[USER_IP]) || isset($log_ip_resp[CLIENT_IP]))
	{
		$str = date('H:i:s') . LOG_SEPR . str_compact(ob_get_contents()) . LOG_LF;
		$file = 'sessions/'. date('m-d') .'_{'. USER_IP .'}_'. CLIENT_IP .'_resp';
		bb_log($str, $file);
	}
	### LOG END ###

	bb_exit($output);
}

function generate_passkey ($user_id, $force_generate = false)
{
	global $lang, $sql;

	$user_id = (int) $user_id;

	// Check if user can change passkey
	if (!$force_generate)
	{
		$sql = "SELECT user_opt
			FROM ". BB_USERS ."
			WHERE user_id = $user_id
			LIMIT 1";

		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not query userdata for passkey', '', __LINE__, __FILE__, $sql);
		}
		if ($row = DB()->sql_fetchrow($result))
		{
			if (bf($row['user_opt'], 'user_opt', 'allow_passkey'))
			{
				message_die(GENERAL_MESSAGE, $lang['NOT_AUTHORISED']);
			}
		}
	}

	for ($i=0; $i < 20; $i++)
	{
		$passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);

		// Insert new row
		$sql = "INSERT IGNORE INTO ". BB_BT_USERS ." (user_id, auth_key) VALUES ($user_id, '$passkey_val')";

		if (DB()->sql_query($sql) && DB()->affected_rows() == 1)
		{
			return $passkey_val;
		}
		// Update
		$sql = "UPDATE IGNORE ". BB_BT_USERS ." SET
				auth_key = '$passkey_val'
			WHERE user_id = $user_id
			LIMIT 1";

		if (DB()->sql_query($sql) && DB()->affected_rows() == 1)
		{
			return $passkey_val;
		}
	}
	return false;
}

function tracker_rm_torrent ($topic_id)
{
	return DB()->sql_query("DELETE FROM ". BB_BT_TRACKER ." WHERE topic_id = ". (int) $topic_id);
}

function tracker_rm_user ($user_id)
{
	return DB()->sql_query("DELETE FROM ". BB_BT_TRACKER ." WHERE user_id = ". (int) $user_id);
}

function get_registered_torrents ($id, $mode)
{
	$field = ($mode == 'topic') ? 'topic_id' : 'post_id';

	$sql = "SELECT topic_id
		FROM ". BB_BT_TORRENTS ."
		WHERE $field = $id
		LIMIT 1";

	if (!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not query torrent id', '', __LINE__, __FILE__, $sql);
	}

	if ($rowset = @DB()->sql_fetchrowset($result))
	{
		return $rowset;
	}
	else
	{
		return false;
	}
}

function exit_redirect ($message, $post_id, $forum_id)
{
	global $template, $lang;

	$exit_message = $message .'<br /><br />'. sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="'. append_sid("viewtopic.php?". POST_POST_URL ."=". $post_id) .'#'. $post_id .'">', '</a>') .'<br /><br />'. sprintf($lang['CLICK_RETURN_FORUM'], '<a href="'. append_sid("viewforum.php?". POST_FORUM_URL ."=$forum_id") .'">', '</a>');
	message_die(GENERAL_MESSAGE, $exit_message);
}

function torrent_error_exit ($message)
{
	global $reg_mode, $return_message, $lang;

	$msg = '';
	if (isset($reg_mode) && ($reg_mode == 'request' || $reg_mode == 'newtopic'))
	{
		if (isset($return_message))
		{
			$msg .= $return_message .'<br /><br /><hr /><br />';
		}
		$msg .= '<b>'. $lang['BT_REG_FAIL'] .'</b><br /><br />';
	}
	$msg .= $message;

	bb_die($msg);
}

// bdecode: based on OpenTracker [http://whitsoftdev.com/opentracker]
function bdecode_file ($filename)
{
	$file_contents = file_get_contents($filename);
	return bdecode($file_contents);
}

function bdecode ($str)
{
	$pos = 0;
	return bdecode_r($str, $pos);
}

function bdecode_r ($str, &$pos)
{
	$strlen = strlen($str);

	if (($pos < 0) || ($pos >= $strlen))
	{
		return null;
	}
	else if ($str[$pos] == 'i')
	{
		$pos++;
		$numlen = strspn($str, '-0123456789', $pos);
		$spos = $pos;
		$pos += $numlen;

		if (($pos >= $strlen) || ($str[$pos] != 'e'))
		{
			return null;
		}
		else
		{
			$pos++;
			return floatval(substr($str, $spos, $numlen));
		}
	}
	else if ($str[$pos] == 'd')
	{
		$pos++;
		$ret = array();

		while ($pos < $strlen)
		{
			if ($str[$pos] == 'e')
			{
				$pos++;
				return $ret;
			}
			else
			{
				$key = bdecode_r($str, $pos);

				if ($key === null)
				{
					return null;
				}
				else
				{
					$val = bdecode_r($str, $pos);

					if ($val === null)
					{
						return null;
					}
					else if (!is_array($key))
					{
						$ret[$key] = $val;
					}
				}
			}
		}
		return null;
	}
	else if ($str[$pos] == 'l')
	{
		$pos++;
		$ret = array();

		while ($pos < $strlen)
		{
			if ($str[$pos] == 'e')
			{
				$pos++;
				return $ret;
			}
			else
			{
				$val = bdecode_r($str, $pos);

				if ($val === null)
				{
					return null;
				}
				else
				{
					$ret[] = $val;
				}
			}
		}
		return null;
	}
	else
	{
		$numlen = strspn($str, '0123456789', $pos);
		$spos = $pos;
		$pos += $numlen;

		if (($pos >= $strlen) || ($str[$pos] != ':'))
		{
			return null;
		}
		else
		{
			$vallen = intval(substr($str, $spos, $numlen));
			$pos++;
			$val = substr($str, $pos, $vallen);

			if (strlen($val) != $vallen)
			{
				return null;
			}
			else
			{
				$pos += $vallen;
				return $val;
			}
		}
	}
}