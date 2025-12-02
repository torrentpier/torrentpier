<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Torrent;

/**
 * Common helper methods for torrent operations.
 */
trait HelperTrait
{
    /**
     * Get torrent info by topic ID.
     *
     * @param int $topicId Topic ID
     * @return array Topic data with allow_reg_tracker
     */
    public static function getTorrentInfo(int $topicId): array
    {
        $row = DB()->table(BB_TOPICS)
            ->select('topic_id, topic_first_post_id, topic_title, topic_poster, forum_id, tracker_status, attach_ext_id')
            ->where('topic_id', $topicId)
            ->fetch();

        if (!$row) {
            bb_die(__('INVALID_TOPIC_ID'));
        }

        $t_data = $row->toArray();
        $t_data['allow_reg_tracker'] = $row->ref(BB_FORUMS, 'forum_id')?->allow_reg_tracker ?? 0;

        return $t_data;
    }

    /**
     * Check that the user has access to torrent operations.
     *
     * @param int $forumId Forum ID
     * @param int $posterId Poster user ID
     */
    protected static function checkAuth(int $forumId, int $posterId): void
    {
        global $userdata;

        if (IS_ADMIN) {
            return;
        }

        $is_auth = auth(AUTH_ALL, $forumId, $userdata);

        if ($posterId != $userdata['user_id'] && !$is_auth['auth_mod']) {
            bb_die(__('NOT_MODERATOR'));
        } elseif (!$is_auth['auth_view'] || !$is_auth['auth_attachments']) {
            bb_die(sprintf(__('SORRY_AUTH_READ'), $is_auth['auth_read_type']));
        }
    }

    /**
     * Exit with a torrent-related error message.
     *
     * @param string $message Error message
     */
    protected static function errorExit(string $message): void
    {
        global $reg_mode, $return_message;

        $msg = '';

        if (isset($reg_mode) && ($reg_mode == 'request' || $reg_mode == 'newtopic')) {
            if (isset($return_message)) {
                $msg .= $return_message . '<br /><br /><hr/><br />';
            }
            $msg .= '<b>' . __('BT_REG_FAIL') . '</b><br /><br />';
        }

        bb_die($msg . $message);
    }
}
