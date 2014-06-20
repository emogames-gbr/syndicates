<?

include("inc/general.php");
$globals = assoc("select * from globals order by round desc limit 1");

define(SHOW_BELOW_STANDARD, 42);
define(SHOW_BELOW_MAX, 50);
define(WARN_BELOW_STANDARD, 40);
define(SEARCHLEFT_STANDARD, 2);
define(SEARCHRIGHT_STANDARD, 0);

$searchleft = floor($searchleft);
if (!$searchleft || $searchleft < 0) $searchleft = SEARCHLEFT_STANDARD;

$searchright = floor($searchright);
if (!$searchright || $searchright < 0) $searchright = SEARCHRIGHT_STANDARD;
if ($searchleft <= $searchright) $searchleft = $searchright + 1;
$searchframe_leftbarrier = get_day_time(time()) + 86400 - $searchleft * 86400;
$searchframe_rightbarrier = get_day_time(time()) + 86400 - $searchright * 86400;


$showbelow = floor($showbelow);
if (!$showbelow) $showbelow = SHOW_BELOW_STANDARD;
if ($showbelow > SHOW_BELOW_MAX) $showbelow = SHOW_BELOW_MAX;
$warnbelow = floor($warnbelow);
if (!$warnbelow) $warnbelow = WARN_BELOW_STANDARD;




$marketlogs = assocs("select * from marketlogs where price100percentage < ".$showbelow." and price100percentage > 0 and time >= $searchframe_leftbarrier and time < $searchframe_rightbarrier ORDER BY time DESC");


$konzernids = array();
foreach ($marketlogs as $vl) {
  if (!$konzernids[$vl['user_id']]) $konzernids[$vl['user_id']] = 1;
  if (!$konzernids[$vl['owner_id']]) $konzernids[$vl['owner_id']] = 1;
}

if ($konzernids) {
  $konzerndaten = assocs("select CONCAT(syndicate,' (#',rid,')') as name, race, id from status where id in (".join(",", array_keys($konzernids)).")", "id");
}

$product = changetype($value[type],$value[prod_id]); $product = $product[product];
if ($value[owner_id] == $konid) {
		$output[$value[time]] = "<font color=red><b>Market Sold: </b></font>".pointit($value[number])." ".($product)." an <a href=\"$href&konid=".$players[$value[user_id]][id]."#$value[time]\">".$players[$value[user_id]][syndicate]." (#".$players[$value[user_id]][rid].")</a> verkauft (VP: ".$value[price]."/".$value[price100]." = ".$value[price100percentage]."%;)";
}
if ($value[user_id] == $konid) {
	if ($value[action] == "back") {
		$output[$value[time]] = "<font color=red><b>Market Back: </b></font>".pointit($value[number])." ".($product)." zurückgenommen";
	}
	if ($value[action] == "buy") {
		$output[$value[time]] = "<font color=red><b>Market Buy: </b></font>".pointit($value[number])." ".($product)." von <a href=\"$href&konid=".$players[$value[owner_id]][id]."#$value[time]\">".$players[$value[owner_id]][syndicate]." (#".$players[$value[owner_id]][rid].")</a> gekauft (KP: ".$value[price]."/".$value[price100]." = ".$value[price100percentage]."%;)";
	}
	if ($value[action] == "sell") {
		$output[$value[time]] = "<font color=red><b>Market Input: </b></font>".pointit($value[number])." ".($product)." eingestellt (VP: <b>".$value[price]."</b>/".$value[price100]." = <b>".$value[price100percentage]."%</b>;".(($value[price100percentage] > 0 && $value[price100percentage] < 0.42) ? "<font color=red><b> < 42% WARNING!</b></font>;":"")." nmeo: ".$value[pricenextmoreexpensiveoffer].")";
	}
}
			unset($product);

$eval_user_id = "
global \$konzerndaten;
\$value2 = htmlentities('§'.\$value);
\$value = \"<a href=\\\"player_specific.php?ia=calc&search=\$value2\\\" target=player_specific class=ver12s>\".\$konzerndaten[\$value]['name'].\"</a>\";
if (\$line[action] == \"buy\") \$append = \" <font color=blue>BUYS</font>\";
\$value .= \$append;
";
setSpecialBehaviour("user_id", $eval_user_id);

$eval_owner_id = "
global \$konzerndaten;
if (\$line[action] == \"back\") \$alternative = \" <font color=green>BACK</font>\";
if (\$line[action] == \"sell\") \$alternative = \" <font color=green>INPUT</font>\";
if (!\$alternative) {
  \$value2 = htmlentities('§'.\$value);
  \$alternative = \"<a href=\\\"player_specific.php?ia=calc&search=\$value2\\\" target=player_specific class=ver12s>\".\$konzerndaten[\$value]['name'].\"</a>\";
  \$alternative = \"<font color=orange>FROM</font> \".\$alternative;
}
\$value = \$alternative;

";
setSpecialBehaviour("owner_id", $eval_owner_id);


$eval_time = "
\$value = date(\"H:i:s, d. M. y\", \$value);
if (\$line[time] > get_day_time(time())-86400) \$value = \"<font color=red>\$value</font>\";
";
setSpecialBehaviour("time", $eval_time);


$eval_price = "
\$value = pointit(\$value).\" / \".pointit(\$line[price100]).\" = \".colorPercentage(\$line[price100percentage]).\"%\";
";
setSpecialBehaviour("price", $eval_price);
function colorPercentage($perc) {
  global $showbelow, $warnbelow;
  if ($perc < $warnbelow) return "<font color=red size=5><b>$perc</b></font>";
  else return $perc;
}


$races = singles("select race from races");
foreach ($races as $race) {
  $mildata[$race] = assocs("select type, name from military_unit_settings where race='".$race."'", "type");
}

$eval_prod_id = "
global \$konzerndaten;
global \$mildata;
\$product = changetype(\$line[type],\$value); 
\$value = \$product[product];
if (\$line[type] == 'mil') \$value = \$mildata[\$konzerndaten[\$line['user_id']]['race']][\$value]['name'];

";
setSpecialBehaviour("prod_id", $eval_prod_id);


// AB HIER NUR NOCH KILL CONDITIONS

$eval_kill_patriots = "
if (\$line[type] == 'mil' && \$line[prod_id] == 19) unset(\$line);
";
if (!$withpatriots)
setKillLineCondition($eval_kill_patriots);

$eval_kill_carrier = "
if (\$line[type] == 'mil' && \$line[prod_id] == 24) unset(\$line);
";
if (!$withcarrier)
setKillLineCondition($eval_kill_carrier);

$eval_kill_input_only = "
if (\$line[owner_id]) unset(\$line);
";
if (!$show)
setKillLineCondition($eval_kill_input_only);

$ausgabe .= printAssocs($marketlogs, 'time, user_id as WER, number, prod_id as ARTIKEL, owner_id as KAUFT VON, price , pricenextmoreexpensiveoffer as pnmeo');

echo "
<html>
<head>
	<title>Syndicates - Global Market checken</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body><center>

Verkäufe, die knapp an der Grenze oder sogar darunter sind:<br><br>
<table border=1><tr><td colspan=2 align=center><b>Anzeige-Fenster (in Tagen)</b></td></tr>
<form method=post>
<input type=hidden name=withcarrier value=$withcarrier>
<input type=hidden name=withpatriots value=$withpatriots>
<input type=hidden name=show value=$show>
<input type=hidden name=showbelow value=$showbelow>
<input type=hidden name=warnbelow value=$warnbelow>
<tr><td>Alles ab</td><td><input type=text name=searchleft value=$searchleft size=3> Tage vor heute 23:59:59</td></tr>
<tr><td>aber nichts jünger als </td><td><input type=text name=searchright value=$searchright size=3> Tage vor heute 23:59:59</td></tr>

<tr><td colspan=2 align=center><b>Warnwerte einstellen (in %)</b></td></tr>
<tr><td>Anzeigen bei < </td><td><input type=text name=showbelow value=$showbelow size=3>% des Einheitenwertes</td></tr>
<tr><td>Warnen bei < </td><td><input type=text name=warnbelow value=$warnbelow size=3>% des Einheitenwertes</td></tr>
<tr><td colspan=2 align=center>
<input type=submit value=los>
</td></tr>
</form>
</table>
<br><br>
<a href=checkGlobalMarket.php?showbelow=$showbelow&warnbelow=$warnbelow&searchleft=$searchleft&searchright=$searchright&withcarrier=$withcarrier&withpatriots=$withpatriots&show=".(abs($show-1))." class=ver12s>".(!$show ? "Auch Käufe anzeigen" : "Nur INPUT (eingestellte Waren) anzeigen")."</a>
<br>
<a href=checkGlobalMarket.php?showbelow=$showbelow&warnbelow=$warnbelow&searchleft=$searchleft&searchright=$searchright&show=$show&withcarrier=$withcarrier&withpatriots=".(abs($withpatriots-1))." class=ver12s>Patriots ".($withpatriots ? "verbergen":"anzeigen")."</a>
&nbsp;&nbsp;&nbsp :::: &nbsp;&nbsp;&nbsp;
<a href=checkGlobalMarket.php?showbelow=$showbelow&warnbelow=$warnbelow&searchleft=$searchleft&searchright=$searchright&show=$show&withpatriots=$withpatriots&withcarrier=".(abs($withcarrier-1))." class=ver12s>Carrier ".($withcarrier ? "verbergen":"anzeigen")."</a>
<br><br>
$fehler
$successmeldung
$informationmeldung</center>
$ausgabe

<br><br>
<u>Legende:</u>
<ol>
<li>Rot geschriebenes Datum bedeutet, dass es sich um heute oder um gestern handelt.<br>
<li>Die Spalte pnmeo steht für \"price_next_more_expensive_offer\", also der Preis des nächst teureren Angebots,
welches am Markt zum Zeitpunkt des Einstellens des Angebots sichtbar war. Um diesen Wert nicht falsch interpretieren zu können, kann es sich auch um ein gleich teures Angebot handeln (daher wäre eigentlich \"price_next_more_expensive_or_equally_expensive_offer\" angebrachter, aber das kann sich ja kein Mensch merken ...)
</ol>
</body>

</html>";







?>
