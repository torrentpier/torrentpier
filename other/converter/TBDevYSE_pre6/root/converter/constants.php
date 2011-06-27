<?php

if (!defined('EXCLUDED_USERS_CSV')) { define('EXCLUDED_USERS_CSV', join(',', array(ANONYMOUS,	BOT_UID,))); }
define('TB_USERS_TABLE',           'users');
define('TB_CATEGORIES_TABLE', 'categories');
define('TB_TORRENTS_TABLE',     'torrents');
define('TB_COMMENTS_TABLE',     'comments');
define('MYBB_FORUMS_TABLE',  'mybb_forums');