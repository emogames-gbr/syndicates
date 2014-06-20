<?
require_once("../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);



$globals = assoc("select * from globals order by round desc limit 1");
$users_sygnatur_to_print = assocs("select users.id, users.username, users.konzernid, status.syndicate, status.land, status.nw, status.rid, status.race, users.sygnatur, users.sygnatur_background from users,status where users.sygnatur not like '' and users.konzernid = status.id", "id");
$synd_names = assocs("select synd_id, name from syndikate", "synd_id");
$honors_raw = assocs("select * from honors");
$stats_raw = assocs("select * from stats where alive > 0", "id");
foreach ($honors_raw as $vl) {
	$honors[$vl['user_id']][$vl['honorcode']] += 1;
}
foreach ($stats_raw as $vl) {
	$stats[$vl['user_id']][$vl['id']] = $vl;
}


$starttime = time();
$count = 0;

foreach ($users_sygnatur_to_print as $vl) {
	$synd_name = ($globals['roundstatus'] > 0 ? $synd_names[$vl['rid']]['name'] : false);

	if ($vl['sygnatur_background'] == "race") $race_for_bg = $vl['race']; else $race_for_bg = $vl['sygnatur_background'];
	if ($vl['sygnatur'] == "honors") $data = $honors[$vl['id']];
	else $data = $stats[$vl['id']];
	
	print_sygnatur($vl['username'], $vl['id'], $vl['syndicate'], $vl['land'], $vl['nw'], $synd_name, $vl['rid'], $race_for_bg, ($globals['roundstatus'] > 0 ? single("select count(*) from status where nw > ".$vl['nw'])+1 : 0), $vl['sygnatur'], $data);
	$count++;
	echo $vl['username']. " ... ".$vl['id']."\n";
}
system("chmod 777 ".DATA.'sygnatur/*');
$endtime = time();



echo "images created: ".$count."\n";
echo "time needed: ".($endtime-$starttime)."\n";





?>
