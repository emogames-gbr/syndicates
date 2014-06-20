<?
include("inc/general.php");

$self = "index.php";

$userThis = (int) $_GET['konzid'];

if($userThis==0){
	echo"<form action=\"getEmos.php\" method=\"get\"><input type=\"text\" name=\"konzid\" value=\"konzid\"><input type=\"submit\" value=\"start\"></form>";
	exit;
}

$userid = single("SELECT emogames_user_id FROM  `users` WHERE konzernid=".$userThis);


if($userThis){

	echo "Konzern: $userThis hat Emos: ".EMOGAMES_get_emos($userid);
				
} 

?>
