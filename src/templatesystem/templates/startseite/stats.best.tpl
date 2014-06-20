{strip}				{if $INIT}
					{assign var='INIT' value=false}
					<table align="center" width="100%">
						<colgroup width="50%" span="2"></colgroup>
						<thead>
							<tr>
								<td colspan="2">Bestenliste:</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								{foreach from=$TOP item=OUT name="rank"}
								{if $smarty.foreach.rank.iteration % 2 && !$smarty.foreach.rank.first}</tr><tr>{/if}
								<td style="vertical-align:top;" {if $smarty.foreach.rank.iteration % 2 && $smarty.foreach.rank.last}colspan="2" align="center"{elseif $smarty.foreach.rank.iteration % 2} align="right"{/if}>
									{include file="stats.best.tpl" LIST=$OUT}
								</td>
								{/foreach}
							</tr>
						</tbody>
					</table>
					<br />
				{else}
					{if $LIST}
						<table width="220px" style="text-align:center; margin:10px 20px 0px 20px; box-shadow:3px 3px 5px #666; -moz-box-shadow:3px 3px 5px #666; -webkit-box-shadow:3px 3px 5px #666;">
							<tr>
								<td class="bottom right left top" style="font-size:16px; background-color:#999;">Top 3 {$LIST.name}</td>
							</tr>
							<tr>
								<td class="bottom right left" style="height:141px; background-color:#BBB">
									{foreach from=$LIST.data item="DATA" name="list"}
										<font style="font-weight:bold">{$DATA.name} (#{$DATA.rid})</font><br />
										{$DATA.value|number_format} {$LIST.type}{if !$smarty.foreach.list.last}<hr style="border:0px; background-color:#666; height:1px; width:20%;" />{/if}
									{foreachelse}
										keine Daten vorhanden
									{/foreach}
								</td>
							</tr>
						</table>
					{/if}
				{/if}
{/strip}