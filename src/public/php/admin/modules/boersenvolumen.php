<?

$hourtime = get_hour_time($time+10);
$data = assocs("select synd_id, aktienkurs from aktien_safekurse where time >= $hourtime", "synd_id");
$playerdata = assocs("select synd_id, sum(number) as nr from aktien group by synd_id", "synd_id");
$playerdata_private = assocs("select synd_id, sum(number) as nr from aktien_privat group by synd_id", "synd_id");

foreach ($data as $ky => $vl)	{
$sumprice_total += ($playerdata[$ky][nr] + $playerdata_private[$ky][nr]) * $vl[aktienkurs];

$sumprice_synds[$ky] = ($playerdata[$ky][nr] + $playerdata_private[$ky][nr]) * $vl[aktienkurs];

}

$ausgabe .= "Gesamtbörsenvolumen: ".pointit($sumprice_total)." Cr.<br><br>";

arsort($sumprice_synds);

$i = 1;
foreach ($sumprice_synds as $ky => $vl)	{
	if ($i <= 30)	{
		$ausgabe .= ($i).". #$ky - ".pointit($vl)." Cr<br>";
		$i++;
	} else { break;}
}


print pointit($sumprice);
?>
