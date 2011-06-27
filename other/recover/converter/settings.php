<?php
// Tracker type
define('TR_TYPE',  'yse'); // 'sky' (SkyTracker) or 'yse' (TBDev YSE)
// Options
define('CLEAN',    true); // Clean TorrentPier's database before converting?
//Users
define('CONVERT_USERS',    true); // Converting users is enabled?
define('C_USERS_PER_ONCE',  250); // Number of users converting per once
//Torrents and categories
define('CONVERT_TORRENTS',   true); // Converting torrents and categories is enabled?
define('C_TORRENTS_PER_ONCE', 400); // Number of torrents converting per once
define('BDECODE',           false); // Recalculate info_hash using bdecode?
//Comments
define('CONVERT_COMMENTS',    true); // Converting comments is enabled?
define('C_COMMENTS_PER_ONCE',  400); // Number of comments converting per once
//Mybb forums & topics
define('CONVERT_MYBB_FORUMS',   false); // Converting forums is enabled?
define('C_FORUMS_PER_ONCE',       100); // Number of forums converting per once

