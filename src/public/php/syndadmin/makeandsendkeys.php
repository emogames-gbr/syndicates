<?


require("../subs.php");
connectdb();
$time = time();
mt_srand($time);

// Keys generieren, wenn noch nicht vorhanden
$number = 2;
$stmt = "insert into abo_keys (abo_key,abo_id,round,time) values ";
$tkeys = array();
for ($i = 0; $i < $number; $i++) {
	$tkex = 1;
	while($tkex || in_array($tkey,$tkeys)) {
		$tkey = createabokey();
		$tkex = single("select abo_key from abo_keys where abo_key='$tkey'");
	}
	$tkeys[] = $tkey;
	$stmt.="('$tkey',1,127,$time),";
}
$stmt=chopp($stmt);
select($stmt);




// Schl?ssel an user versenden
$userstosend = array();
$maxid = single("select id from users order by id desc limit 1");
$lastround = single("select round from globals order by round desc limit 1");
echo "Benachrichtigte User:\n\n";
foreach ($tkeys as $key) {
	$true = false;
	while (!$true)  {
		$random = mt_rand(0,$maxid);

		$active = single("select lastroundplayed from users where id=$random");
		if (!($active-$active > 1) || !in_array($random,$userstosend)) {
			$userstosend[] = $random;
			$true = true;
		}
	}
	$tempuser = assoc("select * from users where id=$random");
	$username = $tempuser[username];

	$message = "Hallo $username,

	herzlichen Glueckwunsch!

	Du hast bei der Verlosung eine der 10 Gratisrunden gewonnen.

	Dein persönlicher Schlüssel lautet:
	`$key`

	Schöne Grüße und viel Spaß beim Spielen, \n\nNicolas Breitwieser";
	$betreff = "Gratisrunde gewonnen!";
	$email = $tempuser[email];
	if ($tempuser[vorname] or $tempuser[nachname]) { $to = "$tempuser[vorname] $tempuser[nachname]";}
 	sendthemail($betreff,$message,$email,$to);

	echo "$tempuser[username]\n";


}



$keys = assocs("select * from abo_keys where abo_id=1");

foreach ($keys as $value) {
	$instring.="'$value[abo_key]',";
}
$instring=chopp($instring);
$usedkeys = singles("select upper(abo_key) from abo_keys where abo_id=1");
$i=0;
foreach ($usedkeys as $temp) {
	if ($i % 20 == 0) {
		echo "i:".$i."\n";
	}
	$i++;
	echo "$temp \n";
}

function createabokey() {
	return createkey(0,16);
}





?>
