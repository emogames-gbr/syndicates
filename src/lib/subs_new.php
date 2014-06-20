<?
//
//	Landgainformel
//

/*
function landgain ($aland,$dland=1) {//falsch!
	
	$back = ( pow( (float) $dland,2.5) / pow ( (float) $aland,1.5) )  * 0.1;
	$smallkonz_multiplier = $aland>$dland  ?  pow($dland,1.48)/$aland : 1;
	//($dland/$aland < 0.2 ? 0.2 : $dland/$aland) > 1 ? 1 : ($dland/$aland < 0.2 ? 0.2 : $dland/$aland); //runde 52 by christian
	$back *= $smallkonz_multiplier; //runde 52 by christian
	if ($back > $dland * MAXLANDGAIN / 100) $back = $dland * MAXLANDGAIN / 100;
	return ($back);
	
}
*/

// 23.08.2013 Hafke : Von angriff.php kopiert, variablen nachgeladen, angriff spezifisches entfernt

function landgain($aland, $arid, $defender_id) {
	if ($defender_id == 0) return 0; // fail aufrufe verhindern
	
	// verteidiger laden
	$target = $defender_id;	
	$sciences_d = getsciences($target);
	$partner_d = getpartner($target);
	$status_d = getallvalues($target);
	$rid = single("select rid from status where id=$target");

	// zusätzliche variablen laden
	$one_prozent_activated = 0;
	
	$smallkonz_multiplier = $aland>$status_d[land]  ?  $status_d[land]/$aland : 1;
	$smallkonz_multiplier *= $aland>$status_d[land]/0.8  ?  pow($status_d[land]/$aland/0.8,1.5) : 1;
	
	$privatkrieg = racherecht($target);
	$isatwar = isatwar($arid, $rid, 1);
	$kondata = assocs("select id, syndicate, land, lastlogintime, createtime, alive, nw,gvi,inprotection,unprotecttime from status where rid=$rid", "id");
	
	$alreadyattackedbyattacker = 0;
	$alreadyattacked = 0;
	$searchtime = $time - 24 * 60 * 60;
	foreach ( assocs("select aid, winner, warattack, done_unter_racherecht from attacklogs where time > $searchtime and did=$target and gbprot = 1 ") as $ky => $vl)	{
		if ($vl[winner] == "a")	{
			if ($vl[aid] == $id): $alreadyattackedbyattacker++;
			else: $alreadyattacked++; endif;
		}
	}
			
	$landgainmultiplier = 0;
	list($bash_protection_multiplier,$dummy) = get_bash_protection($target);
	$landgainmultiplier = $bash_protection_multiplier;
	
	// Inaktivität checken
	$inactivity_mode = 0;
	$ginactive_col = 0;
	$ginactive_col = 0;
	if ($status_d[lastlogintime] + TIME_TILL_GLOBAL_INACTIVE < $time): $inactivity_mode = 2; $ginactive_col = " ginactive, "; $ginactive_value = " '2', ";
	elseif ($status_d[lastlogintime] + TIME_TILL_INACTIVE < $time): $inactivity_mode = 1;  $ginactive_col = " ginactive, "; $ginactive_value = " '1', "; if ($status[rid] == $rid): $inactivity_mode = 2; endif;
	endif;
	# mode 1: für krieg, als inaktiven erkennen
	# mode 2: gang bang zählt net
	
	// start landgain berechnung
	$perc = 0;	// Sammelt alle prozentualen Boni ein, die beim Angriff wirken
	
	$perc -= $sciences_d[glo8] * GLO8BONUS_SECOND_ORBITAL;
	
	if ($sciences_d[mil14] >= 1)
		$fog_of_war_switch2 = 1;
	else
		$fog_of_war_switch2 = 0;
	
	if ($status_d[race] == "pbf" && $isatwar)
		$perc -= PBF_PBF_WAR_LANDGAIN_MALUS; # Wird zurzeit nur oben beim Zusammenrechnen der Boni bzgl. PBF-PBF-Krieg-Landmalus von 15% gesetzt;
	
	$perc -= $msb2nd[14] * $fog_of_war_switch2;
	$perc += $sciences[mil3] * $msb{3};	
	$perc += $sciences[mil10] * MIL10BONUS_LANDGAIN_BONUS;	 //ra gibt landgain boni						
	$perc += $partner[3] * PARTNER_LANDGAINBONUS;	# Partnerschaftsbonus: +5% Landgewinn-Bonus bei erfolgreichem Angriff
	$perc -= $partner_d[20] * PARTNER_LANDLOSSBONUS; # Partnerschaftsbonus: -5% Landverlust bei Angriffen
	
	$landgain = floor(( ( ( pow((float)$status_d[land], 2.5) ) / ( pow((float)$aland, 1.5) ) ) * 0.1 ) * ( 1 + ( $perc / 100 )  ));

	if ($landgain > $status_d[land] * (MAXLANDGAIN/100)) // Cap-Regelung: Wenn Landgain ber maxlandgain liegt dann deckeln 
		$landgain = floor($status_d[land] * (MAXLANDGAIN/100));

	if ($isatwar or $privatkrieg === 1)	
	{
		if ($alreadyattacked+$alreadyattackedbyattacker == 0)
		{
			$landgain = intval($landgain * 1.25);
		}
		if ($alreadyattacked+$alreadyattackedbyattacker >= 2)
		{
			$landgain = intval($landgain * 0.75);
		}
	} else {
		$landgain = intval($landgain * $landgainmultiplier);	
	}
	
	if ($isatwar)
		$landgain = intval($landgain); //dragon12 evtl bug fix	
							
	
	if ($landgain < $status_d[land] * 0.01)
		$one_prozent_activated = $landgain+1;  /* +1, falls $landgain 0 ist, wegen boolean-Abfrage weiter unten*/ 
	
	if($privatkrieg!=1){ //runde58 angepasst by dragon (kein direktes racherecht -> $smallkonz_multiplier kommt dazu
		//echo"alrganin: ".$landgain."<br>";
		$landgain = intval($smallkonz_multiplier*$landgain); //runde 52 by christian
		//echo"neuganin: ".$landgain."<br>";
		//echo "der hier hat kein rr oder krieg";
	}
	
	$landgain = $status_d['land'] - $landgain < 400 ?  $status_d['land'] - 400 : $landgain; //runde52
	
	return $landgain;
}


//
//	onRoundEnd
//	Alles was direkt am Rundenende ausgeführt werden soll
//

function onRoundEnd() {
	setActiveRaces();	
	// StdMarktpreise für die nächste Runde setzen
	setUnitStandardprices();
}

function getEmogamesUserId($syndicates_user_id) {
	$syndicates_user_id = floor($syndicates_user_id);
	return single("select emogames_user_id from users where id=$syndicates_user_id");
}

//
//	onRoundStart
//	zum Start einer Runde (nach Anmeldephase - umschalten von status 0 auf 1)
//

function onRoundStart() {
	global $globals;
	setNextRoundActiveRaces();
	tweet('round_start', array('round' => $globals['round']-2));
}



function setActiveRaces() {
	// Aktive Fraktionen setzen
	select("update races set active = nextactive where nextactive != -1");
	select("update races set nextactive=-1");
	
}


function setUnitStandardprices($what="mil") {
	if ($what == "mil") {
	 	select("update military_unit_settings set current_price = credits+minerals*6+energy*1.2+sciencepoints*20");
		
	}
	if ($what == "spy") {
	 	select("update spy_settings set current_price = credits+energy*1.2");
		
	}
	if (!$what) {
	 	select("update military_unit_settings set current_price = credits+minerals*6+energy*1.2+sciencepoints*20");
	 	select("update spy_settings set current_price = credits+energy*1.2");
	}
}

//
//	setNextRoundActiveRaces()
//	Nach Rundenstart aufrufen

function setNextRoundActiveRaces() {
    select("update races set nextactive = 0");
    if (getServertype() == "classic") {
		//Fraktionen ab R46 wieder manuell bestimmt.
		select("update races set nextactive = -1");
		/*$races = assocs("select race, active from races");
		foreach ($races as $ky => $vl) {
		  if ($vl['active'] == 0) select("update races set nextactive = 1 where race like '".$vl['race']."'");
		  if ($vl['active'] == 1) $available_races[] = $vl['race'];
		}
		list($toprace, $trash) = row("select race, count(*) as tl from status where alive > 0 group by race order by tl desc LIMIT 1");
		select("update races set nextactive=1 where race='".$toprace."'");*/
    } 
	else if(getServertype() == "basic") {
		select("update races set nextactive=1 where race='pbf' or race='uic' or race='sl'");
    }
}


function calcSpylossMax($num,$type="geb") {
	
	if ($type == "geb") {
		return KILL_MAX_KILLBUILDINGS;
		/*if ($num <= 5) return KILL_MAX_KILLBUILDINGS; //R45 übrige Werte nicht verwendet, daher nicht angepasst
		else if ($num <= 10) return 1.2;
		else if ($num <= 20) return 1.0;
		else if ($num <= 30) return 0.6;
		else if ($num > 30) return 0.4;   abgeschafft R59*/
	} else {
		return KILL_MAX_KILLUNITS;
		/*if ($num <= 5) return KILL_MAX_KILLUNITS; //R45 übrige Werte nicht verwendet, daher nicht angepasst
		else if ($num <= 10) return 0.8;
		else if ($num <= 20) return 0.6;
		else if ($num <= 30) return 0.4;
		else if ($num > 30) return 0.3;   abgeschafft R59*/
	}
	
}


// Helper function to evaluate performance of spyloss in total.
function calcWholeSpyLosses($num) {
	
	$losses = 0;
	$rest = 1;
	
	for ($i=1 ; $i <= $num ; $i++) {
		
		$tLosses = calcSpylossMax($i);
		$losses += $tLosses*$rest;
		$rest -= $tLosses / 100;
		
	}
	return $losses;
	//pvar($losses);
	
}


function make_omnimon_series_name($db,$round,$type,$identifier="") {
  
  $back = urlencode($db."_r".$round."_".$type."_".$identifier);
  return $back;
  
}

//
// Simple one line curl request functions
//
function go_verbose($interface, $referer = '', $post = 0) {
                global $go_params;
                global $cookie;
		$params_prepared = array();
		if ($go_params) {
		  foreach ($go_params as $ky => $vl) {
			  $params_prepared[] = "$ky=$vl";
		  }
		}
                $final_params = join("&", $params_prepared);

                $ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPGET, true);
                if ($post == 1) curl_setopt($ch, CURLOPT_POST, 1);
                if ($post == 1 && $final_params) 
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $final_params);
                if ($post == 1) {
                  curl_setopt ($ch, CURLOPT_URL,$interface);
                } else {
                  curl_setopt ($ch, CURLOPT_URL,$interface . ($final_params ? "?$final_params" : "") );
                }
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, REFERER, $referer);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.8.0.5) Gecko/20060719 Firefox/1.5.0.5");
                if ($cookie) {
                        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                }

                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                $erg = curl_exec ($ch);
                curl_close($ch);

                // Cookie nehmen und merken
                preg_match("/Set-Cookie: ?([^;]+);/", $erg, $treffer);
                if ($treffer[1]) {
                        $cookie = $treffer[1];
                }

                $req = explode("\n", $erg);
                $header = array();
                foreach ($req as $ky => $vl) {
                  $header[] = $req[$ky];
                  unset($req[$ky]);
                  if (preg_match("/^\s*$/", $vl)) break;
                }   
                return array('header' => join("\n", $header), 'body' => join("\n", $req));

}

//by dragon12 funktionen für das tutorial
function getCurrentTutorial($id) {
	if(is_mentorprogram($id) != 2) {
		return false;
	}
	if(single('SELECT no_tutorial FROM users WHERE konzernid = '.$id)==1) {
		return false;
	}
	$last_tut = single('SELECT max(sort_order) FROM tutorial WHERE id in (SELECT tutorial_id FROM user_finished_tutorial WHERE konzern_id = '.$id.' and confirmed = 1)');
	if(!$last_tut) {
		$last_tut = 0;
	}
	return assoc('SELECT * FROM tutorial WHERE sort_order > '.$last_tut.' ORDER BY sort_order ASC LIMIT 1');
}


function go($url) {
  $back = go_verbose($url);
  return $back['body'];
}

function dm($message) {
	$game = assoc("select name from game limit 1");
	if ($game[name] == "Syndicates Testumgebung")
		f($message);	
}

?>
