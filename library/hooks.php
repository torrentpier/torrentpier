<?php
/**
 * TorrentPier Hook Reference
 *
 * This file documents all available hooks in TorrentPier v2.9+ for mod development.
 * Hooks allow mods to extend functionality without modifying core files.
 *
 * Hook Types:
 * - Actions: Execute code at specific points (no return value)
 * - Filters: Modify values passing through the system (must return value)
 *
 * Usage Example:
 * ```php
 * // In your mod's hooks.php
 *
 * // Action: Execute code when post is created
 * hooks()->add_action('post.created', function($post_id, $user_id) {
 *     // Your code here
 * }, 10, 2);
 *
 * // Filter: Modify post content
 * hooks()->add_filter('post.content', function($content, $post_id) {
 *     return str_replace('[secret]', '', $content);
 * }, 10, 2);
 * ```
 *
 * @package TorrentPier\Hooks
 * @since 3.0.0
 */

// This file is for documentation only - do not add executable code here

/**
 * ============================================================================
 * AJAX HOOKS
 * ============================================================================
 */

/**
 * Fires before any AJAX action is executed
 *
 * @action ajax.before_execute
 * @param string $action The AJAX action being executed
 * @param array $request Request data
 * @param array $response Response data (by reference)
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('ajax.before_execute', function($action, $request, $response) {
 *     // Log AJAX calls
 *     Log::info("AJAX action: $action");
 * }, 10, 3);
 */

/**
 * Fires before specific AJAX action
 *
 * @action ajax.before_{action_name}
 * @param array $request Request data
 * @param array $response Response data (by reference)
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('ajax.before_view_post', function($request, $response) {
 *     // Validate post access
 * }, 10, 2);
 */

/**
 * Fires after specific AJAX action
 *
 * @action ajax.after_{action_name}
 * @param array $request Request data
 * @param array $response Response data (by reference)
 * @since 3.0.0
 */

/**
 * Fires after any AJAX action is executed
 *
 * @action ajax.after_execute
 * @param string $action The AJAX action that was executed
 * @param array $request Request data
 * @param array $response Response data (by reference)
 * @since 3.0.0
 */

/**
 * ============================================================================
 * POST HOOKS
 * ============================================================================
 */

/**
 * Fires before a post is created
 *
 * @action post.before_create
 * @param array $post_data Post data to be inserted
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('post.before_create', function($post_data) {
 *     // Validate or modify post data before insertion
 * }, 10, 1);
 */

/**
 * Fires after a post is created
 *
 * @action post.created
 * @param int $post_id The ID of the created post
 * @param int $user_id The ID of the author
 * @param int $topic_id The ID of the topic
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('post.created', function($post_id, $user_id, $topic_id) {
 *     // Award karma points for posting
 *     Karma::add($user_id, 1);
 * }, 10, 3);
 */

/**
 * Fires before a post is edited
 *
 * @action post.before_edit
 * @param int $post_id The ID of the post being edited
 * @param array $old_data Original post data
 * @param array $new_data New post data
 * @since 3.0.0
 */

/**
 * Fires after a post is edited
 *
 * @action post.edited
 * @param int $post_id The ID of the edited post
 * @param int $user_id The ID of the editor
 * @since 3.0.0
 */

/**
 * Fires before a post is deleted
 *
 * @action post.before_delete
 * @param int $post_id The ID of the post to be deleted
 * @param array $post_data Post data
 * @since 3.0.0
 */

/**
 * Fires after a post is deleted
 *
 * @action post.deleted
 * @param int $post_id The ID of the deleted post
 * @param int $user_id The ID of the user who deleted it
 * @since 3.0.0
 */

/**
 * Filter post content before display
 *
 * @filter post.content
 * @param string $content Post content (after BBCode parsing)
 * @param int $post_id The post ID
 * @param array $post_data Full post data
 * @return string Modified content
 * @since 3.0.0
 *
 * @example
 * hooks()->add_filter('post.content', function($content, $post_id, $post_data) {
 *     // Add spoiler tags
 *     return preg_replace('/\[spoiler\](.*?)\[\/spoiler\]/s', '<div class="spoiler">$1</div>', $content);
 * }, 10, 3);
 */

/**
 * Filter whether user can edit post
 *
 * @filter post.can_edit
 * @param bool $can Whether user can edit
 * @param array $post Post data
 * @param array $user User data
 * @return bool Modified permission
 * @since 3.0.0
 *
 * @example
 * hooks()->add_filter('post.can_edit', function($can, $post, $user) {
 *     // Prevent readonly users from editing
 *     if ($user['readonly'] != 0) {
 *         return false;
 *     }
 *     return $can;
 * }, 10, 3);
 */

/**
 * Filter whether user can delete post
 *
 * @filter post.can_delete
 * @param bool $can Whether user can delete
 * @param array $post Post data
 * @param array $user User data
 * @return bool Modified permission
 * @since 3.0.0
 */

/**
 * ============================================================================
 * TOPIC HOOKS
 * ============================================================================
 */

/**
 * Fires before topic rendering (viewtopic.php)
 *
 * @action topic.before_render
 * @param int $topic_id Topic ID
 * @param array $topic_data Topic data
 * @since 3.0.0
 */

/**
 * Fires after topic data is loaded
 *
 * @action topic.loaded
 * @param int $topic_id Topic ID
 * @param array $topic_data Topic data (by reference)
 * @since 3.0.0
 */

/**
 * Fires before each post is rendered in topic view
 *
 * @action topic.post.before_render
 * @param array $post_data Post data (by reference)
 * @param int $topic_id Topic ID
 * @since 3.0.0
 */

/**
 * Fires after all posts are rendered
 *
 * @action topic.posts.rendered
 * @param array $posts Array of rendered posts
 * @param int $topic_id Topic ID
 * @since 3.0.0
 */

/**
 * Filter topic title
 *
 * @filter topic.title
 * @param string $title Topic title
 * @param int $topic_id Topic ID
 * @return string Modified title
 * @since 3.0.0
 */

/**
 * ============================================================================
 * USER HOOKS
 * ============================================================================
 */

/**
 * Fires after user logs in successfully
 *
 * @action user.login
 * @param int $user_id User ID
 * @param array $user_data User data
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('user.login', function($user_id, $user_data) {
 *     // Update last login stats
 *     Stats::update_login($user_id);
 * }, 10, 2);
 */

/**
 * Fires after user logs out
 *
 * @action user.logout
 * @param int $user_id User ID
 * @since 3.0.0
 */

/**
 * Fires after user profile is updated
 *
 * @action user.profile.updated
 * @param int $user_id User ID
 * @param array $old_data Original user data
 * @param array $new_data Updated user data
 * @since 3.0.0
 */

/**
 * Fires when viewing user profile
 *
 * @action user.profile.view
 * @param int $user_id Viewed user's ID
 * @param int $viewer_id Viewing user's ID
 * @since 3.0.0
 */

/**
 * Filter user profile data before display
 *
 * @filter user.profile.data
 * @param array $profile_data Profile data
 * @param int $user_id User ID
 * @return array Modified profile data
 * @since 3.0.0
 *
 * @example
 * hooks()->add_filter('user.profile.data', function($profile_data, $user_id) {
 *     // Add karma to profile
 *     $profile_data['karma'] = Karma::get($user_id);
 *     return $profile_data;
 * }, 10, 2);
 */

/**
 * Filter user permissions
 *
 * @filter user.permissions
 * @param array $permissions User permissions
 * @param int $user_id User ID
 * @return array Modified permissions
 * @since 3.0.0
 */

/**
 * ============================================================================
 * TEMPLATE HOOKS
 * ============================================================================
 */

/**
 * Fires before template file is included
 *
 * @action template.before_include
 * @param string $template_file Template file path
 * @param array $vars Template variables (by reference)
 * @since 3.0.0
 */

/**
 * Fires after template is compiled
 *
 * @action template.compiled
 * @param string $template_file Template file path
 * @param string $output Compiled output (by reference)
 * @since 3.0.0
 */

/**
 * Filter template variables before rendering
 *
 * @filter template.vars
 * @param array $vars Template variables
 * @param string $template_name Template name
 * @return array Modified variables
 * @since 3.0.0
 *
 * @example
 * hooks()->add_filter('template.vars', function($vars, $template_name) {
 *     // Add global mod variable
 *     $vars['MOD_VERSION'] = '1.0.0';
 *     return $vars;
 * }, 10, 2);
 */

/**
 * Filter content to inject into page head
 *
 * @filter template.head.content
 * @param string $content Current head content
 * @return string Modified content (CSS, JS, meta tags)
 * @since 3.0.0
 *
 * @example
 * hooks()->add_filter('template.head.content', function($content) {
 *     return $content . '<link rel="stylesheet" href="/mods/karma/style.css">';
 * }, 10, 1);
 */

/**
 * Filter content to inject into page footer
 *
 * @filter template.footer.content
 * @param string $content Current footer content
 * @return string Modified content (JS, tracking codes)
 * @since 3.0.0
 */

/**
 * ============================================================================
 * TORRENT HOOKS
 * ============================================================================
 */

/**
 * Fires after torrent is uploaded
 *
 * @action torrent.uploaded
 * @param int $torrent_id Torrent ID
 * @param int $user_id Uploader ID
 * @param array $torrent_data Torrent data
 * @since 3.0.0
 */

/**
 * Fires after torrent is downloaded
 *
 * @action torrent.downloaded
 * @param int $torrent_id Torrent ID
 * @param int $user_id Downloader ID
 * @since 3.0.0
 */

/**
 * Fires when torrent status changes
 *
 * @action torrent.status.changed
 * @param int $torrent_id Torrent ID
 * @param string $old_status Previous status
 * @param string $new_status New status
 * @since 3.0.0
 */

/**
 * Filter whether user can download torrent
 *
 * @filter torrent.can_download
 * @param bool $can Whether user can download
 * @param array $torrent Torrent data
 * @param array $user User data
 * @return bool Modified permission
 * @since 3.0.0
 */

/**
 * ============================================================================
 * SEARCH HOOKS
 * ============================================================================
 */

/**
 * Filter search query before execution
 *
 * @filter search.query
 * @param string $query Search query
 * @param string $search_type Type of search (posts, topics, torrents)
 * @return string Modified query
 * @since 3.0.0
 */

/**
 * Filter search results before display
 *
 * @filter search.results
 * @param array $results Search results
 * @param string $query Search query
 * @return array Modified results
 * @since 3.0.0
 */

/**
 * ============================================================================
 * ADMIN HOOKS
 * ============================================================================
 */

/**
 * Fires when admin panel is accessed
 *
 * @action admin.panel.access
 * @param int $user_id Admin user ID
 * @param string $section Admin section accessed
 * @since 3.0.0
 */

/**
 * Fires after configuration is saved
 *
 * @action admin.config.saved
 * @param array $old_config Previous configuration
 * @param array $new_config New configuration
 * @since 3.0.0
 */

/**
 * ============================================================================
 * CRON HOOKS
 * ============================================================================
 */

/**
 * Fires during cron execution
 *
 * @action cron.run
 * @param string $cron_type Type of cron job running
 * @since 3.0.0
 *
 * @example
 * hooks()->add_action('cron.run', function($cron_type) {
 *     // Run mod-specific cleanup tasks
 *     if ($cron_type === 'hourly') {
 *         MyMod::cleanup();
 *     }
 * }, 10, 1);
 */

/**
 * ============================================================================
 * DATABASE HOOKS
 * ============================================================================
 */

/**
 * Fires before SQL query execution
 *
 * @action db.query.before
 * @param string $query SQL query
 * @param array $params Query parameters
 * @since 3.0.0
 */

/**
 * Fires after SQL query execution
 *
 * @action db.query.after
 * @param string $query SQL query
 * @param mixed $result Query result
 * @param float $execution_time Execution time in seconds
 * @since 3.0.0
 */

/**
 * ============================================================================
 * CACHE HOOKS
 * ============================================================================
 */

/**
 * Fires before cache is read
 *
 * @action cache.before_read
 * @param string $key Cache key
 * @since 3.0.0
 */

/**
 * Fires before cache is written
 *
 * @action cache.before_write
 * @param string $key Cache key
 * @param mixed $value Cache value
 * @param int $ttl Time to live (seconds)
 * @since 3.0.0
 */

/**
 * ============================================================================
 * MOD SYSTEM HOOKS
 * ============================================================================
 */

/**
 * Fires when mod is activated
 *
 * @action mod.activated
 * @param string $mod_id Mod ID
 * @param string $version Mod version
 * @since 3.0.0
 */

/**
 * Fires when mod is deactivated
 *
 * @action mod.deactivated
 * @param string $mod_id Mod ID
 * @since 3.0.0
 */

/**
 * Fires when mod encounters an error
 *
 * @action mod.error
 * @param string $mod_id Mod ID
 * @param string $error_message Error message
 * @param array $context Error context
 * @since 3.0.0
 */

/**
 * ============================================================================
 * TOTAL HOOKS: 50+
 * ============================================================================
 *
 * This reference will be expanded as more hooks are added to the core.
 * Mod developers should check this file regularly for new hook points.
 *
 * For more information, see:
 * - /docs/v2.9-mod-system/HOOK-API.md
 * - /docs/v2.9-mod-system/MOD-DEVELOPMENT.md
 */
