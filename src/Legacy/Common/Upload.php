<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

/**
 * Class Upload
 * @package TorrentPier\Legacy\Common
 */
class Upload
{
    public $cfg = [
        'max_size' => 0,
        'max_width' => 0,
        'max_height' => 0,
        'allowed_ext' => [],
        'upload_path' => '',
        'up_allowed' => false,
    ];
    public $file = [
        'name' => '',
        'type' => '',
        'size' => 0,
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
    ];
    public $orig_name = '';
    public $file_path = '';      // Stored file path
    public $file_ext = '';
    public $file_ext_id = '';
    public $file_size = '';
    public $ext_ids = []; // array_flip($bb_cfg['file_id_ext'])
    public $errors = [];

    /**
     * Image types array
     *
     * @var array|string[]
     */
    public array $img_types = [
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_BMP => 'bmp',
        IMAGETYPE_WEBP => 'webp'
    ];

    /**
     * @param array $cfg
     * @param array $post_params
     * @param bool $uploaded_only
     * @return bool
     */
    public function init(array $cfg = [], array $post_params = [], $uploaded_only = true)
    {
        global $bb_cfg, $lang;

        $this->cfg = array_merge($this->cfg, $cfg);
        $this->file = $post_params;

        // Check upload allowed
        if (!$this->cfg['up_allowed']) {
            $this->errors[] = $lang['UPLOAD_ERROR_COMMON_DISABLED'];
            return false;
        }

        // upload errors from $_FILES
        if ($this->file['error']) {
            $msg = $lang['UPLOAD_ERROR_COMMON'];
            $msg .= ($err_desc =& $lang['UPLOAD_ERRORS'][$this->file['error']]) ? " ($err_desc)" : '';
            $this->errors[] = $msg;
            return false;
        }
        // file_exists
        if (!file_exists($this->file['tmp_name'])) {
            $this->errors[] = "Uploaded file not exists: {$this->file['tmp_name']}";
            return false;
        }
        // size
        if (!$this->file_size = filesize($this->file['tmp_name'])) {
            $this->errors[] = "Uploaded file is empty: {$this->file['tmp_name']}";
            return false;
        }
        if ($this->cfg['max_size'] && $this->file_size > $this->cfg['max_size']) {
            $this->errors[] = sprintf($lang['UPLOAD_ERROR_SIZE'], humn_size($this->cfg['max_size']));
            return false;
        }
        // is_uploaded_file
        if ($uploaded_only && !is_uploaded_file($this->file['tmp_name'])) {
            $this->errors[] = "Not uploaded file: {$this->file['tmp_name']}";
            return false;
        }
        // get ext
        $this->ext_ids = array_flip($bb_cfg['file_id_ext']);
        $file_name_ary = explode('.', $this->file['name']);
        $this->file_ext = strtolower(end($file_name_ary));

        // img
        if ($this->cfg['max_width'] || $this->cfg['max_height']) {
            if ($img_info = getimagesize($this->file['tmp_name'])) {
                [$width, $height, $type, $attr] = $img_info;

                // redefine ext
                if (!$width || !$height || !$type || !isset($this->img_types[$type])) {
                    $this->errors[] = $lang['UPLOAD_ERROR_FORMAT'];
                    return false;
                }
                $this->file_ext = $this->img_types[$type];

                // width & height
                if (($this->cfg['max_width'] && $width > $this->cfg['max_width']) || ($this->cfg['max_height'] && $height > $this->cfg['max_height'])) {
                    $this->errors[] = sprintf($lang['UPLOAD_ERROR_DIMENSIONS'], $this->cfg['max_width'], $this->cfg['max_height']);
                    return false;
                }
            } else {
                $this->errors[] = $lang['UPLOAD_ERROR_NOT_IMAGE'];
                return false;
            }
        }
        // check ext
        if ($uploaded_only && (!isset($this->ext_ids[$this->file_ext]) || !\in_array($this->file_ext, $this->cfg['allowed_ext'], true))) {
            $this->errors[] = sprintf($lang['UPLOAD_ERROR_NOT_ALLOWED'], htmlCHR($this->file_ext));
            return false;
        }
        $this->file_ext_id = $this->ext_ids[$this->file_ext];

        return true;
    }

    /**
     * @param string $mode
     * @param array $params
     * @return bool
     */
    public function store($mode = '', array $params = [])
    {
        if ($mode == 'avatar') {
            delete_avatar($params['user_id'], $params['avatar_ext_id']);
            $file_path = get_avatar_path($params['user_id'], $this->file_ext_id);
            return $this->_move($file_path);
        }

        if ($mode == 'attach') {
            $file_path = get_attach_path($params['topic_id']);
            return $this->_move($file_path);
        }

        trigger_error("Invalid upload mode: $mode", E_USER_ERROR);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public function _move($file_path)
    {
        $dir = \dirname($file_path);
        if (!file_exists($dir)) {
            if (!bb_mkdir($dir)) {
                $this->errors[] = "Cannot create dir: $dir";
                return false;
            }
        }
        if (!@rename($this->file['tmp_name'], $file_path)) {
            if (!@copy($this->file['tmp_name'], $file_path)) {
                $this->errors[] = 'Cannot copy tmp file';
                return false;
            }
            @unlink($this->file['tmp_name']);
        }
        @chmod($file_path, 0664);

        return file_exists($file_path);
    }
}
