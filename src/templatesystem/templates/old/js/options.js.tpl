{literal}
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('.ajax').each(function(){
		var that = this;
		var submit = $(this).find(':submit');
		var subcell = submit.parent();
		var subtimer;
		submit.css('display', 'none');
		$(this).find('[type=checkbox]').change(function(){
			var qry = $($(this)[0].form).serialize();
			$(that).find('input').attr('disabled', true);
			subcell.html('bitte warten');
			if(subtimer != null) window.clearTimeout(subtimer);
			$.post('?ajax=true', qry)
			.success(function(d){
				$(that).find('input').removeAttr('disabled');
				subcell.html('gespeichert');
				subtimer = window.setTimeout(function(){ subcell.html('') }, 1000);
			});
		});
	});
});
-->
</script>
{/literal}