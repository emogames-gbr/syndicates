<?php

set_time_limit(0);

require ("../includes.php");

$handle = connectdb();
$time = time();
$timeASSI = $time + 5 * 60 *60;
$queries = array();

$globals = assoc("select * from globals order by round desc limit 1");

require(INC."ingame/globalvars.php");

if($globals['roundstatus'] != 1)
	exit();

if($globals['updating'] == 1)
	exit();
	
//$minute = date("i",$time);
//if ($minute == 59 || $minute == 00 || $minute == 1 || $minute == 2) exit();

//if (!file_exists("boerse_update".$db)) {

	//$file = fopen("boerse_update".$db,w);
	//fputs($file,"1");
	//fclose($file);

if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

$getAllSyndicate = "SELECT * FROM `syndikate`";

$allSyndicate = assocs($getAllSyndicate);

foreach($allSyndicate as $syndicate){

	$synId = $syndicate['synd_id'];

	$getAktienGebote = "
		SELECT 
			*
		FROM 
			`aktien_gebote`
		WHERE 
			`aktien_gebote`.action = 'assi' AND
			`aktien_gebote`.rid = $synId AND
			`aktien_gebote`.time <= $timeASSI
		ORDER BY 
			`aktien_gebote`.preis DESC
	";
	
	$getAktienAngebote = "
		SELECT 
			*
		FROM 
			`aktien_gebote`
		WHERE 
			`aktien_gebote`.action = 'offer' AND
			`aktien_gebote`.rid = $synId
		ORDER BY 
			`aktien_gebote`.preis ASC
	";
	
	$getMoneyData = "SELECT id,money FROM `status`";
	
	$gebote = assocs($getAktienGebote);
	$angebote = assocs($getAktienAngebote);
	$money = assocs($getMoneyData,"id");
	/*echo "\n\nGebote:\n";
	print_r($gebote);
	echo "\n\Angebote:\n";
	print_r($angebote);*/
	
	$actionPossible = ($gebote ? true : false) && ($angebote ? true : false);
	
	$iGeboteMax = count($gebote);
	$iAngeboteMax = count($angebote);
	
	$iGebote = 0;
	$iAngebote = 0;
	
	echo "\n=========================\nSyndicate $synId - Handel: $actionPossible\n\n";
	
	while( $iGebote < $iGeboteMax  && $iAngebote < $iAngeboteMax && $actionPossible){
	
		$currentGebot = $gebote[$iGebote];
		$currentAngebot = $angebote[$iAngebote];
		
		if( $currentGebot['preis'] < $currentAngebot['preis'] ){
			echo "\nACTION NOT POSSIBLE\n\n";
			$actionPossible = false;
			
		} else{
		
			$buyableNumber = floor($money[$currentGebot['user_id']]['money'] / $currentGebot['preis']);
			$buyableNumber = $buyableNumber < $currentGebot['number'] ? $buyableNumber : $currentGebot['number'];
			$buyableNumber = $buyableNumber < aktien_buyable($currentGebot['user_id']) ? $buyableNumber : aktien_buyable($currentGebot['user_id']);
			$buyableNumber = $buyableNumber < 0 ? 0 : $buyableNumber;
			$sellableNumber = $currentAngebot['number'];
			if($buyableNumber == 0 || $currentGebot['user_id'] == $currentAngebot['user_id'] ){
				$iGebote++;
			} elseif( $buyableNumber <= $sellableNumber ){
				
				$money[ $currentGebot['user_id'] ]['money'] -= $buyableNumber * $currentGebot['preis'];
				update_credits($currentGebot['user_id'], -$buyableNumber * $currentGebot['preis']);
				update_aktien("add", $currentGebot['user_id'], $buyableNumber, $synId, $currentGebot['preis']);
				
				$money[ $currentAngebot['user_id'] ]['money'] += $buyableNumber * $currentGebot['preis'];
				// Steuer auf den Verkauf von Aktien - R4bbiT - 24.03.12
				$einnahmen = pay_aktien_tax($currentAngebot['user_id'], $synId, $buyableNumber, $currentGebot['preis']);
				$price_once = get_aktien_invprice($currentAngebot['user_id'], $currentGebot['rid']);
				update_credits($currentAngebot['user_id'], $einnahmen);
				update_aktien("sub", $currentAngebot['user_id'], $buyableNumber, $synId, ($price_once > 0 ? $price_once : $currentGebot['preis']));
				
				$angebote[$iAngebote]['number'] -= $buyableNumber;
				select("UPDATE aktien_gebote SET number = ".$angebote[$iAngebote]['number']." WHERE id = ".$angebote[$iAngebote]['id']);
				
				$gebote[$iGebote]['number'] -= $buyableNumber;
				select("UPDATE aktien_gebote SET number = ".$gebote[$iGebote]['number']." WHERE id = ".$gebote[$iGebote]['id']);
				
				if( $angebote[$iAngebote]['number'] == 0)
					select("DELETE FROM aktien_gebote WHERE id = ".$angebote[$iAngebote++]['id']);

				if( $gebote[$iGebote]['number'] == 0)
					select("DELETE FROM aktien_gebote WHERE id = ".$gebote[$iGebote++]['id']);
				
				aktien_log($currentAngebot['user_id'], $currentGebot['user_id'], $synId, $currentGebot['preis'], $buyableNumber, $einnahmen);
				
			} else{ //$buyableNumber > $sellableNumber 
				
				$money[ $currentGebot['user_id'] ]['money'] -= $sellableNumber * $currentGebot['preis'];
				update_credits($currentGebot['user_id'], -$sellableNumber * $currentGebot['preis']);
				update_aktien("add", $currentGebot['user_id'], $sellableNumber, $synId, $currentGebot['preis']);
				
				$money[ $currentAngebot['user_id'] ]['money'] += $sellableNumber * $currentGebot['preis'];
				$einnahmen = pay_aktien_tax($currentAngebot['user_id'], $synId, $sellableNumber, $currentGebot['preis']);
				$price_once = get_aktien_invprice($currentAngebot['user_id'], $currentGebot['preis']);
				update_credits($currentAngebot['user_id'], $einnahmen);
				update_aktien("sub", $currentAngebot['user_id'], $sellableNumber, $synId, ($price_once > 0 ? $price_once : $currentGebot['preis']));
				
				$gebote[$iGebote]['number'] -= $sellableNumber;
				select("UPDATE aktien_gebote SET number = ".$gebote[$iGebote]['number']." WHERE id = ".$gebote[$iGebote]['id']);
				
				select("DELETE FROM aktien_gebote WHERE id = ".$angebote[$iAngebote++]['id']);
				
				aktien_log($currentAngebot['user_id'], $currentGebot['user_id'], $synId, $currentGebot['preis'], $sellableNumber, $einnahmen);
				
			}
			
		}
	
	}

}


function aktien_log($offer_id, $need_id, $rid, $preis, $menge, $einnahmen){

	global $time;
	$tax = 0;
	$tax = ($preis * $menge) - $einnahmen;
	if($tax > 0){
		$string = pointit($menge).'|'.$rid.'|'.pointit($einnahmen).'|'.pointit($preis).'|'.pointit($tax);
		$mid = 64;
	}
	else{
		$string = pointit($menge).'|'.$rid.'|'.pointit($menge * $preis).'|'.pointit($preis);
		$mid = 58;
	}
	select("INSERT INTO message_values (id,user_id,time,werte) VALUES (".$mid.", ".$offer_id.", ".$time.", '".$string."')");
	
	$string = pointit($menge * $preis).'|'.pointit($menge).'|'.$rid.'|'.pointit($preis);
	select("INSERT INTO message_values (id,user_id,time,werte) VALUES (59, ".$need_id.", ".$time.", '".$string."')");				
		
	$insertLog = "
		INSERT 
		INTO aktien_logs
			(offer_id, need_id, rid, preis, menge, time, tax)
		VALUES
			('".$offer_id."', '".$need_id."', '".$rid."', '".$preis."', '".$menge."', '".$time."', '".$tax."')";
			
	select($insertLog);
}

function update_credits($id, $money){
	
	$curMoney = single("select money from status where id =".$id);
	$money = $curMoney + $money;
	select("UPDATE status SET money = ".$money." WHERE id =".$id);
	
}

?>