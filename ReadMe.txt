********************
**   Установка    **
********************

Распаковываем на сервер содержимое папки forum.
Заходим в phpmyadmin открываем или создаём новую базу, потом импортировать дамп (install/sql/mysql.sql)
Правим файл конфигурации config.php (изменить данные входа в БД остальное по усмотрению)

* Файлы favicon.ico (меняем на свою иконку если есть), robots.txt(допуск или запрет ботам поисковиков к серверу, блокирует не все)

************************************
** Права доступа на папки и файлы **
************************************

Устанавливаем права доступа на данные папки 777, на файлы внутри этих папок (кроме .htaccess) 666:
- ajax
- ajax/html 
- images
- images/avatars
- images/avatars/gallery
- images/captcha
- images/logo
- images/ranks
- images/smiles
- cache
- cache/filecache
- cache/filecache/bb_cache
- cache/filecache/datastore
- cache/filecache/session_cache
- files
- files/thumbs
- log
- pictures
- triggers

************************************
** Необходимые значения в php.ini **
************************************

magic_quotes_gpc = Off
extension=php_mbstring.dll
extension=php_pdo.dll
extension=php_sqlite.dll
extension=php_tidy.dll

************************************
** Необходимый запуск cron.php    **
************************************

Подробнее в теме (ссылка будет чуть позднее)