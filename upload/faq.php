<?php

define('IN_PHPBB', true);
define('BB_SCRIPT', 'faq');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

// Start session management
$user->session_start();

// Set vars to prevent naughtiness
$faq = array();
//
// Load the appropriate faq file
//
$mode = request_var('mode', '');
if(!empty($mode))
{
	switch($mode)
	{
		case 'bbcode':
			$lang_file = 'lang_bbcode';
			$l_title = $lang['BBCODE_GUIDE'];
			break;
		default:
			$lang_file = 'lang_faq';
			$l_title = $lang['FAQ'];
			break;
	}
}
else
{
	$lang_file = 'lang_faq';
	$l_title = $lang['FAQ'];
}
include(LANG_ROOT_DIR ."lang_{$bb_cfg['default_lang']}/$lang_file.php");
include(LANG_ROOT_DIR ."lang_{$bb_cfg['default_lang']}/lang_faq_attach.php");

//
// Pull the array data from the lang pack
//
$j = 0;
$counter = 0;
$counter_2 = 0;
$faq_block = array();
$faq_block_titles = array();

for($i = 0; $i < count($faq); $i++)
{
	if( $faq[$i][0] != '--' )
	{
		$faq_block[$j][$counter]['id'] = $counter_2;
		$faq_block[$j][$counter]['question'] = $faq[$i][0];
		$faq_block[$j][$counter]['answer'] = $faq[$i][1];

		$counter++;
		$counter_2++;
	}
	else
	{
		$j = ( $counter != 0 ) ? $j + 1 : 0;

		$faq_block_titles[$j] = $faq[$i][1];

		$counter = 0;
	}
}

//
// Lets build a page ...
//
$template->assign_vars(array(
	'PAGE_TITLE' => $l_title,
	'L_FAQ_TITLE' => $l_title,
));

for($i = 0; $i < count($faq_block); $i++)
{
	if( count($faq_block[$i]) )
	{
		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE' => $faq_block_titles[$i])
		);
		$template->assign_block_vars('faq_block_link', array(
			'BLOCK_TITLE' => $faq_block_titles[$i])
		);

		for($j = 0; $j < count($faq_block[$i]); $j++)
		{
			$row_class = !($j % 2) ? 'row1' : 'row2';

			$template->assign_block_vars('faq_block.faq_row', array(
				'ROW_CLASS' => $row_class,
				'FAQ_QUESTION' => $faq_block[$i][$j]['question'],
				'FAQ_ANSWER' => $faq_block[$i][$j]['answer'],

				'U_FAQ_ID' => $faq_block[$i][$j]['id'])
			);

			$template->assign_block_vars('faq_block_link.faq_row_link', array(
				'ROW_CLASS' => $row_class,
				'FAQ_LINK' => $faq_block[$i][$j]['question'],

				'U_FAQ_LINK' => '#' . $faq_block[$i][$j]['id'])
			);
		}
	}
}

print_page('faq.tpl');