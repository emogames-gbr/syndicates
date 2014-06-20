<?php

require_once("../../inc/ingame/game.php");

require_once("../../inc/ingame/header.php");

if (IsTorExitPoint()) {
echo"Sie nutzen einen Proxy und dürfen deswegen keine Aktionen durchführen. Sollte diese Meldung irrtümlich erscheinen, bitte Christian- per Foren-PN kontaktieren. <a href=\"http://board.emogames.de/pms.php?action=newpm&userid=53756\">(hier klicken)</a>.";
}else{
echo"is okay du bob";
}
imageAlphaBlending($im, true);
imageSaveAlpha($im, true);
imagepng($im);
imagedestroy($im);

function IsTorExitPoint(){
if (gethostbyname(ReverseIPOctets($_SERVER['REMOTE_ADDR']).".".$_SERVER['SERVER_PORT'].".".ReverseIPOctets($_SERVER['SERVER_ADDR']).".ip-port.exitlist.torproject.org")=="127.0.0.2") {
return true;
} else {
return false;
} 
}
function ReverseIPOctets($inputip){
$ipoc = explode(".",$inputip);
return $ipoc[3].".".$ipoc[2].".".$ipoc[1].".".$ipoc[0];
}


require_once("../../inc/ingame/footer.php");


?>