{include file='js/merchandise.js.tpl'}
<br>
<br>
<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline">
	<!-- Votebuttons -->
	
	<tr class="tableHead">
		<td colspan="3" align="center">
			Votebuttons
		</td>
	</tr>
	<tr class="tableInner1">
		<td colspan="3">
		<div style="position:relative;">
		{if $IS_OSTERN && $OSTER_BONI.14923}
			<div style="position:absolute; z-index:1; top:0px; left:400;">
				<a class="normal" href="bonus.php?type=4&amp;egg=14923">
					<img src="images/ostern_14923.png"></a>
			</div>
		{/if}
			<center>
			</center>
		</div>
		</td>
	</tr>
	
	<!-- Syngatur -->
	
	<tr class="tableHead">
		<td colspan="3" align="center">
			Sygnatur
		</td>
	</tr>
	<tr class="tableInner1">
		<td colspan="2">
			Die Sygnatur ist eine kleine Grafik mit Statistiken, die man in seine Foren-Signatur einbinden kann (egal in welchem Forum).
		</td>
		<td align="center">
			<form style="margin:0px" action="merchandise.php" method="post">
				<input type="hidden" name="inner" value="sygnatur">
			{if !$SYGNATUR}
				<input type="hidden" name="activate" value="true">
				<input type="submit" value="aktivieren">
			{else}
				<input type="hidden" name="deactivate" value="true">
				<input type="submit" value="deaktivieren">
			{/if}
			</form>
		</td>
	</tr>
	{if $SYGNATUR}
	<tr class="tableInner1">
		<td>
			Sygnatur-Typ
		</td>
		<form style="margin:0px" action="merchandise.php" method="post">
			<td>
				<input type="hidden" name="inner" value="sygnatur">
				<input type="hidden" name="typechange" value="1">
				<select name="type">
					<option value="actualdata" {if $SYGNATUR == "actualdata"}selected{/if}>Daten von aktueller Runde</option>
					<option value="honors" {if $SYGNATUR == "honors"}selected{/if}>Auszeichnungen zeigen</option>
					<option value="statslastnetworth" {if $SYGNATUR == "statslastnetworth"}selected{/if}>Statistiken (alter Runden) nach NW</option>
					<option value="statsendrank" {if $SYGNATUR == "statsendrank"}selected{/if}>Statistiken (alter Runden) nach Rang</option>
				</select>
			</td>
			<td align="center">
				<input type="submit" value="ändern">
			</td>
		</form>
	</tr>
	<tr class="tableInner1">
		<td>
			Hintergrund
		</td>
		<form style="margin:0px" action="merchandise.php" method="post">
			<td>
				<input type="hidden" name="inner" value="sygnatur">
				<input type="hidden" name="background_change" value="1">
				<select name="background">
					<option value="race" {if $SYGNATUR_BACKGROUND == "race"}selected{/if}>Entsprechend Fraktion</option>
					<option value="pbf" {if $SYGNATUR_BACKGROUND == "pbf"}selected{/if}>Brute Force</option>
					<option value="sl" {if $SYGNATUR_BACKGROUND == "sl"}selected{/if}>Shadow Labs</option>
					<option value="uic" {if $SYGNATUR_BACKGROUND == "uic"}selected{/if}>Un. Ind. Corp.</option>
					<option value="neb" {if $SYGNATUR_BACKGROUND == "neb"}selected{/if}>New Ec. Blocka</option>
					<option value="nof" {if $SYGNATUR_BACKGROUND == "nof"}selected{/if}>Nova Federation</option>
				</select>
			</td>
			<td align="center">
				<input type="submit" value="ändern">
			</td>
		</form>
	</tr>
	<tr>
		<td class="tableInner1" colspan="3">
			<img src="{$WWWDATA}/sygnatur/{$MD5BILDHASH}.gif?{$CREATEKEY}" border="0"><br>
			<br>
			Benutze einfach folgenden BB-Code (Copy & Paste), um die Sygnatur in deine Forensignatur 
			<a href="javascript:info('hilfe','forensignatur')" class="highlightAuftableInner">
				<img src="{$RIPF}_help.gif" border="0" valign="absmiddle">
			</a> einzubinden:<br>
			<input type="text" size="90" onfocus="this.select();" value="[URL={$PROJECT_WWW}?ref_src=u{$ACCOUNTID}][IMG]{$WWWDATA}sygnatur/{$MD5BILDHASH}.gif[/IMG][/URL]">
			<br>
			<br>
			PS: Wenn jemand auf den Link zu Syndicates klickt zählt er im Rahmen des Spieler-Wirbt-Programm als von
			dir geworbener Spieler und du erhältst EMOs, wenn sich der geworbene Spieler EMOs auflädt und ausgibt.
			Mehr Details findest du unter "Spieler werben" unten links im Menü in dem auch der Logout zu erreichen ist.<br>
			<br>
			PPS: Die Sygnatur-Grafik wird jede Nacht um 03:00 Uhr aktualisiert -- zum manuellen aktualisieren einfach
			auf deaktivieren und anschließend wieder auf aktivieren klicken.<br>
			<br>
		</td>
	</tr>
	{/if}
	
		<!-- Banner -->
	
	<tr class="tableHead">
		<td colspan="3" align="center">
			Bilder, Bilder, Bilder
		</td>
	</tr>
	<tr>
		<td class="tableInner1" colspan="3">
			<img src="http://images.emogames.de/banner/fullsize/banner_02.gif" border="0" id="banner02" onClick="merch_banner('banner02')"><br>
			<img src="http://images.emogames.de/banner/fullsize/banner_03.jpg" border="0" id="banner03" onClick="merch_banner('banner03')"><br>
			<img src="http://images.emogames.de/banner/fullsize/banner_04.jpg" border="0" id="banner04" onClick="merch_banner('banner04')"><br>
			<img src="http://www.syndicates-wiki.de/images/9/99/SynbannerNEU.gif" border="0" id="banner15" onClick="merch_banner('banner15')"><br>
			<img src="http://images.emogames.de/banner/buttons/synbutton.gif" border="0" id="banner05"  onClick="merch_banner('banner05')">
			<img src="http://images.emogames.de/banner/buttons/synbutton2.gif" border="0" id="banner06"  onClick="merch_banner('banner06')">
			<img src="http://images.emogames.de/banner/buttons/synbutton3.gif" border="0" id="banner07"  onClick="merch_banner('banner07')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/syndicates/header/header_neutral.jpg" border="0" id="banner08"  onClick="merch_banner('banner08')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/syndicates/header/header_bf.jpg" border="0" id="banner09"  onClick="merch_banner('banner09')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/syndicates/header/header_neb.jpg" border="0" id="banner10"  onClick="merch_banner('banner10')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/syndicates/header/header_sl.jpg" border="0" id="banner11"  onClick="merch_banner('banner11')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/syndicates/header/header_uic.jpg" border="0" id="banner12"  onClick="merch_banner('banner12')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/emogames/header_01.jpg" border="0" id="banner13"  onClick="merch_banner('banner13')"><br>
			<img width="500px" src="http://images.emogames.de/grafiken_extern/emogames/header_02.jpg" border="0" id="banner14"  onClick="merch_banner('banner14')"><br>
			<br>
			Klicke das entsprechende Bild an und im folgenden Feld steht der BBCode für deine Forensygnatur, ...
			<input id="bbcode" type="text" size="70" onfocus="this.select();" value=""> (BBCode)<br>
			<input id="bild" type="text" size="70" onfocus="this.select();" value=""> (Bildlink)
		</td>
	</tr>
</table>
<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline" >
	
		<!-- Wallpaper -->
	
	<tr class="tableHead">
		<td colspan="3" align="center">
			Ultimative Syndicates Wallpaper
		</td>
	</tr>
	<tr class="tableInner1">
		<td align="center">
			<img width="250px" src="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper1_thumb.jpg" border="0"><br><br>
			<a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper1_800.jpg" class="linkAuftableInner" >800*600 px</a> | <a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper1_1024.jpg" class="linkAuftableInner" >1024*768 px</a> | <a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper1_1280.jpg" class="linkAuftableInner" >1280*1024 px</a>
		</td>
		<td align="center">
			<img width="250px" src="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper2_thumb.jpg" border="0"><br><br>
			<a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper2_800.jpg" class="linkAuftableInner" >800*600 px</a> | <a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper2_1024.jpg" class="linkAuftableInner" >1024*768 px</a> | <a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper2_1280.jpg" class="linkAuftableInner" >1280*1024 px</a>
		</td>
	</tr>
	<tr class="tableInner1">
		<td align="center">
			<img width="250px" src="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper3_thumb.jpg" border="0"><br><br>
			<a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper3_800.jpg" class="linkAuftableInner" >800*600 px</a> | <a href="http://images.emogames.de/grafiken_extern/syndicates/syn-wallpaper3_1024.jpg" class="linkAuftableInner" >1024*768 px</a>
		</td>
		<td align="center">
			<img width="250px" src="http://images.emogames.de/grafiken_extern/emogames/emogames_wallpaper1_thumb.jpg" border="0"><br><br>
			<a href="http://images.emogames.de/grafiken_extern/emogames/emogames_wallpaper1_1024.jpg"class="linkAuftableInner">1024*768 px</a>
		</td>
	</tr>
</table>