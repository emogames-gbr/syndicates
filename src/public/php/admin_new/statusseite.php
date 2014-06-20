<?

//**************************************************************************//
//							Übergabe Variablen checken
//**************************************************************************//

$init = (int) $init;
if ($gaction && $gaction != "create" && $gaction != "leave" && $gaction != "joingroup" && $gaction != "changepass" && $gaction != "kick" && $gaction != "nachfolger"): $gaction = ""; endif;
if ($ia && $ia != "finish" && $ia != "confirm"): $ia = ""; endif;
if ($groupid): $groupid = floor($groupid); endif;
if ($place && ($place < 2 or $place > MAX_USERS_A_GROUP)): $place = 0; endif;

//**************************************************************************//
//							Dateispezifische Finals deklarieren
//**************************************************************************//



//**************************************************************************//
//							Variablen initialisieren
//**************************************************************************//

$queries = array();

$player_name = "";		//Spielername
$player_konzern_name = "";		//Firmaname
$player_syndicate_name = "";	//Syndikatsname

$general_announcement="";		//generelle Ankündigung
$president_announcement = "";	//Ankündigung des Päsidenten
$president_announcement_changetime = "";	//Ankündigung des Päsidenten
$print_president_announcement="";

$land_prod = 0;			//Land in Produktion

$spies = 0;			//Anzahl der Spione
$spies_prod = 0;			//Anzahl der Spione in Produktion
$spies_market = 0;

$units = 0;			//Anzahl der Militäreinheiten
$units_prod = 0;			//Anzahl der Militäreinheiten in Produktion
$units_away = 0;			//Anzahl der Militäreinheiten auf Heimkehr
$units_market = 0;

$buildings=0;			//Anzahl der Gebäude
$buildings_prod = 0;		//Anzahl der Gebäude in Produktion

$res_prod_money = 0;		//Produktion - Geld
$res_prod_ore = 0;		//Produktion - Erz
$res_prod_fp = 0;			//Produktion - Forschungspunkte

$res_prod_energy = 0;		//Produktion - Energie
$res_loss_energy = 0;		//Energieverbrauch
$res_prod_energy_final = 0;		//Energieproduktion gesammt

$moneyadd = 0;
$metaladd = 0;
$energyadd = 0;
$sciencepointsadd = 0;

$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
$weiter = "<br><br><a href=statusseite.php class=linkAufsiteBg>weiter</a>";

//**************************************************************************
//							Game.php includen						
//**************************************************************************


require_once("../../inc/ingame/game.php");
require_once(LIB."/js.php");
require_once("../../inc/ingame/header.php");
$tpl->assign("ripf",$ripf);

//START Spruch des Tage by Christian
$spruch=assocs("SELECT * FROM spruch_des_tages","id");
//hier einfach Sprüche hinzu schreiben
srand((double)microtime()*1000000);
if(TRUE){
	$zufallsIndex = rand(0,count($spruch)-1);
	echo "index: ".(count($spruch)-1)." spruch: ".$spruch[$zufallsIndex][txt];
	$infomsg = "<span class=i aling=center><b>Spruch des Tages</b><br>".$spruch[$zufallsIndex][txt];
	$tpl->assign('INFO', $infomsg);
	$tpl->display('info.tpl');
}
//END Spruch des Tages

$ism = single("select is_mentor from users where konzernid = $id");
if ($ism) {
    select("update status set is_mentor = 1 where id = $id");
}

/* if ($game[name] == "Syndicates Testumgebung") {
  if ($togglementor) {
    $mentorflag = single("select is_mentor from users where konzernid = $id");
    if ($mentorflag) {
      select("update status set is_mentor = 0 where id = $id");
      select("update status set mentor_id = 0 where mentor_id = $id");
    } else {
      select("update status set is_mentor = 1 where id = $id");
    }
    select("update users set is_mentor = abs(is_mentor - 1) where konzernid = $id");
  }
  $setclassicflag = floor($setclassicflag);
  if ($setclassicflag) {
    $setclassicflag = $setclassicflag == -1 ? 0 : $setclassicflag;
    select("update users set may_play_on_classic = $setclassicflag where konzernid = $id");
  }
	//pvar($id,id);
	$userid = single("select id from users where konzernid=$id");
	//pvar($userid,userid);
	echo "<table bgcolor=white width=800 align=center><tr><td>";
	echo "_______________________<br>Alle Ausgabe zwischen den Linien ist NUR auf dem Testserver sichtbar<br>";
	echo "<br /><br />TESTSEITE: <a href=\"testconfig.php\">Vorerst einfach hier klicken!</a><br /><br />";
	echo "Mentorflag (1 == true, 0 == false): ".single("select is_mentor from users where konzernid=$id")."<br>";
	echo "Toggle Mentor: <a href=statusseite.php?togglementor=1>toggle now</a><br>";
	echo "Das Mentorflag ist dem User-Account zugeordnet. D.h. nach Konzernlöschung seid ihr immer noch Mentor (wenn das Flag 1 zeigt).<br>Testet damit bitte (Konzernneuerstellung) ob das mit den Gruppen so funktioniert (ab morgen(Mittwoch) 16:00 Uhr) (automatische Erstellung als Mentor, automatische Zuordnung in eine als Spieler)";
	echo "<br>Zum Testen sind die Werte für Syndikate im Moment so einegstellt: maximale Syndikatsgröße ".MAX_USERS_A_SYNDICATE.". Reservierte Plätze für jeden Mentor: ".MENTOR_SPACE_RESERVED." (wenn die Zahlen hier ungleich 4 und 1 sind, seid ihr auf dem Classic-Server, hier braucht ihr nichts bzgl. Mentorenprogramm testen, weil es das hier noch nicht gibt => Ab auf den Basic)";
	echo "<br>Bitte testet auch, dass Mentoren nie in ein Syndikat kommen können, in dem schon ein Mentor ist. Morgen(Mittwoch) abend testen wir dann noch
	das Startrundenskript, welches die Gruppen in Syndikate mischt.<br>Vorerst gilt: Rundenstatus <u>Runde läuft</u> bis morgen(Mittwoch) 16:00 Uhr; danach testen wir das mit den Gruppen und setzen den Rundenstatus auf <u>Anmeldephase</u>.";
	echo "<br>Im Moment also bitte testen: <br>-Mentor bekommt Nachricht wenn er einen Schützling erhält;<br>-Schützling kommt ins selbe Syndikat wie sein Mentor;<br>-Normale Anmeldungen können nicht den reservierten Platz belegen (d.h. sobald 3 Leute im Syndikat sind ist es voll mit den obigen Werten)<br>-Mentorenprogrammbeitritt geht nur auf dem Basic-Server<br>-Was passiert wenn euer Syndikat voll ist und ihr trotzdem einen Schützling bekommt?<br>-was euch sonst noch einfällt";
	echo "<br><br>Nächste Sache: Bitte testet die Anmeldemaske durch; Ihr habt momentan folgenden Wert für das 'may_play_on_classic'-Flag:".single("select may_play_on_classic from users where konzernid = $id").";<br>Wenn das auf 0 steht, dürft ihr euch nicht auf dem Classic anmelden können! Ihr könnt es hier umsetzen:<a href=statusseite.php?setclassicflag=-1>0</a><a href=statusseite.php?setclassicflag=1>1</a><a href=statusseite.php?setclassicflag=3>3</a>;";
	echo "<br>1 und 3 unterscheiden sich nur darin, dass jemand der durch einen Spieler geworben wurde (startet mit Flag auf 3) auf dem Basic-Server noch dem Mentorenprogramm beitreten darf, jemand der 1 hat aber nicht.; Es gibt noch einen 2. Wert, der momentan aber noch keine Auswirkungen hat: 2; 2 erhält jeder der durch Ranking sich das Recht, auf dem Classic-Server zu spielen erspielt hat; dort soll er dann irgendwann auch nochmal das Mentorenprogramm benutzen können. Den Wert 1 hat später jeder Spieler, der mind. 1 Runde auf dem Classic-Server <u>beendet</u> hat.";
	echo"<br><br>Achja: auf dem Testserver gibt es keine Anmeldebeschränkung von 2 pro Tag, ihr könnt euch also so oft löschen wie ihr wollt.<br>";
	$mentor = $status['mentor_id'];
	echo "<br>Hier seht ihr, ob ihr momentan einem Mentor zugeordnet seid: ".($mentor > 0 ? "<b>".single("select syndicate from status where id = $mentor")."</b>":"kein mentor zugeordnet")."<br>";
	echo "<br>Alle Ausgabe zwischen den Linien ist NUR auf dem Testserver sichtbar<br>_______________________<br>";
	echo "</td></tr></table>";
	
} */


//**************************************************************************
//**************************************************************************
//							Eigentliche Berechnungen!
//**************************************************************************
//**************************************************************************

// Daten für istp holen
$ressources = getresstats();
$resstats = $ressources;
	foreach ($resstats as $k => $value) {
		if ($value[type] != "money") {
			$resstats[$k][value] *= RESSTATS_MODIFIER;
		}
	}

$syndikat = assoc("select * from syndikate where synd_id = $status[rid]");



/*
pvar($globals[roundstarttime]);
pvar(time());
pvar((time() -$globals[roundstarttime]));
pvar((time() -$globals[roundstarttime])/60);
pvar((time() -$globals[roundstarttime])/(60*60));
pvar((time() -$globals[roundstarttime])/(60*60*24));
$weeks_played = ceil((round_days_played()+1)/ 7);
pvar($weeks_played);

pvar(round_days_played());
*/

//
// ISTP einstellen
//

/*
 * Zur Vergabe der Weihnachtsboni einfach die Tabellen der Produktivdatenbank leeren und hier die Timestamps anpassen.
 * Weihnachten 2009: 1261674000 bis 1261868400
 */
if(time() > 1261674000 && time() < 1261868400){
	$weihnachtsbonus = 1;	
}
else{
	$weihnachtsbonus = 0;
}
if ($weihnachtsbonus) {
	if (!single("select count(*) from weihnachtsbonus where id = $id")) {
		if ($what == "Cr" or $what == "MWh" or $what == "P" or $what == "t") {
			if (getServertype() == "basic") {
				switch ($what) {
					case "Cr": $column = "money"; $amount = 5000000; break;
					case "MWh": $column = "energy"; $amount = 4166666; break;
					case "P": $column = "sciencepoints"; $amount = 250000; break;
					case "t": $column = "metal"; $amount = 833333; break;
				}
			} else {
				switch ($what) {
					case "Cr": $column = "money"; $amount = 5000000; break;
					case "MWh": $column = "energy"; $amount = 4166666; break;
					case "P": $column = "sciencepoints"; $amount = 250000; break;
					case "t": $column = "metal"; $amount = 833333; break;
				}
			}
			select("update status set $column=$column+$amount where id = $id");
			select("insert into weihnachtsbonus (id) values ($id)");
			$beschr = "Dein Weihnachtsbonus in Höhe von ".pointit($amount)." $what wurde dir erfolgreich gutgeschrieben!<br><br>Das ganze Syndicates-Team bedankt sich für ein tolles Jahr 2009 und wünscht frohe Weihnachten :)";
			$tpl->assign("MSG", $beschr);
			$tpl->display("sys_msg.tpl");
			$status = getallvalues($id);
		}
		
		if (getServertype() == "basic") {
			if (!$what) {
				$beschr = "Bitte wähle deinen Weihnachtsbonus aus (der Wert entspricht dem Standard-Gegenwert von 5.000.000 Credits):<br><br><ul>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=Cr class=linkAufsiteBg>5.000.000 Credits</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=MWh class=linkAufsiteBg>4.166.666 MWh</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=P class=linkAufsiteBg>250.000 Forschungspunkte</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=t class=linkAufsiteBg>833.333 Erz</a>
				</ul>";
				$tpl->assign("MSG", $beschr);
				$tpl->display("sys_msg.tpl");
			}
		} else {
			if (!$what) {
				$beschr = "Bitte wähle deinen Weihnachtsbonus aus (der Wert entspricht dem Standard-Gegenwert von 5.000.000 Credits):<br><br><ul>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=Cr class=linkAufsiteBg>5.000.000 Credits</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=MWh class=linkAufsiteBg>4.166.666 MWh</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=P class=linkAufsiteBg>250.000 Forschungspunkte</a>
				<li><a href=statusseite.php?weihnachtsbonus=1&what=t class=linkAufsiteBg>833.333 Erz</a>
				</ul>";
				$tpl->assign("MSG", $beschr);
				$tpl->display("sys_msg.tpl");
			}
		}
	} else { 
		$errormsg = "Du hast deinen diesjährigen Weihnachtsbonus schon erhalten."; 
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}




if ($inneraction == "setistp" && array_sum(explode("|", $syndikat[creditforschung])) > 0) {
	$istp_res	 = htmlentities($istp_res,ENT_QUOTES);
	$ISTP_CHANGETIME = (get_hour_time($time) + BUILDTIME * 60 * $globals{roundtime} * (1 - buildtimemodifier()) - get_hour_time($time)) / 3600;
	
	if ($status[istp_production] == "none") {
		select("update status set istp_production = '$istp_res' where id = $status[id]");
		$status[istp_production] = $istp_res;
		$beschr = "Ihr Konzern wird mit Hilfe des <i>Inner Syndicate Trade Progams</i>  &nbsp;<b>".$ressources[$istp_res][name]."</b> erwirtschaften.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
	elseif ($status[istp_production] != $istp_res) {
		select("update status set istp_production = '$istp_res',istp_changetime=".$ISTP_CHANGETIME." where id = $status[id]");
		$status[istp_production] = $istp_res;
		$status[istp_changetime] = $ISTP_CHANGETIME;
		$beschr = "Ihr Konzern wird in ".$ISTP_CHANGETIME." Stunden mit Hilfe des <i>Inner Syndicates Trade Progams</i>  &nbsp;<b>".$ressources[$istp_res][name]."</b> erwirtschaften.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");	
	} else {
		$errormsg = "Diese Ressource ist bereits ausgewählt.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}
elseif ($inneraction == "unprotect" && !$dounprotect){
	$errormsg = "Mit dem Verlassen der Schutzzeit ermöglichst du es anderen Spielern dich anzugreifen, auszuspionieren und zu besetehlen. Bist du sicher, dass du die Schutzzeit verlassen möchtest?<br /><br /><center>
		<form id=\"commit_form\" action=\"statusseite.php\" method=\"post\">
		<input type=\"hidden\" name=\"inneraction\" value=\"unprotect\" />
		<input type=\"hidden\" name=\"dounprotect\" value=1 />
        <a href=\"statusseite.php\" class=\"LinkAuftableInner\">NEIN - ich denke nochmal drüber nach.</a><br><br>
        <a href=\"#\" onClick=\"document.getElementById('commit_form').submit();\" class=\"LinkAuftableInner\">JA - ich bin jung und brauch' den Bonus.</a></form></center>
	";
	$tpl->assign('ERROR', $errormsg);
	$tpl->display('fehler.tpl');
}
elseif ($inneraction == "unprotect" && $dounprotect){
	if($time >= $status['createtime']+21600 && $time < $status['unprotecttime']){
		select("update status set unprotecttime = ".$time." where id = $id");
		$status['unprotecttime'] = $time;
		$beschr = "Sie haben die Schutzzeit frühzeitig verlassen und erhalten dadurch in dieser Runde einen Produktionsbonus von ".(getUnprotectBonus($status)*100)."% auf Ihre Standardressource.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
	else{
		$errormsg = "Diese Aktion ist zur Zeit leider nicht möglich.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}
//Schutzzeit frühzeitig beenden
if($time >= $status['createtime']+21600 && $time < $status['unprotecttime']) {
	$tooltip="</td></tr><tr><td class=\"tableHead2\" width=\"300\"><b>Produktionsboni: ".(UNPROTECT_BONUS*100)."% pro Stunde</b></td></tr><tr><td class=\"tableInner1\">
	<b>Erz:</b> United Industries Corporation, New Economic Block<br /></td></tr><tr><td class=\"tableInner1\"><b>Energie:</b> Brute Force, Nova Federation<br /></td></tr><tr><td class=\"tableInner1\"><b>Forschungspunkte:</b> Shadow Labs<br /></td>";
	$tpl->assign('showUnprotectBox', true);
	$tpl->assign('unprotect_tooltip', getJsHelpTag($tooltip));
}


///// Gruppenoptionen, wenn Runde noch nicht gestartet ist
if ($globals[roundstatus] == 0) { // && !isBasicServer($game))	{

	$tpl->assign("showGroups",true);
	# Standard Group Optionen
	$groupoptionen[create] = 1;			$groupoutput[create] = "Eine Gruppe erstellen";
	$groupoptionen[joingroup] = 1;		$groupoutput[joingroup] = "Einer Gruppe beitreten";
	$groupoptionen[leave] = 0;			$groupoutput[leave] = "Die Gruppe verlassen";
	$groupoptionen[changepass] = 0;		$groupoutput[changepass] = "Passwort ändern";
	/*$groupoptionen[openclose] = 0;		$groupoutput[openclose] = "Gruppe öffnen/schließen";*/

	$groupoptionen[kick] = 0;			$groupoutput_ss[kick] = "ausschließen";
	$groupoptionen[nachfolger] = 0;		$groupoutput_ss[nachfolger] = "als Nachfolger";
	
////
//		Gruppenbelegungen

	foreach (range(1, MAX_USERS_A_GROUP) as $vl){
		$users .= "u$vl,";}
	$users = chopp($users);
	
	$result = assoc("select * from groups where $id in ($users)");
	$tpl->assign("group_number",$result[group_id]);
	if ($result) {
		$groupoptionen[create] = 0;
		$groupoptionen[joingroup] = 0;
		$groupoptionen[leave] = 1;
		if ($result[u1] == $id)	{ 
			$cheffe = 1; 
			$groupoptionen[changepass] = 1; 
			/*R46 $groupoptionen[openclose] = 1;*/ 
			$groupoptionen[kick] = 1; 
			$groupoptionen[nachfolger] = 1;
		}
		foreach (range(1,MAX_USERS_A_GROUP) as $vl)	{ if ($result[u.$vl])	{ $users2 .= $result[u.$vl].","; } }
		$users2 = chopp($users2);

		$tpl->assign("group_adminadd", ($cheffe ? " als Administrator":""));
		$i=1;
		$playerresult = assocs("select id, syndicate, race, alive, lastlogintime from status where id in ($users2)", "id");
		
		$mitteilungslink_prepare = "mitteilungen.php?action=psm";
		$tpl_groupmember = array();
		
		foreach (range(1,MAX_USERS_A_GROUP) as $vl) {
			if ($result["u".$vl]){
				$tpl_memb = array();
				if($result["u".$vl."_status"] == 0){
					$trace = $playerresult[$result[u.$vl]][race];
					$alive = $playerresult[$result[u.$vl]][alive];
					$actualplayer = $playerresult[$result[u.$vl]][syndicate];
					if ($id == $playerresult[$result[u.$vl]][id]): $ownplace = $vl; endif;
					if ($trace == "pbf") {$trace ="<img src=\"".$ripf."bf-logo-klein.gif\">";}
					elseif ($trace == "uic") {$trace = "<img src=\"".$ripf."uic-logo-klein.gif\">";}
					elseif ($trace == "sl") {$trace = "<img src=\"".$ripf."sl-logo-klein.gif\">";}
					elseif ($trace == "neb") {$trace = "<img src=\"".$ripf."neb-logo-klein.gif\">";}
					elseif ($trace == "nof") {$trace = "<img src=\"".$ripf."nof-logo-klein.gif\">";}
					$online = "";
					$sessidsactual = assoc("select user_id, gueltig_bis from sessionids_actual where user_id=".$result[u.$vl]);
					if ($time < $sessidsactual["gueltig_bis"]){
						$online = "<img src=\"".$ripf."_online.gif\" border=0 align=\"absmiddle\">";
					}
					elseif($playerresult[$result["u".$vl]]["lastlogintime"] + TIME_TILL_GLOBAL_INACTIVE < $time && $playerresult[$result["u".$vl]]["alive"] != 2){
						$online = "<img src=\"".$ripf."_gl_inaktiv.gif\" border=0 align=\"absmiddle\">";
					}
					else if($playerresult[$result["u".$vl]]["lastlogintime"] + TIME_TILL_INACTIVE < $time && $playerresult[$result["u".$vl]]["alive"] != 2){
						$online = "<img src=\"".$ripf."_lokal_inaktiv.gif\" border=0 align=\"absmiddle\">";
					}
					else{
						$online = "<img src=\"".$ripf."_offline.gif\" border=0 align=\"absmiddle\">";
					}
					$hint = "";
				}
				else{
					$hint = " style='font-style:italic; opacity:0.7;' title='Spieler hat sich noch nicht angemeldet, aber sein Platz ist reserviert.'";
					$actualplayer = single("select username from users where emogames_user_id = ".$result["u".$vl]);
					$online = "";
					$trace = "n/a";
				}
				if ($actualplayer and $alive == 1) {
					if ($i < 10): $o = "0$i"; else: $o = $i; endif;
					$mitteilungslink = $mitteilungslink_prepare . "&rec=".$result[u.$vl];
					$mitteilungslink = "<a href=$mitteilungslink><img src=\"".$ripf."_syn_message_letter.gif\" border=0></a>";
					if ($id == $result[u.$vl] || $result["u".$vl."_status"] == 1){
						$mitteilungslink = "";
						$class = "tableInner2";
					}
					else{
						$class = "tableInner1";
					}
					$tpl_memb[0] = $hint;
					$tpl_memb[1] = $class;
					$tpl_memb[2] = $o;
					$tpl_memb[3] = $trace;
					$tpl_memb[4] = $actualplayer;
					$tpl_memb[5] = $online;
					$tpl_memb[6] = $mitteilungslink;
					$tpl_memb[7] = (($cheffe && $vl == 1) ? "</td><td class=tableInner1>&nbsp;":"");
					$tpl_memb[8] = (($cheffe && $vl > 1) ? "</td><td class=tableInner1><a href=statusseite.php?gaction=kick&place=$vl class=linkAuftableInner>".$groupoutput_ss[kick]."</a>, " : "");
					$tpl_memb[9] = (($cheffe && $vl > 1) ? (($result[nachfolger] == $vl) ? "<font class=highlightAuftableInner>Nachfolger</font>":"<a href=statusseite.php?gaction=nachfolger&place=$vl class=linkAuftableInner>".$groupoutput_ss[nachfolger]."</a>") : "");
					$i++;
					array_push($tpl_groupmember,$tpl_memb);
				}
				elseif ($alive == 0 && !$result["u".$vl."_status"])	{
					select("update groups set u".$vl."='0', u".$vl."_status='0' where group_id='".$result["group_id"]."'");
				}
			}
		}
		$tpl->assign("groupmember",$tpl_groupmember);	
	}
	
	$tpl_groupopt = array();
	foreach ($groupoutput as $ky => $vl)	{
		if ($groupoptionen[$ky])	{
			$tpl_opt[0] = $ky;
			$tpl_opt[1] = $vl;
			array_push($tpl_groupopt,$tpl_opt);
		}
	}
	$tpl->assign("groupoption",$tpl_groupopt);
//
////

}
///// Gruppenoptionen Ende

//							selects fahren

$syndvalues = getsyndvalues();
$player_syndicate_name = $syndvalues[name];
if ($globals[roundstatus] == 0): $player_syndicate_name = "Runde noch nicht gestartet"; endif;
$president_announcement = $syndvalues[announcement];
$president_announcement_changetime = $syndvalues[announcement_lastchangetime];
$notes = html_entity_decode(single("select text from notes where user_id ='".$status[id]."'"));

$spies = (int) spiestotal($id,1);				//cast wg ( 0 = "")
$spies_prod = (int) spiestotal($id,2);
$spies_market = (int) spiestotal($id,4);

$units = (int) miltotal($id,1);
$units_prod = (int) miltotal($id,2);
$units_away = (int) miltotal($id,3);
$units_market = (int) miltotal($id,4);

$underconstruction = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name != 'land'");
$underconstruction = (int) $underconstruction;

$allbuildings = getallbuildings($status{id});
$allbuildings = (int) $allbuildings;

$freeland = freeland($status);


list($moneyadd, $moneylageradd, $hpmoneyadd) = moneyadd($status{id});
list($metaladd, $metallageradd, $hpmetaladd) = metaladd($status{id});
list($sciencepointsadd, $sciencepointslageradd, $hpsciencepointsadd) = sciencepointsadd($status{id});

list($energyadd, $energylageradd, $hpenergyadd) = (energyadd($status{id}));


$maxsave = (energyadd($status{id},3));
if($energyadd==0)
	$energyhours = -1;
else
	$energyhours = -$status{energy}/$energyadd; //solang reicht die nrg noch
	
if($energyhours >= 2)
	$nrgtick=" Ticks";
else
	$nrgtick=" Tick";

if($energyadd >= 0) {
  $criticalenergy2 = "";
} else {
  $criticalenergy2 = pointit($energyhours)." ".$nrgtick;
}
($status{energy} + $energyadd >= $maxsave) ? $maxsave_reached = "&nbsp;<b class=highlightAuftableInner>*</b>" : 1;
($status{energy} + $energyadd >= $maxsave) ? $warner = "<tr><td colspan=3 align=right><b class=highlightAuftableInner>*</b> <strong class=\"achtungAuftableInner\">Lagerkapazitäten erschöpft</strong><br>&nbsp;</td></tr>" : 1;
($status{energy} + $energyadd < 0) ? $critical = $criticalenergy1 = "<font class=highlightAuftableInner> * &#189;</font>" : 1;

$energyadd = $energyadd;
$energyprod = energyadd($status{id},1);
$energyuse = energyadd($status{id},2);

// Selects für nächste aktion
$tables = array(
	"build_buildings" => "buildings",
	"build_military" => "military_unit_settings",
	"build_spies" => "spy_settings",
	"build_sciences" => "sciences",
);

$na_info = array(); // nextaction info
$mintim = 0;
foreach ($tables as $k => $v) {
	$numsum = ",sum(number) as number";
	if ($k == "build_sciences") $numsum = "";
	$idtype = ",unit_id";
	if ($k == "build_buildings") $idtype = ",building_id";
	if ($k == "build_sciences") $idtype = "";
	$temp = assoc("select *$numsum from  $k where user_id = $status[id] group by time$idtype order by time asc limit 1");
	if ($temp[time] && $temp[time] < $mintime || !$mintime) {
		$mintime = $temp[time];
		$na_info[time] = $temp[time];
		$na_info[values] = $temp;
		$na_info[table] = $v;
		$na_info[typetable] = $k;
	}
}

switch ($na_info[table]) {

	case "sciences": 
		$na_info[name] = single("select gamename from sciences where concat(name,typenumber) = '".$na_info[values][name]."'");break;
		
	case "military_unit_settings":
		$na_info[name] = single("select name from military_unit_settings where unit_id = '".$na_info[values][unit_id]."'"); break;
		
	case "spy_settings":
		$na_info[name] = single("select name from spy_settings where unit_id = '".$na_info[values][unit_id]."'");break;
	
	case "buildings":
		if ($na_info[values][building_id] == "127") {
			$na_info[name] = "Land";
			break;
		}
		$na_info[name] = single("select name from buildings where building_id = '".$na_info[values][building_id]."'");break;

}
$na_info[left] = $na_info[time] - $time;
if (!$na_info[name]) $na_info[name] = "Keine Produktion";
if ($time < $globals[roundstarttime]) {
	$na_info = array(); // Neu initialisieren, damit vor Rundenstart Nummer und Typ nicht angezeigt werden
	$na_info[name] = "<span style=\"font-size:13px\">Runde noch nicht gestartet</span>";
}

//							Berechnungen
if ($init) {
	
	if (isKsyndicates()) {
		echo "
			<script language='JavaScript' type='text/javascript' src='http://ads.krawall.de/adx.js'></script>
			<script language='JavaScript' type='text/javascript'>
			<!--
			   if (!document.phpAds_used) document.phpAds_used = ',';
			   phpAds_random = new String (Math.random()); phpAds_random = phpAds_random.substring(2,11);
			   
			   document.write (\"<\" + \"script language='JavaScript' type='text/javascript' src='\");
			   document.write (\"http://ads.krawall.de/adjs.php?n=\" + phpAds_random);
			   document.write (\"&amp;what=zone:68\");
			   document.write (\"&amp;exclude=\" + document.phpAds_used);
			   if (document.referrer)
			      document.write (\"&amp;referer=\" + escape(document.referrer));
			   document.write (\"'><\" + \"/script>\");
			//-->
			</script>		
		";
	}
	
    $global_announcement = assoc("select * from announcements where (type='ingame' or type='both') and time >= ".($time-7*86400)." order by time desc limit 1");
	if (strlen($global_announcement[content]) > 5) {
		$tpl->assign("showGlobalNews", true);
		$tpl->assign("global_poster", $global_announcement[poster]);
		$tpl->assign("global_headine", $global_announcement[headline]);
		$tpl->assign("global_content", $global_announcement[content]);
		$tpl->assign("global_time", mytime($global_announcement[time]));
	}
}

if ($globals[roundstatus] == 1) {
	$tpl->assign("showVotesAdds",true);
	$hourtime = get_hour_time($time);
	$hour = date("H",$time);
	$daytime = $hourtime - $hour*60*60;
	$clickcount1 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $hourtime group by type", "type");
	$clickcount24 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $daytime group by type", "type");
	$bonuscount = $status[land]*30;
	$bonuscount -= $clickcount24[1][count]*$status[land]*4;
	if ($bonuscount < 0) {$bonuscount = 0;}

/*
					<A HREF=\"bonus.php?site=buecher&type=1\" target=\"_blank\" class=linkAuftableInner>Bücher</A> |
					<A HREF=\"bonus.php?site=musik&type=1\" target=\"_blank\" class=linkAuftableInner>Musik</A> |
					<A HREF=\"bonus.php?site=dvd&type=1\" target=\"_blank\" class=linkAuftableInner>DVDs</A> |

*/
	

	$votecounter = single("select count(*) from bonusklicks where time >= ".get_day_time($time)." and page = 'galaxy-news'");
	$galaxy_news_done = 0;
	$gamesdynamite_done = 0;
	$hourtime = get_hour_time($time);
	$hour = date("H",$time);
	$daytime = $hourtime - $hour*60*60;
	$voted_bonusklick = assocs("select page as link from bonusklicks where user_id = $id and time > ".(time() - 24*3600)); // Wird jetzt auch in game.php geholt weil in menu
	foreach ($voted_bonusklick as $vl) {
			if ($vl[link] == "gamesdynamite" ): $gamesdynamite_done = 1;
			elseif ($vl[link] == "galaxy-news"): $galaxy_news_done = 1;
			endif;
	}
	
	$tpl->assign("galaxy_news_done", $galaxy_news_done);
	$tpl->assign("galaxy_credits", ($status['land']*100));
	$tpl->assign("galaxy_votecounter", $votecounter);
	
	if (!($clickcount1[1][count] > 0)) {
		$tpl->assign("crboniclicks","click");
		$tpl->assign("crboni",pointit($bonuscount));
		$user = assoc("select * from users where konzernid = $status[id]");
		if ($time - $status[createtime] > 60*60*24 && $time - $user[createtime] > 60*60*24*3) { 
			$tpl->assign("isnewuser", false);
		}
		else {
			$tpl->assign("isnewuser", true);
		}
	}
	else {
		if ($bonuscount > 0) {
			$tpl->assign("crboniclicks","done");
			$bonuscount += $status[land]*4;
			$tpl->assign("crboni",pointit($bonuscount));
		}
	}

	$bonuscount = ceil($status[land]*0.001);
	if ($bonuscount > 5) $bonuscount = 5;
	
	$linkdata = assoc("select * from bonus_links where type = 2 order by klicks asc limit 1");
	if (!($clickcount1[2][count] > 0) && $clickcount24[2][count] < 5) {
		$tpl->assign("haboniclicks","click");
		$tpl->assign("halinkdataid",$linkdata[id]);
		$tpl->assign("halinkdatatext",$linkdata[linktext]);
		$tpl->assign("haboni",pointit($bonuscount));
	}
	else {
		if ($clickcount24[2][count] < 5) {
			$tpl->assign("haboniclicks","done");
			$tpl->assign("haboni",pointit($bonuscount));
		}
	}

	
	if (array_sum(explode("|", $syndikat[creditforschung])) > 0) {
		$tpl->assign("showTradeInfo",true);				
		$tpl_ress = array();
		foreach ($ressources as $key => $temp) 
		{
			$tpl_res=array();
			if(	
				( $key == $status[istp_production] )						// wenn die Trade-Ressource mit der select-ressource übereinstimmt
				||
				(
					$key == "money" && $status['istp_production'] == "none"  // oder wenn die select-ressource creds ist und Trade auf none, also noch nicht gewählt, steht
				)
			)
			{
				$selected = "selected";		// wähle die entsprechende Ressource aus
			}
			else
			{
				$selected = "";				// ansonsten nicht
			}
			$tpl_res[0]=$key;
			$tpl_res[1]=$selected;
			$tpl_res[2]=$temp[name];
			array_push($tpl_ress,$tpl_res);
		}
		$tpl->assign("ressi",$tpl_ress);
		
		if ($status[istp_changetime] > 0) {
			$tpl->assign("istp_changetime",$status[istp_changetime]);
		}
	}
}


if ($president_announcement) {
	if ($status[new_synannouncement] && !$adminlogin && $panm!=1) {
		$infomsg = "<br><br><center>Hinweis: <b>Der Präsident hat eine neue <a style=\"text-decoration:underline;font-size:12px;\" href=\"statusseite.php?panm=1\">Syndikatsankündigung</a> erstellt!</b></center><br>";
		$tpl->assign('INFO', $infomsg);
		$tpl->display('info.tpl');
	
	}

	if ($panm==1) {
		if ($status[new_synannouncement] && !$adminlogin) {
			$queriesend[] = "update status set new_synannouncement = 0 where id = $id";
		}
	}
	$tpl->assign('showInternNews', true);
	$tpl->assign('news_chars', strlen($president_announcement));
	$tpl->assign('news_time', datum("d.m.Y, H:i", $president_announcement_changetime));
	$tpl->assign('news_style1', ($panm == 1 ? "table-row;" : "none;"));
	$tpl->assign('news_style2', ($panm == 1 ? "" : "style=\"display:none;\""));
	$tpl->assign('news_text', umwandeln_bbcode($president_announcement));
	$tpl->assign('news_style3', ($panm == 1 ? "true" : "false"));
	$tpl->assign('news_style4', ($status[new_synannouncement] && !$adminlogin ? "document.location = \"statusseite.php?panm=1\";" : ""));
	
}

if ($features[KOMFORTPAKET]) {
		if ($status[notespin]==1){
			$tpl->assign("showNotice",true);
			$tpl->assign("notice",umwandeln_bbcode(htmlentities($notes)));
		}
}
        
$races = assoc("select * from races where race ='".$status{race}."'");
js::loadCountdown();

if ($globals[roundstatus] == 1) {
	$naechste_produktion=($na_info[name]  != "Keine Produktion" ?
		$na_info[values][number]." $na_info[name] in <br>
		".js::countdown($na_info[left],array(h,m,s))."</span></b></td>" : $na_info[name]);
}
else {
	$naechste_produktion="$na_info[name]</td>";
}

$tpl->assign("next_prod", $naechste_produktion);
$tpl->assign("wiki",WIKI);
$tpl->assign("geb_total",pointit($allbuildings+$underconstruction));
$tpl->assign("geb_da",pointit($allbuildings));
$tpl->assign("geb_inbau",pointit($underconstruction));
$tpl->assign("land_unbebaut",pointit($freeland));
$tpl->assign("land_total",pointit($status[land]));
$tpl->assign("land_inbau",pointit(getnumberoflandunderconstruction()));
$tpl->assign("mill_da",pointit($units));
$tpl->assign("mill_weg",pointit($units_away));
$tpl->assign("mill_markt",pointit($units_market));
$tpl->assign("mill_inbau",pointit($units_prod));
$tpl->assign("mill_total",pointit($units+$units_away+$units_market+$units_prod));
$tpl->assign("spy_da",pointit($spies));
$tpl->assign("spy_markt",pointit($spies_market));
$tpl->assign("spy_inbau",pointit($spies_prod));
$tpl->assign("spy_total",pointit($spies+$spies_market+$spies_prod));

$nameFn = array(
	'money' => 'Credits',
	'metal' => 'Erz',
	'sciencepoints' => 'Forschungspunkte',
	'energy' => 'Energie'
);
$bonusFn = array(
	'PARTNER_METALBONUS' => 'Partnerbonus',
	'PARTNER_ENERGYBONUS' => 'Partnerbonus',
	'PARTNER_SCIENCEPOINTSBONUS' => 'Partnerbonus',
	'PARTNER_MONEYBONUS' => 'Partnerbonus',
	'PRAESIBONUS' => 'Pr&auml;sidentenbonus',
	'ECO_ENERGY_BONUS' => 'Hoover-Staudamm',
	'ECO_CREDIT_BONUS' => 'Goldener Thron',
	'ECO_METAL_BONUS' => 'Moria',
	'ECO_SCIENCEPOINTS_BONUS' => 'Tempel der Meditation',
	'ECO_ALL_BONUS' => 'Tempel der Arbeit',
	'IND9' => 'Pure Capitalism',
	'IND1' => 'Better Ore Mining',
	'IND17' => 'Scientific Advances',
	'IND2' => 'Advanced Power Management',
	'PBF_ENERGYBONUS' => 'BF-Bonus',
	'PBF_SCIENCE_MALUS' => 'BF-Malus',
	'UIC_METAL_BONUS' => 'UIC-Bonus',
	'UIC_PAUSCHAL_RESSOURCENBONUS' => 'UIC-Bonus',
	'SL_SCIENCE_BONUS' => 'SL-Bonus',
	'SL_METAL_MALUS' => 'SL-Malus',
	'NOF_ENERGYBONUS' => 'NOF-Bonus',
	'NOF_CREDIT_MALUS' => 'NOF-Malus',
	'NEB_METAL_BONUS' => 'NEB-Bonus',
	'WCENTERBONUS' => 'Wirtschaftszentren',
	'SYNERGY_BONUS' => 'Synergiebonus',
	'UNPROTECTION_BONUS' => 'Schutzzeitbonus'
);

$tpl_bonis = array();
foreach(production($status['id']) as $name => $bonuses){
	$tpl_boni = array();
	$total = round(array_sum($bonuses),2);
	$tpl_boni[0] = $nameFn[$name];
	$tpl_boni[1] = array();
	foreach($bonuses as $bonusName => $bonusValue){
		$uni_boni = array();
		$v = round($bonusValue,2);
		$uni_boni[0] = $bonusFn[$bonusName];
    	$uni_boni[1] = $v;
		array_push($tpl_boni[1],$uni_boni);
	}
	$tpl_boni[2] = $total;
	array_push($tpl_bonis,$tpl_boni);
}
$tpl->assign("bonus",$tpl_bonis);
$tpl->assign("showBoniInfo",$tpl_bonis); //trick 17 :)

//prodd
$tpl->assign("criticalenergy1",$criticalenergy1);
$tpl->assign("criticalenergy2",$criticalenergy2);
$tpl->assign("moneyadd",pointit($moneyadd));
$tpl->assign("metaladd",pointit($metaladd));
$tpl->assign("sciencepointsadd",pointit($sciencepointsadd));
$tpl->assign("energyprod",pointit($energyprod));
$tpl->assign("energyuse",pointit($energyuse));
$tpl->assign("energyadd",pointit($energyadd));
$tpl->assign("maxsave_reached",$maxsave_reached);
$tpl->assign("warner",$warner);

if ($moneylageradd or $metallageradd or $sciencepointslageradd or $energylageradd)	{
	$tpl->assign("showStorageProduction",true);
	$tpl->assign("st_curr",$syndvalues[currency]);
	$tpl->assign("st_cr_x",pointit($moneylageradd));
	$tpl->assign("st_cr_hp",pointit($hpmoneyadd));
	$tpl->assign("st_nrg_x",pointit($energylageradd));
	$tpl->assign("st_nrg_hp",pointit($hpenergyadd));
	$tpl->assign("st_fp_x",pointit($sciencepointslageradd));
	$tpl->assign("st_fp_hp",pointit($hpsciencepointsadd));
	$tpl->assign("st_erz_x",pointit($metallageradd));
	$tpl->assign("st_erz_hp",pointit($hpmetaladd));
	if($hpmetaladd || $hpsciencepointsadd || $hpenergyadd)
		$tpl->assign("st_hp",pointit(($hpmetaladd+$hpmoneyadd+$hpenergyadd+$hpsciencepointsadd)));
}
if ($status[partnerschaften]) {

	$tpl->assign("showPartnerBoni",true);
	$partner_available = singles("select id from partnerschaften_settings where round=$globals[round]");
	$partner_settings = assocs("select id, bonus from partnerschaften_general_settings", "id");
	$tpl_partner = array();
	$tpl_partner_available = array();

	if ($partner) {
		foreach ($partner as $ky => $vl) {
			for ($i = 1; $i <= $vl; $i++) {
				array_push($tpl_partner, $partner_settings[$ky][bonus]);
			}
		}
	}

	$pdifferenz = -(array_sum($partner) - $status[partnerschaften]);

	if ($pdifferenz >= 1) {
		if($action != "setpartner"){
			$infomsg = "Sie können noch insgesamt $pdifferenz Partnerschaftsboni wählen! (<font onclick=\"javascript: document.location.href='#pboni'\" style=\"cursor:pointer; font-style:italic;\">siehe unten</font>)";
			$tpl->assign('INFO', $infomsg);
			$tpl->display('info.tpl');
		}

		foreach ($partner_available as $vl) {
			if (!$partner[$vl]) {
				$tpl_pb = array();
				$tpl_pb[0]=$vl;
				$tpl_pb[1]=$partner_settings[$vl][bonus];
				array_push($tpl_partner_available, $tpl_pb);
			 }
		}	
	}
	
	$tpl->assign("partner",$tpl_partner);
	$tpl->assign("partner_available",$tpl_partner_available);
	$tpl->assign("pdifferenz",$pdifferenz);
}


// Proectionausgabe
$PROTECT="";
if (in_protection($status)) {
    $difference = $status['unprotecttime'] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
    if (getServertype() == "classic" && $status[inprotection] == "Y") {
		$tpl->assign("showProtectInfo","config");
		$tpl->assign("prot_time",(PROTECTIONTIME/3600));
    } else {
		$tpl->assign("showProtectInfo","protection");
		$tpl->assign("prot_day",$days);
		$tpl->assign("prot_std",$hours);
		$tpl->assign("prot_min",$minutes);
    }
}

if (($globals[roundendtime] - $time <= 5 * 24 * 3600) && ($globals[roundendtime] - $time > 0)) {
	$difference = $globals[roundendtime] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign("showRoundInfo","end");
	$tpl->assign("rnd_day",$days);
	$tpl->assign("rnd_std",$hours);
	$tpl->assign("rnd_min",$minutes);
	$tpl->assign("rnd_date",date("d. M", $globals[roundendtime]));
	$tpl->assign("rnd_time",date("H:i", $globals[roundendtime]));

}
if ($globals[roundstarttime] - $time > 0) {
	$difference = $globals[roundstarttime] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign("showRoundInfo","start");
	$tpl->assign("rnd_day",$days);
	$tpl->assign("rnd_std",$hours);
	$tpl->assign("rnd_min",$minutes);
	$tpl->assign("rnd_date",date("d. M", $globals[roundstarttime]+3600));
	$tpl->assign("rnd_time",date("H:i", $globals[roundstarttime]+3600));
}

if ($action == "setpartner") {
	//unset($ausgabe);
	//$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
	//$weiter = "<br><br><a href=statusseite.php class=linkAufsiteBg>weiter</a>";
	$tochose = -(array_sum($partner) - $status[partnerschaften]);
	if ($tochose >= 1 && !$globals['updating']) {
		if (!$partner[$pid]) {
			$pid = floor($pid);
			$bonus = single("select id from partnerschaften_settings where id = $pid and round = $globals[round]");
			if (!$ia) {
				if ($bonus) {
					$infomsg = "<br><table width=80% class=i><tr><td><center>Möchten Sie wirklich folgenden Bonus wählen?<br><br>
					".$partner_settings[$bonus][bonus]."<br><br>
					<a href=statusseite.php?action=setpartner&pid=$pid&ia=confirm>JA</a> - <a href=statusseite.php>Abbrechen</a></center></td></tr></table>";
					$tpl->assign('INFO', $infomsg);
					$tpl->display('info.tpl');
				}
				else { 
					$errormsg = "Ungültigen Bonus gewählt!";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl'); 
				}
			}
			if ($ia == "confirm") {
				if ($bonus) {
					$barrier = 0;
					if ($pid == 21) {
						if (beschleunige_forschung(0, 1)) { // testen ob geforscht wird
							beschleunige_forschung(PARTNERBONUS_FORSCHUNG_BESCHLEUNIGEN);// Aktuelle Forschung beschleunigen;
						} else $barrier = 1;
					}
					if (!$barrier) {
						if ($partner[$pid]) {
							$queries[] = "update partnerschaften set level = ".($partner[$pid] + 1)." where user_id = $id and pid = $pid";
						}
						else {
							$queries[] = "insert into partnerschaften (user_id, pid, level) values ($id, $pid, 1)";
						}
						if ($pid == 22) {
							$queries[] = "update status set defspecs = defspecs + ".(PARTNERBONUS_DEFSPECS)." where id = $id";
						}
						if ($pid == 23) {
							$queries[] = "update status set offspecs = offspecs + ".(PARTNERBONUS_OFFSPECS)." where id = $id";
						}
						$beschr = "Sie haben den Bonus <b>\"".$partner_settings[$bonus][bonus]."\"</b> gewählt.";
						$tpl->assign("MSG", $beschr);
						$tpl->display("sys_msg.tpl");
					} else {
						$errormsg = "Sie können den Bonus zum Beschleunigen der Forschung erst wählen, wenn Sie eine Forschung erforschen. Sie erforschen zurzeit nichts.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
					}
				}
				else { 
					$errormsg = "Ungültigen Bonus gewählt!";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
		} else {
			$errormsg = "Sie haben diesen Bonus bereits gewählt.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
	}
	else if(!$globals['updating']) { 
		$errormsg = "Sie können keine weiteren Partnerschaftsboni wählen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');			
	}
	else { 
		$errormsg = "Momentan läuft das stündliche Update. Bitte warten Sie noch einen Augenblick und drücken Sie dann F5 oder laden Sie die Seite erneut.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		exit();
	}
}


///// Wenn Runde noch nicht läuft können einzelne Gruppenoptionen hier die Ausgabe wieder töten!
if ($globals[roundstatus] == 0) { // && !isBasicServer($game))	{
	if ($gaction)	{
		if ($globals[roundstarttime] - $time > 0 || true)	{
			//$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
			//$weiter = "<br><br><a href=statusseite.php class=linkAufsiteBg>weiter</a>";
			//unset($ausgabe);
			if ($gaction == "create")	{
				if ($groupoptionen[create])	{
					$infomsg = "<br><br><center>Bitte wählen Sie ein Gruppenpasswort (mind. 3 Zeichen):<br><br><form action=statusseite.php method=post><input type=hidden name=gaction value=create><input type=text size=9 maxlength=30 name=password><br><br><input type=submit value=weiter></form></center>";
					$tpl->assign('INFO', $infomsg);
					if ($password and preg_match("/^[[0-9a-zA-z]{3,30}$/", $password))	{
						//unset($ausgabe);
						$queries[] = "insert into groups (createtime, password, u1) values ($time, '$password', $id)";
						$beschr = "Sie haben erfolgreich eine Gruppe angelegt. Geben Sie Gruppennummer und Gruppenpasswort an Ihre Freunde weiter, um mit ihnen gemeinsam spielen zu können.<br>Die Gruppennummer wird Ihnen auf der Statusseite angezeigt.";
						$tpl->assign("MSG", $beschr);
						$tpl->display("sys_msg.tpl");
					}
					elseif ($password) { 
						$errormsg = "Das Passwort muss mindestens 3 und darf höchstens 30 Zeichen lang sein. Es sind nur kleine bzw. große Buchstaben von a-z und Zahlen von 0-9 erlaubt.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
						$tpl->display('info.tpl');
					} else {			
						$tpl->display('info.tpl');
					}
				}
				else {
					$errormsg = "Sie können keine neue Gruppe erstellen. Verlassen Sie zunächst Ihre Gruppe.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
			elseif ($gaction == "leave")	{
				if ($groupoptionen[leave])	{
					if (!$cheffe or $result[u.$result[nachfolger]] or $o <= 1)	{ #$o ist für anzahl der mitglieder in der gruppe, s.o.
						if ($result['is_mentor_group']) {
							$infomsg = "<br><br><center>ACHTUNG: Du hast bei der Konzernerstellung angegeben am Mentorenprogramm teilnehmen zu wollen. Du wurdest deshalb mit deinem Mentor dieser Gruppe zugewiesen. Wenn du diese Gruppe jetzt verlässt wirst du mit großer Sicherheit nicht im selben Syndikat landen wie dein Mentor. Wenn du also nicht genau weißt was du tust, raten wir dir vom Verlassen der Gruppe ab.<br><br>Möchtest Du deine Gruppe wirklich verlassen?<br><br><a href=statusseite.php?gaction=leave&ia=finish class=linkAufsiteBg>Bestätigen</a> - <a href=statusseite.php class=linkAufsiteBg>Abbrechen</a></center><br>";
							$tpl->assign('INFO', $infomsg);
							$tpl->display('info.tpl');
						} else {
							$infomsg = "<br><center>Möchten Sie Ihre Gruppe wirklich verlassen?<br><br><a href=statusseite.php?gaction=leave&ia=finish>Bestätigen</a> - <a href=statusseite.php>Abbrechen</a></center>";
							$tpl->assign('INFO', $infomsg);
							if(!$ia)
								$tpl->display('info.tpl');
						}
						if ($ia)	{
							//unset($ausgabe);
							if ($cheffe && $o <= 1){
								$queries[] = "delete from groups where group_id=".$result[group_id];
								$beschr = "Sie haben die Gruppe ".$result[group_id]." soeben verlassen. Da Sie das letzte Mitglied waren, wurde diese Gruppe gelöscht.";
								$tpl->assign("MSG", $beschr);
								$tpl->display("sys_msg.tpl");
							}
							elseif ($cheffe)	{
								$queries[] = "update groups set u1=".$result[u.$result[nachfolger]].", u$result[nachfolger]=0 where group_id=".$result[group_id];
							}
							else { $queries[] = "update groups set u$ownplace=0 where group_id=".$result[group_id]; }
							if ($o > 1)	{
								$beschr = "Sie haben die Gruppe ".$result[group_id]." verlassen.";
								$tpl->assign("MSG", $beschr);
								$tpl->display("sys_msg.tpl");
								foreach (range(1,MAX_USERS_A_GROUP) as $vl) {
									if ($result[u.$vl] && $vl != $ownplace)	{
										$messageinserts[] = "(35, ".$result[u.$vl].", $time, '".$status[syndicate]."')";
									}
								}
								$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
							}
						}
					}
					elseif ($cheffe)	{ 
						$errormsg = "Sie können als Administrator eine Gruppe nicht verlassen, solange diese nach Ihrem Austreten noch mindestens ein Mitglied hat und Sie keinen Nachfolger für das Amt des Gruppenadministrators gewählt haben.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl'); 
					}
				}
				else { 
					$errormsg = "Sie können keine Gruppe verlassen, solange Sie keiner Gruppe angehören.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
			elseif ($gaction == "joingroup")	{
				if ($groupoptionen[joingroup])	{
					$infomsg = "<br><br><center>Bitte geben Sie Gruppennummer und Gruppenpasswort der Gruppe, welcher Sie beitreten möchten, ein:<br><br><form action=statusseite.php method=post><input type=hidden name=gaction value=joingroup><table cellpadding=4 cellspacing=0 border=0 width=400 align=center class=i><tr><td align=right>Gruppennummer</td><td align=left><input type=text name=groupid size=4 value=".($groupid ? $groupid:"\"\"")."></td></tr><tr><td align=right>Gruppenpasswort</td><td align=left><input type=text size=9 maxlength=30 name=password></td></tr><tr><td colspan=2 height=20>&nbsp;</td></tr><tr><td colspan=2 align=center><input type=submit value=weiter></td></tr></table></form></center>";
					$tpl->assign('INFO', $infomsg);
					if ($groupid && $password)	{
						$grouppassword = single("select password from groups where group_id=$groupid");
						if ($grouppassword)	{
							if ($grouppassword == $password)	{
								$result = assoc("select $users from groups where group_id=$groupid");
								foreach (range(1,MAX_USERS_A_GROUP) as $vl) {
									if ($result[u.$vl])	{
										$messageinserts[] = "(36, ".$result[u.$vl].", $time, '".$status[syndicate]."')";
									}
									elseif (!$freeplace)	{ $freeplace = $vl; }
								}
								if ($freeplace)	{
									//unset($ausgabe);
									$beschr = "Sie sind erfolgreich Gruppe $groupid beigetreten.";
									$tpl->assign("MSG", $beschr);
									$tpl->display("sys_msg.tpl");
									$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
									$queries[] = "update groups set u$freeplace=$id where group_id=$groupid";
								}
								else { 
									$errormsg = "In dieser Gruppe sind alle Plätze belegt.";
									$tpl->assign('ERROR', $errormsg);
									$tpl->display('fehler.tpl');
								}
							}
							else { 
								$errormsg = "Sie haben ein falsches Passwort eingegeben.";
								$tpl->assign('ERROR', $errormsg);
								$tpl->display('fehler.tpl');
								$tpl->display('info.tpl');
							}
						}
						else { 
							$errormsg = "Diese Gruppe existiert nicht!";
							$tpl->assign('ERROR', $errormsg);
							$tpl->display('fehler.tpl');
							$tpl->display('info.tpl');
						}
					}
					elseif ($groupid or $password) { 
						$errormsg = "Sie haben entweder Gruppennummer und/oder Gruppenpasswort nicht eingegeben.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
						$tpl->display('info.tpl');
					} else {
						$tpl->display('info.tpl');
					}
				}
				else { 
					$errormsg = "Sie sind bereits einer Gruppe beigetreten.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
			elseif ($gaction == "changepass")	{
				if ($groupoptionen[changepass])	{
					$infomsg = "<br><br><center>Bitte wählen Sie ein Gruppenpasswort (mind. 3 Zeichen):<br><br><form action=statusseite.php method=post><input type=hidden name=gaction value=changepass><input type=text size=9 maxlength=30 name=password value=\"".$result[password]."\"><br><br><input type=submit value=weiter></form></center>";
					$tpl->assign('INFO', $infomsg);
					if ($password and preg_match("/^[[0-9a-zA-z]{3,30}$/", $password))	{
						//unset($ausgabe);
						$queries[] = "update groups set password='$password' where group_id=".$result[group_id];
						$beschr = "Sie haben das Passwort erfolgreich geändert. Geben Sie es zusammen mit der Gruppennummer an Ihre Freunde weiter, um mit ihnen zusammen spielen zu können.";
						$tpl->assign("MSG", $beschr);
						$tpl->display("sys_msg.tpl");
					}
					elseif ($password) { 
						$errormsg = "Das Passwort muss mindestens 3 und darf höchstens 30 Zeichen lang sein. Es sind nur kleine bzw. große Buchstaben von a-z und Zahlen von 0-9 erlaubt.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
						$tpl->display('info.tpl');
					} else {
						$tpl->display('info.tpl');
					}
				}
				else { 
					$errormsg = "Sie können das Passwort nur als Administrator einer Gruppe ändern.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
			/* Ab R46 keine geschlossenen Gruppen mehr möglich.
			
			elseif ($gaction == "openclose")	{
				if ($groupoptionen[openclose])	{
					$ausgabe .= "<br><br><center>Diese Gruppe ist momentan ".($result[open] ? "offen. Bei Rundenstart wird Ihre Gruppe also keinem eigenen Syndikat zugewiesen, sondern mit anderen Spielern vermischt.<br><br><a href=statusseite.php?gaction=openclose&ia=finish class=linkAufsiteBg>Gruppe schließen</a>":"geschlossen. Bei Rundenstart wird Ihrer Gruppe ein eigenes Syndikat zugewiesen, sofern sich mindestens ".USERS_NEEDED_FOR_CLOSED_GROUP." Spieler darin befinden.<br><br><a href=statusseite.php?gaction=openclose&ia=finish class=linkAufsiteBg>Gruppe öffnen</a>")."</center>";
					if ($ia)	{
						unset($ausgabe);
						$queries[] = "update groups set open=".($result[open] ? "0":"1")." where group_id=".$result[group_id];
						s("Sie haben die Gruppe ".($result[open] ? "geschlossen. Sofern sich bei Rundenstart mindestens ".USERS_NEEDED_FOR_CLOSED_GROUP." Spieler in Ihrer Gruppe befinden, wird Ihrer Gruppe ein eigenes Syndikat zugewiesen.":"geöffnet.")."$weiter");
					}
				}
				else { f("Sie sind nicht der Administrator dieser Gruppe bzw. befinden sich in keiner Gruppe.$zurueck"); }
			}*/
			elseif ($gaction == "nachfolger")	{
				if ($groupoptionen[nachfolger])	{
					if ($place)	{
						if ($result[u.$place])	{
							$queries[] = "update groups set nachfolger=$place where group_id=".$result[group_id];
							$beschr = "Sie haben den Administrator-Nachfolger erfolgreich geändert.";
							$tpl->assign("MSG", $beschr);
							$tpl->display("sys_msg.tpl");
						}
						else { 
							$errormsg = "An der angegebenen Position befindet sich kein Spieler.";
							$tpl->assign('ERROR', $errormsg);
							$tpl->display('fehler.tpl');
						}
					}
					else { 
						$errormsg = "Keine Platznummer angegeben.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
					}
				}
				else { 
					$errormsg = "Sie sind nicht der Gruppenadministrator.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
			elseif ($gaction == "kick")	{
				if ($groupoptionen[kick])	{
					if ($place)	{
						if ($result[u.$place])	{
							$queries[] = "update groups set u".$place."=0, u".$place."_status=0 where group_id=".$result[group_id];
							$beschr = "Sie haben den Konzern ".$playerresult[$result[u.$place]][syndicate]." erfolgreich ausgeschlossen.";
							$tpl->assign("MSG", $beschr);
							$tpl->display("sys_msg.tpl");
							foreach (range(2,MAX_USERS_A_GROUP) as $vl) {
								if ($result[u.$vl] && $vl != $place)	{
									$messageinserts[] = "(35, ".$result[u.$vl].", $time, '".$playerresult[$result[u.$place]][syndicate]."')";
								}
							}
							$messageinserts[] = "(37, ".$result[u.$place].", $time, '".$result[group_id]."')";
							$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
						}
						else { 
							$errormsg = "An der angegebenen Position befindet sich kein Spieler.";
							$tpl->assign('ERROR', $errormsg);
							$tpl->display('fehler.tpl');
						}
					}
					else { 
						$errormsg = "Keine Platznummer angegeben.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
					}
				}
				else { 
					$errormsg = "Sie sind nicht der Gruppenadministrator.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
		}
		else { 
			$errormsg = "120 Sekunden vor Zuweisungen der Gruppen sind Änderungen an Gruppen nicht mehr möglich.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
	}
}

db_write($queries);
db_write($queriesend,1); # Für queries die auch nach Rundende ausgeführt werden

//**************************************************************************
//							Header, Ausgabe, Footer
//**************************************************************************

$tpl->display('statusseite.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************
//							Dateispezifische Funktionen					
//**************************************************************************


function getnumberoflandunderconstruction() {
	global $status;
	$action ="select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name = 'land'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}

function getnumberofunitsunderconstruction() {
	global $status;
	$action ="select sum(number) from build_military where user_id ='".$status[id]."'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}
function getnumberofspiesunderconstruction() {
	global $status;
	$action ="select sum(number) from build_spies where user_id ='".$status[id]."'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}
?>