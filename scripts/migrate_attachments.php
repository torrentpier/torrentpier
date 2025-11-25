<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Migration script to move torrent files from old attach_mod system to topic-based storage
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// SAFETY: Remove this line after reviewing the script
die("Remove this die() line after reviewing the script and confirming you have a backup!\n");

// Only run from CLI
if (PHP_SAPI !== 'cli') {
    die("This script must be run from the command line.\n");
}

define('BB_SCRIPT', 'migrate_attachments');
define('IN_SERVICE', true);

require __DIR__ . '/../common.php';

// Configuration
$batch_size = 100;
$dry_run = in_array('--dry-run', $argv);
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);

echo "======================================\n";
echo "TorrentPier Attachment Migration\n";
echo "======================================\n\n";

if ($dry_run) {
    echo "DRY RUN MODE - No changes will be made\n\n";
}

// Get old upload directory from attach config
$old_upload_dir = get_attachments_dir();

if (!is_dir($old_upload_dir)) {
    die("ERROR: Old upload directory not found: $old_upload_dir\n");
}

echo "Old upload directory: $old_upload_dir\n";
echo "New storage path: " . config()->get('attach.upload_path') . "\n\n";

$total_processed = 0;
$total_migrated = 0;
$total_skipped = 0;
$total_failed = 0;

// Process in batches
while (true) {
    // Get batch of torrent attachments that haven't been migrated yet
    // Only process torrents attached to the first post of a topic
    $sql = "
        SELECT
            d.attach_id, d.physical_filename, d.extension, d.filesize, d.real_filename,
            a.post_id,
            p.topic_id,
            t.topic_first_post_id, t.attach_ext_id
        FROM " . BB_ATTACHMENTS_DESC . " d
        JOIN " . BB_ATTACHMENTS . " a ON d.attach_id = a.attach_id
        JOIN " . BB_POSTS . " p ON a.post_id = p.post_id
        JOIN " . BB_TOPICS . " t ON p.topic_id = t.topic_id
        WHERE d.extension = 'torrent'
          AND p.post_id = t.topic_first_post_id
          AND t.attach_ext_id = 0
        ORDER BY d.attach_id
        LIMIT $batch_size
    ";

    $rowset = DB()->fetch_rowset($sql);

    if (!$rowset) {
        break;
    }

    foreach ($rowset as $row) {
        $total_processed++;
        $attach_id = $row['attach_id'];
        $topic_id = $row['topic_id'];
        $filesize = $row['filesize'];

        // Build paths
        $old_path = $old_upload_dir . '/' . basename($row['physical_filename']);
        $new_path = get_attach_path($topic_id);
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
                DB()->query("
                    UPDATE " . BB_TOPICS . " SET
                        topic_attachment = 1,
                        attach_ext_id = 8,
                        attach_filesize = $filesize
                    WHERE topic_id = $topic_id
                ");
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
                echo "  Would update topic: attach_ext_id=8, attach_filesize=$filesize\n";
            }
        } else {
            DB()->query("
                UPDATE " . BB_TOPICS . " SET
                    topic_attachment = 1,
                    attach_ext_id = 8,
                    attach_filesize = $filesize
                WHERE topic_id = $topic_id
            ");
        }

        $total_migrated++;

        if ($verbose) {
            echo "  OK: Migrated successfully\n";
        }
    }

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
}

if ($total_failed > 0) {
    echo "\nWARNING: Some files failed to migrate. Check the output above for details.\n";
}

echo "\nDone.\n";
