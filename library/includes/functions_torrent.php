<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

function torrent_auth_check ($forum_id, $poster_id)
{
	global $lang, $userdata;

	if (IS_ADMIN || IS_CP_HOLDER || $poster_id == $userdata['user_id'])
	{
		return true;
	}

	if (IS_MOD)
	{
		$is_auth = auth(AUTH_MOD, $forum_id, $userdata);
		if ($is_auth['auth_mod']) return true;
	}

	bb_die($lang['NOT_MODERATOR']);
}

function tracker_unregister ($topic_id, $redirect_url = '')
{
	global $bb_cfg, $lang, $log_action;

	$tor = DB()->fetch_row("
		SELECT forum_id, tor_status FROM ". BB_BT_TORRENTS ." WHERE topic_id = ". intval($topic_id) ." LIMIT 1
	");
	$tor_status = isset($tor['tor_status']) ? $tor['tor_status'] : null;

	if ($mode == 'request')
	{
		if (!$torrent)
		{
			bb_die($lang['TOR_NOT_FOUND']);
		}
		if (!$torrent['tracker_status'])
		{
			bb_die('Torrent already unregistered');
		}
		torrent_auth_check($forum_id, $torrent['poster_id']);
	}

	if (!$topic_id)
	{
		$sql = "SELECT topic_id FROM ". BB_BT_TORRENTS ." WHERE attach_id = $attach_id";

		if (!$result = DB()->sql_query($sql))
		{
			bb_die('Could not query torrent information');
		}
		if ($row = DB()->sql_fetchrow($result))
		{
			$topic_id = $row['topic_id'];
		}
	}

	// Unset DL-Type for topic
	if ($bb_cfg['bt_unset_dltype_on_tor_unreg'] && $topic_id)
	{
		$sql = "UPDATE ". BB_TOPICS ." SET topic_dl_type = ". TOPIC_DL_TYPE_NORMAL ." WHERE topic_id = $topic_id LIMIT 1";

		if (!$result = DB()->sql_query($sql))
		{
			bb_die('Could not update topics table #1');
		}
	}

	// Remove peers from tracker
	$sql = "DELETE FROM ". BB_BT_TRACKER ." WHERE topic_id = $topic_id";

	if (!DB()->sql_query($sql))
	{
		bb_die('Could not delete peers');
	}

	// Ocelot
	if ($bb_cfg['ocelot']['enabled'])
	{
		if ($row = DB()->fetch_row("SELECT info_hash FROM ". BB_BT_TORRENTS ." WHERE attach_id = $attach_id LIMIT 1"))
		{
			$info_hash = $row['info_hash'];
		}
		ocelot_update_tracker('delete_torrent', array('info_hash' => rawurlencode($info_hash), 'id' => $topic_id));
	}

	// Delete torrent
	$sql = "DELETE FROM ". BB_BT_TORRENTS ." WHERE attach_id = $attach_id";

	if (!DB()->sql_query($sql))
	{
		bb_die('Could not delete torrent from torrents table');
	}

	// Update tracker_status
	$sql = "UPDATE ". BB_ATTACHMENTS_DESC ." SET tracker_status = 0 WHERE attach_id = $attach_id LIMIT 1";

	if (!DB()->sql_query($sql))
	{
		bb_die('Could not update torrent status #1');
	}

	if ($mode == 'request')
	{
		set_die_append_msg($forum_id, $topic_id);
		bb_die($lang['BT_UNREGISTERED']);
	}
}

function delete_torrent ($attach_id, $mode = '')
{
	global $lang, $reg_mode, $topic_id;

	$attach_id = intval($attach_id);
	$reg_mode = $mode;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die($lang['TOR_NOT_FOUND']);
	}

	$topic_id  = $torrent['topic_id'];
	$forum_id  = $torrent['forum_id'];
	$poster_id = $torrent['poster_id'];

	if ($torrent['extension'] !== TORRENT_EXT)
	{
		bb_die($lang['NOT_TORRENT']);
	}

	torrent_auth_check($forum_id, $poster_id);
	tracker_unregister($attach_id);
	delete_attachment(0, $attach_id);

	return;
}

function change_tor_status ($attach_id, $new_tor_status)
{
	global $lang, $topic_id, $userdata;

	$attach_id = (int) $attach_id;
	$new_tor_status = (int) $new_tor_status;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die($lang['TOR_NOT_FOUND']);
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
		bb_die($lang['TOR_NOT_FOUND']);
	}

	if (!IS_AM) bb_die($lang['ONLY_FOR_MOD']);

	$topic_id        = $torrent['topic_id'];
	$tor_status_gold = intval($tor_status_gold);
	$info_hash       = null;

	DB()->query("UPDATE ". BB_BT_TORRENTS ." SET tor_type = $tor_status_gold WHERE topic_id = $topic_id LIMIT 1");

	// Ocelot
	if ($bb_cfg['ocelot']['enabled'])
	{
		if ($row = DB()->fetch_row("SELECT info_hash FROM ". BB_BT_TORRENTS ." WHERE topic_id = $topic_id LIMIT 1"))
		{
			$info_hash = $row['info_hash'];
		}
		ocelot_update_tracker('update_torrent', array('info_hash' => rawurlencode($info_hash), 'freetorrent' => $tor_status_gold));
	}
}

function tracker_register ($attach_id, $mode = '', $tor_status = TOR_NOT_APPROVED, $reg_time = TIMENOW)
{
	global $bb_cfg, $lang, $reg_mode, $tr_cfg;

	$attach_id = intval($attach_id);
	$reg_mode = $mode;

	if (!$torrent = get_torrent_info($attach_id))
	{
		bb_die($lang['TOR_NOT_FOUND']);
	}

	$post_id   = $torrent['post_id'];
	$topic_id  = $torrent['topic_id'];
	$forum_id  = $torrent['forum_id'];
	$poster_id = $torrent['poster_id'];
	$info_hash = null;

	if ($torrent['extension'] !== TORRENT_EXT) return torrent_error_exit($lang['NOT_TORRENT']);
	if (!$torrent['allow_reg_tracker']) return torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
	if ($post_id != $torrent['topic_first_post_id']) return torrent_error_exit($lang['ALLOWED_ONLY_1ST_POST_REG']);
	if ($torrent['tracker_status']) return torrent_error_exit($lang['ALREADY_REG']);
	if ($this_topic_torrents = get_registered_torrents($topic_id, 'topic')) return torrent_error_exit($lang['ONLY_1_TOR_PER_TOPIC']);

	torrent_auth_check($forum_id, $torrent['poster_id']);

	$filename = get_attachments_dir() .'/'. $torrent['physical_filename'];

	if (!is_file($filename)) return torrent_error_exit('File name error');
	if (!file_exists($filename)) return torrent_error_exit('File not exists');
	if (!$tor = bdecode_file($filename)) return torrent_error_exit('This is not a bencoded file');

	if ($bb_cfg['bt_disable_dht'])
	{
		$tor['info']['private'] = (int) 1;
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
			return torrent_error_exit($msg);
		}
	}

	$info = (@$tor['info']) ? $tor['info'] : array();

	if (!isset($info['name']) || !isset($info['piece length']) || !isset($info['pieces']) || strlen($info['pieces']) % 20 != 0)
	{
		return torrent_error_exit($lang['TORFILE_INVALID']);
	}

	$info_hash     = pack('H*', sha1(bencode($info)));
	$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
	$info_hash_md5 = md5($info_hash);

	// Ocelot
	if ($bb_cfg['ocelot']['enabled'])
	{
		ocelot_update_tracker('add_torrent', array('info_hash' => rawurlencode($info_hash), 'id' => $topic_id, 'freetorrent' => 0));
	}

	if ($row = DB()->fetch_row("SELECT topic_id FROM ". BB_BT_TORRENTS ." WHERE info_hash = '$info_hash_sql' LIMIT 1"))
	{
		set_die_append_msg($forum_id, $topic_id);
		bb_die(sprintf($lang['BT_REG_FAIL_SAME_HASH'], TOPIC_URL . $row['topic_id']));
	}

	$totallen = 0;

	if (isset($info['length']))
	{
		$totallen = (float) $info['length'];
	}
	else if (isset($info['files']) && is_array($info['files']))
	{
		foreach ($info['files'] as $fn => $f)
		{
			$totallen += (float) $f['length'];
		}
	}
	else
	{
		return torrent_error_exit($lang['TORFILE_INVALID']);
	}

	$size = sprintf('%.0f', (float) $totallen);

	$columns = ' info_hash,       poster_id,  topic_id,  forum_id,   size,   reg_time,  tor_status';
	$values = "'$info_hash_sql', $poster_id, $topic_id, $forum_id, '$size', $reg_time, $tor_status";

	$sql = "INSERT INTO ". BB_BT_TORRENTS ." ($columns) VALUES ($values)";

	if (!DB()->sql_query($sql))
	{
		$sql_error = DB()->sql_error();

		if ($sql_error['code'] == 1062) // Duplicate entry
		{
			return torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
		}
		bb_die('Could not register torrent on tracker');
	}

	// Set topic status
	DB()->query("UPDATE ". BB_TOPICS ." SET tracker_status = 1 WHERE topic_id = $topic_id LIMIT 1");

	// Clean all peers for that torrent
	tracker_rm_torrent($topic_id);

	if ($reg_mode == 'request' || $reg_mode == 'newtopic')
	{
		set_die_append_msg($forum_id, $topic_id);
		bb_die(sprintf($lang['BT_REGISTERED'], DOWNLOAD_URL . $topic_id));
	}
	else if ($reg_mode == 'mcp_tor_register')
	{
		return 'OK';
	}

	return true;
}

function delete_torrent ($topic_id)
{
	tracker_unregister($topic_id);
	delete_attach($topic_id, 8);

	return true;
}

function change_tor_status ($topic_id, $tor_status)
{
	global $topic_id, $userdata;

	$tor_status = (int) $tor_status;

	$tor = DB()->fetch_row("SELECT forum_id, poster_id FROM ". BB_BT_TORRENTS ." WHERE topic_id = ". intval($topic_id) ." LIMIT 1");

	torrent_auth_check($tor['forum_id'], $tor['poster_id']);

	DB()->query("
		UPDATE ". BB_BT_TORRENTS ." SET
			tor_status = $tor_status,
			checked_user_id = {$userdata['user_id']},
			checked_time = '". TIMENOW ."'
		WHERE topic_id = $topic_id
		LIMIT 1
	");
}

// Set gold / silver type for torrent
function change_tor_type ($topic_id, $tor_status_gold)
{
	global $lang, $bb_cfg;

	if (!IS_AM) bb_die($lang['ONLY_FOR_MOD']);

	$tor_status_gold = intval($tor_status_gold);
	$info_hash       = null;

	DB()->query("UPDATE ". BB_BT_TORRENTS ." SET tor_type = $tor_status_gold WHERE topic_id = $topic_id LIMIT 1");

	// Ocelot
	if ($bb_cfg['ocelot']['enabled'])
	{
		if ($row = DB()->fetch_row("SELECT info_hash FROM ". BB_BT_TORRENTS ." WHERE topic_id = $topic_id LIMIT 1"))
		{
			$info_hash = $row['info_hash'];
		}
		ocelot_update_tracker('update_torrent', array('info_hash' => rawurlencode($info_hash), 'freetorrent' => $tor_status_gold));
	}
}

function send_torrent_with_passkey ($t_data)
{
	global $userdata, $bb_cfg, $tr_cfg, $lang;

	$topic_id   = $t_data['topic_id'];
	$poster_id  = $t_data['topic_poster'];
	$user_id    = $t_data['user_id'];

	// Запрет на скачивание закрытого или незарегистрированного торрента
	$row = DB()->fetch_row("SELECT tor_status FROM ". BB_BT_TORRENTS ." WHERE topic_id = $topic_id LIMIT 1");

	if (!isset($row['tor_status']))
	{
		bb_die($lang['PASSKEY_ERR_TOR_NOT_REG']);
	}
	else if (isset($bb_cfg['tor_frozen'][$row['tor_status']]))
	{
		if (!$t_data['is_am']) bb_die("Раздача имеет статус: <b>{$lang['tor_status'][$row['tor_status']]}</b><br /><br />Скачивание запрещено");
	}

	$passkey_val = '';

	if ($bt_userdata = get_bt_userdata($user_id))
	{
		$passkey_val = $bt_userdata['auth_key'];
	}

	if (!$passkey_val)
	{
		if (!$passkey_val = generate_passkey($user_id))
		{
			bb_simple_die('Could not generate passkey');
		}
		elseif ($bb_cfg['ocelot']['enabled'])
		{
			ocelot_update_tracker('add_user', array('id' => $user_id ,'passkey' => $passkey_val));
		}
	}

	// Ratio limit for torrents dl
	$user_ratio = get_bt_ratio($bt_userdata);
	$min_ratio = $bb_cfg['bt_min_ratio_allow_dl_tor'];

	if ($min_ratio && $user_id != $poster_id && !is_null($user_ratio))
	{
		if ($user_ratio < $min_ratio)
		{
			$dl = DB()->fetch_row("
				SELECT user_status FROM ". BB_BT_DLSTATUS ." WHERE topic_id = $topic_id AND user_id = $user_id LIMIT 1
			");

			if (!isset($dl['user_status']) || $dl['user_status'] != DL_STATUS_COMPLETE)
			{
				bb_die(sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$user_id&amp;dlc=1"));
			}
		}
	}

	/*
	// лимит количества скачиваний торрент-файлов в день
	if ($user_id != $poster_id && !$t_data['is_am'])
	{
		// лимит
		$daily_dls_limit = 50;

		if (!is_null($user_ratio) && $user_ratio >= 1 && $bt_userdata['u_up_total'] >= 107374182400 )// 100 GB
		{
			$daily_dls_limit = 100;
		}

		// число скачиваний
		$daily_dls_cnt = (int) DB('dls')->fetch_row("SELECT dls_cnt FROM ". BB_USER_DLS_DAILY ." WHERE user_id = $user_id LIMIT 1", 'dls_cnt');

		if ($daily_dls_cnt >= $daily_dls_limit)
		{
			// повторное скачивание
			$can_redownload = DB('dls')->fetch_row("SELECT 1 FROM ". BB_BT_DLS_COUNT ." WHERE topic_id = $topic_id AND user_id = $user_id LIMIT 1");

			if (!$can_redownload)
			{
				bb_log(join("\t", array(date('H:i:s'), $user->ip, $user->id, $topic_id))."\n", 'dls/'.date('m-d') .'-limit');
				set_die_append_msg(null, $topic_id);
				bb_die("Вы уже исчерпали суточный лимит скачиваний торрент-файлов<br /><br />Ваш текущий лимит: $daily_dls_limit в день");
			}
			else
			{
				bb_log(join("\t", array(date('H:i:s'), $user->ip, $user->id, $topic_id))."\n", 'dls/'.date('m-d') .'-redown');
			}
		}

		// счетчик количества скачиваний торрент-файла (для `complete_count` в BB_BT_TORRENTS)
		DB('dls')->query("INSERT IGNORE INTO ". BB_BT_DLS_COUNT ." (topic_id, user_id) VALUES ($topic_id, $user_id)");

		// если файл еще не был скачан этим юзером, увеличиваем счетчик скачиваний
		if (DB('dls')->affected_rows() > 0)
		{
			DB('dls')->query("
					INSERT IGNORE INTO ". BB_USER_DLS_DAILY ." (user_id, dls_cnt) VALUES ($user_id, 1) ON DUPLICATE KEY UPDATE dls_cnt = dls_cnt + 1
				");
		}
	}
	*/

	$filename = get_attach_path($topic_id, 8);
	if (!file_exists($filename))
	{
		bb_simple_die('File not found');
	}
	if (!$tor = bdecode_file($filename))
	{
		bb_simple_die('This is not a bencoded file');
	}

	// tor cleanup
	unset($tor['codepage']);
	unset($tor['nodes']);

	// Announce URL
	$announce = $bb_cfg['ocelot']['enabled'] ? strval($bb_cfg['ocelot']['url'] .$passkey_val. "/announce") : strval($bb_cfg['bt_announce_url'] . "?{$bb_cfg['passkey_key']}=$passkey_val");

	// Replace original announce url with tracker default
	if ($bb_cfg['bt_replace_ann_url'] || !isset($tor['announce']))
	{
		$tor['announce'] = $announce;
	}

	// Delete all additional urls
	if ($bb_cfg['bt_del_addit_ann_urls'] || $bb_cfg['bt_disable_dht'])
	{
		unset($tor['announce-list']);
	}
	elseif (isset($tor['announce-list']))
	{
		$tor['announce-list'] = array_merge($tor['announce-list'], [[$announce]]);
	}

	// Add retracker
	if (isset($tr_cfg['retracker']) && $tr_cfg['retracker'])
	{
		if (bf($userdata['user_opt'], 'user_opt', 'user_retracker') || IS_GUEST)
		{
			if (!isset($tor['announce-list']))
			{
				$tor['announce-list'] = [
					[$announce],
					[$tr_cfg['retracker_host']]
				];
			}
			else
			{
				$tor['announce-list'] = array_merge($tor['announce-list'], [[$tr_cfg['retracker_host']]]);
			}
		}
	}

	// Add publisher & topic url
	$publisher_name = $bb_cfg['server_name'];
	$publisher_url  = make_url(TOPIC_URL . $topic_id);

	$tor['publisher'] = strval($publisher_name);
	unset($tor['publisher.utf-8']);

	$tor['publisher-url'] = strval($publisher_url);
	unset($tor['publisher-url.utf-8']);

	$tor['comment'] = strval($publisher_url);
	unset($tor['comment.utf-8']);

	// Send torrent
	$output   = bencode($tor);
	$dl_fname = ($bb_cfg['torrent_name_style'] ? '['.$bb_cfg['server_name'].'].t' . $topic_id . '.torrent' : clean_filename(basename($attachment['real_filename'])));

	if (!empty($_COOKIE['explain']))
	{
		$out = "attach path: $filename<br /><br />";
		$tor['info']['pieces'] = '[...] '. strlen($tor['info']['pieces']) .' bytes';
		$out .= print_r($tor, true);
		bb_die("<pre>$out</pre>");
	}

	header("Content-Type: application/x-bittorrent; name=\"$dl_fname\"");
	header("Content-Disposition: attachment; filename=\"$dl_fname\"");

	bb_exit($output);
}

function generate_passkey ($user_id, $force_generate = false)
{
	global $bb_cfg, $lang;

	$user_id = (int) $user_id;

	// Check if user can change passkey
	if (!$force_generate)
	{
		$sql = "SELECT user_opt FROM ". BB_USERS ." WHERE user_id = $user_id LIMIT 1";

		if (!$result = DB()->sql_query($sql))
		{
			bb_die('Could not query userdata for passkey');
		}
		if ($row = DB()->sql_fetchrow($result))
		{
			if (bf($row['user_opt'], 'user_opt', 'dis_passkey'))
			{
				bb_die($lang['NOT_AUTHORISED']);
			}
		}
	}

	// Delete all active user records in tracker
	tracker_rm_user($user_id);

	for ($i=0; $i < 20; $i++)
	{
		$passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);
		$old_passkey = null;

		if ($row = DB()->fetch_row("SELECT auth_key FROM ". BB_BT_USERS ." WHERE user_id = $user_id LIMIT 1"))
		{
			$old_passkey = $row['auth_key'];
		}

		// Insert new row
		DB()->query("INSERT IGNORE INTO ". BB_BT_USERS ." (user_id, auth_key) VALUES ($user_id, '$passkey_val')");

		if (DB()->affected_rows() == 1)
		{
			return $passkey_val;
		}
		// Update
		DB()->query("UPDATE IGNORE ". BB_BT_USERS ." SET auth_key = '$passkey_val' WHERE user_id = $user_id LIMIT 1");

		if (DB()->affected_rows() == 1)
		{
			// Ocelot
			if ($bb_cfg['ocelot']['enabled'])
			{
				ocelot_update_tracker('change_passkey', array('oldpasskey' => $old_passkey,'newpasskey' => $passkey_val));
			}
			return $passkey_val;
		}
	}
	return false;
}

function torrent_error_exit ($message)
{
	global $reg_mode, $return_message, $lang;

	if (isset($reg_mode) && $reg_mode == 'mcp_tor_register')
	{
		return $message;
	}

	$msg = '';

	if (isset($reg_mode) && ($reg_mode == 'request' || $reg_mode == 'newtopic'))
	{
		if (isset($return_message))
		{
			$msg .= $return_message .'<br /><br /><hr /><br />';
		}
		$msg .= '<b>'. $lang['BT_REG_FAIL'] .'</b><br /><br />';
	}

	bb_die($msg . $message);
}

function tracker_rm_torrent ($topic_id)
{
	return DB()->sql_query("DELETE FROM ". BB_BT_TRACKER ." WHERE topic_id = ". (int) $topic_id);
}

function tracker_rm_user ($user_id)
{
	return DB()->sql_query("DELETE FROM ". BB_BT_TRACKER ." WHERE user_id = ". (int) $user_id);
}

function ocelot_update_tracker ($action, $updates)
{
	global $bb_cfg;

	$get = $bb_cfg['ocelot']['secret'] . "/update?action=$action";

	foreach ($updates as $key => $value)
	{
		$get .= "&$key=$value";
	}

	$max_attempts = 3;
	$err = false;

	if (ocelot_send_request($get, $max_attempts, $err) === false)
	{
		return false;
	}

	return true;
}

function ocelot_send_request ($get, $max_attempts = 1, &$err = false)
{
	global $bb_cfg;

	$header = "GET /$get HTTP/1.1\r\nConnection: Close\r\n\r\n";
	$attempts = $sleep = $success = $response = 0;
	$start_time = microtime(true);

	while (!$success && $attempts++ < $max_attempts)
	{
		if ($sleep)
		{
			sleep($sleep);
		}

		// Send request
		$file = fsockopen($bb_cfg['ocelot']['host'], $bb_cfg['ocelot']['port'], $error_num, $error_string);
		if ($file)
		{
			if (fwrite($file, $header) === false)
			{
				$err = "Failed to fwrite()";
				$sleep = 3;
				continue;
			}
		}
		else
		{
			$err = "Failed to fsockopen() - $error_num - $error_string";
			$sleep = 6;
			continue;
		}

		// Check for response
		while (!feof($file))
		{
			$response .= fread($file, 1024);
		}
		$data_start = strpos($response, "\r\n\r\n") + 4;
		$data_end = strrpos($response, "\n");
		if ($data_end > $data_start)
		{
			$data = substr($response, $data_start, $data_end - $data_start);
		}
		else
		{
			$data = "";
		}
		$status = substr($response, $data_end + 1);
		if ($status == "success")
		{
			$success = true;
		}
	}

	return $success;
}

// bdecode: based on OpenTracker
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