<?
##########################################################
#
#			 Mod Profiler - By Jannis Breitwieser
#
#			requires xtendsql + db conection
##########################################################

##########################################################
#
# class Profiler
#
# Methods:
#		init() - Tells the profiler to start the profiling action - also creates implicitly the first mark: init
#		end () - Tells the profiler to end the profiling action
# 		add_mark(name) - Adds a Profiling mark
#

class profiler {

	##########################################################
	#			Constructor
	##########################################################

	function profiler() {
		if (func_num_args() > 0) {$arg = func_get_arg(0);}
		global $SCRIPT_NAME;
		if (!$SCRIPT_NAME) {
			$SCRIPT_NAME = "none";
		}
		// Check if tables exist - otherwise create tables
		$tablesok = mysql_query("select run_id from mod_profiler_runs limit 1");
		if (mysql_errno() > 0) {
			$this->create_tables();
		}
		$this->time = time();
		$this->marks= array();
		$this->markscounter=0;
		$this->script = $SCRIPT_NAME. " $arg";

	}

	##########################################################
	##########################################################
	#			PUBLIC FUNCTIONS
	##########################################################
	##########################################################

	function init() {
		select("insert into mod_profiler_runs (time,script) values (".$this->time.",'".$this->script."')");
		$this->run_id = single("select run_id from mod_profiler_runs where time=".$this->time." and script='".$this->script."'");
		$this->add_mark("init");
	}

	function end() {
		$this->add_mark("end");
		$qstring = "insert into mod_profiler_marks (run_id,mark_id,mark_name,mark_time) values ";

		foreach ($this->marks as $temp) {
			$qstring.=" ($temp[run_id],$temp[mark_id],'$temp[mark_name]','$temp[mark_time]'),";
		}
		$qstring = chopp($qstring);
		if ($this->markscounter > 0) {
			select("$qstring");
		}
	}

	function add_mark($arg) {
		$exists = 0;
		$microtime = $this->getmicrotime();
		$this->markscounter++;
		$temp = $this->marks;
		foreach ($temp as $bla) {
			if ($bla[mark_name] == $arg) {
				$exists = 1;
			}
		}
		if (!$exists) {
			$this->marks[] = array(mark_id=>$this->markscounter,mark_time=>$microtime,mark_name=>$arg,run_id=>$this->run_id);
		}

	}




	##########################################################
	#			PRIVATE FUNCTIONS
	##########################################################
	function create_tables() {
		select(" CREATE TABLE IF NOT EXISTS `mod_profiler_runs` (
		`run_id` INT NOT NULL,
		`time` INT NOT NULL,
		`script` VARCHAR(255) NOT NULL
		)");
		select(" ALTER TABLE `mod_profiler_runs` DROP PRIMARY KEY, ADD PRIMARY KEY(`run_id`) ");
		select(" CREATE TABLE IF NOT EXISTS `mod_profiler_marks` (
		`run_id` INT NOT NULL,
		`mark_id` INT NOT NULL,
		`mark_name` VARCHAR(255) NOT NULL,
		`mark_time` VARCHAR(255) NOT NULL
		)");
		select(" ALTER TABLE `mod_profiler_marks` ADD INDEX(`run_id`) ");
		select(" ALTER TABLE `mod_profiler_runs` CHANGE `run_id` `run_id` INT(11) DEFAULT '0' NOT NULL AUTO_INCREMENT");
	}

	function getmicrotime() {
		list($usec,$sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}


}

?>
