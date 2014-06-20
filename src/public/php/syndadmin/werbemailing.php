<?

$startmailing = 0;

require("../subs.php");
connectdb();
$count = 0;
$time = time();
$betreff = "Runde 9 startet am Samstag!";
$i=0;
$userdata = assocs("select * from users where lastroundplayed >= 10");
//$userdata = assocs("select * from users where id = 2");

//$userdata = array( array ( 'vorname' => 'Nicolas', 'nachname' => 'Breitwieser', 'email' => 'bogul2000@gmx.de', 'password' => 'testen', 'id' => '11'));

foreach ($userdata as $user) {
	if (!$user[konzernid] && checkmail($user[email])) {
		if (!$user[vorname] || !$user[nachname]) {
			$hallostring = "Hallo,";
		}
		else {
			$hallostring = "Hallo ".$user[vorname]." ".$user[nachname].",";
		}
		$email = $user[email];
		$to = "$user[vorname] $user[nacnhame] <$user[email]>";


	$message ="

$hallostring

zu allererst die wohl wichtigste Neuigkeit:\n
Syndicates kann ab sofort wieder kostenlos die ganze Runde über gespielt werden!\nDer Probeaccount wurde diesbezüglich erweitert und unterliegt nun keiner zeitlichen Einschränkung mehr!\n

Nachdem die achte Syndicates-Runde vor wenigen Tagen erfolgreich beendet wurde, startet am Samstag den 07.08.04 die neunte Syndicates Runde.\n

Sie können sich ab sofort für die neue Runde anmelden um von Anfang an dabei zu sein!

Wir würden uns freuen, wenn wir Sie diese Runde wieder bei Syndicates begrüßen dürfen.


Das Syndicates Team
http://www.syndicates-online.de

		";

		if ($startmailing) {
			$i++;
			sendthemail($betreff,$message,$email,$to);
		}
		echo $email."\n";
		select("insert into mailed_start
 (userid,password,time,hallostring,email) values ($user[id],'$user[password]',$time,'$hallostring','$user[email]')");
 	$count++;
	} // wenn checkmail
}

echo "\n\n$count emails verschickt";
echo "$i mails verschickt";

?>

