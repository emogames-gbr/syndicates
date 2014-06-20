<?
ob_start();

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$ia = round($inneraction);

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

$time = time();
$handle = connectdb($SERVER_NAME); // Datenbankverbindung herstllen
$queries = array();
$loggedin = 0;
$goon = 1;

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

//							selects fahren									//

//							Berechnungen									//

if ($status[alive] == 2)	{
	
	#$playerstats = assoc("select * from users where autologinkey ='".$autologinkey."'");
	#$id = $playerstats{konzernid};
	$values = getallvalues($id);
	list ($vtime, $mindays) = row("select starttime, mindays from options_vacation where user_id = '$id' order by starttime desc limit 1");
	
	$difference =  $vtime + $mindays * 24 * 60 * 60 - $time;
	$days = (int) ( $difference / (24 * 60 * 60));
	$hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
	$minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$activationtime = $vtime + $mindays * 24 * 60 * 60;
	$activationtime = date("j.m.y \u\m H:i",$activationtime);
	
	//							Daten schreiben									//
	
	//							Ausgabe     									//
	if (!$ia)	{
	    if ($difference > 0) 	{
	    	$tpl->assign("case1", true);
	    	$tpl->assign("days", $days);
	    	$tpl->assign("hours", $hours);
	    	$tpl->assign("minutes", $minutes);
	    	$tpl->assign("activationtime", $activationtime);
	    }
	    elseif ($difference <= 0)	{
	    	$tpl->assign("case2", true);
		}
	}
	elseif ($ia == 1)	{
	    if ($difference > 0) 	{
	    	$tpl->assign("case3", true);
	    	$tpl->assign("days", $days);
	    	$tpl->assign("hours", $hours);
	    	$tpl->assign("minutes", $minutes);
	    	$tpl->assign("activationtime", $activationtime);	    	
	    }
	    elseif ($difference <= 0)	{
	            $queries[] ="update status set alive=1 where id=$id";
	            $queries[] ="update options_vacation set endtime=$time where user_id=$id and starttime = $vtime";
	            ### Statstable updaten
	            ##$queries[] ="update status set lastlogintime=$time where id='$id'";
	            db_write($queries);
	        if ($goon)	{header ("Location: login.php"); exit();}
	    }
	}
}
else { header ("Location: statusseite.php"); exit(); }



//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

ob_end_flush();
require_once("../../inc/ingame/header.php");
$tpl->display("vacation.tpl");
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

?>
