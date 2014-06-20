<?
require("../../../includes.php");
connectdb();
mysql_select_db("syndicates");
set_time_limit(3600);


exit(); # fehler beim dividieren in zeile 151 oder so

	$bildwidth = $chartwidth = 1300;
	$bildheight = $chartheight = 480;
	$diagram_max_height = $bildheight*0.95;
	$x_achse_skala_zwischenstriche = 9;
	$x_achse_skala_pixelhoehe = 8;
	$x_achse_skala_anschriftabstandpixel = 10;

	$y_achse_skala_zwischenstriche = 4;
	$y_achse_skala_anschriftabstandpixel = 15;
	$y_achse_skala_pixelbreite = 8;
	$drawdata = $data_temp = array();
	

//Initialisierung
$daten2 = assocs("select sum(landgrab) as landgrab2, floor(time / 86400)-13633 as tl, count(*) as count from attacklogs where winner = 'a' and ginactive = '' group by tl order by time asc");

foreach ($daten2 as $ky => $vl) {
	$daten[$ky] = array("value" => $vl['landgrab2']/$vl['count'], "value2" => $vl['count'], "time" => $vl['tl']);
}


// Zeilen pro Pixel bestimmen
$mittelungsintervall = array(-0,0); // Intervall um den Messwert herum das berücksichtigt und über das dann gemittelt wird
//$draw_columns = array('clicks_last_minute', 'users_online', 'sload');
$draw_columns = array('value');
$gewichtung = array(1,100, 1);


/*
hallo - Test



$draw_columns = array('function', 'function2');
$daten = array();
	$cp = 4000;
	$rp = 1950;
	$landanteil = 0.8;
	$umwandlungsdauer = 3;
	
	for($i = 0.0; $i <= 3; $i+=0.001) {
		$daten[] = array('time' => $i, 'function' => ($landanteil - $i) / $umwandlungsdauer * ($cp-$rp*(1-$i*2.5)), 'function2' => -1/$umwandlungsdauer * ($cp-$rp*(1-$i*2.5)) + ($landanteil-$i)/$umwandlungsdauer*2.5*$rp);
	}
*/
	
	
$mode = "2time";
									
// Hintergrund laden, Farben initialisieren, Bild vorbereiten
{
	$img = imagecreatetruecolor($bildwidth, $bildheight);
	
	$weiss  = imagecolorallocate ($img, 255, 255, 255);
	$schwarz = imagecolorallocate($img, 0, 0, 0);
	$rot = imagecolorallocate($img, 255, 0, 0);
	$gruen = imagecolorallocate($img, 0, 255, 0);
	$blau = imagecolorallocate($img, 0, 0, 255);
	$grey = imagecolorallocate($img, 205, 230, 230);
	$farben = array('rot', 'gruen', 'blau');

	// Bild grau machen
	imagefilledrectangle ($img, 0, 0, $bildwidth, $bildheight, $grey);
	// Rechteck außen rum als Begrenzer
	// imagerectangle ($img, 0, 0, $width-1, $height-1, $schwarz);

}

// Daten durchgehen um Grenzen sowie Nullpunkt des Koordinatensystems zu finden;
	$y_null = $bildheight-1;
	$x_min_global = $x_max_global = false;
	foreach ($draw_columns as $ky => $vl) {
		$x_min[$vl] = $x_max[$vl] = false;
		$o = 0;
		$data_temp[$vl] = array(); 
		foreach ($daten as $ky2 => $vl2) {
			($x_min[$vl]===false or $x_min[$vl] > $vl2['time']) ? $x_min[$vl] = $vl2['time'] : 1;
			($x_max[$vl]===false or $x_max[$vl] < $vl2['time']) ? $x_max[$vl] = $vl2['time'] : 1;

			($x_min_global===false or $x_min_global > $vl2['time']) ? $x_min_global = $vl2['time'] : 1;
			($x_max_global===false or $x_max_global < $vl2['time']) ? $x_max_global = $vl2['time'] : 1;

			// Daten anders ordnen - zum Mitteln über mehrere Datenpaare
			$data_temp[$vl][$o++] = array($vl2['time'], $vl2[$vl] * $gewichtung[$ky]);
		}
	}
	$y_min_global = $y_max_global = false;
	foreach ($draw_columns as $vl) {
		$y_max[$vl] = $y_min[$vl] = false;
			// Mitteln entsprechend den Angaben in $mittelungsintervall s.o.
			for ($o = 0; $o < count($data_temp[$vl]); $o++) {
				$sum = $data_temp[$vl][$o][1]; $count = 1;
				// Werte im Intervall > aktueller Wert
				for ($j = $o+1; $j < count($data_temp[$vl]) && $data_temp[$vl][$j][0] <= $data_temp[$vl][$o][0] + $mittelungsintervall[1]; $j++) {
					$sum += $data_temp[$vl][$j][1]; $count++;
				}
				// Werte im Intervall < aktueller Wert
				for ($j = $o-1; $j > 0 && $data_temp[$vl][$j][0] >= $data_temp[$vl][$o][0] + $mittelungsintervall[0]; $j--) {
					$sum += $data_temp[$vl][$j][1];	$count++;
				}
				$new_y_value = $sum / $count;
				$drawdata[$vl][] = array($data_temp[$vl][$o][0], $new_y_value);
				($y_min[$vl]===false or $y_min[$vl] > $new_y_value) ? $y_min[$vl] = $new_y_value : 1;
				($y_max[$vl]===false or $y_max[$vl] < $new_y_value) ? $y_max[$vl] = $new_y_value : 1;
				($y_max_global===false or $y_max_global < $new_y_value) ? $y_max_global = $new_y_value : 1;
				($y_min_global===false or $y_min_global > $new_y_value) ? $y_min_global = $new_y_value : 1;
			}
	}
		// y-min nur von bedeutung, wenn negativ
		if ($y_min_global > 0) $y_min_global = 0;
		if ($y_min_global < 0) {
			$y_null_temp = floor(($bildheight) * ($y_max_global / ((-1) * $y_min_global)) / (1 + ($y_max_global / ((-1) * $y_min_global))))-1;
			//echo "y_min: ".$y_min_global."<br>y_null_temp: $y_null_temp -- $y_null<br>";
			if ($y_null_temp < $y_null) $y_null = $y_null_temp;
		}


	

	$pix_per_x = $bildwidth / ($x_max_global - $x_min_global);
	$x_null = $x_min_global >= 0 ? 0 : ($x_max_global < 0 ? $bildwidth : round($pix_per_x * (-1) * $x_min_global));

// Koordinatenachsen zeichnen
{	$fw = imagefontwidth(2);

		// y-Order bestimmen um Schriftlänge zu bestimmen, woraus wiederum der Abstand der y-Achse zum linken Rand ermittelt wird
		// y-Order wird im näcshten if-Block für ($x_null == 0) benötigt
		if ($y_min_global >= 0) $temp = $y_max_global;
		else $temp = ($y_max_global - $y_min_global);
		$yordnung = 0;
		if ($temp >= 1) {
			while(true) {
				$div = pow(10, $yordnung);
				if ($temp / $div < 10) break; else $yordnung++;
			}

		} else {
			while(true) {
				$div = pow(10, $yordnung);
				if ($temp / $div >= 1) break; else $yordnung--;
			}
		}

	// X/Y-Nullpunkte verschieben, wenn die y-Achse ganz links und/oder die x-Achse ganz unten ist, damit die Achsenbeschriftungen angeschrieben werden können
	if ($x_null == 0) {
		$tl = textlength(pow(10, $yordnung)."12", $fw);
		if ($y_min_global < 0) $tl += textlength("-", $fw); # Minuszeichen
		$x_null += $tl+round($y_achse_skala_pixelbreite/2) + 2;
		$chartwidth -= $tl+round($y_achse_skala_pixelbreite/2) + 2;
	}
	if ($y_null == $bildheight-1) {
		 $y_null -= 16;
		 $chartheight -= 16;
	}
	// X-Achse
	imageline($img, 0, $y_null, $bildwidth-1, $y_null, $schwarz);
	// X-Achsenskalierung
		// Ordnung des Wertebereichs auf der X-Achse bestimmen
	//echo "ordnung: $yordnung<br>";
	if ($mode != "time") {
		$temp = ($x_max_global - $x_min_global);
		$ordnung = 0;
		if ($temp >= 1) {
			while(true) {
				$div = pow(10, $ordnung);
				if ($temp / $div < 10) break; else $ordnung++;
			}

		} else {
			while(true) {
				$div = pow(10, $ordnung);
				if ($temp / $div >= 1) break; else $ordnung--;
			}
		}

		// Simulieren für jede Ordnung, ob sie noch passt, bis es dann nicht mehr passt
		for ($o = $ordnung, $k=0; true; $o--, $k++) {
			$start_x = ordnungceil($x_min_global, $o);
			$div_gen = pow(10, $o);

			for ($so = 1; $so <= 5; $so++) {
				if ($so != 1 && $so != 2 && $so != 5) continue 1;
				$length_needed = 0;
				$div = $div_gen / $so;
				if ($div > 0) { // $div == 0 bei Rundungsfehlern => Endlosschleife vermeiden
					for ($u = $start_x; $u <= $x_max_global; $u+=$div) {
						$skalenstrich_pixel = x($u);
						$u = round_science($u);
						$utemp = $u;
						//$u = date("d.M, H:i", $u);
						$text_left_pixel = center($skalenstrich_pixel, $u, $fw);
						$length_needed += textlength($u, $fw) + $x_achse_skala_anschriftabstandpixel;
						$u = $utemp;
					}
					$length_needed -= $x_achse_skala_anschriftabstandpixel; # | | | | hat nur 3 Zwischen räume bei 4 Strichen
					//echo "O: $o - Semi_O: $semi_o LN: $length_needed<br>";
					if ($length_needed > $chartwidth) {
						if ($so > 1) $ordnungfinal = $o; else $ordnungfinal = $o+1;
						$semiordnungfinal = $lastsemiordnung;
						break 2;
					} else $lastsemiordnung = $so;
				}
			}
			// Bei zu kleinen Zahlen (10^-6) je nach Bildgröße ist bereits die erste Ordnung nicht passend ... Notabbruch
			if ($k >= 6) {
				$ordnungfinal = $ordnung;
				$semiordnungfinal = 1;
				break;
			}
		}

		//echo "LASTORDNUNG: ".$ordnungfinal."<br>LAST-SEMI-ORDNUNG: $semiordnungfinal<br>LN: $length_needed<br>";


		
		{
			$start_x = ordnungceil($x_min_global, $ordnungfinal);
			$div = pow(10, $ordnungfinal);
			$div /= $semiordnungfinal;
			if ($div > 0) { // $div == 0 bei Rundungsfehlern => Endlosschleife vermeiden
				for ($u = $start_x; $u <= $x_max_global; $u+=$div) {
					$skalenstrich_pixel = x($u);
					$u = round_science($u);
					$utemp = $u;
					//$u = date("d.M, H:i", $u);
					$text_left_pixel = center($skalenstrich_pixel, $u, $fw);
					// Skalenstrich zeichnen
					imageline($img, $skalenstrich_pixel, $y_null-round($x_achse_skala_pixelhoehe/2), $skalenstrich_pixel, $y_null+round($x_achse_skala_pixelhoehe/2), $schwarz);
					for ($m = 1; $m <= $x_achse_skala_zwischenstriche; $m++) {
						$temp_pixel = x($u - $m*$div / ($x_achse_skala_zwischenstriche+1));
						imageline($img, $temp_pixel, $y_null-round($x_achse_skala_pixelhoehe/4), $temp_pixel, $y_null+round($x_achse_skala_pixelhoehe/4), $schwarz);
						if ($u+$div > $x_max_global) {
							$temp_pixel = x($u + $m*$div / ($x_achse_skala_zwischenstriche+1));
							imageline($img, $temp_pixel, $y_null-round($x_achse_skala_pixelhoehe/4), $temp_pixel, $y_null+round($x_achse_skala_pixelhoehe/4), $schwarz);
						}
					}
	
					if ($text_left_pixel + textlength($u, $fw) <= $bildwidth && $text_left_pixel >= 0 && betrag(($x_max_global-$x_min_global) / $u) <= 10000000)
						imagestring ($img, 2, $text_left_pixel, $y_null+3, $u, $schwarz);
						//echo "u: $u $div<br>";
					$u = $utemp;
				}
			}
		}
	}
	else if ($mode == "time") {

		$orders = array(	// Intervall-Länge -- Datestring -- Zwischenstriche -- Yearsprung -- Monthsprung -- Daysprung -- Hoursprung -- Minutesprung -- Datestrings bei Sprung
			// Datestrings bei Sprung-Semantik: 1. Wert: Sprung über ein Jahr, 2. Wert: Sprung über einen Monat, 3. Wert Sprung über einen Tag, 4. Wert Sprung über eine Stunde
			array(100 * 365 * 86400, 	"Y", 9, 100, 0, 0, 0, 0, ""),
			array(50 * 365 * 86400, 	"Y", 4, 50, 0, 0, 0 ,0, ""),
			array(20 * 365 * 86400, 	"Y", 1, 20, 0, 0, 0 ,0, ""),
			array(10 * 365 * 86400,		"Y", 9, 10, 0, 0, 0 ,0, ""),
			array(5 * 365 * 86400, 		"Y", 4, 5, 0, 0, 0 ,0, ""),
			array(2 * 365 * 86400, 		"Y", 1, 2, 0, 0, 0 ,0, ""),
			array(1 * 365 * 86400, 		"Y", 3, 1, 0, 0, 0 ,0, ""),
			array(1/4 * 365 * 86400, 	"M", 2, 0, 3, 0, 0 ,0, array("Y")),
			array(1/12 * 365 * 86400, 	"M", 0, 0, 1, 0, 0 ,0, array("Y")),
			array(86400, 					"j.", 0, 0, 0, 1, 0 ,0, array("Y, M", "M")),
			array(86400/2, 				"H:i", 1, 0, 0, 0, 12 ,0, array("Y, j. M", "j. M", "j.")),
			array(86400/3, 				"H:i", 1, 0, 0, 0, 8 ,0, array("Y, j. M", "j. M", "j.")),
			array(86400/4, 				"H:i", 1, 0, 0, 0, 6 ,0, array("Y, j. M", "j. M", "j.")),
			array(86400/6, 				"H:i", 3, 0, 0, 0, 4 ,0, array("Y, j. M", "j. M", "j.")),
			array(86400/8, 				"H:i", 2, 0, 0, 0, 3 ,0, array("Y, j. M", "j. M", "j.")),
			array(86400/12, 				"H:i", 1, 0, 0, 0, 2 ,0, array("Y, j. M", "j. M", "j. M")),
			array(3600, 					"H:i", 3, 0, 0, 0, 1 ,0, array("Y, j. M", "j. M", "j. M")),
			array(3600/2, 					"H:i", 2, 0, 0, 0, 0 ,30, array("Y, j. M", "j. M", "j. M")),
			array(3600/4, 					"H:i", 2, 0, 0, 0, 0 ,15, array("Y, j. M", "j. M", "j. M")),
			array(3600/6, 					"i", 1, 0, 0, 0, 0 ,10, array("Y, j. M", "j. M", "j. M", "H:i")),
			array(300,	 					"i", 4, 0, 0, 0, 0 ,5, array("Y, j. M", "j. M", "j. M", "H:i")),
			array(60,	 					"i", 0, 0, 0, 0, 0 ,1, array("Y, j. M", "j. M", "j. M", "H:i"))
			
		);
	
		$temp = ($x_max_global - $x_min_global);
		$ordnung = 0;
		//echo "temp: $temp<br>";
		for ($i = 0; true; $i++) {
			if ($orders[$i][0] <= $temp) { $ordnung = $i+1; break; }
		}

		function timeHandling() {
				$return = -1; // Standard-Ausgabe für Datestring: 2. Arrayelement im orders-Array oben, wenn $return != -1, dann ist eine Stelle aus dem Array "Datestrings bei Sprung" gemeint
				global $tminutes, $thour, $tday, $tmonth, $tyear;
				if ($tminutes > 59) { $tminutes -= 60; $thour++; $return = 3;}
				if ($thour > 23)	{ $thour -= 24; $tday++; $return = 2; }
				if ($tmonth == 2 && $tday > 28) { $tday -= 28; $tmonth++; $return = 1;}
				else if (in_array($tmonth, array(1, 3, 5, 7, 8, 10, 12)) && $tday > 31) { $tday -= 31; $tmonth++; $return = 1;}
				else if (in_array($tmonth, array(4, 6, 9, 11)) && $tday > 30) { $tday -= 30; $tmonth++; $return = 1;}
				if ($tmonth > 12) { $tmonth -= 12; $tyear++; $return = 0;}
				return $return;
		}
			//echo "ordnung vorher: $ordnung<br>";

		// Simulieren für jede Ordnung, ob sie noch passt, bis es dann nicht mehr passt
		for ($o = $ordnung, $k=0; $o <= 21; $o++, $k++) {
			{	// Start-X ermitteln
				$tyear = date("Y", $x_min_global) + $orders[$o][3];
				$tmonth = date("m", $x_min_global) + $orders[$o][4];
				$tday = date("d", $x_min_global) + $orders[$o][5];
				$thour = date("H", $x_min_global) + $orders[$o][6];
				$tminutes = date("i", $x_min_global) + $orders[$o][7];
				$tseconds = date("s", $x_min_global);
				timeHandling();
				if ($orders[$o][0] >= 3600) {$tminutes = 0;}
				if ($orders[$o][0] >= 86400) {  $thour = 0; }
				if ($orders[$o][0] >= 1/12 * 365 * 86400) { $tday = 0; }
				if ($orders[$o][0] >= 1 * 365 * 86400) { $month = 0; }
				if ($orders[$o][7] && $tminutes % $orders[$o][7] != 0) $tminutes -= $tminutes % $orders[$o][7];
				if ($orders[$o][6] && $thour % $orders[$o][6] != 0) $thour -= $thour % $orders[$o][6];
				if ($orders[$o][4] && $tmonth % $orders[$o][4] != 0) $tmonth -= $tmonth % $orders[$o][4];
				$start_x = strtotime("$tyear-$tmonth-$tday $thour:$tminutes:00");
				if ($start_x+$tseconds - $x_min_global == $orders[$o][0]) $start_x -= $orders[$o][0];
				$div_gen = $orders[$o][0];
				
			}
				//echo "start_x: $start_x+$tseconds - $x_min_global == ".$orders[$o][0]."<br>";

				$length_needed = 0;
				$div = $div_gen;
					for ($u = $start_x; $u <= $x_max_global; $u+=$div) {
						{	// Time für Beschriftung mitlaufen lassen
							$tyear = date("Y", $u-$div) + $orders[$o][3];
							$tmonth = date("m", $u-$div) + $orders[$o][4];
							$tday = date("d", $u-$div) + $orders[$o][5];
							$thour = date("H", $u-$div) + $orders[$o][6]; 
							$tminutes = date("i", $u-$div) + $orders[$o][7];
							$return = timeHandling();
							if ($return != -1 && $orders[$o][8][$return]) {
								$text = date($orders[$o][8][$return], $u);
							} else {
								$text = date($orders[$o][1], $u);
							}

						}
						$skalenstrich_pixel = x($u);
						$text_left_pixel = center($skalenstrich_pixel, $text, $fw);
						$length_needed += textlength($text, $fw) + $x_achse_skala_anschriftabstandpixel;
					}
					$length_needed -= $x_achse_skala_anschriftabstandpixel; # | | | | hat nur 3 Zwischen räume bei 4 Strichen
					echo "O2: $o - Semi_O: $semi_o LN: $length_needed<br>";
					$ordnungfinal = $o;
					if ($length_needed > $chartwidth) {
						$ordnungfinal = $o-1;
						break;
					}

			// Bei zu kleinen Zahlen (10^-6) je nach Bildgröße ist bereits die erste Ordnung nicht passend ... Notabbruch
			if ($k >= 22) {
				$ordnungfinal = $ordnung;
				break;
			}
		}


		//echo "LASTORDNUNG: ".$ordnungfinal."<br>LAST-SEMI-ORDNUNG: ".$orders[$ordnungfinal][0]."<br>LN: $length_needed<br>
	//	xminglobal: $x_min_global<br>";


		
		{
			{	// Start-X ermitteln
				$tyear = date("Y", $x_min_global) + $orders[$ordnungfinal][3];
				$tmonth = date("m", $x_min_global) + $orders[$ordnungfinal][4];
				$tday = date("d", $x_min_global) + $orders[$ordnungfinal][5];
				$thour = date("H", $x_min_global) + $orders[$ordnungfinal][6];
				$tminutes = date("i", $x_min_global) + $orders[$ordnungfinal][7];
				$tseconds = date("s", $x_min_global);
				timeHandling();
				if ($orders[$ordnungfinal][0] >= 3600) {$tminutes = 0;}
				if ($orders[$ordnungfinal][0] >= 86400) {  $thour = 0; }
				if ($orders[$ordnungfinal][0] >= 1/12 * 365 * 86400) { $tday = 0; }
				if ($orders[$ordnungfinal][0] >= 1 * 365 * 86400) { $month = 0; }
				if ($orders[$ordnungfinal][7] && $tminutes % $orders[$ordnungfinal][7] != 0) $tminutes -= $tminutes % $orders[$ordnungfinal][7];
				if ($orders[$ordnungfinal][6] && $thour % $orders[$ordnungfinal][6] != 0) $thour -= $thour % $orders[$ordnungfinal][6];
				if ($orders[$ordnungfinal][4] && $tmonth % $orders[$ordnungfinal][4] != 0) $tmonth -= $tmonth % $orders[$ordnungfinal][4];
				$start_x = strtotime("$tyear-$tmonth-$tday $thour:$tminutes:00");
				if ($start_x+$tseconds - $x_min_global == $orders[$ordnungfinal][0]) $start_x -= $orders[$ordnungfinal][0];
				$div = $orders[$ordnungfinal][0];
			}
			if ($div > 0) { // $div == 0 bei Rundungsfehlern => Endlosschleife vermeiden
				for ($u = $start_x; $u <= $x_max_global; $u+=$div) {
					{	// Time für Beschriftung mitlaufen lassen
						$tyear = date("Y", $u-$div) + $orders[$ordnungfinal][3];
						$tmonth = date("m", $u-$div) + $orders[$ordnungfinal][4];
						$tday = date("d", $u-$div) + $orders[$ordnungfinal][5];
						$thour = date("H", $u-$div) + $orders[$ordnungfinal][6];
						$tminutes = date("i", $u-$div) + $orders[$ordnungfinal][7];
						$return = timeHandling();
						if ($return != -1 && $orders[$ordnungfinal][8][$return]) {
							$text = date($orders[$ordnungfinal][8][$return], $u);
							
						} else {
							$text = date($orders[$ordnungfinal][1], $u);
						}
						//echo "text: $text -- day: $tday, hour: $thour<br>";
					}
					$skalenstrich_pixel = x($u);
					$text_left_pixel = center($skalenstrich_pixel, $text, $fw);
					// Skalenstrich zeichnen
					imageline($img, $skalenstrich_pixel, $y_null-round($x_achse_skala_pixelhoehe/2), $skalenstrich_pixel, $y_null+round($x_achse_skala_pixelhoehe/2), $schwarz);
					for ($m = 1; $m <= $orders[$ordnungfinal][2]; $m++) {
						$temp_pixel = x($u - $m*$div / ($orders[$ordnungfinal][2]+1));
						imageline($img, $temp_pixel, $y_null-round($x_achse_skala_pixelhoehe/4), $temp_pixel, $y_null+round($x_achse_skala_pixelhoehe/4), $schwarz);
						if ($u+$div > $x_max_global) {
							$temp_pixel = x($u + $m*$div / ($orders[$ordnungfinal][2]+1));
							imageline($img, $temp_pixel, $y_null-round($x_achse_skala_pixelhoehe/4), $temp_pixel, $y_null+round($x_achse_skala_pixelhoehe/4), $schwarz);
						}
					}
	
					if ($text_left_pixel + textlength($text, $fw) <= $bildwidth && $text_left_pixel >= 0)
						imagestring ($img, 2, $text_left_pixel, $y_null+3, $text, $schwarz);
				}
			}
		}
	}


		

	// Y-Achse
	imageline($img, $x_null, 0, $x_null, $bildheight, $schwarz);
		//echo "ordnung: $yordnung<br>";
		// Simulieren für jede Ordnung, ob sie noch passt, bis es dann nicht mehr passt
		for ($o = $yordnung, $k=0; true; $o--, $k++) {
			$div_gen = pow(10, $o);
			//echo "ordnung: $yordnung<br>";
			$start_y = ($y_min_global >= 0 ? 0 : ordnungceil($y_min_global, $o));
			for ($so = 1; $so <= 5; $so++) {
				if ($so != 1 && $so != 2 && $so != 5) continue 1;
				$length_needed = 0;
				$div = $div_gen / $so;
				if ($div > 0) { // $div == 0 bei Rundungsfehlern => Endlosschleife vermeiden
					for ($u = $start_y; $u <= $y_max_global; $u+=$div) {
						$skalenstrich_pixel = y($u);
						$u = round_science($u);
						$utemp = $u;
						//$u = date("d.M, H:i", $u);
						//$text_left_pixel = center($skalenstrich_pixel, $u, $fw);
						$length_needed += textlength("a", $fw) + $y_achse_skala_anschriftabstandpixel;
						$u = $utemp;
					}
					$length_needed -= $y_achse_skala_anschriftabstandpixel; # | | | | hat nur 3 Zwischen räume bei 4 Strichen
					//echo "O: $o - Semi_O: $so LN: $length_needed<br>";
					if ($length_needed > $chartheight) {
						if ($so > 1) $ordnungfinal = $o; else $ordnungfinal = $o+1;
						$semiordnungfinal = $lastsemiordnung;
						break 2;
					} else $lastsemiordnung = $so;
				}
			}
			// Bei zu kleinen Zahlen (10^-6) je nach Bildgröße ist bereits die erste Ordnung nicht passend ... Notabbruch
			if ($k >= 6) {
				$ordnungfinal = $ordnung;
				$semiordnungfinal = 1;
				break;
			}
		}

		//echo "<br>LASTORDNUNG: ".$ordnungfinal."<br>LAST-SEMI-ORDNUNG: $semiordnungfinal<br>LN: $length_needed<br><br>";


		
		{
			$div = pow(10, $ordnungfinal);
			$start_y = ($y_min_global >= 0 ? 0 : ordnungceil($y_min_global, $ordnungfinal));
			$div /= $semiordnungfinal;
			if ($div > 0) { // $div == 0 bei Rundungsfehlern => Endlosschleife vermeiden
				for ($u = $start_y; $u <= $y_max_global; $u+=$div) {
					$skalenstrich_pixel = y($u);
					$u = round_science($u);
					$text_left_pixel = $x_null - round($y_achse_skala_pixelbreite/2) - textlength($u, $fw);
					// Skalenstrich zeichnen
					imageline($img, $x_null-round($y_achse_skala_pixelbreite/2), $skalenstrich_pixel, $x_null+round($y_achse_skala_pixelbreite/2), $skalenstrich_pixel, $schwarz);
					for ($m = 1; $m <= $y_achse_skala_zwischenstriche; $m++) {
						$temp_pixel = y($u - $m*$div / ($y_achse_skala_zwischenstriche+1));
						imageline($img, $x_null-round($y_achse_skala_pixelbreite/4), $temp_pixel, $x_null+round($y_achse_skala_pixelbreite/4), $temp_pixel, $schwarz);
						if ($u+$div > $y_max_global) {
							$temp_pixel = y($u + $m*$div / ($y_achse_skala_zwischenstriche+1));
							imageline($img, $x_null-round($y_achse_skala_pixelbreite/4), $temp_pixel, $x_null+round($x_achse_skala_pixelhoehe/4), $temp_pixel, $schwarz);
						}
					}
	
					if ($skalenstrich_pixel - round(textlength("a", $fw)/2) >= 0 && $skalenstrich_pixel + round(textlength("a", $fw)/2) <= $chartheight && betrag(($y_max_global-$y_min_global) / $u) <= 10000000)
						imagestring ($img, 2, $text_left_pixel, $skalenstrich_pixel-round(textlength("a", $fw)), $u, $schwarz);
						//echo "u: $u $div sp: $skalenstrich_pixel - ".round(textlength("a", $fw)/2)." >= 0 && $skalenstrich_pixel + ".round(textlength("a", $fw)/2)." <= $chartheight -- ".(($y_max_global-$y_min_global) / $u)."<br>";
				}
			}
		}
	
}
	//imagestring ($img, 1, 100, 14, "14:11 - 3.8.", $schwarz);
// Daten zeichnen
{	$i = 0;
	foreach ($draw_columns as $vl) {


		// Zeichnen
		$end_x = $drawdata[$vl][0][0];
		$end_y = $drawdata[$vl][0][1];
		for ($o = 0; $o < count($drawdata[$vl]); $o++) {
			if ($o >= 1) {
			$start_x = $end_x;
			$start_y = $end_y;

				$end_x = $drawdata[$vl][$o][0];
				$end_y = $drawdata[$vl][$o][1];
				$color = $$farben[$i];
				imageline($img, x($start_x, $vl), y($start_y, $vl), x($end_x, $vl), y($end_y, $vl), $color);
			}
		}
		$i++;
	}
}





// Bild auf Dateisystem schreiben
imagejpeg($img, "chart.jpg");



// Funktionen die die Korrekten X/Y-Werte zurückgeben nach Angabe der relativen Werte in Bezug auf den Koordinatenursprung
function y($y, $column) {
	global $y_max, $y_min, $y_null, $bildheight, $y_max_global;
	//$pix_per_y_temp = $y_null / $y_max[$column];
	$pix_per_y_temp = $y_null / $y_max_global;
	return round($y_null-$y*$pix_per_y_temp);
}
function x($x) {
	global $chartwidth, $x_null, $x_max_global, $x_min_global;
	$pix_per_x = $chartwidth / ($x_max_global - $x_min_global);
	if ($x_min_global > 0) $x -= $x_min_global;
	return round($x_null + $x*$pix_per_x);
}

function ordnungceil($vl, $ordnung) {
	if ($vl < 0 )
	return floor($vl/pow(10, $ordnung))*pow(10,$ordnung);
	return ceil($vl/pow(10, $ordnung))*pow(10,$ordnung);
}
function textlength($text, $fontwidth) {
	return strlen($text) * $fontwidth;
}
function center($pixel, $text, $fontwidth) {
	return round(($pixel - textlength($text, $fontwidth)/2));
}
function round_science($what) {
	$speicher = array();
	if (preg_match("/([^E]+)(E.*$)/", "$what", $speicher))
	return (round($speicher[1]*10)/10).$speicher[2];
	else return $what;
}
function betrag($a) {
	return $a < 0 ? -$a : $a;
}




?>
