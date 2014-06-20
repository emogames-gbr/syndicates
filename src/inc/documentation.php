
<?
if (!$aid) { 
	?>
	<div align="right">
		<form action="index.php?action=docu" method="post">
			Suche:
			<input name="search" value="">
		</form>
	</div>
	<?
}
?>

<?
require_once(INC."ingame/globalvars.php");
// Falls keine Kategorie Übergeben wurde
$kat = (int)$kat;
$aid = (int)$aid;
$commentcount=0;
if ($comments != "true") {unset($comments);}
if ($delete != "true") {unset($delete);}
if ($addcomment != "true") {unset($addcomment);}
if ($confirm != "true") {unset($confirm);}
$text = htmlentities($text,ENT_QUOTES);
$poster = $userdata[username];
$admins = array("Bogul","scytale","Adien");

// Hitstats extern updaten
$what = "documentation";
$today = date("d.m.Y",$time);
$exists = mysql_query("select date from hitstats_extern where date ='$today'");
$exists = mysql_fetch_row($exists);
$exists = $exists[0];
if (!$exists) {
    mysql_query("insert into hitstats_extern (date,$what) values('$today','1')");
}
if ($exists) {
    mysql_query("update hitstats_extern set $what=$what+1 where date='$today'");
}


define ('WIKI', 'http://www.syndicates-wiki.de/index.php');
echo "<b>ACHTUNG:</b><br>Du befindest dich hier auf der alten Anleitung, der sog. Referenz. <br>Manche Informationen können veraltet sein.<br>Die offizielle Spielanleitung findest du <a href=".WIKI." class=gelblink>hier</a>.<br>Eine Woche vor Rundenende werden <a href=index.php?action=docu&kat=2&aid=27 class=gelblink>hier</a> die in der nächsten Runde verfügbaren Partnerschaftsboni bekannt gegeben.<br><br><br>";


// Standardtext
if ($search) {
	$search =htmlentities($search,ENT_QUOTES);
	$search = preg_replace("/%/","\%",$search);
	$suche = $search;
	
	
	echo "<p align=\"left\">Suchergebnisse zum Begriff: <i>".$suche."</i><br><br>";
	$suche_result = array();
	$anleitung = assocs("select * from anleitung where visible=1");
	$suche_result[] = assocs("select * from anleitung where headline like '%".$suche."%' and visible=1 order by kategorie,showposition");
	$suche_result[] = assocs("select * from anleitung where description like '%".$suche."%' and visible=1 order by kategorie,showposition");
	//$result[] = assocs("select * from anleitung where text like '%".$suche."%' order by kategorie,showposition");
	foreach ($anleitung as $temp_r) {
		ob_start();
		eval(fgc(INC."anleitung/$temp_r[filename]"));
		$tfilecontent = ob_get_contents();
		ob_clean();
		
		if (preg_match("/$suche/i",$tfilecontent)) {
			$suche_result[] = array($temp_r);
		}
	}

	$i = 0;
	$aids = array();
	if (is_array($suche_result)) {
		foreach ($suche_result as $assoc) {
			foreach ($assoc as $value) {
				if (!in_array($value[anleitung_id],$aids)) {
					$i++;
					echo "$i. <a href=\"index.php?action=docu&kat=".$value[kategorie]."&aid=".$value[anleitung_id]."&suche=$suche\" class=\"gelblink\">".$value[topic]."</a><br>".$value[description]."<br><br>";
				}
				$aids[] = $value[anleitung_id];
				unset($value);
			}
		}
	}
	if ($i == 0) {
		echo "<i>Es wurden keine Ergebnisse zum angegebenen Suchbegriff gefunden!</i><br><br><br><br>";
		$search ="";
	}
	echo "</p>";
}



$kategorien = assocs("select * from anleitung_kategorien order by kategorie_id","kategorie_id");
if (!$kat && !$search) {
	?>
	<b><? echo "$anleitungdot"; ?> Spielanleitung</b><br><br>
	Jeder Spieler übernimmt die Kontrolle über einen Konzern. Bis zu 20 Konzerne bilden ein Syndikat. Eine Stunde Realzeit entspricht einem Zug bei Syndicates. Eine Spielrunde dauert für gewöhnlich sechs bis acht Wochen, danach gibt es einen Reset und jeder Spieler fängt wieder bei null an.<br>
	Die Spieler in einem Syndikat spielen jeweils zusammen gegen alle anderen Syndikate.<br>
	Aufgabe des Spielers ist es, den eigenen Konzern so mächtig wie möglich zu gestalten.<br><br>
	Dazu stehen ihm viele Möglichkeiten offen. Spezialisieren Sie sich auf heimtückische Spionageangriffe,
	offene Vernichtung, erlangen Sie Macht durch wissenschaftlichen Fortschritt oder kaufen Sie ganze Syndikate auf.<br><br>
	<br><br>
	<?
	$anleitung = assocs("select * from anleitung where visible=1 order by kategorie asc,showposition asc","anleitung_id");
	$count = count($kategorien);
	

	
	foreach ($kategorien as $temp) {
		$katausgabe.="<a class=\"gelblink\" href=\"index.php?action=docu&kat=".$temp[kategorie_id]."\">".$temp[name]."</a><br>".$temp[details]."<br><br>";
	}
	
	$katausgabe.="
		<br><br>
		<table width=\"100%\" class=\"normal\" cellpadding=\"2\">
		<tr class=\"head\">
			<td align=\"center\">
				<b>Nach Menüpunkten</b>
			</td>
		</tr>
		<tr class=\"normal\">
			<td>
				<br>
			";
			foreach ($anleitung as $temp) {
				
				if($temp[menupoint]) {
					$katausgabe.="
						<li><a class=\"gelblink\" href=\"index.php?action=docu&kat=$temp[kategorie]&aid=$temp[anleitung_id]\">".$temp[headline]."</a> 	
					";
				}
			}
			$katausgabe.="
			</td>
		</tr>
		</table>
	";
	
	
	$tempkat=0;
	$indexausgabe.="<ol type=\"1\">";
	foreach ($anleitung as $temp) {
		$warnbasic = "";
		if ($temp[basicavailable] == 0) {
			$warnbasic = "[Nur Classic Server]";
		}
		
		$indexend = "";
		if ($temp[kategorie] != $tempkat) {
			
			
			$tempkat != 0 ? $indexend = "</ol>" : $indexend = "";
			$indexausgabe.="
				$indexend
				<br><li> <b><a class=\"gelblink\" href=\"index.php?action=docu&kat=$temp[kategorie]\">".$kategorien[$temp[kategorie]][name]."</a> </b>
					<br>
					<i>".$kategorien[$temp[kategorie]][details]."</i>
					<br><br>
					<ol type=1>
			";
		}
		
		if (!preg_match("/KOINS/",$temp[headline]) || isKsyndicates()) {
			$indexausgabe.="
				<li><a class=\"gelblink\" href=\"index.php?action=docu&kat=$temp[kategorie]&aid=$temp[anleitung_id]\">".$temp[headline]." </a> $warnbasic
			";
		}
		
		
		$tempkat = $temp[kategorie];
	}
	$indexausgabe.="
			</ol>
		</ol>
	";
	
	/*
	$mainausgabe = "
		<table width=\"90%\" class=\"normal\" cellpadding=\"2\">
			<tr class=\"head\">
				<td align=\"center\">
					<b>Nach Kategorien</b>
				</td>
				<td align=\"center\">
					<b>Index</b>
				</td>
			</tr>
			<tr class=\"normal\" valign=\"top\">
				<td width=\"50%\">
					<br>
					$katausgabe
				</td>
				<td width=\"50%\">
					$indexausgabe
				</td>
			</tr>
		</table>
	";
	*/
	
	$mainausgabe="
		<table width=\"90%\" class=\"normal\" cellpadding=\"2\">
			<tr class=\"head\">
				<td align=\"center\">
					<b>Syndicates Spielanleitung</b>
				</td>
			</tr>
			<tr class=\"normal\" valign=\"top\">
				<td width=\"50%\">
					$indexausgabe
				</td>
			</tr>
		</table>
	";			
	echo $mainausgabe;
	
	echo "
		<br>
		<a href=\"print.php\" target=\"_blank\" class=\"gelblink\"><b>Komplette Anleitung in Druckansicht</b></a>
		<br><br>
	";
	?>
	<br>
	Falls dennoch Fragen offen bleiben, findest du sicher Hilfe im <a class="gelblink" href="http://board.BETREIBER.de/board.php?boardid=14" target="_blank">Fragen und Antworten</a> Forum oder wendest dich einfach an unseren <a class="gelblink" href="mailto:support@BETREIBER.de">E-Mail-Support</a>.<br>
	
	<?
}


elseif (!$search) {
	// Falls keine anleitungsid übergeben wurde:
	if (!$aid) {
		$content = assocs("select * from anleitung where kategorie = $kat and visible=1 order by showposition");
		$kategorie = assoc("select * from anleitung_kategorien where kategorie_id=$kat");
		$ausgabe.="<br>$anleitungdot <b><a href=\"index.php?action=docu\" class=\"gelblink\">".Anleitung."</a> -> <u> ".$kategorie[name]."</u></b><br><br><i>".$kategorie[details]."</i><br><br><table class=\"normal\" width=\"80%\"><tr><td><ul>";

		foreach ($content as $topic) {
			$warnbasic = "";
			if ($topic[basicavailable] == 0) {
				$warnbasic = "[Nur Classic Server]";
			}
			$ausgabe.="<li><a class=\"gelblink\" href=\"index.php?action=docu&kat=$kat&aid=".$topic[anleitung_id]."\">".$topic[topic]."</a> $warnbasic<br>".$topic[description]." <br><br>";

		}
		$ausgabe.="</ul></td></tr></table>";
		$ausgabe.="<br><br><a href=\"index.php?action=docu\" class=\"gelblink\">zurück</a>";

	}

	else {
		$commenctcount = single("select count(*) from anleitung_comments where anleitung_id=$aid");

		// text anzeigen
		if (!$addcomment && !$delete) {
			select("update anleitung set klicks=klicks+1 where anleitung_id=$aid and visible=1");
			$content = assoc("select * from anleitung where anleitung_id=$aid and visible=1");
				echo "
					<table width=\"100%\">
						<tr>
							<td class=\"normal\" align=\"left\">
								<br>$anleitungdot <b><a href=\"index.php?action=docu\" class=\"gelblink\">".Anleitung."</a> -> <a href=\"index.php?action=docu&kat=$content[kategorie]\" class=\"gelblink\">".$kategorien[$content[kategorie]][name]."</a> -> <u>".$content[headline]."</u></b>
							</td>
							<td align=\"right\">
								<a class=\"gelblink\" href=\"print.php?aid=$aid\" target=\"_blank\">Druckansicht</a>
							</td>
						</tr>
					</table><br>";
				if ($content[menupoint]) {
					echo "Menüpunkt: <i>".$content[menupoint]."</i><br><br>";
				}
				
			ob_start();	
			$content[text] = fgc(INC."anleitung/".$content[filename]);
			eval($content[text]);
			$content[text] = ob_get_contents();
			ob_clean();
			if ($suche) {
				$content[text] = preg_replace("/($suche)/i","<span style=\"color:blue;background:yellow\">$1</span>",$content[text]);
			}
			echo $content[text];
		}
		//<a href=\"index.php?action=docu&kat=$kat\" class=\"gelblink\">zurück</a>
		if (!$delete) {
			$ausgabe.="<br><br>
				<table width=\"100%\"><tr><td align=left class=\"normal\">
				<a href=\"javascript:history.back()\" class=\"gelblink\">zurück</a>
				</td>
				<td align=right class=\"normal\">
				<a class=\"gelblink\" href=\"index.php?action=docu&addcomment=true&kat=$kat&aid=$aid\">Anmerkung schreiben</a>
				</td>
				</tr>
				</table>
				<br><br>Autor: ".$content[author]."
			";
		}
		// <a class=\"gelblink\" href=\"index.php?action=docu&comments=true&kat=$kat&aid=$aid\">Anmerkungen lesen ($commenctcount)</a> /

		// Anmerkung hinzufügen
		if ($insert && $aid && $poster && $userid > 0) {
			$comments="true";
			//echo "<p align=center>Ihr Anmerkung wurde hinzugefügt</p><br>";
			$commentcount++;
			$text = preg_replace("/\n/","<br>",$text);
			select("insert into anleitung_comments (anleitung_id,time,poster,text) values ($aid,$time,'$poster','$text')");
		}

		// Anmerkungen lesen
		if ($comments==="true" || 1 && !$delete) {
			$comments= assocs("select * from anleitung_comments where anleitung_id=$aid order by time asc");
			if(count($comments) > 0) {
			?><br><br><hr><p class=\"ver11w\">Anmerkungen:</p>
				<? foreach ($comments as $data) {
					printcomment($data[poster],$data[time],$data[text],$poster,$data[cid],1);
				}
			}
			elseif ($comments==="true") {echo "<br><br><hr>Keine Anmerkungen vorhanden";}
		}

		//Anmerkungsformular
		if ($addcomment && $userid) {
			if (strlen($poster) > 1) {
				echo "<br><br>";
				echo "
					<form action=\"index.php\" method=\"post\">
					<input type=hidden name=action value=\"docu\">
					<input type=hidden name=kat value=\"$kat\">
					<input type=hidden name=aid value=\"$aid\">
					<input type=hidden name=insert value=\"true\">
					Anmerkung schreiben:<br>
					(Themenfremde Anmerkungen werden kommentarlos gelöscht)
					<br>


					<p align=center>
					<textarea name=text rows=4 cols=60></textarea>
					</p>
					<p align=right>
					<input type=submit value=\"abschicken\">
					</p>
					</form>
				";
			}
			else {
				echo "<br><br>Sie haben noch keinen Syndicates Account.";
			}
		}
		elseif ($addcomment && !$userid) {
			echo "<br><br>Sie müssen sich zuerst einloggen, wenn sie eine Anmerkung schreiben wollen.<br><br>
			<a href=\"javascript:history.back()\" class=gelblink>Zurück</a>";
			$ausgabe = "";
		}

		// Kommentar Löschen
		if ($userid && $delete && $cid) {
			$comment = assoc("select * from anleitung_comments where cid=$cid");
			if ($comment[poster] != $poster && !in_array($poster,$admins)) {
				echo "Sie können keine Anmerkungen von anderen Spielern löschen!";
			}
			else {
				if (!$confirm) {
					echo "Wollen sie diese Anmerkung wirklich löschen ?<br><br><br>";
					printcomment($comment[poster],$comment[time],$comment[text],$poster, $data[cid],0);

					echo "<table width=100% align=center><tr><td align=center><a class=\"gelblink\" href=\"index.php?action=docu&kat=$kat&aid=$aid&delete=true&cid=$comment[cid]&confirm=true\">Ja</a></td></tr>";
					echo "<tr><td align=center><a class=\"gelblink\" href=\"index.php?action=docu&kat=$kat&aid=$aid&comments=true\">Nein</a></td></tr></table>";
				}
				else {
					select("delete from anleitung_comments where cid=$cid");
					echo "Ihre Anmerkung wurde gelöscht!<br><br>";
					echo "<center><a class=\"gelblink\" href=\"index.php?action=docu&kat=$kat&aid=$aid&comments=true\">Weiter</a></center>";

				}
			}
		}



	}
}
echo $ausgabe;


function printcomment($poster,$time,$text,$user,$cid,$mode) {
	global $aid,$kat,$admins;
	?>
				<table class="rand" width="100%" align="center" cellspacing="1" cellpadding="0">
				<tr>
				<td width="100%">
					<table cellspacing="0" cellpadding="2" width="100%" class="head">
						<tr >
							<td align="left" width="30%"><em><? echo("<b>$poster</b>");?></em></td>
							<td align="center" width="40%">
							<?
								if ($poster==$user && $mode==1 || in_array($user,$admins)) {
									echo "<a class=\"gelblink\" href=\"index.php?action=docu&kat=$kat&aid=$aid&delete=true&cid=$cid\">Anmerkung löschen</a>";
								};
							?>
							</td>
							<td align="right" width="30%"><? echo (mytime($time));?></td>
						</tr>
					</table>
				</td>
				</tr>
				<tr>
				<td>
					<table class=body cellpadding=4 cellspacing=0 width=100%>
							<tr>
							<td colspan="3" align="left">
							<p align="justify">
							<br>
							<? echo("$text"); ?>
							</p>
							</td>
						</tr>
					</table>
				</td>
				</tr>
				</table>
				<br>
	<?
}

function fgc($path) {
	$HANDLE = fopen($path,r);
	while ($LINE = fgets($HANDLE,1024)) {
		$back .=$LINE;
	}
	fclose($HANDLE);
	return $back;
}

?>


