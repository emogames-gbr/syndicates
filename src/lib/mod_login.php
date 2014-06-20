<?
#
# login_mysql.php
#
# Realises a login / sessionid system for mysql databases
#
# creates tables: sids (sessionids) ,sids_save (for old sessioniods)
# needs user_ids
#

# requires : mysql-databse / connection-handle

#####################################
#                                   #
# Copyright: Jannis Breitwieser     #
# Last updated: 22/02/2004          #
#                                   #
#####################################


# User - Functions:
#
#       login($id,[$duration]) : array[id,sid]
#        // Generates Sessionid for User for $duration in minutes
#        // Standard duration is 60 minutes
		 // Creates the Cookievariables: $loginid and $loginsid, these have to be used for checksid (this will be done
		 // automatically)
#
#	logout($id) : bool
#	 // User will be logged out
#
#       checksid([$id,$sid]) : bool
#        // returns true is sid is ok, otherwise returns false
#		 // If no values are submitted standard cookie values will be taken
#
#       emptysave() : bool
#        // deletes all saved sids
#
#       getsid($id) : string
#        // returns sid of user if sid available
#
#		getid($sid) : int
#        // return id of sid
#



# Table Structure:
#
#       sids/sids_safe: id - int, ctime - int, etime - int,sid varchar(48) // ctime means creation time, etime end time
#

function login() {
        if (func_num_args() < 1) {
                return false;
        }
        $id = func_get_arg(0);
        if (func_num_args() == 2) {
                $duration = func_get_arg(1)*60; // -> Loginzeit in minuten
        }
        else {
                $duration = 60*60;
        }
        $c1 = rmsid($id);
        if (!$c1) {
                $c2 = maketables();
        }
        
        if (func_num_args() == 3) {
        	$databases =func_get_arg(2);
        }
        
        $sid = makesid($id);
        $ctime = time();
        $etime = $ctime + $duration;
        
		setcookie("loginsid","$sid",-1,"/",".".DOMAIN);
		setcookie("loginid","$id",-1,"/",".".DOMAIN);
		//echo "DOMAIN: ".DOMAIN;
	    $ip = getenv ("REMOTE_ADDR");
	    if (is_array($databases) && count($databases) > 1) {
	    	foreach ($databases as $temp) {
		        $s1 = mysql_query("replace into $temp.sids (ctime,etime,id,sid,ip,iptrue) values('$ctime','$etime','$id','$sid','$ip','".get_ip()."')");
		        
		        if (mysql_error()) {
					return false;
				}
		        else {
					$data=array('id'=>$id,'sid'=>$sid);
		        }
	    	}
	    }
	    else {
	    
	        $s1 = mysql_query("replace into sids (ctime,etime,id,sid,ip,iptrue) values('$ctime','$etime','$id','$sid','$ip','".get_ip()."')");
	        
	        if (mysql_error()) {
				return false;
			}
	        else {
				$data=array('id'=>$id,'sid'=>$sid);
	        }
			
        }
		return $data;
		
}

function checksid() {
	if (func_num_args() != 2) {
		global $loginid;
		global $loginsid;
		$id = $loginid;
		$sid = $loginsid;
	}
	else {
		$id = func_get_arg(0);
		$sid = func_get_arg(1);
	}
	$id = (int) $id;
	if (!$id || !$sid) {return false;}
	$s2 = mysql_query("select sid,etime from sids where id=\"".$id."\";");
    if (mysql_error()) { return false;}
    $cmpsid = mysql_fetch_row($s2);
    $time = time();
    # print $cmpsid[0]." ".$sid."<br>".$time." ".$cmpsid[1];
    if ($cmpsid[0] == $sid && $time < $cmpsid[1]) {
           return true;
    }
    else {return false;}
}


function emptysave() {
        $s6 = mysql_query("delete from sids_safe");
        if (mysql_error()) {
                return false;
        }
        else {return true;}
}

function getsid() {
        if (func_num_args() != 1) {
                return false;
        }
        else {
                $id = func_get_arg(0);
                $s7 = mysql_query("select sid from sids where id = $id limit 1");
                if (mysql_error()) {return false;}
                else {
                        $sid = mysql_fetch_row($s7);
                        return $sid[0];
                }
        }
}

function getid() {
	if (func_num_args() != 1) {
		global $loginsid;
		$sid = $loginsid;
	}
	else {
		$sid = func_get_arg(0);
	}
	if (!$sid) {
		return 0;
	}
	else {
		$s8 = mysql_query("select id from sids where sid='$sid' limit 1");
		if (mysql_error()) {return false;}
		else {
				$id = mysql_fetch_row($s8);
				return $id[0];
		}
	}

}

function logout() {
	if (func_num_args() != 1) {
		global $loginid;
		$id = $loginid;
	}
	rmsid($id);
	setcookie("loginsid","",-1,"/",".".DOMAIN);
	setcookie("loginid","",-1,"/",".".DOMAIN);
}













/////////////////////////////////////////////////////////////////////////////////////////////////////////////

# Additional Functions for mod_mysql

#####################################
#                                   #
# Copyright: Jannis Breitwieser     #
# Last updated: 23/02/2004          #
#                                   #
#####################################

function maketables() {
        $tablename = "sids";
        $commands[] = "create table $tablename (id int primary key,ctime int,etime int,sid varchar(255),ip varchar(255))";
        $tablename = "sids_safe";
        $commands[] = "create table $tablename (id int,ctime int,etime int,sid varchar(255),index(`id`),ip varchar(255))";
        foreach ($commands as $temp) {
                $s1 = mysql_query($temp);
                //echo error();
        }
        if (mysql_error()) {return false;}
        else {return true;}

}

function makesid() {
		if (func_num_args() > 0) {
			$tempid = func_get_arg(0);
		}
        list($usec,$sec) = explode(" ", microtime());
        $usec*= 1000000;
        mt_srand($usec);
        $tokens = array();
        for ($i = 48; $i <= 57; $i++) {
                $tokens[] = chr($i);
        }
        for ($i = 65; $i <= 90; $i++) {
                $tokens[] = chr($i);
        }
        for ($i = 97; $i <= 122; $i++) {
                $tokens[] = chr($i);
        }
        $sid = "";
        $alength = count($tokens);
        for ($a = 0; $a <=47; $a++) {
                $rand = mt_rand(0,$alength);
                $sid.= $tokens[$rand];
        }
		if ($tempid) {
			list($usec,$sec) = explode(" ",microtime());
			$bla = (($sec-1000000000) + round($usec*1000)/1000)*1000;
			mt_srand($bla);
			$sid.=crypt($tempid, mt_rand(10,99));
			// Änderung von Nicolas  2200204 $sid.=crypt($accountid, mt_rand(10,99)).$accountid;
		}
        return $sid;

}

#       rmsid($id) : bool
#        // deletes sid from sids, moves sid to sids_save
#

function rmsid() {
        if (func_num_args() != 1) {
                return false;
        }
        else {
                $id = func_get_arg(0);
                $s3 = mysql_query("select * from sids where id = $id limit 1");
                @$data = mysql_fetch_assoc($s3);
				if ($data[sid]) {
	                $s4 = mysql_query("insert into sids_safe (ctime,etime,id,sid,ip) values ('$data[ctime]','$data[etime]','$data[id]','$data[sid]','$data[ip]')");
				}
                $s5 = mysql_query("delete from sids where id = $id");
                if (mysql_error()) {return false;}
                else {return true;}
        }
}

function extractIP($ip) { 
    $b = ereg ("^([0-9]{1,3}\.){3,3}[0-9]{1,3}", $ip, $array); 
    if ($b) { 
            return $array; 
        } else { 
            return false; 
        } 
}  

function get_ip() { 
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // case 1.A: proxy && HTTP_X_FORWARDED_FOR is defined 
        $array = extractIP($_SERVER['HTTP_X_FORWARDED_FOR']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 
    if (!empty($_SERVER['HTTP_X_FORWARDED'])) { // case 1.B: proxy && HTTP_X_FORWARDED is defined 
        $array = extractIP($_SERVER['HTTP_X_FORWARDED']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 
    if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) { // case 1.C: proxy && HTTP_FORWARDED_FOR is defined 
        $array = extractIP($_SERVER['HTTP_FORWARDED_FOR']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 
    if (!empty($_SERVER['HTTP_FORWARDED'])) { // case 1.D: proxy && HTTP_FORWARDED is defined 
        $array = extractIP($_SERVER['HTTP_FORWARDED']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) { // case 1.E: proxy && HTTP_CLIENT_IP is defined 
        $array = extractIP($_SERVER['HTTP_CLIENT_IP']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 

    if (!empty($_SERVER['HTTP_VIA'])) { 
    // case 2:  
    // proxy && HTTP_(X_) FORWARDED (_FOR) not defined && HTTP_VIA defined 
    // other exotic variables may be defined  
    return ( $_SERVER['HTTP_VIA'] .  
            '_' . $_SERVER['HTTP_X_COMING_FROM'] . 
            '_' . $_SERVER['HTTP_COMING_FROM']     
          ) ; 
    } 
    if (!empty($_SERVER['HTTP_X_COMING_FROM']) OR !empty($_SERVER['HTTP_COMING_FROM'])) { 
    // case 3: proxy && only exotic variables defined 
    // the exotic variables are not enough, we add the REMOTE_ADDR of the proxy 
    return ( $_SERVER['REMOTE_ADDR'] .  
            '_' . $_SERVER['HTTP_X_COMING_FROM'] . 
            '_' . $_SERVER['HTTP_COMING_FROM']     
          ) ; 
    } 
  
     
    // case 4: no proxy (or tricky case: proxy+refresh) 
    if (!empty($_SERVER['REMOTE_HOST'])) { 
        $array = extractIP($_SERVER['REMOTE_HOST']); 
        if ($array && count($array) >= 1) { 
            return $array[0]; // first IP in the list 
        } 
    } 
     
    return $_SERVER['REMOTE_ADDR']; 
} 


?>
