<?
ob_start();


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$moneygive=floor($moneygive);$energygive=floor($energygive);$metalgive=floor($metalgive);$sciencepointsgive=floor($sciencepointsgive);
$moneyget=floor($moneyget);$energyget=floor($energyget);$metalget=floor($metalget);$sciencepointsget=floor($sciencepointsget);
$safety=floor($safety);
$pm_als_mail=floor($pm_als_mail);


//$passwordaction = htmlentities($passwordaction,ENT_QUOTES);
$konzernaction = htmlentities($konzernaction,ENT_QUOTES);
$konzernaction2 = htmlentities($konzernaction2,ENT_QUOTES);
$race = htmlentities($race,ENT_QUOTES);
if ($next): $next = floor($next); endif;
$inners = array("safety","pwsend","design","pm_als_mail","acceptall","stopvacation", "nc", "ra","sh","berater", "berater1","np","changeset","tutstart","tutend", "mailbestaetigung", "menuchange", "change_queue_priority","changepath", "show_emogames_name", "hideTipps");
if (!in_array($inner,$inners)) {
    unset($inner);
}
$konzernactions = array("reset","defect", "defect_gezielt", "deletekonzern","vacation");
if (!in_array($konzernaction,$konzernactions)) {
    unset($konzernaction);
}

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

/*
$id_being_killed = $id;
list($username, $rid, $syndicate, $tmail,$tvorname,$tnachname,$emogames_id) = row("select users.username,status.rid,status.syndicate,users.email,users.vorname,users.nachname,users.emogames_user_id from users,status where users.konzernid=".$id_being_killed." and status.id=".$id_being_killed);
pvar($emogames_id);
    EMOGAMES_update_syndicates_konzernid($emogames_id,"0");
*/




//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//
list($username,$accountid,$email,$sygnatur, $sygnatur_background, $hidetipps) = row("select username,id,email,sygnatur,sygnatur_background,hidetipps from users where konzernid = ".$status{id});
$gpacks = assocs("select * from gpacks where visible=1 and template_id=".$status['template_id']." order by sortby asc");
$templates = assocs("select * from templates where visible=1 order by sortby asc");
$vac_activated = single("select starttime from options_vacation where user_id = $status[id] and starttime > $time");
$password = EMOGAMES_getPasswordFromUsername($username);
$queries = array();
$races = assocs("select * from races where active=1");
$allowed = 0;
$maxdefect = 3; // es kann nur 3 mal pro runde defected werden
$maxreset = 3; // es kann nur 3 mal pro runde reseted werden
$time_between_reset = 12 * 3600; // Konzern kann nur alle 60 min resettet werden

if ($race) {
    foreach ($races as $key => $value) {
        if ($key == $race) {$raceok = 1;break;}
    }
}
$raceok == 1 ? 1 : $race = "";
if ($konzernaction != $konzernaction2) {
    f("Die Werte der beiden Aktionsfelder stimmen nicht überein. Die gewünschte Aktion wurde nicht ausgeführt.");
    $konzernaction = "";
}

$mentorflag = single("select is_mentor from users where konzernid = $id");
$defect_gezielt_erlaubt = $mentorflag ? 0 : 0; #($status[paid] && !single("select count(*) from options_defect where user_id = $id and gezielt = 1 and time > $globals[roundstarttime]") && notStartedInGroup($status[id])) ? 1 : 0;

//if ($game_syndikat[synd_type] == "noob-inactive") $defect_gezielt_erlaubt = 1;

function notStartedInGroup($id) {
	for ($i = 1; $i <= MAX_USERS_A_GROUP; $i++) {
		$groupcols[] = "u$i = $id";
	}
	return (!single("select count(*) from groups where ".join(" or ", $groupcols)));
}

$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

if ($game[name] != "Syndicates Testumgebung") $adminlogin = false; // in Testumgebung soll mit Admin-Logon gelöscht werden können.
define(WAR_PUNISHMENT,0.24); // % vom Land, die beim Krieg dem Gegner zugeschrieben werden, wenn man in den u-mod geht oder resettet

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

//							Berechnungen									//



//**********************************************************
//         Acceptall Überweisungen automatisch annehmen/abgeben
//**********************************************************

if ($inner === "acceptall") {
    $action = "update status set moneygive = $moneygive,energygive = $energygive,metalgive = $metalgive,sciencepointsgive = $sciencepointsgive,
				 moneyget = $moneyget,energyget = $energyget,metalget = $metalget,sciencepointsget = $sciencepointsget
				 where id =".$status{id};
    $status[moneygive] = $moneygive;$status[energygive] = $energygive;$status[metalgive] = $metalgive;$status[sciencepointsgive] = $sciencepointsgive;
    $status[moneyget] = $moneyget;$status[energyget] = $energyget;$status[metalget] = $metalget;$status[sciencepointsget] = $sciencepointsget;
    select($action);
}


//**********************************************************
//                         Changepath
//**********************************************************

if ($inner === "changepath") {

	$path = preg_replace("/--/","",$path);
	$path = preg_replace("/#/","",$path);
	$path = preg_replace("/\\\/","/",$path);
	strlen($path) > 1 ? $bstd = 0 : $bstd = 1;
	select("update status set imagepath='$path',buildingstd=$bstd where id = $id");
	$status[imagepath] = $path;
	$status[buildingstd] = $bstd; 
}

if ($_POST['inner'] == "changeset") {
	if ($_POST['gpack_id']) {
		$gpack_id = floor($_POST['gpack_id']);
		$gpack = assoc("select * from gpacks where gpack_id=$gpack_id");
		if ($gpack[visible]) {
			$additional_msg = '';
			if ($gpack_default) {
				$additional_msg = 'Sie haben das Grafikpaket als rundenübergreifenden Standard festgelegt.';
				select("UPDATE users SET gpack_id=$gpack_id WHERE konzernid='".$status['id']."'");
			}
			s("Sie verwenden jetzt das Grafikpaket <b>$gpack[name]</b> ".$additional_msg);
			$status['gpack_id'] = $gpack_id;
			select("update status set gpack_id=$gpack_id where id=".$status['id']."");
			setGPathsFromGpack($gpack);
		}
		else {
			f("Das angegebene Grafikpaket wurde nicht gefunden");
		}
	}
}

$tpl->assign('GPACK_IS_DEFAULT', $status['gpack_id'] == single("SELECT gpack_id FROM users WHERE konzernid='".$status['id']."'"));

if ($_POST['inner'] == "changetpl") {
	if ($_POST['template_id']) {
		$template_id = floor($_POST['template_id']);
		$templ = assoc("select * from templates where template_id=$template_id");
		if ($templ[visible]) {
			s("Sie verwenden jetzt das Template <b>$templ[name]</b>");
			$status[template_id] = $template_id;
			select("update status set template_id=$template_id where id=".$status[id]."");
			$gpacks = assocs("select * from gpacks where visible=1 and template_id=".$status['template_id']." order by sortby asc");
			$tpl->clear_compiled_tpl();
			setTemplatePaths($templ);
			$gpack_id = single("select gpack_id from gpacks where race_default='".$status['race']."' and template_id=".$status[template_id]."");
			$gpack = assoc("select * from gpacks where gpack_id=$gpack_id");
			setGPathsFromGpack($gpack);
		}
		else {
			f("Das angegebene Template wurde nicht gefunden");
		}
	}
}


//**********************************************************
//                         Design
//**********************************************************

if ($inner === "design") {
    if ($status{classic} && !$classic) {
        $status{classic} = "0";
        $action = "update status set classic = 0 where id =".$status{id};
    }
    if (!$status{classic} && $classic) {
        $status{classic} = "1";
        $action = "update status set classic = 1 where id =".$status{id};
    }
    if ($action) {array_push($queries,$action);}
}
if ($status{classic}) {$showclassic = "checked";}

//**********************************************************
//                         safety
//**********************************************************

if ($inner === "safety") {
	select("update status set safety=".$safety." where id=".$status[id]);
	$status[safety] = $safety;
}


//**********************************************************
//                         pm_als_mail
//**********************************************************

if ($inner === "pm_als_mail") {
	select("update status set pm_als_mail=".$pm_als_mail." where id=".$status[id]);
	$status[pm_als_mail] = $pm_als_mail;
}



//**********************************************************
//                         pwsend
//**********************************************************

if ($inner === "pwsend") {
    s("Ihr Passwort wurde an die von ihnen angegebene Emailadresse versandt");
    $betreff = "Passwortanfrage";
    $message = "Ihr syndicates Passwort lautet:\n`".$password."`\n\nDas Syndicates Entwicklerteam\nhttp://syndicates-online.de";
    $tmail = $email;
    $to = $username;
    sendthemail($betreff,$message,$tmail,$to);
    select("insert into options_pwsend (user_id,time,password) values($accountid,$time,'$password')");
}


//**********************************************************
//                         Passwort prüfen
//**********************************************************

if ($konzernaction) {
    if($adminlogin) {
		$allowed = 1;
	}
    else {
		if ($passwordaction) {
			if ($password == md5($passwordaction)) {
				$allowed = 1;
			}
			else {
				pwerror("Sie haben ein falsches Passwort angegeben.");
			}
		}
		else {pwerror("Sie haben ihr Passwort nicht angegeben.");}
    }
}

//**********************************************************
//                         Mailbestätigung bei Angriff / Krieg ein / ausschalten
//**********************************************************

if ($inner === "mailbestaetigung") {
	$status[send_info_mails][1] = $sendmail_on_attack ? "1" : "0";
	$status[send_info_mails][2] = $sendmail_on_war ? "1" : "0";
	select("update status set send_info_mails='1".($status[send_info_mails][1]).($status[send_info_mails][2])."' where id = $id");
}
	$sendmail_on_attack_checked = $status[send_info_mails][1] ? " checked" : "";
	$sendmail_on_war_checked = $status[send_info_mails][2] ? " checked" : "";

//


//**********************************************************
//                      Defect
//**********************************************************
if(
	( $konzernaction == "anfaenger_bereich_verlassen" ) 
	|| 
	( $allowed && $konzernaction == "defect" )
)
{
	if( getServertype() == "basic" )
	{
		if ($konzernaction == "anfaenger_bereich_verlassen") 
		{
			$konzernaction = "defect";
			$out_of_anfaengerbereich = 1;
		}

		$max_users_a_syndicate = MAX_USERS_A_SYNDICATE;
		$min_users_a_closed_syndicate = 11;
	
	    if ($globals[roundstatus] != 0)	
	    {
	        if (!in_protection($status)) 
	        {
	       		$count = single("select count(*) from options_defect where user_id =".$status{id}." and time > $globals[roundstarttime]");
				$mentorflag = single("select is_mentor from users where konzernid = $id");
	            if ($count < $maxdefect && !$mentorflag) { // OR $mentorflag) { // seit dem Mentorenprogramm können Mentoren nicht mehr wechseln. Runde 44; Oktober 2009
					if ($game_syndikat['atwar']) {
						f("Sie können Ihr Syndikat nur in Friedenszeiten wechseln. Warten Sie ab, bis der/die aktuelle(n) Krieg(e) beendet wurden und versuchen Sie es dann erneut.");
					}
					elseif ($status{podpoints} < 0) 
					{
						
					}
					else 
					{
						$type = "normal";
						if ($status[isnoob]) $type = "noob";
						if ($status[isnoob] && $out_of_anfaengerbereich) $type = "normal";
						// neue rid berechnen und boardids berechnen
						$old_rids_DEFECT = singles("select ridbefore from options_defect where user_id = $id and time > ".$globals['roundstarttime']);
						$old_rids_KICK = singles("select rid from politik_kick where kicked = $id and time > ".$globals['roundstarttime']);
						$old_rids = array_merge($old_rids_DEFECT, $old_rids_KICK);
						$old_rids[] = $status[rid];
						$newrid = get_an_empty_syndicate();
						while (single("select atwar from syndikate where synd_id = '$newrid'")) {
							$old_rids[] = $newrid;
							$newrid = get_an_empty_syndicate();
						}
						if ($newrid) {
							/*
							$oldboard = single("select board_id from syndikate where synd_id =".$status{rid});
							$newboard = single("select board_id from syndikate where synd_id =".$newrid);
							*/
							// Db Queries
							if ($out_of_anfaengerbereich) {
								$queries[] = "update status set isnoob = 0 where id = ".$status[id];
								$isnoob_add_for_stats = ",isnoob=0";
							} else{$isnoob_add_for_stats = "";}
							$action ="update status set podpoints=0,rid=".$newrid." where id=".$status{id};
							array_push($queries,$action);
							$action ="update ".$globals{statstable}." set rid=".$newrid."$isnoob_add_for_stats where round=$globals[round] and konzernid=".$status{id};
							array_push($queries,$action);
							$action ="insert into options_defect (user_id,ridbefore,ridafter,time) values ($id,".$status{rid}.",$newrid,$time)";
							array_push($queries,$action);
	
							// Hier auf neues Towncrier Konzept warten
							$message="Der Konzern <b>".$status{syndicate}."</b> hat unser Syndikat aus wirtschaftlichen Interessen verlassen.";
							$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',1)";
							array_push($queries,$action);
							$message="Der Konzern <b>".$status{syndicate}."</b> tritt aus wirtschaftlichen Interessen unserem Syndikat bei.";
							$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$newrid.",'".$message."',1)";
							$action = "UPDATE `status` SET `lager_prohibited` = '0', may_access_boards = '1' WHERE `id` = $id";
							array_push($queries,$action);
	
							/*
							// Forenberechtigungen neu setzen...
	
							$board_user_id = single("select user_id from forum_users where benutzername='".$username."'");
							$action = "update forum_users_boardinfo set board_id='".$newboard."' where board_id='".$oldboard."' and user_id='".$board_user_id."'";
							array_push($queries,$action);
							// Db Queries Ende
							*/
							player_leave_syndicate($status{id},$status{rid});
							$status{rid} = $newrid;
							player_join_syndicate($status{id},$newrid);
	
							$noch = ($maxdefect-1)-$count;
							s("Sie haben ihr Syndikat gewechselt. Sie sind jetzt in Syndikat #".$newrid.". Sie können diese Runde nur noch ".$noch." mal ihr Syndikat wechseln.");
							if ($out_of_anfaengerbereich) i("Du hast den Anfängerbereich soeben verlassen.<br>Von jetzt an bist du nicht mehr vor den erfahreneren Spielern geschützt.<br>Dein Anfängerstatus wurde aufgehoben und du kannst jetzt keine Spieler mehr aus Anfängersyndikaten angreifen.<br>");
						}
						else { f("Sie können ihr Syndikat momentan leider nicht wechseln, da alle vorhandenen Syndikate besetzt sind. Versuchen sie es zu einem späteren Zeitpunkt noch einmal."); }
	                }
				}
	            else {
	                f("Sie können nur drei mal pro Runde das Syndikat wechseln.");
	            }
	        } # if nicht in protection
	        else {
	            f("Sie können ihr Syndikat erst wechseln, wenn sie nicht mehr unter Schutz stehen");
		    if ($mentorflag) f("Mentoren können ihr Syndikat nicht wechseln.");
	        }
	    } # if ($roundstatus != 0) -> ENDE
	    else {
	        f("Sie können das Syndikat erst dann wechseln, wenn die Runde angefangen hat!");
	    }
	}
	else
	{
		f("Auf diesem Server ist es nicht mehr möglich, das Syndikat zu wechseln.");
	}
}


//**********************************************************
//                      Defect_gezielt
//**********************************************************

if ($konzernaction == "defect_gezielt" && $defect_gezielt_erlaubt) 
{
	if( getServertype() == "basic" )
		{
		$max_users_a_syndicate = $mentorflag ? 10000 : MAX_USERS_A_SYNDICATE;
	    if ($globals[roundstatus] != 0)	{
	        if (!in_protection($status)) {
	        $count = single("select count(*) from options_defect where user_id =".$status{id}." and time > $globals[roundstarttime]");
		if ($mentorflag) $count = 0;
	            if ($count < $maxdefect) {
					if ($game_syndikat['atwar']) {
						f("Sie können Ihr Syndikat nur in Friedenszeiten wechseln. 
							Warten Sie ab, bis der/die aktuelle(n) Krieg(e) beendet wurden und versuchen Sie es dann erneut.");
					}
					elseif ($status{podpoints} < 0) {
						f("Sie können ihr Syndikat erst wechseln, wenn sie keine Schulden mehr beim Syndikatslager haben!");
					}
					else  {
						if ($ia) {
							$trotzdem = 0;
							if ($allowed) {
								if ($syndnummer) {
									$syndwechseldata = assoc("select password, name, atwar, synd_type from syndikate where synd_id='".floor($syndnummer)."'");
									if ($syndwechseldata) {
										if (!$syndwechseldata[atwar]) {
											if ($syndwechseldata[password] == $syndpassword || $mentorflag) {
												$anzahl_spieler = single("select count(*) from status where rid = '".floor($syndnummer)."'");
												if ($anzahl_spieler < $max_users_a_syndicate) {
													if (!single("select count(*) from options_defect where ridafter = '".floor($syndnummer)."' and gezielt = 1 and time > ($time-24*3600)")) {
														$newrid = floor($syndnummer);
														if ($syndwechseldata[synd_type] == "normal") $queries[] = "update status set isnoob = 0 where id = ".$status[id];
														$action ="update status set podpoints=0,rid=".$newrid." where id=".$status{id};
														array_push($queries,$action);
														$action ="update ".$globals{statstable}." set rid=".$newrid.",isnoob=0 where round=$globals[round] and konzernid=".$status{id};
														array_push($queries,$action);
														$action ="insert into options_defect (user_id,ridbefore,ridafter,time, gezielt) values ($id,".$status{rid}.",$newrid,$time, 1)";
														array_push($queries,$action);
	
														// Hier auf neues Towncrier Konzept warten
														$message="Der Konzern <b>".$status{syndicate}."</b> hat unser Syndikat aus wirtschaftlichen Interessen verlassen.";
														$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',1)";
														array_push($queries,$action);
														$message="Der Konzern <b>".$status{syndicate}."</b> tritt aus wirtschaftlichen Interessen unserem Syndikat bei.";
														$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$newrid.",'".$message."',1)";
														$action = "UPDATE `status` SET `lager_prohibited` = '0', may_access_boards = '1' WHERE `id` = $id";
														array_push($queries,$action);
														player_leave_syndicate($status{id},$status{rid});
														$status{rid} = $newrid;
														player_join_syndicate($status{id},$newrid);
	
														$noch = ($maxdefect-1)-$count;
														s("Sie haben ihr Syndikat gezielt gewechselt. Sie sind jetzt in Syndikat 
															\"<b>".$syndwechseldata[name]." (#".$newrid.")</b>\". Sie können diese 
															Runde nur noch ".$noch." mal ihr Syndikat wechseln. Ein gezieltes 
															Wechseln ist nun nicht mehr möglich.");
													} else { 
														f("In dieses Syndikat hat innerhalb der letzten 24h bereits ein Spieler gezielt 
															hineingewechselt. Bitte warten Sie noch etwas, bis Sie in dieses Syndikat 
															wechseln können."); 
													}
												} else { f("Dieses Syndikat hat bereits die Maximalzahl an möglichen Spielern ($max_users_a_syndicate Spieler)!"); $ia = "";}
											} else { f("Das von Ihnen angegebene Passwort stimmt nicht mit dem Syndikatspasswort überein!"); $ia = "";}
										} else { f("Das von Ihnen gewählte Syndikat befindet sich zurzeit in einem Krieg. Es kann nur in Syndikate gewechselt werden, welche sich in keinem Krieg befinden."); }
									} else { f("Das von Ihnen eingegebene Syndikat mit der Nummer \"<b>$syndnummer</b>\" existiert nicht!"); $ia = "";}
								} else { f("Sie haben keine Syndikatsnummer angegeben"); $ia = ""; }
							} else { $trotzdem = 1; $ia = ""; }
	
						}
						if (!$ia && ($allowed or $trotzdem)) {
							$goon = 0;
							$tpl->assign('DEFECTED_GEZIELT_SHOW', true);
							$tpl->assign('SYNDNUMMER', $syndnummer);
						}
					}
	            } # if $count < 3
	            else 
	            {
	                f("Sie können nur drei mal pro Runde das Syndikat wechseln.");
	            }
	        } # if nicht in protection
	        else 
	        {
	            f("Sie können ihr Syndikat erst wechseln, wenn sie nicht mehr unter Schutz stehen");
	        }
	    } # if ($roundstatus != 0) -> ENDE
	    else 
	    {
	        f("Sie können das Syndikat erst dann wechseln, wenn die Runde angefangen hat!");
	    }
	}
	else
	{
		f("Sie können das Syndikat erst dann wechseln, wenn die Runde angefangen hat!");
	}
}

//**********************************************************
//                      Vacation
//**********************************************************

if ($allowed && $konzernaction == "vacation") {
		if ($globals[roundstatus] != 0)	{
			if($globals['roundendtime'] - $time > 7*24*60*60){
				$lookuptime = $time - 24 * 60 * 60;
				//$numbers = single("select count(*) from attacklogs where aid=".$id." and time > ".$lookuptime." and arid != drid and ginactive != 2");
				$numbers = 0;
				$vacationstaken = single("select count(*) from options_vacation where user_id = $id");
				$vacation_bereits_aktiviert = single("select count(*) from options_vacation where user_id = $id and starttime > $time");
				if (!$vacation_bereits_aktiviert) {
					if (!$numbers && $vacationstaken < 3)	{
						//$action = "update status set alive=2 where id='$id' ";
						//array_push($queries,$action);
						$vactime = get_hour_time($time) + 25 * 3600;
						$mindays = $game_syndikat['atwar'] ? VACATION_MINDAYS_ATWAR : VACATION_MINDAYS;
						$action = "insert into options_vacation (user_id, timestamp, starttime, mindays) values ('$id', '".time()."', '$vactime', $mindays)";
						$vac_activated = $vactime;
						array_push($queries,$action);
						## Schöne Meldung in towncrier schreiben
						$message="Der Konzern <b>".$status{syndicate}."</b> meldet die baldige vorübergehende Aussetzung der Wettbewerbsfähigkeit an.";
						$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',1)";
						array_push($queries,$action);
						//countToWar(); //urlaubsmodus zum krieg zählen dragon12 R61 -> seit 18.10.2012 nur noch bei Aktivierung
						s("Sie haben den Urlaubsmodus erfolgreich aktiviert. <br>Er wird in 24h zu Ende der vollen Stunde in Kraft treten.<br>Bis dahin ist Ihr Konzern noch nicht geschützt.<br>Sie können sich natürlich solange weiterhin in Ihren Konzern einloggen, allerdings keine Angriffe mehr durchführen.");
						//if ($globals[updating] == 0): header ("Location: logout.php"); endif;
					}
					elseif ($vacationstaken >= 3) {
						f("Sie können den Urlaubsmodus höchsten drei mal pro Runde aktivieren, um Mißbrauch zu vermeiden.");
					}
					/*
					else {
						f("Sie haben innerhalb der letzten 24h ein Mitglied eines anderen Syndikates angegriffen, daher können Sie den Urlaubsmodus noch nicht betreten. <br><br>Bitte versuchen Sie es später erneut!");
					}
					*/
				} else { f("Sie haben den Urlaubsmodus bereits aktiviert!"); }
			} else {
				f("In der letzten Woche vor Rundenende kann der Urlaubsmodus nicht mehr aktiviert werden!");	
			}
		} # if ($roundstatus != 0) -> ENDE
		else {
			 f("Sie können den Urlaubsmodus erst aktivieren, sobald die Runde angefangen hat!");
		}
}

//**********************************************************
//                      Stopvacation
//**********************************************************
if ($action == "stopvacation" && false) {
	$queries[] = "update options_vacation set starttime = 1, endtime = 1, activated_by_update=1 where user_id = $status[id] and starttime > $time";
	$message = "Der Konzern ".bold($status[syndicate])." hat die Aktivierung des Urlaubsmodus abgebrochen.";
	$queries[] ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',1)";
	$vac_activated = 0;
	s("Der Urlaubsmodus wurde erfolgreich abgebrochen. Achtung - der Urlaubsmodus kann nur dreimal pro Runde aktiviert werden!");
	// Towncrier eintrag
}



//**********************************************************
//                      Reset
//**********************************************************

if ($allowed && $konzernaction == "reset") {
	$exceptions = array(138);
	/*$inProtectionTime = ($time - $status['createtime'] <= 
6*3600);*/
	if (!$adminlogin && ISRANDOMRUNDE &&
	    !in_array($id, $exceptions) &&
	    $globals['roundstatus'] == 1 &&
	    ($time - $globals['roundstarttime'] <  RESET_SPERRE_NACH_RUNDENSTART) &&
	    getServertype() == "classic") {
		f("Sie können innerhalb der ersten ".ceil(RESET_SPERRE_NACH_RUNDENSTART / 3600)."h nach Rundenstart Ihren Konzern weder zurücksetzen noch löschen. Diese Einschränkung ist notwendig, damit die Konfigurationsphase nicht zum Pushen missbraucht werden kann.");
	}
	else {
		$angriff_last_24h = single("select count(*) from attacklogs where aid = $id and time > ".($time-24 * 3600));
		$forschung_zerstoeren_last_24h = single("select count(*) from spylogs where aid = $id and action = 'killsciences' and time > ".($time-24 * 3600));
		if (!$angriff_last_24h && !$forschung_zerstoeren_last_24h) {
			$lastreset = single("select time from options_reset where user_id=".$status{id}." order by time desc limit 1");
			$amountreset = single("select count(*) from options_reset where user_id=".$status{id}." and time > ".$globals['roundstarttime']);
			$race ? $newrace = $race : $newrace = $status{race};

			if ($newrace != "sl" and $newrace != "uic" and $newrace != "pbf" and $newrace != "nof" and $newrace != "neb"): $newrace = $status{race}; endif;
		
			/* Ausgebaut R48 o19
			if (!$inProtectionTime && ($time - $time_between_reset < $lastreset || $amountreset >= $maxreset)) {
				$hours = round ($time_between_reset/3600);
				f("Sie können ihren Konzern nur $maxreset mal während einer laufenden Runde und höchstens alle $hours Stunden resetten.");
			}
			else
			{*/
				$syndikate_data = assocs("select * from syndikate", "synd_id");
				/*if ($syndikate_data[$status['rid']]['atwar']) 
				{
					$wardata = assocs("select war_id, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3, first_1_lwt, first_2_lwt, first_3_lwt, second_1_lwt, second_2_lwt, second_3_lwt, first_1_landstart, first_2_landstart, first_3_landstart, second_1_landstart, second_2_landstart, second_3_landstart, artefakt_want_first_1, artefakt_want_first_2, artefakt_want_first_3, artefakt_want_second_1, artefakt_want_second_2, artefakt_want_second_3, starttime from wars where status = 1", "war_id");
					$syndikate_warids = array();
					foreach ($wardata as $ky => $vl) 
					{
						foreach ( array ( "first_synd_1", "first_synd_2", "first_synd_3", "second_synd_1", "second_synd_2", "second_synd_3" ) as $vl2 ) {
							if ($vl[$vl2]) 
							{
								// $anzahl_kriege[$vl[$vl2]]++;
								$syndikate_warids[$vl[$vl2]][] = $ky;
							}
						}
					}
					$enemy = array ( "first" => "second", "second" => "first" );
					foreach ($syndikate_warids[$status['rid']] as $warid) 
					{
						if( $wardata[$warid]['starttime'] <= date("U") )
						{
							foreach ( 
								array ( 
										"first_synd_1" => array(
																	'first', 
																	1), 
										"first_synd_2" => array(
																	'first', 
																	2), 
										"first_synd_3" => array(
																	'first', 
																	3), 
										"second_synd_1" => array(
																	'second', 
																	1), 
										"second_synd_2" => array(
																	'second', 
																	2), 
										"second_synd_3" => array(
																	'second', 
																	3) 
									  ) as $ky2 => $vl2 
								) 
							{
								if ($wardata[$warid][$ky2] == $status['rid']) 
								{
									$ownfirstsecond = $vl2[0];
									$ownnumber = $vl2[1];
								}
							}
							// Rausfinden wieviele Gegner beteiligt sind
							$enemies = array();
							for ($i = 1; $i <= 3; $i++) {
								if ($wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i]) {
									$enemies[] = $wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i];
								}
							}
							// Dem Gegner 24% vom Land als erobert gutschreiben
							for ($i = 1; $i <= 3; $i++) {
								if ($wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i]) {
									$queries[] = "update wars set ".$enemy[$ownfirstsecond]."_".$i."_add = ".$enemy[$ownfirstsecond]."_".$i."_add + ".round($status['land'] * WAR_PUNISHMENT / count($enemies))." where war_id = $warid";
	
								}
							}
						}
					}
				}*/
				
				countToWar();
				
				// defaults aus statstable holen für reset
				$default = array();
				$result = select("describe status");
				while ($return = mysql_fetch_array($result)) {
					if($globals['roundstarttime'] > $time && $return[0] == 'may_access_boards') $return[4] = 1; // R4bbiT - 17.06.11 - Wenn man in der Gruppenphase ist, soll man nach nem Resett nicht aus dem Gruppenboard (bzw. den Boards) ausgeschlossen werden
					$default[$return[0]] = $return[4];
				}
				
				mysql_free_result($result);
				
				$newcreatetime = $time;
				if ($globals[roundstatus] == 0): $newcreatetime = $globals[roundstarttime]; endif;

				#echo $newcreatetime;

				// Db Einträge Start
					//echo ("Time2: $time<br>");
					if (!$inProtectionTime && $globals[roundstatus] > 0) {
						$action = "insert into options_reset (user_id,time) values ($id,$time)";
						array_push($queries,$action);
					}
					# neue rasse in statustable

					$describe = assocs("describe status");
					$upstring = "update status set ";
					$dontupdate = array(may_access_boards,rid,lastupdatetime,dsm,dst,id,rulername,syndicate,lastlogintime,createtime,unprotecttime,image,classic,race, send_info_mails, beraterview, imagepath, sue_sort, sue_mode, isnoob,is_mentor,mentor_id);
					foreach ($describe as $key) {
						// if (($key["Field"] == 'may_access_boards') && ($globals['roundstarttime'] > $time)) $key["Default"] = 1; // R4bbiT - 17.06.11 - Wenn man in der Gruppenphase ist, soll man nach nem Resett nicht aus dem Gruppenboard (bzw. den Boards) ausgeschlossen werden
						if (!(in_array($key{"Field"},$dontupdate)) && ($key["Default"] || $key["Default"] == 0)) {
							$upstring .= $key["Field"]."='".$key["Default"]."',";
						}
					}
					$upstring.="createtime=".$newcreatetime.",";
					$upstring.="unprotecttime=".($newcreatetime+PROTECTIONTIME).",";
					$upstring.="race='".$newrace."' where id=$id";
					//echo $upstring;
					array_push($queries,$upstring);

					if ($globals{roundstatus} == 0) {
						$action = ("update status set createtime=".$globals{roundstarttime}." where id = $id");
						array_push($queries,$action);
					}
					// Forschungsbonus für vergangene Tage in der Runde
					// Tage seit Rundenstart ermitteln für den in Runde 34 eingeführten Bonus bei später gestarteten Konzernen
					$days_since_roundstarttime = floor(($time - $globals['roundstarttime']) / 86400);
					$days_since_roundstarttime = max(0, $days_since_roundstarttime); // verhindert negative Werte
					if ($days_since_roundstarttime)
						$queries[] = "update status set later_started_bonus = ".($days_since_roundstarttime*LATER_STARTED_BONUS_DAILY+LATER_STARTED_BONUS_START)." where id = $id";
					
					
					// Alles Zeugs löschen
					deletetables($id);
					$queries[] = "delete from kosttools_forschungsq where konzernid='$id'";

					// neue rasse in rankings
					$message="Der Konzern <b>".$status{syndicate}."</b> hat beschlossen sich wirtschaftlich neu zu orienteren.";
					$action ="insert into towncrier (time,rid,message,kategorie) values ($time,".$status{rid}.",'$message',1)";
					array_push($queries,$action);
					$ridsave = $status[rid];
					$isnoob_safe = $status[isnoob];
					$rulernamesave = $status[rulername];
					$namesave = $status[syndicate];
					$imagepathsave = $status[imagepath];
					unset($status);
					unset($sciences);
					
					$status=$default;	# folgendes - in den Defaultwerten sind z.B. keine Informationen wie Id, Syndicate oder so drin - die Zuweisung auf $status ist allerdings sinnvoll damit 1. das Networth gleich richtig ausgerechnet werden kann und gleich beim Resetten die Standardressourcen in der Ressourcenleiste erscheinen
					$status[id] = $id;	# daher muss $status[id] explizit gesetzt werden damit die nw-function im vergleich (if ($status[id] == $id)) zieht und
					$status{nw} = nw($id); # der Networth ebenfalls aktualisiert wird
					$status[race] = $newrace;
					$status[imagepath] = $imagepathsave;
					
					// neue rasse in statstable setzen
					$action ="delete from stats where round=$globals[round] and konzernid=$status[id]";
					array_push($queries,$action);
					$user = assoc("select * from users where konzernid=$status[id]");
					$queries[] = ("insert into $globals[statstable] (user_id,konzernid,username,syndicate,race,rulername,rid,round, isnoob) values ('$user[id]','$status[id]','$user[username]','$namesave','$status[race]','$rulernamesave','$ridsave',$globals[round], $isnoob_safe)");

					// nw im statustable aktualisieren
					$action ="update status set nw = ".$status{nw}." where id = $id";
					array_push($queries,$action);
				// Db Einträge Ende
				s("Konzern erfolgreich resettet.");
			//}# wenn resettime ok
		} else { 
			f("Sie haben innerhalb der letzten 24h einen Angriff durchgeführt oder die Spionageaktion \"Forschung zerstören\" ausgeführt. 
				Sie müssen daher noch ein wenig warten, bis Sie sich zurücksetzen können.");
		}
	}
}

//**********************************************************
//                      Deletekonzern
//**********************************************************

if ($allowed && $konzernaction == "deletekonzern") {
	$exceptions = array(696);
	if (!$adminlogin  && ISRANDOMRUNDE && (!in_array($id, $exceptions) && ($globals['roundstatus'] == 1 && ($time - $globals['roundstarttime'] < DELETE_SPERRE_NACH_RUNDENSTART))) && getServertype() == "classic") {
		f("Sie können innerhalb der ersten ".ceil(DELETE_SPERRE_NACH_RUNDENSTART / 3600)."h nach Rundenstart Ihren Konzern nicht löschen.");

	} else if ($globals['roundstatus'] == 1 && $time - $status['createtime'] < DELETE_SPERRE_NACH_CREATE && $game['name'] != "Syndicates Testumgebung") {

	    f("Du kannst deinen Konzern erst ".ceil(DELETE_SPERRE_NACH_CREATE / 3600)."h nach Konzernerstellung löschen. 
	    	Wenn Du dich verbaut haben solltest, kannst du stattdessen deinen Konzern resetten.");
	    
	}
	else {
		if(ISRANDOMRUNDE && $globals['roundstatus'] == 0){
			f("Konzernlöschung derzeit nicht möglich, bitte nutzen sie die Resettoption.");
		} else {
			// Auf Angriff innerhlab der letzten 24h checken
			$difference = $time - single("select time from attacklogs where aid = $id and winner = 'a' order by time desc limit 1");
			$need_to_wait = 24*3600-$difference;
			if ($difference >= 24*3600) 
			{
				$syndikate_data = assocs("select * from syndikate", "synd_id");
				/*if ($syndikate_data[$status['rid']]['atwar']) 
				{
					$wardata = assocs("select war_id, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3, first_1_lwt, first_2_lwt, first_3_lwt, second_1_lwt, second_2_lwt, second_3_lwt, first_1_landstart, first_2_landstart, first_3_landstart, second_1_landstart, second_2_landstart, second_3_landstart, artefakt_want_first_1, artefakt_want_first_2, artefakt_want_first_3, artefakt_want_second_1, artefakt_want_second_2, artefakt_want_second_3, starttime from wars where status = 1", "war_id");
					$syndikate_warids = array();
					foreach ($wardata as $ky => $vl) 
					{
						foreach ( array ( "first_synd_1", "first_synd_2", "first_synd_3", "second_synd_1", "second_synd_2", "second_synd_3" ) as $vl2 ) {
							if ($vl[$vl2]) 
							{
								// $anzahl_kriege[$vl[$vl2]]++;
								$syndikate_warids[$vl[$vl2]][] = $ky;
							}
						}
					}
					$enemy = array ( "first" => "second", "second" => "first" );
					foreach ($syndikate_warids[$status['rid']] as $warid) 
					{
						if( $wardata[$warid]['starttime'] <= date("U") )
						{
							foreach ( 
								array ( 
										"first_synd_1" => array(
																	'first', 
																	1), 
										"first_synd_2" => array(
																	'first', 
																	2), 
										"first_synd_3" => array(
																	'first', 
																	3), 
										"second_synd_1" => array(
																	'second', 
																	1), 
										"second_synd_2" => array(
																	'second', 
																	2), 
										"second_synd_3" => array(
																	'second', 
																	3) 
									  ) as $ky2 => $vl2 
								) 
							{
								if ($wardata[$warid][$ky2] == $status['rid']) 
								{
									$ownfirstsecond = $vl2[0];
									$ownnumber = $vl2[1];
								}
							}
							// Rausfinden wieviele Gegner beteiligt sind
							$enemies = array();
							for ($i = 1; $i <= 3; $i++) {
								if ($wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i]) {
									$enemies[] = $wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i];
								}
							}
							// Dem Gegner 24% vom Land als erobert gutschreiben
							for ($i = 1; $i <= 3; $i++) {
								if ($wardata[$warid][$enemy[$ownfirstsecond]."_synd_".$i]) {
									$queries[] = "update wars set ".$enemy[$ownfirstsecond]."_".$i."_add = ".$enemy[$ownfirstsecond]."_".$i."_add + ".round($status['land'] * WAR_PUNISHMENT / count($enemies))." where war_id = $warid";
	
								}
							}
						}
					}
				}*/
				
				countToWar();
							
				kill_den_konzern($id, "konzerndelete");
				select("insert into options_konzerndelete (user_id,syndicate,rid,time) values ($accountid,'".$status{syndicate}."',".$status{rid}.",$time)");
				header ("Location: ../index.php?action=konzerndelete");
			} 
			else 
			{
				f("Sie können Ihren Konzern erst 24h nach Ihrem letzten erfolgreichen Angriff löschen. Dies wird 
					in ".(floor($need_to_wait/3600))."h und ".(floor(($need_to_wait - floor($need_to_wait/3600)*3600)/60))."m möglich sein.<br>
					Diese Regelung ist notwendig, damit Spieler die sich löschen möchten vorher nicht noch einen Kamikazeangriff 
					durchführen können, ohne dass das Opfer die Chance hat sich zu rächen.");
			}
		}
	}
}

//**********************************************************
//                      Urlaubsmodus abbrechen Anzeige
//**********************************************************
if ($vac_activated) {
	
	i("Ihr Konzern wird am ".mytime($vac_activated)." den Urlaubsmodus aktivieren. Der Urlaubsmodus dauert mindestens 3 Tage und kann erst danach beendet werden - Sie können sich so lange nicht mehr in Ihren Konzern einloggen!");
	//<br><br><center><a style=\"color:black;text-decoration:underline;font-size:12px;\" href=\"options.php?action=stopvacation\">Aktivierung des Urlaubsmodus sofort abbrechen</a></center>.");
}


//****** NAMECHANGE
$noupdate = 0;
$namechangesleft = 0;
$namechangeslefttext = "Zukünftig ist keine Änderung mehr möglich.";
if($time < $status['createtime'] + 7 * 86400){
	$namechangesleft = 1;
	$namechangeslefttext = "Es sind innerhalb der ersten 7 Tage nach Konzernerstellung beliebig viele Änderungen möglich. (Missbrauch dieser Funktion kann Strafen nach sich ziehen!)";
	$noupdate = 1;
}

if (!$namechangesleft && $features[KOMFORTPAKET] && !single("select count(*) from options_namechange where round=$globals[round] and konzernid=$id and time > ".($globals['roundstarttime'] + 7 * 86400))) 
{
	$namechangesleft = 1;
	$namechangeslefttext = "Es ist noch eine Änderung möglich.";
	$noupdate = 1;
}
if ($inner == "nc" && $namechangesleft > 0) {
	list($rulername_old, $syndicate_old) = row("select rulername, syndicate from status where id=$id");
	if ($next) {
		$uname = single("select username from users where konzernid=".$status[id]);
		$syndicate = trim($syndicate);
		$syndicate = preg_replace("/ {2,}/", " ", $syndicate);
		//$rulername = trim($rulername);
		//$rulername = preg_replace("/ {2,}/", " ", $rulername);
		if (strlen($syndicate) < 3 or strlen($syndicate) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_&,.? ]/", $syndicate)) { $barrier=1; $syndicateerror=1; }
		//if (strlen($rulername) < 3 or strlen($rulername) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_.? ]/", $rulername)) { $barrier=1; $rulernameerror=1; }
		//$rulername = (ISRANDOMRUNDE ? $uname : $rulername);
		if (!$barrier):  if (single("select count(*) from status where syndicate like '$syndicate' and id != $id")) { $barrier=1; $alreadyexistserror=1; } endif;
		if (!$barrier) {
			//Nicht nötig, weil oben direkt abgefragt wird: $namechangesleft -= 1;
			if ($globals[roundstatus] == 0)	{
				foreach (range(1, MAX_USERS_A_GROUP) as $vl)	{ $users .= "u$vl,";}
				$users = chopp($users);
				$result = assoc("select group_id,open,nachfolger,password,$users from groups where $id in ($users)");
				if ($result[group_id]) {
					foreach (range(1,MAX_USERS_A_GROUP) as $vl) {
						if ($result[u.$vl] && $result[u.$vl] != $id)	{
							$messageinserts[] = "(44, ".$result[u.$vl].", $time, '<b>$rulername_old</b> von <b>$syndicate_old</b> hat seinen Namen in <b>$rulername_old</b> von <b>$syndicate</b> geändert.')";
						}
					}
					if ($messageinserts) {
						$queries[] = "insert into message_values (id, user_id, time, werte) values ".join(",", $messageinserts);
					}
				}
			}
			s("Sie haben den Namen ihres Geschäftsführers/Konzerns erfolgreich geändert. $namechangeslefttext ");
			$queries[] = "insert into towncrier (time, rid, message,kategorie) values ('$time', '".$status[rid]."', '<b>$rulername_old</b> von <b>$syndicate_old</b> hat seinen Namen in <b>$rulername</b> von <b>$syndicate</b> geändert.',1)";
			$queries[] = "insert into options_namechange (round, time, konzernid, old_rulername, new_rulername, old_syndicate, new_syndicate) values ('".$globals[round]."', '$time', '$id', '$rulername_old', '$rulername', '$syndicate_old', '$syndicate')";
			$queries[] = "update status set syndicate='$syndicate' where id='$id'";  // Geschäftsführer kann nicht mehr geändert werden rulername='$rulername', 
			if (!$noupdate) $queries[] = "update status set nc=nc-1 where id='$id'";
			$queries[] = "update stats set syndicate='$syndicate' where konzernid='$id' and round=".$globals[round];
		}
		else {
			$next = 0;
			if ($alreadyexistserror) { f("Dieser Konzernname wird bereits verwendet. Bitte suchen Sie sich einen anderen aus."); }
			if ($rulernameerror) { f("Bitte geben Sie einen gültigen Konzernchef-Namen von mindestens 3 und höchstens 20 Zeichen an. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ Leerzeichen"); }
			if ($syndicateerror) { f("Bitte geben sie einen gültigen Namen für ihren Konzern an. Mindestens 3 Zeichen und höchstens 20 Zeichen. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ & . , Leerzeichen"); }
		}

	}
	if (!$next) {
		$ausgabeblocked = 1;
		$tpl->assign('NAMECHANGE_SHOW', true);
		$tpl->assign('RULERNAME_OLD', $rulername_old);
		$tpl->assign('SYNDICATE_OLD', $syndicate_old);
	}
}

// ***** NAMECHANGE AUSGABE VORBEREITEN
if ($namechangesleft && $globals[roundstatus] <> 2) {
	$tpl->assign('NAMECHANGESLEFTTEXT', $namechangeslefttext);
	$tpl->assign('NAMECHANGESLEFT_SHOW', true);
}

if ($inner == "change_queue_priority") {
	$qtp = $status[queue_tool_priorities];
	$new = "".floor($new)."";
	if ($new) {
		$counter = 0;
		for ($i = 0; $i <= 2; $i++) {
			if ($new[$i] >= 1 && $new[$i] <= 3 && !$already_there[$qtp[$i]]) {
				$already_there[$qtp[$i]] = 1;
				$counter++;
			}
		}
			if ($counter == 3) {
			$queries[] = "update status set queue_tool_priorities = '$new' where id = $id";
			$status[queue_tool_priorities] = "$new";
		}
	}
}

//// ***** Show_Emogames_Name Änderungen
if ($inner == "show_emogames_name") {
	//ab R48 nur im allianzboard ausblenden moeglich
	if ($activate2 == 2) {
		$status[show_emogames_name] = 2;
	}
	else {
		$status[show_emogames_name] = 1;
	}
	$queriesend[] = "update status set show_emogames_name = ".$status[show_emogames_name]." where id = $id";
}


//////****** RANKING ANONYMITY
if ($inner == "ra") {
	if ($ravalue) {
		$status['ranking_anonymity'] = 1;
	} else {
		$status['ranking_anonymity'] = 0;
	}
	select("update status set ranking_anonymity = ".$status['ranking_anonymity']." where id = ".$id);
}

//////****** HELP TEXTS
if ($inner == "sh") {
	if ($shvalue) {
		$status['show_help'] = 1;
	} else {
		$status['show_help'] = 0;
	}
	select("update status set show_help = ".$status['show_help']." where id = $id");
}

//////****** BERATER anzeigen im Menü
if ($inner == "berater1") {
	if ($berater_show) {
		$status['berater_show'] = 1;
	} else {
		$status['berater_show'] = 0;
	}
	select("update status set berater_show = ".$status['berater_show']." where id = $id");
}

//////****** BERATER Uhrzeit statt Ticks
if ($inner == "berater") {
	if ($beraterview) {
		$status['beraterview'] = 1;
	} else {
		$status['beraterview'] = 0;
	}
	select("update status set beraterview = ".$status['beraterview']." where id = $id");
}

//////****** TIPPS anzeigen/verstecken
if ($inner == "hideTipps") {
	if ($sethidetipps) {
		$hidetipps = 1;
	} else {
		$hidetipps = 0;
	}
	select("update users set hidetipps = ".$hidetipps." where id = ".$accountid);
}

//////****** NOTESPIN
if ($inner == "np") {
	if ($npvalue) {
		$status['notespin'] = 1;
	} else {
		$status['notespin'] = 0;
	}
	$queriesend[] = "update status set notespin = ".$status['notespin']." where id = $id";
}

// ***** SEITENMENÜ KONFIGURATION
if ($inner == "menuchange") {
	if ($subaction == "activate") {
		$queries[] = "update status set mymenue = 5 where id = $id";
		$status[mymenue] = 5;
		s("Sie haben das selbst konfigurierbare Seitenmenü erfolgreich aktiviert.");
		$user_id = single("select id from users where konzernid = $id");
		select("update mymenue set konzernid = $id where user_id = $user_id");
	}
	if ($subaction == "deactivate") {
		$queries[] = "update status set mymenue = 0 where id = $id";
		$status[mymenue] = 0;
		s("Sie haben das selbst konfigurierbare Seitenmenü erfolgreich deaktiviert.");
	}
	if ($subaction == "changeposition") {
		if ($position < 1 or $position > 5) { $position = $status[mymenue]; }
		$queries[] = "update status set mymenue = $position where id = $id";
		$status[mymenue] = $position;
		s("Sie haben die Position des selbst konfigurierbaren Seitenmenüs erfolgreich geändert.");
	}
	if ($subaction == "addlink") {
		$highestposition = single("select position from mymenue where konzernid = $id order by position desc limit 1");
		if (!$highestposition): $highestposition = 0; endif;
		$highestposition++;
		if ($highestposition <= 20) {
			$user_id = single("select id from users where konzernid = $id");
			select("insert into mymenue (user_id, konzernid, position, name, address, new_window) values ('$user_id', '$id', '$highestposition', '".trim(htmlentities($linkname, ENT_QUOTES))."', '".trim(htmlentities($linkurl, ENT_QUOTES))."',".($new_window ? 1:0).")");
			s("Ihr Link wurde erfolgreich hinzugefügt.");
		}
		else { f("Sie haben bereits 20 Linkeinträge. Löschen Sie zunächst einen Link, bevor Sie einen weiteren hinzufügen können."); }
	}
	if ($subaction == "changelinkposition") {
		$linkdata = assocs("select * from mymenue where konzernid = $id order by position asc", "position");
		$anzahl_queued = count($linkdata);
		$pos = floor($pos);
		if ($anzahl_queued)	{
			if ($anzahl_queued > 1)	{
				if ($up or $down)	{
					if ($pos >= 1 && $pos < $anzahl_queued && $up)	{
						$validq = 1;
					}
					elseif (($pos >= 2  && $anzahl_queued >= $pos) && $down)	{
						$validq = 1;
					}
					if ($validq)	{
								if ($up):
									select("update mymenue set position=$pos+1 where id=".$linkdata[$pos][id]);
									select("update mymenue set position=$pos where id=".$linkdata[$pos+1][id]);
								endif;
								if ($down):
									select("update mymenue set position=$pos-1 where id=".$linkdata[$pos][id]);
									select("update mymenue set position=$pos where id=".$linkdata[$pos-1][id]);
								endif;
					}
					else { f("Fehler!");}
				}
				else { f("Ein Parameter fehlt!"); }
			}
			else { f("Sie haben nur eine Forschung in der Warteschlange stehen. Wo es keine Reihenfolge gibt, kann auch keine Reihenfolge geändert werden ;)."); }
		}
		else { f("Sie haben keine Forschung in der Warteschlange stehen. Welche Forschung möchten Sie da bitteschön ändern ?");}
	}
	if ($subaction == "deletelink") {
		$linkdata = assocs("select * from mymenue where konzernid = $id order by position asc", "id");
		$linkid = floor($linkid);
		if ($linkdata[$linkid][id]) {
			select("delete from mymenue where id = $linkid");
			select("update mymenue set position=position-1 where konzernid = $id and position > ".$linkdata[$linkid][position]);
			s("Der Link wurde erfolgreich gelöscht.");
		} else { f("Ungültige Linkangabe!"); }
	}
	if ($subaction == "linkchange") {
		$user_id = single("select id from users where konzernid = $id");
		select("update mymenue set name='".trim(htmlentities($linkname, ENT_QUOTES))."', address='".trim(htmlentities($linkurl, ENT_QUOTES))."', new_window=".($new_window ? "1" : "0")." where id='".floor($linkid)."'");
		s("Ihr Link wurde erfolgreich geändert.");
	}
}

// Eigenes Menü
if ($status[mymenue]) {
	$linkdata = assocs("select * from mymenue where konzernid = $id order by position asc");
	$tpl->assign('LINKDATA_COUNT', count($linkdata));
	if ($linkdata) {
		$tpl->assign('LINKDATA', $linkdata);
	} else { 
		// noch keine Links eingetragen
	}
}

// Prioritätseinstellung für Queue-Tools
if ($features[FORSCHUNGSQ] or $features[MILITAERQ] or $features[GEBAEUDEQ]) {
	$tpl->assign('ANY_FEATURE', true);
	$qtp = $status[queue_tool_priorities];

	// Anzahl Queue-Tools feststellen wegen Rowspan
	$first = "1. ".get_queue_tool_name($qtp, 0);
	$second = "2. ".get_queue_tool_name($qtp, 1);
	$third = "3. ".get_queue_tool_name($qtp, 2);

	$tpl->assign('PRINT_HILFE', print_hilfe("options_assistent_prioritaet"));
	$tpl->assign('FIRST', $first);
	$tpl->assign('FIRST_NEW_POS', switch_queue_priorities(1,2));
	$tpl->assign('SECOND', $second);
	$tpl->assign('SECOND_NEW_POS', switch_queue_priorities(1,2));
	$tpl->assign('SECOND_NEW_POS2', switch_queue_priorities(2,3));
	$tpl->assign('THIRD', $third);
	$tpl->assign('THIRD_NEW_POS', switch_queue_priorities(2,3));
}
	// FUNKTIONEN ZUM ÄNDERN DER QUEUE-PRIORITÄTEN
	function switch_queue_priorities($a, $b) {
		global $qtp;
		$new = $qtp;
		$a--; $b--;
		$new[$b] = $qtp[$a];
		$new[$a] = $qtp[$b];
		return $new;
	}
	function get_queue_tool_name($qtp, $pos) {
		$tp = $qtp[$pos];
		if ($tp == 1) return "Forschungsassistent";
		elseif ($tp == 2) return "Militärassistent";
		elseif ($tp == 3) return "Gebäudeassistent";
	}



//							Daten schreiben									//

// QUERIES SCHREIBEN
db_write($queries);
db_write($queriesend,1);  #$queriesend sind jene queries die auch nach dem Ende der Runde noch funktionieren.

ob_end_flush();


//							Ausgabe     									//

if ($goon && !$ausgabeblocked)	{
	$tpl->assign('GOON', $goon);
	$tpl->assign('AUSGABEBLOCKED', $ausgabeblocked);
	
	if ($game_syndikat['atwar']) 
	{
		i("Ihr Syndikat befindet sich zur Zeit im Kriegszustand oder der Krieg beginnt innerhalb 
			der nächsten 24h. Wenn Sie den Urlaubsmodus jetzt aktivieren, beträgt die 
			<b>Mindestdauer 5 Tage</b>! Außerdem erhält Ihr Kriegsgegner pauschal 
			<b>24% Ihres Landes</b> als erobert gutgeschrieben. Sie verlieren dieses Land 
			zwar nicht, machen es dadurch aber Ihrem Kriegsgegner leichter, den Krieg zu gewinnen.");
	}

	if (getServertype() == "basic" && $defect_gezielt_erlaubt) {
		$tpl->assign('DEFECT_GEZIELT_SHOW', true);
	}
	if (getServertype() == "basic") {
		$tpl->assign('DEFECT_SHOW', true);
	}
	if ($status[isnoob] and false) {
		$tpl->assign('ANFAENGER_BEREICH_VERLASSEN_SHOW', true);
	} 

	// Fraktionswechsel bestimmen (beim Resetten)
	//$status{race}
	$race_output = array(); $vl = array();
	foreach ($races as $key => $value) {
		$vl['o_race'] = $races{$key}{race};
		$vl['o_tag'] = $races{$key}{tag};
		array_push($race_output, $vl);
		unset($vl);
	}
	$tpl->assign('RACES', $race_output);
		
	$tpl->assign('TIME_BETWEEN_RESET', round($time_between_reset/3600));
	
	$tpl->assign('NOTKSYNDICATES', !isKsyndicates());
	if (!isKsyndicates()) {			
		// Templatewahl
		$templates_output = array();
		foreach ($templates as $temp) {
			if ( $temp[template_id] == $status[template_id]) {
				$temp['o_select'] = "selected";
			}
			array_push($templates_output, $temp);
		}
		$tpl->assign('TEMPLATES', $templates_output);
		// Grafikpakete
		$gpacks_output = array();
		foreach ($gpacks as $temp) {
			if ( $temp[gpack_id] == $status[gpack_id]) {
				$temp['o_select'] = "selected";
			}
			array_push($gpacks_output, $temp);		
		}
		$tpl->assign('GPACKS', $gpacks_output);
	}
	
	// Klassisches Design ansehen - deaktiviert
	// need $showclassic
	
	// Lagerüberweisungen - deaktiviert
	/* status wird eh schon assigned
	$status[moneyget] == 1
	$status[moneygive] == 1
	$status[energyget] == 1
	$status[energygive] == 1
	$status[metalget] == 1
	$status[metalgive] == 1
	$status[sciencepointsget] == 1
	$status[sciencepointsgive] == 1 */
	
	// Mail bei Angriff oder Krieg schicken
	$tpl->assign('SENDMAIL_ON_ATTACK_CHECKED', $sendmail_on_attack_checked);
	$tpl->assign('SENDMAIL_ON_WAR_CHECKED', $sendmail_on_war_checked);
	
	// Emogamesnamen anzeigen - deaktiviert
	// need $status[show_emogames_name]

} # ende $goon
//$praemie = 1000 * (get_day_time($time) - get_day_time($globals[roundstarttime]) + 24 * 3600) / 24 / 3600;
//echo "PRÄMIE:".$praemie." -- ".(get_day_time($time) - get_day_time($globals[roundstarttime]) + 24 * 3600);


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('RIPF', $ripf);
$tpl->assign('STATUS', $status);
$tpl->assign('GLOBALS', $globals);
$tpl->assign('FEATURES_KOMFORTPAKET', $features[KOMFORTPAKET]);
$tpl->assign('HIDETIPPS', $hidetipps);

if($_GET['ajax']){
	print_r($_GET);
	print_r($_POST);
	exit();
}
require_once("../../inc/ingame/header.php");
$tpl->display('options.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function deletetables($id) {
    global $queries;
    // Tables aus denen bei reste, delete usw gelöscht werden soll
    $deletetables = array(aktien,user_id,
                         aktien_privat,user_id,
                         aktien_gebote,user_id,
                         build_buildings,user_id,
                         build_military,user_id,
                         build_sciences,user_id,
                         build_spies,user_id,
                         market, owner_id,
                         market_gebote,user_id,
						 message_values,user_id,
                         military_away,user_id,
                         transfer,user_id,
						 jobs,user_id,
                         usersciences,user_id,
						 kosttools_forschungsq, konzernid,
						 kosttools_militaerq, user_id,
						 kosttools_gebaeudeq, user_id,
						 naps_spieler,user_id,
						 naps_spieler,nappartner,
						 naps_spieler_spezifikation,initiator,
						 naps_spieler_spezifikation,partner,
						 partnerschaften,user_id
						 );
    for ($i=0;$i < count($deletetables); $i+= 2) {
        $action = "delete from ".$deletetables{$i}." where ".$deletetables{$i+1}." = $id";
        array_push($queries,$action);
    }
}

function pwerror($text) {
	global $id;
	list ($emcheck, $uid) = row("select email, emogames_user_id from users where konzernid = $id");
    $fehlermeldung=$text."<br><br>Wenn Sie ihr Syndicates Passwort vergessen haben können sie es sich an ihre angegebene E-Mail-Adresse <a class=\"linkAufsiteBg\" href=\"http://emogames.de/index.php?action=pwforgotten&ia=resend&emcheck=".md5($emcheck)."&uid=$uid\" target=_blank><u>zuschicken</u></a> lassen.";
    f($fehlermeldung);
}

function countToWar(){
	global $status;
	$warArray=array();
	for($c=1;$c<=3;$c++){
		$wars=single("select war_id from wars where first_synd_$c=".$status[rid]." and starttime-24*60*60<=".time()." and endtime=0");
		if($wars) $warArray[$wars] = 'first_'.$c.'_add';
		$wars=single("select war_id from wars where second_synd_$c=".$status[rid]." and starttime-24*60*60<=".time()." and endtime=0");
		if($wars) $warArray[$wars] = 'second_'.$c.'_add';
	}
	if($warArray){
		$landwar=single("select land from status where id=".$status[id])*0.25;
		foreach($warArray as $warItem=>$warAdd){
			select("update wars set ".$warAdd."=".$warAdd."+".$landwar." where war_id=$warItem");
			warCheckAndHandle($warItem);
		}
	}
		
}
?>
