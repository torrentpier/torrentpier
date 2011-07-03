
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<table class="forumline">
<tr>
	<th>{SITENAME} - {REGISTRATION}</th>
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
				<a href="#" onclick="$('#go-to-reg').submit(); return false;">Я <b>согласен</b> с этими условиями</a>
				<br /><br />
				<a href="index.php">Я <b>не согласен</b> с этими условиями</a>
			</div>
			<br />
		</div>
	</td>
</tr>
</table>

<noscript><div class="warningBox2 bold tCenter">Для регистрации необходимo включить JavaScript</div></noscript>
