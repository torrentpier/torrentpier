<?php
	require_once('xbt_config.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<link rel=stylesheet href="xbt.css">
<title>XBT Files</title>
<?php
	mysql_connect($mysql_host, $mysql_user, $mysql_pass);
	mysql_select_db($mysql_db);
	$results = mysql_query("select sum(completed) completed, sum(leechers) leechers, sum(seeders) seeders, sum(leechers or seeders) torrents from xbt_files");
	$result = mysql_fetch_assoc($results);
	$result['peers'] = $result['leechers'] + $result['seeders'];
	echo('<table>');
	printf('<tr><th align=right>completed<td align=right>%d<td>', $result['completed']);
	printf('<tr><th align=right>peers<td align=right>%d<td align=right>100 %%', $result['peers']);
	if ($result['peers'])
	{
		printf('<tr><th align=right>leechers<td align=right>%d<td align=right>%d %%', $result['leechers'], $result['leechers'] * 100 / $result['peers']);
		printf('<tr><th align=right>seeders<td align=right>%d<td align=right>%d %%', $result['seeders'], $result['seeders'] * 100 / $result['peers']);
	}
	printf('<tr><th align=right>torrents<td align=right>%d<td>', $result['torrents']);
	printf('<tr><th align=right>time<td align=right colspan=2>%s', gmdate('Y-m-d H:i:s'));
	echo('</table>');
	echo('<hr>');
	$results = mysql_query("select * from xbt_files where leechers or seeders order by ctime desc");
	echo('<table>');
	echo('<tr>');
	echo('<th>fid');
	echo('<th>info_hash');
	echo('<th>leechers');
	echo('<th>seeders');
	echo('<th>completed');
	echo('<th>modified');
	echo('<th>created');
	while ($result = mysql_fetch_assoc($results))
	{
		echo('<tr>');
		printf('<td align=right>%d', $result['fid']);
		printf('<td>%s', bin2hex($result['info_hash']));
		echo('<td align=right>');
		if ($result['leechers'])
			printf('%d', $result['leechers']);
		echo('<td align=right>');
		if ($result['seeders'])
			printf('%d', $result['seeders']);
		echo('<td align=right>');
		if ($result['completed'])
			printf('%d', $result['completed']);
		printf('<td>%s', gmdate('Y-m-d H:i:s', $result['mtime']));
		printf('<td>%s', gmdate('Y-m-d H:i:s', $result['ctime']));
	}
	echo('</table>');
?>
<hr>
<div>
	<a href="http://sourceforge.net/projects/xbtt/"><img src="http://sourceforge.net/sflogo.php?group_id=94951;type=1" alt="XBT project at SF"></a>
	<a href="http://w3.org/"><img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!"></a>
	<a href="http://w3.org/"><img src="http://w3.org/Icons/valid-html401" alt="Valid HTML 4.01!"></a>
</div>