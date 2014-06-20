{set var=$SYMBOL value=array("1" => "pokalgold.gif", "2" => "pokalsilber.gif", "3" => "pokalbronze.gif", "4" => "medaillegold.gif", "5" => "medaillesilber.gif", "6" => "medaillebronze.gif", "11" => "Syn1.png", "12" => "Syn2.png", "13" => "Syn3.png")}
<br />
<table cellspacing="0" cellpadding="0" border="0" class="tableOutline" align="center" width="96%">
	<tr>
		<td>
			<table cellspacing="1" cellpadding="5" border="0" width="100%">
				<tr class="tableHead">
					<td width="140px" class="tableInner2" align="right"><img src="{$GP_PATH}{$RACEICON}-logo-klein.gif" align="absmiddle"></td>
					<td width="290px" colspan="1">{$DETAILS.rulername} von  {$DETAILS.syndicate} (#{$DETAILS.rid})</td>
					<td width="110px">
						{if !$KONZERN.own}
								{if $KONZERN.own_syn}
									<a href="{$KONZERN.url_lager}"><img src="{$GP_PATH}_syn_transfer.gif" border="0" alt="Lager" title="Transfer an {$DETAILS.syndicate}"></a> 
								{else}
									<a href="{$KONZERN.url_angriff}"><img src="{$GP_PATH}_syn_attack.gif" border="0" alt="Angriff" title="{$DETAILS.syndicate} angreifen"></a>
									<a href="{$KONZERN.url_spies}"><img src="{$GP_PATH}_syn_spie.gif" border="0" alt="Spionage" title="Spionage gegen {$DETAILS.syndicate}"></a>
								{/if}
								<a href="{$KONZERN.url_msg}"><img src="{$GP_PATH}_syn_message_letter.gif" border="0" alt="Nachricht" title="{$DETAILS.syndicate} eine Nachricht senden"></a>
								{if $KONZERN.buddy}
									<br><b>(dein Buddy)</b>
								{else}
									<a href="{$KONZERN.url_buddy}"><img src="{$GP_PATH}_praesi.gif" border="0" alt="Buddy" title="als Buddy hinzufügen"></a>
								{/if}
						{/if}
					</td>
				</tr>
				{if $OLD_NAMES}
				<tr class="tableHead2">
					<td><b>Frühere Namen:</b></td>
					<td colspan="2">{$OLD_NAMES}</td>
				</tr>
				{/if}
				<tr class="tableInner1">
					<td valign="top" width="140"><strong>Branche:</strong></td>
					<td width="290">{$BRANCHE}</td>
					<td rowspan="3" align="center" valign="top" width="110" class="tableInner2">
						<table class="tableOutline" cellspacing="1" cellpadding="0" border="0">
							<tr>
								<td class="tableInner1">{if $DETAILS.image}<img src="{$WWWDATA}konzernimages/{$KBILD_PREFIX}{$DETAILS.id}.{$DETAILS.image}" border="0">{else}<img src="{$GP_PATH}/phalter.jpg" width="101" height="136" border="0">{/if}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="tableInner1">
					<td valign=top><strong>Beschreibung:</strong></td>
					<td>{$DESCRIPTION}</td>
				</tr>
				{if $SHOWDETAILS}
				<tr class="tableInner1">
					<td><strong>Spielt seit:</strong></td>
					<td>{if $STARTROUND == -1}Beta 1{elseif $STARTROUND == 0}Beta 2{elseif $STARTROUND == -2 || !$STARTROUND}Einer der Runden 12, 13, 14 oder 15 (unbekannt){else}Runde {$STARTROUND}{/if}</td>
				</tr>
				{if $HONORS}
				<tr class="tableInner1">
					<td valign="top"><strong>Auszeichnungen:</strong></td>
					<td colspan="2">
						<br />
						<table width="400" align="left" class="tableInner1">
							{foreach from=$HONORS item=HONOR name=HONOR}
							{if $smarty.foreach.HONOR.iteration % 3 == 1 % 3}
							<tr>
								<td>
							{elseif $smarty.foreach.HONOR.iteration % 3 == 2 % 3}
								<td>
							{elseif $smarty.foreach.HONOR.iteration % 3 == 3 % 3}
								<td>
							{/if}
								<table border="0" cellpadding="0" cellspacing="0" width="100" class="tableInner1" align="center">
									<tr>
										<td width="100%">
										<p style="font-family: Arial; font-size: 11px; font-weight: 700" align="center">
											<img border="0" src="{$GP_PATH}{$SYMBOL[$HONOR.honorcode]}" {if $HONOR.honorcode >= 10 &&  $HONOR.honorcode <= 20}width="50" height="50"{else}width="45" height="72"{/if}><br/>
											{if $HONOR.round == 1}Beta 1{elseif $HONOR.round == 2}Beta 2{else}Runde {$HONOR.round-2}{/if}: {if $HONOR.honorcode >= 10 &&  $HONOR.honorcode <= 20}Syn-Rang{else}Rang{/if} {$HONOR.rank}
										</p></td>
									</tr>
								</table>
								<br />
							{if $smarty.foreach.HONOR.iteration % 3 == 1 % 3}
								<td>
							{elseif $smarty.foreach.HONOR.iteration % 3 == 2 % 3}
								<td>
							{elseif $smarty.foreach.HONOR.iteration % 3 == 3 % 3}
								<td>
							<tr>
							{/if}
							{/foreach}
						</table>
					</td>
				</tr>
				{/if}
				{/if}
			</table>
		</td>
	</tr>
</table>
&nbsp;<br />
<a href="syndicate.php?rid={$RID}" class="linkAufsiteBg">Zurück zum Syndikat #{$RID}</a>