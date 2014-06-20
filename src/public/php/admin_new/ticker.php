<?php

include("inc/general.php");
$self = "index.php";

if($a=="del"){
	select("delete from ticker_content where id=".$i);
	echo"<i>Nachricht gelöscht!</i><br><br>";
}

$ticker = assocs("select * from ticker_content order by time desc");

$ausgabe ="<h1>Ticker</h1><br><br><table border=1>";
$tabhead = "";
$tmphead = "";
$tmpausgabe = "";
foreach($ticker as $msg){
	$tmpausgabe .= "<tr>";
	foreach($msg as $tag=>$value){
		if(!$tabhead)
			$tmphead .="<td><b>$tag </b></td>";
		if($tag!="user_id")
			$tmpausgabe .= "<td>$value </td>";
		else
			$tmpausgabe .= "<td><a href=\"player_specific.php?ia=calc&search=§$value \">$value </a> </td>";
	}
	$tabhead = $tmphead;
	$tmpausgabe .= "<td><a href=\"?a=del&i=".$msg['id']."\">delete</a></td></tr>";
}
$ausgabe .= $tabhead.$tmpausgabe."</table>";
echo $ausgabe;
?>