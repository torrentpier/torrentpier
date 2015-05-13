<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$map = new sitemap();
$map->create();

if (@file_exists(INT_DATA_DIR . '/sitemap/sitemap.xml'))
{
	$map_link = make_url(INT_DATA_DIR . '/sitemap/sitemap.xml');

	$map->send_url("http://google.com/webmasters/sitemaps/ping?sitemap=", $map_link);
	$map->send_url("http://ping.blogs.yandex.ru/ping?sitemap=", $map_link);
	$map->send_url("http://www.bing.com/ping?sitemap=", $map_link);
	$map->send_url("http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url=", $map_link);
}