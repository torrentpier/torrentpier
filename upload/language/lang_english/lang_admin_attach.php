<?php
/**
*
* attachment mod admin [English]
*
* @package attachment_mod
* @version $Id: lang_admin_attach.php,v 1.3 2005/11/20 13:38:55 acydburn Exp $
* @copyright (c) 2002 Meik Sievertsen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!isset($lang) || !is_array($lang))
{
	$lang = array();
}

//
// Attachment Mod Admin Language Variables
//

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
$lang['UPLOAD_DIRECTORY_EXPLAIN'] = 'Enter the relative path from your phpBB2 installation to the Attachments upload directory. For example, enter \'files\' if your phpBB2 Installation is located at http://www.yourdomain.com/phpBB2 and the Attachment Upload Directory is located at http://www.yourdomain.com/phpBB2/files.';
$lang['ATTACH_IMG_PATH'] = 'Attachment Posting Icon';
$lang['ATTACH_IMG_PATH_EXPLAIN'] = 'This Image is displayed next to Attachment Links in individual Postings. Leave this field empty if you don\'t want an icon to be displayed. This Setting will be overwritten by the Settings in Extension Groups Management.';
$lang['ATTACH_TOPIC_ICON'] = 'Attachment Topic Icon';
$lang['ATTACH_TOPIC_ICON_EXPLAIN'] = 'This Image is displayed before topics with Attachments. Leave this field empty if you don\'t want an icon to be displayed.';
$lang['ATTACH_DISPLAY_ORDER'] = 'Attachment Display Order';
$lang['ATTACH_DISPLAY_ORDER_EXPLAIN'] = 'Here you can choose whether to display the Attachments in Posts/PMs in Descending Filetime Order (Newest Attachment First) or Ascending Filetime Order (Oldest Attachment First).';

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
$lang['FTP_UPLOAD'] = 'Enable FTP Upload';
$lang['FTP_UPLOAD_EXPLAIN'] = 'Enable/Disable the FTP Upload option. If you set it to yes, you have to define the Attachment FTP Settings and the Upload Directory is no longer used.';

$lang['FTP_SERVER'] = 'FTP Upload Server';
$lang['FTP_SERVER_EXPLAIN'] = 'Here you can enter the IP-Address or FTP-Hostname of the Server used for your uploaded files. If you leave this field empty, the Server on which your phpBB2 Board is installed will be used. Please note that it is not allowed to add ftp:// or something else to the address, just plain ftp.foo.com or, which is a lot faster, the plain IP Address.';

$lang['ATTACH_FTP_PATH'] = 'FTP Path to your upload directory';
$lang['ATTACH_FTP_PATH_EXPLAIN'] = 'The Directory where your Attachments will be stored. This Directory doesn\'t have to be chmodded. Please don\'t enter your IP or FTP-Address here, this input field is only for the FTP Path.<br />For example: /home/web/uploads';
$lang['FTP_DOWNLOAD_PATH'] = 'Download Link to FTP Path';
$lang['FTP_DOWNLOAD_PATH_EXPLAIN'] = 'Enter the URL to your FTP Path, where your Attachments are stored.<br />If you are using a Remote FTP Server, please enter the complete url, for example http://www.mystorage.com/phpBB2/upload.<br />If you are using your Local Host to store your Files, you are able to enter the url path relative to your phpBB2 Directory, for example \'upload\'.<br />A trailing slash will be removed. Leave this field empty, if the FTP Path is not accessible from the Internet. With this field empty you are unable to use the physical download method.';
$lang['FTP_PASSIVE_MODE'] = 'Enable FTP Passive Mode';
$lang['FTP_PASSIVE_MODE_EXPLAIN'] = 'The PASV command requests that the remote server open a port for the data connection and return the address of that port. The remote server listens on that port and the client connects to it.';

$lang['NO_FTP_EXTENSIONS_INSTALLED'] = 'You are not able to use the FTP Upload Methods, because FTP Extensions are not compiled into your PHP Installation.';

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
$lang['CATEGORY_STREAM_FILES'] = 'Stream Files';
$lang['CATEGORY_SWF_FILES'] = 'Flash Files';
$lang['ALLOWED'] = 'Allowed';
$lang['ALLOWED_FORUMS'] = 'Allowed Forums';
$lang['EXT_GROUP_PERMISSIONS'] = 'Group Permissions';
$lang['DOWNLOAD_MODE'] = 'Download Mode';
$lang['UPLOAD_ICON'] = 'Upload Icon';
$lang['MAX_GROUPS_FILESIZE'] = 'Maximum Filesize';
$lang['EXTENSION_GROUP_EXIST'] = 'The Extension Group %s already exist'; // replace %s with the group name
$lang['COLLAPSE'] = '+';
$lang['DECOLLAPSE'] = '-';

// Extensions -> Special Categories
$lang['MANAGE_CATEGORIES'] = 'Manage Special Categories';
$lang['MANAGE_CATEGORIES_EXPLAIN'] = 'Here you can configure the Special Categories. You can set up Special Parameters and Conditions for the Special Categorys assigned to an Extension Group.';
$lang['SETTINGS_CAT_IMAGES'] = 'Settings for Special Category: Images';
$lang['SETTINGS_CAT_STREAMS'] = 'Settings for Special Category: Stream Files';
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
$lang['EXTENSION_EXIST_FORBIDDEN'] = 'The Extension %s is defined in your allowed Extensions, please delete it their before you add it here.';  // replace %s with the extension

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
$lang['FILE_COMMENT_CP'] = 'File Comment';

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

// Control Panel -> Attachments
$lang['STATISTICS_FOR_USER'] = 'Attachment Statistics for %s'; // replace %s with username
$lang['SIZE_IN_KB'] = 'Size (KB)';
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
$lang['SORT_POSTS'] = 'Posts';

// View Types
$lang['VIEW_STATISTIC'] = 'Statistics';
$lang['VIEW_SEARCH'] = 'Search';
$lang['VIEW_USERNAME'] = 'Username';
$lang['VIEW_ATTACHMENTS'] = 'Attachments';

// Successfully updated
$lang['ATTACH_CONFIG_UPDATED'] = 'Attachment Configuration updated successfully';
$lang['CLICK_RETURN_ATTACH_CONFIG'] = 'Click %sHere%s to return to Attachment Configuration';
$lang['TEST_SETTINGS_SUCCESSFUL'] = 'Settings Test finished, configuration seems to be fine.';

// Some basic definitions
$lang['ATTACHMENTS'] = 'Attachments';
$lang['ATTACHMENT'] = 'Attachment';
$lang['EXTENSIONS'] = 'Extensions';
$lang['EXTENSION'] = 'Extension';