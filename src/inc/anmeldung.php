 <?
//echo "	<script language=\"javascript\" type=\"text/javascript\" src=\"jquery.js\"></script>";
######## KSYN STUFF

if (isKsyndicates()) {
	$reg_mode_hidden = "<input type=\"hidden\" name=\"mode\" value=\"KOINS\">";
}

####################

require_once (LIB."js.php");
js::loadOver();

if ($referrer->get_referrer_by_src()) {$refsource = $referrer->get_referrer_by_src();}
else {$refsource = "syndicates";}


if ($userdata[konzernid]) {
	$alive=single("select alive from status where id=".$userdata[konzernid]);
}
if ($userdata[konzernid] > 0 && $alive != 0) {
			echo("<p align=left>Sie haben sich bereits angemeldet.</p>");
}
else {
	if (!$userdata[id] && strlen($k_userkey) < 5) {
		echo "
			<table class=\"normal\"><tr><td><br>
			Du bist momentan nicht eingeloggt.
		<br>
		<br>
			<b>Syndicates ist ein Produkt von Emogames.<br>
			Um $game[name] spielen zu können, benötigst du einen Emogames-Account.</b>
		<br><br>
			<b>Wenn du noch keinen Emogames-Account besitzt, dann klicke bitte <a href=$game[emogames_portal_anmeldung]&ref_src=$refsource class=gelblink>hier</a>, um dich bei Emogames anzumelden.</b>
		<br>
		</td>
		<td>
			&nbsp;<a href=\"$game[emogames_portal_anmeldung]&ref_src=$refsource\"><img border=\"0\" src=\"http://images.emogames.de/grafiken_extern/emogames/logo_medium_syn.gif\"></a>	<br>
		</td></tr></table>
		<br>
			Bitte logge dich links im Seitenmenü mit deinen Emogames-Accountdaten ein, falls du bereits einen Account bei Emogames hast.

		<br>
			Du kannst Syndicates nur mit einem Emogames-Account spielen!<br>Wir bitten um dein Verständnis.
		<br>

		<br>
			PS: Schon gewusst?
		<br>
			Mit einem Emogames-Account kannst du nicht nur Syndicates spielen, sondern auch alle anderen Spiele und Services von Emogames benutzen ohne dich erneut anmelden zu müssen!
		";


	}
	else {
		// Nur zwei Konzernerstellungen pro Tag möglich.

		$daytime = get_day_time($time);
		if ($userdata) {
			$konzernids = singles("select syndicate from options_konzerndelete where time >= ".$globals['roundstarttime']." and user_id = ".$userdata['id']);
			if ($konzernids) {
				if (single("select count(*) from status where syndicate in ('".join("','", $konzernids)."') and createtime >= $daytime") >= 2) {
					header("Location: index.php?action=error&code=83647");
					exit();
				}
			}
		}
?>

<font class="normal">
<b><? echo "$anmeldungdot"; ?> Anmeldung zur aktuellen Runde</b><br><br>
<?php

function fehler ($input)
{
    echo "<div style=\"width:600px;\"  align=left class=\"nrot11\" ><li>$input</div><br>";
}

if ($new != 2) {
	if ($rulernameerror == 1) {fehler ("1Bitte geben Sie einen gültigen Konzernchef-Namen von mindestens 3 und höchstens 20 Zeichen an. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ Leerzeichen");}
	if ($syndicateerror==1) {fehler("Bitte geben sie einen gültigen Namen für ihren Konzern an. Mindestens 3 Zeichen und höchstens 20 Zeichen. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ & . , Leerzeichen");}
	if ($raceerror==1) {fehler("Bitte einen gültigen Konzerntyp auswählen.");}
	if ($passworderror==1) {fehler("Bitte ein gültiges Passwort mit mindestens 4 und höchstens 20 Zeichen angeben. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ . , Leerzeichen");}
	if ($usernameerror==1) {fehler("Bitte einen gültigen Benutzernamen mit mindestens 3 und höchstens 20 Zeichen angeben. An Sonderzeichen sind nur folgende Zeichen zulässig: - _ & . , Leerzeichen");}
	if ($emailerror==1) {fehler("Bitte geben Sie eine gültige E-Mail-Adresse an.");}
	if ($emailerror==2) {fehler("Diese E-Mail-Adresse wird leider schon verwendet.");}
	if ($usernameerror==2) {fehler("Dieser Benutzername wird leider schon verwendet.");}
	if ($agberror==1) {fehler("Sie müssen die AGB akzeptieren, wenn Sie an diesem Spiel teilnehmen möchten!");}
	if ($syndicateerror==2) {fehler("Es existiert bereits ein Konzern mit dem von Ihnen gewählten Konzernnamen.");}
	if ($iperror==1) {fehler("Von dieser IP-Adresse wurde vor kurzem erst ein Account erstellt. Probieren Sie es später noch einmal.");}
	if ($ammounterror==1) {fehler("Momentan können leider keine neuen Accounts mehr erstellt werden. Probieren Sie es später noch einmal.");}
	if ($vornameerror==1) {fehler("Bitte geben Sie einen gültigen Vornamen an.");}
	if ($nachnameerror==1) {fehler("Bitte geben Sie einen gültigen Nachnamen an.");}
	if ($password2error==1) {fehler("Die Passwörter stimmen nicht überein.");}
	if ($syndikatserror == 1) {fehler("Das Syndikat, welchem Sie beitreten möchten existiert nicht.");}
	if ($syndikatserror == 2) {fehler("Das von Ihnen eingegebene Passwort stimmt nicht mit dem Syndikatspasswort überein.");}
	if ($syndikatserror == 3) {fehler("Das Syndikat, welchem Sie beitreten möchten ist bereits voll.");}
	if ($syndikatserror == 4) {fehler("Sie können keinem Anfängersyndikat beitreten!");}
	if ($timeerror == 1) {fehler("Zwischen der Eingabe Ihrer Benutzer/Konzerndaten und der Eingabe der Syndikatsdaten ist leider zuviel Zeit verstrichen. Aus Sicherheitsgründen kann die Anmeldung mit den zuvor eingegebenen Daten daher leider nicht durchgeführt werden. <br>Bitte geben Sie die Daten erneut an. <br>Wir bitten die Unannehmlichkeiten zu entschuldigen.");}
	if ($codeerror == 1) {fehler("Sie haben den angezeigten Code falsch bzw. nicht eingegeben.");}
	if ($openerror == 1) { fehler("Dieses Syndikat ist geschlossen. Diesem Syndikat kann daher nicht mehr beigetreten werden."); }
	if ($usernamespaceerror == 1) {fehler("Fehler im Benutzernamen. Führende, abschließende oder in Gruppen von mehr als einem Zeichen auftretende Leerzeichen sind nicht gestattet, um Probleme beim Login zu vermeiden."); }
	if ($sessionerror == 1) { fehler("Ihre Session ist abgelaufen und somit konnte Ihnen kein Emogames-Account zugeordnet werden."); }
	if (!$validationkey) { ?>

<form action="create.php" method="post">
<?=$reg_mode_hidden?>
<table class=normal width=500 align=left cellpadding=0 cellspacing=0 border=0>
	<tr>
		<td>
			<table width="500" cellspacing="1" cellpadding="0" border="0" class=rand align=left>
				<tr>
					<td>
						<table width="500" cellspacing="0" cellpadding="4" border="0" class=head>
							<tr>
								<td width="500" align="center"><b>Syndicates Anmeldung</b></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="500" cellspacing="0" cellpadding="0" border="0" class=body>
							<tr>
								<td width="200" align="left">&nbsp;<b>Konzerntyp:</b></td>
								<td width="300" align="center">
									<table cellspacing=0 cellpadding=2 border=0 width="300" class="ver11w">
										<tr id="fraktionenreihe">
											<?
												// herausfinden ob wir auf dem testserver oder live-server sind
												$db_classic = $servers[classic][db_name];
												$db_basic = $servers[basic][db_name];
												if (getServertype() == "basic") {
												  $fraks_basic = assocs("select * from races","race");
												  $fraks_classic = assocs("select * from `$db_classic`.races","race");
												  $fraks = $fraks_basic;
												  $frakdescs_basic= assocs("select * from fraktionen_beschreibung","race");
												  $frakdescs_classic= assocs("select * from `$db_classic`.fraktionen_beschreibung","race");
												  $frakdescs = $frakdescs_basic;
												} else if (getServertype() == "classic") {
												  $fraks_classic = assocs("select * from races","race");
												  $fraks_basic = assocs("select * from `$db_basic`.races","race");
												  $fraks = $fraks_classic;
												  $frakdescs_classic= assocs("select * from fraktionen_beschreibung","race");
												  $frakdescs = $frakdescs_classic;
												  $frakdescs_basic= assocs("select * from `$db_basic`.fraktionen_beschreibung","race");
												  /*if ($userdata['may_play_on_classic'] == 0) {
												    $fraks = $fraks_basic;
												    $frakdescs = $frakdescs_basic;
												  }*/
												}
												
												// Bilderpfad für ksyn anpassen
												if (isKsyndicates()) {
													foreach ($frakdescs as $k => $temp) {
														$frakdescs[$k][description_html] = preg_replace("/images/","images/krawall_images/",$temp[description_html]);
														
													}
												}
												
												foreach ($frakdescs as $k => $temp) {
													if ($fraks[$k][active] == 1) {
													?><td width=100 align=center><br><img <?=js::showover("<table width=400 cellspacing=1 border=0 cellpadding=0 class=\"normal\">".$frakdescs[$k][description_html]."</table>")?> src="<?=WWWDATA.$local_imagepath?>/<?=strtolower($fraks[$k][tag])?>-logo-mittel.gif" border=0><br><br><?=$fraks[$k][tag]?><br><input type=radio name=race value=<?=$k?> <? if ($race == $k) {echo ("checked");} ?>><br><br></td>														
													<?
													} // If active
												}
											      ?> 
											      <script language="JavaScript">
											      		var fraks_basic = '';
											      </script>
											      <?
												foreach ($frakdescs_basic as $k => $temp) {
													if ($fraks_basic[$k][active] == 1) {
													?>
													<script language="JavaScript">
														fraks_basic += "<td width=100 align=center><br><img <?=str_replace("|||||", "'", addslashes(str_replace("'", "|||||", js::showover("<table width=400 cellspacing=1 border=0 cellpadding=0 class=\"normal\">".$frakdescs[$k][description_html]."</table>"))))?> src=\"<?=WWWDATA.$local_imagepath?>/<?=strtolower($fraks[$k][tag])?>-logo-mittel.gif\" border=0><br><br><?=$fraks[$k][tag]?><br><input type=radio name=race value=<?=$k?> <? if ($race == $k) {echo ("checked");} ?>><br><br></td>";
													</script>
													<?
													} // If active
												}
											      ?> 
											    <script language="JavaScript">
											    		var fraks_classic = '';
											    </script>
											      <?
												foreach ($frakdescs_classic as $k => $temp) {
													if ($fraks_classic[$k][active] == 1) {
													?>
													<script language="JavaScript">
															fraks_classic += "<td width=100 align=center><br><img <?=str_replace("|||||", "'", addslashes(str_replace("'", "|||||", js::showover("<table width=400 cellspacing=1 border=0 cellpadding=0 class=\"normal\">".$frakdescs[$k][description_html]."</table>"))))?> src=\"<?=WWWDATA.$local_imagepath?>/<?=strtolower($fraks[$k][tag])?>-logo-mittel.gif\" border=0><br><br><?=$fraks[$k][tag]?><br><input type=radio name=race value=<?=$k?> <? if ($race == $k) {echo ("checked");} ?>><br><br></td>";
													</script>
													<?
													} // If active
												}
											?> 
											
			
										</tr>
										<tr>
											

										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 border=0>
							<tr>
								<td width="500" align="center">
								Mehr Infos zu den Konzerntypen finden Sie in der &nbsp;<a href="?action=docu&kat=1&aid=1" target="_blank"><? echo "$anmeldungdot"; ?><font face="Verdana" color="#ffd631" style="font-size:11px"> Hilfe</font></a>			</td>
							</tr>
						</table>
					</td>
				</tr>
<? /*				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 border=0>
							<tr>
								<td width="200" align="left">&nbsp;<b>Name Ihres KonzernChefs:</b></td>
								<td width="300" align="center"><input type="text" name="rulername" <? if ($rulername) {echo ("value=\"$rulername\"");} ?> ></td>
							</tr>
						</table>
					</td>
				</tr> */ ?>
				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 border=0>
							<tr>
								<td width="200" align="left">&nbsp;<b>Name Ihres Konzerns:</b></td>
								<td width="300" align="center"><input type="text" name="syndicate" <? if ($syndicate) {echo ("value=\"$syndicate\"");} ?> ></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<? 
  //$userdata['may_play_on_classic'] = 1; 
  //$userdata['startround'] = 44; 


?>
<? if (SHOW_MULTISERVER && !isKsyndicates() && $userdata['may_play_on_classic']) { ?>

<?
// Eine Empfehlung suchen ob Spieler lieber auf dem Classic oder Basic spielen soll
// vor allem im Hinblick darauf, dass zu Runde 44 einmalig alle Spieler das "may_play_on_classic"
// flag erhalten haben, obwohl sie vielleicht erst eine Runde auf dem Basic gespielt hatte.
// Alle die in Runde 41 zuerst gespielt hatten haben mind. Runde 42 und 43 schon auf dem Basic verbracht.
// Diese Spieler bekommen daher standardmäßig den Classic-Server empfohlen.
// Zukünftige Spieler mit dem Classic-Flag bekommen den Classic-Server empfohlen, weil sie ihn sich verdient haben
$servertype = "basic";
if ($userdata['startround'] <= 43 || $userdata['startround'] >= 46) {
  $servertype = "classic";
}


// Availability checken

$servers = assocs("select * from servers", "servertype");
foreach ($servers as $temp) {
	mysql_select_db($temp[db_name]);
	$troundstatus[$temp[servertype]] = single("select roundstatus from globals order by round desc limit 1");
}
connectdb();



?>
<script language="JavaScript">

	$("document").ready(function() {

	  $('#fraktionenreihe').html(fraks_<?=$servertype?>);
	  var mayPlayOnClassic = 1;
	  var forbidMentorenProgramm = <? echo($servertype == "classic" ? 1 : 0); ?>;
	  var standardServer = '<?=$servertype?>';
	});
	  
	  function showMentorBox() {
	    if (typeof forbidMentorenProgramm == "undefined" || !forbidMentorenProgramm) {
	    	$('#mentorenprogramm').css("display","block");
	    }
	    else {
	    	$('#mentorenprogramm').css("display","none");
	    }
	  }
	  
	  
</script>
	<tr>
		<td><br></td>
	</tr>
	<tr>
		<td>
			<table width="500" cellspacing="1" cellpadding="0" border="0" class=rand>

				<tr>
					<td>
						<table width="500" cellspacing="0" cellpadding="4" border="0" class=subhead>
							<tr>
								<td width="500" align="center"><strong>Bitte wählen Sie einen Server aus:</strong>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 width=100%>
							<? if ($troundstatus["basic"] == 2) { $dis = "disabled"; $addtext="<br><b>Anmeldephase noch nicht gestaret!</b>"; $servertype="classic";}?>
							<? 
							if (false && $userdata[startround] +2 < $globals[round] && !$userdata[is_mentor]) { 
								$dis = "disabled"; 
								$addtext="<br><b>Der Basic Server ist nur für neue Emogames-Accounts zugänglich!</b>"; 
								$servertype="classic";
							}
							?>
							<tr>
								<td width=50></td>
								<td align=left width=400>
										<input id="basicinput" <?=$dis?>  type=radio name=servertype value=basic <? if (!$servertype || $servertype =="basic") echo "checked";?>>
										<b>Syndicates Basic</b> (für neue Spieler empfohlen) 
										<img <?=js::showover("<div style=\'color:white\'><b>Basic Server</b><br>Der Syndicates Basic Server ist vor allem für neue Spieler gedacht.<br> Einige der komplexeren Spielelemente wurden deshalb deaktiviert.$addtext</div>")?>src="<?=WWWDATA."/images/_help.gif"?>" > 
										<script language="javascript">
										$("document").ready(function() {
											
											if ($("#basicinput").attr("checked")== true) {
													$('#synjoin').css("display","none");
													$('#fraktionenreihe').html(fraks_basic);
													showMentorBox();
											}
											
											
												$('#basicinput').click(function() {
													$('#synjoin').css("display","none");
													$('#fraktionenreihe').html(fraks_basic);
													showMentorBox();
												});
										});
										</script>
								</td>
								<td width=50></td>
							</tr>
							<?
								$dis = ""; $addtext = "";
								 if ($troundstatus["classic"] == 2) { $dis = "disabled"; $addtext="<br><br><b>Anmeldephase noch nicht gestaret!</b>";}
							?>
							<tr>
								<td></td>
								<td align=left>
										<input id="classicinput" <?=$dis?> id="classicselected" type=radio  name=servertype value=classic  <? if ($servertype =="classic") echo "checked";?>> 
										<b>Syndicates Classic</b> (für erfahrene Spieler) &nbsp; 
										<img <?=js::showover("<div style=\'color:white\'><b>Classic Server</b><br>Der Syndicates Classic Server erlaubt Teambildung<br> vor Rundenstart und beinhaltet alle Spielelemente.<br>Hier spielen hauptsächlich erfahrene Spieler,<br>daher ist dieser Server <b>nicht für Anfänger geeignet!</b>$addtext</div>.")?>src="<?=WWWDATA."/images/_help.gif"?>" >
										<script language="javascript">
										$("document").ready(function() {
											if ($("#classicinput").attr("checked") == true) {

													$('#synjoin').css("display","block");
													$('#fraktionenreihe').html(fraks_classic);
													$('#mentorenprogramm').css("display","none");												
											}
											
												$('#classicinput').click(function() {
													$('#synjoin').css("display","block");
													$('#fraktionenreihe').html(fraks_classic);
													$('#mentorenprogramm').css("display","none");												
												});
										});
										</script>										
										</td>
								<td></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<? } else if (!$userdata['may_play_on_classic'] && !isKsyndicates()) {?>
<script language="JavaScript">
  var noSynChooseBox = 1;
  var standardServer = 'basic';
</script>
<input type=hidden name=servertype value=basic>



<? }?>



<? if ($new == 1 && !isKsyndicates())	{ ?>
	<tr>
		<td><br></td>
	</tr>
	<div id="synjoin">
	</div>
				<!--
	<tr>
		<td>
			<div id="synjoin" >
			<table width="500" cellspacing="1" cellpadding="0" border="0" class=rand>

				<tr>
					<td>
						<table width="500" cellspacing="0" cellpadding="4" border="0" class=subhead>
							<tr>
								<td width="500" align="center"><strong>Bitte wählen Sie eine der folgenden Optionen aus:
								<?
									if(SHOW_MULTISERVER) {
										echo " (Nur Classic-Server)";
									} 
								?></strong>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 width=100%>
							<tr>
								<td width=100></td>
								<td align=left width=300><input type=radio name=syndikatsmodus value=random <? if ($smodus == "random" || !$smodus) {echo ("checked");} ?>> Zufällig in ein Syndikat platzieren</td>
								<td width=100></td>
							</tr>
							<tr>
								<td></td>
								<td align=left><input  type=radio name=syndikatsmodus value=join <? if ($smodus == "join") {echo ("checked");} ?>> Einem bestimmten Syndikat beitreten</td>
								<td></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</div>
			<script language="JavaScript">
				if ((typeof noSynChooseBox != "undefined" && noSynChooseBox == 1) || !$('#classicselected').attr("checked")) {
					$('#synjoin').css("display","none");
				}
			</script>
		</td>
	</tr>
				-->
<? } // new == 1
else { print "<input type=hidden name=syndikatsmodus value=random>"; }
?>
<? if ($new != 2 && !isKsyndicates() && FALSE)	{ // Mentorenprogramm-Auswahl?>
	<tr>
		<td>
			<div id="mentorenprogramm" >
			<table width="500" cellspacing="1" cellpadding="0" border="0" class=rand>
				<tr>
					<td>
						<table class=body cellpadding=4 cellspacing=0 width=100%>
							<tr>
								<td width="500" align="center" colspan=3><strong>Stichwort Mentorenprogramm: Möchtest du Hilfe von einem erfahrenen Spieler bekommen? <img <?=js::showover("<div style=\'color:white\'>Wenn du dich für das Mentorenprogramm entscheidest <br>stehen deine Chancen einen erfolgreichen Konzern aufzubauen <br>wesentlich höher. Im Verlauf des ersten Tages wird sich dein <br>Mentor von selbst bei dir melden und dir beim Erlernen des Spiels helfen.</div>")?>src="<?=WWWDATA."/images/_help.gif"?>" >
								<?
									if (SHOW_MULTISERVER && $userdata['may_play_on_classic']) {
										echo " (Nur Basic-Server)";
									} 
								?></strong>
							</tr>
							<tr>
								<td width=100></td>
								<td align=left width=300><input type=radio name=mentorenprogramm value=no> Nein danke, ich kenn mich aus!</td>
								<td width=100></td>
							</tr>
							<tr>
								<td></td>
								<td align=left><input  type=radio name=mentorenprogramm value=yes> Ja bitte, das macht es einfacher!</td>
								<td></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</div>
			<script language="JavaScript">
				if ((typeof mayPlayOnClassic != "undefined" && mayPlayOnClassic == 1) && document.getElementById('classicselected').checked) {
					document.getElementById('mentorenprogramm').style.display='none';
				}
			</script>
		</td>
	</tr>
<? } // new == 1 
else { print "<input type=hidden name=mentorenprogramm value=no>"; } ?>


<?

?>

	<tr>
		<td><br></td>
	</tr>
	<tr>
		<td>
			<table width="500" cellspacing="1" cellpadding="0" border="0" class=rand>
				<tr>
					<td>
						<table width="500" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="500" align="center"><br>
									<input type="checkbox" name="agb"> Ich akzeptiere die aktuellen <a href="http://BETREIBER.de/index.php?action=agbs" class=gelblink11 target=_blank>AGB</a> von BETREIBER<?=$ksynadd?>.<br>
								<br>
								<input type="submit" name="submit" value="anmelden"><br>
								<br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<input type=hidden name=wid value=<? echo $wid; ?>>
</form>
</font>
<?    } // if !syndikatsmodus
elseif ($validationkey) { ?>
<form action="create.php" method="post">
<input type=hidden name=agb value=on>
	<table width="550" cellspacing="1" cellpadding="0" border="0" class=rand align=left>
		<tr>
			<td>
				<table width="550" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td width=100% colspan=2>Daten vervollständigen</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="550" cellspacing="0" cellpadding="4" border="0" class=body>
					<tr>
						<td width=100% colspan=2>
							<input type=hidden name=servertype value="<? echo $servertype; ?>">
							<input type=hidden name=validationkey value="<? echo $validationkey; ?>">
							Sie möchten einem Syndikat beitreten. Bitte geben Sie Syndikatsnummer und Syndikatspasswort an.
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=body cellpadding=4 cellspacing=0 width=100%>
					<tr>
						<td width="100" align="left">&nbsp;<b>Syndikatsnummer:</b></td>
						<td width="400" align="left">
							<input type="text" name="syndikatsid" <? if($syndikatsid): echo "value=\"$syndikatsid\""; endif;?>>
						</td>
					</tr>
					<tr>
						<td align="left">&nbsp;<b>Syndikatspasswort:</b></td>
						<td align="left">
							<input type="password" name="syndikatspw">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=body cellpadding=4 cellspacing=0 width=100%>
					<tr><td align="center" width="100%" >ODER</td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=body cellpadding=4 cellspacing=0 width=100%>
					<tr>
						<td align="center" width="100%" >
							<table cellpadding=4 cellspacing=0 border=0 width=100% class=body>
								<tr>
									<td width="550" align="left">&nbsp;
										<input type=checkbox name=decisionchanged style="margin:0px;">
										<b>&nbsp;Ich möchte lieber doch zufällig platziert werden</b>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=body cellpadding=10 cellspacing=0 width=100%>
					<tr>
						<td align=center width=100% align="left"><input align="center" type=submit value="Anmeldung abschließen"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=foot cellpadding=10 cellspacing=0 width=100%>
					<tr>
						<td></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<input type=hidden name=wid value=<? echo $wid; ?>>
</form>


<? 		} // elsif validationkey ?>

<?
	} // if new == 2

	else {echo "<br><br><center>Die Runde ist beendet, momentan können keine neuen Accounts erstellt werden.</center>";}

	} // ENDE else ($userdata[konzernid] > 0) {
}
?>
