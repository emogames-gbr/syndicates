{if !$nostats}
	<center>
	<font class=siteGround><br>

	{if $oldstats}
		<!-- ### STATS , AlTE RUNDEN RELATION ###  -->
		<table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="siteGround">
			<tr>
				<td width="400" align="center">
	{/if}
	<!-- ### STATSTABLE ###  -->
	<br>
	<table width="400" cellpadding="0" cellspacing="1" border="0" class="tableOutline" align="center">
		<tr>
			<td>
				<table width="400" cellpadding="3" cellspacing="0" border="0" class=tableHead>
					<tr>
						<td width="400" colspan="2" align="center">
							<b>
								{$roundausgabe} 
								<a class="konzernAttacked" href="../index.php?action=stats#action=hof&round={$round}" target="_blank">
									<i>Details</i>
								</a>
							</b>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableHead2>
					<tr>
						<td width="400" colspan="2" align="center">Allgemein</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=3 class=tableInner1 width=100%>
					<tr>
						<td width="170"><b>{$raceuicshort}</b></td>
						<td width="130"><b>{$uicplayers}</b>&nbsp;&nbsp;({$uicrel}%)</font></td>
					</tr>
					<tr>
						<td><b>{$raceslshort}</b></td>
						<td><b>{$slplayers}</b>&nbsp;&nbsp;({$slrel}%)</font></td>
					</tr>
					<tr>
						<td><b>{$racepbfshort}</b></td>
						<td><b>{$pbfplayers}</b>&nbsp;&nbsp;({$pbfrel}%)</font></td>
					</tr>
					<tr>
						<td><b>{$racenebshort}</b></td>
						<td><b>{$nebplayers}</b>&nbsp;&nbsp;({$nebrel}%)</font></td>
					</tr>
					<tr>
						<td><b>{$racenofshort}</b></td>
						<td><b>{$nofplayers}</b>&nbsp;&nbsp;({$nofrel}%)</font></td>
					</tr>
					<tr>
						<td><b>Spieler Gesamt</b></td>
						<td><b>{$totalplayers}</b></font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableHead2 width="400">
					<tr>
						<td  colspan="2" align="center">Konzern</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableInner1 width="400">
					<tr>
						<td width="170" align="left"><b>Konzernchef</b></td>
						<td width="130" align="left">{$stats.rulername}</font></td>
					</tr>
					<tr>
						<td><b>Konzernname</b></td>
						<td>{$stats.syndicate}</font></td>
					</tr>
					<tr>
						<td><b>Fraktion</b></td>
						<td>{$statsfrakname}</font></td>
					</tr>
					<tr>
						<td><b>Syndikat</b></td>
						<td>{if $oldround}{$ridname} (#{$stats.rid}){else}Runde noch nicht gestartet{/if}</font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableHead2>
					<tr>
						<td width="400" colspan="2" align="center">Statistik</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableInner1 cellpadding=4 cellspacing=0 width=100%>
					<tr>
						<td width="100%" colspan=2 align="left"><b>Angriffe (Standard,Belagerung,Eroberung,im Krieg):</b></td>
					</tr>
					<tr>
						<td width="75"><b>ausgeführt:</b></td>
						<td width="300" align="left">
							<table width="75%" class="tableInner1" align="left">
								<tr>
									<td width=75>
										({$stats_attack_numberdone_normal},
									</td>
									<td width=75>
										{$stats_attack_numberdone_siege},
									</td>
									<td width=75>
										{$stats_attack_numberdone_conquer},
									</td>
									<td width=75>
										{$stats_attack_numberdone_waraffected})
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>davon erfolgreich</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_numberdone_won_normal},
									</td>
									<td width=75>
										{$stats_attack_numberdone_won_siege},
									</td>
									<td width=75>
										{$stats_attack_numberdone_won_conquer},
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>bestes Ergebnis</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_largest_won_normal},
									</td>
									<td width=75>
										{$stats_attack_largest_won_siege},
									</td>
									<td width=75>
										{$stats_attack_largest_won_conquer},
									</td>
									<td width=75>
										{$stats_attack_largest_won_waraffected})
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>insgesamt erobert</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_total_won_normal},
									</td>
									<td width=75>
										{$stats_attack_total_won_siege},
									</td>
									<td width=75>
										{$stats_attack_total_won_conquer},
									</td>
									<td width=75>
										{$stats_attack_total_won_waraffected})
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>erlittene Angriffe</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_numbersuffered_normal},
									</td>
									<td width=75>
										{$stats_attack_numbersuffered_siege},
									</td>
									<td width=75>
										{$stats_attack_numbersuffered_conquer},
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>davon verloren</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_numbersuffered_lost_normal},
									</td>
									<td width=75>
										{$stats_attack_numbersuffered_lost_siege},
									</td>
									<td width=75>
										{$stats_attack_numbersuffered_lost_conquer},
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>größter Verlust</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_largest_loss_normal},
									</td>
									<td width=75>
										{$stats_attack_largest_loss_siege},
									</td>
									<td width=75>
										{$stats_attack_largest_loss_conquer},
									</td>
									<td width=75>
										{$stats_attack_largest_loss_waraffected})
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>gesamt Verlust</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										({$stats_attack_total_loss_normal},
									</td>
									<td width=75>
										{$stats_attack_total_loss_siege},
									</td>
									<td width=75>
										{$stats_attack_total_loss_conquer},
									</td>
									<td width=75>
										{$stats_attack_total_loss_waraffected})
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>ausgef&uuml;hrte Spionageaktionen </b></td>
						<td>{$stats_spyopsdone}</font></td>
					</tr>
					<tr>
						<td><b>davon erfolgreich</b></td>
						<td>{$stats_spyopsdonewon} ({$stats_spyopsdonewon_prozent}%)</font></td>
					</tr>
					<tr>
						<td><b>Spione verloren</b></td>
						<td>{$stats_spies_lost}</font></td>
					</tr>
					<tr>
						<td><b>Ressourcen gestohlen</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=75>
										Cr:{$stats_moneystolen},
									</td>
									<td width=75>
										MWh:{$stats_energystolen}
									</td>
								</tr>
								<tr>
									<td width=75>
										t:{$stats_metalstolen},
									</td>
									<td width=75>
										P:{$stats_sciencepointsstolen}
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>Beste Ergebnisse:</b></td>
						<td>
							<table width=75% class=tableInner1 align=left>
								<tr>
									<td width=150>
										Cr:{$stats_max_steal_money},
									</td>
									<td width=150>
										MWh:{$stats_max_steal_energy}
									</td>
								</tr>
								<tr>
									<td width=150>
										t:{$stats_max_steal_metal},
									</td>
									<td width=150>
										P:{$stats_max_steal_sciencepoints}
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>erlittene Spionageaktionen</b></td>
						<td>{$stats_spyopssuffered}</font></td>
					</tr>
					<tr>
						<td><b>davon verloren</b></td>
						<td>{$stats_spyopssufferedlost} ({$stats_spyopssufferedlost_prozent}%)</font></td>
					</tr>
					<tr>
						<td><b>Spione exekutiert</b></td>
						<td>{$stats_spies_executed}</font></td>
					</tr>
					<tr>
						<td><b>Land gekauft</b></td>
						<td>{$stats_landexplored}</font></td>
					</tr>
					<tr>
						<td><b>Gr&ouml;&szlig;te Konzernst&auml;rke</b></td>
						<td>{$stats_largestnetworth}</font></td>
					</tr>
					<tr>
						<td><b>Gr&ouml;&szlig;ter Grundbesitz</b></td>
						<td>{$stats_largestland} Land</font></td>
					</tr>
					{if $showcurrentNWrank}
					<tr>
						<td><b>aktueller Platz NW-Rank</b></td>
						<td>{$user_rank}</font></td>
					</tr>
					{elseif $showNWrank}
					<tr>
						<td><b>Platz im NW-Rank</b></td>
						<td>{$stats_endrank}</font></td>
					</tr>
					{else}
					<tr>
						<td><b>Platz im NW-Rank</b></td>
						<td>kein Platz gelistet</td>
					</tr>						
					{/if}
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=tableHead width="400">
					<tr>
						<td  colspan="2" align="center">&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	{if $oldstats}
				</td>
				<td valign="top" width="200">
					<table class=siteGround width=75% align=center>
						<tr>
							<td>
								<center>
									<br>
									<font color="white" class="siteGround">
										<u>Statistiken zu fr&uuml;heren Runden:</u>
									</font>
									<br><br>
									{foreach from=$round_stats item=temp}
									<a class="konzernAttacked" href="stats.php?action=stats&round={$temp.a}">
										Statistiken f&uuml;r Runde {$temp.ag}
									</a>
									<br>
									{/foreach}
									{if $showakt}
									<a class="konzernAttacked" href="stats.php?action=stats&round={$origround}">
										Aktuelle Runde
									</a>
									{/if}
									<br>
								</center>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	{/if}	
	{if $showCurrentStats}
	<br>
	<table align=center width=500 style="table-layout:fixed" cellpadding=1 cellspacing=1 class="tableOutline">
	   	<colgroup>
	        <col width="100">
	        <col width="400">
	    </colgroup>
	    <tr class="tableInner1"  width="100%">
	   		<td width="100%" colspan="2" height="20px">
	   			Networth / Land Verlauf {$verlauf.$time_mode}:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	        	<a href="{$this}?time_mode=2" class="linkAufTableInner">dieser Runde</a> | 
	        	<a href="{$this}?time_mode=1" class="linkAufTableInner">der letzten 20 Stunden</a>
	        </td>	
	    </tr>
		{if $showstat}
			{foreach from=$data item=temp}
		
		<tr width="100%" class="tableInner1">
			<td rowspan="2" width="100" align="center">
				{$temp.zeitanzeige}
			</td>
            <td width=400 align=center>
	            <table width="100%" class="tableInner1" cellspacing=0 cellpadding=0>
		            <tr>
    		            <td valign="middle" width="300" align=left style="vertical-align:bottom">
							<img src="{$ripf}dotpixel.gif" align="middle" height="4" width="{$temp.tempnwwidth}">				
                	    </td>
                    	<td width="95" align="right" colspan="2">
	        	            <font style="font-size:9px;">{$temp.o_nw} Nw</font>
    	                </td>
						<td width="5">
							&nbsp;
						</td>
	                </tr>
    	        </table>
            </td>
		</tr>
		<tr width="100%" class="tableInner1">
        	<td width=500 align=center>
            	<table class="tableInner1" cellspacing=0 cellpadding=0>
                	<tr>
                    	<td width="300" align=left style="vertical-align:bottom">
							<img src="{$ripf}dotpixel_blau.gif" height="4" width="{$temp.templandwidth}">
                        </td>
                        <td width="95" align="right" colspan="2">
                        	<font style="font-size:9px;">{$temp.o_land} ha</font>
                        </td>
						<td width="5">
							&nbsp;
						</td>
                    </tr>
                </table>
            </td>
		</tr>
		
			{/foreach}
		{else}
		<tr width="100%" class="tableInner1">
			<td colspan="2" width="100%" align="center">
				Es liegen noch keine Daten vor.
			</td>
		</tr>
		{/if}
	</table>
	{/if}
{else}
	<br>Für diesen Account sind noch keine Statistiken verfügbar
{/if}