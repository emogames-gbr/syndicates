<?

$time = time();

$daytime =	get_day_time($time);
define("DAY",24*60*60);

$daytime += DAY;

$globals = assoc("select * from globals order by round desc limit 1");


echo"<table border=1><tr><td>Tag</td><td># User aktiv</td></tr>";

for ($i=0;$i < 60;$i++) {
	$daytime_orig = $daytime;
	$daytime = $daytime - DAY;
	if ($daytime < $globals[roundstarttime]) break;
	
	
	$stmt = "select count(*) from (
			select distinct user_id from (
			select  user_id from sessionids_actual where angelegt_bei >= $daytime && angelegt_bei < $daytime_orig
			union 
			select  user_id from sessionids_safe where angelegt_bei >= $daytime && angelegt_bei < $daytime_orig
			) as r 
			) as bla";
	
	$anzahl_user =single("$stmt");
	
	
	echo "<tr>
			<td>".mytime($daytime)." bis ".mytime($daytime_orig)."</td>
			<td>$anzahl_user</td>
		  </TR>
	";
	
}
echo"</table>";


?>