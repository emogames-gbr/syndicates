<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//


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


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


$name = preg_replace("/_/", " ", $name);

if ($type == "buildings")	{
	$informations = assoc("select description,verbrauch,intverbrauch,building_id from buildings where name='$name'");
    $tpl->assign("informations", $informations);
}

/* Wird nicht verwendet daher vorerst übersprungen (inok, Sept 2011)
if ($type == "military")	{
	$informations = assoc("select description,op,dp,specials from military_unit_settings where name='$name'");
    $information= "<b>Angriff: </b>".$informations{op}."<br><b>Verteidigung: </b>".$informations{dp}."<br><b>Specials: </b>".$informations{specials}."<br><br>".$informations{description};
}

if ($type == "spies")	{
	$informations = assoc("select description,ip,op,dp from spy_settings where name='$name'");
    $information = "<b>Sabotagestärke: </b>".$informations{op}."<br><strong>Aufklärung: </strong>".$informations{ip}."<br><b>Verteidigungsstärke: </b>".$informations{dp}."<br><br>".$informations{description};
}

if ($type == "sciences")	{
	$information = single("select description from sciences where name='$name'");
} 

// Actions
if ($action == "fullmenue") {
	if ($status[noob_wholemenu]) {
		$status[noob_wholemenu] = 0;
		select ("update status set noob_wholemenu=0 where id=$status[id]");
	}
	else {
		$status[noob_wholemenu] = 1;
		select ("update status set noob_wholemenu=1 where id=$status[id]");
	}
	
}
// Actions ENDE

if ($type == "menu") {
	$name="Komplettes Menu anzeigen";
	if ($status[noob_wholemenu]) {$tvalue = "Eingeschränktes Menu anzeigen";}
	else {$tvalue = "Komplettes Menu anzeigen";}
	$information = "
	Um Einsteiger langsam an Syndicates heranzuführen und nicht mit zahlreichen Optionen zu erschlagen, werden für Anfänger in der Schutzphase einige Menupunkte ausgeblendet.<br>
	Sie können das komplette Menu jederzeit hier aktivieren.
	<br><br>
	<form action=description.php method=\"post\">
	<input type=\"hidden\" name=\"type\" value=\"menu\">
	<input type=\"hidden\" name=\"action\" value=\"fullmenue\">
	<input type=\"submit\" value=\"$tvalue\">
	</form>
	";
}

 if ($type == "monumente" && $name == "werbaut") {
 	$name = "Welches Syndikat baut welches Monument ?";
 	$syns_bauen = assocs("select ba.*,s.name from build_artefakte as ba,syndikate as s where ba.synd_id=s.synd_id");
 	foreach ($artefakte as $temp) {
		$fadd = 0;
 		$inadd="<br><b>$temp[name]</b><br>";
 		foreach ($syns_bauen as $ti) {
 			if ($ti[artefakt_id] == $temp[artefakt_id]) {
 				$inadd.="<li> $ti[name] (#".$ti[synd_id].")<br>";
 				$fadd = 1;
 			}
 		}
 		if ($fadd) {
 			$information.=$inadd;
 		}
 	}
 	$information.="<br>";
 }
 
if ($type == "anfsyn") {
	$name = "Anfängersyndikate";
	$information = "
	Neue Spieler werden bei Syndicates in Anfängersyndikaten untergebracht. Spieler aus Anfängersyndikaten können nicht von Spielern aus normalen Syndikaten
	angegriffen werden oder diese angreifen (gilt auch für Spionegaeaktionen). Es sind ferner keine Allianzen zwischen normalen Syndikaten und Anfängersyndikaten möglich.
	Anfängersyndikate sollen neuen Spielern die Möglichkeit geben, die Welt von Syndicates kennenzulernen ohne gleich mit erfahrenen Spielern in Konkurrenz zu  geraten.
	Spieler, die in einem Anfängersyndikat spielen können jederzeit unter dem Menupunkt 'Optionen' in ein normales Syndikat wechseln.
	";
} 
*/

if ($type == "fraktionen") { // Synübersicht, wenn man auf die Logos klickt
	$desc = single("select description_html from fraktionen_beschreibung where race ='$name'");
	$race = assoc("select * from races where race = '$name'");
	$units = assocs("select * from military_unit_settings where race = '$name' or race='all'");
	$spyunits = assocs("select * from spy_settings where race='$name' or race='all'");
	
	if (isKsyndicates()) { // K-Syndicates Fraktionslogos
		$desc = preg_replace("/images/","images/krawall_images",$desc);
	}
	
	$name = $race[name];
	$tpl->assign("desc", $desc);
	$tpl->assign("units", $units);
	$tpl->assign("spyunits", $spyunits);
}

if ($type == "market_gebote") { // Auf GM-Seite
	$name="Kaufgebote am Weltmarkt";
	// Text in description.tpl
}

if ($type == "monumente") { // Auf Monu-Seite
	$tpl->assign("monu_check", $name);
	if ($name == "beteiligung") {
		$tpl->assign("BUCHUNGSBETRAG_TICK", pointit(BUCHUNGSBETRAG_TICK));
		$name = "Beteiligung am Bau von Monumenten";	
	}	
}

if ($type == "hilfe") {
	$informations = assoc("select name, text from hilfen where flag = '$name'");
	$name = $informations[name];
	$tpl->assign("hilfe_text",$informations[text]);
}

//							selects fahren									//

//							Berechnungen									//

//							Daten schreiben									//

//							Ausgabe     									//


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign("type", $type);
$tpl->assign("name", $name);
$tpl->assign("layout", $layout);

$tpl->display("description.tpl");	

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

?>
