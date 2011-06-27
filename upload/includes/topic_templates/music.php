<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));
if (!$topic_tpl) die('$topic_tpl missing');

$img_align = '=right';
$title_font_size = 24;

if (@$_REQUEST['preview'] && is_array($_POST['msg']))
{
	// this also define item's order to process
	$tpl_items = array(
		'release_name',
		'cover',
		'country',
		'genre',
		'year',
		'format',
		'audio_bitrate',
		'playtime',
		'tracklist',
		'moreinfo',
	);

	$tpl_sprintf = array(
		'cover'        => "[img{$img_align}]%s[/img]\n\n",
		'release_name' => "[size=$title_font_size]%s[/size]\n\n",
		'tracklist'    => "[b]{$lang['TPL']['TRACKLIST']}[/b]:\n%s\n",
	);

	$message = $subject = '';
	$msg = $sbj_ext = array();

	foreach ($tpl_items as $item)
	{
		$msg[$item] = @$_POST['msg'][$item];
	}
	array_deep($msg, 'trim');

	if ($msg)
	{
		$message = tpl_build_message($msg);

		$genre = ($msg['genre']) ? '('. $msg['genre'] .') ' : '';
		$year = ($msg['year']) ? ' - '. $msg['year'] : '';
		$bitrate = ($msg['audio_bitrate']) ? ', '. $msg['audio_bitrate'] : '';
		$format = ($msg['format']) ? ', '. $msg['format'] . $bitrate .'' : '';

		$subject = $genre . $msg['release_name'] . $year . $format;
	}
}