<?

/**
* @todo:
* 1. Keine Noobsyns [DONE]
* 2. Nur Presi darf bau starten und nur wenn wunder nicht gerade existiert und wenn syn nicht schon ein wunder hat [DONE]
* 2.5. Startbaupreis liegt bei 50k fp, damit nicht jeder einfach so mal ein wunder startet... [DONE - DAFÜR REGEL 9]
* 4. funktion zum preis berechnen [DONE]
* 5. towncrieer message bei baustart [DONE]
* 6. pauschalübersicht wer welches wunder hat [DONE]

-----
* 6. update ressourcen investieren
* 7. in syndikatsübersicht wunder anzeigen
* 8. wenn wunder abgeschlossen, andere bauaufträge für wunder löschen, syn wunder zuweisen
*/



//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//

$wert ? $wert = 1 : $wert = 0;
$artefakt_id = floor($artefakt_id);

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//
DEFINE(TESTING,1); // BEI TESTING = 1 ARTEFAKTE auch in anfängersyns möglich

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

// Header include
require_once("../../inc/ingame/header.php");

##
## Nicht auf Basic Servern verfügbar
##
if (isBasicServer($game)) {
	exit("Dieses Spieleelement ist auf diesem Server nicht vorhanden!");
}



if ($game_syndikat[synd_type] != "normal" && !TESTING) {
	$action = "";
	$view = "";
}


//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$queries = array();
$syns_mit_wunder = assocs("select * from syndikate where artefakt_id != 0","artefakt_id"); // Alle Syns mit Monu 
$artefakte_in_bau = singles("select distinct(artefakt_id) from build_artefakte"); // Alle Monus in Bau
$syn_baut = assoc("select * from build_artefakte where synd_id = $game_syndikat[synd_id]"); // eigener Monubau
//$duty_time=single("select duty from build_artefakte where synd_id = $game_syndikat[synd_id]");
$tpl->assign("DUTY_TIME", $syn_baut['duty']); //$duty_time

/* //Unterhaltskosten für Monus Togglen
$syn_has_monu = single("select artefakt_id from syndikate where synd_id = $game_syndikat[synd_id]");
if($syn_has_monu && $game_syndikat[president_id] == $status[id]){
	$weeks_played = ceil((round_days_played()+1)/ 7); //Tunnelfuchs Monument
	$costs = (ARTEFAKT_COST_PER_DAY_PER_WEEK * $weeks_played);
	if($_GET['action']=='toggleCosts'){
		select("update syndikate set artefakt_store=1-artefakt_store where synd_id = $game_syndikat[synd_id]");
	}
	$syn_has_monu_support = single("select artefakt_store from syndikate where synd_id = $game_syndikat[synd_id]");
	if($syn_has_monu_support){
		$info='Ihr Syndicate unterhält zur Zeit ein Monument. Die Unterhaltskosten betragen <b>'.pointit($costs).' Credits</b> <br>
		und werden aus dem Lager beglichen.<br>
		<a href="?action=toggleCosts">Unterhaltszahlungen deaktivieren</a> (Achtung: Sollte heute um 24 Uhr keine Unterhaltszahlung geleistet werden,<br>
		verliert ihr Syndicate das Monument!)';
	} else {
		$info='Ihr Syndicate unterhält zur Zeit ein Monument. Die Unterhaltskosten betragen <b>'.pointit($costs).' Credits</b> <br>
		und sind zur Zeit deaktiviert, ihr Monument wird heute um 24 Uhr zerstört!<br>
		<a href="?action=toggleCosts">Unterhaltszahlungen aktivieren</a>';
	}
	$tpl->assign("INFO", $info);
	$tpl->display("info.tpl");
}*/

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

//							Berechnungen									//

###
###	ACTION == BUILD
###


if ($action == "build" && $artefakt_id) {
	$errors = 0;
	if ($syns_mit_wunder[$artefakt_id]) {
		$errors++;
		$beschr = "Dieses Monument wurde bereits von einem anderen Syndikat errichtet!";
		$tpl->assign("ERROR", $beschr);
		$tpl->display("fehler.tpl");
	}
	if ($game_syndikat[artefakt_id] != 0) {
		$errors++;
		$beschr = "Ihr Syndikat besitzt bereits ein Monument! Jedes Syndikat kann nur ein Monument besitzen.";
		$tpl->assign("ERROR", $beschr);
		$tpl->display("fehler.tpl");
	}
	if ($syn_baut[artefakt_id]) {
		$errors++;
		$beschr = "Ihr Syndikat ist bereits mit der Errichtung eines Monuments beschäftigt!";
		$tpl->assign("ERROR", $beschr);
		$tpl->display("fehler.tpl");
	}
	if(start_sperre()){
		$errors++;
		$tpl->assign("ERROR", "Der Bau eines Monuments kann erst 24 Stunden nach Rundenstart begonnen werden.");
		$tpl->display("fehler.tpl");
	}
	

	/**
	*@todo Nachreichten towncrier*
	*/

	// Keine Fehler - Bau starten
	if ($errors == 0) {
		
		// Towncrier Eintrag
		$syndikate_ids = singles("select synd_id from syndikate where synd_type='normal'");
		$message = "
			Das Syndikat <b>".$game_syndikat[name]." (#".$game_syndikat[synd_id].")</b> hat mit dem Bau des Monumentes ".bold($artefakte[$artefakt_id][name])." begonnen.
		";
		towncrier($syndikate_ids, $message, $execute = 0,3);
		
		// Nachricht an Twitter schicken - R4bbiT - 08.03.11
		tweet('monu_start', array('s_name' => $game_syndikat[name], 's_rid' => $game_syndikat[synd_id], 'a_name' => $artefakte[$artefakt_id][name]));
		
		// DB EINTRAG
		$queries[] = ("insert into build_artefakte (artefakt_id,synd_id) values (".$artefakt_id.",".$game_syndikat[synd_id].") ");
		
		// ERFOLG
		$beschr = "Ihr Syndikat hat mit der Errichtung des Monumentes ".bold($artefakte[$artefakt_id][name])." begonnen.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
		$artefakte_in_bau[] = $artefakt_id; // Für Anzeige später updaten
	}
}



###
###	ACTION == BETEILIGUNG
###
if ($action == "beteiligung") {
	if ($wert == 0) {
		$beschr = "Ihr Konzern wird sich nicht länger am Bau von Monumenten beteiligen. Ihnen werden keine Forschungspunkte mehr abgezogen.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
		$status[invest_in_artefakt] = 0;
		select("update status set invest_in_artefakt=0 where id = $status[id]");
	}
	elseif ($wert == 1) {
		$beschr = "Ihr Konzern wird sich in Zukunft am Bau von Monumenten beteiligen. Wenn Ihr Syndikat mit der Errichtung eines Monuments beschäftigt ist, werden Ihnen pro Zug <b>".pointit(BUCHUNGSBETRAG_TICK)." Forschungspunkte</b> von Ihrem Konto abgebucht";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
		$status[invest_in_artefakt] = 1;
		select("update status set invest_in_artefakt=1 where id = $status[id]");
	}
}




//							Daten schreiben									//

//							Ausgabe     									//



#
#
#	VIEW == BUILD
#
#

$building_prohibited = single("select artefakt_wait from syndikate where synd_id = $game_syndikat[synd_id]");

if ($view == "build" && $artefakt_id) {
	if(start_sperre()){
		$tpl->assign("ERROR", "Der Bau eines Monuments kann erst 24 Stunden nach Rundenstart begonnen werden.");
		$tpl->display("fehler.tpl");
	} elseif ($building_prohibited>0) {
		$tpl->assign("ERROR", "Der Bau eines Monuments ist aufgrund eines Monument-Bauabbruchs in den letzten 3 Tagen zur Zeit nicht möglich.");
		$tpl->display("fehler.tpl");
	} else{
		$beschr = "
			Wollen Sie das Monument <b>".$artefakte[$artefakt_id][name]."</b> wirklich errichten ?<br><br>
			<li>Der Bau des Monuments kann nich abgebrochen werden!<br>
			<li>Stellt ein anderes Syndikat das Monument, an dem Sie bauen zuerst fertig, sind alle in den Bau investierten Ressourcen unwiderbringlich verloren!<br><br>
			<center>
				$greendot <a href=\"monumente.php?action=build&artefakt_id=$artefakt_id\">JA - Mit dem Bau beginnen</a><br><br><br>
				$reddot	<a href=\"monumente.php\">Nein - lieber doch nicht</a>
			</center>
		";
		$tpl->assign("INFO", $beschr);
		$tpl->display("info.tpl");
	}
}


#
#
#	NO VIEW
#
#


if (!$view) {
	####
	####	AUSGABE übersicht artefakte
	####
	
	if($game_syndikat[president_id] == $status[id] && ! $syn_baut[artefakt_id]) {
		$tpl->assign("IS_PRESI_AND_NO_MONU", 1);
	}
	
				$syns_bauen_monu = array();
				foreach ($artefakte as $temp) {
						
						if ($syns_mit_wunder[$temp[artefakt_id]]) {
							$tstatusbuild = 1;
							$temp[status] = 0;
							$synwithmonuname = $syns_mit_wunder[$temp[artefakt_id]][name];
							$synwithmonuid = $syns_mit_wunder[$temp[artefakt_id]][synd_id];						
						}
						elseif (in_array($temp[artefakt_id],$artefakte_in_bau)) {
							$tstatusbuild = 2;
							$temp[status] = 1;
							$syns_bauen_monu = assocs("SELECT s.name, ba.synd_id FROM build_artefakte AS ba,syndikate AS s 
								WHERE ba.synd_id=s.synd_id AND ba.artefakt_id = ".$temp[artefakt_id]);
						}
						else {
							$tstatusbuild = 0;
							$temp[status] = 2;
						}
						
						## Presi aktion
						if($game_syndikat[president_id] == $status[id] && ! $syn_baut[artefakt_id]) {
							if ($temp[status] != 0 ) {
								$buildable = 1;
							}
						}
						if(start_sperre()){
							$buildable = 0;
						}
						
						$monu = array(
							"notfree"=>$tstatusbuild,
							"owner_name"=>$synwithmonuname,
							"owner_id"=>$synwithmonuid,
							"buildlink"=>"monumente.php?view=build&artefakt_id=".$temp[artefakt_id],
							"buildable"=>$buildable,
							"buildsyns_count" => count($syns_bauen_monu),
							"buildsyns" => $syns_bauen_monu,
							"name" => $temp[name],
							"descr" => $temp[bonusdescription]
						);
						$monus[] = $monu;
						unset($tstatusbuild, $synwithmonuname, $synwithmonuid, $buildable, $syns_bauen_monu);
					}	
	$tpl->assign("ALL_MONUS",$monus);
	####
	####	AUSGABE BETEILIGUNG
	####
	
	$tpl->assign("INVEST_IN_IT", $status[invest_in_artefakt]);
	
	####
	####	AUSGABE SYN_BAUT
	####
	if ($syn_baut[artefakt_id]) {
		$tpl->assign("MONU_IN_BUILD", $artefakte[$syn_baut[artefakt_id]][name]);
		$tpl->assign("SYNBUILD", 1);
		$tpl->assign("INVESTET_P", pointit($syn_baut[invested]));
		$tpl->assign("COST", pointit(KOSTEN_ARTEFAKT));
	}
	if(start_sperre()){
		$tpl->assign("STARTSPERRE", 1);
	}
}

##
##	Änfängersyndikate können keine Wunder bauen
##
if ($game_syndikat[synd_type] != "normal" && !TESTING) {
		$beschr = "Für Anfängersyndikate ist das Spielelement <b>Monumente</b> nicht verfügbar.";
		$tpl->assign("ERROR", $beschr);
		$tpl->display("fehler.tpl");
}

db_write($queries);

//**************************************************************************//
//								Ausgabe, Footer								//
//**************************************************************************//

	echo $javascr;
	$tpl->display('monumente.tpl');
require_once("../../inc/ingame/footer.php");
 

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function start_sperre(){
	global $globals, $time;
	if($time < $globals['roundstarttime']+60*60*24){
		return true;
	}
	else{
		return false;
	}
}

?>
