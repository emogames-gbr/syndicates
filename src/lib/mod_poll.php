<?

# requires : mysql-databse / connection-handle

#####################################
#                                   #
# Copyright: Jannis Breitwieser     #
# Last updated: 26/09/2004          #
#                                   #
#####################################

/*
* Function:
* save() -> Speichern
* delete() -> L?schen
* poll(id) -> neuen poll erzeugen, id optional
* get/set funktionen f?r alle vars
* vote($poll_option,$user_id) -> stimme f?r eine polloption
* canvote($user_id) -> Bool, kann user voten ?
*/

class poll {

	/*
	* @VARS
	*/
	var $name; // Pollname
	var $poll_id;
	var $time; // Pollstart
	var $user_id; // Gemacht von unser
	var $user_name; // Gemacht von username
	var $time_bis; // Poll g?ltig bis
	var $votes_total; // Anzahl Stimmen f?r den Poll bisher
	var $synd_id; // Poll ist nur f?r das Syndikat sichtbar - 0 = f?r alle sichtbar



	/*
	*	Class constructor
	*	@Input: Int - id (optional) l?dt
	*/

	function poll () {
		$this->options = array();
		if (func_num_args() > 0) {
			$var = func_get_arg(0);
		}
		if ($var) {
			$temp = assoc("select * from polls where poll_id=$var and deleted=0");
			$options = assocs("select * from polls_options where poll_id=$var");
			$this->set_poll($temp);
			foreach ($options as $ttemp) {
				$this->addOption($ttemp);
			}
		}
		else {
			$this->time = time();
		}
	}


	function getName() {
		return $this->name;
	}
	function getPoll_id() {
		return $this->poll_id;
	}
	function getTime() {
		return $this->time;
	}
	function getUser_id() {
		return $this->user_id;
	}
	function getUser_name() {
		return $this->user_name;
	}
	function getTime_bis() {
		return $this->time_bis;
	}
	function getVotes_total() {
		return $this->votes_toal;
	}
	function getSynd_id() {
		return $this->synd_id;
	}
	function getOptions() {
		return $this->options;
	}


	function setName($var) {
		$this->name =$var;
	}
	function setTime($var) {
		$this->time = $var;
	}
	function setUser_id($var) {
		$this->user_id = $var;
	}
	function setUser_name($var) {
		$this->user_name = $var;
	}
	function setTime_bis($var) {
		$this->time_bis = $var;
	}
	function setVotes_total($var) {
		$this->votes_toal = $var;
	}
	function setSynd_id($var) {
		$this->synd_id = $var;
	}

	function canVote($user_id) {
		if ($this->poll_id) {
			$voted = single("select user_id from users_votes where poll_id=".$this->poll_id."");
		}
		else {
			$this->message="Diese Umfrage wurde noch nicht abgespeichert!";
		}
	}

	function set_poll($array) {
		foreach ($array as  $key => $value) {
			$this->{$key} = $value;
		}
	}

	function addOption($array) { // Es muss mindestns ein "Inhalt" angegeben werden, kann aber auch ein PollTable array gegeben werden
		if (!$this->poll_id) {
			$this->_reload();
		}
		if (!is_array($array)) {
			$array = array(content => $array);
		}
		if (!$array[votes]) {$array[votes]=0;}
		if (!$array[option_id]) {$array[option_id]=0;}
		$array[content] = htmlentities($array[content],ENT_QUOTES);
		$this->options[] = new poll_option(&$this,$array[content],$array[votes],$array[option_id]);
		$this->save();
	}


	function _reload() {
		if (func_num_args() == 0) {
			$this->save();
			$thisid = single("select poll_id from polls where time=".$this->getTime()." and name='".$this->getName()."'");
		}
		else {
			$thisid = func_get_arg(0);
		}
		$this = new poll($thisid);
		echo "hier";

	}

	function save() {
		// Neuer Eintrag
		$this->name = htmlentities($this->name,ENT_QUOTES);
		if (!$this->poll_id) {
			select("
					insert into polls
					(poll_id,name,time,user_id,user_name,time_bis,votes_total,synd_id)
					values
					('".$this->poll_id."','".$this->name."','".$this->time."','".$this->user_id."','".$this->user_name."','".$this->time_bis."',0,'".$this->synd_id."')
			");
			if ($this->options) {
				foreach ($this->options as $temp) {
					$temp->save();
				}
			}
			$thisid = single("select poll_id from polls where time=".$this->getTime()." and name='".$this->getName()."'");
			$this->poll_id = $thisid;
			$this->_reload($this->poll_id);
		}
		// Speichern
		else {
			select("
				update polls set
				name='".$this->name."',
				time='".$this->time."',
				user_id='".$this->user_id."',
				user_name='".$this->user_name."',
				time_bis='".$this->time_bis."',
				votes_total='".$this->votes_total."',
				synd_id='".$this->synd_id."'
			");
			if ($this->options) {
				foreach ($this->options as $temp) {
					$temp->save();
				}
			}
			$this->_reload($this->poll_id);
		}
	}

	function delete() {
		if ($this->poll_id) {
			select("update polls set deleted=1 where poll_id=$this->poll_id");
		}
	}

	function vote($poll_option,$user_id) {
		if ($this->options) {
			$votedyet = single("select user_id from users_votes where poll_id=".$this->poll_id."");
			if (!$votedyet) {
				foreach ($this->options as $key => $temp) {
					if ($temp->getOption_id() == $poll_option) {
						$this->addVote();
						$this->options[$key]->_addVote();
						select("insert into users_votes (user_id,time,poll_id,option_id)
								values
								($user_id,".time().",".$this->poll_id.",'".$poll_option."')
						");
					}
				}
			}
			else {
				$this->message="Sie k?nnen nur einmal bei einer Umfrage abstimmen!";
			}
		}
	}

	function addVote() {
		$this->votes_total++;
	}

	function getMessage() {
		if ($this->message) {
			return $this->message;
		}
		else {
			return 0;
		}
	}

}


/*

Poll Option klasse

functions:
	function poll_option($poll,$poll_id,)  -- Konstruktor
	function _addVote() -- Stimme f?r diese Option abgeben
	function save() -- Speicher objekt ab
	function getContent() -- Gib Inhalt aus
	function getResultAbs() -- Absolute Anzahl Stimmen f?r diese Option
	function getResultRel() -- Relative Anzahl Stimmen


*/

/*
class poll_option {
	var $poll_id;
	var $content;
	var $votes;
	var $option_id;
	var $poll;


	function poll_option($poll,$content,$votes,$option_id) {
		$this->content = $content;
		$this->poll_id = $poll->getPoll_id();
		$this->votes = $votes;
		$this->poll = &$poll;

		if ($option_id) {
			$this->option_id = $option_id;
		}
		else {
			$this->option_id = 0;
		}
	}

	function _addVote() {
		$this->votes++;
		$this->save();
	}

	function save() {
		// Insert
		if (!$this->option_id) {
			select("
				insert into polls_options (poll_id,content,votes)
				values
				(".$this->poll_id.",'".$this->content."',".$this->votes.")
			");
			$this->option_id = single("select max(option_id) from polls_options");
		}
		// Update
		else {
			select("
				update polls_options set
				content='".$this->content."',
				votes = '".$this->votes."'
			");
		}
	}

	function delete() {

	}

	function getContent() {
		return $this->content;
	}

	function getResultAbs() {
		return $this->votes;
	}
	function getResultRel() {
		return ($this->votes / $this->poll->getVotes());
	}
}
*/

/*
 CREATE TABLE `polls` (
`poll_id` INT NOT NULL,
`name` VARCHAR(255) NOT NULL,
`time` INT NOT NULL,
`user_id` INT NOT NULL,
`user_name` VARCHAR(255) NOT NULL,
`time_bis` INT NOT NULL,
`votes_total` INT NOT NULL,
`synd_id` INT NOT NULL,
`deleted` TINYINT DEFAULT '0' NOT NULL,
PRIMARY KEY (`poll_id`)
);


 CREATE TABLE `polls_options` (
`poll_id` INT NOT NULL,
`content` TEXT NOT NULL,
`votes` INT NOT NULL,
`option_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY
);

 ALTER TABLE `s4`.`polls` CHANGE `poll_id` `poll_id` INT(11) DEFAULT '0' NOT NULL AUTO_INCREMENT


  CREATE TABLE `users_votes` (
`user_id` INT NOT NULL,
`time` INT NOT NULL,
`poll_id` INT NOT NULL,
`option_id` INT NOT NULL
);

 ALTER TABLE `users_votes` ADD INDEX(`user_id`)
 ALTER TABLE `users_votes` ADD INDEX(`poll_id`)
 ALTER TABLE `polls` ADD INDEX(`synd_id`)
 ALTER TABLE `polls_options` ADD INDEX(`poll_id`)
*/
