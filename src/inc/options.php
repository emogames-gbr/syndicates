<?
//pvar($game);
$loginkey = my_encrypt(($userdata[emogames_user_id])).createkey();
EMOGAMES_prepare_Login($userdata[emogames_user_id],$loginkey);
exit();

?>



<b><? echo "$optionendot"; ?> Optionen</b><br>

<?
$showmain=1;
$formstart="			<form style=\"margin:0px\" action=index.php method=post>
						<input size=14 type=hidden name=action value=options>
			";
$formend = "</form>";

// Erlaubte ias:
$ias = array("killaccount","mailchange","pwchange");
if (!in_array($ia,$ias)) {unset($ia);}
$status = assoc("select * from status where id = $userdata[konzernid]");

//////////////////////////////
//////////////////////////////
// Passwort ändern:         //
//////////////////////////////
//////////////////////////////

if($ia == "pwchange") {
	$pwold = addslashes($pwold);
	$pwconf = addslashes($pwconf);
	$pwnew = preg_replace("/'|\"/","",$pwnew);
	$pwnew = addslashes($pwnew);
	if ($pwold != $userdata[password]) {
		f("Sie haben ihr altes Passwort nicht korrekt eingegeben");
	}
	elseif (!$pwnew) {
		f("Sie haben kein neues Passwort eingegeben");
	}
	elseif ($pwconf != $pwnew) {
		f("Die neuen Passwörter stimmen nicht überein");
	}
	else {
		select("insert into options_pwchange (pwbefore,pwafter,time,user_id) values ('$userdata[password]','$pwnew',$time,$userdata[id])");
		select("update users set password='$pwnew' where id=$userdata[id]");
		s("Ihr Passwort wurde erfolgreich geändert");
		$userdata[password] = $pwnew;
	}
}

//**********************************************************
//                         Changepath
//**********************************************************

if ($inner != "changepath") {$inner = "";}
if ($inner === "changepath") {
	$path = preg_replace("/--/","",$path);
	$path = preg_replace("/#/","",$path);
	$path = preg_replace("/\\\/","/",$path);
	select("update status set imagepath='$path' where id = $status[id]");
	$status[imagepath] = $path;
}

//////////////////////////////
//////////////////////////////
// Emailadresse ändern:     //
//////////////////////////////
//////////////////////////////

elseif ($ia == "mailchange") {
	if ($userdata[password] != $pwconf) {
		f("Sie haben ihr Passwort nicht korrekt eingegeben");
	}
	else {
		$emailnew = addslashes($emailnew);
		if (!checkmail ($emailnew)) {
			f("Bitte geben sie eine gültige Emailadresse ein");
		}
		else {
			select("insert into options_mailchange (emailbefore,emailafter,time,user_id) values ('$userdata[email]','$emailnew',$time,$userdata[id])");
			select("update users set email='$emailnew' where id=$userdata[id]");
			verification($userdata[id],"mailchange");
			$userdata[email] = $emailnew;
			s("Ihre Emailadresse wurde erfolgreich geändert, sie erhalten in kürze eine Bestätigungsmail");
		}
	}
}

//////////////////////////////
//////////////////////////////
// Account löschen:		    //
//////////////////////////////
//////////////////////////////

elseif ($ia == "killaccount") {
	if (!$final) {
		if ($userdata[password] != $pwconf) {
			f("Sie haben ihr Passwort nicht korrekt eingegeben");
		}
		elseif ($delconf != "on") {
			f("Sie haben die Löschung ihres Accounts nicht bestätigt");
		}
	}
	elseif($final == "true" && $userdata[id] && $sid) {
		$queries = array();
		$globals=assoc("select * from globals order by round desc limit 1");
		if ($userdata[konzernid]) {
			$status = assoc("select * from status where id = $userdata[konzernid]");
		}
		if (!$status[rid])  {$status[rid] = 0;}
		if ($userdata[konzernid]) {
			kill_den_konzern($userdata[konzernid]);
			$action = ("delete from status where id=$userdata[konzernid]");
			array_push($queries,$action);
		}
	    $action = ("insert into options_accountdelete (user_id,syndicate,username,time,rid) values ($userdata[id],'$status[syndicate]','$userdata[username]',$time,'$status[rid]')");
		array_push($queries,$action);
		$action = ("delete from users where id=$userdata[id]");
		array_push($queries,$action);
		//$action = ("delete from ".$globals{statstable}." where round=$globals[round] and user_id=".$userdata[id]);
		array_push($queries,$action);
		$userdata = "";
		$sid = "";
		$autologinkey = "";
		db_write($queries);
		$showmain = 0;
		s("Ihr Account wurde erfolgreich gelöscht");
	}
}



// Standardausgabe
if ($showmain) {
$ausgabe = "
<br>
<table class=rand cellspacing=1 cellpadding=0 width=500>
	<tr>
		<td>
			<table class=head cellpadding=4  cellspacing=0 width=100%>
				<tr>
					<td>Optionen für <i>$userdata[username]</i></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=subhead cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td align=left>
						$formstart
						<input type=hidden name=ia value=pwchange>
						Passwort ändern:
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td width=200>
						Altes Passwort:
					</td>
					<td width=140>
						<input size=14 name=pwold type=password>
					</td>
					<td width=160 align=right>
					</td>
				</tr>
				<tr>
					<td width=200>
						Neues Passwort:
					</td>
					<td width=140>
						<input size=14 name=pwnew type=password>
					</td>
					<td width=160 align=right>
					</td>
				</tr>
				<tr>
					<td width=200>
						Passwort bestätigen:
					</td>
					<td width=140>
						<input size=14 name=pwconf type=password>
					</td>
					<td width=160 align=right>
						<input type=submit value=Ändern>
						$formend
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=subhead cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td align=left width=150>
						$formstart
						<input type=hidden name=ia value=mailchange>
						Emailadresse ändern:
					</td>
					<td align=right width=350>
						(die neue Emailadresse muss wieder bestätigt werden)
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td width=200>
						Neue Emailadresse:
					</td>
					<td width=300 align=left colspan=2>
						<input size=14 name=emailnew>
					</td>
				</tr>
				<tr>
					<td width=200>
						Passwort zur Bestätigung:
					</td>
					<td width=140 align=left>
						<input size=14 size=14 name=pwconf type=password>
					</td>
					<td width=160 align=right>
						<input size=14 type=submit value=Ändern>
						$formend
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=subhead cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td align=left width=200>
						$formstart
						<input type=hidden name=ia value=killaccount>
						Lokalen Grafikpfad ändern:
					</td>
					<td align=left width=200>
					</td>
					<td align=right width=100>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=6  cellspacing=0 width=100%>
				<tr>
					<td width=200>
						Pfad: <input type=hidden name=inner value=changepath>
					</td>
					<td width=200 align=left>
						<input name=path size=25 value=\"$status[imagepath]\">
					</td>
					<td width=100 align=right>
						<input type=submit value=ändern>
						$formend
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=subhead cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td align=left width=120>
						$formstart
						<input type=hidden name=ia value=killaccount>
						Account löschen:
					</td>
					<td align=right width=380>
						(ihren Konzern können sie im Spiel unter 'Optionen' löschen)
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=6  cellspacing=0 width=100%>
				<tr>
					<td width=500>
						Account löschen:&nbsp;
						<input type=checkbox style=\"margin:0px\" name=delconf>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=body cellpadding=3  cellspacing=0 width=100%>
				<tr>
					<td width=200>
						Passwort zur Bestätigung:
					</td>
					<td width=140 align=left>
						<input size=14 size=14 name=pwconf type=password>
					</td>
					<td width=160 align=right>
						<input size=14 type=submit value=Ändern>
						$formend
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table class=foot cellpadding=4  cellspacing=0 width=100%>
				<tr>
					<td width=500>
						&nbsp;
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
";
} // If showmain

echo $fehler;
echo $successmeldung;
echo $ausgabe;

?>
