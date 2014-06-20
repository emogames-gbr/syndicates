<form action="clientScoring.php" method="post" id="ss-form" style="margin-left:75px"> 
	<br /> 
	{foreach from=$RADIO item=VL}
	<strong>{$VL.question}</strong>
	<br />
	<ul>
		{foreach from=$VL.answer item=ANSWER}
			<input type="radio" name="{$VL.name}" value="{$ANSWER.vl}"> 
			{$ANSWER.answer}<br />
		{/foreach}
	</ul>
	<br />
	{/foreach}
	<br />
	{foreach from=$CHECKBOX item=VL}
	<strong>{$VL.question}</strong> <em>(mehrfach Auswahl m&ouml;glich)</em>
	<br />
	<ul>
		{foreach from=$VL.answer item=ANSWER}
		<li>
			<input type="checkbox" name="{$VL.name}{$ANSWER.vl}" value="{$ANSWER.vl}" id="{$VL.name}{$ANSWER.vl}"> 
			{$ANSWER.answer}
		</li>
		{/foreach}
	</ul>
	<br />
	{/foreach}
	<br />
	<strong>Bewerte bitte folgende Elemente</strong> <em>(0 keine Angabe, 1-5 schlecht bis sehr gut)</em>
	<br />
	<br />
	{foreach from=$RADIONR item=VL}
	<strong>{$VL.title}</strong>
	<br />
	<table style="text-align:center;">
		<tr>
			<td></td> 
			<td>0</td>
			<td>1</td>
			<td>2</td>
			<td>3</td>
			<td>4</td>
			<td>5</td> 
			<td></td>
		</tr> 
		<tr>
			<td></td> 
			<td><input type="radio" name="{$VL.name}" value="0" id="group_14_1"></td>
			<td><input type="radio" name="{$VL.name}" value="1" id="group_14_2"></td>
			<td><input type="radio" name="{$VL.name}" value="2" id="group_14_3"></td>
			<td><input type="radio" name="{$VL.name}" value="3" id="group_14_4"></td>
			<td><input type="radio" name="{$VL.name}" value="4" id="group_14_5"></td>
			<td><input type="radio" name="{$VL.name}" value="5" id="group_14_6"></td> 
			<td></td>
		</tr>
	</table>
	<br />
	{/foreach}
	<br />
	{$UEBER_TEXTAREA}
	<br />
	<textarea name="comment" rows="10" cols="50" id="entry_19"></textarea> 
	<br />
	<br />
	<input type="submit" name="submit" value="Absenden">
</form> 