{strip}		{if !$AJAX}<div class="bar_clean">
			<a href="?action=stats" class="bar_1 {if $ACTION == 'stats'}bar_1_hover bar_12_l_hover{else}bar_12_l{/if}">
				Aktuelles der laufenden Runde
			</a>
			<a href="?action=hof" class="bar_1 {if $ACTION == 'hof'}bar_1_hover bar_12_r_hover{else}bar_12_r{/if}">
				Hall of Fame
			</a>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner" id="content">
				<div id="content1">{/if}
					{if $ACTION == 'hof'}
						{include file="stats.hof.tpl"}
					{else}
						{include file="stats.stats.tpl"}
					{/if}{if !$AJAX}
				</div>
				<div id="content2" style="display:none;"></div>
			</div>
			<div class="l_foot"></div>
		</div>{/if}
{/strip}