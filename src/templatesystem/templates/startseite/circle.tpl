{strip}<div class="circle">
	<!-- positiv, neutral, negativ -->
	<div class="special_roundstatus1 special_{$RS1_TYPE}">
		{$RS1_MSG}
	</div>
	<div class="special_roundstatus2 special_{$RS2_TYPE}">
		{$RS2_MSG}
	</div>
	<div class="circle_input">
		<div class="desc uic"></div>
		<div class="desc sl"></div>
		<div class="desc pbf"></div>
		<div class="desc nof"></div>
		<div class="desc neb"></div>
		<div class="desc uic_hover {if !$FRAKTIONEN.uic.loged_in}uic{if $FRAKTIONEN.uic.active}_available{else}_blocked{/if}{/if}" id="desc_uic">
			<div style="text-align:center; font-weight:bold; font-size:13px;">United Industries Corporation<br />- Der Wirtschaftsboss -</div>
			"Als ich den maroden Laden vor 15 Jahren von meinem Vater &uuml;bernommen habe, standen wir kurz davor von Cushinbery Cybergenetics ge&shy;fressen zu werden. Ich habe diese Anf&auml;nger in 5 Jahren komplett aus dem Markt gedr&auml;ngt und produziere jetzt 55% aller weltweit eingesetzt Firestorm Kampfroboter."
		</div>
		<div class="desc sl_hover {if !$FRAKTIONEN.sl.loged_in}sl{if $FRAKTIONEN.sl.active}_available{else}_blocked{/if}{/if}" id="desc_sl">
			<div style="text-align:center; font-weight:bold; font-size:13px;">Shadow Labs<br />- Der Forscher -</div>
			"Gentechnologie ist illegal haben sie gesagt! Tarnkappenbomber sind umoralisch haben sie gesagt! Und wo sind sie geblieben? Der Fort&shy;schritt l&auml;sst sich nicht aufhalten. Stealth-Tec ist den meisten da drau&szlig;en um Lichtjahre in der Forschung voraus. Wir produzieren effizienter, k&auml;mpfen mit fortschrittlichen Waffen und wachsen schneller."
		</div>
		<div class="desc pbf_hover {if !$FRAKTIONEN.pbf.loged_in}pbf{if $FRAKTIONEN.pbf.active}_available{else}_blocked{/if}{/if}" id="desc_pbf">
			<div style="text-align:center; font-weight:bold; font-size:13px;">Brute Force<br />- Der Militarist -</div>
			"Wenn du deinem Gegner Auge um Auge gegen&uuml;ber stehst, ist es egal, ob dein Arsch vergoldet ist. Unsere Wartanks sind vielleicht nicht sch&ouml;n, aber eine Plasmagranate in deinem Hintern ist noch viel h&auml;sslicher. Deshalb entschlie&szlig;en sich st&auml;ndig Konkurrenten dazu, einen Teil ihrer Konzerne an uns abzutreten. Aber dass man uns deshalb als &uuml;berregionale Bedrohung darstellen muss..."
		</div>
		<div class="desc nof_hover {if !$FRAKTIONEN.nof.loged_in}nof{if $FRAKTIONEN.nof.active}_available{else}_blocked{/if}{/if}" id="desc_nof">
			<div style="text-align:center; font-weight:bold; font-size:13px;">Nova Federation<br />- Der Waffenh&auml;ndler -<!-- - Der Kriegsspezialist - --></div>
			"Sollen sich die anderen Konzerne doch gegenseitig die Sch&auml;del wegblasen. Wir verdienen an jedem Krieg. Wenn du nicht untergehen willst, hat selbst der Frieden seinen Preis. Und da sei mal nicht allzu knickrig. Am Ende helfen wir sogar den Kosten bewussten Kunden, indem wir sie auf ein ihrer Verteidigung angemessenes Ma&szlig; verschlanken."
			<!-- "Mit einem NOF legt man sich nur ungern an! Wir k&ouml;nnen zwar auch bei&szlig;en, doch unsere Abwehr ist unsere St&auml;rke. Spionage? Sabota&shy;ge? Angriffe? An uns bei&szlig;t man sich die Z&auml;hne aus. Und zum Dank nimmt die gegnerische Armee noch einen zweist&uuml;ndigen Umweg in Kauf"  -->
		</div>
		<div class="desc neb_hover {if !$FRAKTIONEN.neb.loged_in}neb{if $FRAKTIONEN.neb.active}_available{else}_blocked{/if}{/if}" id="desc_neb">
			<div style="text-align:center; font-weight:bold; font-size:13px;">New Economic Block<br />- Der &Uuml;berstarter -</div>
			"UIC als Wirtschaftsboss? Vergesst es! Dem NEB geh&ouml;rt die Zukunft! Als der NEB sich vor Jahren vom UIC abspaltete, hielt man uns f&uuml;r verr&uuml;ckt! Aber wir zeigen es allen! Wir wachsen schneller und preisg&uuml;nstiger und unsere tech&shy;nisch aufgewerteten Erzf&ouml;rderanlagen laufen dem UIC den Rang ab."
		</div>
		{include file="login.tpl"}
		
		{if $LOGIN == 'first' || $LOGIN == 'fb_reg'}
		<div class="register_outer">
			Jetzt <strong>kostenlos</strong><br />
			<a class="button_orange register lightbox" href="?action=anmeldung"></a>
		</div>
		{else}
		<div class="register_outer" style="top: 285px; left:78px; line-height:15px; font-size:13px;">
			<div style="float:left;"><img src="images/startseite/icon_config.png" style="vertical-align:middle;" /></div>
			<div style="float:left; padding-left:10px; text-align:left;"><a href="?action=emogames_cfg" class="navi_left link" target="_blank" style="text-align:left;">Einstellungen<br />bei Emogames</a></div>
		</div>
		<div class="register_outer" style="top:340px; line-height:15px; font-size:13px;">
			<div style="float:left;"><img src="images/startseite/icon_stats.png" style="vertical-align:middle;" /></div>
			<div style="float:left; padding-left:10px; text-align:left;"><a href="?action=own_stats" class="navi_left link" style="text-align:left;">Eigene<br />Statistiken</a></div>
		</div>
		{/if}
		
		<a class="clean" href="?"><div class="navi_right link_1">NEWS</div></a>
		<a class="clean" href="?action=infos"><div class="navi_right link_2">INFOS</div></a>
		<a class="extern clean" href="http://board.emogames.de/board.php?boardid=6" target="_blank"><div class="navi_right link_3">FORUM</div></a>
		<a class="extern clean" href="http://www.syndicates-wiki.de" target="_blank"><div class="navi_right link_4">SYNPEDIA</div></a>
		<a class="clean" href="?action=stats"><div class="navi_right link_5">STATISTIKEN</div></a>
		
		<form action="http://www.syndicates-wiki.de/index.php" target="_blank" style="padding:0px; margin:0px;">
			<div class="searchform">
				<input type="text" name="search" class="searchinput" id="searchinput" value="In Synpedia finden" />
				<button type="submit" name="fulltext" id="mw-searchButton" class="searchbutton"><img src="images/startseite/search.png" width="12px" height="13px;" /></button>
			</div>
			<input type="hidden" name="title" value="Spezial:Suche" />
		</form>
	</div>
</div>
{/strip}