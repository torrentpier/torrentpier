<?php

$lang['RETURN_CONFIG'] = '%sReturn to Configuration%s';
$lang['CONFIG_UPD'] = 'Configuration Updated Successfully';
$lang['SET_DEFAULTS'] = 'Restore defaults';

//
// Tracker config
//
$lang['TRACKER_CFG_TITLE'] = 'Tracker';
$lang['FORUM_CFG_TITLE'] = 'Forum settings';
$lang['TRACKER_SETTINGS'] = 'Tracker settings';

$lang['OFF'] = 'Disable tracker';
$lang['OFF_REASON'] = 'Disable reason';
$lang['OFF_REASON_EXPL'] = 'this message will be sent to client when the tracker is disabled';
$lang['AUTOCLEAN'] = 'Autoclean';
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

//
// Forum config
//
$lang['FORUM_CFG_EXPL'] = 'Forum config';

$lang['BT_SELECT_FORUMS'] = 'Forum options:';
$lang['BT_SELECT_FORUMS_EXPL'] = 'hold down <i>Ctrl</i> while selecting multiple forums';

$lang['REG_TORRENTS'] = 'Register torrents';
$lang['ALLOWED'] = 'Resolved';
$lang['DISALLOWED'] = 'Prohibited';
$lang['ALLOW_REG_TRACKER'] = 'Allowed forums for registering .torrents on tracker';
$lang['ALLOW_PORNO_TOPIC'] = 'Allow post porno topics';
$lang['SHOW_DL_BUTTONS'] = 'Show buttons for manually changing DL-status';
$lang['SELF_MODERATED'] = 'Users can <b>move</b> their topics to another forum';

$lang['BT_ANNOUNCE_URL_HEAD'] = 'Announce URL';
$lang['BT_ANNOUNCE_URL'] = 'Announce url';
$lang['BT_ANNOUNCE_URL_EXPL'] = 'you can define additional allowed urls in "includes/announce_urls.php"';
$lang['BT_DISABLE_DHT'] = 'Disable DHT network';
$lang['BT_DISABLE_DHT_EXPL'] = 'Disable Peer Exchange and DHT (recommended for private networks, only url announce)';
$lang['BT_CHECK_ANNOUNCE_URL'] = 'Verify announce url';
$lang['BT_CHECK_ANNOUNCE_URL_EXPL'] = 'register on tracker only allowed urls';
$lang['BT_REPLACE_ANN_URL'] = 'Replace announce url';
$lang['BT_REPLACE_ANN_URL_EXPL'] = 'replace original announce url with your default in .torrent files';
$lang['BT_DEL_ADDIT_ANN_URLS'] = 'Remove all additional announce urls';
$lang['BT_ADD_COMMENT'] = 'Torrent comments';
$lang['BT_ADD_COMMENT_EXPL'] = 'adds the Comments filed to the .torrent files (leave blank to use the topic URL as a comment)';
$lang['BT_ADD_PUBLISHER'] = 'Torrent\'s publisher';
$lang['BT_ADD_PUBLISHER_EXPL'] = 'adds the Publisher field and topic URL as the Publisher-url to the .torrent files (leave blank to disable)';

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
$lang['BT_SHOW_DL_BUT_WILL'] = $lang['DL_WILL'];
$lang['BT_SHOW_DL_BUT_DOWN'] = $lang['DL_DOWN'];
$lang['BT_SHOW_DL_BUT_COMPL'] = $lang['DL_COMPLETE'];
$lang['BT_SHOW_DL_BUT_CANCEL'] = $lang['DL_CANCEL'];

$lang['BT_ADD_AUTH_KEY_HEAD'] = 'Passkey';
$lang['BT_ADD_AUTH_KEY'] = 'Enable adding passkey to the torrent-files before downloading';
$lang['BT_GEN_PASSKEY_ON_REG'] = 'Automatically generate passkey';
$lang['BT_GEN_PASSKEY_ON_REG_EXPL'] = "generate passkey during first downloading attempt if current user's passkey is empty";

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

//
// Release
//
$lang['LIST_FORUMS'] = 'List Forums';
$lang['LIST_OF_PATTERNS'] = 'List of patterns';
$lang['ADD_TEMPLATE'] = 'Add the template';

$lang['RELEASE_EXP'] = 'This page displays all forums. For each of them you can set the release type which should be posted in the forum.';
$lang['TPL_NONE'] = 'Don\'t use templates';
$lang['TPL_VIDEO'] = 'Video (basic)';
$lang['TPL_VIDEO_HOME'] = 'Video (home)';
$lang['TPL_VIDEO_SIMPLE'] = 'Video (simple)';
$lang['TPL_VIDEO_LESSON'] = 'Video (lesson)';
$lang['TPL_GAMES'] = 'Games';
$lang['TPL_GAMES_PS'] = 'Games PS/PS2';
$lang['TPL_GAMES_PSP'] = 'Games PSP';
$lang['TPL_GAMES_XBOX'] = 'Games XBOX';
$lang['TPL_PROGS'] = 'Programs';
$lang['TPL_PROGS_MAC'] = 'Programs Mac OS';
$lang['TPL_MUSIC'] = 'Music';
$lang['TPL_BOOKS'] = 'Books';
$lang['TPL_AUDIOBOOKS'] = 'Audiobooks';
$lang['TPL_SPORT'] = 'Sport';