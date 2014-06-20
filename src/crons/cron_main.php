<?
/*****************

Emogames.de Main Cronjob
Runs minutely
decides which cronjob has to run

*****************/


/*******************
*** CONFIG *********
*******************/

// Cronjobs to run minutely:

$minutely = array(
	"checkjobs.php syndicates",
	"update_features_table.php syndicates",
	"startround.php syndicates",
	"other_doings_on_roundstart.php syndicates",
	"market_gebote.php syndicates",
	"boerse_update.php syndicates",
	"serverload.php syndicates",
	"admin_case_strafen_vollziehen.php syndicates",
	"publisher.php syndicates",
);

// Cronjobs to run hourly:
$hourly = array(
	"update.php syndicates",
	"createcode.php syndicates",
	"delete_old_features.php syndicates",
	"heapupdate.php syndicates",
	"anfaenger_syndikate_doings.php syndicates",
	"stats_table_synchronizing.php syndicates",
	"print_sygnatur.php syndicates",
	"prepareNewRound.php syndicates",
);

$once_a_day = array(		# Einmal am Tag, Angabe der Stunde zu der die t?gliche Ausf?hrung erfolgen soll m?glich



);

/*******************
*** CONFIG  ENDE ***
*******************/






$time = time();



// Execute hourlies
if ((int) ($time % 3600) < 60) {
	if ($hourly) {
		foreach ($hourly as $temp) {
			(system ("php $temp &> ".preg_replace("/ /", "", $temp).".log"));
		}
	}
}

// Execute minutelies
foreach ($minutely as $temp) {
	if ($minutely) {
		(system ("php $temp &> ".preg_replace("/ /", "", $temp).".log"));
	}
}



// Execute once a day

if ((int) ($time % 3600) < 60) {
	if ($once_a_day) {
		$hour = date("H",$time);
		for ($i = 0; $i <= count($once_a_day); $i += 2) {
			if ($once_a_day[$i+1] == $hour) {
				system ("php ".$once_a_day[$i]);
			}
		}
	}
}

?>
