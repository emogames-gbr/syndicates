{if $IS_MENTOR}
		<br />
		<br />
		<table  border="0" cellspacing="1" cellpadding="2" width=600 align="center" class="tableOutline">
			<tr class="tableHead2">
				<td align="center" width="32px"><img src="{$GP_PATH}dot-gelb.gif" border="0" hspace="5"></td>
				<td>&nbsp;&nbsp;Konzern</td>
				<td align="right" width="100px">letzter Login</td>
				<td align="right" width="25px">&nbsp;</td>
				<td align="right" width="60px">Land&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td align="right" width="70px">Networth&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td align="right" width="80px">Aktionen</td>
			</tr>
			{foreach name=all_newbs item=NEWB from=$NEWBS}
			{if $SYN_NOW != $NEWB.rid}
			{assign var="SYN_NOW" value=$NEWB.rid}
			<tr class="tableInner2">
				<td align="left" colspan="7">&nbsp;&nbsp;&nbsp;Syn #{$NEWB.rid}:</td>
			</tr>
			{/if}
			<tr class="tableInner1">
				<td align="center"> <a href=javascript:info('fraktionen','{$NEWB.race}') class="highlightAuftableInner"><img src="{$GP_PATH}{$NEWB.raceicon}.gif" alt="{$NEWB.racename}" height="22" width="22" border="0"></a></td>
				<td>&nbsp;&nbsp;<a href="syndicate.php?action=details&detailsid={$NEWB.id}&rid={$NEWB.rid}" class="linkAuftableInner">{$NEWB.name}</a> <a href="syndicate.php?&rid={$NEWB.rid}" class="linkAuftableInner">(#{$NEWB.rid})</a></td>
				<td align="right">{$NEWB.lastlogintime}&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td align="center"> <img src="{$GP_PATH}_{$NEWB.online}.gif" border="0" align="absmiddle"></td>
				<td align="right">{$NEWB.land|number_format}&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td align="right">{$NEWB.nw|number_format}&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td align="right" nowrap>
									{if !$NEWB.pm_from_mentor}<a href="mentor.php?action=ok&id={$NEWB.id}" onclick="return window.confirm('Hast du mit {$NEWB.name} (#{$NEWB.rid}) wirklich schon gesprochen?');"><img src="{$GP_PATH}_praesi.gif" style="height:18px; border:none; vertical-align:middle" alt="Spieler wurde aufgeklärt" title="Spieler wurde aufgeklärt"></a>{/if}
									{if $IS_MENTOR == 2}<a href="mentor.php?action=kick&id={$NEWB.id}" onclick="return window.confirm('{$NEWB.name} (#{$NEWB.rid}) wirklich aus dem Mentorenprogramm rauswerfen?');"><img src="{$GP_PATH}icon_nasp_04.png" style="height:18px; border:none; vertical-align:middle" alt="Spieler rauswerfen" title="Spieler rauswerfen"></a>{/if}
									<a href="spies.php?inneraction=prepare&rid={$NEWB.rid}&target={$NEWB.id}"><img src="{$GP_PATH}_syn_spie.gif" style="height:18px; border:none; vertical-align:middle" alt="Spionage gegen {literal}{{/literal}{$NEWB.name} (#{$NEWB.rid}){literal}}{/literal}"></a>
									<a href="mitteilungen.php?action=psm&rec={$NEWB.id}"><img src="{$GP_PATH}_syn_message_letter.gif" style="height:18px; border:none; vertical-align:middle" alt="{literal}{{/literal}{$NEWB.name} (#{$NEWB.rid}){literal}}{/literal} eine Nachricht senden"></a>
				</td>
			</tr>
			{/foreach}
		</table>
{else}
		Sie sind kein Mentor und dürfen deshalb diese Seite nicht betreten.
{/if}