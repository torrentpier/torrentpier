<?php
/**
 * Karma System Language File - English
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

return [
    // General
    'MOD_NAME' => 'Karma System',
    'MOD_DESCRIPTION' => 'User reputation system with upvote/downvote functionality',

    // Karma terms
    'KARMA' => 'Karma',
    'KARMA_POINTS' => 'Karma Points',
    'TOTAL_KARMA' => 'Total Karma',
    'POSITIVE_VOTES' => 'Positive Votes',
    'NEGATIVE_VOTES' => 'Negative Votes',

    // Actions
    'UPVOTE' => 'Upvote',
    'DOWNVOTE' => 'Downvote',
    'VOTE' => 'Vote',
    'VOTE_SUCCESS' => 'Your vote has been recorded',
    'VOTE_UPDATED' => 'Your vote has been updated',
    'VOTE_REMOVED' => 'Your vote has been removed',

    // Errors
    'ERROR_INVALID_VOTE' => 'Invalid vote value',
    'ERROR_INVALID_USER' => 'Invalid user ID',
    'ERROR_SELF_VOTE' => 'You cannot vote for yourself',
    'ERROR_INSUFFICIENT_POSTS' => 'You need at least %d posts to vote',
    'ERROR_VOTE_LIMIT' => 'You have reached your daily vote limit (%d votes per day)',
    'ERROR_REASON_REQUIRED' => 'Please provide a reason for your vote',
    'ERROR_VOTE_FAILED' => 'Failed to record vote. Please try again.',
    'ERROR_PERMISSION_DENIED' => 'You do not have permission to perform this action',

    // Admin settings
    'SETTINGS_TITLE' => 'Karma System Settings',
    'SETTINGS_SAVED' => 'Settings saved successfully',

    'SETTING_ENABLED' => 'Enable karma system',
    'SETTING_MIN_POSTS' => 'Minimum posts required to vote',
    'SETTING_VOTES_PER_DAY' => 'Maximum votes per day',
    'SETTING_UPVOTE_VALUE' => 'Points for upvote',
    'SETTING_DOWNVOTE_VALUE' => 'Points for downvote',
    'SETTING_SHOW_PROFILE' => 'Show karma in user profiles',
    'SETTING_SHOW_POSTS' => 'Show voting buttons in posts',
    'SETTING_SHOW_USERNAME' => 'Show karma next to username',
    'SETTING_REQUIRE_REASON' => 'Require reason for votes',
    'SETTING_SELF_VOTE' => 'Allow self-voting',
    'SETTING_ICON_UPVOTE' => 'Upvote icon',
    'SETTING_ICON_DOWNVOTE' => 'Downvote icon',

    // Help text
    'HELP_MIN_POSTS' => 'Users must have at least this many posts before they can vote',
    'HELP_VOTES_PER_DAY' => 'Each user can cast this many votes per 24-hour period',
    'HELP_UPVOTE_VALUE' => 'Number of karma points added for each upvote (usually 1)',
    'HELP_DOWNVOTE_VALUE' => 'Number of karma points removed for each downvote (usually -1)',
    'HELP_REQUIRE_REASON' => 'Users must provide a text reason when voting',
    'HELP_SELF_VOTE' => 'Allow users to vote for themselves (not recommended)',

    // Statistics
    'STATS_TOTAL_KARMA' => 'Total Karma',
    'STATS_VOTES_GIVEN' => 'Votes Given',
    'STATS_VOTES_RECEIVED' => 'Votes Received',
    'STATS_TOP_USERS' => 'Top Users by Karma',
    'STATS_RECENT_VOTES' => 'Recent Votes',

    // Notifications
    'NOTIFY_RECEIVED_UPVOTE' => '%s gave you an upvote',
    'NOTIFY_RECEIVED_DOWNVOTE' => '%s gave you a downvote',

    // Misc
    'VIEW_KARMA_HISTORY' => 'View karma history',
    'KARMA_LEADERBOARD' => 'Karma Leaderboard',
    'YOUR_KARMA' => 'Your Karma',
];
