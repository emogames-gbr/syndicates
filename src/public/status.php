<?

require("../includes.php");
require(LIB."picturemodule.php");
connectdb();

$status = 200;
$time = time();

$updatetime = single("select time from updates order by time desc limit 1");
$updateendtime = single("select endtime from updates order by time desc limit 1");
$dbtest = single("select round from globals order by round desc limit 1");
$last5codes = singles("select code_id from codes order by code_id desc limit 5");
if (!$dbtest) {$status = 502;} // Db tut nicht
if ($updatetime - get_hour_time($time) > 120 || $updatetime - get_hour_time($time) < -120) {$status = 503;} // Update nicht gelaufen
if ($updateendtime - get_hour_time($time) > 500 || $updateendtime - get_hour_time($time) < -500) {$status = 503;} // Update h?ngt
foreach ($last5codes as $temp) {
	$picuturecode = showcode($temp);
	if (!$picuturecode) {$status = 504;}
}

echo $status;


?>
