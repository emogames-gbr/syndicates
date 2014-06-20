<?php
require("../subs.php");
connectdb();
/*
$statusdata = assocs("select * from status");
$globals = assoc("select * from globals order by round desc limit 1");

foreach ($statusdata as $temp) {
	select("update stats set rid=$temp[rid] where round=$globals[round] and konzernid=$temp[id]");
}
*/

/*
$smallids = assocs("select count(*) as number,rid from status where rid > 0 group by rid order by rid asc");

foreach ($smallids as $value) {
	if ($value[number] <= 10) {
		$ssids[] = $value;
	}
}

foreach ($ssids as $value) {
	if (!$temp) {$temp = $value[number];$temprid = $value[rid];}
	elseif ($temp) {
		if ($value[number] + $temp <= 20) {

			$query1 = ("update status set rid=$temprid where rid=$value[rid]");
			$query2 = ("update status set rid=rid-1 where rid > $value[rid]");
			$query3 = ("delete from syndikate where synd_id=$value[rid]");
			$query4 = ("update syndikate set synd_id = synd_id-1 where synd_id>$value[rid]");
			select($query1);
			select($query2);
			select($query3);
			select($query4);
			pvar($query1,q1);pvar($query2,q2);pvar($query3,q3);pvar($query4,q4);
			unset($temp,$temprid,$query1,$query2,$query3,$query4);
			unset($ssids,$smallids);
			break;
		}
	}
}
*/



?>
