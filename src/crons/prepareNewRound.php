<?
##
##	Dieses Skript wirft eine neue Runde an
##
##
set_time_limit(3600);
require_once("../includes.php"); // Subfunctions laden
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once("../inc/ingame/globalvars.php"); // fr get_an_empty_syndicate() wichtig!
$time = time(); // Zeit zur skriptausfhrung als timestamp, bitte spï¿½er nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.
$microtime = getmicrotime();
$hourtime = get_hour_time($time);
mt_srand($time);

$globals = assoc("select * from globals order by round desc limit 1");
$anmeldephase = checkSummertime($globals['roundendtime'], ROUND_FREEZETIME_DURATION);
if ($anmeldephase-600 <= $time && $globals['roundstatus'] == 2 && $globals['endanzeige'] != 1) {
	// Hier werden 600 Sekunden abgezogen damit auch wenige Sekunden Verschiebung keine Rolle spielen
	
	//###################################################
	// Alte Runde abschließen
	//###################################################
	
	// Endrank in Stats-Table eintragen
	$stats = assocs("select * from stats where alive > 0 and round=".$globals['round']." and isnoob = 0 order by lastnetworth desc");
	for ($i = 1; $i <= count($stats); $i++) {
		$queries[] = "update stats set endrank = $i where konzernid = ".$stats[$i-1]['konzernid']." and round = ".$globals['round'];
	}
	
	$queries[] = "update military_unit_settings set current_price=0";
	$queries[] = "update spy_settings set current_price=0";
	$queries[] = "update mymenue set konzernid=0";
	$queries[] = "update globals set endanzeige=1 where round=".$globals['round'];

	//Syndikate Table umbenennen // Lief die ganze Zeit nicht, weil der Syn user keine Rechte hatte zum table altern.
	$queries[] = "ALTER TABLE `syndikate` RENAME `syndikate_round_".$globals['round']."`";

	//Neuen Syndikate-Table erstellen
	$queries[] = "CREATE TABLE IF NOT EXISTS `syndikate` (
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
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0;";
	
	onRoundEnd(); // zu finden in lib/subs_new.php
		      // setActiveRaces();  
		      // setUnitStandardprices();
	
	// Krawall Features löschen
	if (DELETE_K_FEATURES_AT_ROUND_END) {
		$queries[] = "delete from users_koins_features";
	}
	
	
	//###################################################
	// Wirft eine neue Runde an
	//###################################################
	
	echo "Starte New-Round Script für Rundenstart (Inoks Version)..\n";
	writelog("Starte New-Round Script für Rundenstart (Inoks Version)..\n");
	
	
	writelog("\n----------\nNeue Zeiten berechnen..\n");
	
	//pvar($globals);
	// Von Mi 20:00 bis Do 20:00
	$anmeldephase = checkSummertime($globals['roundendtime'], ROUND_FREEZETIME_DURATION);
	// Von Do 20:00 bis So 14:00 sind es 2d und 18h
	$roundstarttime = checkSummertime($anmeldephase, ROUND_ANMELEPHASE_DURATION);
	// zu den 49d, 6h ist eine Runde nun von Mi 14:00 bis So 14:00 4d länger
	$roundendtime = checkSummertime($roundstarttime, ROUND_AKTIV_DURATION);
	
	//echo "Anmeldephase: ".date($anmeldephase)."\nRundenstart: ".date($roundstarttime)."\n Rundenende: ".date($roundendtime);
	//echo "Freeze: ".ROUND_FREEZETIME_DURATION."\nAnmeldephase: ".ROUND_ANMELEPHASE_DURATION."\n Runde aktiv: ".ROUND_AKTIV_DURATION; 
	
	writelog("Neuer Rundenstart ist am ".date("d. M. Y, H:i:s", $roundstarttime)."\n");
	writelog("Rundenende ist am ".date("d. M. Y, H:i:s", $roundendtime)."\n");
	
	//###################################################
	// News mit den Gewinnern raushauen
	//###################################################
	
	// Früher public/php/admin_new/rankemos.php
	writelog("\n----------\nZahle Emos aus, bestimme Sieger, News posten..\n");
	
	$round = $globals['round'];
	writelog("Runde (intern) ".$round." abschließen ...\n");
	
	$identifier = "pawldwl2";
	
	if (!single("select count(*) from announcements where headline like 'Gewinner von Runde ".($round-2)."'")) {

        // TOP 10 der letzten Runde rausholen
        $stats_top_10 = assocs("select * from stats where round = $round and alive > 0 order by lastnetworth desc limit 10", "user_id");
        // User_Ids in $ids speichern
        $ids = array();
        foreach ($stats_top_10 as $ky => $vl) {
                $ids[] = $ky;
        }

		// TOP 5 Syndikate
		// geht nicht mehr wenn nur die 10 besten spieler zaehlen 
		// $syndikate_top_10 = assocs("select rid, sum(lastnetworth) as lastnw from stats where round = $round and alive > 0 group by rid order by lastnw desc limit 10", "rid");

		// new 13. April 2010
		$players_raw = assocs("select rid, lastnetworth as lastnw from stats where round = $round and alive > 0 order by lastnw desc");
		$NUM_PLAYERS_THAT_COUNT = USERS_USED_FOR_RANKING; //runde 52
		$syndikate_count = array();
		foreach ($players_raw as $player) {
		  if ($syndikate_count[$player['rid']]++ < $NUM_PLAYERS_THAT_COUNT) {

		    //echo $syndikate_count[$player['rid']]."<br>";
		    $syndikate_nw[$player['rid']] += $player['lastnw'];
		  }
		}
		
		// Nach neuer Regelung: DurchschnittsNW der Ally 14.10.2012 (inok1989)
		
		$allies = 		assocs("SELECT '1' AS isAlly, allianz_id, first, second, third FROM allianzen");
		$synWithoutAlly = 	assocs("SELECT '0' AS isAlly, synd_id FROM syndikate WHERE ally1 = 0 AND ally2 = 0");
		
		// NW bestimmen
		for($i = 0; $i < count($synWithoutAlly); $i++) {
			$synWithoutAlly[$i]['lastnw'] = $syndikate_nw[$synWithoutAlly[$i]['synd_id']];
		}
		foreach($allies as $vl) { // DurchschnittsNW zählt!
			$synWithoutAlly[$i] = $vl;
			$anzahl = 1;
			if ($vl['second'] != 0) $anzahl++;
			if ($vl['third'] != 0) $anzahl++;
			$synWithoutAlly[$i]['lastnw'] = floor(($syndikate_nw[$vl['first']] + $syndikate_nw[$vl['second']] + $syndikate_nw[$vl['third']]) / $anzahl);
			// Größtes Syn an Position 'first' (ACHTUNG 3. Syn einer Ally nicht beachtet, da aktuell nicht verfügbar)
			if ($syndikate_nw[$vl['first']] <= $syndikate_nw[$vl['second']]) {
				$temp = $vl['first'];
				$vl['first'] = $vl['second'];
				$vl['second'] = $temp;
			}
			$i++;
		}
		// nach NW sortieren
		$synWithoutAlly = array_reverse(sort2DimInteger('lastnw', $synWithoutAlly));
		
		$old = 0;
		$new = 0;
		$syndikate_top_10 = array();
		$amounts = array(240, 120, 70, 50, 40, 30, 20, 15, 10, 5, 0);
		while(count($syndikate_top_10) < 10 && $new < 10) {
			if ($synWithoutAlly[$old]['isAlly']) {
				$syndikate_top_10[$new] = $synWithoutAlly[$old];
				$syndikate_top_10[$new]['rid'] = $synWithoutAlly[$old]['first']; 
				$anzahl = 1;
				$amount = $amounts[$new];
				if ($vl['second'] != 0) {
					$new++;
					$anzahl++;
					$amount += $amounts[$new];
					$syndikate_top_10[$new] = $synWithoutAlly[$old];
					$syndikate_top_10[$new]['rid'] = $synWithoutAlly[$old]['second'];
				}
				if ($vl['third'] != 0) {
					$new++;
					$anzahl++;
					$amount += $amounts[$new];
					$syndikate_top_10[$new] = $synWithoutAlly[$old];
					$syndikate_top_10[$new]['rid'] = $synWithoutAlly[$old]['third'];
				}
				// Ausschüttung pro Spieler berechnen
				$amount = $amount / $anzahl + (($amount % $anzahl) > 0 ? 1 : 0);
				$syndikate_top_10[$new]['amount'] = $amount;
				if ($vl['second'] != 0) {
					$syndikate_top_10[$new-1]['amount'] = $amount;
				}
				if ($vl['third'] != 0) {
					$syndikate_top_10[$new-2]['amount'] = $amount;
				}
			} else {
				if ($synWithoutAlly[$old] == NULL) break;
				$syndikate_top_10[$new] = $synWithoutAlly[$old];
				$syndikate_top_10[$new]['rid'] = $synWithoutAlly[$old]['synd_id'];
				$syndikate_top_10[$new]['amount'] = $amounts[$new];
			}
			$new++;
			$old++;
		}
		
		//pvar($syndikate_top_10);
		// new 13. April 2010 ENDE

		$rids = array();
		foreach ($syndikate_top_10 as $ky => $vl) {
			$rids[] = $vl['rid'];
		}
		$syndikatsdata = assocs("select * from syndikate where synd_id in (".join(",", $rids).")", "synd_id");
		$stats_player_top10syn = assocs("select * from stats where round = $round and alive > 0 and rid in (".join(",", $rids).")", "user_id");
		foreach ($stats_player_top10syn as $ky => $vl) {
					$ids[] = $ky;
					$stats_player_top10syn_nach_syn[$vl['rid']][$ky] = $vl;
		}
        
		/*
        // Gucken welche Noobsyns auf den ersten drei Pl?tzen gelandet sind
        $top_3_noobsyns = assocs("select rid, sum(lastnetworth) as lastnetworth from stats where round = $round and alive > 0 and isnoob = 1 group by rid order by lastnetworth desc limit 3", "rid");
        // Die Mentorensyns dieser Syndikate ermitteln
        $count = 0;
        foreach ($top_3_noobsyns as $ky => $vl) {
                $count++;
                $mentoren_syns_3_noobsyns[] = single("select mentorenboard from syndikate".($round != $globals[round] ? "_round_$round":"")." where synd_id = $ky");
                echo "Top Noobsyn $count: #$ky<br>";
        }


        $mentoren_syns_3_noobsyns = array(18, 3, 66);


        // Und schlie?lich die Spieler dieser Syndikat ermitteln
        $mentoren_top_3_noobsyns = assocs("select * from stats where round = $round and alive > 0 and rid in (".join(",", $mentoren_syns_3_noobsyns).") and isnoob = 0", "user_id");
        //Und die User_Ids dieser Spieler dem ids-Array hinzuf?gen
        foreach ($mentoren_top_3_noobsyns as $ky => $vl) {
                $ids[] = $ky;
        }*/
        // Und nun die Userdaten holen:
        $userdata = assocs("select * from users where id in (".join(",", $ids).")", "id");

        // Test-Ausgabe:
        writelog("Top10:\n\n");

        $i = 0;
        
        foreach ($stats_top_10 as $ky => $vl) {
                $i++;
                writelog($userdata[$ky][username]." - ".$vl[syndicate]."\n");
                $reason = "Rang $i in Runde ".($round-2)." bei Syndicates";
                $amount = 500 - $i * 20;
                //EMOGAMES_donate_bonus_emos($userdata[$ky][emogames_user_id],$amount,$reason,$identifier);

                $image = "medaillegold";
                $width = 25;
                $height = 35;
                if ($i == 1) $image = "pokalgold";
                if ($i == 2) $image = "pokalsilber";
                if ($i == 3) $image = "pokalbronze";
                if ($i <= 3) { $width = 35; $height = 66; }

                $top10winnerlines .= "<tr class=body><td align=center><img src=php/images/$image.gif width=$width height=$height></td><td align=middle>$i</td><td>&nbsp;&nbsp;".$userdata[$ky][username]."</td><td align=right>".$vl[syndicate]." (#".$vl[rid].") &nbsp;&nbsp;</td></tr>";
				
				// Pokale werden tatsächlich in update.php vergeben

        }
        

			// Syndikatsranking
			$i = 0;
			$totalemos = 0;
			
			$SynPokal = 11;
			foreach ($syndikate_top_10 as $vl) {
						if ($vl['lastnw'] <= 0) break;
						$ky = $vl['rid'];
						$i++;
						writelog("Syndikat $ky - ".$syndikatsdata[$ky]['name']."\n");
						$reason = "Syndikatsrang $i in Runde ".($round-2)." bei Syndicates";
						$amount = $vl['amount'];
						
						
	
						$image = "medaillebronze";
						$width = 25;
						$height = 35;
						if ($i == 1) $image = "pokalgold";
						if ($i == 2) $image = "pokalsilber";
						if ($i == 3) $image = "pokalbronze";
						if ($i == 4) $image = "medaillegold";
						if ($i == 5) $image = "medaillesilber";
						if ($i == 6) $image = "medaillesilber";
						if ($i <= 3) { $width = 35; $height = 66; }

						$spielernamen = array();
						
						if ($i <= 3) {
							// Verteilung der Pokale an das jeweilige Syndikat (nur Top 3)
							$queries[] = "INSERT INTO honors (user_id,round,honorcode,rank) 
								(SELECT id AS user_id, ".$round." AS round, ".$SynPokal." AS honorcode, ".$i." AS rank 
									FROM users 
									WHERE id IN (SELECT user_id FROM stats WHERE rid = ".$ky." AND round = ".$round."))";
							$SynPokal++;
						}
						
						foreach ($stats_player_top10syn_nach_syn[$ky] as $uid => $trash) {
							$spielernamen[] = "<i>".$userdata[$uid]['username']."</i>";
							$totalemos += $amount;
							EMOGAMES_donate_bonus_emos($userdata[$uid][emogames_user_id],$amount,$reason,$identifier);
						}

	
						$top10synwinnerlines .= "<tr class=body><td align=center><img src=php/images/$image.gif width=$width height=$height></td><td align=middle>$i</td><td>&nbsp;&nbsp;".$syndikatsdata[$ky]['name']." (#$ky)</td><td align=center>$amount</td><td align=right>".join (", ", $spielernamen)."</td></tr>";
	
	
			}

        $content = "<b>Letzte Runde konnten sich folgende Syndikate einen Platz in den Top 10 sichern und insgesamt ".pointit($totalemos)." EMOs gewinnen:</b>
		
         <table class=rand align=center cellspacing=0 cellpadding=0 width=730><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=60>Rang</td><td align=middle width=200>Syndikat</td><td align=middle width=70>Bonus-EMOs</td><td align=center width=340>Spieler</td></tr>$top10synwinnerlines</table></td></tr></table>
			<br>
        <center>Herzlichen Glückwunsch :)</center>
			<br><br>Auch wenn es fü sie keine EMO-Preise mehr gibt, wollen wir an dieser Stelle trotzdem diejenigen 10 Spieler nennen, die letzte Runde in der Einzelrangliste am besten abschnitten:<br><br>
        <table class=rand align=center cellspacing=0 cellpadding=0><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=80>Rang</td><td align=middle width=100>Spieler</td><td align=center width=220>Konzern (#Syndikatsnummer)</td></tr>$top10winnerlines</table></td></tr></table> $extra";
		writelog("CONTENT:\n\n".$content."\n");

        $headline = "Gewinner von Runde ".($round-2);
        select("insert into announcements (time, headline, content, poster, type) values ($time, '$headline', '$content', 'inok', 'outgame')");
	} else { 
		writelog("Preise wurden bereits ausgeschüttet und das Posting wurde schon erstellt\n"); 
	}
	
	
	// Ab hier startet im Prinzip die neue Runde
	writelog("\n----------\nTabellen der alten Runde leeren\n"); 
	
	$queries[] = "TRUNCATE `admin_konzernimages_approved`";
	$queries[] = "TRUNCATE `aktien`";
	$queries[] = "TRUNCATE `aktienlogs`";
	$queries[] = "TRUNCATE `aktien_privat`";
	$queries[] = "TRUNCATE `aktien_privatlogs`";
	$queries[] = "TRUNCATE `aktien_dividenden`";
	$queries[] = "TRUNCATE `aktien_dividenden_detail`";
	$queries[] = "TRUNCATE `aktien_gebote`";
	$queries[] = "TRUNCATE `aktien_logs`";
	$queries[] = "TRUNCATE `aktien_safekurse`";
	$queries[] = "TRUNCATE `allianzen`";
	$queries[] = "TRUNCATE `allianzen_anfragen`";
	$queries[] = "TRUNCATE `attacklogs`";
	
	// Foren müssen speziell behandelt werden, da von Gruppen Themen übernommen werden (siehe unten)
	
	$queries[] = "TRUNCATE `board_qa_messages`";
	$queries[] = "TRUNCATE `board_qa_subjects`";
	$queries[] = "TRUNCATE `boerse_buffer`";
	$queries[] = "TRUNCATE `build_artefakte`";
	$queries[] = "TRUNCATE `build_buildings`";
	$queries[] = "TRUNCATE `build_logs`";
	$queries[] = "TRUNCATE `options_reset`";
	$queries[] = "TRUNCATE `politik_synfus`";
	$queries[] = "TRUNCATE `sessionids_safe`";
	$queries[] = "TRUNCATE `sids_safe`";
	$queries[] = "TRUNCATE `build_military`";
	$queries[] = "TRUNCATE `noob_stage_finished`";
	$queries[] = "TRUNCATE `build_sciences`";
	$queries[] = "TRUNCATE `build_spies`";
	$queries[] = "TRUNCATE `build_syndarmee`";
	$queries[] = "TRUNCATE `codelogs`";
	$queries[] = "TRUNCATE `heaptable`";
	$queries[] = "TRUNCATE `heaptable2`";
	$queries[] = "TRUNCATE `jobs`";
	$queries[] = "TRUNCATE `jobs_logs`";
	$queries[] = "TRUNCATE `kosttools_forschungsq`";
	$queries[] = "TRUNCATE `kosttools_gebaeudeq`";
	$queries[] = "TRUNCATE `kosttools_militaerq`";
	$queries[] = "TRUNCATE `lager_buffer`";
	$queries[] = "TRUNCATE `lagerlogs`";
	$queries[] = "TRUNCATE `losslogs`";
	$queries[] = "TRUNCATE `market`";
	$queries[] = "TRUNCATE `market_gebote`";
	$queries[] = "TRUNCATE `market_gebote_logs`";
	$queries[] = "TRUNCATE `marketlogs`";
	$queries[] = "TRUNCATE `marketpricelog`";
	$queries[] = "TRUNCATE `message_values`";
	$queries[] = "TRUNCATE `messages`";
	$queries[] = "TRUNCATE `military_away`";
	$queries[] = "TRUNCATE `mod_profiler_marks`";
	$queries[] = "TRUNCATE `mod_profiler_runs`";
	$queries[] = "TRUNCATE `mod_referrer_temp`";
	$queries[] = "TRUNCATE `mysql_errors`";
	$queries[] = "TRUNCATE `naps_spieler`";
	$queries[] = "TRUNCATE `naps_spieler_spezifikation`";
	$queries[] = "TRUNCATE `notes`";
	$queries[] = "TRUNCATE `nw_safe`";
	$queries[] = "TRUNCATE `nw_statsfeature`";
	$queries[] = "TRUNCATE `nw_statsfeature_safe`";
	$queries[] = "TRUNCATE `options_defect`";
	$queries[] = "TRUNCATE `options_konzerndelete`";
	$queries[] = "TRUNCATE `options_pwsend`";
	$queries[] = "TRUNCATE `options_vacation`";
	$queries[] = "TRUNCATE `partnerschaften`";
	$queries[] = "TRUNCATE `politik_kick`";
	$queries[] = "TRUNCATE `polls`";
	$queries[] = "TRUNCATE `polls_options`";
	$queries[] = "TRUNCATE `prepare_login`";
	$queries[] = "TRUNCATE `reported`";
	$queries[] = "TRUNCATE `ressources`";
	$queries[] = "TRUNCATE `sessionids_actual`";
	$queries[] = "TRUNCATE `sessionids_admin`";
	$queries[] = "TRUNCATE `settings`";
	$queries[] = "TRUNCATE `sids`";
	$queries[] = "TRUNCATE `spylogs`";
	$queries[] = "TRUNCATE `spylogs_berichte`";
	$queries[] = "TRUNCATE `syndikate_data_safe`";
	$queries[] = "TRUNCATE `syndikate_wechsel`";
	$queries[] = "TRUNCATE `status`";
	$queries[] = "TRUNCATE `syndikate`";
	$queries[] = "TRUNCATE `towncrier`";
	$queries[] = "TRUNCATE `transfer`";
	$queries[] = "TRUNCATE `usersciences`";
	$queries[] = "TRUNCATE `users_votes`";
	$queries[] = "TRUNCATE `wars`";
	$queries[] = "TRUNCATE `ticker_content`";	
	
	// Spezielle Behandlung da Beiträge aus Gruppen übernommen werden müssen
	$queries[] = "TRUNCATE `board_boards_lastklick`";	
	$queries[] = "TRUNCATE `board_subjects_new`";		
	
	// Runde erst eintragen und dann Messages retten, damit auf jeden Fall das Skript korrekt endet
	$queries[] = "INSERT INTO globals (roundstarttime, roundendtime) VALUES (".$roundstarttime.", ".$roundendtime.")";
	// Users_Konzernid auf 0 setzen
	$queries[] = "UPDATE users SET konzernid=0"; 
	
	//$konzern_user_id = assocs("SELECT id AS user_id, konzernid FROM users WHERE konzernid != 0");
	
	$queries[] = "DELETE FROM board_subjects WHERE bid < ".BOARD_ID_OFFSET_GRUPPEN;
	$queries[] = "DELETE FROM board_messages " .
			"WHERE tid NOT IN (SELECT tid FROM board_subjects)";
	
	// Da die KonzernID in Beiträgen und Themen auf die UserID + OFFSET setzen, damit beim erstellen wieder richtig zugeordnet werden kann (Gegenstück unter create.php zu finden)
	$queries[] = "UPDATE board_subjects
			SET kid = kid + ".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET.", 
				lastposter = lastposter + ".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET;
	$queries[] = "UPDATE board_messages AS m SET m.kid = m.kid + ".BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET; // (SELECT ID FROM users WHERE konzernid = m.kid)
	
	$number = 0;
	writelog("\n----------\nSQL Statements starten..\n");
	foreach ($queries as $vl) 
	{
		$number++;
		$meldung = select($vl);
		writelog("$number: $meldung - $vl"."\n" , "1");
	}
	
	$endtime = time();
	$endmicrotime = getmicrotime();
	
	$endmessage = "Skriptlaufzeit von ".date("d. M. Y, H:i:s", $time)." bis ".date("d. M. Y, H:i:s", $endtime).".\nExakte Laufzeit: ".(round(($endmicrotime-$microtime)*1000)/1000)."s.\nInsgesamt wurden $dr Datenbankaufrufe getätigt.\nDas entspricht ".(round($dr/(round(($endmicrotime-$microtime)*1000)/1000)*1000)/1000)." Datenbankaufrufen pro Sekunde.\n";
	
	//####################################################
	// ENDE NEUES SCRIPT
	//###################################################
	
    $globals = assoc("select * from globals order by round desc limit 1");
    writelog("$endmessage","","1");
}
else 
{
  if ($globals[roundstatus] == 2 && $globals[roundendtime] - 1 * 60 * 60 <= $time && $globals[roundendtime] + 1 * 60 * 60 >= $time) 
	{
			$betreff = "Anmeldephasenskript gelaufen ".$argv[1];
			$message = $globals[roundstarttime]." No Text argv1:".$argv[1];
			$email = "admin@domain.de";
			$to = "admin";
			sendthemail($betreff, $message, $email, $to);
	}
}

function writelog($text) 
{
	global $globals;
	static $print;
	if (func_num_args() > 0) {
		$print .= $text;
		//echo $text;
		if (func_num_args() > 2) {
			$writelogdatei = "prepareround_$globals[round].txt";

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


?>