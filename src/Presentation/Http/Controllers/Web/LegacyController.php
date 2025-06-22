<?php

declare(strict_types=1);

namespace TorrentPier\Presentation\Http\Controllers\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Config;
use TorrentPier\Infrastructure\Http\ResponseFactory;

class LegacyController
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get the legacy controller name from the URL path
        $path = $request->getUri()->getPath();
        $controller = null;

        // Extract controller name from .php files or without extension
        if (preg_match('/\/([^\/]+)\.php$/', $path, $matches)) {
            // URL like /terms.php
            $controller = $matches[1];
        } elseif (preg_match('/\/([^\/]+)$/', $path, $matches)) {
            // URL like /terms (without extension)
            $controller = $matches[1];
        } elseif ($path === '/') {
            // Root path should serve index.php
            $controller = 'index';
        }

        if (!$controller) {
            return ResponseFactory::html('Legacy controller not specified', 404);
        }

        $rootPath = dirname(__DIR__, 5);
        $controllerPath = $rootPath . '/controllers/' . $controller . '.php';

        if (!file_exists($controllerPath)) {
            return ResponseFactory::html(
                "<h1>404 - Not Found</h1><p>Legacy controller '{$controller}' not found</p>",
                404
            );
        }

        // Capture the legacy controller output
        $output = '';
        $originalObLevel = ob_get_level();

        try {
            ob_start();

            // Save current globals state (in case they're modified)
            $originalServer = $_SERVER;
            $originalGet = $_GET;
            $originalPost = $_POST;

            // Import essential legacy globals into local scope
            global $bb_cfg, $config, $user, $template, $datastore, $lang, $userdata, $userinfo, $images,
                   $tracking_topics, $tracking_forums, $theme, $bf, $attach_config, $gen_simple_header,
                   $client_ip, $user_ip, $log_action, $html, $wordCensor, $forum_key, $search_id,
                   $session_id, $items_found, $per_page, $topic_id, $req_topics, $forum_id, $mode,
                   $is_auth, $t_data, $postrow, $group_id, $group_info, $post_id, $folder, $post_info,
                   $tor, $post_data, $privmsg, $forums, $redirect, $attachment, $forum_data, $search_all,
                   $redirect_url, $topic_csv, $poster_id, $emailer, $s_hidden_fields, $opt, $msg, $stats,
                   $poster_id_key, $text_match_key, $poster_name_key, $my_topics_key, $my_topics_val, $title_only_key,
                   $all_words_key, $dl_cancel_key, $dl_cancel_val, $dl_compl_key, $dl_compl_val, $dl_down_key,  $dl_down_val,
                   $dl_will_key, $dl_will_val, $new_key, $new_val, $new_topics_key, $new_topics_val, $time_key, $time_val,
                   $order_key, $order_val, $sort_key, $sort_val, $display_as_key, $display_as_val, $topic_key, $topic_val,
                   $poster_id_val, $poster_id_key, $forum_val, $forum_key;

            // Signal to legacy code that we're running through modern routing
            if (!defined('MODERN_ROUTING')) {
                define('MODERN_ROUTING', true);
            }

            // Include the legacy controller
            // Note: We don't use require_once to allow multiple includes if needed
            include $controllerPath;

            // Get the captured output - make sure we only clean our own buffer
            $output = ob_get_clean();

            // Restore globals if needed
            $_SERVER = $originalServer;
            $_GET = $originalGet;
            $_POST = $originalPost;

            // Return the output as HTML response
            return ResponseFactory::html($output);

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

            return ResponseFactory::html($errorHtml, 500);
        }
    }
}
