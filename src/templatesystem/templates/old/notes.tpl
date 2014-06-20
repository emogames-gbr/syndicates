{if $noAssi}
	{if $text}
		Ihre Änderung:<br>
		<br>
		{$text}
	{/if}
	<br>
	<br>
	<table align=center width=80% cellspacing=1 cellpadding=3 bgcolor=black>
		<tr class=tableHead>
			<td align=center>
				Ihr persönlicher Notizblock (max. 15.000 Zeichen)
			</td>
		</tr>
		<tr class=tableInner2>
			<td align=center>
				<form action=notes.php style="margin:0px" method=post>
					<input type=hidden name=inner value=change>
					<textarea name="notiztext" cols=60 rows=20>{$stuff.text}</textarea><br>
					<br>
					<input type=submit value=Eintragen>
					<br>
				</form>
			</td>
		</tr>
	</table>
{else}
	<br>
	<br>
	Der Notizblock ist nur verfügbar, wenn das 
	<a target="_blank" href="premiumfeatures.php" class="highlightAufSitebg" style="font-size:12px" target=_blank>Komfortpaket</a> 
	aktiviert wurde!	
{/if}