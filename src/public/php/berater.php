<?

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($ia and $ia != "killqu" and $ia != "bonus") { $ia = "";
};
if ($innestaction and $innestaction != "next") { $innestaction = "";
};
if ($view and $view != 0 and $view != 1) : unset($view);
endif;

$n = floor($n);
// Anzahl an Einheiten (ganzzahlen) die vernichtet werden sollen
$n < 0 ? $n = 0 : 1;
$n = $what == "sc" ? 1 : $n;

$errormsg = "";
//für tpl
$beschr = "";
$infomsg = "";
$thissite = "berater";

//if ($what and ($what != "geb" or $what != "sa" or $what != "ma" or $what != "hm" or $what != "sc"))	{ $what = "";};

// Die anderen Übergabevariablen wie $what, $type, $t stehen in größerer Relation zueinander und können erst nach dem Game.php-Include
// überprüft werden

// Übergabevariablen werden gecheckt - wenn etwas falsch ist wird proceed nicht auf 1 gesetzt -> es geht nicht weiter -> fehlermeldung

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once ("../../inc/ingame/game.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

// Skalarvariablen

$t = $time;
// interne Zeit für Beraterskript
if ($globals[roundstatus] == 0) { $t = $globals[roundstarttime] + 1;
};

$x = 0;

define(MAXLANDPREISGET, 150000);
// WIeviel man maximal pro Hektar land an geld zurückbekommt
#### (Runde 21 eig. abgeschafft, aber 20 Mio sollten nicht so schnell erreich werden können)
define(LAND_ERSTATTUNG_FAKTOR, 0.60);
$ausgabe_mil = "";
$ausgabe_milaway = "";
$ausgabe_spy = "";
$ausgabe_geb = "";
$ausgabe_for = "";

$hour = date("H");

$forname = "";
$searchtime = "";
$propriate_action_1 = "";
$propriate_action_2 = "";
$propriate_action_3 = "";
$total = 0;
$remain = 0;

$goon = 1;
# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

//Status vom mili  aus der DB holen - Dark-john 16.05.2012
$spiestotal = spiestotal($status{id}, 5, 1);
$miltotal = miltotal($status{id}, 5, 1);
$totalCarriers = getTotalCarriers();

//Maximale anzahl an spys / units
$maxunits = maxunits(mil);
$maxspies = maxunits(spy);


// Arrays

$milnames = array();
$milbuild = array();
$milsorted = array();
$milaway = array();
$milawaysorted = array();

$spynames = array();
$spybuild = array();
$spysorted = array();

$gebnames = array();
$gebbuild = array();
$gebsorted = array();

$queries = array();

$for = array();

$reg_findings = array();

if (isset($view)) {
	$status[beraterview] = $view;
	$queries[] = "update status set beraterview=$view where id=$id";
}

$tplHourCol = array();

for ($i = 1; $i < 21; $i++) {

	$current = "";

	if ($status[beraterview] == 1) {
		if ($hour + $i >= 24)
			$current = ($hour + $i - 24);
		else
			$current = ($hour + $i);
	} else
		$current = $i;

	array_push($tplHourCol, $current);

}

$tpl -> assign("HOURCOL", $tplHourCol);

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

if ($ia == "killqu") {

	$proceed = check_validity($what, $type, $killtime);

	if ($proceed) {

		//all
		$searchtime = get_hour_time($t) + $killtime * 60 * $globals[roundtime];
		$total = row(get_propriate_action($what, $type, "select number"));
		$total = $total[0];
		//endall

		$percent_shredder = min(ceil($status[land] / LAND_SHREDDER_PER_PERCENT_ADD_HA), LAND_SHREDDER_PER_PERCENT_MAX_HA / LAND_SHREDDER_PER_PERCENT_ADD_HA);

		if ($innestaction == "next") {

			if ($total >= $n) {

				//all
				$remain = $total - $n;
				$ok = 0;
				//endall

				//fos
				if ($what == "sc") {
					$ok = 1;
					$beschr = "Du hast soeben die Forschung an \"$forname\" abgebrochen.";
					$tpl -> assign("MSG", $beschr);
				}
				//endfos

				//gebsland
				if ($what == "geb" and $type != "Land") {
					$ok = 1;
					$beschr = "Du hast soeben den Bau von $n $type abgebrochen, welche in $killtime Stunden fertiggestellt worden wären.";
					$tpl -> assign("MSG", $beschr);
				}
				if ($what == "geb" and $type == "Land" and getServertype() == "classic") {
					$ok = 1;
					$landpreis = landkosten();
					$erstPreis = min($landpreis * $percent_shredder / 100, LAND_SHREDDER_PER_MAX_HA);
					$geldbacksumme = floor($erstPreis) * $n;
					$landgeldstring = ",money=money+$geldbacksumme";
					$beschr = "Du hast soeben die Annektierierung von $n $type abgebrochen, welches in $killtime Stunden eingetroffen wäre. Teile des betroffenen Landes konnten für insgesamt " . pointit($geldbacksumme) . " Cr verkauft werden.";
					$tpl -> assign("MSG", $beschr);
					$status{money} += $geldbacksumme;
					$status{nw} = nw($status{id});
					$queries[] = "update status set nw=" . $status[nw] . "$landgeldstring where id=$id";
				}
				//endgebsland

				//dark-john
				//spies
				if ($what == "sa") {
					//Spys dürfen nur entlassen werden wenn oc über 100% besteht , dark-john
					$spiesentlassen = $spiestotal{all} - $maxspies;
					if ($spiestotal{all} < $maxspies || ($spiestotal{all} - $n) < $maxspies) {
						$ok = 0;
						$tpl -> assign("ERROR", "Du darfst nur noch " . $spiesentlassen . " Spionageeinheiten entlassen! ");
					} else {
						$ok = 1;
						$beschr = "Du hast soeben die Ausbildung von $n " . $spynames[$type][name] . " abgebrochen, welche in $killtime Stunden ausgebildet worden wären.";
						$tpl -> assign("MSG", $beschr);
					}
				}
				//endspies
				
				$milentlassennof = $miltotal{all} - $totalCarriers - $maxunits;
				$milcarrierentlassen = $totalCarriers - $maxunits;
				$milentlassen=$miltotal{all}-$maxunits;

				//Es dürfen nur einheiten entlassen werden wenn man 100% im OC steht
				if ($status['race'] == "nof" && ($what == "ma" || $what =="hm") && (($n > $milcarrierentlassen && $type == 24) || ($n > $milentlassennof && $type != 24))) {
					$ok = 0;
					$tpl -> assign("ERROR", "Du darfst nur " . $milentlassennof . " normale Einheiten entlassen oder " . $milcarrierentlassen . " Carrier entlassen");
				} elseif (($miltotal{all} < $maxunits || $n>$milentlassen ) && $status['race'] != "nof" && ($what == "ma" || $what =="hm")) {
					$ok = 0;
					$tpl -> assign("ERROR", "Du darfst keine Einheiten entlassen wenn du nicht im Overcharge bist! ");
				} else {

					//unitmill
					if ($what == "ma") {
						$ok = 1;
						$beschr = "Du hast soeben die Ausbildung von $n " . $milnames[$type][name] . " abgebrochen, welche in $killtime Stunden ausgebildet worden  wären.";
						$tpl -> assign("MSG", $beschr);
					}
					if ($what == "hm") {
						$ok = 1;
						$beschr = "Du hast soeben die Heimkehr von $n " . $milnames[$type][name] . " abgebrochen, welche in $killtime Stunden heimgekehrt wären.";
						$tpl -> assign("MSG", $beschr);
						$status[$type] -= $n;
						$status{nw} = nw($status{id});
						$queries[] = "update status set nw=" . $status[nw] . " where id=$id";
					}
				}
				//endunitmill

				//all
				if ($ok) {
					$queries[] = get_propriate_action($what, $type, "delete from");
					$queries[] = get_propriate_action($what, $type, "log");
					if ($remain)
						$queries[] = get_propriate_action($what, $type, "insert into");
				}
				unset($ia);
				//endall
			} else {
				//gebsland
				if ($what == "geb" and $type != "Land") {$errormsg = "Soviele Gebäude ($n/$total) kannst du nicht abreißen!";
					$tpl -> assign('ERROR', $errormsg);
				}
				if ($what == "geb" and $type == "Land") {$errormsg = "Soviel Land ($n/$total) kannst du nicht abstoßen!";
					$tpl -> assign('ERROR', $errormsg);
				}
				//endgeblans
				if (!$total)
					$total = 0;
				//spes
				if ($what == "sa") {$errormsg = "Die Ausbildung sovieler " . $spynames[$type][name] . " ($n von $total) kannst du nicht abbrechen!";
					$tpl -> assign('ERROR', $errormsg);
				}
				//endspies

				//unitsmill
				if ($what == "ma") {$errormsg = "Die Ausbildung sovieler " . $milnames[$type][name] . " ($n von $total) kannst du nicht abbrechen!";
					$tpl -> assign('ERROR', $errormsg);
				}
				if ($what == "hm") {$errormsg = "Soviele " . $milnames[$type][name] . " ($n von $total) kannst du nicht entlassen!";
					$tpl -> assign('ERROR', $errormsg);
				}
				//endunitsmill

				//all
				unset($innestaction);
				//endall
			}

		} else {
			if ($total)
				print_kill_output($what, $type, $killtime);
			else
				unset($ia);
		}
	} else {
		unset($ia);
	}
} else {
	unset($ia);
}

db_write($queries);

if (!$ia && $goon) {

	$tpl_Tables = array();

	//unitsmill
	$milnames = assocs("select name, unit_id, race, type from military_unit_settings where race='$status[race]' or race='all' order by sort_order", "unit_id");
	$milnamesA = assocs("select name, unit_id, race, type from military_unit_settings where race='$status[race]' order by sort_order", "type");
	$milbuild = rows("select unit_id,number,time from build_military where user_id='$id'");
	$milaway = rows("select unit_id,number,time from military_away where user_id='$id'");

	$tpl_Table = array();
	$tpl_Table["name"] = "Militärausbildung";

	foreach ($milbuild as $value) {

		$x = floor(($value[2] - $t) / ($globals[roundtime] * 60));
		if ($value[0] == 40)
			$value[0] = $milnamesA['elites']['unit_id'];
		if ($value[0] == 41)
			$value[0] = $milnamesA['elites2']['unit_id'];
		if ($value[0] == 42)
			$value[0] = $milnamesA['techs']['unit_id'];

		$milsorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($milsorted)) {

		$tpl_Rows = array();

		foreach ($milnames as $ky => $vl) {

			$tpl_Details = array();

			for ($o = 0, $u = 1; $o <= 19; $o++, $u++) {

				if ($milsorted[$ky][$o]) {
					$milsorted[$ky][$o] = pointit($milsorted[$ky][$o]);
					array_push($tpl_Details, "<a href=berater.php?ia=killqu&what=ma&type=$ky&killtime=$u class=\"linkAuftableInner\">" . $milsorted[$ky][$o] . "</a>");
				} else {
					array_push($tpl_Details, "-");
				}

			}

			array_push($tpl_Rows, array("name" => $vl[name], "details" => $tpl_Details));

		}

		$tpl_Table["rows"] = $tpl_Rows;

	} else {
		$tpl_Table["error"] = "Kein Militär in Bau!";
	}

	array_push($tpl_Tables, $tpl_Table);

	// Militäraway
	$tpl_Table = array();
	$tpl_Table["name"] = "Heimkehrendes Militär";

	foreach ($milaway as $value) {

		$x = floor(($value[2] - $t) / ($globals[roundtime] * 60));
		if ($value[0] == 40)
			$value[0] = $milnamesA['elites']['unit_id'];
		if ($value[0] == 41)
			$value[0] = $milnamesA['elites2']['unit_id'];
		if ($value[0] == 42)
			$value[0] = $milnamesA['techs']['unit_id'];
		$milawaysorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($milawaysorted)) {

		$tpl_Rows = array();

		foreach ($milnames as $ky => $vl) {

			$tpl_Details = array();

			for ($o = 0, $u = 1; $o <= 19; $o++, $u++) {

				if ($milawaysorted[$ky][$o]) {
					$milawaysorted[$ky][$o] = pointit($milawaysorted[$ky][$o]);
					array_push($tpl_Details, "<a href=berater.php?ia=killqu&what=hm&type=$ky&killtime=$u class=\"linkAuftableInner\">" . $milawaysorted[$ky][$o] . "</a>");
				} else {
					array_push($tpl_Details, "-");
				}

			}

			array_push($tpl_Rows, array("name" => $vl[name], "details" => $tpl_Details));

		}

		$tpl_Table["rows"] = $tpl_Rows;

	} else {
		$tpl_Table["error"] = "Kein Militär auf Heimkehr!";
	}

	array_push($tpl_Tables, $tpl_Table);

	// Spione
	$spynames = assocs("select name, unit_id from spy_settings where race='$status[race]' or race='all'", "unit_id");
	$spybuild = rows("select unit_id, number, time from build_spies where user_id='$id'");

	$tpl_Table = array();
	$tpl_Table["name"] = "Spionausbildung";

	foreach ($spybuild as $value) {

		$x = floor(($value[2] - $t) / ($globals[roundtime] * 60));
		$spysorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($spysorted)) {

		$tpl_Rows = array();

		foreach ($spynames as $ky => $vl) {

			$tpl_Details = array();

			for ($o = 0, $u = 1; $o <= 19; $o++, $u++) {

				if ($spysorted[$ky][$o]) {
					$spysorted[$ky][$o] = pointit($spysorted[$ky][$o]);
					array_push($tpl_Details, "<a href=berater.php?ia=killqu&what=sa&type=$ky&killtime=$u class=\"linkAuftableInner\">" . $spysorted[$ky][$o] . "</a>");
				} else {
					array_push($tpl_Details, "-");
				}

			}

			array_push($tpl_Rows, array("name" => $vl[name], "details" => $tpl_Details));

		}

		$tpl_Table["rows"] = $tpl_Rows;

	} else {
		$tpl_Table["error"] = "Keine Spione in Bau!";
	}

	array_push($tpl_Tables, $tpl_Table);

	// Gebäude
	$gebnames = assocs("select name, name_intern,building_id from buildings", "name_intern");
	$gebnames[land][name] = "Land";
	$gebbuild = rows("select building_name,number,time from build_buildings where user_id='$id'");

	$tpl_Table = array();
	$tpl_Table["name"] = "Gebäudeproduktion & Landannektierung";

	foreach ($gebbuild as $value) {

		$x = floor(($value[2] - $t) / ($globals[roundtime] * 60));
		$gebsorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($gebsorted)) {

		$tpl_Rows = array();

		foreach ($gebsorted as $ky => $vl) {

			$tpl_Details = array();

			for ($o = 0, $u = 1; $o <= 19; $o++, $u++) {

				if ($gebsorted[$ky][$o]) {
					$gebsorted[$ky][$o] = pointit($gebsorted[$ky][$o]);
					array_push($tpl_Details, "<a href=berater.php?ia=killqu&what=geb&type=" . $gebnames[$ky][name] . "&killtime=$u class=\"linkAuftableInner\">" . $gebsorted[$ky][$o] . "</a>");
				} else {
					array_push($tpl_Details, "-");
				}
			}

			array_push($tpl_Rows, array("name" => $gebnames[$ky][name], "details" => $tpl_Details));

		}

		$tpl_Table["rows"] = $tpl_Rows;

	} else {
		$tpl_Table["error"] = "Keine Gebäude in Bau! Kein zu annektierendes Land!";
	}

	array_push($tpl_Tables, $tpl_Table);

	$tpl -> assign("TABLES", $tpl_Tables);

	// Forschung

	$for = row("select name,time from build_sciences where user_id='$id'");

	$tpl_Fos = array();
	$tpl_Fos["error"] = "ja";

	if ($for) {

		$x = floor(($for[1] - $t) / ($globals[roundtime] * 60)) + 1;

		if ($status[beraterview] == 1) {
			$x = datum("d.m.Y, H", $for[1]) . ":00 Uhr";
		}

		preg_match("/(\w{3})(\d{1,2})/", $for[0], $reg_findings);
		$forname = row("select gamename from sciences where name='$reg_findings[1]' and typenumber='$reg_findings[2]'");

		$tpl_Fos["name"] = $forname[0];
		$tpl_Fos["time"] = $x;
		$tpl_Fos["link"] = $for[0];
		$tpl_Fos["error"] = "nein";

	}

	$tpl -> assign("FOS", $tpl_Fos);
	$tpl -> assign("LINKOPT", ($status[beraterview] == 1 ? "0" : "1"));
	$tpl -> assign("LINKTXT", ($status[beraterview] == 1 ? "verbleibende Stunden" : "Uhrzeit"));
	$tpl -> assign("YELLOWDOT", $yellowdot);

}

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

//header
require_once ("../../inc/ingame/header.php");

$tpl -> assign("USERINPUT", $userinput);

//Fehler
if ($tpl -> get_template_vars('ERROR') != '') {
	$tpl -> display('fehler.tpl');
}
//Meldung
if ($tpl -> get_template_vars('MSG') != '') {
	$tpl -> display('sys_msg.tpl');
}

$tpl -> display('berater.tpl');
require_once ("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//
?>