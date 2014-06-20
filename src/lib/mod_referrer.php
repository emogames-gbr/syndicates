<?
##################################################################
##################################################################
#		Mod referrer - by Jannis Breitwieser
#		30122004
#
#		requires: subs.php - database connection
#
##################################################################
##################################################################



##################################################################
##################################################################
#
#	Function reference:
##################################################################
/*

	IMPORTANT NOTES:
		- IF You want people to be logged by a specific variable, $ref_src has to be set!
		
		
		
 class referrer

 
 Cookies of this class:
 	- mod_referrer_ident 
 
 
 Methods:
		constructor() - Sets a mod_referrer_ident cookie by which the user is recognized if not existent.
		become_user($referrer_ident,$user_id) - The  user wille be taken from table mod_referrer_temp to mod_referrer, getting the given user_id
 		get_ident - returns the referrers ident.
 		get_referrer_by_src - returns ident by source
 		get_referrer_by_browser - returns browser referrer


*/
##################################################################
##################################################################

class referrer {

	##########################################################
	#			Constructor
	##########################################################
	function referrer() {
		global $ref_src,$mod_referrer_ident;
		if (!$time) {$this->time = time();} else {$this->time = $time;}
		
		// Check is user ist already known in system by a cookie
		$this->ref_ident = $mod_referrer_ident;
		
		// Otherwise Create Random Ident and set cookie
		if (!$this->ref_ident) {
			mysql_query("select referrer_ident from mod_referrer_temp limit 1");
			if (mysql_errno() > 0) {
				$this->create_tables();
			}
			// Daten aufnehmen
			$this->referrer_by_src = $ref_src;
			$this->referrer_by_browser = $_SERVER[HTTP_REFERER];
			
			// Key eintragen
			$exists = 1;
			while ($exists) {
				$random_ident = createkey();
				$exists = single("select referrer_ident from mod_referrer_temp where referrer_ident='$random_ident'");
			}
			// hier macht ie 6 probleme // 
			$endtime = $this->time + 60*60*24*31; // Cookie soll ein Monat lang halten, wird gelöscht, wenn aus dem User ein Final user geworden ist
			setcookie("mod_referrer_ident",$random_ident,$endtime);
			$this->ref_ident = $random_ident;
			select("insert into mod_referrer_temp (referrer_ident,time,referrer_by_src,referrer_by_browser)
					values ('".$this->ref_ident."',".$this->time.",'".$this->referrer_by_src."','".$this->referrer_by_browser."')
			");
		}
	}
	

	##########################################################
	##########################################################
	#			PUBLIC FUNCTIONS
	##########################################################
	##########################################################

	function become_user($referrer_ident,$user_id) {
		
		$data = assoc("select * from mod_referrer_temp where referrer_ident='$referrer_ident'");
		$this->referrer_by_src = $data[referrer_by_src];
		$this->referrer_by_browser = $data[referrer_by_browser];
		
		$exists = single("select user_id from mod_referrer where user_id = '$user_id' ");
		if (!$exists) {
			if (strlen($this->referrer_by_src) > 0 || strlen($this->referrer_by_browser) > 0) {
				select("insert into mod_referrer (user_id,time,referrer_by_src,referrer_by_browser)
						values ('$user_id',".$this->time.",'".$this->referrer_by_src."','".$this->referrer_by_browser."')
				");
			}
		}
		select("
			delete from mod_referrer_temp where referrer_ident = '".$referrer_ident."'
		");
		setcookie("mod_referrer_ident","",-1); // Cookie löschen
	}
	
	function get_ident() {
		return $this->ref_ident;
	}
	
	function get_referrer_by_src() {
		$ref_by_src = single("select referrer_by_src from mod_referrer_temp where referrer_ident='".$this->ref_ident."'");
		return $ref_by_src;
	}
	function get_referrer_by_browser() {
		$ref_by_browser = single("select referrer_by_browser from mod_referrer_temp where referrer_ident='".$this->ref_ident."'");
		return $ref_by_browser;
	}
	
	
	##########################################################
	#			PRIVATE FUNCTIONS
	##########################################################
	function create_tables() {
		select("
			CREATE TABLE IF NOT EXISTS mod_referrer (
			  user_id varchar(255) NOT NULL default '',
			  time int(11) NOT NULL default '0',
			  referrer_by_src varchar(255) NOT NULL default '',
			  referrer_by_browser varchar(255) NOT NULL default '',INDEX(user_id)
			)
		");
		select("
			CREATE TABLE IF NOT EXISTS mod_referrer_temp (
			  referrer_ident varchar(255) NOT NULL default '',
			  time int(11) NOT NULL default '0',
			  referrer_by_src varchar(255) NOT NULL default '',
			  referrer_by_browser varchar(255) NOT NULL default '',PRIMARY KEY(referrer_ident)
			) 
		");
	}	

	
}


?>
