<style type="text/css">
.log_msg { max-height: 100px; overflow: auto; }
table.log_filters { padding: 6px; width: 100%; }
table.log_filters td {
	border: 1px inset; padding: 4px; background: #EFEFEF;
}
</style>

<div class="spacer_4"></div>

<form action="{S_LOG_ACTION}" method="post">
<!-- IF TOPIC_CSV --><input type="hidden" name="t" value="{TOPIC_CSV}" /><!-- ENDIF -->

<table class="bordered w100" cellspacing="0">
	<tr>
		<th class="thHead">{L_ACTS_LOG_SEARCH_OPTIONS}</th>
	</tr>
	<tr>
		<td class="row1">
			<table class="fieldsets borderless bCenter pad_0" cellspacing="0">
				<tr>
					<td rowspan="2" valign="top" nowrap="nowrap" class="row1">
						<fieldset>
						<legend>{L_ACTS_LOG_FORUM}</legend>
						<div>
							<p class="select">{SEL_FORUM}</p>
						</div>
						</fieldset>
					</td>
					<td valign="top" class="row1">
						<fieldset>
						<legend>{L_ACTS_LOG_ACTION}</legend>
						<div>
							<p class="select">{SEL_LOG_TYPE}</p>
						</div>
						</fieldset>
					</td>
					<td valign="top" class="row1">
						<fieldset>
						<legend>{L_USER}</legend>
						<div>
							<p class="select">{SEL_USERS}</p>
						</div>
						</fieldset>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" class="row1">
			<table class="fieldsets borderless">
				<tr>
					<td valign="top" class="row1">
						<fieldset>
						<legend>{L_ACTS_LOG_LOGS_FROM} ({L_ACTS_LOG_FIRST}: {FIRST_LOG_TIME})</legend>
						<div>
							<p class="input nowrap">
								<input class="post" type="text" size="10" maxlength="20" name="{DATETIME_NAME}" value="{DATETIME_VAL}" />
								{L_ACTS_LOG_OR}
								<input class="post" type="text" size="2" maxlength="5" name="{DAYSBACK_NAME}" value="{DAYSBACK_VAL}" />
								{L_ACTS_LOG_DAYS_BACK}
							</p>
						</div>
						</fieldset>
					</td>
					<td valign="top" class="row1">
						<fieldset>
						<legend>{L_ACTS_LOG_TOPIC_MATCH}</legend>
							<div>
								<p class="input"><input class="post" type="text" size="28" maxlength="{TITLE_MATCH_MAX}" name="{TITLE_MATCH_NAME}" value="{TITLE_MATCH_VAL}" /></p>
							</div>
						</fieldset>
					</td>
					<td valign="top" class="row1">
						<fieldset style="height: 45px; width: 110px;">
						<legend>{L_ACTS_LOG_SORT_BY}</legend>
							<div>
								<p class="select nowrap">
									<label><input id="sort_asc" type="radio" name="{SORT_NAME}" value="{SORT_ASC}" {SORT_ASC_CHECKED} /> {L_ASC}</label>
									<label><input id="sort_desc" type="radio" name="{SORT_NAME}" value="{SORT_DESC}" {SORT_DESC_CHECKED} /> {L_DESC}</label>
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
			<span class="med"><input type="submit" class="liteoption" value="&nbsp;{L_SEARCH}&nbsp;" name="submit" /></span>
		</td>
	</tr>
</table>

<div class="spacer_8"></div>

<table class="forumline">
	<tr>
		<th>{L_ACTS_LOG_LOGS_ACTION}</th>
		<th>{L_ACTS_LOG_USERNAME}</th>
		<th>{L_ACTS_LOG_TIME}</th>
		<th width="60%">{L_ACTS_LOG_INFO}</th>
	</tr>
	<!-- BEGIN log -->
	<tr class="{log.ROW_CLASS}">
		<td><a class="med" href="{log.ACTION_HREF_S}"><span class="med">{log.ACTION_DESC}</span></a></td>
		<td class="tCenter" style="line-height: 14px;">
			<div class="med"><a class="med" href="{log.USER_HREF_S}"><b>{log.USERNAME}</b></a></div>
			<div class="small"><i>{log.USER_IP}</i></div>
		</td>
		<td class="small tCenter nowrap" style="line-height: 14px;">
			<div>{log.TIME}</div>
			<div><a class="small" href="{log.DATETIME_HREF_S}"><span class="small"><i>{log.DATE}</i></span></a></div>
		</td>
		<td class="med" style="line-height: 14px;">
			<!-- IF log.MSG -->
			<div class="log_msg">{log.MSG}</div>
			<!-- ENDIF -->
			<!-- IF log.TOPIC_TITLE -->
			<div>
				<a href="{log.TOPIC_HREF_S}" class="med"><span class="med">{log.TOPIC_TITLE}</span></a>
				<!-- IF log.TOPIC_HREF -->
					<a href="{log.TOPIC_HREF}" class="med" target="_blank"><img src="{IMG}icon_latest_reply.gif" class="icon2" alt="" title="" /></a>
				<!-- ENDIF -->
				<!-- IF log.TOPIC_TITLE_NEW -->
					<span class="nav"><em>&raquo;</em></span>
					<a href="{log.TOPIC_HREF_NEW_S}" class="med"><span class="med">{log.TOPIC_TITLE_NEW}</span></a>
					<a href="{log.TOPIC_HREF_NEW}" class="med" target="_blank"><img src="{IMG}icon_latest_reply.gif" class="icon2" alt="" title="" /></a>
				<!-- ENDIF -->
			</div>
			<!-- ENDIF -->
			<!-- IF log.FORUM_NAME -->
			<div class="small nowrap">
				<a href="{log.FORUM_HREF}" class="med" target="_blank"><img src="{IMG}icon_minipost.gif" class="icon1" alt="" title="" /></a>
				<a href="{log.FORUM_HREF_S}" class="med"><i>{log.FORUM_NAME}</i></a>
				<!-- IF log.FORUM_NAME_NEW -->
				<span class="nav"><em>&raquo;</em></span>
				<a href="{log.FORUM_HREF_NEW}" class="med" target="_blank"><img src="{IMG}icon_minipost.gif" class="icon1" alt="" title="" /></a>
				<a href="{log.FORUM_HREF_NEW_S}" class="med"><i>{log.FORUM_NAME_NEW}</i></a>
				<!-- ENDIF -->
			</div>
			<!-- ENDIF -->
		</td>
	</tr>
	<!-- END log -->
	<!-- BEGIN log_not_found -->
	<tr>
	 <td class="row1 tCenter pad_12" colspan="{LOG_COLSPAN}"><span class="gen">{L_NO_MATCH}</span></td>
	</tr>
	<!-- END log_not_found -->
<tr>
	<td class="spaceRow" colspan="{LOG_COLSPAN}"><div class="spacer_4"></div></td>
</tr>
</table>

<!-- IF PAGINATION -->
<div class="nav" style="margin: 8px 4px 14px 4px">
	<div style="float:left">{PAGE_NUMBER}</div>
	<div style="float:right">{PAGINATION}</div>
</div>
<div class="clear"></div>
<!-- ENDIF -->

</form>

<!-- IF FILTERS -->
<div class="spacer_8"></div>

<fieldset class="row3">
<legend>{L_ACTS_LOG_FILTER}</legend>
<table class="log_filters" cellspacing="4">
<tr>
	<!-- IF FILTER_FORUMS -->
	<td>
	<p class="med bold">{L_FORUMS}:</p>
	<div>
		<!-- BEGIN forums -->
		<p class="med mrg_4">{forums.FORUM_NAME}</p>
		<!-- END forums -->
	</div>
	</td>
	<!-- ENDIF -->

	<!-- IF FILTER_TOPICS -->
	<td>
	<p class="med bold">{L_ACTS_LOG_TOPICS}</p>
	<div>
		<!-- BEGIN topics -->
		<p class="med mrg_4">{topics.TOPIC_TITLE}</p>
		<!-- END topics -->
	</div>
	</td>
	<!-- ENDIF -->

	<!-- IF FILTER_USERS -->
	<td>
	<p class="med bold">{L_USER}:</p>
	<div>
		<!-- BEGIN users -->
		<p class="med mrg_4">{users.USERNAME}</p>
		<!-- END users -->
	</div>
	</td>
	<!-- ENDIF -->

</tr></table>
</fieldset>
<!-- ENDIF -->
