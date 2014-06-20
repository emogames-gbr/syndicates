<?

//**************************************************************************//
//	ï¿½ergabe Variablen checken
//**************************************************************************//

if ($pos && $pos != 1 && $pos != 2 && $pos != 3 && $pos != 4 && $pos != 5): $pos = 1; endif;
if ($synd_id): $synd_id = floor($synd_id); endif;
$up = floor($up);
$down = floor($down);


//**************************************************************************//
//	Dateispezifische Finals deklarieren
//**************************************************************************//

//define ("BUILDTIME",12); 	// Zeit, die eine lvl 1 Forschung braucht.
define ("PROBEMAXLEVEL",1); // Maxlevel fr Forschungen bei Konzernen in Probezeit
define ("UIC_INDUSTRIAL_SPEEDBONUS",0); // UIC forscht 25% schneller im industrial zweig
$probeaccountfehler = "Probeaccounts können nur Stufe 1 Forschungen erforschen.";

//**************************************************************************//
//	Game.php includen
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//	Variablen initialisieren
//**************************************************************************//

//berater zeugs
$thissite="forschung";
$t = $time; 
if ($globals[roundstatus] == 0)	{ $t = $globals[roundstarttime] + 1;};

$x = 0;

$ausgabe_for = "";

$hour = date("H");

$forname = "";
$searchtime = "";
$propriate_action_1 = "";
$propriate_action_2 = "";
$propriate_action_3 = "";
$total = 0;
$remain = 0;

$goon = 1;
$searchtime = "";

//ee
$queries = array();	
if ($ia == "killqu")		{

	$proceed = check_validity($what, $type, $killtime);

	if ($proceed)		{

		//all
		$searchtime = get_hour_time($t) + $killtime * 60 * $globals[roundtime];
		$total = row(get_propriate_action($what, $type, "select number"));
		$total = $total[0];
		//endall

		if ($innestaction == "next")	{

			if ($total >= $n)	{
				
				//all
				$remain = $total - $n;
				$ok = 0;
				//endall
				
				//fos
				if ($what == "sc"){
					$ok=1;
					$beschr = "Du hast soeben die Forschung an \"$forname\" abgebrochen.";
					$tpl->assign("MSG", $beschr);
				}
				//endfos
				
				//all
				if ($ok) {
					$queries[] = get_propriate_action($what, $type, "delete from");
					$queries[] = get_propriate_action($what, $type, "log");
					if ($remain)
						$queries[] = get_propriate_action($what, $type, "insert into");
				}
				unset($ia);
				//endall
			} else {
				
				//all
				unset($innestaction);
				//endall
			}
			
		} else {
			if ($total)
				print_kill_output($what, $type, $killtime);
			else 
				unset($ia);
		}
	} else { 
		unset($ia); 
	}
} else { 
		unset($ia); 
}	

if ($queries) db_write($queries);
$tpl->assign("USERINPUT", $userinput);
//ff
$queries = array();



$syndwerte = assoc("select * from syndikate where synd_id = $status[rid]");
$queries = array();								//hier kommt spï¿½er die mysql action rein

//$fehler = "";									//Fehlerausgabe
$build_science = "";								//Forschung die im Moment "gebaut" wird.
$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, description,desc_uic,desc_sl,desc_pbf,desc_nof,desc_neb, gamename, short, sciencecosts,id from sciences where available=1 order by treename desc, level asc, typenumber desc", "name");	//der science Table
// UIC COSTBONUS EINRECHNEN --- wird jetzt in forschable gemacht (update benÃ¶tigt auch die kosten!!)
//pvar($sciencestats,nacher);

$highestAllQ=array(); //for all tree

$scienceid = "";									//ID der Forschung, die bearbeitet wird
$sciencestufe = "";									//Stufe der Forschung, die bearbeitet wird
$sciencetype = "";									// Typ der Forschung, die bearbeitet wird

$timetogo = 0;									//Zeit, die die Forschung noch braucht

//werden erst zum tick fertig
//$ausnahmen_fuer_nicht_sofort_fertig = array("ind16", "glo12", "ind15", "ind14", "glo11");

//fosbonusausnahmen
$ausnahmen_fuer_fosbonus = array("ind19", "mil18", "glo20");

//**************************************************************************//
//**************************************************************************//
//	Eigentliche Berechnungen!
//**************************************************************************//
//**************************************************************************//

//	selects fahren	//
$build_science = assoc( "select * from build_sciences where user_id =".$status{id});
$globals{roundstatus} == 1 ? $hourtime = get_hour_time($time) : $hourtime = $globals{roundstarttime};
$timetogo = ($build_science{time} - $hourtime)/ 3600 ;
$forschungsq = $features[FORSCHUNGSQ];

if (!$forschungsq) { // Gucken ob Forschungsassi bei aktiviertem Urlaubsmodus freigeschaltet wird.
	$forschungsq = single("select count(*) from options_vacation where user_id = $id and starttime > $time and endtime = 0");
}
if ($forschungsq)	{
	$queued = assocs("select name, position from kosttools_forschungsq where konzernid=$id order by position");
}

// Neb forschungsmalus ?

if ($status[race] == "neb") {
	$nebmalus = NEB_SCIENCE_MALUS;
}
else {
	$nebmalus = 0;
}

//	Berechnungen	//


if ($action == "queue" && $forschungsq)	{
	$anzahl_queued = count($queued);
	if ($anzahl_queued < 5)	{
		if ($sciencestats[$what])	{
			if ($sciencestats[$what][level] <= PROBEMAXLEVEL || $status[paid] == 1) {
				$queries[] = "insert into kosttools_forschungsq (konzernid, name, position) values ($id,'$what',".($anzahl_queued+1).")";
				$queued[] = array( "name" => $what, "position" => $anzahl_queued+1);
			}
			else {
				$tpl->assign('ERROR', $probeaccountfehler);
				//include("reminder.php");
			}
		}
		else { $tpl->assign('ERROR', "Ungltige Forschung gewählt.");}
	}
	else { $tpl->assign('ERROR', "Sie haben bereits 5 Forschungen in die Warteschlange gestellt. Entfernen Sie zunächst eine dieser Forschungen daraus oder warten Sie bis die Warteschlange abgearbeitet wurde, bevor Sie eine weitere Forschung hineinstellen können!");}
}
elseif ($action == "queue" && !$forschungsq) { $tpl->assign('ERROR', "Sie haben den Forschungsassistenten für Ihren EmoGames-Account nicht freigeschaltet. Sie können daher keine Forschungen in die Warteschlange stellen. Kaufen Sie bitte den Forschungsassistenten, wenn Sie diese Funktion benutzen möchten."); }

if ($action == "unqueue" && $forschungsq)	{
	$anzahl_queued = count($queued);
	if ($anzahl_queued)	{
		$queries[] = "delete from kosttools_forschungsq where konzernid=$id and position=$pos";
		for ($i = 0; $i < $anzahl_queued; $i++)	{
			if ($queued[$i][position] == $pos)	{
				unset($queued[$i]);
				break;
			}
		}
		if ($anzahl_queued > 1 and $pos != $anzahl_queued)	{
			$queries[] = "update kosttools_forschungsq set position=position-1 where konzernid=$id and position > $pos";
			foreach ($queued as $ky => $vl)	{
				if ($vl[position] > $pos): $queued[$ky][position] -= 1; endif;
			}
		}
	}
	else { $tpl->assign('ERROR', "Sie haben keine Forschung in der Warteschlange stehen. Welche Forschung möchten Sie da bitteschön entfernen ?");}
}
elseif ($action == "unqueue" && !$forschungsq) { $tpl->assign('ERROR', "Sie haben den Forschungsassistenten für Ihren EmoGames-Account nicht freigeschaltet. Sie können daher keine Forschungen in die Warteschlange stellen. Kaufen Sie bitte den en, wenn Sie diese Funktion benutzen möchten."); }

if ($action == "unqueueall" && $forschungsq)	{
	$anzahl_queued = count($queued);
	if ($anzahl_queued)	{
		$queries[] = "delete from kosttools_forschungsq where konzernid=$id";
		$tpl->assign('MSG', "Sie haben die Einträge in Ihrem Forschungsassistenten erfolgreich gelöscht.");
		$queued = array();
	}
	else { $tpl->assign('ERROR', "Sie haben keine Forschungen in der Warteschlange stehen. Welche Forschungen möchten Sie da bitteschön entfernen ?");}
}
elseif ($action == "unqueueall" && !$forschungsq) { $tpl->assign('ERROR', "Sie haben den Forschungsassistenten für Ihren EmoGames-Account nicht freigeschaltet. Sie können daher keine Forschungen in die Warteschlange stellen. Kaufen Sie bitte den Forschungsassistenten, wenn Sie diese Funktion benutzen möchten."); }


if ($action == "modifyqueue" && $forschungsq)	{
	$anzahl_queued = count($queued);
	$pos = floor($pos);
	if ($anzahl_queued)	{
		if ($anzahl_queued > 1)	{
			if ($up or $down)	{
				if ($pos > 0 && $pos < $anzahl_queued && $up)	{
					$validq = 1;
				}
				elseif ($pos > 1 && $pos <= $anzahl_queued && $down)	{
					$validq = 1;
				}
				if ($validq)	{
					$temparray = $queued;
					foreach ($temparray as $ky => $vl) {
						if ($vl[position] == $pos):
							$name1 = $vl[name];
							if ($up): $queued[$ky][position] = $pos+1; $newpos = $pos+1;endif;
							if ($down): $queued[$ky][position] = $pos-1; $newpos = $pos-1; endif;
						endif;
						if ($down and $vl[position] == $pos-1):
							$queued[$ky][position] = $pos; endif;
						if ($up and $vl[position] == $pos+1):
							$queued[$ky][position] = $pos; endif;
					}
					$queries[] = "delete from kosttools_forschungsq where konzernid=$id and position=$pos";
					$queries[] = "update kosttools_forschungsq set position=$pos where position=".($up ? ($pos+1):($down ? ($pos-1):""))." and konzernid=$id";
					$queries[] = "insert into kosttools_forschungsq (konzernid, name, position) values ($id, '$name1', $newpos)";
				}
			}
			else { $tpl->assign('ERROR', "Ein Parameter fehlt!"); }
		}
		else { $tpl->assign('ERROR', "Sie haben nur eine Forschung in der Warteschlange stehen. Wo es keine Reihenfolge gibt, kann auch keine Reihenfolge geändert werden ;)."); }
	}
	else { $tpl->assign('ERROR', "Sie haben keine Forschung in der Warteschlange stehen. Welche Forschung möchten Sie da bitteschön ändern ?");}
}
elseif ($action == "modifyqueue" && !$forschungsq) { $tpl->assign('ERROR', "Sie haben die Forschungsqueue für Ihren EmoGames-Account nicht freigeschaltet. Sie können daher keine Forschungen in die Warteschlange stellen. Kaufen Sie sich bitte das Forschungsqueue-Extra über BETREIBER.de, wenn Sie diese Funktion benutzen möchten."); }

if ($inneraction) {							//$push ist 1 (true) falls fos beschleunigt wird
	if ($build_science{name}) { 						// wenn bereits geforscht wird -> Abbruch
		fehlermeldung("alreadyforsching");
	}
	else {									//es wird im moment noch nichts geforscht: weitermachen
		if ($sciencestats[$inneraction]) {						//existiert die zu forschende Forschung?
			if ($sciencestats[$inneraction][level] <= PROBEMAXLEVEL || $status[paid] == 1) {
				
				$valid = forschable($inneraction, $sciencestats, $sciences, $status[sciencepoints]);
				
				//wenn fosbonus genutzt werden soll ..
				if ($push
				&& $status['later_started_bonus']
				&& !in_array($sciencestats[$inneraction]['name'], $ausnahmen_fuer_fosbonus)
				&& ($valid[0] || $valid[2] == "sciencepoints")) {
					
					$modifikator = 1; // $status[race] == "sl" ? 0.5 : 
					
					$timetopush = fos_duration($sciencestats[$inneraction]['level']) * $modifikator;
					$nwgain		= pointit(constant("NW_FOS_LVL".$sciencestats[$inneraction]['level']));
					
					//fos komplett forschen
					if ($timetopush <= $status['later_started_bonus']) {

						if (!$sciences[$inneraction]) {
							$queries[] = "insert into usersciences (user_id, name, level) values (".$status['id'].", '".$sciencestats[$inneraction]['name']."', 1)";
						} else {
							$queries[] = "update usersciences set level = level + 1 where name = '".$inneraction."' and user_id=".$status['id'];
						}
						
						$sciences[$inneraction] = $sciences[$inneraction]+1;
						$tpl->assign('MSG', "Stufe ".$sciences[$inneraction]." der Forschung \"".$sciencestats[$inneraction]['gamename']."\" wurde fertig gestellt.");
						
						$status['later_started_bonus'] -= $timetopush;
						$queries[] = "update status set later_started_bonus = later_started_bonus - ".$timetopush." where id = ".$status['id'];
				
					//fos nur teilweise forschen
					} else {
						
						//endzeit um restbonus vermindern
						$time_end = zeit($inneraction, $status['later_started_bonus']);
						
						$status{nw} = nw($status{id});
						array_push($queries, "update status set nw=".$status[nw]." where id =".$status{id});
						array_push($queries, "insert into build_sciences (user_id, time, name) values (".$status{id}.",".$time_end.",'".$inneraction."')");
						array_push($queries, "insert into build_logs (user_id, subject_id,time,time_end,number,action,what)
														values (".$status{id}.",'".$sciencestats[$inneraction][id]."',".$time.",".$time_end.",'".($sciences[$inneraction]+1)."',0,'sci')");
						$science = getsciences($status{id});
						$build_science{name} = $inneraction;
						$build_science{time} = $time_end;
						$timetogo = ($time_end - $hourtime)/ 3600 ;
						$tpl->assign('MSG', "Forschung erfolgreich in Auftrag gegeben und mit der verbleibenden Bonuszeit beschleunigt.<br>Verbleibende Dauer: ".$timetogo." Stunden.");
						
						$status['later_started_bonus'] = 0;
						$queries[] = "update status set later_started_bonus = 0 where id = ".$status['id'];
						
					}
				
				//kein bonus vorhanden
				} elseif ($push && !$status['later_started_bonus']) {
					
					$tpl->assign('ERROR', "Sie haben keine Forschungsbonuszeit erhalten.");
					
				//forschung ist in ausnahmearray
				} elseif ($push && in_array($sciencestats[$inneraction]['name'], $ausnahmen_fuer_fosbonus)) {
					
					$tpl->assign('ERROR', "Diese Forschung darf nicht beschleunigt werden.");
					
					//normales forschen (ohne bonus)
				} else {
				
					if ($valid[0])	{
						$status{sciencepoints} -= $valid[1];
						$status{nw} = nw($status{id});
						$time_end = zeit($inneraction);
						array_push($queries, "update status set nw=".$status[nw].",sciencepoints=sciencepoints -".$valid[1]." where id =".$status{id});
						array_push($queries, "insert into build_sciences (user_id, time, name) values (".$status{id}.",".$time_end.",'".$inneraction."')");
						array_push($queries, "insert into build_logs (user_id, subject_id,time,time_end,number,action,what)
														values (".$status{id}.",'".$sciencestats[$inneraction][id]."',".$time.",".$time_end.",'".($sciences[$inneraction]+1)."',0,'sci')");
						$science = getsciences($status{id});
						$tpl->assign('MSG', "Forschung erfolgreich in Auftrag gegeben.");
						$build_science{name} = $inneraction;
						$build_science{time} = $time_end;
						$timetogo = (zeit($inneraction) - $hourtime)/ 3600 ;
					}
					elseif ($valid[2] == "sciencepoints") { fehlermeldung("sciencepoints"); }
					elseif ($valid[2] == "baumstruktur") { fehlermeldung("baumstruktur"); }
					else { fehlermeldung($valid[1]); }
				}
			}
			else {
				$tpl->assign('ERROR', $probeaccountfehler);
				//include("reminder.php");
			}

		} else { fehlermeldung("notexisting");}
	}
}



//	Ausgabe		//

$tpl->assign("USERINPUT", $userinput);

if ($show != "showsynd") {
	if ($error_ausgabe) {$tpl->assign('ERROR', $error_ausgabe);}
	if ($status['later_started_bonus']) {
		$tpl->assign('INFO', "Es sind noch <b>".$status['later_started_bonus']." Bonusstunden</b> übrig.
			Diesen können Sie nutzen um Forschungen zu beschleunigen bzw direkt durchzuforschen. 
			Unterschreitet Ihr restliches Bonuszeitbudget die für die jeweilige Forschung notwendige
			Zeit, so wird die ausgewählte Forschung um die restliche Bonuszeit beschleunigt und muss
			dann regulär fertig geforscht werden.<br>
			(Auf die Bonusstunden gilt kein Forschungsbonus. Die Forschungen Basic Unit Construction, 
			Basic Trade Program und Basic Storage System müssen immer regulär erforscht werden)");
	}
	
	// Aktuelle Forschung
	$build_science['o_gamename'] = $sciencestats[$build_science["name"]]["gamename"];
	if ($build_science['name']) $tpl->assign('TIMETOGO', $timetogo);
	if ($status['beraterview']) $tpl->assign('TIMETOGO_DATE', datum("d.m.Y, H", $build_science{time}).":00");
	$tpl->assign('BUILD_SCIENCE', $build_science);
		
	// Mit Assistent
	if ($forschungsq) {
		// Warteliste
		if (count($queued)) {
			$tpl->assign('ANZ_FOR', count($queued));	
			usort($queued, "forschungssort");
			$sc_queue = $sciences;		
			$sc_queue[$build_science["name"]]++;	
			$queued_output = array();	
			foreach ($queued as $vl) {
				$pos=$vl["position"];
				$n=$vl["name"];
				$vl['o_gamename'] = $sciencestats[$n]["gamename"];
				
				//Erweiterung der Anzeige im Fos-Assi in Runde 46
				$chk_costs = forschable($n, $sciencestats, $sc_queue, $status["sciencepoints"]);
				$vl['o_fosbar'] = $chk_costs[0];
				$vl['o_fosbar_kosten'] = pointit($chk_costs[1]);
				$vl['o_fosbar2'] = $chk_costs[2];
				if ($chk_costs[0] == 1)	$sc_queue[$n]++;
				elseif ($chk_costs[0] == 0 && $chk_costs[2] == "sciencepoints") $sc_queue[$n]++;
				$vl['o_level'] = $sc_queue[$n];
				array_push($queued_output, $vl);
			}
			$tpl->assign('QUEUED', $queued_output);
		}
		else {
			// Warteliste leer
		}
	}
	$fos_output = array();
	$fos = array("mil", "ind", "glo", "all");
	foreach($fos as $tree) {
		$ausgabe_temp = array();
		$valid_forschungslevel = array();
		$temp_output = array(1 => array(),
							2 => array(),
							3 => array(),
							4 => array(),
							5 => array(),
							6 => array(),
							7 => array());
		foreach ($sciencestats as $ky => $vl)	{
			if ($vl['group'] == $tree)	{
				$valid = forschable($ky, $sciencestats, $sciences, $status['sciencepoints']);
				// 	Bei Forschung in bau sollen schon die höheren Forschungskosten des nächsten Levels angezeigt werden.
				if ($build_science{'name'} == $vl['name']) {	
					$tempSciences = $sciences;
					$sciences[$vl['name']]++;
					$valid = forschable($ky, $sciencestats, $sciences, $status['sciencepoints']);
					$sciences = $tempSciences;	
				}
	
				if ($valid[0] or (!$valid[0] and ($valid[2] == "sciencepoints"  or $valid[1] == "maxlevelreached" or $valid[1] == "levelfull" or ($valid[1] == "alreadybestlevel" and $build_science[level] != 7))))	{
					$valid_forschungslevel[$vl[level]] = 1;
				}
				
				// Wartelistenpositionen berechnen
				if ($forschungsq) {
					foreach ($queued as $qvl)	{
						if ($qvl['name'] == $ky)	{
							$temp[$qvl['position']]=$qvl['position'];
							$vl['o_delposition'] = $qvl['position'];
						}
					}					
					if ($temp)	{
						ksort($temp);
						$vl['o_inAssi_position'] = join( ", ", $temp); 
					}
				}
		
				// Level berechnen
				$vl['o_lvl'] = $sciences[$ky]; // aktueller Level
				// $vl['maxlevel'] // höchst möglicher Level
				
				// "aktuelle" Kosten berechnen
				$vl['o_show_kosts'] = ($valid[0] or (!$valid[0] && ($valid[2] == "sciencepoints" || $valid[2] == "baumstruktur")));
				$vl['o_kosts'] = pointit($valid[1]);
				
				// Forschungsbeschreibung
				$vl['o_description'] = $vl[description].$vl['desc_'.$status['race']];
				
				// Überprüfen ob (noch/schon) forschbar, in den Assistent hinzufügbar 
				$vl['o_show_erforschen'] = ($valid[0] or (!$valid[0] && $valid[2] == "sciencepoints" or $valid[1] == "maxlevelreached"));
				$vl['o_key'] = $ky;
				//Ausgabe des Erforschen-Links bzw. der Zeit, bzw. "erforscht"
				$vl['o_Ausnahme'] = in_array($ky, $ausnahmen_fuer_fosbonus);
				if ($forschungsq)	{
					foreach ($queued as $qvl)	{
						if ($qvl[name] == $ky)	{
							$case = 1;
							if ($qvl[position] > $qpos)
								$qpos = $qvl[position];
						}
					}
					
					$vl['o_nochForschable'] = ($valid[0] || ($valid[1] != "levelfull" and $valid[1] != "alreadybestlevel" and $valid[1] != 'maxlevelreached'));
					$vl['o_already_inAssi'] = $case;
				}
				array_push($temp_output[$vl['level']], $vl);
				unset($case, $temp, $qpos, $valid);
			}
		}
		if ($forschungsq)	{
			if ($build_science[name] && $sciencestats[$build_science[name]][group] == $tree)	{
				if ($sciencestats[$build_science[name]][level] < 7)	{
					$valid_forschungslevel[$sciencestats[$build_science[name]][level]+1] = 1;
					$highestAllQ[]=$sciencestats[$build_science[name]][level]+1;
				}
			}
			if ($queued)	{
				foreach ($queued as $vl)	{
					if ($sciencestats[$vl[name]][group] == $tree)	{
						$valid_forschungslevel[$sciencestats[$vl[name]][level]] = 1;
						$highestAllQ[]=$sciencestats[$vl[name]][level];
						if ($sciencestats[$vl[name]][level] < 7)	{
							$valid_forschungslevel[$sciencestats[$vl[name]][level]+1] = 1;
							$highestAllQ[]=$sciencestats[$vl[name]][level]+1;
						}
					}
				}
			}
		}

		if ($tree == "all"){ //all tree
			foreach($highestAllQ as $lvl)
				$valid_forschungslevel[$lvl]=1;
		}
	
		ksort($valid_forschungslevel);
		
		$buildtimebonus = 0;
		if ($status[race] == "sl") $buildtimebonus += 0.25;
		if ($sciences{glo15}) $buildtimebonus += 0.25;
		
		$sciences_tree_output = array();
		foreach ($valid_forschungslevel as $ky => $vl)	{
			$temp['o_stufe'] = $ky;
			$temp['o_duration'] = fos_duration($ky) * (1 - $buildtimebonus);
			$temp['o_NW'] = pointit(constant("NW_FOS_LVL".$ky));
			array_push($sciences_tree_output, $temp);
			unset($temp);
			foreach ($temp_output[$ky] as $fos) {
				array_push($sciences_tree_output, $fos);
			}
		}
		$tree_output = array('name' => $tree,
							 'sciencestats' => $sciences_tree_output);
		array_push($fos_output, $tree_output);
		unset($valid_forschungslevel);
	}
	$tpl->assign('FOS', $fos_output);
}
else {
	if ($globals[roundstatus] == 1 || $globals[roundstatus] == 2)	{
		$tpl->assign('SYND_FOS_SHOW', true);
		// Allianzen wurden deaktiviert
		$allies = row("select ally1,ally2 from syndikate where synd_id=".$status[rid]);
		if ($allies[0]):
			#$names = assocs("select synd_id,name from syndikate where synd_id in (".$allies[0].($allies[1] ? ",".$allies[1]:"").")", "synd_id");
			//$allianzkollegen = "Syndikatsforschungen Ihres Bündnispartners <a href=forschung.php?show=showsynd&synd_id=".$allies[0]." class=linkAufsiteBg>#".$allies[0]."</a> einsehen.<br><br>";
			$tpl->assign('ALLY1', $allies[0]); //ally synfos einsehen by dragon12 25.3.2012/R60
		endif;
		if ($allies[1]):
			//$allianzkollegen = "Syndikatsforschungen Ihrer Bündnispartner  <a href=forschung.php?show=showsynd&synd_id=".$allies[0]." class=linkAufsiteBg>#".$allies[0]."</a>, <a href=forschung.php?show=showsynd&synd_id=".$allies[1]." class=linkAufsiteBg>#".$allies[1]."</a> einsehen.<br><br>";
			$tpl->assign('ALLY2', $allies[1]);
		endif; 
		
		##
		## Aktieneinsicht
		##
		$aktiensynds = assocs("SELECT sum(number) as number, synd_id FROM aktien WHERE user_id = ".$status['id']." AND synd_id != ".$status['rid']." AND synd_id != ".$allies[0]." AND synd_id != ".$allies[1]." GROUP BY synd_id ORDER BY synd_id ASC");

		$temp_id = $synd_id; // Zum prüfen ob, man auch tatsächlich die Erlaubnis zur Einsicht hat
		if($allies[0] != $synd_id) $synd_id = "";
		if (count($aktiensynds) > 0)	{
			$aktiensynds_output = array();
			foreach ($aktiensynds as $temp) {
				list($anzahl,$prozent,$umlauf) = aktienbesitz($status['id'],$temp['synd_id']);
				
				if ($prozent >= AKTIEN_SYNDSCIENCEREADOPTION){
					if ($temp['synd_id'] == $temp_id){
						$synd_id = $temp_id;
					}
					$aktienpossibilities = true;
					array_push($aktiensynds_output, $temp);
				}
			}
			if ($aktienpossibilities){
				$tpl->assign('AKTIENPOSSIBILITIES', true);
				$tpl->assign('AKTIENSYNDS', $aktiensynds_output);
				
			}
		}

		$syndikat = assoc("select * from syndikate where synd_id=".($synd_id ? $synd_id:$status[rid]));
		$statuses = assocs("select id,syndicate from status where rid = ".($synd_id ? $synd_id:$status[rid])." order by syndicate asc");
		$idstring = "";
		foreach ($statuses as $value) {
			$idstring.= $value[id].",";
		}
		$idstring = chopp($idstring);
		$scienceses = assocs("select *,concat(user_id,name) as laber from usersciences where user_id in ($idstring)","laber");
		$developing = assocs("select * from build_sciences where user_id in ($idstring)", "user_id");

		/* // Ausgebaut Runde 20 - 10.03.2006
		{ // Syndikatsciences-Typ plus ï¿½derungsmï¿½lichkeit fr den Prï¿½identen
			$justownlevels = "Jeder Spieler profitiert nur von den Leveln, die er selbst erforscht hat";
			$alllevels = "Jeder Spieler profitiert von sï¿½tlichen erforschten Leveln";

			$modusappend = "";
			if ($id == $syndikat['president_id']) {
				$action = param('action', 'g');
				if ($action == "changemode") {
					$newmode = int(param('newmode', 'g'));
					if ($newmode != $syndikat['syndsciencestype']) {
						$syndikat['syndsciencestype'] = 1 - $syndikat['syndsciencestype'];
						$queries[] = "update syndikate set syndsciencestype = ".$syndikat['syndsciencestype']." where synd_id = ".$status['rid'];
						towncrier($status['rid'], "Der Prï¿½ident hat den Modus der Syndikatsforschungen geï¿½dert. Neuer Modus ist:<br>".($syndikat['syndsciencestype'] == 0 ? $justownlevels : $alllevels),0,2);
					}
				}
				$newmodechange = 1-$syndikat['syndsciencestype'];
				$modusappend = " &nbsp;&nbsp;&nbsp;<a href=forschung.php?show=showsynd&action=changemode&newmode=$newmodechange class=linkAufsiteBg>${yellowdot}ï¿½dern</a>";
			}
			$modus = ($syndikat['syndsciencestype'] == 0 ? $justownlevels : $alllevels).$modusappend;
		}
		//$ausgabe.="<br><br><center>$allianzkollegen$aktienpossibilities${aktstring}<b><u>Modus</u>: $modus</b><br><br><table border=\"0\" class=\"tableOutline\" cellspacing=\"1\" cellpadding=\"5\" width=\"600\" align=center>
		*/
		
		$sciences_output = array();
		// Forschungsnamen speichern
		$sciences_output['ind16_name'] = $sciencestats{'ind16'}{'gamename'};
		$sciences_output['ind15_name'] = $sciencestats{'ind15'}{'gamename'};
		$sciences_output['glo12_name'] = $sciencestats{'glo12'}{'gamename'};
		// Maximalerlevel berechnen
		$sciences_output['ind16_maxlevel'] = $sciencestats['ind16']['maxlevel'];
		$sciences_output['ind15_maxlevel'] = $sciencestats['ind15']['maxlevel'];
		$sciences_output['glo12_maxlevel'] = $sciencestats['glo12']['maxlevel'];
		
		if (!$synd_id) {
			$tpl->assign('MYSYN', true);
			$statuses_output = array();
			foreach ($statuses as $key => $value) {
				$value['ind16_lvl'] = $scienceses[$value['id'].'ind16']['level'];
				$value['ind15_lvl'] = $scienceses[$value['id'].'ind15']['level'];
				$value['glo12_lvl'] = $scienceses[$value['id'].'glo12']['level'];
				$value['o_develop'] = $developing[$value['id']]['name'];
				$value['o_develop_hours'] = (($developing[$value['id']]['time']-get_hour_time($time))/3600);
				array_push($statuses_output, $value);
			}
			$tpl->assign('STATUSES', $statuses_output);
		}
		
		// Levelaufteilung berechnen
		function sum_levels($string, $fostype) {
			global $sciences_output;
			$string = explode("|", $string);
			for ($i=0; $i<$sciences_output[$fostype.'_maxlevel']; $i++) {
				if (!$string[$i]) {$string[$i] = 0;}
				$sciences_output[$fostype.'_lvl'.($i+1)] = $string[$i];
			}
			
		}
		sum_levels($syndikat[energyforschung], 'ind16');
		sum_levels($syndikat[creditforschung], 'ind15');
		sum_levels($syndikat[sabotageforschung], 'glo12');

		// Effektiver Bonus
		if (!$synd_id) $synd_id = $status['rid'];
		$sciences_output['ind16_effektiv'] = get_synfos_count_extern($status,"ind16",$synd_id) * SYNFOS_ISESP_OTHER;
		$sciences_output['ind15_effektiv'] = get_synfos_count_extern($status,"ind15",$synd_id) * SYNFOS_TRADE_OTHER;
		$sciences_output['glo12_effektiv'] = get_synfos_count_extern($status,"glo12",$synd_id) * SYNFOS_ISSDN_OTHER;
		$tpl->assign('SCIENCES', $sciences_output);
	}
	else {
		$tpl->assign('ERROR', "Diese Seite steht erst nach Rundenstart zur Verfügung");
	}
}


//	Daten schreiben	//
db_write ($queries);


//**************************************************************************//
//	Header, Ausgabe, Footer
//**************************************************************************//

$tpl->assign('SHOW', $show);
$tpl->assign('STATUS', $status);
$tpl->assign('LAYOUT', $layout);
$tpl->assign('RIPF', $ripf);
$tpl->assign('FORSCHUNGSQ', $forschungsq);

require_once("../../inc/ingame/header.php");

if ($tpl->get_template_vars('MSG') != '') {
	$tpl->display('sys_msg.tpl');
}
if ($tpl->get_template_vars('ERROR') != '') {
	$tpl->display('fehler.tpl');
}
if ($tpl->get_template_vars('INFO') != '') {
	$tpl->display('info.tpl');
}

$tpl->display('forschung.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//	Dateispezifische Funktionen
//**************************************************************************//

function fehlermeldung ($i) {									//Die Fehlermeldungen
	global $error_ausgabe;
	$temp = "";
	switch ($i) {
		case "baumstruktur":	$temp = "Forschungsstufe zu hoch. Erforschen sie zuerst ein Projekt niedrigerer Stufe. ";	break;
		case "maxlevelreached":	$temp = "Sie haben bereits die höchste Stufe für diese Forschung erreicht. ";			break;
		case "sciencepoints":	$temp = "Nicht genügend Forschungspunkte für die gewünschte Forschung vorhanden! ";	break;
		case "alreadyforsching":	$temp = "Es wird bereits geforscht. Sie können nur an einem Projekt gleichzeitig forschen. ";	break;
		case "levelfull":	$temp = "Je Forschungsstufe können Sie maximal (Anzahl Forschungen)-(1) Forschungen erforschen. Eine Ausnahme bildet hier die Forschungsstufe 5.";	break;
		case "alreadybestlevel":	$temp = "Sie können nur eine Stufe 5 Forschung entwickeln. ";				break;
		case "notexisting":	$temp = "Diese Forschung existiert nicht. ";						break;

	}

	$error_ausgabe = $temp;

	return $temp;
}



function zeit ($forschung, $bonus = 0) {									//Zeit fr das Erforschen von $forschung
	global $sciences, $sciencestats, $globals, $time, $status;
	$modifikator = 1;

	$globals{roundstatus} == 1 ? $hourtime = get_hour_time($time) : $hourtime = $globals{roundstarttime};

	$modifikator = $status[race] == "sl" ? 0.75 : 1;
	if ($status[race] == "uic" && $sciencestats[$forschung][group] == "ind") {
		$modifikator = (100-UIC_INDUSTRIAL_SPEEDBONUS) / 100;
	}
	// Glo 15 Forschung beschleunig forschungsgeschwindigkeit um weitere 25%
	$modifikator = ($sciences{glo15}) ? $modifikator - 0.25 : $modifikator;
	$restdauer = ((fos_duration($sciencestats{$forschung}[level]) - $bonus) * $modifikator);
	// Falls restdauer keine gerade Stunde wird aufgerundet
	if ($restdauer != floor($restdauer)) $restdauer = floor($restdauer) + 1; 
	$temp = $hourtime + $restdauer * 60 * $globals{roundtime};

	return $temp;
}

function forschungssort ($a, $b) {
    if ($a["position"] == $b["position"]) return 0;
    return ($a["position"] < $b["position"]) ? -1 : 1;
}

?>
