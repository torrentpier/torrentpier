<?php

/***************************************************************************
 *                       lang_admin_rebuild_search.php [English]
 *                       ---------------------------------------
 *     begin                : Mon Aug 22 2005
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin_rebuild_search.php,v 2.2.2.0 2006/02/04 18:38:17 chatasos Exp $
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

$lang['REBUILD_SEARCH'] = 'Индексировать';
$lang['REBUILD_SEARCH_DESC'] = 'Эта функция индексирует таблицы поиска. Индексация может занять некоторое время.<br />
Пожалуйста, не закрывайте эту страницу до окончания индексации';

//
// Input screen
//
$lang['STARTING_POST_ID'] = 'Начальный ID сообщения';
$lang['STARTING_POST_ID_EXPLAIN'] = 'Первая запись, с которой начнется обработка<br> Вы можете выбрать, чтобы начать с начала или с записи, на которой вы в последний раз остановились';

$lang['START_OPTION_BEGINNING'] = 'начать с начала';
$lang['START_OPTION_CONTINUE'] = 'продолжить с последней остановленной';

$lang['CLEAR_SEARCH_TABLES'] = 'Очистить таблицы поиска';
$lang['CLEAR_SEARCH_TABLES_EXPLAIN'] = '';
$lang['CLEAR_SEARCH_NO'] = 'Нет';
$lang['CLEAR_SEARCH_DELETE'] = 'Удаление';
$lang['CLEAR_SEARCH_TRUNCATE'] = 'Очистка';

$lang['NUM_OF_POSTS'] = 'Количество записей';
$lang['NUM_OF_POSTS_EXPLAIN'] = 'Общее количество записей в процессе<br />Автоматически заполняется количество от общего/оставшегося числа записей, найденных в БД';

$lang['POSTS_PER_CYCLE'] = 'Записей за цикл';
$lang['POSTS_PER_CYCLE_EXPLAIN'] = 'Количество записей для обработки за один цикл<br />Держите его низким, чтобы избежать таймаута PHP / веб-сервера';

$lang['REFRESH_RATE'] = 'Период обновления';
$lang['REFRESH_RATE_EXPLAIN'] = 'Сколько времени (сек) бездействовать перед переходом к следующему циклу обработки<br />Обычно вам не нужно менять это';

$lang['TIME_LIMIT'] = 'Ограничение времени';
$lang['TIME_LIMIT_EXPLAIN'] = 'Сколько времени (сек) после обработки может длиться до перехода к следующему циклу';
$lang['TIME_LIMIT_EXPLAIN_SAFE'] = '<i>Ваш PHP (Safe Mode) настроен на таймаут %s сек, так что не превышайте этого значения</i>';
$lang['TIME_LIMIT_EXPLAIN_WEBSERVER'] = '<i>Ваш веб-сервер настроен на таймаут %s сек, так что не превышайте этого значения</i>';

$lang['DISABLE_BOARD'] = 'Отключение форума';
$lang['DISABLE_BOARD_EXPLAIN'] = 'Отключать ли форум при обработке';
$lang['DISABLE_BOARD_EXPLAIN_ENABLED'] = 'Он будет включен автоматически после окончания обработки';
$lang['DISABLE_BOARD_EXPLAIN_ALREADY'] = '<i>Ваш форум уже отключен</i>';

//
// Information strings
//
$lang['INFO_PROCESSING_STOPPED'] = 'В последний раз вы остановились в процессе обработки на post_id %s (%s обработанных записей) от %s';
$lang['INFO_PROCESSING_ABORTED'] = 'В последний раз вы прервали процесс обработки на post_id %s (%s обработанных записей) от %s';
$lang['INFO_PROCESSING_ABORTED_SOON'] = 'Пожалуйста, подождите несколько минут, прежде чем продолжить…';
$lang['INFO_PROCESSING_FINISHED'] = 'Вы успешно завершили процесс (%s обработанных записей) от %s';
$lang['INFO_PROCESSING_FINISHED_NEW'] = 'Вы успешно завершили процесс на post_id %s (%s обработанных записей) от %s,<br />но были %s новых записей после этой даты';

//
// Progress screen
//
$lang['REBUILD_SEARCH_PROGRESS'] = 'Процесс перестроения поиска';

$lang['PROCESSED_POST_IDS'] = 'Обработанные записи: %s - %s';
$lang['TIMER_EXPIRED'] = 'Таймер истек в  %s секунд. ';
$lang['CLEARED_SEARCH_TABLES'] = 'Очистка таблиц поиска. ';
$lang['DELETED_POSTS'] = '%s записей были удалены пользователей во время обработки. ';
$lang['PROCESSING_NEXT_POSTS'] = 'Обрабатывается следующие %s записей. Ждите...';
$lang['ALL_SESSION_POSTS_PROCESSED'] = 'Обработаны все записи в текущей сессии.';
$lang['ALL_POSTS_PROCESSED'] = 'Все записи были обработаны успешно.';
$lang['ALL_TABLES_OPTIMIZED'] = 'Все поисковые таблицы были оптимизированы успешно.';

$lang['PROCESSING_POST_DETAILS'] = 'Обрабатываемая запись';
$lang['PROCESSED_POSTS'] = 'Обработанные записи';
$lang['PERCENT'] = 'Процентов';
$lang['CURRENT_SESSION'] = 'Текущая сессия';
$lang['TOTAL'] = 'Всего';

$lang['PROCESS_DETAILS'] = 'с <b>%s</b> до <b>%s</b> (из общего <b>%s</b>)';
$lang['PERCENT_COMPLETED'] = '%s %% завершено';

$lang['PROCESSING_TIME_DETAILS'] = 'Детали текущей сесии';
$lang['PROCESSING_TIME'] = 'Время выполнения';
$lang['TIME_LAST_POSTS'] = 'Последние %s записей';
$lang['TIME_FROM_THE_BEGINNING'] = 'Время с начала';
$lang['TIME_AVERAGE'] = 'Среднее время за цикл';
$lang['TIME_ESTIMATED'] = 'Расчетное время до завершения';

$lang['DAYS'] = 'дней';
$lang['HOURS'] = 'часов';
$lang['MINUTES'] = 'минут';
$lang['SECONDS'] = 'секунд';

$lang['DATABASE_SIZE_DETAILS'] = 'Детали размера БД';
$lang['SIZE_CURRENT'] = 'Текущий';
$lang['SIZE_ESTIMATED'] = 'Расчетный размер после окончания';
$lang['SIZE_SEARCH_TABLES'] = 'Размер таблицы поиска';
$lang['SIZE_DATABASE'] = 'Размер ДБ';

$lang['BYTES'] = 'Байт';

$lang['ACTIVE_PARAMETERS'] = 'Активные параметры';
$lang['POSTS_LAST_CYCLE'] = 'Обработанная(ые) запись(и) на последнем цикле';
$lang['BOARD_STATUS'] = 'Статус форума';
$lang['BOARD_DISABLED'] = 'Отключен';
$lang['BOARD_ENABLED'] = 'Включен';

$lang['INFO_ESTIMATED_VALUES'] = '(*) Все оценочные значения рассчитываются примерно<br />
			на основе текущего завершенного процента и не могут представлять фактического конечного значения.<br />
			С ростом процентов расчетные значения приближаются к фактическим.';

$lang['CLICK_RETURN_REBUILD_SEARCH'] = 'Нажмите %sздесь%s, чтобы вернуться к перестроению поиска';
$lang['REBUILD_SEARCH_ABORTED'] = 'Перестроение поиска прервано на post_id %s.<br /><br />Если вы прервали процесс обработки, вы должны подождать несколько минут до запуска перестроения поиска снова, чтобы последний цикл можно было закончить.';
$lang['WRONG_INPUT'] = 'Вы ввели некоторые неправильные значения. Проверьте введенные данные и попробуйте еще раз.';

// Buttons
$lang['NEXT'] = 'Далее';
$lang['PROCESSING'] = 'Идет обработка...';
$lang['FINISHED'] = 'Закончить';