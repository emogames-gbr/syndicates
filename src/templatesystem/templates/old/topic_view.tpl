		<br />
		<table width="598" border="0" cellpadding="4" cellspacing="1">
			<tr>
				<td align="left" width="50%" class="siteGround">
					<strong><a href="{$SITE}.php" class="linkAufsiteBg">Themenübersicht</a></strong>
				</td>
				<td align="right" width="50%" class="siteGround">
					<strong><a href="{$SITE}.php?action=reply&tid={$TID}" class="linkAufsiteBg">Antworten</a></strong>
				</td>
			</tr>
			<tr>
				<td align="right" class="siteGround" colspan="2">
				{if $NUM_PAGES > 1}
					Seite ({section name=PAGES start=1 loop=$NUM_PAGES+1 step=1}{if $NUM_PAGES > 1 && $smarty.section.PAGES.first && $PAGE != $smarty.section.PAGES.index}<a href="{$SITE}.php?action=view&tid={$TID}&seite={$PAGE-1}" class="linkAufsiteBg">Vorherige</a> {/if}{if $smarty.section.PAGES.index > 1}, {/if}{if $PAGE == $smarty.section.PAGES.index}<b><font class="siteGround">{$smarty.section.PAGES.index}</font></b>{else}<a href="{$SITE}.php?action=view&tid={$TID}&seite={$smarty.section.PAGES.index}" class="linkAufsiteBg">{$smarty.section.PAGES.index}</a>{if $NUM_PAGES > 1 && $smarty.section.PAGES.last} <a href="{$SITE}.php?action=view&tid={$TID}&seite={$PAGE+1}" class="linkAufsiteBg">Nächste</a>{/if}{/if}{/section})
				{/if}
				</td>
			</tr>
		</table>
		<table width="598" border="0" cellpadding="0" cellspacing="0" class="tableOutline">
			<tr>
				<td>
					<table width="598" border="0" cellpadding="4" cellspacing="1">
						<tr>
							<td colspan="2" align="left" class="tableHead">
								<strong>{$TOPIC_TITLE}</strong>
							</td>
						</tr>
						{foreach name=all_postings item=POST from=$POSTS}
							{cycle values="tableInner1,tableInner2" assign="POST_CLASS"}
							<tr>
								<td width="120px" align="left" valign="top" class="{$POST_CLASS}" nowrap>
									<a name="{$POST.mid}"></a>
									{if $POST.newest}<a name="newest"></a>{/if}
									{if $POST.last}<a name="last"></a>{/if}
									<strong><a class="linkMenue" href="syndicate.php?action=details&detailsid={$POST.poster_id}">{$POST.name_of_poster}</a></strong><br />
									von<br />
									<strong><a class="linkMenue" href="syndicate.php?action=details&detailsid={$POST.poster_id}">{$POST.name_of_konz}</a>{if $SITE != 'syndboard' && $SITE != 'gruppenboard'} (<a class="linkMenue" href="syndicate.php?rid={$POST.poster_rid}">#{$POST.poster_rid}</a>){/if}
									<br />{if $POST.emonick}<i>({$POST.emonick})</i></strong>{/if}
									{if $POST.path_to_pic}
										<br />
										<br />
										<table border="0" cellpadding="0" cellspacing="1" class="tableOutline" align="center">
											<tr>
												<td class="tableInner1" align="center" valign="middle">
													<img src="{$POST.path_to_pic}" border="0">
												</td>
											</tr>
										</table>
									{/if}
									{if $POST.mentor}
									<br />
									<br />
									<i style="font-size: larger; font-weight: bold; display: block; margin: 0pt auto; text-align: center;">Mentor</i>
									{/if}
									<br />
									<table class="{$POST_CLASS}" align="center" border="0">
										<tr>
											<td rowspan="2" align="left"><img valign="middle" src="{$GP_PATH}{$POST.poster_race}-logo-klein.gif">&nbsp;</td>
											<td><img src="{$GP_PATH}networth.gif" border="0" align="absmiddle"></td>
											<td align="right">{$POST.poster_nw|number_format}</td>
										</tr>
										<tr>
											<td><img src="{$GP_PATH}land.gif" border="0" align="absmiddle"></td>
											<td align="right">{$POST.poster_land|number_format}</td>
										</tr>
										{if !$POST.own}
										<tr>
											<td colspan="3">
												<a href="mitteilungen.php?action=psm&rec={$POST.poster_id}"><img src="{$GP_PATH}_syn_message_letter.gif" border="0" align="absmiddle"></a>
												{if $SITE == 'syndboard'}<a href="pod.php?pre_id={$POST.poster_id}#t"><img src="{$GP_PATH}_syn_transfer.gif" border="0" align="absmiddle" alt="Transfer an diesen Spieler"></a>{/if}
											</td>
										</tr>
										{/if}
									</table>
								</td>
								<td class="{$POST_CLASS}" valign="top">{$POST.bbcode}</td>
							</tr>
							<tr>
								<td class="{$POST_CLASS}" width="120px" valign="top" nowrap="nowrap">
									<a href="{$SITE}.php?action=view&tid={$TID}&seite={$PAGE}#{$POST.mid}">
									{if $POST.new}
										<img src="{$GP_PATH}posticonnew.gif" border="0" align="absmiddle">
									{else}
										<img src="{$GP_PATH}posticon.gif" border="0" align="absmiddle">
									{/if}
									</a>
									{$POST.date} 
								</td>
								<td class="{$POST_CLASS}" align="right">
									<a href="{$SITE}.php?action=reply&tid={$TID}&mid={$POST.mid}" class="linkAuftableInner">Zitieren</a>
									&nbsp;-&nbsp;
									<a href="{$SITE}.php?action=edit&mid={$POST.mid}" class="linkAuftableInner">Editieren</a>&nbsp;
								</td>
							</tr>
						{/foreach}
					</table>
				</td>
			</tr>
		</table>
		<table width="598" border="0" cellpadding="4" cellspacing="1">
			<tr>
				<td align="right" class="siteGround" colspan="2">
				{if $NUM_PAGES > 1}
					Seite ({section name=PAGES start=1 loop=$NUM_PAGES+1 step=1}{if $NUM_PAGES > 1 && $smarty.section.PAGES.first && $PAGE != $smarty.section.PAGES.index}<a href="{$SITE}.php?action=view&tid={$TID}&seite={$PAGE-1}" class="linkAufsiteBg">Vorherige</a> {/if}{if $smarty.section.PAGES.index > 1}, {/if}{if $PAGE == $smarty.section.PAGES.index}<b><font class="siteGround">{$smarty.section.PAGES.index}</font></b>{else}<a href="{$SITE}.php?action=view&tid={$TID}&seite={$smarty.section.PAGES.index}" class="linkAufsiteBg">{$smarty.section.PAGES.index}</a>{if $NUM_PAGES > 1 && $smarty.section.PAGES.last} <a href="{$SITE}.php?action=view&tid={$TID}&seite={$PAGE+1}" class="linkAufsiteBg">Nächste</a>{/if}{/if}{/section})
				{/if}
				</td>
			</tr>
			<tr>
				<td align="left" width="50%" class="siteGround">
					<strong><a href="{$SITE}.php" class="linkAufsiteBg">Themenübersicht</a></strong>
				</td>
				<td align="right" width="50%" class="siteGround">
					<strong><a href="{$SITE}.php?action=reply&tid={$TID}" class="linkAufsiteBg">Antworten</a></strong>
				</td>
			</tr>
			{if $IS_PRESIDENT}
			<tr>
				<td align="right" colspan="2">
					<a href="{$SITE}.php?action=sticky&tid={$TID}" class="linkAufsiteBg">Oben {if $STICKY}lösen{else}festhalten{/if}</a>
				</td>
			</tr>
			{/if}
		</table>
