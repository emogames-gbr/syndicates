<?

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

//Mögliche interne Aktionen
if ($input && ($input == "gebot_back" || $input == "gebot_back_all" || $input == "gebot" || $input == "changemin" || $input == "buy" || $input == "delete" || $input == "deleteall" || $input == "bringin" )) {$input=$input;} else {unset($input);}
$showmin = (int) $showmin;
$showmin >= 0 ? 1 : $showmin = 0;
$offer_id = (int) $offer_id;
$offer_id >= 0 ? 1 : $offer_id = 0;
$product = $product;
$buy_id > 0 ? $buy_id = (int) $buy_id : $buy_id = 0;


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//
require_once("../../inc/ingame/game.php");

if(!$sciences["ind19"]){
	f("Um den Global Market nutzen zu können, benötigen Sie die Forschung 
		<em>Basic Trade Program</em>.");
	require_once("../../inc/ingame/header.php");
	require_once("../../inc/ingame/footer.php");
	exit;
}

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//
$anzahl_assistenten_plaetze = 5;
if ($features[GEBAEUDEQEX])
	$anzahl_assistenten_plaetze = 100;
	
	
define(MAXCOUNT,$anzahl_assistenten_plaetze);// Maximale Anzahl erlaubter Angebote
if (getServertype() == "classic") {
  define(UNITS_MAX_PRICE,1.20); // Maximalpreis für Einheiten
  define(UNITS_MIN_PRICE,0.55); // Minimalpreis für Einheiten
} else { // BASIC
  define(UNITS_MAX_PRICE,1.0); // Maximalpreis für Einheiten
  define(UNITS_MIN_PRICE,0.4); // Minimalpreis für Einheiten
}
define(RES_MAX_PRICE,500000); // Maximalpreis für Ressourcen
define(DELAY,60*15); // Zufällige inserts in Market nach 5 + 0-15 Minuten (5-20 minuten insgesamt)
define(RES_MIN_MOD_PREIS,0.75); // MAxabweichung für ressourcenpreise
define(RES_MAX_MOD_PREIS,1.25);
define(MINPREISGRENZE,0.75); // Der Minpreis kann nie über 75% des Ressourcenpreises steigen
define(BEHEMOTH_RANGERCOUNT,2);
define(UNITS_UNTERBIETER,50);
define(RES_UNTERBIETER,1);
$probeaccountfehler = "Probeaccounts können keine Waren verkaufen.";
$lasthours = 6; $lasthours == 0 ? $lasthours = 1:1; // Anzahl Stunden die für min / maxpreis berechnung kalkuliert werden.
// define(MARKET_BUILD_TIME_INSTANT,60*60*2); // 20 % der einheiten schon nach 2 stunden verfügbar



$number = check_int($number);
$price = check_int($price);
$anzahl =check_int($anzahl);
$buyprice = check_int($buyprice);



if (!is_new_player($id, 3)) {
	$zufallszahl_vote_class = mt_rand(0,4);
	$classes = array("tableHead", "tableHead2", "tableInner1", "tableInner2", "siteGround");

	$voted = assocs("select link, ip from link_klick_count where user_id = $id and time > ".($time-24*3600));
	$galaxy_news_text = "<b>Deine Hilfe ist gefragt!</b><br>Mach mit und vote Syndicates an die Spitze der aussagekräftigsten Browsergames-Charts Deutschlands!";
	$galaxy_news_link = "<a href='?headeraction=galaxy-news' target='_blank'><img src='http://galaxy-news.de/images/vote.gif' border=0 align=texttop></a>";
	$gamesdynamite_text = "Stimm ab für Syndicates! Du kannst einmal pro Tag für Syndicates stimmen und hilfst so, eine bessere Chartplatzierung zu erreichen, was wiederum mehr Spieler und mehr Spaß bedeutet!<!--<br>Wir würden uns freuen, wenn du Syndicates auch bewerten würdest. Bitte beachte, dass die bestmögliche Note bei 99% liegt. Wenn man versucht 100% einzugeben und seine Eingabe nicht überprüft werden daraus dann 10% und verfälscht die Bewertung erheblich!-->";
	$gamesdynamite_link = "<a href=\"?headeraction=gamesdynamite\" target=\"_blank\"><img src=\"http://voting.gdynamite.de/images/gd_animbutton.gif\" alt=\"vote now!\" border=\"0\"></a>";

	foreach ($voted as $vl) {
		if ($vl[link] == "gamesdynamite" && $vl[ip] == getenv ( "REMOTE_ADDR")): $gamesdynamite_done = 1;
		elseif ($vl[link] == "galaxy-news" && $vl[ip] == getenv ("REMOTE_ADDR")): $galaxy_news = 1;
		endif;
	}


	$galaxy_news = 1;

	if (!$galaxy_news): $browsergame_vote_text = $galaxy_news_text; $browsergame_link = $galaxy_news_link;

	
	//elseif (!$gamesdynamite_done): $browsergame_vote_text = $gamesdynamite_text; $browsergame_link = $gamesdynamite_link;
	endif;

	if ($browsergame_vote_text && $browsergame_link) {
		$tpl->assign('SHOWVOTETEXT', true);
		$tpl->assign('BROWSERGAME_LINK', $browsergame_link);
		$tpl->assign('BROWERSGAME_VOTE_TEXT', $browsergame_vote_text);
		$tpl->assign('BROWSERGAME_LINK', $browsergame_link);
		$tpl->assign('CLASSES', $classes);
	}


}
//$profiler = new profiler($input);
//$profiler->init();
//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//
js::loadOver();


if ($globals[updating] != 1) {
	$lagerbar = energyadd($id,3)-$status[energy];
	$unitstats = getunitstats($status{race});
	$spystats = getspystats($status{race});
	$resstats = getresstats();
	$myoffers = array();
	$alloffers = array(); // Alle angebote gesammelt (billigste mit preis und neuem system)
	$resoffers = array(); // billigste angebote
	$miloffers = array(); // billigste angebote
	$spyoffers = array(); // billigste angebote
	$ownoffers = 0; // Anzahl der eigenen Angebote
	$queries = array();
	$delay = 60*5+ mt_rand(0,DELAY);
	$resnumber = 10; // Anzahl der Ressourcen die auf einmal gekauft werden
	$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden
	$minprices = array();
	
	$market = getmarket($id);
	
	$miltotal = miltotal($status{id},5);
	$spiestotal = spiestotal($status{id},5);
	$maxunits = maxunits(mil);
	$maxspies = maxunits(spy);
	$totalCarriers = getTotalCarriers();
	$moreunits = $maxunits-$miltotal{all}+$totalCarriers;
	if ($moreunits < 0 ) { $moreunits = 0;}
	$morespies = $maxspies-$spiestotal{all};
	if ($morespies < 0 ) { $morespies = 0;}
	
	foreach ($resstats as $key => $value) {
	    if ($key != "money") {
	        $minprices{$key} = $value{value}*10;
	    }
	    else {
	            $minprices{$key} = $value{value};
	    }
	}
	
	
	
	//**************************************************************************//
	//**************************************************************************//
	//							Eigentliche Berechnungen!						//
	//**************************************************************************//
	//**************************************************************************//
	
	
	//							selects fahren									//
	
	//**********************************************************
	//					AKTUELLE ANGEBOTE ERMITTELn
	//**********************************************************

	function angeboteErmitteln() {
		global $time, $miloffers, $spyoffers, $resoffers, $minprices, $unitstats, $spystats;
		
		####### schon vorhandene eigene angebote prüfen
		    //////////////////////////////
		    // Angebote aus der Db Holen//
	        //////////////////////////////
	        $dboffers = assocs ("select type,prod_id,price,sum(number) as numbersum from market where inserttime < $time and number > 0 group by type,prod_id,price order by type,prod_id asc,price asc");
	
	        foreach ($dboffers as $temp) {
	            if (!$alloffers{$temp{type}}{$temp{prod_id}}) {
	                $alloffers{$temp{type}}{$temp{prod_id}} = array(number => $temp{numbersum}, price => $temp{price});
	            }
	        }
			
			$dboffers = assocs ("select type,prod_id,price,sum(number) as numbersum from market where inserttime < $time and number > 0 group by type,prod_id,price order by type,prod_id asc,price asc");
	


			/*pvar($alloffers);
			$u_elites = array(8,9,10,19,24);
			$u_elites2 = array(7,11,12,20,25);
			$u_techs = array(14,15,16,21,26);*/
			
	        // Militärangebote
			if ($alloffers{mil})	{
		        foreach ($alloffers{mil} as $key => $miltemp) {
		                $type = changetype("mil",$key);
		                if ($unitstats{$type{product}}{unit_id} == $key) {
		                    $miloffers{$type{product}} = array(number => $miltemp{number},price => $miltemp{price});
		                }
		        }
			}
	        unset ($alloffers{mil});
	        // Spionageangebote
			if ($alloffers{spy})	{
		        foreach ($alloffers{spy} as $key => $spytemp) {
		                $type = changetype("spy",$key);
		                if ($spystats{$type{product}}{unit_id} == $key) {
		                    $spyoffers{$type{product}} = array(number => $spytemp{number},price => $spytemp{price});
		                }
		        }
			}
	        unset ($alloffers{spy});
	        // Ressourcenangebote
			if ($alloffers{res})	{
		        foreach ($alloffers{res} as $key => $restemp) {
		                $type = changetype("res",$key);
		                $resoffers{$type{product}} = array(number => $restemp{number},price => $restemp{price});
		                !$minprices{$type{product}} ? $minprices{$type{product}} = $restemp{price} : 1;
		        }
			}
	        unset ($alloffers,$dboffers);
	
	}
	angeboteErmitteln();
	
	$prices= calc_produktpreise();
	//pvar($prices);
	
	//							Berechnungen									//
	
	if ($input) {
	
	//**********************************************************
	//                  BACK
	//**********************************************************
	
		$rAusgabe="";
	
		if ($input == "deleteall") {
			$ownofferids = singles("select offer_id from market where owner_id=$status[id]");
			if (count($ownofferids) > 0) {
				foreach ($ownofferids as $temp) {
					$rAusgabe .= takeback($temp);
				}
			}
		}
	
	    if ($input == "delete") {
		mt_srand(getmicrotime());
		$rand = mt_rand(0,2000);
		usleep($rand);
	        if ($offer_id) {
	        	$rAusgabe .= takeback($offer_id);
	        }
	        else{f("Kein Angebot angegeben");}
	    }
	    
	    if($rAusgabe)
			s(substr($rAusgabe,0,-4));
	
	
	//**********************************************************
	//						Gebot einstellen
	//**********************************************************
	if ($input == "gebot") {
		$gebote_bisher = single("select count(*) from market_gebote where user_id=$status[id]");
		$allowed = 1;
		$back = changetype($product);
		$type = $back[type];
		$prod_id = $back[prod_id];
		
		if ($number <= 0) {
			$allowed = 0;
			f("Bitte geben sie eine Anzahl größer Null ein.");
		}
		if (!$product) {
			$allowed = 0;
			f("Sie haben kein Produkt ausgewählt");
		}
		if ($price <= 0) {
			$allowed = 0;
			f("Bitte geben Sie einen angemessenen Preis für das ausgewählte Produkt an");
		}
		if ($gebote_bisher >= MAXANZAHL_GEBOTE) {
			$allowed=0;
			f("Sie dürfen höchsten ".MAXANZAHL_GEBOTE." Kaufgebote abgeben.");
		}
		// Maxpreis ok ? 
		if ($type == "mil") {
			if ($price >= $miloffers{$product}{price}) {
				$maxprice = ($miloffers{$product}{price} > 0 ? $miloffers{$product}{price} : $prices[mil][$product][maxpreis] );
				if ($price >= $maxprice) {
					$maxprice--;
					$allowed = 0;
					f("Kaufangebote für dieses Produkt dürfen den Preis von ".pointit($maxprice)." Cr. momentan nicht überschreiten.");
				}
			} 
		}
		elseif ( $type=="spy") {
			if ($price >= $spyoffers{$product}{price}) {
				$maxprice = ($spyoffers{$product}{price} > 0 ? $spyoffers{$product}{price} : $prices[spy][$product][maxpreis] );
				if ($price >= $maxprice) {
					$maxprice--;
					$allowed = 0;
					f("Kaufangebote für dieses Produkt dürfen den Preis von ".pointit($maxprice)." Cr. momentan nicht überschreiten.");
				}
			} 
		}
		elseif ( $type == "res") {
			if ($price >= $resoffers{$product}{price}) {
				$maxprice = ($resoffers{$product}{price} > 0 ? $resoffers{$product}{price} : $prices[res][$product][maxpreis] );
				if ($price >= $maxprice) {
					$maxprice--;
					$allowed = 0;
					f("Kaufangebote für dieses Produkt dürfen den Preis von ".pointit($maxprice)." Cr. momentan nicht überschreiten.");
				}
			} 
		}
		//$allowed=1; Testing purpose
		if ($allowed == 1) {
	 		$queries[] = "insert into market_gebote 
	 						(user_id,prod_id,type,number,price,time)
	 						values
	 						($status[id],$prod_id,'$type',$number,$price,$time)
	 		";			
			s("Ihr Kaufgebot wurde angenommen.");
		}
		
	}
	
	//**********************************************************
	//						Gebot zurücknehmen
	//**********************************************************	
	if ($input == "gebot_back") {
		$allowed = 1;
		if (!$gebot_id) {
			$allowed = 0;
		}
		
		$gebot = assoc("select * from market_gebote where gebot_id = $gebot_id");
		if ($gebot[user_id] != $status[id]) {
			$allowed = 0;
		}
		if ($allowed) {
			$queries[] = ("delete from market_gebote where gebot_id = $gebot_id");
			s("Ihr Kaufgebot wurde erfolgreich zurückgezogen.");
		}
		else {
			f("Sie können nur Kaufgebote zurücknehmen, die Sie selbst eingestellt haben.");
		}
		
	}
	
	//**********************************************************
	//						Alle Gebote zurücknehmen
	//**********************************************************	
	if ($input == "gebot_back_all") {
		$gebot = assocs("SELECT gebot_id FROM market_gebote WHERE user_id = ".$status['id']);
		foreach($gebot as $vl) {
			$queries[] = ("DELETE FROM market_gebote WHERE gebot_id = ".$vl['gebot_id']);
		}
		s("Alle ihre Kaufgebote wurden erfolgreich zurückgezogen.");	
	}
	
	
	//**********************************************************
	//                    VERKAUFEN
	//**********************************************************
	    if ($input == "bringin") {
	        $insert = changetype($product);
	
	        $accept = 1;
			//if ($status[paid] != 1) {f("$probeaccountfehler");include("reminder.php");$accept=0;}
	        if ($number <1) {f("Wollen Sie wirklich \"nichts\" anbieten ?");$accept=0;}
	        if ($price < 1) {f("Haben Sie etwas zu verschenken ?");$accept=0;}
	
	        if ($status{$product} < $number) {f("Soviel können Sie nicht anbieten.");$accept=0;}
	
	        // Bei militäreinheiten und Spionen min und maxpreis sinnvoll festlegen
		$price100 = 0;
	        if ($insert{type} == "mil") {
				//pvar($product);
				//pvar($prices[mil]);
				$maxpreis = $prices[mil][$product][maxpreis];
				$minpreis = $prices[mil][$product][minpreis];
				$price100 = $prices[mil][$product][unitwert];
				$price100gm = $prices[mil][$product][unitwert2];
	        }
	        if ($insert{type} == "spy") {
				$maxpreis = $prices[spy][$product][maxpreis];
				$minpreis = $prices[spy][$product][minpreis];
				$price100 = $prices[spy][$product][unitwert];
				$price100gm = $prices[spy][$product][unitwert2];
	        }
			if ($insert[type] == "res") {
				$tmaxpreis = $prices[res][$product][maxpreis];
				$tminpreis = $prices[res][$product][minpreis];
				$price100 = $resstats[$product]['value'];
				if ($product != "money") $price100 *= 10;
				if ($price < $tminpreis) {
					$accept=0;
					f("Sie dürfen ihre Ressourcen momentan nicht billiger als ".pointit(ceil($tminpreis))." Cr pro 10 Einheiten anbieten.");
				}
				if ($price > $tmaxpreis) {
					$accept=0;
					f("Sie dürfen ihre Ressourcen momentan nicht teurer als ".pointit(floor($tmaxpreis))." Cr pro 10 Einheiten anbieten.");
				}
			}
	        if ($price > $maxpreis && $insert{type} != "res") {f("So teuer können Sie Ihre Ware nicht anbieten.<br> Der Höchstpreis für dieses Produkt liegt momentan bei ".pointit(floor($maxpreis))." Credits");$accept=0;}
	        if ($price > RES_MAX_PRICE && ($product == "energy" || $product == "metal" || $product == "sciencepoints")) {f("So teuer können Sie Ihre Ware nicht anbieten. Der Höchspreis für dieses Produkt liegt bei 500 Credits je 10 Einheiten");$accept=0;}
	        ### hier gültige produkte in die if anweisung eintragen ###
	        if ($product != "energy" && $product != "metal" && $product != "sciencepoints" && $product != "offspies" && $product != "defspies" && $product != "intelspies" && $product != "offspecs" && $product != "defspecs" && $product != "elites" && $product != "elites2" && $product !="techs") {f("Bitte geben sie ein gültiges Produkt an."); $accept=0;}
	        ### Minimalpreise für Einheiten angeben
	        if ($insert{type} != "res" && $price < $minpreis) {$accept=0;f("So billig können sie ihre Einheiten nicht anbieten.<br>Der mindestpreis für dieses Produkt liegt momentan bei ".pointit(ceil($minpreis))." Credits");}
	        ### nach überprüfung schließlich eintrag in datenbank ###
	        if ($accept == 1) {
	            $result = select("select count(*) from market where owner_id = ".$status{id});
	            $count = mysql_fetch_row($result); $count = $count[0];
	            if ($count < MAXCOUNT) {
	                #### Produkt wird Typ und Id zugewiesen ####
	                ### Statuswerte entsprechend aktualisieren ###
	                $status{$product} -=$number;
					$lagerbar = energyadd($id,3)-$status[energy];
	                ### Eintrag in Datenbank schreiben ###
	                if ($number && $price) {
	                    $inserttime = $time + $delay;
	                    $action="update status set $product=$product-$number where id=".$status{id};
	                    array_push($queries,$action);
	                    $action="insert into market (type,prod_id,number,price,owner_id,inserttime) values ('".$insert{type}."',".$insert{prod_id}.",$number,$price,".$status{id}.",$inserttime)";
	                    array_push($queries,$action);

			    $nextmoreexpensive = single("select price from market where inserttime < $time and number > 0 and type='".$insert['type']."' and prod_id = '".$insert['prod_id']."' and price >= $price ORDER BY price DESC LIMIT 1");
			    if (!$nextmoreexpensive) $nextmoreexpensive = 0;
			    if (!$price100) $price100 = 1;

	                    $action="insert into marketlogs (user_id,number,price,price100,price100percentage,pricenextmoreexpensiveoffer,time,prod_id,type,owner_id,action) values (".$status{id}.",$number,$price,$price100, ".(round($price/$price100*1000)/10).",$nextmoreexpensive,$time,".$insert{prod_id}.",'".$insert{type}."','0','sell')";
	                    array_push($queries,$action);
	                    unset($action);
	                }# if number and price
	                // Produktname bestimmen
	                if ($insert{type} == "res") {$prodname1 = $resstats{$product}{name}; $jeXeinheiten = "10";}
	                elseif($insert{type} == "mil") {$prodname1 = $unitstats{$product}{name}; $jeXeinheiten = "";}
	                else {$prodname1 = $spystats{$product}{name}; $jeXeinheiten = "";}
	                s("Produkt erfolgreich eingetragen. Sie bieten jetzt <b>".pointit($number)."</b> $prodname1 für <b>".pointit($price)."</b> Cr je $jeXeinheiten $prodname1 an");
	            }
	            else {f("Sie können maximal ".MAXCOUNT." Angebote gleichzeitig einstellen");}
	        }
	    }
	
	//**********************************************************
	//                    KAUFEN
	//**********************************************************
	
        $buyallowed = true;
	    if ($input == "buy" && $anzahl > 0 && $buyproduct && $buyprice) {
	        // DIESE FUNKTION ARBEITET MIT IGNORE USER_ABORT!
	        ignore_user_abort(TRUE);
	        $buystart = getmicrotime();
	        $product = changetype($buyproduct);
	        $prebuyers = single("select count(*) from market_buffer where product = '$buyproduct'");
	        // Zuviele Kaufanfragen ?
	        if ($prebuyers > 20) {$buyallowed = false; $buyerror = "Es gibt momentan zuviele Kaufanfragen zu diesem Produkt, bitte versuchen sie es in einigen Sekunden noch einmal";}
	        $buycost = $anzahl * $buyprice;
	        // Ressourcen im 10erpack
	        if ($product{type} == "res") {$buycost = ceil($buycost / 10);}
	        if ($buycost < 1) {$buycost = 1;}
	        // Genug Geld vorhanden ?
	        if ($buycost > $status{money}) {$buyallowed = false; $buyerror = "Sie besitzen nicht genügend Credits um soviel kaufen zu können";}
	        ### Prüfen ob genug Lagerkapazitäten vorhanden, falls Spione oder Militäreinheiten gekauft werden
	        if ($product{type} == "mil")  {
	        // Maximale Anzahl möglicher Einheiten bestimmen
	        //$maxunits = $status{depots} * DEPOTWERT + $status{land} * LANDWERT + $sciences{mil8} * MIL8BONUS * $status{depots};
	        $miltotal = miltotal($status{id});
	        		$moreunits_temp = $moreunits;
	        		if ($status['race'] == "nof" && $buyproduct == "elites") $moreunits_temp = $maxunits - $totalCarriers;
	            if ($moreunits_temp < $anzahl) {$buyallowed = false;$buyerror = ("Sie haben nicht genug Lagerhallen um soviele Militäreinheiten kaufen zu können");}
	        }
			if ($buyproduct == "energy" && $anzahl  > $lagerbar) {
				$buyallowed = false;$buyerror = ("Soviel Energie können Sie nicht speichern.");
			}
	      	# Zusammenzählen der Spione
	        if ($product{type} == "spy")  {
	            $spiestotal = spiestotal($status{id});
	            //$maxspies = $status{spylabs} * SPYLABSWERT + $status{land} * LANDWERT2 + $sciences{glo4} * GLO4BONUS * $status{spylabs};
	            if (($spiestotal + $anzahl) > $maxspies) {$buyallowed = false;$buyerror = ("Sie haben nicht genügend Spionageeinrichtungen");}
	        }
	
	        // Ab hier kommt der große Eintrag in den Buffertable um simultane käufe zu verhindern
	        if ($buyallowed) {
	            select("insert into market_buffer (user_id,product,number,price,time) values(".$status{id}.",'$buyproduct',$anzahl,$buyprice,$time)");
	            $mybuyid = single("select buy_id from market_buffer where user_id =".$status{id}." order by buy_id asc limit 1");
	            $firstbuyid = 0;
	            $waitcycles = 0;
	            // Schleife, bis alle anderen käufe abgewickelt sind
	            while ($mybuyid != $firstbuyid && $waitcycles < 30) {
	                $timelimit = $time - 30;
	                $firstbuyid = single("select buy_id from market_buffer where buy_id <= $mybuyid and product='$buyproduct' and time > $timelimit");
	                usleep(200000);
	                $waitcycles++;
	            }
	            if ($waitcycles >= 30) {
	                $buyallowed = false; $buyerror = "Es gibt momentan zuviele Kaufanfragen zu diesem Produkt, bitte versuchen sie es in einigen Sekunden noch einmal";
	            }
	            if ($buyallowed) {
	                // Jetzt ist käufer dran, gucken ob noch genug ressourcen da sind
	                $minprice = single("select min(price) from market where prod_id=".$product{prod_id}." and type ='".$product{type}."' and inserttime < $time and number > 0");
	                // Produkt mitlerweile teurer ?
	                if ($minprice > $buyprice) {
	                    $buyallowed = false; $buyerror = "Der Kaufpreis für dieses Produkt hat sich geändert, der Kaufvorgang wurde abgebrochen";
	                }
	                // Falls Ressourcen noch zum selben preis da sind, nachschauen ob genug da sind
	                else {
	                    $nochda = single("select sum(number) from market where price<=$buyprice and prod_id=".$product{prod_id}." and type='".$product{type}."' and number >= 0 and inserttime < $time");
	                    if ($anzahl > $nochda) {
	                        $buyallowed = false; $buyerror = "Es sind nicht genug Produkteinheiten verfügbar, der Kaufvorgang wurde abgebrochen";
	                    }
	                    // Schlussendlich, falls noch genug einheiten zum richtigen preis da sind, kaufvorgang abwickeln
	                    elseif ($buyallowed == true) {
	                        // !!! KAUF ABWICKELN !!!
	                        if ($product{type} == "res") {
	                            $output = $resstats{$buyproduct}{name};
	                            $outputBuy = $output;
	                        }
	                        elseif ($product{type} == "mil") {
	                            // Bei Militär und Spionageeinheiten festlegen, wieviel % sofort daheim sein sollen:
	                            $outputBuy = $unitstats{$buyproduct}{name};
	                        }
	                        elseif ($product{type} == "spy") {
	                            // Bei Militär und Spionageeinheiten festlegen, wieviel % sofort daheim sein sollen:
	                            $output = $spystats{$buyproduct}{name};
	                            $outputBuy = $output;
	                        }
				// Price100 für die Logs ermitteln

				$price100 = 0;
				if ($product{type} == "mil") {
						$price100 = $prices[mil][$buyproduct][unitwert];
				}
				if ($product{type} == "spy") {
						$price100 = $prices[spy][$buyproduct][unitwert];
				}
				if ($product[type] == "res") {
					$price100 = $resstats[$buyproduct]['value'];
					if ($buyproduct != "money") $price100 *= 10;
				}
				if (!$price100) $price100 = 1;
	                        // Angebote ermitteln
	                        $fittingoffers = assocs("select * from market where price<=$buyprice and prod_id=".$product{prod_id}." and type='".$product{type}."' and number >= 0 and inserttime < $time order by price,inserttime asc");
	                        $usedoffers = array();
	                        $left = $anzahl;
							$topay = 0;
	                        foreach ($fittingoffers as $value) {
	                            // Angebot wird ganz aufgekauft
	                            if ($left >= $value{number} && $left > 0 && $value[number] >= 0) {
	                                $profit = $value[number] * $value[price];
	                                $left -= $value[number];
	                                if ($value[type] == "res") {
	                                    $profit = round($profit/10);
	                                    if ($profit < 1) {$profit = 1;}
	                                }
									$topay += $profit;
	                                // Angebot aus markt löschen
	                                $queries[] = "delete from market where offer_id = ".$value[offer_id];
	                                // Anbieter geld gutschreiben
	                                $queries[] = "update status set money=money+".$profit." where id=".$value[owner_id];
	                                // Marketlogs schreiben
	                                $queries[]="insert into marketlogs (user_id,owner_id,time,prod_id,type,price,price100,price100percentage,number,action) values (".$status{id}.",'".$value{owner_id}."',$time,'".$value{prod_id}."','".$value{type}."','".$value{price}."','".$price100."','".(round($value['price']/$price100*1000)/10)."','".$value[number]."','buy')";
	                                // Message schreiben
									$stueckpreis = (($profit/$value[number] >= 1000) ? pointit($profit/$value[number]) : number_format(($profit/$value[number]),1));
									if($value[type]=="mil"){
										$owner_race = single("select race from status where id=".$value[owner_id]);
										$owner_usta = getunitstats($owner_race);
										$output = $owner_usta{$buyproduct}{name};
									}
									$werte = pointit($value[number])."|".$output."|".pointit($profit)."|".$stueckpreis;
	                                // Message Kategorie  5, id  4 => Units
									// Message Kategorie 10, id 57 => Ressourcen
									
	                                $queries[] = "insert into message_values (id,user_id,time,werte) values (".($value[type] == "res" ? "57" : "4").",".$value{owner_id}.",'".$time."', '$werte');";
	                            }
	                            // Nur ein Teil wird gekauft
	                            elseif ($left > 0 && $value[number] >= 0) {
	                                $profit = $left * $value[price];
	                                if ($value[type] == "res") {
	                                    $profit = ceil($profit/10);
	                                }
									$topay +=$profit;
	                                // Angebot aus markt löschen
	                                $queries[] = "update market set number=number-$left where offer_id =".$value[offer_id];
	                                // Anbieter geld gutschreiben
	                                $queries[] = "update status set money=money+".$profit." where id=".$value[owner_id];
	                                // Marketlogs schreiben
	                                $queries[]="insert into marketlogs (user_id,owner_id,time,prod_id,type,price,price100,price100percentage,number,action) values (".$status{id}.",'".$value{owner_id}."',$time,'".$value{prod_id}."','".$value{type}."','".$value{price}."','".$price100."','".(round($value['price']/$price100*1000)/10)."','".$left."','buy')";
	                                // Message schreiben
									$stueckpreis = (($profit/$left >= 1000) ? pointit($profit/$left) : number_format(($profit/$left),1));
	                                if($value[type]=="mil"){
										$owner_race = single("select race from status where id=".$value[owner_id]);
										$owner_usta = getunitstats($owner_race);
										$output = $owner_usta{$buyproduct}{name};
									}
									$werte = pointit($left)."|".$output."|".pointit($profit)."|".$stueckpreis;
	                                // Message Kategorie  5, id  4 => Units
									// Message Kategorie 10, id 57 => Ressourcen
	                                $queries[] = "insert into message_values (id,user_id,time,werte) values (".($value[type] == "res" ? "57" : "4").",".$value{owner_id}.",'".$time."', '$werte');";
	                                $left = $left - $value[number];
	                                break;
	                            }
	                        }
	
	                        // Käufer Aktualisieren //
	                        $status{money} -= $topay;
	                        $buildtime = get_hour_time($time) + MARKET_BUILD_TIME;
	                        if ($product{type} != "res") {
	                            $status{nw} = nw($status{id});
	                            if ($product{type} != "res") {
	                                $status{$buyproduct} += $anzahl;
	                            }
	                        }
	                        if ($product{type} == "res") {
	                            $status{$buyproduct} += $anzahl;
	                            $status{nw} = nw($status{id});
	                            $queries[] = "update status set nw=".$status{nw}.",money=money-$topay,".$buyproduct."=".$buyproduct."+".$anzahl." where id = ".$status{id};
	                        }
	                        elseif ($product{type} == "mil") {
	                            $queries[] = "insert into build_military (unit_id, user_id, number, time) values (".$unitstats{$buyproduct}{unit_id}.",".$status{id}.", $anzahl, $buildtime)";
	                            $queries[] = "update status set nw=".$status{nw}.",money=money-$topay where id = ".$status{id}; // ,$buyproduct=$buyproduct+$instantbuy
	                        }
	                        elseif ($product{type} == "spy") {
	                            $queries[] = "insert into build_spies (unit_id, user_id, number, time) values (".$spystats{$buyproduct}{unit_id}.",".$status{id}.", $anzahl, $buildtime)";
	                            $queries[] = "update status set nw=".$status{nw}.",money=money-$topay where id = ".$status{id}; // ,$buyproduct=$buyproduct+$instantbuy
	                        }
	                        // Käufer aktualisieren Ende //
	                        // Erfolgsmeldung ausgeben
							$stueckpreis = (($topay/$anzahl >= 1000) ? pointit($topay/$anzahl) : number_format(($topay/$anzahl),1));
	                        s(pointit($anzahl)." $outputBuy für ".pointit($topay)." Cr erfolgreich erworben (Stückpreis: ".$stueckpreis.").");
	                    }
	                }
	            }
	            // Eintrag aus market buffer entfernen, so dass nächster user abgweickelt werden kann
	            $queries[] = "delete from market_buffer where user_id =".$status{id};
	        }
	    if ($buyerror) {f($buyerror);$anzahl=0;}
	    $buyend = getmicrotime();
	    $buytime = $buyend-$buystart;
	    ignore_user_abort(FALSE);
	    #echo "Buytime: $buytime";
	    } // input == buy
	} // wenn input
	//							Daten schreiben									//
	db_write($queries);
	
	
	
	
	// Angebote nach Kauf aktualisieren
	if ($input == "buy" && $anzahl > 0 && $buyallowed == true) {
		angeboteErmitteln();
	}
	
	

	
	if ($goon)	{

		///////////////////////////////////
		// Angebote aus der Db Holen ENDE//
		///////////////////////////////////
		$result = select($action="select * from market where owner_id =".$status{id});
		while ( $returnstatus = mysql_fetch_assoc($result)) {
			array_push ($myoffers, $returnstatus);
			$ownoffers++;
		}  
		$own_gebote = assocs("select * from market_gebote where user_id = $status[id]");
		    
		// Angeboteerstellenausgabe
		$tpl->assign('OWNOFFERS', $ownoffers);
		$tpl->assign('MAXCOUNT', MAXCOUNT);
		if ($ownoffers < MAXCOUNT) {
			$konto_output = array(); $vl = array();
			foreach ($resstats as $key => $temp) {
				$vl['o_status'] = $status[$key];
				$vl['o_key'] = $key;
				array_push($konto_output, $vl);
				unset($vl);
			}
			foreach($spystats as $key => $value) {
				$vl['o_status'] = $status[$key];
				$vl['o_key'] = $key;
				array_push($konto_output, $vl);
				unset($vl);
			}
			foreach($unitstats as $key => $value) {
				$vl['o_status'] = $status[$key];
				$vl['o_key'] = $key;
				array_push($konto_output, $vl);
				unset($vl);
			}
			$tpl->assign('KONTO', $konto_output);
		}

		// Kaufgebote Start
		$tpl->assign('OWN_GEBOTE_COUNT', count($own_gebote));
		$tpl->assign('MAXANZAHL_GEBOTE', MAXANZAHL_GEBOTE);
		
			$units_output = array(); $vl = array();
			foreach($spystats as $key => $value) {
				$vl['o_key'] = $key;
				$vl['o_unitName'] = $spystats{$key}{name};
				array_push($units_output, $vl);
				unset($vl);
			}
			foreach($unitstats as $key => $value) {
				$vl['o_key'] = $key;
				$vl['o_unitName'] = $unitstats{$key}{name};
				array_push($units_output, $vl);
				unset($vl);
			}
			$tpl->assign('UNITS', $units_output);

			
		$maxbuyable = array();
		$currentprice = array();
		// Kaufgebote Ende	
		
		// Ressourcen start

		// Ressourcenausgabe in Schleife
		$first = 0;
		$resstats_output = array();
		foreach($resstats as $key => $value) {
			$widget_title = "Preisverlauf ".$resstats{$key}{name};
		      
		    if ($key != "money") {
		    	$value['o_resName'] = $resstats{$key}{name};
		    	// Für den Maxbutton bei Kaufangeboten
		    	//$maxbuyable[$value['type']] = array('key' => $value['type'], 'value' => 1000000);
		    	$currentprice[$value['type']] = array('key' => $value['type'], 'value' => $prices['res'][$value['type']]['maxpreis']);
		    	$value['o_JsHelpTagCustom'] = getJsHelpTagCustom(
						"Preisverlauf",
						"_aktien_halter.gif",
						"onClick=\"window.open('croniwidget.php?type=res&title=".($widget_title)."&identifier=".$resstats{$key}{name}."', 
						'".str_replace(" ","_",($widget_title))."', 'width=520 , height=390 ,scrollbars=no')\" style=\"cursor:pointer;\" valign=\"bottom\" ");
				if ($resoffers{$key}) {
					$value['o_resoffers'] = true;
					$resbuyable{$key} = floor($status{money} / $resoffers{$key}{price} * 10);
					if ($resbuyable{$key} > $resoffers{$key}{number}) {
						$resbuyable{$key} = $resoffers{$key}{number};
					}
					if ($value['type'] == "energy") {
						$tpl->assign('ASSI_STD_PRICE', $resoffers{$key}{price}-1);
						if ($buyproduct == "energy" && $input == "buy") {
							$eanzahl = $anzahl;
						} else {
							$eanzahl = 0;
						}
						if ($resbuyable[$key] > $lagerbar-$eanzahl) {
							$resbuyable[$key] = $lagerbar-$eanzahl;
						}
					}
					$value['o_resoffersNumber'] = pointit($resoffers{$key}{number});
					$currentprice[$value['type']]['value'] = $resoffers{$key}{price};
					$value['o_resoffersPrice_pointit'] = pointit($resoffers{$key}{price});
					$value['o_resbuyable_pointit'] = pointit($resbuyable{$key});
					$value['o_resbuyable'] = $resbuyable{$key};
					$value['o_resoffersPrice'] = $resoffers{$key}{price};
					$value['o_key'] = $key;
				}
				array_push($resstats_output, $value);
			} // Wenn key nicht money
		}
		$tpl->assign('RESSTATS_MARKT', $resstats_output);
		// Ressourcen Ende
	
		// Spione Start
		$first = 0;

		// Ausgabe in Schleife
		if ($input == "buy" && strlen($spystats[$buyproduct][name]) > 0) {
			$morespies -= $anzahl;
		}
		
		$spystats_output = array();
		foreach ($spystats as $key => $value) {
			$value['o_spyName'] = $spystats{$key}{name};
			$value['o_JsHelpTagCustom'] = getJsHelpTagCustom(
		        	"Preisverlauf",
		        	"_aktien_halter.gif",
		        	"onClick=\"window.open('croniwidget.php?type=spy&title=".urlencode("Preisverlauf ".$spystats{$key}{name})."&identifier=".$spystats{$key}{unit_id}."', 
		        	'".str_replace(" ","_","Preisverlauf ".$spystats{$key}{name})."', 'width=520 , height=390 ,scrollbars=no')\" style=\"cursor:pointer;\" valign=\"bottom\" ");
			$value['o_unitwert'] = pointit($prices['spy'][$key]['unitwert']);
			// Wie viel unabhängig vom Guthaben maximal kaufbar wäre (für Kaufassi)
			$maxbuyable[$key] = array('key' => $key, 'value' => $morespies);
			$currentprice[$key] = array('key' => $key, 'value' => $prices['spy'][$key]['unitwert']);
			if ($spyoffers{$key}) {
				$value['o_spyoffers'] = true;
				$spybuyable{$key} = floor($status{money} / $spyoffers{$key}{price});
				$spypercent{$key} = floor(100 * $spyoffers{$key}{price} / $prices['spy'][$key]['unitwert']);
	            if ($spybuyable{$key} > $spyoffers{$key}{number}) {
					$spybuyable{$key} = $spyoffers{$key}{number};
				}
				if ($spybuyable{$key} > $morespies) {
					$spybuyable{$key} = $morespies;
				}
		        $value['o_spyoffersNumber'] = pointit($spyoffers{$key}{number});
		        $value['o_spyoffersPrice'] = pointit($spyoffers{$key}{price});
		        $value['o_spypercent'] = $spypercent{$key};
				$value['o_spybuyable_pointit'] = pointit($spybuyable{$key});
				$value['o_spybuyable'] = $spybuyable{$key};
				$value['o_key'] = $key;
				$value['o_spyoffersPrice'] = $spyoffers{$key}{price};
				$currentprice[$key]['value'] = $spyoffers{$key}{price};
				$value['o_spyoffersPrice_pointit'] = pointit($spyoffers{$key}{price});
			}
			array_push($spystats_output, $value);
		}
		$tpl->assign('SPYSTATS_MARKT', $spystats_output);
		unset($wert);
		// Spione Ende
	
		// Militäreinheiten Start

		// Ausgabe in Schleife
		$first = 0;
		if ($input == "buy" && strlen($unitstats[$buyproduct][name]) > 0) {
			if ($status['race'] != "nof" or $buyproduct != "elites")
			$moreunits -= $anzahl;
			else $totalCarriers += $anzahl;
		}
				
		$unitstats_output = array();
		foreach ($unitstats as $key => $value) {
			$value['o_unitName'] = $unitstats{$key}{name};
			$value['o_JsHelpTagCustom'] = getJsHelpTagCustom("Preisverlauf","_aktien_halter.gif","onClick=\"window.open('croniwidget.php?type=mil&title=".urlencode("Preisverlauf ".$unitstats{$key}{name})."&identifier=".$unitstats{$key}{unit_id}."', '".str_replace(" ","_","Preisverlauf ".$unitstats{$key}{name})."', 'width=520 , height=390 ,scrollbars=no')\" style=\"cursor:pointer;\" valign=\"bottom\" ");
			$value['o_unitwert'] = pointit($prices['mil'][$key]['unitwert']);
			// Wie viel unabhängig vom Guthaben maximal kaufbar wäre (für Kaufassi)
			$moreunits_temp = $moreunits;
			if ($status['race'] == "nof" && $key == "elites") $moreunits_temp = $maxunits - $totalCarriers;
			$maxbuyable[$key] = array('key' => $key, 'value' => $moreunits_temp);
			$currentprice[$key] = array('key' => $key, 'value' => $prices['mil'][$key]['unitwert']);
			if ($miloffers{$key}) {
				$value['o_miloffers'] = true;
				$milbuyable{$key} = floor($status{money} / $miloffers{$key}{price});
				$milpercent{$key} = floor(100 * $miloffers{$key}{price} / $prices['mil'][$key]['unitwert']);
				if ($milbuyable{$key} > $miloffers{$key}{number}) {
					$milbuyable{$key} = $miloffers{$key}{number};
				}
				//$moreunits_temp = $moreunits;
				//if ($status['race'] == "nof" && $key == "elites") $moreunits_temp = $maxunits - $totalCarriers;
				if ($milbuyable{$key} > $moreunits_temp) {
					$milbuyable{$key} = $moreunits_temp;
				}
				if ($milbuyable{$key} < 0) $milbuyable{$key} = 0;
		        $value['o_miloffersNumber'] = pointit($miloffers{$key}{number});
		        $value['o_milpercent'] = $milpercent{$key};
		        $value['o_milbuyable_pointit'] = pointit($milbuyable{$key});
				$value['o_milbuyable'] = $milbuyable{$key};
				$currentprice[$key]['value'] = $miloffers{$key}{price};
	            $value['o_key'] = $key;
	            $value['o_miloffersPrice'] = $miloffers{$key}{price};
	            $value['o_miloffersPrice_pointit'] = pointit($miloffers{$key}{price});
			}
			array_push($unitstats_output, $value);
		}
		$tpl->assign('UNITSTATS_MARKT', $unitstats_output);
		
		$tpl->assign('MAXBUYABLE', $maxbuyable);
		$tpl->assign('CURRENTPRICE', $currentprice);
		// Militäreinheiten Ende

		
		// Mindest und Maximalpreise
		$prices_output = array();
		foreach ($prices as $key => $value) { 
			$vl_output = array();
			foreach ($value as $temp) {
				if ($temp[name] != "Credits") {
					$temp['o_minpreis'] = pointit(ceil($temp[minpreis]));
					$temp['o_maxpreis'] = pointit(floor($temp[maxpreis]));
					array_push($vl_output, $temp);
				}
			}
			array_push($prices_output, $vl_output);
		}
		$tpl->assign('PRICES', $prices_output);
						    
		// Aktuelle eigene Verkaufsangebote
		if ($ownoffers > 0) {
			$myoffers_output = array();
			foreach ($myoffers as $key => $value) {
		    	$value['o_offerID'] = $myoffers{$key}{offer_id};
		    	
		        // Name der angebotenen Ware bestimmen
		        if ($myoffers{$key}{type} == "mil") {
		        	foreach ($unitstats as $key2 => $value2) {
		        		if ($unitstats{$key2}{unit_id} == $myoffers{$key}{prod_id}) {
		        			$value['o_name'] = $unitstats{$key2}{name};
		        			break;
		        		}
		        	}
		        }
		        if ($myoffers{$key}{type} == "spy") {
		        	foreach ($spystats as $key2 => $value2) {
		        		if ($spystats{$key2}{unit_id} == $myoffers{$key}{prod_id}) {
		        			$value['o_name'] = $spystats{$key2}{name};
		        			break;
		        		}
		        	}
		        }
		        if ($myoffers{$key}{type} == "res") {
		        	if ($myoffers{$key}{prod_id} == "1") {
		        		$value['o_name'] = "Energie";
		        	} elseif ($myoffers{$key}{prod_id}== "2") {
		        		$value['o_name'] = "Erz";
		        	} else {
		        		$value['o_name'] = "Forschungspunkte";
		        	}
		        }
				if ($myoffers{$key}['inserttime'] <= $time) {
					$value['o_image'] = "_online.gif";
				}
				else {
					$value['o_image'] = "_gl_inaktiv.gif";
				}
				$value['o_number'] = pointit($myoffers{$key}{number});
				$value['o_price'] = pointit($myoffers{$key}{price});
				array_push($myoffers_output, $value);
			}
			$tpl->assign('MYOFFERS', $myoffers_output);
			$tpl->assign('MYOFFERS_COUNT', count($myoffers));
		}

		
		///
		/// EIGENE KAUFGEBOTE ANZEIGEN; FALLS VORHANDEN
		///

		// Aktuelle eigene Kaufangebote
		if (count($own_gebote) > 0) {
			$own_gebote_output = array();
			foreach ($own_gebote as $temp) {
				// Name der angebotenen Ware bestimmen
				if ($temp{type} == "mil") {
					foreach ($unitstats as $key2 => $value) {
						if ($unitstats{$key2}{unit_id} == $temp{prod_id}) {
							$temp['o_name'] = $unitstats{$key2}{name};
							break;
						}
					}
				}
				elseif ($temp{type} == "spy") {
					foreach ($spystats as $key2 => $value) {
						if ($spystats{$key2}{unit_id} == $temp{prod_id}) {
							$temp['o_name'] = $spystats{$key2}{name};
							break;
						}
					}
				}
				elseif ($temp{type} == "res") {
					if ($temp{prod_id} == "1") {
						$temp['o_name'] = "Energie";
					} elseif ($temp{prod_id}== "2") {
						$temp['o_name'] = "Erz";
					} else {
						$temp['o_name'] = "Forschungspunkte";
					}
				}
				$temp['o_number'] = pointit($temp[number]);
				$temp['o_price'] = pointit($temp[price]);
				array_push($own_gebote_output, $temp);
		    }
		    $tpl->assign('OWN_GEBOTE', $own_gebote_output);
		}
	} # goon ende
} // wenn gerade nicht upgedatet wird
elseif ($globals[updating] == 1) {
	f("Momentan läuft das stündliche Update. Der Global Market ist während des stündlichen Updates nicht verfügbar. 
		Probieren Sie es bitte später noch einmal.");
}	


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->assign('RIPF', $ripf);
$tpl->assign('STATUS', $status);
$tpl->assign('SHOWMARKET', $globals.updating != 1 && !$sciences.ind19 && $goon);

require_once("../../inc/ingame/header.php");
$tpl->display('market.tpl');
require_once("../../inc/ingame/footer.php");
//$profiler->end();

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


function calc_produktpreise() {
	global $unitstats,$spystats,$resstats,$lasthours,$minprices,$resoffers,$miloffers,$spyoffers;
	$ausgleichswert = 1;//.9; //irgendwie schwachsinn^^
	$lasthours=1;
		foreach ($resstats as $kk=>$temp) {
			$product = $temp[type];
			$avgwert = single("select $product from ressources order by time desc limit $lasthours");
			
			/*$tavg = 0;
			foreach ($avgwerte as $ttemp) {
				$tavg += $ttemp;
			}
			$avgwert = $tavg / count($avgwerte);
			if (!$avgwert) { // Mit der neuen Konfigurationsphase nur im 0. Tick (gleich nach Rundenstart) interessant
				$avgwert = $temp['value'];
			}*/
			$tminpreis = floor ($resstats[$product][value] * RES_MIN_MOD_PREIS * $ausgleichswert * 10 );
			$tmaxpreis = ceil ($resstats[$product][value] * RES_MAX_MOD_PREIS * $ausgleichswert * 10 );
			
			// ein aktueller Preis ist immer um 1 unterbietbar
			if (0 < $resoffers[$kk]['price'] && $resoffers[$kk]['price'] <= $tminpreis) {
				$tminpreis = $resoffers[$kk]['price'] - RES_UNTERBIETER;
			}
			// Mindestpreis
			if ($tminpreis < 2) $tminpreis = 2;
			$produkte[res][$temp[type]] = array("key" => $temp[type],"name" => $temp[name],"minpreis" =>$tminpreis,"maxpreis" => $tmaxpreis);
		}

		foreach ($unitstats as $kk=>$temp) {
			if ($unitstats[$temp[type]][current_price] == 0) {
				setUnitStandardprices("mil");
			}
           $unitwert = ($unitstats{$temp[type]}{credits} * $resstats{money}{value}
                        +$unitstats{$temp[type]}{energy} * $resstats{energy}{value} * $ausgleichswert 
                        +$unitstats{$temp[type]}{sciencepoints} * $resstats{sciencepoints}{value} * $ausgleichswert
                        +$unitstats{$temp[type]}{minerals} * $resstats{metal}{value} * $ausgleichswert );
                        
           $unitwert2 = ($unitstats{$temp[type]}{credits} * $resstats{money}{value}
                        +$unitstats{$temp[type]}{energy} * ($resoffers{energy}{price} ? $resoffers{energy}{price}/10 : $resstats{energy}{value} * $ausgleichswert )
                        +$unitstats{$temp[type]}{sciencepoints} * ($resoffers{sciencepoints}{price} ? $resoffers{sciencepoints}{price}/10 : $resstats{sciencepoints}{value} * $ausgleichswert )
                        +$unitstats{$temp[type]}{minerals} * ($resoffers{minerals}{price} ? $resoffers{minerals}{price}/10 : $resstats{minerals}{value} * $ausgleichswert ) );
                                  
            /* if ($temp[race] == "nof" && $temp[type] == "techs") {
             	$unitwert += BEHEMOTH_RANGERCOUNT*$produkte[mil]['elites']['unitwert'];
             }
			*/
            $maxpreis = $unitwert * UNITS_MAX_PRICE;
            $minpreis = $unitwert * UNITS_MIN_PRICE;
			$timemaxp = $maxpreis;//$unitstats[$temp[type]][current_price] * RES_MAX_MOD_PREIS;
			$timeminp = $miloffers{$temp{type}}{price}  - UNITS_UNTERBIETER;
			
			/*
			pvar($minpreis,minp);
			pvar($maxpreis,maxp);
			
			pvar($timeminp,time);
			pvar($timemaxp,tmax);
			*/
			
			if ($maxpreis >  $timemaxp) $maxpreis = $timemaxp;
			if ($minpreis > $timeminp && $timeminp > 0)  $minpreis = $timeminp; // OLD - Hier größer!
			if ($minpreis > $unitwert * MINPREISGRENZE) $minpreis = $unitwert * MINPREISGRENZE;
			// Absolute Schranken für Extremfälle und dämliche Pusher!
			if ($minpreis < 100) $minpreis = 100;
			if ($maxpreis < 200) $maxpreis = 200;
			if (90000 < $minpreis) $minpreis = 90000;
			if (100000 < $maxpreis) $maxpreis = 100000;
			$produkte[mil][$temp[type]] = array("key" => $temp[type],"name" => "$temp[name]","minpreis" =>$minpreis,"maxpreis" => $maxpreis,"unitwert" => round($unitwert),"unitwert2" => round($unitwert2));
		}
		
		//pvar($spystats);
		foreach ($spystats as $temp) {
			if ($spystats[$temp[type]][current_price] == 0) {
				setUnitStandardprices("spy");
			}
			
			
           $unitwert = ($spystats{$temp[type]}{credits} * $resstats{money}{value}
                        +$spystats{$temp[type]}{energy} * $resstats{energy}{value}* $ausgleichswert);
						
				$unitwert2 = ($spystats{$temp[type]}{credits} * $resstats{money}{value}
                        +$spystats{$temp[type]}{energy} *($resoffers{energy}{price} ? $resoffers{energy}{price}/10 : $resstats{energy}{value} * $ausgleichswert ));
            $maxpreis = $unitwert * UNITS_MAX_PRICE;
            $minpreis = $unitwert * UNITS_MIN_PRICE;
			$timemaxp = $maxpreis; //$spystats[$temp[type]][current_price] * RES_MAX_MOD_PREIS;
			$timeminp = $spyoffers{$temp{type}}{price}  - UNITS_UNTERBIETER;
			$maxpreis >  $timemaxp ? $maxpreis = (int) $timemaxp : 1;
			($minpreis > $timeminp && $timeminp > 0) ? $minpreis = (int) $timeminp : 1; // Old hier größer!
			if ($minpreis > $unitwert * MINPREISGRENZE) $minpreis = $unitwert * MINPREISGRENZE;
			// Absolute Schranken für Extremfälle und dämliche Pusher!
			if ($minpreis < 100) $minpreis = 100;
			if ($maxpreis < 200) $maxpreis = 200;
			if (90000 < $minpreis) $minpreis = 90000;
			if (100000 < $maxpreis) $maxpreis = 100000;
			$produkte[spy][$temp[type]] = array("key" => $temp[type], "name" => $temp[name],"minpreis" =>$minpreis,"maxpreis" => $maxpreis,"unitwert" => round($unitwert),"unitwert2" => round($unitwert2));
		}



	return $produkte;
}

function takeback($offer_id) {
		
		global $status,$queries,$time;
	
        $result = select("select type,prod_id,number,owner_id,price from market where offer_id=$offer_id");
        $offer = mysql_fetch_assoc($result);
        if ($status{id} == $offer{owner_id}) {
            $product = changetype($offer{type},$offer{prod_id});$product=$product{product};
            $action="delete from market where offer_id = $offer_id";
            array_push($queries,$action);
            $action="insert into marketlogs (user_id,owner_id,prod_id,type,number,price,time,action) values (".$status{id}.",'0','".$offer{prod_id}."','".$offer{type}."',".$offer{number}.",".$offer{price}.",$time,'back') ";
            array_push($queries,$action);
            $status{$product}+= $offer{number};
			$lagerbar = energyadd($id,3)-$status[energy];
            $action="update status set $product=$product+".$offer{number}." where id=".$status{id};
            array_push($queries,$action);
            unset($action);
            return "Angebot erfolgreich zurückgenommen<br>";
        }
        else {f("Sie können kein Angebot zurücknehmen, das nicht von Ihnen stammt.<br>");}
	
}


?>
