<?
//**************************************************************************
// Testconfig, R43, o19 - nur Testversion                                               
//**************************************************************************
require_once("../../inc/ingame/game.php");

if ($game[name] == "Syndicates Testumgebung"){
//**************************************************************************
// Verarbeitung der Eingaben, DB_write
//**************************************************************************

$units = getunitstats($status['race']); //assocs("select name, type from military_unit_settings where race='".$status['race']."'");
$spys = getspystats($status['race']); //assocs("select name, type from spy_settings where race='".$status['race']."'");
$buildings = assocs("select name_intern, name from buildings where race like '%".$status['race']."%' or race='all'");	

if($action == 'reconfig'){
	$sciences = assocs("select gamename,treename,sciences.id as sid,maxlevel,typenumber,usersciences.level as ulevel,concat(sciences.name,typenumber) as treenum from sciences left join usersciences on(concat(sciences.name,typenumber) = usersciences.name and usersciences.user_id = '".$status['id']."') where sciences.available = 1 order by sciences.level");
	$partnerboni = assocs("select bonus,id,level from partnerschaften_general_settings left join partnerschaften on(id = pid and partnerschaften.user_id = '".$status['id']."')");
	
	$queries = array();
	
	//Update Ressourcen
	if(is_numeric($money)) $queries[] = "update status set money = ".$money." where id = '".$status['id']."'";
	if(is_numeric($metal)) $queries[] = "update status set metal = ".$metal." where id = '".$status['id']."'";
	if(is_numeric($energy)) $queries[] = "update status set energy = ".$energy." where id = '".$status['id']."'";
	if(is_numeric($sciencepoints)) $queries[] = "update status set sciencepoints = ".$sciencepoints." where id = '".$status['id']."'";

	//Update Militär
	foreach($units as $unit) if(is_numeric($$unit['type'])) $queries[] = "update status set ".$unit['type']." = ".$$unit['type']." where id = '".$status['id']."'";

	//Update Spies
	if($ops) $queries[] = "update status set spyactions = ".$ops." where id = '".$status['id']."'";
	foreach($spys as $spy) if(is_numeric($$spy['type'])) $queries[] = "update status set ".$spy['type']." = ".$$spy['type']." where id = '".$status['id']."'";
	
	//Update Land
	if(is_numeric($land)) $queries[] = "update status set land = ".$land." where id = '".$status['id']."'";
	
	//Update Sonstiges
	if(is_numeric($later_started_bonus)) $queries[] = "update status set later_started_bonus = ".$later_started_bonus." where id = '".$status['id']."'";
	
	//Update Gebäude
	foreach($buildings as $building) if(is_numeric($$building['name_intern'])) $queries[] = "update status set ".$building['name_intern']." = ".$$building['name_intern']." where id = '".$status['id']."'";
	
	//Update Forschungen
	$queries[] = "delete from usersciences where user_id ='".$status['id']."'";
	foreach($sciences as $science) if(is_numeric($$science['treenum']) && $$science['treenum'] != 0) $queries[] = "insert into usersciences (user_id, name, level) values ('".$status['id']."', '".$science['treenum']."', '".$$science['treenum']."')";
	
	//Update PBs
	$queries[] = "delete from partnerschaften where user_id ='".$status['id']."'";
	if($chkbx) foreach($chkbx as $k => $v) if(is_numeric($k) && $k >= 0) $queries[] = "insert into partnerschaften (user_id, pid, level) values ('".$status['id']."', '".$v."', '1')";
	$queries[] = "update status set partnerschaften = '".count($chkbx)."'";
	
	//Konfig und Tutorial beenden, Späteinsteigerbonus löschen, Schutzzeit beenden
	if($time <= ($status['createtime'] + 6*60*60)) $queries[] = "update status set createtime = '".($status['createtime']-60*60*6)."'";
	$queries[] = "update status set inprotection = 'N'";
	
	db_write($queries);
	s("Update durchgeführt.");
	$status = getallvalues($status['id']);
}

//**************************************************************************
// Ausgabe - Tabellen vorbereiten
//**************************************************************************

// Wirklich die aktuellen werte nehmen, deshalb nochmal auslesen!!!
$sciences = assocs("select gamename,treename,sciences.id as sid,maxlevel,typenumber,usersciences.level as ulevel,concat(sciences.name,typenumber) as treenum from sciences left join usersciences on(concat(sciences.name,typenumber) = usersciences.name and usersciences.user_id = '".$status['id']."') where sciences.available = 1 order by sciences.level");
$partnerboni = assocs("select bonus,id,level from partnerschaften_general_settings left join partnerschaften on(id = pid and partnerschaften.user_id = '".$status['id']."')");


// Vorbereitung der Militärübersicht                 
$ausgabeUnits = "<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Militär-Einheiten</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>";

foreach($units as $unit){
		$ausgabeUnits.= "<tr class=\"tableInner1\"><td>&nbsp;&nbsp;".$unit[name]."</td><td align=\"center\">
		<input id=\"".$unit['type']."\" type=\"text\" style=\"width: 5em;\" name= \"".$unit['type']."\" value=\"".$status[$unit['type']]."\"/></td></tr>";
}

$ausgabeUnits.= "</table></td></tr></table>";

// Vorbereitung der Spionageeinheiten       
$ausgabeSpys = "<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Spionage-Einheiten</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>";

foreach($spys as $spy){
		$ausgabeSpys.= "<tr class=\"tableInner1\"><td>&nbsp;&nbsp;".$spy[name]."</td><td align=\"center\">
		<input id=\"".$spy['type']."\" type=\"text\" style=\"width: 5em;\" name= \"".$spy['type']."\" value=\"".$status[$spy['type']]."\"/></td></tr>";
}

$ausgabeSpys.= "<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Spionageaktionen</td><td align=\"center\">
		<input id=\"ops\" type=\"text\" style=\"width: 5em;\" name= \"ops\" value=\"".$status['spyactions']."\"/></td></tr>";

$ausgabeSpys.= "</table></td></tr></table>";

// Vorbereitung der Sonstige Einstellungen      
$ausgabeSonstiges = "<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Sonstige Optionen</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>";
$ausgabeSonstiges.= "<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Forschungsbonus</td><td align=\"center\">
		<input id=\"later_started_bonus\" type=\"text\" style=\"width: 5em;\" name= \"later_started_bonus\" value=\"".$status['later_started_bonus']."\"/></td></tr>";

$ausgabeSonstiges.= "</table></td></tr></table>";

// Vorbereitung der Gebäudeübersicht                              
$ausgabeBuildings .="<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Land</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>
		
		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Land gesamt</td><td align=\"center\">
		<input id= \"land\"type=\"text\" style=\"width: 5em;\" name= \"land\" value=\"".$status['land']."\" /></td></tr>
		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Land unbebaut</td><td align=\"center\">
		<input id= \"uland\"type=\"text\" style=\"width: 5em;\" name= \"uland\" value=\"0\" disabled/></td></tr>
		</table></td></tr></table><br>
		
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Gebäude</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>";
foreach($buildings as $building){	
		$ausgabeBuildings .="<tr class=\"tableInner1\"><td>&nbsp;&nbsp;".$building['name']."</td><td align=\"center\">
		<input id= \"".$building['name_intern']."\"type=\"text\" style=\"width: 5em;\" name= \"".$building['name_intern']."\" value=\"".$status[$building['name_intern']]."\" /></td></tr>";
}
$ausgabeBuildings .="</table></td></tr></table>";

// Vorbereitung der Forschungsübersicht                              
$ausgabeMilitarySciences ="<br>
		<table width=\"180\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"180\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Military</b></td><td align=\"center\" width=\"80\">Stufe</td></tr>";
$ausgabeIndustrialSciences ="<br>
		<table width=\"180\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"180\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Industrial</b></td><td align=\"center\" width=\"80\">Stufe</td></tr>";
$ausgabeIntelligenceSciences ="<br>
		<table width=\"180\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"180\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Intelligence</b></td><td align=\"center\" width=\"80\">Stufe</td></tr>";	
$ausgabeCommonSciences ="<br>
		<table width=\"180\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"180\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Common</b></td><td align=\"center\" width=\"80\">Stufe</td></tr>";


foreach($sciences as $science){
	$temp = "<tr class=\"tableInner1\"><td>&nbsp;&nbsp;".substr($science['gamename'],0,17)."</td><td align=\"center\">
		<select id=\"".$science['treenum']."\"type=\"text\" style=\"width: 3em;\" name=\"".$science['treenum']."\">";
	for($i = 0; $i <= $science['maxlevel']; $i++){
		$selected = ($i == $science['ulevel'])?" selected":"";
		$temp .= "<option".$selected.">".$i."</option>";
	}	
	$temp .= "</td></tr>";
	
	if($science['treename'] == "mil"){$ausgabeMilitarySciences .= $temp;}
	elseif($science['treename'] == "glo"){$ausgabeIntelligenceSciences .= $temp;}
	elseif($science['treename'] == "ind"){$ausgabeIndustrialSciences .= $temp;}
	elseif($science['treename'] == "all"){$ausgabeCommonSciences .= $temp;}
}		
		
$ausgabeMilitarySciences .= "</table></td></tr></table>";
$ausgabeIndustrialSciences .="</table></td></tr></table>";
$ausgabeIntelligenceSciences .="</table></td></tr></table>";
$ausgabeCommonSciences .="</table></td></tr></table>";

// Vorbereitung der Partnerboni                             
$ausgabePB ="<br>
		<table width=\"540\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"540\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"460\"><b>&nbsp;&nbsp;Partnerschaften</b></td><td align=\"center\" width=\"80\">Auswahl</td></tr>";

foreach($partnerboni as $bonus){		
	$selected = ($bonus['level'] == 1)?" checked=\"checked\"":"";
	
	$ausgabePB .="<tr class=\"tableInner1\"><td>&nbsp;&nbsp;".$bonus['bonus']."</td><td align=\"center\">
	<input id=\"".$bonus['id']."\" type=\"checkbox\" name= \"chkbx[]\" value=\"".$bonus['id']."\"".$selected."/></td></tr>";		
}		
$ausgabePB .="</table></td></tr></table>";	

//Vorbereitung der Ressourcen
$ausgabeRessourcen ="<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Resourcen</b></td><td align=\"center\" width=\"80\">Anzahl</td></tr>

		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Credits</td><td align=\"center\">
		<input id=\"money\" type=\"text\" style=\"width: 5em;\" name= \"money\" value=\"".$status['money']."\"/></td></tr>

		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Energie</td><td align=\"center\">
		<input id= \"energy\"type=\"text\" style=\"width: 5em;\" name= \"energy\" value=\"".$status['energy']."\" /></td></tr>

		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Erz</td><td align=\"center\">
		<input id= \"metal\"type=\"text\" style=\"width: 5em;\" name= \"metal\" value=\"".$status['metal']."\"/></td></tr>

		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Forschungspunkte</td><td align=\"center\">
		<input id= \"sciencepoints\"type=\"text\" style=\"width: 5em;\" name= \"sciencepoints\" value=\"".$status['sciencepoints']."\"/></td></tr>

	</table></td></tr></table>";

//Vorbereitung Syndikatsdaten	
/*$ausgabeSyn ="<br>
		<table width=\"270\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tableOutline\"><tr><td>
		<table  border=\"0\" cellspacing=\"1\" width=\"270\" cellpadding=\"2\"><tr class=\"tableHead\"><td width=\"170\"><b>&nbsp;&nbsp;Syndikatsdaten</b></td><td align=\"center\" width=\"80\">Auswahl</td></tr>
		
		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Monument</td><td align=\"center\">
		<input id= \"monument\"type=\"text\" style=\"width: 5em;\" name= \"monument\" value=\"0\" /></td></tr>
		
		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Syn-Ranger</td><td align=\"center\">
		<input id= \"syndefspecs\"type=\"text\" style=\"width: 5em;\" name= \"syndefspecs\" value=\"0\"/></td></tr>
		
		<tr class=\"tableInner1\"><td>&nbsp;&nbsp;Syn-Marines</td><td align=\"center\">
		<input id= \"synoffspecs\"type=\"text\" style=\"width: 5em;\" name= \"synoffspecs\" value=\"0\"/></td></tr>
	</table></td></tr></table>"; */

//**************************************************************************
// Ausgabe - Tabelle setzen                       
//**************************************************************************

$ausgabe = "<form action=\"testconfig.php?action=reconfig\" method=\"POST\">";

$ausgabe .= "<table width=550 style=\"border:1px solid\" class=i cellpadding=2><tr><td>";
$ausgabe .= "Auf dieser Testseite können einige Werte für den eigenen Konzern angepasst werden, die für das testen"; 
$ausgabe .= " neuer Features u.U. nützlich sein können. Außerdem wird durch Konfiguration die Schutzzeit beendet";
$ausgabe .= ". Allerdings werden die eingebenen Werte dabei aktuell noch nicht auf Sinnhaftigkeit überprüft, es ";
$ausgabe .= "ist, z.B. zu viele Partnerboni oder alle Stufe 5 Forschungen zu wählen. Dies wird zukünfitg noch ";
$ausgabe .= "ergänzt werden, bis dahin bliebt jeder Tester für die Sinnhaftigkeit seiner Werte verantwortlich.";
$ausgabe .= "</td></tr></table>";

$ausgabe .= "<table><tr valign=\"top\"><td width=\"270\">";
$ausgabe .= $ausgabeRessourcen;	
$ausgabe .= $ausgabeUnits;
$ausgabe .= $ausgabeSpys;
$ausgabe .= $ausgabeCommonSciences;

$ausgabe .= "</td><td width=\"270\">";
$ausgabe .= $ausgabeBuildings;
$ausgabe .= "</td></tr></table>";
$ausgabe .= "<table><tr valign=\"top\"><td width=\"180\">";
$ausgabe .= $ausgabeMilitarySciences;
$ausgabe .= "</td><td width=\"180\">";
$ausgabe .= $ausgabeIndustrialSciences;
$ausgabe .= "</td><td width=\"180\">";
$ausgabe .= $ausgabeIntelligenceSciences;
$ausgabe .= "</td></tr></table>";
$ausgabe .= "<table><tr valign=\"top\"><td width=\"540\">";
$ausgabe .= $ausgabeSonstiges;
$ausgabe .= $ausgabePB;
$ausgabe .= "</td></tr><tr><td align=\"right\" valign=top>
			<input class=\"button\" type=\"submit\" value=\"Übernehmen\" /></td></tr></table>";
$ausgabe .= "</form>";

//**************************************************************************
// Header, Ausgabe, Footer
//**************************************************************************
require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");
}
 
else f("Diese Seite steht auf diesem Server leider nicht zur Verfügung.");
?>

