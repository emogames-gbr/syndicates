{strip}
		<div style="width:550px; height:570px; text-align:center; overflow:hidden; clear:both;" id="reg_outer">
			<div style="width:2000px; clear:both; margin:0px; padding:0px;">
				<div id="reg_fb" style="height:670px; width:550px; float:left; display:block;">
					<div class="change_regform right">
						Ohne Facebook-Konto registrieren
					</div>
					<div>
						<fb:registration redirect-uri="{$HTTP}index.php?action=register_account_fb"
						{literal}
							fields="[
										{'name':'name'},
										{'name':'first_name'},
										{'name':'last_name'},
										{'name':'email'},
										{'name':'username','description':'Benutzername','type':'text'},
										{'name':'password'},
										{'name':'AGB','description':'Ich akzeptiere die AGB von BETREIBER und die Nutzungsbedingungen von Syndicates','type':'checkbox'},
									]"
						{/literal}
							fb_only="true"
							onvalidate="validate"
							width="550"
							heigth="550"
							id="reg_fb_form"
						</fb:registration>
					</div>
					<table class="register_tab" align="center">
						<tr>
							<td align="center">
								<a href="http://DOMAIN.de/index.php?action=agbs" class="link" target="_blank">Zu den AGB von BETREIBER</a>
							</td>
						</tr>
						<tr>
							<td align="center">
								<a href="?action=nutzungsbedingungen" class="link" target="_blank">Zu den Nutzungsbedingungen von Syndicates</a>
							</td>
						</tr>
						<tr>
							<td align="center" style="font-size:10px;">
								Hinweis: Der Benutzername identifizert dich im Spiel und im Forum.<br />
								Das Passwort wird in Verbindung mit deinem Benutzername für den herkömmlichen Login oder fürs Forum benötigt.
							</td>
						</tr>
					</table>
				</div>
				<div id="reg_normal" style="height:670px; width:550px; float:left; display:block;">
					<div class="change_regform left">
						Zurück zur Registrierung durch Facebook
					</div>
					<form action="?action=register_account" method="post" id="reg_normal_form">
						<input type="hidden" name="modus" id="modus" value="normal" />
						<table class="register_tab" cellspacing="3px" cellpadding="3px;" align="center">
							<colgroup>
								<col width="40%" />
								<col width="60%" />
							</colgroup>
							<tr>
								<td colspan="2" class="table_head">1. Benutzerinformationen (Pflicht)</td>
							</tr>
							<tr>
								<td>Benutzername:</td>
								<td><input type="text" name="username" id="username" class="input" /></td>
							</tr>
							<tr>
								<td>E-Mail-Adresse:</td>
								<td><input type="text" name="email" id="email" class="input" /></td>
							</tr>
							<tr>
								<td>Passwort:</td>
								<td><input type="password" name="password" id="password" class="input" /></td>
							</tr>
							<tr>
								<td>Passwort wiederholen:</td>
								<td><input type="password" name="password_confirmation" id="password_confirmation" class="input" /></td>
							</tr>
							<tr>
								<td colspan="2" class="table_head">2. Pers&ouml;nliche Informationen (freiwillig)</td>
							</tr>
							<tr>
								<td>Vorname:</td>
								<td><input type="text" name="first_name" name="first_name" class="input" /></td>
							</tr>
							<tr>
								<td>Nachname:</td>
								<td><input type="text" name="last_name" name="last_name" class="input" /></td>
							</tr>
							<tr>
								<td colspan="2"><hr width="75%" align="center" /></td>
							</tr>
							<tr>
								<td>Ich akzeptiere die<br /><a href="http://DOMAIN.de/index.php?action=agbs" class="link" target="_blank">AGB von BETREIBER</a><br />und die<br /><a href="?action=nutzungsbedingungen" class="link" target="_blank">Nutzungsbedingungen von Syndicates</a></td>
								<td align="center"><input type="checkbox" name="AGB" id="AGB" /></td>
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
				<div style="position:absolute; bottom:0px; right:0px; font-size:10px;" class="register_tab">
					Syndicates ist ein Produkt von <a href="http://DOMAIN.de/" class="link" target="_blank">BETREIBER</a>
				</div>
			</div>
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
					else{
						return $(this).val();
					}
				};
			})(jQuery);

			$().ready(function(){
				var timeout = new Array();
				$("#reg_normal_form input").each(function(){
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
				
				$('#reg_normal_form').submit(function(){
					var vals = {};
					var form = this;
					$("#reg_normal_form button").attr('disabled', 'disabled');
					var reg_button = $("#reg_normal_form button").html();
					$("#reg_normal_form button").html('wait..');
					$("#reg_normal_form input").each(function(){
						vals[$(this).attr('name')] = $(this).RegVal();
					});
					validate(vals, function(data){
						var p;
						var pc = 0;
						for (p in data) {
							pc++;
						}
						$("#reg_normal_form input").each(function(){
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
							$("#reg_normal_form button").attr('disabled', '');
							$("#reg_normal_form button").html(reg_button);
						}
					});
					return false;
				});
			});
			
			function validate_single(type, val){
				var obj = {};
				obj[type] = val;
				if(type == 'password_confirmation'){
					obj['password'] = $('#password').RegVal();
				}
				obj['modus'] = $('#modus').RegVal();
				$.post('index.php?ajax=true&action=validate_register',
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
				$.post('index.php?ajax=true&action=validate_register',
						{
							'modus' : form.modus,
							'email' : form.email,
							'username' : form.username,
							'password' : form.password,
							'password_confirmation' : form.password_confirmation,
							'AGB' : form.AGB,
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
			var reg_posstart = 0;
			var reg_offset = $('#reg_outer').width();
			FB.XFBML.parse(document.getElementById('reg_fb'));
			$('.change_regform').each(function(){
				$(this).disableSelection();
				$(this).mousedown(function(){
					var tmp = this;
					reg_posstart = reg_offset - reg_posstart;
					$('#reg_fb').animate({'margin-left':'-'+reg_posstart+'px'}, 1000, 'easeOutCubic');
				});
			});
		</script>
		{/literal}
{/strip}