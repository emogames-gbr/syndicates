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

require("../../subs.php");
$handle = connectdb($SERVER_NAME);

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$id = 772;
$globals = assoc("select * from globals order by round desc limit 1");


$stats = assocs("select rulername, syndicate, konzernid from stats where round=".$globals[round], "konzernid");
$status = assocs("select id, syndicate, rulername from status", "id");

foreach ($stats as $ky => $vl)	{

if ($status[$ky][rulername] != $vl[rulername] or $status[$ky][syndicate] != $vl[syndicate])	{ $queries[] = "update stats set rulername='".$status[$ky][rulername]."', syndicate='".$status[$ky][syndicate]."' where konzernid=$ky and round=".$globals[round];}

}

echo join("<br>", $queries);
db_write($queries);





//							selects fahren									//

//							Berechnungen									//

//							Daten schreiben									//

//							Ausgabe     									//


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//


echo $ausgabe;



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
