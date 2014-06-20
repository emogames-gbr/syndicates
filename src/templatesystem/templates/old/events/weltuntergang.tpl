{literal}
<script type="text/javascript">
	  dojo.require("dojo.dnd.Container");
	  dojo.require("dojo.dnd.Manager");
	  dojo.require("dojo.dnd.Source");
	  dojo.require("dojo.dnd.autoscroll");
	  dojo.require("dijit.ProgressBar");
	  dojo.require("dijit.form.NumberTextBox");
	  dojo.require("dojo.fx");
	  dojo.require("dojox.widget.FisheyeLite");

	  dojo.addOnLoad(function(){      
	  	  // turn li's in this page into fisheye items, presumtiously:  
   	 	dojo.query("li.bounce", dojo.byId("fishEyeList")).forEach(function(n){
     	 	new dojox.widget.FisheyeLite({ },n);
   		 });
  		gebcount();
  	  });

       function Callback(data,ioArgs) {
		if(data.lenght > 5){alert(data);}
		//else{location.href = "statusseite.php";}
       }       
       function Error(data, ioArgs) {
          //alert('Error when retrieving data from the server!');
       }
	var points;
	
	function doAnimation() {
	  var fadeOut = dojo.fadeOut({node: "welcomeDiv",duration: 1000});
	  var wipeOut = dojo.fx.wipeOut({node: "welcomeDiv",duration: 1000});
	  var animOut = dojo.fx.combine([fadeOut, wipeOut]);
	  animOut.play();

	}	

function decommatize(str) {
  var s = new String(str);
  s = s.replace(',','');
  s = s.replace('.','');
  return parseInt(s);
}

// Variablen
{/literal}
var const_geb = new Array();
{foreach from=$buildingstats item=temp}
	var {$temp.key} = {$temp.value};
	const_geb['{$temp.key}'] = new Array();
	const_geb['{$temp.key}']['verbrauch'] = {$temp.intverbrauch};
{/foreach}
var const_land_min = {$LAND_MIN};
var const_land_max = {$LAND_MAX};
var const_prod_cr = {$PROD_HZ};
var const_prod_erz = {$PROD_EFAS};
var const_prod_fp = {$PROD_FLABS};
var const_prod_nrg = {$PROD_KWS};
var const_std_cr = {$STD_CR};
var const_std_erz = {$STD_ERZ};
var const_std_fp = {$STD_FP};
var const_std_nrg = {$STD_NRG};
var const_cur_nrg = {$CUR_NRG};
var prod_cr = 0;
var prod_erz = 0;
var prod_fp = 0;
var prod_nrg = 0;
var prod_nrg_verbrauch = 0;
var prod_nrg_bilanz = 0;
var prod_ges = 0;
var prod_cr_boni_syn = {$PROD_CR_BONI_SYN};
var prod_cr_boni_frak = {$PROD_CR_BONI_FRAK};
var prod_cr_boni_ges = 0;
var prod_nrg_boni_syn = {$PROD_NRG_BONI_SYN};
var prod_nrg_boni_frak = {$PROD_NRG_BONI_FRAK};
var prod_nrg_boni_ges = 0;
var prod_erz_boni_syn = {$PROD_ERZ_BONI_SYN};
var prod_erz_boni_frak = {$PROD_ERZ_BONI_FRAK};
var prod_erz_boni_ges = 0;
var prod_fp_boni_syn = {$PROD_FP_BONI_SYN};
var prod_fp_boni_frak = {$PROD_FP_BONI_FRAK};
var prod_fp_boni_ges = 0;
var mili_baupreis = 0;
var geb_baupreis = 0;
var geb_bauzeit = 0;
var const_mili_baupreis_max = {$MILI_BAUPREIS};
var const_geb_baupreis_max = {$GEB_BAUPREIS};
var const_geb_bauzeit_max = {$GEB_BAUZEIT}; 
var const_synergie = {$SYNERGIE};
var const_synergie_max = {$SYNERGIE_MAX};
var const_fabs = {$BONI_FABS}
var const_fabs_max = {$BONI_FABS_MAX};
var const_bh_kosten_max = {$BONI_BHS_KOSTEN_MAX};
var const_bh_kosten = {$BONI_BHS_KOSTEN};
var const_bh_zeit_max = {$BONI_BHS_ZEIT_MAX};
var const_bh_zeit = {$BONI_BHS_ZEIT};


{literal}

var gebpoints;
function gebcount(){
	showTippZuVielFabs = 0;
	showTippZuVielBHs = 0;
	showTippSynergie = 0;
	
	// GET
	hzs = dijit.byId('tradecenters').getValue();
	kws = dijit.byId('powerplants').getValue();
	efas = dijit.byId('ressourcefacilities').getValue();
	flabs = dijit.byId('sciencelabs').getValue();
	fabs = dijit.byId('factories').getValue();
	bhs = dijit.byId('buildinggrounds').getValue();
	land = dijit.byId('land').getValue();
	if (!hzs) hzs = 0;
	if (!kws) kws = 0;
	if (!efas) efas = 0;
	if (!flabs) flabs = 0;
	if (!fabs) fabs = 0;
	if (!bhs) bhs = 0;
	gebpoints = hzs + kws + efas + flabs + fabs + bhs;
	if (!land || land < const_land_min) land = const_land_min;

	pro_helper = land / 100;
	
	dijit.byId("gebaudebar").update({progress:gebpoints,maximum:const_land_min})
	if (gebpoints>const_land_min) {
		document.getElementById("gebaudepunkte").innerHTML="<h4 style=\'margin-top: 2px; margin-bottom: 8px;color:red;\'>Gebäude: "+gebpoints+"/"+const_land_min+"ha bebaut. Veringern sie die Gebäudeanzahl!</h4>";
	} else {
		document.getElementById("gebaudepunkte").innerHTML="Gebäude:"+gebpoints+"/"+const_land_min+"ha bebaut.<br><br>";
	}
	
	// BERECHNEN
	
	// Boni (Synergie)
	prod_cr_boni_syn = hzs/pro_helper*const_synergie;
	if (prod_cr_boni_syn >= const_synergie_max) { prod_cr_boni_syn = const_synergie_max; }
	prod_nrg_boni_syn = kws/pro_helper*const_synergie;
	if (prod_nrg_boni_syn >= const_synergie_max) { prod_nrg_boni_syn = const_synergie_max; }
	prod_erz_boni_syn = efas/pro_helper*const_synergie;
	if (prod_erz_boni_syn >= const_synergie_max) { prod_erz_boni_syn = const_synergie_max; }
	prod_fp_boni_syn = flabs/pro_helper*const_synergie;
	if (prod_fp_boni_syn >= const_synergie_max) { prod_fp_boni_syn = const_synergie_max; }
	prod_cr_boni_ges = prod_cr_boni_syn + prod_cr_boni_frak;
	prod_nrg_boni_ges = prod_nrg_boni_syn + prod_nrg_boni_frak;
	prod_erz_boni_ges = prod_erz_boni_syn + prod_erz_boni_frak;
	prod_fp_boni_ges = prod_fp_boni_syn + prod_fp_boni_frak;

	// Prod der Gebs
	prod_cr  = Math.floor(hzs   * const_prod_cr  * (100+ prod_cr_boni_ges)/100);
	prod_erz = Math.floor(efas  * const_prod_erz * (100+ prod_erz_boni_ges)/100);
	prod_fp  = Math.floor(flabs * const_prod_fp  * (100+ prod_fp_boni_ges)/100);
	prod_nrg = Math.floor(kws   * const_prod_nrg * (100+ prod_nrg_boni_ges)/100);

	// Energieverbrauch
	prod_nrg_verbrauch = const_geb['tradecenters']['verbrauch']*hzs + const_geb['powerplants']['verbrauch']*kws +
		const_geb['ressourcefacilities']['verbrauch']*efas + const_geb['sciencelabs']['verbrauch']*flabs +
		const_geb['factories']['verbrauch']*fabs + const_geb['buildinggrounds']['verbrauch']*bhs;
	prod_nrg_bilanz = prod_nrg-prod_nrg_verbrauch;
	nrg_prod_ticks = Math.floor(const_cur_nrg/Math.abs(prod_nrg_bilanz)); 

	prod_ges = Math.floor(prod_cr*const_std_cr + prod_erz*const_std_erz + prod_fp*const_std_fp + prod_nrg_bilanz * const_std_nrg);
	
	// Fabs
	mili_baupreis = fabs/pro_helper*const_fabs;
	if (mili_baupreis > const_fabs_max) { mili_baupreis = const_fabs_max; showTippZuVielFabs = 1; }
	mili_baupreis = const_mili_baupreis_max - mili_baupreis;

	// Bauhöfe
	geb_baupreis = bhs/pro_helper*const_bh_kosten;
	if (geb_baupreis > const_bh_kosten_max) geb_baupreis = const_bh_kosten_max;
	geb_baupreis = const_geb_baupreis_max - geb_baupreis;

	geb_bauzeit = bhs/pro_helper*const_bh_zeit;
	if (geb_bauzeit > const_bh_zeit_max) {geb_bauzeit = const_bh_zeit_max; showTippZuVielBHs = 1; }
	geb_bauzeit = const_geb_bauzeit_max - Math.floor(geb_bauzeit);
	
	// SET
	//document.all.meinAbsatz.
	document.getElementById('prod_cr').innerHTML = prod_cr;
	document.getElementById('prod_erz').innerHTML = prod_erz;
	document.getElementById('prod_fp').innerHTML = prod_fp;
	document.getElementById('prod_nrg').innerHTML = prod_nrg;
	document.getElementById('prod_nrg_verbrauch').innerHTML = prod_nrg_verbrauch;
	document.getElementById('prod_nrg_bilanz').innerHTML = prod_nrg_bilanz;
	document.getElementById('prod_ges').innerHTML = prod_ges;
	document.getElementById('prod_cr_boni_syn').innerHTML = prod_cr_boni_syn;
	document.getElementById('prod_cr_boni_ges').innerHTML = prod_cr_boni_ges;
	document.getElementById('prod_nrg_boni_syn').innerHTML = prod_nrg_boni_syn;
	document.getElementById('prod_nrg_boni_ges').innerHTML = prod_nrg_boni_ges;
	document.getElementById('prod_erz_boni_syn').innerHTML = prod_erz_boni_syn;
	document.getElementById('prod_erz_boni_ges').innerHTML = prod_erz_boni_ges;
	document.getElementById('prod_fp_boni_syn').innerHTML = prod_fp_boni_syn;
	document.getElementById('prod_fp_boni_ges').innerHTML = prod_fp_boni_ges;
	document.getElementById('mili_baupreis').innerHTML = mili_baupreis;
	document.getElementById('geb_baupreis').innerHTML = geb_baupreis;
	document.getElementById('geb_bauzeit').innerHTML = geb_bauzeit;

	if (!hzs && !efas && !kws && !flabs) {
		document.getElementById('tipp_noProdgebs_head').style.display = 'table-row';
		document.getElementById('tipp_noProdgebs').style.display = 'table-row';
	} else {
		document.getElementById('tipp_noProdgebs_head').style.display = 'none';
		document.getElementById('tipp_noProdgebs').style.display = 'none';
	}
	if (hzs) {
		document.getElementById('boni_cr_head').style.display = 'table-row';
		document.getElementById('boni_cr_body').style.display = 'table-row';
	} else {
		document.getElementById('boni_cr_head').style.display = 'none';
		document.getElementById('boni_cr_body').style.display = 'none';
	}
	if (efas) {
		document.getElementById('boni_erz_head').style.display = 'table-row';
		document.getElementById('boni_erz_body').style.display = 'table-row';
	} else {
		document.getElementById('boni_erz_head').style.display = 'none';
		document.getElementById('boni_erz_body').style.display = 'none';
	}
	if (kws) {
		document.getElementById('boni_nrg_head').style.display = 'table-row';
		document.getElementById('boni_nrg_body').style.display = 'table-row';
	} else {
		document.getElementById('boni_nrg_head').style.display = 'none';
		document.getElementById('boni_nrg_body').style.display = 'none';
	}
	if (flabs) {
		document.getElementById('boni_fp_head').style.display = 'table-row';
		document.getElementById('boni_fp_body').style.display = 'table-row';
	} else {
		document.getElementById('boni_fp_head').style.display = 'none';
		document.getElementById('boni_fp_body').style.display = 'none';
	}
	if ((0 < prod_cr_boni_syn  && prod_cr_boni_syn  < const_synergie_max) ||
		(0 < prod_nrg_boni_syn && prod_nrg_boni_syn < const_synergie_max) ||
		(0 < prod_erz_boni_syn && prod_erz_boni_syn < const_synergie_max) || 
		(0 < prod_fp_boni_syn  && prod_fp_boni_syn  < const_synergie_max))
		 showTippSynergie = 1;
	if (showTippZuVielFabs || showTippZuVielBHs || showTippSynergie || prod_nrg_bilanz < 0)
		 document.getElementById('tipp_header').style.display = 'table-row';
	else document.getElementById('tipp_header').style.display = 'none';
	if (showTippZuVielFabs) document.getElementById('tipp_zuvielfabs').style.display = 'table-row';
	else document.getElementById('tipp_zuvielfabs').style.display = 'none';
	if (showTippZuVielBHs) document.getElementById('tipp_zuvielbhs').style.display = 'table-row';
	else document.getElementById('tipp_zuvielbhs').style.display = 'none';
	if (showTippSynergie) document.getElementById('tipp_synergie').style.display = 'table-row';
	else document.getElementById('tipp_synergie').style.display = 'none';
	if (prod_nrg_bilanz < 0) {
		document.getElementById('tipp_negativeBilanz').style.display = 'table-row';
		document.getElementById('prod_nrg_vorrat').innerHTML = 'Energievorrat reicht für ' + nrg_prod_ticks + ' Ticks';
	}
	else {
		 document.getElementById('tipp_negativeBilanz').style.display = 'none';
		 document.getElementById('prod_nrg_vorrat').innerHTML = '';
	}
	if (prod_cr_boni_frak) document.getElementById('boni_cr_frak').style.display = 'table-row';
	else document.getElementById('boni_cr_frak').style.display = 'none';
	if (prod_nrg_boni_frak) document.getElementById('boni_nrg_frak').style.display = 'table-row';
	else document.getElementById('boni_nrg_frak').style.display = 'none';
	if (prod_erz_boni_frak) document.getElementById('boni_erz_frak').style.display = 'table-row';
	else document.getElementById('boni_erz_frak').style.display = 'none';
	if (prod_fp_boni_frak) document.getElementById('boni_fp_frak').style.display = 'table-row';
	else document.getElementById('boni_fp_frak').style.display = 'none';
		
}
	</script>

	<style type="text/css">
	  @import "dojo/dojo/resources/dnd.css";
	  @import "dojo/dojo/tests/dnd/dndDefault.css";
	</style>
	<style type="text/css">
	  .box {
	    margin-top: 10px;
	    color: #292929;
	    width: 400px;
	    position: absolute;
	    top: -1300px;
	    left: 100px;
	    border: 1px solid #BABABA;
	    background-color: #FFFCE2;
	    padding-left: 10px;
 	    padding-right: 10px;
   	    margin-left: 10px;
 	    margin-bottom: 1em;
 	    -o-border-radius: 10px;
	    -moz-border-radius: 12px;
  	    -webkit-border-radius: 10px;
  	    -webkit-box-shadow: 0px 3px 7px #adadad;
	    border-radius: 10px;
	    -moz-box-sizing: border-box;
	    -opera-sizing: border-box;
	    -webkit-box-sizing: border-box;
	    -khtml-box-sizing: border-box;	
	    box-sizing: border-box;
	    overflow: hidden;
	    z-index:2
	  }
	</style>
    <style type="text/css">
	  #fishEyeList {
	    cursor:pointer;
	  }
	  #fishEyeList ul {
	    width:100px;	
	    list-style-type:none;
	  }
  	  .fisheyeTarget {
   	  font-weight:bold;
    	  font-size:12px;
	  }
	  #fishEyeList li {
 	  text-align:right;
 	  padding-bottom:12px;
	  }
	  .ilk {
	  border-top:1px solid #999;
	  color:#666;
	  font:14px Arial,sans-serif;
	  }
     </style>{/literal}

	<br />
	<br />
	<center>
		<br><br>
		<form action="statusseite.php" id="configform" method="POST">
			<input type="hidden" name="WeltuntergangNoChance" value="1">
			<div id="gebaudepunkte">
				Gebäude: 0/ {$LAND_MIN} ha bebaut
				<br />
				<br />
			</div>
	 		<div style="width:300px" maximum="200" id="gebaudebar" progress="0" dojoType="dijit.ProgressBar">
			</div>
			<table width="320" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
				<tr>
					<td>
						<table border="0" cellspacing="1" width="320" cellpadding="2">
							<tr class="tableHead">
								<td width="170">&nbsp;&nbsp;<b>Gebäudetyp</b></td>
								<td width="70" align=center>
									<img src="{$ripf}energie.gif" border="0" align="middle">
								</td>
								<td align="center" width="80">
									<b>ändern</b>
								</td>
							</tr>
						{foreach from=$buildingstats item=temp}
							<tr class="tableInner1">
								<td>
									&nbsp;&nbsp;{$temp.name} {$temp.getBuildingTooltip}
								</td>
								<td align=center>
									<img src="{$ripf}_{$temp.intverbrauch}_energie.gif" border="0">
								</td>
								<td align="center">
									<input type="text" style="width: 5em; color:#000;" dojoType="dijit.form.NumberTextBox" id = "{$temp.key}" name= "{$temp.key}" value="{$temp.value}" onKeyUp = "gebcount()" constraints="{literal}{{/literal}min:0,max:{$LAND_MIN},places:0{literal}}{/literal}" promptMessage= "Nur Zahlen zwischen 0 und {$LAND_MIN} eingeben!" required= "true" invalidMessage= "Ungültiger Wert.">
								</td>
							</tr>
						{/foreach}
						</table>
					</td>
				</tr>
			</table>
			<br>
			<br>
			<table width=100% border=0 cellspacing=0 cellpadding=0 align=center>
				<tr>
					<td width=600><b class=titleH1>Mit dieser Konfiguration kommst du auf folgende Werte:</b></td>
				</tr>
				<tr>
					<td colspan=2 height=1 class=titleLine></td>
				</tr>
			</table>
			<table width=100% cellpadding=0 cellspacing=0>
				<tr>
					<td>
						<table cellspacing=5 cellpadding=0 width=585 border=0>
							<tr>
								<!-- Linke Spalte -->
								<td valign=top>
									<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width=205 height=200>
										<tr>
											<td class="tableHead">Produktion pro Tick</td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
                                                  		<td>Geld </td>
                                                  		<td align=right><b class="highlightAuftableInner" id="prod_cr">{$PROD_CR}</b></td>
                                                  		<td width="30">&nbsp;Cr</td>
                                                	</tr>
                                                	<tr>
                                                  		<td>Erz </td>
                                                  		<td align=right><b class="highlightAuftableInner" id="prod_erz">{$PROD_ERZ}</b></td>
                                                  		<td>&nbsp;t</td>
                                                	</tr>
                                                	<tr>
                                                  		<td>Forschungspunkte </td>
                                                  		<td align=right><b class="highlightAuftableInner" id="prod_fp">{$PROD_FP}</b></td>
                                                  		<td>&nbsp;P</td>
                                                	</tr>
                                              	</table>
											</td>
										</tr>
										<tr>
											<td class="tableHead">Energie </td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Produktion</td>
														<td align=right><b class="highlightAuftableInner" id="prod_nrg">{$PROD_NRG}</b></td>
														<td width="30">&nbsp;MWh</td>
													</tr>
													<tr>
														<td>Verbrauch</td>
														<td align=right><b class="highlightAuftableInner" id="prod_nrg_verbrauch">-{$PROD_NRG_VERBRAUCH}</b></td>
														<td>&nbsp;MWh</td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td colspan=3 bgcolor="#000000"><img src="{$ripf}5E78A4.gif" width="1" height="1" border="0"></td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td>Bilanz<br>&nbsp;</td>
														<td align=right><b class="highlightAuftableInner" id="prod_nrg_bilanz">{$PROD_NRG_BILANZ}</b><br>&nbsp;</td>
														<td>&nbsp;MWh<br>&nbsp;</td>
													</tr>
													<tr>
														<td colspan=3 id="prod_nrg_vorrat"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="tableHead">Gesamtgewinn pro Tick</td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Gewinn</td>
														<td align=right><b class="highlightAuftableInner" id="prod_ges">{$PROD_GES}</b></td>
														<td width="30">&nbsp;Cr</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
                    			<!-- MITTLERE SPALTE -->
								<td valign=top>
									<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width=185 id="boni_none">
										<tr id="tipp_noProdgebs_head">
											<td class="tableHead">Boni</td>
										</tr>
										<tr id="tipp_noProdgebs">
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td colspan=3>Aktuell hast du keine Produktionsgebäude gebaut. Für einen sinnvollen Start benötigst du in der Regel mindestens 240 Produktionsgebäude.</td>
													</tr>
												</table> 
											</td>
										</tr>
										<tr id="boni_cr_head" style="display:none;">
											<td class="tableHead">Boni (Credits)</td>
										</tr>
										<tr id="boni_cr_body" style="display:none;">
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Synergiebonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_syn">{$PROD_CR_BONI_SYN}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr id="boni_cr_frak">
														<td>Fraktionsbonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_syn">{$PROD_CR_BONI_FRAK}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td colspan=3 bgcolor="#000000"><img src="{$ripf}5E78A4.gif" width="1" height="1" border="0"></td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td>Gesamt</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_ges">{$PROD_CR_BONI_GES}</b></td><td>&nbsp;%</td>
													</tr>
												</table> 
											</td>
										</tr>
										<tr id="boni_nrg_head" style="display:none;">
											<td class="tableHead">Boni (Energie)</td>
										</tr>
										<tr id="boni_nrg_body" style="display:none;">
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Synergiebonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_nrg_boni_syn">{$PROD_NRG_BONI_SYN}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr id="boni_nrg_frak">
														<td>Fraktionsbonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_syn">{$PROD_NRG_BONI_FRAK}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td colspan=3 bgcolor="#000000"><img src="{$ripf}5E78A4.gif" width="1" height="1" border="0"></td>
													</tr>
													<tr>	
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td>Gesamt</td>
														<td align=right><b class="highlightAuftableInner" id="prod_nrg_boni_ges">{$PROD_NRG_BONI_GES}</b></td><td>&nbsp;%</td>
													</tr>
												</table> 
											</td>
										</tr>
										<tr id="boni_erz_head" style="display:none;">
											<td class="tableHead">Boni (Erz)</td>
										</tr>
										<tr id="boni_erz_body" style="display:none;">
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
															<td>Synergiebonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_erz_boni_syn">{$PROD_ERZ_BONI_SYN}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr id="boni_erz_frak">
														<td>Fraktionsbonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_syn">{$PROD_ERZ_BONI_FRAK}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td colspan=3 bgcolor="#000000"><img src="{$ripf}5E78A4.gif" width="1" height="1" border="0"></td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td>Gesamt</td>
														<td align=right><b class="highlightAuftableInner" id="prod_erz_boni_ges">{$PROD_ERZ_BONI_GES}</b></td><td>&nbsp;%</td>
													</tr>
												</table> 
											</td>
										</tr>
										<tr id="boni_fp_head" style="display:none;">
											<td class="tableHead">Boni (Forschungspunkte)</td>
										</tr>
										<tr id="boni_fp_body" style="display:none;">
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Synergiebonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_fp_boni_syn">{$PROD_FP_BONI_SYN}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr id="boni_fp_frak">
														<td>Fraktionsbonus</td>
														<td align=right><b class="highlightAuftableInner" id="prod_cr_boni_syn">{$PROD_FP_BONI_FRAK}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td colspan=3 bgcolor="#000000"><img src="{$ripf}5E78A4.gif" width="1" height="1" border="0"></td>
													</tr>
													<tr>
														<td colspan=3><img src="{$ripf}5E78A4.gif" width="1" height="3" border="0"></td>
													</tr>
													<tr>
														<td>Gesamt</td>
														<td align=right><b class="highlightAuftableInner" id="prod_fp_boni_ges">{$PROD_FP_BONI_GES}</b></td><td>&nbsp;%</td>
													</tr>
												</table> 
											</td>
										</tr>
									</table>
								</td>
								<!-- RECHTE SPALTE -->
								<td rowspan=1 valign=top>
									<table cellspacing=1 cellpadding=5 border=0 class="tableOutline" width=185 height=55>
										<tr>
											<td class="tableHead">Sonstige Boni</td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Militäreinheiten-Baupreis</td>
														<td align=right><b class="highlightAuftableInner" id="mili_baupreis">{$MILI_BAUPREIS}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Gebäude-Baupreis</td>
														<td align=right><b class="highlightAuftableInner" id="geb_baupreis">{$GEB_BAUPREIS}</b></td>
														<td width="30">&nbsp;%</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="tableInner1">
												<table cellspacing=0 cellpadding=0 border=0 width=100% class="tableInner1">
													<tr>
														<td>Gebäude-Bauzeit</td>
														<td align=right><b class="highlightAuftableInner" id="geb_bauzeit">{$GEB_BAUZEIT}</b></td>
														<td width="30">&nbsp;Ticks</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr id="tipp_header" style="display:none;">
											<td class=tableHead>Tipps</td>
										</tr>
										<tr id="tipp_zuvielfabs" style="display:none;">
											<td class=tableInner1>
												<table cellspacing=0 cellpadding=0 border=0 width=100% class=tableInner1>
													<tr>
														<td>
															Du hast mehr als 20% Fabriken gebaut. Dies ist wirtschaftlich nicht sinnvoll, da bei 20% bereits ihr maximaler Boni eintritt. Überlegen sie sich gut ob sie so die Konfiguration abschließen wollen.
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr id="tipp_zuvielbhs" style="display:none;">
											<td class=tableInner1>
												<table cellspacing=0 cellpadding=0 border=0 width=100% class=tableInner1>
													<tr>
														<td>
															Du hast mehr als 20% Bauhöfe gebaut. Dies ist wirtschaftlich nicht sinnvoll, da bei 20% bereits ihr maximaler Boni eintritt. Überlegen sie sich gut ob sie so die Konfiguration abschließen wollen.
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr id="tipp_synergie" style="display:none;">
											<td class=tableInner1>
												<table cellspacing=0 cellpadding=0 border=0 width=100% class=tableInner1>
													<tr>
														<td>
															Du hast bei mindestens einer Produktionsart nicht den vollen Synergiebonus ausgeschöpft, dies macht in den meisten Fällen keinen Sinn, da du so auf bis zu 50% erhöhte Produktion verzichtest.
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr id="tipp_negativeBilanz" style="display:none;">
											<td class=tableInner1>
												<table cellspacing=0 cellpadding=0 border=0 width=100% class=tableInner1>
													<tr>
														<td>
															Du verbrauchst mehr Energie als du produzierst. Forsche nach abschluss der Konfiguration möglichst als erstes Basic Trade Programm, um dir am Globalen Markt immer genügend Energie nachkaufen zu können.  
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br />
			<center>
				Der Staff wünsch euch einen fröhlichen Weltuntergang und mögen wir in der Hölle eine neue Runde starten.
			</center>
			<input type="submit" value="Ich lass mich doch nicht veräppeln!">
		</form>
	</center>
{literal}
<script language="JavaScript" type="text/javascript">

function submitForm(){
	dojo.xhrPost({
        url: 'configseite.php',
		form: 'configform',
        load: Callback,
		error: Error });
}
/*
function proofLoaded() {
   if (document.readyState == "complete") {
	   gebcount();
   } else {
      setTimeout('proofLoaded()',500);
   }
}*/


</script>
{/literal}