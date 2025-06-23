<h1 class="pagetitle">{PAGE_TITLE}</h1>

<form method="post" action="{S_MODE_ACTION}" name="post">
    <table width="100%">
        <tr>
            <td class="med nowrap tRight">{L_SORT_BY}:&nbsp;{S_MODE_SELECT}&nbsp;&middot;&nbsp;{L_ORDER}:&nbsp;{S_ORDER_SELECT}&nbsp;&middot;&nbsp;{L_ROLE}&nbsp;{S_ROLE_SELECT}&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}"></td>
        </tr>
        <tr>
            <td class="nowrap tRight">
			    <span class="genmed">
				    <input placeholder="{L_SEARCH_S}" type="text" class="post" name="username" maxlength="25" size="25" tabindex="1" value="{S_USERNAME}">&nbsp;<input type="submit" name="submituser" value="{L_FIND_USERNAME}" class="mainoption">
			    </span>
            </td>
        </tr>
    </table>
</form>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{L_USERNAME}</th>
        <th>{L_PM}</th>
        <th>{L_EMAIL}</th>
        <th>{L_LOCATION}</th>
        <th>{L_JOINED}</th>
        <th>{L_POSTS_SHORT}</th>
        <th>{L_WEBSITE}</th>
    </tr>
    </thead>
    <!-- BEGIN memberrow -->
    <tr class="{memberrow.ROW_CLASS} tCenter">
        <td>{memberrow.ROW_NUMBER}</td>
        <td>
            <div>{memberrow.AVATAR}</div>
            <b>{memberrow.USER}</b>
        </td>
        <td>{memberrow.PM}</td>
        <td>{memberrow.EMAIL}</td>
        <td>{memberrow.FROM}</td>
        <td>{memberrow.JOINED}</td>
        <td>{memberrow.POSTS}</td>
        <td>{memberrow.WWW}</td>
    </tr>
    <!-- END memberrow -->
    <!-- BEGIN no_username -->
    <tbody>
    <tr>
        <td class="row1 tCenter pad_8" colspan="9">{no_username.NO_USER_ID_SPECIFIED}</td>
    </tr>
    </tbody>
    <!-- END no_username -->
    <tfoot>
    <tr>
        <td class="catBottom" colspan="9">&nbsp;</td>
    </tr>
    </tfoot>
</table>

<div class="bottom_info">
    <!-- IF PAGINATION -->
    <div class="nav">
        <p style="float: left">{PAGE_NUMBER}</p>
        <p style="float: right">{PAGINATION}</p>
        <div class="clear"></div>
    </div>
    <!-- ENDIF -->

    <div class="spacer_4"></div>

    <div id="timezone">
        <p>{CURRENT_TIME}</p>
        <p>{S_TIMEZONE}</p>
    </div>
    <div class="clear"></div>
</div><!--/bottom_info-->
