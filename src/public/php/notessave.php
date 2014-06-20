<?

##############################
# Zum Speichern der Ajaxanfrage vom Notizblock in der Statusseite
# by Mura 14.07.08
##############################



require_once("../../config/connectdb.php");
require_once("../../config/xtendsql.php");
require_once("../../lib/subs.php");

connectdb();

	//$text = htmlentities($text,ENT_QUOTES);
        $text = addslashes($text);
        $exists = single("select user_id from notes where user_id =$id");
        if ($exists) {
        	select("update notes set text='$text' where user_id=$id");
            }else{
                select("insert into notes (text,user_id) values ('$text',$id)");
            }
?>
