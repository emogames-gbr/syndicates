<?

chdir("../");
include("inc/general.php");

$case_id = int($case_id);
$cases_data = array();

if (!$view) $view = "ablage";

if ($view == "ablage") {
	$cases_to_show = assocs("select * from admin_case_view_history where user_id = $id and isfavorit = 1 order by firstviewtime desc", "case_id");
	if ($case_id and single("select count(*) from admin_case where id = '$case_id'")) {
		if ($action == "add") {
			if (!$cases_to_show[$case_id]) {
				select("update admin_case_view_history set isfavorit = 1 where case_id = $case_id and user_id = $id");
				$cases_to_show = assocs("select * from admin_case_view_history where user_id = $id and isfavorit = 1 order by firstviewtime desc", "case_id");
			}
		}
		elseif ($action == "del") {
			if ($cases_to_show[$case_id]) {
				select("update admin_case_view_history set isfavorit = 0 where case_id = $case_id and user_id = $id");
				$cases_to_show = assocs("select * from admin_case_view_history where user_id = $id and isfavorit = 1 order by firstviewtime desc", "case_id");
			}
		}
	}
}
elseif ($view == "history") {
	$cases_to_show = assocs("select * from admin_case_view_history where user_id = $id and lastviewtime >= ".($time - 24*3600)." order by lastviewtime desc", "case_id");
}
elseif ($view == "bearbeitung") {
	$cases_data = assocs("select * from admin_case where processor_id = $id and status < 4", "id");
	if ($cases_data) $cases_to_show = assocs("select * from admin_case_view_history where user_id = $id and case_id in (".join(",", array_keys($cases_data)).")", "case_id");
}

unset($case_id);

	if ($cases_to_show) {
		if (!$cases_data) $cases_data = assocs("select * from admin_case where id in (".join(",", array_keys($cases_to_show)).")", "id");
		$cases_to_show_ausgabe = "<table width=100% cellpadding=1 cellspacing=0 class=ver10s>";
		foreach ($cases_to_show as $case_id => $data) {
			$cases_to_show_ausgabe .= "<tr><td><a href=../view_case.php?case_id=$case_id class=ver10s target=main>(#$case_id) ".$cases_data[$case_id]['title']."</a>".($view == "ablage" ? "</td><td align=right><a href=$page.php?view=ablage&action=del&case_id=$case_id class=ver10s>rm</a></td>":"")."</td></tr>";
		}
		$cases_to_show_ausgabe .= "</table>";
	} else $cases_to_show_ausgabe = "<font class=ver10s>Keine Cases gefunden</font>";



$ausgabe = $cases_to_show_ausgabe;



echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"../style.css\" TYPE=\"text/css\">
</head>
<body>
$ausgabe
</body>

</html>";

?>

