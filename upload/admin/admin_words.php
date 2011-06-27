<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Word_Censor'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

if (!$bb_cfg['use_word_censor'])
{
	bb_die('Word Censor disabled <br /><br /> ($bb_cfg[\'use_word_censor\'] in config.php)');
}

$mode = request_var('mode', '');
$mode = htmlspecialchars($mode);
//
// These could be entered via a form button
//
if( isset($_POST['add']) )
{
	$mode = "add";
}
else if( isset($_POST['save']) )
{
	$mode = "save";
}

if( $mode != "" )
{
	if( $mode == "edit" || $mode == "add" )
	{
		$word_id = intval(request_var('id', 0));

		$s_hidden_fields = $word = $replacement = '';

		if( $mode == "edit" )
		{
			if( $word_id )
			{
				$sql = "SELECT *
					FROM " . BB_WORDS . "
					WHERE word_id = $word_id";
				if(!$result = DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Could not query words table", "Error", __LINE__, __FILE__, $sql);
				}

				$word_info = DB()->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
				$word        = $word_info['word'];
				$replacement = $word_info['replacement'];
			}
			else
			{
				message_die(GENERAL_MESSAGE, $lang['NO_WORD_SELECTED']);
			}
		}

		$template->assign_vars(array(
			'TPL_ADMIN_WORDS_EDIT' => true,

			"WORD"        => $word,
			"REPLACEMENT" => $replacement,

			"L_WORDS_TEXT"  => $lang['WORDS_EXPLAIN'],
			"L_WORD_CENSOR" => $lang['EDIT_WORD_CENSOR'],

			"S_WORDS_ACTION"  => append_sid("admin_words.php"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);
	}
	else if( $mode == "save" )
	{
		$word_id     = intval(request_var('id', 0));
		$word        = trim(request_var('word', ""));
		$replacement = trim(request_var('replacement', ""));

		if($word == "" || $replacement == "")
		{
			message_die(GENERAL_MESSAGE, $lang['MUST_ENTER_WORD']);
		}

		if( $word_id )
		{
			$sql = "UPDATE " . BB_WORDS . "
				SET word = '" . str_replace("\'", "''", $word) . "', replacement = '" . str_replace("\'", "''", $replacement) . "'
				WHERE word_id = $word_id";
			$message = $lang['WORD_UPDATED'];
		}
		else
		{
			$sql = "INSERT INTO " . BB_WORDS . " (word, replacement)
				VALUES ('" . str_replace("\'", "''", $word) . "', '" . str_replace("\'", "''", $replacement) . "')";
			$message = $lang['WORD_ADDED'];
		}

		if(!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not insert data into words table", $lang['ERROR'], __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['CLICK_RETURN_WORDADMIN'], "<a href=\"" . append_sid("admin_words.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}
	else if( $mode == "delete" )
	{
		$word_id = intval(request_var('id', 0));

		if( $word_id )
		{
			$sql = "DELETE FROM " . BB_WORDS . "
				WHERE word_id = $word_id";

			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not remove data from words table", $lang['ERROR'], __LINE__, __FILE__, $sql);
			}

			$message = $lang['WORD_REMOVED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_WORDADMIN'], "<a href=\"" . append_sid("admin_words.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['NO_WORD_SELECTED']);
		}
	}
}
else
{
	$sql = "SELECT *
		FROM " . BB_WORDS . "
		ORDER BY word";
	if( !$result = DB()->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query words table", $lang['ERROR'], __LINE__, __FILE__, $sql);
	}

	$word_rows = DB()->sql_fetchrowset($result);
	$word_count = count($word_rows);

	$template->assign_vars(array(
		'TPL_ADMIN_WORDS_LIST' => true,

		"L_WORDS_TEXT" => $lang['WORDS_EXPLAIN'],
		"L_ADD_WORD" => $lang['ADD_NEW_WORD'],

		"S_WORDS_ACTION" => append_sid("admin_words.php"),
		"S_HIDDEN_FIELDS" => '')
	);

	for($i = 0; $i < $word_count; $i++)
	{
		$word = $word_rows[$i]['word'];
		$replacement = $word_rows[$i]['replacement'];
		$word_id = $word_rows[$i]['word_id'];

		$row_class = !($i % 2) ? 'row1' : 'row2';

		$template->assign_block_vars("words", array(
			"ROW_CLASS" => $row_class,
			"WORD" => $word,
			"REPLACEMENT" => $replacement,

			"U_WORD_EDIT" => append_sid("admin_words.php?mode=edit&amp;id=$word_id"),
			"U_WORD_DELETE" => append_sid("admin_words.php?mode=delete&amp;id=$word_id"))
		);
	}
}

print_page('admin_words.tpl', 'admin');
