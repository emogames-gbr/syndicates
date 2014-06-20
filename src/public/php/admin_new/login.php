<?

require_once ("../../../includes.php");
connectdb();
$adminlevel = 0;
$time = time();
$globals = assoc("select * from globals order by round desc limit 1");
list($usec,$sec) = explode(" ",microtime()); $init = (($sec-1000000000) + round($usec*1000)/1000)*1000; mt_srand($init);
$servername = $SERVER_NAME;
$id=0; // später userid


DEFINE(A_INC,PUB."php/admin_new/inc");
DEFINE(SESSION_DAUER, 7200);


$username = mysql_real_escape_string($username);

list($user_id, $privilege_level) = row("select id, privilege_level from users where username like '$username'");

if ($user_id) {

	$checkpw = single("select password from users where username='$username'");
//exit;
	if (substr(md5($password),0,20) == $checkpw) {
		if ($privilege_level >= 1) {
			// Sessionid erzeugen

			$sidok = 0;
			while (!$sidok) {
				$sessionid2="";
				for ($i=0;$i<20;$i++) {
					$init = mt_rand(0,2);

					if ($init == 0) {
					$random = mt_rand(65,90);
					}
					if ($init == 1) {
					$random = mt_rand(97,122);
					}
					if ($init == 2) {
					$random = mt_rand(48,57);
					}
					$sessionid2.= chr($random);
				} // For

				$sessionid2 .= crypt($user_id, mt_rand(10,99));

				$checksid = single("select sessionid from admin_sessionids where sessionid = '$sessionid2'");
				if (!$checksid) {$sidok = 1;}
			}


			$gueltig_bis = $time  + SESSION_DAUER;
			$id = $user_id;


			// Neue Sessionid in die DB schreiben
			$ip = getenv ("REMOTE_ADDR");
			if (!$dev) {
				$dev = createkey($id);
				setcookie("dev", $dev, $time+2*365*86400, "/", "");
			}
			$pc_identifier = $dev;
			$browsername = htmlentities($HTTP_USER_AGENT, ENT_QUOTES);
			$hostname = htmlentities(gethostbyaddr($ip), ENT_QUOTES);
			select("insert into admin_sessionids (sessionid, angelegt_bei, gueltig_bis, ip, user_id, pc_identifier, browsername, hostname, privilege_level) values ('$sessionid2',$time,$gueltig_bis,'$ip',$id,'$pc_identifier', '$browsername', '$hostname', $privilege_level)");

			// Spielsessionid setzen
			setcookie ("sessionid_admin", $sessionid2, -1 ,"/", "");

			// Umleitung auf Adminpanel
			header ("Location: index.html"); exit();
		} else header("Location: index.php?error=Sie haben für diesen Bereich nicht die nötigen Rechte.");
	} else header ("Location: index.php?error=Die Passwörter stimmen nicht überein.");
} else header ("Location: index.php?error=Der angegebene Username konnte nicht gefunden werden.");


?>
