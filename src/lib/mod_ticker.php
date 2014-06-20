<?
##################################################
#	MOD TICKER - JANNIS BREITWIESER
#	3.12.2004
# 	REQUIRES MOD_ XTENDSQL + CONNECTION HANDLE
##################################################


##################################
#		CONFIG
##################################

$mod_config=array();
$mod_config[font] = "'verdana,arial,sans-serif'";
$mod_config[font_size]="'10px'";
$mod_config[fontSizeNS4] = "'11px'";
$mod_config[fontWeight] = "'normal'";
$mod_config[fontColor] = "'cccccc'";
$mod_config[textDecoration] = "'none'";
$mod_config[fontColorHover] = "'#ff0000'";
$mod_config[bgColor] = "'#555555'"; //set [='transparent'] for transparent
$mod_config[top] = "0";
$mod_config[left] = "0";
$mod_config[width] = "500";
$mod_config[height] = "32";
$mod_config[position] = "'relative'"; // absolute/relative
$mod_config[timeOut] = "7"; // seconds
$mod_config[pauseOnMouseOver] = "'true'";
$mod_config[leadingSign] = "'_'";


##################################
#		INTERFACE
##################################
/*
Public functions:

// function TICKER_get_ticker()

		- Optional Parameters:
				1. $number - Number of entries to show - standard is 10 , if not changed by config
		- Creates the Javascript ticker code + content
		
		
// function TICKER_insert_content($text)

- Optional Parameters: 
				1. $user_id
		- Adds a text to the ticker 4 display

		
// function TICKER_config_ticker($number,$tickerspeed)

		- Sets config for the ticker
		- Number > 0
		- 1 < Tickerspeed < 20


*/

##################################
# 		PUBLIC FUNCTIONS
##################################

function TICKER_get_ticker() {
	$number = 10;
	if (func_num_args() > 0) {$number = (int) func_get_arg(0);}
	$config = @assoc("select * from ticker_config");
	if (count($config) == 0) {
		create_ticker_database();
		$config = @assoc("select * from ticker_config");
	}
	$action = "select content from ticker_content order by time desc limit ".$config[number];
	$content = singles("$action");
	$back = create_script($content,$config);
	return $back;
}



function TICKER_insert_content($text) {
	$text = preg_replace("/\r|\n/s", " ", $text);
	$text = preg_replace("/</s", "&lt;", $text);
	$text = preg_replace("/>/s", "&gt;", $text);
	$user_id=0;
	if (func_num_args() > 1) {$user_id = (int) func_get_arg(1);}
	if (func_num_args() > 2) {$kostenlos = (int) func_get_arg(2);}
	$time = time();
	select("insert into ticker_content (time,user_id,content) values ($time,$user_id,'".$text."')");
}


function TICKER_config_ticker($number,$tickerspeed) {
	$number = (int) $number;
	if (!$number) {$number = 10;}
	$tickerspeed = (int)$tickerspeed;
	if ($tickerspeed < 1 || $tickerspeed > 20) {$tickerspeed=10;}
	select ("update ticker_config set number=$number,speed=$tickerspeed");
}

##################################
#		PRIVATE FUNCTIONS
##################################



// Erstellt das Javascript
function create_script(&$content,&$config) {
global $mod_config;
	
	
$script = "
<script language=\"JavaScript\">
<!--
n_font='verdana,arial,sans-serif';
n_fontSize=$mod_config[font_size];
n_fontSizeNS4=$mod_config[fontSizeNS4];
n_fontWeight=$mod_config[fontWeight];
n_fontColor=$mod_config[fontColor];
n_textDecoration=$mod_config[textDecoration];
n_fontColorHover=$mod_config[fontColorHover];
n_textDecorationHover='underline';//	| in Netscape4
n_bgColor=$mod_config[bgColor];//set [='transparent'] for transparent
n_top=$mod_config[top];//	|
n_left=$mod_config[left];//	| defining
n_width=$mod_config[width];//	| the box
;//	| the box
n_height=$mod_config[height];//	|
n_position=$mod_config[position];// absolute/relative
n_timeOut=$mod_config[timeOut];//seconds
n_pauseOnMouseOver=true;
n_speed=$config[speed];//1000 = 1 second
n_leadingSign=$mod_config[leadingSign];
n_alternativeHTML='';
n_content=[";
if (count($content) > 0) {
	foreach ($content as $temp) {
		$script.="['','".addslashes(umwandeln_bbcode($temp))."',''],";
	}
}
else {
	$script.="['','',''],";
}
$script = chopp($script);
$script.="
];

n_nS4=document.layers?1:0;n_iE=document.all&&!window.innerWidth&&navigator.userAgent.indexOf(\"MSIE\")!=-1?1:0;n_nSkN=document.getElementById&&(navigator.userAgent.indexOf(\"Opera\")==-1||document.body.innerHTML)&&!n_iE?1:0;n_t=0;n_cur=0;n_l=n_content[0][1].length;n_timeOut*=1000;n_fontSize2=n_nS4&&navigator.platform.toLowerCase().indexOf(\"win\")!=-1?n_fontSizeNS4:n_fontSize;document.write('<style>.nnewsbar,a.nnewsbar,a.nnewsbar:visited,a.nnewsbar:active{font-family:'+n_font+';font-size:'+n_fontSize2+';color:'+n_fontColor+';text-decoration:'+n_textDecoration+';font-weight:'+n_fontWeight+'}a.nnewsbar:hover{color:'+n_fontColorHover+';text-decoration:'+n_textDecorationHover+'}</style>');n_p=n_pauseOnMouseOver?\" onmouseover=clearTimeout(n_TIM) onmouseout=n_TIM=setTimeout('n_new()',\"+n_timeOut+\")>\":\">\";n_k=n_nS4?\"\":\" style=text-decoration:none;color:\"+n_fontColor;function n_new(){if(!(n_iE||n_nSkN||n_nS4))return;var O,mes;O=n_iE?document.all['nnewsb']:n_nS4?document.layers['n_container'].document.layers['nnewsb']:document.getElementById('nnewsb');mes=n_content[n_t][0]!=\"\"&&n_cur==n_l?(\"<a href='\"+n_content[n_t][0]+\"' target='\"+n_content[n_t][2]+\"' class=nnewsbar\"+n_p+n_content[n_t][1].substring(0,n_cur)+n_leadingSign+\"</a>\"):(\"<span class=nnewsbar\"+n_k+\">\"+n_content[n_t][1].substring(0,n_cur)+n_leadingSign+\"</span>\");if(n_nS4)with(O.document){open();write(mes);close()}else O.innerHTML=mes;if(n_cur++==n_l){n_cur=0;n_TIM=setTimeout(\"n_new()\",n_timeOut);n_t++;if(n_t==n_content.length)n_t=0;n_l=n_content[n_t][1].length}else{setTimeout(\"n_new()\",n_speed)}};document.write('<div '+(n_nS4?\"name\":\"id\")+'=n_container style=\"position:'+n_position+';top:'+n_top+'px;left:'+n_left+'px;width:'+n_width+'px;height:'+n_height+'px;clip:rect(0,'+n_width+','+n_height+',0); border:1px solid #000000;\"><div '+(n_nS4?\"name\":\"id\")+'=nnewsb style=\"position:absolute;top:0px;left:0px;width:'+n_width+';height:'+n_height+'px;clip:rect(0,'+n_width+','+n_height+',0);background-color:'+n_bgColor+';layer-background-color:'+n_bgColor+';text-decoration:none;color:'+n_fontColor+';\" class=nnewsbar>'+n_alternativeHTML+'</div></div>');if(!n_nS4)setTimeout(\"n_new()\",1000);else window.onload=n_new;if(n_nS4)onresize=function(){location.reload()}
-->
</script>

";
		return $script;
}


// Erstellt datenbank, falls noch nicht vorhanden
function create_ticker_database() {


	// Deaktiviert da die Tabellen in den Test-Datenbanken herangezogen werden
	/*
	// Content Table erstellen
	select("
		 CREATE TABLE `ticker_config` (
		`id` TINYINT DEFAULT '1' NOT NULL,
		`number` TINYINT DEFAULT '10' NOT NULL,
		`speed` TINYINT DEFAULT '50' NOT NULL,
		INDEX (`id`)
		)
	");
	// Config Table erstellen
	select("
		 CREATE TABLE `ticker_content` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`time` INT NOT NULL,
		`user_id` INT NOT NULL,
		`content` VARCHAR(255) NOT NULL,
		INDEX (`time`)
		)
	");
	select("INSERT INTO `ticker_config` (`id`, `number`, `speed`) VALUES ('1', '10', '10')");
	*/
}


?>
