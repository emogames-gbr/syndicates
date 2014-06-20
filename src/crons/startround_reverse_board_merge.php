<?
##
##	Dieses Skript macht nur Gruppenverteilung und wird daher nichf fr den Basic Server angepasst
##
##
require_once("../includes.php"); // Subfunctions laden
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once("../inc/ingame/globalvars.php"); // fr get_an_empty_syndicate() wichtig!
$time = time(); // Zeit zur skriptausfhrung als timestamp, bitte spï¿½er nicht mehr benutzen, da dies eine Systemfunktion ist und relativ viel leistung frisst.
$microtime = getmicrotime();
$hourtime = get_hour_time($time);
mt_srand($time);


$groups = assocs("select * from groups_new");
$syndikate = assocs("select * from syndikate");

foreach ($syndikate as $vl) {
  $gid = 0;
  foreach ($groups as $vl2) {
    if ($vl2['rid'] == $vl['synd_id'] && $vl['open'] == 0) {
      $gid = $vl2['group_id'];
    }
  }
  if ($gid) {
    select("update board_subjects set bid = ".(BOARD_ID_OFFSET_GRUPPEN+$gid)." where bid = ".$vl2['synd_id']);
    select("update polls set synd_id = ".(BOARD_ID_OFFSET_GRUPPEN+$gid)." where synd_id = ".$vl2['synd_id']);
  }
}



?>
