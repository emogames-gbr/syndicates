<?

###
###	CONFIG
###
### Dateien bestimmen, die eingebunden werden sollen (Achtung, nur libfiles!)
###	 !!Config Files werden automatisch eingebunden!!
###################################################################




$dir = getcwd();

if (preg_match("/^\/srv\/dev/", $dir)) {
	DEFINE('PROJECT_DIR',"/srv/dev/syndicates/");
	DEFINE('PROJECT_WWW',"http://syndicates-dev.DOMAIN.de/");
}



########################
///// CONFIG ENDE /////
########################


// Pfade der anderen Verzeichnisse bestimmen
define('INC',PROJECT_DIR."inc/");
define('CONFIG',PROJECT_DIR."config/");
define('LIB',PROJECT_DIR."lib/");
define('CRONS',PROJECT_DIR."crons/");
define('LOGS',PROJECT_DIR."logs/");

define('PUB',PROJECT_DIR."public/");
define('DATA',PROJECT_DIR."data/");

define('WWWPUB',PROJECT_WWW."");
define('WWWDATA',PROJECT_WWW."data/");
define('TEMPLATES',PROJECT_WWW."templatesystem/templates/");
define('TEMPLATESYSTEM',PROJECT_DIR."templatesystem/");
define('OLD_TEMPLATES',PROJECT_DIR."templatesystem/templates/old/");

define ('WIKI','LINK_TO_WIKI');


########
########	Lib Dateien Include
########

require_once(LIB."subs.php");
require_once(LIB."interface_includes.php");
require_once(LIB."mod_interface.php");
require_once(LIB."mod_referrer.php");



########
########	Config Dateien Include
########

require_once(CONFIG."xtendsql.php");
require_once(CONFIG."connectdb.php");


require_once(LIB."facebook.php");
require_once(LIB."twitter.php"); // Die Twitter Klasse, basierend auf OAauth

define('IS_MOBILE', is_mobile());


?>
