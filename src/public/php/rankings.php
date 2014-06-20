<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($sortby == "nw" || $sortby == "land") {$sortby = $sortby;}
else {$sortby = "nw";}


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

$race = assocs("select name,tag,shortname,race from races where active = 1","race");

//if (!isset($type)) $type = $status[isnoob];
$type = 0;
$type = floor($type);
if ($type != '0' && $type != '1') $type = $status[isnoob];

$sortraces = array("allies", "rel","synd","any");
foreach ($race as $value) {
    array_push($sortraces,$value{race});
}
foreach ($sortraces as $value) {
    if ($value == $sort) {$sortrace = $sort;break;}
}
unset($sort);
if (!$sortrace && $type == $status[isnoob]) {$sortrace = "rel";}
elseif ((!$sortrace or $sortrace == "rel") && $type != $status[isnoob]) { $sortrace = "any"; }

if ($sortrace != "synd") {
	foreach ($race as $key => $value) {
        if ($sortrace == $value{race}) {$tpl->assign("sortraceShortname", $value{tag}); }
    }
}
//if ($sortrace == "allies") { $besten = "Die besten Allianzen:";}
//if (isBasicServer($game) && $sortrace == "allies") $sortrace = "";


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//							Berechnungen									//

// Falls nicht Syndikate gesucht werden:
if ($sortrace != "synd" && $sortrace != "allies") {
    $nwvalues = getrankings($sortrace,$sortby);
}

//							Daten schreiben									//

//							Ausgabe     									//

// Ab hier ausgabe START
	$raceIconArray=array(	"pbf" => "".$ripf."bf-logo-klein.gif",
							"sl" => "".$ripf."sl-logo-klein.gif",
							"uic" => "".$ripf."uic-logo-klein.gif",
							"nof" => "".$ripf."nof-logo-klein.gif",
							"neb" => "".$ripf."neb-logo-klein.gif");
    $myQueryString="sort=".$sortrace."&sortby=".$sortby."&type=$type";  // JOINER
    $tpl->assign("myQueryString", $myQueryString);
    if ($ranktype == "eroberungen" or $ranktype == "diebe") {
		$myQueryString = "";
    }
	//echo $myQueryString;
    // Das obere Menue ausgeben, nach was soll sortiert werden ?
	$links_z1=array(	"sort=allies&sortby=nw&type=$type" => "Allianzen", // deaktiviert - R4bbiT - 07.11.10 --- wieder aktiviert - dragon12 - 25.3.12 (R61)
						"sort=synd&sortby=nw&type=$type" => "Syndikate",
						"sort=any&sortby=nw&type=$type" => "Konzerne");  
	if (isBasicServer($game)) array_shift($links_z1); // Keine Allianzen für Basic Server
						

	if ($type == $status[isnoob]) $links_z1['sort=rel&sortby=nw&type='.$type] = "Relativ";

	foreach($race as $key => $value) {
		$links_z1["sort=".$value{race}."&sortby=nw&type=$type"]=$value{tag};  // JOINER
	}
	$links_z2=array(	"sort=allies&sortby=land&type=$type" => "Allianzen", // deaktiviert - R4bbiT - 07.11.10 -- wieder aktiviert - dragon12 25.3.12 (R61)
						"sort=synd&sortby=land&type=$type" => "Syndikate",
						"sort=any&sortby=land&type=$type" => "Konzerne");  // JOINER
	if (isBasicServer($game)) array_shift($links_z2); // Keine Allianzen für Basic Server

	if ($type == $status[isnoob]) $links_z2['sort=rel&sortby=land&type='.$type] = "Relativ";

	foreach($race as $key => $value) {
		$links_z2["sort=".$value{race}."&sortby=land&type=$type"]=$value{tag};  // JOINER
	}
	
	$hourtime = get_hour_time($time); # 10 Sekunden Sicherheit
	$hour = date("G",$time);
	$temptime = $time;
	while ($hour % 6 != 0) {
		$temptime -= 3600;
		$hourtime = get_hour_time($temptime); # 10 Sekunden Sicherheit
		$hour = date("G",$temptime);
			
	}
	$tpl->assign("hourtime", mytime($hourtime));
    /* <!--".($type == 0 ? "<b>Übrige</b>":"<a href=rankings.php?type=0&sort=$sortrace&sortby=$sortby class=linkAufsiteBg>
     * Übrige</a>")." <b>|</b> ".($type == 1 ? "<b>Anfänger</b>":"
     * <a href=rankings.php?type=1&sort=$sortrace&sortby=$sortby class=linkAufsiteBg>Anfänger</a>")."--></td></tr></table>
     */
	$links_z1_output = array();
    foreach($links_z1 as  $qst => $text) {  // JOINER
    	$temp['qst'] = $qst;
    	$temp['text'] = $text;
    	array_push($links_z1_output, $temp);
    	unset($temp);
	}
	$tpl->assign("links_z1", $links_z1_output);
	$links_z2_output = array();
    foreach($links_z2 as  $qst => $text) {  // JOINER
    	$temp['qst'] = $qst;
    	$temp['text'] = $text;
    	array_push($links_z2_output, $temp);
    	unset($temp);
	}
	$tpl->assign("links_z2", $links_z2_output);
    $tpl->assign("ranktype", $ranktype);
    $tpl->assign("sortrace", $sortrace);
	$tpl->assign("noBasicServer", !isBasicServer($game));
	$tpl->assign("besten", $besten);
	if ($specialization == 1) {
		$privilege_level = single("select privilege_level from users where konzernid = $id");
	} else $privilege_level = "";
	$tpl->assign("in_protection", in_protection($status));
	$tpl->assign("status", $status);

	if ($ranktype == "eroberungen") {
		$data = assocs("select konzernid, syndicate, race, rid, attack_total_won_normal+attack_total_won_conquer as won, attack_total_won_normal+attack_total_won_conquer - attack_total_loss_normal-attack_total_loss_conquer as netto from stats where round = ".$globals['round']." and alive > 0 order by netto desc limit 50", "konzernid");
		$count = 0;
		$involved_ids = array_keys($data);
		$anonymity = assocs("select id, ranking_anonymity from status where id in (".join(",", $involved_ids).")", "id");
		$attacklogs = assocs("select aid, did, landgain from attacklogs where time >= ".($time - 86400)." and (aid in (".join(",", $involved_ids).") or did in (".join(",", $involved_ids).")) and type in (1,3)");
		foreach ($attacklogs as $vl) {
			if ($data[$vl['aid']]) {
				$data[$vl['aid']]['won'] -= $vl['landgain'];
				$data[$vl['aid']]['netto'] -= $vl['landgain'];
			}
			if ($data[$vl['did']]) {
				$data[$vl['did']]['netto'] += $vl['landgain'];
			}
		}
		// Nochmal sortieren wegen der durch die Attacklogs geänderten Werte
		$forsort = array();
		foreach ($data as $vl) {
			$forsort[$vl['konzernid']] = $vl['netto'];
		}
		arsort($forsort);
		$forsort_output = array();
		foreach ($forsort as $key => $trash) {
			$vl = $data[$key];
			$count++;
			$vl['raceIcon'] = $raceIconArray[$vl[race]];
			$vl['raceShortname'] = $racenames[$vl[race]][shortname];
			$vl['o_ismentor'] = ismentor($vl[konzernid]); 
			if ($specialization == 1 && $privilege_level >= 2) {
				$emoname = "(".single("select username from users where konzernid = '".$vl['konzernid']."'").") ";
			} else $emoname = "";
			$vl['o_emoname'] = $emoname;
			$vl['o_anonymity'] = $anonymity[$vl['konzernid']]['ranking_anonymity'];
			$vl['o_count'] = $count;
			$vl['o_netto'] = pointit($vl['netto']);
			$vl['o_won'] = pointit($vl['won']);
			$vl['o_diff'] = pointit($vl['won']-$vl['netto']);
			array_push($forsort_output, $vl);
			unset($vl);
		}
		$tpl->assign("forsort", $forsort_output);

	} elseif ($ranktype == "aktionaere") {   ### Ranking Aktionäre Start
		$ranking = assocs("SELECT a.user_id, sum( b.aktienkurs * a.number ) AS gesamtwert FROM aktien a, syndikate b WHERE a.synd_id = b.synd_id GROUP BY a.user_id ORDER BY gesamtwert DESC LIMIT 50;");
		$count=0;
		$ranking_output = array();
		foreach ($ranking as $rank) {
			$count++;
			$vl = assoc("select konzernid, syndicate, race, rid, nettostolen from stats where round = ".$globals['round']." and alive > 0 and konzernid = ".$rank['user_id']);
			$vl['raceIcon'] = $raceIconArray[$vl[race]];
			$vl['raceShortname'] = $racenames[$vl[race]][shortname];
			$vl['o_emoname'] = $emoname;
            $vl['o_count'] = $count;
            $vl['o_gesamtwert'] = pointit($rank['gesamtwert']);
            array_push($ranking_output, $vl);
            unset($vl);
		}
		$tpl->assign("ranking", $ranking_output);
			
	}
	elseif ($ranktype == "diebe") {
		$data = assocs("select konzernid, syndicate, race, rid, nettostolen from stats where round = ".$globals['round']." and alive > 0 order by nettostolen desc limit 50", "konzernid");
		$count = 0;
		$involved_ids = array_keys($data);
		$anonymity = assocs("select id, ranking_anonymity from status where id in (".join(",", $involved_ids).")", "id");
		$data_ouput = array();
		foreach ($data as $vl) {
					if ($vl{syndicate} == $status{syndicate}) {$a ="class=\"tableInner2\"";} else {$a = "class =\"tableInner1\"";}
					$count++;
					unset ($temprace);
			$vl['raceIcon'] = $raceIconArray[$vl[race]];
			$vl['raceShortname'] = $racenames[$vl[race]][shortname];
			$vl['o_ismentor'] = ismentor($vl[konzernid]);
			if ($specialization == 1 && $privilege_level >= 2) {
				$emoname = "(".single("select username from users where konzernid = '".$vl['konzernid']."'").") ";
			} else $emoname = "";
			$vl['o_emoname'] = $emoname;
			$vl['o_anonymity'] = $anonymity[$vl['konzernid']]['ranking_anonymity'];
			$vl['o_count'] = $count;
			$vl['o_nettostolen'] = pointit($vl['nettostolen']); 
			array_push($data_ouput, $vl);
		}
		$tpl->assign('data', $data_ouput);
	} else {
		if ($sortrace != "synd" && $sortrace != "allies") {
			$count = 0;
			// Für relative rankings nummerierung feststellen
			if ($sortrace == "rel") {
				$sortby == "nw" ? $sortby = "nw_rankings" : 1;
				$sortby == "land" ? $sortby = "land_rankings" : 1;
				$action ="select count(*) from status where $sortby > ".$status{$sortby}." and alive > 0 and isnoob=".$type." order by $sortby asc limit 50";
				$result = select($action);
				$count = mysql_fetch_row($result);$count=$count[0];
				if ($count < 50) {$count = 0;}
				else {$count = $count - 50;}
			}
			$nwvalues_output = array();
			$vl = array();
			foreach ($nwvalues as $key => $value) {
				//if ($nwvalues{$key}{name} == $status{syndicate}) {$nwvalues{$key}{name} = "<font class=\"gelb11\">".$nwvalues{$key}{name}."</font>";}
				$vl['o_name'] = $nwvalues{$key}{name};
				$count++;
				// if ($count % 2 == 1) { $bgcolor_table = "#718AB3";} else { $bgcolor_table = "#819AC3";}
				//$temprace = $race{$nwvalues{$key}{race}}{shortname};
				$vl['race'] = $nwvalues[$key][race];
				$vl['raceIcon'] = $raceIconArray[$nwvalues[$key][race]];
				$vl['raceShortname'] = $racenames[$nwvalues[$key][race]][shortname];
				if ($specialization == 1 && $privilege_level >= 2) {
					$emoname = single("select username from users where konzernid = '".$nwvalues{$key}{id}."'");
				} else $emoname = "";
				$vl['o_emoname'] = $emoname;
				$vl['o_ismentor'] = ismentor($nwvalues{$key}{id});
				if($nwvalues{$key}{land} > 0){
					$fox = $nwvalues{$key}{networth} / $nwvalues{$key}{land};
					$fox = number_format($fox, 1, ",", ".");
				}
				else{
					$fox = "n/a";
				}
				$vl['o_fox'] = $fox;
				$vl['o_count'] = $count;
				$vl['id'] = $nwvalues{$key}{id};
				$vl['rid'] = $nwvalues{$key}{rid};
				$vl['o_nw'] = pointit($nwvalues{$key}{networth});
				$vl['o_land'] = pointit($nwvalues{$key}{land});
				array_push($nwvalues_output, $vl);
				unset($vl);
			} // foreach $nwvalues
			$tpl->assign('nwvalues', $nwvalues_output);
		} // Wenn sortrace nicht synd
		elseif ($sortrace == "synd") {
			$realmvalues = assocs("SELECT nw_ranking AS nw, land_ranking AS land, synd_id AS rid, name FROM syndikate WHERE ".($type == 0 ? "synd_type='normal'":"synd_type != 'normal'")." ORDER BY `".$sortby . "` DESC LIMIT 100","rid");
				
				/* OLD $actionhandle = select("" . 				
						"	SELECT " .
						"		sum(nw_rankings)," .
						"		sum(land_rankings)," .
						"		`rid`," .
						"		syndikate.name " .
						"	FROM " .
						"		status," .
						"		syndikate " .
						"	where " .
						"		syndikate.synd_id = status.rid " .
						"		and alive > 0 " .
						"		and ".($type == 0 ? "synd_type='normal'":"synd_type != 'normal'")." " .
						"	group by " .
						"		rid ORDER BY `sum($sortbytemporder)` DESC LIMIT 100");
				*/		
				// Alte quickfix nw berechnung
				/*	while ($returnstatus =mysql_fetch_row($actionhandle)) 
					{
						$nw10 = assoc("" .
								"SELECT " .
								"	sum( nw_10 ) " .
								"FROM " .
								"	(" .
								"		SELECT " .
								"			nw_rankings AS nw_10 " .
								" 			land_ranking AS land_10 " .
								"		FROM " .
								"			`status` " .
								"		WHERE " .
								"			rid = ".$returnstatus[2]." " .
										"	AND alive > 0 " .
										"ORDER BY " .
										"	nw_rankings " .
										"DESC " .
										"LIMIT 10 " .
									") AS x");
						$realmvalues[$i] = array ('nw'=>$nw10['nw_10'],'land'=>$nw_10['land_10'],'rid' => $returnstatus[2],'realmname' => $returnstatus[3]);									 
						$i++;
					}
				*/
			$count=0;
			$sortArray = array();
			foreach($realmvalues as $key => $array) {
				$sortArray[$key] = $array[$sortby];
			}
			array_multisort($sortArray, SORT_DESC, SORT_NUMERIC, $realmvalues);
			$realmvalues_output = array();
			$vl = array();
			foreach ($realmvalues as $key => $value) {
				if ($realmvalues{$key}{nw} > 0) {
					$count++;
					$vl['o_count'] = $count;
					$vl['rid'] = $realmvalues{$key}{rid};
					$vl['o_name'] = $realmvalues{$key}{name};
					$vl['nw'] = $realmvalues{$key}{nw};
					$vl['o_nw'] = pointit($realmvalues{$key}{nw});
					$vl['o_land'] = pointit($realmvalues{$key}{land});
					array_push($realmvalues_output, $vl);
					unset($vl);
				}
			}
			$tpl->assign('realmvalues', $realmvalues_output);
			
		} # if sort eq synd
		elseif ($sortrace == "allies") // deaktiviert - R4bbiT - 07.11.10 - wieder aktiviert - dragon12 - 25.3.12 (R61)
		{
			$i=0;
			$allies = assocs("select allianz_id, first, second, third, name from allianzen", "allianz_id");
			foreach ($allies as $vl) 
			{
				if ($vl[first]): $allies_candidates[$vl[first]] = $vl[first]; endif;
				if ($vl[second]): $allies_candidates[$vl[second]] = $vl[second]; endif;
				if ($vl[third]): $allies_candidates[$vl[third]] = $vl[third]; endif;
			}
			if ($allies_candidates) 
			{
				$kuendigungen = assocs("select synd_id from allianzen_kuendigungen", "synd_id");
				$syndtable_allydata = assocs("select synd_id, name, ally1, ally2 from syndikate where synd_id in (".join(",", $allies_candidates).")", "synd_id");
				/*foreach ($allies as $ky => $vl)    ---- dragon12 R61, allys können nicht mehr gekündigt werden
				{
					if ($kuendigungen[$vl[first]]): $allies[$ky][first] = 0; unset($allies_candidates[$vl[first]]); endif;
					if ($kuendigungen[$vl[second]]): $allies[$ky][second] = 0; unset($allies_candidates[$vl[second]]); endif;
					if ($kuendigungen[$vl[third]]): $allies[$ky][third] = 0; unset($allies_candidates[$vl[third]]); endif;
				}*/
				foreach ($allies as $ky => $vl) 
				{
					$members = 0;
					if ($vl[first]): if($syndtable_allydata[$vl[first]]): $members++; else: unset($allies[$ky][first]); endif; endif;
					if ($vl[second]): if($syndtable_allydata[$vl[second]]): $members++; else: unset($allies[$ky][second]);endif; endif;
					if ($vl[third]): if($syndtable_allydata[$vl[third]]): $members++; else: unset($allies[$ky][third]);endif; endif;
					if ($members <= 1): unset($allies[$ky]); endif;
				}
	
				$realmvalues = assocs("" .
						"	SELECT " .
						"		nw_ranking as nw," .
						"		land_ranking as land," .
						"		synd_id AS rid," .
						"		name " .
						"	FROM " .
						"		syndikate " .
						"	where " .
						($type == 0 ? "synd_type='normal'":"synd_type != 'normal'")." ".
						"	ORDER BY `$sortby` DESC", "rid");
				
				
				foreach ($allies as $ky => $vl) 
				{
					$allyvalues[$ky] = array ( "nw" => ($realmvalues[$vl[first]][nw] + $realmvalues[$vl[second]][nw] + $realmvalues[$vl[third]][nw]), "land" =>  $realmvalues[$vl[first]][land] + $realmvalues[$vl[second]][land] + $realmvalues[$vl[third]][land], "name" => $vl[name], 'isally' => true);
					$members = 0;
					if ($vl[first]): $members++; $allyvalues[$ky][$members] = $vl[first]; endif;
					if ($vl[second]): $members++; $allyvalues[$ky][$members] = $vl[second]; endif;
					if ($vl[third]): $members++; $allyvalues[$ky][$members] = $vl[third]; endif;
					$allyvalues[$ky][mitglieder] = $members;
					$allyvalues[$ky][nw] /= $members;
					$allyvalues[$ky][land] /= $members;
				}
				$noally_syns = assocs("" .
						"	SELECT " .
						"		nw_ranking as nw," .
						"		land_ranking as land," .
						"       synd_id as rid,".
						"		name " .
						"	FROM " .
						"		syndikate " .
						"	where " .
						"		synd_id not in (".join(",", $allies_candidates).") " .
						"and ".($type == 0 ? "synd_type='normal'":"synd_type != 'normal'")." " .
						"	ORDER BY `$sortby` DESC", "rid");
				
				foreach($noally_syns as $key => $vl) {
					$allyvalues[] = array('nw' => $vl['nw'], 'land' => $vl['land'], 'name' => $vl['name'], 1 => $vl['rid']);
				}
	
				foreach ($allyvalues as $ky => $vl) 
				{
					$tempsafe[$ky] = $vl[$sortby];
				}
				arsort($tempsafe);
				foreach ($tempsafe as $ky => $vl) 
				{
					$allyvalues2[$ky] = $allyvalues[$ky];
				}
				$allyvalues = $allyvalues2;
	
	
				/*$ausgabe.= "
				<table width=\"550\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
				<table width=\"550\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\">
				<tr class=\"tableHead\" height=23>
					<td width=30 align=center>Rang</td>
					<td width=\"200\" align=center>Allianz</td>
					<td align=right>Networth</td>
					<td align=right>Land</td>
				</tr>
				";*/
				$count=1;
				$allies_tpl = array();
				foreach ($allyvalues as $key => $value) {
				if ($count < 50) {
					if ($allyvalues{$key}{nw} >= 0) {
						//if ($count % 2 == 1) {$bgcolor_table = "#718AB3";} else { $bgcolor_table = "#819AC3";}
						if ($allyvalues{$key}{1} == $status{rid} or $allyvalues{$key}{2} == $status{rid} or $allyvalues{$key}{3} == $status{rid}) {$a ="class=\"tableHead\"";} else {$a = "class =\"tableHead2\"";}
						$allies_tpl[$count] = array('class' => $a, 'rowspan' => 1+$value[mitglieder], 'name' => $allyvalues[$key][name], 'nw' => pointit($allyvalues{$key}{nw}), 'land' => pointit($allyvalues{$key}{land}), 'isally' => $value['isally']);
							/*$ausgabe.="
							<tr $a>
								<td align=center rowspan=".(1+$value[mitglieder])."><b>$count</b></td>
								<td align=\"left\">&nbsp;&nbsp;<b>".$allyvalues[$key][name]."</b></td>
								<td align=\"right\"><b>".pointit($allyvalues{$key}{nw})."</b>&nbsp;</td>
								<td align=\"right\"><b>".pointit($allyvalues{$key}{land})."</b>&nbsp;</td>
							</tr>";*/
							for ($i = 1; $i <= $value[mitglieder]-1; $i++) {
								for ($o = 1; $o <= $value[mitglieder]-1; $o++) {
									if ($realmvalues[$value[$o]][$sortby] < $realmvalues[$value[$o+1]][$sortby]): $temp = $value[$o]; $value[$o] = $value[$o+1]; $value[$o+1] = $temp; endif;
								}
							}
							if(!$value['isally'])
								$value[mitglieder] = 1;
							$allies_members_tpl = array();
							for ($i = 1; $i <= $value[mitglieder]; $i++) {
								$allies_members_tpl[$i] = array('rid' => $value[$i], 'name' => $realmvalues[$value[$i]][name], 'nw' => pointit($realmvalues[$value[$i]][nw]), 'land' => pointit($realmvalues[$value[$i]][land]), 'class' => ($status['rid'] == $value[$i])?'tableInner2':'tableInner1');
								//$ausgabe .= "<tr class=tableInner2><td align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"linkAuftableInner\" href=\"syndicate.php?rid=".$value[$i]."\">".$realmvalues[$value[$i]][name]." (#".$value[$i].")</a></td><td align=\"right\">".pointit($realmvalues[$value[$i]][nw])."&nbsp;</td><td align=\"right\">".pointit($realmvalues[$value[$i]][land])."&nbsp;</td></tr>";
							}	
							$allies_tpl[$count]['members'] = $allies_members_tpl;
							$allies_tpl[$count]['members_count'] = count($allies_members_tpl);
							$count += $value[mitglieder];
						}
					}
				}
				$ausgabe.="</table></td></tr></table></center>";
				$tpl->assign('ALLYDATA', $allies_tpl);
			}
			else 
			{ 
				$ausgabe .= "<br>Es wurden noch keine Allianzen geschlossen"; 
			}
		}
	}

// Ausgabe ENDE

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
$tpl->display('rankings.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#=====================GETRANKINGS==============================================
#<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

function getrankings($sortrace,$what) {
    $values = array ();
    $where = $sortrace;
    $sortby = $what;
	global $type;
    $i=0;
    global $status;

	$sortbytemporder = $sortby;
	$sortbytemporder == "nw" ? $sortbytemporder = "nw_rankings" : 1;
	$sortbytemporder == "land" ? $sortbytemporder = "land_rankings" : 1;

    if ($where != "allies" && $where != "synd" && $where != "any" && $where != "rel") {
        $action ="select syndicate as name,nw_rankings as nw,land_rankings,race,rid,id  from status where race = '$where' and alive > 0 and isnoob=$type order by $sortbytemporder desc limit 100";
        $result = select($action);
         while ($returnstatus = mysql_fetch_row($result)) {
                $values[$i] = array ('name' => $returnstatus[0],'networth' => $returnstatus[1],'land' => $returnstatus[2],'race' => $returnstatus[3], 'rid' => $returnstatus[4], 'id' => $returnstatus[5]);
            	$i++; 
          } # while returnstatus
    } # if $where
    
    elseif ($where == "any") {
        $action ="select syndicate as name,nw_rankings as nw,land_rankings,race,rid,id from status where alive > 0 and isnoob=$type order by $sortbytemporder desc limit 100";
        $result = select($action);
         while ($returnstatus = mysql_fetch_row($result)) {
            $values[$i] = array ('name'=>$returnstatus[0],'networth'=>$returnstatus[1],'land' => $returnstatus[2],'race'=> $returnstatus[3], 'rid' => $returnstatus[4], 'id' => $returnstatus[5]);
        	$i++; 
          } # while returnstatus
      } # if $where
    
    elseif ($where == "rel" && $type == $status[isnoob]) {
        if ($sortby == "land") {$sortby1 = $status{land_rankings};$sortby="land_rankings";}
        elseif ($sortby == "nw") {$sortby="nw_rankings";$sortby1 = $status{nw_rankings};}
        $action ="select syndicate as name,nw_rankings as nw,land_rankings,race,rid,id from status where $sortby > $sortby1  and alive > 0 and isnoob=$type order by $sortbytemporder asc limit 50";
        $result = select($action);
        while ($returnstatus = mysql_fetch_row($result)) {
            array_unshift ($values,array ('name'=>$returnstatus[0],'networth'=>$returnstatus[1],'land' => $returnstatus[2],'race'=> $returnstatus[3], 'rid' => $returnstatus[4], 'id' => $returnstatus[5]));
        	$i++; 
          } # while returnstatus
        
        $action ="select syndicate as name,nw_rankings as nw,land_rankings,race,rid,id from status where $sortby <= $sortby1 and alive > 0 and isnoob=$type order by $sortbytemporder desc limit 50";
        $result = select($action);
        while ($returnstatus = mysql_fetch_row($result)) {
            array_push ($values,array('name'=>$returnstatus[0],'networth'=>$returnstatus[1],'land' => $returnstatus[2],'race'=> $returnstatus[3], 'rid' => $returnstatus[4], 'id' => $returnstatus[5]));
        	$i++; 
          } # while returnstatus
      } # if $where
    return $values;
}
?>
