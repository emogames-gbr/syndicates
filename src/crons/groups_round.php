<?


ob_clean();

require_once("../includes.php");
$handle = connectdb();


if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);

exit("DIESES SKRIPT IST VERALTET UND WIRD NICHT MEHR BENÖTIGT! inok1989 30.12.2012");

$max_users = 20;
$queries = array();

$results = assocs("select * from groups_round");

$out = array();
foreach($results as $result){
	$sql_tag = array();
	$sql_val = array();
	if($result["u1"] >= 1){
		foreach(range(1, $max_users) as $vl){
			if($result["u".$vl."_status"] != 1){
				$result["u".$vl] = 0;
			}
			if($result["u".$vl] == $result["nachfolger"]){
				$result["nachfolger"] = $vl;
				$nachf = true;
			}
		}
		if(!$nachf){
			$result["nachfolger"] = 0;
		}
		foreach($result as $tag => $val){
			if($tag != "description" && $tag != "group_id"){
				if(is_numeric($val)){
					$sql_tag[] = $tag;
					$sql_val[] = $val;
				}
				else{
					$sql_tag[] = $tag;
					$sql_val[] = "'".mysql_real_escape_string($val)."'";
				}
			}
		}
		$username = single("select username from users where emogames_user_id = ".$result["u1"]);
		select("insert into groups (is_mentor_group, password, ".implode(", ", $sql_tag)." ) VALUES (0, '".$username."', '".implode("', '", $sql_val)."')");
		echo "\n neue Gruppe:\n";
		echo "Chef: ".$username."\n\n";
	}
}



?>
