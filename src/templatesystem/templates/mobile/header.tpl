{strip}<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{$PAGE_TITLE}</title>
	<link rel="shortcut icon" href="images/syn_favicon.ico" />
	<link rel="stylesheet"  href="/js/jquery.mobile-1.0.min.css" />
	<link rel="stylesheet" href="/mobile.ingame.css" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
	<script src="/js/jquery.mobile-1.0.min.js"></script>
	<script src="/js/plugins.js"></script>
	{literal}<script type="text/javascript">
		var changeViewport = function () {
			if (window.orientation == 90 || window.orientation == -90)
				$('meta[name="viewport"]').attr('content', 'height=device-width,width=device-height,initial-scale=1.0,maximum-scale=1.0');
			else
				$('meta[name="viewport"]').attr('content', 'height=device-height,width=device-width,initial-scale=1.0,maximum-scale=1.0');
		}
		window.addEventListener('orientationchange', changeViewport, true);
		try { changeViewport(); } catch (err) { }
		
		$(document).delegate("#page", "pagecreate", function(){
			$('.menu_button').click(function(){
				show_nav();
			});
			$('#nav a').tap(function(){
				$('#nav li').removeClass('active');
				$(this).find('li').addClass('active');
				hide_nav();
			});
			$('#nav li').removeClass('active');
			$('#page_{/literal}{$PAGE}{literal}').find('li').addClass('active');
			$('.mitteilungen_badge').tap(function(){
				
			});
			$('.nachrichten_badge').tap(function(){
				
			});
			update_box($('.box'));
		});
		$(window).bind('resize orientationchange', function(){
			update_box($('.box'));
		});
		
		
		function hide_nav(){
			$('#nav').animate({left: '-265px'}, 500, 'easeInExpo', function(){ $('.nav_back').remove(); });
			//document.ontouchmove = function(event){};
		}
		function show_nav(){
			$.mobile.silentScroll(0);
			$('<div>',{'class' : 'nav_back'}).click(function(){ hide_nav(); }).appendTo('body');
			$('#nav').animate({left: '-0px'}, 500, 'easeOutExpo');
			//document.ontouchmove = function(event){ event.preventDefault(); };
		}
		function update_box(that){
			var dw = $(document).width();
			if(dw > 390){
				var num = 90 - 90 / 320 * (320-dw);
				$(that).css('width', num+'%');
				$(that).css('margin-left', -($(that).width()-(dw-320)/10)/2+'px');
			}
			else{
				$(that).css('width', '90%').css('margin-left', '-45%');
			}
		}
		
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>{/literal}
</head>

<body>

<div id="mitteilungen_box" class="box">
	<section>
		mitteilungen
	</section>
</div>

<div id="nachrichten_box" class="box">
	<section>
		hallo
	</section>
</div>

<nav id="nav">
	<ul>
		<a href="statusseite.php" id="page_statusseite"><li>Statusseite</li></a>
		<a href="gebaeude.php" id="page_gebaeude"><li>Gebäude & Land</li></a>
		<a href="militaerseite.php" id="page_militaerseite"><li>Militär</li></a>
		<a href="forschung.php" id="page_forschung"><li>Forschung</li></a>
		<a href="mitteilungen.php" id="page_mitteilungen"><li>Mitteilungen</li></a>
		<a href="nachrichten.php" id="page_nachrichten"><li>Nachrichten</li></a>
		<a href="syndboard.php" id="page_syndboard"><li>Synd. Board</li></a>
		<a href="market.php" id="page_market"><li>Global Market</li></a>
		<a href="pod.php" id="page_pod"><li>Lager & Transfer</li></a>
		<a href="aktuelles.php" id="page_aktuelles"><li>Aktuelles</li></a>
	</ul>
</nav>

<section id="page" data-role="page" data-theme="a" class="page">
	<header data-role="header" style="overflow:visible;">
		<h1 style="margin:0px; padding:10px 0px;overflow:visible;">
			<div class="tab">
				<section style="text-align:left;">
					<button class="menu_button">Menü</button>
				</section>
				<section>
					<span class="badgeOuter">
						<img src="images/ingame_mobile/mitteilung.png" />
						{if $MENU_NEW_MAIL}<div class="badge mitteilungen_badge">{$MENU_NEW_MAIL}</div>{/if}
					</span>
				</section>
				<section>
					<span class="badgeOuter">
						<img src="images/ingame_mobile/nachrichten.png" />
						{if $MENU_NEW_MSG_NUM}<div class="badge {$MENU_NEW_MSG_CATEGORY} nachrichten_badge">{$MENU_NEW_MSG_NUM}</div>{/if}
					</span>
				</section>
				<section class="ress">
					<span class="ress">
						<div id="ressCr">{$CREDITS}<span>Cr</span></div>
						<div id="ressNrg">{$ENERGY}<span>MWh</span></div>
					</span>
					<span class="ress">
						<div id="ressErz">{$MINERALS}<span>t</span></div>
						<div id="ressFp">{$SCIENCEPOINTS}<span>P</span></div>
					</span>
				</section>
			</div>
		</h1>
	</header>
	<div data-role="content" id="content">
{/strip}