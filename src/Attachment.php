<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use TorrentPier\Legacy\Common\Upload;
use TorrentPier\Torrent\Registry;

/**
 * Attachment file management.
 */
class Attachment
{
    /**
     * Store an attachment file for a topic.
     * Handles unregistering old torrent if replacing.
     *
     * @param int $topicId Topic ID
     * @param array $fileData $_FILES['fileupload'] array
     * @param bool $torrentRegistered Whether torrent is currently registered on tracker
     * @return array{success: bool, error: string|null, ext_id: int|null}
     */
    public static function store(int $topicId, array $fileData, bool $torrentRegistered = false): array
    {
        if ($torrentRegistered) {
            Registry::unregister($topicId);
        }

        $upload = new Upload();

        if (!$upload->init(config()->getSection('attach'), $fileData)) {
            return ['success' => false, 'error' => implode('<br />', $upload->errors), 'ext_id' => null];
        }

        if (!$upload->store('attach', ['topic_id' => $topicId])) {
            return ['success' => false, 'error' => implode('<br />', $upload->errors), 'ext_id' => null];
        }

        // Update the topic table with new information
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topicId)
            ->update([
                'attach_ext_id' => $upload->file_ext_id,
                'attach_filesize' => $upload->file_size,
            ]);

        return ['success' => true, 'error' => null, 'ext_id' => $upload->file_ext_id];
    }

    /**
     * Delete attachment completely (unregister from the tracker, delete a file and clear the topic).
     * Also handles TorrServer cleanup.
     *
     * @param int $topicId Topic ID
     * @return bool True on success
     */
    public static function delete(int $topicId): bool
    {
        $row = DB()->table(BB_TOPICS)
            ->where('topic_id', $topicId)
            ->fetch();

        if ($row?->tracker_status) {
            Registry::unregister($topicId);
        }

        // Delete a physical file
        $attachPath = self::getPath($topicId);
        if (is_file($attachPath)) {
            unlink($attachPath);
        }

        // Clear attachment info in a topic
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topicId)
            ->update([
                'attach_ext_id' => 0,
                'attach_filesize' => 0,
            ]);

        return true;
    }

    /**
     * Get an attachment file path.
     *
     * @param int $topicId Topic ID
     * @param int|null $extId Extension ID (default: TORRENT_EXT_ID)
     * @return string File path
     */
    public static function getPath(int $topicId, ?int $extId = null): string
    {
        return get_attach_path($topicId, $extId);
    }

    /**
     * Get attachment file size in bytes.
     *
     * @param int $topicId Topic ID
     * @return int File size in bytes, 0 if a file doesn't exist
     */
    public static function getSize(int $topicId): int
    {
        $path = self::getPath($topicId);

        return is_file($path) ? (filesize($path) ?: 0) : 0;
    }

    /**
     * Check if an attachment file exists.
     *
     * @param int $topicId Topic ID
     * @return bool True if a file exists
     */
    public static function exists(int $topicId): bool
    {
        return is_file(self::getPath($topicId));
    }

    /**
     * Check if the M3U file exists for TorrServer integration.
     *
     * @param int $topicId Topic ID
     * @return bool True if M3U file exists
     */
    public static function m3uExists(int $topicId): bool
    {
        return is_file(self::getPath($topicId, M3U_EXT_ID));
    }

    /**
     * Get download filename for attachment.
     *
     * Format depends on tracker.torrent_filename_with_title config:
     * - true: "Topic Title [server.org].t123.torrent"
     * - false: "[server.org].t123.torrent"
     *
     * @param int $topicId Topic ID
     * @param string $topicTitle Topic title
     * @param string $extension File extension (default: 'torrent')
     * @return string Formatted filename for download
     */
    public static function getDownloadFilename(int $topicId, string $topicTitle, string $extension = TORRENT_EXT): string
    {
        $serverName = config()->get('server_name');
        $title = html_ent_decode($topicTitle);

        if (config()->get('tracker.torrent_filename_with_title')) {
            return $title . ' [' . $serverName . '].t' . $topicId . '.' . $extension;
        }

        return '[' . $serverName . '].t' . $topicId . '.' . $extension;
    }
}
