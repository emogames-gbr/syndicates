<head>
	<meta http-equiv="Content-Type" content="text/html"; charset="UTF-16;>
</head>
<?


//**************************************************************************//
//							?bergabe Variablen checken						//
//**************************************************************************//


//**************************************************************************//
//							Dateispezifische Finals deklarieren				//
//**************************************************************************//


//**************************************************************************//
//							Game.php includen								//
//**************************************************************************//


require("../../includes.php");
$handle = connectdb($SERVER_NAME);


$replacements = array(
	"Ã¤"=> "ä",
	"Ã„"=> "Ä",
	"Ã¶"=> "ö",
	"Ã–"=> "Ö",
	"Ã¼"=> "ü",
	"Ãœ"=> "Ü",
	"ÃŸ"=> "ß",
);





$tables = singles("show tables");
//print_r($tables);

foreach ($tables as $temp) {
	$spalten = assocs("describe $temp");
	$table = $temp;
	
		
		//echo "TABLE: $table";
		echo "<br>";
		$toupdate = array();
		foreach ($spalten as $inner) {
			if (ereg("varchar",$inner[Type]) || ereg("text",$inner[Type])) {
				$toupdate[] = $inner[Field];
			}
		}
		//print_r($toupdate);
		$updatestring = "update $table set ";
		foreach($toupdate as $spalte) {
			$repstring = "$spalte";
			foreach ($replacements as $ky => $v) {
				//echo "$k<br>";
				$repstring="replace(".$repstring.",'$ky','$v')";
			}
			$updatestring.=$spalte."=".$repstring.",";
		}
		$updatestring = chopp($updatestring).";";
		if (count($toupdate) > 0) {
			echo("$updatestring");
		}
		//select($updatestring);
}


$time = time();



//**************************************************************************//
//							Variablen initialisieren						//
//**************************************************************************//


//**************************************************************************//
//**************************************************************************//
//							Eigentliche Berechnungen!						//
//**************************************************************************//
//**************************************************************************//


//							selects fahren									//

//							Berechnungen									//

//							Daten schreiben									//

//							Ausgabe     									//


//**************************************************************************//
//							Header, Ausgabe, Footer							//
//**************************************************************************//


echo $ausgabe;



//**************************************************************************//
//							Dateispezifische Funktionen						//
//**************************************************************************//



?>
