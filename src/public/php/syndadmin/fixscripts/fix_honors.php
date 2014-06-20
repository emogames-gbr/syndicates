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
if (!$argv[2]) exit("\n\nKeine Runde übergeben! Abbruch\n\n");
$roundtofix = $argv[2];


$time = time();


//select("delete from honors where round = $roundtofix");



	$data = assocs("select * from stats where alive > 0 and round=".$roundtofix." and isnoob = 0 order by lastnetworth desc limit 100");
	$i = 0;
	foreach ($data as $ky => $vl)	{
		$i++;
		if ($i == 1): $honorcode = 1; endif;
		if ($i == 2): $honorcode = 2; endif;
		if ($i == 3): $honorcode = 3; endif;
		if ($i >= 4 && $i <= 10): $honorcode = 4; endif;
		if ($i >= 11 && $i <= 30): $honorcode = 5; endif;
		if ($i >= 31 && $i <= 100): $honorcode = 6; endif;
		select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$roundtofix.",$honorcode,$i)");
	}


	// Honorcodes für Syndikate
	$top3syns = assocs("select rid,sum(lastnetworth) as nw from stats where alive >= 1 and round = ".$roundtofix." group by rid order by nw desc limit 3");
	
	$currentSynRank = 0;
	foreach ($top3syns as $temp) {
		
		$currentSynRank++;
		$synHonorCode = 10+$currentSynRank;
		
		if ($currentSynRank > 3) break;
		
		$playersInSyn = assocs("select * from stats where alive > 0 and round=".$roundtofix." and rid=".$temp[rid]);
		foreach ($playersInSyn as $ky => $vl) {
			select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$roundtofix.",$synHonorCode,$currentSynRank)");
		}	
		
	}
	


/*
$data = assocs("select * from honors where user_id >= 65535");

foreach ($data as $vl) {
	$real_user_id = single("select user_id from stats where round = ".$vl['round']." and endrank = ".$vl['rank']);

	echo $vl['user_id']." -".$vl['round']."--  -- ".$vl['rank']."--- ".$real_user_id."\n";
}
*/


//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


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