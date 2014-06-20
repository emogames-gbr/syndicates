<?

$specialBehaviours = array();


function printAssocs($a, $theseColsOnly = "", $headrepeat = 20, $tablePars = "cellpadding=5 cellspacing=0 border=1 align=center") {
	global $line;
	if (!$headrepeat) $headrepeat = 20;
	
	$lines = array();
	$translate = array();
	$return = "";
	if ($theseColsOnly) {
	    $theseColsOnly = explode(",", $theseColsOnly);
	    foreach ($theseColsOnly as $ky => $vl) {
		$vl = trim($vl);
		$treffer = array();
		if (preg_match("/([^\ ]+) as (.+)/i", $vl, $treffer)) {
		  $translate[$treffer[1]] = $treffer[2];
		}
		else {
		  $vl = str_replace(" ", "", $vl);
		  $translate[$vl] = $vl;  
		}	
	    }
	}

	$o = 0;
	foreach ($a as $line) {
	  if (!killLineOnConditions($line)) {
	      $currLine = array();
	      foreach ($translate as $key => $val) {
		  if ($o == 0) $head[] = "<b>".$val."</b>";
		  $currLine[] = checkForSpecialBehaviour($key, $line[$key]);
	      }
	      if (($o % $headrepeat) == 0) $lines[] = join("</td><td>", $head);
	      $lines[] = join("</td><td>", $currLine);
	      $o++;
	  }
	}

	$return = "<table".($tablePars ? " $tablePars":"")."><tr><td>".join("</td></tr><tr><td>", $lines)."</td></tr></table>";
	return $return;
}

// WICHTIG: die Variable $specialBehaviours muss am Anfang der PHP-Datei als Array initialisiert werden, sonst kann man Code zum Ausführen übergeben!

function checkForSpecialBehaviour($colname, $value) {
	global $specialBehaviours;
	global $line;
	if ($specialBehaviours[$colname]) {
		eval($specialBehaviours[$colname]);
	}
	
	return $value;
}

// im evalCode ist der aktuelle Spaltenwert als $value anzunehmen (siehe checkForSpecialBehaviour()
// auf andere Werte der aktuellen Zeile kann über die Variable $line zugegriffen werden
function setSpecialBehaviour($targetCol, $evalCode) {
	global $specialBehaviours;
	$specialBehaviours[$targetCol] = $evalCode;
}

// Conditions that kill a line have to unset $line;
function setKillLineCondition($evalCode) {
    global $killLineConditions;
    $killLineConditions[] = $evalCode;
}


function killLineOnConditions($line) {
  global $killLineConditions;
  if ($killLineConditions) {
    foreach ($killLineConditions as $cond) {
      eval($cond);
      if (!$line) return 1;
    }
  }
  return 0;
}

?>
