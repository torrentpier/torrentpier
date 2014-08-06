<?php

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(INC_DIR .'functions_torrent.php');
require(BB_ROOT .'converter/settings.php');
require(BB_ROOT .'converter/functions.php');

// Init userdata
$user->session_start();

while (@ob_end_flush());
ob_implicit_flush();

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
		<input type="submit" name="confirm" value="Recover" />
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

$torrents_count = (int) get_count(BB_BT_TORRENTS, 'attach_id');
$loops = (int) ceil($torrents_count / C_TORRENTS_PER_ONCE);

$not_exist = array();

$attach_dir = get_attachments_dir() .'/';

for ($i = 0; $i < $loops; $i++)
{
	$start = $i * C_TORRENTS_PER_ONCE;
	$offset = C_TORRENTS_PER_ONCE;

	$sql = "SELECT
				tor.attach_id, tor.topic_id, ad.physical_filename
			FROM ". BB_BT_TORRENTS ." tor
			LEFT JOIN ". BB_ATTACHMENTS_DESC ." ad ON(ad.attach_id = tor.attach_id)
			ORDER BY tor.attach_id
			LIMIT $start, $offset";

	$torrents = DB()->fetch_rowset($sql);
	DB()->sql_freeresult();

	foreach ($torrents as $torrent)
	{
		$filename = $attach_dir . $torrent['physical_filename'];
		if (!file_exists($filename))
		{
			$not_exist[] = '<a href="viewtopic.php?t='. $torrent['topic_id'] .'">'. $filename .'</a>';
		}
		else
		{
			$tor = bdecode_file($filename);
			$info = (!empty($tor['info'])) ? $tor['info'] : array();
			$info_hash     = pack('H*', sha1(bencode($info)));
			$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');

			DB()->query("UPDATE ". BB_BT_TORRENTS ."
						SET info_hash = '$info_hash_sql'
						WHERE attach_id = {$torrent['attach_id']}");
		}
	}
}

print_ok ("Completed");

if (!empty($not_exist))
{
	print_ok ("These torrents doesn't exist in filesystem: ". implode(', ', array_unique($not_exist)));
}

}