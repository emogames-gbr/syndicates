{literal}
<script type="text/javascript">
<!--
	var active = new Array();
	var dabei = new Array();
	var duration = 500;
	var preise = new Array();
	var auktionen = new Array();
	var current_price = new Array();
	{/literal}{foreach from=$OWNDATA item=SYN}
	preise[{$SYN.rid}] = {$SYN.number};{/foreach}
	{literal}
	{/literal}{foreach from=$SYNDATA item=SYN}
	auktionen[{$SYN.rid}] = {$SYN.aktien_pool};
	{if $SYN.gebot}
	current_price[{$SYN.rid}] = {$SYN.preis};
	{else}
	current_price[{$SYN.rid}] = {$SYN.aktienkurs};
	{/if}
	{/foreach}
	{literal}
	var show_data = 0;
	var dabei_data = false;
	var duration_data = 500;
	var box = new Array();
	var box_duration = 300;
	var box_wait = 5000;

	var offerids = new Array();
	{/literal}{foreach from=$OFFERDATA item=VL}
	offerids.push({$VL.offer_id});{/foreach}
	{literal}

	var assiids = new Array();
	{/literal}{foreach from=$ASSIDATA item=VL}
	assiids.push({$VL.assi_id});{/foreach}
	{literal}
	
	img1 = new Image(32, 32);  
	img1.src = 'http://syndicates-online.de/php/images/ajax-loader-syndicates.gif';
	
	function show_hide(id) {
		if(dabei[id] == false || dabei[id] == undefined){
			if(!active[id]){
				dabei[id] = true;
				active[id] = true;
				document.getElementById(id+'_tr').style.display = '';
				dojo.fx.wipeIn({node: id+"_form",duration: duration}).play();
				window.setTimeout("document.getElementById('"+id+"_img').src = {/literal}'{$RIPF}_for_up.gif';{literal}", duration);
				window.setTimeout("document.getElementById('"+id+"_img2').src = {/literal}'{$RIPF}_for_up.gif';{literal}", duration);
				window.setTimeout("dabei['"+id+"'] = false;", duration);
			}
			else if(active[id] == true ){
				dabei[id] = true;
				active[id] = false;
				dojo.fx.wipeOut({node: id+"_form",duration: duration}).play();
				window.setTimeout("document.getElementById('"+id+"_tr').style.display = 'none';", duration);
				window.setTimeout("document.getElementById('"+id+"_img').src = {/literal}'{$RIPF}_for_down.gif';{literal}", duration);
				window.setTimeout("document.getElementById('"+id+"_img2').src = {/literal}'{$RIPF}_for_down.gif';{literal}", duration);
				window.setTimeout("dabei['"+id+"'] = false;", duration);
			}
		}
	}
	
	function change_max(a, b){
		if(!a && !a){
			document.getElementById('number_offer').value = preise[document.getElementById('rid_offer').value];
		}
		else if(a == 'auktion'){
			document.getElementById('number_auktion').value = auktionen[document.getElementById('rid_auktion').value];
		}
		else{
			document.getElementById(b).value = a;
		}
	}
	
	function changeSellPrice() {
		var syn = document.getElementById('rid_offer').value;
		document.getElementById('price_offer').value = current_price[syn] - 1;
		document.getElementById('price_offer').focus();
		document.getElementById('price_offer').select();
	}
	
	function changeBuyPrice() {
		var syn = document.getElementById('rid_assi').value;
		document.getElementById('price_assi').value = current_price[syn] - 1;
		document.getElementById('price_assi').focus();
		document.getElementById('price_assi').select();
	}
		
	function show_hide_data(id){
		if(dabei_data == false){
			dabei_data = true;
			if(show_data > 0 && id != show_data){
				dojo.fx.wipeOut({node: show_data+"_data_div",duration: duration_data}).play();
				window.setTimeout("document.getElementById('"+show_data+"_data_tr').style.display = 'none';", duration_data);
				window.setTimeout("document.getElementById('plus_"+show_data+"').innerHTML = '+';", duration_data);
				show_data = false;
				
				document.getElementById(id+'_data_tr').style.display = '';
				dojo.fx.wipeIn({node: id+"_data_div",duration: duration_data}).play();
				document.getElementById('plus_'+id).innerHTML = '-';
				show_data = id;
				
				window.setTimeout("dabei_data = false;", duration_data);
			}
			else if(id == show_data){
				dojo.fx.wipeOut({node: id+"_data_div",duration: duration_data}).play();
				window.setTimeout("document.getElementById('"+id+"_data_tr').style.display = 'none';", duration_data);
				window.setTimeout("document.getElementById('plus_"+id+"').innerHTML = '+';", duration_data);
				window.setTimeout("dabei_data = false;", duration_data);
				show_data = false;
			}
			else{
				document.getElementById(id+'_data_tr').style.display = '';
				dojo.fx.wipeIn({node: id+"_data_div",duration: duration_data}).play();
				document.getElementById('plus_'+id).innerHTML = '-';
				window.setTimeout("dabei_data = false;", duration_data);
				show_data = id;
			}
		}
	}
	
	function show_hide_box(id, action){
		if(action == 'in' && box['div_'+id]){
			window.clearTimeout(box['div_'+id]);
		}
		if(action == 'in' && box['tr_'+id]){
			window.clearTimeout(box['tr_'+id]);
		}
		if(action == 'in' && box['row_'+id]){
			window.clearTimeout(box['row_'+id]);
		}
		if(action == 'in' && box['id_'+id]){
			window.clearTimeout(box['id_'+id]);
		}
		
		if(!box[id] && action == 'in'){
			dojo.attr(id+'_box_row', 'rowspan', 2);
			document.getElementById(id+'_box_tr').style.display = '';
			dojo.fx.wipeIn({node: id+'_box_div',duration: box_duration}).play();
			box[id] = true;
		}
		
		else if(box[id] && action == 'out'){
			box['div_'+id] = window.setTimeout("dojo.fx.wipeOut({node: '"+id+"_box_div',duration: "+box_duration+"}).play();", box_wait);
			box['row_'+id] = window.setTimeout("dojo.attr('"+id+"_box_row', 'rowspan', 0);", box_wait + box_duration);
			box['tr_'+id] = window.setTimeout("document.getElementById('"+id+"_box_tr').style.display = 'none';", box_wait + box_duration);
			box['id_'+id] = window.setTimeout("box["+id+"] = false;", box_wait + box_duration);
		}
	}
		
	
	dojo.addOnLoad(function(){
		// Angebote - Formular
		var offer_load;
		var offer_load2;
		var offer_head;
		var offer_head_css;
		if(dojo.byId('offer_submit')){
			dojo.connect(dojo.byId('offer_submit'), 'onclick', function(event){
				dojo.stopEvent(event);
				if(dojo.byId('offer_head').innerHTML.search(/Aktien verkaufen/) != -1){
					offer_head = dojo.byId('offer_head').innerHTML;
					offer_head_css = dojo.byId('offer_head_css').className;
				}
				if(offer_load){
					window.clearTimeout(offer_load);
				}
				if(offer_load2){
					window.clearTimeout(offer_load2);
				}
				dojo.byId('offer_head').innerHTML = '<img src=\"'+img1.src+'\" title=\"loading..\" border=\"\" height=\"16px\" />';
				dojo.byId('offer_submit').disabled = true;
				dojo.xhrPost({
					form: 'offer_form_',
					handleAs: "json",
					content: { "js_action": 1 },
					load: function(data){
						dojo.byId('offer_head').innerHTML = data.msg;
						if(data.type == 'error'){
							dojo.byId('offer_head_css').className = 'f';
						}
						else if(data.type == 'msg'){
							dojo.byId('offer_head_css').className = 's';
						}
						dojo.byId('offer_submit').disabled = false;
						offer_load = window.setTimeout("document.getElementById('offer_head').innerHTML = '"+offer_head+"';", 3500);
						offer_load2 = window.setTimeout("document.getElementById('offer_head_css').className = '"+offer_head_css+"';", 3500);
						if(data.table){
							offerids.push(data.table.id);
							dojo.place(data.table, dojo.byId('offer_table'), 'after');
						}
					},
					error: function(data){
						alert(data);
					}
				});
			});
		}
		
		
		// Kaufgebote - Formular
		var assi_load;
		var assi_load2;
		var assi_head;
		var assi_head_css;
		if(dojo.byId('assi_submit')){
			dojo.connect(dojo.byId('assi_submit'), 'onclick', function(event){
				dojo.stopEvent(event);
				if(dojo.byId('assi_head').innerHTML.search(/Kaufangebot abgeben/) != -1){
					assi_head = dojo.byId('assi_head').innerHTML;
					assi_head_css = dojo.byId('assi_head_css').className;
				}
				if(assi_load){
					window.clearTimeout(assi_load);
				}
				if(assi_load2){
					window.clearTimeout(assi_load2);
				}
				dojo.byId('assi_head').innerHTML = '<img src=\"'+img1.src+'\" title=\"loading..\" border=\"\" height=\"16px\" />';
				dojo.byId('assi_submit').disabled = true;
				dojo.xhrPost({
					form: 'assi_form_',
					handleAs: "json",
					content: { "js_action": 1 },
					load: function(data){
						dojo.byId('assi_head').innerHTML = data.msg;
						if(data.type == 'error'){
							dojo.byId('assi_head_css').className = 'f';
						}
						else if(data.type == 'msg'){
							dojo.byId('assi_head_css').className = 's';
						}
						dojo.byId('assi_submit').disabled = false;
						assi_load = window.setTimeout("document.getElementById('assi_head').innerHTML = '"+assi_head+"';", 3500);
						assi_load2 = window.setTimeout("document.getElementById('assi_head_css').className = '"+assi_head_css+"';", 3500);
						if(data.table){
							assiids.push(data.table.id);
							dojo.place(data.table, dojo.byId('assi_table'), 'after');
						}
					},
					error: function(data){
						alert(data);
					}
				});
			});
		}
	});
	
	function zurueck(id, type){
		dojo.byId(type+'_submit_'+id).disabled = true;
		dojo.xhrPost({
			handleAs: "json",
			url: "boerse.php",
			content: { "js_action": 1, "offer_id": id, "assi_id": id, "action": type+"_back"},
			load: function(data){
				if(data.msg == 'ok'){
					document.getElementById(type+'_'+id).style.display = 'none';
				}
				else{
					dojo.byId(type+'_submit_'+id).disabled = false;
				}
			}
		});
	}

	function zurueck_all(type){
		dojo.byId(type+'_submit_all').disabled = true;
		dojo.xhrPost({
			handleAs: "json",
			url: "boerse.php",
			content: { "js_action": 1, "action": type+"_back_all"},
			load: function(data){
				if(data.msg == 'ok'){
					document.getElementById(type+'_back_all').style.display = 'none';
					if (type == 'offer') {
						for( var k=0; k < offerids.length; k++ ) {
							//document.getElementById(type+'_'+offerids[k]).style.display = 'none';
							$('tr[type="offer"]').hide();
						}
					} else {
						text = "TextyText: ";
						for( var k=0; k < assiids.length; k++ ) {
							text += " " + assiids[k] + ", "; 
							//document.getElementById(type+'_'+assiids[k]).style.display = 'none';
							$('tr[type="assi"]').hide();
						}
						dojo.byId('assi_head').innerHtml = text;
					}
				}
				else{
					dojo.byId(type+'_submit_all').disabled = false;
				}
			}
		});
	}	

	function wipein(id) {
		var fadeArgs = {
			node: id,
			duration: 10
		};
		dojo.fx.wipeIn(fadeArgs).play();
	}
	
	function wipeout(id) {
		var fadeArgs = {
			node: id,
			duration: 500
		};
		dojo.fx.wipeOut(fadeArgs).play();
	}
-->
</script>
{/literal}