<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateModsTables extends AbstractMigration
{
    /**
     * Create mod system tables
     *
     * Creates two tables:
     * - bb_mods: Stores installed mod metadata and status
     * - bb_mod_logs: Stores mod operation logs (activate, deactivate, errors)
     */
    public function change(): void
    {
        // Create bb_mods table
        $mods = $this->table('bb_mods', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'Installed mods registry'
        ]);

        $mods->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'comment' => 'Auto-increment ID'
        ])
            ->addColumn('mod_id', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Unique mod identifier (e.g., karma, automod)'
            ])
            ->addColumn('name', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Mod display name'
            ])
            ->addColumn('version', 'string', [
                'limit' => 32,
                'null' => false,
                'comment' => 'Mod version (semantic versioning)'
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'comment' => 'Mod description'
            ])
            ->addColumn('author', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Mod author name'
            ])
            ->addColumn('manifest_path', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Full path to manifest.json'
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => 0,
                'null' => false,
                'comment' => '1 if mod is active, 0 if disabled'
            ])
            ->addColumn('installed_at', 'datetime', [
                'null' => false,
                'comment' => 'Installation timestamp'
            ])
            ->addColumn('activated_at', 'datetime', [
                'null' => true,
                'comment' => 'Last activation timestamp'
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'comment' => 'Last update timestamp'
            ])
            ->addIndex(['mod_id'], [
                'unique' => true,
                'name' => 'idx_mod_id'
            ])
            ->addIndex(['is_active'], [
                'name' => 'idx_is_active'
            ])
            ->create();

        // Create bb_mod_logs table
        $logs = $this->table('bb_mod_logs', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'Mod operation logs'
        ]);

        $logs->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'comment' => 'Auto-increment ID'
        ])
            ->addColumn('mod_id', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Mod identifier'
            ])
            ->addColumn('action', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Action performed (activate, deactivate, install, uninstall, error)'
            ])
            ->addColumn('message', 'text', [
                'null' => false,
                'comment' => 'Log message'
            ])
            ->addColumn('details', 'text', [
                'null' => true,
                'comment' => 'Additional details (JSON format for error stack traces, metadata)'
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'comment' => 'Log entry timestamp'
            ])
            ->addIndex(['mod_id'], [
                'name' => 'idx_mod_id'
            ])
            ->addIndex(['action'], [
                'name' => 'idx_action'
            ])
            ->addIndex(['created_at'], [
                'name' => 'idx_created_at'
            ])
            ->create();
    }
}
