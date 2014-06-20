		<br />
		<center>Legen Sie fest, welche Mitglieder Ihres Syndikats die Foren benutzen dürfen:</center><br />
		<table width="350px" align="center" class="tableOutline" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<table width="100%" cellspacing="1" cellpadding="4">
						<tr class="tableHead">
							<td>Konzernname</td>
							<td width="70px" align="center">Aktion</td>
						</tr>
						{foreach name=all_konzis item=KONZERN from=$KONZERNE}
							<tr class="tableInner1">
								<td>{$KONZERN.name}</td>
								<td align="center"><a href="{$SITE}.php?action=manageaccessrights&kid={$KONZERN.id}" class=linkAuftableInner>{if $KONZERN.access}sperren{else}freischalten{/if}</a></td>
							</tr>
						{/foreach}
					</table>
				</td>
			</tr>
		</table>
