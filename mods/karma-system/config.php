<?php
/**
 * Karma System Configuration
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

return [
    'karma' => [
        // General settings
        'enabled' => true,

        // Voting requirements
        'min_posts_to_vote' => 10,        // Minimum posts required to vote
        'votes_per_day' => 5,             // Maximum votes per day per user
        'self_vote_allowed' => false,     // Allow users to vote for themselves
        'require_reason' => false,        // Require reason/comment when voting

        // Point values
        'upvote_value' => 1,              // Points for upvote
        'downvote_value' => -1,           // Points for downvote (negative)

        // Display settings
        'show_in_profile' => true,        // Show karma in user profile
        'show_in_posts' => true,          // Show karma buttons in posts
        'show_in_username' => false,      // Show karma next to username
        'icon_upvote' => '👍',            // Upvote icon/emoji
        'icon_downvote' => '👎',          // Downvote icon/emoji

        // Performance
        'cache_duration' => 3600,         // Cache karma for 1 hour
        'recalculate_schedule' => 'daily', // How often to recalculate all karma

        // Advanced
        'log_votes' => true,              // Log all votes for auditing
        'allow_vote_change' => true,      // Allow users to change their vote
        'show_vote_history' => false,     // Show who voted (privacy setting)
    ]
];
