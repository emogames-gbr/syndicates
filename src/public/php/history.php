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

if ($features[ANGRIFFSDB] ) {
	$tpl->assign('ANGRIFFSDB', true);
	$jetzttag = date("d", $time);
	$jetztmonat = date("m", $time);
	$jetztjahr = date("Y", $time);
	$starttag = date("d",$globals[roundstarttime]);
	$startmonat = date("m", $globals[roundstarttime]);
	$startjahr = date("Y", $globals[roundstarttime]);
	
	
	($vonjahr >= 2003 && $vonjahr <= 2010) ? $vonjahr = (int) $vonjahr : $vonjahr = $startjahr;
	($vonmonat >= 1 && $vonmonat <= 12) ? $vonmonat = (int) $vonmonat : $vonmonat = $startmonat;
	($vontag >= 1 && $vontag <= 31) ? $vontag = (int) $vontag : $vontag = $starttag;
	($bisjahr >= 2003 && $bisjahr <= 2010) ? $bisjahr = (int) $bisjahr : $bisjahr = $jetztjahr;
	($bismonat >= 1 && $bismonat <= 12) ? $bismonat = (int) $bismonat: $bismonat = $jetztmonat;
	($bistag >= 1  && $bistag <= 31)? $bistag = (int) $bistag: $bistag = $jetzttag;
	
	
	
	
	$vontime = ctime($vonjahr, $vontag, $vonmonat);
	$bistime = ctime($bisjahr, $bistag, $bismonat) + 24 * 60 * 60; //0:00 Am Forlgetag, damit Zeit inklusive angezeigt wird
	
	$stdlink = "vontag=$vontag&vonmonat=$vonmonat&vonjahr=$vonjahr&bistag=$bistag&bisjahr=$bisjahr&bismonat=$bismonat&selectview=$view";
	
	$spyactions_settings = assocs("select * from spyaction_settings","action_key");
	$spysettings = assocs("select * from spy_settings where race='$status[race]'","unit_id");
	
	//**************************************************************************//
	//**************************************************************************//
	//							Eigentliche Berechnungen!						//
	//**************************************************************************//
	//**************************************************************************//
	
	
	//							selects fahren									//
	
	//							Berechnungen									//
	
	//							Daten schreiben									//
	
	//							Ausgabe     									//
	
	$tpl->assign('VONTAG', $vontag);
	$tpl->assign('VONMONAT', $vonmonat);
	$tpl->assign('VONJAHR', $vonjahr);
	$tpl->assign('BISTAG', $bistag);
	$tpl->assign('BISMONAT', $bismonat);
	$tpl->assign('BISJAHR', $bisjahr);
	
	$tpl->assign('STATUS', $status);
	$tpl->assign('STDLINK', $stdlink);
	
	
	//**************************//
	// Standardausgabe
	//**************************//
	
	
	if (!$view) {
		$tpl->assign('SELECTVIEW', $selectview);
	}
	
	
	
	//**************************//
	// Detailausgabe einer Spionageaktion
	//**************************//
	if ($view == "spydetails") {
		if ($logid) {
			$log = assoc("select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where id=$logid and log_id=$logid");
			if ($log[aid] == $status[id]) {
	
				// Ergebnis zusammenbasteln
	
	
				// Ergebnis zusammenbasteln ende
	
				$target = assoc("select syndicate,rid,id from status where id=$log[did]");
				
				$tpl->assign('SPYACTIONSNAME', $spyactions_settings[$log[action]][name]);
				$log['o_time'] = mytime($log[time]);
				$tpl->assign('TARGET', $target);
				$tpl->assign('LOG', $log);
			}
			else {
				f("Sie können nur Datensätze von Ihrem Konzern auswählen.");
				$view = $backview;
			}
		}
		else {
			f("Konnte Datensatz nicht finden");
			$view = $backview;
		}
	}
	
	//**************************//
	// Detailausgabe eines Angriffs
	//**************************//
	if ($view == "attackdetails") {
		if ($logid) {
			$log = assoc("select aid, did, bericht, type, time from attacklogs where id=$logid and aid = $id");
			if ($log[aid] == $status[id]) {
	
				// Ergebnis zusammenbasteln
	
	
				// Ergebnis zusammenbasteln ende
	
				$target = assoc("select syndicate,rid,id from status where id=$log[did]");
				$tpl->assign('ATTACKTYPE', getattacktype($log[type]));
				$log['o_time'] = mytime($log[time]);
				$tpl->assign('TARGET', $target);
				$tpl->assign('LOG', $log);
			}
			else {
				f("Sie können nur Datensätze von Ihrem Konzern auswählen.");
				$view = $backview;
			}
		}
		else {
			f("Konnte Datensatz nicht finden");
			$view = $backview;
		}
	}
	
	
	//**************************//
	// ?bersicht, ausgef?hrt Spionageaktionen
	//**************************//
	
	
	if ($view == "spyactions") {
	
		$spyactions = assocs("select * from spylogs where aid=$status[id] and time >=$vontime and time <= $bistime order by time desc"); // limit 100 weg
		$target_ids = array();
		if (is_array($spyactions)) {
			foreach ($spyactions as $temp) {
				$target_ids[] = $temp[did];
			}
		}
	
		if (count($target_ids) > 0) {
			$targets = assocs("select rid,syndicate,id from status where id in (".(implode(",",$target_ids)).")","id");
		}
		if (count($spyactions) > 0) {
			$spyactions_output = array();
			foreach ($spyactions as $key => $value) {
				$value['o_time'] = mytime($value['time']);
				$value['o_syndicate'] = $targets[$value['did']]['syndicate'];
				$value['o_rid'] = $targets[$value['did']]['rid'];
				$value['o_actionName'] = $spyactions_settings[$value[action]][name];
				array_push($spyactions_output, $value);
			}
			$tpl->assign('SPYACTIONS', $spyactions_output);
		}
		else {
			// keine Spionageaktionen in dem Zeitraum ausgeführt
		}
	}
	
	
	//**************************//
	// ?bersicht ausgef?hrte Angriffe
	//**************************//
	
	
	if ($view == "attacks") {
	
		$data = assocs("select id, type, did, drid, drace, time, winner, ginactive, gbprot, warattack, dland, landgain, landgrab, bericht from attacklogs where aid=$status[id] and time >=$vontime and time <= $bistime order by time desc limit 100");
		$target_ids = array();
		if (is_array($data)) {
			foreach ($data as $temp) {
				$target_ids[] = $temp[did];
			}
		}
	
		if (count($target_ids) > 0) {
			$targets = assocs("select rid,syndicate,id from status where id in (".(implode(",",$target_ids)).")","id");
		}
	
		if (count($data) > 0) {
			$data_output = array();
			foreach ($data as $key => $value) {
				$value['o_time'] = mytime($value['time']);
				$value['o_syndicate'] = $targets[$value['did']]['syndicate'];
				$value['o_rid'] = $targets[$value['did']]['rid'];
				$value['o_attackType'] = getattacktype($value['type']);
				array_push($data_output, $value);
			}
			$tpl->assign('DATA', $data_output);
		}
		else {
			// Keine Angriffe in dem Zeitraum ausgeführt
		}
	}
	

} // Wenn feature freigeschaltet

else { 
	$userdata = assoc("select * from users where konzernid=$status[id]");
	$loginkey = createkey($userdata[emogames_user_id]);
	EMOGAMES_prepare_Login($userdata[emogames_user_id],$loginkey);
	f("Um die Spionagedatenbank benutzen zu können, müssen Sie zuerst das <u><a href=\"$game[emogames_portal_address]/index.php?action=features&loginkey=$loginkey\" target=\"_blank\" class=\"linkAufsiteBg\">\"Angriffs- und Spionagedatenbank\"-Premium-Feature</a></u> freischalten lassen.");
	i("In der Angriffs- und Spionagedatenbank werden alle Angriff und Spionageaktionen, die Sie ausgeführt haben automatisch gespeichert. Sie ermöglicht die gezielte Recherche über getätigte Aktionen.");
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('VIEW', $view);

require_once("../../inc/ingame/header.php");
$tpl->display('history.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function ctime($year,$day,$month) {
	global $time;

	$minutes = "00";
	$seconds = "00";
	$hours = "00";
	$uebersetzt = "$year-$month-$day $hours:$minutes:$seconds";
	$timestamp = strtotime($uebersetzt);
	return $timestamp;

}

	function getattacktype($type) {
		if ($type == "1"): return "Standard";
		elseif ($type == "2"): return "Belagerung";
		elseif ($type == "3"): return "Eroberung";
		elseif ($type == "4"): return "Spione zerstören";
		endif;
		return FALSE;
	}


?>
