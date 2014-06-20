<?
set_time_limit(3000);

if (!$what): $what="getenergy"; endif;
if (!$sortings): $sortings = 50; endif;
$playerdata = assocs("select id, syndicate, rid from status", "id");
$data = assocs("select aid, result, offense, defense from spylogs where success=1 and action='$what' order by time desc");


foreach ($data as $ky => $vl)	{
	$temparray = explode(".", $vl[result]);
	$result[$vl[aid]] += implode("", $temparray);
	#$result[$vl[aid]] += preg_replace("/\./", "", $vl[result]);
	$offense[$vl[aid]] += $vl[offense];
	$defense[$vl[aid]] += $vl[defense];
	$count[$vl[aid]]++;
}

$counti = 0;
arsort($result);
	$ausgabe .= "<form action=index.php><input type=hidden name=action value=spystats><select size=4 name=what><option value=getmoney".($what == "getmoney" ? " selected":"").">Credits<option value=getmetal".($what == "getmetal" ? " selected":"").">Erz<option value=getsciencepoints".($what == "getsciencepoints" ? " selected":"").">Forschungspunkte<option value=getenergy".($what == "getenergy" ? " selected":"").">Energie</select><br><br>Anzahl Spieler <input type=text name=sortings value=$sortings size=3><br><br><input type=submit value=los></form><br><br><table cellpadding=4 cellspacing=0 border=1><tr><td width=50>#</td><td>Spieler</td><td>Ressourcen geklaut</td><td>Schnitt Offense</td><td>Schnitt Gegner Defense</td><td>Schnitt geklaut</td><td>Anzahl Aktionen</td></tr>";
foreach ($result as $ky => $vl)	{
$counti++;
	if ($counti <= $sortings)	{
	$ausgabe .= "<tr><td width=50>$counti</td><td>".$playerdata[$ky][syndicate]." (#".$playerdata[$ky][rid].")</td><td>".pointit($vl)."</td><td>".(round($offense[$ky]/$count[$ky]*10)/10)."</td><td>".(round($defense[$ky]/$count[$ky]*10)/10)."</td><td>".(pointit(round($vl/$count[$ky])))."</td><td>".($count[$ky])."</td></tr>";
	}
	else { break;}
}
$ausgabe .= "</table>";

?>
