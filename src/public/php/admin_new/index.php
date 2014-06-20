<?

// DB VERBINDUNG
require_once ("../../../includes.php");
connectdb();
$adminlevel = 0;
$time = time();
$globals = assoc("select * from globals order by round desc limit 1");


DEFINE(A_INC,PUB."php/admin_new/inc");



##
##	Configure
##
$action = preg_replace("/\./","",$action);
if (!file_exists(A_INC."/".$action.".php")) $action ="login";



if ($error) f($error);

echo "
<html>
<head>
	<title>Syndicates  Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>
<body>

<center>$fehler</center>
	<table width=\"60%\" class=\"back\" align=center>
		<tr>
			<td align=\"center\">
				<h1>Syndicates Adminpanel</h1>
				<hr>
			</td>
		</tr>

		<tr>

			<td valign=\"top\" align=\"center\" width=\"80%\">
					<br><br>
					<form action=\"login.php\">
					<input type=\"hidden\" name=\"oa\" value=\"login\">
					<table class=\"back\">
						<tr>
							<td>Username:</td>
							<td><input name=\"username\"></td>
						</tr>
						<tr>
							<td>Password:</td>
							<td><input name=\"password\" type=\"password\"></td>
						</tr>
						<tr class=\"head\">
							<td colspan=\"2\" align=\"center\">
								<input type=\"submit\" value=\"login\">
							</td>
						</tr>
					</table>
					</form>
					<br><br>
			</td>

		</tr>
	</table>
</body>";


?>
