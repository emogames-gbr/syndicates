<?
////////////
// Config///
////////////

define(DAUER, 60*60*24*14); // Nach 2 Wochen werden geworbene Spieler berücksichtigt
define(MINLAND,0); // geworbener Spieler muss mindestens 1000 Land haben

$profit = array(
			land => 50,
			money => 200000,
			energy => 0,
			metal => 20000,
			sciencepoints => 5000
		  ); // Belohnung für werbenden Spieler

$names = array(
			land => "Land",
			money => "Credits",
			energy => "Energie",
			metal => "Erz",
			sciencepoints => "Forschungspunkte"
		 );

$bonusstring = "Für die Werbung eines mitspielers haben sie folgende Prämie erhalten:<br><br>";

/////////////////
// Config ende //
/////////////////

require("../subs.php");

$runs = @file_get_contents("wskriptruns.txt");

if (!$runs) {
	$handle = fopen("wskriptruns.txt", 'w');
	fwrite($handle, 1);
	fclose($handle);



	connectdb();
	$time = time();

	/////////////////////////////////
	// Daten aus db holen ///////////
	/////////////////////////////////

	$globals = assoc("select * from globals order by round desc limit 1");
	$werbungen = assocs("select * from werbungen where time < ".($time-DAUER)." and status=0");
	$users = assocs("select id,konzernid from users","id");
	$statuses = assocs("select id,land,alive from status","id");
	$keys = singles("select user_id from user_keys");
	$abos = singles("select userident from payment_aboinfo where paid=1 or zeitraum_start > $time");

	function my_paid($id) {
		global $keys,$abos;
		if (in_array($id,$keys) || in_array($id,$abos)) {
			return 1;
		}
		else {
			return 0;
		}
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////
	// Berechnungen

	foreach ($profit as $key =>  $value) {
		if ($value > 0) {
			$bonusstring .="<li><b>".pointit($value)."</b> $names[$key]";
		}
	}

	foreach ($werbungen as $key => $value) {
		//pvar($statuses[$users[$value[geworben_id]][konzernid]][land],land);
		if ($statuses[$users[$value[geworben_id]][konzernid]][land] >= MINLAND && my_paid($value[geworben_id])) {
			$upstring = "update status set ";
			foreach ($profit as $ky => $val) {
				$upstring .=" $ky=$ky+$val,";
			}
			if ($statuses[$users[$value[werber_id]][konzernid]][alive] > 0 && $value[time] >= $globals[roundstarttime]) { // Wenn werbender Spieler lebenden Account Konzern hat und Werbung in aktueller Runde geschehen ist
				$upstring = chopp($upstring);
				$upstring .=" where id=".$statuses[$users[$value[werber_id]][konzernid]][id];
				$queries[] = $upstring;
				$queries[] = "update werbungen set status=1 where geworben_id=$value[geworben_id]";
				$queries[] = "insert into message_values (id,user_id,time,werte) values (44,$value[werber_id],$time,'$bonusstring')";
				unset($upstring);
			}
		}
	}

	//pvar($queries,queries);
	db_write($queries);
	echo "run";
	exec("rm wskriptruns.txt");
}
else {
	echo "Werbeskriptfehler!";
	$betreff = "Werbeskriptfehelr!";
	$message = "$time\n";
	$email ="admin@DOMAIN.ch";
	$to = "admin@DOMAIN.ch";
	sendthemail($betreff,$message,$email,$to);
	sleep(10);
	exec("rm wskriptruns.txt");
}

?>
