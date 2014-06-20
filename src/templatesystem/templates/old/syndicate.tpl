<center>
<form action="syndicate.php" method="get">
	<table cellpadding="0" cellspacing="0" align="center" width="550">
		<tr>
			<td width="200">
				<a href="syndicate.php?change=0{if $MODUS.0 == 2}1{else}2{/if}&rid={$RID}" class="linkAufsiteBg">{if $MODUS.0 == 2}<img src="{$IMAGES}dot-gruen.gif" hspace="5" border="0">{else}<img src="{$IMAGES}dot.gif" hspace="5" border="0">{/if} mit Allianzpartnern</a><br />
				<a href="syndicate.php?change=1{if $MODUS.1 == 2}1{else}2{/if}&rid={$RID}" class="linkAufsiteBg">{if $MODUS.1 == 2}<img src="{$IMAGES}dot-gruen.gif" hspace="5" border="0">{else}<img src="{$IMAGES}dot.gif" hspace="5" border="0">{/if} Syndikatsbeschreibung</a>
			</td>
			<td width="150">
					<table cellpadding="5" cellspacing="0" align="center">
						<tr>
							<td><a href="syndicate.php?&rid={$MINUS}" class="linkAufsiteBg">&lt;&lt;</a></td>
							<td>&nbsp;</td>
							<td><input name="rid" value="{$RID}" size="2" maxlength="3" style="text-align:center; width:40px"></td>
							<td>&nbsp;</td>
							<td><a href="syndicate.php?rid={$PLUS}" class=linkAufsiteBg>&gt;&gt;</a></td>
						</tr>
						<tr>
							<td colspan="5" align="center"><input type="submit" value="wechseln"></td>
						</tr>
					</table>
			</td>
			<td width="200" align="right">
				{if $MODUS.0 == 2}<a href="syndicate.php?change=2{if $MODUS.2 == 2}1{else}2{/if}&rid={$RID}" class="linkAufsiteBg">{if $MODUS.2 == 2}<img src="{$IMAGES}dot-gruen.gif" hspace="5" border="0">{else}<img src="{$IMAGES}dot.gif" hspace="5" border="0">{/if} Spieler gemischt anzeigen</a>{else}&nbsp;{/if}
			</td>
		</tr>
	</table>
	<br />
</form>

{if $SYN}
{foreach from=$UEBERSICHTDATA item=UEBERSICHT}
<table cellpadding="0" cellspacing="0" border="0" class="tableOutline" align="center">
	{if $UEBERSICHT.ally1}
	<!-- Ally -->
	<tr>
		<td align="center" class="siteGround"><font class="resourceLeiste">
			{if $UEBERSICHT.ally1}Zusammen mit <a href="syndicate.php?rid={$UEBERSICHT.ally1}" class="linkAuftableInner">#{$UEBERSICHT.ally1}</a>{/if}
			{if $UEBERSICHT.ally2}, <a href="syndicate.php?rid={$UEBERSICHT.ally2}" class="linkAuftableInner">#{$UEBERSICHT.ally2}</a>{/if}
			Mitglied der Allianz "<b>{$UEBERSICHT.allianz_name}</b>"</font></td>
	</tr>
	{/if}
	{if $UEBERSICHT.wardata}
	<!-- Krieg -->
	<tr>
		<td align="center" class="siteGround"><font class="resourceLeiste">{foreach from=$UEBERSICHT.wardata item=WARDATA}Krieg gegen {if $WARDATA.text == "ally"}die Allianz{else if $WARDATA.text == "syn"}das Syndikat{/if} <b>"{$WARDATA.name} ({if $WARDATA.syns.0}<a href="syndicate.php?rid={$WARDATA.syns.0}" class="linkAuftableInner">#{$WARDATA.syns.0}</a>{/if}{if $WARDATA.syns.1}, <a href="syndicate.php?rid={$WARDATA.syns.1}" class="linkAuftableInner">#{$WARDATA.syns.1}</a>{/if}{if $WARDATA.syns.2}, <a href="syndicate.php?rid={$WARDATA.syns.1}" class="linkAuftableInner">#{$WARDATA.syns.2}</a>{/if})"</b><br />{/foreach}</font></td>
	</tr>
	{/if}
	<tr>
		<td>
		<!-- Synübersicht -->
			<table  border="0" cellspacing="1" cellpadding="2" width="580px">
			<!-- Synsite, Name und Monu -->
			{foreach from=$UEBERSICHT.syndata item=SYNDATA}
				<tr class="tableHead">
					{if $SYNDATA.syndikatswebseite}
					<td align="center" valign="middle" height="30px" width="20px"><a href="{$SYNDATA.syndikatswebseite}" target="_blank"><img src="{$GP_PATH}_homepage.gif" border="0"></a></td>
					{/if}
					<td colspan="{if $SYNDATA.syndikatswebseite}6{else}7{/if}" align="center" valign="middle" height="30px" width="100%">
						{$SYNDATA.name} (#{$SYNDATA.synd_id})
						{if $SYNDATA.artefakt_id}<br /><b><img src="{$GP_PATH}_praesi.gif" border="0" align="absmiddle"> Monument: {$SYNDATA.artefakt_name}</b> <img src="{$GP_PATH}_praesi.gif" border="0" align="absmiddle">
						{/if}</td>
				</tr>
				{if $SYNDATA.image}
				<tr class="tableHead2">
					<td colspan="7" align="center" valign="middle" height="90px">
						<table border="0" cellpadding="1" cellspacing="0"  valign="middle" align="center" class="tableOutline">
						<!-- Synbanner -->
							<tr>
								<td class="tableOutline" align="center" valign="middle"><img src="{$WWWDATA}syndikatsimages/{$SBILD_PREFIX}{$SYNDATA.synd_id}.{$SYNDATA.image}" border="0"></td>
							</tr>
						</table>
					</td>
				</tr>
				{/if}
			{/foreach}
				<tr class="tableHead2">
					{if $MODUS.1 == 2 && false}{* sollte aktiviert werden, wenn die Konzerne bei der Synbeschreibung ausgeblendet werden *}
					<td align="center" width="35px">&nbsp;</td>
					<td width="175px">&nbsp;</td>
					<td align="center" width="50px">&nbsp;</td>
					{else}
					<td align="center" width="35px"><a href="syndicate.php?rid={$RID}&orderby={$SORT_RACE.orderby}&ordertype={$SORT_RACE.ordertype}" class="linkAuftableInner">{if $SORT_RACE.img}<img src="{$IMAGES}{if $SORT_RACE.img == "DESC"}asc_order.gif{else if $SORT_RACE.img == "ASC"}desc_order.gif{/if}" border="0"> {/if}<img src="{$GP_PATH}dot-gelb.gif" border="0" hspace="5"></a></td>
					<td width="175px">&nbsp;&nbsp;<a href="syndicate.php?rid={$RID}&orderby={$SORT_KONZERN.orderby}&ordertype={$SORT_KONZERN.ordertype}" class="linkAuftableInner">{if $SORT_KONZERN.img}<img src="{$IMAGES}{if $SORT_KONZERN.img == "DESC"}asc_order.gif{else if $SORT_KONZERN.img == "ASC"}desc_order.gif{/if}" border="0"> {/if}<b>Konzern</b></a></td>
					<td align="center" width="50px">&nbsp;</td>
					{/if}
					<td align="center" width="70px"><a href="syndicate.php?rid={$RID}&orderby={$SORT_LAND.orderby}&ordertype={$SORT_LAND.ordertype}" class="linkAuftableInner">{if $SORT_LAND.img}<img src="{$IMAGES}{if $SORT_LAND.img == "DESC"}asc_order.gif{else if $SORT_LAND.img == "ASC"}desc_order.gif{/if}" border="0"> {/if}<b>Land</b></a></td>
					<td align="center" width="80px"><a href="syndicate.php?rid={$RID}&orderby={$SORT_NW.orderby}&ordertype={$SORT_NW.ordertype}" class="linkAuftableInner">{if $SORT_NW.img}<img src="{$IMAGES}{if $SORT_NW.img == "DESC"}asc_order.gif{else if $SORT_NW.img == "ASC"}desc_order.gif{/if}" border="0"> {/if}<b>Networth</b></a></td>
					<td align="center" width="50px"><a href="syndicate.php?rid={$RID}&orderby={$SORT_FOX.orderby}&ordertype={$SORT_FOX.ordertype}" class="linkAuftableInner">{if $SORT_FOX.img}<img src="{$IMAGES}{if $SORT_FOX.img == "DESC"}asc_order.gif{else if $SORT_FOX.img == "ASC"}desc_order.gif{/if}" border="0"> {/if}<b>Fox</b></a></td>
					<td align="center" width="80px"><b>Aktionen</b></td>
				</tr>
				<!-- Anzeige der Konzerne: START -->
				{if $MODUS.1 == 1 || true}{* Aktiviert: Konzerne werden nicht angezeigt, wenn Synbeschreibung aktiviert - START*}
				{foreach from=$UEBERSICHT.konzerndata item=KONZERN}
				<tr class="tableInner{if $KONZERN.buddy}2{else}{if $KONZERN.own}2{else}1{/if}{/if}">
					<td align="center"> <a href=javascript:info('fraktionen','{$KONZERN.race}') class="highlightAuftableInner"><img src="{$GP_PATH}{$KONZERN.raceicon}-logo-klein.gif" alt="{$KONZERN.racename}" height="22" width="22" border="0"></a></td>
					<td height="27" nowrap="nowrap">&nbsp;&nbsp;<a href="syndicate.php?action=details&detailsid={$KONZERN.id}&rid={$KONZERN.rid}" class="{if $KONZERN.color == "normal"}linkAuftableInner{elseif  $KONZERN.color == "attacked"}konzernAttacked{elseif  $KONZERN.color == "heavyattacked"}konzernHeavyAttacked{elseif  $KONZERN.color == "holiday"}konzernHoliday{elseif  $KONZERN.color == "protected"}konzernProtected{/if}" {if $KONZERN.own}style="font-weight:bold;"{/if}>{$KONZERN.syndicate}{if $MODUS.0 == 2 && $MODUS.2 == 2} (#{$KONZERN.rid}){/if}</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td align="right" nowrap="nowrap">
								{if $KONZERN.mentor}<img src="{$GP_PATH}_help.gif" border="0" align="absmiddle"> {/if}
								{if $KONZERN.is_newb}<img src="{$MENTOR_PIC}" border="0" align="absmiddle"> {/if}
								{if $KONZERN.nap}<img src="{$GP_PATH}icon_nasp_04.png" border="0" align="absmiddle"> {/if}
								{if $KONZERN.president}<img src="{$GP_PATH}_praesi.gif" border="0" align="absmiddle"> {/if}
								{if $KONZERN.aktieninhaber}<img src="{$GP_PATH}_aktien_halter.gif" border="0" align="absmiddle" {if $KONZERN.aktienprozent}title="{$KONZERN.aktienprozent|number_format:1}% Besitz"{/if}> {/if}
								{if 	$KONZERN.status == "online"}<img src="{$GP_PATH}_online.gif" border="0" align="absmiddle">
								{elseif $KONZERN.status == "offline"}<img src="{$GP_PATH}_offline.gif" border="0" align="absmiddle">
								{elseif $KONZERN.status == "gl_inaktiv"}<img src="{$GP_PATH}_gl_inaktiv.gif" border="0" align="absmiddle">
								{elseif $KONZERN.status == "lokal_inaktiv"}<img src="{$GP_PATH}_lokal_inaktiv.gif" border="0" align="absmiddle">{/if}
								</td>
					<td align="right">{$KONZERN.land|number_format}&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td align="right">{$KONZERN.nw|number_format}&nbsp;&nbsp;&nbsp;</td>
					<td align="right"><a {if $KONZERN.schnitt}title="Fraktionsdurchschnitt: {$KONZERN.schnitt|number_format:1}"{/if} style="text-decoration:none; cursor:text;" href="javascript: return false;" class="{if $KONZERN.schnitt_color == "normal"}linkAuftableInner{elseif  $KONZERN.schnitt_color == "gelb"}konzernAttacked{elseif  $KONZERN.schnitt_color == "rot"}konzernHeavyAttacked{/if}">{if $KONZERN.fox}{$KONZERN.fox|number_format:1}{else}n/a{/if}</a>&nbsp;&nbsp;&nbsp;</td>
					<td align=right nowrap>
								{if !$KONZERN.own}
								{if $KONZERN.attackable}<a href="{$KONZERN.url_angriff}"><img src="{$GP_PATH}_syn_attack.gif" border="0" alt="{literal}{{/literal}{$KONZERN.syndicate}{if $MODUS.0 == 2 && $MODUS.2 == 2} (#{$KONZERN.rid}){/if}{literal}}{/literal} angreifen"></a> {/if}
								{if $KONZERN.spieable}<a href="{$KONZERN.url_spies}"><img src="{$GP_PATH}_syn_spie.gif" border="0" alt="Spionage gegen {literal}{{/literal}{$KONZERN.syndicate}{if $MODUS.0 == 2 && $MODUS.2 == 2} (#{$KONZERN.rid}){/if}{literal}}{/literal}"></a> {/if}
								{if $KONZERN.own_syn}<a href="{$KONZERN.url_lager}"><img src="{$GP_PATH}_syn_transfer.gif" border="0" alt="Transfer an {literal}{{/literal}{$KONZERN.syndicate}{if $MODUS.0 == 2 && $MODUS.2 == 2} (#{$KONZERN.rid}){/if}{literal}}{/literal}"></a> {/if}
								<a href="{$KONZERN.url_msg}"><img src="{$GP_PATH}_syn_message_letter.gif" border="0" alt="{literal}{{/literal}{$KONZERN.syndicate}{if $MODUS.0 == 2 && $MODUS.2 == 2} (#{$KONZERN.rid}){/if}{literal}}{/literal} eine Nachricht senden"></a>
								{/if}
								</td>
				</tr>
				{/foreach}
				{/if}
				<!-- Anzeige der Konzerne: ENDE -->
				
				<!-- Zusammenfassung des Syns -->
				<tr class="tableInner2">
					<td colspan="3" class="tableHead2">&nbsp;&nbsp;{$UEBERSICHT.konzerndata|@count} <b>Gesamt:</b></td>
					<td align="right"><strong>{$UEBERSICHT.totalland|number_format}</strong>&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td align="right"><strong>{$UEBERSICHT.totalnetworth|number_format}</strong>&nbsp;&nbsp;&nbsp;</td>
					<td align="right"><strong>{if $UEBERSICHT.totalfox}{$UEBERSICHT.totalfox|number_format:1}{else}n/a{/if}</strong>&nbsp;&nbsp;&nbsp;</td>
					<td align="right"> {$UEBERSICHT.cronimon} </td>
				</tr>
				
				<!-- Synbeschreibung -->
				{if $MODUS.1 == 2}
				{foreach from=$UEBERSICHT.syndata item=SYNDATA}
				{if $MODUS.0 == 2 &&  $MODUS.2 == 2}
				<!-- Wenn Spieler gemischt sind wird hier zusätzlich noch der Name angezeigt -->
				<tr class="tableHead2">
					<td colspan="7" align="center">{$SYNDATA.name} (#{$SYNDATA.synd_id})</td>
				</tr>
				{/if}
				<tr class="tableInner1">
					<td colspan="7">
						<br />
						{if $SYNDATA.description}{$SYNDATA.description}{else}<center>Es wurde keine Syndikatsbeschreibung festgelegt.</center>{/if}
						<br />
					</td>
				</tr>
				{/foreach}
				{/if}
			</table>
		</td>
	</tr>
</table>
<br />
<br />
{/foreach}
{if $MODUS.1 == 1 || true}{* "|| true" entfernt: Legende wird nicht angezeigt, wenn Synbeschreibung aktiviert - START*}
<table cellspacing="1" cellpadding="2" border="0" width="390px" class="tableOutline" align="center">
	<tr>
		<td class="tableHead" colspan="4" align="center" height="28px"><b>Legende</b></td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1" width="28px"><img src="{$GP_PATH}bf-logo-klein.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2" width="167px">Brute Force</td>
		<td align="center" height="28px" width="28px" class="tableInner1"><img src="{$GP_PATH}_online.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2" width="167px">Geschäftsführer online</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}uic-logo-klein.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">United Industries Corporation</td>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}_lokal_inaktiv.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Konzern ist inaktiv</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}sl-logo-klein.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Shadow Labs</td>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}_gl_inaktiv.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Konzern ist global inaktiv</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}neb-logo-klein.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">New Economic Block</td>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}_praesi.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Präsident des Syndikats</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}nof-logo-klein.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Nova Federation</td>
		<td align="center" height="28px" class="tableInner1"><img src="{$GP_PATH}_aktien_halter.gif" border="0" align="absmiddle"></td>
		<td class="tableInner2">Geschäftsführer besitzt über {$AKTIEN_PIC_SYNVIEW|number_format:1}% Aktien Ihres Syndikats</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><a href="#" class="konzernProtected">##</a></td>
		<td class="tableInner2">Unter Schutz</td>
		<td align="center" height="28px" class="tableInner2"><a href="#" class="tableInner1"><!-- <img border="0" src="{$GP_PATH}icon_nasp_04.png"> --></a></td>
		<td class="tableInner2">Buddy oder eigener Konzern</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><a href="#" class="konzernHoliday">##</a></td>
		<td class="tableInner2" colspan="3">Im Urlaub</td>
	</tr>
		<td align="center" height="28px" class="tableInner1"><a href="#" class="konzernAttacked">##</a></td>
		<td class="tableInner2" colspan="3">Innerhalb von 24 Std. <strong>einmal</strong> erfolgreich angegriffen</td>
	</tr>
	<tr>
		<td align="center" height="28px" class="tableInner1"><a href="#" class="konzernHeavyAttacked">##</a></td>
		<td class="tableInner2" colspan="3">Innerhalb von 24 Std. <strong>mehrmals</strong> erfolgreich angegriffen</td>
	</tr>
</table>
{/if}
{/if}
</center>