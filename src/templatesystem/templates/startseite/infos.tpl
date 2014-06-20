{strip}		<script type="text/javascript">
			infos = true;
			{foreach from=$MILSORT item=UNIT name=unit}special.push('{$UNIT}');
			{/foreach}
		</script>
		<div class="bar">
			<div class="text">Worum geht es in dem Spiel?</div>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner">
				In der nahen Zukunft haben Großkonzerne die Macht an sich gerissen. In Syndicates übernimmst du die Kontrolle über einen von ihnen und hast die Wahl zwischen einer von fünf Fraktionen.<br />
				Vertrete die Interessen deines Syndikats, handle mit Militäreinheiten am Weltmarkt oder stehle unliebsamen Konkurrenten ihr Geld. Forsche um deinen Gegnern einen Schritt voraus zu sein und erkläre deinen Feinden den Krieg.<br />
				Erwirtschafte als Erster die meisten Punkte und spiele im Team um den ersten Platz.<br />
				Wirtschaftliche und strategische Herausforderungen erwarten dich und das alles <strong>komplett kostenlos</strong> und direkt im Browser spielbar.
			</div>
			<div class="l_foot"></div>
		</div>
		<br />
		<div class="bar">
			<div class="text">Infos über die Fraktionen</div>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner">
				<table width="100%">
					<colgroup width="20%" span="5"></colgroup>
					<thead>
						<tr>
							{foreach from=$FRAKTIONEN item=FRAK name=logos}<td align="center" {if !$smarty.foreach.logos.last}class="right"{/if} style="border-bottom:none;"><img src="images/startseite/100_{$FRAK.race}.png" alt="{$FRAK.shortname}" /></td>
							{/foreach}
						</tr>
						<tr>
							{foreach from=$FRAKTIONEN item=FRAK name=names}<td align="center" class="{if !$smarty.foreach.names.last}right{/if}">{$FRAK.name}</td>
							{/foreach}
						</tr>
					</thead>
				</table>
				<br />
				<table width="100%" style="text-align:center;">
					<tr>
						<td id="hover_boni" class="toggle_infos bottom right left top" style="font-size:16px; background-color:#999; cursor:pointer;"><span class="tick_hover_boni infos_tick_left"></span>Fraktionsspezifische Boni<span class="tick_hover_boni infos_tick_right"></span></td>
					</tr>
					<tr>
						<td>
							<div id="infos_hover_boni" style="overflow:hidden;">
								<table width="100%">
									<colgroup width="20%" span="5"></colgroup>
									<tr>
										{foreach from=$FRAKTIONEN item=FRAK name=boni}<td align="left" valign="top"  class="d_bottom{if !$smarty.foreach.boni.last} right{/if}" style="font-size:12px;">
												{foreach from=$FRAK.desc item=DESC name=descs}<div {if !$smarty.foreach.descs.last}style="margin-bottom:5px;"{/if}>{$DESC}</div>
												{/foreach}
										</td>{/foreach}
									</tr>
									<tr>
										<td></td><td></td><td></td><td></td><td></td><td></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<br />
				<table width="100%" style="text-align:center;">
					<tr>
						<td id="hover_spy" class="toggle_infos bottom right left top" style="font-size:16px; background-color:#999; cursor:pointer;"><span class="tick_hover_spy infos_tick_left"></span>Spionageeinheiten<span class="tick_hover_spy infos_tick_right"></span></td>
					</tr>
					<tr>
						<td>
							<div id="infos_hover_spy" style="overflow:hidden;">
								<table width="100%">
									<colgroup width="33%" span="3"></colgroup>
									<tr>
										{foreach from=$FRAKTIONEN.uic.spys item=SPY}
										<td valign="top" class="d_bottom" style="font-size:12px;">
											<div align="center" style="margin-bottom:5px;"><strong>{$SPY.name}</strong></div>
											<div style="margin-bottom:5px;">Sabotagepunkte: {$SPY.op}</div>
											<div style="margin-bottom:5px;">Spionagepunkte: {$SPY.ip}</div>
											<div style="margin-bottom:5px;">Spio.verteidigungsp.: {$SPY.dp}</div>
										</td>
										{/foreach}
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<br />
				<table width="100%" style="text-align:center;">
					<tr>
						<td id="hover_mil" class="toggle_infos bottom right left top" style="font-size:16px; background-color:#999; cursor:pointer;"><span class="tick_hover_mil infos_tick_left"></span>Militäreinheiten<span class="tick_hover_mil infos_tick_right"></span></td>
					</tr>
					<tr>
						<td>
							<div id="infos_hover_mil" style="overflow:hidden;">
								<table width="100%">
									<colgroup width="20%" span="5"></colgroup>
									{foreach from=$MILSORT item=UNIT name=unit}<tr id="tr_{$UNIT}">
										{foreach from=$FRAKTIONEN item=FRAK name=mil}{if $smarty.foreach.unit.iteration > 2 || $smarty.foreach.mil.iteration == 3}<td align="left" valign="top" class="d_bottom{if !$smarty.foreach.mil.last && $smarty.foreach.unit.iteration > 2} right{/if}" style="font-size:12px;">
											<div align="center" style="margin-bottom:5px;"><strong>{$FRAK.mil.$UNIT.name}</strong></div>
											<div style="margin-bottom:5px; float:left; width:50%;">AP: {$FRAK.mil.$UNIT.op}</div>
											<div style="margin-bottom:5px;">VP: {$FRAK.mil.$UNIT.dp}</div>
											<div style="overflow:hidden;" class="special_{$UNIT}">Special: {$FRAK.mil.$UNIT.specials}</div>
										</td>{else}<td class="d_bottom"></td>{/if}{/foreach}
									</tr>{/foreach}
								</table>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="l_foot"></div>
		</div>
		<br />
		<div class="bar">
			<div class="text">Screenshots</div>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner">
				<table width="100%" id="table_screenshot">
					<colgroup width="20%" span="5"></colgroup>
					<tbody>
						<tr valign="middle">
							<td>
								Style: BF<br />
								Militärübersicht
							</td>
							<td>
								Style: UIC<br />
								Statusseite
							</td>
							<td>
								Style: SL<br />
								Forschungsseite
							</td>
							<td>
								Style: NEB<br />
								Gebäudeübersicht
							</td>
							<td>
								Style: NoF<br />
								Global Market
							</td>
						</tr>
						<tr valign="middle" height="190px">
							<td><a href="images/startseite/mil_big.png" rel="screens" class="lightbox_el" target="_blank"><img src="images/startseite/mil_small.png" alt="mil_small" /></a></td>
							<td><a href="images/startseite/sta_big.png" rel="screens" class="lightbox_el" target="_blank"><img src="images/startseite/sta_small.png" alt="sta_small" /></a></td>
							<td><a href="images/startseite/fos_big.png" rel="screens" class="lightbox_el" target="_blank"><img src="images/startseite/fos_small.png" alt="fos_small" /></a></td>
							<td><a href="images/startseite/geb_big.png" rel="screens" class="lightbox_el" target="_blank"><img src="images/startseite/geb_small.png" alt="geb_small" /></a></td>
							<td><a href="images/startseite/gm_big.png" rel="screens" class="lightbox_el" target="_blank"><img src="images/startseite/gm_small.png" alt="gm_small" /></a></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="l_foot"></div>
		</div>
{/strip}