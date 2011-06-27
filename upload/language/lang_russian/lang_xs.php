<?php

$lang['EXTREME_STYLES'] = 'Стили';
$lang['XS_TITLE'] = 'Мод &laquo; Extreme Styles &raquo;';

$lang['XS_FILE'] = 'Файл';
$lang['XS_TEMPLATE'] = 'Шаблон';
$lang['XS_ID'] = 'ID';
$lang['XS_STYLE'] = 'Стиль';
$lang['XS_STYLES'] = 'Стили';
$lang['XS_USERS'] = 'Пользователи';
$lang['XS_OPTIONS'] = 'Опции';
$lang['XS_COMMENT'] = 'Комментарии';
$lang['XS_UPLOAD_TIME'] = 'Время загрузки';
$lang['XS_SELECT'] = 'Выбрать';

$lang['XS_CLICK_HERE_LC'] = 'Нажмите здесь';

/*
* navigation
*/
$lang['XS_CONFIG_SHOWNAV'] = array(
	'Конфигурация',
	'Управление кешем',
);

/*
* frame_top.tpl
*/
$lang['XS_MENU_LC'] = 'Меню мода по управлению стилями';
$lang['XS_SUPPORT_FORUM_LC'] = 'Форум поддержки';
$lang['XS_DOWNLOAD_STYLES_LC'] = 'Скачать стили';
$lang['XS_INSTALL_STYLES_LC'] = 'Установить стили';

/*
* index.tpl
*/

$lang['XS_MAIN_COMMENT3'] = 'Все функции управления стилями заменены модулем «<b>eXtreme Styles</b>».<br /><br /><a href="{URL}">Открыть меню «eXtreme Styles»</a>';
$lang['XS_MAIN_TITLE'] = 'Навигационное меню «<b>eXtreme Styles</b>»';
$lang['XS_MENU'] = 'Меню «<b>eXtreme Styles</b>»';

$lang['XS_CONFIGURATION'] = 'Конфигурация';
$lang['XS_CONFIGURATION_EXPLAIN'] = 'Эта функция позволяет вам управлять стилями.';
$lang['XS_MANAGE_CACHE'] = 'Управление кешем';
$lang['XS_MANAGE_CACHE_EXPLAIN'] = 'This feature allows you to manage cached files.';
$lang['XS_SET_CONFIGURATION_LC'] = 'Выбрать конфигурацию';
$lang['XS_SET_DEFAULT_STYLE_LC'] = 'Выбрать стандартный стиль';
$lang['XS_MANAGE_CACHE_LC'] = 'Управление кешем';

/*
* config.tpl
*/

$lang['XS_CONFIG_UPDATED'] = 'Конфигурация обновлена';
$lang['XS_CONFIG_UPDATED_EXPLAIN'] = 'Здесь вы можете изменить конфигурацию и навигационное меню «<b>eXtreme Styles</b>».';
$lang['XS_CONFIG_WARNING'] = 'Внимание: не удаётся записать кэш.';
$lang['XS_CONFIG_WARNING_EXPLAIN'] = 'Каталог кэша защищён от записи. «eXtreme Styles» может пытаться устранить эту проблему.<br /><a href="{URL}">Щёлкните в этом месте</a>, чтобы попытаться изменить режим доступа к каталогу кэша.<br /><br />Если кэш не работает на вашем сервере, то не беспокойтесь - «eXtreme Styles»<br />всё равно увеличит скорость работы форума во много раз даже без кэша.';

$lang['XS_CONFIG_MAINTITLE'] = 'Настройка «<b>eXtreme Styles</b>»';
$lang['XS_CONFIG_SUBTITLE'] = 'Если вы не понимаете, для чего предназначены некоторые переменный, то лучше не меняйте их.';
$lang['XS_CONFIG_TITLE'] = 'Настройка «<b>eXtreme Styles</b>» v{VERSION}';
$lang['XS_CONFIG_CACHE'] = 'Настройка кэширования';

$lang['XS_CONFIG_TPL_COMMENTS'] = 'Добавлять имена файлов tpl в HTML';
$lang['XS_CONFIG_TPL_COMMENTS_EXPLAIN'] = 'При включении этого параметра в код HTML добавляются комментарии, которые позволяют разработчикам стиля видеть, какой файл *.tpl отображён.';

$lang['XS_CONFIG_USE_CACHE'] = 'Включить кэширование';
$lang['XS_CONFIG_USE_CACHE_EXPLAIN'] = 'Кэш сохраняется на диске и ускоряет работу шаблонов, поскольку отпадает необходимость компилировать шаблон каждый раз при отображении.';

$lang['XS_CONFIG_AUTO_COMPILE'] = 'Автоматически сохранять кэш';
$lang['XS_CONFIG_AUTO_COMPILE_EXPLAIN'] = 'Включение или отключение автоматической компиляции и сохранения на диск кэша шаблонов, которые ещё не кэшированы.';

$lang['XS_CONFIG_AUTO_RECOMPILE'] = 'Автоматически перекомпилирвоать кэш';
$lang['XS_CONFIG_AUTO_RECOMPILE_EXPLAIN'] = 'Автоматическая повторная компиляция шаблонов при изменениях.';

$lang['XS_CONFIG_PHP'] = 'Расширение имён файлов кэш';
$lang['XS_CONFIG_PHP_EXPLAIN'] = 'Это расширение кэшированных файлов. Файлы сохранены в формате php, так что расширение по умолчанию - php. Не включайте точку.';

$lang['XS_CONFIG_BACK'] = '<a href="{URL}">Вернуться на страницу конфигурации</a>.';
$lang['XS_CONFIG_SQL_ERROR'] = 'Не удалось обновить общую конфигурацию для {VAR}';

// Debug info
$lang['XS_DEBUG_HEADER'] = 'Отладочная информация';
$lang['XS_DEBUG_EXPLAIN'] = 'Это отладочная информация. Используется для нахождения и устранения проблем при конфигурации кэша.';
$lang['XS_DEBUG_VARS'] = 'Переменные шаблона';
$lang['XS_DEBUG_TPL_NAME'] = 'Имя файла шаблона:';
$lang['XS_DEBUG_CACHE_FILENAME'] = 'Имя файла кэша:';
$lang['XS_DEBUG_DATA'] = 'Отладочные данные:';

$lang['XS_CHECK_HDR'] = 'Проверка кэша для %s';
$lang['XS_CHECK_FILENAME'] = 'Ошибка: недопустимое имя файла';
$lang['XS_CHECK_OPENFILE1'] = 'Ошибка: не удаётся открыть файл "%s". Будет попытка создания каталогов...';
$lang['XS_CHECK_OPENFILE2'] = 'Ошибка: не удаётся повторно открыть файл "%s". Отказ...';
$lang['XS_CHECK_NODIR'] = 'Проверка "%s" - нет такого каталога.';
$lang['XS_CHECK_NODIR2'] = 'Ошибка: не удаётся создать каталог "%s". Проверьте права доступа.';
$lang['XS_CHECK_CREATEDDIR'] = 'Создан каталог "%s"';
$lang['XS_CHECK_DIR'] = 'Проверка "%s" - каталог существует.';
$lang['XS_CHECK_OK'] = 'Файл "%s" открыт для записи. Внешне всё в порядке.';
$lang['XS_ERROR_DEMO_EDIT'] = 'вы не можете редактировать файл в демонстрационном режиме';
$lang['XS_ERROR_NOT_INSTALLED'] = 'Модуль «eXtreme Styles» не установлен. Вы забыли загрузить файл includes/template.php';

/*
* chmod
*/

$lang['XS_CHMOD'] = 'CHMOD';
$lang['XS_CHMOD_RETURN'] = '<br /><br /><a href="{URL}">Вернуться на страницу конфигурации</a>.';
$lang['XS_CHMOD_MESSAGE1'] = 'Конфигурация изменена.';
$lang['XS_CHMOD_ERROR1'] = 'Не удаётся изменить режим доступа в каталоге кэша';

/*
* cache management
*/

$lang['XS_MANAGE_CACHE_EXPLAIN2'] = 'C помощью этой страницы вы можете компилировать или удалять кэшируемые файлы для стилей.';
$lang['XS_CLEAR_ALL_LC'] = 'Очистить все';
$lang['XS_COMPILE_ALL_LC'] = 'Компилировать все';
$lang['XS_CLEAR_CACHE_LC'] = 'Очистить кэш';
$lang['XS_COMPILE_CACHE_LC'] = 'Компилировать кэш';
$lang['XS_CACHE_CONFIRM'] = 'Если у вас установлено много стилей, то эта процедура может вызвать большую нагрузку на сервер. Продолжить?';

$lang['XS_CACHE_NOWRITE'] = 'Ошибка: нет доступа в каталог кэша';
$lang['XS_CACHE_LOG_DELETED'] = 'Удалён файл "{FILE}"';
$lang['XS_CACHE_LOG_NODELETE'] = 'Ошибка: не удаётся удалить файл "{FILE}"';
$lang['XS_CACHE_LOG_NOTHING'] = 'Нет файлов шаблонов для удаления {TPL}';
$lang['XS_CACHE_LOG_NOTHING2'] = 'В каталоге кэша нет файлов для удаления';
$lang['XS_CACHE_LOG_COUNT'] = 'Удалено {NUM} файлов';
$lang['XS_CACHE_LOG_COUNT2'] = 'Ошибка при удалении {NUM} файлов';
$lang['XS_CACHE_LOG_COMPILED'] = 'Скомпилировано {NUM} файлов';
$lang['XS_CACHE_LOG_ERRORS'] = 'Ошибки: {NUM}';
$lang['XS_CACHE_LOG_NOACCESS'] = 'Ошибка: нет доступа в каталог "{DIR}"';
$lang['XS_CACHE_LOG_COMPILED2'] = 'Скомпилирован файл "{FILE}"';
$lang['XS_CACHE_LOG_NOCOMPILE'] = 'Ошибка компиляции файла "{FILE}"';

/*
* style configuration
*/
$lang['TEMPLATE_CONFIG'] = 'Конфигурация шаблона';
$lang['XS_STYLE_CONFIGURATION'] = 'Конфигурация шаблона';