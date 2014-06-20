<!-- Ausgabetabelle schließeten (wird in header geöffnet)-->
</td></tr></table>

</td>
<td class="siteGround"  valign="top"></td>
</tr>
</table>


<table width="100%" class="siteGround" cellpadding=0 cellspacing=0 border=0>
    <tr><td colspan=3 height=20></td></tr>
    <tr><td colspan=3 height=1 class="tableOutline"></td></tr>
    <tr><td colspan=3 height=10></td></tr>
<tr>
  <TD vAlign=top width=170 class="siteGround"><a href="../index.php?action=impressum" class="linkAufsiteBg" target="_blank"&copy; COPYRIGHT<BR>&nbsp;&nbsp;BETREIBERNAME</a></TD>
  <TD vAlign=top class="siteGround"><a href="{$FFORENLINK}" class="linkAufsiteBg"><b>Fragen zum Spiel</b></a> - <a href="../index.php?action=nutzungsbedingungen" class="linkAufsiteBg"  target="_blank"><b>Nutzungsbedingungen</b></a> - <a href="../index.php?action=impressum" class="linkAufsiteBg"  target="_blank"><b>Impressum</b></a><br>
  BETREIBERNAME<BR><BR></TD>
<td valign=top>
{if $LOGIN}
	<table cellpadding=0 cellspacing=0>
	<tr>
		<td><a href=options.php class=linkAufsiteBg><img src="{$IMAGE_PATH}dot-gelb.gif" hspace=\"5\" border=\"0\"> Optionen</a></td>
        <td>{if $NOTEPAD}<a href=notes.php class=linkAufsiteBg><img src="{$IMAGE_PATH}dot-gelb.gif" hspace=\"5\" border=\"0\"> Notizen</a>{/if}</td>
	</tr>
	<tr>
		<td><a href=stats.php class=linkAufsiteBg><img src="{$IMAGE_PATH}dot-gelb.gif" hspace=\"5\" border=\"0\"> Statistiken</a></td>
		<td><a href=report.php class=linkAufsiteBg><img src="{$IMAGE_PATH}dot-gelb.gif" hspace=\"5\" border=\"0\"> Support</a></td>
	</tr>
	</table>
{/if}
	</td>
</tr>
</table>
</td>
</tr></table>
</td>
 <!-- Jetzt Skyscraper zelle-->
<td valign=\"top\" style=\"width:320px;padding-left:10px\">{$FOOTER_GTG}</td></tr></table><br>
</div>
</div>
{$FLASH_IN}
{$ANALYTICS}
</body></html>
