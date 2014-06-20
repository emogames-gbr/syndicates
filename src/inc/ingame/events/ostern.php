<?
	/**
	 * Autor: Konstantin Grupp/inok
	 * 
	 * wird eingebunden in inc/events.php
	 * 
	 */
	
	// 	Osterboni
	if (Events::getSettings('ostern','starttime') < $time && 
		$time < Events::getSettings('ostern','endtime')) {
		$boni = Events::getEvent('ostern');
		if (!$boni) $boni = 0;
		$oster_boni = array('14923' => 1, 	// merchandise.tpl
							'18439' => 1, 	// gamevalues.tpl/boerse.tpl
							'19237' => 1, 	// statusseite.tpl
							'25012' => 1, 	// report.tpl
							'39486' => 1);	// settings.tpl
		$oster_boniStatus = array('14923' => 1, '18439' => 2, '19237' => 4, '25012' => 8, '39486' => 16);
		foreach ($oster_boni as $ky => $vl) {
			if ($boni % 2) {
				//pvar($ky.' boni '.$boni.' rest: '.($boni % 2));
				$boni--; $oster_boni[$ky] = false;
			}
			$boni = $boni / 2;
		}
		$tpl->assign('IS_OSTERN', true);
		$tpl->assign('OSTER_BONI_AMOUNT', pointit(Events::getSettings('ostern','amount')));
		$tpl->assign('OSTER_BONI', $oster_boni);
	}
	
	// Osterboni werden unter bonus.php verteilt!!
	function event_ostern_auszahlen($egg) {
		global $tpl, $oster_boni, $oster_boniStatus, $status;
		
		$egg = addslashes($egg);
       	if (isset($oster_boni[$egg]) && $oster_boni[$egg]) {
       		$amount = Events::getSettings('ostern','amount');
       		select("UPDATE status SET money=money+$amount WHERE id=$status[id]");
       		$boni = Events::getEvent('ostern');
       		if ($boni) {
       			$boni += $oster_boniStatus[$egg];
       			select("UPDATE events SET value = '".$boni."' WHERE type = 'ostern' AND konzernid = ".$status['id']);
       		} else {
       			select("INSERT INTO events (type, konzernid, value) VALUES ('ostern', ".$status['id'].", ".$oster_boniStatus[$egg].")");
       		}
       		$tpl->assign("MSG", "Durch das finden des Osterei's hast du dir ".pointit($amount)." zusätzliche Credits verdient.");
			Events::assign("sys_msg.tpl");
       	} else {
       		$tpl->assign("ERROR", "Das Osterei wurde schon gefunden, dafür gibts auch keinen Bonus!");
			Events::assign("fehler.tpl");
       	}
	}

?>