<script type="text/javascript" src="/misc/js/bbcode.js"></script>
<script type="text/javascript">
    var ExternalLinks_InNewWindow = '{EXT_LINK_NEW_WIN}';
    var hidePostImg = false;
</script>

<form action="{S_CONFIG_ACTION}" method="post">
    <table class="forumline">
        <tr>
            <th>{L_TERMS}</th>
        </tr>
        <tr id="view_message"<!-- IF not PREVIEW_HTML --> class="hidden"<!-- ENDIF -->>
            <td class="row1">
                <div class="view-message">{PREVIEW_HTML}</div>
            </td>
        </tr>
        <tr>
            <td>
                <!-- INCLUDE posting_editor.tpl -->
            </td>
        </tr>
    </table>
</form>

<br clear="all"/>