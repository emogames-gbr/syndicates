{if !$AJAX}<p>
	<ul data-role="listview" id="news_list">
	{/if}{foreach from=$NEWS item=POST}
		<li><a href="?action=show_news&id={$POST.id}" data-transition="pop">
			<h3>{$POST.header}</h3>
			<p>{$POST.mytime} Uhr</p>
		</a></li>
	{/foreach}{if !$AJAX}
	</ul><!--
	<br />
	<div style="text-align:center;"><button data-inline="true" id="more_news" data-theme="b">mehr</button></div>-->
</p>{/if}