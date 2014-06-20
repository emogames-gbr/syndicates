<?
/*
 * HEADER.PHP 2010
 * @author: Wanderzirkus
 * @merger: o19
 *
 * @m-date: 2010-03-05
 */

$content_cookie = $_REQUEST['content_cookie'];
if ( !$init && $time-$content_cookie > 60*5) { // isKsyndicates() &&
	setcookie("content_cookie",$time,"-1","/");
}
// GP-variable $ripf assignen
$tpl->assign("GP_PATH", $ripf);#

//Start Ticker

$tplTicker = array();
define(TICKERKOSTEN,5);
define(TICKER_MAXZEICHEN,150);
$tplTicker['max'] = TICKER_MAXZEICHEN;
$tplTicker['emos'] = TICKERKOSTEN;
$tplTicker['wiki'] = WIKI."Ticker";

if ($action == "ticker_add" && $tickernews != '') {
	if ($ia2 == "ticker_add") {
		$emogames_id = single("select emogames_user_id from users where konzernid=".$status['id']);
		$GUTHABEN = EMOGAMES_get_emos($emogames_id);
		if ($GUTHABEN >= TICKERKOSTEN) {
			if (($l = strlen($tickernews)) <= TICKER_MAXZEICHEN) {
				$text = "Community Ticker<br>$tickernews<br>".TICKERKOSTEN." EMOs";
				EMOGAMES_pay_emos($emogames_id,TICKERKOSTEN,$text);
				TICKER_insert_content($tickernews,$status[id], $kostenlos);
				s("Deine Nachricht wurde erfolgreich eingetragen.");
			}
			else {
				f("Deine Nachricht ist $l Zeichen lang.<br>Tickernachrichten dürfen höchsten ".TICKER_MAXZEICHEN." Zeichen lang sein.");
			}
		}
		else {
			f("Du besitzt nicht genügend EMOs, um eine Nachricht schreiben zu können.<br>Bitte lade deinen Emogames Account wieder auf.");
		}
	}
}

$content = singles("select content from ticker_content order by time desc limit 10");
$msgs = array();
$i = 0;
foreach($content as $text){
	$msgs[$i]['text'] = umwandeln_bbcode($text);
	$msgs[$i]['it']= $i;
	$i++;
}
$tplTicker['script'] = TICKER_get_ticker();
//$tpl->assign("TICKER",$tplTicker);
$tpl->assign("TICKER", false);
$tpl->assign("T_MSGS", $msgs);

//End Ticker


// Tipps - by R4bbiT - 27.09.11
list($userid, $hidetipps) = row('select emogames_user_id, hidetipps from users where konzernid = '.$status['id']);
$tpl->assign('HIDETIPPS', $hidetipps);
if(!$hidetipps){
	$tipp = Tipps::getTipp($page, $userid);
	$tpl->assign('TIPP', $tipp);
	if($_GET['action'] == 'getNextTipp'){
		ob_clean();
		header("content-type: application/json; charset=uft-8");
		echo json_encode(toUTF8(Tipps::getNext((int) $_GET['num'], $page, $userid)));
		exit();
	}
	if($_GET['action'] == 'getPrevTipp'){
		ob_clean();
		header("content-type: application/json; charset=uft-8");
		echo json_encode(toUTF8(Tipps::getPrev((int) $_GET['num'], $page, $userid)));
		exit();
	}
	if($_GET['action'] == 'readTipp'){
		Tipps::toggleUserTipp($userid, (int) $_GET['id']);
	}
}


if ($game[name] == "Syndicates Testumgebung") {
	$nick = $_SERVER['PHP_AUTH_USER'];
	$tpl->assign('PAGE_TITLE', "Dev-".$nick."-Syndicates");
}
else {
	$tpl->assign('PAGE_TITLE', "Syndicates");
}

$local_imagepath = "images";
if (isKsyndicates()) $local_imagepath = "images/krawall_images";

$tpl->assign('LOCAL_IMAGEPATH', WWWDATA.$local_imagepath."/");
$tpl->assign('COMMERCIALS', ($features[WERBUNG_DEAKTIVIERT] ? false : true));
$tpl->assign('IMAGE_PATH', $layout['images']);

$usid=single("select id from users where konzernid=$status[id]");
$seed=$usid."x".$time;

		$flashjs='';
		$flashin='';
$tpl->assign('FLASH_JS', $flashjs);		
$tpl->assign('FLASH_IN', $flashin);		

if (1 == 2 && preg_match("/MSIE/",$HTTP_USER_AGENT)) {
    $tpl->assign('MSIE_USED', true);
    $tpl->assign('MSIE_STYLESHEET', $layout['items']);
} else {
    $tpl->assign('MSIE_USED', false);
}

$outerwidth_value_depending_on_werbung = 782;
$rand_for_werbung = mt_rand(1,5);
	if ((!$features[WERBUNG_DEAKTIVIERT] and ($time - $status[createtime] > 14 * 86400 or !is_new_player($id)) )) {
		if (TRUE OR ($rand_for_werbung >=3 || ($self == "statusseite.php" && $init == 2 || $page=="syndicate"))) {
			$outerwidth_value_depending_on_werbung = 970;
		}
	}
$tpl->assign('OLD_OUTERWIDTH_VALUE', $outerwidth_value_depending_on_werbung);
 if ($loggedin) {
    $tpl->assign('LOGGED_IN', true);
    $tpl->assign('NETWORTH', pointit($status['nw']));
    $tpl->assign('LAND', pointit($status['land']));
    $tpl->assign('CREDITS', pointit($status['money']));
    $tpl->assign('ENERGY', pointit($status['energy']));
    $tpl->assign('MINERALS', pointit($status['metal']));
    $tpl->assign('SCIENCEPOINTS', pointit($status['sciencepoints']));
    $tpl->assign('TIMESTAMP', date("H:i:s", time()));
} // wenn logeddin topleiste anzeigen

$tpl->assign('COLSPAN_MENU', $colspan_menu);

list($usec,$sec) = explode(" ",microtime());
//$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;
$scripttime = (double)($sec+$usec)*1000;

$time = time();

$tpl->assign('SCRIPTTIME', $scripttime);
$tpl->assign('PAGE', $page);

if ($page=="statusseite" || $page=="configseite" || $page=="nachrichten"){
    $tpl->assign('ENABLE_DOJO', true);
} else {
    $tpl->assign('ENABLE_DOJO', false);
}

if ($kopf) {
	foreach ($kopf as $key => $data) {
		$tpl->assign($key, $data);	
	}
}


	if( array_key_exists("chat_enable",$_SESSION) && $_SESSION['chat_enable'] && false)
	{
		$chat = true;
	}
	else
	{
		$chat = false;
	}
	
	if( $chat )
	{
		$chat_javascript = 
			"<script type=\"text/javascript\">
				var contentAjax;
				var sayAjax;
				var timeCode_glob = 0;
                var timeCode_syn = 0;
                var timeCode_alli = 0;
                var timeCode_help = 0;
				var currentTimeout;
				var channel = \"Global\";
                var channel_content_glob = \"\";
                var channel_content_syn = \"\";
                var channel_content_alli = \"\";
                var channel_content_help = \"\";
				var chatSize = 587;
				var chatSizeSidebar = 90;
		
				function chat()
				{
					window.clearTimeout(currentTimeout);
					contentAjax=GetXmlHttpObject();
					if (contentAjax==null)
					{
						alert('Your browser does not support XMLHTTP!');
						return;
					}
					var url=\"chat.php\";
					contentAjax.onreadystatechange=stateChanged;
					
                    var timeCode;
                    switch(channel)
                    {
                        case 'Global':
                            timeCode = timeCode_glob;
                            break;
                        case 'Syndikat':
                            timeCode = timeCode_syn;
                            break;
                        case 'Allianz':
                            timeCode = timeCode_alli;
                            break;
                        case 'Hilfe':
                            timeCode = timeCode_help;
                            break;
                        default:
                            timeCode = timeCode_glob;
                            break;
                    }
					param = 'action=content&chan=' + channel + '&code=' + timeCode;
					contentAjax.open(\"POST\",url,true);
					
					contentAjax.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
					contentAjax.setRequestHeader(\"Content-length\", param.length);
					contentAjax.setRequestHeader(\"Connection\", \"close\");
					contentAjax.send(param); 	
				}
				
				function chatSay(str)
				{
					window.clearTimeout(currentTimeout);
					currentTimeout = window.setTimeout(\"chat()\",10000);
					sayAjax=GetXmlHttpObject();
					if (sayAjax==null)
					{
						alert (\"Your browser does not support XMLHTTP!\");
						return;
					}
					var url=\"chat.php\";
					sayAjax.onreadystatechange=chatSayDone;
					
					param = 'action=say&chan=' + channel + '&say=' + str;
					sayAjax.open(\"POST\",url,true);
					
					sayAjax.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
					sayAjax.setRequestHeader(\"Content-length\", param.length);
					sayAjax.setRequestHeader(\"Connection\", \"close\");
					sayAjax.send(param);
				}			
		
				function stateChanged()
				{
					window.clearTimeout(currentTimeout);
					if (contentAjax.readyState==4)
					{
                        var responseChannel = \"\";
						var newChat = contentAjax.responseText;
						var splitPos = newChat.search(/\|/);
                        var timeCode = 0;
						if( splitPos != -1 )
						{
                            responseChannel = newChat.substr(0,splitPos);
                            newChat = newChat.substr(splitPos+1,newChat.length);

                            splitPos = newChat.search(/\|/);
                            if( splitPos != -1 )
                            {
                                timeCode = newChat.substr(0,splitPos);
                                newChat = newChat.substr(splitPos+1,newChat.length);
                            }
                            else
                            {
                                responseChannel = 'Global';
                                timeCode = 0;
                            }
						}
						else
						{
                            responseChannel = 'Global';
							timeCode = 0;
						}
                        
                        var copyToCurrent = false;
                        if( channel == responseChannel )
                        {
                            copyToCurrent = true;
                        }

                        switch(responseChannel)
                        {
                            case \"Global\":
                                channel_content_glob += newChat;
                                timeCode_glob = timeCode;
                                if( copyToCurrent )
                                {
                                    document.getElementById(\"chat\").innerHTML = channel_content_glob;
                                }
                                break;
                            case \"Syndikat\":
                                channel_content_syn += newChat;
                                timeCode_syn = timeCode;
                                if( copyToCurrent )
                                {
                                    document.getElementById(\"chat\").innerHTML = channel_content_syn;
                                }
                                break;
                            case \"Allianz\":
                                channel_content_alli += newChat;
                                timeCode_alli = timeCode;
                                if( copyToCurrent )
                                { 
                                    document.getElementById(\"chat\").innerHTML = channel_content_alli;
                                }
                                break;
                            case \"Hilfe\":
                                channel_content_help += newChat;
                                timeCode_help = timeCode;
                                if( copyToCurrent )
                                { 
                                    document.getElementById(\"chat\").innerHTML = channel_content_help;
                                }
                                break;
                            default:
                                channel_content_glob += newChat;
                                timeCode_glob = timeCode;
                                if( copyToCurrent )
                                { 
                                    document.getElementById(\"chat\").innerHTML = channel_content_glob;
                                }
                                break;
                        }
		
						var chat = document.getElementById('chatwindow');
						chat.scrollTop = chat.scrollHeight;
		
						currentTimeout = window.setTimeout(\"chat()\",1000);
					}
				}
			
				function chatSayDone()
				{
					window.clearTimeout(currentTimeout);
					if (sayAjax.readyState==4)
					{
						currentTimeout = window.setTimeout(\"chat()\",1000);
					}
				}
				
				function GetXmlHttpObject()
				{
					if (window.XMLHttpRequest)
					{
						// code for IE7+, Firefox, Chrome, Opera, Safari
						return new XMLHttpRequest();
					}
					if (window.ActiveXObject)
					{
						// code for IE6, IE5
						return new ActiveXObject(\"Microsoft.XMLHTTP\");
					}
					return null;
				}
		
				dojo.require(\"dojox.fx\");
				var currentAnimation;
				var active = false;
				var dabei = false;
				
				function doChatAnimation()
				{
					if(active == false && !dabei)
					{
						dabei = true;
						active = true;
						dojox.fx.wipeTo(" .
								"{
									node: 'chatTable',
									duration: 100,
									height: 250,
									onBegin: function()
										{ 
											document.getElementById('chatwindow').style.height=225;
										},
									onEnd: function() {dabei = false;}
								}).play();
						document.getElementById('chatForm').style.display='inline';
						document.getElementById('chatChannelHead').style.display='table-row';
						document.getElementById('chatSidePanel').style.display='none';
						document.getElementById('chatwindow').style.width=chatSize;
					}
					else if(active == true && !dabei)
					{
						dabei = true;
						active = false;
						dojox.fx.wipeTo({node: 'chatTable',duration: 100,height: 25, onBegin: function(){ document.getElementById('chatwindow').style.height=25;},onEnd:function(){dabei = false;}}).play();
						document.getElementById('chatForm').style.display='none';
						document.getElementById('chatChannelHead').style.display='none';
						document.getElementById('chatSidePanel').style.display='table-cell';
						document.getElementById('chatwindow').style.width=chatSize - chatSizeSidebar;
						document.getElementById('chatSidePanel').innerHTML=channel;
					}
				}

                function changeChannel(newChannel)
                {
                    document.getElementById(\"chat_channel_head_glob\").style.fontWeight = 'normal';
                    document.getElementById(\"chat_channel_head_syn\").style.fontWeight = 'normal';";
                    if( $_SESSION['chat_ally'] != 0 )
                    {
                        $chat_javascript .= "document.getElementById(\"chat_channel_head_alli\").style.fontWeight = 'normal';";
                    }
                    
                    $chat_javascript .= 
                    "document.getElementById(\"chat_channel_head_help\").style.fontWeight = 'normal';
                    switch(newChannel)
                    {
                        case \"Global\":
                            channel = newChannel;
                            document.getElementById(\"chat_channel_head_glob\").style.fontWeight = 'bold';    
                            document.getElementById(\"chat\").innerHTML = channel_content_glob;
                            break;
                        case \"Syndikat\":
                            channel = newChannel;
                            document.getElementById(\"chat_channel_head_syn\").style.fontWeight = 'bold';
                            document.getElementById(\"chat\").innerHTML = channel_content_syn;
                            break;
                        case \"Allianz\":
                            channel = newChannel;
                            document.getElementById(\"chat_channel_head_alli\").style.fontWeight = 'bold';
                            document.getElementById(\"chat\").innerHTML = channel_content_alli;
                            break;
                        case \"Hilfe\":
                            channel = newChannel;
                            document.getElementById(\"chat_channel_head_help\").style.fontWeight = 'bold';
                            document.getElementById(\"chat\").innerHTML = channel_content_help;
                            break;
                        default:
                            channel = 'Global';
                            document.getElementById(\"chat_channel_head_glob\").style.fontWeight = 'bold';
                            document.getElementById(\"chat\").innerHTML = channel_content_glob;
                            break;
                    }
                    
                }
			</script>";
            
		
		$tpl->assign('CHAT',true);
        if( $_SESSION['chat_ally'] != 0 )
        {
            
            $tpl->assign('CHAT_ALLY',true);
        }
        else
        {
            $tpl->assign('CHAT_ALLY',false);
        }
		$tpl->assign('CHAT_JAVASCRIPT',$chat_javascript);
	}
	else
	{
		$tpl->assign('CHAT',false);
		$tpl->assign('CHAT_OUTPUT',"");
	}
	
	/*
	 * Code zum verschicken der Willkommensigms.
	* Wenn jemand die Nachricht ändern will, dann bitte hier.
	* Runde 58 by dragon (24.11.2011)
	* Wird nur ausgefŸhrt, wenn man auf der Statusseite ist, um sich auf den anderen Seiten die db Abfrage zu sparen.
	*/
	if ($_SERVER['PHP_SELF'] == '/php/statusseite.php') {
		if (single("select isNew from users where konzernid = '".$id."'") == 1) {
			$message = 'Hallo '.single("select username from users where konzernid = '".$id."'").',
			wir freuen uns, dass du Syndicates gewählt hast. Am linken Rand findest du eine Menüleiste. Schau dir die Seiten am besten in Ruhe an.
Beginnen solltest du auf der [url=http://www.syndicates-online.de/php/configseite.php]Konfigurationsseite[/url], da du dort deinen Konzern für den Start erstmal konfigurieren musst.
In der Welt von Syndicates bist du aber nicht alleine! Mit einem Klick auf [url=http://www.syndicates-online.de/php/syndicate.php]"Syndikate"[/url] siehst du deine Mitspieler. Mit diesen Spielern bildest du für diese Runde eine Gemeinschaft!
Im [url=http://www.syndicates-online.de/php/syndboard.php]Syndikatsboard[/url] kannst du dich mit ihnen unterhalten. Sag doch einfach mal "Hallo" und stell dich vor.\n
Wenn du Fragen zum Spiel hast, kannst du diese im [url=http://www.syndicates-online.de/php/fragen_und_antworten_board.php]Q&A Board[/url] stellen. Die Spieler werden dir umgehend antworten. Syndicates lebt von seiner Community.
Es gibt auch eine Reihe von Mentoren, die sich um neue Spieler kümmern. Diese Spieler sind an dem "?" neben ihrem Konzernnamen, unter "Syndikate" zu erkennen. Sie helfen dir gerne, also schreib sie an.
Eine Liste der Mentoren und in welchen Syndikaten sie sind, findest du im Q&A Board.
Falls du mehr über Syndicates lernen möchtest, ob nun die ersten Schritte, oder fortgeschrittene Taktiken, kannst du auch in der [url=http://www.syndicates-wiki.de]Synpedia[/url] stöbern.\n\n
Wir wünschen dir viel Spaß beim Spielen,\n\n
dein Emogames Team';
			
			select("insert into messages (user_id, sender, time, betreff, message) values ('".$id."', '65000', '".$time."', 'Willkommen bei Syndicates!', '".$message."')");
			select("UPDATE  `users` SET  `isNew` =  '0' WHERE  `konzernid` = '".$id."' LIMIT 1");
		}
	}
	

	include("menu.php");
	//erzeugtes template ausgeben
	if (!$events_NoDisplay) { // manche events wollen den Header manipulieren
		$tpl->display("header.tpl");
	}
	// Menu Einbinden

/*	
if(!assocs("select * from clientScoring where id=".$status['id'])){
	$informationmeldung="<b>Das Syndicates-Team führt eine anonyme Umfrage zu Syndicates durch.<br>Dort habt ihr auch
	die Möglichkeit eure Meinung loszuwerden!<br><br>Als kleines Dankeschön verlosen wir unter allen Teilnehmern <br></b>
	1 Jahreskomplettpaket<br>
	10 Komplettpakete (2 Monate) <br>
	und 25 Forschungsassistenten (2 Monate)!<br><br>
	<a href=\"clientScoring.php\" target=\"_blank\">Jetzt teilnehmen</a>  -  <a href=\"clientScoring.php?stop=1\" target=\"_blank\">Nicht teilnehmen</a>";
}	
*/
//Tutorial by dragon12 R58
$current_tut = getCurrentTutorial($id);
if($current_tut) {
	require_once('tutorial.php');
} else {
	unset($current_tut);
}
//End Tutorial

if (!$events_NoDisplay) {
	Events::display();
}

if ($fehler) {
	$tpl->assign('ERROR', $fehler);
	$tpl->display('fehler.tpl');
}
if ($successmeldung) {
	$tpl->assign('MSG', $successmeldung);
	$tpl->display('sys_msg.tpl');
}
if ($informationmeldung) {
	$tpl->assign('INFO', $informationmeldung);
	$tpl->display('info.tpl');
}

if (isBasicServer($game)) {
    $tpl->assign('SERVER', 'basic');
} else {
    $tpl->assign('SERVER', 'classic');
}

?>
