<?php
include("inc/general.php");
$self = "index.php";

set_time_limit(3600);

include("../../../inc/ingame/globalvars.php");

$time = time();
$identifier = "pawldwl2";

$globals = assoc("select * from globals order by round desc limit 1");
$round = $globals[round];
echo "round: $round<br>";
if ($do != 'true') $do = 0;

// DEAKTIVIERT: Wird nun in crons/prepareNewRound.php automatisch durchgeführt
if (false && $do && !single("select count(*) from announcements where headline like 'Gewinner von Runde ".($round-2)."'")) {

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
		$synWithoutAlly = 	assocs("SELECT '0' AS isAlly, synd_id FROM syndikate_round_".$round." WHERE ally1 = 0 AND ally2 = 0");
		
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
		pvar($synWithoutAlly);
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
		$syndikatsdata = assocs("select * from syndikate_round_$round where synd_id in (".join(",", $rids).")", "synd_id");
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
        $a = "Top10:<br><br>";

        $i = 0;
        
        foreach ($stats_top_10 as $ky => $vl) {
                $i++;
                $a .= $userdata[$ky][username]." - ".$vl[syndicate]."<br>";
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


        }
        

			// Syndikatsranking
			$i = 0;
			$totalemos = 0;
			
			$SynPokal = 11;
			foreach ($syndikate_top_10 as $vl) {
						if ($vl['lastnw'] <= 0) break;
						$ky = $vl['rid'];
						$i++;
						$a .= "Syndikat $ky - ".$syndikatsdata[$ky]['name']."<BR>";
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
							select("INSERT INTO honors (user_id,round,honorcode,rank) 
								(SELECT id AS user_id, ".$round." AS round, ".$SynPokal." AS honorcode, ".$i." AS rank 
									FROM users 
									WHERE id IN (SELECT user_id FROM stats WHERE rid = ".$ky." AND round = ".$round."))");
							$SynPokal++;
						}
						
						foreach ($stats_player_top10syn_nach_syn[$ky] as $uid => $trash) {
							$spielernamen[] = "<i>".$userdata[$uid]['username']."</i>";
							$totalemos += $amount;
							EMOGAMES_donate_bonus_emos($userdata[$uid][emogames_user_id],$amount,$reason,$identifier);
						}

	
						$top10synwinnerlines .= "<tr class=body><td align=center><img src=php/images/$image.gif width=$width height=$height></td><td align=middle>$i</td><td>&nbsp;&nbsp;".$syndikatsdata[$ky]['name']." (#$ky)</td><td align=center>$amount</td><td align=right>".join (", ", $spielernamen)."</td></tr>";
	
	
			}
		/*$ticker_emos = single("select count(*) * 5 from ticker_content");
		$forAwards = ceil($ticker_emos / 6); //2*3
		//spies
		$bestSpy = assocs("select user_id, username from stats where round=$round order by nettostolen desc limit 1");
		$bestSpy = $bestSpy[0];
		$bestSpy['user_id']=single("select emogames_user_id from users where id=".$bestSpy['user_id']);
		//atckaer
		$bestAttack = assocs("select user_id, username from stats where round=$round order by attack_total_won_normal + attack_total_won_conquer desc limit 1");
		$bestAttack = $bestAttack[0];
		$bestAttack['user_id']=single("select emogames_user_id from users where id=".$bestAttack['user_id']);
		//landfarm
		$bestFarm = assocs("select user_id, username,attack_total_loss_normal + attack_total_loss_conquer from stats where round=$round order by attack_total_loss_normal + attack_total_loss_conquer desc limit 1");
		$bestFarm = $bestFarm[0];
		$bestFarm['user_id']=single("select emogames_user_id from users where id=".$bestFarm['user_id']);
		EMOGAMES_donate_bonus_emos($bestSpy['user_id'],$forAwards,"Bester Spion Runde $round",$identifier);
		EMOGAMES_donate_bonus_emos($bestAttack['user_id'],$forAwards,"Bester Angreifer Runde $round",$identifier);
		EMOGAMES_donate_bonus_emos($bestFarm['user_id'],$forAwards,"Trostpreis Runde $round",$identifier);
		
		$extra = "<br>Wie angekï¿½ndigt, zahlen wir ein Teil der durch den Ticker eingenommenen Emos an Sieger verschiedener Kategorien aus. Es gehen jeweils ".$forAwards." Emos an die folgenden Spieler: <b>".$bestSpy['username']."</b> <i>(Bester Spion)</i>, <b>".$bestAttack['username']."</b> <i>(Bester Angreifer)</i> und der Trostpreis an <b>".$bestFarm['username']."</b> <i>(Landfarm ;))</i><br><br>";
		*/
		echo $a;

			/*
        $content = "Letzte Runde konnten sich folgende Spieler einen Platz in den Top 10 zu Rundenende hin sichern. Als kleine Anerkennung fï¿½r diese doch beachtliche Leistung gibt es ein paar Bonus-EMOs :)

        ";*/

        $content = "<b>Letzte Runde konnten sich folgende Syndikate einen Platz in den Top 10 sichern und insgesamt ".pointit($totalemos)." EMOs gewinnen:</b>
		
         <table class=rand align=center cellspacing=0 cellpadding=0 width=730><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=60>Rang</td><td align=middle width=200>Syndikat</td><td align=middle width=70>Bonus-EMOs</td><td align=center width=340>Spieler</td></tr>$top10synwinnerlines</table></td></tr></table>
			<br>
        <center>Herzlichen Glückwunsch :)</center>
			<br><br>Auch wenn es fü sie keine EMO-Preise mehr gibt, wollen wir an dieser Stelle trotzdem diejenigen 10 Spieler nennen, die letzte Runde in der Einzelrangliste am besten abschnitten:<br><br>
        <table class=rand align=center cellspacing=0 cellpadding=0><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=80>Rang</td><td align=middle width=100>Spieler</td><td align=center width=220>Konzern (#Syndikatsnummer)</td></tr>$top10winnerlines</table></td></tr></table> $extra";
		echo "<br><br>CONTENT:<br>$content<br>";

		// <br><br>Wir m?chten uns au?erdem recht herzlich bei den Tutoren bedanken, die den neuen Spielern letzte Runde kr?ftig unter die Arme gegriffen haben.<br>Folgende Spieler haben sich dabei besonders hervorgetan, indem sie ihren Sch?tzlingen auf einen der ersten drei Pl?tze im Anf?nger-Syndikatsranking verholfen haben und erhalten als kleines Dankesch?n und Preis jeweils 100 Bonus-EMOs:<br><br>".join(", ", $tutoren);

        $headline = "Gewinner von Runde ".($round-2);
        select("insert into announcements (time, headline, content, poster, type) values ($time, '$headline', '$content', 'inok', 'outgame')");
} else if ($do) { 
	echo "Preise wurden bereits ausgeschüttet und das Posting wurde schon erstellt. (FUNKTION deaktiviert!!)"; 
} else {
	echo "Willst du die Preise wirklich vergeben und hast nicht wie Jannis öfters einfach 
		mal ausversehn auf den Menüpunkt geklickt, weil er so schön aussieht, dann klicke auf folgenden Link:<br>
		<br>\n\n
		<a href=\"rankemos.php?do=true\">Ausschüttung der EMOs und posten der News jetzt durchführen. ACHTUNG KEINE SICHERHEITSABFRAGE!!</a><br><br>\n\n
		(inok1989 15.10.2012)\n<br>
		<br>
		<h3>EDIT: Inzwischen wird dieses Skript zum Rundenende automatisch ausgeführt! (siehe crons/prepareNewRound.php)</h3>";
}

?>
