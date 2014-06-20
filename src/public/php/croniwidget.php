<?


$type= $_GET['type'];
$title = $_GET['title'];
$identifier = $_GET['identifier'];

require_once("../../inc/ingame/game.php");

// http://omnimon.com/be/get_series_id_by_name.php?1=1&public_user_id=CRONIMON_USER_ID&name=syndicates_r67_spy_3

DEFINE("OMNIMON_PATH","http://api.cronimon.com/");
DEFINE("OMNIMON_USER", OMNIMON_USER_MASS);
DEFINE("OMNIMON_USER_ID", CRONIMON_USER_ID); 

DEFINE("GET_SERIES_URL" , OMNIMON_PATH."get_series_id_by_name.php?1=1&public_user_id=".OMNIMON_USER_ID);
DEFINE("MAKE_WIDGET_URL", OMNIMON_PATH."add_edit_widget_settings.php?1=1&public_user_id=".OMNIMON_USER_ID);
DEFINE("SAVE_SERIES_URL", OMNIMON_PATH."save_series.php?1=1&public_user_id=".OMNIMON_USER_ID);
DEFINE("GET_WIDGET_SETTINGS_URL" , OMNIMON_PATH."get_widget_settings.php?1=1");





$db = "syndicates";
if (isBasicServer($game)) {
  $db = "syndicates_basic";
}

$round = $globals['round'];

if (!$type || !$db || !$round || !$identifier) exit(1);
$series_name = make_omnimon_series_name($db,$round,$type,$identifier);

$series_id = get_series_id($series_name);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.$title.'</title>
</head>

<body style="margin:0px; padding:0px;">';
echo (get_widget(array($series_name)));
echo '</body>';


//
//
//  FUNCTIONS
//
//

function get_widget($series_names) {
  $widget_name       = implode("_",$series_names);
  $widget_identifier = md5($widget_name);
  
  $url = GET_WIDGET_SETTINGS_URL."&w_id=$widget_identifier";
  $widget_settings = (go($url));
  
  // Kein fehler strlen eg [-1]
  if (strlen($widget_settings) <= 5) {
    // Kein Widget -- erzeugen
    $series_ids = array();
    foreach ($series_names as $temp) {
      $temp_id = (int) get_series_id($temp);
      if ($temp_id != 0 && $temp_id != -1) {
        set_series_public($temp_id);
        $series_ids[] = $temp_id;
      }
    }
    make_widget($widget_name,$series_ids);
    
  }

    
  
  return get_widget_code($widget_name);
  
}

// return true/false
function widget_exists($widget_name) {
  
}

function make_widget($widget_identifier,$series_ids) {
  global $title;
  $url = MAKE_WIDGET_URL."&name=".urlencode($title)."&id_public=$widget_identifier&wt=1";
  if (count($series_ids) > 0) {
    foreach ($series_ids as $temp) {
      $url.="&series_id[]=$temp";
    }
  }
  
  $back = go($url);
  
}

function get_series_id($series_name) {
  $url = GET_SERIES_URL."&name=$series_name";
  $series_id = json_decode(go($url));
  $series_id = $series_id[0];
  return $series_id;
}

//
//  Setzt series auf public
//  Überschreibt dabei kategoriezuordnungen
//  Setzt titel auf den global gesetzten titel
//
function set_series_public($series_id) {
  global $title;
  $url = SAVE_SERIES_URL."&series_id=$series_id&ac=2&dn=".urlencode($title)."&sf=1&cid=0";
  $back = go($url);
}


function get_widget_code($widget_identifier) {
  //$widget_identifier = "demo1";
  global $title;
  $back = "<object width=\"520\" height=\"390\">
              <param name=\"wmode\" value=\"opaque\">
              </param>
              <param name=\"movie\" value=\"http://cronimon.com/widgets/CronimonWidget.swf\">
              </param>
              <param name=\"allowFullScreen\" value=\"true\"></param>
              <param name=\"allowscriptaccess\" value=\"always\"></param>
	      
	      <param name=\"flashvars\" value=\"externalSettings={
							'userId' : 'izqbdulg',
							'title'  : '".$title."',
							'chartSettings' : {
							    'series' : ['".$widget_identifier."']
							}
						    }&server_url=widgets.cronimon.com/&host=http://\" />

              <embed width=\"520\" height=\"390\" flashvars=\"externalSettings={
							'userId' : 'izqbdulg',
							'title'  : '".$title."',
							'chartSettings' : {
							    'series' : ['".$widget_identifier."']
							}
						    }&server_url=widgets.cronimon.com/&host=http://\"
              allowscriptaccess=\"always\" src=\"http://cronimon.com//widgets/CronimonWidget.swf\">
              </embed>
          </object>";
  
  return $back;
}

?>
