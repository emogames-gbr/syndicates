<?

//
//	TODO AP/VP:
//  0) Weitere Display-Funktionen programmieren
// 	a) Calc_modified_unitstats-Funktion zur Berechnung von Faktoren, die Units stärker machen
//  b) Darstellung der Boni noch nicht optimal
//  c) Ausklappfunktion für Angriffstärke von Einheiten + unmodifizierte Stärke	
//	d) Synarmee einrechnen
//	e) Zeilensumme Militäreinheiten unter berücksichtigung der 2:1 Regel
//  f) Unitboni vom Titan/Nof-Ranger einkalkulieren
//  g) Vergleich berechneter Werte mit Angriffseite
// 
// 	Z) Landgain/Unitloss/Rückkehrzeit
// 
//


#
# REQUIRES
#
require_once ("js.php");




###
###	BASH PROTECTION
###
function get_bash_protection($target_id) {
	
	global $status,$time;
	
	$timelimit = $time -  TIME_RELEVANT_FOR_BASH_PROTECTION;	
	
	// Siege und killspies
	// Attacktypes:
	// 1 : Normal
	// 2 : Siege
	// 3 : Conquer
	// 4 : Killspies
	
	$attacks_got = single("select count(*) from attacklogs where did=$target_id and time >= $timelimit and gbprot=1 and type in (1,2,3,4) and winner='a' group by did");

	$modifier1 = ($attacks_got >= BASH_PROTECTION_1_ATTACKS_NEED) ? BASH_PROTECTION_1_GAIN : 100;
	$modifier2 = ($attacks_got >= BASH_PROTECTION_2_ATTACKS_NEED) ? BASH_PROTECTION_2_GAIN : 100;
	$modifier =  ($modifier2 != 100 ? $modifier2 : $modifier1) / 100;
	
	return array($modifier,$attacks_got);
}


###
### Erzeuge Ansicht für Militärstatus
###
function getMilitaryStatusView() {
	global $status, $sciences, $artefakte,$partner, $game_syndikat;
	$status_inner = $status;	
	
	/// TEST 
	/*
	for ($i=1; $i < 16; $i++) {
		$sciences["glo".$i] = $sciences["mil".$i] = 1;
	}
	
	$sciences["mil1"] = 2; // Basic Offense
	$sciences["mil2"] = 2; // Basic Defense
	$partner[1] = 1;		// Off partner pb
	//$partner[2] = 1;		// Off partner pb
	$partner[19] = 1;		// Off partner pb
	//$game_syndikat[artefakt_id] = 15;
	$status[offtowers] = 100;
	//$status[deftowers] = 100;
	
	$sciences["mil6"] = 0; // Basic Defense
	*/
	
	/// TEST 
	
	
	
	if (!is_array($status_inner)) return;
	if (!is_array($sciences)) $sciences = getsciences();
	if (!is_array($partner))  $partner = getpartner();
	if (!is_array($game_syndikat)) $game_syndikat = assoc("select * from syndikate where synd_id=$status_inner[rid]");
	

	// Marktdaten können hier für die Berechnung auf Status gerechnet werden.
	$market = getmarket($status_inner[id]);
	if (is_array($market)) {
		foreach ($market as $k => $v) {
			$status_inner[$k] += $v;
		}
	}
	
	/*
	pvar($status_inner[id]);
	pvar($game_syndikat);
	pvar($sciences);
	pvar($partner);	
	*/
	
	$unitstats_raw = getunitstats($status_inner[race]);
	$unitstats_mod = modify_unitstats($unitstats_raw);
	
	$unitcount = count($unitstats_raw);
	
	
	$attackboni_collected = collectDisplayBoni("off");
	$defboni_collected    = collectDisplayBoni("def");
	$synarmyboni_collected= collectDisplayBoni("synarmy");
	$otherboni_collected  = collectDisplayBoni("other");
	
	
	// Showtables - Was an Boni angezeigt wird
	$showtables = array();
	if (count($attackboni_collected) > 0) {
		$showtables[] = array("Angriffsboni","attackboniTable");
	}
	if (count($defboni_collected) > 0) {
		$showtables[] = array("Verteidigungsboni","defboniTable");
	}
	if (count($synarmyboni_collected) > 0) {
		$showtables[] = array("Syndikats-Armee","synarmyTable");
	}
	if (count($otherboni_collected) > 0) {
		$showtables[] = array("Sonstige","otherTable");
	}
	
	
	// Summen der Boni für spätere Anzeige	
	$sum_percent_bonus_a = 0;
	$sum_percent_bonus_d = 0;
	$sum_percent_synarmy = 0;
	$sum_percent_synarmy_war = 0;
	
	
	////////////////////////
	// Build attackBoniTable
	////////////////////////
	
		
		
		$attackboniTable = "
		<table width=\"98%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableInner1\">
		";
		if (count($attackboni_collected) > 0) {
			foreach ($attackboni_collected as $v) {
				$attackboniTable.="
					<tr>
						<td width=\"85%\">$v[0]</td>
						<td width=\"10%\" align=\"left\">$v[1]</td>
						<td width=\"5%\">$v[2]</td>
					</tr>
				";
				
				if ($v[4] == "%") $sum_percent_bonus_a += $v[3];
			}
		}
		$sum_percent_bonus_a = round_percent($sum_percent_bonus_a);
		
		$attackboniTable.="
			<tr style=\"vertical-align:bottom;\">
				<td><hr color=\"black\" size=\"1\"><b>Summe:</b></td>
				<td colspan=\"2\" align=\"left\"><hr color=\"black\" size=\"1\"><b>$sum_percent_bonus_a%</b></td>
			</tr>
		</table>
		";
		
	
	
	
	////////////////////////
	// Build defBoniTable
	////////////////////////
	
		$defboniTable = "
		<table width=\"98%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableInner1\">
		";
		if (count($defboni_collected) > 0) {
			foreach ($defboni_collected as $v) {
				$defboniTable.="
					<tr>
						<td width=\"85%\">$v[0]</td>
						<td width=\"10%\" align=\"left\">$v[1]</td>
						<td width=\"5%\">$v[2]</td>
					</tr>
				";
				
				if ($v[4] == "%") $sum_percent_bonus_d += $v[3];
			}
		}
		$sum_percent_bonus_d = round_percent($sum_percent_bonus_d);
		
		
		$defboniTable.="
			<tr style=\"vertical-align:bottom;\">
				<td><hr size=\"1\" color=\"black\"><b>Summe:</b></td>
				<td colspan=\"2\" align=\"left\"><hr color=\"black\" size=\"1\"><b>$sum_percent_bonus_d%</b></td>
			</tr>
		</table>
		";
		
		
			
	////////////////////////
	// Build Synarmy
	////////////////////////
	
		$synarmyTable = "
		<table width=\"98%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableInner1\">
		";
		if (count($synarmyboni_collected) > 0) {
			foreach ($synarmyboni_collected as $v) {
				$synarmyTable.="
					<tr>
						<td width=\"85%\">$v[0]</td>
						<td width=\"10%\" align=\"left\">$v[1]</td>
						<td width=\"5%\">$v[2]</td>
					</tr>
				";
				
				$sum_percent_synarmy += $v[3];
				$sum_percent_synarmy_war += (MIL15BONUS_FACTOR_SYNARMY_ATWAR-1) * $v[3]; // Zusätzliche Unterstützung im Krieg
			}
		}
		$sum_percent_synarmy = round_percent($sum_percent_synarmy);
		
		
		$synarmyTable.="
			<tr style=\"vertical-align:bottom;\">
				<td><hr  color=\"black\" size=\"1\"><b>Summe:</b></td>
				<td colspan=\"2\" align=\"left\"><hr color=\"black\" size=\"1\"><b>$sum_percent_synarmy%</b></td>
			</tr>
		</table>
		";		
		
		
	
	////////////////////////
	// Build othertable
	////////////////////////
	
		$otherTable = "
		<table width=\"98%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableInner1\">
		";
		if (count($otherboni_collected) > 0) {
			foreach ($otherboni_collected as $v) {
				$otherTable.="
					<tr>
						<td width=\"75%\">$v[0]</td>
						<td width=\"20%\" align=\"left\">$v[1]</td>
						<td width=\"5%\">$v[2]</td>
					</tr>
				";
				
			}
		}
		
		$otherTable.="
		</table>
		";			

	////////////////////////
	// Show Boni
	////////////////////////
	
	
	
	
	$back = "
	
	<!-- BoniTable -->
	<div style=\"display:none\" id=\"boni_expanded\">
	<table width=\"98%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\" align=center><tr><td width=100% align=center>
	
		<table width=\"100%\" cellpadding=\"5\" cellspacing=\"1\" border=\"0\">
			<tr class=\"tableHead\">
				<td align=\"center\" colspan=\"2\" onClick=\"expandBonusView(0)\" >
					<div style=\"position:relative;display:inline;left:0%;\" ".js::classChange("tableInner2","tableHead").">Boni Angriffs- und Verteidigungsstärke [-]</b>
				</td>
			</tr>
			";
	
			for ($j = 0; $j < count($showtables); $j+=2 ) {
				
				if ($j % 2 != 0) continue;
				
				$singlecellonly = (count($showtables) % 2 == 1 && count($showtables)-$j < 2) ? 1 :0;
				
				$back.="
					<tr class=\"tableHead2\" >
						<td align=\"center\" width=\"50%\" ".($singlecellonly == 1 ? "colspan=\"2\"" : "")." >".$showtables[$j][0]."</td>";
						if ($singlecellonly == 0) {
							$back.="<td align=\"center\" width=\"50%\" >".$showtables[$j+1][0]."</td>";
						}
					$back.="
					</tr>
					<tr height=\"100%\">
						<td class=\"tableInner1\" ".($singlecellonly == 1 ? "colspan=\"2\"" : "").">".$$showtables[$j][1]."</td>";
						if ($singlecellonly == 0) {						
							$back.="<td class=\"tableInner1\">".$$showtables[$j+1][1]."</td>";
						}
					$back.="
					</tr> 		
							
				";
				
			}

		$back.="			
		</table>	
	</td></tr></table>
	</div>
	
	<div style=\"display:inline\" id=\"boni_collapsed\" onClick=\"expandBonusView(1)\">
	<table width=\"98%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\" align=center><tr><td width=100% align=center>
	 
		<table width=\"100%\" cellpadding=\"5\" cellspacing=\"1\" border=\"0\">
			<tr class=\"tableHead\" >
				<td align=\"center\" colspan=\"3\" >
					<div ".js::classChange("tableInner2","tableHead")." style=\"position:relative;display:inline;left:0%;\">Boni Angriffs- und Verteidigungsstärke [+]</b>
				</td>
			</tr>
			<tr class=\"tableInner1\">
				<td align=\"center\" width=\"33%\"><b>Angriffsboni</b></td>
				<td align=\"center\" width=\"33%\"><b>Verteidigungsboni</b></td>
				<td align=\"center\" width=\"33%\"><b>Syndikatsarmee</b></td>
			</tr>
			<tr class=\"tableInner1\">
				<td align=\"center\">$sum_percent_bonus_a%</td>
				<td align=\"center\">$sum_percent_bonus_d%</td>
				<td align=\"center\">$sum_percent_synarmy%</td>
			</tr>
		</table>

		
	</td></tr></table>
	</div>

	
	
	
	
	
	<div style=\"height:10px;\"></div>
		
	
	
	
	<!-- Unit Table -->
	<table width=\"98%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\" align=center><tr><td width=100% align=center>
	
		<table width=\"100%\" cellpadding=\"5\" cellspacing=\"1\" border=\"0\">
			
			<tr class=\"tableHead\">
				<td align=\"center\" colspan=\"7\">Militärstärke</td>
			</tr>
		
			
			<tr class=\"tableInner2\">
				<td align=\"center\" colspan=\"4\" style=\"border-right: 2px solid black;\">Unmodifiziert</td>
				<td align=\"center\" colspan=\"2\">Mit Boni </td>
			</tr>
		
			<tr class=\"tableHead2\">
				<td>Einheit</td>
				<td align=\"center\">Anzahl</td>
				<td align=\"center\">AP / VP</td>
				<td align=\"center\" style=\"border-right: 2px solid black;\">Summe</td>
				<td align=\"center\">AP / VP</td>
				<td align=\"center\">Summe ".getJsHelpTag("Die Summe errechnet sich aus:<br>Anzahl Einheiten * <br>modifizierte AP / VP *<br> Angriffsboni / Verteidigungsboni")."</td>
			</tr>
		";

				// Bonusfaktoren gesamt
					$factorA = 1+($sum_percent_bonus_a/100);
					$factorD = 1+($sum_percent_bonus_d/100);
					
				// Summen
					$sumap = 0;
					$sumdp = 0;
					$sumapmod = 0;
					$sumdpmod = 0;
			
				foreach ($unitstats_raw as $k => $v) {
					
					
					
					// Berechnung Werte
					$sumnormalA = $status_inner[$k] * $v[op];
					$sumnormalD = $status_inner[$k] * $v[dp];
					$sumboniA   = floor($status_inner[$k] * $unitstats_mod[$k][op] * $factorA);
					$sumboniD   = floor($status_inner[$k] * $unitstats_mod[$k][dp] * $factorD);
					$sumnormalString = pointit($sumnormalA)." / ".pointit($sumnormalD);
					$sumboniString   = pointit($sumboniA)." / ".pointit($sumboniD);
					
					$sumap += $sumnormalA;
					$sumdp += $sumnormalD;
					
					$sumapmod += $status_inner[$k] * $unitstats_mod[$k][op] * $factorA;
					$sumdpmod += $status_inner[$k] * $unitstats_mod[$k][dp] * $factorD;
					
					
					$back.="
						<tr class=\"tableInner1\">
							<td>".$v[name]."</td>
							<td align=\"center\">".pointit($status_inner[$k])."</td>
							<td align=\"center\">".$v[op]." / ".$v[dp]."</td>
							<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">$sumnormalString</td>
							<td align=\"center\">".$unitstats_mod[$k][op]." / ".$unitstats_mod[$k][dp]."</td>
							<td align=\"center\" class=\"tableInner2\">$sumboniString</td>							
						</tr>
					";
				}
				
				// Erst am Ende Runden --> Genauer
				$sumapmod = floor($sumapmod);
				$sumdpmod = floor($sumdpmod);
	
	
				$back.="
				<tr class=\"tableInner2\">
					<td colspan=\"3\" align=\"right\"><b>Summe:</b>&nbsp;</td>
					<td align=\"center\" style=\"border-right: 2px solid black;\"><b>".pointit($sumap)." / ".pointit($sumdp)."</b></td>
					<td align=\"right\"><b>mit Boni:</b>&nbsp;</td>
					<td align=\"center\" ><b>".pointit($sumapmod)." / ".pointit($sumdpmod)."</b></td>							
				</tr>	
				";
				 
				$back_synarmy_war = ''; // Zurücksetzen
				// Syndikatsarmee
				if ($sum_percent_synarmy > 0 && ($game_syndikat["offspecs"] >0 || $game_syndikat["defspecs"] > 0)) {
					
					$showsecondsum = 1;

					$sum_attackunits = calc_number_offunits_for_synarmy_support($status_inner);
					$sum_defunits	 = calc_number_defunits_for_synarmy_support($status_inner);
					
					$sum_supported_attackunits = min(($sum_attackunits*$sum_percent_synarmy/100),$game_syndikat["offspecs"]);
					$sum_supported_defunits	   = min(($sum_defunits*$sum_percent_synarmy/100),$game_syndikat["defspecs"]);
					
					
					$offsupport = floor($sum_supported_attackunits*$unitstats_raw["offspecs"][op]);
					$defsupport = floor($sum_supported_defunits*$unitstats_raw["defspecs"][dp]);
					
					$offsupport_mod = floor($sum_supported_attackunits*$unitstats_mod["offspecs"][op]*$factorA);
					$defsupport_mod = floor($sum_supported_defunits*$unitstats_mod["defspecs"][dp]*$factorD);
				

					if ($status_inner[race] == 'nof') {
						$synarmyhelp =getJsHelpTag("Carrier werden nicht von der Syndikatsarmee unterstützt.");
					}
					
					$back.="
					<!-- synarmee -->
					<tr class=\"tableHead2\">
						<td colspan=\"7\" align=\"left\">Syndikatsarmee - <div style=\"text-decoration:none;display:inline;font-weight:normal;font-size:11px;\">(Unterstützung mit <b>$sum_percent_synarmy%</b> auf max. ".pointit(($sum_attackunits*$sum_percent_synarmy/100))." Angriffseinheiten und ".pointit(($sum_defunits*$sum_percent_synarmy/100))." Verteidigungseinheiten) ".$synarmyhelp."</div></td>
					</tr>
					
					<tr class=\"tableInner1\">
						<td>Marines</td>
						<td align=\"center\">".pointit((int)$sum_supported_attackunits)."</td>
						<td align=\"center\">".$unitstats_raw["offspecs"][op]." / ".$unitstats_raw["offspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">".pointit($offsupport)." / 0</td>
						<td align=\"center\">".$unitstats_mod["offspecs"][op]." / ".$unitstats_mod["offspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\">".pointit($offsupport_mod)." / 0</td>							
					</tr>				
					 
					<tr class=\"tableInner1\">
						<td>Ranger</td>
						<td align=\"center\">".pointit((int)$sum_supported_defunits)."</td>
						<td align=\"center\">".$unitstats_raw["defspecs"][op]." / ".$unitstats_raw["defspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">0 / ".pointit($defsupport)."</td>
						<td align=\"center\">".$unitstats_mod["defspecs"][op]." / ".$unitstats_mod["defspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\">0 / ".pointit($defsupport_mod)."</td>							
					</tr>	
					
					";
					
					
					// Syndikatsarmee im Krieg (inok R59 Jan 2012)
					
					
					$sum_supported_attackunits_war = min(($sum_attackunits*$sum_percent_synarmy_war/100),$game_syndikat["offspecs"] - $sum_supported_attackunits);
					$sum_supported_defunits_war	   = min(($sum_defunits*$sum_percent_synarmy_war/100),$game_syndikat["defspecs"] - $sum_supported_defunits);
					
					
					$offsupport_war = floor($sum_supported_attackunits_war*$unitstats_raw["offspecs"][op]);
					$defsupport_war = floor($sum_supported_defunits_war*$unitstats_raw["defspecs"][dp]);
					
					$offsupport_mod_war = floor($sum_supported_attackunits_war*$unitstats_mod["offspecs"][op]*$factorA);
					$defsupport_mod_war = floor($sum_supported_defunits_war*$unitstats_mod["defspecs"][dp]*$factorD);
					
					$back_synarmy_war.="
					<!-- synarmee im Krieg -->
					<tr class=\"tableHead2\">
						<td colspan=\"7\" align=\"left\">Syndikatsarmee im Krieg - <div style=\"text-decoration:none;display:inline;font-weight:normal;font-size:11px;\">(zusätzliche Unterstützung mit <b>$sum_percent_synarmy_war%</b>)</div></td>
					</tr>
					
					<tr class=\"tableInner1\">
						<td>Marines</td>
						<td align=\"center\">".pointit((int)$sum_supported_attackunits_war)."</td>
						<td align=\"center\">".$unitstats_raw["offspecs"][op]." / ".$unitstats_raw["offspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">".pointit($offsupport_war)." / 0</td>
						<td align=\"center\">".$unitstats_mod["offspecs"][op]." / ".$unitstats_mod["offspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\">".pointit($offsupport_mod_war)." / 0</td>							
					</tr>				
					 
					<tr class=\"tableInner1\">
						<td>Ranger</td>
						<td align=\"center\">".pointit((int)$sum_supported_defunits_war)."</td>
						<td align=\"center\">".$unitstats_raw["defspecs"][op]." / ".$unitstats_raw["defspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">0 / ".pointit($defsupport_war)."</td>
						<td align=\"center\">".$unitstats_mod["defspecs"][op]." / ".$unitstats_mod["defspecs"][dp]."</td>
						<td align=\"center\" class=\"tableInner2\">0 / ".pointit($defsupport_mod_war)."</td>							
					</tr>	
					
					";
					
					
				}
				// Titanen
				if ($status[race] == "pbf" && $status_inner[techs] > 0 && ($status_inner[offspecs] > 0 || $status_inner[defspecs] > 0 || $$sum_supported_attackunits > 0 || $sum_supported_defunits > 0)) {
					
					$showsecondsum = 1;
					
					$synunit_array = array();
					$synunit_array[offspecs] = $sum_supported_attackunits;
					$synunit_array[defspecs] = $sum_supported_defunits;

					
					$titan_marine_bonus_points = calc_unit_TitanMarineBonus_Raw($status_inner,$synunit_array);
					$titan_ranger_bonus_points = calc_unit_TitanRangerBonus_Raw($status_inner,$synunit_array);
					
					$titan_marine_bonus_points_mod = floor($titan_marine_bonus_points*$factorA);
					$titan_ranger_bonus_points_mod = floor($titan_ranger_bonus_points*$factorD);
					
					$marines_supported = min($status_inner[techs]*PBF_TITAN_MARINE_SUPPORT_NUMBER, floor($status_inner[offspecs]+$sum_supported_attackunits) );
					$ranger_supported = min($status_inner[techs]*PBF_TITAN_RANGER_SUPPORT_NUMBER, floor($status_inner[defspecs]+$sum_supported_defunits) );
					   


					$back.="
					<!-- Titans -->
					<tr class=\"tableHead2\">
						<td colspan=\"7\" align=\"left\">Titanen - <div style=\"text-decoration:none;display:inline;font-weight:normal;font-size:11px;\">(Unterstützen <b>".pointit($marines_supported)."</b> Marines und <b>".pointit($ranger_supported)."</b> Ranger) ".getJsHelpTag("Jeder Titan unterstützt bis zu<br>".PBF_TITAN_MARINE_SUPPORT_NUMBER." Marines mit ".PBF_TITAN_MARINE_SUPPORT_BONUS." Angriffspunkten und<br>".PBF_TITAN_RANGER_SUPPORT_NUMBER." Ranger mit ".PBF_TITAN_RANGER_SUPPORT_BONUS." Verteidigungspunkten")."</div></td>
					</tr>
					
					<tr class=\"tableInner1\">
						<td>Unterstützung Marines</td>
						<td align=\"center\">".pointit((int)$marines_supported)."</td>
						<td align=\"center\">".PBF_TITAN_MARINE_SUPPORT_BONUS." / 0</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">".pointit((int)$titan_marine_bonus_points)." / 0</td>
						<td align=\"center\">".PBF_TITAN_MARINE_SUPPORT_BONUS." / 0</td>
						<td align=\"center\" class=\"tableInner2\">".pointit((int)$titan_marine_bonus_points_mod)." / 0</td>							
					</tr>				
					
					<tr class=\"tableInner1\">
						<td>Unterstützung Ranger</td>
						<td align=\"center\">".pointit((int)$ranger_supported)."</td>
						<td align=\"center\">0 / ".PBF_TITAN_RANGER_SUPPORT_BONUS."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">0 / ".pointit((int)$titan_ranger_bonus_points)."</td>
						<td align=\"center\">0 / ".PBF_TITAN_RANGER_SUPPORT_BONUS."</td>
						<td align=\"center\" class=\"tableInner2\">0 / ".pointit((int)$titan_ranger_bonus_points_mod)."</td>							
					</tr>	
					

					";
				}
				
				// Carrier
				if ($status[race] == "nof" && $status_inner[elites] > 0 && ($status_inner[offspecs] > 0 || $status_inner[defspecs] > 0)) {
					
					$showsecondsum = 1;
					
					$synunit_array = array();
					$synunit_array[offspecs] = $sum_supported_attackunits;
					$synunit_array[defspecs] = $sum_supported_defunits;

					
					$carrier_marine_bonus_points = calc_unit_CarrierMarineBonus_Raw($status_inner,$synunit_array);
					$carrier_ranger_bonus_points = calc_unit_CarrierRangerBonus_Raw($status_inner,$synunit_array);
					
					$carrier_marine_bonus_points_mod = floor($carrier_marine_bonus_points*$factorA);
					$carrier_ranger_bonus_points_mod = floor($carrier_ranger_bonus_points*$factorD);
					
					$marines_supported_car = min($status_inner[elites]*2, floor($status_inner[offspecs]) );
					$ranger_supported_car = min($status_inner[elites]*2, floor($status_inner[defspecs]) );
					   


					$back.="
					<!-- Carrier-->
					<tr class=\"tableHead2\">
						<td colspan=\"7\" align=\"left\">Carrier - <div style=\"text-decoration:none;display:inline;font-weight:normal;font-size:11px;\">(Unterstützen <b>".pointit($marines_supported_car)."</b> Marines und <b>".pointit($ranger_supported_car)."</b> Ranger) ".getJsHelpTag("Jeder Carrier unterstützt bis zu<br>2 Marines mit 2 Angriffspunkten und<br>2 Ranger mit 2 Verteidigungspunkten")."</div></td>
					</tr>
					
					<tr class=\"tableInner1\">
						<td>Unterstützung Marines</td>
						<td align=\"center\">".pointit((int)$marines_supported_car)."</td>
						<td align=\"center\">2 / 0</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">".pointit((int)$carrier_marine_bonus_points)." / 0</td>
						<td align=\"center\">2 / 0</td>
						<td align=\"center\" class=\"tableInner2\">".pointit((int)$carrier_marine_bonus_points_mod)." / 0</td>							
					</tr>				
					
					<tr class=\"tableInner1\">
						<td>Unterstützung Ranger</td>
						<td align=\"center\">".pointit((int)$ranger_supported_car)."</td>
						<td align=\"center\">0 / 2</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">0 / ".pointit((int)$carrier_ranger_bonus_points)."</td>
						<td align=\"center\">0 / 2</td>
						<td align=\"center\" class=\"tableInner2\">0 / ".pointit((int)$carrier_ranger_bonus_points_mod)."</td>							
					</tr>	
					

					";
				}			

				// Patriots
				if ($status[race] == "neb" && $status_inner[elites] > 0 && false) {
				
					$showsecondsum = 1;
					
					$military_away = single("select sum(number) from military_away where user_id = $status_inner[id]");
					$patriots_supported = min($status_inner[elites], $military_away);

					$patriot_returningunits_bonus_points = $patriots_supported * NEB_PATRIOT_RETRUNINGUNITS_BONUS;
					$patriot_returningunits_bonus_points_mod = floor($patriots_supported * NEB_PATRIOT_RETRUNINGUNITS_BONUS * $factorD);
					
					$back.="
					<!-- Titans -->
					<tr class=\"tableHead2\">
						<td colspan=\"7\" align=\"left\">Patriots - <div style=\"text-decoration:none;display:inline;font-weight:normal;font-size:11px;\">(<b>".pointit((int)$patriots_supported)."</b> Patriots werden durch heimkehrende Einheiten verstärkt.) ".getJsHelpTag("Für jede eigene sich auf Heimkehr befindende Einheit erhält ein Patriot einmalig ".NEB_PATRIOT_RETRUNINGUNITS_BONUS." Verteidigungspunkte")."</div></td>
					</tr>			
					
					<tr class=\"tableInner1\">
						<td>Verstärkte Patriots</td>
						<td align=\"center\">".pointit((int)$patriots_supported)."</td>
						<td align=\"center\">0 / ".NEB_PATRIOT_RETRUNINGUNITS_BONUS."</td>
						<td align=\"center\" class=\"tableInner2\" style=\"border-right: 2px solid black;\">0 / ".pointit((int)$patriot_returningunits_bonus_points)."</td>
						<td align=\"center\">0 / ".NEB_PATRIOT_RETRUNINGUNITS_BONUS."</td>
						<td align=\"center\" class=\"tableInner2\">0 / ".pointit((int)$patriot_returningunits_bonus_points_mod)."</td>							
					</tr>		

					";
				}					
				
				
				if ($showsecondsum) {
					$back.="
					<tr class=\"tableInner2\">
						<td colspan=\"3\" align=\"right\"><b>Summe:</b>&nbsp;</td>
						<td align=\"center\" style=\"border-right: 2px solid black;\"><b>".pointit($sumap+$offsupport+$titan_marine_bonus_points+$carrier_marine_bonus_points)." / ".pointit($sumdp+$defsupport+$titan_ranger_bonus_points+$carrier_ranger_bonus_points+$patriot_returningunits_bonus_points)."</b></td>
						<td align=\"right\"><b>mit Boni:</b>&nbsp;</td>
						<td align=\"center\" ><b>".pointit($sumapmod+$offsupport_mod+$titan_marine_bonus_points_mod+$carrier_marine_bonus_points_mod)." / ".pointit($sumdpmod+$defsupport_mod+$titan_ranger_bonus_points_mod+$carrier_ranger_bonus_points_mod+$patriot_returningunits_bonus_points_mod)."</b></td>							
					</tr>	
					";
					
					if ($back_synarmy_war) {
						$back .= $back_synarmy_war;
						
						$back.="
					<tr class=\"tableInner2\">
						<td colspan=\"3\" align=\"right\"><b>Summe im Krieg:</b>&nbsp;</td>
						<td align=\"center\" style=\"border-right: 2px solid black;\"><b>".pointit($sumap+$offsupport+$offsupport_war+$titan_marine_bonus_points+$carrier_marine_bonus_points)." / ".pointit($sumdp+$defsupport+$defsupport_war+$titan_ranger_bonus_points+$carrier_ranger_bonus_points+$patriot_returningunits_bonus_points)."</b></td>
						<td align=\"right\"><b>mit Boni:</b>&nbsp;</td>
						<td align=\"center\" ><b>".pointit($sumapmod+$offsupport_mod+$offsupport_mod_war+$titan_marine_bonus_points_mod+$carrier_marine_bonus_points_mod)." / ".pointit($sumdpmod+$defsupport_mod+$defsupport_mod_war+$titan_ranger_bonus_points_mod+$carrier_ranger_bonus_points_mod+$patriot_returningunits_bonus_points_mod)."</b></td>							
					</tr>	
					";
						
					}
					
				}
					
								
		$back.="		
							
		</table>
	
	
	</td></tr></table><!--Table Outline-->

	
	
	<script language=\"Javascript\">
	<!--

		function expandBonusView(showBonus) {
			if (showBonus == 1) {
				document.getElementById('boni_expanded').style.display = 'inline';
				document.getElementById('boni_collapsed').style.display = 'none';
			}
			else {
				document.getElementById('boni_expanded').style.display = 'none';
				document.getElementById('boni_collapsed').style.display = 'inline';
			}
		}	
	-->
	</script>
	
	";
	
	
	$back .="";



	return $back;
}




#####
#####
#####		CALC FUNCTIONS FOR ATTACK BONUSES
#####		-- Alle Funktionen geben 0 zurück, wenn der Bonus nicht vorhanden ist -
#####		-- sonst % Zahl, Anzahl AP/VP für Steigerung oder % Zahl für Modifikation der Synarmee
#####
#####


#######
####### FORSCHUNGEN 
#######




// Return Percent Number 0-100
function calc_science_BasicOffenseBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil1]*MIL1BONUS_BASIC_OFFENSE;
}

// Return Percent Number 0-100
function calc_science_RAOffenseBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil10]*MIL10BONUS_AP_BONUS;
}


// Return Percent Number 0-100
function calc_science_BasicDefenseBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil2]*MIL2BONUS_BASIC_DEFENSE;
	
}

// Return Percent Number 0-100
function calc_science_DefNetDefenseBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil7]*MIL7BONUS_DEF_NETWORK;
	
}


// Return +2 VP Zahl
function calc_science_RangerUpgradeDefBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	//if ($sciences[mil13] && $status[race] == "neb") return MIL13BONUS_RANGER_UPGRADE;
	return 0;
	
}


// Return +1 AP/VP Zahl
function calc_science_RangerAndMarineBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	$sciences[mil5] = $sciences[mil5] == 3 ? 4 : $sciences[mil5];
	return $sciences[mil5]* MIL5BONUS_RANGER_AND_MARINE;
}

// Return +2 AP/VP Zahl
function calc_science_FowBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil14]* MIL14BONUS_UNITS_VP_EXTRA;
}


// Return +10 AP/VP Zahl
function calc_science_FlexStratBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	if ($sciences[mil6]) return MIL6BONUS_FLEX_STRAT;
	return 0;
	
}


// Return +10%
function calc_science_IWTOffenseBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil12]*MIL12BONUS_IWT;
}


// Return +10%
function calc_science_DefenseNetworkDefBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil7]*MIL7BONUS_DEF_NETWORK;
	
}


// Return +5%
function calc_science_OrbitalDefenseDefBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[glo8]*GLO8BONUS_ORBITAL;
}


// Return % Unterstützung durch Synarmee
function calc_science_SynarmeeBonus($tstatus = "",$tsciences = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,$tsciences);
	
	return $sciences[mil15]*MIL15BONUS_SYNARMY;
}

#######
####### PARTNERBONI
#######

// Return % 
function calc_partner_OffBonus($tstatus = "",$tpartner = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"",$tpartner);
	
	if ($partner[1]) return PARTNER_OFFBONUS;
	return 0;
	
}


// Return % 
function calc_partner_DefBonus($tstatus = "",$tpartner = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"",$tpartner);
	
	if ($partner[2]) return PARTNER_DEFBONUS;
	return 0;
	
}


// Return % 
function calc_partner_SynarmeeBonus($tstatus = "",$tpartner = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"",$tpartner);
	
	if ($partner[19]) return PARTNER_SYNARMEESUPPORT;
	return 0;
	
}


#######
####### MONUMENTE
#######

// Return % 
function calc_monument_OffBonus($tstatus = "",$tsyndikat = "") {
	
	global $artefakte;
	if (!is_array($artefakte)) $artefakte = get_artefakte();
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$tsyndikat);
	if ($artefakte[$syndikat[artefakt_id]][bonusname] == "mil_attack_bonus") return $artefakte[$syndikat[artefakt_id]][bonusvalue];
	
	return 0;
	
}


// Return % 
function calc_monument_DefBonus($tstatus = "",$tsyndikat = "") {
	
	global $artefakte;
	if (!is_array($artefakte)) $artefakte = get_artefakte();
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$tsyndikat);
	
	if ($artefakte[$syndikat[artefakt_id]][bonusname] == "mil_defense_bonus") return $artefakte[$syndikat[artefakt_id]][bonusvalue];
	
	return 0;
	
}




#######
####### Gebäude
#######

// Return % 
function calc_building_OfftowersBonus($tstatus = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$tsyndikat);
	
	$offtowerboni = ( $status[offtowers] * OFFTOWER_BONUS / $status[land] ) * 100;
	
	if ($offtowerboni > OFFTOWER_MAX_BONI) {
		$offtowerboni = OFFTOWER_MAX_BONI;
	}
	
	return $offtowerboni;
	
}

// Return % 
function calc_building_DeftowersBonus($tstatus = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$tsyndikat);
	
	$deftowerboni = ( $status[deftowers] * DEFTOWER_BONUS / $status[land] ) * 100;
	
	if ($deftowerboni > DEFTOWER_MAX_BONI) {
		$deftowerboni = DEFTOWER_MAX_BONI;
	}
	
	return $deftowerboni;
	
}

#######
####### FRAKTIONEN
#######

function calc_race_bfDefBonus($tstatus = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$temp_bonus = 0;
	if ($status['race'] == "pbf") {
		$temp_bonus = floor($status[land] / PBF_DEFENSE_PBFLAND) * PBF_DEFENSE_BONUS_PER_PBFLAND;
		$temp_bonus >PBF_DEFENSE_BONUS_MAX ? $temp_bonus = PBF_DEFENSE_BONUS_MAX : 1; 
	}

	
	return $temp_bonus;
	
}


function calc_race_bfOffBonus($tstatus = "") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$temp_bonus = 0;
	if ($status['race'] == "pbf") {
		$temp_bonus = PBF_ATTACK_BONUS;
	}

	return $temp_bonus;
	
}

function calc_race_NofMarineBonus($tstatus = "") {
	
	global $globals;
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");

	if ($status['race'] != "nof") 
	{
		return 0;
	}
	
	$landconquered = single("select attack_total_won_conquer+attack_total_won_normal from stats where konzernid = $status[id] and round = ".$globals['round']);
	
	$temp_op_plus = floor($landconquered / NOF_MARINE_HA_BARRIER_FOR_OP_PLUS);
	if ($temp_op_plus > NOF_MARINE_MAX_PLUS_OP) $temp_op_plus = NOF_MARINE_MAX_PLUS_OP;
	
	return $temp_op_plus;
	
}


#######
####### UNITS
#######

// Return number of Attackpoints received from Titanbonus/Marines
function calc_unit_TitanMarineBonus_Raw($tstatus = "", $game_syndikat="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$game_syndikat);
	
	
	$temp_bonus = 0;

	if ($status['race'] == "pbf" && $status[techs] > 0) {	
		if (($status[offspecs] or $syndikat[offspecs]) && $status[techs] * PBF_TITAN_MARINE_SUPPORT_NUMBER <= ($status[offspecs] + $syndikat[offspecs])) {
			$temp_bonus = PBF_TITAN_MARINE_SUPPORT_BONUS *PBF_TITAN_MARINE_SUPPORT_NUMBER* $status[techs];
			
		}
		elseif (($status[offspecs] or $syndikat[offspecs]) && $status[techs] * PBF_TITAN_MARINE_SUPPORT_NUMBER > ($status[offspecs] + $syndikat[offspecs])) {
			$temp_bonus = PBF_TITAN_MARINE_SUPPORT_BONUS * ($status[offspecs] + $syndikat[offspecs]);
		}

	}

	return $temp_bonus;
	
}
 
// Return number of Attackpoints received from Titanbonus/Marines
function calc_unit_TitanRangerBonus_Raw($tstatus = "",$game_syndikat="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$game_syndikat);
	
	if ($status['race'] == "pbf" && $status[techs] > 0) {	
		if (($status[defspecs] or $syndikat[defspecs]) && $status[techs] * PBF_TITAN_RANGER_SUPPORT_NUMBER <= ($status[defspecs] + $syndikat[defspecs])) {
			$temp_bonus = PBF_TITAN_RANGER_SUPPORT_BONUS *PBF_TITAN_RANGER_SUPPORT_NUMBER* $status[techs];
			
		}
		elseif (($status[defspecs] or $syndikat[defspecs]) && $status[techs] * PBF_TITAN_RANGER_SUPPORT_NUMBER > ($status[defspecs] + $syndikat[defspecs])) {
			$temp_bonus = PBF_TITAN_RANGER_SUPPORT_BONUS * ($status[defspecs] + $syndikat[defspecs]);
		}

	}


	return $temp_bonus;
	
}

// Return number of Attackpoints received from Titanbonus/Marines
function calc_unit_CarrierMarineBonus_Raw($tstatus = "", $game_syndikat="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$game_syndikat);
	
	
	$temp_bonus = 0;

	if ($status['race'] == "nof" && $status[elites] > 0) {	
		if (($status[offspecs]) && $status[elites] * 2 <= ($status[offspecs])) {
			$temp_bonus = 4* $status[elites];
			
		}
		elseif (($status[offspecs]) && $status[elites] * 2 > ($status[offspecs])) {
			$temp_bonus = 2 * ($status[offspecs]);
		}

	}

	return $temp_bonus;
	
}
 
// Return number of Attackpoints received from Titanbonus/Marines
function calc_unit_CarrierRangerBonus_Raw($tstatus = "",$game_syndikat="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","",$game_syndikat);
	
	if ($status['race'] == "nof" && $status[elites] > 0) {	
		if (($status[defspecs]) && $status[elites] * 2 <= ($status[defspecs])) {
			$temp_bonus = 4* $status[elites];
			
		}
		elseif (($status[defspecs]) && $status[elites] * 2 > ($status[defspecs])) {
			$temp_bonus = 2 * ($status[defspecs]);
		}

	}


	return $temp_bonus;
	
}




#####
#####
#####		DISPLAY FUNCTIONS FOR ATTACK BONUSES
#####
#####


// Display-Bonus-Function
// Return Array if Bonus is present - otherwise 0
// 0 : Name of Bonus
// 1 : Value of Bonus in Text (%, AP, VP)
// 2 : Image Tag with Javascript for Help Display
// 3 : Raw Value of Bonus as number for calculations
// 4 : Type (%, Absolute, Army)


##
##	Forschungen
##

// Basic Offense
function display_science_BasicOffenseBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_BasicOffenseBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil1"]["gamename"]." Level $tsciences[mil1]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil1"]["gamename"]."</b><br>".$sciencestats["mil1"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// RA
function display_science_RAOffenseBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_RAOffenseBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil10"]["gamename"];
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil10"]["gamename"]."</b><br>".$sciencestats["mil10"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}


// Basic Defense
function display_science_BasicDefenseBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_BasicDefenseBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil2"]["gamename"]." Level $tsciences[mil2]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil2"]["gamename"]."</b><br>".$sciencestats["mil2"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Basic Defense
function display_science_DefNetDefenseBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_DefNetDefenseBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil7"]["gamename"]." Level $tsciences[mil7]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil7"]["gamename"]."</b><br>".$sciencestats["mil7"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Ranger Upgrade
function display_science_RangerUpgradeDefBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_RangerUpgradeDefBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil13"]["gamename"]."";
	$back[1] = "+".$bonus." VP";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil13"]["gamename"]."</b><br>".$sciencestats["mil13"]["description"]."");
	$back[3] = $bonus;
	$back[4] = "Absolute";

	return $back;
}

// Ranger & Marine Training
function display_science_RangerAndMarineBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_RangerAndMarineBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil5"]["gamename"]." Level $tsciences[mil5]";
	$back[1] = "+".$bonus." AP/VP";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil5"]["gamename"]."</b><br>".$sciencestats["mil5"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "Absolute";

	return $back;
}

// Flex Strat
function display_science_FlexStratBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_FlexStratBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil6"]["gamename"]."";
	$back[1] = "+".$bonus." AP/VP";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil6"]["gamename"]."</b><br>".$sciencestats["mil6"]["description"]."");
	$back[3] = $bonus;
	$back[4] = "Absolute";

	return $back;
}

// Fow
function display_science_FowBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_FowBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil14"]["gamename"]."";
	$back[1] = "+".$bonus." VP";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil14"]["gamename"]."</b><br>".$sciencestats["mil14"]["description"]."");
	$back[3] = $bonus;
	$back[4] = "Absolute";

	return $back;
}


// IWT Offense-Bonus
function display_science_IWTOffenseBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_IWTOffenseBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil12"]["gamename"]." Level $tsciences[mil12]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil12"]["gamename"]."</b><br>".$sciencestats["mil12"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Defense Network
function display_science_DefenseNetworkDefBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_DefenseNetworkDefBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil7"]["gamename"]." Level $tsciences[mil7]";
	$back[1] = "-".$bonus."% AP";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil7"]["gamename"]."</b><br>".$sciencestats["mil7"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// ODS
function display_science_OrbitalDefenseDefBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_OrbitalDefenseDefBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["glo8"]["gamename"]." Level $tsciences[glo8]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["glo8"]["gamename"]."</b><br>".$sciencestats["glo8"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Synarmee
function display_science_SynarmeeBonus($tstatus = "",$tsciences = "") {
	$sciencestats = getScienceStats();
	$back = array();
	
	$bonus = calc_science_SynarmeeBonus($tstatus,$tsciences);
	if ($bonus == 0) return 0;
	
	$back[0] = $sciencestats["mil15"]["gamename"]." Level $tsciences[mil15]";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>".$sciencestats["mil15"]["gamename"]."</b><br>".$sciencestats["mil15"]["description"]." pro Level");
	$back[3] = $bonus;
	$back[4] = "Army";
	$back[5] = $bonus_war;

	return $back;
}

##
##	Partnerboni
##


// Angriffs-Partnerbonus
function display_partner_OffBonus($tstatus = "",$tpartner = "") {
	$partnerstats = getPartnerStats();
	$back = array();
	
	$bonus = calc_partner_OffBonus($tstatus,$tpartner);
	if ($bonus == 0) return 0;
	
	$back[0] = "Partnerbonus ".$partnerstats[1][bonus]."";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("Partnerbonus: ".$partnerstats[1][bonus]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Def-Partnerbonus
function display_partner_DefBonus($tstatus = "",$tpartner = "") {
	$partnerstats = getPartnerStats();
	$back = array();
	
	$bonus = calc_partner_DefBonus($tstatus,$tpartner);
	if ($bonus == 0) return 0;
	
	$back[0] = "Partnerbonus ".$partnerstats[2][bonus]."";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("Partnerbonus: ".$partnerstats[2][bonus]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Synarmee-Partnerbonus
function display_partner_SynarmeeBonus($tstatus = "",$tpartner = "") {
	$partnerstats = getPartnerStats();
	$back = array();
	
	$bonus = calc_partner_SynarmeeBonus($tstatus,$tpartner);
	if ($bonus == 0) return 0;
	
	$back[0] = "Partnerbonus ".$partnerstats[19][bonus]."";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("Partnerbonus: ".$partnerstats[19][bonus]);
	$back[3] = $bonus;
	$back[4] = "Army";

	return $back;
}


##
##	Artefakte
##

// Schule des Krieges
function display_monument_OffBonus($tstatus = "",$tsyndikat = "") {
	
	global $artefakte;
	if (!is_array($artefakte)) $artefakte = get_artefakte();
	
	//pvar($artefakte);
	
	$back = array();
	$bonus = calc_monument_OffBonus($tstatus,$tsyndikat);
	if ($bonus == 0) return 0;
	
	$back[0] = "".$artefakte[14][name]."";
	$back[1] = "+".$bonus."%";
	$back[2] = getJsHelpTag("<b>Monument ".$artefakte[14][name]."</b><br>".$artefakte[14][bonusdescription]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Große Mauer
function display_monument_DefBonus($tstatus = "",$tsyndikat = "") {
	
	global $artefakte;
	if (!is_array($artefakte)) $artefakte = get_artefakte();
	
	//pvar($artefakte);
	
	$back = array();
	$bonus = calc_monument_DefBonus($tstatus,$tsyndikat);
	if ($bonus == 0) return 0;
	
	$back[0] = "".$artefakte[15][name]."";
	$back[1] = "+".($bonus)."%";
	$back[2] = getJsHelpTag("<b>Monument ".$artefakte[15][name]."</b><br>".$artefakte[15][bonusdescription]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}


##
##	Gebäude
##

// Offtowers
function display_building_OfftowersBonus($tstatus = "") {

	$buildingstats = getbuildingstats();
	
	$back = array();
	$bonus = calc_building_OfftowersBonus($tstatus);
	if ($bonus == 0) return 0;
	
	$back[0] = "".$buildingstats[offtowers][name]."";
	$back[1] = "+".round_percent($bonus)."%";
	$back[2] = getJsHelpTag("<b>".$buildingstats[offtowers][name]."</b><br>".$buildingstats[offtowers][nutzen]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

// Deftowers
function display_building_DeftowersBonus($tstatus = "") {

	$buildingstats = getbuildingstats();
	
	$back = array();
	$bonus = calc_building_DeftowersBonus($tstatus);
	if ($bonus == 0) return 0;
	
	$back[0] = "".$buildingstats[deftowers][name]."";
	$back[1] = "+".round_percent($bonus)."%";
	$back[2] = getJsHelpTag("<b>".$buildingstats[deftowers][name]."</b><br>".$buildingstats[deftowers][nutzen]);
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}

##
##	Fraktionen
##

// BF-Defbonus
function display_race_bfDefBonus($tstatus = "") {

	
	$back = array();
	$bonus = calc_race_bfDefBonus($tstatus);
	if ($bonus == 0) return 0;
	
	$back[0] = "BF-Verteidigungsbonus";
	$back[1] = "+".round_percent($bonus)."%";
	$back[2] = getJsHelpTag("<b>BF-Verteidigungsbonus</b><br>Größenabhängiger Verteidigungsbonus (je ".PBF_DEFENSE_PBFLAND." Land +".PBF_DEFENSE_BONUS_PER_PBFLAND."% Verteidigungsbonus - bis maximal +".PBF_DEFENSE_BONUS_MAX."% auf ".pointit(PBF_DEFENSE_PBFLAND*PBF_DEFENSE_BONUS_MAX)." ha) ");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}


// Bf-Offbonus
function display_race_bfOffBonus($tstatus = "") {

	
	$back = array();
	$bonus = calc_race_bfOffBonus($tstatus);
	if ($bonus == 0) return 0;
	
	$back[0] = "BF-Angriffsbonus";
	$back[1] = "+".round_percent($bonus)."%";
	$back[2] = getJsHelpTag("<b>BF-Angriffsbonus</b><br>+".PBF_ATTACK_BONUS."% Angriffsstärke");
	$back[3] = $bonus;
	$back[4] = "%";

	return $back;
}


// NOF-Defbonus
function display_race_NofMarineBonus($tstatus = "") {

	
	$back = array();
	$bonus = calc_race_NofMarineBonus($tstatus);
	if ($bonus == 0) return 0;
	
	$back[0] = "NOF-Marine Bonus";
	$back[1] = "+".round_percent($bonus)."%";
	$back[2] = getJsHelpTag("<b>NOF-Marine Bonus</b><br>für je ".pointit(NOF_MARINE_HA_BARRIER_FOR_OP_PLUS)." ha im Kampf erobertes Land +1 AP (maximal +".NOF_MARINE_MAX_PLUS_OP." AP ab ".pointit(NOF_MARINE_MAX_PLUS_OP*NOF_MARINE_HA_BARRIER_FOR_OP_PLUS)." ha) ");
	$back[3] = $bonus;
	$back[4] = "Absolute";

	return $back;
}




############################################################################
############################################################################
############################################################################

###
### VARIOUS COMBAT CALCULATION FUNCTIONS
###
// Calc_synarmy_support_total - percent value
function calc_synarmy_support_total($tstatus,$tsciences,$tpartner) {
	
	$support = 0;
	$support += calc_science_SynarmeeBonus($tstatus,$tsciences);
	$support += calc_partner_SynarmeeBonus($tstatus,$tpartner);
	
	return $support;
	
}

// Return number of present offense units
function calc_number_offunits($tstatus="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$unitstats = modify_unitstats(getunitstats($status[race]));
	
	$back = 0;
	foreach ($unitstats as $k => $v) {
		if ($v[op] > 0) $back += $status[$k];
	}
	
	return $back;
	
}


function calc_number_offunits_for_synarmy_support($tstatus="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$numOffUnits = calc_number_offunits($status);
	if ($status["race"] == "nof") {
		$numOffUnits -= $status[elites];
	}
	return $numOffUnits;
	
}

// Return number of defense units
function calc_number_defunits($tstatus="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$unitstats = modify_unitstats(getunitstats($status[race]));
	
	$back = 0;	
	foreach ($unitstats as $k => $v) {
		if ($v[dp] > 0) $back += $status[$k];
	}
	
	return $back;
	
	
}

function calc_number_defunits_for_synarmy_support($tstatus="") {
	list($status,$sciences,$partner,$syndikat) = adapt_params($tstatus,"","","");
	
	$numDefUnits = calc_number_defunits($status);

	if ($status["race"] == "nof") {
		$numDefUnits -= $status[elites];
	}
	return $numDefUnits;
	
}




###
### Helper Functions
###



function modify_unitstats($military_unit_settings) {
	
	global $status;
	// R&M
	$military_unit_settings["offspecs"]["op"] += calc_science_RangerAndMarineBonus();
	$military_unit_settings["defspecs"]["dp"] += calc_science_RangerAndMarineBonus();
	$military_unit_settings["offspecs"]["dp"] += calc_science_RangerAndMarineBonus();
	$military_unit_settings["defspecs"]["op"] += calc_science_RangerAndMarineBonus();
	
	//Fow;
	$military_unit_settings["offspecs"]["dp"] += calc_science_FowBonus();
	$military_unit_settings["defspecs"]["dp"] += calc_science_FowBonus();
	//Änderung Runde 60 FOW gilt nicht bei Carrier - DarkJohn 17.04.2012
	if ($status["race"] != "nof") {
		$military_unit_settings["elites"]["dp"] += calc_science_FowBonus();
	}
	$military_unit_settings["elites2"]["dp"] += calc_science_FowBonus();
	$military_unit_settings["techs"]["dp"] += calc_science_FowBonus();
	
	// RU für nof
	$military_unit_settings["defspecs"]["dp"] += calc_science_RangerUpgradeDefBonus();
	
	// FlexStrat
	$military_unit_settings["offspecs"]["dp"] += calc_science_FlexStratBonus();
	$military_unit_settings["defspecs"]["op"] += calc_science_FlexStratBonus();
	
	// Marine-Bonus für Nof
	$military_unit_settings["offspecs"]["op"] +=calc_race_NofMarineBonus();
	
	
	return $military_unit_settings;
	
}

// ADAPT - PARAMS
// Funktion, um calc-functions auch z.b. für verteidiger oder andere Spieler nutzen zu können
function adapt_params($status_uebergabe="",$sciences_uebergabe="",$partner_uebergabe="", $syndikat_uebergabe="") {
	global $status, $sciences, $partner, $game_syndikat;
	
	$back = array();
	
	// Status
	if (is_array($status_uebergabe)) {
		$back[0] = $status_uebergabe;
	}
	else {
		$back[0] = $status;
	}
	
	// Sciences
	if (is_array($sciences_uebergabe)) {
		$back[1] = $sciences_uebergabe;
	}
	else {
		$back[1] = $sciences;
	}
	
	// Partnerboni
	if (is_array($partner_uebergabe)) {
		$back[2] = $partner_uebergabe;
	}
	else {
		$back[2] = $partner;
	}
	
	// Syndikat --> Artefakte
	if (is_array($syndikat_uebergabe)) {
		$back[3] = $syndikat_uebergabe;
	}
	else {
		$back[3] = $game_syndikat;
	}
	return $back;
	
}

function getJsHelpTag($helptext) {
			global $ripf;

			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" class=\"tableOutline\"><tr><td><table cellspacing=\"0\" cellpadding=\"2\"><tr><td class=\"tableInner1\">".$helptext."</td></tr></table></td></tr></table>";
			return ("<img ".js::showover($overtext)." src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\">");
}

function getJsHelpTagCustom($helptext,$imagename,$addString = "") {
			global $ripf;

			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" class=\"tableOutline\"><tr><td><table cellspacing=\"0\" cellpadding=\"2\"><tr><td class=\"tableInner1\">".$helptext."</td></tr></table></td></tr></table>";
			return ("<img ".js::showover($overtext)."  src=\"".$ripf.$imagename."\" $addString border=0 valign=\"absmiddle\">");
}


function getBigJsHelpTag($helptext) {
			global $ripf;

			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" class=\"tableOutline\"><tr><td><table cellspacing=\"0\" cellpadding=\"2\"><tr><td class=\"tableInner1\">".$helptext."</td></tr></table></td></tr></table>";
			return ("<img ".js::showover($overtext)." src=\"".$ripf."_help_bigger.gif\" border=0 valign=\"absmiddle\">");
}


// Return Array of Display Arrays
function collectDisplayBoni($type="off") {
	// Types:
	// off - Angriffsboni
	// def - Verteidigungsboni
	// synarmy - Synarmee- boni
	// other - Other boni
	
	global $status, $sciences, $partner, $game_syndikat;
	
	$tback = array();
	
	
	//////////////////////
	// Angriffsboni
	//////////////////////
	if ($type == "off") {
	
		$tback[] = display_science_BasicOffenseBonus($status,$sciences);
		$tback[] = display_science_RAOffenseBonus($status,$sciences);
		$tback[] = display_science_IWTOffenseBonus($status,$sciences);
		$tback[] = display_race_BfOffBonus($status,$sciences);
		
		$tback[] = display_partner_OffBonus($status,$partner);
		
		$tback[] = display_monument_OffBonus($status,$game_syndikat);
		
		$tback[] = display_building_OfftowersBonus();
		
	}
	
	//////////////////////
	// Verteidigungsboni
	//////////////////////
	else if ($type == "def") {
		
		$tback[] = display_science_BasicDefenseBonus($status,$sciences);
		$tback[] = display_science_OrbitalDefenseDefBonus($status,$sciences);
		$tback[] = display_science_DefNetDefenseBonus($status,$sciences);
		$tback[] = display_partner_DefBonus($status,$partner);
		
		$tback[] = display_monument_DefBonus($status,$game_syndikat);
		
		$tback[] = display_building_DeftowersBonus($status);
		
		//$tback[] = display_race_bfDefBonus($status);
		//$tback[] = display_race_NofDefBonus($status);
		
	}
	
	//////////////////////
	// Synarmee-Boni
	//////////////////////
	else if ($type == "synarmy") {
		$tback[] = display_science_SynarmeeBonus($status,$sciences);
		$tback[] = display_partner_SynarmeeBonus($status,$partner);
		
		
	}
	
	//////////////////////
	// Restliche Boni die AP/VP bezogen sind
	//////////////////////
	else if ($type == "other") {

		$tback[] = display_science_RangerUpgradeDefBonus($status,$sciences);
		$tback[] = display_science_RangerAndMarineBonus($status,$sciences);
		$tback[] = display_science_FlexStratBonus($status,$sciences);
		$tback[] = display_science_FowBonus($status,$sciences);
		
		//$tback[] = display_race_NofMarineBonus($status);
		
	}

	
	
	
	
	// Nur Arraywerte übernehmen - also auch Boni, die der Spieler tatsächlich hat
	foreach ($tback as $v) {
		if (is_array($v)) {
			$back[] = $v;
		}
	}
	
	return $back;
	
}
?>
