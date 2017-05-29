<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$allowed_extensions = array();
$display_categories = array();
$download_modes = array();
$upload_icons = array();
$attachments = array();

/**
 * Create needed arrays for Extension Assignments
 */
function init_complete_extensions_data()
{
    global $allowed_extensions, $display_categories, $download_modes, $upload_icons;

    if (!$extension_informations = get_extension_informations()) {
        $extension_informations = $GLOBALS['datastore']->update('attach_extensions'); //get_extension_informations()
        $extension_informations = get_extension_informations();
    }
    $allowed_extensions = array();

    for ($i = 0, $size = count($extension_informations); $i < $size; $i++) {
        $extension = strtolower(trim($extension_informations[$i]['extension']));
        $allowed_extensions[] = $extension;
        $display_categories[$extension] = (int)$extension_informations[$i]['cat_id'];
        $download_modes[$extension] = (int)$extension_informations[$i]['download_mode'];
        $upload_icons[$extension] = trim($extension_informations[$i]['upload_icon']);
    }
}

/**
 * Writing Data into plain Template Vars
 */
function init_display_template($template_var, $replacement, $filename = 'viewtopic_attach.tpl')
{
    global $template;

    // This function is adapted from the old template class
    // I wish i had the functions from the 3.x one. :D (This class rocks, can't await to use it in Mods)

    // Handle Attachment Informations
    if (!isset($template->uncompiled_code[$template_var]) && empty($template->uncompiled_code[$template_var])) {
        // If we don't have a file assigned to this handle, die.
        if (!isset($template->files[$template_var])) {
            die("Template->loadfile(): No file specified for handle $template_var");
        }

        $filename_2 = $template->files[$template_var];

        $str = file_get_contents($filename_2);
        if (empty($str)) {
            die("Template->loadfile(): File $filename_2 for handle $template_var is empty");
        }

        $template->uncompiled_code[$template_var] = $str;
    }

    $complete_filename = $filename;
    if ($complete_filename[0] != '/') {
        $complete_filename = $template->root . '/' . $complete_filename;
    }

    if (!file_exists($complete_filename)) {
        die("Template->make_filename(): Error - file $complete_filename does not exist");
    }

    $content = file_get_contents($complete_filename);
    if (empty($content)) {
        die('Template->loadfile(): File ' . $complete_filename . ' is empty');
    }

    // replace $replacement with uncompiled code in $filename
    $template->uncompiled_code[$template_var] = str_replace($replacement, $content, $template->uncompiled_code[$template_var]);
}

/**
 * Display Attachments in Posts
 */
function display_post_attachments($post_id, $switch_attachment)
{
    global $attach_config, $is_auth;

    if ((int)$switch_attachment == 0 || (int)$attach_config['disable_mod']) {
        return;
    }

    if ($is_auth['auth_download'] && $is_auth['auth_view']) {
        display_attachments($post_id);
    }
}

/**
 * Initializes some templating variables for displaying Attachments in Posts
 */
function init_display_post_attachments($switch_attachment)
{
    global $attach_config, $is_auth, $template, $lang, $postrow, $total_posts, $attachments, $forum_row, $t_data;

    if (empty($t_data) && !empty($forum_row)) {
        $switch_attachment = $forum_row['topic_attachment'];
    }

    if ((int)$switch_attachment == 0 || (int)$attach_config['disable_mod'] || (!($is_auth['auth_download'] && $is_auth['auth_view']))) {
        init_display_template('body', '{postrow.ATTACHMENTS}', 'viewtopic_attach_guest.tpl');
        return;
    }

    $post_id_array = array();

    for ($i = 0; $i < $total_posts; $i++) {
        if ($postrow[$i]['post_attachment'] == 1) {
            $post_id_array[] = (int)$postrow[$i]['post_id'];
        }
    }

    if (count($post_id_array) == 0) {
        return;
    }

    $rows = get_attachments_from_post($post_id_array);
    $num_rows = count($rows);

    if ($num_rows == 0) {
        return;
    }

    @reset($attachments);

    for ($i = 0; $i < $num_rows; $i++) {
        $attachments['_' . $rows[$i]['post_id']][] = $rows[$i];
        //bt
        if ($rows[$i]['tracker_status']) {
            if (defined('TORRENT_POST')) {
                bb_die('Multiple registered torrents in one topic<br /><br />first torrent found in post_id = ' . TORRENT_POST . '<br />current post_id = ' . $rows[$i]['post_id'] . '<br /><br />attachments info:<br /><pre style="text-align: left;">' . print_r($rows, true) . '</pre>');
            }
            define('TORRENT_POST', $rows[$i]['post_id']);
        }
        //bt end
    }

    init_display_template('body', '{postrow.ATTACHMENTS}');

    init_complete_extensions_data();
}

/**
 * END ATTACHMENT DISPLAY IN POSTS
 */

/**
 * Assign Variables and Definitions based on the fetched Attachments - internal
 * used by all displaying functions, the Data was collected before, it's only dependend on the template used. :)
 * before this function is usable, init_display_attachments have to be called for specific pages (pm, posting, review etc...)
 */
function display_attachments($post_id)
{
    global $template, $upload_dir, $userdata, $allowed_extensions, $display_categories, $download_modes, $lang, $attachments, $upload_icons, $attach_config;

    $num_attachments = @count($attachments['_' . $post_id]);

    if ($num_attachments == 0) {
        return;
    }

    $template->assign_block_vars('postrow.attach', array());

    for ($i = 0; $i < $num_attachments; $i++) {
        // Some basic things...
        $filename = $upload_dir . '/' . basename($attachments['_' . $post_id][$i]['physical_filename']);
        $thumbnail_filename = $upload_dir . '/' . THUMB_DIR . '/t_' . basename($attachments['_' . $post_id][$i]['physical_filename']);

        $upload_image = '';

        if ($attach_config['upload_img'] && empty($upload_icons[$attachments['_' . $post_id][$i]['extension']])) {
            $upload_image = '<img src="' . $attach_config['upload_img'] . '" alt="" border="0" />';
        } elseif (trim($upload_icons[$attachments['_' . $post_id][$i]['extension']]) != '') {
            $upload_image = '<img src="' . $upload_icons[$attachments['_' . $post_id][$i]['extension']] . '" alt="" border="0" />';
        }

        $filesize = humn_size($attachments['_' . $post_id][$i]['filesize']);

        $display_name = htmlspecialchars($attachments['_' . $post_id][$i]['real_filename']);
        $comment = htmlspecialchars($attachments['_' . $post_id][$i]['comment']);
        $comment = str_replace("\n", '<br />', $comment);

        $denied = false;

        // Admin is allowed to view forbidden Attachments, but the error-message is displayed too to inform the Admin
        if (!in_array($attachments['_' . $post_id][$i]['extension'], $allowed_extensions)) {
            $denied = true;

            $template->assign_block_vars('postrow.attach.denyrow', array(
                    'L_DENIED' => sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachments['_' . $post_id][$i]['extension']))
            );
        }

        if (!$denied || IS_ADMIN) {
            // define category
            $image = false;
            $thumbnail = false;
            $link = false;

            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && (int)$attach_config['img_display_inlined']) {
                if ((int)$attach_config['img_link_width'] != 0 || (int)$attach_config['img_link_height'] != 0) {
                    list($width, $height) = image_getdimension($filename);

                    if ($width == 0 && $height == 0) {
                        $image = true;
                    } else {
                        if ($width <= (int)$attach_config['img_link_width'] && $height <= (int)$attach_config['img_link_height']) {
                            $image = true;
                        }
                    }
                } else {
                    $image = true;
                }
            }

            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && $attachments['_' . $post_id][$i]['thumbnail'] == 1) {
                $thumbnail = true;
                $image = false;
            }

            if (!$image && !$thumbnail) {
                $link = true;
            }

            if ($image) {
                // Images
                if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
                    $img_source = BB_ROOT . DOWNLOAD_URL . $attachments['_' . $post_id][$i]['attach_id'];
                    $download_link = true;
                } else {
                    $img_source = $filename;
                    $download_link = false;
                }

                $template->assign_block_vars('postrow.attach.cat_images', array(
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => $img_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                ));

                // Directly Viewed Image ... update the download count
                if (!$download_link) {
                    $sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . '
						SET download_count = download_count + 1
						WHERE attach_id = ' . (int)$attachments['_' . $post_id][$i]['attach_id'];

                    if (!(DB()->sql_query($sql))) {
                        bb_die('Could not update attachment download count');
                    }
                }
            }

            if ($thumbnail) {
                // Images, but display Thumbnail
                if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
                    $thumb_source = BB_ROOT . DOWNLOAD_URL . $attachments['_' . $post_id][$i]['attach_id'] . '&thumb=1';
                } else {
                    $thumb_source = $thumbnail_filename;
                }

                $template->assign_block_vars('postrow.attach.cat_thumb_images', array(
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => BB_ROOT . DOWNLOAD_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'IMG_THUMB_SRC' => $thumb_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                ));
            }

            // bt
            if ($link && ($attachments['_' . $post_id][$i]['extension'] === TORRENT_EXT)) {
                include ATTACH_DIR . '/displaying_torrent.php';
            } elseif ($link) {
                $target_blank = ((@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT)) ? 'target="_blank"' : '';

                // display attachment
                $template->assign_block_vars('postrow.attach.attachrow', array(
                    'U_DOWNLOAD_LINK' => BB_ROOT . DOWNLOAD_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'DOWNLOAD_NAME' => $display_name,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                    'TARGET_BLANK' => $target_blank,
                    'DOWNLOAD_COUNT' => sprintf($lang['DOWNLOAD_NUMBER'], $attachments['_' . $post_id][$i]['download_count']),
                ));
            }
        }
    }
}
