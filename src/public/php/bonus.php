<?php

//**************************************************************************
//	Game.php includen
//**************************************************************************

require_once("../../inc/ingame/game.php");

//**************************************************************************
//	Uebergabe Variablen checken
//**************************************************************************
if ($globals[roundstatus] == 1 && $globals[updating] != 1) {
	$hourtime = get_hour_time($time);
	$hour = date("H",$time);
	$daytime = $hourtime - $hour*60*60;
	$clickcount1 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $hourtime group by type", "type");
	$clickcount24 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $daytime group by type", "type");
	$bonuscount = $status[land]*30;
	$bonuscount -= $clickcount24[1][count]*$status[land]*4;
	if ($bonuscount < 0) {$bonuscount = 0;}

	if ($type == 1) {

		if (!$clickcount1[1][count]) {

			$links = array(
			);
			select("update status set money=money+$bonuscount where id=$status[id]");
			select("insert into bonusklicks (user_id,time,bonus,page,type) values ($status[id],$time,$bonuscount,'$site', 1)");
			select("update sessionids_actual set locked = 0,microlocked=0 where user_id=".$status[id]);
			header("location: ".$links[$site]."");
		}
		else {
			f("Du hast deinen Credit-Bonus diese Stunde bereits erhalten! Versuche es bitte sp&auml;ter wieder.");
		}
		
	}
	elseif ($type == 2) {

		$bonuscount = ceil($status[land]*0.001);
		if ($bonuscount > 5) $bonuscount = 5;
		$land_in_order = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_id = '127' and time > $time");
		if (getServertype() != "basic" OR BASIC_MAX_LANDGRENZE > ($status[land]+$land_in_order)) {
			if (!$clickcount1[2][count]) {
				if ($clickcount24[2][count] < 5)
				$linkdata = assoc("select * from bonus_links where id = '".floor($site)."'");
				if ($linkdata) {
					if (getServertype() == "basic" && BASIC_MAX_LANDGRENZE - $status['land'] - $land_in_order < $bonuscount) {
						$bonuscount = BASIC_MAX_LANDGRENZE - $status['land'] - $land_in_order;
						if ($bonuscount < 0) $bonuscount = 0;
					}
					select("update status set land=land+$bonuscount where id=$status[id]");
					select("insert into bonusklicks (user_id, time, bonus,page,type) values ($status[id], $time, $bonuscount, '$linkdata[linktext]', 2)");
					select("update bonus_links set klicks=klicks+1 where id=$linkdata[id]");
					select("update sessionids_actual set locked = 0,microlocked=0 where user_id=".$status[id]);
					header("Location: ".$linkdata[url]);
				} else {
					f("Ungültigen Link gewählt. Es wurde kein Bonus vergütet.");
				}
			} else {
				f("Du hast deinen Land-Bonus diese Stunde bereits erhalten! Versuche es bitte naechste Stunde wieder.");
			}
		} else {
			f("Du kannst keinen Land-Bonus mehr nehmen, da du die Maximalzahl möglichen Landes erreicht hast (".BASIC_MAX_LANDGRENZE.")");
		}
	}
        elseif ($type == 4 && isset($oster_boni)) { // Ostern!!
        	event_ostern_auszahlen($egg);
        }
	else { f("Falschen Bonustyp angegeben!"); }

}
elseif($globals[updating]) {
	f("Während des stündlichen Updates kannst du deinen Bonus nicht nutzen. Versuche es bitte nach dem Update noch einmal.");
}
else {
	f("Du kannst den Bonus nur bekommen, w&auml;hrend die Runde l&auml;ft.");
}

require_once("../../inc/ingame/header.php");
require_once("../../inc/ingame/footer.php");

?>
