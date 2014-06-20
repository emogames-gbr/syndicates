<?php
require("../subs.php");
connectdb();

/*
$users = singles("select email from users where lastroundplayed >= 8 || startround < 3");
$users2 = singles("select  emailbefore from options_mailchange");
$users3 = singles("select  emailafter from options_mailchange");
*/

$i=0;

for ($a=1;$a < 12;$a++) {
	$users10 = singles("select email from sa_em".$a."");
	foreach ($users10 as $temp) {
	$i++;
	PMBOX_insert_target($temp,"Starattack-User");
	echo "$a,$i: $temp\n";
}

}

/*
foreach ($users as $temp) {
	$i++;
	PMBOX_insert_target($temp,"Syndicates-User");
	echo "$i: $temp\n";
}
foreach ($users2 as $temp) {
	$i++;
	PMBOX_insert_target($temp,"Syndicates-User");
	echo "$i: $temp\n";
}
foreach ($users3 as $temp) {
	$i++;
	PMBOX_insert_target($temp,"Syndicates-User");
	echo "$i: $temp\n";
}
*/




?>
