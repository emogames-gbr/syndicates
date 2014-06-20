{if $ACTION == 'news'}
	{include file='news.tpl'}
{elseif $ACTION == 'show_news'}
	{include file='news_single.tpl'}
{elseif $ACTION == 'stats'}
	{include file='stats.tpl'}
{elseif $ACTION == 'sonstiges'}
	{include file='sonstiges.tpl'}
{elseif $ACTION == 'impressum'}
	{include file='impressum.tpl'}
{else}
	{include file='home.tpl'}
{/if}