<?

include("inc/general.php");

$lightgreen = "#e2ffd4";
$middlegreen = "#72c44a";
$darkgreen = "#17b90f";
$lightred = "#ffe0e0";
$middlered = "#ff4d4d";
$darkred = "#ff0000";

$user_id = int($user_id);
/*
if ($action and $action != "adduser" and $action != "deluser" and $action != "reply" and $action != "edit" and $action != "closecase" and $action != "opencase" and $action != "makegeneral" and $action != "strafefestlegen" and $action != "zustimmen" and $action != "sendmessage"): $action = ""; endif;
*/

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
	9 => "Forenbeitr?e",
	10 => "Sonstiges"
);

$punishments = assocs("select * from admin_punishment_settings order by id asc", "id");
$userdata = assocs("select * from admin_case_involved where user_id = $user_id", "case_id");
$user_ids[$user_id] = 1;

if ($userdata) {
	$casedata = assocs("select * from admin_case where id in (".(join(",", array_keys($userdata))).") order by endtime desc", "id");
	$lines = "<tr>
					<td align=center>#</td>
					<td align=center>title</td>
					<td align=center>Closed?</td>
					<td align=center>Case-Starter</td>
					<td align=center>Case-Closer</td>
					<td align=center>Bestrafung</td>
					<td align=center>Fazit intern</td>
				</tr>";

	foreach ($casedata as $ky => $vl) {
		$user_ids[$vl['closer_id']] = 1;
		$user_ids[$vl['starter_id']] = 1;
	}


	
	$usernames = assocs("select id, username from users where id in (".join(",", array_keys($user_ids)).")", "id");
	foreach ($casedata as $ky => $vl) {
		if ($vl['status'] == 5) $closed = "<font color=purple>".datum("d.m.Y, H:i", $vl['endtime'])."</font>";
		elseif ($vl['status'] == 0) $closed = "<font color=red>unbearbeitet</font>";
		elseif ($vl['status'] == 1) $closed = "<font color=green>offen</font>";
		elseif ($vl['status'] == 2) $closed = "<font color=orange>offen</font>";
		else												 $closed = "<font color=purple>offen</font>";
		$lines .= "<tr bgcolor=".($userdata[$ky]['status'] == 0 ? "$middlegreen":($userdata[$ky]['punishment_id'] <= 1 ? "white":$middlered))." class=ver14s>
						<td align=center><a href=view_case.php?case_id=$ky target=main class=ver12s>$ky</a></td>
						<td align=center nowrap><a href=view_case.php?case_id=$ky target=main class=ver12s>".$vl['title']."</a></td>
						<td align=center>$closed</td>
						<td align=center>".$usernames[$vl['starter_id']]['username']."</td>
						<td align=center>".$usernames[$vl['closer_id']]['username']."</td>
						<td align=center>".$punishments[$userdata[$ky]['punishment_id']]['bezeichnung']."</td>
						<td align=center>".(($userdata[$ky]['fazit_intern']) ? $userdata[$ky]['fazit_intern'] : ($userdata[$ky]['fazit_user'] ? $userdata[$ky]['fazit_user'] : "n/a"))."</td>
				</tr>";
	}
	$ausgabe = "<center><b>Case-History zum Spieler <br><br>".$usernames[$user_id]['username']."</b></center><br><br><table align=center width=80% border=1>$lines</table>";


	
} else $ausgabe = "Keine Cases zum User mit der User-Id $user_id gefunden";




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

?>

