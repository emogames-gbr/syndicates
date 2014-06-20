<?

##
###	TODO: Tutorial-Tracking
### 	: Anzeige von Messages/Boardnachrichten
##


// Trackuser-Skript

// Useraktionen nachvollziehen und bewerten

// Elementare Anzeigewerte:
// Std-Stuff
// Anmeldezeitpunkt
// Angemeldet ueber Referer
// Angemeldet ueber Krawall/Emogames
// Anzahl Logins (Zeitraeume)
// Erinnerungs-Mail gesendet
// Direktlink auf traceuser skript


//
// Komplexe Anzeigewerte:
//	Board-Messages
//	Ingame Nachrichten (Eingang + Ausgang)
//	Tutorial Steps
// 	Detaillierung Logins


DEFINE(OVERVIEW_CAT_1,24*60*60);
DEFINE(OVERVIEW_CAT_2,24*60*60*2);
DEFINE(OVERVIEW_CAT_3,24*60*60*3);
DEFINE(OVERVIEW_CAT_4,24*60*60*7);
echo " 
	<h3><a href=\"index.php?action=trackuser\">Usertracking</a></h3>
";

// Einzelnen Spieler analysieren
if ($ia == "view_player" && $u_id > 0 && strlen($sid) > 0) {
	view_echo_konzern($u_id);
	view_echo_session($u_id,$sid);
	
}
else if ($ia == "view_player" && $u_id > 0) {
	view_echo_konzern((int)$u_id);
}
// Keine Aktion - Übersicht
else {
	
	view_echo_overview();

}

##
##	View Functions
##


# Overview
function view_echo_overview() {
	$time = time();

	$current_players = array(0 => array(), 1=> array(), 2 => array(), 3 => array());

	$limit_time = $time - OVERVIEW_CAT_4;

	// Relevante Spieler aus DB holen
	//$new_players = assocs("select * from users where createtime >= $limit_time and konzernid > 0");
	$new_players = assocs("select * from users u, status s where u.konzernid=s.id and s.createtime >= $limit_time");
 
	// Spieler in Current Players Array einsortieren
	foreach ($new_players as $temp) {
		if ($time- $temp[createtime]  < OVERVIEW_CAT_1) {
			$current_players[0][] = $temp;
		}
		else if ($time- $temp[createtime] < OVERVIEW_CAT_2) {
			$current_players[1][] = $temp;
		}
		else if ($time- $temp[createtime] < OVERVIEW_CAT_3) {
			$current_players[2][] = $temp;
		}
		else if (($time- $temp[createtime]) < OVERVIEW_CAT_4) {
			$current_players[3][] = $temp;
		}
	
	}

	$ausgabe = "
	<table border=\"1\"><tr><td colspan=\"4\" width=\"1024\" align=\"center\">
			<b>Neue Spieler der letzten ".(OVERVIEW_CAT_4/(24*60*60))." Tage</b>
		</td></tr>
		<tr>
			<td>".(OVERVIEW_CAT_1/(24*60*60))." Tage alt</td>
			<td>".(OVERVIEW_CAT_2/(24*60*60))." Tage alt</td>
			<td>".(OVERVIEW_CAT_3/(24*60*60))." Tage alt</td>
			<td>".(OVERVIEW_CAT_4/(24*60*60))." Tage alt</td>
		</tr>
		<tr>";
		
		// Loop ueber Current players
		foreach ($current_players as $k => $v) {
		
			$ausgabe.="<td><table>";
			if (count($v) > 0) {
				foreach ($v as $player) {
					$ausgabe.="
						<tr valign=\"top\">
	
							<td><a href=\"index.php?action=trackuser&ia=view_player&u_id=".$player[id]."\">$player[username]</a></td>
							<td>".mytime($player[createtime])."</td>
							<td>".mytime($player[lastlogintime])."</td>
	
	
						</tr>
					";
				}
			}
			else {
				$ausgabe.="<tr><td></td></tr>";
			}
			$ausgabe.="</table></td>";

		}
		
	$ausgabe.="	
		</tr>
	</table>
	";	

	echo $ausgabe;

}

# Konzernansicht
function view_echo_konzern($user_id) {
	$user 						= assoc("select * from users where id = $user_id ");
	$status 					= assoc("select * from status where id=$user[konzernid]");
	$messages_received 			= assocs("select * from messages where user_id=$status[id]");
	$count_messages_read 		= single("select count(*) from messages where user_id=$status[id] and gelesen > 0");
	$messages_sent   			= assocs("select * from messages where sender=$status[id]");
	$sessions 					= assocs("select * from (select angelegt_bei,gueltig_bis,ip,pc_identifier,browsername,sessionid,hostname from sessionids_safe where user_id=$status[id] 
											UNION
										select angelegt_bei,gueltig_bis,ip,pc_identifier,browsername,sessionid,hostname from sessionids_actual where user_id=$status[id]) as a order by angelegt_bei desc");
	
	
	$postings 					= assocs("select * from board_messages where kid=$status[id]");
	$referrer 					= assoc("select * from referrers where user_id=$user[id]");
	
	
	$hits = array();
	
	
	$ausgabe= "
	<table border=\"1\">
		<tr>
			<td colspan=\"100\" align=\"center\"><b>Übersicht User: $user[username] - erstellt am:".mytime($user[createtime])."</b></td>
		</tr>
		
		<tr>
		<!-- User -->
		<td>
			<table border=\"1\">
				<tr>
					<td colspan=\"4\"><b>Userübersicht</b></td>
				</tr>
				<tr>
					<td>Letzter Login:</td>
					<td align=\"center\">".mytime($user[lastlogintime])."</td>
				</tr>
				<tr>
					<td>Anzahl Logins:</td>
					<td align=\"center\">".count($sessions)."</td>
				</tr>
				<tr>
					<td>Registriert durch Koins?</td>
					<td align=\"center\">".$user[reg_by_koins]."</td>
				</tr>			
				<tr>
					<td>Geworben von:</td>
					<td align=\"center\">".$user[werber_id]."</td>
				</tr>
				<tr>
					<td>Reminder gesendet?</td>
					<td align=\"center\">".$status[inactivity_reminder_sent]."</td>
				</tr>
				<tr>
					<td>Referrer:</td>
					<td align=\"center\">".$referrer[referrer]." um ".mytime($referrer[time])."
				</tr>

			
			</table>
		</td>
		
		<!-- Konzern -->
		<td>
			<table border=\"1\">
				<tr>
					<td colspan=\"4\"><b>Konzernübersicht</b></td>
				</tr>

				
				<tr>
					<td>Konzern:</td>
					<td>$status[syndicate] (#$status[rid]) erstellt: ".mytime($status[createtime])."</td>
					<td>
						<a href=\"index.php?action=traceuser&ia=trace&konid=$status[id]\" target=\"_blank\">History</a> |
						<a href=\"index.php?action=idswitch&ia=directlogin&target=$status[id]\" target=\"_blank\">Direktlogin</a>
					</td>
				</tr>
				<tr>
					<td>Kon-ID:</td>
					<td>".$status[id]."</td>
				</tr>		
				<tr>
					<td>Verschickte / Empfangene / Gelesene Nachrichten:</td>
					<td>".count($messages_sent)." / ".count($messages_received)." / $count_messages_read </td>
				</tr>
				<tr>
					<td>Beiträge Synboard:</td>
					<td>".count($postings)."</td>
				</tr>
			
			</table>
		</td>
		</tr>
		
		<tr>
			<td colspan=\"20\" align=\"center\"><b>Sessions</b></td>
		</tr>
		<tr>
			<td colspan=\"20\">
		";
		if (count($sessions) > 0) {
			$ausgabe.="
				<table border=\"1\">
					<tr>
						<td>Details</td>
						<td width=\"150\">Von</td>
						<td width=\"150\">Bis</td>
						<td width=\"80\">Dauer</td>
						<td>Ip</td>
						<td>Browser</td>
						<td>Hostname</td>
					</tr>
			";
			foreach ($sessions as $session) {
				$ausgabe.="
					<tr>
						<td><a href=\"index.php?action=trackuser&ia=view_player&u_id=$user[id]&sid=$session[sessionid]\">Check</a></td>
						<td>".mytime($session[angelegt_bei])."</td>
						<td>".mytime($session[gueltig_bis])."</td>
						<td align=\"center\"><b>".(ceil(($session[gueltig_bis]-$session[angelegt_bei])/60))." Min</b></td>
						<td>".$session[ip]."</td>
						<td>".$session[browsername]."</td>
						<td>".$session[hostname]."</td>
					</tr>
				";
			}
			$ausgabe.="
				</tr>
			</table>
			";				
		}
		$ausgabe.="
			</td>
		</tr>
		

		

		
		
		
	</table>
	
		<!-- Postings -->
		<div id=\"\" style=\"display:none;\">
		</div>
	
		<!-- Messages -->
		<div id=\"\" style=\"display:none;\">
		<table>
		<tr>
			<td><b>Empfangen</b></td>
			<td><b>Gesendet</b></td>
		</tr>	
		<tr>
			<td></td>
			<td></td>
		</tr>
		</table>	
		</div>
		

	
	
	";
	
	
	
	
	
	echo $ausgabe;
	
}


# Sessionansicht
function view_echo_session($user_id,$sid) { 
	$user 						= assoc("select * from users where id = $user_id ");
	$session 					= assoc("select * from (select angelegt_bei,gueltig_bis,ip,pc_identifier,sessionid,browsername,hostname from sessionids_safe where user_id=$user[konzernid] and sessionid='".$sid."' 
											UNION
										select angelegt_bei,gueltig_bis,ip,pc_identifier,browsername,sessionid,hostname from sessionids_actual where user_id=$user[konzernid]  and sessionid='".$sid."') as a order by angelegt_bei desc limit 1");
	$clicks						= assocs("select * from 
												(select * from heaptable where user_id=$user[konzernid] and clicktime >$session[angelegt_bei] and clicktime < $session[gueltig_bis] 
														UNION
													select * from heaptable2 where user_id=$user[konzernid] and clicktime >$session[angelegt_bei] and clicktime < $session[gueltig_bis])
													as a order by clicktime asc");
	$pages 						= assocs("select * from pages","id");
	
	$ausgabe="
		<table>
			<tr>
				<td align=\"center\"><b>Sessionverlauf</b></td>
			</tr>
			";
	
			foreach ($clicks as $click) {
				$ausgabe.="
					<tr>
						<td>
							".mytime($click[clicktime])."
						</td>
						<td>
							".$pages[$click[seite]][name]."
						</tr>
					</tr>
				";
			}
	
			$ausgabe.="
		</table>
	";
	echo $ausgabe;
	
	
}


?>
