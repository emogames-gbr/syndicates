			<br>
		{if $SITE == "syndboard"}
			{if $ALLYBOARD == 1} <a href="allianzboard.php" class="hrAufSiteBg">Zum Allianz Board</a><br>
			<br>
			{/if}
			Das Syndikatsforum dient der Syndikatsinternen Kommunikation und
			Absprache.<br>
			Hier können aktuelle Geschehnisse mit den Syndikatsmitgliedern
			diskutiert werden.<br>
			<br>
		{elseif $SITE == "allianzboard"}
			<a href="syndboard.php" class="hrAufSiteBg">Zum Syndikats Board</a><br>
			<br>
			Das Allianzforum dient der syndikatsübergreifenden Kommunikation und
			Absprache mit den Allianzpartnern.<br>
			Hier können aktuelle Geschehnisse mit
			den Mitgliedern der Allianz diskutiert werden.<br>
			<br>
		{/if}
		<table width="598" border="0" cellpadding="4" cellspacing="1">
			<tr>
				<td align="left" class="siteGround"><strong><a href="{$SITE}.php?action=create" class="linkAufSitebg">{if $SITE == "fragen_und_antworten_board"}<b style='font-size: larger'>Neue Frage stellen</b>{else}Neues Thema{/if}</a></strong></td>
				<td align="right"><strong><a href="{$SITE}.php?action=markforumread" class="linkAufSitebg">Forum als gelesen markieren</a></strong></td>
			</tr>
		</table>
		<br />
		<table width="598" border="0" cellpadding="0" cellspacing="0" class="tableOutline">
			<tr>
				<td>
					 <table width="598" border="0" cellpadding="4" cellspacing="1">
						<tr class="tableHead">
							<td width="100%" align="left">{if $SITE == "fragen_und_antworten_board"}Frage{else}Thema{/if}</td>
							<td align="center" nowrap>Erstellt von</td>
							<td align="center">Antworten</td>
							<td align="right" nowrap>Letzter Beitrag</td>
							{if $IS_PRESIDENT}
							<td>&nbsp;</td>
							{/if}
						</tr>
						{if $IS_PRESIDENT}
						<form action="{$SITE}.php" method="post">
							<input type="hidden" name="action" value="deletethreads">
						{/if}
							{foreach name=all_topics item=TOPIC from=$TOPICS}
							<tr class="tableInner1">
								<td width="100%">
									{if $TOPIC.is_sticky == 1} <img src="{$GP_PATH}dot-blau.gif" border="0">{/if}
									{if $TOPIC.is_new == 1} <a href="{$SITE}.php?action=view&tid={$TOPIC.id}&newest=newest#newest" class="linkAufTableInner"><img src="{$GP_PATH}firstnew.gif" border="0"></a>{/if}
									<b><a href="{$SITE}.php?action=view&tid={$TOPIC.id}" class="linkAufTableInner">{$TOPIC.topic_name}</a></b>
								</td>
								<td align="center" nowrap>
									{$TOPIC.date}<br>
									<b>von {$TOPIC.creator}
									{if $TOPIC.from == 1}
										(#{$TOPIC.creator_syn_id})
									{/if}</b>
								</td>
								<td align="center">{$TOPIC.posts}</td>
								<td align="left" nowrap>
									<table class="tableInner1">
										<tr>
											<td><a href="{$SITE}.php?action=view&tid={$TOPIC.id}&seite=last#last" class="linkAufTableInner"><img src="{$GP_PATH}lastpost.gif" border="0"></a></td>
											<td nowrap> {$TOPIC.last_date}<br>
												<b>von {$TOPIC.last_poster}
												{if $TOPIC.from == 1}
												(#{$TOPIC.last_syn_id})
												{/if} </b></td>
										</tr>
									</table>
								</td>
								{if $IS_PRESIDENT}
								<td align="center"><input type="checkbox" name="delete{$TOPIC.subjects}" value="{$TOPIC.id}"></td>
								{/if}
							</tr>
							{foreachelse}
							<tr>
								<td class="tableInner1" align="center" width="100%" colspan="{if $IS_PRESIDENT}5{else}4{/if}">Keine Themen vorhanden.</td>
							</tr>
							{/foreach}
							{if $IS_PRESIDENT}
							<tr class="tableInner1">
								<td colspan="5" width="598" align="right"><input type="submit" value="Markierte Themen löschen"></td>
							</tr>
						</form>
						{/if}
					</table>
				</td>
			</tr>
			{if $IS_PRESIDENT_AND_NO_GP == 1}
			<tr class="siteGround">
				<td style="padding-top:5px; padding-left:5px;"> <a href="{$SITE}.php?action=manageaccessrights" class="linkAufsiteBg"><img src="{$GP_PATH}dot-gelb.gif" border="0"> Zugriffsrechte festlegen</a></td>
			</tr>
			{/if}
		</table>
		<br />
		<br />
		<br />
		<br />
		<table align="center" cellpadding="3" cellspacing="0" border="0" class="siteGround">
			<tr>
				<td><img src="{$GP_PATH}firstnew.gif" border="0"> Zum ersten ungelesenen Beitrag springen</td>
			</tr>
			<tr>
				<td><img src="{$GP_PATH}lastpost.gif" border="0"> Zum letzten Beitrag springen</td>
			</tr>
			<tr>
				<td><img src="{$GP_PATH}dot-blau.gif" border="0">&nbsp;&nbsp; wichtiges Thema</td>
			</tr>
		</table>
