{include file="js/mitteilungen.js.tpl"}
{if $SENDAMSG}
	<form action=mitteilungen.php method=post>
		<input type=hidden name=action value=nachrichten>
		<input type=hidden name=action value=sm>
		<input type=hidden name=rec value={$REC}>{if $REC=="gm"}<input type=hidden name=mid value={$MID}>{/if}
		<table align=center  cellpadding=4>
		<tr><td class=siteGround colspan=2><br>{$MSGorREPLY} versenden an "{$RECSTR}"<br>
		(HTML deaktiviert, BBCode aktiviert (Hilfe <a href=javascript:info('hilfe','bbcode') class="highlightAuftableInner">
		<img src="{$ripf}_help.gif" border=0 valign="absmiddle"></a>))</td></tr>
		<tr><td class=siteGround valign=top>Betreff</td><td><input type=text size=59 maxlength=50 value="{$BETREFF}" name=betreff></td></tr>
		<tr><td class=siteGround valign=top>Mitteilung</td><td>
		<textarea cols=55 rows=13 name=message>{$MESSAGE}</textarea>
		</td></tr>
		<tr><td align=right colspan=2>
		<input type=submit value=verschicken>
		</td></tr>
		</table>			
{/if}
{if $REPORTAMSG}
	{if $PREPAREREPORT}
			<br>
			Diese Option dient dazu beleidigende oder in sonst einer Art und Weise unhöfliche
			Mitteilungen den Gamemastern zu melden.<br>
			Bei mißbräuchlicher Nutzung ist mit Konsequenzen zu rechnen.<br>Wenn Sie diese Mitteilung wirklich melden wollen, geben Sie optional bitte noch an, was Sie an dieser Mitteilung stört.<br>
			<center>
			<form action=mitteilungen.php>
			<input type=hidden name=action value=report>
			<input type=hidden name=report value=true>
			<input type=hidden name=mid value={$MID}>
			<textarea name=additional_text cols=30 rows=5></textarea><br>
			<input type=submit value="Ja, Mitteilung melden"><br><br>
			<a class="linkAufsiteBg" href="mitteilungen.php?mid={$MID}&action=rm"><u>Nein, Mitteilung lieber doch nicht melden</u></a><br>
			</center>
	{/if}
	<br>
	{if $EXECUTEREPORT}
			<br><br>{$REPORTTXT}<br><br>
	{/if}
{/if}
{if $READAMSG}
	<table cellpadding="0" cellspacing="0" border="0" class=tableOutline align=center><tr><td>
	<table align=center width=500 cellpadding=4 cellspacing=1>
	<tr class=tableHead2><td width=500>
		<table width=500 cellpadding=0 cellspacing=0>
			<tr>
				<td class=tableHead2 width=100 align=left height=20>{if $TOR == "in"}Empfangen:{/if}</td>
				<td class=tableHead2 width=250 align=left>{$DATUM}</td>
				<td class=tableHead2 width=150 rowspan="3" align="right">
					<table with=100% cellpadding=0 cellspacing=0>
						{if $COND1}
						<tr><td height=20 align="right" width="100%"><a class="linkAufsiteBg" href="mitteilungen.php?action=psm&mid={$UNIQUEID}">Antworten</a></td></tr>
						{/if}
						{if $COND2}<tr><td height=20 align="right" width="100%"><a class="linkAufsiteBg" href="mitteilungen.php?action=report&mid={$MID}">Mitteilung melden</a></td></tr>
						{else}
						&nbsp;
						{/if}
						<tr><td height=15 align="right"	width="100%"><a href=mitteilungen.php?tor={$TOR}&action=del&delete1={$UNIQUEID} class=linkAufsiteBg>Mitteilung löschen</a></td></tr>
						{if $COND3}
							<tr><td height=15 align="right" width="100%"><a href="pod.php?pre_id=
							{$SENDER}#t"><img src="{$ripf}_syn_transfer.gif" border=0 alt="Transfer an {$SYNDICATE}"></a></td></tr>
						{/if}	                                   							
					</table>
			</tr>
			<tr>
				<td class=tableHead2 width=100 align=left height=20>{if $TOR=="in"}Von{else}An{/if}:</td>
				<td class=tableHead2 width=250 align=left>{$REC}</td>
			</tr>
			<tr>
				<td class=tableHead2 width=100 align=left height=20>Betreff:</td>
				<td class=tableHead2 width=250 align=left>{$BETREFF}</td>
			</tr>
		</table>
		</td></tr>
		<tr><td class=tableInner1><br>{$MESSAGE}<br><br></td></tr>
		</table></td></tr>
	</table>
	<br>
	<table width=500 align=center cellpadding=0 cellspacing=0 class=siteGround>
		<tr>
			<td width=200 align=left><a href=mitteilungen.php?tor={$TOR} class=linkAufsiteBg>Zurück</a></td>
			<td width=100 align=center>&nbsp;</td>
			<td width=200 align=right>
			{if $COND4}<input type="button" onClick="javascript:window.location.href='mitteilungen.php?action=psm&mid={$UNIQUEID}'" class="button" value="Antworten">
			{/if}</td>
		</tr>
	</table>
{/if}
{if $LISTMSG}
	<form action=mitteilungen.php method=post>
		<table align=center width=400 cellpadding=2>
		{if $COND1}
			<tr><td width=100 class=siteGround>Syndikat</td><td align=left><input type=text name=rid value="{$RID}" size=2></td><td align=left>
			<input type=submit name=change value="ändern"></td></tr></form>
			<form action=mitteilungen.php method=post>
			<input type=hidden name=action value=psm>
			<input type=hidden name=rid value={$RID}>
			<tr><td class=siteGround>Mitteilung an </td>
			<td class=siteGround>{$RECEIVER}</td>
			<td><input type=submit value=verfassen></td></tr></form>
		{/if}
		{if $PREMIUM}
			<form action=mitteilungen.php method=post><input type=hidden name=action value=search>
			<input type=hidden name=tor value={$TOR}><tr><td class=siteGround>Suchen nach</td>
			<td class=siteGround><input type=text value="{$SEARCHWORD}" name=searchword></td>
			<td><input type=submit value=suchen></td></tr></form>
		{/if}
		</table><br><br>
		<center><font class=highlightAufSiteBg>(<a href=mitteilungen.php?tor=in class=linkAufsiteBg>
		{if $TOR=="in"}
			<b>
		{/if}
		Posteingang
		{if $TOR=="in"}
			</b>
		{/if}
		</a> | <a href=mitteilungen.php?tor=out class=linkAufsiteBg>
		{if $TOR!="in"}
			<b>
		{/if}
		Postausgang
		{if $TOR!="in"}
			</b>
		{/if}
		</a>)</font></center><br>
		{if $MSGDATA}
			<center>
			<form action=mitteilungen.php method=post name=items>
			<input type=hidden name=tor value={$TOR}>
			<input type=hidden name=action value=del>
			<br>
			<table width=500 align=center border=0 cellpadding=0 cellspacing=0><tr><td align=right class=siteGround>
			Alle Mitteilungen markieren: <input type=checkbox name=delall onclick="checkAll(this)">
			<br><br><input type=submit value="Markierte Mitteilungen löschen"></td></tr></table><br>
			<table cellpadding="0" cellspacing="0" border="0" class=tableOutline><tr><td>
				<table align=center width=500 cellpadding=4 cellspacing=1>
				<tr class=tableHead><td width=500>
				<table width=500 cellpadding=0 cellspacing=0>
					<tr>
					<td class=tableHead width=150  align=center>
					{if $TOR=="in"}
						Von
					{else}
						An
					{/if}
					:</td>
					<td class=tableHead width=250 align=center>Betreff:</td>
					<td class=tableHead width=100 align=center colspan=2>Uhrzeit:</td>
					</tr>
				</table>
				</td></tr>
			{foreach from=$MSGARRAY item=ITEM}
				<tr class=tableInner2><td width=500>
				<table width=500 cellpadding=0 cellspacing=0 border=0>
					<tr>
						<td class=tableInner2 width=150 align=left>{$ITEM[0]}</td>
						<td class=tableInner2 width=230 align=left>{$ITEM[1]}</td>
						<td class=tableInner2 width=100 align=center>{$ITEM[2]}</td>
						<td class=tableInner2 width=20 align=center>{$ITEM[3]}</td>
					</tr>
				</table>
				</td></tr>
			{/foreach}
				</table>
			</td></tr></table><br><br>
			<table width=500 cellpadding=0 cellspacing=0 border=0 align=center><tr><td width=500 align=right><input type=submit value="Markierte Mitteilungen löschen"></td></tr></table>
			</form>
			</center>
		{else}
			<br><center>Es sind keine Mitteilungen vorhanden!<br><br>
		{/if}
{/if}