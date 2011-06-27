<?php
	
define ('IN_PHPBB', true);
define ('IN_SERVICE', true);
require ("./common.php");
require ('./includes/functions_torrent.php');
require ("./converter/settings.php");
require ("./converter/functions.php");

// Init userdata
$user->session_start();

while (@ob_end_flush());
ob_implicit_flush();

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
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

$torrents_count = (int) get_count(BT_TORRENTS_TABLE, 'attach_id');
$loops = (int) ceil($torrents_count / C_TORRENTS_PER_ONCE);

$not_exist = array();

$attach_dir = get_attachments_dir() .'/';
	
for ($i = 0; $i < $loops; $i++)
{
	$start = $i * C_TORRENTS_PER_ONCE;
	$offset = C_TORRENTS_PER_ONCE;	
	
	$sql = "SELECT 
				tor.attach_id, tor.topic_id, ad.physical_filename
			FROM ". BT_TORRENTS_TABLE ." tor
			LEFT JOIN ". ATTACHMENTS_DESC_TABLE ." ad ON(ad.attach_id = tor.attach_id)
			ORDER BY tor.attach_id
			LIMIT $start, $offset";
	
	$torrents = $db->fetch_rowset($sql);
	$db->sql_freeresult();
		
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
			$info_hash_sql = rtrim($db->escape($info_hash), ' ');
		
			$db->query("UPDATE 	". BT_TORRENTS_TABLE ."
						SET info_hash = '$info_hash_sql'
						WHERE attach_id = {$torrent['attach_id']}");
		}
	}
}

print_ok ("Completed");

if(!empty($not_exist))
{
	print_ok ("These torrents doesn't exist in filesystem: ". implode(', ', array_unique($not_exist)));
}

}
