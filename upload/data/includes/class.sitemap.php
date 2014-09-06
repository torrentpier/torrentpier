<?php

class sitemap
{
	var $home           = '';
	var $limit          = 0;
	var $topic_priority = '0.5';
	var $stat_priority  = '0.5';
	var $priority       = '0.6';
	var $cat_priority   = '0.7';

	function sitemap () {
		global $bb_cfg;
		$this->home = 'http://'.$bb_cfg['server_name'].'/';
	}

	function build_map () {
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_static();
		$map .= $this->get_forum();
		$map .= $this->get_topic();
		$map .= "</urlset>";

		return $map;
	}

	function build_index ($count) {
		$lm = date('c');
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= "<sitemap>\n<loc>{$this->home}sitemap/sitemap1.xml</loc>\n<lastmod>{$lm}</lastmod>\n</sitemap>\n";
		for ($i = 0; $i < $count; $i++) {
			$t = $i + 2;
			$map .= "<sitemap>\n<loc>{$this->home}sitemap/sitemap{$t}.xml</loc>\n<lastmod>{$lm}</lastmod>\n</sitemap>\n";
		}
		$map .= "</sitemapindex>";

		return $map;
	}

	function build_stat () {
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_static();
		$map .= $this->get_forum();
		$map .= "</urlset>";

		return $map;
	}

	function build_map_topic ($n) {
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_topic($n);
		$map .= "</urlset>";

		return $map;
	}

	function get_forum () {
		global $datastore;

		$this->priority = $this->cat_priority;
		$xml = '';
		$lm = date('c');

		if (!$forums = $datastore->get('cat_forums')) {
			$datastore->update('cat_forums');
			$forums = $datastore->get('cat_forums');
		}

		$not_forums_id = $forums['not_auth_forums']['guest_view'];
		$ignore_forum_sql = ($not_forums_id) ? "WHERE forum_id NOT IN($not_forums_id)" : '';

		$sql = DB()->sql_query("SELECT forum_id, forum_topics, forum_parent, forum_name FROM " . BB_FORUMS . " " . $ignore_forum_sql . " ORDER BY forum_id ASC");

		while ($row = DB()->sql_fetchrow($sql)) {
			if (function_exists('seo_url')) $loc = $this->home . seo_url(FORUM_URL . $row['forum_id'], $row['forum_name']);
			else $loc = $this->home . FORUM_URL . $row['forum_id'];
			$xml .= $this->get_xml($loc, $lm);
		}

		return $xml;
	}

	function get_topic ($page = false) {
		global $datastore;

		$xml = '';
		$this->priority = $this->topic_priority;

		if ($page) {
			$page = $page - 1;
			$page = $page * 40000;
			$this->limit = " LIMIT {$page},40000";
		} else {
			if ($this->limit < 1) $this->limit = false;
			if ($this->limit) {
				$this->limit = " LIMIT 0," . $this->limit;
			} else {
				$this->limit = '';
			}
		}

		if (!$forums = $datastore->get('cat_forums')) {
			$datastore->update('cat_forums');
			$forums = $datastore->get('cat_forums');
		}

		$not_forums_id = $forums['not_auth_forums']['guest_view'];
		$ignore_forum_sql = ($not_forums_id) ? "WHERE forum_id NOT IN($not_forums_id)" : '';

		$sql = DB()->sql_query("SELECT topic_id, topic_title, topic_time FROM " . BB_TOPICS . " " . $ignore_forum_sql . " ORDER BY topic_time ASC" . $this->limit);

		while ($row = DB()->sql_fetchrow($sql)) {
			if (function_exists('seo_url')) $loc = $this->home . seo_url(TOPIC_URL . $row['topic_id'], $row['topic_title']);
			else $loc = $this->home . TOPIC_URL . $row['topic_id'];
			$xml .= $this->get_xml($loc, date('c', $row['topic_time']));
		}

		return $xml;
	}

	function get_static () {
		global $bb_cfg;

		$xml = '';
		$lm = date('c');
		$this->priority = $this->stat_priority;

		if (isset($bb_cfg['static_sitemap'])) {
			$static_url = preg_replace("/\s/", '', $bb_cfg['static_sitemap']); //вырезаем переносы строк
			preg_match_all('#(https?://[\w-]+[\.\w-]+/((?!https?://)[\w- ./?%&=])+)#', $static_url, $out);

			$static_url = count($out['0']);
			if ($static_url > 0) {
				foreach ($out['0'] as $url) {
					$loc = $url;
					$xml .= $this->get_xml($loc, $lm);
				}
			}
		}

		return $xml;
	}

	function get_xml ($loc, $lm) {
		$xml = "\t<url>\n";
		$xml .= "\t\t<loc>$loc</loc>\n";
		$xml .= "\t\t<lastmod>$lm</lastmod>\n";
		$xml .= "\t\t<priority>" . $this->priority . "</priority>\n";
		$xml .= "\t</url>\n";

		return $xml;
	}

	function send_url ($url, $map) {
		$data = false;
		$file = $url.urlencode($map);

		if (function_exists('curl_init')) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $file);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		} else {
			return @file_get_contents($file);
		}
	}

	function create () {
		$row = DB()->fetch_row("SELECT COUNT(*) AS count FROM " . BB_TOPICS);

		if (!$this->limit) $this->limit = $row['count'];
		if ($this->limit > 40000) {
			$pages_count = @ceil($row['count'] / 40000);

			$sitemap = $this->build_index($pages_count);
			$handler = fopen(SITEMAP_DIR. "sitemap.xml", "wb+");
			fwrite($handler, $sitemap);
			fclose($handler);
			@chmod(SITEMAP_DIR. "sitemap.xml", 0666);

			$sitemap = $this->build_stat();
			$handler = fopen(SITEMAP_DIR. "sitemap1.xml", "wb+");
			fwrite($handler, $sitemap);
			fclose($handler);
			@chmod(SITEMAP_DIR. "sitemap.xml", 0666);

			for ($i = 0; $i < $pages_count; $i++) {
				$t = $i + 2;
				$n = $i + 1;

				$sitemap = $this->build_map_topic($n);
				$handler = fopen(SITEMAP_DIR. "sitemap{$t}.xml", "wb+");
				fwrite($handler, $sitemap);
				fclose($handler);
				@chmod(SITEMAP_DIR. "sitemap{$t}.xml", 0666);
			}
		} else {
			$sitemap = $this->build_map();
			$handler = fopen(SITEMAP_DIR. "sitemap.xml", "wb+");
			fwrite($handler, $sitemap);
			fclose($handler);
			@chmod(SITEMAP_DIR. "sitemap.xml", 0666);
		}

		$params['sitemap_time'] = TIMENOW;
		bb_update_config($params);
	}
}