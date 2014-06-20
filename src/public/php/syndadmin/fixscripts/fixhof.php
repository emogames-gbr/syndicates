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

#### LETZTE NETWORTHWERTE/LANDWERTE SETZEN
/*
$status = assocs("select id, nw, land from status where alive > 0", "id"); 
$stats = assocs("select largestland, largestnetworth, konzernid from stats_round_6", "konzernid"); 
 
 
foreach ($status as $ky => $vl)
{ 
 
if ($stats[$ky][largestland] < $vl[land]): 
select("update stats_round_6 set largestland=".$vl[land]." where konzernid=".$vl[id]); 
endif; 
if($stats[$ky][largestnetworth] < $vl[nw]): 
select("update stats_round_6 set largestnetworth=".$vl[nw]." where konzernid=".$vl[id]); 
endif; 
select("update stats_round_6 set lastnetworth=".$vl[nw].",lastland=".$vl[land]." where konzernid=".$vl[id]); 
 
 
} 
*/

##### ANZAHL FORSCHUNGEN KORRIGIEREN
/*
$data = assocs("select sum(level) as tl, user_id from usersciences group by user_id");

foreach ($data as $ky => $vl)	{
	select("update stats_round_6 set sciencesdone=".$vl[tl]." where konzernid=".$vl[user_id]);
}
*/


### HONORCODES VERGEBEN
/*
$data = assocs("select * from stats_round_6 where alive > 0 order by lastnetworth desc limit 100");
$i = 0;
foreach ($data as $ky => $vl)	{
	$i++;
	if ($i == 1): $honorcode = 1; endif;
	if ($i == 2): $honorcode = 2; endif;
	if ($i == 3): $honorcode = 3; endif;
	if ($i >= 4 && $i <= 10): $honorcode = 4; endif;
	if ($i >= 11 && $i <= 30): $honorcode = 5; endif;
	if ($i >= 31 && $i <= 100): $honorcode = 6; endif;
	$round = 6;
	select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",$round,$honorcode,$i)");
}
*/



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