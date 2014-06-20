{literal}
<script type="text/javascript">
<!--
$('document').ready(function() {
	{/literal}
	{foreach from=$PLAYERS item=PLAYER}
		{if $PLAYER.id != ''}
		{literal}
		$('input[value={/literal}{$PLAYER.id}{literal}]').change(function() {
			$("#target_id").attr('value', '{/literal}{$PLAYER.id}{literal}');
    		$("#target_name").text($("#id_{/literal}{$PLAYER.id}{literal}").text());
    		$("#target_select").removeAttr('disabled');
    		$("#target_select option[value=-1]").remove();
    		$("#target_submit").removeAttr('disabled');
    		$("#extra_ops").show(1000);
    	});
    	{/literal}
    	{/if}
	{/foreach}
	{literal}
});

{/literal}
var const_NORMAL = 1; 
var const_STEALACTIONS = {$STEALACTIONS}; // Anzahl der Spionageanktionen, die Diebstahl benötigt.
var const_KILLSCIENCESACTIONS = {$KILLSCIENCESACTIONS}; // Anzahl Spionageaktionen, die killsciences benötigt // Seit Runde 42 wieder 15 aktionen, vorher 10 
var const_KILLBUILDINGSACTIONS = {$KILLBUILDINGSACTIONS}; // Anzahl der Spionageaktionen, die killbuildings benötigt.
var const_KILLUNITSACTIONS = {$KILLUNITSACTIONS}; //Anzahl der Spionageaktionen, die killunits benötigt.
var const_DELAYAWAYACTIONS = {$DELAYAWAYACTIONS};
{literal}


function updateZusatzOpsBoni() {
	boni = 0;
	std = {/literal}{$ZUSATZOPS_BONI}{literal};
	type = document.getElementById('target_select').value;
	switch (type) {
	case 'getpodpoints': 
	case 'getmetal': 
	case 'getenergy': 
	case 'getsciencepoints':
	case 'getmoney': ops = const_STEALACTIONS; break;
	case 'killsciences': ops = const_KILLSCIENCESACTIONS; break;
	case 'killbuildings': ops = const_KILLBUILDINGSACTIONS; break;
	case 'killunits': ops = const_KILLUNITSACTIONS; break;
	case 'delayaway': ops = const_DELAYAWAYACTIONS; break;
	default: ops = const_NORMAL;
	}
	
	document.getElementById('zusatzops_boni').innerHTML = Math.floor(std/ops); 
}

-->
</script>
{/literal}