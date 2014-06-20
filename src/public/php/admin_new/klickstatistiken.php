<?
include("inc/general.php");
$self = "klickstatistiken.php";
$ausgabe ="";

$id = 2000000;
$standardwidth = 600;


$globals = assocs("select * from globals", "round");


$starttime = 0;
$endtime = 2000000000;
$round = single("select round from globals order by round desc");
$round--;
$starttime = $globals[$round][roundstarttime];
//if ($round): $starttime = $globals[$round][roundstarttime]; $endtime =
//$globals[$round][roundendtime]; endif;



$data = assocs("select * from hitstats where jahr >= 2005","id");


foreach ($data as $ky => $vl)	{
	$timestamp = mktime($vl[stunde], 0, 0, $vl[monat], $vl[tag],$vl[jahr]);
	if ($timestamp >= $starttime and $timestamp <= $endtime) {
	foreach ($vl as $ky2 => $vl2)	{
		if ($ky2 != "id" && $ky2 != "tag" && $ky2 != "monat" && $ky2 != "jahr" && $ky2 != "stunde")	{
			$totalklicks += $vl2;
			$klickdata[$vl[jahr]][$vl[monat]][$vl[tag]][$vl[stunde]][$ky2] += $vl2;
			
			$totalklicks_seiten[$ky2] += $vl2;
			if ($totalklicks_seiten[$ky2] > $seitenmax): $seitenmax = $totalklicks_seiten[$ky2]; endif;
			
			$totalklicks_day[$vl[jahr]][$vl[monat]][$vl[tag]] += $vl2;
			if ($totalklicks_day[$vl[jahr]][$vl[monat]][$vl[tag]] > $daymax): $daymax = $totalklicks_day[$vl[jahr]][$vl[monat]][$vl[tag]]; endif;
			$klickdata_day[$vl[jahr]][$vl[monat]][$vl[tag]][$ky2] += $vl2;
			
			$totalklicks_month[$vl[jahr]][$vl[monat]] += $vl2;
			$klickdata_month[$vl[jahr]][$vl[monat]][$ky2] += $vl2;
		}
	}
	}
}

$ausgabe .= "Klicks insgesamt: <b>".pointit($totalklicks)."</b>";

# Tage
ksort($totalklicks_day);
$ausgabe_tage .= "<br><br><br>Gesamtklicks nach <strong>Tagen</strong>:<br><br>";
$ausgabe_tage .= "<table cellpadding=3 cellspacing=4 border=0>";
$ausgabe_tage .= "<tr><td align=center>Tag</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
foreach ($totalklicks_day as $ky => $vl)	{
	ksort($vl);
	foreach ($vl as $ky2 => $vl2)	{
		ksort($vl2);
		foreach ($vl2 as $ky3 => $vl3)	{
			$width = round($vl3 / $daymax * $standardwidth);
			$ausgabe_tage .= "<tr><td>".($ky3 < 10 ? "0".$ky3:$ky3)." / ".($ky2 < 10 ? "0".$ky2:$ky2)." / ".$ky."</td><td align=right".($vl3==$daymax ? " bgcolor=orange":"")."><b>".pointit($vl3)."</b></td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
		}
	}
}
$ausgabe_tage .= "</table>";

# Seiten
ksort($totalklicks_seiten);
$ausgabe_seiten .= "<br><br><br>Gesamtklicks nach <strong>Seiten</strong>:<br><br>";
$ausgabe_seiten .= "<table cellpadding=3 cellspacing=4 border=0>";
$ausgabe_seiten .= "<tr><td align=center>Seite</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
foreach ($totalklicks_seiten as $ky => $vl)	{
	$width = round($vl / $seitenmax * $standardwidth);
	$ausgabe_seiten .= "<tr><td>$ky</td><td align=right".($vl==$seitenmax ? " bgcolor=orange":"")."><b>".pointit($vl)."</b></td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
}
$ausgabe_seiten .= "</table>";






$ausgabe .= $ausgabe_tage.$ausgabe_seiten;





	$ausgabe = "
				<br><br>
				$ausgabe
				<br><br><br>
				";

echo $ausgabe;

?>
