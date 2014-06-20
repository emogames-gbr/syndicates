{strip}				{if $GLOBALS.roundstatus != 0}
				{if $GLOBALS.roundstatus == 1}
				{include file="stats.warticker.tpl"}
				<br />
				{/if}
				{include file="stats.best.tpl"}
				{else}
				Die Runde wurde noch nich gestartet
				{/if}
{/strip}