<h1>{L_MASS_EMAIL}</h1>

<p>{L_MASS_EMAIL_EXPLAIN}</p>
<br/>

<form method="post" action="{S_USER_ACTION}" onSubmit="return checkForm(this);">

  <table class="forumline">
    <tr>
      <th colspan="2">{L_COMPOSE}</th>
    </tr>
    <tr>
      <td class="row1" align="right"><b>{L_RECIPIENTS}</b></td>
      <td class="row2">{S_GROUP_SELECT}</td>
    </tr>
    <tr>
      <td class="row1" align="right"><b>{L_MASS_EMAIL_MESSAGE_TYPE}</b></td>
      <td class="row2">
        <select name="message_type">
          <option value="text/plain" selected="selected">text/plain</option>
          <option value="text/html">text/html</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="row1" align="right"><b>Reply to</b></td>
      <td class="row2">
        <input type="text" name="reply_to" size="45" maxlength="100" style="width:98%" value="{REPLY_TO}"/>
      </td>
    </tr>
    <tr>
      <td class="row1" align="right"><b>{L_SUBJECT}</b></td>
      <td class="row2">
        <input type="text" name="subject" size="45" maxlength="100" style="width:98%" value="{SUBJECT}"/>
      </td>
    </tr>
    <tr>
      <td class="row1" align="right" valign="top"><span class="gen"><b>{L_MESSAGE}</b></span></td>
      <td class="row2">
        <textarea name="message" rows="15" cols="35" wrap="virtual" style="width:98%">{MESSAGE}</textarea>
      </td>
    </tr>
    <tr>
      <td class="catBottom" colspan="2"><input type="submit" value="{L_SEND_EMAIL}" name="submit" class="mainoption"/>
      </td>
    </tr>
  </table>

</form>

<script type="text/javascript">
  function checkForm(formObj) {
    var formErrors = false;

    if (formObj.message.value.length < 2) {
      formErrors = "{L_EMPTY_MESSAGE_EMAIL}";
    } else if (formObj.subject.value.length < 2) {
      formErrors = "{L_EMPTY_SUBJECT_EMAIL}";
    }

    if (formErrors) {
      alert(formErrors);
      return false;
    }
  }
</script>
