<style>
    .btn {
        cursor: pointer;
        color: rgb(255, 255, 255);
        background-color: #1C508C;
        padding: 5px 9px;
        font-size: 12px;
        border-radius: 5px;
        border: none;
    }

    .td_pads {
        padding: 10px 15px !important;
    }
</style>

<script type="text/javascript">
    ajax.sitemap = function (mode) {
        ajax.exec({
            action: 'sitemap',
            mode: mode
        });
    }
    ajax.callback.sitemap = function (data) {
        if (data.mode == 'create') {
            $('#mess_time').html(data.html);
        } else {
            $('#sitemap').html(data.html);
        }
    }
</script>

<h1>{L_SITEMAP_ADMIN}</h1>

<form action="admin_sitemap.php" method="post">
<table class="forumline">
	<tr class="row1">
		<td width="25%"><span class="gen"><b>{L_INFORMATION}:</b></span></td>
		<td class="td_pads"><div id="mess_time">{MESSAGE}</div></td>
	</tr>
	<tr class="row1">
		<td width="25%"><span class="gen"><b>{L_SITEMAP_OPTIONS}:</b></span></td>
		<td class="td_pads">
			<input type="button" class="btn" value="{L_SITEMAP_CREATE}" onclick="ajax.sitemap('create');">&nbsp;
            <input type="button" class="btn" value="{L_SITEMAP_NOTIFY}" onclick="ajax.sitemap('search_update');"><br />
			<div id="sitemap"></div>
		</td>
	</tr>
	<tr>
		<th colspan="2">{L_SITEMAP_WHAT_NEXT}</th>
	</tr>
	<tr>
		<td class="row1" colspan="2">
			<p>1. {L_SITEMAP_GOOGLE_1}</p>
			<p>2. {L_SITEMAP_GOOGLE_2}</p>
			<p>3. {L_SITEMAP_YANDEX_1}</p>
			<p>4. {L_SITEMAP_YANDEX_2}</p>
			<p>5. {L_SITEMAP_BING_1}</p>
			<p>6. {L_SITEMAP_BING_2}</p>
		</td>
	</tr>
	<tr>
		<th colspan="2">{L_SITEMAP_ADD_TITLE}</th>
	</tr>
	<tr class="row1">
		<td class="row1">
			<span class="gen"><b>{L_SITEMAP_ADD_PAGE}:</b></span>
		</td>
		<td>
			<textarea name="static_sitemap" rows="5" cols="70">{STATIC_SITEMAP}</textarea><br />
			<br><p>{L_SITEMAP_ADD_EXP_1} <br><br><b style="color: #993300;">{L_SITEMAP_ADD_EXP_2}</b></p>
		</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">
			<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
			<input type="reset" value="{L_RESET}" class="liteoption" />
		</td>
	</tr>
</table>
</form>
