<html>
	<head>
		<title>Typenbeschreibung zu "{$name}"</title>
		<link rel="stylesheet" type="text/css" href="{$layout.images}style.css">
	</head>
	<body background="{$layout.images}bg.gif">
	<table width=95% class=siteGround cellspacing=1 cellpadding=4>
		<tr>
			<td align=center class=tableHead>
				<u>
					<b>{$name}</b>
				</u>
				<br><br>
	
	{if $type == "fraktionen"}
	<table class="rand">
		<tr>
			<td class="menueHead">
				Fraktion: 
			</td>
		</tr>
		<tr>
			<td>
				<table class="body">
					<tr>
						{$desc}
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="menueHead">
					Militäreinheiten:
			</td>
		</tr>
		{foreach from=$units item=temp1}
		<tr>
			<td class="body">
				<b>{$temp1.name}</b><br>
				AP: {$temp1.op}<br>
				VP: {$temp1.dp}<br>
				Special: {$temp1.specials}
			</td>
		</tr>
		{/foreach}
		<tr>
			<td class="menueHead">
				Spionageeinheiten:
			</td>
		</tr>
		{foreach from=$spyunits item=temp2}
		<tr>
			<td class="body">
				<b>{$temp2.name}</b><br>
				AP: {$temp2.op}<br>
				VP: {$temp2.dp}<br>
				IP: {$temp2.ip}
			</td>
		</tr>
		{/foreach}
	</table>
	{elseif $type == "buildings"}
	<img style="border:1px solid black" src="{$layout.images}{$informations.building_id}.jpg"><br>
	<br>	
	{$informations.description}<br>
	<br>
	<strong>Stromverbrauch:</strong>
	<i>{$informations.verbrauch} ({$informations.intverbrauch})</i>
	{elseif $type == "market_gebote"}
	Sie können am Markt Kaufgebote für gehandelte Produkte abgeben.
	<ul>
		<li>Sobald das gewünschte Produkt zu einem Preis gehandelt wird, der höchstens so hoch ist, wie der von Ihnen im Kaufgebot angegebene,
				wird dieses Produkt automatisch von Ihnen erworben.</li>
		<li>Beachten Sie bitte, dass Sie immer den von Ihnen angegebenen Preis für das Produkt bezahlen, auch wenn es günstiger angeboten werden sollte.</li>
		<li>Falls mehrere Spieler Angebote für ein Produkt abgegeben haben, wird dieses an den Spieler mit dem höchsten Gebot verkauft.</li>
	</ul>
	{elseif $type == "monumente"}
		{if $monu_check == "beteiligung"}
		Wenn Ihr Syndikat mit dem Bau eines Monumentes beschäftigt ist, werden jedem Spieler des Syndikats pro Zug
		<i>{$BUCHUNGSBETRA_TICK} FP</i> abgezogen und in den Bau des Monuments investiert.
		Wenn Sie sich nicht an dem Bau eines Monumentes beteiligen möchten, wählen Sie bitte hier die Einstellung
		Nein aus! Die Standardeinstellung sieht eine Beteiligung am Bau von Monumenten vor.
		{/if}
	{elseif $type == "hilfe"}
		{$hilfe_text}
	{/if}
				</td>
			</tr>
		</table>
		<br>
		<input type=button onClick=window.close() value="Fenster schließen">
		<br>
	</body>
</html>