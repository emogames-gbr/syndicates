<?
$userid=(int)$userid;
$konzernid = (int)$konzernid;

$self = "index.php";

$ausgabe.="<h1>User ID angeben!</h1>";
$addmessage = "";

if ($duration) {
    $duration *= 60*60;
    $banned_till = $time + $duration;
}

if (!$userid) {
    $ausgabe.="
            <br>User bannen:<br>
            <form action=$self method = \"post\">
            <input name=action value=tempban type=hidden>
            User-Id: <input type=\"text\" size=\"3\" name=\"userid\">
            <input type=\"submit\" value=\"Konzerndaten holen\">
            <input type=\"hidden\" name=\"actn\" value=\"ban\">
            </form>
           ";
}

elseif ($userid && !$banfinal) {
    // Daten zum betroffenen user holen
    //$gemeldetuserid = single("select id from users where konzernid = $gemeldet[id]");
    //$melderuserid = single("select id from users where konzernid = $status[id]");
    list($konzernid,$already) = row("select konzernid,banned from users where id = $userid");
    if ($konzernid) {
    $deleted = single("select deleted from users where id = $userid");
    $status = assoc("select * from status where id = $konzernid");
    $vergehen = assocs("select * from admin_users_punished where user_id = $userid");
    if ($already) {
        $bis = date("d.m.y u\m H:i \U\h\\r",$already);
        $ausgabe.="<br><strong>User ist bereits bis zum $bis gebannt!</strong> <br><br>";
    }
    $ausgabe.="
                <b>Konzern Bannen:</b><br>
                <form actn=$self method=\"post\">
                <input name=action value=tempban type=hidden>
                Grund_Intern:<textarea name=\"reason\">Beleidigung von Mitspielern</textarea><br><br>
                Grund_User:<textarea name=\"userreason\">Beleidigung von Mitspielern</textarea><br><br>
                <select name=duration>
                <option value=12>12 Stunden</option>
                <option value=24>24 Stunden</option>
                <option value=36>36 Stunden</option>
                <option value=48>48 Stunden</option>
				<option value=72>72 Stunden</option>
				<option value=96>4 Tage</option>
				<option value=120>5 Tage</option>
				<option value=144>6 Tage</option>
				<option value=168>7 Tage</option>
				<option value=336>2 Wochen</option>
                <option value=-1>-1 Stunden - Ban aufheben</option>
                </select>
                <input type=\"hidden\" name=\"banfinal\" value=\"konzern\">
                <input type=\"hidden\" name=\"userid\" value=\"$userid\">
                <input type=\"hidden\" name=\"konzernid\" value=\"$konzernid\">
                <input type=\"hidden\" name=\"actn\" value=\"ban\">
                <input type=\"submit\" value=\"Ban\">
                </form>
                <br><br>
                Daten zum betroffenen Konzern (".$status{syndicate}." (#".$status{rid}.")):<br>
                Konzerne dieses Spielers wurde bereits <b>$deleted mal</b> gelöscht<br>
                <br><br>
                Bisherige Vergehen:<br><br>
                <table width=70% class=\"normal\" style=\"border: 1px solid\">
                    <tr>
                    <td>Datum</td>
                    <td>Grund</td>
                    <td>Aktion</td>
                    </tr>
                ";
                foreach ($vergehen as $temp) {
                    $datum = date("d.m.y",$temp[time]);
                    $ausgabe.="
                                <tr>
                                    <td style=\"color:red\">$datum</td>
                                    <td style=\"color:red\">$temp[reason]</td>
                                    <td style=\"color:red\">$temp[action]</td>
                                </tr>
                    ";
                    unset($datum);
                }
                $ausgabe.="</table><br><br>";
                foreach ($status as $key => $value) {
                    $ausgabe.="$key: $value<br>";
                }

    }
    else {
        $ausgabe.="Konzern nicht gefunden";
    }
}

elseif ($userid && $banfinal && $duration) {
        if (!$reason) {$ausgabe.="bitte grund angeben";}
        else {
            $ausgabe.="User $userid banned for ".($duration/3600)."hours";
            $was = "tempban ".($duration/3600)."hours";
            select("insert into admin_users_punished (user_id,time,reason,action,adminuser) values ($userid,$time,'$reason','$was','$adminuser')");
            select("update users set banned=".$banned_till." where id = ".$userid);
            select("update status set lastlogintime=$time where id = $konzernid");
            list($vorname,$nachname,$mail) = row("select vorname,nachname,email from users where id = $userid");
            
            $to = $vorname." ".$nachname;
            $betreff = "Ihr Konzern wurde vom Spiel ausgeschlossen";
            $bis2 = date("d.m.y u\m H:i \U\h\\r",$banned_till);
            if ($userreason) {
                $addmessage = "\n\nGrund: $userreason\n";
            }
            $message="Ihr Syndicates Konzern wurde wegen Verstoßes gegen die Nutzungsbedingungen bis zum $bis2 vom Spiel ausgeschlossen.";
            $message.="$addmessage"."\nWeitere Verstöße gegen die Nutzungsbedingungen können den permanenten Ausschluss vom Spielgeschehen zur Folge haben.\n\n Das Syndicates Entwicklerteam\n http://syndicates-online.de";
            sendthemail($betreff,$message,$mail,$to);
            //echo "Time: $time";
                    ## Sessionid sichern und anschließend löschen
            
            $sessionid_data = row("select sessionid, angelegt_bei, gueltig_bis, ip, user_id from sessionids_actual where user_id='".$konzernid."'");
            if (count($sessionid_data) > 0 && $sessionid_data[1] > 0)	{
                if ($sessionid_data[2] > $time) {
                    $endtime = $time;
                }
                else {
                     $endtime = $sessionid_data[2];
                }
                $actn = "insert into sessionids_safe 
                           (sessionid, angelegt_bei, gueltig_bis, ip, user_id)
                            values
                           ('".$sessionid_data[0]."',
                            ".$sessionid_data[1].",
                            ".$endtime.",
                            '".$sessionid_data[3]."',
                            ".$sessionid_data[4].")
                           ";
                select($actn);
                $actn = "delete from sessionids_actual where sessionid='".$sessionid_data[0]."'";
                select($actn);
            }
    }

}

?>
