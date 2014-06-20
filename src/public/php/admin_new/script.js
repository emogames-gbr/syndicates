// When DOM completely loaded ...
window.addEvent('domready', function() {
    // Array containing Menu Categories
    menues = new Array("konzern", "kommunikation", "synwelt");

    // Foreach Category
    for (var i = 0; i < menues.length; i++) {
        menuOpener(menues[i], document.getElementById('menu_items_'+menues[i]).offsetHeight, i+1);//document.getElementById('menu_items_'+menues[i]).offsetHeight);//menu_height[i]);
    }
	
	for (var i = 0; i < menues.length; i++) {
        menuCloser(menues[i]);//document.getElementById('menu_items_'+menues[i]).offsetHeight);//menu_height[i]);
    }
	
	/*Extra part um hoves zu realisieren Browser unabhängig!
	//wieso tut es das nicht sdgfpojpdäf
	var allElements;
	allElements = document.getElementsByTagName('DIV');

	for (var j = 0; j < allElements.length; j++)
	{
		var el = allElements[j];
		if ( el.hasClass('menu_item' )){
			el.addEvent('mouseover',function(e) {
				el.addClass('menu_item_hover');
				alert(el.className);
			});
			el.addEvent('mouseout',function(e) {
				el.removeClass('menu_item_hover');
			});
		}
	}
	//end part
	*/
		
    var resbar_button = $('resbar_button');
	var newVal = $('resbar').getStyle('left').toInt() + 163;
	resbar.tween('left', newVal + "px"); //ausfahren zum start
	resbar_button.setStyle('left', resbar_button.getStyle('left').toInt() + 163 + "px"); //ausfahren zum start
    resbar_button.addEvent('click', function(e) {
        var resbar = $('resbar');
        if (resbar.hasClass('resbar_closed')) {
            var newVal = $('resbar').getStyle('left').toInt() + 163;
            resbar.tween('left', newVal + "px");
            resbar.removeClass('resbar_closed');
            resbar.addClass('resbar_opened');
            resbar_button.setStyle('left', resbar_button.getStyle('left').toInt() + 163 + "px");
        } else if (resbar.hasClass('resbar_opened')) {
            var newVal = $('resbar').getStyle('left').toInt() - 163;
            resbar.tween('left', newVal + "px");
            resbar.removeClass('resbar_opened');
            resbar.addClass('resbar_closed');
            resbar_button.setStyle('left', resbar_button.getStyle('left').toInt() - 163 + "px");
        } 
    });

	//var headMove = new Drag.Move('head_root', {handle: 'head_drag_handle'});
    //var clocknwMove = new Drag.Move('clocknw');
    //var menuMoveKonzern = new Drag.Move('menu_konzern', {handle: 'menu_drag_handle_konzern'});
    //var menuMoveKommunikation = new Drag.Move('menu_kommunikation', {handle: 'menu_drag_handle_kommunikation'});
    //var menuMoveSynwelt = new Drag.Move('menu_synwelt', {handle: 'menu_drag_handle_synwelt'});
    //var mainMove = new Drag.Move('main', {handle: 'main_head'});

    
});

function menuCloser(category) {
    var itembox = $('menu_items_' + category);
    var bottom = $('menu_bottom_' + category);
	
	//hochfahren zu beginn
	itembox.tween('height', 0);
	itembox.removeClass('menu_items_opened');
	itembox.addClass('menu_items_closed');
	bottom.removeClass('menu_bottom_opened');
	bottom.addClass('menu_bottom_closed');
}

function menuOpener(category, height, index) {
    var itembox = $('menu_items_' + category);
    var button = $('menu_button_' + category);
    var bottom = $('menu_bottom_' + category);
	
    button.addEvent('click', function(e) {
        if (itembox.hasClass('menu_items_closed')) {
            itembox.tween('height', height);
            itembox.removeClass('menu_items_closed');
            itembox.addClass('menu_items_opened');
            bottom.removeClass('menu_bottom_closed');
            bottom.addClass('menu_bottom_opened');
			for (var i = index; i < menues.length; i++) {
				var newVal = $('menu_'+ menues[i]).getStyle('top').toInt() - 20 + height.toInt();
				$('menu_'+ menues[i]).tween('top', newVal + "px");
			}
        } else if (itembox.hasClass('menu_items_opened')) {
            itembox.tween('height', 0);
            itembox.removeClass('menu_items_opened');
            itembox.addClass('menu_items_closed');
            bottom.removeClass('menu_bottom_opened');
            bottom.addClass('menu_bottom_closed');
			for (var i = index; i < menues.length; i++) {
				var newVal = $('menu_'+ menues[i]).getStyle('top').toInt() - height + 20;
				$('menu_'+ menues[i]).tween('top', newVal + "px");
			}
        }
        return false;
    });
}