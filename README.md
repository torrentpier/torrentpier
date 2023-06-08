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

<p align="center">🚧 WIP: TorrentPier Cattle (2.4) 🚧</p>

## 🐂 About TorrentPier

TorrentPier — bull-powered BitTorrent tracker engine, written in php. High speed, simple modification, high load 
architecture, built-in support for alternative compiled announcers (Ocelot, XBT). In addition, we have very helpful 
[official support forum](https://torrentpier.com), where among other things it is possible to test the live 
demo, get any support and download modifications for engine.

## 🌈 Current status

TorrentPier is currently in active development. The goal is to remove all legacy code and rewrite existing to 
modern standards. If you want to go deep on the code, check our [issues](https://github.com/torrentpier/torrentpier/issues) 
and go from there. The documentation will be translated into english in the near future, currently russian is the main language of it.

## 🔧 Requirements

* Apache / nginx
* MySQL 5.5.3 or above / MariaDB 10.0 or above / Percona
* PHP: 7.4 / 8.0 / 8.1 / 8.2
* PHP Extensions: bcmath, intl, tidy (optional), xml, xmlwriter

## 💾 Installation

For installation, you need to follow a few simple steps:

1. Install [Composer](https://getcomposer.org/)
2. Run `composer create-project torrentpier/torrentpier`
3. After run `composer install` on the project directory
4. Create database and import dump located at **install/sql/mysql.sql**
5. Edit database configuration settings in the configuration file or a local copy (see below)
6. Edit domain name in the configuration file or a local copy (see below)
7. Edit domain ssl setting in the configuration file or a local copy (see below)
8. Edit this files:
   1. **favicon.png** (change on your own)
   2. **robots.txt** (change the addresses in lines **Host** and **Sitemap** on your own)
   3. **opensearch_desc.xml** (change the description and address on your own)
   4. **opensearch_desc_bt.xml** (change the description and address on your own)
9. Log in to the forum with admin/admin login/password and finish setting up via admin panel

## 🔑 Access rights on folders and files

You must provide write permissions to the specified folders:
* `data/avatars`
* `data/torrent_files`
* `internal_data/ajax_html`
* `internal_data/atom`
* `internal_data/cache`
* `internal_data/log`
* `internal_data/triggers`
* `sitemap`

The specific settings depend on the server you are using, but in general case we recommend chmod 0755 for folders, 
and chmod 0644 for files in them. If you are not sure, leave it as is.

## 📌 Our recommendations

* ⛓ *The recommended way to run cron.php.* - For significant tracker speed increase may be required to replace built-in cron.php by operating system daemon. For more
  information about that you can read [this thread](https://torrentpier.com/threads/52/) on our support forum.
* ⛓ *Local configuration copy.* - You can override the settings using one of these methods: configuration file **library/config.local.php** and the environment
  file **.env**. Both files are created by copying the appropriate .example templates without extension. Local configuration files
  should not be available for reading to anyone by setting up access rights for your web server.
* ⛓ *Ocelot installation.* - We have built-in support for alternate compiled announcer — Ocelot. The configuration is in the file **library/config.php**,
  the announcer is in the repository [torrentpier/ocelot](https://github.com/torrentpier/ocelot). You can read assembly instructions
  on his repository or in [this thread](https://torrentpier.com/threads/26078/) on our support forum.

## 📝 Official documentation

Documentation for TorrentPier can be found on the [TorrentPier docs website](https://docs.torrentpier.com).

## 💚 Contributing / Contributors

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for 
submitting pull requests to us. But we are always ready to renew your pull-request for compliance with 
these requirements. Just send it.

<a href="https://github.com/torrentpier/torrentpier/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=torrentpier/torrentpier" />
</a>

Made with [contrib.rocks](https://contrib.rocks).

## 💞 Sponsoring

Support this project by becoming a sponsor or a backer. 

[![OpenCollective sponsors](https://opencollective.com/torrentpier/sponsors/badge.svg)](https://opencollective.com/torrentpier)
[![OpenCollective backers](https://opencollective.com/torrentpier/backers/badge.svg)](https://opencollective.com/torrentpier)

## 📦 Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags). 

## 📖 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/torrentpier/torrentpier/blob/master/LICENSE) file for details.
