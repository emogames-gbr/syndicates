<?

include("inc/general.php");

$case_id = int($case_id);
if ($action and $action != "adduser" and $action != "deluser" and $action != "reply" and $action != "edit" and $action != "closecase" and $action != "opencase" and $action != "makegeneral" and $action != "strafefestlegen" and $action != "zustimmen" and $action != "sendmessage"): $action = ""; endif;

if ($pl == 3 && $action) { // 3 == Game-Master Supervisor Rolle. Darf nix machen außer gucken.
  f("Achtung: Als Game-Master Supervisor darfst du nicht als Game-Master tätig werden. Es wurde daher keine Aktion durchgeführt.<br><br>");
  $action = "";
}

$case_type_zuordnungen = array(
	0 => "Mitteilungen",
	1 => "Multi-Reports",
	2 => "Sitting-Reports",
	3 => "Konzernbild",
	4 => "Konzernname",
	5 => "Konzernbeschr.",
	6 => "Syndikatsbanner",
	7 => "Syndikatswebsite",
	8 => "Syndikatsbeschreibung",
	9 => "Forenbeiträge",
	10 => "Sonstiges"
);


$casedata = assoc("select * from admin_case where id = '$case_id'");
if ($casedata) {

	// Admin_Case_View_History Loggin

		list($isexisting,$isfavorit) = row("select id, isfavorit from admin_case_view_history where case_id = $case_id and user_id = $id");
		if (!$isexisting) select("insert into admin_case_view_history (case_id, user_id, firstviewtime, lastviewtime, isfavorit) values ($case_id, $id, $time, $time, 0)");
		else select("update admin_case_view_history set lastviewtime = $time where id = $isexisting");


	$punishments = assocs("select * from admin_punishment_settings order by id asc", "id");
	$weiter = " <a href=$page.php?case_id=$case_id class=ver11w>... weiter ...</a>";
	$weitermain = " <a href=main.php class=ver11w>... weiter ...</a>";
	$zurueck = " <a href=$page.php?case_id=$case_id class=ver11w>... zurück ...</a>";

	$casemessages = assocs("select * from admin_case_messages where case_id = $case_id order by time asc", "id");
	$involved = assocs("select * from admin_case_involved where case_id = $case_id order by status asc", "user_id");


	$uids = array();
	$uids[] = $casedata['starter_id'];
	$uids[] = $casedata['processor_id'];
	$uids[] = $casedata['closer_id'];
	foreach ($casemessages as $vl) {
		if ($vl['sender_id']) $uids[] = $vl['sender_id'];
		if ($vl['receiver_id']) $uids[] = $vl['receiver_id'];
	}
	foreach ($involved as $ky => $vl) {
		$uids[] = $ky;
	}

	// Ein Join über den Status-Table ist nur nötig, wenn der Case aus der aktuellen Runde stammt und somit die Konzerndaten relevant bzw. zum Case passen
	if ($casedata['starttime'] < $globals['roundstarttime']) {
		$userdata = assocs("select * from users where id in (".join(",", $uids).")", "id");
	} else {
		// Ansonsten aktueller Case -> Join über status und Users-Table, um Konzerndaten zu bekommen
		$userdata = assocs("select users.id as user_id, users.username, users.email as email, status.syndicate, users.konzernid, status.rid from users, status where users.konzernid = status.id and users.id in (".join(",", $uids).")" , "user_id");
		$userdata_temp = assocs("select * from users where id in (".join(",", $uids).")", "id");
		foreach ($userdata_temp as $ky => $vl) {
			if (!$userdata[$ky]) {
				$userdata[$ky] = $vl;
			}
		}
	}


	///// Initialisierung ENDE
	///// Ab hier verfügbare Daten:
	/////	$userdata enthält nach user_id sortiert die Daten sämtlicher User, je nach Casedatum mit oder ohne Daten des aktuellen Konzerns des Users



	if ($action == "reply")	{
		if ($ia)	{
			if (!$message)	{
				f("Sie haben keinen Beitrag geschrieben!");
				$ia = "";
			}
			if ($ia)	{
				$message = mres(htmlentities($message, ENT_QUOTES));
				select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,1)");
				select("update admin_case set lastchangetime=$time where id=$case_id");
				s("Ihre Antwort wurde hinzugefügt.$weiter");
			}
		}
		if (!$ia)	{
			select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
			if (!$message) {
				if ($mid && $casemessages[$mid]) {
					$temp_mid = $mid;
					$temp_kid = $casemessages[$mid]['sender_id'];
					$temp_text = $casemessages[$mid]['message_text'];
					$message = "[QUOTE][b]Original von ".$userdata[$temp_kid]['username'];
					$message .= "[/b]
[i]".$temp_text."[/i]
[/QUOTE]";
				}
			}
			$actionausgabe .= "
					<form action=$page.php method=post><input type=hidden name=action value=reply><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id>
					<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
						<table width=598 border=0 cellpadding=3 cellspacing=0>
							<tr><td height=10 colspan=2></td></tr>
							<tr><td width=100 align=right valign=top><strong>Ihre Antwort:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
							<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	}
	elseif ($action == "sendmessage" && $globals['roundstarttime'] < $casedata['starttime'] && $userdata[$uid]['konzernid'])	{
		if ($involved[$uid]) {
			if ($ia)	{
				if (!$message)	{
					f("Sie haben keinen Beitrag geschrieben!");
					$ia = "";
				}
				if ($ia)	{
					$uid = int($uid);
					$message = htmlentities($message, ENT_QUOTES);
					$message = preg_replace("/\n\r?\f?/", "<br>", $message);
					$message = mres($message);
					$subject = mres(htmlentities($subject, ENT_QUOTES));
					select("insert into admin_case_messages (case_id, message_text, sender_id, time, type, subject, receiver_id) values ($case_id,'$message',$id,$time,2,'$subject','$uid')");
					select("update admin_case set lastchangetime=$time where id=$case_id");
					select("insert into messages (user_id, sender, time, betreff, message) values (".$userdata[$uid]['konzernid'].", 0, $time, '$subject (#".$case_id.")', '$message')");
					s("Die Nachricht wurde erfolgreich verschickt.$weiter");
				}
			}
			if (!$ia)	{
				select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
				$actionausgabe .= "
						<form action=$page.php method=post><input type=hidden name=action value=sendmessage><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id><input type=hidden name=uid value=$uid>
						<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
							<table width=598 border=0 cellpadding=3 cellspacing=0>
								<tr><td height=10 colspan=2><strong>Message an den Spieler ".print_person($uid, $casedata['starttime'], $userdata).":</strong></td></tr>
								<tr><td width=100 align=right>Betreff</td><td align=center><input type=text name=subject value=\"$subject\" size=53></td></tr>
								<tr><td width=100 align=right valign=top><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
								<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Message senden\"></td></tr>
							</table>
						</td></tr></table></form>";
			}
		} else $action = "";
	}
	elseif ($action == "edit")	{
			if ($mid && $casemessages[$mid] && $casemessages[$mid]['type'] == 1)	{
					if (($casemessages[$mid]['sender_id'] == $id or $pl >= 2) and $casemessages[$mid]['time'] + 7*24*3600 > $time)	{ # Super-Gamemods oder höher dürfen Beiträge anderer Gamemods editieren, ansonsten nur eigene Beiträge; Beiträge dürfen keine 7 Tage alt sein (verhindert, dass man uns das Zeug später irgendwann mal alles weglöscht
						if ($ia)	{
							if (!$message && !$delete)	{
								f("Sie haben keinen Beitrag geschrieben!");
								$ia = "";
							}
							if ($ia)	{
								if ($delete)	{
									if ($pl >= 3) {
										select("delete from admin_case_messages where id = $mid");
										if ($casemessages[$mid]['time'] == $casedata['lastchangetime'])	{
											select("update admin_case set lastchangetime = ".single("select time from admin_case_messages where case_id = $case_id order by time desc limit 1")." where id = $case_id");
										}
										s("Der Beitrag wurde soeben erfolgreich gelöscht.$weiter");
									} else { f("Sie dürfen keine Beiträge löschen!$zurueck"); }
								}
								else	{
									$message = htmlentities($message, ENT_QUOTES);
									$message = preg_replace("/\n\r?\f?/", "<br>", $message);
									$message = mres($message);
									select("update admin_case_messages set message_text='$message\n\nDieser Beitrag wurde am ".date("d. M, H:i:s")." von ".$userdata[$id]['username']." editiert.' where id=$mid");
									s("Der Beitrag wurde soeben geändert.$weiter");
								}
							}
						}
						if (!$ia)	{
							$actionausgabe .= "
									<form action=$page.php method=post><input type=hidden name=action value=edit><input type=hidden name=ia value=finish><input type=hidden name=mid value=$mid><input type=hidden name=case_id value=$case_id>
									<table width=598 border=0 cellpadding=1 cellspacing=0 align=center><tr><td>
										<table width=598 border=0 cellpadding=3 cellspacing=0 class=ver12s>
											<tr><td height=10 colspan=2></td></tr>
											<tr><td width=100 align=right valign=top><strong>Zu editierender Antwortbeitrag:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>".$casemessages[$mid]['message_text']."</textarea></td></tr>
											".($pl >= 2 ? "<tr><td width=100 height=30></td><td width=498 align=center><strong>Beitrag löschen:</strong> <input type=checkbox value=\"1\" name=delete></td></tr>":"")."
											<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Editieren\"></td></tr>
										</table>
									</td></tr></table></form>";
						}
					} else { f("Sie haben keine Berechtigung diesen Beitrag zu editieren!$zurueck"); }
			}	else { f("Kein Beitrag zum Editieren ausgewählt!$zurueck");}
	}
	elseif ($action == "strafefestlegen" && (($casedata['processor_id'] == $id or $casedata['status'] == 2) && $casedata['starttime'] >= $globals['roundstarttime']) && $casedata['status'] != 5)	{
		$values = array('punishment_id' => 'selectpunishment', 'fazit_user' => 'reason_extern', 'fazit_intern' => 'reason_intern', 'konzernbild_deleted' => 'delete_konzernbild', 'konzernname_deleted' => 'delete_konzernname', 'konzernbeschreibung_deleted' => 'delete_konzernbeschreibung', 'multizuordnung' => 'multizuordnung');
		$checkboxes = array('konzernbild_deleted' => "Konz.bild löschen", 'konzernname_deleted' => "Konz.name löschen u. Hülse geb.", 'konzernbeschreibung_deleted' => "Konz.beschr. löschen");
		if ($ia)	{

				foreach ($involved as $iuid => $iarray) {

					// Ändern der Werte der Postvariablen bei Checkboxen in boolean_int
					foreach ($checkboxes as $vl => $dump) {
						if ($_POST[$values[$vl].$iuid]) $_POST[$values[$vl].$iuid] = 1;
					}


					$c = $involved[$iuid]['punishment_id'] == 0 ? 1:0;
					$flags[$iuid]['first_time_specified'] = $c;

					// Für jeden Parameter gucken, ob sich was geändert hat zu den letzten Einstellungen
					$t = 0;
					foreach ($values as $ky => $vl) {
						if (!$_POST[$vl.$iuid]) $_POST[$vl.$iuid] = 0; // Sorgt dafür, dass "0 == (nichts)" kein false ergibt
						$t = (!$c && $involved[$iuid][$ky] != $_POST[$vl.$iuid]) ? 1:$t;
						$flags[$iuid]['change'.$ky] = (!$c && $involved[$iuid][$ky] != $_POST[$vl.$iuid]) ? 1:0;
						// Werte in das Involved-Array übernehmen
						$involved[$iuid][$ky] = $_POST[$vl.$iuid];
					}
					//Generelles Changeflag
					$flags[$iuid]['change'] = $t;
				}
				$linehighlighter_by_involved_user_id = array();
				foreach ($involved as $iuid => $iarray) {
					if (($iarray['punishment_id'] > 1 or $iarray['konzernbild_deleted'] or $iarray['konzernname_deleted'] or $iarray['konzernbeschreibung_deleted']) and !$iarray['fazit_user']) {
						$linehighlighter_by_involved_user_id[$iuid] = 1;
						f("Es fehlt bei mindestens einem Spieler das Fazit_extern!");
						unset($ia);
					}
				}
				if ($ia) { #$ia kann bis hierhin schon unsetted worden sein, wenn Fazit_extern irgendwo fehlt, wo bestraft wird
					$successanhaengsel = "<br>Die festgelegten Strafen werden in weniger als 1 Minute vollzogen.";
					$newstatus = 4;
					$strafen_festlegen_change_message = array();
					$strafen_festlegen_neu_message = array();
					$something_new = 0;
					$something_changed = 0;
					$messageanhaengsel = "";
					foreach ($involved as $iuid => $iarray) {
						$updatestring = array();
						foreach ($iarray as $key => $value) {
							$updatestring[] = "$key='$value'";
						}
						select("update admin_case_involved set ".join(",", $updatestring)." where case_id=$case_id and user_id = $iuid");
						$existing = assoc("select * from admin_case_involved_punishment_confirmation where involved_id = ".$iarray['id']." and gamemaster_id = $id");
						if ($existing) select("update admin_case_involved_punishment_confirmation set decision = 1 where id = ".$existing['id']);
						else {
							select("insert into admin_case_involved_punishment_confirmation (involved_id, gamemaster_id, decision) values ('".$iarray['id']."','".$id."','1')");
						}
						// Ab hier wird die Case-Message erzeugt
						$checkboxdata = "";
						foreach ($checkboxes as $ky => $vl) {
							$checkboxdata .= "<font color=".($iarray[$ky] ? "red":"black").">".($iarray[$ky] ? "JA":"NEIN")." $vl</font><br>";
							if ($flags[$iuid]['change'.$ky]) $flags[$iuid]['changecheckboxes'] = 1;
						}
						if ($flags[$iuid]['first_time_specified']) {
							$something_new = 1;
							$strafen_festlegen_neu_message[] = "<tr class=ver9s bgcolor=white><td>".$iarray['multizuordnung']."</td><td><font color=".($iarray['status'] == 0 ? "green":"red").">".print_person($iuid, $casedata['starttime'], $userdata)."</font></td><td>".$punishments[$iarray['punishment_id']]['bezeichnung']."</td><td>$checkboxdata</td><td>".$iarray['fazit_user']."</td><td>".$iarray['fazit_intern']."</td></tr>";
						}
						elseif ($flags[$iuid]['change']) {
							$something_changed = 1;
							$strafen_festlegen_change_message[] = "<tr class=ver9s bgcolor=white><td".($flags[$iuid]['change'.'multizuordnung'] ? " bgcolor=yellow":"").">".$iarray['multizuordnung']."</td><td><font color=".($iarray['status'] == 0 ? "green":"red").">".print_person($iuid, $casedata['starttime'], $userdata)."</font></td><td".($flags[$iuid]['change'.'punishment_id'] ? " bgcolor=yellow":"").">".$punishments[$iarray['punishment_id']]['bezeichnung']."</td><td".($flags[$iuid]['change'.'checkboxes'] ? " bgcolor=yellow":"").">$checkboxdata</td><td".($flags[$iuid]['change'.'fazit_user'] ? " bgcolor=yellow":"").">".$iarray['fazit_user']."</td><td".($flags[$iuid]['change'.'fazit_intern'] ? " bgcolor=yellow":"").">".$iarray['fazit_intern']."</td></tr>";
							// Wenn sich an der Hauptbestrafung (das aus der Auswahlbox) etwas ändert, werden die bisherigen Zustimmungen auf 0 gesetzt, das heißt die Leute müssen ihre Zustimmung erneut geben.
							if ($flags[$iuid]['changepunishment_id']) {
								select("update admin_case_involved_punishment_confirmation set decision = 0 where involved_id = ".$iarray['id']." and gamemaster_id != $id");
								$messageanhaengsel = "<br><br><b>ACHTUNG: Da mindestens eine Bestrafung geändert wurde, sind die bisher erfolgten Zustimmungen und Ablehnungen der Bestrafungen der entsprechenden Spieler zurück gesetzt worden.!</b>";
							}
						}

						// Ab hier wird nochmal geguckt, welchen neuen Status der Case bekommt, ob z.B. schon genug Gamemaster für alle Strafen zugestimmt haben
						$gesamtconfirmation = single("select sum(decision) from admin_case_involved_punishment_confirmation where decision <= 1 and involved_id = ".$iarray['id']);
						if ($punishments[$iarray['punishment_id']]['needed_confirmation_count'] > $gesamtconfirmation) {
							$successanhaengsel = "<br>Du hast mindestens eine Strafe festgelegt, die der Zustimmung weiterer Gamemaster bedarf. Sobald genügend Zustimmungen für alle festgelegten Strafen gegeben sind, werden alle Strafen durchgeführt.";
							$newstatus = 3;
						}
					}
					if ($something_new or $something_changed) {
						s("Du hast die Strafen erfolgreich ".($something_new ? "festgelegt":"geändert").".$successanhaengsel<br>$weiter");
						if ($strafen_festlegen_change_message){
							$strafen_festlegen_change_message = join("", $strafen_festlegen_change_message);
							//$strafen_festlegen_change_message = htmlentities($strafen_festlegen_change_message, ENT_QUOTES);
							$strafen_festlegen_change_message = preg_replace("/\n\r?\f?/", "<br>", $strafen_festlegen_change_message);
							//$strafen_festlegen_change_message = mres($strafen_festlegen_change_message);
							select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id, '<b>".$user['username']."</b> ändert die Strafen wie folgt ab:<br><br><table width=100% align=center class=ver10s cellpadding=2 cellspacing=0><tr class=head><td>Multi?</td><td>Spieler</td><td>Bestrafung</td><td></td><td>Fazit extern</td><td>Fazit intern</td></tr>".$strafen_festlegen_change_message."</table>$messageanhaengsel', $id, $time, 4)");
						}
						if ($strafen_festlegen_neu_message){
							$strafen_festlegen_neu_message = join("", $strafen_festlegen_neu_message);
							//$strafen_festlegen_neu_message = htmlentities($strafen_festlegen_neu_message, ENT_QUOTES);
							$strafen_festlegen_neu_message = preg_replace("/\n\r?\f?/", "<br>", $strafen_festlegen_neu_message);
							//$strafen_festlegen_neu_message = mres($strafen_festlegen_neu_message);
							select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id, '<b>".$user['username']."</b> <b>legt</b> die Strafen wie folgt fest:<br><br><table width=100% align=center class=ver10s cellpadding=2 cellspacing=0><tr class=head><td>Multi?</td><td>Spieler</td><td>Bestrafung</td><td></td><td>Fazit extern</td><td>Fazit intern</td></tr>".$strafen_festlegen_neu_message."</table>', $id, $time, 4)");
						}
						select("update admin_case set status=$newstatus, lastchangetime = $time".($newstatus == 4 ? ", closer_id=$id":"")." where id = $case_id");
					} else i("Du hast keine Änderungen vorgenommen.$weiter");
				}
			//$message = "<b>".$user['username']." öffnet und übernimmt den Case.<br><br></b><br><table width=70% class=opening_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
			//select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,0)");
			//select("update admin_case set lastchangetime=$time, endtime=0, status=1, closer_id=0, processor_id = $id where id=$case_id");
			//s("Der Case wurde geöffnet und Ihnen zugewiesen.$weiter");
		}
		if (!$ia)	{
			select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
			$actionausgabe .= "<center><b>Strafen festlegen</b></center>
					<form action=$page.php method=post><input type=hidden name=action value=strafefestlegen><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id>
					<table width=100% border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
						<table width=100% border=0 cellpadding=3 cellspacing=0>
						<tr class=head><td>Multi?</td><td>Spieler</td><td>Bestrafung</td><td></td><td>Fazit extern (geht an User!)</td><td>Fazit intern</td></tr>
						";

						foreach ($involved as $involved_user_id => $array) {

							$selectdata = "";
							$checkboxdata = "";
							$multizuordnung = "";
							foreach ($punishments as $punishment_id => $parray) {
								$valid = 1;
								if ($parray['lifetime_limitation'] > 0) {
									$count = single("select count(*) from admin_case_involved where punishment_id = $punishment_id and user_id = $involved_user_id");
									if ($count >= $parray['lifetime_limitation']) $valid = 0;
								}
								$selectdata .= "<option value=$punishment_id".($array['punishment_id'] == $punishment_id ? " selected":"").">".$parray['bezeichnung'].($parray['needed_confirmation_count'] > 1 ? " (".$parray['needed_confirmation_count'].")":"")."</option>";
							}
							foreach ($checkboxes as $ky => $vl) {
								$checkboxdata .= "<input type=checkbox name=".$values[$ky].$involved_user_id.($array[$ky] ? " checked":"")."> <font class=ver9s>$vl</font><br>";
							}

								$multizuordnung = "<option value=0>--</option>";
							for ($i = 1; $i <= floor(count($involved)/2); $i++) {
								$multizuordnung .= "<option value=$i".($array['multizuordnung'] == $i ? " selected":"").">$i</option>";
							}
							$multizuordnung = "<select name=multizuordnung$involved_user_id>$multizuordnung</select>";


							$actionausgabe .= "<tr class=ver10s".($linehighlighter_by_involved_user_id[$involved_user_id] ? " bgcolor=orange":"")."><td>$multizuordnung</td><td><font color=".($array['status'] == 0 ? "green":"red").">".print_person($involved_user_id, $casedata['starttime'], $userdata)."</font></td><td><select name=selectpunishment$involved_user_id>$selectdata</select></td><td>$checkboxdata</td><td><textarea name=reason_extern$involved_user_id cols=30 rows=10>".$array['fazit_user']."</textarea></td><td><textarea name=reason_intern$involved_user_id cols=30 rows=10>".$array['fazit_intern']."</textarea></td></tr>";
						}

						$actionausgabe .= "
							<tr><td colspan=6 align=center><input type=submit value=\"... go ...\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	}
	elseif ($action == "zustimmen" && $casedata['status'] == 3 && $casedata['starttime'] >= $globals['roundstarttime'])	{
		if ($ia)	{
				$confirmationdata = array();
				$linehighlighter_by_involved_user_id = array();
				$exists = array();
				foreach ($involved as $iuid => $iarray) {
					if ($punishments[$iarray['punishment_id']]['needed_confirmation_count'] > 1) {
						$confirmationdata[$iuid] = assoc("select * from admin_case_involved_punishment_confirmation where involved_id = ".$iarray['id']." and gamemaster_id = $id");
						$exists[$iuid] = $confirmationdata[$iuid] ? 1 : 0;

						// Festlegen der Variablen, die später für die Case-Message bestimmen, ob sich überhaupt etwas geändert hat, falls der GM seine Entscheidung später korrigiert
						if ($confirmationdata[$iuid]['decision'] != $_POST['decision'.$iuid]) $confirmationdata[$iuid]['decisionchange'] = 1;
						if ($confirmationdata[$iuid]['comment'] != $_POST['comment'.$iuid]) $confirmationdata[$iuid]['commentchange'] = 1;

						$confirmationdata[$iuid]['decision'] = $_POST['decision'.$iuid];
						$confirmationdata[$iuid]['comment'] = $_POST['comment'.$iuid];
						if ($confirmationdata[$iuid]['decision'] == -1 && !$confirmationdata[$iuid]['comment']) {
							$linehighlighter_by_involved_user_id[$iuid] = 1;
							f("Es fehlt bei mindestens einem Spieler die Begründung, warum Du keine Zustimmung gegeben hast!");
							unset($ia);
						}
					}
				}

				if ($ia) { #$ia kann bis hierhin schon unsetted worden sein, wenn Fazit_extern irgendwo fehlt, wo bestraft wird
					$successanhaengsel = "<br>Mit dem nächsten Update werden die Strafen vollzogen.";
					$newstatus = 4;
					$zustimmungs_neu_message = array();
					$zustimmungs_change_message = array();
					$something_new = 0;
					$something_changed = 0;
					foreach ($involved as $iuid => $iarray) {
						if ($punishments[$iarray['punishment_id']]['needed_confirmation_count'] > 1) {
							$updatestring = array();
							if ($exists [$iuid]) {
								if ($confirmationdata[$iuid]['decisionchange'] or $confirmationdata[$iuid]['commentchange']) {
									select("update admin_case_involved_punishment_confirmation set decision = ".$confirmationdata[$iuid]['decision'].", comment = '".$confirmationdata[$iuid]['comment']."' where id = ".$confirmationdata[$iuid]['id']);
									$decision = "<font color=".($confirmationdata[$iuid]['decision'] == -1 ? "red":($confirmationdata[$iuid]['decision'] == 1 ? "green":"orange")).">".($confirmationdata[$iuid]['decisionchange'] ? "<b>":"")."".($confirmationdata[$iuid]['decision'] == -1 ? "nein":($confirmationdata[$iuid]['decision'] == 1 ? "ja":"enthaltung"))."".($confirmationdata[$iuid]['decisionchange'] ? "</b>":"")."</font>";
									$zustimmungs_change_message[] = "<tr class=ver9s><td bgcolor=white width=20%><font color=".($array['status'] == 0 ? "green":"red").">".print_person($iuid, $casedata['starttime'], $userdata)."</font></td><td bgcolor=white width=13%>".$punishments[$iarray['punishment_id']]['bezeichnung']."</td><td bgcolor=white width=7%>$decision</td><td class=ver9w width=60%>".($confirmationdata[$iuid]['commentchange'] ? "<b>":"").$confirmationdata[$iuid]['comment'].($confirmationdata[$iuid]['commentchange'] ? "</b>":"")."</td></tr>";
									$something_changed = 1;
								}
							} else {
								select("insert into admin_case_involved_punishment_confirmation (involved_id, gamemaster_id, decision, comment) values (".$iarray['id'].", $id, ".$confirmationdata[$iuid]['decision'].", '".$confirmationdata[$iuid]['comment']."')");
								$decision = "<font color=".($confirmationdata[$iuid]['decision'] == -1 ? "red":($confirmationdata[$iuid]['decision'] == 1 ? "green":"orange")).">".($confirmationdata[$iuid]['decision'] == -1 ? "nein":($confirmationdata[$iuid]['decision'] == 1 ? "ja":"enthaltung"))."</font>";
								$zustimmungs_neu_message[] = "<tr class=ver9s><td bgcolor=white width=20%><font color=".($array['status'] == 0 ? "green":"red").">".print_person($iuid, $casedata['starttime'], $userdata)."</font></td><td bgcolor=white width=13%>".$punishments[$iarray['punishment_id']]['bezeichnung']."</td><td bgcolor=white width=7%>$decision</td><td class=ver9wwidth=60%>".$confirmationdata[$iuid]['comment']."</td></tr>";
								$something_new = 1;
							}
							$gesamtconfirmation = single("select sum(decision) from admin_case_involved_punishment_confirmation where decision <= 1 and involved_id = ".$iarray['id']);
							if ($punishments[$iarray['punishment_id']]['needed_confirmation_count'] > $gesamtconfirmation) {
								$successanhaengsel = "<br>Mindestens eine festgelegte Strafe bedarf der Zustimmung eines weiteren/weiterer Gamemaster. Sobald genügend Zustimmungen für alle festgelegten Strafen gegeben sind, werden alle Strafen durchgeführt.";
								$newstatus = 3;
							}
						}
					}
					if ($something_new or $something_changed) {
						s("Deine Zustimmungen/Ablehnungen wurden eingetragen. $successanhaengsel<br>$weiter");
						if ($zustimmungs_change_message) select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id, '<b>".$user['username']."</b> ändert seine Zustimmung bezüglich der festgelegten Strafen wie folgt:<br><br><table width=100% align=center class=ver10s cellpadding=2 cellspacing=0>".join("", $zustimmungs_change_message)."</table>', $id, $time, 4)");
						if ($zustimmungs_neu_message) select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id, '<b>".$user['username']."</b> <b>gibt</b> seine Zustimmung bezüglich der festgelegten Strafen wie folgt:<br><br><table width=100% align=center class=ver10s cellpadding=2 cellspacing=0>".join("", $zustimmungs_neu_message)."</table>', $id, $time, 4)");
						select("update admin_case set status=$newstatus, lastchangetime = $time where id = $case_id");
					} else i("Du hast keine Änderungen vorgenommen.$weiter");
				}
			//$message = "<b>".$user['username']." öffnet und übernimmt den Case.<br><br></b><br><table width=70% class=opening_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
			//select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,0)");
			//select("update admin_case set lastchangetime=$time, endtime=0, status=1, closer_id=0, processor_id = $id where id=$case_id");
			//s("Der Case wurde geöffnet und Ihnen zugewiesen.$weiter");
		}
		if (!$ia)	{
			select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
			$actionausgabe .= "<center><b>Zustimmungen geben/verweigern</b></center>
					<form action=$page.php method=post><input type=hidden name=action value=zustimmen><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id>
					<table width=100% border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
						<table width=100% border=0 cellpadding=3 cellspacing=0>
						<tr class=head><td>Multi?</td><td>Spieler</td><td>Bestrafung</td><td>Fazit extern</td><td>Fazit intern</td><td>Zustimmung</td><td>Begr. (falls k. Zust.)</td></tr>
						";

						foreach ($involved as $involved_user_id => $array) {
							if ($punishments[$array['punishment_id']]['needed_confirmation_count'] > 1) {
								if (!$confirmationdata[$involved_user_id]) {
									$confirmationdata[$involved_user_id] = assoc("select * from admin_case_involved_punishment_confirmation where involved_id = ".$array['id']." and gamemaster_id = $id");
								}
								$auswahl = "<select name=decision$involved_user_id>";
								$auswahl .= "<option value=1".($confirmationdata[$involved_user_id]['decision'] == 1 ? " selected":"")."><font color=green>JA</font></option>";
								$auswahl .= "<option value=-1".($confirmationdata[$involved_user_id]['decision'] == -1 ? " selected":"")."><font color=red>NEIN</font></option>";
								$auswahl .= "<option value=2".($confirmationdata[$involved_user_id]['decision'] == 2 ? " selected":"")."><font color=orange>Enthaltung</font></option>";
								$auswahl .= "</select>";
								$actionausgabe .= "<tr class=ver10s".($linehighlighter_by_involved_user_id[$involved_user_id] ? " bgcolor=orange":"")."><td>".$array['multizuordnung']."</td><td class=ver10s><font color=".($array['status'] == 0 ? "green":"red").">".print_person($involved_user_id, $casedata['starttime'], $userdata)."</font></td><td>".$punishments[$array['punishment_id']]['bezeichnung']."</td><td>".umwandeln_bbcode_old($array['fazit_user'])."</td><td>".umwandeln_bbcode_old($array['fazit_intern'])."</td><td>$auswahl</td><td><textarea name=comment$involved_user_id cols=15 rows=3>".$confirmationdata[$involved_user_id]['comment']."</textarea></td></tr>";
							}
						}

						$actionausgabe .= "
							<tr><td colspan=7 align=center><input type=submit value=\"... go ...\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	}
	elseif ($action == "opencase" && $casedata['status'] == 5)	{
		if ($ia)	{
			if (!$message)	{
				f("Sie haben keine Begründung geschrieben, weshalb Sie den Case wieder öffnen möchten!");
				$ia = "";
			}
			if ($ia)	{
				$message = mres(htmlentities($message, ENT_QUOTES));
				$message = "<b>".$user['username']." öffnet und übernimmt den Case.<br><br></b><br><table width=70% class=opening_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
				select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,0)");
				select("update admin_case set lastchangetime=$time, endtime=0, status=1, closer_id=0, processor_id = $id where id=$case_id");
				s("Der Case wurde geöffnet und Ihnen zugewiesen.$weiter");
			}
		}
		if (!$ia)	{
			select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
			$actionausgabe .= "<center><b>Diesen Case öffnen und übernehmen</b></center>
					<form action=$page.php method=post><input type=hidden name=action value=opencase><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id>
					<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
						<table width=598 border=0 cellpadding=3 cellspacing=0>
							<tr><td height=10 colspan=2></td></tr>
							<tr><td width=100 align=right valign=top><strong>Begründung:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
							<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	}
	elseif ($action == "deluser" && $casedata['status'] < 4)	{
		if ($involved[$uid]) {
			$uid = int($uid);
			if ($ia)	{
				if (!$message)	{
					f("Sie haben keine Begründung geschrieben, weshalb Sie den Spieler aus dem Case entfernen möchten!");
					$ia = "";
				}
				if ($ia)	{
					$message = mres(htmlentities($message, ENT_QUOTES));
					$message = "<br><b>".$user['username']."</b> entfernt den Spieler <b>".print_person($uid, $casedata['starttime'], $userdata)."</b> aus dem Case.<br><br><table width=70% class=system_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
					select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,4)");
					select("delete from admin_case_involved where case_id = $case_id and user_id = $uid");
					select("update admin_case set lastchangetime=$time where id=$case_id");
					s("Der Spieler wurde aus dem Case entfernt.$weiter");
				}
			}
			if (!$ia)	{
				select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
				$actionausgabe .= "<center><b>Den Spieler ".print_person($uid, $casedata['starttime'], $userdata)." aus dem Case entfernen</b></center>
						<form action=$page.php method=post><input type=hidden name=action value=deluser><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id><input type=hidden name=uid value=$uid>
						<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
							<table width=598 border=0 cellpadding=3 cellspacing=0>
								<tr><td height=10 colspan=2></td></tr>
								<tr><td width=100 align=right valign=top><strong>Begründung:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
								<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
							</table>
						</td></tr></table></form>";
			}
		} else $action = "";
	}
	elseif ($action == "makegeneral" && $casedata['status'] == 1)	{
		if ($id == $casedata['processor_id']) {
			if ($ia)	{
				$message = "<br><b>".$user['username']."</b> deklariert den Case als allgemein.";
				select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,4)");
				select("update admin_case set lastchangetime=$time, status=2 where id=$case_id");
				s("Der Case wurde als allgemein deklariert.$weiter");
			}
			if (!$ia)	{
				$actionausgabe .= "<center><b>Den Case wirklich als allgemein markieren?</b><br><br><a href=$page.php?action=makegeneral&case_id=$case_id&ia=1 class=ver12s>JA</a></center>";
			}
		} else $action = "";
	}
	elseif ($action == "adduser" && $casedata['status'] < 4)	{
		$uid = int($uid);
		if (!$involved[$uid]) {
			if ($ia)	{
				if (!$message)	{
					f("Sie haben keine Begründung geschrieben, weshalb Sie den Spieler zu dem Case hinzunehmen möchten!");
					$ia = "";
				}
				if ($ia)	{
					$message = mres(htmlentities($message, ENT_QUOTES));
					$message = "<br><b>".$user['username']."</b> fügt den Spieler <b>".print_person($uid, $casedata['starttime'], $userdata)."</b> dem Case hinzu.<br><br><table width=70% class=system_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
					select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,4)");
					select("insert into admin_case_involved (case_id, user_id, status) values ($case_id, $uid, 1)");
					select("update admin_case set lastchangetime=$time where id=$case_id");
					s("Der Spieler wurde dem Case hinzugefügt.$weiter");
				}
			}
			if (!$ia)	{
				select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
				$actionausgabe .= "<center><b>Den Spieler ".print_person($uid, $casedata['starttime'], $userdata)." dem Case hinzufügen</b></center>
						<form action=$page.php method=post><input type=hidden name=action value=adduser><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id><input type=hidden name=uid value=$uid>
						<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
							<table width=598 border=0 cellpadding=3 cellspacing=0>
								<tr><td height=10 colspan=2></td></tr>
								<tr><td width=100 align=right valign=top><strong>Begründung:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
								<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
							</table>
						</td></tr></table></form>";
			}
		} else $action = "";
	}
	elseif ($action == "closecase" && $casedata['status'] < 5)	{
		if ($ia)	{
			if (!$message)	{
				f("Sie haben keine Begründung geschrieben, weshalb Sie den Case schließen möchten!");
				$ia = "";
			}
			if ($ia)	{
				$message = mres(htmlentities($message, ENT_QUOTES));
				$message = "<b>".$user['username']." schließt den Case.<br><br></b><br><table width=70% class=closing_message><tr><td valign=top width=50><b>Begründung:</b></td><td align=left>$message</td></tr></table>";
				select("insert into admin_case_messages (case_id, message_text, sender_id, time, type) values ($case_id,'$message',$id,$time,5)");
				select("update admin_case set lastchangetime=$time, endtime=$time, status=5, closer_id=$id where id=$case_id");
				s("Der Case wurde geschlossen.$weitermain");
			}
		}
		if (!$ia)	{
			select("update admin_sessionids set gueltig_bis = gueltig_bis + 1800 where user_id = $id and gueltig_bis-1800 < $time");
			$actionausgabe .= "<center><b>Diesen Case schließen</b></center>
					<form action=$page.php method=post><input type=hidden name=action value=closecase><input type=hidden name=ia value=finish><input type=hidden name=case_id value=$case_id>
					<table width=598 border=0 cellpadding=1 cellspacing=0 class=ver11s align=center><tr><td>
						<table width=598 border=0 cellpadding=3 cellspacing=0>
							<tr><td height=10 colspan=2></td></tr>
							<tr><td width=100 align=right valign=top><strong>Begründung:</strong><br><br>BBCode aktiv</td><td width=498 align=center><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
							<tr><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	} else $action = "";

	if (!$action) {
		// Case-Message-Ausgabe erzeugen
		$case_message_ausgabe_optionen = "<tr><td align=left width=10><strong><a href=main.php class=ver11s>Zurück zur Cases-Übersicht</a></strong> </td><td align=right width=50% colspan=2><strong><a href=$page.php?action=reply&case_id=$case_id class=ver11s>Antworten</a></strong></td></tr>";
		$case_message_ausgabe = "<table class=ver12s border=1 width=100% cellpadding=0 cellspacing=1>$case_message_ausgabe_optionen";
		foreach ($casemessages as $vl) {
			$classchoise = "ver12s";
			$case_message_ausgabe .= "<tr><td colspan=2><table align=center width=100% class=ver12s border=0 cellpadding=3 cellspacing=0>";
			if ($vl['type'] == 0) {		// Opening Message
				$classchoice = "opening_message";
				$case_message_ausgabe .= "<tr valign=top>
											<td class=\"$classchoice\" colspan=3 width=100% valign=top align=center>".$vl['message_text']."</td>
										</tr>
										<tr valign=top>
											<td width=80% class=\"$classchoice\" colspan=3 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td>
										</tr>";
			}
			elseif ($vl['type'] == 1) { // Kommentar eines GM
				$classchoice = (++$int % 2 == 1 ? "gm_message_1" : "gm_message_2");
				$case_message_ausgabe .= "<tr height=70>
											<td width=180 align=middle valign=top class=\"$classchoice\" nowrap><br><strong class=ver12s>".$userdata[$vl['sender_id']]['username']."</strong></td>
											<td class=\"$classchoice\" colspan=2 width=100% valign=top>".umwandeln_bbcode_old($vl['message_text'])."</td>
										</tr>
										<tr>
											<td width=69% class=\"$classchoice\" colspan=2 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td><td class=\"$classchoice\" width=10% align=\"right\"><a href=\"$page.php?action=reply&case_id=$case_id&mid=".$vl['id']."\" class=ver11s>Zitieren</a>&nbsp;-&nbsp;<a href=\"$page.php?action=edit&case_id=$case_id&mid=".$vl['id']."\" class=ver11s>Edit</a>&nbsp;</td>
										</tr>";
			}
			elseif ($vl['type'] == 2) {	// Mitteilung an einen Spieler
				$classchoice = "message_to_player";
				$case_message_ausgabe .= "<tr valign=top class=\"$classchoice\">
											<td valign=top align=center rowspan=2 nowrap><b>".$userdata[$vl['sender_id']]['username']."</b><br><br> an <br><br>".print_person($vl['receiver_id'], $casedata['starttime'], $userdata)."</td><td width=100%>Betreff: <b>".$vl['subject']."</b></td>
										</tr>
										<tr class=\"$classchoice\"><td>".umwandeln_bbcode_old($vl['message_text'])."</td></tr>
										<tr valign=top class=\"$classchoice\">
											<td width=80% class=\"$classchoice\" colspan=3 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td>
										</tr>";
			}
			elseif ($vl['type'] == 3) { // Mitteilung von einem Spieler
				$classchoice = "message_from_player";
				$case_message_ausgabe .= "<tr valign=top class=\"$classchoice\">
											<td valign=top align=center rowspan=2 nowrap>Von<br><b>".print_person($vl['sender_id'], $casedata['starttime'], $userdata)."</b></td><td width=100%>Betreff: <b>".$vl['subject']."</b></td>
										</tr>
										<tr class=\"$classchoice\"><td>".umwandeln_bbcode_old($vl['message_text'])."</td></tr>
										<tr valign=top class=\"$classchoice\">
											<td width=80% class=\"$classchoice\" colspan=3 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td>
										</tr>";
			}
			elseif ($vl['type'] == 4) { // System-Message
				$classchoice = "system_message";
				$case_message_ausgabe .= "<tr valign=top>
											<td class=\"$classchoice\" colspan=3 width=100% valign=top align=center>".$vl['message_text']."</td>
										</tr>
										<tr valign=top>
											<td width=80% class=\"$classchoice\" colspan=3 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td>
										</tr>";
			}
			elseif ($vl['type'] == 5) { // Closing Message
				$classchoice = "closing_message";
				$case_message_ausgabe .= "<tr valign=top>
											<td class=\"$classchoice\" colspan=3 width=100% valign=top align=center>".umwandeln_bbcode_old($vl['message_text'])."</td>
										</tr>
										<tr valign=top>
											<td width=80% class=\"$classchoice\" colspan=3 valign=top nowrap>".datum("d.m.Y, H:i", $vl[time])." </td>
										</tr>";
			}
			$case_message_ausgabe .= "</table></td></tr>";
		}
		$case_message_ausgabe .= "$case_message_ausgabe_optionen</table><br><br><br>";

		$case_bestrafungs_lines = array();
		$checkboxes = array('konzernbild_deleted' => "Konz.bild löschen", 'konzernname_deleted' => "Konz.name löschen u. Hülse geb.", 'konzernbeschreibung_deleted' => "Konz.beschr. löschen");
		foreach ($involved as $iuid => $iarray) {
				$checkboxdata = "";
			foreach ($checkboxes as $ky => $vl) {
				$checkboxdata .= "<font color=".($iarray[$ky] ? "red":"black").">".($iarray[$ky] ? "JA":"NEIN")." $vl</font><br>";
				if ($flags[$iuid]['change'.$ky]) $flags[$iuid]['changecheckboxes'] = 1;
			}
			$zustimmungen = assocs("select * from admin_case_involved_punishment_confirmation where involved_id = ".$iarray['id']);
			$dafuer = 0;
			$dagegen = 0;
			$enthaltung = 0;
			foreach ($zustimmungen as $vl) {
				if ($vl['decision'] == 1) $dafuer++;
				elseif ($vl['decision'] == -1) $dagegen++;
				elseif ($vl['decision'] == 2) $enthaltung++;
			}
			$lacking = $punishments[$iarray['punishment_id']]['needed_confirmation_count'] - $dafuer + $dagegen;
			$case_bestrafungs_lines[] = "<tr class=ver11s bgcolor=white><td>".$iarray['multizuordnung']."</td><td><font color=".($iarray['status'] == 0 ? "green":"red").">".print_person($iuid, $casedata['starttime'], $userdata)."</font></td><td>".$punishments[$iarray['punishment_id']]['bezeichnung']."</td><td>$checkboxdata</td><td>".$iarray['fazit_user']."</td><td>".$iarray['fazit_intern']."</td><td align=center><font color=green>$dafuer</font> - <font color=red>$dagegen</font> - <font color=orange>$enthaltung</font></td><td align=center>$lacking</td></tr>";
		}
		$case_bestrafungs_ausgabe = "<table width=100% border=1 cellpadding=1 cellspacing=0 align=center class=ver12s><tr><td colspan=8 align=center>Festgelegte Konsequenzen</td></tr><tr class=head><td>Multi?</td><td>Spieler</td><td>Bestrafung</td><td></td><td>Fazit extern</td><td>Fazit intern</td><td>Stimmen</td><td>noch benötigt</td></tr>".join("", $case_bestrafungs_lines)."</table>";
	}


	///////////////////////
	//////////////////////
	/////////////////////



	$beteiligte_und_status = "<tr class=ver11s>
		<td valign=top width=50%>
			<table border=0 align=center valign=top cellspacing=0 cellpadding=2 class=ver11s>
				<tr><td><b>Beteiligte Personen</b></td></tr>";

				foreach ($involved as $ky => $vl) {
					$pw = EMOGAMES_getPasswordFromUsername($userdata[$ky]['username']);
					$beteiligte_und_status .= "<tr><td><font color=".($vl['status'] == 0 ? "green":"red").">".print_person($ky, $casedata['starttime'], $userdata)." <a href=\"player_specific.php?ia=calc&search=!".htmlentities($userdata[$ky]['username'])."\" target=player_specific class=ver11s>[go]</a> <a href=view_case.php?case_id=$case_id&action=deluser&uid=$ky target=main class=ver11s>[rm]</a>".(($globals['roundstarttime'] < $casedata['starttime'] && $userdata[$ky]['konzernid']) ? " <a href=view_case.php?case_id=$case_id&action=sendmessage&uid=$ky target=main class=ver11s>[message]</a>":"")."</font><br>password: 'aus sicherheitsgründen verdeckt'<br>email: ".$userdata[$ky]['email']."<br><br></td></tr>";
				}

				$beteiligte_und_status .= "
			</table>
		</td>
		<td width=50% valign=top>
			<table border=0 align=center valign=top cellspacing=0 cellpadding=3 class=ver11s>
				<tr valign=top>
				<td align=center><b>Status</b></td>
				</tr>
				<tr><td>";

				if ($casedata['status'] == 0) $beteiligte_und_status .= "<font color=red>unbearbeitet</font> (<a href=getcase.php?case_id=$case_id class=ver11s>->bearbeiten</a>)";
				elseif ($casedata['status'] == 1) $beteiligte_und_status .= "<font color=green>wird bearbeitet von <b>".$userdata[$casedata['processor_id']]['username']."</b></font>";
				elseif ($casedata['status'] == 2) $beteiligte_und_status .= "<font color=orange>als Allgemein deklariert / Bearbeitender GM wünscht Begutachtung durch andere GM</font>";
				elseif ($casedata['status'] == 3) $beteiligte_und_status .= "<font color=purple>Zur Durchführung der vorgeschlagenen Strafen wird die Zustimmung weiterer Gamemaster benötigt</font>";
				elseif ($casedata['status'] == 4) $beteiligte_und_status .= "<font color=purple>der Case wartet darauf, dass das Update die festgelegten Strafen durchführt</font>";
				elseif ($casedata['status'] == 5) $beteiligte_und_status .= "<font color=purple>der Case ist geschlossen</font>";


				$beteiligte_und_status .= "</td></tr>
			</table>
		</td>
	</tr>";

	$ausgabe = "
	<table width=100% border=0 valign=top align=center cellspacing=0 cellpadding=0 class=ver11s>
	<tr class=ver14s><td align=center colspan=2><b>".$casedata['title']."</b></td></tr>
	$beteiligte_und_status
	</table>
	<br>
	$actionausgabe
	".($casedata['status'] == 5 ? $case_bestrafungs_ausgabe."<br><br><br>":"")."
	$case_message_ausgabe

	<table width=100% border=0 valign=top align=center cellspacing=0 cellpadding=0 class=ver11s>
	$beteiligte_und_status
	</table>
	<br><br>
	$case_bestrafungs_ausgabe
	<br>
	".((($casedata['processor_id'] == $id or $casedata['status'] == 2) && $casedata['starttime'] >= $globals['roundstarttime'] && $casedata['status'] != 5) ? "<a href=$page.php?action=strafefestlegen&case_id=$case_id class=ver12s>Strafe festlegen und Case schließen</a><br>":"")
	 .(($casedata['status'] == 3 && $casedata['starttime'] >= $globals['roundstarttime']) ? "<a href=$page.php?action=zustimmen&case_id=$case_id class=ver12s>Zustimmung geben/verweigern</a><br>":"")
	 .($casedata['status'] < 5 ? "<a href=$page.php?action=closecase&case_id=$case_id class=ver12s>Case schließen (ohne Strafen, nur in Sonderfällen)</a><br>":"")
	 .($casedata['status'] == 5 ? "<a href=$page.php?action=opencase&case_id=$case_id class=ver12s>Case öffnen</a><br>":"")
	 .($isfavorit ? "<a href=case_ablage/?action=del&case_id=$case_id target=case_ablage class=ver12s>Aus Ablage entfernen</a><br>":"")
	 .(!$isfavorit ? "<a href=case_ablage/?action=add&case_id=$case_id target=case_ablage class=ver12s>In Ablage legen</a><br>":"")
	 .(($id == $casedata['processor_id'] and $casedata['status'] == 1) ? "<a href=$page.php?action=makegeneral&case_id=$case_id class=ver12s>Den Case als Allgemein markieren</a><br>":"")
	 ."<br><br><br><br>";

} else { // $casedata nicht da => ungültige Case_Id übergeben
	$ausgabe .= "Die Case-ID #$case_id ist ungültig.";
}




echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body><center>
$fehler
$successmeldung
$informationmeldung</center>
$ausgabe
</body>

</html>";





function umwandeln_bbcode_old($text) {

	preg_match_all("/\[nobreak\](.*?)\[\/nobreak\]/is", $text , $save, PREG_SET_ORDER);
	foreach ($save as $vl) {
		$text = preg_replace("/\[nobreak\](.*?)\[\/nobreak\]/is", str_replace("\n", " ", $vl[1]), $text, 1);
	}
	
	$text = preg_replace('/\n/', '<br />', $text);
	$text = preg_replace('/\[br\]/si', '<br />', $text);
	$text = preg_replace('/\[b\](.*?)\[\/b\]/si', '<font style="font-weight:bold">$1</font>', $text);
	$text = preg_replace('/\[i\](.*?)\[\/i\]/si', '<font style="font-style:italic">$1</font>', $text);
	$text = preg_replace('/\[u\](.*?)\[\/u\]/si', '<font style="text-decoration:underline">$1</font>', $text);
	$text = preg_replace('/\[center\](.*?)\[\/center\]/si', '<div style="text-align:center;">$1</div>', $text);
	$text = preg_replace('/\[ul\](.*?)\[\/ul\]/si', '<ul>$1</ul>', $text);
	$text = preg_replace('/\[ol\](.*?)\[\/ol\]/si', '<ol>$1</ol>', $text);
	$text = preg_replace('/\[li\](.*?)\[\/li\]/si', '<li>$1</li>', $text);
	$text = preg_replace('/\[ol center\](.*?)\[\/ol center\]/si', '<table width=0% align=center class=tableInner1><tr><td><ol>$1</ol></td></tr></table>', $text);
	$text = preg_replace('/\[ul center\](.*?)\[\/ul center\]/si', '<table width=0% align=center class=tableInner1><tr><td><ul>$1</ul></td></tr></table>', $text);
	$text = preg_replace('/\[quote\](.*?)\[\/quote\]/si', '<br /><table width=95% align=center class=tableInner1 cellpadding=0 cellspacing=0><tr><td>Zitat</td><tr><td><hr />$1<hr /></td></tr></table>', $text);
	
	
	$patterns = array();
	$replacements = array();
	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url\](([\w]+?://)?((www\.|ftp\.)?[^ \"\n\r\t<]*?))\[/url\]#is";
	$replacements[] = "<a href=\"http://\\3\" class=gelblink12 target=_blank>\\1</a>";

	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$patterns[] = "#\[url=([\w]+?://([^ \"\n\r\t<]*?))\](.*?)\[/url\]#is";
	$replacements[] = "<a href=\"http://\\2\" class=gelblink12 target=_blank>\\3</a>";

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url=((www|ftp)?\.[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is";
	$replacements[] = "<a href=\"http://\\1\" class=gelblink12 target=_blank>\\3</a>";

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[img\](([\w]+?://)?((www\.|ftp\.)?[^ \"\n\r\t<]*?))\[/img\]#is";
	$replacements[] = "<img src=\"http://\\3\" border=0>";

	$text = preg_replace($patterns, $replacements, $text);

	return $text;
}

?>

