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

$ausgabe = "";
$raceicon=array(	"pbf" => "bf-logo-klein",
					"sl" => "sl-logo-klein",
					"nof" => "nof-logo-klein",
					"uic" => "uic-logo-klein",
					"neb" => "neb-logo-klein");
$sessidsactual = assocs("select user_id, gueltig_bis from sessionids_actual", "user_id");
$time = time();

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$tpl->assign('IS_MENTOR', $status["is_mentor"]);
$tpl->assign("GP_PATH", $ripf);						// GP-variable $ripf assignen

if($status["is_mentor"]){
	
	if($_GET["action"] == "ok" && is_numeric($_GET["id"])){
		select("update status set pm_from_mentor = 1 where id = ".$_GET["id"]);
	}
	
	if($_GET["action"] == "kick" && is_numeric($_GET["id"]) && $status['is_mentor'] == 2){
		select("update users set mentorsystem = 1 where konzernid = ".$_GET["id"]);
	}
	
	$maxround = $globals["round"] - MENTOR_ROUNDS;
	
	$newbs = assocs("select status.rid, status.id, status.race, status.land, status.nw, status.syndicate as name, status.lastlogintime, status.alive, status.createtime, status.pm_from_mentor from status, users where users.startround >= ".$maxround." and status.id = users.konzernid and users.mentorsystem != 1 order by status.rid asc, status.nw desc");
	
	$syn = 0;
	foreach($newbs as $tag => $val){
				
		if ($time < $sessidsactual[$val["id"]]["gueltig_bis"]){
			$newbs[$tag]['online'] = "online";
		}
		elseif($val["lastlogintime"] + TIME_TILL_GLOBAL_INACTIVE < $time){
			$newbs[$tag]['online'] = "gl_inaktiv";
		}
		elseif($val["lastlogintime"] + TIME_TILL_INACTIVE < $time){
			$newbs[$tag]['online'] = "lokal_inaktiv";
		}
		else{
			$newbs[$tag]['online'] = "offline";
		}
		$newbs[$tag]['raceicon'] = $raceicon[$val["race"]];
		$newbs[$tag]['racename'] = $racenames[$val["race"]]["shortname"];
		$newbs[$tag]['lastlogintime'] = datum("d.m.Y, H:i", $val["lastlogintime"]);
	}
	
	$tpl->assign('NEWBS', $newbs);
}


$tpl->display('mentor.tpl');

//							Daten schreiben									//

//**************************************************************************//
//								  Footer									//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>