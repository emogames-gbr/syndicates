<?

$self = "index.php";

$ausgabe.="<h1>User ID angeben!</h1>";
$userid=(int)$userid;
$konzernid = (int)$konzernid;


if (!$userid) {
    $ausgabe.="
            <br>User konzernbeschreibung löschen:<br>
            <form action=$self method = \"get\">
            User-Id: <input type=\"text\" size=\"3\" name=\"userid\">
            <input type=\"submit\" value=\"Konzerndaten holen\">
            <input type=\"hidden\" name=\"action\" value=\"delsettings\">
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
	$settings = assoc("select * from settings where id=$konzernid");
    $vergehen = assocs("select * from admin_users_punished where user_id = $userid");
    if ($already) {
        $bis = date("d.m.y u\m H:i \U\h\\r",$already);
        $ausgabe.="<br><strong>User ist bereits bis zum $bis gebannt!</strong> <br><br>";
    }
    $ausgabe.="
                <b>Konzernbeschreibung löschen:</b><br>
                <form action=$self method=\"get\">
                Grund_Intern:<textarea name=\"reason\">Unpassende Konzernbeschreibung</textarea><br><br>
                Grund_User:<textarea name=\"userreason\">Unpassende Konzernbeschreibung</textarea><br><br>
                <input type=\"hidden\" name=\"banfinal\" value=\"konzern\">
                <input type=\"hidden\" name=\"userid\" value=\"$userid\">
                <input type=\"hidden\" name=\"konzernid\" value=\"$konzernid\">
                <input type=\"hidden\" name=\"action\" value=\"delsettings\">
                <input type=\"submit\" value=\"Löschen\">
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
				$ausgabe.="<b>Konzernbeschreibung:</b><br><br>";
				foreach ($settings as $key => $value) {
					if ($key == "description") $key = "<b>Beschreibung</b>";
					$ausgabe.="$key: $value<br>";
				}
				$ausgabe.="<br>";
                foreach ($status as $key => $value) {
                    $ausgabe.="$key: $value<br>";
                }

    }
    else {
        $ausgabe.="Konzern nicht gefunden";
    }
}

elseif ($userid && $banfinal) {
        if (!$reason) {$ausgabe.="bitte grund angeben";}
        else {
            $ausgabe.="Konzernbeschreibung gelöscht";
            $was = "Konzernbeschreibung löschen";
            $desc = single("select description from settings where id=$konzernid");
            select("insert into admin_users_punished (user_id,time,reason,action,adminuser,casedata) values ($userid,$time,'$reason','$was','$adminuser','$desc')");
            
            select("update settings set description='',kategorie='' where id=$konzernid");
            list($vorname,$nachname,$mail) = row("select vorname,nachname,email from users where id = $userid");
            }

}
?>
