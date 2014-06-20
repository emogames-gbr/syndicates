{strip}	{if !$AJAX}<div class="bar">
			<div class="text">Eigene Statistiken</div>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner">
				<table width="100%" style="text-align:center;">
					<tr>
						<td width="75%" valign="top">
							<div id="stats_content">{/if}
								{if $KONZ}
									{include file="stats.konz.tpl"}
									{if $ROUNDSTATUS > 0}
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
								{else}
									{if $ROUNDS}
									Zu der angegebenen Runde konnten keine Statistiken gefunden werden
									{else}
									Für deinen Konzern gibt es noch keine Statistiken
									{/if}
								{/if}
							</div>{if !$AJAX}
						</td>
						{if $ROUNDS}
						<td width="25%" valign="top" class="left">
							<h3>Statistiken zu früheren Runden:</h3>
							{foreach from=$ROUNDS item=ROUND name=own_rounds}
								{if $ROUND == $GLOBALS.round && $GLOBALS.roundstatus != 0}<a class="ajax" href="?action={$ACTION}&round={$ROUND}">Aktuelle Runde</a>
								{else}
								<a class="ajax" href="?action={$ACTION}&round={$ROUND}">Runde {if $ROUND < 3}Beta{$ROUND}{else}{$ROUND-2}{/if}</a>
								{/if}
								{if !$smarty.foreach.own_rounds.last}<br />{/if}
							{/foreach}
						</td>
						{/if}
					</tr>
				</table>
			</div>
			<div class="l_foot"></div>
		</div>
		{literal}
		<script type="text/javascript">
			$('.ajax').each(function(){
				var href = $(this).attr('href');
				$(this).attr('href', '');
				$(this).fragment($.deparam.querystring(href));
			});
			function change_content(l){
				if(l){
					$('#stats_content').slideUp(1000, function(){
						$.ajax({ 'url' : 'index.php?ajax=true&'+l, 'dataType' : 'text script' })
						.success(function(data){
							//$('#content1').fadeOut(200, function(){ $('#content1').html(data).fadeIn(200); init(); });
							$('#stats_content').html(data);
							$('#stats_content').slideDown(1000);
							init();
						});
					});
				}
			}
		</script>
		{/literal}{/if}
{/strip}