{literal}
<script type="text/javascript">
<!--
$('document').ready(function() {
	{/literal}{foreach from=$LEVELS item=LEVEL}{literal}
	$('#toggle_stufe_{/literal}{$LEVEL.id}{literal}').toggle(function() {
			$('#toggle_stufe_{/literal}{$LEVEL.id}{literal}_symbol').text("[-]");
			$('div[type="stufe_{/literal}{$LEVEL.id}{literal}_toggletext"]').slideDown(1000);
		}, function() {
			$('#toggle_stufe_{/literal}{$LEVEL.id}{literal}_symbol').text("[+]");
			$('div[type="stufe_{/literal}{$LEVEL.id}{literal}_toggletext"]').slideUp(1000);
		});
	{/literal}{/foreach}{literal}
});
-->
</script>
{/literal}