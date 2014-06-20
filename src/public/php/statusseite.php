<?

//**************************************************************************//
//							Übergabe Variablen checken
//**************************************************************************//

$init = (int) $init;
if ($gaction && $gaction != "create" && $gaction != "leave" && $gaction != "joingroup" && $gaction != "changepass" && $gaction != "kick" && $gaction != "nachfolger"): $gaction = ""; endif;
if ($ia && $ia != "finish" && $ia != "confirm"): $ia = ""; endif;
if ($groupid): $groupid = floor($groupid); endif;
if ($place && ($place < 2 or $place > MAX_USERS_A_GROUP)): $place = 0; endif;

//**************************************************************************//
//							Dateispezifische Finals deklarieren
//**************************************************************************//



//**************************************************************************//
//							Variablen initialisieren
//**************************************************************************//

$queries = array();

$player_name = "";		//Spielername
$player_konzern_name = "";		//Firmaname
$player_syndicate_name = "";	//Syndikatsname

$general_announcement="";		//generelle Ankündigung
$president_announcement = "";	//Ankündigung des Päsidenten
$president_announcement_changetime = "";	//Ankündigung des Päsidenten
$print_president_announcement="";

$land_prod = 0;			//Land in Produktion

$spies = 0;			//Anzahl der Spione
$spies_prod = 0;			//Anzahl der Spione in Produktion
$spies_market = 0;

$units = 0;			//Anzahl der Militäreinheiten
$units_prod = 0;			//Anzahl der Militäreinheiten in Produktion
$units_away = 0;			//Anzahl der Militäreinheiten auf Heimkehr
$units_market = 0;

$buildings=0;			//Anzahl der Gebäude
$buildings_prod = 0;		//Anzahl der Gebäude in Produktion

$res_prod_money = 0;		//Produktion - Geld
$res_prod_ore = 0;		//Produktion - Erz
$res_prod_fp = 0;			//Produktion - Forschungspunkte

$res_prod_energy = 0;		//Produktion - Energie
$res_loss_energy = 0;		//Energieverbrauch
$res_prod_energy_final = 0;		//Energieproduktion gesammt

$moneyadd = 0;
$metaladd = 0;
$energyadd = 0;
$sciencepointsadd = 0;

$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
$weiter = "<br><br><a href=statusseite.php class=linkAufsiteBg>weiter</a>";


//**************************************************************************
//							Game.php includen						
//**************************************************************************


require_once("../../inc/ingame/game.php");
require_once(LIB."/js.php");
require_once("../../inc/ingame/header.php");
$tpl->assign("ripf",$ripf);

/* R4bbiT - 12.10.10 - auf Wunsch deaktiviert
//START Spruch des Tage by Christian
$spruch=assocs("SELECT * FROM spruch_des_tages");
//hier einfach Sprüche hinzu schreiben
srand((double)microtime()*1000000);
if(rand(1,10)==5){
	$zufallsIndex = rand(0,count($spruch)-1);
	$infomsg = "<span class=i aling=center><b>Wort zum.. ehm.. Morgen:</b><br><br>".umwandeln_bbcode($spruch[$zufallsIndex][txt])."<br>";
	$tpl->assign('INFO', $infomsg);
	$tpl->display('info.tpl');
}
//END Spruch des Tages
*/

$ism = single("select is_mentor from users where konzernid = $id");
if ($ism) {
    select("update status set is_mentor = ".$ism." where id = $id");
}


//**************************************************************************
//**************************************************************************
//							Eigentliche Berechnungen!
//**************************************************************************
//**************************************************************************

// Daten für istp holen
$ressources = getresstats();
$resstats = $ressources;
	foreach ($resstats as $k => $value) {
		if ($value[type] != "money") {
			$resstats[$k][value] *= RESSTATS_MODIFIER;
		}
	}

$syndikat = assoc("select * from syndikate where synd_id = $status[rid]");


// Boni am Rundenanfagen. Bzw. nach Erstellen des Konzerns
// R4bbiT - 16.10.10
if($status['gamble_own'] && !$globals['updating']){
	
	$maxnum = 9; // ACHTUNG: Dieser Wert liegt auch als Standardwert in der Status-tabelle!!
	
	$unitstats = getunitstats($status['race']);
	$gamble = array();
	$gamble['money'] =  array(
							  'label' => 'Credits',
							  'name' => 'money',
							  'value' => 600000,
							  'type' => 'ress');
	$gamble['energy'] =  array(
							  'label' => 'Energie',
							  'name' => 'energy',
							  'value' => 500000,
							  'type' => 'ress');
	$gamble['metal'] =  array(
							  'label' => 'Erz',
							  'name' => 'metal',
							  'value' => 100000,
							  'type' => 'ress');
	$gamble['sciencepoints'] =  array(
							  'label' => 'Forschungspunkte',
							  'name' => 'sciencepoints',
							  'value' => 37500,
							  'type' => 'ress');
	$gamble['offspecs'] =  array(
							  'label' => $unitstats['offspecs']['name'], // Marines
							  'name' => 'offspecs',
							  'value' => 600,
							  'type' => 'mil');
	$gamble['defspecs'] =  array(
							  'label' => $unitstats['defspecs']['name'], // Ranger
							  'name' => 'defspecs',
							  'value' => 500,
							  'type' => 'mil');
	// ACHTUNG: Gamble MUSS das letzte Element sein
	$gamble['gamble'] =  array(
							  'label' => 'zocken',
							  'name' => 'gamble',
							  'value' => 50,
							  'type' => 'gamble');
	if($_POST['action'] == 'gamble'){
		if($gamble[$_POST['name']]['type'] === 'ress' || $gamble[$_POST['name']]['type'] === 'mil'){
			select('UPDATE status SET '.$_POST['name'].' = '.$_POST['name'].' + '.$gamble[$_POST['name']]['value'].' WHERE id = '.$id);
			$status['gamble_own'] -= 1;
			$status[$_POST['name']] += $gamble[$_POST['name']]['value'];
			$status{nw} = nw($status{id});
			select('UPDATE status SET gamble_own = gamble_own - 1, nw = '.$status{nw}.' WHERE id = '.$id);
			$tpl->assign("MSG", "Sie haben <strong> ".pointit($gamble[$_POST['name']]['value'])." ".$gamble[$_POST['name']]['label']."</strong> erhalten");
			$tpl->display("sys_msg.tpl");
		}
		else if($gamble[$_POST['name']]['type'] === 'gamble'){
			$num = count($gamble)-1;
			$i = 1;
			$x = rand(1, $num);
			foreach($gamble as $value){
				if($i == $x){
					$val = $value['value'] * (1 + $gamble['gamble']['value'] / 100);
					select('UPDATE status SET '.$value['name'].' = '.$value['name'].' + '.$val.' WHERE id = '.$id);
					$status['gamble_own'] -= 1;
					$status[$value['name']] += $val;
					$status{nw} = nw($status{id});
					select('UPDATE status SET gamble_own = gamble_own - 1, nw = '.$status{nw}.' WHERE id = '.$id);
					$tpl->assign("MSG", "Sie haben zufällig <strong> ".pointit($val)." ".$value['label']."</strong> erhalten");
					$tpl->display("sys_msg.tpl");
					break;
				}
				$i++;
			}
		}
	}
	if($_POST['action'] == 'gamble_pre'){
		$tpl->assign('GAMBLE_ASK', true);
		$tpl->assign('GAMBLE', $gamble[$_POST['name']]);
	}
	
	$tpl->assign('GAMBLE_TAKEN', $maxnum - $status['gamble_rest']-$status['gamble_own']);
	$tpl->assign('GAMBLE_MAX', $maxnum);
	$tpl->assign('GAMBLE_TIME', GAMBLE_TIME);
	$tpl->assign('GAMBLEVALUES', $gamble);
	$tpl->assign('GAMBLENUM', $status['gamble_own']);
}


// täglicher Boni von Tag 3-9 (bzw. je nach Einstellung in update.php)
// R4bbiT - 29.12.10
if($status['daily_boni'] && !$globals['updating']){
	
	$unitstats = getunitstats($status['race']);
	$spystats = getspystats($status['race']);
	$daily = array();
	$daily['money'] =  array(
							  'label' => 'Credits',
							  'name' => 'money',
							  'value' => 1000000,
							  'type' => 'ress');
	$daily['energy'] =  array(
							  'label' => 'Energie',
							  'name' => 'energy',
							  'value' => 850000,
							  'type' => 'ress');
	$daily['metal'] =  array(
							  'label' => 'Erz',
							  'name' => 'metal',
							  'value' => 165000,
							  'type' => 'ress');
	$daily['sciencepoints'] =  array(
							  'label' => 'Forschungspunkte',
							  'name' => 'sciencepoints',
							  'value' => 62500,
							  'type' => 'ress');
	$daily['offspecs'] =  array(
							  'label' => $unitstats['offspecs']['name'], // Marines
							  'name' => 'offspecs',
							  'value' => 1000,
							  'type' => 'mil');
	$daily['defspecs'] =  array(
							  'label' => $unitstats['defspecs']['name'], // Ranger
							  'name' => 'defspecs',
							  'value' => 1000,
							  'type' => 'mil');
	$daily['offspies'] =  array(
							  'label' => $spystats['offspies']['name'], // Thief / V-Mann
							  'name' => 'offspies',
							  'value' => 1000,
							  'type' => 'spy');
	$daily['defspies'] =  array(
							  'label' => $spystats['defspies']['name'], // Guardian / Hacker
							  'name' => 'defspies',
							  'value' => 1000,
							  'type' => 'spy');
	//if($status['race'] != 'sl'){
		$daily['intelspies'] =  array(
							  'label' => $spystats['intelspies']['name'], // Agenten
							  'name' => 'intelspies',
							  'value' => 1000,
							  'type' => 'spy');
	//}
	// ACHTUNG: Gamble MUSS das letzte Element sein
	$daily['gamble'] =  array(
							  'label' => 'zocken',
							  'name' => 'gamble',
							  'value' => 50,
							  'type' => 'gamble');
	if($_POST['action'] == 'daily'){
		if($daily[$_POST['name']]['type'] === 'ress' || $daily[$_POST['name']]['type'] === 'mil' || $daily[$_POST['name']]['type'] === 'spy'){
			select('UPDATE status SET '.$_POST['name'].' = '.$_POST['name'].' + '.$daily[$_POST['name']]['value'].' WHERE id = '.$id);
			$status['daily_boni']--;
			$status[$_POST['name']] += $daily[$_POST['name']]['value'];
			$status{nw} = nw($status{id});
			select('UPDATE status SET daily_boni = daily_boni-1, nw = '.$status{nw}.' WHERE id = '.$status['id']);
			$tpl->assign("MSG", "Sie haben <strong> ".pointit($daily[$_POST['name']]['value'])." ".$daily[$_POST['name']]['label']."</strong> erhalten");
			$tpl->display("sys_msg.tpl");
		}
		else if($daily[$_POST['name']]['type'] === 'gamble'){
			$num = count($daily)-1;
			$i = 1;
			$x = rand(1, $num);
			foreach($daily as $value){
				if($i == $x){
					$val = $value['value'] * (1 + $daily['gamble']['value'] / 100);
					select('UPDATE status SET '.$value['name'].' = '.$value['name'].' + '.$val.' WHERE id = '.$id);
					$status['daily_boni']--;
					$status[$value['name']] += $val;
					$status{nw} = nw($status{id});
					select('UPDATE status SET daily_boni = daily_boni-1, nw = '.$status{nw}.' WHERE id = '.$status['id']);
					$tpl->assign("MSG", "Sie haben zufällig <strong> ".pointit($val)." ".$value['label']."</strong> erhalten");
					$tpl->display("sys_msg.tpl");
					break;
				}
				$i++;
			}
		}
	}
	if($_POST['action'] == 'daily_pre' && $daily[$_POST['name']]){
		$tpl->assign('DAILY_ASK', true);
		$tpl->assign('DAILY', $daily[$_POST['name']]);
	}
	
	$tpl->assign('DAILYVALUES', $daily);
	$tpl->assign('DAILYNUM', $status['daily_boni']);
}

/*
pvar($globals[roundstarttime]);
pvar(time());
pvar((time() -$globals[roundstarttime]));
pvar((time() -$globals[roundstarttime])/60);
pvar((time() -$globals[roundstarttime])/(60*60));
pvar((time() -$globals[roundstarttime])/(60*60*24));
$weeks_played = ceil((round_days_played()+1)/ 7);
pvar($weeks_played);

pvar(round_days_played());
*/

// der Weihnachtsbonus ist inzwischen unter inc/events eingebaut

//
// Überprüfen ob in nächster Zeit ein Assistent abläuft
//

$assi_lauft_ab = single("SELECT COUNT(*) FROM features 
	WHERE konzernid = ".$status[id]." AND time_bis < ".($time + 259200)." AND ".$time." < time_bis");
if ($assi_lauft_ab) {
	$tpl->assign("INFO", "Einer oder mehrere deiner Assistenten laufen demnächst aus. Um sie zu verlängern klicke
		<a href=\"premiumfeatures.php\" target=\"_blank\">hier</a>");
	$tpl->display('info.tpl');
}

//
// ISTP einstellen
//
if ($inneraction == "setistp" && array_sum(explode("|", $syndikat[creditforschung])) > 0) {
	$istp_res	 = htmlentities($istp_res,ENT_QUOTES);
	$ISTP_CHANGETIME = (get_hour_time($time) + BUILDTIME * 60 * $globals{roundtime} * (1 - buildtimemodifier()) - get_hour_time($time)) / 3600;
	
	if ($status[istp_production] == "none") {
		select("update status set istp_production = '$istp_res' where id = $status[id]");
		$status[istp_production] = $istp_res;
		$beschr = "Ihr Konzern wird mit Hilfe des <i>Inner Syndicate Trade Progams</i>  &nbsp;<b>".$ressources[$istp_res][name]."</b> erwirtschaften.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
	elseif ($status[istp_production] != $istp_res) {
		select("update status set istp_production = '$istp_res',istp_changetime=".$ISTP_CHANGETIME." where id = $status[id]");
		$status[istp_production] = $istp_res;
		$status[istp_changetime] = $ISTP_CHANGETIME;
		$beschr = "Ihr Konzern wird in ".$ISTP_CHANGETIME." Stunden mit Hilfe des <i>Inner Syndicates Trade Progams</i>  &nbsp;<b>".$ressources[$istp_res][name]."</b> erwirtschaften.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");	
	} else {
		$errormsg = "Diese Ressource ist bereits ausgewählt.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}
elseif ($inneraction == "unprotect" && !$dounprotect){
	$errormsg = "Mit dem Verlassen der Schutzzeit ermöglichst du es anderen Spielern dich anzugreifen, auszuspionieren und zu bestehlen. Bist du sicher, dass du die Schutzzeit verlassen möchtest?<br /><br /><center>
		<form id=\"commit_form\" action=\"statusseite.php\" method=\"post\">
		<input type=\"hidden\" name=\"inneraction\" value=\"unprotect\" />
		<input type=\"hidden\" name=\"dounprotect\" value=1 />
        <a href=\"statusseite.php\" class=\"LinkAuftableInner\">NEIN - ich denke nochmal drüber nach.</a><br><br>
        <a href=\"#\" onClick=\"document.getElementById('commit_form').submit();\" class=\"LinkAuftableInner\">JA - ich bin jung und brauch' den Bonus.</a></form></center>
	";
	$tpl->assign('ERROR', $errormsg);
	$tpl->display('fehler.tpl');
}
elseif ($inneraction == "unprotect" && $dounprotect){
	if($time >= $status['createtime']+21600 && $time < $status['unprotecttime']){
		select("update status set unprotecttime = ".$time." where id = $id");
		$status['unprotecttime'] = $time;
		$beschr = "Sie haben die Schutzzeit frühzeitig verlassen und erhalten dadurch in dieser Runde einen Produktionsbonus von ".(getUnprotectBonus($status)*100)."% auf Ihre Standardressource.";
		$tpl->assign("MSG", $beschr);
		$tpl->display("sys_msg.tpl");
	}
	else{
		$errormsg = "Diese Aktion ist zur Zeit leider nicht möglich.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
	}
}
//Schutzzeit frühzeitig beenden
if($time >= $status['createtime']+21600 && $time < $status['unprotecttime']) {
	$tooltip="</td></tr><tr><td class=\"tableHead2\" width=\"300\"><b>Produktionsboni: ".(UNPROTECT_BONUS*100)."% pro Stunde</b></td></tr><tr><td class=\"tableInner1\">
	<b>Erz:</b> United Industries Corporation, New Economic Block<br /></td></tr><tr><td class=\"tableInner1\"><b>Energie:</b> Brute Force, Nova Federation<br /></td></tr><tr><td class=\"tableInner1\"><b>Forschungspunkte:</b> Shadow Labs<br /></td>";
	$tpl->assign('showUnprotectBox', true);
	$tpl->assign('unprotect_tooltip', getJsHelpTag($tooltip));
}


///// Gruppenoptionen, wenn Runde noch nicht gestartet ist oder zuende (zur Erinnerung!)
if ($globals['roundstatus'] == 0 || $globals['roundstatus'] == 2 || 
	(($globals['roundendtime']-3*86400) < $time && $time < $globals['roundendtime'])) { // && !isBasicServer($game))	{

	$tpl->assign("showGroups",true);
	$tpl->assign('IS_NEXT', !$globals['roundstatus'] == 0);
	
	$user_id = single("SELECT id FROM users WHERE konzernid = '".$status['id']."'");
	
	$tpl->assign('GROUP', assoc("SELECT * FROM groups_new WHERE group_id = (SELECT group_id FROM groups_new_members WHERE user_id = '".$user_id."')"));
	
}
///// Gruppenoptionen Ende

//							selects fahren

$syndvalues = getsyndvalues();
$player_syndicate_name = $syndvalues[name];
if ($globals[roundstatus] == 0): $player_syndicate_name = "Runde noch nicht gestartet"; endif;
$president_announcement = $syndvalues[announcement];
$president_announcement_changetime = $syndvalues[announcement_lastchangetime];
$notes = html_entity_decode(single("select text from notes where user_id ='".$status[id]."'"));

$spies = (int) spiestotal($id,1);				//cast wg ( 0 = "")
$spies_prod = (int) spiestotal($id,2);
$spies_market = (int) spiestotal($id,4);

$units = (int) miltotal($id,1);
$units_prod = (int) miltotal($id,2);
$units_away = (int) miltotal($id,3);
$units_market = (int) miltotal($id,4);

$underconstruction = single("select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name != 'land'");
$underconstruction = (int) $underconstruction;

$allbuildings = getallbuildings($status{id});
$allbuildings = (int) $allbuildings;

$freeland = freeland($status);


list($moneyadd, $moneylageradd, $hpmoneyadd) = moneyadd($status{id});
list($metaladd, $metallageradd, $hpmetaladd) = metaladd($status{id});
list($sciencepointsadd, $sciencepointslageradd, $hpsciencepointsadd) = sciencepointsadd($status{id});

list($energyadd, $energylageradd, $hpenergyadd) = (energyadd($status{id}));


$maxsave = (energyadd($status{id},3));
if($energyadd==0)
	$energyhours = -1;
else
	$energyhours = -$status{energy}/$energyadd; //solang reicht die nrg noch
	
if($energyhours >= 2)
	$nrgtick=" Ticks";
else
	$nrgtick=" Tick";

if($energyadd >= 0) {
  $criticalenergy2 = "";
} else {
  $criticalenergy2 = pointit($energyhours)." ".$nrgtick;
}
($status{energy} + $energyadd >= $maxsave) ? $maxsave_reached = "&nbsp;<b class=highlightAuftableInner>*</b>" : 1;
($status{energy} + $energyadd >= $maxsave) ? $warner = "<tr><td colspan=3 align=right><b class=highlightAuftableInner>*</b> <strong class=\"achtungAuftableInner\">Lagerkapazitäten erschöpft</strong><br>&nbsp;</td></tr>" : 1;
($status{energy} + $energyadd < 0) ? $critical = $criticalenergy1 = "<font class=highlightAuftableInner> * &#189;</font>" : 1;

$energyadd = $energyadd;
$energyprod = energyadd($status{id},1);
$energyuse = energyadd($status{id},2);

// Selects für nächste aktion
$tables = array(
	"build_buildings" => "buildings",
	"build_military" => "military_unit_settings",
	"build_spies" => "spy_settings",
	"build_sciences" => "sciences",
);

$na_info = array(); // nextaction info
$mintim = 0;
foreach ($tables as $k => $v) {
	$numsum = ",sum(number) as number";
	if ($k == "build_sciences") $numsum = "";
	$idtype = ",unit_id";
	if ($k == "build_buildings") $idtype = ",building_id";
	if ($k == "build_sciences") $idtype = "";
	$temp = assoc("select *$numsum from  $k where user_id = $status[id] group by time$idtype order by time asc limit 1");
	if ($temp[time] && $temp[time] < $mintime || !$mintime) {
		$mintime = $temp[time];
		$na_info[time] = $temp[time];
		$na_info[values] = $temp;
		$na_info[table] = $v;
		$na_info[typetable] = $k;
	}
}

switch ($na_info[table]) {

	case "sciences": 
		$na_info[name] = single("select gamename from sciences where concat(name,typenumber) = '".$na_info[values][name]."'");break;
		
	case "military_unit_settings":
		$na_info[name] = single("select name from military_unit_settings where type = '".(single("select type from military_unit_settings where unit_id = '".$na_info[values][unit_id]."'"))."' and (race='".$status['race']."' or race='all')"); break;
		
	case "spy_settings":
		$na_info[name] = single("select name from spy_settings where unit_id = '".$na_info[values][unit_id]."'");break;
	
	case "buildings":
		if ($na_info[values][building_id] == "127") {
			$na_info[name] = "Land";
			break;
		}
		$na_info[name] = single("select name from buildings where building_id = '".$na_info[values][building_id]."'");break;

}
$na_info[left] = $na_info[time] - $time;
if (!$na_info[name]) $na_info[name] = "Keine Produktion";
if ($time < $globals[roundstarttime]) {
	$na_info = array(); // Neu initialisieren, damit vor Rundenstart Nummer und Typ nicht angezeigt werden
	$na_info[name] = "<span style=\"font-size:13px\">Runde noch nicht gestartet</span>";
}

//							Berechnungen
if ($init) {
	
    $global_announcement = assoc("select * from announcements where (type='ingame' or type='both') and time >= ".($time-7*86400)." order by time desc limit 1");
	if (strlen($global_announcement[content]) > 5) {
		$tpl->assign("showGlobalNews", true);
		$tpl->assign("global_poster", $global_announcement[poster]);
		$tpl->assign("global_headine", $global_announcement[headline]);
		$tpl->assign("global_content", $global_announcement[content]);
		$tpl->assign("global_time", mytime($global_announcement[time]));
	}
}

if ($globals[roundstatus] == 1) {
	$tpl->assign("showVotesAdds",true);
	$hourtime = get_hour_time($time);
	$hour = date("H",$time);
	$daytime = $hourtime - $hour*60*60;
	$clickcount1 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $hourtime group by type", "type");
	$clickcount24 = assocs("select type, count(*) as count from bonusklicks where user_id=$status[id] and time > $daytime group by type", "type");
	$bonuscount = $status[land]*30;
	$bonuscount -= $clickcount24[1][count]*$status[land]*4;
	if ($bonuscount < 0) {$bonuscount = 0;}

/*
					<A HREF=\"bonus.php?site=buecher&type=1\" target=\"_blank\" class=linkAuftableInner>Bücher</A> |
					<A HREF=\"bonus.php?site=musik&type=1\" target=\"_blank\" class=linkAuftableInner>Musik</A> |
					<A HREF=\"bonus.php?site=dvd&type=1\" target=\"_blank\" class=linkAuftableInner>DVDs</A> |

*/
	

	$votecounter = single("select count(*) from bonusklicks where time >= ".get_day_time($time)." and page = 'mmofacts'");
	$galaxy_news_done = 0;
	$gamesdynamite_done = 0;
	$hourtime = get_hour_time($time);
	$hour = date("H",$time);
	$daytime = $hourtime - $hour*60*60;
	$voted_bonusklick = assocs("select page as link from bonusklicks where user_id = $id and time > ".(time() - 24*3600)); // Wird jetzt auch in game.php geholt weil in menu
	foreach ($voted_bonusklick as $vl) {
			if ($vl[link] == "gamesdynamite" ): $gamesdynamite_done = 1;
			elseif ($vl[link] == "mmofacts"): $galaxy_news_done = 1;
			endif;
	}
	
	$tpl->assign("galaxy_news_done", $galaxy_news_done);
	$tpl->assign("galaxy_credits", ($status['land']*100));
	$tpl->assign("galaxy_votecounter", $votecounter);
	
	if (!($clickcount1[1][count] > 0)) {
		$tpl->assign("crboniclicks","click");
		$tpl->assign("crboni",pointit($bonuscount));
		$user = assoc("select * from users where konzernid = $status[id]");
		if ($time - $status[createtime] > 60*60*24 && $time - $user[createtime] > 60*60*24*3) { 
			$tpl->assign("isnewuser", false);
		}
		else {
			$tpl->assign("isnewuser", true);
		}
	}
	else {
		if ($bonuscount > 0) {
			$tpl->assign("crboniclicks","done");
			$bonuscount += $status[land]*4;
			$tpl->assign("crboni",pointit($bonuscount));
		}
	}

	$bonuscount = ceil($status[land]*0.001);
	if ($bonuscount > 5) $bonuscount = 5;
	
	$linkdata = assoc("select * from bonus_links where type = 2 order by klicks asc limit 1");
	if (!($clickcount1[2][count] > 0) && $clickcount24[2][count] < 5) {
		$tpl->assign("haboniclicks","click");
		$tpl->assign("halinkdataid",$linkdata[id]);
		$tpl->assign("halinkdatatext",$linkdata[linktext]);
		$tpl->assign("haboni",pointit($bonuscount));
	}
	else {
		if ($clickcount24[2][count] < 5) {
			$tpl->assign("haboniclicks","done");
			$tpl->assign("haboni",pointit($bonuscount));
		}
	}

	
	if (array_sum(explode("|", $syndikat[creditforschung])) > 0) {
		$tpl->assign("showTradeInfo",true);				
		$tpl_ress = array();
		foreach ($ressources as $key => $temp) 
		{
			$tpl_res=array();
			if(	
				( $key == $status[istp_production] )						// wenn die Trade-Ressource mit der select-ressource übereinstimmt
				||
				(
					$key == "money" && $status['istp_production'] == "none"  // oder wenn die select-ressource creds ist und Trade auf none, also noch nicht gewählt, steht
				)
			)
			{
				$selected = "selected";		// wähle die entsprechende Ressource aus
			}
			else
			{
				$selected = "";				// ansonsten nicht
			}
			$tpl_res[0]=$key;
			$tpl_res[1]=$selected;
			$tpl_res[2]=$temp[name];
			array_push($tpl_ress,$tpl_res);
		}
		$tpl->assign("ressi",$tpl_ress);
		
		if ($status[istp_changetime] > 0) {
			$tpl->assign("istp_changetime",$status[istp_changetime]);
		}
	}
}
if($_GET['delscoring']){
	select("insert into clientScoring (id) values (".$status[id].")");
}
if(time() < CLIENT_SCORING_ENDTIME && !single("select COUNT(*) from clientScoring where id=".$status[id])){
	$infomsg = "<br><center>Wir, das Syndicates-Team würden uns freuen, wenn ihr euch 2-3 Minuten Zeit für eine Umfrage nehmen könntet. Ziel ist es, uns ein besseres Bild davon zu machen, was Ihr über einzelne Aspekte der Spiels denkt. Und was verbessert werden könnte, um Syndicates in Zukunft wieder mehr an die Bedürfnisse der Spieler anzupassen.<br>   <a  href=\"clientScoring.php\">Teilnehmen</a> - <a  href=\"statusseite.php?delscoring=1\" onClick=\"if(confirm('Willst du wirklich nicht teilnehmen?')) return true; return false;\">Keine Lust</a> <br><br>Euer Staffteam</center><br>";

		$tpl->assign('INFO', $infomsg);
		$tpl->display('info.tpl');
}


if ($president_announcement) {
	if ($status[new_synannouncement] && !$adminlogin && $panm!=1) {
		$infomsg = "<br><br><center>Hinweis: <b>Der Präsident hat eine neue <a style=\"text-decoration:underline;font-size:12px;\" href=\"statusseite.php?panm=1\">Syndikatsankündigung</a> erstellt!</b></center><br>";
		$tpl->assign('INFO', $infomsg);
		$tpl->display('info.tpl');
	
	}

	if ($panm==1) {
		if ($status[new_synannouncement] && !$adminlogin) {
			$queriesend[] = "update status set new_synannouncement = 0 where id = $id";
		}
	}
	$tpl->assign('showInternNews', true);
	$tpl->assign('news_chars', strlen($president_announcement));
	$tpl->assign('news_time', datum("d.m.Y, H:i", $president_announcement_changetime));
	$tpl->assign('news_style1', ($panm == 1 ? "table-row;" : "none;"));
	$tpl->assign('news_style2', ($panm == 1 ? "" : "style=\"display:none;\""));
	$tpl->assign('news_text', umwandeln_bbcode($president_announcement));
	$tpl->assign('news_style3', ($panm == 1 ? "true" : "false"));
	$tpl->assign('news_style4', ($status[new_synannouncement] && !$adminlogin ? "document.location = \"statusseite.php?panm=1\";" : ""));
	
}

if ($features[KOMFORTPAKET]) {
		if ($status[notespin]==1){
			$tpl->assign("showNotice",true);
			$tpl->assign("notice",umwandeln_bbcode(htmlentities($notes)));
		}
}
        
$races = assoc("select * from races where race ='".$status{race}."'");
js::loadCountdown();

if ($globals[roundstatus] == 1) {
	$naechste_produktion=($na_info[name]  != "Keine Produktion" ?
		$na_info[values][number]." $na_info[name] in <br>
		".js::countdown($na_info[left],array(h,m,s))."</span></b></td>" : $na_info[name]);
}
else {
	$naechste_produktion="$na_info[name]</td>";
}

$tpl->assign("next_prod", $naechste_produktion);
$tpl->assign("wiki",WIKI);
$tpl->assign("geb_total",pointit($allbuildings+$underconstruction));
$tpl->assign("geb_da",pointit($allbuildings));
$tpl->assign("geb_inbau",pointit($underconstruction));
$tpl->assign("land_unbebaut",pointit($freeland));
$tpl->assign("land_total",pointit($status[land]));
$tpl->assign("land_inbau",pointit(getnumberoflandunderconstruction()));
$tpl->assign("mill_da",pointit($units));
$tpl->assign("mill_weg",pointit($units_away));
$tpl->assign("mill_markt",pointit($units_market));
$tpl->assign("mill_inbau",pointit($units_prod));
$tpl->assign("mill_total",pointit($units+$units_away+$units_market+$units_prod));
$tpl->assign("spy_da",pointit($spies));
$tpl->assign("spy_markt",pointit($spies_market));
$tpl->assign("spy_inbau",pointit($spies_prod));
$tpl->assign("spy_total",pointit($spies+$spies_market+$spies_prod));

$nameFn = array(
	'money' => 'Credits',
	'metal' => 'Erz',
	'sciencepoints' => 'Forschungspunkte',
	'energy' => 'Energie'
);
$bonusFn = array(
	'PARTNER_METALBONUS' => 'Partnerbonus',
	'PARTNER_ENERGYBONUS' => 'Partnerbonus',
	'PARTNER_SCIENCEPOINTSBONUS' => 'Partnerbonus',
	'PARTNER_MONEYBONUS' => 'Partnerbonus',
	'PARTNER_ALLBONUS' => 'Partnerbonus',
	'PRAESIBONUS' => 'Pr&auml;sidentenbonus',
	'ECO_ENERGY_BONUS' => 'Hoover-Staudamm',
	'ECO_CREDIT_BONUS' => 'Goldener Thron',
	'ECO_METAL_BONUS' => 'Moria',
	'ECO_SCIENCEPOINTS_BONUS' => 'Tempel der Meditation',
	'ECO_ALL_BONUS' => 'Tempel der Arbeit',
	'IND9' => 'Improved Production',
	'IND1' => 'Better Ore Mining',
	'IND17' => 'Scientific Advances',
	'IND2' => 'Advanced Power Management',
	'IND10' => 'Economic Domination',
	'PBF_ENERGYBONUS' => 'BF-Bonus',
	'PBF_SCIENCE_MALUS' => 'BF-Malus',
	'UIC_METAL_BONUS' => 'UIC-Bonus',
	'UIC_PAUSCHAL_RESSOURCENBONUS' => 'UIC-Bonus',
	'SL_SCIENCE_BONUS' => 'SL-Bonus',
	'SL_METAL_MALUS' => 'SL-Malus',
	'NOF_ENERGYBONUS' => 'NOF-Bonus',
	'NOF_CREDIT_MALUS' => 'NOF-Malus',
	'NEB_METAL_BONUS' => 'NEB-Bonus',
	'WCENTERBONUS' => 'Wirtschaftszentren',
	'SYNERGY_BONUS' => 'Synergiebonus',
	'UNPROTECTION_BONUS' => 'Schutzzeitbonus'
);

$tpl_bonis = array();
foreach(production($status['id']) as $name => $bonuses){
	$tpl_boni = array();
	$total = round(array_sum($bonuses),2);
	$tpl_boni[0] = $nameFn[$name];
	$tpl_boni[1] = array();
	foreach($bonuses as $bonusName => $bonusValue){
		$uni_boni = array();
		$v = round($bonusValue,2);
		$uni_boni[0] = $bonusFn[$bonusName];
    	$uni_boni[1] = $v;
		array_push($tpl_boni[1],$uni_boni);
	}
	$tpl_boni[2] = $total;
	array_push($tpl_bonis,$tpl_boni);
}
$tpl->assign("bonus",$tpl_bonis);
$tpl->assign("showBoniInfo",$tpl_bonis); //trick 17 :)

//prodd
$tpl->assign("criticalenergy1",$criticalenergy1);
$tpl->assign("criticalenergy2",$criticalenergy2);
$tpl->assign("moneyadd",pointit($moneyadd));
$tpl->assign("metaladd",pointit($metaladd));
$tpl->assign("sciencepointsadd",pointit($sciencepointsadd));
$tpl->assign("energyprod",pointit($energyprod));
$tpl->assign("energyuse",pointit($energyuse));
$tpl->assign("energyadd",pointit($energyadd));
$tpl->assign("maxsave_reached",$maxsave_reached);
$tpl->assign("warner",$warner);

if ($moneylageradd or $metallageradd or $sciencepointslageradd or $energylageradd)	{
	$tpl->assign("showStorageProduction",true);
	$tpl->assign("st_curr",$syndvalues[currency]);
	$tpl->assign("st_cr_x",pointit($moneylageradd));
	$tpl->assign("st_cr_hp",pointit($hpmoneyadd));
	$tpl->assign("st_nrg_x",pointit($energylageradd));
	$tpl->assign("st_nrg_hp",pointit($hpenergyadd));
	$tpl->assign("st_fp_x",pointit($sciencepointslageradd));
	$tpl->assign("st_fp_hp",pointit($hpsciencepointsadd));
	$tpl->assign("st_erz_x",pointit($metallageradd));
	$tpl->assign("st_erz_hp",pointit($hpmetaladd));
	if($hpmetaladd || $hpsciencepointsadd || $hpenergyadd)
		$tpl->assign("st_hp",pointit(($hpmetaladd+$hpmoneyadd+$hpenergyadd+$hpsciencepointsadd)));
}

//Partner Boni Anzeigen
if ($status[partnerschaften]) {

	$tpl->assign("showPartnerBoni",true);
	
	$partnerschaften = assocs("SELECT s.id, bonus, type FROM partnerschaften_general_settings AS gs, partnerschaften_settings AS s " .
			"WHERE s.id = gs.id AND s.round = '".$globals['round']."' ORDER BY type", 'id');
	
	$tpl_partnerschaften = array('mill' => array('boni' => array(),'name' => 'Militär'), 
								'spy' => array('boni' => array(),'name' => 'Spionage'),
								'eco' => array('boni' => array(),'name' => 'Wirtschaft'),
								'all' => array('boni' => array(),'name' => 'Allgemein'));
	$tpl_partner = $tpl_partnerschaften;
	$kates = array("mill"=>0,"spy"=>0,"eco"=>0,"all"=>0);
	$partner_gewaehlt = 0;
	if ($partnerschaften) {
		foreach ($partnerschaften as $ky => $vl) {
			if (!$partner[$ky]) {
				$tpl_partnerschaften[$vl['type']]['boni'][$vl['id']] = $vl;
			} else {
				$partner_gewaehlt++;
				$kates[$vl['type']]++;
				$tpl_partner[$vl['type']]['boni'][$vl['id']] = $vl;
			}
		}
	}
	foreach ($tpl_partnerschaften as $type => $vl) {
		if (PBS_PER_TYPE_CHOOSEABLE <= $kates[$type]) {
			$tpl_partnerschaften[$type]['is_full'] = true; 
		}
	}

	$pdifferenz = $status['partnerschaften'] - $partner_gewaehlt;

	if (1 <= $pdifferenz) {
		if($action != "setpartner"){
			if ($pdifferenz>1) $mehrzahl = 'boni'; else $mehrzahl = 'bonus';
			$infomsg = 'Sie können noch insgesamt '.$pdifferenz.' Partnerschafts'.$mehrzahl.' wählen! (<font onclick=\"javascript: document.location.href=\'#pboni\'" style="cursor:pointer; font-style:italic;">siehe unten</font>)';
			$tpl->assign('INFO', $infomsg);
			$tpl->display('info.tpl');
		}
	}
	
	$tpl->assign('PBS_PER_TYPE_CHOOSEABLE', PBS_PER_TYPE_CHOOSEABLE);
	$tpl->assign("PARTNER_1",$tpl_partner);
	$tpl->assign("PARTNERSCHAFTEN",$tpl_partnerschaften);
	$tpl->assign("PARTNER_DIFF",$pdifferenz);
}


// Proectionausgabe
$PROTECT="";
if (in_protection($status)) {
    $difference = $status['unprotecttime'] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
    if (getServertype() == "classic" && $status[inprotection] == "Y") {
		$tpl->assign("showProtectInfo","config");
		$tpl->assign("prot_time",(PROTECTIONTIME/3600));
    } else {
		$tpl->assign("showProtectInfo","protection");
		$tpl->assign("prot_day",$days);
		$tpl->assign("prot_std",$hours);
		$tpl->assign("prot_min",$minutes);
    }
}

if (($globals[roundendtime] - $time <= 5 * 24 * 3600) && ($globals[roundendtime] - $time > 0)) {
	$difference = $globals[roundendtime] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign("showRoundInfo","end");
	$tpl->assign("rnd_day",$days);
	$tpl->assign("rnd_std",$hours);
	$tpl->assign("rnd_min",$minutes);
	$tpl->assign("rnd_date",date("d. M", $globals[roundendtime]));
	$tpl->assign("rnd_time",date("H:i", $globals[roundendtime]));

}


/*foolif
$fool=1301680800;
if(($fool > $time)) {
	$difference = $fool - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign("fool","fool");
	$tpl->assign("af_day",$days);
	$tpl->assign("af_std",$hours);
	$tpl->assign("af_min",$minutes);
	$tpl->assign("af_date",date("d. M", $fool));
	$tpl->assign("af_time",date("H:i", $fool));
}
//fool*/

if ($globals[roundstarttime] - $time > 0) {
	$difference = $globals[roundstarttime] - $time;
    $days = (int) ( $difference / (24 * 60 * 60));
    $hours = (int) (($difference - $days * 24 * 60 * 60) / (60 * 60));
    $minutes = (int) (($difference - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);
	$tpl->assign("showRoundInfo","start");
	$tpl->assign("rnd_day",$days);
	$tpl->assign("rnd_std",$hours);
	$tpl->assign("rnd_min",$minutes);
	$tpl->assign("rnd_date",date("d. M", $globals[roundstarttime]+3600));
	$tpl->assign("rnd_time",date("H:i", $globals[roundstarttime]+3600));
}

if ($action == "setpartner") {
	//unset($ausgabe);
	//$zurueck = "<br><br><br><a href=\"javascript:history.back()\" class=linkAufsiteBg>zurück</a>";
	//$weiter = "<br><br><a href=statusseite.php class=linkAufsiteBg>weiter</a>";
	if ($pdifferenz >= 1 && !$globals['updating']) {
		$partner_gen_set = assoc("SELECT * FROM partnerschaften_general_settings WHERE id = '".addslashes($pid)."'");
		if (!$partner[$pid] && $kates[$partner_gen_set['type']]<PBS_PER_TYPE_CHOOSEABLE) {
			$pid = floor($pid);
			$bonus = single("select id from partnerschaften_settings where id = $pid and round = $globals[round]");
			if (!$ia) {
				if ($bonus) {
					$infomsg = "<br><table width=80% class=i><tr><td><center>Möchten Sie wirklich folgenden Bonus wählen?<br><br>
					".$partner_gen_set['bonus']."<br><br>
					<a href=statusseite.php?action=setpartner&pid=$pid&ia=confirm>JA</a> - <a href=statusseite.php>Abbrechen</a></center></td></tr></table>";
					$tpl->assign('INFO', $infomsg);
					$tpl->display('info.tpl');
				}
				else { 
					$errormsg = "Ungültigen Bonus gewählt!";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl'); 
				}
			}
			if ($ia == "confirm") {
				if ($bonus) {
					$barrier = 0;
					if ($pid == 21) {
						if (beschleunige_forschung(0, 1)) { // testen ob geforscht wird
							beschleunige_forschung(PARTNERBONUS_FORSCHUNG_BESCHLEUNIGEN);// Aktuelle Forschung beschleunigen;
						} else $barrier = 1;
					}
					if (!$barrier) {
						if ($partner[$pid]) {
							$queries[] = "update partnerschaften set level = ".($partner[$pid] + 1)." where user_id = $id and pid = $pid";
						}
						else {
							$queries[] = "insert into partnerschaften (user_id, pid, level) values ($id, $pid, 1)";
						}
						if ($pid == 22) {
							$queries[] = "update status set defspecs = defspecs + ".(PARTNERBONUS_DEFSPECS)." where id = $id";
						}
						if ($pid == 23) {
							$queries[] = "update status set offspecs = offspecs + ".(PARTNERBONUS_OFFSPECS)." where id = $id";
						} 
						$beschr = "Sie haben den Bonus <b>\"".$partner_gen_set['bonus']."\"</b> gewählt.";
						$tpl->assign("MSG", $beschr);
						$tpl->display("sys_msg.tpl");
						// Obige Berechnung nachkorrigieren:
						$tpl_partner[$partner_gen_set['type']]['boni'][$pid] = $partner_gen_set;
						unset($tpl_partnerschaften[$partner_gen_set['type']]['boni'][$pid]);
						if (PBS_PER_TYPE_CHOOSEABLE <= $kates[$partner_gen_set['type']]+1) {
							$tpl_partnerschaften[$partner_gen_set['type']]['is_full'] = true; 
						}
						$pdifferenz -= 1;
						$tpl->assign("PARTNER_1",$tpl_partner);
						$tpl->assign("PARTNERSCHAFTEN",$tpl_partnerschaften);
						$tpl->assign("PARTNER_DIFF",$pdifferenz);
					} else {
						$errormsg = "Sie können den Bonus zum Beschleunigen der Forschung erst wählen, wenn Sie eine Forschung erforschen. Sie erforschen zurzeit nichts.";
						$tpl->assign('ERROR', $errormsg);
						$tpl->display('fehler.tpl');
					}
				}
				else { 
					$errormsg = "Ungültigen Bonus gewählt!";
					$tpl->assign('ERROR', $errormsg);
					$tpl->display('fehler.tpl');
				}
			}
		} elseif (PBS_PER_TYPE_CHOOSEABLE <= $kates[$partner_gen_set['type']]){ 
			$errormsg = "Sie haben bereits zwei Boni dieser Kategorie gewählt.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		} else {
			$errormsg = "Sie haben diesen Bonus bereits gewählt.";
			$tpl->assign('ERROR', $errormsg);
			$tpl->display('fehler.tpl');
		}
	}
	else if(!$globals['updating']) { 
		$errormsg = "Sie können keine weiteren Partnerschaftsboni wählen!";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');			
	}
	else { 
		$errormsg = "Momentan läuft das stündliche Update. Bitte warten Sie noch einen Augenblick und drücken Sie dann F5 oder laden Sie die Seite erneut.";
		$tpl->assign('ERROR', $errormsg);
		$tpl->display('fehler.tpl');
		exit();
	}
}

db_write($queries);
db_write($queriesend,1); # Für queries die auch nach Rundende ausgeführt werden

//**************************************************************************
//							Header, Ausgabe, Footer
//**************************************************************************

$tpl->display('statusseite.tpl');
require_once("../../inc/ingame/footer.php");


//**************************************************************************
//							Dateispezifische Funktionen					
//**************************************************************************


function getnumberoflandunderconstruction() {
	global $status;
	$action ="select sum(number) from build_buildings where user_id ='".$status[id]."' and building_name = 'land'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}

function getnumberofunitsunderconstruction() {
	global $status;
	$action ="select sum(number) from build_military where user_id ='".$status[id]."'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}
function getnumberofspiesunderconstruction() {
	global $status;
	$action ="select sum(number) from build_spies where user_id ='".$status[id]."'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}
?>
