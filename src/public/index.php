<?
session_start();
if($_GET['start'] == 'new'){
	$_SESSION['start'] = 'new';
}
if($_GET['start'] == 'desktop'){
	setcookie('deaktivate_mobile', true, time()+60*60*24*30); // 30 Tage gültig
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);exit(1);
}
else if($_GET['start'] == 'mobile'){
	setcookie('deaktivate_mobile', false, -1); // 30 Tage gültig
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);exit(1);
}
	
#############################################
#											#
# Content: Syndicates Outgame Index file    #
# Copyright: EmoGames Productions   		#
# 2002,2003,2004							#
# Author: Jannis Breitwieser				#
# Last updated: 180204		        		#
#                                  			#
#############################################

define (SHOW_MULTISERVER,0); // Gibt an, ob Mehrserverbetrieb angezeigt werden soll (z.b. bei anmeldung)




ob_start();
require_once("../includes.php");
require_once(LIB."picturemodule.php"); // F?r Logincode
require_once(LIB."mod_login.php"); // F?r Logincode
require_once(INC."style.php");
require_once(INC."ingame/globalvars.php");

connectdb();
if (isKsyndicates() && $cmd == "logout") $action ="logout";

$SYNREF = $_SERVER[SERVER_NAME].$_SERVER[REQUEST_URI];
dontcache();
$SERVER_NAME = getenv(SERVER_NAME);
$SCRIPT_NAME = getenv(SCRIPT_NAME);
$error_old = array();

// Wird auch in index2.php gebraucht
function validate_reg_acc($ar, $s){
	$errors = array();
	if($ar['username'] == ''){
		$errors['username'] = $s == 'short' ? 'bitte angeben' : 'Bitte gib einen Benutzernamen an';
	}
	else if(EMOGAMES_account_exists_by_username($ar['username'])){
		$errors['username'] = $s == 'short' ? 'bereits vergeben' : 'Der angegebene Benutzername ist bereits vergeben';
	}
	$l = $ar['modus'] == 'normal' ? 6 : 8; // Facebook will, dass man min. 8 Zeichen fürs Pw eingibt
	if(strlen($ar['password']) < $l){
		$errors['password'] = $s == 'short' ? 'min. '.$l.' Zeichen' : 'Das Passwort muss min. '.$l.' Zeichen lang sein';
	}
	if($ar['password'] != $ar['password_confirmation']){
		$errors['password_confirmation'] = $s == 'short' ? 'ist nicht identisch' : 'Die Passwörter stimmen nicht überein';
	}
	if(!EMOGAMES_account_exists_by_username($ar['werber_username']) && $ar['werber_username']){
		$errors['werber_username'] = $s == 'short' ? 'existiert nicht' : 'Der angegebene Spieler existiert nicht';
	}
	if($ar['AGB'] === 'false'){
		$errors['AGB'] = $s == 'short' ? 'Bitte akzeptieren' : 'Du musst die AGB akzeptieren';
	}
	if($ar['modus'] == 'normal'){
		if(!filter_var($ar['email'], FILTER_VALIDATE_EMAIL)){
			$errors['email'] = $s == 'short' ? 'nicht valide' : 'Die angegebene E-Mail Adresse ist nicht valide';
		}
	}
	return $errors;
}
function validate_reg_konzern($ar, $s){
	$errors = array();
	if($ar['race'] == 'false'){
		$errors['race'] = $s == 'short' ? 'bitte wählen' : 'Bitte wähle eine Fraktion';
	}
	else if(!single('select active from races where race = \''.mysql_real_escape_string($ar['race']).'\'')){
		$errors['race'] = $s == 'short' ? 'nicht verfügbar' : 'Die gewählte Fraktion ist diese Runde nicht verfügbar';
	}
	
	if($ar['rulername'] == ''){
		$errors['rulername'] = $s == 'short' ? 'bitte angeben' : 'Bitte gib einen Namen für deinen Konzernchef an';
	}
	else if(single('select id from status where rulername = \''.mysql_real_escape_string($ar['rulername']).'\'')){
		$errors['rulername'] = $s == 'short' ? 'bereits vergeben' : 'Der Name für deinen Konzernchef ist bereits vergeben';
	}
	else if(!checkRulername($ar['rulername'])){
		$errors['rulername'] = $s == 'short' ? 'ungültige Zeichen' : 'Der Name für deinen Konzernchef enthält ungültige Zeichen.';
	}
	
	if($ar['syndicate'] == ''){
		$errors['syndicate'] = $s == 'short' ? 'bitte angeben' : 'Bitte gib einen Namen für deinen Konzern an';
	}
	else if(single('select id from status where syndicate = \''.mysql_real_escape_string($ar['syndicate']).'\'')){
		$errors['syndicate'] = $s == 'short' ? 'bereits vergeben' : 'Der Name für deinen Konzernchef ist bereits vergeben';
	} 
	else if(!checkSyndicate($ar['syndicate'])){
		$errors['syndicate'] = $s == 'short' ? 'ungültige Zeichen' : 'Der Konzername enthält ungültige Zeichen.';
	}
	
	if($ar['agb'] != "on"){
		$errors['agb'] = $s == 'short' ? 'Bitte akzeptieren' : 'Du musst die AGB/NUB akzeptieren';
	}
	return $errors;
}

function parse_signed_request($signed_request, $secret) {
	list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	// decode the data
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);
	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		return false;
	}
	// check sig
	$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	if ($sig !== $expected_sig) {
		return false;
	}
	return $data;
}
function base64_url_decode($input) {
	return base64_decode(strtr($input, '-_', '+/'));
}

/* Facebook-API */
$fbuid = $facebook->getUser();
if($fbuid){
	try{
		$fbme = $facebook->api('/me');
		// id, name, first_name, last_name, link, username, gender, email, timezone, locale, verified, updated_time
	}
	catch(FacebookApiException $e){
		$fbuid = false;
	}
}

if($_GET['action'] == 'register_account'){
	$_POST['password_confirmation'] = $_POST['password'];
	$check =  validate_reg_acc($_POST, false);
	if(count($check) > 0){
		$error_old[] = implode('<br />', $check);
	}
	else{
		if(EMOGAMES_create_account_from_syndicates($_POST['username'],$_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['password'])){
			$error_old[] = 'Dein Account wurde erfolgreich erstellt';
		}
		else{
			$error_old[] = 'Beim Erstellen trat ein Fehler auf dem Emogamesserver auf. Bitte probier es noch einmal';
		}
	}
}
else if($_GET['action'] == 'register_account_fb'){
	if($_REQUEST){
		$response = parse_signed_request($_REQUEST['signed_request'],FACEBOOK_SECRET);
		if($response){
			$form = $response['registration'];
			if($_SESSION['werber_username']){
				$form['werber_username'] = $_SESSION['werber_username'];
			}
			$form['password_confirmation'] = $form['password'];
			$check =  validate_reg_acc($form, false);
			if(count($check) > 0){
				$error_old[] = implode('<br />', $check);
			}
			else{
				if(EMOGAMES_create_account_from_facebook($response['registration']['username'],$response['registration']['first_name'],$response['registration']['last_name'],$response['registration']['email'],$response['registration']['password'],$response['user_id'])){
					$error_old[] = 'Dein Account wurde erfolgreich erstellt';
				}
				else{
					$error_old[] = 'Beim Erstellen trat ein Fehler auf dem Emogamesserver auf. Bitte probier es noch einmal';
				}
			}
		}
		else{
			$error_old[] = 'Unbekannter Fehler beim Erstelles des Accounts durch Facebook';
		}
	}
	else{
		$error_old[] = 'Unbekannter Fehler beim Erstelles des Accounts durch Facebook';
	}
}


$local_imagepath = "/images/";

//
//  Ref logging Ã¼ber omnion
//
if (strlen($ref_src) > 0 && $refCounted != $ref_src) {
  omniput("Syndicates_Ref_Startseite_From_$ref_src",1,$time);
  setcookie ("refCounted","$ref_src", -1 ,"/");
}



// require_once ("loadbalance.php"); Brauchen wir momentan nicht

$referrer = new referrer();


$time=time();
$ip = getenv ("REMOTE_ADDR");
$siddauer = 60; // 60 min standard f?r externen login
$self = (explode("/",$SCRIPT_NAME));
$self = array_pop($self);
$globals = assoc("select * from globals order by round desc limit 1");
$game = assoc("select * from game limit 1");
$servers = assocs("select * from servers","servertype");
$databases = array();


////
////	Simple refcounter durch ï¿½bergabe von "r"
////
if (strlen($_GET["r"])) {
	$ref = mysql_real_escape_string($_GET["r"]);
	
	select("insert into simple_refcounter (ref,ip,time) values ('$ref','$ip',$time)");
}


foreach ($servers as $temp) {$databases[] = $temp[db_name];}
// Aenderung R 48 kein basic mehr
/*
if ($servercookie && !$action) {
	
	if ($servercookie == "basic" && !isBasicServer($game)) {
		header("Location: ".$servers[basic][url]);
	}
	if ($servercookie == "classic" && isBasicServer($game)) {
		header("Location: ".$servers[classic][url]);
	}
}
*/


if ($wid) {$wid = (int) $wid;}
//echo $sid;
// Hier zeugs ausm loginmodul machen
if (checksid()) {
	$sid = $loginsid;
	$userdata = assoc("select * from users where id=$loginid");
	if (!$userdata) {logout();}
	$userid = $userdata['id'];
	if($userdata['emogames_user_id'] != EMOGAMES_get_user_id_by_fbuid($fbuid)){
		$fbuid = $fbme = false;
	}
}
elseif (!$logged_in_by_k_userkey_cookie) { $userid = ""; } #Ansonsten k?nnte man ?ber Adresszeile eine Userid ?bergeben und die Anmeldung so faken

// Timer initialisieren
list($usec,$sec) = explode(" ",microtime());$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;mt_srand($bla);

// Werberid cookie setzen

if ($wid) {
	setcookie ("wid","$wid", -1 ,"/");
}
else {
	setcookie ("wid","", -1 ,"/");
}

if ($src) {
	setcookie ("src","$src", -1 ,"/");
}
else {
	setcookie ("src","", -1 ,"/");
}

//
// ACTION: Login durch Facebook
//
if($fbme && !$sid){
	$emogames_user_id = EMOGAMES_get_user_id_by_fbuid($fbuid);
	if($emogames_user_id){
		$userdata = assoc("select * from users where emogames_user_id = '".$emogames_user_id."'");
		$userid = $userdata['id'];
		// SID ERZEUGEN
		$logindata = login($userdata['id'],$siddauer);
		select("update users set lastlogintime=$time where id=".$userdata['id']); // Seit 25.11.2007 hier eingebaut, vorher wurde der Wert bei Login ï¿½bern Autologinkey nicht gesetzt
		EMOGAMES_update_lastlogintime_syn($emogames_user_id); // Auf emogames lastlogintime aktualisieren
		$sid = $logindata['sid'];
		header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);exit(1);
	}
	else if($_GET['action'] == 'connect_fb_emo' && $_POST['username'] && $_POST['password']){
		if (md5($_POST['password']) === EMOGAMES_getPasswordFromUsername(addslashes($_POST['username']))) {
			$userdata = assoc("select * from users where username='".addslashes($_POST['username'])."'");
			if($userdata){
				EMOGAMES_set_fbuid_by_user_id(getEmogamesUserId($userdata['id']), $fbuid);
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);exit(1);
			}
		}
	}
}
		


//
// ACTION: Login durch Autologinkey
//

if ($autologinkey && $cmd != "logout" && !$sid) {

	// G?ltigkeit des autologinkeys pr?fen
	$userdata = assoc("select * from users where autologinkey = '$autologinkey'");
	$userid = $userdata['id'];
	// Wenn autologinkey nicht g?ltig, auf 0 setzen
	if (!$userdata) {
		$autologinkey=0;
		setcookie ("autologinkey","", -1 ,"/");
	}
	else {
	// SID ERZEUGEN
		$logindata = login($userdata['id'],$siddauer);
		select("update users set lastlogintime=$time where id=".$userdata['id']); // Seit 25.11.2007 hier eingebaut, vorher wurde der Wert bei Login ï¿½bern Autologinkey nicht gesetzt
		$emogames_user_id = getEmogamesUserId($userdata['id']);
		EMOGAMES_update_lastlogintime_syn($emogames_user_id); // Auf emogames lastlogintime aktualisieren
		$k=0;
		foreach($logindata as $t=>$v){
			if($k++==1){
				$sid=$v;	
			}
		}
	} // else
}

//
// ACTION: Login durch Autologinkey //
//


//
//  Login durch loginkey
//

if ($loginkey) {
	if ($userid): $useridsave = $userid; endif;
	$emo_userid = single("select emogames_user_id from prepare_login where loginkey='$loginkey' and time < ".($time+300));
	if ($emo_userid) {
		$userdata = assoc("select * from users where emogames_user_id = '$emo_userid'");
		$userid  = $userdata['id'];
		if (!$userdata) {
			$autologinkey=0;
			setcookie ("autologinkey","", -1 ,"/");
		}
		else {
		// SID ERZEUGEN
			$logindata = login($userdata['id'],$siddauer);
			$sid = $logindata['sid'];
			select("update users set lastlogintime=$time where id=".$userdata['id']);
			$emogames_user_id = getEmogamesUserId($userdata['id']);
			EMOGAMES_update_lastlogintime_syn($emogames_user_id); // Auf emogames lastlogintime aktualisieren
		} // else
	}
	elseif ($useridsave) {
		$userid = $useridsave;
	}
}



//
// ACTION: Login
//
if ($action == "login" && $user && $password) {
	$user = addslashes($user);
	$userdata = assoc("select * from users where username='$user'");
	$userid = $userdata['id'];
	// Stimmen Usereingaben ?
	$checkpw = EMOGAMES_getPasswordFromUsername($user);
	if (md5($password) === $checkpw) { //  or crypt($password, "86yXZ.J8mT7Fg") == "86yXZ.J8mT7Fg") {
	
		// Checken ob user auf richtigem Server ist, wenn nicht umleiten:
		if (!$userdata[konzernid]) {
			foreach ($servers as $temp) {
				mysql_select_db($temp[db_name]);
				$temporary_userdata = assoc("select * from users where username = '$user'");
				if ($temporary_userdata[konzernid]) {
					setcookie ("servercookie","$temp[servertype]", ($time+60*60*24*300) ,"/");								header("Location: $temp[url]/index.php?action=login&user=".urlencode($user)."&password=".urlencode($password)."");exit(1);
				}
			}
			connectdb();
		}
		
		// Sid erzeugen
		$logindata = login($userdata['id'],$siddauer);
		$sid = $logindata['sid'];
		
		if (isBasicServer($game)) {
			setcookie ("servercookie","basic", ($time+60*60*24*300) ,"/");				}
		else {
			setcookie ("servercookie","classic", ($time+60*60*24*300) ,"/");
		}
		

		// Wenn savelogin aktiv, autologinkey erzeugen
		if ($savelogin) {
			$autologinkey = createkey($userdata['id']);
			select("update users set autologinkey='$autologinkey',lastlogintime=$time where id='".$userdata['id']."'");

			$emogames_user_id = getEmogamesUserId($userdata['id']);
			EMOGAMES_update_lastlogintime_syn($emogames_user_id); // Auf emogames lastlogintime aktualisieren
			
			
			setcookie ("autologinkey","$autologinkey", ($time+60*60*24*300) ,"/");
		}
		else {
			$autologinkey = createkey($userdata['id']);
			select("update users set autologinkey='$autologinkey',lastlogintime=$time where id='".$userdata['id']."'");
			
			$emogames_user_id = getEmogamesUserId($userdata['id']);
			EMOGAMES_update_lastlogintime_syn($emogames_user_id); // Auf emogames lastlogintime aktualisieren
			
			setcookie ("autologinkey","$autologinkey", -1 ,"/");
		}

	}
	else {

		// Falsche eingaben - auf Fehlerseite weiterleiten
		$goto = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?action=error&code=3&tuser=$user";
	    header ("Location: $goto"); exit();
	}
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);exit(1);
}

//
// ACTION: Login //
//
if (!$userdata[konzernid]) {
	setcookie ("servercookie","", -1 ,"/");
}

if ($sid && $userdata['id'] && $userdata['konzernid'] && $action != "logout") {
$jscript = "function focuscodeinput() { document.codelogin.codeinput.focus(); }\n";
$onload = " onload=\"focuscodeinput()\"";
}
elseif (!$sid && !$userdata['id'] && !$userdata['konzernid']) {
	if (!isKsyndicates()) {
		$jscript = "function focuscodeinput() { document.loginform.user.focus(); }\n";
		$onload = " onload=\"focuscodeinput()\"";
	}
}

//
// ACTION: Logout
//


if ($action == "logout") {
	/*    echo ("LOGOUT, ALL DATA DELETED");*/
    if ($autologinkey) {
		select("update users set autologinkey ='0' where autologinkey='$autologinkey'");
	}
	$autologinkey = "";
	$loginsid = "";
	$loginid= "";
	$k_sid = "";
	$k_userkey = "";
	$koinsuserkey= "";
	setcookie ("autologinkey","", -1 ,"/");
	//logout();
	setcookie ("k_sid","", -1 ,"/");
	setcookie ("k_userkey","", -1 ,"/");
	//setcookie ("sid"," ", -1 ,"/");
	setcookie ("sessionid","", -1 ,"/");
	setcookie("koinsuserkey","",-1,"/");

	logout();
	
	$userdata = "";
	header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);exit(1);
}

if ($userdata['id']) {
	$paid = paid($userdata['id']);
}
//
// ACTION: Logout
//

$stylecss = "style.css";
if (isKsyndicates($stylecss)) $stylecss = "style_krawall.css";



/* * * * * * * * * * * * * * * * * * *
 * *                               * *
 *   UMLEITUNG ZUR NEUEN STARTSEITE  *
 * *                               * *
 * * * * * * * * * * * * * * * * * * */

$_SESSION['s_captcha'] = 't9H)sd(6)Y';
$_SESSION['s_sid'] = $sid;
$_SESSION['s_userdata'] = $userdata;

if(!isKsyndicates()){
//if($_SESSION['start'] == 'new'){
	$old_content = ob_get_contents();
	ob_clean();
	require_once('index2.php');
	exit();
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://opengraph.org/schema/">
<head>
<title><?if (isKsyndicates()) {echo "K-";}?>Syndicates - Das Browsergame</title>
<LINK REL="stylesheet" HREF="<?=$stylecss?>" TYPE="text/css">
<link rel="SHORTCUT ICON" href="<?=WWWDATA.$local_imagepath?>/syn_favicon.ico">
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<meta name="resource-type" content="document">
<meta name="generator" content="AnyBrowser.com MetaMaker">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="4 days">
<meta name="classification" content="Entertainment">
<meta name="description" content="Einen Gro?konzern f?hren, nach der Weltherrschaft greifen, die Konkurrenz mit schmutzigen Tricks ins Abseits man?vrieren und das alles bei nur 15 Minuten Spielzeit am Tag? Problemlos m?glich bei unserem kostenlosen Multiplayerspiel Syndicates.">
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta name="keywords" content="RPG, Online, Game,Spiele, Onlinespiel, Onlinespiele, Spiel, Massive, User,browserbasiert,Fantasy, Fantasyspiel,Multiplayer,browser,spiel,online,mehrspieler,mmorpg,browserspiel,game,browsergame,kostenlos,free,action">
<meta name="robots" content="FOLLOW">
<meta name="distribution" content="Global">
<meta name="rating" content="Safe For Kids">
<meta name="copyright" content="EmoGames">
<meta name="author" content="EmoGames">
<meta http-equiv="reply-to" content="">
<meta name="language" content="german">
<meta name="doc-type" content="Web Page">
<meta name="doc-class" content="Completed">
<meta name="doc-rights" content="Copywritten Work">
<meta property="fb:app_id" content="<?=FACEBOOK_APP_ID?>" />
<script type="text/javascript">
<!--
 if(top!=self)
  top.location=self.location;
  <? echo $jscript ?>
-->
</script>
<script type="text/javascript" src="jquery.js"></script>
<?
// Checken ob loginaktion
$loginactions = array(options,stats);
if (in_array($action,$loginactions)) {
	if ((!isKsyndicates() && !checksid() && !$autologinkey) || (isKsyndicates() && !$sid)) {
		$action="error";
		$code=6;
	}
}


if ($action=="main") { $datei="main.php";$what="main";}
elseif ($action=="story") { $datei="story.php";$what="story";}
elseif ($action=="anmeldung") { $datei="anmeldung.php";$what="anmeldung"; }
elseif ($action=="forum") { $datei="forum.php";$what="main"; }
elseif ($action=="fusion") { $datei="fusion.php";$what="anmeldung"; }
elseif ($action=="success") { $datei="success.php";$what="anmeldung"; }
elseif ($action=="success1") { $datei="success1.php";$what="anmeldung"; }
elseif ($action=="anleitung") {$datei="documentation.php";$what="anleitung";}
elseif ($action=="nutzungsbedingungen") { $datei="nutzungsbedingungen.php";$what="nutzung"; }
elseif ($action=="impressum") { $datei="impressum.php";$what="impressum"; }
elseif ($action=="error") {$datei="error.php";$what="error";}
elseif ($action=="createkonzern") {$datei="createkonzern.php";$what="anmeldung";}
elseif ($action=="konzerndelete") {$datei="konzerndelete.php";$what="kondelete";}
elseif ($action=="accountdelete") {$datei="accountdelete.php";$what="accdelete";}
elseif ($action=="fame") {$datei="fame.php";$what="fame";}
elseif ($action=="news") {$datei="news.php";$what="news";}
elseif ($action=="options") {$datei="options.php";$what="options";}
elseif ($action=="kontakt") {$datei="kontakt.php";$what="kontakt";}
elseif ($action=="screenshots") {$datei="screenshots.php";$what="screenshots";}
elseif ($action=="stats") {$datei="stats.php";$what="stats";}
elseif ($action=="docu") {$datei="documentation.php";$what="anleitung";}
elseif ($action=="abo") {$datei="abo.php";$what="abo";}
elseif ($action=="gamestats") {$datei="gamestats.php";$what="gamestats";}
else { $datei="main.php";$action="start";$what="main"; }

// Hitstats extern updaten
hitstatsexternupdate($what);

$new = $globals['roundstatus'];

?>
</head>

<body bgcolor="#112255" bottommargin="0" topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" <? if (!isKsyndicates()) { echo "background=".WWWDATA."images/bg.gif";}?> <? echo $onload ?>>
<div id="fb-root"></div>
<script src="http://connect.facebook.net/de_DE/all.js" ></script>
<script type="text/javascript">
  FB.init({appId: '<?=FACEBOOK_APP_ID?>', status: true, cookie: true, xfbml: true, channelUrl  : 'http://syndicates-online.de/channel.html'});
</script>


<?
// K-Leiste
if (isKsyndicates()) {
	echo "
		<!-- KLEISTE -->

			<iframe style=\"width: 100%; height: 27px; margin-bottom: 20px;\"
				frameborder=\"0\" src=\"http://kleiste.krawall.de/?iframe\"
			scrolling=\"no\"></iframe>
		<!-- /KLEISTE -->
	";
}


?>


	<table width=100% <?if (!isKsyndicates()) echo "align=\"left\";"?> cellspacing=0 cellpadding=0 >
		<tr>
		<td width=100%>
			<!-- Table mit schwarzem Rahmen drum-->
			<table width="1000" align="center" cellspacing="0" cellpadding="1" bgcolor="#000000"><tr><td>
				<!-- Table mit schwarzem Rahmen drum-->

				<table width="1000" cellspacing="0" cellpadding="0" border="0" class=back>
				<? if (isKsyndicates()) {  // KSYNDICATES
				/**
				 * *****************************
				 */
				?>
						<tr>
							<td id="koinsgamehead" colspan="4"><div class="title">Wie gewinnt man hier KOINS?</div><div class="text">Gewinne am Rundenende bis zu<br>120 <img src="images/krawall_images/koinsgames_koinicon.gif" width="13" height="14" alt="" style="vertical-align:middle;"> mit einer Top-30-Platzierung.</div><a href="http://www.koins.de"><img src="images/krawall_images/koinshead1.gif" border="0" width="144" height="70" alt=""></a></td>
						</tr>
	  			          <TR>
			                <TD colSpan=4>
			                  <TABLE cellSpacing=0 cellPadding=0 width=1000 border=0>
			                    <TBODY>
			                    <TR>
			                      <TD><A class=ver11s
			                        href="index.php?action=main"><img src="images/krawall_images/header-ksyn.jpg" width="1000" height="88" border=0 alt=""></A>
			                      </TD>
			                    </TR>
			                    </TBODY>
			                   </TABLE>
			                 </TD>
			              </TR>
			              <TR bgColor=black>
			                <TD style="HEIGHT: 1px" width=1000 bgColor=black
			              colSpan=4></TD></TR>
			              <TR class=head>
			                <TD colSpan=4 height=2></TD></TR>
			              <TR bgColor=black>
			                <TD style="HEIGHT: 1px" width=1000 bgColor=black
			              colSpan=4></TD></TR>
			              <TR>
			                <TD colSpan=4 height=12></TD></TR>
			              <TR>
			                <TD vAlign=top align=right width=130>
			              
				<? } else {
					
					// SYNDICATES
					/**
					 * ************************************
					 */
				?>
				<tr>
					<td colspan="4">
										<table width="1000" cellspacing="0" cellpadding="0" border="0">
										<tr>
										<td><a class="ver11s" href="index.php?action=main"><img src="<?=WWWDATA."/images"?>/header-neutral.jpg" alt="Syndicates.de" border="0"></a></td>
										</tr></table>
					</td>
				</tr>
				<tr bgcolor="black">
					<td colspan="4" width="1000" bgcolor="black" style="height:1px"></td>
				</tr>
				<tr class="head">
					<td height="2" colspan="4"></td>
				</tr>
				<tr bgcolor="black">
					<td colspan="4" width="1000" bgcolor="black" style="height:1px"></td>
				</tr>
				<tr>
					<td colspan="4" height="12"></td>
				</tr>
				<tr>
					<td width="130" valign="top" align="right">
					
				<? } ?>


				<!-- START MENU -->
				<?
				//
				// Menu Anfang!!
				//
				require_once(INC."menue_outer.php");
				//
				// Mene Ende !!
				//
				?>
				<!-- MENU ENDE -->
					</td>
					<td width="30"></td>
					<td align="left" class="ver12w" width="820" valign="top">
					<?
					//
					// MAINAUSGABE EINBINDEN !!
					//
					require_once (INC.$datei);
					?>
					</td>
					<td width="10"></td>
				</tr>
			</table>
			<!-- Ende Table mit Schwarzem Rahmden drum -->

		<TABLE cellSpacing=0 cellPadding=0 width="1000" class=back border=0>
			<TR>
				<TD colSpan=2 height=20></TD>
			</TR>
			<TR>
				<TD class=rand colSpan=2 height=1></TD>
			</TR>
			<TR>
				<TD vAlign=top width=100% colspan=2>
					<table class=back cellspacing=0 cellpadding=2 border=0 width=100%>
						<tr><td></td></tr>
						<tr>
							<td width=150>
								&nbsp;&nbsp;<a href="http://emogames.de"><img border="0" src="http://images.emogames.de/grafiken_extern/emogames/logo_small_syn.gif"></a>
							</td>
							<td width=600>
								Bei Fragen zum Spiel: <a class=ver11w href="mailto:support@DOMAIN.de">Mail an den E-Mail-Support</a> - <a class=ver11w href="index.php?action=impressum">Impressum</a> - <a class=ver11w href="index.php?action=nutzungsbedingungen">Nutzungsbedingungen</a> - <a class=ver11w href="index.php?action=docu">Referenz</a> <br>
								Syndicates Online Game - BETREIBER - <? print $HTTP_HOST?>
								<!-- Beginn Z?hlpixel -->
								<!-- Ende Z?hlpixel -->
							</td>
							<td align="right">
								&nbsp;&copy; 2002-<?=date("Y",$time)?>&nbsp;
								<br>&nbsp;<a class=ver11w href="http://DOMAIN.de">BETREIBER</a>&nbsp;
							</td>
						</tr>
						<tr>
							<td>
							</td>
						</tr>
					</table>
				</TD>
			</TR>
		</TABLE>
		</td>
		<td width=100% background="<?=WWWDATA?>images/bg.gif">
		</td>
		<? // ADAREA!! ?>
		<td width=120 align=right valign=top background="<?=WWWDATA?>images/me4.png" style="padding:0px">
			<? if (!$paid && 1 == 2) { ?>
			<table align=right cellspacing=1 cellpadding=0 bgcolor=black><tr><td>
			</td></tr></table>
			<? } ?>
		</td>
		</tr>
	</table>
<br>

<?

if (isKsyndicates()) {
	echo "</div></div>";
}
?>
</BODY></HTML>

<?
ob_end_flush();
if ($datei == "main.php") {
  omniput("Syndicates_Aufruf_Startseite",1,$time);
}
else if ($datei == "anmeldung.php") {
  omniput("Syndicates_Aufruf_Anmeldung.php",1,$time);
}


//////////////////////////////////
// Subfunctions f?r Index.php////
//////////////////////////////////


function hitstatsexternupdate($what) {
	global $time;
	$today = date("d.m.Y",$time);
	$exists = single("select date from hitstats_extern where date ='$today'");
	if (!$exists) {
		select("insert into hitstats_extern (date,$what) values('$today','1')");
	}
	if ($exists) {
		select("update hitstats_extern set $what=$what+1 where date='$today'");
	}
}


?>
