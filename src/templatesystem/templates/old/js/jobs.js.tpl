<script language="JavaScript">

	tempid = {$JS_target_id};
	actioncosts = new Array();
	
	{foreach from=$JS_actioncost item=actione}

		actioncosts['{$actione[0]}'] = new Array();
		actioncosts['{$actione[0]}']['minpreis'] = {$actione[1]};
		actioncosts['{$actione[0]}']['preis'] = {$actione[2]};
		actioncosts['{$actione[0]}']['maxpreis'] = {$actione[3]};
		actioncosts['{$actione[0]}']['step'] = {$actione[4]};
		actioncosts['{$actione[0]}']['defaultgain'] = {$actione[5]};
		
	{/foreach}
	
	{literal}
	function setUp(){
		type = (document.getElementById('at_type').value);
		a = new Number(document.getElementById('costs').value);
		if(a + actioncosts[type]['step'] < actioncosts[type]['maxpreis'] ){
			document.getElementById('costs').value = a + Math.ceil(actioncosts[type]['step']);
		}
		else document.getElementById('costs').value  = actioncosts[type]['maxpreis'];
	}
	
	function setDown(){
		type = (document.getElementById('at_type').value);
		a = new Number(document.getElementById('costs').value);
		if(a - actioncosts[type]['step'] > actioncosts[type]['minpreis'] ){
			document.getElementById('costs').value = a - Math.ceil(actioncosts[type]['step']);
		}
		else document.getElementById('costs').value  = actioncosts[type]['minpreis'];
	}
	
	function setMax(){
		type = (document.getElementById('at_type').value);
		document.getElementById('costs').value = actioncosts[type]['maxpreis'];
	}
	
	function setMin(){
		type = (document.getElementById('at_type').value);
		document.getElementById('costs').value = actioncosts[type]['minpreis'];
	}
	
	function setStuff() {
	
		type = (document.getElementById('at_type').value);
	
		document.getElementById('minresult').value = actioncosts[type]['defaultgain'];
		document.getElementById('costs').value = actioncosts[type]['preis'];
	}
	{/literal}
		
</script>