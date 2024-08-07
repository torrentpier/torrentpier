<p align="center"><a href="https://torrentpier.com"><img src="https://torrentpier.com/styles/default/xenforo/bull-logo.svg" width="400px" alt="TorrentPier" /></a></p>

<p align="center">
  Bull-powered BitTorrent tracker engine
  <br>
</p>

<p align="center">
  <a href="https://github.com/torrentpier/torrentpier/blob/master/LICENSE"><img src="https://img.shields.io/github/license/torrentpier/torrentpier" alt="License"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/stars/torrentpier/torrentpier" alt="Stars Packagist"></a>
  <a href="https://github.com/torrentpier/torrentpier/actions"><img src="https://img.shields.io/github/actions/workflow/status/torrentpier/torrentpier/phpmd.yml" alt="Build status"></a>
  <a href="https://crowdin.com/project/torrentpier"><img src="https://badges.crowdin.net/torrentpier/localized.svg" alt="Crowdin"></a>
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
* BitTorrent v2 support
* Event-based invite system
* Bonus points
* Polls system
* PM system
* Multilingual support
* Atom feeds
* and MUCH MORE!

## 🖥️ Demo

* URL: https://torrentpier.duckdns.org
* Username: admin
* Password: admin

Demo is reset every 24 hours!

## 🔧 Requirements

* Apache / nginx
* MySQL 5.5.3 or above / MariaDB 10.0 or above / Percona
* PHP: 8.1 / 8.2 / 8.3
* PHP Extensions: mbstring, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

For installation, you need to follow a few simple steps.

### Quick ☕️

1. [Download latest](https://github.com/torrentpier/torrentpier/releases) version of TorrentPier
2. Open directory with TorrentPier and run in CLI mode `php install.php`
3. Voila! ✨

### Manual 🔩

1. Install [Composer](https://getcomposer.org/)
2. Run `composer create-project torrentpier/torrentpier`
3. After run `composer install` on the project directory
4. Create database and import dump located at **install/sql/mysql.sql**
5. Edit database configuration settings in the environment (`.env.example`, after rename to `.env`)
6. Voila! ✨

### Additional steps 👣

1. Edit domain name and domain port in the configuration file or a local copy (`$reserved_name` and `$reserved_port`)
2. Edit this files:
   1. **favicon.png** (change on your own)
   2. **robots.txt** (change the addresses in lines `Host` and `Sitemap` on your own)
   3. **opensearch_desc.xml** (change the description and address on your own)
   4. **opensearch_desc_bt.xml** (change the description and address on your own)
3. Log in to the forum with **admin/admin** login/password and finish setting up via admin panel

## 🔑 Access rights on folders and files

You must provide write permissions to the specified folders:
* `data/avatars`
* `data/uploads`
* `data/uploads/thumbs`
* `internal_data/atom`
* `internal_data/cache`
* `internal_data/log`
* `internal_data/triggers`
* `sitemap`

The specific settings depend on the server you are using, but in general case we recommend chmod **0755** for folders, 
and chmod **0644** for files in them. If you are not sure, leave it as is.

## 📌 Our recommendations

* *The recommended way to run cron.php.* - For significant tracker speed increase may be required to replace built-in cron.php by operating system daemon.
* *Local configuration copy.* - You can override the settings using local configuration file **library/config.local.php**.

## 💚 Contributing / Contributors

Please read [CONTRIBUTING.md](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details, and the process for 
submitting pull requests to us. But we are always ready to renew your pull-request for compliance with 
these requirements. Just send it.

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
  <summary>Bitcoin</summary>
  bc1qselchy0nnh7xl99glfffedqp7p9gpvatdr9dz9
</details>

<details>
  <summary>ЮMoney</summary>
  4100118022415720
</details>

## 📦 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags). 

## 📖 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/torrentpier/torrentpier/blob/master/LICENSE) file for details.
