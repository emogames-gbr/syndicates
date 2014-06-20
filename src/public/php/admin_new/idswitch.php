<?

include("inc/general.php");
$self = "idswitch.php";
$ausgabe ="";

$assis = array( 8 => "Forschungsassistent", 9 => "Militärassistent", 10 => "Gebäudeassistent", 7 => "Werbung deaktivieren", 11 => "Komfortpaket", 12 => "Angriffs- / Spydb");

if ($ia == "calc") {
	if ($userid) {
		$userid = floor($userid);
		$and = "users.id=$userid";
	}
	if ($konzernid) {
		$konzernid = floor($konzernid);
		$and = "status.id=$konzernid";
	}
	if ($konname) {
		$konname = addslashes($konname);
		$and = "status.syndicate='$konname'";
	}
	if ($koinsname) {
		$koinsname = addslashes($koinsname);
		$and = "users.username='$koinsname'";
	}
	$query = "select users.id as Userid,users.username as Username,status.id as Konzernid,status.syndicate as Konzernname,status.rid as Syndikatsnummer,status.race as Fraktion
						from users,status
						where
						status.id = users.konzernid and
						$and";
	$ustatss = assoc("$query");
	if ($ustatss[Userid]) {
		$features = singles("select feature_id from features where konzernid = $ustatss[Konzernid]");
		foreach ($assis as $ky => $vl) {
			$there = 0;
			foreach ($features as $vl2) {
				if ($vl2 == $ky): $there = 1; break; endif;
			}
			$features_output .= "<tr><td>$vl</td><td>".($there ? "<font color=green>JA</font>":"<font color=red>NEIN</font>")."</td></tr>";
		}
	}
	//echo $query;
	$ausgabe .= "<table class=\"bodys\">";
	if (!$ustatss) {
		if ($konname or $konzernid) {
			$ustatss = assoc("select status.id as Konzernid, status.syndicate as Konzernname, status.rid as Syndikatsnummer, status.race as Fraktion from status where $and");
		}
		if (!$ustatss) {
			$ustatss = assoc("select users.id as Userid, users.username as Username from users where $and");
		}
	}
	if ($ustatss) {
		foreach ($ustatss as $ky => $v) {
				$ausgabe.="<tr><td><b>$ky</b></td><td><b>$v</b></td></tr>";
		}
		$ausgabe .= $features_output;
		if ($isadmin) {
			$ausgabe .= "<tr><td colspan=2><hr></td></tr><tr><td colspan=2 align=center><a href=$self?action=idswitch&ia=directlogin&target=$ustatss[Konzernid] target=_blank>Direktlogin</a></td></tr>";
		}
	}
	else { $ausgabe .= "<tr><td>Keine Übereinstimmung</td></tr>"; }




	$ausgabe .= "</table><br><br><br><br>";
}
elseif ($ia == "directlogin") {
	$target = floor($target);
	$existing = single("select count(*) from status where id = $target");
	if ($existing) {
		$adminsession = createkey();
		select("insert into sessionids_admin (sessionid, angelegt_bei, gueltig_bis, ip, user_id, adminuser) values ('$adminsession', $time, ".($time + 20 * 60).", '".getenv ("REMOTE_ADDR")."', $target, '$adminuser')");
		setcookie ("dontusepacket", 1, -1 ,"/", "");
		setcookie ("adminsessionid", $adminsession, -1 ,"/", "");
		header("Location: ../statusseite.php");
	} else { $ausgabe .= "Ziel existiert nicht!<br><br>"; }

}

$ausgabe.="
	<b>Nur ein Datum angeben!</b><br><br>
	<form action=$self>
		<table class=\"normal\">
			<tr>
				<td>User id:</td><td><input name=userid></td>
			</tr>
			<tr>
				<td>Konzernid:</td><td><input name=konzernid></td>
			</tr>
			<tr>
				<td>Username:</td><td><input name=koinsname></td>
			</tr>
			<tr>
				<td>Konzernname:</td><td><input name=konname></td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<input type=hidden name=ia value=calc>
					<input type=\"hidden\" name=action value=idswitch>
					<input type=submit><br><br>
				</td>
			</tr>
		</table>
	</form>
	<br>
";



echo $ausgabe;



?>
