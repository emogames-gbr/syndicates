{strip}				{if $ROUND}
					<div style="float:right"><a href="?{$HTTP_REF}">Zurück</a></div>
					<div style="width:25%; border-bottom:1px solid #666; height:25px;">Statistiken von {if $ROUND < 3}Beta {$ROUND}{else}Runde {$ROUND-2}{/if}:</div>
					<div align="center" style="width:600px; margin:0 auto; text-align:center;">
						{if $DATA}
							{if $RANKINGS}
								{if $DET_ID}
									{include file="stats.konz.tpl"}
								{else}
									{if $SHOW == 'konz'}{assign var='NAME' value='Konzern'}{else}{assign var='NAME' value='Syndikat'}{/if}
									<table width="100%">
										<thead>
											<tr>
												<td colspan="{if $SHOW == 'konz'}5{else}4{/if}">Die {if $TYPE == 'nw'}stärksten{else}größten{/if}{if $TYPE} {$FRAKTIONEN.$FRAK.name}{/if} {$NAME}e</td>
											</tr>
											<tr>
												<td width="30px"></td>
												<td align="left">{$NAME}</td>
												{if $SHOW == 'konz'}<td width="30px">Fraktion</td>{/if}
												<td width="100px" align="right">Land</td>
												<td width="100px" align="right">Networth</td>
											</tr>
										</thead>
										<tbody>
											{foreach from=$RANKINGS item=RANK name=ranking}
											<tr>
												<td align="right">{$smarty.foreach.ranking.iteration}.</td>
												<td align="left">{if $SHOW == 'synd'}{$RANK.name}{else}<a href="?action=hof&round={$ROUND}&show={$SHOW}&type={$TYPE}{if $FRAK}&frak={$FRAK}{/if}&det_id={$RANK.id}">{$RANK.name}</a>{/if} (#{$RANK.rid})</td>
												{if $SHOW == 'konz'}<td align="center">{$FRAKTIONEN[$RANK.race].tag}</td>{/if}
												<td align="right">{$RANK.land|number_format}</td>
												<td align="right">{$RANK.nw|number_format}</td>
											</tr>
											{/foreach}
										</tbody>
									</table>
								{/if}
							{else}
							<table width="100%">
								<thead>
									<tr>
										<td colspan="2" style="border:none; padding:15px; font-size:20px;">Top 100</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td width="50%"><a href="?action=hof&round={$ROUND}&show=synd&type=land">Die größten Syndikate</a></td>
										<td width="50%"><a href="?action=hof&round={$ROUND}&show=synd&type=nw">Die stärksten Syndikate</a></td>
									</tr>
									<tr>
										<td><a href="?action=hof&round={$ROUND}&show=konz&type=land">Die größten Konzerne</a></td>
										<td><a href="?action=hof&round={$ROUND}&show=konz&type=nw">Die stärksten Konzerne</a></td>
									</tr>
								</tbody>
							</table>
							<br />
							<table width="100%">
								<thead>
									<tr>
										<td colspan="2">Nach Konzerntypen</td>
									</tr>
								</thead>
								<tbody>
									{foreach from=$FRAKTIONEN item=FRAK}
									<tr>
										<td width="50%"><a href="?action=hof&round={$ROUND}&show=konz&type=land&frak={$FRAK.race}">{$FRAK.name}</a></td>
										<td width="50%"><a href="?action=hof&round={$ROUND}&show=konz&type=nw&frak={$FRAK.race}">{$FRAK.name}</a></td>
									</tr>
									{/foreach}
								</tbody>
							</table>
							<br />
							<hr width="50%" />
							<br />
							<table width="100%">
								<thead>
									<tr>
										<td colspan="2">Anteil der Spieler</td>
									</tr>
								</thead>
								<tbody id="tbody_chart" {if !$AJAX}style="display:none;"{/if}>
									<tr>
										<td colspan="2">
											<div id="anteil_chart" style="height:290px; width:80%; margin:auto;"></div>
										</td>
									</tr>
								</tbody>
								{if !$AJAX}<tbody id="tbody_text">
									{foreach from=$FRAKTIONEN item=FRAK}
									<tr>
										<td width="50%">{$FRAK.name}</td>
										<td width="50%">{$FRAK.num} ({$FRAK.prozent}%)</td>
									</tr>
									{/foreach}
								</tbody>{/if}
							</table>
							{literal}<script type="text/javascript">
								var plot_data = [{/literal}{foreach from=$FRAKTIONEN item=FRAK}['{$FRAK.shortname}', {$FRAK.num}],{/foreach}{literal}];
								var plot_color = [{/literal}{foreach from=$FRAKTIONEN item=FRAK}'{$FRAK.color}',{/foreach}{literal}];
							</script>{/literal}
							<hr width="50%" />
							<br />
							<table width="100%">
								<thead>
									<tr>
										<td colspan="2">Sonstiges</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td width="50%">Spieler gesamt</td>
										<td width="50%">{$NUM_PLAYER}</td>
									</tr>
									<tr>
										<td>Angriffe Insgesamt</td>
										<td>{$NUM_ATTS|number_format}</td>
									</tr>
									<tr>
										<td>Spionageaktionen insgesamt</td>
										<td>{$NUM_SPIES|number_format}</td>
									</tr>
									<tr>
										<td>Land gekauft insgesamt</td>
										<td>{$BUY_LAND|number_format} ha</td>
									</tr>
									<tr>
										<td>Durchschnittliche Konzernstärke</td>
										<td>{$AVG_NW|number_format} Nw</td>
									</tr>
									<tr>
										<td>Durchschnittliche Konzerngröße</td>
										<td>{$AVG_LAND|number_format} ha</td>
									</tr>
								</tbody>
							</table>
							{/if}
							<br /><br />
						{else}
							<br />
							Für diese Runde existieren noch keine Ergebnisse
							<br /><br />
						{/if}
						<a href="?{$HTTP_REF}">Zurück</a>
					</div>
				{else}
					<div align="center">
						Statistiken von allen Runden, die bisher gespielt wurden
						<hr width="50%" />
						{foreach from=$ROUNDS item=ROUND}
							<a href="?action=hof&round={$ROUND.id}">Ergebnisse {if $ROUND.round == -2}Beta 1{elseif $ROUND.round == -1}Beta 2{else}Runde {$ROUND.round}{/if}</a><br />
						{/foreach}
						<br />
					</div>
				{/if}
{/strip}