<?
##########################################################
#
#			 Profiler.php - By Jannis Breitwieser
#
#			graphical interface for profiling actions
# 			requires xtendsql, db connection - just include this file.
##########################################################

$scripts_run = assocs("select script,count(*) as number from mod_profiler_runs group by script ");



if ($action) {
	$runs = assocs("select * from mod_profiler_runs where script='$script'");
	$runs_marks = array();
	foreach ($runs as $temp) {
		$runs_marks[] = assocs("select * from mod_profiler_marks where run_id=$temp[run_id] order by mark_id asc");
	}
	$average_marks = array();
	foreach ($runs_marks as $temp) {
		foreach ($temp as $ttemp) {
			if ($average_marks[$ttemp[mark_name]] > 0) {$ex = 1;}
			$average_marks[$ttemp[mark_name]] += (double) $ttemp[mark_time];
			if ($ex == 1 && $ttemp[mark_time] > 0 && $average_marks[$ttemp[mark_name]] > 0) {$average_marks[$ttemp[mark_name]] /=2;}
		}
	}
	/*
	$runcount = count($runs);
	foreach ($average_marks as $key => $value) {
		$average_marks[$key] /= $runcount;
	}
	*/

	$ausgabe.="
		<table bgcolor=black cellspacing=\"1\" cellpadding=\"4\">
			<tr>
				<td colspan=\"5\" bgcolor=white>
					Profiling for $script:
				</td>
			</tr>
			<tr>
				<td colspan=\"5\" bgcolor=white>
					Average Values:
				</td>
			</tr>
			<tr bgcolor=white>
				<td>
					Mark Start / Mark end
				</td>
				<td>
					Time
				</td>
				<td>
					Difference
				</td>
				<td width=\"200\">
					Graphical % of time
				</td>
			</tr>
			";
			$tcount = 0;
			$timesum = 0;
			$differences = array();
			$i=0;
			foreach ($average_marks as $key => $value) {
				$thisrun = array(mark_name=>$key,mark_time=>$value);
				if ($previous) { // Nur wenn es noch mindestens eine ausführung mehr gibt..
					$markstring[$i] = $previous[mark_name]." - ".$thisrun[mark_name];
					$timestring[$i] = $previous[mark_time]." - ".$thisrun[mark_time];
					$difference[$i] = $thisrun[mark_time] - $previous[mark_time];
					$timesum += $difference[$i];
					$i++;
				}
				$tcount++;
				$previous = array(mark_name=>$key,mark_time=>$value);
			}
			/*
			pvar($markstring);
			pvar($runcount,runcount);
			pvar($tcount,tcount);
			*/
			for ($a =0; $a < count($markstring); $a++) {
				$twidth = 200*($difference[$a] / $timesum);
				$ausgabe.="
					<tr bgcolor=white>
						<td>
							".$markstring[$a]."
						</td>
						<td>
							".$timestring[$a]."
						</td>
						<td>
							".$difference[$a]."
						</td>
						<td width=\"200\">
							<img src=\"dotpixel.gif\" height=\"5\" width=\"$twidth\">
						</td>
					</tr>
				";
			}

		$ausgabe.="
			<tr bgcolor=white>
				<td colspan=\"5\" align=\"right\">
					Number of runs: $runcount -------- Total Runtime Average: $timesum msec
				</td>
			</tr>
			<tr bgcolor=white>
				<td colspan=5>
					<a href=\"?action=\">Back</a>
				</td>
			</tr>
		</table>
	";

}


##########################################################
# 				NO ACTION
##########################################################

if (!$action) {
	$ausgabe.="
		<table bgcolor=black cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">
			<tr>
				<td colspan=\"2\" bgcolor=white>
					Profiled scripts (Scrip Name / Number of Runs):
				</td>
			</tr>
			";
			foreach ($scripts_run as $temp) {
				$ausgabe.="
					<tr>
						<td  bgcolor=white>
							<a href=\"?action=details&script=$temp[script]\">$temp[script]</a>
						</td>
						<td bgcolor=white>
							$temp[number]
						</td>
					</tr>
				";
			}
			$ausgabe.="
		</table>
	";
}

echo $ausgabe;





?>
