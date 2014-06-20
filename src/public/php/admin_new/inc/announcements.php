<?

if ($adminlevel > 2) {
	if ($inneraction == 1)	{
		$anm = preg_replace("/'/", "\'", $anm);
		$name = addslashes($name);
		$headline = preg_replace("/'/", "\'", $headline);
        if ($new ==="new") {
    		if ($announcement_type === "ingame" || $announcement_type === "both") {
                $queries[] = "update globals set announcement='$anm' where round=".$globals[round];
            }
            $queries[] = "insert into announcements (time,headline,content,poster,type) values ($time,'$headline','$anm','$name','$announcement_type')";
    		$ausgabe .= "<center><h1><font color=red>Announcement hinzugefügt</font></h1></center>";
    		unset ($action);
        }
        elseif ($new === "edit") {
            $announcement_id = single("select announcement_id from announcements order by time desc limit 1");
            $queries[] = "update announcements set time=$time,headline='$headline',content='$anm',poster='$name',type='$announcement_type' where announcement_id=$announcement_id ";
    		$ausgabe .= "<center><h1><font color=red>Announcement geändert</font></h1></center>";
    		unset ($action);
        }
	}
	else	{
		list($anm,$temptype,$headline,$poster) = row("select content,type,headline,poster from announcements order by time desc limit 1");
		$ausgabe .= "<center>
                        Aktuelles Announcement:<br><br><br>
                        <form action=index.php method=post>
                        
							<select name=new>
								<option value=edit>Aktuelles Announcement editieren
								<option value=new>Neues Announcement erstellen
							</select><br><br>
							<table width=100%><tr>
							<td align=left width=33%>Poster:<input name=name value=\"$poster\"></td>
							<td align=center width=33%>Überschrift:<input name=headline value=\"$headline\"></td>
							<td align=right width=33%></td>
							</tr></table>
							<input type=hidden name=action value=announcements>
							<input type=hidden name=inneraction value=1>
							<textarea name=anm cols=80 rows=10>$anm</textarea><br><br>
							Announcement Typ festlegen:
							<select name=announcement_type>
								<option value=ingame>Ingame Announcement
								<option value=outgame ";if ($temptype === "outgame") {$ausgabe.= "selected ";} $ausgabe.=">Outgame Announcement
								<option value=both ";if ($temptype === "both") {$ausgabe.= "selected ";} $ausgabe.=">Both
							</select>
                        
                        	<input type=submit value=abschicken>
                        </form><br><br>
                        <a href=javascript:history.back()>zurück, ohne Veränderungen!</a>
                    </center>";
	}
	if ($queries): db_write($queries); endif;
}

?>