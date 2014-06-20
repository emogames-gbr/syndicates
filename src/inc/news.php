<?


############
define (DAYS_BOLD,3); // 3 Tage lang neue News dick anzeigen
############
$archive = $archive;
$anm_id = floor($anm_id); ## Vorher gab es eine Mysql-Injection -- Juni 2007
$anm_id > 0 ? 1 : $anm_id=0;
if ($comments != "true" or true) {unset($comments);}
if ($addcomment != "true" or true) {unset($addcomment);}
$text = htmlentities($text,ENT_QUOTES);
$poster = $userdata[username];

$announcement_exists = single("select count(*) from announcements where announcement_id = '".floor($anm_id)."'");



// Normale Ansicht

if (!$anm_id && !$archive) {
		?>
		<FONT class=ver12w>
		<table class="rand" cellspacing=1 cellpadding=3 width=100% align="center">
		<?
		// Announcement daten holen
		$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc limit 7");

				foreach ($anms as $temp) {
					$temp[content] = preg_replace("/\n/","<br>",$temp[content]);

					if (($time - $temp[time]) < (60*60*24*DAYS_BOLD)) {
						$trstrong = "<strong>";
						$trstrongende = "</strong>";
					}
					else {
						$trstrong = "";
						$trstrongende = "";
					}
					if (strlen($temp[headline]) > 26) {
						$temp[headline] = substr($temp[headline],0,23)."...";
					}
					if ($time - $temp[time] < 60*60*24) {$class="gelblink";} else { $class="gelblink";}
					$showtime = mytime($temp[time]);// date("d.m.y",$temp[time]);
					echo "
						<tr class=\"subhead\">
							<td align=left>
								$trstrong<a class=\"$class\" href=\"index.php?action=news&anm_id=$temp[announcement_id]\"><b>$temp[headline]</b></a>$trstrongende
							</td>
							<td align=right>
								<span >".$showtime."</span>
							</td>
						</tr>
						<tr class=\"body\">
							<td align=\"left\" colspan=\"2\">
								<br>";
								
//								pvar($temp[content]);
								//$temp[content] = preg_replace("/<\/?\w*>/","",$temp[content]);
	//							pvar($temp[content]);
								
								//echo "
								//".substr($temp[content],0,200)."";
								//if (strlen($temp[content]) > 200) {echo "...";}
								echo ("
									$temp[content]
								<br><br>
								<!--<table class=\"normal\" align=\"right\"><tr><td align=\"right\"><a class=\"$class\" href=\"index.php?action=news&anm_id=$temp[announcement_id]\">Weiterlesen</a></td></tr></table>-->
							</td>
						</tr>
					");
				}
				echo "
					<tr class=\"body\">
						<td colspan=2 align=center>
							<a href=\"index.php?action=news&archive=true\" class=\"gelblink\"><b>News Archiv</b></a>
						</td>
					</tr>
				";
			echo "</table><br><br>";}

if ($announcement_exists or $action=="news" && $archive) {

	if ($insert && $anm_id && $userid) {
		if ($announcement_exists) {
			$comments="true";
			echo "<p align=center>Ihr Kommentar wurde hinzugefügt</p><br>";
			$text = preg_replace("/\n/","<br>",$text);
			select("insert into announcements_comments (announcement_id,time,poster,text) values ($anm_id,$time,'$poster','$text')");

			header ("location: index.php?action=news&anm_id=$anm_id&comments=true&addmsg=true");
		}
	}

	if ($addmsg == "true") {
		echo "<p align=center>Ihr Kommentar wurde hinzugefügt</p><br>";
	}

		// Normal Anzeigen
		if ($addcomment && $userid) {
			if (strlen($poster) > 1) {
				echo "<br><br>";
				echo "
					<form action=\"index.php\" method=\"post\">
					<input type=hidden name=action value=\"news\">
					<input type=hidden name=anm_id value=\"$anm_id\">
					<input type=hidden name=insert value=\"true\">
					Kommentar schreiben:<br>

					<p align=center>
					<textarea name=text rows=4 cols=60></textarea>
					</p>
					<p align=right>
					<input type=submit value=\"abschicken\">
					</p>
					</form>
					<br><br>
				";
			}
			else {
				echo "Sie haben noch keinen Syndicates Account.";
			}
		}
		elseif ($addcomment && !$userid) {
			echo("Sie müssen sich zuerst einloggen, wenn sie News kommentieren wollen.<br><br><br><br>");

		}
		if ($anm_id && !$archive) {
			$data = assoc("select * from announcements where announcement_id=$anm_id");
			// \n durch <br> ersetzen für ausgabe
			$data[content] = preg_replace("/\n/","<br>",$data[content]);
			$commenctcount = single("select count(*) from announcements_comments where announcement_id=$anm_id");
			?>
				<table class=rand width="100%" align="center" cellspacing="1" cellpadding="0">
				<tr>
				<td width="100%">
					<table cellspacing="0" cellpadding="4" width="100%" class="head">
						<tr>
							<td align="left" width="40%"><em><? echo("<strong>$data[headline]</strong>"); ?></em></td>
							<td align="center" width="20%"><? ?></td>
							<td align="right" width="40%"><? echo("<b>$data[poster]"); echo ", "; echo (mytime($data[time]))."</b>";?></td>
						</tr>
					</table>
				</td>
				</tr>
				<tr>
				<td>
					<table class=body cellpadding=4 cellspacing=0 width=100%>
						<tr>
							<td colspan="3" class="ver11w" align="left">
							<p align="justify">
							<? echo("$data[content]"); ?>
							</p>
							</td>
						</tr>
					</table>
				</td>
				</tr>
				<?
				/*
				<tr>
				<td>
					<table class=foot cellpadding=4 cellspacing=0 width=100%>

						<tr>
							<td colspan="2" align=left width="50%"><a class="gelblink11" href="index.php?action=news&comments=true&anm_id=<? echo $anm_id; ?>">Kommentare lesen (<? echo $commenctcount;?>)</a></td>
							<td align=right width="50%"><a class="gelblink11" href="index.php?action=news&addcomment=true&anm_id=<? echo $anm_id; ?>">Kommentar schreiben</a></td>
						</tr>
					</table>
				</td>
				</tr>
				*/?>
				</table>

		<?
		}
		if ($comments==="true") {
			$comments= assocs("select * from announcements_comments where announcement_id=$anm_id order by time asc");
			if(count($comments) > 0) {
			?><br><br><p class=\"ver11w\">Kommentare:</p>
			<? foreach ($comments as $data) { ?>
				<table class="rand" width="100%" align="center" cellspacing="1" cellpadding="0">
				<tr>
				<td width="100%">
					<table cellspacing="0" cellpadding="4" width="100%" class="head">
						<tr>
							<td align="left" width="30%"><em><? echo("<b>".(($data[poster] == "Bogul" || $data[poster] == "Scytale") ? "<font class=gelb11><u>$data[poster]</u></font>":$data[poster])."</b>");?></em></td>
							<td align="center" width="40%"><? echo("<strong>$data[headline]</strong>");?></td>
							<td align="right" width="30%"><? echo (mytime($data[time]));?></td>
						</tr>
					</table>
				</td>
				</tr>
				<tr>
				<td>
					<table class=body cellpadding=4 cellspacing=0 width=100%>
							<tr>
							<td colspan="3" class="ver11w" align="left">
							<p align="justify">
							<br>
							<? echo("$data[text]"); ?>
							</p>
							</td>
						</tr>
					</table>
				</td>
				</tr>
				</table>
				<br>
			<? } // foreach ende?>
			<?
			} // Wenn comments
			else {echo "<br><br>Keine Kommentare vorhanden";}
		}
		if ($archive) {
			$data = assocs("select * from announcements where type='outgame' or type='both' order by time desc");
			?>
			<table width="600" cellspacing="1" cellpadding="0" align=left class=rand>
				<tr>
					<td>
						<table class= "head" cellspacing="0" cellpadding="4" width=100%>
							<tr>
								<td width="60%" align="left"><strong>Titel</strong></td>
								<td width="20%" align="center"><strong>Autor</strong></td>
								<td width="20%" align="center"><strong>Datum</strong></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class= "body" cellspacing="0" cellpadding="4" width=100%>
							<?
							foreach ($data as $temp) {
								if (($time - $temp[time]) < (60*60*24*DAYS_BOLD)) {
									$trstrong = "<strong>";
									$trstrongende = "</strong>";
								}
								else {
									$tstrong = "";
									$trstrongende = "";
								}
								echo ("
								<tr>
								<td width=\"60%\" align=\"left\">
									$trstrong<a href=\"index.php?action=news&anm_id=$temp[announcement_id]\" class=\"gelblink\">$temp[headline]</a>$trstrongende
								</td>
								<td width=\"20%\" align=\"center\">
									$temp[poster]
								</td>
								<td width=\"20%\" align=\"right\">
									".mytime($temp[time])."
								</td>
								</tr>
								");
							} // while
							?>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class= "foot" cellspacing="0" cellpadding="4" width=100%>
							<tr><td>&nbsp;</td></tr>
						</table>
					</td>
				</tr>
			</table>

			<?
		}
}



?>
