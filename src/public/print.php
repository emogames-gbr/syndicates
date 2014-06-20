<?


$aid=(int)$aid;
require("../includes.php");
connectdb();
require_once(INC."ingame/globalvars.php");

$globals = assoc("select * from globals order by round desc limit 1");
// Hitstats extern updaten
$what = "print";
$time = time();
$today = date("d.m.Y",$time);
$exists = mysql_query("select date from hitstats_extern where date ='$today'");
$exists = mysql_fetch_row($exists);
$exists = $exists[0];
if (!$exists) {
    mysql_query("insert into hitstats_extern (date,$what) values('$today','1')");
}
if ($exists) {
    mysql_query("update hitstats_extern set $what=$what+1 where date='$today'");
}


if ($aid) {
	$contents=assocs("select * from anleitung where anleitung_id=$aid and visible=1");
}
else {
	$contents=assocs("select * from anleitung order by kategorie asc,showposition asc");
	echo "
		<center><h1>Syndicates - Die Wirtschaftssimulation<br>Handbuch</h1>
		<br><br>
	<h2>&copy; BETREIBER YEAR</h2>
		<br><br>
	
	</center>
	";
}
$i=1;
foreach ($contents as $content) {
	$content[text] = fgc(INC."anleitung/".$content[filename]);
	
	echo "
	<br><br><br>
	<center><h2><u>".(!$aid ? "$i. ": "")."$content[headline]</u></h2></center><br><br>";
	eval($content[text]);
	$i++;
	
	
}
if ($aid) {
	echo"<br><br><br>Autor: ".$content[author]."<br><br>http://DOMAIN.de - by BETREIBER";
}



function fgc($path) {
	$HANDLE = fopen($path,r);
	while ($LINE = fgets($HANDLE,1024)) {
		$back .=$LINE;
	}
	fclose($HANDLE);
	return $back;
}

?>
