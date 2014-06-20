<?


require("../subs.php");
connectdb();
$time = time();

$keyabos = assocs("select * from payment_aboinfo where (paid=1 or zeitraum_start >= $time or frist > 0) and zeitraum_artikelid in (199,203)");

$keys = singles("select distinct abo_id from abo_keys where abo_id != 1");
//pvar($keys,keys);

foreach ($keyabos as $vl) {
	//pvar($vl);
	$aboids[] = $vl[aboid];
}

foreach ($keys as $vl) {
	if (!in_array($vl,$aboids)) {
		$TOKICK[] = $vl;
	}
}

pvar($aboids);

pvar($TOKICK,tokick);

$keyids = array();
if (count($TOKICK) > 0) {
	foreach ($TOKICK as $tmp) {
		$tkeyids = singles("select abo_key from abo_keys where abo_id=$tmp");
		$keyids = array_merge($keyids,$tkeyids);
	}

	pvar($keyids,keyids);

	$instring = "('".implode("','",$keyids)."')";

	// Jetzt löschen
	$q1 = ("delete from abo_keys where abo_key in $instring");
	$q2 = ("delete from user_keys where abo_key in $instring");
	select($q1);
	select($q2);
	//pvar($instring);
	pvar($q1);
}

?>

