<?
/*

2010 by R4bbiT

$_GET['id'] => KonzernID
$_GET['type'] => Typ der Anzeige (siehe switch-statement)
$_GET['css'] => css-code fόr Farbwahl und Grφίe

*/

require_once("../../inc/ingame/game.php");
ob_clean();
$show = getallvalues($_GET['id']);
switch ($_GET['type']){
	case 'land':
	case 'rid':
	case 'nw':
		$string = pointit($show[$_GET['type']]);
		break;
	case 'rulername':
	case 'syndicate':
		$string = $show[$_GET['type']];
		break;
	default:
		$string = 'not allowed';
}

if($_GET['css']){
	$str = implode(' ',file('../../data/syn_gpacks/'.$status["gpack_id"].'/style.css'));
	
	$str = str_replace(' ', '', $str);
	$str = str_replace(chr(0x0009), '', $str);
	
	$str = explode($_GET['css'].'{', $str);
	$str = explode('}', $str[1]);
	$css = $str[0];
	
	preg_match('/(\n|\r|\n\r)color:(.+);/i', $css, $back);
	$color = $back[2];
	
	preg_match('/font-size:(.+)px;/i', $css, $back);
	$fontsize = $back[1];
}
else{
	$color = false;
}

if($color != ''){
	if ($color[0] == '#')	$color = substr($color, 1);
	
	if (strlen($color) == 6){
		list($r, $g, $b) = array($color[0].$color[1],
								 $color[2].$color[3],
								 $color[4].$color[5]);
	}
	else if(strlen($color) == 3){
		list($r, $g, $b) = array($color[0].$color[0],
								 $color[1].$color[1],
								 $color[2].$color[2]);
	}
	else{
		list($r, $g, $b) = array(0, 0, 0);
	}
	
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
}
else{
	$r = 0;
	$g = 0;
	$b = 0;
}
/*
$fontsize = ($fontsize > 3 ? round($fontsize * 0.75) : 9);
$font = './arial.ttf';

$bbox = imagettfbbox($fontsize, 0, $font, $string);
$width = abs($bbox[2] - $bbox[0]) + 1;
$height = abs($bbox[7] - $bbox[1]);

$img = imagecreatetruecolor($width, $height);
imagesavealpha($img, true);

$font_color = imagecolorallocate($img, $r, $g, $b);
imagefilledrectangle($img, 0, 0, $width, $height, $font_color);
$trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $trans_colour);

imagettftext($img, $fontsize, 0, 0, $fontsize, $font_color, $font, $string);
*/
$fontsize = 3;
	
$x = imagefontwidth($fontsize)*strlen($string);
$y = imagefontheight($fontsize);

$img = imagecreate($x,$y);
$black = imagecolorallocate($img, 133, 133, 133);
imagecolortransparent($img, $black);
$text_color = imagecolorallocate($img, $r, $g, $b);
imagestring($img, $fontsize, 0, 0, $string, $text_color);

header ("Content-type: image/gif");
imagegif($img);
imagedestroy($img);


?>