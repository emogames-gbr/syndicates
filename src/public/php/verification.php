<?
require_once("../../inc/ingame/game.php");

list ( $vcode_db, $email, $user_id ) = row("select vcode,email,id from users where konzernid='$id'");
$loggedin = 0;

if ($vcode_db) {
	if ($ia == "verify") {
		if ($vcode == $vcode_db) {
			select("update users set vcode='' where konzernid='$id'");
			$ausgabe .= "
		<table width=550 align=center>
			<tr>
				<td width=50></td>
				<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
				<br><br><br>
				<center><b>Sie haben Ihre E-Mail-Adresse verifiziert.</b>
					<br><br><br>Sie werden in 5 Sekunden automatisch auf die Statusseite weitergeleitet.
					<br><br>Sollte die Weiterleitung nicht funktionieren, dann klicken Sie bitte
					<a style=\"text-decoration:underline\" href=\"statusseite.php?init=2\" class=linkAufsiteBg>hier</a>.
				</center>
				<script language=JavaScript>
					setTimeout('getback()', 5000);
					function getback(){parent.location.href=\"statusseite.php?init=2\";}
				</script>
				</td>
			</tr>
		</table>";

		}
		else {
			$ausgabe .= "
		<table width=550 align=center>
			<tr>
				<td width=50></td>
				<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
				<br><br><br>
				<center><b>Fehler!
					<br><br><br>Der von Ihnen eingegebene Verifizierungsschlüssel stimmt nicht mit dem Ihnen zugeschickten überein.
					<br><br>Bitte überprüfen Sie Ihre Eingabe!</b>
					<br><br><br>Sie werden in 5 Sekunden automatisch wieder auf die Verifizierungsseite weitergeleitet.
					<br><br>Sollte die Weiterleitung nicht funktionieren, dann klicken Sie bitte
					<a style=\"text-decoration:underline\" href=\"verification.php\" class=linkAufsiteBg>hier</a>.
				</center>
				<script language=JavaScript>
					setTimeout('getback()', 5000);
					function getback(){parent.location.href=\"verification.php\";}
				</script>
				</td>
			</tr>
		</table>";
		}
	}
	elseif ($ia == "sendagain") {
		verification($user_id, "sendagain");
		$ausgabe .= "
		<table width=550 align=center>
			<tr>
				<td width=50></td>
				<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
					<br><br><br>
					<center><b>Der Verifizierungsschlüssel wurde soeben erneut verschickt.</b>
					<br><br><br>Sie werden in 5 Sekunden automatisch wieder auf die Verifizierungsseite weitergeleitet.<br><br>Sollte die Weiterleitung nicht funktionieren, dann klicken Sie bitte
					<a style=\"text-decoration:underline\" href=\"verification.php\" class=linkAufsiteBg>hier</a>.
					</center>
					<script language=JavaScript>
						setTimeout('getback()', 5000);
						function getback(){parent.location.href=\"verification.php\";}
					</script>
				</td>
			</tr>
		</table>";
	}
	elseif ($ia == "addresschange") {
		if ($mailadress != $email) {
			// if (preg_match("/[\w-_.]+\@([\w-]+\.)+\w{2,4}/",$mailadress)) { // blödsinn von nicolas
			if (checkmail($mailadress)) {
				$count = single("select count(*) from users where email='$mailadress'");
					if ($count == 0) {
						select("insert into options_mailchange (emailbefore,emailafter,time,user_id) values ('$email','$mailadress',$time,$user_id)");
						select("update users set email='$mailadress' where id='$user_id'");
						verification($user_id, "mailchange");
						$ausgabe .= "
							<table width=550 align=center>
								<tr>
									<td width=50></td>
									<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
										<br><br><br><center><b>Der Verifizierungsschlüssel wurde soeben an Ihre geänderte E-Mail-Adresse verschickt.</b>
									</td>
								</tr>
							</table>";
					}
					else {
						$ausgabe .= "
							<table width=550 align=center>
								<tr>
									<td width=50></td>
									<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
										<br><br><br><center><b>Fehler!
										<br><br>Die von Ihnen eingegebene E-Mail-Adresse wird bereits verwendet.
										<br><br>Es wurden keine Änderungen durchgeführt.</b>
									</td>
								</tr>
							</table>";
					}
			}
			else {
				$ausgabe .= "
					<table width=550 align=center>
						<tr>
							<td width=50></td>
							<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
								<br><br><br><center><b>Fehler!
								<br><br><br>Die von Ihnen eingegebene E-Mail-Adresse entspricht nicht dem Muster einer gültigen E-Mail-Adresse.
								<br><br>Es wurden keine Änderungen durchgeführt.</b>
							</td>
						</tr>
					</table>";
			}
		}
		else {
			$ausgabe .= "
				<table width=550 align=center>
					<tr>
						<td width=50></td>
						<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
							<br><br><br><center><b>Fehler!<br><br>Diese E-Mail-Adresse verwenden Sie bereits.
							<br><br>Es wurden keine Änderungen durchgeführt.</b>
						</td>
					</tr>
				</table>";
		}
		$ausgabe .= "
			<table width=550 align=center>
				<tr>
					<td width=50></td>
					<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
						<br><br><br>Sie werden in 5 Sekunden automatisch wieder auf die Verifizierungsseite weitergeleitet.<br><br>Sollte die Weiterleitung nicht funktionieren, dann klicken Sie bitte <a style=\"text-decoration:underline\" href=\"verification.php\" class=linkAufsiteBg>hier</a>.
						</center>
						<script language=JavaScript>
							setTimeout('getback()', 5000);
							function getback(){parent.location.href=\"verification.php\";}
						</script>
					</td>
				</tr>
			</table>";
	}
	else {
		$ausgabe .= "
		<table width=550 align=center>
			<tr>
				<td width=50></td>
				<td class=\"siteGround\" width=\"500\" colspan=3 valign=\"top\">
					<br><br><br><center>Bitte tragen Sie hier den Verifizierungsschlüssel ein, welchen wir Ihnen per Mail zugeschickt haben:</font><br><br>
					<form action=verification.php method=post>
						<input type=hidden name=ia value=verify>
						<input type=text size=19 name=vcode>&nbsp;&nbsp;&nbsp;&nbsp;
						<input type=submit value=verifizieren>
					</form>
					<br><br><br><br>
					<table>
						<tr>
							<td  class=siteGround width=500>
								Keinen Verifizierungsschlüssel erhalten? - Kein Problem!<br><br>Ihre momentan bei uns gespeicherte E-Mail-Adresse lautet \"<i>$email</i>\";
								<br><a style=\"text-decoration:underline\" href=\"verification.php?ia=sendagain\"  class=linkAufsiteBg>Hier</a> können Sie sich den Schlüssel <a style=\"text-decoration:underline\" style=\"text-decoration:underline\" href=\"verification.php?ia=sendagain\" class=linkAufsiteBg>erneut zusenden</a> lassen.
								<br><br><br>
								Alternativ können Sie auch, falls die momentan gespeicherte E-Mail-Adresse (s.o.) nicht (mehr) existiert bzw. momentan nicht abgerufen werden kann, eine andere E-Mail-Adresse eintragen:<br><br>
								<center>
									<form action=verification.php method=post>
										<input type=hidden name=ia value=addresschange>
										<input type=text size=19 name=mailadress>&nbsp;&nbsp;&nbsp;&nbsp;
										<input type=submit value=\"E-Mail-Adresse ändern\">
									</form>
								</center>
							</td>
						</tr>
					</table>
					</center>
				</td>
			</tr>
		</table>
";
	}
}
else {
	header ("Location: statusseite.php?init=2"); exit();
}

require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");

?>
