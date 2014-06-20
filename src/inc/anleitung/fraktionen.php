?><font class="normal">
Syndicates ermöglicht dem Spieler die Wahl zwischen fünf verschiedenen Fraktionen. Jede Fraktion hat ihre Vor- und Nachteile, daher sollte man sich gut überlegen, für welche Fraktion man sich entscheidet.<br>
<br>

<?$fraks = assocs("select * from races","race");

foreach ($fraks as $temp) {
	echo "
		<li>$temp[description]<br><br>	
	";
}
?>
<br><br>

<table width="800" cellspacing="1" cellpadding="5" border="0" class=rand align=center>
	<tr class="head">
		<td width="200" align="center"><b>Fraktion</b></td>
		<td width="300" align="left"><b>&nbsp;Modifikatoren</b></td>
		<td width="100"><b>Diese Runde</b></td>
		<td width="200"><b>Nächste Runde</b></td>
	</tr>
	<?	
		$desc = assocs("select * from fraktionen_beschreibung","race");
		foreach ($desc as $k => $temp) {
			if (isKsyndicates()) {
				$temp[description_html] = preg_replace("/images/","images/krawall_images",$temp[description_html]);
			}
			echo "
			 <tr class=\"body\">
			";
			echo $temp[description_html];
			if ($fraks[$k][active] == 1) {
				$fraks[$k][active] = "<b>Aktiv</b>";
			}
			elseif ($fraks[$k][active] == 0) {
				$fraks[$k][active] = "<b>Nicht aktiv</b>";
			}
			elseif ($fraks[$k][active] == -1) {
				$fraks[$k][active] = "<b>Nicht festgelegt</b>";
			}
			if ($fraks[$k][nextactive] == 1) {
				$fraks[$k][nextactive] = "<b>Aktiv</b>";
			}
			elseif ($fraks[$k][nextactive] == 0) {
				$fraks[$k][nextactive] = "<b>Nicht aktiv</b>";
			}
			elseif ($fraks[$k][nextactive] == -1) {
				$fraks[$k][nextactive] = "<b>Nicht festgelegt</b>";
			}			
			
			
				

						
			
			echo "
				<td align=\"center\">".$fraks[$k][active]."</td>
				<td align=\"center\">".$fraks[$k][nextactive]."</td>
			   </tr>			
			";
		}
	?>
	

</table>

<?
