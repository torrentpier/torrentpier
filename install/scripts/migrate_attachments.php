<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Standalone migration script to move torrent files from old attach_mod system to topic-based storage.
 *
 * This script migrates torrent files from the old attach_mod system (bb_attachments_desc + bb_attachments)
 * to the new topic-based storage where files are stored as {floor(id/10000)}/{id%100}/{topic_id}.torrent
 * and metadata is kept in bb_topics (attach_ext_id, attach_filesize, download_count, tracker_status).
 *
 * BEFORE RUNNING:
 *   1. Make a full database backup
 *   2. Make a backup of the uploads directory
 *   3. Edit the CONFIGURATION section below:
 *      - DB credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
 *      - Paths (OLD_UPLOAD_DIR, NEW_UPLOAD_DIR)
 *      - SET_DL_TYPE: set true if bt_set_dltype_on_tor_reg was enabled
 *      - CLEANUP_OLD_TABLES: set true to delete old bb_attachments* records
 *
 * USAGE:
 *   # Dry run - see what would be migrated without making changes
 *   php install/scripts/migrate_attachments.php --dry-run
 *
 *   # Dry run with verbose output
 *   php install/scripts/migrate_attachments.php --dry-run -v
 *
 *   # Perform actual migration
 *   php install/scripts/migrate_attachments.php
 *
 *   # Perform migration with verbose output
 *   php install/scripts/migrate_attachments.php -v
 *   php install/scripts/migrate_attachments.php --verbose
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Only run from CLI
if (PHP_SAPI !== 'cli') {
    die("This script must be run from the command line.\n");
}

// =====================================================
// CONFIGURATION - Edit these values before running
// =====================================================

// Database connection
const DB_HOST = '127.0.0.1';
const DB_NAME = 'tp';
const DB_USER = 'root';
const DB_PASS = '';

// Table names
const BB_ATTACHMENTS_DESC = 'bb_attachments_desc';
const BB_ATTACHMENTS = 'bb_attachments';
const BB_POSTS = 'bb_posts';
const BB_TOPICS = 'bb_topics';
const BB_BT_TORRENTS = 'bb_bt_torrents';

// Paths (relative to project root)
const OLD_UPLOAD_DIR = 'data/uploads'; // Old attach_mod directory
const NEW_UPLOAD_DIR = 'data/uploads'; // New topic-based directory

// Torrent extension ID in bb_topics.attach_ext_id
const TORRENT_EXT_ID = 8;

// Topic DL type for registered torrents (matches TOPIC_DL_TYPE_DL constant)
const TOPIC_DL_TYPE_DL = 1;

// Set to true if bt_set_dltype_on_tor_reg was enabled on your tracker
const SET_DL_TYPE = true;

// Set to true to delete old attachment records after successful migration
const CLEANUP_OLD_TABLES = true;

// =====================================================
// END CONFIGURATION
// =====================================================

$root_dir = dirname(__DIR__, 2);
$old_upload_path = $root_dir . '/' . OLD_UPLOAD_DIR;
$new_upload_path = $root_dir . '/' . NEW_UPLOAD_DIR;

// Parse CLI arguments
$batch_size = 100;
$dry_run = in_array('--dry-run', $argv);
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);

echo "======================================\n";
echo "TorrentPier Attachment Migration\n";
echo "======================================\n\n";

if ($dry_run) {
    echo "DRY RUN MODE - No changes will be made\n\n";
}

// Validate paths
if (!is_dir($old_upload_path)) {
    die("ERROR: Old upload directory not found: $old_upload_path\n");
}

if (!is_dir($new_upload_path)) {
    if ($dry_run) {
        echo "Would create new upload directory: $new_upload_path\n";
    } else {
        if (!mkdir($new_upload_path, 0755, true)) {
            die("ERROR: Cannot create new upload directory: $new_upload_path\n");
        }
    }
}

echo "Old upload directory: $old_upload_path\n";
echo "New upload directory: $new_upload_path\n\n";

// Connect to database
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("ERROR: Database connection failed: " . $e->getMessage() . "\n");
}

echo "Connected to database: " . DB_NAME . "\n\n";

/**
 * Get new file path for topic (matches get_attach_path logic)
 */
function getNewFilePath(int $topicId, string $basePath): string
{
    $firstDiv = 10000;
    $secDiv = 100;
    return sprintf(
        '%s/%d/%d/%d.torrent',
        $basePath,
        floor($topicId / $firstDiv),
        $topicId % $secDiv,
        $topicId
    );
}

/**
 * Check if torrent is registered in tracker
 */
function isRegisteredOnTracker(PDO $pdo, int $topicId): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM " . BB_BT_TORRENTS . " WHERE topic_id = ? LIMIT 1");
    $stmt->execute([$topicId]);
    return (bool)$stmt->fetchColumn();
}

$total_processed = 0;
$total_migrated = 0;
$total_skipped = 0;
$total_failed = 0;
$last_attach_id = 0;

// Process in batches
while (true) {
    // Get batch of torrent attachments that haven't been migrated yet
    $sql = "
        SELECT
            d.attach_id, d.physical_filename, d.extension, d.filesize, d.real_filename, d.download_count,
            a.post_id,
            p.topic_id,
            t.topic_first_post_id, t.attach_ext_id
        FROM " . BB_ATTACHMENTS_DESC . " d
        JOIN " . BB_ATTACHMENTS . " a ON d.attach_id = a.attach_id
        JOIN " . BB_POSTS . " p ON a.post_id = p.post_id
        JOIN " . BB_TOPICS . " t ON p.topic_id = t.topic_id
        WHERE d.extension = 'torrent'
          AND p.post_id = t.topic_first_post_id
          AND d.attach_id > $last_attach_id
        ORDER BY d.attach_id
        LIMIT $batch_size
    ";

    $stmt = $pdo->query($sql);
    $rowset = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rowset) {
        break;
    }

    foreach ($rowset as $row) {
        $total_processed++;
        $attach_id = $row['attach_id'];
        $topic_id = (int)$row['topic_id'];
        $filesize = (int)$row['filesize'];
        $download_count = (int)$row['download_count'];

        // Build paths
        $old_path = $old_upload_path . '/' . basename($row['physical_filename']);
        $new_path = getNewFilePath($topic_id, $new_upload_path);
        $new_dir = dirname($new_path);

        if ($verbose) {
            echo "Processing attach_id=$attach_id, topic_id=$topic_id\n";
            echo "  Old: $old_path\n";
            echo "  New: $new_path\n";
        }

        // Check if source file exists
        if (!is_file($old_path)) {
            echo "WARNING: Source file not found for topic $topic_id: $old_path\n";
            $total_failed++;
            continue;
        }

        // Check if already migrated
        if (is_file($new_path)) {
            if ($verbose) {
                echo "  SKIP: File already exists at destination\n";
            }
            // Still update the topic table
            if (!$dry_run) {
                $tracker_status = isRegisteredOnTracker($pdo, $topic_id) ? 1 : 0;
                $dl_type_sql = (SET_DL_TYPE && $tracker_status) ? ", topic_dl_type = " . TOPIC_DL_TYPE_DL : "";
                $pdo->exec("
                    UPDATE " . BB_TOPICS . " SET
                        attach_ext_id = " . TORRENT_EXT_ID . ",
                        attach_filesize = $filesize,
                        download_count = $download_count,
                        tracker_status = $tracker_status
                        $dl_type_sql
                    WHERE topic_id = $topic_id
                ");

                // Cleanup old attachment records
                if (CLEANUP_OLD_TABLES) {
                    $pdo->exec("DELETE FROM " . BB_ATTACHMENTS . " WHERE attach_id = $attach_id");
                    $pdo->exec("DELETE FROM " . BB_ATTACHMENTS_DESC . " WHERE attach_id = $attach_id");
                }
            }
            $total_skipped++;
            continue;
        }

        // Create directory structure if needed
        if (!is_dir($new_dir)) {
            if ($dry_run) {
                if ($verbose) {
                    echo "  Would create directory: $new_dir\n";
                }
            } else {
                if (!mkdir($new_dir, 0755, true)) {
                    echo "ERROR: Cannot create directory $new_dir for topic $topic_id\n";
                    $total_failed++;
                    continue;
                }
            }
        }

        // Move the file
        if ($dry_run) {
            if ($verbose) {
                echo "  Would move file to: $new_path\n";
            }
        } else {
            if (!rename($old_path, $new_path)) {
                // Try copy + delete as fallback
                if (!copy($old_path, $new_path)) {
                    echo "ERROR: Cannot move file for topic $topic_id\n";
                    $total_failed++;
                    continue;
                }
                unlink($old_path);
            }
            chmod($new_path, 0644);
        }

        // Update BB_TOPICS
        if ($dry_run) {
            if ($verbose) {
                $registered = isRegisteredOnTracker($pdo, $topic_id);
                echo "  Would update topic: attach_ext_id=" . TORRENT_EXT_ID . ", attach_filesize=$filesize, download_count=$download_count, tracker_status=" . ($registered ? 1 : 0);
                if (SET_DL_TYPE && $registered) {
                    echo ", topic_dl_type=" . TOPIC_DL_TYPE_DL;
                }
                echo "\n";
                if (CLEANUP_OLD_TABLES) {
                    echo "  Would delete from bb_attachments and bb_attachments_desc (attach_id=$attach_id)\n";
                }
            }
        } else {
            $tracker_status = isRegisteredOnTracker($pdo, $topic_id) ? 1 : 0;
            $dl_type_sql = (SET_DL_TYPE && $tracker_status) ? ", topic_dl_type = " . TOPIC_DL_TYPE_DL : "";
            $pdo->exec("
                UPDATE " . BB_TOPICS . " SET
                    attach_ext_id = " . TORRENT_EXT_ID . ",
                    attach_filesize = $filesize,
                    download_count = $download_count,
                    tracker_status = $tracker_status
                    $dl_type_sql
                WHERE topic_id = $topic_id
            ");

            // Cleanup old attachment records
            if (CLEANUP_OLD_TABLES) {
                $pdo->exec("DELETE FROM " . BB_ATTACHMENTS . " WHERE attach_id = $attach_id");
                $pdo->exec("DELETE FROM " . BB_ATTACHMENTS_DESC . " WHERE attach_id = $attach_id");
            }
        }

        $total_migrated++;

        if ($verbose) {
            echo "  OK: Migrated successfully\n";
        }
    }

    // Update last processed attach_id for next batch
    $last_attach_id = (int)$row['attach_id'];

    echo "Progress: $total_processed processed, $total_migrated migrated, $total_skipped skipped, $total_failed failed\n";

    // Small pause to prevent overloading
    usleep(100000); // 100ms
}

echo "\n======================================\n";
echo "Migration Complete\n";
echo "======================================\n";
echo "Total processed: $total_processed\n";
echo "Total migrated:  $total_migrated\n";
echo "Total skipped:   $total_skipped\n";
echo "Total failed:    $total_failed\n";

if ($dry_run) {
    echo "\nThis was a dry run. No changes were made.\n";
    echo "Run without --dry-run to perform the actual migration.\n";
} else {
    if (CLEANUP_OLD_TABLES) {
        echo "\nOld attachment records were deleted from bb_attachments and bb_attachments_desc.\n";
    }
}

if ($total_failed > 0) {
    echo "\nWARNING: Some files failed to migrate. Check the output above for details.\n";
}

echo "\nDone.\n";
