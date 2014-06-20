<?php

echo"file vorher ändern!<br><br>";
exit();
include("inc/general.php");
$self = "index.php";
$identifier = "SOME_PASSWORD";

$giveemos = array(SOME_STRING"=>120);

foreach($giveemos as $name=>$emos){
	$emoid = single("select emogames_user_id from users where username='$name'");
	EMOGAMES_donate_bonus_emos($emoid,$emos,"Wiedergutmachung",$identifier);
	echo"$emos Emos gezahlt an $name with userid:$emoid <br>";
}
//echo"done";	

$year=array(66);
$comp =array(661,94,194,332,45,386,141,72,531,310);
$fos=array(77,272,605,497,148,336,362,635,668,98,8,178,459,173,491,280,511,32,542,234,60,497,27,505,396);

echo "Jahres-Komplett: ".count($year)."<br><br>";
foreach($year as $item){
	$emoid = single("select emogames_user_id from users where konzernid=$item");
	$username = single("select username from users where konzernid=$item");
	echo $username." (".$emoid.")<br>";
	EMOGAMES_donate_bonus_emos($emoid,2016,"Jahres-Komplettpaket gewonnen!",$identifier);
}
echo"<br>";

echo "Komplett: ".count($comp)."<br><br>";
foreach($comp as $item){
	$emoid = single("select emogames_user_id from users where konzernid=$item");
	$username = single("select username from users where konzernid=$item");
	echo $username." (".$emoid.")<br>";
	EMOGAMES_donate_bonus_emos($emoid,480,"Komplettpaket gewonnen!",$identifier);
}
echo"<br>";

echo "Fosaassi: ".count($fos)."<br><br>";
foreach($fos as $item){
	$emoid = single("select emogames_user_id from users where konzernid=$item");
	$username = single("select username from users where konzernid=$item");
	echo $username." (".$emoid.")<br>";
	EMOGAMES_donate_bonus_emos($emoid,120,"Forschungsassistent gewonnen!",$identifier);
}
echo"<br>done.";
*/

/*
foreach($giveemos as $name=>$emos){
	$emoid = single("select emogames_user_id from users where username='$name'");
	EMOGAMES_donate_bonus_emos($emoid,$emos,"Pizzaessen",$identifier);
	echo"$emos Emos gezahlt an $name with userid:$emoid <br>";
}
echo"done";	*/

?>
