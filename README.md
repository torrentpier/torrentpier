<p align="center"><a href="https://torrentpier.com"><img src="https://torrentpier.com/styles/default/xenforo/bull-logo.svg" width="400px" alt="TorrentPier" /></a></p>

<p align="center">
  Bull-powered BitTorrent tracker engine
  <br/>
</p>

<p align="center">
  <a href="https://github.com/torrentpier/torrentpier/blob/master/LICENSE"><img src="https://img.shields.io/github/license/torrentpier/torrentpier" alt="License"></a>
  <a href="https://packagist.org/packages/torrentpier/torrentpier"><img src="https://img.shields.io/packagist/stars/torrentpier/torrentpier" alt="Stars Packagist"></a>
  <a href="https://crowdin.com/project/torrentpier"><img src="https://badges.crowdin.net/torrentpier/localized.svg" alt="Crowdin"></a>
  <a href="https://nightly.link/torrentpier/torrentpier/workflows/ci/master/TorrentPier"><img src="https://img.shields.io/badge/Nightly%20release-gray?logo=hackthebox&logoColor=fff" alt="TorrentPier nightly"></a>
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

TorrentPier is currently in active development. The goal is to remove all legacy code and rewrite the existing code to 
modern specifications. If you want delve deep into the code, check our [issues](https://github.com/torrentpier/torrentpier/issues) 
and go from there. The documentation will be translated to english in the near future, currently russian is the main language of it.

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
* Multilingual support (Russian and English is currently fully supported, with others in the future)
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
* MySQL 5.5.3 or above / MariaDB 10.0 or above / Percona
* PHP: 8.1 / 8.2 / 8.3 / 8.4
* PHP Extensions: mbstring, gd, bcmath, intl, tidy (optional), xml, xmlwriter
* Crontab (Recommended)

## 💾 Installation

For the installation, select one of the installation variants below:

### Quick (Clean install) 🚀

Check out our [autoinstall](https://github.com/torrentpier/autoinstall) repository with detailed instructions.

> [!IMPORTANT]
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
5. Create a database and import the dump located at `install/sql/mysql.sql`
6. Edit database configuration settings in the environment (`.env.example`), after, rename to `.env`
7. Provide write permissions to the specified folders:
   * `data/avatars`, `data/uploads`, `data/uploads/thumbs`
   * `internal_data/atom`, `internal_data/cache`, `internal_data/log`, `internal_data/triggers`
   * `sitemap`
8. Voila! ✨

> [!IMPORTANT]
> The specific settings depend on the server you are using, but in general we recommend chmod **0755** for folders, and chmod **0644** for the files in them.

### Additional steps 👣

1. Edit these files:
   * `favicon.png` (change to your own)
   * `robots.txt` (change the addresses in lines `Host` and `Sitemap` to your own)
2. Log in to the forum using the **admin/admin** login/password, and finish setting up via admin panel. Don't forget to change your password!

## 🔐 Security vulnerabilities

If you discover a security vulnerability within TorrentPier, please follow our [security policy](https://github.com/torrentpier/torrentpier/security/policy), so we can address it promptly.

## 📌 Our recommendations

* *It's recommended to run `cron.php`.* - For significant tracker speed increase it ay be required to replace the built-in cron.php in operating system daemon.
* *Local configuration copy.* - You can override the settings using the local configuration file `library/config.local.php`.

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
