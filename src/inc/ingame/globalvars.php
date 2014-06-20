<?
// hier werden nur globale konstanten definiert, die von mehr als einer datei verwendet werden.


//
////
/////// ACHTUNG: VOR DEM INCLUDE DIESER DATEI MUSS EINE DATENBANKVERBINDUNG BESTEHEN!!!! andernfalls wird die CLASSIC-Konfiguration geladen und es gibt Fehlermeldungen

//Unterschiedliche IF-Blï¿½cke je nach Serverkonfiguration (Basic <--> Classic)

###################################################################
###################################################################
##################		CLASSIC KONSTANTEN
###################################################################
###################################################################
define(ISRANDOMRUNDE,0); //ggt 0

// Eventstimestamps zu Ostern, Weihnachten etc. sind unter inc/events zu finden

define('CLIENT_SCORING_ENDTIME', strtotime('2012-03-18 20:00'));
// CLASSIC
define(PROTECTIONTIME,60*60*78); // Macimal 78 Stunden Schutz - $status['unprotecttime'] gibt das individuelle Schutzzeitende fï¿½r jeden Spieler aus.
define(UNPROTECT_BONUS,0.003);

// Inaktivitätszeiten
define (TIME_TILL_INACTIVE,60*60*24*3); // Zeit bis inaktiv (lokal)
define (TIME_TILL_GLOBAL_INACTIVE,60*60*24*5); // Zeit bis inaktiv (global)
define (TIME_TILL_KILLED,60*60*24*7.1); // Zeit Spieler wegen inaktivitï¿½ gelï¿½cht wird
			
// Sonstiges
define(MENTOR_SPACE_RESERVED, 3); // 3 Plï¿½tze fï¿½r Schï¿½tzlinge reservieren
define(MAX_USERS_A_GROUP, 10);
define(MAX_USERS_AFTER_ROUNDSTART, 15);
define(USERS_USED_FOR_RANKING, 12);
define(MAX_USERS_A_SYNDICATE, 14);		
define(MAX_USERS_A_NOOB_SYNDICATE, 19);
define(USERS_NEEDED_FOR_CLOSED_GROUP, 8);
define(USERS_NEEDED_FOR_CLOSED_SYNDICATE, 12);
define(MAX_PLAYERS_FOR_FUSIONIERUNG, 11);
define(TIME_BEFORE_ROUND_END_NO_JOIN_INTO_TOP_10_SYNDICATE, 1209600); // 2 Wochen
define('BUDDY_COUNT_MAX', 50);			
			
// Bildprï¿½fixe
define("KBILD_PREFIX","konzern_");
define("SBILD_PREFIX","syndikat_");
define(DELAY_AFTER_START,6); // Lagerzugriff erst nach 24 Stunden mï¿½lich // derzeit nicht verwendet
			
// Rundenübergänge, Dauer, Pause, Freezetime etc.
define('ROUND_FREEZETIME_DURATION', 86400); // IMMER game.php anpassen, da dort aus technischer Sicht nicht verwendbar!
define('ROUND_ANMELEPHASE_DURATION', 2*86400+18*3600);
define('ROUND_AKTIV_DURATION', 52*86400+6*3600);
// TODO verwende ÜBERALL die konstanten update.php, prepareNewRound.php, startround.php, index2.php		

###################################################################
###################################################################
##################		ALLGEMEINE KONSTANTEN
###################################################################
###################################################################


// SONSTIGES
define(TESTACCOUNTTIME,60*60*24*60); // 52 Tage Probespielen erlaubt
define(CONFIGTIME,60*60); // Eine Stunde Configtime fr jeden neuen Konzern
define(RESET_SPERRE_NACH_RUNDENSTART, 0*60*60);
define(DELETE_SPERRE_NACH_RUNDENSTART, 72*60*60);
define(DELETE_SPERRE_NACH_CREATE, 24*60*60);
define(START_BUILDTIME,1); // Nur eine Stunde bauzeit fr land / Gebï¿½de wï¿½rend configtime und  1h fr Mil / Spio units wï¿½rend schutzzeit
define(GVIMAXLAND,2000); // Maximales Land, das ein Spieler der GVI haben darf
define (POWERPLANT_STORE,1000); // unmodifizierter wert fr speicherkapazitï¿½ eines kraftwerks
define(IND2BONUS2,1000); // Mehr speicher durch ind2
define(TOP_PERCENTAGE_ALLOWED_TO_PLAY_ON_CLASSIC_BY_RANK, 10); // 10% der besten vom basic-Server dï¿½rfen zukï¿½nftig auf Classic spielen

// old: define(USERS_USED_FOR_SYNFOS, 12);	
// Runde 52 ï¿½nderung start
$syndata = assocs('select synd_id from syndikate');
$minPlayers = 100;
$minPlayersSyn = 0;
foreach($syndata as $tag => $value){
	
	//SynNr
	$synID = $value['synd_id'];
	//Anzahl der Spieler des aktuellen Syndicate ermitteln
	$playersCount = single("select count(id) from status where alive != 0 and rid=".$synID);
	
	if($playersCount < $minPlayers){ //sinnloser Part
		$minPlayers = $playersCount;
		$minPlayersSyn = $synID;
	}

}
define(USERS_USED_FOR_SYNFOS, $minPlayers);
// Runde 52 ï¿½nderung ende

define(MENTOR_ROUNDS, 3); // wie viele Runden zurï¿½ck Neuling von Mentoren als diese gesehen werden
define(MENTOR_PIC, "http://syndicates-online.de/images/Herz.png");

define(GAMBLE_TIME, 6); // Alle 6h gibts das Glï¿½cksspiel

// NETWORTH ab R46
define(NW_AKTIEN, 1); // R4bbiT - 06.09.10 - vorher: 0.0002

define(NW_FOS_LVL1, 500);
define(NW_FOS_LVL2, 1000);
define(NW_FOS_LVL3, 2000);
define(NW_FOS_LVL4, 4000);
define(NW_FOS_LVL5, 8000);
define(NW_FOS_LVL6, 16000);
define(NW_FOS_LVL7, 32000);

define(NW_LAND, 30);

define(NW_MIL_RANGER, 4.0); //vorher 3.816
define(NW_MIL_RANGER_PBF, 4.0); //vorher 4.012
define(NW_MIL_MARINES,4.0); //vorher 3.978
define(NW_MIL_MARINES_PBF,4.0); //vorher 4.428
define(NW_MIL_MARINES_NOF,4.0); //vorher 4.128

define(NW_MIL_ELITES_UIC, 7.0); //vorher 5.895
define(NW_MIL_ELITES2_UIC, 6.0); //vorher 6.516
define(NW_MIL_TECHS_UIC, 10.0); //vorher 8.368

define(NW_MIL_ELITES_SL, 7.0); //vorher 5.338
define(NW_MIL_ELITES2_SL, 6.0); //vorher 5.49
define(NW_MIL_TECHS_SL, 10.0); //vorher 7.769

define(NW_MIL_ELITES_PBF, 7.0); //vorher 6.741
define(NW_MIL_ELITES2_PBF, 6.0); //vorher 5.994
define(NW_MIL_TECHS_PBF, 10.0); //vorher 8.109

define(NW_MIL_ELITES_NEB, 7.0); //vorher 4.93
define(NW_MIL_ELITES2_NEB, 6.0); //vorher 6.11
define(NW_MIL_TECHS_NEB, 10.0); //vorher 7.74

define(NW_MIL_ELITES_NOF, 2.0); //vorher 2.0
define(NW_MIL_ELITES2_NOF, 6.0); //vorher 6.00
define(NW_MIL_TECHS_NOF, 10.0); //vorher 8.6666

define(NW_SPY, 2.7); //vorher 2.718

define(NW_SPY_SL, 2.9); //vorher 2.934

define(NW_SPY_NEB, 2.2); //vorher 2.2
define(NW_SPY_OFF_NEB, 1.4); //vorher 2.2 * 2/3


// BAUZEITEN
define(MINBAUZEIT_GEB,3); // Gebï¿½de braucht min 3 h
define(MINBAUZEIT_MILSPY,6);
define ("BUILDTIME_MIL",20); // Zeit die Militï¿½einheiten Standardmï¿½ig zum bau brauchen
define ("BUILDTIME_SPY",20); // Zeit die Spione Standardmï¿½ig zum bau brauchen
define ("BUILDTIME",20); 	// Zeit, die Land/Gebï¿½de zum Bau brauchen

define(DUR_FOS_LVL1, 4);
define(DUR_FOS_LVL2, 8);
define(DUR_FOS_LVL3, 12);
define(DUR_FOS_LVL4, 24);
define(DUR_FOS_LVL5, 36);
define(DUR_FOS_LVL6, 48);
define(DUR_FOS_LVL7, 60);

// SYNDIKAT
define(MAX_SCHULDENSATZ_PRO_WOCHE, 500);


// VACATION
define(VACATION_MINDAYS, 3);
define(VACATION_MINDAYS_ATWAR, 5);


define ("UIC_INDUSTRIAL_COSTBONUS",0); // 30% gÃ¼nstigere Forschung (30) // 0 seit runde 30
define ("UIC_BUILDINGS_SPEEDBONUS",0.20); // Gebï¿½ude 5h schneller - 25%
define ("UIC_SPIES_SPEEDBONUS",0.00); 	   // Spione 5h schneller

define ("NEB_MORE_UNITS_HA",2); //neb hat 2 units mehr je ha

//Millakas
define(SCHOOLTIME,5); // Zeit, die Einheiten brauchen um von Milit?rakademien ausgebildet zu werden
define(MIL13BONUS_FASTER_SCHOOLS,1); //frd boni


// Bï¿½RSE
define(MINDESTAKTIENKURS,500); // Mindestaktienkurs, damit es da nicht mehr zu problemen kommt.
define(MAXAKTIENKURS,20000); // Maxaktienkurs, damit es da nicht mehr zu problemen kommt.
define(AKTIEN_PREVENTOPTION,10); // R4bbiT - 06.09.12 - Ab 10% Aktienschutz
define(AKTIEN_SYNDSCIENCEREADOPTION,101); // R4bbiT - 19.03.12 - 101%, somit ist die Einsicht in die Synforschungen deaktiviert. Zum Reaktivieren einfach Wert auf x% setzen
define(AKTIEN_AKTUELLES,101); // R4bbiT - 19.03.12 - 101%, somit ist die Einsicht ins Aktuelle deaktiviert. Zum Reaktivieren einfach Wert auf x% setzen
define(AKTIEN_DIVIDENDEN,0.5);
define(AKTIEN_STARTKURS,3000);
define(MAXANZAHL_AKTIENDEPOTS,12); // Man darf von hï¿½hsten 15 unterschiedl Syns aktien haben!
define(MAXSELL_DAY,20000000);
define(MINPREIS_MAKLER, 3000); // R4bbiT - 19.03.12 - Mindestpreis fï¿½r den Makler, wenn er Aktien anbietet

define (AKTIEN_GLOBALSELLMALUS,2); // 2% Verlust je angefangenem Spieltag seit Rundenstart
define(PRESIDENT_BONUS,5); # 5% Bonus fr Prï¿½identen auf Ressourcenproduktion

define(AKTIEN_BASIS, 0); // Die Basismenge der Aktien - R4bbiT - 24.10.10
define(AKTIEN_BASIS_SPIELER, 0); // Die Basismenge der Aktien fï¿½r die Spielerausgabe - R4bbiT - 24.10.10
define(MAXRANGE_AKTIEN, 30); // Maximal +-30% fï¿½r die Aktiengebote - R4bbiT - von 20 auf 30 - 24.10.10
define(MAXTIME_AKTIEN, 72); // Maximaldauert fï¿½r Aktien-boni-ï¿½berlegen = 72h
define(AKTIEN_PIC_SYNVIEW, 1); // ab 1% wird das Bild fï¿½r Aktieninhaber in der Synï¿½bersicht angezeigt
define(DURCHSCHNITT_NUM, 900); // Die letzten 900 Kurse werden als Durchschnittskurs genommen
define(BOERSE_SPERRE, 0*24*60*60); //Man kann erst nach 3 Tagen an die Bï¿½rse
define(AKTIEN_MINTIME, 5); // Mindestens 5 Minuten fï¿½r ein Angebot - R4bbiT - 05.09.10
define(AKTIEN_MAXTIME, 20); // Maximal 20 Minuten fï¿½r ein Angebot - R4bbiT - 05.09.10
define(MAXGEBOTE_AKTIEN, 15); // Maximal 15 Gebote - R4bbiT - von 5 auf 15 - 24.10.10
define(AKTIEN_PRO_LAND, 10); // Pro Land gibts 10 Aktien
define(SELL_BLOCK, 1); // 1h Sperre fï¿½r das Verkaufen der Aktien von dem Syn, von dem man gekauft hat
define(AKTIEN_KAPA_HA, 20);
define(AKTIEN_KAPA_INVEST,5);
define(MIN_AKTIEN_1, 30000); // In der ersten Woche braucht man min. 30.000 Aktien/Tag als Syn - R4bbiT - 23.03.12
define(MIN_AKTIEN_2, 10000); // In den folgenden Tagen dann nur noch 10.000 Aktien/Tag als Syn - R4bbiT - 23.03.12
define(MIN_AKTIEN_DAY, 7); // Nach 7 Tagen wird Faktor 2 genommen - R4bbiT - 23.03.12
define(STEUERN_AKTIEN, 0.5); // 50% Steuern fï¿½r die Mehreinnahme von Aktien - R4bbiT - 23.03.12




// Boni von Forschungen hier deklarieren, falls in mehr als einer Datei gebraucht:
define (GLO4BONUS,4); // 4 Spione mehr durch Improved Spylabs (used in militï¿½seite,market)
define (GLO5BONUS,0.40); // FOrschungen 40% billiger 36htag
define (SYNPOT,2.5); // unmodifizierter bonus fr synergieffekte
define(SYNERGIEMAX,35); // max 35% gebï¿½de wirken fr den synergiebonus
define (IND10BONUS,0.42); // wird zu synpot dazugezï¿½lt, wenn forschung
define (AKTIENAUSGABE,2); // Es werden insgesamt Syndikatsland * 2 Aktien ausgegeben
define (IND10BONUS_PROD,100); //eco gibt 100% auf alles ressis


define (SYNFOS_TRADE_OWN, 40);
define (SYNFOS_TRADE_OTHER, 4);
define (SYNFOS_ISESP_OWN, 75);
define (SYNFOS_ISESP_OTHER, 5);
define (SYNFOS_ISSDN_OWN, 10);
define (SYNFOS_ISSDN_OTHER, 1);

// Tabellennamen

define (WARTABLE, "wars");


define (PRAESIBONUS, 5);	# Prï¿½identen produzierne 5% mehr Ressourcen
// --> In Subs existiert eine MAX_USERS_A_SYNDICATE Variable, diese ist momentan auf 25 gesetzt
// IDS fr unsere Kosttools:

// FEATURES
define (FORSCHUNGSQ, 8);
define (GEBAEUDEQ, 10);
define (GEBAEUDEQEX, 20);	//geb xt
define (MILITAERQ, 9);
define (WERBUNG_DEAKTIVIERT, 7);
define (KOMFORTPAKET, 11);
define (ANGRIFFSDB, 12);


// MILITï¿½RSEITE
define ("SPY_PRICE_PARTNERBONUS",0.15); // Spione 15% billier pro Partnerlevel
define ("PB_IP_GAIN",0.5);
define(PARTNER_EINHEITENBAUZEITBONUS,2); // Einheiten 2 h schneller bauen
define(PARTNERBONUS_OFFSPECS, 6000); # Stï¿½ckzahl
define(PARTNERBONUS_DEFSPECS, 4000); # Stï¿½ckzahl
define(PARTNERBONUS_FORSCHUNG_BESCHLEUNIGEN, 24); # in Stunden;


// Markt
define (MAXANZAHL_GEBOTE,5);
define(MARKET_BUILD_TIME,60*60*10); // Einheiten die auf dem Markt gekauft werden, sind nach 10 Stunden verfgbar

// SPIONAGE
define(MAXSPYOPS, 15);
define(GLO6BONUS, 10); // Stretch Time
define(GLO10BONUS_ADD_OPS,10); //fsr st
define (GLO1BONUS,10); // spione 10% stï¿½rker
define (GLO9BONUS,20); // spione 10% stï¿½rker bei def gegen SPIONAGE wenn spyweb
define(GLO2BONUS,15); // 15% weniger verluste
define(GLO16BONUS, 20); // Durch Capacity Augmenation bis zu 20% hï¿½here Maxgets
define(CAPACITY_AUGMENTATION_BONUS, GLO16BONUS); 

define (MAXSPYOPS_PARTNERBONUS,10); // Pro Partnerbonus kï¿½nen is zu 10 mehr spyactions gespeichert werden.
define (SPYSTRENGTH_PARTNERBONUS,0.1);
define(LOSSES_PARTNERBONUS,20); // 20% Weniger Verluste pro Level

// Spionage - GHZs
define(SECCENTERBONUS2,1.5); // Spione stï¿½rker durch seccenters
define(SECCENTERBONUS3,1.5); // Weniger verluste durch seccenters (1,5%)

define (WAR_CAPACITY_STEAL_BONUS,66); // Im Krieg kï¿½nnen bis zu 66 % Mehr gestohlen werden
define (GEGENSPIONAGE_WKT,50); // WKT fï¿½r gegenspionage
define (GEGENSPIONAGE_WKT_GLO21,15); // 15% WKT fï¿½r Counter Intelligence 
define (SLOFFDEFBONUS,0.3); // 30% Off / defbonus fï¿½r sl
define (NOFDEFBONUS,0.0); // 20% Defbonus fï¿½r nof seit runde 30


define (RACHERECHT_ON_SPYACTIONS_NUMBER, 5);
define (SPYPROTACTIONS, 5); // Anzahl aktionen nach denen gbprot fr spione einsetzt
define (SPYPROTACTIONS_WAR, 15); // Anzahl aktionen nach denen gbprot fr spione einsetzt
define (SPYPROT_PERC, 3); //nach 5/15 ops bekommt man 3% sydefbonus pro op

define (SPYLOSSES,1); // 1% Spione verloren bei failed action
define (SPYMINLOSSES,1); // Min 1% Spione verloren bei failed action (nicht aktiv)
define (SPYMAXLOSSES,3); // Max 3% Spione verloren bei failed action

define (GLO14BONUS_OP_PER_HA,12); //ctp gibt 12op/ha
define (GLO14BONUS_DP_PER_HA,18); //ctp gibt 18dp/ha
define (GLO14BONUS_IP_PER_HA,18); //ctp gibt 18ip/ha

define(STEAL_MAX_CREDITS,15); // Es kï¿½nen maximal 12% Credits gestohlen werden
define(STEAL_MAX_METAL,15); //Werte fï¿½r R45 verdoppelt
define(STEAL_MAX_ENERGY,15); 
define(STEAL_MAX_SCIENCEPOINTS,15);
define(KILL_MAX_KILLBUILDINGS,2);
define(KILL_MAX_KILLUNITS,2); 

// SPionage - benï¿½tigte Spionageaktionen
define (STEALACTIONS, 2); // Anzahl der Spionageanktionen, die Diebstahl benï¿½tigt.
define (KILLSCIENCESACTIONS,15); // Anzahl Spionageaktionen, die killsciences benï¿½tigt // Seit Runde 42 wieder 15 aktionen, vorher 10 
define (KILLBUILDINGSACTIONS,3); // Anzahl der Spionageaktionen, die killbuildings benï¿½tigt.
define (KILLUNITSACTIONS,3); //Anzahl der Spionageaktionen, die killunits benï¿½tigt.
define (DELAYAWAYACTIONS,5); // Anzahl Spionageaktionen die fï¿½r delayaway benï¿½tigt wird

// Absolute Spionageklauwerte
define(STEAL_OS_CREDITS,60);
define(STEAL_OS_METAL,12);
define(STEAL_OS_SCIENCEPOINTS,4);
define(STEAL_OS_ENERGY,60);
define(STEAL_MAX_POD,2); //2% des lagerguthaben
define(KILL_OS_KILLUNITS,0.015);
define(KILL_OS_KILLBUILDINGS,0.003);
define(PODSAVEPERLAND,50000); //50.000hp save

define(STEAL_ISDS_CREDITS,30);
define(STEAL_ISDS_METAL,6);
define(STEAL_ISDS_SCIENCEPOINTS,2);
define(STEAL_ISDS_ENERGY,30);
define(KILL_ISDS_KILLUNITS,0.0075);
define(KILL_ISDS_KILLBUILDINGS,0.0015);


// LAGER
define (RESSTATS_MODIFIER, 1); // Es werden jeweils nur 90% der aktuellen Marktpreise verwendet
						// R57 Lager ist gleich dem GM


// FORSCHUNGEN
define('LATER_STARTED_BONUS_DAILY', 16);
define('LATER_STARTED_BONUS_START', 48);
define (ISTP_CHANGETIME,3);
define(PBF_DEFSPECBONUS, 4); // Pbf Ranger produzieren 5 Energie und speichern entsprechend das 10fache
define (NEB_SCIENCE_MALUS,0.0); // NEB zahlt 25% mehr fr forschungen // Runde 30 deaktiviert
define(IND13WERT,1); // Jede stunde wird dieser wert an land dazuaddiert, wenn man forschung ind13 hat
define(IND6BONUS, 0.4); // Gebï¿½de 10% billiger
define(IND2BONUS,10);
define("IND3BONUS",2); // Gebï¿½de werden 5 Stunden schneller gebaut
define(IND3BONUS_CHEAPER, 0.1); // Gebï¿½de 10% billiger
define("IND5BONUS",4); // Spione und Mileinheiten 3h schneller pro level ind5 - ist im update auch noch drin!
define ("MIL8BONUS",5); // 5 Einheiten mehr durch better space management (used in: militï¿½seite,market, update)
define ("IND8BONUS",10); // 10% billigere Einheiten, cheaper unit prod
define ("GLO3BONUS",15); // 15% billigere Spione, cheaper spy prod
define(GLO25BONUS, 0.8); // Forschungen 20 Prozent billiger
define(IND22BONUS_ZINSEN, 5); //iw reduziert lagersteuern auf 5%
define(IND9BONUS,10); // 10% Bonus fr ind9 science
define(IND9BONUS_OTHER,5); // 10% Bonus fr ind9 science
define(GLO10BONUS_MONEY_GAIN_HA,100); //fsr gibt 100cr/ha
define(GLO10BONUS_MONEY_GAIN_SPY,3.5); //fsr gibt 3.5cr/spoy
define("PARTNER_ALLBONUS",20);

define(MAXLANDPREISGET,150000); // WIeviel man maximal pro Hektar land an geld zurï¿½ckbekommt
											#### (Runde 21 eig. abgeschafft, aber 20 Mio sollten nicht so schnell erreich werden kï¿½nnen)
define (LAND_ERSTATTUNG_FAKTOR,0.60);

define (LAND_SHREDDER_PER_PERCENT_ADD_HA, 200); //alle 200ha 1% mehr shreddern
define (LAND_SHREDDER_PER_PERCENT_MAX_HA, 12000); //bis 12k ha = 60%
define (LAND_SHREDDER_PER_MAX_HA, 250000); //bis 250k/ha
// AUFTRï¿½GE
define (AUFTRAGTIME,60*10); // 30 Minuten
define (WAITTIME,60*10); // 30 Minuten standard warten
define (WAITRANDOM,60*10); // Nochmal bis 30 min zufï¿½lig
define (OUTTIME,60*60*24*3); // Nach 3 Tagen wird Auftrag nicht mehr angezeigt
define (SHOWGROUPS_COUNT,1); // Es gibt 3 verschiedene Gruppen, denen Auftrï¿½e angezeigt werden. -> 20% der Spieler


// GEBï¿½UDE
## Produktionen ##
## Credits ##
define(TRADECENTER_PRODUCTION,250); // normales Gebï¿½ude
define(S_TRADECENTER_PRODUCTION, 200); // Syngebï¿½ude
define(MULTI_CR_PRODUCTION,225); // Nanofabrik fï¿½r UIC
## Energie ##
define(POWERPLANT_PRODUCTION,250);
define(S_POWERPLANT_PRODUCTION,200);
define(MULTI_ENERGY_PRODUCTION,225);
## Forschungspunkte ##
define(SCIENCELAB_PRODUCTION,19);
define(S_SCIENCELAB_PRODUCTION,15);
define(MULTI_FP_PRODUCTION,17);
## Erz ##
define(RESFAC_PRODUCTION,50);
define(S_RESFAC_PRODUCTION,40);
define(MULTI_METAL_PRODUCTION,45);

define(DEPOTWERT,12); // Anzahl der Units die auf ein Hektar Land passen
define(DEPOTWERT_SPIONE, 3);
define(SPYLABSWERT,15); // Anzahl der Spione die auf ein Hektar Land passen
define(SPYLABSWERT_MILITAER, 3);
define(ECOCENTERBONUS,8); // Bonus von Ecocenters fr neb bei 1 Prozent bebauten landes
define(ARMORYPROD_PER_WEEK,4); // Armories produzieren 5 Angriffspunkte pro Stunde pro vergangener Woche
define(ARMORYSAFE_PER_WEEK,100);
define(BANKENSAVE , 1000000);
define(BANKENMAXSAVE ,100);
define ("FACTORYBONUS",2.5); // prozent, um die einheiten durch ein prozent factories billiger werden
define ("SECCENTERBONUS",1.5); // Spione 2% billiger pro land mit seccenters


// LAND
define(LANDWERT,12); // Anzahl der Einheiten die auf ein Hektar Land passen
define(LANDWERT2,12); // Anzahl der Spione die auf ein Hektar Land passen
define(GLO12BONUS_SPY_PER_HA,1); //issdn +1 spy /ha /stufe

define(LANDKAUFMAX,1); // Maximal Land verdoppeln!
define(LANDKAUFMAX_RAW, 1000);
$landkaufmaxabsolut = LANDKAUFMAX_RAW;
//if ($status[race] == "neb"): $landkaufmaxabsolut = LANDKAUFMAX_RAW * 1.5; endif;
define(LANDKAUFMAXABSOLUT,$landkaufmaxabsolut);


// KRIEGPRï¿½MIE
define(WAR_PUNISHMENT,0.25); // % vom Land, die beim Krieg dem Gegner zugeschrieben werden, wenn man in den u-mod geht oder resettet
define (KRIEGSPRAEMIE_TAGFAKTOR, 600); # Basiswert fr Kriegsprï¿½ie pro Tag
define (KRIEG_MONU_ZERSTOERUNG_MINDESTPROZENT_LAND_EROBERT, 0); #ab 0% netto erobert wird das monu zerstï¿½rt
define (KRIEG_MONU_EROBERUNG_MINDESTPROZENT_LAND_EROBERT_BRUTTO, 0.08); #ab 8% brutto erobert wird das monu erobert
define (KRIEG_MONU_EROBERUNG_MINDESTPROZENT_LAND_EROBERT_NETTO, 0); #ab 0% netto erobert wird das monu erobert


// Ressourcen Standardpreise
define(SCIENCEPOINTS_STD_VALUE,16 / 0.9);  // 20 / 0.9
define(METAL_STD_VALUE,6/0.9); // 6 / 0.9
define(ENERGY_STD_VALUE,1.2 / 0.9); //1.2/ 0.9
define(CREDIT_STD_VALUE,1);

// Die ressourcenwerte werden fr das istp noch verwendet -> daher kï¿½nen wir nicht pauschal durch 0,9 teilen..
// die Tradeboni werden auch für Event-boni verwendet!!
define(SCIENCEPOINTS_STD_VALUE_TRADE,16);  // 20 / 0.9
define(METAL_STD_VALUE_TRADE,6); // 6 / 0.9
define(ENERGY_STD_VALUE_TRADE,1.2); //1.2/ 0.9

// LAGER
define (ZINSEN,15);
define (ZINSEN_CREDITS,15); // Seit Runde 42 auch 15 Creds
define (GLOBALISIERUNGSMASTERPLAN_ZINSEN, 0.15); // pro Stunde soviel (in Prozent), dass pro Woche 15% dazukommen

// PARTNERSCHAFTSBONI
define(ANZAHL_VERSCHIEDENER_PARTNERSCHAFTSBONI_SETTING, 15); // Es gibt derzeit 18 Stck
define(ZEIT_VOR_RUNDENENDE_ZU_DER_PARTNERSCHAFTSBONI_NEU_BESTIMMT_WERDEN, 604800); // 7 Tage derzeit
define(ZEIT_VOR_RUNDENENDE_ZU_DER_MONUMENTE_NEU_BESTIMMT_WERDEN, 24*60*60); // 1 Tage derzeit

// ARTEFAKTE
define(BUCHUNGSBETRAG_TICK,75000); // Pro Stunde kann jedes Synmitglied 75k fp bezahlen
define(KOSTEN_ARTEFAKT,12000000);
define(MAX_BUILD_TIME_ARTEFAKT,48); //nach dieser zeit in ticks wird das monu wieder zerstï¿½rt //nur in der db
define(WAIT_TIME_AFTER_ARTEFAKT_ABORT,72); //nach dieser zeit kann wieder ein monu gebaut werden
define(ARTEFAKT_COST_PER_DAY_PER_WEEK, 7000000);
define(ARTEFAKS_PER_TYPE,2);
define(PBS_PER_TYPE_CHOOSEABLE,2);

// Board/Forum IDs
define("BOARD_ID_FAQ", 88888);
define("BOARD_ID_OFFSET_GRUPPEN", 100000);
define("BOARD_ID_OFFSET_MENTOREN", 40000);
define("BOARD_ID_OFFSET_ALLIANZ", 20000);
define("BOARD_RUNDENUEBERGANG_MAX_USER_OFFSET", 100000); // muss größer sein als die höchste user_id!!!! (im Moment ca. 77000), nur während der Runde ändern!!! (in update.php, create.php verwendet)

// Umfragen GruppenID
define("POLL_ID_FAQ", 88888);				// nicht verwendet
define("POLL_ID_OFFSET_GRUPPEN", 100000);	
define("POLL_ID_OFFSET_MENTOREN", 40000);	// nicht verwendet
define("POLL_ID_OFFSET_ALLIANZ", 20000);	// nicht verwendet

// GRUPPEN
define("BOARD_GRUPPEN_VOR_ENDE_FREISCHALTEN", 604800); // 7 * 24 * 3600

// Fraktionsspezifische Boni

	// UIC
	define(UIC_PAUSCHAL_RESSOURCENBONUS,20); // Uic bekommt pauschal 20% produktionsbonus auf alle ressourcen
	// NEB
	// PBF
	// SL
	// NOF
	define(NOF_BUILDTIME_MODIFIER,0.2); // 4 Stunden schneller - in prozent der std bauzeit


// SESSION

define(SESSION_DAUER, 3600); #60 Minuten


////////////////////////////////////////
/////////////////////////////////////////////////////
// ANGRIFF
/////////////////////////////////////////////////////
////////////////////////////////////////


	// Landgain
		define(MAXLANDGAIN,20); // in %
		define(MAXLANDGAIN_RR,25);  // nicht überall in angriff.php verwendet
		define(HOURS_FOR_LANDGAIN, 20); # Neb Land ist  schneller da 
		define(NEB_HOURS_FOR_LANDGAIN, 20); # Neb Land ist  schneller da 
	

	// Defaults
		define( STANDARDAWAYTIME, 20);					
		define( MAX_ATTACKTIME_REDUCTION,14); // Man kann Angriffsdauer hï¿½chstens um 14 Stunden reduzieren
		define( BASE_UNIT_LOSS_A, 10);	
		define( BASE_UNIT_LOSS, 10);		# Grundzahl Unitloss in % * 100;
		define( MAX_LOSS_BONUS, -90);    # Man kann Einheitenverluste maximal um 90% reduzieren
		
		define( DEFENSE_UNIT_MIN_DP, 10); // Einheiten mit mindestens 7 VP zï¿½hlen fï¿½r die 2:1 Regel als Verteidigungseinheiten 

		//units
		define (UIC_RW_LOSS_SPECIAL_FULL, 25); //weniger verluste bei 100% rws in prozent (25%)
		
	// Gebï¿½ude 
		// VP / VP
			define( OFFTOWER_BONUS,3); // Aussenposten geben 3% Angriffsbonus pro % bebautem Land
			define( OFFTOWER_MAX_BONI, 60);
		
			define( DEFTOWER_BONUS, 3); // Forts geben 2,5% Verteidigungsbonus pro % bebautem Land
			define( DEFTOWER_MAX_BONI, 60);
		// Losses
			define( WORKSHOP_PROZENT_BONUS, 2.5); // NOF erhï¿½lt fï¿½r jedes Prozent Werkstï¿½tten 2,5% Loss-Bonus
		// Time
			define( RADAR_BONUS, 1); // Fï¿½r je 1% Radaranlagen kommen Units 1h schneller heim 
	
	
	// Fraktionen
		// AP / VP
			define( PBF_DEFENSE_BONUS_PER_PBFLAND,0);
			define( PBF_DEFENSE_PBFLAND,0); // Alle 300 Land bekommt PBF 1% Defbonus
			define( PBF_DEFENSE_BONUS_MAX,0); // Max 30 Prozent verteidigungsbonus
			define( PBF_ATTACK_BONUS, 10);           # Brute Force Rassenangriffsbonus //
			define( NOF_MARINE_HA_BARRIER_FOR_OP_PLUS, 2000); // NOF-Marine bekommt alle 2000 ha ein OP
			define( NOF_MARINE_MAX_PLUS_OP, 0);	// NOF Maximal +3 AP
			define( NOF_DEFENSE_BONUS,0);
			
			define( PBF_TITAN_MARINE_SUPPORT_NUMBER,2); // Titan unterstï¿½tzt 2 Marines
			define( PBF_TITAN_MARINE_SUPPORT_BONUS,4); // Titan verstï¿½rkt 4 AP bei marines
			define( PBF_TITAN_RANGER_SUPPORT_NUMBER,2); // Titan unterstï¿½tzt 2 Marines
			define( PBF_TITAN_RANGER_SUPPORT_BONUS,4); // Titan verstï¿½rkt 4 vp bei Rangern
			
			define( NEB_PATRIOT_RETRUNINGUNITS_BONUS,9); // Ein Patriot erhï¿½lt je heimkehrende Unit + 9VP
			
		// Losses/Gains
			define( PBF_LANDGAIN_BONUS, 10);	# Landgain Rassenbonus fï¿½r Brute Force
			define( PBF_LOSS_BONUS, 20);		# Grundbonus fï¿½r Brute Force weniger Losses in % * 100;
			define( NEB_LANDLOSS_BONUS,0); // Neb verliert seit runde 30 15% weniegr land
		// Time
			define( PBF_ATTACK_DURATION_BONUS, 2 * 60); # 20% respektive 4h Angriffsbonus # Abgeschafft Mai 2006 / Runde 21 # wieder eingefï¿½hrt runde 30, august 2007
			define( NOF_ENEMY_ATTACKTIME_MALUS, 0 * 60); // Gegnerische Einheiten brauchen bei Angriffen gg nof 2h lï¿½nger
			
	// Partner
		// AP / VP
			define( PARTNER_DEFBONUS,10); // Partnerschaftsbonus fï¿½r Verteidigung gibt +5% Def
			define( PARTNER_OFFBONUS,10); // 10% Angriffsbonus fï¿½r Partnerbonus
			define( PARTNER_SYNARMEESUPPORT, 5); // 10% Syndikatsarmeeuntrstï¿½tzung
			define( PARTNER_SYNARMEESUPPORT_WAR, (2/3) * 10); // 10% effektive Unterstï¿½tzung im Krieg (wird spï¿½ter noch mit 1.5 multipliziert
		// Losses/Gains
			define( PARTNER_LANDGAINBONUS, 10); // 10 % Landgainbonus ï¿½ber Partnerbonus
			define( PARTNER_LANDLOSSBONUS, 10); // 5% weniger Landverlust bei verlorenem Angriff
			define( PARTNER_MILLOSSBONUS, 10); // 10% weniger Milverluste bei verlorenem Angriff
		// Time
			define( PARTNER_AWAYTIME,2); // Partnerbonus fï¿½r Milaway: 2 Stunden
			
			
	// Bash-Protection
		define(TIME_RELEVANT_FOR_BASH_PROTECTION, 60*60*24); // Angriffe der letzten 24h werden fï¿½r Bashschutz in betracht gezogen
		define(BASH_PROTECTION_1_ATTACKS_NEED,1);
		define(BASH_PROTECTION_2_ATTACKS_NEED,2);
		define(BASH_PROTECTION_1_GAIN,75);
		define(BASH_PROTECTION_2_GAIN,50);
		define(BASH_PROTECTION_FOR_CONQUER,75);
		//define(BASH_PROTECTION_1_LANDLOSS_REQUIRED,10); // Ab 10% verlorenem Land setzt einfacher Bashschutz ein:
		//define(BASH_PROTECTION_2_LANDLOSS_REQUIRED,20); // Ab 20% verlorenem Land im Zeitraum setzt dopelter Bashschutz ein
		//define(BASH_PROTECTION_FACTOR_OWN,60);			// 60% weniger gains, wenn man den gleichen Gegner 2 mal angreift
		//define(BASH_PROTECTION_FACTOR_FOREIGN,25);		// 25% weniger gains, wenn man einen gegner angreift, der schonmal angegriffen wurde

 	// Racherecht
		define(RACHERECHTTIME,60*60*24);

	// Forschungen
		// AP / VP
		define (MIL1BONUS_BASIC_OFFENSE, 5); // 5% Angriffbonus
		define (MIL2BONUS_BASIC_DEFENSE, 5); // 5% Angriffbonus
		define (MIL5BONUS_RANGER_AND_MARINE, 1); // Ranger & Marine Training in ganzer Zahl;
		define (MIL6BONUS_FLEX_STRAT, 8); // // Flexible Strategies in ganzer Zahl;
		define (MIL7BONUS_DEF_NETWORK, 15); // Defense Network vp+ in %;
		define (MIL12BONUS_IWT, 10); // 10% Angriffsbonus
		//define (MIL13BONUS_RANGER_UPGRADE, 2); // +2 VP fï¿½r NEB Ranger
		define (MIL15BONUS_SYNARMY, 10); // 10% Unterstï¿½tzung durch Synarmee
		define ('MIL15BONUS_FACTOR_SYNARMY_ATWAR', 1.5);
		define (GLO8BONUS_ORBITAL,5); // 5% Defbonus Orbital Defsystem
		
		// Losses/Gains
		define (MIL3BONUS_PROPAGANDA, 5); // 5% Landgain
		define (MIL9BONUS_HARDEN_ARMOR, 20); // 20% weniger losses
		define (MIL12BONUS_SECOND_IWT, 5); // 5% Gegnerverluste
		define (MIL14BONUS_FOG_OF_WAR, 20); // 20% Weniger Unit losses
		define (MIL14BONUS_SECOND_FOG_OF_WAR, 20); // 20% Weniger Land losses
		define (MIL14BONUS_UNITS_VP_EXTRA,2); //units kriegen 2 vp mehr
		define (GLO8BONUS_SECOND_ORBITAL,10); // 10% Weniger Landloss Orbital Defsystem
		
		
		// Time
		define (MIL4BONUS_COMBAT_MGMT, 1); // 1h schneller
		define (MIL10BONUS_RELENT_ASSAULT, 7); // 7h schneller
		define (MIL10BONUS_AP_BONUS,20);//+20%ap
		define (MIL10BONUS_LANDGAIN_BONUS,20);//+20% landgain
		define (MIL10BONUS_ADDITIONAL_ATTS,7);//+7attacks mehr pro tag
		define (MIL14BONUS_THIRD_FOG_OF_WAR, 4); // +4h Rï¿½ckkehrzeit gegner
		
	// Overcharge
	define(MAX_OVERCHARGE, 50); // 20 PROZENT MAXMIAL OVERCHARGEBAR;

			
define('OMNIMON_USER_STD','emogames');
define('OMNIMON_USER_MASS','SomeOtherUser');




?>
