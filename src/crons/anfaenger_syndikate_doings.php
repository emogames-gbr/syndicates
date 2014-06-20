<?
echo "\n\n";

set_time_limit(0);
require_once ("../includes.php");
$handle = connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once(INC."ingame/globalvars.php");
$time = time();

$queries = array();
$globals = assoc("select * from globals order by round desc limit 1");


if ($globals[roundstarttime] - $time < 0 && preg_match("/basic/", $db) && $globals['roundstatus'] == 1) { // Verschiebung nur auf Basic-Server!!!

	/////
	/////	INAKTIVE RAUS
	/////
	/////

	$inacs = assocs("select * from status where alive = 1 and lastlogintime < ".($time  -  TIME_TILL_INACTIVE).""); // Alive = 2 Urlaub wird nicht verschoben
	$syns = assocs("select * from syndikate", "synd_id");
	foreach ($inacs as $status) {
		$count++;
		if ($syns[$status[rid]][synd_type] != "noob-inactive" AND ($syns[$status[rid]][open] == 1 OR $status['lastlogintime'] < $time - TIME_TILL_GLOBAL_INACTIVE)) {
			$newrid = get_an_empty_syndicate("noob-inactive", $status[rid], 1);
			$who = $status[id];
				if ($newrid)	{
					$queries[] = "update status set rid=".$newrid." where id=".$who;
					$podpoints = $status[podpoints];
					$wholand = $status[land];
					if (!$podpoints): $podpoints = 0; endif;
					if ($podpoints < (-1) * $wholand * 2000): $podpoints = (-1) * $wholand * 2000; endif; // Kleine Sicherheitsvorkehrung, damit sich ein Syndikat keinen "Saboteur" anlegt, ihn um paar hundert Millionen verschuldet und in ein anderes Syndikat reinschickt
					if ($podpoints < 0): $podpoints *= -1; $vorzeichen_old = "+"; $vorzeichen_new = "-";
					else: $vorzeichen_old = "-"; $vorzeichen_new = "+"; endif;
					$queries[] = "update ".$globals{statstable}." set rid=".$newrid." where round=".$globals[round]." and konzernid=".$who;
					$message="Der Konzern <b>".$status{syndicate}."</b> hat unser Syndikat aus wirtschaftlichen Interessen verlassen.";
					$action ="insert into towncrier (time,rid,message) values ($time,".$status{rid}.",'$message')";
					array_push($queries,$action);
					$message="Der Konzern <b>".$status{syndicate}."</b> tritt aus wirtschaftlichen Interessen unserem Syndikat bei.";
					$action ="insert into towncrier (time,rid,message) values ($time,".$newrid.",'".$message."')";
					array_push($queries,$action);
					$queries[] = "insert into syndikate_anfaenger_inaktivenverschiebungen (user_id, time, old_rid, new_rid) values ($status[id], $time, $status[rid], $newrid)";
					player_leave_syndicate($who, $status[rid]);
					player_join_syndicate($who, $newrid);
				}
		}
		echo "$status[syndicate]\n";
	}
	echo "count: $count\n";


}


db_write($queries);

echo "\n\n\n";


?>
