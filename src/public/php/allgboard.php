<?
require_once("../../inc/ingame/game.php");

if ($status[id]) {
	select("update sessionids_actual set locked = 0 where user_id=".$status[id]);
}

header("Location: http://board.DOMAIN.de/board.php?boardid=6");
exit();
?>
