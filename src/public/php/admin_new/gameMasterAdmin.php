<?

include("inc/general.php");

$uid = floor($uid);
$setPrivilegeLevel = floor($setPrivilegeLevel);
if ($setPrivilegeLevel < 0) $setPrivilegeLevel = 0;

$search = mysql_real_escape_string($search);
if (strlen($search) <= 2) $search = "";


if ($uid) {
	if ($pl >= 3 && $pl > $setPrivilegeLevel) {
		select("update users set privilege_level = $setPrivilegeLevel where id = $uid");
		s("Privilege Level des Users mit der ID $uid erfolgreich geändert auf: $setPrivilegeLevel");
	}
}

if (!$mode) $mode = "check";

$menupoints = 
  array("rechte" => "GMs verwalten", "checkset" => "GM-SetAktionen checken",
      "check" => "GM-Aktionen checken", "checkadmins" => "Admin-Logs anschauen");
foreach ($menupoints as $key => $p) {
  if ($mode == $key) {
    $menupoints[$key] = "<b><u>$p</u></b>";
  }
  $menupoints[$key] = "<a href=$self?mode=$key class=ver10s>".$menupoints[$key]."</a>";
}
$ausgabe_menu = join("&nbsp;&nbsp; - &nbsp;&nbsp;", $menupoints);



$currentGms = assocs("select * from users where privilege_level >= 1", "id");

if ($search) {
  if (strlen($search) >= 4 && $mode == "rechte")  $search = "%$search%";
  $currentGms_t = assocs("select * from users where username like '$search'", "id");
  if (!$currentGms_t) f("Keine Übereinstimmung mit dem Suchmuster gefunden");
  else $currentGms = $currentGms_t;
}

if ($mode == "rechte") {
  $ausgabe .= "<center><i>Folgende User haben Zugriffsrechte aufs Admin-Panel:</i></center><br>";


  $evalCode = "
  global \$pl;
  if (\$value < \$pl) {
	  \$temp = \"
	  <form action=$self>
	  <input type=hidden name=mode value=rechte>
	  <input type=hidden name=uid value=\".\$line[id].\">
	  <select name=setPrivilegeLevel>\";
	  for (\$i = 0; \$i < \$pl; \$i++) {
		  \$temp .= \"<option\".(\$value == \$i ? \" selected\":\"\").\">\$i\";
	  }
	  \$temp .= \"
	  </select>
	  <input type=submit value=change>
	  </form>\";
	  \$value = \$temp;
  }";
  setSpecialBehaviour("privilege_level", $evalCode);
  setSpecialBehaviour("createtime", "\$value = date(\"H:i:s\", \$value).\"<br>\".date(\"d. M. y\", \$value);");
  if (!$search) $ausgabe .= printAssocs($currentGms, "id, emogames_user_id, username, email, konzernid, startround, createtime, privilege_level");
  else $ausgabe .= printAssocs($currentGms, "id, emogames_user_id, username, konzernid, startround, createtime, privilege_level");

  $ausgabe .= "<br><br><center>User suchen nach username (mind. 3 Zeichen: exakte Suche; ab 4 Zeichen Teilstringsuche): <form action=$self><input type=hidden name=mode value=rechte><input type=text name=search><input type=submit value=search></form></center><br>";


  $ausgabe .= "<br><br>Legende für die Privilege-Levels:
  <br><b>0 = keine Zugriffsrechte</b> aufs Admin-Panel (http://syndicates-online.de/php/admin_new/)
  <br><b>1 = normaler Game-Master</b>; hat keinen Zugriff auf <u><b><i>Adminstuff</i></b></u> (siehe oben Links)
  <br><b>2 = Chef-Game-Master</b>, darf neue Game-Master ernennen und alte/inaktive Game-Master wieder zum normalen User machen
  <br><b>3 = Game-Master Supervisor</b>:<br><ul><li>kontrolliert die Game-Master.<li>Durch das einschränkende Rechtesystem kann er theoretisch allen Game-Mastern die Rechte wegnehmen und wieder adden. Das sollte er aber nur tun, wenn A) bei Personalwechsel einer GM-Stelle wenn der Chef-Game-Master krank ist oder B) der Chef-Game-Master selbst nach Absprache wechselt.<li>Sollte er einen Game-Master beim Missbrauch erwischen, ist mit dem Chef-Game-Master Rücksprache zu halten.<li>Darf und kann keine Game-Master-Tätigkeiten ausführen. Kann keine Konzerndaten auslesen (über Aktionshistory. Auch das Settingtool ist für ihn deaktiviert).<li>Da er keine Game-Master-Tätigkeiten ausübt wird er auch nicht in der ist-online-Liste im linken Rand aufgeführt.<li>Ansonsten identische Zugriffsrechte wie der Chef-Game-Master (nach Möglichkeit möchte ich den Zugriff auf die Tools der Game-Master soweit einschränken, als die Tools für die Kontrolle unbedingt benötigt werden. Mithilfe erwünscht, Kommentare/Vorschläge was weg kann bitte an Bogul schicken :-))</ul>
  <br><b>4 = Game-Admins</b>";
}
else if ($mode == "check") {
  $limit = floor($limit);
  if (!$limit) $limit = 100;
  $ausgabe .= "<center><table><tr><td><i><form action=$self><input type=hidden name=mode value=check>Die <input type=text value=$limit name=limit><input type=submit value=\"(aktualisieren)\"> letzten Log-Einträge der abgerufenen Spieler durch die GMs.<br>Einschränkung auf einzelnen GM<font color=red>*</font> : <input type=text name=search value=\"$search\"></form></i></td></tr></table></center><br>";
$view_history = assocs("select user_id, target_id, time, opened_actions_history from admin_user_view_history where user_id != $id order by time desc LIMIT $limit");

  foreach ($view_history as $key => $val) {
    if (!$currentGms[$val['user_id']])
      unset($view_history[$key]);
  }

  // User-ID
  $evalCode = "
    global \$currentGms;
    \$value = \$currentGms[\$value]['username'];";
  setSpecialBehaviour("user_id", $evalCode);

  // TIME
    setSpecialBehaviour("time", "
      global \$time;
      \$value = \"<nobr>\".date(\"H:i:s\", \$value).\"\".(\$time - \$value > 86400 ? (\$time - \$value > 2*86400 ? ', '.date(\"d. M. y\", \$value):', <b>gestern</b>'):'').\"</nobr>\";");

  // Target-ID
  $targets = assocs("select syndicate, rid, id from status", "id");
  $konids = assocs("select konzernid, username, id from users", "id");
  $oldest = end($view_history);
  $oldest = $oldest['time'];
  $ttime = $time - $oldest;
  setSpecialBehaviour("target_id", "
    global \$targets, \$konids, \$time, \$ttime, \$line; 
    \$konid = \$konids[\$value]['konzernid'];
    if (!\$konid) \$value = '<div class=ver10s><i>Kein Konzern (mehr?) vorhanden</i><br>User: '.\$konids[\$value]['username'].'</div>';
    else {
      if (\$line['time']) \$ttime = \$time - \$line['time'];
      \$value = \"<a href=traceuser.php?ia=trace&konid=\$konid&ttime=\$ttime target=_blank class=ver12s><font color=blue>\".\$targets[\$konid]['syndicate'].' (#'.\$targets[\$konid]['rid'].')</font></a>';
    }");
  
  // Game-Master-Konzern
  setSpecialBehaviour("id", "
    global \$targets, \$konids, \$time, \$ttime, \$line; 
    \$konid = \$konids[\$line['user_id']]['konzernid'];
      \$value = \"<div class=ver9s>\".\$targets[\$konid]['syndicate'].' (#'.\$targets[\$konid]['rid'].')</div>';
    ");

  setSpecialBehaviour("opened_actions_history", "\$value = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.(\$value == 0 ? '-': (\$value == 2 ? '<font color=orange>DIRECT / FOLLOW up</font>':'<font color=blue>JA</font>'));");
  $ausgabe .= printAssocs($view_history, "user_id as Game-Master, id as Game-Master-Konzern, target_id as Konzern ausgewählt, time as Datum, opened_actions_history as Aktionshistory angesehen");

  $ausgabe .= "<br><table width=70% align=center><tr><td>Hinweise: <ol><li><i>Aktionshistory angesehen</i> zeigt, ob der GM Zugriff auf die Konzerndaten und History hatte (<font color=blue>JA</font> bzw. <font color=orange>DIRECT / FOLLOW up</font>. <font color=orange>Letzteres</font> bedeutet, dass der Zugriff auf die Aktionshistory direkt erfolgt ist ohne den Spieler zuvor über die Suchmaske im Top-Frame bzw. durch Klick auf einen Konzern in einem Case auszuwählen. Dies passiert in der Regel durch Klicken auf Links innerhalb der Aktionshistory. Auffällig wird es, wenn vor dem ersten FOLLOW up kein normaler Zugriff (<font color=blue>JA</font>) erfolgt war). Ein - bedeutet, dass der Spieler nur über die Suchmaske bzw. aus einem Case heraus geladen wurde um z.B. die IP- bzw. Case-History einsehen zu können, d.h. der GM hat keine sensiblen spielrelevanten Daten abgerufen.<li>Es werden hier keine eigenen Logs angezeigt (Benutzer ".single("select username from users where id = $id").").<li>Bei Klick auf den Konzernnamen wird in einem neuen Fenster die Aktionshistory geladen. Dabei ist über die URL bereits eingestellt, dass nur die Aktionen ab dem GM-Zugriff angezeigt werden. Hiermit kann schnell stichprobenweise geprüft werden, ob der GM nach dem Aufruf mit dem Konzern interagiert hat (also das Panel missbraucht hat).<li>Im Zsh. mit 3. dient die Spalte Game-Master-Konzern dazu, in der Aktionshistory des angesehenen Spielers schnell nach dem Konzern des GMs suchen zu können (Copy->Search->Paste).<li><font color=red>*</font> Es werden nicht die letzten $limit Log-Einträge des ausgewählten GMs genommen, sondern nur der GM aus den letzten $limit Log-Einträgen von allen GMs herausgefiltert!</ol></td></tr></table>";
}
else if ($mode == "checkset") {
	$ausgabe="<center><table><tr><td width=200><b>gmid</b></td><td width=200><b>statusid</b></td><td width=80><b>what</b></td><td width=80><b>old</b></td><td width=80><b>new</b></td><td width=150><b>time</b></td></tr>";
	$data=assocs("SELECT * FROM `admin_set_history` order by time desc");
	foreach($data as $item){
		$gm3=single("SELECT konzernid FROM  users where id=".$item['gmid']);
		$gmer=assocs("select syndicate,rid from status where id=".$gm3);
		$gmer=$gmer[0];
		$gm2=single("SELECT username FROM  users where id=".$item['gmid']);
		$sper=assocs("select syndicate,rid from status where id=".$item['statusid']);
		$sper=$sper[0];
		$ausgabe.="<tr><td>".$gm2." - ".$gmer['syndicate']."(#".$gmer['rid'].")</td><td>".$sper['syndicate']."(#".$sper['rid'].")</td><td>".$item['what']."</td><td>".$item['old']."</td><td>".$item['new']."</td><td>".myTime($item['time'])."</td></tr>";
	
	}
	$ausgabe.="</table></center>";
} else if ($mode == 'checkadmins') {
	$ausgabe="<center><table><tr><td width=200><b>gmid</b></td><td width=\"80%\"><b>Logs</b></td><td width=150><b>time</b></td></tr>";
	$data=assocs("SELECT * FROM `admin_logs` order by time desc");
	foreach($data as $item){
		$gm3=single("SELECT konzernid FROM  users where id=".$item['user_id']);
		$gmer=assoc("select syndicate,rid from status where id=".$gm3);
		$gm2=single("SELECT username FROM  users where id=".$item['user_id']);
		$ausgabe.="<tr><td>".$gm2." - ".$gmer['syndicate']."(#".$gmer['rid'].")</td><td>".$item['content']."</td><td>".myTime($item['time'])."</td></tr>";
	}
	$ausgabe.="</table></center>";
} else {
}



echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body><center>
Willkommen im Admin Panel für die Game-Master Verwaltung<br>
$ausgabe_menu
<br><hr>
<br>
$fehler
$successmeldung
$informationmeldung</center>
$ausgabe
</body>

</html>";







?>
