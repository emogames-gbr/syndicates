<?

define("DB_HOST","localhost");     // TODO - Replace
define("DB_USER","dbUserName");		// TODO - Replace
define("DB_PASSWORD","dbPassword");	// TODO - Replace

// Folgende Zeilen nur im Dev-Betrieb wegen variabler Nutzerkennung der Subdomains:
$dir = getcwd();
preg_match("/^\/srv\/([^\/]+)\//", $dir, $tempsave);  
																		// und speichert ihn in $tempsave[1]
if ($tempsave[1] == "dev") $tempsave[1] = "";
else $tempsave[1] .= ".";
DEFINE("DOMAIN","syndicates.".$tempsave[1]."webnotes.de");


if (!$DATABASE && !$_SERVER) {
	$DATABASE = "dev_syndicates";
}
elseif ($_SERVER['HTTP_HOST']) {
	if (substr($_SERVER['HTTP_HOST'],0,5) == "basic" 
			|| substr($_SERVER['HTTP_HOST'],0,12) == "k-syndicates"
			|| ereg("k-syndicates.de",$_SERVER['HTTP_HOST'])
		) {
		$DATABASE = "dev_syndicates_basic";
	}
	else {
		$DATABASE = "dev_syndicates";
	}
}
else {
	$DATABASE = "dev_syndicates";
}


		
		
function connectdb() {
	global $DATABASE; 
	$dbh = mysql_connect (DB_HOST,DB_USER,DB_PASSWORD) or die ("keine Verbindung m?glich");
	mysql_select_db ($DATABASE,$dbh);
}
?>
