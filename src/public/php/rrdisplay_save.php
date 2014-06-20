<?php
//Speicherung des racherecht toggles
//@author dragon12, 2012


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//



//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

if ($toggle && ($toggle == 'dir' || $toggle == 'indir')){
	$toggle = mres($toggle);
	$colname = 'show_'.$toggle.'RR';
	if(select("UPDATE status SET $colname = ($colname + 1) % 2 WHERE id = ".$status['id']))
		echo 'ok';
}



?>