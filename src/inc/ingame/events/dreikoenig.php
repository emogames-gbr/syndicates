<?

	/**
	 * Autor: Konstantin Grupp/inok
	 * 
	 * wird eingebunden in inc/events.php
	 * 
	 */

//
// Drei-Königs-Bonus aktivieren
//

if ($page == 'statusseite' && 
	Events::getSettings('dreikoenig','starttime') < $time && 
	$time < Events::getSettings('dreikoenig','endtime')) {
	$wbonustyp = "Drei-Königsbonus";
	$wbonusAmount = Events::getSettings('dreikoenig','amount');
	$wboni = array(	'Cr'  => array('type' => 'money', 'modifier' => 1),
					'MWh' => array('type' => 'energy', 'modifier' => ENERGY_STD_VALUE_TRADE),
					'P'   => array('type' => 'sciencepoints', 'modifier' => SCIENCEPOINTS_STD_VALUE_TRADE),
					't'   => array('type' => 'metal', 'modifier' => METAL_STD_VALUE_TRADE));
	
	if (!Events::getEvent('dreikoenig', value)) {
		if ($what == "Cr" or $what == "MWh" or $what == "P" or $what == "t") {
			$column = $wboni[$what]['type'];
			$amount = floor($wbonusAmount/$wboni[$what]['modifier']);
			
			select("UPDATE status SET $column=$column+$amount where id = $id");
			select("INSERT INTO events (type, konzernid, value) VALUES ('dreikoenig', $id, 1)");
			$beschr = "Dein $wbonustyp in Höhe von ".pointit($amount)." $what wurde dir erfolgreich gutgeschrieben!<br><br>
				Das ganze Syndicates-Team wünscht ein frohes neues Jahr ".date('Y',$time).".";
			$tpl->assign("MSG", $beschr);
			Events::assign("sys_msg.tpl");
			$status = getallvalues($id);
		}		
		if (!$what) {
			$beschr = "Bitte wähle deinen $wbonustyp aus (der Wert entspricht dem Standard-Gegenwert von ".pointit(floor($wbonusAmount/$wboni['Cr']['modifier']))." Credits):<br><br><ul>
			<li><a href=statusseite.php?dreikoenig=1&what=Cr class=linkAufsiteBg>".pointit(floor($wbonusAmount/$wboni['Cr']['modifier']))." Credits</a>
			<li><a href=statusseite.php?dreikoenig=1&what=MWh class=linkAufsiteBg>".pointit(floor($wbonusAmount/$wboni['MWh']['modifier']))." MWh</a>
			<li><a href=statusseite.php?dreikoenig=1&what=P class=linkAufsiteBg>".pointit(floor($wbonusAmount/$wboni['P']['modifier']))." Forschungspunkte</a>
			<li><a href=statusseite.php?dreikoenig=1&what=t class=linkAufsiteBg>".pointit(floor($wbonusAmount/$wboni['t']['modifier']))." Erz</a>
			</ul>";
			$tpl->assign("MSG", $beschr);
			Events::assign("sys_msg.tpl");
		}
	} elseif ($what) {
		$errormsg = "Du hast deinen diesjährigen $wbonustyp schon erhalten."; 
		$tpl->assign('ERROR', $errormsg);
		Events::assign('fehler.tpl');
	}
}


?>