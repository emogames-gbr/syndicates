<?php


//include("inc/general.php");
include("../../inc/ingame/game.php");

$data=assocs("SELECT time, money,metal*0.9,energy*0.9,sciencepoints*0.9 FROM `ressources` order by time desc");
$vortmp="";$aus="";
foreach($data as $line){
	$aus.="<tr>";
	foreach($line as $name=>$value){
		$value = ($name=="time") ? myTime($value) : $value;
		$aus.="<td>$value</td>";
		if(!$vor) $vortmp.="<td>$name</td>";
	}
	if(!$vor) $vor=$vortmp;
	$aus.="</tr>";
}

echo "<table border=1><tr>$vor</tr>$aus</table>";

?>
