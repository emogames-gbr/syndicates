<?


$globals = assoc("select * from globals order by round desc");
$spieler = single("select count(*) from status where alive >= 1");

echo "
	<table>
		<tr class=back>
			<td>Syndicates Runde:</td>
			<td>".($globals[round]-2)."</td>
		</tr>
		<tr class=back>
			<td>Anzahl Spieler:</td>
			<td>".$spieler."</td>
		</tr>
		<tr class=back>
			<td>Serverzeit:</td>
			<td>".(date("Y-m-d, h:i:s",time()))."</td>
		</tr>
		
	</table>
";


?>
