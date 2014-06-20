<?

//**************************************************************************//
//							?bergabevariablen checken						//
//**************************************************************************//

$sessionid_admin = $sessionid_admin; // ?bergebene id ist sessionid, durch cookie, evtl unescape specchars drauf ?


//**************************************************************************//
//							Variablen deklarieren							//
//**************************************************************************//
$runlogs=0; // auf 0 setzen, damit nicht mehr jeder verd?chtie klick mitgelogt wird
$handle = ""; // Sp?ter dbhandle
$ausgabe= ""; // Hier kommt die gesamte ausgabe rein
$fehler = "";
$querystring = "";
$successmeldung = "";
$kopf = "";
$globals = array (); // spt?ter assoziatives array mit daten aus globals table
$loggedin = 1; // Entscheidet ob menu und ressourcenleiste geladen werden. Wenn true wird beides geladen
$time = time(); // Zeit zur skriptausf?hrung als timestamp, bitte sp?ter nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.
$id = 0;
$status = array(); // speichert alle spielerinformationen
$sciences = array(); // speichert alle infromationen zu forschungen
mt_srand($time);
$dr =0; // anzahl db aufrufe
$ripf = "images/"; # Relativer Imagepfad
$page_raw = getenv(SCRIPT_NAME);
$HTTP_USER_AGENT = getenv(HTTP_USER_AGENT);
$agent = $HTTP_USER_AGENT;
$page = (strrchr($page_raw,'/'));
$page = substr($page,1,strlen($page)-5);
$self = (explode("/",$SCRIPT_NAME));
$self = array_pop($self);

//**************************************************************************//
//							Eigentliche Berechnungen						//
//**************************************************************************//

require_once ("../../../includes.php");
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
$pl = 0; // Privilege Level
$time = time();


DEFINE(A_INC,PUB."php/admin_new/inc");dontcache();
require_once(A_INC."/libPrint.php");



$start = getmicrotime();

$globals = getglobals();
$globals['roundstarttime'] -= 7*86400;
$pages = assocs("select * from admin_pages order by showposition asc", "dateiname");
$game = assoc("select * from game limit 1");
	//$pagestats = assoc("select * from pages where dateiname = '$page'");
	//$pages = assocs("select * from pages where visible=1 order by showposition","dateiname");



if ($globals[roundstatus] == 2 and false) {
			$loggedin = 0;
			require("inc.themes.php");
			$ausgabe .= "<br><br><br><center><b>Die Runde ist beendet, bitte informieren Sie sich auf der <a class=\"highlightAufSiteBg\" href=../index.php>Startseite</a> wann die n?chste Runde gestartet wird.</b></center><br><br><br>";
			include ("header.php");
			echo $ausgabe;
			include("footer.php");
			exit();
}

else { // falls roundstatus nicht 2
	$adminlogin = 0;
		if ($sessionid_admin) {
			$id_data = checksid($sessionid_admin);
			$id = $id_data[user_id];
			$paid=$id_data[paid];
			$pl = $id_data['privilege_level'];
		} else {$id =0;}



	if (!$id) {
		header ("Location: index.php?error=Sie sind nicht eingeloggt.".$sessionid_admin); exit();
	}


	// Wenn id korrekt, Userdaten laden
	$user = assoc("select * from users where id = $id");

	$yellowdot = "<img src=\"../images/dot-gelb.gif\" hspace=\"5\" border=\"0\">";
	$reddot = "<img src=".$layout["images"]."dot-rot.gif hspace=\"5\" border=\"0\">";
	$greendot = "<img src=".$layout["images"]."dot-gruen.gif hspace=\"5\" border=\"0\">";

	// Darf die Seite benutzt werden ?
	$allowed = checkpageaccess($pages, $page);

	// Letzten Klick aktualisieren bis auf das "Wer ist online-Frame", welches sich ständig neu lädt
	if ($allowed and !preg_match("/\/middle\/index\.php/", $page_raw)) select("update admin_sessionids set lastklicktime = '$time' where sessionid = '$sessionid_admin'");

	if ($allowed != 1) {
		//if ($allowed) f($allowed);
		include ("header.php");
		echo "Du hast nicht die nötigen Zugriffsrechte, um auf diese Seite zuzugreifen.";
		include("footer.php");
		exit();
	}

}	// falls roundstatus nicht 2
//update($status{id});











//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


//							Getglobals						//

// Holt Werte aus dem Globalstable
function getglobals() {
		$result = assoc("select * from globals order by round desc limit 1");
return $result;
}



function checkpageaccess($pages, $page) {
    // Seiten, die nur bei laufender Runde funktionieren
	global $pl;
	if ($pages[$page.".php"][privilege_level] > $pl) return 0;
	else return 1;
}

/********************************************************************************

Function checksid()

*********************************************************************************/
// ?berpr?ft eine ?bergebene Sessionid auf g?ltigkeit id zur?ck, falls g?ltig, sonst 0
function checksid($sid) {
		if ($sid) {
			global $time;
			$sessionid_data = array(); // Speichert r?ckgabe des selects
			$ip = getenv ("REMOTE_ADDR");

			$result = select("select sessionid, angelegt_bei, gueltig_bis, ip, user_id, privilege_level from admin_sessionids where sessionid='$sid' and gueltig_bis >= $time");
			if (mysql_num_rows($result) != 1) {return 0;} // wenn sid nicht im table gefunden wurde false zur?ckgeben oder zuf?llig mehrer gleiche sids existieren
			$sessionid_data = mysql_fetch_assoc($result);
			//if ($sessionid_data[ip] != $ip) {return 0;}
			if ($time < $sessionid_data[gueltig_bis])	{

				// User id verl?ngern und zuweisen an r?ckgabevariable, locked auf 1 setzen
				#$gueltig_bis = $time + SESSION_DAUER;
				#if ($sessionid_data[angelegt_bei] + 3600 < $gueltig_bis) {
				#	$gueltig_bis = $sessionid_data[angelegt_bei] + 3600;
				#}
				#ignore_user_abort(TRUE);
				#select("update sessionids_actual set gueltig_bis=$gueltig_bis,locked = 1 where sessionid='$sid'");
			}
		return $sessionid_data;
    }
}


?>
