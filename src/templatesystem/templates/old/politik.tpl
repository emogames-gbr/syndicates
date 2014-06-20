{if !$ACTION}
<br>
<table cellspacing="5" cellpadding="0" border="0" width="550">
	<tr>
		<td class="siteGround">
			<strong>Übersicht</strong>
		</td>
	</tr>
	<tr>
		<td class="siteGround" width="200">
			Syndikatsname
		</td>
		<td width="50"></td>
		<td class="siteGround" align="center">
			{if $SHOW_SYNNAME_EDIT}
			<br>
			<form action="politik.php" method="post">
				<input type="hidden" name="action" value="csn">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" maxlength="50" name="newsyndname" value="{$SNAME}">
				<input class="button" type="submit" value="ändern">
			</form>
			{else}
			{$SNAME}
			{if $ISKING}
			<font class="highlightAufSiteBg">
				<a href="politik.php?action=csn" class="linkAufsiteBg">
					{$YELLOWDOT}ändern</a>
			</font>
			{/if}
			{/if}
		</td>
	</tr>
	<tr>
		<td class="siteGround">
			Syndikatsnummer
		</td>
		<td width="50">&nbsp;</td>
		<td class="siteGround" align="center">
			#{$STATUS.rid}
		</td>
	</tr>
	<tr>
		<td class="siteGround">
			Präsident
		</td>
		<td width="50">&nbsp;</td>
		<td class="siteGround" align="center">
			{$LEGALKING}
		</td>
	</tr>
	<tr>
		<td class="siteGround">
			Interne Währung
		</td>
		<td width="50"></td>
		<td class="siteGround" align="center">
			{if $SHOW_SCURRNAME_EDIT}
			<br>
			<form action="politik.php" method="post">
				<input type="hidden" name="action" value="ccn">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" maxlength="20" name="newcurrname" value="{$SCURRNAME}">
				<input class="button" type="submit" value="ändern">
			</form>
			{else}
			{$SCURRNAME}
			{if $ISKING}
			<font class="highlightAufSiteBg">
				<a href="politik.php?action=ccn" class="linkAufsiteBg">
					{$YELLOWDOT}ändern</a>
			</font>
			{/if}
			{/if}
		</td>
	</tr>
	<tr>
		<td class="siteGround">
			Maximalverschuldung pro Land
		</td>
		<td width="50">&nbsp;</td>
		<td class="siteGround" align="center">
			{if $SHOW_MAXSCHULDEN_EDIT}
			<br>
			<form action="politik.php" method="post">
				<input type="hidden" name="action" value="cms">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" maxlength="40" name="newmaxschulden" value="{$MAXSCHULDEN_EDIT}">
				<input class="button" type="submit" value="ändern">
			</form>
			{else}
			{$MAXSCHULDEN} {$SCURRNAME}
			{if $ISKING}
			<font class="highlightAufSiteBg">
				<a href="politik.php?action=cms" class="linkAufsiteBg">
					{$YELLOWDOT}ändern</a>
			</font>
			{/if}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="3" height="20"></td>
	</tr>
	<tr>
		<td colspan="3" align="center">
		{if $ISKING}
			<table align="center">
				<tr>
					<td>
						<a href="politik.php?action=chanm" class="linkAufsiteBg">
							{$YELLOWDOT}Syndikatsankündigung ändern
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=synbeschr" class="linkAufsiteBg">
							{$YELLOWDOT}Syndikatsbeschreibung ändern
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=chsbil" class="linkAufsiteBg">
							{$YELLOWDOT}Syndikatsbild ändern
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=chspw" class="linkAufsiteBg">
							{$YELLOWDOT}Syndikatspasswort ändern
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=chlsw" class="linkAufsiteBg">
							{$YELLOWDOT}Link auf die Syndikatswebsite ändern
						</a>
					</td>
				</tr>
			</table>
		{/if}
		</td>
	</tr>
</table>
<br>
<table cellspacing="5" cellpadding="0" border="0" width="550">
	<tr>
		<td class="siteGround" colspan="3">
			<strong>Diplomatie</strong>
		</td>
	</tr>
	<!-- START -> lines_diplomatie -->
	{if $ALLY_ANFRAGE}
		<tr>
			<td class="siteGround" width="80">
				Allianz-<br>verh. mit
			</td>
			<td class="siteGround" align="center">
				{if $ALLY_ANFRAGE.partner1.ersteller}<strong>{/if}
				{$ALLY_ANFRAGE.partner1.name} (#{$ALLY_ANFRAGE.partner1.synd_id})
				{if $ALLY_ANFRAGE.partner2.ersteller}</strong>{/if}<br>
				{if $ALLY_ANFRAGE.partner2}
					{if $ALLY_ANFRAGE.partner2.ersteller}<strong>{/if}
					{$ALLY_ANFRAGE.partner2.name} (#{$ALLY_ANFRAGE.partner2.synd_id})
					{if $ALLY_ANFRAGE.partner2.ersteller}</strong>{/if}
				{/if}
			</td>
			<td class="highlightAufSiteBg" width="110" align="center">
				seit {$ALLY_ANFRAGE.days}d, {$ALLY_ANFRAGE.hours}h, {$ALLY_ANFRAGE.minutes}m
				{if $ISKING}
					<br>
					{if $ALLY_ANFRAGE.accept}
					<a href="politik.php?action=ab&what=accept" class="linkAufsiteBg">
						Allianz bestätigen
					</a><br>
					{/if}
					<a href="politik.php?action=ab&what=decline" class="linkAufsiteBg">
						Verhandl. abbr.
					</a>
				{/if}
			</td>
		</tr>
		<tr>
			<td height="20"></td>
		</tr>
	{/if}
	{if $FUSION}
		{foreach from=$FUSION item=VL}

			<tr>
				<td class="siteGround" width="80">
					Fusionierungs-<br>
					Verh. mit
				</td>
				<td class="siteGround" align="center">
					<strong>
						{$VL.synName} (#{$VL.synd_id})
					</strong>
				</td>
				<td class="highlightAufSiteBg" width="110" align="center">
					seit {$VL.days}d, {$VL.hours}h, {$VL.minutes}m
					{if $ISKING && !$VL.isSteller}
						<br>
						{if !$VL.isSteller}
						<a href="politik.php?action=synfus&sac=handle&what=accept&who={$VL.synd_id}" class="linkAufsiteBg" nowrap>
							Fusionier. bestät.
						</a><br>
						{/if}
						<a href="politik.php?action=synfus&sac=handle&what=decline&who={$VL.synd_id}" class="linkAufsiteBg">
							Fusionierung abbr.
						</a>
					{/if}
				</td>
			</tr>
			<tr>
				<td height="20"></td>
			</tr>
		{/foreach}
	{/if}
	{if $ALLY} 
			<tr>
				<td colspan="3" width="100%">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td {if $ALLY.count_member == 3}rowspan="2"{/if} class="siteGround" width="80" valign="middle">
								Allianz mit
							</td>
							<td class="siteGround" align="center">
								{$ALLY.member1.name} (#{$ALLY.member1.synd_id})
							</td>
							<td class="siteGround" align="left" width="100">
								{if $ALLY.member1.status == 'alive'}
									> <strong>alliiert</strong>
								{else}
									> <strong>aufgekündigt</strong>
								{/if}
							</td>
						</tr>
						{if $ALLY.count_member == 3}
						<tr>
							<td class="siteGround" align="center">
								{$ALLY.member2.name} (#{$ALLY.member2.synd_id})
							</td>
							<td class="siteGround" align="left">
								{if $ALLY.member2.status == 'alive'}
									> <strong>alliiert</strong>
								{else}
									> <strong>aufgekündigt</strong>
								{/if}
							</td>
						</tr>
						{/if}
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="right" class="highlightAufSiteBg">
					<a href="politik.php?action=alnamechange" class="linkAufsiteBg">Allianzname ändern</a>
					{* R61, unkuendbar | <a href="politik.php?action=aa" class="linkAufsiteBg">Allianz aufkündigen</a> *}
				</td>
			</tr>
			<tr>
				<td height="20"></td>
			</tr>
	{/if}
	{if $ATWAR}
		{foreach from=$ATWAR item=VL}
			<tr>
				<td>
					<table width="100%" class="tableOutline" cellpadding="0" cellspacing="1" border="1">
						<tr>
							<td>
								<table width="100%" class="siteGround" cellpadding="3" cellspacing="0" border="0">
									<tr>
										<td class="tableHead" width="100%" colspan="3">
											<strong>Krieg</strong>
										</td>
									</tr>
									<tr>
										<td align="left" width="50%">
											{foreach from=$VL.verbuendete item=VL2}
												{if $STATUS.rid == $VL2.synd_id}
													Ihr Syndikat
												{else}
													{$VL2.name} (#{$VL2.synd_id})
												{/if}<br>
											{/foreach}
										</td>
										<td width="10" align="center" valign="middle">vs</td>
										<td align="right" width="50%">
											{foreach from=$VL.enemy item=VL2}
												{$VL2.name} (#{$VL2.synd_id})<br>
											{/foreach}
										</td>
									</tr>
									<tr class="tableInner1">
										<td>
											Land zu Kriegsbeginn: {$VL.landstart_own} ha
										</td>
										<td></td>
										<td>
											Gegner: {$VL.landstart_enemy} ha
										</td>
									</tr>
									<tr class="tableInner1">
										<td>
											Land erobert: {$VL.landwon_own} ha
										</td>
										<td></td>
										<td>
											Land verloren: {$VL.landwon_enemy} ha
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<table cellpadding="0" cellspacing="0" class="siteGround" align="center">
												<tr>
													<td width="50" align="right">Niederlage</td>
													<td align="left" width="{$VL.kriegsbalken_colwidth2}">
														<table width="{$VL.kriegsbalken_colwidth2}" cellpadding="0" cellspacing="2" class="tableOutline" border="0" align="center">
															<tr>
																<td class="tableInner1" align="right" width="{$VL.kriegsbalken_colwidth}">
																	<img src="{$RIPF}dotpixel_blau.gif" width="{$VL.enemywidth}" height=15 border=0>{$VL.ownprozent}
																</td>
																<td class="tableInner1" align="left" width="{$VL.kriegsbalken_colwidth}">
																	{$VL.enemyprozent}<img src="{$RIPF}dotpixel_blau.gif" width="{$VL.ownwidth}" height="15" border="0">
																</td>
															</tr>
														</table>
													</td>
													<td width="50" align="left">Sieg</td>
												</tr>
											</table>
										</td>
									</tr>			
									<tr>
										<td colspan="3">
											Brutto<br />
											<table cellpadding="0" cellspacing="0" class="siteGround" align="center">
												<tr>
													<td width="60" align="left">
														erobert:
													</td>
													<td align="center" width="{$VL.kriegsbalken_colwidth2}">
														<table width="{$VL.kriegsbalken_colwidth2}" cellpadding="0" cellspacing="2" class="tableOutline" border="0" align="center">
															<tr>
																<td class="tableInner1" align="left" width="{$VL.kriegsbalken_colwidth2}">
																	<img src="{$RIPF}dotpixel_blau.gif" width="{$VL.ownwidth_brutto}" height="15" border="0">
																	{$VL.ownprozent_brutto}
																</td>
															</tr>
														</table>
													</td>
													<td width="60" align="left">
														von {if $VL.isAtter}20 %{else}16 %{/if}
													</td>
												</tr>
											</table>
											<table cellpadding="0" cellspacing="0" class="siteGround" align="center">
												<tr>
													<td width="60" align="left">verloren:</td>
														<td align=center width="{$VL.kriegsbalken_colwidth2}">
															<table width="{$VL.kriegsbalken_colwidth2}" cellpadding="0" cellspacing="2" class="tableOutline" border="0" align="center">
																<tr>
																	<td class="tableInner1" align="left" width="{$VL.kriegsbalken_colwidth2}">
																		<img src="{$RIPF}dotpixel_blau.gif" width="{$VL.enemywidth_brutto}" height="15" border="0">
																		{$VL.enemyprozent_brutto}
																	</td>
																</tr>
															</table>
														</td>
														<td width="60" align="left">
															von {if $VL.isAtter}16 %{else}20 %{/if}
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr class="tableInner1">
											<td colspan="2">
												Voraussichtliche Kriegsprämie: {$VL.praemie_own} HP
											</td>
											<td>
												Gegner: {$VL.praemie_enemy} HP
											</td>
										</tr>
									{if $VL.monu}
										<tr class="tableHead">
											<td colspan="3">
												<b>Monument-Eroberung, falls siegreich (wird vom Präsidenten eingestellt):</b>
											</td>
										</tr>
										{foreach from=$VL.verbuendete item=VL2}
										<tr>
											<td>
												{$VL2.name}
											</td>
											<td colspan="2">
											{if $VL2.artefakt}
												{$VL2.artefakt}
											{else}
												-
											{/if}
											{if $ISKING && $STATUS.rid == $VL2.synd_id && $VL2.noMonu}
												<a href="politik.php?action=cme&wid={$VL.warID}" class="linkAuftableInner">
													{$YELLOWDOT}ändern</a>
											{/if}
											</td>
										</tr>
										{/foreach}
									{/if}		
										<tr class="tableHead2">
											<td class="highlightAufSiteBg" width="100%" align="right" colspan="3">
												{if $VL.isBefore}Start in{else}Ende in{/if} {$VL.time_days}d, {$VL.time_hours}h, {$VL.time_minutes}m
												{if $VL.isPossibleToEnd}
													<br>
													<a href="politik.php?action=peace&wnum={$VL.warID}" class="linkAufsiteBg">
														Frieden anbieten
													</a>
												{/if}
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			<tr height="20">
			</tr>
		{/foreach}
	{else}
		<tr>
			<td class="siteGround" colspan="3" align="center">
				Ihr Syndikat befindet sich momentan mit niemandem im Krieg!
			</td>
		</tr>
	{/if}
	<!-- ENDE -> lines_diplomatie -->
	<!-- START -> options_diplomatie -->
	<tr>
		<td height="20" colspan="3"></td>
	</tr>
	<tr>
		<td align="center" colspan="3">
		{if $ISKING}
			<table align="center">
				<tr>
					<td>
						<a href="politik.php?action=ae" class="linkAufsiteBg">
							{$YELLOWDOT}Allianz eingehen
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=dw" class="linkAufsiteBg">
							{$YELLOWDOT}Einem Syndikat den Krieg erklären
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=pkv" class="linkAufsiteBg">
							{$YELLOWDOT}inaktiven Spieler aus dem Syndikat ausschließen
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a href="politik.php?action=synfus" class="linkAufsiteBg">
							{$YELLOWDOT}Mit anderem Syndikat fusionieren
						</a>
					</td>
				</tr>
				<tr>
					<td height="20" colspan="3"></td>
				</tr>
			</table>
		{/if}
		</td>
	</tr>
</table>
<!-- ENDE -> options_diplomatie -->
{if $NASP}
<br>
<table cellspacing="1" cellpadding="5" border="0" width="600" class="tableOutline">
	<tr class="tableHead">
		<td>Privates Abkommen mit</td>
		<td></td>
		<td align="center">Kündigungsfrist</td>
		<td align="right">Kündigungsstrafe</td>
		<td align="center">Art</td>
		<td align="center">Status</td>
		<td align="right">Optionen</td>
	</tr>
	{foreach from=$NASP item=VL}
	<tr class="tableInner1">
		<td>
		{if $VL.isOnline}
			<img src="{$RIPF}_online.gif" border="0" align="absmiddle">
		{else}
			<img src="{$RIPF}_offline.gif" border="0" align="absmiddle">
		{/if}
		{$VL.syndicate} (#{$VL.rid})
		</td>
		<td align="center">
			<a href="mitteilungen.php?action=psm&rec={$VL.nappartner}">
				<img align="middle" src="{$RIPF}_syn_message_letter.gif" border="0" alt="{$VL.syndicate} (#{$VL.rid})} eine Nachricht senden">
			</a>
		</td>
		<td align="center">
			{$VL.kfrist}h
		</td>
		<td align="right">
			{$VL.kstrafe}
		</td>
		<td align="center">
			$napart
		</td>
		<td align="center">
		{if $VL.isGekuendigt}
			<font class="achtungAuftableInner">+{$VL.gekuendigt_hoursLeft}h</font>
		{else}
			{if $VL.type == 0}
			<font class="highlightAuftableInner">inaktiv</font>
			{elseif $VL.type}
			<font class="gruenAuftableInner">aktiv</font>
			{/if}
		{/if}
		</td>
		<td align="right">
		{if !$VL.isGekuendigt}
			{if $VL.type == 0}
				{if $VL.isInitiator}
				<a href="politik.php?action=pab&what=takeback&abkommen={$VL.napid}" class="linkAuftableInner">
					zurücknehmen</a>
				{else}
				<a href="politik.php?action=pab&what=accept&abkommen={$VL.napid}" class="linkAuftableInner">
					annehmen</a> / 
				<a href="politik.php?action=pab&what=decline&abkommen={$VL.napid}" class="linkAuftableInner">
					ablehnen</a>
				{/if}
			{elseif $VL.type}
			<a href="politik.php?action=pab&what=cancel&abkommen={$VL.napid}" class="linkAuftableInner">
				kündigen
			</a>
			{/if}
		{/if}
		</td>
	</tr>
	{/foreach}
</table>
<a href="politik.php?action=pa" class="linkAufsiteBg">
	{$YELLOWDOT}privates Abkommen abschließen
</a>
{/if}
<br>
<table cellspacing="5" cellpadding="0" border="0" width="550" align="center">
	<tr>
		<td>
			<table align="center">
				<tr>
					<td> </td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>
<br>
<table cellspacing="1" cellpadding="5" border="0" width="550" class="tableOutline">
	<tr class="tableHead">
		<td colspan="2">
			<strong>Präsidentenwahl</strong>
		</td>
		<td width="1">
			<strong>Stimmen</strong>
		</td>
		<td align="center">%</td>
		<td>&nbsp;</td>
	</tr>
	 {foreach from=$VOTE item=VL}
		<tr class="tableInner1">
			<td>
				{$VL.rulername} von {$VL.syndicate}
				{if $VL.showEmoname}
				<i>({$VL.emoname})</i>
				{/if}
			</td>
			<td align="center">
			{if $VL.isKing}
				<img src="{$RIPF}_praesi.gif" border="0" align="absmiddle">
			{/if}
			</td>
			<td align="right">
				{$VL.stimmen}
			</td>
			<td align="right">
				{$VL.prozent}
			</td>
			<td align="center">
			{if $VL.id == $STATUS.vote} 
				gewählt
			{else}
				<form action="politik.php">
					<input type="hidden" name="action" value="vote">
					<input type="hidden" name="who" value="{$VL.id}">
					<input class="button" type="submit" value="wählen">
				</form>
			{/if}
			</td>
		</tr>
	{/foreach}
</table>
<!--  ENDE Standardausgabe -->
{elseif $ACTION == 'cme'}
<!--  								-->
<!--  Monument Eroberung ändern 	-->
<!--  								-->
<br><br>
<form action="politik.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="70%">
		<tr>
			<td class="siteGround" height="20">
				Stellen Sie ein, welches Monument Sie erobern möchten, falls Ihr Syndikat den Krieg gewinnt.<br>
				ACHTUNG! Falls Sie mit einem Syndikat alliiert sind, welches <b>den gleichen Monument-Wunsch</b> hat, 
				bekommen Sie das Monument nur dann, wenn Ihr Syndikat mehr Land erobert hat, als ihr Allianzpartner. 
				Ist dies nicht der Fall, geht ihr Syndikat leer aus.
			</td>
		</tr>
		<tr>
			<td height="40" align="center">
				<input type="hidden" name="action" value="cme">
				<input type="hidden" name="wid" value="{$WID}">
				<input type="hidden" name="ia" value="finish">
				<select name="artefakt_id">
				{foreach from=$AVAILABLE_MONUMENTS item=VL}
					<option value="{$VL.ID}" {if $VL.ID == $CURRENT_CHOICE}selected{/if}>{$VL.name}
				{/foreach}
					<option value="0" {if 0 == $CURRENT_CHOICE}selected{/if}>Keines / Monument(e) zerstören
				</select>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input class="button" type="submit" value="ändern">
			</td>
		</tr>
	</table>
</form>
{elseif $ACTION == 'alnamechange'}
<!--  								-->
<!--  Allianzname ändern 			-->
<!--  								-->
<br><br>
<form action="politik.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" align="center">
		<tr>
			<td class="siteGround" height="20">
				Derzeitiger Allianzname
			</td>
		</tr>
		<tr>
			<td height="40">
				<input type="hidden" name="action" value="alnamechange">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" name="newalname" value="{$ALNAME}" size="50" maxlength="50">
			</td>
		</tr>
		<tr>
			<td align="right">
				<input class="button" type="submit" value="ändern">
			</td>
		</tr>
	</table>
</form>
{elseif $ACTION == 'chanm'}
<!--  								-->
<!--  Syndikatsankündigung ändern 	-->
<!--  								-->
<center>
	<br><br>
	<b>Syndikatsankündigung ändern</b>
	<br><br>
</center>
{if $IAC == 'next'}
<br><br>
<form action="politik.php" method="post">
	<input type="hidden" name="action" value="chanm">
	<input type="hidden" name="iac" value="finish">
	<center>Vorschau</center>
	<table cellspacing="1" cellpadding="0" align="center" width="500" class="tableOutline">
		<tr>
			<td>
				<table align="center" cellpadding="4" cellspacing="0" class="tableInner1" width="100%">
					<tr>
						<td>
							{$NEWANNOUNCEMENT_BBC}
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<center>
		<input type="hidden" name="newannouncement" value="{$NEWANNOUNCEMENT}">
		<input type="submit" value="Syndikatsankündigung ändern">
	</center>
</form>
<br><br><br>
{/if}
<center>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="chanm">
		<input type="hidden" name="iac" value="next">
		<table class="siteGround" align="center">
			<tr>
				<td>
				{if $NEWANNOUNCEMENT}
					Wenn du noch nicht zufrieden bist kannst du hier jederzeit nochmal nachbessern.
				{/if}
					<br>BBCode-Hilfe 
					<a href="javascript:info('hilfe','bbcode')" class="highlightAuftableInner">
						<img src="{$RIPF}_help.gif" border="0" valign="absmiddle"></a>
				</td>
			</tr>
			<tr>
				<td>(Maximal 1.500 Zeichen, kein HTML)</td>
			</tr>
			<tr>
				<td align="center">
					<textarea name="newannouncement" cols="60" rows="20">{if $NEWANNOUNCEMENT}{$NEWANNOUNCEMENT}{else}{$ANNOUNCEMENT}{/if}</textarea>
				</td>
			</tr>
			<tr>
				<td align="center">
					<input type="submit" value="weiter zur Vorschau">
				</td>
			</tr>
		</table>
	</form>
</center>
{elseif $ACTION == 'synbeschr'}
<!--  								-->
<!--  Syndikatsbeschreibung ändern 	-->
<!--  								-->
<center>
	<br><br>
	<b>Syndikatsbeschreibung ändern</b>
	<br><br>
</center>
	{if $IAC == "next"}
<br><br>
<form action="politik.php" method="post">
	<input type="hidden" name="action" value="synbeschr">
	<input type="hidden" name="iac" value="finish">
	<center>Vorschau</center>
	<table cellspacing="1" cellpadding="0" align="center" width="500" class="tableOutline">
		<tr>
			<td>
				<table align="center" cellpadding="4" cellspacing="0" class="tableInner1" width="100%">
					<tr>
						<td>
							{$NEWANNOUNCEMENT_BBC}
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<center>
		<input type="hidden" name="newannouncement" value="{$NEWANNOUNCEMENT}">
		<input type="submit" value="Syndikatsbeschreibung ändern">
	</center>
</form>
<br><br><br>
	{/if}
<center>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="synbeschr">
		<input type="hidden" name="iac" value="next">
		<table class="siteGround" align="center">
			<tr>
				<td>
					{if $NEWANNOUNCEMENT}
					Wenn du noch nicht zufrieden bist kannst du hier jederzeit nochmal nachbessern.
					{/if}
					<br>
					BBCode-Hilfe 
					<a href="javascript:info('hilfe','bbcode')" class="highlightAuftableInner">
						<img src="{$RIPF}_help.gif" border="0" valign="absmiddle"></a>
				</td>
			</tr>			
			<tr>
				<td>(Maximal 10.000 Zeichen, kein HTML)</td>
			</tr>
			<tr>
				<td align="center">
					<textarea name="newannouncement" cols="60" rows="20">{if $NEWANNOUNCEMENT}{$NEWANNOUNCEMENT}{else}{$ANNOUNCEMENT}{/if}</textarea>
				</td>
			</tr>
			<tr>
				<td align="center">
					<input type="submit" value="weiter zur Vorschau">
				</td>
			</tr>
		</table>
	</form>
</center>
{elseif $ACTION == 'chspw'}
<!--  								-->
<!--  Syndikatspasswort ändern 		-->
<!--  								-->
<br><br>
<form action="politik.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="500">
		<tr>
			<td class="siteGround" height="20">
				Momentanes Syndikatspasswort:<br>
				(es wird empfohlen kein privates Passwort zu wählen, falls Sie irgendwann einmal nicht mehr Präsident sein sollten)
			</td>
		</tr>
		<tr>
			<td height="40">
				<input type="hidden" name="action" value="chspw">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" name="newsyndpassword" value="{$CURRENTPW}" size="30" maxlength="30">
			</td>
		</tr>
		<tr>
			<td align="right">
				<input class="button" type="submit" value="ändern">
			</td>
		</tr>
	</table>
</form>
{elseif $ACTION == 'chlsw'}
<!--  								-->
<!--  Syndikatswebsite ändern 		-->
<!--  								-->
<br><br>
<form action="politik.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="500">
		<tr>
			<td class="siteGround" height="20">
				<strong>Bitte achten Sie auf die in der Hilfe zu Politik für das Eintragen eines Links auf eine Syndikatswebsite 
				festgelegten Bestimmungen (insbesondere <i>muss</i> auf der Startseite ein Backlink auf <i>syndicates-online.de</i> 
				gesetzt sein!). Bei Missbrauch droht Sperrung oder gar Löschung des Konzerns!</strong><br><br>Momentan verlinkte 
				Syndikatswebsite:<br>(Um eine bestehende Verlinkung zu löschen, einfach den Link löschen und auf <strong>ändern</strong> 
				klicken)
			</td>
		</tr>
		<tr>
			<td height="40">
				<input type="hidden" name="action" value="chlsw">
				<input type="hidden" name="ia" value="next">
				<input class="input" type="text" name="newsyndwebsite" value="{$CURRENTWS}" size="50" maxlength="255">
			</td>
		</tr>
		<tr>
			<td align="right">
				<input class="button" type="submit" value="ändern">
			</td>
		</tr>
	</table>
</form>
{elseif $ACTION == 'chsbil'}
<!--  								-->
<!--  Syndikatbild ändern 			-->
<!--  								-->
<br><br>
<table cellpadding="1" cellspacing="0" border="0" align="center" valign="middle" class="tableOutline">
	<tr>
		<td>
			<table cellpadding="3" cellspacing="0" border="0" align="center" class="tableInner1">
				{if $IMAGE}
				<tr>
					<td class="tableInner1">
						Momentanes Syndikatsbild: <br><br>
						<center>
							<img src="syndikatsimages/{$KBILD_PREFIX}{$STATUS.rid}{$IMAGE}" border="0">
						</center>
					</td>
				</tr>
				<tr>
					<td align="center">
						<form action="politik.php" method="post">
							<input type="hidden" name="action" value="chsbil">
							<input type="hidden" name="ia" value="del">
							<input type="submit" value="Bild löschen">
						</form>
						<br><br>
					</td>
				</tr>
				{/if}
				<tr>
					<td align="left" class="tableInner1">
						Bitte wähle ein Bild von deiner Festplatte aus (max. 100 KB, 540x80 Pixel):
					</td>
				</tr>
				<tr>
					<td height="40" align="center">
						<form action="politik.php" method="post" enctype="multipart/form-data">
							<input type="hidden" name="action" value="chsbil">
							<input type="hidden" name="ia" value="next">
							<input type="hidden" name="MAX_FILE_SIZE" value="102400">
							<input type="file" name="sbil" value="" size="20">
							<input type="submit" value="hochladen">
						</form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{elseif $ACTION == 'dw'}
<!--  																-->
<!--  KRIEG ERKLÄREN										 		-->
<!--  																-->
	{if $SHOW}
<br><br><br>
<center>
	Bitte geben Sie die Nummer des Syndikats ein, welchem Sie den Krieg erklären möchten!
	<br><br>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="dw">
		<input type="hidden" name="ia" value="next">
		<input type="text" maxlength="3" size="2" name="enemyid">&nbsp;&nbsp;
		<input type="submit" value="weiter">
	</form>
	<br><br>
	<a href="politik.php" class="linkAufsiteBg">Zurück</a>
</center>
	{elseif $SHOW_ACCEPT}
<br><br><br>
<center>
	Möchten Sie dem Syndikat "<strong>{$ENEMY} (#{$ENEMYID})</strong>"
	{$PRE1} {$ALLIES} wirklich den Krieg erklären?<br><br>
	<a href="politik.php?action=dw&ia=finish&enemyid={$ENEMYID}" class="linkAufsiteBg">Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
	{elseif $SHOW_NOTALLOWED}
	<br><br><br>
	Die international verabschiedeten Kriegskonventionen gestatten es Ihrem Syndikat nicht, dem
	Syndikat "{$ENEMY}" (#{$ENEMYID}" {if $WITH_ALLIED} und seinen Verbündeten{/if} den 
	Krieg zu erklären.<br>
	Grund:<br>
	{$REASON}
	<br><br>
	<center>
		<a href="javascript:history.back()" class="linkAufsiteBg">zurück</a>
	</center>
	{/if}
{elseif $ACTION == 'ae'}
<!--  																-->
<!--  ALLIANZANFRAGE STELLEN (ursprünglich Allianz eingehen) 		-->
<!--  																-->
	{if $SHOW}
<br><br><br>
<center>
	Bitte geben Sie die Nummer des Syndikats ein, mit dem Sie eine Allianz eingehen möchten!<br><br>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="ae">
		<input type="hidden" name="ia" value="next">
		<input type="text" maxlength="3" size="2" name="allyid">&nbsp;&nbsp;
		<input type="submit" value="weiter">
	</form>
	<br><br>
	<a href="politik.php" class="linkAufsiteBg">Zurück</a>
</center>
	{elseif $SHOW_ACCEPT}
<br><br><br>
<center>
	Möchten Sie sich wirklich mit dem Syndikat "{$ALLY} (#{$ALLYID})" 
	{if $THIRD}<br>und dessen Bündnispartner "{$THIRD} (#{$THIRDID})"{/if} verbünden?
	<br><br>
	<a href="politik.php?action=ae&ia=finish&allyid={$ALLYID}" class="linkAufsiteBg">
		Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
</center>
	{/if}
{elseif $ACTION == 'ab'}
<!--  											-->
<!--  ALLIANZANFRAGE BESTÄTIGEN (ablehnen) 		-->
<!--  											-->
	{if !$WHAT}
<br><br><br>
<center>
	Bitte wählen Sie, ob Sie einer Allianz mit dem Syndikat "{$SYNNAME} (#{$SYNID})" 
	{if $ZWEIT_NAME}<br>und dem Syndikat "{$ZWEIT_NAME} (#$ZWEIT_ID)"{/if}
	zustimmen oder diese ablehnen!<br><br>
	<a href="politik.php?action=ab&what=accept" class="linkAufsiteBg">Zustimmen</a> - 
	<a href="politik.php?action=ab&what=decline" class="linkAufsiteBg">Ablehnen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
</center>
	{elseif $WHAT == "accept"}	
<br><br><br>
<center>
	Möchten Sie sich wirklich mit dem Syndikat "{$SYNNAME} (#{$SYNID})"
	{if $ZWEIT_NAME}<br>und dem Syndikat "{$ZWEIT_NAME} (#$ZWEIT_ID)"{/if}
	verbünden?<br><br>
	<a href="politik.php?action=ab&what=accept&ia=finish" class="linkAufsiteBg">Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
</center>
	{elseif $WHAT == "decline"}	
<br><br><br>
<center>
	Möchten Sie die Allianzverhandlungen mit dem Syndikat "{$SYNNAME} (#{$SYNID})"
	{if $ZWEIT_NAME}<br>und dem Syndikat "{$ZWEIT_NAME} (#$ZWEIT_ID)"{/if}
	wirklich beenden?<br><br>
	<a href="politik.php?action=ab&what=decline&ia=finish" class="linkAufsiteBg">Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
</center>
	{/if}
{elseif $ACTION == 'pkv'}
<!--  											-->
<!--  inaktiven Spieler aus dem Syndikat kicken	-->
<!--  											-->
	{if !$SHOW_ACCEPT}
<br><br><br>
<center>
	Bitte wählen Sie den inaktiven Spieler den Sie aus Ihrem Syndikat ausschließen möchten aus der Auswahlliste aus!
	Der Spieler kommt dann in einen Inaktivenpool, von dem her automatisch einem Syndikat zugewießen wird, sobald
	dieser aktiv wird.
	<br><br>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="pkv">
		<input type="hidden" name="ia" value="next">
		<select name="who">
			{foreach from=$PLAYERS item=VL}
				{if $VL.id != $ID}<option value="{$VL.id}">{$VL.rulername} von {$VL.syndicate}{/if}
			{/foreach}
		</select>&nbsp;&nbsp;
		<input type="submit" value="weiter">
	</form>
	<br><br>
	<a href="politik.php" class="linkAufsiteBg">Zurück</a>
</center>
	{else}
<br><br><br>
<center>
	Möchten Sie den Spieler {$TOKICK_RULERNAME} von {$TOKICK_SYNDICATE} wirklich aus dem Syndikat werfen
	und in den inaktiven Pool schieben?
	<br><br>
	<a href="politik.php?action=pkv&ia=finish&who={$WHO}" class="linkAufsiteBg">Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">Abbrechen</a>
</center>
	{/if}
{elseif $ACTION == 'synfus'}
<!--  											-->
<!--  Syndikatsfusionierung				 		-->
<!--  											-->
{if $SHOW_NEW_SELECTSYN}
<br><br>
Bitte geben Sie die Syndikatsnummer des Syndikats an, mit welchem Sie fusionieren möchten. Beachten Sie bitte, dass bei 
Zustandekommen der Fusionierung das networthschwächere Syndikat in das networthstärkere Syndikat eingegliedert wird.<br>
Zum Zustandekommen der Fusionierung bedarf es der Zustimmung des Präsidenten des anderen Syndikats.<br><br>
Es können nur Syndikate miteinander fusionieren, deren Spielerzahl {$MAX_PLAYERS_FOR_FUSIONIERUNG} oder weniger beträgt, 
die sich nicht in einem Krieg befinden, die keine Allianzverhandlungen führen und die keiner Allianz angehören. 
Nach der Fusion dürfen außerdem nicht mehr als {$MAX_USERS_A_GROUP} Spieler im fusionierten Syndikat vorhanden sein.<br><br>
<form action="politik.php" method="post">
	<table width="400" align="center">
		<tr class="siteGround">
			<td>
				<input type="hidden" name="action" value="synfus">
				<input type="hidden" name="ia" value="next">
				<br>
				<center>
					Syndikatsnummer:<br><br>
					<input type="text" name="synnummer" value=""><br><br>
					<input type="submit" value="weiter">
				</center>
			</td>
		</tr>
	</table>
</form>
{elseif $SHOW_NEW_ACCEPT}
<br><br>
<center>
	Wollen Sie wirklich mit dem Syndikat <b>{$SYNNAME} (#{$SYNNUMMER})</b> fusionieren?
	<br><br>
	<a href="politik.php?action=synfus&ia=finish&synnummer={$SYNNUMMER}" class="linkAufSitebg">JA</a>
	&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; 
	<a href="politik.php" class="linkAufSitebg">Abbrechen</a>
</center>
{elseif $SHOW_ABLEHNEN}
<br><br>
<center>
	Möchten Sie die Fusionierungsverhandlungen mit dem Syndikat <b>{$SYNNAME} (#{$SYNNUMMER})</b> wirklich abbrechen?
	<br><br>
	<a href="politik.php?action=synfus&sac=handle&what=decline&ia=finish&who={$SYNNUMMER}" class="linkAufSitebg">JA</a>
	&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;
	<a href="politik.php" class="linkAufSitebg">Abbruch</a>
</center>
{elseif $SHOW_ACCEPT}
<br><br>
<center>
	Möchten Sie wirklich mit dem Syndikat <b>{$SYNNAME} (#{$SYNNUMMER})</b> fusionieren?<br><br>
	<a href="politik.php?action=synfus&sac=handle&what=accept&ia=finish&who={$SYNNUMMER}" class="linkAufSitebg">JA</a>
	&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;
	<a href="politik.php" class="linkAufSitebg">Abbruch</a>
</center>
{elseif $ACTION == 'pa_new' && FALSE}
<!--  											-->
<!--  Privates Abkommen abschließen (pa) 		-->
<!--  											-->
	{if $SHOW_PLAYER}
<br><br><br>
<center>
	Bitte geben Sie die Nummer des Syndikats an, in welchem sich der Spieler, dem Sie ein Abkommen vorschlagen möchten, befindet!
	<br><br>
	<form action="politik.php" method="post">
		<input type="hidden" name="action" value="pa">
		<input type="text" name="rid" value="{$RID}" size="3"> 
		<input type="submit" value="weiter">
	</form>
	<br><br>
	<a href="politik.php" class="linkAufsiteBg">Zurück</a>
</center>
	{elseif $SHOWTYPE}
<br><br>
Wählen Sie nun den gewünschten Spieler sowie Art des Abkommens aus, und geben Sie an wie lange die Kündigungsfrist und wie 
hoch die Kündigungsstrafe sein soll!<br>
Erläuterung: Nach Abschluss kann der Vertrag jederzeit gekündigt werden. Hierbei muss jedoch die Kündigungsfrist eingehalten 
werden und es müssen vom Kündigenden Cr. in Höhe der Kündigungsstrafe gezahlt werden. Dieses Geld wird vernichtet und kommt 
niemandem zu Gute.<br><br>
<form action="politik.php" method="get">
	<center>
		<input type="hidden" name="action" value="pa">
		<input type="hidden" name="rid" value="{$RID}">
		<table class="siteGround" cellpadding="5" cellspacing="0" border="0">
			<tr class="siteGround">
				<td>Spieler wählen</td>
				<td>
					<select name="plid">
						{foreach from=$PLAYERS item=VL}
							{if $VL != $ID}<option value="{$Vl.ID}">{$VL.rulername} von {$VL.syndicate}</option>{/if}
						{/foreach}
					</select>
				</td>
			</tr>
			<tr class="siteGround">
				<td>Art des Abkommens</td>
				<td>
					<select name="abkommen">
						<option value="1">Nichtangriffspakt (NAP)</option>
						<option value="2">Nichtspionagepakt (NSP)</option>
						<option value="3">Nichtangriffsspionagepakt (NASP)</option>
					</select>
				</td>
			</tr>
			<tr class="siteGround">
				<td>Kündigungsfrist</td>
				<td>
					<select name="frist">
						<option value="24">24h</option>
						<option value="48" selected>48h</option>
						<option value="96">96h</option>
						<option value="168">168h</option>
					</select>
				</td>
			</tr>
			<tr class="siteGround">
				<td>Kündigungsstrafe</td>
				<td>
					<select name="kstrafe">
						<option value="0">0 Cr.</option>
						<option value="5000">5.000 Cr.</option>
						<option value="10000">10.000 Cr.</option>
						<option value="25000">25.000 Cr.</option>
						<option value="50000">50.000 Cr.</option>
						<option value="100000" selected>100.000 Cr.</option>
						<option value="250000">250.000 Cr.</option>
						<option value="500000">500.000 Cr.</option>
						<option value="1000000">1.000.000 Cr.</option>
						<option value="2500000">2.500.000 Cr.</option>
						<option value="5000000">5.000.000 Cr.</option>
						<option value="10000000">10.000.000 Cr.</option>
						<option value="25000000">25.000.000 Cr.</option>
						<option value="50000000">50.000.000 Cr.</option>
						<option value="100000000">100.000.000 Cr.</option>
					</select>
				</td>
			</tr>
		</table>
		<br><br>
		ACHTUNG: Idealerweise sollten vor Absenden dieses Abkommens-Vorschlags bereits Verhandlungen mit dem gewählten Spieler 
		vorausgegangen sein (z.B. über die Mitteilungen). Sinnlos gestellte Abkommens-Vorschläge werden in der Regel vom jeweiligen Spieler meist abgelehnt.
		<br><br>
		<input type="submit" value="weiter">
		<br><br><a href="politik.php" class="linkAufsiteBg">Zurück</a>
	</center>
</form>
	{/if}
{elseif $ACTION == 'pa_edit' && FALSE}
<!--  																						-->
<!--  Privates Abkommen bearbeiten (pab) # Annehmen, Ablehnen, Zurücknehmen, Kündigen 		-->
<!--  																						-->
<br><br><br>
<center>
	Möchten Sie dieses Abkommen wirklich {$OPTION}?<br><br>
	<a href="politik.php?action=pab&ia=finish&what=$what&abkommen={$ABKOMMEN}" class="linkAufsiteBg">
		Bestätigen</a> - 
	<a href="politik.php" class="linkAufsiteBg">
		Abbrechen</a>
</center>
{/if}{/if}