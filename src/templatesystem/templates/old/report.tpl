{if $gmsBenachrichtigt1 || $gmsBenachrichtigt2 || $gmsBenachrichtigt3}
	<br>
	Die Game-Master wurden benachrichtigt.<br>
	<br>
    Aktion: <strong>{$was}</strong><br>
    {if $gmsBenachrichtigt1}
    	Gemeldeter Spieler: <strong>{$gemeldet.syndicate} (#{$gemeldet.rid})</strong><br>
    {/if}
    <br>
    Bitte denken Sie daran, dass der Mißbrauch der Meldeoption ebenfalls als Verstoß gegen die Nutzungsbedingungen
    interpretiert und entsprechend geahndet werden kann.
{elseif $coderBenachrichtigt}
	<br>Die Coder wurden benachrichtigt.<br>
	<br>
	Aktion: <strong>{$was}</strong><br>
	<br>
	Bitte denken Sie daran, dass der Mißbrauch der Meldeoption als Verstoß gegen die Nutzungsbedingungen 
	interpretiert und entsprechend geahndet werden kann.
{elseif $verstos == "partner" && $final}
	<br>Sie werden in Kürze durch die Gamemaster informiert, ob ihre Eintragung als Zusammenspieler erfolgreich war.<br>
	<br>
	Aktion: <strong>{$was}</strong><br>
	<br>
	Bitte denken Sie daran, dass der Mißbrauch der Supportoption als Verstoß gegen die Nutzungsbedingungen
	interpretiert und entsprechend geahndet werden kann.
{elseif !$verstos}
	<br>
	
	{if $IS_OSTERN && $OSTER_BONI.25012}
		<div style="float:right; padding:10px; text-align:center;">
			<div style="width:100%; text-align:center;">
				<a class="normal" href="bonus.php?type=4&amp;egg=25012">
					<img src="images/ostern_25012.png"></a>
			</div>
				Nichts freut einen Gamemaster<br>
				mehr als eine freundlich <br>
				geschriebene Nachricht.
		</div>	
	{/if}
	
    <table align=center class=tableOutline cellspacing=1 cellpadding=3>
        <tr>
            <td class=tableHead>Support-Ticket eröffnen</td>
        </tr>
        <tr class=tableInner1>
            <td>
                <form action=report.php method=post>
                    <select name=verstos>
						<option value=bug>Bug melden
						<option value=partner>Zusammenspieler eintragen
                        <option value=konzernbild>Konzernbild melden
                        <option value=konzernname>Konzernname melden
                        <option value=konzernbeschreibung>Konzernbeschreibung melden
                        <option value=syndikatsbild>Syndikatsbild melden
                        <option value=syndikatswebseite>Syndikatswebseite melden
                        <option value=multi>Multi reporten
                        <option value=sonstiges>Sonstigen Verstoss melden
                    </select>&nbsp;&nbsp;
                    <input type=submit value=weiter>
                </form>
            </td>
        </tr>
        <tr>
         	<td class=tableInner1>
           		Fragen zum Spiel stellen Sie bitte hier: 
           		<a target="_blank" class="gelblink" href="fragen_und_antworten_board.php">Q&A Board</a>
           	</td>
        </tr>
		<tr>
           	<td class=tableInner1>
           		Staffmitglieder anschreiben: 
           		<a target="_blank\" class="gelblink" href="http://board.emogames.de/team.php">Teamliste</a>
           	</td>
        </tr>
		<tr>
           	<td class=tableInner1>
           		Unser IRC-Channel (QuakeNet): #syndicates
           	</td>
        </tr>
	</table>
	<br><br>	
	<table border="0" cellspacing="1" cellpadding="2" width="600" align="center" class="tableOutline">
		<tr class="tableHead">
			<td align="center" width="200px">Meine Supports</td>
			<td align="center" width="100px">Geöffnet</td>
			<td align="center" width="100px">Geschlossen</td>
			<td align="center" width="200px">Gamemaster</td>
		</tr>
		{foreach from=$mysup item=sup}
		<tr class="tableInner1">
			<td align="center">{$sup.title}</td>
			<td align="center">{$sup.o_starttime}</td>
			<td align="center">{$sup.o_endtime}</td>
			<td align="center">{$sup.o_gmname}</td>
		</tr>
		{/foreach}
	</table>
{elseif ($verstos == "konzernbild" || $verstos == "konzernname" || $verstos == "konzernbeschreibung" || $verstos == "sonstiges") && !$final}
    <br>
    <br>
    <table width=60% class=tableOutline align=center cellspacing=1 cellpadding=3>
        <tr align=center class=tableHead>
            <td>{$was}</td>
        </tr>
        <tr class=tableInner1>
            <td>
                <form action=report.php method=post>
                <input type=hidden name=verstos value={$verstos}>
                    <table cellspacing=0 cellpadding=3>
                        <tr>
                            <td width=150 align=left>Syndikat auswählen:</td>
                            <td width=150 align=center>
                                (#<input size="2" type=text name=rid value={$rid}>)
                                <input type=submit name=ridchange value=Wählen>
                            </td>
                        </tr>
                        <tr>
                            <td align=left>Spieler auswählen: </td>
                            <td align=center>
                                {if $players}
	                                <select name=meldeid>
	                                {foreach from=$players item=value}
	                                    <option value="{$value.id}">{$value.syndicate}
									{/foreach}
                                    </select>
                                {else}
                                    Keine Spieler in diesem Syndikat gefunden
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <td>Grund:</td>
                            <td><textarea name=grund rows=3 cols=20></textarea></td>
                        </tr>
                        <tr>
                            <td colspan=2 align=center><br>
                                <input type=submit name=final value="Spieler melden">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
{elseif ($verstos == "syndikatsbild" || $verstos == "syndikatswebseite") && !$final}
    <br><br>
	<table width=60% class=tableOutline align=center cellspacing=1 cellpadding=3>
		<tr align=center class=tableHead>
			<td>{$was}</td>
		</tr>
		<tr class=tableInner1>
            <td>
                <form action=report.php method=post>
                    <input type=hidden name=verstos value={$verstos}>
                    <table cellspacing=0 cellpadding=3>
                        <tr>
                            <td width=150 align=left>Syndikat auswählen:</td>
                            <td width=150 align=center>
                                (#<input size="2" type=text name=rid value={$rid}>)
                                <input type=submit name=ridchange value=Wählen>
                            </td>
                        </tr>
                        <tr>
                        {if $verstos === "syndikatsbild"}
                        	<td colspan="2" align="center">
                            	<img src="syndikatsimages/syndikat_{$rid}.{$img}" border="0">
                            </td>
                        {elseif $verstos ==="syndikatswebseite"}
                        	<td align="left">Syndikatswebseite:</td>
                            <td align="center">
                            	<a target="_blank" href="{$syndlink}">{$syndlink}</a>
                            </td>
						{/if}
                        </tr>
                        <tr>
                            <td>Grund:</td>
                            <td><textarea name=grund rows=3 cols=20></textarea></td>
                        </tr>
                        <tr>
                            <td colspan=2 align=center><br>
                                <input type=submit name=final value="{$was}">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
{elseif ($verstos == "multi" || $verstos == "bug" || $verstos == "partner") && !$final}
	<br><br>
    <table width=90% class=tableOutline align=center cellspacing=1 cellpadding=3>
    	<tr align=center class=tableHead>
    		<td>{$was}</td>
    	</tr>
    	<tr class=tableInner1>
        	<td>
            	<form action=report.php method=post>
                	<input type=hidden name=verstos value={$verstos}>
                	<table class="tableInner1" cellspacing=0 cellpadding=3>
                    	<tr>
                        	<td  width="100%" align="left">
                        		{if $verstos === "multi"}
                        		Bitte nennen Sie die verdächtigen Konzerne (Name und Syndikatsnummer) und erläutern Sie genau,
                        		weshalb Sie der Ansicht sind, dass es sich um einen Spieler mit mehreren Accounts handelt.
                        		{elseif $verstos === "bug"}
                        		Bitte nennen Sie den/die Konzerne (Name und Syndikatsnummer), welche vom Bug
                            	betroffen sind und erläutern Sie genau, weshalb Sie der Ansicht sind, dass es
                            	sich um einen Bug handelt.
                        		{elseif $verstos === "partner"}
                        		Bitte nennen Sie die Konzerne (Name und Syndikatsnummer), welche Sie als Zusammenspieler eintragen
                        		lassen möchten und erläutern Sie genau, weshalb diese als Zusammenspieler gelten.
                        		{/if}
                        		<br>
                        		<br>
                        	</td>
                    	</tr>
                        <tr>
                            <td>
                            	Beteiligte Konzerne:<br>
                            	<br>
								{if $multiexists}
									{foreach from=$multiarray item=temp}
										{$temp.syndicate} (#{$temp.rid})<br>
										<input type="hidden" name=multiarray[] value="{$temp.o_key}">
									{/foreach}
								{/if}
								<br>
								<br>
								Konzern auswählen:
								<select name=multiid>
								{if $gotone}
									{foreach from=$konzerne item=value}
										<option value="{$value.id}">{$value.syndicate} (#{$value.rid})
									{/foreach}
								{else}
									<option value="0">Keine Spieler gefunden
								{/if}
								</select>
								&nbsp;Syndikat: <input size=3 name=selectrid value="{$selectrid}">
								<input type=submit name=selsynd value="Syndikat wählen">
								<br>
								<br>
								<input type=submit name=selectplayer value="Konzern hinzufügen">
							</td>
                        </tr>
                        <tr>
                            <td>
                            	<br>
                            	<br>
                            	Begründung:<br>
                            	<textarea {if $multi_disabled} disabled {/if} name="grund" rows="10" cols="60" align="center">{if $multi_disabled}{if 
                            	$verstos === "multi"}
                        		Sie müssen mindestens zwei betroffene Konzerne auswählen!{elseif 
                        		$verstos === "bug"}
                        		Sie müssen mindestens einen betroffene Konzerne auswählen! Wenn kein Konzern betroffen ist, geben Sie bitte ihren eigenen Konzern an.{elseif 
                            	$verstos === "partner"}
                        		Sie müssen mindestens zwei beteilligte Konzerne auswählen!{/if}{elseif
                        		verstos === "bug"}Wann ist der Bug aufgetreten?
[möglichst genauer Zeitpunkt]

Lässt sich der Fehler reproduzieren?
[Ja -> Wie? / Nein]

Welchen Browser verwendest du?
[Typ, Versionsnummer, Sprachversion]

Fehlerbeschreibung:
[Bitte möglichst sachlich]{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td align=center><br>
                                <input type=submit name=final value="{$was}">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
{/if}