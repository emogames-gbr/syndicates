<?php

//Menu

// braucht folgene Variablen:
//	$sessionid :: String
//	$ksession :: String
//	$ENV  :: Asoz. Array

//benutzt folgende Variablen:

$showGroupboard = ($globals['roundstatus'] != 1 || $globals['roundendtime']-BOARD_GRUPPEN_VOR_ENDE_FREISCHALTEN<$time);
if ($showGroupboard) {
	$user_id = single("SELECT id FROM users WHERE konzernid = '".$status['id']."'");
	list($gruppennummer, $groupcheffe) = row("SELECT group_id, (SELECT konzernid FROM users AS u WHERE u.id = g.admin_id) FROM groups_new AS g WHERE group_id = (SELECT group_id FROM groups_new_members WHERE status != 0 AND user_id = '".$user_id."')");
	
}

	$board_ids = array(BOARD_ID_FAQ); // 88888 ist das Fragen und Antworten forum
	if ( $status[rid]) {
		$board_ids[] = $status[rid];
	}
		
	$new_postings = array();
	
	if ($game_syndikat[allianz_id]) {
		$board_ids[] = BOARD_ID_OFFSET_ALLIANZ+$game_syndikat[allianz_id];
	}
	if ($game_syndikat[mentorenboard]) {
		$board_ids[] = BOARD_ID_OFFSET_MENTOREN+$game_syndikat[mentorenboard];
	}
	if ($showGroupboard) 
	{
		if($gruppennummer)
		{
			$board_ids[] = BOARD_ID_OFFSET_GRUPPEN + $gruppennummer;
		}
	}
		
	if (count($board_ids) >= 1) 
	{
	  $lastclicktimes = assocs("select bid, lastklicktime from board_boards_lastklick where kid = $id and bid in (".join(",", $board_ids).")", "bid");
	  
	  foreach ($board_ids as $vl) 
	  {
		  if ($lastclicktimes[$vl][lastklicktime])
		  { 
		  	$new_postings[$vl] = single("select count(*) from board_messages, board_subjects where board_subjects.bid = $vl and board_messages.tid = board_subjects.tid and board_subjects.time > ".$lastclicktimes[$vl][lastklicktime]." and board_messages.time > ".$lastclicktimes[$vl][lastklicktime]." and board_messages.kid != $id");
		  }
		  else // Falls der Benutzer noch nie im baord war und daher noch kein eintrag in board_boards_lastklick besteht werden einfach alle eintr�ge gez�hlt
		  {
		  	$new_postings[$vl] = single("select count(*) from board_messages, board_subjects where board_subjects.bid = $vl and board_messages.tid = board_subjects.tid and board_messages.kid != $id");
		  }
	  }
	}

if ($loggedin) {
	$tpl->assign('LOGIN', true);
	
	if(hasTestServerMenu()){
		$tpl->assign('ENABLE_TESTSERVER_MENU', true);
		$tpl->assign('MENU_TESTSERVER', getTestServerMenu());
	}
	
	if(hasIndividualMenu($features)){
		$tpl->assign('ENABLE_INDIVIDUAL_MENU', true);
		$tpl->assign('MENU_INDIVIDUAL', getIndividualMenu($status));
	}
	
	$message_kategorien = assocs("select id, kategorie from message_settings", "id");
	$new_message_kategorien = singles("select id from message_values where user_id='$id' and gelesen=0 order by time desc");

	if ($new_message_kategorien) {
		$new_message_kategorie = 1;
		foreach ($new_message_kategorien as $vl) {
			if ($message_kategorien[$vl][kategorie] == 2) { 
				$new_message_kategorie = 2;
			}
			elseif (($message_kategorien[$vl][kategorie] == 7 || $message_kategorien[$vl][kategorie] == 3) && $new_message_kategorie != 2){
				$new_message_kategorie = 7;
			}
			elseif (($message_kategorien[$vl][kategorie] == 5 || $message_kategorien[$vl][kategorie] == 4 || $message_kategorien[$vl][kategorie] == 6 || $message_kategorien[$vl][kategorie] == 10) && $new_message_kategorie != 2 && $new_message_kategorie != 7) { 
				$new_message_kategorie = 5;
			}
		}
	}
	
	if ($new_message_kategorie) {
		if	($new_message_kategorie == 2) {
			$tpl->assign('MENU_NEW_MSG_CATEGORY', 'warning');
		}
		elseif ($new_message_kategorie == 7) {
			$tpl->assign('MENU_NEW_MSG_CATEGORY', 'highlight');
		}
		elseif ($new_message_kategorie == 5) {
			$tpl->assign('MENU_NEW_MSG_CATEGORY', 'green');
		}
		else {
			$tpl->assign('MENU_NEW_MSG_CATEGORY', 'nospan');
		}
		$tpl->assign('MENU_NEW_MSG_NUM', count($new_message_kategorien));
	}
		
	$new_mitteilung = single("select count(*) from messages where messages.gelesen=0 and messages.user_id='$id'");
	
	if ($status[id]) {
		$votes = singles("select poll_id from users_votes where user_id=$status[id]");
	}
	
	// Gibt es neue Umfragen -> Blinken bei umfragen
	$user_rid = $status[rid];

	if ($showGroupboard) 
	{
		$fisch++;
		if ($gruppennummer) 
		{
			$user_rid = BOARD_ID_OFFSET_GRUPPEN + $gruppennummer;
		}
	}

	if ($user_rid) 
	{
		if (count($votes) > 0) {
			$new_umfrage = single("select count(*) from polls where time_bis > $time and deleted=0 and (synd_id = $user_rid or synd_id=0 or ally1=$user_rid or ally2=$user_rid) and !(poll_id in (".implode(",",$votes).")) ");
		}
		else {
			$new_umfrage = single("select count(*) from polls where time_bis > $time and deleted=0 and (synd_id = $user_rid or synd_id=0  or ally1=$user_rid or ally2=$user_rid) ");
		}
	}

	//kommunikationmenu dots
	$komdots = Array();
	if ("mitteilungen.php" == $self) {
		$komdots['msg'] = 'red';
    } 
	if ("nachrichten.php" == $self) {
		$komdots['news'] = 'red';
    } 
    if ("polls.php" == $self) {
		$komdots['polls'] = 'red';
    } 
    if ("tutorboard.php" == $self) {
		$komdots['tuts'] = 'red';
    } 
    if ("gruppenboard.php" == $self) {
		$komdots['grpb'] = 'red';
    } 
    if ("syndboard.php" == $self) {
		$komdots['synb'] = 'red';
    } 
    if ("allianzboard.php" == $self) {
		$komdots['ally'] = 'red';
    } 
    if ("fragen_und_antworten_board.php" == $self) {
		$komdots['qab'] = 'red';
    } 
    if ("allgboard.php" == $self) {
		$komdots['allg'] = 'red';
    } 
	if ("buddy.php" == $self) {
		$komdots['buddy'] = 'red';
    } 
	if ("merchandise.php" == $self) {
		$komdots['merch'] = 'red';
    }
    
    $tpl->assign("KOMDOTS", $komdots);
	
	
	if ($new_mitteilung) {
		$tpl->assign('MENU_NEW_MAIL', $new_mitteilung);
	} 
	else { 
		$tpl->assign('MENU_NEW_MAIL', false); 
	}
	
	if ($new_umfrage) {
		$tpl->assign('MENU_NEW_VOTING', true);
	} 
	else { 
		$tpl->assign('MENU_NEW_VOTING', false); 
	}

    // Position des individuellen Men�s
    $tpl->assign('MENU_INDIVIDUAL_POSITION', $status['mymenue']);

	$kat1order = array();
	foreach ($pages as $value) {
		if ($value[kategorie] == 1) {
			$kat1order[$value[showposition]] = $value[dateiname];
		}
		if ($value[kategorie] == 2) {
			$kat2order[$value[showposition]] = $value[dateiname];
		}
		if ($value[kategorie] == 3) {
			$kat3order[$value[showposition]] = $value[dateiname];
		}
		if ($value[kategorie] == 4) {
			$kat4order[$value[showposition]] = $value[dateiname];
		}
	}
        
    $tpl_menu_konzern = array();
	foreach ($kat1order as $show) {
		$grey = 0;
		$inprotection = in_protection($status);
		if 	(!($pages[$show][dateiname] == "configseite" && $status[inprotection] == 'N') 
			&& !(isBasicServer($game) && $basic_invisible == 1) 
			&& ($status[isnoob] == 0 || !in_protection($status) || $status[noob_wholemenu] || $noobinvisible != 1)
			&& ($pages[$show][dateiname] != 'berater' || $status['berater_show'])){
			
			if ($pages[$show][dateiname].".php" == $self) {
				$dot = 'red';
            } 
			else {
                $dot = 'yellow';
            }
			$tpl_menu_konzern[] = array(
                'name' => $pages[$show]['name'],
                'linkfilename' => $pages[$show][dateiname].".php",
				'sname' => $pages[$show][dateiname],
                'dot' => $dot,
                'target' => $pages[$show]['new_window'],
                'disabled' => $grey,
                'noob_invisible' => $pages[$show]['noob_invisible'],
                'basic_invisible' => $pages[$show]['basic_invisible'],
            );
		}
    }
	
    $tpl->assign('MENU_KONZERN', $tpl_menu_konzern);

	if ($game_syndikat[mentorenboard]) {
            $tpl->assign('MENU_SHOW_TUTOR_BOARD', true);
            $tpl->assign('MENU_TUTOR_BOARD_NEW', (($new_postings[BOARD_ID_OFFSET_MENTOREN+$game_syndikat[mentorenboard]] && $status['may_access_boards']) ? $new_postings[BOARD_ID_OFFSET_MENTOREN+$game_syndikat[mentorenboard]] : 0));
	} 
	else {
        $tpl->assign('MENU_SHOW_TUTOR_BOARD', false);
    }
        
	if ($showGroupboard) {
		$fisch++;
		if ($gruppennummer) {
            $tpl->assign('MENU_SHOW_GROUP_BOARD', true);
        	$tpl->assign('MENU_GROUP_BOARD_NEW', ($new_postings[BOARD_ID_OFFSET_GRUPPEN+$gruppennummer] ? $new_postings[BOARD_ID_OFFSET_GRUPPEN+$gruppennummer] : 0));
		} else {
            $tpl->assign('MENU_SHOW_GROUP_BOARD', false);
        }
			
	}
	
	$bdata= getBuddyNums();
	$tpl->assign('MENU_BUDDY_NUMS', "(".$bdata['on']."/".$bdata['total'].")");
    $tpl->assign('MENU_SYND_BOARD_NEW', (($new_postings[$status[rid]] && $status['may_access_boards']) ? $new_postings[$status[rid]] : 0));
        	
	if ($game_syndikat[allianz_id]) {
        $tpl->assign('MENU_SHOW_ALLY_BOARD', true);
		$tpl->assign('MENU_ALLY_BOARD_NEW', (($new_postings[BOARD_ID_OFFSET_ALLIANZ+$game_syndikat[allianz_id]] && $status['may_access_boards']) ? $new_postings[BOARD_ID_OFFSET_ALLIANZ+$game_syndikat[allianz_id]] : 0));
	} else {
        $tpl->assign('MENU_SHOW_ALLY_BOARD', false);
    }
	
	//$lkt = single("select lastklicktime from board_qa_boards_lastklick where kid = $id and bid = 88888", "bid");
    $tpl->assign('MENU_SHOW_FRAGEN_UND_ANTWORTEN_BOARD', true);
    //$tpl->assign('MENU_FRAGEN_UND_ANTWORTEN_BOARD_NEW', single("select count(*) from board_qa_messages, board_qa_subjects where board_qa_subjects.bid = 88888 and board_qa_messages.tid = board_qa_subjects.tid and board_qa_subjects.time > ".($lkt ? $lkt : 0)." and board_qa_messages.time > ".($lkt ? $lkt : 0)." and board_qa_messages.kid != $id"));
    $tpl->assign('MENU_FRAGEN_UND_ANTWORTEN_BOARD_NEW', 0);
	
	foreach ($kat3order as $show) {
		$grey = 0;
		$inprotection = in_protection($status);
        
		if ($pages[$show][dateiname].".php" == $self) {
            $dot = 'red';
        } 
		else {
            $dot = 'yellow';
        }
                    
		$tpl_menu_synworld[] = array(
            'name' => $pages[$show]['name'],
            'linkfilename' => $pages[$show][dateiname].".php",
			'sname' => $pages[$show][dateiname],
            'dot' => $dot,
            'target' => $pages[$show]['new_window'],
            'disabled' => grey_if_no_access($pages[$show]),
            'noob_invisible' => $pages[$show]['noob_invisible'],
            'basic_invisible' => $pages[$show]['basic_invisible'],
        );
	}
	
	if($status["is_mentor"]){
		$mentor = assoc("select * from pages where id = 43");	// id f�r die Mentoren-Seite
		if ($mentor["dateiname"].".php" == $self) {
            $dot = 'red';
        } 
		else {
            $dot = 'yellow';
        }
		$tpl_menu_synworld[] = array(
            'name' => $mentor["name"],
            'linkfilename' => $mentor["dateiname"].".php",
            'dot' => $dot,
            'target' => $mentor['new_window'],
            'disabled' => 0,
            'noob_invisible' => $mentor['noob_invisible'],
            'basic_invisible' => $mentor['basic_invisible'],
        );
	}
		
	
	$tpl->assign('MENU_SYNWORLD', $tpl_menu_synworld);
	
	// Galaxy News Votebutton einblenden
	if (is_array($voted)) {
		foreach ($voted as $vl) {
			if ($vl[link] == "gamesdynamite"){ 
				$gamesdynamite_done = 1;
			}
			elseif ($vl[link] == "galaxy-news") { 
				$galaxy_news_done = 1;
			}
		}
		$galaxy_news_done = $gamesdynamite_done = 1;
		if (!$galaxy_news_done == 1 && !$gamesdynamite_done == 1) {
       		$tpl->assign('MENU_SHOW_GN_VOTE', true);
		}
	}

	foreach ($kat4order as $show) {
		$grey = 0;
		$inprotection = in_protection($status);
		
		if ($pages[$show][dateiname].".php" == $self) {
			$dot = 'red';
		} 
		else {
			$dot = 'yellow';
		}
				
		$tpl_menu_unnamed[] = array(
			'name' => $pages[$show]['name'],
			'linkfilename' => $pages[$show][dateiname].".php",
			'sname' => $pages[$show][dateiname],
			'dot' => $dot,
			'target' => $pages[$show]['new_window'],
			'disabled' => grey_if_no_access($pages[$show]),
			'noob_invisible' => $pages[$show]['noob_invisible'],
			'basic_invisible' => $pages[$show]['basic_invisible'],
		);
	}
	
	$tpl->assign('MENU_UNNAMED', $tpl_menu_unnamed);
	
	if ($status[isnoob] && in_protection($status)) {
		$tpl->assign('NOOB_IN_PROT', true);
	}

}
else {
	$tpl->assign('COLSPAN_MENU', " colspan=3");
    $tpl->assign('LOGIN', false);
}

function grey_if_no_access($pages) {
	global $time, $globals, $inprotection, $sciences;
	$grey = 0;
	
	if($pages['dateiname'] == "market" && !$sciences["ind19"]){
		return 1;
	}
	else if($pages['dateiname'] == "pod" && !$sciences["glo20"]){
		return 1;
	}
	
	if (getServertype() == "classic" && $status[inprotection] == "Y") {
		if ($pages['dateiname'] != "configseite" && 
			$pages['dateiname'] != "logout" &&
			$pages['dateiname'] != "premiumfeatures" &&
			$pages['dateiname'] != "gamevalues" &&
			$pages['dateiname'] != "allgboard" &&
			$pages['dateiname'] != "syndboard" &&
			$pages['dateiname'] != "polls" &&
			$pages['dateiname'] != "nachrichten" &&
			$pages['dateiname'] != "mitteilungen" &&
			$pages['dateiname'] != "statusseite" &&
			$pages['dateiname'] != "gruppenboard" &&
			$pages['dateiname'] != "settings" &&
			$pages['dateiname'] != "options" &&
			$pages['dateiname'] != "description" &&
			$pages['dateiname'] != "werben") return 1;
	}
	 
	if ( ($pages[roundstatus] == 1 && $globals[roundstarttime] - $time > 0) || ($pages[roundstatus] == 2 && $globals[roundstatus] == 0) || ($pages[roundstatus] == 3 && $inprotection) || 
	     ($pages[roundstatus] == 4 && $inprotection) || ($pages[roundstatus] == 4 && $globals[roundstatus] == 0)) {
		$grey = 1;
	}
	return $grey;
}

function menuePoint($name, $link, $dot="dot-gelb.gif",$target="",$grey = 0,$noobinvisible =0,$basic_invisible = 0) {
	$grey = 0;
	$out = "";
    if (func_num_args() > 4 && func_get_arg(4) > 0) {$grey = 1;}
	global $layout,$self,$status,$game;
	
	if (isBasicServer($game) && $basic_invisible == 1) return;

	if ($status[isnoob] == 0 || !in_protection($status) || $status[noob_wholemenu] || $noobinvisible != 1) {
	

		if ($grey == 1) {
			$tpl->assign(strtoupper(substr($link,strlen($link)-4)).'_GREY', true);
		}
		if ($link == $self) {
			$dot = "dot-rot.gif";
		}
	}
}

function hasIndividualMenu($features) {
	return ($features[KOMFORTPAKET]);
}

function getIndividualMenu($status) {
	$linkdata = assocs("select name, address, new_window from mymenue where konzernid = $status[id] order by position asc");
	foreach ($linkdata as $vl) {
		$tpl_menu_individual[] = array(
			'name' => $vl[name],
			'linkfilename' => $vl[address],
			'dot' => $dot,
			'target' => ($vl[new_window] ? 1 : 0)
		);
	}
	return $tpl_menu_individual;
}

/* Zu Runde 45 erg�nzt, um die Zusatzfeatures auf dem Testserver ins Menu einzubinden.
 * Alle Seiten, die auf dem Testserver zus�tzlich eingebunden werden sollten folgenden Code enthalten:
 *
 *if ($game[name] == "Syndicates Testumgebung"){<!--TESTSEIETNCODE HIER--!>}
 *else f("Diese Seite steht auf diesem Server leider nicht zur Verf�gung.");
 */
function hasTestServerMenu(){
	$game = assoc("select name from game limit 1");
	return ($game[name] == "Syndicates Testumgebung");
}
		
function getTestServerMenu(){		
	$tpl_menu_testserver[] = array(
		'name' => 'Konzernwahl',
		'linkfilename' => 'testswitch.php'
	);
	$tpl_menu_testserver[] = array(
		'name' => 'Konfiguration',
		'linkfilename' => 'testconfig.php'
	);
	$tpl_menu_testserver[] = array(
		'name' => 'Mentoren',
		'linkfilename' => 'testmentoren.php'
	);
	$tpl_menu_testserver[] = array(
		'name' => 'Assistenten',
		'linkfilename' => 'testassis.php'
	);
	return $tpl_menu_testserver;
}
?>
