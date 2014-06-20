<?
/*
Ttile: Syndicates Update Script
Author: Jannis & Nicolas Breitwieser
Date: 18.07.03
All rights reserved
Description: Updating all players every hour, doing some other work as well
Requires: Subs.php, Syndicates Mysql Database
*/


/*
Remarks: Skript ist noch nich fertig!
         Als nächstes:
         - Sciences und transfers verrechnen in dem Teil wo der Spieler aktualisiert wird
         (Transfers die mindestens eine Stunde unterwegs sind, sollen zurückgeschickt werden, an die beteiligten
         Spieler sollen jeweils die dafür vorgesehen Nachrichten verschickt werden (sieh message settings))
         Sciences funktionieren noch nciht richtig, überleg dir was.
         - Börsenkurs un Nw müssen noch berechnet werden.
            Nw dabei am ende der Spieleraktualisierung, Börsenkurs NACH der Spieleraktualisierung
            Der Aktuelle Börsenkurs wird in Syndikate eingetragen UND in dem entsprechenden Börsenkurs safe table
            dieser ermöglicht später eine wesentlich schnellere berechnung des börsenkurses (wegen wachstum der letzten 24h)
            Eintrag in den nw_safe table nicht vergesen.
         -Überschüssiges Militär und Energie müssen vernichtet werden (vor Nw Berechnung)
         - Irgendwo nachschauen, ob alle Konzerne korrekt sind, d.h. ob zu jedem konzern der alive ist ein User existiert.
         - Heaptable leeren und Hitstats / Clicks aktualisieren (einmal am tag)
         - Verschiedene Roundstati (0,3) müssen noch explizit behandelt werden.
         - sold spalte im status einmal am tag auf 0 setzen (jeder spieler darf nur noch für 10mille täglich aktien verkaufen auf dem global market)
         - Units bei kein Strom killen wegen überschuss bei depots und so nicht vergesen
*/

//***************************************************************//
//                      Global Requirements                      //
//***************************************************************//
require ("../subs.php");
$handle = connectdb();
require("../globalvars.php");
$micro1 = getmicrotime();
$time = time();


$page = "UPDATEPROZESS";
if ($manualtime2): $time -= floor($manualtime); endif;
if ($manualtime): $time = $manualtime; endif;
$hourtime = get_hour_time($time+10); # 10 Sekunden Sicherheit
// Zufallsgenerator initialisieren
list($usec,$sec) = explode(" ",microtime());
$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;
mt_srand($bla);
$queries = array();
$querystring = "";
$dr = 0;
$date = date("j.m.y - H:i",$time);
$daydate = date("d.m.Y",$time);
$hour = date("G",$time);
$messagedeletetime = $time - 60*60*24*2.5; // Alle nachrichten älter als 2,5 tage werden gelöscht
$heaptimes = 1; # Legt fest zu welchen Stunden der Heap-Table bearbeitet wird (Modulo, alle X Stunden)
$updated_users = 0;
$users_deleted = 0;
$syndupdates = array();
$syndstring = array();
## DEFINES

define(MAXSPYOPS, 15);
define(GLO6BONUS, 5);
define(IND14WERT,800000); // ind 14 forschung gibt 800k credits bei fertigstellung.

// Zeit zu der das Skript das letzte mal lief
$lastruntime = single("select time from updates order by time desc limit 1");
// globals Array, beinhaltet generelle info zur laufenden Runde
$globals = assoc("select * from globals order by round desc limit 1");
// Pages wegen heaptablesauswertung holen
$pagestats = assocs("select * from pages","id");

$tables = singles("show tables"); # für optimize nachher

### KEINE TABLE LOCKS MEHR

/*
//
// Tablelocks
//

$lockstring = "lock tables ";
foreach ($tables as $value) {
    if ($value != "heaptable" or $hour % $heaptimes == 0) {# && $value != "sessionids_actual" && $value != "sessionids_safe") { # könnte probleme geben wegen user löschen - da muss auf diese beiden tables schreibend zugegriffen werden, geht aber glaub ich net wenn man sie nicht gelockt hat.
        $lockstring .="$value write,";
    }
}
$lockstring = chopp($lockstring);
select ($lockstring);
*/



// Updating auf 1 setzen
select("update globals set updating = 1 where round = ".$globals{round});

//
// Roundstart und Roundendtime prüfen, entsprechend Roundstatus setzen
//

if ($time >= $globals{roundstarttime} && $globals{roundstatus} != 1 && $time < $globals{roundendtime}) {
	# sonst wird bei rundenstart bereits res vergeben
    #$globals{roundstatus}  = 1;
	select("update status set lastlogintime='$hourtime' where alive > 0");
	select("delete from towncrier");
    select("update globals set roundstatus = 1 where round = ".$globals{round});
}
elseif ($time >= $globals{roundendtime} && $globals{roundstatus} != 2) {
	# sonst wird bei rundenende nix mehr fertig gebaut
    #$globals{roundstatus}  = 2;
    select("update globals set roundstatus = 2 where round = ".$globals{round});
}


//***************************************************************//
//                      Eigentliche Skriptausführung             //
//***************************************************************//
//
// Wenn Zeit zwischen letztem Update und Aufrufzeit mindestens 9/10 Der Rundenzeit beträgt, skript ausführen
//

// Zeit, die zwischen 2 updates mindestens verstreichen muss
$toleranzzeit = round($globals{roundtime}*60*0.9);
if ($time - $lastruntime >= $toleranzzeit) {

	if ($globals[roundstatus] == 0 or $globals[roundstatus] == 1)	{
		if ($hour % $heaptimes == 0)	{
			$heapdata = assocs("select user_id, clicktime, seite from heaptable where clicktime < $hourtime");
			if ($heapdata)	{
				#$ausgabe .= "HEAPDATA VORHANDEN";
				foreach ($heapdata as $vl)	{

				$tday = date("j", $vl[clicktime]);
				$tmonth = date("n", $vl[clicktime]);
				$tyear = date("Y", $vl[clicktime]);
				$thour = date("G", $vl[clicktime]);

					$heap[$tyear][$tmonth][$tday][$thour][$vl[seite]]++;
					$user_heap_click_stats[$vl[user_id]]++;
				}

				ksort($heap);
				foreach ($heap as $ky => $vl)	{	# Für jedes Jahr
					ksort($vl);
					foreach ($vl as $ky2 => $vl2)	{	# Für jeden Monat
						ksort($vl2);
						foreach ($vl2 as $ky3 => $vl3)	{	# Für jeden Tag
							ksort($vl3);
							foreach ($vl3 as $ky4 => $vl4)	{	# Für jede Stunde
								$hitstats_insertstring_1 = "";
								$hitstats_insertstring_2 = "";
								foreach ($pagestats as $vl5)	{
									if (!$vl4[$vl5[id]]): $vl4[$vl5[id]] = 0; endif;
									$hitstats_insertstring_1 .= $vl5[dateiname].",";
									$hitstats_insertstring_2 .= $vl4[$vl5[id]].",";
								}
								$hitstats_insertstring_1 .= "tag,monat,jahr,stunde";
								$hitstats_insertstring_2 .= "$ky3,$ky2,$ky,$ky4";

								select ("insert into hitstats ($hitstats_insertstring_1) values ($hitstats_insertstring_2)");
							}
						}
					}
				}
				select("delete from heaptable where clicktime < $hourtime");
			}
			else { }	#$ausgabe .= "KEIN HEAPDATA VORHANDEN :(";}
			$micro2 = getmicrotime();
		# DIE UPDATES DER CLICKS DER EINZELNEN USER WIRD ERST IN DEN SPÄTEREN BLÖCKEN GEMACHT UM STATEMENTS ZU SPAREN wenn Rundenstatus = 1 ist
		}
	}
	elseif ($globals[roundstatus] == 1 or $globals[roundstatus] == 2)	{

	}
	elseif ($globals[roundstatus] == 0 or $globals[roundstatus] == 2)	{

	}
    //
    // Roundstatus = 1 bedeutet, die Runde läuft, ansonsten gibts auch nicht wirklich viel zu tun
    //
    if ($globals{roundstatus} == 1) {

        //***************************************************************//
        //                         Daten holen                           //
        //***************************************************************//

        // Spezifikationen holen
        $buildings = assocs("select * from buildings","building_id");
        $milstats = assocs("select * from military_unit_settings","unit_id");
		$spystats = assocs("select * from spy_settings","unit_id");

        // Einzelne Daten holen
        $statuses = assocs("select * from status","id");
        $sciences_rohdaten = assocs("select * from usersciences");
		$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, sciencecosts, gamename from sciences where available=1", "name");	//der science Table
		$forschungsq_rohdaten = assocs("select konzernid, name, position from kosttools_forschungsq order by position asc");
        $built_buildings = assocs("select building_id,number,user_id,building_name from build_buildings where time <= ".$time);
        $built_military = assocs("select unit_id,user_id,number from build_military where time <= ".$time);
		$in_build_military = assocs("select sum(number) as number,user_id from build_military where time > ".$time." group by user_id", "user_id");
        $built_spies = assocs("select unit_id,user_id,number from build_spies where time <= ".$time);
		$in_build_spies = assocs("select sum(number) as number, user_id from build_spies where time > ".$time." group by user_id", "user_id");
        $built_sciences = assocs("select name,user_id from build_sciences where time <= ".$time);
		$in_build_sciences = assocs("select name,user_id from build_sciences where time > ".$time, "user_id");
        $back_military = assocs("select unit_id,user_id,number from military_away where time <= ".$time);
	    $away_military_for_nw_rohdaten = assocs("select unit_id,user_id,number from military_away where time > ".$time);
        $back_transfer = assocs("select user_id,receiver_id,product,number from transfer where finished=0 and time <= ".($time-60*60));
		$market_stuff_rohdaten = assocs("select owner_id,sum(number) as number,type,prod_id from market group by owner_id,type,prod_id");
        $syndikate_data_safe_rohdaten = assocs("select synd_id, nw, time from syndikate_data_safe where time >= (".($hourtime-24*3600).") order by time desc");
		$syndikate_data = assocs("select synd_id, aktienkurs from syndikate", "synd_id");
		$allianzen_kuendigungen = assocs("select synd_id from allianzen_kuendigungen where time <= ".$time, "synd_id");
		$naps_kuendigungen = singles("select napid from naps_spieler_spezifikation where gekuendigt_time > 0 and gekuendigt_time <= ".$time." and gekuendigt_done=0");
		$micro3 = getmicrotime();

		// Sciences für besseren Aufruf nach User_id verarbeiten

		foreach ($sciences_rohdaten as $vl)	{
			$scienceses[$vl[user_id]][$vl[name]] = $vl[level];
		}

		// Awaymilitary für Networthberechnung vorbereiten

		foreach ($away_military_for_nw_rohdaten as $vl)	{
			$away_military_for_nw[$vl[user_id]][$milstats[$vl[unit_id]][type]] += $vl[number];
		}

		// Marketzeug für Networthberechnung vorbereiten

		foreach ($market_stuff_rohdaten as $vl)	{
			$prod = changetype($vl[type],$vl[prod_id]);
			$markets[$vl[owner_id]][$prod[product]] = $vl[number];
		}

		// Syndikats-Safe-Daten vorbereiten für spätere Berechnung der Aktienkurse

		foreach ($syndikate_data_safe_rohdaten as $vl)	{
			$syndikate_data_safe[$vl[synd_id]][($hourtime-$vl[time])/3600] = $vl[nw];
			#echo "nw: ".$vl[nw]." - synd_id: ".$vl[synd_id]." - time: ".(($hourtime-$vl[time])/3600)."<br>";
		}

		// Forschungsqueue-Daten verarbeiten

		foreach ($forschungsq_rohdaten as $vl)	{
			$forschungsq[$vl[konzernid]][$vl[position]] = $vl[name];
		}


        //************************************************************************//
        //            Schleife für einzelne Spielerupdate starten                 //
        //    Aufwand: n*m, n= Anzahl der Spieler, m= Aktionen für jeden Spieler  //
        //************************************************************************//


		$micro6 = getmicrotime();
        //***********************************************
        //        Zuerst Ressourcen updaten
        //***********************************************
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
                $status{alive} == 1 ? $mod = 1 : $mod = 0.5;
			//  echo "<br><br>Before: Geld:".$status{money}." Energie: ".$status{energy}." Fp: ".$status{sciencepoints}." Metal: ".$status{metal}." Name: ".$status{syndicate}."<br>";
				// Energie
				list ($energyadd, $energyloss) = energyadd($status{id}, 4); # 4 für energyloss damit message erstellt werden kann.
				if ($energyloss): $statuses[$status[id]]{energy} = $status[energy]; $messageinserts .= "(2,".$status[id].",$hourtime, '".pointit($energyloss)."'),"; endif;	# Message mit Energyloss vorbereiten
                $statuses[$status[id]]{energy}  += $energyadd;
                if ($statuses[$status[id]]{energy} < 0) {$statuses[$status[id]]{energy} = 0; $mod *= 0.5; $negative_energy = 1;} # Noch mehr Miese für andere Ressourcen, Energy mind. 0 setzen
				// Money
                $statuses[$status[id]]{money} += round($mod * moneyadd($status{id}));
				// Metal
                $statuses[$status[id]]{metal}  += round($mod * metaladd($status{id}));
				// Sciencepoints
                $statuses[$status[id]]{sciencepoints}  += round($mod * sciencepointsadd($status{id}));
				// echo "After: Geld:".$statuses{$status{id}}{money} ." Energie: ".$statuses{$status{id}}{energy} ." Fp: ".$statuses{$status{id}}{sciencepoints} ." Metal: ".$statuses{$status{id}}{metal} ." Name: ".$status{syndicate}."<br>";
				// Land
				if ($sciences{ind13}) {
					$statuses[$status[id]]{land} += $sciences{ind13} * IND13WERT;
				}
				// Zuviel Militär / Spione ?!
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

				$maxmilstore = $status[land] * LANDWERT + $status[depots] * (DEPOTWERT + $sciences{mil8} * MIL8BONUS);
				$maxspystore = $status[land] * LANDWERT2 + $status[spylabs] * (SPYLABSWERT + $sciences{glo4} * GLO4BONUS);

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

				if ($milloss)	{
					if ($in_build_military[$status[id]][number])	{
						$mil_in_build = assocs("select sum(number) as number,unit_id from build_military where user_id=".$status[id]." group by unit_id","unit_id");
					}
					// FÜR JEDEN MILTYP SACHEN KILLEN
					foreach ($milstats as $vl)	{
						if ($vl[race] == $status[race])	{
							$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $away_military_for_nw[$status[id]][$vl[type]] + $mil_in_build[$vl[unit_id]][number] ) / $totalmilitary * $milloss);
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;
							if ($status[$vl[type]] >= $specificloss): $statuses[$status[id]]{$vl[type]} -= $specificloss;
							else: 	$specificloss -= $status[$vl[type]];
									$statuses[$status[id]]{$vl[type]} = 0;
									$markettypes = changetype($vl[type]);
									$nothing_left = 0;
									$number = 0; $unique_id = 0;
									// ZUERST ALLES VOM MARKT KILLEN
									while ($specificloss > 0 and !$nothing_left)	{
										list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' order by inserttime desc limit 1");
										if ($number > $specificloss): select("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
										elseif ($number): $specificloss -= $number; select("delete from market where offer_id=".$unique_id);
										else: $nothing_left = 1;
										endif;
									}
									// ALS NÄCHSTES MILITÄR AWAY KILLEN
									$nothing_left = 0;
									while ($specificloss > 0 and !$nothing_left)	{
										list($number,$unique_id) = row("select number, unique_id from military_away where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." order by time desc limit 1");
										if ($number > $specificloss): select("update military_away set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
										elseif ($number): $specificloss -= $number; select("delete from military_away where unique_id=".$unique_id);
										else: $nothing_left = 1;
										endif;
									}
									// ALS NÄCHSTES MILITÄR IN BAU KILLEN
									$nothing_left = 0;
									while ($specificloss > 0 and !$nothing_left)	{
										list($number,$unique_id) = row("select number, unique_id from build_military where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." order by time desc limit 1");
										if ($number > $specificloss): select("update build_military set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
										elseif ($number): $specificloss -= $number; select("delete from build_military where unique_id=".$unique_id);
										else: $nothing_left = 1;
										endif;
									}
							endif;
						}
					}
				}
				if ($spyloss)	{
					if ($in_build_spies[$status[id]][number])	{
						$spies_in_build = assocs("select sum(number) as number,unit_id from build_spies where user_id=".$status[id]." group by unit_id","unit_id");
					}
					// FÜR JEDEN MILTYP SACHEN KILLEN
					foreach ($spystats as $vl)	{
						if ($vl[race] == $status[race])	{
							$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $spies_in_build[$vl[unit_id]][number] ) / $totalspies * $spyloss);
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;
							if ($status[$vl[type]] >= $specificloss): $statuses[$status[id]]{$vl[type]} -= $specificloss;
							else: 	$specificloss -= $status[$vl[type]];
									$statuses[$status[id]]{$vl[type]} = 0;
									$markettypes = changetype($vl[type]);
									$nothing_left = 0;
									$number = 0; $unique_id = 0;
									// ZUERST ALLES VOM MARKT KILLEN
									while ($specificloss > 0 and !$nothing_left)	{
										list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' order by inserttime desc limit 1");
										if ($number > $specificloss): select("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
										elseif ($number): $specificloss -= $number; select("delete from market where offer_id=".$unique_id);
										else: $nothing_left = 1;
										endif;
									}
									// ALS NÄCHSTES SPIES IN BAU KILLEN
									$nothing_left = 0;
									while ($specificloss > 0 and !$nothing_left)	{
										list($number,$unique_id) = row("select number, unique_id from build_spies where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." order by time desc limit 1");
										if ($number > $specificloss): select("update build_spies set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
										elseif ($number): $specificloss -= $number; select("delete from build_spies where unique_id=".$unique_id);
										else: $nothing_left = 1;
										endif;
									}
							endif;
						}
					}
				}


				if ($lossstring)	{
					$lossstring = chopp($lossstring);$lossstring = chopp($lossstring);
					$messageinserts .= "(20,".$status[id].",$hourtime, '".$lossstring."'),";
				}
        	}
        }

        //***********************************************
        //        Sachen in Bau verrechnen
        //***********************************************

        // Gebäude
		#$ausgabe .= "<br><br>Gebäude/Land";
        foreach ($built_buildings as $value) {
            // 127 = Land
            if ($value{building_id} != 127) {
				#$ausgabe .= "<br>Geb (BID:".$value{building_id}.")(BNAME:".$value{building_name}."): Konzernid: ".$value{user_id}."; ".$buildings{$value{building_id}}{name_intern}." vorher/nachher ".$statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}}."/";
                $statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}} += $value{number};
				#$ausgabe .= $statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}};
            }
            else {
				#$ausgabe .= "<br>Land: Konzernid: ".$value{user_id}."; Land vorher/nachher ".$statuses{$value{user_id}}{land}."/";
                $statuses{$value{user_id}}{land} += $value{number};
				#$ausgabe .= $statuses{$value{user_id}}{land};
            }
        }
        // Militäreinheiten
		#$ausgabe .= "<br><br>Militäreinheiten";
        foreach ($built_military as $value) {
			#$ausgabe .= "<br>Mil (ID:".$value[unit_id]."): ".$milstats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL (vorher/nachher) ".$value[number]." (".$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}}."/";
            $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} += $value{number};
			#$ausgabe .= $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} . ")";
        }
        // Spies
		#$ausgabe .= "<br><br>Spione";
        foreach ($built_spies as $value) {
			#$ausgabe .= "<br>Spy (ID:".$value[unit_id]."): ".$spystats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL(".$value[number].") vorher(".$statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}}.")/nachher(";
            $statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}} += $value{number};
			#$ausgabe .= $statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}} . ")";
        }
        // Sciences
		#$ausgabe .= "<br><br>Sciences";
        foreach ($built_sciences as $value) {
            if ($scienceses{$value{user_id}}{$value{name}}) {
                $scienceses{$value{user_id}}{$value{name}}++;
				select("update usersciences set level=".$scienceses{$value{user_id}}{$value{name}}." where user_id=".$value{user_id}." and name='".$value{name}."'");
				if ($value{name} == "ind14") { // ind14 forschung verrechnen
					$statuses{$value{user_id}}{money} += IND14WERT*$scienceses{$value{user_id}}{$value{name}};
					$twerte = $sciencestats{ind14}{gamename}."|".(pointit(IND14WERT*$scienceses{$value{user_id}}{$value{name}}));
					$messageinserts.="(40,".$value{user_id}.",$time,'$twerte'),";// Messag schreiben
				}
            }
            else {
				$scienceses{$value{user_id}}{$value{name}} = 1;
				// Status hier gar nicht definiert (Status war nur oben temporäres Schleifenelement), daher bug mit den Forschungen
				if ($value{name} == "glo11") { // glo11 forschung verrechnen, diese wird nicht eingetragen
					$trand = mt_rand(0,5);
					// Wirkung: 0 - 300k credits, 1 - 15k fp, 2 - 250k energie, 3- 50k erz, 4 - 250 Ranger, 5 - 200 marines
					$twerte = $sciencestats{glo11}{gamename}."|";
					switch($trand) {
							case 0:
									$gamblevalue = 300000;
									$gamble = "money";
									$twerte.=(pointit($gamblevalue))." Credits";
								break;
							case 1:
									$gamblevalue = 15000;
									$gamble ="sciencepoints";
									$twerte.=(pointit($gamblevalue))." Forschungspunkte";
								break;
							case 2:
									$gamblevalue = 250000;
									$gamble="energy";
									$twerte.=(pointit($gamblevalue))." Energie";
								break;
							case 3:
									$gamblevalue = 50000;
									$gamble = "metal";
									$twerte.=(pointit($gamblevalue))." Erz";
								break;
							case 4:
									$gamblevalue = 250;
									$gamble ="defspecs";
									$twerte.=(pointit($gamblevalue))." Ranger";
								break;
							case 5:
									$gamblevalue = 200;
									$gamble ="offspecs";
									$twerte.=(pointit($gamblevalue))." Marines";
								break;
					}
					$statuses{$value{user_id}}{$gamble} += $gamblevalue;
					$messageinserts.="(41,".$value{user_id}.",$time,'$twerte'),";// Messag schreiben
					unset($gamblevalue);
				}
				else {
					$sciencesinserts .= "(".$value{user_id}.",'".$value[name]."',1),";
					if ($value{name} == "ind14") { // ind14 forschung verrechnen
						$statuses{$value{user_id}}{money} += IND14WERT;
						$twerte = $sciencestats{ind14}{gamename}."|".pointit(IND14WERT);
						$messageinserts.="(40,".$value{user_id}.",$time,'$twerte'),";// Messag schreiben
					}
				}
            }
			if($value{name} == "ind16") {
				$syndupdates{$statuses{$value{user_id}}{rid}}{energyforschung}++;
			}
			elseif($value{name} == "glo12") {
				$syndupdates{$statuses{$value{user_id}}{rid}}{sabotageforschung}++;
			}
			elseif($value{name} == "ind15") {
				$syndupdates{$statuses{$value{user_id}}{rid}}{creditforschung}++;
			}
			#$ausgabe .= "<br>KID(".$value[user_id].") - Science(".$value[name].") - Neues Level (".$scienceses{$value{user_id}}{$value{name}}.")";
        }
		if (count($syndupdates) > 0) {
			foreach ($syndupdates as $key => $value) {
				$go = 0;
				$setstring = "";
				foreach ($value as $was => $wert) {
					if (!$go) {$go = 1;}
					$setstring.="$was=$was+$wert,";
				}
				$setstring = chopp($setstring);
				if ($go) {
					$syndstring[$key] = ",".$setstring;
				}
				unset($go,$setstring);
			}
		}
		unset($syndupdates);

        // Back Military
				#$ausgabe .= "<br><br>Back Military";
        foreach ($back_military as $value) {
			#$ausgabe .= "<br>Mil (ID:".$value[unit_id]."): ".$milstats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL (vorher/nachher) ".$value[number]." (".$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}}."/";
            $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} += $value{number};
			#$ausgabe .= $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} . ")";
        }
		// Back Transfer
		$resstats = getresstats();
		foreach ($back_transfer as $vl)	{
			$messageinserts .= "(10, ".$vl[user_id].",$hourtime, '".$statuses[$vl[receiver_id]][syndicate]."|".$statuses[$vl[receiver_id]][rid]."|".pointit($vl[number])."|".$resstats[$vl[product]][name]."'),";
			$messageinserts .= "(9, ".$vl[receiver_id].",$hourtime,'".$statuses[$vl[user_id]][syndicate]."|".$statuses[$vl[user_id]][rid]."|".pointit($vl[number])."|".$resstats[$vl[product]][name]."'),";
			$statuses[$vl[user_id]][$vl[product]] += $vl[number];
		}
		// Allianzen Kündigungen
		foreach ($allianzen_kuendigungen as $ky => $vl)	{
			$allies = row("select syndikate.allianz_id,first,second,third from syndikate,allianzen where synd_id=$ky and syndikate.allianz_id=allianzen.allianz_id");
			if ($allies[0])	{
				if ($allies[1] == $ky): $allies[1] = $allies[3];
				elseif ($allies[2] == $ky): $allies[2] = $allies[3];
				endif;
				$syndikate_namen = assocs("select synd_id,name from syndikate where synd_id in ($ky,".$allies[1].($allies[2] ? ",".$allies[2]:"").")","synd_id");
				select("update syndikate set allianz_id=0, ally1=0, ally2=0 where synd_id=$ky");
				if ($allies[2])	{
					select("update allianzen set first=".$allies[1].",second=".$allies[2].",third=0 where allianz_id=".$allies[0]);
					select("update syndikate set ally1=".$allies[1].",ally2=0 where synd_id=".$allies[2]);
					select("update syndikate set ally1=".$allies[2].",ally2=0 where synd_id=".$allies[1]);
					$towncrierinserts .= "($hourtime,".$allies[1].",'Ihr ehemaliger Bündnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat ist nun nur noch mit dem Syndikat <strong>".$syndikate_namen[$allies[2]][name]." (#".$allies[2].")</strong> alliiert.'),";
					$towncrierinserts .= "($hourtime,".$allies[2].",'Ihr ehemaliger Bündnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat ist nun nur noch mit dem Syndikat <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong> alliiert.'),";
					$towncrierinserts .= "($hourtime,".$ky.",'Ihr Syndikat beendet die Allianz mit den Syndikaten <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong> und <strong>".$syndikate_namen[$allies[2]][name]." (#".$allies[2].")</strong>.'),";
				}
				elseif (!$allies[2])	{
					select("update allianzen set first=0,second=0,third=0 where allianz_id=".$allies[0]);
					select("update syndikate set allianz_id=0,ally1=0,ally2=0 where synd_id=".$allies[1]);
					$towncrierinserts .= "($hourtime,".$allies[1].",'Ihr ehemaliger Bündnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat hat keine weiteren Allianzpartner.'),";
					$towncrierinserts .= "($hourtime,".$ky.",'Ihr Syndikat beendet die Allianz mit dem Syndikat <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong>.'),";
				}
			}
		}
		// NAPS-Kündigungen
		if ($naps_kuendigungen)	{
			$napidstring = join(",", $naps_kuendigungen);
			select("update naps_spieler_spezifikation set gekuendigt_done=1 where napid in ($napidstring)");
			select("delete from naps_spieler where napid in ($napidstring)");
		}
		$micro7 = getmicrotime();

        //***************************************************************//
        //                         Daten schreiben                       //
        //***************************************************************//

        select("delete from build_buildings where time <= ".$time);
        select("delete from build_military where time <= ".$time);
        select("delete from build_spies where time <= ".$time);
        select("delete from build_sciences where time <= ".$time);
        select("delete from military_away where time <= ".$time);
		select("delete from allianzen_kuendigungen where time <= ".$time);
		select("update transfer set finished=1 where finished=0 and time <= ".($time-60*60));
		## Scheint so konzipiert zu sein dass transfers ein Safetable ist
        #select("delete from transfer where time <= ".($time-60*60));
		## Wollen wir wirklich Messages löschen ?
        #select("delete from messages where time <= ".$messagedeletetime);
        select("delete from message_values where time <= ".$messagedeletetime);
		$micro75 = getmicrotime();

        // Statustable und Sciencestable neu schreiben
        /*
        $statusprefs = rows("describe status");
        $action = "create table if not exists status2 (";
        $key = "";
        foreach ($statusprefs as $value) {
            if ($value[2] != "YES") {$null = "not null";}
            if ($value[3] == "PRI") {$pri = "primary key";}
            if ($value[3] == "MUL") {$key .= "key";}
            if ($value[4]) {$default = "default '".$value[4]."'";}
            $action.=$value[0]." ".$value[1]." ".$null." ".$pri." ".$default." ".$value[5].",";
            unset($null,$pri,$default);
        }
        $action = chopp($action);$action.=")";
        echo "ACTION: ".$action."<br><br>";
        select("$action");
        */

        foreach($statuses as $status) {
			if ($status[alive] > 0)	{
				if ($status[lastlogintime] + TIME_TILL_KILLED < $time and $status[alive] == 1)	{
					$killtime1 = getmicrotime();
					kill_den_konzern($status[id]);
					$users_deleted++;
					$killtime2 = getmicrotime();
				}
				else	{
					// Daten für Networthberechnung bereitstellen
					$sciences = $scienceses{$status[id]};
					$away = $away_military_for_nw{$status[id]};
					$market = $markets[$status[id]];
					if (count($market) == 0): $market = 1; endif;	# Verhindert dass in der NW-Routine die Marktdaten nochmals unnötig geholt werden!!
					$queuetime1 = getmicrotime();
					if (!$in_build_sciences[$status[id]])	{
						if ($forschungsq[$status[id]])	{
							foreach ($forschungsq[$status[id]] as $ky => $vl)	{
								$valid = forschable($vl,$sciencestats,$sciences,$status[sciencepoints]);
								if ($valid[0])	{
									$modifikator = $status[race] == "sl" ? 0.75 : 1;
									// Glo 15 Forschung beschleunig forschungsgeschwindigkeit um weitere 25%
									$modifikator = $sciences{glo15} ? $modifikator - 0.25 : 1;
									$build_sciencesinserts .= "($status[id],".($hourtime+ 3600 * 12 * $sciencestats[$vl][level] * $modifikator).",'".$vl."'),";
									$status[sciencepoints] -= $valid[1];
									$messageinserts .= "(29, $status[id], $hourtime, '".$sciencestats[$vl][gamename]."'),";
									select("delete from kosttools_forschungsq where konzernid=".$status[id]." and position=$ky");
									if (($ky==1 && $forschungsq[$status[id]][2]) or ($ky==2 && $forschungsq[$status[id]][3])): select("update kosttools_forschungsq set position=position-1 where konzernid=".$status[id]." and position > $ky"); endif;
									break;
								}
							}
						}
					}
					$queuetime1 = getmicrotime();

		$micro775 = getmicrotime();

					// Networthberechnen - Eintragung vorbereiten
					$status[nw] = nw($status[id]);
					$nw_safe_inserts .= "(".$status[id].",".$status[rid].",".$status[nw].",".$status[land].",$hourtime),";

					// Syndikats-Safe-Daten erstellen
					$syndikate_data[$status[rid]][nw] += $status[nw];
					$syndikate_data[$status[rid]][land] += $status[land];

					// Spyactions hochsetzen

					$status[spyactions] += 1 + $sciences[glo10];
					if ($status[spyactions] > MAXSPYOPS + $sciences[glo6] * GLO6BONUS): $status[spyactions] = MAXSPYOPS + $sciences[glo6] * GLO6BONUS; endif;

					// Clicks zuzählen:

					if ($user_heap_click_stats[$status[id]])	{
						$status[clicks] += $user_heap_click_stats[$status[id]];
					}

					// Börsenverkäufe auf 0 setzen wenn 0 Uhr ist

					if ($hour == 0)	{
						$status[sold] = 0;
					}

					// Anzahl Turns hochzählen

					$status[turn]++;

$micro8 = getmicrotime();

			        $updatestring = "update status set ";
			       	foreach ($status as $key => $value) {
						if ($value or $value == 0) {
							$updatestring.= $key."='".$value."',";
						}
					}
			        $updatestring = chopp($updatestring);
			        $updatestring.=" where id = ".$status{id};
			        select($updatestring);
			        $updated_users++;
				}
			}
        }
$micro9 = getmicrotime();


		// Syndikats-Networth-Land-Safes-Eintragungen vorbereiten
		// Für jedes Syndikat den Aktienkurs berechnen
		foreach ($syndikate_data as $ky => $vl)	{
			if (!$syndikate_data[$ky][nw]): $syndikate_data[$ky][nw] = 0; $vl[nw] = 0; endif;
			if (!$syndikate_data[$ky][land]): $syndikate_data[$ky][land] = 0; $vl[land] = 0; endif;
			$syndikate_data_safe_inserts .= "(".$ky.",".$vl[nw].",".$vl[land].",$hourtime),";
			$synd_simple_nw[] = $vl[nw];
		}

		sort($synd_simple_nw);
		//$ins = 1;
		//foreach ($synd_simple_nw as $vl) { echo "$ins - $vl<br>"; $ins++; }

		$anz_syndikate = count($syndikate_data);


		if ($anz_syndikate % 2 == 0): $median_synd_nw = 0.5 * ( $synd_simple_nw[$anz_syndikate/2-1] + $synd_simple_nw[$anz_syndikate/2]);
		else: $median_synd_nw = $synd_simple_nw[floor($anz_syndikate/2)]; endif;
		#$ausgabe .= "<br><br>Synd_NW-MEDIAN: $median_synd_nw<br>";
		$bonus = floor(($hourtime - $globals[roundstarttime]) / 3600 * 1.5);
		#$ausgabe .= "<br>Bonus auf Standardaktienkurs: $bonus<br>";


		foreach ($syndikate_data as $ky => $vl)	{
			#$ausgabe .= "<br><br>Networth des Syndikats: ".$vl[nw]."<br>";
			$prozent_plus = 1;
			$avl_elements = count($syndikate_data_safe[$ky]);
			for ($i=1; $i <= $avl_elements; $i++)	{
				if ($i == 1): $prozent_plus += ($vl[nw] / ($syndikate_data_safe[$ky][$i] ? $syndikate_data_safe[$ky][$i] : 1)) - 1;
				else: $prozent_plus += (($avl_elements + 2 - $i) / ($avl_elements + 1)) * (( ($syndikate_data_safe[$ky][$i-1] ? $syndikate_data_safe[$ky][$i-1] : 1) / ($syndikate_data_safe[$ky][$i] ? $syndikate_data_safe[$ky][$i] : 1)) - 1);
				endif;
				#$ausgabe .= "Syndikat $ky: elemente: $avl_elements - zähler: $i - wert: ".($syndikate_data_safe[$ky][$i] ? $syndikate_data_safe[$ky][$i] : "(1) - 0")." - prozentplus: $prozent_plus<br>";
			}
			if ($prozent_plus < 0.5): $prozent_plus = 0.5; endif;
			if ($prozent_plus > 1.5): $prozent_plus = 1.5; endif;
			$aktienkurs = (1000+$bonus) * ($vl[nw] / ($median_synd_nw ? $median_synd_nw : 1)) * $prozent_plus;
			#$ausgabe .= "aktienkurs vor dieser stunde: ".$vl[aktienkurs]."<br>";
			#$ausgabe .= "neuer preaktienkurs: $aktienkurs<br>";
			if ($aktienkurs / $vl[aktienkurs] < 0.95): $aktienkurs = $vl[aktienkurs] * (0.935 + ( mt_rand(0,6) / 200)); endif;
			if ($vl[aktienkurs] / $aktienkurs < 0.95): $aktienkurs = $vl[aktienkurs] * (1.035 + ( mt_rand(0,6) / 200)); endif;
			$aktienkurs = round($aktienkurs);
			#$ausgabe .= "afteraktienkurs: $aktienkurs<br><br><br>";
			$aktien_safekurse_inserts .= "('$ky', '$aktienkurs', '$hourtime'),";
			select("update syndikate set aktienkurs='$aktienkurs'".$syndstring[$ky]." where synd_id='$ky'");
		}

		// Wird oben bereits teilweise erledigt mit Updates
		/*
        $sciencestring = "insert into usersciences (user_id,name,level) values ";
        foreach ($scienceses as $value) {
            $sciencestring.= "(".$value{user_id}.",".$value{name}.",".$value{level}."),";
        }
        $sciencestring = chopp($sciencestring);
        echo $sciencestring;
		*/

		// Wenn neue Sciences eingetragen werden müssen:

		if ($sciencesinserts)	{
			$sciencesinserts = chopp($sciencesinserts);
			select("insert into usersciences (user_id, name, level) values $sciencesinserts");
		}
		if ($build_sciencesinserts)	{
			$build_sciencesinserts = chopp($build_sciencesinserts);
			select("insert into build_sciences (user_id,time,name) values $build_sciencesinserts");
		}
		if ($messageinserts)	{
			$messageinserts = chopp($messageinserts);
			select("insert into message_values (id, user_id, time, werte) values $messageinserts");
			unset($messageinserts);
		}
		/*
		if (count($syndupdates) > 0) {
			foreach ($syndupdates as $key => $value) {
				$go = 0;
				$setstring = "";
				foreach ($value as $was => $wert) {
					if (!$go) {$go = 1;}
					$setstring.="$was=$was+$wert,";
				}
				$setstring = chopp($setstring);
				if ($go) {
					select("update syndikate set $setstring where synd_id=$key");
				}
				unset($go,$setstring);
			}
		}
		unset($syndupdates);
		*/
		if ($nw_safe_inserts)	{
			$nw_safe_inserts = chopp($nw_safe_inserts);
			select("insert into nw_safe (user_id, rid, nw, land, time) values $nw_safe_inserts");
		}
		if ($syndikate_data_safe_inserts)	{
			$syndikate_data_safe_inserts = chopp($syndikate_data_safe_inserts);
			select("insert into syndikate_data_safe (synd_id, nw, land, time) values $syndikate_data_safe_inserts");
		}
		if ($towncrierinserts)	{
			$towncrierinserts = chopp($towncrierinserts);
			select("insert into towncrier (time,rid,message) values $towncrierinserts");
		}
		if ($aktien_safekurse_inserts)	{
			$aktien_safekurse_inserts = chopp($aktien_safekurse_inserts);
			select("insert into aktien_safekurse (synd_id, aktienkurs, time) values $aktien_safekurse_inserts");
		}



    }

    //
    // Falls Roundstatus = 0 befindet sich das Spiel in der Vorbereitungsphase, es werden nur potentiell falsch eingetragene Zeiten korrigeirt
    //

    elseif ($globals{roundstatus} == 0) {

		foreach ($user_heap_click_stats as $ky => $vl)	{
			select("update status set clicks=clicks+$vl where id=$ky");
		}

    }

    //
    // Pausemodus, nur Zeiten hochsetzen
    //

    elseif ($globals{roundstatus} == 3) {

    }


    // Roundstatusunabhängige Dinge erledigen

    //
    // In updates table schreiben
    //

    $endtime = time();
	#$ausgabe = "Ausführzeit: ".($endtime-$time)."<br>" . $ausgabe;
    select("insert into updates (time,endtime,users_updated,users_deleted,roundstatus,database_requests) values ($time,$endtime,$updated_users,$users_deleted,".$globals{roundstatus}.",".($dr+3).")");
}
else { $ausgabe .= "Seit dem letzten Update ist noch nicht genügend Zeit verstrichen - keine Änderungen am Spielgeschen vorgenommen!";}

//***************************************************************//
//               Eigentliche Skriptausführung Ende               //
//***************************************************************//

// Updating wieder auf 0 setzen
select("update globals set updating = 0 where round = ".$globals{round});
$micro10 = getmicrotime();

//
// Tables Optimieren
//
$optimizestring = "optimize table ";
foreach ($tables as $value) {
    if ($value != "heaptable" && $value != "sessionids_actual") {
        $optimizestring .="`".$value."`,";
    }
}
$optimizestring = chopp($optimizestring);
# serverausfälle könnten hierdurch entstehen:
#select($optimizestring);
//
// Tables unlocken
//


### KEINE TABLE LOCKS MEHR
#select("unlock tables");




//
// Testweise alle Queries ausgeben
//

#echo $querystring."<br><br>";


$micro105 = getmicrotime();
#echo "AUSGABE:<br>$ausgabe<br>";
echo "Drs: $dr<br>";
echo "Date: $date<br>";
echo "DayDate: $daydate<br>";
echo "Hour: $hour <br>";
#echo "QUERYSTRING: $querystring<br>";

//
// Mailzusammenfassung schicken
//


$micro11 = getmicrotime();
$ausgabe = preg_replace("/<br>/", "\n", $ausgabe);

$betreff = "Stündliches Update Runde ".$globals['round'] ." - ".$date;
$message = $ausgabe;

$micro12 = getmicrotime();
$timestring.= $micro1."<br>".$micro10."<br> Dif: ".($micro12-$micro1)."<br>";

$timestring.= "Pregausdruck:".($micro12-$micro11)."<br>";
$timestring.= "Ausgabe:".($micro11-$micro105)."<br>";
$timestring.= "Tableopts:".($micro105-$micro10)."<br>";
$timestring.= "Aktienkurse, Inserts:".($micro10-$micro9)."<br>";
$timestring.= "Updatetime:".($micro9-$micro8)."<br>";
$timestring.= "Andere Berechnungen:".($micro8-$micro775)."<br>";
$timestring.= "Sachen mit status:".($micro775-$micro75)."<br>";
$timestring.= "Deletes:".($micro75-$micro7)."<br>";
$timestring.= "Berechnung:".($micro7-$micro6)."<br>";
$timestring.= "Daten vorbereiten:".($micro6-$micro3)."<br>";
$timestring.= "Daten holen:".($micro3-$micro2)."<br>";
$timestring.= "Heapdata:".($micro2-$micro1)."<br>";
$timestring.= "Killtime:".($killtime2-$killtime1)."<br>";
$timestring.= "Queuetime:".($queuetime2-$queuetime1)."<br>";

$timestring = preg_replace("/<br>/", "\n", $ausgabe);


?>
