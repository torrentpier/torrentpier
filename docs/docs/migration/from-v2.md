---
sidebar_position: 1
title: TorrentPier v2.x
---

# Migrating from TorrentPier v2.x

:::warning Work in Progress
This documentation is currently under development and not yet complete. Some sections may be incomplete or subject to change as the project evolves.
:::

This guide helps you migrate from the legacy TorrentPier v2.x to the new Laravel-based version.

## Overview

The new TorrentPier is a complete rewrite with:
- Modern architecture
- Improved performance
- Better security
- Enhanced user experience

## Pre-Migration Checklist

Before starting migration:

1. **Backup Everything**
   - Full database backup
   - All uploaded files (torrents, images)
   - Configuration files
   - Custom modifications

2. **Review Requirements**
   - PHP 8.4+ (vs PHP 7.0+ in v2.x)
   - MySQL 8.0+ recommended
   - More server resources needed

3. **Plan Downtime**
   - Migration can take hours for large sites
   - Inform your users
   - Choose low-traffic period

## Migration Steps

### Step 1: Prepare New Installation

```bash
# Install new TorrentPier
git clone https://github.com/torrentpier/torrentpier.git torrentpier
cd torrentpier
composer install
npm install && npm run build
```

### Step 2: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your settings:
```env
DB_DATABASE=torrentpier
LEGACY_DB_DATABASE=torrentpier_old
```

### Step 3: Run Migration Command

```bash
# This command will guide you through the migration
php artisan torrentpier:migrate
```

The migration tool will:
- Import users and profiles
- Convert forum structure
- Migrate topics and posts
- Import torrents and peers
- Transfer user statistics

### Step 4: Migrate Files

```bash
# Copy torrent files
cp -r /path/to/old/data/torrent_files /path/to/new/storage/app/torrents

# Copy user avatars
cp -r /path/to/old/data/avatars /path/to/new/storage/app/avatars

# Copy attachments
cp -r /path/to/old/data/attachments /path/to/new/storage/app/attachments
```

### Step 5: Update Configuration

Map old settings to new:

| Old Setting                  | New Setting             | Notes        |
|------------------------------|-------------------------|--------------|
| `$bb_cfg['bt_announce_url']` | `TRACKER_ANNOUNCE_URL`  | In .env file |
| `$bb_cfg['ratio_enabled']`   | `TRACKER_RATIO_ENABLED` | Boolean      |

### Step 6: Test Migration

```bash
# Run in staging environment first
php artisan migrate:status
php artisan torrentpier:verify
```

## Post-Migration

### 1. Verify Data Integrity

```bash
# Check user counts
php artisan torrentpier:check users

# Check torrent counts
php artisan torrentpier:check torrents

# Verify statistics
php artisan torrentpier:check stats
```

### 2. Update DNS

Point your domain to the new installation.

### 3. Monitor Performance

- Watch error logs
- Monitor tracker announces
- Check user reports

### 4. Clean Up

After successful migration:
```bash
# Remove migration flags
php artisan cache:clear

# Optimize
php artisan optimize
```

## Common Issues

### Password Reset Required

Users must reset passwords due to improved hashing.

### Missing Features

Some v2.x features may be pending:
- Check roadmap
- Request features
- Contribute code

### Performance Differences

New version may need tuning:
- Configure Redis
- Optimize database
- Use queue workers

## Custom Modifications

If you had custom mods in v2.x:

1. Document modifications
2. Evaluate if still needed
3. Reimplement using Laravel patterns
4. Submit as pull requests
