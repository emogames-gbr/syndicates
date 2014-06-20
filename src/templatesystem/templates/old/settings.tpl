<br>
Geben Sie ihrem Konzern eine individuelle Note! Laden Sie ein individuelles Bild hoch, 
schreiben Sie eine kurze Hintergrundgeschichte oder fassen Sie die wichtigsten Daten 
Ihrer Firma zusammen. Bitte beachten Sie jedoch, dass Sie für die von Ihnen
veröffentlichten Bilder/Schriften selbst verantwortlich sind.<br>
&nbsp;
<form action=settings.php method="post" enctype="multipart/form-data">
	<input type="hidden" name="inneraction" value="makeentry">
	<table width="600" class="siteGround" align="left" border="0">
		<tr height="15">
			<td colspan=3></td>
		</tr>
	   	<tr>
	   		<td width="85" align="left" valign="top">
	   			Konzernbild:
	   		</td>
   	   		<td width="515" align="left" colspan="2">
				<table cellpadding="1" cellspacing="0" border="0" align="left" valign="middle" class="tableOutline" width="450">
					<tr>
						<td>
							<table cellpadding=3 cellspacing=0 border=0 align=center class=tableInner1 width=450>
								<tr>
									<td>
										<table>
											<tr>
												<td align=left class=tableInner1>
													Bitte wähle ein Bild aus (max. 20 KB, 110 x 140 Pixel):
												</td>
											</tr>
											<tr>
												<td height=40 align=center>
													<input type="hidden" name="MAX_FILE_SIZE" value="20480">
													<input type=file name=sbil value="" size=10> 
													<input type=submit value=hochladen name=submittype>
												</td>
											</tr>
										</table>
									</td>
									{if $status.image}
									<td>
										<table border=0 class=tableInner1 cellpadding=0 align=right>
											<tr>
												<td class=tableInner1 align=center valign=middle>
													Akt. Konzernbild:
												</td>
											</tr>
											<tr>
												<td class=tableInner1 align=center valign=middle>
													<img src="{$WWWDATA}konzernimages/{$KBILD_PREFIX}{$id}.{$status.image}" border="0">
												</td>
											</tr>
											<tr>
												<td align=center class=tableInner1 valign=middle>
													<input type=submit value="Bild löschen" name=submittype>
												</td>
											</tr>
										</table>
									</td>
									{/if}
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
	   	</tr>
	   	<tr height=15>
	   		<td colspan=3></td>
	   	</tr>
		<tr>
	   		<td width=85 align=left valign=middle>
	   			Branche:<br>
	   		</td>
	   		<td width=515 align=left valign=middle colspan=2>
	   		{if $IS_OSTERN && $OSTER_BONI.39486}
	   			<div style="position:relative;">
					<div style="position:absolute; float:left;">
						<a class="normal" href="bonus.php?type=4&amp;egg=39486">
							<img src="images/ostern_39486.png" width="80px"></a>
					</div>
				</div>
			{/if}
	   			<input name=kategorie value="{$data.kategorie}" size=20>
	   		</td>
	   	</tr>
	   	<tr height=15>
	   		<td colspan=3></td>
	   	</tr>
	   	<tr>
	   		<td width=50 align=left valign=top>
	   			Beschreibung:<br>
	   			<br>
	   			BBCode-Hilfe<br>
	   			{$help_bbcode}
	   		</td>
   	   		<td width=550 align=left colspan=2>
	   			<textarea cols=55 rows=13 name=description>{$data.description}</textarea>
	   		</td>
	   	</tr>
	   	<tr height=15>
	   		<td colspan=3></td>
	   	</tr>
	    <tr>
	    	<td align=right>
	    		<input name=showdetails type=checkbox {$checked}>
	    	</td>
	    	<td colspan=2>
	    		Auszeichnungen und Startrunde anzeigen.
	    	</td>
	    </tr>
	    <tr height=15>
	   		<td colspan=3></td>
	   	</tr>
		<tr>
			<td colspan=3 align=right>
				<input type=submit value=Absenden name=submittype>
			</td>
		</tr>
	</table>
</form>