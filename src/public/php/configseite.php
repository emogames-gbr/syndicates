<?
#####################################################################
# Seite zum Konfigurieren seines Konzerns... Neu eingeführt Runde 36 statt der Schutzzeit
# by Mura
####################################################################

require_once("../../inc/ingame/game.php");
require_once (LIB."js.php");
js::loadOver();

$buildingstats = getbuildingstats();
$goon = 1;

//if ($tpl->get_template_vars('MSG') != '') $noMsg = 1;
//if ($tpl->get_template_vars('ERROR') != '') $noError = 1;
//if ($tpl->get_template_vars('INFO') != '') $noInfo = 1;


## Javascript Code im template!

/*
		 foreach ($buildingstats as $key => $value) {            //Gebäudebauformular
                    if ($buildingstats{$key}{erforschbar} == null) {
		        $ausgabe .="parseInt(dijit.byId(\"".$key."\").getValue())+";		
		}} */

$race = single("select race from status where id=".$status['id']);
$prot = single("select inprotection from status where id=".$status['id']);
$gebs = assocs('SELECT erforschbar, name_intern FROM buildings', 'name_intern');

if ($prot==N){
	$tpl->assign('ERROR', "Sie sind nichtmehr in der Konfig-Phase!");
	$goon = 0;
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$id = $_POST[id];
	
	
	$gebpoints = $tradecenters + $powerplants + $ressourcefacilities + $sciencelabs + $factories + $buildinggrounds;
	
	if (!(($tradecenters >= 0) && ($powerplants >= 0) && ($ressourcefacilities >= 0) && 
		($sciencelabs >= 0) && ($factories >= 0) && ($buildinggrounds >= 0) && (is_numeric($tradecenters)) &&
		(is_numeric($powerplants)) && (is_numeric($ressourcefacilities)) && (is_numeric($sciencelabs)) && 
		(is_numeric($factories)) && (is_numeric($buildinggrounds))))
		$tpl->assign('ERROR', 'In die Gebäude Felder müssen Zahlen größer null eingetragen werden');
	elseif ($gebpoints > 400){
		$tpl->assign('ERROR', "Sie haben mehr Gebaeude gebaut als möglich.");
	}
	elseif ($gebpoints < 400){
		$tpl->assign('ERROR', "Sie haben noch nicht ihr gesamtes Land mit Gebäuden bebaut.
		Das Bebauen \n ist in der Konfig-Phase kostenlos und sollte daher unbedingt gemacht werden!");
	}
	else {
		select("UPDATE status SET 
				tradecenters = '".$tradecenters."',
				powerplants = '".$powerplants."',
				ressourcefacilities = '".$ressourcefacilities."',
				sciencelabs = '".$sciencelabs."',
				factories = '".$factories."',
				buildinggrounds = '".$buildinggrounds."'
			WHERE id=".$status['id']);
		select("UPDATE status SET inprotection = 'N' WHERE id=".$status['id']);

		// Für die Game-Master-Logs nochmal alles rausholen
		$status = assoc("SELECT * FROM status WHERE id = ".$status['id']);
		$status['nw'] = nw($status['id']);
		select("UPDATE status SET nw = ".$status['nw'].", nw_last_hour = ".$status['nw']." WHERE id = ".$status['id']);
		$log_message = "";
		foreach ($status as $key => $vl)
		{
	  $log_message .= "$key: $vl<br>\n";
		}
		$time = time();
		select("INSERT INTO konfigurationsphase_logs (time, user_id, logmessage) 
			VALUES (".$time.", ".$status['id'].", '".mysql_real_escape_string($log_message)."')");
		$globals = assoc("SELECT * FROM globals ORDER BY round DESC LIMIT 1");
		if ($time < $globals['roundstarttime']) $time = $globals['roundstarttime'];
		select("UPDATE status SET 
			createtime = '".$time."', unprotecttime = '".($time+PROTECTIONTIME)."' WHERE id=".$status['id']);
		
		$tpl->assign('MSG', "Du hast die Konfigurationsphase erfolgreich abgeschlossen. 
			Als nächstes wäre es eventuell sinnvoll Land zu kaufen und eine Forschung zu starten".
			(!$globals['roundstatus'] ? " und <a href=\"gruppen.php\">eine Gruppe zu suchen mit der du zusammenspielst" : "").".<br><br>
			<a class=\"linkAuftableInner\" href=\"forschung.php\">zu den Forschungen</a><br>
			<a class=\"linkAuftableInner\" href=\"gebaeude.php\">zu Gebäude & Land</a><br>
			<a class=\"linkAuftableInner\" href=\"gruppen.php\">zu den Gruppen</a><br>");
		$goon = 0;
		$done = "konfig-abgeschlossen";
	}

}


if ($goon) {

	##Ausgabe Begrüssungstext
	## Ausgabe ProgressBar
	## Ausgabe Land und Gebäude
	
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
	$tpl->assign('LAND_MIN', $status['land']);
	$tpl->assign('LAND_MAX', $status['land']*2);
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
	$tpl->assign('GEB_BAUZEIT', 20 - ($status['race'] == 'uic' ? 20*UIC_BUILDINGS_SPEEDBONUS : 0));

	$buildingstats_output = array(); $vl = array();
	foreach ($buildingstats as $key => $value) { //Gebäudebauformular
		if($buildingstats{$key}{erforschbar} == null) {
			$vl['name'] = $value[name];
			$vl['getBuildingTooltip'] = getBuildingTooltip($value, 1);
			$vl['intverbrauch'] = $value[intverbrauch];
			$vl['key'] = $key;
			$vl['value'] = ($_POST[$key] ? $_POST[$key] : 0);
			array_push($buildingstats_output, $vl);
			unset($vl);
		}
	}

}
$tpl->assign('buildingstats', $buildingstats_output);
$tpl->assign('status', $status);
$tpl->assign('ripf', $ripf);

#################################################################################
## Dateispezifische Funktionen
###############################################################################


//**************************************************************************
//                                                      Header, Ausgabe, Footer
//**************************************************************************
require_once("../../inc/ingame/header.php");

if (!$noMsg && $tpl->get_template_vars('MSG') != '') {
	$tpl->display('sys_msg.tpl');
}
if (!$noError && $tpl->get_template_vars('ERROR') != '') {
	$tpl->display('fehler.tpl');
}
/*if (!$noInfo && $tpl->get_template_vars('INFO') != '') {
	$tpl->display('info.tpl');
}*/
$tpl->assign('GOON', $goon);
$tpl->display('configseite.tpl');
require_once("../../inc/ingame/footer.php");
?>
