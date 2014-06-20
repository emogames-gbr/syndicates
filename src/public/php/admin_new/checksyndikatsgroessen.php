<?

include("inc/general.php");
$self = "checksyndikatsgroessen.php";
$ausgabe ="";

$round = $globals[round];
$size = 5;

$rids = assocs("select rid, count(*) as tl from stats where round = $round and alive > 0 and isnoob = 0 group by rid", "rid");
$rids_isnoob = assocs("select rid, count(*) as tl from stats where round = $round and alive > 0 and isnoob = 1 group by rid", "rid");
$rids_total =  assocs("select rid, count(*) as tl from stats where round = $round and alive > 0 group by rid order by tl desc", "rid");
$syndikate = assocs("select * from syndikate", "synd_id");

$headline = "<tr bgcolor=orange><td>Syndikatstyp</td><td>Syndikatsname</td><td>Nummer</td><td># Normale Spieler</td><td># Anfängerspieler</td></tr>";
$ausgabe = "<table cellpadding=2 border=1>";
$count2 = 0;
foreach ($rids_total as $ky => $vl) {
	if ($count2 == 0) $ausgabe .= $headline;
	$ausgabe .= "<tr><td".($syndikate[$ky][synd_type] != "normal" ? " bgcolor=yellow":"").">".$syndikate[$ky][synd_type]."</td><td><a href=checksyndikatsgroessen.php?synnummer=$ky>".$syndikate[$ky][name]."</a></td><td>#$ky</td><td".(($rids[$ky][tl] > 0 and $syndikate[$ky][synd_type] != "normal") ? " bgcolor=red":"").">".($rids[$ky][tl] + $rids_isnoob[$ky][tl] > MAX_USERS_A_SYNDICATE ? "<font color=blue size=$size><b>".$rids[$ky][tl]."</b></font>":$rids[$ky][tl])."</td><td".(($rids_isnoob[$ky][tl] > 0 and $syndikate[$ky][synd_type] == "normal") ? " bgcolor=red":"").">".($rids[$ky][tl] + $rids_isnoob[$ky][tl] > MAX_USERS_A_SYNDICATE ? "<font color=blue size=$size><b>".$rids_isnoob[$ky][tl]."</b></font>":$rids_isnoob[$ky][tl])."</td></tr>";
	$count++;
	$sum1 += $rids[$ky][tl];
	$sum2 += $rids_isnoob[$ky][tl];
	if (++$count2 >= 10) $count2 = 0;
}
$ausgabe .= "</table>";

$ausgabe =  "<table border=1 cellpadding=3>
<tr><td></td><td>Stats-Table</td><td>Status-Table</td></tr>
<tr><td>Gesamtsumme</td><td>".($sum1+$sum2)."</td><td>".single("select count(*) from status where alive > 0")."</td></tr>
<tr><td>Summe normale Spieler</td><td>$sum1</td><td>".single("select count(*) from status where alive > 0 and isnoob = 0")."</td></tr>
<tr><td>Summe Anfänger</td><td>$sum2</td><td>".single("select count(*) from status where alive > 0 and isnoob = 1")."</td></tr>
<tr><td>Anzahl Syndikate</td><td>$count</td><td>".single("select count(*) from syndikate")." (Count über den Syndikate-Table)</td></tr>
<tr><td>Alive = 0, aber Synnummer gesetzt</td><td>".single("select count(*) from stats where alive = 0 and rid > 0 and round = $round")."</td><td>".single("select count(*) from status where alive = 0 and rid > 0")."</td></tr>
<tr><td>Alive > 0 aber keine Synnummer gesetzt</td><td>".single("select count(*) from stats where alive > 0 and rid = 0 and round = $round")."</td><td>".single("select count(*) from status where alive > 0 and rid = 0")."</td></tr>

</table><br><br>".$ausgabe;

if ($synnummer) {
	unset($ausgabe);

	$ausgabe =
	"<table cellpadding=3 border=1>
	<tr><td></td><td>Stats-Table</td><td>Status-Table</td><td>isnoob (stats / status)</td></tr>";

	$players = assocs("select * from status where alive > 0 and rid = ".floor($synnummer), "id");
	$players_stats = assocs("select * from stats where rid = ".floor($synnummer)." and round = $round and alive > 0", "konzernid");

	foreach ($players as $ky => $vl) {
		$ids[$ky] = 1;
	}
	foreach ($players_stats as $ky => $vl) {
		$ids[$ky] = 1;
	}

	foreach ($ids as $ky => $vl) {
		$ausgabe .= "<tr><td><a href=checksyndikatsgroessen.php?synnummer=$synnummer&pid=$ky>".($players[$ky] ? $players[$ky][syndicate] : $players_stats[$ky][syndicate])."</a></td><td>".($players_stats[$ky] ? "<font color=green>DA</font>":"<font color=red>NICHT DA</font>")."</td><td>".($players[$ky] ? "<font color=green>DA</font>":"<font color=red>NICHT DA</font>")."</td><td>".$players_stats[$ky][isnoob]." / ".$players[$ky][isnoob]."</td></tr>";
	}
	$ausgabe .= "</table>";

	$pid = floor($pid);
	if ($pid && ($isadmin or ($players_stats[$pid] && !$players[$pid] || !$players_stats[$pid] && $players[$pid]))) {
		unset($ausgabe);

		$ausgabe =
		"<table cellpadding=3 border=1>
		<tr><td></td><td>Stats-Table</td><td>Status-Table</td></tr>";

		$players = assoc("select * from status where alive > 0 and id = $pid");
		$players_stats = assoc("select * from stats where konzernid = $pid and round = $round and alive > 0");




		$values = array("alive", "isnoob", "race", "rid");

		if ($isadmin) $ausgabe .= "<tr><td></td><td><a href=checksyndikatsgroessen.php?synnummer=$synnummer&pid=$pid&deletestats=1>löschen</a></td><td><!--<a href=index.php?action=checksyndikatsgroessen&synnummer=$synnummer&pid=$pid&deletestatus=1>löschen</a>--></td></tr>";

		foreach ($values as $vl) {
			$ausgabe .= "<tr><td>$vl</td><td>$players_stats[$vl]</td><td>$players[$vl]</td></tr>";
		}


		$userdaten = assoc("select * from users where konzernid = $pid");
		if (!$userdaten) {
			$ausgabe .= "<tr><td colspan=2>Keine Userdaten gefunden - Account muss gelöscht sein</td></tr>";

		} else {
			$ausgabe .= "<tr><td>Startrunde</td><td colspan=2>$userdaten[startround]</td></tr>";
			$ausgabe .= "<tr><td>Accountanlegedatum</td><td colspan=2>".date("d.M.Y, H:i", $userdaten[createtime])."</td></tr>";
			$konzerndelete = assocs("select * from options_konzerndelete where user_id = $userdaten[id]");
			echo "Konzerndeletions:<br>";
			pvar($konzerndelete);
			$ausgabe .= "<tr><td>Username</td><td colspan=2>".$userdaten[username]."</td></tr>";
		}
		$ausgabe .= "</table>";

		if (($deletestats xor $deletestatus) and $isadmin) {
			unset ($ausgabe);
			if (!$continue) {
				$ausgabe = "SICHERHEITSABFRAGE, Weiter?<br><br><a href=checksyndikatsgroessen.php?synnummer=$synnummer&pid=$pid&".($deletestats ? "deletestats":"deletestatus")."=1&continue=1>JA</a>";
			}
			else {
				if ($deletestats) {
					select("delete from stats where konzernid = $pid and round = $round");
					$ausgabe .= "DONE";
				}
				elseif ($deletestatus) {
					select("delete from status where id = $pid");
				}
			}
		} elseif ($deletestats AND $deletestatus){ $ausgabe = "Fehler: Nur eines setzen, entweder Stats ODER Status löschen".$ausgabe; }
	}
}

echo $ausgabe;


?>
