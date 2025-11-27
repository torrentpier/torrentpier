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
 * Class Attach
 * @package TorrentPier\Legacy
 */
class Attach
{
    public $post_attach = false;
    public $attach_filename = '';
    public $filename = '';
    public $type = '';

    /**
     * Upload status code
     *
     * @var int
     */
    public int $error = UPLOAD_ERR_OK;

    public $extension = '';
    public $file_comment = '';

    public $attachment_id_list;
    public $attachment_comment_list;
    public $attachment_filesize_list;
    public $attachment_filetime_list;
    public $attachment_filename_list;
    public $attachment_extension_list;
    public $attachment_mimetype_list;
    public $attachment_list;
    public $attachment_thumbnail_list;

    public $num_attachments = 0; // number of attachments in message
    public $filesize = 0;
    public $filetime = 0;
    public $thumbnail = 0;
    public $page = 0; // On which page we are on ? This should be filled by child classes.

    // Switches
    public $add_attachment_body = 0;
    public $posted_attachments_body = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add_attachment_body = get_var('add_attachment_body', 0);
        $this->posted_attachments_body = get_var('posted_attachments_body', 0);

        $this->file_comment = get_var('filecomment', '');
        $this->attachment_id_list = get_var('attach_id_list', [0]);
        $this->attachment_comment_list = get_var('comment_list', ['']);
        $this->attachment_filesize_list = get_var('filesize_list', [0]);
        $this->attachment_filetime_list = get_var('filetime_list', [0]);
        $this->attachment_filename_list = get_var('filename_list', ['']);
        $this->attachment_extension_list = get_var('extension_list', ['']);
        $this->attachment_mimetype_list = get_var('mimetype_list', ['']);

        $this->filename = (isset($_FILES['fileupload']['name']) && $_FILES['fileupload']['name'] !== 'none') ? trim(stripslashes($_FILES['fileupload']['name'])) : '';

        $this->attachment_list = get_var('attachment_list', ['']);
        $this->attachment_thumbnail_list = get_var('attach_thumbnail_list', [0]);
    }

    /**
     * Handle all modes... (intern)
     * @private
     */
    public function handle_attachments($mode)
    {
        global $is_auth, $attach_config, $refresh, $update_attachment, $post_id, $submit, $preview, $error, $error_msg, $lang;

        $sql_id = 'post_id';

        // nothing, if the user is not authorized
        if (!$is_auth['auth_attachments']) {
            return false;
        }

        // Init Vars
        $attachments = [];

        if (!$refresh) {
            $add = isset($_POST['add_attachment']);
            $delete = isset($_POST['del_attachment']);
            $edit = isset($_POST['edit_comment']);
            $update_attachment = isset($_POST['update_attachment']);
            $del_thumbnail = isset($_POST['del_thumbnail']);

            $add_attachment_box = !empty($_POST['add_attachment_box']);
            $posted_attachments_box = !empty($_POST['posted_attachments_box']);

            $refresh = $add || $delete || $edit || $del_thumbnail || $update_attachment || $add_attachment_box || $posted_attachments_box;
        }

        // Get Attachments
        $attachments = get_attachments_from_post($post_id);

        $auth = $is_auth['auth_edit'] || $is_auth['auth_mod'];

        if (!$submit && $mode === 'editpost' && $auth) {
            if (!$refresh && !$preview && !$error) {
                foreach ($attachments as $attachment) {
                    $this->attachment_list[] = $attachment['physical_filename'];
                    $this->attachment_comment_list[] = $attachment['comment'];
                    $this->attachment_filename_list[] = $attachment['real_filename'];
                    $this->attachment_extension_list[] = $attachment['extension'];
                    $this->attachment_mimetype_list[] = $attachment['mimetype'];
                    $this->attachment_filesize_list[] = $attachment['filesize'];
                    $this->attachment_filetime_list[] = $attachment['filetime'];
                    $this->attachment_id_list[] = $attachment['attach_id'];
                }
            }
        }

        $this->num_attachments = is_countable($this->attachment_list) ? \count($this->attachment_list) : 0;

        if ($submit) {
            if ($mode === 'newtopic' || $mode === 'reply' || $mode === 'editpost') {
                if ($this->filename) {
                    if ($this->num_attachments < (int)$max_attachments) {
                        $this->upload_attachment();

                        if (!$error && $this->post_attach) {
                            array_unshift($this->attachment_list, $this->attach_filename);
                            array_unshift($this->attachment_comment_list, $this->file_comment);
                            array_unshift($this->attachment_filename_list, $this->filename);
                            array_unshift($this->attachment_extension_list, $this->extension);
                            array_unshift($this->attachment_mimetype_list, $this->type);
                            array_unshift($this->attachment_filesize_list, $this->filesize);
                            array_unshift($this->attachment_filetime_list, $this->filetime);
                            array_unshift($this->attachment_id_list, '0');

                            $this->file_comment = '';
                            $this->post_attach = false;
                        }
                    } else {
                        $error = true;
                        if (!empty($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= sprintf($lang['TOO_MANY_ATTACHMENTS'], (int)$max_attachments);
                    }
                }
            }
        }

        if ($preview || $refresh || $error) {
            $delete_attachment = isset($_POST['del_attachment']);
            $delete_thumbnail = isset($_POST['del_thumbnail']);

            $add_attachment = isset($_POST['add_attachment']);
            $edit_attachment = isset($_POST['edit_comment']);
            $update_attachment = isset($_POST['update_attachment']);

            // Perform actions on temporary attachments
            if ($delete_attachment || $delete_thumbnail) {
                // store old values
                $actual_id_list = get_var('attach_id_list', [0]);
                $actual_comment_list = get_var('comment_list', ['']);
                $actual_filename_list = get_var('filename_list', ['']);
                $actual_extension_list = get_var('extension_list', ['']);
                $actual_mimetype_list = get_var('mimetype_list', ['']);
                $actual_filesize_list = get_var('filesize_list', [0]);
                $actual_filetime_list = get_var('filetime_list', [0]);

                $actual_list = get_var('attachment_list', ['']);
                $actual_thumbnail_list = get_var('attach_thumbnail_list', [0]);

                // clean values
                $this->attachment_list = [];
                $this->attachment_comment_list = [];
                $this->attachment_filename_list = [];
                $this->attachment_extension_list = [];
                $this->attachment_mimetype_list = [];
                $this->attachment_filesize_list = [];
                $this->attachment_filetime_list = [];
                $this->attachment_id_list = [];
                $this->attachment_thumbnail_list = [];

                // restore values :)
                if (isset($_POST['attachment_list'])) {
                    for ($i = 0, $iMax = is_countable($actual_list) ? \count($actual_list) : 0; $i < $iMax; $i++) {
                        $restore = false;
                        $del_thumb = false;

                        if ($delete_thumbnail) {
                            if (!isset($_POST['del_thumbnail'][$actual_list[$i]])) {
                                $restore = true;
                            } else {
                                $del_thumb = true;
                            }
                        }
                        if ($delete_attachment) {
                            if (!isset($_POST['del_attachment'][$actual_list[$i]])) {
                                $restore = true;
                            }
                        }

                        if ($restore) {
                            $this->attachment_list[] = $actual_list[$i];
                            $this->attachment_comment_list[] = $actual_comment_list[$i];
                            $this->attachment_filename_list[] = $actual_filename_list[$i];
                            $this->attachment_extension_list[] = $actual_extension_list[$i];
                            $this->attachment_mimetype_list[] = $actual_mimetype_list[$i];
                            $this->attachment_filesize_list[] = $actual_filesize_list[$i];
                            $this->attachment_filetime_list[] = $actual_filetime_list[$i];
                            $this->attachment_id_list[] = $actual_id_list[$i];
                            $this->attachment_thumbnail_list[] = $actual_thumbnail_list[$i];
                        } elseif (!$del_thumb) {
                            // delete selected attachment
                            if ($actual_id_list[$i] == '0') {
                                unlink_attach($actual_list[$i]);

                                if ($actual_thumbnail_list[$i] == 1) {
                                    unlink_attach($actual_list[$i], MODE_THUMBNAIL);
                                }
                            } else {
                                delete_attachment($post_id, $actual_id_list[$i], $this->page);
                            }
                        }
                    }
                }
            } elseif ($edit_attachment || $update_attachment || $add_attachment || $preview) {
                if ($edit_attachment) {
                    $actual_comment_list = get_var('comment_list', ['']);

                    $this->attachment_comment_list = [];

                    for ($i = 0, $iMax = is_countable($this->attachment_list) ? \count($this->attachment_list) : 0; $i < $iMax; $i++) {
                        $this->attachment_comment_list[$i] = $actual_comment_list[$i];
                    }
                }

                if ($update_attachment) {
                    if (empty($this->filename)) {
                        $error = true;
                        if (!empty($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= $lang['ERROR_EMPTY_ADD_ATTACHBOX'];
                    }

                    $this->upload_attachment();

                    if (!$error) {
                        $actual_id_list = get_var('attach_id_list', [0]);

                        $attachment_id = 0;
                        $actual_element = 0;

                        for ($i = 0, $iMax = is_countable($actual_id_list) ? \count($actual_id_list) : 0; $i < $iMax; $i++) {
                            if (isset($_POST['update_attachment'][$actual_id_list[$i]])) {
                                $attachment_id = (int)$actual_id_list[$i];
                                $actual_element = $i;
                            }
                        }

                        // Get current information to delete the Old Attachment
                        $sql = 'SELECT physical_filename, comment
							FROM ' . BB_ATTACHMENTS_DESC . '
							WHERE attach_id = ' . (int)$attachment_id;

                        if (!($result = DB()->sql_query($sql))) {
                            bb_die('Unable to select old attachment entry');
                        }

                        if (DB()->num_rows($result) != 1) {
                            $error = true;
                            if (!empty($error_msg)) {
                                $error_msg .= '<br />';
                            }
                            $error_msg .= $lang['ERROR_MISSING_OLD_ENTRY'];
                        }

                        $row = DB()->sql_fetchrow($result);
                        DB()->sql_freeresult($result);

                        $comment = !trim($this->file_comment) ? trim($row['comment']) : trim($this->file_comment);

                        // Update Entry
                        $sql_ary = [
                            'physical_filename' => (string)basename($this->attach_filename),
                            'real_filename' => (string)basename($this->filename),
                            'comment' => (string)$comment,
                            'extension' => (string)strtolower($this->extension),
                            'mimetype' => (string)strtolower($this->type),
                            'filesize' => (int)$this->filesize,
                            'filetime' => (int)$this->filetime,
                        ];

                        $sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . ' SET ' . DB()->build_array('UPDATE', $sql_ary) . '
							WHERE attach_id = ' . (int)$attachment_id;

                        if (!(DB()->sql_query($sql))) {
                            bb_die('Unable to update the attachment');
                        }

                        // Delete the Old Attachment
                        unlink_attach($row['physical_filename']);

                        // todo: log action - torrent updated

                        //bt
                        if ($this->attachment_extension_list[$actual_element] === TORRENT_EXT && $attachments[$actual_element]['tracker_status']) {
                            Torrent::tracker_unregister($attachment_id);
                        }
                        //bt end

                        // Make sure it is displayed
                        $this->attachment_list[$actual_element] = $this->attach_filename;
                        $this->attachment_comment_list[$actual_element] = $comment;
                        $this->attachment_filename_list[$actual_element] = $this->filename;
                        $this->attachment_extension_list[$actual_element] = $this->extension;
                        $this->attachment_mimetype_list[$actual_element] = $this->type;
                        $this->attachment_filesize_list[$actual_element] = $this->filesize;
                        $this->attachment_filetime_list[$actual_element] = $this->filetime;
                        $this->attachment_id_list[$actual_element] = $actual_id_list[$actual_element];
                        $this->file_comment = '';
                    }
                }

                if (($add_attachment || $preview) && !empty($this->filename)) {
                    if ($this->num_attachments < (int)$max_attachments) {
                        $this->upload_attachment();

                        if (!$error) {
                            array_unshift($this->attachment_list, $this->attach_filename);
                            array_unshift($this->attachment_comment_list, $this->file_comment);
                            array_unshift($this->attachment_filename_list, $this->filename);
                            array_unshift($this->attachment_extension_list, $this->extension);
                            array_unshift($this->attachment_mimetype_list, $this->type);
                            array_unshift($this->attachment_filesize_list, $this->filesize);
                            array_unshift($this->attachment_filetime_list, $this->filetime);
                            array_unshift($this->attachment_id_list, '0');

                            $this->file_comment = '';
                        }
                    } else {
                        $error = true;
                        if (!empty($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= sprintf($lang['TOO_MANY_ATTACHMENTS'], (int)$max_attachments);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Basic Insert Attachment Handling for all Message Types
     */
    public function do_insert_attachment($mode, $message_type, $message_id)
    {
        global $upload_dir;

        if ((int)$message_id < 0) {
            return false;
        }

        global $post_info, $userdata;

        $post_id = (int)$message_id;
        $user_id_1 = (isset($post_info['poster_id'])) ? (int)$post_info['poster_id'] : 0;

        if (!$user_id_1) {
            $user_id_1 = (int)$userdata['user_id'];
        }

        if ($mode === 'attach_list') {
            for ($i = 0, $iMax = is_countable($this->attachment_list) ? \count($this->attachment_list) : 0; $i < $iMax; $i++) {
                if ($this->attachment_id_list[$i]) {
                    //bt
                    if ($this->attachment_extension_list[$i] === TORRENT_EXT && !\defined('TORRENT_ATTACH_ID')) {
                        \define('TORRENT_ATTACH_ID', $this->attachment_id_list[$i]);
                    }
                    //bt end

                    // update entry in db if attachment already stored in db and filespace
                    $sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . "
						SET comment = '" . DB()->escape($this->attachment_comment_list[$i]) . "'
						WHERE attach_id = " . $this->attachment_id_list[$i];

                    if (!(DB()->sql_query($sql))) {
                        bb_die('Unable to update the file comment');
                    }
                } else {
                    if (empty($this->attachment_mimetype_list[$i]) && $this->attachment_extension_list[$i] === TORRENT_EXT) {
                        $this->attachment_mimetype_list[$i] = TORRENT_MIMETYPE;
                    }

                    // insert attachment into db
                    $sql_ary = [
                        'physical_filename' => (string)basename($this->attachment_list[$i]),
                        'real_filename' => (string)basename($this->attachment_filename_list[$i]),
                        'comment' => (string)@$this->attachment_comment_list[$i],
                        'extension' => (string)strtolower($this->attachment_extension_list[$i]),
                        'mimetype' => (string)strtolower($this->attachment_mimetype_list[$i]),
                        'filesize' => (int)$this->attachment_filesize_list[$i],
                        'filetime' => (int)$this->attachment_filetime_list[$i],
                    ];

                    $sql = 'INSERT INTO ' . BB_ATTACHMENTS_DESC . ' ' . DB()->build_array('INSERT', $sql_ary);

                    if (!(DB()->sql_query($sql))) {
                        bb_die('Could not store Attachment.<br />Your ' . $message_type . ' has been stored');
                    }

                    $attach_id = DB()->sql_nextid();

                    //bt
                    if ($this->attachment_extension_list[$i] === TORRENT_EXT && !\defined('TORRENT_ATTACH_ID')) {
                        \define('TORRENT_ATTACH_ID', $attach_id);
                    }
                    //bt end

                    $sql_ary = [
                        'attach_id' => (int)$attach_id,
                        'post_id' => (int)$post_id,
                        'user_id_1' => (int)$user_id_1,
                    ];

                    $sql = 'INSERT INTO ' . BB_ATTACHMENTS . ' ' . DB()->build_array('INSERT', $sql_ary);

                    if (!(DB()->sql_query($sql))) {
                        bb_die('Could not store Attachment.<br />Your ' . $message_type . ' has been stored');
                    }
                }
            }

            return true;
        }

        if ($mode === 'last_attachment') {
            if ($this->post_attach && !isset($_POST['update_attachment'])) {
                // insert attachment into db, here the user submited it directly
                $sql_ary = [
                    'physical_filename' => (string)basename($this->attach_filename),
                    'real_filename' => (string)basename($this->filename),
                    'comment' => (string)$this->file_comment,
                    'extension' => (string)strtolower($this->extension),
                    'mimetype' => (string)strtolower($this->type),
                    'filesize' => (int)$this->filesize,
                    'filetime' => (int)$this->filetime,
                ];

                $sql = 'INSERT INTO ' . BB_ATTACHMENTS_DESC . ' ' . DB()->build_array('INSERT', $sql_ary);

                // Inform the user that his post has been created, but nothing is attached
                if (!(DB()->sql_query($sql))) {
                    bb_die('Could not store Attachment.<br />Your ' . $message_type . ' has been stored');
                }

                $attach_id = DB()->sql_nextid();

                $sql_ary = [
                    'attach_id' => (int)$attach_id,
                    'post_id' => (int)$post_id,
                    'user_id_1' => (int)$user_id_1,
                ];

                $sql = 'INSERT INTO ' . BB_ATTACHMENTS . ' ' . DB()->build_array('INSERT', $sql_ary);

                if (!(DB()->sql_query($sql))) {
                    bb_die('Could not store Attachment.<br />Your ' . $message_type . ' has been stored');
                }
            }
        }
    }

    /**
     * Attachment Mod entry switch/output (intern)
     * @private
     */
    public function display_attachment_bodies()
    {
        global $attach_config, $is_auth, $lang, $template, $upload_dir, $forum_id;

        // Choose what to display
        $value_add = $value_posted = 0;

        $this->add_attachment_body = 1;
        $this->posted_attachments_body = 1;

        $s_hidden = '<input type="hidden" name="add_attachment_body" value="' . $value_add . '" />';
        $s_hidden .= '<input type="hidden" name="posted_attachments_body" value="' . $value_posted . '" />';

        $template->assign_vars([
            'ADD_ATTACH_HIDDEN_FIELDS' => $s_hidden,
        ]);

        $attachments = [];

        if ($this->attachment_list) {
            $hidden = '';
            for ($i = 0, $iMax = is_countable($this->attachment_list) ? \count($this->attachment_list) : 0; $i < $iMax; $i++) {
                $hidden .= '<input type="hidden" name="attachment_list[]" value="' . $this->attachment_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="filename_list[]" value="' . $this->attachment_filename_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="extension_list[]" value="' . $this->attachment_extension_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="mimetype_list[]" value="' . $this->attachment_mimetype_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="filesize_list[]" value="' . @$this->attachment_filesize_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="filetime_list[]" value="' . @$this->attachment_filetime_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="attach_id_list[]" value="' . @$this->attachment_id_list[$i] . '" />';
                $hidden .= '<input type="hidden" name="attach_thumbnail_list[]" value="' . @$this->attachment_thumbnail_list[$i] . '" />';

                if (!$this->posted_attachments_body || !$this->attachment_list) {
                    $hidden .= '<input type="hidden" name="comment_list[]" value="' . $this->attachment_comment_list[$i] . '" />';
                }
            }
            $template->assign_var('POSTED_ATTACHMENTS_HIDDEN_FIELDS', $hidden);
        }

        if ($this->add_attachment_body) {
            $template->assign_vars([
                'TPL_ADD_ATTACHMENT' => true,
                'FILE_COMMENT' => htmlspecialchars($this->file_comment),
                'FILESIZE' => $attach_config['max_filesize'],
                'FILENAME' => htmlspecialchars($this->filename),
                'S_FORM_ENCTYPE' => 'enctype="multipart/form-data"',
            ]);
        }

        if ($this->posted_attachments_body && $this->attachment_list) {
            $template->assign_vars([
                'TPL_POSTED_ATTACHMENTS' => true,
            ]);

            for ($i = 0, $iMax = is_countable($this->attachment_list) ? \count($this->attachment_list) : 0; $i < $iMax; $i++) {
                if (@$this->attachment_id_list[$i] == 0) {
                    $download_link = $upload_dir . '/' . basename($this->attachment_list[$i]);
                } else {
                    $download_link = BB_ROOT . DL_URL . $this->attachment_id_list[$i];
                }

                $template->assign_block_vars('attach_row', [
                    'FILE_NAME' => @htmlspecialchars($this->attachment_filename_list[$i]),
                    'ATTACH_FILENAME' => @$this->attachment_list[$i],
                    'FILE_COMMENT' => @htmlspecialchars($this->attachment_comment_list[$i]),
                    'ATTACH_ID' => @$this->attachment_id_list[$i],
                    'U_VIEW_ATTACHMENT' => $download_link,
                ]);

                if (@$this->attachment_id_list[$i]) {
                    $template->assign_block_vars('attach_row.switch_update_attachment', []);
                }
            }
        }

        $template->assign_var('ATTACHBOX');
    }

    /**
     * Upload an Attachment to Filespace (intern)
     */
    public function upload_attachment()
    {
        global $error, $error_msg, $lang, $attach_config, $userdata, $upload_dir, $forum_id;

        $this->post_attach = (bool)$this->filename;

        if ($this->post_attach) {
            $r_file = trim(basename($this->filename));
            $file = $_FILES['fileupload']['tmp_name'];
            $this->type = $_FILES['fileupload']['type'];
            $this->error = $_FILES['fileupload']['error'];

            // Handling errors while uploading
            if (isset($this->error) && ($this->error !== UPLOAD_ERR_OK)) {
                if (isset($lang['UPLOAD_ERRORS'][$this->error])) {
                    bb_die($lang['UPLOAD_ERROR_COMMON'] . '<br/><br/>' . $lang['UPLOAD_ERRORS'][$this->error]);
                } else {
                    bb_die($lang['UPLOAD_ERROR_COMMON']);
                }
            }

            if (isset($_FILES['fileupload']['size']) && $_FILES['fileupload']['size'] === 0) {
                bb_die('Tried to upload empty file');
            }

            $this->type = strtolower($this->type);
            $this->extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
            $this->filesize = (int)filesize($file);

            $allowed_filesize = $row['max_filesize'] ?? $attach_config['max_filesize'];
            $cat_id = isset($row['cat_id']) ? (int)$row['cat_id'] : 0;
            $auth_cache = isset($row['forum_permissions']) ? trim($row['forum_permissions']) : '';
            $row['allow_group'] = $row['allow_group'] ?? 0;

            // check Filename
            if (preg_match("#[\\/:*?\"<>|]#i", $this->filename)) {
                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $error_msg .= sprintf($lang['INVALID_FILENAME'], htmlspecialchars($this->filename));
            }

            // check php upload-size
            if (!$error && $file === 'none') {
                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $ini_val = 'ini_get';

                $max_size = @$ini_val('upload_max_filesize');

                if (empty($max_size)) {
                    $error_msg .= $lang['ATTACHMENT_PHP_SIZE_NA'];
                } else {
                    $error_msg .= sprintf($lang['ATTACHMENT_PHP_SIZE_OVERRUN'], $max_size);
                }
            }

            // Check Forum Permissions
            if (!$error && !IS_ADMIN && !is_forum_authed($auth_cache, $forum_id) && trim($auth_cache)) {
                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $error_msg .= sprintf($lang['DISALLOWED_EXTENSION_WITHIN_FORUM'], htmlspecialchars($this->extension));
            }

            //bt
            // Block uploading more than one torrent file
            global $update_attachment;
            if (!$error && $this->extension === TORRENT_EXT && in_array(TORRENT_EXT, $this->attachment_extension_list) && !$update_attachment) {
                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $error_msg .= $lang['ONLY_1_TOR_PER_TOPIC'];
            }
            // Check if user can post torrent
            global $post_data;
            if (!$error && $this->extension === TORRENT_EXT && !$post_data['first_post']) {
                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $error_msg .= $lang['ALLOWED_ONLY_1ST_POST_ATTACH'];
            }
            //bt end

            if (!$error) {
                // Prepare Values
                $this->filetime = TIMENOW;
                $this->filename = $r_file;

                // Generate hashed filename
                $this->attach_filename = TIMENOW . hash('xxh128', $this->filename);
            }

            if ($error) {
                $this->post_attach = false;
                return;
            }

            // Upload Attachment
            if (!$error) {
                if (ini_get('open_basedir')) {
                    $upload_mode = 'move';
                } else {
                    $upload_mode = 'copy';
                }

                $this->move_uploaded_attachment($upload_mode, $file);
            }

            // Now, check filesize parameters
            if (!$error) {
                if (!$this->filesize) {
                    $this->filesize = (int)@filesize($upload_dir . '/' . $this->attach_filename);
                }
            }

            // check Filesize
            if (!$error && $allowed_filesize && $this->filesize > $allowed_filesize && !(IS_ADMIN || IS_MOD || IS_GROUP_MEMBER)) {
                $allowed_filesize = humn_size($allowed_filesize);

                $error = true;
                if (!empty($error_msg)) {
                    $error_msg .= '<br />';
                }
                $error_msg .= sprintf($lang['ATTACHMENT_TOO_BIG'], $allowed_filesize);
            }

            if ($error) {
                unlink_attach($this->attach_filename);
                unlink_attach($this->attach_filename, MODE_THUMBNAIL);
                $this->post_attach = false;
            }
        }
    }

    // Copy the temporary attachment to the right location (copy, move_uploaded_file)
    public function move_uploaded_attachment($upload_mode, $file)
    {
        global $error, $error_msg, $lang, $upload_dir;

        if (!is_uploaded_file($file)) {
            bb_die('Unable to upload file. The given source has not been uploaded');
        }

        switch ($upload_mode) {
            case 'copy':

                if (!@copy($file, $upload_dir . '/' . basename($this->attach_filename))) {
                    if (!@move_uploaded_file($file, $upload_dir . '/' . basename($this->attach_filename))) {
                        $error = true;
                        if (!empty($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= sprintf($lang['GENERAL_UPLOAD_ERROR'], './' . $upload_dir . '/' . $this->attach_filename);
                        return;
                    }
                }
                @chmod($upload_dir . '/' . basename($this->attach_filename), 0644);

                break;

            case 'move':

                if (!@move_uploaded_file($file, $upload_dir . '/' . basename($this->attach_filename))) {
                    if (!@copy($file, $upload_dir . '/' . basename($this->attach_filename))) {
                        $error = true;
                        if (!empty($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= sprintf($lang['GENERAL_UPLOAD_ERROR'], './' . $upload_dir . '/' . $this->attach_filename);
                        return;
                    }
                }
                @chmod($upload_dir . '/' . $this->attach_filename, 0644);

                break;
        }
    }
}
