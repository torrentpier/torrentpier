<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'og_image');
define('NO_GZIP', true);

require __DIR__ . '/common.php';

use TorrentPier\OpenGraph\OgImageCache;
use TorrentPier\OpenGraph\OgImageGenerator;

// Check if OG image generation is enabled
if (!config()->get('og_image.enabled', true)) {
    http_response_code(404);
    exit;
}

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate input
$validTypes = ['topic', 'forum', 'user'];
if (!in_array($type, $validTypes, true) || $id < 1) {
    http_response_code(404);
    exit;
}

$cache = new OgImageCache();
$generator = new OgImageGenerator();

// Get last update timestamp for smart cache invalidation
$updatedAt = match ($type) {
    'topic' => getTopicUpdatedAt($id),
    'forum' => getForumUpdatedAt($id),
    'user' => getUserUpdatedAt($id),
    default => 0,
};

// Check cache first
if ($cachedFile = $cache->get($type, $id, $updatedAt)) {
    sendImage($cachedFile, true);
    exit;
}

// Generate new image
$imageData = match ($type) {
    'topic' => $generator->generateForTopic($id),
    'forum' => $generator->generateForForum($id),
    'user' => $generator->generateForUser($id),
    default => null,
};

// Fallback to default if generation failed
if ($imageData === null) {
    $imageData = $generator->generateDefault();
}

// Save to cache
$cachedFile = $cache->set($type, $id, $imageData);

// Send response
sendImage($cachedFile, false);

/**
 * Send image with appropriate headers
 */
function sendImage(string $filePath, bool $cacheHit): void
{
    $cacheMaxAge = 86400; // 24 hours

    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=' . $cacheMaxAge);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheMaxAge) . ' GMT');
    header('X-OG-Cache: ' . ($cacheHit ? 'HIT' : 'MISS'));

    readfile($filePath);
}

/**
 * Get topic's last update timestamp (last post time)
 */
function getTopicUpdatedAt(int $id): int
{
    return (int)(DB()->table(BB_TOPICS)->get($id)?->topic_last_post_time ?? 0);
}

/**
 * Get forum's last update timestamp
 */
function getForumUpdatedAt(int $id): int
{
    // Use forum_last_post_id as a proxy for the last update
    return (int)(DB()->table(BB_FORUMS)->get($id)?->forum_last_post_id ?? 0);
}

/**
 * Get user's last update timestamp
 */
function getUserUpdatedAt(int $id): int
{
    // Use last visit time as an update indicator
    return (int)(DB()->table(BB_USERS)->get($id)?->user_lastvisit ?? 0);
}
