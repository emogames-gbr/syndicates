		<br />
		<br />
		<h2 align="center">Übersicht über alle eingefügte Meldungen</h2>
		<br />
		<table border="0" cellspacing="1" cellpadding="2" width="600" align="center" class="tableOutline">
			<tr class="tableHead2">
				<td align="center" width="30px"><img src="{$GP_PATH}dot-gelb.gif" border="0" hspace="5"></td>
				<td align="center" width="100px">Zeit</td>
				<td align="center" width="470px">Text</td>
			</tr>
			{foreach name=ticker item=MELDUNG from=$TICKER_MELDUNGEN}
			<tr class="tableInner1">
				<td align="center">{$MELDUNG.iteration}.:</td>
				<td align="center">{$MELDUNG.date}</td>
				<td align="left">{$MELDUNG.text}</td>
			</tr>
			{/foreach}
		</table>