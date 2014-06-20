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

$update_for = floor($update_for);
$tbl = "gpacks";
if ($update_for) {
	$tbl = "gpacks_updates";
	$addpath="/updates/";
}

$user = assoc("select * from users where konzernid = ".$status[id]."","gpack_id");
$mygpacks = assocs("select * from gpacks where syn_user_id=$user[id]","gpack_id");
//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//
 

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//							selects fahren									//

//							Berechnungen									//
// Damit hochladen richtig funktioniert muss temp schreibbar sein und alle verzeichnisse unter syn_gpacks dem wwwrun user gehören
if ($action == "insert_package") {
	if ($name) {
		$succ = 0;
		$name = addslashes($name);
		$description =addslashes($description);
		if (!$update_for) {
			select("insert into gpacks (name,description,syn_user_id,time) values ('$name','$description',$user[id],$time) ");
		}
		else {
			select("insert into gpacks_updates (name,description,syn_user_id,time,update_for) values ('$name','$description',$user[id],$time,$update_for) ");
		}
		$gpack_id = single("select gpack_id from $tbl order by gpack_id desc limit 1");
		$gpack_path = DATA."/syn_gpacks$addpath/$gpack_id/";
		$file_path = $gpack_path."/pack.zip";
		$formats_to_save = array("css","gif","jpg","png");
		if ($_FILES[data][error] == 0 && $_FILES[data][size] <= (1024*1024*2) and $_FILES[data][size] > 0)	{
			if (preg_match('/\.(zip)$/i', $_FILES[data][name]))	{
				(exec("mkdir $gpack_path -p"));
				chdir($gpack_path);
				if (move_uploaded_file($_FILES['data']['tmp_name'],$file_path)) {
					system("mkdir $gpack_path/../temp");
					
					if (!$update_for) {
					 	$zipfile = $gpack_id;
					}
					else {
						$zipfile = $update_for;
					}
					exec("cp $file_path $gpack_path/../temp/$zipfile.zip");
					exec("unzip $file_path");
					foreach ($formats_to_save as $temp) {
						$cmd = "mv $gpack_path/*.$temp $gpack_path/../temp/";
						exec("$cmd ");
					}
					exec("rm $gpack_path -r");
					exec("mkdir $gpack_path");
					exec("mv $gpack_path/../temp/* $gpack_path");
					$styleexists = (file_exists($gpack_path."/style.css"));
					if ($styleexists) {
						exec("chmod 000 $gpack_path");
						system("rm $gpack_path/../temp -r");
						s("Das Grafikpaket wurde erfolgreich hochgeladen und wird in K&uuml;rze gepr&uuml;ft");
						$betreff ="Neues Grafikpaket hochgeladen -.prüfen";
						$message ="Neues Grafikpaket:\nhttp://´DOMAIN.de/php/admin";
						$email = "info@DOMAIN.de";
						$to = "Admins";
 						sendthemail($betreff,$message,$email,$to);
						$succ=1;
					}
					else {
						//system("rm $gpack_path -r");
						select("delete from $tbl where gpack_id=$gpack_id");
						f("Beim Hochladen des Grafikpakets ist ein Fehler aufgetreten. Bitte stellen Sie sicher, dass Sie die vorgegebene Ordnerstruktur eingehalten haben!");
					}
				}
				else{
					(exec("rm $gpack_path -r"));
					f("Beim hochladen der Datei ist ein Fehler aufgetreten");
				}
				
			}
			else {
				f("Es werden nur .zip Dateien akzeptiert");
			}
		}
		else {
			f("Die maximale Gr&ouml;&szlig;e f&uuml;r ein Grafikpaket betr&auml;gt 2MB");
		}
		
		if (!$succ) {
			select("delete from gpacks where gpack_id = $gpack_id");
		}
	}
	else {
		f("Kein Name angegeben");
	}
}




//chdir(dirname($SCRIPT_FILENAME));

//							Daten schreiben									//

//							Ausgabe     									//

$tpl->assign("view", $view);
if (!$view) {
	$tpl->assign("WWWDATA", WWWDATA);
	$tpl->assign("name", $name);
	$tpl->assign("description", $description);
	if (count($mygpacks) > 0) {
		$tpl->assign("showmygpacks", true);
		$i = 0;
		$mygpacksoutput = array();
		foreach ($mygpacks as $temp) {
			$temp['o_disabled'] = '';
			$is_updated = single("select count(*) from gpacks_updates where update_for=$temp[gpack_id]");
			if ($is_updated) {
				$temp['o_disabled'] = "disabled";
				$temp['o_addstring'] = "(Aktualisierung wird geprüft)";
			}
			else if($temp[visible] == 0) {
				$temp['o_addstring'] ="(Warte auf Freischaltung)";
			}
			$mygpacksoutput[$i] = $temp; $i++;	
		}
		$tpl->assign("mygpacks", $mygpacksoutput);
	}
}
elseif($view == "showall") {
	$gpacks = assocs("select * from gpacks where visible=1 order by sortby asc");
	$tpl->assign("WWWPUB", WWWPUB);
	$tpl->assign("WWWDATA", WWWDATA);
	$i = 0;
	$gpacksoutput = array();
	foreach($gpacks as $temp) {
		$ssmall = WWWDATA."syn_gpacks/$temp[gpack_id]/example.jpg";
		$temp['ssmall'] = $ssmall;
		$ssmall_loc = DATA."syn_gpacks/$temp[gpack_id]/example.jpg";
		$ssbig = WWWDATA."syn_gpacks/$temp[gpack_id]/example_big.jpg";
		$temp['ssbig'] = $ssbig;
		$ssbig_loc =  DATA."syn_gpacks/$temp[gpack_id]/example_big.jpg";
		if(file_exists($ssmall_loc) && file_exists($ssbig_loc)) {
			$temp['shexists'] = true;
		}
		$gpacksoutput[$i] = $temp; $i++;
	}
	$tpl->assign("gpacks", $gpacksoutput);
}


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//
require_once(INC."/ingame/header.php");
$tpl->display("gpacks.tpl");
require_once(INC."/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
