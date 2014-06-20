{literal}
<script type="text/javascript">
<!--
$(document).ready(function(){
	var klicks = new Array();
	$('.knowlist_name').css('cursor', 'pointer').mousedown(function(){
		if(klicks[$(this).attr('rel')]){
			$('#'+$(this).attr('rel')).slideUp(1000, 'easeOutElastic');
			klicks[$(this).attr('rel')] = false;
		}
		else{
			$('#'+$(this).attr('rel')).slideDown(1000, 'easeOutElastic');
			klicks[$(this).attr('rel')] = true;
		}
	});
});
-->
</script>
{/literal}