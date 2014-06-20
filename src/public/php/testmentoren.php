<?
//**************************************************************************
// Testmentoren, R45, o19 - nur Testversion                                               
//**************************************************************************
require_once("../../inc/ingame/game.php");

if ($game[name] == "Syndicates Testumgebung"){
//**************************************************************************
// Verarbeitung der Eingaben, DB_write
//**************************************************************************
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
	$ausgabe = "";
	$ausgabe .= "<table width=550 style=\"border:1px solid\" class=i cellpadding=2><tr><td>";
	$ausgabe .= "Mentorflag (1 == true, 0 == false): ".single("select is_mentor from users where konzernid=$id")."<br>";
	$ausgabe .= "Toggle Mentor: <a href=testmentoren.php?togglementor=1>toggle now</a><br>";
	$ausgabe .= "Das Mentorflag ist dem User-Account zugeordnet. D.h. nach Konzernlöschung seid ihr immer noch Mentor (wenn das Flag 1 zeigt).<br>Testet damit bitte (Konzernneuerstellung) ob das mit den Gruppen so funktioniert (ab morgen(Mittwoch) 16:00 Uhr) (automatische Erstellung als Mentor, automatische Zuordnung in eine als Spieler)";
	$ausgabe .= "<br>Zum Testen sind die Werte für Syndikate im Moment so einegstellt: maximale Syndikatsgröße ".MAX_USERS_A_SYNDICATE.". Reservierte Plätze für jeden Mentor: ".MENTOR_SPACE_RESERVED." (wenn die Zahlen hier ungleich 4 und 1 sind, seid ihr auf dem Classic-Server, hier braucht ihr nichts bzgl. Mentorenprogramm testen, weil es das hier noch nicht gibt => Ab auf den Basic)";
	$ausgabe .= "<br>Bitte testet auch, dass Mentoren nie in ein Syndikat kommen können, in dem schon ein Mentor ist. Morgen(Mittwoch) abend testen wir dann noch
	das Startrundenskript, welches die Gruppen in Syndikate mischt.<br>Vorerst gilt: Rundenstatus <u>Runde läuft</u> bis morgen(Mittwoch) 16:00 Uhr; danach testen wir das mit den Gruppen und setzen den Rundenstatus auf <u>Anmeldephase</u>.";
	$ausgabe .= "<br>Im Moment also bitte testen: <br>-Mentor bekommt Nachricht wenn er einen Schützling erhält;<br>-Schützling kommt ins selbe Syndikat wie sein Mentor;<br>-Normale Anmeldungen können nicht den reservierten Platz belegen (d.h. sobald 3 Leute im Syndikat sind ist es voll mit den obigen Werten)<br>-Mentorenprogrammbeitritt geht nur auf dem Basic-Server<br>-Was passiert wenn euer Syndikat voll ist und ihr trotzdem einen Schützling bekommt?<br>-was euch sonst noch einfällt";
	$ausgabe .= "<br><br>Nächste Sache: Bitte testet die Anmeldemaske durch; Ihr habt momentan folgenden Wert für das 'may_play_on_classic'-Flag:".single("select may_play_on_classic from users where konzernid = $id").";<br>Wenn das auf 0 steht, dürft ihr euch nicht auf dem Classic anmelden können! Ihr könnt es hier umsetzen:<a href=testmentoren.php?setclassicflag=-1>0</a><a href=testmentoren.php?setclassicflag=1>1</a><a href=testmentoren.php?setclassicflag=3>3</a>;";
	$ausgabe .= "<br>1 und 3 unterscheiden sich nur darin, dass jemand der durch einen Spieler geworben wurde (startet mit Flag auf 3) auf dem Basic-Server noch dem Mentorenprogramm beitreten darf, jemand der 1 hat aber nicht.; Es gibt noch einen 2. Wert, der momentan aber noch keine Auswirkungen hat: 2; 2 erhält jeder der durch Ranking sich das Recht, auf dem Classic-Server zu spielen erspielt hat; dort soll er dann irgendwann auch nochmal das Mentorenprogramm benutzen können. Den Wert 1 hat später jeder Spieler, der mind. 1 Runde auf dem Classic-Server <u>beendet</u> hat.";
	$ausgabe .="<br><br>Achja: auf dem Testserver gibt es keine Anmeldebeschränkung von 2 pro Tag, ihr könnt euch also so oft löschen wie ihr wollt.<br>";
	$mentor = $status['mentor_id'];
	$ausgabe .= "<br>Hier seht ihr, ob ihr momentan einem Mentor zugeordnet seid: ".($mentor > 0 ? "<b>".single("select syndicate from status where id = $mentor")."</b>":"kein mentor zugeordnet")."<br>";
	$ausgabe .= "</td></tr></table>";
	
//**************************************************************************
// Header, Ausgabe, Footer
//**************************************************************************
require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");
}
 
else f("Diese Seite steht auf diesem Server leider nicht zur Verfügung.");
?>

