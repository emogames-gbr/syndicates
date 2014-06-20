<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if($action && $action != "create" && $action != "reply" && $action != "edit" && $action != "view" && $action != "deletethreads" && $action != "markforumread" && $action != "manageaccessrights" && $action != "closeopen" && $action != "sticky"){
	$action = "";
}
if($ia && $ia != "finish"){
	$ia = "";
}
if($tid){
	$tid = floor($tid);
}
if($mid){
	$mid = floor($mid);
}
if($delete && $delete != 1){
	$delete = 0;
}
if($seite && $seite != "last"){
	$seite = floor($seite);
}


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//

define (BPS, 15); # 20 Beiträge pro Seite

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

// Header include
// muss weiter unten erst eingefügt werden, da sonst das Menü bei neuen Beiträgen nicht aktuallisiert wird


//**************************************************************************//
//							Variablen initializeialisieren						//
//**************************************************************************//

$tpl->assign('SITE', $page);

$exit = false;
$qa = ''; // Für die andere Tabellen des Fragen und Antworten Board - R4bbiT - 24.02.11
// Alle Tabellen haben ein "board_qa_.." bekommen

if($time >= $globals['roundstarttime'] && $globals['roundstarttime'] + 5 * 60 > $time){
	require_once("../../inc/ingame/header.php");
	$tpl->assign("MSG", "Der Zugang zu den Foren ist wegen der Zuweisung der Gruppen zu ihren Syndikaten erst 5 Minuten nach Rundenstart möglich.");
	$tpl->display("sys_msg.tpl");
	require_once("../../inc/ingame/footer.php");
	exit();		
}
else{
	
	if ($page == "fragen_und_antworten_board") {
		$status['may_access_boards'] = $status['may_access_qna_board'];
	}
	
	// Sperre automatisch aufheben nach 48h
	if (!$status['may_access_boards'] && $status['createtime'] < $time - 86400*2) {
		$status['may_access_boards'] = 1;
		select("UPDATE status SET may_access_boards = 1 WHERE id = '".$status['id']."'");
	}
	
	if (!$status['may_access_boards'] && $page != 'gruppenboard') {
		if (single("select president_id from syndikate where synd_id = ".$status['rid']) != $id) {
			require_once("../../inc/ingame/header.php");
			$tpl->assign("MSG", "Die ersten 2 Tage nach Konzernerstellung ist das Forum optional noch vom Präsident sperrbar. " .
					"Wenden Sie sich bitte an Ihren Präsidenten, falls Sie nicht wissen, weshalb er dies getan hat.");
			$tpl->display("sys_msg.tpl");
			require_once("../../inc/ingame/footer.php");
			exit();		
			//goto footer;
		}
		else{
			select("update status set may_access_boards = 1 where id = ".$id);
			$status['may_access_boards'] = 1;
		}
	}
	if ($status['may_access_boards'] || $page == 'gruppenboard') {
	
		$queries = array();
		$queries2 = array();
		$weitertid = "<br><br><center><a href=$page.php?action=view&tid=$tid&seite=last#last class=linkAufsiteBg>weiter</a></center>";
		
		$tpl->assign("BACK_LINK", "href=javascript:history.back()");
		
		$bid = $status['rid'];
		$rids = $status['rid'];
		
		if($page == "allianzboard"){
			$allianzdata = row("select allianz_id,ally1,ally2 from syndikate where synd_id=".$status['rid']);
			if($allianzdata[0]){
				$bid = BOARD_ID_OFFSET_ALLIANZ + $allianzdata[0];
				$rids .= ",".$allianzdata[1];
				if($allianzdata[2]){
					$rids .= ",".$allianzdata[2];
				}
			}
			else{
				ob_clean();
				header("Location: syndboard.php");
				exit();
			}
			
		}
		elseif ($page == "gruppenboard") {
			if ($globals['roundstatus'] == 1 && ($time < $globals['roundendtime']-BOARD_GRUPPEN_VOR_ENDE_FREISCHALTEN)) {
				ob_clean();
				header("Location: syndboard.php");
				exit();
			}
			
			$user_id = single("SELECT id FROM users WHERE konzernid = '".$status['id']."'");
			list($gruppennummer, $groupcheffe) = row("SELECT group_id, (SELECT konzernid FROM users AS u WHERE u.id = g.admin_id) FROM groups_new AS g WHERE group_id = (SELECT group_id FROM groups_new_members WHERE status != 0 AND user_id = '".$user_id."')");
			if($gruppennummer) {
				$bid = BOARD_ID_OFFSET_GRUPPEN + $gruppennummer;
			}
			else {
				ob_clean();
				header("Location: syndboard.php");
				exit();
			}
		}
		elseif($page == "fragen_und_antworten_board"){
			$bid = BOARD_ID_FAQ;
			$qa = 'qa_';
		}
		initialize($bid, $id);
		
		##############		
		### HEADER ###
		##############
		require_once("../../inc/ingame/header.php");
		// ACHTUNG: in menu.php wird hier $gruppennummer, $groupcheffs nochmals geholt
		
		$kdata = assocs("select id,rulername,syndicate,nw, land, race,image,show_emogames_name,is_mentor".((($bid > BOARD_ID_OFFSET_ALLIANZ && $bid < BOARD_ID_OFFSET_GRUPPEN) || $page == "fragen_und_antworten_board") ? ",rid":"")." from status where rid in ($rids)", "id");
		$emogames_usernamen = assocs("select users.username, users.konzernid from users, status where status.rid in ($rids) and status.id = users.konzernid and status.show_emogames_name >= 1", "konzernid");
		$ausgabe .= "<br>";
		if($page == "syndboard"){
			$syndikatsdata = row("select allianz_id,president_id from syndikate where synd_id=".$status['rid']);
			$president_ids[$syndikatsdata[1]] = 1;
			if ($syndikatsdata[0]){
				$tpl->assign('ALLYBOARD', true);
			}
		}
		elseif($page == "allianzboard"){
			$president_ids = assocs("select president_id from syndikate where synd_id in ($rids)", "president_id");
		}
		elseif ($page == "gruppenboard") {
			$president_ids[$groupcheffe] = 1; // Wird weiter oben geholt
		}
		elseif ($page == "fragen_und_antworten_board") { // Im Fragen und Antworten Forum sind Mentoren == Moderatoren
			if ($status['is_mentor']) {
			    $president_ids[$id] = 1; 
			}
		}
		$tpl->assign("IS_PRESIDENT", $president_ids[$id]);	
	
		//**************************************************************************//
		//**************************************************************************//
		//							Eigentliche Berechnungen!						//
		//**************************************************************************//
		//**************************************************************************//
		
		// Erstellt die Vorschau, wenn man einen neuen Beitrag verfasst, editiert oder ein neues Thema erstellt - R4bbiT - 07.10.10
		if(($action == "create" || $action == "reply" || $action == "edit") && $_POST['preview'] == 'Vorschau'){
			$tpl->assign('PREVIEW', true);
			$tpl->assign('MESSAGE', $_POST['message']);
			$tpl->assign('TITLE', $_POST['title']);
			$tpl->assign('PREVIEW_TEXT', umwandeln_bbcode($_POST['message']));
			$ia = false;
		}
		
		
		if ($action == "markforumread") {
			select("delete from board_".$qa."subjects_new where kid = $id");
			$tpl->assign("MSG", "Forum erfolgreich als gelesen markiert.");
			$tpl->display("sys_msg.tpl");
		}

		if ($action == "manageaccessrights" && $page != "gruppenboard") {
			if ($president_ids[$id]){
				if ($page != "fragen_und_antworten_board") {
				  $members = assocs("select syndicate, id, may_access_boards from status where rid = ".$status['rid'], "id");
				}
				else{
				  $members = assocs("select syndicate, id, may_access_qna_board from status where is_mentor = 0", "id");
				}
				if ($kid) {
					$kid = floor($kid);
					if ($members[$kid] && $kid != $id) {
						if($page != "fragen_und_antworten_board"){
							// Nur wirksam in den ersten 48h nach Konzernerstellung
							$queries2[] = "update status set may_access_boards = 1 - may_access_boards where id = ".$kid;  
							$members[$kid]['may_access_boards'] = 1 - $members[$kid]['may_access_boards'];
							$tpl->assign("ERROR", "Änderung erfolgreich durgeführt.");
							$tpl->display("fehler.tpl");
						}
						else{
							$queries2[] = "update status set may_access_qna_board = 1 - may_access_qna_board where id = ".$kid;
							$members[$kid]['may_access_qna_board'] = 1 - $members[$kid]['may_access_qna_board'];
							$tpl->assign("MSG", "Änderung erfolgreich durchgeführt.");
							$tpl->display("sys_msg.tpl");
						}
					}
					else{
						$tpl->assign("ERROR", "Dieser Konzern befindet sich nicht in ihrem Syndikat.");
						$tpl->display("fehler.tpl");
					}
				}
				
				$rights = array();
				foreach ($members as $ky => $vl) {
					if ($ky != $id) {
						if($page == "fragen_und_antworten_board"){
							$access = $vl['may_access_qna_board'];
						}
						else{
							$access = $vl['may_access_boards'];
						}
						$rights[] = array(
							"access" => $access,
							"name" => $vl['syndicate'],
							"id" => $ky);
						}
				}
				$tpl->assign("KONZERNE", $rights);	
			}
			$tpl->display('boardzugriff.tpl');
			$exit = true;
			//goto footer;
		}
		elseif ($action == "deletethreads")	{
			if ($president_ids[$id])	{
				foreach ($_POST as $ky => $vl)	{
					if (strpos($ky, "elete") == 1){
						$deletefrom[] = floor($vl);
					}
				}
				if ($deletefrom){
					$deletestring = join(",", $deletefrom);
				}
	
				if ($deletestring){
					$validmessages = singles("select distinct tid from board_".$qa."subjects where tid in ($deletestring) and bid=".$bid);
					$validmessages = join(",", $validmessages);
					select("delete from board_".$qa."subjects where tid in ($deletestring) and bid=".$bid);
					select("delete from board_".$qa."subjects_new where tid in (".$deletestring.")");
					select("delete from board_".$qa."messages where tid in (".$validmessages.")");
					$tpl->assign("MSG", "Die Themen wurden erfolgreich gelöscht.");
					$tpl->display("sys_msg.tpl");
					if ($page == "fragen_und_antworten_board") {
						sendthemail("Q&A Board: Thema/Themen gelöscht", $status['syndicate']." löscht ".count($deletefrom)." Themen ", "admin@DOMAIN.de", "Admin");
					};
				}
				else{
					$tpl->assign("ERROR", "Sie haben keine Themen zum Löschen markiert.");
					$tpl->display("fehler.tpl");
				}
			}
		}
		elseif ($action == "sticky") {
			if ($president_ids[$id]){
				$subjects = assoc("select sticky from board_".$qa."subjects where tid = ".$tid." and bid = ".$bid." order by time desc");
				if ($subjects) {
					$newvalue = 1 - $subjects['sticky'];
					if ($newvalue == 1){
						$tpl->assign("MSG", "Das Thema wird jetzt oben festgehalten.");
						$tpl->display("sys_msg.tpl");
					}
					else{
						$tpl->assign("MSG", "Das Thema wird in der Themenübersicht jetzt wieder unter die anderen Themen eingeordnet.");
						$tpl->display("sys_msg.tpl");
					}
					select("update board_".$qa."subjects set sticky = ".$newvalue." where tid = ".$tid);
				}
			}
		}
		elseif ($action == "create"){
			if ($ia){
				$error = array();
				if (strlen($title) < 3 or strlen($title) > 50)	{
					$error[] = "Der Name des Themas muss zwischen 3 und 50 Zeichen lang sein.";
					$ia = false;			
				}
				if (!$message){
					$error[] = "Sie haben keinen Beitrag geschrieben.";
					$ia = false;
				}
				if ($ia)	{
					$title = htmlentities($title, ENT_QUOTES);
					$message = htmlentities($message, ENT_QUOTES);
					if ($globals['updating'] == 0)	{
						select("insert into board_".$qa."subjects (bid, time, kid, title, createtime, lastposter) values (".$bid.",".$time.",".$id.",'".$title."',".$time.",".$id.")");
						$tid = single("select tid from board_".$qa."subjects where bid=".$bid." and createtime = ".$time." and title = '".$title."'");
						select("insert into board_".$qa."messages (tid,time,kid,text) values (".$tid.",".$time.",".$id.",'".$message."')");
					}
					$tpl->assign("MSG", "Ihr Thema wurde erstellt.");
					$tpl->display("sys_msg.tpl");
				}
				else{
					$tpl->assign("ERROR", implode('<br />', $error));
					$tpl->display("fehler.tpl");
				}
			}
			if (!$ia)	{
				select("update sessionids_actual set gueltig_bis = ".($time + SESSION_DAUER)." where user_id = $id");
				$tpl->assign("TITLE", $title);
				$tpl->assign("MESSAGE", $message);
				$tpl->assign("CREATE", true);
				$tpl->display('post_create.tpl');
				$exit = true;	
				//goto footer;
			}
		}
		elseif ($action == "reply")	{
			if ($tid){
				$tiddata = assoc("select bid, title, messages,time from board_".$qa."subjects where tid=".$tid);
				if ($tiddata)	{
					if ($tiddata['bid'] == $bid)	{
						if ($ia){
							if (!$message){
								$tpl->assign("ERROR", "Sie haben keinen Beitrag geschrieben");
								$tpl->display("fehler.tpl");				
								$ia = false;
							}
							if ($ia){
								$message = htmlentities($message, ENT_QUOTES);
								select("insert into board_".$qa."messages (tid,time,kid,text) values (".$tid.",".$time.",".$id.",'".$message."')");
								select("update board_".$qa."subjects set time = ".$time.", lastposter = ".$id.", messages = messages+1 where tid = ".$tid);
								$tpl->assign("BACK_LINK", "href=".$page.".php?action=view&tid=".$tid."&seite=last#last");
								$tpl->assign("SHOW_LINK", 1);
								$tpl->assign("BACK_TITLE", "Zum Beitrag");
								$tpl->assign("MSG","Ihre Antwort wurde hinzugefügt.");
								$tpl->display("sys_msg.tpl");
							}
						}
						if (!$ia)	{
							select("update sessionids_actual set gueltig_bis = ".($time + SESSION_DAUER)." where user_id = $id");
							if (!$message) {
								if ($mid) {
									list ($temp_mid, $temp_tid, $temp_kid, $temp_text) = row("select mid, tid, kid, text from board_".$qa."messages where mid = ".$mid);
									if ($temp_tid == $tid) {
										if (!$kdata[$temp_kid]){
											$kdata[$temp_kid] = assoc("select id,rulername,syndicate,image,rid,nw,land,race from status where id=".$temp_kid);
										}
										$message = "[QUOTE][b]Original von ".$kdata[$temp_kid][syndicate].(($bid > BOARD_ID_OFFSET_ALLIANZ && $bid < BOARD_ID_OFFSET_GRUPPEN) ? " (#".$kdata[$temp_kid][rid].")":"");
										$message .= "[/b]\n[i]".$temp_text."[/i]\n[/QUOTE]\n";
									}
								}
							}
							$tpl->assign("TITLE", $tiddata['title']);
							$tpl->assign("TID", $tid);
							$tpl->assign("MESSAGE", $message);
							$tpl->assign("REPLY", true);
							$tpl->display("post_create.tpl");
							$exit = true;		
							//goto footer;
						}
					}
					else{
						$tpl->assign("ERROR", "Kein Zugriff auf dieses Thema.");
						$tpl->display("fehler.tpl");
					}
				}
				else{ 
					$tpl->assign("ERROR", "Thema existiert nicht.");
					$tpl->display("fehler.tpl");
				}
			}
			else{
				$tpl->assign("ERROR", "Ungültiges Thema gewählt.");
				$tpl->display("fehler.tpl");
			}
		}
		
		elseif ($action == "edit"){
			if ($mid){
				$middata = assoc("select tid,time,text,kid from board_".$qa."messages where mid=".$mid);
				$tid = $middata['tid'];
				if ($tid){
					if ($middata['kid'] == $id or $president_ids[$id]){
						$tiddata = assoc("select bid, title, messages,time from board_".$qa."subjects where tid=".$tid);
						if ($tiddata){
							if ($tiddata['bid'] == $bid){
								if ($ia){
									if (!$message && !$delete){
										$tpl->assign("ERROR", "Sie haben keinen Beitrag geschrieben.");
										$tpl->display("fehler.tpl");				
										$ia = false;
									}
									if ($ia){
										if ($delete){
											if ($president_ids[$id]) {
												if ($tiddata['messages'] == 1){
													select("delete from board_".$qa."subjects where tid=".$tid);
													select("delete from board_".$qa."subjects_new where tid=".$tid);
													$zusatz = "<br />Dies war der letzte Beitrag. Das Thema wurde ebenfalls gelöscht.";
												}
												else{
													if ($middata['time'] == $tiddata['time'])	{
														list($newposter,$newtime) = row("select kid,time from board_".$qa."messages where tid=".$tid." order by time desc limit 1,1");
														if (!$kdata[$newposter]){
															$kdata[$newposter] = assoc("select id,rulername,syndicate,image,rid,nw,land,race from board_".$qa."messages where id='".$newposter."'");
														}
													}
													select("update board_".$qa."subjects set messages=messages-1".($newtime ? ",time=".$newtime : "").($newposter ? ",lastposter=".$newposter : "")." where tid=".$tid);
													$tpl->assign("BACK_LINK", "href=".$page.".php?action=view&tid=".$tid."&seite=last#last");
													$tpl->assign("SHOW_LINK", 1);
													$tpl->assign("BACK_TITLE", "zurück zum Thema");
												}
												select("delete from board_".$qa."messages where mid=".$mid);
												$tpl->assign("MSG", "Der Beitrag wurde soeben erfolgreich gelöscht.".$zusatz);
												$tpl->display("sys_msg.tpl");
											}
											else{
												$tpl->assign("ERROR", "Nur Präsidenten dürfen einzelne Beiträge löschen.");
												$tpl->display("fehler.tpl");
												$ia = false;
											}
										}
										else{
											$message = htmlentities($message, ENT_QUOTES);
											select("update board_".$qa."messages set text='".$message."\n\nDieser Beitrag wurde am ".date("d. M, H:i:s")." von ".$kdata[$id]['rulername']." von ".$kdata[$id]['syndicate'].(($bid > BOARD_ID_OFFSET_ALLIANZ && $bid < BOARD_ID_OFFSET_GRUPPEN) ? " (#".$kdata[$id][rid].")":"")." editiert.' where mid=".$mid);
											$tpl->assign("BACK_LINK", "href=".$page.".php?action=view&tid=".$tid."&seite=last#last");
											$tpl->assign("SHOW_LINK", 1);
											$tpl->assign("BACK_TITLE", "zurück zum Thema");
											$tpl->assign("MSG", "Der Beitrag wurde soeben geändert.");
											$tpl->display("sys_msg.tpl");
										}
									}
								}
								if (!$ia)	{
									$tpl->assign("EDIT", true);
									$tpl->assign("MID", $mid);
									$tpl->assign("TID", $tid);
									$tpl->assign("TITLE", $tiddata['title']);
									$tpl->assign("MESSAGE", ($message ? $message : $middata['text']));
									$tpl->display("post_create.tpl");
									$exit = true;	
									//goto footer;
								}
							}
							else{
								$tpl->assign("ERROR", "Kein Zugriff auf dieses Thema.");
								$tpl->display("fehler.tpl");
							}
						}
						else {
							$tpl->assign("ERROR", "Ungültiger Beitrag.");
							$tpl->display("fehler.tpl");
						}
					}
					else{ 
						$tpl->assign("ERROR", "Sie haben keine Berechtigung diesen Beitrag zu editieren.");
						$tpl->display("fehler.tpl");
					}
				}
				else{
					$tpl->assign("ERROR", "Ungültigen Beitrag gewählt!");
					$tpl->display("fehler.tpl");
				}
			}
			else{
				$tpl->assign("ERROR", "Kein Beitrag zum Editieren ausgewählt.");
				$tpl->display("fehler.tpl");
			}
		}
		
		elseif ($action == "view"){
			if ($tid){
				$tiddata = assoc("select bid, title, messages,time, open, sticky from board_".$qa."subjects where tid=$tid");
				if ($tiddata){
					if ($tiddata['bid'] == $bid){
						$newdata = new_and_not_yet_seen($tid);
						if ($newdata and $newdata['todel'] == 0) {
							select("update board_".$qa."subjects_new set todel=1 where tid=".$tid);
						}
						$anzahl_seiten = ceil($tiddata['messages']/BPS);
						if ($seite == "last"){
							$seite = $anzahl_seiten;
						}
						if ($newest && $newdata){
							$seite = ceil(single("select count(*) from board_".$qa."messages where mid <= ".$newdata['oldest_new_mid']." and tid=".$tid)/BPS);
						}
						if ($seite && $seite > $anzahl_seiten){
							$seite = $anzahl_seiten;
						}
						if (!$seite){
							$seite = 1;
						}
						$tpl->assign('PAGE', $seite);
						$tpl->assign('NUM_PAGES', $anzahl_seiten);
						
						$anzahl_nachrichten = ($seite - 1) * BPS;
						$middata = assocs("select mid,time,kid,text from board_".$qa."messages where tid=".$tid." order by time asc LIMIT ".$anzahl_nachrichten.",".BPS);
						$postings = array();
						foreach ($middata as $vl){
							unset($emonick, $path_to_pic);
							if (!$kdata[$vl['kid']]){
								$kdata[$vl['kid']] = assoc("select id,rulername,syndicate,image,rid,nw,land,race,show_emogames_name from status where id=".$vl['kid']);
							}
							if ($bid > BOARD_ID_OFFSET_ALLIANZ && $bid < BOARD_ID_OFFSET_GRUPPEN || $page == "fragen_und_antworten_board"){
								$link_to_id = "href=syndicate.php?rid=".$kdata[$vl['kid']]['rid'];
								$konz_id = $kdata[$vl['kid']]['rid'];
							}
							if(((($kdata[$vl['kid']]['rid'] && $kdata[$vl['kid']]['rid'] == $status['rid']) or !$kdata[$vl['kid']]['rid']) && $kdata[$vl['kid']]['show_emogames_name']) or ($kdata[$vl['kid']]['show_emogames_name'] == 2 && $page == "allianzboard") or ($kdata[$vl['kid']]['show_emogames_name'] && $page == "gruppenboard" && $globals['roundstatus'] == 0)) {
								$emonick = single("select username from users where konzernid = ".$vl['kid']);
							}
							// Tempfix - R4bbiT - 25.02.11
							if($page == "fragen_und_antworten_board" && !$president_ids[$id]){
								$emonick = false;
							}
							if($kdata[$vl['kid']]['image']) {
								$path_to_pic = WWWDATA."konzernimages/".KBILD_PREFIX.$vl[kid].".".$kdata[$vl[kid]][image];
							}
							$postings[] = array(
											"mid" => $vl['mid'],
											"newest" => ($vl['mid'] == $newdata['oldest_new_mid'] ? true : false),
											"last" => ($vl['time'] == $tiddata['time'] ? true : false),
											"new" => ($newdata and $newdata['time_new_mid'] <= $vl['time'] ? true : false),
											"poster_id" => $kdata[$vl['kid']]['id'],
											"name_of_poster" => $kdata[$vl[kid]][rulername],
											"name_of_konz" => $kdata[$vl[kid]][syndicate],
											"poster_rid" => $kdata[$vl['kid']]['rid'],
											"poster_race" => ($kdata[$vl['kid']]['race'] == 'pbf' ? 'bf' : $kdata[$vl['kid']]['race']),
											"emonick" => $emonick,
											"path_to_pic" => $path_to_pic,
											"poster_id" => $vl['kid'],
											"own" => ($vl['kid'] == $id ? true : false),
											"mentor" => ($page == "fragen_und_antworten_board" && $kdata[$vl[kid]]['is_mentor'] ? trie : false),
											"bbcode" => umwandeln_bbcode($vl['text']),
											"poster_nw" => $kdata[$vl['kid']]['nw'],
											"poster_land" => $kdata[$vl['kid']]['land'],
											"date" => datum("d.m.Y, H:i", $vl['time'])
							);
						}
						$tpl->assign("POSTS", $postings);	
						$tpl->assign("TOPIC_TITLE", $tiddata['title']);
						$tpl->assign("TID", $tid);
						$tpl->assign("STICKY", $tiddata['sticky']);
						$tpl->display('topic_view.tpl');
						$exit = true;	
						//goto footer;
					}
					else{			
						$tpl->assign("ERROR", "Kein Zugriff auf dieses Thema.");
						$tpl->display("fehler.tpl");
 					}
				}else{
					$tpl->assign("ERROR", "Thema existiert nicht.");
					$tpl->display("fehler.tpl");
				}
			}else{
				$tpl->assign("ERROR", "Ungültiges Thema gewählt.");
				$tpl->display("fehler.tpl");
			}
		}
	
		$subjects = assocs("select tid,time,kid,messages,title,createtime,lastposter,open,sticky from board_".$qa."subjects where bid=".$bid." order by sticky desc, time desc;");
		if ($subjects){
			$subjectcount = 0;
			$topics_out = array();
			foreach ($subjects as $vl){
				$newdata = new_and_not_yet_seen($vl['tid']);
				if (!$kdata[$vl['kid']]){
					$kdata[$vl['kid']] = assoc("select id,rulername,syndicate,image,rid, nw, land, race from status where id=".$vl['kid']);
				}
				if (!$kdata[$vl['lastposter']]){
					$kdata[$vl['lastposter']] = assoc("select id, rulername, syndicate, image, rid, nw, land, race from status where id=".$vl['lastposter']);
				}
				
				(($newdata && !$newdata['todel']) ? $new = 1 : $new = 0);
				(($bid > BOARD_ID_OFFSET_ALLIANZ && $bid < BOARD_ID_OFFSET_GRUPPEN || $page == "fragen_und_antworten_board") ? $from = 1 : $from = 0);
				$topics_out[] = array(
					"from"=>$from,
					"is_sticky"=>$vl['sticky'],
					"is_new"=>$new,
					"locked"=>$locked,
					"topic_name"=>$vl[title],
					"date"=>datum("d.m.Y, H:i", $vl['createtime']),
					"last_date"=>datum("d.m.Y, H:i", $vl['time']),
					"creator"=>$kdata[$vl['kid']]['syndicate'],
					"creator_syn_id"=>$kdata[$vl['kid']]['rid'],
					"last_poster"=>$kdata[$vl['lastposter']]['syndicate'],
					"last_syn_id"=>$kdata[$vl['lastposter']]['rid'],
					"posts"=>($vl['messages']-1),
					"subjects"=>$subjectcount,
					"id"=>$vl['tid'] );
				$subjectcount++;
			}
			$tpl->assign("TOPICS", $topics_out);
		}
		if($president_ids[$id] && $page != "gruppenboard"){
			$tpl->assign("IS_PRESIDENT_AND_NO_GP", 1);
		}
	}
}


db_write($queries, 1);
db_write($queries2);



//**************************************************************************//
//								Ausgabe, Footer								//
//**************************************************************************//

if(!$exit){
	$tpl->display('board.tpl');
}
// footer:
require_once("../../inc/ingame/footer.php");



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


function initialize($bid, $id) {
	if ($id && $bid) {
		global $time, $id_data, $queries, $action; #id_data kommt aus game.php von der sessionid, wichtig ist hier angelegt_bei
		$lastklicktime = single("select lastklicktime from board_".$qa."boards_lastklick where kid=$id and bid=$bid");
		if (!$action or $action == "view" or $action == "markforumread") {
			if (!$lastklicktime) { $lastklicktime = $time; select("insert into board_".$qa."boards_lastklick (lastklicktime, kid, bid) values ($time, $id, $bid)"); }
			else { select("update board_".$qa."boards_lastklick set lastklicktime=$time where kid=$id and bid=$bid"); }
		}

		# Wenn das Forum als gelesen markiert wird braucht hier nicht weitergemacht zu werden.
		if ($action != "markforumread") {
			# 1. Checken ob dies der erste Klick in der Session des Users ist
			if ($lastklicktime < $id_data[angelegt_bei]) {
				if ($lastklicktime < $time-600) {
					login_user($bid, $id);
					search_new_threads($bid, $id, $lastklicktime);
				}
				else {
					search_new_threads($bid, $id, $lastklicktime);
				}
			}
			else {
				search_new_threads($bid, $id, $lastklicktime);
			}
		}
	}
}
function login_user($bid, $id) {
	$tids = array();
	$tids = singles("select tid from board_".$qa."subjects where bid=$bid");
	if ($tids) {
		select("delete from board_".$qa."subjects_new where todel=1 and kid=$id and tid in (".join(",", $tids).")");
		$newtids = assocs("select board_".$qa."subjects_new.tid, board_".$qa."subjects_new.time_new_mid, board_".$qa."messages.time from board_".$qa."subjects_new,board_".$qa."messages where board_".$qa."messages.tid in (".join(",", $tids).") and board_".$qa."subjects_new.tid in (".join(",", $tids).") and board_".$qa."subjects_new.oldest_new_mid=board_".$qa."messages.mid", "tid");
		foreach ($newtids as $ky => $vl) {
			if ($vl[time] != $vl[time_new_mid]) { select("update board_".$qa."subjects_new set time_new_mid=".$vl[time]." where tid=$ky"); }
		}
	}
}
function search_new_threads($bid, $id, $lastklicktime) {
	global $queries;
	$tids = array();
	$tids = singles("select tid from board_".$qa."subjects where bid=$bid and time > $lastklicktime");
	if ($tids) {
		$newstuff = assocs("select mid, time, tid, kid from board_".$qa."messages where tid in (".join(",", $tids).") and time > $lastklicktime", "mid");
		foreach ($newstuff as $ky => $vl) {
			if (!$newtiddata[$vl[tid]] or $newtiddata[$vl[tid]][time] > $vl[time]): $newtiddata[$vl[tid]] = array("time" => $vl[time], "mid" => $vl[mid], "kid" => $vl[kid]); endif;
		}
	}
	$alreadynewstuff = assocs("select tid, todel, oldest_new_mid, time_new_mid from board_".$qa."subjects_new where kid=$id", "tid");
	if ($newtiddata) {
		foreach ($newtiddata as $ky => $vl) {
			if ($alreadynewstuff[$ky] and $vl[kid] != $id) {
				if ($alreadynewstuff[$ky][todel]) {
					select("update board_".$qa."subjects_new set todel=0, oldest_new_mid=".$vl[mid]." where kid=$id and tid=$ky");
					$alreadynewstuff[$ky][oldest_new_mid] = $vl[mid];
					$alreadynewstuff[$ky][todel] = 0;
				}
			}
			elseif (!$alreadynewstuff[$ky]) {
				$queries[] = "insert into board_".$qa."subjects_new (kid, tid, oldest_new_mid, time_new_mid".($vl[kid] == $id ? ",todel":"").") values ($id, $ky, ".$vl[mid].", ".$vl[time].($vl[kid] == $id ? ",1":"").")";
				$alreadynewstuff[$ky] = array(	"tid" => $ky,
												"todel" => 0,
												"oldest_new_mid" => $vl[mid],
												"time_new_mid" => $vl[time]);
				if ($vl[kid] == $id): $alreadynewstuff[$ky][todel] = 1; endif;
			}
		}
	}
	new_and_not_yet_seen("initialize", $alreadynewstuff);
}
function new_and_not_yet_seen($tid) {
	static $alreadynewstuff;
	if ($tid == "initialize") {
		if (func_num_args() > 1): $alreadynewstuff = func_get_arg(1); endif;
	}
	else {
		if ($alreadynewstuff[$tid]) {
			return array("todel" => $alreadynewstuff[$tid][todel], "oldest_new_mid" => $alreadynewstuff[$tid][oldest_new_mid], "time_new_mid" => $alreadynewstuff[$tid][time_new_mid]);
		} else { return 0; }
	}
}

?>
