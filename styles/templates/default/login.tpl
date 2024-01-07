<h1 class="pagetitle">{PAGE_TITLE}</h1>

<form action="{S_LOGIN_ACTION}" method="post">

    <input type="hidden" name="redirect" value="{REDIRECT_URL}"/>
    <!-- IF ADMIN_LOGIN --><input type="hidden" name="admin" value="1"/><!-- ENDIF -->

    <p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

    <table class="forumline">
        <tr>
            <th>{L_ENTER_PASSWORD}</th>
        </tr>
        <tr>
            <td class="row1">
                <!-- IF ADMIN_LOGIN -->
                <h4 class="tCenter mrg_16">{L_ADMIN_REAUTHENTICATE}</h4>
                <!-- ENDIF -->
                <div class="mrg_16">
                    <table class="borderless bCenter">
                        <tr>
                            <td width="35%" class="tRight">{L_USERNAME}:</td>
                            <td><input type="text" class="post" name="login_username" size="25" maxlength="40" value="{LOGIN_USERNAME}" tabindex="101"<!-- IF ADMIN_LOGIN --> readonly style="color: gray"<!-- ENDIF --> />
                            </td>
                        </tr>
                        <tr>
                            <td class="tRight">{L_PASSWORD}:</td>
                            <td><input type="password" class="post show_pass_input" name="login_password" value="{LOGIN_PASSWORD}" tabindex="102" size="25" maxlength="32"/>&nbsp;<label><input type="checkbox" class="password_show_checkbox">&nbsp;{L_PASSWORD_SHOW_BTN}</label></td>
                        </tr>
                        <!-- IF CAPTCHA_HTML -->
                        <tr>
                            <td class="tRight nowrap">{L_CAPTCHA}:</td>
                            <td>{CAPTCHA_HTML}</td>
                        </tr>
                        <!-- ENDIF -->
                        <tr>
                            <td colspan="2" class="tCenter nowrap">{L_AUTO_LOGIN}:&nbsp;<input type="checkbox" name="autologin" tabindex="103" <!-- IF ADMIN_LOGIN || AUTOLOGIN_DISABLED -->disabled<!-- ELSE -->checked<!-- ENDIF --> /></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="tCenter pad_6"><input type="submit" name="login" class="bold long" value="{L_LOGIN}"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="tCenter"><a href="{U_SEND_PASSWORD}" class="small">{L_FORGOTTEN_PASSWORD}</a>&nbsp;&middot;&nbsp;<a href="{U_REGISTER}" class="small">{L_REGISTER}</a></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    $('body').on('click', '.password_show_checkbox', function () {
        if ($(this).is(':checked')) {
            $('input.show_pass_input').attr('type', 'text');
        } else {
            $('input.show_pass_input').attr('type', 'password');
        }
    });
</script>
