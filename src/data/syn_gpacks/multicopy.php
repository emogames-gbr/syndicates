<?
$bad = array(100,101,102,103,125);
$bad = array();
for ($i=1;$i < 120;$i++) {
	//if (!in_array($i,$bad)) {
		//if (!file_exists($i))
		system("cp Syn*.png  ./$i");
	//}
}


?>
