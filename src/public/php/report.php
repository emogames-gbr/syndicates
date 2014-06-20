<?
/*
    Mögliche Optionen:
        - Konzernbild Reporten
        - Konzernname Reporten
        - Konzernbeschreibung Reporten
        - Syndikatsbild Reporten
        - Syndikatswebseite Reporten
        (- Messages reporten, implizit in Nachrichten.php)
        - Multi reporten
*/

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$verstosse = array("bug","partner","konzernbild","konzernbeschreibung","konzernname","syndikatswebseite","syndikatsbild","multi","sonstiges");
if (in_array($verstos,$verstosse)) {1;} else {unset($verstos);}
$meldeid = (int) $meldeid;
if ($meldeid <= 0) { $meldeid=0;}
if ($beteiligt) {$grund = $beteiligt."\n\n".$grund;}
$grund = addslashes($grund);
$queries = array();

// CONFIG //
// CONFIG //
$gamepath = "syndicates-online.de";
$adminadresse = "info@DOMAIN.de";
$adminadresse2 = "info2@DOMAIN.de";
$banpath = "http://$gamepath/syndicates/php/admin/index.php?action=ban";
$delpath = "http://$gamepath/syndicates/php/admin/index.php?action=ku";
$bildpfad = "http://$gamepath/syndicates/php/admin/index.php?action=vki&mode=single&mode2=showall&konzernid=";
$delsettingspath = "http://$gamepath/syndicates/php/admin/index.php?action=delsettings";
$multifindpath = "http://$gamepath/syndicates/php/admin/index.php?action=multifind";
// CONFIG //
// CONFIG //


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

if ($verstos === "konzernbild") {
    $was = "Konzernbild melden";
}
elseif ($verstos ==="konzernname") {
    $was = "Konzernname melden";
}
elseif ($verstos ==="konzernbeschreibung") {
    $was = "Konzernbeschreibung melden";
}
elseif ($verstos ==="syndikatswebseite") {
    $was = "Syndikatswebseite melden";
}
elseif ($verstos ==="syndikatsbild") {
    $was = "Syndikatsbild melden";
}
elseif ($verstos ==="multi") {
    $was = "Multi melden";
}
elseif ($verstos ==="sonstiges") {
    $was = "Sonstigen Verstoss melden";
}
elseif ($verstos ==="bug") {
    $was = "Bug melden";
}
elseif ($verstos ==="partner") {
    $was = "Zusammenspieler eintragen";
}
if (!$rid) {$rid = $status{rid};}

$tpl->assign("was", $was);
$tpl->assign("rid", $rid);
$tpl->assign("verstos", $verstos);

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

//							Berechnungen									//

if ((($verstos === "konzernbild" || $verstos === "konzernname" || $verstos === "konzernbeschreibung" || $verstos === "sonstiges") && $meldeid > 0) && $final) {
    $gemeldet = assoc("select * from status where id = $meldeid");
    $gemeldetuserid = single("select id from users where konzernid = $gemeldet[id]");
    $melderuserid = single("select id from users where konzernid = $status[id]");
    $tpl->assign("gmsBenachrichtigt1", true);
    $tpl->assign("gemeldet", $gemeldet);
	if ($verstos == "konzernbild") { $type = 3; $opening_message_var = "das Konzernbild"; }
	if ($verstos == "konzernname") { $type = 4; $opening_message_var = "den Konzernnamen"; }
	if ($verstos == "konzernbeschreibung") { $type = 5; $opening_message_var = "die Konzernbeschreibung"; }
	if ($verstos == "sonstiges") { $type = 10; $opening_message_var = "einen sonstigen Verstoss"; }

	$opening_message_text = "Der Spieler ".$status[syndicate]."(#".$status[rid].") beschwert sich über $opening_message_var von Spieler ".$gemeldet[syndicate]."(#".$gemeldet[rid].")<br><br>Kommentar des Melders:<br>$grund";
	create_case($melderuserid, $was, $type, $gemeldetuserid, $was, $opening_message_text);
	/*
    select("insert into admin_case (starter_id, starttime, title, type) values ($melderuserid, $time, '$was', $type)");
	$case_id = single("select id from admin_case where starter_id = $melderuserid and starttime = $time and title = '$was' and type='$type' order by id asc limit 1");
	select("insert into admin_case_involved (case_id, user_id, status) values ($case_id, $melderuserid, 0),($case_id, $gemeldetuserid, 1)");
	select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id, '', $melderuserid, $time, 0)");
	*/

	/*
    if ($verstos ==="konzernbild") {
        $bild = "Bild: $bildpfad".$gemeldet[id]."\n";
    }
    elseif ($verstos ==="konzernbeschreibung") {
        $settings = assoc("select kategorie,description from settings where id = $gemeldet[id]");
        $beschreibung = "Kategorie: ".$settings[kategorie]."
        Beschreibung: ".$settings[description]."\n";
    }
    $message = "
        Melder: ".$status[syndicate]."(#".$status[id].")
        Gemeldeter: ".$gemeldet[syndicate]."(#".$gemeldet[id].")
        ".$bild."".$beschreibung."
        Grund: $grund\n
        Gemeldeten Bannen: ".$banpath."&userid=".$gemeldetuserid."
        Gemeldeten Löschen: ".$delpath."&userid=".$gemeldetuserid."
		Konzernbeschreibung des gemeldeten Löschen: ".$delsettingspath."&userid=".$gemeldetuserid."
        Melder Bannen: ".$banpath."&userid=".$melderuserid."
    ";
    //sendthemail("$was","$message","$adminadresse","Game-Master");
    //sendthemail("$was","$message","$adminadresse2","Game-Master");
	*/
}
if ((($verstos === "syndikatsbild" || $verstos === "syndikatswebseite") && $rid > 0) && $final) {
    $syndvalues = assoc("select * from syndikate where synd_id = '$rid'");
    if ($syndvalues{president_id} > 0) {
        $meldeid = $syndvalues{president_id};
        $gemeldet = assoc("select * from status where id = ".$syndvalues{president_id});
        $gemeldetuserid = single("select id from users where konzernid = $gemeldet[id]");
        $melderuserid = single("select id from users where konzernid = $status[id]");
        $tpl->assign("gmsBenachrichtigt2", true);
        //$queries[] = "insert into reported (time,gemeldetid,grund,aktion,melderid) values ($time,$gemeldetuserid,'$grund','$verstos',$melderuserid)";
		if ($verstos == "syndikatsbild") { $type = 6; $opening_message_var = "das Syndikatsbanner"; }
		if ($verstos == "syndikatswebseite") { $type = 7; $opening_message_var = "die Syndikatswebsite (<a href=".$syndvalues['syndikatswebseite']." class=ver10s target=_blank>".$syndvalues['syndikatswebseite']."</a>)"; }
		$opening_message_text = "Der Spieler ".$status[syndicate]."(#".$status[rid].") beschwert sich über $opening_message_var von Syndikat Nummer $rid<br>".$gemeldet[syndicate]."(#".$gemeldet[rid].") ist Präsident dieses Syndikats.<br><br>Kommentar des Melders:<br>$grund";
		create_case($melderuserid, $was, $type, $gemeldetuserid, $was, $opening_message_text);
	/*
        if ($verstos ==="syndikatsbild") {
            $bild = "Bild: $bildpfad".$rid."\n";
        }
        elseif ($verstos ==="syndikatswebseite") {
            $webseite="Syndikatswebseite: ".$syndvalues{syndikatswebseite}."\n";
        }
        $message = "
            Melder: ".$status[syndicate]."(#".$status[id].")
            Gemeldeter: ".$gemeldet[syndicate]."(#".$gemeldet[id].")
            ".$bild."".$webseite."
            Grund: $grund\n
            Gemeldeten Bannen: ".$banpath."&userid=".$gemeldetuserid."
            Gemeldeten Löschen: ".$delpath."&userid=".$gemeldetuserid."
            Melder Bannen: ".$banpath."&userid=".$melderuserid."
        ";
        sendthemail("$was","$message","$adminadresse","Game-Master");
        sendthemail("$was","$message","$adminadresse2","Game-Master");
			*/
    }

}

if ($verstos === "multi" && $final) {
	if (!$grund) {
		f("Sie müssen eine Begründung angeben!"); $final = "";
	}
	else {
		$melderuserid = single("select id from users where konzernid = $status[id]");
		$tpl->assign("gmsBenachrichtigt3", true);
		$tempuserids = array();
		foreach ($multiarray as $key) {
			$key = (int)$key;
			/*
			$temp = assoc("select * from status where id=$key");
			$tempuserid = single("select id from users where konzernid=$key");
			$queries[] = "insert into reported (time,gemeldetid,grund,aktion,melderid) values ($time,$tempuserid,'$grund','$verstos',$melderuserid)";
			$konzernenamen.= "$temp[syndicate] (#$temp[rid]) ".$delpath."&userid=$tempuserid\n";
			$mark.="&mark[]=$tempuserid";
			*/
			$tempuserids[] = single("select id from users where konzernid=$key");
		}

		$opening_message_text = "Der Melder ist der Meinung, dass die beteiligten Spieler Multis sind.<br><br>Kommentar des Melders:<br>".preg_replace("/\n/", "<br>", htmlentities($grund, ENT_QUOTES));
		create_case($melderuserid, $was, 1, $tempuserids, $was, $opening_message_text);

	/*
		$message = "
			Melder: ".$status[syndicate]."(#".$status[id].")\n
			Betroffene Konzerne (mit Löschoption):\n
			$konzernenamen\n\n
			Grund: $grund\n
			Multis suchen: $multifindpath$mark\n
			Melder Bannen: ".$banpath."&userid=".$melderuserid."
		";
		sendthemail("$was","$message","$adminadresse","Game-Master");
		sendthemail("$was","$message","$adminadresse2","Game-Master");
	*/
	}
}

if ($verstos === "bug" && $final) {
	if (!$grund) {
		f("Sie müssen eine Erklärung angeben!"); $final = "";
	}
	else {
		$melderuserid = single("select id from users where konzernid = $status[id]");
		$tpl->assign("coderBenachrichtigt", true);
		$tempuserids = array();
		foreach ($multiarray as $key) {
			$key = (int)$key;
			/*
			$temp = assoc("select * from status where id=$key");
			$tempuserid = single("select id from users where konzernid=$key");
			$queries[] = "insert into reported (time,gemeldetid,grund,aktion,melderid) values ($time,$tempuserid,'$grund','$verstos',$melderuserid)";
			$konzernenamen.= "$temp[syndicate] (#$temp[rid]) ".$delpath."&userid=$tempuserid\n";
			$mark.="&mark[]=$tempuserid";
			*/
			$tempuserids[] = single("select id from users where konzernid=$key");
		}

		$opening_message_text = "Der Melder ist der Meinung, dass die beteiligten Spieler in einen Bug verwickelt sind.<br><br>Kommentar des Melders:<br>".preg_replace("/\n/", "<br>", htmlentities($grund, ENT_QUOTES));
		create_case($melderuserid, $was, 1, $tempuserids, $was, $opening_message_text);

	/*
		$message = "
			Melder: ".$status[syndicate]."(#".$status[id].")\n
			Betroffene Konzerne (mit Löschoption):\n
			$konzernenamen\n\n
			Grund: $grund\n
			Multis suchen: $multifindpath$mark\n
			Melder Bannen: ".$banpath."&userid=".$melderuserid."
		";
		sendthemail("$was","$message","$adminadresse","Game-Master");
		sendthemail("$was","$message","$adminadresse2","Game-Master");
	*/
	}
}

if ($verstos === "partner" && $final) {
	if (!$grund) {
		f("Sie müssen eine Begründung angeben!"); $final = "";
	}
	else {
		$melderuserid = single("select id from users where konzernid = $status[id]");
		$tempuserids = array();
		foreach ($multiarray as $key) {
			$key = (int)$key;
			/*
			$temp = assoc("select * from status where id=$key");
			$tempuserid = single("select id from users where konzernid=$key");
			$queries[] = "insert into reported (time,gemeldetid,grund,aktion,melderid) values ($time,$tempuserid,'$grund','$verstos',$melderuserid)";
			$konzernenamen.= "$temp[syndicate] (#$temp[rid]) ".$delpath."&userid=$tempuserid\n";
			$mark.="&mark[]=$tempuserid";
			*/
			$tempuserids[] = single("select id from users where konzernid=$key");
		}

		$opening_message_text = "Folgende Konzern sollen als Zusammenspieler gekennzeichnet werden.<br><br>Kommentar des Melders:<br>".preg_replace("/\n/", "<br>", htmlentities($grund, ENT_QUOTES));
		create_case($melderuserid, $was, 1, $tempuserids, $was, $opening_message_text);

	/*
		$message = "
			Melder: ".$status[syndicate]."(#".$status[id].")\n
			Betroffene Konzerne (mit Löschoption):\n
			$konzernenamen\n\n
			Grund: $grund\n
			Multis suchen: $multifindpath$mark\n
			Melder Bannen: ".$banpath."&userid=".$melderuserid."
		";
		sendthemail("$was","$message","$adminadresse","Game-Master");
		sendthemail("$was","$message","$adminadresse2","Game-Master");
	*/
	}
}

//							Daten schreiben									//

db_write($queries);

//							Ausgabe     									//


// Ausgabe, falls Formular noch nicht abgeschickt wurde
if (!$verstos) {
	$myid=single("select id from users where konzernid=".$status[id]);
	$mysup=assocs("select * from admin_case where starter_id=$myid order by starttime desc");
	$mysup_output = array();
	foreach($mysup as $sup){
		$gmname=single("select username from users where id=".$sup['processor_id']);
		$sup['o_gmname'] = $gmname;
		$sup['o_starttime'] = myTime($sup['starttime']);
		if ($sup['endtime']) $sup['o_endtime'] = myTime($sup['endtime']);
		array_push($mysup_output, $sup);
	}
	$tpl->assign("mysup", $mysup_output);
}

elseif (($verstos === "konzernbild" || $verstos === "konzernname" || $verstos === "konzernbeschreibung" || $verstos === "sonstiges") && !$final) {
    $players = assocs("select syndicate,id from status where rid=$rid");
    if ($players) {
    	$tpl->assign("players", $players);
    }
}

elseif (($verstos === "syndikatsbild" || $verstos === "syndikatswebseite") && !$final) {
	if ($verstos === "syndikatsbild") {
    	list ($synd_id, $name, $pres_id, $img) = row("select synd_id, name, president_id, image from syndikate where synd_id='$rid';");
    	$tpl->assign("img", $img);
    }
    elseif ($verstos ==="syndikatswebseite") {
    	$syndlink = single("select syndikatswebseite from syndikate where synd_id = '$rid'");
        $tpl->assign("syndlink", $syndlink);
    }
}

elseif (($verstos === "multi" || $verstos === "bug" || $verstos === "partner") && !$final) {
	if ($selectplayer) {
		$multiarray[] = (int)$multiid;
	}
	// Schon markierte Spieler
	if (is_array($multiarray)) {
		$tpl->assign('multiexists', true);
		$multiarray_output = array();
		foreach ($multiarray as $key) {
			$key = (int) $key;
			$temp = assoc("select * from status where id='".$key."'");
			$temp['o_key'] = $key;
			array_push($multiarray_output, $temp);
		}
		$tpl->assign('multiarray', $multiarray_output);
	}
	if (!$selectrid) {
		$selectrid = $status['rid'];
	}
	$selectrid = (int)$selectrid;
	$konzerne = assocs("select * from status where rid=$selectrid");
	if (count($konzerne) > 0) $gotone = 1;
	$tpl->assign("konzerne", $konzerne);
	$tpl->assign("gotone", $gotone);
	$tpl->assign("selectrid", $selectrid);								
	$count_multiarray = count($multiarray);
	if (   (($verstos === "multi" || $verstos === "partner") && ($count_multiarray < 2))
		|| ($verstos === "bug" && ($count_multiarray < 1))) {
		$multi_disabled = 1;
	}
	$tpl->assign("multi_disabled", $multi_disabled);
}

$tpl->assign("final", $final);

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
$tpl->display("report.tpl");
require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

?>
