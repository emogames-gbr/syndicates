{if $GOON}
	{if $MESSAGES || $KOMFORTPAKET}
		<script language="JavaScript">{literal}
				function checkAll(thecheckbox,frm) {
					if (thecheckbox.checked == true) {
						for (var i=0;i<frm.elements.length;i++) 	{
							var e = frm.elements[i];
							
							if ((e.type=='checkbox') && !e.disabled) 	{
								if (e.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.style.display == '')							
								e.checked =	true;
														}		
													}
											}
					else 	{
						for (var i=0;i<frm.elements.length;i++) {
							var e = frm.elements[i];
							if ((e.type=='checkbox') && !e.disabled) 	{
								e.checked =	false;
														}		
													}
						}
				}{/literal}
		</script>
		<center>
		<form action="nachrichten.php" method="post" name="items">
			<input type="hidden" name="action" value="delete">
			<table width="585" align="center" border="0">
				<tr>
					{if $KOMFORTPAKET}
					<td align="left" class="siteGround">
						<script language="JavaScript">

	var dabei = false;
	var new_ = false;
	var all = false;
	var ids = new Array();
				
	{foreach from=$KATEGORIEN item=TEMP}
	ids['id_{$TEMP}'] = false;
	{/foreach}

	{literal}
		
	function get_news(id){
		if(!dabei){
			document.getElementById('new_').className = '';
			if(!new_){
				if(document.getElementById('ajax').innerHTML.length > 0){
					new_ = document.getElementById('ajax').innerHTML;
				}
				else{
					new_ = true;
				}
			}
			dabei = true;
			dojo.byId('ajax').innerHTML = '';
			dojo.byId('ajax').style.visibility = 'hidden';
			dojo.byId('ajax_loader_text').style.display = 'block';

			if(id == 'all'){
				change_all();
			}
			else{
				ids['id_'+id] = !ids['id_'+id];
				change();
			}
			dojo.xhrGet({
					url: 'nachrichten.php?action=ajax{/literal}{$URL_}{literal}',
					load: function(data){
							dojo.byId('ajax').innerHTML = data;
							dojo.byId('ajax').style.visibility = 'visible';
							dojo.byId('ajax_loader_text').style.display = 'none';
							dabei = false;
					}
			});
		}
	}
				
	function change(){
		var a = 1;
		var b = 1;
		for(var i in ids){
			if(document.getElementById(b)){
				if(ids[i]){
					document.getElementById(b).className = 'tableHead';
					a++;
				}
				else{
					document.getElementById(b).className = '';
				}
				b++;
			}
		}
		if(a == b){
			document.getElementById('all').className = 'tableHead';
		}
		else{
			document.getElementById('all').className = '';
		}
	}

	function change_all(){
		var a = 0;
		var b = 0;
		for(var i in ids){
			if(ids[i]){
				a++;
			}
			b++;
		}
		if(a == b){
			var c = 1;
			document.getElementById('all').className = '';
			for(var i in ids){
				ids[i] = false;
				if(document.getElementById(c))
					document.getElementById(c).className = '';
				c++;
			}
		}
		else{
			var c = 1;
			document.getElementById('all').className = 'tableHead';
			for(var i in ids){
				ids[i] = true;
				if(document.getElementById(c))
					document.getElementById(c).className = 'tableHead';
				c++;
			}
		}
	}

	function show_new(){
		if(new_.length > 100){
			dojo.byId('ajax').innerHTML = new_;
		}
		else{
			dojo.byId('ajax').innerHTML = '';
		}
		document.getElementById('new_').className = 'tableHead';
		document.getElementById('all').className = '';
		var c = 1;
		for(var i in ids){
			ids[i] = false;
				if(document.getElementById(c))
					document.getElementById(c).className = '';
			c++;
		}
	}
	img1 = new Image(32, 32);  
	img1.src='http://syndicates-online.de/php/images/ajax-loader-syndicates.gif';
	
						</script>{/literal}
						<table class="tableOutline" cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<table cellpadding="2" cellspacing="1">
										<tr class=tableInner1>
											<td width="120" nowrap onclick="javascript: show_new()" id="new_" style='cursor:pointer' class="tableHead">
												ungelesene ({$UNGELESEN_COUNT})
											</td>
											<td colspan="2" nowrap onclick="javascript: get_news('all')" id="all" style='cursor:pointer'>
												Alle Nachrichten anzeigen
											</td>
										</tr>
								{foreach from=$MENUE_COL item=COL}
										<tr class="tableInner1">
									{foreach from=$COL item=VL}
										{if $VL.name}
											<td nowrap onClick="javascript: get_news('{$VL.kat_id}')" id="{$VL.kat_id}" style='cursor:pointer' title="{$VL.description}">
												{$VL.name} ({if $VL.katcount}{$VL.katcount}{else}0{/if}) <!-- test -->
											</td>
										{else}
											<td width=120 nowrap>
												<!-- leer -->
											</td>
										{/if}
									{/foreach}
										</tr>
								{/foreach}
									</table>
								</td>
							</tr>
						</table>		
					</td>
					{/if}
					<td align="right" class="siteGround">
						Alle Nachrichten markieren: 
						<input type="checkbox" name="delall" id="delall" onclick="checkAll(this,items)">
						<br>
						<br>
						<input class="button" type="submit" value="Markierte Nachrichten löschen">
					</td>
				</tr>
			</table>
			<br>
			<div id=ajax>
			{foreach from=$MESSAGES item=VL}				
				<div>
					<table cellpadding="0" cellspacing="0" border="0" width="600" class="tableOutline">
					<tr>
						<td>
							<table cellpadding="0" cellspacing="1" border="0" width="600">
								<tr>
									<td width="20" valign="top" bgcolor="#000000" style="padding-top:8px;">
										<img src="{$RIPF}{$VL.img}.gif" border="0">
									</td>
									<td width="80" valign="top" class="{if $VL.gelesen}tableInner2{else}tableHead{/if}" align="center" style="padding-top:5px;padding-bottom:5px;">
										<span style="font-size:10px">
											<!-- $show_class_pre -->
											{$VL.realdate}
											<!-- $show_class_after -->
										</span>
										<br>
										<B style="font-size:10px">
											<!-- $show_class_pre -->
											{$VL.realtime}
											<!-- $show_class_after -->
										</B>
									</td>
									<td width="470" class="tableInner1" valign="top" class="ver11w" style="padding-left:20px;padding-top:5px;padding-bottom:5px;">
										{$VL.o_message}
									</td>
									<td width="30" class="tableHead" align="center" valign="middle">
										<input type="checkbox" name="delete{$VL.count}" value="{$VL.unique_id}">
									</td>
								</tr>
							</table> 
						</td>
					</tr>
				</table>
				<br>
				</div>
			{/foreach}
			</div>
			<table align="center" width="585">
				<tr>
					<td align="right">
						<input class="button" type="submit" value="Markierte Nachrichten löschen">
					</td>
				</tr>
			</table>
		</form>
		<div id="ajax_loader_text" style="display:none; width: 66px; top: 50px; padding:20px;" class="tableInner1">
			<img src="http://syndicates-online.de/php/images/ajax-loader-syndicates.gif" title="loading.." border="" />
			<br />
			<br />
			loading..
		</div>
	{else}
		<br>
		<center>Es sind keine Nachrichten vorhanden!</center><br>
		<br>
	{/if}
{/if}

