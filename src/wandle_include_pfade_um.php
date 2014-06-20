<?

include("includes.php");

$scharf = 0;

if ($scharf) {
	echo "\n\nGo on ....\n\n";
	rekursion("public", 1);
	rekursion("inc", 1);
	rekursion("lib", 1);
	rekursion("config", 1);
	rekursion("crons", 1);
	check("./includes.php", "includes.php", 0);


}
else {
	echo "Skript ist nicht scharf. Scharf machen nur auf Live-Version. NIEMALS auf Testversion!\n";
}

	function rekursion($dirname, $level) {
		//echo "dirname $dirname, level: $level\n\n";
		$handle = opendir("$dirname");
		$punkt = readdir($handle);
		$punktpunkt = readdir($handle);
		while ($filename = readdir($handle)) {
			if (is_dir($dirname."/".$filename)) {
				#echo $level."DIRECTORY: $filename\n";
				rekursion($dirname."/".$filename, $level+1);
				#echo "\n";
			} else {
				check($dirname."/".$filename, $filename, $level);
			}
		}
	}

	function check($filename, $filenameraw, $level) {
		$paths = array("INC", "CONFIG", "LIB", "CRONS", "LOGS");

		$code = '';
		$handle = fopen($filename, 'r');
		$rewrite = 0;
		while ($line = fgets($handle, 10240)) {

			$line_before = $line;
			if (preg_match("/(require|include)/", $line)) {
				foreach ($paths as $pathname) {
					if (preg_match("/$pathname/", $line)) {
						echo "$level $filename\n";
						$line = preg_replace("/
							\(
								\s*?
								".$pathname."
								\s*?
								\.
								\s*?
								(\"|')
								([^\"']*)
								(\"|')
								\s*?
							\)
								/x", "(\"".constant($pathname)."$2\")", $line);
						echo "line before: $line_before";
						echo "line after: $line\n";
						if ($line != $line_before) $rewrite = 1;

					}
				}
			}
			$code .= $line;
		}

		if ($rewrite) {
			$handle2 = fopen($filename, "w") or die("Can't open File $filename for writing purpose\n");
			fwrite($handle2, $code) or die("Can't write on File $filename\n");
			fclose($handle2);
		}
		fclose($handle);
	}


?>

