<?


//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

// Rundendauer
$days_since_roundstart = ceil((get_day_time($time) - get_day_time($globals[roundstarttime])) / 86400) + 1;
//$days_since_roundstart <= 0 ? $days_since_roundstart = 0 : 1;
$max_days_roundlength = ceil((get_day_time($globals[roundendtime]) - get_day_time($globals[roundstarttime])) / 86400);


// Kriegsprämie
$kriegspraemienfaktor = KRIEGSPRAEMIE_TAGFAKTOR * $days_since_roundstart;
$kriegspraemienfaktor <= 0 ? $kriegspraemienfaktor = 0 : 1;

// Sabotage
$game_syndikat = assoc("select * from syndikate where synd_id=$status[rid]");
$artefakte = get_artefakte();

// Spielzeit
$weeks_played_fuer_waffenlager = ceil((round_days_played()+1)/ 7); // Sonderbehandlung für Waffenlager, da diese im Update auch anders gehandhabt werden
$weeks_played = ceil($days_since_roundstart / 7);
$weeks_max = ceil($max_days_roundlength / 7);

//Gebäude
$buildings = assocs("select name, nw from buildings order by name asc");
$units = assocs("select name, nw from military_unit_settings order by race, unit_id asc");
$spies = assocs("select distinct name, nw from spy_settings order by name asc");


$spy_aktionen = assocs("select action_key,name,type,difficulty from spyaction_settings","action_key"); // Daten zu Spionageaktionen holen

// "Ausgabe"

$tpl->assign("DAYSPLAYED", $days_since_roundstart - 1);
$tpl->assign("ROUNDSTARTTIME", date("l, d. F Y, H:i:s", $globals[roundstarttime]));
$tpl->assign("ROUNDENDTIME", date("l, d. F Y, H:i:s", $globals[roundendtime]));
$tpl->assign("DAYSTOROUNDEND", $max_days_roundlength -$days_since_roundstart + 1);
$tpl->assign("MAX_DAYS_ROUNDLENGTH", $max_days_roundlength);
$tpl->assign("MAXSCHULDENAKTUELL", pointit(max(1, ceil((round_days_played()+1)/ 7)) * MAX_SCHULDENSATZ_PRO_WOCHE));
$tpl->assign("MAXSCHULDENINSGESAMMT", pointit(ceil(($globals['roundendtime'] - $globals['roundstarttime']) / 86400 / 7) * MAX_SCHULDENSATZ_PRO_WOCHE));
$tpl->assign("KRIEGSPRAEMIENFAKTOR", pointit($kriegspraemienfaktor));
$tpl->assign("MAXKRIEGSPRAEMIENFAKTOR", pointit($max_days_roundlength * KRIEGSPRAEMIE_TAGFAKTOR));

// Spionage


// SPIONAGEAKTIONEN
$steal = array(
	array(key => 'getmoney', name => $spy_aktionen['getmoney']['name'], maxget => STEAL_MAX_CREDITS,
		offspies => STEAL_OS_CREDITS, defspies => STEAL_ISDS_CREDITS, intelspies => STEAL_ISDS_CREDITS), 
	array (key => 'getmetal', name => $spy_aktionen['getmetal']['name'], maxget => STEAL_MAX_METAL,
		offspies => STEAL_OS_METAL, defspies => STEAL_ISDS_METAL, intelspies => STEAL_ISDS_METAL),
	array (key => 'getenergy', name => $spy_aktionen['getenergy']['name'], maxget => STEAL_MAX_ENERGY,
		offspies => STEAL_OS_ENERGY, defspies => STEAL_ISDS_ENERGY, intelspies => STEAL_ISDS_ENERGY),
	array (key => 'getsciencepoints', name => $spy_aktionen['getsciencepoints']['name'], maxget => STEAL_MAX_SCIENCEPOINTS, 
		offspies => STEAL_OS_SCIENCEPOINTS, defspies => STEAL_ISDS_SCIENCEPOINTS, intelspies => STEAL_ISDS_SCIENCEPOINTS),
	array (key => 'killunits', name => $spy_aktionen['killunits']['name'], maxget => calcSpylossMax(0,"mil"), damagetime => 60*60*12,
		offspies => KILL_OS_KILLUNITS, defspies => KILL_ISDS_KILLUNITS, intelspies => KILL_ISDS_KILLUNITS),
	array (key => 'killbuildings', name => $spy_aktionen['killbuildings']['name'], maxget=>calcSpylossMax(0),
		offspies => KILL_OS_KILLBUILDINGS,defspies => KILL_ISDS_KILLBUILDINGS,intelspies => KILL_ISDS_KILLBUILDINGS)
			);
$steal_output = array();
$cabonus = GLO16BONUS * $sciences['glo16']; // CA Bonus
$artefakt_id = $game_syndikat[artefakt_id]; // Monument des eigenen Syndikats
if ($artefakte[$artefakt_id][bonusname] == "spy_damagecap_bonus") { // Falls Jungbrunnen
	$cabonus += $artefakte[$artefakt_id][bonusvalue];
}
$cabonusatwar = $cabonus + WAR_CAPACITY_STEAL_BONUS;
foreach ($steal as $temp) {
	$temp['maxget_atwar'] = $temp['maxget'] * (1 + $cabonusatwar/100);
	if ($sciences['glo16']) {
		$temp['maxget'] *= (1 + $cabonus/100);
	}
	array_push($steal_output, $temp);
}
$tpl->assign("STEAL", $steal_output);


// Fox
$fox = assocs("select avg(nw / land) as fox, race from status group by race", "race");
$races = assocs("select race, name from races where active = 1", "race");

$fox_output = array(); $vl = array();
foreach ($fox as $race => $val) {
	$vl['name'] = $races[$race]['name'];
	$vl['fox'] = number_format($val['fox'], 1, ",", ".");
	array_push($fox_output, $vl);
	unset($vl);
}
$tpl->assign('FOX', $fox_output);
$tpl->assign("RIPF", $ripf);

// NW - Gebäude
$buildings_output = array();
foreach($buildings as $b){
	$b['o_nw'] = sprintf('%2.1f', $b["nw"]);
	array_push($buildings_output, $b);	
}
$tpl->assign('BUILDINGS', $buildings_output);			

// NW - Forschungen
$fos_output = array(); $vl = array();
for($i=1; $i<=7; $i++){
	$vl['stufe'] = $i;
	$vl['o_nw'] = pointit(constant("NW_FOS_LVL".$i));
	array_push($fos_output, $vl);
	unset($vl);
}
$tpl->assign('FOS', $fos_output);
			
// NW - Land, Aktien
$tpl->assign('NW_LAND', sprintf('%2.1f', NW_LAND));
$tpl->assign('NW_AKTIEN', NW_AKTIEN);

//playonline
$tpl->assign('PLAYER_ONLINE', getPlayerOnline());

// NW - Mili-Units
$units_output = array();
foreach($units as $u){
	if ($u['name'] != 'BUC' && $u['name'] != 'AUC' && $u['name'] != 'HUC') {
		$u['o_nw'] = sprintf('%2.1f', $u["nw"]);
		array_push($units_output, $u);
	}
}
$tpl->assign('UNITS', $units_output);

// NW - Spy-Units
$spies_output = array();
foreach($spies as $s){
	$s['o_nw'] = sprintf('%2.1f', $s["nw"]);
	array_push($spies_output, $s);	
}
$tpl->assign('SPIES', $spies_output);

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//


require_once("../../inc/ingame/header.php");
$tpl->display('gamevalues.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
