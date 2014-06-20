<?php

/*
CREATE TABLE  `admin_ip_partners` (
 `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `partnership` SMALLINT NOT NULL ,
 `emoid` SMALLINT NOT NULL ,
 `time` TEXT NOT NULL ,
 `reason` TEXT NOT NULL
) TYPE = MYISAM ;
*/


include("inc/general.php");
$self = "index.php";

if($_GET['error'])
	echo "Error: ".$_GET['error']."<br><br>";


if($_GET['action']=="deleteIt"){

	$emoid = $_GET['emoid'];
	
	if(!is_numeric($emoid))
		$emoid=single("select emogames_user_id from users where username='".$emoid."'");
		
	if(!$emoid || $emoid=="0"){
		header("Location: setIpPartners.php?error=WrongUser!");
	} else {	
		select("delete from admin_ip_partners where partnership='".$_GET['pid']."' and emoid='".$emoid."'");
		header("Location: setIpPartners.php?cache=".time());
	}
}

if($_GET['action']=="deleteIn"){
	select("delete from admin_ip_partners where partnership='".$_GET['pid']."'");
	header("Location: setIpPartners.php?cache=".time());
}

if($_GET['action']=="insertIn"){

	$emoid = $_POST['emoid'];

	if(!is_numeric($emoid))
		$emoid=single("select emogames_user_id from users where username='".$emoid."'");
		
	if(!$emoid || $emoid=="0"){
		header("Location: setIpPartners.php?error=WrongUser!");
	} else {
		select("insert INTO admin_ip_partners (partnership,emoid,time,reason) VALUES ('".$_GET['pid']."','".$emoid."','".time()."','".$_POST['reason']."')");
		header("Location: setIpPartners.php?cache=".time());
	}
}

if($_GET['action']=="newPs"){

	$emoid = $_POST['emoid'];

	if(!is_numeric($emoid))
		$emoid=single("select emogames_user_id from users where username='".$emoid."'");
		
	if(!$emoid || $emoid=="0"){
		header("Location: setIpPartners.php?error=WrongUser!");
	} else {	
		select("insert INTO admin_ip_partners (partnership,emoid,time,reason) VALUES ('".++$last."','".$emoid."','".time()."','".$_POST['reason']."')");
		header("Location: setIpPartners.php?cache=".time());
	}
}

echo
	'<style type="text/css">
	.auto-style1 {
		font-family: Verdana, Geneva, Tahoma, sans-serif;
	}
	</style>

	<script type="text/javascript">
		function deleteIt(partid,emoid){
			window.location.href = "?action=deleteIt&pid="+partid+"&emoid="+emoid;
		}
		function deleteIn(partid){
			window.location.href = "?action=deleteIn&pid="+partid;
		}
		function insertIn(partid){
			document.getElementById("insertIn"+partid).style.display = "";
		}
	</script>';

if(!$_GET['action']){

	$data = assocs("SELECT * FROM  `admin_ip_partners` ORDER BY partnership");
	$shipdata = array();

	foreach($data as $user){
		if(!$shipdata[$user["partnership"]]){
			$shipdata[$user["partnership"]] = array();
		} 
		array_push($shipdata[$user["partnership"]], $user);
	}

	echo '<table style="width: 600px">';

	foreach($shipdata as $tag=>$value){

		echo
			'<tr>
				<td style="width: 550px" bgcolor="#808080" class="auto-style1">Ip-Partnership '.$tag.'</td>
				<td style="width: 25px"><img src="insert.png" onclick="insertIn('.$tag.')" title="User hinzufügen"/></td>
				<td style="width: 25px"><img src="delete.png" onclick="deleteIn('.$tag.')" title="Partnership auflösen"/></td>
			</tr>
			<tr id="insertIn'.$tag.'" style="display:none;">
				<td colspan="3">
					<form action="setIpPartners.php?action=insertIn&pid='.$tag.'" method="post">
						<input type="text" value="emoid" name="emoid">
						<input type="text" value="reason" name="reason">
						<input type="submit" value="Einfügen">
					</form>
				</td>
			</tr>';
		
		foreach($value as $user){
		
			$color =  $color ? 'bgcolor="#C0C0C0"' : '';
			$username = single("select username from users where emogames_user_id=".$user["emoid"]);
		
			echo 
				'<tr $color>
					<td style="width: 550px; height: 20px;" class="auto-style1">'.myTime($user["time"]).': '.$username.' ('.$user["reason"].')</td>
					<td style="width: 25px; height: 20px;"></td>
					<td style="width: 25px; height: 20px;"><img src="delete.png" onclick="deleteIt('.$tag.','.$user['emoid'].')" title="User entfernen"/></td>
				</tr>';
			
		}
		
		echo '<tr><td colspan=3><br></td></tr>';
		$last = $last < $tag ? $tag : $last;
		
	}	
		
	echo '</table>';
	
	echo '<br><br><form action="setIpPartners.php?action=newPs&last='.$last.'" method="post">
		<input name="emoid" value="first user" type="text"><br>
		<input name="reason" value="reason" type="text"><br>
		<input type="submit" value="Partnership einrichten">
		</form>';
}
?>