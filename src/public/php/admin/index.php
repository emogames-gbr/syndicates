<?

require ("../../../includes.php");
$handle = connectdb();
$globals = assoc("select * from globals order by round desc limit 1");
$time = time();
$queries = array();
$self = "index.php";

$adminuser = $REMOTE_USER;
if ($adminuser == "admin" || $adminuser=="nicolas"||$adminuser=="mura") {$isadmin = 1;}


$ausgabe_init = "<br><br><table width=60% align=center><tr><td>";
$ausgabe_end = "</td></tr></table>";

$formstart_get = "<form action=$self method=get><input type=hidden name=action value=\"$action\">";
$formstart_post = "<form action=$self method=post><input type=hidden name=action value=\"$action\">";



if ($inneraction): $inneraction = floor($inneraction); endif;
if ($innestaction): $innestaction = floor($innestaction); endif;

$konzernealive = single("select count(*) from status where alive=1");
$kozerneurlaub = single("select count(*) from status where alive=2");
$konzernetot = single("select count(*) from status where alive=0");



ob_start();

?>
<html>
<head>
<title>Syndicates Adminpanel</title>
<LINK REL="stylesheet" HREF="../style_color_default.css" TYPE="text/css">
</head>
<body>



<?




if ($action == "anm" && $isadmin)	{
	if ($inneraction == 1)	{
		$anm = preg_replace("/'/", "\'", $anm);
		$name = addslashes($name);
		$headline = preg_replace("/'/", "\'", $headline);
        if ($new ==="new") {
    		if ($announcement_type === "ingame" || $announcement_type === "both") {
                $queries[] = "update globals set announcement='$anm' where round=".$globals[round];
            }
            $queries[] = "insert into announcements (time,headline,content,poster,type) values ($time,'$headline','$anm','$name','$announcement_type')";
    		$ausgabe .= "<center><h1><font color=red>Announcement hinzugef�gt</font></h1></center>";
    		unset ($action);
        }
        elseif ($new === "edit") {
            $announcement_id = single("select announcement_id from announcements order by time desc limit 1");
            $queries[] = "update announcements set time=$time,headline='$headline',content='$anm',poster='$name',type='$announcement_type' where announcement_id=$announcement_id ";
    		$ausgabe .= "<center><h1><font color=red>Announcement ge�ndert</font></h1></center>";
    		unset ($action);
        }
	}
	else	{
		list($anm,$temptype,$headline,$poster) = row("select content,type,headline,poster from announcements order by time desc limit 1");
		$ausgabe .= "<center>
                        Aktuelles Announcement:<br><br><br>
                        <form action=$self method=post>
                        <select name=new>
                        <option value=edit>Aktuelles Announcement editieren
                        <option value=new>Neues Announcement erstellen
                        </select><br><br>
                        <table width=100%><tr>
                        <td align=left width=33%>Poster:<input name=name value=\"$poster\"></td>
                        <td align=center width=33%>�berschrift:<input name=headline value=\"$headline\"></td>
                        <td align=right width=33%></td>
                        </tr></table>
                        <input type=hidden name=action value=anm>
                        <input type=hidden name=inneraction value=1>
                        <textarea name=anm cols=80 rows=10>$anm</textarea><br><br>
                        Announcement Typ festlegen:
                        <select name=announcement_type>
                        <option value=ingame>Ingame Announcement
                        <option value=outgame ";if ($temptype === "outgame") {$ausgabe.= "selected ";} $ausgabe.=">Outgame Announcement
                        <option value=both ";if ($temptype === "both") {$ausgabe.= "selected ";} $ausgabe.=">Both
                        </select>
                        <input type=submit value=abschicken></form><br><br>
                        <a href=javascript:history.back()>zur�ck, ohne Ver�nderungen!</a>
                    </center>";
	}
}
elseif ($action == "upload" && $isadmin) {
	$filepath = DATA."adminfiles/".$_FILES[data][name];
	if (move_uploaded_file($_FILES['data']['tmp_name'],$filepath)) {
		system("chmod 000 $filepath");
		s("Datei erfolgreich hochgeladen");
	}
	else {
		f("Es ist ein fehler aufgetreten.");
	}
}
elseif ($action == "vki")	{ # View Konzern Images

include ("modules/konzernbilderverwaltung.php");

}

elseif ($action == "vks"  && $isadmin)	{ # View Klick Statistiken

include ("modules/klickstatistiken.php");

}

elseif ($action == "trackuser" && $isadmin) {

include ("modules/trackuser.php");

}

elseif ($action == "idswitch")	{ # View Klick Statistiken

include ("modules/idswitch.php");

}

elseif ($action == "traceuser")	{ # View Klick Statistiken

include ("modules/traceuser.php");

}


elseif ($action == "multifind")	{ # Multifindscript

include ("modules/findmultis.php");

}

elseif ($action == "checkgpacks")	{ # Multifindscript

include ("modules/checkgpacks.php");

}


elseif ($action == "delsettings")	{ # Multifindscript

include ("modules/delsettings.php");

}

elseif ($action == "checksyngroesse") {
include ("modules/checksyndikatsgroessen.php");
}
elseif ($action == "money")	{ # Multifindscript

include ("modules/money.php");

}
elseif ($action == "setmonument")	{ # Multifindscript

include ("modules/setmonument.php");

}
elseif ($action == "checkuser" && $isadmin) {
	include ("modules/checkuser.php");
}

elseif ($action == "vsi"   && $isadmin)	{ # View Konzern Images

include ("modules/syndikatsbilderverwaltung.php");

}

elseif ($action == "adminstats"  && $isadmin)	{ # View Konzern Images

include ("modules/adminstats.php");

}

elseif ($action == "ku"  && $isadmin)	{ # Kill User

include ("modules/killuser.php");

}

elseif ($action == "ban")	{ # User tempor�r bannen

include ("modules/tempban.php");

}

elseif ($action == "bvl"  && $isadmin)	{
	include ("modules/boersenvolumen.php");
}

elseif ($action == "sendingamemessage" && $isadmin) {
	include ("modules/sendingamemessage.php");
}

elseif ($action == "spystats" && $isadmin) {
	include ("modules/spystats.php");
}

elseif ($action == "detect_ip_mates" && $isadmin) {
	include ("modules/detect_ip_mates.php");
}


elseif ($action == "vas"  && $isadmin)	{ # View Anmelde Statistiken
	
	$ausgabe .= "<center><a href=$self>zur�ck</a></center><br><br>";
	
	if ($what != "lastupdatetime" and $what != "lastlogintime" and $what != "lastupdatetime2"): $what = "lastupdatetime"; endif;
	if ($what == "lastupdatetime"): $ausgabe .= "Wie viele haben sich wann angemeldet (inklusive toter Konzerne)?:<br><br><br>"; endif;
	if ($what == "lastupdatetime2"): $what="lastupdatetime";$ausgabe .= "Wie viele haben sich wann angemeldet (exklusive toter Konzerne)?:<br><br><br>"; $optional = " where alive > 0"; endif;
	if ($what == "lastlogintime"): $ausgabe .= "Wie viele haben sich wann zuletzt eingeloggt (exklusive toter Konzerne)?:<br><br><br>"; $optional = " where alive > 0"; endif;

	$daten=assocs("select $what as lastupdatetime from status$optional");
	$standardwidth = 500;


	foreach ($daten as $vl)	{
		$sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])]++;
		if ($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])] > $max)	{
			$max = $sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])][date("G",$vl[lastupdatetime])];
		}
		if (array_sum($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])]) > $daymax)	{
		$daymax = array_sum($sorted[date("M", $vl[lastupdatetime])][date("d",$vl[lastupdatetime])]);
		}
		$total++;
	}

	foreach ($sorted as $ky => $vl)	{
		foreach ($vl as $ky2 => $vl2)	{
			foreach (range(0,23) as $vl3)	{
				if (!$sorted[$ky][$ky2][$vl3]): $sorted[$ky][$ky2][$vl3] = 0; endif;
			}
		}
	}


	#Stunden

	krsort($sorted);
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Stunde</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		ksort($vl);
		foreach ($vl as $ky2 => $vl2)	{
			ksort($vl2);
			foreach ($vl2 as $ky3 => $vl3)	{
				$width = round($vl3 / $max * $standardwidth);
				$ausgabe .= "<tr><td>$ky2.$ky - Stunde $ky3</td><td align=right>$vl3</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
			}
		}
	}
	$ausgabe .= "</table>";
	
	# Tage
	$ausgabe .= "<br><br><br>Nach Tagen:<br><br>";
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Stunde</td><td align=center>Anzahl</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		ksort($vl);
		foreach ($vl as $ky2 => $vl2)	{
			$width = round(array_sum($vl2) / $daymax * $standardwidth);
			$ausgabe .= "<tr><td>$ky2.$ky</td><td align=right>".array_sum($vl2)."</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
		}
	}
	$ausgabe .= "</table>";
	
	
	$ausgabe .= "<br><br>Insgesamt: $total";

	$ausgabe .= "<br><br><br><center><a href=$self>zur�ck</a></center>";
}
elseif ($action == "vgv"  && $isadmin)	{ # View Gruppen Verteilungen
	$ausgabe .= "<center><a href=$self>zur�ck</a></center><br><br>";
	
	foreach (range(1,20) as $vl)	{
		$string .= "u".$vl.",";
	}
	$string = chopp($string);

	$daten=assocs("select $string from groups");
	$standardwidth = 500;
	
	
	foreach ($daten as $vl)	{
		$anzahl = 0;
		foreach (range(1,20) as $vl2)	{
			if ($vl[u.$vl2]): $anzahl++; $total++; endif;
		}
		$sorted[$anzahl]++;
		if ($sorted[$anzahl] > $max): $max = $sorted[$anzahl]; endif;
	}

	foreach (range(1,15) as $vl)	{
		if (!$sorted[$vl]): $sorted[$vl] = 0; endif;
	}
	
	
	#Stunden
	
	krsort($sorted);
	$ausgabe .= "<table cellpadding=3 cellspacing=4 border=0>";
	$ausgabe .= "<tr><td align=center>Anzahl Mitglieder</td><td align=center>Anzahl H�ufigkeit</td><td align=center>Grafik</td></tr>";
	foreach ($sorted as $ky => $vl)	{
		$width = round($vl / $max * $standardwidth);
		$ausgabe .= "<tr><td>$ky</td><td align=right>$vl</td><td><img src=dotpixel.gif width=$width height=10></td></tr>";
		$totalgruppen += $vl;
	}
	$ausgabe .= "</table>";
	
	$totalplayers = single("select count(*) from status where alive > 0");
	
	$ausgabe .= "<br><br>Insgesamt Spieler in Gruppen: $total / $totalplayers (".(round($total/$totalplayers*10000)/100)."%)";

	$ausgabe .= "<br><br>Insgesamt Gruppen: $totalgruppen";
	
	$ausgabe .= "<br><br><br><center><a href=$self>zur�ck</a></center>";
}
elseif ($isadmin) {
	include ("modules/$action.php");

}


?>

<?

if (!$action)	{

	$ausgabe .= "
	<center>

	<h2>
	Adminpanel Funktions�bersicht
	</h2>
	<br>
	<table border=1 cellpadding=3>
		<tr>
			<td>Konzernealive:</td>
			<td>$konzernealive</td>
		</tr>
		<tr>
			<td>Konzerneurlaub:</td>
			<td>$konzerneurlaub</td>
		</tr>
		<tr>
			<td>Konzernetot:</td>
			<td>$konzernetot</td>
		</tr>
	</table>
	<br><br>
		<B>Optionen</B><br><br><BR>
	<table border=1 cellpadding=5>
";

if ($isadmin) {
$ausgabe.="

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=anm><input type=submit value=durchf�hren></form>
	</td><td>
	Announcement festlegen.
	</td></tr>
	<tr height=50></tr>
	<!--
	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=asl><input type=submit value=durchf�hren></form>
	</td><td>
	Runde er�ffnen
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=rss><input type=submit value=durchf�hren></form>
	</td><td>
	Runde sofort starten (hier f�ngt die Runde nun wirklich an zu laufen und es k�nnen Aktionen durchgef�hrt werden)
	</td></tr>
	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=rsb><input type=submit value=durchf�hren></form>
	</td><td>
	Runde sofort beenden
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=rse><input type=submit value=durchf�hren></form>
	</td><td>
	Rundenstart eintragen
	</td></tr>
	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=ree><input type=submit value=durchf�hren></form>
	</td><td>
	Rundenende eintragen
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=str><input type=submit value=durchf�hren></form>
	</td><td>
	Runde vorbereiten (Leute nochmal Mixen, Gruppen zusammen lassen etc.)
	</td></tr>


	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=backup><input type=submit value=durchf�hren>
	</td><td>
	Backup einspielen
				<select size=1 name=whichbackup>
				<option>---deaktiviert---</option>
				</select>
	</form>
	</td></tr>
	-->
	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vks><input type=hidden name=what value=lastupdatetime><input type=submit value=durchf�hren></form>
	</td><td>
	Klickstatistiken ansehen
	</td><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=money><input type=hidden name=what value=money><input type=submit value=durchf�hren></form>
	</td><td>
	Money
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vas><input type=hidden name=what value=lastupdatetime><input type=submit value=durchf�hren></form>
	</td><td>
	Anmeldestatistiken ansehen
	</td>
	<td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vas><input type=hidden name=what value=lastlogintime><input type=submit value=durchf�hren></form>
	</td><td>
	Wer hat sich wann zuletzt eingeloggt ?
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vgv><input type=submit value=durchf�hren></form>
	</td><td>
	Mitgliederverteilungen auf die Gruppen
	</td>
	<td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=ku><input type=submit value=durchf�hren></form>
	</td><td>
	User L�schskript
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=adminstats><input type=submit value=durchf�hren></form>
	</td><td>
	Use Stats
	</td>
	<td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=spystats><input type=submit value=durchf�hren></form>
	</td><td>
	Spystats
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vsi><input type=submit value=durchf�hren></form>
	</td><td>
	Syndikatsimages ansehen / l�schen
	</td>
	<td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=sendingamemessage><input type=submit value=durchf�hren></form>
	</td><td>
	Ingamenachrichten verschicken
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=bvl><input type=submit value=durchf�hren></form>
	</td><td>
	B�rsenvolumen ansehen
	</td>
	<td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=checkuser><input type=submit value=durchf�hren></form>
	</td><td>
	Angriffe/Spionage von User checken
	</td></tr>
	<tr><td align=center valign=middle>
		<form enctype=\"multipart/form-data\" action=$self method=post>
			<input type=hidden name=action value=upload>
				<input type=\"file\" name=\"data\" size=1><input  type=\"submit\" value=\"upload\">
		</form>
	</td><td>
	Upload file
	</td>
	<td align=center valign=middle>
	
	<form action=$self><input type=hidden name=action value=au_daystats><input type=submit value=durchf�hren></form>
	</td>
	<td>Aktive User (24h)</td>
	</tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=serveruserstats><input type=submit value=durchf�hren></form>
	</td><td>
	Erweiterte Server-User-Statistiken
	</td>
	<td>
	<form action=$self><input type=hidden name=action value=trackuser><input type=submit value=durchf�hren></form>
	</td>
	<td>
	Erweitertes User Tracking
	</td>
	
	
	</tr>
";
}


$ausgabe.="


	<tr height=50></tr>
	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=vki><input type=submit value=durchf�hren></form>
	</td><td>
	Konzernimages ansehen / l�schen
	</td><td align=center valign=middle><form action=$self><input type=hidden name=action value=checksyngroesse><input type=submit value=durchf�hren></form></td><td>Syndikatsgr��e �berpr�fen</td></tr>



	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=ban><input type=submit value=durchf�hren></form>
	</td><td>
	User Tempor�r bannen
	</td>
	<td align=center valign=middle><form action=$self><input type=hidden name=action value=detect_ip_mates><input type=submit value=durchf�hren></form></td><td>IP-Partner zu Spieler finden</td>
	</tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=delsettings><input type=submit value=durchf�hren></form>
	</td><td>
	User Konzernbeschreibung l�schen
	</td>
	<td>
	<form action=$self><input type=hidden name=action value=checkgpacks><input type=submit value=durchf�hren></form>
	</td>
	<td>
	Grafikpakete freischalten/�berpr�fen
	</td>
	</tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=multifind><input type=submit value=durchf�hren></form>
	</td><td>
	Multis suchen
	</td>
	<td>
	<form action=$self><input type=hidden name=action value=k-stats><input type=submit value=durchf�hren></form>
	</td>
	<td>
	Krawall User auf Runden
	</td>
	
	
	</tr>


	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=traceuser><input type=submit value=durchf�hren></form>
	</td><td>
	Spieler aktionen verfolgen
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=idswitch><input type=submit value=durchf�hren></form>
	</td><td>
	Spieler User_id Konzernid name switchen
	</td></tr>

	<tr><td align=center valign=middle>
	<form action=$self><input type=hidden name=action value=setmonument><input type=submit value=durchf�hren></form>
	</td><td>
	Monument setzen
	</td></tr>

	</center>";
}

if ($queries): db_write($queries, 1); endif;

echo "<center>$fehler</center>";
echo "<center>$successmeldung</center>";
echo "<center>$ausgabe</center>";
?>
</body>

</html>
<? ob_end_flush(); ?>
