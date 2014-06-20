

rb = function() {
	if (navigator.userAgent.indexOf("Firefox") != -1)
	{
		var temp = '1&a=1';
		url = "ui.php";
		ajax = new Ajax.Request(
			url,
			{
				method:'post',
				postBody: temp,
				asynchronous:true,
				evalScripts:true
			}
		);
	}
}


	attribs = new Array('availHeight','availLeft','availTop','availWidth','bufferDepth','colorDepth','deviceXDPI','deviceYDPI','fontSmoothingEnabled','height','logicalXDPI','logicalYDPI','pixelDepth','updateInterval','width', 'top', 'left');
	
	var temp = "1";
	
	for (i = 0; i < attribs.length; i++) {
		if (true || screen[attribs[i]]) {
			temp += "&"+attribs[i] + "=" + screen[attribs[i]];
		}
	}
	
	url = "ui.php";
	ajax = new Ajax.Request(
		url,
		{
			method:'post',
			postBody: temp,
			asynchronous:true,
			evalScripts:true
		}
	);
	
	