<style type="text/css">
#torhelp .catTitle { color: #AC0000; }
#torhelp ul { padding: 6px 20px 4px; }
#torhelp li { padding-bottom: 2px; }
#torhelp a { text-decoration: none; color: #444444; }
#torhelp p { padding-left: 10px; }
</style>

<div id="torhelp">

	<table class="bordered bCenter w100">
	<tr><td class="catTitle">{L_TORHELP_TITLE}</td></tr>
	<tr>
		<td class="med bold row1 pad_8">
			<p class="normal floatR">[
				<a href="#" onclick="
					var timestamp = Math.floor((new Date()) / 1000);
					setCookie('torhelp', (timestamp + 20*60));
					$('#torhelp').hide('normal');
					return false;
				">{L_HIDE}</a>
				]</p>
			<ul><li>{TORHELP_TOPICS}</li></ul>
		</td>
	</tr>
	</table>

</div><!--/torhelp-->
