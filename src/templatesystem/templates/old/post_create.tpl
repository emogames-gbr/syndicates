
		<h3 align="center">{if $CREATE}Thema erstellen{elseif $REPLY}Antwort erstellen{elseif $EDIT}Beitrag editieren{/if}</h3>
		
		<form action="{$SITE}.php" method="post" name="board">
			{if $CREATE}
			<input type="hidden" name="action" value="create">
			{elseif $EDIT}
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="mid" value="{$MID}">
			<input type="hidden" name="tid" value="{$TID}">
			{elseif $REPLY}
			<input type="hidden" name="action" value="reply">
			<input type="hidden" name="tid" value="{$TID}">
			{/if}
			<input type="hidden" name="ia" value="finish">
			<table width="500" border="0" cellpadding="1" cellspacing="0" class="tableOutline" align="center">
				<tr>
					<td>
						<table width="100%" border="0" cellpadding="3" cellspacing="0">
							{if $PREVIEW}
							<tr class="tableInner1">
								<td colspan="2">
									<fieldset style="margin:10px">
										<legend> Vorschau </legend>
										{$PREVIEW_TEXT}
									</fieldset>
								</td>
							</tr>
							<tr class="siteGround">
								<td colspan="2" height="15px"> </td>
							</tr>
							{/if}
							<tr>
								<td width="100" align="center" class="tableInner2" height="40">
									<strong>Thema:</strong>
								</td>
								<td align="center" class="tableInner2" valign="middle">
									{if $CREATE}
									<input type="text" name="title" size="50" maxlength="50" value="{$TITLE}">
									{else}
									<font size="+1">{$TITLE}</font>
									{/if}
								</td>
							</tr>
							<tr>
								<td align="center" valign="top" class="tableInner1">
									<strong>Ihr Beitrag:</strong><br />
									<br />
									BBCode-Hilfe <a href="javascript: info('hilfe','bbcode')" class="highlightAuftableInner"><img src="{$GP_PATH}_help.gif" border="0" valign="absmiddle"></a>
								</td>
								<td align="center" class="tableInner1">
									<textarea cols="40" rows="20" name="message">{$MESSAGE}</textarea>
								</td>
							</tr>
							<tr class="tableInner1">
								<td height="50"></td>
								<td align="center">
									{if $CREATE}<input type="submit" value="Thema erstellen">
									{elseif $EDIT}<input type="submit" value="Editieren">
									{elseif $REPLY}<input type="submit" value="Antworten">{/if}
									<input type="submit" name="preview" value="Vorschau" />
								</td>
							</tr>
							{if $IS_PRESIDENT && $EDIT}
							<tr class="tableInner1">
								<td height="30">
								</td>
								<td align="center">
									<strong>Beitrag löschen:</strong> <input type="checkbox" value="1" name="delete">
								</td>
							</tr>
							{/if}
						</table>
					</td>
				</tr>
			</table>
		</form>
		<script language="Javascript">
		<!--
			{if $EDIT || $REPLY}document.board.message.focus();{else}document.board.title.focus();{/if}
		//-->
		</script>
