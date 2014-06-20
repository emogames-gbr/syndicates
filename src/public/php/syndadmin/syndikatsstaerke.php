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

require("../subs.php");
$handle = connectdb($SERVER_NAME);
//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


$data = assocs("select user_id, lastnetworth from stats where alive > 0 and round=8", "user_id");

$data2 = assocs("select users.id as user_id, status.id, status.rid as rid, users.username as username, status.syndicate as syndicate from status, users where users.konzernid = status.id and status.alive > 0", "user_id");

foreach ($data2 as $ky => $vl)	{

	$rids[$vl[rid]] += $data[$ky][lastnetworth];
	$rids2[$vl[rid]][$vl[user_id]][username] = $vl[username];
	$rids2[$vl[rid]][$vl[user_id]][syndicate] = $vl[syndicate];
} 

arsort($rids);
$i = 0;
foreach ($rids as $ky => $vl)	{
	$ausgabe .= "Syndikat $ky: ".pointit($vl)."<br>";
	if ($i < 20)	{
		$ausgabe .= "<br><table cellpadding=5>";
		foreach ($rids2[$ky] as $vl2)	{
			$ausgabe .= "<tr><td>USER</td><td>".$vl2[username]."</td><td>KONZERN</td><td>".$vl2[syndicate]."</td></tr>";
		}
		$ausgabe .= "</table><br><br>";
	}
	
	
	
	$i++;
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//


echo $ausgabe;



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
