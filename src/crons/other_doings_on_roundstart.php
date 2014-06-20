<?
##
##	Dieses Skript macht nur Gruppenverteilung und wird daher nichf fr den Basic Server angepasst
##
##
require_once("../includes.php"); // Subfunctions laden
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once("../inc/ingame/globalvars.php"); // fr get_an_empty_syndicate() wichtig!
$time = time(); // Zeit zur skriptausfhrung als timestamp, bitte spï¿½er nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.
$microtime = getmicrotime();
$hourtime = get_hour_time($time);
mt_srand($time);


$globals = assoc("select * from globals order by round desc limit 1");

if (($time >= $globals[roundstarttime] - 50 && $time <= $globals[roundstarttime] + 50)) {

	if ($db == "syndicates" && $globals['round'] == 26) {
		$users = singles("select emogames_user_id from users where konzernid > 0");
		$amount = 100;
		$reason = 'Zum Rundenstart von Runde 24 wieder dabei - kleine Wiedergutmachung für die verpatzte Runde 23';
		$identifier = "pawldwl2";
		foreach ($users as $vl) {
			EMOGAMES_donate_bonus_emos($vl,$amount,$reason,$identifier);
		}
	}
}
else {
	1;
}


function writelog($text) {
	global $globals;
	static $print;
	if (func_num_args() > 0) {
		$print .= $text;
		if (func_num_args() > 2) {
			$writelogdatei = "startroundwritelog_$globals[round].txt";

			if (!$handle = fopen("$writelogdatei", 'a')) {
					echo "Cannot open file ($filename)";
					exit;
			}
			if (!fwrite($handle, $print)) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);
		}
	}
}

?>
