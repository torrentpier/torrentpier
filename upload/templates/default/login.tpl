
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<form action="{S_LOGIN_ACTION}" method="post">

<input type="hidden" name="redirect" value="{REDIRECT_URL}" />
<input type="hidden" name="cookie_test" value="{COOKIE_TEST_VAL}" />
{S_HIDDEN_FIELDS}
<!-- IF ADMIN_LOGIN --><input type="hidden" name="admin" value="1" /><!-- ENDIF -->

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<table class="forumline">
<tr>
	<th>{L_ENTER_PASSWORD}</th>
</tr>
<tr>
	<td class="row1">

	<!-- IF ERR_MSG -->
	<h4 class="warnColor1 tCenter mrg_16">{ERR_MSG}</h4>
	<!-- ENDIF -->

	<div class="mrg_16">
	<table class="borderless bCenter">
	<tr>
		<td width="35%" align="right">{L_USERNAME}:</td>
		<td><input type="text" class="post" name="login_username" size="25" maxlength="40" value="{USERNAME}" <!-- IF ADMIN_LOGIN -->readonly="readonly" style="color: gray"<!-- ENDIF --> /></td>
	</tr>
	<tr>
		<td align="right">{L_PASSWORD}:</td>
		<td><input type="password" class="post" name="login_password" size="25" maxlength="32" /></td>
	</tr>
	<!-- IF CAPTCHA_HTML -->
	<tr>
		<td class="tRight nowrap">Код:</td>
		<td>{CAPTCHA_HTML}</td>
	</tr>
	<!-- ENDIF -->
	<tr>
		<td colspan="2" class="tCenter nowrap">{L_AUTO_LOGIN}: <input type="checkbox" name="autologin" <!-- IF ADMIN_LOGIN || AUTOLOGIN_DISABLED -->disabled="disabled"<!-- ELSE -->checked="checked"<!-- ENDIF --> /></td>
	</tr>
	<tr>
		<td colspan="2" class="warnColor1 tCenter" style="<!-- IF COOKIES_ERROR -->font-size: 24px;<!-- ENDIF -->">{L_COOKIES_REQUIRED}</td>
	</tr>
	<tr>
		<td colspan="2" class="tCenter pad_6"><input type="submit" name="login" class="bold long" value="{L_LOGIN}" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><a href="{U_SEND_PASSWORD}" class="small">{L_FORGOTTEN_PASSWORD}</a></td>
	</tr>
	</table>
	</div>

	</td>
</tr>
</table>

</form>
