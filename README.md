TorrentPier II
======================

TorrentPier II - движок торрент-трекера, написанный на php.

## Установка

Распаковываем на сервер содержимое папки upload.
Заходим в phpmyadmin, открываем или создаем новую базу, потом импортируем дамп (install/sql/mysql.sql)
Правим файл конфигурации config.php (изменяем данные входа в БД, остальное по усмотрению)

* favicon.ico (меняем на свою иконку, если есть)
* robots.txt (меняем адреса в строках Host: и Sitemap: на свои адреса)

## Права доступа на папки и файлы

Устанавливаем права доступа на данные папки 777, на файлы внутри этих папок (кроме .htaccess) 666:
- ajax/html
- cache
- cache/filecache
- files
- files/thumbs
- images
- images/avatars
- images/captcha
- images/ranks
- images/smiles
- log
- triggers

## Необходимые значения в php.ini

    mbstring.internal_encoding = UTF-8
    magic_quotes_gpc = Off

## Необходимые модули для php

    php5-tidy

## Необходимый запуск cron.php

Подробнее в теме http://torrentpier.me/threads/Отвязка-запуск-крона.52/

## Часто задаваемые вопросы

http://torrentpier.me/threads/faq-для-новичков.260/

## Где задать вопрос

http://torrentpier.me/forums/Основные-вопросы-по-torrentpier-ii.10/
