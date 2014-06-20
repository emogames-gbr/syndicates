<?

//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


// Übergabevariablen für neuen Auftrag einstellen
$arid = (int) $arid; if ($arid < 0) {$arid=1;}
$metal = 0;(int) $metal; $metal < 0 ? $metal=0:1;
$credits = floor($credits); $credits< 0? $credits= 0:1;
$energy = 0;floor($energy); $energy < 0 ? $energy = 0:1;
$sciencepoints = 0;(int) $sciencepoints; $sciencepoints < 0 ? $sciencepoints = 0: 1;
$number = floor($number); $number < 0 ? $number = 0 : 1;
$number = floor($number);

$param = floor($param); $param < 0 ? $param = 0:1;
$job_id = floor($job_id); $job_id < 0 ? $job_id = 0:1;
$target_id = floor($target_id); $target_id < 0 ? $target_id = 0: 1;
if ($submit) {$submit = 1; $ia = "createjob";}
if ($changesyn) {$changesyn=1;$action = "new";}
if ($changetarget) {$changetarget=1;$action ="new";}
if ($anonym != "on") {$anonym = "";}


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//



//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");
require_once(LIB."/js.php");

//header includen
require_once("../../inc/ingame/header.php");

if ($number > 10) {
	$errormsg = "Anzahl Wiederholungen von $number auf 10 reduziert.";
	$tpl->assign('ERROR', $errormsg);
	$tpl->display('fehler.tpl');
	$number = 10;
}
//$status[paid] = 1;

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//
$stdform = "<form style=\"margin:0px\" action=jobs.php method=post>";
$tpl->assign("stdform", $stdform);
$types = gettypes($target_id); // Angriffs und Spionagetypen initialisieren
$types_str=getattacktypesA();
if (!$arid) {$arid=$status[rid];}
$type=typeok($type);
//$onlinetime =$time +  60*(30 + mt_rand(0,30));
$onlinetime = $time; // + WAITTIME + mt_rand(0,WAITRANDOM); Seit Runde 28 sofort verfügabr
$outtime = $time - OUTTIME;
$queries = array();
$backmodifier = 0.80; // Man bekommt nur 85% des ursprünglichen Einstellungsgeldes zurück, wenn man einen Job zurück nimmt
$backstring = "Ihr Auftrag wurde erfolgreich zurückgenommen. Sie haben ".($backmodifier*100)
			."% der bezahlten Prämie zurückerhalten."; // Muss mit backmodifier zusammen angepasst werden
$probeaccountfehler = "Der Auftragsmarkt ist für Probeaccounts gesperrt.";
$alliedsyns = alliedsyns($status[rid]);
$attackts = array(siege,conquer,normal);
if ($param && !in_array($type,$attackts)) {
	$infomsg = "Sie haben ein Mindestergebnis für eine Spionage, Sabotageaktion oder Spionezerstören angegeben. Mindestergebnisse sind nur für Angriffsaufträge gültig!";
	$tpl->assign('INFO', $infomsg);
	$tpl->display('info.tpl');
	$param=0;
}

define (MINSPYWORTH,(10));
define (MINATTACKWORTH,(100));
define (ANNAHMELANDBEDINGUNG,0.2); // Max 50% Abweichung beim Auftraggeberland, damit Auftrag noch angezeigt/angenommen werden kann
define (ANNAHMELANDBEDINGUNG_STRING," 5 mal größer/kleiner ");
define (SHOWALL_DELAY,3); // Nach drei Stunden werden generell alle Aufträge angezeigt
define(LANDKOSTEN_AUFTRAG_PROZENT,100); // Prozentuale Landkosten bei Auftrag
define(LANDKOSTEN_AUFTRAG_PROZENT_MAX,150); // Prozentuale Landkosten bei Auftrag
define(LANDKOSTEN_RAGE_AUFTRAG_PROZENT,85); // Prozentuale Landkosten bei Auftrag nur Belagerung & Spykill
define(LANDKOSTEN_RAGE_AUFTRAG_PROZENT_MAX,250); // Prozentuale Landkosten bei Auftrag Belagerung & Spykill
define(MINMAX_JOB, 15000000);
define (MAXATTACKJOB_TIMELIMITED,2);
define(ATTACKJOBS_TIMELIMIT,10*60*60);
define(ATTACKJOBS_TIMELIMIT_STRING,10);


$maxid = single("select max(id) from status");
$minid = single("select min(id) from status");
$status[show_group] = calc_show_group($maxid,$minid);

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//
// InnerActions abarbeiten
//


// Bezahlt ?

if ($status[paid] != 1) {
	$action ="";
	$inner = "";
	$ia = "";
}

//Info  bzgl ausführbare Aufträge  27.8.2010 by Christian

$attackjobs_done_criticaltime = single("select count(*) from jobs_logs where user_id=$status[id] and (type='conquer' or type='normal' ) and finishtime > $time-".ATTACKJOBS_TIMELIMIT." and success=1");
$attackjobs_current = single("select count(*) from jobs where user_id=$status[id] and (type='conquer' or type='normal' )");
$oldest = assocs("select finishtime from jobs_logs where user_id=$status[id] and (type='conquer' or type='normal' ) and finishtime > $time-".ATTACKJOBS_TIMELIMIT." and success=1 order by finishtime ASC limit 1");
$earliestPos = $oldest[0]['finishtime']+ATTACKJOBS_TIMELIMIT;
/*if ($attackjobs_current+$attackjobs_done_criticaltime >= MAXATTACKJOB_TIMELIMITED) {
	if($oldest[0]['finishtime']==0)
		$infomsg = "Es können zur Zeit keine Angriffsaufträge für Sie ausgeführt werden!";
	else
		$infomsg = "Es können frühstens ".myTime($earliestPos)." Uhr wieder Angriffsaufträge für Sie ausgeführt werden!";
	$tpl->assign('INFO', $infomsg);
	$tpl->display('info.tpl');
}*/

//Ende Info

//////////////////////////
// Neuen Auftrag einstellen
//////////////////////////
if ($ia == "createjob" && $submit) {

	$jobok=1;
	$defstatus = assoc("select * from status where id=$target_id ");
	$targetexists = $defstatus[land];
	if ($types[$type][minprice] && $types[$type][type] == 'ip' && !($types[$type][action_key] == 'conquer') ) {
		$rage=false;
		$minprice = $types[$type][minprice];
		$maxprice = $types[$type][maxprice];
	}
	elseif($types[$type][type] == 'op'){
		if($types[$type][action_key] == 'delayaway'){
			$types[$type][minprice] = sabotageminprice($targetexists, 5);
			$types[$type][maxprice] = sabotagemaxprice($targetexists, 5);
		}
		else{
			$types[$type][minprice] = sabotageminprice($targetexists, 3); //makemenicer Konstanten
			$types[$type][maxprice] = sabotagemaxprice($targetexists, 3);
		}
		$minprice = $types[$type][minprice];
		$maxprice = $types[$type][maxprice];
	}else {
		$rage = 0; //$types[$type][action_key] == 'conquer';
		$types[$type][minprice] = attackminprice($target_id, $targetexists, $types[$type][action_key]);
		$types[$type][maxprice] = attackmaxprice($target_id, $targetexists, $types[$type][action_key]);
		$minprice = $types[$type][minprice];
		$maxprice = $types[$type][maxprice];
		if (!$param) {$param = 1;}
		if ($param > $targetexists*0.2) $param=$targetexists*0.2;
	}
	
	if ($defstatus[alive] == 0 ) {
		$jobok = 0;
		$errormsg = "Sie können nur Aufträge gegen Spieler einstellen, die noch leben!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
 
	$jobnumber = single("select count(*) from jobs where user_id=$status[id]");
	if ($jobnumber >= 3) {$jobok=0; 
		$errormsg = "Sie können maximal drei Aufträge gleichzeitig anbieten.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');}
	if (!$type) {$jobok=0; 
		$errormsg = "Bitte einen gültigen Auftragstyp auswählen";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');}
	if (!$number) {$jobok=0;
		$errormsg = "Ihr Auftrag sollte mindestens 1 mal ausgeführt werden";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');}
	if ($target_id == $status[id]) {
		$errormsg = "Sie können keine Aufträge gegen Ihren eigenen Konzern erstellen.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		$jobok=0;}
	
	//or type='siege' or 'killspies'
	//or type='siege' or 'killspies'
	//$attackjobs_created_criticaltime = single("select count(*) from jobs_logs where user_id=$status[id] and (type='conquer' or type='normal' ) and inserttime > $time-".ATTACKJOBS_TIMELIMIT." and success=1");
	$attackjobs_done_criticaltime = single("select count(*) from jobs_logs where user_id=$status[id] and (type='conquer' or type='normal' ) and finishtime > $time-".ATTACKJOBS_TIMELIMIT." and success=1");
	$attackjobs_current = single("select count(*) from jobs where user_id=$status[id] and (type='conquer' or type='normal' )");
	
	
	
	
	//$attacks = array('killspies','normal','siege','conquer');
	$attacks = array('normal','conquer'); // 13.04.07 - killspies und siege sind nicht betroffen
	
	//Ab R46 können nur 2 Aufträge innerhalb von 20h für einen Spieler ausgeführt werden. Vorher konnten nur 2 Aufträge pro 20h eingestellt werden.
	/*if (in_array($type,$attacks) && $attackjobs_current+$attackjobs_done_criticaltime >= MAXATTACKJOB_TIMELIMITED) {
		$errormsg = "Es können nur ".MAXATTACKJOB_TIMELIMITED." Angriffsaufträge innerhalb von ".ATTACKJOBS_TIMELIMIT_STRING." Ticks für sie ausgeführt werden!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		$jobok = 0;
	}*/
	
	$arid = single("select rid from status where id = ".floor($target_id));
	
	if ($arid == $status[rid] && $type != "scienceintel" && $type != "buildintel" && $type != "unitintel1" && $type != "unitintel2" && $type != "newsintel") {
		$errormsg = "Sie können nur Aufträge vom Typ Spionageaktion gegen Syndikatsmitglieder erstellen. Angriffe oder Sabotageaktionen sind nicht erlaubt!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		$jobok = 0;
	}
	
	$synd_type = single("select synd_type from syndikate where synd_id = ".floor($arid));
	if ($synd_type != "normal" and $game_syndikat[synd_type] == "normal") {
		$errormsg = "Sie können nur Aufträge gegen Spieler einstellen, die sich im gleichen \"Bereich\" befinden wie Sie (Unterscheidung zwischen normalen Syndikaten und Anfängersyndikaten!)";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		$jobok = 0;
	}
	//if (!$metal && !$sciencepoints && !$credits && !$energy) {$jobok=0;f("Sie sollten eine angemessene Bezahlung für ihren Auftrag wählen.");}
	if ($credits < $minprice) {
		$jobok = 0; 
		$errormsg = "Die Entlohnung für den gewählten Auftrag muss mindestens ".pointit($minprice)." Credits betragen";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	if ($credits > $maxprice) {
		$jobok = 0; 
		$errormsg = "Die Entlohnung für den gewählten Auftrag darf höchstens ".pointit($maxprice)." Credits betragen";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	if (!$targetexists) {$jobok=0; 
		$errormsg = "Bitte wählen sie ein gültiges Ziel für ihren Auftrag (möglicherweise versuchen sie, einen Auftrag gegen einen Spieler einzustellen, der sich im Urlaubsmodus befindet).";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');}
	
	// Bei Racherecht dürfen generell Aufträge gegen einen Spieler eingesetellt werden
	//echo"debug rid ".$status[rid]." targ:".$targetID;
	//TODO
	if (!racherecht($target_id) && !inwar($status[rid],$target_id) ) {
		if ($targetexists * (1/ANNAHMELANDBEDINGUNG) < $status[land] || $status[land]* (1/ANNAHMELANDBEDINGUNG) < $targetexists) {
			$jobok = 0;
			$errormsg = "Sie können keine Aufträge gegen Spieler einstellen, die mehr als ".ANNAHMELANDBEDINGUNG_STRING."als ihr eigener Konzern sind.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
		if($type=="siege" or $type=="killspies" or $type=="delayaway" or $type=="killbuildings" or $type=="killunits" or $type=="normal"){
			$jobok = 0;
			$errormsg = "Diesen Auftragstyp können Sie nur bei Racherecht einstellen.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
		if ($param > $targetexists * 0.2) {
			$jobok = 0;
			$errormsg = "Es können nicht mehr als 20% des Landes ihres Ziels als Mindestergebnis angegeben werden, da ein solcher Auftrag nicht mehr erfüllt werden könnte!";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
	}
	
	/*
	$resstats=getresstats();
	$worth = $credits + $energy*$resstats[energy][value] + $metal * $resstats[metal][value] + $sciencepoints* $resstats[sciencepoints][value];
	$attacktypes = getattacktypes();
	$minmod = MINSPYWORTH;
	
	foreach ($attacktypes as $value) {
		if ($value[action_key] == $type) {
			$minmod = MINATTACKWORTH;break;
		}
	}
	if ($worth < $status[land]*$minmod) {
		pvar($worth);
		pvar($status[land]*$minmod);
		f("Die Entlohnung für diesen Auftrag ist zu niedrig.<br>Spionageaufträge müssen mindestens mit Ressourcen im Wert von ".pointit($status[land]*MINSPYWORTH)." Credits vergütet werden. <!--, Angriffsaufträge mit Ressourcen im Wert von ".pointit($status[land]*MINATTACKWORTH)." Credits.--><br>(Aktueller Auftragswert: ".pointit((int)$worth).")");$jobok=0;
	}
	*/
	// Alle eingaben ok
	if ($anonym) {
		$modifier = 1.25; // Ausgaben * 1,5 bei anonymem auftrag
	}
	else {
		$modifier = 1;
	}
	$modifier *= $number;
	$paycredits = $credits*$modifier;
	$paycredits = ceil($paycredits);
	//$paycredits = (int) $paycredits; // NICHT AUF INTEGER CASTEN - ÜBERLÄUFE MÖGLICH
	if ($status[money] < $paycredits) {$jobok=0;
		$errormsg = "Sie haben nicht genügend Credits um die Prämie von ".pointit($paycredits)." Credits für diesen Auftrag zu bezahlen.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');}
	if (!$jobok) {
		$action = ""; $ia = "";
	}
	else {
		// Ressourcen abziehen
		$status[money] -= $paycredits;
		$status[nw] = nw($status[id]);
		$queries[] = "update status set money=money-$paycredits,nw=$status[nw] where id=$status[id]";
		if ($anonym) {$anins = 1;} else {$anins=0;}
		if ($types[$type][difficulty] != "attack") {
			$onlinetime=$time;
		}
		$showgroup_temp = mt_rand(0,(SHOWGROUPS_COUNT-1));
		$normgain = landgain($status[land], $status[rid], $target_id);
		$queries[] = "insert into jobs
						(user_id,target_id,type,number,money,energy,metal,sciencepoints,inserttime,onlinetime,param,anonym,show_group, normgain)
						values
						($status[id],$target_id,'$type',$number,$credits,0,0,0,$time,$onlinetime,$param,$anins,$showgroup_temp, $normgain)";

		// Genug Ressourcen vorhanden ?
		// Auftrag erfolgreich eingestellt!
		$succstring = "
			Ihr Auftrag wurde erfolgreich eingestellt, er wird in wenigen Sekunden verfügbar sein. <br>
			Auftragstyp: ";
			$notfound=true;
				foreach ($types as $taga=>$vl) {
					if ($vl[action_key] == $type) {
						$succstring.="<b>$vl[name]</b>";
						$notfound=false;
						break;
					}
				}
				if($notfounded){
					foreach ($types_str as $taga=>$vl) {
					if ($vl[action_key] == $type) {
						$succstring.="<b>$vl[name]</b>";
						break;
					}
				}
				}
				$notfound=true;
			$succstring.="
			<br>
			Ziel: <b>$defstatus[syndicate] (#$arid)</b><br>
			Ihnen wurden <b>".pointit($paycredits)." Credits</b> zur Bezahlung des Auftrages von ihrem Konto abgebucht.
		";
		$beschr = "
			$succstring
		";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
}
//////////////////////////
// Auftrag zurücknehmen
//////////////////////////
elseif($ia == "back" && $job_id) {
	$job = assoc("select * from jobs where id = $job_id and user_id=$status[id]");
	if (!$job[user_id]) {
		$errormsg = "Sie können nur Aufträge zurücknehmen, die sie selbst vergeben haben.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	else if($job[acceptor_id] !=0) {
		$errormsg = "Sie können keine Aufträge zurücknehmen, die bereits angenommen wurden!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	else {
		$backmodifier *= $job[number];
		$addmoney = $job[money] * $backmodifier;
		$status[money] +=ceil($addmoney);
		$status[nw] = nw($status[id]);
		$queries[] = "update status set money=money+$addmoney,nw=$status[nw] where id=$status[id]";
		$queries[] = "insert into jobs_logs
						(id,user_id,target_id,type,param,money,energy,metal,sciencepoints,inserttime,onlinetime,anonym,finishtime,success)
						values
						($job[id],$job[user_id],$job[target_id],'$job[type]',$job[param],$job[money],0,0,0,$job[inserttime],$job[onlinetime],$job[anonym],$time,-1)";
		$queries[] = "delete from jobs where id=$job[id]";
		$notid = " and jobs.id != $job[id]";
		$beschr = "$backstring";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
}
//////////////////////////
// Auftrag annehmen
//////////////////////////
elseif ($ia == "take" && $job_id > 0) {
	
	$takenbefore = single("select  acceptor_id from jobs_logs where  acceptor_id = $status[id] and id = $job_id");
	$targetid2 = single("select target_id from jobs where id = $job_id");
	$selfid = single("select user_id from jobs where id = $job_id");
	$maxdebt = single("select maxschulden from syndikate where synd_id = $status[rid]");
	
	if ($time > $globals[roundendtime]) {
		// keine Auftraege mehr nach Rundenende
		$errormsg = "Runde zu Ende! Sie können keine Aufträge mehr annehmen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	elseif ($status[podpoints]+$status[land] * $maxdebt < 0) {
		$errormsg = "Sie sind zu hoch verschuldet und können momentan keine weiteren Aufträge annehmen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	elseif ($takenbefore) {
		// Checken ob user den Auftrag schonmal angenommen hat
		$errormsg = "Sie haben bereits einmal versucht, diesen Auftrag auzuführen. Sie können jeden Auftrag nur einmal annehmen! Weitere Informationen finden Sie in der Anleitung.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	elseif ($targetid2==$status[id]) {
		$errormsg = "Sie können keine Aufträge gegen sich selber annehmen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
	elseif ($selfid==$status[id]) { 
		$errormsg = "Sie können keine eigenen Aufträge annehmen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
        }
	else {
		$alreadytaken = single("select user_id from jobs where acceptor_id = $status[id] || (acceptor_id != 0 and id = $job_id)");
		if (!$alreadytaken) {
			$job = assoc("select jobs.*,status.syndicate as syndicate,status.rid as rid from jobs,status where jobs.id=$job_id and jobs.target_id=status.id");
			if (  ( $job[show_group] == $status[show_group] || ($job[onlinetime] + 60*60*SHOWALL_DELAY < $time) ) && $job[user_id]) {
				$auftraggeber = assoc("select * from status where id = $job[user_id]");
				$auftraggeberrid = $auftraggeber[rid];
				$targetrid = single("select rid from status where id =$job[target_id]");
				$allyRid = assoc("SELECT first, second FROM allianzen WHERE second = '".$status['rid']."' OR first = '".$status['rid']."'");
				$rids = ($allyRid ? $allyRid : array($status['rid']));
				if ($auftraggeber[land] * ANNAHMELANDBEDINGUNG > $status[land] || $status[land] * ANNAHMELANDBEDINGUNG > $auftraggeber[land]) {
					$errormsg = "Sie können nur Aufträge von Spielern annehmen die höchstens ".ANNAHMELANDBEDINGUNG_STRING." als Ihr Konzern sind.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
				elseif($auftraggeber[isnoob] != $status[isnoob]) {
					$errormsg = "Spieler aus Anfängersyndikaten können keine Aufträge von normalen Spielern annehmen und umgekehrt.";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
				else {
					if (!in_array($auftraggeberrid, $rids) || $job[type] == 'conquer') { //,$alliedsyns)) {
						//pvar($job);
						
						// Prüfen ob im Krieg und ob aufAtrag gegen gegnersyn angenommen werden soll
						$friends = array($status['rid']);
						if ($game_syndikat['allianz_id']) {
							if ($game_syndikat['ally1']) $friends[] = $game_syndikat['ally1'];
							if ($game_syndikat['ally2']) $friends[] = $game_syndikat['ally2'];
						}
						if (in_array($auftraggeberrid, $friends) || !isatwar($status[rid],$targetrid)) {
							
							if (is_array($job)) {
								$message = "Einer ihrer Aufträge wurde angenommen:<br><br> Ziel: $job[syndicate] (#$job[rid])<br>Auftragstyp: ";				$notfounded=true;
								foreach($types as $tvalue) {
									if($tvalue[action_key] == $job[type]) {
										$message.="$tvalue[name]";
										$notfounded=false;
										break;
									}
								}
								if($notfounded){
								foreach($types_str as $tvalue) {
									if($tvalue[action_key] == $job[type]) {
										$message.="$tvalue[name]";break;
									}
								}
								}
								
								$message.="<br><br> Entlohnung:<br>";
								if ($job[money]) {
									$add.=pointit($job[money])." Credits,<br>";
								}
								$add = chopp(chopp(chopp(chopp(chopp($add)))));
								$message.=$add;
	
								$dstring ="update jobs set accepttime=$time,acceptor_id=$status[id] where id=$job_id";
								select($dstring);
								$queries[$dstring];
								$queries[] = "insert into message_values
												(id,user_id,time,werte)
												values
												(44,$job[user_id],$time,'$message')";
								$beschr = "Auftrag erfolgreich angenommen";
								$tpl->assign("MSG", $beschr);
								$tpl->display("sys_msg.tpl");
							}
							else {
								$errormsg = "Auftrag fehlerhaft.";
								$tpl->assign('ERROR', $errormsg);
								$tpl->display('fehler.tpl');
							}
						}
						else {
							$errormsg = "Während eines Krieges können Aufträge gegen Kriegsgegner nur ausgeführt werden, wenn sie von Syndikatsmitgliedern erstellt wurden.";
							$tpl->assign('ERROR', $errormsg);
							$tpl->display('fehler.tpl');
						}
					} else {
						$errormsg = "Sie können keine Aufträge von Syndikats und Allianzmitgliedern annehmen.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
					}
				}
			
			} 
			elseif($job[show_group] && !$job[user_id]) {
				$errormsg = "Es ist ein unerwarteter Fehler aufgetreten. Job-Id: $job_id, Zeit: ".$time." User_id: ".$status[id].". Bitte benachrichtige einen Admin und gib die Daten aus dieser Fehlermeldung an.";
				$tpl->assign('ERROR', $errormsg);
				$tpl->display('fehler.tpl');
			}
			elseif ($job) { 
				$errormsg = "Diesen Auftrag können Sie momentan nicht annehmen.";
				$tpl->assign('ERROR', $errormsg);
				$tpl->display('fehler.tpl');};
		}
		else {
			$errormsg = "Entweder Sie haben bereits einen Auftrag angenommen oder dieser Auftrag wird gerade von einem anderen Spieler ausgeführt! Sie können nur einen Auftrag gleichzeitig ausführen.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
	} // Zu hoch verschuldet
}
//////////////////////////
// Auftrag abbrechen
//////////////////////////
elseif ($ia == "break" && $job_id > 0) {
	end_job($job_id,0);
}
//////////////////////////
// Alljobs
//////////////////////////
elseif($ia =="alljobs") {
	if ($alljobs == "on") {
		$status[alljobs] = 1;
		select("update status set alljobs=1 where id=$status[id]");
	}
	else {
		$status[alljobs] = 0;
		select("update status set alljobs=0 where id=$status[id]");
	}
}


//
// Normale Actions abarbeiten
//
$tpl->assign("action", $action);

if($action == "new") {
	js::loadOver();
	$jobnumber = single("select count(*) from jobs where user_id=$status[id]");
	if ($jobnumber >= 3) {
		$errormsg = "Sie können maximal drei Aufträge gleichzeitig anbieten.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		$action = "";
	}
	$nextrid = get_next_rid($arid);
	$lastrid = get_last_rid($arid);
	$tpl->assign("nextrid", $nextrid);
	$tpl->assign("lastrid", $lastrid);
	$tpl->assign("arid", $arid);
	$actionlink = "<a class=\"linkaufTableInner\" href=\"jobs.php?action=new&credits=$credits&type=$type&number=$number&anonym=$anonym";
	$tpl->assign("actionlink", $actionlink);
	$synd_type = single("select synd_type from syndikate where synd_id = ".floor($arid));
	if ($synd_type != "normal" and $game_syndikat[synd_type] == "normal") {
		$players = array();
		$errormsg = "Sie können nur Aufträge gegen Spieler einstellen, die sich im gleichen \"Bereich\" befinden wie Sie (Unterscheidung zwischen normalen Syndikaten und Anfängersyndikaten!)";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	} else { $players = assocs("select id,syndicate,land from status where rid=$arid"); }
}
if (!$action) {
	$myjobids = singles("select id from jobs where user_id = $status[id]");
	$myjobs = assocs("select jobs.*,jobs.id as jobid,status.syndicate as syndicate,status.rid as rid from jobs,status where user_id = $status[id] and status.id=jobs.target_id$notid","jobid");
	$jobtaken = assoc("select jobs.*,status.syndicate as syndicate,status.rid as rid from jobs,status where acceptor_id = $status[id] and status.id=jobs.target_id");
	if (!$jobtaken[user_id]) {
		$resstats=getresstats();
		if ($sortby == "type") {
			$orderby = "jobs.type $dir,nw asc";
		}
		elseif ($sortby == "worth") {
			$orderby = "worth $wdir";
		}
		elseif ($sortby == "rid") {
			$orderby = "rid $rdir,status.syndicate asc";
		}
		elseif ($sortby == "nw") {
			$orderby = "nw $ndir";
		}
		else {
			$orderby = "worth";
			$wdir = "asc";
		}


		//status.rid != $status[rid] and //target_id <> ".$status[id]." and
		
		
		// aus eigenem Syn und Ally darf der gestellte Auftrag nicht stammen
		// KOmmentar test sdaf
		$allyRid = assoc("SELECT first, second FROM allianzen WHERE second = '".$status['rid']."' OR first = '".$status['rid']."'");
		$rids = ($allyRid ? "'".$allyRid[0]."','".$allyRid[1]."'" : $status['rid']);

		$available = assocs("select (jobs.money) as worth,jobs.*,status.syndicate as syndicate,status.rid as rid,status.nw_last_hour as nw,status.land as land
								from
									jobs,status
								where
									onlinetime <= $time and
									onlinetime >= $outtime and
									status.id=jobs.user_id and
									status.alive=1 and
									jobs.acceptor_id=0 and
									status.land >= ".($status[land]*ANNAHMELANDBEDINGUNG)." and
									status.land <= ".($status[land]*(1/ANNAHMELANDBEDINGUNG))." and
									user_id <> ".$status[id]." and
									(jobs.type = 'conquer' or user_id NOT IN (SELECT id FROM status WHERE rid IN (".$rids."))) and
									(jobs.show_group=$status[show_group] or (onlinetime + 60*60*".SHOWALL_DELAY.") < $time)
									$notid
								order by $orderby"
		);
		$searchtime = $time - 24 * 60 * 60;
		$wherestring2 = "where time > $searchtime and winner='a' and did in (";
		if ($available) {
			foreach ($available as $temp) {
				$availids.="$temp[user_id],";
				$targetids.="$temp[target_id],";
				$wherestring2.="$temp[target_id],";
			}
		} else {
				$availids.="0,";
				$targetids.="0,";
				$wherestring2.="0,";
		}
		$wherestring2 = chopp($wherestring2);
		$wherestring2 .=")";
		$availids = chopp($availids);
		$targetids = chopp($targetids);
		if (count($available) > 0) {
			$auftraggeber = assocs("select distinct syndicate,rid,id,isnoob from status where id in ($availids)","id");
			$targets = assocs("select distinct syndicate,rid,id,alive,land,nw_last_hour from status where id in ($targetids)","id");
		}
	}

	/* Neue FKT seit r 42
	if (count($available) > 0) {
		$attackssuffered = assocs("select did, count(*) as n from attacklogs $wherestring2 and gbprot=1 group by did", "did");
	}
	*/
}



//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

//******************
//	Standardausgabe
//******************

if (!$action) {
	if(!$jobtaken[user_id]) {
	
		if ($sortby== "type" && $dir == "desc") {$dir = "asc";} else {$dir="desc";}
		if ($sortby== "worth" && $wdir=="desc") {$wdir = "asc";} else {$wdir = "desc";}
		if ($sortby== "rid" && $rdir=="desc") {$rdir = "asc";} else {$rdir = "desc";}
		if ($sortby== "nw" && $ndir=="desc") {$ndir = "asc";} else {$ndir = "desc";}
		
		// INPLACE SORTING
		function ridsort($a,$b) {
			global $targets;
			 if ($targets[$a["target_id"]]["rid"] == $targets[$b["target_id"]]["rid"]) {
				return 0;
			}
			return ($targets[$a["target_id"]]["rid"] < $targets[$b["target_id"]]["rid"]) ? -1 : 1;
		}
		function nwsort($a,$b) {
			global $targets;
			 if ($targets[$a["target_id"]]["nw_last_hour"] == $targets[$b["target_id"]]["nw_last_hour"]) {
				return 0;
			}
			return ($targets[$a["target_id"]]["nw_last_hour"] > $targets[$b["target_id"]]["nw_last_hour"]) ? -1 : 1;
		}
		
		
		if ($sortby == "rid") {
			usort ( $available, "ridsort");
		}
		else if ($sortby == "nw") {
			usort ( $available, "nwsort");
		}
		$tpl->assign("dir", $dir);
		$tpl->assign("rdir", $rdir);
		$tpl->assign("wdir", $wdir);
		
				$currentkat="";	//aktuele Kategorie (att,op,ip)
				$ausgabeATT=array();//ausgabeteil att
				$ausgabeSABB=array(); //ausgabeteil op
				$ausgabeSPY=array(); //ausgabeteil ip
					foreach ($available as $value) {
						$protclass = "linkAuftableInner";
						list($dummay,$aSuffered) = get_bash_protection($value[target_id]);
						if ($aSuffered == 1) {
							$protclass = "konzernAttacked";
						}
						elseif ($aSuffered > 1) {
							$protclass = "konzernHeavyAttacked";
						}
						if ($targets[$value[target_id]][alive] == 1) { //(!in_array($auftraggeber[$value[user_id]][rid],$alliedsyns) ||$value[user_id] == $status[id]) &&
							if ($auftraggeber[$value[user_id]][isnoob] == $status[isnoob]) { // Nur wenn Auftraggeber auch noob bzw kein noob ist
								
								$unkategorized = array();
								$unkategorized[0] = "";
								$notfound=true;
								foreach ($types as $tvalue) {
									if ($tvalue[action_key] == $value[type]) {
										$unkategorized[0].="$tvalue[name]";
										$currentkat = $tvalue[type];
										$notfound=false;
										break;
									}
								}
								if($notfound){
									
									foreach ($types_str as $tvalue) {
									if ($tvalue[action_key] == $value[type]) {
										$unkategorized[0].="$tvalue[name]";
										$currentkat = $tvalue[type];
										$notfound=false;
										break;
									}
								}
								}
								//bei sabbs den sabbschutz mit anzeigen, dragon12 27.3.12 (R61)
								$spyprotcat = '';
								if($currentkat=="op") {
									$prottime = $time - (60*60*24);
									$opactions = "'killunits','killbuildings'";
									$opsdone = (KILLBUILDINGSACTIONS+KILLUNITSACTIONS)/2*single('select count(*) from spylogs where did='.$value[target_id].' and action in ('.$opactions.') and time >= '.$prottime.' and success=1');
									$opactions = "'delayaway'";
									$opsdone += DELAYAWAYACTIONS*single('select count(*) from spylogs where did='.$value[target_id].' and action = '.$opactions.' and time >= '.$prottime.' and success=1');
									$opactions = "'killsciences'";
									$opsdone += KILLSCIENCESACTIONS*single('select count(*) from spylogs where did='.$value[target_id].' and action = '.$opactions.' and time >= '.$prottime.' and success=1');
									$opsdone = $opsdone - SPYPROTACTIONS;
									if($opsdone > 15){ //mehr als 5 (10) geb/millisabbs
										$spyprotcat = '<span class="gruenAuftableInner">&nbsp;[leichter Sabbschutz]</span>';
									}
									if($opsdone > 30){ //mehr als 10 (15) geb/millisabbs oder 2(3) fossabbs oder 6(9) rückkehrverzögern
										$needspan = ($protclass != "konzernAttacked");
										$spyprotcat = ($needspan?'<span class="highlightAuftableInner">':'').'&nbsp;[mittlerer Sabbschutz]'.($needspan?'</span>':'');
									}
									if($opsdone > 45){ //mehr als 15 (20)
										$needspan = ($protclass != "konzernHeavyAttacked");
										$spyprotcat = ($needspan?'<span class="achtungAuftableInner">':'').'&nbsp;[starker Sabbschutz]'.($needspan?'</span>':'');
									}
									if($opsdone > 60){ //mehr als 20 (25)
										$needspan = ($protclass != "konzernHeavyAttacked");
										$spyprotcat = ($needspan?'<span class="achtungAuftableInner">':'').'&nbsp;[EXTREMER Sabbschutz]'.($needspan?'</span>':'');
									}
								}
								
								
								if ($value[param] > 0) {
									$unkategorized[0].="<br>Mindestergebnis: ".pointit($value[param])."<br>";
								}
								if ($value[number] > 1) {
									$unkategorized[0].="<br>(Verbleibende Aufträge: ".pointit($value[number]).")";
								}
								$unkategorized[1] = "<a class=$protclass href=\"syndicate.php?rid=".$targets[$value[target_id]][rid]."\">".$targets[$value[target_id]][syndicate]." (#".$targets[$value[target_id]][rid].")".$spyprotcat."<br>
									".pointit($targets[$value[target_id]][nw_last_hour])." Nw,".pointit($targets[$value[target_id]][land])." Land</a>";
								if ($value[money]) {
									$add.=pointit($value[money])." Credits,<br>";
								}
								$add = chopp(chopp(chopp(chopp(chopp($add)))));
								//$add.="<br><b>Wert:".pointit(floor($value[worth]))."</b>";
								$unkategorized[2]=$add;
								$add = "";
								if (!$value[anonym]) {
									$unkategorized[3] = $auftraggeber[$value[user_id]][syndicate]." (#".$auftraggeber[$value[user_id]][rid].")";
								}
								else {
									$unkategorized[3] = "anonym";
								}
								if($status[id] != $value[target_id] && $status[rid] != $targets[$value['target_id']]['rid']){ // R4bbiT 02.01.11
								$unkategorized[4]="<a class=linkaufTableInner href=\"jobs.php?".($status[alljobs] ? "ia" : "action")
										."=take&job_id=$value[id]\">annehmen</a>";
								}
								//kategorisieren
								if($currentkat=="att")
									array_push($ausgabeATT, $unkategorized);
								if($currentkat=="op")
									array_push($ausgabeSABB, $unkategorized);
								if($currentkat=="ip")
									array_push($ausgabeSPY, $unkategorized);
	
							} // Noobstatus gleich
						}
					}
				$tpl->assign("ausgabeATT", $ausgabeATT);
				$tpl->assign("ausgabeSABB", $ausgabeSABB);
				$tpl->assign("ausgabeSPY", $ausgabeSPY);
	}
	elseif ($jobtaken[user_id]) 
	{		
		$job = &$jobtaken;
		$protclass = "linkAuftableInner";
		list($dummay,$aSuffered) = get_bash_protection($job[target_id]);
		if ($aSuffered == 1) {
			$protclass = "konzernAttacked";
		}
		elseif ($aSuffered > 1) {
			$protclass = "konzernHeavyAttacked";
		}
		
		$temp = array();
						
		$resttime = AUFTRAGTIME - ($time-$job[accepttime]);
		$minutes = floor($resttime / 60);
		$resttime %=60;
		$seconds = $resttime;
		$temp[0] = "${minutes}m, ${seconds}s";
		if( $job['type'] == "normal" || $job['type'] == "siege" || $job['type'] == "killspies" )
		{
			$temp[1] = "<a class=$protclass href=\"angriff.php?rid=".$job[rid]."&target=".$job[target_id]."&attacktype=".$job[type]."\">".$job[syndicate]."</a>";
		}
		else
		{
			$temp[1] = "<a class=$protclass href=\"spies.php?inneraction=prepare&rid=".$job[rid]."&target=".$job[target_id]."\">".$job[syndicate]."</a>";
		}
		$temp[1] .= "(<a class=$protclass href=\"syndicate.php?rid=".$job[rid]."\">#".$job[rid]."</a>)";
		//$job[syndicate] (#$job[rid]);
			$notfound=true;					
		foreach ($types as $tvalue) {
			if ($tvalue[action_key] == $job[type]) {
				$temp[2].="$tvalue[name]";
				$notfound=false;	
			}
		}
		if($notfound){
		foreach ($types_str as $tvalue) {
			if ($tvalue[action_key] == $job[type]) {
				$temp[2].="$tvalue[name]";
			}
		}
		
		}
		if ($job[money]) {
			$temp[3]=pointit($job[money])." Credits,<br>";
		}
		//$ausgabe = chopp(chopp(chopp(chopp(chopp($ausgabe)))));		
		if ($job[anonym]) {
			$temp[4]="Auftrag wird anonym ausgeführt.";
		}
		if ($job[param] > 0) {
			$temp[4].="Mindestergebnis: ".pointit($job[param]).".";
		}
		$temp[5]=$job[id];
		
		$tpl->assign("jobtaken", $temp);
	}
	if (count($myjobids) > 0) 
	{
		$tpl->assign("JOBS", printmyjobs());
	}
	if($status[alljobs]) {$checked ="checked=on";}
	$tpl->assign("checked", $checked);
}

//******************
//	Neuen Auftrag einstellen
//******************

elseif($action == "new") {
	if (!$number) {$number = 1;}
	$tpl->assign("number", $number);
	$defland = single("select land from status where id=$target_id");
	$tpl->assign("credits", $credits);
	$tpl->assign("type", $type);
	
	//for js
	$tpl->assign("JS_target_id", $target_id);
	
	$JS_actioncosts = array();

	foreach ($types as $tvalue) {
	
		unset($tempAction);
	
		$tempAction[0] = $tvalue[action_key];

		if($tvalue[type] == 'ip'){
			$tempAction[1] = $tvalue[minprice];
			$tempAction[2] = ceil(($tvalue[minprice]+$tvalue[maxprice])/2);
			$tempAction[3] = $tvalue[maxprice];
			$tempAction[4] = (($tvalue[minprice]-$tvalue[maxprice])/-10);
		}
		elseif($tvalue[type] == 'op' && $tvalue[action_key] == 'delayaway'){
			$tempAction[1] = sabotageminprice($defland,5);
			$tempAction[2] = ceil((sabotageminprice($defland,5)+sabotagemaxprice($defland,5))/2);
			$tempAction[3] = sabotagemaxprice($defland,5);		
			$tempAction[4] = ((sabotageminprice($defland,5)-sabotagemaxprice($defland,5))/-10);									
		}
		elseif($tvalue[type] == 'op' && $tvalue[action_key] != 'delayaway') {
			$tempAction[1] = sabotageminprice($defland,3); 
			$tempAction[2] = ceil((sabotageminprice($defland,3)+sabotagemaxprice($defland,3))/2);
			$tempAction[3] = sabotagemaxprice($defland,3);
			$tempAction[4] = ((sabotageminprice($defland,3)-sabotagemaxprice($defland,3))/-10);
		}
		else {
			$rage = 0;
			$tempAction[1] = attackminprice($target_id, $defland, $tvalue[action_key]);
			$tempAction[2] = ceil((attackminprice($target_id, $defland, $tvalue[action_key])+attackmaxprice($target_id, $defland, $tvalue[action_key]))/2);
			$tempAction[3] = attackmaxprice($target_id, $defland, $tvalue[action_key]);
			$tempAction[4] = ((attackminprice($target_id, $defland, $tvalue[action_key])-attackmaxprice($target_id, $defland, $tvalue[action_key]))/-10);
		}
		
		if($tvalue[type] == 'ip'){
			$tempAction[5] = 0;
		}
		elseif($tvalue[type] == 'op') {
			$tempAction[5] = 0;
		}
		elseif($tvalue[type] == 'killspies') {
			$tempAction[5] = 0;
		}
		else {
			$tempAction[5] = ceil(landgain($status[land], $status[rid], $target_id)*LANDKOSTEN_AUFTRAG_PROZENT/100);
			if($rage)
				$tempAction[5] = ceil(landgain($status[land], $status[rid], $target_id)*LANDKOSTEN_RAGE_AUFTRAG_PROZENT/100);
		}
		
		array_push($JS_actioncosts, $tempAction);
	}
	$tpl->assign("JS_actioncost", $JS_actioncosts);
	//$tpl->display('jobs.js.tpl');
	//js ende
	
	$select1="Spieler:
	<select name=target_id>";
		foreach ($players as $value) {
			$selected = ($value[id] == $target_id) ? " selected" : "";
			if ($value[id] != $status[id]) {
				$select1.="
					<option value=\"$value[id]\"".$selected.">$value[syndicate] - (".pointit($value['land'])." ha)</option>
				";
			}
			else {
				$select1.="
					<option disabled value=\"$value[id]\">$value[syndicate] - (Ihr Konzern)</option>
				";
			}
		}
		if (count($players) == 0) {
			$select1.="
				<option value=0>Keine Spieler in diesem Syndikat gefunden</option>
			";
		}
	$select1.="</select>";
	$tpl->assign("select1", $select1);						
	$select2.="	<select name=type id=\"at_type\" onChange=\"setStuff()\"".(($changetarget)?'':' disabled').">";
		$i=0;			
		if ($target_id > 0) { // 23.08.2013 hafke - nur einblenden wenn target gewählt
			foreach ($types as $tvalue) {
				//Dark-john rage auskommentiert	
				//$rage = $tvalue[action_key] == "siege" || $tvalue[action_key] == "killspies";
				$rage=false;
				$select2.="
					<option $noset value=\"$tvalue[action_key]\">$tvalue[name]";
					if ($tvalue[minprice]) {$select2.=" (- Min:".pointit($tvalue[minprice])." Cr. - Max:".pointit($tvalue[maxprice])." Cr.)";}
					elseif ($tvalue[type] == 'op' && $tvalue[action_key] == 'delayaway') $select2.=" (- Min:".pointit(sabotageminprice($defland,5))." Cr. - Max:".pointit(sabotagemaxprice($defland,5))." Cr.)"; //makemenicer Konstanten
					elseif ($tvalue[type] == 'op' && $tvalue[action_key] != 'delayaway') $select2.=" (- Min:".pointit(sabotageminprice($defland,3))." Cr. - Max:".pointit(sabotagemaxprice($defland,3))." Cr.)"; //makemenicer Konstanten
					else $select2.=" (- Min:".pointit(attackminprice($target_id, $defland, $tvalue[action_key]))." Cr. - Max:".pointit(attackmaxprice($target_id, $defland, $tvalue[action_key]))." Cr.)"; //makemenicer Konstanten)
				$i++;
			}			
		} else {
			$select2 .= "<option $noset value=\"\">Du musst zuerst ein Ziel wählen</option>";
		}
	//Dark-john rage auskommentiert	
	//$rage = $types[$type][action_key] == "siege" || $types[$type][action_key] == "killspies";
	$rage=false;
	$tpl_landgain = ceil(landgain($status[land], $status[rid], $target_id));
	$select2.="</option></select>";
	$tpl->assign("select2", $select2);
	$tpl->assign("changetarget", $changetarget);
	$tpl->assign("costs", ceil((attackminprice($target_id, $defland, $type)+attackmaxprice($target_id, $defland, $type))/2));
	$tpl->assign("jshelp1", js::simplehelp("Wenn Sie denselben Auftrag mehrfach ausführen lassen wollen, können sie hier die Anzahl festlegen (max 10)."));
	$tpl->assign("landgain", $tpl_landgain);
	$tpl->assign("jshelp2", js::simplehelp("Bei Angriffen kann zusätzlich ein Mindestergebnis, beispielsweise für die Anzahl<br> zu erobernden Landes festgelegt werden, auf Spionage/Sabotageaktionen hat dieser Wert keinen Einfluss."));
}

//********************
//	Auftrag annehmen ?
//********************
elseif ($action == "take" && $job_id) {
	if ($time > $globals[roundendtime]) {
        // keine Auftraege mehr nach Rundenende
		$errormsg = "Runde zu Ende! Sie können keine Aufträge mehr annehmen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	} else {
		$infomsg = "Wenn sie einen Auftrag annehmen, müssen sie diesen innerhalb von ".(AUFTRAGTIME / 60)." Minuten erfolgreich erfüllen oder 10% der Entlohnung als Konventionalstrafe entrichten!<br>Es wird nicht geprüft, ob sie in der Lage sind, den angenommenen Auftrag auszuführen.<br><br>
		<center><a href=\"jobs.php?ia=take&job_id=$job_id\">Auftrag annehmen</a><br><br>
		<a href=\"jobs.php\">Zurück</a></center>";
		$tpl->assign('INFO', $infomsg);
		$tpl->display('info.tpl');
    }
}

if ($status[paid] != 1) {
	$ausgabe="";
	$errormsg = "$probeaccountfehler";
	$tpl->assign('ERROR', $errormsg);
	$tpl->display('fehler.tpl');
	//include("reminder.php");
}



db_write($queries);
$tpl->display('jobs.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


function gettypes($pid) {
	global $status;
	$types = getattacktypes($pid);
	$racherecht = racherecht($pid);
	$inwar=inwaractive($status[rid],$pid) || inwar($status[rid],$pid);
	$zusatz="";
	//Wenn Rache nicht 1 ist dann hole nicht typen spykill und verzögern aus der DB ansonsten hole alle typen aus der DB
	$zusatz = (!($racherecht==1 || $inwar== 1) ? " and !(action_key like '%kill%') and !(action_key like '%delay%')" : "");
	$ttypes = assocs("select action_key,name,difficulty,type from spyaction_settings where action_key != 'killsciences' and !(action_key like '%get%') $zusatz order by name","action_key");
	$played_modifier = calc_price_modifier_by_time_played();
	foreach ($ttypes as $key => $temp) {
		if ($temp[difficulty] == "easy") {
			$mul = 1;
		}
		elseif ($temp[difficulty] == "medium") {
			$mul = 2;
		}
		elseif ($temp[difficulty] == "hard") {
			$mul = 3;
		}
		elseif ($temp[difficulty] == "veryhard") {
			$mul = 4;
		}
		if($ttypes[$key]['type'] == 'ip'){
			$ttypes[$key]['minprice'] = 75000*$mul*$played_modifier;
			$ttypes[$key]['maxprice'] = 600000*$mul*$played_modifier;
		}
	}
	$types=array_merge($types,$ttypes);
	return $types;
}

function getattacktypes ($pid) {
	global $status;
	$types = array();
	//echo "debug: ".$status[rid]+" - pid:"+$pid."<br>";
	$racherecht = racherecht($pid);
	//$rache = $racherecht + inwar($status[rid],$pid);
	//$test=inwarpassiv($status[rid],$pid);
	$inwaractive=inwaractive($status[rid],$pid) || inwar($status[rid],$pid);
	//pvar("Rache: ".$racherecht." Krieg: ".inwar($status[rid],$pid)." Krieg (aktiv): ".$inwaractive);
	//Runde 63 Standardaufträge sind bei direktem RR möglich dark-john 15.05.2012 - tatsächlich ab R65 ;) (inok1989)
	if ($racherecht==1) $types[conquer] = array(action_key =>"conquer",name =>"Standardangriff",difficulty => "attack",type => "att");
	//if($rache==1 || $inwaractive==1) $types[normal] = array(action_key =>"normal" ,name =>"Landdezimierung",difficulty => "attack",type => "att");
	if ($racherecht==1||$inwaractive) $types[siege] = array(action_key =>"siege" ,name =>"Belagerungsangriff",difficulty => "attack",type => "att");
	if ($racherecht==1||$inwaractive) $types[killspies] = array(action_key =>"killspies" ,name =>"Spione zerstören",difficulty => "attack",type => "att");
	return $types;
}

function getattacktypesA () {
	$types = array();
	$rache = 1;
	$types[normal] = array(action_key =>"normal" ,name =>"Landdezimierung",difficulty => "attack",type => "att");
	$types[siege] = array(action_key =>"siege" ,name =>"Belagerungsangriff",difficulty => "attack",type => "att");
	$types[conquer] = array(action_key =>"conquer",name =>"Standardangriff",difficulty => "attack",type => "att");
	$types[killspies] = array(action_key =>"killspies" ,name =>"Spione zerstören",difficulty => "attack",type => "att");
	
	$zusatz = "";
	$ttypes = assocs("select action_key,name,difficulty,type from spyaction_settings where action_key != 'killsciences' and !(action_key like '%get%') $zusatz order by name","action_key");
	$played_modifier = calc_price_modifier_by_time_played();
	foreach ($ttypes as $key => $temp) {
		if ($temp[difficulty] == "easy") {
			$mul = 1;
		}
		elseif ($temp[difficulty] == "medium") {
			$mul = 2;
		}
		elseif ($temp[difficulty] == "hard") {
			$mul = 3;
		}
		elseif ($temp[difficulty] == "veryhard") {
			$mul = 4;
		}
		if($ttypes[$key]['type'] == 'ip'){
			$ttypes[$key]['minprice'] = 75000*$mul*$played_modifier;
			$ttypes[$key]['maxprice'] = 600000*$mul*$played_modifier;
		}
	}
	$types=array_merge($types,$ttypes);
	return $types;
}

function typeok($type) {
	global $types;
	if ($type) {
		foreach ($types as $t) {
			if ($t[action_key] == $type) {
				$ok = 1;
				break;
			}
		}
		if (!$ok) {
			$type="";
		}
	}

	return $type;
}

function attackminprice($target, $dland, $type) {
	//$rage eingefügt by Christian 16.10.2010
	//$rage ist true wenn spykill oder belagerung vorliegt
	/*$landkosten = landkosten(); // auf modifizierten Landpreis umgestellt - R4bbiT - 22.02.11
	$landgain = landgain($aland,$dland);
	if($rage)
		$back = 15000000+$dland*$dland;
	else 
		$back = floor($landkosten * $landgain * LANDKOSTEN_AUFTRAG_PROZENT / 100);
	*/
	//landpreis*Landgain (beide des Auftragsgebers)*1,05 + 5 mio Cr , r61 dark-john
	// 50000*600*1.05+5m

	if($type == 'conquer') {
		//pvar("Gain: ".$gain." Preis: ".$alandprice);
		global $status;
		$alandprice=landkosten();
		$gain = landgain($status[land], $status[rid], $target);
		
		return ceil(($gain*$alandprice*1.05)+5000000);
	} else {
		return 10000000+$dland*$dland;
	}
}

function attackmaxprice($target, $dland, $type) {
	//if($rage) //$rage eingefügt by Christian 16.10.2010
		//$value=floor(attackminprice($aland, $dland,$rage)*LANDKOSTEN_RAGE_AUFTRAG_PROZENT_MAX/LANDKOSTEN_RAGE_AUFTRAG_PROZENT);
	//else
		/*$value= floor(attackminprice($aland, $dland,$rage)*LANDKOSTEN_AUFTRAG_PROZENT_MAX/LANDKOSTEN_AUFTRAG_PROZENT);
	return ($value < MINMAX_JOB) ? MINMAX_JOB : $value;*/
	//der Maximalpreis ist Landpreis*Landgain*1,2, r61 dark-john

	if($type == 'conquer') {
		global $status;
		$alandprice=landkosten();
		$gain = landgain($status[land], $status[rid], $target);
		
		return floor((1.2*$gain*$alandprice)+15000000);
	} else {
		return 20000000+$dland*$dland;
	}
}

function sabotageminprice($dland, $ops){
	return floor($dland * 300 * $ops); // makemenicer Konstanten
}

function sabotagemaxprice($dland, $ops){
	return floor($dland * 500 * $ops);
}


function calc_show_group($maxid,$minid) {
	global $status;
	$idcount = $maxid-$minid;
	$groupcount = $idcount/SHOWGROUPS_COUNT;
	$groupcount == 0 ? $groupcount = 1: 1;
	$back = floor ((($status[id] - $minid) / $groupcount));
	return $back;
}



function calc_price_modifier_by_time_played() {
	$days_played = round_days_played();
	$modifier = (floor($days_played / 7)) * 0.5;
	return (1+$modifier);
}



function printmyjobs() {
	global $status,$time,$myjobs,$types,$outtime,$myjobids, $types_str;
	$jobsForTemplate = array();
				foreach ($myjobids as $value){
					$temp = array();
					$temp{3} = "";
					if (count($myjobs[$value][syndicate]) == 0){
						$temp{0} = "Das Ziel Ihres Auftrages existiert nicht mehr, Sie sollten diesen Auftrag zurücknehmen.";
					}
					else{
						$temp{0} = $myjobs[$value][syndicate]."(#".$myjobs[$value][rid].")";
					}
					$notfound=true;
					foreach ($types as $tvalue) {
						if ($tvalue['action_key'] == $myjobs[$value]['type']){
							$temp{1} = "$tvalue[name]";
							$notfound=false;
						}
					}
					if($notfound){
					foreach ($types_str as $tvalue) {
						if ($tvalue['action_key'] == $myjobs[$value]['type']){
							$temp{1} = "$tvalue[name]";
						}
					}
					}
					if ($myjobs[$value][money]){
						$temp{2} = pointit($myjobs[$value][money])." Credits";
					}
					//$back = chopp(chopp(chopp(chopp(chopp($back)))));
					if ($myjobs[$value][anonym]) {
						$temp{3} .= "Auftrag wird anonym ausgeführt.<br>";
					}
					if ($myjobs[$value][number] > 1) {
						$temp{3} .= "Der Auftrag wird noch ".$myjobs[$value][number]." mal ausgeführt.<br>";
					}
					if ($myjobs[$value][param] > 0) {
						$temp{3} .= "Mindestergebnis: ".pointit($myjobs[$value][param]).".<br>";
					}
					if ($myjobs[$value][onlinetime] < $outtime ) {
						$temp{3} = "Dieser Auftrag wurde seit mehr als 3 Tagen nicht erfüllt und wird daher nicht mehr angezeigt, sie sollten diesen Auftrag zurücknehmen.";
					}
					$temp{4} = $value;
					array_push($jobsForTemplate,$temp);
				}	
	return $jobsForTemplate;
}

?>