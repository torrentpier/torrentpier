---
sidebar_position: 1
---

# Installation

:::warning Work in Progress
This documentation is currently under development and not yet complete. Some sections may be incomplete or subject to change as the project evolves.
:::

This guide will help you install and set up TorrentPier on your server.

## Requirements

Before installing TorrentPier, ensure your server meets these requirements:

- **PHP** 8.4 or higher
- **MySQL** 8.0+ / PostgreSQL 15+ / SQLite 3.8.8+ / SQL Server 2017+
- **Node.js** 18.0 or higher
- **Composer** 2.0 or higher
- **Redis** (optional, for caching and queues)

### PHP Extensions

Ensure the following PHP extensions are installed:
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/torrentpier/torrentpier.git torrentpier
cd torrentpier
```

### 2. Install Dependencies

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

### 3. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 4. Configure Database

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=torrentpier
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

Create the database tables:

```bash
php artisan migrate
```

Optionally, seed the database with sample data:

```bash
php artisan db:seed
```

### 6. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 7. Configure Web Server

#### Apache

Ensure `mod_rewrite` is enabled and point your document root to the `public` directory.

#### Nginx

Example configuration:

```nginx
server {
    listen 80;
    server_name torrentpier.local;
    root /path/to/torrentpier/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Post-Installation

### Set Up Cron Jobs

Add the Laravel scheduler to your crontab:

```bash
* * * * * cd /path/to/torrentpier && php artisan schedule:run >> /dev/null 2>&1
```

### Configure Queue Workers

If using queues, set up a supervisor configuration:

```ini
[program:torrentpier-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/torrentpier/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/torrentpier/storage/logs/worker.log
```

## Troubleshooting

### Permission Issues

Ensure proper permissions for storage and cache directories:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear Caches

If you encounter issues, try clearing all caches:

```bash
php artisan optimize:clear
```

## Next Steps

- Configure [Application Settings](/docs/getting-started/configuration)
- Set up [Email Configuration](/docs/getting-started/email)
- Review [Security Best Practices](/docs/getting-started/security)
