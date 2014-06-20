<?

include("inc/general.php");


	if ($inneraction == 1) {
		$target_id = floor($target_id);
		$attackdata_own = assocs("select type, did, drid, drace, time, winner, apoints, dpoints, landgain from attacklogs where aid='$target_id' and ginactive=0 and warattack=0");
		$exceptions = "Angriffe von $target_id außer Angriffe auf Inaktive und Kriegsangriffe<br>";
		if ($attackdata_own) {
			foreach ($attackdata_own as $vl) {
				$ids[] = $vl[did];
			}

			$attackdata_did = assocs("select type, aid, arid, arace, did, drid, drace, time, winner, apoints, dpoints, landgain from attacklogs where did in (".(join(",",$ids)).") and ginactive = 0 and warattack = 0", "aid");
			$spydata_did = assocs("select aid, did, action, success, time, offense, defense, result from spylogs where did in (".(join(",",$ids)).") and (action like 'unitintel%' or action like 'scienceintel' or action like 'buildintel')", "aid");

			$i = 0;
			foreach ($attackdata_own as $vl) {
			$i++;
				$times[$i] = $vl[time];
				$timesdata[$i] = $vl;
			}
			if ($attackdata_did) {
				foreach ($attackdata_did as $vl) {
					if ($vl[aid] != $target_id) {
						$i++;
						$times[$i] = $vl[time];
						$timesdata[$i] = $vl;
						$ids[] = $vl[aid];
						$ids[] = $vl[did];
					}
				}
			}
			if ($spydata_did) {
				foreach ($spydata_did as $vl) {
					$i++;
					$times[$i] = $vl[time];
					$timesdata[$i] = $vl;
					$ids[] = $vl[aid];
					$ids[] = $vl[did];
				}
			}

			arsort($times);
			$names = assocs("select id, syndicate, rid, race from status where id in (".join(",", $ids).")", "id");

			foreach ($times as $ky => $vl) {
				$lines = array();
				$color = "";
				$winnersuccesscolor = "";

				$lines[0] = date("d.m; H:i:s", $timesdata[$ky][time]);

				if ($timesdata[$ky][defense]) {	#Spydata
					$color = "lightgreen";
					if ($timesdata[$ky][aid] != $target_id): $color = "green"; endif;
					$lines[1] = $names[$timesdata[$ky][did]][syndicate]." (#".$names[$timesdata[$ky][did]][rid].") [".$names[$timesdata[$ky][aid]][race]."]";
					$lines[2] = $timesdata[$ky][aid] == $target_id ? "":$names[$timesdata[$ky][aid]][syndicate]." (#".$names[$timesdata[$ky][aid]][rid].") [".$names[$timesdata[$ky][aid]][race]."]";
					$lines[3] = $timesdata[$ky][action] == "unitintel1" ? "Konzernspion" : ($timesdata[$ky][action] == "unitintel2" ? "Genauer Milspion" : ($timesdata[$ky][action] == "scienceintel" ? "Forschungsspion" : "Gebäudespion"));
					$lines[4] = $timesdata[$ky][offense];
					$lines[5] = $timesdata[$ky][defense];
					$lines[6] = $timesdata[$ky][success];
					$lines[7] = "-";
					$winnersuccesscolor = $timesdata[$ky][success] == "1" ? "red" : "lightblue";
				}
				elseif (!$timesdata[$ky][aid]) {	#Attacked self
					$color = "yellow";
					$lines[1] = $names[$timesdata[$ky][did]][syndicate]." (#".$timesdata[$ky][drid].") [".$timesdata[$ky][drace]."]";
					$lines[2] = "";
					$lines[3] = $timesdata[$ky][type] == 1 ? "Standard" : ($timesdata[$ky][type] == 2 ? "Belagerung" : "Eroberung");
					$lines[4] = $timesdata[$ky][apoints];
					$lines[5] = $timesdata[$ky][dpoints];
					$lines[6] = $timesdata[$ky][winner];
					$lines[7] = $timesdata[$ky][landgain];
					$winnersuccesscolor = $timesdata[$ky][winner] == "a" ? "red" : "lightblue";
				}
				else {	#Attacked Others
					$color = "orange";
					$lines[1] = $names[$timesdata[$ky][did]][syndicate]." (#".$timesdata[$ky][drid].") [".$timesdata[$ky][drace]."]";
					$lines[2] = $names[$timesdata[$ky][aid]][syndicate]." (#".$timesdata[$ky][arid].") [".$timesdata[$ky][arace]."]";
					$lines[3] = $timesdata[$ky][type] == 1 ? "Standard" : ($timesdata[$ky][type] == 2 ? "Belagerung" : "Eroberung");
					$lines[4] = $timesdata[$ky][apoints];
					$lines[5] = $timesdata[$ky][dpoints];
					$lines[6] = $timesdata[$ky][winner];
					$lines[7] = $timesdata[$ky][landgain];
					$winnersuccesscolor = $timesdata[$ky][winner] == "a" ? "red" : "lightblue";
				}

				$ausgabe_lines .= "	<tr bgcolor=$color>
										<td width=125>".$lines[0]."</td>
										<td width=255>".$lines[1]."</td>
										<td width=255>".$lines[2]."</td>
										<td>".$lines[3]."</td>
										<td>".$lines[4]."</td>
										<td>".$lines[5]."</td>
										<td bgcolor=$winnersuccesscolor>".$lines[6]."</td>
										<td>".$lines[7]."</td>

									</tr>";

			}

			$ausgabe .= "<center>Farblegende:<br>Orange: Angriff von einem Dritten<br>Gelb: Angriff von zu checkendem Konzern
			<br>Grün: Spionageaktion eines Dritten<br>Hellgrün: Spionageaktion von zu checkendem Konzern<br>Rot: Aktion erfolgreich<br>Blau: Aktion misslungen<br><br></center>



			<table cellpadding=2 border=1 bordercolor=black width=100%>
									<tr>
										<td width=125>Datum</td>
										<td width=255>Opfter</td>
										<td width=255>Angreifer</td>
										<td>Aktionstyp</td>
										<td>Offense</td>
										<td>Defense</td>
										<td>Erg.</td>
										<td>Landgain</td>

									</tr>
			$ausgabe_lines</table>";


		}
		else { $ausgabe .= "$target_id hat keine Angriffe getätigt."; }

	}
	else {
		$ausgabe .= "<form action=$self><input type=hidden name=action value=checkuser><input type=hidden name=inneraction value=1>Konzernid: <input type=text name=target_id><br><br><input type=submit value=go></form>";
	}

	$ausgabe = "<br><br>
				<center>$ausgabe</center>";



echo "
<html>
<head>
	<title>Syndicates - Adminpanel</title>
	<LINK REL=\"stylesheet\" HREF=\"style.css\" TYPE=\"text/css\">
</head>

<body><center>
$fehler
$successmeldung
$informationmeldung</center>
$ausgabe
</body>

</html>";


?>
