<?


//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

// Header einbinden
require_once("../../inc/ingame/header.php");

if ($features[KOMFORTPAKET] || $status['is_mentor']) {
	$tpl->assign("noAssi",true);
	if ($inner == "change") { // Es gab eine Änderung
		//$text = $notiztext;
		if (strlen($notiztext) > 15000) {
			$tpl->assign("ERROR","Ihr Notizblock-Eintrag darf die Länge von 15.000 Zeichen nicht überschreiten. Die Änderung wurde nicht übernommen!");
			$tpl->display('fehler.tpl');
			$notiztext = preg_replace("/\n/","<br>",$notiztext);
			$tpl->assign("text",$notiztext);
		}
		else {
			$notiztext = htmlentities($notiztext,ENT_QUOTES);
			$notiztext = addslashes($notiztext);
			$exists = single("select user_id from notes where user_id = $status[id]");
			if ($exists) {
				select("update notes set text='$notiztext' where user_id=$status[id]");
			}
			else {
				select("insert into notes (text,user_id) values ('$notiztext',$status[id])");
			}
			$tpl->assign("MSG", "Ihr Eintrag wurde erfolgreich übernommen.");
			$tpl->display("sys_msg.tpl");
		}
	}
	$stuff = assoc("select text from notes where user_id=$status[id]");
	//$stuff[text] = preg_replace("/\n/","<br>",$stuff[text]);
	$tpl->assign("stuff",$stuff);
}
else {	
	// Kein Assistent
}

	//							selects fahren									//

	//							Berechnungen									//

	//							Daten schreiben									//

	//							Ausgabe     									//

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

$tpl->display("notes.tpl");
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
