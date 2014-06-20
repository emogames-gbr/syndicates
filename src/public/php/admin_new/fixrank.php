<?php
//rank fix für runde 52 ranking
echo"file vorher ändern!";
exit();



include("inc/general.php");
$self = "index.php";

$round=54;
select("delete from stats where round=54");
select("delete from honors where round=54");

$syndikate_data = assocs("select synd_id from syndikate", "synd_id");

$statuse=assocs("select * from status");

$sums = array();
foreach ($syndikate_data as $ky => $vl)	{
	$sums["nw"]=0;
	$sums["land"]=0;
	$sumsA = assocs("select land, nw from status where rid=$ky order by nw desc limit 12");
	foreach($sumsA as $abc){
		$sums["nw"]+=$abc['nw'];
		$sums["land"]+=$abc['land'];
	}
	select( "UPDATE `syndikate` SET `nw_ranking` = '" . $sums['nw']. "',`land_ranking` = '" . $sums['land'] . "' WHERE `synd_id` = " . $ky . " LIMIT 1");

}
/*
$status = assocs("select * from status where alive > 0", "id");
$users = assocs("select * from users where konzernid > 0", "konzernid");
$stats = assocs("select * from stats where round = $round and konzernid > 0 and alive > 0", "konzernid");
foreach ($status as $ky => $vl) {
	$updates = array();
	if (!$stats[$ky] && $users[$ky]) {
		select("insert into stats (konzernid, username, rulername, syndicate, race, user_id, rid, alive, round, isnoob) values
					($ky, '".$users[$ky][username]."', '".$status[$ky][rulername]."', '".$status[$ky][syndicate]."', '".$status[$ky][race]."', '".$users[$ky][id]."',
					'".$status[$ky][rid]."', '1', '".$round."', '".$status[$ky][isnoob]."')");
	}
	elseif ($users[$ky]) {
		if ($stats[$ky][username] != $users[$ky][username]) $updates[] = "username='".$users[$ky][username]."'";
		if ($stats[$ky][user_id] != $users[$ky][id]) $updates[] = "user_id='".$users[$ky][id]."'";
		if ($stats[$ky][syndicate] != $status[$ky][syndicate]) $updates[] = "syndicate='".$status[$ky][syndicate]."'";
		if ($stats[$ky][rulername] != $status[$ky][rulername]) $updates[] = "rulername='".$status[$ky][rulername]."'";
		if ($stats[$ky][race] != $status[$ky][race]) $updates[] = "race='".$status[$ky][race]."'";
		if ($stats[$ky][rid] != $status[$ky][rid]) $updates[] = "rid='".$status[$ky][rid]."'";
		if ($updates) select("update stats set ".join(",", $updates)." where konzernid = $ky and round=$round");
	}
}

foreach ($stats as $ky => $vl) {
	if (!$status[$ky]) {
		select("update stats set alive = 0 where konzernid = $ky and round=$round");
	}
}
*/

// stats updaten
$status = assocs("select id, nw, land from status where alive > 0", "id");
$stats = assocs("select largestland, largestnetworth, konzernid from stats where round=".$round, "konzernid");

foreach ($status as $ky => $vl)	{
	select("update stats set lastnetworth=".$vl[nw].",lastland=".$vl[land]." where konzernid=".$vl[id]." and round=".$round);
}
/*
// Honorcodes erzeugen - Einzelranks
$data = assocs("select * from stats where alive > 0 and round=".$round." and isnoob = 0 order by lastnetworth desc limit 100");
$i = 0;
foreach ($data as $ky => $vl)	{
	$i++;
	if ($i == 1): $honorcode = 1; endif;
	if ($i == 2): $honorcode = 2; endif;
	if ($i == 3): $honorcode = 3; endif;
	if ($i >= 4 && $i <= 10): $honorcode = 4; endif;
	if ($i >= 11 && $i <= 30): $honorcode = 5; endif;
	if ($i >= 31 && $i <= 100): $honorcode = 6; endif;
	select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$round.",$honorcode,$i)");
}

$top3syns = assocs("select synd_id AS rid, nw_ranking AS nw from syndikate order by nw desc limit 3");

$currentSynRank = 0;*/
/*
foreach ($top3syns as $temp) {
	
	$currentSynRank++;
	$synHonorCode = 10+$currentSynRank;
	
	if ($currentSynRank > 3) break;
	
	$playersInSyn = assocs("select * from stats where alive > 0 and round=".$round." and rid=".$temp[rid]);
	foreach ($playersInSyn as $ky => $vl) {
		select("insert into honors (user_id, round, honorcode, rank) values (".$vl[user_id].",".$round.",$synHonorCode,$currentSynRank)");
	}
	
}*/

/*

$identifier = "pawldwl2";

$globals = assoc("select * from globals order by round desc limit 1");
$round = $globals[round]-1;

$emopay = array(9=>20,19=>5,28=>5,1=>5);

foreach($emopay as $tag=>$value){
	$playersSyn = assocs("select * from stats where round = $round and alive > 0 and rid=$tag", "user_id");
	foreach($playersSyn as $userid=>$stats){
		$emoid = single("select emogames_user_id from users where id=$userid");
		$emoname = single("select username from users where id=$userid");
		echo"Gebe $emoname $value Emos!<br>";
		EMOGAMES_donate_bonus_emos($emoid,$value,"Nachzahlung Rankemos",$identifier);
	}
}

*/

echo"done";

?>
