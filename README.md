<p align="center"><a href="https://github.com/torrentpier/torrentpier"><img src="https://files-ox.torrentpier.com/tp_ox_long.svg" width="400px" alt="TorrentPier" /></a></p>

<p align="center">
  Bull-powered BitTorrent tracker engine
  <br/>
</p>

<p align="center">
  <a href="https://github.com/torrentpier/torrentpier/blob/master/LICENSE"><img src="https://img.shields.io/github/license/torrentpier/torrentpier" alt="License"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/stars/torrentpier/torrentpier" alt="Stars Packagist"></a>
  <a href="https://crowdin.com/project/torrentpier"><img src="https://badges.crowdin.net/torrentpier/localized.svg" alt="Crowdin"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/dt/torrentpier/torrentpier" alt="Downloads"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/v/torrentpier/torrentpier" alt="Version"></a>
  <img src="https://img.shields.io/github/repo-size/torrentpier/torrentpier" alt="Size">
</p>

> **TorrentPier 3.0 (Ox) is the final release of this codebase.**
> The project entered conservation in May 2026. No further patches, security
> fixes, or feature work are planned.
> The community forum is preserved read-only at <https://ox.torrentpier.com/>.
> A new generation of the engine is being written from scratch, possibly under
> a different name. There is no timeline yet.
> If you self-host this release, you are responsible for your own security
> maintenance.

## 🐂 About TorrentPier

TorrentPier — bull-powered BitTorrent tracker engine, written in PHP. High speed, simple modifications, load-balanced
architecture. The original community forum is preserved read-only at
[ox.torrentpier.com](https://ox.torrentpier.com/) as a historical archive.

## 🌈 Current status

TorrentPier 3.0 (Ox) is the final release of this codebase. The project has
entered conservation: no further patches, security fixes, or feature work are
planned. GitHub Issues remain open as a low-noise channel for community
discussion, but there is no commitment to respond.

A new generation of the engine is being written from scratch, possibly under a
different name. There is no timeline yet.

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

## 🔧 Requirements

* Apache / nginx ([example config](install/nginx.conf)) / caddy ([example config](install/Caddyfile))
* MySQL 5.5.3 or above (including MySQL 8.0+) / MariaDB 10.0 or above / Percona
* PHP: 8.4 / 8.5
* PHP Extensions: mysqli, mbstring, gd, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

**Quick start:**
```shell
git clone https://github.com/torrentpier/torrentpier.git
cd torrentpier
composer install
php bull app:install
```

For Docker setup, see [`docker-compose.yml`](docker-compose.yml) and
[`Dockerfile`](Dockerfile) in this repository.

> [!TIP]
> The `php bull app:install` wizard handles all configuration: environment setup, database creation, migrations, and permissions.

**After installation:**
1. Log in using **admin/admin** credentials
2. Change your password immediately
3. Configure the site via admin panel

## 🔐 Security vulnerabilities

This release is in conservation and will not receive security patches. If you
self-host TorrentPier 3.0 (Ox), you are responsible for your own security
maintenance. See [SECURITY.md](.github/SECURITY.md) for details.

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
