<?
	/**
	 * Autor: Konstantin Grupp/inok
	 * 
	 * wird eingebunden in inc/game.php (am Ende)
	 * 
	 */

	class Events {
		
		private static $tpls = "";
		
		private static $event_settings = array();
		private static $events = array();
		
		public static function load() {
			global $status;
			Events::$event_settings = assocs("SELECT * FROM events_settings", 'type');
			Events::$events = assocs("SELECT * FROM events WHERE konzernid = ".$status['id'], 'type');
		}
		
		public static function getSettings($type,$field) {
			return Events::$event_settings[$type][$field];
		}
		public static function getEvent($type,$field = 'value') {
			return Events::$events[$type][$field];
		}
		
		public static function assign($var) {
			Events::$tpls = $var;
		}
		
		/**
		 * Wird in header.php aufgerufen um die tpls an richtiger Stelle
		 * auszugeben
		 */
		public static function display() {
			global $tpl;
			if (is_array(Events::$tpls)) {
				foreach(Events::$tpls as $vl) {
					$tpl->display($vl);
				}
			} elseif (Events::$tpls != "") {
				$tpl->display(Events::$tpls);
			}
		}
		
	}
	
	Events::load();

	require("events/dreikoenig.php");
	require("events/ostern.php");
	require("events/weltuntergang.php");
	require("events/weihnachten.php");

?>