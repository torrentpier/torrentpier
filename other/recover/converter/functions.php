<?php

function print_ok ($sql)
{
	global $err;

	echo ($err) ? "\n<br />" : '';
	$err = '';

	echo '<div>';
	echo "<font color=darkgreen><b>OK</b> - $sql</font>". str_repeat(' ', 256) ."\n<br />";
	echo '</div>';
}

function hex2bin($h)
{
  if (!is_string($h)) return null;
  $r='';
  for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
  return $r;
}

function get_max_val($table_name, $column)
{
	global $db;
	
	$row = $db->fetch_row("SELECT MAX($column) AS $column FROM $table_name LIMIT 1");
	return $row[$column];
}

function get_count($table_name, $column)
{
	global $db;
	
	$row = $db->fetch_row("SELECT COUNT($column) AS $column FROM $table_name LIMIT 1");
	return $row[$column];
}

function set_auto_increment($table_name, $column, $val = null)
{
	global $db;
	
	if (empty($val))
	{
		$row = $db->fetch_row("SELECT MAX($column) AS val FROM $table_name LIMIT 1");
		$db->sql_freeresult();
		$val = (int) $row['val'] + 1;
	}
	$db->query("ALTER TABLE $table_name auto_increment = $val");
}

//Users functions
function tp_users_cleanup()
{
	global $db;
	
	/*
	if (!function_exists('user_delete')) require_once('./includes/functions_admin.php');
	
	if ($row = $db->fetch_row("SELECT user_id FROM ". USERS_TABLE ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .');'))
	{
		foreach ($row as $user)
		{
			user_delete($user['user_id']);
		}
	}*/
	$db->query('DELETE FROM '. USERS_TABLE .' WHERE user_id NOT IN('. EXCLUDED_USERS_CSV .')');
	$db->query('TRUNCATE '. BT_USERS_TABLE);
}

function tp_user_level($tb_class)
{
	switch($tb_class)
	{
		case 0:
		case 1:
		case 2:
		case 3:
			$level = 0;
			break;
		case 4:
			$level = 2;
			break;
		case 5:
		case 6:
		case 7:
			$level = 1;
			break;
		default:
			$level = 0;
			break;
	}
	return $level;
}

function convert_user($user)
{
	global $db;
	
	$user_data = array(
		"user_id"          => $user['id'],
		"user_active"      => ($user['enabled'] == 'yes') ? true : false,
		"username"         => $user['username'],
		"user_password"    => md5($user['password']),	
		"user_lastvisit"   => $user['last_access'],
		"user_regdate"     => $user['added'],
		"user_level"       => tp_user_level($user['class']),
		"user_lang"        => $user['language'],
		"user_dateformat"  => "Y-m-d H:i",
		//"user_opt"         => ,
		"user_avatar"      => !empty($user['avatar']) ? $user['avatar'] : null,
		"user_avatar_type" => !empty($user['avatar']) ? 2 : null,
		"user_email"       => $user['email'],
		"user_website"     => $user['website'],
		"user_icq"         => $user['icq'],		
		"user_aim"         => $user['aim'],
		"user_yim"         => $user['yahoo'],
		"user_msnm"        => $user['msn'],		
	);

	$columns = $values = array();
	
	foreach ($user_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_columns = implode(',', $columns);
	$sql_values = implode(',', $values);
	
	$db->query("INSERT IGNORE INTO ". USERS_TABLE . " ($sql_columns) VALUES($sql_values);");
	
	$bt_user_data = array(
		"user_id"      => $user['id'],
		"auth_key"     => make_rand_str(BT_AUTH_KEY_LENGTH),
		"u_up_total"   => $user['uploaded'],
		"u_down_total" => $user['downloaded'],
	);
	$columns = $values = array();
	
	foreach ($bt_user_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_bt_columns = implode(',', $columns);
	$sql_bt_values = implode(',', $values);
	
	$db->query("INSERT IGNORE INTO ". BT_USERS_TABLE . " ($sql_bt_columns) VALUES($sql_bt_values);");
}

//Torrents and categories functions
function tp_categories_cleanup()
{
	global $db;
	
	$db->query('DELETE FROM '. CATEGORIES_TABLE);
}

function tp_add_category_old($id, $cat_title)
{
	global $db;
	
	$db->query("INSERT IGNORE INTO ". CATEGORIES_TABLE ." (cat_id, cat_title) 
				VALUES ($id, '". $db->escape($cat_title) ."')");
	return;
}

function tp_add_category($cat_data)
{
	global $db;
	
	$columns = $values = array();
	
	foreach ($cat_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_bt_columns = implode(',', $columns);
	$sql_bt_values = implode(',', $values);
	
	$db->query("INSERT IGNORE INTO ". CATEGORIES_TABLE . " ($sql_bt_columns) VALUES($sql_bt_values);");
}

function tp_topics_cleanup()
{
	global $db;
	
	$db->query("TRUNCATE ". ATTACHMENTS_TABLE);
	$db->query("TRUNCATE ". ATTACHMENTS_DESC_TABLE);
	$db->query("TRUNCATE ". BT_TORRENTS_TABLE);
	$db->query("TRUNCATE ". POSTS_TABLE);
	$db->query("TRUNCATE ". POSTS_HTML_TABLE);
	$db->query("TRUNCATE ". POSTS_SEARCH_TABLE);
	$db->query("TRUNCATE ". POSTS_TEXT_TABLE);
	$db->query("TRUNCATE ". TOPICS_TABLE);
	/*if (!function_exists('topic_delete')) require_once('./includes/functions_admin.php');	
	
	if ($row = $db->fetch_row("SELECT topic_id FROM ". TOPICS_TABLE))
	{
		foreach ($row as $topic)
		{
			topic_delete($topic['topic_id']);
		}
	}*/
	
	return;
}

function tp_add_topic($topic_data)
{
	global $db;
	
	$columns = $values = array();
	foreach ($topic_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_columns = implode(',', $columns);
	$sql_values = implode(',', $values);
	
	$db->query("INSERT IGNORE INTO ". TOPICS_TABLE . " ($sql_columns) VALUES($sql_values);");
	return;
}

function tp_add_post($post_data)
{
	global $db;
	
	foreach ($post_data as $key => $data)
	{
		$columns = $values = array();
		foreach ($data as $column => $value)
		{
			$columns[] = $column;
			$values[]  = "'". $db->escape($value) ."'";
		}
		$sql_columns = implode(',', $columns);
		$sql_values = implode(',', $values);
	
		$db->query("INSERT IGNORE INTO bb_{$key} ($sql_columns) VALUES($sql_values);");
	}
	return;
}

function tp_add_attach($attach_data)
{
	global $db;
	
	foreach ($attach_data as $key => $data)
	{
		$columns = $values = array();
		foreach ($data as $column => $value)
		{
			$columns[] = $column;
			$values[]  = "'". $db->escape($value) ."'";
		}
		$sql_columns = implode(',', $columns);
		$sql_values = implode(',', $values);
	
		$db->query("INSERT IGNORE INTO bb_{$key} ($sql_columns) VALUES($sql_values);");
	}
	return;
}

function make_img_path ($name)
{
	global $bb_cfg;

	return make_url("files/images/" . $name);
}

function append_images($tor)
{
	$poster = $screens = '';
	switch(TR_TYPE)
	{		
		case 'yse':
			if(!empty($tor['image1'])) 
			{
				$poster = "[img=right]".make_img_path($tor['image1'])."[/img]";
			}
			if(!empty($tor['image2'])) 
			{
				$screens = '[spoiler="Скриншоты"][img]'.make_img_path($tor['image2'])."[/img][/spoiler]";
			}
			break;
		case 'sky':
			if(!empty($tor['poster'])) 
			{
				$poster = "[img=right]".make_img_path($tor['poster'])."[/img]";
			}
			$has_screens = !empty($tor['screenshot1']) || !empty($tor['screenshot2']) || !empty($tor['screenshot3']) || !empty($tor['screenshot4']);
			if ($has_screens)
			{
				$screens .= '[spoiler="Скриншоты"]';
				for ($i = 1; $i<=4; $i++)
				{
					if(!empty($tor['screenshot'.$i])) 
					{
						$screens .= "[img]".make_img_path($tor['screenshot'.$i])."[/img] \n";
					}
				}
				$screens .= "[/spoiler]";
			}
			break;
	}
	return ($poster . $tor['descr'] . $screens);
}

function convert_torrent($torrent)
{
	global $db;
	
	$topic_data = array(
		"topic_id"       => $torrent['topic_id'],
		"forum_id"       => $torrent['category'],
		"topic_title"    => $torrent['name'],
		"topic_poster"   => $torrent['owner'],
		"topic_time"     => $torrent['added'],
		"topic_views"    => $torrent['views'],
		"topic_type"     => ($torrent['sticky'] == 'yes') ? 1 : 0,
		"topic_first_post_id"  => $torrent['id'],
		"topic_last_post_id"   => $torrent['id'],
		"topic_attachment"     => 1,
		"topic_dl_type"        => 1,
		"topic_last_post_time" => $torrent['added'],
	);
	tp_add_topic($topic_data);
	
	$bbcode_uid = make_bbcode_uid();
	//$post_text = prepare_message($torrent['descr'], true, true, $bbcode_uid);
	$post_text = stripslashes(prepare_message(addslashes(unprepare_message($torrent['descr'])), true, true, $bbcode_uid));
	
	$post_data = array(
		"posts"        => array(
							"post_id"    => $torrent['post_id'],
							"topic_id"   => $torrent['topic_id'],
							"forum_id"   => $torrent['category'],
							"poster_id"  => $torrent['owner'],
							"post_time"  => $torrent['added'],
							"post_attachment"  => 1,
					      ),
		"posts_text"   => array(
							"post_id"    => $torrent['post_id'],
							"bbcode_uid" => $bbcode_uid,
							"post_text"  => $post_text,
					      ),
		"posts_search" => array(
							"post_id"       => $torrent['post_id'],
							"search_words"  => $torrent['search_text'],
					      ),
	);
	tp_add_post($post_data);
	
	$attach_data = array(
		"attachments"        => array(
									"attach_id" => $torrent['attach_id'],
									"post_id"   => $torrent['post_id'],
									"user_id_1" => $torrent['owner'],
								),
		"attachments_desc"   => array(
									"attach_id"          => $torrent['attach_id'],
									"physical_filename"  => $torrent['id'] . ".torrent",
									"real_filename"      => $torrent['filename'],
									"extension"          => "torrent",
									"mimetype"           => "application/x-bittorrent",
									"filesize"           => @filesize(get_attachments_dir() .'/'. $torrent['id'] .".torrent"),
									"filetime"           => $torrent['added'],
									"tracker_status"     => 1,
								),
	);
	tp_add_attach($attach_data);
	
	//Torrents
	if (BDECODE)
	{
		$filename = get_attachments_dir() .'/'. $torrent['id'] .".torrent";	
		if (!file_exists($filename))
		{
			return;
		}
		if (!function_exists('bdecode_file')) include_once('./includes/functions_torrent.php');
		$tor = bdecode_file($filename);
		$info = ($tor['info']) ? $tor['info'] : array();
		$info_hash     = pack('H*', sha1(bencode($info)));
		$info_hash_sql = rtrim($db->escape($info_hash), ' ');
	}
	else
	{
		$info_hash_sql = hex2bin($torrent['info_hash']);
	}
	
	$torrent_data = array(
		"info_hash"      => $info_hash_sql,
		"post_id"        => $torrent['post_id'],
		"poster_id"      => $torrent['owner'],
		"topic_id"       => $torrent['topic_id'],
		"forum_id"       => $torrent['category'],
		"attach_id"      => $torrent['attach_id'],
		"size"   	     => $torrent['size'],
		"reg_time"       => $torrent['added'],
		"complete_count" => $torrent['times_completed'],
		"seeder_last_seen" => $torrent['lastseed'],
	);

	$columns = $values = array();
	
	foreach ($torrent_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_columns = implode(', ', $columns);
	$sql_values = implode(', ', $values);
	
	$db->query("INSERT IGNORE INTO ". BT_TORRENTS_TABLE . " ($sql_columns) VALUES($sql_values);");
	return;
}

//Comments functions
function convert_comment($comment)
{
	global $db;
	
	$bbcode_uid = make_bbcode_uid();
	$post_text = prepare_message($comment['text'], true, true, $bbcode_uid);
	
	$post_data = array(
		"posts"        => array(
							"post_id"    => $comment['id'],
							"topic_id"   => $comment['torrent'],
							"forum_id"   => $comment['category'],
							"poster_id"  => $comment['user'],
							"post_time"  => $comment['added'],
							"poster_ip"  => encode_ip($comment['ip']),
							"post_edit_time"   => $comment['editedat'],
							"post_edit_count"  => $comment['editedat'] ? 1 : 0,
					      ),
		"posts_text"   => array(
							"post_id"    => $comment['id'],
							"bbcode_uid" => $bbcode_uid,
							"post_text"  => $post_text,
					      ),
	);
	tp_add_post($post_data);
	//add_search_words($comment['id'], stripslashes($post_text));
	return;
}

//Forums functions
function tp_forums_cleanup()
{
	global $db;
	
	$db->query('TRUNCATE '. FORUMS_TABLE);
}

function convert_cat($forum, $allow_torrents = true)
{
	global $db;

	$forum_data = array(
		"forum_id"     => $forum['id'],
		"cat_id"       => $forum['cat_id'],
		"forum_name"   => $forum['name'],
		"forum_order"  => $forum['sort'],
		"allow_reg_tracker" => $allow_torrents,
		"allow_dl_topic"    => $allow_torrents,
	);

	$columns = $values = array();
	
	foreach ($forum_data as $column => $value)
	{
		$columns[] = $column;
		$values[]  = "'". $db->escape($value) ."'";
	}
	$sql_columns = implode(',', $columns);
	$sql_values = implode(',', $values);
	
	$db->query("INSERT IGNORE INTO ". FORUMS_TABLE . " ($sql_columns) VALUES($sql_values);");
	return;
}
