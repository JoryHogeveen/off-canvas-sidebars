;/**
 * Off-Canvas Sidebars plugin fixed-scrolltop.js
 *
 * Compatibility for fixed elements with Slidebars
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.3.1
 */
( function ( $ ) {

	$(window).load(function () {

		var prefix = 'ocs';
		if ( typeof ocsOffCanvasSidebars != 'undefined' ) {
			prefix = ocsOffCanvasSidebars.css_prefix;
		}

		var curScrollTopElements;
		var ocs_site = $('#' + prefix + '-site');

		if ( ocs_site.css('transform') != 'none' ) {
			curScrollTopElements = $('#' + prefix + '-site *').filter( function(){ return $(this).css('position') === 'fixed'; } );
			ocsScrollTopFixed();
			ocs_site.on('scroll resize', function() {
				var newScrollTopElements = $('#' + prefix + '-site *').filter( function(){ return $(this).css('position') === 'fixed'; } );
				curScrollTopElements = curScrollTopElements.add(newScrollTopElements);
				ocsScrollTopFixed();
			});
		}

		function ocsScrollTopFixed() {
			if ( curScrollTopElements.length > 0 ) {
				var scrollTop = ocs_site.scrollTop();
				var winHeight = $(window).height();
				var conOffset = ocs_site.offset();
				var conHeight = ocs_site.outerHeight();
				curScrollTopElements.each( function() {
					if ( $(this).css('position') == 'fixed' ) {
						var top = $(this).css('top');
						var bottom = $(this).css('bottom');
						var px;
						if ( top == 'auto' && bottom != 'auto' ) {
							px = ( scrollTop + winHeight ) - ( conOffset.top + conHeight );
						} else {
							px = scrollTop - conOffset.top;
						}
						$(this).css({
							'-webkit-transform': 'translateY(' + px + 'px)',
							'-moz-transform': 'translateY(' + px + 'px)',
							'transform': 'translateY(' + px + 'px)'
						});
					} else {
						$(this).css({
							'-webkit-transform' : '',
							'-moz-transform' : '',
							'transform' : ''
						});
					}
				} );
			}
		}

	});

} ) ( jQuery );