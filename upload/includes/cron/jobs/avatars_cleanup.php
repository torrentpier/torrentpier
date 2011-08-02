<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$fix_errors = true;
$debug_mode = false;

$tmp_tbl       = 'avatars';
$db_max_packet = 800000;
$sql_limit     = 1000;

$check_avatars = false;
$orphan_files = $orphan_db_avatars = array();

DB()->query("
	CREATE TEMPORARY TABLE $tmp_tbl (
		user_avatar VARCHAR(255) NOT NULL default '',
		KEY user_avatar (user_avatar(20))
	) ENGINE = MyISAM DEFAULT CHARSET = utf8
");

DB()->query("ALTER TABLE ". BB_USERS ." ADD INDEX user_avatar(user_avatar(10))");

$avatars_dir = BB_ROOT . $bb_cfg['avatar_path'];

// Get all names of existed avatars and insert them into $tmp_tbl
if ($dir = @opendir($avatars_dir))
{
	$check_avatars = true;
	$files = array();
	$f_len = 0;

	while (false !== ($f = readdir($dir)))
	{
		if ($f == 'index.php' || $f == '.htaccess' || is_dir("$avatars_dir/$f") || is_link("$avatars_dir/$f"))
		{
			continue;
		}
		$f = DB()->escape($f);
		$files[] = "('$f')";
		$f_len += strlen($f) + 5;

		if ($f_len > $db_max_packet)
		{
			$files = join(',', $files);
			DB()->query("INSERT INTO $tmp_tbl VALUES $files");
			$files = array();
			$f_len = 0;
		}
	}
	if ($files = join(',', $files))
	{
		DB()->query("INSERT INTO $tmp_tbl VALUES $files");
	}
	closedir($dir);
}

if ($check_avatars)
{
	// Delete avatars that exist in file system but not exist in DB
	$sql = "SELECT f.user_avatar
		FROM $tmp_tbl f
		LEFT JOIN ". BB_USERS ." u USING(user_avatar)
		WHERE u.user_avatar IS NULL
		LIMIT $sql_limit";

	foreach (DB()->fetch_rowset($sql) as $row)
	{
		if ($filename = basename($row['user_avatar']))
		{
			if ($fix_errors)
			{
				@unlink("$avatars_dir/$filename");
			}
			if ($debug_mode)
			{
				$orphan_files[] = "$avatars_dir/$filename";
			}
		}
	}
	// Find DB records for avatars that exist in DB but not exist in file system
	$sql = "SELECT u.user_id
		FROM ". BB_USERS ." u
		LEFT JOIN $tmp_tbl f USING(user_avatar)
		WHERE u.user_avatar_type = ". USER_AVATAR_UPLOAD ."
			AND f.user_avatar IS NULL
		LIMIT $sql_limit";

	foreach (DB()->fetch_rowset($sql) as $row)
	{
		$orphan_db_avatars[] = $row['user_id'];
	}
	// Delete all orphan avatars from DB
	if ($orphans_sql = join(',', $orphan_db_avatars))
	{
		if ($fix_errors)
		{
			DB()->query("
				UPDATE ". BB_USERS ." SET
					user_avatar      = '',
					user_avatar_type = ". USER_AVATAR_NONE ."
				WHERE user_id IN($orphans_sql)
			");
		}
	}
}

if ($debug_mode)
{
	prn_r($orphan_files, '$orphan_files');
	prn_r($orphan_db_avatars, '$orphan_db_avatars');
}

DB()->query("DROP TEMPORARY TABLE $tmp_tbl");
DB()->query("ALTER TABLE ". BB_USERS ." DROP INDEX user_avatar");

unset($fix_errors, $debug_mode);