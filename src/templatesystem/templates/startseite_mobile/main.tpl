{strip}<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Syndicates - Das Browsergame - Wenn Konzerne die Welt regieren</title>
	<link rel="shortcut icon" href="images/syn_favicon.ico" />
	<link rel="stylesheet"  href="js/jquery.mobile-1.0.min.css" />
	<link rel="stylesheet" href="mobile.css" />
	<link rel="stylesheet" href="style_mobile.php" />
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script src="js/jquery.mobile-1.0.min.js"></script>
	<script src="js/plugins.js"></script>
	{literal}<script type="text/javascript">
		var changeViewport = function () {
			if (window.orientation == 90 || window.orientation == -90)
				$('meta[name="viewport"]').attr('content', 'height=device-width,width=device-height,initial-scale=1.0,maximum-scale=1.0');
			else
				$('meta[name="viewport"]').attr('content', 'height=device-height,width=device-width,initial-scale=1.0,maximum-scale=1.0');
		}
		window.addEventListener('orientationchange', changeViewport, true);
		try { changeViewport(); } catch (err) { }
		
		var num;
		var news = false;
		var loading = false;
		$('.page').live('pagecreate',function(event){
			num = 1;
			news = false;
			if($(window).width() > 500){
				$('.synlogo_mobile').attr('src', 'images/startseite/synlogo_mobile_big.png');
			}
			if($(window).width() > 1000){
				var pagehtml = $(this).html();
				$(this).html('');
				$('<div>', {css : {position : 'relative',  width : '1000px', left : '50%', 'margin-left' : '-500px', 'border-left' : '1px solid #000', 'border-right' : '1px solid #000'}, html : pagehtml }).appendTo($(this));
			};
		});
		$('#page_news').live('pagecreate',function(event){
			news = true;
		});
		$(window).scroll(function(){
			if($(window).scrollTop()+120 >= $(document).height() - $(window).height()){
				if(news && !loading){
					loading = true;
					$.mobile.showPageLoadingMsg();
					$.ajax({ 'url' : 'index.php?ajax=true&action=load_more_news&num='+(num++) })
					.success(function(data){
						$('#news_list').append(data).listview('refresh');
						loading = false;
						$.mobile.hidePageLoadingMsg();
					});
				}
			}
		});
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>{/literal}
</head>

<body>
<div data-role="page" id="page_{$ACTION}" data-theme="a" class="page">
	{if preg_match('/news/', $ACTION) || $ACTION == 'sonstiges' || $ACTION == 'stats' || $ACTION == 'impressum'}<div data-role="header" id="header">
		{include file="header.tpl"}
	</div>{/if}
	<div data-role="navbar" id="navbar">
		{include file="navbar.tpl"}
	</div>
	<div data-role="content" id="content">
		{include file="content.tpl"}
	</div>
	<div data-role="footer" id="footer">
		{include file="footer.tpl}
	</div>
</div>

{literal}
<script type="text/javascript">
	$('[data-role=page]').live('pageshow', function (event, ui) {
		try {
			var pageTracker = _gat._getTracker("UA-745697-5");
			pageTracker._setCustomVar(1, "Mobile Version", "Outgame", 1);
			pageTracker._trackPageview();
		} catch(err) { }
	});
</script>
{/literal}
</body>
</html>{/strip}