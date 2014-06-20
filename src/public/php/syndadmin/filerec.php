<?php
/*
require("php/subs.php");
connectdb();
$base = "/srv/www/htdocs/synloc/htdocs";

getfiles($base);

$dirs = array();
// Rekursiver dateiabstieg, ziemlich cool
function getfiles ($a) {
	global $files;
	$dirs = array();
	$d = opendir($a);
	while($entry = readdir($d)) {
		if (is_dir (($a."/".$entry)) && $entry != "." && $entry != ".." && $entry != "" && $entry != "codes" && $entry != "phpadmin") {
			$dirs[] = $entry;
		}
		elseif ($entry != "." && $entry != ".." && $entry != "") {
			if (preg_match("/.html|.htm|.php|.txt/",$entry)) {
				$files[] = $a."/".$entry;
			}
		}
	}
	while (is_array($dirs) && $tdir = array_pop($dirs)) {
		$get = $a."/".$tdir;
		echo "$get<br>";
		getfiles($get);
	}
}


pvar($files);

foreach ($files as $temp) {
	$stuff = file_get_contents("$temp");
	$stuff = preg_replace("/K-/","",$stuff);
	//system("rm anleitung/$stuff");
	$handle = fopen("$temp", 'w');
	fwrite($handle, $stuff);
	fclose($handle);
	unset($handle);
	//echo htmlentities($stuff);
}

//phpinfo()

/*


*/

/*
	$temp[description] = preg_replace("/K-/","",$temp[description]);
	select("update anleitung set description='$temp[description]' where anleitung_id=$temp[anleitung_id]");
*/

?>
