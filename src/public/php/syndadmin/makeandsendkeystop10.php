<?


require("../subs.php");
connectdb();
$time = time();
mt_srand($time);

// Keys generieren, wenn noch nicht vorhanden
$number = 10;
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
$lastround = single("select round from globals order by round desc limit 1");
$lastround--;
echo "Benachrichtigte User:\n\n";
$winnerids = singles("select user_id from stats where round=$lastround order by lastnetworth desc limit 10");
$i=0;
foreach ($tkeys as $key) {
	$tempuser = assoc("select * from users where id=".$winnerids[$i]."");
	//$tempuser[email] ="admin@DOMAIN.ch";
	$username = $tempuser[username];

	$message = "Hallo $username,

	herzlichen Glueckwunsch!

	Du hast eine Syndicates Gratisrunde gewonnen.

	Dein persönlicher Schlüssel lautet:
	`$key`

	Schöne Grüße und viel Spaß beim Spielen, \n\nADMIN ";
	$betreff = "Gratisrunde gewonnen!";
	$email = $tempuser[email];
	if ($tempuser[vorname] or $tempuser[nachname]) { $to = "$tempuser[vorname] $tempuser[nachname]";}
 	sendthemail($betreff,$message,$email,$to);

	echo "$tempuser[username]\n";
	$i++;


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
