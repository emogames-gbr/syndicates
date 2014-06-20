<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($action and $action != "create" and $action != "reply" and $action != "edit" and $action != "view" and $action != "deletethreads" and $action != "markforumread" and $action != "manageaccessrights"): $action = ""; endif;
if ($ia and $ia != "finish"): $ia = ""; endif;
if ($tid): $tid = floor($tid); endif;
if ($mid): $mid = floor($mid); endif;
if ($delete && $delete != 1): $delete = 0; endif;
if ($seite && $seite != "last"): $seite = floor($seite); endif;


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//

define (BPS, 15); # 20 Beiträge pro Seite

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//							Variablen initializeialisieren						//
//**************************************************************************//

if (!$status['may_access_boards']) {
	if (single("select president_id from syndikate where synd_id = ".$status['rid']) != $id) {
		$ausgabe .= "<br><br>Der Präsident hat Ihnen den Zugang ins Forum verweigert. <br>Wenden Sie sich bitte an Ihren Präsidenten, falls Sie nicht wissen, weshalb er dies getan hat.";
	} else {
		select("update status set may_access_boards = 1 where id = $id");
		$status['may_access_boards'] = 1;
	}
}
if ($status['may_access_boards']) {

	$queries = array();
	$queries2 = array();
	$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
	$weiter = "<br><br><center><a href=$page.php class=linkAufsiteBg>weiter</a></center>";
	$weitertid = "<br><br><center><a href=$page.php?action=view&tid=$tid&seite=last#last class=linkAufsiteBg>weiter</a></center>";

	$bid = $status[rid];
	$rids = $status[rid];

	if ($page == "allianzboard")	{
		$allianzdata = row("select allianz_id,ally1,ally2 from syndikate where synd_id=".$status[rid]);
		if ($allianzdata[0]):
				$bid = 20000 + $allianzdata[0];
				$rids .= ",".$allianzdata[1];
				if ($allianzdata[2]): $rids .= ",".$allianzdata[2]; endif;
		else:	 header ("Location: syndboard.php"); exit();
		endif;
	}
	elseif ($page == "tutorboard")	{
		if ($game_syndikat[mentorenboard]):
				$bid = 40000 + $game_syndikat[mentorenboard];
				$rids = join(",", singles("select synd_id from syndikate where mentorenboard = ".$game_syndikat[mentorenboard]));
		else:	 header ("Location: syndboard.php"); exit();
		endif;
	}
	elseif ($page == "gruppenboard")	{
		foreach (range(1, MAX_USERS_A_GROUP) as $vl)	{ $users_temp .= "u$vl,";}
		$users_temp = chopp($users_temp);
		list($gruppennummer, $groupcheffe) = row("select group_id, u1 from groups where $id in ($users_temp)");
		unset($users_temp);

		if ($gruppennummer) {
			$bid = 60000 + $gruppennummer;
		} else { header ("Location: syndboard.php"); exit(); }
	}

	initialize($bid, $id);


	$kdata = assocs("select id,rulername,syndicate,image,show_emogames_name".(($bid > 20000 && $bid < 60000) ? ",rid":"")." from status where rid in ($rids)", "id");
	$emogames_usernamen = assocs("select users.username, users.konzernid from users, status where status.rid in ($rids) and status.id = users.konzernid and status.show_emogames_name >= 1", "konzernid");

	$ausgabe .= "<br>";
	if ($page == "syndboard")	{
		$syndikatsdata = row("select allianz_id,president_id from syndikate where synd_id=".$status[rid]);
		$president_ids[$syndikatsdata[1]] = 1;
		if ($syndikatsdata[0]): $otherboard .= "<a href=allianzboard.php class=hrAufSiteBg>Zum Allianz Board</a><br><br>"; endif;
		if ($game_syndikat[mentorenboard]): $otherboard .= "<a href=tutorboard.php class=hrAufSiteBg>Zum Tutor Board</a><br><br>"; endif;
	}
	elseif ($page == "allianzboard")	{
		$president_ids = assocs("select president_id from syndikate where synd_id in ($rids)", "president_id");
		$otherboard .= "<a href=syndboard.php class=hrAufSiteBg>Zum Syndikats Board</a><br><br>";
		if ($game_syndikat[mentorenboard]): $otherboard .= "<a href=tutorboard.php class=hrAufSiteBg>Zum Tutor Board</a><br><br>"; endif;
	}
	elseif ($page == "tutorboard")	{
		$president_ids = assocs("select president_id from syndikate where synd_id in ($rids)", "president_id");
		$otherboard .= "<a href=syndboard.php class=hrAufSiteBg>Zum Syndikats Board</a><br><br>";
		if ($game_syndikat[allianz_id]): $otherboard .= "<a href=allianzboard.php class=hrAufSiteBg>Zum Allianz Board</a><br><br>"; endif;
	}
	elseif ($page == "gruppenboard") {
		$president_ids[$groupcheffe] = 1; // Wird weiter oben geholt
	}


	//**************************************************************************//
	//**************************************************************************//
	//							Eigentliche Berechnungen!						//
	//**************************************************************************//
	//**************************************************************************//

	if (!$action or $action == "markforumread")	{
		if ($action == "markforumread") {
			select("delete from board_subjects_new where kid = $id");
			s("Forum erfolgreich als gelesen markiert.");
		}
		if ($page == "syndboard")	{
			$ausgabe .= "Das Syndikatsforum dient der Syndikatsinternen Kommunikation und Absprache.<br>Hier können aktuelle Geschehnisse mit den Syndikatsmitgliedern diskutiert werden.<br><br>";
		}
		if ($page == "allianzboard")	{
			$ausgabe .= "Das Allianzforum dient der syndikatsübergreifenden Kommunikation und Absprache mit den Allianzpartnern.<br>Hier können aktuelle Geschehnisse mit den Mitgliedern der Allianz diskutiert werden.<br><br>";
		}
		if ($page == "tutorboard")	{
			$syndata_for_info = assoc("select synd_id, name from syndikate where mentorenboard = $game_syndikat[mentorenboard] and synd_id != $status[rid]");
			$ausgabe .= "Das Tutor Board soll neuen Spielern die Möglichkeit bieten gezielt erfahrene Spieler ansprechen und ihnen Fragen stellen zu können.<br>Wenn euch etwas unklar ist, dann seid ihr hier genau richtig!<br>Im Tutor-Programm bei Syndicates wird jedem Anfängersyndikat ein normales Syndikat als Tutor zugewiesen. Die Spieler aus dem normalen Syndikat haben die Aufgabe, den neuen Spielern das Spiel näher zu bringen, Fragen zu beantworten und können dabei auch etwas gewinnen (siehe Anleitung). Wer bei Unklarheiten nicht nachfragt, ist selbst schuld ;-)<br><br><b>Du befindest dich in einem ".($game_syndikat[synd_type] == "normal" ? "normalen Syndikat und sollst hier im Forum als Tutor agieren und den Spielern aus dem euch zugewiesenem Anfängersyndikat <i><u>$syndata_for_info[name] (#$syndata_for_info[synd_id])</u></i> dabei helfen in Syndicates rein zu finden." : "Anfängersyndikat. Die Spieler aus dem Syndikat <i><u>$syndata_for_info[name] (#$syndata_for_info[synd_id])</u></i>, welches deinem Syndikat zugewiesen wurde, werden dir bei Fragen oder Unklarheiten gerne weiter helfen.")."<br><br>";
		}
		$subjects = assocs("select tid,time,kid,messages,title,createtime,lastposter from board_subjects where bid=$bid order by time desc;");
		if ($subjects)	{
			$subjectcount = 0;
			foreach ($subjects as $vl)	{
				$newdata = new_and_not_yet_seen($vl[tid]);
				if (!$kdata[$vl[kid]]): $kdata[$vl[kid]] = assoc("select id,rulername,syndicate,image,rid from status where id=".$vl[kid]); endif;
				if (!$kdata[$vl[lastposter]]): $kdata[$vl[lastposter]] = assoc("select id, rulername, syndicate, image, rid from status where id=".$vl[lastposter]); endif;
				$ausgabe_subjects .= "<tr class=tableInner1><td width=\"100%\">".(($newdata && !$newdata[todel]) ? "<a href=$page.php?action=view&tid=".$vl[tid]."&newest=newest#newest class=linkAufTableInner><img src=\"".$ripf."firstnew.gif\" border=0></a> ":"")."<b><a href=$page.php?action=view&tid=".$vl[tid]." class=linkAufTableInner>".$vl[title]."</a></b></td><td align=center nowrap>".datum("d.m.Y, H:i", $vl[createtime])."<br><b>von ".$kdata[$vl[kid]][syndicate].(($bid > 20000 && $bid < 60000) ? " (#".$kdata[$vl[kid]][rid].")":"")."</b></td><td align=center>".($vl[messages]-1)."</td><td align=left nowrap><table class=tableInner1><tr><td><a href=$page.php?action=view&tid=".$vl[tid]."&seite=last#last class=linkAufTableInner><img src=\"".$ripf."lastpost.gif\" border=0></a></td><td nowrap> ".datum("d.m.Y, H:i", $vl[time])."<br><b>von ".$kdata[$vl[lastposter]][syndicate].(($bid > 20000 && $bid < 60000) ? " (#".$kdata[$vl[lastposter]][rid].")":"")."</b></td></tr></table></td>".($president_ids[$id] ? "<td align=center><input type=checkbox name=delete$subjectcount value=".$vl[tid]."></td>":"")."</tr>";
				$subjectcount++;
			}
		}
		else	{
			$ausgabe_subjects = "<tr><td class=tableInner1 align=center width=100% colspan=".($president_ids[$id] ? "5":"4").">Keine Themen vorhanden.</td></tr>";
		}

		$ausgabe .= "$otherboard
					<table width=598 border=0 cellpadding=4 cellspacing=1>
						<tr><td align=left class=siteGround><strong><a href=$page.php?action=create class=linkAufSitebg>Neues Thema</a></strong></td><td align=right><strong><a href=$page.php?action=markforumread class=linkAufSitebg>Forum als gelesen markieren</a></strong></td></tr>
						<tr><td height=10 colspan=2></td></tr>
					</table>

					<table width=598 border=0 cellpadding=0 cellspacing=0 class=tableOutline><tr><td>
						<table width=598 border=0 cellpadding=4 cellspacing=1>
							<tr class=tableHead><td width=100% align=left>Thema</td><td align=center nowrap>Erstellt von</td><td align=center>Antworten</td><td align=right nowrap>Letzer Beitrag</td>".($president_ids[$id] ? "<td>&nbsp;</td>":"")."</tr>
				".($president_ids[$id] ? "<form action=$page.php method=post><input type=hidden name=action value=deletethreads>":"")."
							$ausgabe_subjects
				".($president_ids[$id] ? "<tr class=tableInner1><td colspan=5 width=598 align=right><input type=submit value=\"Markierte Themen löschen\"></td></tr></form>":"")."
						</table>
					</td></tr>".($president_ids[$id] ? "<tr class=siteGround><td><a href=$page.php?action=manageaccessrights class=linkAufsiteBg>${yellowdot}Zugriffsrechte festlegen</a></td></tr>":"")."</table>";

	}
	elseif ($action == "manageaccessrights") {
		if ($president_ids[$id])	{

				$ausgabe .= "Legen Sie fest, welche Mitglieder Ihres Syndikats die Foren benutzen dürfen:<br><br>";
				$members = assocs("select syndicate, id, may_access_boards from status where rid = ".$status['rid'], "id");


			if ($kid) {
				$kid = floor($kid);
				if ($members[$kid] && $kid != $id) {
					$queries2[] = "update status set may_access_boards = 1 - may_access_boards where id = $kid";
					s("Änderung erfolgreich durchgeführt.");
					$members[$kid]['may_access_boards'] = 1 - $members[$kid]['may_access_boards'];
				}
			}

				$ausgabe .= "<table width=80% align=center class=tableOutline cellpadding=0 cellspacing=1><tr><td><table width=100% class=tableInner1 cellspacing=1 cellpadding=4>";
				foreach ($members as $ky => $vl) {
					if ($ky != $id) {
						$ausgabe .= "<tr><td>".$vl['syndicate']."</td><td><a href=$page.php?action=manageaccessrights&kid=$ky class=linkAuftableInner>".($vl['may_access_boards'] ? "sperren":"freischalten")."</td></tr>";
					}
				}
				$ausgabe .= "</table></td></tr></table>";

		}
	}
	elseif ($action == "deletethreads")	{
		if ($president_ids[$id])	{
			foreach ($_POST as $ky => $vl)	{
				if (strpos($ky, "elete") == 1){$deletefrom[] = floor($vl);}
			}
			if ($deletefrom): $deletestring = join(",", $deletefrom); endif;

			if ($deletestring):
				$validmessages = singles("select distinct tid from board_subjects where tid in ($deletestring) and bid=$bid");
				$validmessages = join(",", $validmessages);
				$queries[] = "delete from board_subjects where tid in ($deletestring) and bid=$bid";
				$queries[] = "delete from board_subjects_new where tid in ($deletestring)";
				$queries[] = "delete from board_messages where tid in ($validmessages)";
				s("Die Themen wurden erfolgreich gelöscht.$weiter");
			else: f("Sie haben keine Themen zum Löschen markiert!$zurueck");
			endif;
		}
	}
	elseif ($action == "create")	{
		if ($ia)	{
			if (strlen($title) < 3 or strlen($title) > 50)	{
				f("Der Name des Themas muss zwischen 3 und 50 Zeichen lang sein!");
				$ia = "";
			}
			if (!$message)	{
				f("Sie haben keinen Beitrag geschrieben!");
				$ia = "";
			}
			if ($ia)	{
				$title = htmlentities($title, ENT_QUOTES);
				$message = htmlentities($message, ENT_QUOTES);
				if ($globals[updating] == 0)	{
					select("insert into board_subjects (bid,time,kid,title,createtime,lastposter) values ($bid,$time,$id,'$title',$time,$id)");
					$tid = single("select tid from board_subjects where bid=$bid and title='$title'");
					select("insert into board_messages (tid,time,kid,text) values ($tid,$time,$id,'$message')");
				}
				s("Ihr Thema wurde erstellt.$weiter");
			}
		}
		if (!$ia)	{
			select("update sessionids_actual set gueltig_bis = ".($time + SESSION_DAUER)." where user_id = $id");
			$ausgabe .= "
					<table width=598 border=0 cellpadding=4 cellspacing=1>
						<tr><td align=left class=siteGround><strong>Thema erstellen</strong></td></tr>
						<tr><td height=10></td></tr>
					</table>
					<form action=$page.php method=post><input type=hidden name=action value=create><input type=hidden name=ia value=finish>
					<table width=598 border=0 cellpadding=1 cellspacing=0 class=tableOutline><tr><td>
						<table width=598 border=0 cellpadding=3 cellspacing=0>
							<tr><td width=100 align=right class=tableInner2 height=50><strong>Betreff:</strong></td><td width=498 align=center class=tableInner2><input type=text name=title size=50 maxlength=50 value=\"$title\"></td></tr>
							<tr><td width=100 align=right valign=top class=tableInner1><strong>Ihr Beitrag:</strong><br><br>BBCode-Hilfe <a href=javascript:info('hilfe','bbcode') class=\"highlightAuftableInner\"><img src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\"></a></td><td width=498 align=center class=tableInner1><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
							<tr class=tableInner1><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Thema erstellen\"></td></tr>
						</table>
					</td></tr></table></form>";
		}
	}
	elseif ($action == "reply")	{
		if ($tid)	{
			$tiddata = assoc("select bid, title, messages,time from board_subjects where tid=$tid");
			if ($tiddata)	{
				if ($tiddata[bid] == $bid)	{
					if ($ia)	{
						if (!$message)	{
							f("Sie haben keinen Beitrag geschrieben!");
							$ia = "";
						}
						if ($ia)	{
							$message = htmlentities($message, ENT_QUOTES);
							$queries[] = "insert into board_messages (tid,time,kid,text) values ($tid,$time,$id,'$message')";
							$queries[] = "update board_subjects set time=$time,lastposter=$id,messages=messages+1 where tid=$tid";
							s("Ihre Antwort wurde hinzugefügt.$weitertid");
						}
					}
					if (!$ia)	{
						select("update sessionids_actual set gueltig_bis = ".($time + SESSION_DAUER)." where user_id = $id");
						if (!$message) {
							if ($mid) {
								list ($temp_mid, $temp_tid, $temp_kid, $temp_text) = row("select mid, tid, kid, text from board_messages where mid = $mid");
								if ($temp_tid == $tid) {
									if (!$kdata[$temp_kid]): $kdata[$temp_kid] = assoc("select id,rulername,syndicate,image,rid from status where id=".$temp_kid); endif;
									$message = "[QUOTE][b]Original von ".$kdata[$temp_kid][syndicate].(($bid > 20000 && $bid < 60000) ? " (#".$kdata[$temp_kid][rid].")":"");
									$message .= "[/b]
	[i]".$temp_text."[/i]
	[/QUOTE]";
								}
							}
						}
						$ausgabe .= "
								<table width=598 border=0 cellpadding=4 cellspacing=1>
									<tr><td align=left class=siteGround><strong>".$tiddata[title]."</strong></td></tr>
									<tr><td height=10></td></tr>
								</table>
								<form action=$page.php method=post><input type=hidden name=action value=reply><input type=hidden name=ia value=finish><input type=hidden name=tid value=$tid>
								<table width=598 border=0 cellpadding=1 cellspacing=0 class=tableOutline><tr><td>
									<table width=598 border=0 cellpadding=3 cellspacing=0>
										<tr><td height=10 colspan=2 class=tableInner1></td></tr>
										<tr><td width=100 align=right valign=top class=tableInner1><strong>Ihre Antwort:</strong><br><br>BBCode-Hilfe <a href=javascript:info('hilfe','bbcode') class=\"highlightAuftableInner\"><img src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\"></a></td><td width=498 align=center class=tableInner1><textarea cols=40 rows=20 name=message>$message</textarea></td></tr>
										<tr class=tableInner1><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Antworten\"></td></tr>
									</table>
								</td></tr></table></form>";
					}
				}	else { f("Kein Zugriff auf dieses Thema!$zurueck"); }
			}	else { f("Thema existiert nicht!$zurueck");}
		}	else { f("Ungültiges Thema gewählt!$zurueck");}
	}
	elseif ($action == "edit")	{
			if ($mid)	{
				$middata = assoc("select tid,time,text,kid from board_messages where mid=$mid");
				$tid = $middata[tid];
				if ($tid)	{
					if ($middata[kid] == $id or $president_ids[$id])	{
						$tiddata = assoc("select bid, title, messages,time from board_subjects where tid=$tid");
						if ($tiddata)	{
							if ($tiddata[bid] == $bid)	{
								if ($ia)	{
									if (!$message && !$delete)	{
										f("Sie haben keinen Beitrag geschrieben!");
										$ia = "";
									}
									if ($ia)	{
										if ($delete)	{
											if ($president_ids[$id]) {
												if ($tiddata[messages] == 1):
														$queries[] = "delete from board_subjects where tid=$tid";
														$queries[] = "delete from board_subjects_new where tid=$tid";
														$weiterwhat = $weiter;
												else:
														if ($middata[time] == $tiddata[time])	{
															list($newposter,$newtime) = row("select kid,time from board_messages where tid=$tid order by time desc limit 1,1");
															if (!$kdata[$newposter]): $kdata[$newposter] = assoc("select id,rulername,syndicate,image,rid from board_messages where id='".$newposter."'"); endif;
														}
														$queries[] = "update board_subjects set messages=messages-1".($newtime ? ",time=$newtime":"").($newposter ? ",lastposter=$newposter":"")." where tid=$tid";
														$weiterwhat = $weitertid;
												endif;
												$queries[] = "delete from board_messages where mid=$mid";
												s("Der Beitrag wurde soeben erfolgreich gelöscht.$weiterwhat");
											} else { f("Nur Präsidenten dürfen einzelne Beiträge löschen!$zurueck"); }
										}
										else	{
											$message = htmlentities($message, ENT_QUOTES);
											$queries[] = "update board_messages set text='$message\n\nDieser Beitrag wurde am ".date("d. M, H:i:s")." von ".$kdata[$id][rulername]." von ".$kdata[$id][syndicate].(($bid > 20000 && $bid < 60000) ? " (#".$kdata[$id][rid].")":"")." editiert.' where mid=$mid";
											s("Der Beitrag wurde soeben geändert.$weitertid");
										}
									}
								}
								if (!$ia)	{
									$ausgabe .= "
											<table width=598 border=0 cellpadding=4 cellspacing=1>
												<tr><td align=left class=siteGround><strong>".$tiddata[title]."</strong></td></tr>
												<tr><td height=10></td></tr>
											</table>
											<form action=$page.php method=post><input type=hidden name=action value=edit><input type=hidden name=ia value=finish><input type=hidden name=mid value=$mid><input type=hidden name=tid value=$tid>
											<table width=598 border=0 cellpadding=1 cellspacing=0 class=tableOutline><tr><td>
												<table width=598 border=0 cellpadding=3 cellspacing=0>
													<tr><td height=10 colspan=2 class=tableInner1></td></tr>
													<tr><td width=100 align=right valign=top class=tableInner1><strong>Zu editierender Antwortbeitrag:</strong><br><br>BBCode-Hilfe <a href=javascript:info('hilfe','bbcode') class=\"highlightAuftableInner\"><img src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\"></a></td><td width=498 align=center class=tableInner1><textarea cols=40 rows=20 name=message>".$middata[text]."</textarea></td></tr>
													".($president_ids[$id] ? "<tr class=tableInner1><td width=100 height=30></td><td width=498 align=center><strong>Beitrag löschen:</strong> <input type=checkbox value=\"1\" name=delete></td></tr>":"")."
													<tr class=tableInner1><td width=100 height=50></td><td width=498 align=center><input type=submit value=\"Editieren\"></td></tr>
												</table>
											</td></tr></table></form>";
								}
							}	else { f("Kein Zugriff auf dieses Thema!$zurueck"); }
						}	else { f("Ungültiger Beitrag!$zurueck");}
					} else { f("Sie haben keine Berechtigung diesen Beitrag zu editieren!$zurueck"); }
				}	else { f("Ungültigen Beitrag gewählt!$zurueck");}
			}	else { f("Kein Beitrag zum Editieren ausgewählt!$zurueck");}
	}
	elseif ($action == "view")	{
		if ($tid)	{
			$tiddata = assoc("select bid, title, messages,time from board_subjects where tid=$tid");
			if ($tiddata)	{
				if ($tiddata[bid] == $bid)	{
					$newdata = new_and_not_yet_seen($tid);
					if ($newdata and $newdata[todel] == 0) { $queries[] = "update board_subjects_new set todel=1 where tid=$tid"; };
					$anzahl_seiten = ceil($tiddata[messages]/BPS);
					if ($seite == "last"): $seite = $anzahl_seiten; endif;
					if ($newest && $newdata) { $seite = ceil(single("select count(*) from board_messages where mid <= ".$newdata[oldest_new_mid]." and tid=$tid")/BPS);};
					if ($seite && $seite * BPS > $tiddata[messages]): $seite = $anzahl_seiten; endif;
					if (!$seite): $seite = 1; endif;
					$anzahl_nachrichten = ($seite - 1) * BPS;
					if ($anzahl_seiten > 1)	{
						if ($seite > 1)	{
							$vorherige="<a href=$page.php?action=view&tid=$tid&seite=".($seite-1)." class=linkAufsiteBg>Vorherige</a> ";
						}
						if ($seite < $anzahl_seiten)	{
							$naechste=" <a href=$page.php?action=view&tid=$tid&seite=".($seite+1)." class=linkAufsiteBg>Nächste</a>";
						}
						for ($i = 1; $i <= $anzahl_seiten; $i++)	{
							if ($i == $seite)	{
								if ($i != $anzahl_seiten)	{ $add .= "<b><font class=siteGround>$i</font></b>, ";	}
								else 						{ $add .= "<b><font class=siteGround>$i</font></b>";	}
							}
							else	{
								if ($i != $anzahl_seiten)	{ 	$add .= "<a href=$page.php?action=view&tid=$tid&seite=$i class=linkAufsiteBg>$i</a>, ";	}
								else						{	$add .= "<a href=$page.php?action=view&tid=$tid&seite=$i class=linkAufsiteBg>$i</a>";	}
							}
						}
						$outeroutputtable .= "<tr><td align=right class=siteGround colspan=2>Seite ($vorherige$add$naechste)</td></tr>";
					}

					$middata = assocs("select mid,time,kid,text from board_messages where tid=$tid order by time asc LIMIT $anzahl_nachrichten,".BPS);

					foreach ($middata as $vl)	{
						$count == 0 ? ($classchoice = "tableInner1") : ($classchoice = "tableInner2");
						$count == 0 ? ($count = 1) : ($count = 0);
						if (!$kdata[$vl[kid]]): $kdata[$vl[kid]] = assoc("select id,rulername,syndicate,image,rid from status where id=".$vl[kid]); endif;
						$vl[text] = preg_replace("/\n\r?\f?/", "<br>", $vl[text]);
						$anchor = "<a name=".$vl[mid]."></a>";
						if ($vl[mid] == $newdata[oldest_new_mid]): $anchor .= "<a name=newest></a>"; endif;
						if ($vl[time] == $tiddata[time]): $anchor .= "<a name=last></a>"; endif;
						if ($newdata and $newdata[time_new_mid] <= $vl[time]): $new_or_old_img = "posticonnew.gif"; else: $new_or_old_img = "posticon.gif"; endif;
						$ausgabe_beitraege .= "
							<tr>
								<td width=21% align=left valign=top class=\"$classchoice\" nowrap>$anchor<strong>".$kdata[$vl[kid]][rulername]."</strong><br>von<br><strong>".$kdata[$vl[kid]][syndicate].(($bid > 20000 && $bid < 60000) ? " (#".$kdata[$vl[kid]][rid].")":"").((((($kdata[$vl[kid]][rid] && $kdata[$vl[kid]][rid] == $status[rid]) or !$kdata[$vl[kid]][rid]) && $kdata[$vl[kid]][show_emogames_name]) or ($kdata[$vl[kid]][show_emogames_name] == 2 && $page == "allianzboard")) ? "<br><i>(".$emogames_usernamen[$vl[kid]][username].")</i>":"")."</strong>".($kdata[$vl[kid]][image] ? "<br><br><table border=0 cellpadding=0 cellspacing=1 class=tableOutline align=center><tr><td class=tableInner1 align=center valign=middle><img src=\"".WWWDATA."konzernimages/konzern_".$vl[kid].".".$kdata[$vl[kid]][image]."\" border=0></td></tr></table>":"")."</td>
								<td class=\"$classchoice\" colspan=2 width=79% valign=top>".umwandeln_bbcode($vl[text])."</td>
							</tr>
							<tr>
								<td width=69% class=\"$classchoice\" colspan=2 valign=top nowrap><img src=\"".$ripf."$new_or_old_img\" border=0 align=absmiddle> ".datum("d.m.Y, H:i", $vl[time])." </td><td class=\"$classchoice\" width=10% align=\"right\"><a href=\"$page.php?action=reply&tid=$tid&mid=".$vl[mid]."\" class=linkAuftableInner>Zitieren</a>&nbsp;-&nbsp;<a href=\"$page.php?action=edit&mid=".$vl[mid]."\" class=linkAuftableInner>Edit</a>&nbsp;</td>
							</tr>
							";
					}

					$ausgabe .= "
						<table width=598 border=0 cellpadding=4 cellspacing=1>
							<tr><td align=left class=highlightAufSiteBg colspan=2></td></tr>
							<tr><td height=10 colspan=2></td></tr>
							<tr><td align=left width=50% class=siteGround><strong><a href=$page.php class=linkAufsiteBg>Themenübersicht</a></strong> </td><td align=right width=50% class=siteGround><strong><a href=$page.php?action=reply&tid=$tid class=linkAufsiteBg>Antworten</a></strong></td></tr>
							$outeroutputtable
						</table>
						<table width=598 border=0 cellpadding=0 cellspacing=0 class=tableOutline><tr><td>
							<table width=598 border=0 cellpadding=4 cellspacing=1>
								<tr><td colspan=3 align=left class=tableHead><strong>".$tiddata[title]."</strong></tr>
								$ausgabe_beitraege
							</table>
						</td></tr></table>
						<table width=598 border=0 cellpadding=4 cellspacing=1>
							$outeroutputtable
							<tr><td align=left width=50% class=siteGround><strong><a href=$page.php class=linkAufsiteBg>Themenübersicht</a></strong> </td><td align=right width=50% class=siteGround><strong><a href=$page.php?action=reply&tid=$tid class=linkAufsiteBg>Antworten</a></strong></td></tr>
						</table>";
				}	else { f("Kein Zugriff auf dieses Thema!$zurueck"); }
			}	else { f("Thema existiert nicht!$zurueck");}
		}	else { f("Ungültiges Thema gewählt!$zurueck");}
	}
} // Darf Forum anschauen




db_write($queries, 1);
db_write($queries2);



//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


function initialize($bid, $id) {
	if ($id && $bid) {
		global $time, $id_data, $queries, $action; #id_data kommt aus game.php von der sessionid, wichtig ist hier angelegt_bei
		$lastklicktime = single("select lastklicktime from board_boards_lastklick where kid=$id and bid=$bid");
		if (!$action or $action == "view" or $action == "markforumread") {
			if (!$lastklicktime) { $lastklicktime = $time; select("insert into board_boards_lastklick (lastklicktime, kid, bid) values ($time, $id, $bid)"); }
			else { select("update board_boards_lastklick set lastklicktime=$time where kid=$id and bid=$bid"); }
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
	$tids = singles("select tid from board_subjects where bid=$bid");
	if ($tids) {
		select("delete from board_subjects_new where todel=1 and kid=$id and tid in (".join(",", $tids).")");
		$newtids = assocs("select board_subjects_new.tid, board_subjects_new.time_new_mid, board_messages.time from board_subjects_new,board_messages where board_messages.tid in (".join(",", $tids).") and board_subjects_new.tid in (".join(",", $tids).") and board_subjects_new.oldest_new_mid=board_messages.mid", "tid");
		foreach ($newtids as $ky => $vl) {
			if ($vl[time] != $vl[time_new_mid]) { select("update board_subjects_new set time_new_mid=".$vl[time]." where tid=$ky"); }
		}
	}
}
function search_new_threads($bid, $id, $lastklicktime) {
	global $queries;
	$tids = array();
	$tids = singles("select tid from board_subjects where bid=$bid and time > $lastklicktime");
	if ($tids) {
		$newstuff = assocs("select mid, time, tid, kid from board_messages where tid in (".join(",", $tids).") and time > $lastklicktime", "mid");
		foreach ($newstuff as $ky => $vl) {
			if (!$newtiddata[$vl[tid]] or $newtiddata[$vl[tid]][time] > $vl[time]): $newtiddata[$vl[tid]] = array("time" => $vl[time], "mid" => $vl[mid], "kid" => $vl[kid]); endif;
		}
	}
	$alreadynewstuff = assocs("select tid, todel, oldest_new_mid, time_new_mid from board_subjects_new where kid=$id", "tid");
	if ($newtiddata) {
		foreach ($newtiddata as $ky => $vl) {
			if ($alreadynewstuff[$ky] and $vl[kid] != $id) {
				if ($alreadynewstuff[$ky][todel]) {
					select("update board_subjects_new set todel=0, oldest_new_mid=".$vl[mid]." where kid=$id and tid=$ky");
					$alreadynewstuff[$ky][oldest_new_mid] = $vl[mid];
					$alreadynewstuff[$ky][todel] = 0;
				}
			}
			elseif (!$alreadynewstuff[$ky]) {
				$queries[] = "insert into board_subjects_new (kid, tid, oldest_new_mid, time_new_mid".($vl[kid] == $id ? ",todel":"").") values ($id, $ky, ".$vl[mid].", ".$vl[time].($vl[kid] == $id ? ",1":"").")";
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
