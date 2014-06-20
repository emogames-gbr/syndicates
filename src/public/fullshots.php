<? require_once("../includes.php");


?>
<html>
<head>
	<!--
	<script Language="JavaScript">
		function offset() {
			window.scrollTo(0,100)
		}
	</script>
	-->
</head>
<body onLoad="window.scrollTo(0,48);">
<?


$shotspath = "sshots";
if (isKsyndicates()) {
	$shotspath = "sshots/krawall_sshots";
}

if ($pid) {
	echo "<img border=0 onClick=\"window.close();\" src=\"".WWWDATA."$shotspath/$pid.jpg\"";
}
if ($url) {
	$url = htmlentities($url);
	echo "<img border=0 onClick=\"window.close();\" src=\"$url\"";
	
}

?>
</body>
</html>
