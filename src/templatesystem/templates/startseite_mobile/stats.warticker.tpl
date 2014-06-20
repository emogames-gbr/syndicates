<h3>Warticker:</h3>
<ul data-role="listview" data-inset="true">
	{assign var='NOTHING' value=false}
	{foreach from=$WARTICKER item=DAY}
		{if $DAY.data}{assign var='NOTHING' value=true}
			<li data-role="list-divider">{$DAY.name}</li>
			{foreach from=$DAY.data item=DATA}
				<li style="font-weight:normal; font-size:12px; padding: .7em 15px .7em 15px;">
					<p class="ui-li-aside"><strong>{$DATA.time|date_format:"%H:%M"} Uhr</strong></p>
					{if $DATA.status == 'start'}
						{if $DATA.a_allyname}Die Allianz <em>"{$DATA.a_allyname}"{else}Das Syndikat <em>"{$DATA.a_synname}{/if}" ({foreach from=$DATA.a_rids item=A name=ars}{if !$smarty.foreach.ars.first}, {/if}#{$A}{/foreach})</em>
						 erklärt
						 {if $DATA.e_allyname}der Allianz <em>"{$DATA.e_allyname}"{else}dem Syndikat <em>"{$DATA.e_synname}{/if}" ({foreach from=$DATA.e_rids item=E name=ers}{if !$smarty.foreach.ers.first}, {/if}#{$E}{/foreach})</em>
						 den Krieg.
					{else}
						Der Krieg zwischen
						{if $DATA.a_allyname}der Allianz <em>"{$DATA.a_allyname}"{else}dem Syndikat <em>"{$DATA.a_synname}{/if}" ({foreach from=$DATA.a_rids item=A name=ars}{if !$smarty.foreach.ars.first}, {/if}#{$A}{/foreach})</em>
						und
						{if $DATA.e_allyname}der Allianz <em>"{$DATA.e_allyname}"{else}dem Syndikat <em>"{$DATA.e_synname}{/if}" ({foreach from=$DATA.e_rids item=E name=ers}{if !$smarty.foreach.ers.first}, {/if}#{$E}{/foreach})</em>
						ist beendet.
						{if $DATA.won == 'a'}Der Angreifer konnte den Krieg für sich entscheiden.{elseif $DATA.won == 'e'}Der Verteidiger konnte die Niederlage abwehren.{else}Der Krieg wurde durch die Spielleitung beendet.{/if}
					{/if}
				</li>
			{/foreach}
		{/if}
	{/foreach}
</ul>
{if !$NOTHING}
	Es wurde in den letzten 2 Tagen kein Krieg gestartet oder beendet
{/if}