/**
 * @file
 * A JavaScript file for the theme.
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
/**
 * verify mobile popup
 **/
jQuery(document).ready(function() {
	var link = jQuery('a[rel="modal-node-popup"]');
	if(link) {
		jQuery('body').append('<div><div id="popup"><span class="button b-close"><span></span></span><div class="c_area"></div></div></div>').html();
	}
	jQuery('a[rel="modal-node-popup"]').click(function(e){
		e.preventDefault();
		var scroll = jQuery(document).scrollTop();
		jQuery("#popup-load-img").css("top", scroll+"px");
		jQuery("#popup").css("height", "");
		jQuery("#popup").css("width", "");
		var url = jQuery(this).attr('href');
		var slide = 'slideBack';

		var p = {},
			width = "950",
			height = "850",
			e,
			a = /\+/g,  // Regex for replacing addition symbol with a space
			r = /([^&=]+)=?([^&]*)/g,
			d = function (s) { return decodeURIComponent(s.replace(a, ' ')); },
			q = url.split('?');				
		while (e = r.exec(q[1])) {
			e[1] = d(e[1]);
			e[2] = d(e[2]);
			if(e[1] != 'slide'){
				if(e[1] == 'width') {
					width = e[2];
				}
				else if(e[1] == 'height') {
					height = e[2];
				}
			}
			else {
				if(e[2] == 'top') {
					slide = 'slideDown';
				}
				else if(e[2] == 'bottom') {
					slide = 'slideUp';
				}
				else if(e[2] == 'right') {
					slide = 'slideBack';
				}
				else if(e[2] == 'left') {
					slide = 'slideIn';
				}
			}
		}
		jQuery("#popup").css('width', width+"px");
		jQuery("#popup").css('height', height+"px");
		
		q = url.split('/node/');
		jQuery('.c_area').html('');
		jQuery('.b-close span').html('');
		jQuery('#modal-popup').show();

		jQuery('#popup').bPopup({
			content:'iframe', //'ajax', 'iframe' or 'image'
			contentContainer:'.c_area',
			loadUrl:url, //Uses jQuery.load()
			speed: 650,
			amsl: 0,
			transition: slide,
			positionStyle: 'fixed',
			iframeAttr: ('scrolling="yes" frameborder="0"'),
			onOpen: function() {
				jQuery('#popup-load-img').show();
				jQuery('.c_area').append('<div class="loading-image"><img src="/sites/all/modules/custom/modal_node_display/images/dim-loading.gif"/></div>').html();
				jQuery('.b-close span').html("[Close]");
			},
			onClose: function() {
				jQuery('.c_area').empty();
				jQuery('.b-close span').empty();
			}
		}, function(){
			setTimeout( "jQuery('.loading-image').hide();", 2000);
			width = width - 10;
			height = height - 60;
			jQuery("#popup iframe").css('width', width+"px");
			jQuery("#popup iframe").css('height', height+"px");
		});
	});
});
