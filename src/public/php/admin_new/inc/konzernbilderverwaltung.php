<?
$userid=(int)$userid;
$konzernid = (int)$konzernid;
$self = "index.php?action=konzernbilderverwaltung";
$id = 772;

	$ki_pfad_absolut =DATA."/konzernimages";
	$ki_pfad_relativ = WWWDATA."konzernimages";

	$disapproved_pfad_absolut = PUB."php/admin/konzernimages_disapproved";
	$disapproved_pfad_relativ = WWWPUB."/php/admin/konzernimages_disapproved";
	
	
	
if ($mode == "single")	{ 
	$konzernid = floor($konzernid);
	if ($konzernid)	{
		$userdaten = assoc("select image, alive, username, users.id as user_id, status.id as konzernid from status,users where status.id=$konzernid and users.konzernid=$konzernid");
		$user_disapprovals = assocs("select round, time, number, dateiendung, punishment from admin_konzernimages_disapproved where user_id='".$userdaten[user_id]."'", "number");
		if ($innestaction == 1)	{
				nack($konzernid);
			$ausgabe .= "<br><br>&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;<a href=$self&actn=vki&mode=single&konzernid=$konzernid>zurück zur Userübersicht</a>";
		}
		elseif (!$innestaction)	{
			if ($userdaten[image])	{
				$ausgabe .= "Bisheriges Konzernbild<br><br><img src=".$ki_pfad_relativ."/konzern_".$konzernid.".".$userdaten[image].">";
				$ablehnen_optional = "&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;<a href=$self&actn=vki&mode=single&konzernid=$konzernid&innestaction=1>Bild ablehnen</a>";
			}
			else { $ausgabe .= "User hat kein Konzernbild";}
			$ausgabe .= "<br><br><a href=$self&actn=vki&mode=$mode2>zurück</a>".$ablehnen_optional;
			
			if ($user_disapprovals)	{
				$ausgabe .= "<br><br><br>Bisherige Bildverstöße:<br><br><table border=1 cellpadding=15 cellspacing=0>";
				$ausgabe .= "<tr class=tableHead><td align=center>#</td><td align=center>Runde</td><td align=center>Datum</td><td align=center>Bild</td><td align=center>Bestrafung</td></tr>";
				foreach ($user_disapprovals as $ky => $vl)	{
					$ausgabe .= "<tr class=tableInner1><td align=center>$ky</td><td align=center>".$vl[round]."</td><td align=center>".date("H:i:s, D, d. M Y", $vl[time])."</td><td align=center valign=middle><img src=".$disapproved_pfad_relativ."/user_".$userdaten[user_id]."_".$ky.".".$vl[dateiendung]."></td><td align=center>".$vl[punishment]."</td></tr>";
				}
				$ausgabe .= "</table>";
			}
			else {
				$ausgabe .= "<br><br>Bisher noch keine Bildverstöße";
			}
		}
	}
	else { f("Keine Id übergeben");}
}
elseif ($mode == "showall")	{
	$images = assocs("select id, syndicate, rid, image from status where alive > 0 and image != ''", "id");
	
	$zaehler = 1;
	$spalten = 9; # Mindestens 2;
	
	$ausgabe .= "<center><a href=$self&actn=vki>zurück</a></center>
				<br><br>
				<table border=0 cellpadding=3 cellspacing=3 align=center bgcolor=lightgrey>";
	foreach ($images as $ky => $vl)	{
		    if ($zaehler > $spalten): $zaehler = 1; $pre = "<tr>"; $after = "";
		elseif ($zaehler == $spalten): $pre = ""; $after = "</tr>";
		else: $pre = ""; $after = "";
		endif;
		$ausgabe .= "$pre<td align=center valign=middle width=110 height=140><a href=$self&actn=vki&mode=single&mode2=showall&konzernid=$ky><img src=$ki_pfad_relativ/konzern_".$ky.".".$vl[image]." border=0 title=\"<".$vl[syndicate]."><".$ky."> - Syndikat ".$vl[rid]."\"></a></td>$after";
		$zaehler++;
	}
	$ausgabe .= "</table>
				<br><br>
				<center><a href=$self&actn=vki>zurück</a></center>";
}
elseif ($mode == "pap")	{ # Pic After Pic
	$ausgabe .= "<center><a href=$self&actn=vki>zurück</a></center>
				<br><br>";
				
	
	// Zuerst bei Übergabewerte Bilder akzeptieren / ablehnen
	

	foreach ($_POST as $ky => $vl)	{
		if (strpos($ky, "onzern") == 1){$ids[substr($ky, 7)] = $vl;}
	}
	if ($ids)	{
		foreach ($ids as $ky => $vl)	{
			if ($vl == "nack")	{ nack($ky);}
			elseif ($vl == "ack")	{ ack($ky);}
		}
	}
	
	$sshow = 6;
	$show = $sshow;
	if ($cookieshow): $show = $cookieshow; endif;
	$show = (int)$show;
	if ($showchange and $showchange != $cookieshow): $show = $showchange; setcookie("cookieshow", $showchange, $time+365*24*3600); endif;


	
	$konzernids_already_approved = singles("select konzernid from admin_konzernimages_approved");
	if ($konzernids_already_approved): $konzernids_already_approved = join(",", $konzernids_already_approved);
	else: $konzernids_already_approved = "0"; endif;
	
	$unapproved = assocs("select id, syndicate, rid, image from status where id not in ($konzernids_already_approved) and alive > 0 and image != '' limit $show", "id");
	$not_yet_approved = single("select count(*) from status where id not in ($konzernids_already_approved) and alive > 0 and image != ''");
	$total_konzernpics = single("select count(*) from status where alive > 0 and image != ''");
	
	foreach ($unapproved as $ky => $vl)	{
	
		$ausgabe_zeile_bild .= "<td align=center valign=middle width=110 height=140><img src=$ki_pfad_relativ/konzern_".$ky.".".$vl[image]." border=0 title=\"<".$vl[syndicate]."><".$ky."> - Syndikat ".$vl[rid]."\"></td>";
		$ausgabe_zeile_approve .= "<td align=center valign=middle><input type=radio name=konzern$ky value=ack checked></td>";
		$ausgabe_zeile_disapprove .= "<td align=center valign=middle><input type=radio name=konzern$ky value=nack></td>";
	
	}
	
	
	
	
	$ausgabe .= "
		<b>$not_yet_approved</b> von <b>$total_konzernpics</b> noch nicht akzeptiert!<br><br>
		<form action=$self method=post>
			<input type=hidden name=action value=konzernbilderverwaltung>
			<input type=hidden name=actn value=vki>
			<input type=hidden name=mode value=pap>
			
			<table cellspacing=2 cellpadding=1>
			<tr><td></td>$ausgabe_zeile_bild</tr>
			<tr><td>ABLEHNEN</td>$ausgabe_zeile_disapprove</tr>
			<tr><td>Alles i.O.</td>$ausgabe_zeile_approve</tr>
			</table>
			
			
			<br><br>
			Zeige <input type=text size=3 name=showchange value=$show> Bilder<br>
			<input type=submit value=\"approve (go on)\">
		</form>";
	
	
	$ausgabe .= "<br><br>
					<center><a href=$self&actn=vki>zurück</a></center>";
}

else	{

	$ausgabe .= "<center><a href=$self>zurück</a></center>
				<br><br><br><br><br><br><a href=$self&actn=vki&mode=pap>Pics After Pics (APPROVE MODE)</a><br><br><a href=$self&actn=vki&mode=showall>Showall (FUN MODE)</a><br><br><br><br><br><br>
				<center><a href=$self>zurück</a></center>";

}





function nack($konzernid)	{

	global $ki_pfad_absolut;
	global $disapproved_pfad_absolut;
	global $queries;
	global $globals;
	global $time;
	
	$userdaten = assoc("select image, alive, username, users.id as user_id, status.id as konzernid from status,users where status.id=$konzernid and users.konzernid=$konzernid");
	
	if (file_exists($ki_pfad_absolut."/konzern_".$userdaten[konzernid].".".$userdaten[image]))	{
		$number = single("select number from admin_konzernimages_disapproved where user_id='".$userdaten[user_id]."' order by time desc limit 1");
		$number++;
		if (copy($ki_pfad_absolut."/konzern_".$userdaten[konzernid].".".$userdaten[image], $disapproved_pfad_absolut."/user_".$userdaten[user_id]."_".$number.".".$userdaten[image]))	{
			$queries[] = "insert into admin_konzernimages_disapproved (user_id, round, time, number, dateiendung, punishment) values ('".$userdaten[user_id]."','".$globals['round']."','$time','$number','".$userdaten[image]."','0')";
			s("Konzernbild des Users \"".$userdaten[username]."\" erfolgreich verschoben!");
		}
		if (unlink($ki_pfad_absolut."/konzern_".$userdaten[konzernid].".".$userdaten[image]))	{
			select("update status set image='' where id='$konzernid'"); # Wichtig damit Bild nicht nochmal erscheint beim nächsten Aufruf
			s("Konzernbild des Users \"".$userdaten[username]."\" erfolgreich von Spieloberfläche gelöscht!");
			$was = "Konzernbild gelöscht";
            select("insert into admin_users_punished (user_id,time,reason,action,adminuser,casedata) values
             (".$userdaten[user_id].",$time,'','$was','$adminuser','')");
		}
		if ($userdaten[alive])	{
			$queries[] = "insert into message_values (id, user_id, time) values ('19', '".$konzernid."', '$time')";
		}
	}
	else { f("Konnte Konzernbild nicht finden - Nichts unternommen<br><br>Konzernbild hätte sein müssen bei:<br><i>".$ki_pfad_absolut."/konzern_".$userdaten[konzernid].".".$userdaten[image]."</i>");}
}

function ack($konzernid)	{

	global $ki_pfad_absolut;
	global $disapproved_pfad_absolut;
	global $queries;
	global $globals;
	global $time;

	select("insert into admin_konzernimages_approved (konzernid) values ($konzernid)"); # Wichtig damit Bild nicht nochmal erscheint beim näcshten Aufruf

}



?>
