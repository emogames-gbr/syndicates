{literal}
<script type="text/javascript">
	// COMPRESSOR: http://www.refresh-sf.com/yui/
	var fraks = {	'uic' : 'United Industries Corporation',
					'sl'  : 'Shadow Labs',
					'pbf' : 'Brute Force',
					'nof' : 'Nova Federation',
					'neb' : 'New Economic Block'};
	var act = new Array();
	var heights = new Array();
	var special = new Array();
	var special_heigths = new Array();
	
	var news_height = '250'; // Auf diese Höhe werden die News verkleinert
	var news_open = 1; // Die Anzahl der News die offen bleiben sollen
	var tick_more = './images/startseite/pfeil_read_more.png'; // Pfeil für read more für die News
	var tick_less = './images/startseite/pfeil_read_less.png'; // Pfeil für read less für die News
	var init_first = true;
	var klick = false;
	var url, pre_url;
	var searchinput_text = infos = false;
	var content1 = 1, content2 = 2;
	tmp1 = new Image(18, 18);
	tmp1.src=tick_more;
	tmp2 = new Image(18, 18);
	tmp2.src=tick_less;
	var back_pos = $.cookie('back');
	if(!back_pos){
		back_pos = parseInt(Math.random()*300+200); // Startposition des Hintergrundbildes - Zufallszahl * Range + Offset
	}
	else if(back_pos > 2800){
		back_pos -= 2768; // Falls der Hintergrund schon weiter gewandert ist, als das Bild breit ist, dann wird die Bildbreite abgezeogen,
																	// damit man nicht irgendwann bei -10000 Pixel ist, was ja keinen Sinn macht.
	}
	
	if(tmp = $.param.fragment()){
		window.location.replace('?'+tmp);
	}
	
	
	$().ready(init);
	function init() {
		if(init_first){
			/* * * * *
			 * Wird nur beim 1. Laden der Seite ausgeführt
			 * :: START ::
			 * * * * */
			if($.cookie('ie_warning') == true) $('.ie_warning').hide();
			$('#ie_warning_link').mousedown(function(){ $.cookie('ie_warning', true); $('.ie_warning').hide(500); });
			
			$('.extern').children().each(function(){
				var div = $('<div>', {'css':{'display':'none', 'font-size':'10px', 'margin-top':'-2px', 'color':'#BBB'}, 'html':'externer Link'}).appendTo(this);
				$(this).mouseenter(function(){ div.fadeIn(200); }).mouseleave(function(){ div.fadeOut(200); });
			});
			 
			if($('#input_name'.length > 0))	{     $('#input_name').InputHint({ 'span' : 'label_name', 'sec' : {'input' : 'input_password', 'span' : 'label_password' } }); $('#input_name').focus(); }
			if($('#input_password').length > 0)   $('#input_password').InputHint({ 'span' : 'label_password' });
			if($('#input_codeinput').length > 0){ $('#input_codeinput').InputHint({ 'span' : 'label_codeinput' }); $('#input_codeinput').focus(); }
					
			$.each(fraks, function(tag, val){
				$('.'+tag)
				.mouseenter(function(){
					$('#desc_'+tag).fadeIn(200);
					var position = $('.'+tag+'_blocked').position();
					if(position != null){
						act['div_'+tag] = $('<div>', {
										'id' 	: tag+'_hint',
										'css'	: {
												'display'	: 'none',
												'position'	: 'absolute',
												'left'		: position.left+20,
												'top'		: position.top+20,
												'width'		: '250px',
												'background-color'	: '#FFF',
												'z-index'	: 50,
												'font-size'	: '13px',
												'padding'	: '4px',
												'border'	: '1px solid #FF3333',
												'text-align': 'center'
										},
										'html'	: 'Da jede Runde nur 3 von 5 Fraktionen spielbar sind, ist die Fraktion <strong>'+val+'</strong> diese Runde leider nicht verfügbar. Eventuell ist sie nächste Runde wieder mit von der Partie.'
							  }).appendTo('.circle');
					}
				})
				.mouseleave(function(){
					if(act['div_'+tag]){
						act['div_'+tag].remove();
					}
					$('#desc_'+tag).fadeOut(200);
				});
				
				if($('.'+tag+'_blocked').html() != null){
					act['div_'+tag] = false;
					$('.'+tag).mousedown(function(){
						if(act['div_'+tag].css('display') == 'none'){
							act['div_'+tag].slideDown(500).delay(5000).fadeOut(500);
						}
					});
				}
				
				if($('.'+tag+'_available').length > 0){
					$('.'+tag).mousedown(function(){
						$('.'+tag).fancybox({'href' : '?ajax=true&action=anmeldung&frak='+tag});
					});
				}
			});
			$.each($('.alert'), function(){
				alert_(this);
			});
			
			var x = 0;
			$('.news_post').each(function(){
				if(x >= news_open){
					if($(this).height() > news_height){
						var splits = $(this).attr('id').split('_');
						var id = splits[0];
						$('#'+id+'_tick').css('display', '').html();
						read_more(this, id);
						heights[id] = $(this).height();
						$(this).height(news_height).css('overflow', 'hidden');
						//$(this).html($(this).attr('id'));
					}
				}
				x++;
			});
			
			var background = $('body').css('background-image');
			if(background.lastIndexOf("background3.jpg") > 0){
				$('body').css('background-position', '-'+back_pos+'px 0px');
				window.setInterval('move_bg()', 1000);		// Die Bewegung des Hintergrundbildes soll nur beim kompletten Hintergrundbild (große Geräte) passieren
			}
			
			$('.bar_1').each(function(){
				var regex = /bar_1([a-zA-Z0-9_]+)/;
				var hover = /_hover/;
				var attr = regex.exec($(this).attr('class'));
				var h_true = hover.test(attr[0]);
				var tmp = ohne = false;
				var href = $(this).attr('href');
				$(this).attr('href', '').fragment($.deparam.querystring(href));
				if(!h_true){
					$(this).css('font-weight', 400).css('margin-top', '4px');
				}
				else{
					$(this).css('font-weight', 700);
				}
				$(this).mouseenter(function(){
					if(!klick){
						$(this).stop();
						$(this).animate({ 'height': '25px', 'font-size' : '16px', 'padding-top' : '5px', 'margin-top' : '0px' }, 400, 'linear');
					}
				}).mouseleave(function(){
					if($(this).css('font-weight') != 700){
						if(!klick){
							$(this).stop();
							$(this).animate({ 'height': '20px', 'font-size' : '13px', 'padding-top' : '2px', 'margin-top' : '4px' }, 400, 'linear');
						}
					}
				}).mousedown(function(){
					klick = true;
					$(this).css('font-weight', 700);
					$(this).animate({ 'height': '25px', 'font-size' : '16px', 'padding-top' : '5px', 'margin-top' : '0px' }, 400, 'linear');
					var akt = this;
					$('.bar_1').each(function(){
						if(this != akt){
							$(this).animate({ 'height': '20px', 'font-size' : '13px', 'padding-top' : '2px', 'font-weight' : 400, 'margin-top' : '4px' }, 400, 'linear');
						}
					});
					window.setTimeout('klick = false;', 400);
				});
			});
			
			if(!searchinput_text) var searchinput_text = $('#searchinput').val();
			$('#searchinput').blur(function(){
				if($(this).val() == ''){
					$(this).val(searchinput_text);
				}
			}).focus(function(){
				if($(this).val() == searchinput_text){
					$(this).val('');
				}
			});
			
			if(infos){
				var stock_m_h = 17;
				$.each(special, function(tag, val){
					special_heigths[val] = 0;
					$('.special_'+val).each(function(){
						special_heigths[val] = (special_heigths[val] < $(this).height() ? $(this).height() : special_heigths[val]);
					});
					$('.special_'+val).height(stock_m_h);
					
					$('#tr_'+val).mouseenter(function(){
						$('.special_'+val).stop();
						$('.special_'+val).animate({'height': special_heigths[val]+'px' }, 1500, 'easeOutElastic');
					}).mouseleave(function(){
						$('.special_'+val).stop();
						$('.special_'+val).animate({'height': stock_m_h+'px' }, 1500, 'easeOutElastic');
					});
				});
				
				var inf_dur = 500;
				var inf_klick = new Array();
				var inf_show = new Array();
				$('.toggle_infos').each(function(){
					var id = $(this).attr('id');
					if($('#infos_'+id).length > 0){
						$('.tick_'+id).each(function(){
							$(this).html($('<img>', {'src' : tick_more,'css' : {'vertical-align' : 'middle'}}));
						});
						$('#infos_'+id).hide();
						$(this).mousedown(function(){
							if(!inf_klick[id]){
								inf_klick[id] = true;
								if(inf_show[id] == true){
									inf_show[id] = false;
									$('#infos_'+id).slideUp(inf_dur, function(){ 
										inf_klick[id] = false;
										$('.tick_'+id).each(function(){
											$(this).html($('<img>', {'src' : tick_more,'width' : '18px','height' : '18px','css' : {'vertical-align' : 'middle'}}));
										});
									});
								}
								else{
									inf_show[id] = true;
									$('#infos_'+id).slideDown(inf_dur, function(){ 
										inf_klick[id] = false;
										$('.tick_'+id).each(function(){
											$(this).html($('<img>', {'src' : tick_less,'width' : '18px','height' : '18px','css' : {'vertical-align' : 'middle'}}));
										});
									});
								}
							}
						});
					}
				});
			}
			
			/* Share-Box für die News */
			$('.box_share').each(function(){
				var box = this;
				$(box).children('.button_share').mouseenter(function(){
					$(box).children('.box_share_all').clearQueue();
					$(box).children('.box_share_all').fadeTo(200, 1);
				}).mouseleave(function(){
					$(box).children('.box_share_all').clearQueue();
					$(box).children('.box_share_all').delay(1500).fadeTo(200, 0, function(){ $(this).css('display', 'none'); });
				});
				$(box).children('.box_share_all').mouseenter(function(){
					$(box).children('.box_share_all').clearQueue();
					$(this).fadeTo(200, 1);
				}).mouseleave(function(){
					$(box).children('.box_share_all').clearQueue();
					$(this).delay(500).fadeTo(200, 0, function(){ $(this).css('display', 'none'); });
				});
			});
			
			var bars = [ 'toolbar', 'toolbar2' ];
			var toolbar_dur = 500;
			var toolbar_easing = 'easeOutBack';
			var url_go = false;
			$.each(bars, function(tag, val){
				$('.'+val+'.visible').css({'bottom' : '-34px'});
				$('.'+val+'.visible.bounce').delay(5000).effect('shake', { 'times':2, 'direction':'left', 'distance':5 }, 100);
				$('#'+val+'_small').mousedown(function(){
					$(this).clearQueue();
					$(this).stop();
					$(this).animate({'bottom' : '-70px'}, toolbar_dur, toolbar_easing, function(){
						$('#'+val+'_big').clearQueue();
						$('#'+val+'_big').stop();
						$('#'+val+'_big').animate({'bottom' : '-10px'}, toolbar_dur, toolbar_easing);
					});
				});
				$('#'+val+'_big').mousedown(function(){
					$('.'+val+'_content > a').mousedown(function(){
						url_go = true;
					});
					if(!url_go){
						$(this).clearQueue();
						$(this).stop();
						$(this).animate({'bottom' : '-70px'}, toolbar_dur, toolbar_easing, function(){
							$('#'+val+'_small').clearQueue();
							$('#'+val+'_small').stop();
							$('#'+val+'_small').animate({'bottom' : '-34px'}, toolbar_dur, toolbar_easing);
						});
					}
				});
			});
			
			$('a.lightbox').fancybox();
			$('.lightbox_el').fancybox({'transitionIn':'elastic','transitionOut':'elastic'});
			
			$(window).bind( "hashchange", function(e) {
				pre_url = url;
				url = $.param.fragment();
				if(url == '' && pre_url){
					url = $.param.querystring();
				}
				change_content(url);
			});
			$(window).trigger( "hashchange" );
			/* * * * *
			 * Wird nur beim 1. Laden der Seite ausgeführt
			 * :: ENDE ::
			 * * * * */
		}
		init_first = false;
		
		$('#content1 a').each(function(){
			var href = $(this).attr('href');
			$(this).attr('href', '');
			$(this).fragment($.deparam.querystring(href));
		});
		$('#content2 a').each(function(){
			var href = $(this).attr('href');
			$(this).attr('href', '');
			$(this).fragment($.deparam.querystring(href));
		});
		
		if($('#tbody_text').length > 0 && $('#tbody_chart').length > 0) $('#tbody_text').remove();
		if($('#tbody_chart').length > 0 && plot_data && plot_color){
			$('#tbody_chart').show();
			var plot = jQuery.jqplot('anteil_chart', [plot_data],{
					grid: {
						drawBorder: true,
						borderColor: '#BBB',
						background: '#999',
						shadow: 2,
					},
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer,
						rendererOptions: {
							showDataLabels: true,
							fill: true,
							sliceMargin: 5,
							dataLabelThreshold: 5,
							padding: 10,
							shadowDepth: 4,
							dataLabels: 'value'
						}
					},
					legend: { show:true, location: 'w' },
					seriesColors: plot_color,
			});
		}
	}
	
	function change_content(l){
		if(l){
			$.ajax({ 'url' : 'index.php?ajax=true&'+l, 'dataType' : 'text script' })
			.success(function(data){
				//$('#content1').fadeOut(200, function(){ $('#content1').html(data).fadeIn(200); init(); });
				$('#content'+content2).html(data);
				$('#content'+content2).slideDown(1000);
				$('#content'+content1).slideUp(1000);
				init();
				content1 = (content1 == 1 ? 2 : 1);
				content2 = (content2 == 1 ? 2 : 1);
			});
		}
	}
	
	function alert_(th){
		if(th == '[object HTMLDivElement]'){
			alert_($(th));
		}
		else if(th == '[object Object]'){
			th.delay(5000).animate({ 'opacity' : 0.5 }, 4000).slideUp(1000, function(){ th.remove(); });
			th.mouseenter(function(){
				var q = th.queue("fx");
				if(q.length >= 2){
					th.clearQueue().stop();
					th.css('opacity', 1.0);
				}
			}).mouseleave(function(){
				var q = th.queue()
				if(q.length == 0){
					th.animate({ 'opacity' : 0.5 }, 4000).slideUp(1000, function(){ th.remove(); });
				}
			});
		}
		else{
			var tmp = $('<div>', { 'class' : 'alert', 'html' : th }).appendTo('.alert_box').hide().slideDown(500);
			alert_(tmp);
		}
	}
	
	
	function move_bg(){
		back_pos++;
		$('body').css('background-position', '-'+back_pos+'px 0px');
		$.cookie('back', back_pos);
	}
	
	function read_more(this_, id){
		var name = id + '_tickimage';
		if('#'+name) $('#'+name).remove();
		if('#'+id+'_tick_text') $('#'+id+'_tick_text').remove();
		$('<div>', {
				'id' : id+'_tick_text',
				'css' : {'display' : 'inline','cursor' : 'pointer'},
				'html' : 'mehr'
		})
		.mousedown(function(){
			$(this_).animate({ 'height' : heights[id]+'px' }, heights[id]*1.5, 'easeOutExpo', function(){ read_less(this_, id) });
		}).appendTo('#'+id+'_tick');
		$('<img>', {
		  		'id' : name,
		  		'src' : tick_more,
				'width' : '18px',
				'height' : '18px',
				'css' : {'display' : 'inline','cursor' : 'pointer'}
		}).mousedown(function(){
			$(this_).animate({ 'height' : heights[id]+'px' }, heights[id]*1.5, 'easeOutExpo', function(){ read_less(this_, id) });
		}).appendTo('#'+id+'_tick');
	}
	
	function read_less(this_, id){
		var name = id + '_tickimage';
		if('#'+name) $('#'+name).remove();
		if('#'+id+'_tick_text') $('#'+id+'_tick_text').remove();
		$('<div>', {
				'id' : id+'_tick_text',
				'css' : {'display' : 'inline','cursor' : 'pointer'},
				'html' : 'weniger'
		})
		.mousedown(function(){
			$(this_).animate({ 'height' : news_height+'px' }, heights[id]*1.5, 'easeOutExpo', function(){ read_more(this_, id) });
		}).appendTo('#'+id+'_tick');
		$('<img>', {
		  		'id' : name,
		  		'src' : tick_less,
				'width' : '18px',
				'height' : '18px',
				'css' : {'display' : 'inline','cursor' : 'pointer'}
		}).mousedown(function(){
			$(this_).animate({ 'height' : news_height+'px' }, heights[id]*1.5, 'easeOutExpo', function(){ read_more(this_, id) });
		}).appendTo('#'+id+'_tick');
	}
</script>
{/literal}
