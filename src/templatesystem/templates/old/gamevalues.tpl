<br>
<br>
<table align="center" width="94%" class="tableOutline" cellpadding="0" cellspacing="1">
	<tr>
		<td>
			<table align="center" width="100%" class="tableInner1" cellpadding="4" cellspacing="0">
				<tr class="tablehead">
					<td align="center" colspan="3">Allgemeiner Spielstatus</td>
				</tr>
				<tr class="tablehead2">
					<td>Name des Spielwerts</td>
					<td>aktueller Wert</td>
					<td>Maximaler Wert</td>
				</tr>
				<tr>
					<td>Tage seit Rundenbeginn</td>
					<td>{$DAYSPLAYED}</td>
					<td>{$MAX_DAYS_ROUNDLENGTH}</td>
				</tr>
				<tr>
					<td>Runden<b>start</b></td>
					<td>{$ROUNDSTARTTIME} Uhr</td>
					<td>-</td>
				</tr>
				<tr>
					<td>Runden<b>ende</b></td>
					<td>{$ROUNDENDTIME} Uhr</td>
					<td>-</td>
				</tr>
				<tr>
					<td>Tage bis Rundenende</td>
					<td>{$DAYSTOROUNDEND}</td>
					<td>{$MAX_DAYS_ROUNDLENGTH}</td>
				</tr>
                <tr>
					<td>Spieler online</td>
					<td>{$PLAYER_ONLINE}</td>
					<td></td>
				</tr>
				<tr class="tablehead">
					<td align="center" colspan="3">Zeitlich veränderliche Spielwerte</td>
				</tr>
				<tr class="tablehead2">
					<td>Name des Spielwerts</td>
					<td>aktueller Wert</td>
					<td>Maximaler Wert</td>
				</tr>
				<tr>
					<td>
						<b>Größtmöglicher Wert</b> der Maximalverschuldung pro Land im Lager
						(diese wird vom Präsidenten eingestellt)
					</td>
					<td>{$MAXSCHULDENAKTUELL}</td>
					<td>{$MAXSCHULDENINSGESAMMT}</td>
				</tr>
				<tr>
					<td>Kriegsprämienfaktor</td>
					<td>{$KRIEGSPRAEMIENFAKTOR}</td>
					<td>{$MAXKRIEGSPRAEMIENFAKTOR}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>
<br>
<table align="center" cellspacing="1" cellpadding="5" border="0" class="tableOutline" width="94%">
	<tr>
  		<td class="tableHead">Sabotage/Tragekapazität</td>
 	</tr>
 	<tr>
  		<td class="tableInner1">
  			<table cellspacing="1" cellpadding="3" border="0" width="100%" class="tableInner1">
  				<tr class="tableHead2">
  					<td>Sabotageaktion</td>
  					<td>Erfolg pro Thief</td>
  					<td>pro Guardian</td>
  					<td>pro Agent</td>
  					<td>Max. Erfolg</td>
  					<td>Max. Erfolg (Krieg)</td>
  				</tr>		
			{foreach from=$STEAL item=TEMP}
				<tr>
					<td>{$TEMP.name}</td>
					<td>{$TEMP.offspies}</td>
					<td>{$TEMP.defspies}</td>
					<td>{$TEMP.intelspies}</td>
					<td>
						{$TEMP.maxget}%
						{if $TEMP.key == "killbuildings" or $TEMP.key == "killunits"}{/if}
					</td>
					<td>
						{$TEMP.maxget_atwar}%
						{if $TEMP.key == "killbuildings" or $TEMP.key == "killunits"}{/if}
					</td>
				</tr>
			{/foreach}
   			</table>
   		</td>
   	</tr>
</table>
<br>
<br>
<table align="center" cellspacing="1" cellpadding="5" border="0" class="tableOutline" width="50%">
	<tr>
  		<td class="tableHead">
  			Durchschnittsfox 
  			<a href="http://www.syndicates-wiki.de/index.php?title=Fox" class="highlightAuftableInner" target="_blank">
  				<img src="{$RIPF}_help.gif" border="0" valign="absmiddle">
  			</a>
  		</td>
 	</tr>
 	<tr>
  		<td class="tableInner1">
  			<table cellspacing="1" cellpadding="3" border="0" width="100%" class="tableInner1">
  				<tr class="tableHead2">
  					<td>Fraktion</td>
  					<td align="right">Fox</td>
  				</tr>
			{foreach from=$FOX item=RACE}
				<tr>
					<td>{$RACE.name}</td>
					<td align="right">{$RACE.fox}</td>
				</tr>
			{/foreach}
   			</table>
   		</td>
   	</tr>
</table>
<br>
<br>
<table align="center" cellspacing="1" cellpadding="5" border="0" class="tableOutline" width="94%">
	<tr>
  		<td class="tableHead" colspan="2">Networth</td>
 	</tr>
 	<tr valign="top">
  		<td class="tableInner1" width="50%">
  			<table cellspacing="1" cellpadding="3" border="0" width="100%" class="tableInner1">
  				<tr class="tableHead2">
  					<td width="80%">Gebäude</td>
  					<td width="20%">Networth</td>
				</tr>	
			{foreach from=$BUILDINGS item=B}
				<tr>
					<td>{$B.name}</td>
					<td align="right">{$B.o_nw}</td>
				</tr>	
			{/foreach}
				<tr class="tableHead2">
  					<td>Forschungen</td>
  					<td>Networth</td>
				</tr>
			{foreach from=$FOS item=F}
				<tr>
					<td>Stufe {$F.stufe}</td>
					<td align="right">{$F.o_nw}</td>
				</tr>
			{/foreach}
			</table>
		</td>
		<td class="tableInner1" width="50%">
			<table cellspacing="1" cellpadding="3" border="0" width="100%" height="100%" class="tableInner1">
				<tr class="tableHead2">
  					<td width="80%">Militäreinheit</td>
  					<td width="20%">Networth</td>
			{foreach from=$UNITS item=U}
				<tr>
					<td>{$U.name}</td>
					<td align="right">{$U.o_nw}</td>
				</tr>	
			{/foreach}
				<tr class="tableHead2">
  					<td>Spionageeinheit</td>
  					<td>Networth</td>
  				</tr>
			{foreach from=$SPIES item=S}
				<tr>
					<td>{$S.name}</td>
					<td align="right">{$S.nw}</td>
				</tr>	
			{/foreach}
				<tr class="tableHead2">
  					<td>sonstiges</td>
  					<td>Networth</td>
				</tr>
				<tr>
  					<td>Land</td>
  					<td align="right">{$NW_LAND}</td>
				</tr>
				{*<tr>
	  				<td>je Aktie</td>
  					<td align="right">{$NW_AKTIEN}</td>
				</tr>*}
				<tr>
					<td colspan="2">
					{if $IS_OSTERN && $OSTER_BONI.18439}
						<div style="position:relative; z-index:2; top:0px; left:100;">
							<a class="normal" href="bonus.php?type=4&amp;egg=18439">
								<img src="images/ostern_18439.png"></a>
						</div>
					{/if}
					</td>
				</tr>
   			</table>
   		</td>
   	</tr>
</table>
<br>

