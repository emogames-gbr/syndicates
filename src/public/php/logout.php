<?
require_once("../../inc/ingame/game.php");
$time --;
if (!$adminlogin) {
	select("update sessionids_actual set gueltig_bis = ".$time." where user_id =".$status{id});
}
else {
	select("update sessionids_admin set gueltig_bis = ".$time." where user_id =".$status[id]);
	setcookie("adminsessionid", 0, -1 ,"/",".".DOMAIN);
}
session_destroy();
header("Location: ../index.php");
exit();
?>
