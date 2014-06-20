{literal}
<script type="text/javascript">
<!--
	var kats = new Array();
	
	{/literal}
	{foreach item=KAT from=$KATS}
		var A{$KAT.kategorie} = new Array();
		kats.push(A{$KAT.kategorie});
		{foreach item=ID from=$KAT.ids}
			A{$KAT.kategorie}.push({$ID});
		{/foreach}
	{/foreach}
	{literal}
	
	function $(string) {
		return document.getElementById(string);
	}
	
	displaytc = function(box) {
		for (var i=0; i < 4; i++) {
			var checkname = 't'+i;
			var check = document.getElementById(checkname).checked;
			for (var t = 0; t < kats[i].length; t++) {
				var tempname = 'te_'+kats[i][t];
				if (check) {
					document.getElementById(tempname).style.display = '';
				}
				else {
					document.getElementById(tempname).style.display = 'none';
				}
			}
			
		}
	}
	
	tcuncheckall = function() {
		for (var i=0; i < 4; i++) {
			var temp = 't'+i;
			$(temp).checked=false;
			displaytc();
		}
	}
	
	tccheckall = function() {
		for (var i=0; i < 4; i++) {
			var temp = 't'+i;
			$(temp).checked = true;
			displaytc();
		}
	}
	
-->
</script>
{/literal}