{literal}
<script type="text/javascript">
<!--
//Syndicates Angriffsrechner by dragon12
var target_id = {/literal}{if $TARGET}{$TARGETID}{else}false{/if}{literal};
var target_name = {/literal}{if $TARGET}'{$TARGET_NAME}'{else}false{/if}{literal};

var g_milbau = false;
var g_milaway = false;
var g_own_milbau = false;
var g_own_milaway = false;
var last_delay = 0;
var spy_delay_units = 0;
var spy_delay_gebs = 0;
var calcAtter_log;
var calcDeffer_log;
var readSpy_logs = [];

//test heimkehr:[[1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1], [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]];

function getFosLevel(type, fos) {
	return parseInt($('#'+type+'_fos_'+fos+' option:selected').val());
}

function hasMonu(type, monu) {
	if($('#'+type+'_monu').val() == monu)
		return 1;
	return 0;
}

function submit_attack_form() {
	$('#angr_rines_att').val(removeTrennzeichen($('#angr_rines_att').val()));
	$('#angr_ranger_att').val(removeTrennzeichen($('#angr_ranger_att').val()));
	$('#angr_buc_att').val(removeTrennzeichen($('#angr_buc_att').val()));
	$('#angr_auc_att').val(removeTrennzeichen($('#angr_auc_att').val()));
	$('#angr_huc_att').val(removeTrennzeichen($('#angr_huc_att').val()));
	$('#form_synarmeesend').val($('#angr_synarmeesend').prop('checked')?'on':'off');
	document.attack_form.submit();
}

function reset(type, part) {//TODO
	if(part== 'mil') {
		$('#'+type+'_rines_da').text(0);
		$('#'+type+'_ranger_da').text(0);
		$('#'+type+'_buc_da').text(0);
		$('#'+type+'_auc_da').text(0);
		$('#'+type+'_huc_da').text(0);
		if(type=='angr') {
			$('#'+type+'_rines_att').text(0);
			$('#'+type+'_ranger_att').text(0);
			$('#'+type+'_buc_att').text(0);
			$('#'+type+'_auc_att').text(0);
			$('#'+type+'_huc_att').text(0);
			{/literal}{if $ROLE}{literal}
				g_milbau = false;
				g_milaway = false;
			{/literal}{else}{literal}
			} else {
				g_own_milbau = false;
				g_own_milaway = false;
			{/literal}{/if}{literal}
		}
		
	} else if (part == 'gebs') {
		$('#'+type+'_rines_att').text(0);
	} else if (part == 'fos') {
	
	}
	
	update();
}

function readHead(spy) { //returns several of these: [spy_type(0), name(1), nw(2), land(3), lastIndex(4), datetime(5), tickspassed(6)]
	//var re = /Spionageaktion\s+([\w\säüöß]+)\sgegen\s+(([\w\s-\._\?äüöß&]*)\s+\(#(\d{1,3})\))\s*-\s+(\d{1,3}(\.\d{3})*)Nw,\s+(\d{1,3}(\.\d{3})*)ha\sam\s((\d{2})\.(\d{2})\.(\d{2})\s-\s(\d{2}):(\d{2})\sUhr)\s+(MESZ|MEZ){0,1}/g;
	var re = /Spionageaktion\s+([\w\säüöÄÖÜß]+)\sgegen\s+(([\w\s-\._\?äüöÄÜÖß&]*)\s+\(#(\d{1,3})\))\s*-\s+(\d{1,3}(\.\d{3})*)Nw,\s+(\d{1,3}(\.\d{3})*)ha\sam\s((\d{2})\.(\d{2})\.(\d{2})\s-\s(\d{2}):(\d{2})\sUhr)\s+(MESZ|MEZ){0,1}/g;
	var match;
	var res = [];
	var i = 0;
	var current_t = {/literal}{$CURRENTTIME}{literal}; //now.getTime()/1000;
	var roundtime = {/literal}{$ROUNDTIME}{literal};
	roundtime = roundtime*60; //in hours
	var roundstarttime = {/literal}{$ROUNDSTARTTIME}{literal};
	var spytime;
	var isCEST;
	while(match = re.exec(spy)) {
		isCEST = match[15]?(match[15] == 'MESZ'):true;
		spytime = (new Date(Date.UTC('20'+match[12], parseInt('1'+match[11])-101, match[10], match[13], match[14]))).getTime()/1000-3600-(isCEST?3600:0);
		spytime = Math.floor((current_t-roundstarttime)/roundtime) - Math.floor((spytime-roundstarttime)/roundtime);
		res[i] = [match[1], match[2], match[5].replace(/\./g, ''), match[7].replace(/\./g, ''), re.lastIndex, match[9], spytime];
		if(target_name && target_name != match[3]) {
			alert('Der Name dieses Spielers ('+match[3]+') stimmt nicht mit dem Namen ihres Angriffsziel ('+target_name+') überein.\nÜberprüfen sie bitte ihre Berichte oder fahren sie fort, falls das gewollt ist.');
			target_name = match[3];
		}
		$('#form_target_rid').val(match[4]);
		$.ajax({
			url: 'agr.php',
			data: {tarname:escape(match[3])},
			success:function(data) {
				var json_data = $.parseJSON(data);
				if(json_data['isWar'])
					$('#gen_iswar').attr('checked', true);
				if(json_data['monu'] && $('#{/literal}{if $ROLE}angr{else}vert{/if}{literal}_monu option[value="'+json_data['monu']+'"]').length > 0) {
					$('#{/literal}{if $ROLE}angr{else}vert{/if}{literal}_monu').val(json_data['monu']);
				} else {
					$('#{/literal}{if $ROLE}angr{else}vert{/if}{literal}_monu').val('keins');
				}
				target_id = json_data['id'];
				if(isNaN(target_id)) {
					alert('ACHTUG:\nDer angegegebene Konzern existiert nicht! Es könnte sich um einen alten Spionagebericht handeln');
				} else {
					$('#form_target_id').val(target_id);
					$('#form_button').removeAttr('disabled');
				}
				update();
			}
		});
		i++;
	}
	return res;
}

function readFos(spy, start) {
	spy = spy.replace(/Syndicate\sSpy\sDefense\sNetwork\s*Stufe/, ' '); //sonst wird issdn als dn gelesen!
	var fos = {};
	var name;
	var re;
	{/literal}{if $ROLE}{literal}
		{/literal}{foreach from=$ANGR.forschungen item=FOS}{literal}
			name = '{/literal}{$FOS.name}{literal}'.replace(/(&amp;|\s)/g, function($0) {return ($0 == '&amp;')?'&':'\\s';});
			name += '\\s*Stufe\\s(\\d)';
			re = RegExp(name);
			re.lastIndex = start;
			fos['{/literal}{$FOS.sname}{literal}'] = re.exec(spy);
		{/literal}{/foreach}{literal}
	{/literal}{else}{literal}
		{/literal}{foreach from=$VERT.forschungen item=FOS}{literal}
			name = '{/literal}{$FOS.name}{literal}'.replace(/(&amp;|\s)/g, function($0) {return ($0 == '&amp;')?'&':'\\s';});
			name += '\\s*Stufe\\s(\\d)';
			re = RegExp(name);
			re.lastIndex = start;
			fos['{/literal}{$FOS.sname}{literal}'] = re.exec(spy);
		{/literal}{/foreach}{literal}
	{/literal}{/if}{literal}
	for(var key in fos)
		(fos[key]==null)?fos[key]=0:fos[key] = fos[key][1];
	return fos;
}

function readUnits(spy, start, isMil, ticksDelay) { //returns [frak(0), rines(1), ranger(2), buc(3), auc(4), huc(5), synrines(6), synranger(7), milbau(8), milaway(9)]
	var re = /Marine\s+(\d{1,3}(\.\d{3})*)\s+Ranger\s+(\d{1,3}(\.\d{3})*)\s+(\w+[\w\s]*?)\s+(\d{1,3}(\.\d{3})*)\s+(\w+[\w\s]*?)\s+(\d{1,3}(\.\d{3})*)\s+(\w+[\w\s]*?)\s+(\d{1,3}(\.\d{3})*)/g;
	re.lastIndex = start;
	var match = re.exec(spy);
	var frak = 'none';
	switch(match[11]) {
		case 'Titan': frak = 'bf'; break;
		case 'Stealth Bomber': frak = 'sl'; break;
		case 'Behemoth': frak = 'nof'; break;
		case 'EMP Cannon': frak = 'neb'; break;
		case 'Sentinel': frak = 'uic'; break;
	}
	if(isMil) {
		//reset the ap vp pb in case it was set by the konzi
		{/literal}{if $ROLE}$('#angr_pb_ap').removeAttr('checked'){else}$('#vert_pb_vp').removeAttr('checked'){/if}{literal};
	
		re = /Militärausbildung[\s#\d]+?Marine\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*Ranger\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)/;
		re.lastIndex = start;
		var match3 = re.exec(spy);
		var milbau = [[], [], [], [], []];
		var unit;
		if(match3 != null) {
			for(var i=1;i<match3.length;i++) {
				unit = Math.floor((i-1)/20);
				milbau[unit][i-1-unit*20] = (match3[i]=='-')?0:match3[i];
			}
		} else {milbau = false;}
	
		re = /Heimkehrendes\sMilitär[\s#\d]+?Marine\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*Ranger\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(?:\w+[\w\s]*?)(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)\s*(-|\d{1,3}(?:\.\d{3})*)/;
		re.lastIndex = start;
		match3 = re.exec(spy);
		var milaway = [[], [], [], [], []];
		var unit;
		if(match3 != null) {
			for(var i=1;i<match3.length;i++) {
				unit = Math.floor((i-1)/20);
				milaway[unit][i-1-unit*20] = (match3[i]=='-')?0:match3[i];
			}
		} else
			milaway = false;
	} else {
		{/literal}{if $ROLE}$('#angr_pb_ap').attr('checked', ''){else}$('#vert_pb_vp').attr('checked', ''){/if}{literal};
	}
	re = /Syndikatsarmee\s+Ranger\s+(\d{1,3}(\.\d{3})*)\s+Marines\s+(\d{1,3}(\.\d{3})*)/g;
	var match2 = re.exec(spy);
	if(!match2) {
		//alert('WARNUNG:\nDer von ihnen eingefügte '+(isMil?'Militärspion':'Konzernspion')+' enthält keine Syndikatsarmeedaten.\nBitte kopieren sie den ganzen Bericht, um die Syndikatsarmee mit einzulesen.');
		match2 = [0, 0, 0, 0];
	}
	return [frak, match[1], match[3], match[6], match[9], match[12], (isMil?match2[3]:addTrennzeichen(removeTrennzeichen(match2[3])/0.85)), (isMil?match2[1]:addTrennzeichen(removeTrennzeichen(match2[1])/0.85)), milbau, milaway];
}

function readPbs(spy, start) {
	var pbs = {};
	var re;
	{/literal}{foreach from=$PBS item=PB key=KEY}{literal}
		re = /{/literal}{$PB}{literal}/;
		re.lastIndex = start;
		pbs['{/literal}{$KEY}{literal}'] = spy.match(re);
	{/literal}{/foreach}{literal}
	return pbs;
}

function readAtts(berichte) {
	var re = /((?:GESIEGT)|(?:NICHT\sGEWINNEN))\s!\s+[\s\S]+?(?:(?:sind\swie\folgt)|(?:Verluste\szugefügt)):/g;
	var match;
	while(match = re.exec(berichte)) {
		var start_index = re.lastIndex - 25;
		var vert_losses = readDefVerluste(berichte, start_index);
		var angr_losses = readAttVerluste(berichte, start_index);
		if(match[1] == 'GESIEGT') {
			var landgain = readAttResult(berichte, start_index);
		} else {
			
		}
		var player = '{/literal}{if $ROLE}{literal}angr{/literal}{else}{literal}vert{/literal}{/if}{literal}';
		if(landgain) {
			$('#'+player+'_land_abs').val(addTrennzeichen(removeTrennzeichen($('#'+player+'_land_abs').val())-landgain));
			
			var da;
			da = Math.max(removeTrennzeichen($('#'+player+'_rines_da').val())-vert_losses[0], 0);
			$('#'+player+'_rines_da').val(addTrennzeichen(da));
			da = Math.max(removeTrennzeichen($('#'+player+'_ranger_da').val())-vert_losses[1], 0);
			$('#'+player+'_ranger_da').val(addTrennzeichen(da));
			da = Math.max(removeTrennzeichen($('#'+player+'_buc_da').val())-vert_losses[2], 0);
			$('#'+player+'_buc_da').val(addTrennzeichen(da));
			da = Math.max(removeTrennzeichen($('#'+player+'_auc_da').val())-vert_losses[3], 0);
			$('#'+player+'_auc_da').val(addTrennzeichen(da));
			da = Math.max(removeTrennzeichen($('#'+player+'_huc_da').val())-vert_losses[4], 0);
			$('#'+player+'_huc_da').val(addTrennzeichen(da));
		}
	}
}

function typeToIndex(unit_type) {
	var types = {offspecs:0, defspecs:1, elites:2, elites2:3, techs:4};
	return types[unit_type];
}

function readDefVerluste(bericht, start) {
	var re = /(?:(?:Gegners\s+sind\s+wie\s+folgt)|(?:Verluste\s+zugefügt)):\s+(keine|(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1})/g;
	re.lastIndex = start;
	var match = re.exec(bericht);
	var ret = [0, 0, 0, 0, 0];
	if(match[1] != 'keine') {
		for(var i=2;i<11;i+=2) {
			ret[typeToIndex(getUnitType($.trim(match[i+1])))]=match[i];
		}
	}
	return ret;
}

function readAttVerluste(bericht, start) {
	var re = /Ihre\s+Verluste:\s+(keine|(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1}(?:(\d+|\d{1,3}(?:\.\d{3})*)\s*([A-Za-z\s]+)){0,1})/g;
	re.lastIndex = start;
	var match = re.exec(bericht);
	var ret = [0, 0, 0, 0, 0];
	if(match[1] != 'keine') {
		for(var i=2;i<11;i+=2) {
			ret[typeToIndex(getUnitType($.trim(match[i+1])))]=match[i];
		}
	}
	return ret;
}

function readAttResult(bericht, start) {
	var re = /Sie\s+haben[\s\wüäöß]+?(?:(?:(\d+|\d{1,3}(?:\.\d{3})*)\s+(?:unbebautes\s+){0,1}Land\s+eingenommen)|(?:folgende\s+(\d+|\d{1,3}(?:\.\d{3})*)\s+Gebäude\s+vernichtet:)|(?:Gegner\s+besiegt.)(?:(?:)))/g;
	re.lastIndex = start;
	var match = re.exec(bericht);
	if(match) {
		var landgain = match[1];
		var dest_gebs = match[2];
	} else {console.log('ERROR in readAttResult: kein match gefunden');}
	return landgain;
}

function readAttHead(bericht, start) {
	
}

function readAttGain(bericht, start) {
	
}

function readGebs(spy, start) {//[forts(0), aussis(1), werkst(2), radar(3), inbau(4)]
	var gebs = [];
	gebs[0] = /Forts\s*(\d{1,3}(\.\d{3})*)/g;
	gebs[1] = /Außenposten\s*(\d{1,3}(\.\d{3})*)/g;
	gebs[2] = /Werkstätten\s*(\d{1,3}(\.\d{3})*)/g;
	gebs[3] = /Radaranlagen\s*(\d{1,3}(\.\d{3})*)/g;
	gebs[4] = /in\sbau\s*(\d{1,3}(\.\d{3})*)/g;
	for(var i=0;i<5;i++) {
		gebs[i].lastIndex=start;
		gebs[i] = gebs[i].exec(spy);
		(gebs[i]==null)?gebs[i]=0:gebs[i] = gebs[i][1]
	};
	return gebs;
}

function readSpies(spies) {

	//spies = spies.replace(/[äöüß]/gi,function($0){return umlauts[$0]});
	var heads = readHead(spies);
	for(var key in heads) {
		readSpy(spies, heads[key]);
		
	}
	readAtts(spies);
	update();
}

function update_scandate(scan_type, date, ticks) {
	$('#scandate_'+scan_type).html(' (vom '+date+', <i>vor&nbsp;'+ticks+'&nbsp;Ticks</i>)');
}

function readSpy(spy, head) {
	
	var update_units = false;
	var update_gebs = false;
	var update_fos = false;
	var update_pbs = false;
	var add_incoming = false;
	var start = head[4];
	
	if(head[0] == 'Konzernspion') {
		var units = readUnits(spy, start, false);
		var pbs = readPbs(spy, start);
		update_pbs = true;
		update_units = true;
		update_scandate('units', head[5], head[6]);
		spy_delay_units = head[6];
		$('#gen_spytype').val(1);
	} else if(head[0] == 'Genauer Militärspion') {
		var units = readUnits(spy, start, true);
		var pbs = readPbs(spy, start);
		spy_delay_units = head[6];
		g_milbau = units[8];
		g_milaway = units[9];
		{/literal}{if $ROLE}{literal}
			add_incoming = true;
			var units_add = [0,0,0,0,0];
			var delay = getDelay(true);
			last_delay = delay;
			delay = delay + head[6];
			for(var i=0;i<delay;i++) {
				units_add[0] += removeTrennzeichen(units[8]?units[8][0][i]:0) + removeTrennzeichen(units[9]?units[9][0][i]:0);
				units_add[1] += removeTrennzeichen(units[8]?units[8][1][i]:0) + removeTrennzeichen(units[9]?units[9][1][i]:0);
				units_add[2] += removeTrennzeichen(units[8]?units[8][2][i]:0) + removeTrennzeichen(units[9]?units[9][2][i]:0);
				units_add[3] += removeTrennzeichen(units[8]?units[8][3][i]:0) + removeTrennzeichen(units[9]?units[9][3][i]:0);
				units_add[4] += removeTrennzeichen(units[8]?units[8][4][i]:0) + removeTrennzeichen(units[9]?units[9][4][i]:0);
			}
		{/literal}{/if}{literal}
		update_pbs = true;
		update_units = true;
		update_scandate('units', head[5], head[6]);
		$('#gen_spytype').val(0);
	} else if(head[0] == 'Gebäudespion') {
		var gebs = readGebs(spy, start);
		spy_delay_gebs = head[6];
		update_gebs = true;
		update_scandate('gebs', head[5], head[6]);
	} else if(head[0] == 'Forschungsspion') {
		var fos = readFos(spy, start);
		update_fos = true;
		update_scandate('fos', head[5], head[6]);
	}
	
	var player = '{/literal}{if $ROLE}{literal}angr{/literal}{else}{literal}vert{/literal}{/if}{literal}';
	$('#'+player+'_land_abs').val(head[3]);
	if(update_units) {
		$('#'+player+'_frak_'+units[0]).click();
		$('#'+player+'_rines_da').val(add_incoming?addTrennzeichen(removeTrennzeichen(units[1])+units_add[0]):units[1]);
		$('#'+player+'_ranger_da').val(add_incoming?addTrennzeichen(removeTrennzeichen(units[2])+units_add[1]):units[2]);
		$('#'+player+'_buc_da').val(add_incoming?addTrennzeichen(removeTrennzeichen(units[3])+units_add[2]):units[3]);
		$('#'+player+'_auc_da').val(add_incoming?addTrennzeichen(removeTrennzeichen(units[4])+units_add[3]):units[4]);
		$('#'+player+'_huc_da').val(add_incoming?addTrennzeichen(removeTrennzeichen(units[5])+units_add[4]):units[5]);
		if(units[6] > 0)
			$('#'+player+'_synrines').val(units[6]);
		if(units[7] > 0)
			$('#'+player+'_synranger').val(units[7]);
	}
	if(update_pbs) {
		if(pbs['ap'] && player == 'angr')
			$('#angr_pb_ap').attr('checked', '');
		if(pbs['vp'])
			$('#'+player+'_pb_vp').attr('checked', '');
		if(pbs['verluste'])
			$('#'+player+'_pb_verluste').attr('checked', '');
		if(pbs['landloss'] && player == 'vert')
			$('#vert_pb_land').attr('checked', '');
		if(pbs['landgain'] && player == 'angr')
			$('#angr_pb_land').attr('checked', '');
		if(pbs['heimkehr'] && player == 'angr')
			$('#angr_pb_heimkehr').attr('checked', '');
	}
	if(update_gebs) {
		$('#'+player+'_forts_abs').val(gebs[0]);
		(player=='angr')?$('#angr_aussis_abs').val(gebs[1]):1;
		(getFrak(player)=='nof')?$('#'+player+'_specgeb_abs').val(gebs[2]):1;
		(getFrak(player)=='bf')?$('#'+player+'_specgeb_abs').val(gebs[3]):1;
		(player=='vert')?$('#vert_bau_abs').val(gebs[4]):1;
	}
	if(update_fos) {
		{/literal}{if $ROLE}{literal}
			{/literal}{foreach from=$ANGR.forschungen item=FOS}{literal}
				$('#angr_fos_{/literal}{$FOS.sname}{literal}').val(fos['{/literal}{$FOS.sname}{literal}']);
			{/literal}{/foreach}{literal}
		{/literal}{else}{literal}
			{/literal}{foreach from=$VERT.forschungen item=FOS}{literal}
				$('#vert_fos_{/literal}{$FOS.sname}{literal}').val(fos['{/literal}{$FOS.sname}{literal}']);
			{/literal}{/foreach}{literal}
		{/literal}{/if}{literal}
			
	}
}

function isWar() {
	if($('#gen_iswar').prop('checked')){
		return 1;
	}
	return 0;
}

function isDirRR() {
	if($('#gen_dir_rr').prop('checked')){
		return 1;
	}
	return 0;
}

function getPb(type, pb) {
	var pb_level = 0;
	if($('#'+type+'_pb_'+pb).prop('checked')) {
		pb_level = 1;
	}
	return pb_level;
}

function addTrennzeichen(number) {
	if(isNaN(number))
		number = 0;
	number = Math.floor(number+0.00002);
	if(!number.substr) {
		number = number.toString();
	}
	var parts = [];
	var numstring = '';
	var i2 = number.length%3;
	if(i2 != 0) {
		parts[0] =  number.substr(0, number.length%3)
	}
	for(var i = i2; i < number.length; i += 3) {
		parts[i] = number.substr(i, 3);
	}
	if(parts.length > 0) {
		numstring = parts[0];
		if (i2 == 0) {
			i2 = 3;
		}
	}
	for(var i=i2; i < parts.length; i+=3) {
		numstring += '.'+parts[i];
	}
	return numstring;
}

function removeTrennzeichen(numberstring) {
	if(!numberstring)
		numberstring = '0';
	if(!numberstring.substr)
		numberstring = numberstring.toString();
	numberstring = numberstring.replace(/\./g, '');
	if(numberstring.indexOf(',') != -1)
		return parseFloat(numberstring.replace(/,/g, '.'));
	return parseInt(numberstring);
}

function vert_fosMax() {
	$('#vert_fos_bdt').val(3);
	$('#vert_fos_flex').val(1);
	$('#vert_fos_rmt').val(3);
	$('#vert_fos_sat').val(3);
	$('#vert_fos_dn').val(3);
	$('#vert_fos_ods').val(3);
	$('#vert_fos_fow').val(1);
	
	update();
}
function angr_fosMax() {
	$('#angr_fos_bdt').val(3);
	$('#angr_fos_flex').val(1);
	$('#angr_fos_rmt').val(3);
	$('#angr_fos_sat').val(3);
	$('#angr_fos_dn').val(3);
	$('#angr_fos_ods').val(3);
	
	$('#angr_fos_ra').val(1);
	$('#angr_fos_iwt').val(3);
	$('#angr_fos_bot').val(3);
	
	update();
}

function getApVp(name) {
	switch(name) {
	{/literal}{foreach from=$UNIT_VALUES item=VAL}{literal}
		case '{/literal}{$VAL.name}{literal}':
			return [{/literal}{$VAL.op}{literal}, {/literal}{$VAL.dp}{literal}];
	{/literal}{/foreach}{literal}
	}
}

function getUnitType(name) {
	switch(name) {
	{/literal}{foreach from=$UNIT_VALUES item=VAL}{literal}
		case '{/literal}{$VAL.name}{literal}':
			return '{/literal}{$VAL.type}{literal}';
	{/literal}{/foreach}{literal}
	}
}

function getUnitValues(type) {
	var unitApVp = [];
	//[0][0] = rine ap
	//[0][1] = rine vp etc
	unitApVp[0] = getApVp('Marine');
	unitApVp[1] = getApVp('Ranger');
	unitApVp[0][0] = unitApVp[0][0] + (getFosLevel(type, 'rmt') == 3 ? 4 : getFosLevel(type, 'rmt'));
	unitApVp[0][1] = unitApVp[0][1] + (getFosLevel(type, 'rmt') == 3 ? 4 : getFosLevel(type, 'rmt')) + getFosLevel(type, 'flex')*8;
	unitApVp[1][0] = unitApVp[1][0] + (getFosLevel(type, 'rmt') == 3 ? 4 : getFosLevel(type, 'rmt')) + getFosLevel(type, 'flex')*8;
	unitApVp[1][1] = unitApVp[1][1] + (getFosLevel(type, 'rmt') == 3 ? 4 : getFosLevel(type, 'rmt'));
	unitApVp[2] = getApVp($('#'+type+'_buc_name').text());
	unitApVp[3] = getApVp($('#'+type+'_auc_name').text());
	unitApVp[4] = getApVp($('#'+type+'_huc_name').text());
	
	if(getFosLevel(type, 'fow') == 1) {
		for(var i=0;i<5;i++) {
			if(!(getFrak(type) == 'nof' && i == 2))
				unitApVp[i][1] += 2;
		}
	}
	return unitApVp;
}

function getGebPerc(type, geb) {
	return removeTrennzeichen($('#'+type+'_'+geb+'_abs').val())/getLand(type)*100;
}

function getLand(type) {
	return removeTrennzeichen($('#'+type+'_land_abs').val());
}

function getSmallkonzMult(angr_land, vert_land) {

}

function getLandgain() {
	var angr_land = getLand('angr');
	var vert_land = getLand('vert');
	var war = isWar();
	var gain = Math.pow(vert_land, 2.5)/Math.pow(angr_land, 1.5)*0.1
	if(isDirRR()) {
		war = 1;
	} else {
		gain = gain*((vert_land<angr_land)?vert_land/angr_land:1)*((vert_land<angr_land*0.8)?Math.pow(vert_land*1.25/angr_land, 1.5):1);
	}
	gain *= 1-getFosLevel('vert','fow')*0.2-getFosLevel('vert','ods')*0.1-getPb('vert', 'land')*{/literal}{$CONST.pblandloss}{literal}+getPb('angr', 'land')*{/literal}{$CONST.pblandgain}{literal}+getFosLevel('angr', 'ra')*0.2; //TODO: constant
	if(gain > vert_land*0.2)
		gain = vert_land*0.2;
	
	gain *= getBashschutzFactor();
	if (vert_land-gain < 400)
		gain = vert_land - 400;
	return gain;
}

function getBashschutzFactor() {
	var war = isWar();
	if(isDirRR())
		war = 1;
	return 1-$('#gen_bashschutz').val()*0.25+war*0.25;
}

function getUnitAmount(type, unit) {
	if(unit == 'all') {
		return getUnitAmount(type, 'rines') + getUnitAmount(type, 'ranger') + getUnitAmount(type, 'buc') + getUnitAmount(type, 'auc') + getUnitAmount(type, 'huc');
	}
	var factor = 1-((type=={/literal}{if $ROLE}'angr'{else}'vert'{/if}{literal})?(parseInt($('#gen_spytype').val())*(0.15+((getFrak(type)=='sl' && unit=='huc')?0.15:0))):0);
	return Math.ceil(removeTrennzeichen($('#'+type+'_'+unit+'_da').val())/factor+((type=='vert' && getDelay(true, 'units')>0)?removeTrennzeichen($('#vert_'+unit+'_add').text()):0));
}

function getOffUnitAmount(unit) {
	var factor = 1-{/literal}{if $ROLE}{literal}parseInt($('#gen_spytype').val())*(0.15+((getFrak('angr')=='sl' && unit=='huc')?0.15:0)){/literal}{else}0{/if}{literal};
	return Math.ceil(removeTrennzeichen($('#angr_'+unit+'_att').val())/factor);
}

function getAtterUnits(abs) {
	var units = [[], []];

	//0 = att
	//1 = def
	units[0][0] = getOffUnitAmount('rines');
	units[1][0] = Math.max(getUnitAmount('angr', 'rines') - (abs?0:units[0][0]), 0);
	
	units[0][1] = getOffUnitAmount('ranger');
	units[1][1] = Math.max(getUnitAmount('angr', 'ranger') - (abs?0:units[0][1]), 0);
	
	units[0][2] = getOffUnitAmount('buc');
	units[1][2] = Math.max(getUnitAmount('angr', 'buc') - (abs?0:units[0][2]), 0);
	
	units[0][3] = getOffUnitAmount('auc');
	units[1][3] = Math.max(getUnitAmount('angr', 'auc') - (abs?0:units[0][3]), 0);
	
	units[0][4] = getOffUnitAmount('huc');
	units[1][4] = Math.max(getUnitAmount('angr', 'huc') - (abs?0:units[0][4]), 0);
	
	return units;
}

function getAtterMults() {
	var ap_mult = 1.0;
	var vp_mult = 1.0;
	if(getFrak('angr') == "bf") {
		ap_mult += {/literal}{$CONST.frakbfap}{literal}; //BF frak bonus
	}
	ap_mult += Math.min(getGebPerc('angr', 'aussis'), 20)*0.03;
	ap_mult += getFosLevel('angr', 'bot')*{/literal}{$CONST.fosbot}{literal}; //bot
	ap_mult += getFosLevel('angr', 'iwt')*{/literal}{$CONST.fosiwt}{literal}; //iwt
	ap_mult += getFosLevel('angr', 'ra')*{/literal}{$CONST.fosraap}{literal}; //ra
	
	ap_mult += getPb('angr', 'ap')*{/literal}{$CONST.pbap}{literal}; //+10% ap pb
	ap_mult += hasMonu('angr', 'schule')*{/literal}{$CONST.monuschule}{literal}
	
	vp_mult += Math.min(getGebPerc('angr', 'forts'), 20)*0.03;
	vp_mult += getFosLevel('angr', 'bdt')*{/literal}{$CONST.fosbdt}{literal};
	vp_mult += getFosLevel('angr', 'dn')*{/literal}{$CONST.fosdn}{literal};
	vp_mult += getFosLevel('angr', 'ods')*{/literal}{$CONST.fosods}{literal};
	vp_mult += getPb('angr', 'vp')*{/literal}{$CONST.pbvp}{literal};
	vp_mult += hasMonu('angr', 'mauer')*{/literal}{$CONST.monumauer}{literal}
	
	return [ap_mult, vp_mult];
}

function calcAtter() {
	var units = getAtterUnits(false);
	var unitApVp = getUnitValues('angr');
	var ap = 0;
	var vp = 0;
	var zzv_ap = 0;
	var zzv_vp = 0;
	var syn_angr = 0;
	var syn_vert = 0;
	
	var log = ["calcAtter:\n"];

	var syn_angr_perc = ($('#angr_synarmeesend').prop('checked')?({/literal}{$CONST.satperc}{literal} * getFosLevel('angr', 'sat') * (1+isWar()*0.5)):0);
	var syn_vert_perc = {/literal}{$CONST.satperc}{literal} * getFosLevel('angr', 'sat') * (1+isWar()*0.5);
	
	for(var i=0; i < 5; i++) {
		ap += units[0][i] * unitApVp[i][0]; //raw ap
		log.push("unit ", i, " AP: ", units[0][i], " * ", unitApVp[i][0], " = ", units[0][i] * unitApVp[i][0], '\n');
		vp += units[1][i] * unitApVp[i][1]; //raw vp
		if(getFrak('angr') == 'neb' && i == 4)
			zzv_vp = units[1][i] * unitApVp[i][1]; //emps haben doppelte vp bei 10/4
		log.push("unit ", i, " VP: ", units[0][i], " * ", unitApVp[i][1], " = ", units[0][i] * unitApVp[i][1], '\n');
		
		if(getFrak('angr')=='nof' && i==2) //carrier werden von der synarmee nicht unterstützt
			continue;
		if(unitApVp[i][0] > 0) {
			syn_angr += units[0][i] * syn_angr_perc;
		}
		if(unitApVp[i][1] > 0) {
			syn_vert += units[1][i] * syn_vert_perc;
		}
	}
	//cap synarmy/set support fields
	var synrines_da = removeTrennzeichen($('#angr_synrines').val());
	var synranger_da = removeTrennzeichen($('#angr_synranger').val());
	if(synrines_da<syn_angr) {
		syn_angr = synrines_da;
		$('#angr_synrines_support').addClass('achtungAuftableInner');
	} else
		$('#angr_synrines_support').removeClass('achtungAuftableInner');
	if(synranger_da<syn_vert) {
		syn_vert = synranger_da;
		$('#angr_synranger_support').addClass('achtungAuftableInner');
	} else
		$('#angr_synranger_support').removeClass('achtungAuftableInner');
		
	$('#angr_synrines_support').text(addTrennzeichen(syn_angr));
	$('#angr_synranger_support').text(addTrennzeichen(syn_vert));
	zzv_ap = ap;
	zzv_vp += vp;
	ap += unitApVp[0][0] * syn_angr;
	vp += unitApVp[1][1] * syn_vert;
	log.push("Synarmee: ", syn_angr, " Marines mit ",unitApVp[0][0]," AP und ", syn_vert, " Ranger mit ",unitApVp[1][1]," VP\n");
	
	var supportedRines = 0;
	var supportedRanger = 0;
	var supportedAp = unitApVp[0][0];
	var supportedVp = 0;
	if(getFrak('angr') == "bf") {
		supportedRines = (units[0][0]+syn_angr > units[0][4]*2)?units[0][4]*2:units[0][0]+syn_angr;
		supportedAp = {/literal}{$CONST.titrinesupport}{literal};
		supportedRanger = (units[1][1]+syn_vert > units[1][4]*2)?units[1][4]*2:units[1][1]+syn_vert;
		supportedVp = {/literal}{$CONST.titrangersupport}{literal};
		ap += supportedRines * supportedAp;
		log.push("Titanen unterstützen ",supportedRines," Marines mit ",supportedAp," AP\n");
		vp += supportedRanger * supportedVp;
		log.push("Titanen unterstützen ",supportedRanger," Ranger mit ",supportedVp," VP\n");
	}else if(getFrak('angr') == "nof") {
		supportedRines = (units[0][0] > units[0][2]*2)?units[0][2]*2:units[0][0];
		supportedAp = {/literal}{$CONST.carrierrinesupport}{literal};
		supportedRanger = (units[1][1] > units[1][2]*2)?units[1][2]*2:units[1][1];
		supportedVp = {/literal}{$CONST.carrierrinesupport}{literal};
		ap += supportedRines*supportedAp;
		log.push("Carrier unterstützen ",supportedRines," Marines mit ",supportedAp," AP\n");
		vp += supportedRanger*supportedVp;
		log.push("Carrier unterstützen ",supportedRanger," Ranger mit ",supportedVp," VP\n");
	}
	if(supportedRines > 0) {
		if(supportedRines > units[0][0]) {
			zzv_ap += supportedAp*units[0][0];
			zzv_vp += supportedVp*units[1][1];
		} else {
			zzv_ap += supportedAp*supportedRines;
			zzv_vp += supportedVp*supportedRanger;
		}
	}
	if(getFrak('vert') == "neb") {
		log.push("EMP berechnung:\n");
		var empNeut = Math.ceil(getUnitAmount('vert', 'huc')/2);
		if(empNeut > 0) {
			var ap_voremp = ap;
		}
		var sortedUnits = {};
		for(var i=0;i<5;i++) {
			if (sortedUnits[unitApVp[i][0]])
				sortedUnits[unitApVp[i][0]] += units[0][i];
			else
				sortedUnits[unitApVp[i][0]] = units[0][i];
			if(i==0) {
				sortedUnits[unitApVp[i][0]]+= syn_angr-supportedRines;
			}
		}
		if(supportedRines) {
			if (sortedUnits[unitApVp[0][0]+supportedAp])
				sortedUnits[unitApVp[0][0]+supportedAp] += supportedRines;
			else
				sortedUnits[unitApVp[0][0]+supportedAp] = supportedRines;
		}
		var keys = [];
		for(var key in sortedUnits) {
			keys.push(key);
		}
		keys.sort(function(a,b){return a-b});

		for(var i=keys.length-1;i>=0;i--) {
			if(empNeut > sortedUnits[keys[i]]) {
				empNeut -= sortedUnits[keys[i]];
				ap -= sortedUnits[keys[i]]*keys[i];
				log.push(sortedUnits[keys[i]], " (alles) von Einheit mit ",keys[i]," AP neutralisiert (",sortedUnits[keys[i]]*keys[i]," raw AP)\n");
			} else {
				ap -= empNeut*keys[i]
				log.push(empNeut, " von Einheit mit ",keys[i]," AP neutralisiert (",empNeut*keys[i]," raw AP)\n");
				break;
			}
		}
	}
	var mults = getAtterMults();
	log.push(">", ap, " * ",mults[0], " = ", ap*mults[0], " AP\n");
	ap *= mults[0];
	if(ap_voremp)
		ap_voremp *= mults[0];
	log.push(">", vp, " * ",mults[1], " = ", vp*mults[1], " VP\n");
	vp *= mults[1];
	calcAtter_log = log.join("");
	zzv_ap *= mults[0];
	zzv_vp *= mults[1];
	return [ap, vp, zzv_ap, zzv_vp, ap_voremp];
}

function calcDeffer() {
	var units = [];
	
	units[0] = getUnitAmount('vert', 'rines');
	units[1] = getUnitAmount('vert', 'ranger');
	units[2] = getUnitAmount('vert', 'buc');
	units[3] = getUnitAmount('vert', 'auc');
	units[4] = getUnitAmount('vert', 'huc');
	var unitApVp = getUnitValues('vert');
	var vp = 0;
	var synarmy = 0;
	for(var i=0; i < 5; i++) {
		vp += units[i] * unitApVp[i][1]; //raw vp
		
		if(getFrak('vert')=='nof' && i==2) //carrier werden von der synarmee nicht unterstützt
			continue;
		if(unitApVp[i][1] > 0) {
			synarmy += units[i] * {/literal}{$CONST.satperc}{literal} * getFosLevel('vert', 'sat') * (1+isWar()*0.5);
		}
	}
	//cap synarmy/set support fields
	var synranger_da = removeTrennzeichen($('#vert_synranger').val());
	if(synranger_da<synarmy) {
		synarmy = synranger_da;
		$('#vert_synranger_support').addClass('achtungAuftableInner');
	} else
		$('#vert_synranger_support').removeClass('achtungAuftableInner');
		
	$('#vert_synranger_support').text(addTrennzeichen(synarmy));
	
	vp += unitApVp[1][1] * synarmy;
	if(getFrak('vert') == "bf") {
		vp += (units[1]+synarmy > units[4]*2)?units[4]*2*{/literal}{$CONST.titrangersupport}{literal}:(units[1]+synarmy)*{/literal}{$CONST.titrangersupport}{literal};
	}else if(getFrak('vert') == "nof") {
		vp += (units[1] > units[2]*2)?units[2]*2*{/literal}{$CONST.carrierrangersupport}{literal}:units[1]*{/literal}{$CONST.carrierrangersupport}{literal};
	}
	var vp_mult = 1.0;
	vp_mult += Math.min(getGebPerc('vert', 'forts'), 20)*0.03;
	vp_mult += getFosLevel('vert', 'bdt')*{/literal}{$CONST.fosbdt}{literal};
	vp_mult += getFosLevel('vert', 'dn')*{/literal}{$CONST.fosdn}{literal};
	vp_mult += getFosLevel('vert', 'ods')*{/literal}{$CONST.fosods}{literal};
	vp_mult += hasMonu('vert', 'mauer')*{/literal}{$CONST.monumauer}{literal}
	vp_mult += getPb('vert', 'vp')*{/literal}{$CONST.pbvp}{literal};
	vp *= vp_mult;
	vp = Math.max(getLand('vert')*2, vp);
	return vp;
}

function zehnZuVier() {
	//reset der att felder:
	$('#angr_rines_att').val(0);
	$('#angr_ranger_att').val(0);
	$('#angr_buc_att').val(0);
	$('#angr_auc_att').val(0);
	$('#angr_huc_att').val(0);
	var available = {};
	var unitApVp = getUnitValues('angr');
	var units = getAtterUnits(true);
	var mults = getAtterMults();
	var ratio = [];
	for(var i=0; i<unitApVp.length;i++) {
		if((unitApVp[i][1]*mults[1]) == 0)
			ratio[i] = [9999-i, i];
		else
			ratio[i] = [(unitApVp[i][0]*mults[0])/(unitApVp[i][1]*mults[1]), i];
	}
	if(getFrak('angr') == "bf") {
		var supportedRines = (units[1][0] > units[1][4]*2)?units[1][4]*2:units[1][0];
		var supportedRanger = (units[1][0] > units[1][4]*2)?units[1][4]*2:units[1][0];
		if(supportedRanger > supportedRines) {
			var moreVpTits = supportedRanger - supportedRines;
			var equalTits = supportedRines;
			units[1][4] -= moreVpTits + equalTits;
			units[1][5] = equalTits;
			units[1][6] = moreVpTits;
			ratio[5] = ((unitApVp[4][0]+{/literal}{$CONST.titrinesupport}{literal})*mults[0])/((unitApVp[4][1]+{/literal}{$CONST.titrangersupport}{literal})*mults[1]);
			ratio[6] = (unitApVp[4][0]*mults[0])/((unitApVp[4][1]+{/literal}{$CONST.titrangersupport}{literal})*mults[1])
		} else {
			var moreApTits = supportedRines - supportedRanger;
			var equalTits = supportedRanger;
			units[1][4] -= moreApTits + equalTits;
			units[1][5] = equalTits;
			units[1][6] = moreApTits;
			ratio[5] = ((unitApVp[4][0]+{/literal}{$CONST.titrinesupport}{literal})*mults[0])/((unitApVp[4][1]+{/literal}{$CONST.titrangersupport}{literal})*mults[1]);
			ratio[6] = ((unitApVp[4][0]+{/literal}{$CONST.titrinesupport}{literal})*mults[0])/(unitApVp[4][1]*mults[1])
		}
	}else if(getFrak('angr') == "nof") {
		var supportedRines = (units[1][0] > units[1][4]*2)?units[1][4]*2:units[1][0];
		var supportedRanger = (units[1][0] > units[1][2]*2)?units[1][2]*2:units[1][0];
	}
	var totalUnits=0;
	for(var i=0, j=0; i<units[1].length;i++) {
		if(units[1][i] <= 0 || ratio[i] == 0) {
			ratio.splice(i-j, 1);
			j++;
		}
		totalUnits += units[1][i];
	}
	ratio.sort(function(a, b){return b[0]-a[0]});
	for(var i=0; i<ratio.length;i++) {
		if(available[ratio[i][0]])
			ratio[i][0]+=0.001;
		available[ratio[i][0]] = [];
		available[ratio[i][0]][0] = units[1][ratio[i][1]];
		available[ratio[i][0]][1] = unitApVp[ratio[i][1]][0]*mults[0]; //ap
		available[ratio[i][0]][2] = unitApVp[ratio[i][1]][1]*mults[1]; //vp
		available[ratio[i][0]][3] = ratio[i][1]; //unit#
	}
	var send = getApVpRatio(available, [], 0, Math.max(totalUnits/50, 50), Math.max(totalUnits/1000, 5));
	var unitName;
	for(var i=0;i<5;i++) {
		switch(i) {
			case 0: unitName = 'rines';break;
			case 1: unitName = 'ranger';break;
			case 2: unitName = 'buc';break;
			case 3: unitName = 'auc';break;
			case 4: unitName = 'huc';break;
		}
		$('#angr_'+unitName+'_att').val((send[i])?addTrennzeichen(send[i]):0);
	}
	update();
}

function getApVpRatio(unitObj, lastAmounts, lastRatio, step, precision) {
	var targetRatio = 2.5; //10/4
	if((Math.abs(step) <= precision && lastRatio <= targetRatio) || Math.abs(step)<1) {
		var ret = [];
		for(var key in unitObj) {
			ret[unitObj[key][3]] = lastAmounts[key];
		}
		return ret;
	}
	var ap=0;
	var vp=0;
	var add = true;
	for(var key in unitObj) {
		if(lastAmounts[key]) {
			if(lastAmounts[key] == unitObj[key][0]) {
				ap += unitObj[key][0] * unitObj[key][1]
				continue;
			}
		} else
			lastAmounts[key] = 0;
		if(add) {
			if(lastAmounts[key]+step > unitObj[key][0])
				lastAmounts[key] = unitObj[key][0];
			else
				lastAmounts[key] += step;
			if(lastAmounts[key]<0)
				lastAmounts[key] = 0;
		}
		ap += lastAmounts[key] * unitObj[key][1];
		vp += (unitObj[key][0]-lastAmounts[key])*unitObj[key][2];
		add = false;
		continue;
	}
	var ratio = ap/vp;
	if (ratio > targetRatio) {
		if(step>0)
			step = -step;
		step = Math.round(step/2);
	} else if(step < 0) {
		step = -step;
	}
	return getApVpRatio(unitObj, lastAmounts, ratio, step, precision);
}

function getFrak(type) {
	return $('#'+type+'_frak').text();
}

function getHeimkehr() {
	var ret = 20;
	ret -= getFosLevel('angr', 'ra')*7 + getFosLevel('angr', 'cm')*1;
	ret -= getPb('angr', 'heimkehr')*2 + hasMonu('angr', 'blitz')*4;
	ret += getFosLevel('vert', 'fow')*4 + hasMonu('vert', 'trans')*5;
	if(getFrak('angr') == 'bf') {
		ret -= 2;
		ret -= (getGebPerc('angr', 'specgeb')<10)?Math.floor(getGebPerc('angr', 'specgeb')):10;
	}
	if(ret < 6)
		ret = 6;
	if(ret > 20)
		ret = 20;
	return ret;
}

function getDelay(user_delay, spy_delay_type) {
	var delay = user_delay?parseInt(removeTrennzeichen($('#gen_delay').val())):0;
	if(spy_delay_type) {
		if(spy_delay_type == 'units')
			delay += spy_delay_units;
		else if(spy_delay_type == 'gebs')
			delay += spy_delay_gebs;
	}
	if(delay > 20)
		delay = 20;
	return delay;
}

function blur_single(unitVals, id) {
	if(!blur_inprogress[id]) {
		blur_inprogress[id] = true;
		var ident;
		switch(id) {
			case 0: ident = 'ranger'; break;
			case 1: ident = 'buc'; break;
			case 2: ident = 'auc'; break;
			case 3: ident = 'huc'; break;
			default: console.log('Error in blur_single: no unit with id '+id);
		}
		$('#'+ident+'_att_td').animate({opacity:((unitVals[id+1][0] > 0)?1:0.3)}, 'slow', function() {
			blur_inprogress[id] = false;
		});
	} else {
		setTimeout(function() {blur_single(unitVals, id)}, 400);
	}
}

var current_blur_frak = false;
var blur_inprogress = [false, false, false, false];
function blurAttackFields() {
	//visual effects:
	var local_frak = current_blur_frak;
	if(local_frak) {
		if (local_frak == getFrak('angr'))
			return 'same frak';
	}
	current_blur_frak = getFrak('angr');
	var unitVals = getUnitValues('angr');
	blur_single(unitVals, 0);
	blur_single(unitVals, 1);
	blur_single(unitVals, 2);
	blur_single(unitVals, 3);
}

var voremp_state = 'hidden';
var re_update = false;
function update() {
	var delay = getDelay(true);
	var loc_spy_delay_units = spy_delay_units;
	if(getDelay(true) > 0 || loc_spy_delay_units) {
		//Defender
		$('#vert_units_add').text('Zusätzlich angekommen:');
		var add_units = [0,0,0,0,0];
		{/literal}{if $ROLE}{literal}
			var milbau = g_own_milbau;
			var milaway = g_own_milaway;
		{/literal}{else}{literal}
			var milbau = g_milbau;
			var milaway = g_milaway;
			delay += loc_spy_delay_units;
		{/literal}{/if}{literal}
		if(delay>20)
			delay = 20;
		for(var i=0;i<delay;i++) {
			add_units[0] += removeTrennzeichen(milbau?milbau[0][i]:0) + removeTrennzeichen(milaway?milaway[0][i]:0);
			add_units[1] += removeTrennzeichen(milbau?milbau[1][i]:0) + removeTrennzeichen(milaway?milaway[1][i]:0);
			add_units[2] += removeTrennzeichen(milbau?milbau[2][i]:0) + removeTrennzeichen(milaway?milaway[2][i]:0);
			add_units[3] += removeTrennzeichen(milbau?milbau[3][i]:0) + removeTrennzeichen(milaway?milaway[3][i]:0);
			add_units[4] += removeTrennzeichen(milbau?milbau[4][i]:0) + removeTrennzeichen(milaway?milaway[4][i]:0);
		}
		$('#vert_rines_add').text(addTrennzeichen(add_units[0]));
		$('#vert_ranger_add').text(addTrennzeichen(add_units[1]));
		$('#vert_buc_add').text(addTrennzeichen(add_units[2]));
		$('#vert_auc_add').text(addTrennzeichen(add_units[3]));
		$('#vert_huc_add').text(addTrennzeichen(add_units[4]));
	} else {
		$('#vert_rines_add').text('');
		$('#vert_ranger_add').text('');
		$('#vert_buc_add').text('');
		$('#vert_auc_add').text('');
		$('#vert_huc_add').text('');
	}
	//Attacker delay
	var loc_last_delay = last_delay; //lokal schneller als global
	if(loc_last_delay != delay) {
		{/literal}{if !$ROLE}{literal}
			milbau = g_own_milbau;
			milaway = g_own_milaway;
		{/literal}{else}{literal}
			milbau = g_milbau;
			milaway = g_milaway;
			delay += loc_spy_delay_units;
		{/literal}{/if}{literal}
		if(delay>20)
			delay = 20;
		if(milbau || milaway) {
			add_units = [0, 0, 0, 0, 0];
			var factor = (delay-loc_last_delay)/Math.abs(delay-loc_last_delay);
			var i = ((loc_last_delay<delay)?loc_last_delay:delay);
			var limit = (loc_last_delay<delay)?delay:loc_last_delay;
			for(;i<limit;i++) {
				add_units[0] += factor*(removeTrennzeichen(milbau?milbau[0][i]:0) + removeTrennzeichen(milaway?milaway[0][i]:0));
				add_units[1] += factor*(removeTrennzeichen(milbau?milbau[1][i]:0) + removeTrennzeichen(milaway?milaway[1][i]:0));
				add_units[2] += factor*(removeTrennzeichen(milbau?milbau[2][i]:0) + removeTrennzeichen(milaway?milaway[2][i]:0));
				add_units[3] += factor*(removeTrennzeichen(milbau?milbau[3][i]:0) + removeTrennzeichen(milaway?milaway[3][i]:0));
				add_units[4] += factor*(removeTrennzeichen(milbau?milbau[4][i]:0) + removeTrennzeichen(milaway?milaway[4][i]:0));
			}
			$('#angr_rines_da').val(addTrennzeichen(removeTrennzeichen($('#angr_rines_da').val())+add_units[0]));
			$('#angr_ranger_da').val(addTrennzeichen(removeTrennzeichen($('#angr_ranger_da').val())+add_units[1]));
			$('#angr_buc_da').val(addTrennzeichen(removeTrennzeichen($('#angr_buc_da').val())+add_units[2]));
			$('#angr_auc_da').val(addTrennzeichen(removeTrennzeichen($('#angr_auc_da').val())+add_units[3]));
			$('#angr_huc_da').val(addTrennzeichen(removeTrennzeichen($('#angr_huc_da').val())+add_units[4]));
		}
		last_delay = delay;
	}
	
	var apvp = calcAtter();
	var def_vp = calcDeffer();
	$('#res_ap').text(addTrennzeichen(apvp[0]));
	$('#res_att_vp').text(addTrennzeichen(apvp[1]));
	$('#res_vp').text(addTrennzeichen(def_vp));
	var zzv = (apvp[2]/apvp[3]-2.5)/2.5*100;
	if(apvp[4]) {
		$('#res_ap_voremp_value').text(addTrennzeichen(apvp[4]));
		$('#res_vp_voremp_value').text(addTrennzeichen(apvp[4]-apvp[0]));
		if(voremp_state == 'hidden') {
			voremp_state = 'toshown';
			$('#res_ap_voremp_value').show('slow', function() {
				if(voremp_state == 'toshown') {
					voremp_state = 'shown';
				}
			});
			$('#res_ap_voremp_caption').show('slow');
			$('#res_vp_voremp_caption').show('slow');
			$('#res_vp_voremp_value').show('slow');
		} else if (voremp_state == 'tohidden') {
			setTimeout(function(){
				voremp_state = 'toshown';
				$('#res_ap_voremp_value').show('slow', function() {
				if(voremp_state == 'toshown') {
					voremp_state = 'shown';
				}
			});
				$('#res_ap_voremp_caption').show('slow');
				$('#res_vp_voremp_caption').show('slow');
				$('#res_vp_voremp_value').show('slow');
			}, 1000);
		}
	} else {
		$('#res_ap_voremp_value').text(' ');
		if(voremp_state == 'shown') {
			voremp_state = 'tohidden';
			$('#res_ap_voremp_value').hide('slow', function() {
				if(voremp_state == 'tohidden') {
					voremp_state = 'hidden';
				}
			});
			$('#res_ap_voremp_caption').hide('slow');
			$('#res_vp_voremp_caption').hide('slow');
			$('#res_vp_voremp_value').hide('slow');
		} else if (voremp_state == 'toshown') {
			setTimeout(function(){
				voremp_state = 'tohidden';
				$('#res_ap_voremp_value').hide('slow', function() {
				if(voremp_state == 'tohidden') {
					voremp_state = 'hidden';
				}
			});
				$('#res_ap_voremp_caption').hide('slow');
				$('#res_vp_voremp_caption').hide('slow');
				$('#res_vp_voremp_value').hide('slow');
			}, 1000);
		}
	}
	if(isNaN(zzv)) {
		zzv = 0;
		$('#res_zzv_prozent').removeClass('gruenAuftableInner');
		$('#res_zzv_prozent').removeClass('achtungAuftableInner');
	} else {
		if(zzv > 0) {
			$('#res_zzv_prozent').removeClass('gruenAuftableInner');
			$('#res_zzv_prozent').addClass('achtungAuftableInner');
		} else {
			$('#res_zzv_prozent').removeClass('achtungAuftableInner');
			$('#res_zzv_prozent').addClass('gruenAuftableInner');
		}
	}
	$('#res_zzv_prozent').html(Math.round(zzv*10)/10+'&nbsp;%');
	var success = apvp[0] > def_vp;
	setLosses('angr', success);
	setLosses('vert', success);
	var gain = getLandgain();
	$('#res_gain').text(addTrennzeichen(gain));
	if(getFrak('angr')=='bf') {
		$('#res_special_data').text(addTrennzeichen(Math.min(gain, getOffUnitAmount('buc')/{/literal}{$CONST.unitwartankspergeb}{literal})));
	} else if(getFrak('angr')=='sl') {
		$('#res_special_data').text(addTrennzeichen(getOffUnitAmount('auc')/{/literal}{$CONST.unithhsprospy}{literal}));
	} else if(getFrak('angr')=='nof') {
		var losses = getLosses('angr', 1);
		var behes = getOffUnitAmount('huc')-losses[4]
		var all_losses = losses[0]+losses[1]+losses[2]+losses[3]+losses[4];
		var rec = (behes>all_losses)?all_losses:behes;
		var excess = behes - all_losses;
		var span_class = (excess<0)?'achtungAuftableInner':'gruenAuftableInner';
		$('#res_special_data').html(addTrennzeichen(rec)+'&nbsp;<span class="'+span_class+'">('+((excess<0)?'-':'+')+addTrennzeichen(Math.abs(excess))+')</span>');
	}
	blurAttackFields();

	$('#res_heimkehr').text(getHeimkehr());
	if(success) {
		$('#res_ausgang').text('Angriff erfolgreich!');
		$('#res_ausgang').removeClass('achtungAuftableInner')
		$('#res_ausgang').addClass('gruenAuftableInner')
		{/literal}{if $ROLE}{literal}
			$('#res_vp').removeClass('gruenAuftableInner');
			$('#res_vp').addClass('achtungAuftableInner');
		{/literal}{else}{literal}
			$('#res_ap').removeClass('achtungAuftableInner');
			$('#res_ap').addClass('gruenAuftableInner');
		{/literal}{/if}{literal}
	} else {
		$('#res_ausgang').text('Angriff fehlgeschlagen!');
		$('#res_ausgang').removeClass('gruenAuftableInner')
		$('#res_ausgang').addClass('achtungAuftableInner')
		{/literal}{if $ROLE}{literal}
			$('#res_vp').removeClass('achtungAuftableInner');
			$('#res_vp').addClass('gruenAuftableInner');
		{/literal}{else}{literal}
			$('#res_ap').removeClass('gruenAuftableInner');
			$('#res_ap').addClass('achtungAuftableInner');
		{/literal}{/if}{literal}
	}
	if(re_update) {
		re_update = false;
		setTimeout(update, 10); //sonst werden Änderungen evtl nicht gleich eingerechnet
	} else {re_update = true};
}

function getLosses(type, success) {
	var stdLoss = 0.1;
	var angrLoss = 1+getFosLevel('vert', 'iwt')*{/literal}{$CONST.fosiwtlosses}{literal}-getFosLevel('angr', 'ha')*{/literal}{$CONST.fosha}{literal}-hasMonu('angr', 'nebel')*{/literal}{$CONST.monunebel}{literal}-getPb('angr', 'verluste')*{/literal}{$CONST.pblosses}{literal};
	var vertLoss = 1-getFosLevel('vert', 'fow')*{/literal}{$CONST.fosfowlosses}{literal}+getFosLevel('angr', 'iwt')*{/literal}{$CONST.fosiwtlosses}{literal}-getFosLevel('vert', 'ha')*{/literal}{$CONST.fosha}{literal}-hasMonu('vert', 'nebel')*{/literal}{$CONST.monunebel}{literal}-getPb('vert', 'verluste')*{/literal}{$CONST.pblosses}{literal};
	if(getFrak('angr')=='bf') angrLoss -= {/literal}{$CONST.frakbflosses}{literal};
	if(getFrak('vert')=='bf') vertLoss -= {/literal}{$CONST.frakbflosses}{literal};
	if(getFrak('angr')=='nof') angrLoss -= getGebPerc('angr', 'specgeb')*{/literal}{$CONST.werklosses}{literal};
	if(getFrak('vert')=='nof') vertLoss -= getGebPerc('vert', 'specgeb')*{/literal}{$CONST.werklosses}{literal};
	if(getFrak('vert')=='uic') vertLoss -= {/literal}{$CONST.unitrwlosses}{literal}*getUnitAmount('vert', 'buc')/getUnitAmount('vert', 'all');
	if(angrLoss < 0.1)
		angrLoss = 0.1;
	if(vertLoss < 0.1)
		vertLoss = 0.1;
	
	var ap = removeTrennzeichen($('#res_ap').text());
	var vp = removeTrennzeichen($('#res_vp').text());
	if(ap > vp) {
		angrLoss = angrLoss * (vp/ap);
	} else {
		vertLoss = vertLoss * (ap/vp);
	}
	
	var amount = [];
	var vertApVp = getUnitValues('vert');
	var angrApVp = getUnitValues('angr');
	var bashschutz = getBashschutzFactor();
	var failFaktor = 1;
	if(!success) {
		if (isWar() || isDirRR())
			failFaktor = 0.5;
		else
			failFaktor = 0;
	}
	var smallkonz_mult = 1;
	if(!isDirRR()) {
		var vert_land = getLand('vert');
		var angr_land = getLand('angr');
		smallkonz_mult = ((vert_land<angr_land)?vert_land/angr_land:1)*((vert_land<angr_land*0.8)?Math.pow(vert_land*1.25/angr_land, 1.5):1);
		smallkonz_mult *= smallkonz_mult;
	}
	if(type=='vert') {
		amount[0] = (vertApVp[0][1]==0)?0:removeTrennzeichen($('#vert_rines_da').val())*stdLoss*bashschutz*vertLoss*smallkonz_mult*failFaktor;
		amount[1] = (vertApVp[1][1]==0)?0:removeTrennzeichen($('#vert_ranger_da').val())*stdLoss*bashschutz*vertLoss*smallkonz_mult*failFaktor;
		amount[2] = (vertApVp[2][1]==0)?0:removeTrennzeichen($('#vert_buc_da').val())*stdLoss*bashschutz*vertLoss*smallkonz_mult*failFaktor;
		amount[3] = (vertApVp[3][1]==0)?0:removeTrennzeichen($('#vert_auc_da').val())*stdLoss*bashschutz*((getFrak('vert')=='bf')?(vertLoss>0.25?vertLoss-0.15:0.1):vertLoss)*smallkonz_mult*failFaktor;
		amount[4] = (vertApVp[4][1]==0)?0:removeTrennzeichen($('#vert_huc_da').val())*stdLoss*bashschutz*vertLoss*smallkonz_mult*failFaktor;
		amount[5] = removeTrennzeichen($('#vert_synranger_support').text())*stdLoss*bashschutz*vertLoss*smallkonz_mult*failFaktor;
	} else {
		amount[0] = (angrApVp[0][0]==0)?0:removeTrennzeichen($('#angr_rines_att').val())*stdLoss*angrLoss;
		amount[1] = (angrApVp[1][0]==0)?0:removeTrennzeichen($('#angr_ranger_att').val())*stdLoss*angrLoss;
		amount[2] = (angrApVp[2][0]==0)?0:removeTrennzeichen($('#angr_buc_att').val())*stdLoss*angrLoss;
		amount[3] = (angrApVp[3][0]==0)?0:removeTrennzeichen($('#angr_auc_att').val())*stdLoss*((getFrak('angr')=='bf')?(angrLoss>0.25?angrLoss-0.15:0.1):angrLoss);
		amount[4] = (angrApVp[4][0]==0)?0:removeTrennzeichen($('#angr_huc_att').val())*stdLoss*angrLoss;
		amount[5] = removeTrennzeichen($('#angr_synrines_support').text())*stdLoss*angrLoss;
	}
	return amount;
}

function setLosses(type, success) {
	var amount = getLosses(type, success);
	$('#'+type+'_losses_marines').text(addTrennzeichen(amount[0]));
	$('#'+type+'_losses_ranger').text(addTrennzeichen(amount[1]));
	$('#'+type+'_losses_buc').text(addTrennzeichen(amount[2]));
	$('#'+type+'_losses_auc').text(addTrennzeichen(amount[3]));
	$('#'+type+'_losses_huc').text(addTrennzeichen(amount[4]));
	$('#'+type+'_losses_synarmy').text(addTrennzeichen(amount[5]));
	
	if((amount[0] + amount[1] + amount[2] + amount[3] + amount[4]) == 0) {
		$('#'+type+'_losses_show').hide('slow');
		$('#'+type+'_losses_none').show('slow');
	}
	else {
		$('#'+type+'_losses_show').show('slow');
		$('#'+type+'_losses_none').hide('slow');
	}
}

function setFrakUnits(type, frak) {
	var ident_buc = '#'+type+"_buc_name"
	var ident_auc = '#'+type+"_auc_name"
	var ident_huc = '#'+type+"_huc_name"
	var ident_buc2 = '#'+type+'_losses_buc_name';
	var ident_auc2 = '#'+type+'_losses_auc_name';
	var ident_huc2 = '#'+type+'_losses_huc_name';
	switch(frak) {
		{/literal}{foreach from=$UNITS key=FRAK_KEY item=UNIT}{literal}
			case '{/literal}{$FRAK_KEY}{literal}': $(ident_buc).text('{/literal}{$UNIT.buc}{literal}');
											$(ident_auc).text('{/literal}{$UNIT.auc}{literal}');
											$(ident_huc).text('{/literal}{$UNIT.huc}{literal}');
											$(ident_buc2).text('{/literal}{$UNIT.buc}{literal}');
											$(ident_auc2).text('{/literal}{$UNIT.auc}{literal}');
											$(ident_huc2).text('{/literal}{$UNIT.huc}{literal}');
											break;
		{/literal}{/foreach}{literal}
	}
	//update the special field
	if(type == 'angr') {
		if (frak == 'sl') {
			$('[type=res_special]').animate({opacity: 1}, 'slow');
			$('#res_special_text').text('Max. Spione entführt:');
		} else if (frak == 'bf') {
			$('[type=res_special]').animate({opacity: 1}, 'slow');
			$('#res_special_text').text('Zerstörte Gebäude:');
		} else if (frak == 'nof') {
			$('[type=res_special]').animate({opacity: 1}, 'slow');
			$('#res_special_text').html('Carrier recycelt<br />(inaktive Behes)');
		} else {
			$('[type=res_special]').animate({opacity: 0.2}, 'slow', function() {
				$('#res_special_text').text('Kein Special');
			});
			$('#res_special_data').text('');
		}
	}
	//spezial gebaeude
	if(frak == 'bf' && type == 'angr') {
		$('[hideclass='+type+'_specgeb]').animate({opacity: 1}, 'slow');
		$('#'+type+'_specgeb_name').text('Radaranlagen');
		$('#'+type+'_specgeb_abs').removeAttr('disabled');
		$('#'+type+'_specgeb_rel').removeAttr('disabled');
	} else if (frak == 'nof') {
		$('[hideclass='+type+'_specgeb]').animate({opacity: 1}, 'slow');
		$('#'+type+'_specgeb_name').text('Werkstätten');
		$('#'+type+'_specgeb_abs').removeAttr('disabled');
		$('#'+type+'_specgeb_rel').removeAttr('disabled');
	} else {
		$('#'+type+'_specgeb_abs').attr('disabled', true);
		$('#'+type+'_specgeb_rel').attr('disabled', true);
		$('[hideclass='+type+'_specgeb]').animate({opacity: 0.2}, 'slow', function() {
			$('#'+type+'_specgeb_name').text('Keine');
		});
	}
}

$('document').ready(function(){
	{/literal}
	{foreach from=$FRAKS item=FRAK}
		{literal}
			//angereifer
			$('#angr_frak_{/literal}{$FRAK}{literal}').hover(function(eventData) {
				$('#angr_frak_{/literal}{$FRAK}{literal}').css('opacity', '1');
			}, function(eventData) {
				if($('#angr_frak_{/literal}{$FRAK}{literal}').attr('current') != "this") {
					$('#angr_frak_{/literal}{$FRAK}{literal}').css('opacity', '0.2');
				}
			});
			$('#angr_frak_{/literal}{$FRAK}{literal}').click(function() {
				{/literal}
				{foreach from=$FRAKS item=FRAK2}
					{literal}
						$('#angr_frak_{/literal}{$FRAK2}{literal}').removeAttr('current');
						$('#angr_frak_{/literal}{$FRAK2}{literal}').css('opacity', '0.2');
					{/literal}
				{/foreach}
				{literal}
				$('#angr_frak_{/literal}{$FRAK}{literal}').attr('current', 'this');
				$('#angr_frak_{/literal}{$FRAK}{literal}').css('opacity', '1');
				$('#angr_frak').text('{/literal}{$FRAK}{literal}');
				setFrakUnits('angr', '{/literal}{$FRAK}{literal}');
				update();
			});
			
			//verteidiger
			$('#vert_frak_{/literal}{$FRAK}{literal}').hover(function(eventData) {
				$('#vert_frak_{/literal}{$FRAK}{literal}').css('opacity', '1');
			}, function(eventData) {
				if($('#vert_frak_{/literal}{$FRAK}{literal}').attr('current') != "this") {
					$('#vert_frak_{/literal}{$FRAK}{literal}').css('opacity', '0.2');
				}
			});
			$('#vert_frak_{/literal}{$FRAK}{literal}').click(function() {
				{/literal}
				{foreach from=$FRAKS item=FRAK2}
					{literal}
						$('#vert_frak_{/literal}{$FRAK2}{literal}').removeAttr('current');
						$('#vert_frak_{/literal}{$FRAK2}{literal}').css('opacity', '0.2');
					{/literal}
				{/foreach}
				{literal}
				$('#vert_frak_{/literal}{$FRAK}{literal}').attr('current', 'this');
				$('#vert_frak_{/literal}{$FRAK}{literal}').css('opacity', '1');
				$('#vert_frak').text('{/literal}{$FRAK}{literal}');
				setFrakUnits('vert', '{/literal}{$FRAK}{literal}');
				update();
			});
		{/literal}
	{/foreach}
	
	{literal}
	//change -> update
	$('input').each(function(index) {
		this.onchange = update;
	});
	$('select[type=changecalc]').each(function(index) {
		this.onchange = update;
	});
	
	$('#angr_aussis_rel').change(function() {
		$('#angr_aussis_abs').val(addTrennzeichen(Math.ceil(removeTrennzeichen($('#angr_aussis_rel').val())/100*getLand('angr'))));
	});
	$('#angr_forts_rel').change(function() {
		$('#angr_forts_abs').val(addTrennzeichen(Math.ceil(removeTrennzeichen($('#angr_forts_rel').val())/100*getLand('angr'))));
	});
	$('#angr_specgeb_rel').change(function() {
		$('#angr_specgeb_abs').val(addTrennzeichen(Math.ceil(removeTrennzeichen($('#angr_specgeb_rel').val())/100*getLand('angr'))));
	});
	$('#vert_forts_rel').change(function() {
		$('#vert_forts_abs').val(addTrennzeichen(Math.ceil(removeTrennzeichen($('#vert_forts_rel').val())/100*getLand('vert'))));
	});
	$('#vert_specgeb_rel').change(function() {
		$('#vert_specgeb_abs').val(addTrennzeichen(Math.ceil(removeTrennzeichen($('#vert_specgeb_rel').val())/100*getLand('vert'))));
	});
	$('#angr_aussis_abs').change(function() {
		$('#angr_aussis_rel').val((getLand('angr')<=0)?0:(Math.round(removeTrennzeichen($('#angr_aussis_abs').val())*10000/getLand('angr'))/100).toString().replace('.', ','));
	}); $('#angr_aussis_abs').change();
	$('#angr_forts_abs').change(function() {
		$('#angr_forts_rel').val((getLand('angr')<=0)?0:(Math.round(removeTrennzeichen($('#angr_forts_abs').val())*10000/getLand('angr'))/100).toString().replace('.', ','));
	}); $('#angr_forts_abs').change();
	$('#angr_aussis_abs').change(function() {
		$('#angr_specgeb_rel').val((getLand('angr')<=0)?0:(Math.round(removeTrennzeichen($('#angr_specgeb_abs').val())*10000/getLand('angr'))/100).toString().replace('.', ','));
	}); $('#angr_specgeb_abs').change();
	$('#vert_forts_abs').change(function() {
		$('#vert_forts_rel').val((getLand('vert')<=0)?0:(Math.round(removeTrennzeichen($('#vert_forts_abs').val())*10000/getLand('vert'))/100).toString().replace('.', ','));
	}); $('#vert_forts_abs').change();
	$('#vert_specgeb_abs').change(function() {
		$('#vert_specgeb_rel').val((getLand('vert')<=0)?0:(Math.round(removeTrennzeichen($('#vert_specgeb_abs').val())*10000/getLand('vert'))/100).toString().replace('.', ','));
	}); $('#vert_specgeb_abs').change();
	$('#angr_specgeb_abs').change(function() {
		$('#angr_specgeb_rel').val((getLand('angr')<=0)?0:(Math.round(removeTrennzeichen($('#angr_specgeb_abs').val())*10000/getLand('angr'))/100).toString().replace('.', ','));
	}); $('#angr_specgeb_abs').change();
	$('#vert_bau_abs').change(function() {
		$('#vert_bau_rel').val((getLand('vert')<=0)?0:(Math.round(removeTrennzeichen($('#vert_bau_abs').val())*10000/getLand('vert'))/100).toString().replace('.', ','));
	}); $('#vert_bau_abs').change();
	$('#angr_land_abs').change(function() {
		$('#angr_aussis_abs').change();
		$('#angr_forts_abs').change();
		$('#angr_specgeb_abs').change();
	});
	$('#vert_land_abs').change(function() {
		$('#vert_bau_abs').change();
		$('#vert_forts_abs').change();
		$('#vert_specgeb_abs').change();
	});
	setFrakUnits('angr', '{/literal}{$ANGR.frak}{literal}');
	setFrakUnits('vert', '{/literal}{$VERT.frak}{literal}');
	{/literal}{if $REPORTS}{literal}
		readSpies($('#input_berichte').text());
	{/literal}{/if}{literal}
	//beim laden sofort ein update, damit alles stimmt
	update();
	
	types = ['angr', 'vert'];
	for(var i=0;i<2;i++) {
		$('#'+types[i]+'_rines_da').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_rines_da').val())));
		$('#'+types[i]+'_ranger_da').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_ranger_da').val())));
		$('#'+types[i]+'_buc_da').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_buc_da').val())));
		$('#'+types[i]+'_auc_da').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_auc_da').val())));
		$('#'+types[i]+'_huc_da').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_huc_da').val())));
		$('#'+types[i]+'_land_abs').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_land_abs').val())));
		$('#'+types[i]+'_synrines').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_synrines').val())));
		$('#'+types[i]+'_synranger').val(addTrennzeichen(removeTrennzeichen($('#'+types[i]+'_synranger').val())));
	}
	
	//milbau_arr und milaway_arr in arrays einlesen
	
		{/literal}{if $MILBUILD}{literal}
		var milbau_arr = [[],[],[],[],[]];
		for(var i=0;i<5;i++)
			for(var j=0;j<20;j++)
				milbau_arr[i][j] = 0;
		{/literal}
			{foreach from=$MILBUILD key=KEY item=ITEM}
				{foreach from=$ITEM key=KEY2 item=ITEM2}
					milbau_arr['{$KEY}']['{$KEY2}'] = {$ITEM2};
				{/foreach}
			{/foreach}
		{literal};
		g_own_milbau = milbau_arr;
	{/literal}{/if}{literal}
	{/literal}{if $MILAWAY}{literal}
		var milaway_arr = [[],[],[],[],[]];
		for(var i=0;i<5;i++)
			for(var j=0;j<20;j++)
				milaway_arr[i][j] = 0;
		{/literal}
			{foreach from=$MILAWAY key=KEY item=ITEM}
				{foreach from=$ITEM key=KEY2 item=ITEM2}
					milaway_arr['{$KEY}']['{$KEY2}'] = {$ITEM2};
				{/foreach}
			{/foreach}
		{literal};
		g_own_milaway = milaway_arr;
	{/literal}{/if}
	{if $OWNMONU}{literal}
		$('#{/literal}{if $ROLE}vert{else}angr{/if}{literal}_monu').val('{/literal}{$OWNMONU}{literal}');
	{/literal}{/if}{literal}
});
-->
</script>
{/literal}
