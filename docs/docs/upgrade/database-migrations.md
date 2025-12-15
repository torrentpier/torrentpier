---
sidebar_position: 2
title: Database Migration System
---

# Database Migration System

TorrentPier now includes a modern database migration system using **Phinx** (from CakePHP), replacing the legacy direct SQL import approach. This provides version-controlled database schema management with rollback capabilities.

## Key Benefits

- **Version Control**: Database schema changes are tracked in code
- **Environment Consistency**: Same database structure across development, staging, and production
- **Safe Rollbacks**: Ability to safely revert schema changes
- **Team Collaboration**: No more merge conflicts on database changes
- **Automated Deployments**: Database updates as part of deployment process

## Migration Architecture

### Engine Strategy

- **InnoDB**: Used for all tables for maximum data integrity and reliability
- **ACID Compliance**: Full transaction support and crash recovery for all data
- **Row-Level Locking**: Better concurrency for high-traffic operations

### Directory Structure

```
/database/
  └── migrations/
        ├── 20250619000001_initial_schema.php    # Complete database schema
        ├── 20250619000002_seed_initial_data.php # Essential data seeding
        └── future_migrations...                 # Your custom migrations
/phinx.php                                       # Migration configuration
```

## For New Installations

New installations automatically use migrations instead of the legacy SQL dump:

```bash
# Fresh installation now uses Bull CLI
composer install
php bull app:install
```

The installer will:
1. Set up environment configuration
2. Create the database
3. Run all migrations automatically
4. Seed initial data (admin user, configuration, etc.)

## For Existing Installations

Existing installations continue to work without changes. The migration system is designed for new installations and development workflows.

:::warning Important
Existing installations should **not** attempt to migrate to the new system without proper backup and testing procedures.
:::

## Developer Workflow

### Creating Migrations

```bash
# Create a new migration
php vendor/bin/phinx create AddNewFeatureTable

# Edit the generated migration file
# /migrations/YYYYMMDDHHMMSS_add_new_feature_table.php
```

### Running Migrations

```bash
# Run all pending migrations
php vendor/bin/phinx migrate

# Check migration status
php vendor/bin/phinx status

# Rollback last migration
php vendor/bin/phinx rollback
```

### Migration Template

```php
<?php
use Phinx\Migration\AbstractMigration;

class AddNewFeatureTable extends AbstractMigration
{
    public function change()
    {
        // InnoDB for data integrity
        $table = $this->table('bb_new_feature', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('name', 'string', ['limit' => 100])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex('name')
              ->create();
    }

    // Optional: explicit up/down methods for complex operations
    public function up()
    {
        // Complex data migration logic
    }

    public function down()
    {
        // Rollback logic
    }
}
```

### Engine Guidelines

```php
// Use InnoDB for all tables for maximum reliability
$table = $this->table('bb_user_posts', [
    'engine' => 'InnoDB',
    'collation' => 'utf8mb4_unicode_ci'
]);

// All tracker tables also use InnoDB for data integrity
$table = $this->table('bb_bt_peer_stats', [
    'engine' => 'InnoDB',
    'collation' => 'utf8mb4_unicode_ci'
]);

// Buffer tables use InnoDB for consistency and reliability
public function up() {
    $this->execute('DROP TABLE IF EXISTS buf_temp_data');
    // Recreate with new structure using InnoDB
}
```

## Admin Panel Integration

The admin panel includes a read-only migration status page at `/admin/admin_migrations.php`:

- **Current migration version**
- **Applied migrations history**
- **Pending migrations list**
- **Database statistics**
- **Clear instructions for CLI operations**

:::note Security
The admin panel is **read-only** for security. All migration operations must be performed via CLI.
:::

## Complex Migration Handling

For complex data transformations, create external scripts:

```php
// migrations/YYYYMMDDHHMMSS_complex_data_migration.php
class ComplexDataMigration extends AbstractMigration
{
    public function up()
    {
        $this->output->writeln('Running complex data migration...');

        // Call external script for complex operations
        $result = shell_exec('php ' . __DIR__ . '/../scripts/migrate_torrent_data.php');
        $this->output->writeln($result);

        if (strpos($result, 'ERROR') !== false) {
            throw new Exception('Complex migration failed');
        }
    }
}
```

## Best Practices

### Migration Development

```bash
# 1. Create migration
php vendor/bin/phinx create MyFeature

# 2. Edit migration file
# 3. Test locally
php vendor/bin/phinx migrate -e development

# 4. Test rollback
php vendor/bin/phinx rollback -e development

# 5. Commit to version control
git add migrations/
git commit -m "Add MyFeature migration"
```

### Production Deployment

```bash
# Always backup database first
mysqldump tracker_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
php vendor/bin/phinx migrate -e production

# Verify application functionality
# Monitor error logs
```

### Team Collaboration

- **Never modify existing migrations** that have been deployed
- **Always create new migrations** for schema changes
- **Test migrations on production-like data** before deployment
- **Coordinate with team** before major schema changes

## Configuration

The migration system uses your existing `.env` configuration:

```php
// phinx.php automatically reads from .env
'production' => [
    'adapter' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => (int) env('DB_PORT', 3306),
    'name' => env('DB_DATABASE'),
    'user' => env('DB_USERNAME'),
    'pass' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
]
```

## Troubleshooting

### Common Issues

```bash
# Migration table doesn't exist
php vendor/bin/phinx init  # Re-run if needed

# Migration fails mid-way
php vendor/bin/phinx rollback  # Rollback to previous state

# Check what would be applied
php vendor/bin/phinx status  # See pending migrations
```

### Migration Recovery

```bash
# If migration fails, check status first
php vendor/bin/phinx status

# Rollback to known good state
php vendor/bin/phinx rollback -t 20250619000002

# Fix the migration code and re-run
php vendor/bin/phinx migrate
```

## Migration Setup for Existing Installations

If you have an **existing TorrentPier installation** and want to adopt the migration system, you need to mark the initial migrations as already applied.

### Detection: Do You Need This?

You need migration setup if:
- You have an existing TorrentPier installation with data
- Your database already has tables like `bb_users`, `bb_forums`, etc.
- The admin migration panel shows "Migration System: Not Initialized"

### Step-by-Step Setup Process

**1. Backup Your Database**
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

**2. Initialize Migration Table**
```bash
# This creates the bb_migrations table without running any migrations
php vendor/bin/phinx init
```

**3. Mark Initial Migrations as Applied (Fake Run)**
```bash
# Mark the schema migration as applied without running it
php vendor/bin/phinx migrate --fake --target=20250619000001

# Mark the data seeding migration as applied without running it
php vendor/bin/phinx migrate --fake --target=20250619000002
```

**4. Verify Setup**
```bash
# Check migration status
php vendor/bin/phinx status
```

You should see both initial migrations marked as "up" (applied).

### Alternative: Manual SQL Method

If you prefer manual control, you can directly insert migration records:

```sql
-- Create migration table (if phinx init didn't work)
CREATE TABLE IF NOT EXISTS bb_migrations (
    version bigint(20) NOT NULL,
    migration_name varchar(100) DEFAULT NULL,
    start_time timestamp NULL DEFAULT NULL,
    end_time timestamp NULL DEFAULT NULL,
    breakpoint tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mark initial migrations as applied
INSERT INTO bb_migrations (version, migration_name, start_time, end_time, breakpoint)
VALUES
('20250619000001', 'InitialSchema', NOW(), NOW(), 0),
('20250619000002', 'SeedInitialData', NOW(), NOW(), 0);
```

### Post-Setup Workflow

After setup, your existing installation will work exactly like a fresh installation:

```bash
# Create new migrations
php vendor/bin/phinx create AddNewFeature

# Run new migrations
php vendor/bin/phinx migrate

# Check status
php vendor/bin/phinx status
```

## Security Considerations

- **CLI-only execution**: Migrations run via command line only
- **Read-only admin interface**: Web interface shows status only
- **Backup requirements**: Always backup before production migrations
- **Access control**: Restrict migration command access to authorized personnel
