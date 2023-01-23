<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use samdark\sitemap\Sitemap as STM;
use samdark\sitemap\Index as IDX;

/**
 * Class Sitemap
 * @package TorrentPier\Legacy
 */
class Sitemap
{
    /**
     * Получение списка URL разделов
     *
     * @return array
     */
    private function getForumUrls()
    {
        global $datastore;

        $forumUrls = [];

        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        $not_forums_id = $forums['not_auth_forums']['guest_view'];
        $ignore_forum_sql = $not_forums_id ? "WHERE forum_id NOT IN($not_forums_id)" : '';

        $sql = DB()->sql_query("SELECT forum_id, forum_name FROM " . BB_FORUMS . " " . $ignore_forum_sql . " ORDER BY forum_id ASC");

        while ($row = DB()->sql_fetchrow($sql)) {
            $forumUrls[] = [
                'url' => FORUM_URL . $row['forum_id'],
            ];
        }

        return $forumUrls;
    }

    /**
     * Получение списка URL тем
     *
     * @return array
     */
    private function getTopicUrls()
    {
        global $datastore;

        $topicUrls = [];

        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        $not_forums_id = $forums['not_auth_forums']['guest_view'];
        $ignore_forum_sql = $not_forums_id ? "WHERE forum_id NOT IN($not_forums_id)" : '';

        $sql = DB()->sql_query("SELECT topic_id, topic_title, topic_time FROM " . BB_TOPICS . " " . $ignore_forum_sql . " ORDER BY topic_time ASC");

        while ($row = DB()->sql_fetchrow($sql)) {
            $topicUrls[] = [
                'url' => TOPIC_URL . $row['topic_id'],
                'time' => $row['topic_time'],
            ];
        }

        return $topicUrls;
    }

    /**
     * Получение списка статичных URL
     *
     * @return array
     */
    private function getStaticUrls()
    {
        global $bb_cfg;

        $staticUrls = [];

        if (isset($bb_cfg['static_sitemap'])) {
            /** @var array $urls разбиваем строку по переносам */
            $urls = explode("\n", $bb_cfg['static_sitemap']);
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
     * @throws \InvalidArgumentException
     */
    private function buildDynamicSitemap()
    {
        $sitemap = new STM(SITEMAP_DIR . '/sitemap_dynamic.xml');

        foreach ($this->getForumUrls() as $forum) {
            $sitemap->addItem(make_url($forum['url']), time(), STM::HOURLY, 0.7);
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
     * @throws \InvalidArgumentException
     */
    private function buildStaticSitemap()
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
     * @throws \InvalidArgumentException
     */
    public function createSitemap()
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


    /**
     * Отправка карты сайта на указанный URL
     *
     * @param $url
     * @param $map
     *
     * @return string
     */
    public function sendSitemap($url, $map)
    {
        $file = $url . urlencode($map);

        if (\function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        }

        return file_get_contents($file);
    }
}
