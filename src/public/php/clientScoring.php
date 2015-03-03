<?php

//echo "Umfrage beendet.";
//exit();


require_once("../../inc/ingame/game.php");

define('CLIENT_SCORING_ENDTIME', strtotime('2012-02-21 19:00'));
$show=true; //!$_POST;
$error=0;
// Checkbox zurücksetzen
$login_times=0;
$play_reason=0;
$future_am = 0;
$future_ticker = 0;
$count=0;
$elemente_anzahl = 6; // Anzahl der verwendeten Fragen
$statusId=$status['id'];
$doSql="";

if(single("select COUNT(*) from clientScoring where id=$statusId")){
	$tpl->assign('ERROR', "Du hast bereits an der Befragung teilgenommen.");
	$error=1;
}

if($_GET['stop']==1){
	select("insert into clientScoring (id,comment) values ($statusId,'stop')");
	$tpl->assign('ERROR', "Die Befragung wurde für dich deaktiviert.");
	$error=1;
}

if (time() > CLIENT_SCORING_ENDTIME) {
	$tpl->assign('ERROR', "Im Moment gibt es keine Umfrage.	");
	$error = 1;
}

if ($_POST && !$error) {
	$sql = "insert into clientScoring (id,";
	$val = ") values (".$statusId.",";
	foreach($_POST as $tag=>$value){
		$value = mysql_real_escape_string($value);
		if(strpos($tag, "ogin_times")!=0){
			$login_times +=$value;
		} elseif(strpos($tag, "lay_reason")!=0){
			$play_reason +=$value;
		} elseif (strpos($tag, "uture_ticker") != 0) {
			$future_ticker +=$value;
		} elseif (strpos($tag, "uture_am") != 0) {
			$future_am +=$value;
		} else if($tag=="submit"){
			//nothing
			$sql = $sql."login_times,play_reason,future_ticker,future_am";
			$val = $val.$login_times.",".$play_reason.",".$future_ticker.",".$future_am.")";
			$doSql = $sql.$val;
		} else {
			$sql = $sql.$tag.",";
			$val = $val."'$value',";
			$count++;
		}
	}

	if($count<$elemente_anzahl){
		$tpl->assign('ERROR', "Bitte jede Frage beantworten <a href=\"javascript:history.back()\">Zurück</a>");
		$error = 1;
	} else{
		select($doSql);
		$tpl->assign('MSG', "Vielen Dank für deine Teilnahme!<br /><br />Dein Syndicates-Team");
		$success = 1;
	}
}
elseif($show && !$error){
	
	$tpl->assign('INFO', "Wir, das Syndicates-Team würden uns freuen wenn du dir kurz 2-3 Minuten Zeit nimmst 
		und an dieser Umfrage teilnimmst.");
	
	/*
	 * 		Radiobuttons
	 */
	
	$radio_output = array();
	$temp = array();	
	// Geschlecht
	array_push($temp, array('vl' => 1, 'answer' => "M&auml;nnlich"));
	array_push($temp, array('vl' => 2, 'answer' => "Weiblich"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array('question' => "Geschlecht", 'name' => "gender", 'answer' => $temp));
	$temp = array();
	
	// Altersgruppe
	array_push($temp, array('vl' => 1, 'answer' => "0-18 Jahre"));
	array_push($temp, array('vl' => 2, 'answer' => "18-25 Jahre"));
	array_push($temp, array('vl' => 3, 'answer' => "25-50 Jahre"));
	array_push($temp, array('vl' => 4, 'answer' => "50+ Jahre"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array('question' => "Altersgruppe", 'name' => "age", 'answer' => $temp));
	$temp = array();
	
	// Reallife Tätigkeit
	array_push($temp, array('vl' => 1, 'answer' => "...Sch&uuml;ler"));
	array_push($temp, array('vl' => 2, 'answer' => "...Student"));
	array_push($temp, array('vl' => 3, 'answer' => "...erwerbst&auml;tig"));
	array_push($temp, array('vl' => 4, 'answer' => "sonstiges"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array('question' => "Ich bin...", 'name' => "milieu", 'answer' => $temp));
	$temp = array();
	
	// Investierte Zeit
	array_push($temp, array('vl' => 1, 'answer' => "...h&ouml;chstens 1-2 Stunden..."));
	array_push($temp, array('vl' => 2, 'answer' => "...bis zu 5 Stunden..."));
	array_push($temp, array('vl' => 3, 'answer' => "...mehr als 5 Stunden..."));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array(
		'question' => "Ich investiere ... t&auml;glich in Syndicates", 'name' => "time_spending", 'answer' => $temp));
	$temp = array();
	
	// Die Spielweise (Zukunft)
	array_push($temp, array('vl' => 1, 'answer' => "...aggressiver werden"));
	array_push($temp, array('vl' => 2, 'answer' => "...friedlicher werden"));
	array_push($temp, array('vl' => 3, 'answer' => "...so bleiben"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array(
		'question' => "Die Spielweise in Syndicates sollte...", 'name' => "feature_direction", 'answer' => $temp));
	$temp = array();
	
	/*
	// Die Gruppengröße (Zukunft)
	array_push($temp, array('vl' => 1, 'answer' => "...gr&ouml;&szlig;er werden"));
	array_push($temp, array('vl' => 2, 'answer' => "...kleiner werden"));
	array_push($temp, array('vl' => 3, 'answer' => "...so bleiben"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array(
		'question' => "Die Gruppengr&ouml;&szlig;e soll...", 'name' => "feature_groupsize", 'answer' => $temp));
	$temp = array();
	
	// Die Syndikatsgröße (Zukunft)
	array_push($temp, array('vl' => 1, 'answer' => "...gr&ouml;&szlig;er werden"));
	array_push($temp, array('vl' => 2, 'answer' => "...kleiner werden"));
	array_push($temp, array('vl' => 3, 'answer' => "...so bleiben"));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array(
		'question' => "Die Syndikatsgr&ouml;&szlig;e soll...", 'name' => "feature_synsize", 'answer' => $temp));
	$temp = array(); */
	
	// Der Global Market
	array_push($temp, array('vl' => 1, 'answer' => "Darauf hab ich schon ewig gewartet, danke!"));
	array_push($temp, array('vl' => 2, 'answer' => "Die Vorauswahl von Verkaufen ist ärgerlich, bitte entfernt diese."));
	array_push($temp, array('vl' => 3, 'answer' => "Ich fände es besser, wenn ich über den Abschickenbutton auswählen kann, ob ich Verkaufe oder Einkaufe"));
	array_push($temp, array('vl' => 4, 'answer' => "Ich fände es besser, wenn es zwei getrennte Formulare gibt."));
	array_push($temp, array('vl' => 5, 'answer' => "Bitte teilt die Angebote wieder so auf wie früher, ich war damit glücklicher und kann mir nicht vorstellen, dass ich mich an die aktuelle Implementierung gewöhne."));
	array_push($temp, array('vl' => 0, 'answer' => "keine Angaben"));
	
	array_push($radio_output, array(
		'question' => "Was haltet ihr von der Vereinigung der Gebote am Global Market in der vorletzten Runde?", 
		'name' => "gm_assi", 'answer' => $temp));
	$temp = array();
	
	$tpl->assign('RADIO', $radio_output);
	
	/*
	 * 		Checkboxen
	 */
	
	$checkbox_output = array();
	
	// Loginzeiten
	array_push($temp, array('vl' => 1, 'answer' => "0-6 Uhr"));
	array_push($temp, array('vl' => 2, 'answer' => "6-10 Uhr"));
	array_push($temp, array('vl' => 4, 'answer' => "10-16 Uhr"));
	array_push($temp, array('vl' => 8, 'answer' => "16-24 Uhr"));
	
	array_push($checkbox_output, array(
		'question' => "Ich logge mich regelm&auml;&szlig;ig zu diesen Zeiten ein", 'name' => 'login_times', 'answer' => $temp));
	$temp = array();
	
	// Spielgrund für Syndicates
	array_push($temp, array('vl' => 1, 'answer' => "...um Spa&szlig; zu haben"));
	array_push($temp, array('vl' => 2, 'answer' => "...um erfolgreich im Ranking abzuschneiden"));
	array_push($temp, array('vl' => 4, 'answer' => "...mit Bekannten/Freunden"));
	array_push($temp, array('vl' => 8, 'answer' => "...zum Zeitvertreibt"));
	array_push($temp, array('vl' => 16, 'answer' => "...aus anderen Gr&uuml;nden"));
	
	array_push($checkbox_output, array(
		'question' => "Ich spiele Syndicates ...", 'name' => 'play_reason', 'answer' => $temp));
	$temp = array();
	
	// Zukunft des Auftragsmarktes
	array_push($temp, array('vl' => 1,  'answer' => "Er soll komplett abschafft werden."));
	array_push($temp, array('vl' => 2,  'answer' => "Er soll so bleiben wie er ist."));
	array_push($temp, array('vl' => 4,  'answer' => "Die Landdezimierung ist überflüssig."));
	array_push($temp, array('vl' => 8,  'answer' => "Ohne Standardaufträge ist der Auftragsmarkt überflüssig."));
	array_push($temp, array('vl' => 16, 'answer' => "Standardaufträge bei Racherecht halte ich für sinnvoll."));
	array_push($temp, array('vl' => 32, 'answer' => "Die Preise für Spionageaktionen (Aufklärung) sind zu niedrig."));
	
	array_push($checkbox_output, array(
		'question' => "Was haltet ihr vom Auftragsmark?", 'name' => 'future_am', 'answer' => $temp));
	$temp = array();
	
	// Zukunft des Tickers
	array_push($temp, array('vl' => 1,  'answer' => "wieder wie früher einblenden"));
	array_push($temp, array('vl' => 2,  'answer' => "auf die Statusseite beschränken"));
	array_push($temp, array('vl' => 4,  'answer' => "auf eine eigene Seite verfrachten, als eine Art \"globaler Chat\""));
	array_push($temp, array('vl' => 8,  'answer' => "komplett weg damit (wie im Moment)"));
	
	array_push($checkbox_output, array(
		'question' => "Der Ticker wurde zum Anfang der Runde aufgrund einiger Beschwerden deaktiviert. 
			Welche zukünftige Optionen erachtet ihr für den Ticker als sinnvoll:", 
		'name' => 'future_ticker', 'answer' => $temp));
	$temp = array();
	
	$tpl->assign('CHECKBOX', $checkbox_output);
	
	/*
	 * 		Radiobuttons 2 (Schulnoten)
	 */

	$radionr_output = array();
	//array_push($radionr_output, array('title' => "B&ouml;rse", 'name' => 'wallet'));
	//array_push($radionr_output, array('title' => "Monumente", 'name' => 'monu'));
	//array_push($radionr_output, array('title' => "Krieg", 'name' => 'war'));
	//array_push($radionr_output, array('title' => "Randomrunde", 'name' => 'random'));
	//array_push($radionr_output, array('title' => "Global Market", 'name' => 'market'));
	array_push($radionr_output, array('title' => "Arbeit des Staffs", 'name' => 'staff'));
	//array_push($radionr_output, array('title' => "Regeln/Nutzungsbedingungen", 'name' => 'nubs'));
	
	$tpl->assign('RADIONR', $radionr_output);
	
	$tpl->assign('UEBER_TEXTAREA', 
		'<strong>Sonstige Dinge die ich über Syndicates loswerden möchte</strong><br />
		 <em>(Meinungen, Kritiken, Ideen etc. kurz und bündig, wenn nichts bitte leer lassen)</em>
		 <br>
		 <br>
		 Erwünscht sind auch Namensvorschläge, um die Doppelbenennung der Forschung Fog of War und des 
		 Monuments Nebel des Krieges aufzuheben.');
}

require_once("../../inc/ingame/header.php");

if ($tpl->get_template_vars('MSG') != '') {
	$tpl->display('sys_msg.tpl');
}
if ($tpl->get_template_vars('ERROR') != '') {
	$tpl->display('fehler.tpl');
}
if ($tpl->get_template_vars('INFO') != '') {
	$tpl->display('info.tpl');
}

if ($show && !$error && !$success) {
	$tpl->display('clientScoring.tpl');
}
require_once("../../inc/ingame/footer.php");

?>
