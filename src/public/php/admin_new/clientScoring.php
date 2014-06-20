<?php

include("inc/general.php");

$i=0;
$alleComments = assocs("select comment from clientScoring where comment != 'stop' and comment != ''");
foreach($alleComments as $item){
	echo ++$i.".<br>".str_replace("\n", "<br>", $item['comment'])."<br><br>";
}
//totals

$total = single("select count(*) from clientScoring");
$totalDone = single("select count(*) from clientScoring where comment!='stop'");
$totalNotDone = $total - $totalDone;

//gender

$GENDERabsMale = single("select count(*) where gender=1");
$GENDERabsFemale = single("select count(*) where gender=2");
$GENDERabsNothing = single("select count(*) where gender=0 and comment !='stop'");

$GENDERrelMale = round($GENDERabsMale / $totalDone * 100, 2);
$GENDERrelFemale = round($GENDERabsFemale / $totalDone * 100, 2);
$GENDERrelNothing = round($GENDERabsNothing / $totalDone * 100, 2);

//age
/*0 keine angabe
1 0-18 Jahre
2 18-25 Jahre
3 25-50 Jahre
4 50+ Jahre*/

$AGEabsChild = single("select count(*) from clientScoring where age = 1");
$AGEabsStudent = single("select count(*) from clientScoring where age = 2");
$AGEabsWorker = single("select count(*) from clientScoring where age = 3");
$AGEabsOld = single("select count(*) from clientScoring where age = 4");
$AGEabsNothing = single("select count(*) from clientScoring where age = 0 and comment !='stop'");

$AGErelChild = round($AGEabsChild / $totalDone * 100, 2);
$AGErelStudent = round($AGEabsStudent / $totalDone * 100, 2);
$AGErelWorker = round($AGEabsWorker / $totalDone * 100, 2);
$AGErelOld = round($AGEabsOld / $totalDone * 100, 2);
$AGErelNothing = round($AGEabsNothing / $totalDone * 100, 2);

//milieu
/*0 keine angabe
1 ...Schüler
2 ...Student
3 ...erwerbstätig
4 sonstiges*/

$MILIEUabsSchool = single("select count(*) from clientScoring where milieu = 1");
$MILIEUabsStudent = single("select count(*) from clientScoring where milieu = 2");
$MILIEUabsWorker = single("select count(*) from clientScoring where milieu = 3");
$MILIEUabsElse = single("select count(*) from clientScoring where milieu = 4");
$MILIEUabsNothing = single("select count(*) from clientScoring where milieu = 0 and comment !='stop'");

$MILIEUrelSchool = round($MILIEUabsSchool / $totalDone * 100, 2);
$MILIEUrelStudent = round($MILIEUabsStudent / $totalDone * 100, 2);
$MILIEUrelWorker = round($MILIEUabsWorker / $totalDone * 100, 2);
$MILIEUrelElse = round($MILIEUabsElse / $totalDone * 100, 2);
$MILIEUrelNothing = round($MILIEUabsNothing / $totalDone * 100, 2);

//time_spending
/*0 keine Angaben
 1 ...höchstens 1-2 Stunden...
2 ...bis zu 5 Stunden...
3 ...mehr als 5 Stunden...*/

$SPENDINGabsLess = single("select count(*) from clientScoring where time_spending=1");
$SPENDINGabsSome = single("select count(*) from clientScoring where time_spending=2");
$SPENDINGabsMuch = single("select count(*) from clientScoring where time_spending=3");
$SPENDINGabsNBothing = single("select count(*) from clientScoring where time_spending=0 and comment !='stop'");

$SPENDINGrelLess = round($SPENDINGabsLess / $totalDone * 100, 2);
$SPENDINGrelSome = round($SPENDINGabsSome / $totalDone * 100, 2);
$SPENDINGrelMuch = round($SPENDINGabsMuch / $totalDone * 100, 2);
$SPENDINGrelNBothing = round($SPENDINGabsNBothing / $totalDone * 100, 2);

//login_times
/*+1 0-6 Uhr
+2 6-10 Uhr
+4 10-16 Uhr
+8 16-24 Uhr*/
$logins = assocs("select login_times from clientScoring where comment !='stop'");
$night=0;$morning=0;$mid=0;$after=0;
foreach($logins as $login){
	$login = $login['login_times'];
	$login -= 8;
	$after += $login >= 0 ? 1 : 0;
	$login -= 4;
	$mid += $login >= 0 ? 1 : 0;
	$login -= 2;
	$morning += $login >= 0 ? 1 : 0;
	$login -= 1;
	$night += $login >= 0 ? 1 : 0;
}

$LOGINabsNight = $night;
$LOGINabsMorning = $morning;
$LOGINabsMid = $mid;
$LOGINabsAfter = $after;

$LOGINrelNight = round($LOGINabsNight / $totalDone * 100, 2);
$LOGINrelMorning = round($LOGINabsMorning / $totalDone * 100, 2);
$LOGINrelMid = round($LOGINabsMid / $totalDone * 100, 2);
$LOGINrelAfter = round($LOGINabsAfter / $totalDone * 100, 2);

//play_reason
/*+1 ...um Spaß zu haben
+2 ...um erfolgreich im Ranking abzuschneiden
+4 ...mit Bekannten/Freunden
+8 ...zum Zeitvertreibt
+16 ...aus anderen Gründe
*/

$reasons = assocs("select play_reason from clientScoring where comment !='stop'");
$fun=0;$rank=0;$friends=0;$spending=0;$other=0;
foreach($reasons as $reason){
	$reason = $reason['play_reason'];
	$reason -= 16;
	$other += $reason >= 0 ? 1 : 0;
	$reason -= 8;
	$spending += $reason >= 0 ? 1 : 0;
	$reason -= 4;
	$friends += $reason >= 0 ? 1 : 0;
	$reason -= 2;
	$rank += $reason >= 0 ? 1 : 0;
	$reason -= 1;
	$fun += $reason >= 0 ? 1 : 0;
}

$REASONabsOther = $other;
$REASONabsSpending = $spending;
$REASONabsFriends = $friends;
$REASONabsRank = $rank;
$REASONabsFun = $fun;

$REASONrelOther = round($REASONabsOther / $totalDone * 100, 2);
$REASONrelSpending = round($REASONabsSpending / $totalDone * 100, 2);
$REASONrelFriends = round($REASONabsFriends / $totalDone * 100, 2);
$REASONrelRank = round($REASONabsRank / $totalDone * 100, 2);
$REASONrelFun = round($REASONabsFun / $totalDone * 100, 2);

//feature_direction
/*0 keine Angaben
 1 ...aggressiver werden
 2 ...friedlicher werden
3 ...so bleiben*/

$FEATUREabsAggressiv = single("select count(*) from clientScoring where feature_direction=1");
$FEATUREabsPeace = single("select count(*) from clientScoring where feature_direction=1");
$FEATUREabsNothing = single("select count(*) from clientScoring where feature_direction=0 and comment !='stop'");

$FEATURErelAggressiv = round($FEATUREabsAggressiv / $totalDone * 100, 2);
$FEATURErelPeace = round($FEATUREabsPeace / $totalDone * 100, 2);
$FEATURErelNothing = round($FEATUREabsNothing / $totalDone * 100, 2);

/*
wallet	monu	war	jobs	nubs
0 überflüssig	=	=	=	=
1-5 schlecht-gut				
*/
$things = array("wallet","monu","war","jobs","nubs");
$values = array(0,1,2,3,4,5);
$RATING=array();

foreach($things as $thing){
	$RATING[$thing] = array();
	foreach($values as $value){
		$abs = single("select count(*) from clientScoring where ".$thing."=".$value." and comment!='stop'");
		$rel = round($abs / $totalDone * 100, 2);
		$RATING[$thing][$value] = array('abs'=>$abs,'rel'=>$rel);
	}
	$avg = single("select avg(".$thing.") from clientScoring where ".$thing.">0");
	$avg = round($avg, 2);
	$RATING[$thing]['avg'] = $avg;
}


?>
