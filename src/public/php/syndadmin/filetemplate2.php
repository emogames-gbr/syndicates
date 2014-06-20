<?php
require("php/subs.php");
connectdb();

$i=1;
for ($i=1;$i <= 1;$i++) {
	$statsdata = assocs("select * from stats_round_".$i);
	$a = 0;
	foreach ($statsdata as $key => $value) {
		if ($a==0) {
			foreach ($value as $bla => $blub) {
				if ($bla == "attacksdone") {$bla = "attacks_numberdone_normal";}
				if ($bla == "attacksdonewon") {$bla = "attacks_numberdone_won_normal";}
				if ($bla == "attackssuffered") {$bla = "attacks_numbersuffered_normal";}
				if ($bla == "attackssufferedlost") {$bla = "attack_numbersuffered_lost_normal";}
				if ($bla == "largestgrab") {$bla = "attack_largest_won_normal";}
				if ($bla == "largestgrabsuffered") {$bla = "attack_largest_loss_normal";}

				if ($bla != "sciencesdone") {
					$idents.="$bla,"
				}
				$a=1;
			}
			$idents = chopp($idents);
			$query = "insert into stats ($idents) values ";
		}
		$query.="(";
		foreach($value as $tkey => $tvalue) {
			$query.="$tvalue";
		}
		$query = chopp($query);
		$query.=")";
		echo $query;

	}
	unset($statsdata);
}



?>