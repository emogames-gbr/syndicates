<?

//**************************************************************************//
//							Übergabevariablen checken						//
//**************************************************************************//

$sessionid = $sessionid; // Übergebene id ist sessionid, durch cookie, evtl unescape specchars drauf ?
$ksession = $ksession; // übergebene krawall sessionid
$adminsessionid = $adminsessionid;
$init = round($init);

session_start();
//**************************************************************************//
//							Variablen deklarieren							//
//**************************************************************************//
$runlogs=0; // auf 0 setzen, damit nicht mehr jeder verdächtie klick mitgelogt wird
$handle = ""; // Später dbhandle
$ausgabe= ""; // Hier kommt die gesamte ausgabe rein
$fehler = "";
$querystring = "";
$successmeldung = "";
$kopf = "";
$globals = array (); // sptäter assoziatives array mit daten aus globals table
$loggedin = 1; // Entscheidet ob menu und ressourcenleiste geladen werden. Wenn true wird beides geladen
$time = time(); // Zeit zur skriptausführung als timestamp, bitte später nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.

/*fool
$fool=1301680800;
if($time>$fool and $time<$fool+10*60){
	header("location: ../index.php");
	exit();
}
*/


$id = 0;
$status = array(); // speichert alle spielerinformationen
$sciences = array(); // speichert alle infromationen zu forschungen
mt_srand($time);
$dr =0; // anzahl db aufrufe
$ripf = "images/"; # Relativer Imagepfad
$page = getenv(SCRIPT_NAME);
$HTTP_USER_AGENT = getenv(HTTP_USER_AGENT);
$agent = $HTTP_USER_AGENT;
$lck = floor($lck);
$page = (strrchr($page,'/'));
$page = substr($page,1,strlen($page)-5);
$self = (explode("/",$SCRIPT_NAME));
$self = array_pop($self);



//**************************************************************************//
//							Eigentliche Berechnungen						//
//**************************************************************************//

require_once("../../includes.php"); // Subfunctions laden
$handle = connectdb();

// Das Templatesystem  einbinden
require_once(LIB."smarty/libs/templates_setup.php");

// Eine neue Instanz der Template Klasse erzeugen
$tpl = Template::getInstance();

require_once(LIB."/mod_profiler.php");
dontcache();

require_once (LIB."js.php");
js::loadOver();

// Nicht erlaubte Cookies töten;
$allowed_cookies = array("mod_referrer_ident", "dev", "adminsessionid", "dontusepacket", "loginid", "loginsid", "autologinkey", "sessionid", "PHPSESSID", "fbs_".FACEBOOK_APP_ID, "deaktivate_mobile");
$false_cookies = array();
foreach ($_COOKIE as $ky => $vl) {
	if (!in_array($ky, $allowed_cookies)) {
		unset($_COOKIE[$ky]);
		unset($$ky);
		$false_cookies[$ky] = $vl;
	}
}


$start = getmicrotime();
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen

/*
require_once ("modules/mod_profiler.php");
$profiler = new profiler();
$profiler->init();
*/

//select("INSERT INTO  `peak_tracker` (  `id` ,  `time` ) VALUES (NULL ,  '".time()."');");

$globals = getglobals();
$game = assoc("select * from game limit 1");
$pagestats = assoc("select * from pages where dateiname = '$page'");
$pages = assocs("select * from pages where visible=1 order by showposition","dateiname");

if ($globals[roundstatus] == 2 && $globals[roundendtime] < ($time - 86400)) { // hier eigentlich ROUND_FREEZETIME_DURATION (aus globalsvars.php)
			pvar("TEST");
			header("Location: ../index.php"); 
			exit();
} else { // falls roundstatus nicht 2
	$adminlogin = 0;
	//Hier Abfrage hinzugefügt, ob überhaupt noch eine aktive adminsessionid in der DB vorliegt. Ansonsten wurde der ID hier immer Blödsinn zugewiesen.
	if($adminsessionid) $openadminsessions = single("select count(*) from sessionids_admin where sessionid='$adminsessionid' and gueltig_bis >= $time");
	if ($openadminsessions && $adminsessionid) { 
		$id_data = assoc("select sessionid, angelegt_bei, gueltig_bis, ip, user_id from sessionids_admin where sessionid='$adminsessionid' and gueltig_bis >= $time"); 
		$locked = 0; 
		$id = $id_data[user_id]; 
		$paid = 1; 
		$adminlogin = 1; 
	} elseif ($sessionid) {
		$id_data = checksid($sessionid);
		$mlocked = $id_data['microlocked'];
		$locked = $id_data[locked];
		$id = $id_data[user_id];
		$paid=$id_data[paid];
	} else {$id =0;}

		$checktime = $time-4;

	// $_COOKIE bereinigen und Übergabewerte loggen
	if ($false_cookies) { // s.o.
		$tojoin = array();
		foreach ($false_cookies as $ky => $vl) {
			$tojoin[] = "$ky=$vl";
		}
		filelog(date("H:i:s", $time)." - $id - ".getenv ("REMOTE_ADDR")." ::: ".join("|", $tojoin)."\n", "fantasy_cookies/".date("Y_m_d", $time)."_false_cookies.txt");
	}

	$loggit = array();
	if ($_POST) {
		foreach ($_POST as $ky => $vl) {
			if ("'".floor($vl)."'" == "'$vl'") {

			}
			else {
				$loggit[] = "$ky=$vl";
			}
		}
	}
	//$loggit[] = "";
	//$loggit[] = "";
	//$loggit[] = "";
	if ($_COOKIE && FALSE) {
		foreach ($_COOKIE as $ky => $vl) {
			if ("'".floor($vl)."'" == "'$vl'") {

			}
			else {
				$loggit[] = "$ky=$vl";
			}
		}
	}
	if (count($loggit) == 3) {
		$loggit = array();
	}
	if ($loggit) {
		filelog(date("H:i:s", $time)." - $id - ".getenv ("REMOTE_ADDR")." - ".$page." ::: ".join("|", $loggit)."\n", "post_commands/".date("Y_m_d", $time)."_post_and_cookies_vars.txt");
	}





	if ($sessionid[0] == "*" && $id && !$paid) {	# Das Loginskript ersetzt den ersten Buchstaben der Sessionid automatisch durch einen Stern, wenn der Vcode noch nicht bestätigt wurde
		list($createtime, $vercode) = row("select createtime, vcode from users where konzernid=$id");
		if ($createtime + 70 * 3600 < $time && $vercode && $page != "verification"): header ("Location: verification.php"); exit(); endif;
	}
	if ($pls02): select("insert into code_stealer_tracing (time, id, phrase) values ('$time', '$id', 'pls02 = ".floor($pls02)."')"); endif;

	// Wenn keine id vorhanden ist user nicht richtig eingelogt
	if (!$id) {
			header ("Location: ../index.php?action=error&code=78453"); exit;
				$loggedin = 0;
				$ausgabe .= "<br><br><br><center><b>Sessionid abgelaufen, bitte neu <a class=\"highlightAufSiteBg\" href=../index.php>einloggen</a></b></center><br><br><br>";
				include ("header.php");
				echo $ausgabe;
				include("footer.php");
				exit();
	}

	// Wenn id korrekt, spielerdaten laden
	$status = getallvalues($id);
	
	if( array_key_exists("chat",$_GET) )
	{
		$_SESSION['chat_id'] = $status['id'];
		$_SESSION['chat_rid'] = $status['rid'];
		$_SESSION['chat_name'] = $status['syndicate'];
		$_SESSION['chat_enable'] = false;
        $_SESSION['chat_ally'] = allyOfSyn($status['rid']);
       	$_SESSION['chat_mentor'] = $status['is_mentor'];
	}
	


	// Erst Konfigphase verlassen
	if (getServertype() == "classic" && $status['inprotection'] == "Y" && 
	    $page != "configseite" && 
	    $page != "logout" &&
	    $page != "premiumfeatures" &&
	    $page != "gamevalues" &&
	    $page != "allgboard" &&
	    $page != "syndboard" &&
	    $page != "polls" &&
	    $page != "nachrichten" &&
	    $page != "mitteilungen" &&
	    $page != "statusseite" &&
	    $page != "gruppenboard" &&
	    $page != "settings" &&
	    $page != "options" &&
	    $page != "description" &&
	    $page != "werben" &&
		$page != "testswitch" &&
		$page != "testconfig" &&
		$page != "testmentoren" &&
		$page != "gruppen"
	  ) {
	  header("Location: configseite.php"); 
	  exit();
	}

	require_once("inc.themes.php"); // JOINER
	require_once("globalvars.php");
	$game_syndikat = assoc("select * from syndikate where synd_id=$status[rid]");
	$status[locked] = $locked;
	$status['microlocked'] = $mlocked;
	//$paid = 1; // TEST
	$status[paid] = 1;
	
	//pvar($status[paid],paid);
	//pvar($status[id]);
	// Zahlungserinnerung
	$showreminder = mt_rand(1,(1000));

	// Zur Zeit ohne Bedeutung, da $status[paid] immer 1 ist (wird in login.php in die sessionid geschrieben und hier weiter oben dann in $status[paid] gesetzt
	if ((($showreminder >= 996 ) || $status[showreminder] == 1 ) && $status[paid] == 0){
		if ($_GET || $_POST) {
			select("update status set showreminder=1 where id=$status[id]");
		}
		else {
			$status[showreminder] = 0;
			select("update status set showreminder=0 where id=$status[id]");
			require("inc.themes.php");
			include ("header.php");
			include ("reminder.php");
			include("footer.php");
			exit();
		}
	}

	## Falls Urlaubsmodus aktiv => vacation.php und logout.php sowie verification.php only zulassen
	if ($status[alive] == 2 && !$adminlogin): if ($page != "vacation" && $page != "logout" && $page != "verification"): header ("Location: vacation.php"); exit(); endif; endif;
	
	/*##Proxy user
	if (
	      $_SERVER['HTTP_X_FORWARDED_FOR']
	   || $_SERVER['HTTP_X_FORWARDED']
	   || $_SERVER['HTTP_FORWARDED_FOR']
	   || $_SERVER['HTTP_CLIENT_IP']
	   || $_SERVER['HTTP_VIA']
	   || in_array($_SERVER['REMOTE_PORT'], array(8080,80,6588,8000,3128,553,554))
	   || @fsockopen($_SERVER['REMOTE_ADDR'], 80, $errno, $errstr, 30)
	   ):
	    if ($page != "proxyuser" && $page != "logout" && $page != "verification"):
	 header ("Location: proxyuser.php"); exit(); endif; endif;*/
	 
	$yellowdot = "<img src=\"".$layout["images"]."dot-gelb.gif\" hspace=\"5\" border=\"0\">";
	$reddot = "<img src=".$layout["images"]."dot-rot.gif hspace=\"5\" border=\"0\">";
	$greendot = "<img src=".$layout["images"]."dot-gruen.gif hspace=\"5\" border=\"0\">";
	$sciences = getsciences($id);
	$voted = assocs("select link, ip from link_klick_count where user_id = $id and time > ".($time-24*3600)); // Wird jetzt auch in game.php geholt weil in menu
	$artefakte = get_artefakte();
	$partner = getpartner($id);
	$features = assocs("select feature_id from features where konzernid=$id", "feature_id");
	$sd=$features; //absichern zum späteren vergleich
	if ($status['is_mentor']) $features[KOMFORTPAKET] = 1; // Zum Testen der Werbung

	// Begrenze Premium-Feature-Mailaktion Runde 23
	{	if ($globals[round] == 44) {
			$premiumfeature_mailaktion = single("select premiumfeature_mailaktion from users where konzernid = $id");
			if ($premiumfeature_mailaktion) {
				$features[FORSCHUNGSQ] = 1;
				$features[GEBAEUDEQ] = 1;
				$features[MILITAERQ] = 1;
				$features[WERBUNG_DEAKTIVIERT] = 1;
				$features[KOMFORTPAKET] = 1;
				$features[ANGRIFFSDB] = 1;
			}
		}
	}
	// Premium-Features für Wechsler vom Basic auf Classic in ihrer ersten Classic-Runde
	if (getServertype() == "classic") {
	  $mpoc = single("select may_play_on_classic from users where konzernid = $id");
	  if ($mpoc == 2) {
				  $features[FORSCHUNGSQ] = 1;
				  $features[GEBAEUDEQ] = 1;
				  $features[MILITAERQ] = 1;
				  $features[WERBUNG_DEAKTIVIERT] = 1;
				  $features[KOMFORTPAKET] = 1;
				  $features[ANGRIFFSDB] = 1;
	  }
	}
	//if($sd!=$features) echo "wtf?"; //unmöglich vlt doch
	if ($pagestats{name}): $kopf = kopf($pagestats{name},$pagestats{hilfedateiname},$pagestats{description}); endif;
	//        $away = getaway($id); wegen nw im statustable nicht mehr global
	//        $market = getmarket($id); wegen nw im statustable nicht mehr global
	//        $status{nw} = nw($id); Existiert jetzt im Status table
	// Überflüssige Energie töten
	heaptable_write($status{id},$time,$pagestats); // Eintrag in den Zählheaptable
	user_agent_log($HTTP_USER_AGENT); // useragent mitloggen
	$maxstore = speicherbar($status,$sciences);
	// Nachprüfen ob zuviel Energie vorhanden
	if ($status{energy} > $maxstore) {
		$lost = $status{energy} - $maxstore;
		$status{energy} = $maxstore;
		$status{nw} = nw($status{id});
		if ($globals[updating] == 0)	{
			select("insert into message_values (id,user_id,time,werte) values (2,".$status{id}.",".$time.",'".pointit($lost)."')");
			select("update status set energy=".$status{energy}.",nw = ".$status{nw}." where id = ".$status{id});
			// Loggen
			select("insert into losslogs (user_id,time,product,number) values (".$status{id}.",$time,'energy',$lost)");
		}
	}
	// Darf die Seite benutzt werden ?
	$allowed = checkpageaccess($pagestats,$globals,$status,$time);

	// Einsteigerhilfe gelesen? - Checken, nach 6h ohne Klick auf den Link bzw. nach 20 Klicks ohne Klick auf den Link kommt eine permanente Infobox an den Spieler die den Sachverhalt beschreibt und auf die Gewinnmöglichkeit verweist. Nach 24h ohne Klick bzw. nach 80 Klicks ohne Klick gehts hier nicht weiter und es kommt permanent eine Info-Seite mit Downloadzwang; -- Nach 3 Tagen gehts pauschal weiter, weil dann davon auszugehen ist, dass der User sowieso inaktiv ist und nicht mehr spielen wird
	
	
	//f("Achtung, wir haben ein Backup von Gestern eingespielt - mehr dazu in den News.");
	
	
	if ($headeraction) {
		if ($headeraction == "galaxy-news" or $headeraction == "gamesdynamite") {
			select("insert into link_klick_count (user_id, time, link, ip) values ($id, $time, '$headeraction', '".getenv ("REMOTE_ADDR")."')");
			if ($headeraction == "galaxy-news") $link = "http://www.galaxy-news.de/charts/?op=vote&game_id=44";
			if ($headeraction == "gamesdynamite") $link = "http://bgs.gdynamite.de/charts_vote_130.html";
			header("Location: $link"); exit();
		}
		
	}
	
	##
	##	TEXT FÜR SCHUTZZEIT
	##
	if ($status[inprotection] == "Y" ) {
			i("Dein Konzern befindet sich in der <b>Konfigurationsphase</b>. Während der Konfigurationsphase kannst du deinen Konzern auf das eigentliche Spiel vorbereiten. Dein Konzern ist solange vor Angriffen und vor Spionage geschützt.<br>
			Bitte konfiguriere deinen Konzern auf der <a href=\"configseite.php\" style=\"color:black;text-decoration:underline;font-size:12px;\">Konfigurationsseite</a>.
			<br>");
	}	
	##
	##	TEXT WENN NOCH KEINE GEBÄUDE GEBAUT WURDEN
	##
	if (!in_config($status) &&  getallbuildingsunderconstruction() == 0 && getallbuildings($status[id]) <= 0) {
		i("<br>Du hast noch keine <a href=\"gebaeude.php\">Gebäude</a> gebaut! Baue Gebäude, um Ressourcen zu produzieren und deine wirtschaftliche Entwicklung sicherzustellen.");
	}
	
	// Prüft ob Oster, Weihnachts, Weltuntergangsevents derzeit aktiv sind
	require('events.php');
		
	
		//require_once("redprot.php");
	if ($allowed != 1) {
		if ($allowed) f($allowed);
		include ("header.php");
		include("footer.php");
		exit();
	}
	if ($init == 2) {checkstats($status);}
}	// falls roundstatus nicht 2
//update($status{id});




// Temporäre Sperre:
/*
if (!single("SELECT COUNT(*) FROM users WHERE username = 'inok1989' AND konzernid = ".$id)) {// || !$get_TESTING_SPERRE) {
	echo "<br><br><br><center><b>Wie in den <a class=\"highlightAufSiteBg\" href=../index.php>Änderungen</a> angekündigt ist Syndicates für 14-15 Uhr nicht verfügbar.</b></center><br><br><br>";
	exit();
}
*/






//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


//							Getglobals						//

// Holt Werte aus dem Globalstable
function getglobals() {
		$result = assoc("select * from globals order by round desc limit 1");
return $result;
}



function heaptable_write($id,$time,$page) {
	if ($id && $time && $page) {
		$heaptables = array("heaptable", "heaptable2");
		foreach ($heaptables as $heaptablename) {
        	select("insert into $heaptablename (user_id,clicktime,seite) values ($id,$time,'".$page{id}."')");
		}

	}
        //select("insert into heaptable2 (user_id,clicktime,seite) values ($id,$time,'".$page{id}."')");
}

function user_agent_log($HTTP_USER_AGENT) {
	global $id;
	global $pagestats;
	global $globals;
	global $agent;
	global $runlogs;
	if ($runlogs == 1) {
		if ($status[clicks] / (($time-$globals[roundstarttime])/60/60/24) > 100) {
			select("insert into suspicous_most (user_id,clicktime,seite) values ($id,$time,'".$pagestats{id}."')");
		}
		$last60 = single("select count(*) from heaptable where user_id=$id");
		if ($last60 > 10 && $pagestats[id]==27 || $pagestats[id]==16 ) {
			select("insert into suspicous_fast (user_id,clicktime,seite,agent) values ($id,$time,'".$pagestats{id}."','$agent')");
		}
		// $times_used = single("select number from user_agents_logs where user_agent='$HTTP_USER_AGENT'");
		if ($times_used >= 1) {
			// select("update user_agents_logs set number=number+1 where user_agent='$HTTP_USER_AGENT'");
		}
		else {
			// select("insert into user_agents_logs (user_agent,number) values ('$HTTP_USER_AGENT',1)");
		}
	}
}

function checkpageaccess($page,$globals,$status,$time) {
    // Seiten, die nur bei laufender Runde funktionieren
	global $id, $sciences;
	
    if ($globals[roundstatus] == 2 && $globals[roundendtime] + ROUND_FREEZETIME_DURATION < $time) {
      return "Die Runde ist beendet. Bitte informieren Sie sich auf der Startseite, wann die nächste Runde beginnt.";
    }

    // 1 und 2 sind das selbe. Muss man mal korrigieren <- TODO
    // Seiten die erst ab Rundenstart funktionieren
    if ($page{roundstatus} == 1 || $page[roundstatus] == 2) {
        if ($globals[roundstatus] == 0) {
            return "Diese Seite steht erst nach dem Rundenstart zur Verfügung";
        }
    }
    // Seiten, die nur nach Beenden Konfigphase funktionieren, aber auch schon vor Rundenstart
    if ($page{roundstatus} == 3 || $page[roundstatus] == 4) {
    	if ($status['is_mentor'] && $page['dateiname'] == 'spies')
    		return 1;
		if (in_protection($status)) {
	   		return "Diese Seite steht erst zur Verfügung, wenn Ihr Konzern nicht mehr unter Schutz steht.";
		}
    }

    return 1;
}

function checkstats($status) {
    global $globals;
    $stats = assoc("select * from ".$globals{statstable}." where konzernid = ".$status{id}." and round=$globals[round]");
    if ($status{nw} > $stats{largestnetworth}) {
        select ("update ".$globals{statstable}." set largestnetworth =".$status{nw}." where round=$globals[round] and konzernid = ".$status{id});
    }
    if ($status{land} > $stats{largestland}) {
        select ("update ".$globals{statstable}." set largestland =".$status{land}." where round=$globals[round] and konzernid = ".$status{id});
    }
}

/********************************************************************************

Function checksid()

*********************************************************************************/
// Überprüft eine übergebene Sessionid auf gültigkeit id zurück, falls gültig, sonst 0
function checksid($sid) {
		if ($sid) {
			global $time,$start,$lck;
			$sessionid_data = array(); // Speichert rückgabe des selects
			$ip = getenv ("REMOTE_ADDR");

			$result = select("select sessionid, angelegt_bei, gueltig_bis, ip,microlocked, user_id,locked,paid from sessionids_actual where sessionid='$sid' and gueltig_bis >= $time");
			if (mysql_num_rows($result) != 1) {return 0;} // wenn sid nicht im table gefunden wurde false zurückgeben oder zufällig mehrer gleiche sids existieren
			$sessionid_data = mysql_fetch_assoc($result);
			//if ($sessionid_data[ip] != $ip) {return 0;}
			if ($time < $sessionid_data[gueltig_bis])	{

				// User id verlängern und zuweisen an rückgabevariable, locked auf 1 setzen
				#$gueltig_bis = $time + SESSION_DAUER;
				#if ($sessionid_data[angelegt_bei] + 3600 < $gueltig_bis) {
				#	$gueltig_bis = $sessionid_data[gueltig_bis];
				#}
			
				if (count($_POST) > 0 || $lck == 1) { // COunt post > 0 --> wahrscheinlich aktion
					ignore_user_abort(TRUE);
					#select("update sessionids_actual set gueltig_bis=$gueltig_bis,locked = 1 where sessionid='$sid'");
					
					// Lock nur setzen, wenn noch nicht da
					if ($sessionid_data[locked] == 0 && $sessionid_data[microlocked] == 0) {
						select("update sessionids_actual set locked = $time,microlocked='$start' where sessionid='$sid'");
					}
				}
			}
		return $sessionid_data;
    }
}


?>
