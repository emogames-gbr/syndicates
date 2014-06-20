<?
##
##	SMART MENU
##

$menupoints_loggedout = array(
	"Login" => "login"
);

// Alle rechte normaler Gamemaster
$menupoints[1] = array(
	"Main" => "main",
	"Benutzerdatenwechsel" => "idswitch",
	"Spieleraktionen verfolgen" =>"traceuser",
	"IP Matching" =>  "detect_ip_mates",
	"Multis suchen" => "findmultis",
	"Spieler bannen" => "tempban",
	"Konzernbeschreibung löschen" => "delsettings",
	"Konzernbilder" => "konzernbilderverwaltung",
	"<b>Logout</b>" => "logout"
	
);

// Alle rechte von Supergamemastern
$menupoints[2] = array(
	"Grafikpakete freischalten" => "checkgpacks",
	"Monumente setzen" => "setmonument",
	"TOIMPLEMENT: GM Tracing" => "gmtracing",
);

// Alle rechte von admins
$menupoints[3] = array(
	"Announcement" => "announcements",
	"Use Stats" => "adminstats",
	"Börsenvolumen" => "boersenvolumen",
	"Syn Größe Prüfen" => "checksyndikatsgroessen",
	"Angriffe/Spionage checken" => "checkuser",
	//"Einnahmen" => "money",
	"Ingame Message" => "sendingamemessage",
	"Spystats" => "spystats",
	"Synbilder" => "syndikatsbilderverwaltung",
	"Klickstatistiken" => "klickstatistiken",
	"Anmeldestatistiken" => "anmeldestats",
	"Gruppen Mitgliedeverteilung" => "vgv",
	"TOIMPLEMENT GM Management" => "gmmanagement",
);

// NIcht eingelogt oder kein Admin
if ($adminlevel <= 0) {
	foreach ($menupoints_loggedout as $k => $v) {
		$innermenu.="<td align=center class=subhead><a href=\"index.php?action=$v\">$k</a></td>";
	}
}

// Adminmenu aufbauen
else {
	for ($i=1;$i <= $adminlevel; $i++) {
		if (count($menupoints[$i]) > 0) {
			$a=0;
			foreach ($menupoints[$i] as $k => $v) {
				if ($a > 5) {
					$innermenu.="</tr><tr>";
					$a=0;
				}
				$innermenu.="<td align=center class=subhead><a href=\"index.php?action=$v\">$k</a></td>";
				$a++;
			}
			$innermenu.="<tr><td colspan=100><hr></td></tr>";
		}
	}

}


$menu = "
	<table width=100%  cellspacing=1 cellpadding=3 >
		<tr>
			$innermenu
		</tr>
	</table>
";

?>