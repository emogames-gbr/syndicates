<font class="normal">
<b><? echo "$storydot"; ?> Story</b><br>
<br>
<?
if ($all != "true") {
$id = floor($id);
if (!$id) {
$result = select("select text,headline,author,id,time from story where visible=1 order by time limit 1");
$return = mysql_fetch_assoc($result);
}
else {
$result = select("select text,headline,author,id,time from story where id=$id");
$return = mysql_fetch_assoc($result);
}
select("update story set clicks=clicks+1 where id=".$return[id]);
$result = select("select id,headline from story where id > $return[id] and visible=1 limit 1");
$next = mysql_fetch_assoc($result);
$return[text] = preg_replace("/\n\r?\f?/","<br />",$return[text]);
$return[time] = mytime($return[time]);

?>

<table class=normal width="800" cellspacing="1" boder="0" cellpadding="0">
	<tr>
		<td width=470 valign=top>
			<table class=rand width=100% border=0 cellspacing=1 cellpadding=0>
				<tr>
					<td>
						<table cellspacing="0" cellpadding="4" width="100%" class="head" <? if(strlen($return[headline]) > 25) { echo 'style="background-image:url('.WWWDATA.'images/verlauf-dunkel_long.jpg)"';} ?>>
							<tr>
								<td align="center" width="70%"><? echo("<strong>$return[headline]</strong>");?></td>
								<td align="right" width="30%">Autor: <? echo("$return[author]");?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class=body width=100% cellpadding=4 cellspacing=0 border=0>
							<tr>
								<td colspan="3" width=100% align="left">
									<? echo("$return[text]"); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table cellspacing="0" cellpadding="4" width="100%" class="foot">
							<tr>
								<td align="center" width="30%"><a class=ver11w href="index.php?action=story&all=true">&nbsp;</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td width="10"></td>
		<td valign="top" width="120">
			<table width=100% cellspacing="1" cellpadding="0" class=rand>
				<tr>
					<td>
						<table width="100%" cellpadding="4" cellspacing="0" class=head>
							<tr>
								<td align=center>Die neusten</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class=body width=100% cellpadding=4 cellspacing=0>
								<? // Hier die 5 neusten beiträge verlinken
								$result=select("select id,headline from story where visible=1 order by time desc limit 5");
								while ($newest = mysql_fetch_assoc($result)) { ?>

							<tr>
								<td width="100%" <? echo ("$bodyover $bodyout"); ?> align=center>
								<a class=ver11w href="index.php?action=story&id=<? echo $newest[id]; ?>"><? echo $newest[headline]; ?></a><br>
								</td>
							</tr>
								<?
								}
								?>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table cellspacing="0" cellpadding="4" width="100%" class="foot">
							<tr>
								<td align="center" width="30%"><a class=ver11w href="index.php?action=story&all=true">Alle Teile</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?
if ($next[id]) {
echo("<br>
<table class=\"normal\" width=\"100%\"><tr><td align=\"right\"><a class=ver11w href=\"index.php?action=story&id=$next[id]\" class=\"gelblink\">$next[headline]</a></td></tr></table>
");
}

} // all true

else {
$result = select("select headline,time,author,id from story where visible=1 order by time");
?>
<table width="600" cellspacing="1" cellpadding="0" class=rand>
	<tr>
		<td>
			<table cellspacing="0" cellpadding="3" width=100% class=head>
				<tr>
					<td width="30%" align="left">Datum</td>
					<td width="40%" align="center">Titel</td>
					<td width="30%" align="right">Autor</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width=100% class=body cellspacing=0 cellpadding=3 border=0>
				<?
				// hier daten rein
				while ($all = mysql_fetch_assoc($result)) {
					$all[time] = mytime($all[time]);
					echo ("
					<tr>
						<td width=\"30%\" align=\"left\">
							$all[time]
						</td>
						<td width=\"40%\" align=\"center\">
							<a class=ver11w href=\"index.php?action=story&id=$all[id]\" class=\"ver11s\">$all[headline]</a>
						</td>
						<td width=\"30%\" align=\"right\">
							$all[author]
						</td>
					</tr>");
				} // while

			?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0" cellpadding="3" width=100% class=foot>
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?
}
?>



</font>


