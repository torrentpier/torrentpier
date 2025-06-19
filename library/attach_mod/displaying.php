<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$allowed_extensions = [];
$display_categories = [];
$download_modes = [];
$upload_icons = [];
$attachments = [];

/**
 * Create needed arrays for Extension Assignments
 */
function init_complete_extensions_data()
{
    global $allowed_extensions, $display_categories, $download_modes, $upload_icons;

    if (!$extension_informations = get_extension_informations()) {
        $GLOBALS['datastore']->update('attach_extensions');
        $extension_informations = get_extension_informations();
    }

    $allowed_extensions = [];
    for ($i = 0, $size = count($extension_informations); $i < $size; $i++) {
        $extension = strtolower(trim($extension_informations[$i]['extension']));
        // Get allowed extensions
        if ((int)$extension_informations[$i]['allow_group'] === 1) {
            $allowed_extensions[] = $extension;
        }
        $display_categories[$extension] = (int)$extension_informations[$i]['cat_id'];
        $download_modes[$extension] = (int)$extension_informations[$i]['download_mode'];
        $upload_icons[$extension] = trim($extension_informations[$i]['upload_icon']);
    }
}

/**
 * Render attachments HTML for a post
 */
function render_attachments($post_id)
{
    global $template, $upload_dir, $userdata, $allowed_extensions, $display_categories, $download_modes, $lang, $attachments, $upload_icons, $attach_config;
    
    // Debug: Check if required globals are available
    error_log("render_attachments: upload_dir=" . ($upload_dir ?? 'null') . ", attach_config=" . (isset($attach_config) ? 'set' : 'null') . ", template=" . (isset($template) ? 'set' : 'null'));
    
    // If attachments global is empty, try to load data directly
    if (empty($attachments['_' . $post_id])) {
        if (function_exists('get_attachments_from_post')) {
            $attachment_data = get_attachments_from_post([$post_id]);
            if (!empty($attachment_data)) {
                $attachments['_' . $post_id] = $attachment_data;
                error_log("render_attachments: Loaded " . count($attachment_data) . " attachments for post $post_id");
                
                // Also ensure we have required globals initialized
                if (empty($allowed_extensions) && function_exists('init_complete_extensions_data')) {
                    init_complete_extensions_data();
                }
            } else {
                error_log("render_attachments: get_attachments_from_post returned empty for post $post_id");
            }
        } else {
            error_log("render_attachments: get_attachments_from_post function not found");
        }
    } else {
        error_log("render_attachments: Found existing attachment data for post $post_id");
    }
    
    $num_attachments = @count($attachments['_' . $post_id]);
    
    if ($num_attachments == 0) {
        // Debug: Show what attachments we have
        $debug_keys = array_keys($attachments);
        return '<!-- No attachments found for post_id: ' . $post_id . '. Available keys: ' . implode(', ', $debug_keys) . '. Attachment count: ' . count($attachments) . ' -->';
    }
    
    // Create a new template instance just for rendering attachments
    $template_root = $template ? $template->root : TEMPLATES_DIR . '/default';
    $attach_template = \TorrentPier\Template\Template::getInstance($template_root);
    $attach_template->set_filenames(['body' => 'viewtopic_attach.tpl']);
    
    // Pass necessary variables to the attachment template
    if ($template) {
        $attach_template->assign_vars($template->vars);
    }
    $attach_template->lang =& $lang;
    
    // Start building attachment blocks
    $attach_template->assign_block_vars('attach', []);
    
    for ($i = 0; $i < $num_attachments; $i++) {
        $filename = $upload_dir . '/' . basename($attachments['_' . $post_id][$i]['physical_filename']);

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

            $attach_template->assign_block_vars('attach.denyrow', ['L_DENIED' => sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachments['_' . $post_id][$i]['extension'])]);
        }

        if (!$denied || IS_ADMIN) {
            // define category
            $image = false;
            $thumbnail = false;
            $link = false;

            // Shows the images in topic
            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && (int)$attach_config['img_display_inlined']) {
                if ((int)$attach_config['img_link_width'] != 0 || (int)$attach_config['img_link_height'] != 0) {
                    // Get image sizes
                    [$width, $height] = getimagesize($filename);

                    // Check if image sizes is allowed
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

            // Checks if image is thumbnail
            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && $attachments['_' . $post_id][$i]['thumbnail'] == 1) {
                $thumbnail = true;
                $image = false;
            }

            // Checks whether the image should be displayed as a link
            if (!$image && !$thumbnail) {
                $link = true;
            }

            if ($image) {
                // Images
                if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
                    $img_source = BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'];
                    $download_link = true;
                } else {
                    $img_source = $filename;
                    $download_link = false;
                }

                $attach_template->assign_block_vars('attach.cat_images', [
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => $img_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment
                ]);

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
                    $thumb_source = BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'] . '&thumb=1';
                } else {
                    // Get the thumbnail image
                    $thumbnail_filename = $upload_dir . '/' . THUMB_DIR . '/t_' . basename($attachments['_' . $post_id][$i]['physical_filename']);

                    // Checks the thumbnail existence
                    if (!is_file($thumbnail_filename)) {
                        continue;
                    }

                    $thumb_source = $thumbnail_filename;
                }

                $attach_template->assign_block_vars('attach.cat_thumb_images', [
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'IMG_THUMB_SRC' => $thumb_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                    'DOWNLOAD_COUNT' => declension((int)$attachments['_' . $post_id][$i]['download_count'], 'times'),
                ]);
            }

            // bt
            if ($link && ($attachments['_' . $post_id][$i]['extension'] === TORRENT_EXT)) {
                // For torrents, we need special handling
                // Temporarily swap the global template variable
                $saved_global_template = $template;
                $template = $attach_template;
                
                // Include the torrent display file (it will use the swapped template)
                if (file_exists(ATTACH_DIR . '/displaying_torrent.php')) {
                    include ATTACH_DIR . '/displaying_torrent.php';
                }
                
                // Restore the original template
                $template = $saved_global_template;
            } elseif ($link) {
                $target_blank = ((@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT)) ? 'target="_blank"' : '';

                // display attachment
                $attach_template->assign_block_vars('attach.attachrow', [
                    'U_DOWNLOAD_LINK' => BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'DOWNLOAD_NAME' => $display_name,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                    'TARGET_BLANK' => $target_blank,
                    'IS_IMAGE' => (bool)$target_blank,
                    'DOWNLOAD_COUNT' => declension((int)$attachments['_' . $post_id][$i]['download_count'], 'times')
                ]);
            }
        }
    }
    
    // Capture output
    ob_start();
    $attach_template->pparse('body');
    $html = ob_get_clean();
    
    return $html;
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
    
    // Debug: Log that this function is called
    error_log("init_display_post_attachments called with switch_attachment: $switch_attachment");

    if (empty($t_data) && !empty($forum_row)) {
        $switch_attachment = $forum_row['topic_attachment'];
    }

    if ((int)$switch_attachment == 0 || (int)$attach_config['disable_mod']) {
        return;
    }

    $post_id_array = [];

    for ($i = 0; $i < $total_posts; $i++) {
        if ($postrow[$i]['post_attachment'] == 1) {
            $post_id_array[] = (int)$postrow[$i]['post_id'];
        }
    }

    if (count($post_id_array) == 0) {
        // Debug: Log when no posts have attachments
        error_log("init_display_post_attachments: No posts with attachments found. Total posts: $total_posts");
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

    $template->assign_block_vars('postrow.attach', []);

    for ($i = 0; $i < $num_attachments; $i++) {
        $filename = $upload_dir . '/' . basename($attachments['_' . $post_id][$i]['physical_filename']);

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

            $template->assign_block_vars('postrow.attach.denyrow', ['L_DENIED' => sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachments['_' . $post_id][$i]['extension'])]);
        }

        if (!$denied || IS_ADMIN) {
            // define category
            $image = false;
            $thumbnail = false;
            $link = false;

            // Shows the images in topic
            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && (int)$attach_config['img_display_inlined']) {
                if ((int)$attach_config['img_link_width'] != 0 || (int)$attach_config['img_link_height'] != 0) {
                    // Get image sizes
                    [$width, $height] = getimagesize($filename);

                    // Check if image sizes is allowed
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

            // Checks if image is thumbnail
            if (@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT && $attachments['_' . $post_id][$i]['thumbnail'] == 1) {
                $thumbnail = true;
                $image = false;
            }

            // Checks whether the image should be displayed as a link
            if (!$image && !$thumbnail) {
                $link = true;
            }

            if ($image) {
                // Images
                if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
                    $img_source = BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'];
                    $download_link = true;
                } else {
                    $img_source = $filename;
                    $download_link = false;
                }

                $template->assign_block_vars('postrow.attach.cat_images', [
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => $img_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment
                ]);

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
                    $thumb_source = BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'] . '&thumb=1';
                } else {
                    // Get the thumbnail image
                    $thumbnail_filename = $upload_dir . '/' . THUMB_DIR . '/t_' . basename($attachments['_' . $post_id][$i]['physical_filename']);

                    // Checks the thumbnail existence
                    if (!is_file($thumbnail_filename)) {
                        continue;
                    }

                    $thumb_source = $thumbnail_filename;
                }

                $template->assign_block_vars('postrow.attach.cat_thumb_images', [
                    'DOWNLOAD_NAME' => $display_name,
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'IMG_SRC' => BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'IMG_THUMB_SRC' => $thumb_source,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                    'DOWNLOAD_COUNT' => declension((int)$attachments['_' . $post_id][$i]['download_count'], 'times'),
                ]);
            }

            // bt
            if ($link && ($attachments['_' . $post_id][$i]['extension'] === TORRENT_EXT)) {
                include ATTACH_DIR . '/displaying_torrent.php';
            } elseif ($link) {
                $target_blank = ((@(int)$display_categories[$attachments['_' . $post_id][$i]['extension']] == IMAGE_CAT)) ? 'target="_blank"' : '';

                // display attachment
                $template->assign_block_vars('postrow.attach.attachrow', [
                    'U_DOWNLOAD_LINK' => BB_ROOT . DL_URL . $attachments['_' . $post_id][$i]['attach_id'],
                    'S_UPLOAD_IMAGE' => $upload_image,
                    'DOWNLOAD_NAME' => $display_name,
                    'FILESIZE' => $filesize,
                    'COMMENT' => $comment,
                    'TARGET_BLANK' => $target_blank,
                    'IS_IMAGE' => (bool)$target_blank,
                    'DOWNLOAD_COUNT' => declension((int)$attachments['_' . $post_id][$i]['download_count'], 'times')
                ]);
            }
        }
    }
}
