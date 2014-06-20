<?

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//
$inneractions = array("access","showactions","get","store","showpoints","showprod","transfer","transfertake","transferrefuse");
if (in_array($inneraction,$inneractions)) {$inneraction = $inneraction;} else {$inneraction = "";}

$receiver > 0 ? $receiver = round($receiver) : $receiver = "";
$tid > 0 ? $tid = round($tid) : $tid = 0;

//echo "REC: $receiver <br>IA: $inneraction<br> number: $number";

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");


if(!$sciences["glo20"]){
	require_once("../../inc/ingame/header.php");
	$errormsg = "Um das Lager und den Transfer von Rohstoffen zwischen den Mitgliedern des Syndikats nutzen zu können, benötigen Sie die Forschung <em>Basic Storage System</em>.";
	$tpl->assign('ERROR', $errormsg);
	$tpl->display('fehler.tpl');
	require_once("../../inc/ingame/footer.php");
	exit;
}

$lagerbar = energyadd($id,3)-$status[energy]; // Damit nicht zuviel Energie entnommen wird
$number = check_int($number);
$number_request = check_int($number_request);



//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

if (($status[createtime] + 60*60*DELAY_AFTER_START) <= $time) {
	$queries = array();
	$zeit = 24; // 24 h aktionen anzeigen
	$tpl->assign("zeit", $zeit);
	$resstats = getresstats();
	foreach ($resstats as $k => $value) {
		if ($value[type] != "money") {
			//$resstats[$k][value] *= RESSTATS_MODIFIER;
		}
	}

	if ($product) {
		$ok = 0;
		foreach ($resstats as $value) {
			if ($product == $value{type}) {$ok = 1;break;}
		}
		$ok == 0 ? $product = "" : 1;
	}


	$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden

	//**************************************************************************//
	//**************************************************************************//
	//							Eigentliche Berechnungen!						//
	//**************************************************************************//
	//**************************************************************************//


	//							selects fahren									//
	// Syndvalues holen
	$podvalues = assoc("select * from syndikate where synd_id=".$status{rid});
	$members = assocs("select id,syndicate,podpoints,lager_prohibited  from status where rid = ".$status{rid}." order by syndicate asc","id");
	$myoffers = assocs("select * from transfer where user_id=".$status{id}." and finished = 0","transferid");
	$offers = assocs("select * from transfer where receiver_id=".$status{id}." and finished = 0","transferid");
	//							Berechnungen									//

	//**********************************************************
	//                  SHOWACTIONS
	//**********************************************************
	
	$tpl->assign("inneraction", $inneraction);
	$tpl->assign("currency", $podvalues{currency});

	if ($inneraction == "showactions") {

		$lasttime = $time - 60*60*$zeit; // 24 stunden lagerzugriffe
		$actions = assocs("select * from lagerlogs where rid=".$status[rid]." and time > $lasttime order by time desc");
		$memberids = singles("select id from status where rid=$status[rid]");
		$transfers = assocs("select *,'transfer' as action from transfer where receiver_id in (".implode(",",$memberids).") and time > $lasttime and finished=2 order by time desc");
		$actions = array_merge($actions,$transfers);
		function cmp($a,$b) {
			if ($a[time] < $b[time]) {
				return 1;
			}
			if ($a[time] == $b[time]) {
				return 0;
			}
			else {
				return -1;
			}
		}
		usort($actions,"cmp");
		
		
		if (count($actions) > 0) {
		
			$podactions = array();

			foreach ($actions as $values) {
			
				$podaction = array();
				$showtime = date("d.m.Y, H:i",$values{'time'});
				if ($values{action} == "store") {$tempaction = "Einlagern";}
				elseif ($values{action} == "get") {$tempaction = "Entnehmen";}
				elseif ($values{action} == "transfer") {$tempaction = "Transfer";}
				elseif ($values{action} == "auktion") {$tempaction = "Einlagern - Auktion";}
				if (!isset($members{$values{user_id}})) {
					$members{$values{user_id}} = assoc("select * from status where id = ".$values{user_id});
				}
				if ($values[action] == "transfer") {
					$konzanzeige = $members{$values{user_id}}{syndicate}." <i>an</i> ".$members{$values{receiver_id}}{syndicate};		
				}
				else {
					$konzanzeige = $members{$values{user_id}}{syndicate};		
				}
				
				$podaction[0]=$konzanzeige;
				$podaction[1]=$resstats{$values{product}}{name};
				$podaction[2]=pointit($values{number});
				$podaction[3]=$tempaction;
				$podaction[4]=$showtime;
				
				// Bei Transfers Gegenforderung anzeigen
				if ($values[action] == "transfer" && $values{number_request} > 0) {
					
					$podaction[5] = $members{$values{receiver_id}}{syndicate}." <i>an</i> ".$members{$values{user_id}}{syndicate};		
					$podaction[6] = $resstats{$values{product_request}}{name};
					$podaction[7] = pointit($values{number_request});
					
				}
				
				array_push($podactions, $podaction);
				
			} ## foreach (@actions)
			
			$tpl->assign("podactions", $podactions);
			
		}# falls überhaupt actions

		else {
			$errormsg = "Es wurden keine Zugriffe auf das Lager in den letzten 24 Stunden registriert";
			$tpl->assign('ERROR', $errormsg);
		}

	}

	//**********************************************************
	//                  SHOWPOINTS
	//**********************************************************

	else if ($inneraction == "showpoints") {
	
		$podvaluearray = array();
		
		$gesamt_guthaben = 0;
		foreach ($members as $values) {
			$temppod = array();
			$temppod[0] = $values{syndicate}." (#".$status{rid}.")";
			$temppod[1] = pointit($values{podpoints});
			array_push($podvaluearray, $temppod);
			$gesamt_guthaben += $values['podpoints'];
		}
		
		$wert_gesamt = 0;
		foreach($resstats as $ress => $val){
			$wert_gesamt += $podvalues['pod'.$ress] * $val['value'];
		}
		// Eingefügt, dass das Gesamtspielerguthaben und der Wert des Lagers angezeigt wird - R4bbiT - 19.11.10
		
		$tpl->assign('USER_GESAMT', $gesamt_guthaben); // Gesamtguthaben der Spieler
		$tpl->assign('WERT_POD', $wert_gesamt); // Wert des Lagers
		$tpl->assign("podvalues", $podvaluearray);
		
	}

	//**********************************************************
	//                  SHOWPROD
	//**********************************************************

	else if ($inneraction == "showprod") {

		$lasthourtime = get_hour_time($time);
		$proddata = assocs("select syn_moneyadd, syn_metaladd, syn_sciencepointsadd, syn_energyadd, podpointsplus, user_id from nw_safe where time >= $lasthourtime and rid=".$status[rid], "user_id");

		$memberprod = array();
		
		foreach ($members as $ky => $values) {
		
			$tempprod = array();
			
			$tempprod[0] = $values{syndicate}." (#".$status{rid}.")";
			$tempprod[1] = pointit((int) ($proddata[$ky][syn_moneyadd]));
			$tempprod[2] = pointit((int) ($proddata[$ky][syn_energyadd]));
			$tempprod[3] = pointit((int) ($proddata[$ky][syn_metaladd]));
			$tempprod[4] = pointit((int) ($proddata[$ky][syn_sciencepointsadd]));
			$tempprod[5] = pointit((int) ($proddata[$ky][podpointsplus]));

			$resplus[money] += $proddata[$ky][syn_moneyadd];
			$resplus[energy] += $proddata[$ky][syn_energyadd];
			$resplus[metal] += $proddata[$ky][syn_metaladd];
			$resplus[sciencepoints] += $proddata[$ky][syn_sciencepointsadd];
			$resplus[podpoints] += $proddata[$ky][podpointsplus];
			
			array_push($memberprod,$tempprod);
		}
		
		$tpl->assign("memberprod", $memberprod);

		$totalres[0] = pointit($resplus[money]);
		$totalres[1] = pointit($resplus[energy]);
		$totalres[2] = pointit($resplus[metal]);
		$totalres[3] = pointit($resplus[sciencepoints]);
		$totalres[4] = pointit($resplus[podpoints]);
		
		$tpl->assign("totalres", $totalres);

	}

	//**********************************************************
	//                  ABHEBEN
	//**********************************************************

	elseif ($inneraction == "get") {// && $number && $product) {
		$beschr = "";
		foreach ($resstats as $temp) {
			if (${$temp[type]}) {
				$number = check_int(${$temp[type]});
				$product = $temp[type];
				if (isGesperrtUser($status[lager_prohibited], $product)) {
					$errormsg = "Sie dürfen diesen Ressourcentyp momentan nicht aus dem Lager entnehmen. Der Pr&auml;sident Ihres Syndikats hat Ihnen den Zugriff auf das Syndikatslager für diesen Ressourcentyp bis ".mytime(get_hour_time($status[lager_prohibited]))." untersagt.";
					$tpl->assign('ERROR', $errormsg);
				}
				elseif ($number > 0) {
					$wbuffer = array("insert into lager_buffer (user_id,type,number,time,synd_id) values(".$status{id}.",'$product',$number,$time,$status[rid])");
					$wok = db_write($wbuffer);
					if ($wok) {
						$mybuyid = single("select get_id from lager_buffer where user_id =".$status{id}." order by get_id desc limit 1");
						$firstbuyid = 0;
						$waitcycles = 0;
						$getallowed = true;
						// Schleife, bis alle anderen käufe abgewickelt sind
						while ($mybuyid != $firstbuyid && $waitcycles < 30) {
							$timelimit = $time - 30;
							$firstbuyid = single("select get_id from lager_buffer where get_id <= $mybuyid and synd_id='$status[rid]' and type='$product' and time > $timelimit");
							usleep(200000);
							$waitcycles++;
						}
						if ($waitcycles >= 30) {
							$getallowed = false; 
							$errormsg = "$temp[name]: Es gibt momentan zuviele Entnahmeanfragen zu diesem Produkt, bitte versuchen sie es in einigen Sekunden noch einmal";
							$tpl->assign('ERROR', $errormsg);
						}
						// BUFFER ENDE
						$podproduct = "pod".$product;
						// Genug Podpoints ?
						if ( ($status{podpoints}-$number*$resstats{$product}{value}) < (0 - $status{land}*$podvalues{maxschulden})) {
							$errormsg = "$temp[name]: Sie besitzen nicht genügend ".$podvalues{currency}." um soviele Ressourcen aus dem Lager zu entnehmen";
							$tpl->assign('ERROR', $errormsg);
						}
						// Checken ob genügend Ressourcen vorhanden sind
						else if ($podvalues{$podproduct} < $number) {
							$errormsg = "Soviel ".$resstats{$product}{name}." können sie nicht aus dem Syndikatslager entnehmen";
							$tpl->assign('ERROR', $errormsg);
						}
						else if ($product == "energy" && $number > $lagerbar) {
							$errormsg = "Sie besitzen nicht genügend Lagerkapazitäten, um soviel Energie zu speichern, die Entnahme wurde abgebrochen.";
							$tpl->assign('ERROR', $errormsg);
						}
						// Wenn genügend Podpunkte und Genügend Ressourcen vorhanden sind, dann abheben
						else {
							if ($getallowed) {
								// Podwert bestimmen
								$podwert = ceil($resstats{$product}{value} * $number);
								$podvalues{$podproduct} -=$number;
								$status{$product} += $number;
								$status{podpoints} -= $podwert;
								$status{nw} = nw($status{id});
								// Dbeinträge
								$actions = array ("update status set podpoints = podpoints - $podwert, $product = $product + $number,nw =".$status{nw}." where id =".$status{id},
											"update syndikate set $podproduct = $podproduct - $number where synd_id =".$status{rid},
											"insert into lagerlogs (user_id,rid,number,product,time,action) values (".$status{id}.",".$status[rid].",".$number.",'".$product."',".$time.",'".$inneraction."')");

								foreach ($actions as $value) {
									array_push ($queries,$value);
								}
								// Erfolgsmeldung
								$beschr .= "Sie haben ".pointit($number)." ".$resstats{$product}{name}." für ".pointit($podwert)." ".$podvalues{currency}." aus dem Syndikatslager entnommen.<br>";
							}
						}
						$wende = array("delete from lager_buffer where get_id = $mybuyid");
						db_write($wende);
					}
				}
			}
		}
		if($beschr)
			$tpl->assign("MSG", $beschr);
	} # inneraction eq "get"

	//**********************************************************
	//                  EINZAHLEN
	//**********************************************************

	if ($inneraction == "store" ) {// && $number && $product) {
		$beschr = "";
		foreach ($resstats as $temp) {
			if (${$temp[type]}) {
				$number = check_int(${$temp[type]});
				$product = $temp[type];
				if ($status[lager_prohibited] > $time && FALSE) { // Auch bei Sperrung ist einlagern erlaubt
					$errormsg = "Sie dürfen momentan keine Ressourcen in das Lager einzahlen. Der Pr&auml;sident Ihres Syndikats hat Ihnen den Zugriff auf das Syndikatslager bis zum ".mytime($status[lager_prohibited])." untersagt.";
					$tpl->assign('ERROR', $errormsg);
				}
				elseif ($number > 0) {
					$podproduct = "pod".$product;
					// Nachprüfen ob spieler genug Ressourcen besitzt
					if ($status{$product} < $number) {
						$errormsg = "Soviel ".$resstats{$product}{name}." besitzen sie nicht";
						$tpl->assign('ERROR', $errormsg);
					}

					// Falls genügend Ressourcen vorhanden sind
					else {
						//pvar($product);
						if ($product == "money") {
							$temp_zinsen = $sciences{ind23} ? IND22BONUS_ZINSEN : ZINSEN_CREDITS;
						}
						else $temp_zinsen = $sciences{ind23} ? IND22BONUS_ZINSEN : ZINSEN;
						/* // Niedrigere Zinsen abgeschafft Runde 39 - 2. Januar 2009
						if ($artefakte[$game_syndikat[artefakt_id]][bonusname] == "reduced_podtaxes")
							$temp_zinsen *= (100 - $artefakte[$game_syndikat[artefakt_id]][bonusvalue]) / 100;
						*/
						
						
						$showloss = ceil($number-$number*(100-$temp_zinsen)/100);
						// Savenumber wir später vom status abgezogen
						$savenumber = $number;
						$number = floor($number * (100-$temp_zinsen)/100);
						$podvalues{$podproduct} += $number;
						$podgain = floor ($number * $resstats{$product}{value});
						$status{$product} -= $savenumber;
						$status{podpoints} += $podgain;
						$status{nw} = nw($status{id});
						$beschr .= "Sie haben ".pointit($number)." ".$resstats{$product}{name}." für ".pointit($podgain)." ".$podvalues{currency}." im Syndikatslager eingelagert. ".pointit($showloss)." ".$resstats{$product}{name}." wurden als Steuern** einbehalten.<br>";
						$tpl->assign("MSG", $beschr);
						// Dbeinträge
						$actions = array ("update status set podpoints = podpoints + $podgain, $product = $product - $savenumber,nw = ".$status{nw}." where id =".$status{id},
									"update syndikate set $podproduct = $podproduct + $number where synd_id =".$status{rid},
									"insert into lagerlogs (user_id,rid,number,product,time,action) values (".$status{id}.",".$status[rid].",".$number.",'".$product."',".$time.",'".$inneraction."')");
						foreach ($actions as $value) {
							array_push ($queries,$value);
						}
						// Dividenden auszahlen
						$dividendenbetrag = round($showloss);//*$resstats{$product}{value});
						
						// Nur die ersten 2 Einlagerungen pro Stunde und Spieler geben Dividenden
						$hour_time = get_hour_time($time);
						$anzahl_einlagerungen = single("select count(*) from lagerlogs where user_id=".$status[id]." and time > $hour_time and action like 'store'");
						
						//if ($anzahl_einlagerungen < 2) { // Um Tricksereien zu verhindern
							dividenden($status{rid},$dividendenbetrag,$product);
						//}
					}
				}
			}
		}
		if($beschr)
			$tpl->assign("MSG", $beschr);
	} # inneraction eq "store"

	//**********************************************************
	//                  ÜBERWEISEN
	//**********************************************************

	if ($inneraction == "transfer" && ($number > 0 || $number_request > 0) && $product && $receiver && ($product_request || !$number_request) && isset($number_request) && isset($number)) {
		## Checken ob spieler im selben syndikat ist
		$number = floor($number);
		$number_request = floor ($number_request);
		$product = mysql_real_escape_string($product);
		$product_request = mysql_real_escape_string($product_request);
		$receiverstatus = getallvalues($receiver);
		$receivesciences = getsciences($receiver);
		
		$transferwert = round($resstats{$product}{value} * $number);
		$transferwert_back = round($resstats{$product_request}{value} * $number_request);
		if ($receiverstatus{rid} != $status{rid} || $receiverstatus{id} == $id) {
			$errormsg = "Sie können Ressourcen nur an Syndikatsmitglieder überweisen";
			$tpl->assign('ERROR', $errormsg);
		}
		## Checken, ob Empfänger überhaupt Ressourcen empfangen darf (könnte ja gesperrt sein)
		elseif (isGesperrtUser($receiverstatus[lager_prohibited], $product)) {
			$errormsg = "Der Lagerzugriff für den Konzern <b>".$receiverstatus[syndicate]."</b> ist für diesen Ressourcentyp momentan gesperrt. Sie dürfen diesem Spieler keine Ressourcen dieses Typs überweisen.";
			$tpl->assign('ERROR', $errormsg);
		}
		## Checken ob der Sender die geforderte Ressource empfangen darf, könnte ja gesperrt sein
		elseif (isGesperrtUser($status[lager_prohibited], $product_request)) {
			$errormsg = "Ihr Lagerzugriff auf diese Ressource ist zur Zeit gesperrt. Sie können diese Ressource daher nicht einfordern.";
			$tpl->assign('ERROR', $errormsg);
		}
		## Checken ob Empfänger über genügend Podpunkte verfügt
		elseif (($receiverstatus{podpoints}-$transferwert+$transferwert_back) < (0- $receiverstatus{land}*$podvalues{maxschulden})) {
			$errormsg = $receiverstatus{syndicate}." (#".$receiverstatus{rid}.") verfügt nicht über genügend ".$podvalues{currency}." um ihre Überweisung annehmen zu können.";
			$tpl->assign('ERROR', $errormsg);
		}
		## Checken ob Versender über genügend Podpunkte verfügt
		elseif ($status[podpoints] - $transferwert_back + $transferwert < ( 0- $status{land}*$podvalues{maxschulden} ) ) {
			$errormsg = "Sie verfügen nicht über genügend Handelspunkte um soviele Ressourcen fordern zu können.";
			$tpl->assign('ERROR', $errormsg);		
		}
		## Checken ob genug ressourcen vorhanden sind (Sender)
		elseif ($status{$product} < $number) {
			$errormsg = "Soviel ".$resstats{$product}{name}." besitzen sie nicht";
			$tpl->assign('ERROR', $errormsg);
		}
		## Checken ob genug ressourcen vorhanden sind (Empfänger)
		elseif ($receiverstatus[$product_request] < $number_request) {
			$errormsg = "Der Mitspieler besitzt nicht genügend ".$resstats{$product_request}{name}.".";
			$tpl->assign('ERROR', $errormsg);
		}
		## checken ob empfänger schon auf lager zugreifen darf
		elseif ( ($time-DELAY_AFTER_START*60*60) <=  $receiverstatus[createtime] || !$receivesciences["glo20"]) {
			$errormsg = "Der Spieler <b>".$receiverstatus[syndicate]."</b> darf noch keine Ressourcentransfers annehmen.";
			$tpl->assign('ERROR', $errormsg);
		}
		## checken ob empfänger im Umode ist und eine Gegenforderung gestellt wurde
		elseif ( $number_request > 0 && $receiverstatus{alive}==2) {
						$errormsg = "Der Spieler <b>".$receiverstatus[syndicate]."</b> befindet sich im Urlaubsmodus oder ist inaktiv. Sie können keine Resourcen von ihm fordern!";
						$tpl->assign('ERROR', $errormsg);
		}
		elseif ($receiverstatus{alive}!=2 && $receiverstatus{"lastlogintime"} + TIME_TILL_INACTIVE < $time) {
						$errormsg = "Der Spieler <b>".$receiverstatus[syndicate]."</b> ist inaktiv. Sie können keine Resourcen mit ihm tauschen!";
						$tpl->assign('ERROR', $errormsg);
		}
		## Ausführen der Überweisung
		else {
			## falls spieler nicht alle überweisungen annimmt
			if (!(($receiverstatus[$product."get"] == 1 || $number  == 0) && ($receiverstatus[$product_request."give"] == 1 || $number_request == 0))) {
				$status{$product} -= $number;
				$status{nw} = nw($status{id});
				//Dbeinträge
				## Ressourcen des Senders updaten
				$action ="update status set $product=$product-$number,nw = ".$status{nw}." where id=".$status{id};
				array_push($queries,$action);
				## In Transfertable schreiben
				$action ="insert into transfer (user_id,receiver_id,product,number,product_request,number_request,time,price,request_price) values (".$status{id}.",".$receiverstatus{id}.",'".$product."',".$number.",'".$product_request."',".$number_request.",$time,".$resstats[$product][value].",".$resstats[$product_request][value].")";
				array_push($queries,$action);
				## Message
				$werte = $status{syndicate}."|".$status{rid}."|".pointit($number)."|".$resstats{$product}{name}."|".pointit($transferwert)."|".$podvalues{currency}."|".pointit($number_request)."|".$resstats[$product_request][name]."|".pointit(($transferwert_back))."|".$podvalues{currency};
				## Nachricht an Empfänger der Überweisung
				// Kategorie 6, Id 6
				$action ="insert into message_values (id,user_id,time,werte) values (6,".$receiverstatus{id}.",".$time.",'".$werte."')";
				array_push($queries,$action);
				$beschr = "Sie möchten <b>".pointit($number)." ".$resstats{$product}{name}."</b> im Wert von momentan ".pointit($transferwert)." "
					.$podvalues{currency}."<br>gegen <b>".pointit($number_request)." ".$resstats[$product_request][name]."</b> im Wert von momentan "
					.pointit($transferwert_back)." $podvalues[currency]<br>mit ".$receiverstatus{syndicate}." (#".$receiverstatus{rid}
					.") tauschen.<br>Sie werden benachrichtigt, sobald der Empfänger ihre Überweisung entgegengenommen hat";
				$tpl->assign("MSG", $beschr);
			} # receiverstatus acceptall = 0
			else  {
				$number1 = &pointit($number);
				$number1_request = &pointit($number_request);
				$value1 = &pointit($transferwert);
				$value2 = &pointit($transferwert_back);
				$status{$product} -= $number;
				$status{podpoints} +=$transferwert;
				$status{podpoints} -= $transferwert_back;
				$status{nw} = nw($status{id});
				//Dbeinträge
				$action ="update status set ".$product."=".$product."-".$number.", $product_request=$product_request+$number_request,podpoints=podpoints-".($transferwert_back-$transferwert).",nw=".$status{nw}." where id=".$status{id};
				array_push($queries,$action);
				$action ="update status set ".$product."=".$product."+".$number.", $product_request=$product_request-$number_request,podpoints=podpoints-".($transferwert-$transferwert_back)." where id=".$receiverstatus{id};
				array_push($queries,$action);
				$werte =$status{syndicate}."|".$status{rid}."|".pointit($number)."|".$resstats{$product}{name}."|".pointit($transferwert)."|".$podvalues{currency}."|".$number1_request."|".$resstats{$product_request}{name}."|".$value2."|".$podvalues{currency};
				// Kategorie 6, id 7
				$action ="insert into message_values (id,user_id,time,werte) values (7,".$receiverstatus{id}.",".$time.",'".$werte."')";
				array_push($queries,$action);
				### überweisung in logs schreiben
				$action ="insert into transfer (user_id,receiver_id,product,number,product_request,number_request,time,finished,price,request_price) values (".$status{id}.",".$receiverstatus{id}.",'$product',".$number.",'".$product_request."',".$number_request.",".$time.",2,".$resstats{$product}{value}.",".$resstats{$product_request}{value}.")";
				array_push($queries,$action);
				$hpdiff = $transferwert-$transferwert_back;
				if ($hpdiff < 0) {
					$hpdifftext = abs($hpdiff).' '.$podvalues{currency}.' abgezogen.';
				}
				else { $hpdifftext = $hpdiff.' '.$podvalues{currency}.' gutgeschrieben.'; }
				$beschr = "Sie haben ".pointit($number)." ".$resstats{$product}{name}." an ".$receiverstatus{syndicate}
					." (#".$receiverstatus{rid}.") überwiesen. Sie haben von $receiverstatus[syndicate] $number1_request "
					.$resstats{$product_request}{name}." erhalten. Ihnen wurden ".$hpdifftext;
					//.pointit($transferwert-$transferwert_back)." ".$hpdifferenz;
				$tpl->assign("MSG", $beschr);
			}# receiverstatus acceptall = 1
		} # else von checkanweisungen
	} # inneraction == transfer

	//**********************************************************
	//                  TRANSFER ANNEHMEN
	//**********************************************************

	if ($inneraction == "transfertake" && $tid) {
		### Daten über Transfer besorgen
		$transfer = assoc("select * from transfer where transferid=".$tid);
		// Wenn transfer noch nicht verrechnet wurde
		if ($transfer{finished} == 0) {
			$transferwert = round($resstats{$transfer{product}}{value} * $transfer{number});
			$transferwert_back = round($resstats{$transfer{product_request}}{value} * $transfer{number_request});
			$senderstatus = assoc("select * from status where id=$transfer[user_id]");
			$receivesciences = getsciences($transfer{receiver_id});
			if ($transfer{receiver_id} != $status{id}) {f("Sie sind nicht Adressat dieser Überweisung");}
			elseif ( ($status{podpoints}-$transferwert+$transferwert_back) < (0- $status{land}*$podvalues{maxschulden})) {f("Sie benötigen mehr ".$podvalues{currency}." um diese Überweisung annehmen zu können");}
			elseif ($status[$transfer{product_request}] < $transfer{number_request}) {
				$errormsg = "Sie können diesen Transfer nicht annehmen, da Sie nicht genügend ".$resstats{$transfer{product_request}}{name}." besitzen.";
				$tpl->assign('ERROR', $errormsg);
			}
			elseif ($senderstatus[podpoints]+$transferwert-$transferwert_back < (0- $senderstatus{land}*$podvalues{maxschulden}) ) f("Der Konzern $senderstatus[syndicate](#$senderstatus[rid]) verfügt momentan nicht über genügend $podvalues[currency] um die geforderten Ressourcen annehmen zu können. Probieren Sie es später noch einmal oder lehnen Sie diese Überweisung ab.");
			## checken ob empfänger schon auf lager zugreifen darf
			elseif (!$receivesciences["glo20"]) {
				$errormsg = "Der Spieler <b>".$receiverstatus[syndicate]."</b> darf noch keine Ressourcentransfers annehmen.";
				$tpl->assign('ERROR', $errormsg);
			}
		### falls adressat richtig
			else {
				
				$status{$transfer{product}} += $transfer{number};
				$status{$transfer{product_request}} -= $transfer{number_request};
				
				$status{podpoints} = $status{podpoints} - $transferwert + $transferwert_back;
				$status{nw} = nw($status{id});
				//Dbeinträge
				$action ="update transfer set finished=2 where transferid=".$tid;
				array_push($queries,$action);
				$action ="update status set ".$transfer{product}."=".$transfer{product}."+".$transfer{number}.",".$transfer{product_request}."=".$transfer{product_request}."-".$transfer{number_request}.",podpoints=podpoints-".($transferwert-$transferwert_back).", nw =".$status{nw}." where id=".$status{id};
				array_push($queries,$action);
				$action ="update status set podpoints=podpoints - ".($transferwert_back-$transferwert).",".$transfer{product_request}."=".$transfer{product_request}."+".$transfer{number_request}." where id=".$transfer{user_id};
				array_push($queries,$action);
				$werte =$status{syndicate}."|".$status{rid}."|".pointit($transfer{number})."|".$resstats{$transfer{product}}{name}."|".pointit($transfer[number_request])."|".$resstats{$transfer{product_request}}{name}."|".pointit($transferwert-$transferwert_back)."|".$podvalues{currency};
				// Kategorie 6 id 8
				$action ="insert into message_values (id,user_id,time,werte) values (8,".$transfer{user_id}.",".$time.",'".$werte."')";
				array_push($queries,$action);
				$beschr = "Überweisung erfolgreich angenommen";
				$tpl->assign("MSG", $beschr);
			} #else

		}
		else {
			$errormsg = "Diese Überweisung wurde bereits bearbeitet";
			$tpl->assign('ERROR', $errormsg);
		}
	}

	//**********************************************************
	//                  ZUGRIFF BESCHRÄNKEN
	//**********************************************************
	
	if ($inneraction == "access") {
		$innerinneractions = array("sperren","entsperren");
		in_array($innerinneraction,$innerinneractions) ? 1 : $innerinneraction = "";
		
		if ($innerinneraction == "sperren" && $user_id) {
			if ($status[ispresident]) {
				// Übergabe checken
				$user_id = (int) $user_id;
				$days = floor($days);
				$hours = floor($hours);
				if ($hours > 24) {$days++; $hours = 0;};
				$hours += $days * 24;
				
				if ($hours <= 0) {
					$errormsg = "Sie müssen eine positive Stunden/Tageszahl eingeben, für die Sie den Zugriff auf die gewählten Ressourcen sperren wollen";
					$tpl->assign('ERROR', $errormsg);
				} else {
					$sperrtime = get_hour_time($time) + $hours * 3600;
	
					// Informationen darüber, was gesperrt ist wird in Form von Zahlen an die Sperrtime (die ja auf ganze h zurechtgemünzt ist)
					// angehängt:
					// 1 Credits
					// 2 Energie
					// 4 Erz
					// 8 Forschungspunkte
					// Jede Ressource wird einfach aufaddiert. Maximaler Wert also 1 + 2 + 4 + 8 = 15 für alle Ressourcen gesperrt
					
					$sperrtime > $globals[roundendtime] ? $sperrtime = $globals[roundendtime] : 1;
	
					if ($money) $sperrtime += 1;
					if ($energy) $sperrtime += 2;
					if ($metal) $sperrtime += 4;
					if ($sciencepoints) $sperrtime += 8;
					
					
					// User id übergeben im Syndikat ?
					$userok = 0;
					foreach ($members as $temp) {
						if ($temp[id] == $user_id) {
							$userok = 1; break;
						}
					}
		
					if ($userok == 1) {
						$beschr = "Zugriff auf das Lager für <b>".$members[$user_id][syndicate]."</b> bis zum <b>".mytime(get_hour_time($sperrtime))."</b> gesperrt.";
						$tpl->assign("MSG", $beschr);
						$members[$user_id][lager_prohibited] = $sperrtime;
						select("update status set lager_prohibited = ".$sperrtime." where id=$user_id");
					}
					else {
						$errormsg = "Sie können den Lagerzugriff nur für Spieler aus Ihrem Syndikat sperren.";
						$tpl->assign('ERROR', $errormsg);
					}
				}
			}
			else {
				$errormsg = "Es ist nur dem Syndikatspräsident gestattet, den Zugriff auf das Lager zu kontrollieren.";
				$tpl->assign('ERROR', $errormsg);
			}
						
		}
		
		if ($innerinneraction == "entsperren" && $user_id) {
			if ($status[ispresident]) {
				// User id übergeben im Syndikat ?
				$userok = 0;
				foreach ($members as $temp) {
					if ($temp[id] == $user_id) {
						$userok = 1; break;
					}
				}
				if ($userok == 1) {
					$beschr = "Zugriff auf das Lager für <b>".$members[$user_id][syndicate]."</b> wieder gestattet.";
					$tpl->assign("MSG", $beschr);
					$members[$user_id][lager_prohibited] = 0;
					select("update status set lager_prohibited = 0 where id=$user_id");
				}
				else {
					$errormsg = "Sie können den Lagerzugriff nur für Spieler aus Ihrem Syndikat entsperren.";
					$tpl->assign('ERROR', $errormsg);
				}
			}
			else {
				$errormsg = "Es ist nur dem Syndikatspräsident gestattet, den Zugriff auf das Lager zu kontrollieren.";
				$tpl->assign('ERROR', $errormsg);
			}
		}
		
		// Für ausgabe Standardwerte
		$aday = date("d", $time)+1;
		$amonth = date("m", $time);
		$ayear = date("Y", $time);
		
		$lagermembers = array();
		
		if ($status[ispresident] == 1){
			$tpl->assign("ispresidente",true);
		}
		
		foreach ($members as $temp) {
			
			$tempmember = array();
			
			$tempmember[0] = $temp[lager_prohibited] < $time;
			$tempmember[1] = $temp[id];
			
			if($temp[lager_prohibited] < $time){
				//nix
			}
			else {
				$tempmember[2] = (isGesperrtUser($temp[lager_prohibited]));
				$tempmember[3] = mytime(get_hour_time($temp[lager_prohibited]));
			}
			
			if ($status[ispresident] == 1) {
				$tempmember[4] = ($temp[lager_prohibited] > $time ? (isGesperrtUser($temp[lager_prohibited], "money") ? "ja" : "nein"): "<input type=checkbox name=money checked>");
				$tempmember[5] = ($temp[lager_prohibited] > $time ? (isGesperrtUser($temp[lager_prohibited], "energy") ? "ja" : "nein") : "<input type=checkbox name=energy>");
				$tempmember[6] = ($temp[lager_prohibited] > $time ? (isGesperrtUser($temp[lager_prohibited], "metal") ? "ja" : "nein") : "<input type=checkbox name=metal>");
				$tempmember[7] = ($temp[lager_prohibited] > $time ? (isGesperrtUser($temp[lager_prohibited], "sciencepoints") ? "ja" : "nein") : "<input type=checkbox name=sciencepoints>");		
			}
			
			$tempmember[8] = $temp[syndicate];
			
			array_push($lagermembers,$tempmember);
		}
		
		$tpl->assign("lagermember",$lagermembers);
			
	}
	
	//**********************************************************
	//                  TRANSFER ZURÜCKWEISEN
	//**********************************************************


	if ($inneraction == "transferrefuse" && $tid) {
		
		
		
		
		$transfer = assoc("select * from transfer where transferid =".$tid);
		
		
		if ($transfer[receiver_id] == $status[id] or $transfer{user_id} == $status[id]) {
			
			if ($transfer{finished} == 0) {
				//Dbeinträge
				$action="update transfer set finished=1 where transferid=".$tid;
				array_push($queries,$action);
				$action="update status set ".$transfer{product}."=".$transfer{product}."+".$transfer{number}." where id =".$transfer{user_id};
				array_push($queries,$action);
				// Temporäre und konditionale Daten
				if ($status{id} == $transfer{user_id}) {
					$status{$transfer{product}} += $transfer{number};
					$beschr = "Überweisung erfolgreich zurückgenommen";
					$tpl->assign("MSG", $beschr);
					$werte= $status{syndicate}."|".$status{rid}."|".pointit($transfer{number})."|".$resstats{$transfer{product}}{name};
					// Kategorie 6, id 9
					$action ="insert into message_values (id,user_id,time,werte) values (9,".$transfer{receiver_id}.",".$time.",'".$werte."')";
					array_push($queries,$action);
				}
				else {
					$werte= $members{$transfer{receiver_id}}{syndicate}."|".$status{rid}."|".pointit($transfer{number})."|".$resstats{$transfer{product}}{name};
					// Kategorie 6,id 10
					$action ="insert into message_values (id,user_id,time,werte) values (10,".$transfer{user_id}.",".$time.",'".$werte."')";
					array_push($queries,$action);
					$beschr = "Überweisung zurückgewiesen";
					$tpl->assign("MSG", $beschr);
				}
			}
			else {
				$errormsg = "Diese Überweisung wurde bereits verrechnet";
				$tpl->assign('ERROR', $errormsg);
			}
		}
		else {
			$errormsg = "Sie können nur Überweisungen ablehnen, die an Sie adressiert sind.";
			$tpl->assign('ERROR', $errormsg);
		}
	}


	//							Daten schreiben									//

	db_write($queries);
	//							Ausgabe     									//
	
	$tpl->assign("ripf",$ripf);
	
	if ($goon)	{

		$tpl->assign("goon", true);
		
		if(count($myoffers)>0 || count($offers)>0) {
			//nix
		}
		
		$transactions=array();
		
		foreach ($myoffers as $key => $value) {
			if ($key != $tid) {
				$tmptrans = array();
				$tmptrans[0] =  "toone";
				$tmptrans[1] = pointit($value{number});
				$tmptrans[2] = $resstats{$value{product}}{name};
				if ($value{number_request} > 0) {
					$tmptrans[3] = pointit($value{number_request});
					$tmptrans[4] = $resstats{$value{product_request}}{name};
				}
				$tmptrans[5] = $members{$value{receiver_id}}{syndicate};
				$tmptrans[6] = $treceiver." (#".$status{rid}.")"; 
				$tmptrans[7] = $value{transferid};
				array_push($transactions, $tmptrans);
			}
		}

		foreach ($offers as $key => $value) {
			if ($key != $tid) {
				$tmptrans = array();
				$tmptrans[0] = "tome";
				$tmptrans[1] = pointit($value{number});
				$tmptrans[2] = $resstats{$value{product}}{name};
				$tmptrans[5] = $members{$value{user_id}}{syndicate};
				$tmptrans[6] = "(#".$status{rid}.")";
				if ($value{number_request} > 0) {
					$tmptrans[3] = pointit($value{number_request});
					$tmptrans[4] = $resstats{$value{product_request}}{name};
				}
				$tmptrans[7] = $value{transferid};
				array_push($transactions, $tmptrans);
			}
		}
		
		$tpl->assign("transactions", $transactions);

		if(count($myoffers)>0 || count($offers)>0) {
			//nix
		}


		// Ausgabe Start
			$lagerress = array();
			foreach ($resstats as $value) {
				$eachress = array();
				$maxtake = floor(($status[podpoints] + $podvalues{maxschulden}*$status[land]) / $resstats[$value[type]][value]);
				$maxtake < 0 ? $maxtake = 0:1;
				if ($value[type] == "energy") {
					$speicherbar = speicherbar($status,$sciences);
					$showspeicherbar = $speicherbar - $status[energy];
					if ($maxtake > $showspeicherbar) {
						$maxtake = $showspeicherbar;
					}
				}
				$maxtake > $podvalues[("pod".$value[type]."")] ? $maxtake = $podvalues[("pod".$value[type]."")] : 1;
				$res = "pod".$value{type};
				$eachress[0] = $value{name};
				$eachress[1] = $podvalues{$res};
				$eachress[2] = (isGesperrtUser($status[lager_prohibited], $value[type]) ? "<b>gesperrt</b>":pointit($maxtake));
				$eachress[3] = $value[type];
				$eachress[4] = "maxbuy('".$value[type]."',".$status[$value{type}].",1)";
				$eachress[5] = "maxbuy('".$value[type]."',".$maxtake.",0)";
				array_push($lagerress,$eachress);
			}
			$tpl->assign("lagerress",$lagerress);
			$tpl->assign("lagergut",pointit($status{podpoints}));
			$tpl->assign("lagercur",$podvalues{currency});
			$tpl->assign("maxschuld",pointit($podvalues{maxschulden}*$status[land]));
			$tpl->assign("MAXSAVE", pointit(getSavePodPoints($status, $sciences, $game_syndikat['artefakt_id'])));
			$tpl->assign("lagercur",$podvalues{currency});				
			$add1 = $add2 = "";
			$ressvals = array();
			foreach($resstats as $key => $value) {
				array_push($ressvals,array($value{name},sprintf("%0.2f",$value{value})));
			}
			$tpl->assign("ressvals",$ressvals);
			//$add = chopp($add);

			/*
			###### Hier Formular zum Abheben

			$ausgabe.="

			<form action=pod.php method=post>
			<input type=hidden value=\"get\" name=inneraction>

			<select name=product>
			";
			foreach ($resstats as $value) {
				$ausgabe.="<option value=".$value{type}.">".$value{name}."</option>";
			}
			$ausgabe.="
			</select>
			<input class=\"input\" name=number value\"0\" size=8>
			<input class=\"button\" type=submit value=\"Entnehmen\">
			</form>
			";

			###### Hier Formular zum einzahlen

			$ausgabe.="
			<form action=pod.php method=post>
			<input type=hidden value=\"store\" name=inneraction>

			<select name=product>
			";
			foreach ($resstats as $value) {
				$ausgabe.="<option value=".$value{type}.">".$value{name}."</option>";
			}
			$ausgabe.="
			</select>
			<input class=\"input\" name=number value\"0\" size=8>
			<input class=\"button\" type=submit value=\"Einlagern\">
			</form>

			</center>
			";
			##### Überweisungsträgerformular
			*/
			
			$lagerressjs=array();
			foreach ($resstats as $value) {
				array_push($lagerressjs,array($value{type},$status[$value{type}],$value[value]));
			}
			$tpl->assign("lagerressjs",$lagerressjs);
			
			$select1="";
			foreach ($resstats as $value) {
				$select1.="<option value=".$value{type}.">".$value{name}."</option>";
			}
			$tpl->assign("select1",$select1);
			$select2="";
			foreach ($members as $values) {
				if($values{id} != $status{id}) {
					unset($emoname, $selected);
					if ($pre_id == $values[id]) $selected = "selected";
					$emoname = single('SELECT username FROM users WHERE konzernid = '.$values['id']);
					$select2.="<option $selected value=\"".$values{id}."\">".$values{syndicate}.(($emoname && $status[may_access_boards]) ? ' ['.$emoname.']' : '')."</option>";
				}
			}
			$tpl->assign("select2",$select2);
			$tpl->assign("select3",$select2);
					
			$select4="";
			$resstats = array_reverse($resstats);
			foreach ($resstats as $value) {
				$select4.="<option value=".$value{type}.">".$value{name}."</option>";
			}
			$tpl->assign("select4",$select4);
			$select5="";
			foreach ($resstats as $value) {
				if (!isGesperrtUser($status[lager_prohibited], $value[type]))
				$select5.="<option value=".$value{type}.">".$value{name}."</option>";
			}
			$tpl->assign("select5",$select5);
			$tpl->assign("strcr",( $sciences{ind23} ? IND22BONUS_ZINSEN : ZINSEN_CREDITS));
			$tpl->assign("stres", ( $sciences{ind23} ? IND22BONUS_ZINSEN : ZINSEN));
		// Ausgabe Ende
		
	} # $goon ende
	
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


	$tpl->display('pod.tpl');
	
} // Wenn createtime..

else {
	require_once("../../inc/ingame/header.php");
	if ($globals[roundstatus] == 0) {
		$errormsg = "Diese Seite ist erst ".DELAY_AFTER_START."h nach Spielbeginn verfügbar.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	} else {
		$errormsg = "Diese Seite ist erst ".DELAY_AFTER_START."h nach Erstellung des Konzerns verfügbar.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");


//// Dateispezifische Funktionen

function isGesperrtRessource($ressourcenname, $value) {
	$resvalues = array("money" => 1, "energy" => 2, "metal" => 4, "sciencepoints" => 8);
	if (!$resvalues[$ressourcenname]) {
		echo "error: falscher Ressourcenname übergeben";
		return 0;
	}
	$reihenfolge = array("sciencepoints", "metal", "energy", "money");
	for ($i = 0; $i <= 3; $i++) {
		if ($reihenfolge[$i] == $ressourcenname) {
			if ($value - $resvalues[$ressourcenname] >= 0) return 1;
		} else {
			if ($value - $resvalues[$reihenfolge[$i]] >= 0) $value -= $resvalues[$reihenfolge[$i]];
		}
	}
	return 0;
}

function isGesperrtUser($prohibited_time, $ressourcenname = "") {
	global $time;
	$hourtime = get_hour_time($prohibited_time);
	if ($hourtime > $time) {
		$sperrcode = $prohibited_time - $hourtime;
		
		if ($ressourcenname) {	// Wird immer dann verwendet wenn eine bestimmte Aktion mit einer Ressource getätigt werden soll
			return isGesperrtRessource($ressourcenname, $sperrcode);
		}
		
		$reihenfolge = array("money" => "Cr", "energy" => "MWh", "metal" => "t", "sciencepoints" => "P");
		$return = array();
		foreach ($reihenfolge as $resname => $outputname) {
			if (isGesperrtRessource($resname, $sperrcode)) $return[] = $outputname;
		}
		return join(", ", $return); // Für die normale Anzeige, die alle Syndikastmitglieder sehen können. Sie sehen dann, welche Ressourcen genau beim Spieler gesperrt sind.
	} else return 0;
}

?>