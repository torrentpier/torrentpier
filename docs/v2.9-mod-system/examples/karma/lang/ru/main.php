<?php
/**
 * Karma System Language File - Russian
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

return [
    // General
    'MOD_NAME' => 'Система кармы',
    'MOD_DESCRIPTION' => 'Система репутации пользователей с возможностью голосования',

    // Karma terms
    'KARMA' => 'Карма',
    'KARMA_POINTS' => 'Очки кармы',
    'TOTAL_KARMA' => 'Всего кармы',
    'POSITIVE_VOTES' => 'Положительных голосов',
    'NEGATIVE_VOTES' => 'Отрицательных голосов',

    // Actions
    'UPVOTE' => 'Повысить',
    'DOWNVOTE' => 'Понизить',
    'VOTE' => 'Голосовать',
    'VOTE_SUCCESS' => 'Ваш голос учтен',
    'VOTE_UPDATED' => 'Ваш голос обновлен',
    'VOTE_REMOVED' => 'Ваш голос удален',

    // Errors
    'ERROR_INVALID_VOTE' => 'Неверное значение голоса',
    'ERROR_INVALID_USER' => 'Неверный ID пользователя',
    'ERROR_SELF_VOTE' => 'Вы не можете голосовать за себя',
    'ERROR_INSUFFICIENT_POSTS' => 'Для голосования нужно как минимум %d сообщений',
    'ERROR_VOTE_LIMIT' => 'Вы достигли дневного лимита голосов (%d голосов в день)',
    'ERROR_REASON_REQUIRED' => 'Пожалуйста, укажите причину вашего голоса',
    'ERROR_VOTE_FAILED' => 'Не удалось записать голос. Попробуйте еще раз.',
    'ERROR_PERMISSION_DENIED' => 'У вас нет прав для выполнения этого действия',

    // Admin settings
    'SETTINGS_TITLE' => 'Настройки системы кармы',
    'SETTINGS_SAVED' => 'Настройки успешно сохранены',

    'SETTING_ENABLED' => 'Включить систему кармы',
    'SETTING_MIN_POSTS' => 'Минимум сообщений для голосования',
    'SETTING_VOTES_PER_DAY' => 'Максимум голосов в день',
    'SETTING_UPVOTE_VALUE' => 'Очков за положительный голос',
    'SETTING_DOWNVOTE_VALUE' => 'Очков за отрицательный голос',
    'SETTING_SHOW_PROFILE' => 'Показывать карму в профилях',
    'SETTING_SHOW_POSTS' => 'Показывать кнопки голосования в сообщениях',
    'SETTING_SHOW_USERNAME' => 'Показывать карму рядом с именем',
    'SETTING_REQUIRE_REASON' => 'Требовать причину для голоса',
    'SETTING_SELF_VOTE' => 'Разрешить голосовать за себя',
    'SETTING_ICON_UPVOTE' => 'Иконка повышения',
    'SETTING_ICON_DOWNVOTE' => 'Иконка понижения',

    // Help text
    'HELP_MIN_POSTS' => 'Пользователи должны иметь как минимум столько сообщений, чтобы голосовать',
    'HELP_VOTES_PER_DAY' => 'Каждый пользователь может отдать столько голосов за 24 часа',
    'HELP_UPVOTE_VALUE' => 'Количество очков кармы за положительный голос (обычно 1)',
    'HELP_DOWNVOTE_VALUE' => 'Количество очков кармы за отрицательный голос (обычно -1)',
    'HELP_REQUIRE_REASON' => 'Пользователи должны указывать текстовую причину при голосовании',
    'HELP_SELF_VOTE' => 'Разрешить пользователям голосовать за себя (не рекомендуется)',

    // Statistics
    'STATS_TOTAL_KARMA' => 'Всего кармы',
    'STATS_VOTES_GIVEN' => 'Отданных голосов',
    'STATS_VOTES_RECEIVED' => 'Полученных голосов',
    'STATS_TOP_USERS' => 'Топ пользователей по карме',
    'STATS_RECENT_VOTES' => 'Последние голоса',

    // Notifications
    'NOTIFY_RECEIVED_UPVOTE' => '%s повысил вашу карму',
    'NOTIFY_RECEIVED_DOWNVOTE' => '%s понизил вашу карму',

    // Misc
    'VIEW_KARMA_HISTORY' => 'Посмотреть историю кармы',
    'KARMA_LEADERBOARD' => 'Рейтинг по карме',
    'YOUR_KARMA' => 'Ваша карма',
];
