{strip}		<div class="back_12">
			<div class="navi_left text_name" style="text-align:center;">
				Die aktuelle Runde<br />
				ist beendet
			</div>
			{if $FBUID}<fb:login-button class="button button_logout" style="background:none" autologoutlink="true" perms="email">Logout</fb:login-button>{else}<a class="button button_logout" href="?action=logout"></a>{/if}
		</div>
{/strip}