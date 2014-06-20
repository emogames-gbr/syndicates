{strip}					{if $KONZ}
					<table width="500px" align="center">
						<colgroup>
							<col width="50%">
							<col width="50%">
						</colgroup>
						<thead>
							<tr>
								<td colspan="2">Statistiken zu {$KONZ.syndicate} (#{$KONZ.rid}) Runde {if $ROUND < 3}Beta {$ROUND}{else}{$ROUND-2}{/if}</td>
							</tr>
						</thead>
						<tbody align="left">
							<tr>
								<td colspan="2" class="bottom" align="center" style="padding-top:10px;">Konzerndaten</td>
							</tr>
							<tr>
								<td>Konzernname</td>
								<td>{$KONZ.syndicate}</td>
							</tr>
							<tr>
								<td>Konzernchef</td>
								<td>{$KONZ.rulername}</td>
							</tr>
							<tr>
								<td>Fraktion</td>
								<td>{$FRAKTIONEN[$KONZ.race].name}</td>
							</tr>
							<tr>
								<td>Syndikat</td>
								<td>{$KONZ.synname} (#{$KONZ.rid})</td>
							</tr>
							<tr>
								<td colspan="2" class="bottom" align="center" style="padding-top:10px;">Statistiken</td>
							</tr>
							<tr>
								<td colspan="2">
									<table width="100%">
										<colgroup width="25%" span="1"></colgroup>
										<colgroup width="15%" span="5"></colgroup>
										<tr style="text-align:right;">
											<td align="left" style="padding-left:0px; padding-right:0px;">Angriffe</td>
											<td style="padding-left:0px; padding-right:0px;">Normal</td>
											<td style="padding-left:0px; padding-right:0px;">Belager.</td>
											<td style="padding-left:0px; padding-right:0px;">Erober.</td>
											<td style="padding-left:0px; padding-right:0px;">Spio.zerst.</td>
											<td style="padding-left:0px; padding-right:0px;">im Krieg</td>
										</tr>
									</table>
								</td>
							</tr>
							{foreach from=$KONZ.att_stats item=ST}<tr>
								<td colspan="2">
									<table width="100%">
										<colgroup width="25%" span="1"></colgroup>
										<colgroup width="15%" span="5"></colgroup>
										<tr style="text-align:right;">
											<td align="left" style="padding-left:0px; padding-right:0px;">{$ST.name}</td>
											<td style="padding-left:0px; padding-right:0px;">{$ST.normal|number_format}</td>
											<td style="padding-left:0px; padding-right:0px;">{$ST.siege|number_format}</td>
											<td style="padding-left:0px; padding-right:0px;">{$ST.conquer|number_format}</td>
											<td style="padding-left:0px; padding-right:0px;">{$ST.killspies|number_format}</td>
											<td style="padding-left:0px; padding-right:0px;">{$ST.waraffected|number_format}</td>
										</tr>
									</table>
								</td>
							</tr>{/foreach}
							<tr>
								<td>ausgeführte Spionageaktionen</td>
								<td>{$KONZ.spyopsdone|number_format}</td>
							</tr>
							<tr>
								<td>davon erfolgreich</td>
								<td>{$KONZ.spyopsdonewon|number_format} ({$KONZ.spyopsdonepercent|number_format:1}%)</td>
							</tr>
							<tr>
								<td>Spione verloren</td>
								<td>{$KONZ.spies_lost|number_format}</td>
							</tr>
							<tr>
								<td>Ressourcen gestohlen</td>
								<td>
									{$KONZ.moneystolen|number_format} Cr<br />
									{$KONZ.energystolen|number_format} MWh<br />
									{$KONZ.metalstolen|number_format} t<br />
									{$KONZ.sciencepointsstolen|number_format} P
								</td>
							</tr>
							<tr>
								<td>Beste Ergebnisse</td>
								<td>
									{$KONZ.max_steal_money|number_format} Cr<br />
									{$KONZ.max_steal_energy|number_format} MWh<br />
									{$KONZ.max_steal_metal|number_format} t<br />
									{$KONZ.max_steal_sciencepoints|number_format} P
								</td>
							</tr>
							<tr>
								<td>erlittene Spionageaktionen</td>
								<td>{$KONZ.spyopssuffered|number_format}</td>
							</tr>
							<tr>
								<td>davon verloren</td>
								<td>{$KONZ.spyopssufferedlost|number_format} ({$KONZ.spyopssufferedpercent|number_format:1}%)</td>
							</tr>
							<tr>
								<td>Spione exekutiert</td>
								<td>{$KONZ.spies_executed|number_format}</td>
							</tr>
							<tr>
								<td>Land gekauft</td>
								<td>{$KONZ.landexplored|number_format} ha</td>
							</tr>
							<tr>
								<td>Größte Konzernstärke</td>
								<td>{$KONZ.largestnetworth|number_format} Nw</td>
							</tr>
							<tr>
								<td>Größter Grundbesitz</td>
								<td>{$KONZ.largestland|number_format} ha</td>
							</tr>
							<tr>
								<td>Platz im NW-Rank</td>
								<td>{$KONZ.endrank|number_format}</td>
							</tr>
						</tbody>
					</table>
					{else}
					<br /><br />
					Der angegebene Konzern konnte nicht gefunden werden
					{/if}
{/strip}