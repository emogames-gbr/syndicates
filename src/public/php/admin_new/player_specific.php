<?

include("inc/general.php");


$assis = array( 8 => "Forschungsassistent", 9 => "Militärassistent", 10 => "Gebäudeassistent", 7 => "Werbung deaktivieren", 11 => "Komfortpaket", 12 => "Angriffs- / Spydb");

if ($ia == "calc") {
	if ($search[0] == "|" || $search[0] == "%") $userid = substr($search, 1);
	elseif ($search[0] == "!") $koinsname = substr($search, 1);
	elseif ($search[0] == "&" || $search[0] == "§") $konzernid = substr($search, 1);
	else $konname = $search;

	$allowstatusonlysearch = false;
	$allowusersonlysearch = false;
	if ($userid) {
		$userid = floor($userid);
		$and = "users.id=$userid";
		$allowusersonlysearch = true;
	}
	if ($konzernid) {
		$konzernid = floor($konzernid);
		$and = "status.id=$konzernid";
		$allowstatusonlysearch = true;
	}
	if ($konname) {
		$konname = addslashes($konname);
		$and = "status.syndicate='$konname'";
		$allowstatusonlysearch = true;
	}
	if ($koinsname) {
		$koinsname = addslashes($koinsname);
		$and = "users.username='$koinsname'";
		$allowusersonlysearch = true;
	}
	$ustatss = assoc("select users.id as Userid,users.username as Username,status.id as Konzernid,status.syndicate as Konzernname,status.rid as Syndikatsnummer,status.race as Fraktion, status.alive as Alive
						from users,status
						where
						status.id = users.konzernid and
						$and");
	// Nur in Users suchen
	if (!$ustatss && $allowusersonlysearch) {
		$ustatss = assoc("select id as Userid, username as Username from users where $and");

	}
	// Nur im Status suchen
	if (!$ustatss && $allowstatusonlysearch) {
		$ustatss = assoc("select id as Konzernid, syndicate as Konzernname, rid as Syndikatsnummer, race as Fraktion, alive as Alive from status where $and");

	}
	if ($ustatss[Userid]) {
		$features = singles("select feature_id from features where konzernid = $ustatss[Konzernid]");
		foreach ($assis as $ky => $vl) {
			$there = 0;
			foreach ($features as $vl2) {
				if ($vl2 == $ky): $there = 1; break; endif;
			}
			$features_output .= "<tr class=ver10s><td>$vl</td><td>".($there ? "<font color=green>JA</font>":"<font color=red>NEIN</font>")."</td></tr>";
		}
	}
	
	//echo $query;
	$ausgabe_playerdata .= "<table class=\"bodys\">";
	if ($ustatss) {
		if ($ustatss['Userid']) select("insert into admin_user_view_history (target_id, user_id, time) values (".$ustatss['Userid'].", $id, $time)");
		foreach ($ustatss as $ky => $v) {
			$skip = false;
			if ($ky == "Alive") {
				if ($v != 0) $v = ($v == 1 ? "1" : "im urlaub");
				else $v = "tot / R.I.P.";
				if ($v == 1) $skip = true;
			}
				if (!$skip) $ausgabe_playerdata.="<tr><td><b>$ky</b></td><td><b>$v</b></td></tr>";
		}
		/**
		Auf Wunsch von Jannis deaktiviert - R4bbiT - 13.11.11
		if ($isadmin) {
			$ausgabe_playerdata .= "<tr><td colspan=2><hr></td></tr><tr><td colspan=2 align=center><a href=$self?action=idswitch&ia=directlogin&target=$ustatss[Konzernid] target=_blank>Direktlogin</a></td></tr>";
		}**/
		$playerfound = 1;
	}
	else { $ausgabe_playerdata .= "<tr><td>Keine Übereinstimmung</td></tr>"; $playerfound = 0; $features_output="";}
	$ausgabe_playerdata .= "</table>";
}
/**
Auf Wunsch von Jannis deaktiviert - R4bbiT - 13.11.11
elseif ($ia == "directlogin" && ($pl >= 3 || ($pl >= 1 && $game[name] == "Syndicates Testumgebung"))) {
	$target = floor($target);
	$existing = single("select count(*) from status where id = $target");
	if ($existing) {
		$adminsession = createkey();
		select("insert into sessionids_admin (sessionid, angelegt_bei, gueltig_bis, ip, user_id, adminuser) values ('$adminsession', $time, ".($time + 200 * 60).", '".getenv ("REMOTE_ADDR")."', $target, '$adminuser')");
		setcookie ("dontusepacket", 1, -1 ,"/", "");
		setcookie ("adminsessionid", $adminsession, -1 ,"/", "");
		header("Location: ../statusseite.php");
	} else { $ausgabe .= "Ziel existiert nicht!<br><br>"; }

}**/


// Anzeige bei aktuellem Case regeln
$showcasespecific = 0;
list($lastviewtime, $actual_case_id) = row("select lastviewtime, case_id from admin_case_view_history where user_id = '$id' order by lastviewtime desc limit 1");
if ($lastviewtime > $id_data['angelegt_bei']) {
	$showcasespecific = 1;
}
$case_involved_persons = assocs("select * from admin_case_involved where case_id = '$actual_case_id'", "user_id");


///////////////////////
//////////////////////
/////////////////////


$ausgabe = "
<table width=100% border=0 valign=top cellspacing=0 cellpadding=0><tr class=ver10s>
<td valign=top>
	<form action=player_specific.php>
	<input type=hidden name=ia value=calc>
	<input type=text name=search value=\"$search\"><input type=submit value=go>
	</form>
	<b>Präfixe</b><br>
	(leer) Konzernname<br>
	& oder § Konzernid<br>
	! Username<br>
	% oder | Userid<br>
</td>
<td>
".($playerfound ? "

<!--Spieler löschen<br>-->
<!--Nachricht schreiben<br>-->
".($ustatss['Konzernid'] ? "
<a href=angriffe_und_spionage_checken.php?action=checkuser&inneraction=1&target_id=".$ustatss['Konzernid']." class=ver10s target=main>Angriffe/Spionage checken</a><br>":"")."
<!--Spieler bannen<br>-->
<!--Konzernbeschreibung löschen<br>-->
<!--Konzernbild löschen<br>-->
".($ustatss['Konzernid'] ? "
<a href=traceuser.php?action=traceuser&ia=trace&konid=".$ustatss['Konzernid']." class=ver10s target=main>Aktions-History ansehen</a><br>":"")."
".($ustatss['Konzernid'] ? "
<a href=detect_ip_mates.php?konzernid=".$ustatss['Konzernid']." class=ver10s target=main>Ip-Partner suchen</a>
<br>":"")."
".($ustatss['Userid'] ? "
<br>".(!single("select count(*) from admin_user_user_ablage where user_id=$id and target_id=".$ustatss['Userid']) ? "<a href=player_ablage/?action=add&uid=".$ustatss['Userid']." class=ver10s target=player_ablage>In Ablage legen</a>":"<a href=player_ablage/?action=del&uid=".$ustatss['Userid']." class=ver10s target=player_ablage>Aus Ablage entfernen</a>")."
<br>":"")."
<br>
".($ustatss['Userid'] ? "
<a href=view_case_history.php?user_id=".$ustatss['Userid']." class=ver10s target=main>Case-History ansehen</a>
<br>".(($showcasespecific && $ustatss['Konzernid'] && $ustatss['Alive']) ? ((!$case_involved_persons[$ustatss['Userid']] ? "<a href=view_case.php?case_id=$actual_case_id&action=adduser&uid=".$ustatss['Userid']." target=main class=ver10s>Dem aktuellen Case hinzufügen</a>":
		"<a href=view_case.php?case_id=$actual_case_id&action=deluser&uid=".$ustatss['Userid']." target=main class=ver10s>Aus aktuellem Case entfernen</a>"). // Else
"<br><a href=view_case.php?case_id=$actual_case_id target=main class=ver10s>Zurück zum Case</a>"):""):""):"")."
</td>
<td>
$ausgabe_playerdata
</td>
<td>
".($features_output ? "<table>$features_output</table>":"")."
</td>
</tr></table>";




echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body>
$ausgabe
</body>

</html>";

?>

