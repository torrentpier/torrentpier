<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

require(INC_DIR .'class.sitemap.php');

$map = new sitemap();
$map->create();

if (@file_exists(BB_ROOT. "/sitemap/sitemap.xml"))
{
	$map_link = make_url('/sitemap/sitemap.xml');

	$map->send_url("http://google.com/webmasters/sitemaps/ping?sitemap=", $map_link);
	$map->send_url("http://ping.blogs.yandex.ru/ping?sitemap=", $map_link);
	$map->send_url("http://www.bing.com/ping?sitemap=", $map_link);
	$map->send_url("http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url=", $map_link);
}