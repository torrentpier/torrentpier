<?php
/***************************************************************************
 *                            lang_admin.php [English]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.35.2.13 2005/12/29 11:51:12 acydburn Exp $
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
/* CONTRIBUTORS
	2002-12-15	Philip M. White (pwhite@mailhaven.com)
		Fixed many minor grammatical mistakes
*/
//
// Format is same as lang_main
//
//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['GENERAL'] = 'General Admin';
$lang['USERS'] = 'User Admin';
$lang['GROUPS'] = 'Group Admin';
$lang['FORUMS'] = 'Forum Admin';

$lang['CONFIGURATION'] = 'Configuration';
$lang['PERMISSIONS'] = 'Permissions';
$lang['MANAGE'] = 'Management';
$lang['DISALLOW'] = 'Disallow names';
$lang['PRUNE'] = 'Pruning';
$lang['MASS_EMAIL'] = 'Mass Email';
$lang['RANKS'] = 'Ranks';
$lang['SMILIES'] = 'Smilies';
$lang['BAN_MANAGEMENT'] = 'Ban Control';
$lang['WORD_CENSOR'] = 'Word Censors';
$lang['EXPORT'] = 'Export';
$lang['CREATE_NEW'] = 'Create';
$lang['ADD_NEW'] = 'Add';
$lang['FLAGS'] = 'Flags';
$lang['FORUM_CONFIG'] = 'Forum settings';
$lang['TRACKER_CONFIG'] = 'Tracker settings';
$lang['RELEASE_TEMPLATES'] = 'Release Templates';

//
// Index
//
$lang['ADMIN'] = 'Administration';
$lang['MAIN_INDEX'] = 'Forum Index';
$lang['FORUM_STATS'] = 'Forum Statistics';
$lang['ADMIN_INDEX'] = 'Admin Index';

$lang['TP_VERSION']      = 'TorrenPier version';
$lang['TP_RELEASE_DATE'] = 'Release date';

$lang['CLICK_RETURN_ADMIN_INDEX'] = 'Click %sHere%s to return to the Admin Index';

$lang['STATISTIC'] = 'Statistic';
$lang['VALUE'] = 'Value';
$lang['NUMBER_POSTS'] = 'Number of posts';
$lang['POSTS_PER_DAY'] = 'Posts per day';
$lang['NUMBER_TOPICS'] = 'Number of topics';
$lang['TOPICS_PER_DAY'] = 'Topics per day';
$lang['NUMBER_USERS'] = 'Number of users';
$lang['USERS_PER_DAY'] = 'Users per day';
$lang['BOARD_STARTED'] = 'Board started';
$lang['AVATAR_DIR_SIZE'] = 'Avatar directory size';
$lang['DATABASE_SIZE'] = 'Database size';
$lang['GZIP_COMPRESSION'] ='Gzip compression';
$lang['NOT_AVAILABLE'] = 'Not available';

$lang['ON'] = 'ON'; // This is for GZip compression
$lang['OFF'] = 'OFF';

// Clear Cache
$lang['CLEAR_CACHE'] = 'Clear Cache';
$lang['DATASTORE'] = 'Datastore';
$lang['DATASTORE_CLEARED'] = 'Datastore cleared';
$lang['TEMPLATES'] = 'Templates';

// Update
$lang['UPDATE'] = 'Update';
$lang['USER_LEVELS'] = 'User levels';
$lang['USER_LEVELS_UPDATED'] = 'User levels updated';

// Synchronize
$lang['SYNCHRONIZE'] = 'Synchronize';
$lang['TOPICS'] = 'Topics';
$lang['TOPICS_DATA_SYNCHRONIZED'] = 'Topics data synchronized';
$lang['USER_POSTS_COUNT'] = 'User posts count';
$lang['USER POSTS COUNT SYNCHRONIZED'] = 'User posts count synchronized';

//
//Welcome page
//
$lang['IDX_BROWSER_NSP_FRAME'] = 'Sorry, your browser doesn\'t seem to support frames';
$lang['IDX_CLEAR_CACHE'] ='Clear Cache:';
$lang['IDX_CLEAR_DATASTORE'] = 'Datastore';
$lang['IDX_CLEAR_TEMPLATES'] = 'Templates';
$lang['IDX_CLEAR_NEWNEWS'] = 'Net news';
$lang['IDX_UPDATE'] = 'Update:';
$lang['IDX_UPDATE_USER_LEVELS'] = 'User levels';
$lang['IDX_SYNCHRONIZE'] = 'Synchronize:';
$lang['IDX_SYNCHRONIZE_TOPICS'] = 'Topics';
$lang['IDX_SYNCHRONIZE_POSTCOUNT'] = 'User posts count';
//
// Welcome page END
//

//
// Auth pages
//
$lang['USER_SELECT'] = 'Select a User';
$lang['GROUP_SELECT'] = 'Select a Group';
$lang['SELECT_A_FORUM'] = 'Select a Forum';
$lang['AUTH_CONTROL_USER'] = 'User Permissions Control';
$lang['AUTH_CONTROL_GROUP'] = 'Group Permissions Control';
$lang['AUTH_CONTROL_FORUM'] = 'Forum Permissions Control';
$lang['LOOK_UP_FORUM'] = 'Look up Forum';

$lang['GROUP_AUTH_EXPLAIN'] = 'Here you can alter the permissions and moderator status assigned to each user group. Do not forget when changing group permissions that individual user permissions may still allow the user entry to forums, etc. You will be warned if this is the case.';
$lang['USER_AUTH_EXPLAIN'] = 'Here you can alter the permissions and moderator status assigned to each individual user. Do not forget when changing user permissions that group permissions may still allow the user entry to forums, etc. You will be warned if this is the case.';
$lang['FORUM_AUTH_EXPLAIN'] = 'Here you can alter the authorisation levels of each forum. You will have both a simple and advanced method for doing this, where advanced offers greater control of each forum operation. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.';

$lang['SIMPLE_MODE'] = 'Simple Mode';
$lang['ADVANCED_MODE'] = 'Advanced Mode';
$lang['MODERATOR_STATUS'] = 'Moderator status';

$lang['ALLOWED_ACCESS'] = 'Allowed Access';
$lang['DISALLOWED_ACCESS'] = 'Disallowed Access';
$lang['IS_MODERATOR'] = 'Is Moderator';
$lang['NOT_MODERATOR'] = 'Not Moderator';

$lang['CONFLICT_WARNING'] = 'Authorisation Conflict Warning';
$lang['CONFLICT_ACCESS_USERAUTH'] = 'This user still has access rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having access rights. The groups granting rights (and the forums involved) are noted below.';
$lang['CONFLICT_MOD_USERAUTH'] = 'This user still has moderator rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having moderator rights. The groups granting rights (and the forums involved) are noted below.';

$lang['CONFLICT_ACCESS_GROUPAUTH'] = 'The following user (or users) still have access rights to this forum via their user permission settings. You may want to alter the user permissions to fully prevent them having access rights. The users granted rights (and the forums involved) are noted below.';
$lang['CONFLICT_MOD_GROUPAUTH'] = 'The following user (or users) still have moderator rights to this forum via their user permissions settings. You may want to alter the user permissions to fully prevent them having moderator rights. The users granted rights (and the forums involved) are noted below.';

$lang['PUBLIC'] = 'Public';
$lang['PRIVATE'] = 'Private';
$lang['REGISTERED'] = 'Registered';
$lang['ADMINISTRATORS'] = 'Administrators';
$lang['HIDDEN'] = 'Hidden';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['FORUM_ALL'] = 'ALL';
$lang['FORUM_REG'] = 'REG';
$lang['FORUM_PRIVATE'] = 'PRIVATE';
$lang['FORUM_MOD'] = 'MOD';
$lang['FORUM_ADMIN'] = 'ADMIN';

$lang['AUTH_VIEW'] = $lang['VIEW'] = 'View';
$lang['AUTH_READ'] = $lang['READ'] = 'Read';
$lang['AUTH_POST'] = $lang['POST'] = 'Post';
$lang['AUTH_REPLY'] = $lang['REPLY'] = 'Reply';
$lang['AUTH_EDIT'] = $lang['EDIT'] = 'Edit';
$lang['AUTH_DELETE'] = $lang['DELETE'] = 'Delete';
$lang['AUTH_STICKY'] = $lang['STICKY'] = 'Sticky';
$lang['AUTH_ANNOUNCE'] = $lang['ANNOUNCE'] = 'Announce';
$lang['AUTH_VOTE'] = $lang['VOTE'] = 'Vote';
$lang['AUTH_POLLCREATE'] = $lang['POLLCREATE'] = 'Poll create';
$lang['AUTH_ATTACHMENTS'] = $lang['AUTH_ATTACH'] = 'Post Files';
$lang['AUTH_DOWNLOAD'] = $lang['AUTH_DOWNLOAD'] = 'Download Files';

$lang['SIMPLE_PERMISSION'] = 'Simple Permissions';

$lang['USER_LEVEL'] = 'User Level';
$lang['AUTH_USER'] = 'User';
$lang['AUTH_ADMIN'] = 'Administrator';
$lang['GROUP_MEMBERSHIPS'] = 'Usergroup memberships';
$lang['USERGROUP_MEMBERS'] = 'This group has the following members';

$lang['FORUM_AUTH_UPDATED'] = 'Forum permissions updated';
$lang['USER_AUTH_UPDATED'] = 'User permissions updated';
$lang['GROUP_AUTH_UPDATED'] = 'Group permissions updated';

$lang['AUTH_UPDATED'] = 'Permissions have been updated';
$lang['CLICK_RETURN_USERAUTH'] = 'Click %sHere%s to return to User Permissions';
$lang['CLICK_RETURN_GROUPAUTH'] = 'Click %sHere%s to return to Group Permissions';
$lang['CLICK_RETURN_FORUMAUTH'] = 'Click %sHere%s to return to Forum Permissions';


//
// Banning
//
$lang['BAN_CONTROL'] = 'Ban Control';
$lang['BAN_EXPLAIN'] = 'Here you can control the banning of users. You can achieve this by banning either or both of a specific user or an individual or range of IP addresses or hostnames. These methods prevent a user from even reaching the index page of your board. To prevent a user from registering under a different username you can also specify a banned email address. Please note that banning an email address alone will not prevent that user from being able to log on or post to your board. You should use one of the first two methods to achieve this.';
$lang['BAN_EXPLAIN_WARN'] = 'Please note that entering a range of IP addresses results in all the addresses between the start and end being added to the banlist. Attempts will be made to minimise the number of addresses added to the database by introducing wildcards automatically where appropriate. If you really must enter a range, try to keep it small or better yet state specific addresses.';

$lang['SELECT_IP'] = 'Select an IP address';
$lang['SELECT_EMAIL'] = 'Select an Email address';

$lang['BAN_USERNAME'] = 'Ban one or more specific users';
$lang['BAN_USERNAME_EXPLAIN'] = 'You can ban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['BAN_IP'] = 'Ban one or more IP addresses or hostnames';
$lang['IP_HOSTNAME'] = 'IP addresses or hostnames';
$lang['BAN_IP_EXPLAIN'] = 'To specify several different IP addresses or hostnames separate them with commas. To specify a range of IP addresses, separate the start and end with a hyphen (-); to specify a wildcard, use an asterisk (*).';

$lang['BAN_EMAIL'] = 'Ban one or more email addresses';
$lang['BAN_EMAIL_EXPLAIN'] = 'To specify more than one email address, separate them with commas. To specify a wildcard username, use * like *@hotmail.com';

$lang['UNBAN_USERNAME'] = 'Un-ban one more specific users';
$lang['UNBAN_USERNAME_EXPLAIN'] = 'You can unban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['UNBAN_IP'] = 'Un-ban one or more IP addresses';
$lang['UNBAN_IP_EXPLAIN'] = 'You can unban multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['UNBAN_EMAIL'] = 'Un-ban one or more email addresses';
$lang['UNBAN_EMAIL_EXPLAIN'] = 'You can unban multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['NO_BANNED_USERS'] = 'No banned usernames';
$lang['NO_BANNED_IP'] = 'No banned IP addresses';
$lang['NO_BANNED_EMAIL'] = 'No banned email addresses';

$lang['BAN_UPDATE_SUCESSFUL'] = 'The banlist has been updated successfully';
$lang['CLICK_RETURN_BANADMIN'] = 'Click %sHere%s to return to Ban Control';


//
// Configuration
//
$lang['GENERAL_CONFIG'] = 'General Configuration';
$lang['CONFIG_EXPLAIN'] = 'The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.';

$lang['CLICK_RETURN_CONFIG'] = 'Click %sHere%s to return to General Configuration';

$lang['GENERAL_SETTINGS'] = 'General Board Settings';
$lang['SITE_NAME'] = 'Site name';
$lang['SITE_DESC'] = 'Site description';
$lang['BOARD_DISABLE'] = 'Disable board';
$lang['BOARD_DISABLE_EXPLAIN'] = 'This will make the board unavailable to users. Administrators are able to access the Administration Panel while the board is disabled.';
$lang['ACCT_ACTIVATION'] = 'Enable account activation';
$lang['ACC_NONE'] = 'None'; // These three entries are the type of activation
$lang['ACC_USER'] = 'User';
$lang['ACC_ADMIN'] = 'Admin';

$lang['ABILITIES_SETTINGS'] = 'User and Forum Basic Settings';
$lang['MAX_POLL_OPTIONS'] = 'Max number of poll options';
$lang['FLOOD_INTERVAL'] = 'Flood Interval';
$lang['FLOOD_INTERVAL_EXPLAIN'] = 'Number of seconds a user must wait between posts';
$lang['BOARD_EMAIL_FORM'] = 'User email via board';
$lang['BOARD_EMAIL_FORM_EXPLAIN'] = 'Users send email to each other via this board';
$lang['TOPICS_PER_PAGE'] = 'Topics Per Page';
$lang['POSTS_PER_PAGE'] = 'Posts Per Page';
$lang['HOT_THRESHOLD'] = 'Posts for Popular Threshold';
$lang['DEFAULT_LANGUAGE'] = 'Default Language';
$lang['DATE_FORMAT'] = 'Date Format';
$lang['SYSTEM_TIMEZONE'] = 'System Timezone';
$lang['ENABLE_PRUNE'] = 'Enable Forum Pruning';
$lang['ALLOW_BBCODE'] = 'Allow BBCode';
$lang['ALLOW_SMILIES'] = 'Allow Smilies';
$lang['SMILIES_PATH'] = 'Smilies Storage Path';
$lang['SMILIES_PATH_EXPLAIN'] = 'Path under your phpBB root dir, e.g. images/smiles';
$lang['ALLOW_SIG'] = 'Allow Signatures';
$lang['MAX_SIG_LENGTH'] = 'Maximum signature length';
$lang['MAX_SIG_LENGTH_EXPLAIN'] = 'Maximum number of characters in user signatures';
$lang['ALLOW_NAME_CHANGE'] = 'Allow Username changes';

$lang['AVATAR_SETTINGS'] = 'Avatar Settings';
$lang['ALLOW_LOCAL'] = 'Enable gallery avatars';
$lang['ALLOW_REMOTE'] = 'Enable remote avatars';
$lang['ALLOW_REMOTE_EXPLAIN'] = 'Avatars linked to from another website';
$lang['ALLOW_UPLOAD'] = 'Enable avatar uploading';
$lang['MAX_FILESIZE'] = 'Maximum Avatar File Size';
$lang['MAX_FILESIZE_EXPLAIN'] = 'For uploaded avatar files';
$lang['MAX_AVATAR_SIZE'] = 'Maximum Avatar Dimensions';
$lang['MAX_AVATAR_SIZE_EXPLAIN'] = '(Height x Width in pixels)';
$lang['AVATAR_STORAGE_PATH'] = 'Avatar Storage Path';
$lang['AVATAR_STORAGE_PATH_EXPLAIN'] = 'Path under your phpBB root dir, e.g. images/avatars';
$lang['AVATAR_GALLERY_PATH'] = 'Avatar Gallery Path';
$lang['AVATAR_GALLERY_PATH_EXPLAIN'] = 'Path under your phpBB root dir for pre-loaded images, e.g. images/avatars/gallery';

$lang['EMAIL_SETTINGS'] = 'Email Settings';
$lang['ADMIN_EMAIL'] = 'Admin Email Address';
$lang['EMAIL_SIG'] = 'Email Signature';
$lang['EMAIL_SIG_EXPLAIN'] = 'This text will be attached to all emails the board sends';
$lang['USE_SMTP'] = 'Use SMTP Server for email';
$lang['USE_SMTP_EXPLAIN'] = 'Say yes if you want or have to send email via a named server instead of the local mail function';
$lang['SMTP_SERVER'] = 'SMTP Server Address';
$lang['SMTP_USERNAME'] = 'SMTP Username';
$lang['SMTP_USERNAME_EXPLAIN'] = 'Only enter a username if your SMTP server requires it';
$lang['SMTP_PASSWORD'] = 'SMTP Password';
$lang['SMTP_PASSWORD_EXPLAIN'] = 'Only enter a password if your SMTP server requires it';

$lang['DISABLE_PRIVMSG'] = 'Private Messaging';
$lang['INBOX_LIMITS'] = 'Max posts in Inbox';
$lang['SENTBOX_LIMITS'] = 'Max posts in Sentbox';
$lang['SAVEBOX_LIMITS'] = 'Max posts in Savebox';

// Visual Confirmation
$lang['VISUAL_CONFIRM'] = 'Enable Visual Confirmation';
$lang['VISUAL_CONFIRM_EXPLAIN'] = 'Requires users enter a code defined by an image when registering.';

// Autologin Keys - added 2.0.18
$lang['ALLOW_AUTOLOGIN'] = 'Allow automatic logins';
$lang['ALLOW_AUTOLOGIN_EXPLAIN'] = 'Determines whether users are allowed to select to be automatically logged in when visiting the forum';
$lang['AUTOLOGIN_TIME'] = 'Automatic login key expiry';
$lang['AUTOLOGIN_TIME_EXPLAIN'] = 'How long a autologin key is valid for in days if the user does not visit the board. Set to zero to disable expiry.';

//
// Forum Management
//
$lang['FORUM_ADMIN_MAIN'] = 'Forum Administration';
$lang['FORUM_ADMIN_EXPLAIN'] = 'From this panel you can add, delete, edit, re-order and re-synchronise categories and forums';
$lang['EDIT_FORUM'] = 'Edit forum';
$lang['CREATE_FORUM'] = 'Create new forum';
$lang['CREATE_CATEGORY'] = 'Create new category';
$lang['REMOVE'] = 'Remove';
$lang['ACTION'] = 'Action';
$lang['UPDATE_ORDER'] = 'Update Order';
$lang['CONFIG_UPDATED'] = 'Forum Configuration Updated Successfully';
$lang['EDIT'] = 'Edit';
$lang['MOVE_UP'] = 'Move up';
$lang['MOVE_DOWN'] = 'Move down';
$lang['RESYNC'] = 'Resync';
$lang['NO_MODE'] = 'No mode was set';
$lang['FORUM_EDIT_DELETE_EXPLAIN'] = 'The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side';

$lang['MOVE_CONTENTS'] = 'Move all contents';
$lang['FORUM_DELETE'] = 'Delete Forum';
$lang['FORUM_DELETE_EXPLAIN'] = 'The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.';
$lang['CATEGORY_DELETE'] = 'Delete Category';

$lang['STATUS_LOCKED'] = 'Locked';
$lang['STATUS_UNLOCKED'] = 'Unlocked';
$lang['FORUM_SETTINGS'] = 'General Forum Settings';
$lang['FORUM_NAME'] = 'Forum name';
$lang['FORUM_DESC'] = 'Description';
$lang['FORUM_STATUS'] = 'Forum status';
$lang['FORUM_PRUNING'] = 'Auto-pruning';

$lang['PRUNE_DAYS'] = 'Remove topics that have not been posted to in';
$lang['SET_PRUNE_DATA'] = 'You have turned on auto-prune for this forum but did not set a number of days to prune. Please go back and do so.';

$lang['MOVE_AND_DELETE'] = 'Move and Delete';

$lang['DELETE_ALL_POSTS'] = 'Delete all posts';
$lang['NOWHERE_TO_MOVE'] = 'Nowhere to move to';

$lang['EDIT_CATEGORY'] = 'Edit Category';
$lang['EDIT_CATEGORY_EXPLAIN'] = 'Use this form to modify a category\'s name.';

$lang['FORUMS_UPDATED'] = 'Forum and Category information updated successfully';

$lang['MUST_DELETE_FORUMS'] = 'You need to delete all forums before you can delete this category';

$lang['CLICK_RETURN_FORUMADMIN'] = 'Click %sHere%s to return to Forum Administration';

$lang['SHOW_ALL_FORUMS_ON_ONE_PAGE'] = 'Show all forums on one page';

//
// Smiley Management
//
$lang['SMILEY_TITLE'] = 'Smiles Editing Utility';
$lang['SMILE_DESC'] = 'From this page you can add, remove and edit the emoticons or smileys that your users can use in their posts and private messages.';

$lang['SMILEY_CONFIG'] = 'Smiley Configuration';
$lang['SMILEY_CODE'] = 'Smiley Code';
$lang['SMILEY_URL'] = 'Smiley Image File';
$lang['SMILEY_EMOT'] = 'Smiley Emotion';
$lang['SMILE_ADD'] = 'Add a new Smiley';
$lang['SMILE'] = 'Smile';
$lang['EMOTION'] = 'Emotion';

$lang['SELECT_PAK'] = 'Select Pack (.pak) File';
$lang['REPLACE_EXISTING'] = 'Replace Existing Smiley';
$lang['KEEP_EXISTING'] = 'Keep Existing Smiley';
$lang['SMILEY_IMPORT_INST'] = 'You should unzip the smiley package and upload all files to the appropriate Smiley directory for your installation. Then select the correct information in this form to import the smiley pack.';
$lang['SMILEY_IMPORT'] = 'Smiley Pack Import';
$lang['CHOOSE_SMILE_PAK'] = 'Choose a Smile Pack .pak file';
$lang['IMPORT'] = 'Import Smileys';
$lang['SMILE_CONFLICTS'] = 'What should be done in case of conflicts';
$lang['DEL_EXISTING_SMILEYS'] = 'Delete existing smileys before import';
$lang['IMPORT_SMILE_PACK'] = 'Import Smiley Pack';
$lang['EXPORT_SMILE_PACK'] = 'Create Smiley Pack';
$lang['EXPORT_SMILES'] = 'To create a smiley pack from your currently installed smileys, click %sHere%s to download the smiles.pak file. Name this file appropriately making sure to keep the .pak file extension.  Then create a zip file containing all of your smiley images plus this .pak configuration file.';

$lang['SMILEY_ADD_SUCCESS'] = 'The Smiley was successfully added';
$lang['SMILEY_EDIT_SUCCESS'] = 'The Smiley was successfully updated';
$lang['SMILEY_IMPORT_SUCCESS'] = 'The Smiley Pack was imported successfully!';
$lang['SMILEY_DEL_SUCCESS'] = 'The Smiley was successfully removed';
$lang['CLICK_RETURN_SMILEADMIN'] = 'Click %sHere%s to return to Smiley Administration';


//
// User Management
//
$lang['USER_ADMIN'] = 'User Administration';
$lang['USER_ADMIN_EXPLAIN'] = 'Here you can change your users\' information and certain options. To modify the users\' permissions, please use the user and group permissions system.';

$lang['LOOK_UP_USER'] = 'Look up user';

$lang['ADMIN_USER_FAIL'] = 'Couldn\'t update the user\'s profile.';
$lang['ADMIN_USER_UPDATED'] = 'The user\'s profile was successfully updated.';
$lang['CLICK_RETURN_USERADMIN'] = 'Click %sHere%s to return to User Administration';

$lang['USER_DELETE'] = 'Delete';
$lang['USER_DELETE_EXPLAIN'] = 'Delete this user';
$lang['USER_DELETED'] = 'User was successfully deleted';
$lang['DELETE_USER_POSTS'] = 'Delete all user posts';

$lang['USER_STATUS'] = 'User is active';
$lang['USER_ALLOWPM'] = 'Can send Private Messages';
$lang['USER_ALLOWAVATAR'] = 'Can display avatar';

$lang['ADMIN_AVATAR_EXPLAIN'] = 'Here you can see and delete the user\'s current avatar.';

$lang['USER_SPECIAL'] = 'Special admin-only fields';
$lang['USER_SPECIAL_EXPLAIN'] = 'These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.';


//
// Group Management
//
$lang['GROUP_ADMINISTRATION'] = 'Group Administration';
$lang['GROUP_ADMIN_EXPLAIN'] = 'From this panel you can administer all your usergroups. You can delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description';
$lang['ERROR_UPDATING_GROUPS'] = 'There was an error while updating the groups';
$lang['UPDATED_GROUP'] = 'The group was successfully updated';
$lang['ADDED_NEW_GROUP'] = 'The new group was successfully created';
$lang['DELETED_GROUP'] = 'The group was successfully deleted';
$lang['CREATE_NEW_GROUP'] = 'Create new group';
$lang['EDIT_GROUP'] = 'Edit group';
$lang['GROUP_STATUS'] = 'Group status';
$lang['GROUP_DELETE'] = 'Delete group';
$lang['GROUP_DELETE_CHECK'] = 'Delete this group';
$lang['SUBMIT_GROUP_CHANGES'] = 'Submit Changes';
$lang['RESET_GROUP_CHANGES'] = 'Reset Changes';
$lang['NO_GROUP_NAME'] = 'You must specify a name for this group';
$lang['NO_GROUP_MODERATOR'] = 'You must specify a moderator for this group';
$lang['NO_GROUP_MODE'] = 'You must specify a mode for this group, open or closed';
$lang['NO_GROUP_ACTION'] = 'No action was specified';
$lang['DELETE_OLD_GROUP_MOD'] = 'Delete the old group moderator?';
$lang['DELETE_OLD_GROUP_MOD_EXPL'] = 'If you\'re changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.';
$lang['CLICK_RETURN_GROUPSADMIN'] = 'Click %sHere%s to return to Group Administration.';
$lang['SELECT_GROUP'] = 'Select a group';
$lang['LOOK_UP_GROUP'] = 'Look up group';


//
// Prune Administration
//
$lang['FORUM_PRUNE'] = 'Forum Prune';
$lang['FORUM_PRUNE_EXPLAIN'] = 'This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove <b>sticky</b> topics and <b>announcements</b>. You will need to remove those topics manually.';
$lang['DO_PRUNE'] = 'Do Prune';
$lang['ALL_FORUMS'] = 'All Forums';
$lang['PRUNE_TOPICS_NOT_POSTED'] = 'Prune topics with no replies in this many days';
$lang['TOPICS_PRUNED'] = 'Topics pruned';
$lang['POSTS_PRUNED'] = 'Posts pruned';
$lang['PRUNE_SUCCESS'] = 'Pruning of forums was successful';


//
// Word censor
//
$lang['WORDS_TITLE'] = 'Word Censoring';
$lang['WORDS_EXPLAIN'] = 'From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field. For example, *test* will match detestable, test* would match testing, *test would match detest.';
$lang['WORD'] = 'Word';
$lang['EDIT_WORD_CENSOR'] = 'Edit word censor';
$lang['REPLACEMENT'] = 'Replacement';
$lang['ADD_NEW_WORD'] = 'Add new word';
$lang['UPDATE_WORD'] = 'Update word censor';

$lang['MUST_ENTER_WORD'] = 'You must enter a word and its replacement';
$lang['NO_WORD_SELECTED'] = 'No word selected for editing';

$lang['WORD_UPDATED'] = 'The selected word censor has been successfully updated';
$lang['WORD_ADDED'] = 'The word censor has been successfully added';
$lang['WORD_REMOVED'] = 'The selected word censor has been successfully removed';

$lang['CLICK_RETURN_WORDADMIN'] = 'Click %sHere%s to return to Word Censor Administration';


//
// Mass Email
//
$lang['MASS_EMAIL_EXPLAIN'] = 'Here you can email a message to either all of your users or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for a mass emailing to take a long time and you will be notified when the script has completed';
$lang['COMPOSE'] = 'Compose';

$lang['RECIPIENTS'] = 'Recipients';
$lang['ALL_USERS'] = 'All Users';

$lang['EMAIL_SUCCESSFULL'] = 'Your message has been sent';
$lang['CLICK_RETURN_MASSEMAIL'] = 'Click %sHere%s to return to the Mass Email form';


//
// Ranks admin
//
$lang['RANKS_TITLE'] = 'Rank Administration';
$lang['RANKS_EXPLAIN'] = 'Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility';

$lang['ADD_NEW_RANK'] = 'Add new rank';

$lang['RANK_TITLE'] = 'Rank Title';
$lang['RANK_SPECIAL'] = 'Set as Special Rank';
$lang['RANK_MINIMUM'] = 'Minimum Posts';
$lang['RANK_MAXIMUM'] = 'Maximum Posts';
$lang['RANK_IMAGE'] = 'Rank Image (Relative to phpBB2 root path)';
$lang['RANK_IMAGE_EXPLAIN'] = 'Use this to define a small image associated with the rank';

$lang['MUST_SELECT_RANK'] = 'You must select a rank';
$lang['NO_ASSIGNED_RANK'] = 'No special rank assigned';

$lang['RANK_UPDATED'] = 'The rank was successfully updated';
$lang['RANK_ADDED'] = 'The rank was successfully added';
$lang['RANK_REMOVED'] = 'The rank was successfully deleted';
$lang['NO_UPDATE_RANKS'] = 'The rank was successfully deleted. However, user accounts using this rank were not updated.  You will need to manually reset the rank on these accounts';

$lang['CLICK_RETURN_RANKADMIN'] = 'Click %sHere%s to return to Rank Administration';

//
// Disallow Username Admin
//
$lang['DISALLOW_CONTROL'] = 'Username Disallow Control';
$lang['DISALLOW_EXPLAIN'] = 'Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered. You must first delete that name then disallow it.';

$lang['DELETE_DISALLOW'] = 'Delete';
$lang['DELETE_DISALLOW_TITLE'] = 'Remove a Disallowed Username';
$lang['DELETE_DISALLOW_EXPLAIN'] = 'You can remove a disallowed username by selecting the username from this list and clicking submit';

$lang['ADD_DISALLOW'] = 'Add';
$lang['ADD_DISALLOW_TITLE'] = 'Add a disallowed username';
$lang['ADD_DISALLOW_EXPLAIN'] = 'You can disallow a username using the wildcard character * to match any character';

$lang['NO_DISALLOWED'] = 'No Disallowed Usernames';

$lang['DISALLOWED_DELETED'] = 'The disallowed username has been successfully removed';
$lang['DISALLOW_SUCCESSFUL'] = 'The disallowed username has been successfully added';
$lang['DISALLOWED_ALREADY'] = 'The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present.';

$lang['CLICK_RETURN_DISALLOWADMIN'] = 'Click %sHere%s to return to Disallow Username Administration';

// FTP
$lang['ATTACHMENT_FTP_SETTINGS'] = 'Setting up an FTP upload for attachments';
$lang['FTP_CHOOSE'] = 'Choose Download Method';
$lang['FTP_OPTION'] = '<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically FTP the config file into place.';
$lang['FTP_INSTRUCTS'] = 'You have chosen to FTP the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via FTP to your phpBB2 installation as if you were FTPing to it using any normal client.';
$lang['FTP_INFO'] = 'Enter Your FTP Information';
$lang['ATTEMPT_FTP'] = 'Attempt to FTP config file into place';
$lang['SEND_FILE'] = 'Just send the file to me and I\'ll FTP it manually';
$lang['FTP_PATH'] = 'FTP path to phpBB 2';
$lang['FTP_USERNAME'] = 'Your FTP Username';
$lang['FTP_PASSWORD'] = 'Your FTP Password';
$lang['TRANSFER_CONFIG'] = 'Start Transfer';
$lang['NOFTP_CONFIG'] = 'The attempt to FTP the config file into place failed.  Please download the config file and FTP it into place manually.';

//
// Version Check
//
$lang['VERSION_INFORMATION'] = 'Version Information';

//
// Login attempts configuration
//
$lang['MAX_LOGIN_ATTEMPTS'] = 'Allowed login attempts';
$lang['MAX_LOGIN_ATTEMPTS_EXPLAIN'] = 'The number of allowed board login attempts.';
$lang['LOGIN_RESET_TIME'] = 'Login lock time';
$lang['LOGIN_RESET_TIME_EXPLAIN'] = 'Time in minutes the user have to wait until he is allowed to login again after exceeding the number of allowed login attempts.';

//
// Permissions List
//
$lang['PERMISSIONS_LIST'] = 'Permissions List';
$lang['AUTH_CONTROL_CATEGORY'] = 'Category Permissions Control';
$lang['FORUM_AUTH_LIST_EXPLAIN'] = 'This provides a summary of the authorisation levels of each forum. You can edit these permissions, using either a simple or advanced method by clicking on the forum name. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.';
$lang['CAT_AUTH_LIST_EXPLAIN'] = 'This provides a summary of the authorisation levels of each forum within this category. You can edit the permissions of individual forums, using either a simple or advanced method by clicking on the forum name. Alternatively, you can set the permissions for all the forums in this category by using the drop-down menus at the bottom of the page. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.';
$lang['FORUM_AUTH_LIST_EXPLAIN_ALL'] = 'All users';
$lang['FORUM_AUTH_LIST_EXPLAIN_REG'] = 'All registered users';
$lang['FORUM_AUTH_LIST_EXPLAIN_PRIVATE'] = 'Only users granted special permission';
$lang['FORUM_AUTH_LIST_EXPLAIN_MOD'] = 'Only moderators of this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_ADMIN'] = 'Only administrators';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VIEW'] = '%s can view this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_READ'] = '%s can read posts in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POST'] = '%s can post in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_REPLY'] = '%s can reply to posts this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_EDIT'] = '%s can edit posts in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DELETE'] = '%s can delete posts in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_STICKY'] = '%s can post sticky topics in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ANNOUNCE'] = '%s can post announcements in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VOTE'] = '%s can vote in polls in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POLLCREATE'] = '%s can create polls in this forum';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ATTACHMENTS'] = '%s can post attachments';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DOWNLOAD'] = '%s can download attachments';

//
// Misc
//
$lang['SF_SHOW_ON_INDEX'] = 'Show on main page';
$lang['SF_PARENT_FORUM'] = 'Parent forum';
$lang['SF_NO_PARENT'] = 'No parent forum';
$lang['TEMPLATE'] = 'Template';
//Misc END

//
// Reports (need to translate!)
//
$lang['REPORT_CONFIG_EXPLAIN'] = 'On this page you can change the general configuration of the report feature.';
$lang['REPORT_SUBJECT_AUTH'] = 'Individual permissions';
$lang['REPORT_SUBJECT_AUTH_EXPLAIN'] = 'If this setting is enabled, moderators can only view reports they can edit. For example a post report will be hidden if the user isn\'t a moderator of the forum the post belongs to.';
$lang['REPORT_MODULES_CACHE'] = 'Cache modules in a file';
$lang['REPORT_MODULES_CACHE_EXPLAIN'] = 'Note: The cache directory must be set to <em>CHMOD 777</em> (full write permissions).';
$lang['REPORT_NOTIFY'] = 'Email notification';
$lang['REPORT_NOTIFY_CHANGE'] = 'on status changes and new reports';
$lang['REPORT_NOTIFY_NEW'] = 'on new reports';
$lang['REPORT_LIST_ADMIN'] = 'Admin-only report list';
$lang['REPORT_NEW_WINDOW'] = 'Open subjects in a new window';
$lang['REPORT_NEW_WINDOW_EXPLAIN'] = 'This setting also affects direct links to the reports at the view topic page.';
$lang['REPORT_CONFIG_UPDATED'] = 'The configuration was updated.';
$lang['CLICK_RETURN_REPORT_CONFIG'] = 'Click %sHere%s to return to the configuration.';

$lang['MODULES_REASONS'] = 'Modules &amp; Reasons';
$lang['REPORT_ADMIN_EXPLAIN'] = 'On this page you can install new report modules and edit or uninstall currently installed modules. In addition you can set up predefined reasons for every report module.';
$lang['REPORT_MODULE'] = 'Report module';
$lang['INSTALLED_MODULES'] = 'Installed modules';
$lang['NO_MODULES_INSTALLED'] = 'No modules installed';
$lang['REASONS'] = 'Reasons (%d)';
$lang['SYNC'] = 'Sync';
$lang['UNINSTALL'] = 'Uninstall';
$lang['INSTALL2'] = 'Install';
$lang['INACTIVE_MODULES'] = 'Inactive modules';
$lang['NO_MODULES_INACTIVE'] = 'No inactive modules';
$lang['REPORT_MODULE_NOT_EXISTS'] = 'The selected module doesn\'t exist.';
$lang['CLICK_RETURN_REPORT_ADMIN'] = 'Click %sHere%s to return to the Modules &amp; Reasons administration.';

$lang['BACK_MODULES'] = 'Back to the modules';
$lang['REPORT_REASON'] = 'Report reason';
$lang['NO_REASONS'] = 'No reasons for this module';
$lang['ADD_REASON'] = 'Add reason';
$lang['EDIT_REASON'] = 'Edit reason';
$lang['REASON_DESC_EXPLAIN'] = 'If the description matches with a language variable, the variable will be used instead.';
$lang['REASON_DESC_EMPTY'] = 'You have to enter a report reason.';
$lang['REPORT_REASON_ADDED'] = 'The report reason was added.';
$lang['REPORT_REASON_EDITED'] = 'The report reason was edited.';
$lang['DELETE_REASON'] = 'Delete reason';
$lang['DELETE_REPORT_REASON_EXPLAIN'] = 'Are you sure you want to delete the selected report reason?';
$lang['REPORT_REASON_DELETED'] = 'The report reason was deleted.';
$lang['REPORT_REASON_NOT_EXISTS'] = 'The selected report reason doesn\'t exist.';
$lang['CLICK_RETURN_REPORT_REASONS'] = 'Click %sHere%s to return to the report reasons administration.';

$lang['REPORT_MODULE_SYNCED'] = 'The module was synced.';

$lang['UNINSTALL_REPORT_MODULE'] = 'Uninstall module';
$lang['UNINSTALL_REPORT_MODULE_EXPLAIN'] = 'Are you sure you want to uninstall the selected report module? <br />Note: All reports in the module will be deleted, too.';
$lang['REPORT_MODULE_UNINSTALLED'] = 'The module was uninstalled.';

$lang['INSTALL_REPORT_MODULE'] = 'Install module';
$lang['EDIT_REPORT_MODULE'] = 'Edit module';
$lang['REPORT_PRUNE'] = 'Prune reports';
$lang['REPORT_PRUNE_EXPLAIN'] = 'Cleared reports and reports marked for deletion will be deleted automatically after <var>x</var> days. Set to <em>zero</em> to disable the feature.';
$lang['REPORT_PERMISSIONS'] = 'Report permissions';
$lang['WRITE'] = 'Write';
$lang['REPORT_AUTH'] = array(
	REPORT_AUTH_USER => 'Users',
	REPORT_AUTH_MOD => 'Moderators',
	REPORT_AUTH_CONFIRM => 'Moderators (after confirmation)',
	REPORT_AUTH_ADMIN => 'Administrators');
$lang['REPORT_AUTH_NOTIFY_EXPLAIN'] = 'Moderators will only be notified if they can view and edit the report.';
$lang['REPORT_AUTH_DELETE_EXPLAIN'] = 'If you select <em>Moderators (after confirmation)</em>, deletions have to be confirmed by an administrator.';
$lang['REPORT_MODULE_INSTALLED'] = 'The module was installed.';
$lang['REPORT_MODULE_EDITED'] = 'The module was edited.';
$lang['REPORTS'] = 'Reports';
//
// Reports [END]
//
