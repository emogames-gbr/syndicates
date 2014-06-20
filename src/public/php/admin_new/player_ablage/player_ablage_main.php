<?

chdir("../");
include("inc/general.php");

$uid = int($uid);
$userdata = array();

if (!$view) $view = "ablage";

if ($view == "ablage") {
	$users_to_show = assocs("select * from admin_user_user_ablage where user_id = $id order by time desc", "target_id");
	if ($uid and single("select count(*) from users where id = '$uid'")) {
		if ($action == "add") {
			if (!$users_to_show[$uid]) {
				select("insert into admin_user_user_ablage (user_id, target_id, time) values ($id, $uid, $time)");
				$users_to_show = assocs("select * from admin_user_user_ablage where user_id = $id order by time desc", "target_id");
			}
		}
		elseif ($action == "del") {
			if ($users_to_show[$uid]) {
				select("delete from admin_user_user_ablage where user_id = $id and target_id = $uid");
				$users_to_show = assocs("select * from admin_user_user_ablage where user_id = $id order by time desc", "target_id");
			}
		}
	}
}
elseif ($view == "history") {
	$users_to_show = assocs("select DISTINCT target_id, id, time from admin_user_view_history where user_id = $id and time >= ".($time - 24*3600)." order by time desc", "target_id");
}

unset($uid);

	if ($users_to_show) {
		$users_data = assocs("select * from users where id in (".join(",", array_keys($users_to_show)).")", "id");
		$users_to_show_ausgabe = "<table width=100% cellpadding=1 cellspacing=0 class=ver10s>";
		foreach ($users_to_show as $uid => $data) {
			$users_to_show_ausgabe .= "<tr><td><a href=../player_specific.php?ia=calc&search=".urlencode("%$uid")." class=ver10s target=player_specific>(%$uid) ".$users_data[$uid]['username']."</a>".($view == "ablage" ? "</td><td align=right><a href=$page.php?view=ablage&action=del&uid=$uid class=ver10s>rm</a></td>":"")."</td></tr>";
		}
		$users_to_show_ausgabe .= "</table>";
	} else $users_to_show_ausgabe = "<font class=ver10s>Keine User gefunden</font>";



$ausgabe = $users_to_show_ausgabe;



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

