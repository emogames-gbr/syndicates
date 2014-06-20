<?php


require("../subs.php");
$docroot = "../../";
require("../modules/picturemodule.php");
connectdb();
select("delete from codes");
select("delete from codes_anmeldung");
exec("rm ../../codes/* -r");
exec("rm ../../codes_anmeldung/* -r");

?>
