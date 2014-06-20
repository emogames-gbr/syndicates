<?


require("../../../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

$globals = assoc("select * from globals order by round desc limit 1");

$stats = assocs("select * from stats");

foreach ($stats as $vl) {
	$stats_by_round[$vl['round']] = $vl;
	if ($vl['endrank'] != 0) $already_done[$vl['round']] = 1;
}

for ($i = 1; $i < $globals[round]; $i++) {
	if (!$already_done[$i]) {
		$temp = array();
		foreach ($stats_by_round[$i] as $vl) {
			$temp[$vl['konzernid']] = $vl['lastnetworth'];
		}
		arsort($temp);
		foreach ($temp as $ky => $vl) {
			echo $vl."\n";
		}
	}
}


?>
