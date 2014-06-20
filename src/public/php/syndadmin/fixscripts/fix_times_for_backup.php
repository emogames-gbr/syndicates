<?


//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//


require("../../../../includes.php");
$handle = connectdb($SERVER_NAME);


$time_backup_from = 1157594400; ## Zeit zu der das Backup erstellt wurde

$time_to_fix = 3 * 86400; ## Zeit die zwischen Erstellen des Backups und Einspielzeit verstrichen ist



$tables_to_fix = array(
'aktien_privat' , 'time',
'aktien_privatlogs' , 'time',
'aktien_safekurse' , 'time',
'aktienlogs' , 'time',
'allianzen_anfragen' , 'time',
'allianzen_anfragen' , 'endtime',
'allianzen_kuendigungen' , 'time',
'attacklogs' , 'time',
'boerse_buffer' , 'time',
'bonusklicks' , 'time',
'build_buildings' , 'time',
'build_logs' , 'time',
'build_logs' , 'time_end',
'build_military' , 'time',
'build_sciences' , 'time',
'build_spies' , 'time',
'build_syndarmee' , 'time_send',
'build_syndarmee' , 'time_there',
'globals' , 'roundendtime',
'heaptable2' , 'clicktime',
'jobs' , 'inserttime',
'jobs' , 'onlinetime',
'jobs' , 'accepttime',
'jobs_logs' , 'inserttime',
'jobs_logs' , 'onlinetime',
'jobs_logs' , 'accepttime',
'jobs_logs' , 'finishtime',
'kosttools_gebaeudeq_abgearbeitet' , 'time',
'lager_buffer' , 'time',
'lagerlogs' , 'time',
'link_klick_count' , 'time',
'losslogs' , 'time',
'market' , 'inserttime',
'market_buffer' , 'time',
'market_gebote' , 'time',
'market_gebote_logs' , 'buytime',
'marketlogs' , 'time',
'message_values' , 'time',
'military_away' , 'time',
'naps_spieler_spezifikation' , 'gekuendigt_time',
'nw_safe' , 'time',
'nw_statsfeature' , 'time',
'nw_statsfeature_safe' , 'time',
'options_defect' , 'time',
'options_konzerndelete' , 'time',
'options_namechange' , 'time',
'politik_kick' , 'time',
'politik_synfus' , 'time',
'polls' , 'time',
'polls' , 'time_bis',
'ressources' , 'time',
'sessionids_safe' , 'angelegt_bei',
'sessionids_safe' , 'gueltig_bis',
'sessionids_actual' , 'angelegt_bei',
'sessionids_actual' , 'gueltig_bis',
'spylogs' , 'time',
'status' , 'createtime',
'status' , 'lastupdatetime',
'status' , 'lastlogintime',
'status' , 'istp_changetime',
'syndikate' , 'announcement_lastchangetime',
'syndikate_anfaenger_inaktivenverschiebungen' , 'time',
'syndikate_data_safe' , 'time',
'towncrier' , 'time',
'transfer' , 'time',
'users_votes' , 'time',
'wars' , 'starttime',
'wars' , 'endtime',
'wars_zustimmungen' , 'time');



for ($i = 0; $i < count($tables_to_fix); $i+=2) {
	$ky = $tables_to_fix[$i];
	$vl = $tables_to_fix[$i+1];

	$statement = 'update '.$ky.' set '.$vl.' = '.$vl.' + '.$time_to_fix.' where '.$vl.' >= '.($time_backup_from-3*86400); ## -3*86400 z.B. wg. Attacklogs und Schutzzeit
	//select($statement);
	echo mysql_affected_rows()." affected rows for statement: $statement\n";
}

//select("update globals set roundstarttime = roundstarttime + $time_to_fix");





?>
