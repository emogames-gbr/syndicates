		<br />
		<br />
		<table width=600 align="center">
			<tr>
				<td width="57%" valign="top">
					<table class="tableOutline" border="0" cellspacing="1" cellpadding="2" width="100%">
						<tr class="tableHead2">
							<td align="center" width="32px"><img src="{$GP_PATH}dot-gelb.gif" border="0" hspace="5"></td>
							<td align="right" width="25px">&nbsp;</td>
							<td>&nbsp;&nbsp;Konzern</td>
							<td>&nbsp;&nbsp;Emo-Nick</td>
							<td align="right" width="40px"></td>
						</tr>
                        {if $BUDDYSA}
                        <tr class="tableInner2">
                        	<td colspan="5">&nbsp;<b>Offenen Buddyanfragen</b></td>
                        </tr>
						{foreach name=buddylistA item=BUDDY from=$BUDDYSA}
						<tr class="tableInner1">
							<td align="center">{if $BUDDY.rid} <a href=javascript:info('fraktionen','{$BUDDY.race}') class="highlightAuftableInner"><img src="{$GP_PATH}{$BUDDY.raceicon}.gif" alt="{$BUDDY.race}" height="22" width="22" border="0"></a>{/if}</td>
							<td align="center">{if $BUDDY.rid} <img src="{$GP_PATH}_{$BUDDY.online}.gif" border="0" align="absmiddle">{/if}</td>
							<td>{if $BUDDY.rid}&nbsp;<a href="syndicate.php?action=details&detailsid={$BUDDY.sid}&rid={$BUDDY.rid}" class="linkAuftableInner">{$BUDDY.konzname}</a> <a href="syndicate.php?&rid={$BUDDY.rid}" class="linkAuftableInner">(#{$BUDDY.rid})</a>{/if}</td>
							<td>&nbsp;{$BUDDY.emonick}</td>
							<td align="left" nowrap="nowrap">
                                <a href="buddy.php?action=ok&id={$BUDDY.uid}"><img src="{$GP_PATH}_praesi.gif" style="height:18px; vertical-align:middle; border:none;" alt="Buddy hinzufügen" title="Buddy hinzufügen"></a>
                                <a href="buddy.php?action=kick&id={$BUDDY.uid}"><img src="{$GP_PATH}icon_nasp_04.png" style="height:18px; border:none; vertical-align:middle" alt="Intressiert mich nicht!" title="Intressiert mich nicht!"></a>
                                {if $BUDDY.sid}<a href="mitteilungen.php?action=psm&rec={$BUDDY.sid}"><img src="{$GP_PATH}_syn_message_letter.gif" style="height:18px; border:none; vertical-align:middle" alt="{literal}{{/literal}{$BUDDY.name} (#{$BUDDY.rid}){literal}}{/literal} eine Nachricht senden"></a>{/if}
							</td>
						</tr>
						{/foreach}
                        {/if}
                        <tr class="tableInner2">
                        	<td colspan="5">&nbsp;<b>Buddys</b> ({$BUDDYS_COUNT} von maximal {$BUDDYS_COUNT_MAX})</td>
                        </tr>
                        {foreach name=buddylist item=BUDDY from=$BUDDYS}
						<tr class="tableInner1">
							<td align="center">{if $BUDDY.rid} <a href=javascript:info('fraktionen','{$BUDDY.race}') class="highlightAuftableInner"><img src="{$GP_PATH}{$BUDDY.raceicon}.gif" alt="{$BUDDY.race}" height="22" width="22" border="0"></a>{/if}</td>
							<td align="center">{if $BUDDY.rid} <img src="{$GP_PATH}_{$BUDDY.online}.gif" border="0" align="absmiddle">{/if}</td>
							<td>{if $BUDDY.rid}&nbsp;<a href="syndicate.php?action=details&detailsid={$BUDDY.sid}&rid={$BUDDY.rid}" class="linkAuftableInner">{$BUDDY.konzname}</a> <a href="syndicate.php?&rid={$BUDDY.rid}" class="linkAuftableInner">(#{$BUDDY.rid})</a>{/if}</td>
							<td>&nbsp;{$BUDDY.emonick}</td>
							<td align="left" nowrap="nowrap">
								<a href="buddy.php?action=kick&id={$BUDDY.uid}" onclick="return window.confirm('{$BUDDY.emonick} wirklich aus deiner Buddyliste entfernen?');"><img src="{$GP_PATH}icon_nasp_04.png" style="height:18px; border:none; vertical-align:middle" alt="Buddy löschen" title="Buddy löschen"></a>
								{if $BUDDY.sid}<a href="mitteilungen.php?action=psm&rec={$BUDDY.sid}"><img src="{$GP_PATH}_syn_message_letter.gif" style="height:18px; border:none; vertical-align:middle" alt="{literal}{{/literal}{$BUDDY.name} (#{$BUDDY.rid}){literal}}{/literal} eine Nachricht senden"></a>{/if}
							</td>
						</tr>
						{/foreach}
					</table>
				</td>
				<td width="3%"></td>
				{if $KNOWS}<td width="40%" valign="top">
					<table class="tableOutline" border="0" cellspacing="1" cellpadding="2" width="100%">
						<tr class="tableHead">
							<td>&nbsp;&nbsp;Kennst du?</td>
						</tr>
						<tr class="tableInner1">
							<td>
							{foreach name=knowlist item=KNOW from=$KNOWS}
								<div style="width:100%; border-bottom:#333 1px solid;">
									<div class="knowlist_name" rel="{$smarty.foreach.knowlist.index}_box_tr" style="width:100%; line-height:23px;">
										
										<div style=" margin-top:3px;">
											<a href="buddy.php?action=ok&id={$KNOW.uid}">
												<img src="{$GP_PATH}_praesi.gif" style="height:18px; vertical-align:middle; border:none;" alt="Buddy hinzufügen" title="Buddy hinzufügen"></a>
											<a href="buddy.php?action=kick&id={$KNOW.uid}">
												<img src="{$GP_PATH}icon_nasp_04.png" style="height:18px; border:none; vertical-align:middle" alt="Interessiert mich nicht!" title="Interessiert mich nicht!"></a>
											&nbsp;{$KNOW.emonick}
										</div>
										{* {$KNOW.name} {if $ISRANDOMRUNDE}<i>({$KNOW.emonick})</i>{/if} *}
									</div>
									<div id="{$smarty.foreach.knowlist.index}_box_tr" style="float:no; display:none; padding:2px 8px 5px 8px;">
										{$KNOW.rounds}
									</div>
								</div>
							{/foreach}
							</td>
					</table>
				</td>
                {/if}
			</tr>
		</table><br />
        <p align=right><a href="buddy.php?action=new" class="linkAufTableInner">Buddy hinzuf&uuml;gen</a></p>
		{include file="js/buddy.js.tpl"}