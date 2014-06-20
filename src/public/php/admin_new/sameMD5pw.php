<?php

include("inc/general.php");
$self = "index.php";

$users = assocs("SELECT * FROM users where konzernid != 0");
$hashtab = array();

$ausgabe = "<h1>Mehrfach genutzte Passwort-Hash:</h1><br><br>";

foreach($users as $user){
	$hashtab[$user{password}][count]++;
	$hashtab[$user{password}][hash] = $user{password};
	if(!$hashtab[$user{password}][users])
		$hashtab[$user{password}][users]=array();
	//echo $user{password}.":".$user[username]."<br>";
	array_push( $hashtab[$user{password}][users], $user );
}

foreach($hashtab as $hash){
	if($hash[count]>1){ // ".$hash[hash]."
		$ausgabe .= "Passwort-Hash 'aus sicherheitsgründen verdeckt' wird <b>".$hash[count]."</b> genutzt!<br><br>";
		$ausgabe .= "<table border=1><tr><td width=200><b>UserName:</b></td><td width=200><b>UserId:</b></td></tr>";
		$k = 0;
		foreach($hash[users] as $user){
			$ausgabe .= "<tr BGCOLOR='".( ($k++)%2 == 0 ? "#cccccc" : "white")."'>";
			$ausgabe .= "<td>".$user{username}."</td><td>".$user{emogames_user_id}."</td></tr>";
		}
		$ausgabe .= "</table><br><hr><br>";
	}
}

echo $ausgabe;

?>