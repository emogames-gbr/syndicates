{strip}	<form action="?{$HTTP_QUERY}" method="post" name="form_login">
		<input type="hidden" name="action" value="login" />
		<div class="back_1234">
			<div class="navi_left text_name">
				<div class="input"><span id="label_name">Name</span>
					<input class="absolute" type="text" name="user" id="input_name" />
				</div>
			</div>
			<div class="navi_left text_password">
				<div class="input"><span id="label_password">Passwort</span>
					<input class="absolute" type="password" name="password" id="input_password" />
				</div>
				<div style="text-align:center">
					<a href="#" class="navi_left link">Passwort vergessen?</a>
				</div>
			</div>
			<input type="submit" style="display:none;" />
			<div class="logged_in navi_left">Login speichern <input type="checkbox" name="savelogin" style="vertical-align:middle" /></div>
			<a class="button button_login" href="javascript: document.form_login.submit()"></a>
			<div class="navi_left" style="position:absolute; top:156px; width:201px; left:0px; text-align:center">
				<hr width="20%" style="display:inline-block; height:1px; background-color:#999; color:#999; margin-right:15px; border:none" noshade="noshade" />
				oder
				<hr width="20%" style="display:inline-block; height:1px; background-color:#999; color:#999; margin-left:15px; border:none" noshade="noshade" />
			</div>
			<div style="position:absolute; top:179px; left:7px; text-align:center; width:198px;">
				<fb:login-button autologoutlink="true" scope="email">{if $FBUID}Logout{else}Login{/if}</fb:login-button>
			</div>
		</div>
	</form>
{/strip}