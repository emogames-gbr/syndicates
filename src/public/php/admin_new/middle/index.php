<?

chdir("../");
include("inc/general.php");


	$ripf = "../../../data/syn_gpacks/60/";
	$onlinegif = "<img src=\"".$ripf."_online.gif\" border=0 align=\"absmiddle\">";
	$offlinegif = "<img src=\"".$ripf."_offline.gif\" border=0 align=\"absmiddle\">";
	# Inaktiv nach 7 Tagen nichtstun
	$inactivegif = "<img src=\"".$ripf."_lokal_inaktiv.gif\" border=0 align=\"absmiddle\">";
	# Global inaktiv nach 14 Tagen nichtstun
	$global_inactivegif = "<img src=\"".$ripf."_gl_inaktiv.gif\" border=0 align=\"absmiddle\">";
	# Hyperinaktiv nach 30 Tagen nichtstun
	$hyper_inactivegif = "<img src=\"".$ripf."_gl_inaktiv.gif\" border=0 align=\"absmiddle\"><img src=\"".$ripf."_gl_inaktiv.gif\" border=0 align=\"absmiddle\">";


// Im Folgenden Block wird die Linkausgabe f?rs Men? erarbeitet. Die Linkausgabe steht sp?ter in $linkausgabe
{
	/*
	$menuepunkte = array( "ablage" => "Ablage",  "history" => "History");


	if (!$view or !$menuepunkte[$view]) $view = "ablage"; // Standardview ist "ablage"

	$linkausgabe = array();
	foreach ($menuepunkte as $bezeichner => $name) {
		$linkausgabe[] = ($view == $bezeichner ? "<b><u>$name</u></b>":"<a href=index.php?view=$bezeichner class=ver10s target=player_ablage>$name</a>");
	}
	$linkausgabe = join(" - ", $linkausgabe);
*/
}



$privileged = assocs("select * from users where privilege_level > 0 order by id asc", "id");


$lines = "<tr><td></td><td><i>name</i></td><td><i>last click</i></td></tr>";
foreach ($privileged as $ky => $vl) {
  if ($vl['privilege_level'] != 3) { // Game-Master Supervisors führen keine Game-Master Tätigkeiten durch und brauchen daher nicht in der ist-online-list aufgeführt werden
	$diff = $switch = $minutes = 0;
	list($gueltig_bis, $lastklicktime) = row("select gueltig_bis, lastklicktime from admin_sessionids where user_id = ".$vl['id']." order by gueltig_bis desc limit 1");
	if ($gueltig_bis > $time) {
		$switch = 1;
		$diff = $time - $lastklicktime;
		$minutes = floor($diff / 60);
	}
	$lines .= "<tr><td>".getImagetype($lastklicktime, $gueltig_bis, $vl['username'])."</td><td>".$privileged[$ky]['username']."</td><td align=right>".($switch ? "-".$minutes."m" : "")."</td></tr>";
  }
}




function getImagetype($lastklicktime, $gueltig_bis, $username) {
	global $time, $onlinegif, $offlinegif, $inactivegif, $global_inactivegif, $hyper_inactivegif;
	

	if ($gueltig_bis > $time) {
		return $onlinegif;
	}	elseif ($username == "Bogul" or $username == "Scytale") {
		return $offlinegif;
	}	elseif ($lastklicktime + 30 * 86400 < $time) {
		return $hyper_inactivegif;
	} elseif ($lastklicktime + 14 * 86400 < $time) {
		return $global_inactivegif;
	} elseif ($lastklicktime + 7 * 86400 < $time) {
		return $inactivegif;
	} else {
		return $offlinegif;
	}
}


if (!$help) {
	$ausgabe = "
	<table width=100% cellpadding=0 cellspacing=0><tr><td>
	<table width=100% border=0 valign=top align=left cellpadding=0 cellspacing=0>
	$lines
	</table></td></tr>

	<tr><td><table width=100% valign=bottom align=center><tr><td align=center><br><a href=index.php?help=1 class=ver12s>Legende</a></td></tr></table>
	</td></tr></table>";
} else {
	$ausgabe .= "<b><i>Legende</i></b>
	<table width=100% cellpadding=0 cellspacing=0><tr><td>
	<table width=100% border=0 valign=top align=left cellpadding=0 cellspacing=0>
		<tr><td>$onlinegif</td><td>online</td></tr>
		<tr><td>$offlinegif</td><td>offline</td></tr>
		<tr><td>$inactivegif</td><td>letzter Login >7d</td></tr>
		<tr><td>$global_inactivegif</td><td>letzter Login >14d</td></tr>
		<tr><td>$hyper_inactivegif</td><td>Letzter Login >30d</td></tr>
	</table></td></tr>
	<tr><td><table width=100% valign=bottom align=center><tr><td align=center><br><a href=index.php class=ver12s>zurück</a></td></tr></table>
	</td></tr></table>";
}





echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"../style.css\" TYPE=\"text/css\">
</head>

<script language=\"JavaScript\">
function reeload() {
	location.reload();
}
</script>


<body onload=\"setTimeout('reeload()', 20000)\">
$ausgabe
</body>

</html>";

?>

