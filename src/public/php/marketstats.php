<?php 

require_once '../../inc/ingame/game.php';

if(isset($pw) && $pw == 'SOME_RANDOM_PASSWORD' && ($game['name'] == "Syndicates Testumgebung")) {//hier steht das passwort

	//form
	echo '<form name="unitstats" action="marketstats.php" method="get">'.
			'<input type="hidden" name="pw" value="'.$pw.'" />'.
			'Auswahl:<br /><select name="select[]" multiple>'.
			'<option value="sumnumber">Umgesetzte Menge</option>'.
			'<option value="avgprice">Durchschnittspreis</option>'.
			'<option value="wavgprice">Gewichteter Durchschnittspreis</option>'.
			'<option value="number">Anzahl</option>'.
			'<option value="price">Preis</option>'.
			'<option value="id">Product id</option>'.
			'</select><br />'.
			'Produkt:<br /><select name="product[]" multiple>'.
			'<option value="nrgy">Energie</option>'.
			'<option value="erz">Erz</option>'.
			'<option value="fps">Forschungspunkte</option>'.
			'<option value="marine">Marines</option>'.
			'<option value="ranger">Ranger</option>'.
			'<option value="buc">BUCs</option>'.
			'<option value="auc">AUCs</option>'.
			'<option value="huc">HUCs</option>'.
			'<option value="thiefs">Thiefs</option>'.
			'<option value="guards">Guardians</option>'.
			'<option value="agents">Agents</option>'.
			'</select><br />'.
			'Action: <select name="action">'.
			'<option value="buy">Gekauft</option>'.
			'<option value="0">Alles</option>'.
			'<option value="sell">Angeboten</option>'.
			'<option value="back">Zurückgenommen</option>'.
			'</select><br />'.
			'Von Datum:'.
			'<select name="vontag">'.
			'<option value="0" selected>--- Tag ---</option>'.
			'<option value="1">1</option>'.
			'<option value="2">2</option>'.
			'<option value="3">3</option>'.
			'<option value="4">4</option>'.
			'<option value="5">5</option>'.
			'<option value="6">6</option>'.
			'<option value="7">7</option>'.
			'<option value="8">8</option>'.
			'<option value="9">9</option>'.
			'<option value="10">10</option>'.
			'<option value="11">11</option>'.
			'<option value="12">12</option>'.
			'<option value="13">13</option>'.
			'<option value="14">14</option>'.
			'<option value="15">15</option>'.
			'<option value="16">16</option>'.
			'<option value="17">17</option>'.
			'<option value="18">18</option>'.
			'<option value="19">19</option>'.
			'<option value="20">20</option>'.
			'<option value="21">21</option>'.
			'<option value="22">22</option>'.
			'<option value="23">23</option>'.
			'<option value="24">24</option>'.
			'<option value="25">25</option>'.
			'<option value="26">26</option>'.
			'<option value="27">27</option>'.
			'<option value="28">28</option>'.
			'<option value="29">29</option>'.
			'<option value="30">30</option>'.
			'<option value="31">31</option>'.
			'</select>'.
			'<select name="vonmonth">'.
			'<option value="0" selected>--- Monat ---</option>'.
			'<option value="1">1</option>'.
			'<option value="2">2</option>'.
			'<option value="3">3</option>'.
			'<option value="4">4</option>'.
			'<option value="5">5</option>'.
			'<option value="6">6</option>'.
			'<option value="7">7</option>'.
			'<option value="8">8</option>'.
			'<option value="9">9</option>'.
			'<option value="10">10</option>'.
			'<option value="11">11</option>'.
			'<option value="12">12</option>'.
			'</select>'.
			'<select name="vonjahr">'.
			'<option value="2000" selected>--- Jahr ---</option>'.
			'<option value="2011">2011</option>'.
			'<option value="2012">2012</option>'.
			'<option value="2013">2013</option>'.
			'</select><br />'.
			'Bis Datum:'.
			'<select name="bistag">'.
			'<option value="0" selected>--- Tag ---</option>'.
			'<option value="1">1</option>'.
			'<option value="2">2</option>'.
			'<option value="3">3</option>'.
			'<option value="4">4</option>'.
			'<option value="5">5</option>'.
			'<option value="6">6</option>'.
			'<option value="7">7</option>'.
			'<option value="8">8</option>'.
			'<option value="9">9</option>'.
			'<option value="10">10</option>'.
			'<option value="11">11</option>'.
			'<option value="12">12</option>'.
			'<option value="13">13</option>'.
			'<option value="14">14</option>'.
			'<option value="15">15</option>'.
			'<option value="16">16</option>'.
			'<option value="17">17</option>'.
			'<option value="18">18</option>'.
			'<option value="19">19</option>'.
			'<option value="20">20</option>'.
			'<option value="21">21</option>'.
			'<option value="22">22</option>'.
			'<option value="23">23</option>'.
			'<option value="24">24</option>'.
			'<option value="25">25</option>'.
			'<option value="26">26</option>'.
			'<option value="27">27</option>'.
			'<option value="28">28</option>'.
			'<option value="29">29</option>'.
			'<option value="30">30</option>'.
			'<option value="31">31</option>'.
			'</select>'.
			'<select name="bismonth">'.
			'<option value="0" selected>--- Monat ---</option>'.
			'<option value="1">1</option>'.
			'<option value="2">2</option>'.
			'<option value="3">3</option>'.
			'<option value="4">4</option>'.
			'<option value="5">5</option>'.
			'<option value="6">6</option>'.
			'<option value="7">7</option>'.
			'<option value="8">8</option>'.
			'<option value="9">9</option>'.
			'<option value="10">10</option>'.
			'<option value="11">11</option>'.
			'<option value="12">12</option>'.
			'</select>'.
			'<select name="bisjahr">'.
			'<option value="2020" selected>--- Jahr ---</option>'.
			'<option value="2011">2011</option>'.
			'<option value="2012">2012</option>'.
			'<option value="2013">2013</option>'.
			'</select><br />'.
			'Sortierung:'.
			'<select name="order">'.
			'<option value="0">Nicht sortieren</option>'.
			'<option value="sortasc">Aufsteigend sortieren</option>'.
			'<option value="sortdesc">Absteigend sortieren</option>'.
			'</select>'.
			'nach'.
			'<select name="orderby">'.
			'<option value="prod_id">Produktid</option>'.
			'<option value="time">Zeit</option>'.
			'<option value="number">Anzahl</option>'.
			'<option value="price">Preis</option>'.
			'</select><br />'.
			'Gruppierung nach:'.
			'<select name="groupby">'.
			'<option value="0">Keine Gruppierung</option>'.
			'<option value="prod_id">Produktid</option>'.
			'<option value="number">Anzahl</option>'.
			'<option value="price">Preis</option>'.
			'<option value="type">Typ</option>'.
			'</select><br />'.
			'<input type="submit" name="submit" value="Los!" />'.
			'</form>';
	
	if(isset($submit)) {
		$action = mres($action);
		$vontag = mres($vontag);
		$vonmonth = mres($vonmonth);
		$vonjahr = mres($vonjahr);
		$bistag = mres($bistag);
		$bismonth = mres($bismonth);
		$bisjahr = mres($bisjahr);
		$order = mres($order);
		$orderby = mres($orderby);
		$groupby = mres($groupby);
		
		$select_rep = array('number' => 'number as Anzahl', 'price' => 'price as Preis', 'sumnumber' => 'sum(number) as Umsatzmenge', 'wavgprice' => 'sum(number*price)/sum(number) as GewDurchschnitt', 'avgprice' => 'avg(price) as Durchschnittspreis', 'id' => 'prod_id as Produktid');
		$q_select = "SELECT ".$select_rep[mres($select[0])];
		for($i=1;$i<count($select); $i++) {
			$q_select .= ', '.$select_rep[mres($select[$i])];
		}
		$where_rep = array('marine' => 'prod_id = 2 and type = \'mil\'', 'ranger' => 'prod_id = 4 and type = \'mil\'', 'buc' => 'prod_id = 40 and type = \'mil\'', 'auc' => 'prod_id = 41 and type = \'mil\'', 'huc' => 'prod_id = 42 and type = \'mil\'',
							'nrgy' => 'prod_id = 1 and type = \'res\'', 'erz' => 'prod_id = 2 and type = \'res\'', 'fps' => 'prod_id = 3 and type = \'res\'',
							'thiefs' => 'prod_id = 1 and type = \'spy\'', 'guards' => 'prod_id = 2 and type = \'spy\'', 'angents' => 'prod_id = 3 and type = \'spy\'');
		$q_where = ' WHERE time > '.mktime(0, 0, 0, $vonmonth, $vontag, $vonjahr).' and time < '.mktime(0, 0, 0, $bismonth, $bistag, $bisjahr);
		
		if($product) {
			$q_where .= ' and (';
			$q_where .= '('.$where_rep[mres($product[0])].') ';
			for($i=1;$i<count($product); $i++) {
				$q_where .= ' or ('.$where_rep[mres($product[$i])].') ';
			}
			$q_where .= ') ';
		}
		$q_order = '';
		if($order) {
			if($order == 'orderasc')
				$q_order = ' ORDER BY '.$orderby.' ASC ';
			else 
				$q_order = ' ORDER BY '.$orderby.' DESC' ;
		}
		$q_group = '';
		
		if($groupby) {
			$q_group = ' GROUP BY '.$groupby.' ';
		}
		$query = $q_select.' FROM marketlogs '.$q_where.$q_group.$q_order;
		$t_start = microtime();
		$res = assocs($query);
		$t_taken = microtime()-t_start;
		
		echo '<table cellpadding="2" border="1">';
		$rep_step = 50;
		$count = 0;
		foreach($res as $row) {
			if($count%$rep_step == 0) {
				echo '<tr>';
				foreach($row as $key => $val) {
					echo '<td><b>'.$key.'</b></td>';
				}
				echo '</tr>';
			}
			echo '<tr>';
			foreach($row as $key => $val) {
				echo '<td>'.(is_numeric($val)?pointit($val):$val).'</td>';
			}
			echo '</tr>';
			$count++;
		}
		echo '</table>';
	}
} else {
	echo 'Zugriff verweigert';
}

?>