<?
echo "\n\n";

set_time_limit(0);
require ("../includes.php");
$handle = connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require(INC."ingame/globalvars.php");
$time = time();


##########################################
#	SERVERLOOP
##########################################
$servers = assocs("select * from servers");

foreach ($servers as $temp) {
	mysql_select_db($temp[db_name]);
##########################################


$queries = array();
$globals = assoc("select * from globals order by round desc limit 1");


if ($globals[roundstatus] == 0 or $globals[roundstatus] == 1) {
	$status = assocs("select * from status where alive > 0", "id");
	$users = assocs("select * from users where konzernid > 0", "konzernid");
	$stats = assocs("select * from stats where round = $globals[round] and konzernid > 0 and alive > 0", "konzernid");
	foreach ($status as $ky => $vl) {
		$updates = array();
		if (!$stats[$ky] && $users[$ky]) {
			select("insert into stats (konzernid, username, rulername, syndicate, race, user_id, rid, alive, round, isnoob) values
						($ky, '".$users[$ky][username]."', '".$status[$ky][rulername]."', '".$status[$ky][syndicate]."', '".$status[$ky][race]."', '".$users[$ky][id]."',
						'".$status[$ky][rid]."', '1', '".$globals[round]."', '".$status[$ky][isnoob]."')");
		}
		elseif ($users[$ky]) {
			if ($stats[$ky][username] != $users[$ky][username]) $updates[] = "username='".$users[$ky][username]."'";
			if ($stats[$ky][user_id] != $users[$ky][id]) $updates[] = "user_id='".$users[$ky][id]."'";
			if ($stats[$ky][syndicate] != $status[$ky][syndicate]) $updates[] = "syndicate='".$status[$ky][syndicate]."'";
			if ($stats[$ky][rulername] != $status[$ky][rulername]) $updates[] = "rulername='".$status[$ky][rulername]."'";
			if ($stats[$ky][race] != $status[$ky][race]) $updates[] = "race='".$status[$ky][race]."'";
			if ($stats[$ky][rid] != $status[$ky][rid]) $updates[] = "rid='".$status[$ky][rid]."'";
			if ($updates) select("update stats set ".join(",", $updates)." where konzernid = $ky and round=$globals[round]");
		}
	}

	foreach ($stats as $ky => $vl) {
		if (!$status[$ky]) {
			select("update stats set alive = 0 where konzernid = $ky and round=$globals[round]");
		}
	}

}


//db_write($queries);

echo "\n\n\n";

} // ENDE SERVERLOOP


?>
