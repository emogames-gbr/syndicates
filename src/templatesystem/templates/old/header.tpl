<html>
    <head>
        <title>{$PAGE_TITLE}</title>
        <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
		<meta name="viewport" content="width=800" />
        <link rel="stylesheet" type="text/css" href="{$GP_PATH}style.css" />
        <link rel="SHORTCUT ICON" href="{$LOCAL_IMAGEPATH}syn_favicon.ico" />
        {if $MSIE_USED}
			<link REL="stylesheet" TYPE="text/css" HREF="{$MSIE_STYLESHEET}" />
		{/if}
		{literal}
		<style type="text/css">
			@import "https://ajax.googleapis.com/ajax/libs/dojo/1.5/dijit/themes/tundra/tundra.css";
			.dijitButtonText {
				font-size:12px;
			}
			#ticker_more {
				height:28px;
			}
			.tipp {
				position:fixed;
				right:-1000px;
				width:300px;
				border:1px solid #000;
				-webkit-border-radius:5px;
				   -moz-border-radius:5px;
				    -ms-border-radius:5px;
				     -o-border-radius:5px;
				        border-radius:5px;
				-webkit-box-shadow:3px 3px 3px rgba(0,0,0,0.5);
				   -moz-box-shadow:3px 3px 3px rgba(0,0,0,0.5);
				    -ms-box-shadow:3px 3px 3px rgba(0,0,0,0.5);
				     -o-box-shadow:3px 3px 3px rgba(0,0,0,0.5);
				        box-shadow:3px 3px 3px rgba(0,0,0,0.5);
				overflow:hidden;
			}
			.tippHead {
				text-align:left;
				padding:3px 10px;
				border-bottom:1px solid #000;
			}
			.tippBody {
				padding:10px;
				font-size:13px;
			}
			.tippControl {
				text-align:left;
			}
			.tipp a {
				color:inherit;
				text-decoration:none;
				cursor:pointer;
			}
			.tipp a:hover {
				-webkit-text-shadow:1px 1px 1px rgba(0,0,0,0.5);
				   -moz-text-shadow:1px 1px 1px rgba(0,0,0,0.5);
				    -ms-text-shadow:1px 1px 1px rgba(0,0,0,0.5);
				     -o-text-shadow:1px 1px 1px rgba(0,0,0,0.5);
				        text-shadow:1px 1px 1px rgba(0,0,0,0.5);
			}
			
			
		</style>
		{/literal}
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojo/dojo.xd.js" djConfig="parseOnLoad: true"></script>
		<script type="text/javascript">
			dojo.require("dojo.fx");
			dojo.require("dijit.form.DropDownButton");
			dojo.require("dijit.TooltipDialog");
			dojo.require("dijit.form.TextBox");
			dojo.require("dijit.form.Button");
			dojo.require("dojox.widget.AutoRotator");
			dojo.require("dojox.widget.rotator.Fade");
			dojo.require("dojox.widget.rotator.Slide");
		</script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
		{include file="js/header.js.tpl"}
        
        {$FLASH_JS}
        
		{if $CHAT}
			{$CHAT_JAVASCRIPT}
		{/if}
    </head>
    <body onLoad="showTime();{if $COMMERCIALS} werbung_nachladen();{/if}{if $CHAT} chat(); {/if}" background="{$IMAGE_PATH}bg.gif" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" style="text-align: center;" class="tundra">
		<!--BEGIN MouseOver Hilfe-->
			<div style="z-index:10; position:absolute;display:none;border: 0px solid black" id="over">
				<table id="over_table" class="bodys" cellspacing="0" cellpadding="0">
					<tr>
						<td id="over_inner">
						</td>
					</tr>
				</table>
			</div>
		<!--END MouseOver Hilfe-->
		{if !$HIDETIPPS}
		<div class="tipp" data-id="{$TIPP.id}">
			<div class="tableHead tippHead">
				Tipp <span id="tipp_num">{$TIPP.num}</span> / <span id="tipp_sum">{$TIPP.sum}</span>
				<span style="float:right; vertical-align:top;">
					<input style="vertical-align:middle;" class="readTipp" title="als gelesen markieren" type="checkbox" name="readTipp" value="1" />
					<a style="vertical-align:middle;margin-left:3px;" class="hideTipp" title="verstecken">X</a>
				</span>
			</div>
			<div class="tableInner1 tippBody">
				<span id="tipp_Text">{$TIPP.text}</span>
				<div class="tippControl">
					<span>
						<a class="prevTipp" title="vorheriger Tipp">&lt;&lt;&lt;</a>
					</span>
					<span style="float:right;">
						<a class="nextTipp" title="nächster Tipp">&gt;&gt;&gt;</a>
					</span>
				</div>
			</div>
		</div>
		{/if}
		<div style="text-align:center;">
			<br>
			<div style="margin-left:auto;margin-right:auto;text-align:left;width:{$OLD_OUTERWIDTH_VALUE}px;">
				<!-- ï¿½uï¿½ere Tabelle fï¿½r Skyscraper noch -->
				<table style="width:100%" >
					<tr>
						<td >
							<!-- fï¿½r den globalen schwarzen rahmen -->
							<table width="782" bgcolor="#000000" align="center" cellspacing="0" cellpadding="1" border="0">
								<tr>
									<td valign="top" align="center">

										<table width="780" class="siteGround" align="center" cellspacing="0" cellpadding="0" border="0">
											<tr>
												<td colspan="4">
													
													<table width="100%" cellspacing="0" cellpadding="0" border="0">
														<tr>
															<td>
																<a href="statusseite.php">
																	<img src="{$IMAGE_PATH}header.jpg" alt="Syndicates" border="0">
																</a>
															</td>
														</tr>
													</table>
												
												</td>
											</tr>
											<tr>
												<td colspan="4" height="1" bgcolor="#000000">
												</td>
											</tr>
											{if $LOGGED_IN}
											<tr>
												<td height="16" colspan="5" valign="bottom">
													<!-- ##### TOP LEISTE ####### -->
													<table width="100%" height="100%" background="{$IMAGE_PATH}menubar-reversed.png" cellspacing="0" cellpadding="0" border="0">
														<tr>
															<td width=30 align=right><img src="{$IMAGE_PATH}networth.gif" border=0></td>
															<td class="resourceLeiste">&nbsp; {$NETWORTH} NW</td>
															<td width=1><img src="{$IMAGE_PATH}land.gif" border=0></td>
															<td class="resourceLeiste">&nbsp; {$LAND} ha</td>
															<td width=1><img src="{$IMAGE_PATH}credits.gif" border=0></td>
															<td class="resourceLeiste" id="credits_number">&nbsp; {$CREDITS} Cr</td>
															<td width=1><img src="{$IMAGE_PATH}energie.gif" border=0></td>
															<td class="resourceLeiste">&nbsp; {$ENERGY} MWh</td>
															<td width=1><img src="{$IMAGE_PATH}erz.gif" border=0></td>
															<td class="resourceLeiste">&nbsp; {$MINERALS} t</td>
															<td width=1><img src="{$IMAGE_PATH}fp.gif" border=0></td>
															<td class="resourceLeiste">&nbsp; {$SCIENCEPOINTS} P</td>
															<td width=1><img src="{$IMAGE_PATH}time.gif" id="timeicon" border=0></td>
															<td nowrap=nowrap class="resourceLeiste" width=1><span id="zeit" name="zeit">&nbsp;{$TIMESTAMP}</span>&nbsp;Uhr&nbsp;&nbsp;</td>
														</tr>
													</table>
													<!-- ##### TOP LEISTE ## ENDE  ## -->
												</td>
											</tr>
											<tr>
												<td colspan="4" height="1" bgcolor="#000000"></td>
											</tr>
											<tr>
												<td colspan="4" height="20"></td>
											</tr>
											{/if}
											<tr>
												{include file="menu.tpl"}
												<td class="ver12w" width="600" align="center" valign="top"{$COLSPAN_MENU}>
													<!-- ########## AUSGABE ########## -->
													<!-- Ausgabetabelle ï¿½ffnen -->
													<table class="siteGround" cellspacing=0 align=left width="100%" cellpadding=0>
														<tr>
															<td align="left">
		<br>
		<table width=100% border=0 cellspacing=0 cellpadding=0 align=center>
			{$VOTECODE}
			<tr>
				<td width=400><b class=titleH1>{$HEADLINE}</b></td>
				<td width=200 align=right>
					<a href="{$LINK_HILFESEITE}" target="_blank" class="linkAuftableInner">
						<img onMouseOver="showover(event,'','');contentover('<table border=0 cellspacing=0 cellpadding=1 class=tableOutline><tr><td><table cellspacing=0 cellpadding=2><tr><td class=tableInner1>{$TOOLTIPPTEXT}</td></tr></table></td></tr></table>')"  onmouseout="hideover()" src="{$GP_PATH}_help_bigger.gif" border=0 valign="absmiddle" />
					</a>
				</td>
			</tr>
			<tr>
				<td colspan=2 width="100%" height=1 class=titleLine></td>
			</tr>
			{if $WERBUNG}
			<tr>
				<td align="center" colspan="2" style="padding-bottom:10px;padding-top:10px;">{$WERBUNG}</td>
			</tr>
			{/if}
			
			{if $TICKER}
			<tr>
				<td align="center" colspan="2">
					<table class="normal" cellspacing="0" cellpadding="0" width="500" align="center">
						<tr>
							<td><br></td>
						</tr>
						<tr>
							<td align="center" class="tableOutline" style="padding:1px;">
								<div dojoType="dojox.widget.AutoRotator" style="width:100%; height:32px; overflow:hidden;" id="Ticker" jsId="Ticker" duration="7000" transition="dojox.widget.rotator.crossFade">
									{foreach name=msgs item=T_MSG from=$T_MSGS}
									<div class="tableInner1" style="width:inherit; height:inherit;">{$T_MSG.text}</div>
									{/foreach}
								</div>
							</td>
						</tr>
						<tr>
							<td align="right">
								<div dojoType="dijit.form.DropDownButton">
									<span id="ticker_more">mehr</span>
									<div dojoType="dijit.TooltipDialog" style="display:none;">
										<table align="center" cellspacing="1" cellpadding="3" width=300 bgcolor="black">
											<tr class="tableHead" align="center">
												<td>
													Neue Ticker-Nachricht (max {$TICKER.max} Zeichen):
												</td>
											</tr>
											<tr class="tableInner1" align="center">
												<td>
													<form action="" method="post">
														<input type="hidden" id="action" name="action" value="ticker_add">
														<input type="hidden" id="ia2" name="ia2" value="ticker_add">
														<div style="margin:5px">Eine Nachricht im Ticker kostet <b>{$TICKER.emos} EMOs</b></div>
														<input id="tickernews" type="text" maxlength="{$TICKER.max}" name="tickernews" size="40" />
														<br />
														<br />
														<button type="submit">eintragen</button>
													</form>
													<br />
													<a href="ticker_archiv.php" class="linkAuftableInner">zum Archiv</a><br /><br />                                                    <a href="{$TICKER.wiki}" class="linkAuftableInner" target="_blank">Hilfe</a><br /><br />
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
					</table>
					<br>
				</td>
			</tr>
			{/if}
			
			{if $CHAT}
			<tr>
				<td colspan=2 height=5></td>
			</tr>
			<tr>
				<td colspan=2>
					<table cellpadding="0" cellspacing="0" border="0" width="587" class="tableOutline">
						<tr>
							<td colspan="2" class="resourceLeiste" align=center width="100%" height="16" onClick="doChatAnimation();" style="cursor:pointer;background-image:url({$IMAGE_PATH}menubar-reversed.png);">
								<p style="font-weight:bold;">Chat</p>
							</td>
						</tr>
						<tr id="chatChannelHead" style="display:none;">
							<td colspan="2">
								<table border="1" rules="cols" cellpadding="0" cellspacing="0" height="5" width="100%">
									<tr>
									{if $CHAT_ALLY}
										<td id="chat_channel_head_glob" class="tableHead" style="text-align:center;cursor:pointer;font-weight:bold;" onClick="javascript:changeChannel('Global');" width="25%">Global</td>
										<td id="chat_channel_head_syn" class="tableHead" style="text-align:center;cursor:pointer;font-weight:normal;" onClick="javascript:changeChannel('Syndikat');" width="25%">Syndikat</td>
										<td id="chat_channel_head_alli" class="tableHead" style="text-align:center;cursor:pointer;font-weight:normal;" onClick="javascript:changeChannel('Allianz');" width="25%">Allianz</td>
										<td id="chat_channel_head_help" class="tableHead" style="text-align:center;cursor:pointer;font-weight:normal;" onClick="javascript:changeChannel('Hilfe');" width="25%">Hilfe</td>
									{else}
										<td id="chat_channel_head_glob" class="tableHead" style="text-align:center;cursor:pointer;font-weight:bold;" onClick="javascript:changeChannel('Global');" width="33%">Global</td>
										<td id="chat_channel_head_syn" class="tableHead" style="text-align:center;cursor:pointer;font-weight:normal;" onClick="javascript:changeChannel('Syndikat');" width="33%">Syndikat</td>
										<td id="chat_channel_head_help" class="tableHead" style="text-align:center;cursor:pointer;font-weight:normal;" onClick="javascript:changeChannel('Hilfe');" width="33%">Hilfe</td>
									 {/if}
									
									</tr>
								</table>
							</td>
						</tr>
						<tr id="chatTable" height="25" style="overflow:hidden;">
							<td width="90" id="chatSidePanel" class="tableHead" style="text-align:center;">Global</td>
							<td class="tableInner1">
								<div class="menueInner" style="width:497;height:25;overflow:auto;" id="chatwindow" ><p id="chat"></p></div>
								<form name="chat" id="chatForm" style="padding:0;margin:0;display:none;" action="#" onSubmit="javascript:chatSay(document.getElementById('chatInput').value);document.getElementById('chatInput').value='';return false;">
									&nbsp;&nbsp;&nbsp;<input type="text" id="chatInput" maxlength="255" size="78">&nbsp;<input type="submit"  value="Senden">
								</form>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=2 height=5></td>
			</tr>
			<tr>
				<td colspan=2 height=1 class=titleLine></td>
			</tr>
			{/if}
			{if $SHOW_HELP == 1}
			<tr>
				<td colspan=2>
					<table cellpadding=5 width=100% align=center>
						<tr>
							<td class=i style="border: 1px solid">
								{$HILFETEXT} (Fragezeichen oben rechts)
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan=2 height=1 class=titleLine></td></tr>
			{/if}
		</table>													