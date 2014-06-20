?>
<p>Spionage (Aufklärung und Sabotage) ist ein essentielles Element, um Informationen über andere Konzerne zu sammeln und Ihnen auf subtile Art und Weise Schaden zuzufügen. Niemand sollte gegen einen Feind kämpfen, dessen wahre Stärke er nicht einschätzen kann. Damit dies nicht passiert erfahren Sie hier, was Sie beachten müssen. Um eine Aufklärungs/Sabotageaktion ausführen zu können, benötigen Sie Spione. Der Erfolg der Aktion hängt ab von der Anzahl Ihrer Spione relativ zu Ihrem Land, sowie der Anzahl der gegnerischen Spione relativ zu dem Land des Zieles. Natürlich ist hier auch etwas Glück im Spiel.<br><br>Brute Force und die United Industries Corporation verfügen über je drei verschiede Spione, Shadow Labs haben ihre Spionagemethoden schon optimiert und benötigen nur noch zwei verschiedene Spione. Jeder Spion besitzt drei Werte: Sabotagepunkte (OP), Aufklärungspunkte (AP) und Verteidigungspunkte (VP).<br> Wird eine Aktion gegen Sie ausgeführt, werden Ihre Verteidigungspunkte summiert und mit den Punkten des Eindringlings verrechnet.<br> Führen Sie selbst eine Aktion aus, werden je nach Aktion Ihre Sabotage/Aufklärungspunkte summiert und mit den Verteidigungspunkten Ihres Kontrahenten verglichen, außerdem wird die Anzahl der Ihnen zur Verfügung stehenden "Spionageaktionen" um eins verringert. Sabotageaktionen gegen Spieler, die mehr Land als sie besitzen, sind tendentiell schwerer als gegen Spieler ihrer größe (auf Spionageaktionen hat die Landgröße keinen Einfluss).<br><br> Sie können bis zu fünf zusätzliche Spionageaktionen aufwenden, um die Erfolgswahrscheinlichkeit Ihrer Aktion zu erhöhen.<br> Sie können nur spionieren, solange Sie noch Spionageaktionen haben.<br> Jede Stunde wird die Anzahl Ihrer verfügbaren Spionageaktionen um eins erhöht, bis zu einem Höchstwert von 15.<br> Es können keine Spionageaktionen gegen Konzerne unternommen werden, die mehr als fünf mal kleiner/größer als der eigene Konzern sind, sich noch in der Schutzphase befinden, im Urlaubsmodus sind oder mehr als 5% der Aktien Ihres Syndikats besitzen. Sabotageaktionen gegen Mitglieder eines Syndikates, mit dem Sie sich im Krieg befinden, sind effizienter als gewöhnlich, Spionageaktionen gegen Mitglieder des eigenen Syndikats sind nur dann möglich, wenn diese inaktiv sind. Um neue Spieler zu schützen können keine Spionageaktionen gegen Spieler mit weniger als 1000 Land verübt werden, die weniger als die Hälfte des eigenen Landes / Networths besitzen.<br><br><br>

Anmerkung: Wenn sie gegen einen Spieler mehr als <?=SPYPROTACTIONS+1?> erfolgreiche Sabotageaktionen innerhalb 24 Stunden verüben, wird eine Schutzvorrichtung aktiviert - die Ergebnisse ihrer Aktionen werden dann schnell schlechter. Im Kriegszustand gilt dies nicht.<br><br>
Bei Sabotageaktionen gegen inaktive Spieler ist mit einem schlechteren Ergebnis zu rechnen.
<br><br>
Wenn Sie die Sabotageaktion "Forschung zerstören", oder <? echo RACHERECHT_ON_SPYACTIONS_NUMBER ?> beliebige Sabotage-Aktionen gegen denselben Spieler durchgeführt haben, erhält dieser ein <a href=index.php?action=docu&kat=3&aid=18 class=gelblink>Recht auf Rache</a> gegen Sie.
<br><br>
<table class=rand cellspacing=1 cellpadding=0 border=0 width=600 align="center">
	<tr>
		<td>
			<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
				<tr>
					<td align=center>
						<b><i>Die einzelnen Spione</i></b>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="600" cellspacing="0" cellpadding="5" border="0" class=subhead>
				<tr>
					<td width="150" align="left"><b>Name</b></td>
					<td width="100" align="left"><b>Fraktion</b></td>
					<td width="150" align="left"><b>OP, AP, VP</b></td>
					<td width="150" align="left"><b>Kosten</b></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="600" cellspacing="0" cellpadding="4" border="0" class=body>
				<?
					$result = mysql_query("select name,race,credits,energy,op,dp,ip from spy_settings order by race,unit_id");
					while($return = mysql_fetch_assoc($result)) {
						if ($return[race] == "neb") {$return[race] = "NEB"; }
						if ($return[race] == "uic") {$return[race]= "UIC";}
						if ($return[race] == "nof") {$return[race]= "Nova Federation";}
						if ($return[race] == "pbf") {$return[race]= "Brute Force";}
						if ($return[race] == "sl") {$return[race]= "Shadow Labs";}
						echo ("
							<tr>
								<td width=\"150\" align=\"left\">$return[name]</td>
								<td width=\"100\" align=\"left\">$return[race]</td>
								<td width=\"150\" align=\"left\">$return[op] OP, $return[ip] AP, $return[dp] DP</td>
								<td width=\"150\" align=\"left\">$return[credits]Cr, $return[energy]MWh<br><br></td>
							</tr>
						");
					}
				?>
			</table>
		</td>
	</tr>
</table>

<br><br>

<table class=rand cellspacing=1 cellpadding=0 border=0 width=800 align="center">
	<tr>
		<td>
			<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
				<tr>
					<td align=center>
						<b><i>Verfügbare Spionageaktionen:</i></b>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="800" cellspacing="0" cellpadding="5" border="0" class=subhead>
				<tr>
					<td width="150" align="left"><b>Aktion</b></td>
					<td width="50" align="center"><b>Typ</b></td>
					<td width="80" align="center"><b>Schwierigkeit</b></td>
					<td width="400" align="center"><b>Auswirkung</b></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="800" cellspacing="0" cellpadding="4" border="0" class=body>
				<?
					$result = mysql_query("select type,difficulty,name,description,difficultyausgabe from spyaction_settings");
					while($return = mysql_fetch_assoc($result)) {
						if ($return[type] == "ip") {$typ = "Spionage";}
						elseif ($return[type] == "op")  {$typ = "Sabotage";}
						echo("
							<tr>
								<td width=\"150\" align=\"left\">$return[name]</td>
								<td width=\"50\" align=\"center\">$typ</td>
								<td width=\"80\" align=\"center\">$return[difficultyausgabe]</td>
								<td width=\"400\" align=\"left\">$return[description]<br><br></td>
							</tr>
						");
					}
				?>
			</table>
		</td>
	</tr>
</table>
<?
