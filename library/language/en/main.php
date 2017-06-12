<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// Common, these terms are used extensively on several pages
$lang['ADMIN'] = 'Administrating';
$lang['FORUM'] = 'Forum';
$lang['CATEGORY'] = 'Category';
$lang['HIDE_CAT'] = 'Hide categories';
$lang['HIDE_CAT_MESS'] = 'Part of categories are hidden "options Show" &middot; <a href="index.php?sh=1">Show All</a>';
$lang['TOPIC'] = 'Topic';
$lang['TOPICS'] = 'Topics';
$lang['TOPICS_SHORT'] = 'Topics';
$lang['REPLIES'] = 'Replies';
$lang['REPLIES_SHORT'] = 'Replies';
$lang['VIEWS'] = 'Views';
$lang['POSTS'] = 'Posts';
$lang['POSTS_SHORT'] = 'Posts';
$lang['POSTED'] = 'Posted';
$lang['USERNAME'] = 'Username';
$lang['PASSWORD'] = 'Password';
$lang['EMAIL'] = 'Email';
$lang['PM'] = 'PM';
$lang['AUTHOR'] = 'Author';
$lang['TIME'] = 'Time';
$lang['HOURS'] = 'Hours';
$lang['MESSAGE'] = 'Message';
$lang['TORRENT'] = 'Torrent';
$lang['PERMISSIONS'] = 'Permissions';
$lang['TYPE'] = 'Type';
$lang['SEEDER'] = 'Seeder';
$lang['LEECHER'] = 'Leecher';
$lang['RELEASER'] = 'Releaser';

$lang['1_DAY'] = '1 Day';
$lang['7_DAYS'] = '7 Days';
$lang['2_WEEKS'] = '2 Weeks';
$lang['1_MONTH'] = '1 Month';
$lang['3_MONTHS'] = '3 Months';
$lang['6_MONTHS'] = '6 Months';
$lang['1_YEAR'] = '1 Year';

$lang['GO'] = 'Go to';
$lang['SUBMIT'] = 'Submit';
$lang['RESET'] = 'Reset';
$lang['CANCEL'] = 'Cancel';
$lang['PREVIEW'] = 'Preview';
$lang['AJAX_PREVIEW'] = 'Quick View';
$lang['CONFIRM'] = 'Confirm';
$lang['YES'] = 'Yes';
$lang['NO'] = 'No';
$lang['ENABLED'] = 'Enabled';
$lang['DISABLED'] = 'Disabled';
$lang['ERROR'] = 'Error';
$lang['SELECT_ACTION'] = 'Select action';

$lang['NEXT_PAGE'] = 'Next';
$lang['PREVIOUS_PAGE'] = 'Previous';
$lang['GOTO_PAGE'] = 'Go to page';
$lang['GOTO_SHORT'] = 'Page';
$lang['JOINED'] = 'Joined';
$lang['LONGEVITY'] = 'Longevity';
$lang['IP_ADDRESS'] = 'IP Address';
$lang['POSTED_AFTER'] = 'after';

$lang['SELECT_FORUM'] = 'Select forum';
$lang['VIEW_LATEST_POST'] = 'View latest post';
$lang['VIEW_NEWEST_POST'] = 'View newest post';
$lang['PAGE_OF'] = 'Page <b>%d</b> of <b>%s</b>';

$lang['ICQ'] = 'ICQ';

$lang['SKYPE'] = 'Skype';
$lang['SKYPE_ERROR'] = 'You entered an invalid Skype login';

$lang['TWITTER'] = 'Twitter';
$lang['TWITTER_ERROR'] = 'You entered an invalid Twitter login';

$lang['FORUM_INDEX'] = '%s Forum Index'; // eg. sitename Forum Index, %s can be removed if you prefer

$lang['POST_NEW_TOPIC'] = 'Post new topic';
$lang['POST_NEW_RELEASE'] = 'Post new release';
$lang['POST_REGULAR_TOPIC'] = 'Post regular topic';
$lang['REPLY_TO_TOPIC'] = 'Reply to topic';
$lang['REPLY_WITH_QUOTE'] = 'Reply with quote';

$lang['CLICK_RETURN_TOPIC'] = 'Click %sHere%s to return to the topic'; // %s's here are for uris, do not remove!
$lang['CLICK_RETURN_LOGIN'] = 'Click %sHere%s to try again';
$lang['CLICK_RETURN_FORUM'] = 'Click %sHere%s to return to the forum';
$lang['CLICK_VIEW_MESSAGE'] = 'Click %sHere%s to return to your message';
$lang['CLICK_RETURN_MODCP'] = 'Click %sHere%s to return to Moderator Control Panel';
$lang['CLICK_RETURN_GROUP'] = 'Click %sHere%s to return to group information';

$lang['ADMIN_PANEL'] = 'Go to Administration Panel';
$lang['ALL_CACHE'] = 'All cache';
$lang['ALL_CACHE_CLEARED'] = 'Cache has been cleared';
$lang['ALL_TEMPLATE_CLEARED'] = 'Template cache has been cleared';
$lang['DATASTORE'] = 'Datastore';
$lang['DATASTORE_CLEARED'] = 'Datastore has been cleared';
$lang['BOARD_DISABLE'] = 'Sorry, this forum is disabled. Try to come back later';
$lang['BOARD_DISABLE_CRON'] = 'Forum is down for maintenance. Try to come back later';
$lang['ADMIN_DISABLE'] = 'the forum is disabled by administrator, you can enable it at any time';
$lang['ADMIN_DISABLE_CRON'] = 'forum locked by the trigger cron job, you can remove a lock at any time';
$lang['ADMIN_DISABLE_TITLE'] = 'The forum is disabled';
$lang['ADMIN_DISABLE_CRON_TITLE'] = 'Forum is down for maintenance';
$lang['ADMIN_UNLOCK'] = 'Enable forum';
$lang['ADMIN_UNLOCKED'] = 'Unlocked';
$lang['ADMIN_UNLOCK_CRON'] = 'Remove lock';

$lang['LOADING'] = 'Loading...';
$lang['JUMPBOX_TITLE'] = 'Select forum';
$lang['DISPLAYING_OPTIONS'] = 'Displaying options';

// Global Header strings
$lang['REGISTERED_USERS'] = 'Registered Users:';
$lang['BROWSING_FORUM'] = 'Users browsing this forum:';
$lang['ONLINE_USERS'] = 'In total there are <b>%1$d</b> users online: %2$d registered and %3$d guests';
$lang['RECORD_ONLINE_USERS'] = 'The most users ever online was <b>%s</b> on %s'; // first %s = number of users, second %s is the date.

$lang['ONLINE_ADMIN'] = 'Administrator';
$lang['ONLINE_MOD'] = 'Moderator';
$lang['ONLINE_GROUP_MEMBER'] = 'Group member';

$lang['CURRENT_TIME'] = 'Current time is: <span class="tz_time">%s</span>';

$lang['SEARCH_NEW'] = 'View newest posts';
$lang['SEARCH_SELF'] = 'My posts';
$lang['SEARCH_SELF_BY_LAST'] = 'last post time';
$lang['SEARCH_SELF_BY_MY'] = 'my post time';
$lang['SEARCH_UNANSWERED'] = 'View unanswered posts';
$lang['SEARCH_UNANSWERED_SHORT'] = 'unanswered';
$lang['SEARCH_LATEST'] = 'Latest topics';
$lang['LATEST_RELEASES'] = 'Latest releases';

$lang['REGISTER'] = 'Register';
$lang['PROFILE'] = 'Profile';
$lang['EDIT_PROFILE'] = 'Edit profile';
$lang['SEARCH'] = 'Search';
$lang['MEMBERLIST'] = 'Memberlist';
$lang['USERGROUPS'] = 'Usergroups';
$lang['LASTPOST'] = 'Last Post';
$lang['MODERATOR'] = 'Moderator';
$lang['MODERATORS'] = 'Moderators';
$lang['TERMS'] = 'Terms';
$lang['NOTHING_HAS_CHANGED'] = 'Nothing has been changed';

// Stats block text
$lang['POSTED_TOPICS_TOTAL'] = 'Our users have posted a total of <b>%s</b> topics'; // Number of topics
$lang['POSTED_ARTICLES_ZERO_TOTAL'] = 'Our users have posted a total of <b>0</b> articles'; // Number of posts
$lang['POSTED_ARTICLES_TOTAL'] = 'Our users have posted a total of <b>%s</b> articles'; // Number of posts
$lang['REGISTERED_USERS_ZERO_TOTAL'] = 'We have <b>0</b> registered users'; // # registered users
$lang['REGISTERED_USERS_TOTAL'] = 'We have <b>%s</b> registered users'; // # registered users
$lang['USERS_TOTAL_GENDER'] = 'Boys: <b>%d</b>, Girls: <b>%d</b>, Others: <b>%d</b>';
$lang['NEWEST_USER'] = 'The newest registered user is <b>%s</b>'; // a href, username, /a

// Tracker stats
$lang['TORRENTS_STAT'] = 'Torrents: <b style="color: blue;">%s</b>,&nbsp; Total size: <b>%s</b>'; // first %s = number of torrents, second %s is the total size.
$lang['PEERS_STAT'] = 'Peers: <b>%s</b>,&nbsp; Seeders: <b class="seedmed">%s</b>,&nbsp; Leechers: <b class="leechmed">%s</b>'; // first %s = number of peers, second %s = number of seeders,  third %s = number of leechers.
$lang['SPEED_STAT'] = 'Total speed: <b>%s</b>&nbsp;'; // %s = total speed.

$lang['NO_NEW_POSTS_LAST_VISIT'] = 'No new posts since your last visit';
$lang['NO_NEW_POSTS'] = 'No new posts';
$lang['NEW_POSTS'] = 'New posts';
$lang['NEW_POST'] = 'New post';
$lang['NO_NEW_POSTS_HOT'] = 'No new posts [ Popular ]';
$lang['NEW_POSTS_HOT'] = 'New posts [ Popular ]';
$lang['NEW_POSTS_LOCKED'] = 'New posts [ Locked ]';
$lang['FORUM_LOCKED_MAIN'] = 'Forum is locked';

// Login
$lang['ENTER_PASSWORD'] = 'Please enter username and password to log in.';
$lang['LOGIN'] = 'Log in';
$lang['LOGOUT'] = 'Log out';
$lang['CONFIRM_LOGOUT'] = 'Are you sure you want to log out?';

$lang['FORGOTTEN_PASSWORD'] = 'Password forgotten?';
$lang['AUTO_LOGIN'] = 'Log me on automatically';
$lang['ERROR_LOGIN'] = 'The username you submitted is incorrect or invalid, or the password is invalid.';
$lang['REMEMBER'] = 'Remember';
$lang['USER_WELCOME'] = 'Welcome,';

// Index page
$lang['HOME'] = 'Home';
$lang['NO_POSTS'] = 'No posts';
$lang['NO_FORUMS'] = 'This board has no forums';

$lang['PRIVATE_MESSAGE'] = 'Private Message';
$lang['PRIVATE_MESSAGES'] = 'Private Messages';
$lang['WHOSONLINE'] = 'Who is online';

$lang['MARK_ALL_FORUMS_READ'] = 'Flag all forums as read';
$lang['FORUMS_MARKED_READ'] = 'All forums flagged as read';

$lang['LATEST_NEWS'] = 'Latest news';
$lang['NETWORK_NEWS'] = 'Network news';
$lang['SUBFORUMS'] = 'Subforums';

// Viewforum
$lang['VIEW_FORUM'] = 'View Forum';

$lang['FORUM_NOT_EXIST'] = 'The forum you selected does not exist.';
$lang['REACHED_ON_ERROR'] = 'You have reached this page in error.';
$lang['ERROR_PORNO_FORUM'] = 'This type of forums (18+) was hidden in your profile by you';

$lang['DISPLAY_TOPICS'] = 'Display topics';
$lang['ALL_TOPICS'] = 'All Topics';
$lang['MODERATE_FORUM'] = 'Moderate this forum';
$lang['TITLE_SEARCH_HINT'] = 'search title...';

$lang['TOPIC_ANNOUNCEMENT'] = 'Announcement:';
$lang['TOPIC_STICKY'] = 'Sticky:';
$lang['TOPIC_MOVED'] = 'Moved:';
$lang['TOPIC_POLL'] = '[ Poll ]';

$lang['MARK_TOPICS_READ'] = 'Mark all topics read';
$lang['TOPICS_MARKED_READ'] = 'The topics for this forum have just been marked read';

$lang['RULES_POST_CAN'] = 'You <b>can</b> post new topics in this forum';
$lang['RULES_POST_CANNOT'] = 'You <b>cannot</b> post new topics in this forum';
$lang['RULES_REPLY_CAN'] = 'You <b>can</b> reply to topics in this forum';
$lang['RULES_REPLY_CANNOT'] = 'You <b>cannot</b> reply to topics in this forum';
$lang['RULES_EDIT_CAN'] = 'You <b>can</b> edit your posts in this forum';
$lang['RULES_EDIT_CANNOT'] = 'You <b>cannot</b> edit your posts in this forum';
$lang['RULES_DELETE_CAN'] = 'You <b>can</b> delete your posts in this forum';
$lang['RULES_DELETE_CANNOT'] = 'You <b>cannot</b> delete your posts in this forum';
$lang['RULES_VOTE_CAN'] = 'You <b>can</b> vote in polls in this forum';
$lang['RULES_VOTE_CANNOT'] = 'You <b>cannot</b> vote in polls in this forum';
$lang['RULES_MODERATE'] = 'You <b>can</b> moderate this forum';

$lang['NO_TOPICS_POST_ONE'] = 'There are no posts in this forum.<br />Click on the <b>Post New Topic</b> link on this page to post one.';

// Viewtopic
$lang['VIEW_TOPIC'] = 'View topic';

$lang['GUEST'] = 'Guest';
$lang['POST_SUBJECT'] = 'Post subject';
$lang['SUBMIT_VOTE'] = 'Submit vote';
$lang['VIEW_RESULTS'] = 'View results';

$lang['NO_NEWER_TOPICS'] = 'There are no newer topics in this forum';
$lang['NO_OLDER_TOPICS'] = 'There are no older topics in this forum';
$lang['TOPIC_POST_NOT_EXIST'] = 'The topic or post you requested does not exist';
$lang['NO_POSTS_TOPIC'] = 'There are no posts in this topic';

$lang['DISPLAY_POSTS'] = 'Display posts';
$lang['ALL_POSTS'] = 'All Posts';
$lang['NEWEST_FIRST'] = 'Newest First';
$lang['OLDEST_FIRST'] = 'Oldest First';

$lang['BACK_TO_TOP'] = 'Back to top';

$lang['READ_PROFILE'] = 'View user\'s profile';
$lang['VISIT_WEBSITE'] = 'Visit poster\'s website';
$lang['VIEW_IP'] = 'View poster IP address';
$lang['MODERATE_POST'] = 'Moderate posts';
$lang['DELETE_POST'] = 'Delete this post';

$lang['WROTE'] = 'wrote'; // proceeds the username and is followed by the quoted text
$lang['QUOTE'] = 'Quote'; // comes before bbcode quote output
$lang['CODE'] = 'Code'; // comes before bbcode code output
$lang['SPOILER_HEAD'] = 'hidden text';
$lang['SPOILER_CLOSE'] = 'turn';
$lang['PLAY_ON_CURPAGE'] = 'Start playing on current page';

$lang['EDITED_TIME_TOTAL'] = 'Last edited by %s on %s; edited %d time in total'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['EDITED_TIMES_TOTAL'] = 'Last edited by %s on %s; edited %d times in total'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['LOCK_TOPIC'] = 'Lock the topic';
$lang['UNLOCK_TOPIC'] = 'Unlock the topic';
$lang['MOVE_TOPIC'] = 'Move the topic';
$lang['DELETE_TOPIC'] = 'Delete the topic';
$lang['SPLIT_TOPIC'] = 'Split the topic';

$lang['STOP_WATCHING_TOPIC'] = 'Stop following the topic';
$lang['START_WATCHING_TOPIC'] = 'Follow the topic for replies';
$lang['NO_LONGER_WATCHING'] = 'You are no longer following this topic';
$lang['YOU_ARE_WATCHING'] = 'You are following this topic now';

$lang['TOTAL_VOTES'] = 'Total Votes';
$lang['SEARCH_IN_TOPIC'] = 'search in topic...';
$lang['HIDE_IN_TOPIC'] = 'Hide';

$lang['SHOW'] = 'Show';
$lang['AVATARS'] = 'Avatars';
$lang['RANK_IMAGES'] = 'Rank images';
$lang['POST_IMAGES'] = 'Post images';
$lang['SIGNATURES'] = 'Signatures';
$lang['SPOILER'] = 'Spoiler';
$lang['SHOW_OPENED'] = 'Show opened';
$lang['DOWNLOAD_PIC'] = 'Downloadable pictures';

$lang['MODERATE_TOPIC'] = 'Moderate this topic';
$lang['SELECT_POSTS_PER_PAGE'] = 'posts per page';

// Posting/Replying (Not private messaging!)
$lang['TOPIC_REVIEW'] = 'Topic review';

$lang['NO_POST_MODE'] = 'No post mode selected'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['POST_A_NEW_TOPIC'] = 'Post new topic';
$lang['POST_A_REPLY'] = 'Post new reply';
$lang['POST_TOPIC_AS'] = 'Post topic as';
$lang['EDIT_POST'] = 'Edit post';
$lang['EDIT_TOPIC_TITLE'] = 'Edit topic title';
$lang['EDIT_POST_NOT_1'] = 'You are not allowed ';
$lang['EDIT_POST_NOT_2'] = 'You can not ';
$lang['EDIT_POST_AJAX'] = 'You can not edit post with the status ';
$lang['AFTER_THE_LAPSE'] = 'after the lapse of ';

$lang['DONT_MESSAGE_TITLE'] = 'You should specify message title';
$lang['INVALID_TOPIC_ID'] = 'Topic Absent!';
$lang['INVALID_TOPIC_ID_DB'] = 'Topic does not exist in the database!';

$lang['NOT_POST'] = 'Absent Message';
$lang['NOT_EDIT_TOR_STATUS'] = 'You can not edit release with the status';
$lang['TOR_STATUS_DAYS'] = 'days';

$lang['OPTIONS'] = 'Options';

$lang['POST_ANNOUNCEMENT'] = 'Announcement';
$lang['POST_STICKY'] = 'Sticky';
$lang['POST_NORMAL'] = 'Normal';
$lang['POST_DOWNLOAD'] = 'Download';

$lang['CONFIRM_DELETE'] = 'Are you sure you want to delete this post?';
$lang['CONFIRM_DELETE_POLL'] = 'Are you sure you want to delete this poll?';

$lang['FLOOD_ERROR'] = 'You cannot make another post so soon after your last; please try again in a short while';
$lang['EMPTY_SUBJECT'] = 'You must specify a subject';
$lang['EMPTY_MESSAGE'] = 'You must enter a message';
$lang['FORUM_LOCKED'] = 'This forum is locked: you cannot post, reply to, or edit topics';
$lang['TOPIC_LOCKED'] = 'This topic is locked: you cannot edit posts or make replies';
$lang['TOPIC_LOCKED_SHORT'] = 'Topic locked';
$lang['NO_POST_ID'] = 'You must select a post to edit';
$lang['NO_TOPIC_ID'] = 'You must select a topic to reply to';
$lang['NO_VALID_MODE'] = 'You can only post, reply, edit, or quote messages. Please return and try again';
$lang['NO_SUCH_POST'] = 'There is no such post. Please return and try again';
$lang['EDIT_OWN_POSTS'] = 'Sorry, but you can only edit your own posts';
$lang['DELETE_OWN_POSTS'] = 'Sorry, but you can only delete your own posts';
$lang['CANNOT_DELETE_REPLIED'] = 'Sorry, but you may not delete posts that have been replied to';
$lang['CANNOT_DELETE_POLL'] = 'Sorry, but you cannot delete an active poll';
$lang['EMPTY_POLL_TITLE'] = 'You must enter a title for your poll';
$lang['TO_FEW_POLL_OPTIONS'] = 'You must enter at least two poll options';
$lang['TO_MANY_POLL_OPTIONS'] = 'You have tried to enter too many poll options';
$lang['POST_HAS_NO_POLL'] = 'This post has no poll';
$lang['ALREADY_VOTED'] = 'You have already voted in this poll';
$lang['NO_VOTE_OPTION'] = 'You must specify an option when voting';
$lang['LOCKED_WARN'] = 'You posted into locked topic!';

$lang['ADD_POLL'] = 'Add a poll';
$lang['ADD_POLL_EXPLAIN'] = 'If you do not want to add a poll to your topic, leave the fields blank.';
$lang['POLL_QUESTION'] = 'Poll question';
$lang['POLL_OPTION'] = 'Poll option';
$lang['ADD_OPTION'] = 'Add option';
$lang['UPDATE'] = 'Update';
$lang['POLL_FOR'] = 'Run poll for';
$lang['DAYS'] = 'Days';
$lang['POLL_FOR_EXPLAIN'] = '[ Enter 0 or leave blank for a never-ending poll ]';
$lang['DELETE_POLL'] = 'Delete poll';

$lang['MAX_SMILIES_PER_POST'] = 'Emoticons limit of %s emoticons exceeded.';

$lang['ATTACH_SIGNATURE'] = 'Attach signature (signatures can be changed in profile)';
$lang['NOTIFY'] = 'Notify me when on replies';

$lang['STORED'] = 'Your message has been entered successfully.';
$lang['EDITED'] = 'The message has been changed';
$lang['DELETED'] = 'Your message has been deleted successfully.';
$lang['POLL_DELETE'] = 'Your poll has been deleted successfully.';
$lang['VOTE_CAST'] = 'Your vote has been cast.';

$lang['TOPIC_REPLY_NOTIFICATION'] = 'Topic Reply Notification';
$lang['EMOTICONS'] = 'Emoticons';
$lang['MORE_EMOTICONS'] = 'View more Emoticons';

$lang['FONT_COLOR'] = 'Font colour';
$lang['COLOR_DEFAULT'] = 'Default';
$lang['COLOR_DARK_RED'] = 'Dark Red';
$lang['COLOR_RED'] = 'Red';
$lang['COLOR_ORANGE'] = 'Orange';
$lang['COLOR_BROWN'] = 'Brown';
$lang['COLOR_YELLOW'] = 'Yellow';
$lang['COLOR_GREEN'] = 'Green';
$lang['COLOR_OLIVE'] = 'Olive';
$lang['COLOR_CYAN'] = 'Cyan';
$lang['COLOR_BLUE'] = 'Blue';
$lang['COLOR_DARK_BLUE'] = 'Dark Blue';
$lang['COLOR_INDIGO'] = 'Indigo';
$lang['COLOR_VIOLET'] = 'Violet';
$lang['COLOR_WHITE'] = 'White';
$lang['COLOR_BLACK'] = 'Black';

$lang['FONT_SIZE'] = 'Font size';
$lang['FONT_TINY'] = 'Tiny';
$lang['FONT_SMALL'] = 'Small';
$lang['FONT_NORMAL'] = 'Normal';
$lang['FONT_LARGE'] = 'Large';
$lang['FONT_HUGE'] = 'Huge';

$lang['STYLES_TIP'] = 'Tip: Styles can be applied quickly to selected text.';

$lang['NEW_POSTS_PREVIEW'] = 'Topic has new, edited or unread posts';

// Private Messaging
$lang['PRIVATE_MESSAGING'] = 'Private Messaging';

$lang['NO_NEW_PM'] = 'no new messages';

$lang['NEW_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['NEW_PMS_DECLENSION'] = array('new message', 'new messages');

$lang['UNREAD_PMS_FORMAT'] = '<b>%1$s</b> %2$s'; // 1 new message
$lang['UNREAD_PMS_DECLENSION'] = array('unread', 'unread');

$lang['UNREAD_MESSAGE'] = 'Unread message';
$lang['READ_MESSAGE'] = 'Read message';

$lang['READ_PM'] = 'Read message';
$lang['POST_NEW_PM'] = 'Post message';
$lang['POST_REPLY_PM'] = 'Reply to message';
$lang['POST_QUOTE_PM'] = 'Quote message';
$lang['EDIT_PM'] = 'Edit message';

$lang['INBOX'] = 'Inbox';
$lang['OUTBOX'] = 'Outbox';
$lang['SAVEBOX'] = 'Savebox';
$lang['SENTBOX'] = 'Sentbox';
$lang['FLAG'] = 'Flag';
$lang['SUBJECT'] = 'Subject';
$lang['FROM'] = 'From';
$lang['TO'] = 'To';
$lang['DATE'] = 'Date';
$lang['MARK'] = 'Mark';
$lang['SENT'] = 'Sent';
$lang['SAVED'] = 'Saved';
$lang['DELETE_MARKED'] = 'Delete Marked';
$lang['DELETE_ALL'] = 'Delete All';
$lang['SAVE_MARKED'] = 'Save Marked';
$lang['SAVE_MESSAGE'] = 'Save Message';
$lang['DELETE_MESSAGE'] = 'Delete Message';

$lang['DISPLAY_MESSAGES'] = 'Display messages'; // Followed by number of days/weeks/months
$lang['ALL_MESSAGES'] = 'All Messages';

$lang['NO_MESSAGES_FOLDER'] = 'There are no messages in this folder';

$lang['PM_DISABLED'] = 'Private messaging has been disabled on this board.';
$lang['CANNOT_SEND_PRIVMSG'] = 'Sorry, but the administrator has prevented you from sending private messages.';
$lang['NO_TO_USER'] = 'You must specify a username to whom to send this message.';
$lang['NO_SUCH_USER'] = 'Sorry, but no such user exists.';

$lang['DISABLE_BBCODE_PM'] = 'Disable BBCode in this message';
$lang['DISABLE_SMILIES_PM'] = 'Disable Smilies in this message';

$lang['MESSAGE_SENT'] = '<b>Your message has been sent.</b>';

$lang['CLICK_RETURN_INBOX'] = 'Return to your:<br /><br /> %s<b>Inbox</b>%s';
$lang['CLICK_RETURN_SENTBOX'] = '&nbsp;&nbsp; %s<b>Sentbox</b>%s';
$lang['CLICK_RETURN_OUTBOX'] = '&nbsp;&nbsp; %s<b>Outbox</b>%s';
$lang['CLICK_RETURN_SAVEBOX'] = '&nbsp;&nbsp; %s<b>Savebox</b>%s';
$lang['CLICK_RETURN_INDEX'] = '%sReturn to the Index%s';

$lang['SEND_A_NEW_MESSAGE'] = 'Send a new private message';
$lang['SEND_A_REPLY'] = 'Reply to a private message';
$lang['EDIT_MESSAGE'] = 'Edit private message';

$lang['NOTIFICATION_SUBJECT'] = 'New Private Message has been recieved!';

$lang['FIND_USERNAME'] = 'Find a username';
$lang['SELECT_USERNAME'] = 'Select a Username';
$lang['FIND'] = 'Find';
$lang['NO_MATCH'] = 'No matches found.';

$lang['NO_PM_ID'] = 'Please specify post ID';
$lang['NO_SUCH_FOLDER'] = 'Folder is not found';
$lang['NO_FOLDER'] = 'Please specify the folder';

$lang['MARK_ALL'] = 'Mark all';
$lang['UNMARK_ALL'] = 'Unmark all';

$lang['CONFIRM_DELETE_PM'] = 'Are you sure you want to delete this message?';
$lang['CONFIRM_DELETE_PMS'] = 'Are you sure you want to delete these messages?';

$lang['INBOX_SIZE'] = 'Your Inbox is<br /><b>%d%%</b> full'; // eg. Your Inbox is 50% full
$lang['SENTBOX_SIZE'] = 'Your Sentbox is<br /><b>%d%%</b> full';
$lang['SAVEBOX_SIZE'] = 'Your Savebox is<br /><b>%d%%</b> full';

$lang['CLICK_VIEW_PRIVMSG'] = 'Click %sHere%s to visit your Inbox';

$lang['OUTBOX_EXPL'] = '';

// Profiles/Registration
$lang['VIEWING_USER_PROFILE'] = 'Viewing profile :: %s';
$lang['VIEWING_MY_PROFILE'] = 'My profile [ <a href="%s">Settings / Change profile</a> ]';

$lang['DISABLED_USER'] = 'Account disabled';
$lang['MANAGE_USER'] = 'Administration';

$lang['PREFERENCES'] = 'Preferences';
$lang['ITEMS_REQUIRED'] = 'Items marked with a * are required unless stated otherwise.';
$lang['REGISTRATION_INFO'] = 'Registration Information';
$lang['PROFILE_INFO'] = 'Profile Information';
$lang['PROFILE_INFO_WARN'] = 'Publicly available information';
$lang['AVATAR_PANEL'] = 'Avatar control panel';

$lang['WEBSITE'] = 'Website';
$lang['LOCATION'] = 'Location';
$lang['CONTACT'] = 'Contact';
$lang['EMAIL_ADDRESS'] = 'E-mail address';
$lang['SEND_PRIVATE_MESSAGE'] = 'Send private message';
$lang['HIDDEN_EMAIL'] = '[ Hidden ]';
$lang['INTERESTS'] = 'Interests';
$lang['OCCUPATION'] = 'Occupation';
$lang['POSTER_RANK'] = 'Poster rank';
$lang['AWARDED_RANK'] = 'Awarded rank';
$lang['SHOT_RANK'] = 'Shot rank';

$lang['TOTAL_POSTS'] = 'Total posts';
$lang['SEARCH_USER_POSTS'] = 'Find posts'; // Find all posts by username
$lang['SEARCH_USER_POSTS_SHORT'] = 'Find user posts';
$lang['SEARCH_USER_TOPICS'] = 'Find user topics'; // Find all topics by username

$lang['NO_USER_ID_SPECIFIED'] = 'Sorry, but that user does not exist.';
$lang['WRONG_PROFILE'] = 'You cannot modify a profile that is not your own.';

$lang['ONLY_ONE_AVATAR'] = 'Only one type of avatar can be specified';
$lang['FILE_NO_DATA'] = 'The file at the URL you gave contains no data';
$lang['NO_CONNECTION_URL'] = 'A connection could not be made to the URL you gave';
$lang['INCOMPLETE_URL'] = 'The URL you entered is incomplete';
$lang['NO_SEND_ACCOUNT_INACTIVE'] = 'Sorry, but your password cannot be retrieved because your account is currently inactive';
$lang['NO_SEND_ACCOUNT'] = 'Sorry, but your password cannot be retrieved. Please contact the forum administrator for more information';

$lang['ALWAYS_ADD_SIG'] = 'Always attach my signature';
$lang['HIDE_PORN_FORUMS'] = 'Hide content 18+';
$lang['ALWAYS_NOTIFY'] = 'Always notify me of replies';
$lang['ALWAYS_NOTIFY_EXPLAIN'] = 'Sends an e-mail when someone replies to a topic you have posted in. This can be changed whenever you post.';

$lang['BOARD_LANG'] = 'Board language';
$lang['GENDER'] = 'Gender';
$lang['GENDER_SELECT'] = array(
    0 => 'Unknown',
    1 => 'Male',
    2 => 'Female'
);
$lang['MODULE_OFF'] = 'Module is disabled!';

$lang['BIRTHDAY'] = 'Birthday';
$lang['HAPPY_BIRTHDAY'] = 'Happy Birthday!';
$lang['WRONG_BIRTHDAY_FORMAT'] = 'The birthday format was entered incorrectly.';
$lang['AGE'] = 'Age';
$lang['BIRTHDAY_TO_HIGH'] = 'Sorry, this site, does not accept user older than %d years old';
$lang['BIRTHDAY_TO_LOW'] = 'Sorry, this site, does not accept user yonger than %d years old';
$lang['BIRTHDAY_TODAY'] = 'Users with a birthday today: ';
$lang['BIRTHDAY_WEEK'] = 'Users with a birthday within the next %d days: %s';
$lang['NOBIRTHDAY_WEEK'] = 'No users are having a birthday in the upcoming %d days'; // %d is substitude with the number of days
$lang['NOBIRTHDAY_TODAY'] = 'No users have a birthday today';
$lang['BIRTHDAY_ENABLE'] = 'Enable birthday';
$lang['BIRTHDAY_MAX_AGE'] = 'Max age';
$lang['BIRTHDAY_MIN_AGE'] = 'Min age';
$lang['BIRTHDAY_CHECK_DAY'] = 'Days to check for come shortly birthdays';
$lang['YEARS'] = 'Years';

$lang['NO_THEMES'] = 'No Themes In database';
$lang['TIMEZONE'] = 'Timezone';
$lang['DATE_FORMAT_PROFILE'] = 'Date format';
$lang['DATE_FORMAT_EXPLAIN'] = 'The syntax used is identical to the PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> function.';
$lang['SIGNATURE'] = 'Signature';
$lang['SIGNATURE_EXPLAIN'] = 'This is a block of text that can be added to posts you make. There is a %d character limit';
$lang['SIGNATURE_DISABLE'] = 'Signed off for violation of forum rules';
$lang['PUBLIC_VIEW_EMAIL'] = 'Show e-mail address in my profile';

$lang['EMAIL_EXPLAIN'] = 'At this address you will be sent to complete the registration';

$lang['CURRENT_PASSWORD'] = 'Current password';
$lang['NEW_PASSWORD'] = 'New password';
$lang['CONFIRM_PASSWORD'] = 'Confirm password';
$lang['CONFIRM_PASSWORD_EXPLAIN'] = 'You must confirm your current password if you wish to change it or alter your e-mail address';
$lang['PASSWORD_IF_CHANGED'] = 'You only need to supply a password if you want to change it';
$lang['PASSWORD_CONFIRM_IF_CHANGED'] = 'You only need to confirm your password if you changed it above';

$lang['AUTOLOGIN'] = 'Autologin';
$lang['RESET_AUTOLOGIN'] = 'Reset autologin key';
$lang['RESET_AUTOLOGIN_EXPL'] = 'including all the places you\'ve visited the forum enabled auto-login';

$lang['AVATAR'] = 'Avatar';
$lang['AVATAR_EXPLAIN'] = 'Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than %d pixels, the height no greater than %d pixels, and the file size no more than %d KB.';
$lang['AVATAR_DELETE'] = 'Delete avatar';
$lang['AVATAR_DISABLE'] = 'Avatar control option disabled for violation <a href="%s"><b>forum rules</b></a>';
$lang['UPLOAD_AVATAR_FILE'] = 'Upload avatar';

$lang['SELECT_AVATAR'] = 'Select avatar';
$lang['RETURN_PROFILE'] = 'Return to profile';
$lang['SELECT_CATEGORY'] = 'Select category';

$lang['DELETE_IMAGE'] = 'Delete image';
$lang['CURRENT_IMAGE'] = 'Current image';

$lang['NOTIFY_ON_PRIVMSG'] = 'Notify on new private message';
$lang['HIDE_USER'] = 'Hide your online status';
$lang['HIDDEN_USER'] = 'Hidden user';

$lang['PROFILE_UPDATED'] = 'Your profile has been updated';
$lang['PROFILE_UPDATED_INACTIVE'] = 'Your profile has been updated. However, you have changed vital details, thus your account is inactive now. Check your e-mail to find out how to reactivate your account, or if admin activation is required, wait for the administrator to reactivate it.';

$lang['PASSWORD_MISMATCH'] = 'The passwords you entered did not match.';
$lang['CURRENT_PASSWORD_MISMATCH'] = 'The current password you supplied does not match that stored in the database.';
$lang['PASSWORD_LONG'] = 'Your password must be no more than 32 characters.';
$lang['TOO_MANY_REGISTERS'] = 'You have made too many registration attempts. Please try again later.';
$lang['USERNAME_TAKEN'] = 'Sorry, but this username has already been taken.';
$lang['USERNAME_INVALID'] = 'Sorry, but this username contains an invalid character';
$lang['USERNAME_DISALLOWED'] = 'Sorry, but this username has been disallowed.';
$lang['USERNAME_TOO_LONG'] = 'Your name is too long.';
$lang['USERNAME_TOO_SMALL'] = 'Your name is too small.';
$lang['EMAIL_TAKEN'] = 'Sorry, but that e-mail address is already registered to a user.';
$lang['EMAIL_BANNED'] = 'Sorry, but <b>%s</b> address has been banned.';
$lang['EMAIL_INVALID'] = 'Sorry, but this e-mail address is invalid.';
$lang['EMAIL_TOO_LONG'] = 'Your email is too long.';
$lang['SIGNATURE_TOO_LONG'] = 'Your signature is too long.';
$lang['SIGNATURE_ERROR_HTML'] = 'The signature can contain only BBCode';
$lang['FIELDS_EMPTY'] = 'You must fill in the required fields.';

$lang['WELCOME_SUBJECT'] = 'Welcome to %s Forums'; // Welcome to my.com forums
$lang['NEW_ACCOUNT_SUBJECT'] = 'New user account';
$lang['ACCOUNT_ACTIVATED_SUBJECT'] = 'Account Activated';

$lang['ACCOUNT_ADDED'] = 'Thank you for registering. Your account has been created. You may now log in with your username and password';
$lang['ACCOUNT_INACTIVE'] = 'Your account has been created. However, this forum requires account activation. An activation key has been sent to the e-mail address you provided. Please check your e-mail for further information';
$lang['ACCOUNT_ACTIVE'] = 'Your account has just been activated. Thank you for registering';
$lang['REACTIVATE'] = 'Reactivate your account!';
$lang['ALREADY_ACTIVATED'] = 'You have already activated your account';

$lang['REGISTRATION'] = 'Registration Agreement Terms';

$lang['WRONG_ACTIVATION'] = 'The activation key you supplied does not match any in the database.';
$lang['SEND_PASSWORD'] = 'Send me a new password';
$lang['PASSWORD_UPDATED'] = 'A new password has been created; please check your e-mail for details on how to activate it.';
$lang['NO_EMAIL_MATCH'] = 'The e-mail address you supplied does not match the one listed for that username.';
$lang['NEW_PASSWORD_ACTIVATION'] = 'New password activation';
$lang['PASSWORD_ACTIVATED'] = 'Your account has been re-activated. To log in, please use the password supplied in the e-mail you received.';

$lang['SEND_EMAIL_MSG'] = 'Send an e-mail message';
$lang['NO_USER_SPECIFIED'] = 'No user was specified';
$lang['USER_PREVENT_EMAIL'] = 'This user does not wish to receive e-mail. Try sending them a private message.';
$lang['USER_NOT_EXIST'] = 'That user does not exist';
$lang['EMAIL_MESSAGE_DESC'] = 'This message will be sent as plain text, so do not include any HTML or BBCode. The return address for this message will be set to your e-mail address.';
$lang['FLOOD_EMAIL_LIMIT'] = 'You cannot send another e-mail at this time. Try again later.';
$lang['RECIPIENT'] = 'Recipient';
$lang['EMAIL_SENT'] = 'The e-mail has been sent.';
$lang['SEND_EMAIL'] = 'Send e-mail';
$lang['EMPTY_SUBJECT_EMAIL'] = 'You must specify a subject for the e-mail.';
$lang['EMPTY_MESSAGE_EMAIL'] = 'You must enter a message to be e-mailed.';

$lang['USER_AGREEMENT'] = 'User agreement';
$lang['USER_AGREEMENT_HEAD'] = 'In order to proceed, you must agree with the following rules';
$lang['USER_AGREEMENT_AGREE'] = 'I have read and agree to the User agreement above';

$lang['COPYRIGHT_HOLDERS'] = 'For copyright holders';
$lang['ADVERT'] = 'Advertise on this site';
$lang['NOT_FOUND'] = 'File not found';

// Memberslist
$lang['SORT'] = 'Sort';
$lang['SORT_TOP_TEN'] = 'Top Ten Posters';
$lang['SORT_JOINED'] = 'Joined Date';
$lang['SORT_USERNAME'] = 'Username';
$lang['SORT_LOCATION'] = 'Location';
$lang['SORT_POSTS'] = 'Total posts';
$lang['SORT_EMAIL'] = 'Email';
$lang['SORT_WEBSITE'] = 'Website';
$lang['ASC'] = 'Ascending';
$lang['DESC'] = 'Descending';
$lang['ORDER'] = 'Order';

// Group control panel
$lang['GROUP_CONTROL_PANEL'] = 'User Groups';
$lang['GROUP_CONFIGURATION'] = 'Group Configuration';
$lang['GROUP_GOTO_CONFIG'] = 'Go to Group Configuration panel';
$lang['GROUP_RETURN'] = 'Return to User Group page';
$lang['MEMBERSHIP_DETAILS'] = 'Group Membership Details';
$lang['JOIN_A_GROUP'] = 'Join a Group';

$lang['GROUP_INFORMATION'] = 'Group Information';
$lang['GROUP_NAME'] = 'Group name';
$lang['GROUP_DESCRIPTION'] = 'Group description';
$lang['GROUP_SIGNATURE'] = 'Group signature';
$lang['GROUP_MEMBERSHIP'] = 'Group membership';
$lang['GROUP_MEMBERS'] = 'Group Members';
$lang['GROUP_MODERATOR'] = 'Group Moderator';
$lang['PENDING_MEMBERS'] = 'Pending Members';

$lang['GROUP_TIME'] = 'Created';
$lang['RELEASE_GROUP'] = 'Release Group';

$lang['GROUP_TYPE'] = 'Group type';
$lang['GROUP_OPEN'] = 'Open group';
$lang['GROUP_CLOSED'] = 'Closed group';
$lang['GROUP_HIDDEN'] = 'Hidden group';

$lang['GROUP_MEMBER_MOD'] = 'Group moderator';
$lang['GROUP_MEMBER_MEMBER'] = 'Current memberships';
$lang['GROUP_MEMBER_PENDING'] = 'Memberships pending';
$lang['GROUP_MEMBER_OPEN'] = 'Open groups';
$lang['GROUP_MEMBER_CLOSED'] = 'Closed groups';
$lang['GROUP_MEMBER_HIDDEN'] = 'Hidden groups';

$lang['NO_GROUPS_EXIST'] = 'No Groups Exist';
$lang['GROUP_NOT_EXIST'] = 'That user group does not exist';
$lang['NO_GROUP_ID_SPECIFIED'] = 'Group ID is not specified';

$lang['NO_GROUP_MEMBERS'] = 'This group has no members';
$lang['HIDDEN_GROUP_MEMBERS'] = 'This group is hidden; you cannot view its membership';
$lang['NO_PENDING_GROUP_MEMBERS'] = 'This group has no pending members';
$lang['GROUP_JOINED'] = 'You have successfully subscribed to this group.<br />You will be notified when your subscription is approved by the group moderator.';
$lang['GROUP_REQUEST'] = 'A request to join your group has been made.';
$lang['GROUP_APPROVED'] = 'Your request has been approved.';
$lang['GROUP_ADDED'] = 'You have been added to this usergroup.';
$lang['ALREADY_MEMBER_GROUP'] = 'You are already a member of this group';
$lang['USER_IS_MEMBER_GROUP'] = 'User is already a member of this group';
$lang['GROUP_TYPE_UPDATED'] = 'Successfully updated group type.';
$lang['EFFECTIVE_DATE'] = 'Effective Date';

$lang['COULD_NOT_ADD_USER'] = 'The user you selected does not exist.';
$lang['COULD_NOT_ANON_USER'] = 'You cannot make Anonymous a group member.';

$lang['CONFIRM_UNSUB'] = 'Are you sure you want to unsubscribe from this group?';
$lang['CONFIRM_UNSUB_PENDING'] = 'Your subscription to this group has not yet been approved; are you sure you want to unsubscribe?';

$lang['UNSUB_SUCCESS'] = 'You have been un-subscribed from this group.';

$lang['APPROVE_SELECTED'] = 'Approve Selected';
$lang['DENY_SELECTED'] = 'Deny Selected';
$lang['NOT_LOGGED_IN'] = 'You must be logged in to join a group.';
$lang['REMOVE_SELECTED'] = 'Remove Selected';
$lang['ADD_MEMBER'] = 'Add Member';
$lang['NOT_GROUP_MODERATOR'] = 'You are not this group\'s moderator, therefore you cannot perform that action.';

$lang['LOGIN_TO_JOIN'] = 'Log in to join or manage group memberships';
$lang['THIS_OPEN_GROUP'] = 'This is an open group: click to request membership';
$lang['THIS_CLOSED_GROUP'] = 'This is a closed group: no more users accepted';
$lang['THIS_HIDDEN_GROUP'] = 'This is a hidden group: automatic user addition is not allowed';
$lang['MEMBER_THIS_GROUP'] = 'You are a member of this group';
$lang['PENDING_THIS_GROUP'] = 'Your membership of this group is pending';
$lang['ARE_GROUP_MODERATOR'] = 'You are the group moderator';
$lang['NONE'] = 'None';

$lang['SUBSCRIBE'] = 'Subscribe';
$lang['UNSUBSCRIBE_GROUP'] = 'Unsubscribe';
$lang['VIEW_INFORMATION'] = 'View Information';
$lang['MEMBERS_IN_GROUP'] = 'Members in group';

// Release Groups
$lang['POST_RELEASE_FROM_GROUP'] = 'Post release from group';
$lang['CHOOSE_RELEASE_GROUP'] = 'not selected';
$lang['ATTACH_RG_SIG'] = 'attach release group signature';
$lang['RELEASE_FROM_RG'] = 'Release was prepared by';
$lang['GROUPS_RELEASES'] = 'Group\'s releases';
$lang['MORE_RELEASES'] = 'Find all releases of the group';
$lang['NOT_A_RELEASE_GROUP'] = 'This group is not a release group';

// Search
$lang['SEARCH_OFF'] = 'Search is temporarily disabled';
$lang['SEARCH_ERROR'] = 'At the moment, the search engine is not available<br /><br />Try to repeat the request after several seconds';
$lang['SEARCH_HELP_URL'] = 'Search Help';
$lang['SEARCH_QUERY'] = 'Search Query';
$lang['SEARCH_OPTIONS'] = 'Search Options';

$lang['SEARCH_WORDS'] = 'Search for Keywords';
$lang['SEARCH_WORDS_EXPL'] = 'You can use <b>+</b> to define words which must be in the results and <b>-</b> to define words which should not be in the result (ex: "+word1 -word2"). Use * as a wildcard for partial matches';
$lang['SEARCH_AUTHOR'] = 'Search for Author';
$lang['SEARCH_AUTHOR_EXPL'] = 'Use * as a wildcard for partial matches';

$lang['SEARCH_TITLES_ONLY'] = 'Search topic titles only';
$lang['SEARCH_ALL_WORDS'] = 'all words';
$lang['SEARCH_MY_MSG_ONLY'] = 'Search only in my posts';
$lang['IN_MY_POSTS'] = 'In my posts';
$lang['SEARCH_MY_TOPICS'] = 'in my topics';
$lang['NEW_TOPICS'] = 'New topics';

$lang['RETURN_FIRST'] = 'Return first'; // followed by xxx characters in a select box
$lang['CHARACTERS_POSTS'] = 'characters of posts';

$lang['SEARCH_PREVIOUS'] = 'Search previous';

$lang['SORT_BY'] = 'Sort by';
$lang['SORT_TIME'] = 'Post Time';
$lang['SORT_POST_SUBJECT'] = 'Post Subject';
$lang['SORT_TOPIC_TITLE'] = 'Topic Title';
$lang['SORT_AUTHOR'] = 'Author';
$lang['SORT_FORUM'] = 'Forum';

$lang['DISPLAY_RESULTS_AS'] = 'Display results as';
$lang['ALL_AVAILABLE'] = 'All available';
$lang['BRIEFLY'] = 'Briefly';
$lang['NO_SEARCHABLE_FORUMS'] = 'You do not have permissions to search any forum on this site.';

$lang['NO_SEARCH_MATCH'] = 'No topics or posts met your search criteria';
$lang['FOUND_SEARCH_MATCH'] = 'Search found %d match'; // eg. Search found 1 match
$lang['FOUND_SEARCH_MATCHES'] = 'Search found %d matches'; // eg. Search found 24 matches
$lang['TOO_MANY_SEARCH_RESULTS'] = 'Too many results may be found, please try to be more specific';

$lang['CLOSE_WINDOW'] = 'Close Window';
$lang['CLOSE'] = 'close';
$lang['HIDE'] = 'hide';
$lang['SEARCH_TERMS'] = 'Search terms';

// Auth related entries
// Note the %s will be replaced with one of the following 'user' arrays
$lang['SORRY_AUTH_VIEW'] = 'Sorry, but only %s can view this forum.';
$lang['SORRY_AUTH_READ'] = 'Sorry, but only %s can read topics in this forum.';
$lang['SORRY_AUTH_POST'] = 'Sorry, but only %s can post topics in this forum.';
$lang['SORRY_AUTH_REPLY'] = 'Sorry, but only %s can reply to posts in this forum.';
$lang['SORRY_AUTH_EDIT'] = 'Sorry, but only %s can edit posts in this forum.';
$lang['SORRY_AUTH_DELETE'] = 'Sorry, but only %s can delete posts in this forum.';
$lang['SORRY_AUTH_VOTE'] = 'Sorry, but only %s can vote in polls in this forum.';
$lang['SORRY_AUTH_STICKY'] = 'Sorry, but only %s can post sticky messages in this forum.';
$lang['SORRY_AUTH_ANNOUNCE'] = 'Sorry, but only %s can post announcements in this forum.';

// These replace the %s in the above strings
$lang['AUTH_ANONYMOUS_USERS'] = '<b>anonymous users</b>';
$lang['AUTH_REGISTERED_USERS'] = '<b>registered users</b>';
$lang['AUTH_USERS_GRANTED_ACCESS'] = '<b>users granted special access</b>';
$lang['AUTH_MODERATORS'] = '<b>moderators</b>';
$lang['AUTH_ADMINISTRATORS'] = '<b>administrators</b>';

$lang['NOT_MODERATOR'] = 'You are not a moderator of this forum.';
$lang['NOT_AUTHORISED'] = 'Not Authorised';

$lang['YOU_BEEN_BANNED'] = 'You have been banned from this forum.<br />Please contact the webmaster or board administrator for more information.';

// Viewonline
$lang['ONLINE_EXPLAIN'] = 'users active over the past five minutes';
$lang['LAST_UPDATED'] = 'Last Updated';

// Moderator Control Panel
$lang['MOD_CP'] = 'Moderator Control Panel';
$lang['MOD_CP_EXPLAIN'] = 'Using the form below you can perform mass moderation operations on this forum. You can lock, unlock, move or delete any number of topics.';

$lang['SELECT'] = 'Select';
$lang['DELETE'] = 'Delete';
$lang['MOVE'] = 'Move';
$lang['LOCK'] = 'Lock';
$lang['UNLOCK'] = 'Unlock';

$lang['TOPICS_REMOVED'] = 'The selected topics have been successfully removed from the database.';
$lang['NO_TOPICS_REMOVED'] = 'No topics were removed.';
$lang['TOPICS_LOCKED'] = 'The selected topics have been locked.';
$lang['TOPICS_MOVED'] = 'The selected topics have been moved.';
$lang['TOPICS_UNLOCKED'] = 'The selected topics have been unlocked.';
$lang['NO_TOPICS_MOVED'] = 'No topics were moved.';

$lang['CONFIRM_DELETE_TOPIC'] = 'Are you sure you want to remove the selected topic/s?';
$lang['CONFIRM_LOCK_TOPIC'] = 'Are you sure you want to lock the selected topic/s?';
$lang['CONFIRM_UNLOCK_TOPIC'] = 'Are you sure you want to unlock the selected topic/s?';
$lang['CONFIRM_MOVE_TOPIC'] = 'Are you sure you want to move the selected topic/s?';

$lang['MOVE_TO_FORUM'] = 'Move to forum';
$lang['LEAVE_SHADOW_TOPIC'] = 'Leave shadow topic in old forum.';

$lang['SPLIT_TOPIC_EXPLAIN'] = 'Using the form below you can split a topic in two, either by selecting the posts individually or by splitting at a selected post';
$lang['NEW_TOPIC_TITLE'] = 'New topic title';
$lang['FORUM_FOR_NEW_TOPIC'] = 'Forum for new topic';
$lang['SPLIT_POSTS'] = 'Split selected posts';
$lang['SPLIT_AFTER'] = 'Split from selected post';
$lang['TOPIC_SPLIT'] = 'The selected topic has been split successfully';

$lang['TOO_MANY_ERROR'] = 'You have selected too many posts. You can only select one post to split a topic after!';

$lang['NONE_SELECTED'] = 'You have none selected to perform this operation on. Please go back and select at least one.';
$lang['NEW_FORUM'] = 'New forum';

$lang['THIS_POSTS_IP'] = 'IP address for this post';
$lang['OTHER_IP_THIS_USER'] = 'Other IP addresses this user has posted from';
$lang['USERS_THIS_IP'] = 'Users posting from this IP address';
$lang['IP_INFO'] = 'IP Information';
$lang['LOOKUP_IP'] = 'Look up IP address';

// Timezones ... for display on each page
$lang['ALL_TIMES'] = 'All times are <span class="tz_time">%s</span>'; // This is followed by UTC and the timezone offset

// These are displayed in the timezone select box
$lang['TZ']['-12'] = 'UTC - 12';
$lang['TZ']['-11'] = 'UTC - 11';
$lang['TZ']['-10'] = 'UTC - 10';
$lang['TZ']['-9'] = 'UTC - 9';
$lang['TZ']['-8'] = 'UTC - 8';
$lang['TZ']['-7'] = 'UTC - 7';
$lang['TZ']['-6'] = 'UTC - 6';
$lang['TZ']['-5'] = 'UTC - 5';
$lang['TZ']['-4'] = 'UTC - 4';
$lang['TZ']['-3.5'] = 'UTC - 3.5';
$lang['TZ']['-3'] = 'UTC - 3';
$lang['TZ']['-2'] = 'UTC - 2';
$lang['TZ']['-1'] = 'UTC - 1';
$lang['TZ']['0'] = 'UTC ± 0';
$lang['TZ']['1'] = 'UTC + 1';
$lang['TZ']['2'] = 'UTC + 2';
$lang['TZ']['3'] = 'UTC + 3';
$lang['TZ']['3.5'] = 'UTC + 3.5';
$lang['TZ']['4'] = 'UTC + 4';
$lang['TZ']['4.5'] = 'UTC + 4.5';
$lang['TZ']['5'] = 'UTC + 5';
$lang['TZ']['5.5'] = 'UTC + 5.5';
$lang['TZ']['6'] = 'UTC + 6';
$lang['TZ']['6.5'] = 'UTC + 6.5';
$lang['TZ']['7'] = 'UTC + 7';
$lang['TZ']['8'] = 'UTC + 8';
$lang['TZ']['9'] = 'UTC + 9';
$lang['TZ']['9.5'] = 'UTC + 9.5';
$lang['TZ']['10'] = 'UTC + 10';
$lang['TZ']['11'] = 'UTC + 11';
$lang['TZ']['12'] = 'UTC + 12';
$lang['TZ']['13'] = 'UTC + 13';

$lang['DATETIME']['TODAY'] = 'Today';
$lang['DATETIME']['YESTERDAY'] = 'Yesterday';

$lang['DATETIME']['SUNDAY'] = 'Sunday';
$lang['DATETIME']['MONDAY'] = 'Monday';
$lang['DATETIME']['TUESDAY'] = 'Tuesday';
$lang['DATETIME']['WEDNESDAY'] = 'Wednesday';
$lang['DATETIME']['THURSDAY'] = 'Thursday';
$lang['DATETIME']['FRIDAY'] = 'Friday';
$lang['DATETIME']['SATURDAY'] = 'Saturday';
$lang['DATETIME']['SUN'] = 'Sun';
$lang['DATETIME']['MON'] = 'Mon';
$lang['DATETIME']['TUE'] = 'Tue';
$lang['DATETIME']['WED'] = 'Wed';
$lang['DATETIME']['THU'] = 'Thu';
$lang['DATETIME']['FRI'] = 'Fri';
$lang['DATETIME']['SAT'] = 'Sat';
$lang['DATETIME']['JANUARY'] = 'January';
$lang['DATETIME']['FEBRUARY'] = 'February';
$lang['DATETIME']['MARCH'] = 'March';
$lang['DATETIME']['APRIL'] = 'April';
$lang['DATETIME']['MAY'] = 'May';
$lang['DATETIME']['JUNE'] = 'June';
$lang['DATETIME']['JULY'] = 'July';
$lang['DATETIME']['AUGUST'] = 'August';
$lang['DATETIME']['SEPTEMBER'] = 'September';
$lang['DATETIME']['OCTOBER'] = 'October';
$lang['DATETIME']['NOVEMBER'] = 'November';
$lang['DATETIME']['DECEMBER'] = 'December';
$lang['DATETIME']['JAN'] = 'Jan';
$lang['DATETIME']['FEB'] = 'Feb';
$lang['DATETIME']['MAR'] = 'Mar';
$lang['DATETIME']['APR'] = 'Apr';
$lang['DATETIME']['JUN'] = 'Jun';
$lang['DATETIME']['JUL'] = 'Jul';
$lang['DATETIME']['AUG'] = 'Aug';
$lang['DATETIME']['SEP'] = 'Sep';
$lang['DATETIME']['OCT'] = 'Oct';
$lang['DATETIME']['NOV'] = 'Nov';
$lang['DATETIME']['DEC'] = 'Dec';

// Errors
$lang['INFORMATION'] = 'Information';
$lang['ADMIN_REAUTHENTICATE'] = 'To administer/moderate the board you must re-authenticate yourself.';

// Attachment Mod Main Language Variables
// Auth Related Entries
$lang['RULES_ATTACH_CAN'] = 'You <b>can</b> attach files in this forum';
$lang['RULES_ATTACH_CANNOT'] = 'You <b>cannot</b> attach files in this forum';
$lang['RULES_DOWNLOAD_CAN'] = 'You <b>can</b> download files in this forum';
$lang['RULES_DOWNLOAD_CANNOT'] = 'You <b>cannot</b> download files in this forum';
$lang['SORRY_AUTH_VIEW_ATTACH'] = 'Sorry but you are not authorized to view or download this Attachment';

// Viewtopic -> Display of Attachments
$lang['DESCRIPTION'] = 'Description'; // used in Administration Panel too...
$lang['DOWNLOAD'] = 'Download'; // this Language Variable is defined in admin.php too, but we are unable to access it from the main Language File
$lang['FILESIZE'] = 'Filesize';
$lang['VIEWED'] = 'Viewed';
$lang['DOWNLOAD_NUMBER'] = '%d times'; // replace %d with count
$lang['EXTENSION_DISABLED_AFTER_POSTING'] = 'The Extension \'%s\' was deactivated by an board admin, therefore this Attachment is not displayed.'; // used in Posts and PM's, replace %s with mime type

$lang['ATTACHMENT'] = 'Attachments';
$lang['ATTACHMENT_THUMBNAIL'] = 'Attachment Thumbnail';

// Posting/PM -> Posting Attachments
$lang['ADD_ATTACHMENT'] = 'Add Attachment';
$lang['ADD_ATTACHMENT_TITLE'] = 'Add an Attachment';
$lang['ADD_ATTACHMENT_EXPLAIN'] = 'If you do not want to add an Attachment to your Post, please leave the Fields blank';
$lang['FILENAME'] = 'Filename';
$lang['FILE_COMMENT'] = 'File Comment';

// Posting/PM -> Posted Attachments
$lang['POSTED_ATTACHMENTS'] = 'Posted Attachments';
$lang['UPDATE_COMMENT'] = 'Update Comment';
$lang['DELETE_ATTACHMENTS'] = 'Delete Attachments';
$lang['DELETE_ATTACHMENT'] = 'Delete Attachment';
$lang['DELETE_THUMBNAIL'] = 'Delete Thumbnail';
$lang['UPLOAD_NEW_VERSION'] = 'Upload New Version';

// Errors -> Posting Attachments
$lang['INVALID_FILENAME'] = '%s is an invalid filename'; // replace %s with given filename
$lang['ATTACHMENT_PHP_SIZE_NA'] = 'The Attachment is too big.<br />Could not get the maximum Size defined in PHP.<br />The Attachment Mod is unable to determine the maximum Upload Size defined in the php.ini.';
$lang['ATTACHMENT_PHP_SIZE_OVERRUN'] = 'The Attachment is too big.<br />Maximum Upload Size: %d MB.<br />Please note that this Size is defined in php.ini, this means it\'s set by PHP and the Attachment Mod can not override this value.'; // replace %d with ini_get('upload_max_filesize')
$lang['DISALLOWED_EXTENSION'] = 'The Extension %s is not allowed'; // replace %s with extension (e.g. .php)
$lang['DISALLOWED_EXTENSION_WITHIN_FORUM'] = 'You are not allowed to post Files with the Extension %s within this Forum'; // replace %s with the Extension
$lang['ATTACHMENT_TOO_BIG'] = 'The Attachment is too big.<br />Max Size: %d'; // replace %d with maximum file size, %s with size var
$lang['ATTACH_QUOTA_REACHED'] = 'Sorry, but the maximum filesize for all Attachments is reached. Please contact the Board Administrator if you have questions.';
$lang['TOO_MANY_ATTACHMENTS'] = 'Attachment cannot be added, since the max. number of %d Attachments in this post was achieved'; // replace %d with maximum number of attachments
$lang['ERROR_IMAGESIZE'] = 'The Attachment/Image must be less than %d pixels wide and %d pixels high';
$lang['GENERAL_UPLOAD_ERROR'] = 'Upload Error: Could not upload Attachment to %s.'; // replace %s with local path

$lang['ERROR_EMPTY_ADD_ATTACHBOX'] = 'You have to enter values in the \'Add an Attachment\' Box';
$lang['ERROR_MISSING_OLD_ENTRY'] = 'Unable to Update Attachment, could not find old Attachment Entry';

// Errors -> PM Related
$lang['ATTACH_QUOTA_SENDER_PM_REACHED'] = 'Sorry, but the maximum filesize for all Attachments in your Private Message Folder has been reached. Please delete some of your received/sent Attachments.';
$lang['ATTACH_QUOTA_RECEIVER_PM_REACHED'] = 'Sorry, but the maximum filesize for all Attachments in the Private Message Folder of \'%s\' has been reached. Please let him know, or wait until he/she has deleted some of his/her Attachments.';

// Errors -> Download
$lang['NO_ATTACHMENT_SELECTED'] = 'You haven\'t selected an attachment to download or view.';
$lang['ERROR_NO_ATTACHMENT'] = 'The selected Attachment does not exist anymore';

// Delete Attachments
$lang['CONFIRM_DELETE_ATTACHMENTS'] = 'Are you sure you want to delete the selected Attachments?';
$lang['DELETED_ATTACHMENTS'] = 'The selected Attachments have been deleted.';
$lang['ERROR_DELETED_ATTACHMENTS'] = 'Could not delete Attachments.';
$lang['CONFIRM_DELETE_PM_ATTACHMENTS'] = 'Are you sure you want to delete all Attachments posted in this PM?';

// General Error Messages
$lang['ATTACHMENT_FEATURE_DISABLED'] = 'The Attachment Feature is disabled.';

$lang['DIRECTORY_DOES_NOT_EXIST'] = 'The Directory \'%s\' does not exist or Could not be found.'; // replace %s with directory
$lang['DIRECTORY_IS_NOT_A_DIR'] = 'Please check if \'%s\' is a directory.'; // replace %s with directory
$lang['DIRECTORY_NOT_WRITEABLE'] = 'Directory \'%s\' is not writeable. You\'ll have to create the upload path and chmod it to 777 (or change the owner to you httpd-servers owner) to upload files.<br />If you have only plain FTP-access change the \'Attribute\' of the directory to rwxrwxrwx.'; // replace %s with directory

// Quota Variables
$lang['UPLOAD_QUOTA'] = 'Upload Quota';
$lang['PM_QUOTA'] = 'PM Quota';

// Common Variables
$lang['BYTES'] = 'Bytes';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';
$lang['GB'] = 'GB';
$lang['ATTACH_SEARCH_QUERY'] = 'Search Attachments';
$lang['TEST_SETTINGS'] = 'Test Settings';
$lang['NOT_ASSIGNED'] = 'Not Assigned';
$lang['NO_FILE_COMMENT_AVAILABLE'] = 'No File Comment available';
$lang['ATTACHBOX_LIMIT'] = 'Your Attachbox is<br /><b>%d%%</b> full';
$lang['NO_QUOTA_LIMIT'] = 'No Quota Limit';
$lang['UNLIMITED'] = 'Unlimited';

//bt
$lang['BT_REG_YES'] = 'Registered';
$lang['BT_REG_NO'] = 'Not registered';
$lang['BT_ADDED'] = 'Added';
$lang['BT_REG_ON_TRACKER'] = 'Register on tracker';
$lang['BT_REG_FAIL'] = 'Could not register torrent on tracker';
$lang['BT_REG_FAIL_SAME_HASH'] = 'Another torrent with same info_hash already <a href="%s"><b>registered</b></a>';
$lang['BT_UNREG_FROM_TRACKER'] = 'Remove from tracker';
$lang['BT_UNREGISTERED'] = 'Torrent unregistered';
$lang['BT_REGISTERED'] = 'Torrent registered on tracker<br /><br />Now you need to <a href="%s"><b>download your torrent</b></a> and run it using your BitTorrent client choosing the folder with the original files you\'re sharing as the download path';
$lang['INVALID_ANN_URL'] = 'Invalid Announce URL [%s]<br /><br />must be <b>%s</b>';
$lang['PASSKEY_ERR_TOR_NOT_REG'] = 'Could not add passkey<br /><br />Torrent not registered on tracker';
$lang['PASSKEY_ERR_EMPTY'] = 'Could not add passkey (passkey is empty)<br /><br />Go to <a href="%s" target="_blank"><b>your forum profile</b></a> and generate it';
$lang['BT_PASSKEY'] = 'Passkey';
$lang['BT_GEN_PASSKEY'] = 'create a new';
$lang['BT_PASSKEY_VIEW'] = 'show';
$lang['BT_GEN_PASSKEY_NEW'] = "Attention! After changing the new passkey, you will need to re-download all the active torrents! \n Are you sure you want to create a new passkey?";
$lang['BT_NO_SEARCHABLE_FORUMS'] = 'No searchable forums found';

$lang['SEEDS'] = 'Seed';
$lang['LEECHS'] = 'Leech';
$lang['SPEED_UP'] = 'Speed Up';
$lang['SPEED_DOWN'] = 'Speed Down';

$lang['SEEDERS'] = 'Seeders';
$lang['LEECHERS'] = 'Leechers';
$lang['RELEASING'] = 'Self';
$lang['SEEDING'] = 'Seed';
$lang['LEECHING'] = 'Leech';
$lang['IS_REGISTERED'] = 'Registered';
$lang['MAGNET'] = 'Magnet';
$lang['DC_MAGNET'] = 'Search in DC++ by filename';
$lang['DC_MAGNET_EXT'] = 'Search in DC++ by extension';

//torrent status mod
$lang['TOR_STATUS'] = 'Status';
$lang['TOR_STATUS_SELECT_ACTION'] = 'Select status';
$lang['TOR_STATUS_NOT_SELECT'] = 'You have not selected status.';
$lang['TOR_STATUS_SELECT_ALL'] = 'All statuses';
$lang['TOR_STATUS_NAME'] = array(
    TOR_NOT_APPROVED => 'not checked',
    TOR_CLOSED => 'closed',
    TOR_APPROVED => 'checked',
    TOR_NEED_EDIT => 'not formalized until',
    TOR_NO_DESC => 'not formalized',
    TOR_DUP => 'repeat',
    TOR_CLOSED_CPHOLD => 'closed right',
    TOR_CONSUMED => 'absorbed',
    TOR_DOUBTFUL => 'doubtful',
    TOR_CHECKING => 'verified',
    TOR_TMP => 'temporary',
    TOR_PREMOD => 'pre-moderation',
);
$lang['TOR_STATUS_FAILED'] = 'Such status does not exist!';
$lang['TORRENT_FAILED'] = 'Distribution was not found!';
$lang['TOR_STATUS_DUB'] = 'Distribution has the same status';
$lang['TOR_DONT_CHANGE'] = 'Change of status can not be!';
$lang['TOR_STATUS_OF'] = 'Distribution has the status of:';
$lang['TOR_STATUS_CHANGED'] = 'Status changed: ';
$lang['TOR_BACK'] = ' back';
$lang['PROCEED'] = 'Proceed';
$lang['INVALID_ATTACH_ID'] = 'Missing file identifier!';
$lang['CHANGE_TOR_TYPE'] = 'Type the torrent successfully changed';
$lang['DEL_TORRENT'] = 'Are you sure you want to delete the torrent?';
$lang['DEL_MOVE_TORRENT'] = 'Are you sure you want to delete and move the topic?';
$lang['UNEXECUTED_RELEASE'] = 'Do you have a shapeless release before creating a new fix his unformed!';
$lang['STATUS_DOES_EXIST'] = 'Such status does not exist: ';

// tor_comment
$lang['TOR_MOD_TITLE'] = 'Changing the status of distribution - %s';
$lang['TOR_MOD_MSG'] = "Hello, %s.\n\n Status [url=%s]your[/url] distribution is changed to [b]%s[/b]";

$lang['TOR_AUTH_TITLE'] = 'Changes in the design - %s';
$lang['TOR_AUTH_MSG'] = "Hello, %s.\n\n Making my distribution changed - [url=%s]%s[/url]\n\n Please re-check it.";
$lang['TOR_AUTH_FIXED'] = 'Fixed';
$lang['TOR_AUTH_SENT_COMMENT'] = ' &middot; <span class="seed bold">The information sent to the moderator. Expect.</span>';

$lang['BT_TOPIC_TITLE'] = 'Topic title';
$lang['BT_SEEDER_LAST_SEEN'] = 'Seed last seen';
$lang['BT_SORT_FORUM'] = 'Forum';
$lang['SIZE'] = 'Size';
$lang['PIECE_LENGTH'] = 'Piece length';
$lang['COMPLETED'] = 'Completed';
$lang['ADDED'] = 'Added';
$lang['DELETE_TORRENT'] = 'Delete torrent';
$lang['DELETE_MOVE_TORRENT'] = 'Delete and move topic';
$lang['DL_TORRENT'] = 'Download .torrent';
$lang['BT_LAST_POST'] = 'Last post';
$lang['BT_CREATED'] = 'Topic posted';
$lang['BT_REPLIES'] = 'Replies';
$lang['BT_VIEWS'] = 'Views';

// Gold/Silver releases
$lang['GOLD'] = 'Gold';
$lang['SILVER'] = 'Silver';
$lang['SET_GOLD_TORRENT'] = 'Make gold';
$lang['UNSET_GOLD_TORRENT'] = 'UnMake gold';
$lang['SET_SILVER_TORRENT'] = 'Make silver';
$lang['UNSET_SILVER_TORRENT'] = 'UnMake silver';
$lang['GOLD_STATUS'] = 'GOLD TORRENT! DOWNLOAD TRAFFIC DOES NOT CONSIDER!';
$lang['SILVER_STATUS'] = 'SILVER TORRENT! DOWNLOAD TRAFFIC PARTIALLY CONSIDERED!';

$lang['SEARCH_IN_FORUMS'] = 'Search in Forums';
$lang['SELECT_CAT'] = 'Select category';
$lang['GO_TO_SECTION'] = 'Goto section';
$lang['TORRENTS_FROM'] = 'Posts from';
$lang['SHOW_ONLY'] = 'Show only';
$lang['SHOW_COLUMN'] = 'Show column';
$lang['SEL_CHAPTERS'] = 'Link to the selected partitions';
$lang['NOT_SEL_CHAPTERS'] = 'You have not selected topics';
$lang['SEL_CHAPTERS_HELP'] = 'You can select a maximum %s partition';
$lang['HIDE_CONTENTS'] = 'Hide the contents of {...}';
$lang['FILTER_BY_NAME'] = '<i>Filter by name </i>';

$lang['BT_ONLY_ACTIVE'] = 'Active';
$lang['BT_ONLY_MY'] = 'My releases';
$lang['BT_SEED_EXIST'] = 'Seeder exist';
$lang['BT_ONLY_NEW'] = 'New from last visit';
$lang['BT_SHOW_CAT'] = 'Category';
$lang['BT_SHOW_FORUM'] = 'Forum';
$lang['BT_SHOW_AUTHOR'] = 'Author';
$lang['BT_SHOW_SPEED'] = 'Speed';
$lang['SEED_NOT_SEEN'] = 'Seeder not seen';
$lang['TITLE_MATCH'] = 'Title match';
$lang['BT_USER_NOT_FOUND'] = 'not found';
$lang['DL_SPEED'] = 'Overall download speed';

$lang['BT_DISREGARD'] = 'disregarding';
$lang['BT_NEVER'] = 'never';
$lang['BT_ALL_DAYS_FOR'] = 'all the time';
$lang['BT_1_DAY_FOR'] = 'last day';
$lang['BT_3_DAY_FOR'] = 'last three days';
$lang['BT_7_DAYS_FOR'] = 'last week';
$lang['BT_2_WEEKS_FOR'] = 'last two weeks';
$lang['BT_1_MONTH_FOR'] = 'last month';
$lang['BT_1_DAY'] = '1 day';
$lang['BT_3_DAYS'] = '3 days';
$lang['BT_7_DAYS'] = 'week';
$lang['BT_2_WEEKS'] = '2 weeks';
$lang['BT_1_MONTH'] = 'month';

$lang['DL_LIST_AND_TORRENT_ACTIVITY'] = 'DL-List and Torrent activity';
$lang['DLWILL'] = 'Will download';
$lang['DLDOWN'] = 'Downloading';
$lang['DLCOMPLETE'] = 'Complete';
$lang['DLCANCEL'] = 'Cancel';

$lang['DL_LIST_DEL'] = 'Clear DL-List';
$lang['DL_LIST_DEL_CONFIRM'] = 'Delete DL-List for this topic?';
$lang['SHOW_DL_LIST'] = 'Show DL-List';
$lang['SET_DL_STATUS'] = 'Download';
$lang['UNSET_DL_STATUS'] = 'Not Download';
$lang['TOPICS_DOWN_SETS'] = 'Topic status changed to <b>Download</b>';
$lang['TOPICS_DOWN_UNSETS'] = '<b>Download</b> status removed';

$lang['TOPIC_DL'] = 'DL';

$lang['MY_DOWNLOADS'] = 'My Downloads';
$lang['SEARCH_DL_WILL'] = 'Planned';
$lang['SEARCH_DL_WILL_DOWNLOADS'] = 'Planned Downloads';
$lang['SEARCH_DL_DOWN'] = 'Current';
$lang['SEARCH_DL_COMPLETE'] = 'Completed';
$lang['SEARCH_DL_COMPLETE_DOWNLOADS'] = 'Completed Downloads';
$lang['SEARCH_DL_CANCEL'] = 'Canceled';
$lang['CUR_DOWNLOADS'] = 'Current Downloads';
$lang['CUR_UPLOADS'] = 'Current Uploads';
$lang['SEARCH_RELEASES'] = 'Releases';
$lang['TOR_SEARCH_TITLE'] = 'Torrent search options';
$lang['OPEN_TOPIC'] = 'Open topic';

$lang['ALLOWED_ONLY_1ST_POST_ATTACH'] = 'Posting torrents allowed only in first post';
$lang['ALLOWED_ONLY_1ST_POST_REG'] = 'Registering torrents allowed only from first post';
$lang['REG_NOT_ALLOWED_IN_THIS_FORUM'] = 'Could not register torrent in this forum';
$lang['ALREADY_REG'] = 'Torrent already registered';
$lang['NOT_TORRENT'] = 'This file is not torrent';
$lang['ONLY_1_TOR_PER_POST'] = 'You can register only one torrent in one post';
$lang['ONLY_1_TOR_PER_TOPIC'] = 'You can register only one torrent in one topic';
$lang['VIEWING_USER_BT_PROFILE'] = 'Viewing torrent-profile :: %s'; // %s is username
$lang['CUR_ACTIVE_DLS'] = 'Active torrents';

$lang['TD_TRAF'] = 'Today';
$lang['YS_TRAF'] = 'Yesterday';
$lang['TOTAL_TRAF'] = 'Total';

$lang['USER_RATIO'] = 'Ratio';
$lang['MAX_SPEED'] = 'Speed';
$lang['DOWNLOADED'] = 'Downloaded';
$lang['UPLOADED'] = 'Uploaded';
$lang['RELEASED'] = 'Released';
$lang['BONUS'] = 'On the rare';
$lang['IT_WILL_BE_DOWN'] = 'it will start to be considered after it will be downloaded';
$lang['SPMODE_FULL'] = 'Show peers in full details';

// Seed Bonus
$lang['MY_BONUS'] = 'My bonus (%s bonuses in stock)';
$lang['BONUS_SELECT'] = 'Select';
$lang['SEED_BONUS'] = 'Seed bonus';
$lang['EXCHANGE'] = 'Exchange';
$lang['EXCHANGE_BONUS'] = 'Exchange of seed bonuses';
$lang['BONUS_UPLOAD_DESC'] = '<b>%s to distribution</b> <br /> To exchange bonus points on %1$s traffic which will be added on to the sum of your distribution.';
$lang['BONUS_UPLOAD_PRICE'] = '<b class="%s">%s</b>';
$lang['PRICE'] = 'Price';
$lang['EXCHANGE_NOT'] = 'The exchange not available';
$lang['BONUS_SUCCES'] = 'To you it is successfully enlisted %s';
$lang['BONUS_NOT_SUCCES'] = '<span class="leech">You do not have bonuses available. More seeding!</span>';
$lang['BONUS_RETURN'] = 'Return to the seed bonus exchange';

$lang['TRACKER'] = 'Tracker';
$lang['OPEN_TOPICS'] = 'Open topics';
$lang['OPEN_IN_SAME_WINDOW'] = 'open in same window';
$lang['SHOW_TIME_TOPICS'] = 'show time of the creation topics';
$lang['SHOW_CURSOR'] = 'highlight the row under the cursor';

$lang['BT_LOW_RATIO_FOR_DL'] = "With ratio <b>%s</b> you can not download torrents";
$lang['BT_RATIO_WARNING_MSG'] = 'If your ratio falls below %s, you will not be able to download Torrents! <a href="%s"><b>More about the rating.</b></a>';

$lang['SEEDER_LAST_SEEN'] = 'Seeder not seen: <b>%s</b>';

$lang['NEED_TO_LOGIN_FIRST'] = 'You need to login first';
$lang['ONLY_FOR_MOD'] = 'This option only for moderators';
$lang['ONLY_FOR_ADMIN'] = 'This option only for admins';
$lang['ONLY_FOR_SUPER_ADMIN'] = 'This option only for super admins';

$lang['LOGS'] = 'Topic history';
$lang['FORUM_LOGS'] = 'History Forum';
$lang['AUTOCLEAN'] = 'Autoclean:';
$lang['DESIGNER'] = 'Designer';

$lang['LAST_IP'] = 'Last IP:';
$lang['REG_IP'] = 'Registration IP:';
$lang['OTHER_IP'] = 'Other IP:';
$lang['ALREADY_REG_IP'] = 'With your IP-address is already registered user %s. If you have not previously registered on our tracker, mail to <a href="mailto:%s">Administrator</a>';
$lang['HIDDEN'] = 'Hidden';

// from admin
$lang['NOT_ADMIN'] = 'You are not authorised to administer this board';

$lang['COOKIES_REQUIRED'] = 'Cookies must be enabled!';
$lang['SESSION_EXPIRED'] = 'Session expired';

// Sort memberlist per letter
$lang['SORT_PER_LETTER'] = 'Show only usernames starting with';
$lang['OTHERS'] = 'others';
$lang['ALL'] = 'all';

$lang['POST_LINK'] = 'Post link';
$lang['GOTO_QUOTED_POST'] = 'Go to the quoted post';
$lang['LAST_VISITED'] = 'Last Visited';
$lang['LAST_ACTIVITY'] = 'Last activity';
$lang['NEVER'] = 'Never';

//mpd
$lang['DELETE_POSTS'] = 'Delete selected posts';
$lang['DELETE_POSTS_SUCCESFULLY'] = 'The selected posts have been successfully removed';

//ts
$lang['TOPICS_ANNOUNCEMENT'] = 'Announcements';
$lang['TOPICS_STICKY'] = 'Stickies';
$lang['TOPICS_NORMAL'] = 'Topics';

//dpc
$lang['DOUBLE_POST_ERROR'] = 'You cannot make another post with the exact same text as your last.';

//upt
$lang['UPDATE_POST_TIME'] = 'Update post time';

$lang['TOPIC_SPLIT_NEW'] = 'New topic';
$lang['TOPIC_SPLIT_OLD'] = 'Old topic';
$lang['BOT_LEAVE_MSG_MOVED'] = 'Add bot-message about moving';
$lang['BOT_AFTER_SPLIT_TO_OLD'] = 'Add bot-message about split to <b>old topic</b>';
$lang['BOT_AFTER_SPLIT_TO_NEW'] = 'Add bot-message about split to <b>new topic</b>';
//qr
$lang['QUICK_REPLY'] = 'Quick Reply';
$lang['INS_NAME_TIP'] = 'Insert name or selected text.';
$lang['QUOTE_SELECTED'] = 'Quote selected';
$lang['QR_ATTACHSIG'] = 'Attach signature';
$lang['QR_NOTIFY'] = 'Notify on reply';
$lang['QR_DISABLE'] = 'Disable';
$lang['QR_USERNAME'] = 'Name';
$lang['NO_TEXT_SEL'] = 'Select a text anywhere on a page and try again';
$lang['QR_FONT_SEL'] = 'Font face';
$lang['QR_COLOR_SEL'] = 'Font color';
$lang['QR_SIZE_SEL'] = 'Font size';
$lang['COLOR_STEEL_BLUE'] = 'Steel Blue';
$lang['COLOR_GRAY'] = 'Gray';
$lang['COLOR_DARK_GREEN'] = 'Dark Green';

//txtb
$lang['ICQ_TXTB'] = '[ICQ]';
$lang['REPLY_WITH_QUOTE_TXTB'] = '[Quote]';
$lang['READ_PROFILE_TXTB'] = '[Profile]';
$lang['SEND_EMAIL_TXTB'] = '[E-mail]';
$lang['VISIT_WEBSITE_TXTB'] = '[www]';
$lang['EDIT_DELETE_POST_TXTB'] = '[Edit]';
$lang['SEARCH_USER_POSTS_TXTB'] = '[Search]';
$lang['VIEW_IP_TXTB'] = '[ip]';
$lang['DELETE_POST_TXTB'] = '[x]';
$lang['MODERATE_POST_TXTB'] = '[m]';
$lang['SEND_PM_TXTB'] = '[PM]';

$lang['DECLENSION']['REPLIES'] = array('reply', 'replies');
$lang['DECLENSION']['TIMES'] = array('time', 'times');

$lang['DELTA_TIME']['INTERVALS'] = array(
    'seconds' => array('second', 'seconds'),
    'minutes' => array('minute', 'minutes'),
    'hours' => array('hour', 'hours'),
    'mday' => array('day', 'days'),
    'mon' => array('month', 'months'),
    'year' => array('year', 'years'),
);
$lang['DELTA_TIME']['FORMAT'] = '%1$s %2$s'; // 5(%1) minutes(%2)

$lang['AUTH_TYPES'][AUTH_ALL] = $lang['AUTH_ANONYMOUS_USERS'];
$lang['AUTH_TYPES'][AUTH_REG] = $lang['AUTH_REGISTERED_USERS'];
$lang['AUTH_TYPES'][AUTH_ACL] = $lang['AUTH_USERS_GRANTED_ACCESS'];
$lang['AUTH_TYPES'][AUTH_MOD] = $lang['AUTH_MODERATORS'];
$lang['AUTH_TYPES'][AUTH_ADMIN] = $lang['AUTH_ADMINISTRATORS'];

$lang['NEW_USER_REG_DISABLED'] = 'Sorry, registration is disabled at this time';
$lang['ONLY_NEW_POSTS'] = 'only new posts';
$lang['ONLY_NEW_TOPICS'] = 'only new topics';

$lang['TORHELP_TITLE'] = 'Please help seeding these torrents!';
$lang['STATISTICS'] = 'Statistics';
$lang['STATISTIC'] = 'Statistic';
$lang['VALUE'] = 'Value';
$lang['INVERT_SELECT'] = 'Invert selection';
$lang['STATUS'] = 'Status';
$lang['LAST_CHANGED_BY'] = 'Last changed by';
$lang['CHANGES'] = 'Changes';
$lang['ACTION'] = 'Action';
$lang['REASON'] = 'Reason';
$lang['COMMENT'] = 'Comment';

// search
$lang['SEARCH_S'] = 'search...';
$lang['FORUM_S'] = 'on forum';
$lang['TRACKER_S'] = 'on tracker';
$lang['HASH_S'] = 'by info_hash';

// copyright
$lang['NOTICE'] = '!ATTENTION!';
$lang['COPY'] = 'The site does not give electronic versions of products, and is engaged only in a collecting and cataloguing of the references sent and published at a forum by our readers. If you are the legal owner of any submitted material and do not wish that the reference to it was in our catalogue, contact us and we shall immediately remove her. Files for an exchange on tracker are given by users of a site, and the administration does not bear the responsibility for their maintenance. The request to not fill in the files protected by copyrights, and also files of the illegal maintenance!';

// FILELIST
$lang['FILELIST'] = 'Filelist';
$lang['COLLAPSE'] = 'Collapse directory';
$lang['EXPAND'] = 'Expand';
$lang['SWITCH'] = 'Switch';
$lang['EMPTY_ATTACH_ID'] = 'Missing file identifier!';
$lang['TOR_NOT_FOUND'] = 'File is missing on the server!';
$lang['ERROR_BUILD'] = 'The content of this torrent file can not be viewed on the site (it was not possible to build a list of files)';
$lang['TORFILE_INVALID'] = 'Torrent file is corrupt';

// Profile
$lang['WEBSITE_ERROR'] = 'The "site" may contain only http://sitename';
$lang['ICQ_ERROR'] = 'The field of "ICQ" may contain only icq number';
$lang['INVALID_DATE'] = 'Error date ';
$lang['PROFILE_USER'] = 'Viewing profile';
$lang['GOOD_UPDATE'] = 'was successfully changed';
$lang['UCP_DOWNLOADS'] = 'Downloads';
$lang['HIDE_DOWNLOADS'] = 'Hide the current list of downloads on your profile';
$lang['BAN_USER'] = 'To prevent a user';
$lang['USER_NOT_ALLOWED'] = 'Users are not permitted';
$lang['HIDE_AVATARS'] = 'Show avatars';
$lang['SHOW_CAPTION'] = 'Show your signature';
$lang['DOWNLOAD_TORRENT'] = 'Download torrent';
$lang['SEND_PM'] = 'Send PM';
$lang['SEND_MESSAGE'] = 'Send message';
$lang['NEW_THREADS'] = 'New Threads';
$lang['PROFILE_NOT_FOUND'] = 'Profile not found';

$lang['USER_DELETE'] = 'Delete';
$lang['USER_DELETE_EXPLAIN'] = 'Delete this user';
$lang['USER_DELETE_ME'] = 'Sorry, your account is forbidden to remove!';
$lang['USER_DELETE_CSV'] = 'Sorry, this account is not allowed to delete!';
$lang['USER_DELETE_CONFIRM'] = 'Are you sure you want to delete this user?';
$lang['USER_DELETED'] = 'User was successfully deleted';
$lang['DELETE_USER_ALL_POSTS'] = 'Delete all user posts';
$lang['DELETE_USER_ALL_POSTS_CONFIRM'] = 'Are you sure you want to delete all messages and all topics started by this user?';
$lang['DELETE_USER_POSTS'] = 'Delete all messages, except for capital';
$lang['DELETE_USER_POSTS_ME'] = 'Are you sure you want to delete all my posts?';
$lang['DELETE_USER_POSTS_CONFIRM'] = 'Are you sure you want to delete all messages, except for capital?';
$lang['USER_DELETED_POSTS'] = 'Posts were successfully removed';

$lang['USER'] = 'User';
$lang['ROLE'] = 'Role:';
$lang['MEMBERSHIP_IN'] = 'Membership in';
$lang['PARTY'] = 'Party:';
$lang['CANDIDATE'] = 'Candidate:';
$lang['INDIVIDUAL'] = 'Has the individual rights';
$lang['GROUP_LIST_HIDDEN'] = 'You are not authorized to view hidden groups';

$lang['USER_ACTIVATE'] = 'Activate';
$lang['USER_DEACTIVATE'] = 'Deactivate';
$lang['DEACTIVATE_CONFIRM'] = 'Are you sure you want to enable this user?';
$lang['USER_ACTIVATE_ON'] = 'User has been successfully activated';
$lang['USER_DEACTIVATE_ME'] = 'You can not deactivate my account!';
$lang['ACTIVATE_CONFIRM'] = 'Are you sure you want to disable this user?';
$lang['USER_ACTIVATE_OFF'] = 'User has been successfully deactivated';

// Register
$lang['CHOOSE_A_NAME'] = 'You should choose a name';
$lang['CHOOSE_E_MAIL'] = 'You must specify the e-mail';
$lang['CHOOSE_PASS'] = 'Field for the password must not be empty!';
$lang['CHOOSE_PASS_ERR'] = 'Entered passwords do not match';
$lang['CHOOSE_PASS_ERR_MIN'] = 'Your password must be at least %d characters';
$lang['CHOOSE_PASS_ERR_MAX'] = 'Your password must be no longer than $d characters';
$lang['CHOOSE_PASS_OK'] = 'Passwords match';
$lang['CHOOSE_PASS_REG_OK'] = 'Passwords match, you can proceed with the registration';
$lang['CHOOSE_PASS_FAILED'] = 'To change the password, you must correctly specify the current password';
$lang['EMAILER_DISABLED'] = 'Sorry, this feature is temporarily not working';
$lang['TERMS_ON'] = 'I agree with these terms and conditions';
$lang['TERMS_OFF'] = 'I do not agree to these terms';
$lang['JAVASCRIPT_ON_REGISTER'] = 'To register, heads necessary to enable JavaScript';
$lang['REGISTERED_IN_TIME'] = "At the moment registration is closed<br /><br />You can register from 01:00 to 17:00 MSK (now " . date('H:i') . " MSK)<br /><br />We apologize for this inconvenience";
$lang['AUTOCOMPLETE'] = 'Password generate';
$lang['YOUR_NEW_PASSWORD'] = 'Your are new password:';
$lang['REGENERATE'] = 'Regenerate';

// Debug
$lang['EXECUTION_TIME'] = 'Execution time:';
$lang['SEC'] = 'sec';
$lang['ON'] = 'on';
$lang['OFF'] = 'off';
$lang['MEMORY'] = 'Mem: ';
$lang['QUERIES'] = 'queries';
$lang['LIMIT'] = 'Limit:';

// Attach Guest
$lang['DOWNLOAD_INFO'] = 'Download free and at maximum speed!';
$lang['HOW_TO_DOWNLOAD'] = 'How to Download?';
$lang['WHAT_IS_A_TORRENT'] = 'What is a torrent?';
$lang['RATINGS_AND_LIMITATIONS'] = 'Ratings and Limitations';

$lang['SCREENSHOTS_RULES'] = 'Read the rules laid out screenshots!';
$lang['SCREENSHOTS_RULES_TOPIC'] = 'Read the rules laid out the screenshots in this section!';
$lang['AJAX_EDIT_OPEN'] = 'Have you already opened one quick editing!';
$lang['GO_TO_PAGE'] = 'Go to page ...';
$lang['EDIT'] = 'Edit';
$lang['SAVE'] = 'Save';
$lang['NEW_WINDOW'] = 'in a new window';

// BB Code
$lang['ALIGN'] = 'Align:';
$lang['LEFT'] = 'To the left';
$lang['RIGHT'] = 'To the right';
$lang['CENTER'] = 'Centered';
$lang['JUSTIFY'] = 'Fit to width';
$lang['HOR_LINE'] = 'Horizontal line (Ctrl+8)';
$lang['NEW_LINE'] = 'New line';
$lang['BOLD'] = 'Bold text: [b]text[/b] (Ctrl+B)';
$lang['ITALIC'] = 'Italic text: [i]text[/i] (Ctrl+I)';
$lang['UNDERLINE'] = 'Underline text: [u]text[/u] (Ctrl+U)';
$lang['STRIKEOUT'] = 'Strikeout text: [s]text[/s] (Ctrl+S)';
$lang['QUOTE_TITLE'] = 'Quote text: [quote]text[/quote] (Ctrl+Q)';
$lang['IMG_TITLE'] = 'Insert image: [img]http://image_url[/img] (Ctrl+R)';
$lang['URL'] = 'Url';
$lang['URL_TITLE'] = 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url] (Ctrl+W)';
$lang['CODE_TITLE'] = 'Code display: [code]code[/code] (Ctrl+K)';
$lang['LIST'] = 'List';
$lang['LIST_TITLE'] = 'List: [list]text[/list] (Ctrl+l)';
$lang['LIST_ITEM'] = 'Ordered list: [list=]text[/list] (Ctrl+O)';
$lang['QUOTE_SEL'] = 'Quote selected';
$lang['JAVASCRIPT_ON'] = 'Heads necessary to send messages to enable JavaScript';

$lang['NEW'] = 'New';
$lang['NEWEST'] = 'Newest';
$lang['LATEST'] = 'Latest';
$lang['POST'] = 'Post';
$lang['OLD'] = 'Old';

// DL-List
$lang['DL_USER'] = 'Username';
$lang['DL_PERCENT'] = 'Complete percent';
$lang['DL_UL'] = 'UL';
$lang['DL_DL'] = 'DL';
$lang['DL_UL_SPEED'] = 'UL speed';
$lang['DL_DL_SPEED'] = 'DL speed';
$lang['DL_PORT'] = 'Port';
$lang['DL_FORMULA'] = 'Formula: Uploaded/TorrentSize';
$lang['DL_ULR'] = 'URL';
$lang['DL_STOPPED'] = 'stopped';
$lang['DL_UPD'] = 'upd: ';
$lang['DL_INFO'] = 'shows data <i><b>only for the current session</b></i>';

// Post PIN
$lang['POST_PIN'] = 'Pin first post';
$lang['POST_UNPIN'] = 'Unpin first post';
$lang['POST_PINNED'] = 'First post pinned';
$lang['POST_UNPINNED'] = 'First post unpinned';

// Management of my messages
$lang['GOTO_MY_MESSAGE'] = 'Close and return to the list "My Messages"';
$lang['DEL_MY_MESSAGE'] = 'Selected topics have been removed from the "My Messages"';
$lang['NO_TOPICS_MY_MESSAGE'] = 'No topics found in the list of your posts (maybe you have already removed them)';
$lang['EDIT_MY_MESSAGE_LIST'] = 'edit list';
$lang['SELECT_INVERT'] = 'select / invert';
$lang['RESTORE_ALL_POSTS'] = 'Restore all posts';
$lang['DEL_LIST_MY_MESSAGE'] = 'Delete the selected topic from the list';
$lang['DEL_LIST_MY_MESSAGE_INFO'] = 'After removal of up to update the <b>entire list</b> it can be shown already deleted threads';
$lang['DEL_LIST_INFO'] = 'To delete an order from the list, click on the icon to the left of the names of any section';

// Watched topics
$lang['WATCHED_TOPICS'] = 'Watched topics';
$lang['NO_WATCHED_TOPICS'] = 'You are not watching any topics';

// set_die_append_msg
$lang['INDEX_RETURN'] = 'Back to home page';
$lang['FORUM_RETURN'] = 'Back to forum';
$lang['TOPIC_RETURN'] = 'Back to the topic';
$lang['POST_RETURN'] = 'Go to post';
$lang['PROFILE_EDIT_RETURN'] = 'Return to editing';
$lang['PROFILE_RETURN'] = 'Go to the profile';

$lang['WARNING'] = 'Warning';
$lang['INDEXER'] = 'Reindex search';

$lang['FORUM_STYLE'] = 'Forum style';

$lang['LINKS_ARE_FORBIDDEN'] = 'Links are forbidden';

$lang['GENERAL'] = 'General Admin';
$lang['USERS'] = 'User Admin';
$lang['GROUPS'] = 'Group Admin';
$lang['FORUMS'] = 'Forum Admin';
$lang['MODS'] = 'Modifications';
$lang['TP'] = 'TorrentPier';

$lang['CONFIGURATION'] = 'Configuration';
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
$lang['CRON'] = 'Task Scheduler (cron)';
$lang['REBUILD_SEARCH_INDEX'] = 'Rebuild search index';
$lang['FORUM_CONFIG'] = 'Forum settings';
$lang['TRACKER_CONFIG'] = 'Tracker settings';
$lang['RELEASE_TEMPLATES'] = 'Release Templates';
$lang['ACTIONS_LOG'] = 'Report on action';

//Welcome page
$lang['IDX_BROWSER_NSP_FRAME'] = 'Sorry, your browser doesn\'t seem to support frames';
$lang['IDX_CLEAR_CACHE'] = 'Clear Cache:';
$lang['IDX_CLEAR_DATASTORE'] = 'Datastore';
$lang['IDX_CLEAR_TEMPLATES'] = 'Templates';
$lang['IDX_CLEAR_NEWNEWS'] = 'Net news';
$lang['IDX_UPDATE'] = 'Update:';
$lang['IDX_UPDATE_USER_LEVELS'] = 'User levels';
$lang['IDX_SYNCHRONIZE'] = 'Synchronize:';
$lang['IDX_SYNCHRONIZE_TOPICS'] = 'Topics';
$lang['IDX_SYNCHRONIZE_POSTCOUNT'] = 'User posts count';

// Index
$lang['MAIN_INDEX'] = 'Forum Index';
$lang['FORUM_STATS'] = 'Forum Statistics';
$lang['ADMIN_INDEX'] = 'Admin Index';
$lang['CREATE_PROFILE'] = 'Create profile';

$lang['TP_VERSION'] = 'TorrentPier version';
$lang['TP_RELEASE_DATE'] = 'Release date';
$lang['PHP_INFO'] = 'Information about PHP';

$lang['CLICK_RETURN_ADMIN_INDEX'] = 'Click %sHere%s to return to the Admin Index';

$lang['NUMBER_POSTS'] = 'Number of posts';
$lang['POSTS_PER_DAY'] = 'Posts per day';
$lang['NUMBER_TOPICS'] = 'Number of topics';
$lang['TOPICS_PER_DAY'] = 'Topics per day';
$lang['NUMBER_USERS'] = 'Number of users';
$lang['USERS_PER_DAY'] = 'Users per day';
$lang['BOARD_STARTED'] = 'Board started';
$lang['AVATAR_DIR_SIZE'] = 'Avatar directory size';
$lang['DATABASE_SIZE'] = 'Database size';
$lang['GZIP_COMPRESSION'] = 'Gzip compression';
$lang['NOT_AVAILABLE'] = 'Not available';

// Clear Cache
$lang['CLEAR_CACHE'] = 'Clear Cache';
$lang['TEMPLATES'] = 'Templates';

// Update
$lang['USER_LEVELS'] = 'User levels';
$lang['USER_LEVELS_UPDATED'] = 'User levels have been updated';

// Synchronize
$lang['SYNCHRONIZE'] = 'Synchronize';
$lang['TOPICS_DATA_SYNCHRONIZED'] = 'Topics data have been synchronized';
$lang['USER_POSTS_COUNT'] = 'User posts count';
$lang['USER_POSTS_COUNT_SYNCHRONIZED'] = 'User posts count has been synchronized';

// Online Userlist
$lang['SHOW_ONLINE_USERLIST'] = 'Show the list of online users';

// Auth pages
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

$lang['CONFLICT_WARNING'] = 'Authorisation Conflict Warning';
$lang['CONFLICT_ACCESS_USERAUTH'] = 'This user still has access rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having access rights. The groups granting rights (and the forums involved) are noted below.';
$lang['CONFLICT_MOD_USERAUTH'] = 'This user still has moderator rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having moderator rights. The groups granting rights (and the forums involved) are noted below.';

$lang['CONFLICT_ACCESS_GROUPAUTH'] = 'The following user (or users) still have access rights to this forum via their user permission settings. You may want to alter the user permissions to fully prevent them having access rights. The users granted rights (and the forums involved) are noted below.';
$lang['CONFLICT_MOD_GROUPAUTH'] = 'The following user (or users) still have moderator rights to this forum via their user permissions settings. You may want to alter the user permissions to fully prevent them having moderator rights. The users granted rights (and the forums involved) are noted below.';

$lang['PUBLIC'] = 'Public';
$lang['PRIVATE'] = 'Private';
$lang['REGISTERED'] = 'Registered';
$lang['ADMINISTRATORS'] = 'Administrators';

// These are displayed in the drop down boxes for advanced mode forum auth, try and keep them short!
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
$lang['AUTH_DOWNLOAD'] = 'Download Files';

$lang['SIMPLE_PERMISSION'] = 'Simple Permissions';

$lang['USER_LEVEL'] = 'User Level';
$lang['AUTH_USER'] = 'User';
$lang['AUTH_ADMIN'] = 'Administrator';
$lang['GROUP_MEMBERSHIPS'] = 'Usergroup memberships';
$lang['USERGROUP_MEMBERS'] = 'This group has the following members';

$lang['FORUM_AUTH_UPDATED'] = 'Forum permissions have been updated';
$lang['USER_AUTH_UPDATED'] = 'User permissions have been updated';
$lang['GROUP_AUTH_UPDATED'] = 'Group permissions have been updated';

$lang['AUTH_UPDATED'] = 'Permissions have been updated';
$lang['CLICK_RETURN_USERAUTH'] = 'Click %sHere%s to return to User Permissions';
$lang['CLICK_RETURN_GROUPAUTH'] = 'Click %sHere%s to return to Group Permissions';
$lang['CLICK_RETURN_FORUMAUTH'] = 'Click %sHere%s to return to Forum Permissions';

// Banning
$lang['BAN_CONTROL'] = 'Ban Control';
$lang['BAN_EXPLAIN'] = 'Here you can control the banning of users. You can achieve this by banning either or both of a specific user or an individual or range of IP addresses. These methods prevent a user from even reaching the index page of your board. To prevent a user from registering under a different username you can also specify a banned email address. Please note that banning an email address alone will not prevent that user from being able to log on or post to your board. You should use one of the first two methods to achieve this.';
$lang['BAN_EXPLAIN_WARN'] = 'Please note that entering a range of IP addresses results in all the addresses between the start and end being added to the banlist. Attempts will be made to minimise the number of addresses added to the database by introducing wildcards automatically where appropriate. If you really must enter a range, try to keep it small or better yet state specific addresses.';

$lang['SELECT_IP'] = 'Select an IP address';
$lang['SELECT_EMAIL'] = 'Select an Email address';

$lang['BAN_USERNAME'] = 'Ban one or more specific users';
$lang['BAN_USERNAME_EXPLAIN'] = 'You can ban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['BAN_IP'] = 'Ban one or more IP addresses';
$lang['IP_HOSTNAME'] = 'IP addresses';
$lang['BAN_IP_EXPLAIN'] = 'To specify several different IP addresses separate them with commas.';

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

// Configuration
$lang['GENERAL_CONFIG'] = 'General Configuration';
$lang['CONFIG_EXPLAIN'] = 'The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.';

$lang['CONFIG_MODS'] = 'Configuration modifications';
$lang['MODS_EXPLAIN'] = 'This form allows you to adjust the modifications';

$lang['CLICK_RETURN_CONFIG'] = '%sClick Here to return to General Configuration%s';
$lang['CLICK_RETURN_CONFIG_MODS'] = '%sBack to the settings modifications%s';

$lang['GENERAL_SETTINGS'] = 'General Board Settings';
$lang['SITE_NAME'] = 'Site name';
$lang['SITE_DESC'] = 'Site description';
$lang['FORUMS_DISABLE'] = 'Disable board';
$lang['BOARD_DISABLE_EXPLAIN'] = 'This will make the board unavailable to users. Administrators are able to access the Administration Panel while the board is disabled.';
$lang['ACCT_ACTIVATION'] = 'Enable account activation';
$lang['ACC_NONE'] = 'None'; // These three entries are the type of activation
$lang['ACC_USER'] = 'User';
$lang['ACC_ADMIN'] = 'Admin';

$lang['ABILITIES_SETTINGS'] = 'User and Forum Basic Settings';
$lang['MAX_POLL_OPTIONS'] = 'Max number of poll options';
$lang['FLOOD_INTERVAL'] = 'Flood Interval';
$lang['FLOOD_INTERVAL_EXPLAIN'] = 'Number of seconds a user must wait between posts';
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
$lang['SMILIES_PATH_EXPLAIN'] = 'Path under your TorrentPier root dir, e.g. styles/images/smiles';
$lang['ALLOW_SIG'] = 'Allow Signatures';
$lang['MAX_SIG_LENGTH'] = 'Maximum signature length';
$lang['MAX_SIG_LENGTH_EXPLAIN'] = 'Maximum number of characters in user signatures';
$lang['ALLOW_NAME_CHANGE'] = 'Allow Username changes';

$lang['EMAIL_SETTINGS'] = 'Email Settings';

// Visual Confirmation
$lang['VISUAL_CONFIRM'] = 'Enable Visual Confirmation';
$lang['VISUAL_CONFIRM_EXPLAIN'] = 'Requires users enter a code defined by an image when registering.';

// Autologin Keys
$lang['ALLOW_AUTOLOGIN'] = 'Allow automatic logins';
$lang['ALLOW_AUTOLOGIN_EXPLAIN'] = 'Determines whether users are allowed to select to be automatically logged in when visiting the forum';
$lang['AUTOLOGIN_TIME'] = 'Automatic login key expiry';
$lang['AUTOLOGIN_TIME_EXPLAIN'] = 'How long an autologin key is valid for in days if the user does not visit the board. Set to zero to disable expiry.';

// Forum Management
$lang['FORUM_ADMIN_MAIN'] = 'Forum Administration';
$lang['FORUM_ADMIN_EXPLAIN'] = 'From this panel you can add, delete, edit, re-order and re-synchronise categories and forums';
$lang['EDIT_FORUM'] = 'Edit forum';
$lang['CREATE_FORUM'] = 'Create new forum';
$lang['CREATE_CATEGORY'] = 'Create new category';
$lang['REMOVE'] = 'Remove';
$lang['UPDATE_ORDER'] = 'Update Order';
$lang['CONFIG_UPDATED'] = 'Forum Configuration Has Been Updated Successfully';
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
$lang['DELETE_ALL_TOPICS'] = 'Delete all topics, including announcements and sticky';
$lang['NOWHERE_TO_MOVE'] = 'Nowhere to move to';

$lang['EDIT_CATEGORY'] = 'Edit Category';
$lang['EDIT_CATEGORY_EXPLAIN'] = 'Use this form to modify a category\'s name.';

$lang['FORUMS_UPDATED'] = 'Forum and Category information updated successfully';

$lang['MUST_DELETE_FORUMS'] = 'You need to delete all forums before you can delete this category';

$lang['CLICK_RETURN_FORUMADMIN'] = 'Click %sHere%s to return to Forum Administration';

$lang['SHOW_ALL_FORUMS_ON_ONE_PAGE'] = 'Show all forums on one page';

// Smiley Management
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

$lang['SMILEY_ADD_SUCCESS'] = 'The Smiley has been added successfully';
$lang['SMILEY_EDIT_SUCCESS'] = 'The Smiley has been updated successfully';
$lang['SMILEY_IMPORT_SUCCESS'] = 'The Smiley Pack has been imported successfully!';
$lang['SMILEY_DEL_SUCCESS'] = 'The Smiley has been removed successfully';
$lang['CLICK_RETURN_SMILEADMIN'] = 'Click %sHere%s to return to Smiley Administration';

// User Management
$lang['USER_ADMIN'] = 'User Administration';
$lang['USER_ADMIN_EXPLAIN'] = 'Here you can change your users\' information and certain options. To modify the users\' permissions, please use the user and group permissions system.';

$lang['LOOK_UP_USER'] = 'Look up user';

$lang['ADMIN_USER_FAIL'] = 'Could not update the user\'s profile.';
$lang['ADMIN_USER_UPDATED'] = 'The user\'s profile has been updated successfully.';
$lang['CLICK_RETURN_USERADMIN'] = 'Click %sHere%s to return to User Administration';

$lang['USER_ALLOWPM'] = 'Can send Private Messages';
$lang['USER_ALLOWAVATAR'] = 'Can display avatar';

$lang['ADMIN_AVATAR_EXPLAIN'] = 'Here you can see and delete the user\'s current avatar.';

$lang['USER_SPECIAL'] = 'Special admin-only fields';
$lang['USER_SPECIAL_EXPLAIN'] = 'These fields are not able to be modified by the users. Here you can set their status and other options that are not given to users.';

// Group Management
$lang['GROUP_ADMINISTRATION'] = 'Group Administration';
$lang['GROUP_ADMIN_EXPLAIN'] = 'From this panel you can administer all your usergroups. You can delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description';
$lang['ERROR_UPDATING_GROUPS'] = 'There was an error while updating the groups';
$lang['UPDATED_GROUP'] = 'The group has been updated successfully';
$lang['ADDED_NEW_GROUP'] = 'The new group has been created successfully';
$lang['DELETED_GROUP'] = 'The group has been deleted successfully';
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
$lang['DELETE_OLD_GROUP_MOD_EXPL'] = 'If you\'re changing the group moderator, check this box to remove the old moderator from the group. Otherwise, do not check it, and the user will become a regular member of the group.';
$lang['CLICK_RETURN_GROUPSADMIN'] = 'Click %sHere%s to return to Group Administration.';
$lang['SELECT_GROUP'] = 'Select a group';
$lang['LOOK_UP_GROUP'] = 'Look up group';

// Prune Administration
$lang['FORUM_PRUNE'] = 'Forum Prune';
$lang['FORUM_PRUNE_EXPLAIN'] = 'This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove <b>sticky</b> topics and <b>announcements</b>. You will need to remove those topics manually.';
$lang['DO_PRUNE'] = 'Do Prune';
$lang['ALL_FORUMS'] = 'All Forums';
$lang['PRUNE_TOPICS_NOT_POSTED'] = 'Prune topics with no replies in this many days';
$lang['TOPICS_PRUNED'] = 'Topics pruned';
$lang['POSTS_PRUNED'] = 'Posts pruned';
$lang['PRUNE_SUCCESS'] = 'Forum has been pruned successfully';
$lang['NOT_DAYS'] = 'Prune days not selected';

// Word censor
$lang['WORDS_TITLE'] = 'Word Censoring';
$lang['WORDS_EXPLAIN'] = 'From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field. For example, *test* will match detestable, test* would match testing, *test would match detest.';
$lang['WORD'] = 'Word';
$lang['EDIT_WORD_CENSOR'] = 'Edit word censor';
$lang['REPLACEMENT'] = 'Replacement';
$lang['ADD_NEW_WORD'] = 'Add new word';
$lang['UPDATE_WORD'] = 'Update word censor';

$lang['MUST_ENTER_WORD'] = 'You must enter a word and its replacement';
$lang['NO_WORD_SELECTED'] = 'No word selected for editing';

$lang['WORD_UPDATED'] = 'The selected word censor has been updated successfully';
$lang['WORD_ADDED'] = 'The word censor has been added successfully';
$lang['WORD_REMOVED'] = 'The selected word censor has been removed successfully ';

$lang['CLICK_RETURN_WORDADMIN'] = 'Click %sHere%s to return to Word Censor Administration';

// Mass Email
$lang['MASS_EMAIL_EXPLAIN'] = 'Here you can email a message to either all of your users or all users of a specific group. To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for a mass emailing to take a long time and you will be notified when the script has completed';
$lang['COMPOSE'] = 'Compose';

$lang['RECIPIENTS'] = 'Recipients';
$lang['ALL_USERS'] = 'All Users';

$lang['EMAIL_SUCCESSFULL'] = 'Your message has been sent';
$lang['CLICK_RETURN_MASSEMAIL'] = 'Click %sHere%s to return to the Mass Email form';

// Ranks admin
$lang['RANKS_TITLE'] = 'Rank Administration';
$lang['RANKS_EXPLAIN'] = 'Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility';

$lang['ADD_NEW_RANK'] = 'Add new rank';
$lang['RANK_TITLE'] = 'Rank Title';
$lang['STYLE_COLOR'] = 'Style rank';
$lang['STYLE_COLOR_FAQ'] = 'Specify class for painting at the title of the desired color. For example <i class="bold">colorAdmin<i>';
$lang['RANK_SPECIAL'] = 'Set as Special Rank';
$lang['RANK_MINIMUM'] = 'Minimum Posts';
$lang['RANK_MAXIMUM'] = 'Maximum Posts';
$lang['RANK_IMAGE'] = 'Rank Image';
$lang['RANK_IMAGE_EXPLAIN'] = 'Use this to define a small image associated with the rank';

$lang['MUST_SELECT_RANK'] = 'You must select a rank';
$lang['NO_ASSIGNED_RANK'] = 'No special rank assigned';

$lang['RANK_UPDATED'] = 'The rank has been updated successfully';
$lang['RANK_ADDED'] = 'The rank has been added successfully';
$lang['RANK_REMOVED'] = 'The rank has been deleted successfully';
$lang['NO_UPDATE_RANKS'] = 'The rank has been deleted successfully. However, user accounts using this rank were not updated. You will need to manually reset the rank on these accounts';

$lang['CLICK_RETURN_RANKADMIN'] = 'Click %sHere%s to return to Rank Administration';

// Disallow Username Admin
$lang['DISALLOW_CONTROL'] = 'Username Disallow Control';
$lang['DISALLOW_EXPLAIN'] = 'Here you can control usernames which will not be allowed to be used. Disallowed usernames are allowed to contain a wildcard character of *. Please note that you will not be allowed to specify any username that has already been registered. You must first delete that name then disallow it.';

$lang['DELETE_DISALLOW'] = 'Delete';
$lang['DELETE_DISALLOW_TITLE'] = 'Remove a Disallowed Username';
$lang['DELETE_DISALLOW_EXPLAIN'] = 'You can remove a disallowed username by selecting the username from this list and clicking submit';

$lang['ADD_DISALLOW'] = 'Add';
$lang['ADD_DISALLOW_TITLE'] = 'Add a disallowed username';
$lang['ADD_DISALLOW_EXPLAIN'] = 'You can disallow a username using the wildcard character * to match any character';

$lang['NO_DISALLOWED'] = 'No Disallowed Usernames';

$lang['DISALLOWED_DELETED'] = 'The disallowed username has been removed successfully';
$lang['DISALLOW_SUCCESSFUL'] = 'The disallowed username has been added successfully';
$lang['DISALLOWED_ALREADY'] = 'The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present.';

$lang['CLICK_RETURN_DISALLOWADMIN'] = 'Click %sHere%s to return to Disallow Username Administration';

// Version Check
$lang['VERSION_INFORMATION'] = 'Version Information';

// Login attempts configuration
$lang['MAX_LOGIN_ATTEMPTS'] = 'Allowed login attempts';
$lang['MAX_LOGIN_ATTEMPTS_EXPLAIN'] = 'The number of allowed board login attempts.';
$lang['LOGIN_RESET_TIME'] = 'Login lock time';
$lang['LOGIN_RESET_TIME_EXPLAIN'] = 'Time in minutes the user have to wait until he is allowed to login again after exceeding the number of allowed login attempts.';

// Permissions List
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

// Misc
$lang['SF_SHOW_ON_INDEX'] = 'Show on main page';
$lang['SF_PARENT_FORUM'] = 'Parent forum';
$lang['SF_NO_PARENT'] = 'No parent forum';
$lang['TEMPLATE'] = 'Template';
$lang['SYNC'] = 'Sync';

// Mods
$lang['MAX_NEWS_TITLE'] = 'Max. length of the news';
$lang['NEWS_COUNT'] = 'How many news show';
$lang['NEWS_FORUM_ID'] = 'From what forums to display <br /> <h6>Of the several forums raises, separated by commas. Example 1,2,3</h6>';
$lang['NOAVATAR'] = 'No avatar';
$lang['TRACKER_STATS'] = 'Statistics on the tracker';
$lang['WHOIS_INFO'] = 'Information about IP address';
$lang['SHOW_MOD_HOME_PAGE'] = 'Show on moderators the index.php';
$lang['PREMOD_HELP'] = '<h4><span class="tor-icon tor-dup">&#8719;</span> Pre-moderation</h4> <h6>If you do not have distributions to the status of v, #, or T in this section, including subsections, the distribution will automatically receive this status</h6>';
$lang['TOR_COMMENT'] = '<h4>Commentary on the status of distribution</h4> <h6>Comment successfully allows you to specify releasers mistakes. When nedooformlennyh statuses releasers available form of the response of the correction release</h6>';
$lang['SEED_BONUS_ADD'] = '<h4>Adding seed bonus </h4> <h6> Number of distributions are handed out by the user and the size of bonuses for them (charging times an hour) </h6>';
$lang['SEED_BONUS_RELEASE'] = 'to N-number of releases';
$lang['SEED_BONUS_POINTS'] = 'bonuses in an hour';
$lang['SEED_BONUS_TOR_SIZE'] = '<h4>Minimum distribution for which will be awarded bonuses </h4> <h6> If you want to calculate bonuses for all distribution, leave blank. </h6>';
$lang['SEED_BONUS_USER_REGDATA'] = '<h4>Minimum length of user tracker, after which will be awarded bonuses </h4> <h6> If you want to accrue bonuses to all users, leave blank. </h6>';
$lang['SEED_BONUS_WARNING'] = 'ATTENTION! Seed Bonuses should be in ascending order';
$lang['SEED_BONUS_EXCHANGE'] = 'Configuring Exchange Sid Bonuses';
$lang['SEED_BONUS_ROPORTION'] = 'Proportion addition for an exchange of bonuses on GB';

// Modules, this replaces the keys used
$lang['CONTROL_PANEL'] = 'Control Panel';
$lang['SHADOW_ATTACHMENTS'] = 'Shadow Attachments';
$lang['FORBIDDEN_EXTENSIONS'] = 'Forbidden Extensions';
$lang['EXTENSION_CONTROL'] = 'Extension Control';
$lang['EXTENSION_GROUP_MANAGE'] = 'Extension Groups Control';
$lang['SPECIAL_CATEGORIES'] = 'Special Categories';
$lang['SYNC_ATTACHMENTS'] = 'Synchronize Attachments';
$lang['QUOTA_LIMITS'] = 'Quota Limits';

// Attachments -> Management
$lang['ATTACH_SETTINGS'] = 'Attachment Settings';
$lang['MANAGE_ATTACHMENTS_EXPLAIN'] = 'Here you can configure the Main Settings for the Attachment Mod. If you press the Test Settings Button, the Attachment Mod does a few System Tests to be sure that the Mod will work properly. If you have problems with uploading Files, please run this Test, to get a detailed error-message.';
$lang['ATTACH_FILESIZE_SETTINGS'] = 'Attachment Filesize Settings';
$lang['ATTACH_NUMBER_SETTINGS'] = 'Attachment Number Settings';
$lang['ATTACH_OPTIONS_SETTINGS'] = 'Attachment Options';

$lang['UPLOAD_DIRECTORY'] = 'Upload Directory';
$lang['UPLOAD_DIRECTORY_EXPLAIN'] = 'Enter the relative path from your TorrentPier installation to the Attachments upload directory. For example, enter \'files\' if your TorrentPier Installation is located at http://www.yourdomain.com/torrentpier and the Attachment Upload Directory is located at http://www.yourdomain.com/torrentpier/files.';
$lang['ATTACH_IMG_PATH'] = 'Attachment Posting Icon';
$lang['ATTACH_IMG_PATH_EXPLAIN'] = 'This Image is displayed next to Attachment Links in individual Postings. Leave this field empty if you don\'t want an icon to be displayed. This Setting will be overwritten by the Settings in Extension Groups Management.';
$lang['ATTACH_TOPIC_ICON'] = 'Attachment Topic Icon';
$lang['ATTACH_TOPIC_ICON_EXPLAIN'] = 'This Image is displayed before topics with Attachments. Leave this field empty if you don\'t want an icon to be displayed.';
$lang['ATTACH_DISPLAY_ORDER'] = 'Attachment Display Order';
$lang['ATTACH_DISPLAY_ORDER_EXPLAIN'] = 'Here you can choose whether to display the Attachments in Posts/PMs in Descending Filetime Order (Newest Attachment First) or Ascending Filetime Order (Oldest Attachment First).';
$lang['SHOW_APCP'] = 'Use the new control panel applications';
$lang['SHOW_APCP_EXPLAIN'] = 'Choose whether you want to use a separate control panel applications (yes), or the old method with two boxes for applications and editing applications (none) in the message box. It is difficult to explain how it looks, so try for yourself.';

$lang['MAX_FILESIZE_ATTACH'] = 'Filesize';
$lang['MAX_FILESIZE_ATTACH_EXPLAIN'] = 'Maximum filesize for Attachments. A value of 0 means \'unlimited\'. This Setting is restricted by your Server Configuration. For example, if your php Configuration only allows a maximum of 2 MB uploads, this cannot be overwritten by the Mod.';
$lang['ATTACH_QUOTA'] = 'Attachment Quota';
$lang['ATTACH_QUOTA_EXPLAIN'] = 'Maximum Disk Space ALL Attachments can hold on your Webspace. A value of 0 means \'unlimited\'.';
$lang['MAX_FILESIZE_PM'] = 'Maximum Filesize in Private Messages Folder';
$lang['MAX_FILESIZE_PM_EXPLAIN'] = 'Maximum Disk Space Attachments can use up in each User\'s Private Message box. A value of 0 means \'unlimited\'.';
$lang['DEFAULT_QUOTA_LIMIT'] = 'Default Quota Limit';
$lang['DEFAULT_QUOTA_LIMIT_EXPLAIN'] = 'Here you are able to select the Default Quota Limit automatically assigned to newly registered Users and Users without an defined Quota Limit. The Option \'No Quota Limit\' is for not using any Attachment Quotas, instead using the default Settings you have defined within this Management Panel.';

$lang['MAX_ATTACHMENTS'] = 'Maximum Number of Attachments';
$lang['MAX_ATTACHMENTS_EXPLAIN'] = 'The maximum number of attachments allowed in one post.';
$lang['MAX_ATTACHMENTS_PM'] = 'Maximum number of Attachments in one Private Message';
$lang['MAX_ATTACHMENTS_PM_EXPLAIN'] = 'Define the maximum number of attachments the user is allowed to include in a private message.';

$lang['DISABLE_MOD'] = 'Disable Attachment Mod';
$lang['DISABLE_MOD_EXPLAIN'] = 'This option is mainly for testing new templates or themes, it disables all Attachment Functions except the Admin Panel.';
$lang['PM_ATTACHMENTS'] = 'Allow Attachments in Private Messages';
$lang['PM_ATTACHMENTS_EXPLAIN'] = 'Allow/Disallow attaching files to Private Messages.';
$lang['ATTACHMENT_TOPIC_REVIEW'] = 'Show applications in the review of communications topics when writing an answer?';
$lang['ATTACHMENT_TOPIC_REVIEW_EXPLAIN'] = 'If you put a "yes", all applications will be displayed in the review of communications topics.';

// Attachments -> Shadow Attachments
$lang['SHADOW_ATTACHMENTS_EXPLAIN'] = 'Here you can delete attachment data from postings when the files are missing from your filesystem, and delete files that are no longer attached to any postings. You can download or view a file if you click on it; if no link is present, the file does not exist.';
$lang['SHADOW_ATTACHMENTS_FILE_EXPLAIN'] = 'Delete all attachments files that exist on your filesystem and are not assigned to an existing post.';
$lang['SHADOW_ATTACHMENTS_ROW_EXPLAIN'] = 'Delete all posting attachment data for files that don\'t exist on your filesystem.';
$lang['EMPTY_FILE_ENTRY'] = 'Empty File Entry';

// Attachments -> Sync
$lang['SYNC_THUMBNAIL_RESETTED'] = 'Thumbnail resetted for Attachment: %s'; // replace %s with physical Filename
$lang['ATTACH_SYNC_FINISHED'] = 'Attachment Syncronization Finished.';
$lang['SYNC_TOPICS'] = 'Sync Topics';
$lang['SYNC_POSTS'] = 'Sync Posts';
$lang['SYNC_THUMBNAILS'] = 'Sync Thumbnails';

// Extensions -> Extension Control
$lang['MANAGE_EXTENSIONS'] = 'Manage Extensions';
$lang['MANAGE_EXTENSIONS_EXPLAIN'] = 'Here you can manage your File Extensions. If you want to allow/disallow a Extension to be uploaded, please use the Extension Groups Management.';
$lang['EXPLANATION'] = 'Explanation';
$lang['EXTENSION_GROUP'] = 'Extension Group';
$lang['INVALID_EXTENSION'] = 'Invalid Extension';
$lang['EXTENSION_EXIST'] = 'The Extension %s already exist'; // replace %s with the Extension
$lang['UNABLE_ADD_FORBIDDEN_EXTENSION'] = 'The Extension %s is forbidden, you are not able to add it to the allowed Extensions'; // replace %s with Extension

// Extensions -> Extension Groups Management
$lang['MANAGE_EXTENSION_GROUPS'] = 'Manage Extension Groups';
$lang['MANAGE_EXTENSION_GROUPS_EXPLAIN'] = 'Here you can add, delete and modify your Extension Groups, you can disable Extension Groups, assign a special Category to them, change the download mechanism and you can define a Upload Icon which will be displayed in front of an Attachment belonging to the Group.';
$lang['SPECIAL_CATEGORY'] = 'Special Category';
$lang['CATEGORY_IMAGES'] = 'Images';
$lang['ALLOWED'] = 'Allowed';
$lang['ALLOWED_FORUMS'] = 'Allowed Forums';
$lang['EXT_GROUP_PERMISSIONS'] = 'Group Permissions';
$lang['DOWNLOAD_MODE'] = 'Download Mode';
$lang['UPLOAD_ICON'] = 'Upload Icon';
$lang['MAX_GROUPS_FILESIZE'] = 'Maximum Filesize';
$lang['EXTENSION_GROUP_EXIST'] = 'The Extension Group %s already exist'; // replace %s with the group name

// Extensions -> Special Categories
$lang['MANAGE_CATEGORIES'] = 'Manage Special Categories';
$lang['MANAGE_CATEGORIES_EXPLAIN'] = 'Here you can configure the Special Categories. You can set up Special Parameters and Conditions for the Special Categorys assigned to an Extension Group.';
$lang['SETTINGS_CAT_IMAGES'] = 'Settings for Special Category: Images';
$lang['SETTINGS_CAT_FLASH'] = 'Settings for Special Category: Flash Files';
$lang['DISPLAY_INLINED'] = 'Display Images Inlined';
$lang['DISPLAY_INLINED_EXPLAIN'] = 'Choose whether to display images directly within the post (yes) or to display images as a link ?';
$lang['MAX_IMAGE_SIZE'] = 'Maximum Image Dimensions';
$lang['MAX_IMAGE_SIZE_EXPLAIN'] = 'Here you can define the maximum allowed Image Dimension to be attached (Width x Height in pixels).<br />If it is set to 0x0, this feature is disabled. With some Images this Feature will not work due to limitations in PHP.';
$lang['IMAGE_LINK_SIZE'] = 'Image Link Dimensions';
$lang['IMAGE_LINK_SIZE_EXPLAIN'] = 'If this defined Dimension of an Image is reached, the Image will be displayed as a Link, rather than displaying it inlined,<br />if Inline View is enabled (Width x Height in pixels).<br />If it is set to 0x0, this feature is disabled. With some Images this Feature will not work due to limitations in PHP.';
$lang['ASSIGNED_GROUP'] = 'Assigned Group';

$lang['IMAGE_CREATE_THUMBNAIL'] = 'Create Thumbnail';
$lang['IMAGE_CREATE_THUMBNAIL_EXPLAIN'] = 'Always create a Thumbnail. This feature overrides nearly all Settings within this Special Category, except of the Maximum Image Dimensions. With this Feature a Thumbnail will be displayed within the post, the User can click it to open the real Image.<br />Please Note that this feature requires Imagick to be installed, if it\'s not installed or if Safe-Mode is enabled the GD-Extension of PHP will be used. If the Image-Type is not supported by PHP, this Feature will be not used.';
$lang['IMAGE_MIN_THUMB_FILESIZE'] = 'Minimum Thumbnail Filesize';
$lang['IMAGE_MIN_THUMB_FILESIZE_EXPLAIN'] = 'If a Image is smaller than this defined Filesize, no Thumbnail will be created, because it\'s small enough.';
$lang['IMAGE_IMAGICK_PATH'] = 'Imagick Program (Complete Path)';
$lang['IMAGE_IMAGICK_PATH_EXPLAIN'] = 'Enter the Path to the convert program of imagick, normally /usr/bin/convert (on windows: c:/imagemagick/convert.exe).';
$lang['IMAGE_SEARCH_IMAGICK'] = 'Search Imagick';

$lang['USE_GD2'] = 'Make use of GD2 Extension';
$lang['USE_GD2_EXPLAIN'] = 'PHP is able to be compiled with the GD1 or GD2 Extension for image manipulating. To correctly create Thumbnails without imagemagick the Attachment Mod uses two different methods, based on your selection here. If your thumbnails are in a bad quality or screwed up, try to change this setting.';
$lang['ATTACHMENT_VERSION'] = 'Attachment Mod Version %s'; // %s is the version number

// Extensions -> Forbidden Extensions
$lang['MANAGE_FORBIDDEN_EXTENSIONS'] = 'Manage Forbidden Extensions';
$lang['MANAGE_FORBIDDEN_EXTENSIONS_EXPLAIN'] = 'Here you can add or delete the forbidden extensions. The Extensions php, php3 and php4 are forbidden by default for security reasons, you can not delete them.';
$lang['FORBIDDEN_EXTENSION_EXIST'] = 'The forbidden Extension %s already exist'; // replace %s with the extension
$lang['EXTENSION_EXIST_FORBIDDEN'] = 'The Extension %s is defined in your allowed Extensions, please delete it their before you add it here.'; // replace %s with the extension

// Extensions -> Extension Groups Control -> Group Permissions
$lang['GROUP_PERMISSIONS_TITLE_ADMIN'] = 'Extension Group Permissions -> \'%s\''; // Replace %s with the Groups Name
$lang['GROUP_PERMISSIONS_EXPLAIN'] = 'Here you are able to restrict the selected Extension Group to Forums of your choice (defined in the Allowed Forums Box). The Default is to allow Extension Groups to all Forums the User is able to Attach Files into (the normal way the Attachment Mod did it since the beginning). Just add those Forums you want the Extension Group (the Extensions within this Group) to be allowed there, the default ALL FORUMS will disappear when you add Forums to the List. You are able to re-add ALL FORUMS at any given Time. If you add a Forum to your Board and the Permission is set to ALL FORUMS nothing will change. But if you have changed and restricted the access to certain Forums, you have to check back here to add your newly created Forum. It is easy to do this automatically, but this will force you to edit a bunch of Files, therefore i have chosen the way it is now. Please keep in mind, that all of your Forums will be listed here.';
$lang['NOTE_ADMIN_EMPTY_GROUP_PERMISSIONS'] = 'NOTE:<br />Within the below listed Forums your Users are normally allowed to attach files, but since no Extension Group is allowed to be attached there, your Users are unable to attach anything. If they try, they will receive Error Messages. Maybe you want to set the Permission \'Post Files\' to ADMIN at these Forums.<br /><br />';
$lang['ADD_FORUMS'] = 'Add Forums';
$lang['ADD_SELECTED'] = 'Add Selected';
$lang['PERM_ALL_FORUMS'] = 'ALL FORUMS';

// Attachments -> Quota Limits
$lang['MANAGE_QUOTAS'] = 'Manage Attachment Quota Limits';
$lang['MANAGE_QUOTAS_EXPLAIN'] = 'Here you are able to add/delete/change Quota Limits. You are able to assign these Quota Limits to Users and Groups later. To assign a Quota Limit to a User, you have to go to Users->Management, select the User and you will see the Options at the bottom. To assign a Quota Limit to a Group, go to Groups->Management, select the Group to edit it, and you will see the Configuration Settings. If you want to see, which Users and Groups are assigned to a specific Quota Limit, click on \'View\' at the left of the Quota Description.';
$lang['ASSIGNED_USERS'] = 'Assigned Users';
$lang['ASSIGNED_GROUPS'] = 'Assigned Groups';
$lang['QUOTA_LIMIT_EXIST'] = 'The Quota Limit %s exist already.'; // Replace %s with the Quota Description

// Attachments -> Control Panel
$lang['CONTROL_PANEL_TITLE'] = 'File Attachment Control Panel';
$lang['CONTROL_PANEL_EXPLAIN'] = 'Here you can view and manage all attachments based on Users, Attachments, Views etc...';
$lang['FILECOMMENT'] = 'File Comment';

// Control Panel -> Search
$lang['SEARCH_WILDCARD_EXPLAIN'] = 'Use * as a wildcard for partial matches';
$lang['SIZE_SMALLER_THAN'] = 'Attachment size smaller than (bytes)';
$lang['SIZE_GREATER_THAN'] = 'Attachment size greater than (bytes)';
$lang['COUNT_SMALLER_THAN'] = 'Download count is smaller than';
$lang['COUNT_GREATER_THAN'] = 'Download count is greater than';
$lang['MORE_DAYS_OLD'] = 'More than this many days old';
$lang['NO_ATTACH_SEARCH_MATCH'] = 'No Attachments met your search criteria';

// Control Panel -> Statistics
$lang['NUMBER_OF_ATTACHMENTS'] = 'Number of Attachments';
$lang['TOTAL_FILESIZE'] = 'Total Filesize';
$lang['NUMBER_POSTS_ATTACH'] = 'Number of Posts with Attachments';
$lang['NUMBER_TOPICS_ATTACH'] = 'Number of Topics with Attachments';
$lang['NUMBER_USERS_ATTACH'] = 'Independent Users Posted Attachments';
$lang['NUMBER_PMS_ATTACH'] = 'Total Number of Attachments in Private Messages';
$lang['ATTACHMENTS_PER_DAY'] = 'Attachments per day';

// Control Panel -> Attachments
$lang['STATISTICS_FOR_USER'] = 'Attachment Statistics for %s'; // replace %s with username
$lang['DOWNLOADS'] = 'Downloads';
$lang['POST_TIME'] = 'Post Time';
$lang['POSTED_IN_TOPIC'] = 'Posted in Topic';
$lang['SUBMIT_CHANGES'] = 'Submit Changes';

// Sort Types
$lang['SORT_ATTACHMENTS'] = 'Attachments';
$lang['SORT_SIZE'] = 'Size';
$lang['SORT_FILENAME'] = 'Filename';
$lang['SORT_COMMENT'] = 'Comment';
$lang['SORT_EXTENSION'] = 'Extension';
$lang['SORT_DOWNLOADS'] = 'Downloads';
$lang['SORT_POSTTIME'] = 'Post Time';

// View Types
$lang['VIEW_STATISTIC'] = 'Statistics';
$lang['VIEW_SEARCH'] = 'Search';
$lang['VIEW_USERNAME'] = 'Username';
$lang['VIEW_ATTACHMENTS'] = 'Attachments';

// Successfully updated
$lang['ATTACH_CONFIG_UPDATED'] = 'Attachment Configuration updated successfully';
$lang['CLICK_RETURN_ATTACH_CONFIG'] = 'Click %sHere%s to return to Attachment Configuration';
$lang['TEST_SETTINGS_SUCCESSFUL'] = 'Settings Test has been finished, configuration seems to be fine.';

// Some basic definitions
$lang['ATTACHMENTS'] = 'Attachments';
$lang['EXTENSIONS'] = 'Extensions';
$lang['EXTENSION'] = 'Extension';

$lang['RETURN_CONFIG'] = '%sReturn to Configuration%s';
$lang['CONFIG_UPD'] = 'Configuration Updated Successfully';
$lang['SET_DEFAULTS'] = 'Restore defaults';

// Tracker config
$lang['TRACKER_CFG_TITLE'] = 'Tracker';
$lang['FORUM_CFG_TITLE'] = 'Forum settings';
$lang['TRACKER_SETTINGS'] = 'Tracker settings';

$lang['CHANGES_DISABLED'] = 'Changes disabled (see <b>$bb_cfg[\'tracker\']</b> in config.php)';

$lang['OFF_TRACKER'] = 'Disable tracker';
$lang['OFF_REASON'] = 'Disable reason';
$lang['OFF_REASON_EXPL'] = 'this message will be sent to client when the tracker is disabled';
$lang['AUTOCLEAN_EXPL'] = 'autoclean peers table - do not disable without reason';
$lang['COMPACT_MODE'] = 'Compact mode';
$lang['COMPACT_MODE_EXPL'] = '"Yes" - tracker will only accept clients working in compact mode<br />"No" - compatible mode (chosen by client)';
$lang['BROWSER_REDIRECT_URL'] = 'Browser redirect URL';
$lang['BROWSER_REDIRECT_URL_EXPL'] = 'if user tries to open tracker URL in Web browser<br />leave blank to disable';

$lang['ANNOUNCE_INTERVAL_HEAD'] = 'Misc';
$lang['ANNOUNCE_INTERVAL'] = 'Announce interval';
$lang['ANNOUNCE_INTERVAL_EXPL'] = 'peers should wait at least this many seconds between announcements';
$lang['NUMWANT'] = 'Numwant value';
$lang['NUMWANT_EXPL'] = 'number of peers being sent to client';
$lang['EXPIRE_FACTOR'] = 'Peer expire factor';
$lang['EXPIRE_FACTOR_EXPL'] = 'Consider a peer dead if it has not announced in a number of seconds equal to this many times the calculated announce interval at the time of its last announcement (must be greater than 1)';
$lang['IGNORE_GIVEN_IP'] = 'Ignore IP reported by client';
$lang['UPDATE_DLSTAT'] = 'Store users up/down statistics';

$lang['LIMIT_ACTIVE_TOR_HEAD'] = 'Limits';
$lang['LIMIT_ACTIVE_TOR'] = 'Limit active torrents';
$lang['LIMIT_SEED_COUNT'] = 'Seeding limit';
$lang['LIMIT_SEED_COUNT_EXPL'] = '(0 - no limit)';
$lang['LIMIT_LEECH_COUNT'] = 'Leeching limit';
$lang['LIMIT_LEECH_COUNT_EXPL'] = '(0 - no limit)';
$lang['LEECH_EXPIRE_FACTOR'] = 'Leech expire factor';
$lang['LEECH_EXPIRE_FACTOR_EXPL'] = 'Treat a peer as active for this number of minutes even if it sent "stopped" event after starting dl<br />0 - take into account "stopped" event';
$lang['LIMIT_CONCURRENT_IPS'] = "Limit concurrent IP's";
$lang['LIMIT_CONCURRENT_IPS_EXPL'] = 'per torrent limit';
$lang['LIMIT_SEED_IPS'] = 'Seeding IP limit';
$lang['LIMIT_SEED_IPS_EXPL'] = "allow seeding from no more than <i>xx</i> IP's<br />0 - no limit";
$lang['LIMIT_LEECH_IPS'] = 'Leeching IP limit';
$lang['LIMIT_LEECH_IPS_EXPL'] = "allow leeching from no more than <i>xx</i> IP's<br />0 - no limit";

$lang['USE_AUTH_KEY_HEAD'] = 'Authorization';
$lang['USE_AUTH_KEY'] = 'Passkey';
$lang['USE_AUTH_KEY_EXPL'] = 'enable check for passkey';
$lang['AUTH_KEY_NAME'] = 'Passkey name';
$lang['AUTH_KEY_NAME_EXPL'] = 'passkey key name in GET request';
$lang['ALLOW_GUEST_DL'] = 'Allow guest access to tracker';
$lang['ADD_RETRACKER'] = 'Add retracker in torrent files';

// Forum config
$lang['FORUM_CFG_EXPL'] = 'Forum config';

$lang['BT_SELECT_FORUMS'] = 'Forum options:';
$lang['BT_SELECT_FORUMS_EXPL'] = 'hold down <i>Ctrl</i> while selecting multiple forums';

$lang['REG_TORRENTS'] = 'Register torrents';
$lang['DISALLOWED'] = 'Prohibited';
$lang['ALLOW_REG_TRACKER'] = 'Allowed forums for registering .torrents on tracker';
$lang['ALLOW_PORNO_TOPIC'] = 'Allowed to post content 18+';
$lang['SHOW_DL_BUTTONS'] = 'Show buttons for manually changing DL-status';
$lang['SELF_MODERATED'] = 'Users can <b>move</b> their topics to another forum';

$lang['BT_ANNOUNCE_URL_HEAD'] = 'Announce URL';
$lang['BT_ANNOUNCE_URL'] = 'Announce url';
$lang['BT_ANNOUNCE_URL_EXPL'] = 'you can define additional allowed urls in "includes/torrent_announce_urls.php"';
$lang['BT_DISABLE_DHT'] = 'Disable DHT network';
$lang['BT_DISABLE_DHT_EXPL'] = 'Disable Peer Exchange and DHT (recommended for private networks, only url announce)';
$lang['BT_CHECK_ANNOUNCE_URL'] = 'Verify announce url';
$lang['BT_CHECK_ANNOUNCE_URL_EXPL'] = 'register on tracker only allowed urls';
$lang['BT_REPLACE_ANN_URL'] = 'Replace announce url';
$lang['BT_REPLACE_ANN_URL_EXPL'] = 'replace original announce url with your default in .torrent files';
$lang['BT_DEL_ADDIT_ANN_URLS'] = 'Remove all additional announce urls';
$lang['BT_DEL_ADDIT_ANN_URLS_EXPL'] = 'if the torrent contains the addresses of other trackers, they will be removed';

$lang['BT_SHOW_PEERS_HEAD'] = 'Peers-List';
$lang['BT_SHOW_PEERS'] = 'Show peers (seeders and leechers)';
$lang['BT_SHOW_PEERS_EXPL'] = 'this will show seeders/leechers list above the topic with torrent';
$lang['BT_SHOW_PEERS_MODE'] = 'By default, show peers as:';
$lang['BT_SHOW_PEERS_MODE_COUNT'] = 'Count only';
$lang['BT_SHOW_PEERS_MODE_NAMES'] = 'Names only';
$lang['BT_SHOW_PEERS_MODE_FULL'] = 'Full details';
$lang['BT_ALLOW_SPMODE_CHANGE'] = 'Allow "Full details" mode';
$lang['BT_ALLOW_SPMODE_CHANGE_EXPL'] = 'if "no", only default peer display mode will be available';
$lang['BT_SHOW_IP_ONLY_MODER'] = 'Peers\' <b>IP</b>s are visible to moderators only';
$lang['BT_SHOW_PORT_ONLY_MODER'] = 'Peers\' <b>Port</b>s are visible to moderators only';

$lang['BT_SHOW_DL_LIST_HEAD'] = 'DL-List';
$lang['BT_SHOW_DL_LIST'] = 'Show DL-List in Download topics';
$lang['BT_DL_LIST_ONLY_1ST_PAGE'] = 'Show DL-List only on first page in topics';
$lang['BT_DL_LIST_ONLY_COUNT'] = 'Show only number of users';
$lang['BT_SHOW_DL_LIST_BUTTONS'] = 'Show buttons for manually changing DL-status';
$lang['BT_SHOW_DL_BUT_WILL'] = $lang['DLWILL'];
$lang['BT_SHOW_DL_BUT_DOWN'] = $lang['DLDOWN'];
$lang['BT_SHOW_DL_BUT_COMPL'] = $lang['DLCOMPLETE'];
$lang['BT_SHOW_DL_BUT_CANCEL'] = $lang['DLCANCEL'];

$lang['BT_ADD_AUTH_KEY_HEAD'] = 'Passkey';
$lang['BT_ADD_AUTH_KEY'] = 'Enable adding passkey to the torrent-files before downloading';

$lang['BT_TOR_BROWSE_ONLY_REG_HEAD'] = 'Torrent browser (tracker)';
$lang['BT_TOR_BROWSE_ONLY_REG'] = 'Torrent browser (tracker.php) accessible only for logged in users';
$lang['BT_SEARCH_BOOL_MODE'] = 'Allow boolean full-text searches';
$lang['BT_SEARCH_BOOL_MODE_EXPL'] = 'use *, +, -,.. in searches';

$lang['BT_SHOW_DL_STAT_ON_INDEX_HEAD'] = "Miscellaneous";
$lang['BT_SHOW_DL_STAT_ON_INDEX'] = "Show users UL/DL statistics at the top of the forum's main page";
$lang['BT_NEWTOPIC_AUTO_REG'] = 'Automatically register torrent on tracker for new topics';
$lang['BT_SET_DLTYPE_ON_TOR_REG'] = 'Change topic status to "Download" while registering torrent on tracker';
$lang['BT_SET_DLTYPE_ON_TOR_REG_EXPL'] = 'will change topic type to "Download" regardless of forum settings';
$lang['BT_UNSET_DLTYPE_ON_TOR_UNREG'] = 'Change topic status to "Normal" while unregistering torrent from tracker';

// Release
$lang['TEMPLATE_DISABLE'] = 'Template disabled';
$lang['FOR_NEW_TEMPLATE'] = 'for new patterns!';
$lang['CHANGED'] = 'Changed';
$lang['REMOVED'] = 'Removed';
$lang['QUESTION'] = 'Are you sure want to delete?';

$lang['CRON_LIST'] = 'Cron list';
$lang['CRON_ID'] = 'ID';
$lang['CRON_ACTIVE'] = 'On';
$lang['CRON_ACTIVE_EXPL'] = 'Active tasks';
$lang['CRON_TITLE'] = 'Title';
$lang['CRON_SCRIPT'] = 'Script';
$lang['CRON_SCHEDULE'] = 'Schedule';
$lang['CRON_LAST_RUN'] = 'Last Run';
$lang['CRON_NEXT_RUN'] = 'Next Run';
$lang['CRON_RUN_COUNT'] = 'Runs';
$lang['CRON_MANAGE'] = 'Manage';
$lang['CRON_OPTIONS'] = 'Cron options';

$lang['CRON_ENABLED'] = 'Cron enabled';
$lang['CRON_CHECK_INTERVAL'] = 'Cron check interval (sec)';

$lang['WITH_SELECTED'] = 'With selected';
$lang['NOTHING'] = 'do nothing';
$lang['CRON_RUN'] = 'Run';
$lang['CRON_DEL'] = 'Delete';
$lang['CRON_DISABLE'] = 'Disable';
$lang['CRON_ENABLE'] = 'Enable';

$lang['RUN_MAIN_CRON'] = 'Start cron';
$lang['ADD_JOB'] = 'Add cron job';
$lang['DELETE_JOB'] = 'Are you sure you want to delete cron job?';
$lang['CRON_WORKS'] = 'Cron is now works or is broken -> ';
$lang['REPAIR_CRON'] = 'Repair Cron';

$lang['CRON_EDIT_HEAD_EDIT'] = 'Edit job';
$lang['CRON_EDIT_HEAD_ADD'] = 'Add job';
$lang['CRON_SCRIPT_EXPL'] = 'name of the script from "includes/cron/jobs/"';
$lang['SCHEDULE'] = array(
    'select' => '&raquo; Select start',
    'hourly' => 'hourly',
    'daily' => 'daily',
    'weekly' => 'weekly',
    'monthly' => 'monthly',
    'interval' => 'interval'
);
$lang['NOSELECT'] = 'No select';
$lang['RUN_DAY'] = 'Run day';
$lang['RUN_DAY_EXPL'] = 'the day when this job run';
$lang['RUN_TIME'] = 'Run time';
$lang['RUN_TIME_EXPL'] = 'the time when this job run (e.g. 05:00:00)';
$lang['RUN_ORDER'] = 'Run order';
$lang['LAST_RUN'] = 'Last Run';
$lang['NEXT_RUN'] = 'Next Run';
$lang['RUN_INTERVAL'] = 'Run interval';
$lang['RUN_INTERVAL_EXPL'] = 'e.g. 00:10:00';
$lang['LOG_ENABLED'] = 'Log enabled';
$lang['LOG_FILE'] = 'Log file';
$lang['LOG_FILE_EXPL'] = 'the file for save the log';
$lang['LOG_SQL_QUERIES'] = 'Log SQL queries';
$lang['FORUM_DISABLE'] = 'Disable board';
$lang['BOARD_DISABLE_EXPL'] = 'disable board when this job is run';
$lang['RUN_COUNTER'] = 'Run counter';

$lang['JOB_REMOVED'] = 'The problem has been removed successfully';
$lang['SCRIPT_DUPLICATE'] = 'Script <b>' . @$_POST['cron_script'] . '</b> already exists!';
$lang['TITLE_DUPLICATE'] = 'Task Name <b>' . @$_POST['cron_title'] . '</b> already exists!';
$lang['CLICK_RETURN_JOBS_ADDED'] = '%sReturn to the addition problem%s';
$lang['CLICK_RETURN_JOBS'] = '%sBack to the Task Scheduler%s';

$lang['REBUILD_SEARCH'] = 'Rebuild Search Index';
$lang['REBUILD_SEARCH_DESC'] = 'This mod will index every post in your forum, rebuilding the search tables. You can stop whenever you like and the next time you run it again you\'ll have the option of continuing from where you left off.<br /><br />It may take a long time to show its progress (depending on "Posts per cycle" and "Time limit"), so please do not move from its progress page until it is complete, unless of course you want to interrupt it.';

// Input screen
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

// Information strings
$lang['INFO_PROCESSING_STOPPED'] = 'You last stopped the processing at post_id %s (%s processed posts) on %s';
$lang['INFO_PROCESSING_ABORTED'] = 'You last aborted the processing at post_id %s (%s processed posts) on %s';
$lang['INFO_PROCESSING_ABORTED_SOON'] = 'Please wait some mins before you continue...';
$lang['INFO_PROCESSING_FINISHED'] = 'You successfully finished the processing (%s processed posts) on %s';
$lang['INFO_PROCESSING_FINISHED_NEW'] = 'You successfully finished the processing at post_id %s (%s processed posts) on %s,<br />but there have been %s new post(s) after that date';

// Progress screen
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

$lang['DATABASE_SIZE_DETAILS'] = 'Database size details';
$lang['SIZE_CURRENT'] = 'Current';
$lang['SIZE_ESTIMATED'] = 'Estimated after finish';
$lang['SIZE_SEARCH_TABLES'] = 'Search Tables size';
$lang['SIZE_DATABASE'] = 'Database size';

$lang['ACTIVE_PARAMETERS'] = 'Active parameters';
$lang['POSTS_LAST_CYCLE'] = 'Processed post(s) on last cycle';
$lang['BOARD_STATUS'] = 'Board status';

$lang['INFO_ESTIMATED_VALUES'] = '(*) All the estimated values are calculated approximately<br />based on the current completed percent and may not represent the actual final values.<br />As the completed percent increases the estimated values will come closer to the actual ones.';

$lang['CLICK_RETURN_REBUILD_SEARCH'] = 'Click %shere%s to return to Rebuild Search';
$lang['REBUILD_SEARCH_ABORTED'] = 'Rebuild search aborted at post_id %s.<br /><br />If you aborted while processing was on, you have to wait for some mins until you run Rebuild Search again, so the last cycle can finish.';
$lang['WRONG_INPUT'] = 'You have entered some wrong values. Please check your input and try again.';

// Buttons
$lang['PROCESSING'] = 'Processing...';
$lang['FINISHED'] = 'Finished';

$lang['BOT_TOPIC_MOVED_FROM_TO'] = 'Topic has been moved from forum [b]%s[/b] to forum [b]%s[/b][br][br]%s';
$lang['BOT_MESS_SPLITS'] = 'Topic has been split. New topic - [b]%s[/b][br][br]%s';
$lang['BOT_TOPIC_SPLITS'] = 'Topic has been split from [b]%s[/b][br][br]%s';

$lang['CALLSEED'] = 'Downloaded the call';
$lang['CALLSEED_EXPLAIN'] = 'Take notice with a request to return to the distribution';
$lang['CALLSEED_SUBJECT'] = 'Download help %s';
$lang['CALLSEED_TEXT'] = 'Hello![br]Your help is needed in the release [url=%s]%s[/url][br]If you decide to help, but already deleted the torrent file, you can download it [url=%s]this[/url][br][br]I hope for your help!';
$lang['CALLSEED_MSG_OK'] = 'Message has been sent to all those who downloaded this release';
$lang['CALLSEED_MSG_SPAM'] = 'Request has already been once successfully sent (Probably not you)<br /><br />The next opportunity to send a request to be <b>%s</b>.';
$lang['CALLSEED_HAVE_SEED'] = 'Topic does not require help (<b>Seeders:</b> %d)';

$lang['LOG_ACTION']['LOG_TYPE'] = array(
    'mod_topic_delete' => 'Topic:<br /> <b>deleted</b>',
    'mod_topic_move' => 'Topic:<br /> <b>moved</b>',
    'mod_topic_lock' => 'Topic:<br /> <b>closed</b>',
    'mod_topic_unlock' => 'Topic:<br /> <b>opened</b>',
    'mod_topic_split' => 'Topic:<br /> <b>split</b>',
    'mod_post_delete' => 'Post:<br /> <b>deleted</b>',
    'adm_user_delete' => 'User:<br /> <b>deleted</b>',
    'adm_user_ban' => 'User:<br /> <b>ban</b>',
    'adm_user_unban' => 'User:<br /> <b>unban</b>',
);

$lang['ACTS_LOG_ALL_ACTIONS'] = 'All actions';
$lang['ACTS_LOG_SEARCH_OPTIONS'] = 'Actions Log: Search options';
$lang['ACTS_LOG_FORUM'] = 'Forum';
$lang['ACTS_LOG_ACTION'] = 'Action';
$lang['ACTS_LOG_USER'] = 'User';
$lang['ACTS_LOG_LOGS_FROM'] = 'Logs from ';
$lang['ACTS_LOG_FIRST'] = 'beginning with';
$lang['ACTS_LOG_DAYS_BACK'] = 'days back';
$lang['ACTS_LOG_TOPIC_MATCH'] = 'Topic title match';
$lang['ACTS_LOG_SORT_BY'] = 'Sort by';
$lang['ACTS_LOG_LOGS_ACTION'] = 'Action';
$lang['ACTS_LOG_USERNAME'] = 'Username';
$lang['ACTS_LOG_TIME'] = 'Time';
$lang['ACTS_LOG_INFO'] = 'Info';
$lang['ACTS_LOG_FILTER'] = 'Filter';
$lang['ACTS_LOG_TOPICS'] = 'Topics:';
$lang['ACTS_LOG_OR'] = 'or';

$lang['RELEASE'] = 'Release Templates';
$lang['RELEASES'] = 'Releases';

$lang['BACK'] = 'Back';
$lang['ERROR_FORM'] = 'Invalid fields';
$lang['RELEASE_WELCOME'] = 'Pleae fill in the releae form';
$lang['NEW_RELEASE'] = 'New release';
$lang['NEXT'] = 'Continue';
$lang['OTHER'] = 'Other';

$lang['TPL_EMPTY_FIELD'] = 'You must fill the field <b>%s</b>';
$lang['TPL_EMPTY_SEL'] = 'You must select <b>%s</b>';
$lang['TPL_NOT_NUM'] = '<b>%s</b> - Not a num';
$lang['TPL_NOT_URL'] = '<b>%s</b> - Must be http:// URL';
$lang['TPL_NOT_IMG_URL'] = '<b>%s</b> - Must be http:// IMG_URL';
$lang['TPL_PUT_INTO_SUBJECT'] = 'put into the subject';
$lang['TPL_POSTER'] = 'poster';
$lang['TPL_REQ_FILLING'] = 'requires filling';
$lang['TPL_NEW_LINE'] = 'new line';
$lang['TPL_NEW_LINE_AFTER'] = 'new line after the title';
$lang['TPL_NUM'] = 'number';
$lang['TPL_URL'] = 'URL';
$lang['TPL_IMG'] = 'image';
$lang['TPL_PRE'] = 'pre';
$lang['TPL_SPOILER'] = 'spoiler';
$lang['TPL_IN_LINE'] = 'in the same line';
$lang['TPL_HEADER_ONLY'] = 'only in a title';

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
$lang['SEARCH_INVALID_TIMEZONE'] = 'Invalid Timezone Selected';
$lang['SEARCH_INVALID_MODERATORS'] = 'Invalid Forum Selected';
$lang['SEARCH_INVALID'] = 'Invalid Search';
$lang['SEARCH_INVALID_DAY'] = 'The day you entered was invalid';
$lang['SEARCH_INVALID_MONTH'] = 'The month you entered was invalid';
$lang['SEARCH_INVALID_YEAR'] = 'The year you entered was invalid';
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
$lang['SEARCH_FOR_POSTCOUNT_GREATER'] = 'Searching for users with a post count greater than %d';
$lang['SEARCH_FOR_POSTCOUNT_LESSER'] = 'Searching for users with a post count less than %d';
$lang['SEARCH_FOR_POSTCOUNT_RANGE'] = 'Searching for users with a post count between %d and %d';
$lang['SEARCH_FOR_POSTCOUNT_EQUALS'] = 'Searching for users with a post count value of %d';
$lang['SEARCH_FOR_USERFIELD_ICQ'] = 'Searching for users with a ICQ address matching %s';
$lang['SEARCH_FOR_USERFIELD_SKYPE'] = 'Searching for users with an Skype matching %s';
$lang['SEARCH_FOR_USERFIELD_TWITTER'] = 'Searching for users with an Twitter matching %s';
$lang['SEARCH_FOR_USERFIELD_WEBSITE'] = 'Searching for users with an Website matching %s';
$lang['SEARCH_FOR_USERFIELD_LOCATION'] = 'Searching for users with a Location matching %s';
$lang['SEARCH_FOR_USERFIELD_INTERESTS'] = 'Searching for users with their Interests field matching %s';
$lang['SEARCH_FOR_USERFIELD_OCCUPATION'] = 'Searching for users with their Occupation field matching %s';
$lang['SEARCH_FOR_LASTVISITED_INTHELAST'] = 'Searching for users who have visited in the last %s %s';
$lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'] = 'Searching for users who have visited after the last %s %s';
$lang['SEARCH_FOR_LANGUAGE'] = 'Searching for users who have set %s as their language';
$lang['SEARCH_FOR_TIMEZONE'] = 'Searching for users who have set UTC %s as their timezone';
$lang['SEARCH_FOR_STYLE'] = 'Searching for users who have set %s as their style';
$lang['SEARCH_FOR_MODERATORS'] = 'Search for moderators of the Forum -> %s';
$lang['SEARCH_USERS_ADVANCED'] = 'Advanced User Search';
$lang['SEARCH_USERS_EXPLAIN'] = 'This Module allows you to perform advanced searches for users on a wide range of criteria. Please read the descriptions under each field to understand each search option completely.';
$lang['SEARCH_USERNAME_EXPLAIN'] = 'Here you can perform a case insensitive search for usernames. If you would like to match part of the username, use * (an asterix) as a wildcard.';
$lang['SEARCH_EMAIL_EXPLAIN'] = 'Enter an expression to match a user\'s email address. This is case insensitive. If you want to do a partial match, use * (an asterix) as a wildcard.';
$lang['SEARCH_IP_EXPLAIN'] = 'Search for users by a specific IP address (xxx.xxx.xxx.xxx).';
$lang['SEARCH_USERS_JOINED'] = 'Users that joined';
$lang['SEARCH_USERS_LASTVISITED'] = 'Users whom have visited';
$lang['IN_THE_LAST'] = 'in the last';
$lang['AFTER_THE_LAST'] = 'after the last';
$lang['BEFORE'] = 'Before';
$lang['AFTER'] = 'After';
$lang['SEARCH_USERS_JOINED_EXPLAIN'] = 'Search for users the join Before or After (and on) a specific date. The date format is YYYY/MM/DD.';
$lang['SEARCH_USERS_GROUPS_EXPLAIN'] = 'View all members of the selected group.';
$lang['SEARCH_USERS_RANKS_EXPLAIN'] = 'View all carriers of the selected rank.';
$lang['BANNED_USERS'] = 'Banned Users';
$lang['DISABLED_USERS'] = 'Disabled Users';
$lang['SEARCH_USERS_MISC_EXPLAIN'] = 'Administrators - All users with Administrator powers; Moderators - All forum moderators; Banned Users - All accounts that have been banned on these forums; Disabled Users - All users with disabled accounts (either manually disabled or never verified their email address); Users with disabled PMs - Selects users who have the Private Messages priviliges removed (Done via User Management)';
$lang['POSTCOUNT'] = 'Postcount';
$lang['EQUALS'] = 'Equals';
$lang['GREATER_THAN'] = 'Greater than';
$lang['LESS_THAN'] = 'Less than';
$lang['SEARCH_USERS_POSTCOUNT_EXPLAIN'] = 'You can search for users based on the Postcount value. You can either search by a specific value, greater than or lesser than a value or between two values. To do the range search, select "Equals" then put the beginning and ending values of the range separated by a dash (-), e.g. 10-15';
$lang['USERFIELD'] = 'Userfield';
$lang['SEARCH_USERS_USERFIELD_EXPLAIN'] = 'Search for users based on various profile fields. Wildcards are supported using an asterix (*).';
$lang['SEARCH_USERS_LASTVISITED_EXPLAIN'] = 'You can search for users based on their last login date using this search option';
$lang['SEARCH_USERS_LANGUAGE_EXPLAIN'] = 'This will display users whom have selected a specific language in their Profile';
$lang['SEARCH_USERS_TIMEZONE_EXPLAIN'] = 'Users who have selected a specific timezone in their profile';
$lang['SEARCH_USERS_STYLE_EXPLAIN'] = 'Display users who have selected a specific style.';
$lang['MODERATORS_OF'] = 'Moderators of';
$lang['SEARCH_USERS_MODERATORS_EXPLAIN'] = 'Search for users with Moderating permissions to a specific forum. Moderating permissions are recoginised either by User Permissions or by being in a Group with the right Group Permssions.';

$lang['SEARCH_USERS_NEW'] = '%s yielded %d result(s). Perform <a href="%s">another search</a>.';
$lang['BANNED'] = 'Banned';
$lang['NOT_BANNED'] = 'Not Banned';
$lang['SEARCH_NO_RESULTS'] = 'No users match your selected criteria. Please try another search. If you\'re searching the username or email address fields, for partial matches you must use the wildcard * (an asterix).';
$lang['ACCOUNT_STATUS'] = 'Account Status';
$lang['SORT_OPTIONS'] = 'Sort options:';
$lang['LAST_VISIT'] = 'Last Visit';
$lang['DAY'] = 'Day';

$lang['POST_EDIT_CANNOT'] = 'Sorry, but you cannot edit posts';
$lang['FORUMS_IN_CAT'] = 'forums in that category';

$lang['MC_TITLE'] = 'Comment Moderation';
$lang['MC_LEGEND'] = 'Type a comment';
$lang['MC_FAQ'] = 'Entered text will be displayed under this message';
$lang['MC_COMMENT_PM_SUBJECT'] = "%s in your message";
$lang['MC_COMMENT_PM_MSG'] = "Hello, [b]%s[/b]\nModerator left in your message [url=%s][b]%s[/b][/url][quote]\n%s\n[/quote]";
$lang['MC_COMMENT'] = array(
    0 => array(
        'title' => '',
        'type' => 'Delete comment',
    ),
    1 => array(
        'title' => 'Comment from %s',
        'type' => 'Comment',
    ),
    2 => array(
        'title' => 'Information from %s',
        'type' => 'Information',
    ),
    3 => array(
        'title' => 'Warning from %s',
        'type' => 'Warning',
    ),
    4 => array(
        'title' => 'Violation from %s',
        'type' => 'Violation',
    ),
);

$lang['SITEMAP'] = 'Sitemap';
$lang['SITEMAP_ADMIN'] = 'Manage sitemap';
$lang['SITEMAP_CREATED'] = 'Sitemap created';
$lang['SITEMAP_AVAILABLE'] = 'and is available at';
$lang['SITEMAP_NOT_CREATED'] = 'Sitemap is not yet created';
$lang['SITEMAP_NOTIFY_SEARCH'] = 'Notification of the search engine';
$lang['SITEMAP_SENT'] = 'send completed';
$lang['SITEMAP_ERROR'] = 'sending error';
$lang['SITEMAP_OPTIONS'] = 'Options';
$lang['SITEMAP_CREATE'] = 'Create / update the sitemap';
$lang['SITEMAP_NOTIFY'] = 'Notify search engines about new version of sitemap';
$lang['SITEMAP_WHAT_NEXT'] = 'What to do next?';
$lang['SITEMAP_GOOGLE_1'] = 'Register your site at <a href="http://www.google.com/webmasters/" target="_blank">Google Webmaster</a> using your Google account.';
$lang['SITEMAP_GOOGLE_2'] = '<a href="https://www.google.com/webmasters/tools/sitemap-list" target="_blank">Add sitemap</a> of site you registered.';
$lang['SITEMAP_YANDEX_1'] = 'Register your site at <a href="http://webmaster.yandex.ru/sites/" target="_blank">Yandex Webmaster</a> using your Yandex account.';
$lang['SITEMAP_YANDEX_2'] = '<a href="http://webmaster.yandex.ru/site/map.xml" target="_blank">Add sitemap</a> of site you registered.';
$lang['SITEMAP_BING_1'] = 'Register your site at <a href="https://www.bing.com/webmaster/" target="_blank">Bing Webmaster</a> using your Microsoft account.';
$lang['SITEMAP_BING_2'] = 'Add sitemap of site you registered in its settings.';
$lang['SITEMAP_ADD_TITLE'] = 'Additional pages for sitemap';
$lang['SITEMAP_ADD_PAGE'] = 'Additional pages';
$lang['SITEMAP_ADD_EXP_1'] = 'You can specify additional pages on your site (for example, <b>http://torrentpier.me/memberlist.php</b>) which should be included in your sitemap file that you creating.';
$lang['SITEMAP_ADD_EXP_2'] = 'Each reference must begin with http(s):// and a new line!';

$lang['FORUM_MAP'] = 'Forums\' map';
$lang['ATOM_FEED'] = 'Feed';
$lang['ATOM_ERROR'] = 'Error generating feed';
$lang['ATOM_SUBSCRIBE'] = 'Subscribe to the feed';
$lang['ATOM_NO_MODE'] = 'Do not specify a mode for the feed';
$lang['ATOM_NO_FORUM'] = 'This forum does not have a feed (no ongoing topics)';
$lang['ATOM_NO_USER'] = 'This user does not have a feed (no ongoing topics)';

$lang['HASH_INVALID'] = 'Hash %s is invalid';
$lang['HASH_NOT_FOUND'] = 'Release with hash %s not found';

$lang['TERMS_EMPTY_TEXT'] = '[align=center]The text of this page is edited at: [url=http://%s/admin/admin_terms.php]admin/admin_terms.php[/url]. This line can see only administrators.[/align]';
$lang['TERMS_EXPLAIN'] = 'On this page, you can specify the text of the basic rules of the resource is displayed to users.';

$lang['TR_STATS'] = array(
    0 => 'inactive users in 30 days',
    1 => 'inactive users for 90 days',
    2 => 'medium size distributions on the tracker (many megabytes)',
    3 => 'how many total hands on the tracker',
    4 => 'how many live hands (there is at least 1 led)',
    5 => 'how many hands where that seeding more than 5 seeds',
    6 => 'how many of us uploaders (those who filled at least 1 hand)',
    7 => 'how many uploaders over the last 30 days',
);

$lang['NEW_POLL_START'] = 'Poll enabled';
$lang['NEW_POLL_END'] = 'Poll completed';
$lang['NEW_POLL_ENDED'] = 'This poll has already been completed';
$lang['NEW_POLL_DELETE'] = 'Poll deleted';
$lang['NEW_POLL_ADDED'] = 'Poll added';
$lang['NEW_POLL_ALREADY'] = 'Theme already has a poll';
$lang['NEW_POLL_RESULTS'] = 'Poll changed and the old results deleted';
$lang['NEW_POLL_VOTES'] = 'You must enter a correct response options (minimum 2, maximum is %s)';
$lang['NEW_POLL_DAYS'] = 'The time of the poll (%s days from the moment of creation theme) already ended';
$lang['NEW_POLL_U_NOSEL'] = 'You have not selected that vote';
$lang['NEW_POLL_U_CHANGE'] = 'Edit poll';
$lang['NEW_POLL_U_EDIT'] = 'Change the poll (the old results will be deleted)';
$lang['NEW_POLL_U_VOTED'] = 'All voted';
$lang['NEW_POLL_U_START'] = 'Enable poll';
$lang['NEW_POLL_U_END'] = 'Finish poll';
$lang['NEW_POLL_M_TITLE'] = 'Title of poll';
$lang['NEW_POLL_M_VOTES'] = 'Options';
$lang['NEW_POLL_M_EXPLAIN'] = 'Each row corresponds to one answer (max';

$lang['OLD_BROWSER'] = 'You are using an outdated browser. The website will not display correctly.';
$lang['GO_BACK'] = 'Go Back';

$lang['UPLOAD_ERROR_COMMON'] = 'File upload error';
$lang['UPLOAD_ERROR_SIZE'] = 'The uploaded file exceeds the maximum size of %s';
$lang['UPLOAD_ERROR_FORMAT'] = 'Invalid file type of image';
$lang['UPLOAD_ERROR_DIMENSIONS'] = 'Image dimensions exceed the maximum allowable %sx%s px';
$lang['UPLOAD_ERROR_NOT_IMAGE'] = 'The uploaded file is not an image';
$lang['UPLOAD_ERROR_NOT_ALLOWED'] = 'Extension %s for downloads is not allowed';
$lang['UPLOAD_ERRORS'] = array(
    UPLOAD_ERR_INI_SIZE => 'you have exceeded the maximum file size for the server',
    UPLOAD_ERR_FORM_SIZE => 'you have exceeded the maximum file upload size',
    UPLOAD_ERR_PARTIAL => 'the file was partially downloaded',
    UPLOAD_ERR_NO_FILE => 'file was not uploaded',
    UPLOAD_ERR_NO_TMP_DIR => 'temporary directory not found',
    UPLOAD_ERR_CANT_WRITE => 'write error',
    UPLOAD_ERR_EXTENSION => 'upload stopped by extension',
);

// Captcha
$lang['CAPTCHA'] = 'Check that you are not a robot';
$lang['CAPTCHA_WRONG'] = 'You could not confirm that you are not a robot';
$lang['CAPTCHA_SETTINGS'] = '<h2>ReCaptcha not being fully configured</h2><p>If you haven\'t already generated the keys, you can do it on <a href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a>.<br />After you generate the keys, you need to put them at the file library/config.php.</p>';

// Emailer
$lang['EMAILER_SUBJECT'] = [
    'EMPTY' => 'No Subject',
    'GROUP_ADDED' => 'You have been added to this usergroup',
    'GROUP_APPROVED' => 'Your request has been approved',
    'GROUP_REQUEST' => 'A request to join your group has been made',
    'PRIVMSG_NOTIFY' => 'New Private Message has arrived',
    'TOPIC_NOTIFY' => 'Topic Reply Notification %s',
    'USER_ACTIVATE' => 'Reactivate your account',
    'USER_ACTIVATE_PASSWD' => 'New password activation',
    'USER_WELCOME' => 'Welcome to %s Forums',
    'USER_WELCOME_INACTIVE' => 'Welcome to %s Forums',
];
