{include file='js/agr.js.tpl'}
<center>
{literal}<style>.agr img { cursor:pointer; } </style>{/literal}
<div style="display: none;" id="angr_frak">{$ANGR.frak}</div>
<div style="display: none;" id="vert_frak">{$VERT.frak}</div>
<div style="display: none;" id="target">{if $TARGET}{$TARGET}{/if}</div>
<div style="display: none;" id="input_berichte">{if $REPORTS}{$REPORTS}{/if}</div>
<table width="400" cellspacing="0" class="agr">
<tr valign="top">
	<td>
		<table cellspacing="0" cellpadding="3">
			
			<tr class="tableHead">
				<td colspan="4">
					<center>Ausgang:</center>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center" style="padding:6px;">
					<strong><span id="res_ausgang"></span></strong>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="tableHead2">
					Angreifer:
				</td>
				<td colspan="2" class="tableHead2">
					Verteidiger
				</td>
			</tr>
			<tr class="tableInner1">
				<td>
					Angriffspunkte:<div id="res_ap_voremp_caption" style="display:none; margin-top:3px;">AP vor EMPs:</div>
				</td>
				<td>
					<span id="res_ap">0</span><div id="res_ap_voremp_value" style="display:none; margin-top:3px;">0</div>
				</td>
				<td>
					Verteidigungspunkte:<div id="res_vp_voremp_caption" style="display:none; margin-top:3px;">Neutralisierte AP:</div>
				</td>
				<td>
					<span id="res_vp">0</span><div id="res_vp_voremp_value" style="display:none; margin-top:3px;">0</div>
				</td>
			</tr>
			<tr class="tableInner1">
				<td>
					Verteidigungspunkte:
				</td>
				<td>
					<span id="res_att_vp">0</span>
				</td>
				<td colspan="2" rowspan="2" align="center" valign="center">
					{if !$ROLE}<input type="button" onClick="submit_attack_form()" value="Angreifen" id="form_button" {if !$TARGET}disabled{/if}>{/if}
				</td>
			</tr>
			<tr class="tableInner1">
				<td>
					10/4 Abweichung:
				</td>
				<td id="res_zzv_prozent">
					0&nbsp;%
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="4" align="center">
					Sonstiges
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="1">
					Im Krieg?
				</td>
				<td colspan="3">
					<input type="checkbox" id="gen_iswar" type="changecalc" {if $ISWAR}checked{/if}>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="1">
					Direktes Racherecht?
				</td>
				<td colspan="3">
					<input type="checkbox" id="gen_dir_rr" type="changecalc" {if $ISDIR_RR}checked{/if}>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="1">
					Spionagetyp:
				</td>
				<td colspan="3">
					<select type="changecalc"  type="changecalc"  id="gen_spytype">
						<option value="0">Gen. Milit&auml;rspion</option>
						<option value="1">Konzernspion</option>
					</select>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="1">
					Zeitversatz (Ticks):
				</td>
				<td colspan="3">
					<input type="text" id="gen_delay" value="0">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="1">
					Schutz (Farbe):
				</td>
				<td colspan="3">
					<select id="gen_bashschutz" type="changecalc">
						<option value="0">Wei&szlig;</option>
						<option value="1">Gelb</option>
						<option value="2">Rot</option>
					</select>
				</td>
			</tr>
		</table>
	</td>
	<td>
		<table cellpadding="3" cellspacing="0">
			<tr class="tableHead">
				<td colspan="2" align="center">
					Verluste&nbsp;Angreifer:
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="2">
					<div  id="angr_losses_none" style="display: none;">
						<center>keine</center>
					</div>
					<div id="angr_losses_show">
						<table cellpadding="3" cellspacing="0" width="100%">
							<tr class="tableInner1">
								<td>
									Marines
								</td>
								<td id="angr_losses_marines">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td>
									Ranger
								</td>
								<td id="angr_losses_ranger">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td id="angr_losses_buc_name">
									Stalker
								</td>
								<td id="angr_losses_buc">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td id="angr_losses_auc_name">
									Headhunter
								</td>
								<td id="angr_losses_auc">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td id="angr_losses_huc_name">
									Stealth&nbsp;Bomber
								</td>
								<td id="angr_losses_huc">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td>
									Synmarines
								</td>
								<td id="angr_losses_synarmy">
									0
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="2" align="center">
					Verluste&nbsp;Verteidiger:
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="2">
					<div  id="vert_losses_none" style="display: none;">
						<center>keine</center>
					</div>
					<div id="vert_losses_show">
						<table cellpadding="3" cellspacing="0" width="100%">
							<tr class="tableInner1" id="vert_losses_marines_tr">
								<td>
									Marines
								</td>
								<td id="vert_losses_marines">
									0
								</td>
							</tr>
							<tr class="tableInner1" id="vert_losses_ranger_tr">
								<td>
									Ranger
								</td>
								<td id="vert_losses_ranger">
									0
								</td>
							</tr>
							<tr class="tableInner1" id="vert_losses_buc_tr">
								<td id="vert_losses_buc_name">
									Stalker
								</td>
								<td id="vert_losses_buc">
									0
								</td>
							</tr>
							<tr class="tableInner1" id="vert_losses_auc_tr">
								<td id="vert_losses_auc_name">
									Headhunter
								</td>
								<td id="vert_losses_auc">
									0
								</td>
							</tr>
							<tr class="tableInner1" id="vert_losses_huc_tr">
								<td id="vert_losses_huc_name">
									Stealth&nbsp;Bomber
								</td>
								<td id="vert_losses_huc">
									0
								</td>
							</tr>
							<tr class="tableInner1">
								<td>
									Synranger
								</td>
								<td id="vert_losses_synarmy">
									0
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</td>
	<td>
		<table cellpadding="3" cellspacing="0">
			
			<tr class="tableHead">
				<td colspan="2" align="center">
					Sonstiges:
				</td>
			</tr>
			<tr class="tableInner1">
				<td>
					Landgain
				</td>
				<td id="res_gain">
					0
				</td>
			</tr>
			<tr class="tableInner1">
				<td>
					Heimkehrzeit
				</td>
				<td id="res_heimkehr">
					0
				</td>
			</tr>
			<tr class="tableInner1">
				<td id="res_special_text" type="res_special">
					Max.&nbsp;Spione&nbsp;entf&uuml;hrt:
				</td>
				<td id="res_special_data"  type="res_special">
					0
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="2">
					Spionageberichte:
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center" colspan="2">
					<textarea rows="2" cols="18" id="gen_scans"></textarea>
					<input type="button" id="gen_read_scan" value="Einlesen" onClick="readSpies($('#gen_scans').val());$('#gen_scans').val('');">
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="2">
					<center>Ich bin:</center>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="2" align="center">
				<form action="agr.php" method="get">
					<select id="who_am_i" name="role" onChange="submit()">
						<option id="im_attacker" {if !$ROLE}selected{/if}>Angreifer</option>
						<option id="im_defender"{if $ROLE == "Verteidiger"}selected{/if}>Verteidiger</option>
					</select>
					<input type="hidden" name="target" val="{$TARGETID}">
				</form>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<table width="100%" cellspacing="14" cellpadding="0" class="agr">
<tr valign="top">
	<td width="50%">
		<table cellspacing="0" cellpadding="4" width="100%">
			<tr class="tableHead">
				<td colspan="10">
					<center>Angreiferfraktion{if $TARGET && $ROLE}: {$TARGET}{/if}</center>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="10">
					<table style="cellpadding:3; cellspacing:0;width:100%">
						<tr>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $ANGR.frak == "bf"}1{else}0.2{/if};" id="angr_frak_bf" {if $ANGR.frak == "bf"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}bf-logo-klein.gif" alt="Brute Force" height="22" width="22" border="0">
							<br />BF</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $ANGR.frak == "sl"}1{else}0.2{/if};" id="angr_frak_sl" {if $ANGR.frak == "sl"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}sl-logo-klein.gif" alt="Shadow Labs" height="22" width="22" border="0">
							<br />SL</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $ANGR.frak == "uic"}1{else}0.2{/if};" id="angr_frak_uic" {if $ANGR.frak == "uic"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}uic-logo-klein.gif" alt="United Industries Corporation" height="22" width="22" border="0">
							<br />UIC</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $ANGR.frak == "neb"}1{else}0.2{/if};" id="angr_frak_neb" {if $ANGR.frak == "neb"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}neb-logo-klein.gif" alt="New Economics Block" height="22" width="22" border="0">
							<br />NEB</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $ANGR.frak == "nof"}1{else}0.2{/if};" id="angr_frak_nof" {if $ANGR.frak == "nof"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}nof-logo-klein.gif" alt="Nova Federation" height="22" width="22" border="0">
							<br />NOF</center>
						</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Einheiten{if $ROLE}<span id="scandate_units"></span>{/if}
				</td>
			</tr>
			<tr class="tableHead2">
				<td colspan="4" align="center">
				</td>
				<td colspan="2" align="center">
					Bestand
				</td>
				<td colspan="4" align="left">
					&nbsp;Angriff&nbsp;
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="zehnZuVier();" align="absmiddle" border="none" title="10/4 max">
				</td>
			</tr>
			<form action="angriff.php" target="_blank" method="post" name="attack_form">
				<tr class="tableInner1">
					<td align="center" colspan="4">
						Marines
					</td>
					<td align="center" colspan="2">
						<input type="text" id="angr_rines_da" value="{$ANGR.mil.rines}" size="6">
					</td>
					<td align="center" colspan="4">
						<input type="text" id="angr_rines_att" value="{$ANGR.mil.rinesatt}" size="6" name="unitoffspecs">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_rines_att').val($('#angr_rines_da').val());update();" align="absmiddle" border="none">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_rines_att').val(0);update();" align="absmiddle" border="none">
					</td>
				</tr>
				<tr class="tableInner1">
					<td align="center" colspan="4">
						Ranger
					</td>
					<td align="center" colspan="2">
						<input type="text" id="angr_ranger_da" value="{$ANGR.mil.ranger}" size="6">
					</td>
					<td align="center" colspan="4" id="ranger_att_td">
						<input type="text" id="angr_ranger_att" value="{$ANGR.mil.rangeratt}" size="6" name="unitdefspecs">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_ranger_att').val($('#angr_ranger_da').val());update();" align="absmiddle" border="none">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_ranger_att').val(0);update();" align="absmiddle" border="none">
					</td>
				</tr>
				<tr class="tableInner1">
					<td align="center" colspan="4">
						<div id="angr_buc_name">{if $ANGR.buc.name}{$ANGR.buc.name}{else}Stalker{/if}</div>
					</td>
					<td align="center" colspan="2">
						<input type="text" id="angr_buc_da" value="{$ANGR.mil.buc}" size="6">
					</td>
					<td align="center" colspan="4" id="buc_att_td">
						<input type="text" id="angr_buc_att" value="{$ANGR.mil.bucatt}" size="6" name="unitelites">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_buc_att').val($('#angr_buc_da').val());update();" align="absmiddle" border="none">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_buc_att').val(0);update();" align="absmiddle" border="none">
					</td>
				</tr>
				<tr class="tableInner1">
					<td align="center" colspan="4">
						<div id="angr_auc_name">{if $ANGR.auc.name}{$ANGR.auc.name}{else}Headhunter{/if}</div>
					</td>
					<td align="center" colspan="2">
						<input type="text" id="angr_auc_da" value="{$ANGR.mil.auc}" size="6">
					</td>
					<td align="center" colspan="4" id="auc_att_td">
						<input type="text" id="angr_auc_att" value="{$ANGR.mil.aucatt}" size="6" name="unitelites2">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_auc_att').val($('#angr_auc_da').val());update();" align="absmiddle" border="none">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_auc_att').val(0);update();" align="absmiddle" border="none">
					</td>
				</tr>
				<tr class="tableInner1">
					<td align="center"  colspan="4">
						<div id="angr_huc_name">{if $ANGR.huc.name}{$ANGR.huc.name}{else}Stealth Bomber{/if}</div>
					</td>
					<td align="center" colspan="2">
						<input type="text" id="angr_huc_da" value="{$ANGR.mil.huc}" size="6">
					</td>
					<td align="center" colspan="4" id="huc_att_td">
						<input type="text" id="angr_huc_att" value="{$ANGR.mil.hucatt}" size="6" name="unittechs">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_huc_att').val($('#angr_huc_da').val());update();" align="absmiddle" border="none">
						<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_huc_att').val(0);update();" align="absmiddle" border="none">
					</td>
				</tr>
			<input type="hidden" name="target" value="{$TARGETID}" id="form_target_id">
			<input type="hidden" id="form_synarmeesend" name="synarmeesend" value="on">
			<input type="hidden" name="rid" value="{$TARGET_RID}" id="form_target_rid">
			</form>
			<tr class="tableHead2">
				<td align="center"  colspan="4">
				</td>
				<td align="center" colspan="3">
					Bestand
				</td>
				<td align="center" colspan="3">
					Support
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center"  colspan="4">
					Synmarines&nbsp;<input type="checkbox" id="angr_synarmeesend" title="Syndikatsarmee schicken?" checked="true">
				</td>
				<td align="center" colspan="3">
					<input type="text" id="angr_synrines" value="{if $ANGR.synarmee.offspecs}{$ANGR.synarmee.offspecs}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="3">
					<div id="angr_synrines_support">{if $ANGR.synrines.supp}{$ANGR.synrines.supp}{else}0{/if} </div>
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center"  colspan="4">
					Synranger&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td align="center" colspan="3">
					<input type="text" id="angr_synranger" value="{if $ANGR.synarmee.defspecs}{$ANGR.synarmee.defspecs}{else}999.999{/if}" size="6">
				</td>
				<td align="center" colspan="3">
					<div id="angr_synranger_support">{if $ANGR.synranger.supp}{$ANGR.synranger.supp}{else}999.999{/if} </div>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Land und Geb&auml;ude{if $ROLE}<span id="scandate_gebs"></span>{/if}
				</td>
			</tr>
			<tr class="tableHead2">
				<td colspan="4" align="center">
				</td>
				<td colspan="2" align="center">
					Anzahl
				</td>
				<td colspan="4" align="center">
					Prozent
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					Land
				</td>
				<td align="center" colspan="2">
					<input type="text" id="angr_land_abs" value="{if $ANGR.gebs.land}{$ANGR.gebs.land}{else}400{/if}" size="6">
				</td>
				<td align="center" colspan="4">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					Aussenposten
				</td>
				<td align="center" colspan="2">
					<input type="text" id="angr_aussis_abs" value="{if $ANGR.gebs.aussis}{$ANGR.gebs.aussis}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="angr_aussis_rel" value="0" size="6">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_aussis_rel').val(20);$('#angr_aussis_rel').change();" align="absmiddle" border="none">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_aussis_rel').val(0);$('#angr_aussis_rel').change();" align="absmiddle" border="none">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					Forts
				</td>
				<td align="center" colspan="2">
					<input type="text" id="angr_forts_abs" value="{if $ANGR.gebs.forts}{$ANGR.gebs.forts}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="angr_forts_rel" value="0" size="6">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_forts_rel').val(20);$('#angr_forts_rel').change();" align="absmiddle" border="none">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_forts_rel').val(0);$('#angr_forts_rel').change();" align="absmiddle" border="none">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center" id="angr_specgeb_name" hideclass="angr_specgeb">
					Keine
				</td>
				<td align="center" colspan="2">
					<input type="text" id="angr_specgeb_abs" hideclass="angr_specgeb" value="{if $ANGR.gebs.spec}{$ANGR.gebs.spec}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="angr_specgeb_rel" hideclass="angr_specgeb" value="0" size="6">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#angr_specgeb_rel').val(20);$('#angr_specgeb_rel').change();" align="absmiddle" border="none" hideclass="angr_specgeb">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#angr_specgeb_rel').val(0);$('#angr_specgeb_rel').change();" align="absmiddle" border="none" hideclass="angr_specgeb">
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Forschungen{if $ROLE}<span id="scandate_fos"></span>{/if}&nbsp;
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="angr_fosMax();" align="absmiddle" border="none" title="Worst Case">
				</td>
			</tr>
			{foreach from=$ANGR.forschungen item=FOS}
				<tr class="tableInner1">
					<td colspan="8" align="center">
						{$FOS.name}
					</td>
					<td colspan="2" align="center">
						<select type="changecalc"  id="angr_fos_{$FOS.sname}" style="display:float">
						<option value="0">Stufe 0</option>
						{if $FOS.single}
						<option value="1" {if $ANGR.fos[$FOS.sname] == 1} selected{/if}>Stufe 1</option>
						{else}
						<option value="1" {if $ANGR.fos[$FOS.sname] == 1} selected{/if}>Stufe 1</option>
						<option value="2" {if $ANGR.fos[$FOS.sname] == 2} selected{/if}>Stufe 2</option>
						<option value="3" {if $ANGR.fos[$FOS.sname] == 3} selected{/if}>Stufe 3</option>
						{/if}
						</select>
					</td>
				</tr>
			{/foreach}
			<tr class="tableHead">
				<td colspan="10" align="center">
					Monument
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="10" align="center">
					<select type="changecalc"  id="angr_monu">
						<option value="keins">Kein Monument</option>
						<option value="nebel">Nebel des Krieges</option>
						<option value="schule">Schule des Krieges</option>
						<option value="mauer">Gro&szlig;e Mauer</option>
						<option value="blitz">Blitzkrieg</option>
					</select>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Partnerboni
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="6">
					<input type="checkbox" id="angr_pb_ap" {if $ANGR.pb.ap}checked{/if}> +10% AP
				</td>
				<td colspan="4">
					<input type="checkbox" id="angr_pb_vp" {if $ANGR.pb.vp}checked{/if}> +10% VP
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="6">
					<input type="checkbox" id="angr_pb_verluste" {if $ANGR.pb.verluste}checked{/if}> -10% Verluste
				</td>
				<td colspan="4">
					<input type="checkbox" id="angr_pb_heimkehr" {if $ANGR.pb.heimkehr}checked{/if}> Heimkehr
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="6">
					<input type="checkbox" id="angr_pb_land" {if $ANGR.pb.landgain}checked{/if}> +10% Land
				</td>
				<td colspan="4">
				</td>
			</tr>
		</table>
	</td>
	
	<td width="50%">
	<table cellspacing="0" cellpadding="4" width="100%">
			<tr class="tableHead">
				<td colspan="10">
					<center>Verteidigerfraktion{if $TARGET && !$ROLE}: {$TARGET}{/if}</center>
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="10">
					<table style="cellpadding:3; cellspacing:0;width:100%">
						<tr>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $VERT.frak == "bf"}1{else}0.2{/if};" id="vert_frak_bf" {if $VERT.frak == "bf"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}bf-logo-klein.gif" alt="Brute Force" height="22" width="22" border="0">
							<br />BF</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $VERT.frak == "sl"}1{else}0.2{/if};" id="vert_frak_sl" {if $VERT.frak == "sl"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}sl-logo-klein.gif" alt="Shadow Labs" height="22" width="22" border="0">
							<br />SL</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $VERT.frak == "uic"}1{else}0.2{/if};" id="vert_frak_uic" {if $VERT.frak == "uic"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}uic-logo-klein.gif" alt="United Industries Corporation" height="22" width="22" border="0">
							<br />UIC</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $VERT.frak == "neb"}1{else}0.2{/if};" id="vert_frak_neb" {if $VERT.frak == "neb"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}neb-logo-klein.gif" alt="New Economics Block" height="22" width="22" border="0">
							<br />NEB</center>
						</td>
						<td class="tableInner1" style="cursor: pointer; opacity: {if $VERT.frak == "nof"}1{else}0.2{/if};" id="vert_frak_nof" {if $VERT.frak == "nof"}current="this"{/if}>
							<center><img src="{$IMAGE_PATH}nof-logo-klein.gif" alt="Nova Federation" height="22" width="22" border="0">
							<br />NOF</center>
						</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Einheiten{if !$ROLE}<span id="scandate_units"></span>{/if}
				</td>
			</tr>
			<tr class="tableHead2">
				<td colspan="4" align="center">
				</td>
				<td colspan="3" align="center">
					Bestand
				</td>
				<td colspan="3" align="center" id="vert_units_add">
				
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center" colspan="4">
					Marines
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_rines_da" value="{$VERT.mil.rines}" size="6">
				</td>
				<td align="center" colspan="3" id="vert_rines_add">
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center" colspan="4">
					Ranger
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_ranger_da" value="{$VERT.mil.ranger}" size="6">
				</td>
				<td align="center" colspan="3" id="vert_ranger_add">
					
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center" colspan="4">
					<div id="vert_buc_name">{if $VERT.buc.name}{$VERT.buc.name}{else}Stalker{/if}</div>
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_buc_da" value="{$VERT.mil.buc}" size="6">
				</td>
				<td align="center" colspan="3" id="vert_buc_add">
					
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center" colspan="4">
					<div id="vert_auc_name">{if $VERT.auc.name}{$VERT.auc.name}{else}Headhunter{/if}</div>
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_auc_da" value="{$VERT.mil.auc}" size="6">
				</td>
				<td align="center" colspan="3" id="vert_auc_add">
					
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center"  colspan="4">
					<div id="vert_huc_name">{if $VERT.huc.name}{$VERT.huc.name}{else}Stealth Bomber{/if}</div>
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_huc_da" value="{$VERT.mil.huc}" size="6">
				</td>
				<td align="center" colspan="3" id="vert_huc_add">
					
				</td>
			</tr>
			<tr class="tableHead2">
				<td align="center"  colspan="4">
				</td>
				<td align="center" colspan="3">
					Bestand
				</td>
				<td align="center" colspan="3">
					Support
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center"  colspan="4">
					Synmarines
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_synrines" value="{if $VERT.synarmee.offspecs}{$VERT.synarmee.offspecs}{else}999.999{/if}" size="6">
				</td>
				<td align="center" colspan="3">
					<div id="vert_synrines_support">0</div>
				</td>
			</tr>
			<tr class="tableInner1">
				<td align="center"  colspan="4">
					Synranger
				</td>
				<td align="center" colspan="3">
					<input type="text" id="vert_synranger" value="{if $VERT.synarmee.defspecs}{$VERT.synarmee.defspecs}{else}999.999{/if}" size="6">
				</td>
				<td align="center" colspan="3">
					<div id="vert_synranger_support">0</div>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Land und Geb&auml;ude{if !$ROLE}<span id="scandate_gebs"></span>{/if}
				</td>
			</tr>
			<tr class="tableHead2">
				<td colspan="4" align="center">
				</td>
				<td colspan="2" align="center">
					Anzahl
				</td>
				<td colspan="4" align="center">
					Prozent
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					Land
				</td>
				<td align="center" colspan="2">
					<input type="text" id="vert_land_abs" value="{if $VERT.gebs.land}{$VERT.gebs.land}{else}400{/if}" size="6">
				</td>
				<td align="center" colspan="4">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					Forts
				</td>
				<td align="center" colspan="2">
					<input type="text" id="vert_forts_abs" value="{if $VERT.gebs.forts}{$VERT.gebs.forts}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="vert_forts_rel" value="0" size="6">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#vert_forts_rel').val(20);$('#vert_forts_rel').change();" align="absmiddle" border="none">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#vert_forts_rel').val(0);$('#vert_forts_rel').change();" align="absmiddle" border="none">
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center">
					In Bau
				</td>
				<td align="center" colspan="2">
					<input type="text" id="vert_bau_abs" value="{if $VERT.gebs.inbau}{$VERT.gebs.inbau}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="vert_bau_rel" value="0" size="6">
					 <img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" align="absmiddle" border="none" style="opacity:0.2;">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" align="absmiddle" border="none" style="opacity:0.2;" >
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="4" align="center" id="vert_specgeb_name" hideclass="vert_specgeb">
					Keine
				</td>
				<td align="center" colspan="2">
					<input type="text" id="vert_specgeb_abs" hideclass="vert_specgeb" value="{if $VERT.gebs.spec}{$VERT.geb.spec}{else}0{/if}" size="6">
				</td>
				<td align="center" colspan="4">
					<input type="text" id="vert_specgeb_rel" hideclass="vert_specgeb" value="0" size="6">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="$('#vert_specgeb_rel').val(20);$('#vert_specgeb_rel').change();" align="absmiddle" border="none" hideclass="vert_specgeb">
					<img width="18" height="18" src="{$IMAGE_PATH}_for_down.gif" onclick="$('#vert_specgeb_rel').val(0);$('#vert_specgeb_rel').change();" align="absmiddle" border="none" hideclass="vert_specgeb">
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Forschungen{if !$ROLE}<span id="scandate_fos"></span>{/if}&nbsp;
					<img width="18" height="18" src="{$IMAGE_PATH}_for_up.gif" onclick="vert_fosMax();" align="absmiddle" border="none" title="Worst Case">
				</td>
			</tr>
			{foreach from=$VERT.forschungen item=FOS}
				<tr class="tableInner1">
					<td colspan="8" align="center">
						{$FOS.name}
					</td>
					<td colspan="2" align="center">
						<select type="changecalc"  id="vert_fos_{$FOS.sname}">
						<option value="0">Stufe 0</option>
						{if $FOS.single}
						<option value="1" {if $VERT.fos[$FOS.sname] == 1} selected{/if}>Stufe 1</option>
						{else}
						<option value="1" {if $VERT.fos[$FOS.sname] == 1} selected{/if}>Stufe 1</option>
						<option value="2" {if $VERT.fos[$FOS.sname] == 2} selected{/if}>Stufe 2</option>
						<option value="3" {if $VERT.fos[$FOS.sname] == 3} selected{/if}>Stufe 3</option>
						{/if}
						</select>
					</td>
				</tr>
			{/foreach}
			<tr class="tableHead">
				<td colspan="10" align="center">
					Monument
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="10" align="center">
					<select type="changecalc"  id="vert_monu">
					<option value="keins">Kein Monument</option>
					<option value="nebel">Nebel des Krieges</option>
					<option value="mauer">Gro&szlig;e Mauer</option>
					<option value="trans">Kontinuumtransfunktionator</option>
					</select>
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Partnerboni
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="7">
					<input type="checkbox" id="vert_pb_land" {if $VERT.pb.landloss}checked{/if}> -10% Landverlust
				</td>
				<td colspan="3">
					<input type="checkbox" id="vert_pb_vp" {if $VERT.pb.vp}checked{/if}> +10% VP
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="6">
					<input type="checkbox" id="vert_pb_verluste" {if $VERT.pb.verluste}checked{/if}> -10% Verluste
				</td>
				<td colspan="4">
				
				</td>
			</tr>
			<tr class="tableHead">
				<td colspan="10" align="center">
					Anmerkungen
				</td>
			</tr>
			<tr class="tableInner1">
				<td colspan="10">
					<ul>
						<li>Achtung, die tatsächlichen gegnerischen Unitverluste können von den hier vorhergesagten abweichen!</li>
					</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
</center>
</table>