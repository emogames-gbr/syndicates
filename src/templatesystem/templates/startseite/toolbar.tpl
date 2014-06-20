{strip}	<div class="toolbar_outer">
			{if $IS_MOBILE < 0}<div class="toolbar2 visible bounce" id="toolbar2_small">
				<div class="toolbar2_left"></div>
				<div class="toolbar2_content">
					<img src="images/startseite/icon_i.png" style="width:24px; height:24px;" alt="i" />
				</div>
				<div class="toolbar2_right"></div>
			</div>
			<div class="toolbar2" id="toolbar2_big">
				<div class="toolbar2_left"></div>
				<div class="toolbar2_content">
					<a href="?start=mobile" class="gelblink">Zurück</a> zur<br />mobilen Version
				</div>
				<div class="toolbar2_right"></div>
			</div>
			{/if}
			<div class="toolbar visible{if $ROUND_ICON != 'green'} bounce{/if}" id="toolbar_small">
				<div class="toolbar_left"></div>
				<div class="toolbar_content">
					<img src="images/startseite/icon_i_{$ROUND_ICON}.png" style="width:24px; height:24px;" alt="{$ROUND_ICON}" />
				</div>
				<div class="toolbar_right"></div>
			</div>
			<div class="toolbar" id="toolbar_big">
				<div class="toolbar_left"></div>
				<div class="toolbar_content">
					{if $GLOBALS.roundstatus == 2}
					<strong>Die aktuelle Runde ist beendet</strong><br />
					Anmeldephase: {$ANMELDE_DATUM}, Rundenstart: {$RUNDENSTART_DATUM}
					{elseif $GLOBALS.roundstatus == 1}
					<strong>Runde läuft</strong><br />
					Rundenende: {$RUNDENENDE_DATUM}
					{else}
					<strong>Anmeldephase läuft</strong><br />
					Rundenstart: {$RUNDENSTART_DATUM}
					{/if}
				</div>
				<div class="toolbar_right"></div>
			</div>
		</div>
{/strip}