<?

include("inc/general.php");



// Im Folgenden Block wird die Linkausgabe fürs Menü erarbeitet. Die Linkausgabe steht später in $linkausgabe
{
	$categories_to_build = array( "Adminstuff", "Checkstuff", "Statistikstuff");
	$count = array();
	foreach ($categories_to_build as $category) {
		foreach ($pages as $vl) {
			if ($vl['kategorie'] == $category) {
				if ($vl['privilege_level'] <= $pl && $vl['visible']) {
					$name = $vl['name'];
					$count[$category]++;
					$links[$category][] = "<a href=".$vl['dateiname']." class=ver10s target=main>$name</a>";
				}
			}
		}
		if ($cat == $category) $catfound = 1;
	}

	if (!$catfound) $cat = "";			// Wenn keine Kategorie ausgewählt ist, wird hier jetzt diejenige gewählt, die für den User sichtbar ist und am meisten Einträge hat
	arsort($count);
	if ($cat == "") $cat = array_shift(array_keys($count));

	// Linkausgabe vorbereiten
	$linkausgabe = "";
	foreach ($categories_to_build as $category) {
		$linkausgabe .= $count[$category] ? ($cat == $category ? "<b><u>$category</u></b><br>":"<b><a href=general_functions.php?cat=$category class=ver10s>$category</a></b><br>"):"";
	}
	$linkausgabe .= join("<br>", $links[$cat]);
}




$ausgabe = "
<table width=100% border=0><tr class=ver10s>
<td valign=top>
$linkausgabe
</td>
</tr></table>";





echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body>
$ausgabe
</body>

</html>";

?>

