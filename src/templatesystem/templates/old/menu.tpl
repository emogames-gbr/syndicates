{if $LOGIN}
<td width="130" valign="top" align="right">
	{if $ENABLE_TESTSERVER_MENU == 1}{include file='menu_testserver.tpl'}{/if}
    {if $MENU_INDIVIDUAL_POSITION == 1}{include file='menu_individual.tpl'}{/if}
    <table width="120" cellspacing="0" cellpadding="0" border="0" class="tableOutline">
        <tr>
            <td width="120">
                <table width="120" cellspacing="1" cellpadding="0" border="0">
                    <tr>
                        <td>
                            <table width="120" height="16" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="menueHead" background="{$IMAGE_PATH}menubar.png" class="ver11w" align="left" width="120">
                                        <b>&nbsp;&nbsp;Konzern</b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="120" cellspacing="0" cellpadding="3" border="0">
                                {foreach from=$MENU_KONZERN item=menuPoint}
                                <tr>
                                    <td class="menueInner" id="menu_{$menuPoint.sname}">
                                        {if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}">
                                            <img src="{$IMAGE_PATH}dot-{if $menuPoint.dot == 'red'}rot{else}gruen{/if}.gif" hspace="5" border="0" />
                                            <span id="menu_{$menuPoint.sname}_text">
                                            	{$menuPoint.name}
                                            </span>
                                        </a>{else}{$menuPoint.name}{/if}
                                    </td>
                                </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><img src="images/5E78A4.gif" width="1" height="6" border="0" />
    {if $MENU_INDIVIDUAL_POSITION == 2}{include file='menu_individual.tpl'}{/if}
    <table width="120" cellspacing="0" cellpadding="0" border="0" class="tableOutline">
        <tr>
            <td width="120">
                <table width="120" cellspacing="1" cellpadding="0" border="0">
                     <tr>
                        <td>
                            <table width="120" height="16" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="menueHead" background="{$IMAGE_PATH}menubar.png" class="ver11w" align="left" width="120">
                                        <b>&nbsp;&nbsp;Kommunikation</b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="120" cellspacing=0 cellpadding=3 border=0>
                                <tr>
                                    <td class="menueInner" id="menu_buddy">
                                        <a href="buddy.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.buddy == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Buddy {$MENU_BUDDY_NUMS}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="menueInner" class="menu_msg">
                                        <a href="mitteilungen.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.msg == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            <span{if $MENU_NEW_MAIL} class="gruenAuftableInner"{/if} id="menu_msg_text">
                                            Mitteilungen
                                            </span>{if $MENU_NEW_MAIL}<img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="menueInner" id="menu_notifications">
                                        <a href="nachrichten.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.news == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            <span 
                                            {if $MENU_NEW_MSG_CATEGORY == 'warning'}
                                            class="achtungAuftableInner" 
                                            {elseif $MENU_NEW_MSG_CATEGORY == 'highlight'}
                                            class="highlightAuftableInner" 
                                            {elseif $MENU_NEW_MSG_CATEGORY == 'green'}
                                            class="gruenAuftableInner" 
                                            {elseif $MENU_NEW_MSG_CATEGORY == 'nospan'}
                                            class="nospan" 
                                            {/if}
                                            id="menu_notifications_text">
                                            Nachrichten
                                            </span>
                                            {if $MENU_NEW_MSG_CATEGORY == 'warning' || $MENU_NEW_MSG_CATEGORY == 'highlight' ||
                                            $MENU_NEW_MSG_CATEGORY == 'green' || $MENU_NEW_MSG_CATEGORY == 'nospan'}
                                            <img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                {if $SERVER != 'basic'}
                                <tr>
                                    <td class="menueInner" id="menu_vote">
                                        <a href="polls.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.polls == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            {if $MENU_NEW_VOTING}<span class="gruenAuftableInner">{/if}
                                            Umfragen
                                            {if $MENU_NEW_VOTING}</span> <img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                {if $MENU_SHOW_TUTOR_BOARD}
                                <tr>
                                    <td class="menueInner" id="menu_tutorboard">
                                        <a href="tutorboard.php" {if $TUTORBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.tuts == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Tutor Board{if $MENU_TUTOR_BOARD_NEW > 0} ({$MENU_TUTOR_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                {if $MENU_SHOW_GROUP_BOARD}
                                <tr>
                                    <td class="menueInner" id="menu_gruppenboard">
                                        <a href="gruppenboard.php" {if $GROUPBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.grpb == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Gruppenboard{if $MENU_GROUP_BOARD_NEW > 0} ({$MENU_GROUP_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                <tr>
                                    <td class="menueInner" id="menu_syndboard">
                                        <a href="syndboard.php" {if $SYNDBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.synb == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Synd. Board{if $MENU_SYND_BOARD_NEW > 0} ({$MENU_SYND_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {if $MENU_SHOW_ALLY_BOARD}
                                <tr>
                                    <td class="menueInner" id="menu_allyboard">
                                        <a href="allianzboard.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.ally == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Allianzboard{if $MENU_ALLY_BOARD_NEW > 0} ({$MENU_ALLY_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                {if $MENU_SHOW_FRAGEN_UND_ANTWORTEN_BOARD}
                                <tr>
                                    <td class="menueInner" id="menu_qna">
                                        <a href="fragen_und_antworten_board.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.qab == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Q&A Board {if $MENU_FRAGEN_UND_ANTWORTEN_BOARD_NEW > 0} ({$MENU_FRAGEN_UND_ANTWORTEN_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                <tr>
                                    <td class="menueInner" id="menu_allgboard">
                                        <a href="allgboard.php" class="linkMenue" target="_blank">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.allg == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Forum
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="menueInner" id="menu_allgboard">
                                        <a href="merchandise.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot{if $KOMDOTS.merch == 'red'}-rot{/if}.gif" hspace="5" border="0" />
                                            Merchandise
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><img src="images/5E78A4.gif" width="1" height="6" border="0" />
    {if $MENU_INDIVIDUAL_POSITION == 3}{include file='menu_individual.tpl'}{/if}
    <table width="120" cellspacing="0" cellpadding="0" border="0" class="tableOutline">
        <tr>
            <td width="120">
                <table width="120" cellspacing="1" cellpadding="0" border="0">
                     <tr>
                        <td>
                            <table width="120" height="16" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="menueHead" background="{$IMAGE_PATH}menubar.png" class="ver11w" align="left" width="120">
                                        <b>&nbsp;&nbsp;Syndikate&Welt</b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="120" cellspacing="0" cellpadding="3" border="0">
                                {foreach from=$MENU_SYNWORLD item=menuPoint}
                                <tr>
                                    <td class="menueInner" id="menu_{$menuPoint.sname}">
                                        {if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}">
                                            <img src="{$IMAGE_PATH}dot-{if $menuPoint.dot == 'red'}rot{else}gelb{/if}.gif" hspace="5" border="0" />
                                            <span id="menu_{$menuPoint.sname}_text">
                                            	{$menuPoint.name}
                                            </span>
                                        </a>{else}{$menuPoint.name}{/if}
                                    </td>
                                </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><img src="images/5E78A4.gif" width="1" height="6" border="0" />
    {if $MENU_SHOW_GN_VOTE}
    <br>
	<table width=\"120\" cellspacing=\"1\" cellpadding=\"3\" border=\"0\" class=\"tableOutline\">
		<tr>
			<td width=\"120\" class=\"menueInner\" align=\"center\">
				<a href='?headeraction=galaxy-news' target='_blank'>
					Einmal täglich
					<img src=\"http://www.galaxy-news.de/images/vote.gif\" alt=\"vote now!\" border=\"0\">
				</a>
			</td>
		</tr>
	</table>
	<br>
	{/if}
    {if $MENU_INDIVIDUAL_POSITION == 4}{include file='menu_individual.tpl'}{/if}
    <table width="120" cellspacing="0" cellpadding="0" border="0" class="tableOutline">
        <tr>
            <td width="120">
                <table width="120" cellspacing="1" cellpadding="0" border="0">
                    <tr>
                        <td>
                            <table width="120" cellspacing="0" cellpadding="3" border="0">
                                {foreach from=$MENU_UNNAMED item=menuPoint}
                                <tr>
                                    <td class="menueInner">
                                        {if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}" {if $menuPoint.target}target="_blank" {/if}>
                                            <img src="{$IMAGE_PATH}dot-{if $menuPoint.dot == 'red'}rot{else}blau{/if}.gif" hspace="5" border="0" />
                                            {$menuPoint.name}
                                        </a>{else}{$menuPoint.name}{/if}
                                    </td>
                                </tr>
                                {/foreach}
  								{if $NOOB_IN_PROT}
   								<tr>
									<td class=\"menueInner\">
										<hr>
											&nbsp;Komplettes Menü &nbsp;<a href=javascript:info('menu','1') class=\"highlightAuftableInner\"><img src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\"></a>
									</td>
								</tr>
   								{/if}
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><img src="images/5E78A4.gif" width="1" height="6" border="0" />
    {if $MENU_INDIVIDUAL_POSITION == 5}{include file='menu_individual.tpl'}{/if}
	<br>
</td>
<td class="ver12w" width="30">
	<img src="images/5E78A4.gif" width="1" height="6" border="0" />
</td>

{/if}
