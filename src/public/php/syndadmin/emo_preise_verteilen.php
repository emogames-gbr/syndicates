<?php
require("../../../includes.php");
connectdb();
set_time_limit(3600);


$time = time();
$identifier = "pawldwl2";

$globals = assoc("select * from globals order by round desc limit 1");
$round = $globals[round]-1;
echo "round: $round<br>";

if (!single("select count(*) from announcements where headline like 'Gewinner von Runde ".($round-2)."'")) {

        // TOP 10 der letzten Runde rausholen
        $stats_top_10 = assocs("select * from stats where round = $round and alive > 0 order by lastnetworth desc limit 10", "user_id");
        // User_Ids in $ids speichern
        $ids = array();
        foreach ($stats_top_10 as $ky => $vl) {
                $ids[] = $ky;
        }

		// TOP 5 Syndikate
		// geht nicht mehr wenn nur die 10 besten spieler zaehlen // $syndikate_top_10 = assocs("select rid, sum(lastnetworth) as lastnw from stats where round = $round and alive > 0 group by rid order by lastnw desc limit 10", "rid");

		// new 13. April 2010
		$players_raw = assocs("select rid, lastnetworth as lastnw from stats where round = $round and alive > 0 order by lastnw desc");
		$NUM_PLAYERS_THAT_COUNT = 10;
		$syndikate_count = array();
		foreach ($players_raw as $player) {
		  if ($syndikate_count[$player['rid']]++ < $NUM_PLAYERS_THAT_COUNT) {

		    //echo $syndikate_count[$player['rid']]."<br>";
		    $syndikate_nw[$player['rid']] += $player['lastnw'];
		  }
		}
		arsort($syndikate_nw);
		$i = 0;
		$syndikate_top_10 = array();
		foreach ($syndikate_nw as $syn_id => $nw) {
		  if ($i < 10) {
		    $syndikate_top_10[$syn_id] = array('rid' => $syn_id, 'lastnw' => $nw);
		  }
		  $i++;
		}
		//pvar($syndikate_top_10);
		// new 13. April 2010 ENDE

			$rids = array();
			foreach ($syndikate_top_10 as $ky => $vl) {
				$rids[] = $ky;
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
			foreach ($syndikate_top_10 as $ky => $vl) {
						$i++;
						$a .= "Syndikat $ky - ".$syndikatsdata[$ky]['name']."<BR>";
						$reason = "Syndikatsrang $i in Runde ".($round-2)." bei Syndicates";
						$amounts = array(0, 240, 120, 70, 50, 40, 30, 20, 15, 10, 5);
						$amount = $amounts[$i];
	
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
						
						foreach ($stats_player_top10syn_nach_syn[$ky] as $uid => $trash) {
							$spielernamen[] = "<i>".$userdata[$uid]['username']."</i>";
							$totalemos += $amount;
							EMOGAMES_donate_bonus_emos($userdata[$uid][emogames_user_id],$amount,$reason,$identifier);
						}

	
						$top10synwinnerlines .= "<tr class=body><td align=center><img src=php/images/$image.gif width=$width height=$height></td><td align=middle>$i</td><td>&nbsp;&nbsp;".$syndikatsdata[$ky]['name']." (#$ky)</td><td align=center>$amount</td><td align=right>".join (", ", $spielernamen)."</td></tr>";
	
	
			}
	/*
        $a .= "<br><br><br>Top 3 Noobsyns:<br><br>";

        $ompf = 0;
        foreach ($mentoren_top_3_noobsyns as $ky => $vl) {
                $ompf++;
                $a .= ($ompf)." - ".$userdata[$ky][username]." - ".$vl[syndicate]."<br>";
                $amount = 100;
                $reason = "Tutor eines Top-3-Anf?ngersyndikats in Runde ".($round-2);
                //EMOGAMES_donate_bonus_emos($userdata[$ky][emogames_user_id],$amount,$reason,$identifier);
                $tutoren[] = "<b>".$userdata[$ky][username]."<b>";
        }

        echo "<br><br>";
*/
        echo $a;

			/*
        $content = "Letzte Runde konnten sich folgende Spieler einen Platz in den Top 10 zu Rundenende hin sichern. Als kleine Anerkennung für diese doch beachtliche Leistung gibt es ein paar Bonus-EMOs :)

        ";*/

        $content = "<b>Letzte Runde konnten sich folgende Syndikate einen Platz in den Top 10 sichern und insgesamt ".pointit($totalemos)." EMOs gewinnen:</b>

         <table class=rand align=center cellspacing=0 cellpadding=0 width=730><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=60>Rang</td><td align=middle width=200>Syndikat</td><td align=middle width=70>Bonus-EMOs</td><td align=center width=340>Spieler</td></tr>$top10synwinnerlines</table></td></tr></table>
			<br>
        <center>Herzlichen Glückwunsch :)</center>
			<br><br>Auch wenn es für sie keine EMO-Preise mehr gibt, wollen wir an dieser Stelle trotzdem diejenigen 10 Spieler nennen, die letzte Runde in der Einzelrangliste am besten abschnitten:<br><br>
        <table class=rand align=center cellspacing=0 cellpadding=0><tr><td><table align=center cellpadding=2 cellspacing=1><tr class=subhead><td width=60><b>Runde ".($round-2)."</b></td><td align=middle width=80>Rang</td><td align=middle width=100>Spieler</td><td align=center width=220>Konzern (#Syndikatsnummer)</td></tr>$top10winnerlines</table></td></tr></table>";

		echo "<br><br>CONTENT:<br>$content<br>";

		// <br><br>Wir m?chten uns au?erdem recht herzlich bei den Tutoren bedanken, die den neuen Spielern letzte Runde kr?ftig unter die Arme gegriffen haben.<br>Folgende Spieler haben sich dabei besonders hervorgetan, indem sie ihren Sch?tzlingen auf einen der ersten drei Pl?tze im Anf?nger-Syndikatsranking verholfen haben und erhalten als kleines Dankesch?n und Preis jeweils 100 Bonus-EMOs:<br><br>".join(", ", $tutoren);

        $headline = "Gewinner von Runde ".($round-2);
        select("insert into announcements (time, headline, content, poster, type) values ($time, '$headline', '$content', 'Bogul', 'outgame')");
} else { echo "Preise wurden bereits ausgesch?ttet und das Posting wurde schon erstellt"; }

?>
