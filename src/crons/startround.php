<?
##
##	Dieses Skript macht nur Gruppenverteilung und wird daher nichf fr den Basic Server angepasst
##
##
require_once("../includes.php"); // Subfunctions laden
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once("../inc/ingame/globalvars.php"); // fr get_an_empty_syndicate() wichtig!
$time = time(); // Zeit zur skriptausfhrung als timestamp, bitte spï¿½er nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.
$microtime = getmicrotime();
$hourtime = get_hour_time($time);
mt_srand($time);

if($argv[2] == "shuffle")
{
	classicGroupShuffleScript();
	exit();
}
else if($argv[2] == "groupMix")
{
	createTestGroups();
	exit();
}

$spieler_insgesamt = 0;
$spieler_tot = 0;
$spieler_alive = 0;
$spielerupdated_without_groups = 0;
$spielerupdated_groups = 0;
$spielerupdated_total = 0;


$globals = assoc("select * from globals order by round desc limit 1");
$countitstrong = 1;
if ( $time >= $globals[roundstarttime] && $globals[roundstatus] == 0 && !$globals[already_merged]) {
	//###################################################
	// Neue Version des Shuffle Scriptes (basierend auf DragonTECs Version)
	//###################################################
	
	echo "Starte Gruppen-Shuffle Script für Rundenstart (inok1989 R65 - tatsächlich zufällig)..\n";
	writelog("Starte Gruppen-Shuffle Script für Rundenstart (inok1989 R65 - tatsächlich zufällig)..\n");
	
	define(BREAKPOINT,0.60);  // nach wieviel % der Spieler nicht mehr auf 8, sondern auf 12 aufgeteilt wird (verhindert vorhersagbare mischung von Gruppen nach 8+4=12 oder 6+6=12)
	
	writelog("Breakpoint:" . BREAKPOINT . "\n");
	
	// Variablen initialisieren:
	define('NACHZUEGLER_MAX', 2);
	$gruppen_sum = 0;
	$gruppenMember = array();
	
	$num1 = 0;
	$num2 = 0;
	$num3 = 0;
	$num4 = 0;
	$num5 = 0;
	$num6 = 0;
	$num7 = 0;
	$num8 = 0;
	$num9 = 0;
	$num10 = 0;
	$unknown = 0;
	
	$queries = array();
	
	// Alle Gruppen mit Teilnehmern auslesen
	writelog("Starte Gruppen-Konzerncheck..\n");
	$synAliveData = assocs("SELECT u.id AS user_id, s.alive, u.konzernid FROM status AS s, users AS u WHERE u.konzernid = s.id ORDER BY rand()",'user_id');
	
	$groupquerry = "SELECT g.group_id, is_mentor_group, ist_offen, g.name, " .
			"(SELECT COUNT(*) FROM groups_new_members AS m WHERE m.group_id = g.group_id AND m.status = 1) AS num " . 
			"FROM groups_new AS g WHERE 0 < (SELECT COUNT(*) FROM groups_new_members AS m WHERE m.group_id = g.group_id AND m.status = 1) ORDER BY rand()";
	$gruppen = assocs($groupquerry, 'group_id');
	writelog("Die Gruppen werden in folgender Reihenfolge bearbeitet: \n");
	foreach ($gruppen as $group_id => $vl) {
		$gruppen[$group_id]['members'] = assocs("SELECT g. *, s.id AS konzernid
			FROM groups_new_members AS g, users AS u
			LEFT OUTER JOIN (status AS s) ON ( s.id = u.konzernid )
			WHERE u.id = g.user_id AND group_id = '".$group_id."' AND g.status = 1");
		
		
		$withoutKonzern = 0; $new_admin = false; $new_nachfolger = false;
		// Maximal 2 (NACHZUEGLER_MAX) dürfen nachjoinen
		foreach($gruppen[$group_id]['members'] as $vl2) {
			if (!$vl2['konzernid']) {
				$withoutKonzern++;
				if ($vl2['user_id'] == $vl['admin_id']) $new_admin = true;
				if ($vl2['user_id'] == $vl['nachfolger_id']) $new_nachfolger = true;
			}
			unset($synAliveData[$vl2['user_id']]);
		}
		
		// Falls keiner in der Gruppe einen Konzern hat wird die Gruppe gelöscht!
		if ($withoutKonzern == $vl['num']) {
			$vl['num'] = 0;
			$queries[] = "DELETE FROM groups_new WHERE group_id = '".$group_id."'";
			$queries[] = "DELETE FROM groups_new_members WHERE group_id = '".$group_id."'";
			unset($gruppen[$group_id]);
		} else {		
			if (NACHZUEGLER_MAX < $withoutKonzern) {
				$withoutKonzern -= NACHZUEGLER_MAX;
				$vl['num'] = $vl['num'] - $withoutKonzern;
				$withoutKonzern = NACHZUEGLER_MAX;
			}
			$vl['nachzuegler'] = $withoutKonzern;
			$queries[] = "UPDATE groups_new SET nachzuegler_max = '".$withoutKonzern."' WHERE group_id = '".$group_id."'";
			
			// Überprüfen ob Admin Konzern hat, ansonsten neuen bestimmen 
			if ($new_admin) {
				if ($new_nachfolger) {
					$vl['admin_id'] = single("SELECT user_id FROM groups_new_members AS m, users AS u " .
							"WHERE group_id = '".$group_id."' AND u.id = m.user_id AND u.konzernid != 0 LIMIT 1");
				} else {
					$vl['admin_id'] = $vl['nachfolger_id'];
				}
				$queries[] = "UPDATE groups_new SET admin_id = '".$vl['admin_id']."' WHERE group_id = '".$group_id."'";
			}
		}
			
		$gruppen_sum += $vl['num'];
		
		switch($vl['num']) {
			case 1: $num1++; break;
			case 2: $num2++; break;
			case 3: $num3++; break;
			case 4: $num4++; break;
			case 5: $num5++; break;
			case 6: $num6++; break;
			case 7: $num7++; break;
			case 8: $num8++; break;
			case 9: $num9++; break;
			case 10: $num10++; break;
			default: $unknown++;break;
		}
		writelog($group_id." (".$vl['num']."), ");
		$gruppen[$group_id]['num'] = $vl['num'];
	}
	
	writelog("Gruppenanzahl:" . count($gruppen) . "\n");
	
	writelog("Gruppen-Konzerncheck beendet..\n\n");
	
	//unset($gruppen);
	
	$killed = 0;
	foreach($synAliveData as $i => $vl) {
		if ( $vl['alive'] == 0 ) {
			$killed++;
			unset($synAliveData[$i]);
		}
	}
	
	writelog($killed . " Konzerne sind bereits tot..\n");
	writelog("Nicht zu Gruppen gehörende Konzerne: " . count($synAliveData) . "\n");
	$gruppen_sum += count($synAliveData);
	$num1 += count($synAliveData);
	// print_r($synAliveData_processed);
	
	writelog("Konzernverteilung auf Gruppen: \n");
	
	writelog("1 Konzern: " . $num1 . "\n"); 
	writelog("2 Konzern: " . $num2 . "\n");
	writelog("3 Konzern: " . $num3 . "\n");
	writelog("4 Konzern: " . $num4 . "\n");
	writelog("5 Konzern: " . $num5 . "\n");
	writelog("6 Konzern: " . $num6 . "\n");
	writelog("7 Konzern: " . $num7 . "\n");
	writelog("8 Konzern: " . $num8 . "\n");
	writelog("9 Konzern: " . $num9 . "\n");
	writelog("10 Konzern: " . $num10 . "\n");
	writelog("unknown: " . $unknown . "\n\n");
	
	writelog("Gesamtzahl zu erwartender Konzerne: " . $gruppen_sum . "\n");
	
	$absBreakpoint = $gruppen_sum * BREAKPOINT;
	
	writelog("Breakpoint(absolut): " . $absBreakpoint . "\n");
	
	$syns = array();
	$synMemberCount = array();
	$konzerneProcessed = 0;
	$playerSpace = 0;
	
	writelog("############\nBeginne mit Konzernverteilung von Gruppen auf Syndikate..\n############\n");
	
	$queries[] = "delete from syndikate";
	
	// 2 Syndikate erstellen (weniger geht nicht)
	$syns[1] = array();	$synMemberCount[1] = 0;
	$syns[2] = array();	$synMemberCount[2] = 0;	 
	$queries[] = "insert into syndikate (synd_id,synd_type,name) values (1,'normal','Syndikat')";
	$queries[] = "insert into syndikate (synd_id,synd_type,name) values (2,'normal','Syndikat')";
	
	foreach($gruppen as $i => $currentGroup) // $i = group_id
	{
		writelog("Bearbeite Gruppe " . $i . "(" . $currentGroup['num'] . " Konzern(e), davon ".$currentGroup['nachzuegler']." Nachzuegler)\n");
		$currentMax = MAX_USERS_A_GROUP;
		
		// Am besten passendes Syndikat suchen
		$best_fit = 0; $best_diff = $currentMax+1;
		foreach( $syns as $j => $currentSyn) {
			$diff = $currentMax - $synMemberCount[$j] - $currentGroup['num'];
			if (0 <= $diff && $diff <= $currentMax && $diff < $best_diff) {
				$best_fit = $j;
				$best_diff = $diff;
			}
		}
		
		if ($best_fit == 0) { // neues Syndikat erstellen, da die Gruppe nirgends reinpasst
			$index = count($syns)+1; // get next possible insertion point
			writelog("Kein Platz in vorhandenen, erstelle neues Syndikat (#" . $index . ")..\n");
			$syns[$index][] = $i;
			
			$synMemberCount[$index] = $currentGroup['num'];	
			$konzerneProcessed += $currentGroup['num'];
			
			$prefix_name = addslashes(substr($currentGroup['name'], 0, 5)); 
			
			$queries[] = "insert into syndikate (synd_id,synd_type,name) values (" . $index . ",'normal','Syndikat')";
			$queries[] = "update board_subjects set bid = ".$index.", title = CONCAT('".$prefix_name."', ': ', title) where bid = ".(BOARD_ID_OFFSET_GRUPPEN+$i);
			$queries[] = "update polls set synd_id = ".$index.", name = CONCAT('".$prefix_name."', ': ', name) where synd_id = ".(POLL_ID_OFFSET_GRUPPEN+$i);
			
		} else { // zu Syndikat hinzufügen (syn_id = $best_fit)
			writelog("noch Platz vorhandenen in Syndikat (#".$best_fit.")..\n");
			$syns[$best_fit][] = $i;			
			$synMemberCount[$best_fit] += $currentGroup['num'];	
			$konzerneProcessed += $currentGroup['num'];
			$index = $best_fit;
			
			$prefix_name = addslashes(substr($currentGroup['name'], 0, 5)); 
			$queries[] = "update board_subjects set bid = ".$index.", title = CONCAT('".$prefix_name."', ': ', title) where bid = ".(BOARD_ID_OFFSET_GRUPPEN+$i);
			$queries[] = "update polls set synd_id = ".$index.", name = CONCAT('".$prefix_name."', ': ', name) where synd_id = ".(POLL_ID_OFFSET_GRUPPEN+$i);
		}
	}
	
	writelog("<hr><hr>Checking Syns after group assembly<hr><hr>");
	
	writelog("OUTPUT: #groupId||#userId\n");
	
	writelog("<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\">");
	writelog("<tr>");
	
	foreach($syns as $j => $currentSyn)
	{
		writelog("<td>");
		writelog("<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">");
		
		foreach($currentSyn as $k => $groupId)
		{
			writelog("<tr style=\"background-color:#7777ff;\">");
			writelog("<td>");
			
			foreach($gruppen[$groupId]['members'] as $l => $vl)
			{
				writelog($groupId."||" . $vl['konzernid'] . "<br>");
			}
			
			writelog("</td>");
			writelog("<tr>");
		}			
		writelog("</table>");
		writelog("</td>");
	}
			
	writelog("</tr>");
	writelog("</table>");
	
	writelog("<hr><hr>Erstelle noch zusätzliche Randomsyns (falls nötig)..<hr><hr>\n");
	$synArmyBonus = array();
	
	// Notwendige Syndikat für Randomsyns erstellen
	$max_syns = ceil($gruppen_sum/MAX_USERS_AFTER_ROUNDSTART);
	if (count($syns) < $max_syns) {
		for ($i = count($syns)+1; $i <= $max_syns; $i++) {
			$syns[$i] = array();
			$synMemberCount[$i] = 0;	
			$queries[] = "insert into syndikate (synd_id,synd_type,name) values (" . $i . ",'normal','Syndikat')";
			writelog("Neues Syndikat (#".$i.") neu gegruendet..\n");
		} 
	}
	
	$syns_noGroups = array(); // Hier werden alle Konzerne gespeichert rid => array(konzid)
	$groups_to_syn = array();
	
	foreach($syns as $i => $currentSyns)
	{
		$syns_noGroups[$i] = array();
		foreach($currentSyns as $j => $groupInSyn)
		{
			$groups_to_syn[$groupInSyn] = $i;
			foreach($gruppen[$groupInSyn]['members'] as $k => $memberInGroup)
			{
				$syns_noGroups[$i][] = $memberInGroup['konzernid'];
			}
		}
	}
	
	writelog("<hr><hr>Starte das Random-Hinzufügen..<hr><hr>\n");
	foreach($synAliveData as $id => $vl)
	{
		$konzernid = $vl['konzernid'];
		writelog("Fuege Konzern " . $konzernid . " (UserID: ".$id.") einem Syndikat hinzu..\n");
		$currentMax = 0;
		if($konzerneProcessed < $absBreakpoint) {
			$currentMax = MAX_USERS_A_GROUP;
		} else {
			$currentMax = MAX_USERS_AFTER_ROUNDSTART;
		}
		
		$inserted = false;
		$inserted_syn = 0;
		
		$best_fit = 0; $best_num = MAX_USERS_AFTER_ROUNDSTART;
		foreach($syns_noGroups as $i => $i_v)
		{
			$temp_diff = $currentMax - $synMemberCount[$i] - 1;
			if ($synMemberCount[$i] < $best_num) {
				$best_fit = $i;
				$best_num = $synMemberCount[$i]; 
			}
		}
		if($best_fit != 0) {
			$synMemberCount[$best_fit]++;
			$syns_noGroups[$best_fit][] = $konzernid;  // rid => array(konzid)
			$inserted = true;
			$synArmyBonus[$best_fit]++;
			writelog("Konzern wurde Syndikat " . $best_fit . " hinzugefügt.\n");
		}
		
		if( !$inserted )
		{
			writelog("\n\nFEHLER: Alle Syndikate sind voll! Es wurden nicht genügend Syndikate zuvor erstellt.\n\n\n");
		}
		$konzerneProcessed++;
		
		/* DEBUG ausgabe für Random INSERTs
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo 	"<tr>";
		
		foreach($syns_noGroups as $j => $currentSyn)
		{
			echo "<td>";
			echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
			
			
			foreach($currentSyn as $k => $k_v)
			{
				if( $currentSyn[$k] == $id)
				{
					echo "<tr style=\"background-color:#77ff77;\">";
				}
				else
				{
					echo "<tr style=\"background-color:#7777ff;\">";
				}
				echo	"<td>";
				echo		"XXX<br>";
				echo	"</td>";
				echo "<tr>";
			}			
			echo	"</table>";
			echo "</td>";
		}
				
		echo	"</tr>";
		echo "</table>";
		
		echo "<hr>";
		
		*/
	}
	
	$allPlayerCount = 0;
	
	writelog("Dump der Endzuteilung (Nummern sind KonzernIDs)\n");
	
	writelog("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">");
	writelog("<tr>");
	
	foreach($syns_noGroups as $j => $currentSyn)
	{
		writelog("<td>");
		writelog("<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">");
		
		
		foreach($currentSyn as $k => $k_v)
		{
			writelog("<tr style=\"background-color:#7777ff;\">");
			writelog("<td>");
			writelog($k_v . "<br>");
			$allPlayerCount++;
			writelog("</td>");
			writelog("<tr>");
		}			
		writelog("</table>");
		writelog("</td>");
	}
			
	writelog("</tr>");
	writelog("</table>");
	
	writelog("Player Total: " . $allPlayerCount );
	
	
	
	// Query for updating rids of konz in status
	foreach($syns_noGroups as $synId => $currentSyn)
	{
		$queryString = "UPDATE status SET rid = " . $synId . ", lastlogintime=".$globals['roundstarttime']." WHERE id IN(";
		foreach($currentSyn as $j => $member )
		{
			if ($member) {
				$queryString .= $member . ",";
			}
		}
		$queryString = substr($queryString,0,-1);
		$queryString .= ")";
		$queries[] = $queryString;
	}
	
	// Query for updating rids of konz in stats
	foreach($syns_noGroups as $synId => $currentSyn)
	{
		$queryString = "UPDATE " . $globals[statstable] . " SET rid = " . $synId . " WHERE id IN(";
		foreach($currentSyn as $j => $member )
		{
			if ($member) {
				$queryString .= $member . ",";
			}
		}
		$queryString = substr($queryString,0,-1);
		$queryString .= ")";
		$queries[] = $queryString;
	}
	
	foreach($groups_to_syn as $groupId => $synId)
	{
		$queries[] = "update groups_new set current_rid=" . $synId . " where group_id=" . $groupId . ";";
	}
	//Runde 52 Synarmy Boni Einzahlen
	foreach($synArmyBonus as $synId=>$num){
		$queries[] = "update syndikate set offspecs=".(2000*$num)." where synd_id=".$synId;
		$queries[] = "update syndikate set defspecs=".(2000*$num)." where synd_id=".$synId;
	}
	//End Runde 52
	
	$number = 0;
	writelog("SQL Statements starten..\n");
	foreach ($queries as $vl) 
	{
		$number++;
		$meldung = select($vl);
		writelog("$number: $meldung - $vl"."\n" , "1");
	}
	
	$endtime = time();
	$endmicrotime = getmicrotime();
	
	$endmessage = "Skriptlaufzeit von ".date("d. M. Y, H:i:s", $time)." bis ".date("d. M. Y, H:i:s", $endtime).".\nExakte Laufzeit: ".(round(($endmicrotime-$microtime)*1000)/1000)."s.\nInsgesamt wurden $spieler_insgesamt aus dem Status Table geholt.\nDavon waren $spieler_alive am Leben und $spieler_tot tot.\nInsgesamt wurden $spielerupdated_total Spieler einem Syndikat zugeteilt. $spielerupdated_without_groups davon ohne Gruppe, $spielerupdated_groups mit Gruppe.\nInsgesamt wurden $dr Datenbankaufrufe getï¿½igt.\nDas entspricht ".(round($dr/(round(($endmicrotime-$microtime)*1000)/1000)*1000)/1000)." Datenbankaufrufen pro Sekunde.\n";
	writelog("$endmessage","","1");
	
	//####################################################
	// ENDE NEUES SCRIPT
	//###################################################
	
	select("update status set lastlogintime='$hourtime' where alive > 0");
	select("delete from towncrier");
    select("update globals set roundstatus = 1, already_merged = 1 where round = ".$globals{round});
   
    onRoundStart();
}
else 
{
  if ($globals[roundstatus] == 0 && $globals[roundstarttime] - 1 * 60 * 60 <= $time && $globals[roundstarttime] + 1 * 60 * 60 >= $time) 
	{
			$betreff = "Rundenstart Mischskript gelaufen ".$argv[1];
			$message = $globals[roundstarttime]." No Text argv1:".$argv[1];
			$email = "nicolasbreitwieser@syndicates-online.de";
			$to = "Nicolas";
			sendthemail($betreff, $message, $email, $to);
	}
}

function writelog($text) 
{
	global $globals;
	static $print;
	if (func_num_args() > 0) {
		$print .= $text;
		//echo $text;
		if (func_num_args() > 2) {
			$writelogdatei = "startroundwritelog_$globals[round].txt";

			if (!$handle = fopen("$writelogdatei", 'a')) {
					echo "Cannot open file ($filename)";
					exit;
			}
			if (!fwrite($handle, $print)) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);
		}
	}
}

/* Alte verteilung (sortiert)
 * 

	foreach($gruppen as $i => $currentGroup) // $i = group_id
	{
		writelog("Bearbeite Gruppe " . $i . "(" . $currentGroup['num'] . " Konzern(e), davon ".$currentGroup['nachzuegler']." Nachzuegler)\n");
		$currentMax = 0;
		if($konzerneProcessed < $absBreakpoint) {
			$currentMax = MAX_USERS_A_GROUP;
		} else {
			$currentMax = MAX_USERS_AFTER_ROUNDSTART;
		}
		
		if( $playerSpace < $currentGroup['num'] )  // wenn gruppenanzahl nirgendwo reinpasst, neues syn erstellen
		{
			$thread_counter = 1;
			$index = count($syns)+1; // get next possible insertion point
			writelog("Kein Platz in vorhandenen, erstelle neues Syndikat (#" . $index . ")..\n");
			$syns[$index][] = $i;
			
			$synMemberCount[$index] = $currentGroup['num'];	
			$konzerneProcessed += $currentGroup['num'];
			
			if( ( $currentMax - $synMemberCount[$index] ) >= $playerSpace )
			{
				$playerSpace = $currentMax - $synMemberCount[$index];
			}
			
			$prefix_name = addslashes(substr($currentGroup['name'], 0, 5)); 
			
			$queries[] = "insert into syndikate (synd_id,synd_type,name) values (" . $index . ",'normal','Syndikat')";
			$queries[] = "update board_subjects set bid = ".$index.", title = CONCAT(".$prefix_name.", ': ', title) where bid = ".(BOARD_ID_OFFSET_GRUPPEN+$i);
			$queries[] = "update polls set synd_id = ".$index.", name = CONCAT(".$prefix_name.", ': ', name) where synd_id = ".(POLL_ID_OFFSET_GRUPPEN+$i);
		} else { // in bestehendes Syndikat rein
			$thread_counter++;
			writelog("Gruppe wird in Syndikat ");
			$inserted = false;
			$playerSpace = 0;
			
			foreach( $syns as $j => $currentSyn)
			{
				if( !$inserted && $synMemberCount[$j] + $currentGroup['num'] <= $currentMax )
				{
					writelog($j . " eingefügt..\n");
					$syns[$j][] = $i;			
					$synMemberCount[$j] += $currentGroup['num'];	
					$konzerneProcessed += $currentGroup['num'];
					$index = $j; // müsste die Boardzuteilung fixen (davor war diese Zeile nicht vorhanden)
					$inserted = true;
				}
				
				$currentSynSizeLeft = $currentMax - $synMemberCount[$j];
				if( $currentSynSizeLeft >= $playerSpace)
				{
					$playerSpace = $currentSynSizeLeft;
				}		
			}
			$prefix_name = addslashes(substr($currentGroup['name'], 0, 5)); 
			$queries[] = "update board_subjects set bid = ".$index.", title = CONCAT(".$prefix_name.", ': ', title) where bid = ".(BOARD_ID_OFFSET_GRUPPEN+$i);
			$queries[] = "update polls set synd_id = ".$index.", name = CONCAT(".$prefix_name.", ': ', name) where synd_id = ".(POLL_ID_OFFSET_GRUPPEN+$i);
		}
		
		/* DEBUG Output nach jedem INSERT
		
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo 	"<tr>";
		
		foreach($syns as $j => $currentSyn)
		{
			echo "<td>";
			echo	"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\" style=\"border-color:black;\">";
			
			
			$k_max = count($currentSyn);
			foreach($currentSyn as $k => $k_v)
			{
				if( $currentSyn[$k] == $i)
				{
					echo "<tr style=\"background-color:#77ff77;\">";
				}
				else
				{
					echo "<tr style=\"background-color:#7777ff;\">";
				}
				echo	"<td>";
				
				for($l=0;$l < $gruppenMemberCount[$k_v]; $l++)
				{
					echo		"XXX<br>";
				}
				
				echo	"</td>";
				echo "<tr>";
			}			
			echo	"</table>";
			echo "</td>";
		}
				
		echo	"</tr>";
		echo "</table>";
		
		echo "<hr>";
		
		*/
		
//	}


?>
