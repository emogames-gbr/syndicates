    <br>
    <table width="90%" align="center">
    	<tr class="siteGround">
    		<td align="left" width="60%">
    			Letzte Aktualisierung des Land- und Networth-Rankings: {$hourtime} Uhr
    		</td>
    		<td align="right" width="50%">
    		</td>
    	</tr>
    </table>
    <center>
    	<br>
        <strong>Sortieren nach:</strong>
        <table cellspacing="0" cellpadding="3" border="0" class="siteGround">
        	<tr>
        		<td align="right">
        			<strong>Networth:</strong>
        		</td>
        		<td>
                {foreach from=$links_z1 item=temp}
					{if $temp.qst != $myQueryString}
	                    <a class="highlightAufSiteBg" href="rankings.php?{$temp.qst}">{$temp.text}</a> | 
					{else}
	                    <b class="linkAufsiteBg">{$temp.text}</b> | 
					{/if}
                {/foreach}
            	</td>
        	</tr>
            <tr>
            	<td align="right">
            		<strong>Land:</strong>
            	</td>
            	<td>
                {foreach from=$links_z2 item=temp}
					{if $temp.qst != $myQueryString}
	                    <a class="highlightAufSiteBg" href="rankings.php?{$temp.qst}">{$temp.text}</a> | 
					{else}
	                    <b class="linkAufsiteBg">{$temp.text}</b> | 
					{/if}
                {/foreach}
                </td>
            </tr>
            <tr>
            	<td align="right">
            		<strong>Eroberungen:</strong>
            	</td>
            	<td>
                	{if $ranktype != "eroberungen"}
                	<a class="highlightAufSiteBg" href="rankings.php?ranktype=eroberungen">Konzerne</a>
                	{else}
                	<b class="linkAufsiteBg">Konzerne</b>
                	{/if}
                </td>
            </tr>
            <tr>
            	<td align="right">
            		<strong>Diebe:</strong>
            	</td>
            	<td>
                	{if $ranktype != "diebe"}
                	<a class="highlightAufSiteBg" href="rankings.php?ranktype=diebe">Konzerne</a>
					{else}
					<b class="linkAufsiteBg">Konzerne</b>
					{/if}
                </td>
            </tr>
		{if false && $noBasicServer}
			<tr>
				<td align="right">
					<strong>Aktionäre:</strong>
				</td>
				<td>
                	{if $ranktype != "aktionaere"}
                	<a class="highlightAufSiteBg" href="rankings.php?ranktype=aktionaere">Konzerne</a>
					{else}
                    <b class="linkAufsiteBg">Konzerne</b>
                    {/if}
            </tr>
		{/if}
        </table>
        <br>
	{if $ranktype == "eroberungen"}
		<strong>Die erfolgreichsten Eroberer:<br>
		(Es sind nur Angriffe berücksichtigt, die älter als 24h sind)</strong>
		<br>&nbsp;
		{if $in_protection}
			<br>Das Ranking für Eroberungen ist erst nach der Schutzzeit verfügbar.
		{else}
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
			<tr>
				<td>
					<table width="550" cellpadding="2" cellspacing="1" border="0">
						<tr class="tableHead" height="23">
							<td width="40" align="center">Rang</td>
							<td width="260" colspan="2">Name</td>
							<td width="80" align="right">ha Netto</td>
							<td width="85" align="right">ha erobert</td>
							<td width="85" align="right">ha verloren</td>
						</tr>
					{foreach from=$forsort item=vl}
						<tr class={if $vl.syndicate == $status.syndicate}"tableInner2"{else}"tableInner1"{/if} height="23">
							<td align="center">{$vl.o_count}</td>
							{if $vl.o_anonymity}
								<td align="left" colspan="2">
									&nbsp;{$vl.o_emoname}<i>möchte anonym bleiben</i>
								</td>
							{else}
								<td align="center" width="30">
									<a href="javascript:info('fraktionen','{$vl.race}')" class="highlightAuftableInner">
										<img src="{$vl.raceIcon}" alt="{$vl.raceShortname}" height="22"  border="0">
									</a>
								</td>
								<td align="left">
									&nbsp;{$vl.o_emoname}
									<a href="syndicate.php?action=details&detailsid={$vl.konzernid}&rid={$vl.rid}" class="linkAuftableInner">
										{$vl.syndicate} {if $vl.o_ismentor}<b>(Mentor)</b>{/if} (#{$vl.rid})
									</a>
								</td>
							{/if}
							<td align="right">{$vl.o_netto}</td>
							<td align="right">{$vl.o_won}</td>
							<td align="right">{$vl.o_diff}</td>
						</tr>
					{/foreach}
					</table>
				</td>
			</tr>
		</table>
		{/if}
	{elseif $ranktype == "aktionaere" && false}
		<strong>Die reichsten Aktionäre:</strong>
		<br>&nbsp;
    	{if $in_protection}
			<br>
			Das Ranking für Aktionäre ist erst nach der Schutzzeit verfügbar.
		{else}
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
			<tr>
				<td>
                    <table width="550" cellpadding="2" cellspacing="1" border="0">
                        <tr class="tableHead" height="23">
                            <td width="40" align="center">Rang</td>
                            <td width="260" colspan="2">Name</td>
                            <td width="80" align="right">Vermögen</td>
                        </tr>
					{foreach from=$ranking item=vl}
		 				<tr class={if $vl.syndicate == $status.syndicate}"tableInner2"{else}"tableInner1"{/if}>
                           	<td align="center">{$vl.o_count}</td>
                            <td align=center width=30>
	                           	<a href=javascript:info('fraktionen','{$vl.race}') class="highlightAuftableInner">
                               		<img src="{$vl.raceIcon}" alt="{$vl.raceShortname}" height="22"  border="0">
                               	</a>
                            </td>
                            <td align=left>
                               	&nbsp;
                             	<a href="syndicate.php?action=details&detailsid={$vl.konzernid}&rid={$vl.rid}" class="linkAuftableInner">
                               		{$vl.syndicate} (#{$vl.rid})
                               	</a>
                            </td>
                            <td align="right">{$vl.o_gesamtwert}</td>
                        </tr>
                    {/foreach}
                   	</table>
                </td>
            </tr>
        </table>
		{/if}
	{elseif $ranktype == "diebe"}
		<strong>Die erfolgreichsten Diebe:<br>
		(Erz, Energie und Forschungspunkte sind in ein Credit-Äquivalent umgerechnet)</strong>
		<br>&nbsp;
		{if $in_protection}
			<br>
			Das Ranking für Diebe ist erst nach der Schutzzeit verfügbar.
		{else}
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
			<tr>
				<td>
					<table width="550" cellpadding="2" cellspacing="1" border="0">
						<tr class="tableHead" height="23">
							<td width="40" align="center">Rang</td>
							<td width="260" colspan="2">Name</td>
							<td width="80" align="right">Netto gestohlen</td>
						</tr>
					{foreach from=$data item=vl}
						<tr class={if $vl.syndicate == $status.syndicate}"tableInner2"{else}"tableInner1"{/if}>
							<td align="center">{$vl.o_count}</td>
							{if $vl.o_anonymity}
								<td align="left" colspan="2">
									&nbsp;{$vl.o_emoname}<i>möchte anonym bleiben</i>
								</td>
							{else}
								<td align="center" width="30">
									<a href="javascript:info('fraktionen','{$vl.race}')" class="highlightAuftableInner">
										<img src="{$vl.raceIcon}" alt="{$vl.raceShortname}" height="22"  border="0">
									</a>
								</td>
								<td align="left">
									&nbsp;{$vl.o_emoname}
									<a href="syndicate.php?action=details&detailsid={$vl.konzernid}&rid={$vl.rid}" class="linkAuftableInner">
										{$vl.syndicate} {if $vl.o_ismentor}<b>(Mentor)</b>{/if} (#{$vl.rid})
									</a>
								</td>
							{/if}
							<td align="right">{$vl.o_nettostolen}</td>
						</tr>
					{/foreach}
					</table>
				</td>
			</tr>
		</table>
		{/if}	
	{else}
		{if $sortrace != "synd" and $sortrace != "allies"}
		<strong>
			{if $sortrace == "rel"}Spieler in ihrer Reichweite:
			{elseif $sortrace == "any"}Die besten Spieler:
			{else}Die besten {$sortraceShortname} Spieler:{/if}
		</strong>
		<br>&nbsp;
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
			<tr>
				<td>
					<table width="550" cellpadding="2" cellspacing="1" border="0">
						<tr class="tableHead" height="23">
							<td width="40" align="center">Rang</td>
							<td>Name</td>
							<td width="29">&nbsp;</td>
							<td width="40" align="center">Synd</td>
							<td width="90" align="right">Konzernstärke&nbsp;</td>
							<td width="60" align="right">Land&nbsp;</td>
							<td width="40" align="right">Fox&nbsp;</td>
						</tr>
					{foreach from=$nwvalues item=vl}
						<tr class={if $vl.o_name == $status.syndicate}"tableInner2"{else}"tableInner1"{/if}>
							<td align="center"><b>{$vl.o_count}</b></td>
							<td align="left">&nbsp;
								<a href="syndicate.php?action=details&detailsid={$vl.id}&rid={$vl.rid}" class="linkAuftableInner">
									{$vl.o_name}
									{if $vl.o_ismentor}<b>(Mentor)</b>{/if}
									{if $vl.o_emoname} (<i>{$vl.o_emoname}</i>){/if}
								</a>
							</td>
							<td align="center">
								<a href="javascript:info('fraktionen','{$vl.race}')" class="highlightAuftableInner">
									<img src="{$vl.raceIcon}" alt="{$vl.raceShortname}" height="22"  border="0">
								</a>
							</td>
							<td align="center">
								&nbsp;
								<a href="syndicate.php?rid={$vl.rid}" class="linkAuftableInner">
									#{$vl.rid}
								</a>
							</td>
							<td align="right">{$vl.o_nw}&nbsp;</td>
							<td align="right">{$vl.o_land}&nbsp;</td>
							<td align="right">{$vl.o_fox}&nbsp;</td>
						</tr>
					{/foreach}
					</table>
				</td>
			</tr>
		</table>
		{elseif $sortrace == "synd"} 
		<strong>Die besten Syndikate:</strong>
		<br>&nbsp;
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
			<tr>
				<td>
					<table width="550" cellpadding="2" cellspacing="1" border="0">
						<tr class="tableHead" height="23">
							<td width="30" align="center">Rang</td>
							<td width="200" align="center">Syndikat</td>
							<td align="right">Networth</td>
							<td align="right">Land</td>
						</tr>
					{foreach from=$realmvalues item=vl} 
						{if $vl.nw > 0}
						<tr class={if $vl.rid == $status.rid}"tableInner2"{else}"tableInner1"{/if}>
							<td align="center"><b>{$vl.o_count}</b></td>
							<td align="center">
								<a class="linkAuftableInner" href="syndicate.php?rid={$vl.rid}">
									{$vl.o_name} (#{$vl.rid})
								</a>
							</td>
							<td align="right">{$vl.o_nw}&nbsp;</td>
							<td align="right">{$vl.o_land}&nbsp;</td>
						</tr>
						{/if}
					{/foreach}
					</table>
				</td>
			</tr>
		</table>
		{elseif $sortrace == "allies"} 
		<table width="550" cellpadding="0" cellspacing="0" border="0" class="tableOutline"><tr><td>
				<table width="550" cellpadding="2" cellspacing="1" border="0">
				<tr class="tableHead" height=23>
					<td width=30 align=center>Rang</td>
					<td width="200" align=center>Allianz</td>
					<td align=right>Networth</td>
					<td align=right>Land</td>
				</tr>
		{foreach from=$ALLYDATA item=ally key=count}
			{if $ally.isally}
				<tr {$ally.class}>
					<td align="center" rowspan="{$ally.rowspan}"><b>{$count}{if 2 <= $ally.members_count}/{math equation="x+1" x=$count}{/if}{if 3 <= $ally.members_count}/{math equation="x+2" x=$count}{/if}</b></td>
					<td align="left">&nbsp;&nbsp;<b>{$ally.name}</b></td>
					<td align="right"><b>{$ally.nw}</b>&nbsp;</td>
					<td align="right"><b>{$ally.land}</b>&nbsp;</td>
				</tr>
			{/if}
			{foreach from=$ally.members item=member}
				<tr class="{$member.class}">{if !$ally.isally}<td align="center"><b>{$count}</b></td>{/if}<td align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="linkAuftableInner" href="syndicate.php?rid={$member.rid}">{$member.name} (#{$member.rid})</a></td><td align="right">{if $ally.isally}<i>{/if}{$member.nw}{if $ally.isally}</i>{/if}&nbsp;</td><td align="right">{if $ally.isally}<i>{/if}{$member.land}{if $ally.isally}</i>{/if}&nbsp;</td></tr>
			{/foreach}
		{/foreach}
				
		</table></td></tr></table>
		{/if}
	{/if}
	</center>
