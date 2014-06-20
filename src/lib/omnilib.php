<?
$omnimon_user_id = "CRONION-USER"; // Emogames-User
$omnimon_url = ".put.cronimon.de/insert_values.php";

DEFINE("MAX_DATAPOINTS_PER_REQUEST",100);


// Send multiple datapoints in a single request
function omniputs($names,$values,$times,$user_id="") {
  if (count($names) != count($values) || count($values) != count($times)) {
    return false;
  }
  
  while (count($names) > MAX_DATAPOINTS_PER_REQUEST) {
    $names_temp = array();
    $values_temp = array();
    $times_temp = array();
    
    for ($i=0; $i < MAX_DATAPOINTS_PER_REQUEST; $i++) {
      $names_temp[] = array_shift($names);
      $values_temp[] = array_shift($values);
      $times_temp[] = array_shift($times);
    }
    omniputs($names_temp,$values_temp,$times_temp,$user_id);
  }
  
  global $omnimon_user_id, $omnimon_url;
  if ($user_id != "")$omnimon_user_id = $user_id;
  if (!$omnimon_user_id) die("no \$omnimon_user_id set!");
  if (!$omnimon_url) die("no \$omnimon_url set!");
  omni_resetParams();
  omni_setParam("name", $names);
  omni_setParam("time", $times);
  omni_setParam("value", $values);
  $omnimon_url_ext = "http://".$omnimon_user_id.$omnimon_url;
  omni_go($omnimon_url_ext, "omnilib",0,$omnimon_user_id);  
  
  
  return true;
}

function omniput($name, $value, $time = 0,$user_id="") {
  global $omnimon_user_id, $omnimon_url;
  if ($user_id != "")
  if (!$omnimon_user_id) die("no \$omnimon_user_id set!");
  if ($user_id != "") $omnimon_user_id = $user_id;
  
  if (!$omnimon_url) die("no \$omnimon_url set!");
  omni_resetParams();
  omni_setParam("name", "$name");
  omni_setParam("time", $time);
  omni_setParam("value", $value);
  $omnimon_url_ext = "http://".$omnimon_user_id.$omnimon_url;
  omni_go($omnimon_url_ext, "omnilib",0,$omnimon_user_id);
}

function omni_go_post($interface, $referer) {
  omni_go($interface, $referer, 1);
}
function omni_go($interface, $referer, $post = 0,$omnimon_user_id="") {
                global $omni_params;
                global $cookie;
                foreach ($omni_params as $ky => $vl) {
                        if (!is_array($vl)) {
                          $params_prepared[] = urlencode($ky)."=".urlencode($vl);
                        }
                        else {
                          foreach ($vl as $vl_inner) {
                            $params_prepared[] = urlencode($ky)."[]=".urlencode($vl_inner);
                          }
                        }
                }
                $final_params = join("&", $params_prepared);

                $ch  = curl_init();
                if ($post == 1) curl_setopt($ch, CURLOPT_POST, 1);
                if ($post == 1 && $final_params) 
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $final_params);
                if ($post == 1) {
                  curl_setopt ($ch, CURLOPT_URL,$interface);
                } else {
                  curl_setopt ($ch, CURLOPT_URL,$interface."?$final_params");
                }
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, REFERER, $referer);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.8.0.5) Gecko/20060719 Firefox/1.5.0.5");
                if ($cookie) {
                        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                }

                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                $erg = curl_exec ($ch);
                curl_close($ch);

                // Cookie nehmen und merken
                preg_match("/Set-Cookie: ?([^;]+);/", $erg, $treffer);
                if ($treffer[1]) {
                        $cookie = $treffer[1];
                }


                return $erg;
}
function omni_setParam($type, $value) {
        global $omni_params;
        $omni_params[$type] = $value;
}
function omni_resetParams() {
  global $omni_params;
  $omni_params = array();
}
?>