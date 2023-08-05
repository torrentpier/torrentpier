<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

	    function rustorka($text, $mode=false)
		{


			if($mode == 'title')
			{
				preg_match_all ("#<h1 class=\"bigtitle\"><a href=\".*?\">([\s\S]*?)</a></h1>#", $text, $source, PREG_SET_ORDER);
			    $text = $source[0][1];
				$text = str_replace('<wbr>', '', $text);
		    }
		    elseif($mode == 'torrent')
		    {
				//Торрент не найден - если указана неверная ссылка на скачивание Torrent
		    	preg_match_all ("#<th colspan=\"3\" class=\"genmed\">(.*?).torrent</th>[\s\S]*?<a href=\"download.php\?id=(.*?)\" #", $text, $source, PREG_SET_ORDER);
			    $text = $source[0];
		    }
		    else
		    {
				
				$pos = strpos($text, '<div class="post_body"');
				$text = substr($text, $pos);
				$pos = strpos($text, '<div class="spacer_8"></div>');
				$text = substr($text, 0, $pos);
                $text = preg_replace('/<div class="post_body">/', '', $text);
				
                $text = str_replace('<wbr>', '', $text);
				$text = preg_replace('/<a href="http:\/\/rustorka.com\/forum\/search.*?nm=.*?" class="postLink">(.*?)<\/a>/', '$1', $text);


				$text = preg_replace('/<img class="smile" src=".*?" align="absmiddle" border="0" \/>/', '', $text);
                $text = str_replace('<div class="clear"></div>', '', $text);
                $text = preg_replace('/<!--\/.*?-->/', '', $text);
                $text = str_replace('<div class="spoiler-wrap">', '', $text);
                $text = str_replace('<div class="code_wrap">', '', $text);
                $text = str_replace('<div class="spoiler-body"></span></span></span></span>', '<span class="spoiler-body1">', $text);

				$text = str_replace('<hr />', "\n[hr]\n", $text);
                $text = preg_replace('/<div class="postImg-wrap" style=".*?" align="center"><img src="([^<]*?)" id="postImgAligned" class="postImg" alt="pic" \/><\/div>/', "[align=center][img]\\1[/img][/align]\n", $text);
				$text = preg_replace('/<div class="postImg-wrap" style=".*?" align="([^<]*?)"><img src="([^<]*?)" id="postImgAligned" class="postImg" alt="pic" \/><\/div>/', "[img=left]\\2[/img]\n", $text);
				$text = preg_replace('/<img src="([^<]*?)" id="postImg" class="postImg" align="absmiddle" hspace="0" vspace="4" alt="pic" \/>/', '[img]$1[/img]', $text);
				$text = preg_replace('/<a href="([^<]*?)" rel=".*?" class="zoom"><img src="[^<]*?".*?<(?=\/)\/a>/si', "[thumbnails]$1[/thumbnails]", $text);
				$text = preg_replace('/<a href="([^<]*?)" target="_blank" \/><img.*?src="kinopoisk.php\?id=.*?".*?><\/a>/', "[kp]$1[/kp]", $text);

				$text = preg_replace('/<iframe width=".*?" height=".*?" src=\"([^<]*?)\" frameborder="0" allowfullscreen><\/iframe>/', '[align=center][youtube]$1[/youtube][/align]', $text);
				$text = str_replace('<b>', "[b]", $text);
				$text = str_replace('</b>', "[/b]", $text);

				$text = str_replace('<ul>', '[list]', $text);
				$text = str_replace('</ul>', '[/list]', $text);
				$text = str_replace('<li>', "\n[*]", $text);
				$text = str_replace('</li>', '', $text);
				$text = str_replace('<br />', "\n", $text);
				$text = str_replace('<br clear="all" />', "\n[br]\n", $text);
				$text = str_replace('<div></div>', "\n", $text);
				$text = preg_replace('/<div class="code_head">.*?<script type="text\/javascript">.*?<\/script>.*?<\/div>/', "", $text);
				$text = preg_replace('/<a href="\/cdn-cgi\/l\/email-protection" class="__cf_email__" data-cfemail="[\s\S]*?">[\s\S]*?<\/a>/', "", $text);
				$text = str_replace('<div', '<span', $text);
                $text = str_replace('</div>', '</span>', $text);
                $text = str_replace('<a', '<span', $text);
                $text = str_replace('</a>', '</span>', $text);
                $text = str_replace('&#039;', "'", $text);
				$text = str_replace('&nbsp;', ' ', $text);
				$text = str_replace('&gt;', '>', $text);
				$text = str_replace('&lt;', '<', $text);

				$text = preg_replace_callback('/<span href="http.*?rustorka.com\/forum\/viewtopic.*?" class="postLink">(.*?)<\/span>/', function ($v)
				{ 
				$text_data = $v[1];
				$text_url = strip_tags($text_data);
					return '[url=http://crackstatus.net/tracker.php?' . http_build_query(['nm' => $text_url]) . ']' . $text_data . '[/url]';
				}, 
				$text);
				
				for ($i=0; $i<=20; $i++)
				{
					$text = preg_replace('/<span style="font-weight: bold;">([^<]*?)<(?=\/)\/span>/', '[b]$1[/b]', $text);
					$text = preg_replace('/<span style="text-decoration: underline;">([^<]*?)<(?=\/)\/span>/', '[u]$1[/u]', $text);
					$text = preg_replace('/<span style="text-shadow:  1px 1px 3px [^<]*?">([^<]*?)<(?=\/)\/span>/', '$1', $text);

					$text = preg_replace('/<span style="font-style: italic;">([^<]*?)<(?=\/)\/span>/', '[i]$1[/i]', $text);
					$text = preg_replace('/<span style="font-size: ([^<]*?)px; line-height: normal;">([^<]*?)<(?=\/)\/span>/', "[size=\\1]\\2[/size]", $text);
					$text = preg_replace('/<span style="font-family: ([^<]*?)">([^<]*?)<(?=\/)\/span>/', "[font=\"\\1\"]\\2[/font]", $text);
					$text = preg_replace('#<param name="movie" value="(.*?)"></param>#', "[align=center][youtube]$1[/youtube][/align]", $text);
					$text = preg_replace('/<span style="color: ([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[color=$1]$2[/color]', $text);

					$text = preg_replace('/http:(.*?)fastpic.ru/', "https:$1fastpic.ru/", $text);
					$text = preg_replace('/http:(.*?)imageban.ru/', "https:$1imageban.ru/", $text);
					$text = preg_replace('/http:(.*?)youpic.su/', "https:$1youpic.su/", $text);
					$text = preg_replace('/http:(.*?)lostpic.net/', "https:$1lostpic.net/", $text);
					$text = preg_replace('/http:(.*?)radikal.ru/', "https:$1radikal.ru/", $text);
					$text = str_replace('http://img-fotki.yandex.ru', 'https://img-fotki.yandex.ru', $text);
					$text = preg_replace('/http:(.*?)kinopoisk.ru/', "https:$1kinopoisk.ru", $text);
					$text = preg_replace('/<span href="([^<]*?)" class="postLink">([^<]*?)<(?=\/)\/span>/', '[url=$1]$2[/url]', $text);

					$text = preg_replace('/<p class="q_head"><b>.*?<\/b><\/p>[\s\S]*?<div class="q">([^<]*?)<(?=\/)\/div><!--\/q-->/', "[quote]\n\\1\n[/quote]", $text);  
					$text = preg_replace('/<p class="q_head"><b>(.*?)<\/b>.*?<\/p>[\s\S]*?<div class="q">([^<]*?)<(?=\/)\/div><!--\/q-->/', "[quote=\"\\1\"]\n\\2\n[/quote]", $text);
					$text = preg_replace('/<span class="code">([\s\S]*?)<(?=\/)\/span>/', "[code]\n\\1\n[/code]", $text);
					$text = preg_replace('/<span class="spoiler-head folded clickable nowrap">([^<]*?)<(?=\/)\/span>.*?<span class="spoiler-body">([^<]*?)<(?=\/)\/span>([^<]*?)<(?=\/)\/span>/', "\n[spoiler=\"\\1\"]\n\\2\n[/spoiler]\n", $text);
					$text = preg_replace('/<span class="spoiler-head folded clickable nowrap">([^<]*?)<(?=\/)\/span>.*?<span class="spoiler-body1">([\s\S]*?)<(?=\/)\/span>([^<]*?)<(?=\/)\/span>([^<]*?)<(?=\/)\/span>/', "\n[spoiler=\"\\1\"]\n\\2\n[/spoiler]\n", $text);
					$text = preg_replace('/<span style="text-align: ([^<]*?);">([\s\S]*?)<(?=\/)\/span>/', "[align=\\1]\n\\2\n[/align]", $text);
					$text = preg_replace('#\[url=http.*?imdb.com/title/(\w+\d+)/].*?\[\/url\]#', "[imdb]https://www.imdb.com/title/$1[/imdb]", $text);
					$text = preg_replace('#\[url=http.*?kinopoisk.ru/film/.*?-[0-9]{4}-(\d+)/].*?\[\/url\]#', "[kp]https://www.kinopoisk.ru/film/$1[/kp]", $text);
					$text = preg_replace('#\[url=http.*?kinopoisk.ru/level/.*?/film/(\d+)/].*?\[\/url\]#', "[kp]https://www.kinopoisk.ru/film/$1[/kp]", $text);
					$text = preg_replace('#\[url=http.*?kinopoisk.ru/film/(\d+)/].*?\[\/url\]#', "[kp]https://www.kinopoisk.ru/film/$1[/kp]", $text);
					$text = preg_replace('#\[url=http.*?kinopoisk.ru/film/(\d+)].*?\[\/url\]#', "[kp]https://www.kinopoisk.ru/film/$1[/kp]", $text);
					$text = preg_replace('/http:(.*?)kinopoisk.ru/', "https:$1kinopoisk.ru", $text);
					$text = preg_replace('/\[url=.*?multi-up.com.*?\].*?\[\/url\]/', "", $text);
			    }

				$text = strip_tags(html_entity_decode($text));
				//dump($text);

			}
			return $text;
		}
		
		function rutor($text, $mode = false)
{
    global $bb_cfg;

    if ($mode == 'title') {
        preg_match_all("#<h1>([\s\S]*?)</h1>#", $text, $source, PREG_SET_ORDER);
        $text = $source[0][1];
    } else if ($mode == 'torrent') {
        preg_match_all("#<a href=\".*?/download/([\d]+)\">#", $text, $source, PREG_SET_ORDER);
        $text = $source[0][1];
    } else {
        preg_match_all("#<tr><td style=\"vertical-align:top;\"></td>([\s\S]*?)</td></tr>#si", $text, $source, PREG_SET_ORDER);
        $text = $source[0][1];
		$text = $text . $bb_cfg['release_group'];

        $text = preg_replace('/<td>.*?<img src="([\s\S]*?)".*?\/>/', '[img=left]$1[/img]', $text);
        $text = str_replace('<br />', "\n", $text);
        $text = preg_replace('/<a href="\/tag\/.*?" target="_blank">([\s\S]*?)<\/a>/', '$1', $text);
        $text = preg_replace('/<div class="hidewrap"><div class="hidehead" onclick="hideshow.*?">([\s\S]*?)<\/div><div class="hidebody"><\/div><textarea class="hidearea">([\s\S]*?)<\/textarea><\/div>/', "\n[spoiler=\"\\1\"]\\2[/spoiler]", $text);
        $text = preg_replace('/<a href="([\s\S]*?)" target="_blank">([\s\S]*?)<\/a>/', '[url=$1]$2[/url]', $text);
        $text = preg_replace('/<img src="(.*?)" style="float:(.*?);" \/>/', '[img=$2]$1[/img]', $text);
        $text = preg_replace('/<img src="([\s\S]*?)" \/>/', '[img]$1[/img]', $text);

        $text = str_replace('<center>', '[align=center]', $text);
        $text = str_replace('</center>', '[/align]', $text);
        $text = str_replace('<hr />', '[hr]', $text);

        $text = str_replace('&#039;', "'", $text);
        $text = str_replace('&nbsp;', ' ', $text);

        for ($i = 0; $i <= 20; $i++) {
            $text = preg_replace('/<b>([^<]*?)<(?=\/)\/b>/', '[b]$1[/b]', $text);
            $text = preg_replace('/<u>([^<]*?)<(?=\/)\/u>/', '[u]$1[/u]', $text);
            $text = preg_replace('/<i>([^<]*?)<(?=\/)\/i>/', '[i]$1[/i]', $text);
            $text = preg_replace('/<s>([^<]*?)<(?=\/)\/s>/', '[s]$1[/s]', $text);
            $text = preg_replace('/<font size="([^<]*?)">([^<]*?)<(?=\/)\/font>/', "[size=2\\1]\\2[/size]", $text);
            $text = preg_replace('/<span style="color:([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[color=$1]$2[/color]', $text);
            $text = preg_replace('/<span style="font-family:([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[font="$1"]$2[/font]', $text);
        }
        // Убираем пустое пространство
        $text = preg_replace('#([\r\n])[\s]+#is', "$1", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
    }

    return $text;
}

function riper($text, $mode = false)
{
    global $bb_cfg;

    if ($mode == 'title') {
        preg_match_all('#<div class="h1cla"><h1>([\s\S]*?)</h1></div>#', $text, $source, PREG_SET_ORDER);
        $text = $source[0][1];
        $text = str_replace(' - Скачать торрент бесплатно', '', $text);
    } else if ($mode == 'torrent') {
        preg_match_all("#<a href=\"./download/file.php\?id=([\d]+)\">#", $text, $source, PREG_SET_ORDER);
        $text = $source[0][1];
    } else {
				preg_match_all ('/<div class=\"content"\>([\s\S]*?)<table style="font/', $text, $source, PREG_SET_ORDER);

				preg_match_all ('/<a href="(.*?)" rel="prettyPhotoPosters.*?"><img src=".*?" alt="" style="max-width:.*?"\/><\/a>/', $text, $pic, PREG_SET_ORDER);
			    $poster = ($pic[0][1]) ? "[img=left]". $pic[0][1] ."[/img]\n\n" : "";
			    $text = $poster . @$source[0][1];


        $text = str_replace(' - Скачать торрент', 'hr', $text);
        $text = str_replace('./go.html?', '', $text);
		$text = str_replace('?ref_=tt_mv_close', '', $text);

        // Название
        $text = preg_replace('#<h2 .*?><a .*?>.*?<\/h2>#', "", $text);
		$text = $text . $bb_cfg['release_group'];

        $text = str_replace('<hr />', '[hr]', $text);
 $text = str_replace('<br/>', "\n", $text);
        $text = str_replace('<ul>', '[list]', $text);
        $text = str_replace('</ul>', '[/list]', $text);
        $text = str_replace('<li>', "\n[*]", $text);
        $text = str_replace('</li>', '', $text);
        $text = str_replace('&#039;', "'", $text);
        $text = str_replace('&nbsp;', ' ', $text);
		$text = preg_replace('#</td><td style="vertical-align: top; padding-top: 30px;">[\s\S]*?<table[\s\S]*?><tr><td>#', "", $text);
		$text = str_replace('<div class="clear"></div>', "", $text);
				
        $text = preg_replace('#<code>([^<]*?)<\/code>#', "[code]$1[/code]", $text);
        $text = preg_replace('#<blockquote class="uncited"><div>([^<]*?)<\/div><\/blockquote>#', "[quote]$1[/quote]", $text);

        $text = preg_replace('#<a href="(.*?)" rel="prettyPhotoSscreenshots\[0\]">.*?<\/a>#', "[thumb]$1[/thumb]", $text);
		$text = preg_replace('/<img width=".*?" src="(.*?)" alt="">/', '[img]$1[/img]', $text);

/*
        $text = preg_replace('#<a href="([^<]*?)" class="postlink-local">([^<]*?)<\/a>#', "[url=$1]$2[/url]", $text);
        $text = preg_replace('#<a href="([^<]*?)" class="postlink" rel="nofollow" onclick=".*?">([^<]*?)<\/a>#', "[url=$1]$2[/url]", $text);
        $text = preg_replace('#<a href="([^<]*?)" class="postlink img_link" rel="nofollow" onclick=".*?">([^<]*?)<\/a>#', "[url=$1]$2[/url]", $text);
*/
        $text = preg_replace('#<var title="(.*?)" class="postImg" alt=".*?"\/><\/var>#', '[thumb]$1[/thumb]', $text);
        

        for ($i = 0; $i <= 20; $i++) {
            $text = preg_replace('#<span style="font-weight: bold">([^<]*?)<\/span>#', '[b]$1[/b]', $text);
            $text = preg_replace('#<span style="text-decoration: underline">([^<]*?)<\/span>#', '[u]$1[/u]', $text);
            $text = preg_replace('#<span style="font-style: italic"([^<]*?)<\/span>#', '[i]$1[/i]', $text);
            $text = preg_replace('#<span style="text-decoration: line-through;" accesskey="s">([^<]*?)<\/span>#', '[s]$1[/s]', $text);
            $text = preg_replace('#<div style="text-align: (.*?);">([^<]*?)<\/div>#', "[align=$1]$2[/align]", $text);
			$text = preg_replace('/<a href="([^<]*?)" class="postlink[^<]*?" rel="nofollow" onclick="[^<]*?">([^<]*?)<\/a>/', '[url=$1]$2[/url]', $text);
            //$text = preg_replace('#<span style="font-size: ([^<]*?); line-height: ([^<]*?);">([^<]*?)<\/span>#', "[size=$1]$3[/size]", $text);
			$text = preg_replace('/<span style="font-size: ([^<]*?)0%; line-height:[^<]*?">([^<]*?)<(?=\/)\/span>/', '[size=\\1]\\2[/size]', $text);
            $text = preg_replace('#<span style="color: ([^<]*?)">([^<]*?)<\/span>#', '[color=$1]$2[/color]', $text);
            $text = preg_replace('#<span style="font-family:([^<]*?);">([^<]*?)<\/span>#', '[font="$1"]$2[/font]', $text);
            $text = preg_replace('#<div class="sp-body" title="(.*?)">([^<]*?)<\/div>#', "[align=center][spoiler=\"$1\"]\n$2\n[/spoiler][/align]", $text);
        }
        // Убираем пустое пространство
        $text = preg_replace('#([\r\n])[\s]+#is', "$1", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
    }

    return $text;
}
		
		function tapochek($text, $mode=false)
		{


			if($mode == 'title')
			{
				preg_match_all ("#<h1 class=\"maintitle\"><a href=\".*?\">([\s\S]*?)</a></h1>#", $text, $source, PREG_SET_ORDER);
			    $text = $source[0][1];
				$text = str_replace('<wbr>', '', $text);
		    }
		    elseif($mode == 'torrent')
		    {
				//Торрент не найден - если указана неверная ссылка на скачивание Torrent
		    	preg_match_all ("#<th colspan=\"3\" class=\"genmed\">(.*?).torrent</th>[\s\S]*?<a href=\"download.php\?id=(.*?)\" #", $text, $source, PREG_SET_ORDER);
			    $text = $source[0];
		    }
		    else
		    {
				
				$pos = strpos($text, '<div class="post_body"');
				$text = substr($text, $pos);
				$pos = strpos($text, '<div class="spacer_8"></div>');
				$text = substr($text, 0, $pos);
                $text = str_replace('<div class="post_body">', '', $text);
                $text = str_replace('<wbr>', '', $text);

				$text = preg_replace('/<img class="smile" src=".*?" align="absmiddle" border="0" \/>/', '', $text);
                $text = str_replace('<div class="sp-wrap">', '', $text);
                $text = str_replace('<div class="c-wrap">', '', $text);
                $text = str_replace('<div class="clear"></div>', '', $text);
				

                $text = preg_replace('/<div style="display: none;">.*?<\/div>/', '', $text);
					//Основная часть всякие стили и тд и тп. НЕЛЬЗЯ ДЕЛАТЬ ОДНО В ДРУГОМ! Это новый код!
					$text = str_replace('<wbr>', '', $text);
                    $text = str_replace('<br>', '', $text);
					$text = str_replace('<ul>', '[list]', $text);
					$text = str_replace('</ul>', '[/list]', $text);
					$text = str_replace('<ol class="post-ul">', '[list]', $text);
					$text = str_replace('</ol>', '[/list]', $text);
					$text = str_replace('<li>', "[*]", $text);
					$text = str_replace('</li>', '', $text);
					$text = preg_replace("#<span style=\"font-size: 24px; line-height: normal;\">(.*?)</span>#si", "[align=center][size=24]\\1[/size][/align]", $text);
					$text = preg_replace("#<span class=\"post-br\"><br></span>#si", "\r\n\r\n", $text);
					$text = preg_replace('#<span class=\"post-br\"></span>#si', "\r\n", $text);
					$text = preg_replace("#<hr class=\"post-hr\">#si", "[hr]", $text);
					$text = preg_replace("#<span class=\"post-b\">(.*?)</span><br>#si", "[b]\\1[/b]", $text);
					$text = preg_replace("#<span class=\"post-b\">([^>]*?)требования</span>#si", "[hr][b]Системные требования[/b]", $text);
					$text = preg_replace("#<span class=\"post-b\">Описание</span>#si", "[hr][b]Описание[/b]", $text);
					$text = preg_replace("#<span class=\"post-align\" style=\"text-align: (.*?);\">(.*?)</span>#si", "[align=\\1]\\2[/align]", $text);
					$text = preg_replace("#<var class=\"postImg\" title=\"(.*?)\">.*?</var>#si", "[img]\\1[/img]", $text);
					$text = preg_replace("#<var class=\"postImg postImgAligned img-right\" title=\"(.*?)\">.*?</var>#si", "[img=left]\\1[/img]", $text);
					$text = preg_replace("#<var class=\"postImg postImgAligned img-(.*?)\" title=\"(.*?)\">.*?</var>#si", "[img=\\1]\\2[/img]", $text);
					$text = preg_replace("#<span style=\"font-size: (.*?)px; line-height: normal;\">(.*?)</span>#si", "[size=\\1]\\2[/size]", $text);
                $text = preg_replace('/<div style="display: none;">.*?<\/div>/', '', $text);
                $text = preg_replace('/<a href="([^<]*?)" rel=".*?"><img src=".*?" \/>&nbsp;<\/a>/', '[thumbnails]$1[/thumbnails]', $text);
				$text = str_replace('<hr />', "\n[hr]\n", $text);
                $text = preg_replace('/<var class="postImg" title="(.*?)">&#10;<\/var>/', '[img]$1[/img]', $text);
				$text = preg_replace('/<var class="postImg postImgAligned img-(.*?)" title="([\s\S]*?)">&#10;<\/var>/', "[img=\\1]\\2[/img]\n", $text);
				$text = preg_replace('/<img src="(.*?)" style="float: right;" class="glossy iradius20 horizontal" \/>/', '[img=left]$1[/img]', $text);

$text = preg_replace('/<span class"(.*?)" title="" \/>/', '[img=left]$1[/img]', $text);
	$text = preg_replace("#<a href=\"(.*?)\" rel=\"(.*?)\" class=\"(.*?)\">(.*?)</a>#si", "[thumbnails]$1[/thumbnails]", $text);
	$text = preg_replace("#<span href=\"(.*?)\" rel=\"(.*?)\" class=\"(.*?)\">(.*?)</span>#si", "[thumbnails]$1[/thumbnails]", $text);


				
	$text = preg_replace("#<span class=\"post-hr\">-</span>#si", "[hr]", $text);
	$text = preg_replace("#<var class=\"postImg\" title=\"(.*?)\">.*?</var>#si", "[img]\\1[/img]", $text);
	$text = preg_replace("#<var class=\"postImg postImgAligned img-(.*?)\" title=\"(.*?)\">.*?</var>#si", "[img=\\1]\\2[/img]", $text);


								$text = str_replace('<hr />', "\n[hr]\n", $text);
                $text = preg_replace('/<var class="postImg" title="(.*?)">&#10;<\/var>/', '[img]$1[/img]', $text);
				$text = preg_replace('/<var class="postImg postImgAligned img-(.*?)" title="([\s\S]*?)">&#10;<\/var>/', "[img=\\1]\\2[/img]\n", $text);

				$text = str_replace('<ul>', '[list]', $text);
				$text = str_replace('</ul>', '[/list]', $text);
				$text = str_replace('<li>', "\n[*]", $text);
				$text = str_replace('</li>', '', $text);
				$text = str_replace('<ol type="1">', '[list=1]', $text);
				$text = str_replace('<ol type="a">', '[list=a]', $text);
				$text = str_replace('</ol>', '[/list]', $text);
				$text = str_replace('', "\n", $text);
				$text = str_replace('<div></div>', "\n", $text);
                $text = preg_replace('/<a href="([^<]*?)" rel=".*?"><img src=".*?" \/>&nbsp;<\/a>/', '[img]$1[/img]', $text);

                $text = str_replace('<br clear="all" />', "\n[br]\n", $text);
                $text = str_replace('<br />', "\n", $text);

                $text = str_replace('<div', '<span', $text);
                $text = str_replace('</div>', '</span>', $text);
                $text = str_replace('<a', '<span', $text);
                $text = str_replace('</a>', '</span>', $text);

                $text = str_replace('&#039;', "'", $text);
				$text = str_replace('&nbsp;', ' ', $text);
				$text = str_replace('&gt;', '>', $text);
				$text = str_replace('&lt;', '<', $text);

				for ($i=0; $i<=20; $i++)
				{
					$text = preg_replace('/<span align="([^<]*?)">([^<]*?)<(?=\/)\/span>/', "[align=\\1]\n\\2\n[/align]", $text);
					$text = preg_replace('/<span style="font-weight: bold;">([^<]*?)<(?=\/)\/span>/', '[b]$1[/b]', $text);
					$text = preg_replace('/<span style="text-decoration: underline;">([^<]*?)<(?=\/)\/span>/', '[u]$1[/u]', $text);
					$text = preg_replace('/<span style="font-style: italic;">([^<]*?)<(?=\/)\/span>/', '[i]$1[/i]', $text);
					$text = preg_replace('/<span style="font-size: ([^<]*?)px; line-height: normal;">([^<]*?)<(?=\/)\/span>/', "[size=\\1]\\2[/size]", $text);
					$text = preg_replace('/<span style="font-family: ([^<]*?)">([^<]*?)<(?=\/)\/span>/', "[font=\"\\1\"]\\2[/font]", $text);
					$text = preg_replace('/<span style="text-align: ([^<]*?);">([^<]*?)<(?=\/)\/span>/', "[align=\\1]\n\\2\n[/align]", $text);
					$text = preg_replace('/<span style="color: ([^<]*?);">([^<]*?)<(?=\/)\/span>/', '[color=$1]$2[/color]', $text);

					$text = preg_replace('/<span href="([^<]*?)" class="postLink">([^<]*?)<(?=\/)\/span>/', '[url=$1]$2[/url]', $text);
					$text = preg_replace('/<span class="sp-body" title="([^<]*?)">([\s\S]*?)<(?=\/)\/span>([\s\S]*?)<([^<]*?)\/span>/', "[spoiler=\"\\1\"]\n\\2\n[/spoiler]", $text);
					$text = preg_replace('/<span class="q">([^<]*?)<(?=\/)\/span>([^<]*?)<([^<]*?)\/span>/', "[quote]\n\\1\n[/quote]", $text);
					$text = preg_replace('/<span class="q" head="([^<]*?)">([^<]*?)<(?=\/)\/span>([\s\S]*?)<([^<]*?)\/span>/', "[quote=\"\\1\"]\n\\2\n[/quote]", $text);
					$text = preg_replace('/<span class="c-head">.*?<span class="c-body">([^<]*?)<(?=\/)\/span>([\s\S]*?)<([^<]*?)\/span>/', "[code]\n\\1\n[/code]", $text);
				}
				
			

			    // Убираем пустое пространство

				$text = preg_replace('/([\r\n])[\s]+/is', "\\1", $text);
				$text = str_replace('</span>', '', $text);
				$text = str_replace('<span align="right">', '', $text);
				$text = str_replace('<span align="left">', '', $text);
				
				$text = str_replace('&#039;', "'", $text);
				$text = str_replace('&nbsp;', ' ', $text);
				$text = str_replace('&gt;', '>', $text);
				$text = str_replace('&lt;', '<', $text);
			}
			return $text;
		}

	    function only($text, $mode=false)
		{


			if($mode == 'title')
			{
				preg_match_all ("#<a class=\"maintitle\" href=\"viewtopic.php\?t=.*?\">(.*?)</a>#", $text, $source, PREG_SET_ORDER);
			    $text = $source[0][1];
			}
		    elseif($mode == 'torrent')
		    {
		    	preg_match_all ('#<td colspan="3" class="gen".*?><b>([\s\S]*?).torrent</b></td>.*?<a href="download.php\?id=([\d]+)" rel="nofollow">.*?</a>#s', $text, $source, PREG_SET_ORDER);
			    $text = $source[0];
		    }
		    else
		    {

				preg_match_all ("#<a href=\"posting.php\?mode=quote&amp;p=(.*?)\" rel=\"nofollow\"><img src=\".*?icon_quote.gif\".*?border=\"0\" class=\"pims\"></a>#", $text, $id, PREG_SET_ORDER);
				$post_id = $id[0][1];
//dump($post_id);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://only-soft.org/posting.php?mode=quote&p=$post_id");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_COOKIEFILE, LOG_DIR . 'only_cookie.txt');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$source = curl_exec($ch);
				$source  = iconv('windows-1251', 'UTF-8', $source);
			    $text = $source;
				preg_match_all ("#<textarea.*?\">\[\quote=\".*?\";p=\".*?\"\]([\s\S]*?)\[/quote\]</textarea>#", $text, $source, PREG_SET_ORDER);
				$text = $source[0][1];
				
                $text = str_replace('[poster=', '[img=', $text);
                $text = str_replace('[/poster]', '[/img]', $text);
                $text = str_replace('[poster]', '[img]', $text);
				$text = str_replace('[center]', "[align=center]", $text);
                $text = str_replace('[list=]', '[list]', $text);
                $text = str_replace('[/center]', "[/align]", $text);
				$text = str_replace('?ref_=plg_rt_1', '', $text);
				$text = str_replace('[table]', "", $text);
                $text = str_replace('[/table]', "", $text);
				$text = str_replace('[box]', "[box]", $text);
                $text = str_replace('[/box]', "[/box]", $text);
				$text = str_replace('[cut]', "", $text);
				$text = str_replace('[yt]', "[youtube]", $text);
                $text = str_replace('[/yt]', "[/youtube]", $text);
				$text = preg_replace('/\[spoiler=([\s\S]*?)\]/', '[spoiler="$1"]', $text);

                $text = preg_replace('#\[url=(.*?)\]\[img\](.*?)\[/img\]\[/url\]#', "[thumbnails]$1[/thumbnails]", $text);
                $text = preg_replace('#\[size=11\].*Время раздачи.*\[/size\]#i','', $text);
                $text = preg_replace('#\[align=center\]\[color=\#006699\](.*?)\|(.*?)\[/color\]\[/align\]#i',"[align=center][color=#006699]\\1-\\2[/color][/align]", $text);


				$text = preg_replace('/\[hide=([\s\S]*?)\]/', '[spoiler="$1"]', $text);
				$text = str_replace('[/hide]', '[/spoiler]', $text);
				$text = str_replace('[brc]', "", $text);
				$text = preg_replace('/\[imdb\]tt([\d]+)\[\/imdb\]/', '[imdb]https://www.imdb.com/title/tt$1[/imdb]', $text);
				$text = preg_replace('/\[kp\]([\d]+)\[\/kp\]/', '[kp]https://www.kinopoisk.ru/film/$1[/kp]', $text);

					$text = preg_replace('/http:(.*?)fastpic.ru/', "https:$1fastpic.ru/", $text);
					$text = preg_replace('/http:(.*?)imageban.ru/', "https:$1imageban.ru/", $text);
					$text = preg_replace('/http:(.*?)youpic.su/', "https:$1youpic.su/", $text);
					$text = preg_replace('/http:(.*?)lostpic.net/', "https:$1lostpic.net/", $text);
					$text = preg_replace('/http:(.*?)radikal.ru/', "https:$1radikal.ru/", $text);
					$text = str_replace('http://img-fotki.yandex.ru', 'https://img-fotki.yandex.ru', $text);
					$text = preg_replace('#\[url=mailto.*?].*?\[\/url\]#', "$1", $text);
			}			
			return $text;
		}


function rutracker($text, $mode=false)
		{
			if($mode == 'title')
			{   //Заголовок темы
                preg_match_all ('#<a id="topic-title" class="topic-title-\d+ highlight-cyrillic" href=".*?">(.*?)</a>#', $text, $source, PREG_SET_ORDER);
                $text = $source[0][1];
				$text = str_replace('<wbr>', '', $text);
				
		    }
			
		    elseif($mode == 'torrent')
            {	//Берет название для .torrent и парсит .torent   
				preg_match_all ("#<a id=\"topic-title\" [\s\S]*?>(.*?)</a>[\s\S]*?<a href=\"dl.php\?t=(.*?)\" #", $text, $source, PREG_SET_ORDER); 
				$text = $source[0];
				$text = str_replace('<wbr>', '', $text);
				$text = preg_replace('</>', '', $text);
                //die($text);
		    }    
					
			else
		    {
				$pos = strpos($text, '<div class="post_body"');
				$text = substr($text, $pos);
				$pos = strpos($text, '<div class="clear" style="height: 8px;"></div>');
				$text = substr($text, 0, $pos);	
				
                $text = preg_replace('/<a href="https:\/\/rutracker.org\/forum\/search.*?nm=.*?" class="postLink">(.*?)<\/a>/', '$1', $text);
				$text = preg_replace_callback('/<span href="https.*?rutracker.org\/forum\/viewtopic.*?" class="postLink">(.*?)<\/span>/', function ($v)
				{ 
				$text_data = $v[1];
				$text_url = strip_tags($text_data);
					return '[url=http://crackstatus.net/tracker.php?' . http_build_query(['nm' => $text_url]) . ']' . $text_data . '[/url]';
				}, 
				$text);
				
				
			for ($i=0; $i<=20; $i++)
			 {
					
				    //Основное тело.
					$text = preg_replace('/<div class="post_body" ([^<]*?)([^<]*?)([^<]*?) ([^<]*?)([^<]*?)([^<]*?)>/', "", $text);
					$text = str_replace('</div><!--/post_body-->', '', $text);
					//Основная часть всякие стили и тд и тп. НЕЛЬЗЯ ДЕЛАТЬ ОДНО В ДРУГОМ! Это новый код!
					$text = str_replace('<wbr>', '', $text);
                    $text = str_replace('<br>', '', $text);
					$text = str_replace('<ul>', '[list]', $text);
					$text = str_replace('</ul>', '[/list]', $text);
					$text = str_replace('<ol class="post-ul">', '[list]', $text);
					$text = str_replace('</ol>', '[/list]', $text);
					$text = str_replace('<li>', "[*]", $text);
					$text = str_replace('</li>', '', $text);
					$text = preg_replace("#<span style=\"font-size: 24px; line-height: normal;\">(.*?)</span>#si", "[align=center][size=24]\\1[/size][/align]", $text);
					$text = preg_replace("#<span class=\"post-br\"><br></span>#si", "\r\n\r\n", $text);
					$text = preg_replace('#<span class=\"post-br\"></span>#si', "\r\n", $text);
					$text = preg_replace("#<hr class=\"post-hr\">#si", "[hr]", $text);
					$text = preg_replace("#<span class=\"post-b\">(.*?)</span><br>#si", "[b]\\1[/b]", $text);
					$text = preg_replace("#<span class=\"post-b\">([^>]*?)требования</span>#si", "[hr][b]Системные требования[/b]", $text);
					$text = preg_replace("#<span class=\"post-b\">Описание</span>#si", "[hr][b]Описание[/b]", $text);
					$text = preg_replace("#<span class=\"post-align\" style=\"text-align: (.*?);\">(.*?)</span>#si", "[align=\\1]\\2[/align]", $text);
					$text = preg_replace("#<var class=\"postImg\" title=\"(.*?)\">.*?</var>#si", "[img]\\1[/img]", $text);

                     // by corew [05.11.22] [start]
                     $text = preg_replace("#<var class=\"postImg postImg30\" title=\"(.*?)\">.*?</var>#si", "[align=center][img]\\1[/img][/align]", $text);
                     // by corew [05.11.22] [end]

					$text = preg_replace("#<var class=\"postImg postImgAligned img-right\" title=\"(.*?)\">.*?</var>#si", "[img=left]\\1[/img]", $text);
					$text = preg_replace("#<var class=\"postImg postImgAligned img-(.*?)\" title=\"(.*?)\">.*?</var>#si", "[img=\\1]\\2[/img]", $text);
					$text = preg_replace("#<a href=\"(.*?)\" class=\"(.*?)\">(.*?)</a>#si", "[url=\\1]\\3[/url]", $text);
					$text = preg_replace("#<span style=\"font-size: (.*?)px; line-height: normal;\">(.*?)</span>#si", "[size=\\1]\\2[/size]", $text);
					//05.10.22 Добавил правильную замену шрифта SERIF
					$text = preg_replace("#<span class=\"post-font-serif1\">(.*?)</span>#si", "[font=\"serif\"]\\1[/font]", $text);
					
					
					      //Массовая замена блоков по маске
							while (preg_match("#<(?:span|div) (?:(?:class=\"([^>]*?)\") (?:style=\"(?:[^>]*?): ((?:[^>]*?));\">)|(?:class=\"post-((?:[^>]*?))\">)|(?:style=\"((?:[^>]*?)): ((?:[^>]*?));\">)|(?:(?:style=\"((?:[^>]*?)): ((?:[^>]*?))\") (?:class=\"post-((?:[^>]*?))\">)))((?:(?!<(?:span|div) ).)*?)</(?:span|div)>#sie", $text, $match))
						{
							if (!empty($match[1]))
							{
								switch ($match[1])
								{
									case "p-color": $replace = "[color=".$match[2]."]".$match[9]."[/color]";break;
									case "post-align": $replace = "[align=".$match[2]."]".$match[9]."[/align]";break;
								}
							}
							
							if (!empty($match[3])) $replace = "[".$match[3]."]".$match[9]."[/".$match[3]."]";
							if (!empty($match[4])) 
							{
								switch ($match[4])
								{
									case "font-family": $replace = "[font=\"".$match[5]."\"]".$match[9]."[/font]";break;
									case "font-size": preg_match("#^(\d+)#si", $match[5], $pocket); $replace = "[size=".$pocket[1]."]".$match[9]."[/size]";break;
								}
			
							}
							if (!empty($match[6])) $replace = $match[9];
							$search = "|".preg_quote($match[0])."|si";
							$text = preg_replace($search, $replace, $text);
						}
                          
						  //Долбанный спойлер
							while (preg_match("#<div class=\"((?:[^>]*?))\">(?:(?:(?!<div ).)*?)<div class=\"(?:(?:[^>]*?))\">((?:(?!<div ).)*?)</div>(?:(?:(?!<div ).)*?)<div class=\"(?:(?:[^>]*?))\">((?:(?!<div ).)*?)</div>(?:(?:(?!<div ).)*?)</div>#sie", $text, $match))
						{
							//Сравниваем полученные значения и заменяем по маске
							switch ($match[1]) 
							{
								case "sp-wrap":$replace="[spoiler=\"".$match[2]."\"]".$match[3]."[/spoiler]";break;
								case "c-wrap": $replace="[code]".$match[3]."[/code]"; break;
							}
								$search = "|".preg_quote($match[0])."|si";
								$text = preg_replace($search, strip_tags($replace), $text);
						}


			  }


			}
			return $text;
		}
		
		if (!defined('BB_ROOT')) die(basename(__FILE__));

	    function xatab($text, $mode=false)
		{


			if($mode == 'title')
			{
				preg_match_all ("#<h1 class=\"inner-entry__title\"><a href=\".*?\">([\s\S]*?)</a></h1>#", $text, $source, PREG_SET_ORDER);
			    $text = $source[0][1];
				$text = str_replace('<wbr>', '', $text);
		    }
		    elseif($mode == 'torrent')
		    {
				//Торрент не найден - если указана неверная ссылка на скачивание Torrent
		    	preg_match_all ("#<div id=\"download\" class=\"download-buttons\">(.*?).torrent</th>[\s\S]*?<a href=\"index.php?do=download\&id=(.*?)\" #", $text, $source, PREG_SET_ORDER);
			    $text = $source[0];
		    }
		    else
		    {
				
				$pos = strpos($text, '<section class="inner-entry entry"');
				$text = preg_replace('/<section class="inner-entry entry"><img src="([^<]*?)" \/><\/div>/', "[align=center][img]\\1[/img][/align]\n", $text);
				$text = strip_tags(html_entity_decode($text));
				//dump($text);

			}
			return $text;
		}