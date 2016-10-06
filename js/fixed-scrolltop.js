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
		var scrollTarget = ocs_site = $('#' + prefix + '-site');
		if ( 'auto' != ocs_site.css('overflow-y') ) {
			scrollTarget = $( window );
		}

		if ( ocs_site.css('transform') != 'none' ) {
			curScrollTopElements = $('#' + prefix + '-site *').filter( function(){ return $(this).css('position') === 'fixed'; } );
			ocsScrollTopFixed();
			scrollTarget.on('scroll resize', function() {
				var newScrollTopElements = $('#' + prefix + '-site *').filter( function(){ return $(this).css('position') === 'fixed'; } );
				curScrollTopElements = curScrollTopElements.add( newScrollTopElements );
				ocsScrollTopFixed();
			} );
		}

		function ocsScrollTopFixed() {
			if ( curScrollTopElements.length > 0 ) {
				var scrollTop = scrollTarget.scrollTop(),
				    winHeight = $(window).height(),
				    gblOffset = $('body').offset(),
				    conOffset = ocs_site.offset(),
				    conHeight = ocs_site.outerHeight();

				var activeSidebar = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
				if ( activeSidebar ) {
					var sidebar =  ocsOffCanvasSidebars.slidebarsController.getSlidebar( activeSidebar );
					if ( sidebar.side == 'top' ) {
						gblOffset.top += sidebar.element.height();
					} else if ( sidebar.side == 'top' ) {
						gblOffset.top -= sidebar.element.height();
					}
				}
				curScrollTopElements.each( function() {
					if ( $(this).css('position') == 'fixed' ) {
						var top = $(this).css('top'),
						    bottom = $(this).css('bottom'),
							px;
						if ( top == 'auto' && bottom != 'auto' ) {
							px = ( scrollTop + winHeight ) - ( conOffset.top + conHeight ) + gblOffset.top;
						} else {
							px = scrollTop - conOffset.top + gblOffset.top;
						}
						$(this).css({
							'-webkit-transform': 'translate( 0px, ' + px + 'px )',
							'-moz-transform': 'translate( 0px, ' + px + 'px )',
							'-o-transform': 'translate( 0px, ' + px + 'px )',
							'transform': 'translate( 0px, ' + px + 'px )'
						});
					} else {
						$(this).css({
							'-webkit-transform' : '',
							'-moz-transform' : '',
							'-o-transform' : '',
							'transform' : ''
						});
					}
				} );
			}
		}

	});

} ) ( jQuery );