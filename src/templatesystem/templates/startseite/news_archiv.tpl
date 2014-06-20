{strip}		<div class="bar">
			<div class="text">Newsarchiv</div>
		</div>
		<div class="clean_content">
			<div class="l_head"></div>
			<div class="l_inner">
				<table width="100%">
					<thead>
						<tr>
							<td class="right">Titel</td>
							<td align="center" class="right" width="170px">Autor</td>
							<td align="center" width="140px">Datum</td>
						</tr>
					</thead>
					<tbody>
					{foreach from=$ALL_NEWS item=POST}
						<tr>
							<td class="right bottom"><a href="?action=show_news&id={$POST.id}">{$POST.header}</a></td>
							<td class="right bottom" align="center">{$POST.poster}</td>
							<td class="bottom" align="center">{$POST.mytime} Uhr</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="l_foot"></div>
		</div>
{/strip}