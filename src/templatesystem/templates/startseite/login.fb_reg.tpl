{strip}		<div class="back_123">
			<div class="navi_left text_name" style="left:50px; text-align:center;">
				Du bist bereits bei<br />BETREIBER angemeldet?
				<div style="margin-top:7px;">
					<a class="lightbox" href="?action=fb_connect"><img src="images/startseite/fb_connect_button.png" /></a>
				</div>
			</div>
			<div class="navi_left text_name" style="left:27px; top:88px; text-align:center;">
				Ansonsten kannst du dich<br /><a class="navi_left link lightbox" href="?action=anmeldung">kostenlos registrieren</a>
			</div>
			{if $FBUID}<fb:login-button class="button button_logout" style="background:none" autologoutlink="true" perms="email">Logout</fb:login-button>{else}<a class="button button_logout" href="?action=logout"></a>{/if}
		</div>
{/strip}