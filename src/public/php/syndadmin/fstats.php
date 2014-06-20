<?php
require("../subs.php");
connectdb();

$i=1;
for ($i=1;$i<=1;$i++) {
	$statsdata = assocs("select * from stats_round_".$i);
	$a = 0;
	foreach ($statsdata as $key => $value) {
		if ($a==0) {
			foreach ($value as $bla => $blub) {
				if ($bla == "attacksdone") {$bla = "attack_numberdone_normal";}
				if ($bla == "attacksdonewon") {$bla = "attack_numberdone_won_normal";}
				if ($bla == "attackssuffered") {$bla = "attack_numbersuffered_normal";}
				if ($bla == "attackssufferedlost") {$bla = "attack_numbersuffered_lost_normal";}
				if ($bla == "largestgrab") {$bla = "attack_largest_won_normal";}
				if ($bla == "largestgrabsuffered") {$bla = "attack_largest_loss_normal";}
				if ($bla == "landwon") {$bla = "attack_total_won_normal";}
				if ($bla == "landlost") {$bla = "attack_total_loss_normal";}

				if ($bla != "sciencesdone" && $bla != "id") {
					$idents.="$bla,";
				}
			}
			$idents.="round,";
			$idents = chopp($idents);
			$query = "insert into stats_real ($idents) values ";
			$a=1;
		}
		$query.="(";
		foreach($value as $tkey => $tvalue) {
			if ($tkey != "sciencesdone" && $tkey != "id") {
				$query.="'$tvalue',";
			}
		}
		$query.="$i,";
		$query = chopp($query);
		$query.="),";

	}
	$query = chopp($query);
	//echo $query;
	select($query);
	unset($statsdata);
}



?>
