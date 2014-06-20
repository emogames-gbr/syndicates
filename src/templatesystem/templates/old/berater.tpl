{if $USERINPUT}
{$USERINPUT}
{else}
	<br>
	Ihre persönlichen Berater geben Ihnen einen detaillierten Überblick über alle
	durchgeführten Aktionen. Sie zeigen Ihnen außerdem an, wann bestimmte Entwicklungen zum Abschluss kommen oder wann militärische Aktionen beendet sind.
	<br><br><br>

	{foreach from=$TABLES item=TABLE}
		{if !$TABLE.error}
			<b>
				<u>{$TABLE.name}</u>
			</b>
			<br><br>
			<table width="600" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
				<tr>
					<td>
						<table  border="0" cellspacing="1" width="100%" cellpadding="3">
							<tr class="tableHead">
								<td align=center>#</td>
								{foreach from=$HOURCOL item=CURRENT}
									<td align=middle> &nbsp;{$CURRENT}</td>
								{/foreach}
							</tr>
							{foreach from=$TABLE.rows item=ROW}
								<tr class="tableInner1">
									<td width=105>{$ROW.name}</td>
									{foreach from=$ROW.details item=DETAIL}
										<td align=middle>
											{$DETAIL}
										</td>
									{/foreach}
								</tr>
							{/foreach}
						</table>
					</td>
				</tr>
			</table>
		{else}
			<b>
				<u>{$TABLE.error}</u>
			</b>
			<br>
		{/if}
		<br><br>
	{/foreach}
	
        {if $FOS.error == "nein"}
            <b>
                <u>Forschungen</u>
            </b>
            <br><br>
            <table width="200" cellpadding="0" cellspacing="0" border="0" class="tableOutline">
                <tr>
                    <td>
                        <table  border="0" cellspacing="1" width="200" cellpadding="3">
                            <tr class="tableHead">
                                <td align=center>#</td>
                                <td align=middle>Zeit</td>
                            </tr>
                            <tr class="tableInner1">
                                <td align=middle>{$FOS.name}</td>
                                <td align=middle nowrap>
                                    <a href=berater.php?ia=killqu&what=sc&type={$FOS.link}&killtime=1 class="linkAuftableInner">{$FOS.time}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        {elseif $FOS.error == "ja"}
            <b><u>Zur Zeit werden keine Forschungen betrieben!</u></b><br>
        {/if}
  

	{if $LINKTXT}
	<br><br>
	<a href="berater.php?view={$LINKOPT}" class="linkAufsiteBg">
		{$YELLOWDOT} {$LINKTXT} anzeigen
	</a>
	{/if}
{/if}