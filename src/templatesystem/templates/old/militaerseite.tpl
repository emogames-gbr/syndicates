{if $GOON}
{if $USERINPUT}
{$USERINPUT}
{else}
	{if $ACTION == "viewsynarmystats" && !$ISBASICSERVER}
		<br>
		<br>
		<center>
			<b>
				<u>
					Übersicht über die Übertragungen der einzelnen Syndikatsmitglieder in die Syndikatsarmee
				</u>
			</b>
		</center>
		<br>
		<table class="tableOutline" cellpadding="3" cellspacing="1" align="center" width="550">
			<tr class="tableHead">
				<td>
					Spieler
				</td>
				<td nowrap colspan="2" align="center">
					derzeit unterwegs
				</td>
				<td colspan="2" align="center">
					heute
				</td>
				<td colspan="2" align="center">
					gestern
				</td colspan="2">
				<td colspan="2" align="center">
					vorgestern
				</td>
				<td colspan="2" align="center">
					insgesamt
				</td>
			</tr>
			<tr class="tableHead2">
				<td>
					#
				</td>
				<td align="center">
					M
				</td>
				<td align="center">
					Ranger
				</td>
				<td align="center">
					M
				</td>
				<td align="center">
					Ranger
				</td>
				<td align="center">
					M
				</td>
				<td align="center">
					Ranger
				</td>
				<td align="center">
					M
				</td>
				<td align="center">
					Ranger
				</td>
				<td align="center">
					M
				</td>
				<td align="center">
					Ranger
				</td>
			</tr>
			{foreach from=$PLAYERDATA item=VL}
			<tr class="tableInner2">
				<td nowrap>
					{$VL.syndicate}
				</td>
				<td align="center" class="tableInner1">
					{$VL.o_data1_derzeit}
				</td>
				<td align="center" class="tableInner1">
					{$VL.o_data2_derzeit}
				</td>
				<td align="center">
					{$VL.o_data1_heute}
				</td>
				<td align="center">
					{$VL.o_data2_heute}
				</td>
				<td align="center" class="tableInner1">
					{$VL.o_data1_gestern}
				</td>
				<td align="center" class="tableInner1">
					{$VL.o_data2_gestern}
				</td>
				<td align="center">
					{$VL.o_data1_vorgestern}
				</td>
				<td align="center">
					{$VL.o_data2_vorgestern}
				</td>
				<td align="center" class="tableInner1">
					{if $VL.o_builddata_nach_spieler_total1}
						{$VL.o_builddata_nach_spieler_total1}
					{else}
						0
					{/if}
				</td>
				<td align="center" class="tableInner1">
					{if $VL.o_builddata_nach_spieler_total2}
						{$VL.o_builddata_nach_spieler_total2}
					{else}
						0
					{/if}
				</td>
			</tr>
		{/foreach}
			<tr class="tableHead">
				<td nowrap>
					gesamt
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_1_derzeit}
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_2_derzeit}
				</td>
				<td align="center">
					{$TOTAL_1_heute}
				</td>
				<td align="center">
					{$TOTAL_2_heute}
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_1_gestern}
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_2_gestern}
				</td>
				<td align="center">
					{$TOTAL_1_vorgestern}
				</td>
				<td align="center">
					{$TOTAL_2_vorgestern}
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_1_insgesamt}
				</td>
				<td align="center" class="tableHead2">
					{$TOTAL_2_insgesamt}
				</td>
			</tr>
		</table>
		<br>
		<br>
		{if $SYNARMYINORDER}
		<center>
			<b>
				<u>
					Folgende Einheiten sind gerade unterwegs in die Syndikatsarmee
				</u>
			</b>
		</center>
		<br>
		<br>
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline" align="center">
			<tr>
				<td>
					<table  border="0" cellspacing="1" width="100%" cellpadding="3">
						<tr class="tableHead">
							<td align="center">#</td>
							<td align="middle"> &nbsp;1 </td>
							<td align="middle"> &nbsp;2 </td>
							<td align="middle"> &nbsp;3 </td>
							<td align="middle"> &nbsp;4 </td>
							<td align="middle"> &nbsp;5 </td>
							<td align="middle"> &nbsp;6 </td>
							<td align="middle"> &nbsp;7 </td>
							<td align="middle"> &nbsp;8 </td>
							<td align="middle"> &nbsp;9 </td>
							<td align="middle"> 10 </td>
						</tr>
					{foreach from=$UNITTYPE item=VL}
						<tr class="tableInner1">
							<td width="105"> {$VL.name}</td>
							<td align="middle">{if $VL.in_1_Tick}{$VL.in_1_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_2_Tick}{$VL.in_2_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_3_Tick}{$VL.in_3_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_4_Tick}{$VL.in_4_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_5_Tick}{$VL.in_5_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_6_Tick}{$VL.in_6_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_7_Tick}{$VL.in_7_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_8_Tick}{$VL.in_8_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_9_Tick}{$VL.in_9_Tick}{else}-{/if}</td>
							<td align="middle">{if $VL.in_10_Tick}{$VL.in_10_Tick}{else}-{/if}</td>
						</tr>
					{/foreach}
					</table>
				</td>
			</tr>
		</table>
		{else}
		<center>
			<b>
				<u>
					Keine Einheiten unterwegs zur Syndikatsarmee!
				</u>
			</b>
		</center>
		{/if}
		<br><br><br>
		<a href="militaerseite.php" class="linkAufsiteBg">
			zurück zur Militärübersicht
		</a>
	{else} <!-- normale Militärseite -->
		{$ADDITIONAL_JAVASCRIPT}
		<center>
			<table>
				<tr>
					<td class="siteGround" align="center">
						Übersicht über alle vorhandenen Einheiten.<br>
						Zur Rekrutierung neuer Einheiten verwenden Sie bitte folgendes Formular.
					</td>
				</tr>
			</table>
			<br>
			<!-- Toggle Button für Buildstuff und Status -->
			<table>
				<tr valign="bottom">
					<td>
						<table cellpadding="5" cellspacing="1" border="0" width="250" class="tableOutline" style="margin-right:10px;">
							<tr class="tableHead">
								<td colspan="3" align="center">Ansicht</td>
							</tr>
							<tr class="tableInner1">
								<td  id="td_bauansicht" onMouseOver="checkOver(this)" onMouseOut="checkOut(this)" class="tableInner2" onClick="showStatusView(0)" align="center">
									Bauansicht
								</td>
								<td  id="td_status" onMouseOver="checkOver(this)" onMouseOut="checkOut(this)" onClick="showStatusView(1)" align="center">
									Militärstatus
								</td>
                                <td  id="td_berater" onMouseOver="checkOver(this)" onMouseOut="checkOut(this)" onClick="showStatusView(2)" align="center">
									Berater
								</td>
							</tr>
						</table>
					</td>		
			{if !$MILITAERQ}	<!-- kein Miliassi vorhanden -->
					<td>
						<table cellpadding="5" cellspacing="1" border="0" width="250" class="tableOutline" style="margin-right:10px;">
							<tr class="tableHead">
								<td align="center">
									Militärassistent
								</td>
							</tr>
							<tr class="tableInner1">
								<td>
									Mit diesem Assistenten können Sie Militär- und Spionageeinheiten in eine Warteschlange legen, 
									die nach und nach abgearbeitet wird.
								</td>
							</tr>
							<tr class="tableInner2">
								<td colspan="2" align="center">
									<a href="premiumfeatures.php" class="linkAufsiteBg">
										Mehr Informationen
									</a>
								</td>
							</tr>
						</table>
					</td>
			{/if}
				</tr>
			</table>
	<!-- BAUFORMULAR -->
	<div id="showBuildStuff" style="margin:0px;display:inline;" cellpadding="0" cellspacing="0">
		<form action="militaerseite.php" method="post">
			<table width="98%" cellpadding="0" cellspacing="0" border="0" class="tableOutline" align="center">
				<tr>
					<td width="100%" align="center">
						<table width="100%" cellpadding="5" cellspacing="1" border="0">
							<tr class="tableHead">
								<td>
									<b>Einheit</b>
								</td>
								<td>
									<b>
										{if ($MILITOTAL.away || $SPIESTOTAL.away) && ($MILITOTAL.market || $SPIESTOTAL.market)}
											vorh.
										{else}
											vorhanden
										{/if}
									</b>
								</td>
							{if $MILITOTAL.away || $SPIESTOTAL.away}
								<td nowrap>
									<b>
										{if $MILITOTAL.market || $SPIESTOTAL.market}
											Heimk.
										{else}
											auf Heimk.
										{/if}
									</b>
								</td>
							{/if}
							{if $MILITOTAL.market || $SPIESTOTAL.market}
								<td nowrap>
									<b>
										{if $MILITOTAL.away || $SPIESTOTAL.away}
											Markt
										{else}
											auf Markt
										{/if}
									</b>
								</td>
							{/if}
								<td nowrap>
									<b>Kosten pro Einheit</b>
								</td>
								<td>
									<b>baubar</b>
								</td>
								<td>
									<b>In Bau</b>
								</td>
								<td>
									<b>Anzahl</b>
								</td>
							</tr> 
						<!-- MILITÄREINHEITEN -->
						{foreach from=$UNITSTATS item=VL}
							<tr class="tableInner1">
								<td>
									{$VL.o_unitName}&nbsp;
									<a target="_blank" href="{$WIKI}{$VL.o_unitName}">
										{$VL.o_MilitaryToolTip}
									</a>
								</td>
								<td align="center">
									{$VL.o_status}
								</td>
							{if $MILITOTAL.away || $SPIESTOTAL.away}
								<td align="center">
									{$VL.o_away}
								</td>
							{/if}
							{if $MILITOTAL.market || $SPIESTOTAL.market}
								<td align="center">
									{$VL.o_market}
								</td>
							{/if}
								<td>
									{if $VL.o_credits}{$VL.o_credits} Cr,{/if}
									{if $VL.o_sciencepoints}{$VL.o_sciencepoints} P,{/if}
									{$VL.o_minerals} t, 
									{$VL.o_energy} MWh
								</td>
								<td align="center" id="max_{$VL.o_key}">{$VL.o_milbaubar}</td>
								<td align="center">
									{$VL.o_mil_imbau}
								</td>
								<td>
									<input class="input" type="text" value="0" id="build_{$VL.o_key}" name="{$VL.o_key}" size="3" tabindex="{$VL.o_tabindex}">
									&nbsp;&nbsp;
									<img align="absmiddle" width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxbuy('{$VL.o_key}');">
								</td>
							</tr>
						{/foreach}
							<tr class="tableInner2">
								<td colspan="{if $MILITOTAL.away || $SPIESTOTAL.away}{if $MILITOTAL.market || $SPIESTOTAL.market}8{else}7{/if}
											 {else}{if $MILITOTAL.market || $SPIESTOTAL.market}7{else}6{/if}{/if}">
									<hr class="highlightAuftableInner">
								</td>
							</tr>
							<tr class="tableInner2">
								<td colspan="{if $MILITOTAL.away || $SPIESTOTAL.away}{if $MILITOTAL.market || $SPIESTOTAL.market}8{else}7{/if}
											 {else}{if $MILITOTAL.market || $SPIESTOTAL.market}7{else}6{/if}{/if}">
									Sie haben noch Kapazitäten für 
									<b class="highlightAuftableInner">{$KAPAS_MILI}</b> Militäreinheiten 
									({if $KAPAS_MILI_PRC > 100}<span class="achtungAuftableInner">{$KAPAS_MILI_PRC}%</span>
									{else}{$KAPAS_MILI_PRC}%{/if} genutzt) und
	  								<b class="highlightAuftableInner">{$KAPAS_SPY}</b> Spionageeinheiten 
	  								({if $KAPAS_SPY_PRC > 100}<span class="achtungAuftableInner">{$KAPAS_SPY_PRC}%</span>
	  								{else}{$KAPAS_SPY_PRC}%{/if} genutzt).
									{if $STATUS.race == "nof"}
										<br>Sie haben noch Kapazitäten für 
										<b class="highlightAuftableInner">{$KAPAS_CARRIER}</b> Carrier 
										({if $KAPAS_CARRIER_PRC > 100}<span class="achtungAuftableInner">{$KAPAS_CARRIER_PRC}%</span>
										{else}{$KAPAS_CARRIER_PRC}%{/if} genutzt).
									{/if}
								</td>
							</tr>
							<tr class="tableInner2">
								<td colspan="{if $MILITOTAL.away || $SPIESTOTAL.away}{if $MILITOTAL.market || $SPIESTOTAL.market}8{else}7{/if}
											 {else}{if $MILITOTAL.market || $SPIESTOTAL.market}7{else}6{/if}{/if}">
									<hr class="highlightAuftableInner">
								</td>
							</tr>
						<!-- SPIONAGEEINHEITEN -->
						{foreach from=$SPYSTATS item=VL}
							<tr class="tableInner1">
								<td>
									{$VL.o_spyName}&nbsp;
									<a target="_blank" href="{$WIKI}{$VL._spyName}">
										{$VL.o_SpyToolTip}
									</a>
								</td>
								<td align="center">
									{$VL.o_status}
								</td>
							{if $MILITOTAL.away || $SPIESTOTAL.away}
								<td align="center">
									{$VL.o_away}
								</td>
							{/if}
							{if $MILITOTAL.market || $SPIESTOTAL.market}
								<td align="center">
									{$VL.o_market}
								</td>
							{/if}
								<td>
									{$VL.o_credits} Cr, {$VL.o_energy} MWh
								</td>
								<td align="center" id="max_{$VL.o_key}">{$VL.o_spiesbaubar}</td>
								<td align="center">{$VL.o_spies_imbau}</td>
								<td>
									<input class="input" type="text" value="0" name="{$VL.o_key}" id="build_{$VL.o_key}" size="3" tabindex="{$VL.o_tabindex}">
									&nbsp;&nbsp;
									<img align="absmiddle" width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxbuy('{$VL.o_key}');">
								</td>
							</tr>
						{/foreach}
						</table>
					</td>
				</tr>
			</table>
			<table width="550" cellpadding="5" cellspacing="2" border="0" class="siteGround" align="center">
				<tr>
					<td align="left" valign="bottom">
						<br>
						Aktuelle Baukosten für Militär: <b class="highlightAuftableInner">{$BUILDCOST_MIL_PERCENT}%</b><br>
						Aktuelle Baukosten für Spionageeinheiten: <b class="highlightAuftableInner">{$BUILDCOST_SPIES_PERCENT}%</b><br>
						Aktuelle Bauzeit für Militär: <b class="highlightAuftableInner">{$BUILDTIME_MIL_TICKS}</b> Ticks<br>
						Aktuelle Bauzeit für Spionageeinheiten: <b class="highlightAuftableInner">{$BUILDTIME_SPIES_TICKS}</b> Ticks
					</td>
					<td align="right" valign="top">
					{if $MILITAERQ}
						<input class="radio" type="radio" name="decision" value="queue" tabindex="9"> Bauassistent
					{/if}
						<input type="radio" name="decision" value="build" checked="yes" tabindex="10"> bauen
						<input type="radio" name="decision" value="raze" tabindex="11"> entlassen<br>
						<br>
					{if $STATUS.safety == 1}
						<input type="hidden" name="razereally" value="1">
					{/if}
						<input class="button" type="submit" value="Auftrag ausführen" tabindex="12">
					</td>
				</tr>
			</table>
		</form>
		<!-- MILITÄRASSISTENT -->
		{if $MILITAERQ}
		<br>
		<br>
		<table align="center" width="440">
			<tr>
				<td align="center">
					<table width="330" cellspacing="1" cellpadding="5" border="0" class="tableOutline">
						<tr class="tableHead">
							<td align="center" colspan="2">
								Militärassistent
							</td>
						</tr>
				{if $DATA}
					{foreach from=$DATA item=VL}
						<tr class="tableInner1">
							<td>{$VL.position} ::: {$VL.o_number} {$VL.o_unitName}</td>
							<td>
							{if $VL.position > 1}
								<a href="militaerseite.php?doings=modifyqueue&pos={$VL.position}&down=1" class="linkaufSiteBg">
									<img border="0" src="{$RIPF}_for_up.gif">
								</a>
							{/if}
							{if $VL.position < $ANZ_FOR}
								<a href="militaerseite.php?doings=modifyqueue&pos={$VL.position}&up=1" class="linkaufSiteBg">
									<img border="0" src="{$RIPF}_for_down.gif">
								</a>
							{/if}
								<a href="militaerseite.php?doings=unqueue&pos={$VL.position}" class="linkaufSiteBg">
									<img border="0" src="{$RIPF}_for_deselect.gif">
								</a>
							</td>
						</tr>
					{/foreach}
						<tr class="tableInner2">
							<td colspan="2" align="center">
								<a href="militaerseite.php?doings=unqueueall" class="linkAufsiteBg">
									Warteliste leeren
								</a>
							</td>
						</tr>
				{else}
						<tr class="tableInner1">
							<td colspan="2" align="center">
								Keine Einträge in der Warteliste.
							</td>
						</tr>
				{/if} 
					</table>
				</td>
			</tr>
		</table>
		{/if}
		<!-- SYNDIKATSARMEE --> 
		{if $SYNARMY_SHOW}
		<br><br>
		<br><br>
		<font class="normaltitle">
			<b>Syndikatsarmee</b>
		</font>
		<br><br>
		Einheiten in der Syndikatsarmee unterstützen Sie automatisch. Bitte beachten Sie, dass das Übertragen Ihrer 
		Einheiten in die Syndikatsarmee nicht mehr rückgängig gemacht werden kann.<br>
		<br>
		<form action="militaerseite.php" method="post">
			<input type="hidden" name="decision" value="spend">
			<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline" align="center">
				<tr>
					<td>
						<table width="550" cellpadding="5" cellspacing="1" border="0">
							<tr class="tableHead">
								<td colspan="4">
									<b>Eigene Einheiten</b>
								</td>
								<td colspan="2">
									<b>Einheiten der Syndikatsarmee</b>
								</td>
							</tr>
							<tr class="tableHead2">
								<td align="left">
									<b>Einheit</b>
								</td>
								<td align="center">
									<b>vorhanden</b>
								</td>
								<td align="center">
									<b>übertragen</b>
								</td>
								<td align="center">
									=>
								</td>
								<td align="center">
									<b>Einheit</b>
								</td>
								<td align="right">
									<b>vorhanden</b>
								</td>
							</tr>
							<tr class="tableInner1">
								<td align="left">
									{$OFFSPECS_NAME}
								</td>
								<td align="right">
									{$OFFSPECS_ATHOME}&nbsp;&nbsp;&nbsp;
								</td>
								<td align="center">
									<input type=text name=offspecs size=4 value="0">
								</td>
								<td align="center">
									=>
								</td>
								<td align="center">
									Marine
								</td>
								<td align="right">
									{$OFFSPECS_INSYNARMY}
								</td>
							</tr>
							<tr class="tableInner1">
								<td align="left">
									{$DEFSPECS_NAME}
								</td>
								<td align="right">
									{$DEFSPECS_ATHOME}&nbsp;&nbsp;&nbsp;
								</td>
								<td align="center">
									<input type=text name=defspecs size=4 value="0">
								</td>
								<td align="center">
									=>
								</td>
								<td align="center">
									Ranger
								</td>
								<td align="right">
									{$DEFSPECS_INSYNARMY}
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table width="550" cellpadding="5" cellspacing="2" border="0" class="siteGround" align="center">
				<tr>
					<td align="left" valign="top">
						<a href="?action=viewsynarmystats" class="linkAufsiteBg">{$YELLOWDOT} Einheitenübertragungen einsehen</a>
					</td>
					<td align="right" valign="top">
						<input class="button" type="submit" value="Einheiten übertragen">
					</td>
				</tr>
			</table>
		</form>
		{/if}
	</div>
	<div id="showStatusStuff" style="margin:0px;display:none;" cellpadding="0" cellspacing="0">
		{$MILITARYSTATUSVIEW}
	</div>
    <div id="showBeraterStuff" style="margin:0px;display:none;" cellpadding="0" cellspacing="0">
    	<br />
		{foreach from=$TABLES item=TABLE}
		{if !$TABLE.error}
			<b>
				<u>{$TABLE.name}</u>
			</b>
			<br><br>
			<table width="600" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
				<tr>
					<td>
						<table  border="0" cellspacing="1" width="100%" cellpadding="3">
							<tr class="tableHead">
								<td align=center>#</td>
								{foreach from=$HOURCOL item=CURRENT}
									<td align=middle> &nbsp;{$CURRENT}</td>
								{/foreach}
							</tr>
							{foreach from=$TABLE.rows item=ROW}
								<tr class="tableInner1">
									<td width=105>{$ROW.name}</td>
									{foreach from=$ROW.details item=DETAIL}
										<td align=middle>
											{$DETAIL}
										</td>
									{/foreach}
								</tr>
							{/foreach}
						</table>
					</td>
				</tr>
			</table>
		{else}
			<b>
				<u>{$TABLE.error}</u>
			</b>
			<br>
		{/if}
		<br><br>
	{/foreach}
	</div>
</center>
	{/if}
 {/if}
{/if}