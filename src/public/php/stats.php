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
$round > 0 ? $round = round($round) : $round = $globals['round'];

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$ridname = "";


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//



//							selects fahren									//

/*

huhu

*/

list($user_id, $startround) = row("select id, startround from users where konzernid=".$status[id]);
$userdata[konzernid] = $id;

$origround=$globals{'round'};


if (!$round or $round < $startround or $round > $globals['round']) {$round = $globals{'round'};}
$round = (int)$round;

if ($globals[roundstatus] == 0) {
	$dontshowakt = 1;
	if ($round >= $globals[round]) {
		$round = $globals[round]-1;
	}
	if ($startround == $globals[round]) {
		$nostats = 1;
	}
}

$stats = getstats($user_id,$round);
while(!is_array($stats) && $round > 0) {
	$round--;
	$stats = getstats($user_id,$round);
}

$races = assocs("select * from races","race");
if ($round > 1 && !$nostats && $stats[rid]) {
    $syndtable = "syndikate";
    if ($round < $origround || !$userdata[konzernid]) {
        $syndtable .="_round_".$round;
    }

    $ridname = single("select name from ".$syndtable." where synd_id = ".$stats{rid});
}


//							Berechnungen									//

// Statistiken der letzten Runden f?r aktuellen spieler ermitteln
$a = 1;

$round_stats = array();
while ($a < $origround && !$nostats) {
    $tempstats = getstats($user_id,$a);
    if ($tempstats{round} == $a) {
        # links zu den statistiken der letzten runde(n)
        $ag = 0;
        if ($a == 1) {$ag = "Beta1";}
        if ($a == 2) {$ag = "Beta2";}
        if ($a > 2) {$ag = $a-2;}
        $temp['a'] = $a;
        $temp['ag'] = $ag;
        array_push($round_stats, $temp);
        unset($temp);
    }
    $a++;
} //while $a < $round
if (count($round_stats) > 0) {
	$tpl->assign('oldstats', true);
	$tpl->assign('round_stats', $round_stats);
	$tpl->assign('origround', $origround);
	$tpl->assign('showakt', !$dontshowakt);
}
/*if ($oldstats && !$dontshowakt) {
    $oldstats.="<a class=\"konzernAttacked\" href=\"stats.php?action=stats&round=$origround\">Aktuelle Runde</a><br>";
} */



#####################################################################
if (!$nostats) {
	if ($round == $origround) {
		$from = "status";
	}
	else {
		$from = $globals[statstable];
		$where = "round=$round and";
	}
	$players = assocs("select count(*) as number,race from $from where $where alive > 0 group by race","race");
	$uicplayers = $players{uic}{number} ? $players[uic][number] : 0;
	$pbfplayers = $players{pbf}{number} ? $players[pbf][number] : 0;
	$slplayers = $players{sl}{number} ? $players[sl][number] : 0;
	$nebplayers = $players{neb}{number} ? $players[neb][number] : 0;
	$nofplayers = $players{nof}{number} ? $players[nof][number] : 0;
	$totalplayers = $uicplayers+$slplayers+$pbfplayers+$nebplayers+$nofplayers;
	$uicrel=sprintf("%.1f", ($uicplayers/$totalplayers * 100));
	$pbfrel=sprintf("%.1f", ($pbfplayers/$totalplayers * 100));
	$slrel=sprintf("%.1f", ($slplayers/$totalplayers * 100));
	$nebrel=sprintf("%.1f", ($nebplayers/$totalplayers * 100));
	$nofrel=sprintf("%.1f", ($nofplayers/$totalplayers * 100));

	// Rundenausgabe oben (verlinkt dann auch zur Hall of Fame siehe template)
	$roundshow = $round -2;
	if ($round == 1) {$roundshow="Beta1";}
	else if ($round ==2) {$roundshow="Beta2";}
	if ($round == $origround) {$roundausgabe = "Aktuelle Runde";} 
	else {$roundausgabe="&Uuml;bersicht Runde ".$roundshow;}

//							Daten schreiben									//

//							Ausgabe     									//

	$tpl->assign("roundausgabe", $roundausgabe);
	$tpl->assign("round", $round);					
	$tpl->assign("raceuicshort", $races{uic}{shortname});
	$tpl->assign("uicplayers", pointit($uicplayers));
	$tpl->assign("uicrel", $uicrel);
	$tpl->assign("raceslshort", $races{sl}{shortname});
	$tpl->assign("slplayers", pointit($slplayers));
	$tpl->assign("slrel", $slrel);
	$tpl->assign("racepbfshort", $races{pbf}{shortname});
	$tpl->assign("pbfplayers", pointit($pbfplayers));
	$tpl->assign("pbfrel", $pbfrel);	
	$tpl->assign("racenebshort", $races{neb}{shortname});
	$tpl->assign("nebplayers", pointit($nebplayers));
	$tpl->assign("nebrel", $nebrel);
	$tpl->assign("racenofshort", $races{nof}{shortname});
	$tpl->assign("nofplayers", pointit($nofplayers));
	$tpl->assign("nofrel", $nofrel);
	$tpl->assign("totalplayers", pointit($totalplayers));
	$tpl->assign("stats", $stats);
	$tpl->assign("statsfrakname", $races{$stats{race}}{name});
	$tpl->assign("ridname", $ridname);
	$oldround = ($globals[roundstatus] == 1 or $globals['round'] != $round);
	$tpl->assign("oldround", $oldround);
	// Ausgeführte Angriffe
	$tpl->assign("stats_attack_numberdone_normal", pointit($stats[attack_numberdone_normal]));
	$tpl->assign("stats_attack_numberdone_siege", pointit($stats[attack_numberdone_siege]));
	$tpl->assign("stats_attack_numberdone_conquer", pointit($stats[attack_numberdone_conquer]));
	$tpl->assign("stats_attack_numberdone_waraffected", pointit($stats[attack_numberdone_waraffected]));
	$tpl->assign("stats_attack_numberdone_won_normal", pointit($stats[attack_numberdone_won_normal]));
	$tpl->assign("stats_attack_numberdone_won_siege", pointit($stats[attack_numberdone_won_siege]));
	$tpl->assign("stats_attack_numberdone_won_conquer", pointit($stats[attack_numberdone_won_conquer]));
	$tpl->assign("stats_attack_largest_won_normal", pointit($stats[attack_largest_won_normal]));
	$tpl->assign("stats_attack_largest_won_siege", pointit($stats[attack_largest_won_siege]));
	$tpl->assign("stats_attack_largest_won_conquer", pointit($stats[attack_largest_won_conquer]));
	$tpl->assign("stats_attack_largest_won_waraffected", pointit($stats[attack_largest_won_waraffected]));
	$tpl->assign("stats_attack_total_won_normal", pointit($stats[attack_total_won_normal]));
	$tpl->assign("stats_attack_total_won_siege", pointit($stats[attack_total_won_siege]));
	$tpl->assign("stats_attack_total_won_conquer", pointit($stats[attack_total_won_conquer]));
	$tpl->assign("stats_attack_total_won_waraffected", pointit($stats[attack_total_won_waraffected]));
	// Erlittene Angriffe
	$tpl->assign("stats_attack_numbersuffered_normal", pointit($stats[attack_numbersuffered_normal]));
	$tpl->assign("stats_attack_numbersuffered_siege", pointit($stats[attack_numbersuffered_siege]));
	$tpl->assign("stats_attack_numbersuffered_conquer", pointit($stats[attack_numbersuffered_conquer]));
	$tpl->assign("stats_attack_numbersuffered_lost_normal", pointit($stats[attack_numbersuffered_lost_normal]));
	$tpl->assign("stats_attack_numbersuffered_lost_siege", pointit($stats[attack_numbersuffered_lost_siege]));
	$tpl->assign("stats_attack_numbersuffered_lost_conquer", pointit($stats[attack_numbersuffered_lost_conquer]));
	$tpl->assign("stats_attack_largest_loss_normal", pointit($stats[attack_largest_loss_normal]));
	$tpl->assign("stats_attack_largest_loss_siege", pointit($stats[attack_largest_loss_siege]));
	$tpl->assign("stats_attack_largest_loss_conquer", pointit($stats[attack_largest_loss_conquer]));
	$tpl->assign("stats_attack_largest_loss_waraffected", pointit($stats[attack_largest_loss_waraffected]));
	$tpl->assign("stats_attack_total_loss_normal", pointit($stats[attack_total_loss_normal]));
	$tpl->assign("stats_attack_total_loss_siege", pointit($stats[attack_total_loss_siege]));
	$tpl->assign("stats_attack_total_loss_conquer", pointit($stats[attack_total_loss_conquer]));
	$tpl->assign("stats_attack_total_loss_waraffected", pointit($stats[attack_total_loss_waraffected]));
	// Ausgeführte Spionageaktionen
	$tpl->assign("stats_spyopsdone", pointit($stats{spyopsdone}));
	$tpl->assign("stats_spyopsdonewon", pointit($stats{spyopsdonewon}));
	$spyopsdonewon_prozent = ($stats{spyopsdonewon}*100/($stats{spyopsdone} ? $stats{spyopsdone} : 1)); 
	$tpl->assign("stats_spyopsdonewon_prozent", prozent($spyopsdonewon_prozent));
	$tpl->assign("stats_spies_lost", pointit($stats{spies_lost}));
	// Geklautes (Summe)
	$tpl->assign("stats_moneystolen", pointit($stats[moneystolen]));
	$tpl->assign("stats_energystolen", pointit($stats[energystolen]));
	$tpl->assign("stats_metalstolen", pointit($stats[metalstolen]));
	$tpl->assign("stats_sciencepointsstolen", pointit($stats[sciencepointsstolen]));
	// Geklautes (Maxgrabs)
	$tpl->assign("stats_max_steal_money", pointit($stats[max_steal_money]));
	$tpl->assign("stats_max_steal_energy", pointit($stats[max_steal_energy]));
	$tpl->assign("stats_max_steal_metal", pointit($stats[max_steal_metal]));
	$tpl->assign("stats_max_steal_sciencepoints", pointit($stats[max_steal_sciencepoints]));
	// Erlittene Spionageaktionen
	$tpl->assign("stats_spyopssuffered", pointit($stats{spyopssuffered}));
	$tpl->assign("stats_spyopssufferedlost", pointit($stats{spyopssufferedlost}));
	$spyopssufferedlost_prozent = ($stats{spyopssufferedlost}*100/($stats{spyopssuffered} ? $stats{spyopssuffered} : 1));
	$tpl->assign("stats_spyopssufferedlost_prozent", prozent($spyopssufferedlost_prozent));
	$tpl->assign("stats_spies_executed", pointit($stats{spies_executed}));
	// Land gekauft, höchster NW, meistes Land
	$tpl->assign("stats_landexplored", pointit($stats{landexplored}));
	$tpl->assign("stats_largestnetworth", pointit($stats{largestnetworth}));
	$tpl->assign("stats_largestland", pointit($stats{largestland}));
	// Aktueller NW Platz, bzw. Platz im Endranking
	if ($round == $globals[round] && $globals[roundstatus] == 1) {
		$ranks = assocs('SELECT id FROM status WHERE alive > 0 ORDER BY nw_rankings DESC');
		foreach($ranks as $rank => $user_id){ // <- ganz schön hässlich geschrieben
			if($user_id['id'] == $status['id']){
				$user_rank = $rank+1;
			}
		}
		$tpl->assign("showcurrentNWrank", true);
		$tpl->assign("user_rank", pointit($user_rank));
	}
	else if($stats{endrank} > 0){
		$tpl->assign("showNWrank", true);
		$tpl->assign("stats_endrank", pointit($stats{endrank}));
	}
	else{
		// Kein Platz wird ?gelistet?
	}
	
	
	// Falls Komfortpaket aktiviert kommt nun die NW-Übersicht der aktuellen Runde				
	if ($round == $globals[round] && $globals[roundstatus] == 1 && $features[KOMFORTPAKET]) {
		!$time_mode ? $time_mode = 2: 1;
		//global $ripf;
		###############################
		#		CONFIG For this function	
		###############################
		if ($time_mode == 1) {
			$limitby = "limit 20";
			$from = "nw_safe";
		}
		if ($time_mode == 2) {
			$limitby = "";
			$from = "nw_statsfeature";
		}
		$zeitformat[1] = "G \U\h\\r";
		$zeitformat[2] = "d.m.y - H:i";
		//$maxlines = 20;
		
		###############################
		$data = assocs('select nw,land,time from '.$from.' where user_id='.$status[id].' order by time desc '.$limitby);
		
		$maxland = 0;
		$maxnw = 0;
		$minland = 10000000000;
		$minnw = 10000000000;
		foreach ($data as $temp) {
			if ($temp[nw] > $maxnw) {$maxnw = $temp[nw];}
			if ($temp[nw] < $minnw) {$minnw = $temp[nw];}
			if ($temp[land] > $maxland) {$maxland = $temp[land];}
			if ($temp[land] < $minland) {$minland = $temp[land];}
		}
		$nwdiff = $maxnw-$minnw;
		$landdiff = $maxland-$minland;
		$landdiff <= 0 ? $landdiff = 1: 1;
		$nwdiff <= 0 ? $nwdiff = 1: 1;
		
		// Ausgabevariablen
		$tpl->assign("showCurrentStats", true);
		$tpl->assign("verlauf", $verlauf);
		if (count($data) > 0) {
			$tpl->assign("showstat", true);
			$tpl->assign("ripf", $ripf);
			$tpl->assign("this", $this);
			$data_output = array();
			foreach ($data as $temp) {
				// Daten berechnen
        	    $temp['tempnwwidth']= round (($temp[nw]-$minnw) / $nwdiff * 280)+5;
				$temp['templandwidth'] = round (($temp[land]-$minland) / $landdiff * 280)+5;
				$temp['zeitanzeige'] = date($zeitformat[$time_mode],$temp[time]);
				$temp['o_nw'] = pointit($temp['nw']);
				$temp['o_land'] = pointit($temp['land']);
				array_push($data_output, $temp);
			}
			$tpl->assign("data", $data_output);
		}
		else {
			// Es liegen noch keine Daten vor
		}
	}

} // if ! nostats
else {
	// Für diesen Account sind noch keine Statistiken verfügbar
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//
require_once("../../inc/ingame/header.php");

if ($globals[roundstatus] == 0) {
	$tpl->assign("ERROR", "Runde noch nicht gestartet!");
	$tpl->display("fehler.tpl");
}

$tpl->display('stats.tpl');
require_once("../../inc/ingame/footer.php");

	/*
	$honors = assocs("select * from honors where user_id = $userdata[id]");
	if (count($honors >= 1)) {
		printhonors($honors,1);
	}
	echo $ausgabe;
	*/


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


###########################################################
### Sub f?r statsholen, 1. Argument id, 2. argument runde##
###########################################################

function getstats($user_id_intern) {
    global $origround;
	global $startround;
    global $globals;
    $round_intern = $origround;
    if (func_num_args() > 1) {$round_intern = func_get_arg (1);}
	if ($round_intern < $startround) {$round_intern = $startround;}
	if ($round_intern <= $origround) {
		$stats1 = assoc("select * from $globals[statstable] where round=$round_intern and user_id=".$user_id_intern);
	}
    return $stats1;
}


?>
