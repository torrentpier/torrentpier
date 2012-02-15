<?php

/***************************************************************************
 *                            lang_user_search.php (English)
 *                              -------------------
 *     begin                : Sat Apr 10, 2004
 *     copyright            : (C) 2004 Adam Alkins
 *     email                : phpbb at rasadam dot com
 *	   $Id: lang_user_search.php,v 1.10 2004/12/31 05:26:54 rasadam Exp $
 *
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

$lang['SEARCH_INVALID_USERNAME'] = 'Invalid username entered to Search';
$lang['SEARCH_INVALID_EMAIL'] = 'Invalid email address entered to Search';
$lang['SEARCH_INVALID_IP'] = 'Invalid IP address entered to Search';
$lang['SEARCH_INVALID_GROUP'] = 'Invalid Group entered to Search';
$lang['SEARCH_INVALID_RANK'] = 'Invalid rank entered to Search';
$lang['SEARCH_INVALID_DATE'] = 'Invalid Date entered to Search';
$lang['SEARCH_INVALID_POSTCOUNT'] = 'Invalid Post Count entered to Search';
$lang['SEARCH_INVALID_USERFIELD'] = 'Invalid Userfield data entered';
$lang['SEARCH_INVALID_LASTVISITED'] = 'Invalid data entered for Last Visited search';
$lang['SEARCH_INVALID_LANGUAGE'] = 'Invalid Language Selected';
$lang['SEARCH_INVALID_STYLE'] = 'Invalid Style Selected';
$lang['SEARCH_INVALID_TIMEZONE'] = 'Invalid Timezone Selected';
$lang['SEARCH_INVALID_MODERATORS'] = 'Invalid Forum Selected';
$lang['SEARCH_INVALID'] = 'Invalid Search';
$lang['SEARCH_INVALID_DAY'] = 'The day you entered was invalid';
$lang['SEARCH_INVALID_MONTH'] = 'The day you entered was invalid';
$lang['SEARCH_INVALID_YEAR'] = 'The day you entered was invalid';
$lang['SEARCH_NO_REGEXP'] = 'Your database does not support Regular Expression searching.';
$lang['SEARCH_FOR_USERNAME'] = 'Searching usernames matching %s';
$lang['SEARCH_FOR_EMAIL'] = 'Searching email addresses matching %s';
$lang['SEARCH_FOR_IP'] = 'Searching IP addresses matching %s';
$lang['SEARCH_FOR_DATE'] = 'Searching users who joined %s %d/%d/%d';
$lang['SEARCH_FOR_GROUP'] = 'Searching group members of %s';
$lang['SEARCH_FOR_RANK'] = 'Searching carriers rank of %s';
$lang['SEARCH_FOR_BANNED'] = 'Searching banned users';
$lang['SEARCH_FOR_ADMINS'] = 'Searching Administrators';
$lang['SEARCH_FOR_MODS'] = 'Searching Moderators';
$lang['SEARCH_FOR_DISABLED'] = 'Searching for disabled users';
$lang['SEARCH_FOR_DISABLED_PMS'] = 'Searching for users with disabled Private Messages';
$lang['SEARCH_FOR_POSTCOUNT_GREATER'] = 'Searching for users with a post count greater than %d';
$lang['SEARCH_FOR_POSTCOUNT_LESSER'] = 'Searching for users with a post count less than %d';
$lang['SEARCH_FOR_POSTCOUNT_RANGE'] = 'Searching for users with a post count between %d and %d';
$lang['SEARCH_FOR_POSTCOUNT_EQUALS'] = 'Searching for users with a post count value of %d';
$lang['SEARCH_FOR_USERFIELD_ICQ'] = 'Searching for users with a ICQ address matching %s';
$lang['SEARCH_FOR_USERFIELD_WEBSITE'] = 'Searching for users with an Website matching %s';
$lang['SEARCH_FOR_USERFIELD_LOCATION'] = 'Searching for users with a Location matching %s';
$lang['SEARCH_FOR_USERFIELD_INTERESTS'] = 'Searching for users with their Interests field matching %s';
$lang['SEARCH_FOR_USERFIELD_OCCUPATION'] = 'Searching for users with their Occupation field matching %s';
$lang['SEARCH_FOR_LASTVISITED_INTHELAST'] = 'Searching for users who have visited in the last %s %s';
$lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'] = 'Searching for users who have visited after the last %s %s';
$lang['SEARCH_FOR_LANGUAGE'] = 'Searching for users who have set %s as their language';
$lang['SEARCH_FOR_TIMEZONE'] = 'Searching for users who have set GMT %s as their timezone';
$lang['SEARCH_FOR_STYLE'] = 'Searching for users who have set %s as their style';
$lang['SEARCH_FOR_MODERATORS'] = 'Search for moderators of the Forum -> %s';
$lang['SEARCH_USERS_ADVANCED'] = 'Advanced User Search';
$lang['SEARCH_USERS_EXPLAIN'] = 'This Module allows you to perform advanced searches for users on a wide range of criteria. Please read the descriptions under each field to understand each search option completely.';
$lang['SEARCH_USERNAME_EXPLAIN'] = 'Here you can perform a case insensitive search for usernames. If you would like to match part of the username, use * (an asterix) as a wildcard. Checking the Regular Expressions box will allow you to search based on your regex pattern.';
$lang['SEARCH_EMAIL_EXPLAIN'] = 'Enter an expression to match a user\'s email address. This is case insensitive. If you want to do a partial match, use * (an asterix) as a wildcard. Checking the Regular Expressions box will allow you to search based on your regex pattern.';
$lang['SEARCH_IP_EXPLAIN'] = 'Search for users by a specific IP address (xxx.xxx.xxx.xxx), wildcard (xxx.xxx.xxx.*) or range (xxx.xxx.xxx.xxx-yyy.yyy.yyy.yyy). Note: the last quad .255 is considered the range of all the IPs in that quad. If you enter 10.0.0.255, it is just like entering 10.0.0.* (No IP is assigned .255 for that matter, it is reserved). Where you may encounter this is in ranges, 10.0.0.5-10.0.0.255 is the same as "10.0.0.*" . You should really enter 10.0.0.5-10.0.0.254 .';
$lang['SEARCH_USERS_JOINED'] = 'Users that joined';
$lang['SEARCH_USERS_LASTVISITED'] = 'Users whom have visited';
$lang['IN_THE_LAST'] = 'in the last';
$lang['AFTER_THE_LAST'] = 'after the last';
$lang['BEFORE'] = 'Before';
$lang['AFTER'] = 'After';
$lang['SEARCH_USERS_JOINED_EXPLAIN'] = 'Search for users the join Before or After (and on) a specific date. The date format is YYYY/MM/DD.';
$lang['SEARCH_USERS_GROUPS_EXPLAIN'] = 'View all members of the selected group.';
$lang['SEARCH_USERS_RANKS_EXPLAIN'] = 'View all carriers of the selected rank.';
$lang['ADMINISTRATORS'] = 'Administrators';
$lang['BANNED_USERS'] = 'Banned Users';
$lang['DISABLED_USERS'] = 'Disabled Users';
$lang['USERS_DISABLED_PMS'] = 'Users with disabled PMs';
$lang['SEARCH_USERS_MISC_EXPLAIN'] = 'Administrators - All users with Administrator powers; Moderators - All forum moderators; Banned Users - All accounts that have been banned on these forums; Disabled Users - All users with disabled accounts (either manually disabled or never verified their email address); Users with disabled PMs - Selects users who have the Private Messages priviliges removed (Done via User Management)';
$lang['POSTCOUNT'] = 'Postcount';
$lang['EQUALS'] = 'Equals';
$lang['GREATER_THAN'] = 'Greater than';
$lang['LESS_THAN'] = 'Less than';
$lang['SEARCH_USERS_POSTCOUNT_EXPLAIN'] = 'You can search for users based on the Postcount value. You can either search by a specific value, greater than or lesser than a value or between two values. To do the range search, select "Equals" then put the beginning and ending values of the range separated by a dash (-), e.g. 10-15';
$lang['USERFIELD'] = 'Userfield';
$lang['SEARCH_USERS_USERFIELD_EXPLAIN'] = 'Search for users based on various profile fields. Wildcards are supported using an asterix (*). Checking the Regular Expressions box will allow you to search based on your regex pattern.';
$lang['SEARCH_USERS_LASTVISITED_EXPLAIN'] = 'You can search for users based on their last login date using this search option';
$lang['SEARCH_USERS_LANGUAGE_EXPLAIN'] = 'This will display users whom have selected a specific language in their Profile';
$lang['SEARCH_USERS_TIMEZONE_EXPLAIN'] = 'Users who have selected a specific timezone in their profile';
$lang['SEARCH_USERS_STYLE_EXPLAIN'] = 'Display users who have selected a specific style.';
$lang['MODERATORS_OF'] = 'Moderators of';
$lang['SEARCH_USERS_MODERATORS_EXPLAIN'] = 'Search for users with Moderating permissions to a specific forum. Moderating permissions are recoginised either by User Permissions or by being in a Group with the right Group Permssions.';
$lang['REGULAR_EXPRESSION'] = 'Regular Expression?';

$lang['MANAGE'] = 'Manage';
$lang['SEARCH_USERS_NEW'] = '%s yielded %d result(s). Perform <a href="%s">another search</a>.';
$lang['BANNED'] = 'Banned';
$lang['NOT_BANNED'] = 'Not Banned';
$lang['SEARCH_NO_RESULTS'] = 'No users match your selected criteria. Please try another search. If you\'re searching the username or email address fields, for partial matches you must use the wildcard * (an asterix).';
$lang['ACCOUNT_STATUS'] = 'Account Status';
$lang['SORT_OPTIONS'] = 'Sort options:';
$lang['LAST_VISIT'] = 'Last Visit';
$lang['DAY'] = 'Day';