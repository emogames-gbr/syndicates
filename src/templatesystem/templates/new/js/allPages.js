{literal}
var now = new Date();
var h   = new String();
var min = new String();
var sec = new String();

now.setTime({/literal}{$SCRIPTTIME}{literal});

function mout(x,c) {
	document.getElementById(x).className = c;
}

function mover(x,c) {
	document.getElementById(x).className = c;
}

function showover(event,xAdj,yAdj) {
	if (event.pageX) myX = event.pageX;
	if (event.clientX) myX = event.clientX;
				
	if (event.pageY) myY = event.pageY;
	if (event.clientY) myY = event.clientY;				
	if (!xAdj) xAdj = 30;
	if (!yAdj) yAdj = 30;
					
	myX += parseInt(xAdj) - parseInt(document.body.scrollLeft);
	myY -= parseInt(yAdj) - parseInt(document.body.scrollTop);
				
	elem = document.getElementById('over');
	elem.style.top = myY;
	elem.style.left = myX;
	elem.style.display = 'inline';
}
		    
function hideover() {
	elem = document.getElementById('over');
	elem.style.display = 'none';
}
		    
function contentover(content) {
	document.getElementById('over_inner').innerHTML= content;
}

function info(type, name)	{
	neuesFenster =  open('description.php?type='+type+'&name='+name, 'Info','width=400, height=300, scrollbars=yes');
	neuesFenster.focus()
};

function werbung_nachladen() {
	if (typeof werbung != "undefined") {
		document.getElementById('werbung').src = werbung;
	}
}

function showTime() {        
	if (document.getElementById)
	{
		h   = '' + now.getHours();
		min = '' + now.getMinutes();
		sec = '' + now.getSeconds();
		if (h.length   == 1) h   = '0' + now.getHours();
		if (min.length == 1) min = '0' + now.getMinutes();
		if (sec.length == 1) sec = '0' + now.getSeconds();
		document.getElementById('zeit').innerHTML ='&nbsp;'+ h + ':' + min + ':' + sec;
		now.setSeconds(now.getSeconds() + 1);
		window.setTimeout('showTime();', 1000);
	}
}
{/literal}