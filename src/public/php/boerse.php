<?
header("Location: statusseite.php");
session_start();

// By R4bbiT - zu Runde 51

// update.php Zeile 2480
// $hourtime

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
//						     	  Header   	     	    					//
//**************************************************************************//

//weiter unten

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//



// Gibt die Anzahl der AKtien an, die der User noch kaufen kann
$rest = aktien_buyable($status['id']);
$num_last_divis = 8;
$tpl->assign('RIPF', $ripf);
$tpl->assign('TIME', $time);
$tpl->assign('UPDATE', $globals['updating']);
$tpl->assign('NUM_LAST_DIVIS', $num_last_divis);
$tpl->assign('SELL_BLOCK', SELL_BLOCK);

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

if($_POST['rid'])		$_POST['rid'] = check_int($_POST['rid']);
if($_POST['number'])	$_POST['number'] = check_int($_POST['number']);
if($_POST['preis'])		$_POST['preis'] = check_int($_POST['preis']);

if($time < $status['unprotecttime'] + BOERSE_SPERRE){
	$difference = $status['unprotecttime'] + BOERSE_SPERRE - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign('ERROR', 'Sie können erst in '.$days.' Tagen, '.$hours.' Stunden, '.$minutes.' Minuten an der Börse handeln.');
}
else if($globals['roundstatus'] != 1 && $_POST['action']){
	$tpl->assign('ERROR', 'Die Runde ist zu Ende! Diese Aktion kann nicht mehr ausgeführt werden!');
}
else if($globals['updating'] == 1 && $_POST['action']){
	$tpl->assign('ERROR', 'Während des stündlichen Updates kann keine Aktion ausgeführt werden.');
}
else{
	// Einstellen eines Angebotes
	if($_POST['action'] == 'offer'){
		// Der aktuelle Aktienkurs des Syns
		$akurs = single('SELECT aktienkurs FROM syndikate WHERE synd_id = '.$_POST['rid']);
		// Das aktuelle Aktiendepot des Spielers vom angebotetenen Syn
		$anumber = single('SELECT number FROM aktien WHERE user_id = '.$status['id'].' AND synd_id = '.$_POST['rid']);
		// Die aktuelle Anzahl, die vom Syndikat schon angeboten wird
		$agebot = single('SELECT sum(number) FROM aktien_gebote WHERE user_id = '.$status['id'].' AND rid = '.$_POST['rid'].' AND action = \'offer\'');
		// Die aktuelle Anzahl an Angeboten, die der User eingestellt hat
		$anum_gebote = single('SELECT count(id) FROM aktien_gebote WHERE user_id = '.$status['id'].' AND action = \'offer\'');
		// Die Zeiten des letzten Kaufes auslesen
		$last_buy = assoc('select time + '.(SELL_BLOCK *60*60).' as time, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%H") as h, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%i") as m, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%s") as s, rid from aktien_logs where need_id = '.$status['id'].'  and rid = '.$_POST['rid'].' order by time desc limit 1');		
		
		// Erstellen der Fehlermeldungen
		if($_POST['preis'] < $min = round(($akurs * (100-MAXRANGE_AKTIEN) / 100))){
			$tpl->assign('ERROR', 'Der Preis von '.pointit($min).' Credits darf nicht unterschritten werden');
		}
		/*else if($_POST['preis'] < MINDESTAKTIENKURS){
			$tpl->assign('ERROR', 'Der Preis von '.pointit(MINDESTAKTIENKURS).' Credits darf nicht unterschritten werden');
		}*/
		else if($_POST['preis'] > $max = round(($akurs * (100+MAXRANGE_AKTIEN) / 100))){
			$tpl->assign('ERROR', 'Der Preis von '.pointit($max).' Credits darf nicht überschritten werden');
		}
		/*else if($_POST['preis'] > MAXAKTIENKURS){
			$tpl->assign('ERROR', 'Der Preis von '.pointit(MAXAKTIENKURS).' Credits darf nicht überschritten werden');
		}*/
		else if($_POST['number'] > $anumber){
			$tpl->assign('ERROR', 'Sie wollen mehr Aktien anbieten, als Sie besitzen?');
		}
		else if($_POST['number'] < 1){
			$tpl->assign('ERROR', 'Sie wollen doch keine Aktien verkaufen?');
		}
		else if(!single('SELECT * FROM syndikate WHERE synd_id = '.$_POST['rid'])){
			$tpl->assign('ERROR', 'Bitte bieten Sie Aktien von einem Syndikat an, das auch existiert');
		}
		else if($anumber < $agebot + $_POST['number']){
			$tpl->assign('ERROR', 'So viele Aktien besitzen Sie nicht');
		}
		else if($anum_gebote >= MAXGEBOTE_AKTIEN){
			$tpl->assign('ERROR', 'Sie dürfen nur maximal '.MAXGEBOTE_AKTIEN.' Angebote einstellen');
		}
		else if($last_buy['time'] >= $time){
			$tpl->assign('ERROR', 'Der Verkauf von Aktien von Syndikat #'.$_POST['rid'].' ist bis '.$last_buy['h'].':'.$last_buy['m'].' Uhr gesperrt');
		}
		else{
			$online = $time + rand(AKTIEN_MINTIME * 60, AKTIEN_MAXTIME * 60);
			select('INSERT INTO aktien_gebote (user_id, rid, number, preis, time, action) 
										VALUES ('.$status['id'].', '.$_POST['rid'].', '.$_POST['number'].', '.$_POST['preis'].', '.$online.', \'offer\')');
			$tpl->assign('MSG', 'Aktien werden angeboten');
			if($_POST['js_action']){
				$tpl->assign('SHOW_TABLE', 'offer');
				$tpl->assign('TABLE', assoc('SELECT aktien_gebote.id as id, aktien_gebote.rid, aktien_gebote.number, aktien_gebote.preis, syndikate.name, aktien_gebote.time, aktien_gebote.action as type FROM aktien_gebote, syndikate WHERE syndikate.synd_id = aktien_gebote.rid AND aktien_gebote.user_id = '.$status['id'].' AND aktien_gebote.action = \'offer\' ORDER BY aktien_gebote.id DESC LIMIT 1'));
				$table = $tpl->fetch('boerse.tpl');
			}
		}
	}
	
	// Zurücknehmen eines Angebotes
	else if($_POST['action'] == 'offer_back' && is_numeric($_POST['offer_id'])){
		select('DELETE FROM aktien_gebote WHERE id = '.$_POST['offer_id'].' AND user_id = '.$status['id']);
		if($_POST['js_action']){
			$tpl->assign('MSG', 'ok');
		}
		else{
			$tpl->assign('MSG', 'Angebot erfolgreich zurückgenommen');
			$tpl->display('sys_msg.tpl');
		}
	}
	
	// Zurücknehmen aller Angebote
	else if($_POST['action'] == 'offer_back_all'){
		$offer_ids = assocs('SELECT id FROM aktien_gebote WHERE action = \'offer\' AND user_id = '.$status['id']);
		foreach($offer_ids as $vl) {
			select('DELETE FROM aktien_gebote WHERE id = '.$vl['id']);
		}
		if($_POST['js_action']){
			$tpl->assign('MSG', 'ok');
		}
		else{
			$tpl->assign('MSG', 'Angebote erfolgreich zurückgenommen');
			$tpl->display('sys_msg.tpl');
		}
	}
	
	// Einstellen eines Kaufgebotes
	if($_POST['action'] == 'assi'){
		// Der aktuelle Aktienkurs des Syns
		$akurs = single('SELECT aktienkurs FROM syndikate WHERE synd_id = '.$_POST['rid']);
		// Die aktuelle Anzahl an Angeboten, die der User eingestellt hat
		$anum_gebote = single('SELECT count(id) FROM aktien_gebote WHERE user_id = '.$status['id'].' AND action = \'assi\'');
		// Der Preis vom aktuell sichtbaren Gebot
		$apreis = single('SELECT preis FROM aktien_gebote WHERE rid = '.$_POST['rid'].' AND time <= '.$time.' AND action = \'offer\' ORDER BY preis ASC LIMIT 1');
		if($apreis > 0){
			$min = $apreis;
		}
		else{
			$min = round(($akurs * (100+MAXRANGE_AKTIEN) / 100));
		}
		
		// Erstellen der Fehlermeldungen
		/*if($_POST['preis'] < MINDESTAKTIENKURS){
			$tpl->assign('ERROR', 'Unter '.pointit(MINDESTAKTIENKURS).' Credits je Stück können Sie keine Aktien bekommen');
		}
		else*/ if($_POST['preis'] >= $min){
			$tpl->assign('ERROR', 'Kaufgebote für diese Aktie dürfen den Wert von '.pointit($min-1).' Credits nicht überschreiten');
		}
		else if($_POST['number'] < 1){
			$tpl->assign('ERROR', 'Um keine Aktien zu kaufen benötigen Sie kein Gebot');
		}
		else if(!single('SELECT * FROM syndikate WHERE synd_id = '.$_POST['rid'])){
			$tpl->assign('ERROR', 'Bitte bieten Sie auf Aktien von einem Syndikat, das auch existiert');
		}
		else if($anum_gebote >= MAXGEBOTE_AKTIEN){
			$tpl->assign('ERROR', 'Sie dürfen nur maximal '.MAXGEBOTE_AKTIEN.' Kaufgebote einstellen');
		}
		else{
			select('INSERT INTO aktien_gebote (user_id, rid, number, preis, time, action) 
										VALUES ('.$status['id'].', '.$_POST['rid'].', '.$_POST['number'].', '.$_POST['preis'].', '.$time.', \'assi\')');
			$tpl->assign('MSG', 'Kaufgebot erfolgreich abgegeben');
			if($_POST['js_action']){
				$tpl->assign('SHOW_TABLE', 'assi');
				$tpl->assign('TABLE', assoc('SELECT aktien_gebote.id as id, aktien_gebote.rid, aktien_gebote.number, aktien_gebote.preis, syndikate.name, aktien_gebote.action as type FROM aktien_gebote, syndikate WHERE syndikate.synd_id = aktien_gebote.rid AND aktien_gebote.user_id = '.$status['id'].' AND aktien_gebote.action = \'assi\' ORDER BY aktien_gebote.id DESC LIMIT 1'));
				$table = $tpl->fetch('boerse.tpl');
			}
		}
	}
	
	// Zurücknehmen eines Kaufgebotes
	else if($_POST['action'] == 'assi_back' && is_numeric($_POST['assi_id'])){
		select('DELETE FROM aktien_gebote WHERE id = '.$_POST['assi_id'].' AND user_id = '.$status['id']);
		if($_POST['js_action']){
			$tpl->assign('MSG', 'ok');
		}
		else{
			$tpl->assign('MSG', 'Kaufgebot erfolgreich zurückgenommen');
			$tpl->display('sys_msg.tpl');
		}
	}
	
	// Zurücknehmen aller Kaufangebote
	else if($_POST['action'] == 'assi_back_all'){
		$assi_ids = assocs('SELECT id FROM aktien_gebote WHERE action = \'assi\' AND user_id = '.$status['id']);
		foreach($assi_ids as $vl) {
			select('DELETE FROM aktien_gebote WHERE id = '.$vl['id']);
		}
		if($_POST['js_action']){
			$tpl->assign('MSG', 'ok');
		}
		else{
			$tpl->assign('MSG', 'Kaufangebote erfolgreich zurückgenommen');
			$tpl->display('sys_msg.tpl');
		}
	}
	
	// Falls der Spieler einen Kauf tätigen möchte
	else if($_POST['action'] == 'buy'){
		// Die aktuelle Anzahl an Aktien für den angegebenen Preis raussuchen
		$anumber = single('SELECT sum(number) FROM aktien_gebote WHERE rid = '.$_POST['rid'].' AND action = \'offer\' AND preis = '.$_POST['preis'].' AND time <= '.$time.' GROUP BY preis ORDER BY preis ASC limit 1');
		// Die aktuellen Credits auslesen, ob man überhaupt so viele kaufen kann
		$acredits = single('SELECT money FROM status WHERE id = '.$status['id']);
		// Die Kosten des Aktienpakets berechnen
		$kosten = $_POST['number'] * $_POST['preis'];		
		
		if($_POST['number'] > $anumber){
			$tpl->assign('ERROR', 'So viele Aktien sind für den Preis nichtmehr verfügbar');
		}
		else if($_POST['number'] > $rest){ // $rest wird ziemlich am Anfang schon initialisiert
			$tpl->assign('ERROR', 'Sie können nur noch '.pointit($rest).' Aktien kaufen');
		}
		else if($kosten > $acredits){
			$tpl->assign('ERROR', 'Für so viele Aktien reichen Ihre Credits nicht aus');
		}
		else if($_POST['number'] < 1){
			$tpl->assign('ERROR', 'Eine Aktie sollten Sie schon kaufen');
		}
		else if($_POST['preis'] < 1){
			$tpl->assign('ERROR', 'Wenn es nichtmal den Tod umsonst gibt, dann gibts auch keine Aktien umsonst');
		}
		else{
			$angebote = assocs('SELECT * FROM aktien_gebote WHERE rid = '.$_POST['rid'].' AND preis = '.$_POST['preis'].' AND time <= '.$time.' AND action = \'offer\' ORDER BY time ASC');
			$msgs = array();
			$gets = array();
			$number = $_POST['number'];
			foreach($angebote as $tag => $val){
				$tmp_num = 0;
				if($number > $val['number']){
					$tmp_num = $val['number'];
					$number -= $val['number'];
					$msgs[$val['user_id']] += $val['number'];
					set_gebotmenge($val['id'], $val['number']);
				}
				else if($number < $val['number']){
					$tmp_num = $number;
					$msgs[$val['user_id']] += $number;
					set_gebotmenge($val['id'], $number);
					$number = false;
				}
				else{
					$tmp_num = $number;
					set_gebotmenge($val['id'], $number);
					$msgs[$val['user_id']] += $number;
					$number = false;
				}
				$price_one = get_aktien_invprice($val['user_id'], $_POST['rid']);
				update_aktien('sub', $val['user_id'], $tmp_num, $_POST['rid'], ($price_one > 0 ? $price_one : $_POST['preis']));
				//Auf jeden Verkauf werten Steuern für einen Vorteilsverkauf erhoben, die als Divi ins jeweilige Lager kommen - R4bbiT - 23.03.12
				$einnahmen = pay_aktien_tax($val['user_id'], $_POST['rid'], $tmp_num, $_POST['preis']);
				$gets[$val['user_id']] += $einnahmen;
				update_credits('add', $val['user_id'], $einnahmen);
				if(!$number){
					break;
				}
			}
			
			foreach($msgs as $user => $number){
				$tax = 0;
				if($gets[$user] < $_POST['preis']*$number){
					$tax = ($_POST['preis'] * $number) - $gets[$user];
					$string = pointit($number).'|'.$_POST['rid'].'|'.pointit($gets[$user]).'|'.pointit($_POST['preis']).'|'.pointit($tax);
					$mid = 64;
				}
				else{
					$string = pointit($number).'|'.$_POST['rid'].'|'.pointit(($_POST['preis']*$number)).'|'.pointit($_POST['preis']);
					$mid = 58;
				}
				select("INSERT INTO message_values
								(id,user_id,time,werte)
								VALUES
								(".$mid.", ".$user.", ".$time.", '".$string."')");
				
				aktien_log($user, $status['id'], $_POST['rid'], $_POST['preis'], $number, $tax);
			}
			
			
			// id: 58
			// Sie haben <strong>|</strong> Aktien von Syndikat <strong>#|</strong> verkauft. Sie haben dadurch <strong>|</strong> Credits eingenommen (Kurs: <strong>|</strong> Cr).
			
			// id: 59
			// Sie haben für <strong>|</strong> Credits <strong>|</strong> Aktien von Syndikat <strong>#|</strong> erworben.
	
			update_aktien('add', $status['id'], $_POST['number'], $_POST['rid'], $_POST['preis']);
			update_credits('sub', $status['id'], $_POST['number'] * $_POST['preis']);
			$tpl->assign('MSG', pointit($_POST['number']).' Aktien von Syndikat #'.$_POST['rid'].' für '.pointit($kosten).' Cr erfolgreich erworben (Stückpreis: '.pointit($_POST['preis']).')');
			$rest -= $_POST['number'];
		}
	}
}

/*
 * HEADER
 */

if(!$_POST['js_action']){
	// Wenn es keinen Ajax-Request gibt, wird der normale Header angezeigt
	require_once("../../inc/ingame/header.php");
}
else{
	// Ansonsten wird die Kodierung auf UTF-8 gestellt
	header("content-type: text/html; charset=ISO-8859-1");
}



// Falls es vorher eine Fehlermeldung gab
if($tpl->get_template_vars('ERROR') != ''){
	if($_POST['js_action']){
		// Wenn es als Ajax-Request kam
		$request = $tpl->get_template_vars('ERROR');
		$type = 'error';
	}
	else{
		// Wenn nicht, wird die Meldung normal angezeigt
		$tpl->display('fehler.tpl');
	}
}
// Oder falls es eine erfolgreiche Meldung gab
else if($tpl->get_template_vars('MSG') != ''){
	if($_POST['js_action']){
		$request = $tpl->get_template_vars('MSG');
		$type = 'msg';
	}
	else{
		$tpl->display('sys_msg.tpl');
	}
}

if($_POST['js_action']){
	// Dient als Serverantwort, wenn es einen Ajax-Request gibt
	$rueck = array(
				'msg' => utf8_encode($request), // beeinhaltet die Meldung
				'table' => utf8_encode($table),
				'type' => $type); // beeinhaltet den neuen Eintrag in die Tabelle (bei Geboten)
	echo json_encode($rueck);
	// Abbruch des Script, damit sonst nichts ausgegeben wird
	exit();
}

## BERECHNUNGEN + AUSGABE ##
$syndata = assocs('select aktienkurs, aktien_pool, min_gebot, min_auktion, name, synd_id as rid from syndikate ORDER BY synd_id asc','rid');
$privat = assocs('select number, synd_id as rid, invested from aktien where user_id = '.$status['id'].' order by synd_id asc','rid');
$number_own = assocs('select count(user_id) as number, synd_id from aktien group by synd_id', 'synd_id');
$last_buy = assocs('select time, from_unixtime(time, "%H") as h, from_unixtime(time, "%i") as m, from_unixtime(time, "%s") as s, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%H") as b_h, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%i") as b_m, from_unixtime(time + '.(SELL_BLOCK *60*60).', "%s") as b_s, rid from aktien_logs where need_id = '.$status['id'].' order by time asc', 'rid');

$guthaben = 0;
$gesamt_aktien = 0;
foreach ($syndata as $key => $value) {
	unset($temp, $d_divi, $divi_ges, $count);
	$syndata[$key]['umlauf'] = num_aktien($key);
	$syndata[$key]['makler'] = num_aktien($key,2);
	$syndata[$key]['makler_prozent'] = ($syndata[$key]['umlauf'] > 0 ? 100 / $syndata[$key]['umlauf'] * $syndata[$key]['makler'] : 0);
	
	$syndata[$key]['besitz'] = $privat[$key]['number'];
	$syndata[$key]['invested'] = $privat[$key]['invested'];
	$syndata[$key]['invested_once'] = $privat[$key]['number'] > 0 ? ceil($privat[$key]['invested'] / $privat[$key]['number']) : 0;
	$gesamt_aktien += $syndata[$key]['besitz'];
	$guthaben += $syndata[$key]['besitz'] * $syndata[$key]['aktienkurs'];
	$syndata[$key]['besitz_prozent'] = ($syndata[$key]['umlauf'] > 0) ? 100 / $syndata[$key]['umlauf'] * $syndata[$key]['besitz'] : 0;
	if($privat[$key]['number']){
		$privat[$key]['number'] = $privat[$key]['number'] - single('SELECT sum(number) FROM aktien_gebote WHERE user_id = '.$status['id'].' AND rid = '.$key.' AND action = \'offer\'');
	}
	
	$temp = assoc('select sum(number) as gebot, preis from aktien_gebote  where rid = '.$key.' and action = \'offer\' and time <= '.$time.' group by preis order by preis ASC limit 1');
	$syndata[$key]['gebot'] = $temp['gebot'];
	$syndata[$key]['preis'] = $temp['preis'];
	if($temp['preis'] > 0){
		$syndata[$key]['kaufbar'] = (floor($status['money'] / $temp['preis']) < $temp['gebot']) ? floor($status['money'] / $temp['preis']) : $temp['gebot'];
	}
	$syndata[$key]['kaufbar'] = ($syndata[$key]['kaufbar'] > $rest) ? $rest : $syndata[$key]['kaufbar'];
	
	$syndata[$key]['min'] = ($syndata[$key]['aktienkurs'] * (100-MAXRANGE_AKTIEN) / 100);
	$syndata[$key]['max'] = ($syndata[$key]['aktienkurs'] * (100+MAXRANGE_AKTIEN) / 100);
	
	if($last_buy[$key]['time'] >= $time - SELL_BLOCK *60*60){
		$syndata[$key]['last_buy_status'] = 'show';
	}
	else if($last_buy[$key]['time'] < $time - SELL_BLOCK *60*60 && $last_buy[$key]['time'] > 1){
		$syndata[$key]['last_buy_status'] = 'late';
	}
	else{
		$syndata[$key]['last_buy_status'] = 'none';
	}
	$syndata[$key]['last_buy_h'] = $last_buy[$key]['h'];
	$syndata[$key]['last_buy_min'] = $last_buy[$key]['m'];
	$syndata[$key]['last_buy_sec'] = $last_buy[$key]['s'];
	$syndata[$key]['block_h'] = $last_buy[$key]['b_h'];
	$syndata[$key]['block_min'] = $last_buy[$key]['b_m'];
	$syndata[$key]['block_sec'] = $last_buy[$key]['b_s'];
	
	if($syndata[$key]['umlauf']){
		$user_gr = assocs('SELECT a.user_id as user_id, a.number_save as num, s.syndicate as name, s.rid as rid, '.(100 / $syndata[$key]['umlauf']).' * a.number_save as prozent FROM aktien as a, status as s WHERE a.user_id = s.id AND a.synd_id = '.$key.' AND a.number_save >= '.round($syndata[$key]['umlauf'] * 0.01).' ORDER BY a.number_save DESC');
		$user_kl = assoc('SELECT sum(number_save) as freefloat, '.(100 / $syndata[$key]['umlauf']).' * sum(number_save) as freefloat_prozent FROM aktien WHERE synd_id = '.$key.' AND number_save < '.round($syndata[$key]['umlauf'] * 0.01).' GROUP BY synd_id');
	}
	$syndata[$key]['inhaber'] = $user_gr;
	$syndata[$key]['freefloat'] = $user_kl['freefloat'];
	$syndata[$key]['freefloat_prozent'] = $user_kl['freefloat_prozent'];
	
	$syndata[$key]['cronimon_name'] = urlencode("Kursverlauf Syndikat #".$syndata[$key]['rid']);
		
	$divi_ges = singles("SELECT gesamt FROM aktien_dividenden_detail where user_id = ".$status['id']." AND rid = ".$syndata[$key]['rid']." ORDER by time DESC LIMIT ".$num_last_divis);
	if($divi_ges){
		foreach($divi_ges as $tag => $divi){
			$d_divi += $divi;
			$count++;
		}
		$d_divi = round($d_divi / $count);
	}
	$syndata[$key]['d_divi'] = $d_divi;
		
	if($syndata[$key]['rid'] == $status['rid']){
		$syndata[$key]['own_syn'] = 1;
	}
}


//"Kaufbare Aktien"-Leisten
$prozent = ceil($gesamt_aktien / ($gesamt_aktien + $rest) * 100);
if($prozent < 0) $prozent = 0;
else if($prozent > 100) $prozent = 100;
$tpl->assign('PROZENT',$prozent);
$tpl->assign('TOTALBUYABLE',$gesamt_aktien + $rest);
$tpl->assign('REST', $rest);
$tpl->assign('DIVI', assoc("SELECT * FROM aktien_dividenden where user_id = ".$status['id']." ORDER by time DESC LIMIT 0 , 1"));
$tpl->assign('GUTHABEN', $guthaben);
$tpl->assign('GESAMT_AKTIEN', $gesamt_aktien);
$temp = assocs('SELECT aktien_gebote.id as assi_id, aktien_gebote.rid, aktien_gebote.number, aktien_gebote.preis, syndikate.name FROM aktien_gebote, syndikate WHERE syndikate.synd_id = aktien_gebote.rid AND aktien_gebote.user_id = '.$status['id'].' AND aktien_gebote.action = \'assi\' ORDER BY aktien_gebote.rid ASC');
$tpl->assign('ASSIDATA', $temp);
$tpl->assign('ASSIDATA_COUNT', count($temp));
$temp = assocs('SELECT aktien_gebote.id as offer_id, aktien_gebote.rid, aktien_gebote.number, aktien_gebote.preis, aktien_gebote.time, syndikate.name FROM aktien_gebote, syndikate WHERE syndikate.synd_id = aktien_gebote.rid AND aktien_gebote.user_id = '.$status['id'].' AND aktien_gebote.action = \'offer\' ORDER BY aktien_gebote.rid ASC');
$tpl->assign('OFFERDATA', $temp);
$tpl->assign('OFFERDATA_COUNT', count($temp)); unset($temp);
$tpl->assign('AUKTIONDATA', assocs('SELECT aktien_gebote.id as id, aktien_gebote.rid, aktien_gebote.number, aktien_gebote.preis, syndikate.name, aktien_gebote.time, aktien_gebote.action as type,  aktien_gebote.number * aktien_gebote.preis as einsatz FROM aktien_gebote, syndikate WHERE syndikate.synd_id = aktien_gebote.rid AND aktien_gebote.user_id = '.$status['id'].' AND aktien_gebote.action = \'auktion\' ORDER BY aktien_gebote.rid ASC'));
$tpl->assign('OWNDATA', $privat);
$tpl->assign('SYNDATA', $syndata);
$tpl->display('boerse.tpl');


//**************************************************************************//
//								Ausgabe, Footer								//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function aktien_log($offer_id, $need_id, $rid, $preis, $menge, $tax){
	global $time;
	select("INSERT INTO aktien_logs
					(offer_id, need_id, rid, preis, menge, time, tax)
					VALUES
					('".$offer_id."', '".$need_id."', '".$rid."', '".$preis."', '".$menge."', '".$time."', '".$tax."')");
}

function set_gebotmenge($id, $menge){
	$menge_ist = single("SELECT number FROM aktien_gebote WHERE id = ".$id);
	$rest = $menge_ist - $menge;
	if($rest <= 0){
		select("DELETE FROM aktien_gebote WHERE id = ".$id);
	}
	else{
		select("UPDATE aktien_gebote SET number = ".$rest." WHERE id = ".$id);
	}
}

function update_credits($type, $id, $menge){
	global $status;
	$money = single("SELECT money FROM status WHERE id = ".$id);
	if($type == 'add'){
		$money += $menge;
	}
	else if($type == 'sub'){
		$money -= $menge;
	}
	else{
		return false;
	}
	select("UPDATE status SET money = ".$money.", nw = ".nw($id)." WHERE id = ".$id);
	$status['money'] = $money;
}

?>