<?


require("../subs.php");
connectdb();
$time = time();
mt_srand($time);

// Keys generieren, wenn noch nicht vorhanden
$number = 20;
$stmt = "insert into abo_keys (abo_key,abo_id,round,time) values ";
$tkeys = array();
for ($i = 0; $i < $number; $i++) {
	$tkex = 1;
	while($tkex || in_array($tkey,$tkeys)) {
		$tkey = createabokey();
		$tkex = single("select abo_key from abo_keys where abo_key='$tkey'");
	}
	$tkeys[] = $tkey;
	$stmt.="('$tkey',1,127,$time),";
}
$stmt=chopp($stmt);
select($stmt);


$keys = assocs("select * from abo_keys where abo_id=1");

foreach ($keys as $value) {
	$instring.="'$value[abo_key]',";
}
$instring=chopp($instring);
$usedkeys = singles("select upper(abo_key) from abo_keys where abo_id=1");
$i=0;
foreach ($usedkeys as $temp) {
	if ($i % 20 == 0) {
		echo "i:".$i."\n";
	}
	$i++;
	echo "$temp \n";
}

function createabokey() {
	return createkey(0,16);
}


?>
