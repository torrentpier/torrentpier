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
		'genre',
		'developer',
		'publisher',
		'platform',
		'region',
		'medium',
		'age',
		'sysreq',
		'source_type',
		'firmware',
		'cd_image_type',
		'can_play_xbox360',
		'localization',
		'local_voice',
		'translation_type',
		'medicine',
		'multiplayer',
		'description',
		'moreinfo',
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
		$msg[$item] = @$_POST['msg'][$item];
	}
	array_deep($msg, 'trim');

	if ($msg)
	{
		$message = tpl_build_message($msg);
		$subject = $msg['release_name'];
		$subject .= ($msg['year']) ? ' ('. trim($msg['year'], '/') .')' : '';
		$subject .= ($msg['publisher']) ? ' ('. trim($msg['publisher'], '/') .')' : '';

		if ($msg['localization'] == $lang['TPL']['UI_LANG_OPTIONS'][0]) $subject .= ' (ENG+RUS)';
		if ($msg['localization'] == $lang['TPL']['UI_LANG_OPTIONS'][1]) $subject .= ' (ENG)';
		if ($msg['localization'] == $lang['TPL']['UI_LANG_OPTIONS'][2]) $subject .= ' (RUS)';
		if ($msg['localization'] == $lang['TPL']['UI_LANG_OPTIONS'][3]) $subject .= ' (DEU)';
		
		if ($msg['source_type'] == $lang['TPL']['SOURCE_TYPE_OPTIONS'][0]) $subject .= ' [L]';
		if ($msg['source_type'] == $lang['TPL']['SOURCE_TYPE_OPTIONS'][1]) $subject .= ' [P]';
		if ($msg['source_type'] == $lang['TPL']['SOURCE_TYPE_OPTIONS'][2]) $subject .= ' [Rip]';
		if ($msg['source_type'] == $lang['TPL']['SOURCE_TYPE_OPTIONS'][3]) $subject .= ' [RePack]';
	}
}