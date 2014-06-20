		<!--  TODO: wenn .tpls implementiert, kommt dies hier an deren dateiköpfe -->
		<br>
		<table width=100% border=0 cellspacing=0 cellpadding=0 align=center>
			{$VOTECODE}
			<tr>
				<td width=400><b class=titleH1>{$HEADLINE}</b></td>
				<td width=200 align=right>
					<a href="{$LINK_HILFESEITE}" target="_blank" class="linkAuftableInner">
						<img onmouseover="showover(event,'','');contentover('<table border=0 cellspacing=0 cellpadding=1 class=tableOutline><tr><td><table cellspacing=0 cellpadding=2><tr><td class=tableInner1>{$TOOLTIPPTEXT}</td></tr></table></td></tr></table>')"  onmouseout="hideover()" src="{$GP_PATH}_help_bigger.gif" border=0 valign="absmiddle" />
					</a>
				</td>
			</tr>
			<tr>
				<td colspan=2 height=1 class=titleLine></td>
			</tr>
			{if $SHOW_HELP == 1}
			<tr>
				<td colspan=2>
					<table cellpadding=5 width=100% align=center>
						<tr>
							<td class=i style="border: 1px solid">
								{$HILFETEXT} (Fragezeichen oben rechts)
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan=2 height=1 class=titleLine></td></tr>
			{/if}
		</table>
	{if $SYNBUILD == 1}
				<br>
		<table width=400 cellpadding=0 cellspacing=0 border=0 class=tableOutline align=center>
			<tr><td>
				<table width=400 cellpadding=5 cellspacing=1 border=0>
					<tr class=tableHead >
						<td colspan=2><b>Errichtung eines Monuments!</b></td>
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
									<img src={$GP_PATH}dot-rot.gif hspace=5 border=0> In Besitz von Syndikat {$MONU.owner_name}  #({$MONU.owner_id})
								{elseif   $MONU.notfree == 2}
									<img src={$GP_PATH}dot-gelb.gif hspace=5 border=0> In Bau <a href=javascript:info('monumente','werbaut') class=linkAuftableInner><img src={$GP_PATH}/_help.gif border=0 valign=absmiddle></a>	
								{else}
									<img src={$GP_PATH}dot-gruen.gif hspace=5 border=0> Frei
								{/if}	
								</td>
				
								{if $IS_PRESI_AND_NO_MONU == 1}
									{if $MONU.buildable == 1}
									<td nowrap><a href={$MONU.buildlink} class=linkAufTableInner>bau starten</a></td>
									{else}					
									<td nowrap></td>
									{/if}
								{/if}
							</tr>					
					{/foreach }	
															
									
						
				</table>
			</td></tr>
		</table>
	
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
