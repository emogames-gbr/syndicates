<?

include("inc/general.php");

$data = assocs("select count(*) as sum, time from peak_tracker group by time order by time asc");
echo"<table>";
foreach($data as $t=>$v){
	echo"<tr><td>".$v['time']."</td><td>".$v['sum']."</td></tr>";
}
echo"</table>";

exit;

 	$subject = $k."Content Update zu Runde 55 bei Syndicates ++ EMOs geschenkt";		# Betreff
    $mailmessage = "lalalalalalal12334";					# Body-Message-Text
    $receiver = "admin@DOMAIN.de";						# Empf?ger (Mailadresse)
    $to = "admin@DOMAIN.de";								# AN: - Teil
	EMOGAMES_send_mail($subject,$mailmessage,$receiver,$to);
	
	echo "done";


exit;
$groups = assocs("SELECT group_id, is_mentor_group, open, u1,u1_status,u2,u2_status,u3,u3_status,u4,u4_status,u5,u5_status, u6,u6_status,u7,u7_status,u8,u8_status,u9,u9_status,u10,u10_status, u11,u11_status,u12,u12_status,u13,u13_status,u14,u14_status,u15,u15_status, u16,u16_status,u17,u17_status,u18,u18_status,u19,u19_status,u20,u20_status, ( ( CASE u1 WHEN 0 THEN 0 ELSE CASE u1_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u2 WHEN 0 THEN 0 ELSE CASE u2_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u3 WHEN 0 THEN 0 ELSE CASE u3_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u4 WHEN 0 THEN 0 ELSE CASE u4_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u5 WHEN 0 THEN 0 ELSE CASE u5_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u6 WHEN 0 THEN 0 ELSE CASE u6_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u7 WHEN 0 THEN 0 ELSE CASE u7_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u8 WHEN 0 THEN 0 ELSE CASE u8_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u9 WHEN 0 THEN 0 ELSE CASE u9_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u10 WHEN 0 THEN 0 ELSE CASE u10_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u11 WHEN 0 THEN 0 ELSE CASE u11_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u12 WHEN 0 THEN 0 ELSE CASE u12_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u13 WHEN 0 THEN 0 ELSE CASE u13_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u14 WHEN 0 THEN 0 ELSE CASE u14_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u15 WHEN 0 THEN 0 ELSE CASE u15_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u16 WHEN 0 THEN 0 ELSE CASE u16_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u17 WHEN 0 THEN 0 ELSE CASE u17_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u18 WHEN 0 THEN 0 ELSE CASE u18_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u19 WHEN 0 THEN 0 ELSE CASE u19_status WHEN 0 THEN 1 ELSE 0 END END) + ( CASE u20 WHEN 0 THEN 0 ELSE CASE u20_status WHEN 0 THEN 1 ELSE 0 END END) ) AS num FROM groups ORDER BY is_mentor_group DESC, num DESC");

$num = 0;
$numfea = 0;

$features = 0;
$rounds = array();
$isGroup = array();

for($i=1;$i<=60;$i++){
	if($i>11 and $i<16){
		$rounds['12-15']=0;
		$roundsfea['12-15']=0;
	} else {
		$rounds[$i]=0;
		$roundsfea[$i]=0;
	}
}

foreach($groups as $key => $value){

	for($i=1;$i<=20;$i++){
	
		$id = $value['u'.$i];
		$alive = single("select alive from status where id=".$id);
		$features =  single("select count(*) from features where konzernid=".$id);
		
		
		if($alive!=0){
			$num++;
			$features > 0 ? $numfea++ : 0;
			$startround = single("select startround from users where konzernid=".$id);
			if($startround==""){
				$startround="12-15";
			}
			$rounds[$startround]++;
			$features > 0 ? $roundsfea[$startround]++ : 1;
			$isGroup[$startround]++;
		}
	
	}

}

$all = single("select count(*) from status where alive!=0");
$allfea = single("select COUNT(DISTINCT konzernid) from features");

echo "In Gruppen: ".$num." (".$numfea.")<br>";
echo "Aktiv: ".$all." (".$allfea.")<br>";
echo "Randoms: ".($all-$num)." (".($allfea-$numfea).")<br><br>";

$n=0;
$k=0;
$l=0;

foreach($rounds as $key => $value){

	if($value!=0)
		echo "R".$key." -> ".$value." c-P: ".($n+=$value)."|".(round(100*($n/$num),2))."%|c-fP:".($k+=$roundsfea[$key])."|c-gP:".($l+=$isGroup[$key])."<br>";
		
}

?>
