<b><img src="images/dot-gelb.gif" alt="" border="0"> Statistiken</b><br>
<?
//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//
$round > 0 ? $round = round($round) : $round = $globals['round'];

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//
//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$ridname = "";

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

$user_id = $userdata[id];
$startround = $userdata[startround];
$globals = assoc("select * from globals order by round desc limit 1");

$origround=$globals{'round'};


if (!$round or $round < $startround or $round > $globals['round'] or $round == 13 or $round == 14 or $round == 15) {$round = $globals{'round'};}
$round = (int)$round;

if ($globals[roundstatus] == 0) {
	$dontshowakt = 1;
	if ($round >= $globals[round]) {
		$round = $globals[round]-1;
	}
	if ($startround == $globals[round]) {
		$nostats = 1;
	}
}

$stats = getstats($user_id,$round);
while(!is_array($stats) && $round > 0) {
	$round--;
	$stats = getstats($user_id,$round);
}
$races = assocs("select * from races","race");
if ($round > 1 && !$nostats && $stats[rid]) {
    $syndtable = "syndikate";
    if ($round < $origround || !$userdata[konzernid]) {
        $syndtable .="_round_".$round;
    }

    $ridname = single("select name from ".$syndtable." where synd_id = ".$stats{rid});
}


//							Berechnungen									//

// Statistiken der letzten Runden f?r aktuellen spieler ermitteln
$a = 1;

while ($a < $origround && !$nostats) {
    $tempstats = getstats($user_id,$a);

    if ($tempstats{round} == $a) {
        if (!$oldstats) {
            $oldstats ="<br><font color=\"white\" class=\"ver12w\"><u>Statistiken zu fr&uuml;heren Runden:</u></font><br><br>";
        }
        # links zu den statistiken der letzten runde(n)
        $ag = 0;
        if ($a == 1) {$ag = "Beta1";}
        if ($a == 2) {$ag = "Beta2";}
        if ($a > 2) {$ag = $a-2;}
        $oldstats.="<a class=\"gelblink11\" href=\"index.php?action=stats&round=$a\">Statistiken f&uuml;r Runde $ag</a><br>";
    }
    $a++;
} //while $a < $round
if ($oldstats &&!$dontshowakt) {
    $oldstats.="<a class=\"gelblink11\" href=\"index.php?action=stats&round=$origround\">Aktuelle Runde</a><br>";
}



#####################################################################
if (!$nostats) {
	if ($round == $origround) {
		$from = "status";
	}
	else {
		$from = $globals[statstable];
		$where = "round=$round and";
	}
	$players = assocs("select count(*) as number,race from $from where $where alive > 0 group by race","race");
	$uicplayers = $players{uic}{number};
	$pbfplayers = $players{pbf}{number};
	$slplayers = $players{sl}{number};
	$nebplayers = $players{neb}{number} ? $players[neb][number] : 0;
	$nofplayers = $players{nof}{number} ? $players[nof][number] : 0;

	$totalplayers = $uicplayers+$slplayers+$pbfplayers+$nebplayers+$nofplayers;
	if ($totalplayers > 0) {
		$uicrel=sprintf("%.1f", ($uicplayers/$totalplayers * 100));
		$pbfrel=sprintf("%.1f", ($pbfplayers/$totalplayers * 100));
		$slrel=sprintf("%.1f", ($slplayers/$totalplayers * 100));
		$nebrel=sprintf("%.1f", ($nebplayers/$totalplayers * 100));
		$nofrel=sprintf("%.1f", ($nofplayers/$totalplayers * 100));
	}

	// mehrlink
	$mehrlink = "<a class=\"gelblink11\" href=\"index.php?action=fame&round=$round\" target=\"_blank\"><i>Details</i></a>";

	// Rundenausgabe oben
	$roundshow = $round -2;
	if ($round == 1) {$roundshow="Beta1";}
	else if ($round ==2) {$roundshow="Beta2";}
	if ($round == $origround) {$roundausgabe = "Aktuelle Runde";} else {$roundausgabe="&Uuml;bersicht Runde ".$roundshow." ".$mehrlink;}



//							Daten schreiben									//

//							Ausgabe     									//

	$ausgabe .= "
		<center>
		<font class=normal><br>
	";

	if ($oldstats) {
		$ausgabe.="
			<!-- ### STATS , AlTE RUNDEN RELATION ###  -->
			<table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" align=\"center\" class=\"back\">
				<tr>
					<td width=\"400\" align=\"center\">
		";
	}

	$ausgabe.="
	<!-- ### STATSTABLE ###  -->
	<br>
	<table width=\"400\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\" class=\"rand\" align=\"center\">
		<tr>
			<td>
				<table width=\"400\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" class=head>
					<tr>
						<td width=\"400\" colspan=\"2\" align=\"center\"><b>".$roundausgabe."</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=subhead>
					<tr>
						<td width=\"400\" colspan=\"2\" align=\"center\">Allgemein</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table cellspacing=0 cellpadding=3 class=bodys width=100%>
					<tr>
						<td width=\"170\"><b>".$races{uic}{shortname}."</b></td>
						<td width=\"130\"><b>".pointit($uicplayers)."</b>&nbsp;&nbsp;($uicrel%)</font></td>
					</tr>
					<tr>
						<td><b>".$races{sl}{shortname}."</b></td>
						<td><b>".pointit($slplayers)."</b>&nbsp;&nbsp;($slrel%)</font></td>
					</tr>
					<tr>
						<td><b>".$races{pbf}{shortname}."</b></td>
						<td><b>".pointit($pbfplayers)."</b>&nbsp;&nbsp;($pbfrel%)</font></td>
					</tr>
					<tr>
						<td><b>".$races{neb}{shortname}."</b></td>
						<td><b>".pointit($nebplayers)."</b>&nbsp;&nbsp;($nebrel%)</font></td>
					</tr>
					<tr>
						<td><b>".$races{nof}{shortname}."</b></td>
						<td><b>".pointit($nofplayers)."</b>&nbsp;&nbsp;($nofrel%)</font></td>
					</tr>
					<tr>
						<td><b>Spieler Gesamt</b></td>
						<td><b>".pointit($totalplayers)."</b></font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=subhead width=\"400\">
					<tr>
						<td  colspan=\"2\" align=\"center\">Konzern</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=bodys width=\"400\">
					<tr>
						<td width=\"170\" align=\"left\"><b>Konzernchef</b></td>
						<td width=\"130\" align=\"left\">".$stats{rulername}."</font></td>
					</tr>
					<tr>
						<td><b>Konzernname</b></td>
						<td>".$stats{syndicate}."</font></td>
					</tr>
					<tr>
						<td><b>Fraktion</b></td>
						<td>";
						$ausgabe.= $races{$stats{race}}{name};
					$ausgabe.="</font></td>
					</tr>
					<tr>
						<td><b>Syndikat</b></td>
						<td>".(($globals[roundstatus] == 1 or $globals['round'] != $round) ? $ridname." (#".$stats{rid}.")": "Runde noch nicht gestartet")."</font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=subhead>
					<tr>
						<td width=\"400\" colspan=\"2\" align=\"center\">Statistik</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=bodys cellpadding=4 cellspacing=0 width=100%>
					<tr>
						<td width=\"100%\" colspan=2 align=\"left\"><b>Angriffe (normale,belagerung,eroberung,spionezerstören):</b></td>
					</tr>
					<tr>
						<td width=75><b>ausgeführt:</b></td>
						<td width=\"300\" align=\"left\">
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_numberdone_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numberdone_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numberdone_conquer]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numberdone_killspies]).")
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>davon erfolgreich</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_numberdone_won_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numberdone_won_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numberdone_won_killspies]).",
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>bestes Ergebnis</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_largest_won_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_won_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_won_conquer]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_won_killspies]).")
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width=75><b>insgesamt erobert</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_total_won_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_won_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_won_conquer]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_won_killspies]).")
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>erlittene Angriffe</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_numbersuffered_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numbersuffered_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numbersuffered_conquer]).",
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>davon verloren</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_numbersuffered_lost_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numbersuffered_lost_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_numbersuffered_lost_conquer]).",
									</td>
									<td width=75>
										0)
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>größter Verlust</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_largest_loss_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_loss_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_loss_conquer]).",
									</td>
									<td width=75>
										".pointit($stats[attack_largest_loss_waraffected]).")
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>gesamt Verlust</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										(".pointit($stats[attack_total_loss_normal]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_loss_siege]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_loss_conquer]).",
									</td>
									<td width=75>
										".pointit($stats[attack_total_loss_waraffected]).")
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>ausgef&uuml;hrte Spionageaktionen </b></td>
						<td>".pointit($stats{spyopsdone})."</font></td>
					</tr>
					<tr>
						<td><b>davon erfolgreich</b></td>
						<td>".pointit($stats{spyopsdonewon})." (".prozent(($stats{spyopsdonewon}*100/($stats{spyopsdone} ? $stats{spyopsdone} : 1)))."%)</font></td>
					</tr>
					<tr>
						<td><b>Spione verloren</b></td>
						<td>".pointit($stats{spies_lost})."</font></td>
					</tr>
					<tr>
						<td><b>Ressourcen gestohlen</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=75>
										Cr:".pointit($stats[moneystolen]).",
									</td>
									<td width=75>
										MWh:".pointit($stats[energystolen])."
									</td>
								</tr>
								<tr>
									<td width=75>
										t:".pointit($stats[metalstolen]).",
									</td>
									<td width=75>
										P:".pointit($stats[sciencepointsstolen])."
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>Beste Ergebnisse:</b></td>
						<td>
							<table width=75% class=bodys align=left>
								<tr>
									<td width=150>
										Cr:".pointit($stats[max_steal_money]).",
									</td>
									<td width=150>
										MWh:".pointit($stats[max_steal_energy])."
									</td>
								</tr>
								<tr>
									<td width=150>
										t:".pointit($stats[max_steal_metal]).",
									</td>
									<td width=150>
										P:".pointit($stats[max_steal_sciencepoints])."
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><b>erlittene Spionageaktionen</b></td>
						<td>".pointit($stats{spyopssuffered})."</font></td>
					</tr>
					<tr>
						<td><b>davon verloren</b></td>
						<td>".pointit($stats{spyopssufferedlost})." (".prozent(($stats{spyopssufferedlost}*100/($stats{spyopssuffered} ? $stats{spyopssuffered} : 1)))."%)</font></td>
					</tr>
					<tr>
						<td><b>Spione exekutiert</b></td>
						<td>".pointit($stats{spies_executed})."</font></td>
					</tr>
					<tr>
						<td><b>Land gekauft</b></td>
						<td>".pointit($stats{landexplored})."</font></td>
					</tr>
					<tr>
						<td><b>Gr&ouml;&szlig;te Konzernst&auml;rke</b></td>
						<td>".pointit($stats{largestnetworth})."</font></td>
					</tr>
					<tr>
						<td><b>Gr&ouml;&szlig;ter Grundbesitz</b></td>
						<td>".pointit($stats{largestland})." Land</font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=foot width=\"400\">
					<tr>
						<td  colspan=\"2\" align=\"center\">&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	";



	if ($oldstats) {
		$ausgabe.="
		</td>
		<td valign=\"top\" width=\"200\">
			<table class=back width=75% align=center>
				<tr>
					<td>
						<center>
						$oldstats";
						$ausgabe.="
						</center>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>";
	}
} // if ! nostats
else {
	echo "<br>Für diesen Account sind noch keine Statistiken verfügbar";
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

echo ($ausgabe);
	$ausgabe = "";


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


###########################################################
### Sub f?r statsholen, 1. Argument id, 2. argument runde##
###########################################################

function getstats($user_id_intern) {

    global $origround;
	global $startround;
    global $globals;
    $round_intern = $origround;
    if (func_num_args() > 1) {$round_intern = func_get_arg (1);}
	if ($round_intern < $startround) {$round_intern = $startround;}
	if ($round_intern <= $origround) {
		$stats1 = assoc("select * from $globals[statstable] where round=$round_intern and user_id=".$user_id_intern);
	}
    return $stats1;
}


?>
