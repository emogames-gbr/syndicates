<table width="120" cellspacing="0" cellpadding="0" border="0" class="tableOutline">
	<tr>
		<td width="120">
			<table width="120" cellspacing="1" cellpadding="0" border="0">
				<tr>
					<td>
						<table width="120" height="16" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td class="menueHead" background="{$IMAGE_PATH}menubar.png" class="ver11w" align="left" width="120">
									<b>&nbsp;&nbsp;Testserver</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="120" cellspacing="0" cellpadding="3" border="0">
							{foreach from=$MENU_TESTSERVER item=menuPoint}
							<tr>
								<td class="menueInner">
									{if $menuPoint.linkfilename}<a href="{$menuPoint.linkfilename}" class="linkMenue">
										<img src="{$IMAGE_PATH}dot-gelb.gif" hspace="5" border="0" />
										{$menuPoint.name}
									</a>{else}{$menuPoint.name}{/if}
								</td>
							</tr>
							{/foreach}
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table><img src="images/5E78A4.gif" width="1" height="6" border="0" />