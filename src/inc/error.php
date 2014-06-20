<?php

ob_start();
if ($code == 1) {print "Account nicht vorhanden";}
elseif ($code == 2) {print "Benutzername und/oder Passwort nicht eingegeben";}
elseif ($code == 3) {
	$tuser = htmlentities($tuser,ENT_QUOTES);
	echo "
	<br>
	Besitzen Sie schon einen <a href=$game[BETREIBER_portal_anmeldung]&ref_src=syndicates class=gelblink>BETREIBER-Account</a>? Syndicates ist ab Runde zwölf nur noch mit einem BETREIBER-Account spielbar.<br>
	Melden Sie sich einfach einen <a href=$game[BETREIBER_portal_anmeldung]&ref_src=syndicates class=gelblink>BETREIBER-Account an</a>.<br><br>
	Wenn Sie das Passwort für Ihren BETREIBER-Account vergessen haben, klicken Sie bitte <a href=\"http://BETREIBER.de/index.php?action=pwforgotten&ia=resend&user=$tuser\" class=\"gelblink\">hier</a>.<br><br>";
}
elseif ($code == 31) {
	printmailform();
}
elseif ($code == 4) {print "Dieser Account ist leider nicht mehr verfügbar";}
elseif ($code == 5) {print "Das Loginsystem konnte Ihnen leider keinen Account zuordnen. Bitte vergewissern Sie sich, dass ihr Browser Cookies akzeptiert. Wenn Cookies aktiviert sind und dieser Fehler trotzdem auftritt, loggen Sie sich bitte aus, schließen Sie ihren Browser und versuchen sie erneut, sich einzuloggen. Bei weiteren Problemen wenden Sie sich bitte an den BETREIBER Support unter support@DOMAIN.de.";}
elseif ($code == 6) {print "Ihr Sessionid ist abgelaufen, bitte loggen sie sich neu ein.";}
elseif ($code == 7) {print "In dem Syndikat, dem Sie beitreten wollen, ist kein Platz mehr vorhanden";}
elseif ($code == 8) {print "Du kannst dich mit deinem BETREIBER-Account nicht auf dem Basic-Server anmelden!";}
elseif ($code == 9) {print "Du bist nicht eingelogt!";}
elseif ($code == 10) {print "Die E-Mail-Adresse die zu deinem KOINS-Account gehört wird bereits verwendet. Du kannst deine BETREIBER-Accountdaten <a href=\"http://BETREIBER.de/index.php?action=pwforgotten\" class=\"gelblink\">hier</a> erfragen.";}
elseif ($code == 14) {print "Es ist bereits ein Konzern für diesen Account registriert! Dein KOINS Account wurde mit deinem BETREIBER-Account zusammengelegt. Du kannst dich entweder über KOINS oder BETREIBER einloggen.";}
elseif ($code==12345) {print "Die Anmeldung ist 120 Sekunden vor Rundenstart nicht möglich, da jetzt bereits die Spieler den Syndikaten zugewiesen werden. Versuchen Sie es bitte in 2 Minuten erneut.";}
elseif ($code==15793) {print "Sie sind wegen wiederholtem Regelverstoß permanent von der Teilnahme an Syndicates ausgeschlossen.";}
elseif ($code==65957) {print "Sie haben sich bereits angemeldet.";}
elseif ($code==66666) {print "Sie haben innerhalb von 5 Sekunden mindestens zweimal geklickt. Es ist vorübergehend nicht erlaubt, innerhalb von 5 Sekunden zwei oder mehrmals zu klicken. Sie wurden daher ausgeloggt, und müssen 2 Minuten warten, bevor Sie sich wieder einloggen können."; }
elseif ($code==76453) {print "Sie müssen den beim Login angezeigten Code eingeben! Sollte dies wiederholt nicht funktionieren, leeren Sie bitte Ihren Browsercache.";}
elseif ($code==78453) {print "Ihre Session-ID ist abgelaufen. Bitte loggen Sie sich erneut ein, um weiterspielen zu können.";}
elseif ($code==83647) {print "Pro Tag dürfen nur zwei Konzerne angemeldet werden.";}
elseif ($code==84561) {print "Sie können sich nicht einloggen, solange Sie keinen Konzern angelegt haben!";}
//Account gebannt
elseif ($code==84562) {
    $till = single("select banned from users where id='$userid'");
    $bis = date("d.m.y u\m H:i \U\h\\r",$till);
    if ($bis) {
        print "Ihr Syndicates Account ist noch bis zum $bis vom Spiel ausgeschlossen.<br>Dies kann folgende Gründe haben:<br><br><ol type=1><li>Sie haben innerhalb von 5 Sekunden zwei mal geklickt und wurden deshalb für 2 Minuten aus dem Spiel ausgeschlossen. <!--Um das Update möglichst schnell ausführen zu können benötigt der Server freie Kapazitäten. Durch das viele Klicken werden diese Kapazitäten jedoch eingeschränkt, weshalb diese Regel leider notwendig ist.--><br><br><li>Eine andere, weniger wahrscheinliche Möglichkeit, wieso Sie vom Spiel ausgeschlossen wurden besteht darin, dass Sie gegen die Nutzungsbedingungen verstoßen haben. Die häufigsten Verstöße, die zu diesem Ausschluss führen, bestehen in Beleidigungen anderer Mitspieler. Den genauen Grund für Ihren Ausschluss haben Sie in diesem Fall in einer an Sie verschickten e-Mail mitgeteilt bekommen.</ol>";
    }
    else {
        print "Ihr Syndicates Account ist momentan wegen Verstoß gegen die Nutzungsbedingungen vom Spiel ausgeschlossen.";
    }
}

// Payment: 9xxxx
// übergibt immer $errorstatus

elseif($code == 90001) {
	print "<br>Login nicht möglich, ihr Abonnement ist nicht bezahlt: '$errorstatus.'. Sie können Syndicates unbegrenzt lange kostenlos testen, möchten Sie in den Genuss sämtlicher Features kommen, müssen Sie ein <a href=\"index.php?action=abo\" class=gelblink>Abonnement</a> abschließen, wenn Sie weiterspielen möchten.";
}



// Inner stuff

if ($ia === "sendmail" && checkmail($mail)) {
	$userdata = assoc("select * from users where email='$mail'");
	if (!$userdata) {
		echo "Es ist kein Nutzer mit dieser E-Mail-Adresse im System bekannt.<br>";
		printmailform();
	}
	else {
		$betreff = "Passwortanfrage";
		$message = "Hallo ".$userdata[username].",\n\nSie erhalten, wie angefordert, mit dieser E-Mail Ihre Zugangsdaten.\nWir möchten Sie darum bitten, diese Mail aus Sicherheitsgründen unverzüglich zu löschen.\n\n\nIhre Syndicates Zugangsdaten lauten:\n\nBenutzername: ".$userdata[username]."\nPasswort: ".$userdata[password]."\n\nViel Spaß weiterhin bei Syndicates wünscht Ihnen Ihr Syndicates Admin-Team.";
		$tmail = $mail;
		$to = $userdata[vorname]." ".$userdata[nachname];
		sendthemail($betreff,$message,$tmail,$to);
		echo "<br>Ihr Spielpasswort wurde an Ihre bei Syndicates angegebene E-Mail-Adresse versandt.";
	}
}
elseif($ia === "sendmail") {
	echo "Bitte geben sie eine gültige E-Mail-Adresse ein.<br>
	";
	printmailform();
}




function printmailform() {
echo "<br><br>Wenn Sie das Passwort für Ihren alten Syndicates Account vergessen vergessen haben, können Sie es sich an Ihre bei Syndicates angegebene Adresse schicken lassen. Sie können Ihren Syndicates Account anschließend mit einem neuen BETREIBER-Account verknüpften, um Ihre Statistiken zu behalten.<br><br><form action=\"index.php\" method=post> E-Mail-Adresse: <input name=mail value=\"\"><input name=ia value=sendmail type=hidden><input name=action value=error type=hidden> <input type=\"submit\" value=\"abschicken\"> </form>"; 
}

// fehler in db eintragen
if (!$time) {$time = time();}
if ($time && $code) {
	select("insert into errors (error_id,time) values ($code,$time)");
}


$error_ausgabe = ob_get_contents();
ob_clean();


// Danach std-seite anzeigen
require_once(INC."main.php");

?>

