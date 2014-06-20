<?

$case_ablage_top_param = array();
$case_ablage_main_param = array();

if ($action == "add" or $action == "del") {
	$case_ablage_main_param[] = "action=$action";
	$case_ablage_main_param[] = "case_id=$case_id";
	$case_ablage_main_param[] = "view=ablage";
	$case_ablage_top_param[] = "view=ablage";
} else {
	$case_ablage_top_param[] = "view=$view";
	$case_ablage_main_param[] = "view=$view";
}

?>



<html>

<head>
<title>Syndicates Adminpanel</title>

<FRAMESET ROWS="28,*" border=0>
	<FRAME SRC="case_ablage_top.php<? echo ($case_ablage_top_param ? "?".join("&", $case_ablage_top_param):""); ?>" NAME="case_ablage_top">
	<FRAME SRC="case_ablage_main.php<? echo ($case_ablage_main_param ? "?".join("&", $case_ablage_main_param):""); ?>" NAME="case_ablage_main">
</FRAMESET>
</head>

<BODY BGCOLOR="#000000" TEXT="#FFFF00" LINK="#00FF00" VLINK="#408080" ALINK="#808080">
        Enter HTML code and text here to be read by browsers that can't display frames
</BODY>

</html>

