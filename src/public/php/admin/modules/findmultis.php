<?

$self = "index.php";
$time = time();
$timepoint1 = time();
$dr = 0;
$user = (int) $user;

$ausgabe.="<h1>Mehrfach genutzte Ips der letzten 24 Stunden</h1>";

$safetime = $time - 60*60*24;

$sid24 = assocs("select * from sessionids_safe where angelegt_bei > $safetime");
$sidactual = assocs("select * from sessionids_actual");
foreach ($sidactual as $values) {
    $sid24[] = $values;
}

$ips24 = singles ("select distinct ip from sessionids_safe where angelegt_bei > $safetime");
$ipsactual = singles ("select distinct ip from sessionids_actual where angelegt_bei > $safetime");
foreach ($ipsactual as $value) {
    if (!in_array($value,$ips24)) {
        $ips24[] = $value;
    }
}

foreach ($sid24 as $values) {

// Wenn es IpVariable noch nicht gibt    
    if (!${$values[ip]}) {
        ${$values[ip]} = array($values[user_id]);
    }

// Wenn es IpVariable schon gibt
    else {
        if (!in_array($values[user_id],${$values[ip]})) {
            ${$values[ip]}[] = $values[user_id];
        }
    }
    
    // Wenn es Uservariable noch nicht gibt
    if (!${$values[user_id]}) {
        ${$values[user_id]} = array(ips => 1, lastloginstart => $values[angelegt_bei],lastloginende => $values[gueltig_bis],ipvalues => array($values[ip]));
    }
    
    // Wenn es Uservariable schon gibt
    else {
        if (!in_array ($values[ip],${$values[user_id]}[ipvalues])) {
            ${$values[user_id]}[ips]++;
            ${$values[user_id]}[ipvalues][] = $values[ip];
        }
        if (${$values[user_id]}[lastloginstart] < $values[angelegt_bei]) {
            ${$values[user_id]}[lastloginstart] = $values[angelegt_bei];
        }
        if (${$values[user_id]}[lastloginende] < $values[gueltig_bis]) {
            ${$values[user_id]}[lastloginende] = $values[gueltig_bis];
        }
    }
} // Ende Durchgang aller Sessionids


// Ausgabe
$ausgabe.="<p align=left>";

foreach ($ips24 as $ky => $value) {
    if (count($$value) > 1) {
		$nw = floor($nw); $invalidip = 0; $showit = 0;
		if ($nw): $invalidip = 1; endif;
		$ausgabe_temp = "";
        $useridinstring = " (";
        foreach ($$value as $userid) {
            $useridinstring.="$userid,";
        }
        $useridinstring = chopp($useridinstring);
        $useridinstring.=")";
        $ausgabe_temp.= "Mehrfach genutzte ip: $value   | $useridinstring<br>";
        $ausgabe_temp.="<table  width=\"100%\" cellspacing=0 cellpadding=0 style=\"border:1px solid\" border=1>
                    <tr>
                        <td width=\"30\" align=\"left\">#Ips</td>
                        <td width=\"100\" align=\"left\">Userid (Konzernid)</td>
                        <td width=\"130\" align=\"left\">Name</td>
                        <td width=\"150\" align=\"left\">Email</td>
                        <td width=\"100\" align=\"left\">Passwort</td>
                        <td width=\"120\" align=\"left\">Createtime</td>
                        <td width=\"130\" align=\"left\">Konzernname</td>
                        <td width=\"50\" align=\"left\">Vote</td>
                        <td width=\"50\" align=\"left\">IpMsgs</td>
                        <td width=\"120\" align=\"left\">Lastlogin</td>
                        <td width=\"80\" align=\"left\">Nw</td>
                    </tr>
                    <tr><td colspan=12 height=\"5\"></td></tr>
                    ";
        foreach ($$value as $user) {
			$useridinstring = addslashes($useridinstring);
            $ipmessages = single("select count(*) from messages where user_id in $useridinstring and sender = $user");
            $status = assoc("select status.*,users.* from status,users where status.id = $user and users.konzernid=$user and status.alive > 0");
			if ($nw and $status[nw] > $nw): $invalidip = 0; endif;
            $ct = date("d.m.y - H:i",$status{createtime});
            $lastloginstart= date("H:i:s",${$user}[lastloginstart]);
            $lastloginende= date("H:i:s",${$user}[lastloginende]);
            $ausgabe_temp.= "<tr ";
			if (!$isadmin) {
				$status[nachname] = "";
				$status[vorname] = "";
				$status[password] = "";
				$status[email] = "";
			}
			if ($mark && in_array($status[id],$mark)) { $ausgabe.="style=\"font-weight:bold\"";}

			$ausgabe_temp .=">
							<td>".${$user}[ips]."</td>
							<td>".$status[id]."($user)"."</td>
							<td>".$status{vorname}." ".$status{nachname}."</td>
							<td>".$status{email}."</td>
							<td>".$status{password}."</td>
							<td>$ct</td>
							<td>(#".$status{rid}.") ".$status{syndicate}."</td>
							<td>".$status{vote}."</td>
							<td>".$ipmessages."</td>
							<td>".$lastloginstart."<br>".$lastloginende." </td>
							<td>".pointit($status[nw])."</td>
						</tr>";
			$realuseridstring .= $status[id].",";
			if ($nodouble && !$through[$status[id]]): $showit = 1; endif;
			if ($nodouble): $through[$status[id]] = 1; endif;
			unset($status,$lastlogin,$ct,$ipmessages);
        }

        $ausgabe_temp.="</table>$realuseridstring<br><br>";
		$realuseridstring = "";
	if (!$invalidip and (!$nodouble or ($nodouble and $showit))): $ausgabe .= $ausgabe_temp; endif;
    }
}



$ausgabe."</p>";

$timepoint2=time();
$duration = $timepoint2-$timepoint1;
$ausgabe.="<br><br>Laufzeit: $duration Sekunden<br>Dbaufrufe: $dr";
?>
