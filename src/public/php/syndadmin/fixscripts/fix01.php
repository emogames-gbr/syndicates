<?


//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require("../../subs.php");
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
$count = 0;
/*
$userdata = assocs("select id, username from users", "id");

foreach ($userdata as $ky => $vl) {
	$barrier = 0;
	if (preg_match("/(.+)(01)+$/", $vl[username], $safe)) {
		foreach ($userdata as $ky2 => $vl2) {
			if (preg_match("/^".$safe[1]."$/", $vl2[username])) { $barrier = 1; }
		}
		if (!$barrier): $count++; select("update users set username='".$safe[1]."' where id=".$vl[id]); endif;
	}

}


*/

$usersavedata = assocs("select id, username, email, vorname, nachname from usersave3", "id"),

foreach ($usersavedata as $ky => $vl) {
	$count++;
	select("update users set email='".$vl[email]."', vorname='".$vl[vorname]."', nachname='".$vl[nachname]."' where id=".$vl[id]);
}

$ausgabe .= "$count Leute bearbeitet.";

echo $ausgabe;



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
