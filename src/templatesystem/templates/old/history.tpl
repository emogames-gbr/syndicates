{if $ANGRIFFSDB}
	{if !$VIEW}
		<br>
		<form style="margin:0px;" method="get" action="history.php">
			<table bgcolor="black" cellspacing="1" cellpadding="4" align="center">
				<tr class="tableHead">
					<td colspan="2">
						Angriffs und Spionagedatenbank
					</td>
				</tr>
				<tr class="tableInner1">
					<td >
						Was soll angezeigt werden?
					</td>
					<td>
						<select name="view">
							<option value="attacks" {if $SELECTVIEW == "attacks"}selected{/if}>Angriffe</option>
							<option value="spyactions" {if $SELECTVIEW == "spyactions"}selected{/if}>Spionageaktionen</option>
						</select>
					</td>
				</tr>
				<tr class="tableInner1">
					<td colspan="2">
						Vom <input size="2" name="vontag" value="{$VONTAG}">.
							<input size="2" name="vonmonat" value="{$VONMONAT}">.
							<input size="3" name="vonjahr" value="{$VONJAHR}"> bis 
							<input size="2" name="bistag" value="{$BISTAG}">.
							<input size="2" name="bismonat" value="{$BISMONAT}">.
							<input size="3" name="bisjahr" value="{$BISJAHR}">
					</td>
				</tr>	
				<tr class="tableInner1">
					<td colspan="2" align="center">
						<input type="submit" value="anzeigen">
					</td>
				</tr>
			</table>
		</form>
	{else}
	
	{if $VIEW == "spydetails"}
		{if $LOG.aid == $STATUS.id}
		<br>
		<table bgcolor="black" cellspacing="1" cellpadding="4" align="center">
			<tr class="tableHead">
				<td colspan="10" align="center">
					Spionageaktion {$SPYACTIONSNAME} vom 
					{$LOG.o_time} gegen {$TARGET.syndicate} (#{$TARGET.rid})
				</td>
			</tr>
			<tr class="tableHead2">
				<td colspan="10" align="center">
					Ergebnis:<br>
				</td>
			</tr>
			<tr class="tableInner1" >
				<td colspan="2">
					{$LOG.bericht}
				</td>
			</tr>
		</table>
		<br>
		<center>
			<a class="linkAuftableInner" href="history.php?view=spyactions{$STD_LINK}">Zur&uuml;ck</a>
		</center>
		{/if}
	{elseif $VIEW == "attackdetails"}
		{if $LOG.aid == $STATUS.id}
			<br>
			<table bgcolor="black" cellspacing="1" cellpadding="4" align="center">
				<tr class="tableHead">
					<td colspan="10" align="center">
						Angriff <b>"{$ATTACKTYPE}"</b> vom 
						{$LOG.o_time} gegen {$TARGET.syndicate} (#{$TARGET.rid})
					</td>
				</tr>
				<tr class="tableHead2">
					<td colspan="10" align="center">
						Angriffsbericht:<br>
					</td>
				</tr>
				<tr class="tableInner1" >
					<td colspan="2">
						{$LOG.bericht}
					</td>
				</tr>
			</table>
			<br>
			<center>
				<a class="linkAuftableInner" href="history.php?view=attacks{$STD_LINK}">Zur&uuml;ck</a>
			</center>
		{/if}
	{elseif $VIEW == "spyactions"}
		<br>
		<table bgcolor="black" cellspacing="1" cellpadding="4" align="center" width="90%">
			<tr class="tableHead">
				<td colspan="5">
					Spionageaktion von {$STATUS.syndicate} im Zeitraum vom {$VONTAG}.{$VONMONAT}.{$VONJAHR} bis {$BISTAG}.{$BISMONAT}.{$BISJAHR}
				</td>
			</tr>
			<tr class="tableHead2">
				<td>
					Datum
				</td>
				<td>
					Ziel
				</td>
				<td>
					Spionageaktion
				</td>
				<td>
					Erfolgreich
				</td>
				<td>
					Aktion
				</td>
			</tr>
		{if $SPYACTIONS}
			{foreach from=$SPYACTIONS item=VL}
			<tr class="tableInner1">
				<td>
					{$VL.o_time}
				</td>
				<td>
					{$VL.o_syndicate} (#{$VL.o_rid})
				</td>
				<td>
					{$VL.o_actionName}
				</td>
				<td>
					{if $VL.success}
						<font class=gruenAuftableInner>Ja</font>
					{else}
						<font class=achtungAuftableInner>Nein</font>
					{/if}
				</td>
				<td>
					<a class="linkAuftableInner" href="history.php?{$STDLINK}&view=spydetails&backview=$view&logid={$VL.id}">Details</a>
				</td>
			</tr>
			{/foreach}
		{else}
			<tr class="tableInner1">
				<td colspan="5">
					Sie haben im gewählten Zeitraum keine Spionageaktionen ausgeführt.
				</td>
			</tr>
		{/if}
		</table>
		<br>
		<center>
			<a class="linkAuftableInner" href="history.php?{$STD_LINK}">Zur&uuml;ck</a>
		</center>
	{elseif $VIEW == "attacks"}
		<br>
		<table bgcolor="black" cellspacing="1" cellpadding="4" align="center" width="90%">
			<tr class="tableHead">
				<td colspan="6">
					Angriffe im Zeitraum vom {$VONTAG}.{$VONMONAT}.{$VONJAHR} bis {$BISTAG}.{$BISMONAT}.{$BISJAHR}
				</td>
			</tr>
			<tr class="tableHead2">
				<td>
					Datum
				</td>
				<td>
					Ziel
				</td>
				<td>
					Angriffsart
				</td>
				<td>
					Erfolgreich
				</td>
				<td>
					Land erobert
				</td>
				<td>
					Aktion
				</td>
			</tr>
		{if $DATA}
			{foreach from=$DATA item=VL}
			<tr class="tableInner1">
				<td>
					{$VL.o_time}
				</td>
				<td>
					{$VL.o_syndicate} (#{$VL.o_rid})
				</td>
				<td>
					{$VL.o_type}
				</td>
				<td>
					{if $VL.winner == "a"}
						<font class=gruenAuftableInner>Ja</font>
					{else}
						<font class=achtungAuftableInner>Nein</font>
					{/if}
				</td>
				<td>
					{$VL.landgain}
				</td>
				<td>
					<a class="linkAuftableInner" href="history.php?{$STDLINK}&view=attackdetails&backview=$view&logid={$VL.id}">Details</a>
				</td>
			</tr>
			{/foreach}
		{else}
			<tr class="tableInner1">
				<td colspan="6">
					Sie haben im gewählten Zeitraum keine Angriffe ausgeführt.
				</td>
			</tr>
		{/if}
		</table>
		<br>
		<center>
			<a class="linkAuftableInner" href="history.php?{$STD_LINK}">Zur&uuml;ck</a>
		</center>		
	{/if}

	{/if}
{/if}