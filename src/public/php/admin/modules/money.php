<?

$id = 2000000;
$standardwidth = 600;


$globals = assocs("select * from globals", "round");

include("../../abodata.php");

$abodata[206] = array(
					name => "DSL-Abo",
					laufzeit => "6",
					kosten => 20/6,
					anzahl_spieler => 1,
					id => 206
				);

$money = assocs("select zeitraum_artikelid, count(*) as tl from payment_aboinfo group by zeitraum_artikelid order by zeitraum_artikelid asc");

$ausgabe .= "<table align=center width=100%><tr><td align=center>Aboname</td><td align=center>Anzahl verkauft</td><td align=center>Abopreis</td><td align=center>Abogesamteinnahmen diese Runde</td><td align=center>Abogesamteinnahmen pro Monat</td><td align=center>Spieler versorgt</td></tr>";
foreach ($money as $vl) {
	$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde] = round(100*$abodata[$vl[zeitraum_artikelid]][kosten] * $vl[tl])/100;
	$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat] = round(100*$abodata[$vl[zeitraum_artikelid]][kosten] * $vl[tl] * 30 / 52)/100;
	$abodata[$vl[zeitraum_artikelid]][spieler_versorgt] = $abodata[$vl[zeitraum_artikelid]][anzahl_spieler] * $vl[tl];
	$ausgabe .= "<tr><td align=center>".$abodata[$vl[zeitraum_artikelid]][name]."</td><td align=center>".$vl[tl]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][kosten]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][spieler_versorgt]."</td></tr>";
	$gesamtabos += $vl[tl];
	$gesamteinnahmen_runde += $abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde];
	$gesamteinnahmen_monat += $abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat];
	$spieler_versorgt += $abodata[$vl[zeitraum_artikelid]][spieler_versorgt];
}
$ausgabe .= "<tr height=10><td>&nbsp;</td></tr><tr><td align=center>G E S A M T</td><td align=center>$gesamtabos</td><td align=center>~</td><td align=center>$gesamteinnahmen_runde</td><td align=center>$gesamteinnahmen_monat</td><td align=center>$spieler_versorgt</td></tr>";
$ausgabe .= "</table>";
$gesamteinnahmen_runde_ohne_4players_ohne_mwst = round(100*$gesamteinnahmen_runde/(2*1.16))/100;
$gesamteinnahmen_monat_ohne_4players_ohne_mwst = round(100*$gesamteinnahmen_runde/2/1.16 * 30 / 52)/100;
$ausgabe .= "<br><br><center><b>Gesamteinnahmen die Runde ohne 4Players (-50%) nach Abzug der MWSt. (/1.16):<br><br>$gesamteinnahmen_runde_ohne_4players_ohne_mwst<br><br><br><br>Gesamteinnahmen pro Monat ohne 4Players (-50%) nach Abzug der MWSt. (/1.16):<br><br>$gesamteinnahmen_monat_ohne_4players_ohne_mwst</b></center>";
$ausgabe .= "<br><br><center>Durchschnittlicher Rundenpreis pro Spieler: ".(round(100*$gesamteinnahmen_runde/$spieler_versorgt)/100)."<b></b></center>";

//
// Hier nochmal gleiches skript für paid = 1
//
unset($money,$gesamtabos,$gesamteinnahmen_runde,$gesamteinnahmen_monat,$spieler_versorgt,$abodata,$gesamteinnahmen_runde_ohne_4players_ohne_mwst,$gesamteinnahmen_monat_ohne_4players_ohne_mwst);
///
///
///
include("../../abodata.php");
$abodata[206] = array(
					name => "DSL-Abo",
					laufzeit => "6",
					kosten => 20/6,
					anzahl_spieler => 1,
					id => 206
				);


$ausgabe.="<hr><hr> Ab hier mit paid = 1 only <hr><hr>";

$money = assocs("select zeitraum_artikelid, count(*) as tl from payment_aboinfo where paid=1 group by zeitraum_artikelid order by zeitraum_artikelid asc");

$ausgabe .= "<table align=center width=100%><tr><td align=center>Aboname</td><td align=center>Anzahl verkauft</td><td align=center>Abopreis</td><td align=center>Abogesamteinnahmen diese Runde</td><td align=center>Abogesamteinnahmen pro Monat</td><td align=center>Spieler versorgt</td></tr>";
foreach ($money as $vl) {
	$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde] = round(100*$abodata[$vl[zeitraum_artikelid]][kosten] * $vl[tl])/100;
	$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat] = round(100*$abodata[$vl[zeitraum_artikelid]][kosten] * $vl[tl] * 30 / 52)/100;
	$abodata[$vl[zeitraum_artikelid]][spieler_versorgt] = $abodata[$vl[zeitraum_artikelid]][anzahl_spieler] * $vl[tl];
	$ausgabe .= "<tr><td align=center>".$abodata[$vl[zeitraum_artikelid]][name]."</td><td align=center>".$vl[tl]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][kosten]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat]."</td><td align=center>".$abodata[$vl[zeitraum_artikelid]][spieler_versorgt]."</td></tr>";
	$gesamtabos += $vl[tl];
	$gesamteinnahmen_runde += $abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_runde];
	$gesamteinnahmen_monat += $abodata[$vl[zeitraum_artikelid]][gesamteinnahmen_monat];
	$spieler_versorgt += $abodata[$vl[zeitraum_artikelid]][spieler_versorgt];
}
$ausgabe .= "<tr height=10><td>&nbsp;</td></tr><tr><td align=center>G E S A M T</td><td align=center>$gesamtabos</td><td align=center>~</td><td align=center>$gesamteinnahmen_runde</td><td align=center>$gesamteinnahmen_monat</td><td align=center>$spieler_versorgt</td></tr>";
$ausgabe .= "</table>";
$gesamteinnahmen_runde_ohne_4players_ohne_mwst = round(100*$gesamteinnahmen_runde/(2*1.16))/100;
$gesamteinnahmen_monat_ohne_4players_ohne_mwst = round(100*$gesamteinnahmen_runde/2/1.16 * 30 / 52)/100;
$ausgabe .= "<br><br><center><b>Gesamteinnahmen die Runde ohne 4Players (-50%) nach Abzug der MWSt. (/1.16):<br><br>$gesamteinnahmen_runde_ohne_4players_ohne_mwst<br><br><br><br>Gesamteinnahmen pro Monat ohne 4Players (-50%) nach Abzug der MWSt. (/1.16):<br><br>$gesamteinnahmen_monat_ohne_4players_ohne_mwst</b></center>";
$ausgabe .= "<br><br><center>Durchschnittlicher Rundenpreis pro Spieler: ".(round(100*$gesamteinnahmen_runde/$spieler_versorgt)/100)."<b></b></center>";


?>
