<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));
if (!$topic_tpl) die('$topic_tpl missing');

$img_align = '=right';
$title_font_size = 24;

if (@$_REQUEST['preview'] && is_array($_POST['msg']))
{
	$tpl_items = array(
		'release_name',
		'picture',
		'year',
		'author',
		'performer',
		'genre',
		'publisher',
		'audiobook_type',
		'audio_codec',
		'audio_bitrate',
		'description',
		'moreinfo',
	);

	$tpl_sprintf = array(
		'picture'      => "[img{$img_align}]%s[/img]",
		'release_name' => "[size=$title_font_size]%s[/size]\n\n",
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
		// Subject
		$subject = $msg['release_name'];

		if ($msg['author'])
		{
			$subject = $msg['author'] .' - '. $subject;
		}

		if ($msg['performer'])
		{
			$sbj_ext[] = $msg['performer'];
		}
		if ($msg['year'])
		{
			$sbj_ext[] = $msg['year'];
		}
		if ($sbj_ext)
		{
			$subject .= ' ['. join(', ', $sbj_ext) .']';
		}

		// Message
		$message = tpl_build_message($msg);
	}
}