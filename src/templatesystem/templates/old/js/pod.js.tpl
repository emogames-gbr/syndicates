<script language="Javascript">
	var kurs = new Array();
	{foreach from=$lagerressjs item=jsress}
		{$jsress[0]} = {$jsress[1]};
		kurs['{$jsress[0]}']={$jsress[2]};
	{/foreach}
	{literal}
		function maxbuy(tform,max,storeorget) {
			//alert(tform);
			//alert(max);
			document.forms["resform"].elements[tform].value = max;
			document.forms["resform"].inneraction[storeorget].checked = true;
		}
		
		function maxtrans() {
			valueToInsert = eval(document.getElementById('transfer_product').value);
			document.getElementById('transfer_number').value=valueToInsert;
		}
		
		function maxtrans_with_request() {
			valueToInsert = eval(document.getElementById('transfer_product_with_request').value);
			document.getElementById('transfer_number_with_request').value=valueToInsert;
		}

		function maxtrans_aquivalenz(temp) {
			sendvalue = eval(parseInt(document.getElementById('transfer_number_with_request').value,10));
			getvalue = eval(parseInt(document.getElementById('get_number_with_request').value,10));
			sendkurs = kurs[document.getElementById('transfer_product_with_request').value].toFixed(2);
			getkurs = kurs[document.getElementById('get_product_with_request').value].toFixed(2);
			if (temp ==2) {document.getElementById('transfer_number_with_request').value = Math.round(getvalue*getkurs/sendkurs);
			}else{ document.getElementById('get_number_with_request').value = Math.round(sendvalue*sendkurs/getkurs);}
		}
	{/literal}

</script>