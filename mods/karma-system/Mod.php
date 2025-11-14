<?php
/**
 * Karma System Mod for TorrentPier v2.9
 *
 * @package TorrentPier\Mod\KarmaSystem
 * @author TorrentPier Team
 * @license MIT
 */

namespace TorrentPier\Mod\KarmaSystem;

use TorrentPier\ModSystem\AbstractMod;
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
    public function boot(): void
    {
        dev()->log('hooks', 'karma-system: boot() started');

        // Only run if mod is enabled
        if (!$this->config('enabled', true)) {
            dev()->log('hooks', 'karma-system: Mod is disabled in config');
            return;
        }

        // Register user-related hooks
        if ($this->config('show_in_username', false)) {
            dev()->log('hooks', 'karma-system: Registering user.display_name hook');
            hooks()->add_filter('user.display_name', [$this, 'addKarmaToUsername'], 10, 2);
        }

        dev()->log('hooks', 'karma-system: Registering profile.info_display hook');
        hooks()->add_filter('profile.info_display', [$this, 'displayKarmaInProfile'], 10, 2);

        // Register post-related hooks
        if ($this->config('show_in_posts', true)) {
            dev()->log('hooks', 'karma-system: Registering post.after_display hook');
            hooks()->add_action('post.after_display', [$this, 'displayKarmaButtons'], 10, 1);
        }

        // Register AJAX handlers
        dev()->log('hooks', 'karma-system: Registering AJAX handlers');
        hooks()->add_action('ajax.karma_vote', [$this, 'handleVote']);
        hooks()->add_action('ajax.karma_stats', [$this, 'getStats']);

        // Register admin page
        // TODO: Implement is_admin() check when admin system is ready
        // if (\is_admin()) {
        //     hooks()->add_action('admin.page.karma-settings', [$this, 'showAdminPage']);
        //     hooks()->add_action('admin.save.karma-settings', [$this, 'saveAdminPage']);
        // }

        // Start output buffering to inject JavaScript after posts are rendered
        // This ensures karma data is collected before JavaScript is output
        dev()->log('hooks', 'karma-system: Starting output buffer');
        ob_start([$this, 'injectKarmaAssets']);

        dev()->log('hooks', 'karma-system: boot() complete');
    }

    /**
     * Add karma to username display
     *
     * @param string $profile_html Already rendered profile HTML
     * @param array $user User data
     * @return string Modified profile HTML with karma
     */
    public function addKarmaToUsername($profile_html, $user)
    {
        $user_id = $user['user_id'] ?? 0;
        if ($user_id <= 0) {
            return $profile_html;
        }

        $karma = $this->getUserKarma($user_id);

        if ($karma !== null) {
            $icon = $karma >= 0 ? $this->config('icon_upvote', '👍') : $this->config('icon_downvote', '👎');
            $karma_badge = sprintf(' <span class="karma-badge" style="font-size: 0.9em; color: %s;">%s %d</span>',
                $karma >= 0 ? '#28a745' : '#dc3545',
                $icon,
                abs($karma)
            );
            return $profile_html . $karma_badge;
        }

        return $profile_html;
    }

    /**
     * Display karma information in user profile
     *
     * @param array $profiledata User profile data
     * @return void
     */
    public function displayKarmaInProfile($profiledata)
    {
        if (!$this->config('show_in_profile', true)) {
            return;
        }

        global $template;

        $user_id = $profiledata['user_id'] ?? 0;
        $karma_data = $this->getKarmaDetails($user_id);

        if ($karma_data) {
            // Add karma data to template variables
            $template->assign_vars([
                'KARMA_POINTS' => $karma_data['karma_points'],
                'KARMA_POSITIVE' => $karma_data['positive_votes'],
                'KARMA_NEGATIVE' => $karma_data['negative_votes'],
                'SHOW_KARMA' => true,
            ]);

            dev()->log('hooks', 'karma-system: Added karma data to profile template', $karma_data);
        }
    }

    /**
     * Display karma voting buttons in posts
     *
     * @param array $post_data Post data from viewtopic.php
     * @return void
     */
    public function displayKarmaButtons($post_data)
    {
        file_put_contents('/tmp/karma-inject.log', "displayKarmaButtons() called\n", FILE_APPEND);

        dev()->log('hooks', 'karma-system: displayKarmaButtons() CALLED', ['post_data_keys' => array_keys($post_data)]);

        global $userdata;

        $voter_id = $userdata['user_id'] ?? 0;
        $post_user_id = $post_data['user_id'] ?? 0;
        $post_id = $post_data['post_id'] ?? 0;

        dev()->log('hooks', 'karma-system: voter and post user IDs', [
            'voter_id' => $voter_id,
            'post_user_id' => $post_user_id,
            'post_id' => $post_id
        ]);

        // TEMPORARY: Disable checks for testing
        /*
        // Don't show buttons if user can't vote
        if (!$this->canUserVote($voter_id)) {
            dev()->log('hooks', 'karma-system: User cannot vote', ['voter_id' => $voter_id]);
            return;
        }

        // Don't show buttons for own posts
        if ($post_user_id === $voter_id && !$this->config('self_vote_allowed', false)) {
            dev()->log('hooks', 'karma-system: Self-vote not allowed - RETURNING WITHOUT OUTPUT');
            return;
        }
        */

        dev()->log('hooks', 'karma-system: Passed all checks (CHECKS DISABLED FOR TESTING), will display karma buttons');

        // Check if user already voted
        try {
            $existing_vote = $this->getUserVoteForUser($voter_id, $post_user_id);
        } catch (\Throwable $e) {
            dev()->log('hooks', 'karma-system: Error getting vote', ['error' => $e->getMessage()]);
            $existing_vote = 0;
        }
        dev()->log('hooks', 'karma-system: Got existing vote', ['existing_vote' => $existing_vote]);

        // Get current karma
        try {
            $current_karma = $this->getUserKarma($post_user_id) ?? 0;
        } catch (\Throwable $e) {
            dev()->log('hooks', 'karma-system: Error getting karma', ['error' => $e->getMessage()]);
            $current_karma = 0;
        }
        dev()->log('hooks', 'karma-system: Got current karma', ['current_karma' => $current_karma]);

        // Store karma data for this post in global array
        // JavaScript will use this to inject buttons
        if (!isset($GLOBALS['karma_posts_data'])) {
            $GLOBALS['karma_posts_data'] = [];
        }

        $GLOBALS['karma_posts_data'][$post_id] = [
            'post_id' => $post_id,
            'user_id' => $post_user_id,
            'karma' => $current_karma,
            'existing_vote' => $existing_vote,
            'upvote_active' => $existing_vote === 1,
            'downvote_active' => $existing_vote === -1,
        ];

        dev()->log('hooks', 'karma-system: Stored karma data for post', [
            'post_id' => $post_id,
            'total_posts' => count($GLOBALS['karma_posts_data'])
        ]);
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
     * Inject CSS and JavaScript assets via output buffer
     *
     * This method is called as an output buffer callback to inject
     * karma assets into the page HTML after posts are rendered.
     *
     * @param string $buffer The output buffer content
     * @param int|null $phase The output buffer phase (optional)
     * @return string Modified buffer with injected assets
     */
    public function injectKarmaAssets(string $buffer, ?int $phase = null): string
    {
        try {
            dev()->log('hooks', 'karma-system: injectKarmaAssets() called', ['phase' => $phase, 'buffer_length' => strlen($buffer)]);

            // Get karma data collected during post rendering
            $karma_data = $GLOBALS['karma_posts_data'] ?? [];

            dev()->log('hooks', 'karma-system: Found karma data for posts', ['count' => count($karma_data)]);

            if (empty($karma_data)) {
                dev()->log('hooks', 'karma-system: No karma data to inject, returning buffer unchanged');
                return $buffer;
            }
            // Build CSS and JavaScript to inject
            $assets = '<style>
            .karma-badge {
                display: inline-block;
                margin-left: 5px;
                font-weight: bold;
            }
            .karma-buttons {
                margin-top: 10px;
                padding: 10px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
            }
            .karma-upvote, .karma-downvote {
                padding: 5px 15px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                border-radius: 3px;
                transition: all 0.2s;
                margin-left: 5px;
            }
            .karma-upvote:hover {
                background: #d4edda;
                border-color: #28a745;
            }
            .karma-downvote:hover {
                background: #f8d7da;
                border-color: #dc3545;
            }
            .karma-upvote.active {
                background: #28a745;
                color: white;
                border-color: #28a745;
            }
            .karma-downvote.active {
                background: #dc3545;
                color: white;
                border-color: #dc3545;
            }
        </style>
        <script>
        window.karmaData = ' . json_encode($karma_data, JSON_THROW_ON_ERROR) . ';
        $(document).ready(function() {
            console.log("Karma system: Injecting buttons for", Object.keys(window.karmaData).length, "posts");

            $.each(window.karmaData, function(postId, data) {
                var $postBody = $("#post_" + postId + " .post_body");

                if ($postBody.length === 0) {
                    console.warn("Karma system: Post body not found for post", postId);
                    return;
                }

                var upvoteClass = data.upvote_active ? "karma-upvote active" : "karma-upvote";
                var downvoteClass = data.downvote_active ? "karma-downvote active" : "karma-downvote";

                var karmaHtml =
                    \'<div class="karma-buttons">\' +
                    \'<strong>Karma: \' + data.karma + \'</strong>\' +
                    \'<button class="\' + upvoteClass + \'" data-user-id="\' + data.user_id + \'">\' +
                    \'👍 Upvote\' +
                    \'</button>\' +
                    \'<button class="\' + downvoteClass + \'" data-user-id="\' + data.user_id + \'">\' +
                    \'👎 Downvote\' +
                    \'</button>\' +
                    \'</div>\';

                $postBody.append(karmaHtml);
                console.log("Karma system: Injected buttons for post", postId);
            });

            console.log("Karma system: Injection complete");
        });
        </script>';

        // Inject before </body> or </html>
        if (stripos($buffer, '</body>') !== false) {
            $buffer = str_ireplace('</body>', $assets . "\n</body>", $buffer);
            dev()->log('hooks', 'karma-system: Injected assets before </body>');
        } elseif (stripos($buffer, '</html>') !== false) {
            $buffer = str_ireplace('</html>', $assets . "\n</html>", $buffer);
            dev()->log('hooks', 'karma-system: Injected assets before </html>');
        } else {
            // No closing tags found, append to end
            $buffer .= $assets;
            dev()->log('hooks', 'karma-system: Appended assets to end of buffer');
        }

            dev()->log('hooks', 'karma-system: injectKarmaAssets() complete - CSS/JS added for ' . count($karma_data) . ' posts');

            return $buffer;
        } catch (\Throwable $e) {
            error_log('Karma system injection error: ' . $e->getMessage());
            return $buffer;
        }
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
            $user_id = (int)$user_id;
            return (int) DB()->fetch_row("
                SELECT user_posts FROM bb_users WHERE user_id = $user_id
            ", 'user_posts');
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
        $user_id = (int)$user_id;
        $today_start = (int)strtotime('today');

        $result = DB()->fetch_row("
            SELECT COUNT(*) as cnt FROM bb_karma_votes
            WHERE voter_id = $user_id AND created_at >= $today_start
        ");

        $votes_today = (int)($result['cnt'] ?? 0);
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
