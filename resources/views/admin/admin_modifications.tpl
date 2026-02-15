<style>
    .mod-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        vertical-align: middle;
    }
    .mod-badge-version {
        display: inline-block;
        padding: 1px 5px;
        font-size: 10px;
        background: #e0edff;
        color: #2563eb;
        border-radius: 3px;
        vertical-align: middle;
    }
    .mod-badge-free {
        display: inline-block;
        padding: 1px 5px;
        font-size: 10px;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 3px;
        vertical-align: middle;
    }
    .mod-badge-paid {
        display: inline-block;
        padding: 1px 5px;
        font-size: 10px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 3px;
        vertical-align: middle;
    }
    .mod-badge-other {
        display: inline-block;
        padding: 1px 5px;
        font-size: 10px;
        background: #fef9c3;
        color: #a16207;
        border-radius: 3px;
        vertical-align: middle;
    }
    .mod-install-btn {
        padding: 3px 10px;
        font-size: 11px;
        background: #ccc;
        color: #666;
        border: 1px solid #999;
        border-radius: 3px;
        cursor: not-allowed;
    }
    .mod-tagline {
        font-size: 11px;
        color: #888;
    }
    .mod-error {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        margin: 12px 0;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        border-radius: 6px;
        color: #991b1b;
        font-size: 13px;
        line-height: 1.4;
    }
    .mod-error-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        background: #ef4444;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: bold;
    }
    .mod-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #d1d5db;
        color: #fff;
        font-size: 11px;
        font-weight: bold;
        vertical-align: middle;
    }
    .mod-stars {
        display: inline-block;
        position: relative;
        font-size: 13px;
        line-height: 1;
        letter-spacing: 1px;
    }
    .mod-stars-empty {
        color: #d1d5db;
    }
    .mod-stars-filled {
        position: absolute;
        top: 0;
        left: 0;
        overflow: hidden;
        white-space: nowrap;
        color: #f59e0b;
    }
</style>

<h1>{L_MODIFICATIONS_LIST}</h1>

<p class="gen" style="margin: 4px 0 12px 0;">{L_MODS_DESCRIPTION}</p>

<!-- IF HAS_ERROR -->
<div class="mod-error">
    <span class="mod-error-icon">!</span>
    <span>{API_ERROR}</span>
</div>
<!-- ELSE -->

<table class="bordered w100" cellspacing="0">
    <tr>
        <td class="row1" style="padding: 8px;">
            <form action="{S_MODS_ACTION}" method="get" style="display:inline;">
                <table class="borderless" cellspacing="0" style="margin: 0;">
                    <tr>
                        <td class="med nowrap" style="padding: 2px 12px 2px 0;"><b>{L_MODS_TOTAL}:</b> {TOTAL_RESOURCES}</td>
                        <td class="med nowrap" style="padding: 2px 12px;">
                            <b>{L_MODS_CATEGORY}:</b> {SEL_CATEGORY}
                            <input type="submit" class="liteoption" value="{L_SEARCH}" />
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>

<div class="spacer_8"></div>

<table class="forumline">
    <tr>
        <th>{L_MODIFICATIONS_LIST}</th>
        <th>{L_MODS_AUTHOR}</th>
        <th>{L_MODS_DOWNLOADS}</th>
        <th>{L_MODS_RATING}</th>
        <th>{L_MODS_LAST_UPDATED}</th>
        <th>{L_MODS_ACTIONS}</th>
    </tr>
    <!-- BEGIN resource -->
    <tr class="{resource.ROW_CLASS}">
        <td class="med" style="padding: 6px;">
            <b><a href="{resource.VIEW_URL}" target="_blank" rel="noopener noreferrer">{resource.TITLE}</a></b>
            <span class="mod-badge-version">{resource.VERSION}</span>
            <span class="mod-badge-{resource.CATEGORY_CLASS}">{resource.CATEGORY_TITLE}</span>
            <br /><span class="mod-tagline">{resource.TAG_LINE}</span>
        </td>
        <td class="tCenter" style="padding: 6px;">
            <!-- IF resource.AVATAR_URL -->
            <img class="mod-avatar" src="{resource.AVATAR_URL}" alt="" /><br />
            <!-- ELSE -->
            <span class="mod-avatar-placeholder">{resource.USERNAME_INITIAL}</span><br />
            <!-- ENDIF -->
            <span class="small">{resource.USERNAME}</span>
        </td>
        <td class="tCenter med">{resource.DOWNLOAD_COUNT}</td>
        <td class="tCenter med nowrap">
            <span class="mod-stars" aria-hidden="true">
                <span class="mod-stars-empty">★★★★★</span>
                <span class="mod-stars-filled" style="width:{resource.RATING_PERCENT}%">★★★★★</span>
            </span>
            <br /><span class="small">{resource.RATING_AVG} &middot; {resource.RATING_COUNT_TEXT}</span>
        </td>
        <td class="tCenter small nowrap">{resource.LAST_UPDATE}</td>
        <td class="tCenter"><button class="mod-install-btn" disabled title="{L_MODS_INSTALL_SOON}">{L_MODS_INSTALL}</button></td>
    </tr>
    <!-- END resource -->
    <!-- BEGIN no_resources -->
    <tr>
        <td class="row1 tCenter pad_12" colspan="6"><span class="gen">{L_MODS_NO_RESOURCES}</span></td>
    </tr>
    <!-- END no_resources -->
    <tr>
        <td class="spaceRow" colspan="6">
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

<!-- ENDIF -->

<div class="spacer_8"></div>
<div class="spacer_8"></div>
<div class="spacer_8"></div>
