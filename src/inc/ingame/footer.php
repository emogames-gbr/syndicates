<?
$tpl->assign('FFORENLINK', "fragen_und_antworten_board.php");
$tpl->assign('DATE_NOW', date("Y",$time));

if ($loggedin) $tpl->assign('LOGIN', true); 

if ((!$features[WERBUNG_DEAKTIVIERT] and ($time - $status[createtime] > 14 * 86400 or !is_new_player($id)) )) {
	if (!$init || $init != 2) {
				
		$gtg = "

		";
		
		$tpl->assign('FOOTER_GTG', $gtg);
	}
}

$tpl->assign('NOTEPAD', $features[KOMFORTPAKET]);


$end = getmicrotime();
$diff = $end - $start;

if ($status[id] && !$status[locked] || ($time-$status[locked]) > 3 && $status[locked]) {
	select("update sessionids_actual set locked = 0,microlocked=0 where user_id=".$status[id]);
}
ignore_user_abort(FALSE);

$googleanalytics = "
";
if (!isBasicServer($game)) {
	$omnimonanalytics = "<img style=\"width:1px\" src=\"http://emogames.put.omnimon.de/datapixel.php?name=Syndicates_PIs_Classic&value=1&time=$time\">";
}
else{
	$omnimonanalytics = "<img style=\"width:1px\" src=\"http://emogames.put.omnimon.de/datapixel.php?name=Syndicates_PIs_Classic&value=1&time=$time\">";
}


$tpl->assign('ANALYTICS', $googleanalytics.$omnimonanalytics);
if (!$events_NoDisplay) { // manche events wollen den Footer manipulieren
	$tpl->display("footer.tpl");
} 
?>
