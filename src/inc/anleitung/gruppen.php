?>

<ol>
Während der Anmeldephase können Spieler Gruppen bilden.<br>Es können bis zu <? echo MAX_USERS_A_GROUP; ?> Spieler einer Gruppe angehören.
Spieler die bei Rundenstart in einer Gruppe sind, werden gemeinsam einem Syndikat zugeteilt, können auf
die Weise also zusammen Spielen.<br>
Solange die Runde noch nicht gestartet ist, kann man jederzeit einer Gruppe beitreten oder diese wieder verlassen.
Um einer Gruppe beizutreten ist die Kenntnis der Gruppennummer sowie des Gruppenpassworts nötig.
<br>
Der Gruppenadministrator kann eine Gruppe schließen. <i>[nur Classic-Server]</i> Einer geschlossenen Gruppe,
die mindestens <? echo USERS_NEEDED_FOR_CLOSED_GROUP; ?> Spieler hat, wird bei Rundenstart ein eigenes Syndikat zugewiesen.
<br>
Der Gruppenadministrator kann ferner Spieler aus der Gruppe ausschließen und das Gruppenpasswort ändern.<br>
Verlässt der Gruppenadministator die Gruppe, wird der bestimmte Nachfolger zum Gruppenadministrator.
Ist der Gruppenadministrator der letzter Spieler der Gruppe, wird die Gruppe gelöscht, wenn er sie verlässt.
<br>
Jeder Gruppe steht ein eigenes Forum zur Verfügung, in dem man sich vor Rundenstart schonmal unterhalten und letzte Dinge klären kann.
Die Einträge sind nach Anfang der Runde nicht mehr verfügbar, es sei denn die Gruppe war geschlossen und hat ihr eigenes Syndikat erhalten.
</ol>

<?
