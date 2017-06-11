<style>
.btn{
	color: rgb(255, 255, 255);
	text-decoration: none;
	padding: 2px 7px;
	font-size: 12px;
	border-radius: 3px;
}
.btn-success {
	background-image: -moz-linear-gradient(top, #62c462, #51a351);
	background-image: -ms-linear-gradient(top, #62c462, #51a351);
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62c462), to(#51a351));
	background-image: -webkit-linear-gradient(top, #62c462, #51a351);
	background-image: -o-linear-gradient(top, #62c462, #51a351);
	background-image: linear-gradient(top, #62c462, #51a351);
	background-repeat: repeat-x;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#62c462', endColorstr='#51a351', GradientType=0);
	border-color: #51a351 #51a351 #387038;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	filter: progid:dximagetransform.microsoft.gradient(enabled=false);
}
.btn-success:active,
.btn-success:disabled,
.btn-success:focus,
.btn-success:hover,
.btn-warning:active,
.btn-warning:disabled,
.btn-warning:focus,
.btn-warning:hover {
	text-decoration: none;
	color: rgb(255, 255, 255);
	text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.btn-warning:active,
.btn-warning:disabled,
.btn-warning:focus,
.btn-warning:hover {
	background-color: #f89406;
}
.btn-success:active,
.btn-success:disabled,
.btn-success:focus,
.btn-success:hover {
	background-color: #51a351;
}
.btn-warning {
	background-color: #faa732;
	background-image: -moz-linear-gradient(top, #fbb450, #f89406);
	background-image: -ms-linear-gradient(top, #fbb450, #f89406);
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fbb450), to(#f89406));
	background-image: -webkit-linear-gradient(top, #fbb450, #f89406);
	background-image: -o-linear-gradient(top, #fbb450, #f89406);
	background-image: linear-gradient(top, #fbb450, #f89406);
	background-repeat: repeat-x;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fbb450', endColorstr='#f89406', GradientType=0);
	border-color: #f89406 #f89406 #ad6704;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	filter: progid:dximagetransform.microsoft.gradient(enabled=false);
}
.td_pads { padding: 10px 15px !important; }
</style>

<script type="text/javascript">
ajax.sitemap = function(mode) {
	ajax.exec({
		action : 'sitemap',
		mode : mode
	});
}
ajax.callback.sitemap = function(data) {
	if(data.mode == 'create') $('#mess_time').html(data.html);
	else $('#sitemap').html(data.html);
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
			<a href="#" class="btn btn-success" onclick="ajax.sitemap('create'); return false;">{L_SITEMAP_CREATE}</a>&nbsp;&nbsp;
			<a href="#" class="btn btn-warning" onclick="ajax.sitemap('search_update'); return false;">{L_SITEMAP_NOTIFY}</a><br />
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
			<p>{L_SITEMAP_ADD_EXP_1} <b style="color: #993300;">{L_SITEMAP_ADD_EXP_2}</b></p>
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
