{if $SHOWMARKET}
	{if $SHOWVOTETEXT}
<br>
<table align="center" width="550">
	<tr>
		<td>
			<table width="550" align="center" class="tableOutline" cellpadding="0" cellspacing="1">
				<tr>
					<td align="center">
						<table cellpadding="2" cellspacing="0">
							<tr>
								<td class="{$CLASSES}">
									{$BROWSERGAME_LINK}
								</td>
								<td class={$CLASSES}>
									{$BROWERSGAME_VOTE_TEXT}
								</td>
								<td class={$CLASSES}>
									{$BROWSERGAME_LINK}
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br>
		</td>
	</tr>
</table>
	{/if}
{literal}
<script language="Javascript">
	<!--
		
	{/literal}
		var maxbuyable = Array();
		maxbuyable['energy'] = 6000000;
		maxbuyable['metal'] = 800000;
		maxbuyable['sciencepoints'] = 250000;
	{foreach from=$MAXBUYABLE item=VL}
		maxbuyable['{$VL.key}'] = {$VL.value}; 
	{/foreach}
		var currentprice = Array();
	{foreach from=$CURRENTPRICE item=VL}
		currentprice['{$VL.key}'] = {$VL.value};
	{/foreach}
		var currentmin = Array();
		var currentmax = Array();
	{foreach from=$PRICES item=VL}
		{foreach from=$VL item=VL2}
		currentmin['{$VL2.key}'] = Math.floor({$VL2.minpreis});
		currentmax['{$VL2.key}'] = Math.ceil({$VL2.maxpreis});
		{/foreach}
	{/foreach}
	{literal}
		
		function maxbuy(tform,max) {
			document.forms[tform].elements["anzahl"].value = max;
		}
				
		function maxsell() {
			var product = document.getElementById('selected_product').value;
			var type_sell = document.getElementById('input_type2').checked;
			if (type_sell) {
				document.getElementById('sell_number').value = document.getElementById(product).value;
			}
			else {
				var type_buy = document.getElementById('input_type1').checked;
				if (type_buy) {
					document.getElementById('sell_number').value = maxbuyable[product];
				} else {
					//alert("Du musst erst auswählen ob es ein Kauf oder Verkaufsangebot sein soll.");
					check_type('max');
				}
			} 
		}

		function stdprice() {
			var product = document.getElementById('selected_product').value;
			if (currentprice[product] > 0) temp = currentprice[product] - 1;
			else temp = currentprice[product]; 
			if (temp < currentmin[product]) temp = currentmin[product];
			if (currentmax[product] < temp) temp = currentmax[product];
			document.getElementById('sell_price').value = temp;
			document.getElementById('sell_price').focus();
			document.getElementById('sell_price').select();
		}
		
		function check_type(option) {
			var rad_buy = document.getElementById('input_type1');
			var rad_sell = document.getElementById('input_type2');
			var dialog_text = '<div style="background: white; padding: 8px; font-family: Arial; font-size: 14px;">Du musst noch die Aktion wählen:</div>';
			
			if (rad_buy.checked || rad_sell.checked) {
				if (option == 'submit') document.getElementById('form_gebote').submit();
				$(this).closest('.ui-dialog-content').dialog('close');
			} else {
				$(dialog_text).dialog({
					resizable: false,
					modal: true,
					height: 72,
					width: 250,
					open: function(event, ui) {
						$(".ui-dialog-titlebar-close", this.parentNode).hide();
						$(".ui-dialog-buttonpane", this.parentNode).css({"background": "white", "padding": "8px"});
					},
					buttons: {
						"Kaufen": function() {
							rad_buy.checked = true;
							if (option == 'submit') document.getElementById('form_gebote').submit();
							else if (option == 'max') maxsell();
							$(this).closest('.ui-dialog-content').dialog('close');
						},
						"Verkaufen": function() {
							rad_sell.checked = true;
							if (option == 'submit') document.getElementById('form_gebote').submit();
							else if (option == 'max') maxsell();
							$(this).closest('.ui-dialog-content').dialog('close');
						}
					}
				})
			}
		}
				
	-->
</script>
{/literal}
<br>
Kaufen und Verkaufen Sie Produkte auf dem Weltmarkt. Angebot und Nachfrage regeln die Preise.
Bitte beachten Sie unbedingt die Erklärung zur Funktion und den Beschränkungen des Global Market.<br>
<br>
<a name="kaufen"></a>
<table width="610" cellspacing="1" cellpadding="2">
	<tr>
		<td width="80" class="tableHead" align="center">
			Kaufen
		</td>
		<td width="220" class="tableInner1" align="center">
			<a href="#Preise" class="linkAuftableInner">
				&gt; Mindest & Maximalpreise
			</a>
		</td>
		<td width="130" class="tableInner1" align="center">
			<a href="#meineangebote" class="linkAuftableInner">
				&gt; Eigene Angebote
			</a>
		</td>
		<td width="290">&nbsp;
			
		</td>
	</tr>
	<tr>
		<td colspan="4" height="1" class="tableOutline"></td>
	</tr>
</table>
<br>
Erwerben Sie Ressourcen zu den momentan niedrigsten Weltmarktpreisen.
<br>&nbsp;
<br>
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<form id="form_gebote" style="margin:0px" action="market.php">
				<table width="610" cellspacing="1" cellpadding="2">
					<tr>
						<td  colspan="4" class="tableHead" align="center">
							{if !($OWN_GEBOTE_COUNT < $MAXANZAHL_GEBOTE)}{assign var="KAUF_TEMP" value="disabled"}{/if}
							<input type="radio" name="input" value="gebot" {$KAUF_TEMP} id="input_type1">
							<label for="input_type1">Einkaufen&nbsp;&nbsp;</label>
							<a href="javascript:info('market_gebote','0')" class="highlightAuftableInner">
								<img src="{$RIPF}_help.gif" border="0" valign="absmiddle">
							</a>
							{if !($OWNOFFERS < $MAXCOUNT)}{assign var="VERKAUF_TEMP" value="disabled"}{/if}
							<input type="radio" name="input" value="bringin" {$VERKAUF_TEMP} id="input_type2">
							<label for="input_type2">Verkaufen</label>
						</td>
					</tr>
				{if $OWN_GEBOTE_COUNT < $MAXANZAHL_GEBOTE || $OWNOFFERS < $MAXCOUNT}
					<tr class="tableHead2">
						<td align="center">
							Produkt
						</td>
						<td align="center">
							Anzahl						
						</td>
						<td align="center">
							Preis
						</td>
						<td align="center">
							Aktion
						</td>
					</tr>
					<tr class="tableInner1">
						<td align="center">
							<select name="product" id="selected_product" onchange="stdprice()">
								<option value="energy">Energie</option>
								<option value="metal">Erz</option>
								<option value="sciencepoints">Forschungspunkte</option>
							{foreach from=$UNITS item=VL}
								<option value="{$VL.o_key}">{$VL.o_unitName}</option>
							{/foreach}
							</select>
						</td>
						<td align="center">
							<input name="number" size="8" id="sell_number">
							&nbsp;&nbsp;
							<img align="absmiddle" width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxsell();">
							{foreach from=$KONTO item=VL}
								<input type="hidden" value="{$VL.o_status}" id="{$VL.o_key}">
							{/foreach}
						</td>
						<td align="center">
							<input name="price" size="8" id="sell_price" value="{$ASSI_STD_PRICE}">
						</td>
						<td align="center">
							<input type="button" onClick="check_type('submit')" value="Gebot abgeben">
						</td>
					</tr>
				{/if}
				{if !($OWN_GEBOTE_COUNT < $MAXANZAHL_GEBOTE && $OWNOFFERS < $MAXCOUNT)}
					<tr class="tableInner1">
						<td align="center" colspan="4">
							<b>
							{if !($OWN_GEBOTE_COUNT < $MAXANZAHL_GEBOTE)}
								Sie können nicht mehr als {$MAXANZAHL_GEBOTE} Kaufgebote abgeben.
							{/if}
							<br>
							{if !($OWNOFFERS < $MAXCOUNT)}
								Jeder Benutzer kann maximal {$MAXCOUNT} Angebote 
								gleichzeitig auf dem Global Market einstellen.
							{/if}
							</b>
						</td>
					</tr>
				{/if}
				</table>
			</form>
		</td>
	</tr>
</table>
&nbsp;
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
		    <table width="610" cellspacing="1" cellpadding="2">
				<tr>
					<td class="tableHead" align="center" colspan="7">
						<p style="margin:6 6 6 6;">Rohstoffe erwerben</p>
					</td>
				</tr>
		    	<tr>
		    		<td width="160px" align="center" class="tableHead2" colspan="2">
		    			<b>Typ</b>
		    		</td>
		    		<td width="100px" align="center" class="tableHead2">
		    			<b>Anzahl</b>
		    		</td>
		    		<td width="95px"  align="center" class="tableHead2">
		    			<b>Cr / 10 Einheiten</b>
		    		</td>
		    		<td width="100px" align="center" class="tableHead2">
		    			kaufbar
		    		</td>
		    		<td width="95px"  align="center" class="tableHead2">
		    			<b>Anzahl</b>
		    		</td>
		    		<td width="60px"  align="center" class="tableHead2">
		    			<b>kaufen</b>
		    		</td>
		    	</tr>
		{foreach from=$RESSTATS_MARKT item=VL}
			{if $VL.o_resoffers}
				<tr height="25">
					<form name="{$VL.o_resName}" action="market.php" method="post">
						<input type="hidden" value="buy" name="input">
						<td class="tableInner1">
							&nbsp;{$VL.o_resName}
						</td>
						<td width="18px" class="tableInner1">
							{$VL.o_JsHelpTagCustom}
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_resoffersNumber}
						</td>
						<td align=center class="tableInner1">
							{$VL.o_resoffersPrice_pointit}
						</td>
						<td align=center class="tableInner1">
							{$VL.o_resbuyable_pointit}
						</td>
						<td align=center class="tableInner1" nowrap>
							<input class="input" name="anzahl" size="7">
							&nbsp;
							<img width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxbuy('{$VL.o_resName}',{$VL.o_resbuyable});" align="absmiddle" border="none">
							<input type="hidden" name="buyprice" value="{$VL.o_resoffersPrice}">
							<input type="hidden" name="buyproduct" value="{$VL.o_key}">
						</td>
						<td align=center class="tableInner1">
							<input class="button" type="submit" value="Kaufen">
						</td>
					</form>
				</tr>
			{else} 
				<tr height="25">
					<td class="tableInner1">
						&nbsp;{$VL.o_resName}
					</td>
					<td class="tableInner1">
						{$VL.o_JsHelpTagCustom}
					</td>
					<td class="tableInner1" colspan="5" align="center">
						keine Angebote vorhanden
					</td>
				</tr>
			{/if}
		{/foreach}
			</table>
		</td>
	</tr>
</table>
&nbsp;
<!-- Spionageeinheiten -->
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table width="610" cellspacing="1" cellpadding="2">
				<tr>
					<td class="tableHead" align="center" colspan="7">
						<p style="margin:6 6 6 6;">
							Spione erwerben
						</p>
					</td>
				</tr>
				<tr>
					<td width="145px" align="center" class="tableHead2" colspan="2">
						<b>Typ</b>
					</td>
					<td width="80px" align="center" class="tableHead2">
						<b>Anzahl</b>
					</td>
					<td width="150px" align="center" class="tableHead2">
						<b>Cr / Einheit (Wert / %)</b>
					</td>
					<td width="80px" align="center" class="tableHead2">
						kaufbar
					</td>
					<td width="95px" align="center" class="tableHead2">
						<b>Anzahl</b>
					</td>
					<td width="60px" align="center" class="tableHead2">
						<b>kaufen</b>
					</td>
		    	</tr>
			{foreach from=$SPYSTATS_MARKT item=VL}
				{if $VL.o_spyoffers}
				<tr height="25">
					<form name="{$VL.o_spyName}" action="market.php" method="post">
						<input type="hidden" value="buy" name="input">
						<td class="tableInner1">
							&nbsp;{$VL.o_spyName}
						</td>
						<td width="18px" class="tableInner1">
							{$VL.o_JsHelpTagCustom}
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_spyoffersNumber}
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_spyoffersPrice_pointit} ({$VL.o_unitwert} / {$VL.o_spypercent}%)
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_spybuyable_pointit}
						</td>
						<td align="center" class="tableInner1">
							<input class="input" name="anzahl" maxlenth="8" size="7">
							&nbsp;<img width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxbuy('{$VL.o_spyName}', {$VL.o_spybuyable});"  align="absmiddle" border="none">
							<input type="hidden" name="buyproduct" value="{$VL.o_key}">
							<input type="hidden" name="buyprice" value="{$VL.o_spyoffersPrice}">
						</td>
						<td align="center" class="tableInner1">
							<input class="button" type="submit" value="Kaufen">
						</td>
					</form>
				</tr>
				{else}
				<tr height="25">
					<td class="tableInner1">
						&nbsp;{$VL.o_spyName}
					</td>
					<td class="tableInner1">
						{$VL.o_JsHelpTagCustom}
					</td>
					<td class="tableInner1" colspan="5" align="center">
						keine Angebote vorhanden (Aktueller Wert: {$VL.o_unitwert})
					</td>
				</tr>
		        {/if}
		    {/foreach}
			</table>
		</td>
	</tr>
</table>
<!-- Militäreinheiten -->
&nbsp;
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table width="610" cellspacing="1" cellpadding="2">
				<tr>
					<td class="tableHead" align="center" colspan="7">
						<p style="margin:6 6 6 6;">Militär erwerben</p>
					</td>
				</tr>
				<tr>
					<td width="145px" align="center" class="tableHead2" colspan="2">
						<b>Typ</b>
					</td>
					<td width="80px"  align="center" class="tableHead2">
						<b>Anzahl</b>
					</td>
					<td width="150px" align="center" class="tableHead2">
						<b>Cr / Einheit (Wert / %)</b>
					</td>
					<td width="70px"  align="center" class="tableHead2">
						kaufbar
					</td>
					<td width="105px"  align="center" class="tableHead2">
						<b>Anzahl</b>
					</td>
					<td width="60px"  align="center" class="tableHead2">
						<b>kaufen</b>
					</td>
				</tr>
			{foreach from=$UNITSTATS_MARKT item=VL}
				{if $VL.o_miloffers}
				<tr height="25">
					<form name="{$VL.o_unitName}" action="market.php" method="post">
						<input type="hidden" value="buy" name="input">
						<td class="tableInner1">
							&nbsp;{$VL.o_unitName}
						</td>
						<td width="18px" class="tableInner1">
							{$VL.o_JsHelpTagCustom}
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_miloffersNumber}
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_miloffersPrice_pointit} ({$VL.o_unitwert} / {$VL.o_milpercent}%)
						</td>
						<td align="center" class="tableInner1">
							{$VL.o_milbuyable_pointit}
						</td>
						<td align="center" class="tableInner1" id="{$VL.o_unitName}_number">
							<input class="input" name="anzahl" maxlenth="8" size="7">
							&nbsp;
							<img width="18" height="18" src="{$RIPF}_for_up.gif" onClick="maxbuy('{$VL.o_unitName}',{$VL.o_milbuyable});"  align="absmiddle" border="none">
							<div style="display:none;" id="{$VL.o_unitName}_maxbuy">{$VL.o_milbuyable}</div>
							<input type="hidden" name="buyproduct" value="{$VL.o_key}">
							<input type="hidden" name="buyprice" value="{$VL.o_miloffersPrice}">
						</td>
						<td align="center" class="tableInner1">
							<input class="button" type="submit" value="Kaufen" id="{$VL.o_unitName}_button">
						</td>
					</form>
				</tr>
				{else}
				<tr height="25">
					<td class="tableInner1">
						&nbsp;{$VL.o_unitName}
					</td>
					<td class="tableInner1">
						{$VL.o_JsHelpTagCustom}
					</td>
					<td class="tableInner1" colspan="5" align="center">
						keine Angebote vorhanden (Aktueller Wert: {$VL.o_unitwert})
					</td>
				</tr>
				{/if}
			{/foreach}
			</table>
		</td>
	</tr>
</table>
<!-- Verkaufen und eigene Angebote START -->
<br><br><br>
<a name="Preise"></a>
<table width="610" cellspacing="1" cellpadding="2">
	<tr>
		<td width="80"  class="tableInner1" align="center">
			<a href="#kaufen" class="linkAuftableInner">
				&gt; Kaufen
			</a>
		</td>
		<td width="220" class="tableHead"   align="center">
			<b>Mindest & Maximalpreise</b>
		</td>
		<td width="130" class="tableInner1" align="center">
			<a href="#meineangebote" class="linkAuftableInner">
				&gt; Eigene Angebote
			</a>
		</td>
		<td width="290">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="4" height="1" class="tableOutline"></td>
	</tr>
</table>
<!-- Früher war hier die Angebotausgabe -->
<br>
<table width="570" cellspacing="1" bgcolor="black" cellpadding="4">
	<tr>
		<td colspan="3" class="tableHead" align="center">
			Aktuelle Preise
		</td>
	</tr>
	<tr class="tableInner2">
		<td width="190">
			Ressourcen
		</td>
		<td width="190">
			Militäreinheiten
		</td>
		<td width="190">
			Spionageeinheiten
		</td>
	</tr>
	<tr class="tableInner1">
	{foreach from=$PRICES item=VL}
		<td valign="top">
			<table  cellpadding="4" cellspacing="1" class="tableInner1">
				<tr class="tableInner2">
					<td>
						Produkt
					</td>
					<td>
						Minpreis
					</td>
					<td>
						Maxpreis
					</td>
				</tr>
			{foreach from=$VL item=TEMP}
				<tr class="tableInner1">
					<td>
						{if $TEMP.name == "Forschungspunkte"}
							Forschungsp.
						{else}
							{$TEMP.name}
						{/if}
					</td>
					<td align="center">
						{$TEMP.o_minpreis}
					</td>
					<td align="center">
						{$TEMP.o_maxpreis}
					</td>
				</tr>
			{/foreach}
			</table>
		</td>
	{/foreach}
	</tr>
</table>
<center>
	<br>
	* Der Preis für Ressourcen gilt für je 10 Einheiten, um eine feinere Abstufung zu ermöglichen.<br>
	Der Preis für Spione/Militäreinheiten gilt für eine Einheit.
</center>
<br>
<br>
<a name="meineangebote"></a>
<table width="610" cellspacing="1" cellpadding="2">
	<tr>
		<td width="80" class="tableInner1" align="center">
			<a href="#kaufen" class="linkAuftableInner">
				&gt; Kaufen
			</a>
		</td>
		<td width="220" class="tableInner1" align="center">
			<a href="#Preise" class="linkAuftableInner">
				&gt; Mindest & Maximalpreise
			</a>
		</td>
		<td width="130" class="tableHead" align="center">
			<b>Eigene Angebote</b>
		</td>
		<td width="290">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="4" height="1" class="tableOutline"></td>
	</tr>
</table>
&nbsp;
<!-- Verkaufsassistent -->
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table width="610" cellspacing="1" cellpadding="2">
				<tr>
					<td colspan="6" class="tableHead" align="center">
						<p style="margin:6 6 6 6;">Ihre aktuellen Angebote</p>
					</td>
				</tr>
		        <tr>
					<td width="100" align="center" class="tableHead2"><b>Produkt</b></td>
					<td width="100" align="center" class="tableHead2"><b>Anzahl</b></td>
					<td width="100" align="center" class="tableHead2"><b>Preis</b></td>
					<td width="50"  align="center" class="tableHead2"><b>Status</b></td>
					<td width="105" align="center" class="tableHead2"></td>
					<td width="150" align="center" class="tableHead2"><b>ändern</b></td>
		        </tr>
		{if $OWNOFFERS > 0}
			{foreach from=$MYOFFERS item=VL}
				<tr>
					<form action="market.php" method="post">
		                <input type="hidden" value="delete" name="input">
		                <input type="hidden" value="{$VL.o_offerID}" name="offer_id">
						<td width="110" align="center" class="tableInner1">
							&nbsp;{$VL.o_name}
						</td>
						<td width="110" align="center" class="tableInner1">
							{$VL.o_number}
						</td>
						<td width="110" align="center" class="tableInner1">
							{$VL.o_price}
						</td>
						<td width="50"  align="center" class="tableInner1">
							<img src="{$RIPF}{$VL.o_image}" border:0px>
						</td>
						<td width="105" align="center" class="tableInner1">
							=>
						</td>
						<td width="170" align="center" class="tableInner1">
							<input class="button" type="submit" value="zurücknehmen">
						</td>
					</form>
				</tr>
			{/foreach}
			{if $MYOFFERS_COUNT > 1}
				<tr>
					<td colspan="5" align="center" class="tableHead2"></td>
					<td width="150" align="center" class="tableHead2">
						<form style="border:none;margin: 0px" action="market.php" method="post">
							<input type="hidden" name="input" value="deleteall">
							<input class="button" type="submit" value="Alle zurücknehmen">
						</form>
					</td>
				</tr>
			{/if}
		{else}
				<tr height="25">
					<td width="610" class="tableInner1" align="center" colspan="6">
						Sie haben keine eigenen Angebote auf dem Global Market.
					</td>
				</tr>
		{/if}
			</table>
		</td>
	</tr>
</table>
<!-- Kaufsassistent -->
{if $OWN_GEBOTE_COUNT > 0}
<br>
<table width="610" class="tableOutline" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table width="610" cellspacing="1" cellpadding="2">
				<tr>
					<td colspan="6" class="tableHead" align="center">
						<p style="margin:6 6 6 6;">Ihre aktuellen Kaufgebote</p>
					</td>
				</tr>
				<tr>
					<td width="100" align="center" class="tableHead2"><b>Produkt</b></td>
					<td width="100" align="center" class="tableHead2"><b>Anzahl</b></td>
					<td width="100" align="center" class="tableHead2"><b>Preis</b></td>
					<td width="105" align="center" class="tableHead2"></td>
					<td width="150" align="center" class="tableHead2"><b>ändern</b></td>
				</tr>
			{foreach from=$OWN_GEBOTE item=TEMP}
				<tr class="tableInner1">
					<form action="market.php" method="post" style="margin:0px">
						<input type="hidden" name="input" value="gebot_back">
						<input type="hidden" name="gebot_id" value="{$TEMP.gebot_id}">
						<td align="center">
							{$TEMP.o_name}
						</td>
						<td align="center">
							{$TEMP.o_number}
						</td>
						<td align="center">
							{$TEMP.o_price}
						</td>
						<td align="center">
							=>
						</td>
						<td align="center">
							<input type="submit" value="zurücknehmen">
						</td>
					</form>
				</tr>
			{/foreach}
			{if $OWN_GEBOTE_COUNT > 1}
				<tr>
					<td colspan="4" align="center" class="tableHead2"></td>
					<td width="150" align="center" class="tableHead2">
						<form style="border:none;margin: 0px" action="market.php" method="post">
							<input type="hidden" name="input" value="gebot_back_all">
							<input class="button" type="submit" value="Alle zurücknehmen">
						</form>
					</td>
				</tr>
			{/if}
			</table>
		</td>
	</tr>
</table>
{/if}

{/if}

{literal}
<script language="Javascript">
	<!--		
	// 29.08.2013 Hafke
	$('#sell_price').keyup(function (e) {
		if (e.keyCode == 13) {
			check_type('submit');
		}
	});
	
	$('#sell_number').keyup(function (e) {
		if (e.keyCode == 13) {
			check_type('submit');
		}
	});
	-->
</script>
{/literal}
