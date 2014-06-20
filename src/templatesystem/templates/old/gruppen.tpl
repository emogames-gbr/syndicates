{* 
	Ausgabe wenn in einer Gruppe
									*}
{if $GACTION == 'inGroup'}
<a href="gruppen.php?force_groups=1" class="linkAuftableInner">< zur Gruppenübersicht</a>
<br /><br />
<center>
	<strong>Sie befinden sich mit folgenden Spielern in einer Gruppe für die nächste Runde.</strong>
	<br /><br /><br />
	<table align="center" border="0" cellpadding="5" cellspacing="1" class="tableOutline">
		<tr>
			<td class="tableHead" colspan="3">Konzernname</td>
			<td class="tableHead">Nickname</td>
			<td class="tableHead">Status</td>
			<td class="tableHead">Aktionen</td>
		</tr>
	{foreach from=$GROUPMEMBER item=VL}
		<tr {if $VL.self}class="tableInner2"{else}class="tableInner1"{/if}>
			<td>
				{if $VL.status == 0}<em>{/if}
				{if $VL.nr < 10}0{/if}{$VL.nr}
				{if $VL.status == 0}</em>{/if}
			</td>
			<td>
				{if $VL.status == 0}<em>{/if}
				{if $VL.konzernid}
					{if $VL.race == 'pbf'}
						<img src="{$RIPF}bf-logo-klein.gif">
					{else}
						<img src="{$RIPF}{$VL.race}-logo-klein.gif">
					{/if}
				{/if}
				{if $VL.status == 0}</em>{/if}
			</td>
			<td>
				{if $VL.status == 0}<em>{/if}
				{if $VL.konzernid}
					{$VL.rulername} von {$VL.syndicate} (#{$VL.rid})
				{else}
					hat keinen Konzern
				{/if}
				{if $VL.status == 0}</em>{/if}
			</td>
			<td>
				{if $VL.status == 0}<em>{/if}
				{$VL.username}
				{if $VL.status == 0}</em>{/if}
			</td>
			<td>
				{if $VL.status == 0}<em>{/if}
				{if $VL.online == 'online'}
					<img src="{$RIPF}_online.gif" border="0" align="absmiddle">
				{elseif $VL.online == 'global_inaktiv'}
					{if $VL.konzernid}
						<img src="{$RIPF}_gl_inaktiv.gif" border="0" align="absmiddle">
					{/if}
				{elseif $VL.online == 'lokal_inaktiv'}
					<img src="{$RIPF}_lokal_inaktiv.gif" border="0" align="absmiddle">
				{else}
					<img src="{$RIPF}_offline.gif" border="0" align="absmiddle">
				{/if}
				{if $VL.is_groupadmin}
					<img src="{$RIPF}_praesi.gif" border="0" align="absmiddle" title="Ist Gruppenadmin">
				{/if}
				{if $VL.is_nachfolger}
					<img style="opacity:0.4; filter:alpha(opacity=40);" src="{$RIPF}_praesi.gif" border="0" align="absmiddle" title="Ist Nachfolger">
				{/if}
				{if $VL.status == 0}</em>{/if}
			</td>
			<td>
				{if $VL.status == 0}<em>{/if}
				{if $VL.self}
					&nbsp;
				{else}
					{if $VL.konzernid}
					<a href="mitteilungen.php?action=psm&amp;rec={$VL.konzernid}" class="linkAufsiteBg">
						<img src="{$RIPF}_syn_message_letter.gif" border="0"></a>
					{/if}
					{if $VL.status == 0 && $IS_GROUPADMIN}
						<a href="gruppen.php?gaction=activate&amp;player_id={$VL.user_id}" class="linkAufsiteBg">
							aktivieren</a>
					{/if}
					{if $IS_GROUPADMIN}
						<script type="text/javascript">
							var msg_group_admin = "Willst du wirklich den User zum Admin ernennen. Du verlierst damit deine eigenen Adminrechte komplett.";
						</script>
						<a href="gruppen.php?gaction=kick&amp;player_id={$VL.user_id}" class="linkAuftableInner">
						<img border="0" src="{$RIPF}_for_deselect.gif"></a>
						<a href="gruppen.php?gaction=admin&amp;player_id={$VL.user_id}" class="linkAuftableInner" onClick="return (confirm(msg_group_admin));">
							<img src="{$RIPF}_praesi.gif" border="0" align="absmiddle" title="als Admin ernennen"></a>
						{if !$VL.is_nachfolger}
							<a href="gruppen.php?gaction=nachfolger&amp;player_id={$VL.user_id}" class="linkAuftableInner">
								<img style="opacity:0.4; filter:alpha(opacity=40);" src="{$RIPF}_praesi.gif" border="0" align="absmiddle" title="als Nachfolger benennnen"></a>
						{/if}
					{/if}	
				{/if}
				{if $VL.status == 0}</em>{/if}
			</td>
		</tr>
	{/foreach}
	</table>
	<br /><br />
	<b>Gruppenoptionen</b>
	<br /><br />
	{if 1 < $GROUPMEMBER_COUNT || !$IS_GROUPADMIN}
		<a href="gruppen.php?gaction=leave" class="linkAuftableInner">Gruppe verlassen</a>
	{/if}
	{if $IS_GROUPADMIN}
	<a href="gruppen.php?gaction=delete" class="linkAuftableInner">Gruppe löschen</a>
	{/if}
	<br /><br /><br />
	<table align="center" border="0" cellpadding="5" cellspacing="1" width="550px" class="tableOutline">
		<tr>
			<td colspan="4" class="tableHead">
				Eigenschaften
			</td>
		</tr>
		{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
		<form action="gruppen.php?gaction=sonstiges" method="post">
		{/if}
		<tr>
			<td class="tableInner1" align="right" width="17%">
				Gruppenname
			</td>
			<td class="tableInner1" align="left" width="50%" colspan="2">
			{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
				<input type="text" name="name" value="{$GROUP.name}" size="40" maxlength="50">
			{else}
				{$GROUP.name}
			{/if}
			</td>
			<td class="tableInner1" align="left" width="33%">
			{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
				<input type="submit" value="speichern"
			{/if}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="right" width="17%">
				Ausrichtung
			</td>
			<td class="tableInner1" align="left" width="33%">
			{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
				<input type="text" name="ausrichtung" value="{$GROUP.ausrichtung}" width="100%" maxlength="30">
			{else}
				{$GROUP.ausrichtung}
			{/if}
			</td>
			<td class="tableInner1" align="right" width="17%">
				Gegründet
			</td>
			<td class="tableInner1" align="left" width="33%">
				{$GROUP.createtime}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="right">
				Für Neulinge
			</td>
			<td class="tableInner1" align="left">
			{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
				<input type="radio" name="fuer_neulinge" value="1" {if $GROUP.fuer_neulinge}checked{/if}>Ja<br>
				<input type="radio" name="fuer_neulinge" value="0" {if !$GROUP.fuer_neulinge}checked{/if}>Nein
			{else}
				{if $GROUP.fuer_neulinge}ja{else}nein{/if}
			{/if}
			</td>
			<td class="tableInner1" align="right">
				Status
			</td>
			<td class="tableInner1" align="left">
			{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
				<input type="radio" name="ist_offen" value="1" {if $GROUP.ist_offen}checked{/if}>offen<br>
				<input type="radio" name="ist_offen" value="0" {if !$GROUP.ist_offen}checked{/if}>geschlossen
			{else}
				{if $GROUP.ist_offen}offen{else}geschlossen{/if}
			{/if}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="right">
				Mitglieder
			</td>
			<td class="tableInner1" align="left">
				{$GROUPMEMBER_COUNT}/{$GROUPMEMBER_MAX}
			</td>
			<td class="tableInner1" align="right">
				Gruppenadmin
				{if $GROUP.nachfolger_id != 0}<br>Nachfolger{/if}
			</td>
			<td class="tableInner1" align="left">
				{$GROUP.admin_name} {if $GROUP.nachfolger_id != 0}<br>{$GROUP.nachfolger_name}{/if}
			</td>
		</tr>
		{if $IS_GROUPADMIN && !$CHANGE_DESCRIPTION}
		</form>
		{/if}
		<tr>
			<td class="tableInner1" align="left" colspan="4">
				<b>Beschreibung der Gruppe</b> (auch von aussen sichtbar):
				{if $IS_GROUPADMIN}
					{if !$CHANGE_DESCRIPTION}
					<a href="gruppen.php?gaction=change_des" class="linkAuftableInner">
						Beschreibung ändern</a>
					{else}
					<a href="gruppen.php" class="linkAuftableInner">
						geänderte Beschreibung verwerfen</a>
					{/if}
				{/if}
			</td>
		</tr>
		<tr>
			<td class="tableInner2" align="left" colspan="4">
			{if $IS_GROUPADMIN && $CHANGE_DESCRIPTION}
				<form action="gruppen.php?gaction=description" method="post">
					<textarea rows="7" cols="60" name="description">{$GROUP.description}</textarea><br />
					<input type="submit" value="Speichern">
				</form>
			{else}
				<br />
				{if $GROUP.description_edit}
					{$GROUP.description_edit}
				{else}
					bisher keine Beschreibung
				{/if}
				<br /><br />
			{/if}
			</td>
		</tr>
	</table>
</center>
<br>
<strong>Anmerkungen:</strong>
<ul><li>Das 
	<a href="gruppenboard.php" class="linkAufsiteBg">Gruppenboard</a> 
	ist ab 7 Tage vor Rundenende verfügbar und dann bis zum Start der nächsten Runde.</li>
<li>Erstelle im <a href="http://board.emogames.de" class="linkAufsiteBg" target="_blank">Emogames-Forum</a>
	<a href="http://board.emogames.de/board.php?boardid=18" class="linkAufsiteBg" target="_blank">Syndikats- / Spielersuche</a>
	ein <a href="http://board.emogames.de/newthread.php?boardid=18" class="linkAufsiteBg" target="_blank">Thema</a>,
	um auf deine neue Gruppe aufmerksam zu machen und Mitstreiter zu finden.
{elseif $GACTION == 'deactivatedInGroup'}
<a href="gruppen.php?force_groups=1" class="linkAuftableInner">< zur Gruppenübersicht</a>
<br /><br />
<center>
	<strong>Sie sind in Ihrer Gruppe noch deaktiviert.</strong>
	<br /><br />
	Warten Sie auf die Entscheidung des Gruppenadministators oder
	<a href="gruppen.php?gaction=leave" class="linkAuftableInner">
		<strong>verlassen</strong></a> Sie die Gruppe wieder.
</center>
{elseif $GACTION == 'noGroup'}
<br /><br />
<center>
	{if !$ALREADY_JOINED_A_GROUP}
	<strong>Sie sind noch in keiner Gruppe für nächste Runde.</strong>
	{else}
	<strong>Sie sind bereits in der Gruppe <a href="gruppen.php" class="linkAufsiteBg">"{$GROUP.name}"</a></strong>
	{/if}
	{if $GROUPS}
	<br /><br />
	Bisher gibt es {$GROUPS_COUNT} Gruppe{if $GROUPS_COUNT > 1}n{/if}:
	{if $EXTRA == 'vollAusblenden'}<a class="linkAuftableInner" href="gruppen.php?extra=all{if $ALREADY_JOINED_A_GROUP}&amp;force_groups=1{/if}">(alle Gruppen anzeigen)</a>
	{else}<a class="linkAuftableInner" href="gruppen.php?extra=vollAusblenden{if $ALREADY_JOINED_A_GROUP}&amp;force_groups=1{/if}">(volle/geschlossene Gruppen ausblenden)</a>{/if}
	<br /><br />
	<table align="center" border="0" cellpadding="5" cellspacing="1" class="tableOutline">
		<tr>
			<td class="tableHead" width="80%" colspan="2">
				<strong>Gruppenname</strong>
			</td>
			<td class="tableHead" width="20%">
				<strong>Aktion</strong>
			</td>
		</tr>
		{foreach from=$GROUPS item=VL}
		<tr {$VL.tooltip}>
			<td class="tableInner2" colspan="2">
				{$VL.name} 	
				({$VL.count}/{$GROUPMEMBER_MAX})
			</td>
			<td class="tableInner1" rowspan="2" align="center">
				<a href="mitteilungen.php?action=psm&amp;rec={$VL.admin_konzernid}" class="linkAufsiteBg">
					<img src="{$RIPF}_syn_message_letter.gif" border="0"></a><br><br>
			{if $GROUPMEMBER_MAX != $VL.count && $VL.ist_offen != 0}
				{if $ALREADY_JOINED_A_GROUP}
					{if $VL.group_id == $GROUP.group_id}
					<a href="gruppen.php" class="linkAufsiteBg">zur Gruppe</a>
					{else}
					<i>beitreten</i>
					{/if}
				{else}
				<a href="gruppen.php?gaction=join_text&amp;group_id={$VL.group_id}" class="linkAufsiteBg">
					beitreten</a>
				{/if}
			{else}
				{if $VL.ist_offen == 0}
					geschlossen
				{else}
					voll
				{/if}
			{/if}
			</td>
		</tr>
		<tr class="tableInner1" {$VL.tooltip}>
			<td align="right" valign="top">
				<b>Gründer<br>
				Ausrichtung<br>
				Für Neulinge</b>
			</td>
			<td valign="top">
				{$VL.konz_name}<br>
				{$VL.ausrichtung}<br>
				{if $VL.fuer_neulinge}ja{else}nein{/if}<br>
			</td>
		</tr>
		{/foreach}
	</table>
	{else}
	<br /><br />
	Es wurde noch keine Gruppe erstellt.
	{/if}
	<br /><br />
	<a href="gruppen.php?gaction=create" class="linkAufsiteBg">
		eigene Gruppe erstellen
	</a>
</center>
{elseif $GACTION == 'join_text'}
<a href="gruppen.php?force_groups=1" class="linkAuftableInner">< zur Gruppenübersicht</a>
<br /><br />
<form action="gruppen.php?gaction=join" method="post">
	<input type="hidden" name="group_id" value="{$GROUP_ID}">
	<table align="center" border="0" cellpadding="5" cellspacing="1" width="550px" class="tableOutline">
		<tr>
			<td colspan="4" class="tableHead">
				Bewerbung
			</td>
		</tr>
		<tr>
			<td class="tableInner2" align="left" colspan="4">
				<textarea rows="7" cols="60" name="bewerbungstext"></textarea><br />
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="left" colspan="4">
				Sage etwas über dich und warum du in diese Gruppe willst.
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="right" colspan="4">
				<input type="submit" value="bewerben">
			</td>
		</tr>
	</table>
</form>
{/if}