{foreach from=$MESSAGES item=VL}
	<div>
		<table cellpadding="0" cellspacing="0" border="0" width="600" class="tableOutline">
			<tr>
				<td>
					<table cellpadding="0" cellspacing="1" border="0" width="600">
						<tr>
							<td width="20" valign="top" bgcolor="#000000" style="padding-top:8px;">
								<img src="{$RIPF}{$VL.img}.gif" border="0">
							</td>
							<td width="80" valign="top" class="{if $VL.gelesen}tableInner2{else}tableHead{/if}" align="center" style="padding-top:5px;padding-bottom:5px;">
								<span style="font-size:10px">
									{$VL.realdate}
								</span>
								<br>
								<B style="font-size:10px">
									{$VL.realtime}
								</B>
							</td>
							<td width="470" class="tableInner1" valign="top" class="ver11w" style="padding-left:20px;padding-top:5px;padding-bottom:5px;">
							{$VL.o_message}
							</td>
							<td width="30" class="tableHead" align="center" valign="middle">
								<input type="checkbox" name="delete{$VL.count}" value="{$VL.unique_id}">
							</td>
						</tr>
					</table> 
				</td>
			</tr>
		</table>
		<br>
	</div>
{/foreach}
{if $MORE}
	Sie sehen nur {$MAX} Nachrichten.<br />
	<iframe name="invis" style="display:none;"></iframe>
	<input type="button" onclick="javascript: parent.invis.location = 'nachrichten.php?action=ajax&detail=download{$URL_}'" value="alle Nachrichten downloaden" />
{/if}