{if $INIT}
	{assign var='INIT' value=false}
	<h3>Bestenliste:</h3>
	{foreach from=$TOP item=OUT name="rank"}
		{include file="stats.best.tpl" LIST=$OUT}
	{/foreach}
{else}
	{if $LIST}
		<ul data-role="listview" data-inset="true">
			<li data-role="list-divider">Top 3 {$LIST.name}</li>
			{foreach from=$LIST.data item="DATA" name="list"}
				<li>
					<h3>{$DATA.name} (#{$DATA.rid})</h3>
					<p>{$DATA.value|number_format} {$LIST.type}</p>
				</li>
			{foreachelse}
				<li>keine Daten vorhanden</li>
			{/foreach}
		</ul>
	{/if}
{/if}