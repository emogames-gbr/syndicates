<?

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

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

require_once("../../inc/ingame/header.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$time = time();

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$tpl->assign("GP_PATH", $ripf);						// GP-variable $ripf assignen

if($_POST['action'] == 'insert' && $_POST['text'] != ''){
	$user_id = single('SELECT id FROM users WHERE konzernid = '.$status['id']);
	select('INSERT INTO spruch_des_tages (txt, user) VALUES (\''.mysql_real_escape_string($_POST['text']).'\', '.$user_id.')');
	$tpl->assign('MSG', 'Spruch erfolgreich eingetragen');
	$tpl->display('sys_msg.tpl');
}
else if($_POST['action'] == 'delete'){
	select('DELETE FROM spruch_des_tages WHERE id = '.$_POST['id']);
	$tpl->assign('MSG', 'Spruch erfolgreich gelöscht');
	$tpl->display('sys_msg.tpl');
}

$sprueche = assocs('SELECT * FROM spruch_des_tages ORDER BY id ASC');
$users = assocs('SELECT id, username FROM users', 'id');

foreach($sprueche as $tag => $val){
	$sprueche[$tag]['name'] = $users[$val['user']]['username'];
	$sprueche[$tag]['txt'] = umwandeln_bbcode($sprueche[$tag]['txt']);
}

$tpl->assign("SPRUECHE", $sprueche);


$tpl->display('spruch.tpl');

//							Daten schreiben									//

//**************************************************************************//
//								  Footer									//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>