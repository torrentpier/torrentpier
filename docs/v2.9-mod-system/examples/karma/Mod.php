<?php
/**
 * Karma System Mod for TorrentPier v2.9
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

namespace TorrentPier\Mod\KarmaSystem;

use TorrentPier\Mod\AbstractMod;
use TorrentPier\Mod\Hook;
use TorrentPier\Mod\KarmaSystem\Models\Karma;

/**
 * Main Karma System Mod Class
 *
 * This mod implements a comprehensive karma/reputation system
 * allowing users to upvote or downvote each other based on
 * their contributions to the community.
 */
class Mod extends AbstractMod
{
    /**
     * Boot the mod
     *
     * Register all hooks and initialize mod functionality.
     *
     * @return void
     */
    public function boot()
    {
        // Only run if mod is enabled
        if (!$this->config('enabled', true)) {
            return;
        }

        // Register user-related hooks
        if ($this->config('show_in_username', false)) {
            Hook::add_filter('user.display_name', [$this, 'addKarmaToUsername'], 10, 2);
        }

        Hook::add_filter('profile.info_display', [$this, 'displayKarmaInProfile'], 10, 2);

        // Register post-related hooks
        if ($this->config('show_in_posts', true)) {
            Hook::add_action('post.after_display', [$this, 'displayKarmaButtons'], 10, 1);
        }

        // Register AJAX handlers
        Hook::add_action('ajax.karma_vote', [$this, 'handleVote']);
        Hook::add_action('ajax.karma_stats', [$this, 'getStats']);

        // Register admin page
        if (is_admin()) {
            Hook::add_action('admin.page.karma-settings', [$this, 'showAdminPage']);
            Hook::add_action('admin.save.karma-settings', [$this, 'saveAdminPage']);
        }

        // Add CSS and JavaScript
        Hook::add_action('template.head', [$this, 'addAssets']);
    }

    /**
     * Add karma to username display
     *
     * @param string $username Original username
     * @param array $user User data
     * @return string Modified username with karma
     */
    public function addKarmaToUsername($username, $user)
    {
        $karma = $this->getUserKarma($user['user_id'] ?? 0);

        if ($karma !== null) {
            $icon = $karma >= 0 ? $this->config('icon_upvote', '👍') : $this->config('icon_downvote', '👎');
            return $username . sprintf(' <span class="karma-badge">%s %d</span>', $icon, abs($karma));
        }

        return $username;
    }

    /**
     * Display karma information in user profile
     *
     * @param string $content Profile content
     * @param array $user User data
     * @return string Modified profile content
     */
    public function displayKarmaInProfile($content, $user)
    {
        if (!$this->config('show_in_profile', true)) {
            return $content;
        }

        $user_id = $user['user_id'] ?? 0;
        $karma_data = $this->getKarmaDetails($user_id);

        if ($karma_data) {
            $karma_html = $this->template('profile_karma', [
                'KARMA_POINTS' => $karma_data['karma_points'],
                'POSITIVE_VOTES' => $karma_data['positive_votes'],
                'NEGATIVE_VOTES' => $karma_data['negative_votes'],
                'L_KARMA' => $this->lang('KARMA_POINTS'),
                'L_POSITIVE' => $this->lang('POSITIVE_VOTES'),
                'L_NEGATIVE' => $this->lang('NEGATIVE_VOTES'),
            ], true);

            return $content . $karma_html;
        }

        return $content;
    }

    /**
     * Display karma voting buttons in posts
     *
     * @param array $post Post data
     * @return void
     */
    public function displayKarmaButtons($post)
    {
        global $userdata;

        // Don't show buttons if user can't vote
        if (!$this->canUserVote($userdata['user_id'] ?? 0)) {
            return;
        }

        // Don't show buttons for own posts
        $post_user_id = $post['user_id'] ?? 0;
        if ($post_user_id === ($userdata['user_id'] ?? 0) && !$this->config('self_vote_allowed', false)) {
            return;
        }

        // Check if user already voted
        $existing_vote = $this->getUserVoteForUser($userdata['user_id'] ?? 0, $post_user_id);

        echo $this->template('post_karma', [
            'POST_USER_ID' => $post_user_id,
            'UPVOTED' => $existing_vote === 1 ? 'active' : '',
            'DOWNVOTED' => $existing_vote === -1 ? 'active' : '',
            'ICON_UPVOTE' => $this->config('icon_upvote', '👍'),
            'ICON_DOWNVOTE' => $this->config('icon_downvote', '👎'),
            'L_UPVOTE' => $this->lang('UPVOTE'),
            'L_DOWNVOTE' => $this->lang('DOWNVOTE'),
        ], true);
    }

    /**
     * Handle karma vote AJAX request
     *
     * @return void
     */
    public function handleVote()
    {
        global $userdata;

        // Check permission
        $this->requirePermission('user');

        // Get parameters
        $target_user_id = (int) request_var('user_id', 0);
        $vote_value = (int) request_var('vote', 0); // 1 = upvote, -1 = downvote
        $reason = request_var('reason', '', true);

        // Validate vote value
        if (!in_array($vote_value, [1, -1], true)) {
            ajax_error($this->lang('ERROR_INVALID_VOTE'), 400);
        }

        // Validate target user
        if ($target_user_id <= 0) {
            ajax_error($this->lang('ERROR_INVALID_USER'), 400);
        }

        $voter_id = $userdata['user_id'];

        // Check if user can vote
        if (!$this->canUserVote($voter_id)) {
            $min_posts = $this->config('min_posts_to_vote', 10);
            ajax_error($this->lang('ERROR_INSUFFICIENT_POSTS', $min_posts), 403);
        }

        // Check if voting for self
        if ($target_user_id === $voter_id && !$this->config('self_vote_allowed', false)) {
            ajax_error($this->lang('ERROR_SELF_VOTE'), 403);
        }

        // Check daily vote limit
        if (!$this->checkDailyVoteLimit($voter_id)) {
            $limit = $this->config('votes_per_day', 5);
            ajax_error($this->lang('ERROR_VOTE_LIMIT', $limit), 429);
        }

        // Check if reason is required
        if ($this->config('require_reason', false) && empty($reason)) {
            ajax_error($this->lang('ERROR_REASON_REQUIRED'), 400);
        }

        try {
            // Process vote
            $result = Karma::vote($voter_id, $target_user_id, $vote_value, $reason);

            // Get updated karma
            $new_karma = $this->getUserKarma($target_user_id);

            ajax_response([
                'success' => true,
                'message' => $this->lang('VOTE_SUCCESS'),
                'karma' => $new_karma,
                'vote_type' => $vote_value === 1 ? 'upvote' : 'downvote'
            ]);
        } catch (\Exception $e) {
            $this->log('Karma vote error: ' . $e->getMessage());
            ajax_error($this->lang('ERROR_VOTE_FAILED'), 500);
        }
    }

    /**
     * Get karma statistics
     *
     * @return void
     */
    public function getStats()
    {
        $this->requirePermission('user');

        $user_id = (int) request_var('user_id', 0);

        if ($user_id <= 0) {
            ajax_error($this->lang('ERROR_INVALID_USER'), 400);
        }

        $stats = $this->getKarmaDetails($user_id);

        ajax_response([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Show admin settings page
     *
     * @return void
     */
    public function showAdminPage()
    {
        $this->template('admin/settings', [
            'ENABLED' => $this->config('enabled', true) ? 'checked' : '',
            'MIN_POSTS' => $this->config('min_posts_to_vote', 10),
            'VOTES_PER_DAY' => $this->config('votes_per_day', 5),
            'UPVOTE_VALUE' => $this->config('upvote_value', 1),
            'DOWNVOTE_VALUE' => $this->config('downvote_value', -1),
            'SHOW_IN_PROFILE' => $this->config('show_in_profile', true) ? 'checked' : '',
            'SHOW_IN_POSTS' => $this->config('show_in_posts', true) ? 'checked' : '',
            'SHOW_IN_USERNAME' => $this->config('show_in_username', false) ? 'checked' : '',
            'REQUIRE_REASON' => $this->config('require_reason', false) ? 'checked' : '',
            'SELF_VOTE_ALLOWED' => $this->config('self_vote_allowed', false) ? 'checked' : '',
            'ICON_UPVOTE' => $this->config('icon_upvote', '👍'),
            'ICON_DOWNVOTE' => $this->config('icon_downvote', '👎'),
        ]);
    }

    /**
     * Save admin settings
     *
     * @return void
     */
    public function saveAdminPage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Save settings
        $this->config()->set('enabled', (bool) request_var('enabled', false));
        $this->config()->set('min_posts_to_vote', (int) request_var('min_posts', 10));
        $this->config()->set('votes_per_day', (int) request_var('votes_per_day', 5));
        $this->config()->set('upvote_value', (int) request_var('upvote_value', 1));
        $this->config()->set('downvote_value', (int) request_var('downvote_value', -1));
        $this->config()->set('show_in_profile', (bool) request_var('show_in_profile', false));
        $this->config()->set('show_in_posts', (bool) request_var('show_in_posts', false));
        $this->config()->set('show_in_username', (bool) request_var('show_in_username', false));
        $this->config()->set('require_reason', (bool) request_var('require_reason', false));
        $this->config()->set('self_vote_allowed', (bool) request_var('self_vote_allowed', false));

        // Clear cache
        $this->cache()->deleteByTag('karma');

        $this->flash('success', $this->lang('SETTINGS_SAVED'));
        redirect('/admin/mod-settings?mod=karma-system');
    }

    /**
     * Add CSS and JavaScript assets
     *
     * @return void
     */
    public function addAssets()
    {
        echo '<link rel="stylesheet" href="' . $this->asset('css/style.css') . '">';
        echo '<script src="' . $this->asset('js/karma.js') . '"></script>';
    }

    /**
     * Check if user can vote
     *
     * @param int $user_id User ID
     * @return bool
     */
    private function canUserVote($user_id)
    {
        if ($user_id <= 0) {
            return false;
        }

        $min_posts = $this->config('min_posts_to_vote', 10);
        $user_posts = $this->getUserPostCount($user_id);

        return $user_posts >= $min_posts;
    }

    /**
     * Get user post count
     *
     * @param int $user_id User ID
     * @return int
     */
    private function getUserPostCount($user_id)
    {
        return $this->cache("user_{$user_id}_posts", function () use ($user_id) {
            return (int) DB()->fetchField("
                SELECT user_posts FROM bb_users WHERE user_id = ?
            ", $user_id);
        }, 3600);
    }

    /**
     * Check daily vote limit
     *
     * @param int $user_id User ID
     * @return bool True if user hasn't exceeded limit
     */
    private function checkDailyVoteLimit($user_id)
    {
        $today_start = strtotime('today');
        $votes_today = (int) DB()->fetchField("
            SELECT COUNT(*) FROM bb_karma_votes
            WHERE voter_id = ? AND created_at >= ?
        ", $user_id, $today_start);

        $limit = $this->config('votes_per_day', 5);
        return $votes_today < $limit;
    }

    /**
     * Get user's karma points
     *
     * @param int $user_id User ID
     * @return int|null Karma points or null if not found
     */
    private function getUserKarma($user_id)
    {
        return Karma::getKarma($user_id);
    }

    /**
     * Get detailed karma information
     *
     * @param int $user_id User ID
     * @return array|null Karma details or null if not found
     */
    private function getKarmaDetails($user_id)
    {
        return Karma::getDetails($user_id);
    }

    /**
     * Get user's existing vote for another user
     *
     * @param int $voter_id Voter user ID
     * @param int $target_user_id Target user ID
     * @return int|null Vote value (1, -1) or null if no vote
     */
    private function getUserVoteForUser($voter_id, $target_user_id)
    {
        return Karma::getUserVote($voter_id, $target_user_id);
    }

    /**
     * Recalculate karma for all users (cron job)
     *
     * @return void
     */
    public function recalculateAllKarma()
    {
        $this->log('Starting karma recalculation for all users');

        try {
            $updated = Karma::recalculateAll();
            $this->log("Karma recalculated for {$updated} users");

            // Clear all karma cache
            $this->cache()->deleteByTag('karma');
        } catch (\Exception $e) {
            $this->log('Karma recalculation failed: ' . $e->getMessage(), 'error');
        }
    }
}
