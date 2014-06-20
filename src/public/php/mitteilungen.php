<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

// mid - message-id (ID der Nachricht)
// rec - receiver (Empfänger)

if ($rid): $rid = floor($rid); endif;
if ($action and $action != "psm" and $action != "sm" and $action != "rm" and $action != "del" and $action != "report" and $action != "search"): unset($action); endif;
if ($mid): $mid = floor($mid); endif;
if ($rec and $rec != "syndikat" and $rec != "online" and $rec != "gm"): $rec = floor($rec); endif; // Wenn der Empfänger nicht grad das Syndikat, alle online befindlichen oder ein GM ist, ist es ein spezifischer User.. der wird über seine ID identifiziert und daher das floor()
if ($report != "true") {unset($report);}
if ($tor != "in" && $tor != "out"): $tor = "in"; endif;

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

if (!$rid): $rid = $status[rid]; endif;
$ispresident = $game_syndikat[president_id] == $id ? TRUE : FALSE;

$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//
//// Nachricht verfassen oder senden
//

if ($action == "psm" or $action == "sm")
{
	if ($globals['roundstatus'] == 0) // Abfrage ob Runde noch nicht gestartet
	{
		$rec = floor($rec); // verhindert, dass man Nachrichten an das "Syndikat" schicken kann, siehe nächste Zeile z.B.
			//if (($data[alive] or $rec == "syndikat" or $rec == "online" or $rec == "gm") && $action == "psm")	{

		//Vor Rundenstart sind die User in Gruppen gespeichert (Tabbelle groups) und befinden sich in einem der Spalten: u1, u2, u3, etc.

		foreach (range(1, MAX_USERS_AFTER_ROUNDSTART) as $vl)	// equivalent zu for( $i = 1; $i < MAX_USERS_A_GROUP; $i++)
			{ $users .= "u$vl,";} 						// erstellt einen CSV-String mit allen user-spalten: "u1,u2,u3,u4,"

		$users = chopp($users); // löscht das letzte Zeichen des Strings (das Komma)
		$result = assoc("select group_id,open,nachfolger,password,$users from groups where $id in ($users)"); //liefert ein assoziatives Array mit den Inhalten aller Gruppen zurück wo der user in einem der u-felder steht (sollte nur eine sein)
		$found = 0;
		
		if ($result) //wenn result vorhanden..
		{
			foreach (range(1,MAX_USERS_AFTER_ROUNDSTART) as $vl)  // guckt, im Endeffekt, ob Receiver im gleichen syndikat ist
			{
				if ($result[u.$vl] == $rec) 
					$found = 1;
			}
		}
		//if (!$found)		//wenn nicht, wird der user zum receiver
			//$rec = $id;
	}
	
	
	if ($rec or ($mid && $action == "psm"))	
	{
		$message_or_reply = "Mitteilung";

		if ($mid && $action == "psm")	
		{
			$data = assoc("select betreff, message, gelesen, sender, user_id, syndicate, rid, alive from messages,status where unique_id=$mid and messages.sender=status.id");
			
			if (!$data) 
				$data = assoc("select betreff, message, gelesen, time, unique_id, user_id from messages where unique_id=$mid and sender=0");
				
			if ($data && $data['gelesen'] != 2) 
			{
				if ($data['user_id'] == $id)	
				{
					if( $data['sender'])
					{
						$rec = $data['sender'];
					}
					else
					{
						$rec = "gm";
					}
					
					if ($rec == "gm") 
					{
						preg_match_all("/\(#(\d+)\)/", $data['betreff'], $matches, PREG_SET_ORDER); // speichert alle passenden sachen vom betrett in $matches
						$case_id = $matches[count($matches)-1][1]; //extrahiert die letzte gefundene ID
						if ($case_id) //wenn es eine ID gab...
						{
							$casedata = assoc("select * from admin_case where id = '$case_id'");
							if ($casedata['status'] < 5) 
								$rec = 'gm'; 
							else 
								$rec = "";
						} 
						else 
							$rec = "";
					}
					$message_or_reply = "Antwort";
					
					if( !preg_match("/Re:\{[^}]+}/", $data[betreff]) )
					{
						$betreff = "Re:{".$data[betreff]."}";
					} 
					else
					{ 
						$betreff = $data[betreff];
					}
					
					$data[message] = preg_replace("/(\n|\r\f)/", "", $data[message]);
					$message_data = preg_split("/<br>/", $data[message]);
					$message = ">--Ursprüngliche Mitteilung--\n>Von: ".($data['sender'] == 0 ? "Game-Master":$data[syndicate].($globals['roundstatus'] >= 1 ? " (#".$data[rid].")":""))."\n>\n>\n";
					foreach ($message_data as $vl)	
						{ $message .= ">$vl\n";	}
				}
				else 
				{ 
					$errormsg = "Sie können nur auf eine Mitteilung antworten, die an Sie verschickt wurde!<br>Aktion abgebrochen!";
					$tpl->assign('ERROR', $errormsg);
					unset($action); unset($rec);
				}
			}
			else 
			{ 
				$errormsg = "Ausgewählte Mitteilung  (id: $mid ) existiert nicht !<br>Aktion abgebrochen!";
				$tpl->assign('ERROR', $errormsg);
				unset($action); unset($rec);
			}
		}
		
		if ($rec && $rec != $id)	
		{
			if ((!$mid || $action == "sm") and $rec != "syndikat" and $rec != "online") 
			{
			    if ($rec != 'gm') 
			    {
			    	$data = assoc("select rid, syndicate, alive from status where id=$rec");
			    }
				else if ($rec == "gm") 
				{
					$data = assoc("select betreff, message, gelesen, time, unique_id, user_id from messages where unique_id=$mid and sender=0");
					preg_match_all("/\(#(\d+)\)/", $data['betreff'], $matches, PREG_SET_ORDER);
					$case_id = $matches[count($matches)-1][1];
					$casedata = assoc("select * from admin_case where id = $case_id");
					if ($casedata['status'] < 5) 
					{
						$rec = 'gm'; 
					}
					else 
					{
						$rec = "";
					}
				}
			}
			# Checken ob Betreff und Message jeweils mind. 3 Zeichen lang ist und Message ausgefüllt ist
			if (($data[alive] or $rec == "syndikat" or $rec == "online" or $rec == "gm") && $action == "sm")	{
				$betreff = trim($betreff);
				//$betreff = htmlentities($betreff);
				$betreff = preg_replace("/ {2,}/", " ", $betreff);
				if (strlen($message) < 3)	{ 
					$errormsg = "Die Mitteilung muss mindestens 3 Zeichen lang sein!";
					$tpl->assign('ERROR', $errormsg);
					$action = "psm";}
				if (strlen($betreff) < 3)	{ 
					$errormsg = "Der Betreff muss mindestens 3 Zeichen lang sein!";
					$tpl->assign('ERROR', $errormsg);
					$action = "psm";}
			}
			if (($data[alive] or $rec == "syndikat" or $rec == "online" or $rec == "gm") && $action == "psm")	{
			
				$tpl->assign("SENDAMSG", true);
				$synId = $data[rid];
				$recstring = $data[syndicate];
				
				select("update sessionids_actual set gueltig_bis = ".($time + SESSION_DAUER)." where user_id = $id");
				if ($rec == "syndikat"){
					$recstring = "das gesamte Syndikat";
					$synId = $status[rid];
				}
				if ($rec == "online"){ 
					$recstring = "Spieler, die online sind aus Syndikat"; 
					$synId = $status[rid];
				}
				if ($rec == "gm"){ 
					$recstring = "Game-Master"; 
				}

				$tpl->assign("ripf",$ripf);
				$tpl->assign("REC", $rec);
				$tpl->assign("MSGorREPLY", $message_or_reply);
				$recstring = $recstring.(($synId && $globals['roundstatus'] >= 1) ? " (#".$synId.")":"");
				$tpl->assign("RECSTR", $recstring);
				$tpl->assign("BETREFF", $betreff);
				$tpl->assign("MESSAGE", $message);
				$tpl->assign("MID", $mid);

			}
			elseif (($data[alive] or $rec == "syndikat" or $rec == "online" or $rec == 'gm') && $action == "sm")	{
        				$message = htmlentities($message, ENT_QUOTES);
        				$betreff = str_replace("<i>", "[i]", $betreff);
        				$betreff = str_replace("</i>", "[/i]", $betreff);
        				$betreff = htmlentities($betreff, ENT_QUOTES);
        				$betreff = str_replace("[i]", "<i>", $betreff);
        				$betreff = str_replace("[/i]", "</i>", $betreff);
                        if ($rec == "syndikat") {$betreff.=" <i>(Syndikatsmitteilung)</i>";}
        				if ($rec == "online") {$betreff.=" <i>(mehrere Empfänger)</i>";}
        
        				$message = preg_replace("/\n\r?\f?/", "<br>", $message);
        				
        				// NORMALE MITTEILUNG
        				if ($rec != "syndikat" && $rec != "online" && $rec != "gm"){
        				  
        					$queries[] = "insert into messages (user_id, sender, time, betreff, message) values ('$rec', '$id', '$time', '$betreff', '$message')";
        					$beschr = "Mitteilung erfolgreich verschickt!";
							$tpl->assign("MSG", $beschr);
        					sendPmAsEmail($time,$rec,$status,$betreff,$message);
        				}
        				
        				// SYNDIKATESMITTEILUNG
        				elseif ($rec == "syndikat" && ($ispresident || ismentor($status[id])))	{
        					$syndmemberids = singles("select id from status where rid=".$status[rid]." and id!=$id and alive > 0");
        					if ($syndmemberids) {
        						foreach ($syndmemberids as $vl) { $insertstring .= "('$vl', '$id', '1', '0', '$time', '$betreff', '$message'),";}
        						$insertstring .= "('$id', '$id', '0', '2', '$time', '$betreff', '$message'),";
        						$insertstring = chopp($insertstring);
        						$queries[] = "insert into messages (user_id, sender, deleted_sender, gelesen, time, betreff, message) values $insertstring";
        						$beschr = "Mitteilung erfolgreich an das gesamte Syndikat verschickt";
								$tpl->assign("MSG", $beschr);
        					} else { 
								$errormsg = "Sie sind momentan alleine in Ihrem Syndikat - Keine Mitteilung verschickt!";
								$tpl->assign('ERROR', $errormsg);
							}
        				}
        				
        				// MITTEILUNG AN ALLE; DIE ONLINE SIND
        				elseif ($rec == "online") {
        					//$syndmemberids = singles("select id from status where rid=".$status[rid]." and id!=$id and alive > 0");
        					//$syndmemberids_online = singles("select user_id from sessionids_actual where gueltig_bis > $time and user_id in (".join(",", $syndmemberids).")");
        					$syndmemberids_online = synmemberids_online();
        					if ($syndmemberids_online) {
        						foreach ($syndmemberids_online as $vl) { $insertstring .= "('$vl', '$id', '1', '0', '$time', '$betreff', '$message'),";}
        						$insertstring .= "('$id', '$id', '0', '2', '$time', '$betreff', '$message'),";
        						$insertstring = chopp($insertstring);
        						$queries[] = "insert into messages (user_id, sender, deleted_sender, gelesen, time, betreff, message) values $insertstring";
								$beschr = "Mitteilung erfolgreich an alle Spieler im Syndikat, die gerade online sind verschickt";
								$tpl->assign("MSG", $beschr);
        					} else { 
								$errormsg = "Sie sind momentan als einziger online in Ihrem Syndikat - Keine Mitteilung verschickt!";
								$tpl->assign('ERROR', $errormsg);
							}
        				}
        				
        				// MITTEILUNG AN GM 
        				elseif ($rec == "gm") {
        					$queries[] = "insert into messages (user_id, sender, time, betreff, message) values ('0', '$id', '$time', '$betreff', '$message')";
        					$queries[] = "insert into admin_case_messages (case_id, sender_id, subject, message_text, time, type) values ($case_id, ".single("select id from users where konzernid=$id").", '$betreff', '$message', $time, 3)";
        					$queries[] = "update admin_case set lastchangetime = $time where id = $case_id";
        					$beschr = "Mitteilung erfolgreich verschickt!";
							$tpl->assign("MSG", $beschr);
        				}
			}
			else	{	
				$errormsg = "Ausgewählter Konzern (id: $rec) existiert nicht!<br>Aktion abgebrochen!";
				$tpl->assign('ERROR', $errormsg);
				unset ($action);}
		}
		elseif ($rec == $id)	{ 
			$errormsg = "Sie können sich selbst keine Mitteilung schicken!";
			$tpl->assign('ERROR', $errormsg);
			unset($action);}
		else { 
			$errormsg = "Sie können auf diese Mitteilung nicht antworten.";
			$tpl->assign('ERROR', $errormsg);
			unset($action); }
	}
	else { 
		$errormsg = "Keinen Empfänger angegeben!<br>Aktion abgebrochen!";
		$tpl->assign('ERROR', $errormsg);
		unset($action); }
}


//
// Nachricht wurde gemeldet
//

if ($action === "report" && $mid) {
	
	$tpl->assign('REPORTAMSG', true);
    // Nachschauen ob gemeldete Nachricht überhaupt dem entsprechenden user gehört:
    $check = single("select user_id from messages where unique_id=$mid");
    if ($check == $status{id}) {
        if ($report != "true") {
			$tpl->assign('PREPAREREPORT', true);
            $tpl->assign('MID', $mid);
        }
        elseif ($report === "true") {
			$reported = single("select reported from messages where unique_id=$mid");
			if (!$reported) {
				$tpl->assign('EXECUTEREPORT', true);
				$data = assoc("select betreff, message, gelesen, time, unique_id, sender, user_id, rulername, syndicate, rid, alive from messages,status where unique_id=$mid and messages.sender=status.id");
				$sendervalues = assoc("select * from status where id = ".$data[sender]);
				$senderaccountid = single("select id from users where konzernid = $sendervalues[id]");
				$receivervalues = assoc("select * from status where id = ".$data[user_id]);
				$receiveraccountid = single("select id from users where konzernid = $receivervalues[id]");
				$beschr = "Die Mitteilung wurde erfolgreich gemeldet.";
				$tpl->assign("MSG", $beschr);
				
				$additional_text = mres($additional_text);

				select("update messages set reported=1 where unique_id=$mid");
				$userdata = assocs("select id, konzernid, username from users where konzernid in (".$data['sender'].",".$data['user_id'].")", "konzernid");
				$opening_text = "<table class=ver11s>
					<tr><td><b>Empfänger</b></td><td>".$receivervalues[syndicate]."(#".$receivervalues[rid].") </td></tr>
					<tr><td><b>Absender</b></td><td>".$sendervalues[syndicate]."(#".$sendervalues[rid].") </td></tr>
					<tr><td><b>Betreff</b></td><td>".$data[betreff]."</td></tr>
					<tr><td><b>Inhalt</b></td><td>".$data['message']."</td></tr>
					<tr><td><b>Kommentar</b></td><td>$additional_text</td></tr>
					</table>";
				
				$tpl->assign("ripf",$ripf);
				$tpl->assign('REPORTTXT', $opening_text);  //der part ist muss nicht formatiert werden, hätte es aber nötig, nachträglich ändern!
				
				create_case($userdata[$data['user_id']]['id'], "Mitteilung gemeldet", 0, $userdata[$data['sender']]['id'], "Mitteilung gemeldet", $opening_text);
			}
			else {	
				$errormsg = "Diese Mitteilung wurde bereits gemeldet.";
				$tpl->assign('ERROR', $errormsg);
			}
        }
    }
    else {
		$errormsg = "Diese Mitteilung wurde nicht an Sie versandt";
		$tpl->assign('ERROR', $errormsg);
    }
    $action = "rm";
}


//
//// Nachricht lesen
//

if ($action == "rm")	{
	if ($mid)	{
		$data = array();
		$data = assoc("select betreff, message, gelesen, deleted_sender, time, unique_id, sender, user_id, rulername, syndicate, rid, alive from messages,status where unique_id=$mid and messages.".($tor == "in" ? "sender" : "user_id")."=status.id");
		if (!$data) $data = assoc("select sender, betreff, message, gelesen, time, unique_id, user_id from messages where unique_id=$mid");
		if ($data) {
			if (($tor == "in" && $data[user_id] == $id) or ($tor == "out" && $data[sender] == $id))	{
				if (($tor == "in" && $data[gelesen] != 2) or ($tor == "out" && $data[deleted_sender] != 1))	{
					if ($data[gelesen] == 0 && $tor == "in"): $queries[] = "update messages set gelesen=1 where unique_id=".$data[unique_id]; endif;
					if ($data[rid] == 0): $data[rid] = "-"; endif;
					
					$tpl->assign("ripf",$ripf);
					$tpl->assign('READAMSG', true);
					$tpl->assign("DATUM", date("H:i:s, D, d. M", $data[time]));
					$tpl->assign("TOR", $tor);
					$tpl->assign("MID", $mid);
					$tpl->assign("COND1", ($data[alive] or $data['sender'] == 0) && $tor == "in" && false);
					$tpl->assign("COND2", ($tor == "in" && $data['sender']));
					$tpl->assign("COND3", $status['rid'] == $data['rid']);
					$tpl->assign("COND4", ($data[alive] or $data['sender'] == 0) && $tor == "in");
					$tpl->assign("UNIQUEID", $data[unique_id]);
					$tpl->assign("SENDER", $data['sender']);
					$tpl->assign("SYNDICATE", $data['syndicate']);
					$tpl->assign("BETREFF", $data['betreff']);
					$tpl->assign("MESSAGE", umwandeln_bbcode($data['message']));
					$tpl->assign("REC", ($data['sender'] == 0 or $data['user_id'] == 0) ?
						"<b>Game-Master</b>" : ($data['sender'] == 65000 or $data['user_id'] == 65000) ? "<b>System</b>" : $data[rulername]." von ".$data[syndicate].($globals['roundstatus'] >= 1 ? " (#".$data[rid].")":"")); //Runde58 by dragon, systemnachrichten

				}
				else	{
					$errormsg = "Diese Mitteilung existiert nicht!";
					$tpl->assign('ERROR', $errormsg);
					unset($action); }
			}
			else {
				$errormsg = "Sie können nur Mitteilungen lesen, die an Sie verschickt wurden bzw. die Sie an jemanden verschickt haben!<br>Aktion abgebrochen!";
				$tpl->assign('ERROR', $errormsg);
				unset($action);}
		}
		else {
			$errormsg = "Ausgewählte Mitteilung  (id: $mid ) existiert nicht !<br>Aktion abgebrochen!";
			$tpl->assign('ERROR', $errormsg);
			unset($action);}
	}
	else { 
		$errormsg = "Keine Mitteilung zum Lesen angegeben!<br><br>Aktion abgebrochen!";
		$tpl->assign('ERROR', $errormsg);
		unset($action);}
}

//
//// Mitteilungen löschen
//

if ($action == "del")	{
	foreach ($_POST as $ky => $vl)	{
		if (strpos($ky, "elete") == 1){$deletefrom[] = floor($vl);}
	}
	if ($deletefrom): $deletestring = join(",", $deletefrom); endif;
	
	if (!$deletestring): if ($delete1): $deletestring = $delete1; endif; endif;
	if ($deletestring): $queries[] = "update messages set ".($tor == "in" ? "gelesen=2":"deleted_sender=1")." where ".($tor == "in" ? "user_id":"sender")."='$id' and unique_id in ($deletestring)"; endif;
}

#====================

$globalssave = $globals;
db_write($queries, 1);

#====================

$globals = $globalssave;

//
//// Ausgabe zum Auswählen eines Konzerns an den man eine Mitteilung verschicken möchte erstellen:
//
if ($action != "psm" and $action != "rm" and $goon)	{

	$tpl->assign('LISTMSG', true);
	
	$possible_receivers = assocs("select id, syndicate from status where rid=$rid and alive > 0", "id");
	
	if ($rid == $status[rid]): $receiverauswahl .= "<option value=online>..:::Syndikatsmitgl., die online sind:::.."; endif;
	if (($rid == $status[rid] && $ispresident) || ismentor($status[id])): $receiverauswahl .= "<option value=syndikat>..:::gesamtes Syndikat:::.."; endif;
	
	foreach ($possible_receivers as $ky => $vl)	{
		if ($id != $ky)	{
			$receiverauswahl .= "<option value=$ky>".$vl[syndicate];
		}
	}
	if ($receiverauswahl): $receiverauswahl = "<select name=rec>$receiverauswahl</select>"; endif;
	if (!$receiverauswahl): $receiverauswahl = "Kein Konzern gefunden"; endif;
	
	$tpl->assign('RECEIVER', $receiverauswahl);
	$tpl->assign('COND1', $globals['roundstatus'] >= 1);
	$tpl->assign('PREMIUM', $features[KOMFORTPAKET]);
	$tpl->assign('TOR', $tor);
	$tpl->assign('SEARCHWORD', $searchword);
	$tpl->assign('RID', $rid);

	$statement_ergaenzung = "";
	
	if ($features[KOMFORTPAKET] && $action == "search" && strlen($searchword) > 3) {
		$searchword = mres($searchword);
		$statement_ergaenzung = " and (betreff like '%$searchword%' or message like '%$searchword%') ";
	} elseif ($action == "search" && $features[KOMFORTPAKET]) {
		$infomsg = "<br>Das Suchwort muss mindestens 4 Buchstaben lang sein.<br>";
		$tpl->assign('INFO', $infomsg);
	}

	$message_data = assocs("select gelesen, deleted_sender, time, unique_id, betreff, sender, syndicate, rid, id from messages,status where messages.".($tor == "in" ? "user_id":"sender")."=$id and messages.".($tor == "in" ? "gelesen < 2":"deleted_sender = 0")." and messages.".($tor == "in" ? "sender":"user_id")." = status.id$statement_ergaenzung order by time desc", "unique_id");
	if ($tor == "in") {
		$gm_message_data = assocs("select gelesen, time, unique_id, betreff from messages where user_id=$id and sender=0 and gelesen < 2$statement_ergaenzung order by time desc", "unique_id");
		$system_message_data = assocs("select gelesen, time, unique_id, betreff from messages where user_id=$id and sender=65000 and gelesen < 2$statement_ergaenzung order by time desc", "unique_id"); //Runde 58 by dragon
	} else {
		$gm_message_data = assocs("select gelesen, time, unique_id, betreff from messages where user_id=0 and sender=$id and deleted_sender = 0$statement_ergaenzung order by time desc", "unique_id");
	}
	if ($gm_message_data) $message_data = array_merge($gm_message_data, $message_data);
	if ($system_message_data) $message_data = array_merge($system_message_data, $message_data); //Runde 58 by dragon
	if ($message_data)	{
	
		$tpl->assign('MSGDATA', true);
		
		$count = 0;
		$msgarray = array();
		
		foreach ($message_data as $ky => $vl)	{
			
			$msgdetails = array();
			
			if ($vl[gelesen] == 0 && $tor == "in"): $boldpre = "<b>"; $boldafter = "</b>"; $linkclass = "linkAuftableInner";
			else: $boldpre = ""; $boldafter = ""; $linkclass = "linkAuftableInner"; endif;
			
			array_push($msgdetails ,($vl['sender'] == 0 ? "<b>Game-Master</b>" : $vl['sender'] == 65000 ? "<b>System</b>" : //Runde 58 by dragon: added System message (sender == 65000)
				"<a href=\"syndicate.php?action=details&detailsid=".$vl[id]."&rid=".$vl[rid]
				."\" class=linkAuftableInner>".$vl[syndicate]."</a>".
				($globals['roundstatus'] >= 1 ? " <a href=\"syndicate.php?&rid=".$vl[rid].
				"\" class=linkAuftableInner>(#".$vl[rid].")</a>":"")));
			array_push($msgdetails,$boldpre."<a href=mitteilungen.php?tor=$tor&action=rm&mid=".$vl[unique_id].
				" class=$linkclass>".$vl[betreff]."</a>$boldafter");
			array_push($msgdetails,date("H:i:s, d. M", $vl[time]));
			array_push($msgdetails,"<input type=checkbox name=delete$count value=$vl[unique_id]>");
		
			
			array_push($msgarray,$msgdetails);
		$count++;
		}
		
		$tpl->assign('MSGARRAY', $msgarray);	
		
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

$tpl->display('mitteilungen.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


function sendPmAsEmail($time,$receiver_id,$senderStatus,$betreff,$content) {
  
	$emailOk = single("select pm_als_mail from status where id = $receiver_id");
	  
	// User möchte PNs nicht als email bekommen
	if (!$emailOk) return;
	  
	$rec_userdata = assoc("select * from users where konzernid=$receiver_id");
	if (! is_array($rec_userdata) || count($rec_userdata) <= 0) return; // Keine Nutzerdaten gefunden
	  
	$email = $rec_userdata['email'];
	  
	$to = $rec_userdata['vorname'];
	// Kein Vorname/Nachname gefunden -usernamen nehmen
	if (strlen($to) <= 3) $to = $rec_userdata['username'];
	  
	$subject  = "neue Mitteilung von Spieler ".$senderStatus['syndicate']." (#".$senderStatus['rid'].")";
	  
	$message  = "Hallo $to,\n\n";
	$message  .= "der Spieler ".$senderStatus['syndicate']." (#".$senderStatus['rid'].") hat dir eine Nachricht geschickt:\n\n------------\n";
	$message  .= "$betreff\n\n";
	  
	$message  .= preg_replace("/<br>/","\n",$content);
	  
	$message  .="\n------------\n\nUm auf diese Nachricht zu antworten, logge dich bei Syndicates ein:\nhttp://syndicates-online.de\n\n";
	$message  .="____\n";
	$message  .="\nDu erhältst diese E-Mail, weil du dich bei dem Online-Spiel Syndicates-Online registiert hast. Wenn du Mitteilungen nicht länger als E-Mail bekommen willst, kannst du dieses Feature unter dem Menüpunkt _Optionen_ deaktivieren.";
	  
	$message = html_entity_decode($message);
	  
	sendthemail($subject,$message,$email,$to);
 
}

?>