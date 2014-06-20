{include file='js/quests.js.tpl'}
<center>
	<table width=100% cellpadding="5" cellspacing="0">
		<tr>
			<td class="i" width="150">
				<img src="http://{$GENERAL_URL}" width="140">
			</td>
			<td class="i" valign="top">
				<strong>{$CURRENT_QUEST}</strong><br />
				{$CURRENT_QUEST_TEXT}
			</td>
		</tr>
	</table>
</center>

<table class="tableOutline" cellspacing="1" cellpadding="0" width="600">
	{foreach from=$LEVELS item=LEVEL}
	<tr class="tableInner1">
		<td align="center" id="toggle_stufe_{$LEVEL.id}">
			<div class="tableHead"><span id="toggle_stufe_{$LEVEL.id}_symbol">[+]</span>Stufe {$LEVEL.id}: {$LEVEL.name}</div>
			<div type="stufe_{$LEVEL.id}_toggletext" style="display: none">
				<table cellspacing="0" cellpadding="2" width="100%" border="1px">
					<tr class="tableHead2">
						<td width="10%">#</td>
						<td width="20%">Name</td>
						<td width="50%">Beschreibung</td>
						<td width="10%">Erfolgreich?</td>
						<td width="10%">Belohnung</td>
					</tr>
					<tr>
						<td>text</td>
						<td>more text</td>
						<td>even more text</td>
						<td>ne du</td>
						<td>nix</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	{/foreach}
</table>
