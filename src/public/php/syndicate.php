<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$rid = floor($rid);
$detailsid = floor($detailsid);
if ($action and $action != "details"){
	$action = "";
}

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

require_once("../../inc/ingame/header.php");

// GP-variable $ripf assignen
$tpl->assign("GP_PATH", $ripf);

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

if (!$rid){
	$rid = $status["rid"];
}

$searchtime = $time - 24 * 60 * 60;
$plus = get_next_rid($rid);
$minus = get_last_rid($rid);

$gl1 = "angriff.php?";
$gl2 = "spies.php?inneraction=prepare";
$gl3 = "mitteilungen.php?action=psm";
$gl4 = "pod.php";
$gl5 = "buddy.php?submit=Spieler+ausw%E4hlen";

$wherestring = "";
$wherestring2 = "";

$totalnetworth = array();
$totalland = array();

$detailsuserid = "";
$startround = "";
$kategorie = "";
$description = "";
$showdetails = "";
$showround = "";

$isatwar = inwar($status["rid"], single("select id from status where rid=$rid and alive>0 limit 1")); // isatwar($status["rid"], $rid);
$isprotection = in_protection($status);
$maximum_attacks_reached = 0;
$alliance_id = single("select allianz_id from syndikate where synd_id=".$status["rid"]);

$naps = assocs("select nappartner, type from naps_spieler where user_id=".$id." and type > 0", "nappartner");


$ids = array();
$sessidsactual = array();
$aktien = array();
$attackssuffered = array();
$racenames = array();
$honors = array();
$details = array();
$queries = array();

$syndata = array();
$konzern = array();
$uebersicht = array();


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$tpl->assign("RID", $rid);
$tpl->assign("IMAGES", $layout["images"]);
$tpl->assign("BASIC", isBasicServer($game));
$tpl->assign("WWWDATA", WWWDATA);
$tpl->assign("SBILD_PREFIX", SBILD_PREFIX);
$tpl->assign("KBILD_PREFIX", KBILD_PREFIX);
$tpl->assign("AKTIEN_PIC_SYNVIEW", AKTIEN_PIC_SYNVIEW);
$tpl->assign("MENTOR_PIC", MENTOR_PIC);

if (!$action)	{	# Wenn keine Action übergeben wurde!

	$orderby_array_to_sort = array( "race", "syndicate", "land", "nw", "fox" );
	$orderby_array_to_number = array("race" => "0", "syndicate" => "1",  "land" => "2", "nw" => "3", "fox" => 4);
	$ordertype_array_to_sort = array( "asc", "desc" );
	$ordertype_array_to_number = array ( "asc" => "0", "desc" => "1" );
	
	if ($orderby && isset($orderby_array_to_number[$orderby])) { $changed = 1; $status["sue_sort"][1] = $orderby_array_to_number[$orderby]; }
	if ($ordertype && isset($ordertype_array_to_number[$ordertype])) { $changed = 1; $status["sue_sort"][2] = $ordertype_array_to_number[$ordertype]; }
	$orderby = $orderby_array_to_sort[$status["sue_sort"][1]];
	$ordertype = $ordertype_array_to_sort[$status["sue_sort"][2]];
	
	if ($changed) {
		$queries[] = "update status set sue_sort = '".$status["sue_sort"]."' where id = ".$id;
	}
	
	function get_order($orderby) {
		global $status, $orderby_array_to_sort, $orderby_array_to_number, $ordertype_array_to_sort, $ordertype_array_to_number, $rid, $layout;
		$out = array();
		if ($status["sue_sort"][1] == $orderby_array_to_number[$orderby]) {
			$out["ordertype"] = ($status["sue_sort"][2] == "0") ? "1" : "0";
			$out["img"] = ($status["sue_sort"][2] == "0") ? "ASC" : "DESC";
		}
		else { 
			$out["ordertype"] = 1;
		}
		$out["ordertype"] = $ordertype_array_to_sort[$out["ordertype"]];
		$out["orderby"] = $orderby;
		return $out;
	
	}
	if ($change[0] == 0 or $change[0] == 1 or $change[0] == 2) {
		if ($change[1] == 1 or $change[1] == 2) {
			$status["sue_mode"][$change[0]] = $change[1];
			$queries[] = "update status set sue_mode = '".$status["sue_mode"]."' where id = ".$status["id"];
		}
	}
	$modus = $status["sue_mode"];
	
	$tpl->assign("SORT_RACE", get_order("race"));
	$tpl->assign("SORT_KONZERN", get_order("syndicate"));
	$tpl->assign("SORT_LAND", get_order("land"));
	$tpl->assign("SORT_NW", get_order("nw"));
	$tpl->assign("SORT_FOX", get_order("fox"));
	$tpl->assign("PLUS", get_next_rid($rid));
	$tpl->assign("MINUS", get_last_rid($rid));
	
	$tpl->assign("MODUS", $modus);


	//							selects fahren
	$racenames = assocs("select race, shortname from races", "race");
	#list ($synd_id, $name, $pres_id, $img, $allianz_id, $ally1, $ally2, $website) = row("select synd_id, name, president_id, image, allianz_id, ally1, ally2, syndikatswebseite from syndikate where synd_id='$rid';");
	$syn[] = assoc("select synd_id, synd_type, name, president_id, image,artefakt_id, allianz_id, ally1, ally2, syndikatswebseite, description from syndikate where synd_id='".$rid."';");
	if ($modus[0] == 2) {
		if ($syn[0]["ally1"]) { $syn[] = assoc("select synd_id, name, president_id, image,artefakt_id, allianz_id, ally1, ally2, syndikatswebseite, description, synd_type from syndikate where synd_id='".$syn[0]["ally1"]."';"); }
		if ($syn[0]["ally2"]) { $syn[] = assoc("select synd_id, name, president_id, image,artefakt_id, allianz_id, ally1, ally2, syndikatswebseite, description, synd_type from syndikate where synd_id='".$syn[0]["ally2"]."';"); }
	}	
	
	if ($syn[0]["synd_id"] == $rid){ 		## Falls das Syndikat existiert
		
		$tpl->assign("SYN", true);
		
		foreach ($syn as $ky => $vl) {
			$syndata[$vl["synd_id"]] = $vl;
			$syndata[$vl["synd_id"]]["allianz_name"] = single("select name from allianzen where allianz_id='".$vl["allianz_id"]."'");
			$syndata[$vl["synd_id"]]["artefakt_name"] = $artefakte[$vl["artefakt_id"]]["name"];
			$syndata[$vl["synd_id"]]["description"] = umwandeln_bbcode($vl["description"]);
			$rids[] = $vl["synd_id"];
		}
		
		
		$ids = rows("select id from status where rid in (".join(",", $rids).")"); ## Konzern-IDs
		if (count($ids)){
			$wherestring = "where user_id in (";
			$wherestring2 = "where time > ".$searchtime." and winner='a' and did in (";
		}

		for ($i = 0; $i < count($ids); $i++)	{
			if ($i+1 < count($ids)){
				$wherestring .= "'".$ids[$i][0]."',";
				$wherestring2 .= "'".$ids[$i][0]."',";
			}
			else if($i+1 == count($ids)){
				$wherestring .= "'".$ids[$i][0]."')";
				$wherestring2 .= "'".$ids[$i][0]."')";
			}
		}

		if ($wherestring){
			$sessidsactual = assocs("select user_id, gueltig_bis from sessionids_actual ".$wherestring, "user_id");
			$aktien = assocs("select user_id, number from aktien ".$wherestring." and synd_id=".$status["rid"], "user_id");
			$totalaktien = single("select sum(number) from aktien where synd_id=".$status["rid"]);
			$napdata = singles("select nappartner from naps_spieler where user_id=".$id." and nappartner in (select id from status where rid=".$rid.") ");
			//$attackssuffered = assocs("select did, count(*) as n from attacklogs ".$wherestring2." and gbprot=1 group by did;", "did");
		}
		
		$nwToSelect = "nw_last_hour";
		if ($rid == $status['rid'] || $rid == $syn[0]["ally1"] || $rid == $syn[0]["ally2"]){
			$nwToSelect = "nw";
		}
		
		## Auslesen der Konzerndaten
		$values = assocs("select status.id,status.land,status.race,status.syndicate,status.lastlogintime,status.createtime,status.unprotecttime,status.alive,status.".$nwToSelect." as nw,status.rid,status.gvi,users.is_mentor as mentor,status.inprotection,(status.".$nwToSelect." / status.land) as fox from status,users where status.rid in (".join(",", $rids).") and status.id=users.konzernid order by ".$orderby." ".$ordertype); // JOINER: Order by Syndikat-Name
		
		// Erstellen der Konzerne für die Synübersicht
		for ($i = 0; $i < count($values); $i++){
			unset($konzern);
			$konzern = $values[$i];
			
			$totalnetworth[$konzern["rid"]] += $konzern["nw"];
			$totalland[$konzern["rid"]] += $konzern["land"];

			list($anzahl, $prozent, $umlauf) = aktienbesitz($konzern["id"], $status['rid']);
			$isattackable = isattackable($values[$i]["rid"], $values[$i]["alive"], $values[$i]["lastlogintime"], $values[$i]["land"], $values[$i]["createtime"], $prozent, $isatwar, $isprotection, $maximum_attacks_reached, ($values[$i]["rid"] == $syn[0]["synd_id"] ? $syn[0]["allianz_id"] : ($values[$i]["rid"] == $syn[1]["synd_id"] ? $syn[1]["allianz_id"] : $syn[2]["allianz_id"])), $alliance_id, $naps, $values[$i]["id"], 3,$values[$i]["nw"],$values[$i]["gvi"], $game_syndikat["synd_type"], ($values[$i]["rid"] == $syn[0]["synd_id"] ? $syn[0]["synd_type"] : ($values[$i]["rid"] == $syn[1]["synd_id"] ? $syn[1]["synd_type"] : $syn[2]["synd_type"])),$values[$i]["inprotection"],$values[$i]['unprotecttime']);
			
			$konzern["raceicon"] = ($konzern["race"] == "pbf") ? "bf" : $konzern["race"];
			$konzern["racename"] = $racenames[$konzern["race"]]["shortname"];
			$konzern["land"] = $konzern["land"];
			$konzern["nw"] = $konzern["nw"];
			if($isattackable == 1 or $isattackable == 4 or $isattackable == 3){ $konzern["attackable"] = true; }
			if($isattackable == 1 or $isattackable == 4 or $isattackable == 2){ $konzern["spieable"] = true; }
			if($values[$i]["rid"] == $status["rid"]){ $konzern["own_syn"] = true; }
			
			foreach ($syndata as $vl) {
				if ($values[$i]["id"] == $vl["president_id"]){	// Wenn der Konzern Präsident ist
					$konzern["president"] = true;
				}
			}
			$konzern["color"] = "normal";
			
			if (in_array($values[$i]["id"],$napdata)) {
				$konzern["nap"] = true;
			}
			
			if (isBuddy($values[$i]["id"])) {
				$konzern["buddy"] = true;
			}
			
			if($values[$i]["mentor"] && is_mentorprogram() == 2) {
				$konzern["mentor"] = true;
			}
			else{
				$konzern["mentor"] = false;
			}
			
			if(is_mentorprogram($values[$i]["id"]) == 1){
				$konzern["is_newb"] = true;
			}
			
			if ($time < $sessidsactual[$values[$i]["id"]]["gueltig_bis"]){
				$konzern["status"] = "online";
			}
			else{
				$konzern["status"] = "offline";
			}
			
			if($prozent >= AKTIEN_PIC_SYNVIEW){
				$konzern["aktieninhaber"] = true;
				if($values[$i]['rid'] == $status['rid']){
					$konzern["aktienprozent"] = $prozent;
				}
			}
			
			list($dummy,$aSuffered) = get_bash_protection($values[$i]["id"]);
			if($aSuffered == 1){
				$konzern["color"] = "attacked";
			}
			else if($aSuffered > 1){
				$konzern["color"] = "heavyattacked";
			}
			
			if ($values[$i]["alive"] == 2){
				$konzern["color"] = "holiday";
			}
			else{
				if($values[$i]["lastlogintime"] + TIME_TILL_GLOBAL_INACTIVE < $time){
					$konzern["status"] = "gl_inaktiv";
				}
				else if($values[$i]["lastlogintime"] + TIME_TILL_INACTIVE < $time){
					$konzern["status"] = "lokal_inaktiv";
				}
				
				if(in_protection($konzern)){
					$konzern["color"] = "protected";
				}
			}
			
			$konzern["schnitt_color"] = "normal";
			if($values[$i]["fox"]){
			   $fox = $values[$i]["fox"];
			   if($id == $values[$i]['id']){
					//7.8.10 neuen avg fox berechnung by Chritian
					//land größer
					$aktionA = "SELECT avg(nw/land) FROM status WHERE land >=".$values[$i]["land"]." AND race LIKE '".$values[$i]["race"]."' AND alive > 0 ORDER BY land ASC LIMIT 20";
					//land kleiner
					$aktionB = "SELECT avg(nw/land) FROM status WHERE land <".$values[$i]["land"]." AND race LIKE '".$values[$i]["race"]."' AND alive > 0 ORDER BY land DESC LIMIT 20";
					$schnitt = (single($aktionA)+single($aktionB))/2;
					//old nub schnitt
					//$schnitt = single("select avg(nw / land) from status where race = '".$values[$i]["race"]."'");
					//end
				   $schnitt_8 = $schnitt * 0.8;
				   
				   if($fox >= $schnitt_8 && $fox < $schnitt){
					   $konzern["schnitt_color"] = "gelb";
				   }
				   else if($fox < $schnitt_8){
					   $konzern["schnitt_color"] = "rot";
				   }
				   $konzern["schnitt"] = $schnitt;
			   }
			   $konzern["fox"] = $fox;
			}
			
			$konzern["url_angriff"] = $gl1."rid=".$values[$i]["rid"]."&target=".$values[$i]["id"];
			$konzern["url_spies"] = $gl2."&rid=".$values[$i]["rid"]."&target=".$values[$i]["id"];
			$konzern["url_msg"] = $gl3."&rec=".$values[$i]["id"];
			$konzern["url_lager"] = $gl4."?pre_id=".$values[$i]["id"];

			if ($id == $values[$i]["id"]) { // JOINER
				$konzern["own"] = true;
			}

			if ($modus[2] == 2) {
				$konzerndata[] = $konzern;
			}
			else {
				$konzerndata[$values[$i]["rid"]][] = $konzern;
			}
		}
		
		$i = 0;
		
		foreach($rids as $tag => $val){
			if($modus[2] != 2){ $i++; }
			if(!$uebersicht[$i]){
				$uebersicht[$i] = $syndata[$val];
			}
			$uebersicht[$i]["syndata"][$val] = $syndata[$val];
			if($modus[2] == 2){
				$uebersicht[$i]["konzerndata"] = $konzerndata;
				$uebersicht[$i]["totalland"] = array_sum($totalland);
				$uebersicht[$i]["totalnetworth"] = array_sum($totalnetworth);
			}
			else{
				$uebersicht[$i]["konzerndata"] = $konzerndata[$val];
				$uebersicht[$i]["totalland"] = $totalland[$val];
				$uebersicht[$i]["totalnetworth"] = $totalnetworth[$val];
			}
			if($uebersicht[$i]["totalnetworth"] > 0){
				$uebersicht[$i]["totalfox"] = $uebersicht[$i]["totalnetworth"] / $uebersicht[$i]["totalland"];
			}
			$uebersicht[$i]["cronimon"] = getJsHelpTagCustom("NW-Verlauf","_aktien_halter.gif","onClick=\"window.open('croniwidget.php?type=syn_nw&title=".urlencode("NW-Verlauf Syndikat #".$rid)."&identifier=".$rid."', 'NW_Verlauf_Syndikat_".$rid."', 'width=520 , height=390 ,scrollbars=no')\" style=\"cursor:pointer;\"");
			
			$wardata = assocs("select war_id, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3 from ". WARTABLE ." where status = 1 and (first_synd_1=".$val." or first_synd_2=".$val." or first_synd_3=".$val." or second_synd_1=".$val." or second_synd_2=".$val." or second_synd_3=".$val.")");
			foreach ($wardata as $vl){
				if ($vl["first_synd_1"] == $val or $vl["first_synd_2"] == $val or $vl["first_synd_3"] == $val){
					$var = "second";
				}
				else{
					$var = "first";
				}
				$number = 0;
				for ($a = 1; $a <= 3; $a++) {
					if ($vl[$var."_synd_".$a]){
						$number++;
						$uebersicht[$i]["wardata"][$vl["war_id"]]["syns"][] = $vl[$var."_synd_".$a];
					}
				}
				if ($number >= 2) {
					$uebersicht[$i]["wardata"][$vl["war_id"]]["name"] = single("select name from allianzen where allianz_id = ".single("select allianz_id from syndikate where synd_id = ".$uebersicht[$i]["wardata"][$vl["war_id"]]["syns"][0]));
					$uebersicht[$i]["wardata"][$vl["war_id"]]["text"] = "ally";
				}
				else {
					$uebersicht[$i]["wardata"][$vl["war_id"]]["name"] = single("select name from syndikate where synd_id = ".$uebersicht[$i]["wardata"][$vl["war_id"]]["syns"][0]);
					$uebersicht[$i]["wardata"][$vl["war_id"]]["text"] = "syn";
				}
			}
		}
		
		$tpl->assign("UEBERSICHTDATA", $uebersicht);
		
	}
	else{
		$tpl->assign("ERROR", "Ein Syndikat mit dieser Nummer existiert nicht.<br />Bitte wählen Sie eine andere Syndikatsnummer!");
		$tpl->display("fehler.tpl");
	}## Falls das Syndikat existiert
	
	$tpl->display("syndicate.tpl");
	

} # Wenn keine Action übergeben wurde ENDE
else if ($action == "details")	{

	list ($detailsuserid, $startround, $createtime) = row("select users.id,users.startround, status.createtime from status, users where users.konzernid=status.id and status.id=".$detailsid);
	list ($kategorie, $description, $showdetails) = row("select kategorie, description, showdetails from settings where id = ".$detailsid);
	
	if ($detailsuserid) {
		$nc = assoc("select * from options_namechange where round=".$globals["round"]." and konzernid=".$detailsid." and time > ".($createtime + PROTECTIONTIME));

		$tpl->assign("OLD_NAMES", $nc["old_syndicate"]);
		
		$description = preg_replace("/\n\r?\f?/", "<br />", $description);
	
		if ($showdetails){
			$honors = assocs("select round,honorcode,rank from honors where user_id = '".$detailsuserid."' order by round");
		}
		
		$details = getallvalues($detailsid);	
		
		$tpl->assign("RACEICON", (($details["race"] == "pbf") ? "bf" : $details["race"]));
		$tpl->assign("DETAILS", $details);
		$tpl->assign("BRANCHE", ((!$kategorie) ? "Keine Angaben" : $kategorie));
		$tpl->assign("DESCRIPTION", ((!$description) ? "Keine Angaben" : umwandeln_bbcode($description)));
		$tpl->assign("SHOWDETAILS", $showdetails);
		$tpl->assign("STARTROUND", $startround-2);
		$tpl->assign("HONORS", $honors);
		
		if($rid == $status["rid"]){ $konzern["own_syn"] = true; }
			
		$konzern["url_angriff"] = $gl1."rid=".$rid."&target=".$detailsid;
		$konzern["url_spies"] = $gl2."&rid=".$rid."&target=".$detailsid;
		$konzern["url_msg"] = $gl3."&rec=".$detailsid;
		$konzern["url_lager"] = $gl4."?pre_id=".$detailsid;
		$konzern["url_buddy"] = $gl5."&secret=1&id=".$detailsid;

		$konzern['buddy'] = isBuddy($detailsid);
		if ($id == $detailsid) $konzern["own"] = true;
		
		$tpl->assign('KONZERN', $konzern);
		
		
		$tpl->display("syndicate_details.tpl");
	}
	else{
		$tpl->assign("ERROR", "Konzern wurde nicht gefunden!");
		$tpl->assign("SHOW_LINK", 1);
		$tpl->assign("BACK_LINK", "href=\"syndicate.php?rid=".$rid."\"");
		$tpl->display("fehler.tpl");
	}
}



db_write($queries,1);

//**************************************************************************//
//							        Footer		        					//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


?>