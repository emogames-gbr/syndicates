?>

<i>[Nur Classic-Server]</i><br><br>

Monumente sind einzigartige Gegenstände in Syndicates! <br>Ein Monument wird immer von einem kompletten Syndikat errichtet und kostet <? echo pointit(KOSTEN_ARTEFAKT); ?> P. Forschungspunkte.<br>
Nach dem Bau eines Monumentes profitiert jeder Spieler des Syndikats von den jeweiligen Eigenschaften.
Momentan sind folgende Monumente verfügbar:
<br><br>


<?

$monumente = assocs("select ab.*,a.* from artefakte as a,artefakte_boni as ab where a.artefakt_bonus_id = ab.bonus_id");

$ag .="
<table align=center class=rand cellspacing=1 cellpadding=0 border=0 width=400>
	<tr>
		<td>
			<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
				<tr>
					<td width=120>Monument</td>
					<td align=center width=280>Bonus</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=4 cellspacing=0 width=100%>";
			foreach ($monumente as $m) {
				$ag .="
					<tr>
						<td width=120>$m[name]</td>
						<td width=280>$m[description]</td>
					</tr>
				";
				
			}
			$ag.="
			</table>
		</td>
	</tr>
</table>
";
echo $ag;
?>
<br><br>
<b>Bau von Monumenten</b><br>
Beim Bau von Monumenten sind einige Regeln zu beachten:
<ul>
	<li>Nur der Präsident kann den Bau eines Monumentes starten
	<li>Jedes Syndikat kann maximal ein Monument besitzen
	<li>Baut ein Syndikat ein Monument, werden jedem Spieler pro Zug automatisch <?=pointit(BUCHUNGSBETRAG_TICK)?> P. Forschungspunkte vom Konto abgebucht (falls vorhanden) und in den Bau des Monuments investiert
	<li>Errichten zwei Syndikate dasselbe Monument, erhält das Syndikat das Monument, welches den Bau zuerst fertigstellt. Alle Ressourcen, die von anderen Syndikaten in den Bau dieses Monuments investiert wurden, sind verloren! 
	<li>Jeder Spieler kann entscheiden, ob er beim Bau eines Monuments mithelfen möchte oder nicht
	<!--<li>Für Anfängersyndikate sind Monumente nicht verfügbar-->
</uL>
<br><br> 
<b>Monumente und Krieg</b><br>
Monumente können im Krieg erobert oder zerstört werden.
<ul>
	<li>Verliert ein Syndikat, welches ein Monument besitzt einen Krieg, wird das Monument a) zerstört, wenn das Siegersyndikat bereits ein Monument besitzt oder b) erobert, wenn das Siegersyndikat noch kein Monument besitzt und 6% netto vom Gesamtland zum Zeitpunkt der Kriegserklärung erobert hat.
	<li>Verliert eine Allianz, in der mindestens ein Syndikat ein Monument besitzt, einen Krieg gegen eine andere Allianz, werden soviele Monumente erobert, wie es Syndikate in der Siegerallianz gibt, die noch kein Monument haben, alle weiteren Monumente der Verlierer werden zerstört.
</ul>

<?
