<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * All Attachment Functions processing the Deletion Process
 */

/**
 * Delete Attachment(s) from post(s) (intern)
 */
function delete_attachment($post_id_array = 0, $attach_id_array = 0, $page = 0, $user_id = 0)
{
    // Generate Array, if it's not an array
    if ($post_id_array === 0 && $attach_id_array === 0 && $page === 0) {
        return;
    }

    if ($post_id_array === 0 && $attach_id_array !== 0) {
        $post_id_array = array();

        if (!is_array($attach_id_array)) {
            if (false !== strpos($attach_id_array, ', ')) {
                $attach_id_array = explode(', ', $attach_id_array);
            } elseif (strstr($attach_id_array, ',')) {
                $attach_id_array = explode(',', $attach_id_array);
            } else {
                $attach_id = (int)$attach_id_array;
                $attach_id_array = array();
                $attach_id_array[] = $attach_id;
            }
        }

        // Get the post_ids to fill the array
        $p_id = 'post_id';

        $sql = "SELECT $p_id
			FROM " . BB_ATTACHMENTS . '
				WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
			GROUP BY $p_id";

        if (!($result = OLD_DB()->sql_query($sql))) {
            bb_die('Could not select ids');
        }

        $num_post_list = OLD_DB()->num_rows($result);

        if ($num_post_list == 0) {
            OLD_DB()->sql_freeresult($result);
            return;
        }

        while ($row = OLD_DB()->sql_fetchrow($result)) {
            $post_id_array[] = (int)$row[$p_id];
        }
        OLD_DB()->sql_freeresult($result);
    }

    if (!is_array($post_id_array)) {
        if (trim($post_id_array) == '') {
            return;
        }

        if (false !== strpos($post_id_array, ', ')) {
            $post_id_array = explode(', ', $post_id_array);
        } elseif (strstr($post_id_array, ',')) {
            $post_id_array = explode(',', $post_id_array);
        } else {
            $post_id = (int)$post_id_array;

            $post_id_array = array();
            $post_id_array[] = $post_id;
        }
    }

    if (!count($post_id_array)) {
        return;
    }

    // First of all, determine the post id and attach_id
    if ($attach_id_array === 0) {
        $attach_id_array = array();

        // Get the attach_ids to fill the array
        $whereclause = 'WHERE post_id IN (' . implode(', ', $post_id_array) . ')';

        $sql = 'SELECT attach_id
			FROM ' . BB_ATTACHMENTS . " $whereclause
			GROUP BY attach_id";

        if (!($result = OLD_DB()->sql_query($sql))) {
            bb_die('Could not select attachment id #1');
        }

        $num_attach_list = OLD_DB()->num_rows($result);

        if ($num_attach_list == 0) {
            OLD_DB()->sql_freeresult($result);
            return;
        }

        while ($row = OLD_DB()->sql_fetchrow($result)) {
            $attach_id_array[] = (int)$row['attach_id'];
        }
        OLD_DB()->sql_freeresult($result);
    }

    if (!is_array($attach_id_array)) {
        if (false !== strpos($attach_id_array, ', ')) {
            $attach_id_array = explode(', ', $attach_id_array);
        } elseif (strstr($attach_id_array, ',')) {
            $attach_id_array = explode(',', $attach_id_array);
        } else {
            $attach_id = (int)$attach_id_array;

            $attach_id_array = array();
            $attach_id_array[] = $attach_id;
        }
    }

    if (!count($attach_id_array)) {
        return;
    }

    $sql_id = 'post_id';

    if (count($post_id_array) && count($attach_id_array)) {
        $sql = 'DELETE FROM ' . BB_ATTACHMENTS . '
			WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
				AND $sql_id IN (" . implode(', ', $post_id_array) . ')';

        if (!OLD_DB()->sql_query($sql)) {
            bb_die(trans('messages.ERROR_DELETED_ATTACHMENTS'));
        }

        //bt
        if ($sql_id == 'post_id') {
            $sql = 'SELECT topic_id FROM ' . BB_BT_TORRENTS . ' WHERE attach_id IN(' . implode(',', $attach_id_array) . ')';

            if (!$result = OLD_DB()->sql_query($sql)) {
                bb_die(trans('messages.ERROR_DELETED_ATTACHMENTS'));
            }

            $torrents_sql = array();

            while ($row = OLD_DB()->sql_fetchrow($result)) {
                $torrents_sql[] = $row['topic_id'];
            }

            if ($torrents_sql = implode(',', $torrents_sql)) {
                // Remove peers from tracker
                $sql = 'DELETE FROM ' . BB_BT_TRACKER . "
					WHERE topic_id IN($torrents_sql)";

                if (!OLD_DB()->sql_query($sql)) {
                    bb_die('Could not delete peers');
                }
            }
            // Delete torrents
            $sql = 'DELETE FROM ' . BB_BT_TORRENTS . '
				WHERE attach_id IN(' . implode(',', $attach_id_array) . ')';

            if (!OLD_DB()->sql_query($sql)) {
                bb_die(trans('messages.ERROR_DELETED_ATTACHMENTS'));
            }
        }
        //bt end

        for ($i = 0, $iMax = count($attach_id_array); $i < $iMax; $i++) {
            $sql = 'SELECT attach_id
				FROM ' . BB_ATTACHMENTS . '
						WHERE attach_id = ' . (int)$attach_id_array[$i];

            if (!($result = OLD_DB()->sql_query($sql))) {
                bb_die('Could not select Attachment id #2');
            }

            $num_rows = OLD_DB()->num_rows($result);
            OLD_DB()->sql_freeresult($result);

            if ($num_rows == 0) {
                $sql = 'SELECT attach_id, physical_filename, thumbnail
						FROM ' . BB_ATTACHMENTS_DESC . '
							WHERE attach_id = ' . (int)$attach_id_array[$i];

                if (!($result = OLD_DB()->sql_query($sql))) {
                    bb_die('Could not query attach description table');
                }
                $num_rows = OLD_DB()->num_rows($result);

                if ($num_rows != 0) {
                    $num_attach = $num_rows;
                    $attachments = OLD_DB()->sql_fetchrowset($result);
                    OLD_DB()->sql_freeresult($result);

                    // delete attachments
                    for ($j = 0; $j < $num_attach; $j++) {
                        unlink_attach($attachments[$j]['physical_filename']);

                        if ((int)$attachments[$j]['thumbnail'] == 1) {
                            unlink_attach($attachments[$j]['physical_filename'], MODE_THUMBNAIL);
                        }

                        $sql = 'DELETE FROM ' . BB_ATTACHMENTS_DESC . ' WHERE attach_id = ' . (int)$attachments[$j]['attach_id'];

                        if (!OLD_DB()->sql_query($sql)) {
                            bb_die(trans('messages.ERROR_DELETED_ATTACHMENTS'));
                        }
                    }
                } else {
                    OLD_DB()->sql_freeresult($result);
                }
            }
        }
    }

    // Now Sync the Topic/PM
    if (count($post_id_array)) {
        $sql = 'SELECT topic_id
			FROM ' . BB_POSTS . '
			WHERE post_id IN (' . implode(', ', $post_id_array) . ')
			GROUP BY topic_id';

        if (!($result = OLD_DB()->sql_query($sql))) {
            bb_die('Could not select topic id');
        }

        while ($row = OLD_DB()->sql_fetchrow($result)) {
            attachment_sync_topic($row['topic_id']);
        }
        OLD_DB()->sql_freeresult($result);
    }
}
