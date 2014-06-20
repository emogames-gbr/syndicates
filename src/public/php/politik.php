<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($action and $action != "vote" and $action != "csn" and $action != "ccn" and $action != "cms" and $action != "cme" and 
	$action != "dw" and $action != "pab" and $action != "ae" and $action != "oc" and $action != "ab" and $action != "pa" and 
	$action != "aa" and $action != "chanm" and $action != "chspw" and $action != "chlsw" and $action != "chsbil" and 
	$action != "peace" and $action != "pkv" and $action != "alnamechange" /*and $action != "gvi" */and 
	$action != "synbeschr" and $action != "synfus"): unset($action); endif;
if ($sac and $sac != "handle") { $sac = ""; }
if ($ia and $ia != "next" and $ia != "finish" and $ia != "del" and $ia != "changestatus"): unset($ia); endif;
$newcurrnamevalid = 0;
$newsyndnamevalid = 0;
$newalnamevalid = 0;
if ($newcurrname and preg_match("/^[a-zA-Zäöü]{2,20}$/", $newcurrname)): $newcurrnamevalid = 1; endif;
if ($newsyndname and preg_match("/^[äöü\w-\. ]{3,50}$/", $newsyndname)): $newsyndnamevalid = 1; $newsyndname = preg_replace("/ {2,}/", " ", trim($newsyndname));endif;
if ($newalname and preg_match("/^[äöü\w-\. ]{3,50}$/", $newalname)): $newalnamevalid = 1; $newalname = preg_replace("/ {2,}/", " ", trim($newalname)); endif;
if ($what and $what != "decline" and $what != "accept" and $what != "takeback" and $what != "cancel"): $what = ""; endif;

$who = floor($who);
$newmaxschulden = floor($newmaxschulden);
$enemyid = floor($enemyid);
$wnum = floor($wnum);
$rid = floor($rid);
$plid = floor($plid);
$abkommen = floor($abkommen);
$allyid = floor($allyid);
$continue = 0;
$activewars = 0;
$activewars_self_declared = 0;

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//

define ('PRAESI_PROZENT_TO_KICK', 0.25);
define ('MAX_NETWORTH_DIFFERENCE', 0.8);
define ('KRIEGSMINDESTDAUER', 36 * 3600);
define ('KRIEGSPROZENTVERLUSTE_FUER_FRIEDEN', 6.0);
define ('ABKOMMEN_ANZAHLBESCHRAENKUNG', 30);
define ('TIME_BETWEEN_TWO_KICKVOTES', 1 * 24 * 3600); # wenn möglich nur ganze Stunden angeben

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");


//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$sname = "";
$scurrname = "";
$pid = 0;
$board_id = 0;
$atwar = 0;
$maxschulden = 0;
$kingarraynummer = 0;
$kingid = 0;
$isking = 0;
$valid = 0;
$newcurrnamevalid = $newcurrnamevalid;
$newsyndnamevalid = $newsyndnamevalid;

$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
$weiter = "<br><br><a href=politik.php class=linkAufsiteBg>weiter</a>";

$legalking = "-";
$synd_action = "";
$enemy = 0;
$difference = 0;
$days = 0;
$hours = 0;
$gate = 0;
$minutes = 0;
$announcement = "";
$enemy = "";
$count = 0;
$activewars = 0;


$barrier = 3;


$safe = array();
$vote = array();
$queries = array();
$names = array();
$kuids = array();
$wardata = array();
$kriegsdaten = array();

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//							selects fahren									//

	// Syndikatsdaten holen
	list ($sname, $scurrname, $pid, $board_id, $atwar, $maxschulden, $image, $allianz_id, $ally1, $ally2, $allianzanfrage, $open) = row("select name, currency, president_id, board_id, atwar, maxschulden, image, allianz_id, ally1, ally2, allianzanfrage, open from syndikate where synd_id=".$status{rid});

	$fusionierungsanfrage = assocs("select first, second, time, done from politik_synfus where (first = ".$status[rid]." or second = ".$status[rid].") and done = 0 order by time asc");

	// Syndikatsmitglieder und deren Wahlen holen
	$safe = assocs("select id, syndicate, rulername, vote, land, createtime, lastlogintime, alive, show_emogames_name from status where rid=".$status{rid}." and alive>0");
	$emogames_usernamen = assocs("select users.username, users.konzernid from users, status where status.show_emogames_name >= 1 and status.id = users.konzernid and status.rid=".$status[rid], "konzernid");

//							Berechnungen									//


	// Anzahl der Votes der einzelnen Spieler bestimmen
	foreach ($safe as $vl) { 		if ($vl[vote] && $vl[alive] == 1 && $vl[lastlogintime] + TIME_TILL_INACTIVE > $time): $vote[$vl[vote]]++; elseif ($vl[alive] == 2 or $vl[lastlogintime] + TIME_TILL_INACTIVE <= $time): $abzug_bei_stimmen_verhaeltnis++; endif;		};
	if ($action == "vote")	{
		foreach ($safe as $vl)	{
			if ($vl[id] == $who):
				if ($who != $status{vote}):
					$queriesend[] = "update status set vote=$who where id=$id";
					s("Sie haben erfolgreich abgestimmt!");
					$barrier = 0;
					$vote[$status{vote}]--;
					$vote{$who}++;
					$status{vote} = $who;
				else: $barrier = 2;
				endif;
			endif;
		}

		if ($barrier == 3): f("Dieser Konzern befindet sich nicht in Ihrem Syndikat und kann nicht gewählt werden!");
		elseif ($barrier ==2): f("Sie haben diesen Konzern bereits gewählt.");
		//elseif ($barrier ==1): f("Sie können sich nicht selbst wählen!");
		endif;
	}


	// Präsident bestimmten anhand von Votings, Land und Createtime
	$num = count($safe);
	for ($i = 0; $i < $num ; $i++)	{
		if ($vote{$kingid} < $vote{$safe[$i][id]}){ $kingid = $safe[$i][id]; $kingarraynummer = $i; }
		elseif ($vote{$kingid} == $vote{$safe[$i][id]}){
			if ($safe[$kingarraynummer][land] < $safe[$i][land]): $kingid = $safe[$i][id]; $kingarraynummer = $i;
			elseif ($safe[$kingarraynummer][land] == $safe[$i][land]):
				if ($safe[$kingarraynummer][createtime] > $safe[$i][createtime]): $kingid = $safe[$i][id]; $kingarraynummer = $i; endif;
			endif;
		}
	}

	// Falls durch Veränderung ein neuer Präsident bestimmt wurde - im Syndikatstable updaten und Rechte im Forum neu verteilen/entziehen

	if ($pid != $kingid and $kingid):
		$queriesend[] = "update syndikate set president_id=$kingid where synd_id=".$status[rid];
		$queriesend[] = "update status set ispresident = 0 where id = $pid";
		$queriesend[] = "update status set ispresident = 1 where id = $kingid";
	endif;
	if ($id == $kingid && $status[ispresident] != 1) {
		$queriesend[] = "update status set ispresident = 1 where id = $id";
	}

	// Ab hier steht fest wer Präsident ist
	if ($nk) {
		select("insert into code_stealer_tracing (time, id, phrase) values ('$time', '$id', 'noking, politik.php')");
	}
	
	if ($kingid == $id && !$nk) { # zum testen kann mit ?nk=1 simuliert werden, dass man KEIN präsident ist
		$isking = 1;
	}
	
	if ($allianzanfrage)	{ //runde51 allys weg ---- Runde 60 allys wieder rein by dragon12
		$allyanfrage_output = array();
		$allianzanfragedaten = assoc("select anfragen_id,1t,2t,3t,1s,2s,3s,time from allianzen_anfragen where 1t=".$status[rid]." or 2t=".$status[rid]." or 3t=".$status[rid]." order by time desc limit 1");
		$first = $allianzanfragedaten['1t']; $firstd = $allianzanfragedaten['1s'];
		$second = $allianzanfragedaten['2t']; $secondd = $allianzanfragedaten['2s'];
		if ($allianzanfragedaten['3t']): $third = $allianzanfragedaten['3t']; $thirdd = $allianzanfragedaten['3s']; endif;
		$synd_action = "synd_id in ($first,$second".($third ? ",$third)":")");
		$names_allianz_verhandlungspartner = assocs("select synd_id, name from syndikate where $synd_action", "synd_id");

		if ($second == $ally1 or $third == $ally1): $trenner = ";<br>Ihr Partner: "; else: $trenner = ",<br>"; endif;
		// ERSTER VERHANDLUNGSPARTNER
		if ($status[rid] == $first): $erst = $second; $erstd = $secondd; $zweit = $third; $zweitd = $thirdd; $ownd = $firstd;
		elseif ($status[rid] == $second): $erst = $first; $erstd = $firstd; $zweit = $third; $zweitd = $thirdd; $ownd = $secondd;
		elseif ($status[rid] == $third): $erst = $first; $erstd = $firstd; $zweit = $second; $zweitd = $secondd; $ownd = $thirdd;
		endif;
		
		$allyanfrage_output['partner1'] = array('name' => $names_allianz_verhandlungspartner{$erst}{name}, 
			'synd_id' => $erst, 'ersteller' => $erstd);
		if ($zweit) {
			$allyanfrage_output['partner2'] = array('name' => $names_allianz_verhandlungspartner{$zweit}{name}, 
				'synd_id' => $zweit, 'ersteller' => $zweitd);	
		}
		
		$difference = $time - $allianzanfragedaten[time];
		$allyanfrage_output['days'] = floor ( $difference / (24 * 60 * 60) );
		$allyanfrage_output['hours'] = floor ( ($difference - $days * 24 * 60 * 60) / (60 * 60) );
		$allyanfrage_output['minutes'] = floor ( ($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60 );
		if ($isking) {
			$allyanfrage_output['accept'] = !$ownd;
		}
		
		$tpl->assign('ALLY_ANFRAGE', $allyanfrage_output);
	}
	if ($fusionierungsanfrage && $globals[roundendtime] > $time + 14 * 24 * 3600)	{
		foreach ($fusionierungsanfrage as $ky => $vl) {
			if ($vl[first] != $status[rid]) {
				$other_syn_ids[$vl[first]] = $vl[first];
			}
			else { $other_syn_ids[$vl[second]] = $vl[second]; }
		}
		$other_syn_ids[$status[rid]] = $status[rid];
		$synnames = assocs("select synd_id, name from syndikate where synd_id in (".join(",", $other_syn_ids).")", "synd_id");
		$fusionierungsanfrage_output = array();
		foreach ($fusionierungsanfrage as $ky => $vl) {
			$specific = $vl[first] == $status[rid] ? "second": "first";
			$vl['synName'] = $synnames[$vl[$specific]][name];
			$vl['synd_id'] = $vl[$specific];
			$difference = $time - $vl[time];
			$vl['days'] = floor ( $difference / (24 * 60 * 60) );
			$vl['hours'] = floor ( ($difference - $days * 24 * 60 * 60) / (60 * 60) );
			$vl['minutes'] = floor ( ($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60 );
			if ($isking) {
				$vl['isSteller'] = ($specific == "first");
			}
			
			array_push($fusionierungsanfrage_output, $vl);
		}
		$tpl->assign('FUSION', $fusionierungsanfrage_output);
	}
	

	if ($allianz_id)	{//runde51 allys weg ---- Runde 60 allys wieder rein by dragon12
		$ally_output = array();
		$allies = row("select first,second,third,name from allianzen where allianz_id=$allianz_id");
		$ally_output['count_member'] = 2 + ($vl[2] ? 1 : 0);
		if ($status[rid] == $allies[0]): $showally1 = $allies[1]; $showally2 = $allies[2];
		elseif ($status[rid] == $allies[1]): $showally1 = $allies[0]; $showally2 = $allies[2];
		elseif ($status[rid] == $allies[2]): $showally1 = $allies[0]; $showally2 = $allies[1];
		endif;
		$names_allianz_partner = assocs("select synd_id, name from syndikate where synd_id in ($showally1".($showally2 ? ",$showally2)":")"), "synd_id");
		$gekuendigt_qm = assocs("select synd_id from allianzen_kuendigungen where synd_id in (".$status[rid].",$showally1".($showally2 ? ",$showally2)":")"),"synd_id");
		$status1 = "alive";
		$status2 = "alive";
		if ($gekuendigt_qm[$showally1] or $gekuendigt_qm[$status[rid]]): $status1 = "gekuendigt"; endif;
		if ($gekuendigt_qm[$showally2] or $gekuendigt_qm[$status[rid]]): $status2 = "gekuendigt"; endif;
		
		
		$ally_output['member1'] = array('name' => $names_allianz_partner[$showally1]{name}, 'synd_id' => $showally1, 'status' => $status1);
		$ally_output['member2'] = array('name' => $names_allianz_partner[$showally2]{name}, 'synd_id' => $showally2, 'status' => $status2);
		$tpl->assign('ALLY', $ally_output);
	}

	if ($atwar)	{
		$synd_action = "";
		$wardata = assocs("select war_id, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3, first_1_llt, first_2_llt, first_3_llt, second_1_llt, second_2_llt, second_3_llt, first_1_lwt, first_2_lwt, first_3_lwt, second_1_lwt, second_2_lwt, second_3_lwt, first_1_landstart, first_2_landstart, first_3_landstart, second_1_landstart, second_2_landstart, second_3_landstart, starttime, ended_by, status, artefakt_want_first_1, artefakt_want_first_2, artefakt_want_first_3, artefakt_want_second_1, artefakt_want_second_2, artefakt_want_second_3 from ". WARTABLE ." where (first_synd_1=".$status{rid}." or first_synd_2=".$status{rid}." or first_synd_3=".$status{rid}." or second_synd_1=".$status{rid}." or second_synd_2=".$status{rid}." or second_synd_3=".$status{rid}.")");
		foreach ($wardata as $vl)	{
			$enemy2 = 0; $enemy3 = 0; $llt2 = 0; $llt3 = 0; $llt_own2 = 0; $llt_own3 = 0; $own2 = 0; $own3 = 0;
			if ($vl[status]):
				$activewars++;
				$synd_action .= $vl[second_synd_1].",".$vl[first_synd_1].",";
				if ($vl[second_synd_2]):
					$synd_action .= $vl[second_synd_2].",";
				endif;
				if ($vl[second_synd_3]):
					$synd_action .= $vl[second_synd_3].",";
				endif;
				if ($vl[first_synd_2]):
					$synd_action .= $vl[first_synd_2].",";
				endif;
				if ($vl[first_synd_3]):
					$synd_action .= $vl[first_synd_3].",";
				endif;
				if ($vl[first_synd_1] == $status[rid] or $vl[first_synd_2] == $status[rid] or $vl[first_synd_3] == $status[rid]):
					$activewars_self_declared++;
					$kriegsdaten[$vl[war_id]] = array("gegner1" => $vl[second_synd_1], "gegner2" => $vl[second_synd_2], "gegner3" => $vl[second_synd_3], "own1" => $vl[first_synd_1], "own2" => $vl[first_synd_2], "own3" => $vl[first_synd_3], "llt_own1" => $vl[first_1_llt], "llt_own2" => $vl[first_2_llt], "llt_own3" => $vl[first_3_llt], "llt_enemy1" => $vl[second_1_llt], "llt_enemy2" => $vl[second_2_llt], "llt_enemy3" => $vl[second_3_llt], "lwt_own1" => $vl[first_1_lwt], "lwt_own2" => $vl[first_2_lwt], "lwt_own3" => $vl[first_3_lwt], "lwt_enemy1" => $vl[second_1_lwt], "lwt_enemy2" => $vl[second_2_lwt], "lwt_enemy3" => $vl[second_3_lwt], "starttime" => $vl[starttime], "status" => $vl[status], "ending_by" => $vl[ended_by], "lst_own1" => $vl[first_1_landstart], "lst_own2" => $vl[first_2_landstart], "lst_own3" => $vl[first_3_landstart], "lst_enemy1" => $vl[second_1_landstart], "lst_enemy2" => $vl[second_2_landstart], "lst_enemy3" => $vl[second_3_landstart], "artefakt_want_1" => $vl[artefakt_want_first_1], "artefakt_want_2" => $vl[artefakt_want_first_2], "artefakt_want_3" => $vl[artefakt_want_first_3], "artefakt_want_enemy_1" => $vl[artefakt_want_second_1], "artefakt_want_enemy_2" => $vl[artefakt_want_second_2], "artefakt_want_enemy_3" => $vl[artefakt_want_second_3], "party_self" => "first");
				else:
					$kriegsdaten[$vl[war_id]] = array("gegner1" => $vl[first_synd_1], "gegner2" => $vl[first_synd_2], "gegner3" => $vl[first_synd_3], "own1" => $vl[second_synd_1], "own2" => $vl[second_synd_2], "own3" => $vl[second_synd_3], "llt_own1" => $vl[second_1_llt], "llt_own2" => $vl[second_2_llt], "llt_own3" => $vl[second_3_llt], "llt_enemy1" => $vl[first_1_llt], "llt_enemy2" => $vl[first_2_llt], "llt_enemy3" => $vl[first_3_llt], "lwt_own1" => $vl[second_1_lwt], "lwt_own2" => $vl[second_2_lwt], "lwt_own3" => $vl[second_3_lwt], "lwt_enemy1" => $vl[first_1_lwt], "lwt_enemy2" => $vl[first_2_lwt], "lwt_enemy3" => $vl[first_3_lwt], "starttime" => $vl[starttime], "status" => $vl[status], "ending_by" => $vl[ended_by], "lst_own1" => $vl[second_1_landstart], "lst_own2" => $vl[second_2_landstart], "lst_own3" => $vl[second_3_landstart], "lst_enemy1" => $vl[first_1_landstart], "lst_enemy2" => $vl[first_2_landstart], "lst_enemy3" => $vl[first_3_landstart], "artefakt_want_1" => $vl[artefakt_want_second_1], "artefakt_want_2" => $vl[artefakt_want_second_2], "artefakt_want_3" => $vl[artefakt_want_second_3], "artefakt_want_enemy_1" => $vl[artefakt_want_first_1], "artefakt_want_enemy_2" => $vl[artefakt_want_first_2], "artefakt_want_enemy_3" => $vl[artefakt_want_first_3], "party_self" => "second");
				endif;
			endif;
		}
		

		$synd_action = chopp($synd_action);
		$names = assocs("select synd_id, artefakt_id, name from syndikate where synd_id in ($synd_action)", "synd_id");

		// Hier noch Sort einbauen wenn die Ausgabe erstmal funktioniert
		$kriegsdaten_output = array();
		foreach ($kriegsdaten as $ky => $vl)	{
			$vl['warID'] = $ky;
			$own = array(); $enemy2 = ""; $enemy3 = ""; $negative = 0;
			$difference = 48*60*60 - ($time - $vl[starttime]);
			if ($difference >48*60*60): $difference -= 48*60*60; $negative = 1; endif;
			$days = floor ( $difference / (24 * 60 * 60) );
			$hours = floor ( ($difference - $days * 24 * 60 * 60) / (60 * 60) );
			$minutes = ceil ( ($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60 );
			if ($minutes == 60) $minutes = 59;
			if (false && $isking) $vl['isPossibleToEnd'] = true;
			
			
			$landwon_own = 0;
			$landwon_enemy = 0;
			$landstart_own = 0;
			$landstart_enemy = 0;
			for ($i = 1; $i <= 3; $i++) {
				$landwon_own += $vl['lwt_own'.$i];
				$landwon_enemy += $vl['lwt_enemy'.$i];
				$landstart_own += $vl['lst_own'.$i];
				$landstart_enemy += $vl['lst_enemy'.$i];
			}
			//$landwon_difference = $landwon_own - $landwon_enemy;
			$kriegsbalken_colwidth = 200;
			$vl['kriegsbalken_colwidth'] = $kriegsbalken_colwidth;
			$vl['kriegsbalken_colwidth2'] = $kriegsbalken_colwidth*2;

			$praemie_own = calcWarMoney($status[rid], $ky, 'total', 0)*4;
			$warDetails = warCheckAndHandle($ky);
			
			foreach($warDetails['declarer'] as $syni)
				if($syni==$status[rid]) $itsMeMario=1;
				
				//echo "itsmemario ".$itsMeMario;
			$landwon_enemy = $itsMeMario ? $warDetails['defenderBrutto'] : $warDetails['declarerBrutto'];
			$landstart_own = $itsMeMario ? $warDetails['declarerStartLand'] : $warDetails['defenderStartLand'];
			$landwon_own = $itsMeMario ? $warDetails['declarerBrutto'] : $warDetails['defenderBrutto'];
			$landstart_enemy = $itsMeMario ? $warDetails['defenderStartLand'] : $warDetails['declarerStartLand'];
			
			$landwon_difference = $landwon_own - $landwon_enemy;
		
			//pvar($warDetails );
			//KRIEGSPRAEMIE_TAGFAKTOR * (get_day_time($time) - get_day_time($globals[roundstarttime]) + 24 * 3600) / 24 / 3600 * $landwon_own;
			$praemie_enemy = calcWarMoney($vl{gegner1}, $ky, 'total', 0)*4;
			//KRIEGSPRAEMIE_TAGFAKTOR * (get_day_time($time) - get_day_time($globals[roundstarttime]) + 24 * 3600) / 24 / 3600 * $landwon_enemy;
			$ownprozent = "";
			$enemyprozent = "";
			$brutto_own = $landwon_own/$landstart_enemy;
			$brutto_enemy = $landwon_enemy/$landstart_own;
			$prozent = $brutto_enemy - $brutto_own;//$itsMeMario ? $warDetails['declarerNettoRatio'] : $warDetails['defenderNettoRatio'];
			//$landwon_enemy/$landstart_own - $landwon_own/$landstart_enemy;

			if ($prozent <= 0) {
				$prozent *= (-1);
				// $prozent = $landwon_difference / $landstart_enemy;  ##geändert Runde 36
				$ownwidth = round($prozent / 0.10 * $kriegsbalken_colwidth);
				$ownprozent = (round($prozent * 1000)/10)." % netto <b>erobert</b>!";
				if ($ownwidth > $kriegsbalken_colwidth) $ownwidth = $kriegsbalken_colwidth;
				$enemywidth = 0;
			}
			else {
				//$prozent = $landwon_difference / $landstart_own;      ##geändert Runde 36
				$enemywidth = round($prozent / 0.10 * $kriegsbalken_colwidth);
				$enemyprozent = (round($prozent * 1000)/10)." % netto <b>verloren</b>!";
				if ($enemywidth > $kriegsbalken_colwidth) $enemywidth = $kriegsbalken_colwidth;
				$ownwidth = 0;
			}
			$ownwidth_brutto = round(2*$kriegsbalken_colwidth*$brutto_own/($itsMeMario?0.2:0.16));
			$enemywidth_brutto = round(2*$kriegsbalken_colwidth*$brutto_enemy/($itsMeMario?0.16:0.2));
			$ownprozent_brutto = (round($brutto_own * 1000)/10)." %";
			$enemyprozent_brutto = (round($brutto_enemy * 1000)/10)." %!";
			
			$vl['ownwidth'] = $ownwidth;
			$vl['ownwidth_brutto'] = $ownwidth_brutto;
			$vl['enemywidth'] = $enemywidth;
			$vl['enemywidth_brutto'] = $enemywidth_brutto;
			$vl['ownprozent'] = $ownprozent;
			$vl['ownprozent_brutto'] = $ownprozent_brutto;
			$vl['enemyprozent'] = $enemyprozent;
			$vl['enemyprozent_brutto'] = $enemyprozent_brutto;

			/* // Änderung der Kriegsgewinnregeln Runde 27
			$ownwidth = round($landwon_own / $landstart_enemy / 0.20 * $kriegsbalken_colwidth);
			$ownprozent = (round($landwon_own / $landstart_enemy * 1000)/10);
			if ($ownwidth > $kriegsbalken_colwidth) $ownwidth = $kriegsbalken_colwidth;

			$enemywidth = round($landwon_enemy / $landstart_own / 0.20 * $kriegsbalken_colwidth);
			$enemyprozent = (round($landwon_enemy / $landstart_own * 1000)/10);
			if ($enemywidth > $kriegsbalken_colwidth) $enemywidth = $kriegsbalken_colwidth;
			*/
			

			$vl['monu'] = false;
			for ($i = 1; $i <= 3; $i++) {
				if ($names[$vl['gegner'.$i]][artefakt_id]) { $vl['monu'] = true; break; }
			}
			
			$allied_output = array();
			$allied_output[] = array('name' => $names{$vl{own1}}{name}, 'synd_id' => $vl{own1},
				'artefakt' => $artefakte[$vl['artefakt_want_1']]['name'], 'noMonu' => !$game_syndikat['artefakt_id']);
			if ($vl['own2']) {
				$allied_output[] = array('name' => $names{$vl{own2}}{name}, 'synd_id' => $vl{own2},
					'artefakt' => $artefakte[$vl['artefakt_want_2']]['name'], 'noMonu' => !$game_syndikat['artefakt_id']);
			}
			if ($vl['own3']) {
				$allied_output[] = array('name' => $names{$vl{own3}}{name}, 'synd_id' => $vl{own3},
					'artefakt' => $artefakte[$vl['artefakt_want_3']]['name'], 'noMonu' => !$game_syndikat['artefakt_id']);
			}
			$vl['verbuendete'] = $allied_output;
			
			$enemy_output = array();
			$enemy_output[] = array('name' => $names{$vl{gegner1}}{name}, 'synd_id' => $vl{gegner1});
			if ($vl[gegner2]) {
				$enemy_output[] = array('name' => $names{$vl{gegner2}}{name}, 'synd_id' => $vl{gegner2});
			}
			if ($vl[gegner3]) {
				$enemy_output[] = array('name' => $names{$vl{gegner3}}{name}, 'synd_id' => $vl{gegner3});
			}
			$vl['enemy'] = $enemy_output;
			
			$vl['landstart_own'] = pointit($landstart_own);
			$vl['landstart_enemy'] = pointit($landstart_enemy);
			$vl['landwon_own'] = pointit($landwon_own);
			$vl['landwon_enemy'] = pointit($landwon_enemy);
			
			/* // Änderung der Kriegsgewinnregeln Runde 27
			<tr class=tableInner1>
				<td>Land zu Kriegsbeginn: ".pointit($landstart_own)." ha</td><td></td><td>Gegner: ".pointit($landstart_enemy)." ha</td>
			</tr>
			<tr class=tableInner1>
				<td>Land erobert: ".pointit($landwon_own)." ha (".$ownprozent."%)</td><td></td><td>Land verloren: ".pointit($landwon_enemy)." ha (".$enemyprozent."%)</td>
			</tr>
			*/
			
			$vl['isAtter'] = $itsMeMario;
			
			
			/* // Änderung der Kriegsgewinnregeln Runde 27
			$lines_diplomatie .= "
				<tr>
				<td colspan=3>
					<table cellpadding=0 cellspacing=0 class=siteGround align=center>
						<tr>
				<td width=50 align=right>Niederlage</td>
				<td align=center width=".(2*$kriegsbalken_colwidth).">
					<table width=".(2*$kriegsbalken_colwidth)." cellpadding=0 cellspacing=2 class=tableOutline border=0 align=center>
						<tr>
							<td class=tableInner1 align=right width=$kriegsbalken_colwidth><img src=".$ripf."dotpixel.gif width=$enemywidth height=15 border=0>
							</td>
							<td class=tableInner1 align=left width=$kriegsbalken_colwidth><img src=".$ripf."dotpixel_blau.gif width=$ownwidth height=15 border=0>
							</td>
						</tr>
					</table>
				</td>
				<td width=50 align=left>Sieg</td>
						</tr>
					</table>
				</td>
			</tr>";
			*/
			
			$vl['praemie_own'] = pointit($praemie_own);
			$vl['praemie_enemy'] = pointit($praemie_enemy);
			
			$vl['isBefore'] = $negative;
			$vl['time_days'] = $days;
			$vl['time_hours'] = $hours;
			$vl['time_minutes'] = $minutes;
			
			array_push($kriegsdaten_output, $vl);
		}
		$tpl->assign('ATWAR', $kriegsdaten_output);
	}
	
	
	/* NASPS sind derzeit deaktiviert, deshalb noch nicht hier auf Template übertragen - inok1989 R63
	$napdata = assocs("select nappartner, type, napid from naps_spieler where user_id=$id order by napid desc", "napid");
	if ($napdata)	{
		foreach ($napdata as $vl)	{
			$spielerids[] = $vl[nappartner];
			$napids[] = $vl[napid];
			//<td>Privates Abkommen mit</td><td>Kündigungsfrist</td><td>Kündigungsstrafe</td><td>Art</td><td>Status</td><td>Optionen</td>
		}
		$naps_sd = assocs("select id, syndicate, rid, lastlogintime, alive from status where id in (".join(",",$spielerids).")", "id");
		$naps_spieler_online = assocs("select user_id from sessionids_actual where gueltig_bis > $time and user_id in (".join(",", $spielerids).")", "user_id");
		$naps_sk = assocs("select napid,initiator,partner,type,kstrafe,kfrist,gekuendigt_time from naps_spieler_spezifikation where napid in (".join(",",$napids).")","napid");
		foreach ($napdata as $vl)	{
			//<td>Privates Abkommen mit</td><td>Kündigungsfrist</td><td>Kündigungsstrafe</td><td>Art</td><td>Status</td><td>Optionen</td>
			if ($naps_spieler_online[$vl[nappartner]]) {
				$online = " <img src=\"".$ripf."_online.gif\" border=0 align=\"absmiddle\">";
			} else {
				$online = " <img src=\"".$ripf."_offline.gif\" border=0 align=\"absmiddle\">";
			}
			if ($naps_spieler_online[$vl[nappartner]][alive] == 2) { 1; }
			else {
				if ($naps_sd[$vl[nappartner]][lastlogintime] + TIME_TILL_GLOBAL_INACTIVE < $time): 	$online = " <img src=\"".$ripf."_gl_inaktiv.gif\" border=0 align=\"absmiddle\">";
				elseif ($naps_sd[$vl[nappartner]][lastlogintime] + TIME_TILL_INACTIVE < $time): 	$online = " <img src=\"".$ripf."_lokal_inaktiv.gif\" border=0 align=\"absmiddle\">";
				endif;
			}
			if ($naps_sk[$vl[napid]][type] == 1): $napart = "NAP";
			elseif ($naps_sk[$vl[napid]][type] == 2): $napart = "NSP";
			elseif ($naps_sk[$vl[napid]][type] == 3): $napart = "NASP"; endif;

			if ($vl[type] == 0): 	$napstatus = "<font class=highlightAuftableInner>inaktiv</font>";
									if ($naps_sk[$vl[napid]][initiator] == $id): $napoptionen = "<a href=politik.php?action=pab&what=takeback&abkommen=".$vl[napid]." class=linkAuftableInner>zurücknehmen</a>";
									else: $napoptionen = "<a href=politik.php?action=pab&what=accept&abkommen=".$vl[napid]." class=linkAuftableInner>annehmen</a> / <a href=politik.php?action=pab&what=decline&abkommen=".$vl[napid]." class=linkAuftableInner>ablehnen</a>"; endif;
			elseif ($vl[type]): 	$napstatus = "<font class=gruenAuftableInner>aktiv</font>";
										$napoptionen = "<a href=politik.php?action=pab&what=cancel&abkommen=".$vl[napid]." class=linkAuftableInner>kündigen</a>";
			endif;
			if ($naps_sk[$vl[napid]][gekuendigt_time]): $napstatus = "<font class=achtungAuftableInner>+".(($naps_sk[$vl[napid]][gekuendigt_time]-get_hour_time($time)) / 3600)."h</font>"; $napoptionen = ""; endif;

			$naps .= "<tr class=tableInner1><td>$online ".$naps_sd[$vl[nappartner]][syndicate]." (#".$naps_sd[$vl[nappartner]][rid].")</td><td align=center><a href=mitteilungen.php?action=psm&rec=$vl[nappartner]><img align=absmiddle src=\"".$ripf."_syn_message_letter.gif\" border=0 alt=\"{".$naps_sd[$vl[nappartner]][syndicate]." (#".$naps_sd[$vl[nappartner]][rid].")} eine Nachricht senden\"></a></td><td align=center>".$naps_sk[$vl[napid]][kfrist]."h</td><td align=right>".pointit($naps_sk[$vl[napid]][kstrafe])." Cr</td><td align=center>$napart</td><td align=center>$napstatus</td><td align=right>$napoptionen</td></tr>";
		}
	} */


	// Andere Sachen/Aktionen (ACTIONS)
	
	$tpl->assign('MAXSCHULDEN', pointit($maxschulden));
	$tpl->assign('SCURRNAME', $scurrname);
	$tpl->assign('SNAME', $sname);

	if ($kingid): $tpl->assign('LEGALKING', $safe[$kingarraynummer][rulername] . " <font class=siteGround>von</font> ".$safe[$kingarraynummer]{syndicate}); endif;

	if ($action == "ccn")	{
		if ($isking)	{
			if (!$ia)	{ 
				$tpl->assign('SHOW_SCURRNAME_EDIT', true);
				unset($action);
			}
			elseif ($ia == "next")	{
				if ($newcurrnamevalid)	{
					$queries[] = "update syndikate set currency='$newcurrname' where synd_id=".$status{rid};
					$tpl->assign('SCURRNAME', $newcurrname);
					// Town-Crier CNN AKTUELLES TOWNCRIER Insert - Durch neue Spezifikation ersetzen
					$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Der Präsident hat den Namen der internen Währung von \"<i>$scurrname</i>\" auf \"<i>$newcurrname</i>\" geändert!',2);";
					s("Sie haben den Namen der Währung soeben erfolgreich von \"<i>$scurrname</i>\" auf \"<i>$newcurrname</i>\" geändert.<br>Das Syndikat wird über diesen Vorgang in \"Aktuelles\" informiert!");
				}
				else { f("Der Währungsname muss mindestens zwei und darf höchstens 20 Zeichen lang sein. Es sind nur Buchstaben aus dem Alphabet (a-Z) erlaubt.");}
			}
		}
		else { f("Sie sind nicht der Präsident. Sie können den Namen der Währung nicht ändern!");}
	}
	elseif ($action == "csn")	{
		if ($isking)	{
			if (!$ia)	{
				$tpl->assign('SHOW_SYNNAME_EDIT', true);
				unset($action);
			}
			elseif ($ia == "next")	{
				if ($newsyndnamevalid)	{
					$queries[] = "update syndikate set name='$newsyndname' where synd_id=".$status{rid};
					$tpl->assign('SNAME', $newsyndname);
					// Town-Crier CNN AKTUELLES TOWNCRIER Insert - Durch neue Spezifikation ersetzen
					$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Der Präsident hat den Namen des Syndikats von \"<i>$sname</i>\" auf \"<i>$newsyndname</i>\" abgeändert!',2);";
					#$queries[] = "insert into message_values (id, user_id, time, werte) values ('1', '$id', '$time', '$sname|$newsyndname')";
					s("Sie haben das Syndikat soeben erfolgreich von \"<i>$sname</i>\" in \"<i>$newsyndname</i>\" umbenannt.<br>Das Syndikat wird über diesen Vorgang in \"Aktuelles\" informiert!");
				}
				else { f("Der Syndikatsname muss mindestens 3 und darf höchstens 50 Zeichen lang sein. Es sind nur Buchstaben des Alphabets, Leerzeichen, sowie die Zeichen \"_\", \"-\" und \".\" erlaubt.");}
			}
		}
		else { f("Sie sind nicht der Präsident. Sie können den Namen des Syndikats nicht ändern!");}
	}
	elseif ($action == "cms")	{
		if ($isking)	{
			if (!$ia)	{ 
				$tpl->assign('SHOW_MAXSCHULDEN_EDIT', true);
				$tpl->assign('MAXSCHULDEN_EDIT', $newmaxschulden);
				unset($action);
			}
			elseif ($ia == "next")	{
				$weeks_played = ceil((round_days_played()+1)/ 7);
				if ($newmaxschulden <= MAX_SCHULDENSATZ_PRO_WOCHE * $weeks_played and $newmaxschulden >= 100)	{
					$queries[] = "update syndikate set maxschulden='$newmaxschulden' where synd_id=".$status{rid};
					$tpl->assign('MAXSCHULDEN', pointit($newmaxschulden));
					// Town-Crier CNN AKTUELLES TOWNCRIER Insert - Durch neue Spezifikation ersetzen
					$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Der Präsident hat den zulässigen Wert für die Maximalverschuldung pro Land von \"<i>$maxschulden</i>\" auf \"<i>$newmaxschulden</i>\" abgeändert!',2);";
					s("Sie haben die Maximalverschuldung soeben erfolgreich von \"<i>$maxschulden</i>\" in \"<i>$newmaxschulden</i>\" geändert.<br>Das Syndikat wird über diesen Vorgang in \"Aktuelles\" informiert!");
				}
				else { f("Bei der Angabe der maximal erlaubten Schulden dürfen nur Zahlen verwendet werden. Es ist eine Zahl im Bereich von 100 - ".($weeks_played * 500)." anzugeben! (der Maximalwert erhöht sich jede Woche um 500)");}
			}
		}
		else { f("Sie sind nicht der Präsident. Sie können die Maximalverschuldung nicht ändern!");}
	}

//							Ausgabe     									//

	$vote_output = array();
	foreach ($safe as $vl)	{
		unset ($president);
		$vl['isKing'] = ($vl[id] == $kingid);
		if (!$vote[$vl[id]]): $vote[$vl[id]] = 0; endif;
		
		$vl['showEmoname'] = ($vl[show_emogames_name] && $globals['roundstarttime'] + 5 * 60 < $time);
		$vl['emoname'] = $emogames_usernamen[$vl[id]][username];
		$vl['stimmen'] = $vote{$vl[id]};
		$vl['prozent'] = (round(($vote[$vl[id]] / (count($safe)-$abzug_bei_stimmen_verhaeltnis)*100)*10)/10);
		
		array_push($vote_output, $vl);
	}
	$tpl->assign('VOTE', $vote_output);



	//
	//// Monument Eroberung ändern
	//

	if ($action == "cme") { // Change Monument Eroberung
		unset($ausgabe);
		$wid = floor($wid);
		if ($isking && $kriegsdaten[$wid] && !$game_syndikat['artefakt_id']) {
			// Zur Verfügung stehende Monumente bestimmen
			for ($i = 1; $i <= 3; $i++) {
				$available_monuments[$names[$kriegsdaten[$wid]['gegner'.$i]][artefakt_id]] = $names[$kriegsdaten[$wid]['gegner'.$i]][artefakt_id];
				if ($kriegsdaten[$wid]['own'.$i] == $status['rid']) $position_identifier = $i;
			}
			if (!$ia) { 
				$tpl->assign('WID', $wid);
				$tpl->assign('CURRENT_CHOICE', $kriegsdaten[$wid]['artefakt_want_'.$position_identifier]);
				$available_monuments_output = array();
				foreach ($available_monuments as $vl) {
					$temp = array();
					$temp['ID'] = $vl;
					$temp['name'] = $artefakte[$vl]['name'];
					if ($vl) {
						array_push($available_monuments_output, $temp);
					}
				}
				$tpl->assign('AVAILABLE_MONUMENTS', $available_monuments_output);
				$showPolitik = true;
			}
			elseif ($ia == "finish") {
				$artefakt_id = floor($artefakt_id);
				if ($available_monuments[$artefakt_id] or $artefakt_id == 0) {
					$queries[] = "update wars set artefakt_want_".$kriegsdaten[$wid]['party_self']."_$position_identifier = $artefakt_id where war_id = $wid";
					s("Änderung erfolgreich übernommen.$weiter");
					if ($artefakt_id) towncrier($status['rid'], "Der Präsident stellt ein, dass im Siegesfall das Monument ".$artefakte[$artefakt_id]['name']." erobert wird.",0,2);
					else towncrier($status['rid'], "Der Präsident stellt ein, dass im Siegesfall kein Monument erobert wird.",0,2);
				} else { f("Es ist ein Fehler aufgetreten. Abbruch."); }
			}
		} else { f("Sie haben nicht die nötigen Rechte, um diese Aktion auszuführen."); }
	}

	//
	//// Allianzname ändern
	//

	if ($action == "alnamechange" && $isking && $allianz_id)	{
		if (!$ia)	{ 
			$tpl->assign('ALNAME', $alname);
			$showPolitik = true;
		}
		elseif ($ia == "next")	{
			if ($newalnamevalid)	{
				$queries[] = "update allianzen set name='$newalname' where allianz_id=".$allianz_id;
				$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Der Präsident hat den Namen der Allianz von \"<i>$alname</i>\" auf \"<i>$newalname</i>\" abgeändert!',2);";
				if ($ally1): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$ally1."', 'Ihr Allianzpartner \"<i>$sname</i> (#".$status[rid].")\" hat den Namen der Allianz von \"<i>$alname</i>\" auf \"<i>$newalname</i>\" abgeändert!',2);"; endif;
				if ($ally2): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$ally2."', 'Ihr Allianzpartner \"<i>$sname</i> (#".$status[rid].")\" hat den Namen der Allianz von \"<i>$alname</i>\" auf \"<i>$newalname</i>\" abgeändert!',2);"; endif;
				s("Sie haben den Namen der Allianz soeben erfolgreich von \"<i>$alname</i>\" in \"<i>$newalname</i>\" umbenannt.<br>Die Allianzsyndikate werden über diesen Vorgang in deren jeweiligem \"Aktuellen\" informiert!");
			}
			else { f("Der Allianzname muss mindestens drei und darf höchstens 50 Zeichen lang sein. Es sind nur Buchstaben aus dem Alphabet (a-Zäöü), sowie der Gedankenstrich, der Punkt und das Leerzeichen erlaubt.");}
		}
		else { f("Sie sind nicht der Präsident. Sie können den Namen der Währung nicht ändern!");}
	}

	//
	//// Syndikatsankündigung ändern - sämtliche bisherige Ausgabe löschen
	//

	if ($action == "chanm" and $isking)	{
		unset ($ausgabe);

		$announcement = row("select announcement from syndikate where synd_id=".$status[rid]);	$announcement = $announcement[0];
		if ($iac == "next" or $iac == "finish") {
			$length = strlen($newannouncement);
			$tpl->assign('IAC', $iac);
			if ($length >= 0 and $length <= 100000) {
				$newannouncement = htmlentities(trim($newannouncement), ENT_QUOTES);
				if ($iac == "next") {
					$tpl->assign('NEWANNOUNCEMENT_BBC', umwandeln_bbcode($newannouncement));
					$tpl->assign('NEWANNOUNCEMENT', $newannouncement);
					$showPolitik = true;
					$iac = "";
				}
				elseif ($iac == "finish") {
					$ausgabe = "";
					$queriesend[] = "update syndikate set announcement='".addslashes($newannouncement)."', announcement_lastchangetime=$time where synd_id=".$status{rid};
					$queriesend[] = "update status set new_synannouncement = 1 where rid = ".$status[rid];
					s("Sie haben die Syndikatsankündigung erfolgreich geändert.$weiter");
				}
			}
			else {	$iac = ""; $announcement = $newannouncement;
					if ($length >= 100000): f("Die Syndikatsankündigung darf maximal 100000 Zeichen lang sein! Du hast jedoch $length Zeichen eingegeben. Bitte kürze die Ankündigung entsprechend.");
					elseif ($length < 0): f("Die Syndikatsankündigung muss mindestens 0 Zeichen lang sein!"); endif;
			}
		}
		if (!$iac) {
			$tpl->assign('NEWANNOUNCEMENT', $newannouncement);
			$tpl->assign('ANNOUNCEMENT', $announcement);
			$showPolitik = true;
		}
	}

	//
	//// SyndikatsBeschreibung ändern - sämtliche bisherige Ausgabe löschen
	//

	if ($action == "synbeschr" and $isking)	{
		$announcement = row("select description from syndikate where synd_id=".$status[rid]);	$announcement = $announcement[0];
		if ($iac == "next" or $iac == "finish") {
			$length = strlen($newannouncement);
			$tpl->assign('IAC', $iac);
			if ($length >= 0 and $length <= 100000) {
				$newannouncement = htmlentities(trim($newannouncement), ENT_QUOTES);
				if ($iac == "next") {
					$tpl->assign('NEWANNOUNCEMENT_BBC', umwandeln_bbcode($newannouncement));
					$tpl->assign('NEWANNOUNCEMENT', $newannouncement);
					$showPolitik = true;
					$iac = "";
				}
				elseif ($iac == "finish") {
					$ausgabe = "";
					$queriesend[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status[rid]."', 'Der Präsident hat die Syndikatsbeschreibung geändert.',2);";
					$queriesend[] = "update syndikate set description='".addslashes($newannouncement)."' where synd_id=".$status{rid};
					s("Sie haben die Syndikatsbeschreibung erfolgreich geändert.$weiter");
				}
			}
			else {	$iac = ""; $announcement = $newannouncement;
					if ($length >= 100000): f("Die Syndikatsbeschreibung darf maximal 100000 Zeichen lang sein! Du hast jedoch $length Zeichen eingegeben. Bitte kürze die Ankündigung entsprechend.");
					elseif ($length < 0): f("Die Syndikatsbeschreibung muss mindestens 0 Zeichen lang sein!"); endif;
			}
		}
		if (!$iac) {
			$tpl->assign('NEWANNOUNCEMENT', $newannouncement);
			$tpl->assign('ANNOUNCEMENT', $announcement);
			$showPolitik = true;
		}
	}

	//
	//// Syndikatspasswort ändern
	//

	if ($action == "chspw" and $isking)	{
		unset ($ausgabe);
		
		if (!$ia)	{ 
			$currentpw = single("select password from syndikate where synd_id=".$status[rid]);
			$tpl->assign('CURRENTPW', $currentpw);
			$showPolitik = true;
		}
		elseif ($ia == "next")	{
			if (preg_match("/^[a-zA-Zäöü0-9]{4,30}$/", $newsyndpassword))	{
				$newsyndpassword = addslashes($newsyndpassword);
				$queries[] = "update syndikate set password='$newsyndpassword' where synd_id=".$status{rid};
				s("Sie haben das Syndikatspasswort geändert!$weiter");
			}
			else { f("Das Syndikatspasswort muss zwischen 4 und 30 Zeichen lang sein und darf nur aus Zahlen und Buchstaben bestehen!$zurueck");}
		}
	}

	//
	//// Syndikatswebsite ändern
	//

	if ($action == "chlsw" and $isking)	{
		unset ($ausgabe);

		if (!$ia)	{
			$currentws = single("select syndikatswebseite from syndikate where synd_id=".$status[rid]);
			$tpl->assign('CURRENTWS', $currentws);
			$showPolitik = true;
		}
		elseif ($ia == "next")	{
			if (!$newsyndwebsite or preg_match("/^http:\/\/[^\"\' ]{5,248}$/", $newsyndwebsite))	{
				$newsyndwebsite = preg_replace("/</", "&gt;", $newsyndwebsite);
				$newsyndwebsite = preg_replace("/</", "&gt;", $newsyndwebsite);
				$newsyndwebsite = addslashes($newsyndwebsite);
				$queries[] = "update syndikate set syndikatswebseite='$newsyndwebsite' where synd_id=".$status{rid};
				s("Sie haben den Link auf die Syndikatswebsite geändert!$weiter");
			}
			else { f("Der Link auf die Syndikatswebsite muss folgendem Muster entsprechen: \"<strong>http://[www.][subdomain.]domainname.tld[/verzeichnis]/</strong>\". Werte in eckigen Klammern sind optional.$zurueck");}
		}
	}

	//
	//// Syndikatsbild ändern /  neu hochladen
	//
	if ($action == "chsbil" and $isking)	{
		unset ($ausgabe);
		$uploaddir = DATA.'/syndikatsimages/';
		if ($ia == "next")	{
			if ($_FILES[sbil][error] == 0 && $_FILES[sbil][size] <= 102400 and $_FILES[sbil][size] > 0)	{
				if (preg_match('/\.(jpg|jpeg|png)$/i', $_FILES[sbil][name]))	{

					list($width, $height, $type) = getimagesize($_FILES[sbil][tmp_name]);
					
					if ($width <= 540 and $height <= 80 and $width >= 100 and $height >= 30)	{
						
						#preg_match('#image\/[x\-]*([a-z]+)#', $_FILES[sbil][type], $avatar_filetype);
						#$avatar_filetype = $avatar_filetype[1];
						
						if ($type == 2): $avatar_filetype = "jpg";
						elseif ($type == 3): $avatar_filetype = "png";	endif;

						if ($avatar_filetype == "jpg" or $avatar_filetype == "png")	{
							$filepath=$uploaddir .SBILD_PREFIX.$status[rid].".".$avatar_filetype;
							if (move_uploaded_file($_FILES['sbil']['tmp_name'], $filepath) and $globals[updating] == 0)	{
								system("chmod 444 $filepath");
								if ($avatar_filetype != $image && $image)	{
									unlink($uploaddir .SBILD_PREFIX.$status[rid].".".$image);
								}
								s("Bild erfolgreich hochgeladen :)!<br><br><a href=politik.php class=linkAuftableInner>zurück</a>");
								$queriesend[] = "update syndikate set image='$avatar_filetype' where synd_id=".$status[rid];
							}
							else { f("Unbekannter Fehler aufgetreten<br>Aktion abgebrochen!"); $error=1;};
						}
						else { f("Ungültiges Dateiformat! Bitte ein JPEG- oder ein PNG-Bild hochladen!"); $error=1;}
						#elseif($avatar_filetype == "pjpeg") { f("Das Format deines Bildes ist (ungeachtet der Dateiendung) \"pjpeg\". Dieses Format wird jedoch nicht unterstützt. <br>Bitte anderes Bild wählen!"); $error=1; }
						#elseif($avatar_filetype == "gif")	{ f("Das Format deines Bildes ist (ungeachtet der Dateiendung) \"gif\". Dieses Format wird jedoch nicht unterstützt. <br>Bitte anderes Bild wählen!"); $error=1; }
					}
					else { f("Das Bild darf die maximale Größe von 540 x 80 Pixeln nicht über- und die minimale Größe von 100 x 30 Pixeln nicht unterschreiten!"); $error=1;}
				}
				else { f("Es sind nur .jpg/.jpeg bzw. .png-Dateien erlaubt!");  $error=1;}
			}
			elseif($_FILES[sbil][size] > 102400 or $_FILES[sbil][error] == 2) {
				f("Das Bild darf maximal 102.400 Bytes (100 KB) groß sein!");
				$error=1;
			}
			elseif($_FILES[sbil][error] == 4) {
				f("Es wurde kein Bild hochgeladen! Bitte wähle ein Bild von deiner Festplatte aus."); 
				$error=1;
			}
			else { f("Es ist ein unbekannter Fehler aufgetreten. Bitte erneut versuchen oder ggf. ein anderes Bild auswählen!"); $error=1;}
		}
		elseif ($ia == "del")	{
			if ($image and $globals[updating] == 0)	{
				unlink($uploaddir .KBILD_PREFIX.$status[rid].".".$image);
				$queriesend[] = "update syndikate set image='' where synd_id=".$status[rid];
				s("Das Bild wurde erfolgreich gelöscht<br><br><a href=politik.php class=linkAuftableInner>zurück</a>");
			}
			elseif ($globals[updating] == 1)	{ f("Unbekannter Fehler, bitte in 10 Sekunden erneut versuchen!"); $error=1;}
			else { f("Kein Bild vorhanden welches gelöscht werden kann"); $error=1; }
		}
		
		if (!$ia or $error)	{
			$tpl->assign('IMAGE', $image);
			$tpl->assign('KBILD_PREFIX', KBILD_PREFIX);
			$showPolitik = true;
		}
	}

	//
	//// KRIEG ERKLÄREN
	//

	if ($action == "dw" and $isking)	{
		unset ($ausgabe);
		if (!$allianzanfrage)	{
			if ($activewars_self_declared < 2)	{
				$krieg_forbidden_by_allianz_recently_gebildet = 0;
				$allianz_anfragen_data = assocs("select * from allianzen_anfragen where (1t='$status[rid]' or 2t='$status[rid]' or 3t='$status[rid]') and (1s=1 and 2s=1) and endtime >= (".($time-3*24*3600).")");
				if ($allianz_anfragen_data) {
					foreach ($allianz_anfragen_data as $vl) {
						if (!$vl['3t']) { $krieg_forbidden_by_allianz_recently_gebildet = 1; break; }
						if ($vl['3t']) {
							if ($vl['3s']) {
								$krieg_forbidden_by_allianz_recently_gebildet = 1; break;
							}
						}
					}
				}
				if (!$krieg_forbidden_by_allianz_recently_gebildet or true) { // Änderung ab Runde 14 wieder abgeschafft
					$scheinkrieg = single("select endtime from wars where (first_synd_1=".$status[rid]." or first_synd_2=".$status[rid]." or first_synd_3=".$status[rid].") and ended_by=20000 and endtime > ($time-7*24*3600) order by endtime desc limit 1");
					if (!$scheinkrieg)	{
						if (!$ia)	{
							$tpl->assign('SHOW', true);
							$showPolitik = true;	
						}
						elseif ($ia == "next" or $ia == "finish")	{
							if ($enemyid != $status[rid])	{
								$scheinkriegsdata = assocs("select second_synd_1, second_synd_2, second_synd_3 from wars where (first_synd_1=".$status[rid]." or first_synd_2=".$status[rid]." or first_synd_3=".$status[rid].") and ended_by=20000");
								if ($scheinkriegsdata)	{
									foreach ($scheinkriegsdata as $vl)	{
										if ($vl[second_synd_1] == $enemyid or $vl[second_synd_2] == $enemyid or $vl[second_synd_2] == $enemyid): $enemy_gesperrt = 1; endif;
									}
								}
								if (!$enemy_gesperrt)	{ # scheinkrieg wurde bereits geführt
									$enemy = getsyndname($enemyid,",president_id,allianz_id,ally1,ally2,allianzanfrage,synd_type");
									if ($enemy[0])	{ # Syndikat existiert und hat einen Namen
										if ($enemy[6] == "normal" and $game_syndikat[synd_type] != "normal" or $enemy[6] != "normal" and $game_syndikat[synd_type] == "normal") {
											if ($game_syndikat[synd_type] == "normal") { i("Ihr Syndikat ist kein Anfängersyndikat. Sie können nur Syndikaten den Krieg erklären, die nicht zu den Anfängersyndikaten zählen!$zurueck"); }
											else { i("Ihr Syndikat ist ein Anfängersyndikat. Sie können deshalb nur anderen Anfängersyndikaten Krieg erklären. Das von Ihnen gewählte Syndikat ist allerdings kein Anfängersyndikat.$zurueck"); }
										}
										else {
											if (!$allianz_id or $enemy[2] != $allianz_id)	{ # Syndikat ist nicht mit dem eigenen alliiert
												if (!$names{$enemyid}[name])	{ # Falls sich das Syndikat noch nicht mit dem gewählten im Krieg befindet
													$valid = check_for_restrictions($enemyid, $enemy);
													if ($enemy[3]): $name_ally1 = getsyndname($enemy[3]); $pre1 = " und seinem Verbündeten"; $allies = " Syndikat \"<strong>$name_ally1 (#".$enemy[3].")</strong>\""; $allies_wo_strong = " Syndikat $name_ally1 (#".$enemy[3].")"; endif;
													if ($enemy[4]): $name_ally2 = getsyndname($enemy[4]); $pre1 = " und seinen Verbündeten"; $allies .=  " und Syndikat \"<strong>$name_ally2 (#".$enemy[4].")</strong>\""; $allies_wo_strong = " und Syndikat $name_ally2 (#".$enemy[4].")"; endif;
													if (is_array($allies)) $allies = "";
													if ($ally1): $pre2 = " und sein Verbündeter:"; $after2 = " erklären"; $allies2 = " Syndikat \"<strong>".$names_allianz_partner[$ally1][name]." (#$ally1)</strong>\""; $allies2_wo_strong = " Syndikat ".$names_allianz_partner[$ally1][name]." (#$ally1)\""; endif;
													if ($ally2): $pre2 = " und seine Verbündeten"; $after2 = " erklären"; $allies2 .= " und Syndikat \"<strong>".$names_allianz_partner[$ally2][name]." (#$ally2)</strong>\""; $allies2_wo_strong = " und Syndikat ".$names_allianz_partner[$ally2][name]." (#$ally2)"; endif;
													if ($valid)	{
														if ($ia == "next"){ /*if ($id == 2912): $ausgabe .= "$bogulausgabe"; endif;*/ 
															$tpl->assign('SHOW_ACCEPT', true);
															$tpl->assign('ALLY', $ally[0]);
															$tpl->assign('ALLYID', $allyid);
															$showPolitik = true;
															$tpl->assign('ENEMY', $enemy[0]);
															$tpl->assign('ENEMYID', $enemyid);
															$tpl->assign('PRE1', $pre1);  // ja ich weiß das sollte eher direkt ins Template ;-)
															$tpl->assign('ALLIES', $allies);
														}
														elseif ($ia == "finish")	{
															# KRIEG in Wartable eintragen

															$queries[] = "insert into ". WARTABLE ." (first_synd_1,first_synd_2,first_synd_3,second_synd_1,second_synd_2,second_synd_3, first_1_landstart, first_2_landstart, first_3_landstart, second_1_landstart, second_2_landstart, second_3_landstart, starttime) values ('".$status{rid}."','".($ally1 ? $ally1:"0")."','".($ally2 ? $ally2:"0")."','$enemyid','".($enemy[3] ? $enemy[3]:"0")."','".($enemy[4] ? $enemy[4]:"0")."','".$startland[first][$status[rid]][tl]."','".$startland[first][$ally1][tl]."','".$startland[first][$ally2][tl]."','".$startland[second][$enemyid][tl]."','".$startland[second][$enemy[3]][tl]."','".$startland[second][$enemy[4]][tl]."','".($time+24*3600)."')";
															$queries[] = "update syndikate set atwar=1 where synd_id in (".$status{rid}.",".$enemyid.($ally1 ? ",".$ally1:"").($ally2 ? ",".$ally2:"").($enemy[3] ? ",".$enemy[3]:"").($enemy[4] ? ",".$enemy[4]:"").")";
															## Gucken ob Allianzanfrage da ist
															if ($enemy[5])	{
																list($cancel_anfragen_id, $cancel1, $cancel2, $cancel3) = row("select anfragen_id,1t,2t,3t from allianzen_anfragen where 1t=".$enemyid." or 2t=".$enemyid." or 3t=".$enemyid." order by time desc limit 1");
																$queries[] = "update allianzen_anfragen set endtime=$time where anfragen_id=".$cancel_anfragen_id;
																$queries[] = "update syndikate set allianzanfrage=0 where synd_id in (".$cancel1.",".$cancel2.($cancel3 ? ",".$cancel3:"").")";
																$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '$cancel1', 'Ein Krieg beendet die Allianzverhandlungen.',2);";
																$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '$cancel2', 'Ein Krieg beendet die Allianzverhandlungen.',2);";
																$cancel3 ? $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '$cancel3', 'Ein Krieg beendet die Allianzverhandlungen.',2);":"";
															}
															// TOWNCRIER AKTUELLES CNN Einträge noch ändern!
															$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '$enemyid', 'Das Syndikat \"<strong>$sname (#".$status{rid}.")</strong>\"$pre2$allies2".($after2 ? $after2:" erklärt")." Ihrem Syndikat den Krieg!',2);";
															$enemy[3] ? $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$enemy[3]."', 'Das Syndikat \"<strong>$sname (#".$status{rid}.")</strong>\"$pre2$allies2".($after2 ? $after2:" erklärt")." Ihrem Syndikat den Krieg!',2);":"";
															$enemy[4] ? $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$enemy[4]."', 'Das Syndikat \"<strong>$sname (#".$status{rid}.")</strong>\"$pre2$allies2".($after2 ? $after2:" erklärt")." Ihrem Syndikat den Krieg!',2);":"";
															$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Ihr Syndikat erklärt dem Syndikat \"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies den Krieg!',2);";
															$ally1 ? $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$ally1."', 'Ihr Bündnispartner \"<strong>$sname (#".$status{rid}.")</strong>\" erklärt dem Syndikat \"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies den Krieg!',2);":"";
															$ally2 ? $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$ally2."', 'Ihr Bündnispartner \"<strong>$sname (#".$status{rid}.")</strong>\" erklärt dem Syndikat \"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies den Krieg!',2);":"";
															$temp_synd_ids[] = $enemyid;
															$temp_synd_ids[] = $status['rid'];
															$temp_synd_ids[] = $enemy[3] ? $enemy[3] : 0;
															$temp_synd_ids[] = $enemy[4] ? $enemy[4] : 0;
															$temp_synd_ids[] = $ally1 ? $ally1 : 0;
															$temp_synd_ids[] = $ally2 ? $ally2 : 0;
															// Kriegserklärung an Twitter schicken - R4bbiT - 03.04.11
															tweet('krieg_start', array(	'a_rid' => array($status['rid'], $ally1, $ally2),
																						'd_rid' => array($enemyid, $enemy[3], $enemy[4])));
															$other_syndikate = singles("select synd_id from syndikate where synd_id not in (".join(",", $temp_synd_ids).")");
															towncrier($other_syndikate, "Das Syndikat \"<strong>$sname (#".$status{rid}.")</strong>\"$pre2$allies2".($after2 ? $after2:" erklärt")." dem Syndikat \"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies den Krieg!",0,2);
															$own_players = rows("select id, send_info_mails from status where rid in ($status[rid]".($ally1 ? ",$ally1":"").($ally2 ? ",$ally2":"").")");
															foreach ($own_players as $vl) {
																if ($vl[0] != $id) {
																	$messageinserts[] = "(46, $vl[0], $time, '".addslashes("\"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies")."')";
																}
																if ($vl[1][2] && !isonline($vl[0])) {
																	list($username, $vorname, $nachname, $email) = row("select username, vorname, nachname, email from users where konzernid = $vl[0]");
																	$betreff = "Kriegserklärung";
																	$nachricht = "Hallo ".(($vorname && $nachname) ? "$vorname $nachname" : "$username").",\nIhr Syndikat erklärt dem Syndikat ".$enemy[0]." (#$enemyid)$pre1$allies den Krieg! Der Krieg beginnt in etwa 24h.\n\nSie erhalten diese E-Mail, weil Sie unter Optionen angegeben haben, bei Kriegserklärungen, die Ihr Syndikat betreffen, informiert zu werden, falls Sie nicht eingeloggt sind.\n\nViel Spaß weiterhin beim Spielen, wünscht Ihnen Ihr\nSyndicates-Team";
																	sendthemail($betreff, $nachricht, $email, (($vorname && $nachname) ? "$vorname $nachname" : "$username"));
																}
															}
															$enemy_players = rows("select id, send_info_mails from status where rid in ($enemyid".($enemy[3] ? ",$enemy[3]":"").($enemy[4] ? ",$enemy[4]":"").")");
															foreach ($enemy_players as $vl) {
																$messageinserts[] = "(45, $vl[0], $time, '".addslashes("\"<strong>$sname (#".$status{rid}.")</strong>\"$pre2$allies2".($after2 ? $after2:" erklärt"))."')";
																#echo "<br>".$messageinserts[count($messageinserts)-1];
																if ($vl[1][2] && !isonline($vl[0])) {
																	list($username, $vorname, $nachname, $email) = row("select username, vorname, nachname, email from users where konzernid = $vl[0]");
																	$betreff = "Kriegserklärung";
																	$nachricht = "Hallo ".(($vorname && $nachname) ? "$vorname $nachname" : "$username").",\nDas Syndikat $sname (#".$status{rid}.")$pre2$allies2".($after2 ? $after2:" erklärt")." Ihrem Syndikat den Krieg! Der Krieg beginnt in etwa 24h.\n\nSie erhalten diese E-Mail, weil Sie unter Optionen angegeben haben, bei Kriegserklärungen, die Ihr Syndikat betreffen, informiert zu werden, falls Sie nicht eingeloggt sind.\n\nViel Spaß weiterhin beim Spielen, wünscht Ihnen Ihr\nSyndicates-Team";
																	sendthemail($betreff, $nachricht, $email, (($vorname && $nachname) ? "$vorname $nachname" : "$username"));
																}
															}
															s("Sie haben soeben dem Syndikat \"<strong>".$enemy[0]." (#$enemyid)</strong>\"$pre1$allies den Krieg erklärt! Der Krieg beginnt in 24h.$weiter");
															$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
														} #  Dieses Syndikat liegt außerhalb Ihrer möglichen Kriegserklärungsgrenze von mindestens 60% Ihrer eigenen Syndikatsgesamtstärke
													} else { 
														$tpl->assign('SHOW_NOTALLOWED', true);
														$tpl->assign('ENEMY', $enemy[0]);
														$tpl->assign('ENEMYID', $enemyid);
														$tpl->assign('WITH_ALLIED', $enemy[3]);
														$showPolitik = true;
														$tpl->assign('REASON', $nwdr); 
													}
												} else { f("Ihr Syndikat befindet sich mit dem gewählten Syndikat bereits im Krieg!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
											} else { f("Ihr Syndikat ist mit diesem Syndikat alliiert. Sie können diesem Syndikat keinen Krieg erklären!$zurueck"); }
										}
									} else { f("Dieses Syndikat existiert nicht!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
								} else { f("Sie haben diese Runde gegen dieses Syndikat bereits einen Scheinkrieg geführt.<br>Sie können diesem Syndikat daher für den Rest dieser Runde keinen weiteren Krieg mehr erklären.$zurueck"); }
							} else { f("Sie können Ihrem eigenen Syndikat nicht den Krieg erklären!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
						}
					} else { f("Sie haben innerhalb der letzten 7 Tage einen Scheinkrieg geführt.<br>Sie können erst wieder am ".date("d. M, H:i:s", $scheinkrieg+7*24*3600)." Uhr einen Krieg erklären.$zurueck"); }
				} else { f("Sie haben innerhalb der letzten 3 Tage eine neue Allianz gebildet. Bitte warten Sie bis die 3-Tagesfrist verstrichen ist, bevor Sie einen Krieg erklären können."); }
			} else { f("Sie führen bereits zwei Kriege. Es können immer nur 2 Kriege gleichzeitig erklärt werden!$zurueck"); }
		} else { f("Sie befinden sich zur Zeit in Allianzverhandlungen. Beenden Sie diese Verhandlungen zunächst, bevor Sie einem Syndikat den Krieg erklären können.$zurueck"); }
	}
	
	//
	//// FRIEDEN MACHEN
	//
	/* Wurde schon vor Runden deaktiviert - inok1989 Runde 63
	if ($action == "peace" and $isking and false)	{
		unset ($ausgabe);
		if ($kriegsdaten[$wnum])	{
			if (TRUE OR $kriegsdaten[$wnum][status] && $kriegsdaten[$wnum][starttime] + KRIEGSMINDESTDAUER < $time)	{
			if (($kriegsdaten[$wnum]['lwt_enemy1'] + $kriegsdaten[$wnum]['lwt_enemy2'] + $kriegsdaten[$wnum]['lwt_enemy3'] - $kriegsdaten[$wnum]['lwt_own1'] - $kriegsdaten[$wnum]['lwt_own2']  - $kriegsdaten[$wnum]['lwt_own3']) / ($kriegsdaten[$wnum]['lst_own1'] + $kriegsdaten[$wnum]['lst_own2'] + $kriegsdaten[$wnum]['lst_own3']) >= KRIEGSPROZENTVERLUSTE_FUER_FRIEDEN / 100) {
					if (!$ia)	{
						$syndikat_anrede = "dem Syndikat"; $syndikate = "<strong>".$names[$kriegsdaten[$wnum][gegner1]][name] . " (#".$kriegsdaten[$wnum][gegner1].")</strong>";
						if ($kriegsdaten[$wnum][gegner2]):
							$syndikat_anrede = "den Syndikaten";
							$syndikate .= ",<br><strong>".$names[$kriegsdaten[$wnum][gegner2]][name] . " (#".$kriegsdaten[$wnum][gegner2].")</strong>";
							if ($kriegsdaten[$wnum][gegner3]): $syndikate .= ",<br><strong>".$names[$kriegsdaten[$wnum][gegner3]][name] . " (#".$kriegsdaten[$wnum][gegner3].")</strong>"; endif;
						endif;
						$ausgabe = "<br><br><br><center><table border=0 cellpadding=4 cellspacing=0 width=100%><tr class=siteGround><td align=right valign=middle>Möchten Sie $syndikat_anrede </td><td align=center valign=top>$syndikate</td><td align=left valign=middle>wirklich ein Friedensangebot unterbreiten?</td></tr></table><br><br><a href=politik.php?action=peace&ia=finish&wnum=$wnum class=linkAufsiteBg>Bestätigen</a> - <a href=politik.php class=linkAufsiteBg>Abbrechen</a>";
					}
					elseif ($ia == "finish")	{/*
						// Kriegsprämie bestimmen
						$warupdatestring = ""; kriegspraemie($kriegsdaten[$wnum]);
						$queries[] = "update ". WARTABLE ." set status=0, endtime=$time, ended_by=".$status{rid}."$warupdatestring where war_id=$wnum";
						$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status{rid}."', 'Ihr Syndikat hat mit dem Syndikat \"".$names[$kriegsdaten[$wnum][gegner1]][name]." (#".$kriegsdaten[$wnum][gegner1].")\" Frieden geschlossen, der Krieg ist beendet!',2);";
						$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][gegner1]."', 'Das Syndikat \"$sname (#".$status{rid}.")\" schließt mit Ihrem Syndikat Frieden und beendet hierdurch den Krieg!',2);";
						if ($kriegsdaten[$wnum][gegner2]): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][gegner2]."', 'Das Syndikat \"$sname (#".$status{rid}.")\" schließt mit Ihrem Syndikat Frieden und beendet hierdurch den Krieg!',2);";	endif;
						if ($kriegsdaten[$wnum][gegner3]): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][gegner3]."', 'Das Syndikat \"$sname (#".$status{rid}.")\" schließt mit Ihrem Syndikat Frieden und beendet hierdurch den Krieg!',2);";	 endif;
						if ($kriegsdaten[$wnum][own1] && $kriegsdaten[$wnum][own1] != $status[rid]): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][own1]."', 'Ihr Bündnispartner \"$sname (#".$status{rid}.")\" schließt mit dem Syndikat \"".$names[$kriegsdaten[$wnum][gegner1]][name]." (#".$kriegsdaten[$wnum][gegner1].")\" Frieden und beendet hierdurch den Krieg!',2);";	 endif;
						if ($kriegsdaten[$wnum][own2] && $kriegsdaten[$wnum][own2] != $status[rid]): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][own2]."', 'Ihr Bündnispartner \"$sname (#".$status{rid}.")\" schließt mit dem Syndikat \"".$names[$kriegsdaten[$wnum][gegner1]][name]." (#".$kriegsdaten[$wnum][gegner1].")\" Frieden und beendet hierdurch den Krieg!',2);";	 endif;
						if ($kriegsdaten[$wnum][own3] && $kriegsdaten[$wnum][own3] != $status[rid]): $queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$kriegsdaten[$wnum][own3]."', 'Ihr Bündnispartner \"$sname (#".$status{rid}.")\" schließt mit dem Syndikat \"".$names[$kriegsdaten[$wnum][gegner1]][name]." (#".$kriegsdaten[$wnum][gegner1].")\" Frieden und beendet hierdurch den Krieg!',2);";	 endif;
						if ($activewars == 1): $queries[] = "update syndikate set atwar=0 where synd_id in (".$kriegsdaten[$wnum][own1].($kriegsdaten[$wnum][own2] ? ",".$kriegsdaten[$wnum][own2]:"").($kriegsdaten[$wnum][own3] ? ",".$kriegsdaten[$wnum][own3]:"").")"; endif;
						$count = single("select count(*) from ". WARTABLE ." where ((first_synd_1='".$kriegsdaten[$wnum][gegner1]."' or first_synd_2='".$kriegsdaten[$wnum][gegner1]."' or first_synd_3='".$kriegsdaten[$wnum][gegner1]."' or second_synd_1='".$kriegsdaten[$wnum][gegner1]."' or second_synd_2='".$kriegsdaten[$wnum][gegner1]."' or second_synd_3='".$kriegsdaten[$wnum][gegner1]."') and status=1)");
						if ($count == 1): $queries[] = "update syndikate set atwar=0 where synd_id in (".$kriegsdaten[$wnum][gegner1].($kriegsdaten[$wnum][gegner2] ? ",".$kriegsdaten[$wnum][gegner2]:"").($kriegsdaten[$wnum][gegner3] ? ",".$kriegsdaten[$wnum][gegner3]:"").")"; endif; *-/
						s("Sie haben den Krieg beendet. <br><br><a href=politik.php class=linkAufsiteBg>zurück</a>");
					}
			}
			else {
				f("Dieser Krieg kann noch nicht beendet werden. Sie können erst dann Frieden anbieten, wenn der Netto-Landverlust". KRIEGSPROZENTVERLUSTE_FUER_FRIEDEN."% des Startlandes überschritten hat.");
			}
			}
			elseif ($kriegsdaten[$wnum][starttime] + KRIEGSMINDESTDAUER > $time) { f("Dieser Krieg kann noch nicht beendet werden. Ein Krieg muss für mindestens 24h laufen.<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
			else { f("Ungültige Eingabe!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
		}
		else { f("Ungültigen Krieg ausgewählt!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
	} */
	
	//
	//// ALLIANZANFRAGE STELLEN (ursprünglich Allianz eingehen)
	//
	// von Tag 3 ß 0Uhr bis Tag 11 - 0Uhr

	if ($action == "ae" and $isking && !isBasicServer($game) && ( ( $globals[roundstarttime]+86400*2+36000 < $time && $globals[roundstarttime]+36000+86400*10 >= $time) || $game[name] == "Syndicates Testumgebung"))	{ //runde51 ally weg ---- Runde 60 allys wieder rein by dragon12
		if ($allianz_id)	{
			$allies = row("select first,second,third from allianzen where allianz_id=$allianz_id");
			if ($status[rid] == $allies[0]): $showally1 = $allies[1]; $showally2 = $allies[2];
			elseif ($status[rid] == $allies[1]): $showally1 = $allies[0]; $showally2 = $allies[2];
			elseif ($status[rid] == $allies[2]): $showally1 = $allies[0]; $showally2 = $allies[1];
			endif;
		}
		$gekuendigt_qm = assocs("select synd_id from allianzen_kuendigungen where synd_id in (".$status[rid].($showally1 ? ",$showally1":"").($showally2 ? ",$showally2)":")"),"synd_id");
		/*$top10 = assocs("SELECT sum(nw) as totalnw,`rid` FROM status,syndikate where syndikate.synd_id = status.rid and alive > 0 group by 'rid' ORDER BY totalnw DESC LIMIT 10");
		foreach ($top10 as $ky => $vl)	{
			if ($vl[rid] == $status[rid] or $vl[rid] == $showally1 or $vl[rid] == $showally2): $isintopten = 1; break; endif;
		}*/
		if (!$allianzanfrage && !single('SELECT count(*) FROM ally_pending WHERE syn1='.$status['rid'].' or syn2 = '.$status['rid']) and (!$showally1 and /* "or" bei 3 Allianzen*/ !$showally2) /*and !$atwar R61 by dragon12*/ and !$isintopten and !$gekuendigt_qm[$status[rid]] and !$gekuendigt_qm[$showally1] and !$gekuendigt_qm[$showally2] && ($vote[$id] / (count($safe)-$abzug_bei_stimmen_verhaeltnis)) >= 0.5)	{
			if (!$ia)	{ 
				$tpl->assign('SHOW', true);
				$showPolitik = true;	
			} elseif ($ia == "next" or $ia == "finish")	{
				// Überprüfen ob kein Krieg aktiv ist
				if (single("SELECT COUNT(*) FROM wars WHERE (first_synd_1=".$status['rid']." or first_synd_2=".$status['rid']." or first_synd_3=".$status['rid'].") AND starttime<=".time()."+86400 AND endtime=0")) {
					f("Dein Syndikat befindet sich in einem Krieg das es selbst erklärt hat und kann daher keine Allianz eingehen.$zurueck");
				} else if ($allyid != $status[rid] and $allyid != $showally1 and $allyid != $showally2 && !single('SELECT count(*) FROM ally_pending WHERE syn1 = '.$allyid.' or syn2 = '.$allyid))	{
					$ally = getsyndname($allyid, ",atwar,allianz_id,allianzanfrage,president_id,synd_type");
					/* es gibt keine Anfängersyndikate mehr
					if ($ally[5] == "normal" and $game_syndikat[synd_type] != "normal" or $ally[5] != "normal" and $game_syndikat[synd_type] == "normal") {
						if ($game_syndikat[synd_type] == "normal") { i("Ihr Syndikat ist kein Anfängersyndikat. Sie können sich nur mit Syndikaten alliieren, die nicht zu den Anfängersyndikaten zählen!$zurueck"); }
						else { i("Ihr Syndikat ist ein Anfängersyndikat. Sie können sich deshalb nur mit anderen Anfängersyndikaten alliieren.$zurueck"); }
					} else { */
					if (true) {
						if ($ally[2])	{
							$allies = row("select first,second,third from allianzen where allianz_id=".$ally[2]);
							if ($allyid == $allies[0]): $showally_target_1 = $allies[1]; $showally_target_2 = $allies[2];
							elseif ($allyid == $allies[1]): $showally_target_1 = $allies[0]; $showally_target_2 = $allies[2];
							elseif ($allyid == $allies[2]): $showally_target_1 = $allies[0]; $showally_target_2 = $allies[1];
							endif;
							$gekuendigt_target_qm = assocs("select synd_id from allianzen_kuendigungen where synd_id in (".$allyid.",$showally_target_1".($showally_target_2 ? ",$showally_target_2)":")"),"synd_id");
						}
						/*foreach ($top10 as $ky => $vl)	{
							if ($vl[rid] == $allyid or $vl[rid] == $showally_target_1 or $vl[rid] == $showally_target_2): $isintopten = 1; break; endif;
						}*/
						if ($ally[0] /*and !$ally[1] by dragon12 R61 (im krieg) */and (!$showally_target_1 and /* "or" bei 3 Allianzen*/ !$showally_target_2) and !$ally[3] and !$isintopten and !$gekuendigt_target_qm[$allyid] and !$gekuendigt_target_qm[$showally_target_1] and !$gekuendigt_target_qm[$showally_target_2])	{
							if ((!$showally1 and !$showally2) or (!$showally_target_1 and !$showally_target_2))	{
								if     ($showally_target_1): $third = $showally_target_1;
								elseif ($showally_target_2): $third = $showally_target_2;
								elseif ($showally1): $third = $showally1;
								elseif ($showally2): $third = $showally2;
								else: $third = 0;
								endif;
								if ($third): $thirddata = getsyndname($third,",president_id"); endif;
								if ($ia == "next")	{
									if ($third and ($showally_target_1 or $showally_target_2))	{
										$tpl->assign('THIRD', $thirddata[0]);
										$tpl->assign('THIRDID', $third);
									}
									
									$tpl->assign('SHOW_ACCEPT', true);
									$tpl->assign('ALLY', $ally[0]);
									$tpl->assign('ALLYID', $allyid);
									$showPolitik = true;
								}
								elseif ($ia == "finish")	{
									if ($third)	{
										if ($showally1 or $showally2)	{
											$supposed_to_join = $allyid;
											$second = $status[rid];
											$s_spalte = "2s";
											$messageinserts = "(21,'".$ally[4]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass das Syndikat #".$status[rid]." bereits mit dem Syndikat \"".$thirddata[0]." (#$third)\" alliiert ist und dieses Syndikat dem Antrag zustimmen muss. Ihr Syndikat wird daher bei positivem Verhandlungsausgang auch mit diesem Syndikat alliiert sein.')";
											$messageinserts .= ",(23,'".$thirddata[1]."',$time,'$sname (#".$status[rid].")|".$ally[0]." (#$allyid)')";
											$towncrierinserts = "($time, '".$third."','Ihr Allianzpartner <strong>".$sname." (#".$status[rid].")</strong> und Ihr Syndikat nehmen Allianz-Verhandlungen mit dem Syndikat <strong>$ally[0] (#$allyid)</strong> auf.',2)";
											$towncrierinserts .= ",($time, '".$status[rid]."','Der Präsident und Ihr Allianzpartner <strong>".$thirddata[0]." (#$third)</strong> nehmen Allianz-Verhandlungen mit dem Syndikat <strong>".$ally[0]." (#$allyid)</strong> auf.',2)";
											$towncrierinserts .= ",($time, '".$allyid."','Die Syndikate <strong>".$sname." (#".$status[rid].")</strong> und <strong>".$thirddata[0]." (#$third)</strong> nehmen mit Ihrem Syndikat Allianz-Verhandlungen auf.',2)";
										}
										elseif ($showally_target_1 or $showally_target_2)	{
											$supposed_to_join = $status[rid];
											$second = $allyid;
											$s_spalte = "1s";
											$messageinserts = "(21,'".$ally[4]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass Ihr Bündnispartner \"".$thirddata[0]." (#$third)\" diesem Antrag ebenfalls zustimmen muss.')";
											$messageinserts .= ",(21,'".$thirddata[1]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass Ihr Bündnispartner \"".$ally[0]." (#$allyid)\" diesem Antrag ebenfalls zustimmen muss.')";
											$towncrierinserts = "($time, '".$allyid."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> nimmt mit Ihrem Syndikat und Ihrem Allianzpartner <strong>".$thirddata[0]." (#$third)</strong> Allianz-Verhandlungen auf.',2)";
											$towncrierinserts .= ",($time, '".$third."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> nimmt mit Ihrem Syndikat und Ihrem Allianzpartner <strong>".$ally[0]." (#$allyid)</strong> Allianz-Verhandlungen auf.',2)";
											$towncrierinserts .= ",($time, '".$status[rid]."','Ihr Syndikat nimmt mit den Syndikaten <strong>".$ally[0]." (#$allyid)</strong> und <strong>".$thirddata[0]." (#$third)</strong> Allianz-Verhandlungen auf.',2)";
										}
									}
									else	{
										$supposed_to_join = $allyid;
										$second = $status[rid];
										$s_spalte = "2s";
										$messageinserts = "(21,'".$ally[4]."',$time,'$sname (#".$status[rid].")')"; 
										$towncrierinserts = "($time, '".$allyid."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> nimmt mit Ihrem Syndikat Allianz-Verhandlungen auf.',2)";
										$towncrierinserts .= ",($time, '".$status[rid]."','Ihr Syndikat nimmt mit dem Syndikat <strong>".$ally[0]." (#$allyid)</strong> Allianz-Verhandlungen auf.',2)";
									}
									$queries[] = "insert into message_values (id, user_id, time, werte) values $messageinserts";
									$queries[] = "insert into allianzen_anfragen (1t,2t,3t,time,$s_spalte) values ('".$supposed_to_join."','".$second."','".$third."','$time', 1)";
									$queries[] = "insert into towncrier (time, rid, message,kategorie) values $towncrierinserts";
									$queries[] = "update syndikate set allianzanfrage=1 where synd_id in (".$status[rid].",$allyid".($third ? ",$third":"").")";
									s("Sie haben die Allianzverhandlungen soeben erfolgreich aufgenommen. Sie müssen nun nur noch auf Bestätigung durch den/die Verhandlungspartner warten.$weiter");
								}
							}
							else	{ f("Dieses Syndikat ist bereits mit einem anderen Syndikat alliiert. Da Sie ebenfalls bereits mit einem Syndikat alliiert sind können Sie sich nur noch mit alleinstehenden Syndikaten verbünden.$zurueck");}
						}
						//elseif ($ally[1])	{ f("Das ausgewählte Syndikat befindet sich zur Zeit in einem Krieg. Allianzen können nur in Friedenszeiten geschlossen werden.$zurueck");} #removed by dragon12 R61, ally kann im krieg eingegangen werden, wird aber erst nach dem krieg aktiv
						elseif ($showally_target_1 or /* "and" bei 3 Allianzen*/ $showally_target_2)	{
						//f("Dieses Syndikat ist bereits mit 2 Syndikaten alliiert. Eine Allianz kann aus maximal 3 Syndikaten bestehen.$zurueck");
							f("Dieses Syndikat ist bereits mit einem Syndikat alliiert. Eine Allianz kann aus maximal 2 Syndikaten bestehen.$zurueck");
						}
						elseif ($ally[3])	{ f("Dieses Syndikat befindet sich bereits mit einem anderen Syndikat in Verhandlungen. Bitte versuchen Sie es später noch einmal.$zurueck");}
						elseif ($isintopten){ f("Dieses Syndikat [oder sein bisheriger Allianzpartner (falls vorhanden)] ist eines der Top10-Syndikate (Networth) und kann daher keine Allianzen schließen.$zurueck");}
						elseif ($gekuendigt_target_qm[$allyid] or $gekuendigt_target_qm[$showally_target_1] or $gekuendigt_target_qm[$showally_target_2]) { f("Mit diesem Syndikat können Sie derzeit leider keine Allianzverhandlungen aufnehmen, da eine Allianzkündigung zwischen diesem Syndikat und seinem bisherigen Allianzpartner noch nicht durchgeführt wurde. Warten Sie bitte ab, bis die Allianzkündigung ausgeführt wurde, bevor Sie mit diesem Syndikat wieder Allianzverhandlungen aufnehmen. Erkundigen Sie sich diesbezüglich beim Präsidenten, wann die Kündigung durchgeführt wird.$zurueck"); }
						else { f("Dieses Syndikat existiert nicht!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
					}
				}
				elseif ($allyid == $showally1 or $allyid == $showally2)	{ f("Sie sind mit diesem Syndikat bereits alliiert.$zurueck");}
				elseif(single('SELECT count(*) FROM ally_pending WHERE syn1 = '.$allyid.' or syn2 = '.$allyid)) {
					f("Dieses Syndikat hat bereits einer Allianz zugestimmt, die nur solange innaktiv bleibt, bis der Krieg vorüber ist..$zurueck");
				}
				else { f("Sie können Ihrem eigenen Syndikat keine Allianz vorschlagen!<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>"); }
			}
		}
		elseif ($allianzanfrage)	{
			f("Ihr Syndikat befindet sich bereits mit einem Syndikat in Verhandlungen. Beenden Sie diese Verhandlungen zunächst, bevor Sie weitere starten können.$zurueck");
		}
		elseif ($showally1 /* "and" bei 3 Allianzen*/ or $showally2)	{
			//f("Sie sind bereits mit 2 Syndikaten alliiert. Eine Allianz kann aus maximal 3 Syndikaten bestehen.$zurueck");
			f("Sie sind bereits mit einem Syndikat alliiert. Eine Allianz kann aus maximal 2 Syndikaten bestehen.$zurueck");
		}
		elseif(single('SELECT count(*) FROM ally_pending WHERE syn1='.$status['rid'].' or syn2 = '.$status['rid'])) {
			f("Sie haben bereits einer anderen Allianz zugestimmt, welche nach Ende des Krieges aktiv werden wird. Sie können keine weiteren Allianzen schließen.$zurueck");
		}
		/*elseif ($atwar)	{
			f("Sie befinden sich zur Zeit in einem Krieg. Allianzen können nur in Friedenszeiten geschlossen werden.$zurueck");
		}brauchts nicht mehr, warum siehe oben (dragon12 R61)*/
		elseif ($isintopten) {
			f("Ihr Syndikat [oder ihr Allianzpartner (falls vorhanden)] ist eines der Top10-Syndikate (Networth) und kann daher keine Allianzen schließen.$zurueck");
		}
		elseif ($gekuendigt_qm[$status[rid]] or $gekuendigt_qm[$showally1] or $gekuendigt_qm[$showally2]) {
			f("Sie können derzeit keine weitere Allianz eingehen, da Sie oder einer Ihrer bisherigen Allianzpartner die Allianz aufgekündigt haben. Warten Sie bitte, bis die Allianzkündigung wirksam wird, bevor Sie wieder Allianzverhandlungen aufnehmen.$zurueck");
		}
		elseif(($vote[$id] / (count($safe)-$abzug_bei_stimmen_verhaeltnis)) < 0.5) {//allianzen erst ab 50% der stimmen möglich. dragon12 R60
			f("Sie bennötigen mindestens 50% der stimmen um eine Allianz eingehen zu können.");
		}elseif($globals[roundstarttime]+86400*2+36000 < $time && $globals[roundstarttime]+36000+86400*10 >= $time) {
			i("Sie können Allianzen nur zwischen Tagen 3 bis 10 nach Rundenbeginn eingehen.");
		}
	}
	
	//
	//// ALLIANZANFRAGE BESTÄTIGEN (ablehnen)
	//

	if ($action == "ab" and $isking && ( ( ($vote[$id] / (count($safe)-$abzug_bei_stimmen_verhaeltnis)) >= 0.5 && $globals[roundstarttime]+86400*3 < $time && $globals[roundstarttime]+36000+86400*10 >= $time) || $game[name] == "Syndicates Testumgebung" || $what == "decline") )	{ //runde51 ally weg ---- Runde 60 allys wieder rein by dragon12
		unset ($ausgabe);
		if ($allianzanfrage)	{
			if ($third and !$ally1) {
				$tpl->assign('ZWEIT_NAME', $names_allianz_verhandlungspartner[$zweit]{name});
				$tpl->assign('ZWEIT_ID', $zweit);
			}
			if ($ownd): $what = "decline"; endif;
			$tpl->assign('WHAT', $what);
			if (!$what)	{ 
				$showPolitik = true;
				$tpl->assign('SYNNAME', $names_allianz_verhandlungspartner[$erst]{name});
				$tpl->assign('SYNID', $erst);
			}
			elseif ($what == "accept" or $what == "decline")	{
				if ($ia != "finish")	{
					if ($what == "accept")	{	
						$tpl->assign('SYNNAME', $names_allianz_verhandlungspartner[$erst]{name});
						$tpl->assign('SYNID', $erst);
						$showPolitik = true;
					}
					if ($what == "decline")	{	
						$tpl->assign('SYNNAME', $names_allianz_verhandlungspartner[$erst]{name});
						$tpl->assign('SYNID', $erst);
						$showPolitik = true;
					}
				}
				elseif ($ia == "finish")	{
					$president_ids = assocs("select president_id as pid,synd_id from syndikate where synd_id in ($erst".($zweit ? ",$zweit":"").")", "synd_id");
					if ($what == "accept" && single("SELECT COUNT(*) FROM wars WHERE (first_synd_1=".$status['rid']." or first_synd_2=".$status['rid']." or first_synd_3=".$status['rid'].") AND starttime<=".time()."+86400 AND endtime=0")) {
						f("Dein Syndikat befindet sich in einem Krieg das es selbst erklärt hat und kann daher keine Allianz eingehen.$zurueck");
					} else if ($what == "accept")	{	# Man selbst hat die Anfrage nicht gestellt, da man sonst bereits zugestimmt hätte
						# 3er Allianz soll gebildet werden
						if ($third)	{	# An der Verhandlung sind 3 Syndikate beteiligt  || änderungen R61 hier nicht implementiert, da es 3er allys nicht gibt. Das im krieg eingehen passiert hier noch sofort, nicht nach dem Krieg! dragon12
							if (!$erstd or !$zweitd)	{	# Bisher hat der 3. Verhandlungspartner noch nicht zugestimmt - Allianz kommt noch nicht zustande
								# Ausgehend von einer 2er Allianz
								# tested
								if ($first == $status[rid])	{	# Man selbst soll in eine bestehende 2er-Allianz aufgenommen werden ($first ist immer das dazukommende Syndikat)
									$queries[] = "update allianzen_anfragen set 1s=1 where anfragen_id=".$allianzanfragedaten[anfragen_id];
									s("Sie haben einer Allianz soeben zugestimmt. Ob die Allianz letzen Endes zustande kommt hängt nun nur noch vom Präsidenten von \"<strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>\" ab.$weiter");
									$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass die Allianz erst mit Zustimmung Ihres Bündnispartners \"".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)\" zustande kommt.')";
									$messageinserts .= ",(26,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass die Allianz erst mit Ihrer Zustimmung zustande kommt.')";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Syndikats <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung Ihres Bündnispartners <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
									$towncrierinserts .= ",($time, '".$zweit."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Präsidenten.',2)";
								}
								# Ausgehend von einem einzelnen Syndikat
								# tested
								elseif ($second == $status[rid])	{	# Eigener Bündnispartner hat noch nicht zugestimmt - Allianzvorschlag geht von einzelnem Syndikat aus
									$queries[] = "update allianzen_anfragen set 2s=1 where anfragen_id=".$allianzanfragedaten[anfragen_id];
									s("Sie haben einer Allianz soeben zugestimmt. Ob die Allianz letzen Endes zustande kommt hängt nun nur noch von Ihrem Bündnispartner \"<strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>\" ab.$weiter");
									$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass die Allianz erst mit Zustimmung des Syndikats \"".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)\" zustande kommt.')";
									$messageinserts .= ",(24,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")|".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)| Bitte beachten Sie, dass die Allianz erst mit Ihrer Zustimmung zustande kommt.')";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung Ihres Bündnispartners <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Syndikats <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
									$towncrierinserts .= ",($time, '".$zweit."','Ihr Bündnispartner <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Präsidenten.',2)";
								}
								# Ausgehend von unbekannt (steht noch nicht fest)
								elseif ($third == $status[rid])	{	# Wer von den anderen noch nicht zugestimmt hat steht noch nicht fest - Man selbst hat jedoch bereits einen Bündnispartner
									$queries[] = "update allianzen_anfragen set 3s=1 where anfragen_id=".$allianzanfragedaten[anfragen_id];
									# Ausgehend von einzelnem Syndikat
									if ($erstd)	{	# Allianz geht von einem einzelnen Syndikat aus - Bündnispartner hat noch nicht zugestimmt
										# DIESER BLOCK IST IDENTISCH MIT DEM AUS DEM VORANGEHENDEN IF-BLOCK
										s("Sie haben einer Allianz soeben zugestimmt. Ob die Allianz letzen Endes zustande kommt hängt nun nur noch von Ihrem Bündnispartner von \"<strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>\" ab.$weiter");
										$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass die Allianz erst mit Zustimmung des Syndikats \"".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)\" zustande kommt.')";
										$messageinserts .= ",(24,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")|".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)| Bitte beachten Sie, dass die Allianz erst mit Ihrer Zustimmung zustande kommt.')";
										$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung Ihres Bündnispartners <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
										$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Syndikats <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong>.',2)";
										$towncrierinserts .= ",($time, '".$zweit."','Ihr Bündnispartner <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Präsidenten.',2)";
									}
									# Ausgehend vom Bündnispartner
									# tested
									elseif ($zweitd)	{ # Allianz geht von Bündnispartner aus - eingeladenes Syndikat hat noch nicht zugestimmt
										s("Sie haben einer Allianz soeben zugestimmt. Ob die Allianz letzen Endes zustande kommt hängt nun nur noch vom Präsidenten von \"<strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong>\" ab.$weiter");
										$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Bitte beachten Sie, dass die Allianz erst mit Ihrer Zustimmung zustande kommt.')";
										$messageinserts .= ",(24,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")|".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)| Bitte beachten Sie, dass die Allianz erst mit Zustimmung des Syndikats \"".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)\" zustande kommt.')";
										$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Syndikats <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong>.',2)";
										$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Präsidenten.',2)";
										$towncrierinserts .= ",($time, '".$zweit."','Ihr Bündnispartner <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Bitte beachten: Für das Zustandekommen der Allianz fehlt noch die Zustimmung des Syndikats <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong>.',2)";
									}
								}
							}
							elseif ($erstd && $zweitd)	{ # Man selbst ist der letzte der zustimmt - Allianz kommt zustande
								# Einige grundlegende Queries unabhängig von Verhältnisgrad
								if (!$allianz_id): $allianz_id = single("select allianz_id from syndikate where synd_id=$erst"); endif;
								$queries[] = "update syndikate set allianzanfrage=0, ally1=$second, ally2=$third, allianz_id=$allianz_id where synd_id=$first";
								$queries[] = "update syndikate set allianzanfrage=0, ally1=$third, ally2=$first where synd_id=$second";
								$queries[] = "update syndikate set allianzanfrage=0, ally1=$first, ally2=$second where synd_id=$third";
								$queries[] = "update allianzen set first=$first, second=$second, third=$third where allianz_id=$allianz_id";
								# Ausgehend von einer 2er Allianz
								# tested
								if ($first == $status[rid])	{ # Man selbst soll in eine bestehende 2er-Allianz aufgenommen werden ($first ist immer das dazukommende Syndikat)
									$queries[] = "update allianzen_anfragen set 1s=1,endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
									s("Sie haben einer Allianz soeben zugestimmt. Da alle beteiligten Parteien zugestimmt haben ist Ihr Syndikat nun wirksam mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> alliiert.$weiter");
									$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Ihr Syndikat ist nun mit den Syndikaten \"$sname (#".$status[rid].")\" und \"".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)\" alliiert.')";
									$messageinserts .= ",(26,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")| Ihr Syndikat ist nun mit den Syndikaten \"$sname (#".$status[rid].")\" und \"".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)\" alliiert.')";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> zu.<br>Ihr Syndikat ist nun mit mit diesen beiden Syndikaten alliiert.',2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> zu.<br>Ihr Syndikat ist nun mit den Syndikaten <strong>$sname (#".$status[rid].")</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> alliiert.',2)";
									$towncrierinserts .= ",($time, '".$zweit."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Ihr Syndikat ist nun mit den Syndikaten <strong>$sname (#".$status[rid].")</strong> und <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> alliiert.',2)";
								}
								# Ausgehend von unbekannt (steht noch nicht fest)
								# tested
								elseif ($second == $status[rid] or $third == $status[rid])	{ # Man selbst ist einer aus einer bereits bestehenden 2er-Allianz
									# Sachen die unabhängig des Verhältnisses stimmen
									s("Sie haben einer Allianz soeben zugestimmt. Da alle beteiligten Parteien zugestimmt haben ist Ihr Syndikat nun wirksam mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> alliiert.$weiter");
									$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Ihr Syndikat ist nun mit den Syndikaten \"$sname (#".$status[rid].")\" und \"".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)\" alliiert.')";
									$messageinserts .= ",(24,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")|".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)| Ihr Syndikat ist nun mit den Syndikaten \"$sname (#".$status[rid].")\" und \"".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)\" alliiert.')";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Ihr Syndikat ist nun mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> alliiert.',2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Ihr Syndikat ist nun mit den Syndikaten <strong>$sname (#".$status[rid].")</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> alliiert.',2)";
									$towncrierinserts .= ",($time, '".$zweit."','Ihr Bündnispartner <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Ihr Syndikat ist nun mit den Syndikaten <strong>$sname (#".$status[rid].")</strong> und <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> alliiert.',2)";
									# Geht die Allianz vom einzelnen Syndikat aus und man ist an "zweiter" Stelle
									if ($second == $status[rid])	{
										$queries[] = "update allianzen_anfragen set 2s=1,endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
									}
									# Andernfalls: Von wem die Verhandlungen ausgehen lassen sich hier nicht mehr überprüfen - man selbst steht jedoch an dritter Stelle
									elseif ($third == $status[rid])	{
										$queries[] = "update allianzen_anfragen set 3s=1,endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
									}
								}
							}
						}
						# 2er-Allianz soll gebildet werden - keines der beiden beteiligten Syndikate hat bis jetzt einen Bündnispartner
						# tested
						elseif (!$third)	{ # Bündnis kommt mit Zustimmung sofort zustande da auf keine 3. Partei gewartet werden muss
							if ($globals[updating] == 0) {
								if(single("SELECT count(*) FROM syndikate where atwar = 1 and synd_id in ($first,$second)")) {
									$queries[] = "insert into ally_pending (syn1, syn2, anfragen_id) values ($first, $second, ".$allianzanfragedaten[anfragen_id].")";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Da sich eines der beiden Syndikate im Krieg befindet, wird die Allianz erst nach Beendigung des Kriegs geschlossen.', 2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Da sich eines der beiden Syndikate im Krieg befindet, wird die Allianz erst nach Beendigung des Kriegs geschlossen.', 2)";
									select("update syndikate set allianzanfrage=0 where synd_id=$first");
									select("update syndikate set allianzanfrage=0 where synd_id=$second");
									s("Sie haben einer Allianz soeben zugestimmt. Da sich mindestends eines der beiden Syndikate derzeit im Krieg befindet, wird die Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> erst nach Beendigung des Krieges offiziel.$weiter");
								}
								else {
									select("insert into allianzen (first, second) values ($first,$second)");
									$allianz_id = single("select allianz_id from allianzen where first=$first and second=$second");
									$queries[] = "update syndikate set allianzanfrage=0, ally1=$second, allianz_id=$allianz_id where synd_id=$first";
									$queries[] = "update syndikate set allianzanfrage=0, ally1=$first, allianz_id=$allianz_id where synd_id=$second";
									$queries[] = "update allianzen_anfragen set 1s=1, endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
									s("Sie haben einer Allianz soeben zugestimmt. Ihr Syndikat ist nun mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> alliiert.$weiter");
									$messageinserts = "(26,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")| Ihr Syndikat ist nun mit diesem Syndikat alliiert.')";
									$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident stimmt einer Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> zu.<br>Ihr Syndikat ist nun mit diesem Syndikat alliiert.', 2)";
									$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> stimmt einer Allianz mit Ihrem Syndikat zu.<br>Ihr Syndikat ist nun mit diesem Syndikat alliiert.', 2)";
								}
							}
						}
					}
					elseif ($what == "decline")	{	# Wer die Anfrage gemacht hat steht noch nicht fest - ist aber eigentlich auch egal, da die Verhandlungen abgebrochen werden - Allianz wird gekillt;
						$queries[] = "update syndikate set allianzanfrage=0 where synd_id in ($first,$second".($third ? ",$third)":")");
						if ($third)	{ # Es hätte einer 3er-Allianz gebildet werden sollen
							if ($first == $status[rid])	{	# Man selbst hätte in die Allianz eintreten sollen - Man muss den Antrag nicht zwingend selbst gestellt haben
								$queries[] = "update allianzen_anfragen set 1s=0, endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
								s("Sie haben die Allianzverhandlungen mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> soeben erfolgreich abgebrochen. Die Verhandlungen sind damit gescheitert.$weiter");
								$messageinserts = "(27,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")')";
								$messageinserts .= ",(27,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")')";
								$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident lehnt eine Allianz mit den Syndikaten <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> und <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
								$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> lehnt eine Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$zweit]{name}." (#$zweit)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
								$towncrierinserts .= ",($time, '".$zweit."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> lehnt eine Allianz mit Ihrem Syndikat und Ihrem Bündnispartner <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
							}
							elseif ($second == $status[rid] or $third == $status[rid])	{ # Man selbst ist in einer 2er-Allianz und hätte einen Bündnispartner gewonnen
								$queries[] = "update allianzen_anfragen set ".($second == $status[rid] ? "2":"3")."s=0, endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
								s("Sie haben die Allianzverhandlungen mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> soeben erfolgreich abgebrochen. Die Verhandlungen sind damit gescheitert.$weiter");
								$messageinserts = "(27,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")')";
								$messageinserts .= ",(25,'".$president_ids[$zweit][pid]."',$time,'$sname (#".$status[rid].")|".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)')";
								$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident lehnt eine Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
								$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> lehnt eine Allianz mit Ihrem Syndikat ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
								$towncrierinserts .= ",($time, '".$zweit."','Ihr Bündnispartner <strong>$sname (#".$status[rid].")</strong> lehnt eine Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
							}
						}
						# tested
						elseif (!$third)	{ # Es hätte eine 2er-Allianz werden sollen
							$queries[] = "update allianzen_anfragen set ".($first == $status[rid] ? "1":"2")."s=0, endtime=$time where anfragen_id=".$allianzanfragedaten[anfragen_id];
							s("Sie haben die Allianzverhandlungen mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> soeben erfolgreich abgebrochen. Die Verhandlungen sind damit gescheitert.$weiter");
							$messageinserts = "(27,'".$president_ids[$erst][pid]."',$time,'$sname (#".$status[rid].")')";
							$towncrierinserts .= "($time, '".$status[rid]."','Der Präsident lehnt eine Allianz mit dem Syndikat <strong>".$names_allianz_verhandlungspartner[$erst]{name}." (#$erst)</strong> ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
							$towncrierinserts .= ",($time, '".$erst."','Das Syndikat <strong>$sname (#".$status[rid].")</strong> lehnt eine Allianz mit Ihrem Syndikat ab.<br>Die Verhandlungen sind damit gescheitert.',2)";
						}
					}
					
					if ($messageinserts): $queries[] = "insert into message_values (id, user_id, time, werte) values $messageinserts"; endif;
					if ($towncrierinserts): $queries[] = "insert into towncrier (time, rid, message,kategorie) values $towncrierinserts"; endif;
				}
			}
		}
		elseif (!$allianzanfrage)	{
			f("Ihr Syndikat befindet sich in keinen Verhandlungen. Nehmen Sie, falls möglich, zunächst mit einem anderen Syndikat Verhandlungen auf, bevor Sie eine Allianz bestätigen oder ablehnen können.$zurueck");
		}
	}
	elseif($action == "ab" && $isking && ($vote[$id] / (count($safe)-$abzug_bei_stimmen_verhaeltnis)) < 0.5) {
		f("Sie bennötigen mindestens 50% der stimmen um eine Allianz eingehen zu können.");
	} elseif($globals[roundstarttime]+86400*3 < $time && $globals[roundstarttime]+36000+86400*10 >= $time) {
		if ($action == "ab") {
			f("Sie können Allianzen nur zwischen Tagen 3 bis 10 nach Rundenbeginn eingehen.");
		} else {
			i("Sie können Allianzen nur zwischen Tagen 3 bis 10 nach Rundenbeginn eingehen.");
		}
	}

	//
	//// ALLIANZ AUFKÜNDIGEN
	//
	/*
	if ($action == "aa" and $isking)	{ //runde51 ally weg  ---- Runde 60 allys wieder rein, aber kündigen kann man nicht mehr! by dragon12
		unset ($ausgabe);
		if ($allianz_id) {
			if (!$atwar)	{
				if (!$allianzanfrage)	{
					$allies = row("select first,second,third from allianzen where allianz_id=$allianz_id");
					if ($status[rid] == $allies[0]): $showally1 = $allies[1]; $showally2 = $allies[2];
					elseif ($status[rid] == $allies[1]): $showally1 = $allies[0]; $showally2 = $allies[2];
					elseif ($status[rid] == $allies[2]): $showally1 = $allies[0]; $showally2 = $allies[1];
					endif;
					if ($showally1 == 0 && $showally2): $showally1 = $showally2; $showally2 = 0; endif;
					if ($showally1) {
						$gekuendigt_qm = assocs("select synd_id from allianzen_kuendigungen where synd_id in (".$status[rid].",$showally1".($showally2 ? ",$showally2)":")"),"synd_id");
						$showally1 = $gekuendigt_qm[$showally1] ? 0 : $showally1;
						$showally2 = $gekuendigt_qm[$showally2] ? 0 : $showally2;
						if ($showally1 == 0 && $showally2): $showally1 = $showally2; $showally2 = 0; endif;
						if ($showally1) {
							if (!$gekuendigt_qm[$status[rid]]) {
								if (!$ia)	{ $ausgabe = "<br><br><br><center>Möchten Sie die Allianz mit <strong>".$names_allianz_partner[$showally1]{name}." (#$showally1)</strong>".($showally2 ? " und <strong>".$names_allianz_partner[$showally2]{name}." (#$showally2)</strong>":"")." wirklich aufkündigen?<br><br><a href=politik.php?action=aa&ia=finish class=linkAufsiteBg>Bestätigen</a> - <a href=politik.php class=linkAufsiteBg>Abbrechen</a>";	}
								elseif ($ia == "finish")	{
									$president_ids = assocs("select president_id as pid,synd_id from syndikate where synd_id in ($showally1".($showally2 ? ",$showally2":"").")", "synd_id");
									$hourtime = get_hour_time($time);
									$kuendtime = $hourtime + 25 * 3600;
									s("Sie haben die Allianz mit <strong>".$names_allianz_partner[$showally1]{name}." (#$showally1)</strong>".($showally2 ? " und <strong>".$names_allianz_partner[$showally2]{name}." (#$showally2)</strong>":"")." soeben aufgekündigt.<br>Nach außen hin sind Sie bereits jetzt nicht mehr verbündet, d.h. Sie sind Ihren Bündnispartnern nicht mehr dazu verpflichtet Ihnen in einem Krieg beizustehen und erscheinen in der Syndikatsübersicht auch als alleinstehendes Syndikat. Sie können jedoch bis zum Kündigungszeitpunkt weiterhin keine Angriffe auf Ihre Bündnispartner durchführen und ihnen auch keinen Krieg erklären. Das selbe gilt umgekehrt für Ihre bisherigen Bündnispartner.<br>Der Austritt erfolgt am ".date("d. M, H:i", $kuendtime)." Uhr. $weiter");
									$queries[] = "insert into allianzen_kuendigungen (synd_id,time) values (".$status[rid].",$kuendtime)";
									$queries[] = "update syndikate set ally1=0, ally2=0 where synd_id=".$status[rid];
									$queries[] = "update syndikate set ally2=0, ally1=".($showally2 ? "$showally2":"0")." where synd_id=$showally1";
									$showally2 ? $queries[] = "update syndikate set ally2=0, ally1=$showally1 where synd_id=$showally2":"";
									$messageinserts = "(28,'".$president_ids[$showally1][pid]."',$time,'$sname (#".$status[rid].")|".date("d. M, H:i", $kuendtime)." Uhr')";
									$towncrierinserts = "($time, '".$status[rid]."','Der Präsident kündigt die Allianz mit <strong>".$names_allianz_partner[$showally1]{name}." (#$showally1)</strong>".($showally2 ? " und <strong>".$names_allianz_partner[$showally2]{name}." (#$showally2)</strong>":"")." zum ".date("d. M, H:i", $kuendtime)." Uhr auf.',2)";
									$towncrierinserts .= ",($time, '".$showally1."','Der Präsident von <strong>$sname (#".$status[rid].")</strong> kündigt die Allianz mit Ihrem Syndikat".($showally2 ? " und <strong>".$names_allianz_partner[$showally2]{name}." (#$showally2)</strong>":"")." zum ".date("d. M, H:i", $kuendtime)." Uhr auf.',2)";
									if ($showally2):
										$messageinserts .= ",(28,'".$president_ids[$showally2][pid]."',$time,'$sname (#".$status[rid].")|".date("d. M, H:i", $kuendtime)." Uhr')";
										$towncrierinserts .= ",($time, '".$showally2."','Der Präsident von <strong>$sname (#".$status[rid].")</strong> kündigt die Allianz mit Ihrem Syndikat und <strong>".$names_allianz_partner[$showally1]{name}." (#$showally1)</strong> zum ".date("d. M, H:i", $kuendtime)." Uhr auf.',2)";
									endif;
									$queries[] = "insert into message_values (id, user_id, time, werte) values $messageinserts";
									$queries[] = "insert into towncrier (time, rid, message,kategorie) values $towncrierinserts";
								}
							} else { f("Sie haben die Allianz bereits aufgekündigt.$zurueck"); }
						} else { f("Ihr bisheriger Allianzpartner bzw. Ihre bisherigen Allianzpartner haben die Allianz mit Ihrem Syndikat bereits aufgekündigt.$zurueck"); }
					} else { f("Sie haben keine Allianzpartner, sodass Sie keine Allianz aufkündigen können.$zurueck"); }
				} elseif ($allianzanfrage) { f("Sie befinden sich zur Zeit mit einem Syndikat in Allianzverhandlungen. Beenden Sie diese zunächst, bevor Sie aus einer Allianz austreten können!$zurueck");}
			} elseif ($atwar) { f("Sie befinden sich zur Zeit in einem Krieg. Bitte beenden Sie zunächst alle Kriege, bevor Sie aus einer Allianz austreten können!$zurueck");}
		} else { f("Sie haben keine Allianz geschlossen, die Sie kündigen könnten.$zurueck"); }
	}
	*/ 
	//
	//// PREPARE KICK VOTE
	//
	/* TESTAUSGABE
	if ($KICKTESTS) {
		$kicked_users = assocs("SELECT status.id, status.rid, status.rulername, status.syndicate, status.lastlogintime, status.alive, users.username FROM status, users 
			WHERE status.id = users.konzernid AND alive = 1 AND (rid = 0)");
		foreach($kicked_users as $vl) {
			$vl['lastlogintime'] = date($vl['lastlogintime']);
			pvar($vl);
		}
	} */

	// inaktive Spieler kann der Präsident in einen inaktiven Pool schieben/kicken -- Runde 63 inok1989
	if ($action == "pkv" and $isking)	
	{
		$players = assocs("SELECT rulername, syndicate, id, isnoob FROM status WHERE rid=".$status[rid]." AND alive > 0 AND (lastlogintime + ".TIME_TILL_INACTIVE.") < ".$time, "id");
		$players_all = assocs("SELECT rulername, syndicate, id, isnoob FROM status WHERE rid=".$status[rid]." AND alive > 0 AND (lastlogintime + ".TIME_TILL_INACTIVE.") >= ".$time, "id");
		if (!$players) {
			f("Es gibt keinen inaktiven Spieler in deinem Syndikat.");
		} elseif ($globals[roundstarttime] - $time <= 0) {
			if (!$atwar) {
				if (($vote[$id] / count($players_all)) >= PRAESI_PROZENT_TO_KICK)	{
					// mindestanzahl an Spielern die nötig sind (derzeit deaktiviert)
					if (true || count($players_all) > 4)	{
						$lastkickbarrier = single("select time from politik_kick where time > ($time-".TIME_BETWEEN_TWO_KICKVOTES.") and rid=".$status[rid]);
						if (true || !$lastkickbarrier)	{
							if (!$ia)	{ 
								$tpl->assign('PLAYERS', $players);
								$showPolitik = true;	
							} elseif (!single("SELECT COUNT(*) FROM status 
									WHERE id = '".addslashes($who)."' AND (lastlogintime + ".TIME_TILL_INACTIVE.") < ".$time)) {
								f("Der ausgewählte Konzern ist nicht inaktiv!");
							} elseif ($ia == "next" or $ia == "finish")	{
								if ($players[$who] && $who != $id)	{
									if ($ia == "next")	{
										$tpl->assign('SHOW_ACCEPT', true);
										$tpl->assign('TOKICK_RULERNAME', $players[$who][rulername]);
										$tpl->assign('TOKICK_SYNDICATE', $players[$who][syndicate]);
										$tpl->assign('WHO', $who);
										$showPolitik = true;
									}
									elseif ($ia == "finish")	{
										// Erst nach Rundenstart kickbar
										if ($globals[roundstarttime] /*+ 2 * 24 * 3600*/ < $time)	{
											$type = "normal";
											if ($players[$who]{isnoob}) $type = "noob";
											
											$old_rids_DEFECT = singles("select ridbefore from options_defect where user_id = $who and time > ".$globals['roundstarttime']);
											$old_rids_KICK = singles("select rid from politik_kick where kicked = $who and time > ".$globals['roundstarttime']);
											$old_rids = array_merge($old_rids_DEFECT, $old_rids_KICK);
											$old_rids[] = $status[rid];
											$newrid = get_an_empty_syndicate("inaktiv");
											
											if (true || $newrid)	{
												$queries[] = "insert into politik_kick (time, kicked, rid) values ('$time', '$who', '".$status[rid]."')";
												$queries[] = "update status set rid=".$newrid." where id=".$who;
												
												
												list($podpoints, $wholand) = row("select podpoints, land from status where id = '".floor($who)."'");
												if (!$podpoints): $podpoints = 0; endif;
												if ($podpoints < (-1) * $wholand * 2000): $podpoints = (-1) * $wholand * 2000; endif; // Kleine Sicherheitsvorkehrung, damit sich ein Syndikat keinen "Saboteur" anlegt, ihn um paar hundert Millionen verschuldet und in ein anderes Syndikat reinschickt
												if ($podpoints <= 0){
													$podpoints *= -1;
													$queries[] = "update syndikate set podmoney = podmoney + $podpoints where synd_id = ".$status[rid];
													//$queries[] = "update syndikate set podmoney = podmoney - $podpoints where synd_id = ".$newrid;
												} else {

													$resstats = getresstats();  # Aktuellen Lagerkurs holen
													$ress = assoc("select podenergy, podmetal, podsciencepoints, podmoney from syndikate where synd_id= $status[rid]"); 
													$resswert = array();  #$ress; #neues Array um den Wert der einzelnen Ressis auszurechnen
													foreach ($resstats as $key =>$temp) {
														if ($temp[type] != "money") {
															$resstats[$key][value] *= RESSTATS_MODIFIER; # Lagerpreise ermitteln
														}
														$podproduct = "pod".$temp[type];
														$resswert[$temp[type]] = $ress[$podproduct] * $resstats[$key][value];  # Ressiwert ermitteln
													}
													asort ($resswert); #sortieren damit die kleinsten ressis oben sind..
													$temp = $resswert;
													foreach ($temp as $key => $value) {
														if ($value < 1){ 
															unset($resswert[$key]);  # Ressis mit weniger als 1 Unit entfernen
														}
													}
													reset($resswert);
													$kleinress = current($resswert);  # kleinsten Teiler nehmen um Verhältnis zu berechnen
													$ressumme = 0;     #Summe der einzelnen Verhältnisse um das Lagerguthaben zu teilen..
													foreach ($resswert as $key => $value) {
														$resswert[$key] = $value / $kleinress;   # Verhältnis der Lagerressis zueinander
														$ressumme += $resswert[$key];
													}
													$verpoints = $podpoints / $ressumme;    # wieviele Podpoints pro Verhältnispunkt
													foreach ($resstats as $key => $tres) {
														$resswert[$key] = floor($verpoints * $resswert[$key]  / $resstats[$key][value]);
														$temp = "pod".$key;
														if ($resswert[$key] > $ress[$temp]) {$resswert[$key] = $ress[$temp];}
													}

													$queries[] = "update syndikate set podmoney = podmoney - $resswert[money], 
																	   podmetal = podmetal - $resswert[metal],
																	   podenergy = podenergy - $resswert[energy],
																	   podsciencepoints = podsciencepoints - $resswert[sciencepoints]
																	   where synd_id = ".$status[rid];
													/*$queries[] = "update syndikate set podmoney = podmoney + $resswert[money],
																	   podmetal = podmetal + $resswert[metal],
																	   podenergy = podenergy + $resswert[energy],
																	   podsciencepoints = podsciencepoints + $resswert[sciencepoints]
																	   where synd_id = ".$newrid; */

												}
												// Für GM-Logs & Lagerguthaben
												$queries[] = "INSERT INTO syndikate_wechsel (konzernid, oldrid, newrid, time,
																							 podmoney,podmetal,podenergy,podsciencepoints) 
													VALUES ('".$who."', '".$status[rid]."', '".$newrid."', '".$time."', 
															'".$resswert[money]."', '".$resswert[metal]."', '".$resswert[energy]."', '".$resswert[sciencepoints]."')";

												$queries[] = "update ".$globals{statstable}." set rid=".$newrid." where round=".$globals[round]." and konzernid=".$who;
												$message="Der Konzern <b>".$players[$who]{syndicate}."</b> wurde vom Präsidenten aus dem Syndikat entfernt, weil er inaktiv war.";
												$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',2)";
												array_push($queries,$action);
												//$message="Der Konzern <b>".$players[$who]{syndicate}."</b> tritt aus wirtschaftlichen Interessen unserem Syndikat bei.";
												$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$newrid.",'".$message."',2)";
												array_push($queries,$action);
												player_leave_syndicate($who, $status[rid]);
												$queries[] = "INSERT INTO message_values (id, user_id, time, werte) 
													VALUES (44, $who, $time, 'Der Präsident ihres bisherigen Syndikats hat Sie aus dem Syndikat ausgeschlossen, da sie länger inaktiv waren..')";
												s("Der Spieler ".$players[$who][rulername]." von ".$players[$who][syndicate]." wurde aus Ihrem Syndikat entfernt.$weiter");									
											} else { f("Es gibt momentan kein freies Syndikat, welchem der Spieler zugewiesen werden könnte. Versuchen Sie es später noch einmal."); }
										} else { f("Erst nach Rundenstart können inaktive Spieler aus dem Syndikat ausgeschlossen werden"); }
									}
								}
								elseif (!$players[$who]) { f("Ungültigen Spieler ausgewählt!$zurueck");}
								elseif ($who == $id) { f("Sie können sich nicht selbst zur Wahl stellen!$zurueck");}
							}
						} else { f("Sie haben innerhalb der letzten ".(TIME_BETWEEN_TWO_KICKVOTES / 3600)."h bereits einen Spieler aus dem Syndikat geworfen.$zurueck");}
					} else { f("Ihr Syndikat muss mindestens 5 Spieler haben bevor Sie jemanden hinauswerfen können.$zurueck");}
				} else { f("Sie benötigen eine 75% Stimmmehrheit, um einen Spieler aus dem Syndikat hinauswerfen zu können.$zurueck"); }
			} else { f("Sie können während eines Krieges keine Spieler aus dem Syndikat ausschließen!$zurueck"); }
		} else { f("Sie können vor Rundenstart keine Spieler aus dem Syndikat ausschließen!$zurueck"); }
	}


	//
	//// Syndikatsfusionierung
	//

	if ($action == "synfus" && getServertype() != "basic")	{
		$continue = 0;
		if ($globals[roundendtime] < $time + 14 * 24 * 3600) {
			f("Syndikatsfusionen sind nur bis 2 Wochen vor Rundenende möglich!$zurueck");
		}
		elseif ($globals[roundstarttime] - $time > 0) {
			f("Syndikatsfusionen sind vor Rundenstart nicht möglich!$zurueck");
		}
		/*
		elseif (!$open) {
			f("Ihr Syndikat ist abgeschlossen und kann daher diese Runde nicht mehr fusionieren!$zurueck");
		}
		sonderregelung für runde 12
		*/
		else {
			$max_players_for_fusionierung = MAX_PLAYERS_FOR_FUSIONIERUNG;
			$already_anfrage = single("select count(*) from politik_synfus where (first = ".$status[rid]." or second = ".$status[rid].") and done = 0");
			unset ($ausgabe);

			if ($sac == "handle") {
				$synnummer = 0;
				if ($status[rid] != $who) {
					foreach ($fusionierungsanfrage as $ky => $vl) {
						if ($vl[first] == $who) { $synnummer = $who; break; }
						elseif ($vl[second] == $who) { $synnummer = $who; break; }
					}
				}
			}

			$spieler_im_eigenen_syndikat = single("select count(*) from status where rid = ".$status[rid]);
			list ($atwar, $allianzanfrage, $allianz_id) = row("select atwar, allianzanfrage, allianz_id from syndikate where synd_id = ".$status[rid]);
			if ($synnummer): $spieler_im_anderen_syndikat = single("select count(*) from status where rid = ".floor($synnummer));
							list ($atwar2, $allianzanfrage2, $allianz_id2, $open2, $synd_type2) = row("select atwar, allianzanfrage, allianz_id, open, synd_type from syndikate where synd_id = ".floor($synnummer)); endif;

			if (!$atwar && !$allianzanfrage && !$allianz_id or ($sac == "handle" && $what == "decline")) {
				if ($synnummer && ($synd_type2 == "normal" and $game_syndikat[synd_type] != "normal" or $synd_type2 != "normal" and $game_syndikat[synd_type] == "normal")) {
					if ($game_syndikat[synd_type] == "normal") { i("Ihr Syndikat ist kein Anfängersyndikat. Sie können nur mit Syndikaten fusionieren, die nicht zu den Anfängersyndikaten zählen!$zurueck"); }
					else { i("Ihr Syndikat ist ein Anfängersyndikat. Sie können deshalb nur mit anderen Anfängersyndikaten fusionieren. Das von Ihnen gewählte Syndikat ist allerdings kein Anfängersyndikat.$zurueck"); }
				} else {
					if ($spieler_im_anderen_syndikat or !$synnummer) {
						if ($spieler_im_anderen_syndikat <= $max_players_for_fusionierung or !$synnummer) {
							if ($spieler_im_eigenen_syndikat <= $max_players_for_fusionierung or ($sac == "handle" && $what == "decline")) {
								if ($spieler_im_anderen_syndikat + $spieler_im_eigenen_syndikat <= MAX_USERS_A_SYNDICATE) {
									if ((!$atwar2 && !$allianzanfrage2 && !$allianz_id2 or ($sac == "handle" && $what == "decline")) or !$synnummer) {
										$continue = 1;
									}
									elseif ($atwar2) { f("Das von Ihnen gewählte Syndikat befindet sich zur Zeit in einem Krieg. $zurueck"); }
									elseif ($allianzanfrage2) { f("Das von Ihnen gewählte Syndikat befindet sich zur Zeit in Allianvzerhandlungen. $zurueck"); }
									elseif ($allianz_id2) { f("Das von Ihnen gewählte Syndikat gehört einer Allianz an. Sie können jedoch nur mit allianzlosen Syndikaten fusionieren. $zurueck"); }
								} else { f("Das von Ihnen gewählte Syndikat hat zusammen mit Ihrem Syndikat mehr als ".MAX_USERS_A_SYNDICATE." Mitglieder. Sie können daher nicht mit diesem Syndikat fusionieren.$zurueck"); }
							} else { f("Ihr Syndikat hat mehr als $max_players_for_fusionierung Spieler. Sie können daher nicht mit anderen Syndikaten fusionieren.$zurueck."); }
						} else { f("Das von Ihnen gewählte Syndikat hat mehr als $max_players_for_fusionierung Spieler. Sie können daher nicht mit diesem Syndikat fusionieren.$zurueck"); }
					} else { f("Das von Ihnen gewählte Syndikat existiert nicht, oder hat keine Spieler. Bitte wählen Sie ein anderes Syndikat.$zurueck"); }
				}
				if ($continue) {
					if (!$sac) {
						if (!$already_anfrage) {
							if (floor($synnummer) != $status[rid]) {
								if (!$ia) {
									$tpl->assign('SHOW_NEW_SELECTSYN', true);
									$tpl->assign('MAX_PLAYERS_FOR_FUSIONIERUNG', $max_players_for_fusionierung);
									$tpl->assign('MAX_USERS_A_GROUP', MAX_USERS_A_GROUP);
									$showPolitik = true;
								}
								elseif ($ia) {
									if ($ia == "next") {
										$tpl->assign('SHOW_NEW_ACCEPT', true);
										$tpl->assign('SYNNAME', single("select name from syndikate where synd_id=".floor($synnummer)));
										$tpl->assign('SYNNUMMER', floor($synnummer));
										$showPolitik = true;
									}
									if ($ia == "finish") {
									// Successmeldung
									s("Sie haben dem Syndikat <b>".single("select name from syndikate where synd_id=".floor($synnummer))." (#".floor($synnummer).")</b> erfolgreich vorgeschlagen, miteinander zu fusionieren.$weiter");
									// Eintrag in POLITIK_SYNFUS-Table schreiben
									$queries[] = "insert into politik_synfus (first, second, time, done) values (".floor($synnummer).", ".$status[rid].", $time, 0)";
									// Anderen Präsi informieren
									$queries[] = "insert into message_values (id, user_id, time, werte) values (44, ".single("select president_id from syndikate where synd_id = ".floor($synnummer)).", $time, 'Das Syndikat <b>$sname (#".$status[rid].")</b> möchte mit Ihrem Syndikat fusionieren. Unter Politik können Sie diesen Antrag annehmen oder ablehnen.')";
									// TOWNCRIER
									$queries[] = "insert into towncrier (time, rid, message,kategorie) values ($time, $synnummer, 'Das Syndikat <b>$sname (#".$status[rid].")</b> schlägt Ihrem Syndikat vor miteinander zu fusionieren.', 2), ($time, ".$status[rid].", 'Der Präsident schlägt dem Syndikat <b>".single("select name from syndikate where synd_id=".floor($synnummer))." (#".floor($synnummer).")</b> vor miteinander zu fusionieren.',2)";
									}
								}
							} else { f("Sie können nicht mit sich selbst fusionieren!$zurueck"); }
						} else { f("Es stehen noch Anfragen zwecks Fusionierung aus, bitte beantworten Sie diese zunächst, bevor Sie selbst Fusionierungsanfragen stellen können!$zurueck"); }
					}
					elseif ($sac == "handle") {
						if ($synnummer) {
							if ($what == "decline") {
								if (!$ia) {
									$tpl->assign('SHOW_ABLEHNEN', true);
									$tpl->assign('SYNNAME', $synnames[$synnummer][name]);
									$tpl->assign('SYNNUMMER', floor($synnummer));
									$showPolitik = true;
								}
								elseif ($ia == "finish") {
									s("Sie haben die Fusionierungsverhandlungen mit dem Syndikat <b>".$synnames[$synnummer][name]." (# $synnummer)</b> erfolgreich abgebrochen.$weiter");
									// Eintrag aus POLITIK_SYNFUS-Table löschen
									$queries[] = "delete from politik_synfus where $specific=$synnummer and ".($specific == "first" ? "second": "first")."=".$status[rid]." and done = 0";
									// Anderen Präsi informieren
									$queries[] = "insert into message_values (id, user_id, time, werte) values (44, ".single("select president_id from syndikate where synd_id = ".floor($synnummer)).", $time, 'Das Syndikat <b>$sname (#".$status[rid].")</b> hat die Fusionierungsverhandlungen mit Ihrem Syndikat abgebrochen.')";
									// TOWNCRIER
									$queries[] = "insert into towncrier (time, rid, message,kategorie) values ($time, $synnummer, 'Das Syndikat <b>$sname (#".$status[rid].")</b> bricht die Fusionierungsverhandlungen mit Ihrem Syndikat ab.',2), ($time, ".$status[rid].", 'Der Präsident bricht die Fusionierungsverhandlungen mit dem Syndikat <b>".single("select name from syndikate where synd_id=".floor($synnummer))." (#".floor($synnummer).")</b> ab.',2)";
								}
							}
							elseif ($what == "accept") {
								if (!$ia) {
									$tpl->assign('SHOW_ACCEPT', true);
									$tpl->assign('SYNNAME', $synnames[$synnummer][name]);
									$tpl->assign('SYNNUMMER', floor($synnummer));
									$showPolitik = true;
								}
								elseif ($ia == "finish") {
									s("Sie haben die Fusionierungsverhandlungen mit dem Syndikat <b>".$synnames[$synnummer][name]." (# $synnummer)</b> erfolgreich abgeschlossen und Ihr Syndikat mit diesem Syndikat fusioniert. Herzlichen Glückwunsch!.$weiter");
									//Wer kommt zu wem?
									$own_total_nw = single("select sum(nw) from status where rid=".$status[rid]);
									$other_total_nw = single("select sum(nw) from status where rid=$synnummer");
									if ($other_total_nw < $own_total_nw) {
										$staying = $status[rid]; $moving = $synnummer;
									} elseif ($other_total_nw > $own_total_nw) {
										$staying = $synnummer; $moving = $status[rid];
									}
									// Eintrag aus POLITIK_SYNFUS-Table auf done = 1 setzen und first und second aktualisieren
									$queries[] = "update politik_synfus set first=$moving, second=$staying, done = 1 where $specific=$synnummer and ".($specific == "first" ? "second": "first")."=".$status[rid]." and done = 0";
									// Leute aus den Syndikaten per Message informieren.
									$staying_people = singles("select id from status where rid = $staying");
									$moving_people = singles("select id from status where rid = $moving");
									foreach ($staying_people as $ky => $vl) {
										$messageinserts[] = "(44, $vl, $time, 'Die Fusionierungsverhandlungen mit dem Syndikat <b>".$synnames[$moving][name]." (#$moving)</b> waren erfolgreich. Die Mitglieder dieses Syndikats wurden Ihrem Syndikat zugeordnet. Die Aktien dieses Syndikats wurden dem Wert entsprechend in Aktien Ihres Syndikats umgetauscht.')";
									}
									foreach ($moving_people as $ky => $vl) {
										$messageinserts[] = "(44, $vl, $time, 'Die Fusionierungsverhandlungen mit dem Syndikat <b>".$synnames[$staying][name]." (#$staying)</b> waren erfolgreich. Die Mitglieder Ihres Syndikats wurden diesem Syndikat zugeordnet. Die Aktien Ihres Syndikats wurden dem Wert entsprechend in Aktien dieses Syndikats umgetauscht.')";
									}
									// Syndikat aus Syndikate-Table löschen
									$queries[] = "delete from syndikate where synd_id = $moving";
									// SPIELER-RIDS UPDATEN
									$queries[] = "update status set rid = $staying where rid = $moving";
									// STATS-TABLE UPDATEN
									$queries[] = "update stats set rid = $staying where rid = $moving and round = ".$globals[round];
									// LAGERLOGS;
									$queries[] = "update lagerlogs set rid = $staying where rid = $moving";
									// SYN-BOARD
									$queries[] = "update board_subjects set bid = $staying where bid = $moving";
									// MESSAGES AN DIE MITGLIEDER
									$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
									// TOWNCRIER
									$queries[] = "insert into towncrier (time, rid, message,kategorie) values ($time, $staying, 'Das Syndikat <b>".$synnames[$moving][name]." (#".$moving.")</b> wurde in Ihr Syndikat integriert.',2)";
									// Towncriereinträge des umziehenden Syndikats auf neue Synd_id updaten
									$queries[] = "update towncrier set rid=$staying where rid=$moving";
									list ($aktienkurs_moving, $metal, $money, $energy, $sciencepoints, $dividenden, $energyforschung, $sabotageforschung, $creditforschung, $offspecs, $defspecs) = row("select aktienkurs, podmetal, podmoney, podenergy, podsciencepoints, dividenden, energyforschung, sabotageforschung, creditforschung, offspecs, defspecs from syndikate where synd_id = $moving");

									// AKTIEN SCHNARCH
									$aktienkurs_staying = single("select aktienkurs from syndikate where synd_id = $staying");

									// SYNDIKATSSPEZIFISCHE SACHEN WIE LAGER, SYNFORSCHUNGEN UND SYNARMEE
									$queries[] = "update syndikate set podmoney=podmoney+$money, podenergy=podenergy+$energy, podmetal=podmetal+$metal, podsciencepoints=podsciencepoints+$sciencepoints, dividenden=dividenden+$dividenden, energyforschung=energyforschung+$energyforschung, sabotageforschung=sabotageforschung+$sabotageforschung, creditforschung=creditforschung+$creditforschung, offspecs=offspecs+$offspecs, defspecs=defspecs+$defspecs where synd_id = $staying";

									// AKTIEN DER AKTIONÄRE UMSCHICHTEN

									$aktiendata = assocs("select number, user_id, synd_id from aktien where synd_id in ($staying, $moving)");

									$queries[] = "delete from aktien where synd_id in ($staying, $moving)";

									if ($aktiendata) {
										foreach ($aktiendata as $ky => $vl) {
											if ($vl[synd_id] == $staying) {
												$besitz[$vl[user_id]][$staying] += $vl[number] * $aktienkurs_staying;
											}
											else {
												$besitz[$vl[user_id]][$moving] += $vl[number] * $aktienkurs_moving;
											}
										}
										foreach ($besitz as $ky => $vl) {
											$besitz_gesamt[$ky] += $vl[$staying] + $vl[$moving];
										}
										foreach ($besitz_gesamt as $ky => $vl) {
											$newnumber = ceil($vl / $aktienkurs_staying);
											update_aktien('add', $ky, $newnumber, $staying, $aktienkurs_staying);
										}
									}
								}
							}
						} else { f("Sie befinden sich mit dem gewählten Syndikat nicht in Fusionierungsverhandlungen!$zurueck"); }
					}
				}
			}
			elseif ($atwar) { f("Um mit einem anderen Syndikat fusionieren zu können, dürfen Sie sich nicht im Krieg befinden. $zurueck"); }
			elseif ($allianzanfrage) { f("Um mit einem anderen Syndikat fusionieren zu können, dürfen Sie keine Allianzverhandlungen mit anderen Syndikaten führen. $zurueck"); }
			elseif ($allianz_id) { f("Um mit einem anderen Syndikat fusionieren zu können, dürfen Sie keiner Allianz angehören. Kündigen Sie die Allianz zunächst auf, bevor Sie weitermachen können. $zurueck"); }
		}
	}

/*
	//
	//// Privates Abkommen abschließen (pa)
	//

	if ($action == "pa" && false)	{
		unset ($ausgabe);
		$totalabk = single("select count(*) from naps_spieler where user_id=$id");
		if ($totalabk < ABKOMMEN_ANZAHLBESCHRAENKUNG)	{
			if ($rid): $players = assocs("select rulername, syndicate, id from status where rid=".$rid." order by rulername asc", "id"); endif;
			if (!$rid or !$players)	{
				$ausgabe .= "<br><br><br><center>Bitte geben Sie die Nummer des Syndikats an, in welchem sich der Spieler, dem Sie ein Abkommen vorschlagen möchten, befindet!<br><br><form action=politik.php method=post><input type=hidden name=action value=pa><input type=text name=rid value=".($rid ? $rid:"\"\"")." size=3> <input type=submit value=weiter></form><br><br><a href=politik.php class=linkAufsiteBg>Zurück</a></center>";
			}
			elseif (!$plid && $rid != $status[rid])	{
				foreach ($players as $ky => $vl)	{
					if ($ky != $id): $options .= "<option value=$ky>".$vl[rulername]." von ".$vl[syndicate]; endif;
				}
				$ausgabe .= "<br><br>Wählen Sie nun den gewünschten Spieler sowie Art des Abkommens aus, und geben Sie an wie lange die Kündigungsfrist und wie hoch die Kündigungsstrafe sein soll!<br>Erläuterung: Nach Abschluss kann der Vertrag jederzeit gekündigt werden. Hierbei muss jedoch die Kündigungsfrist eingehalten werden und es müssen vom Kündigenden Cr. in Höhe der Kündigungsstrafe gezahlt werden. Dieses Geld wird vernichtet und kommt niemandem zu Gute.<br><br><center><form action=politik.php method=get><input type=hidden name=action value=pa><input type=hidden name=rid value=$rid><table class=siteGround cellpadding=5 cellspacing=0 border=0><tr class=siteGround><td>Spieler wählen</td><td><select name=plid>$options</select></td></tr><tr class=siteGround><td>Art des Abkommens</td><td><select name=abkommen><option value=1>Nichtangriffspakt (NAP)<option value=2>Nichtspionagepakt (NSP)<option value=3>Nichtangriffsspionagepakt (NASP)</select></td></tr><tr class=siteGround><td>Kündigungsfrist</td><td><select name=frist><option value=24>24h<option value=48 selected>48h<option value=96>96h<option value=168>168h</select></td></tr><tr class=siteGround><td>Kündigungsstrafe</td><td><select name=kstrafe><option value=0>0 Cr.<option value=5000>5.000 Cr.<option value=10000>10.000 Cr.<option value=25000>25.000 Cr.<option value=50000>50.000 Cr.<option value=100000 selected>100.000 Cr.<option value=250000>250.000 Cr.<option value=500000>500.000 Cr.<option value=1000000>1.000.000 Cr.<option value=2500000>2.500.000 Cr.<option value=5000000>5.000.000 Cr.<option value=10000000>10.000.000 Cr.<option value=25000000>25.000.000 Cr.<option value=50000000>50.000.000 Cr.<option value=100000000>100.000.000 Cr.</select></td></tr></table></center>
				<br><br>ACHTUNG: Idealerweise sollten vor Absenden dieses Abkommens-Vorschlags bereits Verhandlungen mit dem gewählten Spieler vorausgegangen sein (z.B. über die Mitteilungen). Sinnlos gestellte Abkommens-Vorschläge werden in der Regel vom jeweiligen Spieler meist abgelehnt.<br><br><center><input type=submit value=weiter></form><br><br><a href=politik.php class=linkAufsiteBg>Zurück</a></center>";
			}
			elseif ($rid == $status[rid])	{ f("Sie können mit Spielern aus Ihrem Syndikat keine Abkommen abschließen.$zurueck"); }
			elseif ($rid && $plid)	{
				if ($players[$plid]
					&& ($abkommen == 1 or $abkommen == 2 or $abkommen = 3)
					&& ($frist == 24 or $frist == 48 or $frist == 96 or $frist == 168)
					&& ($kstrafe == 0 or $kstrafe == 5000 or $kstrafe == 10000 or $kstrafe == 25000 or $kstrafe == 50000 or $kstrafe == 100000 or $kstrafe == 250000 or $kstrafe == 500000 or $kstrafe == 1000000 or $kstrafe == 2500000 or $kstrafe == 5000000 or $kstrafe == 10000000 or $kstrafe == 25000000 or $kstrafe == 50000000 or $kstrafe == 100000000)
					)	{
					if (!$naps_sd[$plid])	{
						$totalabk = single("select count(*) from naps_spieler where user_id=$plid");
						if ($totalabk < ABKOMMEN_ANZAHLBESCHRAENKUNG)	{
							if ($globals[updating] == 0)	{
								ignore_user_abort(TRUE);
								if ($globals[roundstatus] != 2){   #muss man händisch machen da gleich darunter die ID abgefragt wird.
									select("insert into naps_spieler_spezifikation (initiator, partner, type, kstrafe, kfrist) values ($id,$plid,$abkommen,$kstrafe,$frist)");
								}
								$napid = single("select napid from naps_spieler_spezifikation where initiator=$id and partner=$plid order by napid desc limit 1");
								$queries[] = "insert into naps_spieler (napid, user_id, nappartner) values ($napid, $id, $plid), ($napid, $plid, $id)";
								$queries[] = "insert into message_values (id, user_id, time, werte) values (30, $plid, $time, '".$status[rulername]." von ".$status[syndicate]." (#".$status[rid].")')";
								ignore_user_abort(FALSE);
								s("Sie haben dem Spieler ".$players[$plid][rulername]." von ".$players[$plid][syndicate]." (#$rid) ein Abkommen vorgeschlagen. Der Spieler wurde darüber informiert und dürfte Ihre Anfrage demnächst beantworten.$weiter");
							} else { f("Es läuft gerade das stündliche Update, während dem stündlichen Update können keine Aktionen durchgeführt werden. Probieren Sie es bitte in etwa 15 Sekunden noch einmal.<br><br><center><a href=javascript:history.back() class=linkAufTableInner>Zurück</a></center>");}
						} else { f("Dieser Spieler hat bereits die maximale Anzahl möglicher Abkommen abgeschlossen.$zurueck");}
					} else { f("Sie haben mit diesem Spieler bereits ein Abkommen abgeschlossen bzw. befinden sich mit diesem Spieler bereits in Verhandlungen.$zurueck"); }
				} else { f("Ungültige Parameter.$zurueck");}
			}
		} else { f("Sie haben bereits die maximale Anzahl möglicher Abkommen in Höhe von ".ABKOMMEN_ANZAHLBESCHRAENKUNG." abgeschlossen bzw. befinden sich noch in Verhandlungen mit anderen Spielern über ein Abkommen.$zurueck");}
	}

	//
	//// Privates Abkommen bearbeiten (pab) # Annehmen, Ablehnen, Zurücknehmen, Kündigen
	//

	#	$naps_sd = assocs("select id, syndicate, rid from status where id in (".join(",",$spielerids).")", "id");
	#	$naps_sk = assocs("select napid,initiator,partner,type,kstrafe,kfrist,gekuendigt_time from naps_spieler_spezifikation where napid in (".join(",",$napids).")","napid");
	if ($action == "pab")	{
		unset ($ausgabe);
		if ($naps_sk[$abkommen])	{
			if (!$ia)	{
				if ($what == "decline"): $option = "ablehnen";
				elseif ($what == "accept"): $option = "annehmen";
				elseif ($what == "takeback"): $option = "zurücknehmen";
				elseif ($what == "cancel"): $option = "kündigen";
				else: $nap_barrier = 1; endif;
				if (!$nap_barrier)	{
					$ausgabe .= "<br><br><br><center>Möchten Sie dieses Abkommen wirklich $option?<br><br><a href=politik.php?action=pab&ia=finish&what=$what&abkommen=$abkommen class=linkAufsiteBg>Bestätigen</a> - <a href=politik.php class=linkAufsiteBg>Abbrechen</a>";
				} else { f("Ungültige Option gewählt.$zurueck"); }
			}
			elseif ($ia == "finish")	{
				if ($what == "decline"): 	$option = "abgelehnt.";
					if (!$napdata[$abkommen][type] && $naps_sk[$abkommen][partner] == $id)	{
						$queries[] = "update naps_spieler_spezifikation set gekuendigt_von=$id where napid=$abkommen";
						$queries[] = "delete from naps_spieler where napid=$abkommen";
						$queries[] = "insert into message_values (id, user_id, time, werte) values (31, ".$napdata[$abkommen][nappartner].",$time,'".$status[rulername]." von ".$status[syndicate]." (#".$status[rid].")')";
					} else { $error = 1; }
				elseif ($what == "accept"): 	$option = "angenommen.";
					if (!$napdata[$abkommen][type] && $naps_sk[$abkommen][partner] == $id)	{
						$queries[] = "update naps_spieler set type=".$naps_sk[$abkommen][type]." where napid=$abkommen";
						$queries[] = "insert into message_values (id, user_id, time, werte) values (32, ".$napdata[$abkommen][nappartner].",$time,'".$status[rulername]." von ".$status[syndicate]." (#".$status[rid].")')";
					} else { $error = 1; }
				elseif ($what == "takeback"): $option = "zurückgenommen.";
					if (!$napdata[$abkommen][type] && $naps_sk[$abkommen][initiator] == $id)	{
						$queries[] = "update naps_spieler_spezifikation set gekuendigt_von=$id where napid=$abkommen";
						$queries[] = "delete from naps_spieler where napid=$abkommen";
						$queries[] = "insert into message_values (id, user_id, time, werte) values (33, ".$napdata[$abkommen][nappartner].",$time,'".$status[rulername]." von ".$status[syndicate]." (#".$status[rid].")')";
					} else { $error = 1; }
				elseif ($what == "cancel"): $option = "gekündigt. Die Kündigung wird in ".$naps_sk[$abkommen][kfrist]."h zum Stundenende ausgeführt. Die Kündigungsstrafe in Höhe von ".pointit($naps_sk[$abkommen][kstrafe])." Cr wurde Ihnen von Ihrem Guthaben abgezogen.";
					if ($napdata[$abkommen][type] && !$naps_sk[$abkommen][gekuendigt_time])	{
						if ($status[money] >= $naps_sk[$abkommen][kstrafe])	{
							$status[money] -= $naps_sk[$abkommen][kstrafe];
							$queries[] = "update naps_spieler_spezifikation set gekuendigt_von=$id, gekuendigt_time=".(get_hour_time($time)+(1+$naps_sk[$abkommen][kfrist])*3600)." where napid=$abkommen";
							$queries[] = "insert into message_values (id, user_id, time, werte) values (34, ".$napdata[$abkommen][nappartner].",$time,'".$status[rulername]." von ".$status[syndicate]." (#".$status[rid].")|".pointit($naps_sk[$abkommen][kstrafe])."')";
							$queries[] = "update status set money=money-".$naps_sk[$abkommen][kstrafe]." where id=$id";
						} else { $error = 3; };
					} else { $error = 2; }
				else: $nap_barrier = 1; endif;
				if (!$nap_barrier && !$error)	{
					s("Sie haben das Abkommen erfolgreich $option$weiter");
				} elseif ($nap_barrier) { f("Ungültige Option gewählt.$zurueck"); } elseif ($error == 1) { f("Es ist ein Fehler aufgetreten. Sie können diese Aktion nicht ausführen.$zurueck"); } elseif ($error == 2) { f("Dieses Abkommen wurde bereits gekündigt!$zurueck"); } elseif ($error == 3) { f("Sie haben nicht die erforderlichen Credits, um die Kündigungsstrafe zu bezahlen!$zurueck");}
			}
		} else { f("Ungültiges Abkommen gewählt.$zurueck"); }
	}
*/

/* Schon vor Runde 25 irgenwann deaktiviert ^^
	//
	//// Gvi beitreten / verlassen
	//
	if (FALSE && $action == "gvi") {
		unset ($ausgabe);

		if ($ia == "changestatus") {
			if ($status[gvi]) {
				s("Sie sind erfolgreich aus der GVI ausgetreten und müssen keine Mitgliedsbeiträge mehr entrichten.");
				$status[gvi] = 0;
				$queries[] = "update status set gvi=0 where id=$status[id]";
			}
			elseif($status[gvi] == 0) {
				// Angriffe ausgeführt ?
				$comparetime = $time - (60*60*24);
				$attacksdone = single("select aid from attacklogs where aid=$status[id] and time >= $comparetime limit 1");

				if ($attacksdone) {
					f("Sie können der GVI erst beitreten, wenn Sie 24 Stunden lang keine Angriffe durchgeführt haben.");
				}
				elseif($status[land] > 2000) {
					f("Sie dürfen höchsten 2000 Hektar Land besitzen, wenn Sie der GVI beitreten wollen.");
				}
				else {
					s("Sie sind der GVI erfolgreich beigetreten. Ihre Mitgliedschaft wird beendet, wenn Sie mehr als 2000 Hektar Land erreichen oder ihren Mitgliedsbeitrag nicht bezahlen können.");
					$status[gvi] = 1;
					$queries[] = "update status set gvi=1 where id=$status[id]";
				}
			}
		}

/*		Die "Globale Verteidigungs-Initiative" (kurz GVI) genannt gewährt jedem Mitglied Schutz vor Angriffen/Spionageaktionen von Spielern außerhalb einer Grenze von 66% - 150% des eigenen Networths. Dieser Schutz kostet pro Stunde das selbe wie 3 Einheiten Land (Landpreis * 3). Sobald man die Kosten nicht bezahlen kann, wird man aus der Initiative ausgeschlossen (man kann natürlich wieder eintreten, wenn man wieder die nötigen Cr für deren Unterhalt hat).
Jeder Spieler ist anfangs automatisch Mitglied der GVI (Neulinge kennen sich bekanntlich nicht so gut aus, so wird sichergestellt, dass diese vom Schutz profitieren), allerdings fallen während der Schutzzeit keine Unterhaltskosten an. Die Zahlungen beginnen erst nach Ablauf der Schutzzeit.
Die GVI steht ferner nur für Spieler unter 2000 Land zur Verfügung. Erreicht ein Spieler die 2000-Landgrenze, so verlässt er die GVI automatisch und kann erst wieder eintreten, sobald er weniger als 2000 Land hat.
Um in die GVI eintreten zu können, darf man in den letzten 24h niemanden angegriffen haben.*-/
		$gviinfostring = "
		Als Mitglieder der globalen Verteidigungsinitiative können Sie:
		<ul>
		<li>Nicht von Mitspielern angegriffen/ausspioniert werden, die mehr als 150% ihres Networths oder weniger als 66% Ihres Networths besitzen.
		<li>Spieler, die mehr als 150% ihres Networths oder weniger als 66% ihres Networths besitzen weder angreifen noch ausspionieren.
		</ul>
		<br>
		Dieser Schutz gilt nicht während eines Krieges (zwischen den Spielern innerhalb der sich im Krieg befindenden Syndikate), bzw. wenn jemand von Ihnen innerhalb der letzten 24h angegriffen wurde oder Sie innerhalb der letzten 24h von jemand anderem angegriffen wurden (jeweils zwischen Ihnen und dem anderen Spieler).
		<br><br>
		Der Mitgliedsbeitrag der GVI beläuft sich für Sie auf momentan: <b>".(ceil(3*landkosten()))." Credits pro Stunde</b> (Landpreis * 3). Je größer Ihr Konzern wird, desto höher wird also der Mitgliedsbeitrag.<br>
		Konzerne, die den Mitgliedsbeitrag nicht bezahlen können oder mehr als 2000 Hektar Land besitzen, werden automatisch aus der GVI ausgeschlossen.<br>
		Während der Schutzphase ist die Mitgliedschaft in der GVI kostenlos.
		";

		// Austreten
		if ($status[gvi]) {
			$ausgabe.="<br><br>
			Status: <b>Sie sind momentan Mitglied der globalen Verteidigungsinitiative.</b><br><br>
			$gviinfostring<br><br>
			$yellowdot <a class=\"linkaufTableInner\" href=\"politik.php?action=gvi&ia=changestatus\">Ja, ich will aus der GVI austreten</a>
			";
		}
		// Eintreten
		else {
			$ausgabe.="<br><br>
			Status: <b>Sie sind kein Mitglied der globalen Verteidigungsinitiative!</b><br><br>
			$gviinfostring<br>
			Um der globalen Verteidigungsinitiative beitreten zu können, dürfen Sie während der letzten 24 Stunden keinen Angriff ausgeführt haben und müssen weniger als 2000 Hektar Land besitzen.
			<br><br>

			$yellowdot <a class=\"linkaufTableInner\" href=\"politik.php?action=gvi&ia=changestatus\">Ja, ich will Mitglied der GVI werden</a>
			";
		}
	}
*/

//							Daten schreiben									//

	db_write($queries);
	db_write($queriesend,1); # Für queries die auch nach Rundende ausgeführt werden




//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('ISKING', $isking);
$tpl->assign('STATUS', $status);
$tpl->assign('RIPF', $ripf);
$tpl->assign('YELLOWDOT', $yellowdot);
$tpl->assign('ACTION', $action);
	
require_once("../../inc/ingame/header.php");
if (!$showPolitik && ($fehler || $successmeldung || $informationmeldung))
	$tpl->assign('ACTION', false);
$tpl->display('politik.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

// GETSYNDNAME - in subs.php verschoben - R4bbiT - 03.04.11

/*function kriegspraemie($wd) {
	$resstats = getresstats();
	global $queries, $time, $status, $sname, $globals, $artefakte;

	$spyaction_settings = assocs("select * from spyaction_settings", "action_key");
	$enemyids = array();
	if ($wd['gegner1']): $enemyids[] = $wd['gegner1']; $sortable_landwon[1] = $wd['lwt_enemy1']; endif;
	if ($wd['gegner2']): $enemyids[] = $wd['gegner2']; $sortable_landwon[2] = $wd['lwt_enemy2'];endif;
	if ($wd['gegner3']): $enemyids[] = $wd['gegner3']; $sortable_landwon[3] = $wd['lwt_enemy3'];endif;
	arsort($sortable_landwon);

	$ownids = array();
	if ($wd['own1']): $ownids[] = $wd['own1']; $sortable_landwon_own[1] = $wd['lwt_own1']; endif;
	if ($wd['own2']): $ownids[] = $wd['own2']; $sortable_landwon_own[2] = $wd['lwt_own2'];endif;
	if ($wd['own3']): $ownids[] = $wd['own3']; $sortable_landwon_own[3] = $wd['lwt_own3'];endif;
	$ownmemberids = singles("select id from status where rid in (".join(",", $ownids).")");

	if (TRUE) {
		foreach ($ownids as $vl) {
			$artefakt_id = single("select artefakt_id from syndikate where synd_id = $vl");
			$monumente_available[$artefakt_id] = 1;
		}

		// Man selbst verliert als Verliererpartei alle Monumente
		$queries[] = "update syndikate set artefakt_id = 0 where synd_id in (".join(",", $ownids).")";
	}
	foreach ($enemyids as $vl) {
		$artefakte_enemy[$vl] = single("select artefakt_id from syndikate where synd_id = $vl");
	}


	// Für Monumente %-Eroberungsbedingung (6% für Monueroberung, Stand: Runde 33)

	$landstart_own = 0;
	//$landstart_enemy = 0; // wird hier nicht benötigt
	for ($i = 1; $i <= 3; $i++) {
		$landstart_own += $wd['lst_own'.$i];
		//$landstart_enemy += $wd['lst_enemy'.$i];
	}
	
	// Monumente handlen
	foreach ($sortable_landwon as $i => $trash) {
		if (!$artefakte_enemy[$wd['gegner'.$i]] && // Syndikat darf noch kein Monument besitzen
			$wd['artefakt_want_enemy_'.$i] &&	// Syndikat muss ein Monument gezielt gewählt haben, um auch eins zu bekommen
			$monumente_available[$wd['artefakt_want_enemy_'.$i]] && // Das Monument muss im Besitz der Verlierer sein (zusätzliche Sicherheitsmaßnahme)
			((array_sum($sortable_landwon)) / ($landstart_own > 0 ? $landstart_own : 1 ) >= KRIEG_MONU_EROBERUNG_MINDESTPROZENT_LAND_EROBERT / 100)
		)
		{
			$monument_verteilt[$i] = $wd['artefakt_want_enemy_'.$i]; // Zwischenspeicher, um nachher drauf zugreifen zu können
			$monumente_available[$wd['artefakt_want_enemy_'.$i]] = 0; // Monument für etwaige andere Syndikate, die das gleiche gewählt haben ausschließen
		}
	}

	// Monumente zerstören (um Ausgabe in der Message an die Spieler zu erzeugen)
	if ($monumente_available) {
		foreach ($monumente_available as $monument_id => $boolean_availability) {
			if ($boolean_availability == 1) {
				$destroy_monuments[] = $monument_id;
				$destroy_monuments_ausgabe[] = $artefakte[$monument_id]['name'];
			}
		}

		$destroy_monuments_ausgabe = join(", ", $destroy_monuments_ausgabe);
	}

	for ($i = 1; $i <= 3; $i++) {
		$total_synd_land = 0;
		$synd_id = $wd['gegner'.$i];
		$artefakt_get = 0;
		if ($synd_id) {
			$landwon = $wd['lwt_enemy'.$i];
			$praemie = floor(KRIEGSPRAEMIE_TAGFAKTOR * (get_day_time($time) - get_day_time($globals[roundstarttime]) + 24 * 3600) / 24 / 3600 * ($landwon + array_sum($sortable_landwon_own) / count($enemyids)));
			$moneyplus = floor($praemie * 0.50);
			$energyplus = 0;
			$metalplus = floor($praemie * 0.25 / $resstats[metal][value]);
			$sciencepointsplus = floor($praemie * 0.25 / $resstats[sciencepoints][value]);
			$currency = single("select currency from syndikate where synd_id=$synd_id");
			$otherdata = assocs("select id, land from status where rid='$synd_id' and alive = 1 and ((createtime+".PROTECTIONTIME.")<$time)", "id");

			$enemymemberids = array();
			foreach ($otherdata as $ky => $vl) {
				$enemymemberids[] = $ky;
			}
			if (!$enemymemberids): $enemymemberids = array("0"); endif;
			$landgaindata = assocs("select aid, sum(landgain) as landgain from attacklogs where winner='a' and time >= ".$wd['starttime']." and warattack = 1 and arid = $synd_id and drid in (".join(",", $ownids).") group by aid", "aid");
			$spyopsdata = assocs("select aid, action, count(*) as anzahl from spylogs where time >= ".$wd['starttime']." and aid in (".join(",", $enemymemberids).") and did in (".join(",", $ownmemberids).") group by aid, action");
			$punktekonto = array();
			foreach ($spyopsdata as $vl) {
				if ($spyaction_settings[$vl[action]][difficulty] == "easy"): $punktevalue = 1;
				elseif ($spyaction_settings[$vl[action]][difficulty] == "medium"): $punktevalue = 2;
				elseif ($spyaction_settings[$vl[action]][difficulty] == "hard"): $punktevalue = 3;
				elseif ($spyaction_settings[$vl[action]][difficulty] == "veryhard"): $punktevalue = 4;
				endif;
				$punktekonto[$vl[aid]] += $punktevalue * $vl[anzahl];
			}
			$totalspypunkte = array_sum($punktekonto);
			if (!$totalspypunkte): $totalspypunkte = 1; endif;

			if ($otherdata) {
				foreach ($otherdata as $vl) {
					$total_synd_land += $vl[land];
				}
				foreach ($otherdata as $ky => $vl) {
					$pointsplus[$ky] = floor($vl[land] / $total_synd_land * $praemie * 0.25);	# Nur noch 25% der Prämie wird so ausgeschüttet;
					if ($landgaindata[$ky][landgain] > $landwon): $landgaindata[$ky][landgain] = $landwon; endif;	# Sicherheitsmaßnahme gegen unendlich viel Geld
					$pointsplus[$ky] += floor($landgaindata[$ky][landgain] * 2 / $landwon * $praemie * 0.5);	# 50% der Prämie nach der Angriffsbeteiligung im Krieg; * 2 weil in Attacklogs nur die Hälfte eingetragen wird;
					if ($punktekonto[$ky] > $totalspypunkte): $punktekonto[$ky] = $totalspypunkte; endif;	# Sicherheitsmaßnahme gegen unendlich viel Geld
					$pointsplus[$ky] += floor($punktekonto[$ky] / $totalspypunkte * $praemie * 0.25); # 25% der Prämie nach der Spionagebeteiligung im Krieg
				}
				foreach ($otherdata as $ky => $vl)	{
					$queries[] = "update status set podpoints=podpoints+".$pointsplus[$ky]." where id=$ky";
					$messagedata[0] = $sname;
					$messagedata[1] = $status[rid];
					$messagedata[2] = pointit($landwon);
					$messagedata[3] = pointit($moneyplus);
					$messagedata[4] = pointit($energyplus);
					$messagedata[5] = pointit($metalplus);
					$messagedata[6] = pointit($sciencepointsplus);
					$messagedata[7] = pointit($pointsplus[$ky]);
					$messagedata[8] = $currency;
					$messagedata[9] = ($monument_verteilt[$i] ? "<br><br>Ihr Syndikat hat außerdem das Monument <b>".$artefakte[$monument_verteilt[$i]]['name']."</b> vom Gegner <b>erobert</b>.":"").($destroy_monuments_ausgabe ? "<br>".(count($destroy_monuments) > 1 ? "Die Monumente ":"Das Monument ")."<b>".$destroy_monuments_ausgabe."</b> wurde".(count($destroy_monuments) > 1 ? "n":"")." <b>zerstört</b>.":"");
					$messagestring = join("|", $messagedata);
					$insertintomessages .= "('43', '$ky', '$time', '$messagestring'),";
				}
			}
			$warupdatestring .= ",praemie_money_$i=$moneyplus,praemie_metal_$i=$metalplus,praemie_energy_$i=$energyplus,praemie_sciencepoints_$i=$sciencepointsplus";
			$queries[] = "update syndikate set podmetal=podmetal+$metalplus, podmoney=podmoney+$moneyplus, podenergy=podenergy+$energyplus, podsciencepoints=podsciencepoints+$sciencepointsplus where synd_id=$synd_id";
			if ($monument_verteilt[$i]) $queries[] = "update syndikate set artefakt_id =".$monument_verteilt[$i]." where synd_id = '$synd_id'";
		}
	}
	if ($insertintomessages):
		$insertintomessages = chopp($insertintomessages);
		$queries[] = "insert into message_values (id, user_id, time, werte) values $insertintomessages";
	endif;
	return $warupdatestring;
}
*/


// CHECK_FOR_RESTRICTIONS

#1 - erster Kriegsgegner
#2 - zweites Kriegsgegner
#3 - dritter Kriegsgegner
#4 - erster Verbündeter
#5 - zweiter Verbündeter


function check_for_restrictions($eid,$edata)	{
	global $status; global $ally1; global $ally2;
	global $nwdr, $time;
	$otn = 0;
	$etn = 0;
	$q_tn = 0;
	$valid = 1;
	global $ausgabe;
	$own = "(".$status[rid].($ally1 ? ",".$ally1:"").($ally2 ? ",".$ally2:"").")";
	$emy = "(".$eid.($edata[3] ? ",".$edata[3]:"").($edata[4] ? ",".$edata[4]:"").")";
	list($endtime, $duration, $wiedererklarzeit) =	row("select endtime,(endtime-starttime),endtime+7*24*60*60 as laengste from wars where ((first_synd_1 in $own or first_synd_2 in $own or first_synd_3 in $own) and (second_synd_1 in $emy or second_synd_2 in $emy or second_synd_3 in $emy)) or ((first_synd_1 in $emy or first_synd_2 in $emy or first_synd_3 in $emy) and (second_synd_1 in $own or second_synd_2 in $own or second_synd_3 in $own)) order by laengste desc limit 1");
	if ($duration) {
		$days = floor ( $duration / (24 * 60 * 60) );
		$hours = floor ( ($duration - $days * 24 * 60 * 60) / (60 * 60) );
		$minutes = floor ( ($duration - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60 );
		if ($time < $endtime + $duration): $valid = 0; $date1 = date("d. M, H:i:00", $endtime); $date2 = date("d. M, H:i:00", $wiedererklarzeit); $nwdr = "Zwischen mindestens einer Partei aus Ihrer Allianz und der gegnerischen Allianz (wenn keine Allianz besteht, dann ist damit Ihr Syndikat gemeint) ist erst kürzlich ein Krieg geführt worden, der insgesamt ".$days."d, ".$hours."h, ".$minutes."m gedauert hat. Dieser Krieg endete am $date1 Uhr. Da zwischen Kriegsende und einer erneuten Kriegserklärung mindestens soviel Zeit verstreichen muss, wie der Krieg dauerte, können Sie erst wieder am $date2 Uhr diesem Syndikat den Krieg erklären."; endif;
	}
	#$enemy = getsyndname($enemyid,",president_id,allianz_id,ally1,ally2");
	/*
	$top10 = assocs("SELECT sum(nw) as totalnw,`rid` FROM status,syndikate where syndikate.synd_id = status.rid and alive > 0 group by 'rid' ORDER BY totalnw DESC LIMIT 10");
	foreach ($top10 as $ky => $vl)	{
		if (($vl[rid] == $status[rid] && ($ally1 or $ally2 )) or $vl[rid] == $ally1 or $vl[rid] == $ally2):	$valid = 0;
																					$nwdr = "Ihr Syndikat oder einer Ihrer Allianpartner ist ein Top10-Syndikat (Networth) und kann daher keinen Krieg erklären, solange er in der Top10 der Syndikate (nach Networth) ist und mit einem anderen Syndikat alliiert ist!";
																					break; endif;
	}
	*/

	// Erstmal gucken wieviele Kriege der Gegner bereits führt und wie das NW-Verhältnis darin aussieht

	$wardata = assocs("select first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3, status from ". WARTABLE ." where (first_synd_1=".$eid." or first_synd_2=".$eid." or first_synd_3=".$eid." or second_synd_1=".$eid." or second_synd_2=".$eid." or second_synd_3=".$eid.") and status=1");
	foreach ($wardata as $vl)	{
		if ($vl[status]):
			$activewars++;
			if ($activewars < 20000):
				if ($vl[first_synd_1] == $eid or $vl[first_synd_2] == $eid or $vl[first_synd_3] == $eid):
					$kriegsdaten = array("gegner1" => $vl[second_synd_1], "gegner2" => $vl[second_synd_2], "gegner3" => $vl[second_synd_3], "own1" => $vl[first_synd_1], "own2" => $vl[first_synd_2], "own3" => $vl[first_synd_3]);
				else:
					$kriegsdaten = array("gegner1" => $vl[first_synd_1], "gegner2" => $vl[first_synd_2], "gegner3" => $vl[first_synd_3], "own1" => $vl[second_synd_1], "own2" => $vl[second_synd_2], "own3" => $vl[second_synd_3]);
				endif;
			endif;
		endif;
	}
	if ($activewars < 20000) {
		list ($otn, $oan, $oal) = row("select sum(nw), sum(nw)/count(*), sum(land)/count(*) from status where rid in (".$status[rid].($ally1 ? ",".$ally1:"").($ally2 ? ",".$ally2:"").")");
		list ($etn, $ean, $eal) = row("select sum(nw), sum(nw)/count(*), sum(land)/count(*) from status where rid in (".$eid.($edata[3] ? ",".$edata[3]:"").($edata[4] ? ",".$edata[4]:"").")");
		global $startland;
		$startland[first] = assocs("select sum(land) as tl, rid from status where rid in (".$status[rid].($ally1 ? ",".$ally1:"").($ally2 ? ",".$ally2:"").") group by rid", "rid");
		$startland[second] = assocs("select sum(land) as tl, rid from status where rid in (".$eid.($edata[3] ? ",".$edata[3]:"").($edata[4] ? ",".$edata[4]:"").") group by rid", "rid");
		/*
		global $id, $bogulausgabe;
		if ($id == 2912) {
		$bogulausgabe .= "otn: $otn<br>oan: $oan<br><br>etn: $etn<br>ean: $ean<br><br>";
		}*/

		/*
		if ($activewars == 1 && $valid) {
			$eetn = single("select sum(nw) from status where rid in (".$kriegsdaten[gegner1].($kriegsdaten[gegner2] ? ",".$kriegsdaten[gegner2]:"").($kriegsdaten[gegner3] ? ",".$kriegsdaten[gegner3]:"").")");
			if ($eetn > $etn && $valid): $valid = 0; $nwdr = "Dieses Syndikat befindet sich bereits in einem Krieg und ist dabei die schwächere Partei (d.h. der Gesamtnetworth dieses Syndikats [und seiner Verbündeten] liegt unter dem Gesamtnetworth des Kriegsgegners [und dessen Verbündeten])."; endif;
			if ($otn > $etn && $valid): $valid = 0; $nwdr = "Dieses Syndikat befindet sich bereits in einem Krieg und Ihr Syndikat [zusammen mit Ihren Verbündeten] hat einen größeren Gesamtnetworth als dieses Syndikat [und seine Verbündeten]."; endif;
			if ($etn / ($eetn + $otn) < MAX_NETWORTH_DIFFERENCE && $valid): $valid = 0; $nwdr = "Dieses Syndikat befindet sich bereits in einem Krieg und hätte nach einem weiteren Krieg mit Ihrem Syndiakt [und Ihren Verbündeten] einen Gesamtnetworth von weniger als ".(MAX_NETWORTH_DIFFERENCE*100)."% seiner Kriegsgegner."; endif;
		}
		if (!$ally1 and !$ally2 and ($edata[3] or $edata[4]) and $otn > $etn && $valid): $valid = 0; $nwdr = "Als alleinstehendes Syndikat ohne Verbündete können Sie einem Syndikat <strong>mit</strong> Verbündeten nur dann Krieg erklären, wenn der Gesamtnetworth Ihres Syndikats kleiner als der Ihrer Gegner ist."; endif;
		*/
		if ($oal < 1000 or $eal < 1000 && $valid):  $valid = 0; $nwdr = "Um einem Syndikat Krieg erklären zu können, bzw. damit ein Syndikat Krieg erklärt bekommen kann, muss jeder Spieler im Schnitt 1.000 ha Land besitzen.";
		elseif ($etn / $otn < MAX_NETWORTH_DIFFERENCE && $valid): $valid = 0; $nwdr = "Dieses Syndikat hat [mit seinen Verbündeten] einen Gesamtnetworth von weniger als ".(MAX_NETWORTH_DIFFERENCE*100)."% Ihres Gesamtnetworth [zusammen mit Ihren Verbündeten].";
		elseif ($ean / $oan < MAX_NETWORTH_DIFFERENCE && $valid):
			$valid = 0; $nwdr = "Dieses Syndikat hat [mit seinen Verbündeten] einen Durchschnittsnetworth (Gesamtnetworth geteilt durch Anzahl der Spieler) von weniger als ".(MAX_NETWORTH_DIFFERENCE*100)."% Ihres Durchschnittsnetworths [zusammen mit Ihren Verbündeten].";

			$syn_ids = array();
			$syn_ids[] = $eid;
			if ($edata[3]) $syn_ids[] = $edata[3];
			if ($edata[4]) $syn_ids[] = $edata[4];
			if (count($syn_ids) >= 2) {
				$ally_total_nws_by_syn_id = assocs("select sum(nw) as nw, rid from status where rid in (".join(",", $syn_ids).") group by rid", "rid");
				foreach ($syn_ids as $vl) {
					for ($i = 0; $i <= count($syn_ids); $i++) {
						$temp = $ally_total_nws_by_syn_id[$vl]['nw'] / $ally_total_nws_by_syn_id[$i]['nw'];
						if ($temp < 0.5 or $temp > 2) {
							$valid = 1; $nwdr = "";
						}
					}
				}
			}

		endif;


	} else { $valid = 0; $nwdr = "Dieses Syndikat führt bereits zwei verschiedene Kriege. Es können immer nur zwei Kriege gleichzeitig geführt werden."; }

	return $valid;
}


?>
