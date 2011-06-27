<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));
if (!$topic_tpl) die('$topic_tpl missing');

$img_align = '=right';
$title_font_size = 24;

function tpl_func_framesize ($item, $val)
{
	if (!$val['framesize_x'] || !$val['framesize_y']) return '';
	return '[b]'. $GLOBALS['lang']['TPL'][$item] .'[/b]: '. $val['framesize_x'] .'x'. $val['framesize_y'] ."\n";
}

if (isset($_REQUEST['preview']) && is_array($_POST['msg']))
{
	$tpl_items = array(
		'release_name',
		'picture',
		'year',
		'sport_type',
		'participants',
		'playtime',
		'comments',
		'description',
		'moreinfo',
		'quality',
		'format',
		'video_codec',
		'audio_codec',
		'video',
		'audio',
	);

	$tpl_sprintf = array(
		'picture'      => "[img{$img_align}]%s[/img]\n\n",
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

		if ($msg['year'])
		{
			$sbj_ext[] = sprintf($lang['TPL']['Y'], $msg['year']);
		}
		if ($msg['sport_type'])
		{
			$sbj_ext[] = $msg['sport_type'];
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