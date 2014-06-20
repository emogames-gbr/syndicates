<br>
{if $action=="poll"}
	{if $voted}
		<form action="polls.php" method="post" style="margin:0px">
			<table cellspacing=1 cellpadding=5 border=0 class="tableOutline"  width=600>
				<tr class=tableHead>
					<td align=center colspan=2>
						Abstimmen: {$pollname} {$adminadd}
					</td>
				</tr>
				{foreach from=$polloptions item=option}
					{if $option[0]}
						<tr class=tableInner1>
							<td align=left>
								{$option[0]}
							</td>
							<td width=10 align=right>
								<input name="option_id[]" value="{$option[1]}" type="{if !$multi}radio{else}checkbox{/if}">
							</td>
						</tr>
					{/if}
				{/foreach}
				<tr class=tableInner1>
					<td align=center colspan=2>
						<input type="hidden" name="action" value="vote">
						<input type="hidden" name="pollid" value="{$pollid}">
						<input type="submit" value="abstimmen">
					</td>
				</tr>
			</table>
		</form>
	{/if}
	<br>
	<table cellspacing=1 cellpadding=5 border=0 class="tableOutline"  width=600>
		<tr class=tableHead>
			<td align=center colspan=3>
				Ergebnisse: {$pollname} {$adminadd}
			</td>
		</tr>
		<tr class=tableInner2>
			<td align=center>
				Antwort
			</td>
			<td align=center colspan=2>
				Stimmen
			</td>
		</tr>
		{foreach from=$polloptions item=option}
			<tr class=tableInner1>
				<td align=left>
					{$option[2]}
				</td>
				<td width=101 align=left>
					<img src="{$layout}/dotpixel.gif" height="10" width="{$option[3]}">
				</td>
				<td align=center witdh=50>
					{$option[4]} ({$option[5]}%)
				</td>
			</tr>
		{/foreach}
	</table>
	{if $ispresidente}
		<br>
		<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" >
			<tr class=tableInner1>
				<td align=center >
					{$yellowdot} <a class="linkAufTableInner" href="polls.php?view=delete&poll_id={$pollid}">Umfrage löschen</a>
				</td>
			</tr>
		</table>
	{/if}
{elseif $action=="create"}
	<br>
	{if $ispresidente}
		<form action="polls.php" method="post" style="margin:0px">
			<table cellspacing=1 cellpadding=5 border=0 class="tableOutline"  width=600>
				<tr class=tableHead>
					<td align=center colspan=2>
						Neue Umfrage erstellen
					</td>
				</tr>
				<tr class=tableInner1>
					<td>
						Umfrage:
					</td>
					<td>
						<input name="name" size=35 value="{$name}">
					</td>
				</tr>
				<tr class=tableInner1>
					<td>
						Anzahl möglicher Antworten:
					</td>
					<td>
						<input name="number" size=5 value="{$number}">
					</td>
				</tr>
				<tr class=tableInner1>
					<td>
						Mehrere Antworten ermöglichen
					</td>
					<td>
						<input name="multi" type="checkbox">
					</td>
				</tr>
				{if $IS_ADMIN}
				<tr class=tableInner1>
					<td>
						Globale Umfrage
					</td>
					<td>
						<input name="global_poll" type="checkbox">
					</td>
				</tr>
				{/if}
				<tr class=tableInner1>
					<td colspan=2 align=center>
						<input type="submit" value="weiter">
						<input type="hidden" name="view" value="create2">
					</td>
				</tr>
			</table>
		</form>
	{/if}
{elseif $action=="create2"}
	<br>
	{if $ispresidente}
		<form action="polls.php" method="post" style="margin:0px">
			<table cellspacing=1 cellpadding=5 border=0 class="tableOutline"  width=600>
				<tr class=tableHead>
					<td align=center colspan=2>
						Neue Umfrage erstellen
					</td>
				</tr>
				<tr class=tableInner1>
					<td>
						Umfrage:
					</td>
					<td>
						<input name="name" size=35 value="{$name}">
					</td>
				</tr>
				<tr class=tableInner1>
					<td>
						Gültig:
					</td>
					<td>
						<input name="dauer" size=3 value="2"> Tage
					</td>
				</tr>
				{foreach from=$polloptions item=option}
				<tr class=tableInner1>
					<td>
						Antwort {$option[0]}:
					</td>
					<td>
						<input name="a{$option[0]}" size=35 value="{$option[1]}">
					</td>
				</tr>
				{/foreach}
				<tr class=tableInner1>
					<td  align=center colspan=2>
						{if $gotAlly}
							Umfrage auch Allianzpartnern zugänglich machen <input type=checkbox name=allyok>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						{/if}
						<input type="submit" value="weiter">
						<input type="hidden" name="number" value="{$number}">
						<input name="multi" type="hidden" value="{$multi}">
						{if $IS_ADMIN}<input name="global_poll" type="hidden" value="{$GLOBAL_POLL}">{/if}
						<input type="hidden" name="action" value="create">
					</td>
				</tr>
			</table>
		</form>					
	{/if}
{elseif !$action}
	{if $ispresidente}
		<table cellspacing=1 cellpadding=5 border=0 class="tableOutline"  width=300>
			<tr>
				<td class="tableInner1">{$yellowdot} <a class="linkaufTableInner" href="polls.php?view=create">Neue Umfrage erstellen</a></td>
			</tr>
		</table>
	{/if}
	<br>
	<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width=600>
		<tr class=tableHead>
			<td>
				Umfrage
			</td>
			<td>
				erstellt von
			</td>
			<td>
				Gestartet am
			</td>
			<td>
				läuft bis
			</td>
			<td>
				Beteiligung
			</td>
			<td>
				Details
			</td>
		</tr>
		{if $polls}
			{foreach from=$polls item=poll}
				<tr class=tableInner1>
					<td>
						{$poll[0]}
					</td>
					<td>
						{$poll[1]}
					</td>
					<td>
						{$poll[2]}
					</td>
					<td>
						{if $poll[3]}
							{$poll[3]}
						{else}
							beendet
						{/if}
					</td>
					<td>
						{$poll[4]} Stimmen
					</td>
					<td>
						<a href="polls.php?view=poll&poll_id={$poll[5]}" class=linkAuftableInner>
							{$poll[6]}
						</a>
					</td>
				</tr>
			{/foreach}
		{else}
			<tr class=tableInner1>
				<td colspan=6>
					Keine Umfragen vorhanden
				</td>
			</tr>
		{/if}
	</table>
{/if}