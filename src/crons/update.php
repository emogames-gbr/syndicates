<?
set_time_limit(0);
//$manualtime=1075323600;

/*
Ttile: Syndicates Update Script
Author: Jannis & Nicolas Breitwieser
Date: 18.07.03
All rights reserved
Description: Updating all players every hour, doing some other work as well
Requires: Subs.php, Syndicates Mysql Database
*/


/*
Remarks: Skript ist noch nich fertig!
         Als n?chstes:
         - Sciences und transfers verrechnen in dem Teil wo der Spieler aktualisiert wird
         (Transfers die mindestens eine Stunde unterwegs sind, sollen zur?ckgeschickt werden, an die beteiligten
         Spieler sollen jeweils die daf?r vorgesehen Nachrichten verschickt werden (sieh message settings))
         Sciences funktionieren noch nciht richtig, ?berleg dir was.
         - B?rsenkurs un Nw m?ssen noch berechnet werden.
            Nw dabei am ende der Spieleraktualisierung, B?rsenkurs NACH der Spieleraktualisierung
            Der Aktuelle B?rsenkurs wird in Syndikate eingetragen UND in dem entsprechenden B?rsenkurs safe table
            dieser erm?glicht sp?ter eine wesentlich schnellere berechnung des b?rsenkurses (wegen wachstum der letzten 24h)
            Eintrag in den nw_safe table nicht vergesen.
         -?bersch?ssiges Milit?r und Energie m?ssen vernichtet werden (vor Nw Berechnung)
         - Irgendwo nachschauen, ob alle Konzerne korrekt sind, d.h. ob zu jedem konzern der alive ist ein User existiert.
         - Heaptable leeren und Hitstats / Clicks aktualisieren (einmal am tag)
         - Verschiedene Roundstati (0,3) m?ssen noch explizit behandelt werden.
         - sold spalte im status einmal am tag auf 0 setzen (jeder spieler darf nur noch f?r 10mille t?glich aktien verkaufen auf dem global market)
         - Units bei kein Strom killen wegen ?berschuss bei depots und so nicht vergesen
*/

//***************************************************************//
//                      Global Requirements                      //
//***************************************************************//

require_once("../includes.php");
$handle = connectdb();

if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
	
require(INC."ingame/globalvars.php");
require_once (LIB."/mod_profiler.php");

echo "da\n";

$time = time();
$start = getmicrotime();
	
	
$page = "UPDATEPROZESS";
if ($manualtime2): $time -= floor($manualtime); endif;
if ($manualtime): $time = $manualtime; endif;
$hourtime = get_hour_time($time+10); # 10 Sekunden Sicherheit
// Zufallsgenerator initialisieren
list($usec,$sec) = explode(" ",microtime());
$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;
mt_srand($bla);
$queries = array();
$querystring = "";
$dr = 0;
$drausgabe="";
$date = date("j.m.y - H:i",$time);
$daydate = date("d.m.Y",$time);
$hour = date("G",$time);
$messagedeletetime = $time - 60*60*24*2.5; // Alle nachrichten ?lter als 2,5 tage werden gel?scht
$heaptimes = 1; # Legt fest zu welchen Stunden der Heap-Table bearbeitet wird (Modulo, alle X Stunden)
$dividendentimes = 3; # Alle 3 Stunden werden dividenden ausgezahlt // @TODO
$updated_users = 0;
$users_deleted = 0;
$syndupdates = array();
$syndstring = array();
$syndicate_nw_land_ranking = array();
$players_paid = array(); // Speichert, welche spieler pro zug den betrag fr das zu bauende artefakt bezahlt haben
// $players_paid[synid][spielerid]
$players_paid_msgtargets = array();

// Ressourcenstandardpreise, wenn ?nderung hier, dann auch gleich im ressources table ?ndern

// Ressourcenwerte jetzt definiert in globalvars
$sciencepointsprice = SCIENCEPOINTS_STD_VALUE;
$metalprice = METAL_STD_VALUE;
$energyprice = ENERGY_STD_VALUE;
$moneyprice = CREDIT_STD_VALUE;
## DEFINES

//define(MAXSPYOPS, 15); //ist jetzt in globalvars beides
//define(GLO6BONUS, 10); // Stretch Time
//define(IND14WERT,400000); // ind 14 forschung gibt 800k credits bei fertigstellung.
$ind14werte = array(
	1 => 400000,
	2 => 1200000,
	3 => 3200000
);


// globals Array, beinhaltet generelle info zur laufenden Runde
$globals = assoc("select * from globals order by round desc limit 1");

$count = single('SELECT count(id) FROM status WHERE createtime <= '.$globals['roundstarttime'].' AND alive > 0');
if($game[name] == "Syndicates Testumgebung"){
	$count = single('SELECT count(id) FROM status');
}
if(!$count){
	$count = 1;
}
define(AKTIENPACKET_SYNS, AKTIEN_BASIS_SPIELER / $count); // 150k Aktien werden durch die Anzahl der Spieler zu Beginn der Runde geteilt
define(AKTIENPACKET_OWN_PLUS, 8000);


/*
PRODUKTION Fï¿½ WAFFENLAGER REGELN
Veraltetete Regelung, jetzt werde Waffenlager abhï¿½gig von der vergangenen Spielzeit aufgeladen
*/

$weeks_played = ceil((round_days_played()+1)/ 7);

define(ARMORYPROD,$weeks_played*ARMORYPROD_PER_WEEK); // Armories produzieren 10 Angriffspunkte pro stunde
define(ARMORYSAFE,$weeks_played*ARMORYSAFE_PER_WEEK); // Armories k?nnen 500 Angtriffspunkte speichern


define(DELETE_K_FEATURES_AT_ROUND_END,1);
define (RESBACKTIME,20); // Alle 20 Stunden werden transfers zur?ckgebucht
define (PATRIOTWACHSTUM,0.40); // Patriots wachsen mit 0,40% pro stunde
define (PATRIOTTIME,20); // Patriots werden auf 20 stunden in bautable geschrieben
define (PATRIOTS_OVERCHARGE,1.2); // Patriots wachsen bis zum 1,2 fachen in den overcharge rein.
define (DIVIDENDENMINAKTIEN,AKTIEN_DIVIDENDEN); // Dividenden werden erst ab min 1& Aktien ausgegeben



//
//  Omnimon values - syndikate_nw, syndikate_stock
//
$omnimon_names_syndikate_nw = array();
$omnimon_values_syndikate_nw = array();

// Zeit zu der das Skript das letzte mal lief
$lastruntime = single("select time from updates order by time desc limit 1");
// Pages wegen heaptablesauswertung holen
$pagestats = assocs("select * from pages","id");
$game = assoc("select * from game");

//$tables = singles("show tables"); # f?r optimize nachher

### KEINE TABLE LOCKS MEHR

/*
//
// Tablelocks
//
 
$lockstring = "lock tables ";
foreach ($tables as $value) {
    if ($value != "heaptable" or $hour % $heaptimes == 0) {# && $value != "sessionids_actual" && $value != "sessionids_safe") { # k?nnte probleme geben wegen user l?schen - da muss auf diese beiden tables schreibend zugegriffen werden, geht aber glaub ich net wenn man sie nicht gelockt hat.
        $lockstring .="$value write,";
    }
}
$lockstring = chopp($lockstring);
select ($lockstring);
*/



// Updating auf 1 setzen
select("update globals set updating = 1 where round = ".$globals{round});

//
// Roundstart und Roundendtime pr?fen, entsprechend Roundstatus setzen
//

if ($time >= $globals{roundstarttime} && $globals{roundstatus} != 1 && $time < $globals{roundendtime}) {
	# sonst wird bei rundenstart bereits res vergeben
    #$globals{roundstatus}  = 1;
    

}
elseif ($time >= $globals{roundendtime} && $globals{roundstatus} != 2) {
	# sonst wird bei rundenende nix mehr fertig gebaut
    #$globals{roundstatus}  = 2;
    $queries[] =("update globals set roundstatus = 2 where round = ".$globals{round});
}


//***************************************************************//
//                      Eigentliche Skriptausf?hrung             //
//***************************************************************//
//
// Wenn Zeit zwischen letztem Update und Aufrufzeit mindestens 9/10 Der Rundenzeit betr?gt, skript ausf?hren
//


// Zeit, die zwischen 2 updates mindestens verstreichen muss
$toleranzzeit = round($globals{roundtime}*60*0.9);
if ($time - $lastruntime >= $toleranzzeit or $game[name] == "Syndicates Testumgebung") { // >= normal, != zum testen

echo "through\n";

	if ($globals[roundstatus] == 0 or $globals[roundstatus] == 1)	{
		if ($hour % $heaptimes == 0)	{
			//$heapdata = assocs("select user_id, clicktime, seite from heaptable where clicktime < $hourtime");
			if ($heapdata)	{
				$ausgabe .= "HEAPDATA VORHANDEN";
				foreach ($heapdata as $vl)	{

				$tday = date("j", $vl[clicktime]);
				$tmonth = date("n", $vl[clicktime]);
				$tyear = date("Y", $vl[clicktime]);
				$thour = date("G", $vl[clicktime]);

					$heap[$tyear][$tmonth][$tday][$thour][$vl[seite]]++;
					$user_heap_click_stats[$vl[user_id]]++;
				}

				ksort($heap);
				foreach ($heap as $ky => $vl)	{	# F?r jedes Jahr
					ksort($vl);
					foreach ($vl as $ky2 => $vl2)	{	# F?r jeden Monat
						ksort($vl2);
						foreach ($vl2 as $ky3 => $vl3)	{	# F?r jeden Tag
							ksort($vl3);
							foreach ($vl3 as $ky4 => $vl4)	{	# F?r jede Stunde
								$hitstats_insertstring_1 = "";
								$hitstats_insertstring_2 = "";
								foreach ($pagestats as $vl5)	{
									if (!$vl4[$vl5[id]]): $vl4[$vl5[id]] = 0; endif;
									$hitstats_insertstring_1 .= $vl5[dateiname].",";
									$hitstats_insertstring_2 .= $vl4[$vl5[id]].",";
								}
								$hitstats_insertstring_1 .= "tag,monat,jahr,stunde";
								$hitstats_insertstring_2 .= "$ky3,$ky2,$ky,$ky4";

								$queries[] = ("insert into hitstats ($hitstats_insertstring_1) values ($hitstats_insertstring_2)");
							}
						}
					}
				}
				$queries[] =("delete from heaptable where clicktime < $hourtime");
			}
			else { 	$ausgabe .= "KEIN HEAPDATA VORHANDEN :(";}

		# DIE UPDATES DER CLICKS DER EINZELNEN USER WIRD ERST IN DEN SP?TEREN BL?CKEN GEMACHT UM STATEMENTS ZU SPAREN wenn Rundenstatus = 1 ist
		}
	}
	elseif ($globals[roundstatus] == 1 or $globals[roundstatus] == 2)	{

	}
	elseif ($globals[roundstatus] == 0 or $globals[roundstatus] == 2)	{

	}
    //
    // Roundstatus = 1 bedeutet, die Runde l?uft, ansonsten gibts auch nicht wirklich viel zu tun
    //
    

		// Maßnahme gegen Geisterkonzerne (Coder: DragonTEC, Date: 5.10.09)
		// Anm. o19 (damit hast du prima das Adminpanel gefraggt, weil die jetzt Löschungen und Neuanmeldungen nicht mehr überprüfen können)
		// Man sollte die wohl nicht löschen, sondern isalive = 0 oder so...
		
		// $ghost_ids = singles("SELECT id FROM status WHERE id NOT IN ( SELECT konzernid FROM users )	");  // Erzeugt ein Array mit allen Geister-IDs
		
		// for( $ghost_i = 0; $ghost_i < count( $ghost_ids ); $ghost_i++ ) // Geht jede Geister-ID durch
		// {
		   // $queries[] = ("DELETE FROM status WHERE id = " . $ghost_ids[$ghost_i]); // Löscht den Geist aus der status table
		   // $queries[] = ("DELETE FROM stats WHERE konzernid = " . $ghost_ids[$ghost_i] . " AND round = " . $globals['round'] ); // Löscht den Geist aus der stats table der aktuellen Runde
		// }
				
    
    
    
      // Sicherheitscheck auf Status-tabelle
      $statusTabelleInOrdnung = 0;
      $statusTabelleInOrdnung = single("select count(*) from status");
      if ($globals{roundstatus} == 1 && $statusTabelleInOrdnung > 0) {

        //***************************************************************//
        //                         Daten holen                           //
        //***************************************************************//
		$getstart = getmicrotime();
        // Spezifikationen holen
        $buildings = assocs("select * from buildings","building_id");
		$outerbuildings = $buildings;
		#pvar($oouterbuildings);
        $milstats = assocs("select * from military_unit_settings","unit_id");
		$spystats = assocs("select * from spy_settings","unit_id");
		$artefakte = get_artefakte();

        // Einzelne Daten holen
        $users = assocs("select * from users where konzernid > 0", "konzernid");
        $statuses = assocs("select * from status","id");
        $sciences_rohdaten = assocs("select * from usersciences");
		$partnerbonuses_rohdaten = assocs("select * from partnerschaften");
		$sciencestats = assocs("select treename as `group`, concat(name, typenumber) as name, level, maxlevel, sciencecosts, gamename,id from sciences where available=1", "name");	//der science Table
		$science_settings = assocs("select *,concat(name,typenumber) as iname from sciences","iname"); // Wird f?r sp?tere nw berechnugn gebraucht
		$forschungsq_rohdaten = assocs("select konzernid, name, position from kosttools_forschungsq order by position asc");
		$gebaeudeq_rohdaten = assocs("select user_id, building_id, position, number from kosttools_gebaeudeq order by position asc");
		$militaerq_rohdaten = assocs("select user_id, unit_id, position, number, type from kosttools_militaerq order by position asc");
        $built_buildings = assocs("select building_id,number,user_id,building_name from build_buildings where time <= ".$time);
        $built_military = assocs("select unit_id,user_id,number from build_military where time <= ".$time);
		$in_build_military = assocs("select sum(number) as number,user_id from build_military where time > ".$time." group by user_id", "user_id");
		$in_build_carrier = assocs("select sum(number) as number, user_id from build_military where time > ".$time." and (unit_id = 24 or unit_id = 40) group by user_id", "user_id");
        $built_spies = assocs("select unit_id,user_id,number from build_spies where time <= ".$time);
		$in_build_spies = assocs("select sum(number) as number, user_id from build_spies where time > ".$time." group by user_id", "user_id");
        $built_sciences = assocs("select name,user_id from build_sciences where time <= ".$time);
		$in_build_sciences = assocs("select name,user_id from build_sciences where time > ".$hourtime, "user_id");
		$build_syndarmee = assocs("select rid, miltype, time_there, sum(number) as number from build_syndarmee where time_there <= $time and done = 0 group by rid, miltype, time_there");
        $back_military = assocs("select unit_id,user_id,number from military_away where time <= ".$time);
	    $away_military_for_nw_rohdaten = assocs("select unit_id,user_id,number from military_away where time > ".$time);
        $back_transfer = assocs("select user_id,receiver_id,product,number from transfer where finished=0 and time <= ".($time-60*60*RESBACKTIME));
		$market_stuff_rohdaten = assocs("select owner_id,sum(number) as number,type,prod_id from market group by owner_id,type,prod_id");
        $syndikate_data_safe_rohdaten = assocs("select synd_id, nw, land, time from syndikate_data_safe where time >= (".($hourtime-24*3600).") order by time desc");
		$syndikate_data = assocs("select synd_id, max_pool, aktien_pool, aktienkurs, name, dividenden,dividenden_energy,dividenden_sciencepoints,dividenden_metal, aktmod,artefakt_id, open, syndsciencestype, artefakt_id, atwar, podmetal, podmoney, podenergy, podsciencepoints, artefakt_wait from syndikate", "synd_id");
		$synmembers = assocs("select count(*) as number,rid from status group by rid","rid"); // Anzahl Spieler pro Syndikat fr Synforschungen
		$allianzen_kuendigungen = assocs("select synd_id from allianzen_kuendigungen where time <= ".$time, "synd_id");
		$naps_kuendigungen = singles("select napid from naps_spieler_spezifikation where gekuendigt_time > 0 and gekuendigt_time <= ".$time." and gekuendigt_done=0");
 		$resstats = getresstats(); # wichtig, das muss hier am anfang stehen damit f?r die syndikatsgeb?ude die werte von der alten stunde genommen werden, da paar zeilen weiter unten bereits die neuen ermittelt werden
		$syndforschungen = assocs("select energyforschung,sabotageforschung,creditforschung,synarmeeforschung,synd_id from syndikate","synd_id");
		$wardata = assocs("select war_id, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3, first_1_lwt, first_2_lwt, first_3_lwt, second_1_lwt, second_2_lwt, second_3_lwt, first_1_landstart, first_2_landstart, first_3_landstart, second_1_landstart, second_2_landstart, second_3_landstart, artefakt_want_first_1, artefakt_want_first_2, artefakt_want_first_3, artefakt_want_second_1, artefakt_want_second_2, artefakt_want_second_3, starttime from wars where status = 1", "war_id");
		$urlaubsaktivierungen = assocs("select user_id, starttime from options_vacation where starttime <= $hourtime and activated_by_update = 0", "user_id");
		$komfortpaket_users = singles("select konzernid from features where feature_id = 11");
		$forschungsq_users = assocs("select konzernid from features where feature_id = 8", "konzernid");
		$gebaeudeq_users = assocs("select konzernid from features where feature_id = 10", "konzernid");
		$militaerq_users = assocs("select konzernid from features where feature_id = 9", "konzernid");
		$in_build_artefakte = assocs("select * from build_artefakte","synd_id");
		$features_generell_for_kill_decision = assocs("select count(*) as count, konzernid from features group by konzernid", "konzernid");
		$allfeatures = assocs("select konzernid from users where premiumfeature_mailaktion >= 1", "konzernid");
		//select("TRUNCATE TABLE `market_buffer` ");// Quickfix für blockierten Markt - hier sollte während des Updates eh nix mehr drinstehen
		
			foreach ($allfeatures as $vl) {
				$komfortpaket_users[] = $vl['konzernid'];
				$forschungsq_users[$vl['konzernid']] = 1;
				$gebaeudeq_users[$vl['konzernid']] = 1;
				$militaerq_users[$vl['konzernid']] = 1;
				$features_generell_for_kill_decision[$vl['konzernid']] = 1;
			}
		

		if ($syndforschungen) {
			foreach ($syndforschungen as $ky => $vl) {
				$syndforschungen[$ky][energyforschung] = explode("|", $vl[energyforschung]);
				$syndforschungen[$ky][sabotageforschung] = explode("|", $vl[sabotageforschung]);
				$syndforschungen[$ky][creditforschung] = explode("|", $vl[creditforschung]);
				//$syndforschungen[$ky][synarmeeforschung] = explode("|", $vl[synarmeeforschung]);
			}
		} else  {$syndforschungen = 1;}

		//pvar($syndforschungen,syndforsch);
		
		/*//millstats aufbearbeiten  schwachfug^^
		$milstats_ref = array();
		$milstats_ref['nof']=array();
		$milstats_ref['pbf']=array();
		$milstats_ref['neb']=array();
		$milstats_ref['uic']=array();
		$milstats_ref['sl']=array();
		
		foreach($millstats as $unitid=>$values){
			$milstats[$values['race']][$values['type']]=$unitid;
		}*/

		$drausgabe.="Nach holen der Gesamtdaten: $dr \n";
		
		$synids_noanfaenger = singles("select synd_id from syndikate where synd_type='normal'");
					
		// Tag setzen fr Syndikate, die gerade ein Artefakt bauen
		
		$weeks_played = ceil((round_days_played()+1)/ 7); //Tunnelfuchs Monument
		
		foreach($syndikate_data as $synid=>$val){
			if($val['artefakt_wait']>0){
				$syndikate_data[$synid]['artefakt_wait']--;
				$queries[]="update syndikate set artefakt_wait=".$syndikate_data[$synid]['artefakt_wait']." where synd_id=".$synid;
			}	
			/*if($hourtime==0 && $val['artefakt_id']){ //Unterhaltskosten für Monus
				if($val['artefakt_store']==1){
					$queries[] = "update syndikates set podmoney=podmoney-".(ARTEFAKT_COST_PER_DAY_PER_WEEK * $weeks_played)." where synd_id=".$synd_id;
				} else {
					$queries[] = "update syndikates set artefakt_id=0 where synd_id=".$synd_id;
					$msg= "Das Syndikat <b>".$syndikate_data[$tag][name]." (#".$syndikate_data[$tag][synd_id].")</b> hat <b>".$artefakte[$in_build_artefakte[$tag][artefakt_id]][name]." </b> verloren!";
					towncrier($synids_noanfaenger,$msg,0,3);
				}
			}*/
		}
		
		
		foreach ($in_build_artefakte as $tag=>$temp) {
			
			if(--$in_build_artefakte[$tag]['duty']>0){
				$syndikate_data[$temp[synd_id]][build_artefakt] = 1;
				$syndikate_data[$temp[synd_id]][duty_artefakt]=$temp['duty']-1;
				$queries[] = "update build_artefakte set duty=duty-1 where synd_id=$tag";
			} else {
				$queries[] = "delete from build_artefakte where synd_id = ".$tag."";
				$queries[] = "update syndikate set artefakt_wait=".WAIT_TIME_AFTER_ARTEFAKT_ABORT." where synd_id=".$tag;
				$msg= "Der Bau des Monumentes <b>".$artefakte[$in_build_artefakte[$tag][artefakt_id]][name]." durch </b> Syndikat <b>".$syndikate_data[$tag][name]." (#".$syndikate_data[$tag][synd_id].")</b> wurde abgebrochen!";
				towncrier($synids_noanfaenger,$msg,0,3);
			}
			
			
		}

		// Sciences f?r besseren Aufruf nach User_id verarbeiten

		// Nachschauen ob Standardpreise fr einheiten schon gesetzt wurden:
		$zeroexists = single("select min(current_price) from military_unit_settings");
		if ($zeroexists) $zeroexists = single("select min(current_price) from spy_settings");
		//$marine = assoc("select * from military_unit_settings where unit_id=2");
		if ($zeroexists == 0) { // Wird auf 0 gesetzt wenn Runde beendet wird (roundstatus == 1 && time > round_end_time)
		 	select("update military_unit_settings set current_price = credits+minerals*6+energy*1.2+sciencepoints*20");

		 	// Behemoths brauchen spezialbehandlung weil ressourcen dort überall 0 sind.
		 	select("UPDATE `military_unit_settings` SET `description` = NULL ,`current_price` = '10000' WHERE `unit_id` =26 LIMIT 1 ;");
		 	select("update spy_settings set current_price = credits+energy*1.2");
			$milstats = assocs("select * from military_unit_settings","unit_id");
			$spystats = assocs("select * from spy_settings","unit_id");
		}


		foreach ($sciences_rohdaten as $vl)	{
			$scienceses[$vl[user_id]][$vl[name]] = $vl[level];
		}

		foreach ($partnerbonuses_rohdaten  as $vl) {
			$partnerbonuses[$vl[user_id]][$vl[pid]] = $vl[level];
		}

		// Awaymilitary f?r Networthberechnung vorbereiten

		foreach ($away_military_for_nw_rohdaten as $vl)	{
			$away_military_for_nw[$vl[user_id]][$milstats[$vl[unit_id]][type]] += $vl[number];
		}
		
		//bach mill rechnung by Christian 16.8.10
		foreach ($back_military as $vl)	{
			$back_military_ext[$vl[user_id]][$milstats[$vl[unit_id]][type]] += $vl[number];
			$statuses{$vl{user_id}}{$milstats{$vl{unit_id}}{type}} += $vl{number}; //add by Christian 17.8.10
		}
		
		//build mill rechnunf by Christian 16.8.10
		foreach ($built_military as $vl)	{
			$built_military_ext[$vl[user_id]][$milstats[$vl[unit_id]][type]] += $vl[number];
			$statuses{$vl{user_id}}{$milstats{$vl{unit_id}}{type}} += $vl{number}; //add by Christian 17.8.10
		}

		// Marketzeug f?r Networthberechnung vorbereiten

		foreach ($market_stuff_rohdaten as $vl)	{
			$prod = changetype($vl[type],$vl[prod_id]);
			$markets[$vl[owner_id]][$prod[product]] = $vl[number];
		}
		unset($market_stuff_rohdaten); // Wird nicht weiter gebraucht

		// Syndikats-Safe-Daten vorbereiten f?r sp?tere Berechnung der Aktienkurse

		foreach ($syndikate_data_safe_rohdaten as $vl)	{
			if ($vl[land] > 0) { //R45 statt des Networth wird nun das Land zur Kursberechnung verwendet
				if (!$ii[$vl[synd_id]]) {
					$ii[$vl[synd_id]] = 1;
				}
				else {
					$ii[$vl[synd_id]]++;
				}
				$syndikate_data_safe[$vl[synd_id]][$ii[$vl[synd_id]]] = $vl[land];
			}
			#echo "nw: ".$vl[nw]." - synd_id: ".$vl[synd_id]." - time: ".(($hourtime-$vl[time])/3600)."<br>";
		}

		// Forschungsqueue-Daten verarbeiten

		foreach ($forschungsq_rohdaten as $vl)	{
			$forschungsq[$vl[konzernid]][$vl[position]] = $vl[name];
		}
		// Gebaeudeq-Daten verarbeiten
		foreach ($gebaeudeq_rohdaten as $vl)	{
			$gebaeudeq[$vl[user_id]][$vl[position]] = array("building_id" => $vl[building_id], "number" => $vl[number]);
		}
		// Militaerq-Daten verarbeiten
		foreach ($militaerq_rohdaten as $vl)	{
			$militaerq[$vl[user_id]][$vl[position]] = array("unit_id" => $vl[unit_id], "number" => $vl[number], "type" => $vl[type]);
		}
		
		// R4bbiT - 24.10.10
		// alte Maklergebote löschen
		select("DELETE FROM aktien_gebote WHERE user_id = 0 AND time <= ".($hourtime - 60*60*23));
		// Die Aktienmenge der User in die Save-Spalte schreiben - für die stündliche Anzeige der Aktienbesitzer
		select('UPDATE aktien SET number_save = number');


		// Neue Ressourcenpreise und Einheitenpreise ?ber Markt bestimmen

  		/*$energyprices = assocs("select * from marketlogs where action='buy' and prod_id = 1 and type='res' and time>".($time-60*60)." order by time desc, price asc limit 10");
		$metalprices =  assocs("select * from marketlogs where action='buy' and prod_id = 2 and type='res' and time>".($time-60*60)." order by time desc, price asc limit 10");
		$sciencepointsprices = assocs("select * from marketlogs where action='buy' and prod_id = 3 and type='res' and time>".($time-60*60)." order by time desc, price asc limit 10");*/
		
		//neue Preisberechnung R60 (dragon12)
		$num_ticks = 5;
		
		$ressis_sql = 'SELECT prod_id, SUM( price * number*(time-'.($time-3600*(2+$num_ticks)).') ) / SUM( number*(time-'.($time-3600*(2+$num_ticks)).') ) AS avg, SUM(number) as amount, Count(*) as numTrans'
		. ' FROM marketlogs'
		. ' WHERE TYPE = \'res\''
		. ' AND action = \'buy\''
		. ' AND TIME <= '.$time
		. ' AND TIME >= '.($time-3600*$num_ticks)
		. ' GROUP BY prod_id';
		$ress_data = assocs($ressis_sql, "prod_id");
		
		$ressis_day_sql = 'SELECT prod_id, SUM(number)/24 as amount'
		. ' FROM marketlogs'
		. ' WHERE TYPE = \'res\''
		. ' AND action = \'buy\''
		. ' AND TIME <= '.$time
		. ' AND TIME >= '.($time-3600*24)
		. ' GROUP BY prod_id';
		$ress_day_avg = assocs($ressis_day_sql, "prod_id");
		
		//echo "EPRICES: ".$energyprices."<br>".count($energyprices)."<br>";
		/*if ($energyprices) {$energyprice = middlewert($energyprices); $energyprice = maxchange($energyprice, $resstats[energy][value]);}
		if ($metalprices) {$metalprice = middlewert($metalprices);$metalprice = maxchange($metalprice, $resstats[metal][value]);}
		if ($sciencepointsprices) {$sciencepointsprice = middlewert($sciencepointsprices);$sciencepointsprice = maxchange($sciencepointsprice, $resstats[sciencepoints][value]);}*/
		
		//energy
		if($ress_data[1] && $ress_data[1]['numTrans'] > 1 && $ress_day_avg[1]['amount'] > 0) {
			$scale_factor = $ress_data[1]['amount']/$ress_day_avg[1]['amount'];
			if ($scale_factor > 1) $scale_factor = 1;
			$energyprice_rdiff = $ress_data[1]['avg']/10/$resstats[energy][value]-1;
			if($energyprice_rdiff > 0.15) $energyprice_rdiff = 0.15;
			if($energyprice_rdiff < -0.15) $energyprice_rdiff = -0.15;
			$energyprice_rdiff *= $scale_factor;
			$energyprice = $resstats[energy][value] * (1 + $energyprice_rdiff);
		} else {
			$energyprice = $resstats[energy][value];
		}
		
      //metal
		if($ress_data[2] && $ress_data[2]['numTrans'] > 1 && $ress_day_avg[2]['amount'] > 0) {
			$scale_factor = $ress_data[2]['amount']/$ress_day_avg[2]['amount'];
			if ($scale_factor > 1) $scale_factor = 1;
			$metalprice_rdiff = $ress_data[2]['avg']/10/$resstats[metal][value]-1;
			if($metalprice_rdiff > 0.15) $metalprice_rdiff = 0.15;
			if($metalprice_rdiff < -0.15) $metalprice_rdiff = -0.15;
			$metalprice_rdiff *= $scale_factor;
			$metalprice = $resstats[metal][value] * (1 + $metalprice_rdiff);
		} else {
			$metalprice = $resstats[metal][value];
		}
		
		//sciencepoints
		if($ress_data[3] && $ress_data[3]['numTrans'] > 1 && $ress_day_avg[3]['amount'] > 0) {
			$scale_factor = $ress_data[3]['amount']/$ress_day_avg[3]['amount'];
			if ($scale_factor > 1) $scale_factor = 1;
			$sciencepointsprice_rdiff = $ress_data[3]['avg']/10/$resstats[sciencepoints][value]-1;
			if($sciencepointsprice_rdiff > 0.15) $sciencepointsprice_rdiff = 0.15;
			if($sciencepointsprice_rdiff < -0.15) $sciencepointsprice_rdiff = -0.15;
			$sciencepointsprice_rdiff *= $scale_factor;
			$sciencepointsprice = $resstats[sciencepoints][value] * (1 + $sciencepointsprice_rdiff);
		} else {
			$sciencepointsprice = $resstats[sciencepoints][value];
		}
		
		/*if($time > $globals['roundstarttime']+48*60*60){
			if (count($energyprices) < 3) {
				$rand=(0.95+mt_rand(0,10)/100);
				if($rand>1) $energyprice = ceil($resstats[energy][value] * $rand * 10)/10;
				else $energyprice = floor($resstats[energy][value] * $rand * 10)/10;
			}
			if (count($metalprices) < 3) {
				$rand=(0.95+mt_rand(0,10)/100); 
				if($rand>1)$metalprice = ceil($resstats[metal][value] * $rand * 10)/10;
				else $metalprice = floor($resstats[metal][value] * $rand * 10)/10;
			}
			if (count($sciencepointsprices) < 3) {
				$rand=(0.95+mt_rand(0,10)/100);
				if($rand>1) $sciencepointsprice = floor($resstats[sciencepoints][value] * $rand * 10)/10;
				else $sciencepointsprice = floor($resstats[sciencepoints][value] * $rand * 10)/10;
			}
			if($energyprice < 0.1) $energyprice=0.1;
			if($metalprice < 0.1) $metalprice=0.1;
			if($sciencepointsprice < 0.1) $sciencepointsprice = 0.1;
		} else {
			if (count($energyprices) < 3) {
				$energyprice = $resstats[energy][value];
			}
			if (count($metalprices) < 3) { 
				$metalprice = $resstats[metal][value];
			}
			if (count($sciencepointsprices) < 3) {
				$sciencepointsprice = $resstats[sciencepoints][value];
			}		
		}*/
		
		//logs für neue preisberechnung anfang (by dragon12)
		
		/*//energie
		$pricelogs['ene']['oldPrice'] = $resstats['energy']['value'];
		$pricelogs['ene']['newPrice'] = $energyprice;
		$pricelogs['ene']['wavgTick'] = $pricelogsResTick[1]['wavgTick'];
		$pricelogs['ene']['wavgTick2'] = $pricelogsResTick[1]['wavgTick2'];
		$pricelogs['ene']['wavg3Ticks'] = $pricelogsRes3Ticks[1]['wavg3Ticks'];
		$pricelogs['ene']['wavg3Ticks2'] = $pricelogsRes3Ticks[1]['wavg3Ticks2'];
		$pricelogs['ene']['amount'] = $pricelogsResTick[1]['amount'];
		$pricelogs['ene']['amount3'] = $pricelogsRes3Ticks[1]['amount'];
		$pricelogs['ene']['numTrans'] = $pricelogsResTick[1]['numTrans'];
		$pricelogs['ene']['numTrans3'] = $pricelogsRes3Ticks[1]['numTrans'];
		//erz
		$pricelogs['erz']['oldPrice'] = $resstats['metal']['value'];
		$pricelogs['erz']['newPrice'] = $metalprice;
		$pricelogs['erz']['wavgTick'] = $pricelogsResTick[2]['wavgTick'];
		$pricelogs['erz']['wavgTick2'] = $pricelogsResTick[2]['wavgTick2'];
		$pricelogs['erz']['wavg3Ticks'] = $pricelogsRes3Ticks[2]['wavg3Ticks'];
		$pricelogs['erz']['wavg3Ticks2'] = $pricelogsRes3Ticks[2]['wavg3Ticks2'];
		$pricelogs['erz']['amount'] = $pricelogsResTick[2]['amount'];
		$pricelogs['erz']['amount3'] = $pricelogsRes3Ticks[2]['amount'];
		$pricelogs['erz']['numTrans'] = $pricelogsResTick[2]['numTrans'];
		$pricelogs['erz']['numTrans3'] = $pricelogsRes3Ticks[2]['numTrans'];
		//fps
		$pricelogs['fps']['oldPrice'] = $resstats['sciencepoints']['value'];
		$pricelogs['fps']['newPrice'] = $sciencepointsprice;
		$pricelogs['fps']['wavgTick'] = $pricelogsResTick[3]['wavgTick'];
		$pricelogs['fps']['wavgTick2'] = $pricelogsResTick[3]['wavgTick2'];
		$pricelogs['fps']['wavg3Ticks'] = $pricelogsRes3Ticks[3]['wavg3Ticks'];
		$pricelogs['fps']['wavg3Ticks2'] = $pricelogsRes3Ticks[3]['wavg3Ticks2'];
		$pricelogs['fps']['amount'] = $pricelogsResTick[3]['amount'];
		$pricelogs['fps']['amount3'] = $pricelogsRes3Ticks[3]['amount'];
		$pricelogs['fps']['numTrans'] = $pricelogsResTick[3]['numTrans'];
		$pricelogs['fps']['numTrans3'] = $pricelogsRes3Ticks[3]['numTrans'];
		
		foreach($pricelogs as $key => $val) {
			if(!$pricelogsResTick) {
				$val['wavgTick'] = 0;
				$val['wavgTick2'] = 0;
				$val['amount'] = 0;
				$val['numTrans'] = 0;
				
			}
			if (!$pricelogsRes3Ticks) {
				$val['wavg3Ticks'] = 0;
				$val['wavg3Ticks2'] = 0;
				$val['amount3'] = 0;
				$val['numTrans3'] = 0;
			}
			select('insert into marketpricelog (  `time` ,  `product` ,  `oldPrice` ,  `newPrice` ,  `wavgTick` ,  `wavg3Ticks` ,  `wavgTick2` ,  `wacg3Ticks2` ,  `amount` ,  `amount3`,  `numTrans` ,  `numTrans3` )  values ('.$time.', \''.$key.'\', '.(round($val['oldPrice']*10)).', '.(round($val['newPrice']*10)).', '.(round($val['wavgTick']*10)).', '.(round($val['wavg3Ticks']*10)).', '.(round($val['wavgTick2']*10)).', '.(round($val['wavg3Ticks2']*10)).', '.$val['amount'].', '.$val['amount3'].', '.$val['numTrans'].', '.$val['numTrans3'].')');
		}*/
		//logs für neue preisberechnung ende
		
		$query = "insert into ressources (time,money,energy,sciencepoints,metal)
				values ($time,$moneyprice,$energyprice,$sciencepointsprice,$metalprice)";
		//echo "Hier preise: $query";
		$queries[] =($query);

		$drausgabe.="Nach Bestimmung der Ressourcenpreise, vor einzelnen statuses: $dr\n";
		$getend = getmicrotime();


		// Unitpreise:

		foreach ($milstats as $key => $temp) {
			$tprice=$temp[current_price];
			$unit_id = $temp[unit_id];
	  		$tprices = assocs("select * from market where inserttime<=$time and prod_id = $unit_id and type='mil' order by price asc limit 5");
			if ($tprices) {$tprice = middlewert($tprices)*10;}
			$newprice = (int) (($temp[current_price] * 5 + $tprice) / 6);
			select("update military_unit_settings set current_price = $newprice where unit_id=$unit_id");
		}

		// Spypreise:
		foreach ($spystats as $key => $temp) {
			$tprice=$temp[current_price];
			$unit_id = $temp[unit_id];
	  		$tprices = assocs("select * from market where inserttime<=$time and prod_id = $unit_id and type='spy' order by price asc limit 5");
			if ($tprices) {$tprice = middlewert($tprices)*10;}
			$newprice = (int) (($temp[current_price] * 5 + $tprice) / 6);
			select("update spy_settings set current_price = $newprice where unit_id=$unit_id");
		}

        //************************************************************************//
        //            Schleife f?r einzelne Spielerupdate starten                 //
        //    Aufwand: n*m, n= Anzahl der Spieler, m= Aktionen f?r jeden Spieler  //
        //************************************************************************//



        //***********************************************
        //        Zuerst Ressourcen updaten
        //***********************************************
		$beforestatusberechnung = getmicrotime();
		$profiler = new profiler();
		$profiler->init();
		$market_queries = array();
		$market_adddivis = array();
		$units_killed = array();
        foreach ($statuses as $status) {
            // Nur lebende Spieler updaten
            if ($status{alive} > 0) {
				$negative_energy = 0;
				$totalmilitary = 0;
				$totalspies = 0;
				$milloss = 0;
				$milloss_nof_elites = 0;
				$spyloss = 0;
				$lossstring =  "";
				$sciences = $scienceses[$status[id]];
				$partner = $partnerbonuses[$status[id]];
				$units_killed[$status['id']] = array();
                if($status{alive} == 1 ) {
                	 $mod = 1; 
                	 $umod = 1;
                }
                else {
                	$umod = 0.5; // Für energylageradd - weil $mod später noch bei energie geändert wird, was dann bei der energielagerproduktion zu problemen führt. 
                	$mod = 0.5;
                }
				$syndikate_data[$status[rid]][totalland] += $status[land]; // Synland zusammenz?hlen zur aktienberechnung sp?ter
				//  echo "<br><br>Before: Geld:".$status{money}." Energie: ".$status{energy}." Fp: ".$status{sciencepoints}." Metal: ".$status{metal}." Name: ".$status{syndicate}."<br>";
				
				##
				##########
				## Automatischen Wählen der Aktienpakete
				##########
				##
				//if(!$status['aktien_wahl'] && $time >= ($status['unprotecttime'] + 60*60*MAXTIME_AKTIEN)){ // Aktien gibts nach x Tagen nach der Schutzzeit
				//if(!$status['aktien_wahl'] && $time >= $status['unprotecttime')){ // Aktien direkt nach Beenden der Schutzzeit
				/* deaktiviert - R4bbiT - 01.11.12
				if(!$status['aktien_wahl']){ // Aktien gibts direkt bei Rundenstart/ nach Konzernerstellung
					foreach($syndikate_data as $tag => $val){
						if($val['synd_id'] == $status['rid']){
							$num = AKTIENPACKET_OWN_PLUS + AKTIENPACKET_SYNS;
						}
						else{
							$num = AKTIENPACKET_SYNS;
						}
						update_aktien('add', $status['id'], $num, $val['synd_id'], $val['aktienkurs']);
					}
					$statuses[$status['id']]['aktien_wahl'] = 'aktien';
					$queries[] = "INSERT INTO message_values
										(id,user_id,time,werte)
										VALUES
										(60, ".$status['id'].", ".$time.", '')";

				}*/
				
				
				#
				### Vergeben der Gamble-Boni - R4bbiT - 16.10.10
				#
				if($status['gamble_rest']){
					$status_hourtime = get_hour_time($status['createtime']);
					$rest = ($hourtime - $status_hourtime) / 3600;
					if($rest > 0 && ($rest % GAMBLE_TIME) == 0){ // Alle 6 Ticks gibts nen Boni
						$statuses[$status['id']]['gamble_rest'] -= 1;
						$statuses[$status['id']]['gamble_own'] += 1;
					}
				}
				
				
				#
				### Vergeben der täglichen Boni - R4bbiT - 27.12.10
				#
				if($hour == 0){
					$statuses[$status['id']]['daily_boni'] = 0;
				}
				// Verfügbar vom 3. bis zum 9. Spieltag des Konzernes ## ACHTUNG, TAG-1 eintragen!
				if(($hour == 0 || $hour == 12) && $time >= $status['createtime'] + 60*60*24*2 && $time <= $status['createtime'] + 60*60*24*8){
					$statuses[$status['id']]['daily_boni'] += 1;
				}
				
				
				//
				// Erinnerungsmail, wenn Spieler inaktiv geworden ist, nicht im Urlaub ist und noch keine erinnerungsmail bekommen hat
				//
				if ($status[lastlogintime] + TIME_TILL_INACTIVE < $time && $status[alive] == 1 && $status[inactivity_reminder_sent] == 0) {
				
					$user = assoc("select * from users where konzernid = $status[id]");
					$betreff ="Hallo $user[vorname] hast du deinen Konzern vergessen?"; 
					$message = "Hallo $user[vorname],\ndu hast dich seit einiger Zeit nicht mehr bei Syndicates eingelogt. Bitte denke daran, dass Syndicates ein Teamspiel ist und die anderen Spieler in deinem Syndikat auch auf dich angewiesen sind. Wenn du dich in den nächsten Tagen nicht bei Syndicates einloggst, wird dein Konzern automatisch gelöscht.\n\nUm dich einzuloggen, besuche einfach http://syndicates-online.de und gib deine Emogames-Logindaten ein.\n\nMit freundlichen Grüßen,\ndas Emogames Team\n";
					$email = $user[email];
					$to = $user[vorname]." ".$user[nachname];
					$statuses[$status[id]][inactivity_reminder_sent] = 1;
				
					sendthemail($betreff,$message,$email,$to);
					echo $status[syndicate]." - ".$user[email];
				}
			
				//
				// Ressourcen nur, wenn Spieler nicht inaktiv
				//
				if ($status[lastlogintime] + TIME_TILL_GLOBAL_INACTIVE > $time or $status[alive] == 2) {
				// Energie
					list ($energyadd, $energyloss, $energylageradd, $hpenergyadd, $dvdenergyadd) = energyadd($status{id}, 4); # 4 f?r energyloss damit message erstellt werden kann.
					if ($energyloss): $statuses[$status[id]]{energy} = $status[energy]; $messageinserts .= "(2,".$status[id].",$hourtime, '".pointit($energyloss)."'),"; endif;	# Message mit Energyloss vorbereiten
					if ($energyadd > 0) $energyadd *= $mod;
					$statuses[$status[id]]{energy}  += $energyadd;
					if ($statuses[$status[id]]{energy} < 0) {$statuses[$status[id]]{energy} = 0; $mod *= 0.5; $negative_energy = 1;} # Noch mehr Miese f?r andere Ressourcen, Energy mind. 0 setzen
					// Money
					list($moneyadd, $moneylageradd, $hpmoneyadd, $dvdmoneyadd) = moneyadd($status{id});

					$statuses[$status[id]]{money} += round($mod * $moneyadd);
					// Metal
					list($metaladd, $metallageradd, $hpmetaladd, $dvdmetaladd) = metaladd($status{id});
					$statuses[$status[id]]{metal}  += round($mod * $metaladd);
					// Sciencepoints
					list($sciencepointsadd, $sciencepointslageradd, $hpsciencepointsadd, $dvdsciencepointsadd) = sciencepointsadd($status{id});
					$statuses[$status[id]]{sciencepoints}  += round($mod * $sciencepointsadd);
					// ISTP_CHANGETIME reduzieren
					$status[istp_changetime] > 0 ? $statuses[$status[id]][istp_changetime]-- : 1;
					
					
				}
				else {
					$energyadd=0;$energyloss=0;$energylageradd=0;$hpenergyadd=0;$dvdenergyadd=0;
					$moneyadd=0;$moneylageradd=0;$hpmoneyadd=0;$dvdmoneyadd=0;
					$metaladd=0;$metallageradd=0;$hpmetaladd=0;$dvdmetaladd=0;
					$sciencepointsadd=0;$sciencepointslageradd=0;$hpsciencepointsadd=0;$dvdsciencepointsadd=0;
				}

				$safestring[$status[id]] = (round($mod * $moneyadd)).",".(round($mod * $metaladd)).",".(round($mod * $sciencepointsadd)).",".($energyadd).",".($energyloss).",".(round ($mod * $moneylageradd)).",".(round ($mod * $metallageradd)).",".(round ($mod * $sciencepointslageradd)).",".(round ($umod * $energylageradd)).",".(round ($mod * ($hpmoneyadd + RESSTATS_MODIFIER * ($hpenergyadd + $hpmetaladd + $hpsciencepointsadd))));
				# SYNDIKATSRESSOURCEN VERRECHNEN
				$syndikate_data[$status{rid}][dividenden] += round ($mod * ( $dvdmoneyadd ));
				$syndikate_data[$status{rid}][dividenden_energy] += round ($mod * ($dvdenergyadd));
				$syndikate_data[$status{rid}][dividenden_sciencepoints] += round ($mod * ($dvdsciencepointsadd));
				$syndikate_data[$status{rid}][dividenden_metal] += round ($mod * ($dvdmetaladd));
				
				/*automatisches sellen von 30% der lagerprod by dragon12 edit by inok R70 */
				//price wird derzeit zufällig bestimmt, das kann man evtl noch ändern
				$price = mt_rand(0, 100);
				$fraction_sold = 0.3;
				if ($price > 70)
					$price = mt_rand(20, 25);
				else if ($price > 45)
					$price = mt_rand(15, 20);
				else if ($price > 25)
					$price = mt_rand(10, 15);
				else if ($price > 10)
					$price = mt_rand(5, 10);
				else 
					$price = mt_rand(0, 5);
				if($energylageradd > 0)
					$market_queries[] = '(\'res\', 1, '.($fraction_sold*$energylageradd).', '.($resstats[energy][value]*(10+$price/10)).', 0, '.($time+mt_rand(0, 3600)).')';
				if($metallageradd > 0)
					$market_queries[] = '(\'res\', 2, '.($fraction_sold*$metallageradd).', '.($resstats[metal][value]*(10+$price/10)).', 0, '.($time+mt_rand(0, 3600)).')';
				if($sciencepointslageradd > 0)
					$market_queries[] = '(\'res\', 3, '.($fraction_sold*$sciencepointslageradd).', '.($resstats[sciencepoints][value]*(10+$price/10)).', 0, '.($time+mt_rand(0, 3600)).')';
				
				/*$tmp_divis = $fraction_sold*$metallageradd*price/100 + $fraction_sold*$energylageradd*price/100 + $fraction_sold*$sciencepointslageradd*price/100;
				if($market_adddivis[$status['rid']]) {
					$market_adddivis[$status['rid']] += $tmp_divis;
				} else 
					$market_adddivis[$status['rid']] = $tmp_divis;*/
				
				$syndikate_data_ressourcenadd[$status{rid}][podenergy] += round ($mod * $energylageradd*(1-$fraction_sold)); // * RESSTATS_MODIFIER);
				$syndikate_data_ressourcenadd[$status{rid}][podmoney] += round ($mod * $moneylageradd + $energylageradd*$fraction_sold*$resstats[energy][value] + $metallageradd*$fraction_sold*$resstats[metal][value] + $sciencepointslageradd*$fraction_sold*$resstats[sciencepoints][value]); // * RESSTATS_MODIFIER);
				$syndikate_data_ressourcenadd[$status{rid}][podmetal] += round ($mod * $metallageradd*(1-$fraction_sold)); // * RESSTATS_MODIFIER);
				$syndikate_data_ressourcenadd[$status{rid}][podsciencepoints] += round ($mod * $sciencepointslageradd*(1-$fraction_sold));// * RESSTATS_MODIFIER);
				
				/*$old_offers = assocs('SELECT * FROM market where inserttime < '.($time-24*60*60).' and owner_id = 0', 'offer_id');
				
				foreach($old_offers as $oid => $val) {
					if(mt_rand(0, 9) == 9) {
						$queries[] = 'DELETE FROM market WHERE offer_id = '.$oid.' LIMIT 1';
						$market_queries[] = '(\'res\', 3, '.$val['number'].', '.ceil($val['number']*1.1).', 0, '.$time.')';
					}
				}*/

				// Globalisierungsmasterplan berücksichtigen
				if ($artefakte[$syndikate_data[$status['rid']][artefakt_id]][bonusname] == "reduced_podtaxes") {
					$temp_add = round($syndikate_data[$status[rid]][podmoney] * GLOBALISIERUNGSMASTERPLAN_ZINSEN / 100 / $synmembers[$status['rid']]['number']);
					$temp_add_points = $temp_add;
					$syndikate_data_ressourcenadd[$status{rid}][podmoney] += $temp_add;
					
					$temp_add = round($syndikate_data[$status[rid]][podenergy] * GLOBALISIERUNGSMASTERPLAN_ZINSEN / 100 / $synmembers[$status['rid']]['number']);
					$temp_add_points += $temp_add * $energyprice;
					$syndikate_data_ressourcenadd[$status{rid}][podenergy] += $temp_add;

					$temp_add = round($syndikate_data[$status[rid]][podmetal] * GLOBALISIERUNGSMASTERPLAN_ZINSEN / 100 / $synmembers[$status['rid']]['number']);
					$temp_add_points += $temp_add * $metalprice;
					$syndikate_data_ressourcenadd[$status{rid}][podmetal] += $temp_add;

					$temp_add = round($syndikate_data[$status[rid]][podsciencepoints] * GLOBALISIERUNGSMASTERPLAN_ZINSEN / 100 / $synmembers[$status['rid']]['number']);
					$temp_add_points += $temp_add * $sciencepointsprice;
					$syndikate_data_ressourcenadd[$status{rid}][podsciencepoints] += $temp_add;

                    // Handelspunkte erhoehen
					$statuses[$status[id]][podpoints] += $temp_add_points;
				}
				
				$statuses[$status[id]][podpoints] += round ($mod * ($hpmoneyadd + RESSTATS_MODIFIER * ($hpenergyadd  + $hpmetaladd + $hpsciencepointsadd)));
				
				
				// Gegebenenfalls in Monument investieren
				if ($status[invest_in_artefakt] && $syndikate_data[$status[rid]][build_artefakt] && $status[sciencepoints] >= BUCHUNGSBETRAG_TICK && $in_build_artefakte[$status[rid]][invested] < KOSTEN_ARTEFAKT) {
					$statuses[$status[id]][sciencepoints]-= BUCHUNGSBETRAG_TICK;
					$in_build_artefakte[$status[rid]][invested] += BUCHUNGSBETRAG_TICK;
					$players_paid[$status[rid]][] = $status[id];
					$players_paid_msgtargets[$status[rid]][] = $status[id];
				}
				elseif ($syndikate_data[$status[rid]][build_artefakt]) {
					$players_paid_msgtargets[$status[rid]][] = $status[id];
				}

				// Namechanges wegnehmen, falls 2 Tage nach Rundenstart
				if (!in_protection($status) && $status[nc] > 0 && $time > $globals['roundstarttime'] + 2 * 86400 
				    && $status['syndicate'] != $status['id']) { // Bei Vergabe einer Hülse wird der Name auf die Konzernid gesetzt. 
										// Die Hülse soll dann aber nicht vom Update zurückgesetzt werden
					$statuses[$status[id]][nc] = 0;
				}

				// echo "After: Geld:".$statuses{$status{id}}{money} ." Energie: ".$statuses{$status{id}}{energy} ." Fp: ".$statuses{$status{id}}{sciencepoints} ." Metal: ".$statuses{$status{id}}{metal} ." Name: ".$status{syndicate}."<br>";
				// Land
				// automatic land acquisition
				if ($sciences{ind13}) {
					$statuses[$status[id]]{land} += $sciences{ind13} * IND13WERT;
					if (getServertype() == "basic" && $statuses[$status[id]][land] > BASIC_MAX_LANDGRENZE)
				       		$statuses[$status[id]][land] = BASIC_MAX_LANDGRENZE;	
				}
				if ($artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusname'] == "ha_tick_gain") { //großgrundbesistzer monu
					$statuses[$status[id]]{land} += $artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusvalue'];
				}
				
				###
				#######################################
				###	OVERCHARGE BERECHNUNG
				#######################################
				###
				// Zuviel Milit?r / Spione ?!
				
				##
				## Militï¿½ zusammenzï¿½len
				foreach ($milstats as $vl)	{
					if ($vl[race] == $status[race] || $vl[race] == "all")	{  //delelte by Christian 17.8.10
						$totalmilitary += $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $away_military_for_nw[$status[id]][$vl[type]];
					}
				}
				foreach ($spystats as $vl)	{
					if ($vl[race] == $status[race] || $vl[race] == "all")	{
						$totalspies += $status[$vl[type]] + $markets[$status[id]][$vl[type]];
					}
				}
				$totalmilitary += $in_build_military[$status[id]][number];
				$totalspies += $in_build_spies[$status[id]][number];

				$totalmilitary_save[$status[id]] = $totalmilitary;
				########################
				## 

				$maxmilstore = maxunits("mil"); echo "id: ".$status['id'] ." -- maxmilstore: $maxmilstore\n";
				$maxspystore = maxunits("spy");

				if ($status['race'] == "nof") { // Seit Runde 28 verbraucht der Carrier keine Kapazitäten mehr, d.h. er soll bei z.B. Energiemangel auch nicht kaputt gehen und muss überall, wo $totalmilitary verwendet wird, abgezogen werden
					// NOF-Sonderregelung Carrier

					$totalCarriers = $status['elites'] + $away_military_for_nw[$status[id]]['elites'] + $markets[$status[id]]['elites'] + $in_build_carrier[$status[id]]['number'];
					$totalCarriers_safe[$status['id']] = $totalCarriers;
					// if ($totalCarriers > 0.5 * $maxmilstore) $totalCarriers = $maxmilstore * 0.5;
				} else $totalCarriers = 0;


				### Anzahl Verluste berechnen MIL
				if (($totalmilitary-$totalCarriers) > $maxmilstore * (1+MAX_OVERCHARGE/100))	{ // Max Overcharge =  20
					// 4% Der Einheiten die ber 120% hinausgehen verrecken
					$milloss = ceil((($totalmilitary-$totalCarriers)-$maxmilstore * (1+MAX_OVERCHARGE/100))* 1.00); //($totalmilitary / $maxmilstore - 1) / 10);
					
					if (($totalmilitary-$totalCarriers)-$milloss < $maxmilstore * (1+MAX_OVERCHARGE/100)) 
						$milloss = ($totalmilitary-$totalCarriers) - $maxmilstore * (1+MAX_OVERCHARGE/100);
				}
				### Anzahl Verluste Nof-Carrier, wird in $milloss_nof_elites gespeichert
				if ($status['race'] == "nof") {
					if ($totalCarriers > $maxmilstore * ( 1 + MAX_OVERCHARGE / 100)) {
						$milloss_nof_elites = ceil(($totalCarriers-$maxmilstore * (1+MAX_OVERCHARGE/100))* 1.00); //($totalmilitary / $maxmilstore - 1) / 10);
						if ($totalCarriers-$milloss_nof_elites < $maxmilstore * (1+MAX_OVERCHARGE/100)) 
							$milloss_nof_elites = $totalCarriers - $maxmilstore * (1+MAX_OVERCHARGE/100);
					}
				}
				
				### Anzahl Verluste berechnen SPY
				//(Gesamtzahl Einheiten - Gesamtkapazitï¿½en) * [(Gesamtzahl Einheiten / Gesamtkapazitï¿½en - 1) * 10] / 100.
				if ($totalspies > $maxspystore * (1+MAX_OVERCHARGE/100))	{
					$spyloss = ceil(($totalspies-$maxspystore * (1+MAX_OVERCHARGE/100))* 1.00); //($totalspies / $maxspystore - 1) / 10);
					if ($totalspies - $spyloss < $maxspystore * (1+MAX_OVERCHARGE/100)) 
						$spyloss = $totalspies - $maxspystore * (1+MAX_OVERCHARGE/100);
				}
				
				##
				##	
				##

				
				if ($negative_energy)	{


					$temp_landwert = LANDWERT + ($artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusname'] == "land_cap_mil_bonus" ? $artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusvalue'] : 0);
					$temp_landwert *= (1+MAX_OVERCHARGE/100);

					$temp_landwert2 = LANDWERT2 + ($artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusname'] == "land_cap_spy_bonus" ? $artefakte[$syndikate_data[$status[rid]]['artefakt_id']]['bonusvalue'] : 0);
					$temp_landwert2 *= (1+MAX_OVERCHARGE/100);

					
					if (($totalmilitary-$totalCarriers) - $milloss > $status[land] * ($temp_landwert)) { 
						$milloss += ceil((($totalmilitary-$totalCarriers) - $milloss - $status[land] * $temp_landwert) * 0.015);
					}
					if ($totalspies - $spyloss > $status[land] * ($temp_landwert2)) {
						$spyloss += ceil(($totalspies - $spyloss - $status[land] * $temp_landwert2) * 0.015);
					}
				}

				// speichern fr spï¿½er den Militï¿½assistent
				$totalmil_for_militaerq[$status[id]] = ($totalmilitary-$totalCarriers) - $milloss;
				$totalspy_for_militaerq[$status[id]] = $totalspies - $spyloss;


				##
				## Verrechnung der Militï¿½verluste
				##
				if ($milloss or $milloss_nof_elites)	{
					$set_zero = 0;
					if ($in_build_military[$status[id]][number])	{ //by Christian 17.810 add time > ".$time." and 
						$mil_in_build = assocs("select sum(number) as number,unit_id from build_military where time > ".$time." and user_id=".$status[id]." group by unit_id","unit_id");
					}
					else { // HAH - Daran lag es , sehr vielsagender kommentar :D daran lag es, dass manchmal units im oc starben, die es gar nicht gibt, weil sie als dummies von jemand anderem kamen. finally fixed by dragon12 28.3.12
						$set_zero=1;
					}
					// F?R JEDEN MILTYP SACHEN KILLEN
					foreach ($milstats as $vl)	{
						if ($vl[race] == $status[race] || $vl[race] == "all")	{
							if ($set_zero == 1)	$mil_in_build[$vl[unit_id]][number] = 0;
							
							if(($vl['type']=="elites" || $vl['type']=="elites2" || $vl['type']=="techs") && !$set_zero){
								$dummyid=single("select unit_id from military_unit_settings where race='dummy' and type='".$vl['type']."'");
								$mil_in_build[$vl[unit_id]][number]=$mil_in_build[$vl[unit_id]][number]+$mil_in_build[$dummyid][number];
							} else {
								$mil_in_build[$dummyid][number] = 0; //bugfix fuer negative units
							}
							$specificloss = 0;
							if ($status['race'] != "nof" or $vl['type'] != "elites") {
								$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $away_military_for_nw[$status[id]][$vl[type]] + $mil_in_build[$vl[unit_id]][number] ) / ($totalmilitary-$totalCarriers) * $milloss);
							} else if ($status['race'] == "nof" && $vl['type'] == "elites") {
								$specificloss = ceil($milloss_nof_elites);
							}
							
							if ($specificloss < 0) $specificloss = 0;
							
							if($vl[name]=="HUC" || $vl[name]=="AUC" || $vl[name]=="BUC"){
								$vl[name]=single("select name from military_unit_settings where type='".$vl[type]."' and race='".$status['race']."'");
							}
							// Losstring um aktuelle Einheit ergï¿½zen
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;
							
							// ALS ERSTES MILIT?R IN BAU KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{ //by Christian 17.8.10 add time > ".$time." and 
								list($number,$unique_id) = row("select number, unique_id from build_military where time > ".$time." and unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss): $queries[] =("update build_military set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from build_military where unique_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}
							$units_killed[$status['id']][$vl[type]] = $specificloss; //ist fuer nw, in bau zaehlt nicht
							
							// ALS Nï¿½HSTES MILIT?R AWAY KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{ //by Christian 17.8.10 add time > ".$time." and 
								list($number,$unique_id) = row("select number, unique_id from military_away where time > ".$time." and unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss) {
									$queries[] =("update military_away set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
									$away_military_for_nw[$status[id]][$vl[type]] -= $specificloss;
								}
								elseif ($number) {
									$specificloss -= $number; $queries[] =("delete from military_away where unique_id=".$unique_id); $already_done[] = $unique_id;
									$away_military_for_nw[$status[id]][$vl[type]] -= $number;
								}
								else {
									$nothing_left = 1;
									$away_military_for_nw[$status[id]][$vl[type]] = 0;
								}
							}

							// ALS Nï¿½HSTES MILITï¿½ AUF DEM MARKT Tï¿½EN
							$markettypes = changetype($vl[type]);
							$nothing_left = 0;
							$number = 0; $unique_id = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								if($test[$status[id]]) 
									$test[$status[id]] += 2;
								else
									$test[$status[id]] = 2;
								list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
								if ($number > $specificloss) {
									$queries[] =("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
									$markets[$status[id]][$vl[type]] = $markets[$status[id]][$vl[type]] - $specificloss;
									echo '<br />specificloss'.$specificloss.'<br />';
								}
								elseif ($number) {
									$specificloss -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
									$markets[$status[id]][$vl[type]] = $markets[$status[id]][$vl[type]] - $number;
									echo '<br />number'.$number.'<br />';
								}
								else 
									$nothing_left = 1;
							}
							
							$units_killed[$status['id']][$vl[type]] -= $specificloss; //ist fuer nw, mil daheim wird da schon abgezogen
							// ZUM SCHLUSS ERST DAS WAS ZU HAUSE IST
							$statuses[$status[id]]{$vl[type]} -= $specificloss;
						}
					}
				}

				if ($spyloss)	{
					$set_zero = 0;
					if ($in_build_spies[$status[id]][number])	{
						$spies_in_build = assocs("select sum(number) as number,unit_id from build_spies where user_id=".$status[id]." group by unit_id","unit_id");
					}
					else {
						$set_zero = 1;
					}
					// F?R JEDEN MILTYP SACHEN KILLEN
					foreach ($spystats as $vl)	{
						if ($vl[race] == $status[race] || $vl[race] == "all")	{
						if ($set_zero == 1) $spies_in_build[$vl[unit_id]][number] = 0;
							$specificloss = ceil(( $status[$vl[type]] + $markets[$status[id]][$vl[type]] + $spies_in_build[$vl[unit_id]][number] ) / $totalspies * $spyloss);
							if ($specificloss < 0) $specificloss = 0;
							if ($specificloss): $lossstring .= pointit($specificloss)." ".$vl[name].", "; endif;
							
							// Losslogs
							if ($specificloss) {
								select("insert into losslogs 
									(user_id,product,number,time,`status`,market,`build`) 
										values 
											($status[id],
											'$vl[type]',
											$specificloss,
											$time,
											".(int)$status[$vl[type]].",
											".(int)$markets[$status[id]][$vl[type]].",
											".(int)$spies_in_build[$vl[unit_id]][number].")");
							}

							// ALS ERSTES SPIES IN BAU KILLEN
							$nothing_left = 0;
							$already_done = array();
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, unique_id from build_spies where unit_id='".$vl[unit_id]."' and user_id=".$status[id]." ".($already_done ? "and unique_id not in (".join(",",$already_done).") ":"")."order by time desc limit 1");
								if ($number > $specificloss): $queries[] =("update build_spies set number=number-".$specificloss." where unique_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from build_spies where unique_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}
							$units_killed[$status['id']][$vl[type]] = $specificloss; //fuer nw, in bau zaehlt nicht
							
							//DANN ERST DIE AUFM MAKRT
							$markettypes = changetype($vl[type]);
							$nothing_left = 0;
							$number = 0; $unique_id = 0;
							$already_done = array();
							// ZUERST ALLES VOM MARKT KILLEN
							while ($specificloss > 0 and !$nothing_left)	{
								list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
								if ($number > $specificloss): $queries[] =("update market set number=number-".$specificloss." where offer_id=".$unique_id); $specificloss = 0;
								elseif ($number): $specificloss -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
								else: $nothing_left = 1;
								endif;
							}

							$units_killed[$status['id']][$vl[type]] -= $specificloss;
							// ALS LETZTES SPIES DIE ZU HAUSE SIND
							$statuses[$status[id]]{$vl[type]} -= $specificloss;
						}
					}
				}
				
				###
				#######################################
				###	OVERCHARGE BERECHNUNG ENDE 
				#######################################
				###
				
				
				//
				// Uic Nanofabrik updaten
				//
				if ($status{race} == "uic") {
					if ($status{multifunc} % 100 != 0 && $status{multifunc} > 99) {
						$statuses[$status[id]][multifunc]--;
					} elseif($status{multifunc} > 99){
						$statuses[$status[id]][multifunc]=$statuses[$status[id]][multifunc]/100;
					}
				}
				//
				// Pbf Armory
				//
				if ($status{race} == "pbf") {
					if ($status{armories} > 0) {
						$statuses[$status[id]][multifunc] += $status{armories}*ARMORYPROD;
						if ($statuses[$status[id]][multifunc] > $status{armories} * ARMORYSAFE): $statuses[$status[id]][multifunc] = $status{armories} * ARMORYSAFE; endif;
					}
				}
				/*
				//
				// Behemothfabriken
				//
				if ($status[behemothfactories] && ($status['suspend_schools'] == 0 || $status['suspend_schools'] == 2)) {
					if ($status[elites] >= 2) { // Mindestens 2 Carrier für mindestens 1 Behemoth
						$tobuild = $status[behemothfactories] * 2; // maximal Anzahl Behemothfabriken Behemoths bauen
						
						//Behes prodden nur noch beschränkt in OC //Runde 50 by Christian 5.9.2010
						define(MAX_BEHE_OC,20); //Behes prodden 120% in OC
						$maxMillForBehes = $maxmilstore * ( 1 + MAX_BEHE_OC / 100);
						if ($tobuild > $maxMillForBehes - $totalmil_for_militaerq[$status[id]]){
							$tobuild = $maxMillForBehes - $totalmil_for_militaerq[$status[id]]; //fals nötig beschränken
						}
						//Ende BeheOc Beschränkung
						
						// Baubar durch Ressourcenbeschränkung
						foreach (array("credits" => "money", "minerals" => "metal", "energy" => "energy", "sciencepoints" => "sciencepoints") as $milstats_ressourcename => $status_ressourcename) {
							if ($milstats[26][$milstats_ressourcename] > 0) {
								$tempcosts = $milstats[26][$milstats_ressourcename];
								if ($sciences['mil11']) $tempcosts = (int) $tempcosts * 2/3;
								$buildable = floor($statuses[$status[id]][$status_ressourcename] / $tempcosts);
								if ($buildable < $tobuild) $tobuild = $buildable;
							}
						}
						// Bei Energiemangel/Urlaub nur halbe Produktion
						$tobuild = floor($tobuild * $mod);
						if ($statuses[$status[id]][elites] / 2 < $tobuild) $tobuild = floor($statuses[$status[id]][elites] / 2);
						if ($tobuild < 0) $tobuild = 0;
						// Ressourcen abziehen
						if ($tobuild > 0) {
							foreach (array("credits" => "money", "minerals" => "metal", "energy" => "energy", "sciencepoints" => "sciencepoints") as $milstats_ressourcename => $status_ressourcename) {
								if ($milstats[26][$milstats_ressourcename] > 0) {
									$tempcosts = $milstats[26][$milstats_ressourcename];
									if ($sciences['mil11']) $tempcosts = (int) $tempcosts * 2/3;
									$statuses[$status[id]][$status_ressourcename] -= $tobuild * $tempcosts;
								}
							}
							$statuses[$status[id]][techs] += $tobuild;
							$statuses[$status[id]][elites] -= 2 * $tobuild;
						}
					}
				}
				*/
				//
				// Schools
				//
				if ($status[schools] && ($status['suspend_schools'] == 0 || $status['suspend_schools'] == 3)) {
					if ($status[offspecs] || $status[defspecs]) {
						// Diverse Zeitberechnungen zur Formulierung des Db Statements
						$hourtime = get_hour_time($time);
						$faktor=0;
						$unitid = 40;//$milstats[$status[race]]['elites'];
						//toodoo
						$faktor=0;
						if ($sciences['mil13']) $faktor = MIL13BONUS_FASTER_SCHOOLS;

						
						$varnumber = (SCHOOLTIME-$faktor);
						$buildtime_schools = $hourtime + $varnumber * 60 * $globals{roundtime};			#Bauzeit in Sekunden f?r mileinheiten
						// bestimmen, wieviele einheiten maximal ausgebildet werden
						$tobuildtemp = $status[schools] / $varnumber;
						//pvar($varnumber,varnumber);
						// Bei Energiemangel/Urlaub nur halbe Produktion
						$tobuildtemp = floor($tobuildtemp * $mod);
						$tobuild = floor ($tobuildtemp);
						print $mod.":".$tobuild."**\n";
						//pvar($tobuild,tobuild);
						//pvar($status[rest],statusrest);
						$rest = $tobuildtemp - $tobuild;
						//pvar($rest,restbefore);
						if ($status[rest] + $rest > 1) {
							$tsum = $rest + $status[rest];
							$tsumfloor = floor($tsum);
							$tobuild += $tsumfloor;
							$rest = $tsum - $tsumfloor;
						}
						else {
							$rest += $status[rest];
						}
						//pvar($rest,restafter);
						//pvar($tobuild,after);
						$statuses[$status[id]][rest] = $rest;
						$sumspecs = $status[offspecs]+$status[defspecs];
						$offspecsrel = $status[offspecs] / $sumspecs;
						$defspecsrel = $status[defspecs] / $sumspecs;
						$buildoffspecs = floor($tobuild * $offspecsrel);
						$builddefspecs = floor($tobuild * $defspecsrel);
						$buildrest = $tobuild - $buildoffspecs - $builddefspecs;
						if ($offspecsrel > $defspecsrel) {$buildoffspecs += $buildrest;}
						else {$builddefspecs += $buildrest;}
						$buildoffspecs > $status[offspecs] ? $buildoffspecs = $status[offspecs] : 1;
						$builddefspecs > $status[defspecs] ? $builddefspecs = $status[defspecs] : 1;
						$statuses[$status[id]][offspecs] -= $buildoffspecs;
						$statuses[$status[id]][defspecs] -= $builddefspecs;
						$finalnumber = $buildoffspecs + $builddefspecs;
						if ($finalnumber > 0) {
							$mbuildstringschools.= "($unitid,".$status[id].",$finalnumber,$buildtime_schools),";
						}
					}
				}

				//
				// Patriotsvermehrung
				//
				if ($status[race] == "neb" && $status[elites] > 0) {
					$unitid = 40;
					$hourtime = get_hour_time($time);
					$buildtime_patriots = $hourtime + PATRIOTTIME * 60 * $globals{roundtime};			#Bauzeit in Sekunden f?r mileinheiten
					// bestimmen, wieviele einheiten maximal ausgebildet werden
					$tobuildtemp = ($status[elites] / 100) * PATRIOTWACHSTUM;
					if ($status[alive] == 2) $tobuildtemp *= 0.5;
					// BESCHRï¿½KUNG WEGEN MAXUNITZAHL!
					$maxunitstemp = maxunits("mil");
					//pvar($maxunitstemp,maxunitstemp);
					if ($tobuildtemp + $totalmilitary_save[$status[id]] > $maxunitstemp * PATRIOTS_OVERCHARGE) {
						$tobuildtemp = $maxunitstemp * PATRIOTS_OVERCHARGE - $totalmilitary_save[$status[id]];
						$tobuildtemp = $tobuildtemp < 0 ? 0 : $tobuildtemp;
					}

					$tobuild = floor ($tobuildtemp);

					if ($tobuild > 0) {
						$mbuildstringschools.= "($unitid,".$status[id].",$tobuild,$buildtime_patriots),";
					}
				}


				if ($lossstring)	{
					$lossstring = chopp($lossstring);$lossstring = chopp($lossstring);
					$messageinserts .= "(20,".$status[id].",$hourtime, '".$lossstring."'),";
				}
        	}
        }
        if(count($market_queries) > 0) {
        	$market_query = 'insert into market (type, prod_id, number, price, owner_id, inserttime) values '.$market_queries[0];
        	$count = count($market_queries);
        	for($i = 1; $i < $count; $i++) {
        		$market_query .= ', '.$market_queries[$i];
        	}
        	$queries[] = $market_query;
        }
        /*foreach($market_adddivis as $key => $val) {//$rid => 
        	
        }*/
        
		$profiler->add_mark("Zeile 687");
		$afterstatusberechnung = getmicrotime();
		$drausgabe.="Nach den ganzen statusberechnungen: $dr\n";

        //***********************************************
        //        Sachen in Bau verrechnen
        //***********************************************
		
		$buildstart = getmicrotime();
        // Geb?ude
		$ausgabe .= "<br><br>Geb?ude/Land";
        foreach ($built_buildings as $value) {
            // 127 = Land
            if ($value{building_id} != 127) {
				$ausgabe .= "<br>Geb (BID:".$value{building_id}.")(BNAME:".$value{building_name}."): Konzernid: ".$value{user_id}."; ".$buildings{$value{building_id}}{name_intern}." vorher/nachher ".$statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}}."/";
                $statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}} += $value{number};
				$ausgabe .= $statuses{$value{user_id}}{$buildings{$value{building_id}}{name_intern}};
            }
            else {
				$ausgabe .= "<br>Land: Konzernid: ".$value{user_id}."; Land vorher/nachher ".$statuses{$value{user_id}}{land}."/";
                $statuses{$value{user_id}}{land} += $value{number};
				$ausgabe .= $statuses{$value{user_id}}{land};
            }
            $statuses[$value{user_id}][gvi] = 0; // Konfigphase beenden, wenn das erste mal was fertig wird.
        }
		$profiler->add_mark("Zeile 710");
        // Milit?reinheiten
		$ausgabe .= "<br><br>Milit?reinheiten";
        foreach ($built_military as $value) {
			$ausgabe .= "<br>Mil (ID:".$value[unit_id]."): ".$milstats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL (vorher/nachher) ".$value[number]." (".$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}}."/";
            //$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} += $value{number}; decoment by Christian 17.8.10
         if ($statuses[$values['user_id']]['race'] == "nof" && $milstats[$value[unit_id]][type] == "elites") $value[number] = 0; // Carrier verbrauchen keinen Platz
			//$totalmil_for_militaerq[$value{user_id}] += $value{number};	//by Christian 16.8.10	// Lï¿½ung ist nicht ganz sauber - fr den Milassi zweckmï¿½ig, allerdings werden in diesem Tick fertig gebaute Einheiten nicht beim Milloss bercksichtigt (s.o.)
			if ($value{unit_id} == 19) { // Patriots geben 50 geld beim erstmaligen bauen
	            //$statuses{$value{user_id}}{money} += $value{number}*50; // Mit ï¿½derungen Runde 20 entfernt
			}
			$ausgabe .= $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} . ")";
        }
		$profiler->add_mark("Zeile 721");

		// Syndikatsarmee
		if ($build_syndarmee) {
			$miltypes_syndarmee = array(1 => "offspecs", 2 => "defspecs");
			foreach ($build_syndarmee as $vl) {
				$syndupdates[$vl[rid]][$miltypes_syndarmee[$vl[miltype]]] = $vl[number];
			}
		}
		$profiler->add_mark("Zeile 730");

        // Spies
		$ausgabe .= "<br><br>Spione";
        foreach ($built_spies as $value) {
			$ausgabe .= "<br>Spy (ID:".$value[unit_id]."): ".$spystats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL(".$value[number].") vorher(".$statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}}.")/nachher(";
            $statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}} += $value{number};
			$totalspy_for_militaerq[$value{user_id}] += $value{number};		// Lï¿½ung ist nicht ganz sauber - fr den Milassi zweckmï¿½ig, allerdings werden in diesem Tick fertig gebaute Einheiten nicht beim Milloss bercksichtigt (s.o.)
			$ausgabe .= $statuses{$value{user_id}}{$spystats{$value{unit_id}}{type}} . ")";
        }
		$profiler->add_mark("Zeile 739");
        // Sciences
		$ausgabe .= "<br><br>Sciences";
        foreach ($built_sciences as $value) {
            if ($scienceses{$value{user_id}}{$value{name}}) {
                $scienceses{$value{user_id}}{$value{name}}++;
				$queries[] =("update usersciences set level=".$scienceses{$value{user_id}}{$value{name}}." where user_id=".$value{user_id}." and name='".$value{name}."'");
				if ($value{name} == "ind14") { // ind14 forschung verrechnen
					$statuses{$value{user_id}}{money} += ($ind14werte[$scienceses{$value{user_id}}{$value{name}}]);
					$twerte = $sciencestats{ind14}{gamename}."|".(pointit(($ind14werte[$scienceses{$value{user_id}}{$value{name}}])));
					$messageinserts.="(40,".$value{user_id}.",$time,'$twerte'),";// Messag schreiben
				}
            }
            else {
				// Status hier gar nicht definiert (Status war nur oben tempor?res Schleifenelement), daher bug mit den Forschungen
				if ($value{name} == "glo11") { // glo11 forschung verrechnen, diese wird nicht eingetragen
					$trand = mt_rand(0,5);
					// Wirkung: 0 - 300k credits, 1 - 15k fp, 2 - 250k energie, 3- 50k erz, 4 - 250 Ranger, 5 - 200 marines
					$twerte = $sciencestats{glo11}{gamename}."|";
					switch($trand) {
							case 0:
									$gamblevalue = 600000;
									$gamble = "money";
									$twerte.=(pointit($gamblevalue))." Credits";
								break;
							case 1:
									$gamblevalue = 30000;
									$gamble ="sciencepoints";
									$twerte.=(pointit($gamblevalue))." Forschungspunkte";
								break;
							case 2:
									$gamblevalue = 100000;
									$gamble = "metal";
									$twerte.=(pointit($gamblevalue))." Erz";
								break;
							case 3:
									$gamblevalue = 500;
									$gamble ="defspecs";
									$twerte.=(pointit($gamblevalue))." Ranger";
								break;
							case 4:
									$gamblevalue = 400;
									$gamble ="offspecs";
									$twerte.=(pointit($gamblevalue))." Marines";
								break;
							case 5:
									$gamblevalue = 500000;
									$gamble="energy";
									$twerte.=(pointit($gamblevalue))." Energie";
								break;
					}
					$statuses{$value{user_id}}{$gamble} += $gamblevalue;
					$messageinserts.="(41,".$value{user_id}.",$hourtime,'$twerte'),";// Messag schreiben
					unset($gamblevalue);
				}
				else {
					$scienceses{$value{user_id}}{$value{name}} = 1; # sonst wird bei gamble ?ber forschungsassi erst eine stunde sp?ter in auftrag gegeben
					$sciencesinserts .= "(".$value{user_id}.",'".$value[name]."',1),";
					if ($value{name} == "ind14") { // ind14 forschung verrechnen
						$statuses{$value{user_id}}{money} += $ind14werte[1];
						$twerte = $sciencestats{ind14}{gamename}."|".pointit($ind14werte[1]);
						$messageinserts.="(40,".$value{user_id}.",$hourtime,'$twerte'),";// Messag schreiben
					}
				}
            }
			if($value{name} == "ind16") {
				$syndupdates{$statuses{$value{user_id}}{rid}}{energyforschung} +=1;

			}
			elseif($value{name} == "glo12") {
				$syndupdates{$statuses{$value{user_id}}{rid}}{sabotageforschung} += 1;
			}
			elseif($value{name} == "ind15") 
			{
				// Änderung r43 (Programmierer: DragonTEC) Da Credits als Default-wert für Trade eingestellt wird erhalten alle Syndikatsmitglieder eine Benachrichtigung (id 50) das Trade nun läuft
				if( ( $syndforschungen[$statuses[$value[user_id]]['rid']][creditforschung][0] + $syndforschungen[$statuses[$value[user_id]]['rid']][creditforschung][1] + $syndforschungen[$statuses[$value[user_id]]['rid']][creditforschung][2] ) == 0)
				{
					$other_syndicate_members = assocs("select id from status where rid = ( select rid from status where id = ".$value[user_id]." ) and id <> ".$value[user_id]);
					foreach( $other_syndicate_members as $synmember )
					{
						$messageinserts.="(50,".$synmember['id'].",$hourtime,''),";
					}
					$syndforschungen[$statuses[$value[user_id]]['rid']][creditforschung][0]++;
				}
				
				$syndupdates{$statuses{$value{user_id}}{rid}}{creditforschung} += 1;
			}
			$messageinserts.="(49,".$value{user_id}.",$hourtime,'".$sciencestats[$value[name]][gamename]."|".$scienceses{$value{user_id}}{$value{name}}."'),";// Messag schreiben

			$ausgabe .= "<br>KID(".$value[user_id].") - Science(".$value[name].") - Neues Level (".$scienceses{$value{user_id}}{$value{name}}.")";
        }
		$profiler->add_mark("Zeile 816");

		if (count($syndupdates) > 0) {
			foreach ($syndupdates as $key => $value) {
				$go = 0;
				$setstring = "";
				foreach ($value as $was => $wert) {
					if (!$go) {$go = 1;}
					$setstring.="$was=$was+$wert,";
				}
				$setstring = chopp($setstring);
				if ($go) {
					$syndstring[$key] = ",".$setstring;
				}
				unset($go,$setstring);
			}
		}
		unset($syndupdates);

        // Back Military
				$ausgabe .= "<br><br>Back Military";
        foreach ($back_military as $value) {
			$ausgabe .= "<br>Mil (ID:".$value[unit_id]."): ".$milstats[$value[unit_id]][type].": Konzernid: ".$value[user_id]." -> ANZAHL (vorher/nachher) ".$value[number]." (".$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}}."/";
            //$statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} += $value{number}; decomment by Christian 17.8.10
            if ($statuses[$value['user_id']]['race'] == "nof" && $milstats{$value{unit_id}}{type} == "elites") $value['number'] = 0;
			//$totalmil_for_militaerq[$value{user_id}] += $value{number};	//by Christian 16.8.10	// Lï¿½ung ist nicht ganz sauber - fr den Milassi zweckmï¿½ig, allerdings werden in diesem Tick fertig gebaute Einheiten nicht beim Milloss bercksichtigt (s.o.)
			$ausgabe .= $statuses{$value{user_id}}{$milstats{$value{unit_id}}{type}} . ")";
        }
		// Back Transfer
		foreach ($back_transfer as $vl)	{
			$messageinserts .= "(10, ".$vl[user_id].",$hourtime, '".$statuses[$vl[receiver_id]][syndicate]."|".$statuses[$vl[receiver_id]][rid]."|".pointit($vl[number])."|".$resstats[$vl[product]][name]."'),";
			$messageinserts .= "(9, ".$vl[receiver_id].",$hourtime,'".$statuses[$vl[user_id]][syndicate]."|".$statuses[$vl[user_id]][rid]."|".pointit($vl[number])."|".$resstats[$vl[product]][name]."'),";
			$statuses[$vl[user_id]][$vl[product]] += $vl[number];
		}
		// Kriege automatisch beenden
		$profiler->add_mark("Zeile 849");

		
		$profiler->add_mark("Zeile 971");

		// Allianzen K?ndigungen
		foreach ($allianzen_kuendigungen as $ky => $vl)	{
			$allies = row("select syndikate.allianz_id,first,second,third from syndikate,allianzen where synd_id=$ky and syndikate.allianz_id=allianzen.allianz_id");
			if ($allies[0])	{
				for ($i = 1; $i <= 3; $i++) {
					if ($allies[$i] != $ky && ($allianzen_kuendigungen[$allies[$i]] or single("select count(*) from allianzen_kuendigungen where synd_id = ".$allies[$i]))) { $allies[$i] = 0; }
				}
				if ($allies[1] == $ky) {
					if ($allies[2] && $allies[3]) {
						$allies[1] = $allies[2];
						$allies[2] = $allies[3];
						$allies[3] = 0;
					}
					elseif ($allies[2]) {
						$allies[1] = $allies[2];
						$allies[2] = 0;
						$allies[3] = 0;
					}
					else {
						$allies[1] = $allies[3];
						$allies[2] = 0;
						$allies[3] = 0;
					}
				}
				elseif ($allies[2] == $ky) {
					if ($allies[1] && $allies[3]) {
						$allies[1] = $allies[1];
						$allies[2] = $allies[3];
						$allies[3] = 0;
					}
					elseif ($allies[1]) {
						$allies[1] = $allies[1];
						$allies[2] = 0;
						$allies[3] = 0;
					}
					else {
						$allies[1] = $allies[3];
						$allies[2] = 0;
						$allies[3] = 0;
					}
				}
				elseif ($allies[3] == $ky) {
					if ($allies[1] && $allies[2]) {
						$allies[1] = $allies[1];
						$allies[2] = $allies[2];
						$allies[3] = 0;
					}
					elseif ($allies[1]) {
						$allies[1] = $allies[1];
						$allies[2] = 0;
						$allies[3] = 0;
					}
					else {
						$allies[1] = $allies[2];
						$allies[2] = 0;
						$allies[3] = 0;
					}
				}
				$syndikate_namen = assocs("select synd_id,name from syndikate where synd_id in ($ky,".$allies[1].($allies[2] ? ",".$allies[2]:"").")","synd_id");
				$queries[] =("update syndikate set allianz_id=0, ally1=0, ally2=0 where synd_id=$ky");
				if ($allies[1] && $allies[2])	{
					$queries[] =("update allianzen set first=".$allies[1].",second=".$allies[2].",third=0 where allianz_id=".$allies[0]);
					$queries[] =("update syndikate set ally1=".$allies[1].",ally2=0 where synd_id=".$allies[2]);
					$queries[] =("update syndikate set ally1=".$allies[2].",ally2=0 where synd_id=".$allies[1]);
					$towncrierinserts .= "($hourtime,".$allies[1].",'Ihr ehemaliger Bündnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat ist nun nur noch mit dem Syndikat <strong>".$syndikate_namen[$allies[2]][name]." (#".$allies[2].")</strong> alliiert.',2),";
					$towncrierinserts .= "($hourtime,".$allies[2].",'Ihr ehemaliger Bündnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat ist nun nur noch mit dem Syndikat <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong> alliiert.',2),";
					$towncrierinserts .= "($hourtime,".$ky.",'Ihr Syndikat beendet die Allianz mit den Syndikaten <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong> und <strong>".$syndikate_namen[$allies[2]][name]." (#".$allies[2].")</strong>.',2),";
				}
				elseif ($allies[1])	{
					$queries[] =("update allianzen set first=0,second=0,third=0,name='' where allianz_id=".$allies[0]);
					$queries[] =("update syndikate set allianz_id=0,ally1=0,ally2=0 where synd_id=".$allies[1]);
					$towncrierinserts .= "($hourtime,".$allies[1].",'Ihr ehemaliger Bndnispartner <strong>".$syndikate_namen[$ky][name]." (#$ky)</strong> beendet die Allianz mit Ihrem Syndikat.<br>Ihr Syndikat hat keine weiteren Allianzpartner.',2),";
					$towncrierinserts .= "($hourtime,".$ky.",'Ihr Syndikat beendet die Allianz mit dem Syndikat <strong>".$syndikate_namen[$allies[1]][name]." (#".$allies[1].")</strong>.',2),";
				}
				else {
					$queries[] =("update allianzen set first=0,second=0,third=0,name='' where allianz_id=".$allies[0]);
				}
			}
		}
		$profiler->add_mark("Zeile 1052");
		// NAPS-K?ndigungen
		if ($naps_kuendigungen)	{
			$napidstring = join(",", $naps_kuendigungen);
			$queries[] =("update naps_spieler_spezifikation set gekuendigt_done=1 where napid in ($napidstring)");
			$queries[] =("delete from naps_spieler where napid in ($napidstring)");
		}
		$drausgabe.="Nach berechnen der sachen in bau + forschungen: $dr\n";

		//
        // Dividenden auszahlen
		//
		$ausgabe .= "<br>Tick: " . $hour . "| Divitimes: " . $dividendentimes . " <br>";
		if ($hour % $dividendentimes == 0) 
		{
			// Aktiendaten werden hier erst geholt, weil anderweitig noch nicht n?tig und nur alle 3 stunden n?tig
			$synaktien = assocs("select sum(number) as number,synd_id from aktien group by synd_id","synd_id"); // Aktien der einzelnen syndikate
			$privataktien = assocs("select sum(number) as number, synd_id from aktien_privat group by synd_id","synd_id");
			$aktiendaten  = assocs("select number,user_id,synd_id from aktien ORDER BY synd_id ASC");
			$privataktiendaten = assocs("select number,user_id,synd_id from aktien_privat");
			$owners = array();
			// Zuweisen der einzelnen aktien zu den spielern
			foreach ($aktiendaten as $value) 
			{
				$owners[$value[user_id]][$value[synd_id]][number] += $value[number];
			}
			
			foreach ($privataktiendaten as $value) 
			{
				$ownersprivat[$value[user_id]][$value[synd_id]][number] += $value[number];
				if (!$owners[$value[user_id]][$value[synd_id]][number]) $owners[$value[user_id]][$value[synd_id]][number] = 0;
			}
			// Schleife f?r alle Syndiakte
			$ownermessages = array();
			$checkedall = array();
			foreach ($synaktien as $synvalue) 
			{
				$synid = $synvalue[synd_id];
				$synsum = $synvalue[number];
				$betrag = $syndikate_data[$synid][dividenden];
				$betrag_energy = $syndikate_data[$synid][dividenden_energy];
				$betrag_sciencepoints = $syndikate_data[$synid][dividenden_sciencepoints];
				$betrag_metal = $syndikate_data[$synid][dividenden_metal];
				$synname = $syndikate_data[$synid][name];
				$checkedall[$synid] = 0;
				$syndikate_data[$synid][dividenden] = 0;
				$syndikate_data[$synid][dividenden_energy] = 0;
				$syndikate_data[$synid][dividenden_sciencepoints] = 0;
				$syndikate_data[$synid][dividenden_metal] = 0;
				// Aktien ausgeben oder zur?ckziehen ?
				$syndikat = $syndikate_data[$synid]; // f?r available funktion als global
				//$available = 0;
				/*
				$available = available($syndikate_data[$synid][totalland],$synsum,$privaktien[$synid]);
				if($available <= 0) {$syndikate_data[$synid][aktmod]+=1;//echo "AktmodIncrease $synid<br>";
				}
				if($available >= $syndikate_data[$synid][totalland] / 4 && $syndikate_data[$synid][aktmod] > 0) {$syndikate_data[$synid][aktmod]-=1;//echo "AktmodDecrease $synid<br>";
				}
				*/

				// Checken, welche Aktion?re mindestens 5% haben um auch wirkliche die ganzen 10% zu verteilen.
				echo "SYN: $synid";
				
				foreach ($owners as $key => $tvalues) 
				{
					list($anzahl,$prozent,$umlauf) = aktienbesitz($key,$synid,$synaktien[$synid][number],$privataktien[$synid][number],$owners[$key][$synid][number],$ownersprivat[$key][$synid][number]);
					$owners[$key][$synid][prozente] = $prozent;
					$ausgabe .= "---- Owner:$key\nAnzahl:$anzahl - Prozent:".$prozent." - Umlauf:$umlauf\n";
					/*
					pvar($synaktien[$synid][number],gloakt);
					pvar($privataktien[$synid][number],privakt);
					pvar($prozent,prozent);
					pvar($anzahl,anzahl);
					*/
					if ($prozent >= AKTIEN_DIVIDENDEN) 
					{
						$checkedall[$synid] += $tvalues[$synid]{number};
						$checkedall[$synid] += $ownersprivat[$key][$synid][number];
						$validdividendenempfaenger[$key][$synid] = 1;
					}
				}
				
				foreach ($owners as $key => $values) 
				{
					if ($validdividendenempfaenger[$key][$synid])	
					{
						// Diese Prozente werden tats?chlich ausgezahlt !
						$prozent = ($values[$synid]{number}+$ownersprivat[$key][$synid][number])  / $checkedall[$synid] * 100;
						$auszahlung = ceil($betrag * $prozent / 100);
						$auszahlung_energy = ceil($betrag_energy * $prozent / 100);
						$auszahlung_metal = ceil($betrag_metal * $prozent / 100);
						$auszahlung_sciencepoints = ceil($betrag_sciencepoints * $prozent / 100);
						$owners[$key][$synid]{prozent} = $prozent;
						$ausgabe .= "Divi: " . $auszahlung . "Credits<br>";
						$owners[$key][$synid]{dividende} = $auszahlung;
						$ausgabe .= "Divi: " . $auszahlung . "Energie<br>";
						$owners[$key][$synid]{dividende_energy} = $auszahlung_energy;
						$ausgabe .= "Divi: " . $auszahlung . "Erz<br>";
						$owners[$key][$synid]{dividende_metal} = $auszahlung_metal;
						$ausgabe .= "Divi: " . $auszahlung . "FP<br>";
						$owners[$key][$synid]{dividende_sciencepoints} = $auszahlung_sciencepoints;
						$statuses[$key][money] += $auszahlung;
						$statuses[$key][energy] += $auszahlung_energy;
						$statuses[$key][metal] += $auszahlung_metal;
						$statuses[$key][sciencepoints] += $auszahlung_sciencepoints;
					}
				}
				unset($synid,$synsum,$auszahlung,$betrag,$syndikat,$auszahlung_energy,$auszahlung_metal,$auszahlung_sciencepoints);
			}
			
			// Messagestrings erstellen:
			foreach ($owners as $key => $value) 
			{
				$divsum = 0;
				$divsum_credits =0;
				$divsum_energy = 0;
				$divsum_metal = 0;
				$divsum_sciencepoints = 0;
				foreach ($value as $tsynid => $tsyn) 
				{
					$tempsum = 0;
					if (
							(
								$tsyn[dividende] > 0 
								|| $tsyn[dividende_energy] > 0 
								|| $tsyn[dividende_metal] > 0 
								|| $tsyn[dividende_sciencepoints] > 0
							) 
							&& $validdividendenempfaenger[$key][$tsynid] == 1
						) 
					{
						$tempsum = round($tsyn[dividende]+$tsyn[dividende_energy]*$resstats[energy][value]+$tsyn[dividende_metal]*$resstats[metal][value]+$tsyn[dividende_sciencepoints]*$resstats[sciencepoints][value]);
						$divsum_credits += $tsyn[dividende];
						$divsum_energy += $tsyn[dividende_energy];
						$divsum_metal += $tsyn[dividende_metal];
						$prozent = $tsyn[prozent];
						$divsum_sciencepoints += $tsyn[dividende_sciencepoints];
						$divsum += $tempsum;
						$ownermessages[$key] .= "<i>".$syndikate_data[$tsynid][name]." (#".$tsynid."): </i><br>".(pointit($tsyn[dividende]))." Credits, ".(pointit($tsyn[dividende_energy]))." Energie, ".(pointit($tsyn[dividende_metal]))." Erz und ".(pointit($tsyn[dividende_sciencepoints]))." Forschungspunkte<br>im <b>Gesamtwert</b> von <b>".pointit($tempsum)." Handelspunkten</b> bei <i>".prozent($prozent)."%</i> der Aktien<br><br>";
						$queries[] = "INSERT into aktien_dividenden_detail (user_id, rid, time, credits, energy, metal, sciencepoints, gesamt) VALUES ($key, ".$tsynid." , ".time().", ".$tsyn[dividende].", ".$tsyn[dividende_energy].", ".$tsyn[dividende_metal].", ".$tsyn[dividende_sciencepoints].", ".$tempsum.")";
					}
				}
				if ($divsum > 0) {
					$ownermessages[$key] .= "</div><br><br><i>Gesamt: </i><b>".(pointit($divsum_credits))." Credits, ".(pointit($divsum_energy))." Energie, ".(pointit($divsum_metal))." Erz und ".(pointit($divsum_sciencepoints))." Forschungspunkte<br>im <b>Gesamtwert</b> von <b>".pointit($divsum)." Handelspunkten</b>";
					$queries[] = "INSERT into aktien_dividenden (user_id, time, credits, energy, metal, sciencepoints, gesamt) VALUES ($key , ".time().", $divsum_credits, $divsum_energy, $divsum_metal, $divsum_sciencepoints, $divsum)";
				}
				unset($divsum);
			}
			// Aktien Kategorie 4, Hier id 5
			$IDofDetails = "divis_".$time;
			$JSforDetailToggle = "<a onclick=\"var status = (document.getElementById(\'$IDofDetails\').style.display == \'none\') ? \'\' : \'none\';
									document.getElementById(\'$IDofDetails\').style.display = status;\" class=\"LinkAufTableInner\" style=\"cursor: pointer;\">Details ein-/ausblenden</a>";
			$DIVstarting = "<div id=\"$IDofDetails\" style=\"display: none\"><br>";
			//$DIVending = "</div>";
			//$howMany = count($ownermessages);
			//$countWith = 0;
			$instring = "insert into message_values (id,user_id,time,werte) values ";
			foreach ($ownermessages as $key => $value) {
				//if($countWith==0){
					$value = $JSforDetailToggle.$DIVstarting.$value;
				//}
				//if($countWith==$howMany-2){
					//$value .= $DIVending;
				//}
				//$countWith++;
				$thisinstringvalues = 1;
				$instring.="(5,$key,$hourtime,'$value'),";
			}
			// Messages verschicken
			$instring = chopp($instring);
			if($thisinstringvalues) {
				$queries[] =($instring);
			}
		}
		$profiler->add_mark("Zeile 1151");
		$buildend = getmicrotime();
        //***************************************************************//
        //                         Daten schreiben                       //
        //***************************************************************//

		$gvimaxlandstring = GVIMAXLAND;
		$beforestatusupdate = getmicrotime();
		$profiler->add_mark("Zeile 1193");
        foreach($statuses as $status) {
			// GVI Kosten
//					echo $gvimaxlandstring."maxland";

			if (FALSE && ($statuses[$status[id]][gvi] && $status[createtime] + PROTECTIONTIME <= $time && $status[alive] == 1)) {
				$gvicost = ceil(landkosten()*3);
				// Konzern rausschmeiï¿½n
				if ($statuses[$status[id]][money] < $gvicost) {
					$statuses[$status[id]][gvi] =0;
					$twerte ="Sie konnten ihren GVI Mitgliedsbeitrag nicht entrichten! Ihre Mitgliedschaft wurde deshalb gekndigt, sie mssen keine weiteren Mitgliedsbeitrï¿½e mehr entrichten und sind nicht lï¿½ger Mitglied der GVI.";
					$messageinserts.="(44,".$status[id].",$time,'$twerte'),";// Messag schreiben
				}
				// Spieler auch rausschmeiï¿½n
				elseif ($statuses[$status[id]][land] > GVIMAXLAND) {
					$statuses[$status[id]][gvi] = 0;
					$twerte ="Ihr Konzern besitzt jetzt mehr als ".$gvimaxlandstring." Hektar Land und gengt damit nicht mehr den Mitgliedskriterien der GVI. Ihre Mitgliedschaft wurde deshalb gekndigt, sie mssen keine weiteren Mitgliedsbeitrï¿½e mehr entrichten und sind nicht lï¿½ger Mitglied der GVI.";
					$messageinserts.="(44,".$status[id].",$time,'$twerte'),";// Messag schreiben
				}
				// Bezahlen
				else {
					$statuses[$status[id]][money] -=$gvicost;
				}
				unset($gvicost);
			}

			if ($status[createtime] + PROTECTIONTIME <= $time && $status[inprotection] == 'Y') {
			    $statuses[$status[id]][inprotection] = 'N';
			}

			// Partnerschaftslevel checken
			if (floor(($status[land]-1000) / 1000) - $status[partnerschaften] > 0) {
				$statuses[$status[id]][partnerschaften] = (floor(($status[land]-1000) / 1000));
				$statuses[$status[id]][partnerschaften] > 6 ? $statuses[$status[id]][partnerschaften] = 6 : 1;
			}


			if ($status[alive] == 1 && $features_generell_for_kill_decision[$status[id]] or $status[alive] == 2)	{

				$sciences = $scienceses{$status[id]};
				$partner = $partnerbonuses[$status[id]];

				$message_werte = array();
				$geb_messages = array();
				$milq_messages = array();

				for ($i = 0; $i <= 2; $i++) {



					$assistent = $status[queue_tool_priorities][$i];
					if ($assistent == 1 && ($forschungsq_users[$status[id]] or $status[alive] == 2)) {
						// Forschungsassi
						if (!$in_build_sciences[$status[id]])	{
							if ($forschungsq[$status[id]])	{
								foreach ($forschungsq[$status[id]] as $ky => $vl)	{
									if ($status[race] == "neb") {
										$nebmalus = NEB_SCIENCE_MALUS;
									}
									else {
										$nebmalus = 0;
									}
									//pvar($nebmalus,nebmalus);
									$valid = forschable($vl,$sciencestats,$sciences,$status[sciencepoints], 1);
									unset($nebmalus);
									if ($valid[0])	{
										$modifikator = $status[race] == "sl" ? 0.75 : 1;
										// Glo 15 Forschung beschleunig forschungsgeschwindigkeit um weitere 25%
										$modifikator -= $sciences{glo15} ? 0.25 : 0;
										//$modifikator -= ($sciencestats[$vl]['group'] == "ind" && $status[race] == "uic") ? 0.25 : 0; # Ausgebaut Runde 22
										$build_sciencesinserts .= "($status[id],".($hourtime + fos_duration($sciencestats[$vl][level]) * 60 * $globals{roundtime} * $modifikator).",'".$vl."'),";
										$build_sciencesinserts_logs .="($status[id],".$sciencestats[$vl][id].",$time,".($sciences[$vl][level]+1).",0,'sci'),";
										$status[sciencepoints] -= $valid[1];
										$message_werte[] = "Der Forschungsassistent hat soeben für Sie die Forschung <strong>".$sciencestats[$vl][gamename]."</strong> in Auftrag gegeben.";
										select("delete from kosttools_forschungsq where konzernid=".$status[id]." and position=$ky");
										if (($ky==1 && $forschungsq[$status[id]][2]) or ($ky==2 && $forschungsq[$status[id]][3])
											or ($ky == 3 && $forschungsq[$status[id]][4]) or ($ky==4 && $forschungsq[$status[id]][5])) {
											select("update kosttools_forschungsq set position=position-1 where konzernid=".$status[id]." and position > $ky");
										}
										break;
									}
								}
							}
						}
					}
					elseif ($assistent == 2 && $militaerq_users[$status[id]] && $status[alive] == 1) {
						// Militaerassi
						/*
								Folgende Optimierungsmï¿½lichkeiten vorhanden:
								1. Die unitstats gehen ber ALLE Militï¿½einheiten, benï¿½igt werden aber nur die der Rasse des Spielers, auf die Art wird je Spieler JEDE Mil-Einheit JEDER Rasse durchgegangen
								2.

						*/


						if ($militaerq[$status[id]]) 
						{
							$faktor1 = 1; $faktor2 = 1;
							//if ($status{race} == "uic") {$faktor1 -= 0.20;}
							if ($sciences{ind5}) {$faktor1 -= 0.20*$sciences{ind5};$faktor2 -= 0.20*$sciences{ind5};}
							if ($partner[17]) { $faktor1 -= 0.05*$partner[17]*PARTNER_EINHEITENBAUZEITBONUS; $faktor2 -= 0.05*$partner[17]*PARTNER_EINHEITENBAUZEITBONUS; } # -1h EInheitenbauzeit pro Level;
							if ($faktor1 < 0.3) $faktor1 = 0.3;
							if ($faktor2 < 0.3) $faktor1 = 0.3;
							//if ($status[race] == "uic") $faktor2-=UIC_SPIES_SPEEDBONUS;
							$buildtime_mil = $hourtime + BUILDTIME_MIL * 60 * $globals{roundtime} * $faktor1;			#Bauzeit in Sekunden fr mileinheiten
							$buildtime_spies = $hourtime + BUILDTIME_SPY * 60 * $globals{roundtime} * $faktor2;		#Bauzeit in Sekunden

							if (in_protection($status) && getServertype() == "basic") 
							{
								$buildtime_mil = $hourtime + 3600;
								$buildtime_spies = $buildtime_mil;
							}


							$totalmilitary = $totalmil_for_militaerq[$status[id]];
							$totalspies = $totalspy_for_militaerq[$status[id]];
							$max_mil = maxunits(mil);
							$max_spy = maxunits(spy);

							$costmodmil = 0;
							$costmodspies = 0;
							$unitstats_temp = $milstats;
							$spystats_temp = $spystats;
							$mil_in_build = 0;
							$spy_in_build = 0;
							$mil_to_build = array();
							$spy_to_build = array();
							$milq_position_deletes = array();


												// Bonus der Factories berechnen, falls positive Energieproduktion
												//$energyadd = energyadd($status{id}, 6);
												if ($energyadd >= 0 or true) { // positive Energiebilanzregel ab Runde 10 deaktiviert
													$factorymod = ($status{factories}/$status{land})*FACTORYBONUS;
													if ($factorymod >=FACTORYBONUS*2/10 ) {$factorymod=FACTORYBONUS*2/10 ;} // Maximal 50% Bonus von Factories
												}
												$costmodmil += $factorymod;
												unset ($factorymod);

												// Cheaper Unit production
												if  ($sciences{ind8}) 	{
													$costmodmil +=IND8BONUS/100*$sciences{ind8};
												}
												$costmodmil=(1-$costmodmil);

												// Maximal 60% Bonus
												$costmodmil < 0.4 ? $costmodmil = 0.4 : 1;

												// Cheaper Spy production
												if  ($sciences{glo3}) 	{
													$costmodspies +=GLO3BONUS/100*$sciences{glo3};
												}
												// Seccenters
												if ($energyadd >= 0 or true) { // positive Energiebilanzregel ab Runde 10 deaktiviert
													if ($status[seccenters] && $status[race] == "sl") {
														$secbonus = SECCENTERBONUS * $status[seccenters]/$status[land];
														$secbonus > 0.4 ? $secbonus = 0.4:1;
														$costmodspies += $secbonus;
													}
												}
												// Partnerbonus fr Spies
												if ($partner[13]) {
													$costmodspies += $partner[13] * SPY_PRICE_PARTNERBONUS;
												}
												$costmodspies=(1-$costmodspies);
												$costmodspies < 0.4 ? $costmodspies = 0.4 : 1;

												// Unitstats modifizieren, wenn modifikatoren vorliegen
												if ($costmodmil < 1) {
													foreach ($unitstats_temp as $temp => $value) {
														$unitstats_temp{$temp}{credits} = (int) ($unitstats_temp{$temp}{credits} * $costmodmil);
														$unitstats_temp{$temp}{minerals} = (int) ($unitstats_temp{$temp}{minerals} * $costmodmil);
														$unitstats_temp{$temp}{energy} = (int) ($unitstats_temp{$temp}{energy} * $costmodmil);
														$unitstats_temp{$temp}{sciencepoints} = (int) ($unitstats_temp{$temp}{sciencepoints} * $costmodmil);
													}
												}

												// Spystats modifizieren, wenn modifikatoren vorliegen
												if ($costmodspies < 1) {
													foreach ($spystats as $temp => $value) {
														$spystats_temp{$temp}{credits} = (int) ($spystats_temp{$temp}{credits} * $costmodspies);
														$spystats_temp{$temp}{energy} = (int) ($spystats_temp{$temp}{energy} * $costmodspies);
													}

												}


							foreach ($militaerq[$status[id]] as $ky => $vl) {
								if ($vl[type] == 1) { ## 1 == Militï¿½
									if (($unitstats_temp[$vl[unit_id]][erforschbar] == 1 && $sciences{ind7} >= 1) || $unitstats_temp[$vl[unit_id]][erforschbar] != 1) {
										if (($unitstats_temp[$vl[unit_id]][erforschbar] == 2 && $sciences{mil11} >= 1) || $unitstats_temp[$vl[unit_id]][erforschbar] != 2)	{
											if (($unitstats_temp[$vl[unit_id]][erforschbar] == 4 && $sciences{mil18} >= 1) || $unitstats_temp[$vl[unit_id]][erforschbar] != 4)	{
												if ($vl[number] * $unitstats_temp[$vl[unit_id]][credits] <= $status[money] && $vl[number] * $unitstats_temp[$vl[unit_id]][minerals] <= $status[metal] && $vl[number] * $unitstats_temp[$vl[unit_id]][energy] <= $status[energy] && $vl[number] * $unitstats_temp[$vl[unit_id]][sciencepoints] <= $status[sciencepoints]) {
													$max_mil_buyable = $max_mil - $totalmilitary - $mil_in_build;
													if ($status['race'] == "nof" && ($vl['unit_id'] == 24 or $vl['unit_id'] == 40)) $max_mil_buyable = $max_mil - $totalCarriers_safe[$status['id']];
													if ($vl[number] <= $max_mil_buyable) {
														$mil_to_build[$vl[unit_id]] += $vl[number];
														$status[money] -= $vl[number] * $unitstats_temp[$vl[unit_id]][credits];
														$status[metal] -= $vl[number] * $unitstats_temp[$vl[unit_id]][minerals];
														$status[energy] -= $vl[number] * $unitstats_temp[$vl[unit_id]][energy];
														$status[sciencepoints] -= $vl[number] * $unitstats_temp[$vl[unit_id]][sciencepoints];
														if ($status['race'] == "nof" && ($vl['unit_id'] == 24 or $vl['unit_id'] == 40)) {
															$totalCarriers_safe[$status['id']] += $vl[number];
														} else {
															$mil_in_build += $vl[number];
														}
														$milq_position_deletes[] = $ky;
													}
												}
											}
										}
									}
								}
								elseif ($vl[type] == 2) {	## 2 == Spione
									if ($vl[number] * $spystats_temp[$vl[unit_id]][credits] <= $status[money] && $vl[number] * $spystats_temp[$vl[unit_id]][energy] <= $status[energy]) {
										$max_spy_buyable = $max_spy - $totalspies - $spy_in_build;
										if ($vl[number] <= $max_spy_buyable) {
											$spy_to_build[$vl[unit_id]] += $vl[number];
											$status[money] -= $vl[number] * $spystats_temp[$vl[unit_id]][credits];
											$status[energy] -= $vl[number] * $spystats_temp[$vl[unit_id]][energy];
											$spy_in_build += $vl[number];
											$milq_position_deletes[] = $ky;
										}
									}
								}
							}
							if ($mil_to_build or $spy_to_build) {
								if ($mil_to_build) {
									foreach ($mil_to_build as $ky => $vl) {
										$build_military_inserts[] = "(".$status[id].", $vl, ".($buildtime_mil).", $ky)";
										$build_military_inserts_logs[] = "($status[id], $ky,$time, $vl, 0,'mil')";
										$milq_messages[] = "&nbsp;&nbsp;&nbsp;".pointit($vl)." ".$unitstats_temp[$ky][name];
									}
								}
								if ($spy_to_build) {
									foreach ($spy_to_build as $ky => $vl) {
										$build_spies_inserts[] = "(".$status[id].", $vl, ".($buildtime_spies).", $ky)";
										$build_spies_inserts_logs[] = "($status[id], $ky,$time, $vl, 0,'spy')";
										$milq_messages[] = "&nbsp;&nbsp;&nbsp;".pointit($vl)." ".$spystats_temp[$ky][name];
									}
								}
								$milq_position_deletes_number = count($milq_position_deletes) - 1;
								for ($o = $milq_position_deletes_number; $o >= 0; $o--) {
									$pos = $milq_position_deletes[$o];
									select("delete from kosttools_militaerq where user_id=".$status[id]." and position=$pos");
									select("update kosttools_militaerq set position=position-1 where user_id=".$status[id]." and position > $pos");
								}
								// Gebï¿½de-Message;
								$message_werte[] = "<b>Der Militärassistent hat soeben folgende Bauaufträge für Sie in Auftrag gegeben:</b><br>".join("<br>", $milq_messages);
							}
						}
					}
					elseif ($assistent == 3 && $gebaeudeq_users[$status[id]] && $status[alive] == 1) 
					{
						// Gebaeudeassi
						if ($gebaeudeq[$status[id]]) 
						{
							$totalbuildings = getallbuildings($status[id]);
							$buildtime_land = $hourtime + BUILDTIME * 60 * $globals{roundtime} - landtimemodifier();	//Bauzeit Land
							$buildtime_geb = $hourtime + BUILDTIME * 60 * $globals{roundtime} * (1 - buildtimemodifier());		//Bauzeit Gebï¿½de

							$landkosten = 0;
							$gebkosten = 0;
							$land_in_order = 0;
							$geb_in_order = 0;
							$land_to_build = 0;
							$geb_to_build = array();
							$geb_position_deletes = array();


							foreach ($gebaeudeq[$status[id]] as $ky => $vl) 
							{
								if ($vl[building_id] == 127) ## 127 == Land
								{ 
									if (!$landkosten) 
									{
										$landkosten = landkosten();
									}
									
									if ($vl[number] * $landkosten <= $status[money]) 
									{
										if (!$land_in_order) 
										{
											$land_in_order = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_id = '127' and time > $hourtime");
										}

										$max_land_buyable = $status[land] * LANDKAUFMAX;
										
										/*if ($status['race'] == "neb")
										{
											 $landkaufmaxabsolutfaktor = 1.5;
										}
										else
										{*/
											 $landkaufmaxabsolutfaktor = 1;
										//}
										
										$landkaufmaxabsolut = LANDKAUFMAX_RAW * $landkaufmaxabsolutfaktor;
										
										// DT Land Assi Check Test
										// $max_land_buyable = ha-Größe, damit nicht mehr als eigenes Land gekauft werden kann
										// $landkaufmaxabsolut = wieviel man max. kaufen kann ( normal 1000ha, neb => 1500 ha )
										
										if ($max_land_buyable > $landkaufmaxabsolut) 
										{
											$max_land_buyable = $landkaufmaxabsolut;
										}
										$max_land_buyable -= $land_in_order;

										if (getServertype() == "basic") 
										{
										      $max_land_buyable_basic_landgrenze = BASIC_MAX_LANDGRENZE - $status['land'] - $land_in_order;
										      $max_land_buyable = min($max_land_buyable, $max_land_buyable_basic_landgrenze);
										}

										if($vl[number] <= $max_land_buyable) 
										{
											$land_to_build += $vl[number];
											$geskoten_temp = $vl[number] * $landkosten;
											$status[money] -= $geskoten_temp;
											
											//select("insert into  kosttools_gebaeudeq_abgearbeitet (user_id,number,building_id,time,kosten) values ($status[id],$vl[number],$vl[building_id],$time,$geskoten_temp)");
											$land_in_order += $vl[number];
											$geb_position_deletes[] = $ky;
										}
									}
								}
								else 
								{
									if (!$gebkosten) $gebkosten = gebkosten($totalbuildings);
									echo "\n Gebkosten gleich nach: $gebkosten";
									if ($vl[number] * $gebkosten <= $status[money]) 
									{
											if (is_baubar($vl[building_id]) == '1') 
											{
												if (!$geb_in_order) $geb_in_order = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_id != '127' and time > $hourtime");
												$max_geb_buyable = $status[land] - $totalbuildings - $geb_in_order;
												if ($vl[number] <= $max_geb_buyable) 
												{
													$geb_to_build[$vl[building_id]] += $vl[number];
													//$queuemessage.= "Bauassisten: \n gebkosten: $gebkosten, number: $vl[number]\n Statusmoney: $status[money]\n";
													echo "  Gebkosten Gebassi: $gebkosten";
													$geskoten_temp = $vl[number] * $gebkosten;
													$status[money] -= $geskoten_temp;
													//$queuemessage.= "Statusmoney after: $status[money]\n";
													//select("insert into  kosttools_gebaeudeq_abgearbeitet (user_id,number,building_id,time,kosten) values ($status[id],$vl[number],$vl[building_id],$time,$geskoten_temp)");
													$geb_in_order += $vl[number];
													$geb_position_deletes[] = $ky;
												}
											}
									}
								}
							}
							if ($land_to_build or $geb_to_build) 
							{
								if ($land_to_build) 
								{
									$build_buildings_inserts[] = "('land', ".$status[id].", $land_to_build, ".($buildtime_land).", 127)"; # user_id, number, tick, building_id
									$build_buildings_inserts_logs[] = "($status[id], 127,$time, $land_to_build, 0,'building')";
									$geb_messages[] = "&nbsp;&nbsp;&nbsp;".pointit($land_to_build)." Hektar";
									$queries[] = "update stats set landexplored=landexplored+".$land_to_build." where round=$globals[round] and konzernid = ".$status{id};
								}
								if ($geb_to_build) {
									foreach ($geb_to_build as $ky => $vl) {
										$build_buildings_inserts[] = "('".$buildings[$ky][name_intern]."',".$status[id].", $vl, ".($buildtime_geb).", $ky)";
										$build_buildings_inserts_logs[] = "($status[id], $ky,$time, $vl, 0,'building')";
										$geb_messages[] = "&nbsp;&nbsp;&nbsp;".pointit($vl)." ".$buildings[$ky][name];
									}
								}
								$geb_position_deletes_number = count($geb_position_deletes) - 1;
								for ($o = $geb_position_deletes_number; $o >= 0; $o--) {
									$pos = $geb_position_deletes[$o];
									select("delete from kosttools_gebaeudeq where user_id=".$status[id]." and position=$pos");
									select("update kosttools_gebaeudeq set position=position-1 where user_id=".$status[id]." and position > $pos");
								}
								// Gebï¿½de-Message;
								$message_werte[] = "<b>Der Gebäudeassistent hat soeben folgende Bauaufträge für Sie in Auftrag gegeben:</b><br>".join("<br>", $geb_messages);
							}
						}
					}
				}
				// Money und Metal aktualisieren im groï¿½n Statusses-Array
				//$queuemessage.= "User id: $status[id]\n Uberarray money vorher: ".$statuses[$status[id]][money]."\n";
				$statuses[$status[id]][money] = $status[money];
				//$queuemessage.= "Uberarray money nacher: ".$statuses[$status[id]][money]."\n";
				$statuses[$status[id]][metal] = $status[metal];
				$statuses[$status[id]][sciencepoints] = $status[sciencepoints];
				$statuses[$status[id]][energy] = $status[energy];
				// Messageinserts fr alle Queues:
				if ($message_werte) $messageinserts .= "(44, $status[id], $hourtime, '".join("<br><br>", $message_werte)."'),";

			}
		}
		$profiler->add_mark("Zeile 1523");
		#writelog("Test 4\n",0, 1);
		$instatusupdate = getmicrotime();
		$drausgabe.="Nach Forschungsqueue und Networth Berechnung: $dr\n";
		// Schleife hier auseinander genommen zwecks performance messung
        foreach($statuses as $status) 
        {
			if ($status[alive] > 0) {
				if (($status[lastlogintime] + TIME_TILL_KILLED < $time and $status[alive] == 1 and !$features_generell_for_kill_decision[$status[id]]) 
				|| ($status["land"] == 0 and $status["createtime"] + 24*60*60 < $time and $globals["roundstatus"] > 0)) {
				  if ($game[name] != "Syndicates Testumgebung") {
					kill_den_konzern($status[id]);
					$users_deleted++;
				  }
				}
				else	{
					// Daten f?r Networthberechnung bereitstellen
					$sciences = $scienceses{$status[id]};
					$partner = $partnerbonuses[$status[id]];
					$away = $away_military_for_nw{$status[id]};
					$market = $markets[$status[id]];
					$units_killed_local = $units_killed[$status['id']];
					// Away Arrays und ScienceArrays setzen, f?r user, die noch keine eintr?ge haben - sonst holt nw das zeugs wieder
					if (!is_array($sciences)) {$sciences = array();//echo "MADE SCIECNES";
					}
					if (!is_array($away)) {$away = array();//echo "MADEE AWAY";
					}
					if (count($market) == 0): $market = 1; endif;	# Verhindert dass in der NW-Routine die Marktdaten nochmals unn?tig geholt werden!!
					// Daten f?r Networthberechnung bereitstellen
					//echo "\nCALLLED\n";
					$status[nw] = nw($status[id]);
					$status[nw_last_hour] = $status[nw];
					$nw_safe_inserts .= "(".$status[id].",".$status[rid].",".$status[nw].",".$status[land].",".$status[money].",".$status[energy].",".$status[metal].",".$status[sciencepoints].",".$safestring[$status[id]].",$hourtime),";
					if ($hour == 20) {
						$nw_featureinserts .= "(".$status[id].",".$status[nw].",".$status[land].",$time),";
						$nw_featureinserts_safe .= "(".$status[id].",".$status[nw].",".$status[land].",$time,$globals[round]),";
					}

					// Syndikats-Safe-Daten erstellen
					if ($status[rid] != 0) {
						$syndikate_data[$status[rid]][nw][] = $status[nw];
						$syndikate_data[$status[rid]][land][] = $status[land];
					}

					// 6 Stndlich die Rankings aktualisieren
					if ($hour % 6 == 0) 
					{
						$status[nw_rankings] = $status[nw];
						$status[land_rankings] = $status[land];
					}
					// Zusaetzlich bei Tick 0+1 und in den letzten 24 Ticks
					if (($time < $globals[roundstarttime] + 2*60*60 + 30) || ($time > $globals[roundendtime] - 24*60*60 - 30)) {
						$status[nw_rankings] = $status[nw];
						$status[land_rankings] = $status[land];
					}

					// Protection Bonus für Noobs
					// Seit Runde 38: Nach 1 Tick 1000 Bonus-Ranger	
					if ($status['createtime'] < $time && $status['createtime'] + 3600 >= $time) {
						if ($users[$status['id']]['startround'] == $globals['round'] && getServertype() == "basic") {
							$status['defspecs'] += 1000;
							$messageinserts .= "(44,".$status[id].",$hourtime, 'Hallo ".$users[$status['id']]['username'].",<br>um dir den Start zu erleichtern, erhältst du einmalig 1000 Ranger zur Verteidigung. <br><br>Diesen Bonus erhältst du nur in dieser Runde. Eine Runde dauert 7 Wochen. Wann die aktuelle Runde endet, kannst du links im Menü ganz unten unter dem Menüpunkt Spielwerte sehen.<br><br>Viel Spaß noch wünschen dir deine Admins,<br>Bogul und Scytale'),";
						}
					}
					
					// Bonus-Emos fürs Basic-Spieler nach 2 Tagen
					define(BONUS_EMOS_FOR_BASIC_STARTERS, 120);
					if ($status['createtime'] + 2*24*3600 - 3600 < $time && $status['createtime'] + 2*24*3600 >= $time) {
						if ($users[$status['id']]['startround'] == $globals['round'] && getServertype() == "basic") {
							$reason = BONUS_EMOS_FOR_BASIC_STARTERS." Bonus-EMOs als Willkommens-Geschenk.";
							$identifier = "pawldwl2";
							EMOGAMES_donate_bonus_emos($users[$status['id']]['emogames_user_id'],BONUS_EMOS_FOR_BASIC_STARTERS,$reason,$identifier);
							$messageinserts .= "(44,".$status[id].",$hourtime, 'Hallo ".$users[$status['id']]['username'].",<br>um dir den Start noch weiter zu erleichtern, haben wir dir gerade eben ".BONUS_EMOS_FOR_BASIC_STARTERS." Bonus-EMOs geschenkt. Damit kannst du dir z.B. den Forschungsassistenten freischalten. Damit kannst du dann ununterbrochen forschen, was insbesondere dann sehr hilfreich ist, wenn eine Forschung einmal nachts fertig werden sollte. <br>Um den Forschungsassistenten freizuschalten, klicke links im Menü auf den Menüpunkt Premium-Features.<br><br>Viel Spaß weiterhin wünschen dir deine Admins,<br>Bogul und Scytale'),";

							// Jetzt noch eine Email verschicken
							$betreff = "Premium-Feature geschenkt!";
							$username = $users[$status['id']]['username'];
							$vorname = $users[$status['id']]['vorname'];
							$nachname = $users[$status['id']]['nachname'];
							$message = "Hallo $username,\n\ndu bist jetzt schon seit zwei Tagen mit deinem eigenen Konzern am Start. Dafür an dieser Stelle schonmal recht herzlichen Glückwunsch!\nUm dir deinen Start ins erfolgreiche Syndicates-Berufsleben weiter zu erleichtern, haben wir dir ".BONUS_EMOS_FOR_BASIC_STARTERS." Bonus-EMOs geschenkt.\nDas reicht aus um z.B. einen Forschungsassistenten für die ganze Runde über freizuschalten. Mit dem Forschungsassistenten kannst du ununterbrochen forschen, was insbesondere dann sehr hilfreich ist, wenn eine Forschung einmal nachts fertig werden sollte.\nDen Forschungsassistenten kannst du freischalten, indem du im Spiel einfach auf den Menüpunkt Premium-Features klickst.\n\nViele Grüße und weiterhin viel Spaß wünscht dir\nDein Syndicates-Team";
							$email = $users[$status['id']]['email'];
							sendthemail($betreff,$message,$email,(($vorname && $nachname) ? "$vorname $nachname" : "$username"));
						}
					}

					


					// Spyactions hochsetzen
					$status[spyactions] += 1 + 2 * $sciences[glo10]; //seit R 45 gibt FSR 2 zusätzliche Spionageaktionen
					//if ($status["race"] == "sl") $status["spyactions"] += 1; SL bekommt seit Runde 45 keine zusätzliche Spionageaktion mehr pro Tick
					if ($status[spyactions] > MAXSPYOPS + $sciences[glo6] * GLO6BONUS + $sciences[glo10] * GLO10BONUS_ADD_OPS + $partner[14] * MAXSPYOPS_PARTNERBONUS ): $status[spyactions] = MAXSPYOPS + $sciences[glo6] * GLO6BONUS + $sciences[glo10]* GLO6BONUS+ $partner[14] * MAXSPYOPS_PARTNERBONUS; endif;

					// Clicks zuz?hlen:

					if ($user_heap_click_stats[$status[id]])	{
						$status[clicks] += $user_heap_click_stats[$status[id]];
					}

					// B?rsenverk?ufe auf 0 setzen wenn 0 Uhr ist

					if ($hour == 0)	{
						$status[sold] = 0;
					}

					/*echo "\n\nVariable: wardata: \n";
					print_r($wardata);
					echo "\n\nVariable: syndikate_warids \n";
					print_r($syndikate_warids);
					echo "\n\n";*/

					// eventuell in Urlaub stecken
					if ($urlaubsaktivierungen[$status[id]]) 
					{
						$status[alive] = 2;
						$towncrierinserts .= "($hourtime, ".$status[rid].", 'Der Konzern ".$status[syndicate]." ist vorübergehend nicht wettbewerbsfähig.',2),";
						$queries[] = "update options_vacation set activated_by_update = 1 where starttime = ".$urlaubsaktivierungen[$status[id]][starttime]." and user_id = ".$status[id];	
									
					}

					// Anzahl Turns hochz?hlen

					$status[turn]++;
					$updatestring = "update status set ";
					foreach ($status as $key => $value) {
						if ($value or $value == 0) {
							$updatestring.= $key."='".$value."',";
						}
					}
					$updatestring = chopp($updatestring);
					$updatestring.=" where id = ".$status{id};
					$queries[] =($updatestring);
					$updated_users++;
				}
			}
        }
        
		$profiler->add_mark("Zeile 1606");
		$profiler->end();
		$afterstatusupdate = getmicrotime();
		$drausgabe.="Nach updaten der Konzerne: $dr\n";


		$deletestart = getmicrotime();
        $queries[] =("delete from build_buildings where time <= ".$time);
        $queries[] =("delete from build_military where time <= ".$time);
        $queries[] =("delete from build_spies where time <= ".$time);
        $queries[] =("delete from build_sciences where time <= ".$time);
		$queries[] =("update build_syndarmee set done=1 where time_there <= $time and done = 0");
        $queries[] =("delete from military_away where time <= ".$time);
		$queries[] =("delete from allianzen_kuendigungen where time <= ".$time);
		$queries[] =("update transfer set finished=1 where finished=0 and time <= ".($time-60*60*RESBACKTIME));
		## Scheint so konzipiert zu sein dass transfers ein Safetable ist
        #select("delete from transfer where time <= ".($time-60*60));
		## Wollen wir wirklich Messages l?schen ?
        #select("delete from messages where time <= ".$messagedeletetime);
        #$queries[] =("delete from message_values where time <= ".$messagedeletetime." and user_id not in (".join(",", $komfortpaket_users).")");
		$queries[] =("delete from market where number < 0"); // manchmal gibt es angebote mit weniger als 0 einheiten aufm market, komisch aber wahr.
		$deleteend = getmicrotime();
        // Statustable und Sciencestable neu schreiben
        /*
        $statusprefs = rows("describe status");
        $action = "create table if not exists status2 (";
        $key = "";
        foreach ($statusprefs as $value) {
            if ($value[2] != "YES") {$null = "not null";}
            if ($value[3] == "PRI") {$pri = "primary key";}
            if ($value[3] == "MUL") {$key .= "key";}
            if ($value[4]) {$default = "default '".$value[4]."'";}
            $action.=$value[0]." ".$value[1]." ".$null." ".$pri." ".$default." ".$value[5].",";
            unset($null,$pri,$default);
        }
        $action = chopp($action);$action.=")";
        echo "ACTION: ".$action."<br><br>";
        select("$action");
        */

		$drausgabe.="Nach L?schungen der bautables: $dr\n";



		$syndstart = getmicrotime();
		

		####
		####	JObs show group cyceln
		####

		$queries[] = "update jobs set show_group=((show_group+1)%".SHOWGROUPS_COUNT.")" ;

		####
		####	Bei ï¿½derung der Aktienkursberechnung diese UNBEDINGT AUCH IN POLITIK.PHP anpassen!
		####
		
		####
		####	Players Paid verarbeiten
		####
		if (count($players_paid) > 0) {
			$artids_banned = array();
			$smsgs = array();
			$smsgheader = "
				Folgende Spieler haben in den Bau des Monumentes investiert:<br><br>
			";
			foreach ($players_paid as $key => $syn) {
				$smsg = $smsgheader;
				$tcount = 0;
				foreach ($syn as $pl_id) {
					$smsg .="<li>".$statuses[$pl_id][syndicate]."<br>";
					$tcount += BUCHUNGSBETRAG_TICK;
				}
				// ARTEFAKT FERTIG ? 
				//$in_build_artefakte[$key][invested] wird oben schon beim abziehen hochgesetzt, damit insgesamt der artefaktspreis nicht berschritten wird. tcount ist lediglich nochmal eine zusammenzï¿½lung um das update auf build_artefakte fr den invest machen zu kï¿½nen, wenn das artefakt noch nicht fertiggestellt wird
				if ($in_build_artefakte[$key][invested] >= KOSTEN_ARTEFAKT && !in_array($in_build_artefakte[$key][artefakt_id],$artids_banned)) {
					$synids_noanfaenger = singles("select synd_id from syndikate where synd_type='normal'");
					$fertigmsg = "
						Das Syndikat <b>".$syndikate_data[$key][name]." (#".$syndikate_data[$key][synd_id].")</b> hat den Bau des Monumentes <b>".$artefakte[$in_build_artefakte[$key][artefakt_id]][name]."</b> fertiggestellt!
					";
					// eine kleine Nachricht an Twitter - R4bbiT - 08.03.11
					tweet('monu_finish', array('s_name' => $syndikate_data[$key][name], 's_rid' => $syndikate_data[$key][synd_id], 'a_name' => $artefakte[$in_build_artefakte[$key][artefakt_id]][name]));
					
					// Artefakt fr Syn setzen
					$queries[] = "update syndikate set artefakt_id = ".$in_build_artefakte[$key][artefakt_id]." where synd_id = ".$key."";

					// Artefakt darf in diesem zug von keinem anderen syn mehr gebaut werden
					$artids_banned[] = $in_build_artefakte[$key][artefakt_id];

					// Alle benachrichtigem
					towncrier($synids_noanfaenger,$fertigmsg,0,3);
					
					// Alle bauauftrï¿½e lï¿½chen
					$queries[] = "delete from build_artefakte where artefakt_id = ".$in_build_artefakte[$key][artefakt_id]."";
				}
				
				
				$queries[] = "update build_artefakte set invested=invested+$tcount where synd_id=$key";
				nachricht_senden($players_paid_msgtargets[$key],44,$smsg);
			}
		}
		
		
		// Syndikats-Networth-Land-Safes-Eintragungen vorbereiten
		
		####
		####
		####
		
		foreach ($syndikate_data as $ky => $vl)	
		{
			$tempResult = syndicate_total_networth($syndikate_data[$ky]['nw'],$syndikate_data[$ky]['land']);
			$syndikate_data[$ky]['nw'] = $tempResult['nw'];
			$syndikate_data[$ky]['land'] = $tempResult['land'];
			
			//wenn die Ranking-NW und -Land berechnungen laufen auch die NW und landwerte übertragen
			if( $hour % 6 == 0 || ($time < $globals[roundstarttime] + 2*60*60 + 30) || ($time > $globals[roundendtime] - 24*60*60 - 30) )
			{
				$queries[] = "UPDATE `syndikate` SET `nw_ranking` = '" . $syndikate_data[$ky]['nw'] . "',`land_ranking` = '" . $syndikate_data[$ky]['land'] . "' WHERE `synd_id` = " . $syndikate_data[$ky]['synd_id'] . " LIMIT 1 ;";
			}

			if ($ky != "aktmod") 
			{
				if (!$syndikate_data[$ky][nw] || $syndikate_data[$ky]['nw'] == -1 ): $syndikate_data[$ky][nw] = 0; $vl[nw] = 0; endif;
				if (!$syndikate_data[$ky][land] || $syndikate_data[$ky]['land'] == -1 ): $syndikate_data[$ky][land] = 0; $vl[land] = 0; endif;
				$syndikate_data_safe_inserts .= "(".$ky.",".$syndikate_data[$ky][nw].",".$syndikate_data[$ky][land].",$hourtime),";
				
				$synd_simple_nw[] = $syndikate_data[$ky][nw];
				$synd_simple_land[] = $syndikate_data[$ky][land]; //R45
			}
		}
		
		
		
		

		sort($synd_simple_nw);
		sort($synd_simple_land); //R45
		//$ins = 1;
		//foreach ($synd_simple_nw as $vl) { echo "$ins - $vl<br>"; $ins++; }

		$anz_syndikate = count($syndikate_data);
		
		// U.a. Berechnung der Aktienkurse - R4bbiT - 24.10.10
		// Hinweis: Zeile ~450 werdenn die alten Maklergebote gekillt und die Aktienbesitze in die save-spalte geschrieben
		foreach ($syndikate_data as $ky => $vl)	{
			unset($hourkurs, $aktienkurs, $count, $sum, $pool, $diff, $num_ist, $num_makler, $makler_prozent, $mod);
			$ausgabe .= "<br><br>Networth des Syndikats: ".$vl['nw']."<br>";
			$ausgabe .= "<br><br>Land des Syndikats: ".$vl['land']."<br>";
			
			// Auslesen der alten Daten, bzw. den Logs
			$old_kurs =  single('SELECT aktienkurs FROM syndikate WHERE synd_id = '.$ky);
			$last_kurse = singles('SELECT aktienkurs FROM aktien_safekurse WHERE synd_id = '.$ky.' ORDER BY time DESC LIMIT 12');
			$mingebot = single('SELECT preis FROM aktien_gebote WHERE rid = '.$ky.' AND action = \'offer\' AND preis <= '.$old_kurs * 1.3.' and time <= '.$hourtime.' ORDER BY preis ASC LIMIT 1');
			
			// Für einen neuen Kurs müssen entweder alte Kurse gegeben sein oder eben ein aktives Angebot
			if($mingebot || $last_kurse){
				if($mingebot){
					$hourkurs = $mingebot;
				}
				else{
					$hourkurs = $old_kurs * 1.2; // Wenn es kein Gebot gab, wird der alte Kurs her genommen
				}
				$last_kurse[] = $hourkurs;
				$count = count($last_kurse);
				$sum = array_sum($last_kurse);
				$aktienkurs = floor($sum / $count);
			}
			else{
				// Dies passiert eigentl nur im 1. Tick, dann wir der Standardkurs des Syns genommen
				$hourkurs = $aktienkurs = $old_kurs;
			}
			
			$num_ist = num_aktien($ky);
			$num_makler = num_aktien($ky,2);
			$makler_prozent = ($num_ist > 0 ? (100 / $num_ist * $num_makler)/100 : 0);
			// Makler bietet Aktien von 105-95% des Preises an
			// R4bbiT - 20.03.12 - vorher 110-90% und $mod = 1.10 - $makler_prozent;
			$mod = 1.05 - ($makler_prozent / 2);
			if($mod < 0.95) $mod = 0.95;
			$num_soll = AKTIEN_BASIS + $vl['land'] * AKTIEN_PRO_LAND;
			
			// R4bbiT - 23.03.12 - Das Syn muss mind. Tag * 30 (Woche 1) + Tag * 10 (ab Woche 1) Aktien emmitieren. Sonst wird dies als Zahl genommen
			$day = ceil((get_day_time($time) - get_day_time($globals[roundstarttime])) / 86400);
			$num_soll2 = ($day > MIN_AKTIEN_DAY ? MIN_AKTIEN_DAY : $day) * MIN_AKTIEN_1;
			if($day > MIN_AKTIEN_DAY){
				$num_soll2 += ($day - MIN_AKTIEN_DAY) * MIN_AKTIEN_2;
			}
			$num_soll = max($num_soll, $num_soll2);
			$diff = $num_soll - $num_ist;
			if($diff > 0){
				select('INSERT INTO aktien_gebote (user_id, rid, number, preis, time, action)
											VALUES (0, '.$ky.', '.$diff.', '.max(round($aktienkurs * $mod), MINPREIS_MAKLER).', '.($hourtime + 60*5 + rand(AKTIEN_MINTIME * 60, AKTIEN_MAXTIME * 60)).', \'offer\')');
			}
						
			
			// nochmale Beschränkung, damit auch wirklich nichts aus dem Ruder läuft
			/*if($aktienkurs < MINDESTAKTIENKURS){
				$aktienkurs = MINDESTAKTIENKURS;
			}
			else if($aktienkurs > MAXAKTIENKURS){
				$aktienkurs = MAXAKTIENKURS;
			}*/
			
			if(!$hourkurs) $hourkurs = 0;
			if(!$aktienkurs) $aktienkurs = 0;
			// Zu guter letzt wird noch der aktuelle Kurs, NW und Land in die safetabelle geschrieben
			$queries[] = ("INSERT INTO aktien_safekurse
										(synd_id, aktienkurs, time, nw, land)
										VALUES
										(".$ky.", ".$hourkurs.", ".$hourtime.", ".$syndikate_data[$ky]['nw'].", ".$syndikate_data[$ky]['land'].")");
			
			
			$queries[] = ("update syndikate set aktienkurs = ".$aktienkurs.", aktmod=".($vl[aktmod] ? $vl[aktmod] : 0).",podenergy=podenergy+".($syndikate_data_ressourcenadd[$ky][podenergy] ? $syndikate_data_ressourcenadd[$ky][podenergy] : 0).",podmoney=podmoney+".($syndikate_data_ressourcenadd[$ky][podmoney] ? $syndikate_data_ressourcenadd[$ky][podmoney] : 0).",podmetal=podmetal+".($syndikate_data_ressourcenadd[$ky][podmetal] ? $syndikate_data_ressourcenadd[$ky][podmetal] : 0).",podsciencepoints=podsciencepoints+".($syndikate_data_ressourcenadd[$ky][podsciencepoints] ? $syndikate_data_ressourcenadd[$ky][podsciencepoints] : 0).",dividenden=".($vl[dividenden] ? $vl[dividenden] : 0).",dividenden_energy=".($vl[dividenden_energy] ? $vl[dividenden_energy] : 0).",dividenden_metal=".($vl[dividenden_metal] ? $vl[dividenden_metal] : 0).",dividenden_sciencepoints=".($vl[dividenden_sciencepoints] ? $vl[dividenden_sciencepoints] : 0).$syndstring[$ky]." where synd_id='$ky'");

			// Cronimon input daten speichern
			// Syndikatsnw
			$omnimon_names_syndikate_nw[] = make_omnimon_series_name($db,$globals['round'],'syn_nw',$ky);
			$omnimon_values_syndikate_nw[] = $syndikate_data[$ky]['nw'];
			$omnimon_times_syndikate_nw[]  = $hourtime;
			
		}
		if ($hour % $dividendentimes == 0) {
			$queries[] = "update syndikate set dividenden=0,dividenden_energy=0,dividenden_sciencepoints=0,dividenden_metal=0";
		}

		// Wird oben bereits teilweise erledigt mit Updates
		/*
        $sciencestring = "insert into usersciences (user_id,name,level) values ";
        foreach ($scienceses as $value) {
            $sciencestring.= "(".$value{user_id}.",".$value{name}.",".$value{level}."),";
        }
        $sciencestring = chopp($sciencestring);
        echo $sciencestring;
		*/

		// Wenn neue Sciences eingetragen werden m?ssen:

		if ($sciencesinserts)	{
			$sciencesinserts = chopp($sciencesinserts);
			$queries[] =("insert into usersciences (user_id, name, level) values $sciencesinserts");
		}
		if ($build_sciencesinserts)	{
			$build_sciencesinserts = chopp($build_sciencesinserts);
			$build_sciencesinserts_logs = chopp($build_sciencesinserts_logs);
			$queries[] =("insert into build_sciences (user_id,time,name) values $build_sciencesinserts");
			$queries[] = ("insert into build_logs (user_id, subject_id,time, number,action,what) values $build_sciencesinserts_logs");
		}
		if ($messageinserts)	{
			$messageinserts = chopp($messageinserts);
			$queries[] =("insert into message_values (id, user_id, time, werte) values $messageinserts");
			unset($messageinserts);
		}

		if ($mbuildstringschools) {
			$mbuildstringschools = chopp($mbuildstringschools);
			$queries[] = ("insert into build_military (unit_id,user_id,number,time) values  $mbuildstringschools");
			unset($mbuildstringschools);
		}
		/*
		if (count($syndupdates) > 0) {
			foreach ($syndupdates as $key => $value) {
				$go = 0;
				$setstring = "";
				foreach ($value as $was => $wert) {
					if (!$go) {$go = 1;}
					$setstring.="$was=$was+$wert,";
				}
				$setstring = chopp($setstring);
				if ($go) {
					select("update syndikate set $setstring where synd_id=$key");
				}
				unset($go,$setstring);
			}
		}
		unset($syndupdates);
		*/
		if ($nw_safe_inserts)	{
			$nw_safe_inserts = chopp($nw_safe_inserts);
			$queries[] =("insert into nw_safe (user_id, rid, nw, land,money,energy,metal,sciencepoints, moneyadd, metaladd, sciencepointsadd, energyproduktion, energyverbrauch, syn_moneyadd, syn_metaladd, syn_sciencepointsadd, syn_energyadd, podpointsplus, time) values $nw_safe_inserts");
		}
		if ($nw_featureinserts) {
			$nw_featureinserts = chopp($nw_featureinserts);
			$queries[] =("insert into nw_statsfeature (user_id, nw, land, time) values $nw_featureinserts");
		}
		if ($nw_featureinserts_safe) {
			$nw_featureinserts_safe = chopp($nw_featureinserts_safe);
			$queries[] =("insert into nw_statsfeature_safe (user_id, nw, land, time,round) values $nw_featureinserts_safe");
		}
		if ($syndikate_data_safe_inserts)	{
			$syndikate_data_safe_inserts = chopp($syndikate_data_safe_inserts);
			$queries[] =("insert into syndikate_data_safe (synd_id, nw, land, time) values $syndikate_data_safe_inserts");
		}
		if ($towncrierinserts)	{
			$towncrierinserts = chopp($towncrierinserts);
			$queries[] =("insert into towncrier (time,rid,message,kategorie) values $towncrierinserts");
		}
		if ($build_buildings_inserts) {
			$queries[] = "insert into build_buildings (building_name, user_id, number, time, building_id) VALUES ".join(",", $build_buildings_inserts);
			$queries[] = "insert into build_logs (user_id, subject_id,time, number,action,what) values ".join(",", $build_buildings_inserts_logs);
		}
		if ($build_spies_inserts) {
			$queries[] = "insert into build_spies (user_id, number, time, unit_id) VALUES ".join(",", $build_spies_inserts);
			$queries[] = "insert into build_logs (user_id, subject_id,time, number,action,what) values ".join(",", $build_spies_inserts_logs);
		}
		if ($build_military_inserts) {
			$queries[] = "insert into build_military (user_id, number, time, unit_id) VALUES ".join(",", $build_military_inserts);
			$queries[] = "insert into build_logs (user_id, subject_id,time, number,action,what) values ".join(",", $build_military_inserts_logs);
		}


		$drausgabe.="Nach Syndikatsupdaten und aktienberechnungen: $dr\n";
		$syndend = getmicrotime();

		$queries[] = "update status set rid = 0 where alive = 0";

    }

    //
    // Falls Roundstatus = 0 befindet sich das Spiel in der Vorbereitungsphase, es werden nur potentiell falsch eingetragene Zeiten korrigeirt
    //

    elseif ($globals{roundstatus} == 0) {
		if (is_array($user_heap_click_stats)) {
			foreach ($user_heap_click_stats as $ky => $vl)	{
				$queries[] =("update status set clicks=clicks+$vl where id=$ky");
			}
		}
    }

    //
    // Pausemodus, nur Zeiten hochsetzen
    //

    elseif ($globals{roundstatus} == 3) {
    }


    // Roundstatusunabh?ngige Dinge erledigen

    //
    // In updates table schreiben
    //

    $endtime = time();
	$ausgabe = "Ausf?hrzeit: ".($endtime-$time)."<br>" . $ausgabe;
	// echo $ausgabe;
}
else { $ausgabe .= "Seit dem letzten Update ist noch nicht gen?gend Zeit verstrichen - keine ?nderungen am Spielgeschen vorgenommen!";}

//***************************************************************//
//               Eigentliche Skriptausf?hrung Ende               //
//***************************************************************//



// DB SCHREIBEN
db_write($queries,0);

if($wardata){
	foreach ($wardata as $ky => $vl) {
		warCheckAndHandle($ky);
	}
}

// Daten an cronimon schicken

// DragonTEC: Auskommentiert, da auf live nicht vorhanden und somit ausführung von update.php nicht möglich
omniputs($omnimon_names_syndikate_nw,$omnimon_values_syndikate_nw,$omnimon_times_syndikate_nw,OMNIMON_USER_MASS);

if ($time) {
	$done_test = assocs("select * from artefakte where avaible=".$globals[round]);
	$kates=array("mill","spy","eco","all");
	if(!$done_test){
		select("update artefakte set avaible=0 where 1");
		foreach($kates as $kat){
			$ids = assocs("select artefakt_id from artefakte where type='$kat'","artefakt_id");
			$idsA = array_rand ($ids, ARTEFAKS_PER_TYPE);
			foreach($idsA as $id){
				select("update artefakte set avaible=".($globals[round])." where artefakt_id=".$id);
			}
		}
	}
}


if ($time + ZEIT_VOR_RUNDENENDE_ZU_DER_PARTNERSCHAFTSBONI_NEU_BESTIMMT_WERDEN >= $globals[roundendtime]) {
	$done_test = 0;
	$done_test = single("select count(*) from partnerschaften_settings where round = ".($globals[round]+1));
	if (!$done_test) {
	$chosen_array_indizes = array();
	$new_partners = array();
		$partner_raw = singles("select id from partnerschaften_general_settings");
		foreach($partner_raw as $val){
			$new_partners[] = $val;
		}
		/*for ($i = 1; $i <= ANZAHL_VERSCHIEDENER_PARTNERSCHAFTSBONI_SETTING; $i++) {
			$security_counter = 0;
			while (true) {
				if ($security_counter++ > 10000) { break; }
				$rand = mt_rand(0,count($partner_raw)-1);
				if (!in_array($rand, $chosen_array_indizes)) {
					$new_partners[] = $partner_raw[$rand];
					$chosen_array_indizes[] = $rand;
					break;
				}
			}
		}*/
		foreach ($new_partners as $vl) {
			select("insert into partnerschaften_settings (id, round) values ($vl, ".($globals[round]+1).")");
			echo "insert into partnerschaften_settings (id, round) values ($vl, ".($globals[round]+1).")";
		}
	}
}


//tempfix
if ($globals[roundstatus] == 1) {
	$synd = array();
	$stuff = assocs("select user_id,name,level,rid from usersciences,status where status.id=usersciences.user_id and (usersciences.name='glo12' or usersciences.name='ind15' or usersciences.name='ind16' or usersciences.name='mil15')");
	foreach ($stuff as $key) {
		$synd[$key[rid]][$key[name]][$key[level]]++;
	}
	foreach ($synd as $v => $k) {
		//echo "Synd:".$v.":".$k[ind15]."<br>";
		for($i = 1; $i<=3; $i++) {
			if (!$k[ind15][$i]) {$k[ind15][$i] = '0';}
			if (!$k[ind16][$i]) {$k[ind16][$i] = '0';}
			if (!$k[glo12][$i]) {$k[glo12][$i] = '0';}
			if (!$k[mil15][$i]) {$k[mil15][$i] = '0';}
		}
		if (!$k[ind15]) {$k[ind15] = 0;} else { ksort($k[ind15]); $k[ind15] = join("|", $k[ind15]); }
		if (!$k[ind16]) {$k[ind16] = 0;} else { ksort($k[ind16]); $k[ind16] = join("|", $k[ind16]); }
		if (!$k[glo12]) {$k[glo12] = 0;} else { ksort($k[glo12]); $k[glo12] = join("|", $k[glo12]); }
		if (!$k[mil15]) {$k[mil15] = 0;} else { ksort($k[mil15]); $k[mil15] = join("|", $k[mil15]); }

		select("update syndikate set energyforschung='".$k[ind16]."',creditforschung='".$k[ind15]."',sabotageforschung='".$k[glo12]."'"./*,synarmeeforschung='".$k[mil15]."'*/" where synd_id=".$v);
	}
}

//
//// Wenn die Runde beendet ist, hier den Stats-Table fertig machen, Honorcodes setzen, Syndikate Table umbenennen
//
if ($time >= $globals{roundendtime} && $globals{roundstatus} != 2) {
	// stats updaten
	$status = assocs("select id, nw, land from status where alive > 0", "id");
	$stats = assocs("select largestland, largestnetworth, konzernid from stats where round=".$globals[round], "konzernid");
	foreach ($status as $ky => $vl)	{
		select("update stats set lastnetworth=".$vl[nw].",lastland=".$vl[land]." where konzernid=".$vl[id]." and round=".$globals[round]);
	}

	// Unit-Preise auf 0 setzen, damit diese beim nï¿½shten Durchlauf (weiter oben) auf ihren Standard gesetzt werden kï¿½nen
	select("update military_unit_settings set current_price = 0");

	// Honorcodes erzeugen - Einzelranks
	$data = assocs("select * from stats where alive > 0 and round=".$globals[round]." and isnoob = 0 order by lastnetworth desc limit 100");
	$i = 0;
	foreach ($data as $ky => $vl)	{
		$i++;
		if ($i == 1): $honorcode = 1; endif;
		if ($i == 2): $honorcode = 2; endif;
		if ($i == 3): $honorcode = 3; endif;
		if ($i >= 4 && $i <= 10): $honorcode = 4; endif;
		if ($i >= 11 && $i <= 30): $honorcode = 5; endif;
		if ($i >= 31 && $i <= 100): $honorcode = 6; endif;
		select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$globals[round].",$honorcode,$i)");
	}

	// Mentoren bekommen 120 BONUS-EMOs (nur Classic-Server, holt aus beiden DBs die Mentoren raus, damit Doppelteintraege nicht
	// zweimal entlohnt werden
	/*define(BONUS_EMOS_FOR_MENTOREN, 120);
	$mentoren2_raw = singles("select emogames_user_id from `syndicates`.users where is_mentor > 0");
	$mentoren = array();
	foreach ($mentoren2_raw as $vl) { $mentoren[$vl] = 1; }
	$identifier = "pawldwl2";
	foreach ($mentoren as $ky => $vl) {
	  EMOGAMES_donate_bonus_emos_mentoren($ky,BONUS_EMOS_FOR_MENTOREN,BONUS_EMOS_FOR_MENTOREN.' Bonus-EMOs als Dankeschön für die Mithilfe als Mentor',$identifier);
	}*/
	
	// Honorcodes für Syndikate
	/* die Syn-Pokale werden ab jetzt immer im Newsskript verteilt, da dort schon die aufwendige berechnung stattfindet
	 * mit den Allianzen. (inok1989 01.11.2012, R64)
	$top3syns = assocs("select synd_id AS rid, nw_ranking AS nw from syndikate order by nw desc limit 3");
	
	$currentSynRank = 0;
	foreach ($top3syns as $temp) {
		
		$currentSynRank++;
		$synHonorCode = 10+$currentSynRank;
		
		if ($currentSynRank > 3) break;
		
		$playersInSyn = assocs("select * from stats where alive > 0 and round=".$globals[round]." and rid=".$temp[rid]);
		foreach ($playersInSyn as $ky => $vl) {
			select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$globals[round].",$synHonorCode,$currentSynRank)");
		}
		
		
	}
	*/

	// may_play_on_classic == 2 bedeutet, dass es das Komplettpaket (auf dem Classic) umsonst gibt (auto-freischaltung in game.php)
	// da das nur für die erste classic-runde gelten soll, werden die flags hier jetzt zurückgesetzt auf 1, falls ein konzern vorhanden war
	if (getServertype() == "classic") {
	  select("update users set may_play_on_classic = 1 where konzernid > 0 AND may_play_on_classic = 2");
	}
}

/* Befindet sich inzwischen in prepareNewRound.php
if ($time >= ($globals{roundendtime}+ROUND_FREEZETIME_DURATION*60*60) && $globals{endanzeige} != 1) {
	

	// Endrank in Stats-Table eintragen
	$stats = assocs("select * from stats where alive > 0 and round=".$globals[round]." and isnoob = 0 order by lastnetworth desc");
	for ($i = 1; $i <= count($stats); $i++) {
		select("update stats set endrank = $i where konzernid = ".$stats[$i-1]['konzernid']." and round = ".$globals['round']);
	}
	

	select("update military_unit_settings set current_price=0");
	select("update spy_settings set current_price=0");
	select("update mymenue set konzernid=0");
	 select("update globals set endanzeige=1 where round=".$globals['round']);

	//Syndikate Table umbenennen // Lief die ganze Zeit nicht, weil der Syn user keine Rechte hatte zum table altern.
	select("ALTER TABLE `syndikate` RENAME `syndikate_round_".$globals[round]."`");

	//Neuen Syndikate-Table erstellen
	select("CREATE TABLE IF NOT EXISTS `syndikate` (
  `synd_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `synd_type` set('normal','noob','noob-nonspeaker','noob-inactive') NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `president_id` smallint(11) unsigned NOT NULL DEFAULT '0',
  `allianz_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ally1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ally2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `allianzanfrage` tinyint(4) NOT NULL DEFAULT '0',
  `currency` varchar(255) NOT NULL DEFAULT 'Handelspunkte',
  `podmetal` bigint(20) NOT NULL DEFAULT '0',
  `podmoney` bigint(11) NOT NULL DEFAULT '0',
  `podenergy` bigint(20) NOT NULL DEFAULT '0',
  `podsciencepoints` bigint(20) NOT NULL DEFAULT '0',
  `board_id` smallint(11) unsigned NOT NULL DEFAULT '0',
  `mentorenboard` smallint(6) NOT NULL DEFAULT '0',
  `atwar` tinyint(1) NOT NULL DEFAULT '0',
  `maxschulden` smallint(4) NOT NULL DEFAULT '500',
  `announcement` text NOT NULL,
  `announcement_lastchangetime` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `aktienkurs` mediumint(9) unsigned NOT NULL DEFAULT '".AKTIEN_STARTKURS."',
  `aktien_pool` int(11) NOT NULL DEFAULT '0',
  `max_pool` int(11) NOT NULL DEFAULT '0',
  `min_gebot` mediumint (9) NOT NULL DEFAULT '0',
  `min_auktion` mediumint (9) NOT NULL DEFAULT '0',
  `syndikatswebseite` varchar(255) NOT NULL DEFAULT '',
  `dividenden` bigint(20) NOT NULL DEFAULT '0',
  `open` tinyint(4) NOT NULL DEFAULT '1',
  `password` varchar(255) NOT NULL DEFAULT '',
  `energyforschung` varchar(255) DEFAULT '0',
  `sabotageforschung` varchar(255) DEFAULT '0',
  `creditforschung` varchar(255) DEFAULT '0',
  `synarmeeforschung` varchar(255) DEFAULT '0',
  `aktmod` smallint(6) NOT NULL DEFAULT '0',
  `offspecs` int(11) NOT NULL DEFAULT '0',
  `defspecs` int(11) NOT NULL DEFAULT '0',
  `artefakt_id` tinyint(4) NOT NULL DEFAULT '0',
  `syndsciencestype` tinyint(4) NOT NULL DEFAULT '1',
  `dividenden_metal` bigint(20) NOT NULL,
  `dividenden_energy` bigint(20) NOT NULL,
  `dividenden_sciencepoints` bigint(20) NOT NULL,
  `nw_ranking` int(11) NOT NULL DEFAULT '0',
  `land_ranking` int(11) NOT NULL DEFAULT '0',
  `artefakt_wait` int(3) NOT NULL,
  `artefakt_store` INT(10) NOT NULL,
  PRIMARY KEY (`synd_id`),
  KEY `synd_type` (`synd_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0;
");

	// Da die KonzernID in Beiträgen und Themen auf die UserID + OFFSET setzen, damit beim erstellen wieder richtig zugeordnet werden kann (Gegenstück unter create.php zu finden)
	select("UPDATE board_messages AS m         SET m.kid = (SELECT ID FROM users WHERE konzernid = m.kid)+".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET);
	select("UPDATE board_subjects AS s         SET s.kid = (SELECT ID FROM users WHERE konzernid = s.kid)+".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET);
	select("UPDATE board_subjects AS s SET s.lastposter = (SELECT ID FROM users WHERE konzernid = s.lastposter)+".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET);
	//select("UPDATE board_subjects_new AS s     SET s.kid = (SELECT ID FROM users WHERE konzernid = s.kid)+".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET);
	//select("UPDATE board_boards_lastklick AS b SET b.kid = (SELECT ID FROM users WHERE konzernid = b.kid)+".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET);

	// Users_Konzernid auf 0 setzen
	select("update users set konzernid=0");
	
	onRoundEnd(); // zu finden in lib/subs_new.php
		      // setActiveRaces();  
		      // setUnitStandardprices();
	
	// Krawall Features löschen
	if (DELETE_K_FEATURES_AT_ROUND_END) {
		select("delete from users_koins_features");
	}
}
*/


// Updating wieder auf 0 setzen und Update reinschreiben
if (!$endtime) {$endtime = time();}
$dr = select("",1);
select("insert into updates (time,endtime,users_updated,users_deleted,roundstatus,database_requests) values ($time,$endtime,$updated_users,$users_deleted,".$globals{roundstatus}.",".($dr+1).")");
select("update globals set updating = 0 where round = ".$globals{round});
omniput($game['name']."_" . "roundstatus_".$globals['roundstatus']."__update__runtime_in_seconds", ($endtime-$time), $endtime, "emogames");
omniput($game['name']."_" . "roundstatus_".$globals['roundstatus']."__update__users_updated", $updated_users, $endtime, "emogames");
omniput($game['name']."_" . "roundstatus_".$globals['roundstatus']."__update__database_requests", ($dr+1), $endtime, "emogames");
$end = getmicrotime();



$betreff = "St?ndliches Update Runde ".$globals['round'] ." - ".$date;
#$message = $ausgabe;
$message = "Beforestatusberechnung: $beforestatusberechnung,
			 Afterstatusberechung: $afterstatusberechnung,
			  Diff:".($afterstatusberechnung-$beforestatusberechnung).
			  "\nBeforestatusupdate: $beforestatusupdate,
			  Instatusupdate: $instatusupdate,
			   Afterstatusupdate: $afterstatusupdate,
			   Diff1(before - in):".($instatusupdate-$beforestatusupdate).
			   "Diff2 (in - after):".($afterstatusupdate-$instatusupdate).
			   "\nDeletestart: $deletestart,
			 deleteend: $deleteend,
			  Diff:".($deleteend-$deletestart).
			   "\nbuildstart: $buildstart,
			 buildend: $buildend,
			  Diff:".($buildend-$buildstart).
			   "\ngetstart: $getstart,
			 getend: $getdend,
			  Diff:".($getend-$getstart).
			   "\nsyndstart: $syndstart,
			 syndend: $syndend,
			  Diff:".($syndend-$syndstart).
			   "\nDbaufrufre: $end,
			   Diff:".($end-$syndend).
			   "\nGesamt:\nStart:".$start."\nEnde:".$end."\nDiff:".
			   ($end-$start)."\n\nDBREQUESTS: $dr  - Spieler updated:$updated_users\n\n\nDetaillierte Drausgabe: $drausgabe\n\n\n$queuemessage";

echo "<br><br><h1>Skript ended, DRS: $dr</h1>";

//
// Tables Optimieren
//
#$optimizestring = "optimize table ";
#foreach ($tables as $value) {
#    if ($value != "heaptable" && $value != "sessionids_actual") {
#        $optimizestring .="`".$value."`,";
#    }
#}
#$optimizestring = chopp($optimizestring);
# serverausf?lle k?nnten hierdurch entstehen:
#select($optimizestring);
//
// Tables unlocken
//
### KEINE TABLE LOCKS MEHR
#select("unlock tables");

//
// Testweise alle Queries ausgeben
//

#echo $querystring."<br><br>";



#echo "AUSGABE:<br>$ausgabe<br>";
#echo "Drs: $dr<br>";
#echo "Date: $date<br>";
#echo "DayDate: $daydate<br>";
#echo "Hour: $hour <br>";
#echo "QUERYSTRING: $querystring<br>";
//echo "QQSTRING:".$qqstring;

//
// Mailzusammenfassung schicken
//



#$ausgabe = preg_replace("/<br>/", "\n", $ausgabe);

function writelog($text) {
	global $globals;
	static $print;
	if (func_num_args() > 0) {
		//$print .= $text;
		$print = $text;
		if (func_num_args() > 0) {
			$writelogdatei = "updatetest_$globals[round].txt";

			if (!$handle = fopen("$writelogdatei", 'a')) {
					echo "Cannot open file ($filename)";
					exit;
			}
			if (!fwrite($handle, $print)) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);
		}
	}
}

		function middlewert($marketarray) {//veraltet
			$number = 0;
			$numberprice = 0;
			foreach ($marketarray as $value) {
				$number += $value[number];
				$numberprice += $value[number]*$value[price];
			}
			$back = $numberprice / $number;
			return ((round($back))/10);
		}
		function maxchange($a, $b)	{ # Top Secret!  veraltet
			if ($a / $b < 0.9): $a = floor($b * (0.85+mt_rand(0,10)/100) * 10)/10; endif;
			if ($a / $b > 1.1): $a = ceil($b * (1.05+mt_rand(0,10)/100) * 10)/10; endif;
			return $a;
		}// maxchange($energyprice, $resstats[energy][value])


echo "\n";
?>
