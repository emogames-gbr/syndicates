<?

include("inc/general.php");
$self = "setmonument.php";
$ausgabe ="";

$formstart = "
<form method=get>
<input type=hidden name=action value=setmonument>
<input type=hidden name=ia value=finish>
";
$formend = "<input type=submit value=go></form>";


if ($ia == "finish") {
	$monument_id = floor($monument_id);
	$synd_id = floor($synd_id);
	select("update syndikate set artefakt_id = 0 where artefakt_id = $monument_id");
	if ($synd_id) {
		select("update syndikate set artefakt_id = $monument_id where synd_id = $synd_id");
	}
	s("Monument erfolgreich gesetzt");
}


$ausgabe .= "<center>Monument zuweisen. Das eingetragene Syndikat erhält das entsprechende Monument. Soll ein Monument zerstört werden, einfach als Syndikatsnummer n/a auswählen.<br><br></center>";

$monumente = assocs("select * from artefakte");
$synd_ids_monus = assocs("select synd_id, name, artefakt_id from syndikate where artefakt_id > 0", "artefakt_id");
$syndids = singles("select synd_id from syndikate order by synd_id asc");

$ausgabe .= "<table align=center width=50% cellpadding=2 cellspacing=0>";
foreach ($monumente as $vl) {
	$ausgabe .= "$formstart<input type=hidden name=monument_id value=$vl[artefakt_id]><tr><td>$vl[name]</td><td>
	<select name=synd_id><option value=0>n/a";
	foreach ($syndids as $vl2) {
		if ($synd_ids_monus[$vl[artefakt_id]][synd_id] == $vl2) $ausgabe .= "<option selected value=$vl2>$vl2";
		else $ausgabe .= "<option value=$vl2>$vl2";
	}
	$ausgabe .= "
	</td><td>".$synd_ids_monus[$vl[artefakt_id]][name]." (#".$synd_ids_monus[$vl[artefakt_id]][synd_id].")<td>$formend</td></tr>";
}
$ausgabe .= "</table>";

echo $ausgabe;





?>
