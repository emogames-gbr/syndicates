<?	

include("inc/general.php");
$self = "anmeldestats.php";
$ausgabe ="";

if ($adminlevel > 2 || true) {
	
	if ($what != "lastupdatetime" and $what != "lastlogintime" and $what != "lastupdatetime2"): $what = "lastupdatetime"; endif;
	if ($what == "lastupdatetime"): $ausgabe .= "Wie viele haben sich wann angemeldet (inklusive toter Konzerne)?:<br><br><br>"; endif;
	if ($what == "lastupdatetime2"): $what="lastupdatetime";$ausgabe .= "Wie viele haben sich wann angemeldet (exklusive toter Konzerne)?:<br><br><br>"; $optional = " where alive > 0"; endif;
	if ($what == "lastlogintime"): $ausgabe .= "Wie viele haben sich wann zuletzt eingeloggt (exklusive toter Konzerne)?:<br><br><br>"; $optional = " where alive > 0"; endif;

	$daten=assocs("select $what as lastupdatetime from status$optional");
	$standardwidth = 500;


	foreach ($daten as $vl)	{
		$sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])]++;
		if ($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])] > $max)	{
			$max = $sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])];
		}
		if (array_sum($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])]) > $daymax)	{
		$daymax = array_sum($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])]);
		}
		$total++;
	}

	foreach ($sorted as $ky => $vl)	{
		foreach ($vl as $ky2 => $vl2)	{
			foreach (range(0,23) as $vl3)	{
				if (!$sorted[$ky][$ky2][$vl3]): $sorted[$ky][$ky2][$vl3] = 0; endif;
			}
		}
	}


	#Stunden

	krsort($sorted);
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Stunde</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		ksort($vl);
		foreach ($vl as $ky2 => $vl2)	{
			ksort($vl2);
			foreach ($vl2 as $ky3 => $vl3)	{
				$width = round($vl3 / $max * $standardwidth);
				$ausgabe .= "<tr><td>$ky2.$ky - Stunde $ky3</td><td align=right>$vl3</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
			}
		}
	}
	$ausgabe .= "</table>";
	
	# Tage
	$ausgabe .= "<br><br><br>Nach Tagen:<br><br>";
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Stunde</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		ksort($vl);
		foreach ($vl as $ky2 => $vl2)	{
			$width = round(array_sum($vl2) / $daymax * $standardwidth);
			$ausgabe .= "<tr><td>$ky2.$ky</td><td align=right>".array_sum($vl2)."</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
		}
	}
	$ausgabe .= "</table>";
	
	
	$ausgabe .= "<br><br>Insgesamt: $total";

}
elseif ($action == "vgv" )	{ # View Gruppen Verteilungen
	
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
	
}

echo $ausgabe;
?>