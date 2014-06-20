<?
/*****************

Emogames.de Main Cronjob
Runs minutely
decides which cronjob has to run

*****************/


/*******************
*** CONFIG ******
******************/

// Cronjobs to run minutely:

$minutely = array();

// Cronjobs to run hourly:
$hourly = array();




$time = time();



// Execute minutelies
foreach ($minutely as $temp) {
	(system ("php $temp"));
}


// Execute hourlies
if ((int) ($time % 3600) < 10) {
	foreach ($hourly as $temp) {
		system ("php $temp");
	}
}

?>
