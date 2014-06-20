?>
In der rauhen Welt von Syndicates spielt militärische Macht eine große Rolle. Wer sich nicht angemessen verteidigen kann, unterliegt schnell im vorherrschenden Machtkampf.<br>
Damit Sie nicht zu den Verlierern gehören, gibt es hier einige grundlegende Informationen zu Kampfsystem und Militäreinheiten in Syndicates.<br><br>
Mit einigen Ausnahmen kann man von jedem beliebigen Spieler angegriffen werden. Verläuft der Angriff erfolgreich, erobert der Angreifer einen Teil Ihres Landes. Alle darauf errichteten Gebäude werden dabei vernichtet. Unabhängig vom Erfolg der Kampfhandlung verlieren beide beteiligten Seiten einen Teil ihrer Militäreinheiten (Kampfverluste<font color=green>*</font>).<br>
Aber wann ist ein Angriff erfolgreich ?<br>
Jede Militäreinheit besitzt eine bestimmte Anzahl an Angriffpunkten (AP) und Verteidigungspunkten (VP). Der Angriff ist genau dann erfolgreich, wenn der Angreifer mehr Angriffspunkte hat, als der Verteidiger Vertedigigungspunkte.<br>
Um eine effiziente Verteidigung aufzubauen, sollten also nach Möglichkeit Einheiten mit vielen Verteidigungspunkten gebaut werden - anfangs empfehlen sich bei allen Fraktionen <i>Ranger</i> wegen der geringen Kosten.<br>
Details zum Angriff finden sie <a href="index.php?action=docu&kat=2&aid=12" class="gelblink">hier</a>.
<br><br>

<b><i>Bau von Militäreinheiten</i></b><br>
Um neue Militäreinheiten zu produzieren benötigen Sie ausreichend <a href="index.php?action=docu&kat=1&aid=14" class="gelblink">Ressourcen</a> und genügend freie Kapazitäten.<br>
Kapazitäten ? Auf jeden Hektar Land können Sie <?=LANDWERT?> Militäreinheiten bauen, errichten Sie zusätzlich noch Lagerhallen, können Sie entsprechend mehr Militäreinheiten bauen.<br>
Das Produzieren neuer Militärinheiten dauert gewöhnlich 20 Züge. Sie können alternativ Militäreinheiten am <a href="index.php?action=docu&kat=2&aid=7" class="gelblink">Global Market</a> erwerben, diese sind dann schneller verfügbar.
<br><br>
Jede Fraktion bei Syndicates verfügt über fünf verschiedene Militäreinheiten:<br><br><br><br>
<table width=800 cellspacing=0 cellpadding=0 border=0>
	<tr>
		<td>
			<table class=rand cellspacing=1 cellpadding=0 border=0 width=800>
				<tr>
					<td>
						<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
							<tr>
								<td align=center>
									<b><i>United Industries Corporation:</i></b><br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="100" align="left">Name</td>
								<td width="100" align="left">Kampfstärke</td>
								<td width="150" align="left">Produktionskosten</td>
								<td width="350" align="left">Specials</td>
							</tr>
							<tr><td colspan="4" height="8"></td>
							<?
								$result = mysql_query("select name,op,dp,credits,minerals,energy,specials,sciencepoints from military_unit_settings where race='uic' order by unit_id");
								while($return = mysql_fetch_assoc($result)) {
									if ($return[sciencepoints]) {
										$spstring = ", ".$return[sciencepoints]." P";
										//$sentinelstring = "*";
									}
									if ($return[credits] > 0) {
										$crstring = $return[credits]." Cr,";
									}

									echo("
											<tr><td colspan=\"4\" height=\"8\"></td>
											<tr>
												<td width=\"100\" align=\"left\">$return[name]</td>
												<td width=\"100\" align=\"left\">$return[op] AP".$sentinelstring." / $return[dp] VP".$sentinelstring."</td>
												<td width=\"150\" align=\"left\">$crstring $return[minerals] t, $return[energy] MWh$spstring </td>
												<td width=\"350\" align=\"left\">$return[specials]</td>
											</tr>
									");
									unset($spstring,$crstring);
								} // while
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr><td><br></td></tr>
	<tr>
		<td>
			<table class=rand cellspacing=1 cellpadding=0 border=0 width=800>
				<tr>
					<td>
						<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
							<tr>
								<td align=center>
									<b><i>Shadow Labs:</i></b><br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="100" align="left">Name</td>
								<td width="100" align="left">Kampfstärke</td>
								<td width="150" align="left">Produktionskosten</td>
								<td width="350" align="left">Specials</td>
							</tr>
							<tr><td colspan="4" height="8"></td>
							<?
								$result = mysql_query("select name,op,dp,credits,minerals,energy,specials,sciencepoints from military_unit_settings where race='sl' order by unit_id");
								while($return = mysql_fetch_assoc($result)) {
									if ($return[sciencepoints]) {
										$spstring = ", ".$return[sciencepoints]." P";
									}
									if ($return[credits] > 0) {
										$crstring = $return[credits]." Cr,";
									}
									echo("
											<tr><td colspan=\"4\" height=\"8\"></td>
											<tr>
												<td width=\"100\" align=\"left\">$return[name]</td>
												<td width=\"100\" align=\"left\">$return[op] AP / $return[dp] VP</td>
												<td width=\"150\" align=\"left\">$crstring $return[minerals] t, $return[energy] MWh$spstring </td>
												<td width=\"350\" align=\"left\">$return[specials]</td>
											</tr>
									");
									unset($spstring,$crstring);
								} // while
							?>

						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td><br></td></tr>
	<tr>
		<td>
			<table class=rand cellspacing=1 cellpadding=0 border=0 width=800>
				<tr>
					<td>
						<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
							<tr>
								<td align=center>
									<b><i>Brute Force:</i></b><br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="100" align="left">Name</td>
								<td width="100" align="left">Kampfstärke</td>
								<td width="150" align="left">Produktionskosten</td>
								<td width="350" align="left">Specials</td>
							</tr>
							<tr><td colspan="4" height="8"></td>
								<?
								$result = mysql_query("select name,op,dp,credits,minerals,energy,specials,sciencepoints from military_unit_settings where race='pbf' order by unit_id");
								while($return = mysql_fetch_assoc($result)) {
									if ($return[sciencepoints]) {
										$spstring = ", ".$return[sciencepoints]." P";
									}
									if ($return[credits] > 0) {
										$crstring = $return[credits]." Cr,";
									}
									echo("
											<tr><td colspan=\"4\" height=\"8\"></td>
											<tr>
												<td width=\"100\" align=\"left\">$return[name]</td>
												<td width=\"100\" align=\"left\">$return[op] AP / $return[dp] VP</td>
												<td width=\"150\" align=\"left\">$crstring $return[minerals] t, $return[energy] MWh$spstring </td>
												<td width=\"350\" align=\"left\">$return[specials]</td>
											</tr>
									");
									unset($spstring,$crstring);
								} // while
								?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td><br></td></tr>
	<tr>
		<td>
			<table class=rand cellspacing=1 cellpadding=0 border=0 width=800>
				<tr>
					<td>
						<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
							<tr>
								<td align=center>
									<b><i>New Economic Block:</i></b><br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="100" align="left">Name</td>
								<td width="100" align="left">Kampfstärke</td>
								<td width="150" align="left">Produktionskosten</td>
								<td width="350" align="left">Specials</td>
							</tr>
							<tr><td colspan="4" height="8"></td>
							<?
								$result = mysql_query("select name,op,dp,credits,minerals,energy,specials,sciencepoints from military_unit_settings where race='neb' order by unit_id");
								while($return = mysql_fetch_assoc($result)) {
									if ($return[sciencepoints]) {
										$spstring = ", ".$return[sciencepoints]." P";
									}
									if ($return[credits] > 0) {
										$crstring = $return[credits]." Cr,";
									}

									echo("
											<tr><td colspan=\"4\" height=\"8\"></td>
											<tr>
												<td width=\"100\" align=\"left\">$return[name]</td>
												<td width=\"100\" align=\"left\">$return[op] AP / $return[dp] VP</td>
												<td width=\"150\" align=\"left\">$crstring $return[minerals] t, $return[energy] MWh$spstring </td>
												<td width=\"350\" align=\"left\">$return[specials]</td>
											</tr>
									");
									unset($spstring,$crstring);
								} // while
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td><br></td></tr>
	<tr>
		<td>
			<table class=rand cellspacing=1 cellpadding=0 border=0 width=800>
				<tr>
					<td>
						<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
							<tr>
								<td align=center>
									<b><i>Nova Federation:</i></b><br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
							<tr>
								<td width="100" align="left">Name</td>
								<td width="100" align="left">Kampfstärke</td>
								<td width="150" align="left">Produktionskosten</td>
								<td width="350" align="left">Specials</td>
							</tr>
							<tr><td colspan="4" height="8"></td>
							<?
								$result = mysql_query("select name,op,dp,credits,minerals,energy,specials,sciencepoints,type from military_unit_settings where race='nof' order by unit_id");
								while($return = mysql_fetch_assoc($result)) {
									$estring = $return[energy]." MWh";
									if ($return[sciencepoints]) {
										$spstring = ", ".$return[sciencepoints]." P";
									}
									if ($return[credits] > 0) {
										$crstring = $return[credits]." Cr,";
									}
									if ($return[type] == "techs") {
										//$spstring.="*";
										//$estring.="*";
									}

									echo("
											<tr><td colspan=\"4\" height=\"8\"></td>
											<tr>
												<td width=\"100\" align=\"left\">$return[name]</td>
												<td width=\"100\" align=\"left\">$return[op] AP / $return[dp] VP</td>
												<td width=\"150\" align=\"left\">$crstring $return[minerals] t, $estring $spstring </td>
												<td width=\"350\" align=\"left\">$return[specials]</td>
											</tr>
									");
									unset($spstring,$crstring);
								} // while
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>	
</table>
<br><br>




<ul>* Recycelt: In einem erfolgreichen Angriff recycelt ein Sentinel eine gefallene Einheit für pauschal 2000 Cr, wobei dieser Betrag als Lagerguthaben ausbezahlt wird.<br></ul>
<!--<ul>* Produziert: Der Sentinel produziert 10 Cr je Stunde.<br><br></ul>-->
<!--<ul>* Variable Stärke: Die Angriffsstärke richtet sich nach dem Verhältnis von gegnerischem Land zu eigenem Land bzw. eigenem Land zu gegnerischem Land. Es steht hierbei immer die kleinere Landmenge im Nenner. Diese Zahl mit 100 multipliziert ergibt eine Prozentzahl. Für je 10% von 100% abweichend, erhält der Sentinel entweder 1 AP dazu (man selbst steht im Nenner), bzw. weniger (man selbst steht im Zähler). Die Stärke des Sentinel varriiert jedoch nie um mehr als 6 AP.
Die Verteidigungspunkte des Sentinel berechnen sich analog, jedoch können die Verteidigungspunkte nicht unter 17 fallen.
</ul>-->
<ul><font color=green>*</font>Kampfverluste können durch Boni nicht auf weniger als 10% der Standardkampfverluste gesenkt werden!

<?
