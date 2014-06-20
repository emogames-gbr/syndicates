<?
### * S?mtliche ?bergabedaten festlegen;
ob_start();
define(MAXSPIELERANZAHL, 15000);
#define(STANDARD_NW_VALUE, 3694);
//define (MAX_USERS_A_SYNDICATE,25);

//$TEST = 1; // Testmodus ohne ip sperre
## Subs laden
## DB-Handle erzeugen
## Zeit setzen
## Loginmodul laden
## User bestimmen
##
## <<CODE 1:1 von index.php:



include("../includes.php");


$handle = connectdb($SERVER_NAME);
$time=time();
$races = singles("select race from races where active=1");
$game = assoc("select * from game");


include(LIB."mod_login.php"); // F?r Logincode
include(LIB."picturemodule.php"); // F?r Logincode
include(INC."ingame/globalvars.php"); // F?r get_an_empty_syndicate()

if (checksid()) {
	$sid = $loginsid;

	$databases = singles("select db_name from servers");		
	
	login($loginid,60,$databases); // Wichtig f?r cross server loginstuff
	$userdata = assoc("select * from users where id=".$loginid);
	$userid = $userdata[id];
	//print "bla:".$userid;
	$username = $userdata[username];
}



// Hier zeugs ausm loginmodul machen
$syndicate = preg_replace("/ {2,}/", " ", trim($syndicate));
$rulername = preg_replace("/ {2,}/", " ", trim($rulername));

$erroradd = "&syndicate=".urlencode($syndicate)."&rulername=".urlencode($rulername)."&race=$race";
$new_by_koins = 0; // Wird f?r verifcationmail gebraucht

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
// AB HIER NORMALER EMOGAMES ABLAUF
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

if (!isset($userdata) && $userid > 0) { // Bei Login ueber Koins wird nur userid ermittelt, aber nicht pauschal userdata
  $userdata = assoc("select * from users where id=$userid");
}


if (strlen($_GET[loginsid]) > 1) {
	 $loginsid = $_GET[loginsid];
	 $loginid = $_GET[loginid];
	 
}


else if (!$userdata[emogames_user_id]) { $userid = "";} #Ansonsten k?nnte man ?ber Adresszeile eine Userid ?bergeben und die Anmeldung so faken
## >>CODE 1:1 von index.php ENDE
$wid = (int)$wid;
$src = addslashes($src);
$globals = assoc("select * from globals order by round desc limit 1");
$servers = assocs("select * from servers","servertype");





if ($mentorenprogramm != "yes" && $mentorenprogramm != "no") $mentorenprogramm = "no";
if ($userdata['may_play_on_classic'] == 1 && $globals['round'] >= 47) $mentorenprogramm = "no"; 
								     // das bedeutet, dass Leute die es sich auf dem Basic verdient haben (=2) oder
								     // von einem Kumpel geworben wurden (=3) immer noch dem Mentorenprogramm 
								     // beitreten k?nnen

$syndikatsmodus = "random"; # ?nderung Runde 46, kein gezieltes Beitreten mehr m?glich
/*
if ($servertype != "classic" && $servertype != "basic") $servertype = "basic";
if ($servertype == "classic" && $userdata['may_play_on_classic'] == 0) $servertype = "basic";
*/
$servertype = "classic"; // Aenderung Runde 48 - kein basic mehr

if ($servertype == "basic" && $syndikatsmodus == "join") $syndikatsmodus = "random";

if ($servertype == "classic") $mentorenprogramm = "no"; // derzeit kein Mentorenprogramm auf dem Classic;
// Redirect auf anderen Server zur Anmeldung
$extension="create.php?syndicate=".urlencode($syndicate)."&rulername=".urlencode($rulername)."&race=$race&servertype=".urlencode($servertype)."&syndikatsmodus=".urlencode($syndikatsmodus)."&syndikatsid=".urldecode($syndikatsid)."&syndikatspw=".urlencode($syndikatspw)."&agb=".urlencode($agb)."&mentorenprogramm=".urlencode($mentorenprogramm);



// Aenderung Runde 48 - kein basic mehr
/*
/*if ($servertype == "basic" && !isBasicServer($game)) {
	mysql_select_db($servers[basic][db_name]);
	$userid = single("select id from users where emogames_user_id=$userdata[emogames_user_id]");
	$logindata = login($userid,60*60);
	header("Location: ".$servers[basic][url]."/".$extension."&loginsid=".urlencode($logindata['sid'])."&loginid=".$logindata['id']);exit(1);
}
elseif ($servertype =="classic" && isBasicServer($game)) {
	mysql_select_db($servers[classic][db_name]);
	$userid = single("select id from users where emogames_user_id=$userdata[emogames_user_id]");
	$logindata =  login($userid,60*60);
	header("Location: ".$servers[classic][url]."/".$extension."&loginsid=".urlencode($logindata['sid'])."&loginid=".$logindata['id']);exit(1);
}*/




// Checken ob Basic anmeldung und ob erlaubt

/* Deaktiviert
if (isset($userdata) && isBasicServer($game) && $userdata[startround] + 2 < $globals[round]) {
	//header("Location: index.php?action=error&code=8");exit(1);
}
*/



/*
$username		= param('username');
$password		= param('password');
$password2		= param('password2');
$email			= param('email');
$race			= param('race');
$syndicate		= param('syndicate');
$rulername		= param('rulername');
$syndikatsid	= param('syndikatsnummer');
$syndikatspw	= param('syndikatspasswort');
$syndikatsmodus	= param('syndikatsmodus');
$vorname		= param('vorname');
$nachname		= param('nachname');
*/


$ipaddress = getenv("REMOTE_ADDR");
list($roundstatus, $roundstarttime, $round, $statstable) = row("select roundstatus, roundstarttime, round, statstable from globals order by round desc limit 1");
if ($roundstatus != 2 && ($roundstarttime - $time > 120 or $roundstarttime - $time < 0))				{ # BEGINN if ($roundstatus != 2)

	###
	####
	####	Kurze ?berpr?fung ob User nicht bereits einen Konzern hat bzw. gebannt ist

	foreach ($servers as $temp) {
		mysql_select_db($temp[db_name]);
		$tuserdata = assoc("select * from users where emogames_user_id = $userdata[emogames_user_id]");
		$alive=single("select alive from status where id=$tuserdata[konzernid]");
		if ($alive > 0) {
			$error = 65957;
			break;
		}
		elseif ($userdata[deleted] == 127)	{ $error = 15793;	}

		
	}
	connectdb();
	
	if ($error)	{
		header ("Location: index.php?action=error&code=$error"); exit();
	}

	// Nur zwei Konzernerstellungen pro Tag m?glich.

	$daytime = get_day_time($time);
	$konzernids = singles("select syndicate from options_konzerndelete where time >= ".$globals['roundstarttime']." and user_id = ".$userdata['id']);
	if ($konzernids) {
		$concount = single("select count(*) from status where syndicate in ('".join("','", $konzernids)."') and createtime >= $daytime");
		if ($concount >= 2 && $game[name] != "Syndicates Testumgebung") { // TODO
			header("Location: index.php?action=error&code=83647");
			exit();
		}
	}


	## Bis hierhin alles Ok -> weiter gehts!

	#####
	#####	AB HIER WERDEN DIE GANZEN RAHMENBEDINGUNGEN GEPR?FT UND FEHLER BEI DER ANMELDUNG ERKANNT;
	#####
	#####

	################### Maximalanzahl Spieler festlegen, momentan Anmeldungen bis 5000 Spieler #####################

		$anzahl_konzerne = single("select count(alive) from status where alive=1 or alive=2");
		if ($anzahl_konzerne >= MAXSPIELERANZAHL) {	$barrier=1;	$ammounterror=1;	};

	// Falls Validationkey da ist, werden hier jetzt die gespeicherten Daten aus der DB geholt.
	if ($validationkey && single("select count(*) from anmeldung_tempsafe where validationkey='$validationkey'")) {
		list($username, $email, $password, $password2, $race, $rulername, $syndicate, $vorname, $nachname, $valtime, $mentorenprogramm) = row("select username, email, password, password2, race, rulername, syndicate, vorname, nachname, time, mentorenprogramm from anmeldung_tempsafe where validationkey='$validationkey'");
		if ($valtime + 1800 > $time): $validationkeysuccess = 1;
		else:	select("delete from anmeldung_tempsafe where validationkey='$validationkey'");
				header ("Location: index.php?action=anmeldung&timeerror=1"); exit(); endif;
		if (!$decisionchanged): $syndikatsmodus = "join"; else: $syndikatsmodus = "random"; endif;
	}
	elseif (!$validationkey and !$userid and FALSE) { # Anlegen eines User-Accounts seit Einf?hren des Emogames-Accounts hier nicht mehr m?glich
		$tuid = addslashes($tuid);
		$showncode = single("select code_id from codelogs_anmeldung where user_id='$tuid' and time > ($time-300) and action=0 order by time desc limit 1");
		if (!checkusercode($tuid,$showncode,$codeinput,"codes_anmeldung")) { $barrier = 1; $codeerror = 1; }
		select("delete from codelogs_anmeldung where user_id='$tuid'");
	}


	####	BEGINN Konzerndaten ?berpr?fen
	####
		################### Den eingegebenen Konzernnamen auf Richtigkeit pr?fen


		if (!checkSyndicate($syndicate)) { $barrier=1; $syndicateerror=1; }

		################### Den eingegebenen Gesch?ftsf?hrernamen auf Richtigkeit pr?fen
		//if (!checkRulername($rulername)) { $barrier=1; $rulernameerror=1; }

		################### Die ?bergebene Rasse auf Richtigkeit pr?fen
		if (!checkRace($race)) { $barrier=1; $raceerror=1; }

		################## AGB CHecked ?
		if (!checkAgb()) {$barrier = 1; $agberror=1;}
		
		################### Syndikatscheck
		if ($syndikatsmodus == 'join' && $validationkeysuccess)	{
			$syndikatsid = floor($syndikatsid);
			list($syndpassword, $synd_id, $open, $synd_type) = row("select password, synd_id, open, synd_type from syndikate where synd_id='$syndikatsid'");
			if ($synd_id)	{
				if ($synd_type != "normal" && ($userdata[startround] != $globals[round] and $userdata[createtime] + 4 * 7 * 24 * 3600 < $time)) { $barrier = 1; $syndikatserror = 4; } # Checken bei Syndikatsjoin ob der Join ok ist (normale spieler d?rfen keinen Anf?ngersyndikaten beitreten
				if ($open or TRUE) { # War kurzfristig in Runde 12 so gedacht, dass geschlossenen Syndikaten nicht mehr beigetreten werden kann
					if ($syndpassword && $syndpassword == $syndikatspw) {
						$number = single("select count(*) from status where rid=$syndikatsid");
						if ($number >= MAX_USERS_A_GROUP) { $barrier=1; $syndikatserror=3; }

					}
					else { $barrier=1; $syndikatserror=2; }
				} else { $barrier = 1; $openerror = 1; }
			}
			else { $barrier=1; $syndikatserror=1; }
		}


		################### ?BERPR?FEN OB KONZERN MIT DENSELBEN DATEN SCHON VORHANDEN IST
		
		if (syndicateexists($syndicate)) { $barrier=1; $syndicateexist=1;}
		
		if (!$userid) {$barrier=1; $sessionerror=1;}

	####
	####	ENDE Konzerndaten ?berpr?fen

	#### Wenn bis hierhin Fehler aufgetreten sind wird das Skript abgebrochen und der User auf die Anmeldung zur?ckgeleitet und ?ber
	#### die aufgetretenen Fehler informiert

	if ($barrier)	{

		$optional = $roundstatus == 1 ? "&smodus=$syndikatsmodus" : "";

		$error="index.php?action=anmeldung$optional&syndicate=".urlencode($syndicate)."&rulername=".urlencode($rulername)."&race=$race&servertype=".urlencode($servertype);
		if ($username): $error .= "&username=".urlencode($username).""; endif;
		if ($email): $error .= "&email=".urlencode($email).""; endif;
		if ($rulernameerror) {$error.="&rulernameerror=1";}
		if ($syndicateerror) {$error.="&syndicateerror=1";}
		if ($emailerror) {$error.="&emailerror=1";}
		if ($raceerror) {$error.="&raceerror=1";}
		if ($usernameerror) {$error.="&usernameerror=1";}
		if ($passworderror) {$error.="&passworderror=1";}
		if ($emailexist) {$error.="&emailerror=2";}
		if ($usernameexist) {$error.="&usernameerror=2";}
		if ($syndicateexist) {$error.="&syndicateerror=2";}
		if ($iperror) {$error.="&iperror=1";}
		if ($ammounterror) {$error.="&ammounterror=1";}
		if ($vornameerror) {$error.="&vornameerror=1";}
		if ($nachnameerror) {$error.="&nachnameerror=1";}
		if ($password2error) {$error.="&password2error=1";}
		if ($codeerror) {$error.="&codeerror=1";}
		if ($usernamespaceerror) {$error.="&usernamespaceerror=1";}
		if ($openerror) { $error .= "&openerror=1"; }
		if ($agberror) {$error.="&agberror=1";}
		if ($sessionerror) {$error.="&sessionerror=1";}
		// Syndikatserror tritt erst in "2. Stufe" auf, daher Abgrenzung und $error-Neudefinition
		if ($syndikatserror) {$error="index.php?action=anmeldung&validationkey=$validationkey&syndikatserror=$syndikatserror&syndikatsid=$syndikatsid";}

		header ("Location: $error"); exit();
	}

	#####
	#####	AB HIER DANN DEN ACCOUNT ANLEGEN - ALLE PR?FUNGEN ?BERSTANDEN
	#####
	#####

	elseif (!$barrier)	{	# BEGINN elsif (!$barrier)

		if ($syndikatsid && $syndikatsmodus == "join" && $roundstatus == 1 && $validationkeysuccess) { $rid = $syndikatsid; select("delete from anmeldung_tempsafe where validationkey='$validationkey'");}
		elseif ($syndikatsmodus == "join" && !$validationkey) {
			$validationkey = createkey("", "20");
			select("insert into anmeldung_tempsafe (time, validationkey, username, email, password, password2, rulername, syndicate, race, vorname, nachname, mentorenprogramm) values ($time, '$validationkey', '$username', '$email', '$password', '$password2', '$rulername', '$syndicate', '$race', '$vorname', '$nachname', '$mentorenprogramm')");
			header ("Location: index.php?action=anmeldung&validationkey=$validationkey&servertype=$servertype"); exit();
		}
		
		// gezieltes Nachjoinen ist möglich wenn man in der Gruppe war
		$group = assoc("SELECT * FROM groups_new WHERE group_id = (SELECT m.group_id FROM groups_new_members AS m WHERE m.user_id = '".$userid."' AND m.status = 1)");
		if ($group && 0 < $group['nachzuegler_max']) {
			$rid = $group['current_rid'];
			$group['self_joined_later'] = true;
		}
		
		if(!$rid){
			$rid = get_an_empty_syndicate();
		}


		# konzern erstellen
		if ($roundstatus != 2)	{
			
			$reg_by_koins = -1;
			if (isKsyndicates()) {$reg_by_koins = 1;}
			
			// Track registered user
			if (isBasicServer($game)) {
			 omniput("Neuer_Konzern_Basic",1,$time);
			}
			else {
			 omniput("Neuer_Konzern_Classic",1,$time);
			}
			
			if ($reg_by_koins == 1) {
			 omniput("Neuer_Konzern_Basic_Von_Krawall",1,$time);
			}
			
			if ($userid) {
				select("update users set reg_by_koins = $reg_by_koins where id=$userid");
			}
			else {
				echo "Error creating account - no userid given.";
				exit(1);
			}
			
			$ctime=$time;
			$ltime=$ctime;
			
			$may_access_boards = 1;
			if ($roundstatus == 0): $ctime=$roundstarttime; $ltime=$roundstarttime; endif;

			$utime=$ctime+PROTECTIONTIME;
			
			// Runde 65 der Geschäftsführer ist IMMER der Emoname
			$rulername=$username;
			if(ISRANDOMRUNDE){
				//$double=single("select count(*) from status where syndicate='$username'");
				$syndicate=$username;//.($double==0 ? '' : $double); //realnick zwang für random runde
			}
				
			$isnoob = ""; $isnoob_value = "";
			if ($roundstatus == 1) {
				nachricht_senden($rid, 47, array($rulername, $syndicate), 'syndikat', 1);
				towncrier($rid, "Der Spieler <b>$rulername</b> von <b>$syndicate</b> tritt Ihrem Syndikat bei.", 1,1);
				$may_access_boards = 0; // hebt sich nach 24h wieder auf
			}

			// Tage seit Rundenstart ermitteln f?r den in Runde 34 eingef?hrten Bonus bei sp?ter gestarteten Konzernen
			$days_since_roundstarttime = floor(($time - $globals['roundstarttime']) / 86400);
			$days_since_roundstarttime = max(0, $days_since_roundstarttime); // verhindert negative Werte
			
			select("insert into status (lastupdatetime,race,rid,rulername,syndicate,createtime,unprotecttime,lastlogintime,may_access_boards".
			$isnoob.
			($days_since_roundstarttime > 0 ? ",later_started_bonus":"").
			$ismentor.
			$joinedMentorProgramme1.
			") values ($time,'$race','$rid','$rulername','$syndicate','$ctime','$utime','$ltime','$may_access_boards'".
			$isnoob_value.
			($days_since_roundstarttime > 0 ? ",".($days_since_roundstarttime*LATER_STARTED_BONUS_DAILY+LATER_STARTED_BONUS_START):"").
			$ismentorvalue.
			$joinedMentorProgramme2.
			");");
			
			$konzernid = single("select id from status where syndicate='$syndicate'");
			
			//R48 - Benachrichtigung des Präsidenten, dass er Nachzügler freischalten muss.
			if($may_access_boards == 0) {
				$president = single("select president_id from syndikate where synd_id = ".$rid);
				if($president) select("insert into messages (user_id,sender,`time`,betreff,message) values (".$president.", ".$konzernid.",".time().",'Systemnachricht','Der Spieler $rulername von $syndicate ist soeben Ihrem Syndikat beigetreten und aktuell noch für die zugehörigen Foren gesperrt. Er würde sich aber sicher sehr freuen, wenn Sie ihm <a href=\"syndboard.php?action=manageaccessrights\">hier</a> Zugiffsrechte geben würden.')");
			}
			
			// Nachzuegler der Gruppen aktualisieren
			if ($group['self_joined_later']) {
				select("UPDATE groups_new SET nachzuegler_max = nachzuegler_max - 1 WHERE group_id = '".$group['group_id']."'");
			}
			
			// ID der Foreneinträge von letzter Runde im Gruppenboard aktualisieren
			$alte_konzernid = single("SELECT konzernid FROM stats WHERE round = '".($globals['round']-1)."' AND user_id = '".$userid."'");
			if ($alte_konzernid) {
				select("UPDATE board_messages AS m SET m.kid = '".$konzernid."' WHERE kid = ".(BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET+$alte_konzernid));
				select("UPDATE board_subjects AS s SET s.kid = '".$konzernid."' WHERE kid = ".(BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET+$alte_konzernid));
				select("UPDATE board_subjects AS s SET s.lastposter = '".$konzernid."' WHERE lastposter = ".(BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET+$alte_konzernid));
				//select("UPDATE board_subjects_new AS s SET s.kid = '".$konzernid."' WHERE kid = ".(BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET+$alte_konzernid));
				//select("UPDATE board_boards_lastklick AS b SET b.kid = '".$konzernid."' WHERE kid = ".(BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET+$alte_konzernid));
			}
			
			EMOGAMES_update_syndicates_konzernid($userdata[emogames_user_id],$konzernid,$reg_by_koins);
		}
		else { $konzernid=0; }
		
		select("delete from $statstable where user_id='$userid' and round=$round");
		select("update users set konzernid=$konzernid,lastroundplayed=$round where id=$userid");
		
		if ($roundstatus != 2)	{
			# in statstable eintragen
			select("insert into $statstable (user_id,konzernid,username,syndicate,race,rulername,rid,round,isnoob) values ('$userid','$konzernid','$username','$syndicate','$race','$rulername','$rid','$round',".($stats_isnoob ? $stats_isnoob : 0).")");
			# Settings f?r den Konzern anlegen
			select("insert into settings (kategorie,description,id) values ('','',$konzernid)");
		}

		## Verification mail verschicken
		## Gleichzeitig auch Willkommensmail mit Accountdaten

		verification($userid, "createkonzern");

		## Jetzt noch dem User einen Cookie setzen, damit er eingeloggt ist, falls es eine Accountneuanmeldung war

		if ($verification == 1)		{
			$autologinkey = createkey($userdata[id]);
			select("update users set autologinkey='$autologinkey' where id='$userid'");
			login($userid,60);
			setcookie ("autologinkey","$autologinkey", ($time+60*60*24*300),"");
		}

		// F?r den ersten Login einen Code erstellen und automatisch an Login.php ?bergeben
		$code_id = getcodeid();
		showusercode($konzernid,$code_id);
		$code = single("select code from codes where code_id=$code_id");
		## Und ab ins Spiel mit dem neuen User / neuen Konzern :)
		header ("Location: php/login.php?codeinput=$code"); exit();
	}	# ENDE elsif (!$barrier)
} # ENDE if ($roundstatus != 2)
elseif ($roundstatus != 2)	{ header ("Location: index.php?action=error&code=12345"); exit(); }
ob_flush();





function checkSyndicate($syndicate) {
	if (strlen($syndicate) < 3 or strlen($syndicate) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_&., ]/", $syndicate)) {
		return 0;
	}
	return 1;
}


function checkRulername($rulername) {
	if (strlen($rulername) < 3 or strlen($rulername) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_ ]/", $rulername)) {
		return 0;
	}
	return 1;
}

function checkRace($race) {
	global $races;
	if(!in_array($race,$races)) {return 0;}
	return 1;
}

function syndicateexists($syndicate) {
	return ($temp_count = single("select count(*) from status where syndicate='$syndicate'"));
}

function checkAgb() {
	global $agb;
	if ($agb != "on") {
		return 0;
	}
	return 1;
}

		  function findFreeMentorGroup() {
		    global $mentoren;
		    $m = $mentoren;
		    foreach (range(1, MAX_USERS_A_SYNDICATE) as $vl) { $users .= "u$vl,";} $users = chopp($users);
		    while (count($m) >= 1) {
		      $this_mentor_id = $m[mt_rand(0, count($m)-1)];
		    
		      $groupid = single("select group_id from groups where is_mentor_group = 1 and u1 = $this_mentor_id");
		      if ($groupid) {
			$result = assoc("select $users from groups where group_id=$groupid");
			foreach (range(1,MAX_USERS_A_SYNDICATE) as $vl) {
				if ($result[u.$vl])	{ 1; }
				elseif (!$freeplace)	{ $freeplace = $vl; }
			}
		      }
		      if ($freeplace) return array($groupid, $freeplace, $this_mentor_id);
		      else {
			// nicht gefunden
			$mnew = array();
			foreach ($m as $vl) {
			  if ($vl != $this_mentor_id) $mnew[] = $vl;
			}
			$m = $mnew;
		      }
		    }
		    return array(0,0,0);
		  }
		  function findFreeMentor() {
		    global $mentoren;
		    $m = $mentoren;
		    while (count($m) >= 1) {
		      $this_mentor_id = $m[mt_rand(0, count($m)-1)];
		      $mentor_rid = single("select rid from status where id = $this_mentor_id");
		      $mentor_synmembers_count = single("select count(*) from status where rid = $mentor_rid and alive > 0");		    
		      if ($mentor_synmembers_count < MAX_USERS_A_SYNDICATE) {
			return array($mentor_rid, $this_mentor_id);
		      }
		      else {
			// nicht gefunden
			$mnew = array();
			foreach ($m as $vl) {
			  if ($vl != $this_mentor_id) $mnew[] = $vl;
			}
			$m = $mnew;
		      }
		    }
		    return array(0,0);
		  }

?>
