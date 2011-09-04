<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));
if (!$topic_tpl) die('$topic_tpl missing');

$img_align = '=right';
$title_font_size = 24;

function tpl_func_developer ($item, $val)
{
	if (!$val['name'] || !$val['url']) return '';
	return '[b]'. $GLOBALS['lang']['TPL'][strtoupper($item)] .'[/b]: ' . "[url={$val['url']}]{$val['name']}[/url]" ."\n";
}

if (!empty($_REQUEST['preview']) && is_array($_POST['msg']))
{
	$tpl_items = array(
		'release_name',
		'picture',
		'year',
		'version',
		'developer',
		'platform',
		'vista_compatible',
		'sys_requirements',
		'localization',
		'medicine',
		'description',
		'moreinfo',
		'screen_shots',
	);

	$tpl_sprintf = array(
		'release_name' => "[size=$title_font_size]%s[/size]\n\n",
		'picture'      => "[img{$img_align}]%s[/img]\n\n",
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
	}
}