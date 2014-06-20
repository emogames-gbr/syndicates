<?


require("../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require(INC."ingame/globalvars.php");

$servers = assocs("select * from servers");

$time = time();
$tolate = $time - AUFTRAGTIME;
foreach ($servers as $temp) {
	mysql_select_db($temp[db_name]);
	$eviljobs = assocs("select * from jobs where accepttime < $tolate");
	foreach ($eviljobs as $temp) {
		end_job($temp[id],1);
	}
	connectdb();
}


?>
