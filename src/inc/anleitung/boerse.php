?>

Sie können bei Syndicates von jedem Syndikat Aktien erwerben:
<ul>
	<li>Der Preis der Aktien hängt von Stärke des Syndikats und aktuellem Wachstum ab.
	<li>Aktienkurse werden stündlich ermittelt.
	<li>Beim Verkauf von Aktien fallen Steuern von (40 + Spieltag * <?=AKTIEN_GLOBALSELLMALUS?>%) an.<br>
	<!-- beim privaten Verkauf muss gewartet werden, bis ein Spieler Aktien des entsprechenden Syndikats erwerben möchte.-->
	 
		<i>
		Beispiel: Am 5. Spieltag der Runde möchte ein Spieler Aktien im Wert von 1000 Cr. verkaufen. Er erhält dabei selbst 550 Cr. 450 Cr. werden als Steuern einbehalten.
		</i> 
	</li>
 
<!--Die beim globalen Verkauf angefallenen Steuern werden als Dividenden an die verbliebenen Aktionäre ausgeschüttet-->
	<li>Sie können pro Tag aus Aktienverkäufen lediglich <?echo pointit(MAXSELL_DAY);?> Cr. erhalten (nach Abzug der Steuern, s.o.).
<!-- Für den privaten Verkauf gibt es keinerlei Einschränkungen.-->
</ul>
<br>
Der Menüpunkt "Börse" ist erst dann verfügbar, wenn ihr Konzern nicht mehr unter Schutz steht.
<br>
Der Besitz vieler Aktien eines Syndikats bietet Ihnen verschiedene Vorteile:<br><br><br>


<table align=center class=rand cellspacing=1 cellpadding=0 border=0 width=400>
	<tr>
		<td>
			<table class=head cellspacing=0 cellpadding=4 border=0 width=100%>
				<tr>
					<td width=120>Aktienbesitz</td>
					<td align=center width=280>Bonus</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=4 cellspacing=0 width=100%>
				<tr>
					<td width=120><? echo AKTIEN_DIVIDENDEN; ?>%</td>
					<td width=280><li>Auszahlung von Dividenden</td>
				</tr>
				<tr>
					<td width=120><? echo AKTIEN_SYNDSCIENCEREADOPTION; ?>%</td>
					<td width=280><li>Sie erhalten Einsicht in die Syndikatsforschungen.</td>
				</tr>
				<tr>
					<td width=120><? echo AKTIEN_PREVENTOPTION; ?>%</td>
					<td width=280><li>Sie können nicht mehr von Mitgliedern des entsprechenden Syndikats angegriffen oder Ausspioniert werden.<br> Ausnahme: Sie greifen ein Syndikatsmitglied erfolgreich an - dieses hat dann <a href=index.php?action=docu&kat=3&aid=18 class=gelblink11>das Recht, zurückzuschlagen</a>. Außerdem dürfen andere Mitglieder aus diesem Syndikat Sie innerhalb der nächsten 24h angreifen, allerdings gelten für diese nicht die übrigen Vergünstigungen, wie beim <a href=index.php?action=docu&kat=3&aid=18 class=gelblink11>Racherecht</a>.<br>Im Krieg gibt es diesen Schutz nicht.</td>
				</tr>
				<tr>
					<td width=120><? echo AKTIEN_AKTUELLES; ?>%</td>
					<td width=280><li>Sie können interne Syndikatsgeschehnisse mitverfolgen (Aktuelles)</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<br><br>
Dividenden werden alle drei Stunden ausgeschüttet. Es können höchstens von <? echo MAXANZAHL_AKTIENDEPOTS; ?> verschiedenen Syndikaten Aktien erworben werden.
<br><br>
<?
