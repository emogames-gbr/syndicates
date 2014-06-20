<?
//echo "Angriff temporär deaktiviert.";
//exit(1);

//**************************************************************************//
//							Übergabe Variablen checken						//
//**************************************************************************//

$rid = floor($rid);
$target = floor($target);
$attacktarget = floor($attacktarget);


require_once("../../inc/ingame/game.php");
require_once (LIB."js.php");
js::loadOver();

$jobdataTest = assoc("select user_id, type, param, number, money, energy, metal, sciencepoints, accepttime, id, inserttime, onlinetime, anonym, normgain from jobs where target_id = $target and acceptor_id = $id");
if ($attacktype && $attacktype != "normal" && $attacktype != "siege" && $attacktype != "conquer" && $attacktype != "killspies"): $attacktype = "normal"; endif; 
if(!racherecht($target) && !inwar($status[rid],$target) && !$jobdataTest && !($attacktype = "normal") ){
	f("Sie können diesen Angriffstyp gegen diesen Spieler nicht verwenden!");
	$attacktype = "";	
	unset($action);
	$ichmachfalscheattack=1;
}
# Ungültige Attacktypen abfangen und Standard-Attacktype auf "normal" setzen

if ($action and $action != "ANGRIFF !")	{ unset($action);}


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//

define( ALLOWED_ATTACKS_A_DAY, 3);


define( KRIEG_LANDVERLUST_VERTEILUNG_OWNSYN, 0); # Soviel Prozent des eroberten Landes wird aufs eigene Syndikat verteilt, 0.5 == 50% !! ARGS, hier stand zeitweise live 50, das warne katastrophe!
define( KRIEG_LANDVERLUST_VERTEILUNG_GEGNERSYN, 0); # Soviel Prozent des eroberten Landes wird aufs übrige Syndikat umgelegt.
define( UIC_SENTINAL_RECYCLE_GAIN, 2000); # 2000 CR für jeden Sentinel für jede gefallene Einheit
define( NEB_SL_VERSATILE_BONUS, 0);
define( PBF_PBF_WAR_ATTACK_MALUS, 0); // PBF HAT GEGEN PBF IM KRIEG EINEN MALUS VON 15% auf ATTACK STRENGTH UND LANDGAIN
define( PBF_PBF_WAR_LANDGAIN_MALUS, 0);
define( SF_ANTIRANGERWALLBONUS,0); // SFs bekommen +4 Angriff gegen Ranger und walls # Abgeschafft Mai 2006 / Runde 21
//define( NEB_HOURS_FOR_LANDGAIN, 15); # Neb Land ist 4h schneller da // in globalvars.php
define( HALO_LANDMALUS, 0);
define( SF_SPEEDBONUS, 2); // SF kehren 4h schneller nach Hause zurück.
define (HALO_UNITLOSSMALUS, 25);
//define(ANGRIFFRECHNERURL,"http://mura.lms-clan.net/syn/angriffsrechner2/");
//define(ANGRIFFRECHNERURL2,"http://synagr.christian-voelker.de/ ");
//define(ANGRIFFRECHNERURL3,"http://syndicates.over-net.org/");
//define(ANGRIFFRECHNERURL4,"http://agr.tocos-werkstatt.de/");

//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//




//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//

$weeks_played = ceil((round_days_played()+1)/ 7);
define(ARMORYSAFE,$weeks_played*ARMORYSAFE_PER_WEEK); // Armories k?nnen 500 Angtriffspunkte speichern
define(ARMORYSAFEPUNKTE, ARMORYSAFE); // Armories k?nnen 500 Angtriffspunkte speichern

if (!$rid): $rid = $status[rid]; endif;
if (!$attacktype): $attacktype = "normal"; endif;

if($target){
	if(racherecht($target) || inwar($status[rid],$target) || $jobdataTest){
		$validattacktypes = array(	"normal"	=> 1, // $jobdataTest['type']=="normal" || $jobdataTest['type']=="conquer" (wobei normal für Landdezimierung und conquer für standardatt steht)
							"siege"		=> $jobdataTest ? $jobdataTest['type']=="siege" : 1,
							"conquer"	=> $jobdataTest ? 0 : 1,
							"killspies" => $jobdataTest ? $jobdataTest['type']=="killspies" : 1);
	} else{
		$validattacktypes = array(	"normal"	=> 1,
							"siege"		=> 0,
							"conquer"	=> 0,
							"killspies" => 0);
	}
} else {

$validattacktypes = array(	"normal"	=> 1,
							"siege"		=> 0,
							"conquer"	=> 0,
							"killspies" => 0);
					
}
$inactivity_mode = 0;
$totalaktien = 0;
$aktienprozente = 0;
$milexists = 0;
$barrier = 0;
$targetname = "";
$selected = "";
$disabled = "";
$atwarmeldung = "";
$reason = "";
$warattack_col = "";
$warattack_value = "";
$zurueck = "<br><br><br><a href=\"angriff.php?rid=$rid&target=$target\" class=linkAufsiteBg>zurück</a>";

$nextstepvalid = 0;


$disable_konauswahl = " disabled";
$disable_attackbutton = " disabled";
$disable_inputboxes = " disabled";
$standard_wrongsyndicate = "<center>Das gewählte Syndikat existiert nicht!</center>";

$war_land_updateaction = "";

$ginactive_col = "";
$ginactive_value = "";
$typeselect = "";

$safe_match = "";


$queries = array();
$kondata = array();
$konaktien = array();
$wardata = array();
$temp = array();
$opoints = array(); // check
$dpoints = array(); // check
$milsend = array();
$mildefend = array();
$status_d = array();
$sciences_d = array();
$wardata_d = array();
$losses = array();
$losses_d = array();
$milaway = array();	# Attacker
$remain = array();	# Defender
$losses_output = array();
$losses_output_d = array();
$bd = array();
$ba = array();
$bb = array();
$bal = array();
$bbl = array();
$bl = array();



$boni_a = 0;
$boni_d = 0;
$deftowerboni = 0;
$offtowerboni = 0;

$totalopoints = 0;
$totaldpoints = 0;

$winner = "";
$perc = 0;
$perc_d = 0;
$tperc = 0;
$pmod = 0;
$pmod_d = 0;
$awaytime = 0;
$landgain = 0;
$insertlandgain = 0;
$landgain_adjusted = 0;
$landdiff = 0;
$searchtime = 0;
$alreadyattacked = 0;
$alreadyattackedbyattacker = 0;
$landgainmultiplier = 0;
$others_existing = 0;
$totalothersyndland = 0;
$insmilawayvalues = "";
$largestgrab = 0;
$largestloss = 0;
$wartank_destroyed_buildings = 0;
$sonstiges_output = "";
$insertintomessages = "";
$builddeletefroms = "";
$buildinsertintos = "";
$freelandloss = 0;
$cnn = "";
$cnn_a = "";
$cnn_d = "";
$killed = 0;
$stealthed = 0;
$stealthed_col = '';
$attacklogs_insert_client_id = 0;
$attacklogs_insert_client_rid = 0;
$unitspecial_number = 0;

$metalplus = 0;
$energyplus = 0;
$moneyplus = 0;

$caseofwar = "";

$updates = "";
$updates_stats = "";
$updates_d = "";
$updates_stats_d = "";

// Vars für Funktionen

$fehlercode = 0;
$returncode = 0;
$returnvalue = 0;
$durch++;
$temp_output = "";
$counter = 0;
$messagedata = "";
$temp_losses_output = "";
$temp_losses_output_d = "";
$messageid = 0;
$landgain_unmodified = 0;

$ids = array();
$statusdata = array();
$landloss = array();

$hours = 0;
$minutes = 0;
$seconds = 0;
$daytime = 0;

$destroyed_spies = array();
$check = array();
$spytypes = array();
$sperre_spies = array();
$spyloss = array();
$spystats = array();
$new_spyloss = 0;
$totalspies = 0;
$spyloss_meldung = "";
$zufall = 0;

$buildingdaten = array();
$bb_all_data = array();
$freeland = 0;
$totalabzug = 0;
$zufallswert = 0;
$last = "";
$bl_anteil = array();
$bt = array();

$number_of_attacks = 0;
$personen_data = array();

$setwartanks = 0;
$totalabzug = 0;
$totalbuildings = 0;
$total_synd_land = 0;

// GLOBALVARS

$msb = array 	(	// msb steht für "Military Science Bonusses"

	1 => MIL1BONUS_BASIC_OFFENSE, 		// Basic Offense Tactics Bonus in %;
	2 => MIL2BONUS_BASIC_DEFENSE,			// Basic Defense Tactics Bonus in %;
	3 => MIL3BONUS_PROPAGANDA,			// Propaganda in %;
	4 => MIL4BONUS_COMBAT_MGMT*60,		// Combat Management in Sekunden/Minuten - je nach $roundtime;
	5 => MIL5BONUS_RANGER_AND_MARINE,			// Ranger & Marine Training in ganzer Zahl;
	6 => MIL6BONUS_FLEX_STRAT,			// Flexible Strategies in ganzer Zahl;
	7 => MIL7BONUS_DEF_NETWORK, 		// Defense Network in %;
	8 => MIL8BONUS,			// Better Space Management in ganzer Zahl;
	9 => MIL9BONUS_HARDEN_ARMOR,		// Harden Armor in % (weniger Verlust);
	10=> MIL10BONUS_RELENT_ASSAULT*60,		// Relentless Assault Angriffszeit reduziert um X in Sekunden/Minuten - je nach $roundtime;
	12=> MIL12BONUS_IWT,		// Improved Weapon Technology +10% Angriff
	13=> MIL13BONUS_RANGER_UPGRADE,			// Ranger Upgrade + 2 VP
	14=> MIL14BONUS_FOG_OF_WAR,		// Fog of War -25% weniger Verluste
	15=> (MIL15BONUS_SYNARMY / 100)	// 10% Unterstützung aus Synarmee je Level

		);
$msb2nd =	array (	// msb2nd steht für "Military Science Bonusses, Zweitwert" und ist für Forschungen gedacht, die mehrere Sachen tun

	10=>MIL10BONUS_ADDITIONAL_ATTS,			// Anzahl an weiteren möglichen Attacken pro Tag in ganzer Zahl //mil10
	12=>MIL12BONUS_SECOND_IWT,			// Improved Weapon Technology +5% Enemy losses
	14=>MIL14BONUS_SECOND_FOG_OF_WAR			// Fog of War -20% Landloss
		);
$msb3rd = array(	// msb3rd steht für "Military Science Bonusses, Drittwert" und ist für Forschungen gedacht, die mehrere Sachen tun
	14 => MIL14BONUS_THIRD_FOG_OF_WAR * 60			// Fog of War 4h längere Heimkehrzeit
);
$gsb = array	(	// gsb steht für "Global Sciences Bonusses"

	8 => GLO8BONUS_ORBITAL			// Orbital Defense System Verteidigungsbonus in %;

		);
$gsb2nd =array 	(	// gsb2nd steht für "Global Sciences Bonusses, Zweitwert" und ist für die Forschungen gedacht, die mehrere Sachen tun

	8 => GLO8BONUS_SECOND_ORBITAL			// Orbital Defense System weniger Landverlust in %;

		);

// Einschränkungsvariablen festlegen
list($maximum_attacks_reached, $attacks_possible_this_day) = checknumberofattacks();
$isatwar = isatwar($status[rid], $rid, 1);
$syndikat_d = assoc("select * from syndikate where synd_id = '$rid' limit 1");
if ($isatwar && getServertype() != 'basic'): i("Ihr Syndikat befindet sich mit diesem Syndikat im Krieg! Die Syndikatsarmee wird Sie in Angriffen verteidigen, sofern Sie <b>Syndicate Army Program</b> erforscht haben."); endif;
$temp = rows("select allianz_id, synd_type, artefakt_id from syndikate where synd_id in ($status[rid], $rid)");
$allianz_ids = array($temp[0][0], $temp[1][0]);
$synd_types = array($temp[0][1], $temp[1][1]);
if ($allianz_ids[0] && $allianz_ids[0] == $allianz_ids[1]): i("Ihr Syndikat ist mit diesem Syndikat alliiert!"); endif;
if ($synd_types[1] && ($synd_types[0] == "normal" && $synd_types[1] != "normal" or $synd_types[1] == "normal" && $synd_types[0] != "normal")) {
	if ($game_syndikat[synd_type] == "normal") {
		i("Dieses Syndikat ist ein Anfängersyndikat. Sie können deshalb keine Spieler aus diesem Syndikat angreifen!");
	} else { i("Dieses Syndikat ist kein Anfängersyndikat.<br>Sie befinden sich in einem Anfängersyndikat und können daher keine Spieler aus diesem Syndikat angreifen [andersherum natürlich auch nicht]. Diese Regelung ist zu Ihrem eigenen Schutz!"); }
}
$isprotection = in_protection($status);
$naps = assocs("select nappartner, type from naps_spieler where user_id=$id and type > 0", "nappartner");

//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//
$siteisvalid=1;
if ($siteisvalid)	
{
  if ($isprotection) 
  {
    i("<br>Sie koennen erst nach Verlassen der Schutzphase andere Spieler angreifen.<br>");
  } 
  else 
  {
$urlaubsmod = single("select count(*) from options_vacation where endtime = 0 and user_id = $id");
if (!$urlaubsmod)	
{

//							selects fahren									//

	$kondata = assocs("select id, syndicate, land, lastlogintime, createtime, alive, nw,gvi,inprotection,unprotecttime from status where rid=$rid", "id");



	if ($isprotection): f("Sie befinden sich momentan noch unter Schutz und können daher noch nicht angreifen!"); endif;
	if ($maximum_attacks_reached && !$action): i("Sie haben heute bereits ". (ALLOWED_ATTACKS_A_DAY + $sciences{mil10} * $msb2nd{10}) ." mal angegriffen.<br>Sie können jetzt nur noch Angriffe durchführen auf inaktive Spieler oder auf Spieler, deren Syndikate sich mit Ihrem im Krieg befinden!"); endif;

//							Berechnungen									//
	$ausgabe .= racherecht_ausgabe()."
		<br>
		<br>

			<script language=\"Javascript\">
			<!--
				function maxinput(tform, element, max) {
					document.forms[tform].elements[element].value = max;
				}
			-->
			</script>
		
		<table cellspacing=\"0\" cellpadding=\"2\" width=\"600\" class=\"siteGround\">
		<tr>
			<td width=\"160\" class=\"siteGround\" colspan=3>
				Sie können heute noch $attacks_possible_this_day Angriffe durchführen (*)
			</td>
		</tr>
		<form action=\"angriff.php\" method=\"post\" name=\"angriff\">
		<tr>
		       <td width=\"160\" class=\"siteGround\">
			   Syndikat auswählen:
			   </td>
			   <td width=\"300\" class=\"siteGround\">
			   <b>"
			   .($rid > 1 ? "<a href=angriff.php?rid=".($rid-1)." class=linkAufsiteBg>&lt; zurück</a>" : "&lt; zurück").
			   "</b>
			   &nbsp;&nbsp;
			   <input class=\"input\" type=\"text\" value=\"$rid\" maxlength=\"3\" size=\"2\" name=\"rid\"> <b>#</b>
			   &nbsp;&nbsp;
			   <b><a href=angriff.php?rid=".($rid+1)." class=linkAufsiteBg>vor &gt;</a></b>
			   </td>
		       <td width=\"140\" class=\"siteGround\"><input  class=\"button\" name=action type=\"submit\" value=\"auswählen\"></td>
		</tr>
		<tr><td colspan=\"3\">&nbsp;</td></tr>
		<tr>
		       <td valign=\"top\" width=\"160\" class=\"siteGround\">
			   Konzern auswählen:</td>
		       <td valign=\"top\" width=\"300\" class=\"siteGround\">

			   	   <table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"270\" class=\"tableOutline\"><tr><td>
				   <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"270\" class=\"tableInner1\">
				   <tr><td class=\"tableInner1\">
		<br>";
	
	foreach ($kondata as $ky => $vl)	{		#isattackable: 0=nicht angreifbar; 1=angreifbar; 4=(im krieg) nur normal angreifbar
		$reason = "";
		if ($standard_wrongsyndicate): unset($standard_wrongsyndicate); endif;
		
		list($anzahl,$aktienprozente,$umlauf) = aktienbesitz($vl['id'],$status['rid']);
		
		$isatwarvalue = 0;
		if ($isatwar or $personen_data[$ky]) $isatwarvalue = 1;
		$vl[ia] = isattackable($rid, $vl[alive], $vl[lastlogintime], $vl[land], $vl[createtime], $aktienprozente, $isatwarvalue, $isprotection, $maximum_attacks_reached, $allianz_ids[0], $allianz_ids[1], $naps, $ky, 1,$vl[nw],$vl[gvi], $synd_types[0], $synd_types[1], $vl[inprotection], $vl['unprotecttime']);
		$kondata[$ky][ia] = $vl[ia];
		
		// Reduzierten Landgain anzeigen
		list($dummy,$bash_protection_points) =  get_bash_protection($vl[id]);
		

		if ($disable_konauswahl and ($vl[ia] == 1 or $vl[ia] == 4) && !$isprotection): unset($disable_konauswahl); endif;
		if  ($target == $vl[id] and ($vl[ia] == 1 or $vl[ia] == 4) && !$isprotection): $selected = " checked"; $nextstepvalid = 1; $disable_attackbutton = ""; else: $selected = ""; endif;
		if (($vl[ia] == 1 or $vl[ia] == 4) && !$isprotection): $disabled = ""; $reason = ""; else: $disabled = " disabled"; $reason = " <span class=\"highlightAuftableInner\">[".transformfehlercode($vl[ia])."]</span>"; endif;
		if ($vl[ia] == 4 && !$isprotection): $reason = " <span class=\"highlightAuftableInner\">[W]</span>"; if ($target == $vl[id]): f("Dieser Spieler befindet sich in der landschwächeren Hälfte des ausgewählten Syndikats. Ein Angriff auf diesen Spieler zählt deswegen trotz des Kriegszustands als <b>normaler</b> Angriff und zählt zu den maximal 5 möglichen Angriffen pro Tag hinzu!"); endif; endif;

		
		$hours_limit = TIME_RELEVANT_FOR_BASH_PROTECTION	/(60*60);
		if ($reason == "" && $bash_protection_points == 1) {
			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"270\" class=\"tableOutline\"><tr><td><table cellspacing=\"0\" cellpadding=\"2\"><tr><td class=\"tableInner1\">Dieser Konzern wurde in den letzten $hours_limit Stunden bereits erfolgreich angegriffen.<br>Bei einem erfolgreichen Angriff wird das Ergebnis wie<br>z.B. erobertes Land um mindestens 25% reduziert.</td></tr></table></td></tr></table>";
			$reason =" <span class=\"highlightAuftableInner\">reduzierter Erfolg</span> <img ".js::showover($overtext)." src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\">";
		}
		elseif ($reason == "" && $bash_protection_points >= 2) {
			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"270\" class=\"tableOutline\"><tr><td><table cellpadding=\"2\" cellspacing=\"0\"><tr><td class=\"tableInner1\">Dieser Konzern wurde in den letzten $hours_limit Stunden bereits<b> mehrfach </b>erfolgreich angegriffen.<br>Bei einem erfolgreichen Angriff wird das Ergebnis wie<br>z.B. erobertes Land um mindestens 40% reduziert.</td></tr></table></td></tr></table>";
			$reason =" <span class=\"highlightAuftableInner\">stark reduzierter Erfolg</span> <img ".js::showover($overtext)." src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\">";
		}elseif ($reason == "" && isBuddy($vl['id'])) {
			$overtext = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"270\" class=\"tableOutline\"><tr><td><table cellpadding=\"2\" cellspacing=\"0\"><tr><td class=\"tableInner1\">Dieser Konzern gehört einem Spieler, der sich auf ihrer Buddylist befindet.</td></tr></table></td></tr></table>";
			$reason =" <span class=\"highlightAuftableInner\">[Buddy]</span>";
		}
		
		
		if ($id != $vl[id]): $ausgabe .= "<input name=\"target\" type=\"radio\" onChange=\"submit()\" value=\"".$vl[id]."\"$disabled$selected> ".$vl[syndicate]."$reason<br>"; endif;
		
		
		
	}


	$ausgabe .= "$standard_wrongsyndicate
				<br>
				   </td></tr>
				   </table>
				   </td></tr></table>

			   </td>
		       <td width=\"140\" class=\"siteGround\" valign=\"bottom\"></td>
		</tr>
		<tr><td colspan=\"3\">&nbsp;</td></tr>
		<tr>
		       <td valign=\"top\" width=\"160\" class=\"siteGround\">
			   Gewähltes Ziel: </td>
		       <td width=\"440\" class=\"highlightAufSiteBg\" colspan=\"2\" valign=\"bottom\"><b>"
			   .($target && $nextstepvalid ? "\"".$kondata[$target][syndicate]."\" (#$rid)<input class=\"input\" type=hidden name=attacktarget value=$target>" : "Kein Ziel ausgewählt").
			   "</b><span style=\"margin-left: 30px;\"><input type=\"button\" onClick=\"location.href='http://".$_SERVER['SERVER_NAME']."/php/agr.php?target='+$('input[name=attacktarget]').val()\" value=\"Angriff planen\"".$disable_attackbutton."></span></td>
		</tr>";

	$ausgabe .= "
		<tr><td colspan=\"3\">&nbsp;</td></tr>
		<tr>
		       <td valign=\"top\" width=\"160\" class=\"siteGround\">
			   Angriffstyp auswählen: </td>
		       <td width=\"440\" class=\"highlightAufSiteBg\" colspan=\"2\" valign=\"bottom\"><b>
			   <select name=attacktype".($nextstepvalid ? "" : " disabled").">"
			   .($validattacktypes[normal] ? "<option value=normal".($attacktype == "normal" ? " selected":"").">Standard" : "").
			    ($validattacktypes[siege] ? "<option value=siege".($attacktype == "siege" ? " selected":"").">Belagerung" : "").
				($validattacktypes[conquer] ? "<option value=conquer".($attacktype == "conquer" ? " selected":"").">Eroberung" : "").
				($validattacktypes[killspies] ? "<option value=killspies".($attacktype == "killspies" ? " selected":"").">Spione zerstören" : "").
			   "</b></td>
		</tr>";

	$ausgabe .= "
			<tr><td colspan=\"3\">&nbsp;</td></tr>
		<tr>
		       <td valign=\"top\" width=\"160\" class=\"siteGround\">
			   Streitmacht aufstellen:</td>
		       <td valign=\"top\" width=\"300\" class=\"siteGround\">

			   	   <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"270\" class=\"tableOutline\"><tr><td>
				   <table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"270\">
				   <tr class=\"tableHead\">
				   <td>Einheitenklasse</td>
				   <td align=right>verfügbar</td>
				   <td>senden</td>
				   </tr>
				   ";

	// Einheitendaten holen
	$wardata = assocs("select name, type, op, dp,dp as dp_orig, erforschbar, unit_id from military_unit_settings where race='".$status[race]."' or race='all'", "type");
	
	// Sciences auswerten und Einheitenwerte entsprechend modifizieren (NUR die Raw-Offense-Defense-Werte !!!)
	if ($sciences{mil6}): 	$wardata[defspecs][op] = $msb[6];
							$wardata[offspecs][dp] = $msb[6]; endif;
	if ($sciences{mil14}): 	$wardata[defspecs][dp] += MIL14BONUS_UNITS_VP_EXTRA;  //fow vp boni
							$wardata[offspecs][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							if($status['race'] != 'nof') //Carrier bekommen keinen vp bonus mehr von FoW (R60, dragon12)
								$wardata[elites][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							$wardata[elites2][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							$wardata[techs][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							endif;
	/*if ($status['race'] == "nof" && $isatwar) {	// Im Krieg haben NoF-Einheiten alle +2DP
		foreach ($wardata as $ky => $vl) {
			$wardata[$ky]['dp'] += 2;
		}
	}*/
	$sciences[mil5] = $sciences[mil5] == 3 ? 4 : $sciences[mil5];
	$wardata[defspecs][dp] += $sciences[mil5] * $msb[5];
	//if ($status['race'] == "neb") $wardata[defspecs][dp] += $sciences[mil13] * $msb[13];
	$wardata[offspecs][op] += $sciences[mil5] * $msb[5];
	$wardata[defspecs][op] += $sciences[mil5] * $msb[5];
	$wardata[offspecs][dp] += $sciences[mil5] * $msb[5];
	
	if ($sciences{ind7} >= $wardata[elites2][erforschbar]): $wardata[elites2][erforscht] = 1; endif;
	if ($sciences{mil11} >= $wardata[techs][erforschbar]): $wardata[techs][erforscht] = 1; endif;
	//if ($status['race'] == "nof" && $sciences['ind12']) $wardata[techs][erforscht] = 1; // Techs seit RUnde 28 für NOF auch nur noch über HUC

	// Selectstring für Statustable zusammenstellen
	foreach ($wardata as $vl)	{ $typeselect .= "$vl[type],";}
	$typeselect = chopp($typeselect);

	// Für jeden existenten Einheitentyp überprüfen, ob eine Zeile geschrieben werden soll
	foreach ($wardata as $vl)	{
		if ( $vl[op] and (!$vl[erforschbar] or $vl[erforscht] or $status[$vl[type]]))	{
					if (!$milexists && $status[$vl[type]]): $milexists = 1; endif;
					if (!$disable_inputboxes && !$status[$vl[type]]): $disable_inputboxes = " disabled";
					elseif ($disable_inputboxes && $status[$vl[type]] && $nextstepvalid): $disable_inputboxes = ""; endif;
					$ausgabe .= "<tr class=\"tableInner1\"><td>".$vl[name]."</td><td align=right>&nbsp;&nbsp;&nbsp;".pointit($status[$vl[type]])."</td><td><input class=\"input\" name=\"unit".$vl[type]."\" type=\"text\" value=\"".(floor($_REQUEST["unit".$vl[type]]) ? floor($_REQUEST["unit".$vl[type]]) : "")."\" size=5$disable_inputboxes>".(!$disable_inputboxes ? " <img width=18 height=18 src=\"".$ripf."_for_up.gif\" onClick=\"maxinput('angriff', 'unit".$vl[type]."',".$status[$vl[type]].");\"  align=absmiddle border=none>":"")."</td></tr>";
		}
	}
	if ($status[race]=="pbf" && false && ($status[multifunc] or $status[armories]))	{ $ausgabe .= "<tr class=\"tableInner1\"><td>Waffenlager Angriffspunkte</td><td align=right>&nbsp;&nbsp;&nbsp;".pointit($status[multifunc])."</td><td><input class=\"input\" name=\"waffenlagerangriffspunkte\" type=\"text\" value=\"".(floor($_POST["waffenlagerangriffspunkte"]) ? floor($_POST["waffenlagerangriffspunkte"]) : "")."\" size=5></td></tr>"; }
	if (!$milexists)	{ $disable_attackbutton = " disabled"; f("Sie besitzen zurzeit keine offensiven Militäreinheiten. Sie können daher keine Angriffe durchführen!");}


/*
				   <td class=\"gelb11\">Heavy Marians</td>
				   <td class=\"hellgruen11\">234</td>
				   <td class=\"ver12w\"><input name=\"\" type=\"text\" value=\"\" size=5></td>
*/
	$ausgabe .= "
				   <tr class=\"tableInner1\"><td colspan=\"3\" >Syndikatsarmee mitschicken: <input type=\"checkbox\" name=\"synarmeesend\" ".(($synarmeesend!='off')?'checked':'')."></td></tr>	
				  </table>
				   </td></tr>
				</table>
			   </td>
		       <td width=\"140\" class=\"siteGround\" valign=\"bottom\"><input class=\"button\" name=action type=\"submit\" value=\"ANGRIFF !\"$disable_attackbutton></td>
		</tr>
		</form>
		</table>
		<br><br>
		<table class=\"tableInner1\" style=\"border:1px solid;\">
			<tr>
				<td>
     <a class=\"linkAufTableInner\" href=\"history.php?selectview=attacks\">
     	<img src=\"$ripf/dot-gelb.gif\" hspace=\"5\" border=\"0\">Angriffsdatenbank</a>
     <a class=\"linkAufTableInner\" href=\"spies.php?inneraction=prepare&rid=$rid&target=$target\">
     	<img src=\"$ripf/dot-gelb.gif\" hspace=\"5\" border=\"0\">Ziel ausspionieren</a>
				</td>
			</tr>
		</table>
		<br>". /*
		<table class=\"tableInner1\" style=\"border:1px solid;\">
			<tr>
			<td>Verwendung der externen Angriffsrechner ohne Gewähr!</td>
			</tr>
			<tr>
				<td>
     <a class=\"linkAufTableInner\" target=\"_blank\" href=\"".ANGRIFFRECHNERURL2."\"><img src=\"$ripf/dot-gelb.gif\" hspace=\"5\" border=\"0\">Angriffsrechner von RoE</a>
     <br /><a class=\"linkAufTableInner\" target=\"_blank\" href=\"agr.php\"><img src=\"$ripf/dot-gelb.gif\" hspace=\"5\" border=\"0\">Interner Angriffsrechner</a>
				</td>
			</tr>
		</table>*/ "
		
		<br><br>* Die angegebene Zahl noch möglicher Angriffe an diesem Tag bezieht sich auf Personen, 
				die nicht inaktiv sind, auf die Sie kein Racherecht haben und deren Syndikate sich 
				<b>nicht</b> mit Ihrem Syndikat im Krieg befinden. Konzerne die in eine dieser 3 Kategorien 
				fallen können Sie beliebig oft angreifen.";


	// Wenn Action da ist -> Ab hier auf Angreifbarkeit prüfen und dann den Attack berechnen

	$target = $attacktarget;	// Verhindert, dass ein Umschalten der Radiobuttons ein anderes, als das "gewählte Ziel" angreift

	if ($action && !$isprotection && $kondata[$target] && !in_protection($kondata[$target]))	{

		// Anzahl angreifende und daheim bleibende Einheiten für 2:1 Regel
		$count_attacking_units_a = 0;
		$count_defending_units_a = 0; // Anzahl Einheiten, die daheim bleiben für 2:1 Regel seit Runde 42
		$count_attacking_carrier_a = 0;
		$count_defending_carrier_a = 0;
		
		if ($kondata[$target][ia] == 1 or $kondata[$target][ia] == 4)	{
			// Aus den Übergabeparametern die Einheitenanzahlen herausholen
			foreach ($_POST as $ky => $vl)	{
				if (preg_match("/unit([\w\d]+)/", $ky, $safe_match) and floor($vl) > 0)	{
					foreach ($wardata as $ky2 => $vl2)	{
						if ($ky2 == $safe_match[1]) {
							$vl = preg_replace('/\./', '', $vl);
							if ( $vl2[op] and floor($vl) and (!$vl2[erforschbar] or $vl2[erforscht] or $status[$ky2]))	{
								$milsend[$ky2] = floor($vl);
								if (floor($vl) > $status[$ky2]): $barrier = 1; endif;
							}
						}
					}
				}
				elseif ($ky == "waffenlagerangriffspunkte") {
					if ($status[race] == "pbf" && ($status[multifunc] < $vl or $vl > $status[armories] * ARMORYSAFEPUNKTE)): $barrier = 2; endif;
				}
			}
			$underattack = single("select underattack from status where id=$target");
			if (!$underattack): $lastattack = single("select time from attacklogs where did=$target order by time desc limit 1");
								if ($time - $lastattack < 10): $barrier=3;
								elseif (!$barrier): select("update status set underattack=0 where id=$target");
								endif;
			else: $barrier=3; endif;
			// Fehler ausgeben
			if ($barrier == 1)	{ f("Soviele Einheiten stehen nicht unter Ihrem Befehl!");}
			elseif ($barrier == 2)	{ f("Ihre Waffenlager verfügen nicht über so viele Angriffspunkte oder die von Ihnen eingegebene Anzahl Angriffspunkte ist größer als <b>[Anzahl Waffenlager] </b>*<b> [".ARMORYSAFEPUNKTE." speicherbare Punkte pro Waffenlager]</b> !");}
			elseif ($barrier == 3)  { f("Dieser Konzern wurde innerhalb der letzten 10 Sekunden bereits angegriffen!"); }
			elseif (array_sum($milsend) == 0)	{ f("Womit wollen Sie denn Angreifen?");}
			elseif (!isattackable_paid($target, $isatwar))	{ f("Diesen Konzern können Sie nicht angreifen, da Sie einen Probeaccount spielen, dieser Konzern jedoch ein Abonnement hat.<br>Sie können diesen Konzern nur dann angreifen, wenn er Sie innerhalb der letzten 24h erfolgreich angegriffen hat, oder wenn Ihr Syndikat mit diesem Syndikat im Krieg ist und dieser Konzern Sie, oder eines Ihrer Syndikatsmitglieder innerhalb der letzten 24h erfolgreich angegriffen hat.$zurueck"); }
			// AB HIER DANN FAST ENDGÜLTIGE ANGRIFFSBERECHNUNG
			else	{
				i("", TRUE); # bisher erzeugte Informationsmeldungen kicken
				unset ($ausgabe); // Bisherige Ausgabe töten
				attackausgabe("init");	# Ausgabe Initialisieren;
				a ("id", $target);
				//
				//// Verteidiger vorbereiten
				//
				$sciences_d = getsciences($target);
				$partner_d = getpartner($target);
				$status_d = getallvalues($target);

				// Inaktivität checken
				if ($status_d[lastlogintime] + TIME_TILL_GLOBAL_INACTIVE < $time): $inactivity_mode = 2; $ginactive_col = " ginactive, "; $ginactive_value = " '2', ";
				elseif ($status_d[lastlogintime] + TIME_TILL_INACTIVE < $time): $inactivity_mode = 1;  $ginactive_col = " ginactive, "; $ginactive_value = " '1', "; if ($status[rid] == $rid): $inactivity_mode = 2; endif;
				endif;
				# mode 1: für krieg, als inaktiven erkennen
				# mode 2: gang bang zählt net

				// Einheitendaten holen
				$wardata_d = assocs("select name, type, dp, erforschbar, unit_id from military_unit_settings where race='".$status_d[race]."' or race='all'", "type");

				/*if ($status_d['race'] == "nof" && $isatwar) {	// Im Krieg haben NoF-Einheiten alle +2DP
					foreach ($wardata_d as $ky => $vl) {
						$wardata_d[$ky]['dp'] += 2;
					}
				}*/

				// Sciences auswerten und Einheitenwerte entsprechend modifizieren (NUR die Raw-Offense-Defense-Werte !!!)
				$sciences_d[mil5] = $sciences_d[mil5] == 3 ? 4 : $sciences_d[mil5];
				if ($sciences_d{mil6}): $wardata_d[offspecs][dp] = $msb[6]; $wardata_d[defspecs][op] = $msb[6];
				 endif;
				$wardata_d[defspecs][dp] += $sciences_d[mil5] * $msb[5];
				$wardata_d[offspecs][dp] += $sciences_d[mil5] * $msb[5];
				if ($sciences_d{mil14}): 	$wardata_d[defspecs][dp] += MIL14BONUS_UNITS_VP_EXTRA;  //fow vp boni
							$wardata_d[offspecs][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							if($status_d['race'] != 'nof') //Carrier bekommen keine bonus vp von fow mehr (R60, dragon12)
								$wardata_d[elites][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							$wardata_d[elites2][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							$wardata_d[techs][dp] += MIL14BONUS_UNITS_VP_EXTRA; 
							endif;
				//if ($status_d[race] == "neb"): $wardata_d[defspecs][dp] += $sciences_d[mil13] * $msb[13]; endif;
				if ($sciences_d{ind7} >= $wardata_d[elites2][erforschbar]): $wardata_d[elites2][erforscht] = 1; endif;
				if ($sciences_d{mil11} >= $wardata_d[techs][erforschbar]): $wardata_d[techs][erforscht] = 1; endif;
				
				

				$market_mil_d_raw = assocs("select sum(number) as number,type,prod_id from market where owner_id = '".$target."' group by type,prod_id");
				foreach ($market_mil_d_raw as $vl)	{
					$prod = changetype($vl[type],$vl[prod_id]);
					$market_mil_d[$prod[product]] = $vl[number];
				}
				// Verteidigende Einheiten bestimmen
				foreach ($wardata_d as $ky => $vl)	{
						if ( $vl[dp] and ($status_d[$ky] or $market_mil_d[$ky]) and (!$vl[erforschbar] or $vl[erforscht] or $status_d[$ky] or $market_mil_d[$ky]))	{
							$mildefend[$ky] = $status_d[$ky] + $market_mil_d[$ky];
						}
					}
				
					
				//
				//// Boni-Werte bestimmen
				//
				foreach ($sciences as $ky => $vl)	{ if ($vl): a("sciences $ky", $vl); endif; }
				foreach ($sciences_d as $ky => $vl)	{ if ($vl): a("sciences_d $ky", $vl); endif; }

				// VERTEIDIGER
				$boni_d += $sciences_d{mil2} * $msb{2};	# Basic Defense Tactics
				$boni_d += $sciences_d{mil7} * $msb{7};	# Defense Network +vp%
				$boni_d += $sciences_d{glo8} * $gsb{8};	# Orbital Defense System
				$boni_d += $partner_d[2] * PARTNER_DEFBONUS;			# Partnerschaftsbonus: +5% Verteidigungsbonus
				
				
				$deftowerboni = ( $status_d{deftowers} * DEFTOWER_BONUS / $status_d{land} ) * 100 ;
				if ($deftowerboni > DEFTOWER_MAX_BONI): $deftowerboni = DEFTOWER_MAX_BONI; endif;
				$boni_d += $deftowerboni;
				
				
				if ($artefakte[$syndikat_d[artefakt_id]][bonusname] == "mil_defense_bonus") $boni_d += $artefakte[$syndikat_d[artefakt_id]][bonusvalue];
				// NOF-Verteidigungsbonus
				//if ($status_d['race'] == "nof") $boni_d += NOF_DEFENSE_BONUS; # Nof hat 20% Verteidigungsbonus
				// PBF-Verteidigungsbonus
				/*if ($status_d['race'] == "pbf") {
					$temp_bonus = floor($status_d[land] / PBF_DEFENSE_PBFLAND) * PBF_DEFENSE_BONUS_PER_PBFLAND;
					$temp_bonus >PBF_DEFENSE_BONUS_MAX ? $temp_bonus = PBF_DEFENSE_BONUS_MAX : 1; 
					$boni_d += $temp_bonus; # Nof hat 20% Verteidigungsbonus
				}*/
				

				// ANGREIFER VERTEIDIGUNGSBONI FÜR 10/3-REGEL -- Update 16.06.09 - Runde 42 wird diese Regel ausgebaut - nur noch 2:1 Regel- theoretisch nicht mehr nötig
				$boni_a_d += $sciences{mil2} * $msb{2};	# Basic Defense Tactics
				$boni_a_d += $sciences{mil7} * $msb{7};	# Defense Network +vp%
				$boni_a_d += $sciences{glo8} * $gsb{8};	# Orbital Defense System
				$boni_a_d += $partner[2] * PARTNER_DEFBONUS;			# Partnerschaftsbonus: +5% Verteidigungsbonus
				$deftowerboni_a = ( $status{deftowers} * DEFTOWER_BONUS / $status{land} ) * 100 ;
				if ($deftowerboni_a > DEFTOWER_MAX_BONI): $deftowerboni_a = DEFTOWER_MAX_BONI; endif;
				$boni_a_d += $deftowerboni_a;
				if ($artefakte[$game_syndikat[artefakt_id]][bonusname] == "mil_defense_bonus") $boni_a_d += $artefakte[$game_syndikat[artefakt_id]][bonusvalue];
				//if ($status['race'] == "nof") $boni_a_d += NOF_DEFENSE_BONUS; # Nof hat 20% Verteidigungsbonus

				// ANGREIFER
				$boni_a += $sciences{mil1} * $msb{1};	# Basic Offense Tactics
				$boni_a += $sciences{mil10} * MIL10BONUS_AP_BONUS;	# ra ap% boni
				//$boni_a -= $sciences_d{mil7} * $msb{7};	# Defense Network nicht mehr ap malus
				$boni_a += $sciences{mil12} * $msb{12}; # Improved Weapon Technology
				$boni_a += $partner[1] * PARTNER_OFFBONUS;			# Partnerschaftsbonus: +5% Angriffsbonus
				if ($status[race] == "pbf"):
					$boni_a += PBF_ATTACK_BONUS;
					if ($status_d[race] == "pbf" && $isatwar) {
						$boni_a -= PBF_PBF_WAR_ATTACK_MALUS;
						$landgainmalus += PBF_PBF_WAR_LANDGAIN_MALUS;
					}
				endif;
				$offtowerboni = ( $status[offtowers] * OFFTOWER_BONUS / $status[land] ) * 100;
				if ($offtowerboni > OFFTOWER_MAX_BONI): $offtowerboni = OFFTOWER_MAX_BONI; endif;
				if ($status_d[race] == "neb" && $status[race] == "sl" || $status_d[race] == "sl" && $status[race] == "neb") { $boni_a += NEB_SL_VERSATILE_BONUS; }
				$boni_a += $offtowerboni;
				if ($artefakte[$game_syndikat[artefakt_id]][bonusname] == "mil_attack_bonus") $boni_a += $artefakte[$game_syndikat[artefakt_id]][bonusvalue];

				//
				//// Angriffspunkte/Verteidigungspunkte bestimmen
				//

				// Synarmee für den Angreifer
				$syndikatsarmee = assoc("select offspecs,defspecs from syndikate where synd_id=".$status[rid]);
				/* Keine Syndikatsforschung mehr seit Runde 17 - September 2005
				$syndikatsarmee[synarmeeforschung] = explode("|", $syndikatsarmee[synarmeeforschung]);
				for ($i = 0; $i < 3; $i++) {
					for ($o = 1; $o <= 3/*$sciences{mil15}* / && $o <= $i+1; $o++) {
						$syndikatsarmee_prozentsatz += $syndikatsarmee[synarmeeforschung][$i];
					}
				}
				*/
				$syndikatsarmee_prozentsatz = $sciences{mil15} * $msb{15};
				if (!$isatwar) {
					$syndikatsarmee_prozentsatz += ($partner[19] ? (PARTNER_SYNARMEESUPPORT/100) : 0);
				} else {
					$syndikatsarmee_prozentsatz += ($partner[19] ? (PARTNER_SYNARMEESUPPORT_WAR/100) : 0);
				}
				if (!$synarmeesend){$syndikatsarmee_prozentsatz=0;}  #Synarmee auschaltbar
				

				// Synarmee für den Verteidiger

				$syndikatsarmee_d = assoc("select defspecs,offspecs"./*,synarmeeforschung*/" from syndikate where synd_id=".$status_d[rid]);
				/* Keine Syndikatsforschung mehr seit Runde 17 - September 2005
				$syndikatsarmee_d[synarmeeforschung] = explode("|", $syndikatsarmee_d[synarmeeforschung]);
				for ($i = 0; $i < 3; $i++) {
					for ($o = 1; $o <= 3/*$sciences_d{mil15}* / && $o <= $i+1; $o++) {
						$syndikatsarmee_d_prozentsatz += $syndikatsarmee_d[synarmeeforschung][$i];
					}
				}
				*/
				$syndikatsarmee_d_prozentsatz = $sciences_d{mil15} * $msb{15};
				if (!$isatwar) {
					$syndikatsarmee_d_prozentsatz += ($partner_d[19] ? (PARTNER_SYNARMEESUPPORT/100) : 0);
				} else {
					$syndikatsarmee_d_prozentsatz += ($partner_d[19] ? (PARTNER_SYNARMEESUPPORT_WAR/100) : 0);
				}
				if ($isatwar) $synarmeefaktor = MIL15BONUS_FACTOR_SYNARMY_ATWAR; else $synarmeefaktor = 1;
				
				/*
				foreach (array(0 => "offspecs", 1 => "defspecs") as $vl) {
					if ($milsend[$vl] * $syndikatsarmee_prozentsatz * $synarmeefaktor < $syndikatsarmee[$vl]): $syndikatsarmee[$vl] = $milsend[$vl] * $syndikatsarmee_prozentsatz * $synarmeefaktor; endif;
					if ($mildefend[$vl] * $syndikatsarmee_d_prozentsatz * $synarmeefaktor < $syndikatsarmee_d[$vl]): $syndikatsarmee_d[$vl] = $mildefend[$vl] * $syndikatsarmee_d_prozentsatz * $synarmeefaktor; endif;
				}*/
				$milsum_unterstuetzt_durch_syndikatsarmee = array_sum($milsend) - ($status['race'] == "nof" ? $milsend['elites'] : 0);
				if ($milsum_unterstuetzt_durch_syndikatsarmee * $syndikatsarmee_prozentsatz * $synarmeefaktor < $syndikatsarmee[offspecs]) {
					 $syndikatsarmee[offspecs] = $milsum_unterstuetzt_durch_syndikatsarmee * $syndikatsarmee_prozentsatz * $synarmeefaktor;
				}
				if ($milsum_unterstuetzt_durch_syndikatsarmee * $syndikatsarmee_prozentsatz * $synarmeefaktor < $syndikatsarmee[defspecs]) {
					 $syndikatsarmee[defspecs] = $milsum_unterstuetzt_durch_syndikatsarmee * $syndikatsarmee_prozentsatz * $synarmeefaktor;
				}

				// Anzahl Einheiten ermitteln, welche die Synarmee unterstützen, falls weniger
				$milsum_unterstuetzt_durch_syndikatsarmee_d = array_sum($mildefend) - ($status_d['race'] == "nof" ? $mildefend['elites'] : 0);
				if ($milsum_unterstuetzt_durch_syndikatsarmee_d * $syndikatsarmee_d_prozentsatz * $synarmeefaktor < $syndikatsarmee_d[defspecs]) {
					$syndikatsarmee_d[defspecs] = $milsum_unterstuetzt_durch_syndikatsarmee_d * $syndikatsarmee_d_prozentsatz * $synarmeefaktor;
				}
				if ($milsum_unterstuetzt_durch_syndikatsarmee_d * $syndikatsarmee_d_prozentsatz * $synarmeefaktor < $syndikatsarmee_d[offspecs]) {
					$syndikatsarmee_d[offspecs] = $milsum_unterstuetzt_durch_syndikatsarmee_d * $syndikatsarmee_d_prozentsatz * $synarmeefaktor;
				}
				

				// Angriffspunkte

				// Strike Fighter / Titan Sonderschnarchbehandlung
				if ($status[race] == "pbf"):
					if ($milsend[elites2]):
						if ($milsend[elites2] <= ($mildefend[defspecs] + $syndikatsarmee_d[defspecs])): $strike_fighter_op_plus += SF_ANTIRANGERWALLBONUS;
						elseif ($milsend[elites2] > ($mildefend[defspecs] + $syndikatsarmee_d[defspecs])): $strike_fighter_op_plus += SF_ANTIRANGERWALLBONUS * (($mildefend[defspecs] + $syndikatsarmee_d[defspecs]) / $milsend[elites2]);
						endif;
						if ($status_d[race] == "uic") {
							if ($milsend[elites2] <= $mildefend[elites2]): $strike_fighter_op_plus += SF_ANTIRANGERWALLBONUS;
							elseif ($milsend[elites2] > $mildefend[elites2]): $strike_fighter_op_plus += SF_ANTIRANGERWALLBONUS * ($mildefend[elites2] / $milsend[elites2]);
							endif;
						}
						$wardata[elites2][op] += $strike_fighter_op_plus;
					endif;
					if ($milsend[techs]):
						if (($milsend[offspecs] or $syndikatsarmee[offspecs]) && $milsend[techs] * PBF_TITAN_MARINE_SUPPORT_NUMBER <= ($milsend[offspecs] + $syndikatsarmee[offspecs])): $wardata[offspecs][op] += PBF_TITAN_MARINE_SUPPORT_BONUS * ($milsend[techs] * PBF_TITAN_MARINE_SUPPORT_NUMBER) / ($milsend[offspecs] + $syndikatsarmee[offspecs]);
						elseif (($milsend[offspecs] or $syndikatsarmee[offspecs]) && $milsend[techs] * PBF_TITAN_MARINE_SUPPORT_NUMBER >= ($milsend[offspecs] + $syndikatsarmee[offspecs])): $wardata[offspecs][op] += PBF_TITAN_MARINE_SUPPORT_BONUS;
						endif;
					endif;
					if ($status[techs] - $milsend[techs]):
						if (($status[defspecs] - $milsend[defspecs]) >= PBF_TITAN_RANGER_SUPPORT_NUMBER * ($status[techs] - $milsend[techs])): $wardata[techs][dp] += PBF_TITAN_RANGER_SUPPORT_BONUS*PBF_TITAN_RANGER_SUPPORT_NUMBER;
						else: $wardata[defspecs][dp] += PBF_TITAN_RANGER_SUPPORT_BONUS;
						endif;
					endif;
									
					/*if ($status_d['race'] == "sl") {
						$wardata_d[elites][dp] += 2; # Sl Stalker haben gegen BF + 2 dp
					}*/
				endif;
				
				if ($status[race] == "nof"): //hardcoded aus faulheit, gibt der synarmee zu viele vp, irrelevant weil die eh nicht zu 10/4 zählt TODO: umschreiben
					if ($status[elites] - $milsend[elites]):
							if (($status[defspecs] - $milsend[defspecs]) >= 2 * ($status[elites] - $milsend[elites])): $wardata[elites][dp] += 4;
							else: $wardata[defspecs][dp] += 2;
							endif;
					endif;
				endif;
	
				if ($status_d[race] == "pbf"):
						if ($mildefend[techs]):
							if (($mildefend[defspecs] or $syndikatsarmee_d[defspecs]) && $mildefend[techs] * PBF_TITAN_RANGER_SUPPORT_NUMBER <= ($mildefend[defspecs] + $syndikatsarmee_d[defspecs])): $wardata_d[techs][dp] += PBF_TITAN_RANGER_SUPPORT_BONUS*PBF_TITAN_RANGER_SUPPORT_NUMBER;
							elseif (($mildefend[defspecs] or $syndikatsarmee_d[defspecs] ) && $mildefend[techs] * PBF_TITAN_RANGER_SUPPORT_NUMBER >= ($mildefend[defspecs] + $syndikatsarmee_d[defspecs])): $wardata_d[defspecs][dp] += PBF_TITAN_RANGER_SUPPORT_BONUS;
							endif;
						endif;
				endif;

				// NoF

				/*
				// NoF Marine + 1 AP bei 2000 ha erobert, maximal +3 bei 6000
				if ($status['race'] == "nof") {
					if ($milsend[offspecs] or $syndikatsarmee[offspecs]) {
						$temp_total_erobert_bisher = single("select attack_total_won_conquer+attack_total_won_normal from stats where konzernid = $id and round = ".$globals['round']);
						$temp_op_plus = floor($temp_total_erobert_bisher / NOF_MARINE_HA_BARRIER_FOR_OP_PLUS);
						if ($temp_op_plus > NOF_MARINE_MAX_PLUS_OP) $temp_op_plus = NOF_MARINE_MAX_PLUS_OP;
						$wardata[offspecs][op] += $temp_op_plus;
					}
					if ($status_d['race'] == "sl") {
						$wardata_d[elites][dp] += 2; # SL Stalker haben gegen NoF + 2dp
					}
				}
				*/

				/* Ausgebaut Runde 17 - Septemebr 2005
				// Sentinel Actual OP / DP ausrechnen
				if ($status[race] == "uic") {
					if ($milsend[techs]) {
						if ($status[land] > $status_d[land]) {
							$temp_techchange = ( ($status[land] / $status_d[land] * 100) - 100 ) / 10;
							if ($temp_techchange > 6): $temp_techchange = 6; endif;
							$wardata[techs][op] -= $temp_techchange;
						}
						elseif ($status[land] < $status_d[land]) {
							$temp_techchange = ( ($status_d[land] / $status[land] * 100) - 100 ) / 10;
							if ($temp_techchange > 6): $temp_techchange = 6; endif;
							$wardata[techs][op] += $temp_techchange;
						}
					}
				}
				if ($status_d[race] == "uic") {
					if ($mildefend[techs]) {
						if ($status_d[land] > $status[land]) {
							$temp_techchange = ( ($status_d[land] / $status[land] * 100) - 100 ) / 10;
							if ($temp_techchange > 6): $temp_techchange = 6; endif;
							$wardata_d[techs][dp] -= $temp_techchange;
						}
						elseif ($status_d[land] < $status[land]) {
							$temp_techchange = ( ($status[land] / $status_d[land] * 100) - 100 ) / 10;
							if ($temp_techchange > 6): $temp_techchange = 6; endif;
							$wardata_d[techs][dp] += $temp_techchange;
						}
					}
				}
				*/
				/*if ($status_d[race] == "neb") {  //Patriotenrückkehrboni
					if ($mildefend[elites]) {
						$military_away_defender = single("select sum(number) from military_away where user_id = $target");
						if ($military_away_defender > $mildefend[elites]) $wardata_d[elites][dp] += 9;
						else $wardata_d[elites][dp] += $military_away_defender * 9 / $mildefend[elites];
					}
				}*/

				// Stealth Bomber Tarnen ausrechnen
				if ($status[race] == "sl")	{
					if ($milsend[techs])	{
						$zufall = mt_rand(1,10000);
						if ($zufall <= 85 * ($milsend[techs] / array_sum($milsend) * 100)):
							#a("stealth","$zufall <= ".(85 * ($milsend[techs] / array_sum($milsend) * 100)));
							$stealthed = 1;
							$stealthed_col = ', stealthed ';
							$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Dieser Angriff verlief getarnt. Falls Ihr Gegner kein Spy Web hat, weiß er nicht, wer ihn angegriffen hat.";
							$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Dieser Angriff verlief getarnt.";
							if ($sciences_d[glo9]) {
							  $sonstiges_output_d .= " Ihr Spy Web hat den Angreifer jedoch ermittelt. Sie wurden von <b>".$status[syndicate]."(#".$status[rid].")</b> angegriffen.";
							  // Folgender Kommentar im Angreiferbericht ist wichtig, damit der Verteidiger trotzdem das Racherecht angezeigt bekommt
							  $sonstiges_output .= "<!-- SPYWEB war vorhanden -->";
							}
						endif;
					}
				}


				

				foreach ($milsend as $ky => $vl)	{
					$opoints[$ky] = $vl * $wardata[$ky][op];
					a("data angreifer", "$ky - ".$opoints[$ky]." = ".$vl." * ".$wardata[$ky][op]);
				}
				foreach (array("offspecs") as $ky) { // Defspecs mit OP greifen jetzt nicht mehr mit an: Februar 2007 - Runde 27
					if (($ky == "offspecs" or $ky == "defspecs") && $syndikatsarmee[$ky]) {
						$opoints_synarmee['syn_'.$ky] = $syndikatsarmee[$ky] * $wardata[$ky][op];
						a("syndikatsarmee", "$ky - ".$opoints_synarmee['syn_'.$ky]." == offspecs ??");
					}
				}
				if ($status[race] == "nof") { //nof bug gefixt, bei dem carrier manchmal die synarmee im angriff unterstützen by dragon12 R58
					if ($milsend[elites]) {
						if (($milsend[offspecs]) && $milsend[elites] * 2 <= ($milsend[offspecs])) {
							$opoints[offspecs] += $milsend[elites] * 4;
							//historisch wertvolle lösung :D  $wardata[offspecs][op] += 2 * ($milsend[elites] * 2) / ($milsend[offspecs]);
						}
						elseif (($milsend[offspecs]) && $milsend[elites] * 2 >= ($milsend[offspecs])) {
							$opoints[offspecs] += $milsend[offspecs] * 2;
						}
					}
				}
				foreach ($wardata as $ky => $vl)	{
					$opoints_d[$ky] = ($status[$ky] - $milsend[$ky]) * $vl[dp];
					if($status['race'] == 'neb' && $ky == 'techs')
						$opoints_d[$ky] *= 2;
					
					// hier noch für 2:1 Regel berechnen, wieviele angreifende und wieviele verteidigende einheiten es gibt
					if ($milsend[$ky] > 0) {
						if (!($status['race'] == "nof" && $ky=="elites")){
							$count_attacking_units_a += $milsend[$ky];
						} else {
							$count_attacking_carrier_a += $milsend[$ky];
						}
		
					}
					if ($vl['dp'] >= DEFENSE_UNIT_MIN_DP && !($status['race'] == "nof" && $ky=="elites") ) {
						$count_defending_units_a += $status[$ky] - $milsend[$ky];
						//echo"die viecher haben ".$ky.": ".$vl['dp_orig']." sind zu:".($status[$ky] - $milsend[$ky]);
						
					} elseif( $status['race'] == "nof" && $ky=="elites"){
						$count_defending_carrier_a += $status[$ky] - $milsend[$ky];
					}
					
					//echo "angreider:".$count_attacking_units_a." defen:".$count_defending_units_a. " ";
				}
				
				// Berechnung der Gesamtangriffspunkte
				$totalapoints = array_sum($opoints) * ( 1 + $boni_a / 100 );
				$totalapoints_d = array_sum($opoints_d) * ( 1 + $boni_a_d / 100);
				if ($totalapoints_d == 0): $totalapoints_d = 1; endif;
				if ($waffenlagerangriffspunkte && $status[race] == "pbf" && $status[multifunc] >= $waffenlagerangriffspunkte)	{
					$totalapoints += $waffenlagerangriffspunkte;
					$status[multifunc] -= $waffenlagerangriffspunkte;
					$updates .= "multifunc=multifunc-$waffenlagerangriffspunkte,";
				}
				a("totalapoints", $totalapoints." = ".array_sum($opoints)." * ( 1 + ".$boni_a." / 100 )");

				// Hier bis Runde 41 10/4 - Regel
				
				if ($totalapoints_d <= 0 or $totalapoints / $totalapoints_d > 10 / 4) {	# 10/4 - Regel unterschritten
					unset ($ausgabe);
					f("Sie müssen für jeweils 10 aufgebrachte Angriffspunkte mindestens 4 Verteidigungspunkte zu Hause lassen. Mit Ihrer aufgestellten Streitmacht unterschreiten Sie dieses Verhältnis.$zurueck");
					require_once("../../inc/ingame/header.php");

					
					echo $ausgabe;
require_once("../../inc/ingame/footer.php");
	exit();
				}
				
				
				
				// Seit Runde 42 2:1 - Regel für Verhältnis Angriffseinheiten zu Verteidigungseinheiten.
				// Verteidigungseinheiten sind alle Units, die mindestens 7 VP haben
				
				
				
				/*if (($count_attacking_units_a > $count_defending_units_a*2) || ($count_attacking_carrier_a > $count_defending_carrier_a*2)) { // 2:1 - Regel unterschritten
					unset ($ausgabe);
					f("Für je zwei angreifende Einheiten muss mindestens eine Verteidigungseinheit mit mindestens 10VP deinen Konzern beschützen. Mit deiner aufgestellten Streitmacht unterschreitest du dieses Verhältnis.$zurueck");
					
				}
				else { */
				# Ansonsten alles in Ordnung, Angriff wird nun tatsächlich durchgeführt

					## NEB EMP CANNON SPECIAL
					if ($status_d[race] == "neb" && $status_d[techs]) {
						$empcannonsleft = floor($status_d[techs]/2);
						$offensevalues = array();
						foreach ($wardata as $ky => $vl) {
							$offensevalues[$vl[op]] += $milsend[$ky];
							if ($ky == "offspecs") {
								$offensevalues[$vl[op]] += $syndikatsarmee[$ky];
							}
						}
						krsort($offensevalues); // Durch krsort werden einheiten mit max op nach vorne sortiert.
						 
						// Seit Runde 42 hat EMP-Cannon wieder 7 VP und neutralisiert die stärksten angreifer!
						foreach ($offensevalues as $ky => $vl) {
							//if ($ky < 10) continue; // EMP Neutralisiert zuerst Einheiten mit mindestens 10 AP;
							if ($empcannonsleft > $vl) { 
								$opointabzug += $vl * $ky; 
								$empcannonsleft -= $vl; 
								$offensevalues[$ky] -= $vl;
							}
							else { 
								$opointabzug += $empcannonsleft * $ky; 
								$offensevalues[$ky] -= $empcannonsleft; 
								$empcannonsleft = 0; 
								break;
							}
						}
						/*
						if ($empcannonsleft > 0) { // Erst jetzt, wenn noch Cannons übrig sind, wird der Rest neutralisiert
							foreach ($offensevalues as $ky => $vl) {
								if ($empcannonsleft > $vl) { $opointabzug += $vl * $ky; $empcannonsleft -= $vl; }
								else { $opointabzug += $empcannonsleft * $ky; break;}
							}				
						}
						*/
						$totalapoints -= $opointabzug * (1 + $boni_a / 100);
						a("opointabzug", $opointabzug);
					}
					## FALLS SYNDIKATSARMME DABEI, WERDEN HIER JETZT ERST DIE ANGRIFFSPUNKTE DAZUADDIERT, DAMIT SIE NCIHT FÜR 10/4-REGEL GELTEN
					if ($opoints_synarmee): $totalapoints += array_sum($opoints_synarmee) * ( 1 + $boni_a / 100 ); endif;
					// Verteidigungspunkte
					foreach ($mildefend as $ky => $vl)	{
						$dpoints[$ky] = $vl * $wardata_d[$ky][dp];
						a("data verteidiger", "$ky - ".$dpoints[$ky]." = ".$status_d[$ky]." * ".$wardata_d[$ky][dp]);
					}
					foreach (array("defspecs") as $ky) {
						if (($ky == "offspecs" or $ky == "defspecs") && $syndikatsarmee_d[$ky]) {
							$dpoints['syn_'.$ky] = $syndikatsarmee_d[$ky] * $wardata_d[$ky][dp];
							a("syndikatsarmee", "$ky - ".$dpoints['syn_'.$ky]." == defspecs ??");
						}
					}
					
					if ($status_d[race] == "nof") { //nof bug gefixt, bei dem carrier die synarmee unterstützen by dragon12 R58
						if ($mildefend[elites]) {
							if (($mildefend[defspecs]) && $mildefend[elites] * 2 <= ($mildefend[defspecs])) {
								$dpoints[defspecs] += 4 * $mildefend[elites];
							}
							elseif (($mildefend[defspecs]) && $mildefend[elites] * 2 >= ($mildefend[defspecs])) {
								$dpoints[defspecs] += 2 * $mildefend[defspecs];
							}
						}
					}
					$totaldpoints = array_sum($dpoints) * ( 1 + $boni_d / 100 );
					
					a("totaldpoints", $totaldpoints." = ".array_sum($dpoints)." * ( 1 + ".$boni_d." / 100 )");

					if ($totaldpoints < $status_d[land] * 2): $totaldpoints = $status_d[land] * 2; endif;

					$winner = $totalapoints > $totaldpoints ? "a" : "d";
					a("winner", $winner);

					//
					//// Sieger steht fest - einige Preelementare Dinge vorbereiten
					//
					if ($winner == "a")	{
						attackausgabe("win");
						// Gebäudedaten aus DB holen für späteren Gebäudeloss
						$bd = assocs("select name, name_intern, building_id from buildings", "name_intern");
						#$bd[land] = array(name => "Land", name_intern => "land", building_id => 127);
						// Einträge in den Statistiktable
						$updates_stats .= "attack_numberdone_$attacktype=attack_numberdone_$attacktype+1,attack_numberdone_won_$attacktype=attack_numberdone_won_$attacktype+1";
						$updates_stats_d .= "attack_numbersuffered_$attacktype=attack_numbersuffered_$attacktype+1,attack_numbersuffered_lost_$attacktype=attack_numbersuffered_lost_$attacktype+1";
					}
					else {
						attackausgabe("lost");
						// Einträge in den Statistiktable
						$updates_stats .= "attack_numberdone_$attacktype=attack_numberdone_$attacktype+1";
						$updates_stats_d .= "attack_numbersuffered_$attacktype=attack_numbersuffered_$attacktype+1";
					}


					//
					//// VERLUSTE: Berechnen, Aktualisieren;
					//

					//früher erst vor landberechnung benötigt, ab runde 5 dann auch für unit gang bang protection
					$searchtime = $time - 24 * 60 * 60;
					$alreadyattackedbyattacker = 0;
					$alreadyattacked = 0;
					$alreadyattackedbyattacker_defended = 0;
					$alreadyattacked_defended = 0;
					foreach ( assocs("select aid, winner, warattack, done_unter_racherecht from attacklogs where time > $searchtime and did=$target and gbprot = 1 ") as $ky => $vl)	{
						if ($vl[winner] == "a")	{
							if ($vl[aid] == $id): $alreadyattackedbyattacker++;
							else: $alreadyattacked++; endif;
						}
						elseif ($vl[winner] == "d" and ($vl['warattack'] or $vl['done_unter_racherecht']))	{
							if ($vl[aid] == $id): $alreadyattackedbyattacker_defended++;
							else: $alreadyattacked_defended++; endif;
						}
					}
					$privatkrieg = racherecht($target); // racherecht ist in subs.php
					// Bash-Schutz
					list($bash_protection_multiplier,$dummy) = get_bash_protection($target);
					
					//$landgainmultiplier = pow(0.75, (float)$alreadyattacked) * pow(0.4, (float)$alreadyattackedbyattacker); # gangbang prot // Bis Runde 43 aktuell
					$landgainmultiplier = $bash_protection_multiplier;
					
					$unit_gang_bang_protection_modificator = $landgainmultiplier;
					$unit_gang_bang_protection_modificator_fail = pow(0.8, (float)$alreadyattacked_defended) * pow(0.7, (float)$alreadyattackedbyattacker_defended); # unit gang bang protection
					
					
					//if ($inactivity_mode == 2 or $privatkrieg): $landgainmultiplier = 1; $unit_gang_bang_protection_modificator = 1; endif; # falls ziel inaktiv ist, privatkrieg herrscht, oder krieg herrscht und ziel in stärkerer landhälfte (ansonsten ia=4), gibts keine gangbang prot
					/*if ($inactivity_mode == 2 or ($privatkrieg==1) or ($isatwar and $kondata[$target][ia] == 1)): $attacked_temp = $alreadyattacked + $alreadyattackedbyattacker; $landgainmultiplier = pow(0.95, (float) $attacked_temp); $unit_gang_bang_protection_modificator = $landgainmultiplier; endif; # Im Krieg ab Runde 11 andere Regelung mit 5% Schutz pro Angriff*/

					// Basisprozentwerte bestimmen anhand von Rasse und Forschungen
					$pmod = 1; $pmod_d = 1;
					if ($totaldpoints < $totalapoints)
						$pmod = $totaldpoints / $totalapoints;
					elseif ($totalapoints < $totaldpoints)
						$pmod_d = $totalapoints / $totaldpoints;

					$perc -= $msb{9} * $sciences[mil9];
					if ($status[race] == "pbf"){
						$perc -= PBF_LOSS_BONUS;
						//$perc_d += 5 * ($milsend[offspecs] / array_sum($milsend));
					}
					if ($status['race'] == "nof") {
						//$perc_d -= HALO_UNITLOSSMALUS * ($milsend[elites2] / array_sum($milsend));
						if ($status['workshops']) $perc -= WORKSHOP_PROZENT_BONUS * $status['workshops'] / $status['land'] * 100;
					}
					$perc += $msb2nd{12} * $sciences_d{mil12};
					$perc -= $partner[24] * PARTNER_MILLOSSBONUS;
					if ($artefakte[$game_syndikat[artefakt_id]][bonusname] == "mil_unitloss_bonus") $perc -= $artefakte[$game_syndikat[artefakt_id]][bonusvalue];

					$perc_d -= $msb{9} * $sciences_d[mil9];
					$perc_d -= $partner_d[24] * PARTNER_MILLOSSBONUS;
					if ($sciences_d[mil14]): $fog_of_war_lossbonus = 1; else: $fog_of_war_lossbonus = 0; endif;
					$perc_d -= $msb{14} * $fog_of_war_lossbonus;
					if ($status_d[race] == "pbf"): $perc_d -= PBF_LOSS_BONUS; endif;
					if ($status_d[race] == "nof") {
						if ($status_d['workshops']) $perc_d -= WORKSHOP_PROZENT_BONUS * $status_d['workshops'] / $status_d['land'] * 100;
					}
					
					if($status_d['race'] == 'uic')
						$perc_d -= UIC_RW_LOSS_SPECIAL_FULL * $mildefend['elites']/array_sum($mildefend);
					
					$perc_d += $msb2nd{12} * $sciences{mil12};
					if ($artefakte[$syndikat_d[artefakt_id]][bonusname] == "mil_unitloss_bonus") $perc_d -= $artefakte[$syndikat_d[artefakt_id]][bonusvalue];

					$awaytime = $time + $globals[roundtime] * 60 * STANDARDAWAYTIME;	# Awaytime in Sekunden
					if ($sciences_d[mil14] >= 1): $fog_of_war_switch = 1; else: $fog_of_war_switch = 0; endif;
					$bonusawaytime = $msb{4} * $sciences{mil4} + $msb{10} * $sciences{mil10} - $msb3rd[14] * $fog_of_war_switch;
					if ($status[race] == "pbf"): $bonusawaytime += PBF_ATTACK_DURATION_BONUS; endif;
					//if ($status_d['race'] == "nof") $bonusawaytime -= NOF_ENEMY_ATTACKTIME_MALUS;
					$bonusawaytime += $partner[4] * 60 * PARTNER_AWAYTIME; # Partnerschaftsbonus: Angriffszeit um 1h reduziert
					if ($artefakte[$game_syndikat[artefakt_id]][bonusname] == "attack_speed_bonus") $bonusawaytime += $artefakte[$game_syndikat[artefakt_id]][bonusvalue] * 60;
					if ($artefakte[$syndikat_d[artefakt_id]][bonusname] == "attack_speed_malus") $bonusawaytime -= $artefakte[$syndikat_d[artefakt_id]][bonusvalue] * 60;
					$radarbonus = floor($status['radar'] / $status['land'] * 100 / RADAR_BONUS);
					if ($radarbonus > 10) {$radarbonus=10;}
					$bonusawaytime += $radarbonus * 60;
					if ($bonusawaytime < 0): $bonusawaytime = 0; endif;				# Höchstens 20h Angriffsdauer
					if ($bonusawaytime > MAX_ATTACKTIME_REDUCTION * 60): $bonusawaytime = MAX_ATTACKTIME_REDUCTION * 60; endif;	# Maximal 14h Reduzierung bzw. Mindestangriffsdauer von 6h;
					$awaytime -= $globals[roundtime] * ($bonusawaytime);

					$awaytime = get_hour_time($awaytime);

					a("awaytime", $awaytime);
					
					//Runde 58 by dragon12
					//bashschutz fŸr landkleinere
					$smallkonz_multiplier = $status[land]>$status_d[land]  ?  $status_d[land]/$status[land] : 1;
					$smallkonz_multiplier *= $status[land]>$status_d[land]/0.8  ?  pow($status_d[land]/$status[land]/0.8,1.5) : 1;
					
					//historisch wertvoll spinna
					//($status_d[land]/$status[land] < 0.2 ? 0.2 : $status_d[land]/$status[land]) > 1 ? 1 : ($status_d[land]/$status[land] < 0.2 ? 0.2 : $status_d[land]/$status[land]); //runde 52 by christian
					if($privatkrieg !== 1 and !$isatwar){ //runde52
						$smallkon_loss = $smallkonz_multiplier*$smallkonz_multiplier; //runde 52 by christian
					} else {
						$smallkon_loss = 1;
					}
					// In Schleife jeden einzelnen der gesendeten Einheitentypen bearbeiten

					// Angreifer
					foreach ($milsend as $ky => $vl)	{
						$tperc = $perc;
						
						$awaytime_tp = $awaytime;
						/*if ($status[race] == "pbf" and $ky == "elites2") {
							$sf_speedbonus = SF_SPEEDBONUS;
							if ($bonusawaytime / 60 + $sf_speedbonus > MAX_ATTACKTIME_REDUCTION) $sf_speedbonus = MAX_ATTACKTIME_REDUCTION - $bonusawaytime / 60;
							$awaytime_tp -= $sf_speedbonus * 3600;
						}*/

						//if ($status[race] == "uic" and $ky == "elites"){ $tperc += 10;}
						if ($status[race] == "pbf" and $ky == "elites2") { $tperc -= 15; }
						if ($tperc < MAX_LOSS_BONUS): $tperc = MAX_LOSS_BONUS; endif; # Maximal 90% weniger Verluste
						if ($status[race] == "sl" and $ky == "techs" and $stealthed == 1){ $tperc = -100;} # 0% Verluste für gestealthte Bomber
										#elseif ($status[race] == "uic" and $ky == "elites2"){ $tperc -= 40;} # rein theoretisch unwichtig für attacker, aber man kann ja nie wissen

						$milaway[$ky] = floor($vl * ( 1 - ( BASE_UNIT_LOSS_A * ( 1 +  $tperc / 100) * $pmod / 100 )));
						$losses[$ky] = $vl - $milaway[$ky];
						if ($losses[$ky]): $losses_output[$ky] = output_unitloss($wardata[$ky][name], $losses[$ky]); endif;
						if ($milaway[$ky]): $insmilawayvalues .= "(".$wardata[$ky][unit_id].", $id, ".$milaway[$ky].", $awaytime_tp),"; endif;
						$updates .= "$ky=$ky-$vl,";	# Updatestring für Statusupdate zusammenschreiben. Einheiten von Mysql abziehen lassen, keinen absoluten Wert eintragen!
						$status[$ky] -= $losses[$ky]; # Wichtig für neue NW-Berechnung
						a("losses/away $ky", $losses[$ky]."/".$milaway[$ky]);
					}
					if ($insmilawayvalues):
						$insmilawayvalues = chopp($insmilawayvalues);
						$queries[] = "insert into military_away (unit_id, user_id, number, time) values $insmilawayvalues";
					endif;
					a("pmod", $pmod);

						// Verteidiger
					foreach ($mildefend as $ky => $vl)	{
						$tperc = $perc_d;
						//if ($status_d[race] == "uic" and $ky == "elites"){ $tperc += 10;}
						//if ($status_d[race] == "pbf" and $ky == "defspecs") { $tperc += 30; }
						#elseif ($status_d[race] == "sl" and $ky == "techs"){ $tperc = -100; } # 0% Verluste für Bomber in der Defense
						//$tperc *= $smallkon_loss; //Runde52
						if ($tperc < MAX_LOSS_BONUS): $tperc = MAX_LOSS_BONUS; endif; # Maximal 90% weniger Verluste
						$modifier_on_unsuccessful_attack = ($winner == "a") ? 1 : (($isatwar || $privatkrieg)?0.5:0); //Runde52
						$modifier_on_unsuccessful_attack *= $unit_gang_bang_protection_modificator_fail;
						$remain[$ky] = ceil($vl * 
						(1 - 
							( 1 +  $tperc / 100) 
							* ( BASE_UNIT_LOSS_A * $pmod_d / 100) 
							* $modifier_on_unsuccessful_attack
							* $unit_gang_bang_protection_modificator
							* $smallkon_loss
						)
							);
						$losses_d[$ky] = $vl - $remain[$ky];
						
						if ($losses_d[$ky]):
							$losses_output_d[$ky] = output_unitloss($wardata_d[$ky][name], $losses_d[$ky]);

							// ALS ERSTES MILITÄR AUF DEM MARKT TÖTEN
							$markettypes = changetype($ky, 0 ,$status_d[race]);
							$nothing_left = 0;
							$number = 0; $unique_id = 0;
							$templosses = $losses_d[$ky];
							$already_done = array();
							if ($market_mil_d[$ky]) {
								while ($templosses > 0 and !$nothing_left)	{
									list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status_d[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
									if ($number > $templosses): $queries[] =("update market set number=number-".$templosses." where offer_id=".$unique_id); $templosses = 0;
									elseif ($number): $templosses -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
									else: $nothing_left = 1;
									endif;
								}
							}
							if ($templosses > 0) {
								$updates_d .= "$ky=$ky-".$templosses.",";
								$status_d[$ky] -= $templosses;
							}
						endif;
						a("losses/remain defender $ky", $losses_d[$ky]."/".$remain[$ky]);

					}

					// Syndikatsarmee abfertigen
					if ($syndikatsarmee_prozentsatz && array_sum($losses)):
						$syndikatsarmeeloss[offspecs] = ceil(array_sum($losses) * ($syndikatsarmee[offspecs] / array_sum($milsend)));
						//if ($losses[defspecs]): $syndikatsarmeeloss[defspecs] = ceil($losses[defspecs] * ($syndikatsarmee[defspecs] / $milsend[defspecs])); endif;
						if ($syndikatsarmeeloss[offspecs] or $syndikatsarmeeloss[defspecs]):
							$queries[] = "update syndikate set ".($syndikatsarmeeloss[offspecs] ? "offspecs=offspecs-".$syndikatsarmeeloss[offspecs]:"").($syndikatsarmeeloss[defspecs] && $syndikatsarmeeloss[offspecs] ? ",":"").($syndikatsarmeeloss[defspecs] ? "defspecs=defspecs-".$syndikatsarmeeloss[defspecs]:"")." where synd_id=".$status[rid];
						endif;
						if ($syndikatsarmeeloss[defspecs] or $syndikatsarmeeloss[offspecs]):$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Die Syndikatsarmee hat Sie in diesem Kampf unterstützt und dabei ".($syndikatsarmeeloss[offspecs] ? "<b>".$syndikatsarmeeloss[offspecs]."</b> Marines":"").($syndikatsarmeeloss[offspecs] && $syndikatsarmeeloss[defspecs] ? " und ":"").($syndikatsarmeeloss[defspecs] ? "<b>".$syndikatsarmeeloss[defspecs]."</b> Ranger":"")." verloren"; endif;
					endif;
					if ($syndikatsarmee_d_prozentsatz && array_sum($losses_d)):
						$syndikatsarmeeloss_d[defspecs] = ceil(array_sum($losses_d) * ($syndikatsarmee_d[defspecs] / array_sum($mildefend)));
						//if ($losses_d[offspecs]): $syndikatsarmeeloss_d[offspecs] = ceil($losses_d[offspecs] * ($syndikatsarmee_d[offspecs] / $mildefend[offspecs])); endif;
						if ($syndikatsarmeeloss_d[defspecs] or $syndikatsarmeeloss_d[offspecs]):
							$queries[] = "update syndikate set ".($syndikatsarmeeloss_d[offspecs] ? "offspecs=offspecs-".$syndikatsarmeeloss_d[offspecs]:"").($syndikatsarmeeloss_d[defspecs] && $syndikatsarmeeloss_d[offspecs] ? ",":"").($syndikatsarmeeloss_d[defspecs] ? "defspecs=defspecs-".$syndikatsarmeeloss_d[defspecs]:"")." where synd_id=".$status_d[rid];
						endif;
						if ($syndikatsarmeeloss_d[defspecs] or $syndikatsarmeeloss_d[offspecs]): $sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Die Syndikatsarmee hat Sie in diesem Kampf unterstützt und dabei ".($syndikatsarmeeloss_d[offspecs] ? "<b>".$syndikatsarmeeloss_d[offspecs]."</b> Marines":"").($syndikatsarmeeloss_d[offspecs] && $syndikatsarmeeloss_d[defspecs] ? " und ":"").($syndikatsarmeeloss_d[defspecs] ? "<b>".$syndikatsarmeeloss_d[defspecs]."</b> Ranger":"")." verloren"; endif;
					endif;

					// Stalker Ressourcendiebstahlspecial verrechnen;
					//stalkerextra($status, $status_d, floor($milaway[offspecs]+$syndikatsarmee[offspecs]));
					//roboticwallextra($losses_d[elites2]);
					phoenixextra(1, $losses[elites2]);
					phoenixextra(2, $losses_d[elites2]);
					if ($winner == "a") {
						//sentinelextra(1, $milaway[techs], array_sum($losses)+array_sum($losses_d)); # 1 für Angreifer
						//sentinelextra(2, $remain[techs], array_sum($losses)+array_sum($losses_d)); # 2 für Verteidiger
					}
					behemothextra(1, $milaway[techs], array_sum($losses)); # 1 für Angreifer
					behemothextra(2, $remain[techs], array_sum($losses_d)); # 2 für Verteidiger

					//
					//// Falls der Angreifer gewonnen haben sollte, wird hier jetzt der Landgain ausgerechnet;
					//
     				if ($winner == "a")	
     				{
						$perc = 0;	// Sammelt alle prozentualen Boni ein, die beim Angriff wirken
						if ($status[race] == "pbf") // BF Landgain Bonus berechnung
						{
							// $perc += PBF_LANDGAIN_BONUS;
							 if ($status['armories'] > 0) 
							 {
								if ($status['armories'] / $status['land'] < 0.1) // Max 10% Bonus;
								{	
									$perc += $status['armories'] / $status['land'] * 100;
								} 
								else 
								{
									$perc += 10;	// Max 10% Bonus
								}
							 }
						}
						
						
						/*if ($status_d[race] == "neb") 
						{
							$perc -= NEB_LANDLOSS_BONUS; // Neb Landloss bonus 15%
						}*/
						
						$perc -= $sciences_d[glo8] * $gsb2nd{8};
						
						if ($sciences_d[mil14] >= 1)
						{
							$fog_of_war_switch2 = 1;
						}  
						else
						{
							$fog_of_war_switch2 = 0;
						}
						
						$perc -= $msb2nd[14] * $fog_of_war_switch2;						
						$perc += $sciences[mil3] * $msb{3};		
						$perc += $sciences[mil10] * MIL10BONUS_LANDGAIN_BONUS;	 //ra gibt landgain boni						
						$perc += $partner[3] * PARTNER_LANDGAINBONUS;	# Partnerschaftsbonus: +5% Landgewinn-Bonus bei erfolgreichem Angriff
						$perc -= $partner_d[20] * PARTNER_LANDLOSSBONUS; # Partnerschaftsbonus: -5% Landverlust bei Angriffen
						$perc -= $landgainmalus; # Wird zurzeit nur oben beim Zusammenrechnen der Boni bzgl. PBF-PBF-Krieg-Landmalus von 15% gesetzt;
						
						/*if($status[race] == "neb") 
						{
							$perc += 20 * ($milsend[offspecs] / array_sum($milsend));
						}*/
						
						/*if($status[race] == "nof") 
						{
							$perc -= HALO_LANDMALUS * ($milsend[elites2] / array_sum($milsend));
						}*/

							$landgain = floor(( ( ( pow((float)$status_d[land], 2.5) ) / ( pow((float)$status[land], 1.5) ) ) * 0.1 ) * ( 1 + ( $perc / 100 )  ));
						
						a("landgain normal vorher", $landgain);

						if ($landgain > $status_d[land] * (MAXLANDGAIN/100)) // Cap-Regelung: Wenn Landgain über maxlandgain liegt dann deckeln 
						{
							$landgain = floor($status_d[land] * (MAXLANDGAIN/100));
						}

						if (	
								$isatwar 
								&&
								$kondata[$target][ia] == 1 
								&& 
								!$inactivity_mode 
								&& 
								(
									$attacktype == "normal" 
									|| 
									$attacktype == "conquer"
								)
							)	
						{
							if (getServertype() == "basic") list ($others_existing, $totalothersyndland) = row("select count(*), sum(land) from status where rid='".$status_d{rid}."' and id != $target and alive = 1 and (createtime + ".PROTECTIONTIME.") < ".$time);
							else list ($others_existing, $totalothersyndland) = row("select count(*), sum(land) from status where rid='".$status_d{rid}."' and id != $target and alive = 1 and inprotection='N'");
							a("daten: andere existente - anderes gesamtland", $others_existing ." - ". $totalothersyndland);
						}

						if (
								$isatwar or $privatkrieg===1
							)	
						{
							if ($alreadyattacked+$alreadyattackedbyattacker == 0)
							{
								$landgain = intval($landgain * 1.25);
							}
							if ($alreadyattacked+$alreadyattackedbyattacker >= 2)
							{
								$landgain = intval($landgain * 0.75);
							}
						} else {
							$landgain = intval($landgain * $landgainmultiplier);	
						}
						
						if (
								$isatwar
							)	
						{
							$landgain = intval($landgain); //dragon12 evtl bug fix	
						}

						/*if ($landgain < 10) // Minimum Landgain 10 ohne gang bang protection //runde 52
						{
							$landgain = 10;
						}*/
						
												
						
						if ($landgain < $status_d[land] * 0.01)
						{
							$one_prozent_activated = $landgain+1;  /* +1, falls $landgain 0 ist, wegen boolean-Abfrage weiter unten*/ 
							//$landgain = round($status_d[land] * 0.01); // Immer mindestens 1% Land vom Target //runde52
						}
						
						/*if ($landgain < 5) // egal was ist, immer mindestens 5 land; //runde52
						{
							$landgain = 5; 
						}*/
						if($privatkrieg!=1){ //runde58 angepasst by dragon (kein direktes racherecht -> $smallkonz_multiplier kommt dazu
							//echo"alrganin: ".$landgain."<br>";
							$landgain = intval($smallkonz_multiplier*$landgain); //runde 52 by christian
							//echo"neuganin: ".$landgain."<br>";
							//echo "der hier hat kein rr oder krieg";
						}
						
						$landgain_land_qot_for_loss = $landgain / $status_d[land];
						
						$wts_do = $landgain;

						if ($attacktype == "killspies")
						{
							list($updates_d_add, $killedspies) = killspies(); 
							if (!$killedspies)
							{
								$killedspies = 0;
							}
							$updates_d .= $updates_d_add; 
							$landgain = 0; 
							
							$warfactor = 1;
							if( $isatwar )
							{
								$warfactor = 1.5;
							}
							
							/*if ( $killedspies < ( $status_d[land] * 0.5 * $warfactor ) )
							{
								$gbprot_bez = ",gbprot";
								$gbprot_value = ",'0'";
							}*/
						}
						
						if ($attacktype == "siege") // Wenn Belagerungsangriff wird Landgain auf Gebs umgerechnet
						{
							$destroyed_buildings = floor($landgain * 1.5); //Änderung R46 - vorher: ($isatwar && !$inactivity_mode ? 1.75 : 1.5)
							$landgain = 0;
							//echo"gebget: ".$destroyed_buildings;
						}
						
						if ($attacktype == "conquer")
						{
							$savelandgain = $landgain; 
							if (!$one_prozent_activated or ($one_prozent_activated - 1) *2 > $landgain) 
							{
								$landgain = intval($landgain * 1.5); 
							}
							
							$enemyfreeland = get_free_land($status_d); 
							
							if ($enemyfreeland < $landgain)
							{
								if ($enemyfreeland >= 0)
								{
									$landgain = $enemyfreeland; 
								}
								else
								{
									$landgain = 0; 
								}
							}
							if ($savelandgain*0.75 >= $landgain)
							{
								$gbprot_bez = ",gbprot"; 
								$gbprot_value = ",'0'"; 
							}
							$landgain = $status_d['land'] - $landgain < 400 ?  $status_d['land'] - 400 : $landgain; //runde52
							$wts_do = $landgain;//wartanks zerstören nicht mehr als erobert wird (Runde 59 by dragon12)
							
						}
						$landgain = $status_d['land'] - $landgain < 400 ?  $status_d['land'] - 400 : $landgain; //runde52
						$landgrab = $landgain;	# Landgrab ist das was der Gegner tatsächlich verliert (außerhalb von Krieg) bzw. das was ohne Abstriche erobert worden wäre (Krieg)
						// Anfangen mit Zerstören >:)
						
						getinsertlandgain(); # Legt $insertlandgain fest; ab hier wurde $landgain entsprechend der Angriffstypen endgültig festgelegt
						
						if( $attacktype == "siege" )
						{
							$this_vergleichswert = $destroyed_buildings;
						}
						else if( $attacktype == "killspies" )
						{
							$this_vergleichswert =$killedspies;
						}
						else
						{
							$this_vergleichswert =$insertlandgain;
						}
						
						$wartank_destroyed_buildings = wartankextra($status, $status_d, $milsend[elites], $wts_do);
						
						// Gibt auf jeden Fall Probleme wenn man irgendwann mal Einheiten von BF und SL gleichzeitig haben kann (Wartanks und Headhunter)
						if ($wartank_destroyed_buildings > 0)
						{
							$unitspecial_number = $wartank_destroyed_buildings;
						} 
						
						$updates_d .= headhunterextra($milsend[elites2]);

						/*
						if ($isatwar and $kondata[$target][ia] == 1 and !$inactivity_mode && ($attacktype == "normal" or $attacktype == "conquer"))	{
							list ($others_existing, $totalothersyndland) = row("select count(*), sum(land) from status where rid='".$status_d{rid}."' and id != $target and alive = 1 and ((createtime+".PROTECTIONTIME.")<$time)");
							a("daten: andere existente - anderes gesamtland", $others_existing ." - ". $totalothersyndland);
						}
						*/
						if (
								$isatwar 
								&&
								$kondata[$target][ia] == 1 
								&&
								!$inactivity_mode 
								&& 
								$others_existing > 0 
								&& 
								$totalothersyndland > $status_d{land} 
								&& 
								(
									$attacktype == "normal" 
									||
									$attacktype == "conquer"
								)
							)	
						{
							$landgain_adjusted = ceil($landgain * (1-KRIEG_LANDVERLUST_VERTEILUNG_GEGNERSYN)); // Da bei passivelangverlusten nur 50% vom eigentlichen Opfer abgezogen wird, wird das ganze hier verrechnet
														
							$landverlust_gegnersyn = $landgain - $landgain_adjusted; // Berechnung, wieviel Land der Gegner verliert
							
							if ($landgain_adjusted >= $status_d[land])
							{
								$killed = 1; 
								$landgain_adjusted = $status_d[land]; 
								$insertlandgain = $landgain; 
								$landgain = floor($landgain_adjusted / KRIEG_LANDVERLUST_VERTEILUNG); // WTF? Macht kein Sinn und Konstante existiert nicht
							}
							
							//$caseofwar = " ($landgain)"; //raus wegen kein passiv land
							
							$landdiff = floor($landgain * KRIEG_LANDVERLUST_VERTEILUNG_OWNSYN); // Wieviel land erhält der Angreifer weniger (welches aufs eigene Syn verteilt wird)
							
							warattack($status_d, $target, $landgain);
							
							$others_existing_own_syndikat = assocs("select id, land from status where rid='".$status[rid]."' and id != '$id' and alive = 1 and inprotection='N'", "id");
							$others_existing_other_syndikat = assocs("select id, land from status where rid='".$status[rid]."' and id != '$id' and alive = 1 and inprotection='N'", "id");
							
							if (count($others_existing_own_syndikat) > 0)
							{
								$insertlandgain = ceil($landgain * (1-KRIEG_LANDVERLUST_VERTEILUNG_OWNSYN)); 
								//war_divide_on_syndicate($landdiff, $others_existing_own_syndikat); // r44: Abschaffung des passiv-gains  // r45 wieder eingeführt ;)
								$insertlandgain = adjust_basic_landgrenze($insertlandgain, $id);
							}
							
							$queries[] = "update wars set $war_land_updateaction=$war_land_updateaction+$landgain,$war_land_updateaction_own=$war_land_updateaction_own+$landgain where war_id=$war_id";
							$updates_d .= kill_land_and_buildings($target, $status_d, $landgain_adjusted, $wartank_destroyed_buildings);
							$updates_d .= "land=land-$landgain_adjusted,"; $status_d[land] -= $landgain_adjusted;	$landplus_for_attacklogs = $landgain_adjusted;
							wartankausgabe($wartank_destroyed_buildings);
							attackausgabe("kampfergebnisse_win_war");
							//if (!$killed): message_for_defender("attacklost_war"); endif; // wird jetzt weiter unten nach der Jobs-Abhandlung gemacht, wegen Anzeige des Auftraggebers
							

							$warattack_col = " warattack,";	# WICHTIG für Eintrag in Attacklogstable falls KRIEG ist
							$warattack_value = " '1',";
						}
						else	
						{
							if ($landgain >= $status_d[land]): $killed = 1; $insertlandgain = floor(($insertlandgain / $landgain) * $status_d[land]); $landgain = $status_d[land];  endif;
							$updates_d .= kill_land_and_buildings($target, $status_d, $landgain, $wartank_destroyed_buildings);
							$updates_d .= "land=land-$landgain,"; $status_d[land] -= $landgain;		$landplus_for_attacklogs = $landgain;
							wartankausgabe($wartank_destroyed_buildings);
							attackausgabe("kampfergebnisse_win");
							//if (!$killed): message_for_defender("attacklost"); endif; // wird jetzt weiter unten nach der Jobs-Abhandlung gemacht, wegen Anzeige des Auftraggebers
						}

						// Gucken ob Auftragsangriff

						$jobdata = assoc("select user_id, type, param, number, money, energy, metal, sciencepoints, accepttime, id, inserttime, onlinetime, anonym, normgain from jobs where target_id = $target and acceptor_id = $id");
						if (false) // nur zum Testen!! 
						{
							foreach ($jobdata as $ky => $vl) 
							{
								echo "$ky => $vl<br>";
							}
							echo "<br>Attacktype: $attacktype";
						}
						
						if ($jobdata) 
						{
							$attacktype_remember = $attacktype;
							if ($attacktype == "conquer")// Attacktype temporär nicht zw. Eroberung und Normal unterscheiden
							{
								//$attacktype = "normal";
							}
							if ($jobdata[type] == $attacktype || ($jobdata[type] == "conquer" && "normal" == $attacktype)) 
							{
								$ausgabe_forward = $jobdata[user_id];

								//Message für den Verteidiger anpassen;
								$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Dieser Angriff war ein Auftragsangriff.</b>";
								if ($jobdata['anonym']) 
								{
									$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Über die Herkunft des Auftraggebers lässt sich leider nichts feststellen, da der Auftrag anonym eingestellt wurde.";
									// Ueber diesen String wird weiter oben die Anzeige des Racherechts geregelt.
									// Da nur der Angreiferbereicht gespeichert wird, muss die Ausgabe auch beim Angreifer erfolgen.
									// Durch den HTML-Kommentar bekommt der Angreifer die Meldung nicht zu sehen.
									$sonstiges_output .= "<!-- dies war ein anonymer Auftrag -->";
									$ausgabe .= "<!-- dies war ein anonymer Auftrag -->";
								} 
								else 
								{
									$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Der Auftraggeber konnte als <b>".single("select syndicate from status where id = ".$jobdata[user_id])." (#".single("select rid from status where id = ".$jobdata[user_id]).")</b> identifiziert werden.";
								}
								$attacklogs_insert_client_id = $jobdata[user_id];
								
								$attacklogs_insert_client_rid = single("select rid from status where id = ".$jobdata[user_id]);
								
								$ausgabe_auftrag = "<br><b>Dieser Angriff war ein Auftragsangriff.";
								
								if	(
										$attacktype == "normal" 
										|| 
										$attacktype == "conquer"
									) 
								{
									$ausgabe_auftrag .= " <br>Sie erhalten deshalb kein Land.";
								}
								else
								{
									$ausgabe_auftrag .= "";
								}
								$ausgabe_auftrag .= "</B><br>";
								
								// Den gesamte bisher beim Auftrag erreichten Landgein berechnen
								
								$tempsinglestring = "select sum(landgain)+sum(unitspecialgain) from attacklogs where did = $target and aid = $id and time > " . $jobdata[accepttime] . " and type in (";
								
								switch($attacktype)
								{
									case "normal":
										$tempsinglestring .= "1,3";
										break;
										
									case "siege":
										$tempsinglestring .= "2";
										break;
									
									case "conquer":
										$tempsinglestring .= "3";
										break;
									
									default:
										$tempsinglestring .= "4";
										break;
								}

								$tempsinglestring .= ")";

								$totalbisher = single($tempsinglestring);
								
								$unitspecialgainbisher = 0;
								
								// Wartanks für siege aus NICHT-Siege-Angriffen (siege-Angriffe wurden oben bereits erfasst)
								if ($attacktype == "siege") 
								{
									$unitspecialgainbisher = single("select sum(unitspecialgain) from attacklogs where did = $target and aid = $id and time > ".$jobdata[accepttime]." and type not in (2)");
									$unitspecial_number_IF_APPROPRIATE = $unitspecial_number;
								}

								// Headhunter für spykill aus NICHT-Spykill-Angriffen (Spykill-Angriffe wurden oben bereits erfasst)
								if ($attacktype == "killspies") 
								{
									$unitspecialgainbisher = single("select sum(unitspecialgain) from attacklogs where did = $target and aid = $id and time > ".$jobdata[accepttime]." and type not in (4)");
									$unitspecial_number_IF_APPROPRIATE = $unitspecial_number;
								}

								if ($attacktype == "normal" or $attacktype == "conquer")
								{
									$this_vergleichswert = $landgrab;
									$totalbisher = single("select sum(landgrab) from attacklogs where did = $target and aid = $id and time > ".$jobdata[accepttime]." and type in (".($attacktype == "normal" ? "1,3":($attacktype == "siege" ? "2":($attacktype == "conquer" ? "3" : "4"))).")");
								}

								if ($jobdata[param] <= ($this_vergleichswert + $unitspecial_number_IF_APPROPRIATE) + ($totalbisher + $unitspecialgainbisher))
								{
									$auftrag_erfolgreich = 1;
									$ausgabe_auftrag .= "<br><b>Sie haben mit diesem Angriff den Auftrag erfolgreich erfüllt und erhalten als Belohnung ".pointit($jobdata[money])." Cr</b><br><br>";
									$queries[] = "update status set money = money + ".($jobdata[money] ? $jobdata[money] : 0).", metal = metal + ".($jobdata[metal] ? $jobdata[metal] : 0).", energy = energy + ".($jobdata[energy] ? $jobdata[energy] : 0).", sciencepoints = sciencepoints + ".($jobdata[sciencepoints] ? $jobdata[sciencepoints] : 0)." where id = $id";
									$status[money] += $jobdata[money];
									$status[energy] += $jobdata[energy];
									$status[metal] += $jobdata[metal];
									$status[sciencepoints] += $jobdata[sciencepoints];
									if ($jobdata[number] > 1) 
									{
										$queries[] = "update jobs set number = number - 1, acceptor_id = 0, accepttime = 0 where id = ".$jobdata[id];
									}
									else 
									{ 
										$queries[] = "delete from jobs where id = ".$jobdata[id]; 
									}
									$finshtime = mktime(date('G'), 0, 0, date('m'), date('d'), date('Y'))+3600;
									$queries[] = "insert into jobs_logs (user_id, acceptor_id, target_id, type, param, money, energy, metal, sciencepoints, inserttime, onlinetime, accepttime, anonym, finishtime, success) values	($jobdata[user_id], $id, $target, '$attacktype', $jobdata[param], $jobdata[money], $jobdata[energy], $jobdata[metal], $jobdata[sciencepoints], $jobdata[inserttime], $jobdata[onlinetime], $jobdata[accepttime], $jobdata[anonym], $finshtime, 1)";
									if ($attacktype == "normal" or $attacktype == "conquer") 
									{
										$auftraggeberland = single("select land from status where id = ".$jobdata[user_id]);
										if ($auftraggeberland > $status[land] || TRUE) // OR TRUE ergänzt Runde 26 Februar 2007 - Änderung am AM
										{ 
											if ($totalbisher) 
											{
												list($firstattack_landgain, $d_land) = row("select landgain, dland from attacklogs where aid = $id and did = $target and time > ".$jobdata[accepttime]." order by time asc limit 1");
											}
											else 
											{
												$firstattack_landgain = $this_vergleichswert;
												$d_land = $status_d[land];
												if ($landgain_adjusted)
												{
													 $d_land += $landgain_adjusted; 
												}
												else
												{
													 $d_land += $landgain; 
												}
											}
											
											/* alt (inok1989 24.12.2012)
											$landgain_auftraggeber = floor(( ( ( pow((float)$d_land, 2.5) ) / ( pow((float)$auftraggeberland, 1.5) ) ) * 0.1 ) * ( 1 + ( 0 / 100 )  ));
											if ($landgain_auftraggeber > $d_land * (MAXLANDGAIN/100))
											{
												$landgain_auftraggeber = intval($d_land * (MAXLANDGAIN/100));
											}
											if($auftraggeberland > $d_land){
												$smaller_land_factor = $d_land / $auftraggeberland;
												$smaller_land_factor = $smaller_land_factor < 0.2 ? 0.2 : $smaller_land_factor;
												$smaller_land_factor = $smaller_land_factor > 1 ? 1 : $smaller_land_factor;
												$landgain_auftraggeber = intval($landgain_auftraggeber * $smaller_land_factor);
											} */
											
											// Da nur bei direktem Racherecht möglich erhält man Land nach der RR-Formel für den Landgain
											$landgain_auftraggeber = floor(( ( ( pow((float)$d_land, 2.5) ) / ( pow((float)$auftraggeberland, 1.5) ) ) * 0.1 )*1.25);
											if ($landgain_auftraggeber > $d_land * (MAXLANDGAIN_RR/100))
											{
												$landgain_auftraggeber = intval($d_land * (MAXLANDGAIN_RR/100));
											}
											
											/* Runde 52 Mingain weg by Christian
											if ($landgain_auftraggeber < 10) # Minimum Landgain 10 ohne gang bang protection
											{
												$landgain_auftraggeber = 10;
											}
											//$landgain_auftraggeber = floor($landgain_auftraggeber * pow(0.75, (float)$alreadyattacked));
											if ($landgain_auftraggeber < $d_land * 0.01) // Immer mindestens 1% Land vom Target;
											{
												$landgain_auftraggeber = round($d_land * 0.01);
											}
											if ($landgain_auftraggeber < 5) // egal was ist, immer mindestens 5 land;
											{
												$landgain_auftraggeber = 5; 
											} */
											
											if( $attacktype_remember == "conquer" and FALSE) // Sonst kann man sich Land zu 35% holen, Juli 2007
											{
												$savelandgain_auftraggeber = $landgain_auftraggeber;
												$landgain_auftraggeber *= 2;
												$enemyfreeland = $enemyfreeland; /* wurde oben ja schon berechnet */
												if ($enemyfreeland < $landgain_auftraggeber)
												{
													if ($enemyfreeland >= 0)
													{
														$landgain_auftraggeber = $enemyfreeland;
													}
													else
													{
														//$landgain_auftraggeber = 0;
													}
												}
											}
           								/* Rausgenommen Runde 26 -- 19. Februar 2007
											if ($firstattack_landgain == 0) {
												$landinsert_auftrag = 0;
											} else { $landinsert_auftrag = floor($landgain_auftraggeber / $firstattack_landgain * ($this_vergleichswert + $totalbisher)); }
											
           								*/
											$landinsert_auftrag = $landgain_auftraggeber;
											
											if ($landinsert_auftrag > $this_vergleichswert + $totalbisher)
											{
												$landinsert_auftrag = $this_vergleichswert + $totalbisher;
											}
											
										}
										else 
										{
											$landinsert_auftrag = floor($this_vergleichswert + $totalbisher);
										}
										if($jobdata[type] == 'conquer'){ // bei Standardaufträgen bekommt das Land der Steller
										//$landinsert_auftrag = getinsertlandgain($auftraggeberland, $landinsert_auftrag);
										//$landinsert_auftrag = $jobdata[normgain] < $landinsert_auftrag ?  $jobdata[normgain] : $landinsert_auftrag;
										
										
										$buildinsertintos .= "('land', '".$jobdata[user_id]."', '$landinsert_auftrag', '".get_hour_time($time+20*3600)."', '127'),";
										}
									}
								}
							} 
							else 
							{ 
								$nojobattack = 1;
							}
							
							$attacktype = $attacktype_remember; // Attacktype wieder zurücksetzen
						}
						
						if (!$jobdata or $nojobattack) 
						{
							// Land in Bau eintragen; Abfrage nach $landgain, da bei Belagerung $landgain nachträglich auf 0 gesetzt wird
							$hours_for_landgain = HOURS_FOR_LANDGAIN;
							/*if ($status[race] == "neb") 
							{
								$hours_for_landgain = NEB_HOURS_FOR_LANDGAIN;
							}*/
							
							if ($landgain)
							{
								$buildinsertintos .= "('land', '$id', '$insertlandgain', '".get_hour_time($time+$hours_for_landgain*3600)."','127'),";
							}
						}

						if (
								$isatwar 
								&&
								$kondata[$target][ia] == 1 
								&&
								!$inactivity_mode 
								&& 
								$others_existing > 0 
								&& 
								$totalothersyndland > $status_d{land} 
								&& 
								(
									$attacktype == "normal" 
									|| 
									$attacktype == "conquer"
								)
							)	
						{
							if (!$killed)
							{
								message_for_defender("attacklost_war");
							}
						} 
						else 
						{
							if (!$killed)
							{
								message_for_defender("attacklost");
							}
						}


						// Eintrag in Statistik Table
						a("destroyed_buildings", $destroyed_buildings);
						
						$largestgrab = single("select attack_largest_won_$attacktype from ".$globals[statstable]." where konzernid=$id and round=".$globals[round]);
						
						// $this_vergleichswert wird in zweile 1438 definiert und bezeichnet den Wert von $landgain bzw. $destroyed_buildings, je nach Angriffstyp
						if ($largestgrab > $this_vergleichswert)
						{
							$updates_stats .= ",attack_total_won_$attacktype=attack_total_won_$attacktype+$this_vergleichswert";
						} 
						else
						{
							$updates_stats .= ",attack_total_won_$attacktype=attack_total_won_$attacktype+$this_vergleichswert,attack_largest_won_$attacktype=$this_vergleichswert";
						}
						
						// Alle Sachen von sämtlichen Beteiligten aus Buildings-Table rauslöschen außer Land
						if ($builddeletefroms)
						{
							$builddeletefroms = chopp($builddeletefroms);
							$queries[] = "delete from build_buildings where building_id != 127 and user_id in ($builddeletefroms)";
						}

						// Übrig gebliebene Buildingeinträge wieder reinschreiben
						if ($buildinsertintos)
						{
							$buildinsertintos = chopp($buildinsertintos);
							$queries[] = "insert into build_buildings (building_name, user_id, number, time, building_id) values $buildinsertintos";
						}

						// CNN schreiben/ Aktuelles
						if ($status{rid} == $status_d{rid})
						{
							if ($attacktype == "normal" || $attacktype == "conquer")
							{
								$cnn = "<b>".$status[syndicate]."</b> <span class=highlightAuftableInner>siegte</span> im Kampf gegen <b>".$status_d[syndicate]."</b> und konnte <b class=highlightAuftableInner>$insertlandgain</b> Land übernehmen!";
							} 
							else if ($attacktype == "siege")
							{
								$cnn = "<b>".$status[syndicate]."</b> <span class=highlightAuftableInner>siegte</span> im Kampf gegen <b>".$status_d[syndicate]."</b> und konnte während der Belagerung <b class=highlightAuftableInner>$destroyed_buildings</b> Gebäude zerstören!";
							} 
							else if ($attacktype == "killspies")
							{
								$cnn = "<b>".$status[syndicate]."</b> <span class=highlightAuftableInner>siegte</span> im Kampf gegen <b>".$status_d[syndicate]."</b> und konnte <b class=highlightAuftableInner>".pointit($killedspies)."</b> Spione zerstören!";
							}
						}
						else	
						{
							$cnn_a = "<b>".$status[syndicate]."</b> <font class=gruenAuftableInner>siegte</font> im Kampf gegen <b>".$status_d[syndicate]."(#$rid)</b> und konnte ".($attacktype == "siege" ? "während der Belagerung ":"")."<font class=gruenAuftableInner><b>".($attacktype == "siege" ? $destroyed_buildings:($attacktype == "killspies" ? pointit($killedspies):$insertlandgain.$caseofwar))."</b></font> ".($attacktype == "conquer" ? "unbebautes ":"").($attacktype == "siege" ? " Gebäude zerstören!":($attacktype == "killspies" ? " Spione zerstören!":"Land übernehmen!"));
							$cnn_d = "<b>".$status_d[syndicate]."</b> <font class=achtungAuftableInner>unterlag</font> im Kampf gegen <b>".($stealthed ? "Unbekannt":$status[syndicate])."(#".($stealthed ? "???":$status[rid]).")</b> und verlor ".($attacktype == "siege" ? "während der Belagerung ":"")."<font class=achtungAuftableInner><b>".($attacktype == "siege" ? $destroyed_buildings:($attacktype == "killspies" ? pointit($killedspies):$insertlandgain.$caseofwar))."</b></font> ".($attacktype == "conquer" ? "unbebautes ":"").($attacktype == "siege" ? " Gebäude!":($attacktype == "killspies" ? " Spione!":"Land an den Feind!"));
						}

						// Falls Konzern gekillt wurde

						if ($killed)	{
							kill_den_konzern($target, "kampf");
							attackausgabe("enemy killed");

							// CNN NEU SCHREIBEN

							if ($status{rid} == $status_d{rid})	{
								$cnn = "<b>".$status[syndicate]."</b> <font class=highlightAuftableInner>siegte</font> im Kampf gegen <b>".$status_d[syndicate]."</b> und konnte <font class=highlightAuftableInner><b>$insertlandgain</b></font> Land übernehmen!<br><font class=highlightAuftableInner><b>".$status_d[syndicate]."</b> wurde durch diesen Angriff zerstört!</font>";
							}
							else	{
								$cnn_a = "<b>".$status[syndicate]."</b> <font class=gruenAuftableInner>siegte</font> im Kampf gegen <b>".$status_d[syndicate]."(#$rid)</b> und konnte <font class=gruenAuftableInner><b>$insertlandgain</b></font> Land übernehmen!<br><font class=gruenAuftableInner><b>".$status_d[syndicate]."</b> wurde durch diesen Angriff zerstört!</font>";
								$cnn_d = "<b>".$status_d[syndicate]."</b> <font class=achtungAuftableInner>unterlag</font> im Kampf gegen <b>".($stealthed ? "Unbekannt":$status[syndicate])."(#".($stealthed ? "???":$status[rid]).")</b> und verlor <font class=achtungAuftableInner><b>$insertlandgain</b></font> Land an den Feind!<br><font class=achtungAuftableInner><b>".$status_d[syndicate]."</b> wurde durch diesen Angriff zerstört!</font>";
							}
						}
					}
					else	{ ## Winner == DEFENDER
						attackausgabe("kampfergebnisse_loss");
						message_for_defender("attackwon");

						// CNN schreiben
						
						if ($status{rid} == $status_d{rid})	
						{
							$cnn = "<b>".$status[syndicate]."</b> kämpfte gegen <b>".$status_d[syndicate]."</b>, <font class=highlightAuftableInner>wurde</font> jedoch <font class=highlightAuftableInner>zurückgeschlagen</font>!";
						}
						else	
						{
							$cnn_a = "<b>".$status[syndicate]."</b> griff <b>".$status_d[syndicate]."(#$rid)</b> an, <font class=achtungAuftableInner>wurde</font> jedoch leider <font class=achtungAuftableInner>zurückgeschlagen</font>!";
							$cnn_d = "<b>".$status_d[syndicate]."</b> wurde von <b>";
							if($stealthed)
							{
								$cnn_d .= "Unbekannt(#???)";
							}
							else
							{
								$cnn_d .= $status[syndicate] . "(#" . $status[rid] . ")";
							} 
							$cnn_d .= "</b> angegriffen, <font class=gruenAuftableInner>konnte</font> den Angriff jedoch <font class=gruenAuftableInner>erfolgreich zurückschlagen</font>!";
						}
					}

					// CNN vorbereiten
					if ($status{rid} == $status_d{rid})	
					{
						$queries[] = "insert into towncrier (time, rid, message,kategorie) values ($time, ".$status{rid}.", '$cnn',0)";
					}
					else	
					{
						$queries[] = "insert into towncrier (time, rid, message,kategorie) values ($time, ".$status[rid].", '$cnn_a',0), ($time, ".$status_d[rid].", '$cnn_d',0)";
					}

					if ($insertintomessages)
					{
						$insertintomessages = chopp($insertintomessages);
						$queries[] = "insert into message_values (id, user_id, time, werte) values $insertintomessages";
					}

					$queries[] = "update ".$globals[statstable]." set $updates_stats where konzernid=$id and round=".$globals[round];
					
					$queries[] = "update ".$globals[statstable]." set $updates_stats_d where konzernid='$target' and round=".$globals[round];
					
					$updates = chopp($updates);
					
	                $status{nw} = nw($status{id});
	                
					$queries[] = "update status set $updates,nw='".$status[nw]."' where id=$id";
					
					if ($updates_d and !$killed)
					{
						$updates_d = chopp($updates_d);
	               		$status_d{nw} = nw($status_d{id});
						$queries[] = "update status set $updates_d,nw='".$status_d[nw]."' where id=$target";
						if ($status_d[send_info_mails][1] && !isonline($target)) {
							$wentoffline = single("select gueltig_bis from sessionids_actual where user_id = $target");
							if (!$wentoffline) {
								$wentoffline = single("select gueltig_bis from sessionids_safe where user_id = $target order by gueltig_bis desc limit 1");
							}
							if ($time - single("select time from attacklogs where did = $target and time > $wentoffline order by time desc limit 1") > 10 * 60) {
								list($username, $vorname, $nachname, $email) = row("select username, vorname, nachname, email from users where konzernid = $target");
								$betreff = "Angriffsbenachrichtigung";
								$nachricht = "Hallo ".(($vorname && $nachname) ? "$vorname $nachname" : "$username").",\nIhr Konzern wurde soeben angegriffen".($winner == "a" ? " und konnte den Angriff leider nicht abwehren":", konnte den Angriff jedoch glücklicherweise abwehren").".\n\nWeitere Angriffe innerhalb der nächsten 10 Minuten auf Ihren Konzern werden keine zusätzlichen Benachrichtigungen zur Folge haben, damit Ihr Postfach nicht unnötig gefüllt wird.\n\nSie erhalten diese E-Mail, weil Sie unter Optionen angegeben haben, bei Kriegserklärungen, die Ihr Syndikat betreffen, informiert zu werden, falls Sie nicht eingeloggt sind.\n\nViel Spaß weiterhin beim Spielen, wünscht Ihnen Ihr\nSyndicates-Team";
								sendthemail($betreff, $nachricht, $email, (($vorname && $nachname) ? "$vorname $nachname" : "$username"));
							}
						}
					}

					foreach ($bd as $ky => $vl)	
					{
						$column .= "l$ky,";
						$change .= "'".$bl{$vl[building_id]}."',";
					}
					$spyweb_lvl = single('select level from usersciences where user_id = '.$target.' and name = \'glo9\'');
					$queries[] = "INSERT INTO attacklogs (type,aid, did,arid, drid, client_id, client_rid, job_anonym, time, arace, drace, do, dd, de1, de2, dt, ddeftowers, ao, ad, ae1, ae2, at, aofftowers, apoints, dpoints, aland, dland, winner,$ginactive_col$warattack_col done_unter_racherecht, aol, adl, ae1l, ae2l, atl, dol, ddl, de1l, de2l, dtl, $column lfreeland, landgain, landgrab, unitspecialgain, bericht".$gbprot_bez." $stealthed_col, spyweb_lvl) 
						VALUES ('".($attacktype == "normal" ? "1":($attacktype == "siege" ? "2":($attacktype == "conquer" ? "3" : "4")))."','$id', '$target', '".$status{rid}."', '".$status_d{rid}."', '$attacklogs_insert_client_id','$attacklogs_insert_client_rid', '".($jobdata?$jobdata['anonym']:0)."', '$time', '".$status[race]."', '".$status_d[race]."', '".$mildefend[offspecs]."', '".$mildefend[defspecs]."', '".$mildefend[elites]."', '".$mildefend[elites2]."', '".$mildefend[techs]."', '".$status_d{deftowers}."', '".$milsend[offspecs]."', '".$milsend[defspecs]."', '".$milsend[elites]."','".$milsend[elites2]."','".$milsend[techs]."', '".$status{offtowers}."', '$totalapoints', '$totaldpoints','".$status{land}."', '".($status_d{land}+$landplus_for_attacklogs)."', '$winner',$ginactive_value$warattack_value '".(($privatkrieg && !$warattack_value) ? "1":"0")."','".$losses[offspecs]."', '".$losses[defspecs]."', '".$losses[elites]."','".$losses[elites2]."','".$losses[techs]."', '".$losses_d[offspecs]."', '".$losses_d[defspecs]."', '".$losses_d[elites]."', '".$losses_d[elites2]."','".$losses_d[techs]."', $change '$freelandloss', '".($attacktype == "siege" ? $destroyed_buildings:($attacktype == "killspies" ? $killedspies:$insertlandgain))."', '".$landgrab."', '$unitspecial_number','".addslashes($ausgabe)."'".$gbprot_value." ".($stealthed_col ? ", '$stealthed'" : '').", '".$spyweb_lvl."')";
					$queries[] = "update status set underattack=0 where id=$target";
				//}
			}
		}
		else 
		{ 
			f("Diesen Konzern können Sie nicht angreifen!: <font class=gruenAuftableInner>[".transformfehlercode($kondata[$target][ia])."]</font>");
		}
	}
	
	if ($ausgabe_forward) 
	{
		if ($auftrag_erfolgreich) 
		{
			$premessage = "Der angenommene Auftrag wurde erfolgreich ausgeführt".(($attacktype == "normal" or $attacktype == "conquer") ? ". Sie erhalten vom insgesamt eroberten Land <b>".pointit($landinsert_auftrag)." Land</b> (soviel wie Sie selbst erobert hätten)" : "").":";
		} 
		else 
		{
			$premessage = "Der angenommene Auftrag wurde zum Teil erfolgreich ausgeführt:";
		}
		
		$queries[] = "insert into message_values (id, user_id, time, werte) values (44, $jobdata[user_id], $time, '".addslashes("$premessage<br><br>$ausgabe")."')";
		$ausgabe = $ausgabe_auftrag . $ausgabe;
	}
	db_write($queries);
	
	if($war_id)
		warCheckAndHandle($war_id);	
	
}
else 
{ 
	f("Sie haben den Urlaubsmodus aktiviert. In der Zeit bis zum Urlaubsbeginn können Sie keine Angriffe durchführen."); 
}
}
}
else { f("Das Angriffsmodul ist vorübergehend deaktiviert.");}
//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//

require_once("../../inc/ingame/header.php");
echo $ausgabe;
require_once("../../inc/ingame/footer.php");


//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//

function checknumberofattacks() {

$returnvalue = 0;

global $time;
global $sciences;
global $msb2nd;
global $id;

$hours = date("G", $time);
$minutes = date("i", $time);
$seconds = date("s", $time);
$daytime = $time - $hours * 3600 - $minutes * 60 - $seconds;

$number_of_attacks = single("select count(*) from attacklogs where aid='$id' and arid!=drid and ginactive!=2 and warattack!=1 and done_unter_racherecht = 0 and time > $daytime");
if ($number_of_attacks >= ALLOWED_ATTACKS_A_DAY + $sciences{mil10} * $msb2nd{10}): $returnvalue = 1; endif;

return array($returnvalue, (ALLOWED_ATTACKS_A_DAY + $sciences{mil10} * $msb2nd{10} - $number_of_attacks));

}


function adjust_basic_landgrenze($insertlandgain, $id_intern, $land = 0) {
	if ($land == 0) {
		global $status;
		$land = $status['land'];
	}
	if (getServertype() == "basic") {
		$land_under_construction = getnumberoflandunderconstruction($id_intern); 
		if ($land + $land_under_construction + $insertlandgain > BASIC_MAX_LANDGRENZE)
			$insertlandgain = BASIC_MAX_LANDGRENZE - $land - $land_under_construction;
	}
	return $insertlandgain;
}


function getinsertlandgain() {
	## Bestimmt, wieviel Land der Spieler tatsächlich bekommt, da bei Inaktiven ab gewisser Landgröße mit Abzügen zu rechnen ist
	if (func_num_args() < 1) {
		global $insertlandgain, $landgain, $inactivity_mode, $status;
		$insertlandgain = $landgain;
		if ($inactivity_mode >= 1)	{
			if ($status[land] > 1000): $insertlandgain = floor($landgain * 0.5); endif;
			if ($status[land] > 2000): $insertlandgain = floor($landgain * 0.3); endif;
			if ($status[land] > 3000): $insertlandgain = floor($landgain * 0.2); endif;
			if ($status[land] > 4000): $insertlandgain = floor($landgain * 0.1); endif;
			if ($status[land] > 5000): $insertlandgain = floor($landgain * 0); endif;
		}
		$insertlandgain = adjust_basic_landgrenze($insertlandgain, $status['id']);
	}
	else {
		global $inactivity_mode;
		$land = func_get_arg(0);
		$landgain = func_get_arg(1);
		if ($inactivity_mode >= 1)	{
			$landgain_new = $landgain;
			if ($land > 1000): $landgain_new = floor($landgain * 0.5); endif;
			if ($land > 2000): $landgain_new = floor($landgain * 0.3); endif;
			if ($land > 3000): $landgain_new = floor($landgain * 0.2); endif;
			if ($land > 4000): $landgain_new = floor($landgain * 0.1); endif;
			if ($land > 5000): $landgain_new = floor($landgain * 0); endif;
			return $landgain_new;
		}
		else { return $landgain; }
	}
}

function wartankextra($status,$status_d, $wartanks, $landgain)	{
	
	$landgain = min(intval($status_d['land'] * 0.2),$landgain); //maximal 20% des landes

	if ($status[race] == "pbf")	{
		$extrakill = round($wartanks * 0.02);
		if ($extrakill > $landgain): $extrakill = $landgain; endif;
		return $extrakill;
	}
	else	{ return 0; }
}

function wartankausgabe($wartank_destroyed_buildings)	{
	global $sonstiges_output;
	global $sonstiges_output_d;
	if ($wartank_destroyed_buildings)	{
		$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre Wartanks haben insgesamt $wartank_destroyed_buildings zusätzliche Gebäude zerstört (in den obigen Verlusten inkl.)";
		$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Die Wartanks Ihres Gegners haben insgesamt $wartank_destroyed_buildings zusätzliche Gebäude zerstört (in den obigen Verlusten inkl.)";
	}
}

function roboticwallextra($rwlosses)	{
	global $sonstiges_output_d;
	global $updates_d;
	global $status_d;

	if ($status_d[race] == "uic" && $rwlosses)	{
		$resstats = getresstats();
		$rwcosts = assoc("select credits as money, minerals as metal, energy, sciencepoints from military_unit_settings where unit_id=10");
		foreach ($rwcosts as $ky => $vl)	{
			$rwwert += $vl * $resstats[$ky][value];
		}
		$moneyplus = round($rwwert * $rwlosses * 0.5);
		$status_d[money] += $moneyplus;
		$updates_d .= "money=money+$moneyplus,";
		$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Aus den Trümmern Ihrer Robotic Walls konnten insgesamt ".pointit($moneyplus)." Cr gewonnen werden.";
	}

}

function sentinelextra($who, $sentinels, $losses) {
	global $sonstiges_output, $time;
	global $sonstiges_output_d;
	global $updates, $updates_d;
	global $status, $status_d;
	global $queries;

	if ($who === 1)	{
		if ($status[race] == "uic" && $sentinels) {
			if ($sentinels > $losses) { $moneyplus = $losses * UIC_SENTINAL_RECYCLE_GAIN; }
			elseif ($sentinels < $losses) { $moneyplus = $sentinels * UIC_SENTINAL_RECYCLE_GAIN; }
			$status[podpoints] += $moneyplus;
			$updates .= "podpoints=podpoints+$moneyplus,";
			$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre Sentinels konnten aus den Trümmern der gefallenen Einheiten Schrott für insgesamt ".pointit($moneyplus)." Cr (in Form von Lagerguthaben) recyceln!";
			$queries[] = "update syndikate set podmoney = podmoney + $moneyplus where synd_id = ".$status['rid'];
		}
	}
	elseif ($who === 2)	{
		if ($status_d[race] == "uic" && $sentinels) {
			if ($sentinels > $losses) { $moneyplus = $losses * UIC_SENTINAL_RECYCLE_GAIN; }
			elseif ($sentinels < $losses) { $moneyplus = $sentinels * UIC_SENTINAL_RECYCLE_GAIN; }
			$status_d[podpoints] += $moneyplus;
			$updates_d .= "podpoints=podpoints+$moneyplus,";
			$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Ihre Sentinels konnten aus den Trümmern der gefallenen Einheiten Schrott für insgesamt ".pointit($moneyplus)." Cr (in Form von Lagerguthaben) recyceln!"; 
			$queries[] = "update syndikate set podmoney = podmoney + $moneyplus where synd_id = ".$status_d['rid'];
		}
	}
}

function behemothextra($who, $behemoths, $losses) {
	global $sonstiges_output, $time;
	global $sonstiges_output_d;
	global $queries;
	global $status, $status_d;
	
	$hourtime = get_hour_time($time);

	if ($who === 1)	{
		if ($status[race] == "nof" && $behemoths) {
			if ($behemoths >= $losses) { $rangerplus = $losses; }
			elseif ($behemoths < $losses) { $rangerplus = $behemoths; }
			$queries[] = "insert into military_away (unit_id, user_id, number, time) values (24, ".$status['id'].", $rangerplus, ".($hourtime+10*3600).")"; // 24 = carriers id
			echo "::";
			$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre Behemoths konnten die Trümmer der gefallenen Einheiten zu insgesamt <b>".pointit($rangerplus)."</b> Carriers umwandeln! (Diese sind in 10 Stunden verfügbar)";
		}
	}
	elseif ($who === 2)	{
		if ($status_d[race] == "nof" && $behemoths) {
			if ($behemoths >= $losses) { $rangerplus = $losses; }
			elseif ($behemoths < $losses) { $rangerplus = $behemoths; }
			$queries[] = "insert into military_away (unit_id, user_id, number, time) values (24, ".$status_d['id'].", $rangerplus, ".($hourtime+10*3600).")"; //24 ==carriers
				//echo "insert into military_away (unit_id, user_id, number, time) values (24, ".$status['id'].", $rangerplus, ".($hourtime+10*3600).")"; 
			$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Ihre Behemoths konnten die Trümmer der gefallenen Einheiten zu insgesamt <b>".pointit($rangerplus)."</b> Carriers umwandeln! (Diese sind in 10 Stunden verfügbar)";
		}
	}
}

function phoenixextra($who, $losses) {
	global $sonstiges_output;
	global $sonstiges_output_d;
	global $status, $status_d, $queries, $time;
	$hourtime = get_hour_time($time);
	if ($who === 1)	{
		if ($status[race] == "neb" && $losses) {
			global $wardata;
			$queries[] = "insert into build_military (unit_id, user_id, number, time) values (".$wardata[elites2][unit_id].",".$status[id].",$losses,".($hourtime+10*3600).")";
			$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre gefallenen Phoenix regenerieren sich und sind in 10h wieder einsatzfähig.";
		}
	}
	elseif ($who === 2)	{
		if ($status_d[race] == "neb" && $losses) {
			global $wardata_d;
			$queries[] = "insert into build_military (unit_id, user_id, number, time) values (".$wardata_d[elites2][unit_id].",".$status_d[id].",$losses,".($hourtime+10*3600).")";
			$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Ihre gefallenen Phoenix regenerieren sich und sind in 10h wieder einsatzfähig.";
		}
	}
}


function killspies() {

	global $sonstiges_output;
	global $sonstiges_output_d;
	global $status, $status_d;
	global $updates, $isatwar, $landgainmultiplier,$market_mil_d, $queries;

	define('SPY_LOSS_STANDARD', 0.25);
	
	$check = array(1);
	$spytypes = array("offspies", "defspies", "intelspies");
	foreach ($spytypes as $vl)	{	$totalspies	+= $status_d[$vl]+$market_mil_d[$vl];}
	$destroyed_spies_total = floor($totalspies * ($isatwar ? SPY_LOSS_STANDARD : SPY_LOSS_STANDARD));
	$destroyed_spies_total *= $landgainmultiplier;
	$destroyed_spies_total = floor($destroyed_spies_total);
	$destroyed_spies = floor($destroyed_spies);
	$destroyed_spies = array($destroyed_spies_total);
	if ($totalspies)	{
		for ($i=0, $o=1; $i < count($spytypes); $i++, $o++)	{
			foreach ($spytypes as $vl)	{
				if ($check[$i])	{
					if (!$sperre_spies{$vl})	{
						$spyloss{$vl} += (($status_d{$vl}+$market_mil_d[$vl]) / $totalspies) * $destroyed_spies[$i];
						if (($spyloss{$vl} - floor($spyloss{$vl})) >= 1/3): $spyloss{$vl} += 1;	endif;	# Wenn Dezimalen größer 0.333 sind wird aufgerundet
						$spyloss{$vl} = floor($spyloss{$vl});
						if ($spyloss{$vl} and $spyloss{$vl} / ($status_d{$vl}+$market_mil_d[$vl]) >= 0.2)	{
							$zufall = mt_rand(0,60);
							$new_spyloss = floor(($status_d{$vl}+$market_mil_d[$vl]) * SPY_LOSS_STANDARD); 
							//inok: früher statt SPY_LOSS_STANDARD: (0.17 + $zufall / 1000)); # Neuen Spyloss auf zufällig 17% - 23% festlegen
							$destroyed_spies[$o] += ($spyloss{$vl} - $new_spyloss); # Differenz bestimmen um die fehlenden Spies ermitteln zu können
							$spyloss{$vl} = $new_spyloss;	# Den gesamten Spyloss des bestimmten Typs aktualisieren
							$sperre_spies{$vl} = 1; $check[$o] = 1;	# Den bestimmten Spytyp fr näcshten Durchgang sperren - durch Check näcshten Durchgang ermöglichen
						}
					}
				} else { break 2; };
			}
		}
		$destroyed_spies = floor($destroyed_spies);

		$spystats = getspystats($status_d[race]);

		foreach ($spytypes as $vl)	{
			if ($spyloss{$vl} or $vl == "offspies" or $vl == "defspies"):
				$spyloss_meldung .= pointit($spyloss{$vl})." ".$spystats{$vl}[name]."s, ";
				// ALS ERSTES SPIONE AUF DEM MARKT TÖTEN
					$markettypes = changetype($vl, 0 ,$status_d[race]);
					$nothing_left = 0;
					$number = 0; $unique_id = 0;
					$templosses = $spyloss{$vl};
					$already_done = array();
					if ($market_mil_d[$vl]) {
						while ($templosses > 0 and !$nothing_left)	{
							list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status_d[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
							if ($number > $templosses): $queries[] =("update market set number=number-".$templosses." where offer_id=".$unique_id); $templosses = 0;
							elseif ($number): $templosses -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
							else: $nothing_left = 1;
							endif;
						}
					}
					if ($templosses > 0) {
						$updates_d .= "$vl=$vl-".$templosses.",";
						$status_d[$vl] -= $templosses;
					}
			endif;
		}

		$spyloss_meldung = chopp($spyloss_meldung);
		$spyloss_meldung = chopp($spyloss_meldung);

		$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre Truppen konnten insgesamt $spyloss_meldung töten!";
		$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Gegnerische Truppen konnten insgesamt $spyloss_meldung töten!";

		return array($updates_d, $destroyed_spies_total);
	}
}

function headhunterextra($headhunters)	{

	global $sonstiges_output;
	global $sonstiges_output_d;
	global $status, $status_d;
	global $updates, $market_mil_d, $queries, $unitspecial_number;

	if ($status[race] == "sl" && $headhunters >= 10)	{
		$destroyed_spies = array(floor($headhunters * 0.125 * 2)); // Seit Runde 10 doppelt soviel
		if ($destroyed_spies[0] > 0) $unitspecial_number = $destroyed_spies[0]; // Save-Variable für Attacklogs, die für Aufträge benötigt wird
		$check = array(1);
		$spytypes = array("offspies", "defspies", "intelspies");
		foreach ($spytypes as $vl)	{	$totalspies	+= $status_d[$vl] + $market_mil_d[$vl];}
		if ($totalspies)	{
			for ($i=0, $o=1; $i < count($spytypes); $i++, $o++)	{
				foreach ($spytypes as $vl)	{
					if ($check[$i])	{
						if (!$sperre_spies{$vl})	{
							$spyloss{$vl} += (($status_d{$vl}+$market_mil_d[$vl]) / $totalspies) * $destroyed_spies[$i];
							if (($spyloss{$vl} - floor($spyloss{$vl})) >= 1/3): $spyloss{$vl} += 1;	endif;	# Wenn Dezimalen größer 0.333 sind wird aufgerundet
							$spyloss{$vl} = floor($spyloss{$vl});
							if ($spyloss{$vl} and $spyloss{$vl} / ($status_d{$vl}+$market_mil_d[$vl]) >= 0.2)	{
								$zufall = mt_rand(0,60);
								$new_spyloss = floor(($status_d{$vl}+$market_mil_d[$vl]) * (0.17 + $zufall / 1000)); # Neuen Spyloss auf zufällig 17% - 23% festlegen
								$destroyed_spies[$o] += ($spyloss{$vl} - $new_spyloss); # Differenz bestimmen um die fehlenden Spies ermitteln zu können
								$spyloss{$vl} = $new_spyloss;	# Den gesamten Spyloss des bestimmten Typs aktualisieren
								$sperre_spies{$vl} = 1; $check[$o] = 1;	# Den bestimmten Spytyp fr näcshten Durchgang sperren - durch Check näcshten Durchgang ermöglichen
							}
						}
					} else { break 2; };
				}
			}
			
			$spystats = getspystats($status_d[race]);
			
			foreach ($spytypes as $vl)	{
				if ($spyloss{$vl} or $vl == "offspies" or $vl == "defspies"):
					$spyloss_meldung .= $spyloss{$vl}." ".$spystats{$vl}[name]."s, ";
					// ALS ERSTES SPIONE AUF DEM MARKT TÖTEN
					$markettypes = changetype($vl, 0 ,$status_d[race]);
					$nothing_left = 0;
					$number = 0; $unique_id = 0;
					$templosses = $spyloss{$vl};
					$already_done = array();
					if ($market_mil_d[$vl]) {
						while ($templosses > 0 and !$nothing_left)	{
							list($number,$unique_id) = row("select number, offer_id from market where type='".$markettypes[type]."' and prod_id='".$markettypes[prod_id]."' and owner_id='".$status_d[id]."' ".($already_done ? "and offer_id not in (".join(",",$already_done).") ":"")."order by inserttime desc limit 1");
							if ($number > $templosses): $queries[] =("update market set number=number-".$templosses." where offer_id=".$unique_id); $templosses = 0;
							elseif ($number): $templosses -= $number; $queries[] =("delete from market where offer_id=".$unique_id); $already_done[] = $unique_id;
							else: $nothing_left = 1;
							endif;
						}
					}
					if ($templosses > 0) {
						$updates_d .= "$vl=$vl-".$templosses.",";
						$status_d[$vl] -= $templosses;
					}
					$abwerben += $spyloss{$vl};
				endif;
			}
			if ($abwerben % 3 > 0) {
				$zufall = mt_rand(0,1);
					$offspiesplus = floor($abwerben/3);
					$defspiesplus = floor($abwerben/3);
					$intelspiesplus = floor($abwerben/3);
			}
			else {	$offspiesplus = floor($abwerben/3);
					$defspiesplus = floor($abwerben/3);
					$intelspiesplus = floor($abwerben/3);
			}
			$status[offspies] += $offspiesplus;
			$status[defspies] += $defspiesplus;
			$status[intelspies] += $intelspiesplus;
			$updates .= "offspies=offspies+".$offspiesplus.",";
			$updates .= "defspies=defspies+".$defspiesplus.",";
			$updates .= "intelspies=intelspies+".$intelspiesplus.",";
			$spyloss_meldung_angreifer .= pointit($offspiesplus)." ".$spystats{offspies}[name]."s, ";
			$spyloss_meldung_angreifer .= pointit($defspiesplus)." ".$spystats{defspies}[name]."s, ";
			$spyloss_meldung_angreifer .= pointit($intelspiesplus)." ".$spystats{intelspies}[name]."s";

			$spyloss_meldung = chopp($spyloss_meldung);
			$spyloss_meldung = chopp($spyloss_meldung);

			$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre Headhunter konnten zusätzlich $spyloss_meldung_angreifer für Ihren Konzern abwerben!";
			$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Gegnerische Headhunter konnten außerdem $spyloss_meldung abwerben!";
			
			return $updates_d;
		}
	}
}

function stalkerextra (&$status, $status_d, $stalkers_left)	{

	global $updates;
	global $updates_d;
	global $sonstiges_output;
	global $sonstiges_output_d;

	if ($status[race] == "sl")	{
		if ($stalkers_left > 0)	{

			$metalplus = floor($status_d{metal} * 0.1);
			if ($metalplus > $stalkers_left * 20): $metalplus = $stalkers_left * 20; endif;
			if ($metalplus < 0) $metalplus = 0;
			$status[metal] += $metalplus;
			$updates .= "metal=metal+$metalplus,";
			$updates_d .= "metal=metal-$metalplus,";

			$energyplus = floor($status_d{energy} * 0.1);
			if ($energyplus > $stalkers_left * 10): $energyplus = $stalkers_left * 10; endif;
			if ($energyplus < 0) $energyplus = 0;
			$status[energy] += $energyplus;
			$updates .= "energy=energy+$energyplus,";
			$updates_d .= "energy=energy-$energyplus,";

			$moneyplus = floor($status_d{money} * 0.1);
			if ($moneyplus > $stalkers_left * 100): $moneyplus = $stalkers_left * 100; endif;
			if ($moneyplus < 0) $moneyplus = 0;
			$status[money] += $moneyplus;
			$updates .= "money=money+$moneyplus,";
			$updates_d .= "money=money-$moneyplus,";

			$sonstiges_output .= "<br><b>&#155;</b>&nbsp;Ihre SL Marines konnten insgesamt ".pointit($moneyplus)." Cr, ".pointit($metalplus)." t und ".pointit($energyplus)." MWh stehlen";
			$sonstiges_output_d .= "<br><b>&#155;</b>&nbsp;Gegnerische SL Marines konnten außerdem $moneyplus Cr, $metalplus t und $energyplus MWh stehlen";
		}
	}
}


function a($what, $two) {

//global $ausgabe;

//$ausgabe .= "<table bgcolor=#7CA573><tr><td>$what:</td><td width=20></td><td>$two</td></tr></table>";

}

//
//// W A R A T T A C K
//

$ll_anteil = array();

function warattack(&$status, $target, $landgain_unmodified)	
{
	// Dient den Berechnungen für Passivlandverlust
	
	global $rid;
	global $time;
	global $queries;
	global $statusdata;
	global $landloss, $attacktype;
	
	$zufallswert = 1000000;

	$landgain = floor ($landgain_unmodified * KRIEG_LANDVERLUST_VERTEILUNG_GEGNERSYN);#done#
	a("landgain/landgain_unmodified", $landgain ." / ".$landgain_unmodified);
	$ids = singles("select id from status where rid=" . $status['rid'] . " and id!=$target and alive=1 and inprotection='N'");
	foreach ($ids as $vl)	
	{
		$statusdata{$vl} = getallvalues($vl);
		$total_synd_land += $statusdata{$vl}[land];
	}
	
	foreach ($ids as $vl)	
	{
		$landloss{$vl} = floor($landgain * $statusdata{$vl}[land] / $total_synd_land);
		a("landloss ".$statusdata{$vl}[syndicate], $landloss{$vl});
		$totalabzug += $landloss{$vl};
		a("totalabzug", $totalabzug);
	}

	while ($landgain != $totalabzug)	
	{
		$last = 0; // unset ($last);
		foreach ($ids as $vl)	{
			$ll_anteil{$vl} = array( $ll_anteil{$last}[1]+1,$ll_anteil{$last}[1]+round(($statusdata{$vl}[land]-$landloss{$vl}) / ($total_synd_land - $totalabzug) * $zufallswert) );
			$last = $vl;
		}
		$zufall = mt_rand(1, $zufallswert);
		foreach ($ids as $vl)	{
			if ($ll_anteil{$vl}[0] <= $zufall && $ll_anteil{$vl}[1] >= $zufall): $landloss{$vl}++; $totalabzug++; a("gefunden", $statusdata{$vl}[syndicate]."++"); break; endif;
		}
	}

	foreach ($ids as $vl)	{
		if ($landloss{$vl})
		{	
			$hisLand = single("select land from status where id=$vl");
			$hisLoss = $landloss{$vl};
			$hisLoss = min($hisLand - 400, $hisLoss);
			$updates = kill_land_and_buildings($vl, $statusdata{$vl}, $hisLoss);
			$updates .= "land=land-".$hisLoss.",";
			//$updates = kill_land_and_buildings($vl, $statusdata{$vl}, $landloss{$vl});
			//$updates .= "land=land-".$landloss{$vl}.",";
			$updates = chopp($updates);
			$queries[] = "update status set $updates where id=$vl";
			message_for_defender("part_of_war", $vl);
			a("loss ". $statusdata{$vl}[syndicate], $hisLoss);
		}
	}
}



function war_divide_on_syndicate($landdiff, $otherdata) 
{
	global $globals, $queries, $landwon, $buildinsertintos, $time;

	$zufallswert = 1000000;
	
	mailsendtonico("");
	
	foreach ($otherdata as $vl) 
	{
		$total_synd_land += $vl[land];
	}
	
	mailsendtonico("test");
	
	$count_otherdata = count($otherdata);
	
	foreach ($otherdata as $ky => $vl) 
	{
		$landwon[$ky] = floor($landdiff / $count_otherdata); # Änderung Runde 25, 8.11.2006 -alt: //floor($vl[land] / $total_synd_land * $landdiff);
		$landwon[$ky] = adjust_basic_landgrenze($landwon[$ky], $vl['id'], $vl['land']);
		$totaldone += $landwon[$ky];
	}
	
	mailsendtonico("d$landdiff != $totaldone");
	
	while ($landdiff != $totaldone) 
	{
		$last = 0; // unset ($last);
		$ll_anteil = array();
		foreach ($otherdata as $ky => $vl)	
		{
			$ll_anteil{$ky} = array( $ll_anteil{$last}[1]+1,$ll_anteil{$last}[1]+round(($vl[land]+$landwon{$ky}) / ($total_synd_land + $totaldone) * $zufallswert) );
			$last = $ky;
		}
		
		$zufall = mt_rand(1, $zufallswert);
		
		foreach ($otherdata as $ky => $vl)	
		{
			a("Zufall [unten, oben]", $zufall."[".$ll_anteil[$ky][0].", ".$ll_anteil[$ky][1]."]");
			if ($ll_anteil{$ky}[0] <= $zufall && $ll_anteil{$ky}[1] >= $zufall)
			{
				$landwon[$ky]++; 
				$totaldone++;
				break; 
			}
		}
	}
	
	mailsendtonico("");
	
	foreach ($otherdata as $ky => $vl)	
	{
		if ($landwon{$ky})
		{
			$buildinsertintos .= "('land', '$ky', '".$landwon[$ky]."', '".get_hour_time($time+20*3600)."','127'),";
			$largestgain = single("select attack_largest_won_waraffected from ".$globals[statstable]." where konzernid=$ky and round=".$globals[round]);
			if ($landwon[$ky] > $largestgain)
			{
				$queries[] = "update ".$globals[statstable]." set attack_largest_won_waraffected=".$landwon[$ky].",attack_total_won_waraffected=attack_total_won_waraffected+".$landwon[$ky].",attack_numberdone_waraffected=attack_numberdone_waraffected+1 where konzernid='$ky' and round=".$globals[round];
			}
			else
			{
				$queries[] = "update ".$globals[statstable]." set attack_total_won_waraffected=attack_total_won_waraffected+".$landwon[$ky].",attack_numberdone_waraffected=attack_numberdone_waraffected+1 where konzernid='$ky' and round=".$globals[round];
			}
			message_for_defender("attackwon_part_of_war", $ky);
		}
	}
	mailsendtonico("");
}

function get_free_land($status) {
	global $bd, $target;
	if (!$bd && func_num_args() < 2): $bd_intern = assocs("select name, name_intern, building_id from buildings", "name_intern");
	elseif ($bd): $bd_intern = $bd;
	else: $bd_intern = func_get_arg(2);
	endif;
	foreach ($bd_intern as $ky => $vl) { $ba[$vl[building_id]] = $status[$ky]; }
	$buildingdaten = assocs("select building_name, number, time, building_id from build_buildings where user_id='$target' and building_id != 127");
	foreach ($buildingdaten as $ky => $vl)	{
			$bb[$vl[building_id]] += $vl[number];
	}
	foreach ($bd_intern as $vl) { $bt{$vl[building_id]} = $ba{$vl[building_id]} + $bb{$vl[building_id]}; $totalbuildings += $bt{$vl[building_id]}; } # Anzahl aller Gebäude (bereits gebaut + in Bau) ermitteln
	$freeland = $status[land] - $totalbuildings;
	return $freeland;
}

function get_total_geb($status) {
	global $bd, $target;
	if (!$bd && func_num_args() < 2): $bd_intern = assocs("select name, name_intern, building_id from buildings", "name_intern");
	elseif ($bd): $bd_intern = $bd;
	else: $bd_intern = func_get_arg(2);
	endif;
	foreach ($bd_intern as $ky => $vl) { $ba[$vl[building_id]] = $status[$ky]; }
	$buildingdaten = assocs("select building_name, number, time, building_id from build_buildings where user_id='$target' and building_id != 127");
	foreach ($buildingdaten as $ky => $vl)	{
			$bb[$vl[building_id]] += $vl[number];
	}
	foreach ($bd_intern as $vl) { $bt{$vl[building_id]} = $ba{$vl[building_id]} + $bb{$vl[building_id]}; $totalbuildings += $bt{$vl[building_id]}; } # Anzahl aller Gebäude (bereits gebaut + in Bau) ermitteln
	$freeland = $totalbuildings;
	return $freeland;
}

//
//// K I L L  _ L A N D _ A N D _ B U I L D I N G S
//

function kill_land_and_buildings($target, &$status, $landgain)	
{
	global $bd;
	global $bl; $bl = array();
	global $builddeletefroms;
	global $buildinsertintos;
	global $globals;
	global $queries;
	global $freelandloss, $attacktype, $status_d;
	
	$zufallswert = 1000000;
	
	if (func_num_args() > 3) 
	{ 
		global $wartank_destroyed_buildings; 
		$setwartanks = &$wartank_destroyed_buildings;
	}

	#Wenn der hier behandelte Konzern nicht der Konzern ist, der angegriffen wurde, wird der Landloss als "normaler" Landloss deklariert, da hier auch normale Gebäude kaputt gehen sollen.
	if ($target != $status_d[id]): $attacktype_temp = "waraffected"; else: $attacktype_temp = $attacktype; endif;

	// Einige Werte je nach Angriffstyp anpassen
	if ($attacktype_temp == "siege"): global $destroyed_buildings; $setwartankssafe = $wartank_destroyed_buildings; $setwartanks = $setwartankssafe + $destroyed_buildings; endif;
	if ($attacktype_temp == "killspies"): global $killedspies; endif;
	a("$ setwartankssafe", $setwartanks);
	// Eintrag in Statistik Table
	if ($attacktype_temp == "waraffected"): $additional_stats_input = ",attack_numbersuffered_waraffected=attack_numbersuffered_waraffected+1"; endif;
	$this_vergleichswert = (($attacktype_temp == "siege") ? $destroyed_buildings:($attacktype_temp == "killspies" ? $killedspies : $landgain));
	$largestloss = single("select attack_largest_loss_$attacktype_temp from ".$globals[statstable]." where konzernid=$target and round=".$globals[round]);
	if ($largestloss > $this_vergleichswert): $queries[] = "update ".$globals[statstable]." set attack_total_loss_$attacktype_temp=attack_total_loss_$attacktype_temp+$this_vergleichswert$additional_stats_input where konzernid='$target' and round=".$globals[round];
	else: $queries[] = "update ".$globals[statstable]." set attack_total_loss_$attacktype_temp=attack_total_loss_$attacktype_temp+$this_vergleichswert,attack_largest_loss_$attacktype_temp=$this_vergleichswert$additional_stats_input where konzernid='$target' and round=".$globals[round]; endif;

		# $ba ~ bereits gebaute Gebäude (buildings_available)
		# $bb ~ sich im Bau befindende Gebäude (build_buildings)
		# $bt - $ba + $bb
		# $bal ~ Verlust an Gebäuden für den jeweiligen Gebäudetyp bereits gebauter Gebäude
		# $bbl ~ Verlust an Gebäuden für den jeweiligen Gebäudetyp sich im Bau befindender Gebäude
		# $bl - Verlust an Gebäuden in Bau + bereits gebaut gesamt

	foreach ($bd as $ky => $vl) { $ba[$vl[building_id]] = $status[$ky]; }

	$buildingdaten = assocs("select building_name, number, time, building_id from build_buildings where user_id='$target' and building_id != 127");
	if ($buildingdaten): $builddeletefroms .= "$target,"; endif;
	// Sich im Bau befindende Gebäude dem %bb-Hash hinzufügen (build_buildings)

	foreach ($buildingdaten as $ky => $vl)	{
		#if ($vl[building_id] != 127)	{	# Land (=id 127) ist kein Gebäude
			$bb_all_data[$vl[building_id]][$vl[time]] += $vl[number];
			$bb[$vl[building_id]] += $vl[number];
		#}
	}
	foreach ($bd as $vl) { $bt{$vl[building_id]} = $ba{$vl[building_id]} + $bb{$vl[building_id]}; $totalbuildings += $bt{$vl[building_id]}; } # Anzahl aller Gebäude (bereits gebaut + in Bau) ermitteln
	$freeland = $status[land] - $totalbuildings;
	if ($freeland < 0): $ausgleich = $freeland * (-1); endif;
	## Ausgabe
	foreach ($bd as $vl) { a($vl[name_intern]." bau/gebaut", $bb{$vl[building_id]}." / ".$ba{$vl[building_id]});}
	## Wenn der Landgain kleiner ist als das Freie Land und keine Gebäude durch Wartanks zerstört werden, wird hier abgebrochen
	if ($landgain <= $freeland && !$setwartanks)	{	a("what?", "return: $landgain < $freeland"); $freelandloss = $landgain; $landgain = 0; }
	## Hier wird der Landgain angepasst, entweder auf Landgain - Freies Land + Setwartanks oder auf Landgain = Setwartanks, je nach Fall
	if ($landgain > $freeland):	$freelandloss = $freeland; $landgain -= $freeland - $setwartanks;	else: $freelandloss = $landgain; if ($setwartanks < $totalbuildings): $landgain = $setwartanks; else: $landgain = $totalbuildings; $setwartanks = $totalbuildings; endif; endif;
	a("neuer landgain(gebäudezerstörgain)", "$landgain -= $freeland - $setwartanks | $landgain = $setwartanks");
	## Hier wird der erste Grobe Verlust an Gebäuden der einzelnen Typen bestimmt, da immer abgerundet wird ist der Verlust fast nie 100%
	foreach ($bd as $ky => $vl)	{
		$bl[$vl[building_id]] = floor($landgain * ($ba[$vl[building_id]] + $bb[$vl[building_id]]) / ($status[land] + $ausgleich));
		$totalabzug += $bl[$vl[building_id]];
	}
	## Ab hier wird der Überschuss an Land welcher durch Prozentrechnung unter den Tisch gefallen ist berechnet
	$endlosbarrier = 0;
	while ($landgain > $totalabzug)	{
		unset ($last);
		foreach ($bd as $vl)	{
			$bl_anteil{$vl[building_id]} = array( $bl_anteil{$last[building_id]}[1]+1,$bl_anteil{$last[building_id]}[1]+round(($bt{$vl[building_id]}-$bl{$vl[building_id]}) / ($totalbuildings - $totalabzug) * $zufallswert) );
			$last = $vl;
		}
		$zufall = mt_rand(1, $zufallswert);
		foreach ($bd as $vl)	{
			if ($bl_anteil{$vl[building_id]}[0] <= $zufall && $bl_anteil{$vl[building_id]}[1] >= $zufall): $bl{$vl[building_id]}++; $totalabzug++; a("gefunden", $vl[name]."++"); break; endif;
		}
		if ($endlosbarrier++ > 1000) break;
	}
	## Hier steht der gesamte Gebäudeabzug fest: es werden zunächst Gebäude in Bau getötet, danach die Gebäude die schon gebaut sind.
	$bl_temp = $bl;	# $bl kopieren
	foreach ($bd as $vl)	{
		a($bl_temp[$vl[building_id]], $vl[name]);
		if ($bb{$vl[building_id]} < $bl{$vl[building_id]}) { $bal{$vl[building_id]} = $bl{$vl[building_id]} - $bb{$vl[building_id]}; $updates .= $vl[name_intern]."=".$vl[name_intern]."-".$bal{$vl[building_id]}.","; }
		elseif ($bb{$vl[building_id]} > $bl{$vl[building_id]}) {
			krsort($bb_all_data[$vl[building_id]]);
			foreach ($bb_all_data{$vl[building_id]} as $ky2 => $vl2)	{
				if ($bl_temp[$vl[building_id]] >= $vl2): $bl_temp[$vl[building_id]] -= $vl2; $vl2 = 0;
				elseif ($vl2 > $bl_temp[$vl[building_id]]): $vl2 -= $bl_temp[$vl[building_id]]; $bl_temp[$vl[building_id]] = 0; endif;
				if ($vl2): $buildinsertintos .= "('".$vl[name_intern]."','$target','$vl2','$ky2','".$vl[building_id]."'),"; endif;
			};
		}
	}

	// Je nach Angriffstyp nochmal ein paar Sachen richtigstellen
	if ($attacktype_temp == "siege"): if ($setwartanks < $destroyed_buildings + $setwartankssafe): if ($setwartanks < $destroyed_buildings): $destroyed_buildings = $setwartanks; endif; $wartank_destroyed_buildings = $setwartanks - $destroyed_buildings; else: $wartank_destroyed_buildings = $setwartankssafe; endif; endif;

	return $updates;
}

//
//
//	AUSGABEFUNKTIONEN
//
//


function output_unitloss($milname, $lossamount)	{

	return array("<table width=\"250\" border=0 cellspacing=1 class=\"siteGround\" cellpadding=2 align=center>
				<tr><td width=\"50\" align=right><b class=\"siteGround\">$lossamount</b></td><td width=5>&nbsp;&nbsp;</td>
					<td width=\"195\" align=left class=\"siteGround\">$milname</td>
				</tr>
			</table>", "$lossamount $milname");
}


function attackausgabe($switch)	{

	global $ausgabe;

	####
	#######	Init, steht bei Angriff immer ganz oben als Pseudotitel sozusagen
	####

	if ($switch == "init")	{

	$ausgabe .= "<br><b>Kampfergebnisse</b><br><br>";

	}

	####
	#######	Win, Meldung über Sieg
	####

	elseif ($switch == "win")		{
	$ausgabe .= "<b class=\"highlightAufSiteBg\">SIE HABEN GESIEGT !</b><br>";
	}

	####
	#######	Loss, Meldung über Niederlage
	####

	elseif ($switch == "lost")		{
	$ausgabe .= "<b class=\"highlightAufSiteBg\">SIE KONNTEN LEIDER NICHT GEWINNEN !</b><br>";
	}

	####
	#######	Gegner getötet, kurze Information darüber
	####

	elseif ($switch == "enemy killed")		{
	$ausgabe .= "<br><b>Mit diesem Angriff haben Sie das letzte Stück Land Ihres Gegners erobert und ihn somit vernichtet!</b>";
	}

	####
	#######	Win, Ausgabe der detaillierten Kampfergebnisse wie Einheiten vernichtet, Gebäude zerstört, Land erobert
	####

	elseif ($switch == "kampfergebnisse_win")	{

		$temp_output = temp_destroyed_buildings_output("attacker");

		global $landgain;
		global $insertlandgain;
		global $status_d;
		global $ausgabe;
		global $losses_output;
		global $losses_output_d;
		global $sonstiges_output, $attacktype, $wartank_destroyed_buildings;
		global $time;

		if ($landgain != $insertlandgain)	{
			$possible_inactive = " (Sie erhalten davon $insertlandgain)";
		}


		$ausgabe .= "
		<br><br>
		<b>&#155;</b>
		Sie haben Ihren <b>Gegner</b> \"<i>".$status_d[rulername]." von ".$status_d[syndicate]." (#".$status_d['rid'].")</i>\" am ".mytime($time)." Uhr - besiegt und ihm folgende <b>Verluste</b> zugefügt:
		<br>
		<br>";
		$counter = 0;
		foreach ($losses_output_d as $ky => $vl) 
		{
			$counter++; 
			$ausgabe .= $losses_output_d[$ky][0];
		}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		$ausgabe .= "
		<br>
		<b>&#155;</b>&nbsp;<b>Ihre Verluste:</b> <br>
		<br>";
		$counter = 0;
		foreach ($losses_output as $ky => $vl) { $counter++; $ausgabe .= $losses_output[$ky][0];}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		if ($attacktype == "normal" or $attacktype == "conquer") {
			$ausgabe .= "<br>
			<br>
			<b>&#155;</b>&nbsp;Sie haben insgesamt <font class=\"highlightAufSiteBg\"><b>".$landgain."</b>".$possible_inactive."</font> <b>".($attacktype == "conquer" ? " unbebautes ":"")."Land eingenommen</b> <br>
			";
			if ($attacktype == "normal" or $wartank_destroyed_buildings) {
				$ausgabe .= "<br>
				<b>&#155;</b>&nbsp;und Ihrem <b>Gegner</b> folgende <b>Gebäude vernichtet</b>:
				<br><br>
				".$temp_output;
			}
			$ausgabe .= "<br><br>";
		}
		elseif ($attacktype == "siege") {
			global $destroyed_buildings;
			$ausgabe .= "<br>
			<br>
			<b>&#155;</b>&nbsp;Sie haben Ihrem <b>Gegner</b> während der <b>Belagerung</b> folgende <font class=\"highlightAufSiteBg\"><b>".$destroyed_buildings."</b></font> <b>Gebäude vernichtet</b>:
			<br><br>
			".$temp_output."<br><br>";
		}
		elseif ($attacktype == "killspies") {
			global $destroyed_buildings;
			$ausgabe .= "<br>
			<br>
			<b>&#155;</b>&nbsp;Sie haben Ihren <b>Gegner</b> besiegt.
			<br><br>
			";
			if($wartank_destroyed_buildings) {
				$ausgabe .= "<b>&#155;</b>&nbsp;Sie konnten außerdem folgende <b>Gebäude vernichten</b>:
				<br><br>
				".$temp_output.'<br><br>';
			}
		}
		$ausgabe .= $sonstiges_output;
	}

	####
	#######	Win-War, Ausgabe der detaillierten Kampfergebnisse wie Einheiten vernichtet, Gebäude zerstört, Land erobert
	####		Änderung zu Win: Hinweis darauf, dass es ein Kriegsangriff war und die Syndikatspartner ebenfalls Land verloren haben.

	elseif ($switch == "kampfergebnisse_win_war")	{

		$temp_output = temp_destroyed_buildings_output("attacker");

		global $landgain;
		global $insertlandgain;
		global $status_d;
		global $ausgabe;
		global $losses_output;
		global $losses_output_d;
		global $sonstiges_output;
		global $landgain_adjusted;
		global $landverlust_gegnersyn;
		global $landdiff, $attacktype, $wartank_destroyed_buildings;

		if ($landgain != $insertlandgain)	{
			//$possible_inactive = " (Sie erhalten davon $insertlandgain)"; // kein passiv land
		}

		$ausgabe .= "
		<br><br>
		<b>&#155;</b>
		Sie haben Ihren <b>Gegner</b> \"<i>".$status_d[rulername]." von ".$status_d[syndicate]."</i>\" besiegt und ihm folgende <b>Verluste</b> zugefügt:
		<br>
		<br>";
		foreach ($losses_output_d as $ky => $vl) { $counter++; $ausgabe .= $losses_output_d[$ky][0];}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		$ausgabe .= "
		<br>
		<b>&#155;</b>&nbsp;<b>Ihre Verluste:</b> <br>
		<br>";
		$counter = 0;
		foreach ($losses_output as $ky => $vl) { $counter++; $ausgabe .= $losses_output[$ky][0];}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		if ($attacktype != "killspies") {
			$ausgabe .= "<br>
			<br>
			<b>&#155;</b>&nbsp;Sie haben insgesamt <font class=\"highlightAufSiteBg\"><b>".$landgain."</b>".$possible_inactive."</font> <b>Land eingenommen</b> <br>";
			if ($landverlust_gegnersyn > 0 and false) {
			$ausgabe .= "<br>Da es sich um einen Angriff auf ein Syndikat welches mit Ihrem Syndikat Krieg führt, gehandelt hat, stammen $landgain_adjusted ".($attacktype == "conquer" ? " <b>unbebautes</b> ":"")."Land direkt von Ihrem Gegner und $landdiff Land von den übrigen Syndikatsmitgliedern des Feindsyndikats.";
			}
			/*$ausgabe .= "
			<br>Sie erhalten davon $landgain_adjusted Land. "/*.Die übrigen $landdiff Land wurden auf die Mitglieder Ihres Syndikats verteilt".($landverlust_gegnersyn == 0 ? " (Angriff während eines Krieges)":"").."<br>";*/
			if ($attacktype == "normal" or $wartank_destroyed_buildings) {
				$ausgabe .= "<br>
				<b>&#155;</b>&nbsp;Sie haben Ihrem <b>Gegner</b> zusätzlich folgende <b>Gebäude vernichtet</b>:
				<br><br>
				".$temp_output;
			}
		}
		$ausgabe .= "<br><br>
		".$sonstiges_output;
	}
	
	####
	#######	Loss, Ausgabe der detaillierten Kampfergebnisse wie Einheiten vernichtet
	####
	
	elseif ($switch == "kampfergebnisse_loss")	{
	
		global $landgain;
		global $insertlandgain;
		global $status_d;
		global $ausgabe;
		global $losses_output;
		global $losses_output_d;
		global $sonstiges_output;
		
		$ausgabe .= "
		<br><br>
		<b>&#155;</b>
		Sie konnten Ihren <b>Gegner</b> \"<i>".$status_d[rulername]." von ".$status_d[syndicate]."</i>\" leider <b>nicht besiegen</b>!<br><br>
		<b>&#155;</b></font>&nbsp;<b>Die Verluste</b> Ihres Gegners sind wie folgt:<br><br>";
		foreach ($losses_output_d as $ky => $vl) { $counter++; $ausgabe .= $losses_output_d[$ky][0];}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		$ausgabe .= "
		<br>
		<b>&#155;</b>&nbsp;<b>Ihre Verluste:</b> <br>
		<br>";
		$counter = 0;
		foreach ($losses_output as $ky => $vl) { $counter++; $ausgabe .= $losses_output[$ky][0];}
		if (!$counter): $ausgabe .= "<center><b>keine</b></center><br><br>"; endif;
		$ausgabe .= $sonstiges_output;
	}

}

function temp_destroyed_buildings_output($type)	{
	global $bl;
	global $bd;
	$durch = 0;
	if ($type == "attacker"):
		$temp_output = "	<table border=0 class='tableOutline' align=center cellspacing=0 cellpadding=0><tr><td>
								<table border=0 cellspacing=1 cellpadding=4 width=300>";
		foreach ($bd as $vl)	{
			if ($bl[$vl[building_id]])	{
				$durch++;
				$temp_output .=	"	<tr class=\"tableInner1\"><td width=200>".$vl[name]."</td><td width=200>&nbsp;&nbsp;".$bl[$vl[building_id]]."</td></tr>";
			}
		}
		if (!$durch): $temp_output .= "<tr class=\"tableInner1\"><td colspan=2 width=400 align=center>Es wurden keine Gebäude zerstört!</td></tr>"; endif;
		$temp_output .= "		</table></td></tr></table>";
	elseif ($type == "defender"):
		foreach ($bd as $vl)	{
			if ($bl[$vl[building_id]])	{
				$durch++;
				$temp_output .=	$bl[$vl[building_id]] . " ". $vl[name].", ";  
			}
		}
		if ($durch): $temp_output = chopp($temp_output); $temp_output = chopp($temp_output);
		elseif (!$durch): $temp_output = "Es wurden keine Gebäude zerstört!"; endif;
	endif;


	return $temp_output;
}

function message_for_defender($switch)	{

	global $status;
	global $status_d;
	global $statusdata;
	global $sonstiges_output_d;
	global $losses_output;
	global $losses_output_d;
	global $landgain;
	global $landgain_adjusted;
	global $landdiff;
	global $queries;
	global $time;
	global $target;
	global $stealthed;
	$target_intern = $target;
	global $landloss, $landwon;
	
	if (func_num_args() > 1)
	{
		$target_intern = func_get_arg(1);
	}
	
	global $insertintomessages, $attacktype;

	if (!$sonstiges_output_d)
	{
		$sonstiges_output_d = " n/a"; $schalter = 1;
	}

	$temp_output = temp_destroyed_buildings_output("defender");

	foreach ($losses_output as $ky => $vl)	
	{
		$temp_losses_output .= $vl[1].", ";
	}
	foreach ($losses_output_d as $ky => $vl)	
	{
		$temp_losses_output_d .= $vl[1].", ";
	}
	$temp_losses_output = chopp($temp_losses_output);	$temp_losses_output = chopp($temp_losses_output);
	$temp_losses_output_d = chopp($temp_losses_output_d);	$temp_losses_output_d = chopp($temp_losses_output_d);
	if (!$temp_losses_output): $temp_losses_output = "keine"; endif;
	if (!$temp_losses_output_d): $temp_losses_output_d = "keine"; endif;

	if ($attacktype == "normal"):		$angriffsart = "Standardangriff";
	elseif ($attacktype == "siege"):	$angriffsart = "Belagerungsangriff";
	elseif ($attacktype == "conquer"):	$angriffsart = "Eroberungsangriff";
	elseif ($attacktype == "killspies"):$angriffsart = "Spione zerstören";
	endif;

	if ($switch == "attacklost")	{
		$messagedata = $angriffsart."|".($stealthed ? "Unbekannt":$status[syndicate])."|".($stealthed ? "???":$status[rid])."|".$landgain."|".$temp_output."|".$temp_losses_output_d."|".$temp_losses_output."|".$sonstiges_output_d;
		$messageid = 15;
	}
	elseif ($switch == "attackwon")	{
		$messagedata = ($stealthed ? "Unbekannt":$status[syndicate])."|".($stealthed ? "???":$status[rid])."|".$temp_losses_output_d."|".$temp_losses_output."|".$sonstiges_output_d;
		$messageid = 16;
	}
	elseif ($switch == "part_of_war")	{/*
		$messagedata = $status_d[rulername]."|".$status_d[syndicate]."|".($stealthed ? "Unbekannt":$status[syndicate])."|".($stealthed ? "???":$status[rid])."|".$landgain_adjusted."|".$landdiff."|".$landloss{$target_intern}."|".$temp_output;
		$messageid = 17;*/
	}
	elseif ($switch == "attacklost_war")	{
		$messagedata = $angriffsart."|".($stealthed ? "Unbekannt":$status[syndicate])."|".($stealthed ? "???":$status[rid])."|".$landgain_adjusted."|"/*.$landdiff."|"*/.$temp_output."|".$temp_losses_output_d."|".$temp_losses_output."|".$sonstiges_output_d;
		$messageid = 15; //18
	}
	elseif ($switch == "attackwon_part_of_war") {
		/*$messagedata = $status[rulername]."|".$status[syndicate]."|".$status_d[syndicate]."|".$status_d[rid]."|".$landgain."|".$landdiff."|".$landwon{$target_intern};
		$messageid = 42;*/
	}

	if ($schalter)	{ $sonstiges_output_d = "";};
	for ($i = 0; $i < 1; $i++)	{$insertintomessages .= "('$messageid', '$target_intern', '$time', '$messagedata'),";};
}

function mailsendtonico($data) {
	global $id;
	static $countit;
	if (!$countit): $countit = 0; endif;
	if ($id == 4538000) {
		sendthemail("Angriff Barrier $countit","$data","admin@DOMAIN.de","ADMIN");
		$countit++;
	}

}
/* Ausgelagert in subs_attack.php
function get_bash_protection($target_id) {
	
	global $status,$time;
	// Berechnung des Bashschutzes
	// Verlust von 10% --> 1. Schutzphase / Je fremd / Eigenverlust
	// Verlust von 20% --> 2. Schutzphase
	// Zusätzlich zählen Angriffe anderer Angriffstypen für den Bashschutz
	//
	// Berechnung über Punkte:
	// > 10% in 24h --> 1 Punkt
	// > 20% in 24h --> 2 Punkte
	// Jeder nicht landhol Angriffstyp mit Flasg gbprot = 1 --> 1 Punkt (Siege, Conquer hier relevant)
	// Beides wird aufgesplittet nach eigenen und fremdangriffen, wobei ein eigener angriff auch ein fremdangriff ist
	//
	
	$timelimit = $time -  TIME_RELEVANT_FOR_BASH_PROTECTION;	
	$modifier;
	
	// Std und Conquer
	$stmt_own = "select sum(landgrab/dland) from attacklogs where did=$target_id and aid=$status[id] and time >= $timelimit and type in (1,3) group by did";
	$percent_lost_own_attacks = single($stmt_own);
	
	$stmt_foreign = "select sum(landgrab/dland) from attacklogs where did=$target_id and time >= $timelimit and type in (1,3) group by did"; 
	$percent_lost_foreign_attacks = single($stmt_foreign);
	
	
	// Siege und killspies
	// Attacktypes:
	// 1 : Normal
	// 2 : Siege
	// 3 : Conquer
	// 4 : Killspies
	
	$num_gbprot_attacks_own = single("select count(*) from attacklogs where did=$target_id and aid=$status[id] and time >= $timelimit and gbprot=1 and type in (2,4) group by did");
	$num_gbprot_attacks_foreign = single("select count(*) from attacklogs where did=$target_id and time >= $timelimit and gbprot=1 and type in (2,4) group by did");
	
	
	// Punkte berechnen
	$points_own = 0;
	$points_foreign = 0;
	
	// Eigene Punkte durch Landangriffe
	if ($percent_lost_own_attacks * 100 >= BASH_PROTECTION_2_LANDLOSS_REQUIRED) {
		$points_own = 2;	
	}
	elseif ($percent_lost_own_attacks * 100 >= BASH_PROTECTION_1_LANDLOSS_REQUIRED) {
		$points_own = 1;	
	}
	
	// Fremdpunkte durch Landangriffe
	if ($percent_lost_foreign_attacks * 100 >= BASH_PROTECTION_2_LANDLOSS_REQUIRED) {
		$points_foreign = 2;	
	}
	elseif ($percent_lost_foreign_attacks * 100 >= BASH_PROTECTION_1_LANDLOSS_REQUIRED) {
		$points_foreign = 1;	
	}
	
	// Nicht landgain angriffe mit gbprot addieren
	$points_own+=$num_gbprot_attacks_own;
	$points_foreign+=$num_gbprot_attacks_foreign;
	
	
	// Cap bei 2 Punkten
	if ($points_own > 2) $points_own = 2;
	if ($points_foreign > 2) $points_foreign = 2;
	
	// Faktor berechnen
	$landgainmultiplier_own		 = pow(((100-BASH_PROTECTION_FACTOR_OWN)/100), (float)$points_own);
	$landgainmultiplier_foreign	 = pow(((100-BASH_PROTECTION_FACTOR_FOREIGN)/100), (float)$points_foreign);
	
	
	// Für den Verteidiger besseren faktor zurückgeben
	$modifier = min($landgainmultiplier_own,$landgainmultiplier_foreign);
	$points_back = max($points_own,$points_foreign);
	
	
	return array($modifier,$points_back);
}
*/

function getnumberoflandunderconstruction($id) {
	$action ="select sum(number) from build_buildings where user_id ='".$id."' and building_name = 'land'";
    	$actionhandle = select($action);
    	$values = mysql_fetch_row($actionhandle);
    	return $values[0];
}
?>
