<?php

$lang['EXTREME_STYLES'] = 'eXtreme Styles';
$lang['XS_TITLE'] = 'eXtreme Styles mod';

$lang['XS_FILE'] = 'File';
$lang['XS_TEMPLATE'] = 'Template';
$lang['XS_ID'] = 'ID';
$lang['XS_STYLE'] = 'Style';
$lang['XS_STYLES'] = 'Styles';
$lang['XS_USERS'] = 'Users';
$lang['XS_OPTIONS'] = 'Options';
$lang['XS_COMMENT'] = 'Comment';
$lang['XS_UPLOAD_TIME'] = 'Upload Time';
$lang['XS_SELECT'] = 'Select';

$lang['XS_CLICK_HERE_LC'] = 'click here';

/*
* navigation
*/
$lang['XS_CONFIG_SHOWNAV'] = array(
	'Configuration',
	'Manage Cache',
);

/*
* frame_top.tpl
*/
$lang['XS_MENU_LC'] = 'extreme styles mod menu';
$lang['XS_SUPPORT_FORUM_LC'] = 'support forum';
$lang['XS_DOWNLOAD_STYLES_LC'] = 'download styles';
$lang['XS_INSTALL_STYLES_LC'] = 'install styles';

/*
* index.tpl
*/

$lang['XS_MAIN_COMMENT3'] = 'All functions of phpBB styles management are replaced with eXtreme Styles mod.<br /><br /><a href="{URL}">Click here</a> to see menu.';
$lang['XS_MAIN_TITLE'] = 'eXtreme Styles Navigation Menu';
$lang['XS_MENU'] = 'eXtreme Styles Menu';

$lang['XS_CONFIGURATION'] = 'Configuration';
$lang['XS_CONFIGURATION_EXPLAIN'] = 'This feature allows you to change the eXtreme Styles configuration.';
$lang['XS_MANAGE_CACHE'] = 'Manage Cache';
$lang['XS_MANAGE_CACHE_EXPLAIN'] = 'This feature allows you to manage cached files.';
$lang['XS_SET_CONFIGURATION_LC'] = 'set configuration';
$lang['XS_SET_DEFAULT_STYLE_LC'] = 'set default style';
$lang['XS_MANAGE_CACHE_LC'] = 'manage cache';

/*
* config.tpl
*/

$lang['XS_CONFIG_UPDATED'] = 'Configuration updated.';
$lang['XS_CONFIG_UPDATED_EXPLAIN'] = 'You need to refresh this page before the new configuration can take effect. <a href="{URL}">Click here</a> to refresh page.';
$lang['XS_CONFIG_WARNING'] = 'Warning: cache cannot be written.';
$lang['XS_CONFIG_WARNING_EXPLAIN'] = 'Cache directory is not writeable. eXtreme Styles can attempt to fix this problem.<br /><a href="{URL}">Click here</a> to try to change access mode to cache directory.<br /><br />If cache doesn\'t work on your server for some reason don\'t worry - eXtreme Styles<br />increases forum speed many times even without cache.';

$lang['XS_CONFIG_MAINTITLE'] = 'eXtreme Styles mod Configuration';
$lang['XS_CONFIG_SUBTITLE'] = 'This is the configuration for eXtreme Styles. If you don\'t understand what certain variables do then don\'t change it.';
$lang['XS_CONFIG_TITLE'] = 'eXtreme Styles mod v{VERSION} settings';
$lang['XS_CONFIG_CACHE'] = 'Cache configuration';

$lang['XS_CONFIG_TPL_COMMENTS'] = 'Add tpl filenames in html';
$lang['XS_CONFIG_TPL_COMMENTS_EXPLAIN'] = 'This feature adds comments to html code that allow style designers to detect which tpl file is displayed.';

$lang['XS_CONFIG_USE_CACHE'] = 'Use cache';
$lang['XS_CONFIG_USE_CACHE_EXPLAIN'] = 'Cache is saved to disk and it will accelerate templates system because there would be no need to compile template every time it is shown.';

$lang['XS_CONFIG_AUTO_COMPILE'] = 'Automatically save cache';
$lang['XS_CONFIG_AUTO_COMPILE_EXPLAIN'] = 'This will automatically compile templates that are not cached and save to cache directory.';

$lang['XS_CONFIG_AUTO_RECOMPILE'] = 'Automatically re-compile cache';
$lang['XS_CONFIG_AUTO_RECOMPILE_EXPLAIN'] = 'This will automatically re-compile templates if a template was changed.';

$lang['XS_CONFIG_PHP'] = 'Extension of cache filenames';
$lang['XS_CONFIG_PHP_EXPLAIN'] = 'This is extension of cached files. Files are stored in php format so default extension is "php". Do not include dot';

$lang['XS_CONFIG_BACK'] = '<a href="{URL}">Click here</a> to return to configuration.';
$lang['XS_CONFIG_SQL_ERROR'] = 'Failed to update general configuration for {VAR}';

// Debug info
$lang['XS_DEBUG_HEADER'] = 'Debug info';
$lang['XS_DEBUG_EXPLAIN'] = 'This is debug info. Used to find/fix problems when configuring cache.';
$lang['XS_DEBUG_VARS'] = 'Template variables';
$lang['XS_DEBUG_TPL_NAME'] = 'Template filename:';
$lang['XS_DEBUG_CACHE_FILENAME'] = 'Cache filename:';
$lang['XS_DEBUG_DATA'] = 'Debug data:';

$lang['XS_CHECK_HDR'] = 'Checking cache for %s';
$lang['XS_CHECK_FILENAME'] = 'Error: invalid filename';
$lang['XS_CHECK_OPENFILE1'] = 'Error: cannot open file "%s". Will try to create directories...';
$lang['XS_CHECK_OPENFILE2'] = 'Error: cannot open file "%s" for the second time. Giving up...';
$lang['XS_CHECK_NODIR'] = 'Checking "%s" - no such directory.';
$lang['XS_CHECK_NODIR2'] = 'Error: cannot create directory "%s" - you might need to check permissions.';
$lang['XS_CHECK_CREATEDDIR'] = 'Created directory "%s"';
$lang['XS_CHECK_DIR'] = 'Checking "%s" - directory exists.';
$lang['XS_CHECK_OK'] = 'Opened file "%s" for writing. Everything seems to be ok.';
$lang['XS_ERROR_DEMO_EDIT'] = 'you cannot edit file in demo mode';
$lang['XS_ERROR_NOT_INSTALLED'] = 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php';

/*
* chmod
*/

$lang['XS_CHMOD'] = 'CHMOD';
$lang['XS_CHMOD_RETURN'] = '<br /><br /><a href="{URL}">Click here</a> to return to configuration.';
$lang['XS_CHMOD_MESSAGE1'] = 'Configuration changed.';
$lang['XS_CHMOD_ERROR1'] = 'Cannot change access mode to cache directory';

/*
* cache management
*/

$lang['XS_MANAGE_CACHE_EXPLAIN2'] = 'This feature allows you to compile or remove cached files for styles.';
$lang['XS_CLEAR_ALL_LC'] = 'clear all';
$lang['XS_COMPILE_ALL_LC'] = 'compile all';
$lang['XS_CLEAR_CACHE_LC'] = 'clear cache';
$lang['XS_COMPILE_CACHE_LC'] = 'compile cache';
$lang['XS_CACHE_CONFIRM'] = 'If you have many styles it might cause huge server load. Are you sure you want to continue?';

$lang['XS_CACHE_NOWRITE'] = 'Error: cannot access cache directory';
$lang['XS_CACHE_LOG_DELETED'] = 'Deleted {FILE}';
$lang['XS_CACHE_LOG_NODELETE'] = 'Error: cannot delete file {FILE}';
$lang['XS_CACHE_LOG_NOTHING'] = 'Nothing to delete for template {TPL}';
$lang['XS_CACHE_LOG_NOTHING2'] = 'Nothing to delete in cache directory';
$lang['XS_CACHE_LOG_COUNT'] = 'Successfully deleted {NUM} files';
$lang['XS_CACHE_LOG_COUNT2'] = 'Error deleting {NUM} files';
$lang['XS_CACHE_LOG_COMPILED'] = 'Compiled: {NUM} files';
$lang['XS_CACHE_LOG_ERRORS'] = 'Errors: {NUM}';
$lang['XS_CACHE_LOG_NOACCESS'] = 'Error: cannot access directory {DIR}';
$lang['XS_CACHE_LOG_COMPILED2'] = 'Compiled: {FILE}';
$lang['XS_CACHE_LOG_NOCOMPILE'] = 'Error compiling: {FILE}';

/*
* style configuration
*/
$lang['TEMPLATE_CONFIG'] = 'Template Config';
$lang['XS_STYLE_CONFIGURATION'] = 'Template Configuration';