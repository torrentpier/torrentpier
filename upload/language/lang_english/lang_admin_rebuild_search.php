<?php

/***************************************************************************
 *                       lang_admin_rebuild_search.php [English]
 *                       ---------------------------------------
 *     begin                : Mon Aug 22 2005
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin_rebuild_search.php,v 2.2.2.0 2006/02/04 18:38:17 chatasos Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

$lang['REBUILD_SEARCH'] = 'Rebuild Search Index';
$lang['REBUILD_SEARCH_DESC'] = 'This mod will index every post in your forum, rebuilding the search tables.
You can stop whenever you like and the next time you run it again you\'ll have the option of continuing from where you left off.<br /><br />
It may take a long time to show its progress (depending on "Posts per cycle" and "Time limit"),
so please do not move from its progress page until it is complete, unless of course you want to interrupt it.';

//
// Input screen
//
$lang['STARTING_POST_ID'] = 'Starting post_id';
$lang['STARTING_POST_ID_EXPLAIN'] = 'First post where processing will begin from<br />You can choose to start from the beginning or from the post you last stopped';

$lang['START_OPTION_BEGINNING'] = 'start from beginning';
$lang['START_OPTION_CONTINUE'] = 'continue from last stopped';

$lang['CLEAR_SEARCH_TABLES'] = 'Clear search tables';
$lang['CLEAR_SEARCH_TABLES_EXPLAIN'] = '';
$lang['CLEAR_SEARCH_NO'] = 'NO';
$lang['CLEAR_SEARCH_DELETE'] = 'DELETE';
$lang['CLEAR_SEARCH_TRUNCATE'] = 'TRUNCATE';

$lang['NUM_OF_POSTS'] = 'Number of posts';
$lang['NUM_OF_POSTS_EXPLAIN'] = 'Number of total posts to process<br />It\'s automatically filled with the number of total/remaining posts found in the db';

$lang['POSTS_PER_CYCLE'] = 'Posts per cycle';
$lang['POSTS_PER_CYCLE_EXPLAIN'] = 'Number of posts to process per cycle<br />Keep it low to avoid php/webserver timeouts';

$lang['REFRESH_RATE'] = 'Refresh rate';
$lang['REFRESH_RATE_EXPLAIN'] = 'How much time (secs) to stay idle before moving to next processing cycle<br />Usually you don\'t have to change this';

$lang['TIME_LIMIT'] = 'Time limit';
$lang['TIME_LIMIT_EXPLAIN'] = 'How much time (secs) post processing can last before moving to next cycle';
$lang['TIME_LIMIT_EXPLAIN_SAFE'] = '<i>Your php (safe mode) has a timeout of %s secs configured, so stay below this value</i>';
$lang['TIME_LIMIT_EXPLAIN_WEBSERVER'] = '<i>Your webserver has a timeout of %s secs configured, so stay below this value</i>';

$lang['DISABLE_BOARD'] = 'Disable board';
$lang['DISABLE_BOARD_EXPLAIN'] = 'Whether or not to disable your board while processing';
$lang['DISABLE_BOARD_EXPLAIN_ENABLED'] = 'It will be enabled automatically after the end of processing';
$lang['DISABLE_BOARD_EXPLAIN_ALREADY'] = '<i>Your board is already disabled</i>';

//
// Information strings
//
$lang['INFO_PROCESSING_STOPPED'] = 'You last stopped the processing at post_id %s (%s processed posts) on %s';
$lang['INFO_PROCESSING_ABORTED'] = 'You last aborted the processing at post_id %s (%s processed posts) on %s';
$lang['INFO_PROCESSING_ABORTED_SOON'] = 'Please wait some mins before you continue...';
$lang['INFO_PROCESSING_FINISHED'] = 'You successfully finished the processing (%s processed posts) on %s';
$lang['INFO_PROCESSING_FINISHED_NEW'] = 'You successfully finished the processing at post_id %s (%s processed posts) on %s,<br />but there have been %s new post(s) after that date';

//
// Progress screen
//
$lang['REBUILD_SEARCH_PROGRESS'] = 'Rebuild Search Progress';

$lang['PROCESSED_POST_IDS'] = 'Processed post ids : %s - %s';
$lang['TIMER_EXPIRED'] = 'Timer expired at %s secs. ';
$lang['CLEARED_SEARCH_TABLES'] = 'Cleared search tables. ';
$lang['DELETED_POSTS'] = '%s post(s) were deleted by your users during processing. ';
$lang['PROCESSING_NEXT_POSTS'] = 'Processing next %s post(s). Please wait...';
$lang['ALL_SESSION_POSTS_PROCESSED'] = 'Processed all posts in current session.';
$lang['ALL_POSTS_PROCESSED'] = 'All posts were processed successfully.';
$lang['ALL_TABLES_OPTIMIZED'] = 'All search tables were optimized successfully.';

$lang['PROCESSING_POST_DETAILS'] = 'Processing post';
$lang['PROCESSED_POSTS'] = 'Processed Posts';
$lang['PERCENT'] = 'Percent';
$lang['CURRENT_SESSION'] = 'Current Session';
$lang['TOTAL'] = 'Total';

$lang['PROCESS_DETAILS'] = 'from <b>%s</b> to <b>%s</b> (out of total <b>%s</b>)';
$lang['PERCENT_COMPLETED'] = '%s %% completed';

$lang['PROCESSING_TIME_DETAILS'] = 'Current session details';
$lang['PROCESSING_TIME'] = 'Processing time';
$lang['TIME_LAST_POSTS'] = 'Last %s post(s)';
$lang['TIME_FROM_THE_BEGINNING'] = 'From the beginning';
$lang['TIME_AVERAGE'] = 'Average per cycle';
$lang['TIME_ESTIMATED'] = 'Estimated until finish';

$lang['DAYS'] = 'days';
$lang['HOURS'] = 'hours';
$lang['MINUTES'] = 'minutes';
$lang['SECONDS'] = 'seconds';

$lang['DATABASE_SIZE_DETAILS'] = 'Database size details';
$lang['SIZE_CURRENT'] = 'Current';
$lang['SIZE_ESTIMATED'] = 'Estimated after finish';
$lang['SIZE_SEARCH_TABLES'] = 'Search Tables size';
$lang['SIZE_DATABASE'] = 'Database size';

$lang['BYTES'] = 'Bytes';

$lang['ACTIVE_PARAMETERS'] = 'Active parameters';
$lang['POSTS_LAST_CYCLE'] = 'Processed post(s) on last cycle';
$lang['BOARD_STATUS'] = 'Board status';
$lang['BOARD_DISABLED'] = 'Disabled';
$lang['BOARD_ENABLED'] = 'Enabled';

$lang['INFO_ESTIMATED_VALUES'] = '(*) All the estimated values are calculated approximately<br />
			based on the current completed percent and may not represent the actual final values.<br />
			As the completed percent increases the estimated values will come closer to the actual ones.';

$lang['CLICK_RETURN_REBUILD_SEARCH'] = 'Click %shere%s to return to Rebuild Search';
$lang['REBUILD_SEARCH_ABORTED'] = 'Rebuild search aborted at post_id %s.<br /><br />If you aborted while processing was on, you have to wait for some mins until you run Rebuild Search again, so the last cycle can finish.';
$lang['WRONG_INPUT'] = 'You have entered some wrong values. Please check your input and try again.';

// Buttons
$lang['NEXT'] = 'Next';
$lang['PROCESSING'] = 'Processing...';
$lang['FINISHED'] = 'Finished';