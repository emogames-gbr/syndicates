<?


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($inneraction != "makeentry") {$inneraction = "";}
if ($kategorie) {
    $kategorie = htmlentities($kategorie,ENT_QUOTES);
}

if ($description) {
    $description = htmlentities($description,ENT_QUOTES);
}
if ($showdetails != "on") {$showdetails = "";}
if ($showdetails == "on") {$checked = "checked=true";$showdetails=1;}
else {$checked=""; $showdetails=0;}

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

$queries = array();

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

if ($submittype != "Absenden") {
    $data = assoc("select * from settings where id =".$status{id});

    if ($data[showdetails]) {$checked = "checked=true";}
    else {$checked="";}

}



//							Berechnungen									//

if ($inneraction == "makeentry" && $submittype=="Absenden") {
	if (strlen ($description) < 100000 && strlen($kategorie) < 255) {
		//            $description = addslashes($description);
		//            $kategorie = addslashes($kategorie);
		$action = "replace delayed into settings (id,kategorie,description,showdetails) values (".$status{id}.",'".$kategorie."','".$description."',".$showdetails.")";
		array_push($queries,$action);
		$data{kategorie} =  preg_replace("/\\\/","",$kategorie);
		$data{description} =  preg_replace("/\\\/","",$description);
		s("Eintrag erfolgreich übernommen");
	} # wennn länge < 100000
	else {
	f("Ihre Beschreibung ist zu lang. Maximal 100.000 Zeichen erlaubt. Für die Branche sind maximal 255 Zeichen erlaubt.");
	}
}


	//
	//// Konzernbild ändern /  neu hochladen
	//

		$uploaddir = DATA.'/konzernimages/';
		if ($submittype == "hochladen")	{
			if ($_FILES[sbil][error] == 0 && $_FILES[sbil][size] <= 20480 and $_FILES[sbil][size] > 0)	{
				if (preg_match('/\.(jpg|jpeg|png)$/i', $_FILES[sbil][name]))	{
					
					list($width, $height, $type) = getimagesize($_FILES[sbil][tmp_name]);
					
					if ($width <= 110 and $height <= 140 and $width >= 39 and $height >= 50)	{
						
						#preg_match('#image\/[x\-]*([a-z]+)#', $_FILES[sbil][type], $avatar_filetype);
						#$avatar_filetype = $avatar_filetype[1];
						
						if ($type == 2): $avatar_filetype = "jpg";
						elseif ($type == 3): $avatar_filetype = "png";	endif;
						
						if ($avatar_filetype == "jpg" or $avatar_filetype == "png")	{
							$filepath = $uploaddir .KBILD_PREFIX.$id.".".$avatar_filetype;
							if (move_uploaded_file($_FILES['sbil']['tmp_name'],$filepath ) and $globals[updating] == 0)	{
								system("chmod 444 $filepath");
								if ($avatar_filetype != $status[image] && $status[image])	{
									unlink($uploaddir .KBILD_PREFIX.$id.".".$status[image]);
								}
								s("Bild erfolgreich hochgeladen :)!");
								$status[image] = $avatar_filetype;
								$queries[] = "update status set image='$avatar_filetype' where id=$id";
								$queries[] = "delete from admin_konzernimages_approved where konzernid=$id";
							}
							else { f("Unbekannter Fehler aufgetreten<br>Aktion abgebrochen!"); $error=1;};
						}
						else { f("Ungültiges Dateiformat! Bitte ein JPEG- oder ein PNG-Bild hochladen!"); $error=1;}
						#elseif($avatar_filetype == "pjpeg") { f("Das Format deines Bildes ist (ungeachtet der Dateiendung) \"pjpeg\". Dieses Format wird jedoch nicht unterstützt. <br>Bitte anderes Bild wählen!"); $error=1; }
						#elseif($avatar_filetype == "gif")	{ f("Das Format deines Bildes ist (ungeachtet der Dateiendung) \"gif\". Dieses Format wird jedoch nicht unterstützt. <br>Bitte anderes Bild wählen!"); $error=1; }
					}
					else { f("Das Bild darf die maximale Größe von 110 x 140 Pixeln nicht über- und die minimale Größe von 39 x 50 Pixeln nicht unterschreiten!"); $error=1;}
				}
				else { f("Es sind nur .jpg/.jpeg bzw. .png-Dateien erlaubt!");  $error=1;}
			}
			elseif($_FILES[sbil][size] > 20480 or $_FILES[sbil][error] == 2) { 
				f("Das Bild darf maximal 20.480  Bytes (20 KB) groß sein!"); 
				$error=1;
			}
			elseif($_FILES[sbil][error] == 4) { 
				f("Es wurde kein Bild hochgeladen! Bitte wähle ein Bild von deiner Festplatte aus."); 
				$error=1;
			}
			else { f("Es ist ein unbekannter Fehler aufgetreten. Bitte erneut versuchen oder ggf. ein anderes Bild auswählen!"); $error=1;}
		}
		if ($submittype == "Bild löschen")	{
			if ($status[image] and $globals[updating] == 0)	{
				unlink($uploaddir .KBILD_PREFIX.$id.".".$status[image]);
				$queries[] = "update status set image='' where id=$id";
				s("Das Bild wurde erfolgreich gelöscht");
				$status[image] = "";
			}
			elseif ($globals[updating] == 1)	{ f("Unbekannter Fehler, bitte in 10 Sekunden erneut versuchen!"); $error=1;}
			else { f("Kein Bild vorhanden welches gelöscht werden kann"); $error=1; }
		}
		
		$tpl->assign("status", $status);
		if ($status[image])	{
			$tpl->assign("WWWDATA", WWWDATA);
			$tpl->assign("KBILD_PREFIX", KBILD_PREFIX);
			$tpl->assign("id", $id);
		}
		//							Ausgabe     									//

		$tpl->assign("data", $data);
		$tpl->assign("bbcode_hilfe", print_hilfe("bbcode"));
     	$tpl->assign("checked", $checked);
     	
//							Daten schreiben									//
db_write($queries,1);

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
$tpl->display("settings.tpl");
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//


/*

###########################################
########## Eintrag vornehmen ##############
###########################################

*/

?>
