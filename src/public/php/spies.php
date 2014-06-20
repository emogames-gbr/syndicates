<?
//exit(1);

//Meldungen vorbereiten
$errormsg	= "";
$beschr		= "";
$infomsg	= "";
$tpl_result = false;


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

// Inneraction parameter checken


if ($target)
	$target = (int) $target;
	
if (!$target)
	$targetname = "Kein Ziel ausgewählt";

$addactions = round($addactions);
$addactions > 5 ? $addactions = 5:1;
$addactions < 0 ? $addactions = 0:1;

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


$maxget_bonus_in_percent = 0; // Initialbonus auf spionageklaukapazität
//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");
$protactions = SPYPROTACTIONS; // Anzahl aktionen nach denen gbprot für sabaktionen einsetzt.


//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//
// Hier noch Übergabe checken, da $status benötigt
if (!$rid && $rid != "0") {$rid = $status{rid};}
if ($rid <= 0) {$rid=1;}
$rid = (int) $rid;

// Zufallsgenerator initialisieren   ---- braucht es nicht, dragon12 23.3.2012
/*list($usec,$sec) = explode(" ",microtime());
$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;
echo $bla;
mt_srand($bla);*/

// Schwierigkeitsgrade alt:
/*$easy = 1.66;
$medium=0.8;
$hard = (1/2);
$veryhard = (0.4);*/
//Schwerigskeitgrade Runde 58 by dragon12
$easy = 0.4;
$medium=0.6;
$hard = 1.2;
$veryhard = 1.6;

$resstats = getresstats();
foreach ($resstats as $k => $value) {
	if ($value[type] != "money") {
		$resstats[$k][value] *= RESSTATS_MODIFIER;
	}
}

$players = array();
$aktien = array();
$aktionen = assocs("select action_key,name,type,difficulty from spyaction_settings","action_key"); // Daten zu Spionageaktionen holen
$job = assoc("select * from jobs where acceptor_id=$status[id]");
if (!$job[user_id]) {$job = "";}
$queries = array();
$resultstring ="";
$offmarket = getmarket($status[id]);

$inneractions = array("prepare");
foreach ($aktionen as $key => $value) {
    array_push ($inneractions,$value{key});
}
// jetzt übergabe checken
if ($inneraction) {
    $allowed = 0;
    foreach ($inneractions as $temp) {
        if ($temp == $inneraction) {$allowed=1;break;}
    }
    if ($allowed == 0) {$inneraction == 0;}
}
$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//ist es sommerzeit?
$tpl->assign('IS_CEST', date('I'));

//							selects fahren									//

// Spione zusammenzählen
$spystats = getspystats($status{race});
$sumspies = 0;
foreach ($spystats as $key => $value) {
    $sumspies += $status{$key}+$offmarket[$key];
}
/*if ($sciences{glo14}) {
	$sumspies += $status{offspecs};
}*/
// Steht Spieler noch unter Schutz ?
$is_mentor_spy = ($status['is_mentor'] && (is_mentorprogram($target) == 1));
if (in_protection($status) && !$is_mentor_spy) {
    unset($inneraction);
    unset($target);
    $errormsg	= "Sie können nicht spionieren, solange sie unter Schutz stehen";
}
// Hat Spieler Spione ?
else if ($sumspies == 0 && !$is_mentor_spy) {
    unset($inneraction);
    unset($target);
    $errormsg	= "Sie brauchen Spione um spionieren zu können";
}
// Hat Spieler Spionageaktionen ?
else if ($status{spyactions} == 0 && !$is_mentor_spy) {
    unset($inneraction);
    unset($target);
    $errormsg	= "Sie haben keine Spionageaktionen mehr!";
}

$maxSpyOpsThis = MAXSPYOPS + $sciences[glo6] * GLO6BONUS+ $partner[14] * MAXSPYOPS_PARTNERBONUS + $sciences[glo10] * GLO10BONUS_ADD_OPS; //by Christian 7.9.10

// Selbst noch unter Schutz ?
$isprotection = in_protection($status) && !$is_mentor_spy;
// Eigene Allianz_ID ?
$allianz_id = single("select allianz_id from syndikate where synd_id=".$status[rid]);
// Gegnerische Allianz ID ?
$rid == $status[rid] ? ($synd_type2 = $game_syndikat[synd_type]) : (list($allianz_id2, $synd_type2) = row("select allianz_id, synd_type from syndikate where synd_id=".$rid));
// NAPS
$naps = assocs("select nappartner, type from naps_spieler where user_id=$id and type > 0", "nappartner");

if ($target) {
	// atwar durch racherecht
	//if (is_array($atwarids) && in_array($target,$atwarids)) {$atwar = 1;}
	//if (racherecht($target)) {$atwar = 2;} // sinnlose Zeile die nur Bugs hervorruft, RR wird extra abgefragt -> deaktiviert!! -- Runde 66 inok1989

    // Spione und Spionageaktionen ?
    if (($status{spyactions} - $addactions <= 0 || $sumspies == 0) && !$is_mentor_spy) {
        unset($inneraction);
        if ($inneraction) {$errormsg	= "Sie haben zu wenig Spionageaktionen oder zu wenig Spione für diese Aktion";}
        else {$errormsg	= "Sie haben zu wenig Spionageaktionen oder zu wenig Spione um spionieren zu können";}
    }
    $defstatus = getallvalues($target);
	
	// Atwar nochmal checken, damit nicht als rid ein anderer parameter angegeben wird, als der target tatsächlich hat.
	if (!$atwar) {
		$atwar = inwar($status[rid],$defstatus[id]);
	}
	if ($inneraction == "killsciences" && racherecht($target)!=1) {
		$inneraction = ""; $errormsg	= "Sie können die Spionageaktion Forschung zerstören nur wählen, wenn Sie direktes Racherecht beim angewählten Spieler habe.";
	}
	//echo" atwar: $atwar  rr:".racherecht($target);
	// Ressourcen aus transfer dazuaddieren
	$transres = assocs("select * from transfer where user_id=".$defstatus{id}." and finished = 0");
	$transresPB = assocs("select * from transfer where user_id=".$status{id}." and finished = 0");
	foreach ($transres as $value) {
		$dtres[$value[product]] += $value[number];
	}
	foreach ($transresPB as $value) {
		$tresPB[$value[product]] += $value[number];
	}
	
	// Berechnung für Aktienschutz
	list($anzahl,$aktienprozent,$umlauf) = aktienbesitz($defstatus[id],$status['rid']);
	$defstatus{aktien} = $aktienprozent;

//	echo $defstatus{aktien}."<br>";
    $targetname = $defstatus{syndicate}." (#".$defstatus{rid}.")";

    #$spyable = spyable($defstatus{createtime},$defstatus{aktien},$defstatus{land},$defstatus{alive},$defstatus{rid},$defstatus{lastlogintime},$atwar);
	if ($status[gvi]): $gvi = 1; else: $gvi = $defstatus[gvi]; endif;
	$spyable = isattackable( $defstatus{rid}, $defstatus{alive}, $defstatus{lastlogintime}, $defstatus{land}, $defstatus{createtime}, $defstatus{aktien}, $atwar, $isprotection, 0, $allianz_id, $allianz_id2, $naps, $target, 2,$defstatus{nw},$gvi, $game_syndikat[synd_type], $synd_type2,$defstatus[inprotection],$defstatus['unprotecttime']);
    if ($spyable != 1 and $spyable != 4 && !$is_mentor_spy) {
		$errormsg	= "".transformfehler($spyable,"message")."";
		unset($target);
		$targetname = "Kein Ziel ausgewählt";
	}

}

// Bei Killsciences wegen 20 Spionageaktionen checken und ob gegner überhaupt forschungen hat
if ($inneraction == "killsciences") {
	$defsciences = getsciences($defstatus{id}, "killsciences");
	if ($status{spyactions} < KILLSCIENCESACTIONS+$addactions) {
		$errormsg	= "Sie benötigen mindestens ".KILLSCIENCESACTIONS." Spionageaktionen um diese Spionageaktion ausführen zu können";
		$inneraction = "prepare";
	}
	elseif (count($defsciences) < 1) {
		$errormsg	= "Dieser Spieler besitzt keine Forschungen, die sie zerstören könnten";
		$inneraction = "prepare";
	}
}

// Bei Killbuildings checken, ob die zusätzliche Aktion vorhanden ist
// und auch ausgeführt werden darf
if ($inneraction == "killbuildings") {
	if (		!$atwar  //Runde52
		&&	!racherecht($target) 
		&&      !($job[target_id] == $target && $job[type] == "killbuildings")	
		) {
		$errormsg	= "Die Spionageaktion <b>Gebäude zerstören</b> können Sie nur ausführen, wenn<br>a) Sie oder ein Spieler aus Ihrem Syndikat Racherecht gegen das Ziel haben.<br>b) Sie sich mit dem Syndikat im Krieg befinden<br>c) oder Sie einen Auftrag gegen das Ziel angenommen haben.";
		$inneraction = "prepare";
	}
	else
	if ($status[spyactions] < KILLBUILDINGSACTIONS+$addactions) {
		$errormsg	= "Sie benötigen mindestens ".(KILLBUILDINGSACTIONS)." Spionageaktionen um diese Spionageaktion ausführen zu können";
		$inneraction = "prepare";
	}
}
// Bei Killunits checken, ob erlaubt
if ($inneraction == "killunits") {
	if (		!$atwar  //Runde52
		&&	!racherecht($target) 
		&&      !($job[target_id] == $target && $job[type] == "killunits")	
		) {
		$errormsg	= "Die Spionageaktion <b>Militäreinheiten zerstören</b> können Sie nur ausführen, wenn<br>a) Sie oder ein Spieler aus Ihrem Syndikat Racherecht gegen das Ziel haben.<br>b) Sie sich mit dem Syndikat im Krieg befinden<br>c) oder Sie einen Auftrag gegen das Ziel angenommen haben.";
		$inneraction = "prepare";
	}
	else
	if ($status[spyactions] < KILLUNITSACTIONS+$addactions) {
		$errormsg	= "Sie benötigen mindestens ".(KILLUNITSACTIONS)." Spionageaktionen um diese Spionageaktion ausführen zu können";
		$inneraction = "prepare";
	}
}
// Bei Delayaway checken, ob die zusätzlichen Aktionen vorhanden ist
if ($inneraction == "delayaway") {
		if (		!$atwar  //Runde52
		&&	!racherecht($target) 
		&&      !($job[target_id] == $target && $job[type] == "delayaway")	
		) {
		$errormsg	= "Die Spionageaktion <b>Rückkehr verzögern</b> können Sie nur ausführen, wenn<br>a) Sie oder ein Spieler aus Ihrem Syndikat Racherecht gegen das Ziel haben.<br>b) Sie sich mit dem Syndikat im Krieg befinden<br>c) oder Sie einen Auftrag gegen das Ziel angenommen haben.";
		$inneraction = "prepare";
	}
	else
	if ($status[spyactions] < DELAYAWAYACTIONS+$addactions) {
		$errormsg	= "Sie benötigen mindestens ".(DELAYAWAYACTIONS)." Spionageaktionen um diese Spionageaktion ausführen zu können";
		$inneraction = "prepare";
	}
}

// Bei Diebstahl checken, ob die zusätzlichen Aktionen vorhanden ist
if ($inneraction == "getmoney" || $inneraction == "getmetal" || $inneraction == "getenergy" || $inneraction == "getsciencepoints" || $inneraction == "getpodpoints") {
	if ($status[spyactions] < STEALACTIONS+$addactions) {
		$errormsg	= "Sie benötigen mindestens ".(STEALACTIONS)." Spionageaktionen um diese Spionageaktion ausführen zu können";
		$inneraction = "prepare";
	}
}


// Checken, ob killactions erlaubt sind
if ($inneraction == "killsciences" || $inneraction == "killbuildings" || $inneraction == "killunits" || $inneraction == "delayaway") {
	if (!$sciences{glo13}) {
		$inneraction = "prepare";
		$glo13name = single("select gamename from sciences where name ='glo' and typenumber=13");
		$errormsg	= "Sie benötigen die Forschung $glo13name um diese Spionageaktion ausführen zu können";
	}
}


if(is_mentorprogram($defstatus["id"]) == 1 && $inneraction != "unitintel1" && $interaction !="podintel" && $inneraction != "unitintel2" && $inneraction != "buildintel" && $inneraction != "scienceintel" && $inneraction != "prepare" && $inneraction != ""){
	$errormsg	= "Sie sind Mentor und dürfen dadurch keinen Neuling beklauen!";
	$inneraction = "prepare";
}


if (!$inneraction || $inneraction == "prepare") {
    // Daten zum Spieler holen
    $players = assocs("select inprotection, unprotecttime, createtime,land,alive,rid,lastlogintime,id,syndicate as name,nw,gvi from status,syndikate where rid = $rid and rid = synd_id","id");
    // Nachschauen welche Spieler ausspionierbar sind:
    foreach ($players as $key => $value) {
		$gvi = 0;
		if ($atwar) {$tempatwar = 1;}
		elseif (is_array($atwarids) && in_array($value[id],$atwarids)) {$tempatwar = 1;} //&& added by dragon R58, es gab immer fehlermeldungen
		if ($status[gvi]): $gvi = 1; else: $gvi = $value[gvi]; endif;
		
		list($anzahl,$aktienprozent,$umlauf) = aktienbesitz($value['id'],$status['rid']);
		
		$spyable = isattackable( $value{rid}, $value{alive}, $value{lastlogintime}, $value{land}, $value{'createtime'}, $aktienprozent, $tempatwar, $isprotection, 0, $allianz_id, $allianz_id2, $naps, $key, 2,$value{nw}, $gvi, $game_syndikat[synd_type], $synd_type2,$value['inprotection'], $value['unprotecttime']);
		if ($spyable == 4): $spyable = 1; endif; # Im Krieg gibts noch keine Notwendigkeit zwischen stärkerer oder schwächerer Landhälfte zu unterscheiden.
        $players{$key}{spyable} = $spyable;
		$tempatwar = 0;
    }
}


//							Berechnungen									//

//**********************************************************
//                  Spionageaktion ausführen
//**********************************************************

if ($target) {
	$targetpaid = isattackable_paid($target, $atwar);
}
	$kondata = assocs("select id, syndicate, land, lastlogintime, createtime, alive, nw,gvi,inprotection,unprotecttime from status where rid=$rid", "id");

if ($target && ($spyable == 1 || $spyable == 4) && $inneraction && $inneraction != "prepare" && $targetpaid && (!in_protection($kondata[$target]) ||$is_mentor_spy ) ) {
	if ($spyable == 4) {
		$atwar = 0; // Spionageaktion zählt als normale Spionageaktion weil gegen schwächere Hälfte des Syndikats
	}

    // Erfolg bestimmen
    $defmarket = getmarket($defstatus{id});
    if (!$defsciences) {$defsciences = getsciences($defstatus{id});}
	$defpartner = getpartner($defstatus[id]);
	/*
	pvar($defstatus[id],defid);
	pvar($defpartner,defpartner);
	*/
    $defspystats = getspystats($defstatus{race});
    $offense = 0;
    $defense = 0;
    // Spyactions abziehen
    $spyactionsused = 1 + $addactions;
	if ($inneraction == "getmoney" || $inneraction == "getmetal" || $inneraction == "getenergy" || $inneraction == "getsciencepoints" || $inneraction == "getpodpoints") {
		$spyactionsused += STEALACTIONS-1;
	}
	elseif ($inneraction == "killsciences") {
		$spyactionsused += KILLSCIENCESACTIONS-1;
	}
	elseif ($inneraction == "killbuildings") {
		$spyactionsused += KILLBUILDINGSACTIONS-1;
	}
	elseif ($inneraction == "killunits") {
		$spyactionsused += KILLUNITSACTIONS-1;
	}
	elseif ($inneraction == "delayaway") {
		$spyactionsused += DELAYAWAYACTIONS-1;
	}
    $status{spyactions} -= ($spyactionsused);
    // defunitstats holen, falls aktion unitintel1,2 oder killunits ist
    if ($aktionen[$inneraction][action_key] == "unitintel1" || $aktionen[$inneraction][action_key] == "unitintel2" || $aktionen[$inneraction][action_key] == "killunits") {$defunitstats = getunitstats($defstatus{race});}

    // Offense und Defence berechnen
    /*

    Punkte berechnen sich aus: (Angriffspunkte / Land) +1
    Jede Addaction bringt 20%/ben. ops bonus
    */
    $success = 0;

    //Offense
    foreach ($spystats as $key => $value) {
        $offense += ( $offmarket[$key] + $status{$key} ) * 
		( $value{$aktionen{$inneraction}{type}} + ($partner[27] && ($aktionen{$inneraction}{type} == ip) ? PB_IP_GAIN : 0) );
		
		//echo $key." - ".($offmarket[$key] + $status{$key})." zu ".$value{$aktionen{$inneraction}{type}}."  off: ".$offense."<br>";
    }
	
	if ($sciences{glo14}) { //ctp
		if($aktionen{$inneraction}{type} == op) $offense += GLO14BONUS_OP_PER_HA * $status[land];
		if($aktionen{$inneraction}{type} == ip) $offense += GLO14BONUS_IP_PER_HA * $status[land];
	}
	
	
    $offense = $offense * (1+$addactions*0.2/($spyactionsused-$addactions));
    dm('atwar: '.$atwar);
    dm('addactions: '.$addactions);
    dm('multiplikator: '.(1+$addactions*0.2/($spyactionsused-$addactions)));
    $offbonus = 0;
    if ($sciences{glo1}){$offbonus += GLO1BONUS/100*$sciences{glo1};}
    if ($status{race} == "sl") {
        if($aktionen{$inneraction}{type} == op) $offbonus += SLOFFDEFBONUS;
		// Seccenters
		if ($status[seccenters]) {
			$secbonus = $status[seccenters] / $status[land] * SECCENTERBONUS2;
			$secbonus > 0.3 ? $secbonus = 0.3 : 1;
			$offbonus += $secbonus;
		}
		//$offense += $status[elites2]; // HH gebene je 1 punkt
    }

	if ($partner[11]) {
		$offbonus += $partner[11] * SPYSTRENGTH_PARTNERBONUS;
	}
    $offense *= (1+$offbonus);
	/*if ($aktionen{$inneraction}{type} == op) {
		if ($status[land] < $defstatus[land]) {
			$offense *= (1+$status[land]/$defstatus[land])/2;
		}
	}*/
//    echo $offense;
    $offense = $offense / $status{land};

	$sabotageforschung = get_synfos_count_extern($defstatus,"glo12",$defstatus['rid']);
	
    foreach ($defspystats as $key => $value) {
        $defense += ($defmarket[$key]+$defstatus{$key}) * $value{dp};
    }
	if ($defsciences{glo14}) { //ctp
		$defense += $defsciences{glo14} * GLO14BONUS_DP_PER_HA * $defstatus[land];
	}
    $defbonus = 0;
    if ($defsciences{glo1}){$defbonus += GLO1BONUS/100*$defsciences{glo1};}
    
    // SPyweb +10% gegen SPIONAGE
    if ($aktionen{$inneraction}{type} == "ip" && $defsciences{glo9} == 3){$defbonus += GLO9BONUS/100;}
    
    if ($defstatus{race} == "sl") {
       // $defbonus +=SLOFFDEFBONUS;
		// Seccenters
		if ($defstatus[seccenters]) {
			$secbonus = $defstatus[seccenters] / $defstatus[land] * SECCENTERBONUS2;
			$defsecbonus > 0.3 ? $defsecbonus = 0.3 : 1;
			$defbonus += $defsecbonus;
		}
    }
    if ($defstatus[race] == "nof") {
    	$defbonus +=NOFDEFBONUS;
    }
    
    
	if ($defpartner[11]) {
		$defbonus += $defpartner[11] * SPYSTRENGTH_PARTNERBONUS;
	}

	if ($aktionen{$inneraction}{type} == "op") { //verdammt nochmal issdn schützt gegen sabotage dummköpfes
		
		//ISSDN Fix //Runde52
		$sabotageforschung *= SYNFOS_ISSDN_OTHER;
		$sabotageforschung += $defsciences[glo12] * SYNFOS_ISSDN_OWN;
		$defbonus += $sabotageforschung / 100;

	}

    $defense *= (1+$defbonus);
	//echo "<br>$defense";
    $defense = $defense / $defstatus{land};
    //$match = $offense / $defense; alte erfolgsrechnung (Runde 58 by dragon12)
    
    //Schwierigskeitgrad mit eingerechnet: (Runde 58 by dragon12)
    $defense *= $$aktionen{$inneraction}{difficulty};
    
    if (strpos($inneraction, 'kill') !== false || $inneraction == 'delayaway') { //wieder eingeführ R59 by dragon12, aber nur für sabs
    	
    	//define(SUCCESS_LOSS_AT_PROTOPS,2); //pro Op nach ProtOps verliert man 2% an Erfolgsquote
    	//define(MAX_SUCCESS_LOSS_AT_PROTOPS,90); //bis zu einem Maximum von 90%  - gibts nicht mehr
    
    	//Runde52 Erfolgsabnahme bei Sabbschutz
    	//$steal = array(	"killunits" => 1,
    		//	"killbuildings" => 2,
    			//"delayaway" => 3,
    			//"killscience" => 4
    	//);
    
    	$prottime = $time - (60*60*24);
    	$opactions = "'killunits','killbuildings'";
    	$opsdone = (KILLBUILDINGSACTIONS+KILLUNITSACTIONS)/2*single('select count(*) from spylogs where did='.$defstatus[id].' and action in ('.$opactions.') and time >= '.$prottime.' and success=1');
    	$opactions = "'delayaway'";
    	$opsdone += DELAYAWAYACTIONS*single('select count(*) from spylogs where did='.$defstatus[id].' and action = '.$opactions.' and time >= '.$prottime.' and success=1');
    	$opactions = "'killsciences'";
    	$opsdone += KILLSCIENCESACTIONS*single('select count(*) from spylogs where did='.$defstatus[id].' and action = '.$opactions.' and time >= '.$prottime.' and success=1');
    	$opsdone += $spyactionsused-$addactions;
    	if (1 <= $atwar || racherecht($target)===1) {
    		$opsdone = $opsdone < SPYPROTACTIONS_WAR ? 0 : $opsdone - SPYPROTACTIONS_WAR;
    	} else {
    		$opsdone = $opsdone < SPYPROTACTIONS ? 0 : $opsdone - SPYPROTACTIONS;
    	}
    	
    	dm($opsdone);
    	$spyprot_defbonus = $opsdone * SPYPROT_PERC;
    	dm('defbonus:'.$spyprot_defbonus);
    	$defense *= 1 + $spyprot_defbonus/100;
    	$opactions="";
    }
    
    //VerstŠrkung des stŠrkeren Runde 58 by dragon12
    $offense = pow($offense, 2.5);
    $defense = pow($defense, 2.5);
    
    dm("off: ".$offense."<br />def: ".$defense); //testausgabe
    
    /*Erfolgsberechnung alt, geŠndert Runde58 by dragon12
    // Schnitt 60
    $random = mt_rand(10,110) * $$aktionen{$inneraction}{difficulty};
//    echo ("<br>Random: $random<br>match:$match");
    if ($random * $match >= 50) {$success = 1;}
    else {$success = 0;}
    */
    //Erfolgsberechnung neu Runde58 by dragon12
    if($offense > 0){
    	$erfolgs_abschnitt = $offense*100/($offense+$defense);
    	//es gibt immer eine 2% chance zu failen oder durchzukommen, also hier ein cap:
    	if($erfolgs_abschnitt>99) $erfolgs_abschnitt = 99;
    	else if ($erfolgs_abschnitt<2) $erfolgs_abschnitt=2;
    	$random = mt_rand(0, 100);
    	if($random < $erfolgs_abschnitt) {$success = 1;}
    	else {$success=0;}
    	dm('random = '.$random.', erfolg bei random < '.$erfolgs_abschnitt);
    }
    
    // Generell 2% Chance zu gewinnen bzw Fehlschlag zu haben: alt (Runde 58 by dragon12)
    /*$newrand = mt_rand(0,100);
    if ($newrand < 2) {$success = 0;}
    else if ($newrand > 98) {$success = 1;}
    */
    
    //
    // bei artefakt 50% chance auf fail
    // R47 Fixed DragonTEC
   	$defsyn = assoc("select * from syndikate where synd_id = ".$defstatus[rid]."");
	$artefakt_id_def = $defsyn[artefakt_id];
	
    $artefakte[$artefakt_id_def][bonusname] == "spy_fail_bonus" ? $avalue  = $artefakte[$artefakt_id_def][bonusvalue] : 1; 
    if ($avalue) {
	    $final_rand = mt_rand(0,100);
    	if ($final_rand < $avalue) {
    		$success = 0;
    	}	
    }
	
    if(is_mentorprogram($defstatus["id"]) == 1){
		$success = 1;
		$mentor = true;
		$status{spyactions} += $spyactionsused;
		$spyactionsused = 0;
	}
	
	
	$tpl_result['success'] = $success;
	$tpl_result['op'] = $inneraction;
	$tpl_result['name'] = $aktionen{$inneraction}{name};
	$tpl_result['victim_rid'] = $defstatus{rid};
	$tpl_result['victim_name'] = $defstatus{syndicate};
	$tpl_result['victim_nw'] = pointit($defstatus[nw]);
	$tpl_result['victim_land'] = pointit($defstatus[land]);
	$tpl_result['time'] = mytime($time, "noDayReplacement");
  
    
    // ERFOLG AB HIER BESTIMMT, ERGEBNIS DER SPIONAGEAKTION STEHT FEST!
//    echo "<br> Succ: $success";

    if ($success == 1) {
        // Die beiden Ausgabearrays initialisieren
        $ausgabename = array();
        $ausgabewert = array();
        $ausgabemessage = "";
		
		if(!$mentor){
			if ($defsciences{glo9}) {
				$messageadd="Mit Hilfe unseres Spy Webs konnten wir feststellen, dass die Spione von ".$status{syndicate}." (#".$status{rid}.") kamen.";
			}
			if ($defsciences[glo9] >= 2) $messageadd.=" Ausgeführte Spionageaktion: <b>".$aktionen{$inneraction}{name}."</b>.";
			if ($job[target_id] == $defstatus[id] && $job[type] == $inneraction && $defsciences{glo9}) {
				if (!$job[anonym]) {
					$jobuserdata = assoc("select syndicate,rid from status where id = $job[user_id]");
					$messageadd.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen von <b>".$jobuserdata[syndicate]." (#".$jobuserdata[rid].")</b> erstellten Auftrag!</i>";
				$jobadd.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen von <b>".$jobuserdata[syndicate]." (#".$jobuserdata[rid].")</b> erstellten Auftrag!</i>";
				}
				else {
					$messageadd.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen anonymen Auftrag!</i>";
				$jobadd.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen anonymen Auftrag!</i>";
				}
			}
		}
	
		## Ausgabe zum Respyauswählen auf der Erfolgsseite
		$tpl_respy=array();
		$tpl_respy['targetname']=$targetname;
		$tpl_respy['target']=$target;
		$tpl_respy['rid']=$rid;
		$tpl_respy['actions']=array();
	
		if ($target) {
			array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Spionage --',"disable"=>"disabled"));
			foreach ($aktionen as $key => $value) {
				if ($key == "getpodpoints") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Sabotage --',"disable"=>"disabled"));
				}
				if ($key == "killunits") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- nur bei Racherecht & Krieg --',"disable"=>"disabled"));
				}
				
				if ($key == "killsciences" && $status{spyactions} < KILLSCIENCESACTIONS) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				} elseif (($key == "killsciences" || $key == "killbuildings" || $key == "killunits" || $key == "delayaway") && !$sciences{glo13}) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				}
				else {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>""));
				}
			}
		}
		
		$tpl_respy['ops']=$status{spyactions};
		$tpl_respy['maxops']=$maxSpyOpsThis;
		$tpl->assign('RESPY',$tpl_respy);
     
        // Wenn inneraction erlaubt, array mit diebstahleffizienz erzeugen:
        if ($aktionen{$inneraction}{type} == "op") {
        	$matchtime = $time - 60*60*24; // Vergleiche aktionen von vor einem tag
        	$numActions = 0;
			if ($inneraction == "killunits") {
				$numActions = single("select count(*) from spylogs where did=$defstatus[id] and success=1 and action='killunits' and time > $matchtime");
			}
			else if ($inneraction == "killbuildings") {
				$numActions = single("select count(*) from spylogs where did=$defstatus[id] and success=1 and action='killbuildings' and time > $matchtime");
			}
			else if ($inneraction == "delayaway") {
				$numActions = single("select count(*) from spylogs where did=$defstatus[id] and success=1 and action='delayaway' and time > $matchtime");
			}

            // Auswirkungsspezifikationen der einzelnen aktionen
            $steal = array("getmoney" => array(offspies => STEAL_OS_CREDITS, defspies => STEAL_ISDS_CREDITS, intelspies => STEAL_ISDS_CREDITS, maxget => STEAL_MAX_CREDITS),
                           "getmetal" => array (offspies => STEAL_OS_METAL, defspies => STEAL_ISDS_METAL, intelspies => STEAL_ISDS_METAL, maxget => STEAL_MAX_METAL),
                           "getenergy" => array (offspies => STEAL_OS_ENERGY, defspies => STEAL_ISDS_ENERGY, intelspies => STEAL_ISDS_ENERGY, maxget => STEAL_MAX_ENERGY),
                           "getsciencepoints" => array (offspies => STEAL_OS_SCIENCEPOINTS, defspies => STEAL_ISDS_SCIENCEPOINTS, intelspies => STEAL_ISDS_SCIENCEPOINTS, maxget => STEAL_MAX_SCIENCEPOINTS),
                           "killunits" => array (offspies => KILL_OS_KILLUNITS, defspies => KILL_ISDS_KILLUNITS, intelspies => KILL_ISDS_KILLUNITS, maxget => calcSpylossMax($numActions,"mil"), damagetime => 60*60*12),
						   "killbuildings" => array(offspies => KILL_OS_KILLBUILDINGS,defspies => KILL_ISDS_KILLBUILDINGS,intelspies => KILL_ISDS_KILLBUILDINGS,maxget=>calcSpylossMax($numActions)),
						   "delayaway" => array(offspies => 0,defspies => 0,intelspies => 0,maxget=>calcSpylossMax($numActions)), //Runde52
						   "getpodpoints" => array(offspies => STEAL_OS_CREDITS, defspies => STEAL_ISDS_CREDITS, intelspies => STEAL_ISDS_CREDITS, maxget => STEAL_MAX_POD),
						);
						
						
			//pvar($steal,"stealtable");						
			// Eigenes Artefakt für bis zur doppelten Kapazität klauen ? 
			$artefakt_id = $game_syndikat[artefakt_id];		
			if ($artefakte[$artefakt_id][bonusname] == "spy_damagecap_bonus") {
				/*
				foreach ($steal as $key => $value) {
					$steal[$key][maxget] *= (1+$artefakte[$artefakt_id][bonusvalue] / 100);
				}
				*/
				$maxget_bonus_in_percent += $artefakte[$artefakt_id][bonusvalue];
			}
			
			if ($sciences[glo16]) {
				/*
				foreach ($steal as $key => $value) {
					$steal[$key][maxget] *= (1+$sciences[glo16] * CAPACITY_AUGMENTATION_BONUS / 100);
				}
				*/
				$maxget_bonus_in_percent += $sciences[glo16] * CAPACITY_AUGMENTATION_BONUS;
			}

			// Gangbang modifier bestimmen
 			$prottime = $time - (60*60*24); // GbProt gegen einen Spieler soll genau einen Tag gelten
			foreach ($steal as $key => $value) {
				$opactions.="'$key',";
			}
			$opactions = chopp($opactions);
			$opsdone = single("select count(*) from spylogs where did=".$defstatus[id]." and (action like '%kill%' or action like '%delay%') and time >= $prottime and success=1");
			$gbmod=1;
			
			// Bestimmen ab wann Basschutz einsetzt
			/*if ($atwar) { //gibt es seit Runde59 nicht mehr, by dragon12
				$protactions = 2;
			}
			else {
				$protactions = 1;
			}
			$gbops=$opsdone;
			
			if ($gbops > 2*$protactions  && $gbops < 6*$protactions ) {
				$gbmod = .75;
			} elseif ($gbops >= 6*$protactions ) {
				$gbmod = .5;
			}*/

            // Alle Stealaktionen behandeln
            if ($inneraction != "killunits" && $inneraction != "killbuildings" && $inneraction != "killsciences" && $inneraction != "delayaway") {
                // Im Krieg kann bis zu 2/3 mehr geklaut werden maximal
                
                if (1 <= $atwar || racherecht($target)===1) {//bugfix by dragon12 R59
               		$maxget_bonus_in_percent += WAR_CAPACITY_STEAL_BONUS;
                }
               	$steal{$inneraction}{maxget} *= (1+($maxget_bonus_in_percent/100));
                $product = substr($inneraction,3,strlen($inneraction));
                $get = 0;
                foreach ($spystats as $key => $value) {
                    $get += ($status{$value{type}}+$offmarket[$value[type]]) * $steal{$inneraction}{$value{type}};
                }
                $prodsum = ($defstatus{$product}+$defmarket{$product}+$dtres[$product]);
                $max = $prodsum/100*$steal{$inneraction}{maxget};
                $get > ($max) ? $get = $max : 1;
                
				/*if (!$atwar) {
					//$get =  floor ($gbmod * $get); fällt weg
				}*/
				//Spydeefartefakt
				$artefakte[$artefakt_id_def][bonusname] == "spy_damagereduction_bonus"  ? ($get = $get *  ($artefakte[$artefakt_id_def][bonusvalue] / 100)) : 1;
				
				// Hier banken behandeln
				if ($inneraction == "getmoney") {
					$percentbanks = $defstatus[banks] / $defstatus[land];
					$percentbanks *= 100;
					$percentbanks > BANKENMAXSAVE ? $percentbanks = BANKENMAXSAVE : 1;
					$saved = $percentbanks * BANKENSAVE;
					
					if ($get > $prodsum - $saved) {
						$get = $prodsum-$saved;
					}
				}
				
				if ($inneraction == "getpodpoints") {
					$podops=single("select count(*) from spylogs where aid=$status[id] and did=$defstatus[id] and action ='getpodpoints' and time >= $prottime and success=1");
					if($podops >1 and $podops < 4)
						$get *= .5;
					elseif($podops >= 4)
						$get = 0;
					//$saved = $defstatus[land]*(pow(2, $defsciences["ind10"]+($artefakte[$artefakt_id_def][bonusname] == "reduced_podtaxes" ? 1 : 0)))*PODSAVEPERLAND;
					$saved = getSavePodPoints($defstatus, $defsciences, $artefakt_id_def); // in subs
					if ($get > $prodsum - $saved) {
						$get = $prodsum-$saved;
					}
				}
				
                $get = round($get);
				$get <= 0 ? $get = 0:1;

				if (!$job || $job[target_id] != $defstatus[id] || $job[type] != $inneraction) {
					if ($defstatus[lastlogintime] + TIME_TILL_INACTIVE > $time) {
					}
					else {
						$get = floor($get*0.5); // Spieler ist inaktiv -> reduzierte Spygains
						$badget=1;
					}
                	$status{$product} += $get;
				}
				// statsstring
				$stats = assoc("select * from stats where konzernid=$status[id] and round=$globals[round]");
				$maxstring = "max_steal_".($product=='podpoints' ? 'money' : $product);
				if ($stats[$maxstring] <= $get) {
					$maxupdatestring = "$maxstring=$get,";
				}
			
				$sproduct = ($product=='podpoints' ? 'money' : $product)."stolen";
				$statsstring = ",$sproduct=$sproduct+$get,nettostolen=nettostolen+".($get * $resstats{($product=='podpoints' ? 'money' : $product)}{value})." ";

				if ($get > 0) {
					if ($defstatus[$product] <= $get) {
							$rest = $get-$defstatus{$product};
						$defupdatewert = $defstatus{$product};
						$defstatus[$product] = 0;
					}
					else {
						$defupdatewert = $get;
							$defstatus{$product} -= $get;
					}
				} else {
					$defupdatewert = 0;
				}

				// falls transferressourcen vorhanden
				if ($rest > 0 && $dtres[$product]) {
					if ($rest < $dtres[$product]) {
						$transfers = assocs("select * from transfer where user_id=".$defstatus[id]." and product='".$product."' and finished =0");
						foreach ($transfers as $vl) {
							if ($rest > $vl[number]) {
								$rest -= $vl[number];
								$vl[number] = 0;
								$action= ("update transfer set number=0 where transferid=".$vl[transferid]);
								$queries[] = $action;
							}
							else {
								$vl[number] -= $rest;
								$action= ("update transfer set number=".$vl[number]." where transferid=".$vl[transferid]);
								$queries[] = $action;
								$rest=0;
							}

						}

					}
					else {
						$rest = $rest - $dtres[$product];
						$action=("update transfer set number=0 where product='".$product."' and user_id=".$defstatus[id]);
						$queries[] = $action;
					}
				}

                if($rest > 0) {
				    $marketdata = changetype($product);
			        $offers = assocs("select offer_id, number from market where type='".$marketdata{type}."' and prod_id =".$marketdata{prod_id}." and owner_id = '".$defstatus{id}."' ORDER BY number ASC"); //and number >= $rest limit 1
                    foreach($offers as $offer) {
                    	//echo ($rest."<br>");
                    	if($rest <= 0) {
                    		break;
                    	}
                    	if($rest >= $offer[number]) {
                    		$action = "DELETE FROM market WHERE offer_id=".$offer[offer_id]." AND owner_id=".$defstatus{id};
                    		$rest -= $offer[number];
                    	} elseif($rest < $offer[number]) {
                    		$action = "UPDATE market SET number = number - ".$rest." WHERE offer_id = ".$offer[offer_id]." AND owner_id=".$defstatus{id};
                    		$rest = 0;
                    	}
                    	array_push($queries, $action);
                    }
			        //$offerid = $offerid[0];
                    //$action = "update market set number = number - $rest where offer_id = $offerid";
                    //array_push ($queries,$action);
                    $market = getmarket($defstatus{id});
                    $market{$product} -= $rest;
                    $defnw = nw($defstatus{id});
                    unset($market);
                    $market=getmarket($status{id});
                }

				$defnw = nw($defstatus{id});
				if ($badget == 1) {
					$defupdatewert *=2;
				}
				$action = "update status set $product=$product - $defupdatewert,nw = ".$defnw." where id =".$defstatus{id};
				array_push ($queries,$action);
                // Ressourcen gutschreiben
				// Auf Job aufpassen
				if ($job[target_id] == $defstatus[id] && $job[type] == $inneraction) {
					$action = "update status set $product=$product + $get where id =".$job{user_id};
	                array_push ($queries,$action);
					$action = "update status set spyactions = spyactions - $spyactionsused,nw = ".$status{nw}." where id =".$status{id};
	                array_push ($queries,$action);
				}
				else {
					$status{nw} = nw($status{id});
					$action = "update status set ".($product=='podpoints' ? 'money' : $product)."=".($product=='podpoints' ? 'money' : $product)." + $get,spyactions = spyactions - $spyactionsused,nw = ".$status{nw}." where id =".$status{id};
	                array_push ($queries,$action);
				}
				$prodname = ($resstats{$product}{name} == "") ? single("select currency from syndikate where synd_id=".$defstatus[rid]): $resstats{$product}{name};
                // Message an bestohlenen schicken
                $messageid = 12;
                $werte = pointit($get)."|".$prodname."|".$messageadd;

                $ausgabemessage ="Sie konnten folgende Ressourcen stehlen:";
                array_push ($ausgabename,$prodname);
                array_push ($ausgabewert,pointit($get));
            }
            // Bis hier alle Stehlaktionen behandelt
			// Ab hier zerstöraktionen
            elseif ($inneraction == "killunits") {
                if (!(1 <= $atwar || racherecht($target)===1)) {
					$was = "beschädigen";
					$wasmail = "beschädigt";
				}
				else {
					$was = "beschädigen (im Krieg werden 40% der beschädigten Einheiten permanent zerstört)";
					$wasmail = "beschädigt (im Krieg werden 40% der beschädigten Einheiten permanent zerstört)";
					$steal{$inneraction}{maxget} *=1;
				}
				if ((1 <= $atwar) || racherecht($target)===1) {//bugfix by dragon12 R59
               		$maxget_bonus_in_percent += WAR_CAPACITY_STEAL_BONUS;
                }
               	$steal{$inneraction}{maxget} *= (1+($maxget_bonus_in_percent/100));
                $rel = array();
                $alldefunits = 0;
                $defcarrier = 0;
				foreach ($defunitstats as $key => $value) {
					if($defstatus['race'] != 'nof' || $value['type'] != 'elites')
						$alldefunits += ($defmarket[$value[type]]+$defstatus{$value{type}});
					else {
						$defcarrier = ($defmarket[$value[type]]+$defstatus{$value{type}});
					}
				}
				if ($alldefunits) {
					foreach ($defunitstats as $key => $value) {
						if($defstatus['race'] != 'nof' || $value['type'] != 'elites')
							$rel{$value{type}} = ($defmarket[$value[type]]+$defstatus{$value{type}}) / $alldefunits;
						else
							$rel{$value{type}} = 0;
					}
				}
                # gesamtverlust max 3%
                $unitskilled = 0;
                foreach($spystats as $value) {
                    $unitskilled += ($offmarket[$value[type]]+$status{$value{type}}) * $steal{$inneraction}{$value{type}};
                }
                $unitskilled_carrier = $unitskilled;
                if ($unitskilled > $steal{$inneraction}{maxget}*$alldefunits/100) {$unitskilled = $steal{$inneraction}{maxget}*$alldefunits/100;}
                if ($unitskilled_carrier > $steal{$inneraction}{maxget}*$defcarrier/100) {
                	$unitskilled_carrier = $steal{$inneraction}{maxget}*$defcarrier/100;
                }
                // Defartefakt
				$artefakte[$artefakt_id_def][bonusname] == "spy_damagereduction_bonus"  ? ($unitskilled *=   ($artefakte[$artefakt_id_def][bonusvalue] / 100)) : 1;
				$artefakte[$artefakt_id_def][bonusname] == "spy_damagereduction_bonus"  ? ($unitskilled_carrier *=   ($artefakte[$artefakt_id_def][bonusvalue] / 100)) : 1;
                
				//if (!$atwar) {
					$unitskilled = floor ($unitskilled); //*$gbmod
					$unitskilled_carrier = floor ($unitskilled_carrier);
				//}

                $killed = array();
                foreach ($defunitstats as $value) {
                	if($defstatus['race'] != 'nof' || $value['type'] != 'elites')
                    	$killed{$value{type}} = round ($rel{$value{type}} * $unitskilled);
                	else
                		$killed{$value{type}} = $unitskilled_carrier;
                    array_push($ausgabename,$value{name});
                    array_push($ausgabewert,pointit($killed{$value{type}}));
                }
                $ausgabemessage = "Sie konnten folgende Militäreinheiten $was:";
                $werte=$wasmail."|";
                foreach ($defunitstats as $value) {
                    $werte.="<tr class=tableInner1><td>".$value{name}."</td><td>".$killed{$value{type}}."</td></tr>";
                }
                $werte.="|".$messageadd;
                $messageid = 11;

                // Dbrequests:
                    // Spyactions
                    $action = "update status set spyactions = spyactions - $spyactionsused where id =".$status{id};
                    array_push($queries,$action);
                    // Units abziehen
                    $action = "update status set ";
                    foreach ($killed as $key => $value) {
						// Hier zuerst units auf market killen
						// Sind genug Einheiten auf dem Markt, um diese zuerst töten zu können ?
						if ($defmarket[$key] >= $killed[$key]) {
							$tlosses =$killed[$key];
							// Ziehe Einheiten von angeboten ab
							$stuff = changetype($key,0,$defstatus[race]);
							$type = $stuff[type];
							$prod_id = $stuff[prod_id];
							$i=0;
							while ($tlosses > 0 && $i < 100) {
								$largestoffer = assoc("select * from market where owner_id=$defstatus[id] and prod_id=$prod_id and type='$type' order by inserttime desc limit 1");
								if ($largestoffer[number] >= $tlosses) {
									$queries[] = "update market set number=number-$tlosses where offer_id=$largestoffer[offer_id]";
									$tlosses = 0;
								}
								else {
									$tlosses -= $largestoffer[number];
									if ($globals[updating] != 1) select("delete from market where offer_id=$largestoffer[offer_id]");
								}
								$i++;
							}
						}
						// Wenn nicht, töte alle auf dem markt und ziehe dann einheiten daheim ab
						else {
							$stuff = changetype($key,0,$defstatus[race]);
							$type = $stuff[type];
							$prod_id = $stuff[prod_id];
							$queries[] = "delete from market where owner_id=$defstatus[id] and prod_id=$prod_id and type='$type'";
							$tlosses = $killed{$key};
							$tlosses -= $defmarket[$key];
							// Lösche angebote, ziehe rest von status ab
							$status{$key} -= $tlosses;
							$action .="$key = $key - $tlosses,";
/*							$action .="$key = $key - $value,";
							$defstatus{$key} -= $value;
							*/
						}
                    }
                    $defnw = nw($defstatus{id});
                    //$action = chopp($action);
                    $action.= "nw = ".$defnw." where id = ".$defstatus{id};
                    array_push($queries,$action);
                    // Wieder in bau schicken, wenn nicht atwar:
                    $buildtime = get_hour_time($time)+60*60*5;
                    $action="insert into military_away (user_id,unit_id,number,time) values ";
                    foreach ($defunitstats as $value) {
                        if ($killed{$value{type}} > 0) {
							$killquery_valid=1;
                            if (!(1 <= $atwar || racherecht($target)===1)) {
                                $rebuild = $killed{$value{type}};
                            }
                            else {
                                // Nur 60% der zerstörten Einheiten werden im Kriegsfall wieder in bau geschickt
                                $rebuild = ceil($killed{$value{type}} * 0.6);
                            }
                            $action.="(".$defstatus{id}.",".$value{unit_id}.",".$rebuild.",$buildtime),";
                        }
                    }
                    $action = chopp ($action);
					if ($killquery_valid) { //$alldefunits && 
                    	array_push($queries,$action);
					}

                // Dbrequests:
            }
			//Gebäude 
			elseif ($inneraction == "killbuildings") {

                if ((1 <= $atwar) || racherecht($target)===1) {
                	$maxget_bonus_in_percent += WAR_CAPACITY_STEAL_BONUS;
                }
               	$steal{$inneraction}{maxget} *= (1+($maxget_bonus_in_percent/100));
                $rel = array();
				$alldefbuildings = $defstatus[land]; //getallbuildings($defstatus{id});
				if($steal{$inneraction}{maxget}*$alldefbuildings/100 > getallbuildings($defstatus{id}))
					$alldefbuildings = getallbuildings($defstatus{id});
				$alldefbuildings = ($alldefbuildings == 0 ? 1 : $alldefbuildings);
				
				$buildings = assocs("select name_intern,name from buildings where race like '%".$defstatus{race}."%' or race = 'all'");
				foreach ($buildings as $value) {
					$rel{$value{name_intern}} = $defstatus{$value{name_intern}} / $alldefbuildings;
				}
                # gesamtverlust max 1%
                $buildingskilled = 0;
                foreach($spystats as $value) {
                    $buildingskilled += ($status{$value{type}}+$offmarket[$value[type]]) * $steal{$inneraction}{$value{type}};
                }
                if ($buildingskilled > $steal{$inneraction}{maxget}*$alldefbuildings/100) {$buildingskilled = $steal{$inneraction}{maxget}*$alldefbuildings/100;}
                
                // Defartefakt
				$artefakte[$artefakt_id_def][bonusname] == "spy_damagereduction_bonus"  ? ($buildingskilled *=   ($artefakte[$artefakt_id_def][bonusvalue] / 100)) : 1;
                
                //if (!$atwar) {
					$buildingskilled = floor ($buildingskilled * $gbmod);
                //}
				// Min 1 Gebäude killen
				if ($buildingskilled < 1 && $alldefbuildings > 0) {
					$buildingskilled = 1;
				}
                $killed = array();
                foreach ($buildings as $value) {
                    $killed{$value{name_intern}} = round ($rel{$value{name_intern}} * $buildingskilled);
					// by R4bbiT - 22.02.11 - Hier werden auch nur noch die Gebäude angezeigt, die auch zerstört wurden
                    if($killed{$value{name_intern}} > 0){
						array_push($ausgabename,$value{name});
                    	array_push($ausgabewert,pointit($killed{$value{name_intern}}));
					}
                }
				// by R4bbiT - 22.02.11 - zusätzlich wird angezeigt, wie viele Gesamt kaputt gingen
				array_push($ausgabename,'Gesamt');
				array_push($ausgabewert,pointit(array_sum($killed)));
                $ausgabemessage = "Sie konnten folgende Gebäude zerstören:";
                #$werte="|";
                foreach ($buildings as $value) {
					// s.o.
					if($killed{$value{name_intern}} > 0){
						$werte.="<tr class=tableInner1 width=60% align=left><td>".$value{name}."</td><td width=40% align=right>".$killed{$value{name_intern}}."</td></tr>";
					}
                }
				// s.o.
				$werte.="<tr class=tableInner1 width=60% align=left><td>Gesamt</td><td width=40% align=right>".pointit(array_sum($killed))."</td></tr>";
                $werte.="|".$messageadd;
                $messageid = 38;

                // Dbrequests:
                    // Spyactions
                    $action = "update status set spyactions = spyactions - $spyactionsused where id =".$status{id};
                    array_push($queries,$action);
                    // Units abziehen
                    $action = "update status set ";
                    foreach ($killed as $key => $value) {
                        $action .="$key = $key - $value,";
                        $defstatus{$key} -= $value;
                    }
                    $defnw = nw($defstatus{id});
                    $action = chopp($action);
                    $action.= ",nw = ".$defnw." where id = ".$defstatus{id};
                    array_push($queries,$action);
                // Dbrequests:
            }
			//Rückkehr verzögern
			elseif ($inneraction == "delayaway") {
				$unitcnt = floor(single("select sum(number) from military_away where user_id = ".$defstatus[id]." and time < ($time+3600*19)"));
				$queries[] = "update military_away set time = time + 3600 where user_id=".$defstatus[id]." and time < ($time+3600*19)";
				
				$ausgabemessage = "Sie haben die Heimkehr aller Einheiten um eine Stunde verzögert";
				array_push($ausgabename,"Anzahl betroffene Einheiten:");
				array_push($ausgabewert,"$unitcnt");
				$werte="Feindliche Spione haben die Heimkehr unserer Militäreinheiten um eine Stunde verzögert!";
				$werte.=$messageadd;
				$messageid = 48;

				// Dbrequests:
					// Spyactions
					$action = "update status set spyactions = spyactions - $spyactionsused where id =".$status{id};
					array_push($queries,$action);
			}
			//Forschungen zerstören
			elseif ($inneraction == "killsciences") {
				$scienceprefs = assocs("select *,concat(name,typenumber) as keyname from sciences","keyname");
				$maxkilllevel = 7;

				// Ermittlung der zu zerstörenden Forschung
				//pvar($defsciences);
				$gegnerforschungen = count($defsciences);
				//pvar($gegnerforschungen,gf);

				$limit = 0;
				while (!$done && $limit < 100 ) {
					//mt_srand($time);
					$zufall = mt_rand(1,$gegnerforschungen);
					//pvar($zufall,zufall);
					$count = 1;
					foreach ($defsciences as $key => $value) {
						if ($count == $zufall) {
							if ($scienceprefs{$key}{level} <= $maxkilllevel) {
								$killed = $scienceprefs{$key}{gamename}." Level ".$value;
								$logname = $key;
								if ($value == 1) {
									$queries[] ="delete from usersciences where user_id = ".$defstatus[id]." and name='$key'";
								}
								elseif ($value > 1) {
									$queries[] = "update usersciences set level=level-1 where user_id = ".$defstatus[id]." and name='$key'";
								}
								$done = 1;
							}
							break;
						}
						$count++;
					}
					$limit++;
				}
				if ($limit == 100) {
					$errormsg	= "Es konnte keine passende zerstörbare Forschung ermittelt werden";
				}
				//

				$ausgabemessage = "Sie konnten folgende Forschung zerstören:";
				array_push($ausgabename,$killed);
				array_push($ausgabewert,"zerstört");
				$werte.="<tr class=tableInner1 width=60% align=left><td>".$killed."</td><td width=40% align=right></td></tr>";
				$werte.="|".$messageadd;
				$messageid = 39;

				// Dbrequests:
					// Spyactions
					$action = "update status set spyactions = spyactions - $spyactionsused where id =".$status{id};
					array_push($queries,$action);
					// Units abziehen
					$action = "update status set ";
					$defnw = nw($defstatus{id});
					$action.= "nw = ".$defnw." where id = ".$defstatus{id};
					array_push($queries,$action);
				// Dbrequests:
            }

			// Message verschicken
            $action="insert into message_values (id,user_id,time,werte) values ($messageid,'".$defstatus{id}."','$time','$werte')";
            array_push ($queries,$action);
        } // Wenn Sabotageaktion


        else if ($aktionen{$inneraction}{type} == "ip") {
//        echo ("INNERACTION: $inneraction <br>");

            // UNITINTEL
            if ($inneraction == "unitintel1") {
            	##
            	## DEF
            		$abweichung = 15; // Stdabweichung 15% bei Unitintel
            		$ab_abweichung = 30; // Abweichung bei Stealth bombern
            		$artefakte[$artefakt_id_def][bonusname] == "spy_precision_bonus" ? $abweichung = 30 : 1; // Das Spyartefakt verursacht 30% Abweichung
            	##
            	##
            	
            	
                $ausgabemessage = "Der Konzern ".$defstatus{syndicate}." (#".$defstatus{rid}.") besitzt folgende Militäreinheiten und Ressourcen: (Daten über Militäreinheiten sind zu $abweichung% ungenau)";
                $data = assoc("select offspecs,defspecs,elites,elites2,techs,money,metal,energy,sciencepoints from status where id =".$defstatus{id});
                $defmarketres = getmarket($defstatus{id});
                $i=0;
                foreach ($data as $key => $value) {
                    if ($i >= count($defunitstats)) {
	                        array_push($ausgabename,$resstats{$key}{name});
	                        array_push($ausgabewert,pointit($value+$defmarketres{$key}+$dtres[$key]));
                    }
                    else {
						if ($defstatus[race] != "sl" or $key != "techs")	{
	                        $rand = mt_rand((100-$abweichung)*100000,(100+$abweichung)*100000)/100000;
						}
						else {
	                        $rand = mt_rand((100-$ab_abweichung)*100000,(100+$ab_abweichung)*100000) / 100000;
						}
						$temp = round(($value+$defmarket[$key])*$rand/100);
						array_push($ausgabename,$defunitstats{$key}{name});
						array_push($ausgabewert,pointit($temp));
                    }
                    $i++;
               }
               // Artefakt, als es noch Synarmee gab
		/*
		Deaktiviert by R4bbiT - 29.03.12
					$synarmy = assoc("select offspecs,defspecs from syndikate where synd_id=$defstatus[rid]");
					$rand = mt_rand((100-$abweichung)*100000,(100+$abweichung)*100000)/100000;
					$synarmy[offspecs] = floor( $synarmy[offspecs]*$rand/100);
					$rand = mt_rand((100-$abweichung)*100000,(100+$abweichung)*100000)/100000;
					$synarmy[defspecs] = floor($synarmy[defspecs]*$rand/100);
					$tpl_result['army_ranger']=pointit($synarmy[defspecs]);
					$tpl_result['army_rines']=pointit($synarmy[offspecs]);

				$partner_settings = assocs("select id, bonus from partnerschaften_general_settings", "id");
				
				$unitintelausgabe .= "<br>
					<table border=0 align=center cellspacing=0 cellpadding=0 class=\"tableOutline\"><tr><td>
						 <table border=0 cellspacing=1 cellpadding=3 width=300>
							<tr><td colspan=2 class=\"tableHead\"><b>Syndikatsarmee</b></td></tr>
							<tr>
								<td width=200 class=\"tableInner1\">Ranger</td>
								<td width=100 class=\"tableInner1\" align=right>".pointit($synarmy[defspecs])."&nbsp;&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td width=200 class=\"tableInner1\">Marines</td>
								<td width=100 class=\"tableInner1\" align=right>".pointit($synarmy[offspecs])."&nbsp;&nbsp;&nbsp;</td>
							</tr>
						</table>
					</table>
					<br>
				";
				
				$partnerausgabe="<br>
				<center>
					<table cellspacing=1 width=\"400\" cellpadding=5 border=0 class=\"tableOutline\" align=center >
						<tr>
							<td class=\"tableHead\" height=20>Partnerschaftsboni <a href=\"../index.php?action=docu&kat=2&aid=27\" class=linkAufsiteBg target=\"_blank\"><img src=\"".$ripf."_help.gif\" border=\"0\" align=\"absmiddle\"></a></td>
						</tr>
						<tr>
							<td class=\"tableInner1\">
								<table cellspacing=0 cellpadding=0 border=0  class=\"tableInner1\">
									<tr>
										<td>
											";
											$tpl_result['pbs']=array();
											if ($defpartner && count($defpartner) > 0) {
												foreach ($defpartner as $ky => $vl) {
													for ($i = 1; $i <= $vl; $i++) {
														$partnerschaften_temp[] = "<li>".$partner_settings[$ky][bonus];
														array_push($tpl_result['pbs'],$partner_settings[$ky][bonus]);
													}
												}
													$partnerausgabe .= "<ul>".join("<br>", $partnerschaften_temp)."</ul>";
											}
											else { $partnerausgabe .= "Noch keine Boni gewählt!"; }
					$partnerausgabe.="
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</center>
				";
				
				//$unitintelausgabe.=$partnerausgabe;
				
				if ($defpartner && count($defpartner) > 0) {
					$tpl_result['pbs']=array();
					foreach ($defpartner as $ky => $vl) {
						for ($i = 1; $i <= $vl; $i++) {
							array_push($tpl_result['pbs'],$partner_settings[$ky][bonus]);
						}
					}
				}
				else { $partnerausgabe .= "Noch keine Boni gewählt!"; }
				unset($partnerschaften_temp);
		*/		
				// Partnerbonus
            	if ($defpartner[25] || $defsciences["glo21"]) {
            		$rand = mt_rand(0,100);
					$wkt = $defpartner[25] * GEGENSPIONAGE_WKT + $defsciences["glo21"] * GEGENSPIONAGE_WKT_GLO21;
            		if ($rand <= $wkt && !$mentor) {
						$unitstats = getunitstats($status{race});            			
		                $ausgabemessagePB = "<center><strong>Gegenspionage erfolgreich!</strong><br><br>Sie konnten die Spionageaktion <strong>Konzernspion</strong> gegen <strong>".$status{syndicate}." (#".$status{rid}.")</strong><br>- ".pointit($status[nw])."Nw, ".pointit($status[land])."ha am ".mytime($time, "noDayReplacement")." Uhr -<br><strong>erfolgreich</strong> ausführen.<br><br>Der Konzern ".$status{syndicate}." (#".$status{rid}.") besitzt folgende Militäreinheiten und Ressourcen: (Daten über Militäreinheiten sind zu $abweichung% ungenau)</center>";
		                
		                
		                $dataPB = assoc("select offspecs,defspecs,elites,elites2,techs,money,metal,energy,sciencepoints from status where id =".$status{id});
		                $marketresPB = getmarket($status{id});
            			
						$ausgabemessagePB.="
						
					            <table border=0 align=center cellspacing=0 cellpadding=0 class=\"tableOutline\"><tr><td>
					            <table border=0 cellspacing=1 cellpadding=3 width=300>
					            <tr><td colspan=2 class=\"tableHead\"><b>Übersicht</b></td></tr>
						
						";
		                $i=0;
		                foreach ($dataPB as $key => $value) {
		                    if ($i >= count($unitstats)) {
			                        $a1 = $resstats{$key}{name};
			                        $a2 = pointit($value+$marketres{$key}+$tresPB[$key]);
		                    }
		                    else {
								if ($status[race] != "sl" or $key != "techs")	{
			                        $rand = mt_rand((100-$abweichung),(100+$abweichung));
								}
								else {
			                        $rand = mt_rand((100-$ab_abweichung),(100+$ab_abweichung));
								}
								$temp = round(($value+$marketresPB[$key])*$rand/100);
								$a1 = $unitstats{$key}{name};
								$a2 = pointit($temp);
								
								
								
		                    }
		                    $i++;
		                   
		                    $ausgabemessagePB.="
					            <tr>
					            <td width=200 class=\"tableInner1\">".$a1."</td>
					            <td width=100 class=\"tableInner1\" align=right>".$a2."&nbsp;&nbsp;&nbsp;</td>
					            </tr>
		                    
		                    ";
		               }
		               $ausgabemessagePB.="
		               	</table></td></tr></table>
		               	<br>
		               ";
		               
		               // Artefakt, als es noch Synarmee gab
					   /*if (!isBasicServer($game)) {
							$synarmyPB = assoc("select offspecs,defspecs from syndikate where synd_id=$status[rid]");
							$synarmyPB[offspecs] = floor( $synarmyPB[offspecs]*$rand/100);
							$synarmyPB[defspecs] = floor($synarmyPB[defspecs]*$rand/100);
							$unitintelausgabePB="<br>
					            <table border=0 align=center cellspacing=0 cellpadding=0 class=\"tableOutline\"><tr><td>
		   					         <table border=0 cellspacing=1 cellpadding=3 width=300>
										<tr><td colspan=2 class=\"tableHead\"><b>Syndikatsarmee</b></td></tr>
										<tr>
											<td width=200 class=\"tableInner1\">Ranger</td>
											<td width=100 class=\"tableInner1\" align=right>".pointit($synarmyPB[defspecs])."&nbsp;&nbsp;&nbsp;</td>
										</tr>
										<tr>
											<td width=200 class=\"tableInner1\">Marines</td>
											<td width=100 class=\"tableInner1\" align=right>".pointit($synarmyPB[offspecs])."&nbsp;&nbsp;&nbsp;</td>
										</tr>
									</table>
								</table>
								<br>
							";
						}
						unset($partnerschaften_temp);
						$partnerausgabePB = "
						<center>
							<table cellspacing=1 width=\"400\" cellpadding=5 border=0 class=\"tableOutline\" align=center >
								<tr>
									<td class=\"tableHead\" height=20>Partnerschaftsboni <a href=\"../index.php?action=docu&kat=2&aid=27\" class=linkAufsiteBg target=\"_blank\"><img src=\"".$ripf."_help.gif\" border=\"0\" align=\"absmiddle\"></a></td>
								</tr>
								<tr>
									<td class=\"tableInner1\">
										<table cellspacing=0 cellpadding=0 border=0  class=\"tableInner1\">
											<tr>
												<td>
													";
													if ($partner && count($partner) > 0) {
														foreach ($partner as $ky => $vl) {
															for ($i = 1; $i <= $vl; $i++) {
																$partnerschaften_temp[] = "<li>".$partner_settings[$ky][bonus];
															}
														}
															$partnerausgabePB .= "<ul>".join("<br>", $partnerschaften_temp)."</ul>";
													}
													else { $partnerausgabePB .= "Noch keine Boni gewählt!"; }
							$partnerausgabePB.="
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</center>
						";*/
		               
	            		$PBausgabe = $ausgabemessagePB.$partnerausgabePB.$unitintelausgabePB;
			            $action="insert into message_values 
			            		(id,user_id,time,werte) 
			            		values 
			            		(48,'".$defstatus{id}."','$time','$PBausgabe')";
	            		select($action);
            		}
            		
            	}
					

            }
            // UNITINTEL 2
            if ($inneraction == "unitintel2") {
            	
				$tpl_table = array();
            	##
            	## DEF
            		$abweichung = 0; // Stdabweichung 0% bei Unitintel2
            		$artefakte[$artefakt_id_def][bonusname] == "spy_precision_bonus" ? $abweichung = 10 : 1; // Das Spyartefakt verursacht 10% Abweichung bei genauem militärspion
            	##
            	##
            	
                $ausgabemessage = "Der Konzern ".$defstatus{syndicate}." (#".$defstatus{rid}.") besitzt folgende Militäreinheiten:";
                $abweichung ? $ausgabemessage.="<br><b>Daten über Militäreinheiten sind zu $abweichung% ungenau!</b>" : 1;
				$tpl_result['abweichung'] = $abweichung;
				
                $data = assoc("select offspecs,defspecs,elites,elites2,techs from status where id =".$defstatus{id});
                foreach ($data as $key => $value) {
                		$rand_mod = (mt_rand((100-$abweichung),(100+$abweichung)) / 100);
                        array_push($ausgabename,$defunitstats{$key}{name});
                        array_push($ausgabewert,pointit(round($rand_mod*($value+$defmarket[$key]))));
                }
           		$rand_mod = (mt_rand((100-$abweichung),(100+$abweichung)) / 100);
                $milbuild = rows("select bm.unit_id,(round(number*$rand_mod)) as number,time, mus.sort_order from build_military as bm, military_unit_settings as mus where bm.unit_id = mus.unit_id and user_id=".$defstatus{id});
           		$rand_mod = (mt_rand((100-$abweichung),(100+$abweichung)) / 100);
                $milaway = rows("select ma.unit_id,(round(number*$rand_mod)) as number,time, mus.sort_order from military_away as ma, military_unit_settings as mus where ma.unit_id = mus.unit_id and user_id=".$defstatus{id});
                $milnames = assocs("select name, unit_id from military_unit_settings where race='".$defstatus[race]."' or race='all' ORDER BY sort_order");
				$milnamesByType = assocs("select name, type, unit_id from military_unit_settings where race='".$defstatus[race]."'", "type");
                // Templatevariablen für Ausgabe

                $t = $time; // interne Zeit für Beraterskript
                $class_spaltenbezeichner= "tableHead";
                $class_name				= "gelb10";
                $class_anzahl			= "ver10w";
                $class_null				= "hellgruen10";
                $tbl_bg_color = "#718AB3";

                // Militärbau
				$this_table = array();
				
                foreach ($milbuild as $value)	{
                	$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
                	$milsorted[$value[3]-1][$x] += $value[1];
                }
				//pvar($milbuild);
                if (sizeof($milsorted))	{
				
					$millbuil_str='
						<b><u>Militärausbildung</u></b><br><br>
						<table width="600" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
							<tr>
								<td>
									<table  border="0" cellspacing="1" width="100%" cellpadding="3">
										<tr class='.$class_spaltenbezeichner.'>
											<td align=center>#</td><td align=middle> &nbsp;1 </td>
											<td align=middle> &nbsp;2 </td>
											<td align=middle> &nbsp;3 </td>
											<td align=middle> &nbsp;4 </td>
											<td align=middle> &nbsp;5 </td>
											<td align=middle> &nbsp;6 </td>
											<td align=middle> &nbsp;7 </td>
											<td align=middle> &nbsp;8 </td>
											<td align=middle> &nbsp;9 </td>
											<td align=middle> 10 </td>
											<td align=middle> 11 </td>
											<td align=middle> 12 </td>
											<td align=middle> 13 </td>
											<td align=middle> 14 </td>
											<td align=middle> 15 </td>
											<td align=middle> 16 </td>
											<td align=middle> 17 </td>
											<td align=middle> 18 </td>
											<td align=middle> 19 </td>
											<td align=middle> 20 </td>
										</tr>';
					
					$this_table['name'] = "Militärausbildung";
					$this_table['class'] = $class_spaltenbezeichner;
					$tpl_rows = array();
					foreach ($milnames as $ky => $vl){
						$millbuil_str.='<tr class="tableInner1">';
						$millbuil_str.='<td width=105> '.$vl[name].'</td>';
						$thisRowA = array();
						for ($o = 0, $u = 1; $o <= 19; $o++, $u++)	{
							if ($milsorted[$ky][$o]) {$milsorted[$ky][$o] = pointit($milsorted[$ky][$o]);
								array_push($thisRowA ,$milsorted[$ky][$o]);
								$millbuil_str.= '<td align=middle>'.$milsorted[$ky][$o].'</td>';}
							else {
								array_push($thisRowA ,"-");
								$millbuil_str.= '<td align=middle>-</td>';
							}
						}
						array_push($tpl_rows,array("name"=>$vl[name],"details"=>$thisRowA));
						$millbuil_str.= "</tr>";
					}
					$millbuil_str .= '</table>
								</td>
							</tr>
						</table>';
					$this_table['rows'] = $tpl_rows;		
                }	else	{ $this_table['error'] = "Kein Militär in Bau!";$millbuil_str='<b><u>Kein Militär in Bau!</u></b><br><br>';}

				array_push($tpl_table,$this_table);
                // Militäraway
				$this_table = array();

                foreach ($milaway as $value)	{
                	$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
                	$milawaysorted[$value[3]-1][$x] += $value[1];

                }

                if (sizeof($milawaysorted))	{
					$millawa_str='
						<b><u>Heimkehrendes Militär</u></b><br><br>
						<table width="600" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
							<tr>
								<td>
									<table  border="0" cellspacing="1" width="100%" cellpadding="3">
										<tr class='.$class_spaltenbezeichner.'>
											<td align=center>#</td><td align=middle> &nbsp;1 </td>
											<td align=middle> &nbsp;2 </td>
											<td align=middle> &nbsp;3 </td>
											<td align=middle> &nbsp;4 </td>
											<td align=middle> &nbsp;5 </td>
											<td align=middle> &nbsp;6 </td>
											<td align=middle> &nbsp;7 </td>
											<td align=middle> &nbsp;8 </td>
											<td align=middle> &nbsp;9 </td>
											<td align=middle> 10 </td>
											<td align=middle> 11 </td>
											<td align=middle> 12 </td>
											<td align=middle> 13 </td>
											<td align=middle> 14 </td>
											<td align=middle> 15 </td>
											<td align=middle> 16 </td>
											<td align=middle> 17 </td>
											<td align=middle> 18 </td>
											<td align=middle> 19 </td>
											<td align=middle> 20 </td>
										</tr>';
					
					$this_table['name'] = "Heimkehrendes Militär";
					$this_table['class'] = $class_spaltenbezeichner;
					$tpl_rows = array();
					foreach ($milnames as $ky => $vl)	{
						$millawa_str.='<tr class="tableInner1">';
						$millawa_str.='<td width=105> '.$vl[name].'</td>';
						$thisRowA = array();
						for ($o = 0, $u = 1; $o <= 19; $o++, $u++)	{
							if ($milawaysorted[$ky][$o]) {
								$milawaysorted[$ky][$o] = pointit($milawaysorted[$ky][$o]);
								array_push($thisRowA ,$milawaysorted[$ky][$o]);
								$millawa_str.= '<td align=middle>'.$milawaysorted[$ky][$o].'</td>';
							}
							else {
								array_push($thisRowA ,"-");
								$millawa_str.= '<td align=middle>-</td>';
							}
						}
						array_push($tpl_rows,array("name"=>$vl[name],"details"=>$thisRowA));
						$millawa_str.= "</tr>";
					}
					$millawa_str .= '</table>
								</td>
							</tr>
						</table>';
					$this_table['rows'] = $tpl_rows;	

                }	else	{
					 $this_table['error'] = "Kein Militär auf Heimkehr!";
					 $millawa_str='<b><u>Kein Militär auf Heimkehr!</u></b><br><br>';
				}

				array_push($tpl_table,$this_table);
				$tpl_result['mill_table'] = $tpl_table;
				
                $unitintel2ausgabe = $millbuil_str."<br><br>".$millawa_str;
				$synarmy = assoc("select offspecs,defspecs from syndikate where synd_id=$defstatus[rid]");
		
				$tpl_result['army_ranger']=pointit($synarmy[defspecs]);
				$tpl_result['army_rines']=pointit($synarmy[offspecs]);

				$partner_settings = assocs("select id, bonus from partnerschaften_general_settings", "id");
				
				$unitintel2ausgabe .= "<br>
					<table border=0 align=center cellspacing=0 cellpadding=0 class=\"tableOutline\"><tr><td>
						 <table border=0 cellspacing=1 cellpadding=3 width=300>
							<tr><td colspan=2 class=\"tableHead\"><b>Syndikatsarmee</b></td></tr>
							<tr>
								<td width=200 class=\"tableInner1\">Ranger</td>
								<td width=100 class=\"tableInner1\" align=right>".pointit($synarmy[defspecs])."&nbsp;&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td width=200 class=\"tableInner1\">Marines</td>
								<td width=100 class=\"tableInner1\" align=right>".pointit($synarmy[offspecs])."&nbsp;&nbsp;&nbsp;</td>
							</tr>
						</table>
					</table>
					<br>
				";
				
				$partnerausgabe="
				<center>
					<table cellspacing=1 width=\"400\" cellpadding=5 border=0 class=\"tableOutline\" align=center >
						<tr>
							<td class=\"tableHead\" height=20>Partnerschaftsboni <a href=\"../index.php?action=docu&kat=2&aid=27\" class=linkAufsiteBg target=\"_blank\"><img src=\"".$ripf."_help.gif\" border=\"0\" align=\"absmiddle\"></a></td>
						</tr>
						<tr>
							<td class=\"tableInner1\">
								<table cellspacing=0 cellpadding=0 border=0  class=\"tableInner1\">
									<tr>
										<td>
											";
											$tpl_result['pbs']=array();
											if ($defpartner && count($defpartner) > 0) {
												foreach ($defpartner as $ky => $vl) {
													for ($i = 1; $i <= $vl; $i++) {
														$partnerschaften_temp[] = "<li>".$partner_settings[$ky][bonus];
														array_push($tpl_result['pbs'],$partner_settings[$ky][bonus]);
													}
												}
													$partnerausgabe .= "<ul>".join("<br>", $partnerschaften_temp)."</ul>";
											}
											else { $partnerausgabe .= "Noch keine Boni gewählt!"; }
					$partnerausgabe.="
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</center>
				";
				 //$unitintel2ausgabe .= "<br><br>".$partnerausgabe;
				
					
            }
            // BUILDINTEL
            elseif ($inneraction == "buildintel") {
                $ausgabemessage = "Der Konzern ".$defstatus{syndicate}." (#".$defstatus{rid}.") besitzt folgende Gebäude:";
                $buildings = assocs("select * from buildings","name_intern");
                $selectwhat = "";
                foreach ($buildings as $key => $value) {
					$traces = explode(",",$value[race]);
																							// By R4bbiT - 22.02.11 - Es werden  nur noch Gebäude angezeigt, von denen mind 1 existiert
					if ((in_array($defstatus[race],$traces) || $value[race] == "all") && $defstatus[$value[name_intern]] > 0) {
						array_push($ausgabename,$buildings{$key}{name});
						array_push($ausgabewert,pointit($defstatus[$value[name_intern]]));
					}
                }
				$underconstruction = single("select sum(number) from build_buildings where user_id ='".$defstatus[id]."' and building_name != 'land'");
				$underconstruction = (int) $underconstruction;
				$allbuildings = getallbuildings($defstatus{id});
				$allbuildings = (int) $allbuildings;
				$freeland = (int) ($defstatus[land] -  $allbuildings - $underconstruction);
				$unbebaut = $freeland;
				array_push($ausgabename,"in bau");
				array_push($ausgabewert,pointit($underconstruction));
				array_push($ausgabename,"unbebaut");
				array_push($ausgabewert,pointit($unbebaut));
            } //podintel
			            elseif ($inneraction == "podintel") {
                $ausgabemessage = "Die Konzerne des Syndicate (#".$defstatus{rid}.") besitzen folgende Lagerguthaben<br>\n(Daten über Lagerstände sind zu 10% ungenau)";
                $pods = assocs("select syndicate, podpoints from status where rid=".$defstatus{rid}." and alive=1");
                $selectwhat = "";
				
				foreach($pods as $pod){
					$abweichung = 10; //lagerspy 10% ungenau
					$randm = (mt_rand((100-$abweichung),(100+$abweichung)) / 100);
					array_push($ausgabename,$pod['syndicate']." (#".$defstatus{rid}.")");
					array_push($ausgabewert,pointit($pod['podpoints']*$randm));
				}
               
            }
            //SCIENCEINTEL
            elseif ($inneraction == "scienceintel") {
                $ausgabemessage = "Aktueller Technologiebericht von ".$defstatus{syndicate}." (#".$defstatus{rid}.")";
                $sciencesettings = assocs("select concat(name,typenumber) as name,gamename from sciences","name");
                $data = assocs("select *,u.level as ulevel from usersciences u,sciences s where user_id =".$defstatus{id}." and u.name = concat(s.name,s.typenumber) order by treename desc, s.level asc, s.typenumber desc");
                $current_tree = "";
                foreach ($data as $key => $value) {
                	if ($current_tree != $value[treename]) {
                		if ($value[treename] == "mil") $header = "MILITARY SCIENCES"; 
                		if ($value[treename] == "ind") $header = "INDUSTRIAL SCIENCES"; 
                		if ($value[treename] == "glo") $header = "INTELLIGENCE SCIENCES"; 
						array_push($ausgabename,"---------- <strong>$header</strong>");
						array_push($ausgabewert,"----------");
                	}
                	
                	$current_tree = $value[treename];
                    array_push($ausgabename,$value{gamename});
                    array_push($ausgabewert,"Stufe ".$value{ulevel});
                }
                if (count ($data) == 0) {
                    array_push($ausgabename,"Keine Forschungen");
                    array_push($ausgabewert,"vorhanden");
                }
            }
            // NEWSINTEL
            elseif ($inneraction == "newsintel") {
                // COPIED FROM AKTUELLES.PHP START HERE
                    $hours = date("H");
                    $minutes = date("i");
                    $seconds = date("s");
                    $day_begin_time = $time - $hours * 3600 - $minutes * 60 - $seconds;

                    $aktienprozente = 0;
                    $totalaktien = 0;
                    $aktstring = "";
                    $tcdata = array();
                    $temptime = 0;

                    $news_today = "";
                    $news_yesterday = "";
					
					$tpl_yesterday=array();
					$tpl_today=array();
					
                    // CHANGE HERE: RID AUF DEFSTATUS RID
                    $tcdata = assocs("select time, message, id from towncrier where rid=".$defstatus[rid]." order by time desc");

                    foreach ($tcdata as $vl)	{
                    	if ($time - $vl[time] < ( 60 * 60 * 24 * 2))	{
                    		$temptime = date("H:i", $vl[time]);
                    		if ($vl[time] > $day_begin_time)	{
                    			$news_today .= "<tr class=\"tableInner1\"><td width=60 align=center>$temptime Uhr</td><td>".$vl[message]."</td></tr>";
								array_push($tpl_today, array("time"=>$temptime, "msg"=>$vl[message]));
                    		}
                    		elseif ($vl[time] >= $day_begin_time - 24 * 3600)	{
                    			$news_yesterday .= "<tr class=\"tableInner1\"><td width=60 align=center>$temptime Uhr</td><td>".$vl[message]."</td></tr>";
								array_push($tpl_yesterday, array("time"=>$temptime, "msg"=>$vl[message]));
                    		}
                    	}
                    }
					
					$tpl_result['news']=array();
					array_push($tpl_result['news'],array("name"=>"Heute","details"=>$tpl_today));
					array_push($tpl_result['news'],array("name"=>"Gestern","details"=>$tpl_yesterday));

                    if (!$news_today): $news_today = "<tr class=\"tableInner1\"><td width=570 colspan=2 align=center>Keine Daten vorhanden</td></tr>"; endif;
                    if (!$news_yesterday): $news_yesterday = "<tr class=\"tableInner1\"><td width=570 colspan=2 align=center>Keine Daten vorhanden</td></tr>"; endif;

                    // Die ersten 2 Zeilen wurden angepasst.
                   
                
                // Synforschungen noch anzeigen:
                
                $synfos = assoc("select energyforschung,sabotageforschung,creditforschung from syndikate where synd_id = ".$defstatus[rid]."");
                $sciencesettings = assocs("select concat(name,typenumber) as name,gamename from sciences","name");
                $matches = array( 
                	 "energyforschung" => "ind16",
                	 "creditforschung" => "ind15",
                	 "sabotageforschung" =>"glo12",
                );
                
				$tpl_synfos=array();
				$synfosausgabe = '';
				
				foreach ($synfos as $key => $value) {
					$value = explode("|",$value);
					while (count($value) < 3) {
						$value[] = 0;
					}
					
					$tpl_levels=array();
					$tpl_fosname = $sciencesettings[$matches[$key]]['gamename'];
					$synfosausgabe .= '<tr class="tableInner1">
										<td>
											<b>'.$tpl_fosname.':</b>
										</td>
										<td>';
					
					foreach ($value as $key => $tvl) {
						array_push($tpl_levels, "Level ".($key+1).": ".$tvl);
						$synfosausgabe .= '<b>Level '.($key+1).': '.$tvl.'</b><br>';
					}
					
					$synfosausgabe .= '	</td>
									</tr>';
					
					array_push($tpl_synfos, array("name"=>$tpl_fosname,"levels"=>$tpl_levels));
				}
     
				
				$tpl_result['synfos']=$tpl_synfos;
				
				$newsintelausgabe='
				
				<p align="center">Aktuelles aus Syndikat (#'.$defstatus[rid].')</p>
				<center>
					<table cellpadding="0" cellspacing="0" border="0" class="tableOutline">
						<tr>
							<td>
								<table  border="0" cellspacing="1" cellpadding="5" width=570>
									<tr class="tableHead">
										<td colspan=2 align="center" valign="middle" height="15">Heute</td>
									</tr>
									'.$news_today.'
								</table>
							</td>
						</tr>
					</table>
					<br>
					<table cellpadding="0" cellspacing="0" border="0" class="tableOutline">
						<tr>
							<td>
								<table  border="0" cellspacing="1" cellpadding="5" width=570>
									<tr class="tableHead">
										<td colspan=2 align="center" valign="middle" height="15">Gestern</td>
									</tr>
									'.$news_yesterday.'
								</table>
							</td>
						</tr>
					</table>
					<br>
				<br>
				<table cellpadding="0" cellspacing="0" border="0" class="tableOutline">
					<tr>
						<td>
							<table  border="0" cellspacing="1" cellpadding="5" width=370>
								<tr class="tableHead">
									<td colspan=4 align="center" valign="middle" height="15">Syndikatsforschungen:</td>
								</tr>
								'.$synfosausgabe.'
							</table>
						</td>
					</tr>
				</table>
			</center>';
            }
			
			

            $action ="update status set spyactions = spyactions - $spyactionsused where id = ".$status{id};
            array_push($queries,$action);
            if ($messageadd) {
	            $werte = "Unser Spyweb hat Spionageaktivitäten von <i>$status[syndicate] (#$status[rid])</i> in unserem Konzern registriert.";
	            // SPYWEB EINTRAG 
	            if ($defsciences[glo9] >= 2) $werte.=" Ausgeführte Spionageaktion: <b>".$aktionen{$inneraction}{name}."</b>.";
				$werte.= "<br>".$jobadd;
	            $action="insert into message_values (id,user_id,time,werte) values (48,'".$defstatus{id}."','$time','$werte')";
	            array_push ($queries,$action);
            }

        } // Spionageaktione
		//
        // Ausgabe der erfolgreichen Spionageaktion
		//
		
		
        $erfausgabe="
            <br><br><center>
            Sie konnten die Spionageaktion <b>".$aktionen{$inneraction}{name}."</b> gegen
            <b>".$defstatus{syndicate}." (#".$defstatus{rid}.")</b><br>- ".pointit($defstatus[nw])."Nw, ".pointit($defstatus[land])."ha am ".mytime($time, "noDayReplacement")." Uhr -<br>
			<b>erfolgreich</b> ausführen.<br><br>
            <br>$ausgabemessage<br>   
            <br>
            <table border=0 align=center cellspacing=0 cellpadding=0 class=\"tableOutline\"><tr><td>
            <table border=0 cellspacing=1 cellpadding=3 width=300>
        ";
		
        if ($inneraction != "newsintel") {
            $erfausgabe.="<tr><td colspan=2 class=\"tableHead\"><b>Übersicht</b></td></tr>";
        }
		
		$tpl_ausgabe = array();
		
        $num = count($ausgabename);
        for ($i=0;$i < $num;$i++) {
			
			array_push($tpl_ausgabe, array("name" => $ausgabename[$i], "value" => $ausgabewert[$i]));
			
			if($ausgabewert[$i] == '----------'){
				$erfausgabe.="
				<tr>
				<td width=300 class=\"tableInner1\" colspan=2 align=center>".$ausgabename[$i]." ".$ausgabewert[$i]."</td>
				</tr>
				";
			}
			else{
				$erfausgabe.="
				<tr>
				<td width=200 class=\"tableInner1\">".$ausgabename[$i]."</td>
				<td width=100 class=\"tableInner1\" align=right>".$ausgabewert[$i]."&nbsp;&nbsp;&nbsp;</td>
				</tr>
				";
			}
			$resultadd = preg_replace("/\./","",$ausgabewert[$i]);
            $resultstring .= $resultadd."|";
			if ($inneraction == "killsciences") {
				$resultstring=$logname."|";
			}
        }
        $resultstring = chopp($resultstring);
        $erfausgabe.="</table></td></tr></table><br><br></center>";
        if ($inneraction == "unitintel2" || $inneraction == "unitintel1") {$erfausgabe.=$unitintel2ausgabe;$erfausgabe.=$unitintelausgabe;$erfausgabe.=$partnerausgabe;}
        if ($inneraction == "newsintel") {$erfausgabe.=$newsintelausgabe;}
		
		$tpl_result['header'] = $ausgabemessage;
		$tpl_result['ausgabe'] = $tpl_ausgabe;

		
		
		//
		// Jobausgabe ?
		// Wenn ja, Job Beenden und meldungen verschicken und...
		//
		
		if ($job[target_id] == $defstatus[id] && $job[type] == $inneraction) {
		
			$tpl->assign("ISJOB",true);
			
			$smeldung = "Sie haben ihren Auftrag erfolgreich erfüllt, sie haben<br>";
			if ($job[money] > 0) {
				$smeldung.= pointit($job[money])." Credits<br>";
				$status[money] += $job[money];
			}
			$status[nw] = nw($status[id]);
			$smeldung.="für die Ausführung dieses".($job['anonym']?" anonymen ":" ")."Auftrags erhalten.";
			s($smeldung);
			$queries[] = "insert into jobs_logs
							(user_id,acceptor_id,target_id,type,money,energy,metal,sciencepoints,inserttime,onlinetime,accepttime,anonym,finishtime,success)
							values
							($job[user_id],$job[acceptor_id],$job[target_id],'$job[type]',$job[money],$job[energy],$job[metal],$job[sciencepoints],$job[inserttime],$job[onlinetime],$job[accepttime],$job[anonym],$time,1)
			";
			if ($job[number] > 1) {
				$queries[] ="update jobs set number=number-1,acceptor_id=0,accepttime=0 where id=$job[id]";
			}
			else {
				$queries[] = "delete from jobs where id=$job[id]";
			}
			$jmessage = "Der angenommene Auftrag wurde erfolgreich ausgeführt:<br><br>";
			$jmessage.=$erfausgabe."";
			$queries[] ="
				insert into message_values
					(id,user_id,time,werte)
					values
					(44,$job[user_id],$time,'$jmessage')
			";
			$queries[] ="update status set money=$status[money],metal=$status[metal],energy=$status[energy],sciencepoints=$status[sciencepoints],nw=$status[nw] where id=$status[id]";
			$logalt = 1; // Alternatives loggin aktivieren -> auftragnehmer erhält log.
		}
		else {
			$ausgabe= $erfausgabe.$respyausgabe;  # seit Runde 38 kann auf der Ergebnisseite nochmal gegen den gleichen Konzern vorgegangen werden
		}
		$tausgabe = $smeldung.$erfausgabe;;

    } // success == 1

    // Aktion nicht erfolgreich, Auswirkungen:
    else {
    	
    	## Ausgabe zum Respyauswählen auf der Erfolgsseite
		$tpl_respy=array();
		$tpl_respy['targetname']=$targetname;
		$tpl_respy['target']=$target;
		$tpl_respy['rid']=$rid;
		$tpl_respy['actions']=array();
	
		if ($target) {
			array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Spionage --',"disable"=>"disabled"));
			foreach ($aktionen as $key => $value) {
				if ($key == "getpodpoints") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Sabotage --',"disable"=>"disabled"));
				}
				if ($key == "killunits") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- nur bei Racherecht & Krieg --',"disable"=>"disabled"));
				}
				
				if ($key == "killsciences" && $status{spyactions} < KILLSCIENCESACTIONS) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				} elseif (($key == "killsciences" || $key == "killbuildings" || $key == "killunits" || $key == "delayaway") && !$sciences{glo13}) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				}
				else {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>""));
				}
			}
		}
		
		$tpl_respy['ops']=$status{spyactions};
		$tpl_respy['maxops']=$maxSpyOpsThis;
		$tpl->assign('RESPY',$tpl_respy);
    	
       $random = SPYLOSSES/100; //Es werden nun immer fest 1% der spies verloren
       $allspies = 0;
       $defallspies = 0;
	   $tspystats = $spystats;
	   $ttspystats = $spystats;
	   if ($status[race] == "sl") {
	   		$tspystats[bla][type] = "intelspies"; // wegen combatlosses
	   }
	   /*if ($defsciences[glo14]) {
	   		$tspystats[sci][type] = "offspecs"; // wegen combatlosses
	   		//$ttspystats[sci][type] = "offspecs"; // wegen combatlosses
	   }
	   if ($sciences[glo14]) {
	   		//$tspystats[sci][type] = "offspecs"; // wegen combatlosses
	   		$ttspystats[sci][type] = "offspecs"; // wegen combatlosses
	   }
	   */
	   /*
	   if ($status[race] == "sl" && $status[elites2] > 0 ) {
	   		$ttspystats[blub][type] = "elites2"; // wegen combatlosses
	   }
	   */
       foreach ($tspystats as $value) {
            $defallspies += $defstatus{$value{type}}+$defmarket[$value[type]]; // Gesamtsumme
       }
	   foreach ($ttspystats as $value) {
	        $allspies += $offmarket[$value[type]]+$status{$value{type}};
		}
		//$defsciences{glo14} ? $defallspies+=$defstatus{offspecs} : 1;
		//$sciences{glo14} ? $allspies += $status{offspecs} : 1;


       // Es können maximal soviele Spione verloren werden, wie der Gegner besitzt
       $sumlosses = $allspies * $random;
       $sumlosses > $defallspies ? $sumlosses = $defallspies : 1;
	   $sumlosses < 0 ? $sumlosses = 0: 1;
       //echo "SUMLOSSES: $sumlosses";
       $relspies = array();
       foreach ($ttspystats as $value) {
            $rel{$value{type}} = ($offmarket[$value[type]]+$status{$value{type}}) / $allspies;
       }
	   //$sciences{glo14} ? $rel{offspecs} = $status{offspecs} / $allspies : 1;

       $losses = array();
       $action = "update status set ";
       foreach ($ttspystats as $value) {
            $lossbonus = ($sciences{glo2} * GLO2BONUS / 100);
            
			if ($status[seccenters]) {
				$secbonus = $status[seccenters] / $status[land] * SECCENTERBONUS3;
				$lossbonus += $secbonus;
			}
            
            
			$lossbonus += ($partner[12] *LOSSES_PARTNERBONUS / 100);
            if ($status[race] == "sl") {
                $lossbonus += 0.3; // 20% weniger verluste für sl durch racebonus
            }
			$lossbonus > 1 ? $lossbonus = 1 : 0;
            $losses{$value{type}} = ceil($sumlosses * $rel{$value{type}}*(1 - $lossbonus));
			// Sind genug Einheiten auf dem Markt, um diese zuerst töten zu können ?
			if ($offmarket[$value[type]] >= $losses[$value[type]]) {
				$tlosses =$losses[$value[type]];
				// Ziehe Einheiten von angeboten ab
				$stuff = changetype($value[type]);
				$type = $stuff[type];
				$prod_id = $stuff[prod_id];
				$i=0;
				while ($tlosses > 0 && $i < 100) {
					$largestoffer = assoc("select * from market where owner_id=$status[id] and prod_id=$prod_id and type='$type' order by inserttime desc limit 1");
					if ($largestoffer[number] >= $tlosses) {
						$queries[] = "update market set number=number-$tlosses where offer_id=$largestoffer[offer_id]";
						$tlosses = 0;
					}
					else {
						$tlosses -= $largestoffer[number];
						if ($globals[updating] != 1)	select("delete from market where offer_id=$largestoffer[offer_id]");
					}
					$i++;
				}
			}
			// Wenn nicht, töte alle auf dem markt und ziehe dann einheiten daheim ab
			else {
				$stuff = changetype($value[type]);
				$type = $stuff[type];
				$prod_id = $stuff[prod_id];
				$queries[] = "delete from market where owner_id=$status[id] and prod_id=$prod_id and type='$type'";
				$tlosses = $losses{$value{type}};
				$tlosses -= $offmarket[$value[type]];
				// Lösche angebote, ziehe rest von status ab
	            $status{$value{type}} -= $tlosses;
	            $action .="$value[type] = $value[type] - $tlosses,";
			}
       }
	   /*if ($sciences{glo14}) {
	   		$offspeclosses = ceil($sumlosses * $rel{offspecs}*(1 - $lossbonus));
			$status{offspecs} -= $offspeclosses;

	   }*/
		//pvar($status[nw],vorher);
       $status{nw} = nw($status{id});
       $action .= "nw=".$status{nw}.", ";
		//pvar($status[nw],nachher);
       foreach ($losses as $key => $value) {
            $resultstring .= $value."|";
       }
	  /* if ($sciences{glo14}) {
	   		$action .="offspecs = offspecs - $offspeclosses,";
			$resultstring.=$offspeclosses."|";
			$losses{offspecs} = $offspeclosses; // damit array sum nacher stimmt
	   }*/
	   $action .= "spyactions = spyactions-$spyactionsused,";
       $resultstring = chopp($resultstring);
       $action = chopp($action);
       $action .=" where id =".$status{id};
       array_push($queries,$action);
	   $sumlosses = array_sum($losses);
       //$werte = pointit($sumlosses)."|".$status{syndicate}."|".$status{rid};
       
       $werte = "Wir haben <i>".pointit($sumlosses)."</i> Spione von <i>$status[syndicate] (#$status[rid])</i> beim Versuch uns zu infiltrieren exekutiert.";
	   if ($defsciences[glo9] >= 2) $werte.=" Ausgeführte Spionageaktion: <b>".$aktionen{$inneraction}{name}."</b>.";
	   if ($job[target_id] == $defstatus[id] && $job[type] == $inneraction && $defsciences{glo9}) {
			if (!$job[anonym]) {
				$jobuserdata = assoc("select syndicate,rid from status where id = $job[user_id]");
				$werte.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen von <b>".$jobuserdata[syndicate]." (#".$jobuserdata[rid].")</b> erstellten Auftrag!</i>";
			}
			else {
				$werte.="<br><i>Bei der ausgeführten Spionageaktion handelte es sich um einen anonymen Auftrag!</i>";
			}
		}
	   
      // TODO
       
		$action="insert into message_values (id,user_id,time,werte) values (48,'".$defstatus{id}."','$time','$werte')";
		array_push($queries,$action);
		
		$tausgabe = '<center>
			<br><br>
			<b>Spionageeinsatz fehlgeschlagen</b><br>
			<br>
			Sie konnten die Spionageaktion <i>'.$aktionen{$inneraction}{name}.'</i> 
			gegen <b>'.$defstatus{syndicate}.' (#'.$defstatus{rid}.')</b> nicht erfolgreich ausführen.<br>
			<br>
			<table border=0 align=center cellspacing=0 cellpadding=0 class="tableOutline">
				<tr>
					<td>
						<table border=0 cellspacing=1 cellpadding=3 width=300>
							<tr>
								<td colspan=2 class="tableHead">
									<b>Verluste</b>
								</td>
							</tr>';
							

		$tpl_result['losses']=array();
        foreach ($losses as $key => $value) {
			if ($key != "offspecs") {
				$tausgabe .= '	<tr>
									<td width=200 class="tableInner1">'.$spystats{$key}{name}.'</td>
									<td width=100 class="tableInner1">'.pointit($value).'</td>
								</tr>';
				array_push($tpl_result['losses'],array("name"=>$spystats{$key}{name},"value"=>pointit($value)));
			}
        }
		
		$tausgabe.='
						</table>
					</td>
				</tr>
			</table>
			<br><br>
		</center>';

    }
if ($logalt) {$aid = $job[user_id];}
else {$aid = $status[id];}
$spyweb_lvl = single('select level from usersciences where user_id = '.$target.' and name = \'glo9\'');
$spylogaction="insert into spylogs (aid,did,originid,action,success,result,time,offense,defense,random,drid,spyweb_lvl, isAnonym) values ('".$aid."','".$target."','".$status[id]."','".$inneraction."','".$success."','".$resultstring."','".$time."','".$offense."','".$defense."','".$random."','".$rid."','".$spyweb_lvl."', '".($job['anonym']?'1':'0')."')";
//array_push($queries,$action);
// Eintrag in Statstable
if ($success) {
    $setstringoff = ",spyopsdonewon = spyopsdonewon+1";
    $setstringdef = ",spyopssufferedlost = spyopssufferedlost +1";
}
else {
    $setstringoff = "";
    $setstringdef = "";
}
$action = "update stats set $maxupdatestring spies_lost=spies_lost+".(int)$sumlosses.", spyopsdone=spyopsdone+1 $statsstring $setstringoff where konzernid=$id and round=$globals[round]";
array_push($queries,$action);
$action = "update stats set spies_executed=spies_executed+".(int)$sumlosses.",spyopssuffered=spyopssuffered+1 $setstringdef where round=$globals[round] and konzernid=".$defstatus{id};
array_push($queries,$action);


} // Wenn Aktion durchgeführt wurde

//							Daten schreiben									//
$suc = db_write($queries);
if (strlen($spylogaction) > 0 && $globals[updating] != 1 && $suc) {
	select($spylogaction);
	$lastid = mysql_insert_id();
	select("insert into spylogs_berichte (log_id,bericht) values ($lastid,compress('".addslashes($tausgabe)."'))");
}

//							Ausgabe     									//

if ($goon)	{

	$ausgabe = $ausgabe;

	if ((!$inneraction || $inneraction == "prepare") || ($target && $spyable != 1 && $spyable != 4)) {
	
		$tpl->assign('RACHERECHT_AUSGABE', racherecht_ausgabe());
		
		$tpl->assign('RID',$rid);
		$tpl->assign('RIDLEFT',$rid-1);
		$tpl->assign('RIDRIGHT',$rid+1);
	    
	    // Spieler des Syndikats in Schleife durchgehen
		$tpl_players=array();
	    foreach($players as $key => $value) {#
			$thisPlayer=array();
			if(is_mentorprogram($value["id"]) == 1){
				$thisPlayer['mentor']=MENTOR_PIC;
			}
			if ($value[id] == $job[target_id]) {
				$thisPlayer['job'] = true;
			} else {$addjob = "";}
			$thisPlayer['name']= $value{name};
			if(isBuddy($value{id})){
				$thisPlayer['error']="Buddy";
			}
	        if ($value{spyable} == 1) {
				$thisPlayer['current']=$target == $value{id};
				$thisPlayer['id']= $value{id};
	        }
	        else {
				
				$thisPlayer['error']=transformfehler($value{spyable},"sign");
	        }
			array_push($tpl_players,$thisPlayer);
	    }
		$tpl->assign("PLAYERS",$tpl_players);
		
		$tpl_respy=array();
		$tpl_respy['targetname']=$targetname;
		$tpl_respy['target']=$target;
		$tpl_respy['rid']=$rid;
		$tpl_respy['actions']=array();
				
		if (true) {
			array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Spionage --',"disable"=>"disabled"));
			foreach ($aktionen as $key => $value) {
				if ($key == "getpodpoints") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- Sabotage --',"disable"=>"disabled"));
				}
				if ($key == "killunits") {
					array_push($tpl_respy['actions'],array("key"=>'default',"name"=>'-- nur bei Racherecht & Krieg --',"disable"=>"disabled"));
				}
				
				if ($key == "killsciences" && $status{spyactions} < KILLSCIENCESACTIONS) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				} elseif (($key == "killsciences" || $key == "killbuildings" || $key == "killunits" || $key == "delayaway") && !$sciences{glo13}) {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>"disabled"));
				}
				else {
					array_push($tpl_respy['actions'],array("key"=>$key,"name"=>$value{name},"disable"=>""));
				}
			}
		}
		$tpl_respy['ops']=$status{spyactions};
		$tpl_respy['maxops']=$maxSpyOpsThis;
		$tpl->assign('RESPY',$tpl_respy);
		
	} // ausgabe für übersicht (nicht für einzelne Spionageaktionen) //todo
$tpl->assign("RESULT",$tpl_result);
} # ende $goon$tar

// Für die Anzeige wie viel zusätzliche Spionageaktionen bringen
$tpl->assign('STEALACTIONS', STEALACTIONS); // Anzahl der Spionageanktionen, die Diebstahl benötigt.
$tpl->assign('KILLSCIENCESACTIONS', KILLSCIENCESACTIONS); // Anzahl Spionageaktionen, die killsciences benötigt // Seit Runde 42 wieder 15 aktionen, vorher 10 
$tpl->assign('KILLBUILDINGSACTIONS', KILLBUILDINGSACTIONS); // Anzahl der Spionageaktionen, die killbuildings benötigt.
$tpl->assign('KILLUNITSACTIONS', KILLUNITSACTIONS); //Anzahl der Spionageaktionen, die killunits benötigt.
$tpl->assign('DELAYAWAYACTIONS', DELAYAWAYACTIONS);
$tpl->assign('ZUSATZOPS_BONI', 0.2*100);

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

//header
require_once("../../inc/ingame/header.php");

//Infobox
if($infomsg != ''){
	$tpl->assign("INFO",$infomsg);
	$tpl->display('info.tpl');
}

//Fehler
if($errormsg != ''){
	$tpl->assign("ERROR",$errormsg);
	$tpl->display('fehler.tpl');
}
//Meldung
if($beschr != ''){
	$tpl->assign("MSG",$beschr);
	$tpl->display('sys_msg.tpl');
}

//ausgabe
$tpl->assign('RIPF',$ripf);
$tpl->display('spies.tpl');
$tpl->assign("RESULT",$tpl_result);

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function transformfehler($code,$type) {
	if ($type == "sign") {
		return transformfehlercode($code);
	}
	else {
		if ($code == "E0") {
			return "Sie können keine Spionageaktionen gegen Spieler in ihrem Syndikat unternehmen (solange diese nicht inaktiv sind).";
		}
		else if ($code == "E1") {
			return "Sie können keine Spionageaktionen gegen Spieler in ihrem Syndikat unternehmen (solange diese nicht inaktiv sind).";
		}
		else if ($code == "E2") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, die mehr als fünfmal größer bzw. weniger als 1/5 so groß wie Sie sind.";
		}
		else if ($code == "E3") {
			return "Sie können keine Spionageaktionen gegen Spieler unternehmen, die noch unter Schutz stehen.";
		}
		else if ($code == "E4") {
			return "Sie können keine Spionageaktionen gegen Spieler unternehmen, die sich im Urlaubsmodus befinden.";
		}
		else if ($code == "E5") {
			return "Sie können keine Spionageaktionen gegen Spieler unternehmen, die Aktienschutz von Ihrem Syndikats besitzen.";
		}
		else if ($code == "E6") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, solange Sie selbst noch unter Schutz stehen.";
		}
		else if ($code == "E8") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, die zusammen mit Ihnen in der selben Allianz sind.";
		}
		else if ($code == "E9") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, mit denen Sie einen NSP oder einen NASP abgeschlossen haben.";
		}
		else if ($code == "E10") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, die weniger als 1000 Land besitzen und weniger als halb soviel Land oder Networth besitzen wie ihr Konzern. Wenn sie weniger als 1000 Land besitzen, können sie keine Spionageaktionen gegen Spieler durchführen, die mehr als doppelt soviel Land oder Networth besitzen wie sie.";
		}
		else if ($code == "E11") {
			return "Sie können keine Spionageaktionen gegen Spieler durchführen, die sich in einem Syndikat befinden, mit dem sie Krieg haben und die weniger als die hälfte ihres Landes UND Networths haben, solange es noch Spieler in diesem Syndikat gibt, die mehr als die Hälfte ihres Landes oder Networths besitzen.";
		}
		else if ($code == "E12") {
			return "Sie / Ihr Ziel stehen unter dem Schutz der GVI. Es können keine Angriffe/Spionageaktionen auf Spieler verübt werden / von Spielern verübt werden, bei denen einer der Spieler Mitglied der GVI ist und einer der Spieler mehr als 1,5 mal so groß ist, wie der andere.";
		}
		else if ($code == "E13") {
			return "Dieser Spieler ist in einem anderen Bereich (Anfänger <-> Übrige); Eine Spionage über diese Grenzen hinweg ist nicht möglich!";
		}
	}
}

// Bitte alle Parameter angeben, sons funktioniert es nicht, gibt Fehlermeldung zurück, wenn Spionage nicht möglich, ansonsten 1

/* Fehlercodes:
    E1: Sie können keine Spionageaktionen gegen Spieler unternehmen, die mehr als 5% der Aktien ihres Syndikats besitzen
    E2: Sie können im Kriegsfall keine Spionageaktionen gegen Spieler unternehmen, die mehr als 10% der Aktien ihres Syndikats besitzen
    E3: Sie können keine Spionageaktionen gegen Spieler unternehmen, die noch unter Schutz stehen
    E4: Sie können keine Spionageaktionen gegen Spieler unternehmen, die sich im Urlaubsmodus befinden
    E5: Sie können keine Spionageaktionen gegen Spieler in ihrem Syndikat unternehmen (solange diese nicht inaktiv sind)
    E6: Sie können keine Spionageaktionen gegen Spieler durchführen, die mehr als fünf mal größer bzw kleiner als als ihr Konzern sind
*/

?>
