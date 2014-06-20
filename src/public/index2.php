<?
session_start();
if($_GET['ajax']){
	header("content-type: text/html; charset=ISO-8859-1");
}
/* * * * * * * * * * * * * * * * * * * * * * */
/*                                           */
/*      neue Startseite für Syndikates       */
/*   by Jonathan Hasenfuß - R4bbiT - 2011    */
/*                                           */
/*********************************************/


/* * *
 * INFO:
 * Die Daten für den Login usw. kommen aus der index.php,
 * in welcher diese Datei eingefügt wird
 * * */
require_once("../includes.php");
require_once(LIB."picturemodule.php"); // F?r Logincode
require_once(LIB."mod_login.php"); // F?r Logincode
require_once(INC."style.php");
require_once(INC."ingame/globalvars.php");

connectdb();

select("INSERT INTO  `peak_tracker` (  `id` ,  `time` ) VALUES (NULL ,  '".time()."');");

// KONSTANTEN
define('COUNT_NEWS', 5);		// Anzahl an neuer News, die auf der Startseite angezeigt werden
define('NEWS_NEW', 3*24*60*60);	// Wie lange eine News als "new" markiert werden soll (in Sekunden)
define('ROUNDENDTIME', 48);		// Wie viele Stunden man sich nach Rundenende noch einloggen kann
$time = time();
$fraktionen = get_fraks(); // race => race / name / tag / shortname / description / active / nextactive
$globals = assoc("select * from globals order by round desc limit 1");
$stats_cfg = array(	'ausgeführt'				=>	'attack_numberdone_',
				'davon erfolgreich'			=>	'attack_numberdone_won_',
				'bestes Ergebnis'			=>	'attack_largest_won_',
				'insgesamt erobert'			=>	'attack_total_won_',
				'erlittene Angriffe'		=>	'attack_numbersuffered_',
				'davon verloren'			=>	'attack_numbersuffered_lost_',
				'größter Verlust'			=>	'attack_largest_loss_',
				'gesamt Verlust'			=>	'attack_total_loss_');
$stats_types = array('normal', 'siege', 'conquer', 'waraffected', 'killspies');
			// Standard, Belagerung, Eroberung, im Krieg, Spykill
$error = array();
$error += $error_old; //Kommt von index.php

if($_GET['action'] == 'error'){
	include(INC.'error.php');
	if($error_ausgabe != ''){
		$error[]=str_replace('class=gelblink', '', $error_ausgabe);
	}
	ob_end_clean();
}

//pvar($_GET);
//pvar($_POST);
//pvar($old_content);

/*$param  =   array(
	'method'    => 'fql.query',
	'query'     => 'SELECT share_count, like_count, comment_count, total_count FROM link_stat WHERE url="..."',
	'callback'  => ''
);
$facebook->api($param);
pvar( $facebook->api($param) );*/

/*$twitterStream = new TwitterOAuth ($consumer_key, $consumer_secret, $access_key, $access_secret);
$twitterStream->host = 'https://stream.twitter.com/1/';
$followers = $twitterStream->get('statuses/sample');

pvar($followers);*/


// Das Templatesystem  einbinden
require_once(LIB."smarty/libs/templates_setup.php");

// Eine neue Instanz der Template Klasse erzeugen
$tpl = Template::getInstance();
if(IS_MOBILE > 0){
	$tpl->setTemplateSet('startseite_mobile/');
}
else{
	$tpl->setTemplateSet('startseite/');
}
/* Facebook-Zeugs, Login usw. geschieht in index.php */
$tpl->assign(array(
				   		'FB_APP_ID'		=> FACEBOOK_APP_ID,
						'FB_API'		=> FACEBOOK_API,
						'FB_SECRET'		=> FACEBOOK_SECRET
						));
$tpl->assign('FBUID', $fbuid);

//pvar(EMOGAMES_account_exists_by_username('rabbit'));

/*
 * STARTSEITE - nur SMARTPHONE
 */
if($_GET['action'] == 'load_more_news' && $_GET['ajax'] && IS_MOBILE > 0){
	$_GET['num'] = (int) $_GET['num'];
	$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc limit ".($_GET['num']*COUNT_NEWS).', '.COUNT_NEWS);
	$tpl_news = array();
	$i = 0;
	foreach($anms as $news){
		$i++;
		$tpl_news[] = array(
						'id' => $news['announcement_id'],
						'header' => (strlen($news['headline']) > 43 ? substr($news['headline'],0,40)."..." : $news['headline']),
						'datum' => date('d.m.y', $news['time']),
						'zeit' => date('H:i', $news['time']),
						'mytime' => mytime($news['time']),
						'text' => preg_replace("/\n/","<br />",$news['content']),
						'hr' => ($i < COUNT_NEWS ? true : false),
						'new' => ($news['time'] >= $time - NEWS_NEW ? true : false )
						);
	}
	$tpl->assign('AJAX', $_GET['ajax']);
	$tpl->assign('NEWS', $tpl_news);
	$tpl->display('news.tpl');
	exit();
}

/*
 * STARTSEITE
 */
else if($_GET['action'] == 'anmeldung'){
	omniput("Syndicates_Aufruf_Anmeldung.php",1,$time);
	if($_GET['frak']){
		$_SESSION['choosed_frak'] = $_GET['frak'];
	}
	$x = 0;
	foreach($fraktionen as $f){
		if($f['active']) $x++;
	}
	$tpl->assign('FRAKS_ACTIVE', $x);
	$tpl->assign('FRAKS_SPAN', floor(100/$x));
}
else if($_GET['action'] == 'validate_register' && $_GET['ajax']){
	/*
	Formvalidation für das Registriern-Formular.
	Sowohl für den normalen als auch für den Facebook Teil
	*/
	if($_POST['werber_username']){
		$_SESSION['werber_username'] = $_POST['werber_username'];
	}
	$o = validate_reg_acc($_POST, 'short');
	$c = ob_get_contents();
	ob_end_clean();
	$a = array();
	foreach($o as $t => $v){
		$a[utf8_encode($t)] = utf8_encode($v);
	}
	echo json_encode(array('errors' => $a, 't' => utf8_encode($c)));
	exit();
}
else if($_GET['action'] == 'validate_konzern' && $_GET['ajax']){
	/*
	Formvalidation für das Konzernerstellen-Formular.
	*/
	$o = validate_reg_konzern($_POST, 'short');
	$c = ob_get_contents();
	ob_end_clean();
	$a = array();
	foreach($o as $t => $v){
		$a[utf8_encode($t)] = utf8_encode($v);
	}
	echo json_encode(array('errors' => $a, 't' => utf8_encode($c)));
	exit();
}
else if($_GET['action'] == 'fb_connect'){
	//
}
else if($_GET['action'] == 'own_stats'){
	$round = (int) $_GET['round'];
	$startround = $userdata['startround'];
	$origround=$globals{'round'};
	$rounds = singles('select round from '.$globals['statstable'].' where user_id = '.$userdata['id'].' order by round DESC');
	$tpl->assign('ROUNDS', $rounds);
	
	if(!$round || $round < $startround || $round > $globals['round'] || $round == 13 || $round == 14 || $round == 15){
		$round = $globals{'round'};
	}
	
	$konz = false;
	$konz = assoc('select * from '.$globals['statstable'].' where round='.$round.' and alive > 0 and user_id='.$userdata['id']);
	if($konz){
		$konz['spyopsdonepercent'] = ($konz['spyopsdone'] > 0 ? $konz['spyopsdonewon'] * 100 / $konz['spyopsdone'] : 0);
		$konz['spyopssufferedpercent'] = ($konz['spyopssuffered'] > 0 ? $konz['spyopssufferedlost'] * 100 / $konz['spyopssuffered'] : 0);
		$konz['synname'] = single('select name from syndikate_round_'.$round.' where synd_id = '.$konz['rid']);
		$tmp = array();
		$i=0;
		foreach($stats_cfg as $tag => $val){
			$tmp[$i]['name'] = $tag;
			foreach($stats_types as $t){
				$tmp[$i][$t] = $konz[$val.$t];
			}
			$i++;
		}
		$konz['att_stats'] = $tmp;
		
		$wh = 'from '.$globals['statstable'].' where round='.$round.' and alive > 0';
		$all = single('select count(syndicate) '.$wh);
		foreach($fraktionen as $tag => $val){
			$tmp = single('select count(race) '.$wh.' and race=\''.$tag.'\'');
			$num = $tmp / $all*100;
			$fraktionen[$tag]['num'] = $tmp;
			$fraktionen[$tag]['prozent'] = number_format($num, 2, ',', '.');
			$fraktionen[$tag]['prozent_raw'] = number_format($num, 2);
		}
		$tpl->assign('NUM_PLAYER', $all);
		$tpl->assign('NUM_ATTS', single('select sum(attack_numberdone_normal) + sum(attack_numberdone_siege) + sum(attack_numberdone_conquer) + sum(attack_numberdone_killspies) '.$wh));
		$tpl->assign('NUM_SPIES', single('select sum(spyopsdone) '.$wh));
		$tpl->assign('BUY_LAND', single('select sum(landexplored) '.$wh));
		$tpl->assign('AVG_NW', single('select avg(lastnetworth) '.$wh));
		$tpl->assign('AVG_LAND', single('select avg(lastland) '.$wh));
		$tpl->assign('ROUNDSTATUS', single('select roundstatus from globals where round = '.$round));
	}
	$tpl->assign('ROUND', $round);
	$tpl->assign('KONZ', $konz);
}
else if($_GET['action'] == 'emogames_cfg'){
	$loginkey = my_encrypt($userdata['emogames_user_id']).createkey();
	EMOGAMES_prepare_Login($userdata['emogames_user_id'],$loginkey);
	if ($game[name] == "Syndicates Testumgebung") {
		$emogames = "DOMAIN.de";
	} else { $emogames = "DOMAIN.de"; }
	header("location: http://$emogames/index.php?loginkey=$loginkey&$arg");
}
else if($_GET['action'] == 'infos'){
	frak_ext(get_frak_desc());
	frak_ext(get_frak_milis());
	frak_ext(get_frak_spys());
}
else if($_GET['action'] == 'nutzungsbedingungen'){
	$file = fopen(INC.'nutzungsbedingungen.php','r');
	if($file){
		$txt = '';
		while(!feof($file)){
			$txt .= fgets($file);
		}
		$txt = preg_split('/\|\*\| \?\>(\r|\n|\r\n)/', $txt);
		$txt = $txt[1];
		$txt = str_replace(' class="gelblink"', '', $txt);
	}
	else{
		$txt = 'Die Nutzungsbedingungen sind zur Zeit nicht verfügbar';
	}
	$tpl->assign('NUBS', $txt);
}
else if($_GET['action'] == 'impressum'){
	require_once(INC.'impressum.php');
	$t = ob_get_contents();
	$t = str_replace('<a', '<a target="_blank"', $t);
	preg_match_all('#<FONT class=normal>(.*)<\/FONT>#sU', $t, $a);
	ob_end_clean();
	$tpl->assign('IMPRESSUM', $t);
	$tpl->assign('IMPRESSUM_', $a[0][0].$a[0][1]);
}
else if($_GET['action'] == 'stats'){
	$vkrieg = 24*60*60; //Vorlaufzeit für Kriege - 24h
	$skrieg = get_day_time($time)-24*60*60;
	//Warticker
	$wars = assocs('select starttime-'.$vkrieg.' as starttime, endtime, ended_by, first_synd_1, first_synd_2, first_synd_3, second_synd_1, second_synd_2, second_synd_3 from wars where starttime-'.$vkrieg.' > '.$skrieg.' or endtime > '.$skrieg.' order by starttime desc');
	$warticker = array();
	$warticker[0]['name'] = 'Heute';
	$warticker[1]['name'] = 'Gestern';
	foreach($wars as $tag => $val){
		$tmp = array();
		$tmp['status'] = ($val['starttime'] > $val['endtime'] ? 'start' : 'end');
		$tmp['time'] = ($val['starttime'] > $val['endtime'] ? $val['starttime'] : $val['endtime']);
		$tmp['a_synname'] = single('select name from syndikate where synd_id = '.$val['first_synd_1']);
		$tmp['a_allyname'] = false;
		if($val['first_synd_2'] || $val['first_synd_3']){
			$tmp['a_allyname'] = single('select name from allianzen where '.$val['first_synd_1'].' in (first, second, third)');
		}
		
		$tmp['e_synname'] = single('select name from syndikate where synd_id = '.$val['second_synd_1']);
		$tmp['e_allyname'] = false;
		if($val['second_synd_2'] || $val['second_synd_3']){
			$tmp['e_allyname'] = single('select name from allianzen where '.$val['second_synd_1'].' in (first, second, third)');
		}
		
		for($i=1; $i<=3; $i++){
			if($val['first_synd_'.$i]){
				$tmp['a_rids'][] = $val['first_synd_'.$i];
			}
			if($val['second_synd_'.$i]){
				$tmp['e_rids'][] = $val['second_synd_'.$i];
			}
		}
		
		if($val['ended_by'] == $val['first_synd_1'] || $val['ended_by'] == $val['first_synd_2'] || $val['ended_by'] == $val['first_synd_3']){
			$tmp['won'] = 'a';
		}
		else if($val['ended_by'] == $val['second_synd_1'] || $val['ended_by'] == $val['second_synd_2'] || $val['ended_by'] == $val['second_synd_3']){
			$tmp['won'] = 'e';
		}
		else{
			$tmp['won'] = 'admin';
		}
		$warticker[(date('d', $time) == date('d', $tmp['time']) ? 0 : 1)]['data'][] = $tmp;
	}
	$tpl->assign('WARTICKER', $warticker);
	
	$top = array();
	// top 3 Spieler - NW:
	$tmp = assocs("select nw_rankings as value, syndicate as name, rid from status where alive > 0 order by nw_rankings desc limit 3");
	$top[] = array( 'name' => 'Spieler - Networth', 'type' => 'Nw', 'data' => $tmp);
	
	// top 3 Spieler - Land:
	$tmp = assocs("select land_rankings as value, syndicate as name, rid from status where alive > 0 order by land_rankings desc limit 3");
	$top[] = array( 'name' => 'Spieler - Land', 'type' => 'Hektar', 'data' => $tmp);
	
	// top 3 Syn - NW:
	$tmp = assocs("select nw_ranking as value, name, synd_id as rid from syndikate order by nw_ranking desc limit 3");
	$top[] = array( 'name' => 'Syndikate - Networth', 'type' => 'Nw', 'data' => $tmp);
	
	// top 3 Syn - Land:
	$tmp = assocs("select land_ranking as value, name, synd_id as rid from syndikate order by land_ranking desc limit 3");
	$top[] = array( 'name' => 'Syndikate - Land', 'type' => 'Hektar', 'data' => $tmp);
	
	// top 3 Standard:
	$tmp = assocs('select a.landgain as value, s.syndicate as name, s.rid as rid from attacklogs as a, status as s where a.time <= '.($time-60*60*24).' and a.type = 1 and a.aid = s.id order by a.landgain desc limit 3');
	$top[] = array( 'name' => 'Standard', 'type' => 'Hektar', 'data' => $tmp);
	
	// top 3 Belagerung:
	$tmp = assocs('select a.landgain as value, s.syndicate as name, s.rid as rid from attacklogs as a, status as s where a.time <= '.($time-60*60*24).' and a.type = 2 and a.aid = s.id order by a.landgain desc limit 3');
	$top[] = array( 'name' => 'Belagerung', 'type' => 'Gebäude', 'data' => $tmp);
	
	// top 3 Eroberung:
	$tmp = assocs('select a.landgain as value, s.syndicate as name, s.rid as rid from attacklogs as a, status as s where a.time <= '.($time-60*60*24).' and a.type = 3 and a.aid = s.id order by a.landgain desc limit 3');
	$top[] = array( 'name' => 'Eroberung', 'type' => 'Hektar', 'data' => $tmp);
	
	// top 3 Spykills:
	$tmp = assocs('select a.landgain as value, s.syndicate as name, s.rid as rid from attacklogs as a, status as s where a.time <= '.($time-60*60*24).' and a.type = 4 and a.aid = s.id order by a.landgain desc limit 3');
	$top[] = array( 'name' => 'Spione zerstören', 'type' => 'Spione', 'data' => $tmp);
	
	$tmp = assocs('select spyopsdonewon as value,rid,syndicate as name from stats where round='.$globals['round'].' order by spyopsdonewon desc limit 3');
	$top[] = array( 'name' => 'erfolgreiche Spione', 'type' => 'Aktionen', 'data' => $tmp);
	
	$tpl->assign('TOP', $top);
	$tpl->assign('INIT', true);
	
}
else if($_GET['action'] == 'hof'){
	$rounds = array();
	for($i = $globals['round']; $i > 0; $i--){
		$r = ( $i <= 2 ? $i-3 : $i-2);
		if($i < $globals['round'] || ($i == $globals['round'] && $globals['roundstatus'] == 2)){
			$rounds[$i]['round'] = $r;
			$rounds[$i]['id'] = $i;
		}
	}
	$tpl->assign('ROUNDS', $rounds);
	
	$_GET['round'] = (int) $_GET['round'];
	if($_GET['round'] > 0){
		$wh = 'from '.$globals['statstable'].' where round='.$_GET['round'].' and alive > 0'; //um etwas Tipparbeit zu sparen
		
		$result = single('select count(*) '.$wh);
		$tpl->assign('DATA', ($result > 0 && $_GET['round'] < $globals['round']) || ($_GET['round'] == $globals['round'] && $globals['roundstatus'] == 2 && $result) ? true : false);
		if($tpl->_tpl_vars['DATA']){
			$all = single('select count(syndicate) '.$wh);
			foreach($fraktionen as $tag => $val){
				$tmp = single('select count(race) '.$wh.' and race=\''.$tag.'\'');
				$num = $tmp / $all*100;
				$fraktionen[$tag]['num'] = $tmp;
				$fraktionen[$tag]['prozent'] = number_format($num, 2, ',', '.');
				$fraktionen[$tag]['prozent_raw'] = number_format($num, 2);
			}
			$tpl->assign('NUM_PLAYER', $all);
			$tpl->assign('NUM_ATTS', single('select sum(attack_numberdone_normal) + sum(attack_numberdone_siege) + sum(attack_numberdone_conquer) + sum(attack_numberdone_killspies) '.$wh));
			$tpl->assign('NUM_SPIES', single('select sum(spyopsdone) '.$wh));
			$tpl->assign('BUY_LAND', single('select sum(landexplored) '.$wh));
			$tpl->assign('AVG_NW', single('select avg(lastnetworth) '.$wh));
			$tpl->assign('AVG_LAND', single('select avg(lastland) '.$wh));

			if($_GET['show'] != 'konz' && $_GET['show'] != 'synd') $_GET['show'] = false;
			if($_GET['type'] != 'nw' && $_GET['type'] != 'land') $_GET['type'] = false;
			if(!$fraktionen[$_GET['frak']]) $_GET['frak'] = false;
			if($_GET['det_id']) $_GET['det_id'] = (int) $_GET['det_id'];
			
			if($_GET['show'] && $_GET['type']){
				$o = ($_GET['type'] == 'nw' ? 'lastnetworth' : 'lastland');
				if($_GET['show'] == 'synd'){
					$s = 'id,rid,sum(lastland) as land,sum(lastnetworth) as nw';
					$g = 'group by rid';
					$o = 'sum('.$o.')';
					$sname = assocs('select synd_id,name from syndikate_round_'.$_GET['round'], 'synd_id');
				}
				else{
					$s = 'id,rid,lastland as land,lastnetworth as nw,syndicate as name,race';
					$g = '';
				}
				$r = ($_GET['frak'] ? 'and race=\''.$_GET['frak'].'\'' : '');
				$rankings = assocs('select '.$s.' '.$wh.' '.$r.' '.$g.' order by '.$o.' desc limit 100');
				
				if($_GET['show'] == 'synd'){
					foreach($rankings as $tag => $val){
						$rankings[$tag]['name'] = $sname[$val['rid']]['name'];
					}
				}
				$tpl->assign('RANKINGS', $rankings);
				
				
				$konz = false;
				if($_GET['det_id'] > 0){
					$konz = assoc('select * '.$wh.' and id='.$_GET['det_id']);
					$konz['spyopsdonepercent'] = ($konz['spyopsdone'] > 0 ? $konz['spyopsdonewon'] * 100 / $konz['spyopsdone'] : 0);
					$konz['spyopssufferedpercent'] = ($konz['spyopssuffered'] > 0 ? $konz['spyopssufferedlost'] * 100 / $konz['spyopssuffered'] : 0);
					$konz['synname'] = single('select name from syndikate_round_'.$_GET['round'].' where synd_id = '.$konz['rid']);
					$tmp = array();
					$i=0;
					foreach($stats_cfg as $tag => $val){
						$tmp[$i]['name'] = $tag;
						foreach($stats_types as $t){
							$tmp[$i][$t] = $konz[$val.$t];
						}
						$i++;
					}
					$konz['att_stats'] = $tmp;
				}
				$tpl->assign('KONZ', $konz);
			}
		}
	}
}
else if($_GET['action'] == 'show_news'){
	// Um gezielt nur eine News an zu zeigen
	preg_match('/action=([a-zA-Z0-9_]+)/', $_SERVER['HTTP_REFERER'], $back); // $back[1] ist die Unterseite, die man zuvor aufgerufen hatte
	$fail = true;
	if($_GET['id'] != ''){
		$_GET['id'] = (int) $_GET['id'];
		$news = assoc('select * from announcements where announcement_id ='.$_GET['id']);
		if($news){
			$fail = false;
			$news_short = strip_tags('['.$news['headline'].'] - '.preg_replace("/[\n|\r|\r\n]/"," ",$news['content']));
			$tpl_news = array(
						'id' => $news['announcement_id'],
						'header' => (strlen($news['headline']) > 63 ? substr($news['headline'],0,60)."..." : $news['headline']),
						'datum' => date('d.m.y', $news['time']),
						'zeit' => date('H:i', $news['time']),
						'mytime' => mytime($news['time']),
						'text' => preg_replace("/(\n)/","<br />",$news['content']),
						'poster' => $news['poster'],
						'action' => $back[1],
						'news_short' => (strlen($news_short) > 247 ? substr($news_short,0,250)."..." : $news_short)
						);
		}
	}
	if($fail){
		$tpl_news = array(
					'id' => -1,
					'header' => '--',
					'datum' => '--.--.--',
					'zeit' => '--:--',
					'mytime' => '--.--.-- - --:--',
					'text' => 'Die gesuchte News konnte nicht gefunden werden',
					'poster' => 'Error',
					'action' => $back[1]
					);
	}
	$tpl->assign('POST', $tpl_news);
	if(IS_MOBILE > 0){
		$ct = single("select count(announcement_id) from announcements where (type ='outgame' or type = 'both') and time >= ".$news['time']);
		$tpl->assign('TEST', $ct.'-'.floor(($ct-1) / COUNT_NEWS));
		$tpl->assign('BACK_BUTTON', '?action=news&num='.floor(($ct-1) / COUNT_NEWS));
	}

}
else if($_GET['action'] == 'news_archiv'){
	$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc");
	$tpl_news = array();
	foreach($anms as $news){
		$tpl_news[] = array(
						'id' => $news['announcement_id'],
						'header' => $news['headline'],//(strlen($news['headline']) > 43 ? substr($news['headline'],0,40)."..." : $news['headline']),
						'datum' => date('d.m.y', $news['time']),
						'zeit' => date('H:i', $news['time']),
						'mytime' => mytime($news['time']),
						'text' => preg_replace("/\n/","<br />",$news['content']),
						'new' => ($news['time'] >= $time - NEWS_NEW ? true : false ),
						'poster' => $news['poster']
						);
	}
	$tpl->assign('ALL_NEWS', $tpl_news);
}
else{
	// Mainteil der Page
	// Die normale Ansicht aller News
	if(IS_MOBILE > 0){
		omniput("Syndicates_Aufruf_Startseite_Mobile",1,$time);
		$_GET['num'] = ((int) $_GET['num'] > 0 ? (int) $_GET['num'] : 0);
		$_GET['num']++;
		$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc limit ".$_GET['num']*COUNT_NEWS);
		$s = array();
		foreach($_GET as $t => $v) $s[] = $t.'='.$v;
		$tpl->assign('MORE_NEWS_LINK', implode('&', $s));
	}
	else{
		omniput("Syndicates_Aufruf_Startseite",1,$time);
		$anms = assocs("select * from announcements where type ='outgame' or type = 'both' order by time desc limit ".COUNT_NEWS);
	}
	$tpl_news = array();
	$i = 0;
	foreach($anms as $news){
		$i++;
		$tpl_news[] = array(
						'id' => $news['announcement_id'],
						'header' => (strlen($news['headline']) > 63 ? substr($news['headline'],0,60)."..." : $news['headline']),
						'datum' => date('d.m.y', $news['time']),
						'zeit' => date('H:i', $news['time']),
						'mytime' => mytime($news['time']),
						'text' => preg_replace("/\n/","<br />",$news['content']),
						'hr' => ($i < COUNT_NEWS ? true : false),
						'new' => ($news['time'] >= $time - NEWS_NEW ? true : false )
						);
	}
	$tpl->assign('NEWS', $tpl_news);
}


//pvar(array($sid, $userdata['id'], $userdata));
if($sid && $userdata['id']){
	if($globals['roundstatus'] == 2 && $globals['roundendtime'] < ($time - ROUNDENDTIME * 60 * 60)){
		// Wenn die Runde beendet ist, kann man sich nichtmehr einloggen
		$tpl->assign('LOGIN', 'end');
	}
	else{
		if($userdata['konzernid'] > 0){
			// Wenn man eingeloggt ist, wird das Captcha angezeigt
			//$_SESSION['s_captcha'] = 't9H)sd(6)Y';
			//$_SESSION['s_sid'] = $sid;
			//$_SESSION['s_userdata'] = $userdata;
			$tpl->assign('LOGIN', 'second');
			$tpl->assign('PATHSET', single('select imagepath from status where id='.$userdata['konzernid']));
		}
		else{
			$tpl->assign('LOGIN', 'konz');
		}
	}
}
else{
	if($fbme){
		$tpl->assign('LOGIN', 'fb_reg');
	}
	else{
		// Wenn nicht, dann wird der Loginbereich angezeigt
		$tpl->assign('LOGIN', 'first');
	}
}
$tpl->assign('REGISTER', 'acc');

// TODO
$time = time();


// Für die Toolbar, wann Rundenende/start/..
// Übergang zu Sommer/Winterzeit beachten
$rundenstart = $globals['roundstarttime'];
$rundenende = $globals['roundendtime'];

// Anmeldephase findet direkt mit dem Rundenende statt
$next_anmeldephase = checkSummertime($globals['roundendtime'], ROUND_FREEZETIME_DURATION);
// Von Do 20:00 bis Mi 14:00 sind es 2d und 18h
$next_rundenstart = checkSummertime($next_anmeldephase, ROUND_ANMELEPHASE_DURATION);
$aglRight = '&nbsp;&nbsp;&nbsp;&nbsp;';
$aglLeft = '&nbsp;&nbsp;&nbsp;&nbsp;';
if ($time < $rundenstart) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', '<br>Anmeldephase'.$aglRight);
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'Rundenstart <br>'.$aglLeft.'So '.date('H:i',$rundenstart).' Uhr');		
} elseif ($time < ($rundenende - 45*86400)) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', 'Runde erst<br>gestartet'.$aglRight);
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'schnell<br>'.$aglLeft.'einsteigen');
} elseif ($time < ($rundenende - 10*86400)) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', 'Runde läuft');
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'noch '.getDayDifference($time, $rundenende).' Tage');
} elseif ($time < ($rundenende - 2*86400)) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', 'Runde läuft');
	$tpl->assign('RS2_TYPE', 'neutral'); $tpl->assign('RS2_MSG', 'nur noch <br>'.$aglLeft.getDayDifference($time, $rundenende).' Tage');	
} elseif ($time < $rundenende) {
	$tpl->assign('RS1_TYPE', 'negativ'); $tpl->assign('RS1_MSG', 'Runde endet<br> am Mi '.date('H:i',$rundenende).$aglRight);
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'Neustart in <br>'.$aglLeft.getDayDifference($time, $next_rundenstart).' Tagen');
} elseif ($time < ($next_anmeldephase - date('G', $next_anmeldephase)*3600 - date('i', $next_anmeldephase)*60)) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', 'Start der<br>Anmeldephase'.$aglRight);
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'am Do<br>'.$aglLeft.date('H:i',$next_anmeldephase).' Uhr');
} elseif ($time < $next_anmeldephase) {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', '<br>Anmeldephase'.$aglRight);
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', 'startet heute <br>'.$aglLeft.date('H:i',$next_anmeldephase).' Uhr');
} else {
	$tpl->assign('RS1_TYPE', 'positiv'); $tpl->assign('RS1_MSG', '');
	$tpl->assign('RS2_TYPE', 'positiv'); $tpl->assign('RS2_MSG', '');	
}

$tpl->assign('ANMELDE_DATUM', datum("d.m.Y, H:i", $next_anmeldephase).' Uhr'); // +21600 für 20Uhr
$tpl->assign('RUNDENSTART_DATUM', datum("d.m.Y, H:i", ($time < $rundenstart ? $rundenstart : $next_rundenstart)).' Uhr');
$tpl->assign('RUNDENENDE_DATUM', datum("d.m.Y, H:i", $rundenende).' Uhr');
if($globals['roundstatus'] == 2){
	$tpl->assign('ROUND_ICON', 'red');
}
else if($globals['roundendtime'] - 60*60*24*5 < $time || $globals['roundstatus'] == 0){
	$tpl->assign('ROUND_ICON', 'yellow');
}
else{
	$tpl->assign('ROUND_ICON', 'green');
}


$tpl->assign('HTTP', 'http://'.$_SERVER['HTTP_HOST'].'/');
$z = preg_split('#'.$_SERVER['SCRIPT_NAME'].'\?#', $_SERVER['HTTP_REFERER']);
$tpl->assign('HTTP_REF', get_backlink());
$tpl->assign('HTTP_QUERY', $_SERVER['QUERY_STRING']);
foreach($_GET as $tag => $val){
	$tpl->assign(strtoupper($tag), $val);
}
$tpl->assign('IE_WARNING', $_COOKIE['ie_warning']);
$tpl->assign('IS_MOBILE', IS_MOBILE);
$tpl->assign('FRAKTIONEN', $fraktionen);
$tpl->assign('GLOBALS', $globals);
$tpl->assign('ERRORS', $error);
if($_GET['ajax']){
	unset($_GET['ajax']);
	if($_GET['action'] == 'anmeldung'){
		if($sid && $userdata['id']){
			$tpl->display('register.konz.tpl');
		}
		else{
			$tpl->display('register.tpl');
		}
	}
	else{
		$tpl->assign('HTTP_REF', get_backlink());
		$tpl->display('content.tpl');
	}
}
else{
	$tpl->display('main.tpl');
}

/* * *
 * FUNKTIONEN
 * * */

function get_fraks($only_ava = false){
	global $sid, $userdata, $globals;
	$tmp = assocs('SELECT * FROM races'.($only_ava ? 'WHERE active = 1' : ''), 'race');
	foreach($tmp as $tag => $val){
		if($sid && $userdata['konzernid'] || $globals['roundstatus'] > 1){
			$tmp[$tag]['loged_in'] = true;
		}
		else{
			$tmp[$tag]['loged_in'] = false;
		}
	}
	return $tmp;
}

function get_frak_desc(){
	$desc = assocs('SELECT * FROM fraktionen_beschreibung', 'race');
	$out = array();
	foreach($desc as $tag => $val){
		$tmp = array();
		preg_match_all('/[^-]<li>(.*)<\/li>[^-]/', $val['description_html'], $tmp);
		foreach($tmp[1] as $t => $v){
			$tmp[1][$t] = preg_replace('/<span(.*)>(.*)<\/span>/', '<strong>$2</strong>', $tmp[1][$t]);
		}
		$out[$tag]['desc'] = $tmp[1];
	}
	return $out;
}

function get_frak_milis(){
	global $fraktionen, $tpl;
	if(!$fraktionen) $fraktionen = get_fraks;
	$sort = array('offspecs', 'defspecs', 'elites', 'elites2', 'techs');
	if($tpl) $tpl->assign('MILSORT', $sort);
	$out = array();
	foreach($sort as $s){
		$tmp = assocs('select * from military_unit_settings where type = \''.$s.'\'');
		if(count($tmp) == 1){
			foreach($fraktionen as $tag => $val){
				$out[$tag]['mil'][$s] = $tmp[0];
			}
		}
		else{
			foreach($tmp as $t => $v){
				$out[$v['race']]['mil'][$s] = $v;
			}
		}
	}
	return $out;
}

function get_frak_spys(){
	global $fraktionen;
	if(!$fraktionen) $fraktionen = get_fraks;
	$out = array();
	foreach($fraktionen as $tag => $val){
		$tmp = assocs('select * from spy_settings where race = \'all\' or race = \''.$tag.'\'');
		$out[$tag]['spys'] = $tmp;
	}
	return $out;
}


function frak_ext($arr){
	global $fraktionen;
	if(!$fraktionen) return false;
	foreach($fraktionen as $t => $v){
		$fraktionen[$t] += $arr[$t];
	}
}		

function get_backlink(){
	$ct = 0;
	foreach($_GET as $tag => $val){
		if($val != '') $ct++;
	}
	switch ($ct){
		case 1: $b = 0; break;
		case 2: $b = 1; break;
		case 5: $b = $_GET['det_id'] ? 4 : 2; break;
		case 6: $b = 5; break;
		default: $b = 2;
	}
	$back = array();
	$i = 0;
	foreach($_GET as $tag => $val){
		if($i >= $b){
			break;
		}
		$back[] = $tag.'='.$val;
		$i++;
	}
	return implode('&',$back);
}

function checkSyndicate($syndicate) {
	if (strlen($syndicate) < 3 or strlen($syndicate) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_&., ]/", $syndicate)) {
		return 0;
	}
	return 1;
}


function checkRulername($rulername) {
	if (strlen($rulername) < 3 or strlen($rulername) > 20 or preg_match("/[^\wäöüÄÖÜß\d-_ ]/", $rulername)) {
		return 0;
	}
	return 1;
}
?>