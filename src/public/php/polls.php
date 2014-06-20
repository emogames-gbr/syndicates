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

require_once("../../inc/ingame/game.php");

$poll_id = floor($poll_id);
//$is_president = 1;

// um auch gruppen-polls zu untestützen
$user_rid = $status[rid];

if( $globals['roundstatus'] == 0)
{
	$user_id = single("SELECT id FROM users WHERE konzernid = ".$status['id']);
	list($gruppennummer, $groupcheffe) = row("select group_id, admin_id from groups_new WHERE group_id = (SELECT group_ID FROM groups_new_members WHERE user_id = ".$user_id.")");
	
	$user_rid = POLL_ID_OFFSET_GRUPPEN + $gruppennummer;
	if( $groupcheffe == $user_id)
	{
		$is_president = 1;
	}
}
else
{
	$is_president = is_president($status);
}

$game_syndikat[ally1] = (int) $game_syndikat[ally1];
$game_syndikat[ally2] = (int) $game_syndikat[ally2];
$umfragen = assocs("select * from polls where (synd_id=$user_rid || synd_id=0 || ally1=$user_rid  || ally2 = $user_rid ) and deleted=0 order by time desc");
$admin_userids = array(1,2, 55910, 77428, 10898);
// 1 - Bogul, 2 - Scytale, 55910 - inok1989, 77428 - Jonny25k, 10898 - Misuke 
$uid= single("select id from users where konzernid=$status[id]");
if (in_array($uid,$admin_userids)) {$isadmin = true;$username =single("select username from users where id=$uid");} else {$isadmin=false;}

$tpl->assign('IS_ADMIN', $isadmin);
$tpl->assign('GLOBAL_POLL', $_POST['global_poll']);

if($_POST['global_poll'] != 'on' &&  ($view == 'create2' || $action == "create")){
	$isadmin = false;
}

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//



//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

//							Berechnungen									//

//*********************************
//			Action Vote
//*********************************

if ($action == "vote") {
	$poll_id = (int) $pollid;
	if ($poll_id) {
		$poll = assoc("select * from polls where poll_id=$poll_id and deleted=0");
		if ($poll[synd_id] == $user_rid || $poll[synd_id] == 0 || $user_rid == $poll[ally1] || $user_rid == $poll[ally2] ) {
			$voted = single("select user_id from users_votes where poll_id=$poll_id and user_id=$status[id]");
			if (!$voted) {
				select("update polls set votes_total=votes_total+1 where poll_id=$poll_id");
				if(!$option_id)
					select("insert into users_votes (user_id,time,poll_id,option_id) values ($status[id],$time,$poll_id,0)");
				else{
					foreach($option_id as $op_id){
						$op_id = (int) $op_id;
						select("insert into users_votes (user_id,time,poll_id,option_id)
								values
								($status[id],$time,$poll_id,$op_id)
						");
						select("update polls_options set votes=votes+1 where option_id=$op_id");
					}
				}
				$beschr = "Ihre Stimme wurde gezählt";
				$tpl->assign("MSG", $beschr);
				$view="poll";
			}
			else {
			$errormsg = "Sie haben Ihre Stimme zu dieser Umfrage bereits abgegeben.";
			$tpl->assign('ERROR', $errormsg);
			}
		}
		else {
		$errormsg = "Sie können sich nicht an Umfragen anderer Syndikate beteiligen!";
		$tpl->assign('ERROR', $errormsg);
		}
	}
	else {
	$errormsg = "Umfrage nicht gefunden";
	$tpl->assign('ERROR', $errormsg);
	}
}

//*********************************
//			Action == delete
//*********************************

if ($action == "delete") {
	$poll_id=(int) $poll_id;
	$poll = assoc("select * from polls where poll_id=$poll_id");
	if ($isadmin) {$insertpollrid = 0;$insertname=$username;}
	else {$insertpollrid = $user_rid;$insertname=$status[syndicate];}
	if ($poll[synd_id] == $insertpollrid) {
		select("update polls set deleted=1 where poll_id=$poll_id");
		$beschr = "Umfrage erfolgreich gelöscht!";
		$tpl->assign("MSG", $beschr);
		$umfragen = assocs("select * from polls where synd_id=$insertpollrid and deleted=0 order by time desc");
	}
	else {
	$errormsg = "Sie können nur Umfragen ihres Syndikates löschen";
	$tpl->assign('ERROR', $errormsg);
	}
}

//*********************************
//			Action Create
//*********************************

if ($action == "create") {
	if ($is_president || $isadmin) {
		$number = (int) $number;
		$dauer = (int) $dauer;
		if ($dauer <1) {$dauer=1;
			$infomsg = "Ihre Umfrage muß mindestens 1 Tag lang gültig sein.";
			$tpl->assign('INFO', $infomsg);
		}
		elseif($dauer>30) {$dauer=30;
			$infomsg = "Ihre Umfrage darf höchstens 30 Tage lang gültig sein.";
			$tpl->assign('INFO', $infomsg);
		}
		if ($number <=0 && strlen($name) > 0) {
		$errormsg = "Keine Antworten oder keine Umfrage angegeben!";
		$tpl->assign('ERROR', $errormsg);
		}
		else {
			$name = htmlentities($name,ENT_QUOTES);
			$time_bis = $time+ 60*60*24*$dauer;
			if ($isadmin) {$insertpollrid = 0;$insertname=$username;}
			else {$insertpollrid = $user_rid;$insertname=$status[syndicate];}
			if ($allyok == "on") {
				if ($game_syndikat[ally1] >0) {
					$allyokadd = ",ally1";
					$allyokaddvalues = ",$game_syndikat[ally1]";
				}
				if ($game_syndikat[ally2] >0) {
					$allyokadd .= ",ally2";
					$allyokaddvalues .= ",$game_syndikat[ally2]";
				}
			}
			select("
				insert into polls
				(name,time,user_id,user_name,time_bis,multi,votes_total,synd_id$allyokadd)
				values
				('$name',$time,$status[id],'$insertname',$time_bis,$multi,0,$insertpollrid$allyokaddvalues)
			");
			$poll_id = single("select poll_id from polls where user_id='$status[id]' and time=$time and synd_id=$insertpollrid");
			if ($poll_id) {
				for ($i=1;$i<$number+1;$i++) {
					$temp = "a$i";
					$$temp = htmlentities($$temp,ENT_QUOTES);
					if (strlen($$temp) > 0) {
						select("
							insert into polls_options (poll_id,content,votes)
							values
							($poll_id,'".$$temp."',0)
						");
					}
				}
				$umfragen = assocs("select * from polls where (synd_id=$user_rid || synd_id=0 || ally1=$user_rid  || ally2 = $user_rid ) and deleted=0 order by time desc");
				$beschr = "Umfrage erfolgreich erstellt.";
				$tpl->assign("MSG", $beschr);
			}
			else {
				$errormsg = "Beim Erstellen der Umfrage ist ein Fehler aufgetreten!";
				$tpl->assign('ERROR', $errormsg);
				select("delete from polls where name='$name' and synd_id=$insertpollrid");
				$view = "create2";
			}
		}
	}
	else {
		$errormsg = "Nur der Präsident kann Umfragen erstellen!";
		$tpl->assign('ERROR', $errormsg);
	}
}


//							Daten schreiben									//

//							Ausgabe     									//

//$ausgabe.= kopf("Umfragen","");
//$ausgabe.="<br>";


//*********************************
//			View == delete
//*********************************

if ($view == "delete") {
	$poll_id = (int) $poll_id;
	if ($isadmin) {$insertpollrid = 0;$insertname=$username;}
	else {$insertpollrid = $user_rid;$insertname=$status[syndicate];}
	$poll = assoc("select * from polls where synd_id=$insertpollrid and poll_id=$poll_id");
	if ($is_president || $isadmin) {
		$infomsg = "
			Wollen sie die Umfrage '".$poll[name]."' wirklich löschen ? <br><br>
			<center>
				<a href=\"polls.php?action=delete&poll_id=$poll_id\">Ja</a>
				<br>
				<br>
				<a href=\"polls.php\">Nein</a>
			</center>";
		$tpl->assign('INFO', $infomsg);
	}
	else {
	$errormsg = "Nur der Präsident kann Umfragen löschen";
	$tpl->assign('ERROR', $errormsg);
	}
}

//für tpl layout
$tpl->assign("layout",$layout[images]);
$tpl->assign("yellowdot",$yellowdot);
$tpl->assign("action",$view);
$tpl->assign("ispresidente",($is_president || $isadmin));


//*********************************
//			View == poll
//*********************************


if ($view == "poll") {
	$pollsynd = single("select synd_id from polls where poll_id=$poll_id and deleted=0");
	$poll = assoc("select * from polls where poll_id=$poll_id and deleted=0");
	if ($pollsynd == $user_rid || $pollsynd == 0 || $user_rid == $poll[ally1] || $user_rid == $poll[ally2]) {
		//Daten holen
		$poll_options = assocs("select * from polls_options where poll_id=$poll_id");
		$voted = single("select user_id from users_votes where poll_id=$poll_id and user_id=$status[id]");
		/*$v_total=0;
		foreach ($poll_options as $temp) {
			$v_total += $temp[votes];
		}*/
		$v_total = $poll[votes_total];
		if ($poll[synd_id] == 0) {$adminadd = "<i>(Offizielle Umfrage der Admins)</i>";}
		if ($poll[ally1] > 0 || $poll[ally2] > 0) {$adminadd = "<i>(Allianzumfrage)</i>";}

		$tpl->assign("pollname",$poll[name]);
		$tpl->assign("pollid",$poll_id);
		$tpl->assign("multi",$poll[multi]);
		$tpl->assign("adminadd",$adminadd);		
		$tplPollOptions = array();		
		if (!$voted) {
			$tpl->assign("voted",true); //hier umgekehrt also eigentlich false^^
			$k=0; //einfacher
			foreach ($poll_options as $temp) {
				if (strlen($temp[content]) > 0) {
					$tplPollOptions[$k][0] = $temp[content];
					$tplPollOptions[$k][1] = $temp[option_id];
				}
				$k++;
			}
		}	
		$k=0;
		foreach ($poll_options as $temp) {
			if ($v_total > 0) {
				$tprozent = ($temp[votes]/$v_total);
				$tprozent_int = ($temp[votes]/$v_total);
			}
			else {
				$tprozent = "0.00";
				$tprozent_int = 0;
			}
			$tplPollOptions[$k][2] = $temp[content];
			$tplPollOptions[$k][3] = ($tprozent_int*100+1);
			$tplPollOptions[$k][4] = $temp[votes];
			$tplPollOptions[$k][5] = (prozent($tprozent*100));
			$k++;
		}

		if ($is_president || $isadmin) {
			$tpl->assign("pollid",$poll[poll_id]);
		}
		$tpl->assign("polloptions",$tplPollOptions);
	}
	else {
		$errormsg = "Sie können nur Umfragen, die zu Ihrem Syndikat gehört auswählen";
		$tpl->assign('ERROR', $errormsg);
		$view="";
		$tpl->assign("action",$view);
	}
}

//*********************************
//			View == create
//*********************************

if ($view == "create") {
	if ($is_president || $isadmin) {
		$tpl->assign("name",$name);
		$tpl->assign("number",$number);
	}
	else {
		$errormsg = "Nur der Syndikatspräsident kann Umfragen erstellen!";
		$tpl->assign('ERROR', $errormsg);
		$view = "";
		$tpl->assign("action",$view);
	}
}

//*********************************
//			View == Create2
//*********************************

if ($view == "create2") {
	if ($is_president || $isadmin) {
		$name=htmlentities($name,ENT_QUOTES);
		$number= (int)$number;
		$multi = ($multi == "on" ? 1 : 0);
		if ($number > 20) {
			$infomsg = "Sie können maximal 20 verschiedene Antworten pro Umfrage erstellen.";
			$tpl->assign('INFO', $infomsg);
			$number = 20;
		}
		$tpl->assign("name",$name);
		$tpl->assign("number",$number);
		$tpl->assign("multi",$multi);
		$tplPollOptions = array();
		for ($i=1;$i<$number+1;$i++) {
			$temp = "a$i";
			array_push($tplPollOptions,array($i,$$temp));
		}
		$tpl->assign("polloptions",$tplPollOptions);
		if ($game_syndikat[ally1] > 0 || $game_syndikat[ally2] > 0) {
			$tpl->assign("gotAlly",true);
		}
	}
	else {
		$errormsg = "Nur der Syndikatspräsident kann Umfragen erstellen!";
		$tpl->assign('ERROR', $errormsg);
		$view = "";
		$tpl->assign("action",$view);
	}
}


//*********************************
//			Standardausgabe
//*********************************

if (!$view) {
	$tplPolls = array();
	if (is_array($umfragen) && count($umfragen) > 0) {
		$tplPoll = array();
		foreach ($umfragen as $temp) {
			$tplPoll[0] = $temp[name];
			$tplPoll[1] = $temp[user_name];
			$tplPoll[2] = mytime($temp[time]);
			if ($temp[time_bis] > $time) {
				$tplPoll[3] = mytime($temp[time_bis]);
			}
			$tplPoll[4] = $temp[votes_total];
			$tplPoll[5] = $temp[poll_id];
			if ($temp[time_bis] > $time) {
				$tplPoll[6] = "Zur Umfrage";
			}
			else {
				$tplPoll[6] = "Ergebnisse";
			}
			array_push($tplPolls,$tplPoll);
			unset($tplPoll);
		}
		$tpl->assign("polls", $tplPolls);
	}
}
//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

//Vermeidung von Info Redundanz
if($tpl->get_template_vars('INFO') != ''){
	$storeInfo = $tpl->get_template_vars('INFO');
}
//header
require_once("../../inc/ingame/header.php");

//Infobox
if($tpl->get_template_vars('INFO') != '' && $storeInfo){
	$tpl->assign("INFO",$storeInfo);
	$tpl->display('info.tpl');
}

//Fehler
if($tpl->get_template_vars('ERROR') != ''){
	$tpl->display('fehler.tpl');
}
//Meldung
if($tpl->get_template_vars('MSG') != ''){
	$tpl->display('sys_msg.tpl');
}

$tpl->display('polls.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function is_president($status) {
		global $user_rid;
		$president_id = single("select president_id from syndikate where synd_id = $user_rid");
		if ($president_id == $status[id]) {
			return 1;
		}
		else {
			return 0;
		}
}

?>
