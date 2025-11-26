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
 * Simple torrent upload handler using topic-based storage
 */
class TorrentUpload
{
    private array $errors = [];
    private int $fileSize = 0;
    private int $extId = 8; // torrent extension ID

    /**
     * Store a torrent file for a topic
     *
     * @param int $topic_id Topic ID
     * @param array $uploaded_file $_FILES array element
     * @return int|false File size on success, false on failure
     */
    public function store(int $topic_id, array $uploaded_file): int|false
    {
        // Check for upload errors
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($uploaded_file['error']);
            return false;
        }

        // Check if a file exists
        if (!file_exists($uploaded_file['tmp_name'])) {
            $this->errors[] = 'Uploaded file not found';
            return false;
        }

        // Validate file size
        $this->fileSize = filesize($uploaded_file['tmp_name']);
        if (!$this->fileSize) {
            $this->errors[] = 'Uploaded file is empty';
            return false;
        }

        $maxSize = config()->get('attach.max_size');
        if ($maxSize && $this->fileSize > $maxSize) {
            $this->errors[] = 'File exceeds maximum allowed size';
            return false;
        }

        // Validate extension
        $ext = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'torrent') {
            $this->errors[] = 'Only .torrent files are allowed';
            return false;
        }

        // Validate it's actually an uploaded file
        if (!is_uploaded_file($uploaded_file['tmp_name'])) {
            $this->errors[] = 'Invalid upload';
            return false;
        }

        // Get a destination path
        $file_path = get_attach_path($topic_id);
        $dir = dirname($file_path);

        // Create a directory if needed
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $this->errors[] = 'Cannot create storage directory';
                return false;
            }
        }

        // Move the file
        if (!rename($uploaded_file['tmp_name'], $file_path)) {
            if (!copy($uploaded_file['tmp_name'], $file_path)) {
                $this->errors[] = 'Cannot move uploaded file';
                return false;
            }
            @unlink($uploaded_file['tmp_name']);
        }

        @chmod($file_path, 0644);

        if (!is_file($file_path)) {
            $this->errors[] = 'File storage failed';
            return false;
        }

        return $this->fileSize;
    }

    /**
     * Delete a torrent file for a topic
     *
     * @param int $topic_id Topic ID
     * @return bool
     */
    public static function delete(int $topic_id): bool
    {
        $file_path = get_attach_path($topic_id);
        return is_file($file_path) && unlink($file_path);
    }

    /**
     * Check if a torrent file exists for a topic
     *
     * @param int $topic_id Topic ID
     * @return bool
     */
    public static function exists(int $topic_id): bool
    {
        return is_file(get_attach_path($topic_id));
    }

    /**
     * Get the file size of a stored torrent
     *
     * @param int $topic_id Topic ID
     * @return int|false
     */
    public static function getSize(int $topic_id): int|false
    {
        $file_path = get_attach_path($topic_id);
        return is_file($file_path) ? filesize($file_path) : false;
    }

    /**
     * Get the extension ID for torrent files
     *
     * @return int
     */
    public function getExtId(): int
    {
        return $this->extId;
    }

    /**
     * Get file size of the last uploaded file
     *
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * Get any errors that occurred
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get a human-readable upload error message
     *
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            default => 'Unknown upload error',
        };
    }

    /**
     * Update the topic table with attachment info
     *
     * @param int $topic_id Topic ID
     * @param int $ext_id Extension ID (8 for torrent)
     * @param int $filesize File size in bytes
     * @return bool
     */
    public static function updateTopicAttachment(int $topic_id, int $ext_id, int $filesize): bool
    {
        return (bool) DB()->query("
            UPDATE " . BB_TOPICS . " SET
                topic_attachment = 1,
                attach_ext_id = $ext_id,
                attach_filesize = $filesize
            WHERE topic_id = $topic_id
        ");
    }

    /**
     * Clear topic attachment info
     *
     * @param int $topic_id Topic ID
     * @return bool
     */
    public static function clearTopicAttachment(int $topic_id): bool
    {
        return (bool) DB()->query("
            UPDATE " . BB_TOPICS . " SET
                topic_attachment = 0,
                attach_ext_id = 0,
                attach_filesize = 0
            WHERE topic_id = $topic_id
        ");
    }
}
