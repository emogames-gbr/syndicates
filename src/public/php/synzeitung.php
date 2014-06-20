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


//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$tpl->assign("GP_PATH", $ripf);						// GP-variable $ripf assignen

$emonick = single('SELECT username FROM users WHERE konzernid = '.$status['id']);

if ($emonick == 'Remake...?' || $emonick == 'R4bbiT'){
	require_once("../../inc/ingame/header.php");
	if ($_POST['action'] && $_POST['msg'] != ''){
		if($_POST['type'] == 'Vorschau'){
			$tpl->assign('MSG', $_POST['msg']);
			$tpl->assign('PREVIEW_TEXT', umwandeln($_POST['msg']));
		}
		else{
			// Msg-ID: 44
			$users = singles('SELECT id FROM status WHERE alive > 0');
			foreach($users as $tag => $user_id){
				select('INSERT INTO message_values (id, user_id, time, werte)
											VALUES (44, '.$user_id.', '.$time.', \''.mysql_real_escape_string(umwandeln($_POST['msg'])).'\')');
			}
			$tpl->assign('MSG', 'Nun wissen alle Bescheid! :o)');
			$tpl->display('sys_msg.tpl');
		}
	}
	$tpl->display('synzeitung.tpl');
}
else{
	header("Location: http://syn-aktuell.goroth.de/");
	exit();
}


//							Daten schreiben									//

//**************************************************************************//
//								  Footer									//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function umwandeln($txt){
	$txt = umwandeln_bbcode($txt);
	$txt = preg_replace('/\[extrablatt\]/', '<img src="http://syndicates-online.de/images/extrablatt.jpg" name="extrablatt" alt="extrablatt" />', $txt);
	$txt = preg_replace('/\[neueausgabe\]/', '<img src="http://syndicates-online.de/images/neueausgabe.jpg" name="neueausgabe" alt="neueausgabe" />', $txt);
	return $txt;
}



?>