<?
//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//
$decision == "build" || $decision == "raze" || $decision == "spend" || $decision == "queue" ? 1 : $decision = "";
$build_mil = array();
$build_spies = array();

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");
require_once (LIB."js.php");
js::loadOver();



//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

//Forschung, die gerade erforscht wird.
$developing_science = single("select name from build_sciences where user_id = ".$status[id]." and `time` > ".$time);

// Header action
if ($headeraction) {
	emoheader("action=$headeraction");
}

// Unitstats und Spystats aus Db holen
$unitstats = getunitstats($status{race});
$spystats = getspystats($status{race});
$update = 0;
$inprot = $status[createtime] + PROTECTIONTIME >= $time ? 1:0; // Konzern unter schutz ?
$costmodmil = 0; // Wert mit dem Miltäreinheitenpreis multipliziert wird, wegen diverser boni
$costmodspies = 0; // Wert mit dem Miltäreinheitenpreis multipliziert wird, wegen diverser boni

// Gesamtanzahl militäreinheiten und spione als array
$miltotal = miltotal($status{id}, 5, 1);
$spiestotal = spiestotal($status{id}, 5, 1);

// Maximale Anzahl möglicher Einheiten bestimmen
$maxunits = maxunits(mil);
$maxspies = maxunits(spy);
$queries = array(); // hier kommen alle schreibmysqlqueries rein, wird aber erst am ende verrechnet wegen transfersicherheit und so.

$total_verschiedene_einheiten = 0;
// Hier teilweise Übergabewerte überprüfen:
foreach ($unitstats as $key => $value) {
    $$key = check_int($$key);
    $$key < 0 ? $$key = 0:1;
    $build_mil{$key} = check_int($$key);
	if ($$key > 0) $total_verschiedene_einheiten++;
}
foreach ($spystats as $key => $value) {
    $$key = check_int($$key);
    $$key < 0 ? $$key = 0:1;
    $build_spies{$key} = ($$key);
	if ($$key > 0) $total_verschiedene_einheiten++;
}

////////////////// ASSISTENT
$name = "Militärassistent";
$name_dativ = "Militärassistenten";
$militaerq = $features[MILITAERQ];

$anzahl_assistenten_plaetze = 10;
if ($militaerq) {
	$assistenten_auftraege = assocs("select * from kosttools_militaerq where user_id=$id");
	$assistenten_auftraege_number = count($assistenten_auftraege);
	$assistenten_auftraege_frei = $anzahl_assistenten_plaetze - $assistenten_auftraege_number;
}


function milsort ($a, $b) {
    if ($a["position"] == $b["position"]) return 0;
    return ($a["position"] < $b["position"]) ? -1 : 1;
}

if ($doings == "unqueue" && $militaerq)	{
	if ($assistenten_auftraege_number)	{
		$queries[] = "delete from kosttools_militaerq where user_id=$id and position=$pos";
		for ($i = 0; $i < $assistenten_auftraege_number; $i++)	{
			if ($assistenten_auftraege[$i][position] == $pos)	{
				unset($assistenten_auftraege[$i]);
				break;
			}
		}
		if ($assistenten_auftraege_number > 1 and $pos != $assistenten_auftraege_number)	{
			$queries[] = "update kosttools_militaerq set position=position-1 where user_id=$id and position > $pos";
			foreach ($assistenten_auftraege as $ky => $vl)	{
				if ($vl[position] > $pos): $assistenten_auftraege[$ky][position] -= 1; endif;
			}
		}
	}
	else { $tpl->assign('ERROR', "Sie haben keine Aufträge in der Warteschlange stehen. Welche Einträge möchten Sie da bitteschön entfernen?");}
}


if ($doings == "unqueueall" && $militaerq)	{
	if ($assistenten_auftraege_number)	{
		$queries[] = "delete from kosttools_militaerq where user_id=$id";
		$tpl->assign('MSG', "Sie haben die Einträge in Ihrem $name_dativ erfolgreich gelöscht.");
		$assistenten_auftraege = array();
	}
	else { $tpl->assign('ERROR', "Sie haben keine Aufträge in der Warteschlange stehen. Welche Einträge möchten Sie da bitteschön entfernen?");}
}


if ($doings == "modifyqueue" && $militaerq)	{
	$pos = floor($pos);
	if ($assistenten_auftraege_number)	{
		if ($assistenten_auftraege_number > 1)	{
			if ($up or $down)	{
				if ($pos >= 1 && $pos < $assistenten_auftraege_number && $up)	{
					$validq = 1;
				}
				elseif (($pos >= 2  && $assistenten_auftraege_number >= $pos) && $down)	{
					$validq = 1;
				}
				if ($validq)	{
					$temparray = $assistenten_auftraege;
					foreach ($temparray as $ky => $vl) {
						if ($vl[position] == $pos):
							$name1 = $vl[unit_id];
							$type1 = $vl[type];
							$number = $vl[number];
							if ($up): $assistenten_auftraege[$ky][position] = $pos+1; $newpos = $pos+1;endif;
							if ($down): $assistenten_auftraege[$ky][position] = $pos-1; $newpos = $pos-1; endif;
						endif;
						if ($down and $vl[position] == $pos-1):
							$assistenten_auftraege[$ky][position] = $pos; endif;
						if ($up and $vl[position] == $pos+1):
							$assistenten_auftraege[$ky][position] = $pos; endif;
					}
					$queries[] = "delete from kosttools_militaerq where user_id=$id and position=$pos";
					$queries[] = "update kosttools_militaerq set position=$pos where position=".($up ? ($pos+1):($down ? ($pos-1):""))." and user_id=$id";
					$queries[] = "insert into kosttools_militaerq (user_id, type, number, unit_id, position) values ($id, '$type1', '$number', '$name1', $newpos)";
				}
			}
			else { $tpl->assign('ERROR', "Ein Parameter fehlt!"); }
		}
		else { $tpl->assign('ERROR', "Sie haben nur einen Auftrag in der Warteschlange stehen. Wo es keine Reihenfolge gibt, kann auch keine Reihenfolge geändert werden ;)."); }
	}
	else { $tpl->assign('ERROR', "Sie haben keine Aufträge in der Warteschlange stehen. Welchen Eintrag möchten Sie da bitteschön ändern ?");}
}

if ($doings == "buyqueue") {
			$ausgabe .= "<br>Bevor Sie sich dazu entscheiden den $name freizuschalten, vorab einige erklärende Worte dazu.<br><br>
			Der $name kostet Sie einmalig 50 EMOs (diese \"Währung\" erhalten Sie, wenn Sie Ihren Emogames-Account zuvor aufgeladen haben, wie dies geht, erfahren Sie <a href=\"militaerseite.php?headeraction=charge\"  class=gelblink target=_blank>hier</a>). Wenn Sie sich gleich für ein Paket entscheiden, können Sie sogar noch bis zu 30% sparen! Sie können den $name dann einen Monat lang benutzen. Wenn Sie den $name_dativ danach nicht weiter benutzen möchten, brauchen Sie nichts weiter zu tun. Eine automatische Verlängerung bieten wir zwar an, diese wird allerdings nur auf Ihren Wunsch hin aktiv. Falls Sie Ihren Spielaccount löschen oder aus anderweitigen Gründen verlieren sollten, bleibt der $name selbstverständlich auch für den neu erstellten Spielaccount verfügbar. Sollten Sie Ihren Emogames-Account löschen, ist bei Wiederanmeldung eine Zuordnung des $name_dativ für Ihren Spielaccount nicht mehr möglich.<br><br><br>Der $name erlaubt Ihnen, bis zu ".$anzahl_assistenten_plaetze." Aufträge für den Einheitenbau in eine Art \"Warteschlange\" zu stellen und diese in der gewünschten Reihenfolge zu 	ordnen.<br>Bei jedem Tick überprüft der Assistent die Aufträge der Reihe nach von oben nach unten und führt soviele davon wie möglich aus (abhängig von Ihrem MCr- und kt-Guthaben).<br><br>";
			// http://test.DOMAIN.de/index.php?server_id=".$game[server_id]."&game_id=2&feature_id=2&action=features&ia=buy
			if (!$forschungsq): $ausgabe .= "<table align=center><tr><td class=siteGround align=center>Möchten Sie den $name_dativ <b>für einen Monat</b> freischalten?<br><br><center><a href=\"militaerseite.php?headeraction=features".urlencode("&view=2")."\" class=gelblink target=_blank>Ja, dies kostet mich einmalig 50 EMOs.</a></center></td></tr><tr><td><center><a href=militaerseite.php class=gelblink>Nein, ich möchte den $name_dativ nicht freischalten.</a></center></td></tr></table><br><br>";
			elseif ($forschungsq): $ausgabe .= "Sie haben den $name_dativ bereits freigeschaltet"; endif;

}

////// ENDE ///// ASSISTENT





$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//							Berechnungen									//

// BEGIN Spystats und Unitstats modifizieren, je nach modifikatoren:
    // Bonus der Factories berechnen, falls positive Energieproduktion
	$energyadd = energyadd($status{id}, 6);
    if ($energyadd >= 0 or true) { // positive Energiebilanzregel ab Runde 10 deaktiviert
        $factorymod = ($status{factories}/$status{land})*FACTORYBONUS;
        if ($factorymod >=FACTORYBONUS*2/10 ) {$factorymod=FACTORYBONUS*2/10 ;} // Maximal 50% Bonus von Factories
    }
    $costmodmil += $factorymod;
    unset ($factorymod);

    // Cheaper Unit production
    if  ($sciences{ind8}) 	{
        $costmodmil +=IND8BONUS/100*$sciences{ind8};
    }
    $costmodmil=(1-$costmodmil);

    // Maximal 60% Bonus
    $costmodmil < 0.4 ? $costmodmil = 0.4 : 1;

    // Cheaper Spy production
    if  ($sciences{glo3}) 	{
        $costmodspies +=GLO3BONUS/100*$sciences{glo3};
    }
	// Seccenters
    if ($energyadd >= 0 or true) { // positive Energiebilanzregel ab Runde 10 deaktiviert
		if ($status[seccenters] && $status[race] == "sl") {
			$secbonus = SECCENTERBONUS * $status[seccenters]/$status[land];
			$secbonus > 0.4 ? $secbonus = 0.4:1;
			$costmodspies += $secbonus;
		}
    }
	// Partnerbonus für Spies
	if ($partner[13]) {
		$costmodspies += $partner[13] * SPY_PRICE_PARTNERBONUS;
	}
    $costmodspies=(1-$costmodspies);
	$costmodspies < 0.4 ? $costmodspies = 0.4 : 1;

    // Unitstats modifizieren, wenn modifikatoren vorliegen
   if ($costmodmil < 1 or ($status['race'] == "nof" && $sciences['mil11'])) {
        foreach ($unitstats as $temp => $value) {
        		if (true){//($status['race'] != "nof" || $temp != "techs") {
					$unitstats{$temp}{credits} = (int) ($unitstats{$temp}{credits} * $costmodmil);
					$unitstats{$temp}{minerals} = (int) ($unitstats{$temp}{minerals} * $costmodmil);
					$unitstats{$temp}{energy} = (int) ($unitstats{$temp}{energy} * $costmodmil);
					$unitstats{$temp}{sciencepoints} = (int) ($unitstats{$temp}{sciencepoints} * $costmodmil);
				} else {	// Nur für Behemooth
					$temp_costmod = 1;
					if ($sciences['mil11']) $temp_costmod = 2/3;
					$unitstats{$temp}{credits} = (int) ($unitstats{$temp}{credits} * $temp_costmod);
					$unitstats{$temp}{minerals} = (int) ($unitstats{$temp}{minerals} * $temp_costmod);
					$unitstats{$temp}{energy} = (int) ($unitstats{$temp}{energy} * $temp_costmod);
					$unitstats{$temp}{sciencepoints} = (int) ($unitstats{$temp}{sciencepoints} * $temp_costmod);
				}
        }
    }
    $tpl->assign('BUILDCOST_MIL_PERCENT', round($costmodmil * 100, 2));
    

    // Spystats modifizieren, wenn modifikatoren vorliegen
    if ($costmodspies < 1) {
        foreach ($spystats as $temp => $value) {
            $spystats{$temp}{credits} = (int) ($spystats{$temp}{credits} * $costmodspies);
            $spystats{$temp}{energy} = (int) ($spystats{$temp}{energy} * $costmodspies);
        }

    }
    $tpl->assign('BUILDCOST_SPIES_PERCENT', round($costmodspies * 100, 2));
// END Spystats und Unitstats modifizieren, je nach modifikatoren:

#### Ressourcen checken um zu berechnen wieviele einzelne Einheiten gebaut werden können:
$milbaubar = military_baubar();
$spiesbaubar = spies_baubar();
$totalCarriers = getTotalCarriers();

# Kapazitäten berechnen
$moreunits = $maxunits-$miltotal{all} + $totalCarriers;
if ($moreunits < 0 ) { $moreunits = 0;}
$morespies = $maxspies-$spiestotal{all};
if ($morespies < 0 ) { $morespies = 0;}



// Syndikatsarmeedaten holen
$syndikatsarmee = assoc("select offspecs, defspecs from syndikate where synd_id=".$status[rid]);


//********************************************************//
//                    EINHEITEN BAUEN
//********************************************************//

// Diverse Zeitberechnungen zur Formulierung des Db Statements
$hourtime = get_hour_time($time);
if ($globals{roundstatus} == 0)	{$hourtime = $globals{roundstarttime};}
$faktor1 = 1; $faktor2 = 1;
//if ($status{race} == "uic") {$faktor1 -= 0.20;}
if ($sciences{ind5}) {$faktor1 -= 0.20*$sciences{ind5};$faktor2 -= 0.20*$sciences{ind5};}
if ($partner[17]) { $faktor1 -= 0.05*$partner[17]*PARTNER_EINHEITENBAUZEITBONUS; $faktor2 -= 0.05*$partner[17]*PARTNER_EINHEITENBAUZEITBONUS; } # -1h EInheitenbauzeit pro Level;
//if ($status[race] == "uic") $faktor2-=UIC_SPIES_SPEEDBONUS;
if ($faktor1 < 0.3) $faktor1 = 0.3;
if ($faktor2 < 0.3) $faktor2 = 0.3;
$buildtime_mil = $hourtime + BUILDTIME_MIL * 60 * $globals{roundtime} * $faktor1;			#Bauzeit in Sekunden für mileinheiten
$buildtime_spies = $hourtime + BUILDTIME_SPY * 60 * $globals{roundtime} * $faktor2;		#Bauzeit in Sekunden
$buildtime_mil_ticks = BUILDTIME_MIL * $faktor1;
$buildtime_spies_ticks =BUILDTIME_SPY * $faktor2;

if (in_protection($status) && getServertype() == "basic") {
	$buildtime_mil = $hourtime + START_BUILDTIME*60*60;
	$buildtime_spies = $hourtime + START_BUILDTIME*60*60;
	$buildtime_mil_ticks = START_BUILDTIME;
	$buildtime_spies_ticks = START_BUILDTIME;
}


	// ------------------------ queue ------------------------
	if ($decision == "queue" && $militaerq) {

		$auftraege_neu_anzahl = $total_verschiedene_einheiten;
		$inserts_temp = array();
        foreach ($unitstats as $ky => $vl) {
			// Forschungen checken
			if (floor($$ky) > 0  && ( ($unitstats[$ky]["erforschbar"] == 1 && $sciences["ind7"] < 1 && "ind7" != $developing_science) || ($unitstats[$ky]["erforschbar"] == 2 && $sciences["mil11"] < 1 && "mil11" != $developing_science) || $unitstats[$ky]["erforschbar"] == 3 || ($unitstats[$ky]["erforschbar"] == 4 && $sciences["mil18"] < 1 && "mil18" != $developing_science)) ) {
				$disallow = 1;
				if ($unitstats[$ky][erforschbar] == 1) {
					$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Advanced Unit Construction</i> um <b>".$unitstats[$ky][name]."</b> bauen zu können");
				}
				if ($unitstats[$ky][erforschbar] == 2) {
					$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Hightech Unit Construction</i> um <b>".$unitstats[$ky][name]."</b> bauen zu können");
				}
				if ($unitstats[$ky][erforschbar] == 3) {
					$tpl->assign('ERROR', "Die Einheit <b>".$unitstats[$ky][name]."</b> kann nicht normal gebaut werden. Bitte schauen Sie sich die Einheitenbeschreibung an, um zu erfahren, wie Sie diese Einheit produzieren können.");
				}
        		if ($unitstats[$ky][erforschbar] == 4) {
        			$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Basic Unit Construction</i> um <b>".$unitstats[$k][name]."</b> bauen zu können");
        		}
				break;
			
			}
        }
		
		if (!$disallow) {
			if ($auftraege_neu_anzahl <= $assistenten_auftraege_frei) {
				foreach ($unitstats as $ky => $vl) {
	
				
					if (floor($$ky)) {
						$temp_number = ++$assistenten_auftraege_number;
						$inserts_temp[] = "($id, 1, ".$vl[unit_id].", ".check_int($$ky).", ".($temp_number).")";
						$assistenten_auftraege[] = array("user_id" => $id, "type" => 1, "unit_id" => $vl[unit_id], "number" => $$ky, "position" => $temp_number);
					}
				}
				foreach ($spystats as $ky => $vl) {
					if (floor($$ky)) {
						$temp_number = ++$assistenten_auftraege_number;
						$inserts_temp[] = "($id, 2, ".$vl[unit_id].", ".check_int($$ky).", ".($temp_number).")";
						$assistenten_auftraege[] = array("user_id" => $id, "type" => 2, "unit_id" => $vl[unit_id], "number" => $$ky, "position" => $temp_number);
					}
				}
				if ($inserts_temp) {
					$queries[] = "insert into kosttools_militaerq (user_id, type, unit_id, number, position) values ".join(",", $inserts_temp);
					$tpl->assign('MSG', "Ihre Aufträge wurden der Auftragswarteschlange erfolgreich hinzugefügt.");
				}
				//$militaerq_ausgabe = writemilq($assistenten_auftraege);
			} else { $tpl->assign('ERROR', "Sie haben nicht soviele Plätze in Ihrer Auftragsschlange frei. Sie wollten $auftraege_neu_anzahl Aufträge einstellen, haben aber nur noch $assistenten_auftraege_frei Plätze frei!"); }
		}
	}
	// ------------------------ / ------------------------

if ($decision == "build") {
    // Zusammenzählen der neuen Einheiten, die gebaut werden sollen
    $milbuildsum = array_sum ($build_mil);
    $spybuildsum = array_sum ($build_spies);

	if ($status['race'] == "nof") { // Carrier Sonderkacke R28
		$milbuildsum -= $build_mil['elites'];
	}


    // Zuerst schauen ob nicht zuviele Einheiten gebaut werden sollen (POINT1)
    if (($milbuildsum <= $moreunits && $spybuildsum <= $morespies) AND ($status['race'] != "nof" or ($status['race'] == "nof" and ($build_mil['elites'] == 0 or ($build_mil['elites'] > 0 and $build_mil['elites'] <= $maxunits-$totalCarriers))))) {
        //Kosten berechnen
        $moneycosts =0;
        $metalcosts =0;
        $energycosts=0;
        foreach($build_mil as $key => $value) {
        	// Check nach Forschungen wird jetzt hier gemacht
        	//if ($unitstats[$key][erforschbar] == 1 && !$sciences[ind7]) $build_mil[$key] = 0;
        	//if ($unitstats[$key][erforschbar] == 2 && !$sciences[mil11]) $build_mil[$key] = 0;
        
            $moneycosts += $unitstats{$key}{credits} * $value;
            $energycosts += $unitstats{$key}{energy} * $value;
            $metalcosts += $unitstats{$key}{minerals} * $value;
			$sciencepointscosts += $unitstats{$key}{sciencepoints} * $value;
        }

        foreach($build_spies as $key => $value) {
            $moneycosts += $spystats{$key}{credits} * $value;
            $energycosts += $spystats{$key}{energy} * $value;
        }
        
        // Checken ob AUC / HUC da sind
        $disallow = 0;
        foreach ($build_mil as $k => $v) {
        	if ($v > 0  && ( ($unitstats[$k]["erforschbar"] == 1 && $sciences["ind7"] < 1 && $decision != "queue") || ($unitstats[$k]["erforschbar"] == 2 && $sciences["mil11"] < 1 && $decision != "queue") || $unitstats[$k]["erforschbar"] == 3 || ($unitstats[$k]["erforschbar"] == 4 && $sciences["mil18"] < 1 && $decision != "queue")) ) {
        		$disallow = 1;
        		if ($unitstats[$k][erforschbar] == 1) {
        			$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Advanced Unit Construction</i> um <b>".$unitstats[$k][name]."</b> bauen zu können");
        		}
        		if ($unitstats[$k][erforschbar] == 2) {
        			$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Hightech Unit Construction</i> um <b>".$unitstats[$k][name]."</b> bauen zu können");
        		}
				if ($unitstats[$k][erforschbar] == 3) {
					$tpl->assign('ERROR', "Die Einheit <b>".$unitstats[$k][name]."</b> kann nicht normal gebaut werden. Bitte schauen Sie sich die Einheitenbeschreibung an, um zu erfahren, wie Sie diese Einheit produzieren können.");
				}
        		if ($unitstats[$k][erforschbar] == 4) {
        			$tpl->assign('ERROR', "Sie benötigen die Forschung <i>Basic Unit Construction</i> um <b>".$unitstats[$k][name]."</b> bauen zu können");
        		}
        		break;
        	}
        }
         
	## Fix damit man mit -Cr HUCs bauen kann..
	if ($status{money} < 0){$tempmoney = 0;}else{$tempmoney = $status{money};}
        
        if (!$disallow) {
			if ($tempmoney >= $moneycosts && $status{metal} >= $metalcosts && $status{energy} >= $energycosts && $status{sciencepoints} >= $sciencepointscosts) {


				// Queries auf das querie array legen:
				// Guthaben
				// Aktuelle Variablen aktualisieren
				$status{money} -= $moneycosts;
				$status{metal} -= $metalcosts;
				$status{energy} -= $energycosts;
				$status{sciencepoints} -= $sciencepointscosts;
				$status{nw} = nw($status{id});
				array_push ($queries,"update status set money=money-$moneycosts,metal=metal-$metalcosts,energy=energy-$energycosts,sciencepoints=sciencepoints-$sciencepointscosts,nw = ".$status{nw}." where id = ".$status{id});
				// Inserts für Militäreinheiten
				$nostring_mil=0;
				$nostring_spies=0;
				$mbuildstring = "insert into build_military (unit_id,user_id,number,time) values ";
				$mbuildstringlog = "insert into build_logs (subject_id,user_id,number,time,time_end,what) values ";
				foreach ($build_mil as $key => $value) {
					if ($value > 0) {
						$nostring_mil=1;
						$miltotal{all}+=$value;
						$miltotal{build}+=$value;
						//if ($status[race] == "sl" && $key == "elites2")	{ $buildtime_mil = $hourtime + (($buildtime_mil-$hourtime) * ($faktor1-0.25))/($faktor1);}
						$mbuildstring.="(".$unitstats{$key}{unit_id}.",".$status{id}.",$value,$buildtime_mil),";
						$mbuildstringlog.="(".$unitstats{$key}{unit_id}.",".$status{id}.",$value,$time,$buildtime_mil,'mil'),";
						$tpl->assign('MSG', "$value ".$unitstats{$key}{name}." erfolgreich in Auftrag gegeben.");
						if ($status['race'] == "nof" && ($unitstats[$key]['unit_id'] == 24 || $unitstats[$key]['unit_id'] == 40)) $totalCarriers += $value;
					}
				}
				$mbuildstring = chopp($mbuildstring);
				$mbuildstringlog = chopp($mbuildstringlog);
				if ($nostring_mil) {
					array_push($queries,$mbuildstring);
					array_push($queries,$mbuildstringlog);
				}
				unset ($mbuildstring,$mbuildstringlog);

				// Inserts für Spionageeinheiten
				$sbuildstring = "insert into build_spies (unit_id,user_id,number,time) values ";
				$sbuildstringlog = "insert into build_logs (subject_id,user_id,number,time,time_end,what) values ";
				foreach ($build_spies as $key => $value) {
					if ($value > 0) {
						$nostring_spies=1;
						$spiestotal{all}+=$value;
						$spiestotal{build}+=$value;
						$sbuildstring.="(".$spystats{$key}{unit_id}.",".$status{id}.",$value,$buildtime_spies),";
						$sbuildstringlog.="(".$spystats{$key}{unit_id}.",".$status{id}.",$value,$time,$buildtime_spies,'spy'),";
						$tpl->assign('MSG', $value.' '.$spystats{$key}{name}.' erfolgreich in Autrag gegeben');
					}
				}
				$sbuildstring = chopp($sbuildstring);
				$sbuildstringlog = chopp($sbuildstringlog);
				if ($nostring_spies) {
					array_push($queries,$sbuildstring);
					array_push($queries,$sbuildstringlog);
				}
				unset ($sbuildstring,$sbuildstringlog);

				#### Ressourcen checken um zu berechnen wieviele einzelne Einheiten gebaut werden können:
				$milbaubar = military_baubar();
				$spiesbaubar = spies_baubar();
				# Kapazitäten berechnen
				$moreunits = $maxunits-$miltotal{all} + $totalCarriers;
				if ($moreunits < 0 ) { $moreunits = 0;}
				$morespies = $maxspies-$spiestotal{all};
				$spies_used_percent = round(($spiestotal[all] / $maxunits) * 100);
				if ($morespies < 0 ) { $morespies = 0;}
			}
			// Wenn nicht genügend Ressourcen vorhanden sind (POINT2)
			else {
				$tpl->assign('ERROR', "Sie haben nicht genügend Ressourcen um soviele Einheiten bauen zu können");
			}
		}
    }
    // Wenn zuviele Einheiten gebaut werden (POINT1)
    else {
        $tpl->assign('ERROR', "Sie haben nicht genügend freie Kapazitäten um so viele Einheiten bauen zu können");
    }
}

//********************************************************//
//                    EINHEITEN Zerstören
//********************************************************//

if ($_POST['decision'] == "raze") {
    // Nachschauen ob überhaupt genügend einheiten vorhanden sind
    $razeok = 1;
    foreach ($build_mil as $key => $value) {
        if ($value > 0 && $status{$key} < $value) {$razeok = 0;}
    }
    if ($razeok == 1) {
        foreach ($build_spies as $key => $value) {
            if ($value > 0 && $status{$key} < $value) {$razeok = 0;}
        }
    }
    // Wenn genug einheiten vorhanden und entlassen bestätigt (POINT1)
    if ($razeok == 1 && $razereally == 1) {
        $noraze = 0;
        $razestring ="update status set ";
        
        //Variable zur zwischenspeicherung wie viele Einheiten Max entlassen werden dürfen, Änderung runde 63 es dürfen nur noch units entlassen werden
        //welche über den OC von 150% gehen, dark-john 14.05.2012
        $milentlassen=$miltotal{all}-$maxunits;
		$milentlassennof=$miltotal{all}-$totalCarriers-$maxunits;
		$milcarrierentlassen=$totalCarriers-$maxunits;
		//Wenn ein OC größer 150% besteht und die Anzahl der Units die entlassen werden den oc von 150% nicht unterschreiten
		//$status['race']!="nof")
        if($miltotal{all}>($maxunits) && array_sum($build_mil)<=$milentlassen && $status['race']!="nof"){
        	$mbuildstringlog = "insert into build_logs (subject_id,user_id,number,time,action,what) values ";
        	foreach ($build_mil as $key => $value) {
            	if ($value > 0) {
                	$status{$key} -= $value;
                	$miltotal{all}-= $value;
                	$miltotal{status}-= $value;
                	$norazemil=1;
	            	$mbuildstringlog.="(".$unitstats{$key}{unit_id}.",".$status{id}.",$value,$time,1,'mil'),";
            		$razestring.="$key = $key - $value,";
                	$tpl->assign('MSG', "Sie haben $value ".$unitstats{$key}{name}." entlassen");
                	if ($status['race'] == "nof" && $unitstats[$key]['unit_id'] == 24) $totalCarriers -= $value;
            	}
        	}
			$mbuildstringlog = chopp($mbuildstringlog);
			//carriersonderscheis für militäreinheiten dürfen nur entlassen werden wenn ein oc über 150% besteht und auch nur was drüber geht dark-john
		}elseif ($status['race']=="nof" && (($totalCarriers>($maxunits) && ($build_mil['elites']<=$milcarrierentlassen)&& $build_mil['elites']>0 && array_sum($build_mil)== $build_mil['elites'] ) 
		    || (($miltotal{all}-$totalCarriers)>($maxunits) && (array_sum($build_mil)-$build_mil['elites'])<=$milentlassennof && $build_mil['elites']==0))){
			$mbuildstringlog = "insert into build_logs (subject_id,user_id,number,time,action,what) values ";
        	foreach ($build_mil as $key => $value) {
            	if ($value > 0) {
                	$status{$key} -= $value;
                	$miltotal{all}-= $value;
                	$miltotal{status}-= $value;
                	$norazemil=1;
	            	$mbuildstringlog.="(".$unitstats{$key}{unit_id}.",".$status{id}.",$value,$time,1,'mil'),";
            		$razestring.="$key = $key - $value,";
                	$tpl->assign('MSG', "Sie haben $value ".$unitstats{$key}{name}." entlassen");
                	if ($status['race'] == "nof" && $unitstats[$key]['unit_id'] == 24) $totalCarriers -= $value;
            	}
        	}
			$mbuildstringlog = chopp($mbuildstringlog);
		}elseif ($miltotal{all}<($maxunits && $status['race']!="nof"  )){
			$tpl->assign('ERROR', "Sie dürfen maximal ".$milentlassen." Militäreinheiten entlassen");
		}elseif($build_mil['elites']>$milcarrierentlassen && $status['race']=="nof"){
			$tpl->assign('ERROR', "Sie dürfen maximal ".$milcarrierentlassen." Militäreinheiten entlassen");
		}elseif((array_sum($build_mil)-$build_mil['elites'])>$milentlassennof && $status['race']=="nof" ){
			$tpl->assign('ERROR', "Sie dürfen maximal ".$milentlassennof." Militäreinheiten entlassen");
		}elseif(array_sum($build_mil)>$milentlassen){
			$tpl->assign('ERROR', "Sie dürfen maximal ".$milentlassen." Militäreinheiten entlassen");
		}
		
		$spieentlassen=$spiestotal{all}-$maxspies;
		//Wenn ein OC größer 150% besteht und die Anzahl der Units die entlassen werden den oc von 150% nicht unterschreiten
		if($spiestotal{all}>($maxspies) && array_sum($build_spies)<=$spieentlassen){
        	$sbuildstringlog = "insert into build_logs (subject_id,user_id,number,time,action,what) values ";
        	foreach ($build_spies as $key => $value) {
            	if ($value > 0) {
               	 	$status{$key} -= $value;
                	$spiestotal{all}-= $value;
                	$spiestotal{status}-= $value;
                	$norazespy=1;
					$sbuildstringlog.="(".$spystats{$key}{unit_id}.",".$status{id}.",$value,$time,1,'spy'),";
                	$razestring.="$key = $key - $value,";
                	$tpl->assign('MSG', "Sie haben $value ".$spystats{$key}{name}." entlassen");
            	}
        	}
			$sbuildstringlog = chopp($sbuildstringlog);
		}elseif ($spiestotal{all}<($maxspies) && array_sum($build_spies)>0){
			$tpl->assign('ERROR', "Sie können nur Spionageeinheiten entlassen wenn Sie weiter als 100% im Overcharge sind.");
		}elseif(array_sum($build_spies)>$spieentlassen && array_sum($build_spies)>0){
			$tpl->assign('ERROR', "Sie dürfen maximal ".$spieentlassen." Spionageeinheiten entlassen");
		}
		
        $status{nw} = nw($status{id});
        $razestring = chopp($razestring);
        $razestring.=",nw = ".$status{nw}." where id =".$status{id};
        if ($norazespy || $norazemil) {
            array_push ($queries,$razestring);
        }
        if ($norazemil) {
			array_push($queries,$mbuildstringlog);
		}
        if ($norazespy) {
			array_push($queries,$sbuildstringlog);
		}

        $milbaubar = military_baubar();
        $spiesbaubar = spies_baubar();
        # Kapazitäten berechnen
        $moreunits = $maxunits-$miltotal{all} + $totalCarriers;
        if ($moreunits < 0 ) { $moreunits = 0;}
        $morespies = $maxspies-$spiestotal{all};
        if ($morespies < 0 ) { $morespies = 0;}
    }
    // Einheiten vorhanden aber entlassen noch nicht bestätigt
    elseif ($razeok == 1 && !$razereally){
    		
	//$linkext = "decision=raze&razereally=1";
 	$beschr = "Sie sind gerade dabei Units zu entlassen.<br><br> Wollen sie wirklich folgende Units entlassen:<br><br>
		<form id=\"commit_form\" action=\"militaerseite.php\" method=\"post\">
	";
	   foreach ($unitstats as $key => $value) {                           //Units zusammenzählen
                    if ($$key) {
                      $beschr .= "<li>".pointit($$key)."  $value[name] </li><input type=\"hidden\" name=\"".$key."\" value=\"".$$key."\" />";
                    }
                }
	    foreach ($spystats as $key => $value) {                           //Spys zusammenzählen
                    if ($$key) {
                      $beschr .= "<li>".pointit($$key)."  $value[name] </li><input type=\"hidden\" name=\"".$key."\" value=\"".$$key."\" />";
                        }
                }
				
		
	
	
                 $beschr .= "<br>
                        <center>
								<input type=\"hidden\" name=\"decision\" value=\"raze\" />
								<input type=\"hidden\" name=\"razereally\" value=1 />
                                <a href=\"militaerseite.php\">NEIN - war ein Versehen</a><br><br>
                                <a href=\"#\" onClick=\"document.getElementById('commit_form').submit();\">JA - Sofort entlassen!</a></form>
                        </center>";
                $tpl->assign('INFO', $beschr); 
				

          
    //foreach($build_mil as $key => $value) {

    }
    // Wenn nicht soviel Einheiten vorhanden (POINT1)
    else {
        $tpl->assign('ERROR', "Soviele Einheiten besitzen sie nicht");
    }
}

//********************************************************//
//                    EINHEITEN der Syndikatsarmee zuführen
//********************************************************//

if ($decision == "spend" && $globals[roundstatus] == 2 && !isBasicServer($game)) {        # Runde zu Ende. Funktion geht nicht mehr.
	$tpl->assign('ERROR', "<br><center><b>Die Runde ist zu Ende!  Diese Aktion kann nicht mehr ausgeführt werden!</b></center>");
}

if ($decision == "spend" && $globals[roundstatus] == 1 && !isBasicServer($game)) {
	if ($status['createtime'] + PROTECTIONTIME >= $time) {
		$tpl->assign('ERROR', "Sie können der Syndikatsarmee erst ".(PROTECTIONTIME/3600)."h nach Erstellen Ihres Konzerns Militäreinheiten zuteilen.");
	}
	else {
		$offspecs = floor($offspecs); $defspecs = floor($defspecs);
		if ($offspecs) { // Marines ab Runde 13 deaktiviert
			if ($status[offspecs] >= $offspecs) {
				$offspecupdate1 = "offspecs=offspecs-$offspecs";
				$temp = $offspecs;
				while ($temp > 0) {
					$temp_input = 0;
					if ($temp > 65535) {
						$temp_input = 65535;
						$temp -= 65535;
					} else {
						$temp_input = $temp;
						$temp = 0;
					}
					$insertvalues[] = "('".$status[rid]."','$id','1','$temp_input','$time', '".($hourtime + 10 * 3600)."')";
				}
				$status[offspecs] -= $offspecs;
				$tpl->assign('MSG', "Sie haben ".pointit($offspecs)." ".$unitstats[offspecs][name]." erfolgreich in die Syndikatsarmee überführt. Die Truppen sind dort in 10 Stunden verfügbar.");
				$update = 1;
			}
			else { $tpl->assign('ERROR', "Soviele ".$unitstats[offspecs][name]." besitzen Sie nicht."); }
		}
		if ($defspecs) {
			if ($status[defspecs] >= $defspecs) {
				$defspecupdate1 = "defspecs=defspecs-$defspecs";
				$temp = $defspecs;
				while ($temp > 0) {
					$temp_input = 0;
					if ($temp > 65535) {
						$temp_input = 65535;
						$temp -= 65535;
					} else {
						$temp_input = $temp;
						$temp = 0;
					}
					$insertvalues[] = "('".$status[rid]."','$id','2','$temp_input','$hourtime', '".($hourtime + 10 * 3600)."')";
				}
				$status[defspecs] -= $defspecs;
				$update = 1;
				$tpl->assign('MSG', "Sie haben ".pointit($defspecs)." ".$unitstats[defspecs][name]." erfolgreich in die Syndikatsarmee überführt. Die Truppen sind dort in 10 Stunden verfügbar.");
			}
			else { $tpl->assign('ERROR', "Soviele ".$unitstats[defspecs][name]." besitzen Sie nicht."); }
		}
		if ($update) { $queries[] = "update status set $offspecupdate1".($offspecupdate1 && $defspecupdate1 ? ",":"")."$defspecupdate1 where id=$id"; $queries[] = "insert into build_syndarmee (rid, user_id, miltype, number, time_send, time_there) values ".join(",", $insertvalues);}
	}
}

//							Daten schreiben									//
db_write($queries);
//							Ausgabe schreiben								//
unset($queries);

// Positive Energiebilanzregel ab Runde 10 deaktiviert
// Fehlerausgabe, wenn facs und zu wenige energie
/*
if ($status[factories] > 0) {
	if ($energyadd < 0) {
		$tpl->assign('ERROR', "Achtung: aufgrund unzuereichener Energieversorung sind ihre Fabriken stillgelegt!");
	}
}
*/

if ($goon)	{
	
	####################################################
	###		ANSICHT SYNARMEE
	####################################################
	
	
	if ($action == "viewsynarmystats" && !isBasicServer($game)) { // Syndikatsarmee ab Runde 12 wieder deaktiviert
		$cols = array("heute" => 0, "gestern" => 24 * 3600, "vorgestern" => 2 * 24 * 3600);
		$hours = date("H");
		$minutes = date("i");
		$seconds = date("s");
		$day_begin_time = $time - $hours * 3600 - $minutes * 60 - $seconds;
		$two_days_ago = $day_begin_time - 2 * 24 * 3600;
		$playerids = array(0);
		$builddata = assocs("select user_id, rid, miltype, number, time_send, time_there from build_syndarmee where rid = ".$status[rid]." and time_there > $two_days_ago");
		$builddata_total = assocs("select user_id, miltype, sum(number) as number from build_syndarmee where rid = ".$status[rid]." group by user_id, miltype");
		if ($builddata) {
			foreach ($builddata as $vl) {
				if (!$playerids[$vl[user_id]]): $playerids[$vl[user_id]] = $vl[user_id]; endif;
				$builddata_nach_spieler[$vl[user_id]][] = $vl;
				if ($vl[time_there] > $time) {
					$x = floor ( ($vl[time_there] - $time) / ($globals[roundtime] * 60));
					$syndarmysorted[$vl[miltype]][$x] += $vl[number];
				}
			}
		}
		if ($builddata_total) {
			foreach ($builddata_total as $vl) {
				$builddata_nach_spieler_total[$vl[user_id]][$vl[miltype]] = $vl[number];
				$total_insgesamt[$vl[miltype]] += $vl[number];
			}
		}
		$playerdata = assocs("select syndicate, id from status where rid = ".$status[rid]." or id in (".join(",", $playerids).") order by syndicate asc", "id");


		function wann($there) {
			global $day_begin_time, $cols, $time;
 			foreach ($cols as $ky => $vl) { if ($day_begin_time - $there <= $vl && $time >= $there) { return $ky; } }
			return "derzeit";
		}
		$total[1] = array( "derzeit" => 0, "heute" => "0", "gestern" => 0, "vorgestern" => 0);
		$total[2] = array( "derzeit" => 0, "heute" => "0", "gestern" => 0, "vorgestern" => 0);
		$playerdata_output = array();
		foreach ($playerdata as $ky => $vl) {
			$data = array(1 => array( "derzeit" => 0, "heute" => "0", "gestern" => 0, "vorgestern" => 0), 
						  2 => array( "derzeit" => 0, "heute" => "0", "gestern" => 0, "vorgestern" => 0));
			if ($builddata_nach_spieler[$ky]) {
				foreach ($builddata_nach_spieler[$ky] as $vl2) {
					$data[$vl2[miltype]][wann($vl2[time_there])] += $vl2[number];
					$total[$vl2[miltype]][wann($vl2[time_there])] += $vl2[number];
				}
			}
			$vl['o_data1_derzeit'] = pointit($data[1][derzeit]);
			$vl['o_data2_derzeit'] = pointit($data[2][derzeit]);
			$vl['o_data1_heute'] = pointit($data[1][heute]);
			$vl['o_data2_heute'] = pointit($data[2][heute]);
			$vl['o_data1_gestern'] = pointit($data[1][gestern]);
			$vl['o_data2_gestern'] = pointit($data[2][gestern]);
			$vl['o_data1_vorgestern'] = pointit($data[1][vorgestern]);
			$vl['o_data2_vorgestern'] = pointit($data[2][vorgestern]);
			$vl['o_builddata_nach_spieler_total1'] = pointit($builddata_nach_spieler_total[$ky][1]);
			$vl['o_builddata_nach_spieler_total2'] = pointit($builddata_nach_spieler_total[$ky][2]);
			array_push($playerdata_output, $vl);
		}
		$tpl->assign('PLAYERDATA', $playerdata_output);
		
		// Gesammt-Zeile
		$tpl->assign('TOTAL_1_derzeit', pointit($total[1][derzeit]));
		$tpl->assign('TOTAL_2_derzeit', pointit($total[2][derzeit]));
		$tpl->assign('TOTAL_1_heute', pointit($total[1][heute]));
		$tpl->assign('TOTAL_2_heute', pointit($total[2][heute]));
		$tpl->assign('TOTAL_1_gestern', pointit($total[1][gestern]));
		$tpl->assign('TOTAL_2_gestern', pointit($total[2][gestern]));
		$tpl->assign('TOTAL_1_vorgestern', pointit($total[1][vorgestern]));
		$tpl->assign('TOTAL_2_vorgestern', pointit($total[2][vorgestern]));
		$tpl->assign('TOTAL_1_insgesamt', pointit($total_insgesamt[1]));
		$tpl->assign('TOTAL_2_insgesamt', pointit($total_insgesamt[2]));

		// Einheiten die gerade in die Syndikatsarmee übertragen werden
		if (sizeof($syndarmysorted))	{
			$tpl->assign('SYNARMYINORDER', true);
			$unittype_output = array();
			foreach (array(1 => array("name" => "Marines", "type" => "offspecs"), 
						   2 => array("name" => "Ranger", "type" => "defspecs")) as $ky => $vl)	{
				for ($o = 0, $u = 1; $o <= 9; $o++, $u++)	{
					$vl['in_'.$u.'_Tick'] = $syndarmysorted[$ky][$o];
					
					/*if ($syndarmysorted[$ky][$o]) {
						$syndarmysorted[$ky][$o] = pointit($syndarmysorted[$ky][$o]);
					}
					else { - } */
				}
				array_push($unittype_output, $vl);
			}
			$tpl->assign('UNITTYPE', $unittype_output);
		}
		else { 
			// Keine Einheiten unterwegs zur Syndikatsarmee!
		}
	}
	else {
		
		
	//beraterzeugs
	$thissite="militaerseite";
	$t = $time; 
	if ($globals[roundstatus] == 0)	{ $t = $globals[roundstarttime] + 1;};
	
	$x = 0;

	$ausgabe_mil = "";
	$ausgabe_milaway = "";
	$ausgabe_spy = "";
	
	$hour = date("H");
	
	$forname = "";
	$searchtime = "";
	$propriate_action_1 = "";
	$propriate_action_2 = "";
	$propriate_action_3 = "";
	$total = 0;
	$remain = 0;
	
	$goon = 1;
	$searchtime = "";
	
	$tplHourCol = array();
	
	for($i = 1; $i < 21; $i++){
	
		$current = "";
		
		if ($status[beraterview] == 1) {
			if ($hour+$i >= 24)
				$current = ($hour+$i-24);
			else
				$current = ($hour+$i);
		} else
			$current = $i;
	
		array_push($tplHourCol, $current);
		
	}
	
	$tpl->assign("HOURCOL",$tplHourCol);

	
	$tpl_Tables = array();

	//unitsmill
	$milnames = assocs("select name, unit_id, race, type from military_unit_settings where race='$status[race]' or race='all' order by sort_order", "unit_id");
	$milnamesA = assocs("select name, unit_id, race, type from military_unit_settings where race='$status[race]' order by sort_order", "type");
	$milbuild = rows("select unit_id,number,time from build_military where user_id='$id'");
	$milaway = rows("select unit_id,number,time from military_away where user_id='$id'");

	$tpl_Table = array();
	$tpl_Table["name"] = "Militärausbildung";

	foreach ($milbuild as $value)	{

		$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
		if ($value[0]==40)
			$value[0] = $milnamesA['elites']['unit_id'];
		if ($value[0]==41)
			$value[0] = $milnamesA['elites2']['unit_id'];
		if ($value[0]==42)
			$value[0] = $milnamesA['techs']['unit_id'];
			
		$milsorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($milsorted))	{
		
		$tpl_Rows = array();

		foreach ($milnames as $ky => $vl){
		
			$tpl_Details = array();
			
			for ($o = 0, $u = 1; $o <= 19; $o++, $u++)	{
			
				if ($milsorted[$ky][$o]) {
					$milsorted[$ky][$o] = pointit($milsorted[$ky][$o]);
					array_push($tpl_Details , "<a href=$thissite.php?ia=killqu&what=ma&type=$ky&killtime=$u class=\"linkAuftableInner\">".$milsorted[$ky][$o]."</a>");
				}
				else {
					array_push($tpl_Details, "-");
				}
				
			}
			
			array_push($tpl_Rows, array("name"=>$vl[name],"details"=>$tpl_Details));
			
		}
		
		$tpl_Table["rows"] = $tpl_Rows;
		
	} else {
		$tpl_Table["error"] = "Kein Militär in Bau!";
	}

	array_push($tpl_Tables, $tpl_Table);

	// Militäraway
	$tpl_Table = array();
	$tpl_Table["name"] = "Heimkehrendes Militär";

	foreach ($milaway as $value)	{

		$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
		if ($value[0]==40)
			$value[0] = $milnamesA['elites']['unit_id'];
		if ($value[0]==41)
			$value[0] = $milnamesA['elites2']['unit_id'];
		if ($value[0]==42)
			$value[0] = $milnamesA['techs']['unit_id'];
		$milawaysorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($milawaysorted))	{

		$tpl_Rows = array();

		foreach ($milnames as $ky => $vl)	{
		
				$tpl_Details = array();

				for ($o = 0, $u = 1; $o <= 19; $o++, $u++)	{
				
					if ($milawaysorted[$ky][$o]) {
						$milawaysorted[$ky][$o] = pointit($milawaysorted[$ky][$o]);
						array_push($tpl_Details , "<a href=$thissite.php?ia=killqu&what=hm&type=$ky&killtime=$u class=\"linkAuftableInner\">".$milawaysorted[$ky][$o]."</a>");
					} 
					else { 
						array_push($tpl_Details, "-");
					}	
					
				}
				
				array_push($tpl_Rows, array("name"=>$vl[name],"details"=>$tpl_Details));
					
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

	foreach ($spybuild as $value)	{

		$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
		$spysorted[$value[0]][$x] += $value[1];

	}

	if (sizeof($spysorted))	{

		$tpl_Rows = array();

		foreach ($spynames as $ky => $vl){
		
			$tpl_Details = array();
			
			for ($o = 0, $u = 1; $o <= 19; $o++, $u++)	{
			
				if ($spysorted[$ky][$o]){
					$spysorted[$ky][$o] = pointit($spysorted[$ky][$o]);
					array_push($tpl_Details , "<a href=$thissite.php?ia=killqu&what=sa&type=$ky&killtime=$u class=\"linkAuftableInner\">".$spysorted[$ky][$o]."</a>");
				}
				else { 
					array_push($tpl_Details , "-");
				}
				
			}
			
			array_push($tpl_Rows, array("name"=>$vl[name],"details"=>$tpl_Details));
			
		}

		$tpl_Table["rows"] = $tpl_Rows;

	} else { 
		$tpl_Table["error"] = "Keine Spione in Bau!";
	}

	array_push($tpl_Tables, $tpl_Table);
	$tpl->assign("TABLES",$tpl_Tables);
	
		if ($ia == "killqu")		{
	
		$proceed = check_validity($what, $type, $killtime);
		if ($proceed)		{
	
			//all
			$searchtime = get_hour_time($t) + $killtime * 60 * $globals[roundtime];
			$total = row(get_propriate_action($what, $type, "select number"));
			$total = $total[0];
			//endall
	
			if ($innestaction == "next")	{
	
				if ($total >= $n && is_numeric($n) && $n > 0)	{
					
					//all
					$remain = $total - $n;
					$ok = 0;
					//endall
					
					
					//spies
					if ($what == "sa"){
						//dark-john
						//es dürfen nur units > 100% OC entlassen werden
						$spiesentlassen=$spiestotal{all}-$maxspies;
						if($spiestotal{all}<$maxspies || ($spiestotal{all}-$n ) < $maxspies ){
							$ok=0;
							$tpl->assign("ERROR", "Du darfst nur noch ".$spiesentlassen." Spionageeinheiten entlassen! ");
						}else{
							$ok=1;
							$beschr = "Du hast soeben die Ausbildung von $n ".$spynames[$type][name]." abgebrochen, welche in $killtime Stunden ausgebildet worden wären.";
							$tpl->assign("MSG", $beschr);
						}
					}
					//endspies
					$milentlassennof=$miltotal{all}-$totalCarriers-$maxunits;
					$milcarrierentlassen=$totalCarriers-$maxunits;
					$milentlassen=$miltotal{all}-$maxunits;
					
					//Es dürfen nur einheiten entlassen werden wenn man 100% im OC steht
					if($status['race']=="nof" && (($n > $milcarrierentlassen && $type==24) || ($n > $milentlassennof && $type!=24) )){
		   					$ok=0;
		   					$tpl->assign("ERROR", "Du darfst nur ".$milentlassennof." normale Einheiten entlassen oder ".$milcarrierentlassen." Carrier entlassen");
					}elseif(($miltotal{all} < $maxunits || $n>$milentlassen ) && $status['race']!="nof" ){  
							$ok=0;
							$tpl->assign("ERROR", "Du darfst keine Einheiten entlassen wenn du nicht im Overcharge bist! ");
					}else{
					
					//unitmill
					if ($what == "ma"){
							$ok=1;
						
							$beschr = "Du hast soeben die Ausbildung von $n ".$milnames[$type][name]." abgebrochen, welche in $killtime Stunden ausgebildet worden  wären.";
							$tpl->assign("MSG", $beschr);
				
					}
					if ($what == "hm"){
							$ok=1;
						
						$beschr = "Du hast soeben die Heimkehr von $n ".$milnames[$type][name]." abgebrochen, welche in $killtime Stunden heimgekehrt wären.";
						$tpl->assign("MSG", $beschr);
						$status[$type] -= $n;
						$status{nw} = nw($status{id});
						$queries[] = "update status set nw=".$status[nw]." where id=$id";

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
					if (!$total) $total = 0;
					//spes
					if ($what == "sa")
						{$errormsg = "Die Ausbildung sovieler ".$spynames[$type][name]." ($n von $total) kannst du nicht abbrechen!";$tpl->assign('ERROR', $errormsg);}
					//endspies
					
					//unitsmill	
					if ($what == "ma")
						{$errormsg = "Die Ausbildung sovieler ".$milnames[$type][name]." ($n von $total) kannst du nicht abbrechen!";$tpl->assign('ERROR', $errormsg);}
					if ($what == "hm")
						{$errormsg = "Soviele ".$milnames[$type][name]." ($n von $total) kannst du nicht entlassen!";$tpl->assign('ERROR', $errormsg);}
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
	
	
	//ende berater zeugs
	db_write($queries);	
		
		//$javascript = js::$loadClassChange().js::$loadOver();
		
		$javascript = getMilitaryPageJavaScript();
		
		$tpl->assign('ADDITIONAL_JAVASCRIPT', $javascript);
		####################################################
		###		ANSICHT MILITÄR - UND SPIONAGE BAU
		####################################################
		
		# unis in bau ermitteln
		$mil_imbau = military_in_build($status{id});
		$spies_imbau = spies_in_build($status{id});
		$totalCarriers = getTotalCarriers();

		// BEGIN $AUSGABE
					
		## AUSGABE BAUFORMULAR
		$tpl->assign('SPIESTOTAL', $spiestotal);
		$tpl->assign('MILITOTAL', $miltotal);

		####################################################
		## ausgabe für militäreinheiten
		####################################################
								
		$unit_output = array();
		$tabindex = 1;
		foreach ($unitstats as $key => $value) {
			if (   ($value["erforschbar"] == 1 && $sciences["ind7"] < 1) 
				|| ($value["erforschbar"]== 2 && $sciences["mil11"] < 1) 
				|| ($value["erforschbar"]== 4 && $sciences["mil18"] < 1)) {
				$milbaubar{$key} = "n/a";
			}
			else{
				$milbaubar{$key} = pointit($milbaubar[$key]);
			}
			// Name, ToolTip, Anzahl zuhause
			$value['o_unitName'] = $unitstats[$key][name];
			$value['o_MilitaryToolTip'] = getMilitaryToolTip($unitstats[$key],1);
			$value['o_status'] = pointit($status[$key]);
			// Falls Mili oder Spies unterwegs
			if ($spiestotal["away"] or $miltotal["away"]) $value['o_away'] = pointit($miltotal["away_array"][$key]);
			if ($spiestotal["market"] or $miltotal["market"]) $value['o_market'] = pointit($miltotal["market_array"][$key]);
			// Ressourcen
			if ($unitstats[$key]["credits"]) $value['o_credits'] = pointit($unitstats[$key]["credits"]);
			if ($unitstats[$key]["sciencepoints"]) $value['o_sciencepoints'] = pointit($unitstats[$key]["sciencepoints"]);
			$value['o_minerals'] = pointit($unitstats[$key]["minerals"]);
			$value['o_energy'] = pointit($unitstats[$key]["energy"]);
					
			$value['o_key'] = $key;
			$value['o_milbaubar'] = $milbaubar{$key};
			$value['o_mil_imbau'] = pointit($mil_imbau[$key]);
			$value['o_tabindex'] = $tabindex++;
			array_push($unit_output, $value);
		} # foreach keys unitstats
		$tpl->assign('UNITSTATS', $unit_output);
		
		// Kapazitätsanzeige Miliunits
		$units_used_percent = round((($miltotal[all]-$totalCarriers) / $maxunits) * 100);
		$tpl->assign('KAPAS_MILI', pointit($moreunits));
		$tpl->assign('KAPAS_MILI_PRC', $units_used_percent);
		// Kapazitätsanzeige Spys
		$spies_used_percent = round(($spiestotal[all] / $maxspies) * 100);
		$tpl->assign('KAPAS_SPY', pointit($morespies));
		$tpl->assign('KAPAS_SPY_PRC', $spies_used_percent);
		// Kapazitätsanzeige Nof Carrier
		if ($status['race'] == "nof") {
			$morecarriers = ((($maxunits - $totalCarriers) < 0) ? 0 : ($maxunits - $totalCarriers));
			$carriers_used_percent = round($totalCarriers / $maxunits * 100);
			$tpl->assign('KAPAS_CARRIER', pointit($morecarriers));
			$tpl->assign('KAPAS_CARRIER_PRC', $carriers_used_percent);
		} 

		## Ausgabe für Spionageinheiten
		$spystats_output = array();
		foreach ($spystats as $key => $value) {
			$value['o_spyName'] = $spystats{$key}{name};
			$value['o_SpyToolTip'] = getSpyToolTip($spystats{$key}, 1);
			$value['o_status'] = pointit($status{$key});
			if ($spiestotal[away] or $miltotal[away]) $value['o_away'] = pointit($spiestotal[away_array][$key]);
			if ($spiestotal[market] or $miltotal[market]) $value['o_market'] = pointit($spiestotal[market_array][$key]);
			$value['o_credits'] = pointit($spystats{$key}{credits});
			$value['o_energy'] = pointit($spystats{$key}{energy});
			$value['o_key'] = $key;
			$value['o_spiesbaubar'] = pointit($spiesbaubar{$key});
			$value['o_spies_imbau'] = pointit($spies_imbau{$key});
			$value['o_tabindex'] = $tabindex++;
			array_push($spystats_output, $value);
		}		
		$tpl->assign('SPYSTATS', $spystats_output);	
		// Bauzeit der Einheiten
		$tpl->assign('BUILDTIME_MIL_TICKS', $buildtime_mil_ticks);
		$tpl->assign('BUILDTIME_SPIES_TICKS', $buildtime_spies_ticks);
			// Militärassistent
		if ($militaerq) {
			$data = $assistenten_auftraege;
			if (!$data) { $data = FALSE; }
			if (!$unitstats_intern) $unitstats_intern = assocs("select * from military_unit_settings order by sort_order", "unit_id");
			if (!$spystats_intern) $spystats_intern = assocs("select * from spy_settings", "unit_id");
			if (is_array($data)) {
				$tpl->assign('ANZ_FOR', count($data));
				usort($data, "milsort");
				$data_output = array();
				foreach ($data as $vl) {
					$pos=$vl["position"];
					$vl['o_number'] = pointit($vl[number]);
					if ($vl[type] == 1) $vl['o_unitName'] = $unitstats_intern[$vl[unit_id]][name];
					else $vl['o_unitName'] = $spystats_intern[$vl[unit_id]][name];
					array_push($data_output, $vl);
				}
				$tpl->assign('DATA', $data_output);
			} else {
				// Keine Einträge in der Warteliste.
			}
		}
		#### ENDE AUSGABE BAUFORMULAR

		// SYNDIKATSARMEE
		if (($globals[roundstatus] == 1 ||$globals[roundstatus] == 2)&& !isBasicServer($game)) {	
			$tpl->assign('SYNARMY_SHOW', true);
			$tpl->assign('OFFSPECS_NAME', $unitstats[offspecs][name]);
			$tpl->assign('OFFSPECS_ATHOME', pointit($status[offspecs]));
			$tpl->assign('OFFSPECS_INSYNARMY', pointit($syndikatsarmee[offspecs]));
			$tpl->assign('DEFSPECS_NAME', $unitstats[defspecs][name]);
			$tpl->assign('DEFSPECS_ATHOME', pointit($status[defspecs]));
			$tpl->assign('DEFSPECS_INSYNARMY', pointit($syndikatsarmee[defspecs]));
		} 
			
		// Ende showBuildStuff
			
		// BERECHNUNG von getMilitaryStatusView in lib/subs_attack.php
		$tpl->assign('MILITARYSTATUSVIEW', getMilitaryStatusView());
	}
} # ende $goon


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('MILITAERQ', $militaerq);
$tpl->assign('YELLOWDOT', $yellowdot);
$tpl->assign('RIPF', $ripf);
$tpl->assign('WIKI', WIKI);
$tpl->assign('ACTION', $action);
$tpl->assign('GOON', $goon);
$tpl->assign('STATUS', $status);


require_once("../../inc/ingame/header.php");


$tpl->assign("USERINPUT", $userinput);

//Fehler
if ($tpl->get_template_vars('ERROR') != ''){
	$tpl->display('fehler.tpl');
}
//Meldung
if ($tpl->get_template_vars('MSG') != ''){
	$tpl->display('sys_msg.tpl');
}
if ($tpl->get_template_vars('INFO') != '') {
	$tpl->display('info.tpl');
}
$tpl->display('militaerseite.tpl');
require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


###############################
#### military_baubar ##########
###############################

function military_baubar() {

    $baubar = array ();
    global $unitstats,$status,$maxunits,$miltotal,$totalCarriers,$sciences;
    if (!isset($totalCarriers)) $totalCarriers = getTotalCarriers();
    $money_limit = 0;
    $metal_limit = 0;
    $energy_limit = 0;
	$sciencepoints_limit = 0;
    $space_limit = 0;
	$fix = 1000000; // Kein limit

    foreach ($unitstats as $key => $value) {
        if (!$unitstats{$key}{credits}) { $money_limit  = $fix; }
		else { $money_limit  = (int) ($status{money} / $unitstats{$key}{credits}); };
		if ($unitstats{$key}{minerals} > 0) {
        	$metal_limit  = (int) ($status{metal} / $unitstats{$key}{minerals});
		} else {$metal_limit = $fix;}
		if ($unitstats{$key}{energy} > 0) {
        	$energy_limit  = (int) ($status{energy} / $unitstats{$key}{energy});
		} else {$energy_limit = $fix;}

		if (!$unitstats{$key}{sciencepoints}) { $sciencepoints_limit = 1000000; }
		else { $sciencepoints_limit = (int) ($status{sciencepoints} / $unitstats{$key}{sciencepoints}); };

        $space_limit  = (int) $maxunits-($miltotal{all}-$totalCarriers);
        if ($key == "elites" && $status['race'] == "nof") $space_limit = floor($maxunits-$totalCarriers);
        $sort = array ($money_limit,$metal_limit,$energy_limit,$sciencepoints_limit,$space_limit);
        sort ($sort);
        $sort[0] < 0 ? $sort[0] = 0 : 1;

			// Behemoths noch Ranger berücksichtigen:
			if ($status['race'] == "nof" && false) {
				if ($key == "techs") {
					{ // wie oben, nur ohne $space_limit
						$sort = array($money_limit,$metal_limit,$energy_limit,$sciencepoints_limit);
						sort($sort);
						$sort[0] < 0 ? $sort[0] = 0 : 1;
					}
					//echo "sp limit ".$sort[0]." - $sciencepoints_limit";
					$baubar_durch_ranger = floor($status['elites'] / 2);
					if ($baubar_durch_ranger < $sort[0]) $sort[0] = $baubar_durch_ranger;
					if (!$sciences['mil11']) $sort[0] = "n/a"; // benötigt Hightech Unit Construction
				}
			}
        
        $baubar[$key] = $sort[0];
    }
    return $baubar;
}




###############################
#### spies_baubar ##########
###############################

function spies_baubar() {
    
    global $spystats,$status,$maxspies,$spiestotal;    
    $baubar =array ();
    $money_limit = 0;
    $energy_limit = 0;
    $space_limit = 0;
    
    foreach ($spystats as $key => $value) {
        $money_limit  = (int) ($status{money} / $spystats{$key}{credits});
        $energy_limit  = (int) ($status{energy} / $spystats{$key}{energy});
        $space_limit  = (int) $maxspies-$spiestotal{all};
        $sort = array ($money_limit,$energy_limit,$space_limit);
        sort ($sort);
        $sort[0] < 0 ? $sort[0] = 0 : 1;
        $baubar[$key] = $sort[0];
    }
return $baubar;
}

#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#=====================military_in_build========================================
#<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

// Seit Runde 28 in subs.php

#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#=====================spies_in_build===========================================
#<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

function spies_in_build($id) {
    
    $imbau=array ();
    global $spystats;
    foreach ($spystats as $key => $value) {$imbau{$key} = 0;}
    
    $result = select("select unit_id,number from build_spies where user_id=$id");
    
    for ($i=0; $return = mysql_fetch_row($result);$i++) {
        foreach ($spystats as $key => $value) {
            if ($spystats{$key}{unit_id} == $return[0]) {
                $imbau{$key} += $return[1]; break;
            }
       } # foreach keys
   } #for return
    return $imbau;						 
} # subende

function getMilitaryPageJavaScript() {
	return "
	
			<script language=\"Javascript\">
			<!--
				var view = 'Build';
			
				function maxbuy(product) {
					mproduct = 'max_'+product;
					bproduct = 'build_'+product;
					document.getElementById(bproduct).value = document.getElementById(mproduct).innerHTML; 
				}
				
				
				function showStatusView(showStatus) {
					if (showStatus == 0){
						view = 'Build';
						
						document.getElementById('showStatusStuff').style.display = 'none';
						document.getElementById('showBuildStuff').style.display = 'inline';
						document.getElementById('showBeraterStuff').style.display = 'none';
						
						document.getElementById('td_bauansicht').className = 'tableInner2';
						document.getElementById('td_status').className = 'tableInner1';
						document.getElementById('td_berater').className = 'tableInner1';
					}
					else if (showStatus == 1) {
						view = 'Status';
						
						document.getElementById('showStatusStuff').style.display = 'inline';
						document.getElementById('showBuildStuff').style.display = 'none';
						document.getElementById('showBeraterStuff').style.display = 'none';
						
						document.getElementById('td_bauansicht').className = 'tableInner1';
						document.getElementById('td_status').className = 'tableInner2';
						document.getElementById('td_berater').className = 'tableInner1';
					}
					else if (showStatus == 2) {
						view = 'Berater';
						
						document.getElementById('showStatusStuff').style.display = 'none';
						document.getElementById('showBuildStuff').style.display = 'none';
						document.getElementById('showBeraterStuff').style.display = 'inline';
						
						document.getElementById('td_bauansicht').className = 'tableInner1';
						document.getElementById('td_status').className = 'tableInner1';
						document.getElementById('td_berater').className = 'tableInner2';
					}
				}
				
				
				function checkOver(elem) {
					elem.className='tableInner2';
					elem.style.cursor='pointer';
				}
				
				function checkOut(elem) {
					
					if (document.getElementById('td_bauansicht') == elem && view == 'Status') {
						elem.className='tableInner1';
					}
					if (document.getElementById('td_status') == elem && view == 'Build') {
						elem.className='tableInner1';
					}
					if (document.getElementById('td_berater') == elem && view == 'Berater') {
						elem.className='tableInner1';
					}
				}
				
			-->
			</script>	
			".js::loadClassChange()."
			".js::loadOver()."
			
	";
	
}


?>
