<style>
    .spam-details {
        display: none;
        max-height: 300px;
        overflow: auto;
        padding: 6px;
        margin-top: 4px;
        background: #f5f5f5;
        border: 1px solid #ddd;
        font-family: monospace;
        font-size: 11px;
        white-space: pre-wrap;
        word-break: break-all;
    }
</style>

<h1>{L_SPAM_LOG}</h1>

<table class="bordered w100" cellspacing="0">
    <tr>
        <td class="row1" style="padding: 8px;">
            <p class="med">{L_SPAM_LOG_DESC}</p>
            <div class="spacer_4"></div>
            <table class="borderless" cellspacing="0" style="margin: 0;">
                <tr>
                    <td class="med nowrap" style="padding: 2px 12px 2px 0;"><b>{L_SPAM_LOG_TOTAL}:</b> {STATS_TOTAL}</td>
                    <td class="med nowrap" style="padding: 2px 12px;"><b class="leechmed">{L_SPAM_LOG_DENIED_COUNT}:</b> {STATS_DENIED}</td>
                    <td class="med nowrap" style="padding: 2px 12px;"><b class="seedmed">{L_SPAM_LOG_MODERATED_COUNT}:</b> {STATS_MODERATED}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="spacer_8"></div>

<form action="{S_SPAM_LOG_ACTION}" method="get">
    <table class="bordered w100" cellspacing="0">
        <tr>
            <th class="thHead">{L_SEARCH}</th>
        </tr>
        <tr>
            <td class="row1 tCenter">
                <table class="fieldsets borderless bCenter pad_0" cellspacing="0">
                    <tr>
                        <td valign="top" class="row1">
                            <fieldset>
                                <legend>{L_SPAM_LOG_DECISION}</legend>
                                <div><p class="select">{SEL_DECISION}</p></div>
                            </fieldset>
                        </td>
                        <td valign="top" class="row1">
                            <fieldset>
                                <legend>{L_SPAM_LOG_CHECK_TYPE}</legend>
                                <div><p class="select">{SEL_TYPE}</p></div>
                            </fieldset>
                        </td>
                        <td valign="top" class="row1">
                            <fieldset>
                                <legend>{L_SPAM_LOG_PROVIDER}</legend>
                                <div><p class="select">{SEL_PROVIDER}</p></div>
                            </fieldset>
                        </td>
                        <td valign="top" class="row1">
                            <fieldset>
                                <legend>IP</legend>
                                <div>
                                    <p class="input">
                                        <input class="post" type="text" size="16" maxlength="45" name="{IP_NAME}" value="{IP_VAL}" />
                                    </p>
                                </div>
                            </fieldset>
                        </td>
                        <td valign="top" class="row1">
                            <fieldset style="height: 45px; width: 110px;">
                                <legend>{L_SORT_BY}</legend>
                                <div>
                                    <p class="select nowrap">
                                        <label>
                                            <input type="radio" name="{SORT_NAME}" value="{SORT_ASC}" {SORT_ASC_CHECKED} /> {L_ASC}
                                        </label>
                                        <label>
                                            <input type="radio" name="{SORT_NAME}" value="{SORT_DESC}" {SORT_DESC_CHECKED} /> {L_DESC}
                                        </label>
                                    </p>
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="catBottom" style="padding: 0; height: 26px">
                <span class="med">
                    <input type="reset" value="{L_CANCEL}" class="liteoption" />
                    <input type="submit" class="liteoption" value="{L_SEARCH}" name="submit" />
                </span>
            </td>
        </tr>
    </table>
</form>

<div class="spacer_8"></div>

<table class="forumline">
    <tr>
        <th>{L_SPAM_LOG_CHECK_TYPE}</th>
        <th>IP</th>
        <th>{L_EMAIL} / {L_USERNAME}</th>
        <th>{L_SPAM_LOG_DECISION}</th>
        <th>{L_SPAM_LOG_PROVIDER}</th>
        <th>{L_SPAM_LOG_REASON}</th>
        <th>{L_SPAM_LOG_RESPONSE_TIME}</th>
        <th>{L_DATE}</th>
        <th>{L_SPAM_LOG_DETAILS}</th>
    </tr>
    <!-- BEGIN log -->
    <tr class="{log.ROW_CLASS}">
        <td class="med tCenter">
            {log.CHECK_TYPE}
            <!-- IF log.POST_ID -->
            <div class="small"><a href="{log.POST_LINK}" target="_blank">#p{log.POST_ID}</a></div>
            <!-- ENDIF -->
        </td>
        <td class="small tCenter nowrap">{log.CHECK_IP}</td>
        <td class="med tCenter" style="line-height: 14px;">
            <!-- IF log.CHECK_EMAIL -->
            <div class="small"><i>{log.CHECK_EMAIL}</i></div>
            <!-- ENDIF -->
            <!-- IF log.USER_PROFILE -->
            <div class="med">{log.USER_PROFILE}</div>
            <!-- ELSEIF log.CHECK_USERNAME -->
            <div class="med"><b>{log.CHECK_USERNAME}</b></div>
            <!-- ENDIF -->
        </td>
        <td class="med tCenter"><b class="{log.DECISION_CLASS}">{log.DECISION}</b></td>
        <td class="small tCenter">{log.PROVIDER_NAME}</td>
        <td class="small">{log.REASON}</td>
        <td class="small tCenter">{log.TOTAL_TIME_MS}</td>
        <td class="small tCenter nowrap">{log.CHECK_TIME}</td>
        <td class="small tCenter">
            <!-- IF log.DETAILS -->
            <a href="#" onclick="var el=document.getElementById('details_{log.ROW_ID}');el.style.display=el.style.display==='block'?'none':'block';return false;">{L_SPAM_LOG_DETAILS}</a>
            <div id="details_{log.ROW_ID}" class="spam-details">{log.DETAILS}</div>
            <!-- ENDIF -->
        </td>
    </tr>
    <!-- END log -->
    <!-- BEGIN log_not_found -->
    <tr>
        <td class="row1 tCenter pad_12" colspan="{LOG_COLSPAN}"><span class="gen">{L_SPAM_LOG_NO_RECORDS}</span></td>
    </tr>
    <!-- END log_not_found -->
    <tr>
        <td class="spaceRow" colspan="{LOG_COLSPAN}">
            <div class="spacer_4"></div>
        </td>
    </tr>
</table>

<!-- IF PAGINATION -->
<div class="nav" style="margin: 8px 4px 14px 4px">
    <div style="float:left">{PAGE_NUMBER}</div>
    <div style="float:right">{PAGINATION}</div>
</div>
<div class="clear"></div>
<!-- ENDIF -->

<div class="spacer_8"></div>
<div class="spacer_8"></div>
<div class="spacer_8"></div>
