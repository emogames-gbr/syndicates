<?
//**************************************************************************//
//							Variablen deklarieren							//
//**************************************************************************//
ob_start();
require_once("../../includes.php");
require_once(LIB."picturemodule.php");
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
require_once(INC."ingame/globalvars.php");
require_once(LIB."/mod_login.php"); // Für Logincode
$codeinput = addslashes($codeinput);

$data = array();
$accountid = 0;
$konzernid = 0;
$vcode = "";
$alive = 0;
$sessionid="";
$sessionid2 = "";
$time = time();
list($usec,$sec) = explode(" ",microtime()); $init = (($sec-1000000000) + round($usec*1000)/1000)*1000; mt_srand($init);
$servername = $SERVER_NAME;
$game = assoc("select * from game limit 1");
$id=0; // später konzernid
print "bla";
print_r($game);
//**************************************************************************//
//							Eigentliche Berechnungen						//
//**************************************************************************//


// Hitstats extern updaten
$today = date("d.m.Y",$time);
$exists = mysql_query("select date from hitstats_extern where date ='$today'");
$exists = mysql_fetch_row($exists);
$exists = $exists[0];
$what = "login";
if (!$exists) { mysql_query("insert into hitstats_extern (date,$what) values('$today','1')"); }
if ($exists) { mysql_query("update hitstats_extern set $what=$what+1 where date='$today'"); }
$globals = assoc("select * from globals order by round desc limit 1");

// Login vorbereiten
if (checksid()) { $accountid = getid(); echo "HIER"; }


if ($accountid)	{
	list($userid,$konzernid, $createtime, $vcode) = row("select id,konzernid, createtime, vcode from users where id = $accountid");
	$konzerncreate = single("select createtime from status where id=$konzernid");

	// Auf error weiterleiten, falls kein Konzern existiert
	if (!$konzernid)	{ header ("Location: ../index.php?action=error&code=84561"); exit;}

	// Auf error weiterleiten, falls Konzern gebannt ist
	if (single("select banned from users where konzernid = $konzernid") > $time) { header ("Location: ../index.php?action=error&code=84562"); exit;}

	// Nachschauen ob Konzern noch lebt
	if ($konzernid) { $alive = single("select alive from status where id = $konzernid"); }

	// Toten Spieler auf Accounterstellung weiterleiten
	if (!$alive && $accountid)			{ header ("Location: ../index.php?action=anmeldung"); exit;}	# Account verreckt

	// Bildercode prüfen
	if ($konzernid) {
		$showncode = single("select code_id from codelogs where user_id=$konzernid and time > ($time-3600) and action=0 order by time desc limit 1");
		if (!checkusercode($konzernid,$showncode,$codeinput)) {
			header ("Location: ../index.php?action=error&code=76453&$codeinput"); exit;
		}
	}


	$paid_intern = 1;



	//
	// Payment prüfen Ende
	//


	// Sessionid erzeugen
	$sidok = 0;
	while (!$sidok) {
		$sessionid2="";
		for ($i=0;$i<20;$i++) {
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
			$sessionid2.= chr($random);
		} // For

		$sessionid2 .= crypt($accountid, mt_rand(10,99));

		$checksid = single("select sessionid from sessionids_actual where sessionid = '$sessionid2'");
		if (!$checksid) {$sidok = 1;}
	}


	//Sessionid manipulieren, falls user seinen Vcode noch nicht eingegeben hat.
	if ($vcode && $createtime + 70 * 3600 < $time) { $sessionid2 = preg_replace ("/./","*",$sessionid2, 1);}

	if ($servername != "localhost")	{$valid_time = SESSION_DAUER;}
	else if ($servername == "localhost")	{$valid_time = SESSION_DAUER;}

	$gueltig_bis = $time  + $valid_time;
	$id = $konzernid;


	// Nachschauen ob noch Sessionid im Actual table vorhanden und in safe schreiben
	$result= rows("select sessionid, angelegt_bei, gueltig_bis, ip, user_id, pc_identifier, browsername, hostname, iptrue from sessionids_actual where user_id='$id'");
	select ("delete from sessionids_actual where user_id='$id'");
	if (count($result) > 0) {
		foreach ($result as $ky => $vl)	{
			if ($vl[2] > $time) { $endtime = $time;}
			else { $endtime = $vl[2];}
			$lastklicktime = single("select clicktime from heaptable2 where user_id = '$id' order by clicktime desc limit 1");
			if ($endtime > $lastklicktime) $endtime = $lastklicktime;
			if ($endtime < $vl[1]) $endtime = $vl[1];
			select ("insert into sessionids_safe (sessionid, angelegt_bei, gueltig_bis, ip, user_id, pc_identifier, browsername, hostname, iptrue) values ('$vl[0]',$vl[1],$endtime,'$vl[3]', $vl[4], '$vl[5]', '$vl[6]', '$vl[7]', '$vl[8]')");
			omniput($game['name']."_" . "session_onlinedauer", ($endtime - $vl[1]), $time);
		}
	}

	// Neue Sessionid in die DB schreiben
	$ip = getenv ("REMOTE_ADDR");
	if (!$dev) {
		$dev = createkey($id);
		setcookie("dev", $dev, $time+2*365*86400, "/", ".".DOMAIN);
	}
	$pc_identifier = $dev;
	$browsername = htmlentities($HTTP_USER_AGENT, ENT_QUOTES);
	$hostname = ""; // htmlentities(gethostbyaddr($ip), ENT_QUOTES); // Hat Probleme gemacht
	select("insert into sessionids_actual (sessionid, angelegt_bei, gueltig_bis, ip, user_id, pc_identifier, browsername, hostname, paid, iptrue) values ('$sessionid2',$time,$gueltig_bis,'$ip',$id,'$pc_identifier', '$browsername', '$hostname', $paid_intern, '".get_ip()."')");

	// Spielsessionid setzen
	setcookie ("sessionid", $sessionid2, -1 ,"/",".".DOMAIN);
	omniput($game['name']."_" . "Konzern_Login", 1, $time);

	// Lastlogintime updaten
	select("update status set lastlogintime = $time where id = $id");

	// Spieler der Seinen VCode bestätigen muss auf verification.php umleiten
	if ($vcode && $createtime + 70 * 3600 < $time) { header ("Location: verification.php"); exit; }

	// Spieler in Urlaub auf VacationSeite schicken ggf.
	if ($alive == 2) {header ("Location: vacation.php"); exit;}	// Urlaub

	//stats lastnw und lastland aktualisieren
	$status = assoc("select * from status where id=$konzernid");
	$stats = assoc("select * from stats where round=$globals[round] and konzernid=$konzernid");
	if ($status[nw] > $stats[largestnetworth]) {$stats[largestnetworth] = $status[nw];}
	if ($status[land] > $stats[largestland]) {$stats[largestland] = $status[land];}
	select("update stats set largestnetworth=$stats[largestnetworth],largestland=$stats[largestland],lastnetworth=$status[nw],lastland=$status[land] where round=$globals[round] and konzernid=$konzernid");

	// Grafikpaketcookie
	if($status[imagepath] && !$usepacket) {
		setcookie ("dontusepacket", 1, -1 ,"/",".".DOMAIN);
	}
	else {
		setcookie ("dontusepacket", 0, -1 ,"/",".".DOMAIN);
	}

	// Gucken ob der Spieler ein Inaktiver ist der wieder zurückverschoben werden muss - wieder eingeführt Runde 64 inok1989
		$rid = single("SELECT rid FROM status WHERE id = ".$konzernid);
		pvar($rid);
		if (!$rid) { // implizit wenn in Syndikat 0 (Inaktivenpool)
			$synd_type = single("select synd_type from syndikate where synd_id = $status[rid]");
			// Jetzt verschiebung vornehmen, dazu syndikat suchen
			$newrid = get_an_empty_syndicate();
			while (single("select atwar from syndikate where synd_id = '$newrid'")) {
				$old_rids[] = $newrid;
				$newrid = get_an_empty_syndicate();
			}
			$who = $status[id];
			if ($newrid)	{
				$queries = array();
				$queries[] = "update status set rid=".$newrid." where id=".$who;
				
				$podpoints = $status[podpoints];
				$wholand = $status[land];
				if ($podpoints <= 0) {
					$resswert = array('podmoney' => $podpoints, 'podmetal' => 0, 'podenergy' => 0, 'podsciencepoints' => 0);
				} else {
					$resswert = assoc("SELECT * FROM syndikate_wechsel WHERE newrid = 0 AND konzernid = ".$konzernid." ORDER BY time DESC LIMIT 1");
				}
				$queries[] = "update syndikate set podmoney = podmoney + ".$resswert['podmoney'].", 
															  podmetal = podmetal + ".$resswert['podmetal'].",
															  podenergy = podenergy + ".$resswert['podenergy'].",
															  podsciencepoints = podsciencepoints + ".$resswert['podsciencepoints']."
														   where synd_id = ".$newrid;
				
				$queries[] = "INSERT INTO syndikate_wechsel (konzernid, oldrid, newrid, time) 
					VALUES ('".$who."', '".$status[rid]."', '".$newrid."', '".$time."')";
				$queries[] = "update ".$globals{statstable}." set rid=".$newrid." where round=".$globals[round]." and konzernid=".$who;
				$message="Der Konzern <b>".$status{syndicate}."</b> tritt aus wirtschaftlichen Interessen unserem Syndikat bei.";
				$action ="insert into towncrier (time,rid,message) values ($time,".$newrid.",'".$message."')";
				array_push($queries,$action);
				//$queries[] = "update syndikate_anfaenger_inaktivenverschiebungen set back = 1 where user_id = $status[id] and new_rid = $status[rid] and old_rid = $newrid ";
				player_join_syndicate($who, $newrid);
				db_write($queries);
			}
		}

	// Umleitung auf Spieldatei
	if ($status[inprotection] == "Y") {
	  header ("Location: configseite.php"); exit;
	}
	else {
	  header ("Location: statusseite.php?init=2"); exit;
	}

} else { header ("Location: ../index.php?action=error&code=5"); exit; }

# 1:	Account nicht vorhanden
# 2:	Username und/oder Passwort nicht eingegeben
# 3:	Passwort falsch eingegeben / nicht identisch
# 4:	Account verreckt / von einem anderen Konzern geplättet (Alive = 0), der Spieler darf nicht mehr spielen!
# 5:	Kann keine Userid aufschlüsseln
ob_flush();

?>
