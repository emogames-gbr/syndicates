<?

include("inc/general.php");
if (!$cases_show_modus) $cases_show_modus = 2;
if ($cases_show_modus == "null") $cases_show_modus = 0;
floor($cases_show_modus);
if ($cases_show_modus < 0 or $cases_show_modus > 3) $cases_show_modus = 2;

$case_type_zuordnungen = array(
	0 => "Mitteilungen",
	1 => "Multi-Reports",
	2 => "Sitting-Reports",
	3 => "Konzernbild",
	4 => "Konzernname",
	5 => "Konzernbeschr.",
	6 => "Syndikatsbanner",
	7 => "Syndikatswebseite",
	8 => "Syndikatsbeschreibung",
	9 => "Forenbeiträge",
	10 => "Sonstiges"
);

$unseen_cases_for_overview = assocs("select type, count(*) as anzahl from admin_case where status = 0 group by type", "type");
$case_count_by_status = assocs("select status, count(*) as tl from admin_case where status != 4 group by status", "status");
for ($i = 0; $i <= 3; $i++) if (!$case_count_by_status[$i]['tl']) $case_count_by_status[$i]['tl'] = 0;

// Cases mit Status 0, 1 oder 2 werden hier geholt und in der Box oben rechts je nach Auswahl angezeigt
$cases = assocs("select * from admin_case where status = $cases_show_modus", "id");
if ($cases) {
	$cases_show_ausgabe .= "<table class=ver11s cellpadding=3 border=1 cellspacing=0>";
	$headline = "<tr><td><b>#</b></td><td><b>title</b></td><td><b>Erstelldatum</b></td><td><b>Case-Starter</b></td><td><b>wird bearbeitet von</b></td><td align=center><b>ansehen</b></td></tr>";
	$user_ids = array();
	foreach ($cases as $case_id => $vl) {
		$user_ids[] = $vl['starter_id'];
		if ($vl['processor_id']) $user_ids[] = $vl['processor_id'];
	}
	$usernames = assocs("select username, id from users where id in (".join(",", $user_ids).")", "id");
	$count = 9;
	foreach ($cases as $case_id => $vl) {
		if (++$count == 10) { $count = 0; $cases_show_ausgabe .= $headline; }
		$cases_show_ausgabe .= "<tr><td align=right>".$vl['id']."</td><td align=center>".$vl['title']."</td><td align=center>".datum("d.m.Y, H:i", $vl['starttime'])."</td><td align=center>".$usernames[$vl['starter_id']]['username']."</td><td align=center>".($vl['status'] == 0 ? "<a href=getcase.php?case_id=$case_id class=ver11s>->bearbeiten</a>":$usernames[$vl['processor_id']]['username'])."</td><td align=center><a href=view_case.php?case_id=$case_id class=ver11s>go</a></tr>";
	}
	$cases_show_ausgabe .= "</table>";
} else { $cases_show_ausgabe .= "Es wurden keine Cases gefunden."; }

// Cases mit Status 5 werden, falls sie noch keine 3 Tage her sind in der "Cases-bearbeitet" Box unten aufgeführt, damit sich jeder die Arbeit der anderen ansehen kann
$cases_closed = array();
$limit = floor($limit);
if (!$limit) $limit = 1;
$per_limit = floor($per_limit);
$per_limit_standard = 30;

if (!$per_limit) $per_limit = $per_limit_standard;
$per_limit_to_chose = array(10, 30, 50, 100, 200);
$ausgabe_pages_a_site = array();
foreach ($per_limit_to_chose as $vl) {
	$temp = $vl;
	if ($vl == $per_limit) $temp = "<b>$temp</b>";
	$temp = "<a href=main.php?limit=$limit&per_limit=$vl class=ver10s>$temp</a>";
	$ausgabe_pages_a_site[] = $temp;
}
$ausgabe_pages_a_site = join(", ", $ausgabe_pages_a_site);

$cases_closed[0] = assocs("select * from admin_case where endtime >= ".get_day_time(get_day_time($time)-2)." and status = 5 order by endtime desc", "id");
$cases_closed[1] = assocs("select * from admin_case where endtime < ".get_day_time(get_day_time($time)-2)." order by endtime desc limit ".(($limit-1)*$per_limit).", $per_limit", "id");

	// Ausgabe zum Auswählen der Seite vorbereiten
	$older_cases_count = single("select count(*) from admin_case where endtime < ".get_day_time(get_day_time($time)-2));
	$pages = $older_cases_count / $per_limit;
	if ($pages != floor($pages)) $pages = floor($pages);
	$pagestring = "(";
	$pagestringBuildArray = array();
	for ($i = 1; $i <= $pages; $i++) {
		$current = $i;
		if ($limit == $i) $current = "<b>$current</b>";
		$current = "<a href=main.php?limit=$i&per_limit=$per_limit class=ver11s>$current</a>";
		if ($i <= 3) {
			$pagestringBuildArray[] = $current;
		}	
		else {
			if (!$pointsadded1 && $limit >= 7) { $pagestringBuildArray[] = "(...)"; $pointsadded1 = 1; }
			if (abs($limit - $i) <= 2) $pagestringBuildArray[] = $current;
			if ($i - $limit > 2) {
				if (!$pointsadded2 && $limit + 6 <= $pages) { $pagestringBuildArray[] = "(...)"; $pointsadded2 = 1; }
				if ($i + 3 > $pages) $pagestringBuildArray[] = $current;
			}
		}
	}
	$pagestring .= join(",", $pagestringBuildArray);
	$pagestring .= ")";

for ($i = 0; $i <= 1; $i++) {
	if ($cases_closed[$i]) {
		$cases_closed_ausgabe[$i] .= "<table class=ver11s cellpadding=3 border=1 cellspacing=0>";
		$headline = "<tr><td><b>#</b></td><td><b>title</b></td><td><b>Close-Datum</b></td><td><b>Case-Starter</b></td><td><b>Case-Closer</b></td><td align=center><b>ansehen</b></td></tr>";
		$user_ids[$i] = array();
		foreach ($cases_closed[$i] as $case_id => $vl) {
			$user_ids[$i][] = $vl['starter_id'];
			if ($vl['processor_id']) $user_ids[$i][] = $vl['processor_id'];
			$user_ids[$i][] = $vl['closer_id'];
		}
		$usernames = assocs("select username, id from users where id in (".join(",", $user_ids[$i]).")", "id");
		$count = 9;
		foreach ($cases_closed[$i] as $case_id => $vl) {
			if (++$count == 10) { $count = 0; $cases_closed_ausgabe[$i] .= $headline; }
			$cases_closed_ausgabe[$i] .= "<tr".($vl['status'] != 5 ? " background-color=orange":"")."><td align=right>".$vl['id']."</td><td align=center>".$vl['title']."</td><td align=center>".datum("d.m.Y, H:i", $vl['endtime'])."</td><td align=center>".$usernames[$vl['starter_id']]['username']."</td><td align=center>".($vl['status'] == 0 ? "<a href=getcase.php?case_id=$case_id class=ver11s>->bearbeiten</a>":$usernames[$vl['closer_id']]['username'])."</td><td align=center><a href=view_case.php?case_id=$case_id class=ver11s>go</a></tr>";
		}
		$cases_closed_ausgabe[$i] .= "</table>";
	} else { $cases_closed_ausgabe[$i] .= "Es wurden keine Cases gefunden."; }
}



///////////////////////
//////////////////////
/////////////////////





$ausgabe = "
<center><b>Cases Overview</b></center><br>
<table width=100% border=0 valign=top align=center cellspacing=0 cellpadding=0 class=ver11s>
<tr class=ver10s>
	<td valign=top width=30%>
		<table border=0 align=center valign=top cellspacing=0 cellpadding=2 class=ver11s>
			<tr><td colspan=3><b>Übersicht ungesehene Cases</b></td></tr>";

			foreach ($case_type_zuordnungen as $ky => $vl) {
				$ausgabe .= "<tr><td>".($unseen_cases_for_overview[$ky]['anzahl'] ? $unseen_cases_for_overview[$ky]['anzahl']:"0")."</td><td>$vl</td><td>".($unseen_cases_for_overview[$ky]['anzahl'] ? "<a href=getcase.php?type=$ky class=ver11s>bearbeiten</a>":"")."</td></tr>";
			}

			$ausgabe .= "<tr><td><br><br></td></tr>
		</table>
	</td>
	<td width=50% valign=top rowspan=2>
		<table border=0 align=center valign=top cellspacing=0 cellpadding=3 class=ver11s>
			<tr valign=top>
			<td align=center>".($cases_show_modus == 3 ? "<b>zuzustimmende Cases</b>":"<a href=main.php?cases_show_modus=3 class=ver11s>zuzustimmende Cases</a>")." (".$case_count_by_status[3]['tl'].") | ".($cases_show_modus == 2 ? "<b>\"Allgemeine\" Cases</b>":"<a href=main.php?cases_show_modus=2 class=ver11s>\"Allgemeine\" Cases</a>")." (".$case_count_by_status[2]['tl'].") | ".($cases_show_modus == 1 ? "<b>Cases in Bearbeitung</b>":"<a href=main.php?cases_show_modus=1 class=ver11s>Cases in Bearbeitung</a>")." (".$case_count_by_status[1]['tl'].") | ".($cases_show_modus == 0 ? "<b>unbearbeitete Cases</b>":"<a href=main.php?cases_show_modus=null class=ver11s>unbearbeitete Cases</a>")." (".$case_count_by_status[0]['tl'].")</td>
			</tr>

			<tr><td><hr></td></tr>

			<tr>
			<td align=center>$cases_show_ausgabe</td>
			</tr>

			<tr height=50><td><hr></td></tr>
			<tr>
				<td>
					<table border=0 align=center valign=top cellspacing=0 cellpadding=3 class=ver11s>
						<tr valign=top>
						<td align=center class=ver12s><b>Heute..Vorgestern bearbeitete geschlossene Cases</b><br></td>
						</tr>
						<tr>
						<td align=center>".$cases_closed_ausgabe[0]."</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr height=50><td><hr></td></tr>
			<tr>
				<td>
					<table border=0 align=center valign=top cellspacing=0 cellpadding=3 class=ver11s>
						<tr valign=top>
						<td align=center class=ver12s><b>Ältere Cases</b> $pagestring<br>Anzahl Cases pro Seite: $ausgabe_pages_a_site</td>
						</tr>
						<tr>
						<td align=center>".$cases_closed_ausgabe[1]."</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>

</tr>


</table>";




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

