<?
/**
* class js
* Collection of Javascript functions
* @author Jannis Breitwieser
* @copyright Jannis Breitwieser
* @date 27.03.2005
* @package Emogames Mw Project
* 
*/


class Js  {
	
	
	public static $loadClassChange=0;
	public static $loadOver=0;
	public static $loadCountdown= 0;
	
	/**
	* loadClassChange
	* Load the Javascript Code for the Classchange JS Function
	*/
	public static function loadClassChange() {
		self::$loadClassChange = 1;
		echo "
			<script type=\"text/javascript\" language=\"JavaScript\">
			    <!--
				function mout(x,c) {
					document.getElementById(x).className = c;
				}
				
				function mover(x,c) {
					document.getElementById(x).className = c;
				}
			    -->
			</script>
		";
	}
	
	/**
	* loadInit
	* Load the Javascript Code for the init function, which executes all later init functions
	*/
	/*
	public static function loadInit() {
		echo "
			<script type=\"text/javascript\" language=\"JavaScript\">
				function init() {
				
				}			
			</script>
		";
	}
	*/
	
	public static function loadCountdown() {
		self::$loadCountdown = 1;
		echo"
			<script type=\"text/javascript\">
			    <!--
			    
			    function count(timeleft,id) {
			    	hours = parseInt(timeleft / 3600) % 60;
			    	minutes = parseInt(timeleft /60) % 60;
			    	seconds = timeleft % 60;
			    	
			    	hours < 0 ? hours = 0 : 1;
			    	minutes < 0 ? minutes = 0:1;
			    	seconds < 0 ? seconds = 0:1;
			    	
			    	if (String(hours).length < 2) hours = '0'+hours;
			    	if (String(minutes).length < 2) minutes = '0'+minutes;
			    	if (String(seconds).length < 2) seconds = '0'+seconds;
			    	
			    	var hourstring = 'a'+id+'h';
			    	var minutesstring = 'a'+id+'m';
			    	var secondsstring = 'a'+id+'s';
			    
			    	document.getElementById(hourstring).innerHTML = hours+':';
			    	document.getElementById(minutesstring).innerHTML = minutes+':';
			    	document.getElementById(secondsstring).innerHTML =seconds;
			    }
		    	//-->
			</script> 
		";
	}
	
	public static function loadOver() {
		js::$loadOver = 1;
	 /*echo("	
		<div style=\"z-index:10; position:absolute;display:none;border: 0px solid black\" id=\"over\">
			<table id=\"over_table\" class=\"bodys\" cellspacing=\"0\" cellpadding=\"0\">
				<tr>
					<td id=\"over_inner\">
					</td>
				</tr>
			</table>
		</div>
		<script type=\"text/javascript\">
		    <!--
		
		    function showover(event,xAdj,yAdj) {
				//if (!event) event = window.event;
				//alert(event.clientX);
				if (event.pageX) myX = event.pageX;
				if (event.clientX) myX = event.clientX;
				
				if (event.pageY) myY = event.pageY;
				if (event.clientY) myY = event.clientY;
				//alert(myX+'blub'+myY);
				//alert(document.body.scrollTop);				
				if (!xAdj) xAdj = 30;
				if (!yAdj) yAdj = 30;
					
				myX += parseInt(xAdj) - parseInt(document.body.scrollLeft);
				myY -= parseInt(yAdj) - parseInt(document.body.scrollTop);
				
		    	elem = document.getElementById('over');
		    	elem.style.top = myY;
		    	elem.style.left = myX;
		    	elem.style.display = 'inline';
		    }
		    
		    function hideover() {
		    	elem = document.getElementById('over');
		    	elem.style.display = 'none';
		    }
		    
		    function contentover(content) {
				document.getElementById('over_inner').innerHTML= content;
		    	
		    }
		    
		    //hideover();
		    //-->

		    
		</script> 
		");*/
	}
	
	
	/**
	* ClassChange
	* To be Applied on an html Element
	* @param String mouseOverClass
	* @param String mouseOutClass
	*/
	public static function classChange($mover,$mout) {
		if (!js::$loadClassChange) throw new Exception("ClassChange NICHT geladen");
		static $id;
		if (!isset($id)) {$id=1;}
		else {$id++;}
		return " id=\"$id\"  onmouseover=\"mover($id,'$mover')\" onmouseout=\"mout($id,'$mout')\" ";
	}
	
	/**
	* Link
	* To be Applied on an html Element
	* @param String Url
	*/
	
	public static function link($url) {
		echo " onClick=\"parent.location.href='$url'\"";
	}
	
	/**
	* OmoHelp
	* To be Applied on an html Element 
	* Shows a text Onmouseover
	*/
	public static function showover($text,$xadj="",$yadj="") {
		if (!js::$loadOver) throw new Exception("loadOver NICHT geladen");
		
		return " onmouseover=\"showover(event,'$xadj','$yadj');contentover('".js::input($text)."')\"  onmouseout=\"hideover()\" ";
	}
	
	
	/**
	* input
	* Javascriptinput of a String
	*	@param String
	*	@return String
	*/
	public static function input($string) {
		$string = preg_replace("/(\n|\r)/","",$string);
		$string = preg_replace("/(\")/","",$string);
		return $string;
	}
	
	/**
	* description
	* requires Loadover
	* @param Description // Description_id
	* @return echo
	*/
	public static function description($desc,$nolink=0) {
		
		if (!is_a($desc,"description")) {
			$desc_id = $desc;
			$desc = neu("description",$desc_id);
		}
		
		global $ripf;
		if(!is_a($desc,"description")) throw new exception ("Description: Wrong object passed");
		
		$txt = "<u><b>".$desc->getHeadline()."</b></u><br><br>";
		$txt .= $desc->getMouseOver();
		
		if (!$nolink) {
			$a1 = "<a href=\"c_diary.php?d=".$desc->getDescriptionId()."\">";
			$a2 = "</a>";
		}
		
		echo "$a1<img style=\"vertical-align:bottom\" border=\"0\" ";js::showover($txt);echo " src=\"$ripf/help.gif\">$a2";
	}
	
	/**
	* countdown
	* @param int Zeit in Sekunden
	* @param Array Format kombination aus "hms" - gib an, was angezeigt werden soll - Default = "hms"
	* @param int 1 = Output directly 0 = return String default return String
	* @return echo
	*/
	public static function countdown($int,$format = array("h","m","s"),$output=0) {
		if (self::$loadCountdown != 1) throw new Exception("Loadcountdown not executed");

		static $counter;
		if (!isset($counter)) $counter = 0;
		$counter ++;
				
		$hours = floor($int / 3600);
		$minutes = floor($int / 60) % 60;
		$seconds = $int % 60;

		$duration_name = "a".$counter."d";
		$hour_id = "a".$counter."h";
		$minutes_id = "a".$counter."m";
		$seconds_id = "a".$counter."s";
		
		// Stunden
		if (in_array("h",$format)) {
				$back.= "<span id=\"$hour_id\">:</span>";
		}
		else {
				$back.= "<span style=\"visibility:hidden;\" id=\"$hour_id\">:</span>";
		}
		
		// Minuten
		if (in_array("m",$format)) {
			$back.= "<span  id=\"$minutes_id\">$minutes:</span>";
		}
		else {
			$back.= "<span style=\"visibility:hidden;\" id=\"$minutes_id\">$minutes:</span>";
		}
		
		// Sekunden
		if (in_array("s",$format)) {
			$back.= "<span id=\"$seconds_id\">$seconds</span>";
		}
		else {
			$back.= "<span style=\"visibility:hidden;\" id=\"$seconds_id\">$seconds</span>";
		}
		
		$back.= "
			    
			<script type=\"text/javascript\">
			    <!--
			    
			    var $duration_name=$int;
			    
			    function ticker_$counter() {
			    	count($duration_name,$counter);
			    	$duration_name--;
			    	window.setTimeout('ticker_$counter()',1000);
			    }
			    
		    	ticker_$counter();
		    	//-->
			</script> 
		";
		
		if (!$output) return $back;
		
		echo $back;
		
	}
	
	public static function simplehelp($text,$xadjust=30,$yadjust=30) {
		
		$text = "<table style=\'border:1px solid black;\' class=tableInner1><tr ><td>".$text."</td></tr></table>";
		$back = "<img src=\"images/_help.gif\" border=0 valign=\"absmiddle\"  ".js::showover($text,$xadjust,$yadjust).">";
		return $back;
		
	}
	
}




?>
