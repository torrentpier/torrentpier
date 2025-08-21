<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class AttachPosting
 * @package TorrentPier\Legacy
 */
class AttachPosting extends Attach
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->page = 0;
    }

    /**
     * Insert an Attachment into a Post (this is the second function called from posting.php)
     *
     * @param $post_id
     */
    public function insert_attachment($post_id)
    {
        global $is_auth, $mode;

        // Insert Attachment ?
        if ($post_id && ($mode === 'newtopic' || $mode === 'reply' || $mode === 'editpost') && $is_auth['auth_attachments']) {
            $this->do_insert_attachment('attach_list', 'post', $post_id);
            $this->do_insert_attachment('last_attachment', 'post', $post_id);

            if (((is_countable($this->attachment_list) ? \count($this->attachment_list) : 0) > 0 || $this->post_attach) && !isset($_POST['update_attachment'])) {
                $sql = 'UPDATE ' . BB_POSTS . ' SET post_attachment = 1 WHERE post_id = ' . (int)$post_id;

                if (!DB()->sql_query($sql)) {
                    bb_die('Unable to update posts table');
                }

                $sql = 'SELECT topic_id FROM ' . BB_POSTS . ' WHERE post_id = ' . (int)$post_id;

                if (!($result = DB()->sql_query($sql))) {
                    bb_die('Unable to select posts table');
                }

                $row = DB()->sql_fetchrow($result);
                DB()->sql_freeresult($result);

                $sql = 'UPDATE ' . BB_TOPICS . ' SET topic_attachment = 1 WHERE topic_id = ' . (int)$row['topic_id'];

                if (!DB()->sql_query($sql)) {
                    bb_die('Unable to update topics table');
                }
            }
        }
    }

    /**
     * Handle Attachments (Add/Delete/Edit/Show) - This is the first function called from every message handler
     */
    public function posting_attachment_mod()
    {
        global $mode, $confirm, $is_auth, $post_id, $delete, $refresh;

        if (!$refresh) {
            $add_attachment_box = !empty($_POST['add_attachment_box']);
            $posted_attachments_box = !empty($_POST['posted_attachments_box']);

            $refresh = $add_attachment_box || $posted_attachments_box;
        }

        // Choose what to display
        $result = $this->handle_attachments($mode);

        if ($result === false) {
            return;
        }

        if ($confirm && ($delete || $mode === 'delete' || $mode === 'editpost') && ($is_auth['auth_delete'] || $is_auth['auth_mod'])) {
            if ($post_id) {
                delete_attachment($post_id);
            }
        }

        $this->display_attachment_bodies();
    }
}
