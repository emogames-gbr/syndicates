<?php


$maxcount = 20;
require_once("../includes.php");

$handle = connectdb(); // Datenbankverbindung herstllen
//if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
//$db = $argv[1];
$serverdbs = singles("select db_name from servers");

//echo $db;
require_once(LIB."/picturemodule.php");
connectdb();
mysql_select_db($db);

$time = time();

//debug
select("insert into `check_cron` (time) values (".$time.")");

mt_srand($time);

$runs = @file_get_contents("ccoderuns.txt");
if (!$runs) {
	$handle = fopen("ccoderuns.txt", 'w');
	fwrite($handle, 1);
	fclose($handle);
	$count = single("select count(*) from codes");
	if ($count >= $maxcount) {
		exec("rm ".DATA."codes/* -r");
		echo "Renew All Codes!";
		foreach ($serverdbs as $temp) {
			mysql_select_db($temp);		
			select("delete from codes");
			for ($a =0;$a < 4; $a++) {
				$string = "";
				for ($i=0;$i < 3;$i++) {
					$string .= mt_rand(0,9);
				}
				create_code($string);
				sleep(1);
			}
		}
		connectdb();
	}

	echo "\nDone\n";
	
		foreach ($serverdbs as $temp) {
			mysql_select_db($temp);		
	
			$string = "";
			for ($i=0;$i < 3;$i++) {
				$string .= mt_rand(0,9);
			}
			create_code($string);
		}
		connectdb();

	exec("rm ccoderuns.txt");
}
else {
}

?>
