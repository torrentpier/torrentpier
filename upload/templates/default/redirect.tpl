
<script language="javascript" type="text/javascript">
$(function(){
	time = {TIME};
	countdown();
});
function countdown()
{
	if(time != 0)
	{
		$('span#time').text(time);
		setTimeout('countdown()',1000);
		time--;
	}
}
</script>
<table cellpadding="2" cellspacing="0" width="100%">
<tr>
	<td width="100%">
		<h1 class="maintitle">{PAGE_TITLE}</h1>
        <div id="forums_top_links" class="nav">
		    <a href="{U_INDEX}">{T_INDEX}</a>
		</div>
	</td>
</tr>
</table>
<div class="category">
	<h3 class="cat_title">{PAGE_TITLE}</h3>
	<div class="f_tbl_wrap pad_10">
	<div class="q pad_10">
		Вы покидаете <b>{SITENAME}</b> и переходите на <a href="{URL}"><i>{URL_TITLE}</i></a>.
		<div class="spacer_10"></div>
		Вы будете переадресованы через <span class="bold" id="time">{TIME}</span> секунд.
		<div class="spacer_10"></div>
		<span style="color: red;">Внимание! Администрация не несет отвественности за сайт на которой вы переходите.</span>
	</div>
	</div>
</div>


