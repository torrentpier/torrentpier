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
		'genre',
		'publisher',
		'edition',
		'isbn',
		'format',
		'quality',
		'pages_count',
		'description',
		'moreinfo',
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

		if ($msg['author'])
		{
			$subject = $msg['author'] .' - '. $subject;
		}

		if ($msg['year'])
		{
			$sbj_ext[] = $msg['year'];
		}
		if ($msg['format'])
		{
			$sbj_ext[] = $msg['format'];
		}
		if ($sbj_ext)
		{
			$subject .= ' ['. join(', ', $sbj_ext) .']';
		}

		// Message
		$message = tpl_build_message($msg);
	}
}