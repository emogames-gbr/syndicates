<html>
<head>
<title>Gettime Skript (c) by Nicolas Breitwieser 2004</title>
</head>

<body>

<?

$time = time();
$stunde = date("H", $time);
$minute = date("i", $time);
$sekunde = date("s", $time);
$tag = date("d", $time);
$monat = date("m", $time);
$jahr = date("Y", $time);

$ausgabe .= "<center>Aktueller Timestamp: <b>".$time."</b></center>";

if ($timestamp):	$timestamp = floor($timestamp);
					$uebersetzt = date("d. M. Y, H:i:s", $timestamp);
					$ausgabe .= "<br><center><b>$timestamp</b> steht für <b>$uebersetzt</b></center>";
					endif;
if ($hours):		$uebersetzt = "$year-$month-$day $hours:$minutes:$seconds";
					$timestamp = strtotime($uebersetzt);
					$ausgabe .= "<br><center><b>$uebersetzt</b> steht für <b>$timestamp</b></center";
					endif;

echo "$ausgabe";
?>
<center>
<br><br>
<form action=gettime.php>
Unix Time Stamp übersetzen:<br><br>
<input type=text name=timestamp>
<br>
<input type=submit value=go>
</form>
<br><br>
<form action=gettime.php>
Festes Datum in Unix Timestamp übersetzen:<br><br>
<table align=center>
<tr><td>Stunde</td><td><input type=text name=hours value="<? echo $stunde ?>"></td></tr>
<tr><td>Minute</td><td><input type=text name=minutes value=00></td></tr>
<tr><td>Sekunde</td><td><input type=text name=seconds value=00></td></tr>
<tr><td>Tag</td><td><input type=text name=day value="<? echo $tag ?>"></td></tr>
<tr><td>Monat</td><td><input type=text name=month value="<? echo $monat ?>"></td></tr>
<tr><td>Jahr</td><td><input type=text name=year value="<? echo $jahr ?>"></td></tr>
<tr><td colspan=2><input type=submit value=go></td></tr>

</center>

</body>
</html>
