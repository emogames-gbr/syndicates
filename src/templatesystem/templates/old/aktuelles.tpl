{include file='js/aktuelles.js.tpl'}
		<br />
		<center>
		{if $ALLY_1}
			Aktuelles Ihres Bündnispartners <a href="aktuelles.php?details={$ALLY_1}" class="linkAufsiteBg">#{$ALLY_1}</a> lesen.<br /><br />
		{elseif $ALLY_2}
			Aktuelles Ihrer Bündnispartner <a href="aktuelles.php?details={$ALLY_1}" class="linkAufsiteBg">#{$ALLY_1}</a>, <a href="aktuelles.php?details={$ALLY_2}" class="linkAufsiteBg">#{$ALLY_2}</a> lesen.
		{/if}
		
		{if $AKTIEN}
			Ihre Aktien erlauben Ihnen, die aktuellen Geschehnisse aus den folgenden Syndikaten mitzuverfolgen:<br />
			{foreach name=aktien item=AKTIE from=$AKTIEN}
				<a href="aktuelles.php?details={$AKTIE.rid}" class="linkAufsiteBg">#{$AKTIE.rid}</a>{if $smarty.foreach.aktien.iteration < $smarty.foreach.aktien.total},{/if}
			{/foreach}
			<br />
			<br />
		{/if}
		
		{if $DETAILS == 1}
			Aktuelles aus Syndikat #{$SYN_NR}: <br />
			<br />
		{/if}
		</center>
		<table cellpadding="3" cellspacing="1" border="0" bgcolor="black" width="70%" align="center">
			<tr class="tableHead">
				<td colspan="4"> Folgende Kategorien anzeigen:
				<div style="float:right; margin-right:20px"><span onclick="tccheckall()" style="text-decoration:underline; cursor:pointer;"> Alle </span> | <span  style="text-decoration:underline; cursor:pointer;" onclick="tcuncheckall()"> Keine </span></div></td>
			</tr>
			<tr class="tableInner1">
				<td> Angriffe
					<input id="t0" type="checkbox" checked onclick="displaytc()"></td>
				<td> Politik
					<input id="t2" type="checkbox" checked onclick="displaytc()"></td>
				<td> Syndikatsstatus
					<input id="t1" type="checkbox" checked onclick="displaytc()"></td>
				<td> Monumente
					<input id="t3" type="checkbox" checked onclick="displaytc()"></td>
			</tr>
		</table>
		<br />
		<table cellpadding="5" cellspacing="1" border="0" bgcolor="black" width="570px" align="center">
			<tr class="tableHead">
				<td colspan="2" align="center" valign="middle" height="15">Heute</td>
			</tr>
			{foreach name=news_today item=NEWS from=$NEWS_TODAY}
			<tr class="tableInner1" id="te_{$NEWS.id}">
				<td width="60" align="center">{$NEWS.time} Uhr</td>
				<td>{$NEWS.message}</td>
			</tr>
			{foreachelse}
			<tr class="tableInner1">
				<td colspan="2" align="center">Heute geschah noch nichts</td>
			</tr>
			{/foreach}
		</table>
		<br />
		<table cellpadding="5" cellspacing="1" border="0" bgcolor="black" width="570px" align="center">
			<tr class="tableHead">
				<td colspan="2" align="center" valign="middle" height="15">Gestern</td>
			</tr>
			{foreach name=news_yesterday item=NEWS from=$NEWS_YESTERDAY}
			<tr class="tableInner1" id="te_{$NEWS.id}">
				<td width="60" align="center">{$NEWS.time} Uhr</td>
				<td>{$NEWS.message}</td>
			</tr>
			{foreachelse}
			<tr class="tableInner1">
				<td colspan="2" align="center">Gestern geschah nichts</td>
			</tr>
			{/foreach}
		</table>
		{if $BEFORE_YESTERDAY == 1} <br />
		<table cellpadding="5" cellspacing="1" border="0" bgcolor="black" width="570px" align="center">
			<tr class="tableHead">
				<td colspan="2" align="center" valign="middle" height="15">Vorgestern</td>
			</tr>
			{foreach name=news_before_yesterday item=NEWS from=$NEWS_BEFORE_YESTERDAY}
			<tr class="tableInner1" id="te_{$NEWS.id}">
				<td width="60" align="center">{$NEWS.time} Uhr</td>
				<td>{$NEWS.message}</td>
			</tr>
			{foreachelse}
			<tr class="tableInner1">
				<td colspan="2" align="center">Keine Daten vorhanden</td>
			</tr>
			{/foreach}
		</table>
		{/if} 
		{if $DETAILS == 1}
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:history.back()" class="linkAufsiteBg">zurück</a>
		{/if}
		