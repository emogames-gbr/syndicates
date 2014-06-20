<?


include("inc/general.php");

// Created by Jannis Breitwieser
// 270104 - 23:29
// Bei den buildlogs muss noch nachgebessert werden (das was bisher nicht mitgelogt wurde)

$konid = (int) $konid;
$ttime = (int) $ttime;
if (!$ttime) {$ttime = $time;}


if ($ia == "trace") {

	// Log this call for the super game master to check
	$targetUserId = single("select id from users where konzernid = ".$konid);
	if ($targetUserId==$id && false){
		echo "Kein Zugriff auf eigenen Konzern!";
		exit;
	}
	
	if ($targetUserId) {
	  $logentries = assocs("select * from admin_user_view_history where user_id = ".$id." and target_id = $targetUserId and time > ".$id_data['angelegt_bei'], "id");
	  if (!$logentries) {
	    select("insert into admin_user_view_history (user_id, target_id, time, opened_actions_history) values ($id, $targetUserId, $time, 2)");
	  } else {
	    $entries_ids = join(",", array_keys($logentries));
	    select("update admin_user_view_history set opened_actions_history = 1 where opened_actions_history = 0 and id in ($entries_ids)");
	  }
	}



	$todo = array(marketlogs,spylogs,lagerlogs,attacklogs,buildlogs,aktienlogs,aktienprivatlogs,transferlogs,jobs,jobs_logs,'reset','syndarmee','synwechsel');
	$output = array();
	$players = assocs("select id,syndicate, rid from status","id");
	$thisplayer = assoc("select * from status where id=$konid");
	$baselink = "<a href=\"".$self."?action=traceuser&ia=trace&ttime=".$ttime."\"";
	$status = assoc("select * from status where id=$konid");
	$ausgabe.="<b>Konzern: ".$status[syndicate]." (#$status[rid]) [$status[race]]</b><br><br> ";
	$href= $self."?action=traceuser&ia=trace&ttime=".$ttime;
	// Marketlogs
	if (in_array(marketlogs,$todo)) {
		$stuff = assocs("select * from marketlogs where (owner_id=$konid or user_id=$konid) and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			$product = changetype($value[type],$value[prod_id]); $product = $product[product];
			if ($value[owner_id] == $konid) {
					$output[$value[time]] = "<font color=red><b>Market Sold: </b></font>".pointit($value[number])." ".($product)." an <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> verkauft (VP: ".$value[price]."/".$value[price100]." = ".$value[price100percentage]."%;)";
			}
			if ($value[user_id] == $konid) {
				if ($value[action] == "back") {
					$output[$value[time]] = "<font color=red><b>Market Back: </b></font>".pointit($value[number])." ".($product)." zurückgenommen";
				}
				if ($value[action] == "buy") {
					$output[$value[time]] = "<font color=red><b>Market Buy: </b></font>".pointit($value[number])." ".($product)." von <a href=\"$href&konid=".$players[$value[owner_id]][id]."#$value[time]\">".$players[$value[owner_id]][syndicate]." (#".$players[$value[owner_id]][rid].")</a> gekauft (KP: ".$value[price]."/".$value[price100]." = ".$value[price100percentage]."%;)";
				}
				if ($value[action] == "sell") {
					$output[$value[time]] = "<font color=red><b>Market Input: </b></font>".pointit($value[number])." ".($product)." eingestellt (VP: <b>".$value[price]."</b>/".$value[price100]." = <b>".$value[price100percentage]."%</b>;".(($value[price100percentage] > 0 && $value[price100percentage] < 42) ? "<font color=red><b> < 42% WARNING!</b></font>;":"")." nmeo: ".$value[pricenextmoreexpensiveoffer].")";
				}
			}
			unset($product);
		}
	}
	// Jobs
	if (in_array(jobs,$todo)) {
		$stuff = assocs("select * from jobs where (user_id = $konid or target_id=$konid or acceptor_id=$konid) and inserttime >= ".($time-$ttime));
		foreach ($stuff as $value) {
			// Eingestellt oder Ziel
			if ($value[user_id] == $konid || $value[target_id] == $konid) {
				while ($output[$value[inserttime]]) {
					$value[inserttime]++;
				}
				
				if ($value[user_id] == $konid) {
					// Eingestellt
					$output[$value[inserttime]] = "<font color=brown><b>Job eingestellt:</b></font>".pointit($value[money])."Cr für ".$value[type]."  gegen
					<a href=\"$href&konid=".$players[$value[target_id]][id]."#$value[time]\">".$players[$value[target_id]][syndicate]." (#".$players[$value[target_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym]
						
					";
				}
				
				if ($value[target_id] == $konid) {
				
					// Ziel
					$output[$value[inserttime]] = "<font color=brown><b>Job gegen diesen Spieler eingestellt:</b></font>".pointit($value[money])."Cr für ".$value[type]."  von
					<a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym]
					";
				}
			}
			// Angenommen
			if ($value[acceptor_id] == $konid || $value[target_id] == $konid) {
				while ($output[$value[accepttime]]) {
					$value[accepttime]++;
				}
				if ($value[accepttime] >= $time - $ttime) {
				  if ($value[acceptor_id] == $konid) {
					  // Angenommen
					  $output[$value[accepttime]] = "<font color=brown><b>Job  angenommen:</b></font>".pointit($value[money])."Cr für ".$value[type]."  von
					  <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym] - gegen Spieler
					  <a href=\"$href&konid=".$players[$value[target_id]][id]."#$value[time]\">".$players[$value[target_id]][syndicate]." (#".$players[$value[target_id]][rid].")</a>";
				  }
				  
				  if ($value[target_id] == $konid) {
					  // Ziel
					  $output[$value[accepttime]] = "<font color=brown><b>Job gegen diesen Spieler angenommen:</b></font>".pointit($value[money])."Cr für ".$value[type]."  von
					  <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym] - angenommen von 
					  <a href=\"$href&konid=".$players[$value[acceptor_id]][id]."#$value[time]\">".$players[$value[acceptor_id]][syndicate]." (#".$players[$value[acceptor_id]][rid].")</a>				
					  ";
				  }
				}
			}
		}
	}
	
	// Jobslogs
	
	if (in_array(jobs_logs,$todo)) {
		$stuff = assocs("select * from jobs_logs where (user_id = $konid or target_id=$konid or acceptor_id=$konid) and finishtime >= ".($time-$ttime));
		foreach ($stuff as $value) {
			// Eingestellt oder Ziel
			if ($value[user_id] == $konid || $value[target_id] == $konid || $value[acceptor_id]) {
				while ($output[$value[finishtime]]) {
					$value[finishtime]++;
				}
				while ($output[$value[accepttime]]) {
					$value[accepttime]++;
				}
				
				if ($value[user_id] == $konid) {
					// Eingestellt
					$output[$value[finishtime]] = "<font color=pink><b>Joblog eingestellt:</b></font>".pointit($value[money])."Cr für ".$value[type]."  gegen
					<a href=\"$href&konid=".$players[$value[target_id]][id]."#$value[time]\">".$players[$value[target_id]][syndicate]." (#".$players[$value[target_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym] - angenommen von: 
					<a href=\"$href&konid=".$players[$value[acceptor_id]][id]."#$value[time]\">".$players[$value[acceptor_id]][syndicate]." (#".$players[$value[acceptor_id]][rid].")</a>
					<b>success: $value[success]</b>wd
						
					";
				}
				
				if ($value[accepttime] >= $time-$ttime) {
				  if ($value[target_id] == $konid) {
					  // Ziel
					  $output[$value[accepttime]] = "<font color=pink><b>Joblog GEGEN diesen Spieler angenommen:</b></font>".pointit($value[money])."Cr für ".$value[type]."  von
					  <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym]
					  - angenommen von: 
					  <a href=\"$href&konid=".$players[$value[acceptor_id]][id]."#$value[time]\">".$players[$value[acceptor_id]][syndicate]." (#".$players[$value[acceptor_id]][rid].")</a>
					   - <b>success: $value[success]</b>
					  ";
				  }
				  
				  if ($value[acceptor_id] == $konid) {
					  // Angenommen
					  $output[$value[accepttime]] = "<font color=pink><b>Joblog  angenommen:</b></font>".pointit($value[money])."Cr für ".$value[type]."  von
					  <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> mit $value[number] wiederholungen und anonym: $value[anonym] - gegen Spieler
					  <a href=\"$href&konid=".$players[$value[target_id]][id]."#$value[time]\">".$players[$value[target_id]][syndicate]." (#".$players[$value[target_id]][rid].")</a> - <b>success: $value[success]</b>";
				  }
				}
				
			}
		}
	}	
	
	
	// Spylogs
	if (in_array(spylogs,$todo)) {
		$stuff = assocs("select * from spylogs where (aid = $konid or did=$konid) and time >= ".($time-$ttime));
		$ids = array();
		foreach ($stuff as $vl) {
			$ids[] = $vl['id'];
		}
		if ($ids) {
			$berichte = assocs("select log_id, uncompress(bericht) as bericht from spylogs_berichte where log_id in (".join(",", $ids).")", "log_id");
		}
		$spycount = 0;
		foreach ($stuff as $value) {
			$spycount++;
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[aid] == $konid) {
				$output[$value[time]] = "<a href=javascript:toggleBericht('spybericht$spycount') style=''><font color=\"green\"><b>Spyaction DONE:</b></font></a>".
				$value[action]." gegen <a href=\"$href&konid=".$value[did]."#$value[time]\">".$players[$value[did]][syndicate]." (#".$players[$value[did]][rid].")</a> erfolg:".$value[success]." result:".$value[result]." ausgeführt<div id=spybericht$spycount style='display:none;border:1px solid black'>".$berichte[$value['id']]['bericht']."</div>";
			}
			if ($value[did] == $konid) {
				$output[$value[time]] = "<a href=javascript:toggleBericht('spybericht$spycount') style=''><font color=\"green\"><b>Spyaction ERLITTEN:</b></font></a>"
				.$value[action]." von <a href=\"$href&konid=".$value[aid]."#$value[time]\">".$players[$value[aid]][syndicate]." (#".$players[$value[aid]][rid].")</a> erfolg:".$value[success]." result:".$value[result]."<div id=spybericht$spycount style='display:none;border:1px solid black'>".$berichte[$value['id']]['bericht']."</div>";
			}
		}
	}
	// Lagerlogs
	if (in_array(lagerlogs,$todo)) {
		$stuff = assocs("select * from lagerlogs where user_id = $konid and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[action] == "store") {
				$output[$value[time]] = "<font color=\"blue\"><b>Einlagern:</b></font> ".(pointit($value[number]))." ".$value[product];
			}
			if ($value[action] == "get") {
				$output[$value[time]] = "<font color=\"blue\"><b>Entnehmen:</b></font> ".(pointit($value[number]))." ".$value[product];
			}
		}
	}
	// Attackogs
	if (in_array(attacklogs,$todo)) {
		$stuff = assocs("select * from attacklogs where (aid = $konid or did=$konid) and time >= ".($time-$ttime));
		$what = array(1 => "normal", 2 => "Belagerung", 3 => "Eroberung", 4 => "Spione zerstören");
		$berichtcount = 0;
		foreach ($stuff as $value) {
			$berichtcount++;
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[aid] == $konid) {
				$output[$value[time]] = "<a href=javascript:toggleBericht($berichtcount) style=''><font color=\"black\"><b>ATTACK DONE (".($what[$value['type']])."):</b></font></a>".
				$value[action]." gegen <a href=\"$href&konid=".$value[did]."#$value[time]\">".$players[$value[did]][syndicate]." (#".$players[$value[did]][rid].")</a> result:".$value[landgain]." land gewonnen<div id=$berichtcount style='display:none;border:1px solid black'>".$value['bericht']."</div>";
			}
			if ($value[did] == $konid) {
				$output[$value[time]] = "<a href=javascript:toggleBericht($berichtcount) style=''><font color=\"black\"><b>ATTACK ERLITTEN (".($what[$value['type']])."):</b></font></a>"
				.$value[action]." von <a href=\"$href&konid=".$value[aid]."#$value[time]\">".$players[$value[aid]][syndicate]." (#".$players[$value[aid]][rid].")</a>  result:".$value[landgain]." land verloren<div id=$berichtcount style='display:none;border:1px solid black'>".$value['bericht']."</div>";
			}
		}
	}
	// Buildlogs
	if (in_array(buildlogs,$todo)) {
		$stuff = assocs("select * from build_logs where (user_id = $konid) and time >= ".($time-$ttime));
		$bstats = assocs("select * from buildings","building_id");
		$bstats[127][name] = "land";
		$mstats = assocs("select * from military_unit_settings","unit_id");
		$sstats = assocs("select * from spy_settings","unit_id");
		$scistats = assocs("select *,gamename as name from sciences","id");
		$zuweis = array(
						building => bstats,
						mil => mstats,
						spy => sstats,
						sci => scistats
						);

		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[action] == 0) {
				$done = "gebaut";
			}
			elseif ($value[action] == 1) {
				$done = "entlassen";
			}
			elseif ($value[action] == 2) {
				$done = "abgebrochen";
			}
			$output[$value[time]] = "<font color=yellow>Buildlogs:</font>".pointit($value[number])." ".${$zuweis[$value[what]]}[$value[subject_id]][name]." ".$done;
		}
	}
	//Aktienlogs
	if (in_array(aktienlogs,$todo)) {
		$need = assocs("select * from aktien_logs where need_id = $konid and time >= ".($time-$ttime));
		foreach ($need as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			$output[$value[time]] = "<font color=\"#ff23fa\"><b>Aktien kaufen:</b></font> ".(pointit($value[menge]))." von Syndikat #".$value[rid]." für ".pointit($value[menge] * $value[preis])." Cr (Kurs: ".pointit($value[preis])." Cr) von <a href=\"$href&konid=".$players[$value[offer_id]][id]."#$value[time]\">".$players[$value[offer_id]][syndicate]." (#".$players[$value[offer_id]][rid].")</a>";
		}
		$offer = assocs("select * from aktien_logs where offer_id = $konid and time >= ".($time-$ttime));
		foreach ($offer as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			$output[$value[time]] = "<font color=\"#ff23fa\"><b>Aktien verkaufen:</b></font> ".(pointit($value[menge]))." von Syndikat #".$value[rid]." für ".pointit($value[menge] * $value[preis])." Cr (Kurs: ".pointit($value[preis])." Cr) von <a href=\"$href&konid=".$players[$value[need_id]][id]."#$value[time]\">".$players[$value[need_id]][syndicate]." (#".$players[$value[need_id]][rid].")</a>";
		}
	}
	//Aktienprivatlogs
	/*if (in_array(aktienprivatlogs,$todo)) {
		$stuff = assocs("select * from aktien_privatlogs where (seller_id=$konid or user_id=$konid) and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[seller_id] == $konid) {
					$output[$value[time]] = "<font color=\"#aaff00\"><b>Privataktien Sold: </b></font>".pointit($value[number])." Aktien von Syndikat #".($value[synd_id])." an <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> verkauft";
			}
			if ($value[user_id] == $konid) {
				if ($value[action] == 2) { // back
					$output[$value[time]] = "<font color=\"#aaff00\"><b>Privataktien back: </b></font>".pointit($value[number])." Aktien von Syndikat #".($value[synd_id])." zurückgenommen";
				}
				if ($value[action] == 0) { // buy
					$output[$value[time]] = "<font color=\"#aaff00\"><b>Privataktien Buy: </b></font>".pointit($value[number])." Aktien von Syndikat #".($value[synd_id])." von <a href=\"$href&konid=".$players[$value[seller_id]][id]."#$value[time]\">".$players[$value[seller_id]][syndicate]." (#".$players[$value[seller_id]][rid].")</a> gekauft";
				}
			}
		}
	}*/
	/*
		user_id:4
		receiver_id:300
		product:money
		number:1
		product_request:money
		number_request:1
		time:1295141574
		finished:2
		transferid:14267
	*/
	// Transferlogs
	if (in_array(transferlogs,$todo)) {
		$stuff = assocs("select * from transfer where finished > 1 and (receiver_id=$konid or user_id=$konid) and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[user_id] == $konid) {
				if ($value[finished] == 2) { // reject
					$output[$value[time]] = "<font color=\"#aa00ff\"><b>Transfer verschickt: </b></font>".pointit($value[number])." ".($value[product])." (".$value[price].")"." an <a href=\"$href&konid=".$players[$value[receiver_id]][id]."#$value[time]\">".$players[$value[receiver_id]][syndicate]." (#".$players[$value[receiver_id]][rid].")</a> verschickt gegen 
						".pointit($value[number_request])." ".($value[product_request])." (".$value[request_price].")";
				}
			}
			if ($value[receiver_id] == $konid) {
				if ($value[finished] == 2) { // accept
					$output[$value[time]] = "<font color=\"#aa00ff\"><b>Transfer angenommen: </b></font>".pointit($value[number])." ".($value[product])." (".$value[price].")"." von <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> erhalten  gegen 
						".pointit($value[number_request])." ".($value[product_request])." (".$value[request_price].")";
				}
			}
		}
	}
	// Resetlogs
	if (in_array('reset',$todo)) {
		$stuff = assocs("select * from options_reset where user_id = $konid and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time]]) { // outputtimefreimachen
				$value[time]++;
			}
			if ($value[user_id] == $konid) {
				$color = "#FF7626"; // Dark Orange
				$output[$value[time]] = "<font color=\"$color\"><b>RESET</b></font>";
			}
		}
	}
	// Syndarmee
	if (in_array('syndarmee',$todo)) {
		$stuff = assocs("select * from build_syndarmee where user_id = $konid and time_send >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value[time_send]]) { // outputtimefreimachen
				$value[time_send]++;
			}
			if ($value[user_id] == $konid) {
				$color = "#FFE70D"; // Dark Yellow
				$color2 = "#0D25FF"; // Blue
				$output[$value[time_send]] = "<font color=\"$color\" style='background-color:$color2'><b>SYNARMEE eingezahlt</b></font> in Syndikat #".$value['rid'].": <b>".$value['number']." ".($value['miltype'] == 1 ? "Marines":"Ranger")."</b>";
			}
		}
	}
	// Inaktivenverschiebung des eigenen Konzerns
	if (in_array('synwechsel',$todo)) {
		$stuff = assocs("select * from syndikate_wechsel where konzernid = $konid and time >= ".($time-$ttime));
		foreach ($stuff as $value) {
			while($output[$value['time']]) { // outputtimefreimachen
				$value['time']++;
			}
			if ($value[konzernid] == $konid) {
				$color = "#FF7626"; // Dark Red
				$output[$value['time']] = "<font color=\"$color\"><b>Verschoben</b></font> in Syndikat #".$value['newrid']." von #".$value['oldrid'];
			}
		}
	}

	// AUSGABE
	krsort($output);
	$ausgabe .= "<script language=\"JavaScript\">
	var toggles = new Array();
	function toggleBericht(id) {
		if (typeof toggles[id] == 'undefined') toggles[id] = 0;
		if (toggles[id] == 1) {
			document.getElementById(id).style.display = 'none';
			toggles[id] = 0;
		} else {
			document.getElementById(id).style.display = '';
			toggles[id] = 1;
		}
	}
	
	</script>";
	$ausgabe .= "<table cellpadding=0 cellspacing=0 border=0 align=center>";
	foreach ($output as $k => $vl) {
		$vl = "<a name=\"$k\"></a>".$vl;
		$ausgabe.= "<tr><td width=150 align=left>".mytimes($k)."</td><td width=600 align=left>$vl</td></tr>";
	}
	$ausgabe .= "</table>";

	if ($pl != 3) { // Game-Maser-Supervisor darf keine Statusdaten einsehen können.
	  pvar($thisplayer,currentplayer);
	} else {
	  echo "Normalerweise stünden an dieser Stelle (zusätzlich zur Aktions-History, s.u.) die Details des Konzerns ('status'-Tabelle). Also z.B. die Gebäude, Ressourcen und Einheiten etc. (allerdings keine Forschungen und keine Partner-Boni). Als Game-Master Supervisor siehst du diese Informationen hier allerdings nicht. Stichwort 'Rollenteilung'.<br>Die Aktionshistory an sich ist für dich sichtbar, damit du kontrollieren kannst, ob ein Game-Master seine Macht missbraucht und sich bevorteilt hat.<br><br>";
	}
}

if (!$ia) {
	$self = "index.php";
	$ausgabe.="
		<form action=$self>
		Konzernid angeben!:<br><input type=hidden name=action value=traceuser>
		<input type=hidden name=ia value=trace>
		<input name=konid>
			<br>Zeitraum in Sekunden (0 = alle einträge):<input name=ttime>
			<br><input type=submit >
		</form>
	";
}

function mytimes($time) {

$back = date("d.m.y - H:i:s",$time);
return $back;

}

echo $ausgabe;


?>
