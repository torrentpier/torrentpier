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
</p>

## 🐂 About TorrentPier

TorrentPier — bull-powered BitTorrent Public/Private tracker engine, written in PHP. High speed, simple modifications, load-balanced
architecture. In addition, we have a very helpful
[official support forum](https://torrentpier.com), where it's possible to get support and download modifications for the engine.

## 🌈 Current status

TorrentPier 3.0 is a complete architectural overhaul of the engine, moving from legacy PHP to modern Laravel-inspired patterns.

**Key modernizations in 3.0:**
- **DI Container**: Illuminate Container with 11 service providers
- **Bootstrap Pipeline**: Modular initialization with bootstrappers
- **PSR-7/PSR-15**: Modern HTTP stack with middleware architecture
- **Twig Templates**: Replacing a legacy styles system
- **Bull CLI**: Console commands for all operations
- **Unified Routing**: Single entry point with semantic URLs

See the [Upgrade Guide](https://docs.torrentpier.com/docs/upgrade) for migration details and the [documentation](https://docs.torrentpier.com) for full reference.

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
* PHP: 8.4 / 8.5
* PHP Extensions: mysqli, mbstring, gd, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

For detailed installation instructions, see our [Installation Guide](https://docs.torrentpier.com/docs/getting-started/installation).

**Quick start:**
```shell
git clone https://github.com/torrentpier/torrentpier.git
cd torrentpier
composer install
php bull app:install
```

For Docker setup, see the [Docker documentation](https://docs.torrentpier.com/docs/getting-started/installation#method-4-docker).

> [!TIP]
> The `php bull app:install` wizard handles all configuration: environment setup, database creation, migrations, and permissions.

**After installation:**
1. Log in using **admin/admin** credentials
2. Change your password immediately
3. Configure the site via admin panel

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

## 📦 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags).

## 📖 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/torrentpier/torrentpier/blob/master/LICENSE) file for details.
