{if $USERINPUT}
{$USERINPUT}
{else}
{if $SHOW != "showsynd"}
<br>
<br>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top" class="siteGround">
			Hier können Sie Forschungen in Auftrag geben. 
			Ebenfalls ist es möglich, für das Syndikat Forschungen zu betreiben:<br>
			<br>
			<img src="{$LAYOUT.images}dot-gelb.gif" align="absmiddle" border="0">
				<a class="linkAufsiteBg" href="forschung.php?show=showsynd">
					Syndikatsforschungen anzeigen
				</a>
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;
		</td>
		<td valign="top">
			<table cellpadding="5" cellspacing="1" border="0" width="330" class="tableOutline" style="margin-right:10px;">
				<tr class="tableHead">
					<td align="center" colspan="2">
						Forschungsassistent
					</td>
				</tr>
                <tr class="tableInner1">
					<td width="250">
						Aktuell: <b>{if $BUILD_SCIENCE.name}<a href=forschung.php?ia=killqu&what=sc&type={$BUILD_SCIENCE.name}&killtime=1 class="linkAuftableInner">{$BUILD_SCIENCE.o_gamename}</a>{else}keine{/if}</b>
					</td>
					<td width="80">
					{if $BUILD_SCIENCE.name}
						{if $STATUS.beraterview}
							{$TIMETOGO_DATE}
						{else}
							{$TIMETOGO} Stunde{if $TIMETOGO > 1}n{/if}
						{/if}
					{else}
						&nbsp;
					{/if}
					</td>
				</tr>
		{if !$FORSCHUNGSQ} 
				<tr class="tableInner1">
					<td colspan="2">
						Mit diesem Assistenten können Sie bis zu 5 Forschungen in eine Warteschlange legen, die nach
						und nach abgearbeitet wird.
					</td>
				</tr>
				<tr class="tableInner2">
					<td colspan="2" align="center">
						<a href="premiumfeatures.php" class="linkAufsiteBg">
							Mehr Informationen
						</a>
					</td>
				</tr>
		{else}
			{if !$QUEUED}
				<tr class="tableInner1">
					<td colspan="2" align="center">
						Keine Einträge in der Warteliste.
					</td>
				</tr>
			{else}
				{foreach from=$QUEUED item=VL}
				<tr class="tableInner1">
					<td>
						{$VL.position}. {$VL.o_gamename} Level {$VL.o_level} <br />
						{if $VL.o_fosbar == 1}
							Kosten: {$VL.o_fosbar_kosten} FP
						{elseif $VL.o_fosbar == 0 && $VL.o_fosbar2 == "sciencepoints"}
							<font class="highlightAuftableInner">
								Kosten: {$VL.o_fosbar_kosten} FP
							</font>
						{elseif $VL.o_fosbar == 0 && $VL.o_fosbar2 == "baumstruktur"}
							<font class="achtungAuftableInner">
								Zur Zeit nicht möglich.
							</font>
						{else}
							<font class="achtungAuftableInner">
								Nicht mehr möglich.
							</font>
						{/if}
					</td>
					<td>
						{if $VL.position > 1}
						<a href="forschung.php?action=modifyqueue&pos={$VL.position}&down=1" class="linkaufSiteBg">
							<img border="0" src="{$RIPF}_for_up.gif">
						</a>
						{/if}
						{if $VL.position < $ANZ_FOR}
						<a href="forschung.php?action=modifyqueue&pos={$VL.position}&up=1" class="linkaufSiteBg">
							<img border="0" src="{$RIPF}_for_down.gif">
						</a>
						{/if}
						<a href="forschung.php?action=unqueue&pos={$VL.position}" class="linkaufSiteBg">
							<img border="0" src="{$RIPF}_for_deselect.gif">
						</a>
					</td>
				</tr>
				{/foreach}
				<tr class="tableInner2">
					<td colspan="2" align="center">
						<a href="forschung.php?action=unqueueall" class="linkAufsiteBg">
							Warteliste leeren
						</a>
					</td>
				</tr>
			{/if}
		{/if}
			</table>
		</td>
	</tr>
</table>
<br>
{foreach from=$FOS item=TREE}
<table width="600" cellspacing="1" cellpadding="0" border="0" class="tableOutline">
	<tr>
		<td>
			<table class="tableHead" width="598" cellspacing="0" cellpadding="5">
				<tr >
					<td align="center">
						{if $TREE.name == "mil"}Military Sciences
						{elseif $TREE.name == "ind"}Industrial Sciences
						{elseif $TREE.name == "glo"}Intelligence Sciences
						{elseif $TREE.name == "all"}Common Sciences
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{foreach from=$TREE.sciencestats item=VL}
		{if !$VL.o_stufe}
	<tr>
		<td>
			<table class="tableInner1" border="0" cellspacing="0" cellpadding="5" width="598">
				<tr >
					<td colspan="2" align="left" width="368" align="left">
						<span class ="highlightAuftableInner">
							{$VL.short}
						</span> 
						<b>
							{$VL.gamename}
						</b>
					{if $FORSCHUNGSQ && $VL.o_inAssi_position}
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<font class="highlightAuftableInner">
							Wartelistenposition: {$VL.o_inAssi_position}
						</font>
					{/if}
					</td>
					<td width="90" align="left" valign="top" rowspan="2">
						{section name="I" start=0 loop=$VL.o_lvl step=1}
							<img src="{$LAYOUT.images}erforscht.gif" border="0">
						{/section}
						{section name="I" start=$VL.o_lvl loop=$VL.maxlevel step=1}
							<img src="{$RIPF}_for_nicht_erforscht.gif" border="0">
						{/section}
						<br>
						{if $VL.o_lvl != 0}Level {$VL.o_lvl} {else} nicht erforscht{/if}<br>
						<img src="{$RIPF}5E78A4.gif" width="90" height="1" border="0">
					</td>
					<td width="140" align="left">
						 {if $VL.o_show_kosts}{$VL.o_kosts} P{else}&nbsp;{/if}
					</td>
				</tr>
				<tr >
					<td width="12">&nbsp;
					</td>
					<!-- ROWSPAN! <td> </td> -->
					<td align="left" width="356">
						{$VL.o_description}
					</td>
				{if $VL.o_show_erforschen}
					{if $VL.o_lvl < $VL.maxlevel}
						{if $BUILD_SCIENCE.name == $VL.o_key}
					<td align="right" class="achtungAuftableInner" width="130">
							Noch {if $TIMETOGO > 1}{$TIMETOGO} Stunden{else}eine Stunde{/if}
						{else}
					<td align="left" width="140">
						<span id="{$VL.o_key}_number"><a href="forschung.php?inneraction={$VL.o_key}&lck=1" id="{$VL.o_key}_button" class="linkAuftableInner">
							erforschen
						</a></span>
							{if $STATUS.later_started_bonus && !$VL.o_Ausnahme}<br>
							<a href="forschung.php?inneraction={$VL.o_key}&lck=1&push=1" class="linkAuftableInner">
								Forschungsbonus nutzen
							</a>
							{/if}
						{/if}
					{else}
					<td align="right" class="highlightAuftableInner" width="130">
						erforscht
					{/if}
				{else}
					<td width="140">
				{/if}
				{if $FORSCHUNGSQ}<br>
					{if $VL.o_nochForschable}
						{if $VL.o_already_inAssi == 1}
							<a href="forschung.php?action=queue&what={$VL.o_key}" class="linkAuftableInner">
								der Warteliste hinzufügen
							</a>
							<br>
							<a href="forschung.php?action=unqueue&pos={$VL.o_delposition}" class="linkAuftableInner">
								von Warteliste entfernen
							</a>
						{elseif !$VL.o_already_inAssi}
							<a href="forschung.php?action=queue&what={$VL.o_key}" class="linkAuftableInner">auf Warteliste setzen</a>
						{/if}
					{/if}
				{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
		{else}
	<tr>
		<td align="left">
			<table class="tableHead2" width="598" cellspacing="0" cellpadding="5">
				<tr >
					<td>
						Forschungsstufe {$VL.o_stufe}
					</td>
					<td align="right">
						Bauzeit: {$VL.o_duration} Ticks - 
						Networth: {$VL.o_NW}/Stufe
					</td>
				</tr>
			</table>
		</td>
	</tr>
		{/if}
	{/foreach}
</table>
<br>
<br>
{/foreach}
{elseif $SYND_FOS_SHOW}
<br>
<br>
<center>
	{if $AKTIENPOSSIBILITIES or $ALLY1}
		Ihre Aktien erlauben Ihnen, die erledigten Syndikatsforschungen aus den folgenden Syndikaten mitzuverfolgen:<br />
		{foreach from=$AKTIENSYNDS item=TEMP}
			<a href="forschung.php?show=showsynd&synd_id={$TEMP.synd_id}" class="linkAuftableInner">
				#{$TEMP.synd_id}
			</a>
		{/foreach}
		<br />
		Syndikatsforschungen Ihres Bündnispartners <a href=forschung.php?show=showsynd&synd_id={$ALLY1} class=linkAufsiteBg>#{$ALLY1}</a> einsehen.
<br />
<br />
		{if $SYND_ID}
			<b>Syndikatsforschungen von (#{$SYND_ID}):</b><br>
			<br>
		{/if}
	{/if}
	<table border="0" class="tableOutline" cellspacing="1" cellpadding="5" width="600" align="center">
		<tr class="tableHead">
			<td width="112" align="center">
				{if $MYSYN}Spieler{/if}
			</td>
			<td width="120" align="center">
				{$SCIENCES.ind16_name}
			</td>
			<td width="120" align="center">
				{$SCIENCES.ind15_name}
			</td>
			<td width="120" align="center">
				{$SCIENCES.glo12_name}
			</td>
		</tr>
	{if $MYSYN}
		{foreach from=$STATUSES item=VL}
		<tr class="tableInner1">
			<td align="left" width="112">
				{$VL.syndicate}
			</td>
			<!-- Inner Syndicate Energy Saving Program -->
			<td width="120" align="center">
				{section name="I" start=0 loop=$VL.ind16_lvl}
					<img src="{$LAYOUT.images}erforscht.gif" border="0">
				{/section}
				{section name="I" start=$VL.ind16_lvl loop=$SCIENCES.ind16_maxlevel}
					<img src="{$RIPF}_for_nicht_erforscht.gif" border="0">
				{/section}
				{if $VL.o_develop == "ind16"} [{$VL.o_develop_hours}h]{/if}
			</td>
			<!-- Inner Syndicate Trade Program -->
			<td width="120" align="center">
				{section name="I" start=0 loop=$VL.ind15_lvl}
					<img src="{$LAYOUT.images}erforscht.gif" border="0">
				{/section}
				{section name="I" start=$VL.ind15_lvl loop=$SCIENCES.ind15_maxlevel}
					<img src="{$RIPF}_for_nicht_erforscht.gif" border="0">
				{/section}
				{if $VL.o_develop == "ind15"} [{$VL.o_develop_hours}h]{/if}
			</td>
			<!-- Inner Syndicate Spy Defense Network -->
			<td width="120" align="center">
				{section name="I" start=0 loop=$VL.glo12_lvl}
					<img src="{$LAYOUT.images}erforscht.gif" border="0">
				{/section}
				{section name="I" start=$VL.glo12_lvl loop=$SCIENCES.glo12_maxlevel}
					<img src="{$RIPF}_for_nicht_erforscht.gif" border="0">
				{/section}
				{if $VL.o_develop == "glo12"} [{$VL.o_develop_hours}h]{/if}
			</td>
		</tr>
		{/foreach}
	{/if}
		<tr class="tableInner2">
			<td width="112" align="center">
				Gesamt:
			</td>
			<!-- Inner Syndicate Energy Saving Program -->
			<td width="120\" align="center">
				<b>Level 1: {$SCIENCES.ind16_lvl1}</b>
			</td>
			<!-- Inner Syndicate Trade Program -->
			<td width="120" align="center">
				<b>Level 1: {$SCIENCES.ind15_lvl1}</b><br>
				<b>Level 2: {$SCIENCES.ind15_lvl2}</b><br>
				<b>Level 3: {$SCIENCES.ind15_lvl3}</b>
			</td>
			<!-- Inner Syndicate Spy Defense Network -->
			<td width="120" align="center">
				<b>Level 1: {$SCIENCES.glo12_lvl1}</b><br>
				<b>Level 2: {$SCIENCES.glo12_lvl2}</b><br>
				<b>Level 3: {$SCIENCES.glo12_lvl3}</b>
			</td>
		</tr>
		<tr class="tableInner2">
			<td width="112" align="center">
				Effektiver Bonus:
			</td>
			<td width="120" align="center">
				<b>
					{$SCIENCES.ind16_effektiv}%
				</b>
			</td>
			<td width="120" align="center">
				<b>
					{$SCIENCES.ind15_effektiv} Cr
				</b>
			</td>
			<td width="120" align="center">
				<b>
					{$SCIENCES.glo12_effektiv}%
				</b>
			</td>
		</tr>
	</table>
</center>
<br>
<p align="left">
	<a class="linkAufsiteBg" href="javascript:history.back()">
		<u>Zurück</u>
	</a>
</p>
{/if}
{/if}