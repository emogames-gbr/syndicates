	{if $SYNBUILD == 1}
				<br>
		<table width=400 cellpadding=0 cellspacing=0 border=0 class=tableOutline align=center>
			<tr><td>
				<table width=400 cellpadding=5 cellspacing=1 border=0>
					<tr class=tableHead >
						<td colspan=2><b>Errichtung eines Monuments! (Verbleibende Zeit: <b>{$DUTY_TIME}</b> Ticks)</b></td>
					</tr>
					<tr class=tableHead2 >
						<td  colspan=2>Ihr Syndikat errichtet das Monument <u>{$MONU_IN_BUILD}</u></td>
					</tr>
					<tr class=tableHead2 >
						<td>Bereits investiert</td>
						<td>Benötigte Ressourcen</td>
					</tr>
					<tr class=tableInner1 >
						<td>{$INVESTET_P} P</td>
						<td>{$COST} P</td>
					</tr>
				</table>
			</td></tr>
		</table>
	{/if}
		<br>
		<table width=600 cellpadding=0 cellspacing=0 border=0 class=tableOutline align=center>

			<tr><td>
				<table width=600 cellpadding=5 cellspacing=1 border=0>
					<tr class=tableHead>
						<td ><b>Monument</b></td>
						<td ><b>Bonus</b></td>
						<td nowrap><b>Status</b></td>
						{if $IS_PRESI_AND_NO_MONU == 1}
						<td ><b>Aktion</b></td>
						{/if}
					</tr>

					{foreach name=all_monus item=MONU from=$ALL_MONUS}
							<tr class=tableInner1>
								<td>{$MONU.name}</td>
								<td>{$MONU.descr}</td>
								<td nowrap>
								{if $MONU.notfree == 1}
									<img src={$GP_PATH}dot-rot.gif hspace=5 border=0> In Besitz von Syndikat {$MONU.owner_name}  (#{$MONU.owner_id})
								{elseif   $MONU.notfree == 2}
									<img src={$GP_PATH}dot-gelb.gif hspace=5 border=0> In Bau von{if $MONU.buildsyns_count > 1}<br>{/if}
									{assign var="SYN_COUNT" value="1"}
									{foreach from=$MONU.buildsyns item=SYN}
									{$SYN.name} (#{$SYN.synd_id}){if $MONU.buildsyns_count >= $SYN_COUNT++}<br>{/if}
									{/foreach}
									{* <a href=javascript:info('monumente','werbaut') class=linkAuftableInner><img src={$GP_PATH}/_help.gif border=0 valign=absmiddle></a> *}	
								{else}
									<img src={$GP_PATH}dot-gruen.gif hspace=5 border=0> Frei
								{/if}	
								</td>
				
								{if $IS_PRESI_AND_NO_MONU == 1}
									{if $MONU.buildable == 1}
									<td nowrap><a href={$MONU.buildlink} class=linkAufTableInner>bau starten</a></td>
									{elseif $STARTSPERRE}
									<td align="center">*</td>
									{else}
									<td nowrap></td>
									{/if}
								{/if}
							</tr>					
					{/foreach }	
															
									
						
				</table>
			</td></tr>
		</table>
		<br />
		<div align="center">* ein Monument kann erst 24 Stunden nach Rundenstart begonnen werden</div>
		<br />
	
		<br>
				<table width=300 cellpadding=0 cellspacing=0 border=0 class=tableOutline align=center>
			<tr><td>
				<table width=300 cellpadding=5 cellspacing=1 border=0>
					<tr class=tableHead >
						<td colspan=2>Beteiligung am Bau von Monumenten <a href=javascript:info('monumente','beteiligung') class=linkAuftableInner><img src={$GP_PATH}_help.gif border=0 valign=absmiddle></a></td>
					</tr>
					<tr class=tableInner1>
						<td>Ich beteilige mich am Bau:</td>
						<td>
							<form method=post action=monumente.php>
								<input type=hidden name=action value=beteiligung>
								<select name=wert>
									<option value=0  {if $INVEST_IN_IT == 0} selected {/if}>Nein
									<option value=1 {if $INVEST_IN_IT == 1} selected {/if}>Ja
								</select>
								<input type=submit value=absenden>
							</form>
						</td>
					</tr>
				</table>
			</td></tr>
		</table>
