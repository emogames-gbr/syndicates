<?

echo "<a href=\"javascript:back()\">Zurück</a>";

$time = time();
$timelimit = $time - 60*60*24*3; // 3 Tage nicht eingelogt gilt noch als aktiv

$globals = assoc("select * from globals order by round desc limit 1");

select("use syndicates;");
$userc = assocs("select id, emogames_user_id, startround, lastroundplayed, k_userkey from users", "id");
$statsc = array();
$statsc_raw = assocs("select user_id, round from stats where alive > 0");
	foreach ($statsc_raw as $ky => $vl) {
		$statsc[$vl['user_id']][$vl['round']] = 1;
	}
	unset($statsc_raw);
	
select("use syndicates_basic;");
$userb = assocs("select id, emogames_user_id, startround, lastroundplayed, k_userkey from users", "id");
$statsb = array();
$statsb_raw = assocs("select user_id, round from stats where alive > 0");
	foreach ($statsb_raw as $ky => $vl) {
		$statsb[$vl['user_id']][$vl['round']] = 1;
	}
	unset($statsb_raw);


$anzahl_spieler_mit_erstem_konzern_auf_classic = array();
$anzahl_spieler_mit_erstem_konzern_auf_basic = array();
$anzahl_weitere_runde_gespielt_classic = array();
$anzahl_weitere_runde_gespielt_basic = array();
$spieler_zurzeit_auf_classic = array();
$spieler_zurzeit_auf_basic = array();
$spieler_von_basic_in_folgerunde_auf_classic_gewechselt = array();
$spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt = array();
$krawall_user = array();
$rounds_played_total = array();
foreach ($userc as $id => $udata) {
	if ($udata['lastroundplayed'] or $userb[$id]['lastroundplayed']) $totalplayers[$udata['startround']]++;
	$rounds_played_total[$udata['startround']] += count($statsc[$id]) + count($statsb[$id]);
	$isK = ($userb[$id]['k_userkey'] ? 1 : 0);
	$krawall_user[$udata['startround']] += ($isK ? 1 : 0);
	if ($statsc[$id])
	foreach ($statsc[$id] as $round => $trash) {
		if ($round == $udata['startround']) {
			$anzahl_spieler_mit_erstem_konzern_auf_classic[$round]++;
			$isK = 0;
			if ($statsb[$id][$round+1] or $statsc[$id][$round+1])
				$anzahl_weitere_runde_gespielt_classic[$round]++;
			
				if ($udata['lastroundplayed'] == $globals['round'])
					$spieler_zurzeit_auf_classic['classic'][$udata['startround']]++;
			
				if ($userb[$id]['lastroundplayed'] == $globals['round'])
					$spieler_zurzeit_auf_basic['classic'][$udata['startround']]++;
			break;
		}
	}
	if ($statsb[$id])
	foreach ($statsb[$id] as $round => $trash) {
		if ($round == $udata['startround'])	{
			$anzahl_spieler_mit_erstem_konzern_auf_basic[$round]++;
				if ($udata['lastroundplayed'] == $globals['round'])
					$spieler_zurzeit_auf_classic[$isK]['basic'][$udata['startround']]++;
			
				if ($userb[$id]['lastroundplayed'] == $globals['round'])
					$spieler_zurzeit_auf_basic[$isK]['basic'][$udata['startround']]++;
				if ($statsc[$id][$round+1])
					$spieler_von_basic_in_folgerunde_auf_classic_gewechselt[$isK][$round]++;
				if ($statsc[$id][$round+2])
					$spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[$isK][$round]++;

				if ($statsb[$id][$round+1] or $statsc[$id][$round+1])
				$anzahl_weitere_runde_gespielt_basic[$isK][$round]++;
					
			break;
		}
	}
}


$classic_colour = "red";
$basic_colour = "blue";
$koins_colour = "green; style=\"font-size:smaller; color: green\"";

$headline = "		<tr>
			<td>Runde</td>
			<td>Gesamtanmeldungen<br>(Prozentsatz erstes Rundenende erlebt [Stats-Table-Eintrag])</td>
			<td>Anzahl Spieler mit erstem Konzern bei Rundenende<br><font color=$basic_colour>auf Basic</font> [davon <font color=$koins_colour>KOINS-User</font>]<br><font color=$classic_colour>auf Classic</font></td>
			<td>Wieviele Spieler davon spielen zur Zeit auf dem Classic?</td>
			<td>Wieviele auf dem Basic?</td>
			<td>Wieviele haben eine weitere Runde gespielt und lebend beendet?</td>
			<td>Wieviele Anfänger-Spieler sind vom Basic auf den Classic gewechselt in der<br><font color=orange>Folgerunde</font><br><font color=purple>Folge-Folge-Runde</font></td>
			<td>Wieviele Runden haben die Spieler seither im Schnitt gespielt?</td>
		</tr>";



$lines = array();
for ($round = 18; $round <= $globals['round']; $round++) {
	$cells = array();
	$cells[] = ($round-2).($round == 25 ? " db cr a sh" : "");
	
	$cells[] = $totalplayers[$round]." (".(round(($anzahl_spieler_mit_erstem_konzern_auf_classic[$round] + $anzahl_spieler_mit_erstem_konzern_auf_basic[$round]) / $totalplayers[$round] * 1000)/10)."%) [".(round(($anzahl_weitere_runde_gespielt_basic[0][$round] + $anzahl_weitere_runde_gespielt_basic[1][$round] + $anzahl_weitere_runde_gespielt_classic[$round]) / $totalplayers[$round] * 1000)/10)."%]";
	
	$cells[] = "<font color=$basic_colour>".$anzahl_spieler_mit_erstem_konzern_auf_basic[$round]."</font> <font color=$koins_colour>(".$krawall_user[$round]." [".(round($krawall_user[$round] / $anzahl_spieler_mit_erstem_konzern_auf_basic[$round]*100))."%])</font><br>"."<font color=$classic_colour>".$anzahl_spieler_mit_erstem_konzern_auf_classic[$round]."</font>";
	
	$cells[] = "
		<font color=$basic_colour>".$spieler_zurzeit_auf_classic[0]['basic'][$round]."</font>
			+
		<font color=$koins_colour>".$spieler_zurzeit_auf_classic[1]['basic'][$round]." [".(round($spieler_zurzeit_auf_classic[1]['basic'][$round] / ($spieler_zurzeit_auf_classic[1]['basic'][$round] + $spieler_zurzeit_auf_classic[0]['basic'][$round]) * 100))."%]</font>

		<br><font color=$classic_colour>".$spieler_zurzeit_auf_classic['classic'][$round]."</font>";
	
	$cells[] = "
		<font color=$basic_colour>".$spieler_zurzeit_auf_basic[0]['basic'][$round]."</font>
			+
		<font color=$koins_colour>".$spieler_zurzeit_auf_basic[1]['basic'][$round]." [".(round($spieler_zurzeit_auf_basic[1]['basic'][$round] / ($spieler_zurzeit_auf_basic[1]['basic'][$round] + $spieler_zurzeit_auf_basic[0]['basic'][$round]) * 100))."%]</font>

		<br><font color=$classic_colour>".$spieler_zurzeit_auf_basic['classic'][$round]."</font>";

	$cells[] = "
		<font color=$basic_colour>".$anzahl_weitere_runde_gespielt_basic[0][$round]." [".round($anzahl_weitere_runde_gespielt_basic[0][$round] / (($anzahl_spieler_mit_erstem_konzern_auf_basic[$round]-$krawall_user[$round])) * 100)."%]</font>
			+
		<font color=$koins_colour>".$anzahl_weitere_runde_gespielt_basic[1][$round]." [".(round($anzahl_weitere_runde_gespielt_basic[1][$round] / ($krawall_user[$round])*100))."%]</font>

		<br><font color=$classic_colour>".$anzahl_weitere_runde_gespielt_classic[$round]." [".(round($anzahl_weitere_runde_gespielt_classic[$round] / $anzahl_spieler_mit_erstem_konzern_auf_classic[$round] * 100))."%]</font>";
	
	$cells[] = "
		<font color=orange>".$spieler_von_basic_in_folgerunde_auf_classic_gewechselt[0][$round]."</font>
			+
		<font color=$koins_colour>".$spieler_von_basic_in_folgerunde_auf_classic_gewechselt[1][$round]." [".(round($spieler_von_basic_in_folgerunde_auf_classic_gewechselt[1][$round] / ($spieler_von_basic_in_folgerunde_auf_classic_gewechselt[1][$round] + $spieler_von_basic_in_folgerunde_auf_classic_gewechselt[0][$round]) * 100))."%]</font>

		<br><font color=purple>".$spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[0][$round]."</font>
			+
		<font color=$koins_colour>".$spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[1][$round]." [".(round($spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[1][$round] / ($spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[1][$round] + $spieler_von_basic_in_folgefolgerunde_auf_classic_gewechselt[0][$round]) * 100))."%]";
	
	$cells[] = round($rounds_played_total[$round] / ($anzahl_spieler_mit_erstem_konzern_auf_classic[$round] + $anzahl_spieler_mit_erstem_konzern_auf_basic[$round]) * 10)/10;
	

	
	$lines[] = "<tr><td>".join("</td><td>", $cells)."</td></tr>";
	if ($round % 5 == 0) $lines[] = $headline;
}



echo "
	Hinweis: Beim Prozentsatz tatsächlich gespielt in der 2. Spalte werden auch Spieler berücksichtigt, die bis Rundenende wg. Inaktivität noch nicht gelöscht wurden, aber trotzdem nicht mehr zu Ende gespielt haben.

	Die Statistiken für die Runden < 18 sind leider unkorrekt und werden daher nicht angezeigt.<br><br>
	<table border=1 align=\"center\">
		".join("", $lines)."
	</table>

	
";



?>