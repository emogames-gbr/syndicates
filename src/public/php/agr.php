<?
//Syndicates Angriffsrechner
//@author dragon12, 2012


//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//
if ($details){
	$details = floor($details);
}
if($role) {
	if($role != 'Verteidiger')
		unset($role);
}
if($target) {
	$target = floor($target);
	$targetid = $target;
}


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//

require_once("../../inc/ingame/game.php");

if($tarname) {
	$tarname = mres(urldecode($tarname)); //mres wird erst in game.php includet
	$data = assoc("SELECT id, rid FROM status WHERE syndicate = '$tarname'");
	$monus = array(13 => 'nebel', 17 => 'blitz', 14 => 'schule', 15 => 'mauer', 19 => 'trans');
	$monu = $monus[single("SELECT artefakt_id FROM syndikate WHERE synd_id =".$data['rid'])];
	$json_arr = array('id' => $data['id'], 'iswar' => isatwar($status['rid'], $data['id']), 'monu'=>$monu);
	echo json_encode($json_arr);
	exit(0);
}

//**************************************************************************//
//						     	  Header   	     	    					//
//**************************************************************************//

// Header include
require_once("../../inc/ingame/header.php");

//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$t = time();

if($target) {
	$target = assoc('SELECT rid, syndicate FROM status WHERE id='.$target);
	
	if($features[ANGRIFFSDB]) {
		$ids = singles('SELECT id FROM status WHERE rid = '.$status['rid']);
		$query_all = ' and spylogs.aid in ('.join(',', $ids).') and spylogs.did='.$targetid.' and spylogs_berichte.log_id=spylogs.id and success=1 and is_mentor_spy = 0 ORDER BY time DESC LIMIT 1';
		
		$spytype = "'unitintel1'"; //"'unitintel1', 'unitintel2', 'scienceintel', 'buildintel'";
		$konzilog = assoc('select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where action='.$spytype.$query_all);
		$spytype = "'unitintel2'";
		$millilog = assoc('select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where action='.$spytype.$query_all);
		$spytype = "'scienceintel'";
		$fossilog = assoc('select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where action='.$spytype.$query_all);
		$spytype = "'buildintel'";
		$geblog = assoc('select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where action='.$spytype.$query_all);
		
		if($millilog) {
			$unit_scan = ($millilog['time'] > ($konzilog['time']-20*60*60))?'milli':'konz'; //konzi ist besser als milli, falls der milli mehr als 20h alt ist
		}
		
		$spytype = "'killunits', 'killbuildings'";
		//$sabbs = assocs('select spylogs.*,uncompress(spylogs_berichte.bericht) as bericht from spylogs,spylogs_berichte where spylogs.did='.$targetid.' and spylogs.aid in ('.join(',', $ids).') and spylogs_berichte.log_id=spylogs.id and action in ('.$spytype.') and success=1 and time >= '.$from_time);
		
		if($konzilog) {
			$logs['konz'] = preg_replace('/<.+?>/', '',$konzilog['bericht']);
		}
		if($millilog) {
			$logs['mil'] = preg_replace('/<.+?>/', '',$millilog['bericht']);
		}
		if($geblog) {
			$logs['geb'] = preg_replace('/<.+?>/', '',$geblog['bericht']);
		}
		if($fossilog) {
			$logs['fos'] = preg_replace('/<.+?>/', '',$fossilog['bericht']);
		}
		$reports = $logs['fos'].' '.$logs['geb'];
		if($unit_scan == 'milli') {
			$reports .=' '.$logs['mil'].' ';
		} else {
			$reports .=' '.$logs['konz'].' ';
		}
		/*if($sabbs) {
			foreach($sabbs as $sabb) {
				$reports .= ' '.preg_replace('/<.+?>/', '',$sabb['bericht']);
			}
		}*/
		$tpl->assign('REPORTS', $reports);
	}
}

//militär in bau und auf Heimkehr in arrays schieben
$milbuild = rows("select bm.unit_id,bm.number,bm.time, mus.sort_order from build_military as bm, military_unit_settings as mus where bm.user_id=$id and bm.unit_id = mus.unit_id");
$milaway = rows("select ma.unit_id,ma.number,ma.time, mus.sort_order from military_away as ma, military_unit_settings as mus where ma.user_id=$id and ma.unit_id = mus.unit_id");
$milbuild_sorted = array();
$milaway_sorted = array();
foreach ($milbuild as $value)	{
	$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
	$milbuild_sorted[$value[3]-1][$x] += $value[1];
}
foreach ($milaway as $value)	{
	$x = floor ( ($value[2] - $t) / ($globals[roundtime] * 60));
	$milaway_sorted[$value[3]-1][$x] += $value[1];
}
$tpl->assign('MILAWAY', $milaway_sorted);
$tpl->assign('MILBUILD', $milbuild_sorted);

//eigenes monument
$monus = array(13 => 'nebel', 17 => 'blitz', 14 => 'schule', 15 => 'mauer', 19 => 'trans');
$monu = $monus[single("SELECT artefakt_id FROM syndikate WHERE synd_id =".$status['rid'])];
$tpl->assign('OWNMONU', $monu);

$angr_forschungen = array(array('name' => 'Basic Offense Tactics', 'sname' => 'bot', 'single' => false),
						array('name' => 'Combat Management', 'sname' => 'cm', 'single' => false),
						array('name' => 'Flexible Strategies', 'sname' => 'flex', 'single' => true),
						array('name' => 'Ranger &amp; Marine Training', 'sname' => 'rmt', 'single' => false),
						array('name' => 'Improved Weapon Technology', 'sname' => 'iwt', 'single' => false),
						array('name' => 'Syndicate Army Training', 'sname' => 'sat', 'single' => false),
						array('name' => 'Harden Armor', 'sname' => 'ha', 'single' => false),
						array('name' => 'Relentless Assault', 'sname' => 'ra', 'single' => true),
						array('name' => 'Basic Defense Tactics', 'sname' => 'bdt', 'single' => false),
						array('name' => 'Defense Network', 'sname' => 'dn', 'single' => false),
						array('name' => 'Orbital Defense System', 'sname' => 'ods', 'single' => false),
						array('name' => 'Fog of War', 'sname' => 'fow', 'single' => true)
					);
$vert_forschungen = array(array('name' => 'Basic Defense Tactics', 'sname' => 'bdt', 'single' => false),
						array('name' => 'Flexible Strategies', 'sname' => 'flex', 'single' => true),
						array('name' => 'Ranger &amp; Marine Training', 'sname' => 'rmt', 'single' => false),
						array('name' => 'Improved Weapon Technology', 'sname' => 'iwt', 'single' => false),
						array('name' => 'Syndicate Army Training', 'sname' => 'sat', 'single' => false),
						array('name' => 'Harden Armor', 'sname' => 'ha', 'single' => false),
						array('name' => 'Defense Network', 'sname' => 'dn', 'single' => false),
						array('name' => 'Orbital Defense System', 'sname' => 'ods', 'single' => false),
						array('name' => 'Fog of War', 'sname' => 'fow', 'single' => true)
					);

$units = array('sl' => array('buc' => 'Stalker', 'auc' => 'Headhunter', 'huc' => 'Stealth Bomber'),
				'bf' => array('buc' => 'Wartank', 'auc' => 'Strike Fighter', 'huc' => 'Titan'),
				'uic' => array('buc' => 'Robotic Wall', 'auc' => 'Firestorm', 'huc' => 'Sentinel'),
				'neb' => array('buc' => 'Patriot', 'auc' => 'Phoenix', 'huc' => 'EMP Cannon'),
				'nof' => array('buc' => 'Carrier', 'auc' => 'Halo', 'huc' => 'Behemoth')
		);

$angr_mil;
$vert_mil;

$angr_fos;
$vert_fos;

$angr_pbs;
$vert_pbs;

$angr_frak = 'sl';
$vert_frak = 'sl';

$partner_stats = getPartnerStats();
$pb_res = array('/\+/', '/\s/', '/\d/');
$pb_rep = array('\\+', '\\s', '\\d*');
$partner_boni = array('ap' => preg_replace($pb_res, $pb_rep, $partner_stats[1]['bonus']), 'vp' => preg_replace($pb_res, $pb_rep, $partner_stats[2]['bonus']), 'landgain' => preg_replace($pb_res, $pb_rep, $partner_stats[3]['bonus']),
					'heimkehr' => preg_replace($pb_res, $pb_rep, $partner_stats[4]['bonus']), 'landloss' => preg_replace($pb_res, $pb_rep, $partner_stats[20]['bonus']), 'verluste' => preg_replace($pb_res, $pb_rep, $partner_stats[24]['bonus']));

if($role) {
	$angr_mil = array('rines' => 0, 'ranger' => 0,
			'buc' => 0, 'auc' => 0, 'huc' => 0,
			'rinesatt' => 0, 'rangeratt' => 0, 'bucatt' => 0, 'aucatt' => 0, 'hucatt' => 0);
	$vert_mil = array('rines' => $status['offspecs'], 'ranger' => $status['defspecs'],
			'buc' => $status['elites'], 'auc' => $status['elites2'], 'huc' => $status['techs'],);
	$vert_fos = array('bot' => $sciences['mil1'], 'cm' => $sciences['mil4'], 'flex' => $sciences['mil6'],
			'rmt' => $sciences['mil5'], 'iwt' => $sciences['mil12'], 'sat' => $sciences['mil15'],
			'ha' => $sciences['mil9'], 'ra' => $sciences['mil10'], 'bdt' => $sciences['mil2'],
			'dn' => $sciences['mil7'], 'ods' => $sciences['glo8'], 'fow' => $sciences['mil14']);
	$vert_frak = ($status['race'] == 'pbf' ? 'bf' : $status['race']);
	$vert_gebs = array('land' => $status['land'], 'forts' => $status['deftowers'], 'werk' => $status['workshops']);
	$vert_synarmee = $syndikatsarmee = assoc("select offspecs,defspecs from syndikate where synd_id=".$status[rid]);
	$vert_pbs = array('vp' => $partner[2], 'landloss' => $partner[20], 'verluste' => $partner[24]);
} else {
	$angr_mil = array('rines' => $status['offspecs'], 'ranger' => $status['defspecs'],
					'buc' => $status['elites'], 'auc' => $status['elites2'], 'huc' => $status['techs'],
					'rinesatt' => 0, 'rangeratt' => 0, 'bucatt' => 0, 'aucatt' => 0, 'hucatt' => 0);
	$vert_mil = array('rines' => 0, 'ranger' => 0,
			'buc' => 0, 'auc' => 0, 'huc' => 0,);
	$angr_fos = array('bot' => $sciences['mil1'], 'cm' => $sciences['mil4'], 'flex' => $sciences['mil6'], 
					'rmt' => $sciences['mil5'], 'iwt' => $sciences['mil12'], 'sat' => $sciences['mil15'],
					'ha' => $sciences['mil9'], 'ra' => $sciences['mil10'], 'bdt' => $sciences['mil2'],
					'dn' => $sciences['mil7'], 'ods' => $sciences['glo8'], 'fow' => $sciences['mil14']);
	$angr_frak = ($status['race'] == 'pbf' ? 'bf' : $status['race']);
	$angr_gebs = array('land' => $status['land'], 'forts' => $status['deftowers'], 'aussis' => $status['offtowers'], 'spec' => (($status['race'] == 'pbf')?$status['radar']: (($status['race'] == 'nof')?$status['workshops']:0)));
	$angr_synarmee = $syndikatsarmee = assoc("select offspecs,defspecs from syndikate where synd_id=".$status[rid]);
	$angr_pbs = array('ap' => $partner[1], 'vp' => $partner[2], 'landgain' => $partner[3], 'verluste' => $partner[24], 'heimkehr' => $partner[4]);
}

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//

$unit_values = assocs("select name, op, dp, type from military_unit_settings where not(race = 'dummy')");

$fraks = array('bf', 'sl', 'uic', 'neb', 'nof');
$tpl->assign('UNITS', $units);
$tpl->assign('UNIT_VALUES', $unit_values);
$tpl->assign('ROLE', $role);
$tpl->assign('FRAKS', $fraks);
$tpl->assign('CONST', array('satperc' => MIL15BONUS_SYNARMY/100, 'frakbfap' => PBF_ATTACK_BONUS/100, 'pbap' => PARTNER_OFFBONUS/100, 'pbvp' => PARTNER_DEFBONUS/100,
							'pblandgain' => PARTNER_LANDGAINBONUS/100, 'pblosses' => 0.1,
							'pblandloss' => PARTNER_LANDLOSSBONUS/100, 'fosbot' => MIL1BONUS_BASIC_OFFENSE/100, 'fosbdt' => MIL2BONUS_BASIC_DEFENSE/100,
							'fosflex' => MIL6BONUS_FLEX_STRAT/100, 'fosdn' => MIL7BONUS_DEF_NETWORK/100, 'fosiwt' => MIL12BONUS_IWT/100,
							'fosods' => GLO8BONUS_ORBITAL/100, 'fosraap' => MIL10BONUS_AP_BONUS/100,
							'fosiwtlosses' => 0.05, 'fosfowlosses' => 0.2, 'fosha' => 0.2,
							'titrinesupport' => 4, 'titrangersupport' => 4,
							'carrierrinesupport' => 2, 'carrierrangersupport' => 2,
							'monumauer' => 0.2, 'monunebel' => 0.3, 'monuschule' => 0.2,
							'frakbflosses' => 0.2, 'werklosses' => 0.025,
							'unitwartankspergeb' => 50, 'unithhsprospy' => 4, 'unitrwlosses' => UIC_RW_LOSS_SPECIAL_FULL/100));
$tpl->assign('ANGR', array('frak' => $angr_frak, 'forschungen' => $angr_forschungen, 'mil' => $angr_mil, 'fos' => $angr_fos, 'gebs' => $angr_gebs, 'synarmee' => $angr_synarmee, 'pb' => $angr_pbs));	
$tpl->assign('VERT', array('frak' => $vert_frak, 'forschungen' => $vert_forschungen, 'mil' => $vert_mil, 'fos' => $vert_fos, 'gebs' => $vert_gebs, 'synarmee' => $vert_synarmee, 'pb' => $vert_pbs));
if($target) {
	$tpl->assign('TARGET', $target['syndicate'].' (#'.$target['rid'].')');
	$tpl->assign('TARGET_NAME', $target['syndicate']);
	$tpl->assign('TARGETID', $targetid);
	$tpl->assign('TARGET_RID', $target['rid']);
	$tpl->assign('ISWAR', isatwar($status['rid'], $target['rid']));
}
$tpl->assign('PBS', $partner_boni);
$tpl->assign('ROUNDTIME', $globals[roundtime]);
$tpl->assign('ROUNDSTARTTIME', $globals[roundstarttime]);
$tpl->assign('CURRENTTIME', $t);
$tpl->display('agr.tpl');





//**************************************************************************//
//							  Ausgabe, Footer	     						//
//**************************************************************************//

require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

?>