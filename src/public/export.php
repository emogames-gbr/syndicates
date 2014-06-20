<?

ob_start();
require_once("../includes.php");
require_once(LIB."picturemodule.php"); // F?r Logincode
connectdb();



$username = mysql_real_escape_string($username);
$password = mysql_real_escape_string($password);

$globals = getglobals();
$game = assoc("select * from game limit 1");
###
###	User checken
###
$md5password = substr(md5($password),0,20);
$substrpw = substr($password,0,20);
$userok = single("select id from users where username='".$username."' and (password='".$md5password."' or password='$substrpw')");
$userdata = assoc("select * from users where username='$username'");

if (!$userok) {
	echo "Invalid authentification data!";
	exit(1);	
}

###############################


if ($globals[roundstatus] != 1) {
	echo "Die aktuelle Runde ist entweder beendet oder hat noch nicht gestartet!";
	exit(1);
}

############################################
###	Anzeige des Marketfiles
############################################





###
###
###

if ($action == "market") {
	header("Content-type: text/xml");

	$db = "syndicates";
	if (isBasicServer($game)) {
		$db = "syndicates_basic";
	}
	$filename = DATA."published/".$db."_market.xml";
	
	$data = file_get_contents($filename);
	/* echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"; */
	echo $data;


}
else {
	echo "Invalid action given!";
	exit(1);
}





function getglobals() {
		$result = assoc("select * from globals order by round desc limit 1");
return $result;
}


?>