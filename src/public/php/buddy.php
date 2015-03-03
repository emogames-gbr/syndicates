<?

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");
$time = time();
$userId = single("select id from users where konzernid=".$status[id]);
$firstTimeDB = single("select buddy_calc from users where id=$userId");
if($changesyn=="wählen") $action="new";
if($submit=="Spieler auswählen") $action="ok";

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//



//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

//myfirst time
if(!$firstTimeDB && !$firsttime){
	require_once("../../inc/ingame/header.php");
	$infomsg="Sie öffnen die Buddyseite zum ersten Mal. Zur Erfassung potentieller Buddys sind einige Berechnungen nötig. <br>Mit dem klick auf \"Weiter\" werden diese durchgeführt. Bitte beachten Sie, dass der Vorgang bis zu einer Minute dauern kann und das eine Aktualisierung der Seite den Vorgang verlangsamt.<br>
	<a href=\"buddy.php?firsttime=agree\">->Weiter</a>";
	$tpl->assign("INFO",$infomsg);
	$tpl->display('info.tpl');
	require_once("../../inc/ingame/footer.php");
	exit();
	}
elseif(!$firstTimeDB && $firsttime=="agree"){
global $relBreakPoint,$absBreakPoint,$deepBreakPoint, $playerNet, $perRoundQuantifier, $maxQuantifier, $minQuantifier, $normalAttQuantifier;

$relBreakPoint = 0.1;
$absBreakPoint = 2;
$deepBreakPoint = 2;
$maxQuantifier = 1.5;
$minQuantifier = 1;
$perRoundQuantifier = 0.1;
$normalAttQuantifier = 2;
$playerNet = array();


$deep=1;
/*
$data = array();

$data[avg_att1_gain] = ceil(single("select (sum(attack_total_won_normal)+sum(attack_total_won_conquer))/count(*) from stats", "user_id"));
$data[max_att1_gain] = ceil(single("select (sum(attack_total_won_normal)+sum(attack_total_won_conquer))/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));
$data[avg_att2_gain] = ceil(single("select sum(attack_total_won_siege)/count(*) from stats", "user_id"));
$data[max_att2_gain] = ceil(single("select sum(attack_total_won_siege)/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));
$data[avg_att3_gain] = ceil(single("select sum(attack_largest_won_killspies)/count(*) from stats", "user_id"));
$data[max_att3_gain] = ceil(single("select sum(attack_largest_won_killspies)/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));
$data[avg_stolen_gain] = ceil(single("select sum(nettostolen)/count(*) from stats", "user_id"));
$data[max_stolen_gain] = ceil(single("select sum(nettostolen)/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));
$data[avg_ops_done] = ceil(single("select sum(spyopsdonewon)/count(*) from stats", "user_id"));
$data[max_ops_done] = ceil(single("select sum(spyopsdonewon)/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));

$data[avg_att1_gain_this] = ceil(single("select (sum(attack_total_won_normal)+sum(attack_total_won_conquer))/count(*) from stats where user_id=$userId"));
$data[avg_att2_gain_this] = ceil(single("select sum(attack_total_won_siege)/count(*) from stats where user_id=$userId"));
$data[avg_att3_gain_this] = ceil(single("select sum(attack_largest_won_killspies)/count(*) from stats where user_id=$userId"));
$data[avg_stolen_gain_this] = ceil(single("select sum(nettostolen)/count(*) from stats where user_id=$userId"));
$data[avg_ops_done_this] = ceil(single("select sum(spyopsdonewon)/count(*) from stats where user_id=$userId"));

$data[max_att1_gain_this] = ceil(single("select (max(attack_total_won_normal)+max(attack_total_won_conquer)) from stats where user_id=$userId"));
$data[max_att2_gain_this] = ceil(single("select max(attack_total_won_siege) from stats where user_id=$userId"));
$data[max_att3_gain_this] = ceil(single("select max(attack_largest_won_killspies) from stats where user_id=$userId"));
$data[max_stolen_gain_this] = ceil(single("select max(nettostolen) from stats where user_id=$userId"));
$data[max_ops_done_this] = ceil(single("select max(spyopsdonewon) from stats where user_id=$userId"));

$data[avg_rank] = ceil(single("select sum(endrank)/count(*) from stats  where endrank>0", "user_id"));
$data[min_rank] = ceil(single("select sum(endrank)/count(*) as maxgain from stats where endrank>0 group by user_id order by maxgain asc limit 1"));
$data[avg_nw] = ceil(single("select sum(lastnetworth)/count(*) from stats", "user_id"));
$data[max_nw] = ceil(single("select sum(lastnetworth)/count(*) as maxgain from stats group by user_id order by maxgain desc limit 1"));
$data[min_rank_this] = ceil(single("select min(endrank) from stats where user_id=$userId and endrank>0"));
$data[max_nw_this] = ceil(single("select max(lastnetworth) from stats where user_id=$userId"));
$data[avg_rank_this] = ceil(single("select sum(endrank)/count(*) from stats where user_id=$userId and endrank>0"));
$data[avg_nw_this] = ceil(single("select sum(lastnetworth)/count(*) from stats where user_id=$userId"));

$att_skill = min(1, ($data[max_att1_gain_this]/$data[max_att1_gain]+$data[max_att2_gain_this]/$data[max_att2_gain]+$data[max_att3_gain_this]/$data[max_att3_gain])/3);
$att_playstyle = min(1, max( $normalAttQuantifier*$data[avg_att1_gain_this]/$data[max_att1_gain], $data[avg_att2_gain_this]/$data[max_att2_gain], $data[avg_att3_gain_this]/$data[max_att3_gain],0));

$spy_skill = min(1, ($data[max_stolen_gain_this]/$data[max_stolen_gain]+$data[max_ops_done_this]/$data[max_ops_done])/2);
$spy_playstyle = min(1, max($data[avg_stolen_gain_this]/$data[max_stolen_gain],$data[avg_ops_done_this]/$data[max_ops_done]));

$rank_skill = min(1, ($data[max_nw_this]/$data[max_nw]+$data[min_rank]/$data[min_rank_this])/2);
$rank_playstyle = min(1, max($data[avg_nw_this]/$data[max_nw], $data[min_rank]/$data[avg_rank_this]));

//ausgabe
require_once("../../inc/ingame/header.php");
echo "relBreakPoint: $relBreakPoint absBreakPoint: $absBreakPoint deepBreakPoint: $deepBreakPoint <br>";
echo "maxQuantifier: $maxQuantifier minQuantifier: $minQuantifier perRoundQuantifier: $perRoundQuantifier <br>";
echo "normalAttQuantifier: $normalAttQuantifier<br><br>";

echo "<u>skill:</u><br>att:".round($att_skill*100,2)."%<br>spy:".round($spy_skill*100,2)."%<br>rank:".round($rank_skill*100,2)."%<br>";
echo "<br><u>playstyle:</u><br>att:".round($att_playstyle*100,2)."%<br>spy:".round($spy_playstyle*100,2)."%<br>rank:".round($rank_playstyle*100,2)."%<br>";
echo "<br><u>people you like:</u><br>";
*/
getStats($userId,$deep, 1);
foreach($playerNet as $playerId=>$chanceToKnow){
	$is=single("select count(*) from users_buddy where (uid1=$userId and uid2=$playerId) or (uid2=$userId and uid1=$playerId)");
	if($playerId != $userId){
		if(!$is){
			select("INSERT INTO `users_buddy` (`id`, `uid1`, `uid2`, `reaction`, `reactionTime`, `time`, `autoCreate`, `status`, `quantity1`, `quantity2`) VALUES (NULL, '$userId', '$playerId', '0', '0', '$time', '1', '0', '$chanceToKnow', '$chanceToKnow')");
		} else {
			$me = single("select quantity1 from users_buddy where (uid1=$userId and uid2=$playerId)");
			if($me){
				if($chanceToKnow > $me) select("update users_buddy set quantity1='$chanceToKnow' where (uid1=$userId and uid2=$playerId) or (uid2=$userId and uid1=$playerId)");
			} else {
				$me = single("select quantity2 from users_buddy where (uid2=$userId and uid1=$playerId)");
				if($chanceToKnow > $me) select("update users_buddy set quantity2='$chanceToKnow' where (uid1=$userId and uid2=$playerId) or (uid2=$userId and uid1=$playerId)");
			}
		}
	}
}
select("update users set buddy_calc=1 where id=$userId");
}
//jetzt biste entjungfert

require_once("../../inc/ingame/header.php");


$ausgabe = "";
$raceicon=array(	"pbf" => "bf-logo-klein",
					"sl" => "sl-logo-klein",
					"nof" => "nof-logo-klein",
					"uic" => "uic-logo-klein",
					"neb" => "neb-logo-klein");
$sessidsactual = assocs("select user_id, gueltig_bis from sessionids_actual", "user_id");


//http://syndicates.progamer.webnotes.de/php/buddy.php?action=ok&id=442
$errormsg="";
$msg="";
$infomsg="";
$id = mysql_real_escape_string($_GET['id']);
if($action){
	$nume=getBuddyNums();
		if($action=="ok" && $nume['total']<BUDDY_COUNT_MAX){
			if($nume['total']>=BUDDY_COUNT_MAX){ $errormsg="Sie können nicht mehr als 25 Spieler auf ihrer Buddylist haben.";}
			else {
				if (is_numeric($id) && $secret) $id=single("SELECT id FROM users WHERE konzernid = ".$id);
				$sid=single("select konzernid from users where id=$id");
				$possible=single("select count(*) from users where id=$id and konzernid>0");
				if($possible && !($sid == $status[id])){
					$data = assoc("select * from users_buddy where (uid1=$userId and uid2=$id) or (uid2=$userId and uid1=$id)");
					$me = $data['uid1'] == $userId;
					if(!$data){
						$msg = "Der Spieler wurde informiert und kann die Buddyanfrage bestätigen.";
						select("INSERT INTO `users_buddy` (`id`, `uid1`, `uid2`, `reaction`, `reactionTime`, `time`, `autoCreate`, `status`, `quantity1`, `quantity2`) VALUES (NULL, '$userId', '".$id."', '$$userId', '0', '$time', '0', '1', '1', '1')");
						select("INSERT INTO  `message_values` (`id` ,  `user_id` ,  `time` ,  `gelesen` ,  `werte` ) VALUES ('44',  '$sid',  '$time',  '0',  'Ein Spieler hat ihnen eine Buddyanfrage geschickt. Sie können diese auf der <a href=\"buddy.php\">Buddy-Seite</a> einsehen.')");
					} elseif($data['status']==1 && $data['reaction']==1){
						$errormsg= "Der ausgewählte Spieler ist bereits auf ihrere Buddylist.";
					} elseif($data['status']==1 && $data['reaction']!=$userId){
						$msg = "Erfolgreich zur Buddylist hinzugefügt.";
						select("update users_buddy set status=1, reaction=1, reactionTime=$time where id=".$data[id]);
						select("INSERT INTO  `message_values` (`id` ,  `user_id` ,  `time` ,  `gelesen` ,  `werte` ) VALUES ('44',  '$sid',  '$time',  '0',  'Ein Spieler hat ihre Buddyanfrage angenommen.')");
					} elseif($data['status']!= 1){
						$msg = "Der Spieler wurde informiert und kann die Buddyanfrage bestätigen.";
						select("update users_buddy set status=1, reaction=$userId, reactionTime=0 where id=".$data[id]);
						select("INSERT INTO  `message_values` (`id` ,  `user_id` ,  `time` ,  `gelesen` ,  `werte` ) VALUES ('44',  '$sid',  '$time',  '0',  'Ein Spieler hat ihnen eine Buddyanfrage geschickt. Sie können diese auf der <a href=\"buddy.php\">Buddy-Seite</a> einsehen.')");
					}
				} else {
					$errormsg="Aktion nicht möglich.";
				}
			}
		}
	
	
	elseif($action=="kick"){
		//$id
		$data = assoc("select * from users_buddy where (uid1=$userId and uid2=$id) or (uid2=$userId and uid1=$id)");
		$me = $data[uid1] == $userId;
		$sid=single("select konzernid from users where id=$id");
		if(!$data){
			$errormsg = "Aktion nicht möglich.";
		} elseif($data['status']==1 && $data['reaction']==1){
			select("update users_buddy set status=2 where id=".$data[id]);
			$msg = "Der Spieler wurde aus ihrer Buddylist entfernt.";
		} elseif($data['status']==1 && $data['reaction']>1){
			$msg = "Buddyanfrage abgelehnt.";
			select("update users_buddy set status=2 where id=".$data[id]);
			select("INSERT INTO  `message_values` (`id` ,  `user_id` ,  `time` ,  `gelesen` ,  `werte` ) VALUES ('44',  '$sid',  '$time',  '0',  'Ein Spieler hat ihre Buddyanfrage abgelehnt.')");
		} elseif($data['status']==0 && $data['autoCreate']==1){
			$msg = "Spieler wird nicht mehr angezeigt.";
			select("update users_buddy set status=2 where id=".$data[id]);
		}
	} elseif($action=="new"){
		if (!$arid) $arid=$status[rid];
		$nextrid = get_next_rid($arid);
		$lastrid = get_last_rid($arid);
		if($globals[roundstatus]==0) $players = assocs("select users.id as id, syndicate, users.username,race from status, users where rid=$arid and users.konzernid=status.id ORDER BY land");
		else $players = assocs("select users.id as id,syndicate,race,land, users.username from status, users where rid=$arid and users.konzernid=status.id ORDER BY land");

		$infomsg = "<form action=\"buddy.php?action=ok\" method=\"GET\">
				<table cellpadding=\"5\" cellspacing=\"1\" border=\"0\" width=550 class=\"tableOutline\" >
				<tr>
					<td align=center class=\"tableHead\" colspan=2>Buddyanfrage versenden</td>
				</tr>
					<tr>
						<td class=\"tableInner1\" align=left>
								Syndikat wählen: <br>
								<a class=\"linkaufTableInner\" href=\"buddy.php?action=new&arid=$lastrid\"><<</a>
								(#<input name=arid value=$arid size=3>) <input type=submit name=changesyn value=\"wählen\">
								 <a class=\"linkaufTableInner\" href=\"buddy.php?action=new&arid=$nextrid\">>></a>
						</td>
						<td class=\"tableInner1\" align=left>
							Spieler:
							<select name=id>";
									foreach ($players as $value) {
										$infomsg.="<option value=\"$value[id]\">".($value['land'] ? $value['land']."ha - " : "")."$value[syndicate] ($value[username])</option>";
									}
									if (count($players) == 0) {
										$infomsg.="<option value=0>Keine Spieler in diesem Syndikat gefunden</option>";
									}
								$infomsg.="</select>
							<input type=submit name=submit value=\"Spieler auswählen\">
						</td>
					</tr>
				</table>
			</form>";
	}
	
	//Fehler
	if($errormsg != ''){
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	//Meldung
	if($msg != ''){
		$tpl->assign('MSG', $msg);
		$tpl->display('sys_msg.tpl');
	}
	//Info
	if($infomsg){
		$tpl->assign("INFO",$infomsg);
		$tpl->display('info.tpl');
	}
}

$buddyInvites = array(); //

$buddys = array();
$buddysA = array();

$buddysR = getAllBuddy();
foreach($buddysR as $t=>$v){
	$hisSt = assoc("select * from status where id=".$v['sid']);
	$online = $time < $sessidsactual[$v['sid']]["gueltig_bis"]  ? 'online' : 'offline';
	$hisSt['rid'] = $globals['roundstatus']==0 ? ($hisSt ? 999 : 0) : $hisSt['rid'];
	if($v['reaction']==1){
	$buddys[]= array('race' => $hisSt['race'], 'raceicon' => ($hisSt['race'] == 'pbf' ? 'bf' : $hisSt['race'] ).'-logo-klein', 'online' => $online, 'konzname' => $hisSt['syndicate'], 'emonick' => single("select username from users where id=".$v['uid']), 'uid'=>$v['uid'],'reaction'=>$v['reaction']==1, 'sid'=>$hisSt['id'], 'rid'=>$hisSt['rid']);
	} else {
	$buddysA[]= array('race' => $hisSt['race'], 'raceicon' => ($hisSt['race'] == 'pbf' ? 'bf' : $hisSt['race'] ).'-logo-klein', 'online' => $online, 'konzname' => $hisSt['syndicate'], 'emonick' => single("select username from users where id=".$v['uid']), 'uid'=>$v['uid'],'reaction'=>$v['reaction']==1, 'sid'=>$hisSt['id'], 'rid'=>$hisSt['rid']);	
	}
}

/* Buddys nach emoname sortieren */
$buddys = sort2DimString('emonick', $buddys);
 
//$buddys[] = array('race' => 'pbf', 'raceicon' => 'bf-logo-klein', 
//					'online' => 'online', 'konzname' => 'muh', 'emonick' => 'KillerName010');
//$buddys[] = array('race' => 'pbf', 'raceicon' => 'bf-logo-klein', 'online' => 'online', 'konzname' => 'muh', 'emonick' => 'KillerName010');
//$buddys[] = array('race' => 'pbf', 'raceicon' => 'bf-logo-klein', 'online' => 'online', 'konzname' => 'muh', 'emonick' => 'KillerName010');
$tpl->assign('BUDDYS', $buddys);
$tpl->assign('BUDDYS_COUNT', count($buddys));
$tpl->assign('BUDDYS_COUNT_MAX', BUDDY_COUNT_MAX);
$tpl->assign('BUDDYSA', $buddysA);


$knows = getNextFreeBuddy($num=3);
//$knows[] = array('name' => 'GanzTollerName', 'emonick' => 'drölf muhaha', 'rounds' => 13);

$tpl->assign('KNOWS', $knows);
$tpl->assign('ISRANDOMRUNDE', ISRANDOMRUNDE);
$tpl->display('buddy.tpl');

//							Daten schreiben									//

//**************************************************************************//
//								  Footer									//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function getStats($userId,$deep, $factor){
	
	global $relBreakPoint,$absBreakPoint,$deepBreakPoint, $playerNet, $perRoundQuantifier, $maxQuantifier, $minQuantifier;
	
	$ausgabe='';
	if($deep > $deepBreakPoint) return '';
	$userStats = assocs("select * from stats where user_id=".$userId." order by round desc");
	$playersInContactWith = array();
	$timesPlayed=0;
	
	foreach($userStats as $roundData){
		
		$timesPlayed++;
		
		$quantifier = max($minQuantifier,$maxQuantifier-$perRoundQuantifier*$timesPlayed);
		
		$playersWith = assocs("select user_id from stats where round=".$roundData['round']." and rid>0 and rid=".$roundData['rid']);
		
		foreach($playersWith as $playersWithId){
			if(isset($playersInContactWith[$playersWithId[user_id]])) $playersInContactWith[$playersWithId[user_id]] += 1*$quantifier;
			else $playersInContactWith[$playersWithId[user_id]]=1*$quantifier;
			
		}
	}
	
	$orderedByTimes = array();
	for($i=0; $i<=60;$orderedByTimes[++$i]=array());
	
	foreach($playersInContactWith as $playerId=>$timesPlayedWith){
		if($playerId!=$userId){
			$orderedByTimes[ceil($timesPlayedWith)][]=$playerId;
		}
	}
	
	
	for($i=60; $i>=max($absBreakPoint,ceil($timesPlayed*$relBreakPoint)); $i--){
		foreach($orderedByTimes[$i] as $playerId){
			for($k=$deep; $k>1; $k--) $ausgabe .= " -> ";
			$ausgabe .= "Name: ".(single("select username from users where id=".$playerId))." RelTimes:".round(min($i/$timesPlayed, 1)*100*$factor,2)."<br>";
			$ausgabe .= getStats($playerId,$deep+1, min($i/$timesPlayed, 1)*$factor,2);
			if(isset($playerNet[$playerId])) $playerNet[$playerId] = max(round(min($i/$timesPlayed, 1)*100*$factor,2),$playerNet[$playerId]);
			else $playerNet[$playerId] = round(min($i/$timesPlayed, 1)*100*$factor,2); 
		}
	}
	
	return $ausgabe;
}

?>
