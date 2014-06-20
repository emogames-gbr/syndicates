<?
include("inc/general.php");

/*select("INSERT INTO  `admin_pages` (  `id` ,  `kategorie` ,  `showposition` ,  `name` ,  `dateiname` ,  `visible` ,  `privilege_level` ) 
VALUES (
NULL ,  'Adminstuff',  '4',  'Featuretool',  'featureTool.php',  '1',  '3'
)");

echo "INSERT INTO  `admin_pages` (  `id` ,  `kategorie` ,  `showposition` ,  `name` ,  `dateiname` ,  `visible` ,  `privilege_level` ) 
VALUES (
NULL ,  'Adminstuff',  '4',  'Featuretool',  'featuereTool.php',  '1',  '3'
)";

exit;*/

$self = "index.php";

$time = time();
$time2 = time()+5*365*24*60*60;
$userThis = (int) $_GET['konzid'];

if ($userThis==0){
	echo"Setzt alle Features für User mit KonzernID (für 5 Jahre):
	<form action=\"featureTool.php\" method=\"get\"><input type=\"text\" name=\"konzid\" value=\"konzid\"><input type=\"submit\" value=\"start\"></form>
	Diese könnten evtl. wieder überschrieben werden. Müsste ich im Detail nachgucken. Bogul. März 2012";
	exit;
}

$userid = single("SELECT emogames_user_id FROM  `users` WHERE konzernid=".$userThis);

echo $userThis." mit userid:".$userid." aktiviert!";

if ($userThis){

	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '7')");
	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '8')");
	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '9')");
	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '10')");
	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '11')");
	select("INSERT INTO  `features` (  `konzernid` ,  `feature_id` ) VALUES ('".$userThis."',  '12')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '7',  '".$time."',  '".$time2."', '1')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '8',  '".$time."',  '".$time2."', '1')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '9',  '".$time."',  '".$time2."', '1')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '10',  '".$time."',  '".$time2."', '1')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '11',  '".$time."',  '".$time2."', '1')");
	select("INSERT INTO  `users_features` ( `emogames_user_id` ,  `feature_id` ,  `time` ,  `time_bis` ,  `server_id` ) VALUES ( '".$userid."',  '12',  '".$time."',  '".$time2."', '1')");
					
	sendthemail("Alle Features gesetzt für User mit ID: $userThis durch User mit ID: $id",
	  "kein Text",
	  "info@DOMAIN.de",
	  "INFO");
} 

?>
