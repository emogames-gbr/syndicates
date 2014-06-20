{if $DEFECTED_GEZIELT_SHOW}
<br><br>
<form action="options.php" method="post">
	<input type="hidden" name="ia" value="1">
	<input type="hidden" name="konzernaction" value="defect_gezielt">
	<input type="hidden" name="konzernaction2" value="defect_gezielt">
	<table width="500" class="siteGround" align="center">	
		<tr>
			<td>
				Bitte geben Sie Syndikatsnummer und Syndikatspasswort des Syndikates, 
				in welches Sie wechseln möchten, sowie das Passwort für Ihren Account 
				(aus Sicherheitsgründen) an:<br>
				<br>
				<table align="center" width="400" class="siteGround">
					<tr>
						<td>Syndikatsnummer</td>
						<td>
							<input type="text" value="{$SYNDNUMMER}" name="syndnummer">
						</td>
					</tr>
					<tr>
						<td>Syndikatspasswort</td>
						<td>
							<input type="password" name="syndpassword">
						</td>
					</tr>
					<tr>
						<td>Emogames-Accountpasswort</td>
						<td>
							<input type="password" name="passwordaction">
						</td>
					</tr>
					<tr height="20">
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="submit" value="Syndikat wechseln">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
{/if}
{if $NAMECHANGE_SHOW}
<br><br><br>
<center>
	Sie möchten den Namen ihres Geschäftsführers / Konzerns ändern. Tragen Sie hier bitte die neuen Namen ein:<br>
	<br>
	<form action="options.php" method="post">
		<input type="hidden" name="inner" value="nc">
		<input type="hidden" name="next" value="1">
		<table cellpadding="3" class="siteGround" align="center">
			<tr>
				<td>Name des Geschäftsführers:</td>
				<td>
					<input type="text" name="rulername" value="{$RULERNAME_OLD}" size="19" disabled="disabled">
				</td>
			</tr>
			<tr>
				<td>Konzernname:</td>
				<td>
					<input type="text" name="syndicate" value="{$SYNDICATE_OLD}" size="19">
				</td>
			</tr>
		</table>
		<br>
		<input type="submit" value="Namen ändern">
	</form>
</center>
{/if}
{if $GOON && !$AUSGABEBLOCKED}
<br>
<br>
<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline" >
	<tr>
		<td align="center" class="tableHead" colspan="3">
			Kritische Optionen
		</td>
	</tr>
	<form action="options.php" method="post">
		<tr>
			<td class="tableInner1" align="left">
				Option wählen:
			</td>
			<td class="tableInner1" align="left" colspan="2">
				Bestätigen:
			</td>
		</tr>
		<tr>
			<td class="tableInner1" valign="middle">
				<select name="konzernaction" size="1">
					<option value="none">Keine Aktion ausgewählt</option>
				{if $DEFECT_GEZIELT_SHOW}
					<option value="defect_gezielt">Syndikat gezielt wechseln</option>
				{/if}
				{if $DEFECT_SHOW}
					<option value="defect">Syndikat wechseln</option>
				{/if}
				{if $ANFAENGER_BEREICH_VERLASSEN_SHOW}
					<option value="anfaenger_bereich_verlassen">Anfängerber. verl. (Syndikat wechseln)
				{/if}
					<option value="vacation">Urlaubsmodus aktivieren</option>
					<option value="reset">Konzern resetten</option>
					<option value="deletekonzern">Konzern löschen</option>
				</select>
			</td>
			<td class="tableInner1" valign="middle">
				<select name="konzernaction2" size="1">
					<option value="none">Keine Aktion ausgewählt</option>
				{if $DEFECT_GEZIELT_SHOW}
					<option value="defect_gezielt">Syndikat gezielt wechseln</option>
				{/if}
				{if $DEFECT_SHOW}
					<option value="defect">Syndikat wechseln</option>
				{/if}
				{if $ANFAENGER_BEREICH_VERLASSEN_SHOW}
					<option value="anfaenger_bereich_verlassen">Anfängerber. verl. (Syndikat wechseln)
				{/if}
					<option value="vacation">Urlaubsmodus aktivieren</option>
					<option value="reset">Konzern resetten</option>
					<option value="deletekonzern">Konzern löschen</option>
				</select>
			</td>
			<td class="tableInner1">
				Passwort:&nbsp;
				<input class="input" type="password" name="passwordaction" size="15">
			</td>
		</tr>
		<tr>
			<td class="tableInner1" colspan="2">
				Beim Reset können sie den Fraktionstyp ändern:
				<select name="race" size="1">
					<option value="{$STATUS.race}">Keine Änderung</option>
				{foreach from=$RACES item=VL}
					<option value="{$VL.o_race}">{$VL.o_tag}</option>
				{/foreach}
				</select>
			</td>
			<td class="tableInner1" align="center">
				<input class="button" type="submit" value="Absenden">
			</td>
		</tr>
	</form>
	<tr>
		<td colspan="3" class="tableInner1" align="center">
			Achtung, es wird keine weitere Bestätigung eingeholt. Die Aktion wird sofort durchgeführt!<br>
			Pro Runde kann höchstens 3 mal der Konzern resetted werden. Zwischen zwei Resets müssen 
			mindestens {$TIME_BETWEEN_RESET}h liegen.
		</td>
	</tr>		
	{if $NOTKSYNDICATES}
	<tr class="tableHead">
		<td colspan="3" align="center">
			Grafiksets
		</td>
	</tr>
<!--<tr class="tableHead">
		<td>
			Template auswählen:
		</td>
		<form style="margin:0px" action="options.php" method="post">
			<td>
				<input name="inner" value="changetpl" type="hidden">
				<select name="template_id">
				{foreach from=$TEMPLATES item=TEMP}
					<option {$TEMP.o_select} value="{$TEMP.template_id}">{$TEMP.name}</option>
				{/foreach}
				</select>
			</td>
			<td align="center" >
				<input type="submit" value="Ändern">
			</td>
		</form>
	</tr> -->
	<tr class="tableInner1">
		<td>
			Grafikset auswählen:
		</td>
		<form style="margin:0px" action="options.php" method="post">
			<td>
				<input name="inner" value="changeset" type="hidden">
				<select name="gpack_id">
				{foreach from=$GPACKS item=TEMP}
					<option {$TEMP.o_select} value="{$TEMP.gpack_id}">{$TEMP.name}</option>
				{/foreach}
				</select>		
				<input type="checkbox" name="gpack_default" value="1" {if $GPACK_IS_DEFAULT}checked{/if}> als eigener Standard festlegen 	
			</td>
			<td align="center">
				<input  type="submit" value="Ändern">
			</td>
		</form>
	</tr>
	<tr class="tableInner1">
		<td>
			Oder lokales Grafikset verwenden:
		</td>
		<script language="javascript" type="text/javascript">
			{literal}
			function verify_changepath() {
				var msg = "Wir raten von der Nutzung eines lokalen Grafiksets ab, da diese ein Sicherheitsrisiko darstellen können. Trotzdem fortfahren?";
				return confirm(msg);
			};
			{/literal}
		</script>
		<form style="margin:0px" action="options.php" method="post" onSubmit="return(verify_changepath())">
			<td>
				<input name="inner" value="changepath" type="hidden">
				<input size="25" name="path" value="{$STATUS.imagepath}">
				<a href="http://board.emogames.de/thread.php?threadid=469" target="_blank">
					<img border="0" src="{$RIPF}_help.gif" valign="absmiddle">
				</a>
			</td>
			<td align="center" rowspan="2">
				<input type="submit" value="Ändern">
			</td>
		</form>
	</tr>
	<tr class="tableInner1">
		<td colspan="2">
			Wir raten von der Nutzung eines lokalen Grafiksets ab, da diese ein Sicherheitsrisiko darstellen können.
		</td>
	</tr>
	<tr class="tableInner1">
		<td>
			<a class="linkAufTableInner" href="gpacks.php?view=showall">
				<u>Übersicht aller Grafikpakete</u>
			</a>
		</td>
		<td colspan="2">
			<a  class="linkAufTableInner" href="gpacks.php">
				<u>Eigenes Grafikset hochladen</u>
			</a>
		</td>
	</tr>
	{/if}
	<tr class="tableHead">
		<td colspan="3" align="center">
			Sonstige Optionen
		</td>
	</tr>
<!--<tr>
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="design">
				Klassiches Design verwenden
				<input name="classic" type="checkbox" {$SHOWCLASSIC}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr> -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="acceptall">
				<center>
					Lagerüberweisungen von Syndikatsmitgliedern automatisch akzeptieren.<br>
					<br>
					<table width="250" class="tableInner1">	
						<tr>
							<td align="center">Resource</td>
							<td align="center">geben lassen</td>
							<td align="center">nehmen lassen</td>
						</tr>
						<tr>
							<td align="center">Credits</td>
							<td align="center">
								<input {if $STATUS.moneyget == 1}checked{/if} type="checkbox" name="moneyget" value="1">
							</td>
							<td align="center">
								<input {if $STATUS.moneygive == 1}checked{/if} type="checkbox" name="moneygive" value="1">
							</td>
						</tr>
						<tr>
							<td align = center>Energie</td>
							<td align = center>
								<input {if $STATUS.energyget == 1}checked{/if} type="checkbox" name="energyget" value="1">
							</td>
							<td align = center>
								<input {if $STATUS.energygive == 1}checked{/if} type="checkbox" name="energygive" value="1">
							</td>
						</tr>
						<tr>
							<td align="center">Erz</td>
							<td align="center">
								<input {if $STATUS.metalget == 1}checked{/if} type="checkbox" name="metalget" value="1">
							</td>
							<td align="center">
								<input {if $STATUS.metalgive == 1}checked{/if} type="checkbox" name="metalgive" value="1">
							</td>
						</tr>
						<tr>
							<td align="center">Forschungspunkte</td>
							<td align="center">
								<input {if $STATUS.sciencepointsget == 1}checked{/if} type="checkbox" name="sciencepointsget" value="1">
							</td>
							<td align="center">
								<input {if $STATUS.sciencepointsgive == 1}checked{/if} type="checkbox" name="sciencepointsgive" value="1">
							</td>
						</tr>
					</table>
				</center>
			</td>
			<td class="tableInner1" align="center">
				<input class="button" type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="mailbestaetigung">
				Mail schicken wenn offline bei Angriff 
				<input name="sendmail_on_attack" type="checkbox" {$SENDMAIL_ON_ATTACK_CHECKED}>, 
				Kriegserklärung <input name="sendmail_on_war" type="checkbox" {$SENDMAIL_ON_WAR_CHECKED}>
			</td>
			<td class="tableInner1" align="center">
				<input class="button" type="submit" value="Absenden">
			</td>
		</form>
	</tr>
<!--<tr>
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="show_emogames_name">
				Emogames-Benutzernamen im Allianz-Forum anzeigen lassen 
				<input name="activate2" value="2" type="checkbox" {if $STATUS.show_emogames_name == 2}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input class="button" type="submit" value="Absenden">
			</td>
		</form>
	</tr> -->
{if $NAMECHANGESLEFT_SHOW}
<!-- namechangeoption -->
	<tr>
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">	
				<input type="hidden" name="inner" value="nc">
				Konzernname/Geschäftsführername ändern? {$NAMECHANGESLEFTTEXT} 
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Name ändern">
			</td>
		</form>
	</tr>
{/if}
<!-- ranking_anonymityoption -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="ra">
				Möchten Sie im Ranking für Eroberungen und Diebe anonym bleiben? 
				<input name="ravalue" type="checkbox" {if $STATUS.ranking_anonymity}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- safety_option -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="safety">
				Sicherheitswarnungen nicht anzeigen 
				<input name="safety" type="checkbox" {if $STATUS.safety}checked{/if} value="1">
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- pm_als_mail_option -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="pm_als_mail">
				Mitteilungen als E-Mail empfangen 
				<input name="pm_als_mail" type="checkbox" {if $STATUS.pm_als_mail}checked{/if} value="1">
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- queue_priority_option -->
	{if $ANY_FEATURE}
	<tr>
		<td rowspan="3" class="tableInner1">
			Priorität der Assistenten festlegen: {$PRINT_HILFE}
		</td>
		<td align="right" class="tableInner1">
			{$FIRST}
		</td>
		<td align="left" class="tableInner1">
			<a href="options.php?inner=change_queue_priority&new={$FIRST_NEW_POS}">
				<img border="0" src="{$RIPF}_for_down.gif">
			</a>
		</td>
	</tr>
	<tr>
		<td class="tableInner1" align=right>
			{$SECOND}
		</td>
		<td align="left" class="tableInner1">
			<a href="options.php?inner=change_queue_priority&new={$SECOND_NEW_POS}">
				<img border="0" src="{$RIPF}_for_up.gif">
			</a> 
			<a href="options.php?inner=change_queue_priority&new={$SECOND_NEW_POS2}">
				<img border="0" src="{$RIPF}_for_down.gif">
			</a>
		</td>
	</tr>
	<tr>
		<td class="tableInner1" align="right">
			{$THIRD}
		</td>
		<td align="left" class="tableInner1">
			<a href="options.php?inner=change_queue_priority&new={$THIRD_NEW_POS}">
				<img border="0" src="{$RIPF}_for_up.gif">
			</a>
		</td>
	</tr>
	{/if}
	<!-- display_help_on_all_pages -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="sh">
				Sollen ihnen auf jeder Seite einleitende Hilfetexte angezeigt werden? 
				<input name="shvalue" type="checkbox" {if $STATUS.show_help}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- Berater anzeigen -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="berater1">
				Soll der Berater/die Bauaufträge im Menü zusätzlich noch angezeigt werden? 
				<input name="berater_show" type="checkbox" {if $STATUS.berater_show}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- In Berater die Uhrzeit anzeigen statt der Ticks -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="berater">
				Soll auf der Militär, Forschungs und Gebäudeseite die Uhrzeit statt den verbleibenden Ticks angezeigt werden? 
				<input name="beraterview" type="checkbox" {if $STATUS.beraterview}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<!-- die Tipps anzeigen/verstecken -->
	<tr class="ajax">
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="hideTipps">
				Möchten Sie die Tipps auf allen Seiten dauerhaft deaktivieren?
				<input name="sethidetipps" type="checkbox" {if $HIDETIPPS}checked{/if}>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="Absenden">
			</td>
		</form>
	</tr>
	<tr class="tableHead">
		<td colspan="3" align="center">
			Sygnatur (inzwischen unter <a href="merchandise.php" class="linkAufTableInner">Merchandise</a>)
		</td>
	</tr>
	{if $FEATURES_KOMFORTPAKET}
	<tr class="tableHead">
		<td colspan="3" align="center">
			Eigenkonfigurierbares Seitenmenü {if $STATUS.mymenue}(aktiviert){else}(deaktiviert){/if}
		</td>
	</tr>
		{if $STATUS.mymenue}
	<tr>
		<form action="options.php" method="post">
			<input type="hidden" name="inner" value="menuchange">
			<input type="hidden" name="subaction" value="deactivate">
			<td class="tableInner1" colspan="2">
				Seitenmenü deaktivieren
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="absenden">
			</td>
		</form>
	</tr>
	<tr>
		<form action="options.php" method="post">
			<input type="hidden" name="inner" value="menuchange">
			<input type="hidden" name="subaction" value="changeposition">
			<td class="tableInner1">
				Position ändern
			</td>
			<td class="tableInner1">
				<select name="position">
					<option value="1" {if $STATUS.mymenue == 1}selected{/if}>Ganz oben</option>
					<option value="2" {if $STATUS.mymenue == 2}selected{/if}>Zw. Konzern und Komm.</option>
					<option value="3" {if $STATUS.mymenue == 3}selected{/if}>Zw. Komm. und Synd...</option>
					<option value="4" {if $STATUS.mymenue == 4}selected{/if}>Zw. Synd... und unterster Box</option>
					<option value="5" {if $STATUS.mymenue == 5}selected{/if}>Ganz unten</option>
				</select>
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="absenden">
			</td>
		</form>
	</tr>
	<tr>
		<td class="tableInner1" colspan="3">
			<table width="100%" class="tableInner1" border="0">
				<tr>
					<td width="10%"></td>
					<td width="30%"><b>Name</b></td>
					<td width="40%"><b>Adresse</b></td>
					<td width="10%"><b>Neues Fenster</b></td>
					<td width="10%"></td>
				</tr>
			{if $LINKDATA}
				{foreach from=$LINKDATA item=VL}
				<tr>
					<form action="options.php" method="get">
						<td nowrap>
							{if 1 < $VL.position}	
							<a href="options.php?inner=menuchange&subaction=changelinkposition&pos={$VL.position}&down=1" class="linkaufSiteBg">
								<img border="0" src="{$RIPF}_for_up.gif">
							</a>
							{/if}
							{if $VL.position < $LINKDATA_COUNT}
							<a href="options.php?inner=menuchange&subaction=changelinkposition&pos={$VL.position}&up=1" class="linkaufSiteBg">
								<img border="0" src="{$RIPF}_for_down.gif">
							</a>
							{/if}
							<a href="options.php?inner=menuchange&subaction=deletelink&linkid={$VL.id}" class="linkaufSiteBg">
								<img border="0" src="{$RIPF}_for_deselect.gif">
							</a>
							<input type="hidden" name="inner" value="menuchange">
							<input type="hidden" name="subaction" value="linkchange">
							<input type="hidden" name="linkid" value="{$VL.id}">
						</td>
						<td>
							<input type="text" name="linkname" size="20" maxlength="255" value="{$VL.name}">
						</td>
						<td>
							<input type="text" name="linkurl" size="30" maxlength="255" value="{$VL.address}">
						</td>
						<td align="center">
							<input type="checkbox" name="new_window" {if $VL.new_window}checked="true"{/if}>
						</td>
						<td align="center">
							<input type="submit" value="ändern">
						</td>
					</form>
				</tr>
				{/foreach}
			{else}
				<tr>
					<td align="center" class="tableInner1" colspan="5">
						Noch keine Links eingetragen
					</td>
				</tr>
			{/if}
				<tr height="30"></tr>
				<tr>
					<td align="center" class="tableInner1" colspan="4">
						<b><u>Neuen Link hinzufügen:</u></b>
					</td>
				</tr>
				<tr height="15"></tr>
				<tr>
					<td width="10%"></td>
					<td width="30%"><b>Name</b></td>
					<td width="40%"><b>Adresse</b></td>
					<td width="20%"></td>
				</tr>
				<tr>
					<form action="options.php" method="get">
						<td>	
							<input type="hidden" name="inner" value="menuchange">
							<input type="hidden" name="subaction" value="addlink">
						</td>
						<td>
							<input type="text" name="linkname" size="20" maxlength="255">
						</td>
						<td>
							<input type="text" name="linkurl" size="30" maxlength="255" value="http://">
						</td>
						<td align="center">
							<input type="checkbox" name="new_window" checked="true">
						</td>
						<td>
							<input type="submit" value="hinzufügen">
						</td>
					</form>
				</tr>
			</table>
		</td>
	</tr>
		{else}
	<tr>
		<form action="options.php" method="post">
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="menuchange">
				<input type="hidden" name="subaction" value="activate">
				Seitenmenü aktivieren
			</td>
			<td class="tableInner1" align="center">
				<input type="submit" value="absenden">
			</td>
		</form>
	</tr>
		{/if}
	<tr>
		<form action=options.php method=post>
			<td class="tableInner1" colspan="2">
				<input type="hidden" name="inner" value="np">
				Soll der Inhalt ihrer Notizblocks auf der Startseite angezeigt werden? 
				<input name="npvalue" type="checkbox" {if $STATUS.notespin}checked{/if}>
				</td>
				<td class="tableInner1" align="center">
					<input type="submit" value="Absenden">
					
				</td>
		</form>
	</tr>

	{/if}
</table>
{/if}
{include file="js/options.js.tpl"}