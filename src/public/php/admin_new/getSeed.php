<?php

include("inc/general.php");


$seed=$_GET['seed'];
$status=$_GET['statusid'];

if($seed){
	$data = assocs("select * from flash_cookie_users where seed='$seed'");	
	foreach($data as $item){
		$round=single("select round from globals where roundendtime>".$item[firsttime]." and roundstarttime <".$item[firsttime]);
		$konzname= single("select syndicate from stats where konzernid=$item[statusid] and round=$round");
		$username= single("select username from stats where konzernid=$item[statusid] and round=$round");
		$userid= single("select user_id from stats where konzernid=$item[statusid] and round=$round");
		$synnr=single("select rid from status where id=$item[statusid]");	
		echo "Runde:".($round-2)." User: $username Userid: $userid<br>";
		//echo "<a href=\"getSeed.php?statusid=$item[statusid]\">".$konzname."</a> (#".$synnr.")";
		if(strrpos($item[seed], $userid.'x')===false) echo "<h1>!!!!!!!!</h1>";
		//echo"<br>";
		echo "Seed: <a href=\"getSeed.php?seed=$item[seed]\">$item[seed]</a> <br>";
		echo "Create: ".mytime($item[firsttime])." Last: ".mytime($item[lasttime])."<br><hr><br>";	
		/*echo single("select syndicate from status where id=$item[statusid]")." (#".single("select rid from status where id=$item[statusid]").")<br>";
		echo "Seed: $seed <br>";
		echo "Create: ".mytime($item[firsttime])." Last: ".mytime($item[lasttime])."<br><hr><br>";	*/
	}
} elseif($status){
	$data = assocs("select * from flash_cookie_users where statusid=$status");	
	foreach($data as $item){
		echo single("select syndicate from status where id=$item[statusid]")." (#".single("select rid from status where id=$item[statusid]").")<br>";
		echo "Seed: $item[seed] <br>";
		echo "Create: ".mytime($item[firsttime])." Last: ".mytime($item[lasttime])."<br><hr><br>";	
	}
} else {
	$data = assocs("select * from flash_cookie_users where seed<>'' order by seed");
	$old='';
	foreach($data as $item){
		$round=single("select round from globals where roundendtime>".$item[firsttime]." and roundstarttime <".$item[firsttime]);
		$konzname= single("select syndicate from stats where konzernid=$item[statusid] and round=$round");
		$username= single("select username from stats where konzernid=$item[statusid] and round=$round");
		$userid= single("select user_id from stats where konzernid=$item[statusid] and round=$round");
		$synnr=single("select rid from status where id=$item[statusid]");	
		echo "Runde:".($round-2)." User: $username Userid: $userid<br>";
		//echo "<a href=\"getSeed.php?statusid=$item[statusid]\">".$konzname."</a> (#".$synnr.")";
		if(strrpos($item[seed], $userid.'x')===false) echo "<h1>!!!!!!!!</h1>";
		//echo"<br>";
		echo "Seed: <a href=\"getSeed.php?seed=$item[seed]\">$item[seed]</a> <br>";
		echo "Create: ".mytime($item[firsttime])." Last: ".mytime($item[lasttime])."<br><hr><br>";	
		$old=$item[seed];
	}
}

?>
