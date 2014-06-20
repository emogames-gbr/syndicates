{if !$view}
	<br>
	<br>
	<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline" >
		<tr>
			<td align=center class="tableHead" colspan=2>Grafikpaket einstellen</td>
			<form enctype="multipart/form-data" action=gpacks.php method=post>
				<input name="action" type="hidden" value="insert_package">
		</tr>
		<tr>
			<td class="tableHead2" colspan=2> 
				Hinweise: Es werden nur Zip Dateien mit maximaler Gr&ouml;&szlig;e von 2MB akzeptiert.<br>
				In dem <u><a class="linkAufsiteBg" href="{$WWWDATA}/syn_gpacks/example/example.zip">Beispielgrafikpaket</a></u>
				sind alle Dateien definiert, die ge&auml;ndert werden können. Die Ordnerstruktur und Dateinamen
				müssen beibehalten werden!<br>
				Nach dem Hochladen wird das Grafikpaket von uns gepr&uuml;ft und freigeschaltet.
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left>
				Name des Grafikpakets:
			</td>
			<td class="tableInner1" align=left>
				<input name="name" value="{$name}">
				{if $showmygpacks}
					<select name="update_for"><option value="">Neues Grafikpaket";
					{foreach from=$mygpacks item=temp}					
						<option {$temp.o_disabled} value="{$temp.gpack_id}">
							{$temp.name} {$temp.o_addstring}
						</option>
					{/foreach}
					</select>
				{/if}
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left>
				Pfad w&auml;hlen:
			</td>
			<td class="tableInner1" align=left colspan=2>
				<input type="file" name="data">
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align=left>
				Beschreibung
			</td>
			<td class="tableInner1" align=left colspan=2>
				<textarea rows=7 cols=30 name="description">{$description}</textarea>
			</td>
		</tr>
		<tr>
			<td class="tableInner1" align="center" colspan="2">
				<input type="submit" value="Grafikpaket hochladen">
			</td>
		</tr>
	</table>
	<br>
{elseif $view == "showall"}
	<br>
	<table cellpadding="5" cellspacing="1" border="0" width="600" class="tableOutline" >
		<tr>
			<td align=center class="tableHead" colspan=3>
				Verfügbare Grafikpakete
			</td>
		</tr>
	{foreach from=$gpacks item=temp}
		<tr class="tableInner1">
			<td width=170>
				<b>{$temp.name}</b><br>
				{$temp.description}
			</td>
			<td width=240>
				{if $temp.shexists}
					<center>
						<a href="{$WWWPUB}fullshots.php?url={$temp.ssbig}" target="_blank">
							<img width="200" border=0 src="{$temp.ssmall}">
						</a>
					</center>
				{else}
					<b>Kein Screenshot vorhanden</a>
				{/if}
			</td>
			<td width = 190>
				<li>
					<a class="linkAufTableInner" href="{$WWWDATA}syn_gpacks/{$temp.gpack_id}/{$temp.gpack_id}.zip">Grafikpaket herunterladen</a>
				<br><br>
				<li>
					<a class="linkAufTableInner" href="options.php?inner=changeset&gpack_id={$temp.gpack_id}">Grafikpaket verwenden</a>
			</td>
		</tr>
	{/foreach}
	</table>	
{/if}