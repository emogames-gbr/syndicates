<?


#########################################################
#														#
# Content: Syndicates Subfunctions		    			#
# Copyright: EmoGames Productions   					#
# 2002,2003,2004										#
# Author: Jannis Breitwieser / Nicolas Breitwieser		#
# Last updated: 180204		        					#
#                                  						#
#########################################################


//////////////  REQUIRES ////////////////////////
//
//  xtendsql.php - Vereinfachte Sql Anfragen (self)
//
//require_once ("mod_poll.php");

///////////// REQUIRES ENDE ///////////////////////

/********************************************************************************

Function available()
Verfgbare anzahl aktien

*********************************************************************************/
require_once("k_subs.php"); // Krawall Subfunctions
require_once("subs_new.php"); // New Subfunctions
require_once("subs_attack.php"); // Attack Subfunctions
require_once("omnilib.php");

function int($value) {
	return floor($value);
}
function mres($value) {
	return mysql_real_escape_string($value);
}

function inc(&$ref) {
	if (!isset($ref)) $ref = 0;
	$ref++;
}

function dec(&$ref) {
	if (!isset($ref)) $ref = 0;
	$ref--;
}

function init($array, $from = 'pg', $function = '') {
	if (preg_match("/[^pgc]/", $from)) echo "Fehlerhafter Aufruf von init<br>\n";
	else
	foreach ($array as $vl) {
		global $$vl;
		$$vl = param($vl, $from);
		if ($function) $$vl = @$function($$vl);
	}
}

function param($value, $type) {
	for ($i = 0; $i <= strlen($type)-1; $i++) {
		if ($type[$i] === "p") {
			if (isset($_POST[$value])) { return $_POST[$value]; break;}
		}
		elseif ($type[$i] === "g") {
			if (isset($_GET[$value])) { return $_GET[$value]; break;}
		}
		elseif ($type[$i] === "c") {
			if (isset($_COOKIE[$value])) { return $_COOKIE[$value]; break;}
		}
	}
}

function mySetCookie($name,$value) {
	setcookie($name,$value,-1,"/");
} 

function myDelCookie($name) {
	setcookie ($name,"", -1 ,"/");
}


function getVoteCode($id) {

	if (isKsyndicates()) return;
	
	if (!is_new_player($id, 3)) { // && !$init
		$zufallszahl_vote_class = mt_rand(0,4);
		global $voted;
		
		$classes = array("tableHead", "tableHead2", "tableInner1", "tableInner2", "siteGround");
		if (!is_array($voted)) {
			$voted = assocs("select link, ip from link_klick_count where user_id = $id and time > ".($time-24*3600)); // Wird jetzt auch in game.php geholt weil in menu
		}
		$galaxy_news_text = "<b>Bitte einmal am Tag für Syndicates stimmen für mehr Spieler und damit mehr Spielspaß Danke.</b>";
		$galaxy_news_link = "<a href='?headeraction=galaxy-news' target='_blank'><img src='http://www.galaxy-news.de/images/vote.gif' border=0 align=texttop></a>";
		/*
		$gamesdynamite_text = "<b>Das Super-Browserspiel 2005</b><br>Die dritte und letzte Phase vom Wettbewerb um das beste Browserspiel 2005 ist heute gestartet. Um diesen Wettbewerb gewinnen zu k?nen (wir haben sehr gute realistische Chancen, es lohnt sich also!!), ben?igen wir jedoch eure Hilfe. Stimmt bitte so oft ab wie es euch m?lich ist (normalerweise mind. 1 mal pro 24h). Jede Stimme z?lt und bringt uns weitere Punkte!<br><br>Hier nochmal ein ?erblick, wie ihr mithelft, Syndicates auf Platz 1 zu bringen!:<br><ol><li><a href=\"?headeraction=gamesdynamite\" target=\"_blank\" class=linkAuftableinner>Klickt rechts auf den Vote-Button (oder hier)</a> und gebt eure Stimme fr Syndicates ab (so oft wie m?lich)<li>Im Forum von gamesdynamite.de einen Account anmelden (das ist wichtig, Gastbeitr?e z?len nicht!) und <a href=http://forum.gdynamite.de/board.php?boardid=23 class=linkAuftableinner target=_blank>hier</a> fr Syndicates soviele Beitr?e wie m?lich verfassen (Spam ist dort erwnscht)</ol>";
		*/
		/*$gamesdynamite_text = "Deine Stimme fr Syndicates - jede Stimme hilft um eine bessere Chartplatzierung zu erhalten und damit mehr Spieler auf Syndicates aufmerksam zu machen.<br>Und dass mehr Spieler = mehr Spielspa?bedeutet ist doch logisch, oder :)?<br>Fr eure Untersttzung bedanken wir uns recht herzlich,<br><br><i>Bogul und Scytale, Gameadmins</i>";*/
	
		$gamesdynamite_link = "<a href=\"?headeraction=gamesdynamite\" target=\"_blank\"><img src=\"http://voting.gdynamite.de/images/gd_animbutton.gif\" alt=\"vote now!\" border=\"0\"></a>";
	
		foreach ($voted as $vl) {
			if ($vl[link] == "gamesdynamite" ): $gamesdynamite_done = 1;
			elseif ($vl[link] == "galaxy-news"): $galaxy_news = 1;
			//pvar($vl);
			endif;
		}

		$galaxy_news = 1;
	
		//if (!$gamesdynamite_done): $browsergame_vote_text = $galaxy_news_text; $browsergame_link = $gamesdynamite_link;
		//endif;
		if (!$galaxy_news): $browsergame_vote_text = $galaxy_news_text; $browsergame_link = $galaxy_news_link;
		endif;
	
		if ($browsergame_vote_text && $browsergame_link) {
			$browsergame_vote_text = "
				<tr><td colspan=\"2\" align=\"center\"><table width=\"580\" style=\"horizontal-align:center\" class=tableOutline cellpadding=0 cellspacing=1>
					<tr>
						<td align=\"center\">
						<table cellpadding=4 cellspacing=0>
						<tr><td class=$classes[$zufallszahl_vote_class]>
							$browsergame_vote_text
						</td>
						<td class=$classes[$zufallszahl_vote_class]>
							$browsergame_link
						</td></tr></table>
						</td>
					</tr>
				</table><br>
				</td></tr>
			";
		}
	}
	return $browsergame_vote_text;


}

function filelog($text, $filename) {
	/* Logging deaktiviert - Jannis 06.11.06
	global $globals;
	if (func_num_args() > 0) {
		$writelogdatei = LOGS.$filename;

		if (!$handle = fopen(("$writelogdatei"), 'a')) {
				//echo "Cannot open file ($filename)";
				//exit;
		}
		if (!fwrite($handle, $text)) {
			//echo "Cannot write to file ($filename)";
			//exit;
		}
		fclose($handle);
	}
	*/
}


function available($syndland,$umlauf,$privat) {
	/*
	global $syndikat;
	$syndland = (int) $syndland; $umlauf = (int) $umlauf; $privat = (int) $privat; $syndikat[aktmod] = (int) $syndikat[aktmod];
	$back = 0;
	$back = round($syndland * AKTIENAUSGABE * pow((1.01),($syndikat[aktmod])) - $umlauf - $privat);
	if ($back <= 0) {$back = 0;}
	return $back;
	*/
	return 1000000;
}

/********************************************************************************

Function setGPathsFromGpack($gpack)

*********************************************************************************/

function setGPathsFromGpack($gpack) {
	global $tpl,$ripf,$layout;
	//$gpackpath=TEMPLATES.$tpl->choosed."syn_gpacks/";
	$gpackpath=WWWDATA."syn_gpacks/";
	$ripf = $gpackpath.$gpack[gpack_id]."/";
	$layout["images"] = $gpackpath.$gpack[gpack_id]."/";
}

/********************************************************************************

Function setTemplatePaths($tpl)

*********************************************************************************/

function setTemplatePaths($template) {
	global $tpl;
	$tpl->setTemplateSet($template['path']);
}

/********************************************************************************

Function bold($string)

*********************************************************************************/

function bold($string) {
	return "<strong>$string</strong>";
}


/********************************************************************************

Function emoheader()

*********************************************************************************/

function emoheader ($arg) {
	global $game, $id;
	$emogames_id = single("select emogames_user_id from users where konzernid = '$id'");
	$loginkey = my_encrypt($emogames_id).createkey();
	EMOGAMES_prepare_Login($emogames_id,$loginkey);
	if ($game[name] == "Syndicates Testumgebung") {
		$emogames = "dev.BETREIBER.de";
	} else { $emogames = "BETREIBER.de"; }
	header("location: http://$emogames/index.php?loginkey=$loginkey&$arg");
}



/********************************************************************************

Function aktienbesitz()

*********************************************************************************/

function aktienbesitz($user,$synd_id) {
	// Argumente:
	// 1 - User id
	// 2 - Syndikats id
	// 3 - Ausgeschttete Aktien normal
	// 4 - Ausgeschttete Aktien privat
	// 5 - Userbesitz normal
	// 6- Userbesitz privat

	static $outnormala;
	static $outprivatea;

	if (func_num_args() >= 3) {$outnormal = func_get_arg(2);}
	elseif($outnormala[$synd_id]) {$outnormal = $outnormala[$synd_id];}
	else {$outnormal = num_aktien($synd_id);}
	if (func_num_args() >= 4) {$outprivate = func_get_arg(3);}
	elseif($outprivatea[$synd_id]) {$outprivate = $outprivatea[$synd_id];}
	else {$outprivate = single("select sum(number) from aktien_privat where synd_id=$synd_id");}
	if (func_num_args() >= 5) {$ownnormal = func_get_arg(4);}
	else {$ownnormal = single("select sum(number) from aktien where synd_id=$synd_id and user_id=$user");}
	if (func_num_args() >= 6) {$ownprivate = func_get_arg(5);}
	else {$ownprivate = single("select sum(number) from aktien_privat where synd_id=$synd_id and user_id=$user");}

	if ($outnormal) {
		$outnormala[$synd_id] = $outnormal;
	}
	if ($outprivate) {
		$outprivatea[$synd_id] = $outprivate;
	}

	$anzahl = $ownnormal + $ownprivate;
	$umlauf = $outnormal + $outprivate;

	if ($umlauf > 0) {
		$prozent = $anzahl / $umlauf * 100;
	}
	else {$prozent=0;}

	$back[0] = $anzahl;
	$back[1] = $prozent;
	$back[2] = $umlauf;
	
	return $back;
}


/********************************************************************************

Function print_hilfe()

*********************************************************************************/

function print_hilfe($hilfe) {
	global $layout;
	return "<a href=\"javascript:info('hilfe','".$hilfe."')\" class=linkAuftableInner><img src=\"$layout[images]/_help.gif\" border=0 valign=\"absmiddle\"></a>";
}



// GET SCIENCE STATS
function getScienceStats() {
	static $sciencestats;
	if (is_array($sciencestats)) {
		return $sciencestats;
	}
	
	$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, description, gamename, sciencecosts,id from sciences where available=1", "name");	//der science Table
	return $sciencestats;
}

// GET PARTNER STATS
function getPartnerStats() {
	static $partner_settings;
	if (is_array($partner_settings)) {
		return $partner_settings;
	}
	
	$partner_settings = assocs("select id, bonus, type as typ from partnerschaften_general_settings", "id");
	return $partner_settings;
}


/********************************************************************************

Function check_int()

*********************************************************************************/

function get_artefakte() {
	static $artefakte;
	
	if (!$artefakte) {

		$artefakte = assocs("
			select 
				a.artefakt_id as artefakt_id,
				a.name as name, 
				ab.name as bonusname,
				ab.description as bonusdescription,
				ab.type as bonustype,
				ab.value as bonusvalue
			from 
				artefakte as a,
				artefakte_boni as ab
			where 
				a.artefakt_bonus_id = ab.bonus_id
				and avaible!=0
			","artefakt_id"
		);
	}
	return $artefakte;
}

/********************************************************************************

Function check_int()

*********************************************************************************/

function check_int($input) {
	$input = str_replace ( ".", "", $input);
	$input = floor($input);
	$input < 0 ? $input = 0: 1;
	return $input;
}


/********************************************************************************

Function print_person()

*********************************************************************************/

// $starttime ist wichtig, damit bei den Person-Prints vergangener Runden nicht Konzernname aus der aktuellen Runde genommen wird

function print_person($id, $starttime) { # ($id, $data[, $userdata[, $use_bb_code]])
	static $internal_data;
	global $time, $globals;
	$everything_ok = 1;
	if ($starttime < $globals['roundstarttime']) $nokonzerndata = 1;
	if (func_num_args() > 2) $data = func_get_arg(2);
	if (func_num_args() > 3) $use_bb_code = func_get_arg(3);
	if ($data) {
		foreach ($data as $ky => $vl) {
			$found = 0;
			if ($internal_data) {
				foreach ($internal_data as $ky2 => $vl2) {
					if ($ky2 == $ky) { $found = 1; break; }
				}
			}
			if (!$found) $internal_data[$ky] = $vl;
		}
	}
	if (!$internal_data[$id]) {
		$internal_data[$id] = assoc("select users.id as user_id, users.username, status.syndicate, users.konzernid, status.rid from users, status where users.konzernid = status.id and users.id = $id");
		if (!$internal_data[$id]) {
			$internal_data[$id] = assoc("select * from users where id = $id");
			$everything_ok = 0;
		}
	}
	$d = &$internal_data[$id];
	if (!$nokonzerndata and $everything_ok) return $d['syndicate']." (#".$d['rid'].") &".$d['konzernid']." !".$d['username']." %".$d['user_id'];
	else return "!".$d['username']." %".$d['user_id']."";
}




/********************************************************************************

Function create_case()

*********************************************************************************/

// $involved kann ein einzelner user sein, oder ein array

function create_case($starter, $title, $type, $involved, $opening_message_subject, $opening_message_text, $starter_eq_involved = 1) {
	$starter = floor($starter);
	global $time;
	if ($starter) {
		select("insert into admin_case (starter_id, title, type, starttime) values ($starter, '$title', $type, $time)");
		$case_id = single("select id from admin_case where starter_id=$starter and  title='$title' and type=$type and starttime = $time order by id asc limit 1");
		if ($case_id) {
			// Starer des Cases als beteiligten Spieler eintragen, sofern der Case nicht von einem GM ge?fnet wurde
			if ($starter_eq_involved) select("insert into admin_case_involved (user_id, case_id, status) values ($starter, $case_id, 0)");
			// Beteiligte Spieler eintragen
			if ($involved) {
				if (!is_array($involved)) $involved = array($involved);
				foreach ($involved as $vl) {
					$vl = floor($vl);
					if ($vl) select("insert into admin_case_involved (user_id, case_id, status) values ($vl, $case_id, 1)");
				}
			}
			// Opening_Message eintragen
			select("insert into admin_case_messages (case_id, subject, type, time, message_text, sender_id, receiver_id) values ($case_id, '$opening_message_subject', 0, $time, '$opening_message_text', 0, 0)");
		} else return 0;
	} else return "0";
}





/********************************************************************************

Function print_sygnatur

*********************************************************************************/









/********************************************************************************

Function createkey()
erzeugt einen 40 stelligen Zufallscode

*********************************************************************************/


function createkey() {
    	$key = "";
		if (func_num_args() > 0) {
			$id = func_get_arg(0);
		}

		$length = 40;
		if (func_num_args() > 1) { $length = func_get_arg(1); }

    	for ($i=0;$i<$length;$i++) {
			$init = mt_rand(0,2);
			if ($init == 0) {
				$random = mt_rand(65,90);
			}
			if ($init == 1) {
				$random = mt_rand(97,122);
			}
			if ($init == 2) {
				$random = mt_rand(48,57);
			}
			$key.= chr($random);
		} // For
		if ($id) {
			$id = (int) $id;
			$key .= crypt($id, mt_rand(10,99));
		}
		return $key;
}



/********************************************************************************

Function time_played()

*********************************************************************************/

function round_time_played() {	// Gibt zeit in Sekunden zurck, die die Runde schon läft
	global $globals,$time;
	if (!$globals[roundstarttime]) {
		$roundstarttime = single("select roundstarttime from globals order by round desc limit 1");
	}
	else {
		$roundstarttime  = $globals[roundstarttime];
	}
	if (!$time) {
		$time = time();
	}
	$back = $time-$roundstarttime;

	return $back;

}


/********************************************************************************

Function round_days_played()

*********************************************************************************/

function round_days_played() {

	$back = floor((round_time_played()) / (60*60*24)); // Anzahl komplett vergangener Tage seit Rundenstart
	return $back;

}


/********************************************************************************

Function mytime()

*********************************************************************************/

// Wegen kompatibilitäsfragen aus "funcs" bernommen
function mytime($time, $noDayReplacement = 0) {

if (!$noDayReplacement) $back = datum("d.m.y - H:i",$time);
else $back = date("d.m.y - H:i", $time);
return $back;

}

function datum($datestring, $changetime) {
	global $time;
	$bold_begin = func_num_args() > 2 ? "[b]":"<b>";
	$bold_end = func_num_args() > 2 ? "[/b]":"</b>";

	// kleines y tempor?r gro? machen und merken
	if (preg_match("/y/", $datestring)) {
		$datestring = str_replace("y", "Y", $datestring);
		$small_y = 1;
	}

	if (strpos(" ".$datestring, "d.m.Y")) {
		if (date("d.m.Y", $time) == date("d.m.Y", $changetime)) {
			return $bold_begin."heute".$bold_end.date(str_replace("d.m.Y", "", $datestring), $changetime);
		}
		elseif (date("d.m.Y", $time-24*3600) == date("d.m.Y", $changetime)) {
			return $bold_begin."gestern".$bold_end.date(str_replace("d.m.Y", "", $datestring), $changetime);
		}
		elseif (date("d.m.Y", $time+24*3600) == date("d.m.Y", $changetime)) {
			return $bold_begin."morgen".$bold_end.date(str_replace("d.m.Y", "", $datestring), $changetime);
		}
	}

	// gro?es y wieder klein machen
	if ($small_y) $datestring = str_replace("Y", "y", $datestring);
	
	return date($datestring, $changetime);
}



/********************************************************************************

Function alliedsyns()

*********************************************************************************/

function alliedsyns($rid) {
	$back = singles("select first,second,third from allianzen where first=$rid or second=$rid or third=$rid");
	if (count($back) <= 0 || !$back) {
		$back = array($rid);
		return $back;
	}
	else {
		return $back;
	}
}

/********************************************************************************

Function alliOfSyn()

*********************************************************************************/

function allyOfSyn($rid) 
{
    $back = single("select allianz_id from allianzen where first=$rid or second=$rid or third=$rid");
    if ( !$back ) 
    {
        return 0;
    }
    else 
    {
        return $back;
    }
}

/********************************************************************************

Function in_config()
Jeder Konzern ist die erste Stunde seiner Existenz in der Konfigurationsphase, in 
der er Gebäude und Lann in einer Stunde fertigstellt.

*********************************************************************************/

function in_config($status) {
	//return false; // TESTING
	global $game;
	if (isBasicServer($game)) return false;
	return 0;

}


/********************************************************************************

Function in_protection()
In der Schutzzeit werden Militä- und Spionageeinheiten jetzt innerhalbe von einer
Stunde fertiggestellt

*********************************************************************************/

function in_protection($status) {

  global $time;
  if (!$time) $time = time();

  if (getServertype() == "basic") {
    if ($status[createtime] + PROTECTIONTIME >= $time) {
	    return 1;
    }
    else {
	    return 0;
    }
  } else if (getServertype() == "classic") {
	if ($status['inprotection'] == 'Y' ||  $time <= $status['unprotecttime']) {
		return 1;
	}
	else {
		return 0;
	}
  }
}


/********************************************************************************

Function is_new_player()

*********************************************************************************/

function is_new_player($id, $default = 49) {

	global $globals, $time, $status;
	static $startround;
	if (!is_array($globals)) {
		$globals = assoc("select * from globals order by round desc limit 1");
	}
	if (!$time) {
		$time = time();
	}
	if (!is_array($status)) {
		$status = assocs("select * from status where id=$id");
	}
	if (!$startround) {
		$startround = single("select startround from users where konzernid = $id");
	}
	if ($startround == $globals[round] && $time - $status[createtime] <= $default * 86400) {
		return true;
	}
	else return false;
}


/********************************************************************************

Function player_join_syndicate()
// Benutzt db_write()

*********************************************************************************/

function player_join_syndicate($id,$joinsynd) {

	global $status;
	global $sciences;
	if (!$status) {$status_intern = assoc("select * from status where id =$id");}
	else {$status_intern = $status;}
	if (!$sciences) {$sciences_intern = getsciences($id);}
	else {$sciences_intern = $sciences;}

	$forschungen = array (	array ("energyforschung", "ind16")
							,array ("sabotageforschung", "glo12")
							,array ("creditforschung", "ind15")
							//,array ("synarmeeforschung", "mil15")
						);

	if ($joinsynd > 0) {
		$synfos = assoc("select energyforschung, sabotageforschung, creditforschung, synarmeeforschung from syndikate where synd_id = ".$joinsynd);
		$queries_intern = array();
		foreach ($forschungen as $vl) {
			if ($sciences_intern{$vl[1]}) {
				$synfos[$vl[0]] = explode("|", $synfos[$vl[0]]);
				$synfos[$vl[0]][$sciences_intern{$vl[1]}-1]++;
				$queries_intern[] = "update syndikate set $vl[0]='".join("|", $synfos[$vl[0]])."' where synd_id=".$joinsynd;
			}
		}
	}

	db_write($queries_intern,0);
}

/********************************************************************************

Function player_leave_syndicate()
// Benutzt db_write()

*********************************************************************************/

function player_leave_syndicate($id,$leavesynd) {

	### Wenn die Werte hier global sind, verliert beinem getöeten Spieler das Syndikat die Forschungen des Angreifers
	#global $status;
	#global $sciences;
	if (!$status) {$status_intern = assoc("select * from status where id =$id");}
	else {$status_intern = $status;}
	if (!$sciences) {$sciences_intern = getsciences($id);}
	else {$sciences_intern = $sciences;}

	$forschungen = array (	array ("energyforschung", "ind16")
							,array ("sabotageforschung", "glo12")
							,array ("creditforschung", "ind15")
							//,array ("synarmeeforschung", "mil15")
						);

	if ($leavesynd > 0) {
		$synfos = assoc("select energyforschung, sabotageforschung, creditforschung, synarmeeforschung from syndikate where synd_id = ".$leavesynd);
		$queries_intern = array();
		foreach ($forschungen as $vl) {
			if ($sciences_intern{$vl[1]}) {
				$synfos[$vl[0]] = explode("|", $synfos[$vl[0]]);
				$synfos[$vl[0]][$sciences_intern{$vl[1]}-1]--;
				$queries_intern[] = "update syndikate set $vl[0]='".join("|", $synfos[$vl[0]])."' where synd_id=".$leavesynd;
			}
		}
	}
	db_write($queries_intern,0);
}


/********************************************************************************

Function werbung_hit()

*********************************************************************************/

function werbung_hit($werbung_name) {
	global $time;
	if (!$time) {$time = time();}
	$tag = date("d", $time);
	$monat = date("m", $time);
	$jahr = date("Y", $time);
	$datum = $tag.".".$monat.".".$jahr;
	$qry = mysql_query("update werbung_einblendungen set number=number+1 where werbung_name='$werbung_name' and datum='$datum'");
	$ar = mysql_affected_rows();
	if ($ar == 0) {
		mysql_query("insert into werbung_einblendungen (werbung_name,datum,number) values ('$werbung_name','$datum',1)");		
	}
}


/********************************************************************************

Function maxunits()

*********************************************************************************/
// REQUIRES A GLOBAL $STATUS ARRAY

function maxunits($what,$gs = 0) {
	global $status,$sciences, $partner,$game_syndikat,$syndikate_data,$artefakte;
	if ($gs != 0 && is_array($gs)) $game_syndikat = $gs;
	
	if (is_array($syndikate_data) && $syndikate_data[$status[rid]]) {
		$game_syndikat = $syndikate_data[$status[rid]];
	}   
	
	$artefakt_id = $game_syndikat[artefakt_id];
	//echo "\n\n--- MAXUNITS CALLED---\n";
	//echo "Artefakt Id: $artefakt_id\n";

	
	if ($what == "mil") {
		
		$artefakte[$artefakt_id][bonusname] == "depots_kap_bonus" ? $bvalue  = $artefakte[$artefakt_id][bonusvalue] : $bvalue=0; 
		$artefakte[$artefakt_id][bonusname] == "land_cap_mil_bonus" ? $avalue  = $artefakte[$artefakt_id][bonusvalue]*$status[land] : $avalue=0;
		$lagerkapas = ($status{depots} * (DEPOTWERT+$bvalue) + $status{land} * LANDWERT + ($sciences{mil8} * MIL8BONUS + $partner[5] * 1) * $status{depots} + $status[spylabs] * SPYLABSWERT_MILITAER +$avalue);
		if($status['race']=="neb") $lagerkapas += NEB_MORE_UNITS_HA * $status{land};
		/*
		if ($status['race'] == "nof" AND FALSE) { // SEIT RUNDE 28 verbraucht der Carrier lediglich keinen Platz mehr
			// Kompliziertere Sache, da Market-und Away Werte ben?tigt werden und diese auf den normalen Seiten und im Update unterschiedlich zur Verf?gung stehen:
					global $away, $away_military_for_nw;
					if (isset($away_military_for_nw) && is_array($away_military_for_nw)) $away_intern = $away_military_for_nw[$status['id']];
					else if (isset($away) && is_array($away))	$away_intern = $away;
					else $away_intern = getaway($status['id']);
					
					global $market, $markets;
					if (isset($markets) && is_array($markets)) $market_intern = $markets[$status['id']];
					else if (isset($market) && is_array($market)) $market_intern = $market;
					else $market_intern = getmarket($status['id']);
			

					$totalCarriers = $status['elites'] + $market_intern['elites'] + $away_intern['elites'];

			if ($lagerkapas > $totalCarriers) $lagerkapas += $totalCarriers;
			else $lagerkapas *= 2; // maximal Lagerkapas durch Carrier verdoppeln
		}
		*/
		return $lagerkapas;
		
	}
	elseif ($what == "spy") {
		$artefakte[$artefakt_id][bonusname] == "land_cap_spy_bonus" ? $avalue  = $artefakte[$artefakt_id][bonusvalue]*$status[land] : $avalue=0; 
		$issdn_ha = $sciences{glo12} ? GLO12BONUS_SPY_PER_HA * $sciences{glo12} : 0; //issdn
		return ($status{spylabs} * SPYLABSWERT + $status{land} * (LANDWERT2+$issdn_ha) + ($sciences{glo4} * GLO4BONUS + $partner[5] * 1) * $status{spylabs} + $status[depots] * DEPOTWERT_SPIONE+$avalue);
	}
}


function getTotalCarriers($nostatic = 0, $caller = "normal") {
	global $status;

	if ($status['race'] == "nof") {
		global $away, $away_military_for_nw;
		if (isset($away_military_for_nw) && is_array($away_military_for_nw)) $away_intern = $away_military_for_nw[$status['id']];
		else if (isset($away) && is_array($away))	$away_intern = $away;
		else $away_intern = getaway($status['id']);
	
		global $market, $markets;
		if (isset($markets) && is_array($markets)) $market_intern = $markets[$status['id']];
		else if (isset($market) && is_array($market)) $market_intern = $market;
		else $market_intern = getmarket($status['id']);
	
		global $in_build_carrier; // aus Update
		if (isset($in_build_carrier) && is_array($in_build_carrier)) $in_build_carrier_intern = $in_build_carrier[$status['id']]['number'];
		else if (isset($mil_imbau) && is_array($mil_imbau)) $in_build_carrier_intern = $mil_imbau['elites'];
		else { static $mil_imbau;
			if (!isset($mil_imbau) or $nostatic) $mil_imbau = military_in_build($status['id']);
			$in_build_carrier_intern = $mil_imbau['elites'];
		}
		
	
	
	
		$totalCarriers = $status['elites'] + $market_intern['elites'] + $away_intern['elites'] + $in_build_carrier_intern;
		if ($status['id'] == 12 and false) {
			$whatcall = $caller."Call";
			if (isset($in_build_carrier)) $whatcall = "updateCall";
			
			$message = "totalCarriers: $totalCarriers\nStatus: ".$status['elites']."\nMarket: ".$market_intern['elites']."\naway: ".$away_intern['elites']."\nin_build: ".$in_build_carrier_intern."\n";
			if ($whatcall != "normalCall") {
				sendthemail("Carriercheck $whatcall",$message,'admin@domain.de','admin@domain.de');
			}
		}
		return $totalCarriers;
	} else return 0;
}


function military_in_build($id) {

    $imbau=array();
    global $status;
    global $unitstats;
    if (!$unitstats) $unitstats = getunitstats($status{race});
    foreach ($unitstats as $key => $value) {$imbau{$key} = 0;}
            
    $result = select("select unit_id,number from build_military where user_id=$id");
    
    for ($i=0; $return = mysql_fetch_row($result);$i++) {        
        foreach ($unitstats as $key => $value) {        
            if ($unitstats{$key}{unit_id} == $return[0]) {
            $imbau{$key} += $return[1]; break;
            }    
        } # foreach keys
    } #for return
    return $imbau;						
} # subende


/********************************************************************************

Function getmicrotime()

*********************************************************************************/

function getmicrotime() {
    list($usec,$sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}


/********************************************************************************

Function get_next_rid($rid)

*********************************************************************************/
function get_next_rid($rid) {
	$nextrid = single("select synd_id from syndikate where synd_id > $rid order by synd_id asc limit 1");
	if (!$nextrid) {
		return $rid;
	}
	return $nextrid;
}

/********************************************************************************

Function get_last_rid($rid)

*********************************************************************************/
function get_last_rid($rid) {
	$lastrid= single("select synd_id from syndikate where synd_id < $rid order by synd_id desc limit 1");
	if (!$lastrid) {
		return $rid;
	}
	return $lastrid;
}


/********************************************************************************

Function prozent

*********************************************************************************/

function prozent($wert) {
	$wert = round($wert,1);
    $prozent = sprintf("%01.1f",$wert);
    return $prozent;
}

/********************************************************************************

Function miltotal

*********************************************************************************/

function miltotal($id) {
    // 1. Argument: Spielerid, damit statusarray bestimmen, dbaufruf nur, wenn spieler nicht aktueller spieler ist
    global $status;
    global $away;
    global $market;
    if (func_num_args() > 1) {$what = func_get_arg (1);}
	if (func_num_args() > 2) {$return_arrays = func_get_arg (2); }

    if ($status{id} == $id) {
        $intern = $status;
        $away ? $away_intern = $away : $away_intern = getaway($id);
        is_array($market) ? $market_intern = $market : $market_intern = getmarket($id);
    }
    else {
        $intern = getallvalues($id);
        // Falls gebraucht, away holen
        if (!$what || $what == 3 || $what == 5) {$away_intern = getaway($id);}
        if (!$what || $what == 4 || $what == 5) {$market_intern = getmarket($id);}
    }


    // Argumente checken

    // 2. Argument bestimmt, welcher Wert bestimmt werden soll, wird kein 2. Argument angegeben, wird alles zurckgegeben
    //
    // 1: Nur Statusmilitä
    // 2: Nur Militä in Bau
    // 3: Nur Militä Away
    // 4: Nur Miltä auf Market


    // Status militä:
    if ($what == 1 || !$what || $what == 5) {
        $statusmil =  $intern{offspecs} + $intern{defspecs} + $intern{elites} + $intern{elites2} + $intern{techs};
        if ($what == 1) {return $statusmil;}
    }

    // Militä in bau
    if ($what == 2 || !$what || $what == 5) {
        $result = select("select sum(number) from build_military where user_id = ".$intern{id});
        if (mysql_num_rows($result) > 0) {$buildmil = mysql_fetch_row($result); $buildmil = $buildmil[0];}
        else {$buildmil = 0;}
        if ($what == 2) {return $buildmil;}
    }

    // Militä away
    if ($what == 3 || !$what || $what == 5) {
        $awaymil = array_sum($away_intern);
    }

    // Militä auf markt
    if ($what == 4 || !$what || $what == 5) {
            $marketmil = $market_intern{elites}+$market_intern{elites2}+$market_intern{offspecs}+$market_intern{defspecs}+$market_intern{techs};
        }

    
    // $what = 5 Gibt strukturiertes assoziatives Array der einzelnen Werte zurck, ohne Parameter wird einfach die Gesamtanzahl als Skalarwert zurckgegegeben
    if ($what == 5) {
		$back = array("status" => $statusmil,
					"away" => $awaymil,
					"build" => $buildmil,
					"market" =>$marketmil,
					"all" => ($statusmil+$buildmil+$awaymil+$marketmil));
		if ($return_arrays) {
			$back[away_array] = $away_intern;
			$back[market_array] = $market_intern;
		}
		return $back;
    }
    return ($statusmil+$buildmil+$awaymil+$marketmil);

}

/********************************************************************************

Function ersetze

*********************************************************************************/

# Ben?igt 3 Argumente
# $_[0] = Zeichen, welches zu ersetzen ist
# $_[1] = Zeichen, welches anstattdessen gesetzt werden soll
# $_[2] = String, auf welchen die Ersetzung angewendet werden soll

function ersetze ($was,$durch,$in){
$in = preg_replace("/$was/e","$durch","$in");
return $in;
}


///////////////////// NACHRICHT AN SPIELER VERSCHICKEN
// $targets sind die Ziele, k?nen entweder mehrere in einem Array sein oder ein einzelner Skalar; M?liche Werte sind Konzern-IDs und Syndikatsnummern
// Um zwischen Konzernen und Syndikatsnummern zu unterscheiden muss der letzte Parameter '$type' gesetzt werden. Standardm?ig bei Nachrichten an Konzernen
// kann er weggelassen werden; will man jedoch Syndikatsnummern angeben muss man 'syndikat' setzen;
// message_id bezeichnet die ID-Nummer der Message und $params muss ein Array oder ein Skalar mit den entsprechenden Werten sein.
// $execute gibt an, ob die Statements gleich ausgefhrt oder lieber dem $queries-Array hinzugefgt werden sollen, Standard ist 0, also $queries

function nachricht_senden($targets, $message_id, $params, $type = 'spieler', $execute = 0) {
	global $queries, $time;
	$add = array();
	$queries_intern = array();
	if (!is_array($targets)) { $targets = array($targets); }
	if (!is_array($params)) { $params = array($params); }
	if ($message_id > 0 and count($params) > 0 and count($targets) > 0 and ($type == 'spieler' or $type == 'syndikat')) {
		$werte = join("|", $params);
		if ($type == "spieler") {
			foreach ($targets as $vl) {
				$add[] = "('".floor($message_id)."', '".floor($vl)."', '$time', '$werte')";
			}
		}
		elseif ($type == "syndikat") {
			foreach ($targets as $vl) {
				$ids = singles("select id from status where rid = '".floor($vl)."'");
				if ($ids) {
					foreach ($ids as $vl2) {
						$add[] = "('".floor($message_id)."', '$vl2', '$time', '$werte')";
					}
				}
			}
		}
		if ($add) $queries_intern[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $add);
		if ($execute) {
			db_write($queries_intern, 0);
		}
		else {
			foreach ($queries_intern as $vl) {
				$queries[] = $vl;
			}
		}
	} else { echo "Beim Versenden einer Nachricht ist ein Fehler aufgetreten. Bitte wenden Sie sich an einen Administrator, um das Problem zu lösen!"; }
}



//////////////////// Message in den Towncrier schreiben
// $rids kann skalar oder ein Array mit mehreren Syndikatsnummern sein
// $message selbstredend;
// $execute gibt an, ob die Statements gleich ausgefhrt oder lieber dem $queries-Array hinzugefgt werden sollen, Standard ist 0, also $queries
// $kategorie gibt die kategorie an, die f?r den eintrag eingetragen werden soll 
//
//	0 = Angriffe 
//	1 = Spielerwechsel / Urlaub / Optionen
//	2 = Politik ( Allianezn und ?hnlcihes)
// 	3 = Monumente

function towncrier($rids, $message, $execute = 0,$kategorie = 0) {
	global $queries, $time;
	$add = array();
	$queries_intern = array();
	if (!is_array($rids)) $rids = array($rids);
	if ($rids) {
		foreach ($rids as $vl) {
			$add[] = "('$time', '$vl', '$message','$kategorie')";
		}
		if ($add) $queries_intern[] = "insert into towncrier (time, rid, message,kategorie) values ".join(",", $add);
		if ($execute) {
			db_write($queries_intern, 0);
		}
		else {
			foreach ($queries_intern as $vl) {
				$queries[] = $vl;
			}
		}
	}
}



/********************************************************************************

Function spiestotal

*********************************************************************************/

function spiestotal($id) {
    // 1. Argument: Spielerid, damit statusarray bestimmen, dbaufruf nur, wenn spieler nicht aktueller spieler ist
    global $status;
    global $market;

    if ( $status{id} == $id) {
        $intern = $status;
        is_array($market) ? $market_intern = $market : $market_intern = getmarket($id);
    }
    else {
        $intern = getallvalues($id);
        if (!$what || $what == 4 || $what ==5 ) {$market_intern = getmarket($id);}
    }

    // Argumente checken

    // 2. Argument bestimmt, welcher Wert bestimmt werden soll, wird kein 2. Argument angegeben, wird alles zurckgegeben
    //
    // 1: Nur Statusspies
    // 2: Nur spies in Bau
    // 3: Nur spies Away
    // 4: Nur spies auf Market

    if (func_num_args() > 1) {$what = func_get_arg (1);}
	if (func_num_args() > 2) {$return_arrays = func_get_arg (2); }

    // Status spies:
    if ($what == 1 || !$what || $what == 5) {
        $statusspies =  $intern{offspies} + $intern{defspies} + $intern{intelspies};
        if ($what == 1) {return $statusspies;}
    }

    // spies in bau
    if ($what == 2 || !$what || $what == 5) {
        $result = select("select sum(number) from build_spies where user_id = ".$intern{id});
        if (mysql_num_rows($result) > 0) {$buildspies = mysql_fetch_row($result); $buildspies = $buildspies[0];}
        else {$buildspies = 0;}
        if ($what == 2) {return $buildspies;}
    }

    // spies away
    if ($what == 3 || !$what || $what == 5) {
        $awayspies = 0;
    }

    // spies auf markt
    if ($what == 4 || !$what || $what == 5) {
        $marketspies = $market_intern{offspies} + $market_intern{defspies} + $market_intern{intelspies};
    }

    if ($what == 5) {
		$back = array("status" => $statusspies,
					"away" => $awayspies,
					"build" => $buildspies,
					"market" =>$marketspies,
					"all" => ($statusspies+$buildspies+$awayspies+$marketspies));
		if ($return_arrays) {
			$back[away_array] = $away_intern;
			$back[market_array] = $market_intern;
		}
     	return $back;
    }
    return ($statusspies+$buildspies+$awayspies+$marketspies);

}

/********************************************************************************

Function pvar() // printvar
// Hilft bei debugging ausgaben

*********************************************************************************/

function pvar($var) {
	static $reclevel = 0;
	$print = "Pvar";
	if (func_num_args() > 1) {
		$print = func_get_arg(1);
	}
	if (func_num_args() > 2) {
		$rec=1;
		$reclevel++;
		//echo "<b><span style=\"color:red\">Reclevel start: $reclevel</span></b><br>";
	}
	// Table schreiben, wenn reclevel =0
	if ($reclevel == 0) {
		echo "<table bgcolor=black cellspacing=1 cellpadding=0 width=80%><tr><td><table bgcolor=\"#999999\" width=100% cellspacing = 3><tr><td>";
	}
	if (is_object($var)) {
		$var = get_object_vars($var);
	}
	if (is_array($var)) {
		echo "<li><b><span style=\"color:white\">$print:</span></b><br><ol type=1>";
		foreach ($var as $key => $value) {
			if (is_array($value)) {
				pvar($value,$key,1);
			}
			else {
				echo "<li><i><span style=\"color:white\">$key:</span></i><span style=\"color:green\">$value</span><br>";
			}
		}
	}
	else {
		echo "<b><span style=\"color:white\">$print: $var</span></b><br>";
	}
	if ($rec) {
		unset($rec);
		echo "</ol>"; //<b><span style=\"color:red\">Reclevel finished: $reclevel</span></b><br>";
		$reclevel--;
	}
	// Table schreiben, wenn reclevel =0
	if ($reclevel == 0 && func_num_args() <= 2) {
		echo "</td></tr></table></td></tr></table>";
	}

}

/********************************************************************************

Function dontcache()
// Sendet header, muss also vor ausgabe aufgerufen werden.

*********************************************************************************/

function dontcache() {
	header("Pragma:no-cache");
	header("Cache-Control:private,no-store,no-cache,must-revalidate");
}

/********************************************************************************

Function chopp

*********************************************************************************/

function chopp($string) {

$string = substr($string,0,strlen($string)-1);
return $string;

}


function freeland($status) {
	global $outerbuildings;
	$underconstruction = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name != 'land'");
	$underconstruction = (int) $underconstruction;
	$allbuildings = getallbuildings($status{id});
	$allbuildings = (int) $allbuildings;
	$freeland = (int) ($status[land] -  $allbuildings - $underconstruction);
	return $freeland;
}

/********************************************************************************

Function gebkostet()

*********************************************************************************/

function gebkosten($buildings) {
	global $status,$sciences, $partner;

	define( PARTNER_GEBKOSTENBONUS,20);

	$buildingcostsmod = 0;
	$buildingcosts = (int) ( 1000+ ($buildings * $buildings) /2500); // Ab Runde 30
	//$buildingcosts *=0.9; 							//Sonderrabatt :)
    // Nur bei positiver Energiebilanz Bonus durch Bauh?e
    if (true or energyadd($status{id},6) > 0) { // positive Energiebilanz seit Runde 10 abgeschafft
    	$buildinggroundsmod = (($status{buildinggrounds}/$status{land})*3.5);		//Bauh?e
    	if ($buildinggroundsmod >= 0.7) {$buildinggroundsmod = 0.7;}
    }

	//if ($status{land} < 200) {$buildingcosts= 1000;}				//Mindestpreis ohne Bauh?e/Forschungen
	if ($buildingcosts < 1000) {$buildingcosts = 1000;}				//Mindestpreis ohne Bauh?e/Forschungen
	if ($sciences{ind6}) {$buildingcostsmod += IND6BONUS * $sciences{ind6};}		//New Building Methods
	if ($sciences{ind3}) {$buildingcostsmod += IND3BONUS_CHEAPER * $sciences{ind3};} //ibc
	if ($partner[18]) { $buildingcostsmod += $partner[18] * PARTNER_GEBKOSTENBONUS/100; } // 5% billigere Gebäude pro Bonuslevel;
	if ($status[race] == "neb"): $buildingcostsmod += 0.0; endif; // 0 seit runde 30 
	$buildingcostsmod += $buildinggroundsmod;
	$buildingcosts *= (1-$buildingcostsmod);
	if ($buildingcosts <= 0): $buildingcosts = 1; endif;
	return (int) $buildingcosts;
}


function landtimemodifier() {
	global $status;
	$temp = 0;
	/*if ($status{race} == "neb") {
		$temp = 5 * 3600; // Neb baut 4 Stunden schneller // 5 Stunden seit runde 30 
	}*/
	return $temp;
}

function buildtimemodifier() {
	global $status,$sciences,$partner;
	define(PARTNER_BUILDINGSBUILDTIMEBONUS,2);
	$temp = 0;
	$minbauzeit = 0.85; ## Entspricht maximaler Beschleunigung um 85%, also 15% von 20h = 3h Mindestbauzeit
	if ($status{race} == "neb") {
		//$temp += 0.15; // Neb Speedbonus Runde 30 deaktiviert
	}
	if ($status[race] == "nof") {
		//$temp += NOF_BUILDTIME_MODIFIER; // Nof Speedbonus in Runde 30 deaktiviert  
	}
	if ($status[race] == "uic") {
		$temp += UIC_BUILDINGS_SPEEDBONUS;
	}
	if ($sciences{ind3}) { //ibc
		$temp += $sciences{ind3}*0.1;
	}
	if ($status[buildinggrounds]) {
		$buildinggroundtimebonus = $status{buildinggrounds}/$status{land}*100/2;
		if ($buildinggroundtimebonus > 10) {$buildinggroundtimebonus = 10;}
		if ($buildinggroundtimebonus)	{
			$temp += 1/20*floor($buildinggroundtimebonus);
		}
	}
	if ($partner[15]) { // Partnerbonus -2h Gebäudebauzeit
		$temp += 0.05 * $partner[15]*PARTNER_BUILDINGSBUILDTIMEBONUS;
	}
	if ($temp > $minbauzeit) $temp = $minbauzeit;

	return $temp;
}



/********************************************************************************

Function isatwar

*********************************************************************************/

function isatwar($own,$geg)	{
	global $time;
    if (func_num_args() > 2) {$check = func_get_arg (2);}

	$atwar = singles("select atwar from syndikate where synd_id in ($own,$geg)");

	if ($atwar[0] && $atwar[1])	{

		$wardata = assoc("select first_synd_1,first_synd_2,first_synd_3,second_synd_1,second_synd_2,second_synd_3,status,war_id from ". WARTABLE ." where (((first_synd_1=$own or first_synd_2=$own or first_synd_3=$own) and (second_synd_1=$geg or second_synd_2=$geg or second_synd_3=$geg)) or ((first_synd_1=$geg or first_synd_2=$geg or first_synd_3=$geg) and (second_synd_1=$own or second_synd_2=$own or second_synd_3=$own))) and status=1 and starttime <= $time");

		if ($check)	{
			if ($wardata[status])	{
				global $war_land_updateaction, $war_land_updateaction_own;
				global $war_id;
				$war_id = $wardata[war_id];
				if ($wardata[first_synd_1] == $geg)	{ $war_land_updateaction = "first_1_llt";}
				elseif ($wardata[first_synd_2] == $geg)	{ $war_land_updateaction = "first_2_llt";}
				elseif ($wardata[first_synd_3] == $geg)	{ $war_land_updateaction = "first_3_llt";}
				elseif ($wardata[second_synd_1] == $geg)	{ $war_land_updateaction = "second_1_llt";}
				elseif ($wardata[second_synd_2] == $geg)	{ $war_land_updateaction = "second_2_llt";}
				elseif ($wardata[second_synd_3] == $geg)	{ $war_land_updateaction = "second_3_llt";}
				if ($wardata[first_synd_1] == $own)	{ $war_land_updateaction_own = "first_1_lwt";}
				elseif ($wardata[first_synd_2] == $own)	{ $war_land_updateaction_own = "first_2_lwt";}
				elseif ($wardata[first_synd_3] == $own)	{ $war_land_updateaction_own = "first_3_lwt";}
				elseif ($wardata[second_synd_1] == $own)	{ $war_land_updateaction_own = "second_1_lwt";}
				elseif ($wardata[second_synd_2] == $own)	{ $war_land_updateaction_own = "second_2_lwt";}
				elseif ($wardata[second_synd_3] == $own)	{ $war_land_updateaction_own = "second_3_lwt";}
			}
		}
	}
	if ($wardata[status]): return 1; else: return 0; endif;
}

/********************************************************************************

Function transformfehlercode

*********************************************************************************/

function transformfehlercode($fehlercode)	{
	if 		($fehlercode == "E0"): $returncode = "Syndikatsmitglied";
	elseif 	($fehlercode == "E1"): $returncode = "Syndikatsmitglied";
	elseif	($fehlercode == "E2"): $returncode = "Größe";
	elseif	($fehlercode == "E3"): $returncode = "Unter Schutz";
	elseif	($fehlercode == "E4"): $returncode = "Im Urlaub";
	elseif	($fehlercode == "E5"): $returncode = "Aktienschutz";
	elseif	($fehlercode == "E6"): $returncode = "Unter Schutz";
	elseif	($fehlercode == "E7"): $returncode = "Max Angriffe err.";
	elseif	($fehlercode == "E8"): $returncode = "Allianzpartner";
	elseif	($fehlercode == "E9"): $returncode = "Abkommen";
	elseif	($fehlercode == "E10"): $returncode = "Größe";
	elseif  ($fehlercode == "E13"): $returncode = "Syndikatstyp";	
	elseif	($fehlercode == "E11"): $returncode = "W";
	elseif	($fehlercode == "E12"): $returncode = "GVI";
	elseif	($fehlercode == "E14"): $returncode = "Neuling";
	elseif  ($fehlercode == "ERIP"): $returncode = "RIP";
	endif;
	return $returncode;
}


/********************************************************************************

Function isattackable

*********************************************************************************/

// Diese Funktion geht davon aus, dass die Daten eines gltigen Spielers bergeben werden
// Es wird nur auf Zusammenh?ge in Relation mit dem zu berprfenden Konzern geprft, das hei?, wenn man z.B.
// selbst noch in Protection ist, muss das anderweitig geprft werden

// $rid:
// $alive:
// $lastlogintime:
// $land:
// $createtime:
// $aktienprozente:
// $isatwar:
// $isprotection:
// $maximum_attacks_reached:
// $allianz_id1:
// $allianz_id2:
// $naps:						Array mit Konzernids als Schlsseln und den Zahlen 1,2,3 als Werte fr den 2.Dimensionsschlssel "type" fr die entsprechenden NAPS (1=NAP, 2=NSP, 3=NASP)
// $id:							Id des Spielers um ihn mit dem NAPS-Array abgleichen zu k?nen
// $kontext:					Kontext des Aufrufes wg. NAPS: 1=isattackable, 2=isspyable, 3=isattackable and/or isspyable,
// 								bei Rckgabe 1 ist beides m?lich, bei Rckgabe 2 ist nur Spy m?lich, bei Rckgabe 3 ist nur Attack m?lich.
//								bei Rckgabe 4 (nur bei isatwar) wird der Konzern von den Kriegsregeln ausgenommen
// $nw - Networth der verteidigenden spielers


function isattackable( $rid, $alive, $lastlogintime, $land, $createtime, $aktienprozente, $isatwar, $isprotection, $maximum_attacks_reached, $allianz_id1, $allianz_id2, $naps, $id, $kontext, $nw, $gvi, $synd_type1, $synd_type2, $inprotection, $unprotecttime)	{
	# ERIP: Konzern ist tot
	# E0: aktiver Konzern aus eigenem Syndikat
	# E1: inaktiver Konzern (zeitlich gesehen) aus eigenem Syndikat im Urlaubsmodus
	# E2: au?rhalb der 20% - 500% - Grenze und NICHT global inaktiv
	# E3: Konzern befindet sich noch unter Schutz
	# E4: Urlaubsmodus aktiv
	# E5: Aktienbesitz erlaubt Angriff nicht
	# E6: Man selbst ist noch unter Schutz
	# E7: Maximalzahl Angriffe je Tag erreicht
	# E8: Syndikate sind miteinander alliiert
	# E9: Es besteht ein NA(S)P (Modi 1), N(A)SP (Modi 2), NASP (Modi 3)
	# E10: Spieler ist kleiner als 1000 Land und hat weniger als 50% Nw oder Land
	# E12: au?rhalb der 66% - 150% grenze wegen networth und gvi
	# E13: Noob <-> Nichtnoobsyndikat
	# E14: Neuling
	## vorbergehend nicht definiert: # E11: Krieg und Spieler weniger als 50% Nw und Land wobei es noch Spieler im gegn. Syndikat gibt, die mehr als 50% Nw ODER Land haben

	global $status,$game, $time, $globals;
	$returnvalue = 1;
	if ($alive > 0) {
		/*	## DIESE REGELUNG WURDE NUR IN RUNDE 5 VERWENDET UND DANACH WIEDER VERWORFEN, WEGEN REGELVEREINFACHUNG
		if ($isatwar && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time)) {
			$landdata = singles("select land from status where rid=$rid order by land asc");
			$number = ceil(count($landdata)/2)-1;
			if ($landdata[$number] >= $land) {
				$returnvalue=4;
			}
		}
		*/
		if(($globals["round"] - single("select startround from users where konzernid = ".$id) <= MENTOR_ROUNDS) && $status["is_mentor"] && single("select mentorsystem from users where konzernid = ".$id) != 1){
			if($kontext == 2){
				$returnvalue = 1;
			}
			elseif($kontext == 3){
				$returnvalue = 2;
			}
			else{
				$returnvalue = "E14";
			}
		}
		else{
			if ($allianz_id1 && $allianz_id1 == $allianz_id2 && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time)){ $returnvalue = "E8"; }
			elseif ($naps[$id][type] == 3 or ($kontext == 1 && $naps[$id][type] == 1) or ($kontext == 2 && $naps[$id][type] == 2)) { $returnvalue = "E9"; }
			elseif ($isprotection) { $returnvalue = "E6"; }
			elseif ($status[rid] == $rid)	{
				if ($lastlogintime + TIME_TILL_INACTIVE > $time)	{ $returnvalue = "E0";}
				elseif (in_protection(array('inprotection' => $inprotection, 'unprotecttime' => $unprotecttime, 'createtime' => $createtime)))	{ $returnvalue = "E3";}
				elseif ($alive == 2)	{ $returnvalue = "E1"; }
			}
			elseif ((($land / $status['land'] < 0.2 || $land / $status['land'] > 5) && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time)))	{ $returnvalue = "E2";}
			elseif (isBasicServer($game) && (($land / $status[land] < 0.5 || $land / $status[land] > 2 || $nw / $status[nw] < 0.5 || $nw / $status[nw] > 2) && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time)) && ($land < 1000 || $status[land] < 1000)) { $returnvalue = "E10"; }
			elseif (in_protection(array('inprotection' => $inprotection, 'unprotecttime' => $unprotecttime, 'createtime' => $createtime)))	{ $returnvalue = "E3"; }
			elseif ($alive == 2)	{ $returnvalue = "E4"; }
			elseif ($aktienprozente >= AKTIEN_PREVENTOPTION && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time) && !$isatwar){
				if (!racherecht($id)){
					$returnvalue = "E5";
				}
			}
			elseif (false && ($gvi || $status[gvi]) && !$isatwar && !racherecht($id) && ($nw / $status[nw] < 2/3 || $nw / $status[nw] > 1.5) && ($lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time)) {$returnvalue="E12";}
			elseif ($maximum_attacks_reached and !($isatwar or racherecht($id)) and (($status[rid] != $rid and $lastlogintime + TIME_TILL_GLOBAL_INACTIVE > $time) or ($status[rid] == $rid and $lastlogintime + TIME_TILL_INACTIVE > $time))) { $returnvalue = "E7"; }
			elseif ($synd_type1 == "normal" && $synd_type2 != "normal" or $synd_type2 == "normal" && $synd_type1 != "normal") { $returnvalue = "E13"; }
	
			if (($returnvalue == 1 or $returnvalue == 4) && $kontext == 3 && $naps[$id][type] == 1) { $returnvalue = 2; }
			elseif (($returnvalue == 1 or $returnvalue == 4) && $kontext == 3 && $naps[$id][type] == 2) { $returnvalue = 3; }
		}
	} else { $returnvalue = "ERIP"; }
	return $returnvalue;
}

function isattackable_paid($id, $isatwar) {
	/*
	global $globals, $time, $status;
	$userid = single("select id from users where konzernid = $id");
	$paid_intern = 1;
	$tpr = single("select round from paid_users_intern where round >= $globals[round] and user_id='$userid'");
	if ($tpr) {
		$paid_intern=1;
	}
	else {
		$aboexists = single("select aboid from payment_aboinfo where userident='$userid' and (paid=1 or zeitraum_start >= $time)");
		$keyexists = single("select round from user_keys where user_id='$userid' and round='$globals[round]'"); // Nachgucken ob fr user user_keys existiert -> user hat einen key eingegeben -> z?lt auch als bezahlt
		// Abo existiert: bezahlt ?
		if ($aboexists || $keyexists) {
				$paid_intern=1;
		}
	}


	if ($isatwar) { $racherecht = single("select count(*) from attacklogs where aid=$id and drid=".$status[rid]." and winner='a' and time > ($time - 24 * 60 * 60)"); }
	elseif (!$racherecht) {
		$racherecht = single("select count(*) from attacklogs where aid=$id and did=".$status[id]." and winner='a' and time > ($time - 24 * 60 * 60)");
	}

	if ($status[paid]) { return 1; }
	else { if (!$racherecht): return $paid_intern ? 0 : 1; else: return 1; endif; }
	*/ 
	return 1;

}


#
#	Function racherecht
#
# funktioniert nur in Bezug auf den Spieler der das Skript ausfhrt, $status muss unbedingt vorhanden sein!
function racherecht($id) {
	
	global $globals, $status, $time;
	$searchtime = $time - 24 * 60 * 60;
	
	//primäre = 1 (direkt)
	//sekundär = 2 (indirekt)
	
	// unsichtbares Racherecht abgeschafft, gibt nur noch mit SpyWeb RR -- Runde 59 Jan 2012 (inok)
	//ally rr
	$rids = '('.$status['rid'];
	$allyid = single('SELECT ally1 FROM syndikate WHERE synd_id = '.$status['rid']);
	if ($allyid != 0) {
		$rids .= ', '.$allyid;
	
	}
	$rids .= ')';
	
	// SpyWeb wird benötigt
	// Bei getarnten SB-Atts haben SB keine Verluste // TODO
	$spyweb_att = "AND 
		(stealthed = 0 OR (SELECT COUNT(*) FROM usersciences WHERE name='glo9' AND user_id=did))";
	
	//fails ohne schaden geben kein Racherecht mehr R59 by dragon12 (nochmal unten bei racherecht_ausgabe())
	$fail_nolosses = 'and (winner = \'a\' or done_unter_racherecht = 1 OR done_unter_racherecht = 2 OR warattack = 1)';
	
	// Ausgetauscht, da in der auskommentierten Version kein RR gegen den Atter gegeben wurde (15.07.2012 - Demajo)
	//$att_anonym = ' and NOT MATCH(bericht) AGAINST (\'+(dies war ein anonymer Auftrag)\' IN BOOLEAN MODE)';
	$att_anonym = ' and NOT (MATCH(bericht) AGAINST (\'+(dies war ein anonymer Auftrag)\' IN BOOLEAN MODE) AND client_id='.$id.')';
	
	//Angriffe
	if(single("SELECT COUNT(*) FROM attacklogs 
			WHERE 
				time > $searchtime and 
				drid in ".$rids." and 
				(aid=$id or client_id=$id) $fail_nolosses $spyweb_att $att_anonym"))
		$privatkrieg=2;
	if(single("SELECT COUNT(*) FROM attacklogs 
			WHERE 
				time > $searchtime and 
				did=".$status[id]." and 
				(aid=$id or client_id=$id) $fail_nolosses $spyweb_att $att_anonym"))
		$privatkrieg=1;

	//Sabbs/Lagerdiebstahl	
	if(
		((single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				drid in ".$rids." and 
				(aid=$id or originid=$id) and 
				success = 1 and 
				spyweb_lvl > 0 and
				(action like 'kill%' or action like 'get%' or action like 'delay%') 
			GROUP BY did 
			ORDER BY COUNT(*) DESC LIMIT 1") >= RACHERECHT_ON_SPYACTIONS_NUMBER)) || 
		single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				drid in ".$rids." and 
				(aid=$id or originid=$id) and 
				success = 1 and 
				spyweb_lvl > 0 and
				(action like 'kill%' or action like 'delay%')") || 
		single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				drid in ".$rids." and 
				(aid=$id or originid=$id) and 
				success = 1 and 
				spyweb_lvl > 0 and
				(action='getpodpoints')") > 1
		AND $privatkrieg != 1
	)  
		$privatkrieg=2;

	if(
		(single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				did=".$status[id]." and 
				(aid=$id or originid=$id) and 
				success = 1 and 
				spyweb_lvl > 0 and
				(action like 'kill%' or action like 'get%' or action like 'delay%') 
				$spyweb_spys") >= RACHERECHT_ON_SPYACTIONS_NUMBER) || 
		single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				did=".$status[id]." and 
				(aid=$id or originid=$id) and 
				success = 1 and 
				spyweb_lvl > 0 and
				(action like 'kill%' or action like 'delay%')") ||
			single("SELECT COUNT(*) FROM spylogs
					WHERE
					time > $searchtime and
					did=".$status[id]." and
					(aid=$id or originid=$id) and
					success = 1 and
					spyweb_lvl > 0 and
					(action='getpodpoints')") > 1
	)
		$privatkrieg=1;
	//Fossab	
	if(single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				drid in ".$rids." and 
				aid = $id and 
				success = 1 and 
				spyweb_lvl > 0 and
				action like 'killsciences'") 
		AND $privatkrieg != 1)
		$privatkrieg=2;

	if(single("SELECT COUNT(*) FROM spylogs 
			WHERE 
				time > $searchtime and 
				did=".$status[id]." and 
				aid = $id and 
				success = 1 and 
				spyweb_lvl > 0 and
				action like 'killsciences'"))
		$privatkrieg=1;
	return $privatkrieg;
}


///
///	Function isonline
///

function isonline($id) {
	global $time;
	return single("select count(*) from sessionids_actual where gueltig_bis > $time and user_id = $id");
}

///
/// racherecht ausgabe
///

function racherecht_ausgabe() {
	global $id, $ripf, $sciences, $personen_data, $status;
	$time = time();
	$racherechttime = $time - 24 * 3600;
	$rids = '('.$status['rid'];
	$allyid = single('SELECT ally1 FROM syndikate WHERE synd_id = '.$status['rid']);
	if ($allyid != 0) {
		$rids .= ', '.$allyid;
		
	}
	$rids .= ')';
	$racherecht = assocs("SELECT * FROM attacklogs 
		WHERE 
			(did = $id or drid in ".$rids.") AND 
			time > $racherechttime AND 
			(stealthed = 0 OR (SELECT COUNT(*) FROM usersciences WHERE name='glo9' AND user_id=did))
			AND (winner = 'a' OR done_unter_racherecht = 1 OR done_unter_racherecht = 2 OR warattack = 1)");
	$racherecht_personen = array();
	//if ($sciences[glo9]) { # Racherechtanzeige von Spionageaktionen, nur wenn Spyweb erforscht
		$racherecht_scienceskill = assocs("SELECT spylogs.*, uncompress(spylogs_berichte.bericht) as bericht FROM spylogs, spylogs_berichte 
				WHERE 
					(did = $id or drid in ".$rids.") AND time > $racherechttime AND success = 1 AND 
					(action like 'killsciences') AND spyweb_lvl > 0 and spylogs.id = spylogs_berichte.log_id");
		$spyactions_pod = assocs("SELECT aid,did, uncompress(spylogs_berichte.bericht) as bericht FROM spylogs, spylogs_berichte 
				WHERE 
					drid in ".$rids." and success =1 and time > $racherechttime and  
					spyweb_lvl > 0 and action = 'getpodpoints' and spylogs.id = spylogs_berichte.log_id order by time asc");
		
		$spyactions_dieb = assocs("SELECT aid,did, uncompress(spylogs_berichte.bericht) as bericht FROM spylogs, spylogs_berichte 
				WHERE 
					drid in ".$rids." and success =1 and time > $racherechttime and  
					spyweb_lvl > 0 and action like 'get%' and spylogs.id = spylogs_berichte.log_id and action != 'getpodpoints' order by time asc");
		$spyactions_sabb = assocs("SELECT aid,did, time, uncompress(spylogs_berichte.bericht) as bericht, 0 as isJob FROM spylogs, spylogs_berichte 
				WHERE 
					drid in ".$rids." and success =1 and time > $racherechttime and  
					spyweb_lvl > 0 and spylogs.id = spylogs_berichte.log_id and (action like 'kill%' or action like 'delay%') UNION
				SELECT originid,did, time, uncompress(spylogs_berichte.bericht) as bericht, 1 as isJob FROM spylogs, spylogs_berichte 
				WHERE 
					drid in ".$rids." and success =1 and time > $racherechttime and  
					spyweb_lvl > 0 and spylogs.id = spylogs_berichte.log_id and (action like 'kill%' or action like 'delay%') order by 3 asc");
		$rr_actions = array();
		$rr_type = array();
		foreach($spyactions_dieb as $act){
			isset($rr_actions[$act['aid']]) ? 1 : $rr_actions[$act[aid]] = array();
			isset($rr_actions[$act['aid']][$act[did]]) ? $rr_actions[$act[aid]][$act[did]]++ : $rr_actions[$act[aid]][$act[did]]=1;
			if($rr_actions[$act[aid]][$act[did]] >= RACHERECHT_ON_SPYACTIONS_NUMBER)
				$rr_type[$act[aid]]=$act['did'];
		}
		foreach($spyactions_pod as $act){
			isset($rr_actions[$act[aid]]) ? 1 : $rr_actions[$act[aid]] = array();
			isset($rr_actions[$act[aid]][$act[did]]) ? $rr_actions[$act[aid]][$act[did]]++ : $rr_actions[$act[aid]][$act[did]]=1;
			if($rr_actions[$act[aid]][$act[did]] >= 2)
				$rr_type[$act[aid]]=$act[did];
		}
		foreach($spyactions_sabb as $act){
			isset($rr_actions[$act[aid]]) ? 1 : $rr_actions[$act[aid]] = array();
			isset($rr_actions[$act[aid]][$act[did]]) ? $rr_actions[$act[aid]][$act[did]]++ : $rr_actions[$act[aid]][$act[did]]=1;
			//if($rr_actions[$act[aid]][$act[did]] >= RACHERECHT_ON_SPYACTIONS_NUMBER)
			if(!(strpos($act['bericht'], "für die Ausführung dieses anonymen Auftrags erhalten.") && $act['isJob'] == 0))
				$rr_type[$act[aid]]=$act[did];
		}
		
	/*	$times_provoked = assocs("select aid, count(*) as tl,did from spylogs where (did = $id or drid=".$status[rid].") and time > $racherechttime and success = 1 and action like 'get%' and action != 'getpodpoints' and (select count(*) from usersciences where name='glo9' and user_id=did) group by did, aid having tl >= '".RACHERECHT_ON_SPYACTIONS_NUMBER."'", "aid");
	$times_provokedEXT = assocs("select originid as aid, count(*) as tl,did from spylogs where (did = $id or drid=".$status[rid].") and time > $racherechttime and success = 1 and action like 'get%' and action != 'getpodpoints' and (select count(*) from usersciences where name='glo9' and user_id=did) group by originid having tl >= '".RACHERECHT_ON_SPYACTIONS_NUMBER."'", "aid");
		$times_others = assocs("select aid, count(*) as tl,did from spylogs where (did = $id or drid=".$status[rid].") and time > $racherechttime and success = 1 and (action like 'kill%' or action like 'delay%') and (select count(*) from usersciences where name='glo9' and user_id=did) group by aid having tl >= 1", "aid");
		$times_othersEXT = assocs("select originid as aid, count(*) as tl,did from spylogs where (did = $id or drid=".$status[rid].") and time > $racherechttime and success = 1 and (action like 'kill%' or action like 'delay%') and (select count(*) from usersciences where name='glo9' and user_id=did) group by aid having tl >= 1", "aid");
		foreach($times_provokedEXT as $item){
			//if(racherecht($item['aid'])) echo "-test-";			
			$times_provoked[] = $item;
		}
		foreach($times_others as $item){
			//if(racherecht($item['aid'])) echo "-test-";			
			$times_provoked[] = $item;
		}
		foreach($times_othersEXT as $item){
			//if(racherecht($item['aid'])) echo "-test-";			
			$times_provoked[] = $item;
		}
		//pvar($racherecht_scienceskill); 
	//}*/
	
	if ($racherecht) {
		$racherecht_copy = $racherecht;//Auftrag anonym eingestellt
		foreach ($racherecht_copy as $ky => $vl) {
			if (strpos($vl['bericht'], "dies war ein anonymer Auftrag")) {
				$racherecht[$ky]['client_id'] = 0;
				$racherecht[$ky]['client_rid'] = 0;
			}
			if (strpos($vl['bericht'], "Dieser Angriff verlief getarnt.") && !strpos($vl['bericht'], "SPYWEB war vorhanden")) {
				if ($racherecht[$ky]['client_id']) {
					$racherecht[$ky]['aid'] = 0;
					$racherecht[$ky]['arid'] = 0;
				}
				else unset($racherecht[$ky]);
			}
		}
	}
	if ($racherecht or $racherecht_scienceskill || $rr_type) {
		if ($racherecht) {
			foreach ($racherecht as $vl) {
				//if(assocs("SELECT * FROM  `usersciences` where name='glo9' and user_id=".$vl[did])){
					if ($vl['aid']) 
					{
						if (!$racherecht_personen[$vl[aid]] || $racherecht_personen[$vl[aid]] < $vl[time]) {
							$racherecht_personen[$vl[aid]] = $vl[time];
						}
						$personen_fuer_sql_abfrage[] = $vl[aid];
					}
					if ($vl['client_id']) {
						if (!$racherecht_personen[$vl[client_id]] or $racherecht_personen[$vl[client_id]] < $vl[time]) {
							$racherecht_personen[$vl[client_id]] = $vl[time];
						}
						$personen_fuer_sql_abfrage[] = $vl[client_id];
					}
				//}
			}
		}
		if ($racherecht_scienceskill) {
			foreach ($racherecht_scienceskill as $vl) {
				if (!$racherecht_personen[$vl[aid]] or $racherecht_personen[$vl[aid]] < $vl[time]) {
					$racherecht_personen[$vl[aid]] = $vl[time];
				}
				$personen_fuer_sql_abfrage[] = $vl[aid];
				if ($vl[aid] != $vl[originid]) { // Passiert nur bei Aufträgen
					if (!$racherecht_personen[$vl[originid]] or $racherecht_personen[$vl[originid]] < $vl[time]) {
						$racherecht_personen[$vl[originid]] = $vl[time];
					}
					$personen_fuer_sql_abfrage[] = $vl[originid];
				}
			}
		}
		if ($rr_type) {
			foreach ($rr_type as $aid=>$did) {
				
				$temp_time = single("SELECT time from spylogs 
					WHERE 
						(aid=$aid or originid=$aid) and did = $did and success = 1 and
						(action like 'kill%' or action like 'get%' or action like 'delay%') 
					ORDER BY time DESC LIMIT 1");
				/*if(assocs("SELECT * FROM  `usersciences` where name='glo9' and user_id=".$vl[did])){
					$temp_time = single("select time from spylogs where aid = ".$vl[aid]." and (did = $id or drid=".$status[rid].")  and time > $racherechttime and success = 1 and (action like 'get%') order by time desc limit ".(RACHERECHT_ON_SPYACTIONS_NUMBER-1).", 1");
					if(!$temp_time)
						$temp_time = single("select time from spylogs where originid = ".$vl[aid]." and (did = $id or drid=".$status[rid].")  and time > $racherechttime and success = 1 and (action like 'get%') order by time desc limit ".(RACHERECHT_ON_SPYACTIONS_NUMBER-1).", 1");
					if(!$temp_time)
						$temp_time = single("select time from spylogs where aid = ".$vl[aid]." and (did = $id or drid=".$status[rid].")  and time > $racherechttime and success = 1 and (action like 'kill%' or action like 'delay%') order by time desc limit 1, 1");
					if(!$temp_time)
						$temp_time = single("select time from spylogs where originid = ".$vl[aid]." and (did = $id or drid=".$status[rid].")  and time > $racherechttime and success = 1 and (action like 'kill%' or action like 'delay%') order by time desc limit 1, 1");*/
					if (!$racherecht_personen[$aid] or $racherecht_personen[$aid] < $temp_time) {
						$racherecht_personen[$aid] = $temp_time;
					}
					$personen_fuer_sql_abfrage[] = $aid;
				//}
			}
		}
		$raceicon=array(	"pbf" => "bf-logo-klein",
					"sl" => "sl-logo-klein",
					"uic" => "uic-logo-klein",
					"nof" => "nof-logo-klein",

					"neb" => "neb-logo-klein");
		$count_direkt = 0;
		$count_indirekt = 0;

		if ($personen_fuer_sql_abfrage) {
			$lines_racherecht_direkt = "<tr><td><div id=\"dirRR\" ".($status['show_dirRR']?"":"style=\"display:none;\"")."><table align=center class=siteGround cellpadding=2 cellspacing=0 width=500>";
			$lines_racherecht_indirekt = "<tr><td><div id=\"indirRR\" ".($status['show_indirRR']?"":"style=\"display:none;\"")."><table align=center class=siteGround cellpadding=2 cellspacing=0 width=500>";
			$personen_data = assocs("select syndicate, rid, race, nw, land, id from status where id in (".join(",", $personen_fuer_sql_abfrage).")", "id");
			foreach ($personen_data as $vl) {
				$difference = -($time - ($racherecht_personen[$vl[id]] + 24 * 3600));
				$hours = floor ( ($difference) / (60 * 60) );
				$minutes = floor ( ($difference - $hours * 60 * 60) / 60 );
				$remaining_time = $hours."h, ".$minutes."m";
				$lines_racherecht = "<tr><td><a href=syndicate.php?rid=$vl[rid]&action=details&detailsid=$vl[id] class=linkAuftableInner><img src=".$ripf.$raceicon[$vl[race]].".gif border=0 width=22 height=22 align=absmiddle> $vl[syndicate] (#".$vl[rid]."), ".pointit($vl[nw])." NW, ".pointit($vl[land])." ha, noch $remaining_time</a></td><td><a href=syndicate.php?rid=$vl[rid] class=linkAuftableInner>Zur Syndikatsübersicht</a></td></tr>";
				
				if(racherecht($vl[id])===1) {
					$lines_racherecht_direkt .= $lines_racherecht;
					$count_direkt++;
				}
				else {
					$lines_racherecht_indirekt .= $lines_racherecht;
					$count_indirekt++;
				}
			}
			$lines_racherecht_indirekt .= "</td></tr></table></div>";
			$lines_racherecht_direkt .= "</td></tr></table></div>";
			if($count_direkt==1)
				$spieler_direkt = "Spieler";
			else
				$spieler_direkt = "Spielern";
			if($count_indirekt==1)
				$spieler_indirekt = "Spieler";
			else
				$spieler_indirekt = "Spielern";
			$toggle_direkt = "<a class=\"linkAuftableInner\" onClick=\"javascript:$('#dirRR').toggle();$.ajax({url: 'rrdisplay_save.php',data: {toggle:'dir'}, success:function(data){if($('#dirRRspan').text()=='-') $('#dirRRspan').text('+'); else $('#dirRRspan').text('-');}}); return false;\">[<span id=\"dirRRspan\">".($status['show_dirRR']?'-':'+')."</span>]</a>";
			$toggle_indirekt = "<a class=\"linkAuftableInner\" onClick=\"javascript:$('#indirRR').toggle();$.ajax({url: 'rrdisplay_save.php',data: {toggle:'indir'}, success:function(data){if($('#indirRRspan').text()=='-') $('#indirRRspan').text('+'); else $('#indirRRspan').text('-');}}); return false;\">[<span id=\"indirRRspan\">".($status['show_indirRR']?'-':'+')."</span>]</a>";
			return "<table align=center class=siteGround cellpadding=2 cellspacing=0 width=500><tr><td>Sie haben derzeit Racherecht  bei folgenden Spielern:  <a href=\"".WIKI."Racherecht\" target=\"_blank\">".getJsHelpTag("Racherecht erhalten Sie gegen Spieler, von denen Sie oder Spieler Ihres Syndicates angegriffen, sabotiert oder beklaut wurden. <br>Für genauere Informationen bitte auf das Fragezeichen klicken")."</a></td></tr>".(($count_direkt>0)?"<tr><td>$toggle_direkt Direktes Racherecht bei $count_direkt $spieler_direkt:</td></tr>":"").$lines_racherecht_direkt.(($count_indirekt>0)?"<tr><td>$toggle_indirekt Indirektes Racherecht bei $count_indirekt $spieler_indirekt:</td></tr>":"").$lines_racherecht_indirekt."</table><br>";
		} else return "";
	}
	return "";
}
	
	
/********************************************************************************

Function isKsyndicates()

*********************************************************************************/

function isKsyndicates() {
	global $_SERVER;
	if (ereg("k-syndicates",$_SERVER['HTTP_HOST'])) return 1;
	return 0;
}


/********************************************************************************

Function isBasicServer()

*********************************************************************************/


function isBasicServer($game) {
	if ($game[servertype] == 1) {
		return 0;
	}
	elseif ($game[servertype] == 2) {
		return 1;
	}
	else {
		exit("Invalid Servertype configuration detected");
	}
}

function isClassicServer($game) {
	if (!isBasicServer($game)) {
		return 1;
	}
	else {
		return 0;
	}
}

/********************************************************************************

Function getServertype()

*********************************************************************************/

function getServertype() {
	global $game;
	static $temp;
	if (!$game && !$temp) {
		$temp = assoc("select * from game limit 1");
	} elseif (!$temp) $temp = $game;
	if ($temp['servertype'] == 1) return "classic";
	if ($temp['servertype'] == 2) return "basic";
}




/********************************************************************************

Function get_hour_time

*********************************************************************************/

// Liefert den Timestamp der letzten vergangenen vollen stunde
function get_hour_time($time)									{
	return (floor(($time / 3600)))*3600;
}

function get_day_time($time) {
	$hours = date("H", $time);
	$minutes = date("i", $time);
	$seconds = date("s", $time);
	return $time - $hours * 3600 - $minutes * 60 - $seconds;
}


/********************************************************************************

Function f (Fehlerausgabe) und s (success) und i (information)

*********************************************************************************/

function f($f) {
    $allfehler = array();
    global $fehler;
	if ($f) {
		array_push ($allfehler, $f);
		foreach ($allfehler as $temp)	{
			$fehler.= $temp."<br>";
		}
	}
}

function s($s)		{
    $allsuccess = array();
    global $successmeldung;
	if ($s) {
	    array_push ($allsuccess, $s);
		foreach ($allsuccess as $temp)	{
			$successmeldung.= "$temp<br>";
		}
	}
}


### Informationmeldung Anfang

function i($i)		{
    $allinformation = array();
	global $informationmeldung;
	if ($i) {
		array_push ($allinformation, $i);
		foreach ($allinformation as $temp)	{
			$informationmeldung.= "$temp<br>";
		}
	}
}
### Informationmeldung Ende

/********************************************************************************

Function getallbuildings

*********************************************************************************/

function getallbuildings($id) {
    global $status;
	global $outerbuildings;
	$temp = 0;
	if ($status{id} == $id) {$status_intern = $status;}
	else {$status_intern = getallvalues($id);}
	if (!$outerbuildings) {
		$buildings = getbuildingstats();
		foreach ($buildings as $value) {
			$temp += $status_intern{$value[name_intern]};
		}
		#pvar($temp);
	}
	else {
		foreach ($outerbuildings as $ky => $vl) {
			$temp += $status_intern[$vl[name_intern]];
		}
	}
	return $temp;
}


/********************************************************************************

Function getallbuildingsunderconstruction

*********************************************************************************/


function getallbuildingsunderconstruction() {
	global $status;
	$return = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name != 'land'");
    return $return;
}



/********************************************************************************

Function speicherbar

*********************************************************************************/
function speicherbar(&$inner,&$sciences_intern) {
	/*
	$addranger=0;
	$addnanos = 0;
    if ($inner{race} == "uic" && $inner{multifunc} == 6) {
		$addnanos = $inner{multiprod};
	} else {$addnanos = 0;}
	if ($inner[race] == "pbf" && !$sciences_intern[mil13]) {
		$addranger = PBF_DEFSPECBONUS*$inner{defspecs}*10;
		// PBF_DEFSPECBONUS IN GLOBALSVARS.PHP
	}
    $sciences_intern{ind2} ?
	$maxsave = ($sciences_intern{ind2}*IND2BONUS2+POWERPLANT_STORE)*($inner{powerplants}+$inner{s_powerplants}+$addnanos)+$addranger
	: $maxsave = POWERPLANT_STORE*($inner{powerplants}+$inner{s_powerplants}+$addnanos)+$addranger;
	$maxsave+= $inner[land] * 50;
	*/
	// ENERGIESPEICHER RUNDE 12 ABGESCHAFFT;

	$maxsave = 9999999999;

	return $maxsave;

}

/********************************************************************************

Function synbonus

*********************************************************************************/

function synbonus($prozent,$inner_sciences) {
    // 1. Prozentualer Anteil des Gebädes (gebäde/allgebäde)), 2. Argument Sciences Hash
	/* OLD - Ge?dert Runde 17 - September 2005
    $prozent*100 > SYNERGIEMAX ? $prozent = SYNERGIEMAX/100 : 1;
    // Es sollen schon bei 35% die vorher erst bei 50% erreichten synergieboni vergeben werden
    $prozent = $prozent / 0.7;
    ($inner_sciences{ind10} && $prozent >= 0.1) ? $synpot = SYNPOT+IND10BONUS : $synpot = SYNPOT;
    $synbonus = pow(($prozent*10),($synpot));
	*/
	//  Neue Formel Runde 17 von Eusterw - September 2005
	if ($prozent > 0.25) $prozent = 0.25;
	$synbonus = $prozent * 200;
	//if ($inner_sciences{ind10}) $synbonus *= 2;
    return $synbonus;
}

/********************************************************************************

Function multiprodverbrauch

*********************************************************************************/

function multiprodverbrauch($inner) {
	if ($inner{multifunc} == 1) {return 10;}
	elseif ($inner{multifunc} == 16) {return 40;}
	elseif ($inner{multifunc} == 11) {return 40;}
	else {return 0;}
}


/********************************************************************************

Function wcenterbonus
returns x mit x als bonus 10 = 10% bonus

*********************************************************************************/

function wcenterbonus($inner) {
	if ($inner[race] == neb) {
		$rel = $inner[ecocenters] / $inner[land];
		if ($rel >= 1) {$rel= 1;}
		$back = $rel * ECOCENTERBONUS * 100;
	}
	else {
		$back = 0;
	}
	return $back;
}



/********************************************************************************

Produktionsaufschl?sselung

*********************************************************************************/



function production($id) {
	global $status;
	global $sciences;
	global $partner;
	global $syndforschungen; // aus update
	global $synmembers; // aus update.php
	global $syndikate_data; // aus update.php
	global $game_syndikat;
	global $resstats;
	global $artefakte;

	if($id == $status['id']) {
		$inner = $status;
		$sciences_intern = $sciences;
		$partner_intern = $partner;
	} else {
		$inner = getallvalues($id);
		$sciences_intern = getsciences($id);
		$partner_intern = getpartner($id);
	}
	
	$result = array();
	$ressources = array('money', 'energy', 'metal', 'sciencepoints');
	
	foreach($ressources as $ressource) {
		$result[$ressource] = array('bonus' => array(), 'production' => array());
		$producers[$ressource] = array();
	}
	$producers['money'] += array('tradecenters', 's_tradecenters');
	$producers['energy'] += array('powerplants', 's_powerplants');
	$producers['metal'] += array('ressourcefacilities', 's_ressourcefacilities');
	$producers['sciencepoints'] += array('sciencelabs', 's_sciencelabs');
	if($inner['race'] == 'uic') {
		$producers[$ressources[($inner['multifunc']-1)/5]][] = 'multiprod';
	}
	
	// ISTP-Produktion
	if(isset($result[$inner['istp_production']])) {
		$result[$inner['istp_production']]['production'] += calculateISTP($inner, $sciences_intern, $inner['istp_production']);
	}
	
	// Partnerboni
	$result['metal']['bonus']['PARTNER_ALLBONUS'] = 20*((int)$partner_intern[6]);
	$result['money']['bonus']['PARTNER_ALLBONUS'] = 20*((int)$partner_intern[6]);
	$result['energy']['bonus']['PARTNER_ALLBONUS'] = 20*((int)$partner_intern[6]);
	$result['sciencepoints']['bonus']['PARTNER_ALLBONUS'] = 20*((int)$partner_intern[6]);
	
	// Pr?sidentenbonus
	if($status['ispresident']) {
		foreach($ressources as $ressource) {
			$result[$ressource]['bonus']['PRAESIBONUS'] = PRAESIBONUS;
		}
	}
	
	//Schutzzeitbonus
	//TODO - kann bestimmt werden über getUnprotectBonus($status) die einen Wert zwischen 0 und 0.072 (0 bis 7,2%) liefert => bei UIC, NEB auf Erz => BF, NOF auf Ene => SL auf FP
	$table = array(
		'pbf' => 'energy',
		'nof' => 'energy',
		'uic' => 'metal',
		'neb' => 'metal',
		'sl' => 'sciencepoints'
	);
	$result[$table[$inner['race']]]['bonus']['UNPROTECTION_BONUS'] = getUnprotectBonus($status)*100;
	$result['money']['bonus']['UNPROTECTION_BONUS'] = getUnprotectBonus($status)*100;
	
	// Monumentbonus
	$table = array(
		'eco_energy_bonus' => 'energy',
		'eco_credit_bonus' => 'money',
		'eco_metal_bonus' => 'metal',
		'eco_sciencepoints_bonus' => 'sciencepoints'
	);
	
	$syndikate_data[$inner[rid]] ? $game_syndikat = $syndikate_data[$inner[rid]] : 1;
	$artefakt_id = $game_syndikat[artefakt_id];
	$artefakt = $artefakte[$artefakt_id];

	if($artefakt['bonusname'] == 'eco_all_bonus') {
		foreach($ressources as $ressource) {
			$result[$ressource]['bonus']['ECO_ALL_BONUS'] = $artefakt['bonusvalue'];
		}
	} else if(isset($table[$artefakt['bonusname']])) {
		$result[$table[$artefakt['bonusname']]]['bonus'][strtoupper($artefakt['bonusname'])] = $artefakt['bonusvalue']; 
	}
	
	// Forschungsboni
	$result['money']['bonus']['IND9'] = IND9BONUS*$sciences_intern['ind9'];	
	$result['money']['bonus']['IND10'] = IND10BONUS_PROD*$sciences_intern['ind10'];	
	
	$result['energy']['bonus']['IND2'] = IND2BONUS*$sciences_intern['ind2'];
	$result['energy']['bonus']['IND9'] = IND9BONUS_OTHER*$sciences_intern['ind9'];
	$result['energy']['bonus']['IND10'] = IND10BONUS_PROD*$sciences_intern['ind10'];
	
	$result['metal']['bonus']['IND1'] = IND1BONUS*$sciences_intern['ind1'];	
	$result['metal']['bonus']['IND9'] = IND9BONUS_OTHER*$sciences_intern['ind9'];	
	$result['metal']['bonus']['IND10'] = IND10BONUS_PROD*$sciences_intern['ind10'];	
	
	$result['sciencepoints']['bonus']['IND17'] = IND17BONUS*$sciences_intern['ind17'];	
	$result['sciencepoints']['bonus']['IND9'] = IND9BONUS_OTHER*$sciences_intern['ind9'];	
	$result['sciencepoints']['bonus']['IND10'] = IND10BONUS_PROD*$sciences_intern['ind10'];	
	
	
	// Fraktionsabh?ngige Produktion und -boni
	if($inner['race'] == 'pbf') {
		$result['energy']['bonus']['PBF_ENERGYBONUS'] = 0;
		$result['sciencepoints']['bonus']['PBF_SCIENCE_MALUS'] = 0;
	
	} else if($inner['race'] == 'uic') {
		$result['metal']['bonus']['UIC_METAL_BONUS'] = 0;
		foreach($result as $key => $_) {
			$result[$key]['bonus']['UIC_PAUSCHAL_RESSOURCENBONUS'] = UIC_PAUSCHAL_RESSOURCENBONUS;
		}
	
	} else if($inner['race'] == 'sl') {
		$result['sciencepoints']['bonus']['SL_SCIENCE_BONUS'] = 0;
		$result['metal']['bonus']['SL_METAL_MALUS'] = 0;
	
	} else if($inner['race'] == 'nof') {
		$result['energy']['bonus']['NOF_ENERGYBONUS'] = 0;
		$result['money']['bonus']['NOF_CREDIT_MALUS'] = 0;
	
	} else if($inner['race'] == 'neb') {
		$result['metal']['bonus']['NEB_METAL_BONUS'] = 0;
		
		// Wirtschaftszentren
		foreach($ressources as $ressource) {
			$result[$ressource]['bonus']['WCENTERBONUS'] = wcenterbonus($inner);
		}
	}
	
	// Synergiebonus
	$land = ($inner['land'] > 0 ? $inner['land'] : 1);
	foreach($ressources as $ressource) {
		$producer = 0;
		foreach($producers[$ressource] as $building) {
			$producer += $inner[$building];
		}
		$result[$ressource]['bonus']['SYNERGY_BONUS'] = synbonus($producer/$land, $sciences_intern);
	}
	
	foreach($ressources as $ressource) {
		$preserve = false;
		foreach(array('bonus','production') as $key) {
			$result[$ressource][$key] = array_filter($result[$ressource][$key]);
			$preserve |= (count($result[$ressource][$key]) > 0);
		}
		
		$produces = false;		
		foreach($producers[$ressource] as $building) {
			$produces |= ($inner[$building] > 0);
		}
		
		
		if(!$preserve || !$produces) {
			unset($result[$ressource]);
		}
	}
	
	// bisher sind nur die Boni gefragt:
	$return = array();
	foreach($ressources as $ressource) {
		if(isset($result[$ressource])) {
			$return[$ressource] = $result[$ressource]['bonus'];
		}
	}
	
	return $return;
}

function calculateISTP($inner, $sciences, $ressource) {
	$result = array('ISTP_OWN' => 0, 'ISTP_OTHERS' => 0);
	if ($inner['istp_changetime'] > 0 || $ressource != $inner['istp_production']) {
		return $result;
	}
	
	$bonusgebaeude = array(
		"tradecenters",
		"ressourcefacilities",
		"powerplants",
		"sciencelabs",
		"s_tradecenters",
		"s_ressourcefacilities",
		"s_powerplants",
		"s_sciencelabs",
		"multiprod"
	);
	$f = array(
		'money' => 1,
		'energy' => ENERGY_STD_VALUE_TRADE,
		'metal' => METAL_STD_VALUE_TRADE,
		'sciencepoints' => SCIENCEPOINTS_STD_VALUE_TRADE
	);

	// Warum "Creditforschung"?
	$creditforschung = determine_creditforschung($inner, $sciences);
	// Umrechnungsfaktoren k und  berechnen
	$k1 = $sciences['ind15'] * SYNFOS_TRADE_OWN / $f[$inner['istp_production']];
	$k2 = $creditforschung * SYNFOS_TRADE_OTHER / $f[$inner['istp_production']];

	foreach($bonusgebaeude as $value) {
		$result['ISTP_OWN'] += floor($inner[$value] * $k1);
		$result['ISTP_OTHERS'] += floor($inner[$value] * $k2);
	}
	return $result;
}


/********************************************************************************

Function energyadd

*********************************************************************************/

// Optional 2. Argzment:
// 1: Nur Produktion
// 2: Nur Verbrauch
// 3: Lagerkapazitä
// ansonsten wird der zuwachs angegeben

function energyadd($id) {
	//define(PBF_ENERGYBONUS,10);
	define(PBF_ENERGYBONUS,0); // Seit runde 30 0
	define(PARTNER_ENERGYBONUS,20);
if (getServertype() == "basic"){
	define(ENERGYFORSCHUNGWERT,2);
	define(ENERGY_SAVING_PROGRAM_OWN_BONUS, 20); // Eingefhrt Runde 20 - 10.03.2006
}else{
        define(ENERGYFORSCHUNGWERT,1.5);
        define(ENERGY_SAVING_PROGRAM_OWN_BONUS, 15); // Eingefhrt Runde 20 - 10.03.2006
}
	define(NOF_ENERGYBONUS,0); // 10 % Energiebonus f?r nof // seit runde 30 0

    if (func_num_args() > 1) {$opt = func_get_arg (1);}
	if ($opt == 3) {return 9999999999;}

    global $status;
    global $sciences;
	global $partner;
	global $syndforschungen; // aus update
	global $synmembers; // aus update.php
	global $syndikate_data; // aus update.php
	global $game_syndikat;
	global $resstats,$artefakte;

     if($id == $status{id}) {
        $inner = $status;
        $sciences_intern = $sciences;
		$partner_intern = $partner;
     }
     else {
        $inner = getallvalues($id);
        $sciences_intern = getsciences($id);
		$partner_intern = getpartner($id);
    }

    $need = array (ressourcefacilities=>40,sciencelabs=>40,depots=>25,spylabs=>25,offtowers=>10,deftowers=>10,mines=>25,buildinggrounds=>10,factories=>40,tradecenters=>10,s_tradecenters=>10,s_ressourcefacilities=>40,s_sciencelabs=>40,seccenters=>25,schools=>40,armories=>25,ecocenters=>10,banks=>10,behemothfactories=>40);

/*
 * ?nderung r46 DragonTEC: Alle synfos berechnungen wurden in get_synfos_count_extern zusammengefasst
*/
	
	$energyforschung = get_synfos_count_extern($inner, "ind16", $inner['rid']);
	
	global $resstats; if (!$resstats): $resstats = getresstats(); endif;
	//if ($id == 20179) echo "energyforschung: $energyforschung<br>";

        // Stromverbrauch der einzelnen Gebäde

    $energyboni=0;
	$energysurplus=0;
	$lageradd=0;
	$mulpro=0;
	$handelspunkteadd=0;
	$dividendenadd=0;
	$verbrauchbonus=$energyforschung*SYNFOS_ISESP_OTHER;
	# Partnerschaftsbonus
	if ($partner_intern[6]) { $energyboni += PARTNER_ALLBONUS * $partner_intern[6]; }	# Partnerschaftsbonus: +10% Energieproduktion
	# forschung
	if ($sciences_intern{ind2}) {$energyboni +=IND2BONUS*$sciences_intern{ind2};}
	if ($sciences_intern{ind9}) {$energyboni +=IND9BONUS_OTHER*$sciences_intern['ind9'];}
	if ($sciences_intern{ind10}) $energyboni += IND10BONUS_PROD;
    # synergiebonus
    $allbuildings = getallbuildings($inner{id});
	$synbuildings_energy = $inner{powerplants} + $inner{s_powerplants};
	if ($inner{race} == "uic" && $inner{multifunc} == 6) {
		$synbuildings_energy += $inner{multiprod}; // Multiprod dazuaddieren, falls produktion gerade energie ist
	}
    if ($inner{land}==0) {
       $relpowerplants = $synbuildings_energy;
    } else {
      $relpowerplants =  $synbuildings_energy / $inner{land};
    }
    $synbonus = synbonus($relpowerplants,$sciences_intern);
    $energyboni += $synbonus;
    // 10% Energiebonus fr pbf
    if ($inner{race} == "pbf" ) {
        $energyboni += PBF_ENERGYBONUS;
    }
    if ($inner{race} == "uic" ) {
        $energyboni += UIC_PAUSCHAL_RESSOURCENBONUS;
    }
    if ($inner{race} == "nof" ) {
        $energyboni += NOF_ENERGYBONUS;
    }
    
	#unprot bonus
	if ($inner{race} == "pbf"||$inner{race} == "nof") {
    	$energyboni += getUnprotectBonus($status)*100;
   	}
	
	## artefakte bonus
	$syndikate_data[$inner[rid]] ? $game_syndikat = $syndikate_data[$inner[rid]] : 1;
	$artefakt_id = $game_syndikat[artefakt_id];
	$artefakte[$artefakt_id][bonusname] == "eco_energy_bonus" ? $energyboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	$artefakte[$artefakt_id][bonusname] == "eco_all_bonus" ? $energyboni += $artefakte[$artefakt_id][bonusvalue] : 1;


	// Speicherbar
	$maxsave = speicherbar($inner,$sciences_intern);
	# verbrauch berechnen
	$verbrauch = 0;
	foreach ($need as $key => $value) {
		$verbrauch += $inner{$key}*$value;
	}
	$energyboni += wcenterbonus($inner);

	## Pr?i Bonus
	if ($status[ispresident]): $energyboni += PRAESIBONUS; endif;

    if ($inner{race} == "uic"):
		// Multifunc definition:
		// Handelszentrum, uic: 1-5
		// Kraftwerk, uic: 6-10
		// Erzf?deranlage, uic : 11-15
		// Forschungslabor, uic: 16-20
		// Angriffspunkte, pbf: int
		$verbrauch += $inner{multiprod} * multiprodverbrauch($inner);
		//$verbrauchbonus-=10; # +10% Energieverbrauch-Malus abgeschafft Runde 18
	endif;

	// ?derung Runde 20: Syndikatsforschungen bringen bei Erforschung bis zu 50% selbst:
	$verbrauchbonus += $sciences_intern['ind16'] * SYNFOS_ISESP_OWN;
	$verbrauchbonus += $partner_intern[26] * 20; // Gebäude verbrauchen 20% weniger Energie - Partnerschaftsbonus
	

	$verbrauch = $verbrauch * ((100-$verbrauchbonus) /100);

	$verbrauch < 0 ? $verbrauch = 0 : 1;

    if ($opt == 2) {return round($verbrauch);}
    # produktion berechnen
	$standardprod = $inner{powerplants}*POWERPLANT_PRODUCTION;
	if ($inner{race} == "uic" && $inner{multifunc} == 6) {
		$mulpro = $inner{multiprod}*MULTI_ENERGY_PRODUCTION; // Multiprod dazuaddieren, falls produktion gerade energie ist
	}
    $produktion = ($standardprod * (1+($energyboni/100)));
	if ($inner{race} == "uic" && $inner{multifunc} == 6) {
		$lageradd += $mulpro * (1+($energyboni/100));
		//$handelspunkteadd += round($lageradd * $resstats[energy][value]);
		//$dividendenadd += round($lageradd * 15 / 85);
	}
	if ($inner[s_powerplants]): $lageradd += round($inner[s_powerplants] * S_POWERPLANT_PRODUCTION * (1+($energyboni/100)));  endif;
    if (($inner{race} == "pbf" || $inner{race} == "nof") && $sciences_intern{ind22}) {
         $produktion += ($inner{defspecs}+$inner{offspecs})*PBF_DEFSPECBONUS;
    }
	$handelspunkteadd += round($lageradd * $resstats[energy][value]); 
	$dividendenadd += round($lageradd * 15 / 85);

	if ($inner[istp_production] == "energy" && $inner[istp_changetime] == 0) {
		$creditforschung = determine_creditforschung($inner, $sciences_intern);
		$bonusgebaeude = array("tradecenters","ressourcefacilities","powerplants","sciencelabs","s_tradecenters","s_ressourcefacilities","s_powerplants","s_sciencelabs","multiprod");
		$umrechnungsfaktor = ($sciences_intern['ind15'] * SYNFOS_TRADE_OWN + $creditforschung * SYNFOS_TRADE_OTHER) / ENERGY_STD_VALUE_TRADE;
		foreach ($bonusgebaeude as $value) {
			$produktion += floor($inner{$value} * $umrechnungsfaktor);
		}
	}


    if ($opt == 1) {return round($produktion);}
    # berschuss berechnen
	// opt 6 fr bauh?e und facs
	if ($opt == 6) {
		return ($produktion - $verbrauch);
	}
    $energyadd = $produktion - $verbrauch;
	if ($syndforschungen) filelog("#$id($syndikatsforschungsmodus) - energyforschung: $energyforschung($energyforschung_pre) - energyadd: $energyadd - verbrauch: $verbrauch - verbrauchbonus: $verbrauchbonus\n", "energylossbug.log");
    // Zuviel energie ?
	if ($opt == 4) {
		$energyloss = 0;
		if ($inner[energy] > $maxsave) {
			$energyloss = $inner[energy] - $maxsave;
			$energyadd = 0;
			$inner[energy] = $maxsave;
		}
		elseif ($energyadd + $inner{energy} > $maxsave)	{
			$energyloss = $energyadd - ($maxsave - $inner{energy});
			$energyadd = $maxsave - $inner{energy};
		}
		return array( round($energyadd), round($energyloss), $lageradd, $handelspunkteadd, $dividendenadd );
	}
	if ($opt == 5)	{
		return array( round($energyadd), $lageradd, $handelspunkteadd);
	}
    ($energyadd+$inner{energy}) > $maxsave ? $energyadd = $maxsave-$inner{energy}:1;
    #$status{energy} >= $maxsave ? $energyadd = 0:1;
    return array( round($energyadd), $lageradd, $handelspunkteadd);

}


/********************************************************************************

Function sciencepointsadd

*********************************************************************************/

function sciencepointsadd($id) {

    define(SL_SCIENCE_BONUS,0); // Seit runde 30 0 
    define(PBF_SCIENCE_MALUS,0); // Seit runde 30 0
	define(PARTNER_SCIENCEPOINTSBONUS,20);
	define(IND17BONUS,10); // Seit Runde 39

    // Werte holen
    global $status;
    global $sciences;
	global $partner;
	global $resstats,$game_syndikat,$syndikate_data,$artefakte;

     if($id == $status{id}) {
        $inner = $status;
        $sciences_intern = $sciences;
		$partner_intern = $partner;
     }
     else {
        $inner = getallvalues($id);
        $sciences_intern = getsciences($id);
		$partner_intern = getpartner($id);
    }
	global $resstats; if (!$resstats): $resstats = getresstats(); endif;

    $scienceboni=0;
	$lageradd=0;
	$handelspunkteadd=0;
	$dividendenadd=0;
	$mulpro=0;
    #produktion berechnen
    $sciencepointsadd = $inner{sciencelabs}*SCIENCELAB_PRODUCTION;				# Anzahl der Sciencelabs mit 5 multiplizieren
	$synbuildings_sciencepoints = $inner{sciencelabs} + $inner{s_sciencelabs};
	if ($inner{race} == "uic" && $inner{multifunc} == 16) {
		$synbuildings_sciencepoints += $inner{multiprod};
		$mulpro = $inner{multiprod} * MULTI_FP_PRODUCTION;
	}

    # boni berechnen
    if ($sciences_intern{ind17}) {$scienceboni+=IND17BONUS*$sciences_intern{ind17};}			# Falls die Forschung "ind17" ("Scientific Advances") erforscht ist
	if ($sciences_intern{ind10}) $scienceboni += IND10BONUS_PROD;
	if ($sciences_intern{ind9}) {$scienceboni +=IND9BONUS_OTHER*$sciences_intern['ind9'];}
    if ($inner{race} == "uic") {
    	$scienceboni += UIC_PAUSCHAL_RESSOURCENBONUS;
   	}
    if ($inner{race} == "sl") {$scienceboni += SL_SCIENCE_BONUS;}		# Falls die Rasse "sl" ist, Anzahl der Sciencepoints um 25 % erh?en
    if ($inner{race} == "pbf") {$scienceboni -=PBF_SCIENCE_MALUS;}		# Falls die Rasse "pbf" ist, Anzahl der Sciencepoints um 10 % erniedrigen.
	if ($partner_intern[6]) { $scienceboni += PARTNER_ALLBONUS * $partner_intern[6]; }# Partnerschaftsbonus: +10% Forschungspunkteproduktion

	#unprot bonus
	if ($inner{race} == "sl") {
    	$scienceboni += getUnprotectBonus($status)*100;
   	}
	
	## artefakte bonus
	$syndikate_data[$inner[rid]] ? $game_syndikat = $syndikate_data[$inner[rid]] : 1;
	$artefakt_id = $game_syndikat[artefakt_id];
	$artefakte[$artefakt_id][bonusname] == "eco_sciencepoints_bonus" ? $scienceboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	$artefakte[$artefakt_id][bonusname] == "eco_all_bonus" ? $scienceboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	
	
	## Pr?i Bonus
	if ($status[ispresident]): $scienceboni += PRAESIBONUS; endif;

    #synergiebonus
    $allbuildings = getallbuildings($inner{id});
    if ($inner{land}==0) {
       $relsciencelabs = $synbuildings_sciencepoints;
    } else {
      $relsciencelabs = $synbuildings_sciencepoints / $inner{land};
    }
    $scienceboni += synbonus($relsciencelabs,$sciences_intern);
	$scienceboni += wcenterbonus($inner);
    # boni verrechnen
    $sciencepointsadd *= (1+($scienceboni/100));
	if ($inner{race} == "uic" && $inner{multifunc} == 16) {
		$lageradd += $mulpro * (1+($scienceboni/100));
		//$handelspunkteadd += round($lageradd * $resstats[sciencepoints][value]); 
		//$dividendenadd += round($lageradd * 15 / 85);
	}
	if ($inner[s_sciencelabs]):
		 $lageradd += round($inner[s_sciencelabs] * S_SCIENCELAB_PRODUCTION * (1+($scienceboni/100))); 
	endif;
	$handelspunkteadd += round($lageradd * $resstats[sciencepoints][value]); 
	$dividendenadd += round($lageradd * 15 / 85);
    #(energyadd($status{id}) + $status{energy}) >= 0 ? 1 : $sciencepointsadd *= 0.5;
    ### Sl ranger produzieren forschungspunkte
    if ($inner{race} == "sl" && $sciences_intern{ind22}) {$sciencepointsadd += floor(($inner{defspecs}+$inner{offspecs} ) / 3);}

	if ($inner[istp_production] == "sciencepoints" && $inner[istp_changetime] == 0) {
		$creditforschung = determine_creditforschung($inner, $sciences_intern);
		$bonusgebaeude = array("tradecenters","ressourcefacilities","powerplants","sciencelabs","s_tradecenters","s_ressourcefacilities","s_powerplants","s_sciencelabs","multiprod");
		$umrechnungsfaktor = ($sciences_intern['ind15'] * SYNFOS_TRADE_OWN + $creditforschung * SYNFOS_TRADE_OTHER) / SCIENCEPOINTS_STD_VALUE_TRADE;  
		foreach ($bonusgebaeude as $value) {
			$sciencepointsadd += floor($inner{$value} * $umrechnungsfaktor);
		}
	}


	return array(round($sciencepointsadd), $lageradd, $handelspunkteadd, $dividendenadd);
}

/********************************************************************************

Function metaladd

*********************************************************************************/

function metaladd($id) {

    define(UIC_METAL_BONUS,0); // Veraltet
    define(NEB_METAL_BONUS,0); // prozentualer bonus fr neb // Seit runde 30 0
    define(SL_METAL_MALUS,0); //Geldmalus von sl in prozent (also 10%) // Seit runde 30 0
    define(IND1BONUS,10);
	define(PARTNER_METALBONUS,20);
    // Werte holen
    global $status;
    global $sciences;
	global $partner;
	global $resstats,$artefakte,$game_syndikat,$syndikate_data;

     if($id == $status{id}) {
        $inner = $status;
        $sciences_intern = $sciences;
		$partner_intern = $partner;
     }
     else {
        $inner = getallvalues($id);
        $sciences_intern = getsciences($id);
		$partner_intern = getpartner($id);
    }
    $metalboni=0;
	$lageradd=0;
	$handelspunkteadd=0;
	$dividendenadd=0;
	$mulpro=0;
	global $resstats; if (!$resstats): $resstats = getresstats(); endif;

    # produktion berechnen
    $metaladd = $inner{ressourcefacilities} * RESFAC_PRODUCTION;			# Anzahl der Ressourcefacilities mit 25 multiplizieren
	$synbuildings_metal = $inner{ressourcefacilities} + $inner[s_ressourcefacilities];
	if ($inner{race} == "uic" && $inner{multifunc} == 11) {
		$synbuildings_metal += $inner{multiprod};
		$mulpro = $inner{multiprod} * MULTI_METAL_PRODUCTION;
	}

    # boni bestimmen
    if ($inner{race} == "uic") {
    	$metalboni += UIC_PAUSCHAL_RESSOURCENBONUS;
   	}
    elseif ($inner{race} == "neb") {$metalboni += NEB_METAL_BONUS;}				# Falls die Rasse "uic" ist, Anzahl des Metalls um 10 % erh?en
	elseif ($inner{race} == "sl")	{ $metalboni -= SL_METAL_MALUS;}
	
	#unprot bonus
	if ($inner{race} == "uic"||$inner{race} == "neb") {
    	$metalboni += getUnprotectBonus($status)*100;
   	}
	
	
	## artefakte bonus
	$syndikate_data[$inner[rid]] ? $game_syndikat = $syndikate_data[$inner[rid]] : 1;
	$artefakt_id = $game_syndikat[artefakt_id];
	$artefakte[$artefakt_id][bonusname] == "eco_metal_bonus" ? $metalboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	$artefakte[$artefakt_id][bonusname] == "eco_all_bonus" ? $metalboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	
    if ($sciences_intern{ind1}) {$metalboni+=IND1BONUS*$sciences_intern{ind1};}			# Falls die Forschung "ind1" ("Increased Mining Production") erforscht ist, Metall um 5% erh?en
	if ($sciences_intern{ind9}) {$metalboni +=IND9BONUS_OTHER*$sciences_intern['ind9'];}
	if ($sciences_intern{ind10}) $metalboni += IND10BONUS_PROD;
	if ($partner_intern[6]) { $metalboni += PARTNER_ALLBONUS * $partner_intern[6]; }	# Partnerschaftsbonus: +10% Erzproduktion
    # synergiebonus
    $allbuildings = getallbuildings($inner{id});
    if ($inner{land}==0) {
       $relressourcefacilities = $synbuildings_metal;
    } else {
      $relressourcefacilities = $synbuildings_metal / $inner{land};
    }
    $metalboni += synbonus($relressourcefacilities,$sciences_intern);
	$metalboni += wcenterbonus($inner);

	## Pr?i Bonus
	if ($status[ispresident]): $metalboni += PRAESIBONUS; endif;


    # boni verrechnen
    $metaladd *= (($metalboni/100)+1);
	if ($inner{race} == "uic" && $inner{multifunc} == 11) {
		$lageradd += $mulpro * (1+($metalboni/100));
		//$handelspunkteadd += round($lageradd * $resstats[metal][value]);
		//$dividendenadd += round($lageradd * 15 / 85);
	}
	if ($inner[s_ressourcefacilities]): $lageradd += round($inner[s_ressourcefacilities] * S_RESFAC_PRODUCTION * (1+($metalboni/100))); endif;
	
	$handelspunkteadd += round($lageradd * $resstats[metal][value]);
	$dividendenadd += round($lageradd * 15 / 85);

    #(energyadd($status{id}) + $status{energy}) >= 0 ? 1 : $metaladd *= 0.5;
    ## Uic ranger produzieren erz
    if (($inner{race} == "uic") && $sciences_intern{ind22}) {$metaladd +=floor($inner{defspecs}+$inner{offspecs} );}

	## Rangerproduktion vorerst deaktiviert
    //if ($inner{race} == "uic" && !$sciences_intern{mil13}) {$metaladd +=$inner{defspecs}/2;}

	if ($inner[istp_production] == "metal" && $inner[istp_changetime] == 0) {
		$creditforschung = determine_creditforschung($inner, $sciences_intern);
		$bonusgebaeude = array("tradecenters","ressourcefacilities","powerplants","sciencelabs","s_tradecenters","s_ressourcefacilities","s_powerplants","s_sciencelabs","multiprod");
		$umrechnungsfaktor = ($sciences_intern['ind15'] * SYNFOS_TRADE_OWN + $creditforschung * SYNFOS_TRADE_OTHER) / METAL_STD_VALUE_TRADE;
		foreach ($bonusgebaeude as $value) {
			$metaladd += floor($inner{$value} * $umrechnungsfaktor);
		}
	}


	return array(round($metaladd), $lageradd, $handelspunkteadd, $dividendenadd);
}


/********************************************************************************

Function moneyadd

*********************************************************************************/

function moneyadd($id) {

    // Produktionswerte definieren:
    define(UIC_RESFAC_BONUS,0); // UIC produktion fr ressourcefacilities
	define(UIC_SENTINAL_ADD, 5); # jeder Sentinel produziert 10 Cr // R28 ausgebaut // Runde 36 wieder eingebaut //R61 von 10 auf 5
	define(NOF_CREDITMALUS,0); // NOF produziert 20% weniger creds // 0 Seit R30
    define(MAXGLOVALUE,10000000); // Maximal 10 mille fr glo7 forschung als verrechnungsbasis
	define(MAXPARTNERZINSVALUE, MAXGLOVALUE); // Durch den Partnerbonus k?nnen maimal 10 Mio zus?tzlich verzinst werden
    define(GLO7BONUS,2); // 2% bonus von glo7 forschung + 0.5 pro Stufe extra
    define(GLO7BONUS_SKILL,0.5); // glo7 forschung + 0.5% pro Stufe extra
	define(PARTNERZINSBONUS, GLO7BONUS); // 2% bonus vom partnerbonus
	define(PRESIDENT_BONUS,5); # 5% Bonus fr Pr?identen
	define(PARTNER_CREDITBONUS,20); // 20% Bonus fr partnerschaft

    // Werte holen
    global $status;
    global $sciences;
	global $partner;
	global $syndikate_data;
	global $game_syndikat,$artefakte;

     if($id == $status{id}) {
        $inner = $status;
        $sciences_intern = $sciences;
		$partner_intern = $partner;
     }
     else {
        $inner = getallvalues($id);
        $sciences_intern = getsciences($id);
		$partner_intern = getpartner($id);
    }
	global $resstats; if (!$resstats): $resstats = getresstats();  endif;

    $moneyboni=0;
    $moneyadd =0;
	$handelspunkteadd=0;
	$dividendenadd=0;
	$lageradd= 0;
	$mulpro=0;
	
    ## uic bonus
    # Produktion von tradecenters und minen
    $tradecenteradd = $inner{tradecenters}*TRADECENTER_PRODUCTION;		# Anzahl der Tradecenters mit 125 multiplizieren
	
    if ($inner{race} == "uic") {
		$resfacadd = ($inner{ressourcefacilities} + $inner{s_ressourcefacilities})*UIC_RESFAC_BONUS;
		if ($inner{multifunc} == 1) {
		    $mulpro= $inner{multiprod}*MULTI_CR_PRODUCTION;		# Anzahl der Multiprods mit 125 multiplizieren, falls diese gerade credits produzieren
		}
	}	# Falls Rasse "uic" ist, zu Creditplus Anzahl der RF * 10 addieren


    ## rassen und forschungsboni
    if ($sciences_intern{ind9}) {$moneyboni +=IND9BONUS*$sciences_intern{ind9};}	# Falls die Forschung "ind9" ("Markstrategien") erforscht ist, Creditplus um 20 % erh?en
	if ($sciences_intern{ind10}) $moneyboni += IND10BONUS_PROD;
	if ($partner_intern[6]) { $moneyboni += PARTNER_ALLBONUS * $partner_intern[6]; }	# Partnerschaftsbonus: +10% Creditproduktion
	$moneyboni += wcenterbonus($inner);

	#unprot bonus
   	$moneyboni += getUnprotectBonus($status)*100;
   		
	## artefakte bonus
	$syndikate_data[$inner[rid]] ? $game_syndikat = $syndikate_data[$inner[rid]] : 1;
	$artefakt_id = $game_syndikat[artefakt_id];
	$artefakte[$artefakt_id][bonusname] == "eco_credit_bonus" ? $moneyboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	$artefakte[$artefakt_id][bonusname] == "eco_all_bonus" ? $moneyboni += $artefakte[$artefakt_id][bonusvalue] : 1;
	
	## Pr?i Bonus
	if ($status[ispresident]): $moneyboni += PRAESIBONUS; endif;


    # synergiebonus
    $allbuildings = getallbuildings($inner{id});
	$synbuildings_money = $inner{tradecenters} + $inner{s_tradecenters};
	if ($inner{race} == "uic" && $inner{multifunc} == 1) {
		$synbuildings_money += $inner{multiprod};
	}
    if ($inner{land}==0) {
       $reltradecenters = 0;
    } else {
      $reltradecenters = $synbuildings_money / $inner{land};
    }
    $synboni += synbonus($reltradecenters,$sciences_intern);

    ## boni verrechnen
	if ($inner[race] == "uic") {
		$sentinelproduction = $inner[techs] * UIC_SENTINAL_ADD;
		$moneyboni += UIC_PAUSCHAL_RESSOURCENBONUS;
	} 
	else {
		$sentinelproduction = 0;
	}
	if ($inner[race] == "nof") {
		$moneyboni -= NOF_CREDITMALUS;
	}
    $moneyadd = $tradecenteradd * ((($synboni+$moneyboni)/100)+1) + $sentinelproduction;
	if ($inner{race} == "uic" && $inner{multifunc} == 1) {
		$lageradd += $mulpro * (1+(($synboni+$moneyboni)/100));
		//$handelspunkteadd += round($lageradd * $resstats[money][value]); 
		//$dividendenadd += round($handelspunkteadd * ZINSEN_CREDITS / (100 - ZINSEN_CREDITS));
	}
	if ($inner[s_tradecenters]): $lageradd += round($inner[s_tradecenters] * S_TRADECENTER_PRODUCTION * (1+(($synboni+$moneyboni)/100))); endif;
	
	$handelspunkteadd += round($lageradd * $resstats[money][value]); 
	$dividendenadd += round($handelspunkteadd * ZINSEN_CREDITS / (100 - ZINSEN_CREDITS));
	
    // Genug energie vorhanden ? andernfalls produktion halbieren
    #(energyadd($status{id}) + $status{energy}) >= 0 ? 1 : $moneyadd *= 0.5;

    // zinsforschung (Investment Strategies)
    $investLvl = $sciences_intern{glo7} + ($partner[16] ? 1 : 0);
    $maxzinsenbestand = MAXGLOVALUE*$investLvl;
    $minzinsenbestand = 0; // Negative Geldbetr?ge werden nicht verzinst
    $glo7_zinssatz = (GLO7BONUS + $investLvl * GLO7BONUS_SKILL)/100;
	$maxzinsen_aus_partnerbonus = $partner[16] * MAXPARTNERZINSVALUE;
	$already_verzinst = 0;
	if ($inner[money] > $minzinsenbestand) {
		if ($investLvl && $inner{money} <= $maxzinsenbestand ) {
			$moneyadd += $inner{money}*$glo7_zinssatz; $already_verzinst = $inner[money];
		} elseif ($investLvl && $inner{money} > $maxzinsenbestand) {
			$moneyadd += $maxzinsenbestand*$glo7_zinssatz; $already_verzinst = $maxzinsenbestand;
		}
	}
	
	//fsr
	$moneyforfsr = $sciences_intern{glo10} ? GLO10BONUS_MONEY_GAIN_SPY * ($status[offspies]+$status[defspies]+$status[intelspies]) : 0;
	$moneyadd += $moneyforfsr;
	
	## neb ranger produzieren cr
    if (($inner{race} == "neb") && $sciences_intern{ind22}) {$moneyadd +=floor($inner{defspecs}+$inner{offspecs})*6;}
	
	// Trade
	
	
	if(													// Wenn Trade auf Credits steht oder noch nciht gew?hlt ist und die Wechselzeit vorbei ist
		(
			$inner[istp_production] == "money"
			||
			$inner[istp_production] == "none"			// Nicht gew?hlt wird bei Trade wie Credits behandelt (?nderung r43,Programmierer: DragonTEC)
		)
		&& $inner[istp_changetime] == 0
	) 
	{
		$creditforschung = determine_creditforschung($inner, $sciences_intern);
		$bonusgebaeude = array("tradecenters","ressourcefacilities","powerplants","sciencelabs","s_tradecenters","s_ressourcefacilities","s_powerplants","s_sciencelabs","multiprod");
		$umrechnungsfaktor = ($sciences_intern['ind15'] * SYNFOS_TRADE_OWN + $creditforschung * SYNFOS_TRADE_OTHER);
		foreach ($bonusgebaeude as $value) 
		{
			$moneyadd += floor($inner{$value} * $umrechnungsfaktor);
		}
	}

	return array(round($moneyadd), $lageradd, $handelspunkteadd, $dividendenadd);

}


/********************************************************************************

Function dividenden

*********************************************************************************/

// Achtung, diese Funktion SCHREIB mit Db_write und benutzt das array $queries!
// Erstes Argument ist das Syndikat, fr das Dividenden ausgezahlt werden sollen, 2. Argument ist die gesamtsumme, die verteilt werden soll
// Dividendenauszahlung jetzt im Update alle 3 stunden
function dividenden($rid,$betrag,$product) {
	global $queries;
	//pvar($product);
	if ($product == "money") {
        $queries[] = "update syndikate set dividenden=dividenden+$betrag where synd_id=$rid";
	}
	else {
		$spalte = "dividenden_".$product;
        $queries[] = "update syndikate set $spalte=$spalte+$betrag where synd_id=$rid";
	}
}

/*********************************************************************************

Function get_synfos_count_extern

*********************************************************************************/
function get_synfos_count_extern(&$status, $whatsynfos, $rid)
{
	/*
	 * ind15 - trade
	 * ind16 - isesp
	 * glo12 - issdn
	 */
	if( $whatsynfos != 'ind15' && $whatsynfos != 'ind16' && $whatsynfos != 'glo12')
	{
		return 0;
	}
	
	$synmembercount = single("select count(*) from status where rid=" . $rid);
	
	if( $synmembercount == 0 )
	{
		return -1;
	}
	
	$synmaxmembers = USERS_USED_FOR_SYNFOS;
	
	$multifactor = 1;
	
	if( $synmembercount < $synmaxmembers )
	{
		$multifactor = $synmaxmembers / $synmembercount;
	}
	
	return single("SELECT 
						SUM(level) * " . $multifactor . " 						
					FROM
						(
							SELECT 
								level
							FROM 
								usersciences, status
							WHERE 
								usersciences.user_id = status.id
								AND
								status.rid = " . $rid . "
								AND
								usersciences.name = '" . $whatsynfos . "'
							ORDER BY
								level DESC
							LIMIT
								" . $synmaxmembers . "
						) AS tradelevels");
	
	
}


/*********************************************************************************

Function determine creditforschung (Trade)

*********************************************************************************/
function determine_creditforschung(&$inner, $sciences_intern) 
{
	/*
	 * ?nderung R46 DragonTEC: Testweise komplette Neuimplementierung dieser Funktion
	 */
 
	return get_synfos_count_extern($inner, "ind15",$inner['rid']);
	
	/*
	global $syndforschungen;
	global $synmembers;
	global $syndikate_data;
	global $game_syndikat;


	if ($inner[rid] > 0) # && paid(single("select id from users where konzernid = $id"))) { # Deaktiviert ab Runde 12
	{
		if ($game_syndikat) 
		{
			$syndikatsforschungsmodus = $game_syndikat['syndsciencestype'];
		} 
		else 
		{
			$syndikatsforschungsmodus = $syndikate_data[$inner['rid']]['syndsciencestype'];
		}

		if (is_array($syndforschungen)) 
		{
			$syn_open = $syndikate_data[$inner[rid]][open];
			for ($i = 0; $i < 3; $i++) 
			{
				for ($o = 1; $o <= ($syndikatsforschungsmodus ? 3 : $sciences_intern[ind15]) && $o <= $i+1; $o++) 
				{
					$creditforschung += $syndforschungen[$inner[rid]][creditforschung][$i];
				}
			}
		}
		else 
		{
			$creditforschung_temp = explode("|", single("select creditforschung from syndikate where synd_id=$inner[rid]"));
			$syn_open = $game_syndikat[open];
			for ($i = 0; $i < 3; $i++) 
			{
				for ($o = 1; $o <= ($syndikatsforschungsmodus ? 3 : $sciences_intern[ind15]) && $o <= $i+1; $o++) 
				{
					$creditforschung += $creditforschung_temp[$i];
				}
			}
		}

		if ($creditforschung) 
		{
			if (!is_array($synmembers)) 
			{
				$tempmembers = single("select count(*) from status where rid=$inner[rid]");
				$syn_open = $game_syndikat[open];
			}
			else 
			{
				$tempmembers = $synmembers[$inner[rid]][number];
				$syn_open = $syndikate_data[$inner[rid]][open];
			}
			/*	DAS HIER NICHT AUSQUOTEN, WIRD NUR BEI EINEM PAY-MODELL BEN?IGT!
			$tcount = 0;
			$synmembers = singles("select users.id from status, users where status.rid = $inner[rid] and status.id = users.konzernid");
			foreach ($synmembers as $vl) {
				if (paid($vl)): $tcount++; endif;
			}
			$tempmembers = $tcount;
			*/ /*
			//$cfmult = MAX_USERS_A_SYNDICATE / $tempmembers;
			)$cfmult = MAX_USERS_A_SYNDICATE / $tempmembers;
			/* wieder ausgebaut mit der einf?hrung des basic-servers
			if (!$syn_open && $tempmembers < 17) { $cfmult = 17 / $tempmembers; } // ?derung Runde 19, fr geschlossene Syns nur Hochrechnung auf 17 Leute
			elseif (!$syn_open) { $cfmult = 1; }
			*/ /*
			$creditforschung *= $cfmult;
			unset($tempmembers);
		}
	}
	else 
	{
		$creditforschung = 0;
	}
	
	return $creditforschung;
	*/
}


/********************************************************************************

Function pointit

*********************************************************************************/

function pointit($input) {
	$input = sprintf("%d", $input);
	if ($input < 0): $minusschranke = 1; $input *= -1; endif;
    $length = strlen($input);
    $i=0;
    $new="";
    while ($i <= $length) {
        $new= substr ($input, $length-$i, 1).$new;
        if ($i %3 == 0 && $i>0 && $i != $length) {
            $new =".".$new;
        } // if
        $i++;

    } // while
    // - string korrigieren:
    if ($minusschranke) {
        $new = "-".$new;
    }
    return $new;
} // function


/********************************************************************************

Function getresstats

*********************************************************************************/

function getresstats() {
    $values = array(
                    energy => array(name => "Energie",type=>"energy",value=> ENERGY_STD_VALUE),
                    metal => array (name => "Erz",type=>"metal",value => METAL_STD_VALUE),
                    sciencepoints => array (name => "Forschungspunkte",type=>"sciencepoints", value => SCIENCEPOINTS_STD_VALUE),
                    money => array (name=> "Credits",type=>"money", value => 1)
    );
	$actual = assoc("select energy,money,metal,sciencepoints from ressources order by time desc limit 1");
	if ($actual)	{
		foreach ($actual as $key => $value) {
			$values{$key}{value} = $value;
		}
	}
    return $values;
}

/********************************************************************************

Function getallvalues

*********************************************************************************/


function getallvalues() {
    // Argumente checken
    if (func_num_args() > 0) {$id_intern = func_get_arg (0);}
    else {$id_intern = $id;}
    $values = assoc("select * from status where id = $id_intern");
    
    
    return $values;
}



/********************************************************************************

Function changetype

*********************************************************************************/


function changetype() {

    global $status;
    $race = $status{race};

    if (func_num_args() > 0) {$arg1 = func_get_arg (0);}
    if (func_num_args() > 1) {$arg2 = func_get_arg (1);}
    if (func_num_args() > 2) {$race = func_get_arg (2);}
    $stuff = array();

    #umwandlung in string
    if ($arg2) {

        #ressourcen
        if ($arg1 == "res") {
            if ($arg2 == 1) {$stuff{product} = "energy";}
            elseif ($arg2 == 2) {$stuff{product} = "metal";}
            elseif ($arg2 == 3) {$stuff{product} = "sciencepoints";}
        }

        elseif ($arg1 == "spy") {
			$offarray = array(1,4,7,9,12);
			$defarray = array(2,5,8,10,13);
			$intelarray = array(3,6,11,14);
            if (in_array($arg2,$offarray)) {$stuff{product} = "offspies";}
            elseif (in_array($arg2,$defarray)) {$stuff{product} = "defspies";}
            elseif (in_array($arg2,$intelarray)) {$stuff{product} = "intelspies";}
        }

        elseif ($arg1 == "mil") {
            if ($arg2 == 1 || $arg2 == 2 || $arg2 == 3 || $arg2 == 17 ||$arg2 == 22) {$stuff{product} = "offspecs";}
            elseif ($arg2 == 4 || $arg2 == 5 || $arg2 == 6 || $arg2 == 18 || $arg2 == 23) {$stuff{product} = "defspecs";}
            elseif ($arg2 == 10 || $arg2 == 8 || $arg2 == 9 || $arg2 == 19 || $arg2 == 24 || $arg2 == 40) {$stuff{product} = "elites";}
            elseif ($arg2 == 7 || $arg2 == 11 || $arg2 == 12 || $arg2 == 20 || $arg2 == 25 || $arg2 == 41) {$stuff{product} = "elites2";}
            elseif ($arg2 == 14 || $arg2 == 15 || $arg2 == 16 || $arg2 == 21 || $arg2 == 26 || $arg2 == 42) {$stuff{product} = "techs";}
        }
    }

    # umwandlung in type und prod_id
    else {
        if ($arg1 == "energy") {$stuff{type} = "res";$stuff{prod_id}= 1;}
        elseif ($arg1 == "metal") {$stuff{type}= "res";$stuff{prod_id}= 2;}
        elseif ($arg1 == "sciencepoints") {$stuff{type}= "res";$stuff{prod_id}= 3;}
        elseif ($arg1 == "offspies") {$stuff{type} = "spy";$stuff{prod_id}=1;}
       /* elseif ($arg1 == "offspies" && $race == "uic") {$stuff{type} = "spy";$stuff{prod_id}=4;}
        elseif ($arg1 == "offspies" && $race == "sl") {$stuff{type} = "spy";$stuff{prod_id}=7;}
        elseif ($arg1 == "offspies" && $race == "neb") {$stuff{type} = "spy";$stuff{prod_id}=9;}
        elseif ($arg1 == "offspies" && $race == "nof") {$stuff{type} = "spy";$stuff{prod_id}=12;}*/
        elseif ($arg1 == "defspies") {$stuff{type} = "spy";$stuff{prod_id}=2;}
       /* elseif ($arg1 == "defspies" && $race == "uic") {$stuff{type} = "spy";$stuff{prod_id}=5;}
        elseif ($arg1 == "defspies" && $race == "sl") {$stuff{type} = "spy";$stuff{prod_id}=8;}
        elseif ($arg1 == "defspies" && $race == "neb") {$stuff{type} = "spy";$stuff{prod_id}=10;}
        elseif ($arg1 == "defspies" && $race == "nof") {$stuff{type} = "spy";$stuff{prod_id}=13;}*/
        elseif ($arg1 == "intelspies") {$stuff{type} = "spy";$stuff{prod_id}=3;}
      /*  elseif ($arg1 == "intelspies" && $race == "uic") {$stuff{type} = "spy";$stuff{prod_id}=6;}
        elseif ($arg1 == "intelspies" && $race == "neb") {$stuff{type} = "spy";$stuff{prod_id}=11;}
        elseif ($arg1 == "intelspies" && $race == "nof") {$stuff{type} = "spy";$stuff{prod_id}=14;}*/
        elseif ($arg1 == "offspecs") {$stuff{type} = "mil";$stuff{prod_id}=2;}
     /*   elseif ($arg1 == "offspecs" && $race == "sl") {$stuff{type} = "mil";$stuff{prod_id}=1;}
        elseif ($arg1 == "offspecs" && $race == "uic") {$stuff{type} = "mil";$stuff{prod_id}=2;}
        elseif ($arg1 == "offspecs" && $race == "neb") {$stuff{type} = "mil";$stuff{prod_id}=17;}
        elseif ($arg1 == "offspecs" && $race == "nof") {$stuff{type} = "mil";$stuff{prod_id}=22;}*/
        elseif ($arg1 == "defspecs") {$stuff{type} = "mil";$stuff{prod_id}=4;}
      /*  elseif ($arg1 == "defspecs" && $race == "uic") {$stuff{type} = "mil";$stuff{prod_id}=5;}
        elseif ($arg1 == "defspecs" && $race == "pbf") {$stuff{type} = "mil";$stuff{prod_id}=6;}
        elseif ($arg1 == "defspecs" && $race == "neb") {$stuff{type} = "mil";$stuff{prod_id}=18;}
        elseif ($arg1 == "defspecs" && $race == "nof") {$stuff{type} = "mil";$stuff{prod_id}=23;}*/
        /*elseif ($arg1 == "elites" && $race == "uic") {$stuff{type} = "mil";$stuff{prod_id}=7;}
        elseif ($arg1 == "elites" && $race == "pbf") {$stuff{type} = "mil";$stuff{prod_id}=9;}
        elseif ($arg1 == "elites" && $race == "sl") {$stuff{type} = "mil";$stuff{prod_id}=8;}
        elseif ($arg1 == "elites" && $race == "neb") {$stuff{type} = "mil";$stuff{prod_id}=19;}
        elseif ($arg1 == "elites" && $race == "nof") {$stuff{type} = "mil";$stuff{prod_id}=24;}
        elseif ($arg1 == "elites2" && $race == "uic") {$stuff{type} = "mil";$stuff{prod_id}=10;}
        elseif ($arg1 == "elites2" && $race == "pbf") {$stuff{type} = "mil";$stuff{prod_id}=12;}
        elseif ($arg1 == "elites2" && $race == "sl") {$stuff{type} = "mil";$stuff{prod_id}=11;}
        elseif ($arg1 == "elites2" && $race == "neb") {$stuff{type} = "mil";$stuff{prod_id}=20;}
        elseif ($arg1 == "elites2" && $race == "nof") {$stuff{type} = "mil";$stuff{prod_id}=25;}
        elseif ($arg1 == "techs" && $race == "uic") {$stuff{type} = "mil";$stuff{prod_id}=15;}
        elseif ($arg1 == "techs" && $race == "pbf") {$stuff{type} = "mil";$stuff{prod_id}=16;}
        elseif ($arg1 == "techs" && $race == "sl") {$stuff{type} = "mil";$stuff{prod_id}=14;}
        elseif ($arg1 == "techs" && $race == "neb") {$stuff{type} = "mil";$stuff{prod_id}=21;}
        elseif ($arg1 == "techs" && $race == "nof") {$stuff{type} = "mil";$stuff{prod_id}=26;}*/
		elseif ($arg1 == "elites") {$stuff{type} = "mil";$stuff{prod_id}=40;}
		elseif ($arg1 == "elites2") {$stuff{type} = "mil";$stuff{prod_id}=41;}
		elseif ($arg1 == "techs") {$stuff{type} = "mil";$stuff{prod_id}=42;}
    }

    
    return $stuff;
}

/********************************************************************************

Function getmarket

*********************************************************************************/

function getmarket($id) {

    $market = array();
    $result = select("select sum(number),type,prod_id from market where owner_id =".$id." group by type,prod_id");
    while ($return = mysql_fetch_row($result)) {
        $prod = changetype($return[1],$return[2]);
        $market{$prod{product}} = $return[0];
    }
    return $market;
}

/********************************************************************************

Function getaway

*********************************************************************************/

function getaway($id) {
    $away = array();

    $result = select("select sum(number), unit_id from military_away where user_id =".$id." group by unit_id");
    while ($return = mysql_fetch_row($result)) {
        if  ($return[1] == 1 || $return[1] == 2 || $return[1] == 3 || $return[1] == 17 || $return[1] == 22) {$away{offspecs} += $return[0];}
        elseif ($return[1] == 4 || $return[1] == 5 || $return[1] == 6 || $return[1] == 18 || $return[1] == 23 ) {$away{defspecs} += $return[0];}
        elseif  ($return[1] == 10 || $return[1] == 8 || $return[1] == 9 || $return[1] == 19 || $return[1] == 24 || $return[1] == 40) {$away{elites} += $return[0];}
        elseif ($return[1] == 7 || $return[1] == 11 || $return[1] == 12 || $return[1] == 20 || $return[1] == 25 || $return[1] == 41) {$away{elites2} += $return[0];}
	    elseif ($return[1] == 14 || $return[1] == 15 || $return[1] == 16 || $return[1] == 21 || $return[1] == 26 || $return[1] == 42) {$away{techs} += $return[0];}
    }
	
    return $away;
}


/********************************************************************************

Function nw

*********************************************************************************/



function nw($id) {

    // Status und Sciences initialisieren

	// Komsische Regelung frs Update: Marketarray mussd 1 sein, sonst wirds geholt away und sciences mssen arrays sein, sonst werden sie gheholt
    global $status;    global $sciences;    global $away;    global $market;
    global $defstatus; global $defsciences; global $defaway; global $defmarket;
	global $status_d; global $sciences_d;
	global $science_settings;
	global $units_killed_local;
	static $aktienwerte;
	static $aktienwert_eigenes_syndikat;
	
    $nw=0;
	if (!$science_settings) {
		$science_settings = assocs("select *,concat(name,typenumber) as iname from sciences","iname"); //AAAAH
	}
	
	// Werte von Aktien holen
	// Schlechteste Einheit kostet etwa 1400 pro nw -> fr 15k Cr / Handelpunkte und aktienwert gibt es 1 NW.
	if (!$aktienwerte) {
		$aktienwerte = assocs("select a.user_id,sum(a.number) as vl from aktien a, syndikate s where a.synd_id = s.synd_id group by a.user_id","user_id");
		// R4bbiT - 06.09.10 - alt: sum(a.number*s.aktienkurs) // neu: sum(a.number)
		//?nderung R 43: gibt schon L?nger keinen privaten Verkauf mehr
		//$aktienwerte_private = assocs("select a.user_id,sum(a.number*s.aktienkurs) as vl from aktien_privat a, syndikate s where a.synd_id = s.synd_id group by a.user_id","user_id");
		//pvar($aktienwerte_private);
	}
	
	
	//pvar($away);
	//pvar($sciences);
	//pvar($market);
    if ($status{id} == $id) {
        $status_intern = $status;
		if (is_array($sciences)) {
			$sciences_intern = $sciences;
		}
		else {
			$sciences_intern = getsciences($id); // holt daten
			#echo "GETSCIENCES";
		}
		if (is_array($away)) {
			$away_intern = $away;
		}
		else {
			$away_intern = getaway($id); // holt daten
			#echo "GETAWAY<br>";
		}
		if ($market) {
			$market_intern = $market;
		}
		else {
			$market_intern = getmarket($id); // holt daten
			#echo "GETMARKET";
		}



    }
    else if ($defstatus{id} == $id or $status_d{id} == $id) {
        ($defstatus || $status_d) ? $status_intern = ($defstatus ? $defstatus : $status_d) : $status_intern = getallvalues($id);
        ($defsciences || $sciences_d) ? $sciences_intern = ($defsciences ? $defsciences : $sciences_d) : $sciences_intern = getsciences($id);
        $defaway ? $away_intern = $defaway : $away_intern = getaway($id);
        $defmarket ? $market_intern = $defmarket : $market_intern = getmarket($id);
    }
    else {
        $status_intern = getallvalues($id);
        $sciences_intern = getsciences($id);
        $away_intern = getaway($id);
        $market_intern = getmarket($id);
    }
   
    if($units_killed_local) {
    	$units_killed_intern = $units_killed_local;
    	foreach($units_killed_intern as $key=>$value) {
    		$status_intern{$key} -= $value;
    	}
    }

    // Einheiten away zu status dazuz?len
    foreach($away_intern as $key => $value) {
        $status_intern{$key} += $value;
    }
    // Einheiten und Ressourcen auf market zu status z?len
	if ($market_intern != 1)	{ # $market wird von UPDATE.PHP auf 1 gesetzt wenn nix da ist, damit die NW-Routine nicht nochmal Queries rausjagt
	    foreach($market_intern as $key => $value) {
	        $status_intern{$key} += $value;
	    }
	}

    // Aber hier eigentliche berechnungen, alles davor war nur werte fetchen
    ## networth sciences
    # lvl 4 2500, lvl 3 1000, lvl 2 400, lvl 1 150

	// NW fr Aktien und Handelspunkte:
	//print_r($aktienwerte);
	//echo "\nSPIELER:".$status[syndicate]." - AKTIENWERT:".$aktienwerte[$status[id]][vl]."\n";
	/*
	pvar("\nSPIELER:".$status[syndicate]." - AKTIENWERT:".$aktienwerte[$status[id]][vl]."\n");
	pvar($status_intern[podpoints],pp);
	pvar($nw,vorher);
	*/

	/*if ($status_intern['rid']) 
	  $abzug_eigenes_syndikat = single("select (a.number * s.aktienkurs) from aktien a, syndikate s where a.synd_id = s.synd_id and a.synd_id = ".$status_intern['rid']." and a.user_id = ".$status_intern['id']); //update ende R42!
	else $abzug_eigenes_syndikat = 0;
	*/
	//status_intern[podpoints_nw] aus den Summen gestrichen, Lagerguthaben soll keinen NW geben.
	$nw += floor($aktienwerte[$id][vl] * NW_AKTIEN); // 10k Handelspunkte / Ressourcen = 1nw
	//?nderun R 43: gibt schon L?nger keinen privaten Verkauf mehr
	//$nw += floor(($aktienwerte_private[$id][vl]) / 5000); // 10k Handelspunkte / Ressourcen = 1nw
	

	// Forschungsspezifikationen holen
    foreach ($sciences_intern as $key => $value) {
        if ($science_settings{$key}{level} == 7) { $nw+=$value*NW_FOS_LVL7;}
        elseif ($science_settings{$key}{level} == 6) { $nw+=$value*NW_FOS_LVL6;}
        elseif ($science_settings{$key}{level} == 5) { $nw+=$value*NW_FOS_LVL5;}
        elseif ($science_settings{$key}{level} == 4) { $nw+=$value*NW_FOS_LVL4;}
        elseif ($science_settings{$key}{level} == 3) { $nw+=$value*NW_FOS_LVL3;}
        elseif ($science_settings{$key}{level} == 2) { $nw+=$value*NW_FOS_LVL2;}
        elseif ($science_settings{$key}{level} == 1) { $nw+=$value*NW_FOS_LVL1;}
    }

    ## networth land (5 pro)
    $nw += $status_intern{land}*NW_LAND;

    ## networth gebäde (10pro)

	$building_nw_values = assocs("select name_intern, nw from buildings", "name_intern");
    $gebaeudelow = array (banks,ressourcefacilities,tradecenters,sciencelabs,powerplants,spylabs,depots,factories,buildinggrounds,mines,s_tradecenters,s_ressourcefacilities,s_powerplants,s_sciencelabs,schools,behemothfactories);
    foreach ($gebaeudelow as $key) {$nw+=$status_intern{$key}* $building_nw_values[$key]["nw"];} //VORHER KONSTANTE
    $gebaeudehigh = array (offtowers,deftowers,armories,radar,seccenters,ecocenters,multiprod,workshops);
    foreach ($gebaeudehigh as $key) {$nw+=$status_intern{$key}* $building_nw_values[$key]["nw"];}


    ## Einheiten
    ## einzelne Networths:
    # Alle Defspecs: 4, Offspecs: 5, Bf OffSpecs: 6, BfElite1: 11,2 BfElite2:8, SlElite1: 8 SlElite2:7,2 UicElite1:9,3 UicElite2: 10
	// Fr runde 5 networths der einheiten auf 0,7 gesenkt
	// specialists zus?zlich um 0,3 gesenkt 
	if ($status_intern{race} == "pbf") $uids = array(2,4,9,12,16,1,2,3);
	if ($status_intern{race} == "sl") $uids = array(2,4,8,11,14,1,2,3);
	if ($status_intern{race} == "uic") $uids = array(2,4,10,7,15,1,2,3);
	if ($status_intern{race} == "neb") $uids = array(2,4,19,20,21,1,2,3);
	if ($status_intern{race} == "nof") $uids = array(2,4,24,25,26,1,2,3);
	
	$utypes = array('offspecs', 'defspecs', 'elites', 'elites2', 'techs');
	foreach($utypes as $t){
		 $nw +=$status_intern{$t} * single('select nw from military_unit_settings where (race = \'all\' or race = \''.$status_intern['race'].'\') and type = \''.$t.'\'');
	}
	
	$nw +=$status_intern{offspies} * single("select nw from spy_settings where unit_id = ".$uids[5]);
	$nw +=$status_intern{defspies} * single("select nw from spy_settings where unit_id = ".$uids[6]);
	/*if($status_intern["race"] != "sl")*/
	$nw +=$status_intern{intelspies} * single("select nw from spy_settings where unit_id = ".$uids[7]);
	
    # Ressourcen
    # 2000 Cred = 1 Nw, 1769 Energie = 1 NW, 100,5 Sciencepoints = 1NW, 314,5 Metal = 1 NW, 2000 Handelspunkte = 1nw
	// fr runde 5 handelspunkte drastisch gesenkt vorher / 2000, jetzt durch 40000
    //$nw += (round($status_intern{money}/(2000*4)+$status_intern{podpoints}/40000)+ round($status_intern{metal}/(314.5*2))+round($status_intern{energy}/(1769*2))+round($status_intern{sciencepoints}/(100.5*2)));
	/*
	if ($status[syndicate] == "test") {
		pvar($status_intern,status);
		pvar($sciences_intern,sciences);
		pvar($market_intern,market);
		pvar($away_intern,away);
		pvar($nw,nw);
	}
	*/

    return round($nw);
}

/********************************************************************************

Function db_write

*********************************************************************************/

function db_write($queries, $ignore_update = 0) {
	global $page,$time,$start,$adminlogin,$lck;
	global $status;
	$return = 1;
	$microlock_ok = 1;
	global $globals;
	if (!$globals['updating'])	$globals = assoc("select * from globals order by round desc limit 1");	# Updating Wert muss ganz aktuell sein damit es zu m?lichst keiner ?erschneidung mit Update kommt, deshalb nochmal rausholen
	
	// Checken ob ein microlock gesetzt ist
	if ($page != "UPDATEPROZESS" && !$ignore_update && (count($_POST) > 0 || $lck==1) ) {
		$actual_microlock = single("select microlocked from sessionids_actual where user_id=$status[id]");
		if ((string)trim($actual_microlock) != (string)trim($start) && !$adminlogin) {$microlock_ok=0;}
		/* N?tig ?
		else {
		}
		*/
	}
    if ((((!$globals{updating} or $ignore_update) && ($microlock_ok==1 || !$status[id])) || $page == "UPDATEPROZESS") && ($globals[roundstatus] <> 2 || $ignore_update)) {
        if (! is_array ($queries) && $queries) {
            echo ("<font color=\"white\">Hallo, ich bin die kleine db_write Funktion. Du hast mich leider nicht  mit einem Array gefttert, daher tu ich auch nix fr dich...</font>");
			$return = 0;
        }
        elseif ($queries) {
            if (func_num_args() > 2) {
            	$show = func_get_arg (2);
            }

            if (!$show || $show == 1) {
				$ignorealreadyon = ignore_user_abort();
				if (!$ignorealreadyon): ignore_user_abort(TRUE); endif;
                foreach ($queries as $temp) {
                    select($temp);
                    if ($show == 1) {
                        echo $temp."<br>";
                        echo mysql_info()."<br>";
                    }
                }
                global $successmeldung;
                if ($status[id]) {
	                if (strlen($fehler) > 0 ) {
	                	select("update status set lastsuccess = '$fehler'");
	                }
	                else if (strlen($successmeldung) > 0 && $status[id]) {
	                	select("update status set lastsuccess = '$successmeldung' where id=$status[id]");
	                }
	                elseif ($status[id])  {
	                	select("update status set lastsuccess = '' where id=$status[id]");
	                }
                }
               if (!$ignorealreadyon): ignore_user_abort(FALSE); endif;
            }
            else if ($show == 2) {
                foreach ($queries as $temp) {
                    echo $temp."<br>";
                }
            }
            $what = ignore_user_abort();
        }
    }
 	else {
		if ($queries)	{
			global $ausgabe; global $goon;
			global $successmeldung; global $allsuccess;
			global $fehler; global $allfehler;
			$ausgabe = "";
			$goon = 0;	# Falls in einem Skript nach db_write() noch Ausgabe vorgenommen wird muss mit $goon die Ausgabe unterbunden werden
			$successmeldung = ""; $allsuccess = array();
			$fehler = ""; $allfehler = array();

			global $status; global $id;
			$tlocked = $status[locked];
			$tpaid = $status[paid];
			$status = getallvalues($id);
			$status[locked] = $tlocked;
			$status[paid] = $tpaid;
            $status{nw} = nw($status{id});
            
            //pvar($time);
            //pvar($status[locked],sl);
			if ($globals[updating]) {
	        	f("Es läuft gerade das stündliche Update, während dem stündlichen Update können keine Aktionen durchgefhrt werden. Probieren Sie es bitte in etwa 60 Sekunden noch einmal.<br><br><center><a href=javascript:history.back() class=linkAufTableInner>Zurück</a></center>");
				$return = 0;
			}
			elseif ($globals[roundstatus] ==2){
				f("<br><center><b>Die Runde ist zu Ende!  Diese Aktion kann nicht mehr ausgeführt werden!</b></center>");
			        $return = 0;
				} 
				elseif ($time - $status[locked] <= 3 || $microlock_ok != 1) {
				f("Zu schnelle Wiederholung der Anfrage, Aktion konnte nicht durchgeführt werden. Bitte warten Sie nach jeder Aktion, bis die Seite neu geladen wurde! Vergewissern sie sich bitte, dass ihre Anfrage nicht bereits bearbeitet wurde. Sie können in spätestens drei Sekunden wieder eine Aktion ausführen.");
				
				if (strlen($status[lastsuccess]) > 0) {
					f("<br><b>Status der letzten Aktion:</b><br><br>".$status[lastsuccess]);
				}
				f("Sollte diese Meldung trotzdem Warten weiter erscheinen, hilft ausloggen und wieder einloggen.");
				
				$return = 0;
				//sleep(1);
			}
		}
	}
	
	// Locks l?sen
	/* Erst in footer, damit seite auch wirklich geladen wurde
	if ($status[id]) {
			select("update sessionids_actual set microlock='0',locked=0 where user_id=$status[id]");	
	}
	*/
	
	return $return;
}


#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#==============================================================================
#=====================GET_AN_EMTPY_SYNDICATE===================================
#==============================================================================
#<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

# Neu erstellt von Christian-, eingefügt/ getestet von R4bbit - 14.08.10
# bei erneuter Änderung in create/ login/ politik/ options.php auf den Aufruf achten!
function get_an_empty_syndicate($extra = null){

	if ($extra == 'inaktiv') {
		return 0;
	}
	
	$minPlayers = 100;
	$minPlayersSyn = 0;
	
	$avgPlayers = 0;
	$numSyn = 0;
	
	$playersSynArray = array();
	
	$syndata = assocs('select synd_id from syndikate');
	
	foreach($syndata as $tag => $value){
	
		$numSyn++;
		
		//SynNr
		$synID = $value['synd_id'];
		//Anzahl der Spieler des aktuellen Syndicate ermitteln
		$playersCount = single("select count(id) from status where alive != 0 and rid=".$synID);
		
		// Nachzuegler müssen auch hier beachtet werden!
		$nachzuegler = single("SELECT sum(nachzuegler) FROM groups_new WHERE current_rid = '".$synID."'");
		if ($nachzuegler) {
			$playersCount += $nachzuegler;
		}
		
		$avgPlayers += $playersCount;
		
		if($playersCount < $minPlayers){ //sinnloser Part
			$minPlayers = $playersCount;
			$minPlayersSyn = $synID;
		}
		
		$playersSynArray[] = array("id"=>$synID, "players"=>$playersCount);		
	
	}
	
	$avgPlayers = ceil($avgPlayers / $numSyn);
	
	$minSyns = array();
	$iMinSyns = 0;
	$minXSyns = array();
	$iMinXSyns = 0;
	$avgSyns = array();
	$iAvgSyns = 0;
	
	foreach($playersSynArray as $currentSyn){
		if($currentSyn['players'] < $avgPlayers - 1)
			$minXSyns[$iMinXSyns++] = $currentSyn['id'];
		if($currentSyn['players'] < $avgPlayers)
			$minSyns[$iMinSyns++] = $currentSyn['id'];
		if($currentSyn['players'] <= ($avgPlayers + 1))
			$avgSyns[$iAvgSyns++] = $currentSyn['id'];
	}
	
	if($minXSyns){
		$iRandom = mt_rand(0, $iMinXSyns - 1);
		return $minXSyns[$iRandom];
	}
	
	if($minSyns){
		$iRandom = mt_rand(0, $iMinSyns - 1);
		return $minSyns[$iRandom];
	}
	
	if($avgSyns){
		$iRandom = mt_rand(0, $iAvgSyns - 1);
		return $avgSyns[$iRandom];
	}
	
}

#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#==============================================================================
#=====================VERIFICATION=============================================
#==============================================================================
#<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

function verification() {

	$id_intern = func_get_arg(0);	# ID des Spielers
	$type = func_get_arg(1);		# Art des Aufrufes:
									# 	createaccount: Account erstellt
									# 	mailchange: Mailadresse ge?dert
									# 	sendagain: Code nochmal senden
	global $password, $rulername, $syndicate, $new_by_koins;

	list($username, $email, $vorname, $nachname, $vcode) = row("select username, email, vorname, nachname, vcode from users where id=".$id_intern);
	if ($type != "sendagain") {
		$verificationcode = createkey("", 20); #Key mit L?ge 20
	}
	else { $verificationcode = $vcode; }

	if ($type != "mailchange" and $type != "sendagain") {
		$message = "\n\nHerzlich Willkommen bei Syndicates,\n";
	}
	else {
		$message = "\n\nHallo $username,\n";
	}
	if ($type == "mailchange" or $type == "sendagain") {
		$message .= "\num die Gültigkeit Ihrer aktuellen Mailadresse zu bestätigen, geben Sie bitte den untenstehenden Verifizierungsschlssel bei ihrem nächsten Login an.\nIhr Verifizierungsschlüssel lautet $verificationcode";
		$betreff = "Verifizierung Ihrer Mailadresse";
	}
	elseif ($type == "createaccount") {
		$message .= "\nIhr Account wurde erfolgreich erstellt. Wir wünschen Ihnen viel Erfolg mit Ihrem Konzern.\n\nBitte speichern Sie den untenstehenden Verifizierungsschlüssel ab, um die Gültigkeit Ihrer E-Mailadresse bestätigen zu können.\nDen Schlssel benötigen Sie bei Ihrem ersten Login drei Tage nach Accounterstellung, um weiterspielen zu können.\nIhr Verifizierungsschlssel lautet $verificationcode.";
		$betreff = "Bestätigung und Verifizierung Ihrer Anmeldung";
	}
	elseif ($type == "createkonzern") {
		if (!$round) {
			$round = single("select round from globals order by round desc limit 1");
		}
		$thisround = $round-2;
		$message .= "\nHiermit bestätigen wir Ihre Anmeldung eines neuen Konzerns für die Runde $thisround.";
		$betreff = "Anmeldungsbestätigung";
	}

	if ($type == "createaccount") {
		$message .= "\n\nIhre Accountdaten im Überblick:\n\nBenutzername: $username\nPasswort: $password";
	}
	if ($type == "createkonzern" or $type == "createaccount") {
		$message .= "\n\nIhre Konzerndaten im Überblick:\n\nGeschäftsführer: $rulername\nKonzernname: $syndicate";
	}

	
	if (!isKsyndicates()) {
		$gameurl="http://www.BETREIBER.de";
		
	}
	

	$message .= "\n\n\nViel Spaß beim Spielen.\nDas Syndicates Team\n\n$gameurl";

	if ($type != "sendagain" and $type != "createkonzern")	{
		select("update users set vcode='$verificationcode' where id='$id_intern'");
	}


	if ($vorname or $nachname): $to = "$vorname $nachname";
	else: $to = $username; endif;
 	sendthemail($betreff,$message,$email,$to);
}


/********************************************************************************

Function getsyndvalues

*********************************************************************************/

function getsyndvalues() {

    global $status;

    // Argumente checken
    if (func_num_args() > 0) {
    	$rid_intern = func_get_arg (0);
    }
    else {
    	$rid_intern = $status{rid};
    }

    $values = assoc("select * from syndikate where synd_id=".$rid_intern);

    return $values;

}

//
////
////// F O R S C H A B L E
////
//


// $s muss den Forschungstable enthalten: WICHTIG: Spalte "name" muss als Schlssel "group" existieren und "name"."typenumber" als "name";
function forschable($name,$s,$sciences, $spoints)	{
	static $baumstrukturpass;
	static $number;
	static $erforscht;
	global $nebmalus, $status;
	
 			if (func_num_args() > 4) { if (func_get_arg (4)): $baumstrukturpass = array(); $erforscht = array(); endif;}	# Bei 5. Parameter den Baumstrukturarray l?chen
	// Ist das Forschung schon maximal ausgebaut ?
	if ($s[$name][maxlevel] > $sciences[$name])	{
		// Falls die Forschung keine Level 1 Forschung ist und einer Gruppe angeh?t, fr welche das Forschungslevel noch nicht geprft wurde (bei mehrfachem Aufruf von forschabel
		// - wurde bereits eine Forschung des n?hstniedrigen Levels erforscht ?
		if ($s[$name][level] > 1 && !$baumstrukturpass[$s[$name][group]][$s[$name][level]])	{
			if ($sciences)	{
				foreach ($sciences as $ky => $vl)	{
					if ($s[$ky][level] + 1 >= $s[$name][level] && $s[$name][group] == $s[$ky][group] )	{
						$baumstrukturpass[$s[$name][group]][$s[$name][level]] = 1;
						break;
					}
				}
			}
		}
		elseif ($s[$name][level] == 1 && !$baumstrukturpass[$s[$name][group]][$s[$name][level]]) {
		$baumstrukturpass[$s[$name][group]][$s[$name][level]] = 1;
		}
		
		// Falls dies der erste Aufruf der Funktion ist wird aus den verfgbaren Forschungen ermittelt zu welchem Level es wieviele Forschungen gibt, geordnet nach Gruppe
		if (!$number)	{
			foreach ($s as $vl)	{
				$number[$vl[group]][$vl[level]]++;
			}
		}
		// Falls dies der erste Aufruf der Funktion ist bzw. die Funktion fr einen anderen Benutzer aufgerufen wurde und dies durch ?ergabe des 5. Parameters gekennzeichnet wurde,
		// dann wird hier ermittelt wieviele Forschungen der Benutzer von den jeweiligen Level geordnet nach Gruppen bereits erforscht hat. Das ist wichtig, da man aus einer Gruppe immer eine Forschung nicht erforschen kann.
		if (!$erforscht)	{
			if ($sciences)	{
				foreach ($sciences as $ky => $vl)	{
					$erforscht[$s[$ky][group]][$s[$ky][level]]++;
				}
			}
		}
		for($i=1;$i<7;$i++){ //all fix
			if($erforscht[ind][$i] || $erforscht[glo][$i] || $erforscht[mil][$i]){
				$erforscht[all][$i]=1;
				$number[all][$i]=1;
				$baumstrukturpass[all][$i+1] = 1;
			}
		}
		
		// Hier wird berprft ob noch mehr als 2 Forschungen aus dem  Zweig frei sind, bzw. ob eine Forschung verbessert werden soll, falls es nur noch eine nicht erforschte gibt. Greift nur bei Level indem es mehr als 1 Forschung gibt!
		if ($number[$s[$name][group]][$s[$name][level]] - $erforscht[$s[$name][group]][$s[$name][level]] > 1 or ($number[$s[$name][group]][$s[$name][level]] - $erforscht[$s[$name][group]][$s[$name][level]] == 1 && ($number[$s[$name][group]][$s[$name][level]] == 1 or $sciences[$name])))	{
			// Falls es sich um eine LVL-7-Forschung handelt muss sichergestellt werden, dass noch keine erforscht wurde - diese Abfrage l?st keine Weiterentwicklungen von Lvl-5-Forschungen zu!
			if ($s[$name][level] != 7 or (!$erforscht[mil][7] and !$erforscht[ind][7] and !$erforscht[glo][7] and !$erforscht[all][7]))	{
				$preisfaktor = 1;
				if ($sciences[$name] == 1): $preisfaktor = 3;
				elseif ($sciences[$name] == 2): $preisfaktor = 8; endif;
				if ($status['race'] == "uic" and $s[$name]['group'] == "ind") $bonus = UIC_INDUSTRIAL_COSTBONUS / 100; else $bonus = 0;
				$preis = (1 - $sciences[glo5] * GLO5BONUS + $nebmalus - $bonus) * $s[$name][sciencecosts] * $preisfaktor;
				//pvar($s[$name]);
				//pvar($preis);
				// Hier wird abgefragt ob die Forschung bzgl. der Baumstruktur in Ordnung geht (siehe oben)
				if ($baumstrukturpass[$s[$name][group]][$s[$name][level]])	{
						if ($preis <= $spoints) { return array(1,$preis); }
						else { return array(0, $preis, "sciencepoints"); }
				}
				else {
					// Hier muss Bonus nochmal berechnet werden!
					if ($status['race'] == "uic" and $s[$name]['group'] == "ind") $bonus = UIC_INDUSTRIAL_COSTBONUS / 100; else $bonus = 0;
					$preis = (1 - $sciences[glo5] * GLO5BONUS+ $nebmalus- $bonus) * $s[$name][sciencecosts];
					return array(0, $preis, "baumstruktur"); }
			}
			else { $error = "alreadybestlevel"; }
		}
		else { $error = "levelfull"; }	
	}
	else { $error = "maxlevelreached"; }
	return array(0,$error);
}

/*******************
umwandeln_bbcode
*******************/

function umwandeln_bbcode($text) {
	global $tpl;

	$tpl->config_load('bbcodes.conf', 'BBCODES');
	
	preg_match_all("/\[nobreak\](.*?)\[\/nobreak\]/is", $text , $save, PREG_SET_ORDER);
	foreach ($save as $vl) {
		$text = preg_replace("/\[nobreak\](.*?)\[\/nobreak\]/is", str_replace("\n", " ", $vl[1]), $text, 1);
	}
	
	$codes = array();
	$codes[] = array(	"tag" => "quote",
					 	"start" => $tpl->get_config_vars('QUOTE_START'),
						"ende" => $tpl->get_config_vars('QUOTE_END'));
	$codes[] = array(	"tag" => "b",
					 	"start" => $tpl->get_config_vars('B_START'),
						"ende" => $tpl->get_config_vars('B_END'));
	$codes[] = array(	"tag" => "i",
					 	"start" => $tpl->get_config_vars('I_START'),
						"ende" => $tpl->get_config_vars('I_END'));
	$codes[] = array(	"tag" => "u",
					 	"start" => $tpl->get_config_vars('U_START'),
						"ende" => $tpl->get_config_vars('U_END'));
	$codes[] = array(	"tag" => "center",
					 	"start" => $tpl->get_config_vars('CENTER_START'),
						"ende" => $tpl->get_config_vars('CENTER_END'));
	$codes[] = array(	"tag" => "ul",
					 	"start" => $tpl->get_config_vars('UL_START'),
						"ende" => $tpl->get_config_vars('UL_END'));
	$codes[] = array(	"tag" => "ol",
					 	"start" => $tpl->get_config_vars('OL_START'),
						"ende" => $tpl->get_config_vars('OL_END'));
	$codes[] = array(	"tag" => "li",
					 	"start" => $tpl->get_config_vars('LI_START'),
						"ende" => $tpl->get_config_vars('LI_END'));
	$codes[] = array(	"tag" => "ol center",
					 	"start" => $tpl->get_config_vars('OL_CENTER_START'),
						"ende" => $tpl->get_config_vars('OL_CENTER_END'));
	$codes[] = array(	"tag" => "ul center",
					 	"start" => $tpl->get_config_vars('$UL_CENTER_START'),
						"ende" => $tpl->get_config_vars('UL_CENTER_END'));
	
	foreach($codes as $code){
		while(preg_match('/\['.$code["tag"].'\](.*)\[\/'.$code["tag"].'\]/Uis', $text)) {
			$text = preg_replace('/\['.$code["tag"].'\](.*)\[\/'.$code["tag"].'\]/Uis', $code["start"]."\\1".$code["ende"], $text);
		}
	}
	
	$text = preg_replace('/\n/', '<br />', $text);
	$text = preg_replace('/\[br\]/si', '<br />', $text);
	
	
	while(preg_match('/\[utube\](.*)\[\/utube\]/Uis', $text, $bbcode)){
		if(!preg_match('#youtube.com\/watch\?v=(.*)&#', $bbcode[1], $bbcode_new)){
			preg_match('#youtube.com\/watch\?v=(.*)#', $bbcode[1], $bbcode_new);
		}
		$key = $bbcode_new[1];
		$text = preg_replace('/\[utube\](.*)\[\/utube\]/Uis', '<object width="480" height="385"><param name="movie" value="http://www.youtube.com/v/'.$key.'&hl=de_DE&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/'.$key.'&hl=de&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>', $text, 1);
	}
	
	$patterns = array();
	$replacements = array();
	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url\](([\w]+?://)?((www\.|ftp\.)?[^ \"\n\r\t<]*?))\[/url\]#is";
	$replacements[] = "<a href=\"http://\\3\" ".$tpl->get_config_vars('CONF_LINK').">\\1</a>";

	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$patterns[] = "#\[url=([\w]+?://([^ \"\n\r\t<]*?))\](.*?)\[/url\]#is";
	$replacements[] = "<a href=\"http://\\2\" ".$tpl->get_config_vars('CONF_LINK').">\\3</a>";

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url=((www|ftp)?\.[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is";
	$replacements[] = "<a href=\"http://\\1\" ".$tpl->get_config_vars('CONF_LINK').">\\3</a>";

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[img\](([\w]+?://)?((www\.|ftp\.)?[^ \"\n\r\t<]*?))\[/img\]#is";
	$replacements[] = "<img style=\"max-width:450px;\" src=\"http://\\3\" ".$tpl->get_config_vars('CONF_IMAGE').">";

	
	// Syndikatsnummern stets verlinken
	$patterns[] = "#([^&0-9])\#([0-9]{1,2})([^0-9]{1})#is";
	$replacements[] = "\\1<a href=\"syndicate.php?rid=\\2\" ".$tpl->get_config_vars('CONF_SYN_LINK').">#\\2</a>\\3"; 
	
	$text = preg_replace($patterns, $replacements, $text);

	return $text;
}


/********************
set_link_for_mitteilungen
**********************/

function set_link_for_mitteilungen($id,$text) {
	if (func_num_args() > 2): $target = func_get_arg(2); endif;
	return "<a href=mitteilungen.php?action=psm&rec=$id class=linkAufsiteBg".($target ? " target=$target":"").">$text</a>";
}


/********************************************************************************

Function kill_account

*********************************************************************************/

function kill_account($user_id) { // Id aus dem usertable!
		$queries = array();
		$time = time();
		$globals=assoc("select * from globals order by round desc limit 1");
		$userdata = assoc("select * from users where id=$user_id");
		if ($userdata[id]) {
			if ($userdata[konzernid]) {
				$status = assoc("select * from status where id = $userdata[konzernid]");
			}
			if (!$status[rid])  {$status[rid] = 0;}
			if ($userdata[konzernid]) {
				kill_den_konzern($userdata[konzernid]);
				$action = ("delete from status where id=$userdata[konzernid]");
				array_push($queries,$action);
			}
		    $action = ("insert into options_accountdelete (user_id,syndicate,username,time,rid) values ($userdata[id],'$status[syndicate]','$userdata[username]',$time,'$status[rid]')");
			array_push($queries,$action);
			$action = ("delete from users where id=$userdata[id]");
			array_push($queries,$action);
			db_write($queries);
			return 1;
		}
		return 0;
}


/********************************************************************************

Function kill_den_konzern

*********************************************************************************/

function kill_den_konzern($id_being_killed) {

    /*
    Parameter:
    1 - Konzernid
    2 - "Way of Being killed"
    3 - Multidelete ?
    4 - Userid
    */
	global $globals;
	global $time;

    /*
    $multidelete hat folgende werte:
    1 - Nur Konzern L?chen
    2 - Konzern + Useraccount l?chen
    3 - Konzern l?chen, Useraccount bannen (kann sich nie mehr anmelden // Multis sollten gebannt werden)
    */
    #	$colorade = 1;	# zum farbigen hervorheben der entsprechenden queries (zum testen);
    $queries_intern = array();
    if (!$globals) {$globals = assoc("select * from globals order by round desc limit 1");}
    if (!$time) {$time = time();}

    if (func_num_args() > 1) {
    	$way_of_being_killed = func_get_arg (1);
    }
    if (func_num_args() > 2) {
    	$multidelete = func_get_arg (2);
    }
	if (func_num_args() > 3) {
        $useridlocal = func_get_arg (3);
		if (!$id_being_killed) {$id_being_killed = single("select konzernid from users where id=$useridlocal");}
	}

    #	$ausgabe .= "$id_being_killed blalbb<br>";

	if (!$id_being_killed) {
		list($username, $tmail,$tvorname,$tnachname,$emogames_id) = row("select users.username,users.email,users.vorname,users.nachname,users.emogames_user_id from users where users.id=".$user);
	}
	else {
		list($username, $rid, $syndicate, $tmail,$tvorname,$tnachname,$emogames_id) = row("select users.username,status.rid,status.syndicate,users.email,users.vorname,users.nachname,users.emogames_user_id from users,status where users.konzernid=".$id_being_killed." and status.id=".$id_being_killed);
	}
#	$ausgabe.="HIER: $rid, $username";
    #$ausgabe .= "<br><br>$action<br>bla: ($username, $rid)<br>========0";
	player_leave_syndicate($id_being_killed,$rid);
	
	/*$warArray=array(); //auf Optionseite
	for($c=1;$c<=3;$c++){
		$wars=single("select war_id from wars where first_synd_$c=$rid and starttime-24*60*60<=".time()." and endtime=0");
		if($wars) $warArray[$wars] = 'first_$c_add';
		$wars=single("select war_id from wars where second_synd_$c=$rid and starttime-24*60*60<=".time()." and endtime=0");
		if($wars) $warArray[$wars] = 'second_$c_add';
	}
	if($warArray){
		$landwar=single("select land from status where id=".$id_being_killed)*0.25;
		foreach($warArray as $warItem=>$warAdd){
			select("update wars set ".$warAdd."=".$warAdd."+".$landwar." where war_id=$warItem");
			warCheckAndHandle($warItem);
		}
	}*/
	
    if ($multidelete) {
        // Konzerndelete
        if ($multidelete == 1) {
        }
        // Accountdelete
        else if ($multidelete == 2) {
            $way_of_being_killed == "accountdelete";
        }
        // Ban
        else if ($multidelete == 3) {
        	$action = "update users set konzernid=0,deleted=127 where id=".$useridlocal;
            array_push($queries_intern,$action);
        }
    }


	$action = "update users set konzernid=0 where konzernid=$id_being_killed";
    array_push($queries_intern,$action);

	if ($way_of_being_killed === "accountdelete")	{ // Wenn Account gel?cht wird -> Forenaccount l?chen
        #echo "ACCOUNTDELETE way: $way_of_being_killed";
        #$action = "delete from users where id = ".$useridlocal;
        #$queries_intern[] = $action;
    }



	## Aus den blichen Tabellen l?chen
	#$action = "delete from settings where id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from jobs where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from aktien where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from aktien_privat where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from aktien_gebote where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from build_buildings where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from build_sciences where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from build_military where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from market where owner_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from build_spies where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from military_away where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from transfer where user_id=".$id_being_killed;array_push($queries_intern,$action);
	#$action = "delete from messages where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from message_values where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from usersciences where user_id=".$id_being_killed;array_push($queries_intern,$action);
	#$action = "delete from nw_safe where user_id=".$id_being_killed;array_push($queries_intern,$action);
	$action = "delete from naps_spieler where user_id=".$id_being_killed." or nappartner=".$id_being_killed;array_push($queries_intern,$action);
	//if (!($way_of_being_killed == "accountdelete"))	{
    	$action = "update status set alive=0, rid=0, land=0 where id=".$id_being_killed;array_push($queries_intern,$action);
    //}

	// Messages von diesem Konzern als gelesen markieren, falls diese noch ungelesen waren, wg. der Blinkanzeige im Seitenmen?
	$action = "update messages set gelesen = 1 where sender=".$id_being_killed." and gelesen = 0";array_push($queries_intern,$action);

    ## Sessionid sichern und anschlie?nd l?chen

    $sessionid_data = row("select sessionid, angelegt_bei, gueltig_bis, ip, user_id from sessionids_actual where user_id='".$id_being_killed."'");

    if ($sessionid_data[0])	{
        if ($sessionid_data[2] > $time) {
            $endtime = $time;
        }
        else {
             $endtime = $sessionid_data[2];
        }
        $action = "insert into sessionids_safe
                   (sessionid, angelegt_bei, gueltig_bis, ip, user_id)
                    values
                   ('".$sessionid_data[0]."',
                    ".$sessionid_data[1].",
                    ".$endtime.",
                    '".$sessionid_data[3]."',
                    ".$sessionid_data[4].")
                   ";
        array_push($queries_intern,$action);
        $action = "delete from sessionids_actual where sessionid='".$sessionid_data[0]."'";
        array_push($queries_intern,$action);
    }

	if (!$globals[round]) {$globals = assoc("select * from globals order by round desc limit 1");}
    if (!$multidelete){
    	if (!($way_of_being_killed == "accountdelete"))	{
	        $action = "update stats set rid = 0, alive=0 where round=$globals[round] and konzernid='".$id_being_killed."'";array_push($queries_intern,$action);
	    }
    }

    if ($multidelete) {
        $multimessage = " <font class=gelb10>[Verstoß gegen die Nutzungsbedingungen]</font>";
        $action = ("delete from stats where round=$globals[round] and konzernid='".$id_being_killed."'");
        array_push($queries_intern,$action);
    }
    else {
        $multimessage ="";
    }
    if (!($way_of_being_killed === "kampf")) {
        $action ="insert into towncrier (time,rid,message) values ($time,'$rid','Der Konzern ".$syndicate." (#".$rid.") meldet Konkurs an und ist nicht länger wirtschaftsfähig.".$multimessage."')";
        array_push($queries_intern,$action);
    }
    ##### Hier muss noch eine Mailfunktion rein fr sp?er dann.

    if ($way_of_being_killed === "kampf") {
        $to = $tvorname." ".$tnachname;
        $betreff = "Ihr Konzern meldet Konkurs an";
        $message="Ihr Syndicates Konzern unterlag im Konkurrenzkampf auf dem freien Markt.\nIhr Syndicates-Account bleibt aber weiterhin bestehen. Wenn Sie Syndicates wieder spielen wollen, können sie sich wie gewohnt mit ihren Accountdaten einloggen und einen neuen Konzern erstellen.\n\n Das Syndicates Entwicklerteam\n http://www.BETREIBER.de";
        sendthemail($betreff,$message,$tmail,$to);
    }

    if ($multidelete)	{
        $to = $tvorname." ".$tnachname;
        $betreff = "Ihr Konzern wurde gelöscht";
		if ($multidelete == 1 or $multidelete == 2 or $multidelete == 3) { // Verhindert das Senden einer Mail im neuen Admintool, 2. Februar 2006
			if ($multidelete == 1) {
				$message="Ihr Syndicates Konzern wurde wegen Verstoß gegen die Nutzungsbedingungen unwiederbringlich gelöscht.\nSofern Sie sich mit den Nutzungsbedingungen vertraut gemacht haben, steht es Ihnen offen, jederzeit einen neuen Account zu eröffnen.\n\n Das Syndicates Entwicklerteam\n http://www.BETREIBER.de";
			}
			else if ($multidelete == 2) {
				$message="Ihr Syndicates Account wurde wegen Verstoß gegen die Nutzungsbedingungen unwiederbringlich gelöscht.\nSofern Sie sich mit den Nutzungsbedingungen vertraut gemacht haben, steht es Ihnen offen, jederzeit einen neuen Account zu eröffnen.\n\n Das Syndicates Entwicklerteam\n http://www.BETREIBER.de";
			}
			else if ($multidelete == 3) {
					$message="";
			}
			sendthemail($betreff,$message,$tmail,$to);
		}
    }
    // Statements schreiben
    db_write($queries_intern);
    // Emogames - konzernid auf 0 setzen
    EMOGAMES_update_syndicates_konzernid($emogames_id,"0");
    #	$colorade = 0;	# zum farbigen hervorheben der entsprechenden queries (zum testen); aus
}

/********************************************************************************

Function sendthemail

*********************************************************************************/


function sendthemail() {
	

	
	if (isKsyndicates()) $k = "K-";
    $subject = $k."Syndicates - ".func_get_arg (0);		# Betreff
    $mailmessage = func_get_arg (1);					# Body-Message-Text
    $receiver = func_get_arg (2);						# Empf?ger (Mailadresse)
    $to = func_get_arg (3);								# AN: - Teil
	EMOGAMES_send_mail($subject,$mailmessage,$receiver,$to);
	
    $mailmessage = addslashes($mailmessage);


	/*$SENDER_EMAIL = "admin@domain.de";
	$SENDER_NAME = "Syndicates - Infomailer";
	$TO_EMAIL = $receiver;
	$SUBJECT = $subject;
	$MAIL_TEXT = $mailmessage;

	$ok = mail($TO_EMAIL, $SUBJECT, $MAIL_TEXT,
		"From: \"$SENDER_NAME\" <$SENDER_EMAIL>\n",
		("-f".$SENDER_EMAIL));*/

    //$ok = mail ($receiver,$subject,$mailmessage, "From: admin@domain.de","-admin@domain.de");

	/*
	if (!$handle = fopen("mails.txt", 'a')) {
			echo "Cannot open file ($filename)";
			exit;
	}
	$somecontent = "An: $receiver\nFrom: admin@domain.de\n\nBetreff: $subject\nBody:\n$mailmessage\n\n";
	// Write $somecontent to our opened file.
	if (!fwrite($handle, $somecontent)) {
		echo "Cannot write to file ($filename)";
		exit;
	}
	fclose($handle);
	*/
    return $ok;
}


/********************************************************************************

Function makesynlink

*********************************************************************************/
function makesynlink($synname,$synid,$classstyle = "") {
	$synlink = "<a href=\"syndicate.php?rid=$synid\" class=\"$classstyle\">$synname</a>";
}





/********************************************************************************

Function getsciences

*********************************************************************************/


function getsciences()  {

    // Argumente checken
    if (func_num_args() > 0) {$id_intern = func_get_arg (0);}
    else {$id_intern = $id;}
    $values = array();
	if (func_num_args() > 1){
		if(func_get_arg(1) == "killsciences"){
			$action ="select name,level from usersciences where user_id='$id_intern' and name != 'mil18' and name != 'ind19' and name != 'glo20'";
		}
		else{
			$action ="select name,level from usersciences where user_id='$id_intern'";
		}
	}
	else{
		$action ="select name,level from usersciences where user_id='$id_intern'";
	}
    $actionhandle = select($action);

    while ($return = mysql_fetch_row($actionhandle)) {
        #$ausgabe."Wert vorher: $values{$return[$i] } $return[$i] <br>";
        $values[$return[0]] = $return[1];

    }
    return $values;
}


/********************************************************************************

Function getpartner

*********************************************************************************/


function getpartner()  {
	global $id;
    // Argumente checken
    if (func_num_args() > 0) {$id_intern = func_get_arg (0);}
    else {$id_intern = $id;}
    $values = array();

    $action ="select pid, level from partnerschaften where user_id='$id_intern'";
    $actionhandle = select($action);

    while ($return = mysql_fetch_row($actionhandle)) {
        #$ausgabe."Wert vorher: $values{$return[$i] } $return[$i] <br>";
        $values[$return[0]] = $return[1];

    }
    return $values;
}


/********************************************************************************

Function checkmail()

*********************************************************************************/

function checkmail($string) {
	if (strlen($string) <= 5 || !preg_match("/[\w-_.]+\@([\w-]+\.)+\w{2,4}/",$string)) {
		return 0;
	}
	else {
		return 1;
	}
}

/********************************************************************************

Function end_job($job_id)

*********************************************************************************/

function end_job($job_id,$caller) {
	// Caller = 0, wenn direkt (dann smeldung, sonst nachricht)
	// Caller = 1, wenn indirekt durch subs beispielsweise
	DEFINE(JOB_PENALTY,0.1);
	global $time,$status;
	if (!$time) {$time = time();}
	$queries = array();
	$job_inner = assoc("select * from jobs where id=$job_id");
	static $resstats;
	if (!is_array($resstats)) {$resstats = getresstats();}

	// Wenn job berhaupt angenommen wurde
	if ($job_inner[acceptor_id]) {
		$schulden = 0;
		if ($caller == 1) {
			$takenstatus = assoc("select * from status where id=$job_inner[acceptor_id]");
		}
		else {
			$takenstatus = &$status;
		}
		$paymoney = floor($job_inner[money] * JOB_PENALTY); // tempor?r rausgenommen
		$payenergy = floor($job_inner[energy] * JOB_PENALTY);
		$paymetal = floor($job_inner[metal] * JOB_PENALTY);
		$paysciencepoints = floor($job_inner[sciencepoints] * JOB_PENALTY);
		$ressis = array(money,metal,energy,sciencepoints);
		foreach ($ressis as $v) {
			$what = "pay".$v;
			if ($takenstatus[$v] >= $$what) {
				$takenstatus[$v] -= $$what;
			}
			else {
				$schulden += (($$what- $takenstatus[$v])*$resstats[$v][value]);
				$takenstatus[$v] = 0;
			}
		}
		if ($schulden > 0) {
			if ($takenstatus[podpoints] > -1000000000) {$takenstatus[podpoints] -= $schulden;}
		}
		$anmessage = "Sie haben den angenommenen Auftrag nicht erfüllt, ".(JOB_PENALTY*100)."% der vereinbarten Prämie wurden als Konventionalstrafe von ihrem Konto abgezogen.";

		// Nachsehen, ob der Auftragnehmer bereits etwas Land erobert hatte und ihm dieses nachtr?glich gutschreiben, ?nderung Runde 39, 1. Januar 2009
		if ($job_inner[type] == "normal") 
		{
		  $landgrab = single("select sum(landgrab) from attacklogs where did=".$job_inner[target_id]." and aid=".$job_inner[acceptor_id]." and time > ".$job_inner[accepttime]." and type in (1,3)");
		  if ($landgrab > 0) {
		    $anmessage .= "<br>Sie konnten vom Auftragsziel aber insgesamt $landgrab ha erobern, welche jetzt gerade annektiert werden.";
		    $hours_for_landgain = HOURS_FOR_LANDGAIN;
		    //if ($takenstatus[race] == "neb") $hours_for_landgain = NEB_HOURS_FOR_LANDGAIN;
		    $queries[] = "insert into build_buildings (building_name, user_id, number, time, building_id) values ('land', '".$job_inner[acceptor_id]."', '$landgrab','".get_hour_time($time+$hours_for_landgain*3600)."','127')";
		  }

		}
		
		if ($caller == 0) {
			$takenstatus[nw] = nw($takenstatus[id]);
			s($anmessage);
		}
		else 
		{
			$queries[] = "
						insert into message_values
						(id,user_id,time,werte)
						values
						(44,$job_inner[acceptor_id],$time,'$anmessage')
			";
		}
		$agmessage = "Der Auftragnehmer konnte den Auftrag nicht erfolgreich ausführen, ihr Auftrag ist jetzt wieder für alle Spieler verfügbar.";
		$queries[] = "update status set money=$takenstatus[money], energy=$takenstatus[energy], metal=$takenstatus[metal],sciencepoints = $takenstatus[sciencepoints],podpoints=$takenstatus[podpoints],nw=$takenstatus[nw] where id=$takenstatus[id]";
		$queries[] = "insert into jobs_logs
						(id,user_id,acceptor_id,target_id,type,param,money,energy,metal,sciencepoints,inserttime,onlinetime,accepttime,anonym,finishtime,success)
						values
						($job_id,$job_inner[user_id],$job_inner[acceptor_id],$job_inner[target_id],'$job_inner[type]',$job_inner[param],$job_inner[money],$job_inner[energy],$job_inner[metal],$job_inner[sciencepoints],$job_inner[inserttime],$job_inner[onlinetime],$job_inner[accepttime],$job_inner[anonym],$time,0)";
		$queries[] = "update jobs set acceptor_id=0, accepttime=0 where id=$job_inner[id]";
		$queries[] = "insert into message_values
						(id,user_id,time,werte)
						values
						(44,$job_inner[user_id],$time,'$agmessage')
		";
		db_write($queries);
	}
}


/********************************************************************************

Function getunitstats

*********************************************************************************/


function getunitstats($race)  {

	$dummy_values = assocs("select * from military_unit_settings where race='dummy' order by sort_order", "type");
    $values = assocs("select * from military_unit_settings where race = '$race' or race='all' order by sort_order", "type");
	
	foreach($values as $key => $value){
	
		$values[$key]['race'] = $race;
		$typ=$values[$key]['type'];
		
		if($typ == "elites" || $typ == "elites2" || $typ == "techs")
			$values[$key]['unit_id'] = $dummy_values[$key]['unit_id'];	
	}
	
    return $values; 
	
}



/********************************************************************************



*********************************************************************************/

function paid($user_id) {
	/*
	global $globals,$time;
	if (!is_array($globals)) {
		$globals = assoc("select * from globals order by round desc limit 1");
	}
	// 3 M?lichkeiten fr Paid: 1 - Normales Abo, 2 - Abokey von Synabo, 3 - Eintrag von uns aus
	// 2. Argument fr Hardpaid! Dann wird nur paid zurckgegeben, wenn der User ein wirklich bezahltes Abo hat, frist und starttzeitraum in der zukunft wird dann nicht bercksichtigt
	if(func_num_args() == 2) {
		$hardpaid = 1;
	}
	if (!$hardpaid) {
		$paid = single("select aboid from payment_aboinfo where userident='$user_id' and (paid=1 or frist=1 or zeitraum_start>$time)");
		if (!$paid) {
			$paid = single("select user_id from user_keys where user_id='$user_id' and round>=$globals[round]");
		}
		if (!$paid) {
			$paid = single("select user_id from paid_users_intern where user_id='$user_id' and round>=$globals[round]");
		}
	}
	else {
		$paid = single("select aboid from payment_aboinfo where userident='$user_id' and paid=1");
		if (!$paid) {
			$paid = single("select user_id from user_keys where user_id='$user_id' and round>=$globals[round]");
		}
	}
	return $paid;
	*/
	return 1;
}




/********************************************************************************

Function getbuildingstats

*********************************************************************************/
function round_percent($value) {
	return (floor($value*100))/100;
}

/********************************************************************************

Function getbuildingstats

*********************************************************************************/
function getbuildingstats() {
	global $status;
	
	static $values;
	if (is_array($values)) return $values;
	
	$values = assocs( "select * from buildings","name_intern");
	if ($status) {
		foreach ($values as $k => $vl) {
			if (preg_match("/".($status[race])."/",$vl[race])) {
				$values[$k][race] = $status[race];
			}
		}
	}

	return $values;
}

/********************************************************************************

Function is_baubar

*********************************************************************************/
function is_baubar($geb_id) {
	global $status, $sciences, $developing_science;
	
	$geb = assoc("select * from buildings where building_id = '".$geb_id."'");
	if (preg_match("/".($status[race])."/",$geb[race])) {
		$geb["race"] = $status["race"];
	}
	if($geb["race"] == $status["race"] || $geb["race"] == "all"){
		if($geb["erforschbar"] && is_numeric($geb["erforschbar"])){
			// Die angegebe Zahl sagt aus, dass man mindestens eine Forschung Stufe X ben?tigt
			
			$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, description, gamename, sciencecosts,id from sciences where available=1 and level >= ".$geb["erforschbar"], "name");	//der science Table
			foreach ($sciences as $ky => $vl)	{
				if($sciencestats[$ky]["level"]){
					return 1;
				}
			}
			foreach($sciencestats as $val){
				if($val["name"] == $developing_science){
					return 2;
				}
			}
		}
		else if($geb["erforschbar"] && !is_numeric($geb["erforschbar"])){
			// Wenn man nur eine einfach Forschung ben?tigt
			if($sciences[$geb["erforschbar"]]["level"]>=$geb["erforschbar_lvl"]){
				return 1;
			}
			else if( $geb["erforschbar"] == $developing_science 
			&& $sciences[ $geb["erforschbar"] ]["level"]+1 >= $geb["erforschbar_lvl"] ){
				return 2;
			}
		}
		else{
			// Wenn man garnichts daf?r ben?tigt
			return 1;
		}
	}
	// Wenn man das Gebäude überhauptnich bauen darf
	return false;
	
	/*
	false => nicht baubar
	1 	  => baubar
	2	  => Forschung hierf?r ist in Bau
	*/
}


/********************************************************************************

Function getspystats

*********************************************************************************/


function getspystats($race)  {
    $values = assocs("select * from spy_settings where race = '$race' or race='all'","type");
	foreach($values as $key=>$value){
		$values[$key]['race']=$race;
	}
    return $values;
}


/********************************************************************************

Function getSavePodPoints

*********************************************************************************/


function getSavePodPoints($status1, $sciences1, $artefakt_id1)  {
	global $artefakte;
	$value = $status1['land'] * 
		(pow(2, 
			$sciences1["ind10"]+($artefakte[$artefakt_id1]['bonusname'] == "reduced_podtaxes" ? 1 : 0)))*PODSAVEPERLAND;
	// Guthaben im Lager, dass vor Lagerdiebstahl geschützt ist (in pod.php und spies.php verwendet)
    return $value;
}



/********************************************************************************

Function isMentor

*********************************************************************************/


function ismentor($id)  {

global $game;

if ($game[servertype] == 2) {
    $value = single("select is_mentor from users where konzernid = '$id'");
	return $value;
}}



/********************************************************************************

Function landkosten()

*********************************************************************************/

function landkosten() {
	global $status, $sciences, $partner;
	define(IND4BONUS, 10); # Land 10% billiger
	define(PARTNER_LANDPRICEBONUS,10);

	if ($status[land] > 0) {
		//$landkosten =  1000 + ( $status{land}*$status{land}) / 200;
		$landkosten =  landkosten_base($status[land]);
	}
	else {
		$landkosten = 1000;
	}
	// $landcosts *=0.75; <- war im perl script
	if ($status{land} <= 100) {$landkosten = 1000;}
	if ($status{race} == "pbf") {$landkostenmod += 0.0;} # Land 10% teurer // Seit Runde 30 0 
	elseif ($status[race] == "neb") { $landkostenmod -= 0.20; } //neb baut 20% billiger land
	if ($sciences{ind4}) { $landkostenmod -= 0.1 * $sciences{ind4}; }
	if ($partner[10]) { $landkostenmod -= PARTNER_LANDPRICEBONUS / 100 * $partner[10]; }	# Partnerschaftsbonus: Der Erwerb von Land wird 5% kostengnstiger
	$landkosten *= 1 + $landkostenmod;

	return (int) $landkosten;
}


function landkosten_base($land) {
	return 1000 + ( $land*$land) / 350;
}





function printhonors($honors) {
	global $ausgabe;
	$tabellenregler = 0;
	$ausgabe .= "<br><table width=400 align=left class=tableInner1>";

	foreach ($honors as $vl)	{
			$tabellenregler++;
			if ($tabellenregler == 1): $tabellenanfang = "<tr><td>"; $tabellenende = "</td>";
		elseif ($tabellenregler == 2): $tabellenanfang = "<td>"; $tabellenende = "</td>";
		elseif ($tabellenregler == 3): $tabellenanfang = "<td>"; $tabellenende = "</td></tr>"; $tabellenregler = 0;
		endif;

			if ($vl[honorcode] == 1): $symbol = "award_gold.gif";
		elseif ($vl[honorcode] == 2): $symbol = "award_silver.gif";
		elseif ($vl[honorcode] == 3): $symbol = "award_bronze.gif";
		elseif ($vl[honorcode] == 4): $symbol = "medal_gold.gif";
		elseif ($vl[honorcode] == 5): $symbol = "medal_silver.gif";
		elseif ($vl[honorcode] == 6): $symbol = "medal_bronze.gif";
		endif;

			if ($vl[round] == 1): $honorround = "Beta 1";
		elseif ($vl[round] == 2): $honorround = "Beta 2";
		elseif ($vl[round] > 2): $honorround = "Runde ".($vl[round]-2);
		endif;

		if ($vl[honorcode] <= 3)	{
			$ausgabe .= "
				$tabellenanfang
				<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98\" class=tableInner1 background=\"".$layout[images]."$symbol\">
				<tr>
					<td width=\"100%\">
					<p style=\"font-family: Arial; font-size: 11px; font-weight: 700; color: #000000\" align=\"center\">
					<img border=\"0\" src=\"".$layout[images]."blank.gif\" width=\"98\" height=\"73\"><br>$honorround<br>
					<img border=\"0\" src=\"".$layout[images]."blank.gif\" width=\"98\" height=\"13\"></span></td>
				</tr>
				</table><br>
				$tabellenende
			";
		}
		elseif ($vl[honorcode] >= 4 and $vl[honorcode] <= 6)	{

			$ausgabe .= "
			$tabellenanfang
				<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100\" class=tableInner1>
				<tr>
					<td width=\"100%\">
					<p style=\"font-family: Arial; font-size: 11px; font-weight: 700\" align=\"center\">
					<img border=\"0\" src=\"".$layout[images]."$symbol\" width=\"45\" height=\"72\"><br>$honorround: Rang ".$vl[rank]."</td>
				</tr>
				</table><br>
			$tabellenende
			";
		}
	}

	if ($tabellenregler == 1 or $tabellenregler == 2): $ausgabe .= "</tr>"; endif;

	$ausgabe .= "</table></td></tr>";

}

/********************************************************************************

Function synmemberids_online


-- Gibt die Ids der Spieler des eigenen Syndikats zurck, die gerade online sind
*********************************************************************************/


function synmemberids_online() {
	global $status,$time,$id;
	$syndmemberids = singles("select id from status where rid=".$status[rid]." and id!=$id and alive > 0");
	if (count($syndmemberids) > 0) {
		$syndmemberids_online = singles("select user_id from sessionids_actual where gueltig_bis > $time and user_id in (".join(",", $syndmemberids).")");
	}
	return $syndmemberids_online;

}


/********************************************************************************

Function getallvalues

*********************************************************************************/

/*
#
##	$_[0] = "Titel der jeweiligen Page";
#
##	$_[1] = "Name auf die Hilfeseite";
#
##	$_[2] = "Untertitel" (falls erfordert) - noch nirgendwo benutzt -> Hilfetext seit R46
#
*/
function kopf($headline,$hilfeseite,$hilfetext = "") {
	
	global $layout,$status,$init,$page,$features, $time, $globals,$informationmeldung;
	//pvar($informationmeldung);


	if ($status[noob_stage] == 1) {
		global $action;
		if (!($action == "sm")) {
			$WERBUNG = "mitteilung_schreiben";
		}
	}
	elseif ($status[noob_stage] == 3) {
		$WERBUNG = "eusterw_anleitung";
	}
	elseif ($status[noob_stage] == 5) {
		$WERBUNG = "anleitung";
	}
	if ($WERBUNG == "mitteilung_schreiben" && $globals[roundstatus] == 1) {
		global $ripf;
		$onlinemembers = synmemberids_online();
		if (count($onlinemembers) > 0) {
			$onlinemembers = assocs("select syndicate,id from status where id in (".join(",", $onlinemembers).")");
		}
			$header = "
					<table width=\"580\" align=center class=tableOutline cellpadding=0 cellspacing=1>
					<tr>
						<td align=\"center\">
							<table width=\"100%\" cellpadding=5 cellspacing=0>
								<tr>
									<td class=tableHead>
										<b>Neue Aufgabe: Eine Nachricht an einen Mitspieler schreiben!</b>
									</td>
								</tr>
								<tr>
									<td class=tableInner1>
										Syndikates ist ein Teamspiel!<br>
										Du spielst mit bis zu ".MAX_USERS_A_SYNDICATE." Spielern in einem Syndikat zusammen.
										Folgende Spieler aus deinem Syndikat sind gerade online:<br>";
										$i=0;
										foreach($onlinemembers as $temp) {
											if ($i < 5) {
												$header.="<li>$temp[syndicate]&nbsp;
												<a href=mitteilungen.php?action=psm&rec=$temp[id]&betreff=$betreff><img src=\"".$ripf."_syn_message_letter.gif\" border=0></a>
												<br>";
											}
											$i++;
										}
										$header.="
										<br>
										schreib doch einfach mal einem von Ihnen eine Nachricht und sag Hallo.
									</td>
								</tr>
							</table>
									
			
			";
			werbung_hit("mitteilung_schreiben_fs");
		
	}
	elseif ($WERBUNG == "eusterw_anleitung" && $globals[roundstatus] == 1) {
		$zufallszahl_vote_class = mt_rand(0,3);
		$classes = array( "tableHead2", "tableInner1", "tableInner2", "siteGround");
		global $yellowdot;
		$header = "
				<table width=\"580\" align=center class=tableOutline cellpadding=0 cellspacing=1>
				<tr>
					<td align=\"center\">
						<table cellpadding=5 cellspacing=0>
							<tr>
							<td class=$classes[$zufallszahl_vote_class]>
								<a href='statusseite.php?headeraction=Einsteigerhilfe' target='_blank'><img src='".$layout['images']."/pic_info_webmail_helpicon.gif' border=0 align=texttop></a>
							</td>
							<td class=$classes[$zufallszahl_vote_class]>
								<b>Neue Aufgabe: den Syndicates Anfänger-Guide lesen!</b><br>Nimm Dir 10 Minuten Zeit und <a href='statusseite.php?headeraction=Einsteigerhilfe' class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank>lies Dir folgende Einstiegshilfe durch</a>.<br>Zum Lesen benötigst Du den Adobe Acrobat Reader, welchen Du Dir <a href=\"statusseite.php?headeraction=Acrobat-Reader\" class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank>hier</a> herunterladen kannst.
							</td>
								<td class=$classes[$zufallszahl_vote_class]>
								<a href='statusseite.php?headeraction=Acrobat-Reader' target='_blank'><img src='".$layout['images']."/getacrobat.gif' border=0 align=texttop></a>
							</td></tr>
							<!--
							<tr>
							<td colspan=3 class=$classes[$zufallszahl_vote_class]>
								Nachdem Du Dir die Einstiegshilfe durchgelesen hast, bist Du bestimmt bereit für ein kleines Quiz!<br><b>Du kannst</b> dabei bis zu <b>100 EMOs gewinnen, mit denen sich Premium Features erwerben lassen.</b>!<br><a href=quiz.php class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg").">${yellowdot}Auf zum Quiz!</a>
							</td>
							</tr>-->
						</table>";
				werbung_hit("eustianleitung_fs");
	}
	elseif ($WERBUNG == "anleitung" && $globals[roundstatus] == 1) {
		$zufallszahl_vote_class = mt_rand(0,3);
		$classes = array( "tableHead2", "tableInner1", "tableInner2", "siteGround");
		global $yellowdot;
		$header = "
				<table width=\"580\" align=center class=tableOutline cellpadding=0 cellspacing=1>
				<tr>
					<td align=\"center\">
						<table cellpadding=5 cellspacing=0>
							<tr>
								<td class=$classes[$zufallszahl_vote_class]>
									<b>Neue Aufgabe: Die Syndicates Anleitung kennen!</b><br><br>
									Syndicates verfügt ber eine umfangreiche <a href='statusseite.php?headeraction=Anleitung' class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank>Anleitung</a>.<br>
									Du musst diese nicht sofort komplett lesen, du solltest aber darüber Bescheid wissen, dass es diese Anleitung gibt.
									Zur Anleitung kommst du entweder über <a href='statusseite.php?headeraction=Anleitung' class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank>diesen Link</a>, oder von der Syndicates  <a href='http://BETREIBER.de'  class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank >Startseite</a> über den Menupunkt <a href='statusseite.php?headeraction=Anleitung' class=".($classes[$zufallszahl_vote_class] == "tableHead" ? "highlightAufSiteBg" : "highlightAufSiteBg")." target=_blank>Anleitung</a>. Die Anleitung ist außerdem durchgehend im Kopfteil über das Fragezeichen im grauen Viereck verlinkt.<br>
									In der Anleitung findest du nahezu alle Informationen zu Syndicates, die dich irgendwann einmal interessieren könnten.
								</td>
							</tr>
						</table>";
				werbung_hit("eustianleitung_fs");
	}

	if (!$header && !$features[WERBUNG_DEAKTIVIERT] && !isKsyndicates()) {
		require_once(INC."werbung_header.php");
		$header = "<table class=tableOutline cellpadding=1 cellspacing=0 align=center><tr><td>".getHeaderWerbung()."</td></tr></table>";
	}

	if ($header) {
		$header .="
							</td>
							</tr>
							</table>
		";
	}



	if ($features[WERBUNG_DEAKTIVIERT] && $WERBUNG != "eusterw_anleitung") {
		$header = "";
	}
	
	global $ripf;
	$boxtip = "";
	$tooltip = "";
	if($hilfetext){
		$tooltip = 		js::input($hilfetext." (click)");	
	}
	//<img src=\"".$ripf."_help_bigger.gif\" border=\"0\">
	
					
	$link_hilfeseite = $hilfeseite ? WIKI.$hilfeseite : WIKI."Synpedia_-_Spielanleitung_f%C3%BCr_Syndicates";

	$headdata = Array(
		"HEADLINE" => $headline,
		"LINK_HILFESEITE" => $link_hilfeseite,
		"SHOW_HELP" => $status[show_help],
		"HILFETEXT" => $hilfetext,
		"TOOLTIPPTEXT" => $tooltip,
		"VOTECODE" => getVoteCode($status[id]),
		"WERBUNG" => $header,
	);	

	return $headdata;

}



//############################################## mCrypt 2.4.9 ###############
function hex2bin($data) {
$len = strlen($data);
for($i=0;$i<$len;$i+=2) {
$newdata .= pack("C",hexdec(substr($data,$i,2)));
}

return($newdata);
}


function my_encrypt($sString) { //Daten verschlsseln
$sCryptoKey = "tz54gh8hg";
if($sString!="" && $sCryptoKey!="") {
$iIV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256,
MCRYPT_MODE_ECB), MCRYPT_RAND);
$sEncrypted = mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $sCryptoKey,
$sString, MCRYPT_MODE_ECB, $iIV);
$sEncrypted=bin2hex($sEncrypted);
}
 else {
$sEncrypted=$sString;
}

return($sEncrypted);
}

 


function my_decrypt($sString) { //Daten entschlsseln
$sCryptoKey = "tz54gh8hg";
if($sString!="" && $sCryptoKey!="") {
$sString = hex2bin($sString);
$iIV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256,
MCRYPT_MODE_ECB), MCRYPT_RAND);
$sDecrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sCryptoKey,
$sString, MCRYPT_MODE_ECB, $iIV);
}




else {
$sDecrypted=$sString;
}

return(trim($sDecrypted));
}



function my_k_encrypt($sString) { //Daten verschlsseln
$sCryptoKey = "fgd76rt74ur5";
if($sString!="" && $sCryptoKey!="") {
$iIV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256,
MCRYPT_MODE_ECB), MCRYPT_RAND);
$sEncrypted = mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $sCryptoKey,
$sString, MCRYPT_MODE_ECB, $iIV);
$sEncrypted=bin2hex($sEncrypted);
}
 else {
$sEncrypted=$sString;
}

return($sEncrypted);
}

function my_k_decrypt($sString) { //Daten entschlsseln
$sCryptoKey = "fgd76rt74ur5";
if($sString!="" && $sCryptoKey!="") {
$sString = hex2bin($sString);
$iIV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256,
MCRYPT_MODE_ECB), MCRYPT_RAND);
$sDecrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sCryptoKey,
$sString, MCRYPT_MODE_ECB, $iIV);
}




else {
$sDecrypted=$sString;
}

return(trim($sDecrypted));
}



function print_sygnatur($username, $userid, $konzernname, $land, $nw, $synd_name, $rid, $fraktion = "uic", $rank = 0, $type = 'actualdata', $temp = array())
{
	static $gpack;
	//Initialisierung
	if (!$gpack) $gpack = assoc("select * from gpacks where gpack_id=1");
	$fraktionshintergruende = array ( "sl" 	=> DATA."/images/syg_sl2.gif",
					  "pbf"	=> DATA."/images/syg_bf2.gif",
					  "nof"	=> DATA."/images/syg_nof2.gif",
					  "uic" => DATA."/images/syg_uic2.gif",
					  "neb" => DATA."/images/syg_neb2.gif");
	$nwlogo = DATA."syn_gpacks/".$gpack[gpack_id]."/networth.gif";
	$landlogo = DATA."syn_gpacks/".$gpack[gpack_id]."/land.gif";
	$honorlogos = array(
		1 => DATA."syn_gpacks/".$gpack[gpack_id]."/pokalgold.gif",
		2 => DATA."syn_gpacks/".$gpack[gpack_id]."/pokalsilber.gif",
		3 => DATA."syn_gpacks/".$gpack[gpack_id]."/pokalbronze.gif",
		4 => DATA."syn_gpacks/".$gpack[gpack_id]."/medaillegold.gif",
		5 => DATA."syn_gpacks/".$gpack[gpack_id]."/medaillesilber.gif",
		6 => DATA."syn_gpacks/".$gpack[gpack_id]."/medaillebronze.gif",
		11 => DATA."syn_gpacks/".$gpack[gpack_id]."/Syn1.gif",
		12 => DATA."syn_gpacks/".$gpack[gpack_id]."/Syn2.gif",
		13 => DATA."syn_gpacks/".$gpack[gpack_id]."/Syn3.gif"
	);
	//pvar($honorlogos);

	$basewidth = 67;
	$baseheight = 38;
	
	// Hintergrund laden
		$img = imagecreatefromgif($fraktionshintergruende[$fraktion]);
		$weiss  = imagecolorallocate ($img, 253, 253, 254);

	if ($type == "actualdata") {
		//Konzernnamen schreiben:
		imagestring ($img, 2, $basewidth-3, $baseheight+0, "Konzern: $konzernname", $weiss);
	
		// Syndikatsname
		if ($synd_name === false) {
		imagestring ($img, 2, $basewidth-16, $baseheight+14, "Runde noch nicht gestartet - jetzt anmelden!", $weiss);
		} else 
		imagestring ($img, 2, $basewidth-9, $baseheight+14, "Syndikat: ".$synd_name." (#$rid)", $weiss);
	
		// Land
		imagestring ($img, 2, $basewidth+30, $baseheight+30, pointit($land)." ha", $weiss);
		$img_to_copy_from = imagecreatefromgif($landlogo);
		$size = getimagesize($landlogo);
		$faktor = 0.9;
		imagecopyresized ($img, $img_to_copy_from, $basewidth+0, $baseheight+30, 0, 0,$size[0]*$faktor, $size[1]*$faktor, $size[0], $size[1]);
		
		// Networth
		imagestring ($img, 2, $basewidth+130, $baseheight+30, pointit($nw)." NW", $weiss);
		$img_to_copy_from = imagecreatefromgif($nwlogo);
		$size = getimagesize($nwlogo);
		imagecopyresized ($img, $img_to_copy_from, $basewidth+100, $baseheight+30, 0, 0, $size[0]*$faktor, $size[1]*$faktor,$size[0], $size[1]);
	
		// Rank
		if ($rank) {
			$fw = imagefontwidth(2);
			$middle_pixel = 383;
			$begin_pixel = $middle_pixel - round(strlen($rank) * $fw / 2);
			imagestring ($img, 2, 370, 5, "Rang", $weiss);
			imagestring ($img, 2, $begin_pixel, 20, "$rank", $weiss);
		}

	// USERNAME schreiben
	imagestring ($img, 3, $basewidth+70, $baseheight-15, $username, $weiss);


		
	} //## $type == "actualdata"
	elseif ($type == "honors") {
		$honors = $temp;
		for ($i = 1; $i <= 13; $i++) {
			if (!$honors[$i]) $honors[$i] = 0;
			if ($i == 6) $i = 10;
		}
		ksort($honors);
		$i = 0;
		foreach ($honors as $honor_id => $honored_times) {
			if ($honored_times) {
				$widthadjust = $i * 45;
				$img_to_copy_from = imagecreatefromgif($honorlogos[$honor_id]);
				//pvar($honor_id." ".$honorlogos[$honor_id]." ".$img_to_copy_from);
				$size = getimagesize($honorlogos[$honor_id]);
				//if ($honored_times == 0) {	imagegreyscale($img_to_copy_from, false); $faktor = 0.40; } else $faktor = 0.5;
				if (10 < $honor_id) {
					$faktor = 0.4;
				} else {
					$faktor = 0.5;
				}
				imagecopyresized ($img, $img_to_copy_from, $basewidth-20+$widthadjust, $baseheight-10, 0, 0, $size[0]*$faktor, $size[1]*$faktor,$size[0], $size[1]);
				imagestring ($img, 5, $basewidth-20+$widthadjust+$size[0]*$faktor*0.2, $baseheight-25, $honored_times."x", $weiss);
				$i++;
			}
			// mehr als 6 Auszeichnungen machen die Sygnatur kaputt, deshalb werden sie nicht angezeigt
			if ($i == 6) break;
		}

		// USERNAME schreiben
		imagestring ($img, 3, $basewidth+120, $baseheight+30, $username, $weiss);
	}
	elseif (preg_match("/stats(.+)/", $type, $what)) {
		$stats = $temp;
		$temp = array();
		$sortorder = array("lastnetworth" => "down", "endrank" => "up");
		foreach ($stats as $vl) {
			$temp[$vl['id']] = $vl[$what[1]];
			if ($sortorder[$what[1]] == "down") {
				arsort($temp);
			} else asort($temp);
		}
		$i = 0;
		foreach($temp as $identifier => $trash) {
			$vl = $stats[$identifier];
			$i++; if ($i >= 5) break;
			$roundsymbol = $vl['round'] <= 2 ? "B" : "R";
			$vl['round'] = $vl['round'] > 2 ? $vl['round']-2 : $vl['round'];
			$text = $roundsymbol.$vl['round'].": #".pointit($vl['endrank']).", ".pointit($vl['lastnetworth'])."NW, ".pointit($vl['lastland'])."ha, ".$vl['syndicate'];
			imagestring ($img, 2, $basewidth-28, $baseheight-25+$i*14, $text, $weiss);
			
		}
		// USERNAME schreiben
		imagestring ($img, 3, $basewidth+70, $baseheight-28, $username, $weiss);
	}

	
	
	// Bild auf Dateisystem schreiben
	imagegif($img, DATA.'sygnatur/'.md5(md5($userid)).".gif");
}

function imagegreyscale(&$img, $dither=1) {   
   if (!($t = imagecolorstotal($img))) {
       $t = 1;
       imagetruecolortopalette($img, $dither, $t);   
   }
   for ($c = 0; $c < $t; $c++) {   
       $col = imagecolorsforindex($img, $c);
       $min = min($col['red'],$col['green'],$col['blue']);
       $max = max($col['red'],$col['green'],$col['blue']);
       $i = ($max+$min)/2;
       imagecolorset($img, $c, $i, $i, $i);
   }
}

function syndicate_total_networth($nw_array_of_all_members, $land_array_of_all_members=0)
{
	$nwsum = -1;
	$landsum = -1;
	
	$countedMemberNum = USERS_USED_FOR_RANKING;
	
	if( count($nw_array_of_all_members) < $countedMemberNum )
	{
		$countedMemberNum = count( $nw_array_of_all_members );
	}
	
	
	if( count($land_array_of_all_members) == count($nw_array_of_all_members) )
	{
		$sortedElements = array();
		
		for($i=0;$i<$countedMemberNum;$i++)
		{
			$sortedElements[] = array( 'nw' => 0, 'land' => 0);
		}
		
		$allElements = count($nw_array_of_all_members);
		
		for($i=0;$i<$allElements;$i++)
		{
			$sortedElements_num = count($sortedElements);
			for( $j = 0; $j < $sortedElements_num; $j++)
			{
				if( $nw_array_of_all_members[$i] > $sortedElements[$j]['nw'] )
				{
					if( $j != 0)
					{
						$sortedElements[$j-1]['nw'] = $sortedElements[$j]['nw'];
						$sortedElements[$j-1]['land'] = $sortedElements[$j]['land'];
					}
					$sortedElements[$j]['nw'] = $nw_array_of_all_members[$i];
					$sortedElements[$j]['land'] = $land_array_of_all_members[$i];
				}
				else
				{
					break;
				}				
			}
		}
		
		$nwsum = 0;
		$landsum = 0;
		
		for($i = 0; $i < $countedMemberNum; $i++)
		{
			$nwsum += $sortedElements[$i]['nw'];
			$landsum += $sortedElements[$i]['land'];
		}		
		
	}
	else
	{
		rsort($nw_array_of_all_members);
		
		$nwsum = 0;
		
		for($i = 0; $i < $countedMemberNum; $i++)
		{
			$nwsum += $nw_array_of_all_members[$i];
		}
	}
	
	$result = array();
	$result['nw'] = $nwsum;
	$result['land'] = $landsum;
	
	return $result;
}

function getBuildingTooltip($value, $showdescription){
	$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, description, gamename, sciencecosts,id from sciences where available=1", "name");	//der science Table
	if(is_numeric($value["erforschbar"])){
		$fos = "mind. Stufe ".$value["erforschbar"]." Forschung (beliebig)";
	}
	else{
		if($sciencestats[$value["erforschbar"]]["gamename"]){
			$fos = $sciencestats[$value["erforschbar"]]["gamename"];
		}
		else{
			$fos = "keine Forschung nötig";
		}
	}
	$tooltip =  "</td></tr><tr><td class=\"tableHead2\" width=\"300\">
	<b>Bezeichnung:</b> ".$value["name"]."
	</td></tr><tr><td class=\"tableInner1\">
	<b>Eigenschaft: ".$value["nutzen"]."</b><br />
	</td></tr><tr><td class=\"tableInner1\">
	<b>Synergiebonus:</b> ".($value["synbonus"] == '*'?"ja":"nein")." <br />
	<b>Stromverbrauch:</b> ".$value["intverbrauch"]." Mwh pro Stunde<br />
	<b>Networth:</b> ".$value["nw"]." NW<br />
	<b>Forschung:</b> ".$fos;
	if($showdescription){
		$tooltip.=" </td></tr><tr><td class=\"tableInner1\" width=\"300\"><b>Beschreibung:</b> ".$value["description"];	
	}
	return getJsHelpTag($tooltip);
}

function getMilitaryTooltip($value, $showdescription){
	$tooltip =  "</td></tr><tr><td class=\"tableHead2\" width=\"300\">
	<b>Bezeichnung:</b> ".$value["name"]."
	</td></tr><tr><td class=\"tableInner1\">
	<b>Angriffspunkte:</b> ".$value["op"]." AP<br />
	<b>Verteidigungspunkte:</b> ".$value["dp"]." VP<br />
	<b>Networth:</b> ".sprintf('%2.1f',$value["nw"])." NW<br />
	<b>Forschung:</b> ".($value["erforschbar"] == 0?"keine Forschung nötig":($value["erforschbar"] == 1?"Advanced Unit Construction":($value["erforschbar"] == 2?"Hightech Unit Construction":($value["erforschbar"] == 3?"Behemothfabriken":($value["erforschbar"] == 4?"Basic Unit Construction":"-")))))."
	</td></tr><tr><td class=\"tableInner1\"  width=\"300\"><b>Special:</b> ".$value["specials"]."<br />";
	if($showdescription){
		$tooltip.=" </td></tr><tr><td class=\"tableInner1\" width=\"300\"><b>Beschreibung:</b> ".$value["description"];	
	}
	return getJsHelpTag($tooltip);
}

function getSpyTooltip($value, $showdescription){
	$tooltip =  "</td></tr><tr><td class=\"tableHead2\" width=\"300\">
	<b>Bezeichnung:</b> ".$value["name"]."
	</td></tr><tr><td class=\"tableInner1\">
	<b>Spionagepunkte:</b> ".$value["ip"]." IP<br />
	<b>Sabotagepunkte:</b> ".$value["op"]." OP<br />
	<b>Spionageabwehr:</b> ".$value["dp"]." DP<br />
	<b>Networth:</b> ".sprintf('%2.1f',$value["nw"])." NW<br />";
	if($showdescription){
		$tooltip.=" </td></tr><tr><td class=\"tableInner1\" width=\"300\"><b>Beschreibung:</b> ".$value["description"];	
	}
	return getJsHelpTag($tooltip);
}

function getFosTooltip($value, $showdescription){
	$tooltip =  "</td></tr><tr><td class=\"tableHead2\" width=\"300\">
	<b>Bezeichnung:</b> ".$value["gamename"]."
	</td></tr><tr><td class=\"tableInner1\">
	<b>Forschungsbaum:</b> ".($value["treename"] == 'mil'?"Military Sciences":($value["treename"] == 'glo'?"Global Sciences":"Industiral Sciences"))."<br />
	<b>Baumlevel:</b> ".$value["level"]."<br />
	<b>Ausbaustufen:</b> ".$value["maxlevel"]."<br />
	<b>Networth:</b> ".($value["gamename"] == 'Gamble'?"0":pointit(constant("NW_FOS_LVL".$value["level"])))."<br />";
	if($showdescription){
		$tooltip.=" </td></tr><tr><td class=\"tableInner1\" width=\"300\"><b>Beschreibung:</b> ".$value["description"];	
	}
	return getJsHelpTag($tooltip);
}

function classicGroupShuffleScript()
{
	define(BREAKPOINT,0.60);
	
	$groupquerry = "SELECT 
    group_id,
    is_mentor_group,
    open,
    u1,u2,u3,u4,u5,u6,u7,u8,u9,u10,u11,u12,u13,u14,u15,u16,u17,u18,u19,u20,
    (
        ( CASE u1 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u2 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u3 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u4 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u5 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u6 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u7 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u8 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u9 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u10 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u11 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u12 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u13 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u14 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u15 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u16 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u17 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u18 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u19 WHEN 0 THEN 0 ELSE 1 END) + 
        ( CASE u20 WHEN 0 THEN 0 ELSE 1 END)
    ) AS num FROM groups ORDER BY is_mentor_group DESC, num DESC";
	$gruppen = assocs($groupquerry);
		
	$synAliveData = assocs("select id, alive from status");
	$synAliveData_processed = array();
	
	foreach($synAliveData as $i => $i_v)
	{
		$synAliveData_processed[$i_v['id']] = $i_v['alive'];
	}
	unset($synAliveData);
	
	$gruppen_sum = 0;
	$gruppenMemberCount = array();
	$gruppenMember = array();
	
	$num1 = 0;
	$num2 = 0;
	$num3 = 0;
	$num4 = 0;
	$num5 = 0;
	$num6 = 0;
	$num7 = 0;
	$num8 = 0;
	$unknown = 0;
	
	foreach($gruppen as $i => $currentGroup)
	{
		for($i=1;$i <= 20; $i++)
		{
			$currentGroupMember = $currentGroup["u" . $i];
			if( $currentGroupMember != 0 )
			{
				if( array_key_exists($currentGroupMember,$synAliveData_processed) )
				{
					if( $synAliveData_processed[$currentGroupMember] != 0 )
					{
						$gruppenMember[$currentGroup['group_id']][] = $currentGroupMember;
						unset($synAliveData_processed[$currentGroupMember]);
					}
					else
					{
						"Konzern " . $currentGroupMember . " ist bereits tot..\n";
						unset($synAliveData_processed[$currentGroupMember]);
					}
				}
				else
				{
					echo "Geisterkonzern gefunden mit id: " . $currentGroupMember . "!\n";
				}
			}
			
		}

		$gruppen_sum += count($gruppenMember[$currentGroup['group_id']]);
		
		switch(count($gruppenMember[$currentGroup['group_id']]))
		{
			case 1: $num1++; break;
			case 2: $num2++; break;
			case 3: $num3++; break;
			case 4: $num4++; break;
			case 5: $num5++; break;
			case 6: $num6++; break;
			case 7: $num7++; break;
			case 8: $num8++; break;
			default: $unknown++;break;
		}
	}
	
	foreach($synAliveData_processed as $i => $i_v)
	{
		if( $i_v == 0 )
		{
			unset($synAliveData_processed[$i]);
		}
	}
	
	echo "Nicht zu Gruppen gehörende Konzerne: " . count($synAliveData_processed) . "\n";
	$gruppen_sum += count($synAliveData_processed);
	$num1 += count($synAliveData_processed);
	// print_r($synAliveData_processed);

	echo "1: " . $num1 . "\n"; 
	echo "2: " . $num2 . "\n";
	echo "3: " . $num3 . "\n";
	echo "4: " . $num4 . "\n";
	echo "5: " . $num5 . "\n";
	echo "6: " . $num6 . "\n";
	echo "7: " . $num7 . "\n";
	echo "8: " . $num8 . "\n";
	echo "unknown: " . $unknown . "\n";
	
	$absBreakpoint = $gruppen_sum * BREAKPOINT;
	
	$syns = array();
	$synMemberCount = array();
	$konzerneProcessed = 0;
	$playerSpace = 0;
	
	$queries = array();
	
	//print_r($gruppen);
	
	foreach($gruppenMember as $i => $currentGroup)
	{
		// echo "last free:" . $playerSpace . "\n";
		// echo "inserting group " . $i . "(num:" . count($currentGroup) . "|free:" . $playerSpace . ")\n";
		$currentMax = 0;
		if($konzerneProcessed < $absBreakpoint)
		{
			$currentMax = MAX_USERS_A_GROUP;
		}
		else
		{
			$currentMax = MAX_USERS_A_SYNDICATE;
		}
		
		if( $playerSpace < count($currentGroup) )  // wenn gruppenanzahl nirgendwo reinpasst, neues syn erstellen
		{
			// echo "creating new syn..\n";
			$index = count($syns); // get next possible insertion point
			$syns[$index][] = $i;
			
			$synMemberCount[$index] = count($currentGroup);	
			$konzerneProcessed += count($currentGroup);
			
			if( ( $currentMax - $synMemberCount[$index] ) >= $playerSpace )
			{
				$playerSpace = $currentMax - $synMemberCount[$index];
			}
			
			$queries[] = "update board_subjects set bid = ".$index." where bid = ".(60000+$i);
			$queries[] = "update polls set synd_id = ".$index." where synd_id = ".(60000+$i);
			
			
			// echo "New Syn: " . $index . "|||space left: " . $playerSpace . "\n";
		}
		else
		{
			// echo "inserting in syn ";
			$inserted = false;
			$playerSpace = 0;
			
			$j_max = count($syns);
			foreach( $syns as $j => $currentSyn)
			{
				if( !$inserted && $synMemberCount[$j] + count($currentGroup) <= $currentMax )
				{
					// echo $j . "..\n";
					$syns[$j][] = $i;			
					$synMemberCount[$j] += count($currentGroup);	
					$konzerneProcessed += count($currentGroup);
					$inserted = true;
				}
				
				$currentSynSizeLeft = $currentMax - $synMemberCount[$j];
				if( $currentSynSizeLeft >= $playerSpace)
				{
					$playerSpace = $currentSynSizeLeft;
				}		
			}
		}
		
		/*
		
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo 	"<tr>";
		
		foreach($syns as $j => $currentSyn)
		{
			echo "<td>";
			echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
			
			
			$k_max = count($currentSyn);
			foreach($currentSyn as $k => $k_v)
			{
				if( $currentSyn[$k] == $i)
				{
					echo "<tr style=\"background-color:#77ff77;\">";
				}
				else
				{
					echo "<tr style=\"background-color:#7777ff;\">";
				}
				echo	"<td>";
				
				for($l=0;$l < $gruppenMemberCount[$k_v]; $l++)
				{
					echo		"XXX<br>";
				}
				
				echo	"</td>";
				echo "<tr>";
			}			
			echo	"</table>";
			echo "</td>";
		}
				
		echo	"</tr>";
		echo "</table>";
		
		echo "<hr>";
		
		*/
		
	}
	
	echo "<hr><hr>Checking Syns after group assembly<hr><hr>";
	
	echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\">";
	echo 	"<tr>";
	
	foreach($syns as $j => $currentSyn)
	{
		echo "<td>";
		echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
		
		foreach($currentSyn as $k => $k_v)
		{
			echo "<tr style=\"background-color:#7777ff;\">";
			echo	"<td>";
			
			$l_max = count($gruppenMember[$k_v]);
			for($l=0;$l < $l_max; $l++)
			{
				echo		"XXX<br>";
			}
			
			echo	"</td>";
			echo "<tr>";
		}			
		echo	"</table>";
		echo "</td>";
	}
			
	echo	"</tr>";
	echo "</table>";
	
	$syns_noGroups = array();
	$groupsToSyn = array();
	
	foreach($syns as $i => $currentSyns)
	{
		foreach($currentSyns as $j => $groupInSyn)
		{
			$groups_to_syn[$groupInSyn] = $i;
			foreach($gruppenMember[$groupInSyn] as $k => $memberInGroup)
			{
				$syns_noGroups[$i][] = $memberInGroup;
			}
		}
	}

	echo "<hr><hr>Starting to insert randoms<hr><hr>";
	
	foreach($synAliveData_processed as $id => $v)
	{
		$currentMax = 0;
		if($konzerneProcessed < $absBreakpoint)
		{
			$currentMax = MAX_USERS_A_GROUP;
		}
		else
		{
			$currentMax = MAX_USERS_A_SYNDICATE;
		}
		
		$inserted = false;
		
		foreach($syns_noGroups as $i => $i_v)
		{
			if( count($i_v) < $currentMax)
			{
				$syns_noGroups[$i][] = $id;
				$inserted = true;
				break;
			}
		}
		
		if( !inserted )
		{
			$syns_noGroups[count($syns_noGroups)][] = $id;
		}
		
		/*
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo 	"<tr>";
		
		foreach($syns_noGroups as $j => $currentSyn)
		{
			echo "<td>";
			echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
			
			
			foreach($currentSyn as $k => $k_v)
			{
				if( $currentSyn[$k] == $id)
				{
					echo "<tr style=\"background-color:#77ff77;\">";
				}
				else
				{
					echo "<tr style=\"background-color:#7777ff;\">";
				}
				echo	"<td>";
				echo		"XXX<br>";
				echo	"</td>";
				echo "<tr>";
			}			
			echo	"</table>";
			echo "</td>";
		}
				
		echo	"</tr>";
		echo "</table>";
		
		echo "<hr>";
		
		*/
	}
	
	$allPlayerCount = 0;
	
	echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	echo 	"<tr>";
	
	foreach($syns_noGroups as $j => $currentSyn)
	{
		echo "<td>";
		echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
		
		
		foreach($currentSyn as $k => $k_v)
		{
			echo "<tr style=\"background-color:#7777ff;\">";
			echo	"<td>";
			echo		"XXX<br>";
			$allPlayerCount++;
			echo	"</td>";
			echo "<tr>";
		}			
		echo	"</table>";
		echo "</td>";
	}
			
	echo	"</tr>";
	echo "</table>";
	
	echo "Player Total: " . $allPlayerCount;
	

	
	// Query for updating rids of konz in status
	foreach($syns_noGroups as $synId => $currentSyn)
	{
		$queryString = "UPDATE status SET rid = " . $synId . ", lastlogintime=".$globals['roundstarttime']." WHERE id IN(";
		foreach($currentSyn as $j => $member )
		{
			$queryString .= $member . ",";
		}
		chopp($queryString);
		$queryString .= ")";
		$queries[] = $queryString;
	}
	
	// Query for updating rids of konz in status
	foreach($syns_noGroups as $synId => $currentSyn)
	{
		$queryString = "UPDATE " . $globals[statstable] . " SET rid = " . $synId . ", lastlogintime=".$globals['roundstarttime']." WHERE id IN(";
		foreach($currentSyn as $j => $member )
		{
			$queryString .= $member . ",";
		}
		chopp($queryString);
		$queryString .= ")";
		$queries[] = $queryString;
	}
	
	foreach($groups_to_syn as $groupId => $synId)
	{
		$queries[] = "update groups set rid=" . $synId . " where group_id=" . $groupId . ";";
	}
	
	$queries[] = "delete from groups where rid=0;";
	
	print_r($queries);

}

function createTestGroups()
{
	define(NEWGROUPPERCENT,0.19);
	
	select("DELETE FROM groups");
	
	$allKonzerne = assocs("SELECT id FROM status WHERE alive > 0");
	
	/*
	$allKonzerne = array();
	for( $i = 0; $i < 1000; $i++)
	{
		$allKonzerne[] = 5;
	}
	*/
	
	$gruppen = array();
	$gruppenMemberCount = array();
	
	$i_max = count($allKonzerne);
	
	for($i = 0; $i < $i_max; $i++)
	{
		$min = 0;
		$max = 100;
		
		$result = rand($min, $max);
		
		if( $result <= ( $max * NEWGROUPPERCENT) || count($gruppen) == 0 )
		{
			// create new group
			$gruppen[][]= $allKonzerne[$i]['id'];
			$gruppenMemberCount[] = 1;
		}
		else
		{
			//join existing group
			$min = 1;
			$max = count($gruppen);
			
			while(true)
			{
				$result = rand($min,$max);
				if( $gruppenMemberCount[$result] < MAX_USERS_A_GROUP )
				{
					$gruppen[$result][] = $allKonzerne[$i]['id'];
					$gruppenMemberCount[$result]++;
					break;
				}
				
			}			
		}
	}
	
	$i_max = count($gruppenMemberCount);
	
	$num1 = 0;
	$num2 = 0;
	$num3 = 0;
	$num4 = 0;
	$num5 = 0;
	$num6 = 0;
	$num7 = 0;
	$num8 = 0;
	$unknown = 0;
	
	$time = time();
	
	
	
	$newGroupQuery = "INSERT INTO groups (createtime,u1,u2,u3,u4,u5,u6,u7,u8) VALUES ";
	
	for($i = 0; $i < $i_max; $i++)
	{
		
		$newGroupQuery .= "(" . $time . ", ";
		
		for($j = 1; $j <= MAX_USERS_A_GROUP; $j++)
		{
			if( $j <= $gruppenMemberCount[$i])
			{
				$newGroupQuery .= $gruppen[$i][$j-1];
			}
			else
			{
				$newGroupQuery .= "0";
			}
			
			if( $j != MAX_USERS_A_GROUP )
			{
				$newGroupQuery .= ", ";
			}
		}
		
		$newGroupQuery .= ")";
		
		if( $i != $i_max - 1 )
		{
			$newGroupQuery .= ", ";
		}
		
		switch($gruppenMemberCount[$i])
		{
			case 1: $num1++; break;
			case 2: $num2++; break;
			case 3: $num3++; break;
			case 4: $num4++; break;
			case 5: $num5++; break;
			case 6: $num6++; break;
			case 7: $num7++; break;
			case 8: $num8++; break;
			default: $unknown++;break;
		}
	}
	
	echo "Query: " . $newGroupQuery . "\n\n";
	
	echo "1: " . $num1 . "\n"; 
	echo "2: " . $num2 . "\n";
	echo "3: " . $num3 . "\n";
	echo "4: " . $num4 . "\n";
	echo "5: " . $num5 . "\n";
	echo "6: " . $num6 . "\n";
	echo "7: " . $num7 . "\n";
	echo "8: " . $num8 . "\n";
	echo "unknown: " . $unknown . "\n";
	
	select($newGroupQuery);
	
	//print_r($gruppenMemberCount);
}

function is_mentorprogram($id = false){
	global $status, $globals;
	
	if(!$id){
		$id = $status["id"];
	}
	
	if(single("select mentorsystem from users where konzernid = ".$id) == 1){
		return false;
	}
	
	$startround = single("select startround from users where konzernid = ".$id);
	if(($globals["round"] - $startround <= MENTOR_ROUNDS) && $status["is_mentor"]){
		return 1;
	}
	else if(($globals["round"] - $startround <= MENTOR_ROUNDS) || $status["is_mentor"]){
		return 2;
	}
	return false;
}


function fos_duration($lvl) {
	return constant("DUR_FOS_LVL".$lvl);
}

function getUnprotectBonus($status){
    return (floor(($status['createtime'] + PROTECTIONTIME - $status['unprotecttime'])/3600)*UNPROTECT_BONUS);
}

// Wird f?r den Partnerschaftsbonus als auch den Bonus bei sp?terem Rundenstart ben?tigt.
function beschleunige_forschung($forschungsboost, $testflag = 0) {
	$ausnahmen_fuer_nicht_sofort_fertig = array("ind16", "glo12", "ind15", "ind14", "glo11");
	global $id, $time, $sciences, $status, $queries;
	static $science_in_build;
	
	if (!$science_in_build) {
		$science_in_build = assoc("select * from build_sciences where user_id = $id");
	}
	
	// Testflag zum Nachsehen, ob das Beschleunigen geht (d.h. wird überhaupt geforscht und wenn ja ist die Zeit zur Fertigstellung > 0?)
	if ($testflag) {
		if (!$science_in_build) return false;
		if ($science_in_build['time'] - $time <= 0) return false;
		if (in_array($science_in_build['name'], $ausnahmen_fuer_nicht_sofort_fertig) && $science_in_build['time'] - $time <= 3600) return false;
		return true;
	}
	
	if (!$testflag) {
		$science_in_build['time'] -= $forschungsboost * 3600;
	
		// Bestimmte Forschungen erfordern eigenen Code bei Fertigstellung im Update, dies sind die Forschungen aus dem Array $ausnahmen_fuer_...
		if ($science_in_build['time'] <= $time && !in_array($science_in_build['name'], $ausnahmen_fuer_nicht_sofort_fertig)) {
			if (!$sciences[$science_in_build['name']]) {
					$queries[] = "insert into usersciences (user_id, name, level) values ($id, '".$science_in_build['name']."', 1)";
			}
			else {
				$queries[] = "update usersciences set level = level + 1 where name = '".$science_in_build['name']."' and user_id=$status[id]";
			}
			s("Die aktuelle Forschung wurde fertig gestellt.");
			$queries[] = "delete from build_sciences where user_id = $id";
		} else {
			s("Die aktuelle Forschung wurde um ".$forschungsboost."h beschleunigt.");
			if ($science_in_build['time'] <= $time && in_array($science_in_build['name'], $ausnahmen_fuer_nicht_sofort_fertig)) {
				$science_in_build['time'] = get_hour_time($time)+3600; # MindestForschugnszeit f?r die Ausnahmeforschungen 1h.
			}
			$queries[] = "update build_sciences set time = ".$science_in_build['time']." where user_id = $id";
		}
		return 1;
	}
}

// addiert oder subtrahiert Aktien vom Guthaben des Spielers
function update_aktien($what, $id, $menge, $synd, $price = 0){
	if(($what == 'add' || $what == 'sub') && is_numeric($menge) && select("SELECT alive FROM status WHERE id='".$id."'") && select("SELECT synd_id FROM syndikate WHERE synd_id='".$synd."'")){
		$vorhanden = single("SELECT sum(number) FROM aktien WHERE user_id = '".$id."' AND synd_id = '".$synd."'");
		$invested = single("SELECT sum(invested) FROM aktien WHERE user_id = '".$id."' AND synd_id = '".$synd."'");
		$numscols = single("SELECT count(*) FROM aktien WHERE user_id = '".$id."' AND synd_id = '".$synd."'");
		//Dark-john aktien fix 31.05.2012
		$price_one = get_aktien_invprice($id, $synd);
		if(!$numscols && $what == 'add'){
			select("INSERT INTO aktien (user_id, synd_id, number, invested) VALUES ('".$id."', '".$synd."', '".$menge."', '".($menge * $price)."')");
			return true;
		}
		
		switch($what){
			case 'add':
				$vorhanden += $menge;
				$invested += floor($menge * $price);
				break;
			case 'sub':
				$vorhanden -= $menge;
				//Dark-john aktien fix 31.05.2012
				$invested -= floor($menge * $price_one);
				break;
		}
		
		if($vorhanden <= 0){
			select("DELETE FROM aktien WHERE user_id = '".$id."' AND synd_id = '".$synd."'");
				return true;
		}
		elseif($vorhanden > 0){
			select("UPDATE aktien SET number = '".$vorhanden."', invested = '".$invested."' WHERE user_id = '".$id."' AND synd_id = '".$synd."' limit 1");
			return true;
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function num_aktien($rid, $type = false){
	$user = single('select sum(number) from aktien WHERE synd_id = '.$rid);
	$makler = single('select sum(number) from aktien_gebote where user_id = 0 and action = \'offer\' and rid = '.$rid);
	switch ($type){
		case 1:
			return $user;
		case 2:
			return $makler;
		default:
			return $user + $makler;
	}
}

// Gibt das Geld zurück, welches man für Syn X an Geld in Aktien ausgegeben hat - R4bbiT - 23.03.12
function get_aktien_invested($kid, $rid){
	$val = single('select invested from aktien where synd_id = '.$rid.' and user_id = '.$kid);
	return ($val ? $val : 0);
}

// Gibt den Preis für 1 Aktie zurück, den man bisher im Schnitt ausgegeben hat - R4bbiT - 23.03.12
function get_aktien_invprice($kid, $rid){
	$invested = get_aktien_invested($kid, $rid);
	$val = single('select number from aktien where synd_id = '.$rid.' and user_id = '.$kid);
	if($val > 0){
		return ceil($invested / $val);
	}
	return 0;
}

// Berechnet die Steuer, die an alle Syns als Divi, bei einer Transaktion, überwiesen wird - R4bbiT - 23.03.12
// Ausgabe sind die echten Einnahmen, die der Spieler bekommt
function pay_aktien_tax($kid, $rid, $tmp_num, $price){
	global $queries;
	$einnahmen = $tmp_num * $price;
	$price_one = get_aktien_invprice($kid, $rid);
	if($price_one < $price && $price_one > 0){
		$steuern = ceil(($price - $price_one) * STEUERN_AKTIEN);
		$einnahmen = $tmp_num * ($price - $steuern);
		$tax = $tmp_num * $steuern;
		
		$syns = singles('select synd_id from syndikate');
		$num_syns = count($syns);
		
		$pro_syn = ceil($tax / $num_syns);
		foreach($syns as $v){
			dividenden($v, $pro_syn, 'money');
		}
		db_write($queries);
	}
	return floor($einnahmen);
}

// Um Statusmeldungen an Twitter zu schicken - R4bbiT - 28.09.10
function tweet_string($meldung, $type = true){
	// $twitter muss vorher initialisiert werden! (in lib/twitter.php)
	global $twitter, $game;
	if($type && $game[name] == "Syndicates Testumgebung"){
		return true;
	}
	if(!$twitter){
		return false;
	}
	$meldung = substr($meldung, 0, 140);
	$meldung = utf8_encode($meldung);
	$twitter->post('statuses/update', array('status' => $meldung));
	return true;
}

// Um Meldungen an die Pinnwand der Facebook-App zu schicken
function tweet_facebook($msg, $link = false){
	// $facebook muss vorher initialisiert werden! (in lib/facebook.php)
	global $facebook;
	//	msg = Normaler Text
	//	link - url = Die URL vom Link, welcher gepostet werden soll
	//		 - name = Der Name des Links, der anstatt der URL erscheinend soll
	if($facebook){
		$post = array();
		//pvar($msg);
		$post['message'] = utf8_encode($msg);
		if($link['url']){
			$post['link'] = $link['url'];
			if($link['name']){
				$post['name'] = $link['name'];
			}
		}
		$s = $facebook->api('/'.FACEBOOK_APP_ID.'/feed', 'post', $post);
	}
	return false;
}

// Twittert, je nach Option, ne passende Meldung
function tweet($op, $vars){
	$msg = false;
	$str = '';
	switch($op){
		case 'monu_start':
			# 	s_name	= Syndikatsname
			#	s_rid 	= Syndikatesnummer
			#	a_name	= Name des Monumentes
			$msgt = 'Das Syndikat "'.$vars['s_name'].'" (#'.$vars['s_rid'].') hat mit dem Bau des Monumentes "'.$vars['a_name'].'" begonnen';
			$msgf = 'Das Syndikat "'.$vars['s_name'].'" (#'.$vars['s_rid'].') hat mit dem Bau des Monumentes "'.$vars['a_name'].'" begonnen';
			break;
		case 'monu_finish':
			# 	s_name	= Syndikatsname
			#	s_rid 	= Syndikatesnummer
			#	a_name	= Name des Monumentes
			$msgt = 'Das Syndikat "'.$vars['s_name'].'" (#'.$vars['s_rid'].') hat den Bau des Monumentes "'.$vars['a_name'].'" fertiggestellt';
			$msgf = 'Das Syndikat "'.$vars['s_name'].'" (#'.$vars['s_rid'].') hat den Bau des Monumentes "'.$vars['a_name'].'" fertiggestellt';
			break;
		case 'krieg_start':
			#	a_rid	= Array mit den Synnummern der Angreifer
			#	d_rid	= Array mit den Synnummern des Opfers
			$msgt = ($vars['a_rid'][1] ? 'Die Syndikate' : 'Das Syndikat').' #'.$vars['a_rid'][0].($vars['a_rid'][1] ? ($vars['a_rid'][2] ? ',' : ' und ').' #'.$vars['a_rid'][1] : '').($vars['a_rid'][2] ? ' und #'.$vars['a_rid'][2] : '').' '.($vars['a_rid'][1] ? 'ziehen gegen ' : 'zieht gegen').' #'.$vars['d_rid'][0].($vars['d_rid'][1] ? ($vars['d_rid'][2] ? ',' : ' und ').' #'.$vars['d_rid'][1] : '').($vars['d_rid'][2] ? ' und #'.$vars['d_rid'][2] : '').' in den Krieg';
			if($vars['a_rid'][1]){
				$allyname = single('SELECT name FROM allianzen WHERE '.$vars['a_rid'][0].' in (first, second, third)');
				$str .= 'Die Allianz "'.$allyname.'" (#'.$vars['a_rid'][0].($vars['a_rid'][1] ? ', #'.$vars['a_rid'][1] : '').($vars['a_rid'][2] ? ', #'.$vars['a_rid'][2] : '').')';
			}
			else{
				$str .= 'Das Syndikat "'.getsyndname($vars['a_rid'][0]).'" (#'.$vars['a_rid'][0].')';
			}
			$str .= ' hat soeben ';
			if($vars['d_rid'][1]){
				$allyname = single('SELECT name FROM allianzen WHERE '.$vars['d_rid'][0].' in (first, second, third)');
				$str .= 'der Allianz "'.$allyname.'" (#'.$vars['d_rid'][0].($vars['d_rid'][1] ? ', #'.$vars['d_rid'][1] : '').($vars['d_rid'][2] ? ', #'.$vars['d_rid'][2] : '').')';
			}
			else{
				$str .= 'dem Syndikat "'.getsyndname($vars['d_rid'][0]).'" (#'.$vars['d_rid'][0].')';
			}
			$str .= ' den Krieg erklärt. Der Kriegszustand beginnt in 24 Stunden. Viel Spaß an alle Beteiligten!';
			$msgf = $str;
			//$msgf = ($vars['a_rid'][1] ? 'Die Syndikate' : 'Das Syndikat').' #'.$vars['a_rid'][0].($vars['a_rid'][1] ? ($vars['a_rid'][2] ? ',' : ' und ').' #'.$vars['a_rid'][1] : '').($vars['a_rid'][2] ? ' und #'.$vars['a_rid'][2] : '').' '.($vars['a_rid'][1] ? 'ziehen gegen ' : 'zieht gegen').' #'.$vars['d_rid'][0].($vars['d_rid'][1] ? ($vars['d_rid'][2] ? ',' : ' und ').' #'.$vars['d_rid'][1] : '').($vars['d_rid'][2] ? ' und #'.$vars['d_rid'][2] : '').' in den Krieg';
			break;
		case 'round_start':
			#	round	= Die Nummer der Runde die startet
			$msgt = 'Soeben hat die '.$vars['round'].'. Syndicatesrunde begonnen. Viel Erfolg an alle, die dabei sind.';
			$msgf = "In Syndicates hat soeben die ".$vars['round'].". Runde gestartet. Viel Erfolg an alle, die dabei sind.\nDu hast den Start verpasst? Dann schnell anmelden, sonst ist die Konkurrenz eher am Ziel!";
			break;
		default:
			return false;
	}
	if($msgt){
		// Message an Twitter
		tweet_string($msgt);
	}
	if($msgf){
		// Message an Facebook
		tweet_facebook($msgf);
	}
	return true;
}

// gibts die Anzahl der Aktien zurück, die man noch kaufen kann
function aktien_buyable($id){
	$sciences = getsciences($id);
	$partner = getpartner($id);
	$status = assoc("select *  from status where id=$id");
	$makler = single('SELECT sum(number) FROM aktien_gebote WHERE user_id = 0 AND action = \'offer\'');
	$umlauf = single('SELECT sum(number) FROM aktien');
	$gesamt = $makler + $umlauf;
	$own = single('SELECT sum(number) FROM aktien WHERE user_id = '.$id);
	$rest =/* min(round($gesamt * 0.02) - $own, */ $status[land]*(AKTIEN_KAPA_HA+$sciences[glo7]*AKTIEN_KAPA_INVEST+$partner[16]*AKTIEN_KAPA_INVEST)-$own;//);

	return $rest;
}

function getsyndname($synd_id)	{
	$additional_data = '';
    if (func_num_args() > 1) {$additional_data = func_get_arg (1);}
	$syndname = row("select name$additional_data from syndikate where synd_id='$synd_id'");
	if ($additional_data): return $syndname; endif;

	return $syndname[0];
}

function calcWarMoney($synID, $warID, $player, $isWinner){
	
	$playDay = round_days_played();
	
	//Constants
	$WAR_MONEY = array();
	$WAR_MONEY['unitintel1'] = 10000*$playDay;
	$WAR_MONEY['buildintel'] = 10000*$playDay;
	$WAR_MONEY['newsintel'] = 20000*$playDay;
	$WAR_MONEY['scienceintel'] = 20000*$playDay;
	$WAR_MONEY['unitintel2'] = 50000*$playDay;
	$WAR_MONEY['killunits'] = 100000*$playDay;
	$WAR_MONEY['killbuildings'] = 100000*$playDay;
	$WAR_MONEY['killsciences'] = 100000*$playDay;
	$WAR_MONEY['delayaway'] = 100000*$playDay;
	
	$WAR_MONEY['att1'] = 700*$playDay; //Standard
	$WAR_MONEY['att2'] = 700*$playDay; //Belagerung //landgain
	$WAR_MONEY['att3'] = 700*$playDay; //Eroberung
	$WAR_MONEY['att4'] = 70*$playDay; //Spy
	
	$WAR_MONEY['day'] = 500000*$playDay;	
	
	$REPERATION_FACTOR = 0.5;
	$LOSER_FACTOR = 0.5;
	
	$relevant_SPYACTIONS = array("'unitintel1'","'buildintel'","'newsintel'","'scienceintel'","'unitintel2'","'killunits'","'killbuildings'","'killsciences'","'delayaway'");
	$relevant_ATTS = array('1','2','3','4');
	
	$warData = assocs("SELECT * FROM  `wars` WHERE war_id=".$warID);
	$warData = $warData[0];
	
	$participants = singles("SELECT id FROM status WHERE rid=".$synID);
	
	$warStartTime = $warData['starttime'];
	$payFactor =  1.25 - (intval((time()-$warStartTime)/60/60) * 0.01);
	$enemySynIDs = array();
	
	$warMoneyPlayer = array();
	
	$declarerWins = $defenderWins = 0;

	for($i=0; $i<4; $i++){
		
		if($warData['first_synd_'.$i] == $synID)
			for($j=0; $j<4; $j++)
				if($warData['second_synd_'.$j] > 0)
					$enemySynIDs[] = $warData['second_synd_'.$j];
					
		if($warData['second_synd_'.$i] == $synID)
			for($j=0; $j<4; $j++)
				if($warData['first_synd_'.$j] > 0)
					$enemySynIDs[] = $warData['first_synd_'.$j];
					
	}
	
	foreach($enemySynIDs as $enemySynID){
		
		$participantsEnenemy = singles("SELECT id FROM status WHERE rid=".$enemySynID);
		
		//Aktiv
		
		$attackData = assocs("SELECT landgain, type, aid from attacklogs WHERE arid=".$synID." AND drid=".$enemySynID." AND time>=".$warStartTime); // AND warattack=1
		
		foreach($attackData as $attack){
			$warMoneyPlayer[$attack['aid']]+= $attack['landgain'] * $WAR_MONEY['att'.$attack['type']] * $payFactor;
			$warMoneyTotal += $attack['landgain'] * $WAR_MONEY['att'.$attack['type']] * $payFactor;
		}
		
		$spyData = assocs("SELECT count(*) as times, action, aid FROM  `spylogs`  WHERE aid IN (".implode(',', $participants).") AND did IN (".implode(',', $participantsEnenemy).") AND action IN (".implode(',', $relevant_SPYACTIONS).") AND success=1 AND time>=".$warStartTime." GROUP BY aid,action");
		foreach($spyData as $spy){
			$warMoneyPlayer[$spy['aid']]+= $spy['times'] * $WAR_MONEY[$spy['action']] * $payFactor;
			$warMoneyTotal += $spy['times'] * $WAR_MONEY[$spy['action']] * $payFactor;
		}
		
		
		//Passiv
		
			$attackData = assocs("SELECT landgain, type, did from attacklogs WHERE drid=".$synID." AND arid=".$enemySynID." AND time>=".($warStartTime-24*3600)); // AND warattack=1  --- seit R61 geben die aktionen vor kriegsbeginn schon kriegsprämie
		
		foreach($attackData as $attack){
			$warMoneyPlayer[$attack['did']]+= $attack['landgain'] * $WAR_MONEY['att'.$attack['type']] * $REPERATION_FACTOR * $payFactor;
			$warMoneyTotal += $attack['landgain'] * $WAR_MONEY['att'.$attack['type']] * $REPERATION_FACTOR * $payFactor;
		}
		
		$spyData = assocs("SELECT count(*) as times, action,did FROM  `spylogs`  WHERE did IN (".implode(',', $participants).") AND aid IN (".implode(',', $participantsEnenemy).") AND action IN (".implode(',', $relevant_SPYACTIONS).") AND success=1 AND time>=".($warStartTime-24*3600)." GROUP BY did,action");
		foreach($spyData as $spy){
			$warMoneyPlayer[$spy['did']]+= $spy['times'] * $WAR_MONEY[$spy['action']] * $REPERATION_FACTOR * $payFactor;
			$warMoneyTotal += $spy['times'] * $WAR_MONEY[$spy['action']] * $REPERATION_FACTOR * $payFactor;
		}
		
	}
	
	if($isWinner){
		foreach($participants as $participant){
			$warMoneyPlayer[$participant] += $WAR_MONEY['day'] * $payFactor;
			$warMoneyTotal += $WAR_MONEY['day'] * $payFactor;
		}	
	} else {
		$warMoneyTotal = 0;
		foreach($participants as $participant){
			$warMoneyPlayer[$participant] *= $LOSER_FACTOR;
			$warMoneyTotal += $warMoneyPlayer[$participant];
		}
	}
	
	if($player == 'total')
		return $warMoneyTotal;
	elseif($player == 'all')
		return $warMoneyPlayer;
	else
		return $warMoneyPlayer[$player];
	
}

function warCheckAndHandle($warID){

	$resstats = getresstats();

	$declarer = array();
	$offender = array();
	$monuDeclarerHas = array();
	$monuDeclarerWants = array();
	$monuDefenderHas = array();
	$monuDefenderWants = array();
	
	$declarerGotLand=0;
	$defenderGotLand=0;
	
	$monuAll = array();
	$monuLost = array();
	
	$warData = assocs("SELECT * FROM  `wars` WHERE war_id=".$warID);
	$warData = $warData[0];
	
	$warStart = $warData['starttime'];
	
	$bruttoLandSyn = array();
	
	$declarerStartLand = 0;
	$defenderStartLand = 0;
	
	for($i=0; $i<4; $i++){
		
		if($warData['first_synd_'.$i]){
			
			$declarer[] = $warData['first_synd_'.$i];
			$declarerStartLand += $warData['first_'.$i.'_landstart'];
			$members = singles("select id from status where rid=".$warData['first_synd_'.$i]);
			$vancLand = single("SELECT sum(land) from status, options_vacation 
				WHERE 	user_id in (".implode(',',$members).") and 
						user_id=id and activated_by_update=1 and timestamp >=".($warStart-24*60*60));
			$defenderGotLand += $vancLand * WAR_PUNISHMENT + $warData['first_'.$i.'_add'];
			$hisMonu = single("select artefakt_id from syndikate where synd_id=".$warData['first_synd_'.$i]);
			$wantMonu = $warData['artefakt_want_first_'.$i]; 
			if($hisMonu) {$monuDeclarerHas[$hisMonu] = $warData['first_synd_'.$i]; $allMonu[$warData['first_synd_'.$i]] = $hisMonu; }
			if($wantMonu) $monuDeclarerWants[$wantMonu] = $warData['first_synd_'.$i];
		
		}
					
		if($warData['second_synd_'.$i]){
			
			$defender[] = $warData['second_synd_'.$i];
			$defenderStartLand += $warData['second_'.$i.'_landstart'];
			$members = singles("select id from status where rid=".$warData['second_synd_'.$i]);
			$vancLand = single("SELECT sum(land) from status, options_vacation 
				WHERE 	user_id in (".implode(',',$members).") and 
						user_id=id and activated_by_update=1 and starttime >=".($warStart-24*60*60));
			$declarerGotLand += $vancLand * WAR_PUNISHMENT + $warData['second_'.$i.'_add'];
			$hisMonu = single("select artefakt_id from syndikate where synd_id=".$warData['second_synd_'.$i]);
			$wantMonu = $warData['artefakt_want_second_'.$i]; 
			if($hisMonu) {$monuDefenderHas[$hisMonu] = $warData['second_synd_'.$i]; $allMonu[$warData['second_synd_'.$i]] = $hisMonu; }
			if($wantMonu) $monuDefenderWants[$wantMonu] = $warData['second_synd_'.$i];
		}
					
	}
	
	//TODO: warum ist $bruttoLandSyn nur innerhalb der foreach schleife mit werten gefüllt?
	foreach($declarer as $syndID){
		$bruttoLandSyn[$synID] = single("SELECT sum(landgain) from attacklogs WHERE arid=".$syndID." AND drid in (".implode(',',$defender).") AND warattack=1 AND time>=".$warStart." AND type in (1,3)");
		$declarerGotLand += $bruttoLandSyn[$synID];
	}
		
	foreach($defender as $syndID){
		$bruttoLandSyn[$synID] = single("SELECT sum(landgain) from attacklogs WHERE arid=".$syndID." AND drid in (".implode(',',$declarer).") AND warattack=1 AND time>=".$warStart." AND type in (1,3)");
		$defenderGotLand += $bruttoLandSyn[$synID];
	}
	
	
	$declarerStr = implode(', #', $declarer);
	$defenderStr = implode(', #', $defender);
	
	$declarerBrutto = $declarerGotLand;
	$defenderBrutto = $defenderGotLand;
	
	$defenderBruttoRatio = $defenderBrutto / $declarerStartLand;
	$declarerBruttoRatio = $declarerBrutto / $defenderStartLand;
	$declarerNettoRatio = $declarerBruttoRatio - $defenderBruttoRatio;
	$defenderNettoRatio = $defenderBruttoRatio - $declarerBruttoRatio;
	//$declarerNetto = $defenderNettoRatio / $declarerStartLand;
	//$defenderNetto = $declarerNettoRatio / $defenderStartLand;
	
	$warOver = $warData['endtime'];
	$warDuration = $warOver - $warStart;
	
	//echo"wastra: $warStart wardua: $warDuration time:".time();
	
	if(!$warOver){
		$warDuration = time() - $warStart;
		if($declarerBruttoRatio >= 0.2 or $declarerNettoRatio >= 0.1)
			$declarerWins = 1;

		elseif($warDuration >= 48 * 60 * 60 or $defenderBruttoRatio >= 0.16 or $defenderNettoRatio >= 0.08)
			$defenderWins = 1;
		else
			$declarerWins = $defenderWins = 0;
		
		$warIsOverNow = $declarerWins + $defenderWins;
	}
	
	
	if($warIsOverNow){
		
		// Als Angeifer werden Monumente nur verloren, wenn man auch netto weniger als 0% erobert hat
		if ($declarerWins || $declarerNettoRatio < KRIEG_MONU_ZERSTOERUNG_MINDESTPROZENT_LAND_EROBERT) {
			foreach($allMonu as $syndID=>$artID){
				
				$monuHere = $declarerWins ? $monuDeclarerHas[$artID] : $monuDefenderHas[$artID];
				$monuIsWant = $declarerWins ? $monuDeclarerWants[$artID] : $monuDefenderWants[$artID];
				if ($defenderWins && 
					$defenderNettoRatio < KRIEG_MONU_EROBERUNG_MINDESTPROZENT_LAND_EROBERT_NETTO &&
					$defenderBruttoRatio < KRIEG_MONU_EROBERUNG_MINDESTPROZENT_LAND_EROBERT_BRUTTO) {
					$monuIsWant = false;
				}
				
				if(!$monuHere and !$monuIsWant){
					$monuLost[] = $artID;
					select("update syndikate set artefakt_id=0 where artefakt_id=".$artID);
				} elseif(!$monuHere and $monuIsWant){
					$monuLost[] = array($artID => 0);
					$hisMonu = single("select artefakt_id from syndikate where synd_id=".$monuIsWant);
					if($hisMonu) $monuLost[] = array($hisMonu => $monuIsWant);
					select("update syndikate set artefakt_id=0 where artefakt_id=".$artID);
					select("update syndikate set artefakt_id=".$artID." where synd_id=".$monuIsWant);				
				}
			}
		}
	
		$sqlQry = "update wars set status=0,endtime=".time().",ended_by=".($declarerWins ? $declarer[0] : $defender[0])." where war_id=".$warID;
		select($sqlQry);
		
		foreach($declarer as $synID){
			
			$warMoneyData = calcWarMoney($synID, $warID, 'all', $declarerWins);	
			$warMoneyTotal = calcWarMoney($synID, $warID, 'total', $declarerWins);
			
			$credits = ceil($warMoneyTotal * 0.25);
			$energy = ceil($credits / $resstats[energy][value]);
			$erz = ceil($credits / $resstats[metal][value]);
			$fp = ceil($credits / $resstats[sciencepoints][value]);
			select("update syndikate set podmoney=podmoney+".$credits.",podenergy=podenergy+".$energy.",podmetal=podmetal+".$erz.",podsciencepoints=podsciencepoints+".$fp." where synd_id=".$synID);
			
			$numberOfWars = single("select count(*) from wars where (first_synd_1=".$synID." or first_synd_2=".$synID." or first_synd_3=".$synID." or second_synd_1=".$synID." or second_synd_1=".$synID." or second_synd_1=".$synID.") and endtime = 0");
			
			if (!$numberOfWars) {
				select("update syndikate set atwar = 0 where synd_id = ".$synID);
			}
			
			$bruttoLand = $bruttoLandSyn[$synID];
			$currency = single("select currency from syndikate where synd_id = ".$synID);
			$synName = single("select name from syndikate where synd_id = ".$synID);
			
			$monuString2 = "";
			$monuCountLoss = 0;
			
			if($monuLost){
				
				foreach($monuLost as $arteID => $syndID){
			
					if($syndID == $synID){
						$monuString = "<br><br>Ihr Syndikat hat außerdem das Monument <b>".$artefakte[$arteID]['name']."</b> vom Gegner <b>erobert</b>.";
					}
			
					if($syndID == 0){$monuString2 .= $artefakte[$arteID]['name']."<br>"; $monuCountLoss++; }
			
				}
			
			}
			
			$monuString .= $monuString2 ? ($monuCountLoss > 1 ? "Die Monumente ":"Das Monument ")."<b><br>".$monuString2."</b><br> wurde".($monuCountLoss > 1 ? "n":"")." <b>zerstört</b>.":"";
			
			foreach($warMoneyData as $statusID => $toPay){
				
				select("update status set podpoints = podpoints + ".$toPay." where id=".$statusID);
				
				$messagedata=array();
				$messagedata[0] = "";//$synName;
				$messagedata[1] = $defenderStr;
				$messagedata[2] = pointit($declarerBrutto);
				$messagedata[3] = pointit($credits);
				$messagedata[4] = pointit($energy);
				$messagedata[5] = pointit($erz);
				$messagedata[6] = pointit($fp);
				$messagedata[7] = pointit($toPay);
				$messagedata[8] = $currency;
				$messagedata[9] = $monuString; 
				$messagestring = join("|", $messagedata);
				
				select("insert into message_values (id, user_id, time, werte) values ('43', '$statusID', '".time()."', '$messagestring')");
			}	
			
			$gegenStr = $defenderStr;
			
			if($declarerWins)
				$msg_temp = "Der Krieg gegen <b>#".$gegenStr."</b> ist gewonnen, da die Verluste beim Gegner zu gro&szlig; waren.";
			else
				$msg_temp = "Der Krieg gegen <b>#".$gegenStr."</b> ist verloren, da die Verluste auf der eigenen Seite zu gro&szlig; waren.";
				
			select("insert into towncrier (time,rid,message,kategorie) values (".time().",".$synID.",'$msg_temp',2)");
						
		}
		
		foreach($defender as $synID){
			
			$warMoneyData = calcWarMoney($synID, $warID, 'all', $defenderWins);	
			$warMoneyTotal = calcWarMoney($synID, $warID, 'total', $defenderWins);
			
			$credits = ceil($warMoneyTotal * 0.25);
			$energy = ceil($credits / $resstats[energy][value]);
			$erz = ceil($credits / $resstats[metal][value]);
			$fp = ceil($credits / $resstats[sciencepoints][value]);
			
			select("update syndikate set podmoney=podmoney+".$credits.",podenergy=podenergy+".$energy.",podmetal=podmetal+".$erz.",podsciencepoints=podsciencepoints+".$fp." where synd_id=".$synID);
			
			$numberOfWars = single("select count(*) from wars where (first_synd_1=".$synID." or first_synd_2=".$synID." or first_synd_3=".$synID." or second_synd_1=".$synID." or second_synd_1=".$synID." or second_synd_1=".$synID.") and endtime = 0");
			
			if (!$numberOfWars) {
				select("update syndikate set atwar = 0 where synd_id = ".$synID);
			}
			
			$bruttoLand = $bruttoLandSyn[$synID];
			$currency = single("select currency from syndikate where synd_id = ".$synID);
			$synName = single("select name from syndikate where synd_id = ".$synID);
			
			$monuString2 = "";
			$monuCountLoss = 0;
			
			if($monuLost){
				
				foreach($monuLost as $arteID => $syndID){
			
					if($syndID == $synID){
						$monuString = "<br><br>Ihr Syndikat hat außerdem das Monument <b>".$artefakte[$arteID]['name']."</b> vom Gegner <b>erobert</b>.";
					}
			
					if($syndID == 0){$monuString2 .= $artefakte[$arteID]['name']."<br>"; $monuCountLoss++; }
			
				}
			
			}
			
			$monuString .= $monuString2 ? ($monuCountLoss > 1 ? "Die Monumente ":"Das Monument ")."<b><br>".$monuString2."</b><br> wurde".($monuCountLoss > 1 ? "n":"")." <b>zerstört</b>.":"";
			
			foreach($warMoneyData as $statusID => $toPay){
				
				select("update status set podpoints = podpoints + ".$toPay." where id=".$statusID);
				
				$messagedata=array();
				$messagedata[0] = "";//$synName;
				$messagedata[1] = $declarerStr;
				$messagedata[2] = pointit($defenderBrutto);
				$messagedata[3] = pointit($credits);
				$messagedata[4] = pointit($energy);
				$messagedata[5] = pointit($erz);
				$messagedata[6] = pointit($fp);
				$messagedata[7] = pointit($toPay);
				$messagedata[8] = $currency;
				$messagedata[9] = $monuString; 
				$messagestring = join("|", $messagedata);
				
				select("insert into message_values (id, user_id, time, werte) values ('43', '$statusID', '".time()."', '$messagestring')");
			}
			
			$gegenStr = $declarerStr;
			
			if($defenderWins)
				$msg_temp = "Der Krieg gegen <b>#".$gegenStr."</b> ist gewonnen, da die Verluste beim Gegner zu gro&szlig; waren.";
			else
				$msg_temp = "Der Krieg gegen <b>#".$gegenStr."</b> ist verloren, da die Verluste auf der eigenen Seite zu gro&szlig; waren.";
				
			select("insert into towncrier (time,rid,message,kategorie) values (".time().",".$synID.",'$msg_temp',2)");
		}
		
		//check if any allies are pending
		$allys_pending = assocs('SELECT * FROM ally_pending WHERE syn1 in ('.implode(', ', $defender).','.implode(', ', $declarer).') or syn2 in('.implode(', ', $defender).','.implode(', ', $declarer).')');
		if(count($allys_pending) > 0) {
			foreach($allys_pending as $vl) {
				if(!single("SELECT count(*) FROM syndikate where atwar = 1 and synd_id in ($first,$second)")) {
					$first = $vl['syn1'];
					$second = $vl['syn2'];
					$time = time();
					$syn_names = assocs("SELECT name, synd_id FROM syndikate WHERE synd_id in ($first, $second)", "synd_id");
					select("insert into allianzen (first, second) values ($first,$second)");
					$allianz_id = single("select allianz_id from allianzen where first=$first and second=$second");
					select("update syndikate set allianzanfrage=0, ally1=$second, allianz_id=$allianz_id where synd_id=$first");
					select("update syndikate set allianzanfrage=0, ally1=$first, allianz_id=$allianz_id where synd_id=$second");
					select("update allianzen_anfragen set 1s=1, endtime=$time where anfragen_id=".$vl['anfragen_id']);
					select("insert into towncrier (time, rid, message,kategorie) values ($time, '".$second."','Die Allianz mit dem Syndikat <strong>".$syn_names[$first]['name']." (#$first)</strong> ist nun offiziell, da der Krieg beendet ist.', 2), ($time, '".$first."','Die Allianz mit dem Syndikat <strong>$syn_names[$second]['name'] (#$second)</strong> ist nun offiziell, da der Krieg beendet ist.', 2)");
					select("DELETE FROM ally_pending WHERE syn1=$first and syn2=$second");
				}
			}
		}
		
		$result = array();
		
		$result['winner'] = $declarerWins ? 'declarer' : 'defender';
		$result['ended'] = 1;
		
	} else {
		
		$result = array();
		
		$result['land'] = $bruttoLandSyn;
		$result['defenderBrutto'] = $defenderBrutto;
		$result['defenderBruttoRatio'] = $defenderBruttoRatio;
		$result['declarerBrutto'] = $declarerBrutto;
		$result['declarerBruttoRatio'] = $declarerBruttoRatio;
		//$result['defenderNetto'] = $defenderNetto;
		$result['defenderNettoRatio'] = $defenderNettoRatio;
		//$result['declarerNetto'] = $declarerNetto;
		$result['declarerNettoRatio'] = $declarerNettoRatio;
		$result['declarerStartLand'] = $declarerStartLand;
		$result['defenderStartLand'] = $defenderStartLand;
		$result['declarer'] = $declarer;
		$result['defender'] = $defender;
						
	}
	
	//pvar($warMoneyData);
	//echo "   <br>  $warMoneyTotal";
	
	return $result;
	
}

function inwar($myrid,$targetID){
		if ($targetID=='') return 0;
	$hisrid=single("select rid from status where id=$targetID");
	$wars=single("select count(*) from wars where ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid)) and starttime<=".(time()+24*60*60)." and endtime=0");
	$wars1=single("select count(*) from wars where (((first_synd_1=$myrid or first_synd_2=$myrid or first_synd_3=$myrid) and (second_synd_1=$hisrid or second_synd_2=$hisrid or second_synd_3=$hisrid)) or ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid))) and starttime<=".time()." and endtime=0");
	return $wars + $wars1;
}

//Dark-john
function inwarpassiv($myrid,$targetID){
		if ($targetID=='') return 0;
	$hisrid=single("select rid from status where id=$targetID");
	$wars=single("select count(*) from wars where ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid)) and starttime<=".(time()+24*60*60)." and endtime=0");
	//$wars1=single("select count(*) from wars where (((first_synd_1=$myrid or first_synd_2=$myrid or first_synd_3=$myrid) and (second_synd_1=$hisrid or second_synd_2=$hisrid or second_synd_3=$hisrid)) or ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid))) and starttime<=".time()." and endtime=0");
	return $wars;
}

//dark-john
function inwaractive($myrid,$targetID){
		if ($targetID=='') return 0;
	$hisrid=single("select rid from status where id=$targetID");
	//$wars=single("select count(*) from wars where ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid)) and starttime<=".(time()+24*60*60)." and endtime=0");
	$wars1=single("select count(*) from wars where (((first_synd_1=$myrid or first_synd_2=$myrid or first_synd_3=$myrid) and (second_synd_1=$hisrid or second_synd_2=$hisrid or second_synd_3=$hisrid)) or ((second_synd_1=$myrid or second_synd_2=$myrid or second_synd_3=$myrid) and (first_synd_1=$hisrid or first_synd_2=$hisrid or first_synd_3=$hisrid))) and starttime<=".time()." and endtime=0");
	return $wars1;
}


function is_mobile(){
	$r = 0;
	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
		$r = 1;
	}
	if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
		$r = 1;
	}    
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
	$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda ','xda-');
	
	if (in_array($mobile_ua,$mobile_agents)) {
		$r = 1;
	}
	if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {
		$r = 1;
	}
	if(preg_match('/(^m\.|^www\.m\.)/', $_SERVER['HTTP_HOST'])){
		$r = 1;
	}
	if($r > 0 && $_COOKIE['deaktivate_mobile']){
		return -1;
	}
	return $r;
}

// CHECK_VALIDITY

function check_validity($what, $type, $killtime)	{

global $status;

	if ($what == "geb")	{	# Wenn Gebäude getötet werden sollen
	global $gebnames;
	$gebnames = assocs("select name, name_intern, building_id from buildings","name");
	$gebnames[Land] = array( name => "Land", name_intern => "land", building_id => 127);

		foreach ($gebnames as $ky => $vl)	{ if ($ky == $type)	{	$proceed = 1; break;}; 	};	# Wenn der "Type" gültig ist;

						}

	elseif ($what == "sa")	{	# Wenn S pion A usbildung belangt wird
	global $spynames;
	$spynames = assocs("select name, unit_id from spy_settings where race='$status[race]' or race='all'","unit_id");
		foreach ($spynames as $ky => $vl)	{ if ($ky == $type)	{	$proceed = 1; break;}; 	};	# Wenn der "Type" gültig ist;

							}

	elseif ($what == "ma" or $what == "hm")	{
	global $milnames;
	$milnames = assocs("select name, unit_id from military_unit_settings where race='$status[race]' or race='all' or race='dummy'", "unit_id");
		foreach ($milnames as $ky => $vl)	{ if ($ky == $type)	{	$proceed = 1; break;}; 	};	# Wenn der "Type" gültig ist;

											}

	elseif ($what == "sc")	{
		if (preg_match("/^mil\d{1,2}$/",$type) or preg_match("/^glo\d{1,2}$/",$type) or preg_match("/^ind\d{1,2}$/",$type))	{
			$proceed = 1; preg_match("/(\w{3})(\d{1,2})/", $type, $reg_findings);
			global $forname;
			$forname = row("select gamename from sciences where name='$reg_findings[1]' and typenumber='$reg_findings[2]';");
			$forname = $forname[0];
		};
							}

	# Jetzt wird die Übergabe der Killtime überprüft;

	if ($proceed)		{ $proceed = 0;	# Variable kann wiederverwertet werden;
			foreach (range(1,20) as $vl)	{ if ($vl == $killtime)	{ $proceed = 1; break;}	};			# Wenn die zu tötende Stunde valid ist;
						}

return $proceed;

}


// GET_PROPRIATE_ACTION

function get_propriate_action($what,$type,$inner)	{

global $searchtime; 
global $remain;
global $id;
global $gebnames;
global $n,$time,$sciences;
//if (strtolower($type) == "land" && $inner != "select number") return "select 1";


if ($what == "geb")	{
	if ($inner == "select number")	{
 		return "select sum(number) from build_buildings where user_id='$id' and building_name='".$gebnames[$type][name_intern]."' and time=$searchtime";
									}
	elseif ($inner == "delete from"){

		return "delete from build_buildings where user_id='$id' and building_name='".$gebnames[$type][name_intern]."' and time=$searchtime";
									}
	elseif ($inner == "insert into"){
		return "insert into build_buildings (building_name, user_id, number, time, building_id) values ('".$gebnames[$type][name_intern]."', $id, $remain, $searchtime,'".$gebnames[$type][building_id]."');";
									}
	elseif ($inner == "log"){
		return "insert into build_logs (subject_id, user_id, number, time, action,what) values ('".$gebnames[$type][building_id]."', $id, $n, $time,2,'building');";
	}
					}

elseif ($what == "sa")	{
	 if ($inner == "select number")	{
		  return "select sum(number) from build_spies where user_id='$id' and unit_id='$type' and time=$searchtime";
									}
	elseif ($inner == "delete from"){
		  return "delete from build_spies where user_id='$id' and unit_id='$type' and time=$searchtime";
									}
	elseif ($inner == "insert into"){
		  return "insert into build_spies (unit_id, user_id, number, time) values ('$type', $id, $remain, $searchtime);";
									}
	elseif ($inner == "log"){
		return "insert into build_logs (subject_id, user_id, number, time, action,what) values
		 ('".$type."', $id, $n, $time,2,'spy');";
	}
						}

elseif ($what == "ma")	{
	$alttype=single("select type from military_unit_settings where unit_id='$type'");
	$altid=single("select unit_id from military_unit_settings where type='$alttype' and race='dummy'");
	if ($inner == "select number")	{
		  return "select sum(number) from build_military where user_id='$id' and (unit_id='$type' or unit_id='$altid') and time=$searchtime";
									}
	elseif ($inner == "delete from")	{
		return "delete from build_military where user_id='$id' and (unit_id='$type' or unit_id='$altid') and time=$searchtime";
										}
	elseif ($inner == "insert into")	{
		return "insert into build_military (unit_id, user_id, number, time) values ('$type', $id, $remain, $searchtime)";
										}
	elseif ($inner == "log"){
		return "insert into build_logs (subject_id, user_id, number, time, action,what) values ('".$type."', $id, $n, $time,2,'mil');";
	}
						}

elseif ($what == "hm")	{
	if ($inner == "select number")	{
		return "select sum(number) from military_away where user_id='$id' and unit_id='$type' and time=$searchtime";
									}
	elseif ($inner == "delete from")	{
		return "delete from military_away where user_id='$id' and unit_id='$type' and time=$searchtime";
										}
	elseif ($inner == "insert into")	{
		return "insert into military_away (unit_id, user_id, number, time) values ('$type', $id, $remain, $searchtime);";
										}
	elseif ($inner == "log"){
		return "insert into build_logs (subject_id, user_id, number, time, action,what) values ('".$type."', $id, $n, $time,2,'mil');";
	}
						}

elseif ($what == "sc")	{
	if ($inner == "select number")	{
		return "select count(*) from build_sciences where user_id='$id' and name='$type'";
									}
	elseif ($inner == "delete from")	{
		return "delete from build_sciences where user_id='$id' and name='$type'";
										}
	elseif ($inner == "log"){
		$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, description, gamename, sciencecosts,id from sciences where available=1", "name");	//der science Table
		return "insert into build_logs (subject_id, user_id, number, time, action,what)
		values ('$sciencestats[$ype][id]', $id, ".($sciences[$type]+1).", $time,2,'sci');";
	}
						}


}

// PRINT_KILL_OUTPUT

function print_kill_output($what,$type,$killtime)	{

	global $total;
	global $ausgabe;
	global $spynames;
	global $milnames;
	global $forname;
	global $userinput;
	global $thissite;
	global $status;
	
	$percent_shredder = min(ceil($status[land]/LAND_SHREDDER_PER_PERCENT_ADD_HA), LAND_SHREDDER_PER_PERCENT_MAX_HA/LAND_SHREDDER_PER_PERCENT_ADD_HA);

	if ($what == "geb" and $type != "Land"): $ersetze1 = "Gebäudebau"; endif;
	if ($what == "geb" and $type == "Land"): $ersetze1 = "Landerwerb"; endif;
	if ($what == "sa"): $ersetze1 = "Spionausbildung"; endif;
	if ($what == "ma"): $ersetze1 = "Militärausbildung"; endif;
	if ($what == "hm"): $ersetze1 = "Heimkehr"; endif;

	if ($what == "geb"): $ersetze2 = "Erwartete Fertigstellung"; endif;
	if ($what == "sa" or $what == "ma"): $ersetze2 = "Erwartetes Ausbildungsende"; endif;
	if ($what == "hm"): $ersetze2 = "Erwartete Heimkehr"; endif;

	if ($what == "geb"): $name = $type; endif;
	if ($what == "sa"): $name = $spynames[$type][name]; endif;
	if ($what == "ma" or $what == "hm"): $name = $milnames[$type][name]; endif;

	if ($what != "sc")	{
		$userinput = "
			<br><br><center><table cellspacing=1 border=0 cellpadding=3 class=\"tableOutline\"><form action=$thissite.php method=post><tr><td class=\"tableHead\" align=center><b>$ersetze1 abbrechen</b></td></tr>
			<tr><td class=\"tableInner1\" align=center>
			$ersetze2 in $killtime Stunden:<br><br>
			<input type=hidden name=ia value=killqu>
			<input type=hidden name=innestaction value=next>
			<input type=hidden name=what value=\"$what\">
			<input type=hidden name=type value=\"$type\">
			<input type=hidden name=killtime value=\"$killtime\">
			<table border=0 cellpadding=0 cellspacing=0>
			<tr><td><input class=\"input\" type=text name=n size=4 value=$total maxlength=20></td><td class=\"tableInner1\">$name</td></tr>
			<tr><td colspan=2 align=center>&nbsp;<br><input class=\"button\" type=submit value=\"$ersetze1 stoppen\"> <input class=\"button\" type=button onClick=\"javascript:history.back();\" value=\"zurück\"></td></tr>
			</table><br>
			<br><b>Achtung!</b><br>Die jeweiligen Einheiten oder Gebäude werden ohne Kostenausgleich unwiederbringlich zerstört.<br>Es erfolgt keine Gutschrift für bereits bezahlte Produktionskosten (Ausnahme Land - hier werden ".($percent_shredder)."% des aktuellen Kaufpreises (max. ".LAND_SHREDDER_PER_MAX_HA." Cr/Ha) zurückerstattet)!
			</td></tr></form></table></center>";
	}
	elseif ($what == "sc")	{
		$userinput = "<br><br><center><table cellspacing=1 border=0 cellpadding=3 class=\"tableOutline\"><tr><td class=\"tableHead\">Möchten Sie die Forschung an \"$forname\" wirklich abbrechen?</td></tr>
					<tr><td class=\"tableInner1\" align=center><a href=$thissite.php?ia=killqu&what=sc&type=$type&innestaction=next&killtime=1 class=linkAuftableInner>bestätigen</a> - <a href=$thissite.php class=linkAuftableInner>zurück</a></td></tr></table></center>";
		}

}

function isBuddy($id){
	
	//0 nein, 1 ja aktiv, 2 nein gelöscht
	global $status;
	$uid1 = single("select id from users where konzernid=".$status[id]);
	$uid2 = single("select id from users where konzernid=".$id);
	$bdata = assoc("select * from users_buddy where ((uid1 = $uid1 and uid2 = $uid2) or (uid2 = $uid1 and uid1 = $uid2)) and status=1 and reaction=1");
	if(!$bdata) return 0;
	else return $bdata[status];
	
}

function getNextFreeBuddy($num=1){
	
	//num = anzhal gesuchten buddies
	global $status;
	$nextBuddy = array();
	$uid1 = single("select id from users where konzernid=".$status[id]);
	$bdata = assocs("select * from users_buddy where (uid1 = $uid1 or uid2 = $uid1) and status=0 and autoCreate=1 order by (quantity1+quantity2) desc");
	foreach($bdata as $tag=>$val){
		if(single("select konzernid from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]))){
		$rounds = getAllBuddyRounds($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]);
		$roundss=array();
		foreach($rounds as $t=>$v)
			$roundss[] = "R".$t." - ".$v;
		$rounds=implode("<br>", $roundss);
		$nextBuddy[] = array("uid"=>($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]),"emonick"=>single("select username from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1])), "rounds"=>$rounds, "name"=>single("select syndicate from status where id=(select konzernid from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]).")")." (#".single("select rid from status where id=(select konzernid from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]).")").")", "sid"=>single("select konzernid from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1])));
		if(--$num==0) break;
		}
	}
	return $nextBuddy;
	
}

function getAllBuddy(){
	
	//num = anzhal gesuchten buddies
	global $status;
	$nextBuddy = array();
	$uid1 = single("select id from users where konzernid=".$status[id]);
	$bdata = assocs("select * from users_buddy where (uid1 = $uid1 or uid2 = $uid1) and status=1 and reaction<> $uid1 order by reactionTime desc");
	foreach($bdata as $tag=>$val){
		$nextBuddy[] = array("reaction"=> $val[reaction],"uid"=>($val[uid1] == $uid1 ? $val[uid2] : $val[uid1]),"time"=>$val[reactionTime], "sid"=>single("select konzernid from users where id=".($val[uid1] == $uid1 ? $val[uid2] : $val[uid1])));
	}
	return $nextBuddy;
	
}

function getBuddyNums(){
		//num = anzhal gesuchten buddies
	global $status;
	$sessidsactual = assocs("select user_id, gueltig_bis from sessionids_actual", "user_id");
	$time = time();
	$nextBuddy = array();
	$uid1 = single("select id from users where konzernid=".$status[id]);
	$bdata = assocs("select * from users_buddy where (uid1 = $uid1 or uid2 = $uid1) and status=1 and reaction=1 order by reactionTime desc");
	$tot=0;
	$on=0;
	foreach($bdata as $tag=>$val){
		$tot++;
		$sid=single("select konzernid from users where id =".($val['uid1']==$uid1 ? $val['uid2'] : $val['uid1']));
		if($time < $sessidsactual[$sid]["gueltig_bis"]) $on++;
	}
	return array("total"=>$tot, "on"=>$on);
	
}

function getPlayerOnline(){
		//num = anzhal gesuchten buddies
		$time = time();
	return  single("select count(*) from sessionids_actual where gueltig_bis>$time");

	
}

function getAllBuddyRounds($id){
	
	global $status, $mek;
	$rounds = array();
	$uid1 = single("select id from users where konzernid=".$status[id]);
	//$uid2 = single("select id from users where konzernid=".$id);
	$uid2=$id;
	$rdata = assocs("select round, rid from stats where user_id=$uid1 order by round desc", "round");
	if($mek) pvar($rdata);

	foreach($rdata as $tag=>$val){
		$ja=single("select count(*) from stats where user_id=$uid2 and round=$tag and rid>0 and rid=".$val['rid']);
		if($ja) $rounds[$val['round']-2] = single("select name from syndikate_round_".$val['round']." where synd_id=".$val['rid'])." (#".$val['rid'].")";
	}
	return $rounds;
	
}

//by R4bbiT - 20.11.11 - Klasse um die Tipps zu steuern
class Tipps {
	public static function getTipp($site, $userid){
		$like = ' WHERE (site LIKE \'%'.$site.',%\' OR site LIKE \'%all,%\') and id not in (select tippid from tippshidden where userid = '.$userid.')';
		$tipp = assoc('SELECT id, id as t_id, text, site, (SELECT count(id) FROM tipps '.$like.') as sum, (SELECT count(id) FROM tipps '.$like.' AND id <= t_id) as num FROM tipps '.$like.' ORDER BY RAND() LIMIT 1');
		return $tipp;
	}
	public static function getNext($id, $site, $userid){
		$like = ' WHERE (site LIKE \'%'.$site.',%\' OR site LIKE \'%all,%\') and id not in (select tippid from tippshidden where userid = '.$userid.')';
		$tipp = assoc('SELECT id, id as t_id, text, site, (SELECT count(id) FROM tipps '.$like.') as sum, (SELECT count(id) FROM tipps '.$like.' AND id <= t_id) as num FROM tipps '.$like.' ORDER BY id ASC LIMIT '.($id).', 1');
		if(!$tipp){
			$tipp = assoc('SELECT id, id as t_id, text, site, (SELECT count(id) FROM tipps '.$like.') as sum, (SELECT count(id) FROM tipps '.$like.' AND id <= t_id) as num FROM tipps '.$like.' ORDER BY id ASC LIMIT 1');
		}
		return $tipp;
	}
	public static function getPrev($id, $site, $userid){
		$like = ' WHERE (site LIKE \'%'.$site.',%\' OR site LIKE \'%all,%\') and id not in (select tippid from tippshidden where userid = '.$userid.')';
		if($id >= 2){
			$tipp = assoc('SELECT id, id as t_id, text, site, (SELECT count(id) FROM tipps '.$like.') as sum, (SELECT count(id) FROM tipps '.$like.' AND id <= t_id) as num FROM tipps '.$like.' ORDER BY id ASC LIMIT '.($id-2).', 1');
		}
		else{
			$tipp = assoc('SELECT id, id as t_id, text, site, (SELECT count(id) FROM tipps '.$like.') as sum, (SELECT count(id) FROM tipps '.$like.' AND id <= t_id) as num FROM tipps '.$like.' ORDER BY id DESC LIMIT 1');
		}
		return $tipp;
	}
	public static function toggleUserTipp($userid, $tippid){
		$already_hidden = single('select id from tippshidden where userid = '.$userid.' and tippid = '.$tippid);
		if($already_hidden){
			select('delete from tippshidden where id = '.$already_hidden);
		}
		else{
			select('insert into tippshidden (userid, tippid) values('.$userid.', '.$tippid.')');
		}
	}
}
function toUTF8($var){
	if(is_array($var)){
		foreach($var as $t => $v){
			if(is_array($v))
				$var[$t] = toUTF8($v);
			else
				$var[$t] = utf8_encode($v);
		}
		return $var;
	}
	else
		return utf8_encode($var);
}

/**
 * 
 * Sortiert ein 2 Dimensionales Array nach einem key (2012_10_14 inok1989)
 * @param $key (z.B. emonick)
 * @param $ary
 */
function sort2DimString($key, $ary) {
	for($j=0; $j < count($ary); $j++) {
		$tmp = $ary[$j];
		$i = $j;
		while(($i >= 0) && (strcasecmp($ary[$i-1][$key], $tmp[$key]) > 0)) {
			$ary[$i] = $ary[$i-1];
			$i--;
		}
		$ary[$i] = $tmp;
	}
	return $ary;
}
function sort2DimInteger($key, $ary) {
	for($j=0; $j < count($ary); $j++) {
		$tmp = $ary[$j];
		$i = $j;
		while(($i >= 0) && ($ary[$i-1][$key] > $tmp[$key])) {
			$ary[$i] = $ary[$i-1];
			$i--;
		}
		$ary[$i] = $tmp;
	}
	return $ary;
}

/**
 * Function getDayDifference
 * 
 * Beachtet Sommer/Winterzeit
 */ 
function getDayDifference($time1, $time2) {
	//echo $time1.' '.(date('G', $time1)*3600 + date('i', $time1)*60 + date('s', $time1))."\n<br>";
	//echo $time2.' '.(date('G', $time2)*3600 + date('i', $time2)*60 + date('s', $time2))."\n<br>";
	$diff = 0;
	// Übergang zu Sommer/Winterzeit beachten
	if (date('I', $time2) && !date('I', $time1)) $diff = 3600; 
	if (date('I', $time1) && !date('I', $time2)) $diff = -3600;
	$time1 = $time1 - date('G', $time1)*3600 - date('i', $time1)*60 - date('s', $time1);
	$time2 = $time2 - date('G', $time2)*3600 - date('i', $time2)*60 - date('s', $time2);
	$diff += $time2 - $time1;
	return ($diff/86400);
}

/**
 * Function checkSummertime
 * 
 * Übergang zu Sommer/Winterzeit beachten
 */ 
function checkSummertime($time, $plusDays, $plusHours = null) {
	if ($plusHours != null) {
		$return = $time + $plusDays*86400 + $plusHours*3600;
	} else {
		$return = $time + $plusDays;
	}
	if (date('I', $time) && !date('I', $return)) $return += 3600; 
	if (date('I', $return) && !date('I', $time)) $return -= 3600;
	return $return;
}

?>
