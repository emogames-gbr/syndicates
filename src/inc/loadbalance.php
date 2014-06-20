<?

##################
define (MAX_LOAD,3);
##################




if (SERVERNAME != "Alturo1" && SERVERNAME != "NGZ-1-(Athlon XP 3000+)") {
	if ($ref_src) {
		$refadd ="&ref_src=$ref_src";
	}
	if ($loginkey) {

		$refadd2 = "&loginkey=$loginkey";
	}
	$ngzload = single("select sload from serverload where server ='NGZ-1-(Athlon XP 3000+)' order by time desc limit 1");
	if ($ngzload < MAX_LOAD) {

		header("location: http://213.202.214.184/syndicates/index.php?1=1$refadd$refadd2");
	}
}

?>
