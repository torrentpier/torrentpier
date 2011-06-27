//////////////////////////////////////
//                                  //
// TorrentPier SVN based for R775   //
// Time/Date: 13:00 14.01.2009      //
// Site: http://torrentpier.info/   //
//                                  //
// Project owners:                  //
//   roadtrain4eg, PandoraBox2007   //
//                                  //
// Project members:                 //
//   segalws, givemex, bulanovk,    //
//   johnny.concent                 //
//                                  //
// Code modification:               //
//   Pandora, RoadTrain             //
//                                  //
//////////////////////////////////////

********************
**     Папки      **
********************
  +[DIR] other
  |- backup            -    Очень удобная программа для создания бэкапа MySQL сервера, а также восстановить (http://sypex.net/)
  |- test              -    скрипты для теста работы MySQL, сервера, апатча.
  |- bench             -    хз... что за скрипт но после него включается дебаг сайта
  |- bt_simple         -    оригинальная версия анонсера не тестил не знаю юзайте лучше bt
  |- mods              -    папка с модами
  |- converters        -    конвертеры с других движков

  +[DIR] forum
  |- bt                -    анонсер без него нельзя передавать информацию через трекер
  |- *                 -    форум трекера TorrentPier

  +[DIR] Install
  |- sql               -    Дамп БД MySQL который нада залить
  |- server            -    Настройка сервера
  |- sphinx            -    Файлы конфигурации для (SphinX Search Engine)

  +[DIR] announce      -    альтернативные аннонсеры

  +[DIR] update        -    скрипты для обновления

********************
**   Установка    **
********************
Распаковываем на сервер
 [*] содержимое папки forum
 [*] файлы favicon.ico (меняем на свою иконку если есть), robots.txt(Допуск или запрет ботам поисковиков к серверу, блокирует не все)
 [*] зайти в phpmyadmin открыть или создать новую базу, потом Импортировать дамп (папку MySQL_dump не заливать на сервер)!
 [*] Отредактировать config.php: изменить данные входа в БД остальное по усмотрению.
   - Не забываем это настроить в config.php    

// Cookie
$bb_cfg['cookie_domain'] = '.mysite.ru';     # '.yourdomain.com'
$bb_cfg['cookie_path']   = '/';              # '/forum/'

$bb_cfg['script_path'] = '/';

$bb_cfg['sitename'] = 'TORRENTS.RU (see $bb_cfg[\'sitename\'] in config.php)'; # Вписать отображаемое название сайта

   - желательно (но не обязательно) также в строке:
$bb_cfg['server_name'] = $_SERVER['SERVER_NAME'];
     заменить $_SERVER['SERVER_NAME'] на '.mysite.ru' (подставить своё доменное имя)
   - в строке:
$bb_cfg['server_port'] = $_SERVER['SERVER_PORT'];
     заменить $_SERVER['SERVER_PORT'] на 80

************************************
** Права доступа на папки и файлы **
************************************

Устанавливаем права доступа на данные папки 777, на файлы внутри этих папок (кроме .htaccess) 666:

- ajax
- ajax/html 
- images
- images/avatars
- images/avatars/gallery
- images/flags
- images/logo
- images/ranks
- images/smiles
- cache
- cache/filecache
- cache/filecache/bb_cache
- cache/filecache/datastore
- cache/filecache/session_cache
- cache/filecache/tr_cache
- files
- files/thumbs
- log
- pictures
- triggers

************************************
** Необходимые значения в php.ini **
************************************
mbstring.internal_encoding = UTF-8
magic_quotes_gpc = Off
