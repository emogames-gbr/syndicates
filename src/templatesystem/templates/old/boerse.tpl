{*
WICHTIG:
> Änderungen im Titel der Formulare MÜSSEN auch in der jeweiligen JS-Funktion unter boerse.js.tpl geändert werden, da sonst die Funktion nichtmehr korrekt funktioniert
*}
{if $SHOW_TABLE == 'offer'}
<tr class="tableInner1" id="{$TABLE.type}_{$TABLE.id}" height="30px" type="offer">
	<td align="left">&nbsp;&nbsp;&nbsp;{$TABLE.name} (#{$TABLE.rid})</td>
	<td align="center">{$TABLE.number|number_format}</td>
	<td align="center">{if $TIME < $TABLE.time}<img src="{$RIPF}_gl_inaktiv.gif" alt="Offline" align="absmiddle" />{else}<img src="{$RIPF}_online.gif" alt="Offline" align="absmiddle" />{/if}</td>
	<td align="center">{$TABLE.preis|number_format}</td>
	<td align="center"><input type="button" value="zurücknehmen" id="{$TABLE.type}_submit_{$TABLE.id}" onclick="javascript: zurueck({$TABLE.id}, '{$TABLE.type}')" /></td>
</tr>
{elseif $SHOW_TABLE == 'assi'}
<tr class="tableInner1" height="30px" type="assi">
	<td align="left">&nbsp;&nbsp;&nbsp;{$TABLE.name} (#{$TABLE.rid})</td>
	<td align="center">{$TABLE.number|number_format}</td>
	<td align="center">{$TABLE.preis|number_format}</td>
	<td align="center"><input type="button" value="zurücknehmen" id="{$TABLE.type}_submit_{$TABLE.id}" onclick="javascript: zurueck({$TABLE.id}, '{$TABLE.type}')" /></td>
</tr>
{elseif $UPDATE}
{assign var="ERROR" value="Momentan läuft das stündliche Update. Die Börse ist während des stündlichen Updates nicht verfügbar. Probieren Sie es bitte später noch einmal."}
{include file="fehler.tpl"}
{else}
		<!-- ****** DIVIDENDE - START ****** -->
		<a name="stat"></a>
		<table width="610px" cellspacing="1" cellpadding="2" align="center">
			<tr>
				<td width="110px" class="tableHead" align="center">Statistik</td>
				<td width="110px" class="tableInner1" align="center"><a href="#assi" class="linkAuftableInner">&gt; Kaufen</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#overview" class="linkAuftableInner">&gt; Übersicht</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#offer" class="linkAuftableInner">&gt; Verkaufen</a></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5" height="1" class="tableOutline"></td>
			</tr>
		</table>
		<br />
		<div align="center">
			Sie besitzen <strong>{$GESAMT_AKTIEN|number_format}</strong> Aktien im Wert von <b>{$GUTHABEN|number_format}</b> Credits.<br />
			Ihre letzte Dividendenausschüttung betrug:<br />
			<b>{$DIVI.credits|number_format}</b> Credits, <b>{$DIVI.metal|number_format}</b> t Erz, <b>{$DIVI.energy|number_format}</b> MWh Energie und <b>{$DIVI.sciencepoints|number_format}</b> Forschungspunkte.
			<br /><br />
			<div style="width:500px; text-align:left; margin:0 auto; border:1px solid #000; padding:1px; position:relative; height:16px;">
				<div style="position:absolute; top:1px; z-index:1; width:100%;">
					<img src="{$RIPF}dotpixel_blau.gif" height="15px" width="{$PROZENT}%">
				</div>
                <div style="position:absolute; top:1px; margin:0 auto; z-index:2; width:100%; text-align:center;">
					{$PROZENT}%
				</div>
			</div> 
			{if $REST > 0}Sie können noch <strong>{$REST|number_format}</strong> Aktien kaufen{else}Sie haben die maximale Aktienanzahl erreicht.{/if}
		</div>
		<!-- ****** DIVIDENDE - ENDE ****** -->
		<br />
		<br />
		<!-- ****** AKTIEN KAUFEN - START ****** -->
		<a name="assi"></a>
		<table width="610px" cellspacing="1" cellpadding="2" align="center">
			<tr>
				<td width="110px" class="tableInner1" align="center"><a href="#stat" class="linkAuftableInner">&gt; Statistik</a></td>
				<td width="110px" class="tableHead" align="center">Kaufen</td>
				<td width="110px" class="tableInner1" align="center"><a href="#overview" class="linkAuftableInner">&gt; Übersicht</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#offer" class="linkAuftableInner">&gt; Verkaufen</a></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5" height="1" class="tableOutline"></td>
			</tr>
		</table>
		<br />
		<table cellspacing="1px" cellpadding="0px" width="610px" border="0" class="tableOutline" align="center">
			<tr onClick="javascript: show_hide('assi');" class="tableHead" style="cursor:pointer;">
				<td colspan="5" align="center" id="assi_head_css"><p style="margin:6 6 6 6; height:18px" id="assi_head"><img src="{$RIPF}_for_down.gif" id="assi_img" alt="Show/ Hide" align="absmiddle" style="height:16px;" />&nbsp;&nbsp;&nbsp;Kaufangebot abgeben&nbsp;&nbsp;&nbsp;<img src="{$RIPF}_for_down.gif" id="assi_img2" alt="Show/ Hide" align="absmiddle" style="height:16px;" /></p></td>
			</tr>
			<tr id="assi_tr" style="display:none;">
				<td colspan="5">
					<div id="assi_form" style="display:none;">
						<table width="100%" border="0px" cellpadding="0px" cellspacing="0px" height="40px">
							<form action="boerse.php" method="post" style="padding:0px; margin:0px;" id="assi_form_">
								<input type="hidden" name="action" value="assi" />
								<tr class="tableHead2">
									<td align="center">Syndikat: 
										<select name="rid" id="rid_assi" onchange="changeBuyPrice()">
										{foreach from=$SYNDATA item=SYN}
											<option value="{$SYN.rid}">{$SYN.rid}</option>
										{/foreach}
										</select>
									</td>
									<td align="center">Menge: <input type="text" name="number" size="10" id="number_assi" /></td>
									<td align="center">Höchstpreis: <input type="text" name="preis" size="10" id="price_assi" /></td>
									<td align="center"><input id="assi_submit" type="submit" value="Gebot abgeben" /></td>
								</tr>
							</form>
						</table>
					</div>
				</td>
			</tr>
			<tr class="tableHead2" height="30px" id="assi_table">
				<td align="left" width="280px">&nbsp;&nbsp;&nbsp;Syndikat (#)</td>
				<td align="center" width="80px">Menge</td>
				<td align="center" width="60px">Preis</td>
				<td align="center" width="130px">ändern</td>
			</tr>
			{if $ASSIDATA}
			{foreach from=$ASSIDATA item=ASSI}
			<form action="?" method="post">
				<input type="hidden" name="assi_id" value="{$ASSI.assi_id}" />
				<input type="hidden" name="action" value="assi_back" />
				<tr class="tableInner1" id="assi_{$ASSI.assi_id}" height="30px" type="assi">
					<td align="left">&nbsp;&nbsp;&nbsp;{$ASSI.name} (#{$ASSI.rid})</td>
					<td align="center">{$ASSI.number|number_format}</td>
					<td align="center">{$ASSI.preis|number_format}</td>
					<td align="center"><input type="button" value="zurücknehmen" id="assi_submit_{$ASSI.assi_id}" onclick="javascript: zurueck({$ASSI.assi_id}, 'assi')" /></td>
				</tr>
			</form>
			{/foreach}
			{if $ASSIDATA_COUNT > 1}
				<tr class="tableInner1" id="assi_back_all" >
					<td colspan="3" align="center"></td>
					<td width="150" align="center">
						<form style="border:none;margin: 0px" action="?" method="post">
							<input class="button" type="button" value="Alle zurücknehmen" id="assi_submit_all" onclick="javascript: zurueck_all('assi')" />
						</form>
					</td>
				</tr>
			{/if}
			{else}
			<tr class="tableInner1">
				<td align="center" colspan="5" height="25px">Momentan keine Kaufgebote</td>
			</tr>
			{/if}
		</table>
		<!-- ****** AKTIEN KAUFEN - ENDE ****** -->
		<br />
		<br />
		<br />
		<!-- ****** ÜBERSICHT - START ****** -->
		<a name="overview"></a>
		<table width="610px" cellspacing="1" cellpadding="2" align="center">
			<tr>
				<td width="110px" class="tableInner1" align="center"><a href="#stat" class="linkAuftableInner">&gt; Statistik</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#assi" class="linkAuftableInner">&gt; Kaufen</a></td>
				<td width="110px" class="tableHead" align="center">Übersicht</td>
				<td width="110px" class="tableInner1" align="center"><a href="#offer" class="linkAuftableInner">&gt; Verkaufen</a></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5" height="1" class="tableOutline"></td>
			</tr>
		</table>
		<br />
		<table cellspacing="1px" cellpadding="0px" width="610px" border="0px" class="tableOutline" align="center">
			<tr>
				<td colspan="6" class="tableHead" align="center" ><p style="margin:6 6 6 6;">Aktienübersicht</p></td>
			</tr>
			<tr class="tableHead2" height="30px">
				<td align="center">Syndikat</td>
				<td align="center" colspan="2">Sie besitzen</td>
				<td align="center">Angebot</td>
				<td align="center">Preis</td>
				<td align="center">Direktkauf</td>
			</tr>
			{foreach from=$SYNDATA item=SYN}<!-- ## SYNDIKAT #{$SYN.rid} ## -->
			<form action="boerse.php" method="post">
				<input type="hidden" name="action" value="buy" />
				<input type="hidden" name="rid" value="{$SYN.rid}" />
				<input type="hidden" name="preis" value="{$SYN.preis}" />
				<tr class="tableInner1" height="30px" onMouseOver="javascript: this.className = 'tableInner2';" onMouseOut="javascript: this.className = 'tableInner1';">
					<td align="left" width="290px" id="{$SYN.rid}_box_row" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer">&nbsp;[<font id="plus_{$SYN.rid}">+</font>] {$SYN.name} (#{$SYN.rid})</td>
					<td align="right" width="60px" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer">{$SYN.besitz|number_format}&nbsp;&nbsp;&nbsp;</td>
					<td align="right" width="50px" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer"><em>{$SYN.besitz_prozent|number_format:1} %</em>&nbsp;&nbsp;&nbsp;</td>
					{if $SYN.gebot}
					<td align="right" width="70px" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer">{$SYN.gebot|number_format}&nbsp;&nbsp;&nbsp;</td>
					<td align="right" width="60px" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer">{$SYN.preis|number_format}&nbsp;&nbsp;&nbsp;</td>
					<td align="right" width="80px">&nbsp;&nbsp;&nbsp;<input type="text" name="number" size="5" id="number_{$SYN.rid}" onfocus="javascript: show_hide_box({$SYN.rid}, 'in')" onblur="javascript: show_hide_box({$SYN.rid}, 'out')" />&nbsp;&nbsp;&nbsp;</td>
					{else}
					<td align="center" width="210px" colspan="3" onclick="javascript: show_hide_data({$SYN.rid});" style="cursor:pointer">keine Angebote (Kurs: {$SYN.aktienkurs|number_format} Cr)</td>
					{/if}
				</tr>
				<tr class="tableInner1" id="{$SYN.rid}_box_tr" style="display:none;">
					<td colspan="5">
						<div id="{$SYN.rid}_box_div" style="display:none;">
							<table width="100%" border="0px" cellpadding="0px" cellspacing="0px" height="30px">
								<tr class="tableInner2">
									<td width="110px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Maximal kaufbar:</td>
									<td width="80px" align="right">{$SYN.kaufbar|number_format}&nbsp;&nbsp;&nbsp;</td>
									<td align="right" width="140px"><img src="{$RIPF}_for_up.gif" title="Maximale Anzahl kaufen" style="cursor:pointer;" align="absmiddle" onclick="javascript: change_max('{$SYN.kaufbar}', 'number_{$SYN.rid}');" />&nbsp;&nbsp;<input type="submit" value="kaufen" />&nbsp;&nbsp;&nbsp;</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				<tr id="{$SYN.rid}_data_tr" style="display:none;">
					<td colspan="6">
						<div id="{$SYN.rid}_data_div" style="display:none;">
							<table width="100%" border="0px" cellpadding="0px" class="tableInner2">
								<tr>
									<td width="50%" valign="top">
									<div style="position:relative;">
									{if $IS_OSTERN && ($SYN.rid == 1 || $SYN.rid == 9) && $OSTER_BONI.18439}
										<div style="position:absolute; z-index:2; top:0px; left:100;">
											<a class="normal" href="bonus.php?type=4&amp;egg=18439">
												<img src="images/ostern_18439.png"></a>
										</div>
									{/if}
										<table class="tableInner2" height="100%" width="100%">
											<tr>
												<td width="70%">&nbsp;&nbsp;&nbsp;<img title="Kurs-Verlauf" src="{$RIPF}_aktien_halter.gif" onClick="window.open('croniwidget.php?type=syn_stock&title={$SYN.cronimon_name}&identifier={$SYN.rid}', 'Kurs_Verlauf_Syndikat_{$SYN.rid}', 'width=520 , height=390 ,scrollbars=no')" style="cursor:pointer; height:16px;" align="absmiddle" border="0" /> Aktienkurs:</td>
												<td width="20%" align="right">&nbsp;&nbsp;&nbsp;<strong>{$SYN.aktienkurs|number_format}</strong></td>
												<td width="10%"></td>
											</tr>
											<tr>
												<td colspan="3"><hr /></td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;Aktien im Umlauf:</td>
												<td align="right">&nbsp;&nbsp;&nbsp;<strong>{$SYN.umlauf|number_format}</strong></td>
												<td> Stk.</td>
											</tr>
											<tr>
												<td colspan="3"><hr /></td>
											</tr>
											{foreach from=$SYN.inhaber item=INHABER}<tr>
												<td>&nbsp;&nbsp;&nbsp;{$INHABER.name} (<a href="syndicate.php?rid={$INHABER.rid}" class="linkAuftableInner">#{$INHABER.rid}</a>)</td>
												<td align="right">&nbsp;&nbsp;&nbsp;{$INHABER.num|number_format}</td>
												<td align="right">({$INHABER.prozent|number_format:1}%)</td>
											</tr>
											{/foreach}<tr height="3px">
												<td colspan="3"> </td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;Aktien in Spielerbesitz &lt;1%:</td>
												<td align="right">&nbsp;&nbsp;&nbsp;{$SYN.freefloat|number_format}</td>
												<td align="right">({$SYN.freefloat_prozent|number_format:1}%)</td>
											</tr>
											<tr height="3px">
												<td colspan="3"> </td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;Aktien im Besitz des Maklers:</td>
												<td align="right">&nbsp;&nbsp;&nbsp;{$SYN.makler|number_format}</td>
												<td align="right">({$SYN.makler_prozent|number_format:1}%)</td>
											</tr>
										</table>
										</div>
									</td>
									<td width="50%" valign="top">
										<table class="tableInner2" height="100%" width="100%">
											<tr height="18px">
												<td colspan="2">&nbsp;&nbsp;&nbsp;Aktuelle Angebotsspanne: <strong>{$SYN.min|number_format}</strong> bis <strong>{$SYN.max|number_format}</strong> Credits</td>
											</tr>
											<tr>
												<td colspan="2"><hr /></td>
											</tr>
											<tr>
												<td width="70%">&nbsp;&nbsp;&nbsp;Durchschnittsdividende ({$NUM_LAST_DIVIS} Divis):</td>
												<td width="30%" align="right"><strong>{$SYN.d_divi|number_format}</strong> Cr&nbsp;&nbsp;&nbsp;</td>
											</tr>
											<tr>
												<td colspan="2"><hr /></td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;Letzter eigener Aktienkauf:</td>
												<td align="right"><strong>{if $SYN.last_buy_status == 'show'}{$SYN.last_buy_h}:{$SYN.last_buy_min} Uhr{elseif $SYN.last_buy_status == 'late'}vor über {$SELL_BLOCK}h{else}-{/if}</strong>&nbsp;&nbsp;&nbsp;</td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;Aktiendepot für Verkäufe gesperrt bis:</td>
												<td align="right"><strong>{if $SYN.last_buy_status == 'show'}{$SYN.block_h}:{$SYN.block_min} Uhr{else}-{/if}</strong>&nbsp;&nbsp;&nbsp;</td>
											</tr>
											<tr>
												<td colspan="2"><hr /></td>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;bisher investiert:</td>
												<td align="right"><strong>{$SYN.invested|number_format}</strong> Cr&nbsp;&nbsp;&nbsp;</td>
											</tr>
											</tr>
											<tr>
												<td>&nbsp;&nbsp;&nbsp;im Schnitt pro Aktie ausgegeben:</td>
												<td align="right"><strong>{$SYN.invested_once|number_format}</strong> Cr&nbsp;&nbsp;&nbsp;</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</form>
			{/foreach}
		</table>
		<!-- ****** ÜBERSICHT - ENDE ****** -->
		<br />
		<br />
		<br />
		<!-- ****** AKTIEN VERKAUFEN - START ****** -->
		<a name="offer"></a>
		<table width="610px" cellspacing="1" cellpadding="2" align="center">
			<tr>
				<td width="110px" class="tableInner1" align="center"><a href="#stat" class="linkAuftableInner">&gt; Statistik</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#assi" class="linkAuftableInner">&gt; Kaufen</a></td>
				<td width="110px" class="tableInner1" align="center"><a href="#overview" class="linkAuftableInner">&gt; Übersicht</a></td>
				<td width="110px" class="tableHead" align="center">Verkaufen</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5" height="1" class="tableOutline"></td>
			</tr>
		</table>
		<br />
		<table cellspacing="1px" cellpadding="0px" width="610px" border="0" class="tableOutline" align="center">
			<tr onClick="javascript: show_hide('offer');" class="tableHead" style="cursor:pointer;">
				<td colspan="5" align="center" id="offer_head_css"><p style="margin:6 6 6 6; height:18px" id="offer_head">{if $OWNDATA}<img src="{$RIPF}_for_down.gif" id="offer_img" alt="Show/ Hide" align="absmiddle" style="height:16px;" />&nbsp;&nbsp;&nbsp;{/if}Aktien verkaufen{if $OWNDATA}&nbsp;&nbsp;&nbsp;<img src="{$RIPF}_for_down.gif" id="offer_img2" alt="Show/ Hide" align="absmiddle" style="height:16px;" />{/if}</p></td>
			</tr>
			{if $OWNDATA}
			<tr id="offer_tr" style="display:none;">
				<td colspan="5">
					<div id="offer_form" style="display:none;">
						<table width="100%" border="0px" cellpadding="0px" cellspacing="0px" height="40px">
							<form action="boerse.php" method="post" style="padding:0px; margin:0px;" id="offer_form_">
								<input type="hidden" name="action" value="offer" />
								<tr class="tableHead2">
									<td align="center">Syndikat: 
										<select name="rid" id="rid_offer" onchange="changeSellPrice()">
										{foreach from=$OWNDATA item=SYN}
											<option value="{$SYN.rid}">{$SYN.rid}</option>
										{/foreach}
										</select>
									</td>
									<td align="center">Menge: <input type="text" name="number" size="10" id="number_offer" /> <img src="{$RIPF}_for_up.gif" onClick="javascript: change_max(false, false);" alt="Max" align="absmiddle" style="cursor:pointer;" /></td>
									<td align="center">Preis: <input type="text" name="preis" size="10" id="price_offer" /></td>
									<td align="center"><input id="offer_submit" type="submit" value="Aktien anbieten" /></td>
								</tr>
							</form>
						</table>
					</div>
				</td>
			</tr>
			<tr class="tableHead2" height="30px" id="offer_table">
				<td align="left" width="280px">&nbsp;&nbsp;&nbsp;Syndikat (#)</td>
				<td align="center" width="80px">Menge</td>
				<td align="center" width="60px">Status</td>
				<td align="center" width="60px">Preis</td>
				<td align="center" width="130px">ändern</td>
			</tr>
			{if $OFFERDATA}
			{foreach from=$OFFERDATA item=OFFER}
			<form action="?" method="post">
				<input type="hidden" name="offer_id" value="{$OFFER.offer_id}" />
				<input type="hidden" name="action" value="offer_back" />
				<tr class="tableInner1" id="offer_{$OFFER.offer_id}" height="30px" type="offer">
					<td align="left">&nbsp;&nbsp;&nbsp;{$OFFER.name} (#{$OFFER.rid})</td>
					<td align="center">{$OFFER.number|number_format}</td>
					<td align="center">{if $TIME < $OFFER.time}<img src="{$RIPF}_gl_inaktiv.gif" alt="Offline" align="absmiddle" />{else}<img src="{$RIPF}_online.gif" alt="Offline" align="absmiddle" />{/if}</td>
					<td align="center">{$OFFER.preis|number_format}</td>
					<td align="center"><input type="button" value="zurücknehmen" id="offer_submit_{$OFFER.offer_id}" onclick="javascript: zurueck({$OFFER.offer_id}, 'offer')" /></td>
				</tr>
			</form>
			{/foreach}
			{if $OFFERDATA_COUNT > 1}
				<tr class="tableInner1" id="offer_back_all" >
					<td colspan="4" align="center"></td>
					<td width="150" align="center">
						<form style="border:none;margin: 0px" action="?" method="post">
							<input class="button" type="button" value="Alle zurücknehmen" id="offer_submit_all" onclick="javascript: zurueck_all('offer')" />
						</form>
					</td>
				</tr>
			{/if}
			{else}
			<tr class="tableInner1">
				<td align="center" colspan="5" height="25px">Momentan keine Angebote</td>
			</tr>
			{/if}
			{else}
			<tr class="tableHead2">
				<td align="center" colspan="5" height="25px">Keine Aktien vorhanden</td>
			</tr>
			{/if}
		</table>
		<!-- ****** AKTIEN VERKAUFEN - ENDE ****** -->
		{include file="js/boerse.js.tpl"}
{/if}