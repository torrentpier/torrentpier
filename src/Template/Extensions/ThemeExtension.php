<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * Theme extension for Twig templates
 *
 * Provides all theme-related variables without PHP globals.
 * Replaces tpl_config.php functionality.
 */
class ThemeExtension extends AbstractExtension implements GlobalsInterface
{
    private string $imgPath = '';
    private string $sharedImgPath = '';

    public function setTemplatePath(string $templateDir): void
    {
        $templateName = basename($templateDir);
        $this->imgPath = '/styles/templates/' . $templateName . '/images/';
        $this->sharedImgPath = '/styles/images/';
    }

    public function getGlobals(): array
    {
        $images = $this->getImages();

        return array_merge(
            [
                'images' => $images,
                'IMG' => $this->imgPath,
                'POST_BTN_SPACER' => '&nbsp;',
                'TOPIC_ATTACH_ICON' => '<img src="' . $this->sharedImgPath . 'icon_clip.gif" alt="" />',
                'OPEN_MENU_IMG_ALT' => '<img src="' . $this->imgPath . 'menu_open_1.gif" class="menu-alt1" alt="" />',
            ],
            $this->getConfigVars(),
            $this->getPostButtonsVars(),
            $this->getPostIconsVars($images),
            $this->getPmIconsVars($images)
        );
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('theme_image', [$this, 'getImage']),
            new TwigFunction('theme_img_tag', [$this, 'getImageTag'], ['is_safe' => ['html']]),
        ];
    }

    public function getImage(string $key): string
    {
        return $this->getImages()[$key] ?? '';
    }

    public function getImageTag(string $key, string $class = '', string $alt = ''): string
    {
        $src = $this->getImage($key);
        if (!$src) {
            return '';
        }
        $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
        return '<img src="' . htmlspecialchars($src) . '"' . $classAttr . ' alt="' . htmlspecialchars($alt) . '" />';
    }

    private function getConfigVars(): array
    {
        if (!function_exists('config')) {
            return [];
        }

        $topicLeftColWidth = config()->get('topic_left_column_witdh') ?? 150;
        $postImgWidthDecr = config()->get('post_img_width_decr') ?? 52;
        $attachImgWidthDecr = config()->get('attach_img_width_decr') ?? 52;

        return [
            'TOPIC_LEFT_COL_SPACER_WITDH' => $topicLeftColWidth - 8,
            'POST_IMG_WIDTH_DECR_JS' => $topicLeftColWidth + $postImgWidthDecr,
            'ATTACH_IMG_WIDTH_DECR_JS' => $topicLeftColWidth + $attachImgWidthDecr,
            'FEED_IMG' => '<img src="' . $this->imgPath . 'feed.png" class="feed-small" alt="' . __('ATOM_FEED') . '" />',
        ];
    }

    private function getPostButtonsVars(): array
    {
        if (!defined('POSTING_URL')) {
            return [];
        }

        return [
            'QUOTE_URL' => POSTING_URL . '?mode=quote&amp;' . POST_POST_URL . '=',
            'EDIT_POST_URL' => POSTING_URL . '?mode=editpost&amp;' . POST_POST_URL . '=',
            'DELETE_POST_URL' => POSTING_URL . '?mode=delete&amp;' . POST_POST_URL . '=',
            'IP_POST_URL' => FORUM_PATH . 'modcp?mode=ip&amp;' . POST_POST_URL . '=',
        ];
    }

    private function getPostIconsVars(array $images): array
    {
        return [
            'MINIPOST_IMG' => '<img src="' . $images['icon_minipost'] . '" class="icon1" alt="' . __('POST') . '" />',
            'ICON_GOTOPOST' => '<img src="' . $images['icon_gotopost'] . '" class="icon1" alt="' . __('GO') . '" title="' . __('GOTO_PAGE') . '" />',
            'MINIPOST_IMG_NEW' => '<img src="' . $images['icon_minipost_new'] . '" class="icon1" alt="' . __('NEW') . '" />',
            'ICON_LATEST_REPLY' => '<img src="' . $images['icon_latest_reply'] . '" class="icon2" alt="' . __('LATEST') . '" title="' . __('VIEW_LATEST_POST') . '" />',
            'ICON_NEWEST_REPLY' => '<img src="' . $images['icon_newest_reply'] . '" class="icon2" alt="' . __('NEWEST') . '" title="' . __('VIEW_NEWEST_POST') . '" />',
        ];
    }

    private function getPmIconsVars(array $images): array
    {
        return [
            'INBOX_IMG' => '<img src="' . $images['pm_inbox'] . '" class="pm_box_icon" alt="" />',
            'OUTBOX_IMG' => '<img src="' . $images['pm_outbox'] . '" class="pm_box_icon" alt="" />',
            'SENTBOX_IMG' => '<img src="' . $images['pm_sentbox'] . '" class="pm_box_icon" alt="" />',
            'SAVEBOX_IMG' => '<img src="' . $images['pm_savebox'] . '" class="pm_box_icon" alt="" />',
        ];
    }

    private function getImages(): array
    {
        $main = $this->imgPath;
        $img = $this->sharedImgPath;

        return [
            // post_buttons
            'icon_delpost' => $main . 'icon_delete.gif',
            'icon_mod' => $main . 'icon_mod.gif',
            'icon_birthday' => $main . 'icon_birthday.gif',
            'icon_male' => $main . 'icon_male.gif',
            'icon_female' => $main . 'icon_female.gif',
            'icon_nogender' => $main . 'icon_nogender.gif',

            'icon_tor_m3u_icon' => $img . 'tor_m3u_format.png',
            'icon_tor_filelist' => $img . 't_info.png',
            'icon_tor_gold' => $img . 'tor_gold.gif',
            'icon_tor_silver' => $img . 'tor_silver.gif',

            // post_icons
            'icon_minipost' => $main . 'icon_minipost.gif',
            'icon_gotopost' => $main . 'icon_minipost.gif',
            'icon_minipost_new' => $main . 'icon_minipost_new.gif',
            'icon_latest_reply' => $main . 'icon_latest_reply.gif',
            'icon_newest_reply' => $main . 'icon_newest_reply.gif',

            // forum_icons
            'forum' => $main . 'folder_big.gif',
            'forum_new' => $main . 'folder_new_big.gif',
            'forum_locked' => $main . 'folder_locked_big.gif',

            // topic_icons
            'folder' => $main . 'folder.gif',
            'folder_new' => $main . 'folder_new.gif',
            'folder_hot' => $main . 'folder_hot.gif',
            'folder_hot_new' => $main . 'folder_new_hot.gif',
            'folder_locked' => $main . 'folder_lock.gif',
            'folder_locked_new' => $main . 'folder_lock_new.gif',
            'folder_sticky' => $main . 'folder_sticky.gif',
            'folder_sticky_new' => $main . 'folder_sticky_new.gif',
            'folder_announce' => $main . 'folder_announce.gif',
            'folder_announce_new' => $main . 'folder_announce_new.gif',
            'folder_dl' => $main . 'folder_dl.gif',
            'folder_dl_new' => $main . 'folder_dl_new.gif',
            'folder_dl_hot' => $main . 'folder_dl_hot.gif',
            'folder_dl_hot_new' => $main . 'folder_dl_hot_new.gif',

            // attach_icons
            'icon_clip' => $img . 'icon_clip.gif',
            'icon_dn' => $img . 'icon_dn.gif',
            'icon_magnet' => $img . 'magnet.png',
            'icon_magnet_v2' => $img . 'magnet_v2.png',

            // pm_icons
            'pm_inbox' => $main . 'msg_inbox.gif',
            'pm_outbox' => $main . 'msg_outbox.gif',
            'pm_savebox' => $main . 'msg_savebox.gif',
            'pm_sentbox' => $main . 'msg_sentbox.gif',
            'pm_readmsg' => $main . 'folder.gif',
            'pm_unreadmsg' => $main . 'folder_new.gif',
            'pm_new_msg' => '',
            'pm_no_new_msg' => '',

            // topic_mod_icons
            'topic_mod_lock' => $main . 'topic_lock.gif',
            'topic_mod_unlock' => $main . 'topic_unlock.gif',
            'topic_mod_split' => $main . 'topic_split.gif',
            'topic_mod_move' => $main . 'topic_move.gif',
            'topic_mod_delete' => $main . 'topic_delete.gif',
            'topic_dl' => $main . 'topic_dl.gif',
            'topic_normal' => $main . 'topic_normal.gif',

            // voting/progress
            'voting_graphic_0' => $main . 'voting_bar.gif',
            'voting_graphic_1' => $main . 'voting_bar.gif',
            'voting_graphic_2' => $main . 'voting_bar.gif',
            'voting_graphic_3' => $main . 'voting_bar.gif',
            'voting_graphic_4' => $main . 'voting_bar.gif',
            'progress_bar' => $main . 'progress_bar.gif',
            'progress_bar_full' => $main . 'progress_bar_full.gif',
        ];
    }
}
