<?

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

if ($action and $action != "delete"): unset($action); endif;

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


$realtime = ""; /* svn commit test 

- test bestanden */
$realdate = "";
$message = "";
$count = 0;
$deletestring = "";
$update_messagestring = "";

$structure = array();
$prep_structure = array();
$messages = array();
$queries = array();
$update_messages = array();
$temp = array();
$deletefrom = array();

$goon = 1;	# Variable die von db_write auf 0 gesetzt wird wenn gerade upgedatet wird um weitere ausgabe zu unterbinden


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


// Messages löschen wenn angegeben

	if ($action == "delete")	{
		foreach ($_POST as $ky => $vl)	{
			if (strpos($ky, "elete") == 1){$deletefrom[] = floor($vl);}
		}
		if ($deletefrom): $deletestring = join(",", $deletefrom); endif;
		
		if ($deletestring): $queries[] = "delete from message_values where user_id='$id' and unique_id in ($deletestring)"; endif;
	}

	## QUERIES SCHREIBEN

	db_write($queries, 1);

//							selects fahren									//

$messages = assocs("select unique_id, id, time, gelesen, werte from message_values where user_id='$id' order by time desc;");
$structure = assocs("select id, kategorie, img, message from message_settings", "id");
$kategorien = assocs("select * from message_kategorien", "kat_id");

$komfortpaket = $features[KOMFORTPAKET];
#$komfortpaket = true;
if($_GET['action'] == 'ajax' && $komfortpaket){
	unset($_GET['action']);
	header("content-type: text/html; charset=ISO-8859-1");
	$temp = array();
	foreach($_GET as $tag => $val){
		if($val == "true"){
			$temp[] = $tag;
		}
	}
	if($temp){
		$kat_temp = assocs("select kat_id from message_kategorien where kat_id in (".join(",", $temp).")");
		$temp = array();
		foreach($kat_temp as $val){
			$temp[] = $val["kat_id"];
		}
		$set_temp = assocs("select id from message_settings where kategorie in (".join(",", $temp).")");
		
		$temp = array();
		foreach($set_temp as $val){
			$temp[] = $val["id"];
		}
		$messages_ = assocs("select unique_id, id, time, gelesen, werte from message_values where user_id='$id' and id in (".join(",", $temp).") order by time desc;");
		#echo "<pre>".print_r(join(",", $messages), true)."</pre>";
	
		if(!$messages_){
			unset($_GET);
		}
		else{
			$messages = $messages_;
		}
		
		foreach ($structure as $ky => $vl)	{
			$prep_structure[$ky] = explode ("|", $vl[message]);
		}
		
		$max = 999999;
		$more = false;
		$download = "Nachrichten von '".$status['rulername']." von ".$status['syndicate']."'\r\n
		Stand: ".date("D, d. M", $time)." - ".date("H:i:s", $time)."\r\n
		____________________________________\r\n\r\n";
		$tpl->assign('RIPF', $ripf);
		$messages_output = array();
		foreach ($messages as $vl)	{
			if((!$_GET && $vl["gelesen"] == 0) || !$komfortpaket || $_GET){
				if($count >= $max && !$_GET['detail']){
					$more = true;
					break;
				}
				$temp = explode ("|", $vl[werte]);
				unset ($message);
				if ($vl[gelesen] == 0){
					$update_messages[] = $vl[unique_id];
				}
				
				$num = count($prep_structure[$vl[id]]);
				for ($i = 0; $i < $num; $i++)	{
					if ($i+1 == $num){	
						$message .= $prep_structure[$vl{id}][$i];
					}
					else{
						$message .= $prep_structure[$vl[id]][$i].$temp[$i];
					}
				}
				$vl['img'] = $structure[$vl[id]][img];
				$vl['realdate'] = date("D, d. M", $vl[time]);
				$vl['realtime'] = date("H:i:s", $vl[time]);
				$vl['o_message'] = $message;
				$vl['count'] = $count;
				array_push($messages_output, $vl);
				$download .= "\n	".$realdate."\r\n\n".$realtime."	".strip_tags($message)."\r\n\r\n";
				$count++;
			}
		}
		$tpl->assign('MESSAGES', $messages_output);
		$tpl->assign('MORE', $more);
		if($more){
			foreach($kategorien as $kat_id => $vl){
				$url_ .= "&".$kat_id."='+ids['id_".$kat_id."']+'";
			}
			$tpl->assign('MAX', $max);
			$tpl->assign('URL_', $url_);
		}
		if($_GET['detail'] == 'download'){
			header("Content-Type: text/plain");
			header("Content-Disposition: attachment; filename=Nachrichten_".date("d.M.-H.i.s", $time).".txt");
			echo $download;
		}
		else{
			$tpl->display('nachrichten_ajax.tpl');
		}
	}
	exit();
}


if ($messages) {
	if ($komfortpaket) {
		$url_ = "";
		$kategorien_output = array();
		foreach($kategorien as $kat_id => $vl){
			array_push($kategorien_output, $kat_id);
			$javascript .=" ids['id_".$kat_id."'] = false;";
			
		}
		$tpl->assign('KATEGORIEN', $kategorien_output);
		$katcount = array();
		$katorder = array();
		$ungelesen_count = 0;
		foreach ($messages as $vl) {
			$katcount[$structure[$vl['id']]['kategorie']]++;
			if (!$vl['gelesen']) $ungelesen_count++;
			$katorder[$structure[$vl['id']]['kategorie']][] = $vl['unique_id'];
		}
		$kats_per_line = 3;
		$kat_lines = array();
		$temp_katcount = 0;
		$tpl->assign('UNGELESEN_COUNT', $ungelesen_count);
		$menue_col_output = array();
		$menue_row_output = array();
		$count = 0;
		foreach ($kategorien as $kat_id => $vl) {
			$url_ .= "&".$kat_id."='+ids['id_".$kat_id."']+'";		
			$vl['kat_id'] = $kat_id;
			$vl['katcount'] = $katcount[$kat_id];
			$count++;
			array_push($menue_row_output, $vl);
			if ($count == $kats_per_line) {
				array_push($menue_col_output, $menue_row_output);
				$menue_row_output = array();
				$count = 0;
			}
		}
		if ($count != 0) array_push($menue_col_output, $menue_row_output);
		while(($count) != $kats_per_line) {
			$temp = array_pop($menue_col_output);
			$temp2 = array();
			array_push($temp, $temp2);
			array_push($menue_col_output, $temp);
			$count++;
		}
		$tpl->assign('MENUE_COL', $menue_col_output);
		$tpl->assign('URL_', $url_);
		
	}
}


//							Berechnungen									//


if($komfortpaket){
	if($_GET){
		$temp = array();
		foreach($_GET as $tag => $val){
			if($val == 1 && is_numeric($tag)){
				$temp[] = $tag;
			}
		}
		if($temp){
			$kat_temp = assocs("select kat_id from message_kategorien where kat_id in (".join(",", $temp).")");
			$temp = array();
			foreach($kat_temp as $val){
				$temp[] = $val["kat_id"];
			}
			$set_temp = assocs("select id from message_settings where kategorie in (".join(",", $temp).")");
			
			$temp = array();
			foreach($set_temp as $val){
				$temp[] = $val["id"];
			}
			$messages_ = assocs("select unique_id, id, time, gelesen, werte from message_values 
								 where user_id='$id' and id in (".join(",", $temp).") order by time desc;");
			#echo "<pre>".print_r(join(",", $messages), true)."</pre>";
		}
		if(!$messages_){
			unset($_GET);
		}
		else{
			$messages = $messages_;
		}
	}
}


//							Ausgabe     									//


// Falls nicht geupdatet wurde, wird nun ausgegeben ... go on ;)
if ($goon)	{
	$tpl->assign('GOON', $goon);
	if ($messages)	{
		$tpl->assign('KOMFORTPAKET', $komfortpaket);
		
		// Nur vorrübergehend!!
		$tpl->assign('kategorienausgabe', $kategorienausgabe);
		
		foreach ($structure as $ky => $vl)	{
			$prep_structure[$ky] = explode ("|", $vl[message]);
		}
		
		$tpl->assign('RIPF', $ripf);
		$messages_output = array();
		foreach ($messages as $vl)	{
			if((!$_GET && $vl["gelesen"] == 0) || !$komfortpaket || $_GET){
				unset ($message);
				$temp = explode ("|", $vl[werte]);
				$vl['realdate'] = date("D, d. M", $vl[time]);
				$vl['realtime'] = date("H:i:s", $vl[time]);
				if ($vl['gelesen'] == 0) $update_messages[] = $vl[unique_id];
				
				$num = count($prep_structure[$vl[id]]);
				for ($i = 0; $i < $num; $i++)	{
					if ($i+1 == $num) { 	
						$message .= $prep_structure[$vl{id}][$i];
					}
					else {
						$message .= $prep_structure[$vl[id]][$i].$temp[$i];
					}
				}
				$vl['img'] = $structure[$vl[id]][img];
				$vl['o_message'] = $message;
				$vl['count'] = $count;
				
				array_push($messages_output, $vl);
				$count++;
			}
		}
		$tpl->assign('MESSAGES', $messages_output);
		$update_messagestring = join (",", $update_messages);
		if ($update_messagestring and true/*$globals[updating] == 0*/ && !$adminlogin) {
			select("update message_values set gelesen=1 where user_id='$id' and unique_id in ($update_messagestring)");
		}
		elseif ($update_messagestring and $globals[updating] == 1) {
			f("Es läuft momentan das stündliche Update. Neue  Nachrichten konnten daher nicht
			   als gelesen markiert werden. Bitte laden Sie die Nachrichten in etwa 10 Sekunden 
			   erneut, um dies nachzuholen.");
		}
	}
	else { 
		// Es sind keine Nachrichten vorhanden
	}
} # $goon ende

//							Daten schreiben									//

//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
$tpl->display('nachrichten.tpl');
require_once("../../inc/ingame/footer.php");

//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>