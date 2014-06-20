<? if ($globals[roundstatus] != 0) { if ($globals[roundstatus] == 1) {?>
			<strong><? echo "$startseitedot";?> Warticker</strong><br>
			<br>
			<table class=rand width=90% cellspacing=1 cellpadding=0><tr><td>
			<?
			$wardatastart = assocs("select starttime-24*3600 as starttime, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3 from wars where starttime <> 0 order by starttime desc limit 8","starttime");
			$wardataend = assocs("select * from wars where endtime <> 0 order by endtime desc limit 8","endtime");
			// Newsticker daten holen
				$heute = date("d",$time);
				// Allianznamen und Syndikatsnamen ermitteln
				foreach ($wardatastart as $key => $value) {
					$times[] = $key;
					if ($value[first_synd_2]) {
						$allianzids[] = $value[first_synd_1];
					}
					else {
						$synids[] = $value[first_synd_1];
					}
					if ($value[second_synd_2]) {
						$allianzids[] = $value[second_synd_2];
					}
					else {
						$synids[] = $value[second_synd_1];
					}
				}
				foreach ($wardataend as $key => $value) {
					$times[] = $key;
					if ($value[first_synd_2]) {
						$allianzids[] = $value[first_synd_1];
					}
					else {
						$synids[] = $value[first_synd_1];
					}
					if ($value[second_synd_2]) {
						$allianzids[] = $value[second_synd_2];
					}
					else {
						$synids[] = $value[second_synd_1];
					}
				}

				$allynamen = assocs("select syndikate.synd_id as synd_id,allianzen.name from allianzen,syndikate where allianzen.allianz_id=syndikate.allianz_id and syndikate.allianz_id and (syndikate.synd_id = first or syndikate.synd_id = second or syndikate.synd_id = third)","synd_id");

				$synquery = "select name,synd_id from syndikate where synd_id in (";
				if ($synids) {
					foreach ($synids as $key => $value) {
						$synquery.="$value,";
						$continue = 1;
					}
				}
				$synquery = chopp($synquery);
				$synquery.=")";
				if ($continue == 1) {
					$synnamen = assocs("$synquery","synd_id");
				}
				unset($continue);

				// Meldungen generieren
				if (count($times) > 0) {
					rsort($times);
				}

				$gstart = 0;
				$hstart = 0;
				$shown=0;
				$gestern = $heute-1;
				$tblstart = "<table class=rand cellspacing=0 cellpadding=0 width=100%><tr><td>";
				$tblstart1 = "<table class=rand cellspacing=0 cellpadding=0 width=100%><tr><td>";
				$tblend = "</td></tr></table>";
				if (!$times[0]) {echo "$tblstart<table class=head cellspacing=0 cellpadding=4 width=100%><tr><td >Bisher wurden keine Kriege registriert.</td></tr></table>$tblend";}
				else {
					foreach ($times as $key => $value) {
						if ($shown < 8 && (date("d",$value) == $heute || date("d",$value) == $gestern)) {
							if ($hstart == 0 && date("d",$value) == $heute) {
								$hstart =1;
								echo "$tblstart<table class=head width=100% cellpadding=4 cellspacing=0><tr><td align=center><b><i>Heute:</i></b></td></tr></table>$tblend";
							}

							if ($gstart == 0 && date("d",$value) != $heute) {
								$gstart =1;
								echo "$tblstart<table class=head width=100% cellpadding=4 cellspacing=0><tr><td align=center><b><i>Gestern:</i></b></td></tr></table>$tblend";
							}
							echo "$tblstart<table class=body cellspacing=0 cellpadding=4 width=100%><tr><td>";
							if ($wardatastart[$value]) {
								$temp = $wardatastart[$value];
								if ($allynamen[$temp[first_synd_1]]) {
									$group1 = "Die Allianz <i>'".$allynamen[$temp[first_synd_1]][name]."'</i>";
								}
								else {
									$group1 = "<i>".$synnamen[$temp[first_synd_1]][name]."(#$temp[first_synd_1])</i>";
								}
								if ($allynamen[$temp[second_synd_1]]) {
									$group2 = "der Allianz <i>'".$allynamen[$temp[second_synd_1]][name]."'</i>";
								}
								else {
									$group2 = "<i>".$synnamen[$temp[second_synd_1]][name]." (#$temp[second_synd_1]) </i>";
								}
								echo "<li>".(date("H:i",$value))." - $group1 erkl�rt $group2 den Krieg!<br>";
								$written = 1;
							}
							elseif($wardataend[$value]) {
								$temp = $wardataend[$value];
								if ($allynamen[$temp[first_synd_1]]) {
									$group1 = "der Allianz <i>'".$allynamen[$temp[first_synd_1]][name]."'</i>";
								}
								else {
									$group1 = "<i>".$synnamen[$temp[first_synd_1]][name]." (#$temp[first_synd_1])</i> ";
								}
								if ($allynamen[$temp[second_synd_1]]) {
									$group2 = "der Allianz <i>'".$allynamen[$temp[second_synd_1]][name]."'</i>";
								}
								else {
									$group2 = "<i>".$synnamen[$temp[second_synd_1]][name]." (#$temp[second_synd_1])</i> ";
								}
								echo "<li>".(date("H:i",$value))." - Der Krieg zwischen $group1 und $group2 ist zuende<br>";
								$written = 1;
							}
							echo "</td></tr></table>$tblend";
							$shown++;
						}
					}
					if (!$written) {
						echo "$tblstart<table class=head cellspacing=0 cellpadding=4 width=100%><tr><td > In den letzten zwei Tagen wurden keine Kriege registriert.</td></tr></table>$tblend";
					}
				}

			?>
   
<?
// Ab hier die landgrabs
?></td></tr></table>
<br><br>
<? 
if ($globals['round'] == 51) {
echo "$startseitedot";?><strong> Marktpreise Runde 49</strong><br><br>
<table>
  <tr>
    <td>
      <object width="750" height="300" type="application/x-shockwave-flash" id="_cw" data="http://widgets.cronimon.com//CronimonWidget.swf"><param name="wmode" value="opaque" /><param name="movie" value="http://widgets.cronimon.com//CronimonWidget.swf" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="flashvars" value="externalName=dyQS1cVSNsTqdtc9811MoXmgBIMn8npxkkTQ2TH73mIottZ8&server_url=widgets.cronimon.com/&host=http://" /><embed width="750" height="300" flashvars="externalName=dyQS1cVSNsTqdtc9811MoXmgBIMn8npxkkTQ2TH73mIottZ8&server_url=widgets.cronimon.com/&host=http://" wmode="opaque" allowscriptaccess="always" id="_cw" src="http://widgets.cronimon.com//CronimonWidget.swf" /></object>    
    </td>
  </tr>
  <tr>
    <td>
<object width="750" height="300" type="application/x-shockwave-flash" id="_cw" data="http://widgets.cronimon.com//CronimonWidget.swf"><param name="wmode" value="opaque" /><param name="movie" value="http://widgets.cronimon.com//CronimonWidget.swf" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="flashvars" value="externalName=EN6UPU7N56phBiEMuxcytk3V3lwH7wUHnY78qYMW8Nk4LC&server_url=widgets.cronimon.com/&host=http://" /><embed width="750" height="300" flashvars="externalName=EN6UPU7N56phBiEMuxcytk3V3lwH7wUHnY78qYMW8Nk4LC&server_url=widgets.cronimon.com/&host=http://" wmode="opaque" allowscriptaccess="always" id="_cw" src="http://widgets.cronimon.com//CronimonWidget.swf" /></object>    </td>
  </tr>
</table>

<br>
<ul>
  <li><a href="http://smartcharts.cronimon.com/chart.php?chart_id=C6V7ouH9oSUfbO3a4hhu3zvYUenw3hiuyUrn1nLWw9Qw4PY" target="_blank" class="gelblink">Preisverlauf UIC</a></li>
  <li><a href="http://smartcharts.cronimon.com/chart.php?chart_id=d7EPP6NWv5TTXuG3iu3fBerTSh95t6nvQr9K1KROG8A0FnCo" target="_blank" class="gelblink">Preisverlauf NOF</a></li>

</ul>

<br>
<? } ?>

<br><br>
<strong><? } echo "$startseitedot";?> Bestenliste</strong><br><br>
<table>
	<tr>
		<td valign=top>
   		<table width="180" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="180" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Top 3 Standard</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$grab = assocs("select * from attacklogs where time <=".($time-60*60*24)." and type in (1) order by landgain desc limit 10");
						$ids = "(";
						foreach ($grab as $ky) {
							$ids .= "'$ky[aid]',";
						}
						$ids = chopp($ids); $ids.=")";
						$ii=0;
						if (strlen($ids) > 3) {
							$players = assocs("select * from status where alive > 0 and id in $ids","id");
							foreach ($grab as $ky) {
								if ($players[$ky[aid]][syndicate] && $ii < 3) {
									$ii++;
									/*
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									*/
									echo "<b class=\"gelb11\">".$players[$ky[aid]][syndicate]." (#".$players[$ky[aid]][rid].")</b><br>
											".pointit($ky[landgain])." Hektar<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>
		</td>
		<td valign=top>
   		<table width="180" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="180" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Top 3 Belagerung</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$grab = assocs("select * from attacklogs where time <=".($time-60*60*24)." and type in (2) order by landgain desc limit 10");
						$ids = "(";
						foreach ($grab as $ky) {
							$ids .= "'$ky[aid]',";
						}
						$ids = chopp($ids); $ids.=")";
						$ii=0;
						if (strlen($ids) > 3) {
							$players = assocs("select * from status where alive > 0 and id in $ids","id");
							foreach ($grab as $ky) {
								if ($players[$ky[aid]][syndicate] && $ii < 3) {
									$ii++;
									/*
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									*/
									echo "<b class=\"gelb11\">".$players[$ky[aid]][syndicate]." (#".$players[$ky[aid]][rid].")</b><br>
											".pointit($ky[landgain])." Geb�ude<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>
		</td>
		<td valign=top>
   		<table width="180" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="180" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Top 3 Eroberung</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$grab = assocs("select * from attacklogs where time <=".($time-60*60*24)." and type in (3) order by landgain desc limit 10");
						$ids = "(";
						foreach ($grab as $ky) {
							$ids .= "'$ky[aid]',";
						}
						$ids = chopp($ids); $ids.=")";
						$ii=0;
						if (strlen($ids) > 3) {
							$players = assocs("select * from status where alive > 0 and id in $ids","id");
							foreach ($grab as $ky) {
								if ($players[$ky[aid]][syndicate] && $ii < 3) {
									$ii++;
									/*
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									*/
									echo "<b class=\"gelb11\">".$players[$ky[aid]][syndicate]." (#".$players[$ky[aid]][rid].")</b><br>
											".pointit($ky[landgain])." Hektar<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>
		</td>
		<td valign=top>
   		<table width="180" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="180" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Top 3 Spione zerst�ren</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$grab = assocs("select * from attacklogs where time <=".($time-60*60*24)." and type in (4) order by landgain desc limit 10");
						$ids = "(";
						foreach ($grab as $ky) {
							$ids .= "'$ky[aid]',";
						}
						$ids = chopp($ids); $ids.=")";
						$ii=0;
						if (strlen($ids) > 3) {
							$players = assocs("select * from status where alive > 0 and id in $ids","id");
							foreach ($grab as $ky) {
								if ($players[$ky[aid]][syndicate] && $ii < 3) {
									$ii++;
									/*
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									*/
									echo "<b class=\"gelb11\">".$players[$ky[aid]][syndicate]." (#".$players[$ky[aid]][rid].")</b><br>
											".pointit($ky[landgain])." Spione<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>
		</td></tr><tr>

<td td valign=top>
   		<table width="180" cellspacing="1" cellpadding="0" border="0" bgcolor=rand>
            <tr>
                <td>
					<table width="180" cellspacing="0" cellpadding="4" border="0" class=head>
					<tr>
						<td align="left">
							<b>&nbsp;Most successful Spies</b>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width="100%" cellspacing=0 cellpadding=2 border=0 class=body>
					<tr><td height=5></td></tr>
					<tr>
						<td align=center valign=center>
						<?php
						$statsmaxround = single("select max(round) from stats");
						$grab = assocs("select spyopsdonewon,rid,syndicate from stats where round=$statsmaxround order by spyopsdonewon desc limit 3");
						$ii = 0;
						if (count($grab) > 0) {
							foreach ($grab as $value) {
								if ($value[syndicate] && $ii < 3) {
									$ii++;
									/*
									if (strlen($players[$ky[aid]][syndicate]) > 17) {
										$players[$ky[aid]][syndicate] = substr($players[$ky[aid]][syndicate],0,13)."...";
									}
									*/
									echo "<b class=\"gelb11\">".$value[syndicate]." (#".$value[rid].")</b><br>
											".pointit($value[spyopsdonewon])." Aktionen<br><br>";
								}
							}
						}
						?>

						</td>
					</tr>
					<tr><td height=5></td></tr>
					</table>
    	        </td>
            </tr>
        </table>

</td>

</tr></table>
<? } else {
	echo "Die aktuelle Runde wurde noch nicht gestartet.";}
?>
