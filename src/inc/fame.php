
<b><? echo "$hofdot"; ?> Hall of Fame</b><br>
<?
$globals= assoc("select * from globals order by round desc limit 1");


// Datenbankverbindung herstellen
$query = mysql_query("select round,roundstatus from globals order by round desc limit 1");
list($lastround,$roundstatus) = mysql_fetch_array($query);
$round = floor($round);
// Wenn keine Runde übergeben wurde, Liste ausgeben:
if (! $round) {
	echo "<center>";
	$i=1;
	while ($i < $lastround || ($i == $lastround && $roundstatus == 2)) {
	$zeigerunde = "Runde ".($i-2);
	if ($i == 1) {$zeigerunde = "Beta 1";}
	if ($i == 2) {$zeigerunde = "Beta 2";}
	print"<br><a class=\"gelblink\" href=\"index.php?action=fame&round=$i\">Ergebnisse $zeigerunde</a>";
	$i++;
}
echo "</center>"; }
?>


<?

// wenn runde übergeben wurde, spezifische ausgabe:
if ($round && !$show && !$details) {
	$result = single("select count(*) from $globals[statstable] where round=$round and alive > 0");
	if (($result > 0 && $round < $lastround) || ($round == $lastround && $roundstatus == 2 && $result)) {
		?>
		<center>
			<table>
				<tr>
					<td></td><td align=center class="normal">Top 100</td><td></td>
				</tr>
				<tr><td colspan=3><br></td></tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=synd&show2=land\">Die größten Syndikate</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=synd&show2=nw\">Die stärksten Syndikate</a>"; ?></td>
				</tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=all&show2=land\">Die größten Konzerne</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=all&show2=nw\">Die stärksten Konzerne</a>"; ?></td>
				</tr>
				<tr><td colspan=3><br></td></tr>
				<tr>
					<td></td><td align=center class="normal">Nach Konzerntypen</td><td></td>
				</tr>
				<tr><td colspan=3><br></td></tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=uic&show2=land\">United Industries Corporation</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=uic&show2=nw\">United Industries Corporation</a>"; ?></td>
				</tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=sl&show2=land\">Shadow Labs</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=sl&show2=nw\">Shadow Labs</a>"; ?></td>
				</tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=pbf&show2=land\">Brute Force</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=pbf&show2=nw\">Brute Force</a>"; ?></td>
				</tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=neb&show2=land\">New Economic Block</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=neb&show2=nw\">New Economic Block</a>"; ?></td>
				</tr>
				<tr class="gelb12">
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=nof&show2=land\">Nova Federation</a>"; ?></td>
					<td></td>
					<td><? print "<a class=\"gelblink\" href=\"index.php?action=fame&round=".$round."&show=nof&show2=nw\">Nova Federation</a>"; ?></td>
				</tr>
				<tr><td colspan=3><br></td></tr>
				<tr><td colspan=3><br></td></tr>

				<tr class="normal">
					<td></td>
					<td>Konzerntypen</td>
					<td></td>
				</tr>
				<tr><td colspan=3><br></td></tr>
				<tr>
					<td align="center" class="normal">United Industries Corporation</td>
					<td  align="center" class="normal">Shadow Labs</td>
					<td  align="center" class="normal">Brute Force</td>
				</tr>
				<?
					$all1 = mysql_query("select count(syndicate) from $globals[statstable] where round=$round and   alive > 0");
					$all2 = mysql_fetch_row($all1);
					$all = $all2[0];

					$uic1 = mysql_query("select count(race) from $globals[statstable] where round=$round and   race='uic' and alive > 0");
					$uic2 = mysql_fetch_row($uic1);
					$uic = $uic2[0];

					$sl1 = mysql_query("select count(race) from $globals[statstable] where round=$round and   race ='sl' and alive > 0");
					$sl2 = mysql_fetch_row($sl1);
					$sl = $sl2[0];

					$pbf1 = mysql_query("select count(race) from $globals[statstable] where round=$round and   race='pbf' and alive > 0");
					$pbf2 = mysql_fetch_row($pbf1);
					$pbf = $pbf2[0];

					$neb1 = mysql_query("select count(race) from $globals[statstable] where round=$round and   race='neb' and alive > 0");
					$neb2 = mysql_fetch_row($neb1);
					$neb = $neb2[0];
					
					$nof1 = mysql_query("select count(race) from $globals[statstable] where round=$round and   race='nof' and alive > 0");
					$nof2 = mysql_fetch_row($nof1);
					$nof = $nof2[0];
					

					if ($all > 0) {
						$uicproz = $uic / $all*100;
						$pbfproz = $pbf / $all * 100;
						$slproz = $sl / $all * 100;
						$nebproz = $neb / $all * 100;
						$nofproz = $nof / $all * 100;
					}
				?>
				<tr>
					<td align="center" class="normal"><? printf ("%.2f",$uicproz); ?>%</td>
					<td align="center" class="normal"><? printf ("%.2f",$slproz); ?>%</td>
					<td align="center" class="normal"><? printf ("%.2f",$pbfproz); ?>%</td>
				</tr>
				<? if (true || $nebproz > 0) { ?>
				<tr>
					<td  align="center" class="normal"><br>New Economic Block</td>
					<td  align="center" class="normal"><br>Nova Federation</td>
					<td></td>
				</tr>
				<tr>
					<td align="center" class="normal"><? printf ("%.2f",$nebproz); ?>%</td>
					<td align="center" class="normal"><? printf ("%.2f",$nofproz); ?>%</td>
					<td align="center" class="normal"></td>
				</tr>
				<? } ?>

				<tr><td colspan=3><br></td></tr>
				<tr><td colspan=3><br></td></tr>
				<tr class="normal">
					<td></td>
					<td>Sonstiges</td>
					<td></td>
				</tr>
					<tr><td colspan=3><br></td></tr>
					<tr class="normal">
					<td>Spieler gesamt</td>
					<td></td>
					<td>
					<? print pointit($all); ?></td>
				</tr>

				<tr class="normal">
					<td>Angriffe Insgesamt</td>
					<td></td>
					<td>
						<?
							$attacks = single("select sum(attack_numberdone_normal)+ sum(attack_numberdone_siege)+ sum(attack_numberdone_conquer) from $globals[statstable] where round=$round and   alive > 0");
							echo pointit($attacks);
						?>
					</td>
				</tr>
				<tr class="normal">
					<td>Spionageaktionen insgesamt</td>
					<td></td>
					<td>
						<?
							$handle2 = mysql_query("select sum(spyopsdone) from $globals[statstable] where round=$round and   alive > 0");
							$handle1 = mysql_fetch_row($handle2);
							$handle = $handle1[0];
							echo pointit($handle);
						?>
					</td>
				</tr>
				<tr class="normal">
				<td>Land gekauft insgesamt</td>
				<td></td>
				<td>
					<?
						$handle2 = mysql_query("select sum(landexplored) from $globals[statstable] where round=$round and   alive > 0");
						$handle1 = mysql_fetch_row($handle2);
						$handle = $handle1[0];
						echo pointit($handle);
					?>
				</td>
				</tr>
				<tr class="normal">
				<td>Durchschnittliche Konzernstärke</td>
				<td></td>
				<td>
					<?
						$handle2 = mysql_query("select avg(lastnetworth) from $globals[statstable] where round=$round and   alive > 0");
						$handle1 = mysql_fetch_row($handle2);
						$handle = $handle1[0];
						print pointit((int)($handle));
						//printf ("%.0f",$handle);
					?>
				</td>
				</tr>
				<tr class="normal">
				<td>Durchschnittliche Konzerngröße</td>
				<td></td>
				<td>
					<?
						$handle2 = mysql_query("select avg(lastland) from $globals[statstable] where round=$round and   alive > 0");
						$handle1 = mysql_fetch_row($handle2);
						$handle = $handle1[0];
						print pointit((int)($handle));
						//printf ("%.0f",$handle);
					?>
				</td>
				</tr>
			</table>
		</center>



		<?
	} // if $result
	if (!$result) {
		echo "<br><br>Für diese Runde existieren noch keine Ergebnisse";
	}
	// ende if $round
	?>
	<?
}


if ($round && $show && $show2) {

	$result = mysql_query("select syndicate from $globals[statstable] where round=$round and alive > 0");

	if ($result && $round < $lastround || ($round == $lastround && $roundstatus == 2 && $result)) {

		if ($show == "synd" && $show2 == "nw") {
		$what = "rid,sum(lastland),sum(lastnetworth)";
		$rest ="where alive > 0 and round=$round and isnoob = 0  group by rid order by sum(lastnetworth) desc ";
											}

		elseif ($show == "synd" && $show2 == "land") {
		$what = "rid,sum(lastland),sum(lastnetworth)";
		$rest ="where alive > 0 and round=$round and isnoob = 0  group by rid order by sum(lastland) desc ";
											}

		elseif ($show == "all" && $show2 == "nw") {
		$what = "rid,lastland,lastnetworth,syndicate,race";
		$rest ="where alive > 0 and round=$round and isnoob = 0  order by lastnetworth desc ";
											}

		elseif ($show == "all" && $show2 == "land") {
		$what = "rid,lastland,lastnetworth,syndicate,race";
		$rest ="where alive > 0 and round=$round and isnoob = 0  order by lastland desc ";
											}

		elseif ($show2 == "land") {
		$what = "rid,lastland,lastnetworth,syndicate";
		$rest ="where race='$show' and alive > 0  and round=$round and isnoob = 0 order by lastland desc ";
											}

		elseif ($show2 == "nw") {
		$what = "rid,lastland,lastnetworth,syndicate";
		$rest ="where race='$show' and alive > 0  and round=$round and isnoob = 0 order by lastnetworth desc ";
											}

		$query ="select $what from $globals[statstable] $rest limit 100";
		
		
		
		?>
		<table align=center>
			<tr class="gelb12">
			<td></td>
			<? if ($show == "all") {echo "<td></td>";} ?>
			<td><?
			if ($show2 == "land") {$what ="größten";}
			else {$what = "stärksten";}

			if ($show == "synd") {$who ="Syndikate";}
			elseif ($show == "all") {$who="Konzerne";}
			elseif ($show == "uic") {$who="United Industries Konzerne";}
			elseif ($show == "pbf") {$who="Brute Force Konzerne";}
			elseif ($show == "sl") {$who="Shadow Labs Konzerne";}
			elseif ($show == "neb") {$who="New Econimic Block Konzerne";}
			elseif ($show == "nof") {$who="Nova Federation Konzerne";}
			if ($who =="Syndikate") {
				$syndtable = "syndikate_round_";
				$syndtable.=$round;
				if ($round > 1) {
					$syndquery=("select synd_id,name from $syndtable ");
					$syndhandle = mysql_query($syndquery);
				}
			}
			echo "Die ".$what." ".$who;
			?></td>
			<td></td>
			</tr>
			<tr>
			<? if ($show == "all") {echo "<td></td>";} ?>
			<td colspan=3><br></td></tr>
			<tr class="gelb12">
			<? if($show != "synd") { echo"<td align=\"center\">Konzern</td>";} ?>
			<? if($show == "synd") { echo"<td align=\"center\">Syndikat</td>";} ?>
			<? if ($show == "all") {echo "<td aligh=\"center\">Fraktion</td>";} ?>
			<td align="center">Land</td>
			<td align="center">Networth</td>
			</tr>
			<tr>
			<? if ($show == "all") {echo "<td></td>";} ?>
			<td colspan=3><br></td></tr>
			<?


			$handle2 = mysql_query($query);

			if ($round > 1 && $show == "synd") {
				$synddaten = array();
				$m=0;
				while (@$sreturn = mysql_fetch_row($syndhandle)) {
					$synddaten[$sreturn[0]] = $sreturn[1];
					$m++;
				}
				$m=0;
			}

			$i=1;
			$tempname="";
			while ($handle1 = mysql_fetch_row($handle2)) {
				if ($show == "synd" && $handle1[0] > 0 && $handle1[1] > 0 && $handle1[2] > 0) {
				if ($round > 1) {$tempname=$synddaten[$handle1[0]];}

				echo "<tr class=\"normal\" align=\"center\"><td>$tempname (#$handle1[0])</td><td>".pointit($handle1[1])."</td><td>".pointit($handle1[2])."</td></tr>";
				$tempname="";
				}

				elseif ($show && $handle1[0] > 0 && $handle1[1] > 0 && $handle1[2] > 0) {$code = urlencode($handle1[3]);
				echo "<tr class=\"normal\" align=\"left\"><td>".$i.".&nbsp;&nbsp;<a class=\"gelblink\"  href=\"index.php?action=fame&round=$round&save1=$show&save2=$show2&details=$code\">".$handle1[3]."</a> (#".$handle1[0].")</td>"; if ($show == "all") {
			if ($handle1[4] == "uic") {$race = "UIC";}
			elseif ($handle1[4] == "pbf") {$race = "Bf";}
			elseif ($handle1[4] == "sl") {$race = "Sl";}
			elseif ($handle1[4] == "neb") {$race = "NEB";}
			elseif ($handle1[4] == "nof") {$race = "NoF";}
			echo "<td align=\"center\">$race</td>";}
			echo "<td align=\"center\">".pointit($handle1[1])."</td><td align=\"center\">".pointit($handle1[2])."</td></tr>";
				$i++;
				}
			}
			?>
		</table>

<?
	}
	else {
		echo "Keine Daten Vorhanden.";
	}
	// if round & show &..
}

if ($round && $details && $round < $globals[round]) {
	$stats = assoc("select * from stats where round=$round and syndicate='$details'");
	if (is_array($stats)) {
		statsausgabe($stats);
	}
	else {
		echo "Zu diesem Spieler wurden keine Statistiken gefunden";
	}
	echo"<br><br><a class=\"gelblink\" href=\"index.php?action=fame&round=$round&show=$save1&show2=$save2\">Zurück</a>";
}
elseif ($round && $details) {
	echo "<br>Zu diesem Spieler wurden für diese Runde keine Statistiken gefunden";
}

//mysql_close ($dbh);
if ($show) {
echo"<br><br><a class=\"gelblink\" href=\"index.php?action=fame&round=$round\">Zurück</a>";}
?>








<?
// DIES FUNKTION HIER NICHT MODIFIZIEREN,SONDERN AUS STATS.PHP REINKOPIEREN!
function statsausgabe($stats) {
	global $globals;
	global $round;
	$races = assocs("select * from races","race");
	$players = assocs("select count(*) as number,race from stats where round=$round and alive > 0 and isnoob=0 group by race","race");
	$uicplayers = $players{uic}{number}? $players[uic][number] : 0;
	$pbfplayers = $players{pbf}{number}? $players[pbf][number] : 0;
	$slplayers = $players{sl}{number}? $players[sl][number] : 0;
	$nebplayers = $players{neb}{number} ? $players[neb][number] : 0;
	$nofplayers = $players{nof}{number} ? $players[nof][number] : 0;
	if ($round == 1) {$round = "Beta1";}
	if ($round == 2) {$round = "Beta2";}
	if ($round > 2) {$round -=2;}

	$totalplayers = $uicplayers+$slplayers+$pbfplayers;
	$uicrel=sprintf("%.1f", ($uicplayers/$totalplayers * 100));
	$pbfrel=sprintf("%.1f", ($pbfplayers/$totalplayers * 100));
	$slrel=sprintf("%.1f", ($slplayers/$totalplayers * 100));
	$nebrel=sprintf("%.1f", ($nebplayers/$totalplayers * 100));
	$nofrel=sprintf("%.1f", ($nofplayers/$totalplayers * 100));

	$ausgabe.="<br><br>
	<table width=\"400\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\" class=\"rand\" align=\"center\">
		<tr>
			<td>
				<table width=\"400\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" class=head>
					<tr>
						<td width=\"400\" colspan=\"2\" align=\"center\"><b>Statistiken zu ".$stats[syndicate]." (#".$stats[rid].") Runde ".$round."</b></td>
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
						<td><b>".$races{neb}{shortname}."</b></td>
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
						<td width=\"100%\" colspan=2 align=\"left\"><b>Angriffe (normale,belagerung,eroberung,im krieg):</b></td>
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
										".pointit($stats[attack_numberdone_waraffected]).")
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
										".pointit($stats[attack_numberdone_won_conquer]).",
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
										".pointit($stats[attack_largest_won_waraffected]).")
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
										".pointit($stats[attack_total_won_waraffected]).")
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
	echo $ausgabe;
}




