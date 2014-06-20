{if $LOGIN}
{if $ENABLE_TESTSERVER_MENU == 1}{include file='menu_testserver.tpl'}{/if}
   {if $MENU_INDIVIDUAL_POSITION == 1}{include file='menu_individual.tpl'}{/if}
<div id="menu_konzern" class="menu_block">
    <div id="menu_drag_handle_konzern" class="menu_drag_handle"></div>
    <div id="menu_head_konzern" class="menu_head">
        <span class="menu_head_inner">Konzern</span>
    </div>
    <div id="menu_items_konzern" class="menu_items menu_items_opened" style="height: 206px;">
     	{counter start=0 print=false}
     	{foreach from=$MENU_KONZERN item=menuPoint}
        <div id="menu_item{counter}_konzern" class="menu_item"><span class="menu_item_inner">
        	{if $menuPoint.linkfilename}
        		<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}">
            		{$menuPoint.name}
              	</a>
             {else}{$menuPoint.name}{/if}
        </span></div>
   		{/foreach}
    </div>
    <div id="menu_bottom_konzern" class="menu_bottom menu_bottom_opened">
        <div id="menu_button_konzern" class="menu_button"></div>
    </div>
</div>

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
                                    <td class="menueInner">
                                        <a href="mitteilungen.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            {if $MENU_NEW_MAIL}<span class="gruenAuftableInner">{/if}
                                            Mitteilungen
                                            {if $MENU_NEW_MAIL}</span> <img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="menueInner">
                                        <a href="nachrichten.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            {if $MENU_NEW_MSG_CATEGORY == 'warning'}
                                            <span class="achtungAuftableInner">
                                            {elseif $MENU_NEW_MSG_CATEGORY == 'highlight'}
                                            <span class="highlightAuftableInner">
                                            {elseif $MENU_NEW_MSG_CATEGORY == 'green'}
                                            <span class="gruenAuftableInner">
                                            {/if}
                                            Nachrichten
                                            {if $MENU_NEW_MSG_CATEGORY == 'warning' || $MENU_NEW_MSG_CATEGORY == 'highlight' ||
                                            $MENU_NEW_MSG_CATEGORY == 'green'}</span>
                                            <img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                {if $SERVER != 'basic'}
                                <tr>
                                    <td class="menueInner">
                                        <a href="polls.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            {if $MENU_NEW_VOTING}<span class="gruenAuftableInner">{/if}
                                            Umfragen
                                            {if $MENU_NEW_VOTING}</span> <img src="{$GP_PATH}mail1.gif" border="0" align="bottom" />{/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                {if $MENU_SHOW_TUTOR_BOARD}
                                <tr>
                                    <td class="menueInner">
                                        <a href="tutorboard.php" {if $TUTORBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            Tutor Board{if $MENU_TUTOR_BOARD_NEW > 0} ({$MENU_TUTOR_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                {if $MENU_SHOW_GROUP_BOARD}
                                <tr>
                                    <td class="menueInner">
                                        <a href="groupboard.php" {if $GROUPBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            Gruppenboard{if $MENU_GROUP_BOARD_NEW > 0} ({$MENU_GROUP_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                <tr>
                                    <td class="menueInner">
                                        <a href="syndboard.php" {if $SYNDBOARD_GREY} class="linkMenueGrey" {else} class="linkMenue" {/if}>
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            Synd. Board{if $MENU_SYND_BOARD_NEW > 0} ({$MENU_SYND_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {if $MENU_SHOW_ALLY_BOARD}
                                <tr>
                                    <td class="menueInner">
                                        <a href="groupboard.php" class="linkMenue">
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            Allianzboard{if $MENU_ALLY_BOARD_NEW > 0} ({$MENU_ALLY_BOARD_NEW}){/if}
                                        </a>
                                    </td>
                                </tr>
                                {/if}
                                <tr>
                                    <td class="menueInner">
                                        <a href="allgboard.php" class="linkMenue" target="_blank">
                                            <img src="{$IMAGE_PATH}dot.gif" hspace="5" border="0" />
                                            Forum
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
                                        <b>&nbsp;&nbsp;Syndikat & Welt</b>
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
                                    <td class="menueInner">
                                        {if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}">
                                            <img src="{$IMAGE_PATH}dot-{if $menuPoint.dot == 'red'}rot{else}gelb{/if}.gif" hspace="5" border="0" />
                                            {$menuPoint.name}
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
                                        {if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="{if $menuPoint.disabled == 1}linkMenueGrey{else}linkMenue{/if}">
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
											&nbsp;Komplettes Men? &nbsp;<a href=javascript:info('menu','1') class=\"highlightAuftableInner\"><img src=\"".$ripf."_help.gif\" border=0 valign=\"absmiddle\"></a>
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


{/if}
