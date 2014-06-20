{strip}		<div class="back_12">
			<div class="navi_left text_name" style="left:36px; text-align:center;">
				<a href="?action=anmeldung" class="navi_left link lightbox" style="font-size:14px; font-weight:bold;">Konzern erstellen</a><br />
				oder eine Fraktion wählen &rarr;
			</div>
			{if $FBUID}<fb:login-button class="button button_logout" style="background:none" autologoutlink="true" perms="email">Logout</fb:login-button>{else}<a class="button button_logout" href="?action=logout"></a>{/if}
		</div>
{/strip}