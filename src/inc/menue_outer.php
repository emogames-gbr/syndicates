<!-- login -->
<?
$betreiber = "BETREIBER";
$forenlink = "http://board.emogames.de/board.php?boardid=6";


if (isKsyndicates()) $betreiber = "BETREIBER2";
?>
		<table width="120" cellspacing="0" cellpadding="0" border="0" bgcolor="Black">
        <tr>
            <td>
				<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
				<tr>
					<td>
						<table width="120" cellspacing=0 cellpadding=2 border=0 class=head>
								<tr>
									<td align="left"><b>&nbsp;<?=$betreiber?>-Login</b></td>
								</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>

	<!-- login inhalt -->
			<table width="120" cellspacing="3" cellpadding="0" class=bodys>
			<? if ($sid || 1) { ?>
			<tr>
				<td align="center">
					<?php
	// Wenn Runde nicht beendet ist
	if ($new != 2 || 1==1) {
					// Wenn Schnittstelle online
					if ($sid && $userdata[id]) {
						// Falls sid vorhanden, spiel möglich

						if (!$userdata[konzernid] && $new != 2) {
							
							// Checken ob nicht auf einem anderen Server ein Konzern ist:
							foreach ($servers as $temp) {
								mysql_select_db($temp[db_name]);
								$temporary_userdata = assoc("select * from users where emogames_user_id = '$userdata[emogames_user_id]'");
								if ($temporary_userdata[konzernid]) {
									$akey = createkey($autologinkey);
									select("update users set autologinkey='$akey' where emogames_user_id='$userdata[emogames_user_id]'");
									header("Location: $temp[url]/index.php?autologinkey=$akey");exit(1);
								}
							}
							connectdb();

							
							
							echo ("Willkommen $userdata[username]<br><br>
							<a href=\"index.php?action=anmeldung\" class=\"gelblink11\">Neuen Konzern erstellen</a><br><br>");
						}
						elseif ($new == 2 && $globals[roundendtime] < ($time - ROUND_FREEZETIME_DURATION)) {
								if ($userdata[username]) {
									echo ("Willkommen $userdata[username]<br><br>");
								}
								echo ("Die aktuelle Runde ist beendet<br><br>");
						}
						else {
							$pathset = single("select imagepath from status where id=$userdata[konzernid]");
							if ($pathset) {$pathset = "checked";}
							$code_id = getcodeid();
							if (isKsyndicates()) {
								echo "<div>Eingelogt als<br><b>$userdata[username]</b></div><br>";
							}
							echo ("
									<form style=\"margin:0px\" action=\"php/login.php\" method=\"post\" name=codelogin>
									Logincode:<br><br>"
							);
								//echo (showusercode($userdata[konzernid],$code_id));
								echo '<img src="captcha.php?='.$time.'" />';
								echo ("<br><br>
								<input name=\"codeinput\" size=\"4\" style=\"font-size:9px;height:17px\"><br><br>");
								if($pathset) { echo "<table class=bodys><tr><td>Grafikpaket verwenden:</td><td> <input name=usepacket type=checkbox $pathset></td></tr></table><br>";}
								echo ("<input type=submit value=\"Zum Spiel\" style=\"font-size:9px;vertical-align:middle;border: 1px outset;margin-top:0px;margin-bottom:0px\">
							</form></td></tr>");
						}

					}

				// Nicht eingelogt, user Normal einloggen
				else {

					
				if (!isKsyndicates()) {
					echo ("
					<form style=\"margin:0px\" action=\"$self\" method=\"post\" name=loginform>
								<input type=hidden name=\"action\" value=\"login\">
								&nbsp;<b>Name:</b><br>
								&nbsp;<input type=\"text\" name=\"user\" maxlength=\"30\" style=\"width:100px;height:18px;font-size:11px;font-family:verdana;\"></td>
							</tr>
							<tr><td height=\"1\"></td></tr>
							<tr>
								<td align=\"center\">
									&nbsp;<b>Kennwort:</b><br>
									&nbsp;<input type=\"password\" name=\"password\" maxlength=\"30\" style=\"width:100px;height:18px;font-size:11px;font-family:verdana;\">
									<tr><td height=\"3\" colspan=\"1\"></td></tr>
									<tr><td height=\"1\" colspan=\"1\" bgcolor=\"#000000\">
								</td>
							</tr>
							<tr><td height=\"2\"></td></tr>
							<tr><td height=\"2\" align=center>Login speichern<input name=savelogin type=checkbox $savelogin></td></tr>
							<tr><td height=\"2\"></td></tr>
							<tr>
								<td align=\"center\" >
									&nbsp;<input type=\"submit\" value=\"Login\" style=\"width:50px;font-size:11px;font-family:verdana;\" >&nbsp;&nbsp;&nbsp;</form>
								</td>
							</tr>")
							;
	
					}

				}
	} // Wenn Runde beendet
	/*
	else {
			if ($userdata[username]) {
				echo ("Willkommen $userdata[username]<br><br>");
			}
			echo ("Die aktuelle Runde ist beendet<br><br>");
	}
	*/
	?>
			</td>
		</tr>

				<?
				
				if (strlen($sid) > 10 && (!isKsyndicates() || $userdata[username]) ) {
					
					echo ("
				<tr><td height=\"1\" colspan=\"1\"></td></tr>
				<tr><td height=\"2\"></td></tr>
				<tr>
					<td align=\"center\" $bodyover $bodyout>"); ?>
					<a class=ver11s href="index.php?action=logout"><? echo "$logoutdot10"; ?>Logout<? echo "$logoutdot10"; ?></a>
				<?
				echo ("
				</tr>
				<tr><td height=\"2\"></td></tr>
				");
				}

} // If $sid


?>
			</table>
	<!-- login inhalt ende -->
		</td></tr></table>
		</td></tr></table>
<!-- login ende -->
	<br>
<!-- loggedin options -->
<? if ($sid && $userdata ) { ?>
		<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
        <tr>
            <td>
				<table width="120" cellspacing="0" cellpadding="2" border="0" class=head>
					<tr>
						<td align="left">
							<b><b>&nbsp;Mein Account</b></b>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td >
				<table width="120" cellspacing=0 cellpadding=3 border=0 class=body>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?> onClick="window.location='index.php?action=stats';">
						<script type="text/javascript"><!--
							document.write('<? echo "$statsdot10"; ?><span style="cursor:pointer;" class="ver11s">Statistiken</span>');
						--></script>
						<noscript>
							<a class=ver11s href="index.php?action=stats"><? echo "$statsdot10"; ?>Statistiken</a>
						</noscript>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		<br>
<? } // IF sid und userdata
?>

<!-- loggedin options ende -->
<!-- menü -->
		<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
        <tr>
            <td>
            		<table width="120" cellspacing="0" cellpadding="2" border="0" class=head>
            		<tr>
                        <td align="left">
                   		    <b><b>&nbsp;Menü</b></b>
                        </td>
                    </tr>
					</table>
			</td>
		</tr>

		<tr>
			<td >
				<table width="120" cellspacing=0 cellpadding=3 border=0 class=body>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=main"><? echo "$startseitedot10"; ?>Startseite</a>
					</td>
				</tr>
				<tr>
					<?
						$latestnews = single("select time from announcements order by time desc limit 1");
						if (time() - $latestnews < 60*60*24*3) {
							$newsstring = "<span style=\"cursor:pointer;color:#AA0000\" class=\"ver11s\">News!</span>";
						}
						else {
							$newsstring = "<span style=\"cursor:pointer\" class=\"ver11s\">News</span>";
						}
					?>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=news"><? echo "$startseitedot10"; ?><?=$newsstring?></a>
					</td>
				</tr>
				<? if (!$userdata[konzernid]) { ?>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=anmeldung"><? echo "$anmeldungdot10"; ?>Anmeldung</a>
					</td>
				</tr>
				<? }  // Anmeldungspunkt nur anzeigen, wenn noch kein KOnzern erstellt wurde?>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s target="_blank" href="<?=WIKI?>"><? echo "$anleitungdot10"; ?>Anleitung</a>
					</td>
				</tr>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" href="<?=$forenlink?>" target="_blank" class=ver11s><? echo "$forumdot10"; ?>Forum</a>
					</td>
				</tr>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=story"><? echo "$storydot10"; ?>Story</a>
					</td>
				</tr>
				<!--
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=nutzungsbedingungen"><? echo "$nutzungsbeddot10"; ?>Nutzungsbed.</a>
					</td>
				</tr>
				-->
				<tr>
				
     				<td class=body <? echo ("$bodyover $bodyout");?>> 
							<a style="text-decoration:none" class=ver11s href="index.php?action=fame"><? echo "$hofdot10"; ?>Hall of Fame</a>
					</td>
				</tr>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=gamestats"><? echo "$hofdot10"; ?>Statistiken</a>
					</td>
				</tr>
				<!--
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="http://syn-aktuell.goroth.de/" target="_blank"><? echo "$hofdot10"; ?>Syn Aktuell</a>
					</td>
				</tr>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=docu"><? echo "$impressumdot10"; ?>Referenz</a>
					</td>
				</tr>
				<tr>
     				<td class=body <? echo ("$bodyover $bodyout"); ?>>
							<a style="text-decoration:none" class=ver11s href="index.php?action=impressum"><? echo "$impressumdot10"; ?>Impressum</a>
					</td>
				</tr>
				-->
				</table>
				</td>
</td></tr></table>
<!-- menu ende -->
<br>
<!-- status -->
	<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
		<tr>
			<td>
				<table width="120" cellspacing="0" cellpadding="3" border="0" class=head>
					<tr>
						<td align="left"><b>&nbsp;Spielstatus</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width=100% cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>

						<?php
						$wochentage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
						if ($new == "2") {
							$uhrzeit1 = date("H:i", $globals['roundstarttime']);
							$uhrzeit2 = "20:00"; // Anmeldephase immer erst ab 20:00 Uhr
							$roundstarttime = $globals['roundstarttime'] + 56 * 86400;
							$registertime = $roundstarttime - 3 * 86400;
							if ($globals['round'] == 44 && getServertype() == "classic") $uhrzeit1 = date("H:i", $globals['roundstarttime'] - 6 * 3600);

							
							$tag = datum("d.m.Y", $registertime);
							if (!preg_match("/heute/", $tag)) $tag = $wochentage[date("w", $registertime)].", ".$tag;
							$termine = "<br><br><b>Anmeldephase</b> ab<br>".$tag."<br>".$uhrzeit2." Uhr";

							$tag = datum("d.m.Y", $roundstarttime);
							if (!preg_match("/heute/", $tag)) $tag = $wochentage[date("w", $roundstarttime)].", ".$tag;
							$termine .= "<br><br><b>Rundenstart</b> am<br>".$tag."<br>".$uhrzeit1." Uhr";
						
							print "<b class=\"rot11\"> Runde beendet</b>$termine";
						}
						if ($new == "0") {
							$uhrzeit = date("H:i", $globals['roundstarttime']);
							$roundstarttime = $globals['roundstarttime'];

							$tag = datum("d.m.Y", $roundstarttime);
							if (!preg_match("/heute/", $tag)) $tag = $wochentage[date("w", $roundstarttime)].", ".$tag;
							$termine = "<br><br><b>Rundenstart</b> am<br>".$tag."<br>".$uhrzeit." Uhr";
							
							print "<b class=\"gelb11\"> Anmeldephase</b><br><br>Die Runde startet in kürze. Anmelden um von Anfang an dabei zu sein!$termine";
						}
						if ($new == "1") {
							$uhrzeit = date("H:i", $globals['roundendtime']);
							$roundendtime = $globals['roundendtime'];
							
							$tag = datum("d.m.Y", $roundendtime);
							if (!preg_match("/heute/", $tag)) $tag = $wochentage[date("w", $roundendtime)].", ".$tag;
							$termine = "<br><br><b>Rundenende</b> am<br>".$tag."<br>".$uhrzeit." Uhr";
							
							print "<b class=\"hellgruen11\"> Runde läuft</b>$termine";
						}		?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
				</table>
			</td>
		</tr>
	</table>
<!-- status ende -->
<br>
<!-- Zeitungen -->
<? if ($new == 1) { ?>
<!-- wars -->
    		<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
				<tr>
					<td>
						<table width="120" cellspacing="0" cellpadding="4" border="0" class=head>
							<tr>
								<td align="left"><b>&nbsp;Top 3 Spieler</b></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
							<tr>
								<td align=center valign=center>

								<?php
								$top3 = assocs("select * from status where alive > 0 and isnoob = 0 order by nw_rankings desc limit 3");
								foreach ($top3 as $ky) {
									if (strlen($ky[syndicate]) > 17) {
										$ky[syndicate] = substr($ky[syndicate],0,13)."...";
									}
									echo "<b class=\"gelb11\">$ky[syndicate] (#$ky[rid])</b><br>
											".pointit($ky["nw_rankings"])." Nw<br><br>";
								}
								?>


								</td>
							</tr>
							<tr><td height=5></td></tr>
						</table>
					</td>
				</tr>
            </table>
<!-- wars ende -->
<br>
<? } ?>

<?
if ($new == 1 && count($grab) > 0 && 1 == 2) { 
//$grab = assocs("select * from attacklogs where time <=".($time-60*60*24)." and type in (1, 3) order by landgain desc limit 10");	
	?>

<!-- Players -->
    		<table width="120" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="120" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Best Grabs</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$ids = "(";
						foreach ($grab as $ky) {
						$ids .= "'$ky[aid]',";
						}
						$ids = chopp($ids); $ids.=")";
						$ii=0;
						if (strlen($ids) > 3) {
							$players = assocs("select * from status where alive > 0 and id in $ids","id");
							foreach ($grab as $ky) {
								if ($players[$ky[aid]][syndicate] && $ii < 3) {
									$ii++;
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									echo "<b class=\"gelb11\">".$players[$ky[aid]][syndicate]." (#".$players[$ky[aid]][rid].")</b><br>
											".pointit($ky[landgain])." Hektar<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>
<!-- wars ende -->
<br>
<? } ?>
<table width="120" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
	<tr>
		<td>
			<table width="120" cellspacing="0" cellpadding="3" border="0" class=head>
			<tr>
				<td align="center">
				Partner
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="120" cellspacing="0" cellpadding="3" border="0" class=body>
			<tr>
			<? if (!isKsyndicates()) {?>
				<td align="center">
					<br>
				</td>
			<? } else { ?>
				<td align="center">
					 <a href="http://BETREIBER.de/"><img src="http://images.BETREIBER.de/grafiken_extern/emogames/logo_tiny.gif" border="0" style="margin:10px;"></a>
				</td>
			<? } ?>
			</tr>
			</table>
		</td>
	</tr>
</table>


