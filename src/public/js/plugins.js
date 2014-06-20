(function($){
	$.fn.ToolTip = function(opt){
		opt = $.extend({
			text: 'Beispiel',
			duration: 200,
			offset: 15
		}, opt);
		
		function show(evt){
			$('<div>', {
					'id'	: 'tooltip',
					'css'	: {
								'display'	: 'none',
								'position'	: 'absolute',
								'left'		: evt.pageX + opt.offset,
								'top'		: evt.pageY + opt.offset
					},
					'html'	: opt.text
			}).appendTo('body').fadeIn(opt.duration);
		}
		(this).each(function(){
			$(this).bind({
				mouseenter: function(evt){
					if($('#tooltip').css('opacity') != 0){
						$('#tooltip').stop().remove();
					}
					show(evt);
				},
				mouseleave: function(evt){
					$('#tooltip').fadeOut(
						opt.duration,
						function(){
							$(this).remove();
						}
					);
				},
				mousemove: function(evt){
					$('#tooltip').css({
						'left'		: evt.pageX + opt.offset,
						'top'		: evt.pageY + opt.offset
					});
				}
			});
		});
	};
})(jQuery);


(function($){
	$.fn.InputHint = function(opt){
		opt = $.extend({
		}, opt);
		if(!opt.span){
			alert('Das Label des Element mit der Id "'+$(this).attr('id')+'" fehlt');
			return false;
		}
		var text = $('#'+opt.span).html();
		if($(this).val() != ''){
			$('#'+opt.span).hide();
		}
		$(this).blur(function(){
			if($(this).val() == ''){
				$('#'+opt.span).fadeIn(200);
			}
		}).focus(function(){
			$('#'+opt.span).fadeOut(200);
		});
	};
})(jQuery);

/*
 * jQuery BBQ: Back Button & Query Library - v1.2.1 - 2/17/2010
 * http://benalman.com/projects/jquery-bbq-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,p){var i,m=Array.prototype.slice,r=decodeURIComponent,a=$.param,c,l,v,b=$.bbq=$.bbq||{},q,u,j,e=$.event.special,d="hashchange",A="querystring",D="fragment",y="elemUrlAttr",g="location",k="href",t="src",x=/^.*\?|#.*$/g,w=/^.*\#/,h,C={};function E(F){return typeof F==="string"}function B(G){var F=m.call(arguments,1);return function(){return G.apply(this,F.concat(m.call(arguments)))}}function n(F){return F.replace(/^[^#]*#?(.*)$/,"$1")}function o(F){return F.replace(/(?:^[^?#]*\?([^#]*).*$)?.*/,"$1")}function f(H,M,F,I,G){var O,L,K,N,J;if(I!==i){K=F.match(H?/^([^#]*)\#?(.*)$/:/^([^#?]*)\??([^#]*)(#?.*)/);J=K[3]||"";if(G===2&&E(I)){L=I.replace(H?w:x,"")}else{N=l(K[2]);I=E(I)?l[H?D:A](I):I;L=G===2?I:G===1?$.extend({},I,N):$.extend({},N,I);L=a(L);if(H){L=L.replace(h,r)}}O=K[1]+(H?"#":L||!K[1]?"?":"")+L+J}else{O=M(F!==i?F:p[g][k])}return O}a[A]=B(f,0,o);a[D]=c=B(f,1,n);c.noEscape=function(G){G=G||"";var F=$.map(G.split(""),encodeURIComponent);h=new RegExp(F.join("|"),"g")};c.noEscape(",/");$.deparam=l=function(I,F){var H={},G={"true":!0,"false":!1,"null":null};$.each(I.replace(/\+/g," ").split("&"),function(L,Q){var K=Q.split("="),P=r(K[0]),J,O=H,M=0,R=P.split("]["),N=R.length-1;if(/\[/.test(R[0])&&/\]$/.test(R[N])){R[N]=R[N].replace(/\]$/,"");R=R.shift().split("[").concat(R);N=R.length-1}else{N=0}if(K.length===2){J=r(K[1]);if(F){J=J&&!isNaN(J)?+J:J==="undefined"?i:G[J]!==i?G[J]:J}if(N){for(;M<=N;M++){P=R[M]===""?O.length:R[M];O=O[P]=M<N?O[P]||(R[M+1]&&isNaN(R[M+1])?{}:[]):J}}else{if($.isArray(H[P])){H[P].push(J)}else{if(H[P]!==i){H[P]=[H[P],J]}else{H[P]=J}}}}else{if(P){H[P]=F?i:""}}});return H};function z(H,F,G){if(F===i||typeof F==="boolean"){G=F;F=a[H?D:A]()}else{F=E(F)?F.replace(H?w:x,""):F}return l(F,G)}l[A]=B(z,0);l[D]=v=B(z,1);$[y]||($[y]=function(F){return $.extend(C,F)})({a:k,base:k,iframe:t,img:t,input:t,form:"action",link:k,script:t});j=$[y];function s(I,G,H,F){if(!E(H)&&typeof H!=="object"){F=H;H=G;G=i}return this.each(function(){var L=$(this),J=G||j()[(this.nodeName||"").toLowerCase()]||"",K=J&&L.attr(J)||"";L.attr(J,a[I](K,H,F))})}$.fn[A]=B(s,A);$.fn[D]=B(s,D);b.pushState=q=function(I,F){if(E(I)&&/^#/.test(I)&&F===i){F=2}var H=I!==i,G=c(p[g][k],H?I:{},H?F:2);p[g][k]=G+(/#/.test(G)?"":"#")};b.getState=u=function(F,G){return F===i||typeof F==="boolean"?v(F):v(G)[F]};b.removeState=function(F){var G={};if(F!==i){G=u();$.each($.isArray(F)?F:arguments,function(I,H){delete G[H]})}q(G,2)};e[d]=$.extend(e[d],{add:function(F){var H;function G(J){var I=J[D]=c();J.getState=function(K,L){return K===i||typeof K==="boolean"?l(I,K):l(I,L)[K]};H.apply(this,arguments)}if($.isFunction(F)){H=F;return G}else{H=F.handler;F.handler=G}}})})(jQuery,this);
/*
 * jQuery hashchange event - v1.2 - 2/11/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,i,b){var j,k=$.event.special,c="location",d="hashchange",l="href",f=$.browser,g=document.documentMode,h=f.msie&&(g===b||g<8),e="on"+d in i&&!h;function a(m){m=m||i[c][l];return m.replace(/^[^#]*#?(.*)$/,"$1")}$[d+"Delay"]=100;k[d]=$.extend(k[d],{setup:function(){if(e){return false}$(j.start)},teardown:function(){if(e){return false}$(j.stop)}});j=(function(){var m={},r,n,o,q;function p(){o=q=function(s){return s};if(h){n=$('<iframe src="javascript:0"/>').hide().insertAfter("body")[0].contentWindow;q=function(){return a(n.document[c][l])};o=function(u,s){if(u!==s){var t=n.document;t.open().close();t[c].hash="#"+u}};o(a())}}m.start=function(){if(r){return}var t=a();o||p();(function s(){var v=a(),u=q(t);if(v!==t){o(t=v,u);$(i).trigger(d)}else{if(u!==t){i[c][l]=i[c][l].replace(/#.*/,"")+"#"+u}}r=setTimeout(s,$[d+"Delay"])})()};m.stop=function(){if(!n){r&&clearTimeout(r);r=0}};return m})()})(jQuery,this);
/*jslint browser: true */ /*global jQuery: true */

/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

// TODO JsDoc

/**
 * Create a cookie with the given key and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String key The key of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given key.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String key The key of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function (key, value, options) {
    
    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }
        
        value = String(value);
        
        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

jQuery.fn.extend({ 
	disableSelection : function() { 
		return this.each(function() { 
			this.onselectstart = function() { return false; }; 
			this.unselectable = "on"; 
			jQuery(this).css('user-select', 'none'); 
			jQuery(this).css('-o-user-select', 'none'); 
			jQuery(this).css('-moz-user-select', 'none'); 
			jQuery(this).css('-khtml-user-select', 'none'); 
			jQuery(this).css('-webkit-user-select', 'none'); 
		}); 
	} 
}); 

/**
 * jquery.dump.js
 * @author Torkild Dyvik Olsen
 * @version 1.0
 * 
 * A simple debug function to gather information about an object.
 * Returns a nested tree with information.
 * 
 */
(function($) {

$.fn.dump = function() {
   return $.dump(this);
}

$.dump = function(object) {
   var recursion = function(obj, level) {
      if(!level) level = 0;
      var dump = '', p = '';
      for(i = 0; i < level; i++) p += "\t";
      
      t = type(obj);
      switch(t) {
         case "string":
            return '"' + obj + '"';
            break;
         case "number":
            return obj.toString();
            break;
         case "boolean":
            return obj ? 'true' : 'false';
         case "date":
            return "Date: " + obj.toLocaleString();
         case "array":
            dump += 'Array ( \n';
            $.each(obj, function(k,v) {
               dump += p +'\t' + k + ' => ' + recursion(v, level + 1) + '\n';
            });
            dump += p + ')';
            break;
         case "object":
            dump += 'Object { \n';
            $.each(obj, function(k,v) {
               dump += p + '\t' + k + ': ' + recursion(v, level + 1) + '\n';
            });
            dump += p + '}';
            break;
         case "jquery":
            dump += 'jQuery Object { \n';
            $.each(obj, function(k,v) {
               dump += p + '\t' + k + ' = ' + recursion(v, level + 1) + '\n';
            });
            dump += p + '}';
            break;
         case "regexp":
            return "RegExp: " + obj.toString();
         case "error":
            return obj.toString();
         case "document":
         case "domelement":
            dump += 'DOMElement [ \n'
                  + p + '\tnodeName: ' + obj.nodeName + '\n'
                  + p + '\tnodeValue: ' + obj.nodeValue + '\n'
                  + p + '\tinnerHTML: [ \n';
            $.each(obj.childNodes, function(k,v) {
               if(k < 1) var r = 0;
               if(type(v) == "string") {
                  if(v.textContent.match(/[^\s]/)) {
                     dump += p + '\t\t' + (k - (r||0)) + ' = String: ' + trim(v.textContent) + '\n';
                  } else {
                     r--;
                  }
               } else {
                  dump += p + '\t\t' + (k - (r||0)) + ' = ' + recursion(v, level + 2) + '\n';
               }
            });
            dump += p + '\t]\n'
                  + p + ']';
            break;
         case "function":
            var match = obj.toString().match(/^(.*)\(([^\)]*)\)/im);
            match[1] = trim(match[1].replace(new RegExp("[\\s]+", "g"), " "));
            match[2] = trim(match[2].replace(new RegExp("[\\s]+", "g"), " "));
            return match[1] + "(" + match[2] + ")";
         case "window":
         default:
            dump += 'N/A: ' + t;
            break;
      }
      
      return dump;
   }
   
   var type = function(obj) {
      var type = typeof(obj);
      
      if(type != "object") {
         return type;
      }
      
      switch(obj) {
         case null:
            return 'null';
         case window:
            return 'window';
         case document:
            return 'document';
         case window.event:
            return 'event';
         default:
            break;
      }
      
      if(obj.jquery) {
         return 'jquery';
      }
      
      switch(obj.constructor) {
         case Array:
            return 'array';
         case Boolean:
            return 'boolean';
         case Date:
            return 'date';
         case Object:
            return 'object';
         case RegExp:
            return 'regexp';
         case ReferenceError:
         case Error:
            return 'error';
         case null:
         default:
            break;
      }
      
      switch(obj.nodeType) {
         case 1:
            return 'domelement';
         case 3:
            return 'string';
         case null:
         default:
            break;
      }
      
      return 'Unknown';
   }
   
   return recursion(object);
}

function trim(str) {
   return ltrim(rtrim(str));
}

function ltrim(str) {
   return str.replace(new RegExp("^[\\s]+", "g"), "");
}

function rtrim(str) {
   return str.replace(new RegExp("[\\s]+$", "g"), "");
}

})(jQuery);

/**
 * @author Alexander Farkas
 * v. 1.22
 */


(function($) {
	if(!document.defaultView || !document.defaultView.getComputedStyle){ // IE6-IE8
		var oldCurCSS = $.curCSS;
		$.curCSS = function(elem, name, force){
			if(name === 'background-position'){
				name = 'backgroundPosition';
			}
			if(name !== 'backgroundPosition' || !elem.currentStyle || elem.currentStyle[ name ]){
				return oldCurCSS.apply(this, arguments);
			}
			var style = elem.style;
			if ( !force && style && style[ name ] ){
				return style[ name ];
			}
			return oldCurCSS(elem, 'backgroundPositionX', force) +' '+ oldCurCSS(elem, 'backgroundPositionY', force);
		};
	}
	
	var oldAnim = $.fn.animate;
	$.fn.animate = function(prop){
		if('background-position' in prop){
			prop.backgroundPosition = prop['background-position'];
			delete prop['background-position'];
		}
		if('backgroundPosition' in prop){
			prop.backgroundPosition = '('+ prop.backgroundPosition;
		}
		return oldAnim.apply(this, arguments);
	};
	
	function toArray(strg){
		strg = strg.replace(/left|top/g,'0px');
		strg = strg.replace(/right|bottom/g,'100%');
		strg = strg.replace(/([0-9\.]+)(\s|\)|$)/g,"$1px$2");
		var res = strg.match(/(-?[0-9\.]+)(px|\%|em|pt)\s(-?[0-9\.]+)(px|\%|em|pt)/);
		return [parseFloat(res[1],10),res[2],parseFloat(res[3],10),res[4]];
	}
	
	$.fx.step. backgroundPosition = function(fx) {
		if (!fx.bgPosReady) {
			var start = $.curCSS(fx.elem,'backgroundPosition');
			if(!start){//FF2 no inline-style fallback
				start = '0px 0px';
			}
			
			start = toArray(start);
			fx.start = [start[0],start[2]];
			var end = toArray(fx.end);
			fx.end = [end[0],end[2]];
			
			fx.unit = [end[1],end[3]];
			fx.bgPosReady = true;
		}
		//return;
		var nowPosX = [];
		nowPosX[0] = ((fx.end[0] - fx.start[0]) * fx.pos) + fx.start[0] + fx.unit[0];
		nowPosX[1] = ((fx.end[1] - fx.start[1]) * fx.pos) + fx.start[1] + fx.unit[1];           
		fx.elem.style.backgroundPosition = nowPosX[0]+' '+nowPosX[1];

	};
})(jQuery);