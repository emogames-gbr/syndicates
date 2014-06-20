<?


set_time_limit(0);
require ("../includes.php");
$handle = connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require(INC."/ingame/globalvars.php");
$time = time();

$globals = assoc("select * from globals order by round desc limit 1");

if($globals['updating'] == 1)
	exit();

// Skript nicht ums update rum laufen lassen - kann sonst probleme mit einheiten kapazitäten geben zwichenzeitlich.
//$minute = date("i",$time);
//if ($minute == 59 || $minute == 00 || $minute == 1 || $minute == 2) exit();

//
// Just as a minutely cronjob delete marketbuffer to prevent locks
select("TRUNCATE TABLE `market_buffer` ");
select("delete from market where number < 0"); // shouldnt happen
///// Minutely cronjob


$queries = array();


if (!file_exists("market_gebote_running".$db)) {
	$file = fopen("market_gebote_running".$db,w);
	fputs($file,"1");
	fclose($file);

	$servers = assocs("select * from servers");
	
	foreach ($servers as $temp) {
		mysql_select_db($temp[db_name]);

		$queries = array();



		/////////////////////
		// Daten sammeln
		/////////////////////
		$gebote = assocs("select * from market_gebote order by type,prod_id asc ,price desc ,time asc");
		$angebote = assocs("select * from market where inserttime <= ".($time+360)." order by type,prod_id asc ,price asc");
		
		$statuses = assocs("select id,money,race,land,depots,spylabs,offspecs,defspecs,elites,elites2,techs,intelspies,defspies,offspies from status where alive = 1","id");
		$status = array();
		$sciences = array();
		$artefakte = get_artefakte();
		
		$sciences_rohdaten = assocs("select * from usersciences");
		foreach ($sciences_rohdaten as $vl)	{
			$scienceses[$vl[user_id]][$vl[name]] = $vl[level];
		}
		
		$buildtime = get_hour_time($time) + MARKET_BUILD_TIME;
		
		$resstats = getresstats();
		
		
		
		// Gebote gegen Angebote matchen
		
		
		
		/////////////////////
		// Gebote durchlaufen
		/////////////////////
		$anzahl_angebote = count($angebote);
		$anzahl_gebote = count($gebote);
		$n = 0;
		$last_offer_same_type_mark = 0;
		$last_offer_same_prod_id = 0;
		
		
		
		foreach ($gebote as $gebotkey => $gebot) {
			
			/////////////////////
			// Parallel Angebote durchlaufen
			/////////////////////
			$i = max($last_offer_same_type_mark,$last_offer_same_prod_id);
			echo "Neuer Durchlauf, i fängt an bei: $i\n";
			while ($i < $anzahl_angebote) {
				$n++;
				
				// Aktueller Typ ist kleiner als angebotstyp, aus schleife aussteigen, nächstes gebot nehmen
				if (cmptype($gebot[type],$angebote[$i][type]) < 0) {
					break;
				}
				// Aktueller Typ ist größer als angebotstyp, 
				elseif (cmptype($gebot[type],$angebote[$i][type]) > 0) {
					$i++;
					$last_offer_same_type_mark = $i; // Marke setzen, damit kleinere Typen gar nicht mehr beachtet werden
					continue;
				}
				
				// Ab hier gleiche Stufe der Typen garantiert
				
				if ($gebot[prod_id] < $angebote[$i][prod_id]) {
					break; // Für das aktuelle Gebot kann es keine Angebote mehr geben
				}
				elseif ($gebot[prod_id] > $angebote[$i][prod_id]) {
					$i++;
					$last_offer_same_prod_id = $i;
					continue;
				}
				
				// Ab hier gleiche Typen und gleiche Stufe gewährleistet, Gebote kommen vom höchsten Preis her, Angebote vom niedrigsten
				
				
				
				if ($gebot[prod_id] == $angebote[$i][prod_id] && $gebot[type] == $angebote[$i][type] && $angebote[$i][price] <= $gebot[price] && $angebote[$i][price] > 0 && $gebote[$gebotkey][number] > 0 && $angebote[$i][number] > 0) {
					//echo "Angebot nummer vorher: ".$angebote[$i][number]."\n";
					$anzahl_bought = buy($gebote[$gebotkey],$angebote[$i],$statuses,$gebotkey,$scienceses);
					if ($anzahl_bought > 0) {
						db_write($queries);
						// Modifizierte Tabellendaten aktualisieren
						/*
						echo "Käufer:\n";
						print_r($statuses[$gebot[user_id]]);
						*/
						$statuses[$gebot[user_id]] = assoc("select * from status where id = ".$gebot[user_id]."");
						/*
						print_r($statuses[$gebot[user_id]]);
						echo "Verkäufer:\n";
						print_r($statuses[$angebote[$i][owner_id]]);
						*/
						$statuses[$angebote[$i][owner_id]] = assoc("select * from status where id = ".$angebote[$i][owner_id]."");
						/*
						print_r($statuses[$angebote[$i][owner_id]]);
						echo "Gebot:\n";
						print_r($gebote[$gebotkey]);
						*/
						$gebote[$gebotkey] = assoc("select * from market_gebote where gebot_id=".$gebot[gebot_id]."");
						/*
						print_r($gebote[$gebotkey]);
						echo "Angebot:\n";
						print_r($angebote[$i]);
						*/
						$angebote[$i] = assoc("select * from market where offer_id = ".$angebote[$i][offer_id]."");
						//print_r($angebote[$i]);
						
					}
					$queries = array();
					//echo "Angebot nummer nacher: ".$angebote[$i][number]."\n";
				}
				
				
				$i++;
			}
				
			
		}
	} // Foreach servers








echo "
	Anzahl Angebote:$anzahl_angebote\n
	Anzahl Gebote:$anzahl_gebote\n
	Durchläufe: $n\n
";


select("delete from market_gebote where number < 0");


system("rm market_gebote_running".$db);

} // Wenn skript nicht gerade läuft
else {
	$betreff = "Marketscript läuft länger als eine Minute";
	$message = "$time\n";
	$email ="admin@domain.de";
	$to = "admin@domain.de";
	sendthemail($betreff,$message,$email,$to);

}



//
// Sortierfunktion für gebote nach type und produkt id
//

function cmptype($type1,$type2) {
	$types = array(
		mil => 1,
		res => 2,
		spy => 3,
	);	
	
	if ($type1 == $type2) {
		return 0;
	}
	elseif ($types[$type1] < $types[$type2]) {
		return -1;
	}
	else {
		return 1;
	}
}




function buy(&$gebot,$angebot,&$statuses,$gebotkey,&$scienceses) {

	global $queries,$gebote,$angebote,$i,$time,$unitstats,$resstats,$spystats,$status,$sciences,$buildtime, $partner,$minprices,$produkte;
	
	// Aktuelle Daten bei Kaufvorgang holen!
	$statuses[$gebot[user_id]] = assoc("select * from status where id = ".$gebot[user_id]."");
	$statuses[$angebote[$i][owner_id]] = assoc("select * from status where id = ".$angebote[$i][owner_id]."");
	$gebote[$gebotkey] = assoc("select * from market_gebote where gebot_id=".$gebot[gebot_id]."");
	$angebote[$i] = assoc("select * from market where offer_id = ".$angebote[$i][offer_id]."");

	
	$status = $statuses[$gebot[user_id]];
	$sciences = $scienceses[$gebot[user_id]];
	$partner = getpartner($status[id]);
	$game_syndikat = assoc("select * from syndikate where synd_id = $status[rid]");
	/*
	print_r("Status:$status\nSciences:$sciences");
	print_r($status);
	print_r($sciences);
	*/
	print "-----------------------------------\nBuy called!\n";
	/*
	print_r($gebot);
	print("Matching:");
	print_r($angebot);
	*/

	
	if ($gebot{type} == "res") {$buymult=10;}
	else {
		$buymult=1;
	}
	$number_to_buy = floor ($statuses[$gebot[user_id]][money]*$buymult / $gebot[price]);

	if ($number_to_buy < 0) $number_to_buy = 0;
	$number_to_buy > $angebot[number] ? $number_to_buy = $angebot[number] : 1;
	if ($number_to_buy < 0) $number_to_buy = 0;
	$number_to_buy > $gebot[number] ? $number_to_buy = $gebot[number] : 1;
	if ($number_to_buy < 0) $number_to_buy = 0;
	$price_to_pay = $gebot[price] * $number_to_buy;
	$orignumbertobuy = $number_to_buy;


	$price_to_pay1 = $price_to_pay;
	$number_to_buy1 = $number_to_buy;
	if ($gebot{type} == "res") {$price_to_pay /= 10;}
	elseif ($gebot[type] == "mil") {
			$maxunits = maxunits(mil,$game_syndikat);
	        $miltotal = miltotal($gebot[user_id]);
	        
			
			// andere Fraktionnen haben keine Carrier 21.8.10 by Christian
			if( $statuses[$gebot[user_id]][race] != "nof")
				$totalCarriers = 0;
			else
				$totalCarriers = carriertotal($gebot[user_id]); //by Christian 20.8.10
			
			$product_temp = changetype($gebot[type],$gebot[prod_id]);
			$product_temp = $product_temp[product];

			$space_left = $maxunits - ($miltotal - $totalCarriers);
			if ($status['race'] == "nof" && $product_temp == "elites") {
				 $space_left = $maxunits - $totalCarriers;
			}
	        
            if ($number_to_buy > $space_left) {
            	// @todo Hier vielleicht $miltotal hochzählen ??? Testläufe fahren
            	$number_to_buy = $space_left;
            	$price_to_pay = $gebot[price] * $number_to_buy;
            }
			//print_r($number_to_buy."ntb\n");
	}
	elseif ($gebot[type] == "spy") {
			$maxspies = maxunits(spy,$game_syndikat);
            $spiestotal = spiestotal($gebot[user_id]);
            /*
			print_r($maxspies."maxspies\n");
			print_r($spiestotal."stotal\n");
			print_r($number_to_buy."ntb\n");
			*/
            if (($spiestotal + $number_to_buy) > $maxspies) {
            	$number_to_buy = $maxspies - $spiestotal;
            	$price_to_pay = $gebot[price] * $number_to_buy;
            }
			//print_r($number_to_buy."ntb\n");
	}
	$price_to_pay2 = $price_to_pay;
	$number_to_buy2 = $number_to_buy;
	
	print "Number to buy: $number_to_buy\n";
	print "Price to pay: $price_to_pay\n";
	
	// Es wird gekauft
	
	
	// Nochmal sicherheitschecks, die sollten aber eigentlich nicht nötig sein...
	if ($number_to_buy > $orignumbertobuy) {
		$number_to_buy = $orignumbertobuy; $price_to_pay = $gebot[price] * $number_to_buy;
		if ($gebot{type} == "res") {$price_to_pay /= 10;}
	}
	$number_to_buy < 0 ? $number_to_buy = 0: 1;
	
	
	if ($number_to_buy > 0) {
		$number_to_buy = floor ($number_to_buy);
		$price_to_pay = ceil ($price_to_pay);
		$orignumber = $gebot[number];
		
		
		$product = changetype($gebot[type],$gebot[prod_id]);
		$product = $product[product];
		
		
		
		// Gutschrift an Käufer - bei Einheiten bautable...

		if ($gebot[type] == "res") {	
			$queries[] = "update status set money=money-$price_to_pay,$product=$product+$number_to_buy where id=$gebot[user_id]";
		}
		else {
			$queries[] = "update status set money=money-$price_to_pay where id=$gebot[user_id]";
			$gebot[type] == "mil" ? $table = "build_military" : $table = "build_spies";
			$queries[] = 
				"insert into $table 
				(unit_id,user_id,number,time)
				values
				($gebot[prod_id],$gebot[user_id],$number_to_buy,".($buildtime).")
			";
		}
		
		
		// Verkäufer erhält in jedem Fall Geld
		$queries[] = "update status set money=money+$price_to_pay where id=$angebot[owner_id]";
		$moneybefore = $statuses[$gebot[user_id]][money];
		//$statuses[$gebot[user_id]][money] -= $price_to_pay;
		$moneyafter = $statuses[$gebot[user_id]][money] - $price_to_pay;
		
		// Angebot updaten
		if ($number_to_buy == $angebot[number]) {
			$queries[] = "delete from market where offer_id=$angebot[offer_id]";
		}
		else {
			$queries[] = "update market set number=number-$number_to_buy where offer_id=$angebot[offer_id]";
		}
		
		// Gebot updaten
		if ($number_to_buy >= $orignumber) {
			$queries[] = "delete from market_gebote where gebot_id=$gebot[gebot_id]";
		}
		else {
			$queries[] = "update market_gebote set number=number-$number_to_buy where gebot_id=$gebot[gebot_id]";
		}
		
		// Benachrichtigungen schreiben an Käufer und Verkäufer
	        // Produktname bestimmen
	        
	        // OPTIMIZE
			$unitstats = getunitstats($statuses[$gebot[user_id]][race]);
			$spystats = getspystats($statuses[$gebot[user_id]][race]);
	        
		//Berechnungen für Resspreise by Christian 19.8.10
		if(!$minprices){

			$dboffers = assocs ("select type,prod_id,price,sum(number) as numbersum from market where inserttime < $time and number > 0 group by type,prod_id,price order by type,prod_id asc,price asc");
	
	        foreach ($dboffers as $temp) {
	            if (!$alloffers{$temp{type}}{$temp{prod_id}}) {
	                $alloffers{$temp{type}}{$temp{prod_id}} = array(number => $temp{numbersum}, price => $temp{price});
	            }
	        }
			
			if ($alloffers{res})	{
		        foreach ($alloffers{res} as $key => $restemp) {
		                $type = changetype("res",$key);
		                $resoffers{$type{product}} = array(number => $restemp{number},price => $restemp{price});
		                $minprices{$type{product}} = $restemp{price};
		        }
			}
			
		}
		
		//Berechnungen für Resspreise by Christian 20.8.10		
		if(!$produkte[ $statuses[$gebot[user_id]][race] ]){
			foreach ($unitstats as $temp) {
			   $unitwert = ($unitstats{$temp[type]}{credits} * $minprices{money}
							+$unitstats{$temp[type]}{energy} * $minprices{energy} / 10
							+$unitstats{$temp[type]}{sciencepoints} * $minprices{sciencepoints} / 10
							+$unitstats{$temp[type]}{minerals} * $minprices{metal} / 10);
				
							
				 if ($temp[race] == "nof" && $temp[type] == "techs") {
					$unitwert += BEHEMOTH_RANGERCOUNT*$produkte['elites']['unitwert'];
				 }
				$produkte[$temp[race]][$temp[type]] = round($unitwert);		
			}

			foreach ($spystats as $temp) {	
			   $unitwert = ($spystats{$temp[type]}{credits} * $minprices{money}
							+$spystats{$temp[type]}{energy} * $minprices{energy} / 10);
			   
				$produkte[$temp[race]][$temp[type]] = round($unitwert);
			}
		}
		
		//100%Preis errechnen by Christian 19.8.10
		if ($gebot{type} == "res")
			$price100 = $minprices[$product];
		else
			$price100 = $produkte[ $statuses[$gebot[user_id]][race] ][$product];
		$price100per=(round($gebot{price}/$price100*1000)/10);
		//Ende 100%Preis
	        if ($gebot{type} == "res") {$prodname1 = $resstats{$product}{name}; $mes_kat=56; $output=$prodname1;}
	        elseif($gebot{type} == "mil") {
				$prodname1 = $unitstats{$product}{name}; $mes_kat=55;
				$owner_race = single("select race from status where id=".$angebot[owner_id]);
				$owner_usta = getunitstats($owner_race);
				$output = $owner_usta{$product}{name};
			
			}
	        else {$prodname1 = $spystats{$product}{name}; $mes_kat=55; $output=$prodname1;}
	        // Nachricht an Anbieter
	        if ($gebot[price] > $angebot[price]) {
	        	$addvalue = "Da Sie an einen Spieler verkaufen konnten, der ein Angebot abgegeben hatte, fällt Ihr Gewinn etwas höher aus, als erwartet.";
	        }
	        else {
	        	$addvalue = "";
	        }
			$stueckpreis = (($price_to_pay/$number_to_buy >= 1000) ? pointit($price_to_pay/$number_to_buy) : number_format(($price_to_pay/$number_to_buy),1));
	        $message_anbieter = "Es wurden <i>".pointit($number_to_buy)." $output</i> von ihnen gekauft. Sie haben dadurch <b>".pointit($price_to_pay)." Cr</b> eingenommen (Stückpreis: ".$stueckpreis."). $addvalue";
	        $message_kaufer = "Sie haben <i>".pointit($number_to_buy)." $prodname1</i> für <b>".pointit($price_to_pay)." Cr</b> auf dem Weltmarkt erworben (Stückpreis: ".$stueckpreis.").";
	        $queries[] = "insert into message_values
	        			 (id,user_id,time,werte)
	        			values
	        			 ($mes_kat,$angebot[owner_id],$time,'$message_anbieter')
	        ";
	        $queries[] = "
						insert into message_values	
	        			 (id,user_id,time,werte)
	        			values
	        			 ($mes_kat,$gebot[user_id],$time,'$message_kaufer')	        
	        ";
 
	        
	    // Logs schreiben //add price100, price100percentage by Christian 19.8.10
        $queries[]="insert into marketlogs (user_id,owner_id,time,prod_id,type,price,number,action,byscript,price100,price100percentage) values ($gebot[user_id],'".$angebot[owner_id]."',$time,'".$gebot{prod_id}."','".$gebot{type}."','".$gebot{price}."','".$number_to_buy."','buy',1,'".$price100."','".$price100per."')";		
        $queries[] = "insert into market_gebote_logs
        	(gebot_id,gebot_user_id,angebot_user_id,type,produkt_id,number,price,angebot_price,buytime,number_to_buy1,number_to_buy2,price_to_pay1,price_to_pay2,buyer_status_money_before,buyer_status_money_after)
        	values
        	($gebot[gebot_id],$gebot[user_id],$angebot[owner_id],'$gebot[type]',$gebot[prod_id],$gebot[number],$gebot[price],$angebot[price],$time,'$number_to_buy1','$number_to_buy2','$price_to_pay1','$price_to_pay2',$moneybefore,$moneyafter)
        ";
     
        
	}

	/*	
	echo "Number to buy: $number_to_buy\n";
	echo "\n\n";
	*/
	unset($partner);
	return $number_to_buy;
}
function carriertotal($id) {

//Neue Carrierermittlung by Christian 20.8.10

    $intern = getallvalues($id);
    $away_intern = getaway($id);
    $market_intern = getmarket($id);

	//Carrier vorhanden
    $statuscarr =  $intern{elites};
  
    //Carrier in bau
	$result = select("select sum(number) from build_military where (unit_id=24 or unit_id=40) and user_id = ".$id);
	if (mysql_num_rows($result) > 0) {$buildcarr = mysql_fetch_row($result); $buildcarr = $buildcarr[0];}
	else {$buildcarr = 0;}

    //Carrier away
    $awaycarr = $away_intern{elites};

    // Militä auf markt
    $marketcarr = $market_intern{elites};

	return ($statuscarr+$buildcarr+$awaycarr+$marketcarr);

}
?>
