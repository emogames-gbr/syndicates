<? if ($loggedin) { ?>
	<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#000000">
    <tr>
		<td>
			<table width="600" cellpadding="1" cellspacing="1" border="0" bgcolor="#000000">
			<tr bgcolor="#364974" height="16">
				<td width="80" align="center" class="ver11w"><b>Networth</b></td>
				<td width="70" align="center" class="ver11w"><b>Land</b></td>
				<td width="110" align="center" class="ver11w"><b>Geld</b></td>
				<td width="110" align="center" class="ver11w"><b>Energie</b></td>
				<td width="90" align="center" class="ver11w"><b>Erz</b></td>
				<td width="110" align="center" class="ver11w"><b>Forschungsp.</b></td>							</tr>
			<tr bgcolor="#86A0CC" height="16">
			<?
			echo "
				<td width=\"80\" align=\"center\" class=\"ver11s\">".pointit($status{nw})."</td>
				<td width=\"70\" align=\"center\" class=\"ver11s\">".pointit($status{land})." H</td>
				<td width=\"110\" align=\"center\" class=\"ver11s\">".pointit($status{money})." Cr</td>
				<td width=\"110\" align=\"center\" class=\"ver11s\">".pointit($status{energy})." MWh</td>
				<td width=\"90\" align=\"center\" class=\"ver11s\">".pointit($status{metal})." t</td>
				<td width=\"110\" align=\"center\" class=\"ver11s\">".pointit($status{sciencepoints})." P</td>
				";
			?>
			</tr></table>	
		</td>
    </tr>
	</table>
						
	<br>

<? } // falls eingelogt ?>    