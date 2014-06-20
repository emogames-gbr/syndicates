<?
require_once("../includes.php");
connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

$time = time();
$tag = date("d",$time);
$monat = date("m",$time);
$jahr = date("Y",$time);
$stunde = date("H",$time);
$minute = date("i",$time);
$sekunde = date("s",$time);



$back = system("uptime");
$pattern = "/load average:([^\n]*)/i";
preg_match($pattern,$back,$matches);
print_r($matches);


$loadstring = "$stunde:$minute:$sekunde - $matches[0]\n";




$single_loads = explode(" ",$matches[1]);

$clicks_last_minute = single("select count(*) from heaptable where clicktime >= $time - 60");
$users_online = single("select count(sessionid) from sessionids_actual where gueltig_bis > $time");


print_r ($single_loads);
$single_loads[1] = chopp($single_loads[1]);
select("insert into serverload (server,time,sload, clicks_last_minute, users_online) values ('".SERVERNAME."',$time,'$single_loads[1]', $clicks_last_minute, $users_online)" );


#####################################
?>
