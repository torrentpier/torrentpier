<?php

global $page_cfg, $template, $images, $lang;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$width = $height = array();
$template_name = basename(dirname(__FILE__));

$_img = BB_ROOT . 'styles/images/';
$_main = BB_ROOT . 'styles/' . basename(TEMPLATES_DIR) . '/' . $template_name . '/images/';
$_lang = $_main . 'lang/' . basename($di->config->get('default_lang')) . '/';

// post_buttons
$images['icon_quote'] = $_lang . 'icon_quote.gif';
$images['icon_edit'] = $_lang . 'icon_edit.gif';
$images['icon_search'] = $_lang . 'icon_search.gif';
$images['icon_profile'] = $_lang . 'icon_profile.gif';
$images['icon_pm'] = $_lang . 'icon_pm.gif';
$images['icon_email'] = $_lang . 'icon_email.gif';
$images['icon_delpost'] = $_main . 'icon_delete.gif';
$images['icon_ip'] = $_lang . 'icon_ip.gif';
$images['icon_mod'] = $_main . 'icon_mod.gif';
$images['icon_www'] = $_lang . 'icon_www.gif';
$images['icon_icq'] = $_lang . 'icon_icq_add.gif';

$images['icon_mc'] = $_lang . 'icon_mc.gif';
$images['icon_poll'] = $_lang . 'icon_poll.gif';

$images['icon_birthday'] = $_main . 'icon_birthday.gif';
$images['icon_male'] = $_main . 'icon_male.gif';
$images['icon_female'] = $_main . 'icon_female.gif';
$images['icon_nogender'] = $_main . 'icon_nogender.gif';

// post_icons
$images['icon_minipost'] = $_main . 'icon_minipost.gif';
$images['icon_gotopost'] = $_main . 'icon_minipost.gif';
$images['icon_minipost_new'] = $_main . 'icon_minipost_new.gif';
$images['icon_latest_reply'] = $_main . 'icon_latest_reply.gif';
$images['icon_newest_reply'] = $_main . 'icon_newest_reply.gif';

// forum_icons
$images['forum'] = $_main . 'folder_big.gif';
$images['forum_new'] = $_main . 'folder_new_big.gif';
$images['forum_locked'] = $_main . 'folder_locked_big.gif';

// topic_icons
$images['folder'] = $_main . 'folder.gif';
$images['folder_new'] = $_main . 'folder_new.gif';
$images['folder_hot'] = $_main . 'folder_hot.gif';
$images['folder_hot_new'] = $_main . 'folder_new_hot.gif';
$images['folder_locked'] = $_main . 'folder_lock.gif';
$images['folder_locked_new'] = $_main . 'folder_lock_new.gif';
$images['folder_sticky'] = $_main . 'folder_sticky.gif';
$images['folder_sticky_new'] = $_main . 'folder_sticky_new.gif';
$images['folder_announce'] = $_main . 'folder_announce.gif';
$images['folder_announce_new'] = $_main . 'folder_announce_new.gif';
$images['folder_dl'] = $_main . 'folder_dl.gif';
$images['folder_dl_new'] = $_main . 'folder_dl_new.gif';
$images['folder_dl_hot'] = $_main . 'folder_dl_hot.gif';
$images['folder_dl_hot_new'] = $_main . 'folder_dl_hot_new.gif';

// attach_icons
$images['icon_clip'] = $_img . 'icon_clip.gif';
$images['icon_dn'] = $_img . 'icon_dn.gif';
$images['icon_magnet'] = $_img . 'magnet.png';
$images['icon_dc_magnet'] = $_img . 'dc_magnet.png';
$images['icon_dc_magnet_ext'] = $_img . 'dc_magnet_ext.png';

// posting_icons
$images['post_new'] = $_lang . 'post.gif';
$images['post_locked'] = $_lang . 'reply-locked.gif';
$images['reply_new'] = $_lang . 'reply.gif';
$images['reply_locked'] = $_lang . 'reply-locked.gif';
$images['release_new'] = $_lang . 'release.gif';

// pm_icons
$images['pm_inbox'] = $_main . 'msg_inbox.gif';
$images['pm_outbox'] = $_main . 'msg_outbox.gif';
$images['pm_savebox'] = $_main . 'msg_savebox.gif';
$images['pm_sentbox'] = $_main . 'msg_sentbox.gif';
$images['pm_readmsg'] = $_main . 'folder.gif';
$images['pm_unreadmsg'] = $_main . 'folder_new.gif';
$images['pm_replymsg'] = $_lang . 'reply.gif';
$images['pm_postmsg'] = $_lang . 'msg_newpost.gif';
$images['pm_quotemsg'] = $_lang . 'icon_quote.gif';
$images['pm_editmsg'] = $_lang . 'icon_edit.gif';
$images['pm_new_msg'] = '';
$images['pm_no_new_msg'] = '';

// topic_mod_icons will be replaced with SELECT later...
$images['topic_watch'] = '';
$images['topic_un_watch'] = '';
$images['topic_mod_lock'] = $_main . 'topic_lock.gif';
$images['topic_mod_unlock'] = $_main . 'topic_unlock.gif';
$images['topic_mod_split'] = $_main . 'topic_split.gif';
$images['topic_mod_move'] = $_main . 'topic_move.gif';
$images['topic_mod_delete'] = $_main . 'topic_delete.gif';
$images['topic_dl'] = $_main . 'topic_dl.gif';
$images['topic_normal'] = $_main . 'topic_normal.gif';

$images['voting_graphic'][0] = $_main . 'voting_bar.gif';
$images['voting_graphic'][1] = $_main . 'voting_bar.gif';
$images['voting_graphic'][2] = $_main . 'voting_bar.gif';
$images['voting_graphic'][3] = $_main . 'voting_bar.gif';
$images['voting_graphic'][4] = $_main . 'voting_bar.gif';
$images['progress_bar'] = $_main . 'progress_bar.gif';
$images['progress_bar_full'] = $_main . 'progress_bar_full.gif';

$template->assign_vars(array(
    'IMG' => $_main,
    'TEXT_BUTTONS' => $di->config->get('text_buttons'),
    'POST_BTN_SPACER' => ($di->config->get('text_buttons')) ? '&nbsp;' : '',
    'TOPIC_ATTACH_ICON' => '<img src="' . $_img . 'icon_clip.gif" alt="" />',
    'OPEN_MENU_IMG_ALT' => '<img src="' . $_main . 'menu_open_1.gif" class="menu-alt1" alt="" />',
    'TOPIC_LEFT_COL_SPACER_WITDH' => $di->config->get('topic_left_column_witdh') - 8, // 8px padding
    'POST_IMG_WIDTH_DECR_JS' => $di->config->get('topic_left_column_witdh') + $di->config->get('post_img_width_decr'),
    'ATTACH_IMG_WIDTH_DECR_JS' => $di->config->get('topic_left_column_witdh') + $di->config->get('attach_img_width_decr'),
    'MAGNET_LINKS' => $di->config->get('magnet_links_enabled'),
    'FEED_IMG' => '<img src="' . $_main . 'feed.png" class="feed-small" alt="' . $lang['ATOM_FEED'] . '" />',
));

// post_buttons
if (!empty($page_cfg['load_tpl_vars']) && ($vars = array_flip($page_cfg['load_tpl_vars']))) {
    if (isset($vars['post_buttons'])) {
        $template->assign_vars(array(
            'QUOTE_IMG' => ($di->config->get('text_buttons')) ? $lang['REPLY_WITH_QUOTE_TXTB'] : '<img src="' . $images['icon_quote'] . '" alt="' . $lang['REPLY_WITH_QUOTE_TXTB'] . '" title="' . $lang['REPLY_WITH_QUOTE'] . '" />',
            'EDIT_POST_IMG' => ($di->config->get('text_buttons')) ? $lang['EDIT_DELETE_POST_TXTB'] : '<img src="' . $images['icon_edit'] . '" alt="' . $lang['EDIT_DELETE_POST_TXTB'] . '" title="' . $lang['EDIT_POST'] . '" />',
            'DELETE_POST_IMG' => ($di->config->get('text_buttons')) ? $lang['DELETE_POST_TXTB'] : '<img src="' . $images['icon_delpost'] . '" alt="' . $lang['DELETE_POST_TXTB'] . '" title="' . $lang['DELETE_POST'] . '" />',
            'IP_POST_IMG' => ($di->config->get('text_buttons')) ? $lang['VIEW_IP_TXTB'] : '<img src="' . $images['icon_ip'] . '" alt="' . $lang['VIEW_IP_TXTB'] . '" title="' . $lang['VIEW_IP'] . '" />',
            'MOD_POST_IMG' => ($di->config->get('text_buttons')) ? $lang['MODERATE_POST_TXTB'] : '<img src="' . $images['icon_mod'] . '" alt="' . $lang['MODERATE_POST_TXTB'] . '" title="' . $lang['MODERATE_POST'] . '" />',
            'MC_IMG' => ($di->config->get('text_buttons')) ? '[' . $lang['COMMENT'] . ']' : '<img src="' . $images['icon_mc'] . '" alt="[' . $lang['COMMENT'] . ']" title="' . $lang['COMMENT'] . '" />',
            'POLL_IMG' => ($di->config->get('text_buttons')) ? $lang['TOPIC_POLL'] : '<img src="' . $images['icon_poll'] . '" alt="' . $lang['TOPIC_POLL'] . '" title="' . $lang['ADD_POLL'] . '" />',

            'QUOTE_URL' => BB_ROOT . POSTING_URL . "?mode=quote&amp;p=",
            'EDIT_POST_URL' => BB_ROOT . POSTING_URL . "?mode=editpost&amp;p=",
            'DELETE_POST_URL' => BB_ROOT . POSTING_URL . "?mode=delete&amp;p=",
            'IP_POST_URL' => BB_ROOT . "modcp.php?mode=ip&amp;p=",

            'PROFILE_IMG' => ($di->config->get('text_buttons')) ? $lang['READ_PROFILE_TXTB'] : '<img src="' . $images['icon_profile'] . '" alt="' . $lang['READ_PROFILE_TXTB'] . '" title="' . $lang['READ_PROFILE'] . '" />',
            'PM_IMG' => ($di->config->get('text_buttons')) ? $lang['SEND_PM_TXTB'] : '<img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PM_TXTB'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" />',
            'EMAIL_IMG' => ($di->config->get('text_buttons')) ? $lang['SEND_EMAIL_TXTB'] : '<img src="' . $images['icon_email'] . '" alt="' . $lang['SEND_EMAIL_TXTB'] . '" title="' . $lang['SEND_EMAIL'] . '" />',
            'WWW_IMG' => ($di->config->get('text_buttons')) ? $lang['VISIT_WEBSITE_TXTB'] : '<img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE_TXTB'] . '" title="' . $lang['VISIT_WEBSITE'] . '" />',
            'ICQ_IMG' => ($di->config->get('text_buttons')) ? $lang['ICQ_TXTB'] : '<img src="' . $images['icon_icq'] . '" alt="' . $lang['ICQ_TXTB'] . '" title="' . $lang['ICQ'] . '" />',

            'EMAIL_URL' => BB_ROOT . "profile.php?mode=email&amp;u=",
            'FORUM_URL' => BB_ROOT . FORUM_URL,
            'PM_URL' => BB_ROOT . PM_URL,
            'PROFILE_URL' => BB_ROOT . PROFILE_URL,
        ));
    }
    if (isset($vars['post_icons'])) {
        $template->assign_vars(array(
            'MINIPOST_IMG' => '<img src="' . $images['icon_minipost'] . '" class="icon1" alt="' . $lang['POST'] . '" />',
            'ICON_GOTOPOST' => '<img src="' . $images['icon_gotopost'] . '" class="icon1" alt="' . $lang['GO'] . '" title="' . $lang['GOTO_PAGE'] . '" />',
            'MINIPOST_IMG_NEW' => '<img src="' . $images['icon_minipost_new'] . '" class="icon1" alt="' . $lang['NEW'] . '" />',
            'ICON_LATEST_REPLY' => '<img src="' . $images['icon_latest_reply'] . '" class="icon2" alt="' . $lang['LATEST'] . '" title="' . $lang['VIEW_LATEST_POST'] . '" />',
            'ICON_NEWEST_REPLY' => '<img src="' . $images['icon_newest_reply'] . '" class="icon2" alt="' . $lang['NEWEST'] . '" title="' . $lang['VIEW_NEWEST_POST'] . '" />',
        ));
    }
    if (isset($vars['topic_icons'])) {
        $template->assign_vars(array(
            'MOVED' => TOPIC_MOVED,
            'ANNOUNCE' => POST_ANNOUNCE,
            'STICKY' => POST_STICKY,
            'LOCKED' => TOPIC_LOCKED,
        ));
    }
    if (isset($vars['pm_icons'])) {
        $template->assign_vars(array(
            'INBOX_IMG' => '<img src="' . $images['pm_inbox'] . '" class="pm_box_icon" alt="" />',
            'OUTBOX_IMG' => '<img src="' . $images['pm_outbox'] . '" class="pm_box_icon" alt="" />',
            'SENTBOX_IMG' => '<img src="' . $images['pm_sentbox'] . '" class="pm_box_icon" alt="" />',
            'SAVEBOX_IMG' => '<img src="' . $images['pm_savebox'] . '" class="pm_box_icon" alt="" />',
        ));
    }
}
