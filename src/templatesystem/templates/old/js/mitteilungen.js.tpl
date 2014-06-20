<script language="JavaScript">
	{literal}
	function checkAll(thecheckbox) {
		if (thecheckbox.checked == true) {
			for (var i=0;i<document.items.elements.length;i++) 	{
				var e = document.items.elements[i];
				if ((e.type=='checkbox') && !e.disabled) 	{
					e.checked =	true;
											}		
										}
								}
		else 	{
			for (var i=0;i<document.items.elements.length;i++) {
				var e = document.items.elements[i];
				if ((e.type=='checkbox') && !e.disabled) 	{
					e.checked =	false;
											}		
										}
			}
	}
	{/literal}		
</script>