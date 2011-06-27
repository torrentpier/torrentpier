<?php
/***************************************************************************
 *                            lang_admin.php [Russian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// Translation performed by Alexey V. Borzov (borz_off)
// borz_off@cs.msu.su
//


$lang['GENERAL'] = 'Общие настройки';
$lang['USERS'] = 'Пользователи';
$lang['GROUPS'] = 'Группы';
$lang['FORUMS'] = 'Форумы';

$lang['CONFIGURATION'] = 'Конфигурация';
$lang['PERMISSIONS'] = 'Права доступа';
$lang['MANAGE'] = 'Управление';
$lang['DISALLOW'] = 'Запрещённые имена';
$lang['PRUNE'] = 'Чистка';
$lang['MASS_EMAIL'] = 'Массовая рассылка почты';
$lang['RANKS'] = 'Звания';
$lang['SMILIES'] = 'Смайлики';
$lang['BAN_MANAGEMENT'] = 'Чёрные списки (Ban)';
$lang['WORD_CENSOR'] = 'Автоцензор';
$lang['EXPORT'] = 'Экспорт';
$lang['CREATE_NEW'] = 'Создать';
$lang['ADD_NEW'] = 'Добавить';
$lang['FLAGS'] = 'Флаги';
$lang['REBUILD_SEARCH_INDEX'] = 'Перечесть индексы поиска';
$lang['ACTIONS_LOG'] = 'Лог действий';
$lang['FORUM_CONFIG'] = 'Настройки форумов';
$lang['TRACKER_CONFIG'] = 'Настройки трекера';
$lang['RELEASE_TEMPLATES'] = 'Шаблоны для релизов';
$lang['ACTIONS_LOG'] = 'Отчет по действиям';
$lang['SEARCH'] = 'Поиск пользователей';

//
//Welcome page
//
$lang['IDX_BROWSER_NSP_FRAME'] = 'Простите, но ваш браузер не поддерживает фреймы.';
$lang['IDX_CLEAR_CACHE'] ='Очистить кеш:';
$lang['IDX_CLEAR_DATASTORE'] = 'Данные';
$lang['IDX_CLEAR_TEMPLATES'] = 'Шаблоны';
$lang['IDX_CLEAR_NEWNEWS'] = 'Новости';
$lang['IDX_UPDATE'] = 'Обновить:';
$lang['IDX_UPDATE_USER_LEVELS'] = 'Уровни пользователей';
$lang['IDX_SYNCHRONIZE'] = 'Синхронизировать:';
$lang['IDX_SYNCHRONIZE_TOPICS'] = 'Темы';
$lang['IDX_SYNCHRONIZE_POSTCOUNT'] = 'Количество сообщений';
//
// Welcome page END
//

//
// Index
//
$lang['ADMIN'] = 'Администрирование';
$lang['MAIN_INDEX'] = 'Список форумов';
$lang['FORUM_STATS'] = 'Статистика Форумов';
$lang['ADMIN_INDEX'] = 'Главная страница';

$lang['TP_VERSION']      = 'Версия TorrenPier';
$lang['TP_RELEASE_DATE'] = 'Дата выпуска';

$lang['CLICK_RETURN_ADMIN_INDEX'] = '%sВернуться на главную страницу администраторского раздела%s';

$lang['STATISTIC'] = 'Статистика';
$lang['VALUE'] = 'Значение';
$lang['NUMBER_POSTS'] = 'Кол-во сообщений';
$lang['POSTS_PER_DAY'] = 'Сообщений в день';
$lang['NUMBER_TOPICS'] = 'Кол-во тем';
$lang['TOPICS_PER_DAY'] = 'Тем в день';
$lang['NUMBER_USERS'] = 'Кол-во пользователей';
$lang['USERS_PER_DAY'] = 'Пользователей в день';
$lang['BOARD_STARTED'] = 'Дата запуска';
$lang['AVATAR_DIR_SIZE'] = 'Размер директории с аватарами';
$lang['DATABASE_SIZE'] = 'Объём БД';
$lang['GZIP_COMPRESSION'] ='сжатие Gzip';
$lang['NOT_AVAILABLE'] = 'Недоступно';

$lang['ON'] = 'ВКЛ'; // This is for GZip compression
$lang['OFF'] = 'ВЫКЛ';

// Clear Cache
$lang['CLEAR_CACHE'] = 'Очистить кеш';
$lang['DATASTORE'] = 'Datastore';
$lang['DATASTORE_CLEARED'] = 'Datastore очищен';
$lang['TEMPLATES'] = 'Шаблона';

// Update
$lang['UPDATE'] = 'Обновить';
$lang['USER_LEVELS'] = 'Уровни и права пользователей';
$lang['USER_LEVELS_UPDATED'] = 'Уровни и права пользователей обновлены';

// Synchronize
$lang['SYNCHRONIZE'] = 'Синхронизировать';
$lang['TOPICS'] = 'Темы';
$lang['TOPICS_DATA_SYNCHRONIZED'] = 'Темы синхронизированы';
$lang['USER_POSTS_COUNT'] = 'Количество сообщений пользователей.';
$lang['USER POSTS COUNT SYNCHRONIZED'] = 'Количество сообщений пользователей синхронизированы.';

//
// Auth pages
//
$lang['USER_SELECT'] = 'Выберите пользователя';
$lang['GROUP_SELECT'] = 'Выберите группу';
$lang['SELECT_A_FORUM'] = 'Выберите форум';
$lang['AUTH_CONTROL_USER'] = 'Права пользователей';
$lang['AUTH_CONTROL_GROUP'] = 'Права групп';
$lang['AUTH_CONTROL_FORUM'] = 'Доступ к форумам';
$lang['LOOK_UP_FORUM'] = 'Выбрать форум';

$lang['GROUP_AUTH_EXPLAIN'] = 'Здесь вы можете изменить права доступа и статус модератора для каждой группы пользователей. Не забывайте при изменении прав доступа для групп, что права доступа для отдельных пользователей могут давать пользователю возможность входа в форумы и т.п. Вы будете предупреждены в этом случае.';
$lang['USER_AUTH_EXPLAIN'] = 'Здесь вы можете изменить права доступа и статус модератора для отдельных пользователей. Не забывайте при изменении прав пользователя, что права доступа для группы могут давать пользователю возможность входа в форумы и т.п. Вы будете предупреждены в этом случае.';
$lang['FORUM_AUTH_EXPLAIN'] = 'Здесь вы можете регулировать доступ к каждому форуму. У вас будет обычный и продвинутый режим для этого, продвинутый даёт больше возможностей для контроля. Помните, что изменение прав доступа к форуму повлияет на то, какие пользователи смогут совершать в нём различные действия';

$lang['SIMPLE_MODE'] = 'Простой режим';
$lang['ADVANCED_MODE'] = 'Продвинутый режим';
$lang['MODERATOR_STATUS'] = 'Статус модератора';

$lang['ALLOWED_ACCESS'] = 'Доступ открыт';
$lang['DISALLOWED_ACCESS'] = 'Доступ закрыт';
$lang['IS_MODERATOR'] = 'Модератор';
$lang['NOT_MODERATOR'] = 'Не модератор';

$lang['CONFLICT_WARNING'] = 'Предупреждение о конфликте прав';
$lang['CONFLICT_ACCESS_USERAUTH'] = 'У пользователя (пользователей) всё ещё есть права доступа к этому форуму, связанные с членством в группе. Вам, возможно, надо изменить права доступа для групп или исключить пользователя из группы для того, чтобы полностью закрыть ему права доступа. Группы, дающие такие права, перечислены ниже.';
$lang['CONFLICT_MOD_USERAUTH'] = 'У данного пользователя всё ещё есть право модерирования этого форума, связанное с его членством в группе. Вам, возможно, надо изменить права доступа для групп или исключить пользователя из группы для того, чтобы полностью закрыть ему право модерации. Группы, дающие это право, перечислены ниже.';

$lang['CONFLICT_ACCESS_GROUPAUTH'] = 'У пользователя (пользователей) всё ещё есть права доступа к этому форуму из-за установок их личных прав. Вам, возможно, надо изменить их права для того, чтобы полностью закрыть им доступ. Пользователи, имеющие такие права, перечислены ниже.';
$lang['CONFLICT_MOD_GROUPAUTH'] = 'У пользователя (пользователей) всё ещё есть право модерирования этого форума из-за установок их личных прав. Вам, возможно, надо изменить их права для того, чтобы полностью закрыть им возможность модерирования. Пользователи, имеющие такие права, перечислены ниже.';

$lang['PUBLIC'] = 'Публичный';
$lang['PRIVATE'] = 'Приватный';
$lang['REGISTERED'] = 'Зарегистрированный';
$lang['ADMINISTRATORS'] = 'Администраторы';
$lang['HIDDEN'] = 'Спрятанный';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['FORUM_ALL'] = 'Все';
$lang['FORUM_REG'] = 'Регистр.';
$lang['FORUM_PRIVATE'] = 'Приватный';
$lang['FORUM_MOD'] = 'Модератор';
$lang['FORUM_ADMIN'] = 'Админ';

$lang['AUTH_VIEW'] = $lang['VIEW'] = 'Видеть';
$lang['AUTH_READ'] = $lang['READ'] = 'Читать';
$lang['AUTH_POST'] = $lang['POST'] = 'Создавать темы';
$lang['AUTH_REPLY'] = $lang['REPLY'] = 'Отвечать';
$lang['AUTH_EDIT'] = $lang['EDIT'] = 'Редактировать';
$lang['AUTH_DELETE'] = $lang['DELETE'] = 'Удалять';
$lang['AUTH_STICKY'] = $lang['STICKY'] = 'Прилеплять темы';
$lang['AUTH_ANNOUNCE'] = $lang['ANNOUNCE'] = 'Создавать объявления';
$lang['AUTH_VOTE'] = $lang['VOTE'] = 'Голосовать';
$lang['AUTH_POLLCREATE'] = $lang['POLLCREATE'] = 'Создавать опросы';
$lang['AUTH_ATTACHMENTS'] = $lang['AUTH_ATTACH'] = 'Прикреплять файлы';
$lang['AUTH_DOWNLOAD'] = $lang['AUTH_DOWNLOAD'] = 'Скачивать файлы';

$lang['SIMPLE_PERMISSION'] = 'Простое право доступа';

$lang['USER_LEVEL'] = 'Статус пользователя';
$lang['AUTH_USER'] = 'Пользователь';
$lang['AUTH_ADMIN'] = 'Администратор';
$lang['GROUP_MEMBERSHIPS'] = 'Членство в группах';
$lang['USERGROUP_MEMBERS'] = 'В этой группе состоят';

$lang['FORUM_AUTH_UPDATED'] = 'Права доступа к форуму изменены';
$lang['USER_AUTH_UPDATED'] = 'Права пользователя изменены';
$lang['GROUP_AUTH_UPDATED'] = 'Права группы изменены';

$lang['AUTH_UPDATED'] = 'Права доступа изменены';
$lang['CLICK_RETURN_USERAUTH'] = '%sВернуться к управлению правами пользователей%s';
$lang['CLICK_RETURN_GROUPAUTH'] = '%sВернуться к управлению правами групп%s';
$lang['CLICK_RETURN_FORUMAUTH'] = '%sВернуться к управлению доступом к форумам%s';


//
// Banning
//
$lang['BAN_CONTROL'] = 'Чёрные списки';
$lang['BAN_EXPLAIN'] = 'Здесь вы можете закрывать пользователям любой доступ к форумам. Вы можете внести в чёрный список конкретного пользователя, а также один ил несколько IP адресов или имён серверов. Этот метод не даст пользователю увидеть даже список форумов. Чтобы запретить регистрацию под другим именем, вы можете также внести в чёрный список адрес e-mail. Учтите, запрещение только e-mail адреса не закроет пользователю возможность заходить на форум и писать сообщения. Для этого вам придётся воспользоваться одним из первых двух методов.';
$lang['BAN_EXPLAIN_WARN'] = 'Учтите, что ввод диапазона IP адресов приведёт к добавлению всех адресов между первым и последним в &laquo;чёрный список&raquo;. Будут проделаны попытки уменьшить это количество вводом шаблонов, где это возможно. Если вам действительно надо ввести диапазон адресов, постарайтесь сделать его поменьше или, что ещё лучше, вводите отдельные адреса.';

$lang['SELECT_IP'] = 'Выберите IP адрес';
$lang['SELECT_EMAIL'] = 'Выберите адрес e-mail';

$lang['BAN_USERNAME'] = 'Закрытие доступа отдельным пользователям';
$lang['BAN_USERNAME_EXPLAIN'] = 'Вы можете закрыть доступ нескольким пользователям за один раз, используя подходящую для вашего компьютера и браузера комбинацию клавиатуры и мыши.';

$lang['BAN_IP'] = 'Закрыть доступ с одного или нескольких адресов IP или хостов';
$lang['IP_HOSTNAME'] = 'Адреса IP или хосты';
$lang['BAN_IP_EXPLAIN'] = 'Чтобы указать несколько разных адресов или хостов, разделите их запятыми. Чтобы указать последовательность адресов IP разделите начало и конец дефисом (-), чтобы указать шаблон используйте *';

$lang['BAN_EMAIL'] = 'Запретить e-mail адреса';
$lang['BAN_EMAIL_EXPLAIN'] = 'Чтобы запретить несколько e-mail адресов, разделите их запятыми. Чтобы указать шаблон, используйте *, например *@mail.ru';

$lang['UNBAN_USERNAME'] = 'Вновь открыть доступ пользователям';
$lang['UNBAN_USERNAME_EXPLAIN'] = 'Вы можете вновь открыть доступ нескольким пользователям за один раз, используя подходящую для вашего компьютера и браузера комбинацию клавиатуры и мыши.';

$lang['UNBAN_IP'] = 'Вновь открыть доступ с адресов IP';
$lang['UNBAN_IP_EXPLAIN'] = 'Вы можете вновь разрешить доступ с нескольких адресов IP за один раз, используя подходящую для вашего компьютера и браузера комбинацию клавиатуры и мыши.';

$lang['UNBAN_EMAIL'] = 'Вновь разрешить адреса e-mail';
$lang['UNBAN_EMAIL_EXPLAIN'] = 'Вы можете вновь разрешить несколько адресов e-mail за один раз, используя подходящую для вашего компьютера и браузера комбинацию клавиатуры и мыши.';

$lang['NO_BANNED_USERS'] = 'Чёрный список пользователей пуст';
$lang['NO_BANNED_IP'] = 'Чёрный список адресов IP пуст';
$lang['NO_BANNED_EMAIL'] = 'Чёрный список адресов e-mail пуст';

$lang['BAN_UPDATE_SUCESSFUL'] = 'Чёрный список был успешно обновлён';
$lang['CLICK_RETURN_BANADMIN'] = '%sВернуться к чёрным спискам%s';


//
// Configuration
//
$lang['GENERAL_CONFIG'] = 'Общие настройки';
$lang['CONFIG_EXPLAIN'] = 'Эта форма позволит вам изменить общие настройки форумов. Для управления пользователями и отдельными форумами используйте соответствующие ссылки слева.';

$lang['CLICK_RETURN_CONFIG'] = '%sВернуться к общим настройкам%s';

$lang['GENERAL_SETTINGS'] = 'Общие настройки форумов';
$lang['SITE_NAME'] = 'Название сайта';
$lang['SITE_DESC'] = 'Описание сайта';
$lang['BOARD_DISABLE'] = 'Отключить форумы';
$lang['BOARD_DISABLE_EXPLAIN'] = 'Форумы станут недоступными пользователям. У Администраторов останется доступ через Панель Администрирования пока форум выключен.';
$lang['ACCT_ACTIVATION'] = 'Включить активизацию учётных записей';
$lang['ACC_NONE'] = 'Нет'; // These three entries are the type of activation
$lang['ACC_USER'] = 'Пользователем';
$lang['ACC_ADMIN'] = 'Администратором';

$lang['ABILITIES_SETTINGS'] = 'Общие настройки форумов и пользователей';
$lang['MAX_POLL_OPTIONS'] = 'Макс. кол-во вариантов ответа в опросе';
$lang['FLOOD_INTERVAL'] = 'Задержка &laquo;флуда&raquo;';
$lang['FLOOD_INTERVAL_EXPLAIN'] = 'Время (в секундах), которое должно пройти между двумя сообщениями пользователя.';
$lang['BOARD_EMAIL_FORM'] = 'Рассылка e-mail сообщений через форумы';
$lang['BOARD_EMAIL_FORM_EXPLAIN'] = 'Пользователи смогут посылать друг другу e-mail через форумы';
$lang['TOPICS_PER_PAGE'] = 'Тем на страницу';
$lang['POSTS_PER_PAGE'] = 'Сообщений на страницу';
$lang['HOT_THRESHOLD'] = 'Сообщений в &laquo;популярной&raquo; теме';
$lang['DEFAULT_LANGUAGE'] = 'Язык по умолчанию';
$lang['DATE_FORMAT'] = 'Формат даты';
$lang['SYSTEM_TIMEZONE'] = 'Часовой пояс';
$lang['ENABLE_PRUNE'] = 'Включить чистку форумов';
$lang['ALLOW_BBCODE'] = 'Разрешить BBCode';
$lang['ALLOW_SMILIES'] = 'Разрешить смайлики';
$lang['SMILIES_PATH'] = 'Путь к смайликам';
$lang['SMILIES_PATH_EXPLAIN'] = 'Каталог ниже корня phpBB, например images/smilies';
$lang['ALLOW_SIG'] = 'Разрешить подписи';
$lang['MAX_SIG_LENGTH'] = 'Макс. длина подписи';
$lang['MAX_SIG_LENGTH_EXPLAIN'] = 'Максимальное кол-во символов в подписи пользователя';
$lang['ALLOW_NAME_CHANGE'] = 'Разрешить смену имени пользователя';

$lang['AVATAR_SETTINGS'] = 'Настройки аватар';
$lang['ALLOW_LOCAL'] = 'Разрешить аватар из галереи';
$lang['ALLOW_REMOTE'] = 'Разрешить удалённых аватар';
$lang['ALLOW_REMOTE_EXPLAIN'] = 'Ссылка на аватару, находящуюся на другом сайте';
$lang['ALLOW_UPLOAD'] = 'Разрешить закачку аватар';
$lang['MAX_FILESIZE'] = 'Макс. размер файла аватары';
$lang['MAX_FILESIZE_EXPLAIN'] = 'Для закачанных файлов';
$lang['MAX_AVATAR_SIZE'] = 'Макс. размер изображения';
$lang['MAX_AVATAR_SIZE_EXPLAIN'] = '(высота x ширина в пикселях)';
$lang['AVATAR_STORAGE_PATH'] = 'Путь к аватарам';
$lang['AVATAR_STORAGE_PATH_EXPLAIN'] = 'Каталог ниже корня phpBB, например images/avatars';
$lang['AVATAR_GALLERY_PATH'] = 'Путь к галерее аватар';
$lang['AVATAR_GALLERY_PATH_EXPLAIN'] = 'Каталог ниже корня phpBB для готовых картинок, например images/avatars/gallery';

$lang['EMAIL_SETTINGS'] = 'Настройки e-mail';
$lang['ADMIN_EMAIL'] = 'Адрес e-mail администратора';
$lang['EMAIL_SIG'] = 'Подпись в сообщениях e-mail';
$lang['EMAIL_SIG_EXPLAIN'] = 'Этот текст будет подставляться во все письма, рассылаемые из форумов';
$lang['USE_SMTP'] = 'Использовать сервер SMTP для отправки почты';
$lang['USE_SMTP_EXPLAIN'] = 'Отметьте, если вы хотите/вынуждены отсылать почту через сервер SMTP, а не локальную почтовую службу';
$lang['SMTP_SERVER'] = 'Адрес сервера SMTP';
$lang['SMTP_USERNAME'] = 'Имя пользователя для SMTP';
$lang['SMTP_USERNAME_EXPLAIN'] = 'Не указывайте имя пользователя если оно не требуется для работы с вашим сервером SMTP';
$lang['SMTP_PASSWORD'] = 'Пароль для SMTP';
$lang['SMTP_PASSWORD_EXPLAIN'] = 'Не указывайте пароль если он не требуется для работы с вашим сервером SMTP';

$lang['DISABLE_PRIVMSG'] = 'Личные сообщения';
$lang['INBOX_LIMITS'] = 'Макс. число сообщений в папке &laquo;Входящие&raquo;';
$lang['SENTBOX_LIMITS'] = 'Макс. число сообщений в папке &laquo;Отправленные&raquo;';
$lang['SAVEBOX_LIMITS'] = 'Макс. число сообщений в папке &laquo;Сохранённые&raquo;';

// Visual Confirmation
$lang['VISUAL_CONFIRM'] = 'Включить визуальное подтверждение';
$lang['VISUAL_CONFIRM_EXPLAIN'] = 'Потребовать от пользователей ввести при регистрации изображённый на картинке код.';

// Autologin Keys - added 2.0.18
$lang['ALLOW_AUTOLOGIN'] = 'Разрешить автоматический вход на форум';
$lang['ALLOW_AUTOLOGIN_EXPLAIN'] = 'Разрешен ли пользователям автоматический вход на форум';
$lang['AUTOLOGIN_TIME'] = 'Автоматический вход на форум действителен';
$lang['AUTOLOGIN_TIME_EXPLAIN'] = 'Срок в днях с последнего посещения, в течение которого пользователь может автоматически войти на форум. Установите равным нулю, если хотите отключить данную возможность.';
//
// Forum Management
//
$lang['FORUM_ADMIN_MAIN'] = 'Управление форумами';
$lang['FORUM_ADMIN_EXPLAIN'] = 'Здесь вы можете создавать, удалять и изменять порядок вывода категорий и форумов';
$lang['EDIT_FORUM'] = 'Изменить форум';
$lang['CREATE_FORUM'] = 'Создать новый форум';
$lang['CREATE_CATEGORY'] = 'Создать новую категорию';
$lang['REMOVE'] = 'Удалить';
$lang['ACTION'] = 'Действие';
$lang['UPDATE_ORDER'] = 'Изменить порядок';
$lang['CONFIG_UPDATED'] = 'Конфигурация форумов успешно изменена';
$lang['EDIT'] = 'Изменить';
$lang['MOVE_UP'] = 'вверх'; // 'Сдвинуть вверх';
$lang['MOVE_DOWN'] = 'вниз'; // 'Сдвинуть вниз';
$lang['RESYNC'] = 'Синхронизация';
$lang['NO_MODE'] = 'Не было задано действие';
$lang['FORUM_EDIT_DELETE_EXPLAIN'] = 'Здесь вы можете изменить название и описание форума, закрыть его (или вновь открыть) и настроить автоматическую чистку. Для управления правами доступа к форуму воспользуйтесь соответствующей ссылкой в левой части.';

$lang['MOVE_CONTENTS'] = 'Перенести всё содержимое';
$lang['FORUM_DELETE'] = 'Удалить форум';
$lang['FORUM_DELETE_EXPLAIN'] = 'Здесь вы сможете удалить форум (или категорию) и решить, куда перенести все темы (или форумы), которые там содержались.';
$lang['CATEGORY_DELETE'] = 'Удалить Категорию';

$lang['STATUS_LOCKED'] = 'Закрыт';
$lang['STATUS_UNLOCKED'] = 'Открыт';
$lang['FORUM_SETTINGS'] = 'Общие параметры форума';
$lang['FORUM_NAME'] = 'Название форума';
$lang['FORUM_DESC'] = 'Описание';
$lang['FORUM_STATUS'] = 'Статус форума';
$lang['FORUM_PRUNING'] = 'Автоматическая чистка';

$lang['PRUNE_DAYS'] = 'Удалять темы, в которых не было сообщений последние';
$lang['SET_PRUNE_DATA'] = 'Вы выбрали для этого форума автоматическую чистку, но не указали количество дней. Пожалуйста, вернитесь и укажите.';

$lang['MOVE_AND_DELETE'] = 'Перенести и удалить';

$lang['DELETE_ALL_POSTS'] = 'Удалить все темы';
$lang['NOWHERE_TO_MOVE'] = 'Некуда переносить';

$lang['EDIT_CATEGORY'] = 'Изменить категорию';
$lang['EDIT_CATEGORY_EXPLAIN'] = 'Используйте эту форму, чтобы изменить название категории';

$lang['FORUMS_UPDATED'] = 'Информация о форумах и категориях успешно изменена';

$lang['MUST_DELETE_FORUMS'] = 'Вы должны удалить все форумы, прежде чем сможете удалить эту категорию';

$lang['CLICK_RETURN_FORUMADMIN'] = '%sВернуться к управлению форумами%s';

$lang['SHOW_ALL_FORUMS_ON_ONE_PAGE'] = 'Открыть все форумы на одной странице';

//
// Smiley Management
//
$lang['SMILEY_TITLE'] = 'Утилита редактирования смайликов';
$lang['SMILE_DESC'] = 'Здесь вы можете редактировать список смайликов';

$lang['SMILEY_CONFIG'] = 'Управление смайликами';
$lang['SMILEY_CODE'] = 'Код смайлика';
$lang['SMILEY_URL'] = 'Файл с изображением смайлика';
$lang['SMILEY_EMOT'] = 'Эмоция смайлика';
$lang['SMILE_ADD'] = 'Добавить новый смайлик';
$lang['SMILE'] = 'Смайлик';
$lang['EMOTION'] = 'Эмоция';

$lang['SELECT_PAK'] = 'Выберите файл с набором (.pak)';
$lang['REPLACE_EXISTING'] = 'Заменить существующий смайлик';
$lang['KEEP_EXISTING'] = 'Сохранить существующий смайлик';
$lang['SMILEY_IMPORT_INST'] = 'Вы должны распаковать набор смайликов и закачать все файлы в подходящую для вашей установки директорию. Потом выберите в этой форме нужную информацию для импорта набора смайликов.';
$lang['SMILEY_IMPORT'] = 'Импорт набора смайликов';
$lang['CHOOSE_SMILE_PAK'] = 'Выберите файл .pak с набором';
$lang['IMPORT'] = 'Импортировать смайлики';
$lang['SMILE_CONFLICTS'] = 'Что делать в случае конфликта';
$lang['DEL_EXISTING_SMILEYS'] = 'Удалить перед импортом существующие смайлики';
$lang['IMPORT_SMILE_PACK'] = 'Импортировать набор смайликов';
$lang['EXPORT_SMILE_PACK'] = 'Создать набор смайликов';
$lang['EXPORT_SMILES'] = 'Для создания набора смайликов из смайликов, установленных в данный момент, %sскачайте файл smiles.pak%s. Переименуйте его как вам нужно, сохранив при этом расширение .pak, затем создайте файл zip, содержащий все изображения смайликов, а также этот файл.';

$lang['SMILEY_ADD_SUCCESS'] = 'Смайлик был успешно добавлен';
$lang['SMILEY_EDIT_SUCCESS'] = 'Смайлик был успешно изменён';
$lang['SMILEY_IMPORT_SUCCESS'] = 'Набор смайликов был успешно импортирован';
$lang['SMILEY_DEL_SUCCESS'] = 'Смайлик был успешно удалён';
$lang['CLICK_RETURN_SMILEADMIN'] = '%sВернуться к списку смайликов%s';


//
// User Management
//
$lang['USER_ADMIN'] = 'Управление пользователями';
$lang['USER_ADMIN_EXPLAIN'] = 'Здесь вы можете изменить информацию о пользователе. Чтобы изменить права доступа используйте панель управления правами доступа';

$lang['LOOK_UP_USER'] = 'Выбрать пользователя';

$lang['ADMIN_USER_FAIL'] = 'Не могу изменить профиль пользователя';
$lang['ADMIN_USER_UPDATED'] = 'Профиль пользователя был успешно изменён';
$lang['CLICK_RETURN_USERADMIN'] = '%sВернуться к управлению пользователями%s';

$lang['USER_DELETE'] = 'Удаление';
$lang['USER_DELETE_EXPLAIN'] = 'Удалить этого пользователя';
$lang['USER_DELETED'] = 'Пользователь был успешно удалён';
$lang['DELETE_USER_POSTS'] = 'Удалить все сообщения пользователя';

$lang['USER_STATUS'] = 'Пользователь активен';
$lang['USER_ALLOWPM'] = 'Может посылать личные сообщения';
$lang['USER_ALLOWAVATAR'] = 'Может показывать аватару';

$lang['ADMIN_AVATAR_EXPLAIN'] = 'Здесь вы можете просмотреть и удалить текущую аватару пользователя';

$lang['USER_SPECIAL'] = 'Поля только для админа';
$lang['USER_SPECIAL_EXPLAIN'] = 'Эти поля сами пользователи редактировать не могут. Здесь вы можете установить их статус и сделать прочие недоступные им настройки.';


//
// Group Management
//
$lang['GROUP_ADMINISTRATION'] = 'Управление группами';
$lang['GROUP_ADMIN_EXPLAIN'] = 'Здесь вы можете управлять всеми вашими группами: это включает удаление, добавление и изменение групп. Вы можете назначать модераторов, изменять открытый/закрытый статус группы и устанавливать её название и описание.';
$lang['ERROR_UPDATING_GROUPS'] = 'Ошибка при изменении группы.';
$lang['UPDATED_GROUP'] = 'Группа была успешно изменена';
$lang['ADDED_NEW_GROUP'] = 'Группа была успешно создана';
$lang['DELETED_GROUP'] = 'Группа была успешно удалена';
$lang['CREATE_NEW_GROUP'] = 'Создать новую группу';
$lang['EDIT_GROUP'] = 'Изменить группу';
$lang['GROUP_STATUS'] = 'Статус группы';
$lang['GROUP_DELETE'] = 'Удалить группу.';
$lang['GROUP_DELETE_CHECK'] = 'Удалить эту группу';
$lang['SUBMIT_GROUP_CHANGES'] = 'Сохранить изменения';
$lang['RESET_GROUP_CHANGES'] = 'Отменить изменения';
$lang['NO_GROUP_NAME'] = 'Вы должны указать название группы';
$lang['NO_GROUP_MODERATOR'] = 'Вы должны выбрать модератора группы';
$lang['NO_GROUP_MODE'] = 'Вы должны выбрать режим группы: открытый или закрытый';
$lang['NO_GROUP_ACTION'] = 'Не было выбрано действие';
$lang['DELETE_OLD_GROUP_MOD'] = 'Удалить старого модератора?';
$lang['DELETE_OLD_GROUP_MOD_EXPL'] = 'Если вы меняете модератора группы и поставите здесь галочку, то предыдущий модератор будет исключён из группы. Если вы её не поставите, то он станет обычным членом группы.';
$lang['CLICK_RETURN_GROUPSADMIN'] = '%sВернуться к управлению группами%s';
$lang['SELECT_GROUP'] = 'Выберите группу';
$lang['LOOK_UP_GROUP'] = 'Выбрать группу';


//
// Prune Administration
//
$lang['FORUM_PRUNE'] = 'Чистка форумов';
$lang['FORUM_PRUNE_EXPLAIN'] = 'Будут удалены темы, в которых не было новых сообщений за выбранное число дней. Если вы не введёте число, то будут удалены все темы. Не будут удалены <b>прилепленные</b> темы и <b>объявления</b>. Вам придётся удалять такие темы вручную.';
$lang['DO_PRUNE'] = 'Провести чистку';
$lang['ALL_FORUMS'] = 'Все форумы';
$lang['PRUNE_TOPICS_NOT_POSTED'] = 'Удалить темы, в которых не было ответов за данное кол-во дней';
$lang['TOPICS_PRUNED'] = 'Тем вычищено';
$lang['POSTS_PRUNED'] = 'Сообщений вычищено';
$lang['PRUNE_SUCCESS'] = 'Форум успешно почищен';


//
// Word censor
//
$lang['WORDS_TITLE'] = 'Автоцензор';
$lang['WORDS_EXPLAIN'] = 'Здесь вы можете добавить, изменить или удалить слова, которые будут автоматически подвергаться цензуре на ваших форумах. Кроме того, пользователи не смогут зарегистрироваться под именами, содержащими эти слова. В списке слов могут использоваться шаблоны (*), т.е. к \'*тест*\' подойдёт \'протестировать\', к \'тест*\' &mdash; \'тестирование\', к \'*тест\' &mdash; \'протест\'.<br>(Примечание переводчика) Рекомендую пользоваться этой фичей <b>очень</b> аккуратно: например, некие очевидные замены буду неадекватно реагировать на слова \'потребитель\', \'употреблять\' и т.п.';
$lang['WORD'] = 'Слово';
$lang['EDIT_WORD_CENSOR'] = 'Изменить автоцензор';
$lang['REPLACEMENT'] = 'Замена';
$lang['ADD_NEW_WORD'] = 'Добавить новое слово';
$lang['UPDATE_WORD'] = 'Обновить автоцензор';

$lang['MUST_ENTER_WORD'] = 'Вы должны ввести слово и его замену';
$lang['NO_WORD_SELECTED'] = 'Не выбрано слово для редактирования';

$lang['WORD_UPDATED'] = 'Выбранный автоцензор был успешно изменён';
$lang['WORD_ADDED'] = 'Автоцензор был успешно добавлен';
$lang['WORD_REMOVED'] = 'Выбранный автоцензор был успешно удалён';

$lang['CLICK_RETURN_WORDADMIN'] = '%sВернуться к управлению автоцензором%s';


//
// Mass Email
//
$lang['MASS_EMAIL_EXPLAIN'] = 'Вы можете разослать e-mail сообщение либо всем вашим пользователям, либо пользователям, входящим в определённую группу. Сообщение будет отправлено на административный адрес, с BCC: всем получателям. Если вы отправляете письмо большой группе людей, то будьте терпеливы: не останавливайте загрузку страницы после нажатия кнопки. Массовая рассылка может занять много времени, вы увидите сообщение, когда выполнение завершится.';
$lang['COMPOSE'] = 'Текст сообщения';

$lang['RECIPIENTS'] = 'Получатели';
$lang['ALL_USERS'] = 'Все пользователи';

$lang['EMAIL_SUCCESSFULL'] = 'Ваше сообщение было отправлено';
$lang['CLICK_RETURN_MASSEMAIL'] = '%sВернуться к массовой рассылке%s';


//
// Ranks admin
//
$lang['RANKS_TITLE'] = 'Управление званиями';
$lang['RANKS_EXPLAIN'] = 'Здесь вы можете добавлять, редактировать, просматривать и удалять звания. Вы также можете создавать специальные звания, которые могут затем быть присвоены пользователям на странице управления пользователями.';

$lang['ADD_NEW_RANK'] = 'Новое звание';

$lang['RANK_TITLE'] = 'Звание';
$lang['RANK_SPECIAL'] = 'Специальное звание';
$lang['RANK_MINIMUM'] = 'Минимум сообщений';
$lang['RANK_MAXIMUM'] = 'Максимум сообщений';
$lang['RANK_IMAGE'] = 'Картинка к званию (относительно корня phpBB2)';
$lang['RANK_IMAGE_EXPLAIN'] = 'Здесь вы можете присвоить всем имеющим такое звание специальное изображение. Вы можете указать либо относительный, либо абсолютный путь к изображению';

$lang['MUST_SELECT_RANK'] = 'Извините, вы не выбрали звание. Вернитесь и попробуйте ещё раз.';
$lang['NO_ASSIGNED_RANK'] = 'Специального звания не присвоено';

$lang['RANK_UPDATED'] = 'Звание было успешно изменено';
$lang['RANK_ADDED'] = 'Звание было успешно добавлено';
$lang['RANK_REMOVED'] = 'Звание было успешно удалено';
$lang['NO_UPDATE_RANKS'] = 'Звание было успешно удалено. Тем не менее, информация о пользователях, у которых было это звание, не была изменена. Вам придётся изменить эту информацию вручную.';

$lang['CLICK_RETURN_RANKADMIN'] = '%sВернуться к управлению званиями%s';

//
// Disallow Username Admin
//
$lang['DISALLOW_CONTROL'] = 'Запрещённые имена пользователя';
$lang['DISALLOW_EXPLAIN'] = "Здесь вы можете задать имена, которые будут запрещены к использованию. Запрещённые имена могут содержать шаблон '*'. Учтите: вы не сможете запретить имя, если уже существует пользователь с таким именем. Вам придётся сначала удалить пользователя, а уже потом запретить имя.";

$lang['DELETE_DISALLOW'] = 'Удалить';
$lang['DELETE_DISALLOW_TITLE'] = 'Удалить запрещённое имя пользователя';
$lang['DELETE_DISALLOW_EXPLAIN'] = 'Вы можете убрать запрещённое имя, выбрав его из списка и нажав кнопку &laquo;сохранить&raquo;';

$lang['ADD_DISALLOW'] = 'Добавить';
$lang['ADD_DISALLOW_TITLE'] = 'Добавить запрещённое имя пользователя';
$lang['ADD_DISALLOW_EXPLAIN'] = 'Вы можете запретить имя пользователя, используя шаблон \'*\', который подходит к любому символу';

$lang['NO_DISALLOWED'] = 'Нет запрещённых имён';

$lang['DISALLOWED_DELETED'] = 'Запрещённое имя пользователя было успешно удалено';
$lang['DISALLOW_SUCCESSFUL'] = 'Запрещённое имя пользователя было успешно добавлено';
$lang['DISALLOWED_ALREADY'] = 'Имя, которое вы пытаетесь запретить, либо уже запрещено, либо есть в списке нецензурных слов, либо существует пользователь с подходящим именем';

$lang['CLICK_RETURN_DISALLOWADMIN'] = '%sВернуться к управлению запрещёнными именами%s';

// FTP
$lang['ATTACHMENT_FTP_SETTINGS'] = 'Настройка закачки вложений на FTP';
$lang['FTP_CHOOSE'] = 'Выберите метод скачивания';
$lang['FTP_OPTION'] = '<br />В этой версии PHP включены возможности FTP, вы можете попробовать сначала автоматически закачать файл настроек по FTP в нужный каталог.';
$lang['FTP_INSTRUCTS'] = 'Вы решили закачать файл настроек по FTP в каталог, содержащий phpBB 2. Пожалуйста, укажите информацию, требуемую для осуществления этого процесса. Учтите, что путь FTP должен быть полным путём к вашей установке phpBB 2, как если бы вы пользовались обычным клиентом FTP.';
$lang['FTP_INFO'] = 'Укажите настройки FTP';
$lang['ATTEMPT_FTP'] = 'Попробовать закачать файл настроек по FTP';
$lang['SEND_FILE'] = 'Просто прислать файл, я закачаю его вручную';
$lang['FTP_PATH'] = 'Путь FTP к каталогу phpBB 2';
$lang['FTP_USERNAME'] = 'Имя пользователя для FTP';
$lang['FTP_PASSWORD'] = 'Пароль для FTP';
$lang['TRANSFER_CONFIG'] = 'Начать закачку';
$lang['NOFTP_CONFIG'] = 'Попытка закачать файл настроек по FTP завершилась неудачей. Пожалуйста, скачайте файл настроек и поместите его в нужный каталог вручную.';

//
// Version Check
//
$lang['VERSION_INFORMATION'] = 'Информация о версии TorrentPier';

//
// Login attempts configuration
//
$lang['MAX_LOGIN_ATTEMPTS'] = 'Разрешено попыток входа';
$lang['MAX_LOGIN_ATTEMPTS_EXPLAIN'] = 'Количество разрешенных попыток входа на трекер. Для отключения поставьте 0.';
$lang['LOGIN_RESET_TIME'] = 'Время блокировки имени пользователя.';
$lang['LOGIN_RESET_TIME_EXPLAIN'] = 'Время, через которое пользователь сможет войти на трекер, после превышения количества разрешенных попыток входа (в минутах).';

// TODO: Translate PLST to RUS
// Permissions List
//
$lang['PERMISSIONS_LIST'] = 'Список прав доступа';
$lang['AUTH_CONTROL_CATEGORY'] = 'Права доступа к категориям';
$lang['FORUM_AUTH_LIST_EXPLAIN'] = 'Этот модуль дает возможность установить права доступа для каждого форума. Вы можете изменить эти права, простым или расширеным способом, нажав на название форума. Помните, что при изменении прав доступа повлияет на пользователей, выполнять различные операции в них.';
$lang['CAT_AUTH_LIST_EXPLAIN'] = 'This provides a summary of the authorisation levels of each forum within this category. You can edit the permissions of individual forums, using either a simple or advanced method by clicking on the forum name. Alternatively, you can set the permissions for all the forums in this category by using the drop-down menus at the bottom of the page. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.';
$lang['FORUM_AUTH_LIST_EXPLAIN_ALL'] = 'Все пользователи';
$lang['FORUM_AUTH_LIST_EXPLAIN_REG'] = 'Все зарегистрированые пользователи';
$lang['FORUM_AUTH_LIST_EXPLAIN_PRIVATE'] = 'Только пользователи со спец правами';
$lang['FORUM_AUTH_LIST_EXPLAIN_MOD'] = 'Только модераторы этого форума';
$lang['FORUM_AUTH_LIST_EXPLAIN_ADMIN'] = 'Только администраторы';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VIEW'] = '%s может просматривать этот форум';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_READ'] = '%s может просматривать сообщения в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POST'] = '%s может создавать сообщения в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_REPLY'] = '%s может отвечать на сообщения в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_EDIT'] = '%s может редактировать сообщения в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DELETE'] = '%s может удалять сообщения в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_STICKY'] = '%s может прикреплять темы в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ANNOUNCE'] = '%s может размещать объявления в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_VOTE'] = '%s может голосовать в опросах этого форума';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_POLLCREATE'] = '%s может создавать опросы в этом форуме';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_ATTACHMENTS'] = '%s может прикреплять вложения';
$lang['FORUM_AUTH_LIST_EXPLAIN_AUTH_DOWNLOAD'] = '%s может скачивать вложения';

//
// Misc
//
$lang['SF_SHOW_ON_INDEX'] = 'Показывать на главной';
$lang['SF_PARENT_FORUM'] = 'Родительский форум';
$lang['SF_NO_PARENT'] = 'Нет родительского форума';
$lang['TEMPLATE'] = 'Шаблон';

//
// Reports
//
$lang['REPORT_CONFIG_EXPLAIN'] = 'На этой странице находятся основные настройки модуля "Сообщения о нарушениях".';
$lang['REPORT_SUBJECT_AUTH'] = 'Индивидуальные права доступа';
$lang['REPORT_SUBJECT_AUTH_EXPLAIN'] = 'Если опция включена, то модераторы смогут просматривать и редактировать только сообщения о нарушениях в модерируемых ими форумах.';
$lang['REPORT_MODULES_CACHE'] = 'Кэшировать модули в файлах';
$lang['REPORT_MODULES_CACHE_EXPLAIN'] = 'Замечание: права доступа к директории cache в режим "полный доступ на запись и чтение" (<em>CHMOD 777</em>).';
$lang['REPORT_NOTIFY'] = 'Уведомления по e-mail';
$lang['REPORT_NOTIFY_CHANGE'] = 'об изменениях статусов и новых сообщениях';
$lang['REPORT_NOTIFY_NEW'] = 'о новых сообщениях';
$lang['REPORT_LIST_ADMIN'] = 'Список сообщений доступен только администратору';
$lang['REPORT_NEW_WINDOW'] = 'Открывать страницу с нарушением в новом окне';
$lang['REPORT_NEW_WINDOW_EXPLAIN'] = 'Эта опция так же влияет н вид ссылок к форме отправки сообщения о нарушении на страницах просмотра тем.';
$lang['REPORT_CONFIG_UPDATED'] = 'Конфигурция обновлена.';
$lang['CLICK_RETURN_REPORT_CONFIG'] = '%sНажмите%s для возврата к настройкам модуля.';

$lang['MODULES_REASONS'] = 'Модули &amp; Причины';
$lang['REPORT_ADMIN_EXPLAIN'] = 'На этой странице вы можете установить новый модуль, изменить настройки модуля или удалить уже установленный модуль. Так же здесь вы можете задать установить Причины написания сообщений о нарушении для каждого модуля.';
$lang['REPORT_MODULE'] = 'Модуль Сообщений о нарушении';
$lang['INSTALLED_MODULES'] = 'Установленные модули';
$lang['NO_MODULES_INSTALLED'] = 'Нет установленных модулей';
$lang['REASONS'] = 'Причины (%d)';
$lang['SYNC'] = 'Синхронизировать';
$lang['UNINSTALL'] = 'Удалить';
$lang['INSTALL2'] = 'Установить';
$lang['INACTIVE_MODULES'] = 'Неактивные модули';
$lang['NO_MODULES_INACTIVE'] = 'Нет неактивных модулей';
$lang['REPORT_MODULE_NOT_EXISTS'] = 'Выбранный модель не существует.';
$lang['CLICK_RETURN_REPORT_ADMIN'] = '%sНажмите%s для возврата к настройкам Модулей &amp; Причин.';

$lang['BACK_MODULES'] = 'Назад к модулям';
$lang['REPORT_REASON'] = 'Причина написания сообщения';
$lang['NO_REASONS'] = 'Нет определенных Причин для этого модуля';
$lang['ADD_REASON'] = 'Добавить Причину';
$lang['EDIT_REASON'] = 'Редактировать Причину';
$lang['REASON_DESC_EXPLAIN'] = 'Если название совпадет с языковой переменно, то будет использована переменная.';
$lang['REASON_DESC_EMPTY'] = 'Нобходимо ввести текст Причины.';
$lang['REPORT_REASON_ADDED'] = 'Причина добавлена.';
$lang['REPORT_REASON_EDITED'] = 'Причина отредактирована.';
$lang['DELETE_REASON'] = 'Удалить Причину';
$lang['DELETE_REPORT_REASON_EXPLAIN'] = 'Вы уверены, что хотите удалить выбранную Причину?';
$lang['REPORT_REASON_DELETED'] = 'Причина удалена.';
$lang['REPORT_REASON_NOT_EXISTS'] = 'Выбранная Причина не существует.';
$lang['CLICK_RETURN_REPORT_REASONS'] = '%sНажмите%s для возврата к настройкам Причин сообщений о нарушениях.';

$lang['REPORT_MODULE_SYNCED'] = 'Модуль синхронизирован.';

$lang['UNINSTALL_REPORT_MODULE'] = 'Удалить модуль';
$lang['UNINSTALL_REPORT_MODULE_EXPLAIN'] = 'Вы уверены, что хотите удалить выбранный модуль? <br />Замечание: все сообщения для этого модуля также будут удалены.';
$lang['REPORT_MODULE_UNINSTALLED'] = 'Модуль удален.';

$lang['INSTALL_REPORT_MODULE'] = 'Установить модуль';
$lang['EDIT_REPORT_MODULE'] = 'Редактировать настройки модуля';
$lang['REPORT_PRUNE'] = 'Очистить сообщения';
$lang['REPORT_PRUNE_EXPLAIN'] = 'Зыкрытые и отмеченные для удаления сообщения автоматически будут удалены через  <var>x</var> дней. Значение <em>zero</em> отключает автоматическую чистку.';
$lang['REPORT_PERMISSIONS'] = 'Права доступа';
$lang['WRITE'] = 'Написать';
$lang['REPORT_AUTH'] = array(
	REPORT_AUTH_USER => 'Пользователи',
	REPORT_AUTH_MOD => 'Модераторы',
	REPORT_AUTH_CONFIRM => 'Модераторы (после подтверждения)',
	REPORT_AUTH_ADMIN => 'Администраторы');
$lang['REPORT_AUTH_NOTIFY_EXPLAIN'] = 'Модераторы будут уведомлены только, если они могут просматривать и редактировать сообщение.';
$lang['REPORT_AUTH_DELETE_EXPLAIN'] = 'Если выбрано <em>Модераторы (после подтверждения)</em>, удаление сообщения должно быть подтверждено администратором.';
$lang['REPORT_MODULE_INSTALLED'] = 'Модуль удален.';
$lang['REPORT_MODULE_EDITED'] = 'Модуль отредактирован.';
$lang['REPORTS'] = 'Нарушения';
//
// Reports [END]
//
