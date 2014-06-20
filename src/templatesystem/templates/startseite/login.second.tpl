{strip}	<form action="php/login.php" method="post" name="form_login">
		<input type="hidden" name="action" value="login" />
		<div class="back_123">
			<div class="navi_left text_captcha">
				<!--<img src="images/startseite/cappy{1|rand:2}.gif" />-->
			</div>
			<div class="navi_left text_codeinput">
				<div class="input"><span id="label_codeinput">Logincode</span>
					<input class="absolute" type="text" name="codeinput" id="input_codeinput" />
				</div>
			</div>
			<input type="submit" style="display:none;" />
			{if $PATHSET}<div class="navi_left" style="bottom:50px; height:21px; left:15px;" title="Grafikpaket verwenden"><input name="usepacket" type="checkbox" checked="checked" />GrPaket</div>{/if}
			<a class="button button_zum_spiel" href="javascript: document.form_login.submit()"{if $PATHSET} style="left:93px;"{/if}></a>
			{if $FBUID}<fb:login-button class="button button_logout" style="background:none" autologoutlink="true" perms="email">Logout</fb:login-button>{else}<a class="button button_logout" href="?action=logout"></a>{/if}
		</div>
	</form>
{/strip}