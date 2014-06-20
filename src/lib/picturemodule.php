<?php

// Author: Jannis Breitwieser
// created: 310104
// last changed: 310104
// requires: subs.php, mysql connection, aktivierten mt srand
//$docroot = "";
$expath = DATA."codes";
define(PATH,WWWDATA."codes");

#mt_srand(time()); // aktivieren, falls nicht schon vorher aktiviert wurde


function makeimage($string,$filename) {
	//header ("Content-type: image/png");
	$max = 18;
	$im = ImageCreate ($max, $max) or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
	$background_color = ImageColorAllocate ($im, 255, 255, 255);
	$text_color = ImageColorAllocate ($im, 0, 0, 0);
	$pixel_color = imagecolorallocate($im,200,200,200);
	//imagecolortransparent($im,$text_color);
	//$im = imagerotate($im,1,$background_color);
	for ($i=0;$i < 10*$max; $i++) {
		$rand1 = mt_rand(0,$max);
		$rand2 = mt_rand(0,$max);
		imagesetpixel($im,$rand1,$rand2,$pixel_color);
	}
	$rand3 = mt_rand(2,6);
	$rand4 = mt_rand(0,4);
	//imagepsslantfont(4,0);
	ImageString($im,5,$rand3,$rand4,$string,$text_color);
	//ImageString ($im, 5, 5, 5, "Ein Test-String", $text_color);
	//touch("test.png");
	ImagePNG ($im,$filename);
	//ImagePNG ($im);
}

function create_enviroment() {
	$codestableexists = mysql_query("select time from codes limit 1");
	if ($a = mysql_error()) {
		echo $a." Creating new Table";
		mysql_query("create table codes (time int, code_id int primary key auto_increment,code varchar(255))");
	}
	$codelogsexists = mysql_query("select time from codelogs");
		if ($a = mysql_error()) {
		echo $a." Creating new Table";
		mysql_query("create table codelogs (time int, code_id int,user_id int,action tinyint)");
	}
	$pathexists = file_exists("$expath");
	if (!$pathexists) {
		mkdir($expath);
	}
}

function create_code($string) {
	global $expath;
    if (func_num_args() > 1) {$alternative_path = func_get_arg (1);}
	$savestring = $string;
	if (!$time) {$time = time();}
		select("insert into codes (time,code) values ($time,'$savestring')");
		$codeid = single("select code_id from codes order by time desc limit 1");
		$dir = $expath."/".$codeid;
	/*
	elseif ($alternative_path) {
		select("insert into codes_anmeldung (time,code) values ($time,'$savestring')");
		$codeid = single("select code_id from codes_anmeldung order by time desc limit 1");
		global $docroot;
		$dir = $docroot.$alternative_path."/".$codeid;
	}
	*/
	mkdir($dir);
	// Array erzeugen
	$sarray = array();
	while (($a = strlen($string)) > 0) {
		$sarray[] = $string[0];
		$string = substr($string,1,($a-1));
	}

	$i = 0;
	foreach ($sarray as $value) {
		$filename = $dir."/".$i.".png";
		makeimage($value,$filename);
		$i++;
	}
	return $codeid;
}

// Returns html to show the code,false if code cant be read
function showcode($code_id) {
    if (func_num_args() > 1) {$alternative_path = func_get_arg (1);}
	$code_id = (int) $code_id;
	$return = "";
	$codepath = PATH."/".$code_id;
	$filepath = DATA."codes/".$code_id;
	if ($alternative_path): global $docroot; $codepath = $docroot.$alternative_path."/".$code_id; endif;
	$codeexists = file_exists($filepath);
	if (!$codeexists) {return "false";}
	// Wenn code existiert
	else {
		$i=0;
		$pic=($filepath."/".$i.".png");
		while(file_exists($pic)) {
			$pic=($filepath."/".$i.".png");
			$i++;
		}
		$i--;
		$codelength = $i;
		for ($a = 0;$a < $codelength; $a++) {
			$pic=($codepath."/".$a.".png");
			$return.="<img src=\"$pic\">";
		}
		return $return;
	}
}

// Zeigt code an und speichert, im logstable, dass der code dem user angezeigt wurde
function showusercode($user_id,$code_id) {
    if (func_num_args() > 2) {$alternative_path = func_get_arg (2); $user_id = addslashes($user_id);}
	else { $user_id = (int) $user_id; };
	$code_id = (int) $code_id;

	if (!$time) {$time = time();}
	if (!$alternative_path) { select("insert into codelogs (user_id,code_id,time,action) values ($user_id,$code_id,$time,0)"); 
		return showcode($code_id);
	}
	elseif ($alternative_path) { select("insert into codelogs_anmeldung (user_id,code_id,time,action) values ('$user_id',$code_id,$time,0)"); return showcode($code_id, $alternative_path);};

}


function getcodeid() {
	$number = 5;
	if (func_num_args() > 0) { if(func_get_arg(0)): $number = func_get_arg(0); endif;}
    if (func_num_args() > 1) { $alternative_path = func_get_arg (1); }
	if (!$alternative_path) { $cids = singles ("select code_id from codes order by time desc limit $number"); }
	elseif ($alternative_path) { $cids = singles ("select code_id from codes_anmeldung order by time desc limit $number"); }
	$rand = mt_rand(0,($number-1));
	return $cids[$rand];
}

// Gibt 0 zurück, wenn false, sonst 1
function checkcode($code_id,$input) {
    if (func_num_args() > 2) { $alternative_path = func_get_arg (2); }
	$input = addslashes($input);
	$code_id = (int) $code_id;
	if (!$alternative_path) { $code = single("select code from codes where code_id=$code_id"); }
	elseif ($alternative_path) { $code = single("select code from codes_anmeldung where code_id=$code_id"); }
	if ($code === $input) {
		return 1;
	}
	else return 0;
}

function checkusercode($user_id,$code_id,$input) {
    if (func_num_args() > 3) { $alternative_path = func_get_arg (3); $user_id = addslashes($user_id); }
	else { $user_id = (int) $user_id; }
	$code_id = (int) $code_id;$input = addslashes($input);
	if (!$alternative_path) {
		$result = checkcode($code_id,$input);
		if (!$time) {$time = time();}
		if ($result) {
			//select("insert into codelogs (user_id,code_id,time,action) values ($user_id,$code_id,$time,1)");
			return 1;
		}
		else {
			//select("insert into codelogs (user_id,code_id,time,action) values ($user_id,$code_id,$time,-1)");
			return 0;
		}
	}
	elseif ($alternative_path) {
		$result = checkcode($code_id,$input,$alternative_path);
		if (!$time) {$time = time();}
		if ($result) {
			select("insert into codelogs_anmeldung (user_id,code_id,time,action) values ('$user_id',$code_id,$time,1)");
			return 1;
		}
		else {
			select("insert into codelogs_anmeldung (user_id,code_id,time,action) values ('$user_id',$code_id,$time,-1)");
			return 0;
		}
	}
}

?>
