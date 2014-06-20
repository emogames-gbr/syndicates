<?
require_once ("../includes.php");
$handle = connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
require_once(INC."ingame/globalvars.php");


##########################################
#	SERVERLOOP
##########################################
$servers = assocs("select * from servers");

//foreach ($servers as $temp) {
//	mysql_select_db($temp[db_name]);
##########################################


set_time_limit(1500);

$time = time();
$hourtime = get_hour_time($time+10); # 10 Sekunden Sicherheit
$globals = assoc("select * from globals order by round desc limit 1");
$heaptimes = 1; # Legt fest zu welchen Stunden der Heap-Table bearbeitet wird (Modulo, alle X Stunden)
$hour = date("G",$time);
$pagestats = assocs("select * from pages","id");

$anzahl = 10000;
$offset = 0;
$goon = 1;

if ($globals[roundstatus] == 0 or $globals[roundstatus] == 1)	{
	if ($hour % $heaptimes == 0)	{
		while ($goon) {
			$heapdata = assocs("select user_id, clicktime, seite from heaptable where clicktime < $hourtime LIMIT $offset, $anzahl");
			if ($heapdata)	{
				$ausgabe .= "HEAPDATA VORHANDEN";
				foreach ($heapdata as $vl)	{

				$tday = date("j", $vl[clicktime]);
				$tmonth = date("n", $vl[clicktime]);
				$tyear = date("Y", $vl[clicktime]);
				$thour = date("G", $vl[clicktime]);

					$heap[$tyear][$tmonth][$tday][$thour][$vl[seite]]++;
					$user_heap_click_stats[$vl[user_id]]++;
				}
				unset($heapdata);
				$offset += $anzahl;
				echo "Neuer Offset: $offset - und weiter gehts\n";
			} else { $goon = 0; echo "gestoppt\n"; }
		}
		if ($heap) {
			ksort($heap);
			$i = 0;
			foreach ($heap as $ky => $vl)	{	# F?r jedes Jahr
				ksort($vl);
				foreach ($vl as $ky2 => $vl2)	{	# F?r jeden Monat
					ksort($vl2);
					foreach ($vl2 as $ky3 => $vl3)	{	# F?r jeden Tag
						ksort($vl3);
						foreach ($vl3 as $ky4 => $vl4)	{	# F?r jede Stunde
							$hitstats_insertstring_1 = "";
							$hitstats_insertstring_2 = "";
							foreach ($pagestats as $vl5)	{
								if (!$vl4[$vl5[id]]): $vl4[$vl5[id]] = 0; endif;
								$hitstats_insertstring_1 .= $vl5[dateiname].",";
								$hitstats_insertstring_2 .= $vl4[$vl5[id]].",";
							}
							$hitstats_insertstring_1 .= "tag,monat,jahr,stunde";
							$hitstats_insertstring_2 .= "$ky3,$ky2,$ky,$ky4";
							$i++;
							$queries[] = ("insert into hitstats ($hitstats_insertstring_1) values ($hitstats_insertstring_2)");
							echo "$i. Query erstellt\n";
						}
					}
				}
			}
			$queries[] =("delete from heaptable where clicktime < $hourtime");
			echo "Delete-Query erstellt\n";
		}
		else { 	$ausgabe .= "KEIN HEAPDATA VORHANDEN :(";}

	# DIE UPDATES DER CLICKS DER EINZELNEN USER WIRD ERST IN DEN SP?TEREN BL?CKEN GEMACHT UM STATEMENTS ZU SPAREN wenn Rundenstatus = 1 ist
	}
}
elseif ($globals[roundstatus] == 1 or $globals[roundstatus] == 2)	{

}
elseif ($globals[roundstatus] == 0 or $globals[roundstatus] == 2)	{

}

$betreff = "heapdata";
db_write($queries);

//} // SERVERLOOP ENDE

?>
