<html>
    <head>
        <title>{$PAGE_TITLE}</title>
        <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/general.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/head.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/clock.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/menu.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/main.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/box1.css" />
		<link rel="stylesheet" type="text/css" href="{$GP_PATH}css/box2.css" />
        <link rel="SHORTCUT ICON" href="{$LOCAL_IMAGEPATH}syn_favicon.ico" />
        {if $MSIE_USED}
			<link REL="stylesheet" TYPE="text/css" HREF="{$MSIE_STYLESHEET}" />
		{/if}
		{if $ENABLE_DOJO}
			<style type="text/css">
				@import "dojo/dijit/themes/tundra/tundra.css";
			</style>
			<script type="text/javascript" src="dojo/dojo/dojo.js" djConfig="parseOnLoad: true">
			</script>
			<script type="text/javascript" src="dojo/dojo/mydojo.js">
			</script>
        {/if}
		{include file="js/header.js.tpl"}
    </head>
    <body onload="showTime();{if $COMMERCIALS} werbung_nachladen();{/if}" background="{$IMAGE_PATH}bg.gif" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" style="text-align: center;" class="tundra">
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
		<div id="head_root">
    		<div id="head_drag_handle"></div>
    		<div id="head"></div>
			<div id="head_back"></div>
    		<div id="resbar" class="resbar_closed">
			{if $LOGGED_IN}
        		<span id="resbar_credits">{$CREDITS}</span>
        		<span id="resbar_energy">{$ENERGY}</span>
        		<span id="resbar_minerals">{$MINERALS}</span>
        		<span id="resbar_sciencepoints">{$SCIENCEPOINTS}</span>
        	{else}
        		<span id="resbar_credits">--</span>
        		<span id="resbar_energy">--</span>
        		<span id="resbar_minerals">--</span>
        		<span id="resbar_sciencepoints">--</span>
        	{/if}
    		</div>
    		<div id="resbar_button"></div>
		</div>
		<div id="clocknw">
		{if $LOGGED_IN}
    		<span id="clocknw_land_img"></span>
			<span id="clocknw_land">{$LAND}</span>
    		<span id="clocknw_nw_img"></span>
    		<span id="clocknw_nw">{$NETWORTH}</span>
        {else}
    		<span id="clocknw_land_img"></span>
			<span id="clocknw_land">--</span>
    		<span id="clocknw_nw_img"></span>
    		<span id="clocknw_nw">--</span>
        {/if}
		</div>
		{include file="menu.tpl"}
															