<!-- IF EDITABLE_TPLS -->
<div id="editable-tpl-input" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value"/>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;"/>
		<input type="button" class="editable-cancel" value="x" style="width: 30px;"/>
	</span>
</div>
<div id="editable-tpl-yesno-select" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value">
			<option value="1">&nbsp;{L_YES}&nbsp;</option>
			<option value="0">&nbsp;{L_NO}&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;"/>
		<input type="button" class="editable-cancel" value="x" style="width: 30px;"/>
	</span>
</div>
<div id="editable-tpl-yesno-radio" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<label><input class="editable-value" type="radio" name="editable-value" value="1"/>{L_YES}</label>
		<label><input class="editable-value" type="radio" name="editable-value" value="0"/>{L_NO}</label>&nbsp;
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;"/>
		<input type="button" class="editable-cancel" value="x" style="width: 30px;"/>
	</span>
</div>
<div id="editable-tpl-yesno-gender" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value">
			<option value="0">&nbsp;{$lang['GENDER_SELECT'][0]}&nbsp;</option>
			<option value="1">&nbsp;{$lang['GENDER_SELECT'][1]}&nbsp;</option>
			<option value="2">&nbsp;{$lang['GENDER_SELECT'][2]}&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-twitter" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value" value="{TWITTER}"/>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-birthday" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="date" class="editable-value" value="{BIRTHDAY}"/>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<!-- ENDIF / EDITABLE_TPLS -->
