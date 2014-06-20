<?

///
///	Define Gpacks
///



$templ = assoc("select * from templates where template_id= ".$status[template_id]." and visible=1");
if (!$templ && !$template_id) {
	$template_id = single("select template_id from templates where default_tpl='1'");
	select("update status set template_id=".$template_id." where id=".$status[id]);
	if (!$template_id) $template_id=1;
	$templ = assoc("select * from templates where template_id=".$template_id);
}

if(IS_MOBILE > 0 && false){
	$tpl->setTemplateSet('mobile');
}
else{
	setTemplatePaths($templ);
}

if (!isKsyndicates()) {
	
	$gpack = assoc("select * from gpacks where gpack_id= ".$status[gpack_id]." and template_id= ".$status[template_id]." and visible=1");

	if (!$gpack['gpack_id'] && !$gpack_id) {
		$gpack_id = single("SELECT gpack_id FROM users WHERE konzernid = '".$status['id']."'");
		if (!$gpack_id) {
			$gpack_id = single("select gpack_id from gpacks where race_default='".$status['race']."' and template_id=".$status[template_id]."");
		}
		select("update status set gpack_id='$gpack_id'  where id='$status[id]'");
		if (!$gpack_id) $gpack_id=1;
		$gpack = assoc("select * from gpacks where gpack_id=$gpack_id");
	}
} else {
		$gpack_id = single("select gpack_id from gpacks where race_default='k_".$status['race']."'");
		if ($k_gpack_id) $gpack_id = mysql_real_escape_string($k_gpack_id);
		$gpack = assoc("select * from gpacks where gpack_id=$gpack_id");
}


setGPathsFromGpack($gpack);

// Pfad wird gerade geändert
if ($inner == "changepath") {
	$path = preg_replace("/--/","",$path);
	$path = preg_replace("/#/","",$path);
	$path = preg_replace("/\\\/","/",$path);
	$status[imagepath] = $path;
}
if ($status[imagepath] && ! $dontusepacket) {
	$layout["images"] = "file://localhost/$status[imagepath]/";
	$layout["colorcss"] = "file://localhost/$status[imagepath]/";
	$ripf = "file://localhost/$status[imagepath]/";
	pvar($layout);
}
?>
