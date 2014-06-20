<?

chdir("../");
include("inc/general.php");



// Im Folgenden Block wird die Linkausgabe f?rs Men? erarbeitet. Die Linkausgabe steht sp?ter in $linkausgabe
{
	$menuepunkte = array( "ablage" => "Ablage",  "history" => "History");


	if (!$view or !$menuepunkte[$view]) $view = "ablage"; // Standardview ist "ablage"

	$linkausgabe = array();
	foreach ($menuepunkte as $bezeichner => $name) {
		$linkausgabe[] = ($view == $bezeichner ? "<b><u>$name</u></b>":"<a href=index.php?view=$bezeichner class=ver11s target=player_ablage>$name</a>");
	}
	$linkausgabe = join(" - ", $linkausgabe);
}




$ausgabe = "
<table width=100% border=0 valign=top align=left cellpadding=0 cellspacing=0>
<tr class=ver11s>
<td valign=top>
$linkausgabe
</td>
</tr></table>";





echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"../style.css\" TYPE=\"text/css\">
</head>

<body>
$ausgabe
</body>

</html>";

?>

