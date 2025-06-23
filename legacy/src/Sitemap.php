<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use InvalidArgumentException;

use samdark\sitemap\Sitemap as STM;
use samdark\sitemap\Index as IDX;

/**
 * Class Sitemap
 * @package TorrentPier
 */
class Sitemap
{
    /**
     * Получение списка URL разделов
     *
     * @return array
     */
    private function getForumUrls(): array
    {
        global $datastore;

        $forumUrls = [];

        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        $not_forums_id = $forums['not_auth_forums']['guest_view'];
        $ignore_forum_sql = $not_forums_id ? "WHERE f.forum_id NOT IN($not_forums_id)" : '';

        $sql = DB()->sql_query("
            SELECT
                f.forum_id,
                f.forum_name,
                MAX(t.topic_time) AS last_topic_time
            FROM " . BB_FORUMS . " f
            LEFT JOIN " . BB_TOPICS . " t ON f.forum_id = t.forum_id
            " . $ignore_forum_sql . "
            GROUP BY f.forum_id, f.forum_name
            ORDER BY f.forum_id ASC
        ");

        while ($row = DB()->sql_fetchrow($sql)) {
            $forumUrls[] = [
                'url' => FORUM_URL . $row['forum_id'],
                'time' => $row['last_topic_time']
            ];
        }

        return $forumUrls;
    }

    /**
     * Получение списка URL тем
     *
     * @return array
     */
    private function getTopicUrls(): array
    {
        global $datastore;

        $topicUrls = [];

        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        $not_forums_id = $forums['not_auth_forums']['guest_view'];
        $ignore_forum_sql = $not_forums_id ? "WHERE forum_id NOT IN($not_forums_id)" : '';

        $sql = DB()->sql_query("SELECT topic_id, topic_title, topic_last_post_time FROM " . BB_TOPICS . " " . $ignore_forum_sql . " ORDER BY topic_last_post_time ASC");

        while ($row = DB()->sql_fetchrow($sql)) {
            $topicUrls[] = [
                'url' => TOPIC_URL . $row['topic_id'],
                'time' => $row['topic_last_post_time'],
            ];
        }

        return $topicUrls;
    }

    /**
     * Получение списка статичных URL
     *
     * @return array
     */
    private function getStaticUrls(): array
    {
        $staticUrls = [];

        if (config()->has('static_sitemap')) {
            /** @var array $urls разбиваем строку по переносам */
            $urls = explode("\n", config()->get('static_sitemap'));
            foreach ($urls as $url) {
                /** @var string $url проверяем что адрес валиден и с указанными протоколом */
                if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
                    $staticUrls[] = [
                        'url' => trim($url),
                    ];
                }
            }
        }

        return $staticUrls;
    }

    /**
     * Генерация карты сайта (динамичные URL)
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function buildDynamicSitemap(): array
    {
        $sitemap = new STM(SITEMAP_DIR . '/sitemap_dynamic.xml');

        foreach ($this->getForumUrls() as $forum) {
            $sitemap->addItem(make_url($forum['url']), $forum['time'], STM::HOURLY, 0.7);
        }

        foreach ($this->getTopicUrls() as $topic) {
            $sitemap->addItem(make_url($topic['url']), $topic['time'], STM::DAILY, 0.5);
        }

        $sitemap->write();

        return $sitemap->getSitemapUrls(make_url('/sitemap') . '/');
    }

    /**
     * Генерация карты сайта (статичные URL)
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function buildStaticSitemap(): array
    {
        $staticSitemap = new STM(SITEMAP_DIR . '/sitemap_static.xml');

        foreach ($this->getStaticUrls() as $url) {
            $staticSitemap->addItem($url['url'], time(), STM::WEEKLY, 0.5);
        }

        $staticSitemap->write();

        return $staticSitemap->getSitemapUrls(make_url('/sitemap') . '/');
    }

    /**
     * Генерация карты сайта
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function createSitemap(): bool
    {
        $index = new IDX(SITEMAP_DIR . '/sitemap.xml');

        foreach ($this->buildDynamicSitemap() as $sitemapUrl) {
            $index->addSitemap($sitemapUrl);
        }

        foreach ($this->buildStaticSitemap() as $sitemapUrl) {
            $index->addSitemap($sitemapUrl);
        }

        $index->write();

        /** обновляем время генерации карты сайта в конфиге */
        bb_update_config(['sitemap_time' => TIMENOW]);

        return true;
    }
}
