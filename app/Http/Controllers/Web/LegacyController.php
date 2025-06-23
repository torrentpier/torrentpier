<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TorrentPier\Config;

class LegacyController
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function index(Request $request): Response
    {
        return $this->handleController($request, 'index');
    }

    public function ajax(Request $request): Response
    {
        return $this->handleController($request, 'ajax');
    }

    public function dl(Request $request): Response
    {
        return $this->handleController($request, 'dl');
    }

    public function dl_list(Request $request): Response
    {
        return $this->handleController($request, 'dl_list');
    }

    public function feed(Request $request): Response
    {
        return $this->handleController($request, 'feed');
    }

    public function filelist(Request $request): Response
    {
        return $this->handleController($request, 'filelist');
    }

    public function group(Request $request): Response
    {
        return $this->handleController($request, 'group');
    }

    public function group_edit(Request $request): Response
    {
        return $this->handleController($request, 'group_edit');
    }

    public function info(Request $request): Response
    {
        return $this->handleController($request, 'info');
    }

    public function login(Request $request): Response
    {
        return $this->handleController($request, 'login');
    }

    public function memberlist(Request $request): Response
    {
        return $this->handleController($request, 'memberlist');
    }

    public function modcp(Request $request): Response
    {
        return $this->handleController($request, 'modcp');
    }

    public function playback_m3u(Request $request): Response
    {
        return $this->handleController($request, 'playback_m3u');
    }

    public function poll(Request $request): Response
    {
        return $this->handleController($request, 'poll');
    }

    public function posting(Request $request): Response
    {
        return $this->handleController($request, 'posting');
    }

    public function privmsg(Request $request): Response
    {
        return $this->handleController($request, 'privmsg');
    }

    public function profile(Request $request): Response
    {
        return $this->handleController($request, 'profile');
    }

    public function search(Request $request): Response
    {
        return $this->handleController($request, 'search');
    }

    public function terms(Request $request): Response
    {
        return $this->handleController($request, 'terms');
    }

    public function tracker(Request $request): Response
    {
        return $this->handleController($request, 'tracker');
    }

    public function viewforum(Request $request): Response
    {
        return $this->handleController($request, 'viewforum');
    }

    public function viewtopic(Request $request): Response
    {
        return $this->handleController($request, 'viewtopic');
    }


    public function handleController(Request $request, string $controller): Response
    {
        $rootPath = dirname(__DIR__, 4);
        $controllerPath = $rootPath . '/controllers/' . $controller . '.php';

        if (!file_exists($controllerPath)) {
            return new Response(
                "<h1>404 - Not Found</h1><p>Legacy controller '{$controller}' not found</p>",
                404,
                ['Content-Type' => 'text/html']
            );
        }

        // Capture the legacy controller output
        $output = '';
        $originalObLevel = ob_get_level();

        try {
            // Ensure legacy common.php is loaded for legacy controllers
            if (!defined('BB_PATH')) {
                require_once $rootPath . '/common.php';
            }

            ob_start();

            // No need to save/restore superglobals - legacy controllers may modify them intentionally

            // Signal to legacy code that we're running through modern routing
            if (!defined('MODERN_ROUTING')) {
                define('MODERN_ROUTING', true);
            }

            // Import essential legacy globals into local scope
            global $bb_cfg, $config, $user, $template, $datastore, $lang, $userdata, $userinfo, $images,
                   $tracking_topics, $tracking_forums, $theme, $bf, $attach_config, $gen_simple_header,
                   $client_ip, $user_ip, $log_action, $html, $wordCensor, $search_id,
                   $session_id, $items_found, $per_page, $topic_id, $req_topics, $forum_id, $mode,
                   $is_auth, $t_data, $postrow, $group_id, $group_info, $post_id, $folder, $post_info,
                   $tor, $post_data, $privmsg, $forums, $redirect, $attachment, $forum_data, $search_all,
                   $redirect_url, $topic_csv, $poster_id, $emailer, $s_hidden_fields, $opt, $msg, $stats,
                   $page_cfg, $ads, $cat_forums, $last_session_data, $announce_interval, $auth_pages,
                   $lastvisit, $current_time, $excluded_forums_csv, $sphinx, $dl_link_css, $dl_status_css,
                   $upload_dir, $topic_data, $attachments;

            // GPC variables created dynamically via $GLOBALS in tracker.php and search.php
            global $all_words_key, $all_words_val, $active_key, $active_val, $cat_key, $cat_val,
                   $dl_cancel_key, $dl_cancel_val, $dl_compl_key, $dl_compl_val, $dl_down_key, $dl_down_val,
                   $dl_will_key, $dl_will_val, $forum_key, $forum_val, $my_key, $my_val, $new_key, $new_val,
                   $title_match_key, $title_match_val, $order_key, $order_val, $poster_id_key, $poster_id_val,
                   $poster_name_key, $poster_name_val, $user_releases_key, $user_releases_val, $sort_key, $sort_val,
                   $seed_exist_key, $seed_exist_val, $show_author_key, $show_author_val, $show_cat_key, $show_cat_val,
                   $show_forum_key, $show_forum_val, $show_speed_key, $show_speed_val, $s_rg_key, $s_rg_val,
                   $s_not_seen_key, $s_not_seen_val, $time_key, $time_val, $tor_type_key, $tor_type_val,
                   $hash_key, $hash_val, $chars_key, $chars_val, $display_as_key, $display_as_val,
                   $dl_user_id_key, $dl_user_id_val, $my_topics_key, $my_topics_val, $new_topics_key, $new_topics_val,
                   $text_match_key, $text_match_val, $title_only_key, $title_only_val, $topic_key, $topic_val;

            // Include the legacy controller
            // Note: We don't use require_once to allow multiple includes if needed
            include $controllerPath;

            // Get the captured output - make sure we only clean our own buffer
            $output = ob_get_clean();

            // Return the output as HTML response
            return new Response($output, 200, ['Content-Type' => 'text/html']);

        } catch (\Throwable $e) {
            // Clean up any extra output buffers that were started, but preserve original level
            while (ob_get_level() > $originalObLevel) {
                ob_end_clean();
            }

            // Return error response
            $errorHtml = "
                <h1>Legacy Controller Error</h1>
                <p><strong>Controller:</strong> {$controller}</p>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>
            ";

            if (function_exists('dev') && dev()->isDebugEnabled()) {
                $errorHtml .= "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            }

            return new Response($errorHtml, 500, ['Content-Type' => 'text/html']);
        }
    }
}
