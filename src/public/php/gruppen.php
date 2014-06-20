<?

//**************************************************************************//
//							Übergabe Variablen checken
//**************************************************************************//


//**************************************************************************//
//							Variablen initialisieren
//**************************************************************************//

$queries = array();
$show_username = 1; // immer Emoname anzeigen R65 ($globals['roundstatus'] == 1) xor ($globals['roundstatus'] == 0);
$users = "";

$ausgabe = "";

$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
$weiter = "<br><br><a href=gruppen.php class=linkAufsiteBg>weiter</a>";

//**************************************************************************
//							Game.php includen						
//**************************************************************************


require_once("../../inc/ingame/game.php");

//**************************************************************************
//**************************************************************************
//							Eigentliche Berechnungen!
//**************************************************************************
//**************************************************************************


//$syndikat = assoc("select * from syndikate where synd_id = ".$status["rid"]);
//$emogames_id = single("select emogames_user_id from users where konzernid = ".$status["id"]);
$user_id = single("select id from users where konzernid = ".$status["id"]);

// Einstellugnen von eigener Gruppe auslesen
$result = assoc("SELECT * FROM groups_new WHERE group_id = (SELECT group_id FROM groups_new_members WHERE user_id = '".$user_id."')");

$is_groupadmin = ($result['admin_id'] == $user_id);

require_once("../../inc/ingame/header.php");

if  ($result) {
	if ($is_groupadmin) {
		//
		//	Spieler aktivieren
		//
		if ($gaction == "activate" && is_numeric($player_id)) {
			$groupmember_count = single("SELECT COUNT(*) FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND status = 1");
			if (!single("SELECT 1 FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND user_id = '".$player_id."' AND status = 0")) {
				$tpl->assign('ERROR', "Der Spieler ist nicht in deiner Gruppe oder bereits aktiviert.");
				$tpl->display('fehler.tpl');
			} else if (MAX_USERS_A_GROUP <= $groupmember_count) {
				$tpl->assign('ERROR', "Deine Gruppe ist bereits voll besetzt.");
				$tpl->display('fehler.tpl');
			} else {
				$queries[] = "UPDATE groups_new_members SET status = 1 WHERE user_id = '".$player_id."'";
				$username = single("SELECT username FROM users WHERE id = ".$player_id);
				$group_member = assocs("SELECT user_id FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND status != 0");
				$messageinserts = array();
				$konz_id = single("select konzernid from users where id = ".$player_id);
				$messageinserts[] = "(54, ".$konz_id.", $time, '')";
				foreach ($group_member as $vl) {
					$konz_id = single("SELECT konzernid FROM users WHERE id = ".$vl["user_id"]);
					$messageinserts[] = "(36, ".$konz_id.", $time, '".$username."')";
				}
				$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
				$tpl->assign('MSG', "Sie haben den Spieler \"".$username."\" erfolgreich für ihre Gruppe für nächste Runde akzeptiert.");
				$tpl->display('sys_msg.tpl');
			}
		}
		//
		//	Spieler aus der Gruppe kicken	
		//
		else if ($gaction == "kick" && is_numeric($player_id)) {
			if (!single("SELECT 1 FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND user_id = '".$player_id."'")) {
				$tpl->assign('ERROR', "Der Spieler ist nicht in deiner Gruppe.");
				$tpl->display('fehler.tpl');
			} elseif ($ia == "finish") {
				$queries[] = "DELETE FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND user_id = '".$player_id."'";
				if ($result['nachfolger_id'] == $player_id) {
					$queries[] = "UPDATE groups_new SET nachfolger_id = 0 WHERE group_id = '".$result['group_id']."'";
				}
				$username = single("SELECT username FROM users WHERE id = ".$player_id);
				$group_member = assocs("SELECT user_id FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND status != 0");
				$messageinserts = array();
				foreach ($group_member as $vl) {
					if ($vl["user_id"] != $player_id) {
						$konz_id = single("select konzernid from users where id = ".$vl["user_id"]);
						$messageinserts[] = "(35, ".$konz_id.", $time, '".$username."')";
					} else {
						$konz_id = single("select konzernid from users where id = ".$vl["user_id"]);
						$messageinserts[] = "(37, ".$konz_id.", $time, '')";
					}
				}
				$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
				$tpl->assign('MSG', "Sie haben den Spieler \"".$username."\" erfolgreich aus ihrer Gruppe ausgeschlossen.");
				$tpl->display('sys_msg.tpl');
			} else {
				$username = single("SELECT username FROM users WHERE id = ".$player_id);
				$tpl->assign('ERROR', "<center>Möchten Sie den Spieler \"".$username."\" wirklich aus Ihrer Gruppe entfernen?<br /><br /><a href=gruppen.php?gaction=kick&player_id=".$player_id."&ia=finish class=linkAufsiteBg>Bestätigen</a> - <a href=gruppen.php class=linkAufsiteBg>Abbrechen</a></center>");
				$tpl->display('fehler.tpl'); 
			}
		}
		//
		//	Den Nachfolger/nächsten Gruppenadmin bestimmen	
		//
		else if ($gaction == "nachfolger" && is_numeric($player_id)) {
			if (!single("SELECT 1 FROM groups_new_members WHERE user_id = '".$player_id."' AND status != 0")) {
				$tpl->assign('ERROR', "Der Spieler ist nicht in deiner Gruppe oder nicht aktiviert.");
				$tpl->display('fehler.tpl'); 
			} elseif ($user_id == $player_id) {
				$tpl->assign('ERROR', "Du kannst dich selbst nicht als Nachfolger setzen.");
				$tpl->display('fehler.tpl'); 
			} else {
				$queries[] = "UPDATE groups_new SET nachfolger_id = '".$player_id."' WHERE group_id = '".$result['group_id']."'";
				$username = single("SELECT username FROM users WHERE id = '".$player_id."'");
				$tpl->assign('MSG',"\"".$username."\" ist nun ihr neuer Nachfolger.");
				$tpl->display('sys_msg.tpl');
			}
		}
		//
		//	jemand anderen als Gruppenadmin einsetzen
		//
		else if ($gaction == "admin" && is_numeric($player_id)) {
			if (!single("SELECT 1 FROM groups_new_members WHERE user_id = '".$player_id."' AND status != 0")) {
				$tpl->assign('ERROR', "Der Spieler ist nicht in deiner Gruppe oder nicht aktiviert.");
				$tpl->display('fehler.tpl'); 
			} elseif ($user_id == $player_id) {
				$tpl->assign('ERROR', "Du bist bereits Admin der Gruppe.");
				$tpl->display('fehler.tpl'); 
			} else {
				$queries[] = "UPDATE groups_new SET admin_id = '".$player_id."' WHERE group_id = '".$result['group_id']."'";
				if ($result['nachfolger_id'] == $player_id) {
					$queries[] = "UPDATE groups_new SET nachfolger_id = 0 WHERE group_id = '".$result['group_id']."'";
				}
				$username_old = single("SELECT username FROM users WHERE id = '".$user_id."'");
				$username_admin = single("SELECT username FROM users WHERE id = '".$player_id."'");
				$group_member = assocs("SELECT user_id FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND status != 0");
				$messageinserts = array();
				foreach ($group_member as $vl) {
					if ($vl["user_id"] == $player_id) {
						$konz_id = single("select konzernid from users where id = ".$vl["user_id"]);
						$messageinserts[] = "(65, ".$konz_id.", $time, '".$username_old."')";
					} else {
						$konz_id = single("select konzernid from users where id = ".$vl["user_id"]);
						$messageinserts[] = "(66, ".$konz_id.", $time, '".$username_admin."')";
					}
				}
				$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
				$tpl->assign('MSG',"\"".$username_admin."\" ist nun der neue Admin der Gruppe.");
				$tpl->display('sys_msg.tpl');
			}
		}
		//
		//	Gruppe löschen	
		//
		else if ($gaction == "delete") {
			if ($ia == "finish") {
				$queries[] = "DELETE FROM groups_new_members WHERE group_id = ".$result["group_id"];
				$queries[] = "DELETE FROM groups_new WHERE group_id = ".$result["group_id"];
				$group_member = assocs("SELECT user_id FROM groups_new_members WHERE group_id = '".$result['group_id']."'");
				$messageinserts = array();
				foreach ($group_member as $vl) {
					$konz_id = single("select konzernid from users where id = ".$vl["user_id"]);
					$messageinserts[] = "(53, ".$konz_id.", $time, '')";
				}
				$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
				$tpl->assign('MSG', "Sie haben ihre Gruppe erfolgreich gelöscht.");
				$tpl->display('sys_msg.tpl');
			} else {
				$tpl->assign('ERROR', "<center>Möchten Sie Ihre Gruppe wirklich löschen?<br /><br /><a href=gruppen.php?gaction=delete&ia=finish class=linkAufsiteBg>Bestätigen</a> - <a href=gruppen.php class=linkAufsiteBg>Abbrechen</a></center>");
				$tpl->display('fehler.tpl'); 
			}
		}
		//
		//	Beschreibung ändern	
		//
		else if ($gaction == "sonstiges") {
			if (strlen($name) < 5 || 50 < strlen($name) || strlen($ausrichtung) < 5 || 50 < strlen($ausrichtung)) {
				$tpl->assign('ERROR', "Ihr Gruppenname muss zwischen 5 und 50 Zeichen haben. Sie müssen außerdem eine Ausrichtung angeben (5-30 Zeichen)");
				$tpl->display('fehler.tpl');
			} else {
				$fuer_neulinge = ($fuer_neulinge ? 1 : 0);
				$ist_offen = ($ist_offen ? 1 : 0);
				$queries[] = "UPDATE groups_new SET " .
						"name = '".addslashes($name)."', " .
						"ausrichtung = '".addslashes($ausrichtung)."'," .
						"fuer_neulinge = '".$fuer_neulinge."'," .
						"ist_offen = '".$ist_offen."' WHERE group_id = '".$result['group_id']."'";
				$tpl->assign('MSG', "Du hast erfolgreich die Eigenschaften gespeichert.");
				$tpl->display('sys_msg.tpl');
			}
		}
		//
		//	Beschreibung ändern	
		//
		else if ($gaction == "description") {
			$description = htmlentities($description,ENT_QUOTES);
			$description = addslashes($description);
			$queries[] = "UPDATE groups_new SET description='".$description."' WHERE group_id=".$result["group_id"];
			$tpl->assign('MSG', "Beschreibung erfolgreich aktualisiert.");
			$tpl->display('sys_msg.tpl');
		} else if ($gaction == "change_des") {
			$tpl->assign('CHANGE_DESCRIPTION', true);
		}
		
		
	}
	//
	//	eigene Gruppe verlassen
	//
	if ($gaction == "leave") {
		if ($is_groupadmin && $result['nachfolger_id'] == 0) { // Wenn man kein Chef ist, einen Nachfolger hat oder sich nur noch der Chef in der Gruppe befindet
			$tpl->assign('ERROR', "Sie können als Administrator eine Gruppe nicht verlassen, solange kein Nachfolger feststeht. Sind sie der letzte in der Gruppe, dann löschen sie die Gruppe direkt ".$zurueck);
			$tpl->display('fehler.tpl');
		} elseif ($ia == "finish") {
				$queries[] = "DELETE FROM groups_new_members WHERE user_id = '".$user_id."'";
				if ($is_groupadmin) {
					$queries[] = "UPDATE groups_new SET admin_id = '".$result['nachfolger_id']."', nachfolger_id = '0' " .
							"WHERE group_id = '".$result['group_id']."'";
				}
				if ($result['nachfolger_id'] == $user_id) {
					$queries[] = "UPDATE groups_new SET nachfolger_id = 0 WHERE group_id = '".$result['group_id']."'";
				}
				$group_member = assocs("SELECT user_id FROM groups_new_members WHERE group_id = '".$result['group_id']."' AND status != 0");
				$messageinserts = array();
				$username = single("SELECT username FROM users WHERE id = '".$user_id."'");
				foreach ($group_member as $vl) {
					if ($user_id != $vl['user_id'])	{
						$konz_id = single("select konzernid from users where id = ".$vl['user_id']);
						$messageinserts[] = "(35, ".$konz_id.", $time, '".$username."')";
					}
				}
				if ($messageinserts) {
					$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
				}
				$tpl->assign('MSG', "Sie haben die Gruppe verlassen.");
				$tpl->display('sys_msg.tpl');
		} else {
			$tpl->assign('ERROR', "<center>Möchten Sie Ihre Gruppe wirklich verlassen?<br /><br /><a href=gruppen.php?gaction=leave&ia=finish class=linkAufsiteBg>Bestätigen</a> - <a href=gruppen.php class=linkAufsiteBg>Abbrechen</a></center>");
			$tpl->display('fehler.tpl');
		}
	}
} else { // Falls im Moment in keiner Gruppe
	
	//
	//	Neue Gruppe erstellen	
	//
	if($gaction == "create") {
		if (!$show_username) {
			$name = $status['syndicate'];
		} else {
			$name = single("SELECT username FROM users WHERE id = ".$user_id);
		}
		$queries[] = "INSERT INTO groups_new (name, description, createtime, admin_id) " .
				"VALUES ('".addslashes($name)."', '', ".$time.", ".$user_id.")";
		$queries[] = "INSERT INTO groups_new_members (group_id, user_id, status) " .
				"VALUES ((SELECT group_id FROM groups_new WHERE admin_id = '".$user_id."'), '".$user_id."', '1')";
		$tpl->assign('MSG', "Sie haben erfolgreich eine Gruppe angelegt.<br><br>" .
				"Erstelle im " .
				"<a href=\"http://board.emogames.de\" class=\"linkAufsiteBg\" target=\"_blank\">Emogames-Forum</a> " .
				"<a href=\"http://board.emogames.de/board.php?boardid=18\" class=\"linkAufsiteBg\" target=\"_blank\">Syndikats- / Spielersuche</a> " .
				"ein <a href=\"http://board.emogames.de/newthread.php?boardid=18\" class=\"linkAufsiteBg\" target=\"_blank\">Thema</a>, " .
				"um auf deine neue Gruppe aufmerksam zu machen und Mitstreiter zu finden.");
				
		$tpl->display('sys_msg.tpl');
	}
	//
	//	einer Gruppe beitreten	
	//
	else if ($gaction == "join" && is_numeric($group_id)) {
		$result = assoc("SELECT * from groups_new WHERE group_id = ".$group_id);
		$groupmember_count = single("SELECT COUNT(*) FROM groups_new_members WHERE group_id = '".$group_id."' AND status = 1");
		if (MAX_USERS_A_GROUP <= $groupmember_count) {
			$tpl->assign('ERROR', "In dieser Gruppe sind alle Plätze belegt.");
			$tpl->display('fehler.tpl');
		} else {
			// Nachricht schicken an Gruppenadmin
			$bewerbungstext = addslashes(htmlentities($bewerbungstext."\n\nDiese Nachricht wurde beim Beitritt automatisch generiert.",ENT_QUOTES));
			$konz_id = single("SELECT konzernid FROM users WHERE id = '".$result['admin_id']."'");
			select("insert into messages (user_id, sender, time, betreff, message) values (".$konz_id.", '".$status['id']."', $time, '<b>Bewerbung für Gruppe</b>', '$bewerbungstext')");
			// Aufnehmen in Gruppe
			$queries[] = "INSERT INTO groups_new_members (group_id, user_id) " .
					"VALUES ('".$group_id."', '".$user_id."')";
			$username = single("SELECT username FROM users WHERE id = ".$user_id);
			$queries[] = "INSERT INTO message_values (id, user_id, time, werte) " .
					"VALUES (52, ".$konz_id.", $time, '".$username."')";
			$tpl->assign('MSG', "Sie sind erfolgreich der Gruppe von <strong>\"".$result['name']."\"</strong> beigetreten.");
				$tpl->display('sys_msg.tpl');
		}
	}
	
}

if ($globals['roundstatus'] == 1) {
	db_write($queries);
} else {
	db_write($queries,1); # Für queries die auch nach Rundende ausgeführt werden
}	

///////////////////////////////////////////
//
//	Ausgabe wenn in einer Gruppe	
//
///////////////////////////////////////////
$result = assoc("SELECT * FROM groups_new WHERE group_id = (SELECT group_id FROM groups_new_members WHERE user_id = '".$user_id."')");
$is_groupadmin = ($result["admin_id"] == $user_id);

if ($force_groups) {
	$tpl->assign('ALREADY_JOINED_A_GROUP', !!$result);
	$tpl->assign('GROUP', $result);
	if (!$extra) $extra = 'all';
} else {
	if (!$extra) $extra = 'vollAusblenden';
}

if ($result && !$force_groups) {
	if (single("SELECT COUNT(*) FROM groups_new_members WHERE user_id = '".$user_id."' AND status != 0")) {
		$gaction = 'inGroup';
		$tpl->assign('IS_GROUPADMIN', $is_groupadmin);
		$groupmember = assocs("SELECT g.user_id, g.status, s.id AS konzernid, s.syndicate, s.rulername, s.rid, s.race, s.alive, s.lastlogintime, u.username " .
				"FROM groups_new_members AS g, users AS u LEFT JOIN status AS s ON (s.id = u.konzernid) " .
				"WHERE g.group_id = '".$result['group_id']."' AND g.user_id = u.id " .
				"ORDER BY u.username");
		$groupmember_output = array();
		$i = 1;
		foreach ($groupmember as $vl) {
			$vl['nr'] = $i;
			
			$vl['self'] = ($vl['user_id'] == $user_id);
			$vl['is_groupadmin'] = ($vl['user_id'] == $result['admin_id']);
			$vl['is_nachfolger'] = ($vl['user_id'] == $result["nachfolger_id"]);
			if ($vl['konzernid']) {
				$sessidsactual = assoc("select user_id, gueltig_bis from sessionids_actual where user_id=".$vl['konzernid']);
			} else {
				$sessidsactual = array();
			}
			if ($time < $sessidsactual["gueltig_bis"]) {
				$vl['online'] = 'online';
			} elseif($vl["lastlogintime"] + TIME_TILL_GLOBAL_INACTIVE < $time && $vl["alive"] != 2) {
				$vl['online'] = 'global_inaktiv';
			} else if($vl["lastlogintime"] + TIME_TILL_INACTIVE < $time && $vl["alive"] != 2) {
				$vl['online'] = 'lokal_inaktiv';
			} else {
				$vl['online'] = 'offline';
			}
			array_push($groupmember_output, $vl);
			$i++;
		}
		$tpl->assign('GROUPMEMBER_COUNT', count($groupmember_output));
		$tpl->assign('GROUPMEMBER', $groupmember_output);
		
		$result['createtime'] = datum("d.m.Y, H:i", $result['createtime']);
		
		if (!$is_groupadmin || $gaction != "change_des") {
			$result['description_edit'] = preg_replace("/\n/","<br>",$result["description"]);
		}
		$result['admin_name'] = single("SELECT username FROM users WHERE id = '".$result['admin_id']."'");
		$result['nachfolger_name'] = single("SELECT username FROM users WHERE id = '".$result['nachfolger_id']."'");
		$tpl->assign('GROUP', $result);
	} else {
		$konz = assoc("SELECT s.ID, s.syndicate, s.rid " .
				"FROM status AS s, users AS u WHERE s.id = u.konzernid AND u.id = ".$result["admin_id"]);
		$gaction = 'deactivatedInGroup';
		$tpl->assign('KONZ', $konz);
	}
} elseif ($gaction != 'join_text') {
	//
	//	Ausgabe wenn in keiner Gruppe	
	//	
	$gaction = 'noGroup';
	
	if (!$force_groups && $status['inprotection'] == 'Y') {
		$tpl->assign('INFO', "Syndicates ist ein Gruppenspiel, daher ist es wichtig sich frühzeitig Verbündete zu suchen. " .
				"Bewerben Sie sich bei einer Gruppe ihrer Wahl oder schauen sie im Forum unter Spielersuche nach passenden Mitspielern. " .
				"Wenn Sie sich bei keiner Gruppe bewerben, werden Sie zufällig einem Syndikat zugeteilt!");
		$tpl->display('info.tpl');
	}
	
	$groups = assocs("SELECT *, " .
			"(SELECT COUNT(*) FROM groups_new_members AS h WHERE h.group_id = g.group_id AND h.status = 1) AS count, " .
			"(SELECT u.konzernid FROM users AS u WHERE u.id = g.admin_id) AS admin_konzernid " .
			"FROM groups_new AS g");
	$tpl->assign('GROUPS_COUNT', count($groups));
	$tpl->assign('EXTRA', $extra);
	$groups_output = array();
	if ($groups) {
		foreach($groups as $val) {
			if ($val["description"]) {
				$val['tooltip'] = js::showover("<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" class=\"tableOutline\"><tr><td><table cellspacing=\"0\" cellpadding=\"2\"><tr><td class=\"tableInner2\" align=center><strong>Beschreibung:</strong> ".(preg_replace("/\n/","<br>",$val["description"]))."</td></tr></table></td></tr></table>");	
			} else {
				$val['tooltip'] = "";
			}
			if (!$show_username) {
				$val['konz_name'] = single("SELECT concat(s.syndicate,' (#',s.rid,')') FROM users AS u, status AS s 
					WHERE u.id = '".$val['admin_id']."' AND s.id = u.konzernid");
			} else {
				$val['konz_name'] = single("SELECT u.username FROM users AS u 
					WHERE u.id = '".$val['admin_id']."'");
			}
			if ($extra == 'all' || (MAX_USERS_A_GROUP != $val['count'] && $val['ist_offen'] != 0)) {
				array_push($groups_output, $val);
			}
		}
	}
	$tpl->assign('GROUPS', $groups_output);
} else {
	$tpl->assign('GROUP_ID', $group_id);
}

//**************************************************************************
//							Header, Ausgabe, Footer
//**************************************************************************
$tpl->assign('RIPF', $ripf);
$tpl->assign('GACTION', $gaction);
$tpl->assign('GROUPMEMBER_MAX', MAX_USERS_A_GROUP);

$tpl->display('gruppen.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************
//							Dateispezifische Funktionen					
//**************************************************************************

?>
