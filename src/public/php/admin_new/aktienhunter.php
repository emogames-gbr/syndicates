<?php

include("inc/general.php");
$self = "index.php";


$userId = (int) $_GET["userid"];

$analyse .= "Aktienanalyse von User: $userId <br><br>";

$sql = assocs("SELECT * FROM  aktien_gebote WHERE user_id=".$userId);

pvar($sql);

//case 1
//verkauf und kauf von aktien eines syns in 48h
$analyse .= "<br><h1> Case1 - Vielfach Handel in 2 Tagen</h1><br>";

$analyse .= "<hr>";

//case 2
//vermehrter handel zwischen zwei spielern
$analyse .= "<br><h1> Case2 - Regelmäßige Handelspartner (Aktionen > 10)</h1><br>";

$result = assocs("SELECT offer_id,need_id,count(*) 
	FROM aktien_logs 
	WHERE offer_id=$userId or need_id=$userId 
	GROUP BY offer_id,need_id HAVING COUNT(*) > 10");
	
foreach($result as $handel){
	$analyse .= "KäuferId: ".$handel["need_id"]." VerkäuferId: ".$handel["offer_id"]." Vorkommen: ".$handel["count(*)"]."<br>";
}

$analyse .= "<hr>";

//case 3
//kauf/verkauf = 0,5
$analyse .= "<br><h1> Case3 - Verkauf/Kaufpreise vergleichen</h1><br>";

$result = assocs("SELECT rid,offer_id,need_id,(sum(preis * menge) / sum(menge)) 
	FROM aktien_logs 
	WHERE offer_id=$userId 
	GROUP BY rid, offer_id","rid");
	
$result2 = assocs("SELECT rid,offer_id,need_id,(sum(preis * menge) / sum(menge)) 
	FROM aktien_logs 
	WHERE need_id=$userId 
	GROUP BY rid, need_id","rid");
	
$syndata = array();

foreach($result2 as $item){
	$syndata[$item["rid"]]["offer"] = $item["(sum(preis * menge) / sum(menge))"];
}

foreach($result as $item){
	$syndata[$item["rid"]]["buy"] = $item["(sum(preis * menge) / sum(menge))"];
}

foreach($syndata as $tag=>$value){

	$color = "";
	//gleiches Syn also preise vergleichen
	if( ($value["offer"] && $value["buy"]) && (($value["offer"] / $value["buy"] >= 2) || ($value["buy"] / $value["offer"] >= 2)))
		$color = "color=red"; //auffällig	
	$analyse .= "<font ".$color.">Syn#".$tag." - Kauft durschnittlich für ".$value["offer"]." und verkauft durchschnittlich für ".$value["buy"]."</font><br>";	

}

echo $analyse;