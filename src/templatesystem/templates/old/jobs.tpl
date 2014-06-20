{if !$action}
	{if !$jobtaken}
		<br>
		<table class=tableOutline cellspacing=1 cellpadding=5>
			<tr>
				<td class=tableInner2>
					<a href="jobs.php?action=new" class=linkAufTableInner>Neuen Auftrag einstellen</a>
				</td>
			</tr>
		</table>
		<br>
		<br>
		<table class=tableOutline cellspacing=1 cellpadding=4 width=98%>
			<tr>
				<td colspan=5 width=100% class=tableHead align=center>
					Verfügbare Aufträge
				</td>
			</tr>
			<tr class=tableInner2>
				<td>
					<a class=linkaufTableInner href="jobs.php?sortby=type&dir={$dir}"><u>Auftragstyp:</u></a>
				</td>
				<td>
					Ziel: (<a class=linkaufTableInner href="jobs.php?sortby=rid&rdir={$rdir}">
					<u>nach #</u></a>, <a class=linkaufTableInner href="jobs.php?sortby=nw&ndir={$rdir}"><u>nach nw</u></a>)
				</td>
				<td>
					<a class=linkaufTableInner href="jobs.php?sortby=worth&wdir={$wdir}"><u>Entlohnung:</u></a>
				</td>
				<td>
					Auftraggeber:
				</td>
				<td>
					Aktion:
				</td>
			</tr>
			{if $ausgabeSPY}
				<tr class="tableInner2"> 
					<td align="left" colspan="5">&nbsp;Spionage:</td> 
				</tr>
				{foreach from=$ausgabeSPY item=job}
					<tr class=tableInner1>
						<td>
							{$job[0]}
						</td>
						<td>
							{$job[1]}
						</td>
						<td>
							{$job[2]}
						</td>
						<td>
							{$job[3]}
						</td>
						<td>
							{$job[4]}
						</td>
					</tr>
				{/foreach}
			{/if}
			{if $ausgabeSABB}
				<tr class="tableInner2"> 
					<td align="left" colspan="5">&nbsp;Sabotage:</td> 
				</tr>
				{foreach from=$ausgabeSABB item=job}
					<tr class=tableInner1>
						<td>
							{$job[0]}
						</td>
						<td>
							{$job[1]}
						</td>
						<td>
							{$job[2]}
						</td>
						<td>
							{$job[3]}
						</td>
						<td>
							{$job[4]}
						</td>
					</tr>
				{/foreach}
			{/if}
			{if $ausgabeATT}
				<tr class="tableInner2"> 
					<td align="left" colspan="5">&nbsp;Angriffe:</td> 
				</tr>
				{foreach from=$ausgabeATT item=job}
					<tr class=tableInner1>
						<td>
							{$job[0]}
						</td>
						<td>
							{$job[1]}
						</td>
						<td>
							{$job[2]}
						</td>
						<td>
							{$job[3]}
						</td>
						<td>
							{$job[4]}
						</td>
					</tr>
				{/foreach}
			{/if}	
		</table>
		<br><br>
	{else}
		<table class=tableOutline cellspacing=1 cellpadding=4 width=98%>
			<tr>
				<td align=center class=tableHead colspan=3>
					Aktueller Auftrag
				</td >
				<td align=right class=tableHead colspan=2>
					verbleibende Zeit:
					{$jobtaken[0]}
				</td>
			</tr>
			<tr class=tableInner1>
				<td width=20%>
					Ziel:
				</td>
				<td width=18%>
					Auftragstyp:
				</td>
				<td width=22%>
					Entlohnung:
				</td>
				<td width=20%>
					Sonstiges:
				</td>
				<td width=20%>
					Aktion:
				</td>
			</tr>
			<tr class=tableInner1>
				<td>
					{$jobtaken[1]}
				</td>
				<td>
					{$jobtaken[2]}
				</td>
				<td>
					{$jobtaken[3]}
				</td>
				<td>
					{$jobtaken[4]}
				</td>
				<td>
					<form style="margin:0px" action=jobs.php method=post>
						<input type=hidden name=ia value=break>
						<input type=hidden name=job_id value={$jobtaken[5]}>
						<input type=submit value="Auftrag abbrechen">
					</form>
				</td>
			</tr>
		</table>
		<br><br>	
	{/if}
	{if $JOBS}
		<table class=tableOutline cellspacing=1 cellpadding=5 width=98%>
			<tr>
				<td align=center class=tableHead colspan=5>
					Meine Aufträge
				</td>
			</tr>
			<tr class=tableInner1>
				<td width=20%>
					Ziel:
				</td>
				<td width=18%>
					Auftragstyp:
				</td>
				<td width=22%>
					Entlohnung:
				</td>
				<td width=20%>
					Sonstiges:
				</td>
				<td width=20%>
					Aktion:
				</td>
			</tr>
		{foreach from=$JOBS item=JOB}
			<tr class=tableInner1>
				<td>
				{$JOB[0]}
				</td>
				<td>
				{$JOB[1]}
				</td>
				<td>
				{$JOB[2]}
				</td>
				<td>
				{$JOB[3]}
				</td>
				<td>
					<form style="margin:0px" action=jobs.php method=post>
						<input type=hidden name=ia value=back>
						<input type=hidden name=job_id value={$JOB[4]}>
						<input type=submit value="zurücknehmen">
					</form>
				</td>
			</tr>
		{/foreach}		
		</table>
	{/if}
	<br><br>
	<form action=jobs.php method=post>
		<input type=hidden name=ia value=alljobs>
		Aufträge ohne Nachfrage annehmen: <input type=checkbox name=alljobs {$checked}> <input type=submit value=abschicken>
	</form>
	<br><br>
	<table cellspacing=1 cellpadding=2 border=0 width=350 class="tableOutline" align=center>
		<tr>
			<td class="tableHead" colspan=4 align=center height=26><b>Legende</b></td>
		</tr>
		<tr>
			<td align=center height=26 class="tableInner1"><a href="#" class="konzernAttacked">##</a></td><td class=tableInner2 colspan=3>Innerhalb von 24 Std. <strong>einmal</strong> erfolgreich angegriffen</td>
		</tr>
		<tr>
			<td align=center height=26 class="tableInner1"><a href="#" class="konzernHeavyAttacked">##</a></td><td class=tableInner2 colspan=3>Innerhalb von 24 Std. <strong>mehrmals</strong> erfolgreich angegriffen</td>
		</tr>
	</table>
{/if}
{if $action == "new"}
	<br><br>
	<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline">
		<tr>
			<td align=center class="tableHead" colspan=3>Auftrag einstellen</td>
			{$stdform}
		</tr>
		<tr>
			<td class="tableInner1" align=left width=30%>
					Syndikat wählen:<br>
					{$actionlink}&arid={$lastrid}"><<</a>
					<input type=hidden name="number" value="{$number}">
					<input type=hidden name="credits" value="{$credits}">
					<input type=hidden name="type" value="{$type}">
					(#<input name=arid value={$arid} size=3>)
					<input type=submit name=changesyn value="wählen">
					 {$actionlink}&arid={$nextrid}">>></a>
			</td>
			<td class="tableInner1" align=left width=70%>
				{include file="js/jobs.js.tpl"}
				{$select1}
				<input type=submit name=changetarget value="wählen">
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left>
				Auftragstyp:
			</td>
			<td class="tableInner1" align=left>
				{$select2}
				<br>
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left>
				Bezahlung:<br>
			</td>
			<td class="tableInner1" align=left>
				<table class=tableOutline width=100% cellspacing=0 cellpadding=0>
						<tr class=tableInner1>
							<td width=20%>
								<input type="button" value="Min." onClick="setMin()"
									{if !$changetarget}
									  disabled
									{/if}
								/>
								<input type="button" value="<<" onClick="setDown()"
									{if !$changetarget}
									  disabled
									{/if}
								/>
								<input id="costs" size=10 name=credits value="{$costs}"
									{if !$changetarget}
									  disabled
									{/if}
								/>
								<input type="button" value=">>" onClick="setUp()"
									{if !$changetarget}
									  disabled
									{/if}
								/>
								<input type="button" value="Max." onClick="setMax()"
									{if !$changetarget}
									  disabled
									{/if}
								/> Credits
							</td>
						</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left colspan=2>
				Auftrag <input name="number" value="{$number}" size=2
				{if !$changetarget}
				  disabled
				{/if}
				> mal ausführen. {$jshelp1}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left colspan=2>
				Mindestergebnis: 
				<input id="minresult" name="param" size=5 value="{$landgain}"
				{if !$changetarget}
				  disabled
				{/if}
				>&nbsp;&nbsp; {$jshelp2}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left colspan=2>
				Auftrag anonym ausführen:
				<input name="anonym" type="checkbox"
				{if !$changetarget}
				  disabled
				{/if}
				> (1,25 fache Kosten, der Auftraggeber wird versteckt)
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=center colspan=2>
				<input type=submit name=submit value="Auftrag einstellen"
				{if !$changetarget}
				  disabled
				{/if}
				>
				</form>
			</td>
		</tr>
	</table>
{/if}		