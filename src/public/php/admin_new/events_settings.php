<?

include("inc/general.php");
$self = "index.php";

$events = assocs("SELECT * FROM events_settings", 'type');

if ($pl < 3) die("Not allowed");

if ($type && $do) {
	$type = addslashes($type);
	$do = addslashes($do);
	$error = 0;
	if ($do == 'edit') {
		if (!is_numeric($starttime)) {
			$starttime = strtotime($starttime);
		}
		if (!is_numeric($endtime)) {
			$endtime = strtotime($endtime);
		}
		
		if (is_numeric($amount) && is_numeric($starttime) && is_numeric($endtime)) {
			select("UPDATE events_settings SET amount = '".$amount."', starttime = '".$starttime."', endtime = '".$endtime."' WHERE type = '".$type."'");
			$logs = "event: $type, $do: amount=".$events[$type]['amount']." to ".$amount." stime=".date('d.m.Y H:i:s',$events[$type]['starttime'])." to ".date('d.m.Y H:i:s',$starttime).", etime=".date('d.m.Y H:i:s',$events[$type]['endtime'])." to ".date('d.m.Y H:i:s',$endtime);
			$output = 'Die Menge, Start- und Endzeiten wurden erfolgreich übernommen.';
		} else {
			$output = "Starttime und Endtime muss ein Unixtimestamp sein (Sekunden seit 1.1.1970)";
			$error = 1;
		}
	} elseif ($do == 'truncate') {
		$logs = "event: $type, $do: zuvor waren ".single("SELECT COUNT(*) FROM events WHERE type = '".$type."'").' Einträge vorhanden';
		select("DELETE FROM events WHERE type = '".$type."'");
		$output = "Die Boni für $type wurden nun zurückgesetzt.";
	}
	if (!$error) {
		select("INSERT INTO admin_logs(time,user_id,content) " .
			"VALUES ('".time()."','".$id."', '".addslashes($logs)."')");
	}
	$events = assocs("SELECT * FROM events_settings", 'type');
}

?><h1>Einstellungen zu den Events</h1>
<br>
<? echo $output ?>
<br>
<br>
<style type="text/css">

	table {
		border:1px solid #000000;
	}

	td {
		vertical-align: top;
	}

</style>
<table>
	<tr>
		<th>Typ</th>
		<th>Menge</th>
		<th>Startzeit</th>
		<th>Endzeit</th>
		<th>Optionen</th>
	</tr>
<?
foreach($events as $ky => $vl) {
	echo "
		<form action=\"events_settings.php\" method=\"POST\"><tr>
			<input type=\"hidden\" name=\"type\" value=\"".$ky."\">
			<input type=\"hidden\" name=\"do\" value=\"edit\">
			<td rowspan=\"2\">".$ky."</td>
			<td rowspan=\"2\"><input type=\"text\" name=\"amount\" value=\"".$vl['amount']."\"></td>
			<td><input type=\"text\" name=\"starttime\" value=\"".date('Y-m-d H:i:s', $vl['starttime'])."\"></td>
			<td><input type=\"text\" name=\"endtime\" value=\"".date('Y-m-d H:i:s', $vl['endtime'])."\"></td>
			<td><input type=\"submit\" value=\"OK\"></td>
		</tr></form>
		<form action=\"events_settings.php\" method=\"POST\"><tr>
			<input type=\"hidden\" name=\"type\" value=\"".$ky."\">
			<input type=\"hidden\" name=\"do\" value=\"truncate\">
			<td colspan=\"2\">von ".single("SELECT COUNT(*) FROM events WHERE type = '".$ky."'")." Spielern im Moment verwendet</td>
			<td><input type=\"submit\" value=\"Boni zurücksetzen\"></td>
		</tr></form>";
}
?>
</table>
<br>
<br>
<p><b>Anmerkung:</b> Um die Zeit in den Unix-Timestamp umzurechnen kannst du zum Beispiel den folgenden Rechner nützen: <a href="http://www.unixtime.de/" target="_blank">http://www.unixtime.de/</a><br>
Alternativ ist auch die folgende Schreibweise korrekt: YYYY-MM-DD HH:MM:SS</p><br><br>
<p>Eingebaut von inok1989 im Dezember 2012</p>