<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg;

$mode = (string) $this->request['mode'];
$map  = new sitemap();
$html = '';

switch ($mode)
{
	case 'create':
		$map->create();
		if (@file_exists(BB_ROOT. "/sitemap/sitemap.xml"))
		{
			$html .= 'Файл sitemap создан: <b>'.bb_date(TIMENOW, $bb_cfg['post_date_format']).'</b> и доступен по адресу: <a href="'.make_url('/sitemap/sitemap.xml').'" target="_blank">'.make_url('/sitemap/sitemap.xml').'</a>';
		} else {
			$html .= 'Файл sitemap еще не создан';
		}
	break;

	case 'search_update':
		if (!@file_exists(BB_ROOT. "/sitemap/sitemap.xml"))
		{
			$map->create();
		}

		$map_link = make_url('/sitemap/sitemap.xml');

		if (strpos($map->send_url("http://google.com/webmasters/sitemaps/ping?sitemap=", $map_link), "successfully added") !== false) {
			$html .= '<br />Уведомление поисковой системы Google: <font style="color: green;">отправка завершена</font>';
		} else {
			$html .= '<br />Уведомление поисковой системы Google: <font style="color: red;">ошибка отправки</font> URL: <a href="http://google.com/webmasters/sitemaps/ping?sitemap='.urlencode($map_link).'" target="_blank">http://google.com/webmasters/sitemaps/ping?sitemap='.$map_link.'</a>';
		}

		if (strpos($map->send_url("http://ping.blogs.yandex.ru/ping?sitemap=", $map_link), "OK") !== false) {
			$html .= '<br />Уведомление поисковой системы Yandex: <font style="color: green;">отправка завершена</font>';
		} else {
			$html .= '<br />Уведомление поисковой системы Yandex: <font style="color: red;">ошибка отправки</font> URL: <a href="http://ping.blogs.yandex.ru/ping?sitemap='.urlencode($map_link).'" target="_blank">http://ping.blogs.yandex.ru/ping?sitemap='.$map_link.'</a>';
		}

		if ($map->send_url("http://www.bing.com/webmaster/ping.aspx?siteMap=", $map_link)) {
			$html .= '<br />Уведомление поисковой системы Bing: <font style="color: green;">отправка завершена</font>';
		} else {
			$html .= '<br />Уведомление поисковой системы Bing: <font style="color: red;">ошибка отправки</font>';
		}

		if (strpos ($map->send_url("http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url=", $map_link), "Thanks for the ping") !== false) {
			$html .= '<br />Уведомление поисковой системы Weblogs: <font style="color: green;">отправка завершена</font>';
		} else {
			$html .= '<br />Уведомление поисковой системы Weblogs: <font style="color: red;">ошибка отправки</font> URL: <a href="http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url='.urlencode($map_link).'" target="_blank">http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url='.$map_link.'</a>';
		}

		if ($map->send_url("http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=", $map_link)) {
			$html .= '<br />Уведомление поисковой системы Yahoo: <font style="color: green;">отправка завершена</font>';
		} else {
			$html .= '<br />Уведомление поисковой системы Yahoo: <font style="color: red;">ошибка отправки</font> URL: <a href="http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap='.urlencode($map_link).'" target="_blank">http://rpc.weblogs.com/pingSiteForm?name=InfraBlog&url='.$map_link.'</a>';
		}
	break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;