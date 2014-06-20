{literal}
<script type="text/javascript">
<!--
function blinkArrow(arrowId) {
	var top_off = $('#'+arrowId).offset().top;
	$('#'+arrowId).animate({opacity: 0.3, top: top_off-10}, 800, 'easeInQuad', function() {
		$('#'+arrowId).animate({opacity: 1, top: top_off}, 800, 'easeInQuad', function() {blinkArrow(arrowId)});;
	});
}

function placeArrow(arrowId) {
	var offset = $('#topbar_credits').position();
	var height = $('#topbar_credits').height();
	offset.top +=height+22;
	$('#'+arrowId).offset(offset);
	$('#'+arrowId).show();
	blinkArrow(arrowId);
	setInterval(function() {
		$('#'+arrowId).css('left', $('#topbar_credits').position().left);
	}, 4000);
}

function placeArrows() {
	placeArrow('arrow');	
}

function showMidBox(text) {
	$('#quest_box').animate({opacity: 0}, 400);
	
	$('#box_tut_text').html(text);
	$('#box_tut').css('width', 300);
	//box etwa mittig anzeigen, 45/55
	var boxTop = $(window).height()/10*4.5-$('#box_tut').height()/2+$(window).scrollTop();
	var boxLeft = $(window).width()/2-250/2;
	$('#box_tut').offset({top: boxTop, left: boxLeft});
	$('#box_tut').show();
	$('#box_tut').animate({opacity:1}, 700);
	$('#back_blur').animate({width: $(window).width(), height: $(window).height()}, 500);
}

function hideBox() {
	$('#quest_box').animate({opacity: 1}, 700);
	//$('#box_tut').hide();
	//$('#back_blur').css('width', 0);
	//$('#back_blur').css('height', 0);
	$('#back_blur').animate({width: 0, height: 0}, 700);
	$('#box_tut').animate({opacity: 0}, 700, function(){
		$('#box_tut').css('top', 0);
		$('#box_tut').css('left', 0);
		$('#box_tut').hide();
	})
}

function showMenuBox(menu_item, text) {
	showQuestBox('#menu_'+menu_item, text, 1, 0, 0, 1);
	flashElement('menu_'+menu_item+'_text','gruenAuftableInner',1800);
}

function showQuestBox(item_selector, text, offsetLeft, offsetTop, centerLeft, centerTop) {
	$('#quest_box').show();
	var width = {/literal}{if $WIDTH}{$WIDTH}{else}175{/if}{literal};
	$('#quest_box').width(width);
	$('#quest_box_text').html(text);
	
	var offset = $(item_selector).offset();
	offset.left += $(item_selector).width()*offsetLeft-width*centerLeft/4;
	offset.top += $(item_selector).height()*offsetTop-$('#quest_box').height()*centerTop/2+1;
	$('#quest_box').offset(offset);
	
	setInterval(function() {
		var offset = $(item_selector).offset();
		offset.left += $(item_selector).width()*offsetLeft-width*centerLeft/4;
		offset.top += $(item_selector).height()*offsetTop-$('#quest_box').height()*centerTop/2;
		$('#quest_box').offset(offset);
	}, 1000);
}

function scrollToMiddle(item_selector) {
	$(window).scrollTop($(item_selector).offset().top-$(window).height()/2);
}

function flashElement(elId, elClass, interval) {
	$('#'+elId).addClass(elClass);
	setTimeout(function() {
		$('#'+elId).removeClass(elClass);
		setTimeout(function() {
			flashElement(elId, elClass, interval);
		}, interval/2);
	}, interval/2);
}

function removeTrennzeichen(numberstring) {
	if(numberstring.indexOf('.') != -1) {
		var parts = numberstring.split('.');
		var number = '';
		for(var i = 0; i < parts.length; i++) {
			number += parts[i];
		}
		return parseInt(number);
	}
	return parseInt(numberstring);
}

{/literal}{if $TASK}{literal}
	function tryAgain() {
		hideBox();
		$('#quest_box').show();
	}
	
	function getNumAchieved() {
		{/literal}{if $TASK.type == 'gm'}{literal}
			return Math.min(parseInt($('#{/literal}{$TASK.item}{literal}_maxbuy').text()), parseInt($('#{/literal}{$TASK.item}{literal}_number input[name=anzahl]').val()));
		{/literal}{elseif $TASK.type == 'ressi_leiste'}{literal}
			return parseInt(removeTrennzeichen($('#tut_amount').val()));
		{/literal}{elseif $TASK.type == 'config'}{literal}
			return parseInt($('#tradecenters').val())+parseInt($('#powerplants').val())+parseInt($('#ressourcefacilities').val())+parseInt($('#sciencelabs').val())+parseInt($('#buildinggrounds').val())+parseInt($('#factories').val());
		{/literal}{elseif $TASK.type == 'gebs'}{literal}
			return parseInt($('#{/literal}{$TASK.item}{literal}_number').val())
		{/literal}{else}{literal}
			return 1;
		{/literal}{/if}{literal}
	}
	
	function hideTutorial() {
		hideBox();
		ret_instant = true;
	}
	
	var stop_submit = true;
	var ret_instant = false;
	function submitTutorial() {
		if(ret_instant)
			return true;
		var ret_val = !stop_submit
		var numAchieved = getNumAchieved();
		if(numAchieved {/literal}{if $TASK.allow_more}{literal}>={/literal}{else}{literal}=={/literal}{/if}{$TASK.amount}{literal}) {
			$.ajax({'url' : '?tid={/literal}{$TASK.id}{literal}&success=true'}).success(function() {
				$('#quest_next').removeAttr('disabled');
			});
			showMidBox('{/literal}{$TASK.success_text}{literal}<br /><input id="quest_next" type="button" onClick="{/literal}{if $TASK.type == 'gm'}{literal}document.{/literal}{$TASK.item}{literal}.submit();{/literal}{elseif $TASK.type == 'config'}{literal}submitForm();{/literal}{elseif $TASK.type == 'gebs'}{literal}$(\'#land_form\').submit();{/literal}{elseif $TASK.type == 'fos'}{literal}window.location = \'forschung.php?inneraction={/literal}{$TASK.item}{literal}&lck=1\'{/literal}{else}{literal}hideBox();{/literal}{/if}{literal}" value="Weiter" disabled>');
			$('#quest_box').hide();
			stop_submit=false;
		} else {
			showMidBox('{/literal}{$TASK.failure_text}{literal}<br /><input type="button" onClick="tryAgain()" value="Nochmal Probieren">');
		}
		
		return ret_val;
	}
{/literal}{/if}{literal}
$(document).ready(function() {
	/*$('#back_blur').click(function() {
		hideBox();
	});*/
	{/literal}{if $MENU_BOX.show}{literal}
		setTimeout(function() {
			showMenuBox('{/literal}{$MENU_BOX.item}{literal}', '{/literal}{$MENU_BOX.text}{literal}');
		}, 100);
	{/literal}{/if}{literal}
	{/literal}{if $TASK}{literal}
			setTimeout(function() {
				$('#quest_box_heading').html('{/literal}{if $TASK.heading}{$TASK.heading}{else}Tutorial{/if}{literal}');
				showQuestBox('#{/literal}{$TASK.item}{literal}_number', '{/literal}{$TASK.text}{literal}', 0, 1.2, 1, 0);
				scrollToMiddle('#{/literal}{$TASK.item}{literal}_number');
			}, 100);
			$('#{/literal}{$TASK.item}{literal}_button').click(function() {return submitTutorial();})
	{/literal}{/if}{literal}
});

-->
</script>
{/literal}