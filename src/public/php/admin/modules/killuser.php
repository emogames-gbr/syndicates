<?

$self = "index.php";


if ($who) {
	$who_temp = preg_replace("/ {2,}/", " ", $who);
	$who_temp = preg_replace("/\r/", "", $who_temp);
	$who_temp = preg_replace("/\f/", "", $who_temp);
	$data_step1 = explode("\n", $who_temp);
	foreach ($data_step1 as $vl) {
		$data_step2 = explode(",", $vl);
		foreach ($data_step2 as $vl2) {
			$data_step3 = explode(" ", $vl2);
			foreach ($data_step3 as $vl3) {
				if (preg_match("/^\d+$/", $vl3)): $konzids[] = $vl3; $konzernids_total[] = $vl3; endif;
				if (preg_match("/^%(\d+)$/", $vl3, $temp)): $userids[] = $temp[1]; endif;
				if (preg_match("/^&(\d+)$/", $vl3, $temp)): $synids[] = $temp[1]; endif;
			}
		}
	}

	if ($userids) {
		$userdata = assocs("select id, konzernid, username from users where id in (".join(",", $userids).") and konzernid > 0", "konzernid");
		if ($userdata) {
			foreach ($userdata as $ky => $vl) { $konzernids_total[] = $ky; }
		}
	}

	if ($synids) {
		$syndata = singles("select id from status where rid in (".join(",", $synids).") and alive > 0 order by rid asc");
		if ($syndata) {
			foreach ($syndata as $ky => $vl) { $konzernids_total[] = $vl; }
		}
	}

	if ($konzernids_total) {
		$reason_formatted = preg_replace("/\n[\r\f]*/", "<br>", $reason);
		$reason_formatted = preg_replace("/[\n\r\f]/", "", $reason_formatted);
		if ($multidelete): $multidelete = "<input type=hidden name=multidelete value=1>"; endif;
		$empfdata = assocs("select status.id, status.syndicate, status.rid, users.id as user_id from status,users where status.id in (".join(",", $konzernids_total).") and status.alive > 0 and status.id=users.konzernid", "id");
		if ($empfdata && $inneraction != 1) {
			if ($userdata) {
				$ausgabe .= "<br><br>Folgende Spieler konnten anhand der eingetragenen Userids (oberste Priorität) ermittelt werden:<br><table cellpadding=3><tr><td>Userid</td><td>Konzernname</td><td>Konzernid</td></tr>";
				foreach ($userdata as $ky => $vl) {
					$ausgabe .= "<tr><td>".$vl[id]."</td><td>".$empfdata[$ky][syndicate]." (#".$empfdata[$ky][rid].")</td><td>$".$ky."</td></tr>";
					$done[$ky] = 1;
				}
				$ausgabe .= "</table>";
			}
			if ($konzids) {
				foreach ($konzids as $vl) {
					if ($empfdata[$vl] && !$done[$vl]) {
						$temp_konzidausgabe .= "<tr><td>$".$vl."</td><td width=20>&nbsp;</td><td>".$empfdata[$vl][syndicate]." (#".$empfdata[$vl][rid].")</td></tr>";
						$done[$vl] = 1;
					}
				}
				if ($temp_konzidausgabe): $ausgabe .= "<br><br>Folgende Spieler konnten anhand der eingetragenen Konzernids (zweitoberste Priorität) ermittelt werden:<br><table cellpadding=3>$temp_konzidausgabe</table>"; endif;
			}
			if ($syndata) {
				foreach ($syndata as $ky => $vl) {
					if (!$done[$vl]) {
						$temp_synidausgabe .= "<tr><td>$".$vl."</td><td width=20>&nbsp;</td><td>".$empfdata[$vl][syndicate]." (#".$empfdata[$vl][rid].")</td></tr>";
					}
				}
				if ($temp_synidausgabe): $ausgabe .= "<br><br>Folgende Spieler konnten anhand der eingetragenen Syndikatsids (niedrigste Priorität) ermittelt werden:<br><table cellpadding=3>$temp_synidausgabe</table>"; endif;
			}
			$ausgabe .= "<br><br>Wollen Sie diese Spieler wegen folgendem Grund<br><br><table border=1><tr><td width=500>$reason_formatted</td></tr></table><br><br>wirklich löschen?<br><br><form action=$self method=post>$multidelete<input type=hidden name=action value=ku><input type=hidden name=who value=\"$who\"><input type=hidden name=reason value=\"$reason\"><input type=hidden name=inneraction value=1><input type=submit value=JA></form>";
		}
		elseif ($empfdata && $inneraction == 1) {
			$reason_formatted = addslashes($reason);
			$maxmultidelete = single("select multi_id from users order by multi_id desc limit 1");
			$multinumber = $maxmultidelete + 1;
			foreach ($empfdata as $ky => $vl) {
				$count++;
                kill_den_konzern($ky,0,1);
                select("insert into admin_users_punished (user_id,time,reason,action,adminuser) values (".$vl[user_id].",$time,'$reason_formatted','konzern gelöscht','$adminuser')");
			}
			if ($count > 1 && $multidelete) {
				foreach ($empfdata as $ky => $vl) {
					 select("update users set multi_id = $multinumber where id = ".$vl[user_id]);
				}
			}
			$ausgabe .= "Die Spieler ($count Stück) wurden erfolgreich gelöscht.";
		}
		elseif (!$empfdata) { $barrieroff = 1; $ausgabe .= "Keine Konzerne gefunden!! Eingetragene Konzerne wahrscheinlich schon tot oder nicht existent<br><br>"; }
	}
	else { $barrieroff = 1; $ausgabe .= "Keine Konzerne gefunden!! Möglicherweise fehlerhafte Eingabe.<br><br>"; }
}

if (!$who or $barrieroff) {

$ausgabe .= "
<br><br>
<form action=$self method=post><input type=hidden name=action value=ku>
Löschgrund eintragen:<br><br>
<textarea name=reason cols=68 rows=5>$message</textarea><br><br><br>
Zu löschende Spieler eintragen:<br><br>
<table cellpadding=8><tr><td><textarea name=who cols=35 rows=10>$who</textarea><br><input type=checkbox name=multidelete checked> Als Multis markieren (bei >1 Spieler)</td><td width=250>Eingabe: Zahlen (ids)<br><br>kein Präfix: Konzernid<br>% Präfix: Userid<br>& Präfix: Syndikatsid<br><br>Trennung mehrerer Spieler per Leerzeichen, neue Zeile oder Komma.</td></tr></table>
<br><br><input type=submit value=weiter>

</form>";
}






	$ausgabe = "<center><a href=$self>zurück</a></center>
				<br><br>
				$ausgabe
				<br><br><br>
				<center><a href=$self>zurück</a></center>";





?>
