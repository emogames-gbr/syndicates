<?

// Created by Nicolas Breitwieser
// 26.10.2005 - 17:00 Uhr

$statusdata = array();
$userdata = array();
$sessions = array();
$sessions_by_ips = array();
$total_sessions_by_ips = array();
$different_players = array();
$ips = array();
$time_ip_gueltig = array();
$totaltime_ip_gueltig = 0;
$totaltime_online = 0;
$time_ip_online = array();
$different_players_onlinetime_by_ip = array();
$pcids_not_with_same_ip = array();

echo $ausgabe_init;

	// PR?CHECKS, wenn Eingabe
	$konzernid = floor($konzernid);
	if ($konzernid) {
		$statusdata = assoc("select * from status where id = $konzernid");
		if ($statusdata) {
			$userdata = assoc("select * from users where konzernid = $konzernid");
			if (!$userdata) { echo "Zum angegebenen Konzern existiert kein User-Account. User wird sich gel?scht haben.";  }
		}
		else { echo "Ein Konzern mit der ID $konzernid existiert nicht"; $konzernid = ""; }
	}
if (!$konzernid && !$ip) {


	echo "	$formstart_get
			Konzern-ID: <input type=text name=konzernid value=\"$konzernid\"><br><br>
			<input type=submit value=weiter></form>";


}

else {

	?>
	<style type="text/css">
		a:link { text-decoration:none; }
		a:visited { text-decoration:none; }
		a:hover { text-decoration:none; }
		a:active { text-decoration:none; }
		a:focus { text-decoration:none; }
	</style>
	<script language="JavaScript">
	<!--
		function info(konzernid, ip)	{
			neuesFenster =  open("index.php?action=detect_ip_mates&konzernid="+konzernid+"&ip="+ip, "Info","width=800, height=600, scrollbars=yes");
			neuesFenster.focus()
		};
	-->
	</script>
	<?

	if ($konzernid) {
		$sessions = assocs("select * from sessionids_safe where user_id = $konzernid and angelegt_bei >= $globals[roundstarttime] order by angelegt_bei desc");
		foreach ($sessions as $vl) {
			$sessions_by_ips[$vl[ip]][] = $vl;
			if (!in_array($vl[ip], $ips)) $ips[] = $vl[ip];
			$sessions_by_pcid[$vl[pc_identifier]][] = $vl;
			if (!in_array($vl[pc_identifier], $pcids)) $pcids[] = $vl[pc_identifier];
		}
	} else {
		$sessions_by_ips[$ip] = array();
		$sessions_by_pcid[$pcid] = array();
	}

		$different_ips = count($sessions_by_ips);
		foreach ($sessions_by_ips as $ky => $vl) {
			$total_sessions_by_ips[$ky] = assocs("select sessionid, angelegt_bei, gueltig_bis, ip, user_id, gueltig_bis - angelegt_bei as validtime, hostname, pc_identifier, browsername from sessionids_safe where ip like '$ky' and angelegt_bei >= $globals[roundstarttime] order by angelegt_bei desc");
			foreach ($total_sessions_by_ips[$ky] as $vl2) {
				if (!$different_players_by_ip[$ky][$vl2[user_id]]) $different_players[$vl2[user_id]]++;
				$different_players_by_ip[$ky][$vl2[user_id]]++;
				$different_players_onlinetime_by_ip[$ky][$vl2[user_id]] += $vl2[validtime];
				$different_players_onlinetime_total[$vl2[user_id]] += $vl2[validtime];
				$time_ip_online[$ky] += $vl2[validtime];
				$totaltime_online += $vl2[validtime];
			}
			arsort($different_players_onlinetime_by_ip[$ky]);
			$time_ip_gueltig[$ky] = (-1) * ($total_sessions_by_ips[$ky][count($total_sessions_by_ips[$ky])-1][angelegt_bei] - $total_sessions_by_ips[$ky][0][gueltig_bis]);
			$totaltime_ip_gueltig += $time_ip_gueltig[$ky];
		}
		$different_pcids = count($sessions_by_pcid);
		foreach ($sessions_by_pcid as $ky => $vl) {
			$total_sessions_by_pcid[$ky] = assocs("select sessionid, angelegt_bei, gueltig_bis, ip, user_id, gueltig_bis - angelegt_bei as validtime, hostname, pc_identifier, browsername from sessionids_safe where pc_identifier like '$ky' and angelegt_bei >= $globals[roundstarttime] order by angelegt_bei desc");
			$lastused_done = 0;
			foreach ($total_sessions_by_pcid[$ky] as $vl2) {
				if (!$different_players_by_pcid[$ky][$vl2[user_id]]) $different_players_after_pcid[$vl2[user_id]]++;
				if (!$lastused_done && $vl2[user_id] == $konzernid) {
					$lastused_done = 1; $lastused_pcid[$ky] = $vl2[angelegt_bei];
				}
				$different_players_by_pcid[$ky][$vl2[user_id]]++;
				$different_players_onlinetime_by_pcid[$ky][$vl2[user_id]] += $vl2[validtime];
				$different_players_onlinetime_total_after_pcid[$vl2[user_id]] += $vl2[validtime];
				$time_pcid_online[$ky] += $vl2[validtime];
				$totaltime_online_after_pcid += $vl2[validtime];
			}
			arsort($different_players_onlinetime_by_pcid[$ky]);
			$time_pcid_gueltig[$ky] = (-1) * ($total_sessions_by_pcid[$ky][count($total_sessions_by_pcid[$ky])-1][angelegt_bei] - $total_sessions_by_pcid[$ky][0][gueltig_bis]);
			$totaltime_pcid_gueltig += $time_pcid_gueltig[$ky];
		}
		if ($different_players) $other_playerdata = assocs("select * from status where id in (".join(",", array_merge(array_keys($different_players), array_keys($different_players_after_pcid))).")", "id");
		//$other_playerdata = array_merge($other_playerdata, $other_playerdata_merge);

		arsort($different_players);


	// Hauptteil: Keine IP-?bergeben (danach Teil mit IP-?bergabe)


	if (!$ip && !$pcid) {


		// AUSGABE


		foreach ($different_players_after_pcid as $ky => $vl) {
			if (!in_array($ky, array_keys($different_players))) $pcids_not_with_same_ip[$ky] = 1;
		}


		$list_players_by_haeufigkeit_mit_dabei = "<table bgcolor=black cellspacing=0 cellpadding=0><tr><td><table cellpadding=2 cellspacing=1><tr bgcolor=orange><td><font size=2>Spieler</font></td><td bgcolor=yellow><font size=2>bei # IPs dabei</font></td><td bgcolor=yellow></td><td bgcolor=yellow><font size=2>Onlinezeit</font></td><td bgcolor=lightblue><font size=2>bei # PCIDs dabei</font></td><td bgcolor=lightblue></td><td bgcolor=lightblue><font size=2>Onlinezeit</font></td></tr>";
		foreach (($different_players + $pcids_not_with_same_ip) as $ky => $vl) {
			if ($ky != $konzernid) {
				$list_players_by_haeufigkeit_mit_dabei .= "<tr bgcolor=white><td><font size=2><a href=index.php?action=detect_ip_mates&konzernid=$ky color=blue>".$other_playerdata[$ky][syndicate]." (#".$other_playerdata[$ky][rid].")</a></font></td><td align=center bgcolor=#fafc76><font size=2>$vl</font></td><td bgcolor=#fafc76><font size=2>".round($different_players[$ky]/$different_ips*100)."%</font></td><td align=center bgcolor=#fafc76><font size=2>".round($different_players_onlinetime_total[$ky]/3600)."h</font></td><td align=center bgcolor=#d6ddf9><font size=2>".$different_players_after_pcid[$ky]."</font></td><td bgcolor=#d6ddf9><font size=2>".round($different_players_after_pcid[$ky]/$different_pcids*100)."%</font></td><td align=center bgcolor=#d6ddf9><font size=2>".round($different_players_onlinetime_total_after_pcid[$ky]/3600)."h</font></td></tr>";
			}
		}
		$list_players_by_haeufigkeit_mit_dabei .= "</table></td></tr></table>";

		$different_players_count = count($different_players) - 1;
		$different_players_count_after_pcid = count($different_players_after_pcid) - 1;

		$ips_ausgabe = "<table bgcolor=black cellspacing=0 cellpadding=0><tr><td><table cellpadding=2 cellspacing=1>";
		foreach ($ips as $vl) {
			$tp = 0;
			$second_longest_online = 0;
			foreach ($different_players_onlinetime_by_ip[$vl] as $ky2 => $vl2) {
				$tp++;
				if ($tp == 1) $longest_online = $ky2;
				if ($tp == 2) { $second_longest_online = $ky2; break; }
			}
			$number_of_players = count($different_players_by_ip[$vl]);
			$number_of_logins = count($total_sessions_by_ips[$vl]);
			$ips_ausgabe .= "<tr bgcolor=white>
						<td".(date("l",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei]) == "Sunday" ? " bgcolor=orange":(date("l",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei]) == "Saturday" ? " bgcolor=yellow":""))."><font size=2>".date("l",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei])."</font></td>
						<td bgcolor=#c3dbc0><font size=2>".date("d.",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei])."</font></td>
						<td bgcolor=#c3dbc0><font size=2>".date("M",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei])."</font></td>
						<td bgcolor=#c3dbc0><font size=2>".date("H:i",$total_sessions_by_ips[$vl][count($total_sessions_by_ips[$vl])-1][angelegt_bei])."</font></td>
						<td align=right bgcolor=#c3dbc0><font size=2>".round($time_ip_online[$vl]/3600)."h</font></td>
						<td bgcolor=#fafc76><a href=index.php?action=detect_ip_mates&konzernid=$konzernid&ip=$vl><font size=2><font color=black>$vl</font></a></font></td>
						<td align=right bgcolor=#fafc76><a href=javascript:info('$konzernid','$vl')><font size=2 color=black>".round($time_ip_gueltig[$vl]/3600)."h</a></font></td>
						<td bgcolor=#d6ddf9><font size=2>-<b>$number_of_players</b>-</font></td>
						<td bgcolor=#d6ddf9><font size=2>-$number_of_logins-</font></td>
						<td align=right bgcolor=#d6ddf9><font size=2>".round($different_players_onlinetime_by_ip[$vl][$konzernid] / $time_ip_online[$vl] * 100)."%</font></td>
						<td bgcolor=#f4f4f4><a href=index.php?action=detect_ip_mates&konzernid=$longest_online><font size=2 color=".($longest_online == $konzernid ? "green" : "red")."><b>".$other_playerdata[$longest_online][syndicate]." (#".$other_playerdata[$longest_online][rid].")</a></b> [".round($different_players_onlinetime_by_ip[$vl][$longest_online] / $time_ip_online[$vl] * 100)."%]</font></td>
						<td bgcolor=#f4f4f4>".($second_longest_online ? "<a href=index.php?action=detect_ip_mates&konzernid=$second_longest_online><font  size=2 color=".($second_longest_online == $konzernid ? "green" : "red")."><b>".$other_playerdata[$second_longest_online][syndicate]." (#".$other_playerdata[$second_longest_online][rid].")</a></b> [".round($different_players_onlinetime_by_ip[$vl][$second_longest_online] / $time_ip_online[$vl] * 100)."%]</font>":"")."</td>

						</tr>";
		}
		$ips_ausgabe .= "</table></td></tr></table>";

		$pcids_ausgabe = "<table bgcolor=black cellspacing=0 cellpadding=0><tr><td><table cellpadding=2 cellspacing=1>";
		arsort($lastused_pcid);
		foreach ($lastused_pcid as $vl => $last_used) {
			$tp = 0;
			$second_longest_online = 0;
			foreach ($different_players_onlinetime_by_pcid[$vl] as $ky2 => $vl2) {
				$tp++;
				if ($tp == 1) $longest_online = $ky2;
				if ($tp == 2) { $second_longest_online = $ky2; break; }
			}
			$number_of_players = count($different_players_by_pcid[$vl]);
			$number_of_logins = count($total_sessions_by_pcid[$vl]);
			$pcids_ausgabe .= "<tr bgcolor=white>
						<td align=right bgcolor=#c3dbc0><font size=2>".round($time_pcid_online[$vl]/3600)."h</font></td>
						<td bgcolor=#fafc76><a href=index.php?action=detect_ip_mates&konzernid=$konzernid&pcid=$vl><font size=2><font color=black>".substr($vl, 0, 6).substr($vl, strlen($vl)-7, 8)."</font></a></font></td>
						<td bgcolor=#d6ddf9><font size=2>-<b>$number_of_players</b>-</font></td>
						<td bgcolor=#d6ddf9><font size=2>-$number_of_logins-</font></td>
						<td align=right bgcolor=#d6ddf9><font size=2>".round($different_players_onlinetime_by_pcid[$vl][$konzernid] / $time_pcid_online[$vl] * 100)."%</font></td>
						<td bgcolor=#f4f4f4><a href=index.php?action=detect_ip_mates&konzernid=$longest_online><font size=2 color=".($longest_online == $konzernid ? "green" : "red")."><b>".$other_playerdata[$longest_online][syndicate]." (#".$other_playerdata[$longest_online][rid].")</a></b> [".round($different_players_onlinetime_by_pcid[$vl][$longest_online] / $time_pcid_online[$vl] * 100)."%]</font></td>
						<td bgcolor=#f4f4f4>".($second_longest_online ? "<a href=index.php?action=detect_ip_mates&konzernid=$second_longest_online><font  size=2 color=".($second_longest_online == $konzernid ? "green" : "red")."><b>".$other_playerdata[$second_longest_online][syndicate]." (#".$other_playerdata[$second_longest_online][rid].")</a></b> [".round($different_players_onlinetime_by_pcid[$vl][$second_longest_online] / $time_pcid_online[$vl] * 100)."%]</font>":"")."</td>
						<td bgcolor=#f4f4f4><font size=2>".((date("d", $last_used) == date("d", time()) OR (date("M", $last_used)+1 == date("M", time()) && date("d", $last_used) > date("d", time()))) ? date("H:i:s", $last_used).", <b>heute</b>":date("H:i:s, d.M", $last_used))."</font></td>

						</tr>";
		}
		$pcids_ausgabe .= "</table></td></tr></table>";


			echo "
			</td></tr></table>
				<table align=center bgcolor=black cellspacing=0 cellpadding=0><tr><td>
					<table align=center cellspacing=1 cellpadding=5>
						<tr bgcolor=white><td>".innertable("<b>Spieler</b>", $statusdata[syndicate]." (#".$statusdata[rid].")")."</td>
						<td>".innertable("<b>Username</b>", $userdata[username])."</td></tr>
						<tr bgcolor=white><td bgcolor=#fafc76>".innertable("<b>IP Different IPs</b>", $different_ips)."</td><td bgcolor=#d6ddf9>".innertable("<b>PCID Different PCIDs</b>", $different_pcids)."</td></tr>
						<tr bgcolor=white><td bgcolor=#fafc76>".innertable("<b>IP Different Players</b>", $different_players_count)."</td><td bgcolor=#d6ddf9>".innertable("<b>PCID Different Players</b>", $different_players_count_after_pcid)."</td></tr>
						<tr bgcolor=white><td bgcolor=#fafc76>".innertable("<b>IPs Validtime</b>", round($totaltime_ip_gueltig/3600)."h")."</td><td bgcolor=#d6ddf9>".innertable("<b>(PCIDs Validtime</b>", round($totaltime_pcid_gueltig/3600)."h<b>)*</b>")."</td></tr>
						<tr bgcolor=white><td bgcolor=#fafc76>".innertable("<b>IP Onlinezeit Spieler</b>", round($different_players_onlinetime_total[$konzernid]/3600)."h (".(round($different_players_onlinetime_total[$konzernid]/$totaltime_online*100)).")%")."</td><td bgcolor=#d6ddf9>".innertable("<b>PCID Onlinezeit Spieler</b>", round($different_players_onlinetime_total_after_pcid[$konzernid]/3600)."h (".(round($different_players_onlinetime_total_after_pcid[$konzernid]/$totaltime_online_after_pcid*100)).")%")."</td></tr>
						<tr bgcolor=white><td bgcolor=#fafc76>".innertable("<b>IP Onlinezeit alle</b>", round($totaltime_online/3600)."h (100%)")."</td><td bgcolor=#d6ddf9>".innertable("<b>PCID Onlinezeit alle</b>", round($totaltime_online_after_pcid/3600)."h (100%)")."</td></tr>
					</table>
				</td></tr></table><br><br>

				<table cellpadding=8 width=100% border=0><tr>
				<td valign=top align=right><b>Übersicht</b><br>$list_players_by_haeufigkeit_mit_dabei</td>
				<td valign=top align=left><b>PCIDs</b><br>$pcids_ausgabe</td></tr>
				<tr><td valign=top align=center colspan=2><b>IPs</b><br>$ips_ausgabe</td>
				</tr></table><table><tr><td>* Wert ohne Bedeutung";
	}


	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////

	// Andernfalls wenn IP ?bergeben wurde, Daten f?r diese IP auflisten
	elseif ($ip) {

		// Spieler auflisten

		foreach ($different_players_onlinetime_by_ip[$ip] as $ky => $vl) {
			$first_one = $ky; break;
		}
		$list_players_by_relative_onlinetime = "<table bgcolor=black cellspacing=0 cellpadding=0 width=100%><tr><td><table cellpadding=2 cellspacing=1 width=100%><tr bgcolor=orange><td>Spieler</td><td colspan=".($different_players_onlinetime_by_ip[$ip][$first_one] >= 86400 ? "5":"4").">Onlinetime</td><td>Logins</td><td>Onlinetime / Login</td></tr>";
		foreach ($different_players_onlinetime_by_ip[$ip] as $ky => $vl) {
				$list_players_by_relative_onlinetime .= "<tr bgcolor=white><td><a href=index.php?action=detect_ip_mates&konzernid=$ky color=blue>".$other_playerdata[$ky][syndicate]." (#".$other_playerdata[$ky][rid].")</a></td>".($vl >= 86400 ? "<td align=center>".floor($vl/86400)."d </td>":($different_players_onlinetime_by_ip[$ip][$first_one] >= 86400 ? "<td></td>":""))."<td align=right>".($vl >= 3600 ? floor(($vl-floor($vl/86400)*86400)/3600)."h ":"")."</td><td align=right>".round(($vl-floor($vl/3600)*3600)/60)."m</td><td align=right>".round($vl*100/$time_ip_online[$ip])."%</td><td width=200><img src=dotpixel.gif border=0 height=10 width=".(round(round(200*$vl/$time_ip_online[$ip])))."></td><td align=center>".$different_players_by_ip[$ip][$ky]."</td><td>".round($vl/$different_players_by_ip[$ip][$ky]/60)."m</td></tr>";
		}
		$list_players_by_relative_onlinetime .= "</table></td></tr></table>";


		// Genaue Sessions auflisten

		$sessions_ausgabe = "<table bgcolor=black cellspacing=0 cellpadding=0><tr><td><table cellpadding=2 cellspacing=1>";
		$count = 0;
		krsort($total_sessions_by_ips[$ip]);
		foreach ($total_sessions_by_ips[$ip] as $vl) {
			$newday = 0;
			$day = date("d", $vl[angelegt_bei]);
			$month = date("m", $vl[angelegt_bei]);
			$year = date("Y", $vl[angelegt_bei]);
			if ($day > $lastday) { $lastday = $day; $newday = 1;}
			if ($month > $lastmonth) { if ($lastmonth) { $lastday = 1; }; $lastmonth = $month; $newday = 1; }
			if ($year > $lastyear) { if ($lastyear) { $lastday = 1; $lastmonth = 1; }; $lastyear = $year; $newday = 1;}
			if (++$count >= 2 && $count <= count($total_sessions_by_ips[$ip])) {
				$zwischentime = $vl[angelegt_bei] - $last_gueltig_bis;
				if ($zwischentime > 10 * 60) {
					$sessions_ausgabe .= "<tr bgcolor=white".($zwischentime > 0 ? " height=".(round($zwischentime/60/10)*10 < 360 ? round($zwischentime/60/10)*10 : 360):"")."><td colspan=8 align=center valign=center bgcolor=".($zwischentime < 0 ? "#fffaad":"white")."><font size=".($zwischentime > 0 ? ceil($zwischentime/3600/6) : "1").">".($zwischentime >= 86400 ? floor($zwischentime/86400)."d, ":"").($zwischentime >= 3600 ? (floor($zwischentime/3600) % 24)."h, ":"").(floor($zwischentime/60) % 60)."m, ".($zwischentime % 60)."s</font></td></tr>";
				}
			}

			$sessions_ausgabe .= "<tr bgcolor=white>".($newday ? "
							<td bgcolor=orange><font size=2>".($newday ? date("l",$vl[angelegt_bei]):"")."</font></td>
							<td bgcolor=orange><font size=2>".($newday ? date("d.",$vl[angelegt_bei]):"")."</font></td>
							<td bgcolor=orange><font size=2>".($newday ? date("M",$vl[angelegt_bei]):"")."</font></td>":"<td colspan=3 bgcolor=".($zwischentime < 0 ? "#fffaad":"white").">".($zwischentime < 0 ? "<font size=1>".($zwischentime <= -86400 ? ceil($zwischentime/86400)."d, ":"").($zwischentime <= -3600 ? (ceil($zwischentime/3600) % 24)."h, ":"").(ceil($zwischentime/60) % 60)."m, ".($zwischentime % 60)."s</font>":"")."</td>")."
							<td bgcolor=#c3dbc0><font size=2>".date("H:i:s",$vl[angelegt_bei])."</font></td>
							<td align=right bgcolor=#c3dbc0><font size=2>".round($vl[validtime]/60)."m</font></td>
							<td bgcolor=#c3dbc0><font size=2>".date("H:i:s",$vl[gueltig_bis])."</font></td>
							<td bgcolor=#f4f4f4><font size=2><a href=index.php?action=detect_ip_mates&konzernid=$vl[user_id]>".($vl[user_id] == $konzernid ? "<font color=blue><b>":"<font color=black>").$other_playerdata[$vl[user_id]][syndicate]." (#".$other_playerdata[$vl[user_id]][rid].")".($vl[user_id] == $konzernid ? "</b></font>":"</font>")."</a></font></td>
							<td><font size=2><a href=index.php?action=detect_ip_mates&konzernid=$vl[user_id]&pcid=$vl[pc_identifier]>".substr($vl[pc_identifier], 0, 6).substr($vl[pc_identifier], strlen($vl[pc_identifier])-7, 8)."</a></font></td>
							<!--
							<td><font size=2>".preg_replace("/\([^\)]*\)/", "", $vl[browsername])."</font></td>
							<td><font size=2>$vl[hostname]</font></td>-->
							</tr>";
			$last_gueltig_bis = $vl[gueltig_bis];
		}
		$sessions_ausgabe .= "</table></td></tr></table>";

		$number_of_players_online = count($different_players_by_ip[$ip]);
		$number_of_logins = count($total_sessions_by_ips[$ip]);

			echo "
			</td></tr></table>
				<table align=center bgcolor=black cellspacing=0 cellpadding=0><tr><td>
					<table align=center cellspacing=1 cellpadding=5>

						<tr bgcolor=white><td>".innertable("<b>IP</b>", $ip)."</td><td>".innertable("<b>Different Players</b>", $number_of_players_online)."</td></tr>
						<tr bgcolor=white><td>".innertable("<b>IP Validtime</b>", round($time_ip_gueltig[$ip]/3600)."h")."</td><td>".innertable("<b>Logins</b>", $number_of_logins)."</td></tr>
						<tr bgcolor=white><td>".($konzernid ? innertable("<b>Onlinezeit Spieler</b>", round($different_players_onlinetime_by_ip[$ip][$konzernid]/3600)."h (".(round($different_players_onlinetime_by_ip[$ip][$konzernid]/$time_ip_online[$ip]*100)).")%") :"")."</td><td>".innertable("<b>Onlinezeit alle</b>", round($time_ip_online[$ip]/3600)."h")."</td></tr>
						".($konzernid ? "<tr bgcolor=white><td>".innertable("<b>Spieler</b>", $statusdata[syndicate]." (#".$statusdata[rid].")")."</td>
						<td>".innertable("<b>Username</b>", $userdata[username])."</td></tr>":"")."
					</table>
				</td></tr></table><br><br>

				<table cellpadding=8 width=100% border=0><tr>
				<td valign=top align=right>$list_players_by_relative_onlinetime</td></tr>
				<tr><td valign=top align=center>$sessions_ausgabe</td>
				</tr></table><table><tr><td>";

	}

	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////

	// Andernfalls wenn PCID ?bergeben wurde, Daten f?r diese IP auflisten

	elseif($pcid) {

		// Spieler auflisten

		foreach ($different_players_onlinetime_by_pcid[$pcid] as $ky => $vl) {
			$first_one = $ky; break;
		}
		$list_players_by_relative_onlinetime = "<table bgcolor=black cellspacing=0 cellpadding=0 width=100%><tr><td><table cellpadding=2 cellspacing=1 width=100%><tr bgcolor=orange><td>Spieler</td><td colspan=".($different_players_onlinetime_by_pcid[$pcid][$first_one] >= 86400 ? "5":"4").">Onlinetime</td><td>Logins</td><td>Onlinetime / Login</td></tr>";
		foreach ($different_players_onlinetime_by_pcid[$pcid] as $ky => $vl) {
				$list_players_by_relative_onlinetime .= "<tr bgcolor=white><td><a href=index.php?action=detect_ip_mates&konzernid=$ky color=blue>".$other_playerdata[$ky][syndicate]." (#".$other_playerdata[$ky][rid].")</a></td>".($vl >= 86400 ? "<td align=center>".floor($vl/86400)."d </td>":($different_players_onlinetime_by_pcid[$pcid][$first_one] >= 86400 ? "<td></td>":""))."<td align=right>".($vl >= 3600 ? floor(($vl-floor($vl/86400)*86400)/3600)."h ":"")."</td><td align=right>".round(($vl-floor($vl/3600)*3600)/60)."m</td><td align=right>".round($vl*100/$time_pcid_online[$pcid])."%</td><td width=200><img src=dotpixel.gif border=0 height=10 width=".(round(round(200*$vl/$time_pcid_online[$pcid])))."></td><td align=center>".$different_players_by_pcid[$pcid][$ky]."</td><td>".round($vl/$different_players_by_pcid[$pcid][$ky]/60)."m</td></tr>";
		}
		$list_players_by_relative_onlinetime .= "</table></td></tr></table>";


		// Genaue Sessions auflisten

		$sessions_ausgabe = "<table bgcolor=black cellspacing=0 cellpadding=0><tr><td><table cellpadding=2 cellspacing=1>";
		$count = 0;
		krsort($total_sessions_by_pcid[$pcid]);
		foreach ($total_sessions_by_pcid[$pcid] as $vl) {
			$newday = 0;
			$day = date("d", $vl[angelegt_bei]);
			$month = date("m", $vl[angelegt_bei]);
			$year = date("Y", $vl[angelegt_bei]);
			if ($day > $lastday) { $lastday = $day; $newday = 1;}
			if ($month > $lastmonth) { if ($lastmonth) { $lastday = 1; }; $lastmonth = $month; $newday = 1; }
			if ($year > $lastyear) { if ($lastyear) { $lastday = 1; $lastmonth = 1; }; $lastyear = $year; $newday = 1;}
			if (++$count >= 2 && $count <= count($total_sessions_by_pcid[$pcid])) {
				$zwischentime = $vl[angelegt_bei] - $last_gueltig_bis;
				if ($zwischentime > 10 * 60) {
					$sessions_ausgabe .= "<tr bgcolor=white".($zwischentime > 0 ? " height=".(round($zwischentime/60/10)*10 < 360 ? round($zwischentime/60/10)*10 : 360):"")."><td colspan=8 align=center valign=center bgcolor=".($zwischentime < 0 ? "#fffaad":"white")."><font size=".($zwischentime > 0 ? ceil($zwischentime/3600/6) : "1").">".($zwischentime >= 86400 ? floor($zwischentime/86400)."d, ":"").($zwischentime >= 3600 ? (floor($zwischentime/3600) % 24)."h, ":"").(floor($zwischentime/60) % 60)."m, ".($zwischentime % 60)."s</font></td></tr>";
				}
			}

			$sessions_ausgabe .= "<tr bgcolor=white>".($newday ? "
							<td bgcolor=orange><font size=2>".($newday ? date("l",$vl[angelegt_bei]):"")."</font></td>
							<td bgcolor=orange><font size=2>".($newday ? date("d.",$vl[angelegt_bei]):"")."</font></td>
							<td bgcolor=orange><font size=2>".($newday ? date("M",$vl[angelegt_bei]):"")."</font></td>":"<td colspan=3 bgcolor=".($zwischentime < 0 ? "#fffaad":"white").">".($zwischentime < 0 ? "<font size=1>".($zwischentime <= -86400 ? ceil($zwischentime/86400)."d, ":"").($zwischentime <= -3600 ? (ceil($zwischentime/3600) % 24)."h, ":"").(ceil($zwischentime/60) % 60)."m, ".($zwischentime % 60)."s</font>":"")."</td>")."
							<td bgcolor=#c3dbc0><font size=2>".date("H:i:s",$vl[angelegt_bei])."</font></td>
							<td align=right bgcolor=#c3dbc0><font size=2>".round($vl[validtime]/60)."m</font></td>
							<td bgcolor=#c3dbc0><font size=2>".date("H:i:s",$vl[gueltig_bis])."</font></td>
							<td bgcolor=#f4f4f4><font size=2><a href=index.php?action=detect_ip_mates&konzernid=$vl[user_id]>".($vl[user_id] == $konzernid ? "<font color=blue><b>":"<font color=black>").$other_playerdata[$vl[user_id]][syndicate]." (#".$other_playerdata[$vl[user_id]][rid].")".($vl[user_id] == $konzernid ? "</b></font>":"</font>")."</a></font></td>
							<td><font size=2><a href=index.php?action=detect_ip_mates&konzernid=$vl[user_id]&ip=$vl[ip]>".$vl[ip]."</a></font></td>
							<!--
							<td><font size=2>".preg_replace("/\([^\)]*\)/", "", $vl[browsername])."</font></td>
							<td><font size=2>$vl[hostname]</font></td>-->
							</tr>";
			$last_gueltig_bis = $vl[gueltig_bis];
		}
		$sessions_ausgabe .= "</table></td></tr></table>";

		$number_of_players_online = count($different_players_by_pcid[$pcid]);
		$number_of_logins = count($total_sessions_by_pcid[$pcid]);

			echo "
			</td></tr></table>
				<table align=center bgcolor=black cellspacing=0 cellpadding=0><tr><td>
					<table align=center cellspacing=1 cellpadding=5>

						<tr bgcolor=white><td>".innertable("<b>PCID</b>", substr($pcid, 0, 6).substr($pcid, strlen($pcid)-7, 8))."</td><td>".innertable("<b>Different Players</b>", $number_of_players_online)."</td></tr>
						<tr bgcolor=white><td>".innertable("<b>PCID Validtime</b>", round($time_pcid_gueltig[$pcid]/3600)."h")."</td><td>".innertable("<b>Logins</b>", $number_of_logins)."</td></tr>
						<tr bgcolor=white><td>".($konzernid ? innertable("<b>Onlinezeit Spieler</b>", round($different_players_onlinetime_by_pcid[$pcid][$konzernid]/3600)."h (".(round($different_players_onlinetime_by_pcid[$pcid][$konzernid]/$time_pcid_online[$pcid]*100)).")%") :"")."</td><td>".innertable("<b>Onlinezeit alle</b>", round($time_pcid_online[$pcid]/3600)."h")."</td></tr>
						".($konzernid ? "<tr bgcolor=white><td>".innertable("<b>Spieler</b>", $statusdata[syndicate]." (#".$statusdata[rid].")")."</td>
						<td>".innertable("<b>Username</b>", $userdata[username])."</td></tr>":"")."
					</table>
				</td></tr></table><br><br>

				<table cellpadding=8 width=100% border=0><tr>
				<td valign=top align=right>$list_players_by_relative_onlinetime</td></tr>
				<tr><td valign=top align=center>$sessions_ausgabe</td>
				</tr></table><table><tr><td>";

	}
}



//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////




echo $ausgabe_end;


function innertable($arg1, $arg2) {
	return "<table width=100%><tr><td>$arg1</td><td align=center>$arg2</td></tr></table>";
}


?>
