{strip}<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://opengraph.org/schema/"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="resource-type" content="document">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="4 days">
<meta name="classification" content="Entertainment">
<meta name="description" content="{if $POST.news_short}{$POST.news_short}{else}Einen Großkonzern führen, nach der Weltherrschaft greifen, die Konkurrenz mit schmutzigen Tricks ins Abseits manövrieren und das alles bei nur 15 Minuten Spielzeit am Tag? Problemlos möglich bei unserem kostenlosen Multiplayerspiel Syndicates.{/if}">
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta name="keywords" content="RPG, Online, Game,Spiele, Onlinespiel, Onlinespiele, Spiel, Massive, User,browserbasiert,Fantasy, Fantasyspiel,Multiplayer,browser,spiel,online,mehrspieler,mmorpg,browserspiel,game,browsergame,kostenlos,free,action">
<meta name="robots" content="FOLLOW">
<meta name="distribution" content="Global">
<meta name="rating" content="Safe For Kids">
<meta name="copyright" content="BETREIBER">
<meta name="author" content="BETREIBER">
<meta http-equiv="reply-to" content="">
<meta name="language" content="german">
<meta name="doc-type" content="Web Page">
<meta name="doc-class" content="Completed">
<meta name="doc-rights" content="Copywritten Work">
<meta property="fb:app_id" content="{$FB_APP_ID}" />
<title>Syndicates - Das Browsergame - Wenn Konzerne die Welt regieren</title>
<link rel="stylesheet" type="text/css" href="style.php?action={$ACTION}" />
<style type="text/css">
{include file="../../../public/new_var.css"}
</style>
<link rel="shortcut icon" href="images/syn_favicon.ico" />
<link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 1100px)" href="new_mobile.css" />
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="new_ie.css" />
<![endif]-->
<!--[if lt IE 8]>
<style type="text/css">{literal}
	.content { position:absolute; top:0px; }
	.foot { position:absolute; top:500px; display:none; }
	.circle .input span { display:none; }
	.circle .input {  background-image:url(images/startseite/input_circle.png); border:1px solid #000; outline:none; padding:5px 7px 0px 7px; background-color:transparent; width:126px; height:21px; }
</style>{/literal}
<![endif]-->

<link rel="stylesheet" type="text/css" href="./js/fancybox/jquery.fancybox-1.3.4.css" />
<link rel="stylesheet" type="text/css" href="./js/plugins.css" />
{if $ACTION == 'stats' || $ACTION == 'hof' || $ACTION == 'own_stats'}
<link rel="stylesheet" type="text/css" href="js/jqplot/jquery.jqplot.min.css" />{/if}
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">{literal}{lang: 'de'}{/literal}</script>
<script type="text/javascript" src="http://connect.facebook.net/de_DE/all.js"></script>
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
<script type="text/javascript" src="js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>
{if $ACTION == 'stats' || $ACTION == 'hof' || $ACTION == 'own_stats'}
<script type="text/javascript" src="js/jqplot/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="js/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
<!--[if lt IE 9]><script type="text/javascript" src="./js/jqplot/excanvas.min.js"></script><![endif]-->{/if}

{include file="_js.min.tpl"}

</head>

<body>
<noscript>
<div class="js-warning"><strong>Achtung</strong><br />Um diese Seite, und damit das Spiel Syndicates, nutzen zu können, wird Javascript benötigt. Bitte aktiviere es in deinen Browsereinstellungen oder erlaube es im jeweiligen Add-On</div>
</noscript>
{if $IE_WARNING != 'true'}<div class="ie_warning"><strong>Achtung</strong><br />Sie verwenden eine Version des Internet Explorers, die <a href="http://de.wikipedia.org/wiki/HTML5" target="_blank">HTML5</a> und <a href="http://de.wikipedia.org/wiki/Cascading_Style_Sheets" target="_blank">CSS3</a> nicht korrekt unterstützt.<br />Bitte updaten Sie ihren Internet Explorer auf mindestens Version 9 (<a href="http://windows.microsoft.com/de-DE/internet-explorer/products/ie/home" target="_blank">Zum Download</a>)<hr width="50%" /><a href="#" id="ie_warning_link">Meldung verstecken</a></div>{/if}

<div class="alert_box">{foreach from=$ERRORS item=ERROR}<div class="alert">{$ERROR}</div>{/foreach}</div>

<div id="fb-root"></div>
{literal}
<script type="text/javascript">
FB.init({appId: '{/literal}{$FB_APP_ID}{literal}', status: true, cookie: true, xfbml: true, channelUrl  : 'http://syndicates-online.de/channel.php', oauth : true});
FB.Event.subscribe('auth.login', function(){  });
FB.Event.subscribe('auth.logout', function(){ window.location = '?action=logout'; });
</script>
{/literal}

{include file="circle.tpl"}

{include file="social_box.tpl"}

{include file="content.tpl"}

{include file="footer.tpl"}

{include file="toolbar.tpl"}
{literal}
<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	try {
		var pageTracker = _gat._getTracker("UA-745697-5");
		pageTracker._trackPageview();
	} catch(err) {}
</script>
{/literal}
</body>
</html>{/strip}
