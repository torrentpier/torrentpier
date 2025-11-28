<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

use Exception;
use TorrentPier\Attachment;
use TorrentPier\Image\ImageService;

/**
 * Class Upload
 * @package TorrentPier\Legacy\Common
 */
class Upload
{
    /**
     * Default config pattern
     *
     * @var array
     */
    public array $cfg = [
        'max_size' => 0,
        'max_width' => 0,
        'max_height' => 0,
        'allowed_ext' => [],
        'upload_path' => '',
        'up_allowed' => false,
    ];

    /**
     * File params pattern
     *
     * @var array
     */
    public array $file = [
        'name' => '',
        'full_path' => '',
        'type' => '',
        'size' => 0,
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
    ];

    public $file_ext = '';
    public $file_ext_id = '';

    /**
     * File size
     *
     * @var int
     */
    public int $file_size = 0;

    /**
     * All allowed extensions to upload
     *
     * @var array
     */
    public array $ext_ids = [];

    /**
     * Store caught errors while uploading
     *
     * @var array
     */
    public array $errors = [];

    /**
     * Image types array
     *
     * @see https://www.php.net/manual/en/image.constants.php
     * @var array|string[]
     */
    public array $img_types = [
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_BMP => 'bmp',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_AVIF => 'avif'
    ];

    /**
     * Initialize uploader
     *
     * @param array $cfg
     * @param array $post_params
     * @param bool $uploaded_only
     * @return bool
     */
    public function init(array $cfg = [], array $post_params = [], bool $uploaded_only = true): bool
    {
        global $lang;

        $this->cfg = array_merge($this->cfg, $cfg);
        $this->file = $post_params;

        // Check upload allowed
        if (!$this->cfg['up_allowed']) {
            $this->errors[] = $lang['UPLOAD_ERROR_COMMON_DISABLED'];
            return false;
        }

        // Handling errors while uploading
        if (isset($this->file['error']) && ($this->file['error'] !== UPLOAD_ERR_OK)) {
            if (isset($lang['UPLOAD_ERRORS'][$this->file['error']])) {
                $this->errors[] = $lang['UPLOAD_ERROR_COMMON'] . '<br/><br/>' . $lang['UPLOAD_ERRORS'][$this->file['error']];
            } else {
                $this->errors[] = $lang['UPLOAD_ERROR_COMMON'];
            }
            return false;
        }

        // Check file exists
        if (!file_exists($this->file['tmp_name'])) {
            $this->errors[] = "Uploaded file not exists: {$this->file['tmp_name']}";
            return false;
        }

        // Check file is not empty
        if (!$this->file_size = filesize($this->file['tmp_name'])) {
            $this->errors[] = "Uploaded file is empty: {$this->file['tmp_name']}";
            return false;
        }

        // is_uploaded_file
        if ($uploaded_only && !is_uploaded_file($this->file['tmp_name'])) {
            $this->errors[] = "Not uploaded file: {$this->file['tmp_name']}";
            return false;
        }

        // Got file extension
        $file_name_ary = explode('.', $this->file['name']);
        $this->file_ext = strtolower(end($file_name_ary));

        $this->ext_ids = array_flip(config()->get('file_id_ext'));

        // Actions for images [E.g. Change avatar]
        if ($this->cfg['max_width'] || $this->cfg['max_height']) {
            if ($img_info = getimagesize($this->file['tmp_name'])) {
                [$width, $height, $type] = $img_info;

                // redefine ext
                if (!$width || !$height || !$type || !isset($this->img_types[$type])) {
                    $this->errors[] = $lang['UPLOAD_ERROR_FORMAT'];
                    return false;
                }
                $this->file_ext = $this->img_types[$type];

                // Resize image to fit max dimensions (always resize for avatars to optimize file size)
                try {
                    $maxWidth = $this->cfg['max_width'] ?: null;
                    $maxHeight = $this->cfg['max_height'] ?: null;

                    ImageService::read($this->file['tmp_name'])
                        ->scaleDown($maxWidth, $maxHeight)
                        ->save($this->file['tmp_name']);

                    // Update file size after resize
                    $this->file_size = filesize($this->file['tmp_name']);

                    // If still too large, try to compress with lower quality
                    if ($this->cfg['max_size'] && $this->file_size > $this->cfg['max_size']) {
                        $this->compressToMaxSize($this->file['tmp_name'], $this->cfg['max_size']);
                        $this->file_size = filesize($this->file['tmp_name']);
                    }
                } catch (Exception $e) {
                    $this->errors[] = sprintf($lang['UPLOAD_ERROR_DIMENSIONS'], $this->cfg['max_width'], $this->cfg['max_height']);
                    return false;
                }
            } else {
                $this->errors[] = $lang['UPLOAD_ERROR_NOT_IMAGE'];
                return false;
            }
        }

        // Check file size (after image processing or for non-images)
        if ($this->cfg['max_size'] && $this->file_size > $this->cfg['max_size']) {
            $this->errors[] = sprintf($lang['UPLOAD_ERROR_SIZE'], humn_size($this->cfg['max_size']));
            return false;
        }

        // Check extension
        if ($uploaded_only && (!isset($this->ext_ids[$this->file_ext]) || !\in_array($this->file_ext, $this->cfg['allowed_ext'], true))) {
            $this->errors[] = sprintf($lang['UPLOAD_ERROR_NOT_ALLOWED'], htmlCHR($this->file_ext));
            return false;
        }
        $this->file_ext_id = $this->ext_ids[$this->file_ext];

        return true;
    }

    /**
     * Store uploaded file
     *
     * @param string $mode
     * @param array $params
     * @return bool
     */
    public function store(string $mode, array $params = []): bool
    {
        switch ($mode) {
            case 'avatar':
                delete_avatar($params['user_id'], $params['avatar_ext_id']);
                $file_path = get_avatar_path($params['user_id'], $this->file_ext_id);
                break;
            case 'attach':
                $file_path = Attachment::getPath($params['topic_id']);
                break;
            default:
                throw new \RuntimeException("Invalid upload mode: $mode");
        }

        return $this->_move($file_path);
    }

    /**
     * Move file to target path
     *
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

    /**
     * Compress image to fit max file size by reducing quality
     *
     * @param string $path
     * @param int $maxSize
     * @return void
     */
    private function compressToMaxSize(string $path, int $maxSize): void
    {
        $quality = 85;
        $minQuality = 30;

        while (filesize($path) > $maxSize && $quality > $minQuality) {
            $quality = max($quality - 10, $minQuality);
            ImageService::read($path)->save($path, quality: $quality);
            clearstatcache(true, $path);
        }
    }
}
