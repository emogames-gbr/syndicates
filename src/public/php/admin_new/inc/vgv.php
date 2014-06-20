<?

if ($adminlevel > 2) {
	$ausgabe .= "<center><a href=$self>zurück</a></center><br><br>";
	
	foreach (range(1,20) as $vl)	{
		$string .= "u".$vl.",";
	}
	$string = chopp($string);

	$daten=assocs("select $string from groups");
	$standardwidth = 500;
	
	
	foreach ($daten as $vl)	{
		$anzahl = 0;
		foreach (range(1,20) as $vl2)	{
			if ($vl[u.$vl2]): $anzahl++; $total++; endif;
		}
		$sorted[$anzahl]++;
		if ($sorted[$anzahl] > $max): $max = $sorted[$anzahl]; endif;
	}

	foreach (range(1,15) as $vl)	{
		if (!$sorted[$vl]): $sorted[$vl] = 0; endif;
	}
	
	
	#Stunden
	
	krsort($sorted);
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Anzahl Mitglieder</td><td align=center>Anzahl Häufigkeit</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		$width = round($vl / $max * $standardwidth);
		$ausgabe .= "<tr><td>$ky</td><td align=right>$vl</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
		$totalgruppen += $vl;
	}
	$ausgabe .= "</table>";
	
	$totalplayers = single("select count(*) from status where alive > 0");
	
	$ausgabe .= "<br><br>Insgesamt Spieler in Gruppen: $total / $totalplayers (".(round($total/$totalplayers*10000)/100)."%)";

	$ausgabe .= "<br><br>Insgesamt Gruppen: $totalgruppen";
	
	$ausgabe .= "<br><br><br><center><a href=$self>zurück</a></center>";
}
?>