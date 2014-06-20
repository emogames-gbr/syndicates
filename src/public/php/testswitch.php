<?
//**************************************************************************
// Testconfig, R46, o19 - nur Testversion                                               
//**************************************************************************
require_once("../../inc/ingame/game.php");

if ($game[name] == "Syndicates Testumgebung"){
//**************************************************************************
// Benötigte Variablen
//**************************************************************************
	if (!$arid) $arid=$status[rid];
	$nextrid = get_next_rid($arid);
	$lastrid = get_last_rid($arid);
	$players = assocs("select id,syndicate,race,land from status where rid=$arid ORDER BY syndicate");

//**************************************************************************
// Öffnet Admin-Session im neuen Konzern
//**************************************************************************
	if($action == "switch" && $submit){
		$existing = single("select count(*) from status where id = ".$target_id);
		if ($existing) {
			$adminsession = createkey();
			select("insert into sessionids_admin (sessionid, angelegt_bei, gueltig_bis, ip, user_id, adminuser) values ('$adminsession', $time, ".($time + 20 * 60).", '".getenv ("REMOTE_ADDR")."', ".$target_id.", 'Testing')");
			setcookie ("dontusepacket", 1, -1,"/", "");
			setcookie ("adminsessionid", $adminsession, -1,"/", "");
			header("Location: testconfig.php");
		}
		else f("Ziel existiert nicht.");
	}
//**************************************************************************
// Zeigt Konzernauswahl-Formular
//**************************************************************************
	else{
		// Macht KOnzerne wieder aktiv
		if($_GET['action'] == 'active'){
			select('update `status` set `lastlogintime` = '.$time);
			s('Alle Konzerne wieder aktiv :o)');
		}
		// Setzt werte zurück
		if($_GET['action'] == 'resett'){
			
			// ha auf max 15000 setzen
			select('UPDATE status SET land = 15000 WHERE land > 15000');
			select('UPDATE status SET 
				powerplants = 0, depots = 0, sciencelabs = 0, tradecenters = 0, ressourcefacilities = 0,
				spylabs = 0, factories = 0, buildinggrounds = 0, offtowers = 0, deftowers = 0, mines = 0, 
				s_tradecenters = 0, s_sciencelabs = 0, s_powerplants = 0, s_ressourcefacilities = 0, 
				multiprod = 0, seccenters = 0, armories = 0, ecocenters = 0, behemothfactories = 0,
				workshops = 0, schools = 0, banks = 0, radar = 0 
			WHERE land < (powerplants + depots + sciencelabs + tradecenters + ressourcefacilities +
				spylabs + factories + buildinggrounds + offtowers + deftowers + mines + 
				s_tradecenters + s_sciencelabs + s_powerplants + s_ressourcefacilities + 
				multiprod + seccenters + armories + ecocenters + behemothfactories +
				workshops + schools + banks + radar)');
			
			// Ressourcen zrücksetzen
			select('UPDATE status SET podpoints =100000000 WHERE podpoints >100000000');
			select('UPDATE status SET money =100000000 WHERE money >100000000');
			select('UPDATE status SET energy =100000000 WHERE energy >100000000');
			select('UPDATE status SET metal =100000000 WHERE metal >100000000');
			select('UPDATE status SET sciencepoints =100000000 WHERE sciencepoints >100000000');
			
			// Lager zurücksetzen
			select('UPDATE syndikate SET podmoney =100000000 WHERE podmoney >100000000');
			select('UPDATE syndikate SET podenergy =100000000 WHERE podenergy >100000000');
			select('UPDATE syndikate SET podmetal =100000000 WHERE podmetal >100000000');
			select('UPDATE syndikate SET podsciencepoints =100000000 WHERE podsciencepoints >100000000');
			s('Konzerne, Syndikate etc. resettet');
		}
				// Setzt werte zurück
		if($_GET['action'] == 'resett_boerse'){
			$temp = single("SELECT `COLUMN_DEFAULT` FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE `TABLE_SCHEMA` = 'dev_syndicates' and `TABLE_NAME` = 'syndikate' and `COLUMN_NAME` = 'aktienkurs'");
			select('UPDATE syndikate SET aktienkurs = '.$temp);
			select('UPDATE status SET aktien_wahl = \'\'');
			select('TRUNCATE `aktien`');
			select('TRUNCATE `aktienlogs`');
			select('TRUNCATE `aktien_dividenden`');
			select('TRUNCATE `aktien_dividenden_detail`');
			select('TRUNCATE `aktien_gebote`');
			select('TRUNCATE `aktien_logs`');
			select('TRUNCATE `aktien_privat`');
			select('TRUNCATE `aktien_privatlogs`');
			select('TRUNCATE `aktien_safekurse`;');
			s('Die Börse wurde zurückgesetzt (nächsten Tick abwarten)');
		}
		
		// Führt manuellen Tick aus
		if ($_GET['action'] == 'manual_tick') {
			$temp = system("cd ../../crons/ && php update.php syndicates",$temp);
		}
		
		// Kriegszeit zurücksetzen, damit Krieg startet
		if ($_GET['action'] == 'krieg_2350back') {
			// alle Kriege um 23h 50min zurücksetzen
			select("UPDATE wars SET starttime = starttime - 3600*23-3000"); 
			s('Der Kriegsbeginn aller Kriege wurde erfolgreich um 23h 50min in die Vergangenheit verschoben.');
		}
		
		// Urlaubszeit zurücksetzen, damit Urlaub startet
		if ($_GET['action'] == 'urlaub_24back') {
			// alle Urlaube um 24h zurücksetzen
			select("UPDATE options_vacation SET starttime = starttime - 3600*24"); 
			s('Der Urlaubsbeginn aller Spieler (die Urlaub aktiviert haben) wurde erfolgreich um 24h in die Vergangenheit verschoben.');
		}
		
		// Erstellt konzerne
		if ($_GET['action'] == 'createKonzerne') {
			if ($globals['roundstatus'] != 0) {
				f("Ist nur vor Rundenstart möglich!!!!");
			} else {
				$users = assocs("SELECT * FROM users", 'id');
				$round = $globals['round'];
				$i = 0;
				foreach($users as $userid => $vl) {
					
					$rid = 0;
					$race = 'uic';
					$rulername = $vl['username'];
					$syndicate = $vl['username'];
					$username = $vl['username'];
					
					$ctime = $globals['roundstarttime'];
					$utime = $ctime+PROTECTIONTIME;;
					$ltime = $globals['roundstarttime'];
					
					$may_access_boards = 1;
					
					if (!$vl['konzernid']) {
						$i++;
						select("insert into status (lastupdatetime,race,rid,rulername,syndicate,createtime,unprotecttime,lastlogintime,may_access_boards".
								") values ($time,'$race','$rid','$rulername','$syndicate','$ctime','$utime','$ltime','$may_access_boards'".
								");");
					 	
						$konzernid = single("select id from status where syndicate='$syndicate'");
						 
						select("update users set konzernid=$konzernid,lastroundplayed=$round where id=$userid");
						
						select("insert into stats (user_id,konzernid,username,syndicate,race,rulername,rid,round,isnoob) values ('$userid','$konzernid','$username','$syndicate','$race','$rulername','$rid','$round',".($stats_isnoob ? $stats_isnoob : 0).")");
					}
				}
				s("Es wurden erfolgreich $i Konzerne erstellt.");
			}
		}
		
		$ausgabe = "<table width=550 style=\"border:1px solid\" class=i cellpadding=2><tr><td>
		Mit dieser Seite könnt ihr einfach im Spiel die Konzerne wechseln. Bitte achtet aber darauf, dass ihr keine Konzerne konfiguriert 
		oder spielt, mit denen aktuell jemand Anderer testet. Markiert des halb bitte <a href=\"http://board.emogames.de/thread.php?threadid=22740\" target=_blank>HIER</a>, welche Konzerne ihr aktuell in Benutzung habt.
		Wenn ihr Konzerne nicht mehr zum testen benötigt, tragt das bitte auch im entsprechenden Thread ein.
		</td></tr></table>
		<br />
		<form action=\"testswitch.php?action=switch\" method=\"POST\">
			<table cellpadding=\"5\" cellspacing=\"1\" border=\"0\" width=550 class=\"tableOutline\" >
			<tr>
				<td align=center class=\"tableHead\" colspan=2>Konzern wechseln</td>
			</tr>
				<tr>
					<td class=\"tableInner1\" align=left>
							Syndikat wählen: <br>
							<a class=\"linkaufTableInner\" href=\"testswitch.php?arid=$lastrid\"><<</a>
							(#<input name=arid value=$arid size=3>) <input type=submit name=changesyn value=\"wählen\">
							 <a class=\"linkaufTableInner\" href=\"testswitch.php?arid=$nextrid\">>></a>
					</td>
					<td class=\"tableInner1\" align=left>
						Spieler:
						<select name=target_id>";
								foreach ($players as $value) {
									$selected = ($value[id] == $target_id) ? " selected" : "";
									if ($value[id] != $status[id]) {
										$ausgabe.="
											<option value=\"$value[id]\"".$selected.">[$value[race]] $value[syndicate] ($value[land]ha)</option>
										";
									}
									else {
										$ausgabe.="
											<option disabled value=\"$value[id]\">[$value[race]] $value[syndicate] - ($value[land]ha - aktuell)</option>
										";
									}
								}
								if (count($players) == 0) {
									$ausgabe.="
										<option value=0>Keine Spieler in diesem Syndikat gefunden</option>
									";
								}
							$ausgabe.="
						</select>
						<input type=submit name=submit value=\"Konzern wechseln\">
					</td>
				</tr>
			</table>
		</form>
		<br />
		<br />
		<br />
		<form method=\"get\" action=\"testswitch.php\">Alle Konzern wieder atktiv machen: <input type=\"hidden\" name=\"action\" value=\"active\"><input type=\"submit\" value=\"GO!\"></form>
		<form method=\"get\" action=\"testswitch.php\">Werte normalisieren (kappt Lagerguthaben, HP, Ressourcen, Land (15k ha), etc. ): <input type=\"hidden\" name=\"action\" value=\"resett\"><input type=\"submit\" value=\"GO!\"></form>
		<form method=\"get\" action=\"testswitch.php\">Börse zurücksetzen: <input type=\"hidden\" name=\"action\" value=\"resett_boerse\"><input type=\"submit\" value=\"GO!\"></form>
		<form method=\"get\" action=\"testswitch.php\">Kriegsbeginn vorziehen (ALLE Kriege um 23h 50min): <input type=\"hidden\" name=\"action\" value=\"krieg_2350back\"><input type=\"submit\" value=\"GO!\"></form>
		<form method=\"get\" action=\"testswitch.php\">Urlaubsbeginn vorziehen (ALLE Urlaubsbeginne (WTF) um 24h): <input type=\"hidden\" name=\"action\" value=\"urlaub_24back\"><input type=\"submit\" value=\"GO!\"></form>
		<h1>KRITISCHE EINGRIFFE</h1>
		<p>Nur mit Vorsicht benützen!!</p>
		<form method=\"get\" action=\"testswitch.php\">Manuellen Tick auslösen: <input type=\"hidden\" name=\"action\" value=\"manual_tick\"><input type=\"submit\" value=\"GO!\"></form>
		<form method=\"get\" action=\"testswitch.php\">Für jeden User ohne Konzern einen erstellen: <input type=\"hidden\" name=\"action\" value=\"createKonzerne\"><input type=\"submit\" value=\"GO!\"></form>";
	}
//**************************************************************************
// Header, Ausgabe, Footer
//**************************************************************************
	require_once("../../inc/ingame/header.php");
	echo $ausgabe;
	require_once("../../inc/ingame/footer.php");
}
else f("Diese Seite steht auf diesem Server leider nicht zur Verfügung.");
?>