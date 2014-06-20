<?


$gpacks_path = DATA."/syn_gpacks/";
$showpath = WWWDATA."syn_gpacks/";
$gfiles = array("jpg","png","gif");
$gpackdir = $gpacks_path."/$gid";
$gpackshowpath =$showpath."/$gid/";


if ($ia == "decsb" && $gid) {
	select("update gpacks set sortby=sortby-1 where gpack_id=$gid");
}
if ($ia == "incsb" && $gid) {
	select("update gpacks set sortby=sortby+1 where gpack_id=$gid");
}

if ($ia == "toggle" && $gid) {
	$gpack = assoc("select * from gpacks where gpack_id = $gid");
	
	// Sperren
	if ($gpack[visible] == 1) {
		select("update gpacks set visible=0 where gpack_id=$gid");
		denypermissions($gpackdir);
	}
	
	// Freischalten
	else {
		select("update gpacks set visible=1 where gpack_id=$gid");
		allowpermissions($gpackdir);
	}
}

// Löschen

if ($ia == "del" && $gid) {
	system("rm $gpackdir -r");
	select("delete from gpacks where gpack_id=$gid");
	select("update status set gpack_id = 5 where gpack_id=$gid");
	s("Das Grafikpaket wurde erfolgreich gelöscht");
}

//
//	STDVIEW
//
$stdlink = "index.php?action=checkgpacks";
if (!$view) {

	$gpacks = assocs("select * from gpacks order by sortby asc");
	
	$packets_in_use = assocs("select gpack_id, count(*) as tl from status group by gpack_id", "gpack_id");
	
	$ausgabe.="
	<table border=1>
		<tr>
			<td>Grafikpaket</td>
			<td>Benutzer</td>
			<td>Eingestellt von</td>
			<td>Eingestellt um</td>
			<td>Freigeschaltet</td>
			<td>Anzeigeposition</td>
			<td>Beschreibung</td>
			<td>Aktionen</td>
		</tr>
	";
	foreach ($gpacks as $temp) {
		$user = assoc("select * from users where id=$temp[syn_user_id]");
		
		if ($temp[visible] == 1) {
			$vstring = "<font color=\"green\">$temp[visible]</font>&nbsp;- <a href=\"$stdlink&ia=toggle&gid=$temp[gpack_id]\">sperren</a>";
		}
		else {
			$vstring = "<font color=\"red\">$temp[visible]</font>&nbsp;- <a href=\"$stdlink&ia=toggle&gid=$temp[gpack_id]\">freischalten</a>";
		}
	
		$ausgabe.="
			<tr>
				<td>$temp[name]</td>
				<td>".$packets_in_use[$temp['gpack_id']]['tl']."</td>
				<td>$user[username]</td>
				<td>".mytime($temp[time])."</td>
				<td align=\"center\">$vstring</td>
				<td align=center><a href=\"$stdlink&ia=decsb&gid=$temp[gpack_id]\"> -- </a>$temp[sortby]<a href=\"$stdlink&ia=incsb&gid=$temp[gpack_id]\"> ++ </a></td>
				<td>$temp[description]</td>
				<td><a href=\"$stdlink&view=show&gid=$temp[gpack_id]\">Inhalt Anzeigen</a> | <a href=\"$stdlink&view=del&gid=$temp[gpack_id]\">Grafikpaket löschen</a></td>
			</tr>
		";
	}
	
	$ausgabe.="</table>";
}



if ($view == "del") {
	if($gid) {
		$gpack = assoc("select * from gpacks where gpack_id=$gid");
			f("Das Grafikpaket ".$gpack['name']." wirklich löschen!
				<br><br>	<a href=\"$stdlink&ia=del&gid=$gpack[gpack_id]\">Ja, löschen!</a>
				<br><br> <a href=\"$stdlink\">Nein, nicht löschen</a>

			");
	}
}


//
//	Show
//

if($view == "show") {
	ob_start();
	$ausgabe.="
		<a href=\"$stdlink\">Zurück</a><br><br>
	";
	if ($gid) {
		$gpack = assoc("select * from gpacks where gpack_id=$gid");
		if (!$gpack[visible]) {
			allowpermissions($gpackdir);
		}

		$dirhandle = opendir($gpackdir);
		
		$ausgabe.="<table>";
		$i=1;
		while (($file = readdir($dirhandle)) !== false) {
			
			if ($i % 3 == 1) {
				$ausgabe.="<tr>";
			}
			
			$ausgabe.="<td width=200>$file<br>";
			// Verzeichnisse
			if (!preg_match("/.css/",$file) && !is_dir($file)) {
				$ausgabe.="<img src=\"$gpackshowpath/$file\"><br>";
			}
			$ausgabe.="</td>";

			if ($i % 3 == 0) {
				$ausgabe.="</tr>";
			}
			
			$i++;
		}
		$ausgabe.="</table>";
		echo $ausgabe;
		$ausgabe="";
		ob_end_flush();
		flush();
		ob_flush();
	}
}

function allowpermissions($gpackdir) {
		system("chmod 500 $gpackdir");
		system("chmod 444 $gpackdir/*");
		system("chmod 000 $gpackdir/*/*");
}

function denypermissions($gpackdir) {
		system("chmod 000 $gpackdir");
}


?>
