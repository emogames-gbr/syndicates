{strip}	<div style="width:450px; text-align:center;">
			<form action="create.php" method="post" id="reg_konz_form">
				<table class="register_tab" align="center" style="width:100%;">
					<colgroup>
						<col width="40%" />
						<col width="60%" />
					</colgroup>
					<tr>
						<td colspan="2" class="table_head">Konzern für Runde {$GLOBALS.round-2} erstellen</td>
					</tr>
					<tr>
						<td colspan="2" align="center">Fraktion:</td>
					</tr>
					<tr>
						<td colspan="2">
							<table width="100%" cellpadding="5px" id="race">
								<colgroup width="{$FRAKS_SPAN}%" span="{$FRAKS_ACTIVE}"></colgroup>
								<tr>
									{foreach from=$FRAKTIONEN item=FRAKS name=fraks}{if $FRAKS.active}<td align="center" rel="{$FRAKS.tag}" id="{$FRAKS.tag}" class="frak_selector">
										<label for="{$FRAKS.tag}_radio"><img src="images/startseite/100_{$FRAKS.race}.png" style="max-height:60px;" alt="{$FRAKS.shortname}" /><br />
										{$FRAKS.shortname}</label><br />
										<input type="radio" name="race" class="race" value="{$FRAKS.race}" id="{$FRAKS.tag}_radio"{if $FRAK == $FRAKS.race} checked="checked"{/if} /><br />
									</td>{/if}{/foreach}
								</tr>
								<tr>
									<td style="font-size:10px;" colspan="{$FRAKS_ACTIVE}" align="center">Infos zu den Fraktionen gibt´s in der <a href="http://www.syndicates-wiki.de/index.php?title=Fraktionen" target="_blank">Synpedia</a></td>
								</tr>
							</table>
						</td>
					</tr>
					<!--
					<tr>
						<td>Name deines KonzernChefs:</td>
						<td align="center"><input type="text" name="rulername" id="rulername" class="input" /></td>
					</tr> -->
					<input type="hidden" name="rulername" value="defaultname">
					<tr>
						<td>Name deines Konzerns:</td>
						<td align="center"><input type="text" name="syndicate" id="syndicate" class="input" /></td>
					</tr>
					<tr>
						<td colspan="2"><hr width="75%" align="center" /></td>
					</tr>
					<tr>
						<td>Ich akzeptiere die<br /><a href="http://DOMAIN.de/index.php?action=agbs" class="link" target="_blank">AGB von BETREIBER</a><br />und die<br /><a href="?action=nutzungsbedingungen" class="link" target="_blank">Nutzungsbedingungen von Syndicates</a></td>
						<td align="center"><input type="checkbox" name="agb" id="agb" /></td>
					</tr>
					<tr>
						<td colspan="2"><hr width="75%" align="center" /></td>
					</tr>
					<tr>
						<td colspan="2" align="center"><button type="submit" class="submit">Anmelden</button></td>
					</tr>
				</table>
			</form>
		</div>
		{literal}
		<script type="text/javascript">
			(function($){
				$.fn.RegVal = function(){
					if($(this).attr('type') == 'checkbox'){
						if($('#'+$(this).attr('id')+':checked').length > 0)
							return $('#'+$(this).attr('id')+':checked').val();
						return false;
					}
					else if($(this).attr('type') == 'radio'){
						if($('.'+$(this).attr('name')+':checked').length > 0)
							return $('.'+$(this).attr('name')+':checked').val();
						return false;
					}
					else{
						return $(this).val();
					}
				};
			})(jQuery);
			
			$().ready(function(){
				change_radios();
				 $('input[name="race"]').change(function(){
					change_radios();
				});
				var timeout = new Array();
				$("#reg_konz_form input").each(function(){
					var n = $(this).attr('name');
					var tmp = this;
					$(this).blur(function(){
						validate_single(n, $(tmp).RegVal());
					}).keyup(function(){
						clearTimeout(timeout[n]);
						timeout[n] = setTimeout(function(){
							validate_single(n, $(tmp).RegVal());
						},500);
					});
				});
				
				$('#reg_konz_form').submit(function(){
					var vals = {};
					var form = this;
					$("#reg_konz_form button").attr('disabled', 'disabled');
					var reg_button = $("#reg_konz_form button").html();
					$("#reg_konz_form button").html('wait..');
					$("#reg_konz_form input").each(function(){
						vals[$(this).attr('name')] = $(this).RegVal();
					});
					validate(vals, function(data){
						var p;
						var pc = 0;
						for (p in data) {
							pc++;
						}
						$("#reg_konz_form input").each(function(){
							build_hint($(this).attr('name'), '', false);
						});
						if(pc == 0){
							form.submit();
						}
						else{
							var tmp;
							for(tmp in data){
								build_hint(tmp, data[tmp], true);
							}
							$("#reg_konz_form button").attr('disabled', '');
							$("#reg_konz_form button").html(reg_button);
						}
					});
					return false;
				});
			});
			
			
			function validate_single(type, val){
				var obj = {};
				obj[type] = val;
				$.post('index.php?ajax=true&action=validate_konzern',
						obj,
						function(data){
							cb_single(type, data.errors);
						},
						'json'
				);
			}
			function cb_single(type, msg){
				if(!msg[type]){
					build_hint(type, msg[type], false);
				}
				else{
					build_hint(type, msg[type], true);
				}
			}
			
			function build_hint(type, msg, action){
				if(!action){
					$('#'+type).removeClass('error');
					$('#'+type).addClass('success');
					$('#'+type+'_hint').remove();
				}
				else{
					$('#'+type).removeClass('success');
					$('#'+type).addClass('error');
					$('#'+type+'_hint').remove();
					$('<div>', {
							'id'	: type+'_hint',
							'css'	: {
										'color' : '#910005',
										'font-size' : '10px'
							},
							'html'	: msg
					}).appendTo($('#'+type).parent());
				}
			}
			
			function validate(form, cb) {
				$.post('index.php?ajax=true&action=validate_konzern',
						{
							'race' : form.race,
							'rulername' : form.rulername,
							'syndicate' : form.syndicate,
							'agb' : form.agb
						},
						function(data){
							var p;
							var pc = 0;
							for (p in data.errors) {
								pc++;
							}
							if(pc == 0)	cb({});
							else cb(data.errors);
						},
						'json'
				);
			}
			
			function change_radios(){
				$('.frak_selector').each(function(){
					var id = $(this).attr('rel');
					if($('#'+id+'_radio:checked').length == 0){
						$('#'+id).fadeTo(200, 0.5);
					}
					else{
						$('#'+id).fadeTo(200, 1);
					}
				});
			}
		</script>
		{/literal}
{/strip}