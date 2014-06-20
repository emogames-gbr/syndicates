?>
<p>
Die Infrastruktur Ihres Konzerns wird maßgeblich durch die errichteten Gebäude bestimmt. Auf jedem Hektar Land, den Sie besitzen, können Sie ein Gebäude bauen. Wenn Sie ihren Landbesitz erweitern möchten, ist es möglich, neues Land hinzuzukaufen.
<br><br>
Jedes errichtete Gebäude hat einen bestimmten <b>Energiebedarf</b>.<br>
Gebäude mit <b>geringem</b> Energiebedarf benötigen einen Unterhalt von <b>10 Energieeinheiten</b> pro Stunde.<br>
Gebäude mit <b>mittlerem</b> Energiebedarf benötigen einen Unterhalt von <b>25 Energieeinheiten</b> pro Stunde.<br>
Gebäude mit <b>hohem</b> Energiebedarf benötigen einen Unterhalt von <b>40 Energieeinheiten</b> pro Stunde.<br><br>
Achten Sie darauf, immer genügend Energie zur Verfügung zu haben, sonst kann es passieren, dass einige Gebäude Produktionseinbußen erleiden oder gar nicht mehr funktionieren!
<br>
<br>
<table class="rand" width="70%" cellspacing=1 cellpadding=0 align="center"><tr><td>
<table width="100%%" cellpadding="4" align="center" cellspacing=1>
		<tr class="head">
			<td align="center">
				<B>Einige Regeln im Umgang mit Land und Gebäuden:</B>
			</td>
		</tr>
		<tr class="body">
			<td>
				<ul>
					<li> Der Bau eines Gebäudes oder der Erwerb neuen Landes dauert gewöhnlich 20 Stunden (die Bauzeit lässt sich durch verschiedene Boni auf bestenfalls 3h reduzieren)
					<li> Sie können höchstens soviel Land auf einmal dazukaufen, wie Sie bereits besitzen, allerdings nie mehr als 1000 Land gleichzeitig.
					<li>Bei Energiemangel arbeiten viele Gebäude nur eingeschränkt oder gar nicht!
				</ul>
			</td>
		</tr>
	</table>
</td></tr></table>
<br>
<br>Die Preise für Gebäude und Land berechnen sich folgendermaßen:
<br><br>
<b>
Landpreis = 1000 + Landmenge * Landmenge / 280<br><br>
Gebäudepreis = 1000 + Gebäudemenge * Gebäudemenge / 1500
</b>
<br><br>
Im Einzelnen stehen Ihnen folgende Gebäude zur Verfügung:
<br><br>
		<table align="center" width="700" cellspacing="1" cellpadding="0" border="0" class=rand>
			<tr>
				<td>
					<table width="700" cellspacing="0" cellpadding="4" border="0" class=head>
						<tr>
							<td width="200" align="left"><b>&nbsp;Gebäude</b></td>
							<td width="400" align="center"><b>&nbsp;&nbsp;&nbsp;Nutzen</b></td>
							<td width="50" align="center"><b>&nbsp;Energiebedarf</b></td>
							<td width="50" align="center"><b>&nbsp;Fraktion</b></td>
						</tr>
					</table>
				</td>
			</tr>
						<?
						$races = assocs("select * from races","race");
						$result = mysql_query("select building_id,name,nutzen,verbrauch,synbonus,race,erforschbar from buildings order by erforschbar,race,building_id");
						while($return = mysql_fetch_assoc($result)) {
							$race = array();
							$races_split = split(",", $return[race]);
							foreach ($races_split as $vl) { array_push($race, $races[$vl][tag]); };
							$race = join(", ", $race);
							if (preg_match("/all/", $return[race])) {$race = "alle";}
							if ($return[erforschbar] == 1) {$erforschbar = "<span class=\"nrot11\">*</span>";}
							elseif ($return[erforschbar] == 2) {$erforschbar = "<span class=\"nrot11\">**</span>";}
							else {$erforschbar = "";}
							echo("
								<tr>
									<td>
										<table class=body cellpadding=6 cellspacing=0 border=0>
											<tr>
												<td width=\"200\" align=\"left\" style=\"border-right:1px solid black\">
													<center><b>$return[name]$return[synbonus]$erforschbar</b></center><br>
													<img width=\"200\" src=\"images/$return[building_id].jpg\">
													
												</td>
												<td width=\"400\"  align=\"left\">$return[nutzen]<br><br></td>
												<td width=\"50\" align=\"center\">$return[verbrauch]</td>
												<td width=\"70\" align=\"center\">$race</td>
											</tr>
										</table>
									</td>
								</tr>
							");
						}
						?>
		</table>
<br><br>
<font class ="normal">
<br>
<!--Fabriken, Bauhöfe und Geheimdienstzentren arbeiten nur bei einer positiven Energiebilanz. Ist bei einer negativen Energiebilanz alle Energie aufgebraucht, wird jegliche Ressourcenproduktion halbiert.<br>--><br>
Bei den Syndikatsproduktionsgebäuden wird der angegebene Betrag ins <a href="index.php?action=docu&kat=2&aid=13" class="gelblink">Syndikatslager</a> eingezahlt.<br>
Produziert ein Syndikatsgebäude beispielsweise 100 Einheiten einer Ressource, werden diese 100 Einheiten ins Lager eingezahlt (diese sind dann auch dort vorhanden), 11 Credits (bei 10% Lagersteuer) werden dabei als Dividenden verbucht (d.h. theoretisch "produziert" das Gebäude 111 Einheiten, da die übrigen Einheiten aber als Steuern einbehalten werden, sind diese nicht angegeben; Hinweis: die ins Lager produzierte Menge wird durch das Monument <i><a href="index.php?action=docu&kat=2&aid=30" class="gelblink">Globalisierungsmasterplan</a></i> nicht erhöht, wie man vielleicht erwarten würde, stattdessen wird lediglich der für die Dividenden vorgesehene Betrag reduziert, also die <b>theoretische</b> Produktion verringert.).<br><br>

*: Für dieses Gebäude gilt der <a href="index.php?action=docu&kat=3&aid=16" class="gelblink">Synergiebonus</a>.<br>
<span class="nrot11">*</span>: Um dieses Gebäude bauen zu können, müssen Sie zuerst die Forschung <i>Advanced Building Construction</i> erforschen.<br>
<span class="nrot11">**</span>: Um dieses Gebäude bauen zu können, müssen Sie zuerst die Forschung <i>Hightech Building Construction</i> erforschen.<br>
