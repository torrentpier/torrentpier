<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!empty($setmodules)) {
    if (IS_SUPER_ADMIN) {
        $module['GENERAL']['MIGRATIONS_STATUS'] = basename(__FILE__);
    }

    return;
}

require __DIR__ . '/pagestart.php';

if (!IS_SUPER_ADMIN) {
    bb_die(__('ONLY_FOR_SUPER_ADMIN'));
}

use TorrentPier\Database\MigrationStatus;

// Initialize migration status
$migrationStatus = new MigrationStatus();
$status = $migrationStatus->getMigrationStatus();
$schemaInfo = $migrationStatus->getSchemaInfo();

// Template variables
template()->assign_vars([
    'PAGE_TITLE' => __('MIGRATIONS_STATUS'),
    'CURRENT_TIME' => date('Y-m-d H:i:s'),

    // Migration status individual fields
    'MIGRATION_TABLE_EXISTS' => $status['table_exists'],
    'MIGRATION_CURRENT_VERSION' => $status['current_version'],
    'MIGRATION_APPLIED_COUNT' => count($status['applied_migrations']),
    'MIGRATION_PENDING_COUNT' => count($status['pending_migrations']),

    // Setup status fields
    'SETUP_REQUIRES_SETUP' => $status['requires_setup'] ?? false,
    'SETUP_TYPE' => $status['setup_status']['type'] ?? __('UNKNOWN'),
    'SETUP_MESSAGE' => $status['setup_status']['message'] ?? '',
    'SETUP_ACTION_REQUIRED' => $status['setup_status']['action_required'] ?? false,
    'SETUP_INSTRUCTIONS' => $status['setup_status']['instructions'] ?? '',

    // Schema info individual fields
    'SCHEMA_DATABASE_NAME' => $schemaInfo['database_name'],
    'SCHEMA_TABLE_COUNT' => $schemaInfo['table_count'],
    'SCHEMA_SIZE_MB' => $schemaInfo['size_mb'],
]);

// Assign migration data for template
if (!empty($status['applied_migrations'])) {
    foreach ($status['applied_migrations'] as $i => $migration) {
        template()->assign_block_vars('applied_migrations', [
            'VERSION' => $migration['version'],
            'NAME' => $migration['migration_name'] ?? __('UNKNOWN'),
            'START_TIME' => $migration['start_time'] ?? __('UNKNOWN'),
            'END_TIME' => $migration['end_time'] ?? __('UNKNOWN'),
            'ROW_CLASS' => ($i % 2) ? 'row1' : 'row2',
        ]);
    }
}

if (!empty($status['pending_migrations'])) {
    foreach ($status['pending_migrations'] as $i => $migration) {
        template()->assign_block_vars('pending_migrations', [
            'VERSION' => $migration['version'],
            'NAME' => $migration['name'],
            'FILENAME' => $migration['filename'],
            'ROW_CLASS' => ($i % 2) ? 'row1' : 'row2',
        ]);
    }
}

// Output template using standard admin pattern
print_page('admin_migrations.tpl', 'admin');
