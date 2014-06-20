<?
	$redac = floor($redac);
	if ($redac == 1) {
		$modrprot = 60*60*12;
		select("update status set createtime = createtime - $modrprot,reduce_protection=-1 where id=$status[id]");
		$status[reduce_protection] = 0;
		$status[createtime] -= $modrprot;
	}
	elseif ($redac == 2) {
		select("update status set reduce_protection=0 where id=$status[id]");
		$status[reduce_protection] = 0;
	}


if ($status[reduce_protection] == 1) {

	i("Wir haben heute nacht um 04:00 Uhr ein Backup von Mittwoch 04:00 Uhr eingespielt (näheres dazu in den News). Da einige Spieler dadurch nicht die Möglichkeit hatten,
	Verteidigungseinheiten zu bauen, wurde die Schutzzeit für JEDEN Spieler OPTIONAL um 12 Stunden verlängert (d.h. jeder Spieler hat jetzt 3 Tage und 12 Stunden Schutzzeit). Wenn du möchtest, kannst du auf die 12 Stunden zusätzliche Schutzzeit verzichten:<center><br><br>
	<a href=\"statusseite.php?redac=1\">Ich möchte auf die zusätzliche Schutzzeit VERZICHTEN</a><br><br>
	<a href=\"statusseite.php?redac=2\">Ich AKZEPTIERE die verlängerte Schutzzeit von 12 Stunden</a></center>"
	);
}


?>