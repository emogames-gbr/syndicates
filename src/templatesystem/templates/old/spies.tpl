{include file='js/spies.js.tpl'}
{if $RESULT && $RESULT.success == 1 && !$ISJOB}
	<center>
		<br><br>
		Sie konnten die Spionageaktion <b>{$RESULT.name}</b> gegen
		<b>{$RESULT.victim_name} (#{$RESULT.victim_rid})</b>
		<br>- {$RESULT.victim_nw}Nw, {$RESULT.victim_land}ha am {$RESULT.time} Uhr {if $IS_CEST}MESZ{else}MEZ{/if} -
		<br><b>erfolgreich</b> ausführen.<br><br>
		<br>{$RESULT.header}<br><br>
		<table border=0 align=center cellspacing=0 cellpadding=0 class="tableOutline">
			<tr>
				<td>
					<table border=0 cellspacing=1 cellpadding=3 width=300>
						{if $RESULT.op != "newsintel"}
							<tr>
								<td colspan=2 class="tableHead">
									<b>Übersicht</b>
								</td>
							</tr>
						{/if}
						{foreach from=$RESULT.ausgabe item=PAIR}
							{if $PAIR.value == '----------'}
							<tr>
								<td width=300 class="tableInner1" colspan="2" align="center">
									{$PAIR.name} {$PAIR.value}
								</td>
							</tr>
							{else}
							<tr>
								<td width=200 class="tableInner1">
									{$PAIR.name}
								</td>
								<td width=100 class="tableInner1" align=right>
									{$PAIR.value}&nbsp;&nbsp;&nbsp;
								</td>
							</tr>
							{/if}
						{/foreach}
					</table>
				</td>
			</tr>
		</table>
		<br><br>
	</center>
	{if $RESULT.op == "newsintel"}
		<p align="center">Aktuelles aus Syndikat (#{$RESULT.victim_rid})</p>
		<center>
			{foreach from=$RESULT.news item=NEWS}
				<table cellpadding="0" cellspacing="0" border="0" class="tableOutline">
					<tr>
						<td>
							<table  border="0" cellspacing="1" cellpadding="5" width=570>
								<tr class="tableHead">
									<td colspan=2 align="center" valign="middle" height="15">{$NEWS.name}</td>
								</tr>
								{if $NEWS.details}
									{foreach from=$NEWS.details item=DETAIL}
										<tr class="tableInner1">
											<td width=60 align=center>
												{$DETAIL.time}
											</td>
											<td>
												{$DETAIL.msg}
											</td>
										</tr>
									{/foreach}
								{else}
									<tr class="tableInner1">
										<td width=570 colspan=2 align=center>Keine Daten vorhanden</td>
									</tr>
								{/if}
							</table>
						</td>
					</tr>
				</table>
				<br>				
			{/foreach}
			<br>
			<table cellpadding="0" cellspacing="0" border="0" class="tableOutline">
				<tr>
					<td>
						<table  border="0" cellspacing="1" cellpadding="5" width=370>
							<tr class="tableHead">
								<td colspan=4 align="center" valign="middle" height="15">Syndikatsforschungen:</td>
							</tr>
							{foreach from=$RESULT.synfos item=SYNFOS}
								<tr class="tableInner1">
									<td>
										<b>{$SYNFOS.name}:</b>
									</td>
									<td>
										{foreach from=$SYNFOS.levels item=LEVEL}
											<b>{$LEVEL}</b><br>									
										{/foreach}
									</td>
								</tr>
							{/foreach}
						</table>
					</td>
				</tr>
			</table>
        </center>
	{elseif $RESULT.op == "unitintel2" || $RESULT.op == "unitintel1"}
		{if $RESULT.op == "unitintel2"}
			{foreach from=$RESULT.mill_table item=TABLE}
				{if $TABLE.name}
					<b><u>{$TABLE.name}</u></b><br><br>
					<table width="600" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
						<tr>
							<td>
								<table  border="0" cellspacing="1" width="100%" cellpadding="3">
									<tr class={$TABLE.class}>
										<td align=center>#</td><td align=middle> &nbsp;1 </td>
										<td align=middle> &nbsp;2 </td>
										<td align=middle> &nbsp;3 </td>
										<td align=middle> &nbsp;4 </td>
										<td align=middle> &nbsp;5 </td>
										<td align=middle> &nbsp;6 </td>
										<td align=middle> &nbsp;7 </td>
										<td align=middle> &nbsp;8 </td>
										<td align=middle> &nbsp;9 </td>
										<td align=middle> 10 </td>
										<td align=middle> 11 </td>
										<td align=middle> 12 </td>
										<td align=middle> 13 </td>
										<td align=middle> 14 </td>
										<td align=middle> 15 </td>
										<td align=middle> 16 </td>
										<td align=middle> 17 </td>
										<td align=middle> 18 </td>
										<td align=middle> 19 </td>
										<td align=middle> 20 </td>
									</tr>
									{foreach from=$TABLE.rows item=ROW}
										<tr class="tableInner1">
											<td width=105> {$ROW.name}</td>
											{foreach from=$ROW.details item=YROW}
												<td align=middle>{$YROW}</td>
											{/foreach}
										</tr>
									{/foreach}
								</table>
							</td>
						</tr>
					</table>
				{else}
					<b><u>{$TABLE.error}</u></b>
				{/if}
				<br><br>
			{/foreach}
		<table border=0 align=center cellspacing=0 cellpadding=0 class="tableOutline">
			<tr>
				<td>
					<table border=0 cellspacing=1 cellpadding=3 width=300>
						<tr>
							<td colspan=2 class="tableHead">
								<b>Syndikatsarmee</b>
							</td>
						</tr>
						<tr>
							<td width=200 class="tableInner1">Ranger</td>
							<td width=100 class="tableInner1" align=right>{$RESULT.army_ranger}&nbsp;&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td width=200 class="tableInner1">Marines</td>
							<td width=100 class="tableInner1" align=right>{$RESULT.army_rines}&nbsp;&nbsp;&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br><br><br><br>
		<center>
			<table cellspacing=1 width="400" cellpadding=5 border=0 class="tableOutline">
				<tr>
					<td class="tableHead" height=20>
						Partnerschaftsboni 
						<a href="../index.php?action=docu&kat=2&aid=27" class="linkAufsiteBg" target="_blank">
							<img src="{$RIPF}_help.gif" border="0" align="absmiddle">
						</a>
					</td>
				</tr>
				<tr>
					<td class="tableInner1">
						<table cellspacing=0 cellpadding=0 border=0 class="tableInner1">
							<tr>
								<td>
									{if $RESULT.pbs}
										<ul>
											{foreach from=$RESULT.pbs item=PB}
												<li>{$PB}<br>
											{/foreach}
										</ul>
									{else}
										Noch keine Boni gewählt!
									{/if}
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</center>
		{/if}
	{/if}
{elseif $RESULT && $RESULT.success == 0}
	<center>
		<br><br>
		<b>Spionageeinsatz fehlgeschlagen</b><br>
        <br>
        Sie konnten die Spionageaktion <i>{$RESULT.name}</i> 
		gegen <b>{$RESULT.victim_name} (#{$RESULT.victim_rid})</b> nicht erfolgreich ausführen.<br>
        <br>
        <table border=0 align=center cellspacing=0 cellpadding=0 class="tableOutline">
			<tr>
				<td>
					<table border=0 cellspacing=1 cellpadding=3 width=300>
						<tr>
							<td colspan=2 class="tableHead">
								<b>Verluste</b>
							</td>
						</tr>
						{foreach from=$RESULT.losses item=LOSS}
							<tr>
								<td width=200 class="tableInner1">{$LOSS.name}</td>
								<td width=100 class="tableInner1">{$LOSS.value}</td>
							</tr>
						{/foreach}
					</table>
				</td>
			</tr>
		</table>
		<br><br>
	</center>
{/if}

<br><br>
{$RACHERECHT_AUSGABE}
<table border="0" cellspacing=0 cellpadding=0 class="tableOutline">
	<tr>
		<td>
			{if !$RESULT}
				<table border=0 cellspacing=0 cellpadding=0 width=600>
					<tr>
						<form action="spies.php" method="post">
							<td width="160" class="siteGround">
								Syndikat auswählen:
							</td>
							<td width="300" class="siteGround">
								<b>
									<a class="linkAufsiteBg" href="spies.php?rid={$RIDLEFT}">&lt; zurück</a>
									&nbsp;&nbsp;(
								</b>
								<input type="text" name="rid" value="{$RID}" maxlength="3" size="2"> <b>#)</b>
								&nbsp;&nbsp;
								<b>
									<a class="linkAufsiteBg" href="spies.php?rid={$RIDRIGHT}">vor &gt;</a>
								</b>
							</td>
							<td width="140" class="siteGround">
								<input type="submit" value="auswählen">
							</td>
						</form>
					</tr>
					<tr width="600">
						<td valign="top" width="160" class="siteGround">
							Konzern auswählen:
						</td>
						<td valign="top" width="300" class="siteGround">
							<table border="0" cellspacing="0" cellpadding="1" width="270" class="tableOutline">
								<tr>
									<td>
										<table border="0" cellspacing="0" cellpadding="0" width="270" class="tableInner1">
											<form action="spies.php" method="post">
											<input type="hidden" name="inneraction" value="prepare">
											<input type="hidden" name="rid" value="{$RID}">
											<tr>
												<td class="tableInner1">
													<br>
													{if $PLAYERS}
														{foreach from=$PLAYERS item=PLAYER}
															&nbsp;
															<input name="target" type="radio" value="{$PLAYER.id}" 
															{if $PLAYER.error}
																 {if $PLAYER.error=="Buddy"}{else}disabled{/if} 
															{/if}
															{if $PLAYER.current}
																 checked 
															{/if}
															> 
															{if $PLAYER.mentor}
																&nbsp;&nbsp;&nbsp;
																<img src="{$PLAYER.mentor}" title="Spionageaktionen sind zu 100% erfolgreich" height=10px border=0 align="absmiddle">
															{/if}
															<span id="id_{$PLAYER.id}">{$PLAYER.name}</span>
															{if $PLAYER.job}
																<font class=highlightAuftableInner>AUFTRAGSZIEL</font>
															{/if}
															{if $PLAYER.error}
																<font class=highlightAuftableInner>
																	[{$PLAYER.error}]
																</font>
															{/if}
															<br>
														{/foreach}
													{else}
														<center>Keine Spieler im entsprechenden Syndikat gefunden</center><br>
													{/if}
													<br>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td width="140" class="siteGround" valign="bottom">
						</td>
						</form>
					</tr>
				</table>
			{/if}
			{if $RESPY}
				<table border=0 cellspacing=0 cellpadding=0 width=600>
					<tr>
						<td align="center" class="siteGround">
							<table cellspacing="0" cellpadding="3" width="600"> 
								<tr>
									<form action="spies.php" method="post">
									<input id="target_id" type="hidden" name="target" value="{$RESPY.target}">
									<input type="hidden" name="rid" value="{$RESPY.rid}">
									<td valign="top" width="160" class="siteGround">
										Gewähltes Ziel:
									</td>
									<td width="440" class="siteGround" colspan="2" valign="bottom">
										<b><div id="target_name">{$RESPY.targetname}</div></b>
									</td>
								</tr>
								<tr>
									<td valign="top" width="160" class="siteGround">
										Aktion auswählen:
									</td>
									<td valign="top" width="300" class="siteGround">
											<select id="target_select" name="inneraction" size="1" {if $RESPY.target == ''}disabled{/if} onChange="updateZusatzOpsBoni()">
												{if $RESPY.target == ''}<OPTION value="-1">Kein Ziel gewählt</OPTION>{/if}
												{foreach from=$RESPY.actions item=SPYACTION}
													<OPTION value="{$SPYACTION.key}" {$SPYACTION.disable}>
														{$SPYACTION.name}
													</OPTION>
												{/foreach}
											</select>
											<br><br>
											<div id="extra_ops" {if $RESPY.target == ''}style="display: none"{/if}>
												Zusätzliche Spionageaktionen: 
												<input size="3" name="addactions" value="0" style="font-size:11px;vertical-align:middle">(max 5)<br>
												(bewirkt pro Spionageaktion <span id="zusatzops_boni">{$ZUSATZOPS_BONI}</span>% erhöhte Erfolgswahrscheinlichkeit)
											</div>
									</td>
									{if !$RESPY.target}
										<td valign="top" width="140" class="siteGround">
										<input id="target_submit" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LOS!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" disabled>
									{else}
										<td valign="top" width="140" class="siteGround">
										<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LOS!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
									{/if}
										</form>
									</td>
								</tr>
								<tr>
									<td valign="top" width="100%" colspan="3" align="center" width="160" class="siteGround">
										Sie haben noch <span class="highlightAufSiteBg">{$RESPY.ops}
										</span>  Spionageaktionen  (maximal: <span class="highlightAufSiteBg">{$RESPY.maxops}</span>)
									</td>
								</tr>
								<tr>
									<td valign="top" width="160" class="siteGround">
										Sonstiges:
									</td>
									<td width="440" class="siteGround" colspan="2" valign="bottom">
										<table class="tableInner1" style="border:1px solid; width:300px;">
											<tr>
												<td>
													{if $RESULT}
													<a class="linkAuftableInner" href="spies.php?inneraction=prepare&rid={$RESPY.rid}&target={$RESPY.target}">
														<img src="{$RIPF}/dot-gelb.gif" hspace="5" border="0">anderes Ziel wählen</a><br>
													
													<a class="linkAuftableInner" href="angriff.php?inneraction=prepare&rid={$RESPY.rid}&target={$RESPY.target}">
														<img src="{$RIPF}/dot-gelb.gif" hspace="5" border="0">Ziel angreifen</a><br>
													{/if}
													<a class="linkAufTableInner" href="history.php?selectview=spyactions">
														<img src="{$RIPF}/dot-gelb.gif" hspace="5" border="0">Spionagedatenbank</a>
												</td>
											</tr>
										</table>										
									</td>
								</tr>
							</table>
							<br>
						</td>
					</tr>
				</table>
			{/if}
		</td>
	</tr>
</table>
<br><br>
