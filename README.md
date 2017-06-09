<p align="center"><img src="https://torrentpier.me/forum/styles/default/xenforo/bull-logo.svg" width="400px" /></p>
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
* PHP: 5.5 / 5.6 / 7.0 / 7.1
* PHP Extensions: bcmath, intl, tidy (optional)

## Installation

For installation you need to follow a few simple steps:

1. Распаковываем на сервер содержимое скачанной вами папки
1. Создаем базу данных, в которую при помощи phpmyadmin (или любого другого удобного инструмента) импортируем дамп, расположенный в папке **install/sql/mysql.sql**
1. Правим файл конфигурации **library/config.php**, загруженный на сервер:
> ***'db' => array('localhost', 'tp_216', 'user', 'pass', $charset, $pconnect)***
В данной строке изменяем данные входа в базу данных
***$domain_name = 'torrentpier.me';***
В данной строке указываем ваше доменное имя. Остальные правки в файле вносятся по усмотрению, исходя из необходимости из внесения (ориентируйтесь на описания, указанные у полей).

1. Редактируем указанные файлы:
 + **favicon.png** (меняем на свою иконку, если есть)  
 + **robots.txt** (меняем адреса в строках **Host** и **Sitemap** на свои)
 + **opensearch_desc.xml** (меняем описание и адрес на свои)
 + **opensearch_desc_bt.xml** (меняем описание и адрес на свои)

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

## Рекомендуемый способ запуска cron.php

Для значительного ускорения работы трекера может потребоваться отвязка встроенного форумного крона. С более подробной информацией об отвязке крона, вы можете ознакомиться в данной теме https://torrentpier.me/threads/52/ на нашем форуме поддержки.

## Local configuration

Начиная с ревизии 599 была добавлена поддерка автоматического подключения файла config.local.php, при создании его вами. В данном файле вы можете переопределять настройки файла config.php для конкретного сервера, на котором запущен трекер или в целом менять стандартные значения файла config.php, для более простого обновления файлов движка в дальнейшем.

## Установка Ocelot

В движок встроена по-умолчанию поддержка альтернативного компилируемого анонсера - Ocelot. Настройка производится в файле **library/config.php**, сам анонсер находится в репозитории https://github.com/torrentpier/ocelot

Инструкция по сборке приведена на нашем форуме: https://torrentpier.me/threads/sborka-ocelot-pod-debian-7-1.26078/
Для работы анонсера требуется замена двух таблиц в базе данных - дамп в файле: **install/sql/ocelot.sql**

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
