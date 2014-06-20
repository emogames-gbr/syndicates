<?


/**
 * Autor: Konstantin Grupp/inok
 * 
 * wird eingebunden in inc/events.php
 * 
 */
 

// Weltuntergang (21.12.2012)
if ((Events::getSettings('weltuntergang','starttime') < $time) && 
	($time < Events::getSettings('weltuntergang','endtime'))) {
	if (preg_match('/.*forum.php/', $_SERVER['PHP_SELF']) ||
		preg_match('/.*fragen_und_antworten_board.php/', $_SERVER['PHP_SELF']) ||
		preg_match('/.*report.php/', $_SERVER['PHP_SELF'])) {
		// Diese Seiten sind weiterhin aufrufbar und nicht geblockt
	}
	// Prüfen ob auf logout gedrückt
	elseif (preg_match('/.*logout.php/', $_SERVER['PHP_SELF'])) {
		select("INSERT INTO events (type, konzernid, value) VALUES ('weltuntergang', " . $status['id'] . ", 2)");
	// Prüfen ob bereits erschreckt xD
	} elseif ($WeltuntergangNoChance) {
		select("INSERT INTO events (type, konzernid, value) VALUES ('weltuntergang', " . $status['id'] . ", 1)");
	// Frische Opfer erschrecken xD
	} elseif (!Events::getEvent('weltuntergang')) { //!single("SELECT COUNT(*) FROM events WHERE type = 'weltuntergang' AND konzernid = " . $status['id'])) {
		// Werte auf 0 setzen
		$events_NoDisplay = true; // deaktiviert $tpl->display(header/footer)

		// Header
		include ("../../inc/ingame/header.php");
		$tpl->assign('NETWORTH', 0);
		$tpl->assign('LAND', 0);
		$tpl->assign('CREDITS', 0);
		$tpl->assign('ENERGY', 0);
		$tpl->assign('MINERALS', 0);
		$tpl->assign('SCIENCEPOINTS', 0);
		$tpl->display('header.tpl');

		// Konfigseite faken
		require_once (LIB . "js.php");
		js :: loadOver();
		$buildingstats = getbuildingstats();

		$tradecenters = ($tradecenters && is_numeric($tradecenters) ? $tradecenters : 0);
		$powerplants = ($powerplants && is_numeric($powerplants) ? $powerplants : 0);
		$ressourcefacilities = ($ressourcefacilities && is_numeric($ressourcefacilities) ? $ressourcefacilities : 0);
		$ciencelabs = ($sciencelabs && is_numeric($sciencelabs) ? $sciencelabs : 0);
		$factories = ($factories && is_numeric($factories) ? $factories : 0);
		$buildinggrounds = ($buildinggrounds && is_numeric($buildinggrounds) ? $buildinggrounds : 0);

		// Konstanten
		$tpl->assign('PROD_HZ', TRADECENTER_PRODUCTION);
		$tpl->assign('PROD_EFAS', RESFAC_PRODUCTION);
		$tpl->assign('PROD_FLABS', SCIENCELAB_PRODUCTION);
		$tpl->assign('PROD_KWS', POWERPLANT_PRODUCTION);
		$tpl->assign('STD_CR', CREDIT_STD_VALUE);
		$tpl->assign('CUR_NRG', $status['energy']);
		$tpl->assign('LAND_MIN', 0);
		$tpl->assign('LAND_MAX', 0 * 2);
		$tpl->assign('STD_ERZ', 6); //METAL_STD_VALUE);
		$tpl->assign('STD_FP', 16); //SCIENCEPOINTS_STD_VALUE);
		$tpl->assign('STD_NRG', 1.2); //ENERGY_STD_VALUE);
		$tpl->assign('SYNERGIE', 2);
		$tpl->assign('SYNERGIE_MAX', 50);
		$tpl->assign('BONI_FABS', 2.5);
		$tpl->assign('BONI_FABS_MAX', 50);
		$tpl->assign('BONI_BHS_KOSTEN_MAX', 70);
		$tpl->assign('BONI_BHS_KOSTEN', 3.5);
		$tpl->assign('BONI_BHS_ZEIT_MAX', 10);
		$tpl->assign('BONI_BHS_ZEIT', 0.5);

		$tpl->assign('PROD_CR', 0);
		$tpl->assign('PROD_NRG', 0);
		$tpl->assign('PROD_ERZ', 0);
		$tpl->assign('PROD_FP', 0);
		$tpl->assign('PROD_NRG_VERBRAUCH', 0);
		$tpl->assign('PROD_NRG_BILANZ', 0);
		$tpl->assign('PROD_GES', 0);
		$tpl->assign('TRADECENTERS', $tradecenters);
		$tpl->assign('PROD_CR_BONI_SYN', 0);
		$tpl->assign('PROD_CR_BONI_FRAK', ($status['race'] == 'uic' ? UIC_PAUSCHAL_RESSOURCENBONUS : 0));
		$tpl->assign('PROD_CR_BONI_GES', 0);
		$tpl->assign('POWERPLANTS', $powerplants);
		$tpl->assign('PROD_NRG_BONI_SYN', 0);
		$tpl->assign('PROD_NRG_BONI_FRAK', ($status['race'] == 'uic' ? UIC_PAUSCHAL_RESSOURCENBONUS : 0));
		$tpl->assign('PROD_NRG_BONI_GES', 0);
		$tpl->assign('RESSOURCEFACILITIES', $ressourcefacilities);
		$tpl->assign('PROD_ERZ_BONI_SYN', 0);
		$tpl->assign('PROD_ERZ_BONI_FRAK', ($status['race'] == 'uic' ? UIC_PAUSCHAL_RESSOURCENBONUS : 0));
		$tpl->assign('PROD_ERZ_BONI_GES', 0);
		$tpl->assign('SCIENCELABS', $sciencelabs);
		$tpl->assign('PROD_FP_BONI_SYN', 0);
		$tpl->assign('PROD_FP_BONI_FRAK', ($status['race'] == 'uic' ? UIC_PAUSCHAL_RESSOURCENBONUS : 0));
		$tpl->assign('PROD_FP_BONI_GES', 0);
		$tpl->assign('MILI_BAUPREIS', 100);
		$tpl->assign('GEB_BAUPREIS', 100);
		$tpl->assign('GEB_BAUZEIT', 20 - ($status['race'] == 'uic' ? 20 * UIC_BUILDINGS_SPEEDBONUS : 0));

		$buildingstats_output = array ();
		$vl = array ();
		foreach ($buildingstats as $key => $value) { //Gebäudebauformular
			if ($buildingstats[$key]['erforschbar'] == null) {
				$vl['name'] = $value[name];
				$vl['getBuildingTooltip'] = getBuildingTooltip($value, 1);
				$vl['intverbrauch'] = $value[intverbrauch];
				$vl['key'] = $key;
				$vl['value'] = ($_POST[$key] ? $_POST[$key] : 0);
				array_push($buildingstats_output, $vl);
				unset ($vl);
			}
		}
		$tpl->assign('buildingstats', $buildingstats_output);
		$tpl->assign('status', $status);
		$tpl->assign('ripf', $ripf);
		
		$tpl->assign('ERROR', "Es tut uns schrecklich leid, aber gegen die Mächte die die Mayas beschworen haben kommen wir leider nicht an.</b><br><br> 
		Aufgrund mysteriöser Umstände wurden alle Konzerne zurückgesetzt und starten bei 0 und nein nicht einmal die 400 ha zum Starten wurden vergeben.");
		$tpl->display('fehler.tpl');
		$tpl->display('events/weltuntergang.tpl');

		// Footer
		include ("../../inc/ingame/footer.php");
		$tpl->display('footer.tpl');
		exit();
	}
}
?>