<?

require("../subs.php");
connectdb();
list($usec,$sec) = explode(" ",microtime());$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;mt_srand($bla);

function mykey() {
	$key = "";
	$length = 6;
	for ($i=0;$i<$length;$i++) {
		if ($i == 0) {
			$random = mt_rand(48,57);
		}
		elseif ($i == 1) {
			$random = mt_rand(65,90);
		}
		else {
			$random = mt_rand(97,122);
		}
		$key.= chr($random);
	} // For
	return $key;
}

$users = singles("select id from users");

foreach ($users as $idd) {
	$key = mykey();
	select("update users set password='$key' where id=$idd");
	unset($key);
}



?>
