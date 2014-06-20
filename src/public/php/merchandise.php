<?php
ob_start();


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$konzernaction = htmlentities($konzernaction,ENT_QUOTES);
$konzernaction2 = htmlentities($konzernaction2,ENT_QUOTES);
$race = htmlentities($race,ENT_QUOTES);
if ($next): $next = floor($next); endif;
$inners = array("sygnatur");
if (!in_array($inner,$inners)) {
    unset($inner);
}

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
list($username,$accountid,$email,$sygnatur, $sygnatur_background) = row("select username,id,email,sygnatur,sygnatur_background from users where konzernid = ".$status{id});
$vac_activated = single("select starttime from options_vacation where user_id = $status[id] and starttime > $time");
$queries = array();
$races = assocs("select * from races where active=1");

if ($race) {
    foreach ($races as $key => $value) {
        if ($key == $race) {$raceok = 1;break;}
    }
}
$raceok == 1 ? 1 : $race = "";

$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//// ***** Show_Emogames_Name Änderungen
if ($inner == "sygnatur") {
	if ($activate) {
		$queriesend[] = "update users set sygnatur = 'actualdata', sygnatur_background='race' where konzernid = $id";
		$sygnatur = 'actualdata';
		$sygnatur_background = "race";
	}
	elseif ($deactivate) {
		$queriesend[] = "update users set sygnatur = '' where konzernid = $id";
		unlink(DATA.'sygnatur/'.md5(md5($accountid)).".gif");
		$sygnatur = 0;
	}
	elseif ($typechange) {
		$valid_types = array("actualdata", "honors", "statslastnetworth", "statsendrank");
		if (in_array($type, $valid_types)) {
			select("update users set sygnatur = '$type' where konzernid = $id");
			$sygnatur = $type;
		}
	}
	elseif ($background_change) {
		$valid_types = array("race", "pbf", "sl", "uic", "neb","nof");
		if (in_array($background, $valid_types)) {
			select("update users set sygnatur_background = '$background' where konzernid = $id");
			$sygnatur_background = $background;
		}
	}
	if ($activate or $typechange or $background_change) {
		$data = array();
		if ($sygnatur == "honors") {
			$data_raw = assocs("select * from honors where user_id = $accountid");
			foreach ($data_raw as $vl) {
				$data[$vl['honorcode']] += 1;
			}
		} elseif (preg_match("/stats/", $sygnatur)) {
			$data = assocs("select * from stats where user_id = $accountid and alive > 0 and round < $globals[round]", "id");
		}
		$race_for_background = $sygnatur_background == "race" ? $status['race'] : $sygnatur_background;
		print_sygnatur($username, $accountid, $status['syndicate'], $status['land'], $status['nw'], ($globals['roundstatus'] > 0 ? single("select name from syndikate where synd_id = ".$status['rid']) : false), $status['rid'], $race_for_background, ($globals['roundstatus'] > 0 ? single("select count(*) from status where nw > ".$status['nw'])+1 : 0), $sygnatur, $data);
		system("chmod 777 ".DATA.'sygnatur/'.md5(md5($accountid)).".gif");
	}
}






if ($goon)	{

	
	// Sygnatur
	$tpl->assign('SYGNATUR', $sygnatur);
	$tpl->assign('SYGNATUR_BACKGROUND', $sygnatur_background);
	if ($sygnatur) {
		$tpl->assign('WWWDATA', WWWDATA);
		$tpl->assign('PROJECT_WWW', PROJECT_WWW);
		$tpl->assign('MD5BILDHASH', md5(md5($accountid)));
		$tpl->assign('CREATEKEY', createkey('', 4));				
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('RIPF', $ripf);
$tpl->assign('STATUS', $status);
$tpl->assign('GLOBALS', $globals);
$tpl->assign('FEATURES_KOMFORTPAKET', $features[KOMFORTPAKET]);
$tpl->assign('HIDETIPPS', $hidetipps);

if($_GET['ajax']){
	print_r($_GET);
	print_r($_POST);
	exit();
}
require_once("../../inc/ingame/header.php");
$tpl->display('merchandise.tpl');
require_once("../../inc/ingame/footer.php");


?>