<?
session_start();
/* * *
 * Dient zur Generierung von Captchas
 * by Jonathan Hasenfuß - R4bbiT - 2011
 * * */
if($_SESSION['s_captcha'] == 't9H)sd(6)Y'){
	$sid = $_SESSION['s_sid'];
	$userdata = $_SESSION['s_userdata'];
	
	$_SESSION['s_captcha'] = false;
	
	require_once("../includes.php");
	require_once(LIB."picturemodule.php"); // F?r Logincode
	require_once(LIB."mod_login.php"); // F?r Logincode
	require_once(INC."style.php");
	connectdb();
	
	$code_id = getcodeid();
	
	$code = single("select code from codes where code_id=".$code_id);
	
	showusercode($userdata['konzernid'],$code_id);
}
else{
	$code = 'error';
}



ob_clean();


$str = str_split($code);
$im = @imagecreate (86, 25)
		or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
$r = rand(50, 255);
$g = rand(50, 255);
$b = rand(50, 255);
$background_color = imagecolorallocate($im, $r, $g, $b);
$text_color = imagecolorallocate($im, 0, 0, 0);
$y = 1;
$x = 10;
if($code != 'error'){
	foreach($str as $s){
		$x += rand(0,17);
		imagestring($im, 5, $x, $y+rand(0,10), $s, $text_color);
		$x += 8;
	}
}
else{
	imagestring($im, 5, $x, $y+rand(0,10), $code, $text_color);
}
header ("Content-type: image/png");
imagepng($im);
imagedestroy($im);

?>