<?

include("inc/general.php");
$self = "adminstats.php";

$races = singles("select race from races");


if ($inner) {
	echo "<a href=".$self."Back</a><br>";
}


// buildingsstats
if ($inner == "buildings") {
	$buildstats = array();
	$tempsum = array();
	$wholesum = 0;

	$buildings = singles("select name_intern from buildings");
	$races = singles("select race from races");


	foreach ($buildings as $key) {
		foreach($races as $trace) {
			$temp = single("select sum($key) from status where race = '".$trace."'");
			$buildstats[$key][$trace] = $temp;
			$buildstats[$key][sum] += $temp;
			$wholesum += $temp;
		}
	}
	$ausgabe.="<br><b><i> Gebäude gesamt: ".pointit($wholesum)."</i></b><br><br>";
	foreach ($buildstats as $l => $key) {
	$tempsum[$l] = 0;
		$ausgabe.="<b>GEBÄUDE: $l</b> - ";
		$detausgabe.="<b>GEBÄUDE: $l</b><br>";
		foreach ($key as $trace => $tvalue) {
			if (in_array($trace,$races)) {
				if ($buildstats[$l][sum] <= 0) {$buildstats[$l][sum] = 1;}
				$detausgabe.="Fraktion: $trace - Anzahl: ".pointit($tvalue)." - intern Relativ: ".prozent(($tvalue/$buildstats[$l][sum]*100))."%<br>";
				$tempsum[$l] += $tvalue;
			}
		}
			$ausgabe.="Fraktion: SUMME - Anzahl: ".pointit($tempsum[$l])." - Relativ: <b>".prozent(($tempsum[$l]/$wholesum*100))."%</b><br>";
	}
	$ausgabe.="<br><br>";
	$ausgabe.="$detausgabe";
}

//Forschungen
if ($inner == "sciences") {
$sum = 0;
	$sciencestats = array();
	$sstats = assocs("select *,concat(name,typenumber) as rname from sciences order by treename,level","rname");
	foreach ($sstats as $key => $value) {
		for ($i=1; $i <= 5; $i++) {
			$templevel = single("select count(*) from usersciences where name='$key' and level =$i");
			$sciencestats[$key][$i] = $templevel;
			$sciencestats[$key][sum] += $templevel;
			$sum+=$templevel;
		}
	}
	$ausgabe.="<br><b><i> Forschungen gesamt (alle level): ".pointit($sum)."</i></b><br><br>";
	$ausgabe.="<table width=100%>";
	$i = 0;
	foreach ($sciencestats as $science => $value) {
		if ($i % 3 == 0) {
			$ausgabe.="<tr>";
		}
		if ($i % 15 == 0 && $i > 0) {
			$ausgabe.="<tr><td colspan=3><hr></td></tr>";
		}

		$ausgabe.="<td>";
		$ausgabe.= "<br><b>".$sstats[$science][gamename]."</b><br>";
		$detausgabe = "";
		foreach ($value as $level => $number) {
			if ($level != "sum") {
				if ($number > 0) {
					$detausgabe.="Level: $level/$number &nbsp;&nbsp;&nbsp; -";
				}
			}
		}
		$ausgabe.="Gesamt Spieler: <b><i>".pointit($sciencestats[$science][sum])."</i></b><br>";
		$detausgabe = chopp($detausgabe);
		$ausgabe.="$detausgabe<br>";
		$ausgabe.="</td>";

		if ($i % 3 == 2) {
			$ausgabe.="</tr>";
		}
		$i++;
	}
	$ausgabe.="</table>";
}

// Units
if ($inner == "units") {
	$sum = 0;
	$units = array();
	foreach ($races as $trace) {
		$unitsettings[$trace] = assocs("select * from military_unit_settings where race='$trace'","type");
		$rnumber[$trace] = single("select count(*) from status where alive > 0 and race='$trace'");
		$back = assocs("select offspecs,defspecs,elites,elites2,techs from status where alive > 0 and race ='".$trace."'");
		foreach ($back as $temp) {
			foreach ($temp as $key => $value) {
				$units[$trace][$key] += $value;
				$all[$key] += $value;
				$sum += $value;
			}
		}
	}

	$ausgabe.="<br><b><i> Einheiten gesamt: ".pointit($sum)."</i></b><br><br>";

	foreach ($all as $key => $value) {
		$ausgabe.="$key: ".pointit($value)."<br>";
	}
	$ausgabe.="<br><br>Nach Fraktionen pro Spieler:<br><br>";
	foreach ($units as $trace => $tunits) {
		foreach ($tunits as $name => $number) {
			$ausgabe.=$unitsettings[$trace][$name][name]." - ".pointit(round($number/$rnumber[$trace]))."<br>";
		}
		$ausgabe.="<br>";
	}
}
if (!$inner) {
	echo "
		<form action=$self>
			Stats zu: <Select name=inner>
						<option value=buildings>Gebäude</option>
						<option value=sciences>Forschungen</option>
						<option value=units>Militäreinheiten</option>
					</select>
			<br><br>
			<input type=hidden name=action value=adminstats>
			<input type=submit value=Zeigen>
		</form>
	";
}

$ausgabe = "<table class=\"body\"><tr><td>".$ausgabe."</td></tr></table>";


?>
