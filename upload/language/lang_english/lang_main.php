<?php

setlocale(LC_ALL, 'eu_US.UTF-8');
$lang['CONTENT_ENCODING'] = 'UTF-8';
$lang['CONTENT_DIRECTION'] = 'ltr';
$lang['DATE_FORMAT'] =  'Y-m-d'; // This should be changed to the default date format for your language, php date() format
$lang['TRANSLATION_INFO'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['FORUM'] = 'Forum';
$lang['CATEGORY'] = 'Category';
$lang['TOPIC'] = 'Topic';
$lang['TOPICS'] = 'Topics';
$lang['TOPICS_SHORT'] = 'Topics';
$lang['REPLIES'] = 'Replies';
$lang['REPLIES_SHORT'] = 'Replies';
$lang['VIEWS'] = 'Views';
$lang['POST'] = 'Post';
$lang['POSTS'] = 'Posts';
$lang['POSTS_SHORT'] = 'Posts';
$lang['POSTED'] = 'Posted';
$lang['USERNAME'] = 'Username';
$lang['PASSWORD'] = 'Password';
$lang['EMAIL'] = 'Email';
$lang['POSTER'] = 'Poster';
$lang['AUTHOR'] = 'Author';
$lang['TIME'] = 'Time';
$lang['HOURS'] = 'Hours';
$lang['MESSAGE'] = 'Message';
$lang['TORRENT'] = 'Torrent';
$lang['MANAGE'] = 'Profile';
$lang['PERMISSIONS'] = 'Permissions';

$lang['1_DAY'] = '1 Day';
$lang['7_DAYS'] = '7 Days';
$lang['2_WEEKS'] = '2 Weeks';
$lang['1_MONTH'] = '1 Month';
$lang['3_MONTHS'] = '3 Months';
$lang['6_MONTHS'] = '6 Months';
$lang['1_YEAR'] = '1 Year';

$lang['GO'] = 'Go';
$lang['JUMP_TO'] = 'Jump to';
$lang['SUBMIT'] = 'Submit';
$lang['DO_SUBMIT'] = 'Submit';
$lang['RESET'] = 'Reset';
$lang['CANCEL'] = 'Cancel';
$lang['PREVIEW'] = 'Preview';
$lang['CONFIRM'] = 'Confirm';
$lang['YES'] = 'Yes';
$lang['NO'] = 'No';
$lang['ENABLED'] = 'Enabled';
$lang['DISABLED'] = 'Disabled';
$lang['ERROR'] = 'Error';
$lang['SELECT_ACTION'] = 'Select action';

$lang['NEXT'] = 'Next';
$lang['PREVIOUS'] = 'Previous';
$lang['GOTO_PAGE'] = 'Goto page';
$lang['GOTO_SHORT'] = 'Page';
$lang['JOINED'] = 'Joined';
$lang['LONGEVITY'] = 'Longevity';
$lang['IP_ADDRESS'] = 'IP Address';
$lang['POSTED_AFTER'] = 'after';

$lang['SELECT_FORUM'] = 'Select a forum';
$lang['VIEW_LATEST_POST'] = 'View latest post';
$lang['VIEW_NEWEST_POST'] = 'View newest post';
$lang['PAGE_OF'] = 'Page <b>%d</b> of <b>%s</b>';

$lang['ICQ'] = 'ICQ Number';

$lang['FORUM_INDEX'] = '%s Forum Index';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['POST_NEW_TOPIC'] = 'Post new topic';
$lang['POST_REGULAR_TOPIC'] = 'Post regular topic';
$lang['REPLY_TO_TOPIC'] = 'Reply to topic';
$lang['REPLY_WITH_QUOTE'] = 'Reply with quote';

$lang['CLICK_RETURN_TOPIC'] = 'Click %sHere%s to return to the topic'; // %s's here are for uris, do not remove!
$lang['CLICK_RETURN_LOGIN'] = 'Click %sHere%s to try again';
$lang['CLICK_RETURN_FORUM'] = 'Click %sHere%s to return to the forum';
$lang['CLICK_VIEW_MESSAGE'] = 'Click %sHere%s to view your message';
$lang['CLICK_RETURN_MODCP'] = 'Click %sHere%s to return to the Moderator Control Panel';
$lang['CLICK_RETURN_GROUP'] = 'Click %sHere%s to return to group information';

$lang['ADMIN_PANEL'] = 'Go to Administration Panel';

$lang['BOARD_DISABLE'] = 'Sorry, but this board is currently unavailable.  Please try again later.';

$lang['LOADING'] = 'Loading...';
$lang['JUMPBOX_TITLE'] = 'Select forum';
$lang['DISPLAYING_OPTIONS'] = 'Displaying options';

//
// Global Header strings
//
$lang['REGISTERED_USERS'] = 'Registered Users:';
$lang['BROWSING_FORUM'] = 'Users browsing this forum:';
$lang['ONLINE_USERS'] = 'In total there are <b>%1$d</b> users online: %2$d Registered and %3$d Guests';
$lang['RECORD_ONLINE_USERS'] = 'Most users ever online was <b>%s</b> on %s'; // first %s = number of users, second %s is the date.
$lang['USERS'] = 'users';

$lang['ONLINE_ADMIN'] = 'Administrator';
$lang['ONLINE_MOD'] = 'Moderator';
$lang['ONLINE_GROUP_MEMBER'] = 'Group member';

$lang['YOU_LAST_VISIT'] = 'You last visited on: <span class="tz_time">%s</span>';
$lang['CURRENT_TIME'] = 'The time now is: <span class="tz_time">%s</span>';

$lang['SEARCH_NEW'] = 'View newest posts';
$lang['SEARCH_SELF'] = 'my posts';
$lang['SEARCH_SELF_BY_LAST'] = 'last post time';
$lang['SEARCH_SELF_BY_MY'] = 'my post time';
$lang['SEARCH_UNANSWERED'] = 'View unanswered posts';
$lang['SEARCH_UNANSWERED_SHORT'] = 'unanswered';
$lang['SEARCH_LATEST'] = 'latest';

$lang['REGISTER'] = 'Register';
$lang['PROFILE'] = 'Profile';
$lang['EDIT_PROFILE'] = 'Edit your profile';
$lang['SEARCH'] = 'Search';
$lang['MEMBERLIST'] = 'Memberlist';
$lang['FAQ'] = 'FAQ';
$lang['BBCODE_GUIDE'] = 'BBCode Guide';
$lang['USERGROUPS'] = 'Usergroups';
$lang['LASTPOST'] = 'Last Post';
$lang['MODERATOR'] = 'Moderator';
$lang['MODERATORS'] = 'Moderators';
$lang['TERMS'] = 'Terms';

//
// Stats block text
//
$lang['POSTED_TOPICS_TOTAL'] = 'Our users have posted a total of <b>%s</b> topics'; // Number of topics
$lang['POSTED_ARTICLES_ZERO_TOTAL'] = 'Our users have posted a total of <b>0</b> articles'; // Number of posts
$lang['POSTED_ARTICLES_TOTAL'] = 'Our users have posted a total of <b>%s</b> articles'; // Number of posts
$lang['REGISTERED_USERS_ZERO_TOTAL'] = 'We have <b>0</b> registered users'; // # registered users
$lang['REGISTERED_USERS_TOTAL'] = 'We have <b>%s</b> registered users'; // # registered users
$lang['NEWEST_USER'] = 'The newest registered user is <b>%s%s%s</b>'; // a href, username, /a

// Tracker stats
$lang['TORRENTS_STAT'] = 'Torrents: <b style="color: blue;">%s</b>,&nbsp; total size: <b>%s</b>'; // first %s = number of torrents, second %s is the total size.
$lang['PEERS_STAT'] = 'Peers: <b>%s</b>,&nbsp; seeders: <b class="seedmed">%s</b>,&nbsp; leechers: <b class="leechmed">%s</b>'; // first %s = number of peers, second %s = number of seeders,  third %s = number of leechers.
$lang['SPEED_STAT'] = 'Total speed: <b>%s</b>&nbsp;'; // %s = total speed.

$lang['NO_NEW_POSTS_LAST_VISIT'] = 'No new posts since your last visit';
$lang['NO_NEW_POSTS'] = 'No new posts';
$lang['NEW_POSTS'] = 'New posts';
$lang['NEW_POST'] = 'New post';
$lang['NO_NEW_POSTS_HOT'] = 'No new posts [ Popular ]';
$lang['NEW_POSTS_HOT'] = 'New posts [ Popular ]';
$lang['NO_NEW_POSTS_LOCKED'] = 'Locked';
$lang['NEW_POSTS_LOCKED'] = 'New posts [ Locked ]';
$lang['FORUM_LOCKED_MAIN'] = 'Forum is locked';


//
// Login
//
$lang['ENTER_PASSWORD'] = 'Please enter your username and password to log in.';
$lang['LOGIN'] = 'Log in';
$lang['LOGOUT'] = 'Log out';
$lang['CONFIRM_LOGOUT'] = 'Are you sure you want to log out?';

$lang['FORGOTTEN_PASSWORD'] = 'Forgot password?';
$lang['AUTO_LOGIN'] = 'Log me on automatically each visit';
$lang['ERROR_LOGIN'] = 'You have specified an incorrect or inactive username, or an invalid password.';
$lang['REMEMBER'] = 'Remember';
$lang['USER_WELCOME'] = 'Welcome,';

//
// Index page
//
$lang['INDEX'] = 'Index';
$lang['HOME'] = 'Home';
$lang['NO_POSTS'] = 'No Posts';
$lang['NO_FORUMS'] = 'This board has no forums';

$lang['PRIVATE_MESSAGE'] = 'Private Message';
$lang['PRIVATE_MESSAGES'] = 'Private Messages';
$lang['WHOSONLINE'] = 'Who is Online';

$lang['MARK_ALL_FORUMS_READ'] = 'Mark all forums read';
$lang['FORUMS_MARKED_READ'] = 'All forums have been marked read';

$lang['LATEST_NEWS'] = 'Latest news';
$lang['SUBFORUMS'] = 'Subforums';

//
// Viewforum
//
$lang['VIEW_FORUM'] = 'View Forum';

$lang['FORUM_NOT_EXIST'] = 'The forum you selected does not exist.';
$lang['REACHED_ON_ERROR'] = 'You have reached this page in error.';

$lang['DISPLAY_TOPICS'] = 'Display topics from previous';
$lang['ALL_TOPICS'] = 'All Topics';
$lang['TOPICS_PER_PAGE'] = 'topics per page';
$lang['MODERATE_FORUM'] = 'Moderate this forum';
$lang['TITLE_SEARCH_HINT'] = 'search title...';

$lang['TOPIC_ANNOUNCEMENT'] = 'Announcement:';
$lang['TOPIC_STICKY'] = 'Sticky:';
$lang['TOPIC_MOVED'] = 'Moved:';
$lang['TOPIC_POLL'] = '[ Poll ]';

$lang['MARK_TOPICS_READ'] = 'Mark all topics read';
$lang['TOPICS_MARKED_READ'] = 'The topics for this forum have now been marked read';

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


//
// Viewtopic
//
$lang['VIEW_TOPIC'] = 'View topic';

$lang['GUEST'] = 'Guest';
$lang['POST_SUBJECT'] = 'Post subject';
$lang['SUBMIT_VOTE'] = 'Submit Vote';
$lang['VIEW_RESULTS'] = 'View Results';

$lang['NO_NEWER_TOPICS'] = 'There are no newer topics in this forum';
$lang['NO_OLDER_TOPICS'] = 'There are no older topics in this forum';
$lang['TOPIC_POST_NOT_EXIST'] = 'The topic or post you requested does not exist';
$lang['NO_POSTS_TOPIC'] = 'No posts exist for this topic';

$lang['DISPLAY_POSTS'] = 'Display posts from previous';
$lang['ALL_POSTS'] = 'All Posts';
$lang['NEWEST_FIRST'] = 'Newest First';
$lang['OLDEST_FIRST'] = 'Oldest First';

$lang['BACK_TO_TOP'] = 'Back to top';

$lang['READ_PROFILE'] = 'View user\'s profile';
$lang['VISIT_WEBSITE'] = 'Visit poster\'s website';
$lang['VIEW_IP'] = 'View IP address of poster';
$lang['MODERATE_POST'] = 'Moderate posts';
$lang['DELETE_POST'] = 'Delete this post';

$lang['WROTE'] = 'wrote'; // proceeds the username and is followed by the quoted text
$lang['QUOTE'] = 'Quote'; // comes before bbcode quote output.
$lang['CODE'] = 'Code'; // comes before bbcode code output.
$lang['CODE_COPIED'] = 'Code copied to clipboard';
$lang['CODE_COPY'] = 'copy to clipboard';
$lang['SPOILER_HEAD'] = 'hidden text';

$lang['EDITED_TIME_TOTAL'] = 'Last edited by %s on %s; edited %d time in total'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['EDITED_TIMES_TOTAL'] = 'Last edited by %s on %s; edited %d times in total'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['LOCK_TOPIC'] = 'Lock this topic';
$lang['UNLOCK_TOPIC'] = 'Unlock this topic';
$lang['MOVE_TOPIC'] = 'Move this topic';
$lang['DELETE_TOPIC'] = 'Delete this topic';
$lang['SPLIT_TOPIC'] = 'Split this topic';

$lang['STOP_WATCHING_TOPIC'] = 'Stop watching this topic';
$lang['START_WATCHING_TOPIC'] = 'Watch this topic for replies';
$lang['NO_LONGER_WATCHING'] = 'You are no longer watching this topic';
$lang['YOU_ARE_WATCHING'] = 'You are now watching this topic';

$lang['TOTAL_VOTES'] = 'Total Votes';
$lang['SEARCH_IN_TOPIC'] = 'search in topic...';
$lang['HIDE_IN_TOPIC'] = 'Hide';

$lang['FLAGS'] = 'flags';
$lang['AVATARS'] = 'avatars';
$lang['RANK_IMAGES'] = 'rank images';
$lang['POST_IMAGES'] = 'post images';
$lang['SMILIES'] = 'smilies';
$lang['SIGNATURES'] = 'signatures';
$lang['SPOILER'] = 'Spoiler';
$lang['SHOW_OPENED'] = 'show opened';

$lang['MODERATE_TOPIC'] = 'Moderate this topic';
$lang['SELECT_POSTS_PER_PAGE'] = 'posts per page';

//
// Posting/Replying (Not private messaging!)
//
$lang['MESSAGE_BODY'] = 'Message body';
$lang['TOPIC_REVIEW'] = 'Topic review';

$lang['NO_POST_MODE'] = 'No post mode specified'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['POST_A_NEW_TOPIC'] = 'Post a new topic';
$lang['POST_A_REPLY'] = 'Post a reply';
$lang['POST_TOPIC_AS'] = 'Post topic as';
$lang['EDIT_POST'] = 'Edit post';
$lang['OPTIONS'] = 'Options';

$lang['POST_ANNOUNCEMENT'] = 'Announcement';
$lang['POST_STICKY'] = 'Sticky';
$lang['POST_NORMAL'] = 'Normal';
$lang['POST_DOWNLOAD'] = 'Download';

$lang['CONFIRM_DELETE'] = 'Are you sure you want to delete this post?';
$lang['CONFIRM_DELETE_POLL'] = 'Are you sure you want to delete this poll?';

$lang['FLOOD_ERROR'] = 'You cannot make another post so soon after your last; please try again in a short while.';
$lang['EMPTY_SUBJECT'] = 'You must specify a subject when posting a new topic.';
$lang['EMPTY_MESSAGE'] = 'You must enter a message when posting.';
$lang['FORUM_LOCKED'] = 'This forum is locked: you cannot post, reply to, or edit topics.';
$lang['TOPIC_LOCKED'] = 'This topic is locked: you cannot edit posts or make replies.';
$lang['TOPIC_LOCKED_SHORT'] = 'Topic locked';
$lang['NO_POST_ID'] = 'You must select a post to edit';
$lang['NO_TOPIC_ID'] = 'You must select a topic to reply to';
$lang['NO_VALID_MODE'] = 'You can only post, reply, edit, or quote messages. Please return and try again.';
$lang['NO_SUCH_POST'] = 'There is no such post. Please return and try again.';
$lang['EDIT_OWN_POSTS'] = 'Sorry, but you can only edit your own posts.';
$lang['DELETE_OWN_POSTS'] = 'Sorry, but you can only delete your own posts.';
$lang['CANNOT_DELETE_REPLIED'] = 'Sorry, but you may not delete posts that have been replied to.';
$lang['CANNOT_DELETE_POLL'] = 'Sorry, but you cannot delete an active poll.';
$lang['EMPTY_POLL_TITLE'] = 'You must enter a title for your poll.';
$lang['TO_FEW_POLL_OPTIONS'] = 'You must enter at least two poll options.';
$lang['TO_MANY_POLL_OPTIONS'] = 'You have tried to enter too many poll options.';
$lang['POST_HAS_NO_POLL'] = 'This post has no poll.';
$lang['ALREADY_VOTED'] = 'You have already voted in this poll.';
$lang['NO_VOTE_OPTION'] = 'You must specify an option when voting.';
$lang['LOCKED_WARN'] = 'You posted into locked topic!';

$lang['ADD_POLL'] = 'Add a Poll';
$lang['ADD_POLL_EXPLAIN'] = 'If you do not want to add a poll to your topic, leave the fields blank.';
$lang['POLL_QUESTION'] = 'Poll question';
$lang['POLL_OPTION'] = 'Poll option';
$lang['ADD_OPTION'] = 'Add option';
$lang['UPDATE'] = 'Update';
$lang['DELETE'] = 'Delete';
$lang['POLL_FOR'] = 'Run poll for';
$lang['DAYS'] = 'Days';
$lang['POLL_FOR_EXPLAIN'] = '[ Enter 0 or leave blank for a never-ending poll ]';
$lang['DELETE_POLL'] = 'Delete Poll';

$lang['DISABLE_BBCODE_POST'] = 'Disable BBCode in this post';
$lang['DISABLE_SMILIES_POST'] = 'Disable Smilies in this post';

$lang['BBCODE_IS_ON'] = '%sBBCode%s is <u>ON</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCODE_IS_OFF'] = '%sBBCode%s is <u>OFF</u>';
$lang['SMILIES_ARE_ON'] = 'Smilies are <u>ON</u>';
$lang['SMILIES_ARE_OFF'] = 'Smilies are <u>OFF</u>';

$lang['ATTACH_SIGNATURE'] = 'Attach signature (signatures can be changed in profile)';
$lang['NOTIFY'] = 'Notify me when a reply is posted';

$lang['STORED'] = 'Your message has been entered successfully.';
$lang['DELETED'] = 'Your message has been deleted successfully.';
$lang['POLL_DELETE'] = 'Your poll has been deleted successfully.';
$lang['VOTE_CAST'] = 'Your vote has been cast.';

$lang['TOPIC_REPLY_NOTIFICATION'] = 'Topic Reply Notification';

$lang['BBCODE_B_HELP'] = 'Bold text: [b]text[/b]  (alt+b)';
$lang['BBCODE_I_HELP'] = 'Italic text: [i]text[/i]  (alt+i)';
$lang['BBCODE_U_HELP'] = 'Underline text: [u]text[/u]  (alt+u)';
$lang['BBCODE_Q_HELP'] = 'Quote text: [quote]text[/quote]  (alt+q)';
$lang['BBCODE_C_HELP'] = 'Code display: [code]code[/code]  (alt+c)';
$lang['BBCODE_L_HELP'] = 'List: [list]text[/list] (alt+l)';
$lang['BBCODE_O_HELP'] = 'Ordered list: [list=]text[/list]  (alt+o)';
$lang['BBCODE_P_HELP'] = 'Insert image: [img]http://image_url[/img]  (alt+p)';
$lang['BBCODE_W_HELP'] = 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)';
$lang['BBCODE_A_HELP'] = 'Close all open bbCode tags';
$lang['BBCODE_S_HELP'] = 'Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000';
$lang['BBCODE_F_HELP'] = 'Font size: [size=x-small]small text[/size]';

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

//
// Private Messaging
//
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

$lang['DISPLAY_MESSAGES'] = 'Display messages from previous'; // Followed by number of days/weeks/months
$lang['ALL_MESSAGES'] = 'All Messages';

$lang['NO_MESSAGES_FOLDER'] = 'You have no messages in this folder';

$lang['PM_DISABLED'] = 'Private messaging has been disabled on this board.';
$lang['CANNOT_SEND_PRIVMSG'] = 'Sorry, but the administrator has prevented you from sending private messages.';
$lang['NO_TO_USER'] = 'You must specify a username to whom to send this message.';
$lang['NO_SUCH_USER'] = 'Sorry, but no such user exists.';

$lang['GALLERY_DISABLE'] = 'Gallery disable';

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

$lang['NOTIFICATION_SUBJECT'] = 'New Private Message has arrived!';

$lang['FIND_USERNAME'] = 'Find a username';
$lang['SELECT_USERNAME'] = 'Select a Username';
$lang['FIND'] = 'Find';
$lang['NO_MATCH'] = 'No matches found.';

$lang['NO_POST_ID'] = 'No post ID was specified';
$lang['NO_SUCH_FOLDER'] = 'No such folder exists';
$lang['NO_FOLDER'] = 'No folder specified';

$lang['MARK_ALL'] = 'Mark all';
$lang['UNMARK_ALL'] = 'Unmark all';

$lang['CONFIRM_DELETE_PM'] = 'Are you sure you want to delete this message?';
$lang['CONFIRM_DELETE_PMS'] = 'Are you sure you want to delete these messages?';

$lang['INBOX_SIZE'] = 'Your Inbox is<br /><b>%d%%</b> full'; // eg. Your Inbox is 50% full
$lang['SENTBOX_SIZE'] = 'Your Sentbox is<br /><b>%d%%</b> full';
$lang['SAVEBOX_SIZE'] = 'Your Savebox is<br /><b>%d%%</b> full';

$lang['CLICK_VIEW_PRIVMSG'] = 'Click %sHere%s to visit your Inbox';

$lang['OUTBOX_EXPL'] = '';

//
// Profiles/Registration
//
$lang['VIEWING_USER_PROFILE'] = 'Viewing profile :: %s'; // %s is username
$lang['ABOUT_USER'] = 'All about %s'; // %s is username

$lang['DISABLED_USER'] = 'Account disabled';
$lang['MANAGE_USER'] = 'Administration';

$lang['PREFERENCES'] = 'Preferences';
$lang['ITEMS_REQUIRED'] = 'Items marked with a * are required unless stated otherwise.';
$lang['REGISTRATION_INFO'] = 'Registration Information';
$lang['PROFILE_INFO'] = 'Profile Information';
$lang['PROFILE_INFO_WARN'] = 'This information will be publicly viewable';
$lang['AVATAR_PANEL'] = 'Avatar control panel';
$lang['AVATAR_GALLERY'] = 'Avatar gallery';

$lang['WEBSITE'] = 'Website';
$lang['LOCATION'] = 'Location';
$lang['CONTACT'] = 'Contact';
$lang['EMAIL_ADDRESS'] = 'E-mail address';
$lang['SEND_PRIVATE_MESSAGE'] = 'Send private message';
$lang['HIDDEN_EMAIL'] = '[ Hidden ]';
$lang['INTERESTS'] = 'Interests';
$lang['OCCUPATION'] = 'Occupation';
$lang['POSTER_RANK'] = 'Poster rank';

$lang['TOTAL_POSTS'] = 'Total posts';
$lang['USER_POST_PCT_STATS'] = '%.2f%% of total'; // 1.25% of total
$lang['USER_POST_DAY_STATS'] = '%.2f posts per day'; // 1.5 posts per day
$lang['SEARCH_USER_POSTS'] = 'Find posts by %s'; // Find all posts by username
$lang['SEARCH_USER_POSTS_SHORT'] = 'Find user posts';
$lang['SEARCH_USER_TOPICS'] = 'Find user topics'; // Find all topics by username

$lang['NO_USER_ID_SPECIFIED'] = 'Sorry, but that user does not exist.';
$lang['WRONG_PROFILE'] = 'You cannot modify a profile that is not your own.';

$lang['ONLY_ONE_AVATAR'] = 'Only one type of avatar can be specified';
$lang['FILE_NO_DATA'] = 'The file at the URL you gave contains no data';
$lang['NO_CONNECTION_URL'] = 'A connection could not be made to the URL you gave';
$lang['INCOMPLETE_URL'] = 'The URL you entered is incomplete';
$lang['WRONG_REMOTE_AVATAR_FORMAT'] = 'The URL of the remote avatar is not valid';
$lang['NO_SEND_ACCOUNT_INACTIVE'] = 'Sorry, but your password cannot be retrieved because your account is currently inactive';
$lang['NO_SEND_ACCOUNT'] = 'Sorry, but your password cannot be retrieved. Please contact the forum administrator for more information';

$lang['ALWAYS_ADD_SIG'] = 'Always attach my signature';
$lang['HIDE_PORN_FORUMS'] = 'Hide porno forums';
$lang['ALWAYS_NOTIFY'] = 'Always notify me of replies';
$lang['ALWAYS_NOTIFY_EXPLAIN'] = 'Sends an e-mail when someone replies to a topic you have posted in. This can be changed whenever you post.';

$lang['BOARD_STYLE'] = 'Board Style';
$lang['BOARD_LANG'] = 'Board Language';
$lang['NO_THEMES'] = 'No Themes In database';
$lang['TIMEZONE'] = 'Timezone';
$lang['DATE_FORMAT_PROFILE'] = 'Date format';
$lang['DATE_FORMAT_EXPLAIN'] = 'The syntax used is identical to the PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> function.';
$lang['SIGNATURE'] = 'Signature';
$lang['SIGNATURE_EXPLAIN'] = 'This is a block of text that can be added to posts you make. There is a %d character limit';
$lang['PUBLIC_VIEW_EMAIL'] = 'Always show my e-mail address';

$lang['CURRENT_PASSWORD'] = 'Current password';
$lang['NEW_PASSWORD'] = 'New password';
$lang['CONFIRM_PASSWORD'] = 'Confirm password';
$lang['CONFIRM_PASSWORD_EXPLAIN'] = 'You must confirm your current password if you wish to change it or alter your e-mail address';
$lang['PASSWORD_IF_CHANGED'] = 'You only need to supply a password if you want to change it';
$lang['PASSWORD_CONFIRM_IF_CHANGED'] = 'You only need to confirm your password if you changed it above';

$lang['AUTOLOGIN'] = 'Autologin';
$lang['RESET_AUTOLOGIN'] = 'Reset autologin key';
$lang['RESET_AUTOLOGIN_EXPL'] = '';

$lang['AVATAR'] = 'Avatar';
$lang['AVATAR_EXPLAIN'] = 'Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than %d pixels, the height no greater than %d pixels, and the file size no more than %d KB.';
$lang['UPLOAD_AVATAR_FILE'] = 'Upload Avatar from your machine';
$lang['UPLOAD_AVATAR_URL'] = 'Upload Avatar from a URL';
$lang['UPLOAD_AVATAR_URL_EXPLAIN'] = 'Enter the URL of the location containing the Avatar image, it will be copied to this site.';
$lang['PICK_LOCAL_AVATAR'] = 'Select Avatar from the gallery';
$lang['LINK_REMOTE_AVATAR'] = 'Link to off-site Avatar';
$lang['LINK_REMOTE_AVATAR_EXPLAIN'] = 'Enter the URL of the location containing the Avatar image you wish to link to.';
$lang['AVATAR_URL'] = 'URL of Avatar Image';
$lang['SELECT_FROM_GALLERY'] = 'Select Avatar from gallery';
$lang['VIEW_AVATAR_GALLERY'] = 'Show gallery';

$lang['SELECT_AVATAR'] = 'Select avatar';
$lang['RETURN_PROFILE'] = 'Cancel avatar';
$lang['SELECT_CATEGORY'] = 'Select category';

$lang['DELETE_IMAGE'] = 'Delete Image';
$lang['CURRENT_IMAGE'] = 'Current Image';

$lang['NOTIFY_ON_PRIVMSG'] = 'Notify on new Private Message';
$lang['HIDE_USER'] = 'Hide your online status';

$lang['PROFILE_UPDATED'] = 'Your profile has been updated';
$lang['PROFILE_UPDATED_INACTIVE'] = 'Your profile has been updated. However, you have changed vital details, thus your account is now inactive. Check your e-mail to find out how to reactivate your account, or if admin activation is required, wait for the administrator to reactivate it.';

$lang['PASSWORD_MISMATCH'] = 'The passwords you entered did not match.';
$lang['CURRENT_PASSWORD_MISMATCH'] = 'The current password you supplied does not match that stored in the database.';
$lang['PASSWORD_LONG'] = 'Your password must be no more than 32 characters.';
$lang['TOO_MANY_REGISTERS'] = 'You have made too many registration attempts. Please try again later.';
$lang['USERNAME_TAKEN'] = 'Sorry, but this username has already been taken.';
$lang['USERNAME_INVALID'] = 'Sorry, but this username contains an invalid character such as \'.';
$lang['USERNAME_DISALLOWED'] = 'Sorry, but this username has been disallowed.';
$lang['EMAIL_TAKEN'] = 'Sorry, but that e-mail address is already registered to a user.';
$lang['EMAIL_BANNED'] = 'Sorry, but <b>%s</b> address has been banned.';
$lang['EMAIL_INVALID'] = 'Sorry, but this e-mail address is invalid.';
$lang['SIGNATURE_TOO_LONG'] = 'Your signature is too long.';
$lang['FIELDS_EMPTY'] = 'You must fill in the required fields.';
$lang['AVATAR_FILETYPE'] = 'The avatar filetype must be .jpg, .gif or .png';
$lang['AVATAR_FILESIZE'] = 'The avatar image file size must be less than %d KB'; // The avatar image file size must be less than 6 KB
$lang['AVATAR_IMAGESIZE'] = 'The avatar must be less than %d pixels wide and %d pixels high';

$lang['WELCOME_SUBJECT'] = 'Welcome to %s Forums'; // Welcome to my.com forums
$lang['NEW_ACCOUNT_SUBJECT'] = 'New user account';
$lang['ACCOUNT_ACTIVATED_SUBJECT'] = 'Account Activated';

$lang['ACCOUNT_ADDED'] = 'Thank you for registering. Your account has been created. You may now log in with your username and password';
$lang['ACCOUNT_INACTIVE'] = 'Your account has been created. However, this forum requires account activation. An activation key has been sent to the e-mail address you provided. Please check your e-mail for further information';
$lang['ACCOUNT_INACTIVE_ADMIN'] = 'Your account has been created. However, this forum requires account activation by the administrator. An e-mail has been sent to them and you will be informed when your account has been activated';
$lang['ACCOUNT_ACTIVE'] = 'Your account has now been activated. Thank you for registering';
$lang['ACCOUNT_ACTIVE_ADMIN'] = 'The account has now been activated';
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
$lang['CC_EMAIL'] = 'Send a copy of this e-mail to yourself';
$lang['EMAIL_MESSAGE_DESC'] = 'This message will be sent as plain text, so do not include any HTML or BBCode. The return address for this message will be set to your e-mail address.';
$lang['FLOOD_EMAIL_LIMIT'] = 'You cannot send another e-mail at this time. Try again later.';
$lang['RECIPIENT'] = 'Recipient';
$lang['EMAIL_SENT'] = 'The e-mail has been sent.';
$lang['SEND_EMAIL'] = 'Send e-mail';
$lang['EMPTY_SUBJECT_EMAIL'] = 'You must specify a subject for the e-mail.';
$lang['EMPTY_MESSAGE_EMAIL'] = 'You must enter a message to be e-mailed.';

$lang['USER_AGREEMENT'] = 'User Agreement';
$lang['USER_AGREEMENT_HEAD'] = 'In order to proceed, you must agree with the following rules';
$lang['USER_AGREEMENT_AGREE'] = 'I have read and agree to the User Agreement above';

$lang['COPYRIGHT_HOLDERS'] = 'For Copyright Holders';
$lang['ADVERT'] = 'Advertise on this site';

//
// Visual confirmation system strings
//
$lang['CONFIRM_CODE_WRONG'] = 'The confirmation code you entered was incorrect';
$lang['TOO_MANY_REGISTERS'] = 'You have exceeded the number of registration attempts for this session. Please try again later.';
$lang['CONFIRM_CODE_IMPAIRED'] = 'If you are visually impaired or cannot otherwise read this code please contact the %sAdministrator%s for help.';
$lang['CONFIRM_CODE'] = 'Confirmation code';
$lang['CONFIRM_CODE_EXPLAIN'] = 'Enter the code exactly as you see it. The code is case sensitive and zero has a diagonal line through it.';



//
// Memberslist
//
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


//
// Group control panel
//
$lang['GROUP_CONTROL_PANEL'] = 'User Groups';
$lang['MEMBERSHIP_DETAILS'] = 'Group Membership Details';
$lang['JOIN_A_GROUP'] = 'Join a Group';

$lang['GROUP_INFORMATION'] = 'Group Information';
$lang['GROUP_NAME'] = 'Group name';
$lang['GROUP_DESCRIPTION'] = 'Group description';
$lang['GROUP_MEMBERSHIP'] = 'Group membership';
$lang['GROUP_MEMBERS'] = 'Group Members';
$lang['GROUP_MODERATOR'] = 'Group Moderator';
$lang['PENDING_MEMBERS'] = 'Pending Members';

$lang['GROUP_TYPE'] = 'Group type';
$lang['GROUP_OPEN'] = 'Open group';
$lang['GROUP_CLOSED'] = 'Closed group';
$lang['GROUP_HIDDEN'] = 'Hidden group';

$lang["GROUP_MEMBER_MOD"] = 'Group moderator';
$lang["GROUP_MEMBER_MEMBER"] = 'Current memberships';
$lang["GROUP_MEMBER_PENDING"] = 'Memberships pending';
$lang["GROUP_MEMBER_OPEN"] = 'Open groups';
$lang["GROUP_MEMBER_CLOSED"] = 'Closed groups';
$lang["GROUP_MEMBER_HIDDEN"] = 'Hidden groups';

$lang['NO_GROUPS_EXIST'] = 'No Groups Exist';
$lang['GROUP_NOT_EXIST'] = 'That user group does not exist';

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


//
// Search
//
$lang['SEARCH_QUERY'] = 'Search Query';
$lang['SEARCH_OPTIONS'] = 'Search Options';

$lang['SEARCH_WORDS'] = 'Search for Keywords';
$lang['SEARCH_WORDS_EXPL'] = 'You can use <b>+</b> to define words which must be in the results and <b>-</b> to define words which should not be in the result (ex: "+word1 -word2"). Use * as a wildcard for partial matches';
$lang['SEARCH_AUTHOR'] = 'Search for Author';
$lang['SEARCH_AUTHOR_EXPL'] = 'Use * as a wildcard for partial matches';

$lang['SEARCH_TITLES_ONLY'] = 'Search topic titles only';
$lang['SEARCH_ALL_WORDS'] = 'all words';
$lang['IN_MY_POSTS']  = 'In my posts';
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

//
// Auth related entries
//
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


//
// Viewonline
//
$lang['REG_USERS_ZERO_ONLINE'] = 'There are 0 Registered users and '; // There are 5 Registered and
$lang['REG_USERS_ONLINE'] = 'There are %d Registered users and '; // There are 5 Registered and
$lang['REG_USER_ONLINE'] = 'There is %d Registered user and '; // There is 1 Registered and
$lang['HIDDEN_USERS_ZERO_ONLINE'] = '0 Hidden users online'; // 6 Hidden users online
$lang['HIDDEN_USERS_ONLINE'] = '%d Hidden users online'; // 6 Hidden users online
$lang['HIDDEN_USER_ONLINE'] = '%d Hidden user online'; // 6 Hidden users online
$lang['GUEST_USERS_ONLINE'] = 'There are %d Guest users online'; // There are 10 Guest users online
$lang['GUEST_USERS_ZERO_ONLINE'] = 'There are 0 Guest users online'; // There are 10 Guest users online
$lang['GUEST_USER_ONLINE'] = 'There is %d Guest user online'; // There is 1 Guest user online
$lang['NO_USERS_BROWSING'] = 'There are no users currently browsing this forum';

$lang['ONLINE_EXPLAIN'] = 'users active over the past five minutes';
$lang['LAST_UPDATED'] = 'Last Updated';

//
// Moderator Control Panel
//
$lang['MOD_CP'] = 'Moderator Control Panel';
$lang['MOD_CP_EXPLAIN'] = 'Using the form below you can perform mass moderation operations on this forum. You can lock, unlock, move or delete any number of topics.';

$lang['SELECT'] = 'Select';
$lang['DELETE'] = 'Delete';
$lang['MOVE'] = 'Move';
$lang['LOCK'] = 'Lock';
$lang['UNLOCK'] = 'Unlock';

$lang['TOPICS_REMOVED'] = 'The selected topics have been successfully removed from the database.';
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

$lang['SPLIT_TOPIC'] = 'Split Topic Control Panel';
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


//
// Timezones ... for display on each page
//
$lang['ALL_TIMES'] = 'All times are <span class="tz_time">%s</span>'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Hours';
$lang['-11'] = 'GMT - 11 Hours';
$lang['-10'] = 'GMT - 10 Hours';
$lang['-9'] = 'GMT - 9 Hours';
$lang['-8'] = 'GMT - 8 Hours';
$lang['-7'] = 'GMT - 7 Hours';
$lang['-6'] = 'GMT - 6 Hours';
$lang['-5'] = 'GMT - 5 Hours';
$lang['-4'] = 'GMT - 4 Hours';
$lang['-3.5'] = 'GMT - 3.5 Hours';
$lang['-3'] = 'GMT - 3 Hours';
$lang['-2'] = 'GMT - 2 Hours';
$lang['-1'] = 'GMT - 1 Hours';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Hour';
$lang['2'] = 'GMT + 2 Hours';
$lang['3'] = 'GMT + 3 Hours';
$lang['3.5'] = 'GMT + 3.5 Hours';
$lang['4'] = 'GMT + 4 Hours';
$lang['4.5'] = 'GMT + 4.5 Hours';
$lang['5'] = 'GMT + 5 Hours';
$lang['5.5'] = 'GMT + 5.5 Hours';
$lang['6'] = 'GMT + 6 Hours';
$lang['6.5'] = 'GMT + 6.5 Hours';
$lang['7'] = 'GMT + 7 Hours';
$lang['8'] = 'GMT + 8 Hours';
$lang['9'] = 'GMT + 9 Hours';
$lang['9.5'] = 'GMT + 9.5 Hours';
$lang['10'] = 'GMT + 10 Hours';
$lang['11'] = 'GMT + 11 Hours';
$lang['12'] = 'GMT + 12 Hours';
$lang['13'] = 'GMT + 13 Hours';

// These are displayed in the timezone select box
$lang['TZ']['-12'] = 'GMT - 12 Hours';
$lang['TZ']['-11'] = 'GMT - 11 Hours';
$lang['TZ']['-10'] = 'GMT - 10 Hours';
$lang['TZ']['-9'] = 'GMT - 9 Hours';
$lang['TZ']['-8'] = 'GMT - 8 Hours';
$lang['TZ']['-7'] = 'GMT - 7 Hours';
$lang['TZ']['-6'] = 'GMT - 6 Hours';
$lang['TZ']['-5'] = 'GMT - 5 Hours';
$lang['TZ']['-4'] = 'GMT - 4 Hours';
$lang['TZ']['-3.5'] = 'GMT - 3.5 Hours';
$lang['TZ']['-3'] = 'GMT - 3 Hours';
$lang['TZ']['-2'] = 'GMT - 2 Hours';
$lang['TZ']['-1'] = 'GMT - 1 Hours';
$lang['TZ']['0'] = 'GMT';
$lang['TZ']['1'] = 'GMT + 1 Hour';
$lang['TZ']['2'] = 'GMT + 2 Hours';
$lang['TZ']['3'] = 'GMT + 3 Hours';
$lang['TZ']['3.5'] = 'GMT + 3.5 Hours';
$lang['TZ']['4'] = 'GMT + 4 Hours';
$lang['TZ']['4.5'] = 'GMT + 4.5 Hours';
$lang['TZ']['5'] = 'GMT + 5 Hours';
$lang['TZ']['5.5'] = 'GMT + 5.5 Hours';
$lang['TZ']['6'] = 'GMT + 6 Hours';
$lang['TZ']['6.5'] = 'GMT + 6.5 Hours';
$lang['TZ']['7'] = 'GMT + 7 Hours';
$lang['TZ']['8'] = 'GMT + 8 Hours';
$lang['TZ']['9'] = 'GMT + 9 Hours';
$lang['TZ']['9.5'] = 'GMT + 9.5 Hours';
$lang['TZ']['10'] = 'GMT + 10 Hours';
$lang['TZ']['11'] = 'GMT + 11 Hours';
$lang['TZ']['12'] = 'GMT + 12 Hours';
$lang['TZ']['13'] = 'GMT + 13 Hours';

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

//
// Errors (not related to a
// specific failure on a page)
//
$lang['INFORMATION'] = 'Information';
$lang['CRITICAL_INFORMATION'] = 'Critical Information';

$lang['GENERAL_ERROR'] = 'Error';
$lang['CRITICAL_ERROR'] = 'Critical Error';
$lang['AN_ERROR_OCCURED'] = 'An Error Occurred';
$lang['A_CRITICAL_ERROR'] = 'A Critical Error Occurred';

$lang['ADMIN_REAUTHENTICATE'] = 'To administer/moderate the board you must re-authenticate yourself.';
$lang['LOGIN_ATTEMPTS_EXCEEDED'] = 'The maximum number of %s login attempts has been exceeded. You are not allowed to login for the next %s minutes.';

//
// Attachment Mod Main Language Variables
//

// Auth Related Entries
$lang['RULES_ATTACH_CAN'] = 'You <b>can</b> attach files in this forum';
$lang['RULES_ATTACH_CANNOT'] = 'You <b>cannot</b> attach files in this forum';
$lang['RULES_DOWNLOAD_CAN'] = 'You <b>can</b> download files in this forum';
$lang['RULES_DOWNLOAD_CANNOT'] = 'You <b>cannot</b> download files in this forum';
$lang['SORRY_AUTH_VIEW_ATTACH'] = 'Sorry but you are not authorized to view or download this Attachment';

// Viewtopic -> Display of Attachments
$lang['DESCRIPTION'] = 'Description'; // used in Administration Panel too...
$lang['DOWNLOAD'] = 'Download'; // this Language Variable is defined in lang_admin.php too, but we are unable to access it from the main Language File
$lang['FILESIZE'] = 'Filesize';
$lang['VIEWED'] = 'Viewed';
$lang['DOWNLOAD_NUMBER'] = '%d times'; // replace %d with count
$lang['EXTENSION_DISABLED_AFTER_POSTING'] = 'The Extension \'%s\' was deactivated by an board admin, therefore this Attachment is not displayed.'; // used in Posts and PM's, replace %s with mime type

// Posting/PM -> Posting Attachments
$lang['ADD_ATTACHMENT'] = 'Add Attachment';
$lang['ADD_ATTACHMENT_TITLE'] = 'Add an Attachment';
$lang['ADD_ATTACHMENT_EXPLAIN'] = 'If you do not want to add an Attachment to your Post, please leave the Fields blank';
$lang['FILE_NAME'] = 'Filename';
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
$lang['ATTACHMENT_PHP_SIZE_NA'] = 'The Attachment is too big.<br />Couldn\'t get the maximum Size defined in PHP.<br />The Attachment Mod is unable to determine the maximum Upload Size defined in the php.ini.';
$lang['ATTACHMENT_PHP_SIZE_OVERRUN'] = 'The Attachment is too big.<br />Maximum Upload Size: %d MB.<br />Please note that this Size is defined in php.ini, this means it\'s set by PHP and the Attachment Mod can not override this value.'; // replace %d with ini_get('upload_max_filesize')
$lang['DISALLOWED_EXTENSION'] = 'The Extension %s is not allowed'; // replace %s with extension (e.g. .php)
$lang['DISALLOWED_EXTENSION_WITHIN_FORUM'] = 'You are not allowed to post Files with the Extension %s within this Forum'; // replace %s with the Extension
$lang['ATTACHMENT_TOO_BIG'] = 'The Attachment is too big.<br />Max Size: %d %s'; // replace %d with maximum file size, %s with size var
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

$lang['DIRECTORY_DOES_NOT_EXIST'] = 'The Directory \'%s\' does not exist or couldn\'t be found.'; // replace %s with directory
$lang['DIRECTORY_IS_NOT_A_DIR'] = 'Please check if \'%s\' is a directory.'; // replace %s with directory
$lang['DIRECTORY_NOT_WRITEABLE'] = 'Directory \'%s\' is not writeable. You\'ll have to create the upload path and chmod it to 777 (or change the owner to you httpd-servers owner) to upload files.<br />If you have only plain ftp-access change the \'Attribute\' of the directory to rwxrwxrwx.'; // replace %s with directory

$lang['FTP_ERROR_CONNECT'] = 'Could not connect to FTP Server: \'%s\'. Please check your FTP-Settings.';
$lang['FTP_ERROR_LOGIN'] = 'Could not login to FTP Server. The Username \'%s\' or the Password is wrong. Please check your FTP-Settings.';
$lang['FTP_ERROR_PATH'] = 'Could not access ftp directory: \'%s\'. Please check your FTP Settings.';
$lang['FTP_ERROR_UPLOAD'] = 'Could not upload files to ftp directory: \'%s\'. Please check your FTP Settings.';
$lang['FTP_ERROR_DELETE'] = 'Could not delete files in ftp directory: \'%s\'. Please check your FTP Settings.<br />Another reason for this error could be the non-existence of the Attachment, please check this first in Shadow Attachments.';
$lang['FTP_ERROR_PASV_MODE'] = 'Unable to enable/disable FTP Passive Mode';

// Attach Rules Window
$lang['RULES_PAGE'] = 'Attachment Rules';
$lang['ATTACH_RULES_TITLE'] = 'Allowed Extension Groups and their Sizes';
$lang['GROUP_RULE_HEADER'] = '%s -> Maximum Upload Size: %s'; // Replace first %s with Extension Group, second one with the Size STRING
$lang['ALLOWED_EXTENSIONS_AND_SIZES'] = 'Allowed Extensions and Sizes';
$lang['NOTE_USER_EMPTY_GROUP_PERMISSIONS'] = 'NOTE:<br />You are normally allowed to attach files within this Forum, <br />but since no Extension Group is allowed to be attached here, <br />you are unable to attach anything. If you try, <br />you will receive an Error Message.<br />';

// Quota Variables
$lang['UPLOAD_QUOTA'] = 'Upload Quota';
$lang['PM_QUOTA'] = 'PM Quota';
$lang['USER_UPLOAD_QUOTA_REACHED'] = 'Sorry, you have reached your maximum Upload Quota Limit of %d %s'; // replace %d with Size, %s with Size Lang (MB for example)

// User Attachment Control Panel
$lang['USER_ACP_TITLE'] = 'User ACP';
$lang['UACP'] = 'User Attachment Control Panel';
$lang['USER_UPLOADED_PROFILE'] = 'Uploaded: %s';
$lang['USER_QUOTA_PROFILE'] = 'Quota: %s';
$lang['UPLOAD_PERCENT_PROFILE'] = '%d%% of total';

// Common Variables
$lang['BYTES'] = 'Bytes';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';
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
$lang['BT_GEN_PASSKEY'] = 'Passkey';
$lang['BT_GEN_PASSKEY_URL'] = 'Generate or change Passkey';
$lang['BT_GEN_PASSKEY_EXPLAIN'] = 'Generate your personal id for torrent tracker';
$lang['BT_GEN_PASSKEY_EXPLAIN_2'] = "<b>Warning!</b> After generating new id you'll need to <b>redownload all active torrent's!</b>";
$lang['BT_GEN_PASSKEY_OK'] = 'New personal identifier generated';
$lang['BT_NO_SEARCHABLE_FORUMS'] = 'No searchable forums found';

$lang['SEEDERS'] = 'Seeders';
$lang['LEECHERS'] = 'Leechers';
$lang['RELEASING'] = 'Self';
$lang['SEEDING'] = 'Seeding';
$lang['LEECHING'] = 'Leeching';
$lang['IS_REGISTERED'] = 'Registered';
$lang['MAGNET'] = 'Magnet';

//torrent status mod
$lang['TOR_STATUS'] = 'Status';
$lang['TOR_STATUS_SELECT_ACTION'] = 'Select status';
$lang['TOR_STATUS_CHECKED'] = 'checked';//2
$lang['TOR_STATUS_NOT_CHECKED'] = 'not checked';//0
$lang['TOR_STATUS_CLOSED'] = 'closed';//1
$lang['TOR_STATUS_D'] = 'repeat';//3
$lang['TOR_STATUS_NOT_PERFECT'] = 'neoformleno';//4
$lang['TOR_STATUS_PART_PERFECT'] = 'nedooformleno';//5
$lang['TOR_STATUS_FISHILY'] = 'doubtful';//6
$lang['TOR_STATUS_COPY'] = 'closed right';//7
//end torrent status mod

$lang['BT_TOPIC_TITLE'] = 'Topic title';
$lang['BT_SEEDER_LAST_SEEN'] = 'Seed last seen';
$lang['BT_SORT_FORUM'] = 'Forum';
$lang['SIZE'] = 'Size';
$lang['PIECE_LENGTH'] = 'Piece length';
$lang['COMPLETED'] = 'Completed';
$lang['ADDED'] = 'Added';
$lang['DELETE_TORRENT'] = 'Delete torrent';
$lang['DEL_MOVE_TORRENT'] = 'Delete and move topic';
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
$lang['UNSET_SILVER_TORRENT'] = 'UnMake gold';
$lang['GOLD_STATUS'] = 'GOLD TORRENT! DOWNLOAD TRAFFIC DOES NOT CONSIDER!';
$lang['SILVER_STATUS'] = 'SILVER TORRENT! DOWNLOAD TRAFFIC PARTIALLY CONSIDERED!'; 
// End - Gold/Silver releases

$lang['SEARCH_IN_FORUMS'] = 'Search in Forums';
$lang['SELECT_CAT']    = 'Select category';
$lang['GO_TO_SECTION'] = 'Goto section';
$lang['TORRENTS_FROM'] = 'Posts from';
$lang['SHOW_ONLY'] = 'Show only';
$lang['SHOW_COLUMN'] = 'Show column';

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
$lang['BT_1_DAY']    = '1 day';
$lang['BT_3_DAYS']    = '3 days';
$lang['BT_7_DAYS']   = 'week';
$lang['BT_2_WEEKS']  = '2 weeks';
$lang['BT_1_MONTH']  = 'month';

$lang['DL_LIST_AND_TORRENT_ACTIVITY'] = 'DL-List and Torrent activity';
$lang['DL_WILL'] = 'Will download';
$lang['DL_DOWN'] = 'Downloading';
$lang['DL_COMPLETE'] = 'Complete';
$lang['DL_CANCEL'] = 'Cancel';

$lang['DLWILL_2'] = 'Will download';
$lang['DLDOWN_2'] = 'Downloading';
$lang['DLCOMPLETE_2'] = 'Complete';
$lang['DLCANCEL_2'] = 'Cancel';

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
$lang['SEARCH_DL_COMPLETE_DOWNLOADS']   = 'Completed Downloads';
$lang['SEARCH_DL_CANCEL'] = 'Canceled';
$lang['CUR_DOWNLOADS'] = 'Current Downloads';
$lang['CUR_UPLOADS']   = 'Current Uploads';
$lang['SEARCH_USER_RELEASES'] = 'Find all current releases';
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
$lang['CUR_ACTIVE_DLS'] = 'Currently active torrents';
$lang['VIEW_TORRENT_PROFILE'] = 'Torrent-profile';

$lang['PROFILE_UP_TOTAL'] = 'Total uploaded';	
$lang['PROFILE_DOWN_TOTAL'] = 'Total downloaded';
$lang['PROFILE_BONUS'] = 'Bonus';		
$lang['PROFILE_RELEASED'] = 'Total released';
$lang['PROFILE_RATIO'] = 'Ratio';
$lang['PROFILE_MAX_SPEED'] = 'Speed';
$lang['PROFILE_IT_WILL_BE_DOWNLOADED'] = 'it will start to be considered after it will be downloaded';

$lang['CURR_PASSKEY'] = 'Current passkey:';
$lang['SPMODE_FULL'] = 'Show peers in full details';

$lang['BT_RATIO'] = 'Ratio';
$lang['YOUR_RATIO'] = 'Your Ratio';
$lang['DOWNLOADED'] = 'Downloaded';
$lang['UPLOADED'] = 'Uploaded';
$lang['RELEASED'] = 'Released';
$lang['BT_BONUS_UP'] = 'Bonus';

$lang['TRACKER'] = 'Tracker';
$lang['GALLERY'] = 'Gallery';
$lang['OPEN_TOPICS'] = 'Open topics';
$lang['OPEN_IN_SAME_WINDOW'] = 'open in same window';

$lang['BT_LOW_RATIO_FUNC'] = "You can't use this option (ratio is too low)";
$lang['BT_LOW_RATIO_FOR_DL'] = "With ratio <b>%s</b> you can't download torrents";
$lang['BT_RATIO_WARNING_MSG'] = 'If your ratio falls below %s, you will not be able to download Torrents! <a href="%s"><b>More about the rating.</b></a>';

$lang['SEEDER_LAST_SEEN'] = 'Seeder not seen: <b>%s</b>';

//
// MAIL.RU Keyboard
//
$lang['KB_TITLE'] = 'Russian keyboard';
$lang['KB_RUS_KEYLAYOUT'] = 'Layout: ';
$lang['KB_NONE'] = 'None';
$lang['KB_TRANSLIT'] = 'Translit';
$lang['KB_TRADITIONAL'] = 'Traditional';
$lang['KB_RULES'] = 'Using translit';
$lang['KB_SHOW'] = 'Show keyboard (Make sure you\'re using Cyrillic codepage!)';
$lang['KB_ABOUT'] = 'About';
$lang['KB_CLOSE'] = 'Close';
$lang['KB_TRANSLIT_MOZILLA'] = 'Select text you wish to translit and click \'Translit\'.';
$lang['KB_TRANSLIT_OPERA7'] = 'Click here to translit your message.';

$lang['NEED_TO_LOGIN_FIRST'] = 'You need to login first';
$lang['ONLY_FOR_MOD'] = 'This option only for moderators';
$lang['ONLY_FOR_ADMIN'] = 'This option only for admins';
$lang['ONLY_FOR_SUPER_ADMIN'] = 'This option only for super admins';

$lang['ACCESS'] = 'Access';
$lang['ACCESS_SRV_LOAD'] = 'Depend on server load';
$lang['LOGS'] = 'Topic history';

$lang['LAST_IP'] = 'Last IP:';
$lang['REG_IP']  = 'Registration IP:';
$lang['ALREADY_REG']   = 'With your IP-address is already registered user %s. If you have not previously registered on our tracker, mail to <a href="mailto:%s">Administrator</ a>';

//
// That's all, Folks!
// -------------------------------------------------

// from lang_admin
$lang['NOT_ADMIN'] = 'You are not authorised to administer this board';

$lang['COOKIES_REQUIRED'] = 'Cookies must be enabled!';
$lang['SESSION_EXPIRED'] = 'Session expired';

// FLAGHACK-start
$lang['COUNTRY_FLAG'] = 'Country Flag';
$lang['SELECT_COUNTRY'] = 'SELECT COUNTRY' ;
// FLAGHACK-end

// Sort memberlist per letter
$lang['SORT_PER_LETTER'] = 'Show only usernames starting with';
$lang['OTHERS'] = 'others';
$lang['ALL'] = 'all';

$lang['POST_LINK'] = 'Post link';
$lang['LAST_VISITED'] = 'Last Visited';
$lang['LAST_ACTIVITY'] = 'Last activity';
$lang['NEVER'] = 'Never';

//mpd
$lang['DELETE_POSTS'] = 'Delete selected posts';
$lang['DELETE_POSTS_SUCCESFULLY'] = 'The selected posts have been successfully removed';
//mpd end

//ts
$lang['TOPICS_ANNOUNCEMENT'] = 'Announcements';
$lang['TOPICS_STICKY'] = 'Stickies';
$lang['TOPICS_NORMAL'] = 'Topics';
//ts end

//dpc
$lang['DOUBLE_POST_ERROR'] = 'You cannot make another post with the exact same text as your last.';
//dpc end

//upt
$lang['UPDATE_POST_TIME'] = 'Update post time';
//upt end

$lang['TOPIC_SPLIT_NEW'] = 'New topic';
$lang['TOPIC_SPLIT_OLD'] = 'Old topic';
$lang['BOT_LEAVE_MSG_MOVED'] = 'Add bot-message about moving';
$lang['BOT_AFTER_SPLIT_TO_OLD'] = 'Add bot-message about split to <b>old topic</b>';
$lang['BOT_AFTER_SPLIT_TO_NEW'] = 'Add bot-message about split to <b>new topic</b>';
//qr
$lang['QUICK_REPLY'] = 'Quick Reply';
$lang['INS_NAME_TIP'] = 'Insert name or selected text.';
$lang['QUOTE_SELECTED'] = 'Quote selected';
$lang['TRANSLIT_RULES'] = 'Translit Rules';
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
//qr end

//txtb
$lang['ICQ_TXTB'] = '[ICQ]';
$lang['AIM_TXTB'] = '[AIM]';
$lang['MSNM_TXTB'] = '[MSN]';
$lang['YIM_TXTB'] = '[Yahoo]';
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
//txtb end

$lang['DECLENSION']['REPLIES'] = array('reply', 'replies');
$lang['DECLENSION']['TIMES'] = array('time', 'times');

$lang['DELTA_TIME']['INTERVALS'] = array(
	'seconds' => array('second', 'seconds'),
	'minutes' => array('minute', 'minutes'),
	'hours'   => array('hour',   'hours'),
	'mday'    => array('day',    'days'),
	'mon'     => array('month',  'months'),
	'year'    => array('year',   'years'),
);
$lang['DELTA_TIME']['FORMAT'] = '%1$s %2$s';  // 5(%1) minutes(%2)

$lang['AUTH_TYPES'][AUTH_ALL]   = $lang['AUTH_ANONYMOUS_USERS'];
$lang['AUTH_TYPES'][AUTH_REG]   = $lang['AUTH_REGISTERED_USERS'];
$lang['AUTH_TYPES'][AUTH_ACL]   = $lang['AUTH_USERS_GRANTED_ACCESS'];
$lang['AUTH_TYPES'][AUTH_MOD]   = $lang['AUTH_MODERATORS'];
$lang['AUTH_TYPES'][AUTH_ADMIN] = $lang['AUTH_ADMINISTRATORS'];

$lang['NEW_USER_REG_DISABLED'] = 'Sorry, registration is disabled at this time';
$lang['ONLY_NEW_POSTS'] = 'only new posts';
$lang['ONLY_NEW_TOPICS'] = 'only new topics';

$lang['TORHELP_TITLE'] = 'Please help seeding these torrents!';

//
// Reports (need to translate it!)
//
$lang['REPORTS'] = 'Reports';
$lang['NEW_REPORT'] = ' (one open)';
$lang['NO_NEW_REPORTS'] = ': no new Reports';
$lang['NEW_REPORTS'] = ' (%d open)';
$lang['REPORT_INDEX'] = 'Index';
$lang['STATISTICS'] = 'Statistics';
$lang['STATISTIC'] = 'Statistic';
$lang['VALUE'] = 'Value';
$lang['REPORT_COUNT'] = 'Current report count';
$lang['REPORT_MODULES_COUNT'] = 'Report modules count';
$lang['REPORT_HACK_COUNT'] = 'Overall report count';
$lang['DELETED_REPORTS'] = 'Reports suggested for deletion';
$lang['REPORT_TYPE'] = 'Report type';
$lang['REPORT_BY'] = 'by';
$lang['NO_REPORTS'] = 'No reports';
$lang['INVERT_SELECT'] = 'Invert selection';
$lang['REPORTED_BY'] = 'Reported by';
$lang['REPORTED_TIME'] = 'Reported on';
$lang['STATUS'] = 'Status';
$lang['LAST_CHANGED_BY'] = 'Last changed by';
$lang['CHANGES'] = 'Changes';
$lang['REPORT_CHANGE_TEXT'] = 'Marked as "%1$s" by %2$s on %3$s.';
$lang['REPORT_CHANGE_TEXT_COMMENT'] = 'Marked as "%1$s" by %2$s on %3$s:<br />%4$s';
$lang['REPORT_CHANGE_DELETE_TEXT'] = 'Suggested for deletion by %1$s on %2$s.';
$lang['ACTION'] = 'Action';
$lang['REPORT_MARK'] = 'Mark as';
$lang['OPEN_REPORTS'] = 'Offene Meldungen';
$lang['NO_REPORTS_FOUND'] = 'No matching reports found.';
$lang['NO_REPORTS_SELECTED'] = 'No reports were selected.';
$lang['REPORT_NOT_EXISTS'] = 'The selected report doesn\'t exist.';
$lang['REPORT_NOT_SUPPORTED'] = 'This feature isn\'t supported.';
$lang['CLICK_RETURN_REPORT'] = '%sClick here%s to return to the report.';
$lang['CLICK_RETURN_REPORT_LIST'] = '%sClick here%s to return to the report list.';

$lang['REPORT_STATUS'] = array(
	REPORT_NEW => 'new',
	REPORT_OPEN => 'open',
	REPORT_IN_PROCESS => 'in process',
	REPORT_CLEARED => 'cleared',
	REPORT_DELETE => 'suggested for deletion');

$lang['REASON'] = 'Reason';
$lang['REPORT_SUBJECT'] = 'Subject';
$lang['REPORT_TITLE_EMPTY'] = 'You have to enter a title of the report.';
$lang['REPORT_DESC_EMPTY'] = 'You have to enter a message.';
$lang['REPORT_INSERTED'] = 'The report was sent to the team.';

$lang['CHANGE_REPORT'] = 'Change report';
$lang['CHANGE_REPORTS'] = 'Change reports';
$lang['CHANGE_REPORT_EXPLAIN'] = 'Are you sure you want to change the status of the selected report?';
$lang['CHANGE_REPORTS_EXPLAIN'] = 'Are you sure you want to change the status of the selected reports?';
$lang['COMMENT'] = 'Comment';
$lang['REPORT_CHANGED'] = 'The status of the selected report was changed.';
$lang['REPORTS_CHANGED'] = 'The status of the selected reports was changed.';

$lang['DELETE_REPORT'] = 'Delete report';
$lang['DELETE_REPORTS'] = 'Delete reports';
$lang['DELETE_REPORT_EXPLAIN'] = 'Are you sure you want to delete the selected report?';
$lang['DELETE_REPORTS_EXPLAIN'] = 'Are you sure you want to delete the selected reports?';
$lang['REPORT_DELETED'] = 'The selected report was deleted.';
$lang['REPORTS_DELETED'] = 'The selected reports were deleted.';//
// Reports [END]
//

// Medal [BEGIN]
$lang['MEDAL'] = 'Honour roll';
$lang['TOP_10'] = 'Ten best';
$lang['TOP_10_RATIO'] = 'on Upload/Download Ratio';
$lang['TOP_10_SIZE_DOWNLOAD'] = 'on volume of the loaded';
$lang['BEST_RELIZER'] = 'Ten best';
$lang['BEST_RELEASES'] = 'The best releases';
$lang['DOWNLOAD_MONTH'] = 'Downloadings of releases for a month';
$lang['THANKS_MONTH'] = 'Thank\'s of releases for month';
$lang['BEST_RELEASES_MONTH'] = 'The best releases for a month';
$lang['BEST_RELEASES_WEEK'] = 'The best releases for a week';
$lang['THANKS'] = 'Thanks';
$lang['RELEASES'] = 'Releases';
$lang['AVERAGE_RATING'] = 'Average estimation';
$lang['BEST_COUNT_DOWNLOAD'] = 'on count downloadings';
$lang['BEST_COUNT_THANKS'] = 'on count thanks';
$lang['DOWNLOADS'] = 'Downloads';
$lang['ON_AVERAGE'] = 'On average';
// Medal [END]

// search
$lang['SEARCH_S'] = 'search...';
$lang['FORUM_S'] = 'on forum';
$lang['TRACKER_S'] = 'on tracker';

// copyright
$lang['NOTICE'] = '!ATTENTION!';
$lang['POWERED'] = 'Powered by <a href="http://torrentpier.info">TorrentPier</a> &copy; <strong>Meithar</strong>, RoadTrain, Pandora';
$lang['DIVE'] = 'The forum is submitted on base <a href="http://www.phpbb.com">phpBB</a> &copy; phpBB Group';
$lang['COPY'] = 'The site does not give electronic versions of products, and is engaged only in a collecting and cataloguing of the references sent and published at a forum by our readers. If you are the legal owner of any submitted material and do not wish that the reference to him{it} was in our catalogue, contact us and we shall immediately remove her. Files for an exchange on tracker are given by users of a site, and the administration does not bear the responsibility for their maintenance. The request to not fill in the files protected by copyrights, and also files of the illegal maintenance!';