---
sidebar_position: 1
title: Database migrations
---

# Database migrations

TorrentPier uses [Phinx](https://phinx.org/) for database migrations. This guide covers how to run and create migrations.

## Running migrations

### Check migration status

See which migrations have been applied:

```bash
php vendor/bin/phinx status -c phinx.php
```

### Run pending migrations

Apply all pending migrations:

```bash
php vendor/bin/phinx migrate -c phinx.php
```

### Run specific migration

Migrate to a specific version:

```bash
php vendor/bin/phinx migrate -c phinx.php -t 20250619000001
```

### Rollback migrations

Rollback the last migration:

```bash
php vendor/bin/phinx rollback -c phinx.php
```

Rollback to a specific version:

```bash
php vendor/bin/phinx rollback -c phinx.php -t 20250619000001
```

## For existing installations

If you're upgrading an existing TorrentPier installation, you may need to mark migrations as already applied:

```bash
php vendor/bin/phinx migrate -c phinx.php --fake
```

This records the migrations as applied without actually running them.

## Creating migrations

### Generate a new migration

```bash
php vendor/bin/phinx create MyNewMigration -c phinx.php
```

This creates a file in `migrations/` with a timestamp prefix.

### Migration structure

```php
<?php

use Phinx\Migration\AbstractMigration;

final class MyNewMigration extends AbstractMigration
{
    public function up(): void
    {
        // Apply changes
        $this->table('my_table')
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['name'])
            ->create();
    }

    public function down(): void
    {
        // Reverse changes
        $this->table('my_table')->drop()->save();
    }
}
```

### Common operations

#### Create table

```php
$this->table('users')
    ->addColumn('username', 'string', ['limit' => 50])
    ->addColumn('email', 'string', ['limit' => 100])
    ->addColumn('created_at', 'datetime')
    ->addIndex(['username'], ['unique' => true])
    ->addIndex(['email'], ['unique' => true])
    ->create();
```

#### Modify table

```php
$this->table('users')
    ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true])
    ->changeColumn('email', 'string', ['limit' => 150])
    ->removeColumn('old_column')
    ->update();
```

#### Add foreign key

```php
$this->table('posts')
    ->addColumn('user_id', 'integer')
    ->addForeignKey('user_id', 'users', 'id', [
        'delete' => 'CASCADE',
        'update' => 'NO_ACTION'
    ])
    ->create();
```

#### Raw SQL

```php
$this->execute('ALTER TABLE users ADD FULLTEXT INDEX (username, email)');
```

## Seed data

### Run seeders

```bash
php vendor/bin/phinx seed:run -c phinx.php
```

### Run specific seeder

```bash
php vendor/bin/phinx seed:run -c phinx.php -s UserSeeder
```

## Configuration

Migration settings are in `phinx.php`:

```php
return [
    'paths' => [
        'migrations' => 'migrations',
        'seeds' => 'migrations/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_DATABASE'),
            'user' => getenv('DB_USERNAME'),
            'pass' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
        ]
    ]
];
```

## Best practices

1. **Always test migrations** on a copy of production data before deploying
2. **Write reversible migrations** with proper `down()` methods
3. **Keep migrations small** — one logical change per migration
4. **Never modify** existing migrations that have been applied to production
5. **Use timestamps** in migration names for proper ordering
