<?
############
define (DAYS_BOLD,2); //  Tage lang neue News dick anzeigen
############

?>
<!--
		<table width="120" cellspacing="0" cellpadding="0" border="0" bgcolor="Black">
        <tr>
            <td>
				<table width="120" cellspacing="1" cellpadding="0" border="0" class=rand>
				<tr>
					<td>
						<table width="120" cellspacing=0 cellpadding=2 border=0 class=head>
-->

								<script type="text/javascript">
									function imgSwitch(imgname, newsrc) {
										document.images[imgname].src=newsrc;
									}
								</script>

<table cellspacing=0 cellpadding=0 border=0 width=100% style="font-family:Arial">
	<tr>
		<td valign=top width=70%>
			<? if (strlen($error_ausgabe) > 0) { // Fehlerausgabe?>
			
			<table class="f" cellspacing=1 cellpadding=3 width=100%>
				<tr class="head">
					<td align="center" style="font-size:14px;font-family:Arial">
						<b>Achtung</b>
					</td>
				</tr>
			
				<tr class="body">
					<td style="font-size:13px;font-family:Arial;">
						<? echo $error_ausgabe; ?> 
					</td>
				</tr>
			</table>
			<br>
			
			<? } 	?>		
		
			<table class="rand" cellspacing=1 cellpadding=3 width=100%>
				<tr class="head">
					<td align="center" style="font-size:14px;font-family:Arial">
						<b>Willkommen bei Syndicates</b>
					</td>
				</tr>
				<tr class="body">
					<td style="font-size:14px;font-family:Arial">
					<?
						$days_played = round_days_played();
					?>
						<? if(isKsyndicates()) {?>
						<? } else { ?>
						<?
						//Syndicates ist ein <b>Onlinestrategiespiel mit faszinierender Spieltiefe!</b> Um zu spielen benötigst du lediglich deinen Webbrowser.
						?>

					<? if ($days_played > 49) { //Normaler Text ?>
							Syndicates ist ein <b>Onlinestrategiespiel mit faszinierender Spieltiefe!</b> Um zu spielen benötigst du lediglich deinen Webbrowser.
						<? } else if ($days_played > 0) { // Runde kürzlich gestartet ?>


					<table width="100%" style="font-family:Arial;width:99%;margin-left:5px;margin-right:15px;" class="body">
						<tr class="bodynv">
							<td >
								<a href="http://emogames.de/index.php?action=anmeldung&ref_src=<? echo $refsource; ?>&cf=syn" class="normal"><img border="0" src="images/round_started.jpg" valign="center" width="224" height="113"></a>
							</td>
							
							
							<td style="font-size:14px;font-family:Arial;width:100%;" class="bodynv">
							 <div style="padding-left:5px;width:95%;">
      						In der nahen Zukunft haben Großkonzerne die Macht an sich gerissen. 
      						In Syndicates übernimmst du die Kontrolle über einen dieser Konzerne. 
      						Du vertrittst die Interessen deines Syndikats, handelst mit Militäreinheiten am Weltmarkt oder
      						stiehlst unliebsamen Konkurrenten ihr Erz. Erforsche Hightech-Gebäude und erkläre deinen Nachbarn den Krieg!
      						 <!--<b><a  style="font-size:14px;" href="index.php?action=anmeldung">Jetzt anmelden</a></b>-->
      					</div>
							</td>
						</tr>
					</table>
					
	

						
					<? } else { // Runde noch nicht gestaret ?>
					<table width="100%" style="font-family:Arial" class="body">
						<tr class="bodynv">
							<td>
								<a href="http://emogames.de/index.php?action=anmeldung&ref_src=<? echo $refsource; ?>&cf=syn" class="normal"><img border="0" src="images/round_starts.jpg" valign="center" width="224" height="113"></a>
							</td>
							<td style="font-size:13px;font-family:Arial" class="bodynv">
							Die neue Syndicates-Runde beginnt in Kürze. 
							Zum Rundenstart fangen alle Spieler von vorne an. Die Karten werden neu gemischt und jeder hat die gleichen Chancen. Jetzt Konzern anmelden um von Anfang an dabei zu sein!<br><br>
							<a href="http://emogames.de/index.php?action=anmeldung&ref_src=<? echo $refsource; ?>&cf=syn" class="normal"><b>Steige jetzt ein und sei von Anfang an mit dabei!</b></a>
							</td>
						</tr>
					</table>
				<?} ?>
						
	
						<!-- Rundenende Anzeige -->
						<!--
						<table width=100% style="font-family:Arial">
						   <tr class="body">
						   	<td width=20%>
							<img src="images/runde_39_daten.gif" valign="center">
							</td>
							<td width=80% style="font-size:13px;font-family:Arial">
							Liebe Syndicatlerinnen und Syndicatler!<br>
							Die aktuelle Runde neigt sich wieder mal dem Ende zu und die nächste steht schon vor der Tür. Anmelden ab Donnerstag, 01.01.09 und losgehen wird es dann am  Sonntag drauf um 20:00. Wir sehen uns!
							</td>
						   </tr>
						   
						</table>
						-->
						

						

						
						<? } ?>
					</td>
				</tr>
			</table>
			<?
			
			 if (!isKsyndicates()) {
					?>
							<? if (strlen($error_ausgabe) <= 0) { // Bei Fehlerausgabe weglassen?>
							<table style="margin-top:10px;" class="rand" cellspacing=1 cellpadding=3 width=100%  height=50>
								<tr class="body">
									<td valign=middle style="font-size:20px;font-family:Arial;padding-bottom:3px">
									
									 <div style="float:left;margin-right:20px;margin-top:18px;margin-bottom:10px;margin-left:10px;">
                      <A href="http://emogames.de/index.php?action=anmeldung&ref_src=&cf=syn">
                        <img src="<?=WWWDATA.$local_imagepath?>anmelden_btn_off_small.gif" width="260" 
  						            onMouseOver="imgSwitch('anmeldebtn', '<?=WWWDATA.$local_imagepath?>anmelden_btn_on_small.gif');" 
  						             onMouseOut="imgSwitch('anmeldebtn', '<?=WWWDATA.$local_imagepath?>/anmelden_btn_off_small.gif');" 
  						             name="anmeldebtn" height="39" border="0" alt="">
                     </A>
                   </div>
                   
									 <div style="margin-left:5px;font-size:12px;">
									     <img src="<?=WWWDATA.$local_imagepath?>/krawall_images//pfeil.gif" style="margin-right:10px;"> 
									       Werde Teil eines Syndikats<br>
									     <img src="<?=WWWDATA.$local_imagepath?>/krawall_images//pfeil.gif"style="margin-right:10px;"> 
									       Entwickle Deine Strategie<br>
									     <img src="<?=WWWDATA.$local_imagepath?>/krawall_images//pfeil.gif" style="margin-right:10px;"> 
									       Wirtschaftsboss, Forscher oder Militarist?<br>
									     <img src="<?=WWWDATA.$local_imagepath?>/krawall_images//pfeil.gif" style="margin-right:10px;"> 
									       Du entscheidest!<br />
									     <img src="<?=WWWDATA.$local_imagepath?>/krawall_images//pfeil.gif" style="margin-right:10px;"> 
									       <strong>Kostenlos und ohne Download sofort spielen</strong>
									 </div>
                   
									</td>
								</tr>
							</table>
							<? } ?>
			   <?
			 }
			
			?>
			
			<? if (strlen($error_ausgabe) <= 0 && !isKsyndicates()) { // Bei Fehlerausgabe weglassen?>
			<br>
			<table class="rand" cellspacing=1 cellpadding=3 width=100%>
				<tr class="head">
					<td align="center" style="font-size:14px;font-family:Arial">
						<b>Geschichten von der Front</b>
					</td>
				</tr>
				<tr class="body">
					<td style="font-size:13px;font-family:Arial">
					
					 <div id="historydiv">
					 
					   <!-- UIC -->
					   <div style="width:100%;">
					   
					      <div style="color:#EDEDED;text-align:center;width:140px;float:left;padding:5px;border:0px solid #f0c542;border-style:outset;margin-top:8px;">
					      <b>United Industries<br>Corporation (UIC)</b> 
					      <br>
  					      <div style="width:100%;text-align:center;margin-top: 0 auto; margin-bottom: 0 auto;">
                      <a target="_blank" href="<?=WIKI?>Fraktionen" style=""> 
                        <img border=none src="<?=WWWPUB.$local_imagepath?>/uic_logo_neu_small.png" style="margin-top:5px;">
                      </a>
                  </div>
                </div>
                
                <div style="color:#ededed;font-family:Georgia;font-style:italic;font-size:14px;padding:5px;text-align:justify;margin-right:5px;">
                    <center style="color:#1060a0;margin-bottom:5px;"><b>Der Wirtschaftsboss</b></center>
                    "Als ich den maroden Laden vor 15 Jahren von meinem Vater übernommen habe, standen wir kurz davor
                    von Cushinbery Cybergenetics gefressen zu werden. Ich habe diese Anfänger in 5 Jahren komplett aus dem Markt 
                    gedrängt und produziere jetzt 55% aller weltweit eingesetzt Firestorm Kampfroboter."<br>
                </div>
                
					   </div>
					   <hr style="width:90%;height:0px;background-color: transparent;border: 0px; border-top: 1px dashed #38668C;margin-top:10px;margin-bottom:8px;">
					   <!-- SL -->
					   <div style="width:100%;">
					   
					      <div style="color:#EDEDED;text-align:center;width:140px;float:right;padding:5px;border:0px solid #f0c542;border-style:outset;margin-top:8px;margin-bottom:10px;">
					      <b>Shadow Labs<br>Corporation (SL)</b> 
					      <br>
  					      <div style="width:100%;text-align:center;">
                      <a target="_blank" href="<?=WIKI?>Fraktionen" style=""> 
                        <img border=none src="<?=WWWPUB.$local_imagepath?>/sl_logo_neu_small.png" style="margin-top:5px;">
                      </a>
                  </div>
                </div>
                 
                <div style="color:#ededed;font-family:Georgia;font-style:italic;font-size:14px;padding:5px;text-align:justify;margin-left:5px;">
                    <center style="color:#157f00;margin-bottom:5px;"><b>Der Forscher</b></center>
                    "Gentechnologie ist illegal haben sie gesagt! Tarnkappenbomber sind umoralisch haben sie gesagt! 
                    Und wo sind sie geblieben? Der Fortschritt lässt sich nicht aufhalten. Stealth-Tec ist den meisten 
                    da draußen um Lichtjahre in der Forschung voraus. Wir produzieren effizienter, kämpfen mit fortschrittlichen
                    Waffen und wachsen schneller."<br>
                </div>
                
					   </div>
					   <hr style="width:90%;height:0px;background-color: transparent;border: 0px; border-top: 1px dashed #38668C;margin-top:10px;margin-bottom:8px;">
					   <!-- BF -->
					   <div style="width:100%;">
					   
					      <div style="color:#EDEDED;text-align:center;width:140px;float:left;padding:5px;border:0px solid #f0c542;border-style:outset;margin-top:14px;">
					      <b>Brute Force<br>(BF)</b> 
					      <br>
  					      <div style="width:100%;height:100%;text-align:cetnter;margin-top: 0 auto; margin-bottom: 25px;margin-top:15px;">
                      <a target="_blank" href="<?=WIKI?>Fraktionen" style=""> 
                        <img border=none src="<?=WWWPUB.$local_imagepath?>/bf_logo_neu_small.png" style="margin-top:5px;">
                      </a>
                  </div>
                </div>
                
                <div style="color:#ededed;font-family:Georgia;font-style:italic;font-size:14px;padding:5px;text-align:justify;margin-right:5px;">
                    <center style="color:#7f0000;margin-bottom:5px;"><b>Der Militarist</b></center>
                    "Wenn du deinem Gegner Auge in Auge gegenüber stehst, interessiert sich keiner dafür, ob dein Arsch vergoldet ist.
                    Unsere Wartanks sind vielleicht nicht schön, aber eine Plasmagranate in deinem Hintern ist noch viel hässlicher.
                    Deshalb haben sich allein in den letzten 14 Tagen drei Konkurrenten dazu entschlossen, einen Teil ihrer Konzerne an
                    Mars Mechanics abzutreten. Aber dass man uns deshalb als überregionale Bedrohung in den Medien darstellen muss...
                    "<br>
                </div>
                
					   </div>
					   
					   
					 
					 </div>
					
					</td>
				</tr>
			</table>
			
			<? } ?>

			<br>
						<?

						if ($referrer->get_referrer_by_src()) {$refsource = $referrer->get_referrer_by_src();}
						else {$refsource = "syndicates";}

						if(!isKsyndicates()) { ?>
							
								
						<? } else { ?>
								<!-- AB HIER, die tabelle drumrum -->
														
														<BR><TABLE class=rand cellSpacing=1 cellPadding=0
								                        width="100%" border=1>
								                          <TBODY>
								                          <TR class=body>
								                            <TD
								                            style="FONT-SIZE: 12px; FONT-FAMILY: Arial"
								                            valign="top">
															<table cellspacing="0" cellpadding="0" border="0">
															<tr><td valign="top" rowspan="2" style="FONT-SIZE: 11px; FONT-FAMILY: Arial;color:#ffffff;"><A class=normal
								                             
								                              href="index.php?action=anmeldung"><img src="<?=WWWDATA.$local_imagepath?>anmelden_btn_off.gif" width="289" onMouseOver="imgSwitch('anmeldebtn', '<?=WWWDATA.$local_imagepath?>anmelden_btn_on.gif');" onMouseOut="imgSwitch('anmeldebtn', '<?=WWWDATA.$local_imagepath?>/anmelden_btn_off.gif');" name="anmeldebtn" height="59" border="0" alt=""></A><p style="text-align:center;margin:0;padding:0 5px 0 0;">
								Hierfür wird ein <a class="normal" href="http://www.koins.de" style="FONT-SIZE:12px;">KOINS-Account</a> benötigt.</p></td>
								
															<td valign="top" style="padding:16px 6px 6px 6px;"><img src="<?=WWWDATA.$local_imagepath?>/pfeil.gif" width="6" height="10" alt=""></td>
															<td style="FONT-SIZE: 12px; FONT-FAMILY: Arial;color:#ffffff;padding:13px 3px 3px 3px;">
								40 <img src="<?=WWWDATA.$local_imagepath?>/koinsMiniLogo12x12_blue.gif" width="12" height="12" style="vertical-align:middle" alt=""> für Deine aktive Teilnahme*<br>
								<span style="color:#bac8db;font-size:11px;">*2 Wochen mitspielen & min. 750 ha Land</span>
								</td></tr>
								<tr>						<td valign="top" style="padding:6px;"><img src="<?=WWWDATA.$local_imagepath?>/pfeil.gif" width="6" height="10" alt=""></td>
															<td style="FONT-SIZE: 12px; FONT-FAMILY: Arial;color:#ffffff;padding:3px;">bis zu 120 <img src="<?=WWWDATA.$local_imagepath?>/koinsMiniLogo12x12_blue.gif" width="12" height="12" alt="" style="vertical-align:middle"> für die Top 30 Spieler und Rückerstattung der mit KOINS bezahlten Features.
								
								</td></tr></table><br>
														  </TD></TR></TBODY></TABLE><BR>
														  
								<!-- BIS HIER-->
                        <? } ?>
                        
      <? if (isKsyndicates()) { ?>
			<br>
			<table class="rand" cellspacing=1 cellpadding=3 width=100%>
				<tr class="head">
					<td align="center" style="font-size:14px;font-family:Arial">
						<b>Spielfeatures</b>
					</td>
				</tr>
				<tr class="body">
					<td >
						<table class="normal" style="font-size:13px;font-family:Arial;padding-left:10px" cellpadding=3 widht=100%>
							<tr>
								<td><li>Stark teamorientiert</td>
							</tr>
							<tr>
								<td><li>Fünf komplett verschiedene Fraktionen</td>
							</tr>
							<tr>
								<td width=100%>
									<table class="normal" style="font-size:13px;font-family:Arial;font-weight:bold;padding-left:10px" width=100%>
										<tr>
											<td width=20% align="center"><a target="_blank" href="<?=WIKI?>Fraktionen"><img border=none src="<?=WWWPUB.$local_imagepath?>/uic-logo-mittel.gif"></a></td>
											<td width=20% align="center"><a target="_blank" href="<?=WIKI?>Fraktionen"><img border=none src="<?=WWWPUB.$local_imagepath?>/bf-logo-mittel.gif"></a></td>
											<td width=20% align="center"><a target="_blank" href="<?=WIKI?>Fraktionen"><img border=none src="<?=WWWPUB.$local_imagepath?>/neb-logo-mittel.gif"></a></td>
											<td width=20% align="center"><a target="_blank" href="<?=WIKI?>Fraktionen"><img border=none src="<?=WWWPUB.$local_imagepath?>/sl-logo-mittel.gif"></a></td>
											<td width=20% align="center"><a target="_blank" href="<?=WIKI?>Fraktionen"><img border=none src="<?=WWWPUB.$local_imagepath?>/nof-logo-mittel.gif"></a></td>
										</tr>
										<tr>
											<td align="center"><a href="index.php?action=docu&kat=1&aid=1" class="ver11w">UIC
											<br><span style="font-size:8px;">United Industries Corporation</span>
											</a></td>
											<td align="center"><a href="index.php?action=docu&kat=1&aid=1" class="ver11w">BF
											<br><span style="font-size:8px">Brute Force</span>
											</a></td>
											<td align="center"><a href="index.php?action=docu&kat=1&aid=1" class="ver11w">NEB
											<br><span style="font-size:8px">New Economic Block</span>
											</a></td>
											<td align="center"><a href="index.php?action=docu&kat=1&aid=1" class="ver11w">SL
											<br><span style="font-size:8px">Shadow Labs</span>
											<td align="center"><a href="index.php?action=docu&kat=1&aid=1" class="ver11w">NoF
											<br><span style="font-size:8px">Nova Federation</span>
											</td></a>
										</tr>
									</table>
									</a>
								</td>
							</tr>
							<tr>
								<td>
									<br>
									<li>Zahlreiche Spielelemente:
									<table class="normal">
										<tr>
											<td>
												<span style="font-weight:bold">
												<ul>
													<li>Echtzeithandel mit Mitspielern
													<li>Spannende Kämpfe
													<li>Spionageaktionen
													<?if(!isKsyndicates()) {?><li>Aufträge an Mitspieler vergeben<?}?>
												<span>
											</td>
											<td>
												<span style="font-weight:bold">
												<ul>
													<li>Ausgeklügelter Forschungsbaum
													<li>Allianzen und Politik
													<li>Zahlreiche Teamspielelemente
													<?if(!isKsyndicates()) {?><li>Börsenhandel<?}?>
												<span>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<? } ?>
			
			
		</td>
			
		<td width=2%>
		</td>
		
				
		<td valign=top width=28% align="center" class="normal">
			<table width="90%" height="80px">
				<tr>
					<td width="50%" align="center">
						<a target="_blank" border="0" href="http://twitter.com/emogames"><img border="0" src="images/follow_us-b.png"></a>
					</td>
					<td width="50%" align="center">
						<fb:like href="http://www.facebook.com/apps/application.php?id=401508759907160" width="77px" layout="box_count" font="tahoma"></fb:like>
					</td>
				</tr>
			</table>
			<table class="rand" cellspacing=1 cellpadding=3 width=100%>
				<tr class="head">
					<td align="center" style="font-size:14px;font-family:Arial">
						<B>Screenshots</B>
					</td>
				</tr>
				<tr class="body">
					<td>
						<? require(INC."screenshots.php"); ?>
					</td>
				</tr>
			</table>
		</td>
		
	</tr>
</table>



<?
/*
		<!--
		<FONT class=ver12w>
			<B><? echo "$startseitedot"; ?> News</B>
			<BR>
			<BR>
			<?
			// Announcement daten holen
			$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc limit 8");

			echo "<table class=\"ver12w\" cellspacing=0 cellpadding=0 width=100% align=center>";
				foreach ($anms as $temp) {
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
					$showtime = date("d.m.y",$temp[time]);
					echo ("
						<tr>
							<td align=left>
								$trstrong<a class=\"$class\" href=\"index.php?action=news&anm_id=$temp[announcement_id]\">$temp[headline]</a>$trstrongende
							</td>
							<td align=right>
								<span >".$showtime."</span>
							</td>
						</tr>
					");
				}
				echo "
					<tr>
						<td colspan=2 height=10></td>
					</tr>
					<tr>
						<td colspan=2 align=left>
							Ältere News im <a href=\"index.php?action=news&archive=true\" class=\"gelblink\">News Archiv</a>.
						</td>
					</tr>
				";
			echo "</table><br><br>";
			?>
		</FONT>
		-->
		
		
		
		
		
		
		
		
						Syndicates ist ein browserbasiertes Multiplayer Onlinespiel. Sie können Syndicates direkt in ihrem Webbrowser spielen, ohne Software installieren zu müssen.<br>
						Das Spielprinzip: In der nahen Zukunft sind die Großkonzerne zu den wahren Herrschern aufgestiegen. In Syndicates übernehmen sie die Kontrolle über einen dieser Konzerne. Als Teil eines Syndikates versuchen Sie, Ihre wirtschaftliche, militärische und politische Macht zu maximieren, um Ihre Interessen durchzusetzen.
						Neue Spieler finden Hilfe in unserem <a href="index.php?action=docu&kat=1&aid=2" class="gelblink">Tutorial</a>.<br>
						Wenn Sie Lust auf Syndicates bekommen haben, können Sie sich <a href="index.php?action=anmeldung" class="gelblink">jetzt anmelden</a> und kostenlos spielen!
						<br><br>
		
		*/
?>
