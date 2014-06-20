<?
require("../subs.php");
connectdb();
$time = time();
$payment_aboinfodata = assocs("select * from payment_aboinfo");

foreach ($payment_aboinfodata as $value) {
	$valuesstring.="(";
	foreach ($value as $key => $tvalue) {
		if (!$keyone) {
			$keystring .="$key,";
		}
		$valuesstring.="'$tvalue',";
	}
	$valuesstring.="$time),";
	if (!$keyone) {
		$keystring.="time";
	}
	$keyone = 1;
}
$valuesstring=chopp($valuesstring);

$query = "insert into payment_aboinfo_datesave ($keystring) values $valuesstring";
echo $query;
select($query);

?>
