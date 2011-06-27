
<script type="text/javascript">
ajax.callback.user_register = function(data){
	$('#'+ data.mode).html(data.html);
};
</script>

<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form method="post" action="profile.php" class="tokenized" enctype="multipart/form-data">
<input type="hidden" name="mode" value="{MODE}" />
<input type="hidden" name="reg_agreed" value="1" />
<!-- IF ADM_EDIT -->
<input type="hidden" name="u" value="{PR_USER_ID}" />
<!-- ENDIF -->

<table class="forumline usercp_register">
<col class="row1" width="35%">
<col class="row2" width="65%">
<tbody class="pad_4">
<tr>
	<th colspan="2">Регистрационная информация</th>
</tr>
<tr>
	<td class="row2 small" colspan="2">Поля отмеченные * обязательны к заполнению</td>
</tr>
<tr>
	<td>Имя: *</td>
	<td><!-- IF CAN_EDIT_USERNAME --><input id="username" onBlur="ajax.exec({ action: 'user_register', mode: 'check_name', username: $('#username').val()}); return false;" type="text" name="username" size="35" maxlength="25" value="{USERNAME}" /><!-- ELSE --><b>{USERNAME}</b><!-- ENDIF -->
    &nbsp;<span id="check_name"></span></td>
</tr>
<tr>
	<td>Адрес email: * <!-- IF EDIT_PROFILE --><!-- ELSE --><h6>На этот адрес вам будет отправлено письмо для завершения регистрации</h6><!-- ENDIF --></td>
	<td><input id="email" onBlur="ajax.exec({ action: 'user_register', mode: 'check_email', email: $('#email').val()}); return false;" type="text" name="user_email" size="35" maxlength="40" value="{USER_EMAIL}" <!-- IF EDIT_PROFILE --><!-- IF $bb_cfg['email_change_disabled'] -->readonly="readonly" style="color: gray;"<!-- ENDIF --><!-- ENDIF --> />
	    <span id="check_email"></span></td>
</tr>
<!-- IF EDIT_PROFILE and not ADM_EDIT -->
<tr>
	<td>Текущий пароль: * <h6>Вы должны указать ваш текущий пароль, если хотите изменить его или поменять свой e-mail</h6></td>
	<td><input type="password" name="cur_pass" size="35" maxlength="20" value="" /></td>
</tr>
<!-- ENDIF -->
<tr>
	<td><!-- IF EDIT_PROFILE -->Новый пароль: * <h6>Указывайте пароль только если вы хотите его поменять</h6><!-- ELSE -->Пароль: *<!-- ENDIF --></td>
	<td><input id="pass" type="<!-- IF SHOW_PASS -->text<!-- ELSE -->password<!-- ENDIF -->" name="new_pass" size="35" maxlength="20" value="" /> &nbsp;<i class="med">максимум 20 символов</i></td>
</tr>
<tr>
	<td>Подтвердите пароль: * <!-- IF EDIT_PROFILE --><h6>Подтверждать пароль нужно в том случае, если вы изменили его выше</h6><!-- ENDIF --></td>
	<td><input id="pass_confirm" onBlur="ajax.exec({ action: 'user_register', mode: 'check_pass', pass: $('#pass').val(), pass_confirm: $('#pass_confirm').val() }); return false;" type="<!-- IF SHOW_PASS -->text<!-- ELSE -->password<!-- ENDIF -->" name="cfm_pass" size="35" maxlength="20" value="" />
	    <span id="check_pass"></span></td>
</tr>
<!-- IF CAPTCHA_HTML -->
<tr>
	<td>Код подтверждения:</td>
	<td>{CAPTCHA_HTML}</td>
</tr>
<!-- ENDIF -->
<!-- IF EDIT_PROFILE -->
<!-- IF not ADM_EDIT -->
<tr>
	<td>{L_AUTOLOGIN}:</td>
	<td><a href="{U_RESET_AUTOLOGIN}">{L_RESET_AUTOLOGIN}</a><h6>{L_RESET_AUTOLOGIN_EXPL}</h6></td>
</tr>
<!-- ENDIF -->
<!-- BEGIN switch_bittorrent -->
<tr>
	<th colspan="2"><a name="bittorrent"></a>TorrentPier</th>
</tr>
<tr>
	<td>{L_GEN_PASSKEY}<h6>{L_GEN_PASSKEY_EXPLAIN}</h6></td>
	<td class="med">{L_GEN_PASSKEY_EXPLAIN_2}<br />{S_GEN_PASSKEY}</td>
</tr>
<tr>
	<td>{L_CURR_PASSKEY}</td>
	<td class="med">{CURR_PASSKEY}</td>
</tr>
<!-- END switch_bittorrent -->
<tr>
	<th colspan="2">Профиль</th>
</tr>
<tr>
	<td>Пол:</td>
	<td>
		<select name="user_gender" id="user_gender">
			<option value="0" <!-- IF USER_GENDER_0 -->selected="selected"<!-- ENDIF -->>&nbsp;Не определилось&nbsp;</option>
			<option value="1" <!-- IF USER_GENDER_1 -->selected="selected"<!-- ENDIF -->>&nbsp;Мужской&nbsp;</option>
			<option value="2" <!-- IF USER_GENDER_2 -->selected="selected"<!-- ENDIF -->>&nbsp;Женский&nbsp;</option>
		</select>
	</td>
</tr>
<tr>
	<td>ICQ:</td>
	<td><input type="text" name="user_icq" size="30" maxlength="15" value="{USER_ICQ}" /></td>
</tr>
<tr>
	<td>CommFort:</td>
	<td><input type="text" name="user_commfort" size="30" maxlength="15" value="{USER_COMMFORT}" /></td>
</tr>
<tr>
	<td>Skype:</td>
	<td><input type="text" name="user_skype" size="30" maxlength="15" value="{USER_SKYPE}" /></td>
</tr>
<tr>
	<td>Сайт:</td>
	<td><input type="text" name="user_website" size="50" maxlength="100" value="{USER_WEBSITE}" /></td>
</tr>
<tr>
	<td>Род занятий:</td>
	<td><input type="text" name="user_occ" size="50" maxlength="100" value="{USER_OCC}" /></td>
</tr>
<tr>
	<td>Интересы:</td>
	<td><input type="text" name="user_interests" size="50" maxlength="150" value="{USER_INTERESTS}" /></td>
</tr>
<tr>
	<td>Откуда:</td>
	<td>
		<div><input type="text" name="user_from" size="50" maxlength="100" value="{USER_FROM}" /></div>
	</td>
</tr>
<!-- ENDIF -->
<tr>
	<td>Часовой пояс:</td>
	<td>{TIMEZONE_SELECT}</td>
</tr>
<!-- IF EDIT_PROFILE -->
<tr>
	<th colspan="2">Личные настройки</th>
</tr>
<!-- IF SIG_DISALLOWED -->
<tr>
	<td colspan="2" class="tCenter pad_12">Опция управления подписью отключена за нарушение <a href="{$bb_cfg['terms_and_conditions_url']}"><b>правил форума</b></a></td>
</tr>
<!-- ELSE -->
<tr>
	<td>Подпись:<h6>максимум {$bb_cfg['max_sig_chars']} символов</h6></td>
	<td><textarea name="user_sig" rows="5" cols="60" style="width: 96%;">{USER_SIG}</textarea></td>
</tr>
<!-- ENDIF -->

<!-- IF $bb_cfg['pm_notify_enabled'] -->
<tr>
	<td>Уведомлять о новых личных сообщениях:</td>
	<td>
		<label><input type="radio" name="notify_pm" value="1" <!-- IF NOTIFY_PM -->checked="checked"<!-- ENDIF --> />	Да</label>&nbsp;&nbsp;
		<label><input type="radio" name="notify_pm" value="0" <!-- IF not NOTIFY_PM -->checked="checked"<!-- ENDIF --> />	Нет</label>
	</td>
</tr>
<!-- ENDIF -->
<!-- IF $bb_cfg['porno_forums'] -->
<tr>
	<td>{$bb_cfg['lang_hide_porno_forums']}:</td>
	<td>
		<label><input type="radio" name="hide_porn_forums" value="1" <!-- IF HIDE_PORN_FORUMS -->checked="checked"<!-- ENDIF --> />	Да</label>&nbsp;&nbsp;
		<label><input type="radio" name="hide_porn_forums" value="0" <!-- IF not HIDE_PORN_FORUMS -->checked="checked"<!-- ENDIF --> />	Нет</label>
	</td>
</tr>
<!-- ENDIF -->


<!-- ENDIF / EDIT_PROFILE -->

<!-- IF SHOW_REG_AGREEMENT -->
<tr>
	<td class="row2" colspan="2">
	<style type="text/css">
	#infobox-wrap { width: 740px; }
	#infobox-body {
		background: #FFFFFF; color: #000000; padding: 1em;
		height: 300px; overflow: auto; border: 1px inset #000000;
	}
	</style>
	<div id="infobox-wrap" class="bCenter row1">
		<fieldset class="pad_6">
		<legend class="med bold mrg_2 warnColor1">Для продолжения регистрации Вы должны принять наше ПОЛЬЗОВАТЕЛЬСКОЕ СОГЛАШЕНИЕ</legend>
			<div class="bCenter">
				<?php include($bb_cfg['user_agreement_html_path']) ?>
			</div>
			<p class="med bold mrg_4 tCenter"><label><input type="checkbox" value="" checked="checked" disabled="disabled" /> Я прочел ПОЛЬЗОВАТЕЛЬСКОЕ СОГЛАШЕНИЕ и обязуюсь его не нарушать</label></p>
		</fieldset>
	</div><!--/infobox-wrap-->
	</td>
</tr>
<!-- ENDIF / SHOW_REG_AGREEMENT -->

<tr>
	<td class="catBottom" colspan="2">
	<div>
		<!-- IF EDIT_PROFILE --><input type="reset" value="Вернуть" name="reset" /> &nbsp; <!-- ENDIF -->
		<input type="submit" name="submit" value="Отправить<!-- IF SHOW_REG_AGREEMENT --> (Я согласен с условиями)<!-- ENDIF -->" class="bold" />
	</div>
	</td>
</tr>

</tbody>
</table>

</form>