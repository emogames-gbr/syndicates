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

require("../../../../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);


$time = time();


$data = assocs("select * from syndikate_anfaenger_inaktivenverschiebungen");

foreach ($data as $ky => $vl) {
	select("update status set rid = ".$vl[old_rid]." where id = ".$vl['user_id']);
	select("update stats set rid = ".$vl[old_rid]." where konzernid = ".$vl['user_id']" and round = 34");
}




echo $ausgabe;



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>