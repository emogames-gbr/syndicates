<?
/*
CREATE TABLE  `admin_set_history` (
 `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `gmid` INT NOT NULL ,
 `statusid` INT NOT NULL ,
 `what` TEXT NOT NULL ,
 `old` TEXT NOT NULL ,
 `new` TEXT NOT NULL ,
 `time` TEXT NOT NULL
) TYPE = MYISAM ;

INSERT INTO  `admin_pages` (  `id` ,  `kategorie` ,  `showposition` ,  `name` ,  `dateiname` ,  `visible` ,  `privilege_level` ) 
VALUES (
NULL ,  'Adminstuff',  '3',  'Settingtool',  'settingtool.php',  '1',  '2'
);



*/

include("inc/general.php");


if ($pl == 3) {
  exit('Als Game-Master Supervisor hast du leider kein Recht auf dieses Modul zuzugreifen. Damit du trotzdem weißt, was das Modul macht: der Chef-Game-Master hat damit die Möglichkeit bei beliebigen Konzernen Daten (aus der "status"-Tabelle, also quasi nahezu alles) beliebig zu ändern.');
}

$self = "index.php";

$time = time();
$userThis = (int) $_GET['konid'];

if($userThis==0){
	echo"<form action=\"settingtool.php\" method=\"get\"><input type=\"text\" name=\"konid\" value=\"konzid\"><input type=\"submit\" value=\"start\"></form>";
	exit;
}

if($_GET['action']=='set'){
	if($_POST['old']!=$_POST['new']);
	select("update status SET ".$_GET['what']."=".$_POST['new']." where id=".$userThis);
	select("INSERT INTO admin_set_history(gmid, statusid, what, old, new, time) VALUES ('".$id."', '".$userThis."', '".$_GET['what']."','".$_POST['old']."','".$_POST['new']."','".time()."')");
}

$status = assocs("select * from status where id = $userThis");
select("INSERT INTO  `admin_user_view_history` (user_id ,target_id,time) VALUES ('".$id."',  '".$userThis."',  '".time()."')");

echo"<h1>status edit of status.id = $userThis </h1><br><br><table>";

foreach($status[0] as $tag=>$value){
	echo "<form action=\"?action=set&what=".$tag."&konid=".$userThis."\" method=\"post\"><tr><td>".$tag."</td><td>".$value."</td><td>
		<input id=\"new\" name=\"new\" value=\"".$value."\" type=\"text\"><input id=\"old\" name=\"old\" value=\"".$value."\" type=\"hidden\">
		<input type=\"submit\" value=\"change\"</td></form></tr>";
}

echo "</table>";

?>
