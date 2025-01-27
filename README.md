<p align="center"><a href="https://torrentpier.com"><img src="https://torrentpier.com/styles/default/xenforo/bull-logo.svg" width="400px" alt="TorrentPier" /></a></p>

<p align="center">
  Bull-powered BitTorrent tracker engine
  <br/>
</p>

<p align="center">
  <a href="https://github.com/torrentpier/torrentpier/blob/master/LICENSE"><img src="https://img.shields.io/github/license/torrentpier/torrentpier" alt="License"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/stars/torrentpier/torrentpier" alt="Stars Packagist"></a>
  <a href="https://github.com/torrentpier/torrentpier/actions"><img src="https://img.shields.io/github/actions/workflow/status/torrentpier/torrentpier/phpmd.yml" alt="Build status"></a>
  <a href="https://crowdin.com/project/torrentpier"><img src="https://badges.crowdin.net/torrentpier/localized.svg" alt="Crowdin"></a>
  <a href="https://nightly.link/torrentpier/torrentpier/workflows/build/master/TorrentPier"><img src="https://img.shields.io/badge/Nightly%20release-gray?logo=hackthebox&logoColor=fff" alt="TorrentPier nightly"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/dt/torrentpier/torrentpier" alt="Downloads"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/v/torrentpier/torrentpier" alt="Version"></a>
  <a href="https://github.com/torrentpier/torrentpier/releases"><img src="https://img.shields.io/github/release-date/torrentpier/torrentpier" alt="Last release"></a>
  <img src="https://img.shields.io/github/repo-size/torrentpier/torrentpier" alt="Size">
</p>

## 🐂 About TorrentPier

TorrentPier — bull-powered BitTorrent Public/Private tracker engine, written in php. High speed, simple modification, high load 
architecture. In addition, we have very helpful 
[official support forum](https://torrentpier.com), where it's possible to get any support and download modifications for engine.

## 🌈 Current status

TorrentPier is currently in active development. The goal is to remove all legacy code and rewrite existing to 
modern standards. If you want to go deep on the code, check our [issues](https://github.com/torrentpier/torrentpier/issues) 
and go from there. The documentation will be translated into english in the near future, currently russian is the main language of it.

## ✨ Features
* Rich forum browsing/moderation tools
* High-load capable, heavily configurable announcer
* Scrape support
* FreeLeech
* [TorrServer integration](https://github.com/YouROK/TorrServer) support
* BitTorrent v2 support
* Event-based invite system
* Bonus points
* Polls system
* PM system
* Multilingual support (Fully supported for now only Russia and English languages)
* Atom feeds
* ... and MUCH MORE!

## 🖥️ Demo

* URL: https://torrentpier.duckdns.org
* Username: `admin`
* Password: `admin`

> [!NOTE]
> Demo is resetting every 24 hours!

## 🔧 Requirements

* Apache / nginx / caddy
* MySQL 5.5.3 or above / MariaDB 10.0 or above / Percona
* PHP: 8.1 / 8.2 / 8.3
* PHP Extensions: mbstring, gd, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

For installation, select one of installation variants below.

### Quick (Clean install) 🚀

Check out our [autoinstall](https://github.com/torrentpier/autoinstall) repository with detailed instructions.

> [!IMPORTANT]
> Thanks to [Sergei Solovev](https://github.com/SeAnSolovev) for installation script ❤️

### Quick (For web-panels) ☕️

1. Select the folder where you want to install TorrentPier (`cd /path/to/public_html`)
2. Download latest version of TorrentPier (`sudo git clone https://github.com/torrentpier/torrentpier.git .`)
3. After, run `php install.php` and follow the given steps
4. Voila! ✨

### Manual 🔩

1. Install [Composer](https://getcomposer.org/)
2. Run `composer create-project torrentpier/torrentpier`
3. [Check our system requirements](#-requirements)
4. After, run `composer install` on the project directory
5. Create database and import dump located at `install/sql/mysql.sql`
6. Edit database configuration settings in the environment (`.env.example`), after, rename to `.env`
7. Provide write permissions to the specified folders:
   * `data/avatars`, `data/uploads`, `data/uploads/thumbs`
   * `internal_data/atom`, `internal_data/cache`, `internal_data/log`, `internal_data/triggers`
   * `sitemap`
8. Voila! ✨

> [!IMPORTANT]
> The specific settings depend on the server you are using, but in general case we recommend chmod **0755** for folders, and chmod **0644** for files in them.

### Additional steps 👣

1. Edit domain name and domain port in the configuration file or a local copy (`$reserved_name` and `$reserved_port`)
2. Edit this files:
   * `favicon.png` (change on your own)
   * `robots.txt` (change the addresses in lines `Host` and `Sitemap` on your own)
3. Log in to the forum with **admin/admin** login/password and finish setting up via admin panel

## 🔐 Security vulnerabilities

If you discover a security vulnerability within TorrentPier, please follow our [security policy](https://github.com/torrentpier/torrentpier/security/policy), so we can address it promptly.

## 📌 Our recommendations

* *The recommended way to run `cron.php`.* - For significant tracker speed increase may be required to replace built-in cron.php by operating system daemon.
* *Local configuration copy.* - You can override the settings using local configuration file `library/config.local.php`.

## 💚 Contributing / Contributors

Please read our [contributing policy](CONTRIBUTING.md) and [code of conduct](CODE_OF_CONDUCT.md) for details, and the process for 
submitting pull requests to us. But we are always ready to renew your pull-request for compliance with 
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
  42zJE3FDvN8foP9QYgDrBjgtd7h2FipGCGmAcmG5VFQuRkJBGMbCvoLSmivepmAMEgik2E8MPWUzKaoYsGCtmhvL7ZN73jh
</details>

<details>
  <summary>YooMoney</summary>
  4100118022415720
</details>

## 📦 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags). 

## 📖 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/torrentpier/torrentpier/blob/master/LICENSE) file for details.
