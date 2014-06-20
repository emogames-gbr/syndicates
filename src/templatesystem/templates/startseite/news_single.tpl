{strip}		<div class="news">
			<div class="news_bar">
				<div class="text">{$POST.header}</div>
				<div class="date">{$POST.mytime} Uhr</div>
				<div class="box_share">
					<div class="button_share"></div>
					<div class="box_share_all">
						<div class="box_share_head"></div>
						<div class="box_share_content">
							<div><fb:like href="{$HTTP}?action=show_news&id={$POST.id}" width="120px" layout="button_count" style="margin-bottom:7px;" action="like"></fb:like></div>
							<br />
							<div><a href="http://twitter.com/share" data-url="{$HTTP}?action=show_news&id={$POST.id}" class="twitter-share-button" data-text="'{$POST.header}' @ TWITTERACC" data-count="horizontal" data-via="BETREIBER" data-lang="de"></a></div>
							<br />
							<div><g:plusone size="medium" href="{$HTTP}?action=show_news&id={$POST.id}"></g:plusone></div>
						</div>
						<div class="box_share_foot"></div>
					</div>
				</div>
			</div>
			<div class="news_content">
				<div class="n_head"></div>
				<div class="n_inner" id="{$POST.id}_inner">
					<span style="font-style:italic">{$POST.poster}</span> schrieb:
					<hr width="20%" align="left" />
					{$POST.text}<br />
				</div>
				<div class="n_foot"></div>
			</div>
			<br />
			<div style="text-align:center"><a href="?{if $POST.action}action={$POST.action}{/if}">Zurück</a></div>
		</div>
{/strip}