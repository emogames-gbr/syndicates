{literal}
function count(timeleft,id) {
	hours = parseInt(timeleft / 3600) % 60;
	minutes = parseInt(timeleft /60) % 60;
	seconds = timeleft % 60;
	
	hours < 0 ? hours = 0 : 1;
	minutes < 0 ? minutes = 0:1;
	seconds < 0 ? seconds = 0:1;
	
	if (String(hours).length < 2) hours = '0'+hours;
	if (String(minutes).length < 2) minutes = '0'+minutes;
	if (String(seconds).length < 2) seconds = '0'+seconds;
	
	var hourstring = 'a'+id+'h';
	var minutesstring = 'a'+id+'m';
	var secondsstring = 'a'+id+'s';

	document.getElementById(hourstring).innerHTML = hours+':';
	document.getElementById(minutesstring).innerHTML = minutes+':';
	document.getElementById(secondsstring).innerHTML =seconds;
}
{/literal}