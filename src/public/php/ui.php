<?

require("../../includes.php");

connectdb();


$time = time();
$id = "";


if ($sessionid) {
	$id_data = checksid($sessionid);$locked = $id_data[locked];$id = $id_data[user_id];$paid=$id_data[paid];
} else {$id =0;}


###
###	Borgeye tracking
###
if ($a==1) {
	$sessionid = mysql_real_escape_string($sessionid);
	select("insert into borgeye_tracker (user_id,session_id,time) values ($id,'$sessionid',$time) ");	
}


if ($id) {
	$pcid = $dev;
	$ip = getenv ("REMOTE_ADDR");


	
	
				$attribs = array('availHeight','availLeft','availTop','availWidth','bufferDepth','colorDepth','deviceXDPI','deviceYDPI','fontSmoothingEnabled','height','logicalXDPI','logicalYDPI','pixelDepth','updateInterval','width', 'top', 'left');


	$user_id = single("select id from users where konzernid = $id");

	$already_there = single("select count(*) from ajax_screen where user_id = $user_id and pcid = '".addslashes($pcid)."'");

	if (!$already_there) {
		
		$statement_bez = array();
		$statement_val = array();
		
		$statement_bez[] = "pcid";
		$statement_bez[] = "ip";
		$statement_bez[] = "time";
		$statement_bez[] = "user_id";
	
		$statement_val[] = "'".$pcid."'";
		$statement_val[] = "'".$ip."'";
		$statement_val[] = "'".$time."'";
		$statement_val[] = "'".$user_id."'";
		
		
		
		foreach ($attribs as $vl) {
			if ($$vl != "undefined") {
				$statement_bez[] = "`".$vl."`";
				$statement_val[] = "'".addslashes($$vl)."'";
			}
		}
		
		
		
		select("insert into ajax_screen (".join(",", $statement_bez).") values (".join(",", $statement_val).")");
	}
}




function checksid($sid) {
		if ($sid) {
			global $time;
			$sessionid_data = array(); // Speichert rückgabe des selects
			$ip = getenv ("REMOTE_ADDR");

			$result = select("select sessionid, angelegt_bei, gueltig_bis, ip, user_id,locked,paid from sessionids_actual where sessionid='$sid' and gueltig_bis >= $time");
			if (mysql_num_rows($result) != 1) {return 0;} // wenn sid nicht im table gefunden wurde false zurückgeben oder zufällig mehrer gleiche sids existieren
			$sessionid_data = mysql_fetch_assoc($result);
			//if ($sessionid_data[ip] != $ip) {return 0;}
			if ($time < $sessionid_data[gueltig_bis])	{

				// User id verlängern und zuweisen an rückgabevariable, locked auf 1 setzen
				#$gueltig_bis = $time + SESSION_DAUER;
				#if ($sessionid_data[angelegt_bei] + 3600 < $gueltig_bis) {
				#	$gueltig_bis = $sessionid_data[gueltig_bis];
				#}
			
				if (count($_POST) > 0) { // COunt post > 0 --> wahrscheinlich aktion
					ignore_user_abort(TRUE);
					#select("update sessionids_actual set gueltig_bis=$gueltig_bis,locked = 1 where sessionid='$sid'");
					//select("update sessionids_actual set locked = $time where sessionid='$sid'");
				}
			}
		return $sessionid_data;
    }
}


?>
