<?


//**************************************************************************//
//							�bergabe Variablen checken						//
//**************************************************************************//
if ($details){
	$details = floor($details);
}

//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

// Header include
require_once("../../inc/ingame/header.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//





//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//
$levels = array(array('id' => 1, 'name' => 'Tutorial'), array('id' => 2, 'name' => 'test2'));


$tpl->assign('GENERAL_URL', $_SERVER['SERVER_NAME'].'/data/syn_gpacks/59/7.jpg');
$tpl->assign('CURRENT_QUEST', "General Bauhof sagt hallo!");
$tpl->assign("CURRENT_QUEST_TEXT", "Die Beschreibung die das zu Beschreibende beschreibt.");
$tpl->assign("LEVELS", $levels);
$tpl->display("quests.tpl");








//**************************************************************************//
//							  Ausgabe, Footer	     						//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>