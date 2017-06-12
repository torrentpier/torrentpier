<p align="center"><a href="https://torrentpier.me/"><img src="https://torrentpier.me/forum/styles/default/xenforo/bull-logo.svg" width="400px" /></a></p>
<p align="center">
    <a href="http://torrentpier.herokuapp.com/"><img src="http://torrentpier.herokuapp.com/badge.svg" alt="Slack"></a>
    <a href="https://crowdin.com/project/torrentpier"><img src="https://d322cqt584bo4o.cloudfront.net/torrentpier/localized.svg" alt="Crowdin"></a>
    <a href="https://scrutinizer-ci.com/g/torrentpier/torrentpier/"><img src="https://img.shields.io/scrutinizer/g/torrentpier/torrentpier.svg" alt="Scrutinizer"></a>
    <a href="https://www.codacy.com/app/Exile37/torrentpier"><img src="https://img.shields.io/codacy/grade/8b79a63a6d464b81bf0a39923f42bdf5/master.svg" alt="Codacy"></a>
    <br />
    <a href="https://insight.sensiolabs.com/projects/1a5d5098-e0b0-45c2-816a-020dfd50acaf"><img src="https://img.shields.io/sensiolabs/i/1a5d5098-e0b0-45c2-816a-020dfd50acaf.svg" alt="SensioLabs Insight"></a>
    <a href="https://travis-ci.org/torrentpier/torrentpier"><img src="https://img.shields.io/travis/torrentpier/torrentpier/master.svg" alt="Travis"></a>
    <a href="https://circleci.com/gh/torrentpier/torrentpier"><img src="https://img.shields.io/circleci/project/github/torrentpier/torrentpier/master.svg" alt="CircleCI"></a>
    <a href="https://codecov.io/gh/torrentpier/torrentpier"><img src="https://img.shields.io/codecov/c/github/torrentpier/torrentpier/master.svg" alt="Codecov"></a>
</p>

## About TorrentPier

TorrentPier — bull-powered BitTorrent tracker engine, written in php. High speed, simple modification, high load 
architecture, built-in support for alternative compiled announcers (Ocelot, XBT). In addition we have very helpful 
[official support forum](https://torrentpier.me/forum), where among other things it is possible to test the live 
demo, get any support and download modifications for engine.

## Current status

TorrentPier is currently in active development. The goal is to remove all legacy code and rewrite existing to 
modern standards. If you want to go deep on the code, check our [issues](https://github.com/torrentpier/torrentpier/issues) 
and go from there. The documentation will be translated into english in the near future, currently russian is the main language of it.

## Requirements

* Apache / nginx
* MySQL / MariaDB / Percona
* PHP: 5.6 / 7.0 / 7.1
* PHP Extensions: bcmath, intl, tidy (optional)

## Installation

For installation you need to follow a few simple steps:

1. Unpack to the server the contents of the downloaded folder
1. Install [Composer](https://getcomposer.org/) and run `composer install` on the downloaded directory
1. Create database and import dump located at **install/sql/mysql.sql**
1. Edit database configuration settings in the configuration file or a local copy (see below)
1. Edit domain name in the configuration file or a local copy (see below)
1. Edit this files:
   1. **favicon.png** (change on your own)
   1. **robots.txt** (change the addresses in lines **Host** and **Sitemap** on your own)
   1. **opensearch_desc.xml** (change the description and address on your own)
   1. **opensearch_desc_bt.xml** (change the description and address on your own)
1. Log in to the forum with admin/admin login/password and finish setting up via admin panel

## Access rights on folders and files

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

## The recommended way to run cron.php

For significant tracker speed increase may be required to replace built-in cron.php by operating system daemon. For more 
information about that you can read [this thread](https://torrentpier.me/forum/threads/52/) on our support forum.

## Local configuration copy

You can override the settings using one of these methods: configuration file **library/config.local.php** and the environment
file **.env**. Both files are created by copying the appropriate .example templates without extension. Local configuration files 
should not be available for reading to anyone by setting up access rights for your web server.

## Ocelot installation

We have built-in support for alternate compiled announcer — Ocelot. The configuration is in the file **library/config.php**,
the announcer is in the repository [torrentpier/ocelot](https://github.com/torrentpier/ocelot). You can read assembly instructions
on his repository or in [this thread](https://torrentpier.me/forum/threads/26078/) on our support forum.

## Official documentation

Documentation for TorrentPier can be found on the [TorrentPier docs website](https://docs.torrentpier.me).

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for 
submitting pull requests to us. But we are always ready to renew your pull-request for compliance with 
these requirements. Just send it.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/torrentpier/torrentpier/tags). 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
