<?
/**
* Stellt erweiterte Datenbankfunktionen zur Verfügung
* @author Jannis Breitwieser
* @copyright Jannis Breitwieser
* @date 27.03.2005
* @package ExtendSql
* 
*/


/**
* Für insert oder Updates benutzen
* Gibt lesbare ausführliche Fehlermeldung, wenn Request nicht funktioniet
* Zählt Anzahl Datenbankzugriffe pro Skriptdurchlauf
* @param String $query Beliebige Sql Anfrage
* @param int $show_calls - wenn gesetzt, wird action ignoriert und einfach die anzahl der bisher getätigten db aufrufe zurückgegeben
* @return SqlResult
*/


####
####	CONFIG FÜR XTENDSQL
####
	DEFINE (xtendsql_LOG_NONINDEXED_QUERIES,0);
	DEFINE (xtendsql_SHOW_ERRORS,1);
	DEFINE (xtendsql_LOG_ERRORS,1);
	DEFINE (xtendsql_PRINT_STATEMENTS,0);  
	DEFINE (xtendsql_LOG_QUERY_TIME,1);
####
####	CONFIG ENDE
####



function select($action,$show_calls = 0) {
	
	
    // Holt Daten aus db, übergeben wird ein sql kommando, zurückgegeben wird die referenz auf das db rückgabeobjekt
    static $calls;
    static $time_local;
    static $timesum;
    
    if (!$time_local) {
    	$time_local = time();
    }
    
    
    ####
    ####	Return, wenn nur anzahl aufrufe ausgegeben werden soll
    ####
    if ($show_calls == 1) {
    	return $calls;
    }
    elseif ($show_calls == 2) {
    	return $timesum;
    }
    
    
    $calls++;

    ##
    ## Langsame selects suchen ?
    ##
    if (xtendsql_LOG_NONINDEXED_QUERIES) {
    	
    	$slow_queries_exists = mysql_query("select query_id from unindexed_queries limit 1");
    	if (mysql_errno()) {
    		mysql_query("create table unindexed_queries (query_id int primary key auto_increment,query varchar (255),exp varchar(255))");
    	}
    	
	    $explain_result = mysql_query("explain $action");
	    @$explain = mysql_fetch_assoc($explain_result);
	    if (is_array($explain) && count($explain) > 0 && !$explain[possible_keys] && strtolower(substr($action,0,6)) == "select" && !$explain[key]) {
		    $expstring = "";
		    foreach ($explain as $key => $value) {
		    	$expstring .= $key.":".$value."\n";
		    }
		    $ip_action = preg_replace("/'/","\'",$action);
		    select("insert into unindexed_queries (query,exp) values ('$ip_action','$expstring') ");
	    }
    }
    
    ##
    ##
    ##
    if (xtendsql_LOG_QUERY_TIME) {
		$before = getmicrotime();    	
    }
    $result = @mysql_query($action);
    if (xtendsql_LOG_QUERY_TIME) {
    	$after = getmicrotime();
    	$duration = $after-$before;
    	$timesum += $duration;
    }
    if (xtendsql_PRINT_STATEMENTS) {
	   	pvar($action); 
    }
    
    $error = mysql_error();
    if ($error) {
    	
    	###
    	###	Fehler anzeigen
    	###
    	if (xtendsql_SHOW_ERRORS) {
			echo "<b><font color = \"orange\">FEHLER IN ACTION:</font></b>".$action."<br>";
	        echo $error."<br>";
    	}
		
    	
    	###
    	###	Fehler loggen
    	###
    	if (xtendsql_LOG_ERRORS) {
	        // Fehler in Datenbank loggen

	        // Syndicates-spezifisch {
	        global $pagestats,$status;
           if (!$status{id}) {$id = 0;} else {$id = $status{id};}
           if (!$pagestats) {$page = "external script";} else {$page = $pagestats{name};}
	        // }
	        $page .= "\n=>\$_REQUEST: ".print_r($_REQUEST,1);
	        $page .= "\n=>\$_SERVER: ".print_r($_SERVER,1);
	        $page = addslashes($page);
            @mysql_query("insert into mysql_errors (time,error,statement,user_id,page) values ('".$time_local."','".addslashes($error)."','".addslashes($action)."', '$id', '".$page."')");
            if (mysql_errno()) {
            	@mysql_query("create table mysql_errors (time int,error varchar (255), statement varchar(255),page text)");
	            @mysql_query("insert into mysql_errors (time,error,statement,user_id,page) values ('".$time_local."','".addslashes($error)."','".addslashes($action)."','$id','".$page."')");
            }
    	}
    } 
    $querystring.=$action."<br>";
	
	$log_statements = 0;
	if ($log_statements)	{
		if (preg_match('/insert into/', $action))	{	}
		else	{
		$temp = preg_replace('/([^(=| )]+) ?= ?((\')[^\']+\',?|[^(,| )]+)/',"\\1", $action);

		mysql_query("insert into mysql_statements (statement) values ('$temp')");
		}
	}
	
    return $result;
}

/**
* Gibt ein eindimensionales array einer ZEILE zurück
* @param String Sql Query
* @return Array
*/

function row($query) {
    $result = select($query);
    $return = mysql_fetch_row($result);
    return $return;
}

/**
* Gibt ein zweidimensionales array mehrer Zeilen zurück
* @param String Sql Query
* @return Array
*/

function rows($query) {
    $return = array();
    $result = select($query);
    while ($returnstatus = mysql_fetch_row($result)) {
        array_push($return,$returnstatus);
    }
    return $return;
}


/**
* Gibt ein eindimensionales assoziatives array einer ZEILE zurück
* @param String Sql Query
* @return Array
*/

function assoc($query) {
    $result = select($query);
    $return = mysql_fetch_assoc($result);
    return $return;
}

/**
* Gibt ein zweidimensionales assoziatives array mehrer Zeilen zurück
* @param String Sql Query
* @return Array
*/

function assocs($query) {
    if (func_num_args() > 1) {$opt = func_get_arg (1);}
        $return = array();
    $result = select($query);
    if (!$opt) {
        while ($returnstatus = mysql_fetch_assoc($result)) {
            array_push($return,$returnstatus);
        }
    }
    else {
        while ($returnstatus = mysql_fetch_assoc($result)) {
            	$return{$returnstatus{$opt}} = $returnstatus;
        }
    }
    return $return;
}

/**
* Gibt den Wert einer einzelnen Zelle zurück
* @param String Sql Query
* @return String
*/

function single($query) {
    $result = select($query);
    $return = mysql_fetch_row($result);
    $return = $return[0];
    return $return;
}

/**
* Gibt ein Array einer SPALTE zurück
* @param String Sql Query
* @return Array
*/

function singles($query) {
    $return = array();
    $result = select($query);
    while ($returnstatus = mysql_fetch_row($result)) {
        array_push($return,$returnstatus[0]);
    }
    return $return;
}


/**
* Gibt ein eindimensionales Array von Objekten zurück
* @param String Sql Query
* @param String Name der Klasse von der die Objekte erzeugt werden sollen
* @return Array
*/


function objects($query,$classname) {
    $return = array();
    $result = assocs($query);


    foreach ($result as $temp) {
    	$object_temp = new $classname($temp);
    	$return[] = $object_temp;
    	neu($classname,$object_temp->getPrimary(),"",0,$object_temp);
    }
    return $return;
}


?>
