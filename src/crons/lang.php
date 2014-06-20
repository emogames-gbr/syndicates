<?
		$profiler->init();
        foreach ($statuses as $status) {
            // Nur lebende Spieler updaten
            if ($status{alive} > 0) {
				$negative_energy = 0;
				$totalmilitary = 0;
				$totalspies = 0;
				$milloss = 0;
				$spyloss = 0;
				$lossstring =  "";
				$sciences = $scienceses[$status[id]];
				$partner = $partnerbonuses[$status[id]];
                $status{alive} == 1 ? $mod = 1 : $mod = 0.5;
				$syndikate_data[$status[rid]][totalland] += $status[land]; // Synland zusammenz?hlen zur aktienberechnung sp?ter
			//  echo "<br><br>Before: Geld:".$status{money}." Energie: ".$status{energy}." Fp: ".$status{sciencepoints}." Metal: ".$status{metal}." Name: ".$status{syndicate}."<br>";
				// Ressourcen nur, wenn Spieler nicht inaktiv
				if ($status[lastlogintime] + TIME_TILL_GLOBAL_INACTIVE > $time) {
				// Energie
					list ($energyadd, $energyloss, $energylageradd, $hpenergyadd, $dvdenergyadd) = energyadd($status{id}, 4); # 4 f?r energyloss damit message erstellt werden kann.
					if ($energyloss): $statuses[$status[id]]{energy} = $status[energy]; $messageinserts .= "(2,".$status[id].",$hourtime, '".pointit($energyloss)."'),"; endif;	# Message mit Energyloss vorbereiten
					$statuses[$status[id]]{energy}  += $energyadd;
					if ($statuses[$status[id]]{energy} < 0) {$statuses[$status[id]]{energy} = 0; $mod *= 0.5; $negative_energy = 1;} # Noch mehr Miese f?r andere Ressourcen, Energy mind. 0 setzen
					// Money
					list($moneyadd, $moneylageradd, $hpmoneyadd, $dvdmoneyadd) = moneyadd($status{id});
					$statuses[$status[id]]{money} += round($mod * $moneyadd);
					// Metal
					list($metaladd, $metallageradd, $hpmetaladd, $dvdmetaladd) = metaladd($status{id});
					$statuses[$status[id]]{metal}  += round($mod * $metaladd);
					// Sciencepoints
					list($sciencepointsadd, $sciencepointslageradd, $hpsciencepointsadd, $dvdsciencepointsadd) = sciencepointsadd($status{id});
					$statuses[$status[id]]{sciencepoints}  += round($mod * $sciencepointsadd);
				}
				else {
					$energyadd=0;$energyloss=0;$energylageradd=0;$hpenergyadd=0;$dvdenergyadd=0;
					$moneyadd=0;$moneylageradd=0;$hpmoneyadd=0;$dvdmoneyadd=0;
					$metaladd=0;$metallageradd=0;$hpmetaladd=0;$dvdmetaladd=0;
					$sciencepointsadd=0;$sciencepointslageradd=0;$hpsciencepointsadd=0;$dvdsciencepointsadd=0;
				}

				$safestring[$status[id]] = (round($mod * $moneyadd)).",".(round($mod * $metaladd)).",".(round($mod * $sciencepointsadd)).",".($energyadd).",".($energyloss).",".(round ($mod * $moneylageradd)).",".(round ($mod * $metallageradd)).",".(round ($mod * $sciencepointslageradd)).",".(round ($mod * $energylageradd)).",".(round ($mod * ($hpenergyadd + $hpmoneyadd + $hpmetaladd + $hpsciencepointsadd)));
				# SYNDIKATSRESSOURCEN VERRECHNEN
				$syndikate_data[$status{rid}][dividenden] += round ($mod * ($dvdenergyadd + $dvdmoneyadd + $dvdmetaladd + $dvdsciencepointsadd));
				$syndikate_data_ressourcenadd[$status{rid}][podenergy] += round ($mod * $energylageradd);
				$syndikate_data_ressourcenadd[$status{rid}][podmoney] += round ($mod * $moneylageradd);
				$syndikate_data_ressourcenadd[$status{rid}][podmetal] += round ($mod * $metallageradd);
				$syndikate_data_ressourcenadd[$status{rid}][podsciencepoints] += round ($mod * $sciencepointslageradd);
				$statuses[$status[id]][podpoints] += round ($mod * ($hpenergyadd + $hpmoneyadd + $hpmetaladd + $hpsciencepointsadd));

				// Namechanges wegnehmen, falls noch unter schutz
				if ($status[createtime] + PROTECTIONTIME < $time && $status[nc] > 0) {
					$statuses[$status[id]][nc] = 0;
				}

				// echo "After: Geld:".$statuses{$status{id}}{money} ." Energie: ".$statuses{$status{id}}{energy} ." Fp: ".$statuses{$status{id}}{sciencepoints} ." Metal: ".$statuses{$status{id}}{metal} ." Name: ".$status{syndicate}."<br>";
				// Land
				if ($sciences{ind13}) {
					$statuses[$status[id]]{land} += $sciences{ind13} * IND13WERT;
				}
				// Zuviel Milit?r / Spione ?!
				foreach ($milstats as $vl)	{
					if ($vl[race] == $status[race])	{
						$totalmilitary += $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $away_military_for_nw[$status[id]][$vl[type]];
					}
				}
				foreach ($spystats as $vl)	{
					if ($vl[race] == $status[race])	{
						$totalspies += $status[$vl[type]] + $markets[$status[id]][$vl[type]];
					}
				}
				$totalmilitary += $in_build_military[$status[id]][number];
				$totalspies += $in_build_spies[$status[id]][number];

				$totalmilitary_save[$status[id]] = $totalmilitary;

				$maxmilstore = maxunits("mil");
				$maxspystore = maxunits("spy");

				if ($totalmilitary > $maxmilstore * 1.5)	{
					$milloss = ceil($totalmilitary-$maxmilstore*1.5);
				}
				if ($totalspies > $maxspystore * 1.5)	{
					$spyloss = ceil($totalspies-$maxspystore*1.5);
				}
				if ($negative_energy)	{
					if ($totalmilitary - $milloss > $status[land] * LANDWERT): $milloss += ceil(($totalmilitary - $milloss - $status[land] * LANDWERT) * 0.015); endif;
					if ($totalspies - $spyloss > $status[land] * LANDWERT2): $spyloss += ceil(($totalspies - $spyloss - $status[land] * LANDWERT2) * 0.015); endif;
				}

				// speichern für später den Militärassistent
				$totalmil_for_militaerq[$status[id]] = $totalmilitary - $milloss;
				$totalspy_for_militaerq[$status[id]] = $totalspies - $spyloss;


				if ($milloss)	{
					if ($in_build_military[$status[id]][number])	{
						$mil_in_build = assocs("select sum(number) as number,unit_id from build_military where user_id=".$status[id]." group by unit_id","unit_id");
					}
					// F?R JEDEN MILTYP SACHEN KILLEN
					foreach ($milstats as $vl)	{
						if ($vl[race] == $status[race])	{
							$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $away_military_for_nw[$status[id]][$vl[type]] + $mil_in_build[$vl[unit_id]][number] ) / $totalmilitary * $milloss);
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;

							// ALS ERSTES MILIT?R IN BAU KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, unique_id from build_military where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss): $queries[] =("update build_military set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from build_military where unique_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							// ALS NÄCHSTES MILIT?R AWAY KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, unique_id from military_away where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss): $queries[] =("update military_away set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from military_away where unique_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							// ALS NÄCHSTES MILITÄR AUF DEM MARKT TÖTEN
							$markettypes = changetype($vl[type]);
							$nothing_left = 0;
							$number = 0; $unique_id = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
								if ($number > $specificloss): $queries[] =("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							// ZUM SCHLUSS ERST DAS WAS ZU HAUSE IST
							$statuses[$status[id]]{$vl[type]} -= $specificloss;
						}
					}
				}

				if ($spyloss)	{
					if ($in_build_spies[$status[id]][number])	{
						$spies_in_build = assocs("select sum(number) as number,unit_id from build_spies where user_id=".$status[id]." group by unit_id","unit_id");
					}
					// F?R JEDEN MILTYP SACHEN KILLEN
					foreach ($spystats as $vl)	{
						if ($vl[race] == $status[race])	{
							$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $spies_in_build[$vl[unit_id]][number] ) / $totalspies * $spyloss);
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;

							// ALS ERSTES SPIES IN BAU KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, unique_id from build_spies where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss): $queries[] =("update build_spies set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from build_spies where unique_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							//DANN ERST DIE AUFM MAKRT
							$markettypes = changetype($vl[type]);
							$nothing_left = 0;
							$number = 0; $unique_id = 0;
							$already_done = array();
							// ZUERST ALLES VOM MARKT KILLEN
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
								if ($number > $specificloss): $queries[] =("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							// ALS LETZTES SPIES DIE ZU HAUSE SIND
							$statuses[$status[id]]{$vl[type]} -= $specificloss;
						}
					}
				}
				//
				// Uic Nanofabrik updaten
				//
				if ($status{race} == "uic") {
					if ($status{multifunc} % 5 != 1 && $status{multifunc} > 0) {
						$statuses[$status[id]][multifunc]--;
					}
				}
				//
				// Pbf Armory
				//
				if ($status{race} == "pbf") {
					if ($status{armories} > 0) {
						$statuses[$status[id]][multifunc] += $status{armories}*ARMORYPROD;
						if ($statuses[$status[id]][multifunc] > $status{armories} * ARMORYSAFE): $statuses[$status[id]][multifunc] = $status{armories} * ARMORYSAFE; endif;
					}
				}
				//
				// Schools
				//
				if ($status[schools]) {
					if ($status[offspecs] || $status[defspecs]) {
						// Diverse Zeitberechnungen zur Formulierung des Db Statements
						$hourtime = get_hour_time($time);
						$faktor=0;
						$unitid = 7;
						if ($status[race] == "pbf") {
							$faktor = -1;
							$unitid = 9;
						}
						elseif ($status[race] == "sl") {
							$faktor = 1;
							$unitid = 8;
						}
						elseif ($status[race] == "neb") {
							$faktor = -1;
							$unitid = 20;
						}
						$varnumber = (SCHOOLTIME-$faktor);
						$buildtime_schools = $hourtime + $varnumber * 60 * $globals{roundtime};			#Bauzeit in Sekunden f?r mileinheiten
						// bestimmen, wieviele einheiten maximal ausgebildet werden
						$tobuildtemp = $status[schools] / $varnumber;
						//pvar($varnumber,varnumber);
						$tobuild = floor ($tobuildtemp);
						//pvar($tobuild,tobuild);
						//pvar($status[rest],statusrest);
						$rest = $tobuildtemp - $tobuild;
						//pvar($rest,restbefore);
						if ($status[rest] + $rest > 1) {
							$tsum = $rest + $status[rest];
							$tsumfloor = floor($tsum);
							$tobuild += $tsumfloor;
							$rest = $tsum - $tsumfloor;
						}
						else {
							$rest += $status[rest];
						}
						//pvar($rest,restafter);
						//pvar($tobuild,after);
						$statuses[$status[id]][rest] = $rest;
						$sumspecs = $status[offspecs]+$status[defspecs];
						$offspecsrel = $status[offspecs] / $sumspecs;
						$defspecsrel = $status[defspecs] / $sumspecs;
						$buildoffspecs = floor($tobuild * $offspecsrel);
						$builddefspecs = floor($tobuild * $defspecsrel);
						$buildrest = $tobuild - $buildoffspecs - $builddefspecs;
						if ($offspecsrel > $defspecsrel) {$buildoffspecs += $buildrest;}
						else {$builddefspecs += $buildrest;}
						$buildoffspecs > $status[offspecs] ? $buildoffspecs = $status[offspecs] : 1;
						$builddefspecs > $status[defspecs] ? $builddefspecs = $status[defspecs] : 1;
						$statuses[$status[id]][offspecs] -= $buildoffspecs;
						$statuses[$status[id]][defspecs] -= $builddefspecs;
						$finalnumber = $buildoffspecs + $builddefspecs;
						if ($finalnumber > 0) {
							$mbuildstringschools.= "($unitid,".$status[id].",$finalnumber,$buildtime_schools),";
						}
					}
				}

				//
				// Patriotsvermehrung
				//
				if ($status[race] == "neb" && $status[elites] > 0) {
					$unitid = 19;
					$hourtime = get_hour_time($time);
					$buildtime_patriots = $hourtime + PATRIOTTIME * 60 * $globals{roundtime};			#Bauzeit in Sekunden f?r mileinheiten
					// bestimmen, wieviele einheiten maximal ausgebildet werden
					$tobuildtemp = ($status[elites] / 100) * PATRIOTWACHSTUM;
					// BESCHRÄNKUNG WEGEN MAXUNITZAHL!
					$maxunitstemp = maxunits("mil");
					pvar($maxunitstemp,maxunitstemp);
					if ($tobuildtemp + $totalmilitary_save[$status[id]] > $maxunitstemp * PATRIOTS_OVERCHARGE) {
						$tobuildtemp = $maxunitstemp * PATRIOTS_OVERCHARGE - $totalmilitary_save[$status[id]];
						$tobuildtemp = $tobuildtemp < 0 ? 0 : $tobuildtemp;
					}

					$tobuild = floor ($tobuildtemp);

					if ($tobuild > 0) {
						$mbuildstringschools.= "($unitid,".$status[id].",$tobuild,$buildtime_patriots),";
					}
				}


				if ($lossstring)	{
					$lossstring = chopp($lossstring);$lossstring = chopp($lossstring);
					$messageinserts .= "(20,".$status[id].",$hourtime, '".$lossstring."'),";
				}
        	}
        }
?>