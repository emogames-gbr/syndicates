<?php
	//header ("Content-type: image/png");
	echo $code;
	$file = "codes/".$code."/".$pid.".png";
	image2wbmp($file);
?>
