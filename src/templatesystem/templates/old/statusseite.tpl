<table cellspacing=5 cellpadding=0 width=600 border=0 align="center">
	{if $showRoundInfo}
	<tr>
		<td valign=top colspan=3 width=600><!-- roundinfo -->
			<div>
				<table cellspacing=5 cellpadding=0 width=600 border=0 class="siteGround">
					<tr>
						<td>
							<center>
								<font class=siteGround>
									{if $showRoundInfo == "end"}
										Achtung! Die aktuelle Runde endet in
									{else}
										Hinweis! Die Runde startet in
									{/if}
									<strong class="highlightAufSiteBg">
										{$rnd_day}
									</strong>
									Tagen, 
									<strong class="highlightAufSiteBg">
										{$rnd_std}
									</strong>
									Stunden und 
									<strong class="highlightAufSiteBg">
										{$rnd_min}
									</strong>
									Minuten.
									{if $showRoundInfo == "end"}
										<br>Das letzte stündliche Update 
									{else}
										<br>Das erste stündliche Update 
									{/if}
									findet statt am 
									{$rnd_date}
									 um 
									{$rnd_time}
									Uhr.<br>&nbsp;
								</font>
							</center>
						</td>
					</tr>
				</table>	
			</div>
		</td>
	</tr>
	{/if}
	{if $showGlobalNews}
	<tr>
		<td valign=top colspan=3 width=600><!-- globaleankündigung -->
			<div>
				<table width="600" cellpadding="5" cellspacing="1" class="tableOutline" border="0">
					<tr>
						<td class="tableHead" align="left" width="20%">
							{$global_poster}
						</td>
						<td class="tableHead" align="center">
							{$global_headine}
						</td>
						<td class="tableHead" align="center" width=20>
							<a href="statusseite.php" style="text-decoration:none;color:#000000">X</a>
						</td>
					</tr>
					<tr class="tableInner1">
						<td colspan=3>
							{$global_content}
							<p align=right>
								<font style="font-size:10px">
									{$global_time}
								</font>
							</p>
						</td>
					</tr>
				</table>	
			</div>
		</td>
	</tr>
	{/if}

	{if $showGroups}
	<tr>
		<td valign=top colspan=3 width=600><!-- gruppen -->
			<div>
				<table cellpadding="5" cellspacing="1" border="0" width="600">
					<tr>
						<td class=siteGround>
							{if $GROUP}
							<br>
							<center>
								Sie befinden sich in Gruppe "<a href="gruppen.php" class="linkAuftableInner">{$GROUP.name}</a>" für die {if $IS_NEXT}nächste{else}aktuelle{/if} Runde.
							</center>
							{else}
							<a href="gruppen.php" class="linkAuftableInner">
								<table width="550" style="border:1px solid;" class="f" cellpadding="2" align="center">
									<tr>
										<td>
											Sie befinden sich in keiner Gruppe, 
									<a href="gruppen.php" class="linkAuftableInner">suchen sie sich noch eine Gruppe</a> 
									für die {if $IS_NEXT}nächste{else}aktuelle{/if} Runde oder 
									<a href="gruppen.php?gaction=create" class="linkAuftableInner">erstellen</a>
									sie sich eine eigene Gruppe. (Wenn Sie in keiner Gruppe sind, werden Sie zufällig einem Syndikat zugeteilt!)
										</td>
									</tr>
								</table>
							</a>
							{/if}
							<br><br>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	{/if}

	{if $showProtectInfo}
	<tr>
		<td valign=top colspan=3 width=600><!-- protect -->
			<div>
				<table cellpadding="5" cellspacing="1" border=0 width="600" class="tableOutline">
					{if $showProtectInfo=="config"}
						<tr>
							<td class="siteGround">
								<center>
									<font>
										Ihr Konzern steht unter Schutz bis sie die Konfiguration abgeschlossen haben und ist anschließend nochmals für maximal
										<strong class="highlightAufSiteBg">
											 {$prot_time}
										</strong>
										h geschützt.
										<br>&nbsp;
									</font>
								</center>
							</td>
						</tr>
					{elseif $showProtectInfo=="protection"}
						<tr>
							<td class="siteGround">
								<center>
									<font>
										Ihr Konzern steht noch für maximal 
										<strong class="highlightAufSiteBg">{$prot_day}</strong>
										Tage, 
										<strong class="highlightAufSiteBg">{$prot_std}</strong>
										Stunden und 
										<strong class="highlightAufSiteBg">{$prot_min}</strong>
										Minuten unter Schutz.<br>&nbsp;
									</font>
								</center>
							</td>
						</tr>
					{/if}
				</table>
			</div>
		</td>
	</tr>
	{/if}
		{if $IS_OSTERN && $OSTER_BONI.19237}
		<div style="position:relative;">
			<div style="float:right;">
				<a class="normal" href="bonus.php?type=4&amp;egg=19237">
					<img src="images/ostern_19237.png"></a>
			</div>
			Rechtzeitig zu Ostern gibt es nun ein kleines Suchspiel. Es sind an ein paar Stellen Ostereier
			wie dieses hier versteckt. Für jedes das ihr findet bekommt ihr durch draufklicken {$OSTER_BONI_AMOUNT} Cr 
			gutgeschrieben. Viel Spaß beim Suchen!<br><br>
			Das Syndicates-Staff-Team wünscht euch frohe Ostern!
		</div>
		{/if}
		
	{if $GAMBLENUM >= 1 || $DAILYNUM >= 1}
	<tr>
		<td valign=top colspan=3 width=600><!-- Gamble-Boni -->
			<div>
				<table width="100%" cellspacing="0" cellpadding="0">
					<tr>
						{if $GAMBLENUM >= 1}
						<td valign="baseline" align="{if $DAILYNUM >= 1}left{else}center{/if}">
							<table cellpadding="5" cellspacing="1" border=0 width="290" class="tableOutline">
								<tr>
									<td class="tableHead">Glücksspiel - {$GAMBLE_TAKEN} von {$GAMBLE_MAX} gewählt <a href="{$wiki}Statusseite" class="linkAufsiteBg" target="_blank" style="float:right"><img src="{$ripf}_help.gif" border="0" align="absmiddle"></a></td>
								</tr>
								<tr>
									<td class="tableInner1" align="center">
									{if $GAMBLE_ASK}{if $GAMBLE.label != 'zocken'}+{$GAMBLE.value} {$GAMBLE.label} wirklich nehmen?<br />{else}Möchten Sie wirklich einen zufälligen Bonus?<br />{/if}
										<form action="statusseite.php" method="post" name="gamble_form">
											<input type="hidden" name="action" value="gamble" />
											<input type="hidden" name="name" value="{$GAMBLE.name}" />
											<a href="javascript: document.gamble_form.submit();"  class="linkAuftableInner">Ja</a> - <a href="statusseite.php"  class="linkAuftableInner">Nein</a>
										</form>
									{else}Sie haben noch {$GAMBLENUM} {if $GAMBLENUM > 1}Boni{else}Bonus{/if} zur Auswahl:
										<form action="statusseite.php" method="post">
											<input type="hidden" name="action" value="gamble_pre" />
											<table class="tableInner1" border="0">
												{foreach from=$GAMBLEVALUES item=VALUE}<tr>
													<td><input type="radio" name="name" value="{$VALUE.name}" id="{$VALUE.name}" /></td>
													<td><label for="{$VALUE.name}">{if $VALUE.name == 'gamble'}{$VALUE.label} (+{$VALUE.value}% eines Randombonis){else}{$VALUE.value|number_format} {$VALUE.label}{/if}</label></td>
												</tr>
												{/foreach}
											</table>
											<input type="submit" value="bestätigen" />
										</form>
									{/if}</td>
								</tr>
							</table>
						</td>
						{/if}
						{if $DAILYNUM >= 1}
						<td valign="baseline" align="{if $GAMBLENUM >= 1}right{else}center{/if}">
							<table cellpadding="5" cellspacing="1" border=0 width="290" class="tableOutline">
								<tr>
									<td class="tableHead">täglicher Bonus <a href="{$wiki}Statusseite" class="linkAufsiteBg" target="_blank" style="float:right"><img src="{$ripf}_help.gif" border="0" align="absmiddle"></a></td>
								</tr>
								<tr>
									<td class="tableInner1" align="center">
									{if $DAILY_ASK}{if $DAILY.label != 'zocken'}+{$DAILY.value} {$DAILY.label} wirklich nehmen?<br />{else}Möchten Sie wirklich einen zufälligen Bonus?<br />{/if}
										<form action="statusseite.php" method="post" name="daily_form">
											<input type="hidden" name="action" value="daily" />
											<input type="hidden" name="name" value="{$DAILY.name}" />
											<a href="javascript: document.daily_form.submit();"  class="linkAuftableInner">Ja</a> - <a href="statusseite.php"  class="linkAuftableInner">Nein</a>
										</form>
									{else}Sie haben noch {$DAILYNUM} {if $DAILYNUM > 1}Boni{else}Bonus{/if} zur Auswahl:
										<form action="statusseite.php" method="post">
											<input type="hidden" name="action" value="daily_pre" />
											<table class="tableInner1" border="0">
												{foreach from=$DAILYVALUES item=VALUE}<tr>
													<td><input type="radio" name="name" value="{$VALUE.name}" id="{$VALUE.name}" /></td>
													<td><label for="{$VALUE.name}">{if $VALUE.name == 'gamble'}{$VALUE.label} (+{$VALUE.value}% eines Randombonus){else}{$VALUE.value|number_format} {$VALUE.label}{/if}</label></td>
												</tr>
												{/foreach}
											</table>
											<input type="submit" value="bestätigen" />
										</form>
									{/if}
								</tr>
							</table>
						</td>
						{/if}
					</tr>
				</table>
				<br />
			</div>
		</td>
	</tr>
	{/if}
	{if $showInternNews}
	<tr>
		<td valign=top colspan=3 width=600><!-- präsiankündigung -->
			<div>
				<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline">
					<tr>
						<td align=center class="tableHead" width=100% onClick="doAnimation();" style="cursor:pointer">
							<b><u>Ankündigung des Präsidenten ({$news_chars} Zeichen)</u></b>
						</td>
						<td nowrap class=tableHead>vom {$news_time} Uhr</td>
					</tr>
					<tr id="announcement" style="display:{$news_style1}">
						<td class="tableInner1" align=left colspan=2>
							<div id="president_announcement" {$news_style2}>
								{$news_text}
							</div>
						</td>
					</tr>
				</table>
				{literal}
				<script type="text/javascript">
					var currentAnimation;
					var active = {/literal}{$news_style3}{literal};
					var dabei = false;
					
					function doAnimation() {
						if(active == false && !dabei){
							{/literal}{$news_style4}{literal}
							dabei = true;
							active = true;
							//document.getElementById('president_announcement').style.display = '';
							document.getElementById('announcement').style.display = '';
							currentAnimation = dojo.fx.wipeIn({node: "president_announcement",duration: 1000});
							currentAnimation.play();
							//dabei = false;
							window.setTimeout("dabei = false;", 1000);
						}
						else if(active == true && !dabei){
							dabei = true;
							active = false;
							currentAnimation = dojo.fx.wipeOut({node: "president_announcement",duration: 1000});
							currentAnimation.play();
							//document.getElementById('president_announcement').style.display = 'none';
							//document.getElementById('announcement').style.display = 'none';
							window.setTimeout("document.getElementById('announcement').style.display = 'none';", 1000);
							window.setTimeout("dabei = false;", 1000);
							//dabei = false;
						}
					}
				</script>
				{/literal}
			</div>
		</td>
	</tr>
	{/if}
	{if $showNotice}
	<tr>
		<td valign=top colspan=3 width=600><!-- notizen -->
			<div>
				<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline">
					<tr>
						<td align=center class="tableHead" colspan=3>Notizen</td>
					</tr>
					<tr>
						<td class="tableInner1" align=left colspan=3>
							{$notice}
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	{/if}
	<tr>
		<td valign=top width=200 rowspan="3">
			<div><!-- prod -->		
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">Produktion pro Stunde</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
								<tr>
									<td>
										Credits 
										{if $criticalenergy1}<font class=highlightAuftableInner> * &#189;</font>
										{/if}
									</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$moneyadd}
										</b>
									</td>
									<td width="30">&nbsp;Cr</td>
								</tr>  
								<tr>
									<td>
										Erz 
										{if $criticalenergy1}<font class=highlightAuftableInner> * &#189;</font>
										{/if}
									</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$metaladd}
										</b>
									</td>
									<td>&nbsp;t</td>
								</tr>
								<tr>
									<td>
										Forschungspunkte 
										{if $criticalenergy1}<font class=highlightAuftableInner> * &#189;</font>
										{/if}
									</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$sciencepointsadd}
										</b>
									</td>
									<td>&nbsp;P</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div><!-- nrg -->
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">
							Energie 
							{if $criticalenergy2}
								<b style="font-size:11px;font-family:arial,verdana,sans-serif;">[reicht für {$criticalenergy2}]</b>
							{/if}
						</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
								<tr>
									<td>Produktion</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$energyprod}
										</b>
									</td>
									<td width="30">&nbsp;MWh</td>
								</tr>
								<tr>
									<td>Verbrauch</td>
									<td align=right>
										<b class="highlightAuftableInner">
											-{$energyuse}
										</b>
									</td>
									<td>&nbsp;MWh</td>
								</tr>
								<tr>
									<td colspan=3 align="center">
										<hr size="1px" style="color:#000" />
									</td>
								</tr>
								<tr>
									<td>Bilanz<br>&nbsp;</td>
									<td align=right>
										<b class="{if $criticalenergy2}achtungAuftableInner{else}highlightAuftableInner{/if}">
											{$energyadd}
										</b>
										<br>&nbsp;
									</td>
									<td>
										&nbsp;MWh
										{if $maxsave_reached}
											&nbsp;
											<b class=highlightAuftableInner>*</b>
										{/if}
										<br>&nbsp;
									</td>
								</tr>
								{if $warner}
									<tr>
									<td colspan=3 align=right>
										<b class=highlightAuftableInner>*</b>
										<strong class="achtungAuftableInner">Lagerkapazitäten erschöpft</strong>
										<br>&nbsp;
									</td>
									</tr>
								{/if}	
							</table>
						</td>
					</tr>
				</table>
			</div>
			{if $showBoniInfo}
			<div><!-- boni -->
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					{foreach from=$bonus item=bonkeys}
						<tr>
							<td class="tableHead">
								Boni {$bonkeys[0]}
							</td>
						</tr>
						<tr>
							<td class="tableInner1">
								<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
									{foreach from=$bonkeys[1] item=bonus}
										<tr>
											<td>
												{$bonus[0]}
											</td>
											<td align=right>
												<b class="highlightAuftableInner">
													{$bonus[1]}
												</b>
											</td>
											<td width="30">&nbsp;%</td>
										</tr>
									{/foreach}
									<tr>
										<td colspan=3 align="center">
											<hr size="1px" style="color:#000" />
										</td>
									</tr>
									<tr>
										<td>Gesamt</td>
										<td align=right>
											<b class="highlightAuftableInner">
											{$bonkeys[2]}
											</b>
										</td>
										<td>&nbsp;%</td>
									</tr>
								</table>
							</td>
						</tr>
					{/foreach}
				</table>
			</div>
			{/if}
			{if $showVotesAdds}
			<br>
			<div><!-- dynavote -->
				{if !$galaxy_news_done}
					<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
						<tr>
							<td class="tableHead" align=center>{$galaxy_credits} Credits</td>
						</tr>
						<tr>
							<td class="tableInner1" align=center>
								Aktueller Vote-Counter: <b>{$galaxy_votecounter}</b> Votes
							</td>
						</tr>
						<tr>
							<td class="tableInner1" align=center>
								<a href="bonus.php?site=mmofacts&type=3" target="_blank">
									<img src="images/voting_mmofacts.png" style="border:0;" alt="mmofacts">
								</a>								
							</td>
						</tr>
					</table>
				{else}
					<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
						<tr>
							<td class="tableHead" align=center>
								Du hast heute schon gevotet.<br>
								Heute wurde insgesamt schon <b>{$galaxy_votecounter}</b> mal gevotet.
								<a href="http://de.mmofacts.com/syndicates-44#track" class="mmofacts-widget" data-style="horizontal-counter" data-id="44">
									mmofacts
								</a>
								<script type="text/javascript" src="http://www.mmofacts.com/static/js/widget.js"></script>
							</td>
						</tr>
					</table>
				{/if}
			</div>
			<br>
			<div><!-- Crboni -->
				{if $crboniclicks == "click"}
					<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
						<tr>
							<td class="tableHead" align=center>Credit-Bonus</td>
						</tr>
						<tr>
							<td class="tableInner1" align=center>
								Aktueller Bonus: <b>{$crboni}</b> Credits
							</td>
						</tr>
						<tr>
							<td class="tableInner1" align=center>
								{if !$isnewuser}
									<A HREF="bonus.php?site=videogames&type=1" target="_blank" class=linkAuftableInner>Computerspiele</A> |
									<A HREF="bonus.php?site=buecher&type=1" target="_blank" class=linkAuftableInner>Bücher</A> |
									<A HREF="bonus.php?site=musik&type=1" target="_blank" class=linkAuftableInner>Musik</A> |
									<A HREF="bonus.php?site=dvd&type=1" target="_blank" class=linkAuftableInner>DVDs</A>
								{else}
									<A HREF="bonus.php?site=Jamba_Klingeltoene&type=1" target="_blank" class=linkAuftableInner>Jamba Klingeltöne</A>
								{/if}
							</td>
						</tr>
					</table>
				{elseif $crboniclicks == "done"}
					<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
						<tr>
							<td class="tableHead" align=center>
								Du hast deinen Credit-Bonus in Höhe von {$crboni} Credits diese Stunde bereits erhalten.
							</td>
						</tr>
					</table>
				{else}
					<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
						<tr>
							<td class="tableHead" align=center>Du hast heute bereits alle möglichen Credit-Boni erhalten.</td>
						</tr>
					</table>
				{/if}
			</div>
			<br>
			<div><!-- landboni -->
			{if $haboniclicks == "click"}
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead" align=center>Land-Bonus</td>
					</tr>
					<tr>
						<td class="tableInner1" align=center>
							Aktueller Bonus: <b>{$haboni}</b> ha
						</td>
					</tr>
					<tr>
						<td class="tableInner1" align=center>
							<a href="bonus.php?site={$halinkdataid}&type=2" target="_blank" class=linkAuftableInner>
								{$halinkdatatext}
							</a>
						</td>
					</tr>
				</table>
			{elseif $haboniclicks == "done"}
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead" align=center>
							Du hast deinen Land-Bonus in Höhe von {$haboni} ha diese Stunde bereits erhalten.
						</td>
					</tr>
				</table>
			{else}
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead" align=center>
							Du hast heute bereits alle möglichen Land-Boni erhalten.
						</td>
					</tr>
				</table>
			{/if}
			</div>
			{/if}
		</td>
		<td valign="top" width="200" height="200px">
			<div><!-- mill -->
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">Militär</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width="100%" class="tableInner1">
								<tr>
									<td>Streitkräfte</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$mill_da}
										</b>
									</td>
								</tr>
								<tr>
									<td>auf Heimkehr</td>
									<td align=right>
										{$mill_weg}
									</td>
								</tr>
								<tr>
									<td>zum Verkauf</td>
									<td align=right>
										{$mill_markt}
									</td>
								</tr>
								<tr>
									<td>in Produktion</td>
									<td align=right>
										{$mill_inbau}
									</td>
								</tr>
								<tr>
									<td colspan=3 align="center">
										<hr size="1px" style="color:#000" />
									</td>
								</tr>
								<tr>
									<td>Gesamt</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$mill_total}
										</b>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<br />
			<div><!-- spies -->
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">Geheimdienst</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
								<tr>
									<td>Spione</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$spy_da}
										</b>
									</td>
								</tr>
								<tr>
									<td>zum Verkauf</td>
									<td align=right>
										{$spy_markt}
									</td>
								</tr>
								<tr>
									<td>in Ausbildung</td>
									<td align=right>
										{$spy_inbau}
									</td>
								</tr>
								<tr>
									<td colspan=3 align="center">
										<hr size="1px" style="color:#000" />
									</td>
								</tr>
								<tr>
									<td>Gesamt</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$spy_total}
										</b>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div><!-- trade -->
			{if $showTradeInfo}
			<br />
			<div>
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead" align=center>Inner Syndicate Trade Program</td>
					</tr>
					<tr>
						<td class="tableInner1" align=center>
							<form action="statusseite.php" style="margin:0px">
								<select name="istp_res" style="width:150px">
									{foreach from=$ressi item=res}					
										<option value="{$res[0]}" {$res[1]}>{$res[2]}
									{/foreach}						
								</select>
								<input type="hidden" name="inneraction" value="setistp">
								<input type="submit" value="ändern">
							</form>
						</td>
					</tr>
						{if $istp_changetime > 0}
							<tr>
								<td class="tableInner1" align=center>
									<b>Produktionsbeginn in {$istp_changetime} Tick{if $istp_changetime > 1}s{/if}.</b>
								</td>
							</tr>
						{/if}
				</table>
			</div>
			{/if}
		</td>
		<td valign=top width="200">
			<div><!-- nextprod -->
				<table cellspacing=1 cellpadding=4 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">Nächste Produktion</td>
					</tr>
					<tr>
						<td class="tableInner1" align="center">
							<b class="highlightAuftableInner">
							<span style="font-size:15px">
								{$next_prod}
							</span>
							</b>
						</td>
					</tr>
				</table>
			</div>
			<br />
			<div><!-- landgebs -->
				<table cellspacing=1 cellpadding=4 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead">Gebäude & Land</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
								<tr>
									<td>Gebäude</td>
									<td align=right>
										<b class="highlightAuftableInner">
											{$geb_da}
										</b>
									</td>
								</tr>
									<td>in Bau</td>
									<td align=right>
										{$geb_inbau}
									</td>
								</tr>
								<tr>
									<td>Gesamt</td>
									<td align=right>
										{$geb_total}
									</td>
								</tr>
								<tr>
									<td><br>Land</td>
									<td align=right>
										<br>
										<b class="highlightAuftableInner">
											{$land_total}
										</b>
									</td>
								</tr>
								<tr>
									<td>unbebaut</td>
									<td align=right {if $land_unbebaut>0}class="achtungAuftableInner"{/if}>
										{$land_unbebaut}
									</td>
								</tr>
								<tr>
									<td>in Vorbereitung</td>
									<td align=right>
										{$land_inbau}
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div><!-- unprot -->
			{if $showUnprotectBox}
			<br />
			<div>
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%">
					<tr>
						<td class="tableHead" align=center>Schutzzeit verlassen</td>
					</tr>
					<tr>
						<td class="tableInner1" align=left>
							Wenn Sie die Schutzzeit frühzeitig verlassen erhalten Sie pro Stunde einen geringen Produktionsbonus auf die Standardressource Ihrer Fraktion.{$unprotect_tooltip}<br><br> 
							Im Gegenzug verzichten Sie auf den Schutz vor Angriffen, Spionage und Diebstahl. Da Sie nicht mehr in die Schutzzeit zurückkehren können, sobald Sie diese einmal verlassen haben, 
							sollten Sie sich diesen Schritt gut überlegen. 
						</td>
					</tr>
					<tr>
						<td class="tableInner1" align=center>
							<form action="statusseite.php" method="post">
								<input type="hidden" name="inneraction" value="unprotect">
								<input class="button" type="submit" value="Schutz verlassen"> 
							</form>
						</td>
					</tr>
				</table>
			</div>
			{/if}
		</td>
	</tr>
	{if $showStorageProduction}
		<tr>
			<td valign="top" colspan=2 width="400px" height="20px">
			<!-- lagerprod -->
				<br />
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%" align="left" valign="top">
					<tr>
						<td class="tableHead">
							Syndikatsproduktion pro Tick
						</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=370 class="tableInner1">
								<tr>
									<td width=140>&nbsp;</td>
									<td width=85 align=center>Produktion</td>
									<td width="145">{$st_curr} (int. Währung)</td>
								</tr>
								<tr>
									<td colspan=3 align="center">
										<hr size="1px" style="color:#000" />
									</td>
								</tr>
								{if $st_cr_x}
									<tr>
										<td>Credits {$criticalenergy1}</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_cr_x}
											</b> Cr
										</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_cr_hp}
											</b>
										</td>
									</tr>
								{/if}
								{if $st_nrg_x}
									<tr>
										<td>Energie</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_nrg_x}
											</b> MWh
										</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_nrg_hp}
											</b>
										</td>
									</tr>
								{/if}
								{if $st_fp_x}
									<tr>
										<td>Forschungspunkte {$criticalenergy1}</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_fp_x}
											</b> P
										</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_fp_hp}
											</b>
										</td>
									</tr>
								{/if}
								{if $st_erz_x}
									<tr>
										<td>Erz {$criticalenergy1}</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_erz_x}
											</b> t
										</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_erz_hp}
											</b>
										</td>
									</tr>
								{/if}
								{if $st_hp}
									<tr>
										<td colspan=3 align="center">
											<hr size="1px" style="color:#000" />
										</td>
									</tr>
									<tr>
										<td>Gesamt</td>
										<td align=center>&nbsp;</td>
										<td align=center>
											<b class="highlightAuftableInner">
												{$st_hp}
											</b>
										</td>
									</tr>
								{/if}
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $showPartnerBoni}
	<!-- partnerboni -->
		<tr>
			<td valign="top" colspan=2 width="400px">
			<!-- lagerprod -->
				<br />
				<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width="100%" align="left" valign="top">
					<tr>
						<td class="tableHead" height=20>
							<a name=pboni></a>
							Partnerschaftsboni 
							<a href="{$wiki}Partnerschaften" class="linkAufsiteBg" target="_blank" style="float:right">
								<img src="{$ripf}_help.gif" border="0" align="absmiddle">
							</a>
						</td>
					</tr>
					<tr>
						<td class="tableInner1">
							<table cellspacing=0 cellpadding=0 border=0 width=370 class="tableInner1">
								<tr>
									<td>
								{if $PARTNER_1}
									{foreach from=$PARTNER_1 item=TYPE}
										{if $TYPE.boni}
										{$TYPE.name}:<br>
										<ul>
											{foreach from=$TYPE.boni item=PART}
											<li>{$PART.bonus}</li>
											{/foreach}
										</ul>
										{/if}
									{/foreach}
								{else} 
									Noch keine Boni gewählt!
								{/if}
									</td>
								</tr>
								{if $PARTNER_DIFF >= 1}
									<tr>
										<td>
											<hr width=100%>
										</td>
									</tr>
									<tr>
										<td> 
											Sie können noch insgesamt {$PARTNER_DIFF} Partnerschafts{if $PARTNER_DIFF > 1}boni{else}bonus{/if} wählen. Beachten Sie jedoch bitte, dass die Wahl nicht mehr rückgängig gemacht werden kann! (maximal {$PBS_PER_TYPE_CHOOSEABLE} pro Kategorie wählbar)
										</td>
									</tr>
									<tr>
										<td>
											<br>
										{foreach from=$PARTNERSCHAFTEN item=TYPE}
											{if $TYPE.is_full}
											{$TYPE.name}: (keine Boni mehr wählbar)<br>
											{elseif $TYPE.boni}
											{$TYPE.name}:<br>
											<ul>
												{foreach from=$TYPE.boni item=PART}
												<li>
													<a href="statusseite.php?action=setpartner&pid={$PART.id}" class="linkAuftableInner">
														{$PART.bonus}
													</a>
												</li>
												{/foreach}
											</ul>
											{/if}	
										{/foreach}
										</td>
									</tr>
								{/if}
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
</table>
