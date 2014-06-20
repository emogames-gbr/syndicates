<?

include("inc/general.php");


$globals = assoc("select * from globals order by round desc limit 1");
$globals2 = assoc("select * from globals where round = ".($globals['round']-1));

$konzerndelete = assocs("select * from options_konzerndelete where time > ".$globals2['roundendtime']." ORDER BY time DESC");
$accountdelete = assocs("select * from options_accountdelete where time > ".$globals2['roundendtime']." and rid > 0 ORDER BY time DESC");
$resets = assocs("select * from options_reset where time > ".$globals2['roundendtime']." ORDER BY time DESC");



$ausgabe1 .= "<center><i>Folgende Konzerne haben sich gelöscht:</i></center><br>";

$evalCode = "
\$value = \"<a href=\\\"player_specific.php?ia=calc&search=\$value\\\" target=player_specific class=ver12s>\$value</a>\";
";
setSpecialBehaviour("syndicate", $evalCode);

$evalCode = "
\$value2 = htmlentities('|'.\$value);
\$value = \"<a href=\\\"player_specific.php?ia=calc&search=\$value2\\\" target=player_specific class=ver12s>\$value</a>\";
";
setSpecialBehaviour("user_id", $evalCode);

$evalCode = "
\$value = date(\"H:i:s, d. M. y\", \$value);
if (\$line[time] > get_day_time(time())-86400) \$value = \"<font color=red>\$value</font>\";
";
setSpecialBehaviour("time", $evalCode);
$ausgabe1 .= printAssocs($konzerndelete, "user_id, syndicate, rid, time");


$ausgabe2 .= "<center><i>Folgende Konzerne haben sich resetted:</i></center><br>";
$evalCode = "
\$value2 = htmlentities('§'.\$value);
\$data = assoc(\"select syndicate, rid from status where id = \$value\");

\$value = \$data[syndicate].\" (#\$data[rid])\";
\$value = \"<a href=\\\"player_specific.php?ia=calc&search=\$value2\\\" target=player_specific class=ver12s>\$value</a>\";
";
setSpecialBehaviour("user_id", $evalCode);

$ausgabe2 .= printAssocs($resets, "user_id, time");


$ausgabe_accountdelete  = "<center><i>Folgende <b>Accounts</b> haben sich gelöscht:</i></center><br>";
$ausgabe_accountdelete .= printAssocs($accountdelete, "username, syndicate, rid, time");



$ausgabe .= "<table width=100%><tr><td valign=top>$ausgabe_accountdelete<br>$ausgabe1</td><td valign=top>$ausgabe2</td></tr></table>";


echo "
<html>
<head>
	<title>Syndicates - Kürzliche Resets und Konzernlöschungen</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body><center>
Folgende Konzerne haben sich kürzlich resetted oder gelöscht<br><br>
$fehler
$successmeldung
$informationmeldung</center>
$ausgabe
</body>

</html>";







?>
