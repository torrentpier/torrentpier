<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));
if (!$topic_tpl) die('$topic_tpl missing');

$img_align = '=right';
$title_font_size = 24;

function tpl_func_framesize ($item, $val)
{
	if (!$val['framesize_x'] || !$val['framesize_y']) return '';
	return '[b]'. $GLOBALS['lang']['TPL'][strtoupper($item)] .'[/b]: '. $val['framesize_x'] .'x'. $val['framesize_y'] ."\n";
}

function tpl_func_manufacturer ($item, $val)
{
	if (!$val['name'] || !$val['url']) return '';
	return '[b]'. $GLOBALS['lang']['TPL'][strtoupper($item)] .'[/b]: ' . "[url={$val['url']}]{$val['name']}[/url]" ."\n";
}

if (isset($_REQUEST['preview']) && is_array($_POST['msg']))
{
	$tpl_items = array(
		'release_name',
		'picture',
		'original_name',
		'manufacturer',
		'year',
		'lang',
		'country',
		'genre',
		'playtime',
		'translation',
		'--BR--1',
		'director',
		'--BR--2',
		'casting',
		'--BR--3',
		'description',
		'--BR--4',
		'moreinfo',
		'--BR--5',
		'quality',
		'format',
		'video_codec',
		'audio_codec',
		'video',
		'audio',
		'screen_shots',
	);

	$tpl_sprintf = array(
		'picture'      => "[img{$img_align}]%s[/img]\n\n",
		'release_name' => "[size=$title_font_size]%s[/size]\n\n",
	);

	$message = $subject = '';
	$msg = $sbj_ext = array();

	foreach ($tpl_items as $item)
	{
		$msg[$item] = !empty($_POST['msg'][$item]) ? $_POST['msg'][$item] : '';
	}
	array_deep($msg, 'trim');

	if ($msg)
	{
		if ($msg['original_name'])
		{
			$msg['release_name'] .= ' / '. trim($msg['original_name'], '/');
			unset($msg['original_name']);
		}

		// Subject
		$subject = $msg['release_name'];
		$subject .= ($msg['director']) ? ' ('. trim($msg['director'], '/') .')' : '';

		if ($msg['year'])
		{
			$sbj_ext[] = sprintf($lang['TPL']['Y'], $msg['year']);
		}
		if ($msg['genre'])
		{
			$sbj_ext[] = $msg['genre'];
		}
		if ($msg['quality'])
		{
			$sbj_ext[] = $msg['quality'];
		}
		if ($sbj_ext)
		{
			$subject .= ' ['. join(', ', $sbj_ext) .']';
		}

		// Message
		$message = tpl_build_message($msg);
	}
}