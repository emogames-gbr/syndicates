		<div align="center">
			<br />
			<br />
			<table class="siteGround">
				<tr>
					<td align="center">
						[neueausgabe]
					</td>
					<td align="center">
						[extrablatt]
					</td>
				</tr>
				<tr>
					<td>
						<img src="http://syndicates-online.de/images/neueausgabe.jpg" name="neueausgabe" alt="neueausgabe" />
					</td>
					<td>
						<img src="http://syndicates-online.de/images/extrablatt.jpg" name="extrablatt" alt="extrablatt" />
					</td>
				</tr>
			</table>
			{if $PREVIEW_TEXT}
			<br />
			<br />
			<fieldset style="margin:10px; width:80%; text-align:left">
				<legend> Vorschau </legend>
				{$PREVIEW_TEXT}
			</fieldset>
			{/if}
			<br />
			<br />
			Bitte die Nachricht eingeben:<br />
			<form action="synzeitung.php" method="post">
				<input type="hidden" name="action" value="insert" />
				<textarea name="msg" rows="10" cols="40">{$MSG}</textarea><br />
				<input type="submit" name="type" value="Abschicken" onclick="return window.confirm('Die Nachricht wirklich an alle schicken?');"> <input type="submit" name="type" value="Vorschau">
			</form>
			<em>Hinweis: BBCodes sind an. Zusätzlich zeigen die Codes von oben die Bilder an.</em><br />
			<br />
			<a href="http://syn-aktuell.goroth.de/" target="_blank" class="linkAuftableInner">Zur Zeitung</a>
		</div>