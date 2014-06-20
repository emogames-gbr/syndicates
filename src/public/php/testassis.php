<?
//**************************************************************************
// Assis an und abschalten, R47, R4bbiT - nur Testversion                                               
//**************************************************************************
require_once("../../inc/ingame/game.php");

if ($game["name"] == "Syndicates Testumgebung"){
	
	$emo_id = single("select emogames_user_id from users where konzernid = ".$status["id"]);
		
	if($_GET["assi"] != "" && $_GET["action"] == "delete"){
		single("delete from users_features where emogames_user_id = ".$emo_id." and feature_id = ".constant($_GET["assi"]));
		header("Location: testassis.php");
	}
	else if($_GET["assi"] != "" && $_GET["action"] == "insert"){
		single("insert into users_features (emogames_user_id, feature_id, time, time_bis, auto, server_id) values(".$emo_id.", ".constant($_GET["assi"]).", ".time().", ".(time()+60*60*24*365).", 0, 1)");
		header("Location: testassis.php");
	}
	
	function create($as){
		global $emo_id;
		if(single("select id from users_features where emogames_user_id = ".$emo_id." and feature_id = ".constant($as))){
			return "<a href='?assi=".$as."&action=delete' class='linkaufTableInner'>deaktivieren</a>";
		}
		else{
			return "<a href='?assi=".$as."&action=insert' class='linkaufTableInner'>aktivieren</a>";
		}
	}
	
	$ausgabe = "
		<table width=550 style=\"border:1px solid\" class=i cellpadding=2>
			<tr>
				<td>
					Hier könnte ihr benötigte Assis an- und abschalten.<br />
					Nach der Änderung müsst ihr allerdings bis zu 1 Min warten, bis Syn das kapiert.
				</td>
			</tr>
		</table>
		<br />
		<table cellpadding=\"5\" cellspacing=\"1\" border=\"0\" width=350 class=\"tableOutline\" align=center >
			<tr>
				<td class=\"tableInner1\" align=left>Forschungsassi:</td>
				<td class=\"tableInner1\" align=left>".create("FORSCHUNGSQ")."</td>
			</tr>
			<tr>
				<td class=\"tableInner1\" align=left>Gebäudesassi:</td>
				<td class=\"tableInner1\" align=left>".create("GEBAEUDEQ")."</td>
			</tr>
			<tr>
				<td class=\"tableInner1\" align=left>Militärsassi:</td>
				<td class=\"tableInner1\" align=left>".create("MILITAERQ")."</td>
			</tr>
			<tr>
				<td class=\"tableInner1\" align=left>Werbung deaktivieren:</td>
				<td class=\"tableInner1\" align=left>".create("WERBUNG_DEAKTIVIERT")."</td>
			</tr>
			<tr>
				<td class=\"tableInner1\" align=left>Komfortpaket:</td>
				<td class=\"tableInner1\" align=left>".create("KOMFORTPAKET")."</td>
			</tr>
			<tr>
				<td class=\"tableInner1\" align=left>Angriffs-/ Spydb:</td>
				<td class=\"tableInner1\" align=left>".create("ANGRIFFSDB")."</td>
			</tr>
		</table>";
	
//**************************************************************************
// Header, Ausgabe, Footer
//**************************************************************************
	require_once("../../inc/ingame/header.php");
	echo $ausgabe;
	require_once("../../inc/ingame/footer.php");
}
else f("Diese Seite steht auf diesem Server leider nicht zur Verfügung.");
?>