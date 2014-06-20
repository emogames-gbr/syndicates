<?php

//include("inc/general.php");
include("../../inc/ingame/game.php");

$seed=substr($_GET['seed'],0,19);
$time=time();

$count = single("select count(*) from flash_cookie_users where statusid=".$status[id]." and seed='".$seed."'");

if(!$count){
	select("INSERT INTO  `flash_cookie_users` (  `id` ,  `statusid` ,  `seed` ,  `firsttime` ,  `lasttime` ) VALUES (NULL ,  '".$status[id]."',  '$seed',  '$time',  '$time')");
	echo"1";
}else{
	select("update `flash_cookie_users` set lasttime='$time' where statusid=".$status[id]." and seed='".$seed."'");
	echo"2";
}
	


?>
