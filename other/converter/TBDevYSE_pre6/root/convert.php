<?php

define ('IN_FORUM', true);
define ('BB_ROOT', './');
require (BB_ROOT .'common.php');
require (BB_ROOT .'converter/constants.php');
require (BB_ROOT .'converter/settings.php');
require (BB_ROOT .'converter/functions.php');

// Start session management
$user->session_start();

if (!IS_ADMIN) die("Restricted access");
while (@ob_end_flush());
ob_implicit_flush();

error_reporting(E_ALL);
@ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>
</head>
<body style="font: 12px Courier, monospace; white-space: nowrap;">

<?php

if (empty($_POST['confirm']))
{
	echo '
		<br />
		<center>
		<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
		<input type="submit" name="confirm" value="Start convert" />
		</form>
		</center>
	</body>
	';

	exit;
}
else
{

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', @ini_get('max_execution_time') + 1200);

// Step 1: Converting Users
if (CONVERT_USERS)
{
	if (CLEAN)
	{
		tp_users_cleanup();
		print_ok ("Users cleared");
	}

	$max_uid = (int) get_max_val(BB_USERS, 'user_id');
	$max_uid = ($max_uid > 1) ? $max_uid : 1;

	$users_count = (int) get_count(TB_USERS_TABLE, 'id');
	$loops = (int) ceil($users_count / C_USERS_PER_ONCE);
	$pass = array();

	switch(TR_TYPE)
	{
		case 'yse':
			$_sql = 'avatar, ';
			break;

		default:
			$_sql = '';
			break;
	}

	for ($i = 0; $i < $loops; $i++)
	{
		$start = $i * C_USERS_PER_ONCE;
		$offset = C_USERS_PER_ONCE;

		$sql = "
			SELECT
				id, username, email, status, UNIX_TIMESTAMP(added) AS added, UNIX_TIMESTAMP(last_access) AS last_access,
				class, icq, msn, aim, yahoo, website, $_sql
				uploaded, downloaded, enabled, language
			FROM ". TB_USERS_TABLE ."
			ORDER BY id
			LIMIT $start, $offset";

		$users = DB()->fetch_rowset($sql);
		DB()->sql_freeresult();

		foreach ($users as $user)
		{
			$user['id'] += $max_uid;
			$user['password'] = make_rand_str(15);
			convert_user($user);
			$pass[] = array(
				'tb_user_id'  => $user['id'] - $max_uid,
				'username'    => $user['username'],
				'new_passwd'  => $user['password'],
			);
		}
	}
	$passf = fopen('./converter/passwords.php', 'w');
	$to_write  = "<?php \n";
	$to_write .= '$passwords = '. var_export($pass, true) .';';
	fwrite($passf, $to_write);
	fclose($passf);
	set_auto_increment(BB_USERS, 'user_id');

	print_ok ("Total $users_count users from TBDev converted");
	unset($users, $pass, $to_write);
}

if (CONVERT_TORRENTS)
{
	require_once(INC_DIR .'functions_post.php');
	require_once(INC_DIR .'bbcode.php');

	if (CLEAN)
	{
		tp_categories_cleanup();
		tp_forums_cleanup();
		tp_topics_cleanup();
		print_ok ("Categories, forums and topics cleared");
	}

	$max_uid = !empty($max_uid) ? $max_uid : 1;

	//Create a category for torrents
	$max_cat_id = (int) get_max_val(BB_CATEGORIES, 'cat_id');
	$tr_cat_id = $max_cat_id + 1;

	$tp_cat_data = array(
		"cat_id"       => $tr_cat_id,
		"cat_title"    => 'Tracker',
	);
	tp_add_category($tp_cat_data);
	set_auto_increment(BB_CATEGORIES, 'cat_id');
	unset($tp_cat_data);

	$cats = $db->fetch_rowset("SELECT id, sort, name FROM ". TB_CATEGORIES_TABLE);
	DB()->sql_freeresult();

	$max_forum_id = (int) get_max_val(BB_FORUMS, 'forum_id');

	foreach ($cats as $cat)
	{
		$cat['id'] += $max_forum_id;
		$cat['cat_id'] = $tr_cat_id;
		convert_cat($cat);
	}
	set_auto_increment(BB_FORUMS, 'forum_id');
	print_ok ("Categories from TBDev converted");
	unset($cats);

	// Start of torrents converting
	switch(TR_TYPE)
	{
		case 'yse':
			$_sql = 'image1, image2, ';
			break;

		case 'sky':
			$_sql = 'poster, screenshot1, screenshot2, screenshot3, screenshot4, ';
			break;

		default:
			$_sql = '';
			break;
	}

	$max_topic_id  = (int) get_max_val(BB_TOPICS, 'topic_id');
	$max_post_id   = (int) get_max_val(BB_POSTS, 'post_id');
	$max_attach_id = (int) get_max_val(BB_ATTACHMENTS, 'attach_id');

	$torrents_count = (int) get_count(TB_TORRENTS_TABLE, 'id');
	$loops = (int) ceil($torrents_count / C_TORRENTS_PER_ONCE);

	for ($i = 0; $i < $loops; $i++)
	{
		$start = $i * C_TORRENTS_PER_ONCE;
		$offset = C_TORRENTS_PER_ONCE;
		$sql = "
			SELECT
				id, info_hash, name, filename, search_text, descr, $_sql
				category, UNIX_TIMESTAMP(added) AS added, size, views,
				UNIX_TIMESTAMP(last_action) AS lastseed, times_completed, owner, sticky
			FROM ". TB_TORRENTS_TABLE ."
			ORDER BY id
			LIMIT $start, $offset";

		$torrents = DB()->fetch_rowset($sql);
		DB()->sql_freeresult();

		foreach ($torrents as $torrent)
		{
			$torrent['topic_id']  = $torrent['id'] + $max_topic_id;
			$torrent['post_id']   = $torrent['id'] + $max_post_id;
			$torrent['attach_id'] = $torrent['id'] + $max_attach_id;
			$torrent['owner'] += $max_uid;
			$torrent['descr'] = append_images($torrent);
			convert_torrent($torrent);
			//print_r($torrent);
		}
	}
	set_auto_increment(BB_TOPICS, 'topic_id');
	set_auto_increment(BB_POSTS,   'post_id');
	print_ok ("Total $torrents_count torrents from TBDev converted");
	unset($torrents);

	if (CONVERT_COMMENTS)
	{
		$max_post_id   = (int) get_max_val(BB_POSTS, 'post_id');
		$max_topic_id  = (int) get_max_val(BB_TOPICS, 'topic_id');
		$max_attach_id = (int) get_max_val(BB_ATTACHMENTS, 'attach_id');

		$comments_count = (int) get_count(TB_COMMENTS_TABLE, 'id');
		$loops = (int) ceil($comments_count / C_COMMENTS_PER_ONCE);

		for ($i = 0; $i < $loops; $i++)
		{
			$start = $i * C_COMMENTS_PER_ONCE;
			$offset = C_COMMENTS_PER_ONCE;
			$sql = "
				SELECT
					c.id, c.user, c.torrent, c.text, tor.category,
					UNIX_TIMESTAMP(c.added) AS added, UNIX_TIMESTAMP(c.editedat) AS editedat, c.ip
				FROM ". TB_COMMENTS_TABLE ." c
				LEFT JOIN ". TB_TORRENTS_TABLE ." tor ON(tor.id = c.torrent)
				WHERE c.torrent <> 0
				ORDER BY c.id
				LIMIT $start, $offset";

			$comments = DB()->fetch_rowset($sql);
			DB()->sql_freeresult();

			foreach ($comments as $comment)
			{
				$comment['user'] += $max_uid;
				$comment['id'] += $max_post_id;
				convert_comment($comment);
			}
		}
		unset($comments);
		set_auto_increment(BB_POSTS, 'post_id');
		print_ok ("Total $comments_count comments from TBDev converted");
	}
}

?>
</div>
<br />
Converting completed.
</body>
</html>
<?php } ?>