---
sidebar_position: 2
title: Recommendations
---

# Recommendations

Best practices and recommendations for running TorrentPier in production.

## Cron Jobs

TorrentPier supports two cron modes:

### Option 1: TorrentPier Cron Manager (Default)

Set `APP_CRON_ENABLED=true` in `.env`. TorrentPier will handle cron internally without external crontab. No additional setup required.

This is the recommended option for most installations.

### Option 2: External Cron (High-Load Production)

Set `APP_CRON_ENABLED=false` in `.env` and add to your crontab:

```bash
# Add to crontab (crontab -e)
# Run maintenance tasks every 10 minutes
*/10 * * * * cd /path/to/torrentpier && php bull cron:run >> /dev/null 2>&1
```

For high-traffic trackers, external cron provides better control and monitoring.

:::tip Docker
Docker installations automatically use external cron. The container runs `php bull cron:run` every 10 minutes. Set `APP_CRON_ENABLED=false` in your `.env` file.
:::

### What Cron Handles

- Session cleanup
- Cache maintenance
- Tracker statistics updates
- Sitemap generation
- Log rotation
- Dead torrent cleanup

### Verify Cron Status

```bash
# List all scheduled tasks
php bull cron:list

# Run cron manually for testing
php bull cron:run -v
```

## Local Configuration

Override settings using the local configuration file `config/config.local.php`. This file:

- Is not tracked by Git (add to `.gitignore`)
- Takes precedence over main configuration
- Is ideal for environment-specific settings

### Example Local Config

```php
<?php
// config/config.local.php

return [
    // Enable debug mode for development
    'debug' => [
        'enable' => true,
    ],

    // Override database settings
    'database' => [
        'host' => '127.0.0.1',
        'database' => 'torrentpier_dev',
    ],

    // Custom site settings
    'sitename' => 'My Dev Tracker',
];
```

## Performance Tips

### Enable OPcache

Ensure PHP OPcache is enabled for significant performance gains:

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Set to 1 in development
```

### Cache Configuration

Use file-based caching for small installations or Redis/Memcached for larger deployments:

```php
// config/config.local.php
return [
    'cache' => [
        'type' => 'redis',  // or 'memcached', 'file'
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
];
```

### Database Optimization

Run regular database maintenance:

```bash
# Optimize database tables
php bull database:optimize

# Check database health
php bull database:check
```

## Security Recommendations

### File Permissions

```bash
# Recommended permissions
chmod -R 755 storage
chmod -R 755 public/storage
chmod 644 .env
chmod 644 config/*.php
```

### Environment Security

- Never expose `.env` file publicly
- Use strong database passwords
- Enable HTTPS in production
- Configure trusted proxies for CDN

### Regular Updates

Keep TorrentPier and dependencies updated:

```bash
# Update dependencies
composer update

# Run migrations after updates
php bull migrate
```

## Backup Strategy

### Database Backups

```bash
# Create backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated daily backups (add to cron)
0 3 * * * mysqldump -u user -p'pass' torrentpier > /backups/torrentpier_$(date +\%Y\%m\%d).sql
```

### File Backups

Important directories to backup:
- `storage/` - Uploaded files, cache
- `config/config.local.php` - Local configuration
- `.env` - Environment settings

## Monitoring

### Log Files

Monitor these log files for issues:

```bash
# Application logs
tail -f storage/logs/torrentpier.log

# PHP error log
tail -f /var/log/php-fpm/error.log

# Web server logs
tail -f /var/log/nginx/error.log
```

### Health Checks

```bash
# System information
php bull about

# Cache status
php bull cache:status

# Migration status
php bull migrate:status
```
