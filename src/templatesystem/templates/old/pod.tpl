{include file="js/pod.js.tpl"}
{if $inneraction=="showactions"}
	{if $podactions}
		<br>Lagerzugriffe der letzten {$zeit} Stunden:<br>&nbsp;
		<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
			<tr>
				<td>
					<table cellspacing="1" cellpadding=4 border=0 width="600">
						<tr class="tableHead">
							<td width="200">Konzern</td>
							<td width="80">Produkt</td>
							<td width= "80" align="right">Anzahl</td>
							<td width="100">Aktion</td>
							<td width="140">Datum, Uhrzeit</td>
						</tr>
		
						{foreach from=$podactions item=action}
						
							<tr class="tableInner1">
								<td align="left">{$action[0]}</td>
								<td align="left">{$action[1]}</td>
								<td align="right">{$action[2]}</td>
								<td align="left">{$action[3]}</td>
								<td align="left">{$action[4]} Uhr</td>
							</tr>
							
							{if $action[5]}
							
								<tr class="tableInner1">
									<td align="left">{$action[5]}</td>
									<td align="left">{$action[6]}</td>
									<td align="right">{$action[7]}</td>
									<td align="left">{$action[3]}</td>
									<td align="left">{$action[4]} Uhr</td>
								</tr>
								
							{/if}
									
						{/foreach}
		
					</table>
				</td>
			</tr>
		</table>
		
	{/if}

{elseif $inneraction=="showpoints"}

	<br>Guthaben der einzelnen Konzerne:<br>&nbsp;
	<center><table cellspacing=0 cellpadding=0 border=0 class="tableOutline"><tr><td>
	<table cellspacing="1" cellpadding=4 border=0 width="250px">
	<tr class="tableHead">
		<td align="left" width="60%">Konzern</td>
		<td align="right" width="40%">{$currency}&nbsp;&nbsp;</td>
	</tr>
	
	{foreach from=$podvalues item=pods}
		<tr class="tableInner1">
			<td align="left">{$pods[0]}&nbsp;&nbsp;</td>
			<td align="right">{$pods[1]}&nbsp;&nbsp;</td>
		</tr>
	{/foreach}
	
	<tr class="tableInner1">
		<td align="center" colspan="2"><hr width="60%" /></td>
	</tr>
	<tr class="tableInner1">
		<td align="left">Gesamtguthaben</td>
		<td align="right">{$USER_GESAMT|number_format}&nbsp;&nbsp;</td>
	</tr>
	<tr class="tableInner1">
		<td align="left">Wert des Lagers</td>
		<td align="right">{$WERT_POD|number_format}&nbsp;&nbsp;</td>
	</tr>
	
	</table></td></tr></table></center>

{elseif $inneraction=="showprod"}

	<br>Syndikats-Ressourcenproduktion der einzelnen Konzerne <b>des letzten Ticks</b>:<br>&nbsp;
	<center>
	<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
		<tr>
			<td>
				<table cellspacing="1" cellpadding=4 border=0>
					<tr class="tableHead">
						<td align="left">Konzern</td>
						<td align="center">Credits</td>
						<td align="center">Energie</td>
						<td align="center">Erz</td>
						<td align="center">Forschungspunkte</td>
						<td align="right">{$currency}&nbsp;&nbsp;</td>
					</tr>
					{foreach from=$memberprod item=member}
						<tr class="tableInner1">
							<td align="left">{$member[0]}&nbsp;&nbsp;</td>
							<td align="center">{$member[1]}</td>
							<td align="center">{$member[2]}</td>
							<td align="center">{$member[3]}</td>
							<td align="center">{$member[4]}</td>
							<td align="right">{$member[5]}&nbsp;&nbsp;</td>
						</tr>
					{/foreach}
					<tr class="tableInner2">
						<td align="left"></td>
						<td align="center">{$totalres[0]}</td>
						<td align="center">{$totalres[1]}</td>
						<td align="center">{$totalres[2]}</td>
						<td align="center">{$totalres[3]}</td>
						<td align="right">{$totalres[4]}&nbsp;&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</center>
{elseif $inneraction == "access"}
	<br>
	<center>
		<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
			<tr>
				<td>
					<table cellspacing="1" cellpadding=4 border=0>
						<tr class="tableHead">
							<td colspan="100" align="left">Lagerzugriff</td>
						</tr>
						<tr class="tableHead2">
							<td>Konzernname</td>
							<td>Zugriff</td>
							{if $ispresidente}
								<td>Sperren</td>
								<td>Cr</td>
								<td>MWh</td>
								<td>t</td>
								<td>P</td>
							{/if}
						</tr>
						{foreach from=$lagermember item=memb}
							<tr class="tableInner1">
								<td>{$memb[8]}</td>
								<td>
								{if $memb[0]==true}
									<span style="color:#00cc00">Zugriff erlaubt</span>
									</td>
									{if $ispresidente}
										<td align="center">
											<form action="pod.php" method="post" style="margin:0px">
												Für <input name="days" value="0" size="2"> Tage und
												<input type="hidden" name="inneraction" value="access">
												<input type="hidden" name="innerinneraction" value="sperren">
												<input type="hidden" name="user_id" value="{$memb[1]}">
												<input name="hours" value="0" size="2"> Stunden
												<input type="submit" value="sperren">
										</td>
										<td>{$memb[4]}</td>
										<td>{$memb[5]}</td>
										<td>{$memb[6]}</td>
										<td>{$memb[7]}</form></td>
									{/if}
								{else}
									<span style="color:#ff6666">
										Zugriff auf {$memb[2]} gesperrt bis {$memb[3]}
									</span>
									</td>
									{if $ispresidente}
										<td align="center"><a href="pod.php?inneraction=access&innerinneraction=entsperren&user_id={$memb[1]}" align="center" class="linkAufsiteBg">
										Sperrung aufheben</a>
										</td>
										<td>{$memb[4]}</td>
										<td>{$memb[5]}</td>
										<td>{$memb[6]}</td>
										<td>{$memb[7]}</td>
									{/if}
								{/if}
							</tr>
						{/foreach}			
					</table>
				</td>
			</tr>
		</table>
	</center>
{/if}
{if $goon}
	{if $transactions}
		<br>
		<center>
			<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
				<tr>
					<td>
						<table cellspacing=1 cellpadding=5 border=0>
							<tr>
								<td class="tableHead" align=center>Überweisungen</td>
							</tr>
							{foreach from=$transactions item=trans}
							<tr>
								<td class="tableInner1">
									Überweisung über {$trans[1]} {$trans[2]}
									{if $trans[3]}
										 gegen {$trans[3]} {$trans[4]}
									{/if}
									{if $trans[0]=="toone"}
										 an
									{else}
										 von
									{/if}
									{$trans[5]} {$trans[6]} 
									{if $trans[0]=="toone"}
										 <a href="pod.php?inneraction=transferrefuse&tid={$trans[7]}" class="linkAuftableInner">
											zurücknehmen
										</a>
									{else}
										 <a href="pod.php?inneraction=transfertake&tid={$trans[7]}" class="linkAuftableInner">
										 annehmen</a> / <a href="pod.php?inneraction=transferrefuse&tid={$trans[7]}" 
										 class="linkAuftableInner">ablehnen?
										 </a>
									{/if}
								</td>
							</tr>		
							{/foreach}
						</table>
					</td>
				</tr>
			</table>
		</center>
	{/if}
	<br>Ihr Syndikat besitzt momentan folgende Ressourcen:<br>
	<br>
	<center>
		<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
			<tr>
				<td>
					<table width="450" cellspacing=1 cellpadding=5 border=0>
						<tr class="tableHead">
							<form name="resform" action=pod.php method=post style="margin:0px">
							<td>Ressource</td>
							<td align=right>Anzahl&nbsp;&nbsp;</td>
							<td align=right>Max. Entnahme&nbsp;&nbsp;</td>
							<td>Anzahl</td>
						</tr>
						{foreach from=$lagerress item=ress}
						<tr class="tableInner1">
							<td>{$ress[0]}</td>
							<td align=right>{$ress[1]|number_format}&nbsp;&nbsp;</td>
							<td align=right>{$ress[2]}</td>
							<td><input class="input" name={$ress[3]} value"0" size=6>
								&nbsp;&nbsp;<img width=18 height=18 src="{$ripf}_for_up.gif" onClick="{$ress[4]}"  align=absmiddle border=none>
								&nbsp;<img width=18 height=18 src="{$ripf}_for_down.gif" onClick="{$ress[5]}"  align=absmiddle border=none>
							</td>
						</tr>
						{/foreach}
						<tr>
							<td colspan="4" class="tableInner2" >
								<table class="tableInner2" border=0 width="100%" cellpadding="0" cellspacing="0" style="border:none">
									<tr style="padding-top:6px">
										<td>
											<img width=18 height=18 src="{$ripf}_for_up.gif">: Maximale Anzahl einlagern<br>
											<img width=18 height=18 src="{$ripf}_for_down.gif">: Maximale Anzahl entnehmen
										</td>
										<td colspan="3" align="right">
											<input type=radio id=1 name=inneraction value=get>Entnehmen
											<input type=radio id=2 name=inneraction value=store checked>Einlagern
											<br><br>
											<input type="submit" value="Ausführen">
											</form>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>&nbsp;<br><strong>Sie haben <span class="highlightAufSiteBg">{$lagergut}</span>
		 {$lagercur}</strong><br>(Ihre momentan mögliche Maximalverschuldung liegt bei 
		 <span class="highlightAufSiteBg">{$maxschuld}</span> {$lagercur} <br>
		 und vor Dieben sind <span class="highlightAufSiteBg">{$MAXSAVE}</span> {$lagercur} sicher.)
	</center>
	<br>Aktuelle Kurse*:<br><br>
	<center>
		<table cellspacing=0 cellpadding=0 border=0 class="tableOutline">
			<tr>
				<td>
					<table width="200" cellspacing=1 cellpadding=5 border=0>
						<tr class="tableHead">
						{foreach from=$ressvals item=val}
							<td align=center>{$val[0]}</td>
						{/foreach}
						</tr>
						<tr class="tableInner1">
						{foreach from=$ressvals item=val}
							<td align=center>{$val[1]}</td>
						{/foreach}
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
		<a align="left" class="linkAufsiteBg" href="pod.php?inneraction=showactions">Lagerzugriffe der letzten 24 Stunden</a>
		|
		<a align="left" class="linkAufsiteBg" href="pod.php?inneraction=showpoints">Guthaben der einzelnen Konzerne</a>
		|
		<a align="left" class="linkAufsiteBg" href="pod.php?inneraction=showprod">Syndikats-Ressourcenproduktion</a>
		<br>
		<a align="left" class="linkAufsiteBg" href="pod.php?inneraction=access">Zugriff auf das Syndikatslager</a>
		<br><br>
		<!--
			<br>
			<a name="t"></a>
			Ressourcen an ein Syndikatsmitglied überweisen:
			<form action=pod.php method=post>
			<input type=hidden value="transfer" name="inneraction">
			<select name=product id="transfer_product">
				{$select1}
			</select>
			<input class="input" id="transfer_number" name=number value="0" size=8>
			<img width=18 height=18 src="{$ripf}_for_up.gif" onClick="maxtrans();"  align=absmiddle border=none> an 
			<select name=receiver>
				{$select2}
			</select>
			<input class="button" type=submit value="Überweisen">
			</form>
			<br><br>
		-->
		
		<br>
		<a name="t"></a>
		
		<form action=pod.php method=post style="margin:0px">
		<table class="tableOutline" width="420" cellspacing=1 cellpadding=5 border=0>
			<tr class="tableHead">
				<td colspan="4" align="center">
					Tausche mit Spieler 
					<select name=receiver>
						{$select3}
					</select> 
				</td>
			</tr>
			<tr class="tableInner1">
				<td width="50">
					Biete 
				</td>
				<td width="100">
				<input type=hidden value="transfer" name="inneraction">
					<select name=product id="transfer_product_with_request">
						{$select4}
					</select>
					</td>
					<td width="200">
						<input class="input" id="transfer_number_with_request" name=number value="0" size=8>
						<img width=18 height=18 src="{$ripf}_for_up.gif" onClick="maxtrans_with_request();" align=absmiddle border=none>
						<img width=18 height=18 src="{$ripf}_for_aequiv.gif" onClick="maxtrans_aquivalenz(1);" align=absmiddle border=none>
					</td>
					<td width="50">tausche</td>
				</tr>
				<tr class="tableInner1">
				<td>
					gegen
				</td>
				<td>
				<select name="product_request" id="get_product_with_request">
					{$select5}
				</select>
				</td>
				<td>
					<input class="input" id="get_number_with_request" name="number_request" value="0" size=8>
					<img width=18 height=18 src="{$ripf}_for_aequiv.gif" onClick="maxtrans_aquivalenz(2);" align=absmiddle border=none> 
				</td>
				<td>
					<input class="button" type="submit" value="tauschen"> 
				</td>
			</tr>
		</table>
		</form>
		<br><br>			
		<br><br>	
		<table class="tableInner1" width="400">
			<tr>
				<td>
				<br>* Direkte Überweisungen werden zu den Kursen bei Annahme verrechnet. Lesen Sie sich diesbezüglich bitte auch die Hilfe durch. <br><br>
				** Beim Einlagern von Credits und anderen Ressourcen fallen Steuern von {$stres}% an.
				<!-- Beim Einlagern von Credits fallen {$strcr}% Steuern an, beim Einlagern anderer Ressourcen fallen {$stres}% Steuern an!  -->
				</td>
			<tr>
		</table>
	</center>
{/if}