
<h1 class="pagetitle">Вход</h1>

<form action="{LOGIN_URL}" method="post">

<input type="hidden" name="redirect" value="{REDIRECT_URL}" />
<!-- IF ADMIN_LOGIN --><input type="hidden" name="admin" value="1" /><!-- ENDIF -->

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<table class="forumline">
<tr>
	<th>Вход</th>
</tr>
<tr>
	<td class="row1">

	<!-- IF LOGIN_ERR_MSG -->
	<h4 class="warnColor1 tCenter mrg_16">{LOGIN_ERR_MSG}</h4>
	<!-- ELSEIF ADMIN_LOGIN -->
	<h4 class="tCenter mrg_16">Для получения доступа к мод/админ опциям необходимо еще раз ввести пароль</h4>
	<!-- ELSE -->
	<h4 class="tCenter mrg_16">Введите ваше имя и пароль</h4>
	<!-- ENDIF -->

	<div class="mrg_16">
	<table class="borderless bCenter">
	<tr>
		<td width="31%" class="tRight">Имя:</td>
		<td width="69%"><input type="text" name="login_username" size="25" maxlength="30" value="{LOGIN_USERNAME}" tabindex="101" <!-- IF ADMIN_LOGIN -->readonly="readonly" style="color: gray"<!-- ENDIF --> /></td>
	</tr>
	<tr>
		<td class="tRight">Пароль:</td>
		<td><input type="password" name="login_password" size="25" maxlength="32" value="{LOGIN_PASSWORD}" tabindex="102" /></td>
	</tr>
	<!-- IF CAPTCHA_HTML -->
	<tr>
		<td class="tRight nowrap">Код:</td>
		<td>{CAPTCHA_HTML}</td>
	</tr>
	<!-- ENDIF -->
	<tr>
		<td colspan="2" class="tCenter med nowrap pad_4"><label><input type="checkbox" name="ses_short" value="1" <!-- IF ADMIN_LOGIN -->disabled="disabled"<!-- ENDIF --> tabindex="103" /> Короткая сессия (автовыход через полчаса неактивности)</label></td>
	</tr>
	<tr>
		<td colspan="2" class="tCenter pad_8"><input type="submit" name="login" class="bold long" value="Вход" tabindex="104" /></td>
	</tr>
	<tr>
		<td colspan="2" class="tCenter med">В вашем браузере должны быть включены куки и JavaScript!</td>
	</tr>
	<tr>
		<td colspan="2" class="tCenter"><a href="profile.php?mode=sendpassword" class="med">Забыли пароль?</a></td>
	</tr>
	</table>
	</div>

	</td>
</tr>
</table>

</form>
