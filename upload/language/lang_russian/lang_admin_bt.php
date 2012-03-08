<?php

$lang['RETURN_CONFIG'] = '%sВернуться к настройкам%s';
$lang['CONFIG_UPD'] = 'Конфигурация успешно изменена';
$lang['SET_DEFAULTS'] = 'Значения по умолчанию';

//
// Tracker config
//
$lang['TRACKER_CFG_TITLE'] = 'Трекер';
$lang['FORUM_CFG_TITLE'] = 'Настройки форумов';
$lang['TRACKER_SETTINGS'] = 'Настройки трекера';

$lang['OFF'] = 'Отключить трекер';
$lang['OFF_REASON'] = 'Причина отключения';
$lang['OFF_REASON_EXPL'] = 'этот текст будет отправляться клиенту пока трекер отключен';
$lang['AUTOCLEAN'] = 'Автоочистка';
$lang['AUTOCLEAN_EXPL'] = 'периодически очищать таблицу peer\'s - не отключайте без особой необходимости!';
$lang['COMPACT_MODE'] = 'Компактный режим';
$lang['COMPACT_MODE_EXPL'] = '"Да" - трекер будет работать только в компактном режиме<br />"Нет" - будет определяется клиентом<br />в компактном режиме расход трафика наименьший, но могут возникнуть проблемы из-за несовместимости с очень старыми клиентами';
$lang['BROWSER_REDIRECT_URL'] = 'Browser redirect URL';
$lang['BROWSER_REDIRECT_URL_EXPL'] = "переадресация на этот URL при попытке зайти на трекер Web browser'ом<br />оставьте пустым для отключения";

$lang['ANNOUNCE_INTERVAL_HEAD'] = 'Разное';
$lang['ANNOUNCE_INTERVAL'] = 'Announce интервал';
$lang['ANNOUNCE_INTERVAL_EXPL'] = 'пауза между announcements';
$lang['NUMWANT'] = 'Значение numwant';
$lang['NUMWANT_EXPL'] = 'количество источников (peers) отправляемых клиенту';
$lang['EXPIRE_FACTOR'] = 'Фактор смерти peer\'ов';
$lang['EXPIRE_FACTOR_EXPL'] = "время жизни peer'а расчитывается как announce интервал умноженный на фактор смерти peer'а<br />должен быть не меньше 1";
$lang['IGNORE_GIVEN_IP'] = 'Игнорировать указанный клиентом IP';
$lang['UPDATE_DLSTAT'] = 'Вести учет скачанного/отданного юзером';

$lang['LIMIT_ACTIVE_TOR_HEAD'] = 'Ограничения';
$lang['LIMIT_ACTIVE_TOR'] = 'Ограничить количество одновременных закачек';
$lang['LIMIT_SEED_COUNT'] = 'Seeding ограничение';
$lang['LIMIT_SEED_COUNT_EXPL'] = 'ограничение на количество одновременных раздач<br />0 - нет ограничений';
$lang['LIMIT_LEECH_COUNT'] = 'Leeching ограничение';
$lang['LIMIT_LEECH_COUNT_EXPL'] = 'ограничение на количество одновременных закачек<br />0 - нет ограничений';
$lang['LEECH_EXPIRE_FACTOR'] = 'Leech expire factor';
$lang['LEECH_EXPIRE_FACTOR_EXPL'] = 'сколько минут считать начатую закачку активной, независимо от того, остановил ли ее юзер<br />0 - учитывать остановку';
$lang['LIMIT_CONCURRENT_IPS'] = 'Ограничить количество подключений с разных IP';
$lang['LIMIT_CONCURRENT_IPS_EXPL'] = 'считается отдельно для каждого торрента';
$lang['LIMIT_SEED_IPS'] = 'Seeding IP ограничение';
$lang['LIMIT_SEED_IPS_EXPL'] = "раздаваь можно не более чем с <i>хх</i> IP's<br />(0 - нет ограничений)";
$lang['LIMIT_LEECH_IPS'] = 'Leeching IP ограничение';
$lang['LIMIT_LEECH_IPS_EXPL'] = "скачивать можно не более чем с <i>хх</i> IP's<br />(0 - нет ограничений)";

$lang['USE_AUTH_KEY_HEAD'] = 'Авторизация';
$lang['USE_AUTH_KEY'] = 'Passkey';
$lang['USE_AUTH_KEY_EXPL'] = 'включить авторизацию по passkey';
$lang['AUTH_KEY_NAME'] = 'Имя ключа passkey';
$lang['AUTH_KEY_NAME_EXPL'] = 'имя ключа, который будет добавляться в GET запросе к announce url для идентификации юзера';
$lang['ALLOW_GUEST_DL'] = 'Разрешить "гостям" (неавторизованным юзерам) доступ к трекеру';

//
// Forum config
//
$lang['FORUM_CFG_EXPL'] = 'Настройки форума';

$lang['BT_SELECT_FORUMS'] = 'Форумы, в которых:';
$lang['BT_SELECT_FORUMS_EXPL'] = 'для выделения нескольких форумов, отмечайте их с нажатой клавишей <i>Ctrl</i>';

$lang['REG_TORRENTS'] = 'Регистрация торрентов';
$lang['ALLOWED'] = 'Разрешена';
$lang['DISALLOWED'] = 'Запрещена';
$lang['ALLOW_REG_TRACKER'] = 'Разрешена регистрация торрентов на трекере';
$lang['ALLOW_PORNO_TOPIC'] = 'Разрешено создавать порно топики';
$lang['SHOW_DL_BUTTONS'] = 'Показывать кнопки для изменения DL-статуса';
$lang['SELF_MODERATED'] = 'Автор топика может перенести его в другой форум';

$lang['BT_ANNOUNCE_URL_HEAD'] = 'Announce URL';
$lang['BT_ANNOUNCE_URL'] = 'Announce url';
$lang['BT_ANNOUNCE_URL_EXPL'] = 'дополнительные разрешенные адреса можно задать в "includes/announce_urls.php"';
$lang['BT_DISABLE_DHT'] = 'Запретить DHT сети';
$lang['BT_DISABLE_DHT_EXPL'] = 'Запретить обмен пирами и DHT (рекомендовано для приватных сетей, только url announce)';
$lang['BT_CHECK_ANNOUNCE_URL'] = 'Проверять announce url';
$lang['BT_CHECK_ANNOUNCE_URL_EXPL'] = 'разрешить регистрацию на трекере только если announce url входит в список разрешенных';
$lang['BT_REPLACE_ANN_URL'] = 'Заменять announce url';
$lang['BT_REPLACE_ANN_URL_EXPL'] = 'заменять оригинальный announce url в .torrent файлах на ваш';
$lang['BT_DEL_ADDIT_ANN_URLS'] = 'Удалять все дополнительные announce urls';
$lang['BT_DEL_ADDIT_ANN_URLS_EXPL'] = 'если торрент содержит адреса других трекеров, они будут удалены';
$lang['BT_ADD_COMMENT'] = 'Добавлять в торрент комментарий';
$lang['BT_ADD_COMMENT_EXPL'] = 'оставьте пустым для добавления адреса топика в качестве комментария';
$lang['BT_ADD_PUBLISHER'] = 'Добавлять адрес топика как publisher-url и это имя в качестве имени publisher';
$lang['BT_ADD_PUBLISHER_EXPL'] = 'для отключения - оставьте пустым';

$lang['BT_SHOW_PEERS_HEAD'] = 'Peers-List';
$lang['BT_SHOW_PEERS'] = 'Показывать список источников (seeders/leechers)';
$lang['BT_SHOW_PEERS_EXPL'] = 'будет выводиться над топиком с торрентом';
$lang['BT_SHOW_PEERS_MODE'] = 'По умолчанию показывать источники как:';
$lang['BT_SHOW_PEERS_MODE_COUNT'] = 'Только количество';
$lang['BT_SHOW_PEERS_MODE_NAMES'] = 'Только имена';
$lang['BT_SHOW_PEERS_MODE_FULL'] = 'Подробно';
$lang['BT_ALLOW_SPMODE_CHANGE'] = 'Разрешить подробный показ источников';
$lang['BT_ALLOW_SPMODE_CHANGE_EXPL'] = 'если выбрано "нет" - будет доступен только режим по умолчанию';
$lang['BT_SHOW_IP_ONLY_MODER'] = '<b>IP</b> могут видеть только модераторы';
$lang['BT_SHOW_PORT_ONLY_MODER'] = '<b>Port</b> могут видеть только модераторы';

$lang['BT_SHOW_DL_LIST_HEAD'] = 'DL-List';
$lang['BT_SHOW_DL_LIST'] = 'Показывать DL-List при просмотре топика';
$lang['BT_DL_LIST_ONLY_1ST_PAGE'] = 'Показывать DL-List только на первой странице топика';
$lang['BT_DL_LIST_ONLY_COUNT'] = 'Показывать только количество';
$lang['BT_SHOW_DL_LIST_BUTTONS'] = 'Показывать кнопки для изменения DL-статуса';
$lang['BT_SHOW_DL_BUT_WILL'] = $lang['DL_WILL'];
$lang['BT_SHOW_DL_BUT_DOWN'] = $lang['DL_DOWN'];
$lang['BT_SHOW_DL_BUT_COMPL'] = $lang['DL_COMPLETE'];
$lang['BT_SHOW_DL_BUT_CANCEL'] = $lang['DL_CANCEL'];

$lang['BT_ADD_AUTH_KEY_HEAD'] = 'Passkey';
$lang['BT_ADD_AUTH_KEY'] = 'Aвтодобавление passkey к торрент-файлам перед их скачиванием';
$lang['BT_GEN_PASSKEY_ON_REG'] = 'Автоматически генерировать passkey';
$lang['BT_GEN_PASSKEY_ON_REG_EXPL'] = 'если passkey не найден, генерировать его при первом скачивании торрента';

$lang['BT_TOR_BROWSE_ONLY_REG_HEAD'] = 'Torrent browser (трекер)';
$lang['BT_TOR_BROWSE_ONLY_REG'] = 'Torrent browser (tracker.php) не доступен для гостей';
$lang['BT_SEARCH_BOOL_MODE'] = 'Разрешить полнотекстовый поиск в логическом режиме';
$lang['BT_SEARCH_BOOL_MODE_EXPL'] = 'использовать *, +, - и т.д. при поиске';

$lang['BT_SHOW_DL_STAT_ON_INDEX_HEAD'] = 'Разное';
$lang['BT_SHOW_DL_STAT_ON_INDEX'] = 'Показывать UL/DL статистику юзера на главной странице форума';
$lang['BT_NEWTOPIC_AUTO_REG'] = 'Регистрировать торренты на трекере для новых топиков';
$lang['BT_SET_DLTYPE_ON_TOR_REG'] = 'Изменять статус топика на "Download" во время регистрации торрента на трекере';
$lang['BT_SET_DLTYPE_ON_TOR_REG_EXPL'] = 'не зависит от того, разрешено ли в этом форуме создавать Download-топики (в настройках форумов)';
$lang['BT_UNSET_DLTYPE_ON_TOR_UNREG'] = 'Изменять статус топика на "Normal" во время удаления торрента с трекера';

//
// Release
//
$lang['LIST_FORUMS'] = 'Список форумов';
$lang['LIST_OF_PATTERNS'] = 'Список шаблонов';
$lang['ADD_TEMPLATE'] = 'Добавить шаблон';

$lang['RELEASE_EXP'] = 'На этой странице отображаются форумы, для которых можно выбрать шаблон нового топика (релиза).';
$lang['TPL_NONE'] = 'Не использовать шаблоны';
$lang['TPL_VIDEO'] = 'Видео, с указанием перевода';
$lang['TPL_VIDEO_HOME'] = 'Видео, без указания перевода';
$lang['TPL_VIDEO_SIMPLE'] = 'Видео, без подробностей';
$lang['TPL_VIDEO_LESSON'] = 'Видеоуроки';
$lang['TPL_GAMES'] = 'Игры';
$lang['TPL_GAMES_PS'] = 'Игры PS/PS2';
$lang['TPL_GAMES_PSP'] = 'Игры PSP';
$lang['TPL_GAMES_XBOX'] = 'Игры XBOX';
$lang['TPL_PROGS'] = 'Программы';
$lang['TPL_PROGS_MAC'] = 'Программы Mac OS';
$lang['TPL_MUSIC'] = 'Музыка';
$lang['TPL_BOOKS'] = 'Книги';
$lang['TPL_AUDIOBOOKS'] = 'Аудиокниги';
$lang['TPL_SPORT'] = 'Спорт';