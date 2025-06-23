<p align="center"><a href="https://torrentpier.com"><img src="https://torrentpier.com/styles/default/xenforo/bull-logo.svg" width="400px" alt="TorrentPier" /></a></p>

<p align="center">
  Bull-powered BitTorrent tracker engine
  <br/>
</p>

<p align="center">
  <a href="https://github.com/torrentpier/torrentpier/blob/master/LICENSE"><img src="https://img.shields.io/github/license/torrentpier/torrentpier" alt="License"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/stars/torrentpier/torrentpier" alt="Stars Packagist"></a>
  <a href="https://crowdin.com/project/torrentpier"><img src="https://badges.crowdin.net/torrentpier/localized.svg" alt="Crowdin"></a>
  <a href="https://nightly.link/torrentpier/torrentpier/workflows/ci/master/TorrentPier-master"><img src="https://img.shields.io/badge/Nightly%20release-gray?logo=hackthebox&logoColor=fff" alt="TorrentPier nightly"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/dt/torrentpier/torrentpier" alt="Downloads"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/v/torrentpier/torrentpier" alt="Version"></a>
  <a href="https://github.com/torrentpier/torrentpier/releases"><img src="https://img.shields.io/github/release-date/torrentpier/torrentpier" alt="Last release"></a>
  <img src="https://img.shields.io/github/repo-size/torrentpier/torrentpier" alt="Size">
  <a href="https://github.com/SamKirkland/FTP-Deploy-Action"><img src="https://img.shields.io/badge/Deployed to TorrentPier Demo with-FTP DEPLOY ACTION-%3CCOLOR%3E?color=2b9348" alt="Deployed to TorrentPier Demo with FTP Deploy Action"></a>
</p>

## 🐂 About TorrentPier

TorrentPier — bull-powered BitTorrent Public/Private tracker engine, written in PHP. High speed, simple modifications, load-balanced
architecture. In addition, we have a very helpful
[official support forum](https://torrentpier.com), where it's possible to get support and download modifications for the engine.

## 🌈 Current status

TorrentPier is currently undergoing a **major 3.0 rewrite** to remove all legacy code and modernize the codebase to current PHP standards. **Backward compatibility is not a priority** - this release focuses on moving forward with clean, modern architecture. If you want to delve deep into the code, check our [issues](https://github.com/torrentpier/torrentpier/issues) and go from there.

> [!NOTE]
> TorrentPier 3.0 will introduce breaking changes. Existing installations should remain on 2.x versions until ready to migrate to the new architecture.

## ✨ Features
* Rich forum with browsing/moderation tools
* High-load capable, heavily configurable announcer
* Scrape support
* FreeLeech
* [TorrServer integration](https://github.com/YouROK/TorrServer) support
* BitTorrent v2 support
* Event-based invite system
* Bonus points
* Polling system
* PM/DM system
* Multilingual support (Russian and English are currently fully supported, with others in the future)
* Atom/RSS feeds
* ... and so MUCH MORE!

## 🖥️ Demo

* URL: https://torrentpier.duckdns.org
* Username: `admin`
* Password: `admin`

> [!NOTE]
> Demo resets every 24 hours!

## 🔧 Requirements

* Apache / nginx ([example config](install/nginx.conf)) / caddy ([example config](install/Caddyfile))
* MySQL 5.5.3 or above (including MySQL 8.0+) / MariaDB 10.0 or above / Percona
* PHP: 8.3 / 8.4
* PHP Extensions: mbstring, gd, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

For the installation, select one of the installation variants below:

### Quick (Clean install) 🚀

Check out our [autoinstall](https://github.com/torrentpier/autoinstall) repository with detailed instructions.

> [!NOTE]
> Thanks to [Sergei Solovev](https://github.com/SeAnSolovev) for this installation script ❤️

### Quick (For web-panels) ☕️

1. Select the folder where you want TorrentPier installed
   ```shell
   cd /path/to/public_html
   ```
2. Download the latest version of TorrentPier
   ```shell
   sudo git clone https://github.com/torrentpier/torrentpier.git .
   ```
3. After completing, execute the command below and follow the instructions
   ```shell
   php install.php
   ```
4. Voila! ✨

### Manual 🔩

1. Install [Composer](https://getcomposer.org/)
2. Run the following command to create the TorrentPier project
   ```shell
   composer create-project torrentpier/torrentpier
   ```
3. [Check our system requirements](#-requirements)
4. After, run this command in the project directory to install Composer dependencies
   ```shell
   composer install
   ```
5. Edit database configuration settings in the environment (`.env.example`), after, rename to `.env`
6. Create a database and run migrations to set up the schema
   ```shell
   php vendor/bin/phinx migrate --configuration=phinx.php
   ```
7. Provide write permissions to the specified folders:
   * `data/avatars`, `data/uploads`, `data/uploads/thumbs`
   * `internal_data/atom`, `internal_data/cache`, `internal_data/log`, `internal_data/triggers`
   * `sitemap`
8. Voila! ✨

> [!TIP]
> You can automate steps 4-7 by running `php install.php` instead, which will guide you through the setup process interactively.

> [!IMPORTANT]
> The specific settings depend on the server you are using, but in general we recommend chmod **0755** for folders, and chmod **0644** for the files in them.

### Additional steps 👣

1. Edit these files:
   * `favicon.png` (change to your own)
   * `robots.txt` (change the addresses in lines `Host` and `Sitemap` to your own)
2. Log in to the forum using the **admin/admin** login/password, and finish setting up via admin panel. Don't forget to change your password!

## 🔐 Security vulnerabilities

If you discover a security vulnerability within TorrentPier, please follow our [security policy](https://github.com/torrentpier/torrentpier/security/policy), so we can address it promptly.

## 🧪 Testing

TorrentPier includes a comprehensive testing suite built with **Pest PHP**. Run tests to ensure code quality and system reliability:

```shell
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage
```

For detailed testing documentation, see [tests/README.md](tests/README.md).

## 🖥️ CLI Usage

TorrentPier 3.0 includes a Laravel-style CLI tool called **Dexter** for managing your application:

```shell
# Basic usage
php dexter                    # Shows available commands
php dexter info              # System information
php dexter cache:clear       # Clear caches
php dexter migrate          # Run database migrations
php dexter help <command>   # Command help
```

## 🦌 Laravel Herd Configuration

If you're using [Laravel Herd](https://herd.laravel.com) for local development, you'll need special configuration to properly serve the legacy `/admin/` and `/bt/` directories. By default, Herd routes all requests through the modern front controller, but these directories need to be served directly.

### The Problem

TorrentPier has legacy directories (`/admin/` and `/bt/`) that contain their own `index.php` files and should be processed directly by the web server, not through the Laravel-style routing system. Laravel Herd's default nginx configuration sends all requests to `public/index.php`, which causes these legacy endpoints to fail.

### Solution: Site-Specific Nginx Configuration

#### Step 1: Generate Custom Nginx Config

Run one of these commands to create a site-specific nginx configuration:

```shell
# Option A: Isolate PHP version
herd isolate php@8.4

# Option B: Secure the site with SSL
herd secure
```

This generates a custom nginx configuration file at:
`~/Library/Application\ Support/Herd/config/valet/Nginx/[your-site-name]`

#### Step 2: Edit the Generated Config

Open the generated nginx configuration file and add these location blocks **before** the main Laravel location block:

```nginx
# Serve /admin/ directory directly
location /admin/ {
    alias /path/to/your/torrentpier/admin/;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/opt/homebrew/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        include fastcgi_params;
    }
}

# Serve /bt/ directory directly
location /bt/ {
    alias /path/to/your/torrentpier/bt/;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/opt/homebrew/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        include fastcgi_params;
    }
}
```

> **Note**: Replace `/path/to/your/torrentpier/` with the actual path to your TorrentPier installation.

#### Step 3: Restart Herd

```shell
herd restart
```

### Alternative Solutions

#### Option 1: Root .htaccess (May work with some Herd configurations)

Create a `.htaccess` file in your project root:

```apache
RewriteEngine On

# Exclude admin and bt directories from Laravel routing
RewriteRule ^admin/ - [L]
RewriteRule ^bt/ - [L]

# Handle everything else through Laravel
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [L]
```

#### Option 2: Move to Public Directory

Move the directories to the public folder:

```shell
mv admin/ public/admin/
mv bt/ public/bt/
```

> **Warning**: This requires updating any hardcoded paths in the legacy code.

### Testing Your Configuration

After applying the configuration, test these URLs:

```shell
# Admin panel should load
curl -I http://your-site.test/admin/

# BitTorrent tracker should respond
curl -I http://your-site.test/bt/

# Announce endpoint should work
curl -I http://your-site.test/bt/announce.php

# Modern routes should still work
curl -I http://your-site.test/hello
```

All should return HTTP 200 status codes.

### Troubleshooting

- **502 Bad Gateway**: Check PHP-FPM socket path in nginx config
- **404 Not Found**: Verify directory paths in nginx location blocks
- **403 Forbidden**: Check file permissions on admin/bt directories
- **Still routing through Laravel**: Ensure location blocks are placed before the main Laravel location block

For more details about Herd nginx configuration, see the [official Herd documentation](https://herd.laravel.com/docs/macos/sites/nginx-configuration).

## 📌 Our recommendations

* *It's recommended to run `cron.php`.* - For significant tracker speed increase it may be required to replace the built-in cron.php with an operating system daemon.
* *Local configuration copy.* - You can override the settings using the local configuration file `library/config.local.php`.

## 💚 Contributing / Contributors

Please read our [contributing policy](CONTRIBUTING.md) and [code of conduct](CODE_OF_CONDUCT.md) for details, and the process for
submitting pull requests to us. But we are always ready to review your pull-request for compliance with
these requirements. Just send it!

<a href="https://github.com/torrentpier/torrentpier/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=torrentpier/torrentpier" alt="Contributors"/>
</a>

Made with [contrib.rocks](https://contrib.rocks).

## 💞 Sponsoring

Support this project by becoming a sponsor or a backer.

[![OpenCollective sponsors](https://opencollective.com/torrentpier/sponsors/badge.svg)](https://opencollective.com/torrentpier)
[![OpenCollective backers](https://opencollective.com/torrentpier/backers/badge.svg)](https://opencollective.com/torrentpier)

<details>
  <summary>Monero</summary>

```
42zJE3FDvN8foP9QYgDrBjgtd7h2FipGCGmAcmG5VFQuRkJBGMbCvoLSmivepmAMEgik2E8MPWUzKaoYsGCtmhvL7ZN73jh
```
</details>

<details>
  <summary>YooMoney</summary>

```
4100118022415720
```
</details>

## 📦 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags).

## 📖 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/torrentpier/torrentpier/blob/master/LICENSE) file for details.
