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

$basefile = DATA."/published/".$db."_";

$omniput_names = array();
$omniput_values = array();
$omniput_times = array();


###ההההההההההההההההההההההההההההההההההההההההההההההההההההה
#### - Marktdaten schreiben		הההההההההההההההההההההההה
###ההההההההההההההההההההההההההההההההההההההההההההההההההההה

$marketfile_extension = "market.xml";

$marketfile = $basefile.$marketfile_extension;
echo "$marketfile";
$handle = fopen($marketfile,w);
$contentstring = "<marketexport>
					<createtime_timestamp>$time</createtime_timestamp>
					<createtime_date>".date("d.m.y - H:i", $time)."</createtime_date>
";




	$races = assocs("select * from races","race");
	$resstats = getresstats();
	
$dboffers = assocs ("select type,prod_id,price,sum(number) as numbersum from market where inserttime < $time and number > 0 group by type,prod_id,price order by type,prod_id asc,price asc");

foreach ($dboffers as $temp) {
    if (!$alloffers{$temp{type}}{$temp{prod_id}}) {
        $alloffers{$temp{type}}{$temp{prod_id}} = array(number => $temp{numbersum}, price => $temp{price});
    }
}
// Ressourcenangebote
if ($alloffers{res})	{
    foreach ($alloffers{res} as $key => $restemp) {
            $type = changetype("res",$key);
            $resoffers{$type{product}} = array(number => $restemp{number},price => $restemp{price});
            $minprices{$type{product}} = $restemp{price};
    }
}


$contentstring.="<ressources>";
foreach ($resstats as $k => $v) {
	$contentstring.="
		<ressource>
			<name>$v[name]</name>
			<price>".(int)$resoffers[$k][price]."</price>
			<number>".(int)$resoffers[$k][number]."</number>
		</ressource>
	";
	
	// Add Omniput values for resources
	if ((int)$resoffers[$k][number] > 0) {
	  $omniput_names[] = make_omnimon_series_name($db,$globals['round'],'res',$v['name']);
	  $omniput_values[] = $resoffers[$k][price];
	  $omniput_times[] = $time;
	}
	
}
$contentstring.="</ressources>";
	
foreach ($races as $race => $race_complete) {	
		
		$unitstats = getunitstats($race);
		$spystats = getspystats($race);
		

	
        // Militהrangebote
		if ($alloffers{mil})	{
	        foreach ($alloffers{mil} as $key => $miltemp) {
	                $type = changetype("mil",$key);
	                if ($unitstats{$type{product}}{unit_id} == $key) {
	                    $miloffers{$type{product}} = array(number => $miltemp{number},price => $miltemp{price});
	                }
	        }

	        
		}
        // Spionageangebote
		if ($alloffers{spy})	{
	        foreach ($alloffers{spy} as $key => $spytemp) {
	                $type = changetype("spy",$key);
	                if ($spystats{$type{product}}{unit_id} == $key) {
	                    $spyoffers{$type{product}} = array(number => $spytemp{number},price => $spytemp{price});
	                }
	        }
		}
		
		$contentstring.="<race>\n
							<race_name>$race_complete[name]</race_name>
							<military_units>
		";
		foreach ($unitstats as $k => $v) {
			$contentstring.="<unit>
								<name>$v[name]</name>
								<price>".(int)$miloffers[$k][price]."</price>
								<number>".(int)$miloffers[$k][number]."</number>
							</unit>
			
			";
			
	        
    	// Add Omniput values for military
    	if ((int)$miloffers[$k][number] > 0) {
    	  $omniput_names[] = make_omnimon_series_name($db,$globals['round'],'mil',$v['unit_id']);
    	  $omniput_values[] = $miloffers[$k][price];
    	  $omniput_times[] = $time;
    	}			
    	
		}
		$contentstring.="</military_units>
						<spy_units>	";
		
		foreach ($spystats as $k => $v) {
			$contentstring.="<unit>
								<name>$v[name]</name>
								<price>".(int)$spyoffers[$k][price]."</price>
								<number>".(int)$spyoffers[$k][number]."</number>
							</unit>
			
			";
			
    	// Add Omniput values for spies
    	if ((int)$spyoffers[$k][number] > 0) {
    	  $omniput_names[] = make_omnimon_series_name($db,$globals['round'],'spy',$v['unit_id']);
    	  $omniput_values[] = $spyoffers[$k][price];
    	  $omniput_times[] = $time;
    	}			
    			
		}
		
		
		$contentstring.="
			</spy_units>
		</race>";
		unset($miloffers,$spyoffers);
		
}
unset ($alloffers,$dboffers);



$contentstring .="</marketexport>";
fputs($handle,$contentstring);
fclose($handle);


$syns = singles('SELECT synd_id FROM syndikate');
foreach($syns as $tag => $syn){
	unset($preis);
	$preis = single('SELECT preis FROM aktien_gebote WHERE rid = '.$syn.' and action = \'offer\' and time <= '.$time.' ORDER BY preis ASC limit 1');
	if($preis > 0){
		// Aktienkurs
		$omnimon_names_syndikate_stock[] = make_omnimon_series_name($db,$globals['round'],'syn_stock',$syn);
		$omnimon_values_syndikate_stock[] = $preis;
		$omnimon_times_syndikate_stock[]  = $time;
	}
}



omniputs($omnimon_names_syndikate_stock,$omnimon_values_syndikate_stock,$omnimon_times_syndikate_stock,OMNIMON_USER_MASS);


omniputs($omniput_names,$omniput_values,$omniput_times,OMNIMON_USER_MASS);



?>
