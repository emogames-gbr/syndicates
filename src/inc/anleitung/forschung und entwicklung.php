?>
Forschung hat bei Syndicates einen hohen Stellenwert. Wer auf veraltete Technik und überholte Methoden setzt, hat im Falle einer Konfrontation nicht gerade die besten Karten. Momentan gibt es bei Syndicates drei Forschungskategorien: <i>Military Sciences</i>, <i>Industrial Sciences</i> und <i>Global Sciences</i>. Um forschen zu können, müssen Sie zuerst Forschungspunkte anhäufen, diese werden durch <a href="index.php?action=docu&kat=1&aid=3" class="gelblink">Forschungslabore</a> bereitgestellt. Einzelne Forschungen sind in verschiedene Technologiestufen unterteilt. Je höher die Technologiestufe, desto mehr Zeit und Forschungspunkte sind notwendig um zu forschen. Weiterhin können Sie nur an einem Forschungsprojekt gleichzeitig arbeiten. Um Forschungsprojekte höherer Technologiestufen starten zu können, müssen Sie zumindest ein Projekt aus derselben Kategorie erforscht haben, welches in der Technologiestufe genau eins unter Ihrem neuen Projekt steht. Sie können in jeder Kategorie nur vier der fünf Stufe Eins Forschungen entwickeln, nur drei der vier Stufe Zwei Forschungen usw. Es kann ausserdem nur eine einzige Stufe 5 Forschung entwickelt werden. Die meisten entwickelten Forschungen können noch weiterentwickelt werden, dies ist jedoch vergleichsweise teuer. Forschungen, die mit einem <span class=nrot12>*</span> gekennzeichnet sind, können nicht weiterentwickelt werden. Bei weiterentwickelten Forschungen wird die angegebene Wirkung mit dem Forschungslevel multipliziert.<br>
<br>Die einzelnen Forschungen sind im folgenden aufgezählt:
<br><br>


<?
$sarray = array(mil,ind,glo);
$snames = array("Military Sciences","Industrial Sciences","Global Sciences");
for ($a=0; $a<3;$a++) {
?>
	<table class=rand cellspacing=1 cellpadding=0 border=0 width=800 align="center">
		<tr>
			<td>
				<table class=head cellpadding=4 cellspacing=0 border=0 width=100%>
					<tr>
						<td align=center>
							<b><i><? echo $snames[$a]; ?></i></b>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=subhead cellpadding=4 cellspacing=0 border=0 width=100%>
					<tr>
						<td  width="200" align="left"><b>Forschung:</b></td>
						<td  width="450" align="left"><b>Nutzen:</b></td>
						<td  width="150" align="center"><b>Kosten:</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<? for ($i=1;$i<6;$i++) { ?>
		<tr>
			<td>
				<table class=subsubhead cellpadding=4 cellspacing=0 border=0 width=100%>
					<tr>
						<td width="800" colspan="3" align="left"><b>Technologiestufe <? echo $i; ?>:</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class=body cellpadding=4 cellspacing=0 border=0 width=100%>
					<?
					$result = mysql_query("select gamename,description,sciencecosts,maxlevel from sciences where level=$i and treename='$sarray[$a]' and available = 1 order by typenumber");
					while($return = mysql_fetch_assoc($result)) {
					if ($return[maxlevel] == 1) {$star = "<span class=\"nrot11\"> *</span>";}
					echo("
							<tr>
								<td width=\"200\" align=\"left\">$return[gamename]$star</td>
								<td width=\"450\" align=\"left\">$return[description]</td>
								<td width=\"150\" align=\"center\">$return[sciencecosts] P</td>
							</tr>
							<tr>
								<td width=\"800\" colspan=\"3\" height=\"7\" align=\"left\"></td>
							</tr>
					");
					$star="";
					}
					?>
				</table>
			</td>
		</tr>
		<? } ?>
	</table>
	<br><br>
<? } ?>
	(Syndikatsforschung): Der angegebene Bonus gilt für ein vollbesetztes Syndikat mit <? echo (MAX_USERS_A_SYNDICATE); ?> Spielern. Wenn ein Syndikat nicht vollbesetzt ist, wird die Wirkung der Syndikatsforschungen entsprechend erhöht, um das Syndikat nicht zu benachteiligen. <!--Eine Ausnahme bilden <b>geschlossene</b> Syndikate. Dies sind Syndikate, die sich dazu entschlossen haben, keine neuen Spieler aufzunehmen (mehr hierzu ist unter <a href=index.php?action=docu&kat=2&aid=9 class=ver12w>Politik</a> nachzulesen). Für diese Syndikate wird nur dann die Wirkung der Forschungen angehoben, wenn sich weniger als 17 Spieler darin befinden. In diesem Fall wird auch nur auf 17 Spieler hochgerechnet und nicht auf 20.<br><!--ACHTUNG: Der Präsident des Syndikats hat die Möglichkeit einzustellen, dass ein Spieler nicht von allen im Syndikat entwickelten Forschungen profitiert, sondern nur von so vielen Leveln einer Syndikatsforschung profitieren kann, wie er selbst erforscht hat.<br>
	Beispiel:Hat ein Spieler eine Syndikatsforschung auf Stufe 2 erforscht und zwei andere Spieler seines Syndikats haben dieselbe Forschung auf Stufe 3 erforscht, profitiert dieser Spieler bei dieser Einstellung lediglich von 6 Leveln der Forschung, die beiden anderen Spielern profitieren von den kompletten 8 Stufen, die insgesamt erforscht wurden.<br>--><br>Bei der Anzeige der von den einzelnen Spielern des Syndikats erforschten Syndikatsforschungen bedeutet eine in eckigen Klammern angegebene Stundenzahl, dass dieser Spieler gerade dabei ist diese Syndikatsforschung weiter zu entwickeln. Die Zahl selbst gibt an, in wievielen Stunden die Forschung fertiggestellt wird.
<?
