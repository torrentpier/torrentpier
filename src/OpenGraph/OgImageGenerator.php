<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\OpenGraph;

use Fractal512\OpenGraphImage\OpenGraphImage;

/**
 * Generates Open Graph images for topics, forums, and user profiles
 */
class OgImageGenerator
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    private array $ogConfig;

    public function __construct()
    {
        $this->ogConfig = config()->get('og_image') ?? [];
    }

    /**
     * Generate OG image for a topic
     */
    public function generateForTopic(int $topicId): ?string
    {
        $topic = $this->getTopicData($topicId);
        if (!$topic) {
            return null;
        }

        $stats = sprintf('%d replies', (int)$topic['topic_replies']);

        return $this->generate(
            title: $topic['topic_title'],
            subtitle: $topic['forum_name'],
            stats: $stats
        );
    }

    /**
     * Generate OG image for a forum
     */
    public function generateForForum(int $forumId): ?string
    {
        $forum = $this->getForumData($forumId);
        if (!$forum) {
            return null;
        }

        $stats = sprintf('%d topics • %d posts',
            (int)$forum['forum_topics'],
            (int)$forum['forum_posts']
        );

        return $this->generate(
            title: $forum['forum_name'],
            subtitle: $forum['forum_desc'] ?: config()->get('sitename', ''),
            stats: $stats
        );
    }

    /**
     * Generate OG image for a user profile
     */
    public function generateForUser(int $userId): ?string
    {
        $user = $this->getUserData($userId);
        if (!$user) {
            return null;
        }

        $stats = sprintf('%s posts', number_format((int)$user['user_posts']));

        return $this->generate(
            title: $user['username'],
            subtitle: config()->get('sitename', 'TorrentPier'),
            stats: $stats
        );
    }

    /**
     * Generate default/fallback OG image
     */
    public function generateDefault(): string
    {
        return $this->generate(
            title: config()->get('sitename', 'TorrentPier'),
            subtitle: config()->get('site_desc', '')
        );
    }

    /**
     * Create gradient background
     */
    private function createBackground(): string
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;

        $image = imagecreatetruecolor($width, $height);

        // White/light gray gradient
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $gray = (int)(250 - (10 * $ratio));
            $color = imagecolorallocate($image, $gray, $gray + 2, $gray + 4);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Left accent
        $accent = imagecolorallocate($image, 218, 54, 51);
        imagefilledrectangle($image, 0, 0, 5, $height, $accent);

        $tempFile = sys_get_temp_dir() . '/og_bg_' . uniqid() . '.png';
        imagepng($image, $tempFile);

        return $tempFile;
    }

    /**
     * Core image generation method
     */
    private function generate(
        string $title,
        string $subtitle = '',
        string $stats = ''
    ): string {
        $backgroundPath = $this->createBackground();

        // Build display text with visual hierarchy using line breaks
        $displayText = $this->truncateText($title, 60);

        if ($subtitle) {
            $displayText .= "\n\n" . $this->truncateText($subtitle, 80);
        }

        if ($stats) {
            $displayText .= "\n\n" . $stats;
        }

        $config = [
            'driver' => 'gd',
            'image_width' => self::WIDTH,
            'image_height' => self::HEIGHT,
            'background_path' => $backgroundPath,
            'background_fill' => true,
            'overlay_alpha' => 0,
            'text_color' => '#24292f',
            'text_font_size' => 42,
            'text_pos_x' => 80,
            'text_pos_y' => 200,
            'text_horizontal_align' => 'left',
            'text_vertical_align' => 'top',
            'text_wrap_width' => 900,
            'text_line_height' => 1.6,
            'logo_path' => $this->ogConfig['logo_path'] ?? null,
            'logo_position' => 'bottom-left',
            'logo_pos_x' => 80,
            'logo_pos_y' => 80,
        ];

        $ogImage = new OpenGraphImage($config);

        $tempFile = sys_get_temp_dir() . '/og_' . uniqid() . '.png';
        $ogImage->make($displayText)->save($tempFile);

        $imageData = file_get_contents($tempFile);

        @unlink($tempFile);
        @unlink($backgroundPath);

        return $imageData;
    }

    /**
     * Get topic data with forum name
     */
    private function getTopicData(int $topicId): ?array
    {
        $topic = DB()->table(BB_TOPICS)->get($topicId);
        if (!$topic) {
            return null;
        }

        $forum = $topic->ref(BB_FORUMS, 'forum_id');

        return [
            'topic_id' => $topic->topic_id,
            'topic_title' => $topic->topic_title,
            'topic_replies' => $topic->topic_replies,
            'topic_time' => $topic->topic_time,
            'forum_name' => $forum?->forum_name ?? '',
        ];
    }

    /**
     * Get forum data
     */
    private function getForumData(int $forumId): ?array
    {
        $forum = DB()->table(BB_FORUMS)->get($forumId);
        if (!$forum) {
            return null;
        }

        return [
            'forum_id' => $forum->forum_id,
            'forum_name' => $forum->forum_name,
            'forum_desc' => $forum->forum_desc,
            'forum_topics' => $forum->forum_topics,
            'forum_posts' => $forum->forum_posts,
        ];
    }

    /**
     * Get user data
     */
    private function getUserData(int $userId): ?array
    {
        $user = DB()->table(BB_USERS)->get($userId);
        if (!$user) {
            return null;
        }

        return [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'user_posts' => $user->user_posts,
        ];
    }

    /**
     * Truncate text preserving words
     */
    private function truncateText(string $text, int $maxLength): string
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = strip_tags($text);
        $text = trim($text);

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $text = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($text, ' ');

        if ($lastSpace !== false && $lastSpace > $maxLength * 0.7) {
            $text = mb_substr($text, 0, $lastSpace);
        }

        return $text . '...';
    }
}
