TorrentPier
======================
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/8b79a63a6d464b81bf0a39923f42bdf5)](https://www.codacy.com/app/Exile37/torrentpier?utm_source=github.com&utm_medium=referral&utm_content=torrentpier/torrentpier&utm_campaign=badger)
[![Crowdin](https://d322cqt584bo4o.cloudfront.net/torrentpier/localized.svg)](https://crowdin.com/project/torrentpier) [![Join the chat at https://gitter.im/torrentpier/torrentpier](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/torrentpier/torrentpier?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Current status

TorrentPier is currently in active development. The goal is to remove all legacy code and rewrite existing to modern standards. If you want to go deep on the code, check out https://github.com/torrentpier/torrentpier/issues and go from there. The documentation will be translated into english in the near future, currently russian is the main language of project.

## About

TorrentPier - движок торрент-трекера, написанный на php. Высокая скорость работы, простота модификации, устойчивость к высоким нагрузкам, в том числе и поддержка альтернативных анонсеров (например, Ocelot). Помимо этого, крайне развитый официальный форум поддержки, где помимо прочего можно испытать движок в работе на демо-версии, не устанавливая его, а также получить любую другую интересующую вас информацию и скачать моды.

## Installation

Для установки вам необходимо выполнить несколько простых шагов:

1. Распаковываем на сервер содержимое скачанной вами папки

2. Создаем базу данных, в которую при помощи phpmyadmin (или любого другого удобного инструмента) импортируем дамп, расположенный в папке **install/sql/mysql.sql**
3. Правим файл конфигурации **library/config.php**, загруженный на сервер:
> ***'db1' => array('localhost', 'tp_216', 'user', 'pass', $charset, $pconnect)***    
В данной строке изменяем данные входа в базу данных    
***$domain_name = 'torrentpier.me';***    
В данной строке указываем ваше доменное имя. Остальные правки в файле вносятся по усмотрению, исходя из необходимости из внесения (ориентируйтесь на описания, указанные у полей). Также вы можете создать файл **library/config.local.php** в который продублировать все изменяемые значения, чтобы была возможность обновления основного файла конфигурации через git. Значения из этого файла будут иметь приоритет перед всеми остальными.

4. Редактируем указанные файлы:
 + **favicon.ico** (меняем на свою иконку, если есть)  
 + **robots.txt** (меняем адреса в строках **Host** и **Sitemap** на свои)
 + **opensearch_desc.xml** (меняем описание и адрес на свои)
 + **opensearch_desc_bt.xml** (меняем описание и адрес на свои)

## Права доступа на папки и файлы

Исходя из настроек вашего сервера, устанавливаем рекомендуемые права доступа (chmod) на указанные папки **777**, а на файлы внутри этих папок (кроме файлов **.htaccess** и **.keep**) **666**:
- data/avatars
- data/torrent_files
- internal_data/ajax_html
- internal_data/atom
- internal_data/cache
- internal_data/log
- internal_data/sitemap
- internal_data/triggers

## Необходимая версия php

Минимально поддерживаемой версией в настоящий момент является 7.0.8. Существует поддержка вплоть до версии 7.1.

## Необходимые настройки php

    mbstring.internal_encoding = UTF-8
    magic_quotes_gpc = Off
Внести данные настройки необходимо в файл **php.ini**. Их вам может установить ваш хостер по запросу, если у вас возникают какие-либо проблемы с их самостоятельной установкой. Впрочем, эти настройки могут быть установлены на сервере по-умолчанию, поэтому их внесение требуется исключительно по необходимости.

## Необходимые модули php

    php5-tidy
Начиная с версии 2.0.9 (ревизия 592 в старой нумерации) данный модуль не является обязательным, но его установка крайне рекомендуется для повышения качества обработки html-кода тем и сообщений пользователей. 

## Рекомендуемый способ запуска cron.php

Для значительного ускорения работы трекера может потребоваться отвязка встроенного форумного крона. С более подробной информацией об отвязке крона, вы можете ознакомиться в данной теме https://torrentpier.me/threads/52/ на нашем форуме поддержки.

## Локальный файл конфигурации

Начиная с ревизии 599 была добавлена поддерка автоматического подключения файла config.local.php, при создании его вами. В данном файле вы можете переопределять настройки файла config.php для конкретного сервера, на котором запущен трекер или в целом менять стандартные значения файла config.php, для более простого обновления файлов движка в дальнейшем.

## Установка Ocelot

В движок встроена по-умолчанию поддержка альтернативного компилируемого анонсера - Ocelot. Настройка производится в файле **library/config.php**, сам анонсер находится в репозитории https://github.com/torrentpier/ocelot

Инструкция по сборке приведена на нашем форуме: https://torrentpier.me/threads/sborka-ocelot-pod-debian-7-1.26078/
Для работы анонсера требуется замена двух таблиц в базе данных - дамп в файле: **install/sql/ocelot.sql**

## Папка install

В корне движка присутствует папка **install**, в которой находятся служебные файлы, необходимые для его установки (дамп базы, примеры конфигов) и обновления (дамперы, скрипты конвертации). Доступ к данной папке по-умолчанию закрыт, но если ее присутствие вам мешает - вы можете ее удалить. На файлы **README.md** и **CONTRIBUTORS.md** это также распространяется.

## Полезные ссылки

+ Наш форум https://torrentpier.me/
+ Центр загрузки https://get.torrentpier.me/
+ Часто задаваемые вопросы https://faq.torrentpier.me/
+ Где задать вопрос https://torrentpier.me/forums/10/
