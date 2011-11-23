
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<table class="forumline">
<tr>
	<th>{SITENAME} - {L_REGISTRATION}</th>
</tr>
<tr>
	<td class="row1">
		<div class="w80 bCenter">
			<?php require(BB_ROOT .'/misc/html/agreement.html'); ?>
			<br /><br />
			<div class="tCenter">
				<form id="go-to-reg" action="profile.php?mode=register" method="post">
				<input type="hidden" name="reg_agreed" value="1" />
				</form>
				<a href="#" onclick="$('#go-to-reg').submit(); return false;">{L_TERMS_ON}</a>
				<br /><br />
				<a href="index.php">{L_TERMS_OFF}</a>
			</div>
			<br />
		</div>
	</td>
</tr>
</table>

<noscript><div class="warningBox2 bold tCenter">{L_JAVASCRIPT_ON_REGISTER}</div></noscript>
