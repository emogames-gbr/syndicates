<?php
require("../../../includes.php");
connectdb();
set_time_limit(3600);

$time = time();



$blacklist = array();

$data = assocs("select vorname, nachname, username, email from users where emogames_user_id > 0 and lastroundplayed = 25 and multi_id = 0 and konzernid = 0");
//$data = assocs("select vorname, nachname, username, email from users where username = 'Bogul'");

$time = time();
echo "TIMESTAMP: $time\nCOUNT DATA: ".count($data)."\nCOUNT BLACKLIST: ".count($blacklist)."\n\n";
$i = 0;
foreach ($data as $vl) {
	if (!in_array($vl[email], $blacklist)) {
		$betreff = "Server läuft wieder - Anmeldestart zu Runde 24";
		$body = "Hallo ".$vl[vorname].",\n\nSyndicates befindet sich nach den Serverausfällen der letzten Tagen jetzt wieder in der Anmeldephase zu Runde 24.\n\nRundenstart ist am Fr., den 15. September 2006 um 20:00 Uhr.\n\nUm dich für die verpatzte Runde 23 zu entschädigen, erhältst du einmalig als Wiedergutmachung 100 Bonus-EMOs, wenn du dich bis Rundenstart anmeldest.\n\nWir würden uns sehr darüber freuen, dich diese Runde wieder bei Syndicates begrüßen zu dürfen.\n\nMit freundlichen Grüßen,\n\nBogul und Scytale\n Emogames\n\n\nhttp://syndicates-online.de";
		sendthemail($betreff, $body, $vl[email], $vl[vorname]." ".$vl[nachname]);
		echo ++$i;
		echo " ".$vl[username]."\n";
	} else echo "in blacklist: $vl[email]\n";
}

$time = time();
echo "\n\nTIMESTAMP ENDE: $time\n\n";

//select("update users set premiumfeature_mailaktion = 0");
//select("update users set premiumfeature_mailaktion = 1 where emogames_user_id > 0 and lastroundplayed <= 22 and multi_id = 0 and konzernid = 0");

?>
