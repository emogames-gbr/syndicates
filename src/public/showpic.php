<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Syndicates: Bild</title>
	<LINK REL="stylesheet" HREF="style.css" TYPE="text/css">
	<script>
	<!--
		function check(wt,ht) {
			w = wt+50;
			h = ht+110;
			if(w>(screen.availWidth-50)) {
			  w = screen.availWidth-50;
			}
			if(h>(screen.availHeight-20)) {
			  h = screen.availHeight-20;
			}
			if(w<350) {
			  w = 350;
			}
			if(h<200) {
			  h = 200;
			}
			if(wt>1 && ht>1) {
				window.resizeTo(w, h);
			} else {
				if(document.shot.complete == true) {
					w = document.shot.width;
					h = document.shot.height;
					window.setTimeout("check("+w+","+h+")",100);
				} else {
					window.setTimeout("check(-1,-1)",100);
				}
			}
			window.moveTo((screen.availWidth/2)-(w/2),(screen.availHeight/2)-(h/2));
		}
	// -->
	</script>
</head>
<body bgcolor=#405169 text=#ffffff leftmargin=0 rightmargin=0 topmargin=0 marginheight=0 marginwidth=0 onLoad="focus();">

<div align="center"><br>
<table cellspacing=0 cellpadding=8 border=0>
<tr>
	<td><a href="javascript:opener.focus(); window.close();"><img name="shot" src="<?=WWWDATA?>images/<? echo $_GET["picdatei"];?>" border="0"></a></td>
</tr>
</table>
</div>
<script>
<!--
check(400,461);
// -->
</script>

</body></html>
