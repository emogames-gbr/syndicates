?>


Kaum ein Spieler wird auf Dauer Syndicates spielen können, ohne irgendwann einmal in einen Krieg hineinzugeraten oder vielleicht sogar absichtlich einen anzufangen.<br>
Alle wichtigen Informationen bzgl. Krieg sind hier deshalb nochmals zusammengefasst:<br><br><br>

<ol>
<li><b>Wer kann mit wem Krieg führen?</b><br><br>
	<ul>
		<li>In einem Krieg können maximal sechs Syndikate beteiligt sein, wenn nämlich zwei 3er-Allianzen gegeneinander antreten.
		<li>Im Übrigen ist jede denkbare Konstellation zwischen einzelnem Syndikat und Allianz mit zwei oder drei Syndikaten möglich
	</ul>
<br>
<li><b>Bedingungen, um Krieg erklären zu können</b><br><br>
	<ul>
		<li>WICHTIG: Nach einer Kriegserklärung dauert es 24h, bis der Krieg wirklich beginnt, also die weiter unten aufgeführten Änderungen bei Angriffen und Spionage zu wirken beginnen. Diese Vorwarnzeit dient dazu, dem Gegner die Chance zu gewähren, sich halbwegs auf den Krieg vorzubereiten und so unfaire Bashaktionen durch die kriegserklärende Partei gleich nach der Kriegserklärung zu verhindern.
		<li>Befindet sich ein Syndikat in einer Allianz, kann diesem Syndikat alleine kein Krieg erklärt werden. Möchte man diesem Syndikat den Krieg erklären, erklärt man ihn automatisch auch seinen Allianzpartnern.
		<li>Wenn eine Allianz jemand anderem den Krieg erklären möchte, müssen die Präsidenten der an der Allianz beteiligten Syndikate alle zustimmen (derzeit noch nicht implementiert).
		<li>Im Folgenden ist mit der Bezeichnung "Syndikat" auch "Allianz" gemeint. Der Unterschied zwischen Syndikat und Allianz spielt also bei den folgenden Einschränkungen keine Rolle.				<li>Ein Syndikat kann erst dann Krieg erklären bzw. erklärt bekommen, wenn jeder Spieler im Schnitt 1.000 ha Land besitzt (also Gesamtland/Anzahl_der_Spieler größer oder gleich 1.000).
		<li>Das Syndikat, welchem der Krieg erklärt werden soll, darf zum Zeitpunkt der Kriegserklärung maximal 20% weniger Networth als auch Durchschnittsnetworth (Gesamtnetworth geteilt durch Anzahl der Spieler) haben als das kriegserklärende Syndikat. (Beachte Ausnahmeregelung bei Allianzen mit kleinen Syndikaten, siehe nächster Punkt)
		<li>Syndikaten, die mit mindestens einem anderen Syndikat alliiert sind, welches weniger als 50% des Networths des Syndikats hat, kann unabhängig vom Faktor Durchschnittsnetworth der Krieg erklärt werden.
		<li>Zwischen zwei Kriegserklärungen gegen den selben Gegner muss mindestens soviel Zeit verstreichen, wie der Krieg davor gedauert hat. Das bedeutet, wenn ein krieg 48h lang ging, kann erst 48h nach Ende dieses Krieges ein erneuter Krieg zwischen den selben Parteien erklärt werden.
	</ul>
<br>
<li><b>Änderungen bei Angriffen während eines Krieges</b><br><br>
	<ul>
		<li>Die Angriffstypen "Belagerung" und "Spione zerstören" sind während des Kriegszustandes effizienter, siehe auch <a href=index.php?action=docu&kat=2&aid=12 class=gelblink>Angriff</a>.
		<li>Während eines Krieges werden 50% des Landgewinns bei erfolgreichen Angriffen gleichmäßig unter den Syndikatsmitgliedern aufgeteilt, damit nicht nur die auf den Angriff spezialisierten Spieler einen Nutzen davon haben.
		<li>Es besteht nur ein sehr geringer Schutz vor wiederholten Angriffen.
		<li>Es gibt keine Beschränkung der Anzahl ausführbarer Angriffe (für gewöhnlich können nur fünf Angriffe pro Tag unternommen werden).
		<li>Um zu verhindern, dass einzelne Spieler zu den alleinigen Opfern eines Krieges werden, gilt folgende Prioritätenregelung:<br>
Bei Spielern, die noch nicht angegriffen wurden (weiß in der Syndikatsübersicht), wird insgesamt 20% mehr Land erobert als gewöhnlich.<br>
Spieler, die bereits einmal angegriffen wurden haben mit normalem Landverlust (100%) zu rechnen.<br>Bei Spielern die mehr als einmal angegriffen wurden, sinkt die Menge eroberten Landes pauschal auf 80% dessen, was erobert würde, wenn sie unter normalen Bedingungen angegriffen würden.
	</ul>
<br>
<li><b>Änderungen bei Spionage während eines Krieges</b><br><br>
	<ul>
		<li><b>Sabotage</b>aktionen sind während des Kriegszustandes effizienter.
		<li>Während eines Krieges kann die Sabotageaktion "Forschung zerstören" ausgeführt werden.
		<li>Sabotageaktionen werden nicht mehr schwächer, wenn mehrere Aktionen gegen denselben Spieler ausgeführt werden.
		<li>Bei der Sabotageaktion "Militäreinheiten zerstören" wird nun tatsächlich ein Teil der sabotierten Einheiten vernichtet (ca. 40%), der übrige Teil wird nachwievor für 12h außer Gefecht gesetzt.
	</ul>
<br>
<li><b>Änderungen bei Aufträgen während eines Krieges</b><br><br>
	<ul>
		<li>Aufträge gegen Spieler aus dem/den Syndikat/en des Kriegsgegners können nicht mehr angenommen werden. Ausnahme: der Auftraggeber kommt aus dem eigenen Syndikat bzw. der eigenen Allianz. (<i>Sinn und Zweck dieser Regel ist, dass das eroberte Land innerhalb der Kriegsparteien bleibt und nicht an Auftraggeber außerhalb des Krieges verschoben werden kann</i>)
	</ul>
<br>
<li><b>Kriegsende</b><br><br>
	<ul>
		<li>Um zu vermeiden, dass ein Krieg ewig dauert, zählt diejenige Partei, die zuerst 12% des Landes, welche sie zu Beginn des Krieges besessen hat, durch den Kriegsgegner in Angriffen verloren hat, abzüglich des selbst eroberten Landes vom Kriegsgegner ("Seilziehen-Prinzip"), als Verlierer des Krieges. Der Gewinner enthält entsprechend die Prämie (s.o.) und evtl. das Monument des Verlierers (wichtig: der Präsident muss einstellen, dass das Monument erobert oder zerstört werden soll!).<br>Um der verteidigenden Partei das Gewinnen zu erleichtern, benötigt sie je 12h Krieg, die vergangen sind, 3% weniger Landerobung zum Gewinnen, bis nach 4 Tagen der Krieg für den Angreifer automatisch verloren ist (es reichen dann -12% Landeroberung für den Verteidiger zum Gewinnen, was dann quasi immer erfüllt ist, da der Angreifer sonst schon gewonnen hätte).
		<li>Jede Partei hat nach 6%-netto-Landverlust bezogen auf das zum Zeitpunkt der Kriegserklärung vorhandene Gesamtland die Möglichkeit zu kapitulieren und dadurch den Krieg zu beenden. Ein eventuell vorhandenes Monument geht dabei allerdings verloren.
		<li><b>Kriegsprämie</b>
			<ul>
				<li>Am Ende eines Krieges bekommt der Gewinner (das Syndikat, welches den Krieg beendet, ist der Verlierer) eine Kriegsprämie in Höhe von 600 Handelspunkten (das ist die interne Syndikatswährung) pro erobertem Land während des Krieges und pro angefangenem Spieltag zu Beginn der Kriegserklärung (beginnt ein Krieg beispielsweise am 14. Tag nach Rundenstart, ist die Prämie 8.400 Handelspunkte je erobertem Land). Der ermittelte Wert wird auf die Ressourcen Credits, Erz und Forschungspunkte im Verhältnis 2:1:1 aufgeteilt und ins Lager eingezahlt.
25% der Prämie wird nach dem Verhältnis des eigenen Lands zum Syndikatsgesamtland verteilt.
25% nach Anzahl der erfolgreichen Spionageaktionen (der Schwierigkeitsgrad wird entsprechend< 1=einfach,2=mittel,3=schwer,4=sehr schwer gewichtet).
Die restlichen 50% schließlich nach erobortem Land / zerstörten Gebäuden / zerstörten Spionen (für die Gewichtung zählen jeweils 2 eroberte Land aus einem Eroberungsangriff, 1,75 zerstörte Gebäude bei einem Belagerungsangriffe bzw. 5 zerstörte Spione wie 1 erobertes Land aus einem Standardangriff)
Jeder einzelne Spieler erhält dann seinen Anteil am Wert dieser Ressourcen als Handelspunkte gutgeschrieben.
				<li><!--Wurde der Krieg innerhalb der ersten 36h gewonnen (dies ist nur durch die 15%-Regel möglich, s.o.) und b-->Besitzt der Verlierer ein Monument, wird dieses vom Gewinner erobert, sofern der Gewinner mindestens 6% Land netto erobert hat und noch kein Monument besitzt. Andernfalls wird es zerstört. (Wichtig: der Präsident muss dies allerdings einstellen, sonst wird das Monument zerstört!)
			</ul>
	</ul>
<li><b>Sonstiges</b><br>
	<ul><li>Während des Krieges können keine Spieler gekickt werden oder in das Syndikat wechseln.
	<li>Es können maximal 2 Kriege zur selben Zeit erklärt werden
	</ul>

<br><br>

<?
