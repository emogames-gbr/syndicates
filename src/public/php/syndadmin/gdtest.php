<?
require("../../../includes.php");
connectdb();
mysql_select_db("syndicates_basic");
set_time_limit(3600);
echo "Skript gestartet<br>";


									



	list($konzernname, $land, $nw, $rid, $fraktion) = row("select syndicate, land, nw, rid, race from status where id = $konzernid");
	$username = single("select username from users where konzernid = $konzernid");
	$rank = single("select count(*) from status where nw > $nw")+1;





?>