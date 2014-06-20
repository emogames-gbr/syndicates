<?

##############################
# Zum Speichern ob der Notizblock aufgeklappt ist
# by Mura 14.07.08
##############################



require_once("../../config/connectdb.php");
require_once("../../config/xtendsql.php");
require_once("../../lib/subs.php");

connectdb();

        	select("update status set notespin='$pinstatus' where id=$id");
?>
