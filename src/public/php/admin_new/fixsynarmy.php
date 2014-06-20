<?php

echo"fix script für runde 52 synarmy not given for randoms";
exit();

include("inc/general.php");
$self = "index.php";

$armygive = array();
$isGroup=array();
$groups=assocs("select * from groups","group_id");
foreach($groups as $group_id=>$group){
	for($i=0;$i<21;$i++){
		if($group['u'.$i] != 0 && $group['u'.$i."_status"] == 0)
			$isGroup[$group['u'.$i]] = 1;
	}
}

pvar($isGroup);

$syndata=assocs("select * from syndikate","synd_id");
foreach($syndata as $rid=>$syn){
	$players=assocs("select * from status where alive=1 and rid=$rid");
	foreach($players as $player){
		if($isGroup[$player['id']]!=1)
			$armygive[$rid]++;
	}
}

foreach($armygive as $rid=>$num){
	echo"Syn#".$rid." - $num Units <br>";
	select("update syndikate set offspecs=".(2000*$num)." where synd_id=".$rid);
	select("update syndikate set defspecs=".(2000*$num)." where synd_id=".$rid);
}

?>