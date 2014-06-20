{strip}				<table align="center" width="100%">
					<thead>
						<tr>
							<td>Warticker:</td>
						</tr>
					</thead>
					<tbody>{assign var='NOTHING' value=false}{/strip}
						{foreach from=$WARTICKER item=DAY}
						{if $DAY.data}{assign var='NOTHING' value=true}{strip}
						<tr>
							<td>
								<table width="100%" style="box-shadow:3px 3px 5px #666; -moz-box-shadow:3px 3px 5px #666; -webkit-box-shadow:3px 3px 5px #666; margin-top:10px;">
									<tr>
										<td class="bottom right left top" style="font-size:16px; background-color:#999; text-align:center;">{$DAY.name}</td>
									</tr>
									<tr>
										<td class="bottom right left" style="background-color:#BBB">
											<table>
												{/strip}{foreach from=$DAY.data item=DATA}{strip}
												<tr>
													<td align="right" width="90px;">{$DATA.time|date_format:"%H:%M"} Uhr -</td>
													<td>
														{/strip}{if $DATA.status == 'start'}
															{if $DATA.a_allyname}Die Allianz <em>"{$DATA.a_allyname}"{else}Das Syndikat <em>"{$DATA.a_synname}{/if}" ({foreach from=$DATA.a_rids item=A name=ars}{if !$smarty.foreach.ars.first}, {/if}#{$A}{/foreach})</em>
															 erklärt
															 {if $DATA.e_allyname}der Allianz <em>"{$DATA.e_allyname}"{else}dem Syndikat <em>"{$DATA.e_synname}{/if}" ({foreach from=$DATA.e_rids item=E name=ers}{if !$smarty.foreach.ers.first}, {/if}#{$E}{/foreach})</em>
															 den Krieg.
														{else}
															Der Krieg zwischen
															{if $DATA.a_allyname}der Allianz <em>"{$DATA.a_allyname}"{else}dem Syndikat <em>"{$DATA.a_synname}{/if}" ({foreach from=$DATA.a_rids item=A name=ars}{if !$smarty.foreach.ars.first}, {/if}#{$A}{/foreach})</em>
															und
															{if $DATA.e_allyname}der Allianz <em>"{$DATA.e_allyname}"{else}dem Syndikat <em>"{$DATA.e_synname}{/if}" ({foreach from=$DATA.e_rids item=E name=ers}{if !$smarty.foreach.ers.first}, {/if}#{$E}{/foreach})</em>
															ist beendet.
															{if $DATA.won == 'a'}Der Angreifer konnte den Krieg für sich entscheiden.{elseif $DATA.won == 'e'}Der Verteidiger konnte die Niederlage abwehren.{else}Der Krieg wurde durch die Spielleitung beendet.{/if}
														{/if}{strip}
													</td>
												</tr>
												{/strip}{/foreach}{strip}
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						{/strip}{/if}
						{/foreach}{strip}
						{if !$NOTHING}
						<tr>
							<td>Es wurde in den letzten 2 Tagen kein Krieg gestartet oder beendet</td>
						</tr>
						{/if}
					</tbody>
				</table>
{/strip}