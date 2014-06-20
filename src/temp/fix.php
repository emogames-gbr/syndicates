<?

require("../includes.php");
connectdb();

$b = assocs("select * from buildings");

foreach ($b as $temp) {
	echo ($temp["name_intern"]."+");
}

?>

