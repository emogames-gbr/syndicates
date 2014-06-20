{strip}{if !$AJAX}<div class="content">
	<div class="c_head"></div>
	<div class="c_inner">
		<div class="circle_foot" style="z-index:1;"></div>{/if}
		<div style="z-index:2; position:relative;">
		{if $ACTION == 'show_news'}
			{include file="news_single.tpl"}
		{elseif $ACTION == 'news_archiv'}
			{include file="news_archiv.tpl"}
		{elseif $ACTION == 'stats' || $ACTION == 'hof'}
			{include file="stats.tpl"}
		{elseif $ACTION == 'infos'}
			{include file="infos.tpl"}
		{elseif $ACTION == 'nutzungsbedingungen'}
			{include file="nubs.tpl"}
		{elseif $ACTION == 'impressum'}
			{include file="impressum.tpl"}
		{elseif $ACTION == 'fb_connect'}
			{include file="fb_connect.tpl"}
		{elseif $ACTION == 'own_stats'}
			{include file="own_stats.tpl"}
		{else}
			{include file="news.tpl"}
		{/if}
		</div>{if !$AJAX}
	</div>
	<div class="c_foot"></div>
	{if $NEWS}
		{include file="news.bar.tpl"}
	{/if}
</div>{/if}
{/strip}