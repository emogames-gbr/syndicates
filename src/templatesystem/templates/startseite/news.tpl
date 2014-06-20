{strip}		{foreach from=$NEWS item=POST}
		<div class="news">
			<div class="news_bar">
				{if $POST.new}<div style="float:left; margin-top:1px; width:0px; padding-left:-40px; margin-left:-2px;"><img src="images/startseite/news_new.png" width="28px;" height="22px;" alt="new" /></div>{/if}
				<div class="text"><a href="?action=show_news&id={$POST.id}">{$POST.header}</a></div>
				<div class="date">{$POST.mytime} Uhr</div>
				<div class="box_share">
					<div class="button_share"></div>
					<div class="box_share_all">
						<div class="box_share_head"></div>
						<div class="box_share_content">
							<div><fb:like href="{$HTTP}?action=show_news&id={$POST.id}" width="120px" layout="button_count" style="margin-bottom:7px;" action="like"></fb:like></div>
							<br />
							<div><a href="http://twitter.com/share" data-url="{$HTTP}?action=show_news&id={$POST.id}" class="twitter-share-button" data-text="'{$POST.header}' @ TWITTER-USER-ACC" data-count="horizontal" data-via="BETREIBER" data-lang="de"></a></div>
							<br />
							<div><g:plusone size="medium" href="{$HTTP}?action=show_news&id={$POST.id}"></g:plusone></div>
						</div>
						<div class="box_share_foot"></div>
					</div>
				</div>
			</div>
			<div class="news_content">
				<div class="n_head"></div>
				<div class="n_inner news_post" id="{$POST.id}_inner">
					{$POST.text}<br />
				</div>
				<div class="n_inner" style="text-align:right; padding-right:25px; padding-top:5px; display:none;" id="{$POST.id}_tick"></div>
				<div class="n_foot"></div>
				{if $POST.hr}<div class="n_hr"></div>{/if}
			</div>
		</div>
		{/foreach}
{/strip}