<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//
if ($details){
	$details = floor($details);
}

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

// Header include
require_once("../../inc/ingame/header.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


$hours = date("H");
$minutes = date("i");
$seconds = date("s");
$day_begin_time = $time - $hours * 3600 - $minutes * 60 - $seconds;

$aktienprozente = 0;
$totalaktien = 0;
$aktstring = "";
$tcdata = array();
$temptime = 0;
$temprid = 0;

$news_today = "";
$news_yesterday = "";



//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

if ($time >= $globals['roundstarttime'] && $globals['roundstarttime'] + 5 * 60 > $time) {
	$tpl->assign("ERROR", "Der Zugang zum Aktuellen ist wegen der Zuweisung der Gruppen zu ihren Syndikaten erst 5 Minuten nach Rundenstart möglich.");
	$tpl->display("fehler.tpl");
}
else{
	$allies = row("select ally1,ally2 from syndikate where synd_id=".$status['rid']);
	$tpl->assign("ALLY_1", $allies[0]);
	$tpl->assign("ALLY_2", $allies[1]);
	
	##
	## Einsicht durch Aktien
	##
	$prozent_details = 0;
	$tmp = array();
	$aktien = assocs('select number, synd_id as rid from aktien where user_id = '.$status['id'].' order by synd_id asc','rid');
	foreach($aktien as $key => $value){
		list($anzahl,$prozent,$umlauf) = aktienbesitz($status['id'], $value['rid']);
		
		if($prozent >= AKTIEN_AKTUELLES && $value['rid'] != $status['rid']){
			$tmp[]['rid'] = $value['rid'];
		}
		
		if($details == $value['rid']){
			$prozent_details = $prozent;
		}
	}
	$tpl->assign('AKTIEN', $tmp);
		
	if($details and $details != $status['rid']){
		if ($prozent_details < AKTIEN_AKTUELLES && $details != $allies[0] && $details != $allies[1]) {
			$tpl->assign('ERROR', 'Sie müssen mindestens '.AKTIEN_AKTUELLES.'% der Aktien des gewählten Syndikats bestizen, um dessen Neuigkeiten lesen zu können.');
			$tpl->display('fehler.tpl');
		}
		else {
			$temprid = $details;
			$tpl->assign("SYN_NR", $details);
			$tpl->assign("DETAILS", 1);
		}
	}
	
	
	if(!$temprid){
		$temprid = $status['rid'];
	}
	
	if($realmnummer){
		select("insert into code_stealer_tracing (time, id, phrase) values ('$time', '$id', 'realmnummer = ".floor($realmnummer)."')");
	}
	
	$tcdata = assocs("select time, message, id,kategorie from towncrier where rid=".$temprid." order by time desc");
	
	//							Berechnungen									//
	$katCounts = array(0=>array(),1=>array(),2=>array(),3=>array());
	$running_id = 0;
	foreach ($tcdata as $vl)	{
		$running_id++;
		if ($time - $vl['time'] < ( 60 * 60 * 24 * 2) or $byd == 1)	{
			
			$showok = 1;
			if ($status['isnoob']) {
				if (preg_match("/wirtschaftlichen Interessen/",$vl['message'])) {
					$showok = 0;
				}				
			}
			$vl['message'] = preg_replace("/\(#(\d*)\)/"," (<u><a class=\"linkMenue\" href=\"syndicate.php?rid=$1\">#$1</a></u>)",$vl['message']);
			if ($showok == 1) {
				$temptime = date("H:i", $vl['time']);
				if ($secondson) {
					$temptime = date("H:i:s", $vl['time']);
				}
				if ($vl['time'] > $day_begin_time)	{
					$katCounts[$vl['kategorie']]['ids'][] = $running_id;
					$katCounts[$vl['kategorie']]['kategorie'] = $vl['kategorie'];
					$news_today[] = array( 
											"id" => $running_id,
											"time" => $temptime,
											"message" => $vl['message']
											);
				}
				elseif ($vl[time] >= $day_begin_time - 24 * 3600)	{
					$katCounts[$vl['kategorie']]['ids'][] = $running_id;
					$katCounts[$vl['kategorie']]['kategorie'] = $vl['kategorie'];
					$news_yesterday[] = array( 
											"id" => $running_id,
											"time" => $temptime,
											"message" => $vl['message']
											);
				}
				elseif ($vl[time] >= $day_begin_time - 48 * 3600 && $byd == 1)	{
					$katCounts[$vl['kategorie']]['ids'][] = $running_id;
					$katCounts[$vl['kategorie']]['kategorie'] = $vl['kategorie'];
					$news_before_yesterday[] = array( 
											"id" => $running_id,
											"time" => $temptime,
											"message" => $vl['message']
											);
				}
			}
		}
	
	}
	//							Daten schreiben									//

	if (isset($news_today) && !empty($news_today)){
		$tpl->assign("NEWS_TODAY", $news_today);
	}
	if (isset($news_yesterday) && !empty($news_yesterday)){
		$tpl->assign("NEWS_YESTERDAY", $news_yesterday);	
	}
	if ($news_before_yesterday) {
		$tpl->assign("BEFORE_YESTERDAY", 1);
		$tpl->assign("NEWS_BEFORE_YESTERDAY", $news_before_yesterday);	
	}
	
	$tpl->assign('KATS', $katCounts);
	$tpl->display("aktuelles.tpl");		
}


//**************************************************************************//
//							  Ausgabe, Footer	     						//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
