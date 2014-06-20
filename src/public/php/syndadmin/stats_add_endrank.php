<?
set_time_limit(3600);

require("../../../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

$globals = assoc("select * from globals order by round desc limit 1");

$stats = assocs("select * from stats where alive > 0");

foreach ($stats as $vl) {
	$stats_by_round[$vl['round']][] = $vl;
	if ($vl['endrank'] != 0) $already_done[$vl['round']] = 1;
}

$time = time();
if ($globals['roundendtime'] < $time) $globals['round']++;

for ($i = 1; $i < $globals['round']; $i++) {
	if (!$already_done[$i]) {
		$temp = array();
		foreach ($stats_by_round[$i] as $vl) {
			$temp[$vl['konzernid']] = $vl['lastnetworth'];
		}
		arsort($temp);
		$rank = 0;
		foreach ($temp as $ky => $vl) {
			$rank++;
			select("update stats set endrank = $rank where konzernid = $ky and round = $i");
			echo "update stats set endrank = $rank where konzernid = $ky and round = $i\n";
		}
	}
}


?>
