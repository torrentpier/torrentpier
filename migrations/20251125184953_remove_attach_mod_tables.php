<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove legacy attach_mod tables after migration to topic-based storage
 *
 * IMPORTANT: Only run this migration after:
 * 1. Running scripts/migrate_attachments.php to move existing files
 * 2. Verifying all torrents work correctly with the new system
 * 3. Having a database backup
 */
final class RemoveAttachModTables extends AbstractMigration
{
    /**
     * Tables to be removed
     */
    private const LEGACY_TABLES = [
        'bb_attachments',
        'bb_attachments_desc',
        'bb_extensions',
        'bb_extension_groups',
        'bb_attachments_config',
        'bb_attach_quota',
        'bb_quota_limits',
    ];

    public function up(): void
    {
        // Safety check: Verify data migration has been completed
        // Check if there are any topics with attach_ext_id = 0 that have torrents in old system
        $result = $this->fetchRow("
            SELECT COUNT(*) as cnt
            FROM bb_attachments_desc d
            JOIN bb_attachments a ON d.attach_id = a.attach_id
            JOIN bb_posts p ON a.post_id = p.post_id
            JOIN bb_topics t ON p.topic_id = t.topic_id
            WHERE d.extension = 'torrent'
              AND p.post_id = t.topic_first_post_id
              AND t.attach_ext_id = 0
        ");

        if ($result && $result['cnt'] > 0) {
            throw new \RuntimeException(
                "Cannot drop tables: {$result['cnt']} torrent(s) have not been migrated yet. " .
                "Please run scripts/migrate_attachments.php first."
            );
        }

        // Drop legacy tables
        foreach (self::LEGACY_TABLES as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
                echo "Dropped table: $table\n";
            }
        }

        // Remove attach_id column from bb_bt_torrents if it exists
        $bt_torrents = $this->table('bb_bt_torrents');
        if ($bt_torrents->hasColumn('attach_id')) {
            // First drop the index if it exists
            if ($bt_torrents->hasIndex('attach_id')) {
                $bt_torrents->removeIndex(['attach_id'])->save();
            }
            $bt_torrents->removeColumn('attach_id')->save();
            echo "Removed attach_id column from bb_bt_torrents\n";
        }
    }

    public function down(): void
    {
        // Recreate attach_id column in bb_bt_torrents
        $bt_torrents = $this->table('bb_bt_torrents');
        if (!$bt_torrents->hasColumn('attach_id')) {
            $bt_torrents
                ->addColumn('attach_id', 'integer', [
                    'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_MEDIUM,
                    'signed' => false,
                    'default' => 0,
                    'null' => false,
                    'after' => 'forum_id'
                ])
                ->addIndex('attach_id', ['unique' => true])
                ->save();
        }

        // Note: We cannot restore the dropped tables and their data
        // A full database restore from backup would be required
        echo "WARNING: Legacy attach_mod tables cannot be automatically restored.\n";
        echo "Please restore from a database backup if needed.\n";
    }
}
