<?

require_once("../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

$time = time();

$servers = assocs("select * from servers");

foreach ($servers as $temp) {
	mysql_select_db($temp[db_name]);


select("delete from users_features where time_bis < $time");

}


?>
