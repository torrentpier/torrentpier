<style>
    .prof-tbl * {
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    input[name="username"], input[name="user_email"], input[name="cur_pass"], input[name="new_pass"] {
        width: 255px;
    }

    .prof-tbl td {
        padding: 4px 6px;
    }

    .prof-title {
        text-align: right;
    }

    .prof-tbl h6 {
        margin: 4px 0 4px 4px;
        color: #444444;
        line-height: 100%;
        display: inline-block;
    }
</style>
<script type="text/javascript">
    $(function () {
        var tab_idx = 100;
        $('input,select,textarea', '#prof-form').not(':hidden').not(':disabled').each(function () {
            $(this).attr({tabindex: ++tab_idx});
        });
    });

    ajax.callback.user_register = function (data) {
        $('#' + data.mode).html(data.html);
    };
</script>
<div id="autocomplete_popup">
    <div class="relative">
        <div class="close" onclick="$('div#autocomplete_popup').hide();"></div>
        <div class="title">{L_YOUR_NEW_PASSWORD}</div>
        <div>
            <input value="" autocomplete="off" type="text"/>
            <span class="regenerate" title="{L_REGENERATE}" onclick="autocomplete(true, {#PASSWORD_MIN_LENGTH#});"></span>
        </div>
    </div>
</div>
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form id="prof-form" method="post" action="profile.php<!-- IF IS_ADMIN && PR_USER_ID -->?{#POST_USERS_URL#}={PR_USER_ID}<!-- ENDIF -->"
      class="tokenized" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="{MODE}"/>
    <input type="hidden" name="reg_agreed" value="1"/>
    <!-- IF NEW_USER --><input type="hidden" name="admin" value="1"/><!-- ENDIF -->
    <!-- IF ADM_EDIT -->
    <input type="hidden" name="{#POST_USERS_URL#}" value="{PR_USER_ID}"/>
    <!-- ENDIF -->

    <table class="forumline prof-tbl">
        <col class="row1" width="35%">
        <col class="row2" width="65%">
        <tbody class="pad_4">
        <tr>
            <th colspan="2">{L_REGISTRATION_INFO}</th>
        </tr>
        <tr>
            <td class="row2 small tCenter" colspan="2">{L_ITEMS_REQUIRED}</td>
        </tr>
        <tr>
            <td class="prof-title">{L_USERNAME}: *</td>
            <td><!-- IF CAN_EDIT_USERNAME --><input id="username" onBlur="ajax.exec({ action: 'user_register', mode: 'check_name', username: $('#username').val()}); return false;" type="text" name="username" size="35" maxlength="25" value="{USERNAME}"/><!-- ELSE --><b>{USERNAME}</b><!-- ENDIF -->&nbsp;<span id="check_name"></span></td>
        </tr>
        <tr>
            <td class="prof-title">{L_EMAIL}: * <!-- IF EDIT_PROFILE --><!-- ELSE IF $bb_cfg['reg_email_activation'] --><br/><h6>{L_EMAIL_EXPLAIN}</h6><!-- ENDIF --></td>
            <td><input id="email" onBlur="ajax.exec({ action: 'user_register', mode: 'check_email', email: $('#email').val()}); return false;" type="text" name="user_email" size="35" maxlength="40" value="{USER_EMAIL}"<!-- IF EDIT_PROFILE and not ADM_EDIT and not IS_ADMIN --><!-- IF !$bb_cfg['emailer']['enabled'] or $bb_cfg['email_change_disabled'] --> readonly style="color: gray;"<!-- ENDIF --><!-- ENDIF --> />&nbsp;<span id="check_email"></span></td>
        </tr>
        <!-- IF EDIT_PROFILE and not ADM_EDIT -->
        <tr>
            <td class="prof-title">{L_CURRENT_PASSWORD}: * <br/><h6>{L_CONFIRM_PASSWORD_EXPLAIN}</h6></td>
            <td><input class="show_pass_input" type="password" name="cur_pass" size="35" maxlength="32" value=""/>&nbsp;<label><input type="checkbox" class="password_show_checkbox">&nbsp;{L_PASSWORD_SHOW_BTN}</label></td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <td class="prof-title"><!-- IF EDIT_PROFILE -->{L_NEW_PASSWORD}: * <br/><h6>{L_PASSWORD_IF_CHANGED}</h6><!-- ELSE -->{L_PASSWORD}: *<!-- ENDIF --></td>
            <td>
                <input id="pass" type="<!-- IF SHOW_PASS -->text<!-- ELSE -->password<!-- ENDIF -->" name="new_pass" size="35" maxlength="32" value=""/>&nbsp;<span id="autocomplete" data-password-length="{#PASSWORD_MIN_LENGTH#}" title="{L_AUTOCOMPLETE}">&#9668;</span>&nbsp;<i class="med">{PASSWORD_LONG}</i>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_CONFIRM_PASSWORD}: * <!-- IF EDIT_PROFILE --><br/>
                <h6>{L_PASSWORD_CONFIRM_IF_CHANGED}</h6><!-- ENDIF --></td>
            <td>
                <input id="pass_confirm" onBlur="ajax.exec({ action: 'user_register', mode: 'check_pass', pass: $('#pass').val(), pass_confirm: $('#pass_confirm').val() }); return false;" type="<!-- IF SHOW_PASS -->text<!-- ELSE -->password<!-- ENDIF -->" name="cfm_pass" size="35" maxlength="32" value=""/>&nbsp;<span id="check_pass"></span>
            </td>
        </tr>
        <!-- IF $bb_cfg['invites_system']['enabled'] and not EDIT_PROFILE -->
        <tr>
            <td class="prof-title">{L_INVITE_CODE}: *</td>
            <td><input type="text" name="invite_code" size="35" value="{INVITE_CODE}"/></td>
        </tr>
        <!-- ENDIF -->
        <!-- IF CAPTCHA_HTML -->
        <tr>
            <td class="prof-title">{L_CAPTCHA}: *</td>
            <td>{CAPTCHA_HTML}</td>
        </tr>
        <!-- ENDIF -->
        <!-- IF EDIT_PROFILE -->
        <!-- IF not ADM_EDIT -->
        <tr>
            <td class="prof-title">{L_AUTOLOGIN}:</td>
            <td><a href="{U_RESET_AUTOLOGIN}">{L_RESET_AUTOLOGIN}</a><br/><h6>{L_RESET_AUTOLOGIN_EXPL}</h6></td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <th colspan="2">{L_PROFILE_INFO}</th>
        </tr>
        <!-- IF $bb_cfg['gender'] -->
        <tr>
            <td class="prof-title">{L_GENDER}:</td>
            <td>{USER_GENDER}</td>
        </tr>
        <!-- ENDIF -->
        <!-- IF $bb_cfg['birthday_enabled'] -->
        <tr>
            <td class="prof-title">{L_BIRTHDAY}:</td>
            <td><input type="date" name="user_birthday" value="{USER_BIRTHDAY}"/></td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <td class="prof-title">{L_ICQ}:</td>
            <td><input type="text" name="user_icq" size="30" maxlength="15" value="{USER_ICQ}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_SKYPE}:</td>
            <td><input type="text" name="user_skype" size="30" maxlength="32" value="{USER_SKYPE}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_TWITTER}:</td>
            <td><input type="text" name="user_twitter" size="30" maxlength="15" value="{USER_TWITTER}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_WEBSITE}:</td>
            <td><input type="text" name="user_website" size="50" maxlength="100" value="{USER_WEBSITE}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_OCCUPATION}:</td>
            <td><input type="text" name="user_occ" size="50" maxlength="100" value="{USER_OCC}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_INTERESTS}:</td>
            <td><input type="text" name="user_interests" size="50" maxlength="150" value="{USER_INTERESTS}"/></td>
        </tr>
        <tr>
            <td class="prof-title">{L_LOCATION}:</td>
            <td>
                <label>{L_SET_OWN_COUNTRY}
                    <input {CHECKED_MANUAL_COUNTRY} name="user_from_set_manual" type="checkbox">
                </label>
                <hr/>
                <div id="country_select_hide">{COUNTRY_SELECT}&nbsp;<span id="check_country">{COUNTRY_SELECTED}</span></div>
                <div style="display: none;" id="country_manual_select"><input type="text" name="user_from" size="50" maxlength="150" value="{USER_FROM}"/></div>
            </td>
            <script type="text/javascript">
                $(document).ready(function () {
                    // Handle manual country select
                    const $manualCheckbox = $('input[name="user_from_set_manual"]');
                    const $countrySelectHide = $('div#country_select_hide');
                    const $countryManualSelect = $('div#country_manual_select');
                    function toggleCountrySelectors() {
                        if ($manualCheckbox.is(':checked')) {
                            $countrySelectHide.find('select').prop('disabled', true);
                            $countrySelectHide.hide();
                            $countryManualSelect.find('input').prop('disabled', false);
                            $countryManualSelect.show();
                        } else {
                            $countryManualSelect.find('input').prop('disabled', true);
                            $countryManualSelect.hide();
                            $countrySelectHide.find('select').prop('disabled', false);
                            $countrySelectHide.show();
                        }
                    }
                    toggleCountrySelectors();
                    $manualCheckbox.change(toggleCountrySelectors);

                    // Handle flag icon changing
                    $('#user_from').bind('change', function () {
                        ajax.exec({
                            action: 'user_register',
                            mode: 'check_country',
                            country: $(this).val()
                        });
                    });
                });
            </script>
        </tr>
        <!-- ENDIF -->
        <!-- IF $bb_cfg['allow_change']['language'] -->
        <tr>
            <td class="prof-title">{L_BOARD_LANG}:</td>
            <td>{LANGUAGE_SELECT}</td>
        </tr>
        <!-- ENDIF -->
        <!-- IF $bb_cfg['allow_change']['timezone'] -->
        <tr>
            <td class="prof-title">{L_SYSTEM_TIMEZONE}:</td>
            <td>{TIMEZONE_SELECT}</td>
        </tr>
        <!-- ENDIF -->
        <!-- IF EDIT_PROFILE -->
        <tr>
            <th colspan="2">{L_PREFERENCES}</th>
        </tr>
        <!-- IF TEMPLATES_SELECT -->
        <tr>
            <td class="prof-title">{L_FORUM_STYLE}:</td>
            <td>
                <div style="margin: 3px 0;">
                    {TEMPLATES_SELECT}
                </div>
            </td>
        </tr>
        <!-- ENDIF -->
        <!-- IF not SIG_DISALLOWED -->
        <tr id="view_message" class="hidden">
            <td class="prof-title">{L_PREVIEW}:</td>
            <td>
                <div class="signature"></div>
            </td>
        </tr>
        <script type="text/javascript">
            ajax.callback.posts = function (data) {
                $('#view_message').show();
                $('.signature').html(data.message_html);
                initPostBBCode('.signature');
            };
        </script>
        <!-- ENDIF -->
        <tr>
            <td class="prof-title">{L_SIGNATURE}: <br/><h6>{SIGNATURE_EXPLAIN}</h6></td>
            <!-- IF SIG_DISALLOWED -->
            <td class="tCenter">{L_SIGNATURE_DISABLE}</td>
            <!-- ELSE -->
            <td>
                <textarea id="user_sig" name="user_sig" rows="5" cols="60" style="width: 96%;">{USER_SIG}</textarea>
                <input type="button" value="{L_PREVIEW}" onclick="ajax.exec({ action: 'posts', type: 'view_message', message: $('textarea#user_sig').val() });">
            </td>
            <!-- ENDIF -->
        </tr>

        <!-- IF IS_ADMIN || $bb_cfg['show_email_visibility_settings'] -->
        <tr>
            <td class="prof-title">{L_PUBLIC_VIEW_EMAIL}:</td>
            <td>
                <label><input type="radio" name="user_viewemail" value="1" <!-- IF USER_VIEWEMAIL -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_viewemail" value="0" <!-- IF not USER_VIEWEMAIL -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <td class="prof-title">{L_HIDE_USER}:</td>
            <td>
                <label><input type="radio" name="user_viewonline" value="1" <!-- IF USER_VIEWONLINE -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_viewonline" value="0" <!-- IF not USER_VIEWONLINE -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_ALWAYS_NOTIFY}:<br/><h6>{L_ALWAYS_NOTIFY_EXPLAIN}</h6></td>
            <td>
                <label><input type="radio" name="user_notify" value="1" <!-- IF USER_NOTIFY -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_notify" value="0" <!-- IF not USER_NOTIFY -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <!-- IF $bb_cfg['pm_notify_enabled'] -->
        <tr>
            <td class="prof-title">{L_NOTIFY_ON_PRIVMSG}:</td>
            <td>
                <label><input type="radio" name="user_notify_pm" value="1" <!-- IF USER_NOTIFY_PM -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_notify_pm" value="0" <!-- IF not USER_NOTIFY_PM -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <!-- ENDIF -->
        <!-- IF SHOW_DATEFORMAT -->
        <tr>
            <td class="prof-title">{L_DATE_FORMAT}:<br/><h6>{L_DATE_FORMAT_EXPLAIN}</h6></td>
            <td><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="14"/></td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <th colspan="2">{L_UCP_DOWNLOADS}</th>
        </tr>
        <tr>
            <td class="prof-title">{L_HIDE_PORN_FORUMS}:</td>
            <td>
                <label><input type="radio" name="user_porn_forums" value="1" <!-- IF USER_PORN_FORUMS -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_porn_forums" value="0" <!-- IF not USER_PORN_FORUMS -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_ADD_RETRACKER}:</td>
            <td>
                <label><input type="radio" name="user_retracker" value="1" <!-- IF USER_RETRACKER -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_retracker" value="0" <!-- IF not USER_RETRACKER -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_HIDE_DOWNLOADS}:</td>
            <td>
                <label><input type="radio" name="user_dls" value="1" <!-- IF USER_DLS -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_dls" value="0" <!-- IF not USER_DLS -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_CALLSEED_EXPLAIN}:</td>
            <td>
                <label><input type="radio" name="user_callseed" value="1" <!-- IF USER_CALLSEED -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_callseed" value="0" <!-- IF not USER_CALLSEED -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <td class="prof-title">{L_HIDE_PEER_TORRENT_CLIENT}:</td>
            <td>
                <label><input type="radio" name="user_hide_torrent_client" value="1" <!-- IF USER_HIDE_TORRENT_CLIENT -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_hide_torrent_client" value="0" <!-- IF not USER_HIDE_TORRENT_CLIENT -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <!-- IF $bb_cfg['ip2country_settings']['enabled'] -->
        <tr>
            <td class="prof-title">{L_HIDE_PEER_COUNTRY_NAME}:</td>
            <td>
                <label><input type="radio" name="user_hide_peer_country" value="1" <!-- IF USER_HIDE_PEER_COUNTRY -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_hide_peer_country" value="0" <!-- IF not USER_HIDE_PEER_COUNTRY -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <!-- ENDIF -->
        <tr>
            <td class="prof-title">{L_HIDE_PEER_USERNAME}:</td>
            <td>
                <label><input type="radio" name="user_hide_peer_username" value="1" <!-- IF USER_HIDE_PEER_USERNAME -->checked<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
                <label><input type="radio" name="user_hide_peer_username" value="0" <!-- IF not USER_HIDE_PEER_USERNAME -->checked<!-- ENDIF --> />{L_NO}</label>
            </td>
        </tr>
        <tr>
            <th colspan="2">{L_AVATAR_PANEL}</th>
        </tr>
        <!-- IF AVATAR_DISALLOWED -->
        <tr>
            <td colspan="2" class="tCenter pad_12">{AVATAR_DIS_EXPLAIN}</td>
        </tr>
        <!-- ELSE -->
        <tr>
            <td colspan="2">
                <table class="borderless bCenter med" style="width: 600px;">
                    <col class="w60">
                    <col class="w40">
                    <tr>
                        <td>
                            {AVATAR_EXPLAIN}
                            <!-- IF $bb_cfg['avatars']['up_allowed'] -->
                            <div class="spacer_4"></div>
                            {L_UPLOAD_AVATAR_FILE}:
                            <input type="hidden" name="MAX_FILE_SIZE" value="{$bb_cfg['avatars']['max_size']}"/>
                            <input type="file" name="avatar" accept="image/*"/>
                            <!-- ENDIF -->
                        </td>
                        <td class="tCenter nowrap">
                            <p class="mrg_6">{AVATAR_IMG}</p>
                            <p><label><input type="checkbox" name="delete_avatar"/> {L_DELETE_IMAGE}</label></p>
                            <p><label><input type="checkbox" name="use_monster_avatar"/> {L_SET_MONSTERID_AVATAR}</label></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- ENDIF / !AVATAR_DISALLOWED -->
        <!-- ENDIF / EDIT_PROFILE -->

        <!-- IF SHOW_REG_AGREEMENT -->
        <tr>
            <td class="row2" colspan="2">
                <div id="infobox-wrap" class="bCenter row1">
                    <fieldset class="pad_6">
                        <legend class="med bold mrg_2 warnColor1">{L_USER_AGREEMENT_HEAD}</legend>
                        <div class="bCenter">
                            <?php include($V['HTML_AGREEMENT']); ?>
                        </div>
                        <p class="med bold mrg_4 tCenter"><label><input type="checkbox" value="" onclick="toggle_disabled('agreement', this.checked)"/>&nbsp;{L_USER_AGREEMENT_AGREE}</label></p>
                    </fieldset>
                </div><!--/infobox-wrap-->
            </td>
        </tr>
        <!-- ENDIF / SHOW_REG_AGREEMENT -->

        <tr>
            <td class="catBottom" colspan="2">
                <div id="submit-buttons">
                    <!-- IF EDIT_PROFILE -->
                    <input type="reset" value="{L_RESET}" name="reset" class="lite"/>&nbsp;&nbsp;
                    <!-- ENDIF -->
                    <input type="submit" <!-- IF SHOW_REG_AGREEMENT -->id="agreement" disabled<!-- ENDIF --> name="submit" value="{L_SUBMIT}" class="main"/>
                </div>
            </td>
        </tr>

        </tbody>
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
