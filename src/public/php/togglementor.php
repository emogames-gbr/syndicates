<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//


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

$queries = array();

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//
$userdata = assoc("select * from users where konzernid=$id");
if ($userdata[username] != "Scytale" && $userdata[username] != "Bogul" && $userdata[username] != "Jonny25k" && $userdata[username] != "rabbit" && $userdata[username] != "R4bbiT" && $userdata[username] != "inok1989") { // Sirdom ist louts basic acc
	header("location: statusseite.php");
	exit(1);
}
kopf("Mentoren Setup",""); 


if (strlen($togglementoraccountname) != 0) {
	$togglementoraccountname = mysql_real_escape_string($togglementoraccountname);
	
	$accountexits = single("select id from users where username='".$togglementoraccountname."'");
	if (!$accountexits) {
		f("Der angegebene Useraccount existiert nicht");
	}
	else {
		
		$is_mentor = single("select is_mentor from users where id = $accountexits");
		
		if ($is_mentor) {
			s("Der Account <b>$togglementoraccountname</b> <i>wird nicht länger</i> als Mentor gekennzeichnet!");
			select("update users set is_mentor=0 where id=$accountexits");
			select("update status set is_mentor=0 where id = (select konzernid from users where username='".$togglementoraccountname."')");
		}
		else {
			s("Der Account <b>$togglementoraccountname</b> <i>wird jetzt</i> als Mentor gekennzeichnet!");
			select("update users set is_mentor=1 where id=$accountexits");
			select("update status set is_mentor=1 where id = (select konzernid from users where username='".$togglementoraccountname."')");
			
		}
		
		
	}
	
	
}

if($_GET['action'] == 'chef'){
	select("update users set is_mentor=1 where is_mentor >= 1");
	select("update status set is_mentor=1 where is_mentor >= 1");
	select("update status, users set status.is_mentor=2, users.is_mentor=2 where users.id = ".$_GET['id']." and status.id = users.konzernid");
}


$mentoren = assocs("select * from users where is_mentor >= 1");

$ausgabe.="

<b>Mentorstatus für Useraccount ändern:</b><br><br>
<form action=\"togglementor.php\" method=\"post\">

Useraccount: <input name=\"togglementoraccountname\" value=\"\"><br>
<br>
<input type=\"submit\" value=\"Mentorstatus ändern (an/aus)\">

</form>
<br><br>


<h1>".count($mentoren)." Aktuelle Mentoren (Useraccountnamen):</h1>";

foreach ($mentoren as $temp) {
	$ausgabe.="<li>$temp[username]  ".($temp['is_mentor'] == 2 ? '[Chef]' : '<a href="?action=chef&id='.$temp['id'].'"  class="linkAuftableInner"><em>[zum Chef]</em></a>')."</li>";
}



//							Daten schreiben									//
db_write($queries);

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


/*


###########################################
########## Eintrag vornehmen ##############
###########################################







1;
*/

?>
