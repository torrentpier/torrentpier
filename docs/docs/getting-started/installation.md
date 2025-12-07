---
sidebar_position: 1
---

# Installation

This guide will help you install TorrentPier on your server.

## Requirements

- **PHP** 8.4 or higher
- **MySQL** 8.0+ / MariaDB 10.5+ / Percona Server
- **Composer** 2.0 or higher
- **Web server**: Apache or Nginx

### PHP extensions

Ensure these PHP extensions are installed:

- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO + pdo_mysql
- Tokenizer
- XML
- Zlib

## Installation methods

### Method 1: Automated installer (recommended)

```bash
git clone https://github.com/torrentpier/torrentpier.git
cd torrentpier
composer install
php install.php
```

The installer will guide you through:
- Database configuration
- Admin account creation
- Initial settings

### Method 2: Composer create-project

```bash
composer create-project torrentpier/torrentpier
cd torrentpier
php install.php
```

### Method 3: Manual installation

1. Clone or download the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Configure database settings in `.env`
5. Run migrations: `php vendor/bin/phinx migrate`
6. Configure your web server

## Environment configuration

Edit `.env` file with your settings:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=torrentpier
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Site
SITE_URL=https://your-domain.com

# Cache (optional)
CACHE_DRIVER=file
```

## Web server configuration

### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/torrentpier;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|git|env) {
        deny all;
    }
}
```

### Apache

Ensure `mod_rewrite` is enabled. The `.htaccess` file is included in the repository.

## Post-installation

### Set up cron jobs

Add to your crontab:

```bash
# Run maintenance tasks every minute
* * * * * php /path/to/torrentpier/cron.php >> /dev/null 2>&1
```

### Directory permissions

Ensure the web server can write to:

```bash
chmod -R 775 internal_data
chown -R www-data:www-data internal_data
```

## Troubleshooting

### Permission issues

```bash
chmod -R 775 internal_data
chmod 644 .env
```

### Database connection errors

- Verify credentials in `.env`
- Check MySQL is running
- Ensure database exists

### Blank page / 500 error

- Check PHP error log
- Verify all PHP extensions are installed
- Run `composer install` again
