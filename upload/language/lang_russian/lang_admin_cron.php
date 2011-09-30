<?php

$lang['CRON_LIST'] = 'Список задач';
$lang['CRON_ID'] = 'ID';
$lang['CRON_ACTIVE'] = 'Вкл';
$lang['CRON_ACTIVE_EXPL'] = 'Активность задачи';
$lang['CRON_TITLE'] = 'Название задачи';
$lang['CRON_SCRIPT'] = 'Скрипт';
$lang['CRON_SCHEDULE'] = 'Запуск';
$lang['CRON_LAST_RUN'] = 'Посл. запуск';
$lang['CRON_NEXT_RUN'] = 'След. запуск';
$lang['CRON_RUN_COUNT'] = 'Запусков';
$lang['CRON_MANAGE'] = 'Управление';
$lang['CRON_OPTIONS'] = 'Настройки крона';

$lang['CRON_ENABLED'] = 'Крон включён';
$lang['CRON_CHECK_INTERVAL'] = 'Проверка (сек)';

$lang['WITH_SELECTED'] = 'С выделенными';
$lang['NOTHING'] = 'ничего не делать';
$lang['CRON_RUN'] = 'запустить';
$lang['CRON_DEL'] = 'удалить';
$lang['CRON_DISABLE'] = 'отключить';
$lang['CRON_ENABLE'] = 'включить';

$lang['RUN_MAIN_CRON'] = 'Запустить крон';
$lang['ADD_JOB'] = 'Добавить задачу';
$lang['CRON_WORKS'] = 'Крон в данный момент запущен или завис &#0183; ';
$lang['REPAIR_CRON'] = 'Восстановить';

$lang['CRON_EDIT_HEAD_EDIT'] = 'Редактировать задачу';
$lang['CRON_EDIT_HEAD_ADD'] = 'Добавить задачу';
$lang['CRON_SCRIPT_EXPL'] = 'название в папке "includes/cron/jobs/"';
$lang['SCHEDULE'] = array(
    'select'   => '&raquo; Выберите запуск',
    'hourly'   => 'ежечасно',
	'daily'    => 'ежедневно',
	'weekly'   => 'еженедельно',
	'monthly'  => 'ежемесячно',
	'interval' => 'интервал'
);
$lang['NOSELECT'] = 'Не указан';
$lang['RUN_DAY'] = 'День запуска';
$lang['RUN_DAY_EXPL'] = 'день месяца/недели, когда эта задача будет выполняться';
$lang['RUN_TIME'] = 'Время запуска';
$lang['RUN_TIME_EXPL'] = 'время запуска этой задачи (напр. 05:00:00)';
$lang['RUN_ORDER'] = 'Порядок запуска';
$lang['LAST_RUN'] = 'Последний запуск';
$lang['NEXT_RUN'] = 'Следующий запуск';
$lang['RUN_INTERVAL'] = 'Интервал запуска';
$lang['RUN_INTERVAL_EXPL'] = 'напр. 00:10:00';
$lang['LOG_ENABLED'] = 'Логирование включено';
$lang['LOG_FILE'] = 'Файл лога';
$lang['LOG_FILE_EXPL'] = 'файл, куда будут сохраняться логи';
$lang['LOG_SQL_QUERIES'] = 'Логировать SQL запросы';
$lang['DISABLE_BOARD'] = 'Отключать форум';
$lang['DISABLE_BOARD_EXPL'] = 'отключать форум, когда задача выполняется?';
$lang['RUN_COUNTER'] = 'Кол-во запусков';

$lang['JOB_REMOVED'] = 'Задача была успешно удалена';
$lang['SCRIPT_DUPLICATE'] = 'Скрипт <b>'. @$_POST['cron_script'] .'</b> уже существует!';
$lang['TITLE_DUPLICATE'] = 'Название задачи <b>'. @$_POST['cron_title'] .'</b> уже существует!';
$lang['CLICK_RETURN_JOBS_ADDED'] = '%sВернуться к добавлению задачи%s';
$lang['CLICK_RETURN_JOBS'] = '%sВернуться к планировщику задач%s';
