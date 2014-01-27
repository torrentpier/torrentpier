<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));
if (!$post_info) die('$post_info missing');

function build_tpl_item ($item, $val)
{
	if (strpos($item, '--BR--') === 0)
	{
		return "\n\n";
	}
	if (!$val)
	{
		return '';
	}

	if (function_exists("tpl_func_$item"))
	{
		return call_user_func("tpl_func_$item", $item, $val);
	}
	else if (isset($GLOBALS['tpl_sprintf'][$item]))
	{
		return sprintf($GLOBALS['tpl_sprintf'][$item], $val);
	}
	else
	{
		return '[b]'. $GLOBALS['lang']['TPL'][strtoupper($item)] .'[/b]: '. $val ."\n";
	}
}

function tpl_build_message ($msg)
{
	$message = '';
	foreach ($msg as $item => $val)
	{
		if (is_array($item))
		{
			$name = array_keys($item);
			$item = $name[0];
		}
		$message .= build_tpl_item($item, $val);
	}
	return $message;
}

function tpl_func_screen_shots ($item, $val)
{
	if (!$val) return '';

	$img = preg_replace('#(?<=\s)(http\S+?(jpg|gif|png))(?=\s)#i', '[img]$1[/img]', " $val ");

	return '[spoiler="'. $GLOBALS['lang']['TPL'][strtoupper($item)] .'"]' . trim($img) ."\n" ."[/spoiler]";
}

// get tpl data
$sql = "SELECT *
	FROM ". BB_TOPIC_TPL_OLD ."
	WHERE tpl_id = ". (int) $post_info['topic_tpl_id'];

if ($topic_tpl = DB()->fetch_row($sql))
{
	$message = $subject = '';
	$tpl_script = basename($topic_tpl['tpl_script']);

	// this include() should return $message and $subject on submit
	require(INC_DIR ."topic_templates/$tpl_script.php");

	$lang['TPL']['GUIDE'] = array();
	@include(INC_DIR ."topic_templates/{$tpl_script}_guide.php");

	if (isset($_REQUEST['preview']))
	{
		$_POST['subject'] = $subject;
		$_POST['message'] = $message;
	}
	else
	{
		require(INC_DIR .'topic_templates/tpl_selects.php');

		$template->assign_vars(array(
			'PAGE_TITLE'        => $lang['NEW_RELEASE'],
			'FORUM_NAME'        => htmlCHR($post_info['forum_name']),
			'S_ACTION'          => POSTING_URL . "?mode=newtopic&tpl=1&". POST_FORUM_URL .'='. $post_info['forum_id'],
			'S_CANCEL_ACTION'   => FORUM_URL . $post_info['forum_id'],
			'TORRENT_EXT'       => TORRENT_EXT,
			'TORRENT_EXT_LEN'   => strlen(TORRENT_EXT) + 1,
			'U_VIEW_FORUM'      => FORUM_URL . $post_info['forum_id'],

			'REGULAR_TOPIC_BUTTON' => true, # (IS_AM),
			'REGULAR_TOPIC_HREF'   => POSTING_URL . "?mode=newtopic&". POST_FORUM_URL .'='. $post_info['forum_id'],

			'L_TITLE'           => $lang['TPL']['RELEASE_NAME'],
			'L_TITLE_DESC'      => $lang['TPL']['RELEASE_NAME_DESC'],
			'L_ORIGINAL_TITLE'  => $lang['TPL']['ORIGINAL_NAME'],
			'L_ORIGINAL_TITLE_DESC' => $lang['TPL']['ORIGINAL_NAME_DESC'],
			'L_TITLE_EXP'       => $lang['TPL']['NAME_EXP'],

			'TORRENT_SIGN'      => $bb_cfg['torrent_sign'],
		));

		foreach ($lang['TPL'] as $name => $val)
		{
			$template->assign_vars(array(
				'L_'. strtoupper($name) => $val,
			));
		}
		foreach ($lang['TPL']['GUIDE'] as $name => $guide_post_id)
		{
			$template->assign_vars(array(
				strtoupper($name) .'_HREF' => POST_URL ."$guide_post_id&amp;single=1#$guide_post_id",
			));
		}

		$tpl_file = basename($topic_tpl['tpl_template']) .'.tpl';

		print_page("topic_templates/$tpl_file");
	}
}