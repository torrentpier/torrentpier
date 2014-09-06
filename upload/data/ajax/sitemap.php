<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang;

$mode = (string) $this->request['mode'];
$map  = new sitemap();
$html = '';

switch ($mode)
{
	case 'create':
		$map->create();
		if (@file_exists(UPLOAD_DIR. "sitemap.xml")) {
			$html .= $lang['SITEMAP_CREATED'].': <b>'.bb_date(TIMENOW, $bb_cfg['post_date_format']).'</b> '.$lang['SITEMAP_AVAILABLE'].': <a href="'.make_url('/sitemap/sitemap.xml').'" target="_blank">'.make_url('/sitemap/sitemap.xml').'</a>';
		} else {
			$html .= $lang['SITEMAP_NOT_CREATED'];
		}
	break;

	case 'search_update':
		if (!@file_exists(UPLOAD_DIR. "sitemap.xml")) $map->create();

		$map_link = make_url(UPLOAD_DIR.'sitemap.xml');

		if (strpos($map->send_url("http://google.com/webmasters/sitemaps/ping?sitemap=", $map_link), "successfully added") !== false) {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Google: <font style="color: green;">'.$lang['SITEMAP_SENT'].'</font>';
		} else {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Google: <font style="color: red;">'.$lang['SITEMAP_ERROR'].'</font> URL: <a href="http://google.com/webmasters/sitemaps/ping?sitemap='.urlencode($map_link).'" target="_blank">http://google.com/webmasters/sitemaps/ping?sitemap='.$map_link.'</a>';
		}

		if (strpos($map->send_url("http://ping.blogs.yandex.ru/ping?sitemap=", $map_link), "OK") !== false) {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Yandex: <font style="color: green;">'.$lang['SITEMAP_SENT'].'</font>';
		} else {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Yandex: <font style="color: red;">'.$lang['SITEMAP_ERROR'].'</font> URL: <a href="http://ping.blogs.yandex.ru/ping?sitemap='.urlencode($map_link).'" target="_blank">http://ping.blogs.yandex.ru/ping?sitemap='.$map_link.'</a>';
		}

		if ($map->send_url("http://www.bing.com/ping?sitemap=", $map_link)) {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Bing: <font style="color: green;">'.$lang['SITEMAP_SENT'].'</font>';
		} else {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Bing: <font style="color: red;">'.$lang['SITEMAP_ERROR'].'</font> URL: <a href="http://www.bing.com/ping?sitemap='.urlencode($map_link).'" target="_blank">http://www.bing.com/ping?sitemap='.$map_link.'</a>';
		}

		if (strpos($map->send_url("http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url=", $map_link), "Thanks for the ping") !== false) {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Weblogs: <font style="color: green;">'.$lang['SITEMAP_SENT'].'</font>';
		} else {
			$html .= '<br />'.$lang['SITEMAP_NOTIFY_SEARCH'].' Weblogs: <font style="color: red;">'.$lang['SITEMAP_ERROR'].'</font> URL: <a href="http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url='.urlencode($map_link).'" target="_blank">http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url='.$map_link.'</a>';
		}
	break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;