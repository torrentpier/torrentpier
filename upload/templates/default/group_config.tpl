<script type="text/javascript">
    function manage_group(mode, value){
        ajax.exec({
            action   : 'manage_group',
            mode     : mode,
            group_id : {GROUP_ID},
            value    : value
        });
        ajax.callback.manage_group = function(data){
            console.log(data);
        }
    }
</script>

<h1 class="pagetitle">{PAGE_TITLE}<!-- IF GROUP_NAME --> :: {GROUP_NAME}<!-- ENDIF --></h1>
<p class="nav"><a href="{U_GROUP_URL}">{L_GROUP_RETURN}</a></p>
<form action="{S_GROUPCP_ACTION}" method="post">
    {S_HIDDEN_FIELDS}
<table class="forumline pad_4">
    <col class="row1" width="20%">
    <col class="row2" width="100%">
    <tr>
        <th colspan="2">{L_GROUP_INFORMATION}</th>
    </tr>
    <tr>
        <td>{L_GROUP_NAME}:</td>
        <td><input type="text" id="group_name" size="80" value="{GROUP_NAME}" onblur="javascript:manage_group(this.id, this.value, this.id);" /></td>
    </tr>
    <tr>
        <td>{L_GROUP_DESCRIPTION}:</td>
        <td><div id="preview"></div>
            <p>
                <textarea cols="80" id="group_description" rows="6" >{GROUP_DESCRIPTION}</textarea>
            </p>
            <p>
                <input type="button" value="{L_AJAX_PREVIEW}" onclick="ajax.exec({ action: 'posts', type: 'view_message', message: $('textarea#group_description').val()});ajax.callback.posts=function(data){$('div#preview').show().html(data.message_html)}">
                <input type="button" value="{L_SAVE}" onclick="javascript:manage_group('group_description',$('textarea#group_description').val())">
            </p>
        </td>
    </tr>
    <tr>
        <td>{L_GROUP_TYPE}:</td>
        <td>
            <p>
                <label><input type="radio" name="group_type" onchange="javascript:manage_group(this.name,this.value)" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} />{L_GROUP_OPEN}</label> &nbsp;&nbsp;
                <label><input type="radio" name="group_type" onchange="javascript:manage_group(this.name,this.value)" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />{L_GROUP_CLOSED}</label> &nbsp;&nbsp;
                <label><input type="radio" name="group_type" onchange="javascript:manage_group(this.name,this.value)" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />{L_GROUP_HIDDEN}</label>
            </p>
        </td>
    </tr>
    <tr>
        <td>{L_RELEASE_GROUP}</td>
        <td>
            <label><input type="radio" name="release_group" value="1" onclick="javascript:manage_group(this.name,this.value)" <!-- IF RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
            <label><input type="radio" name="release_group" value="0" onclick="javascript:manage_group(this.name,this.value)" <!-- IF not RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
        </td>
    </tr>
    <tr>
        <td>{L_AVATAR}</td>
        <td><input type="file" />&nbsp;<input class="mainoption" type="submit" name="avatarupload" value="{L_UPLOAD_AVATAR_FILE}" /></td>
    </tr>
</table>
</form>