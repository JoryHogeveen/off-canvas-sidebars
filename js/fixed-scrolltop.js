;/**
 * Off-Canvas Sidebars plugin fixed-scrolltop.js
 *
 * Compatibility for fixed elements with Slidebars
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.4
 */
( function ( $ ) {

	$(window).on( 'ocs_initialized', function () {

		var prefix = ocsOffCanvasSidebars.css_prefix;

		var curScrollTopElements;
		var scrollTarget = ocs_site = ocsOffCanvasSidebars.container;
		if ( 'auto' != ocs_site.css('overflow-y') ) {
			scrollTarget = $( window );
		}

		if ( ocs_site.css('transform') != 'none' ) {
			curScrollTopElements = $('#' + prefix + '-site *').filter( function() { return $(this).css('position') === 'fixed'; } );
			ocsOffCanvasSidebars.scrollTopFixed();
			scrollTarget.on( 'scroll resize', function() {
				var newScrollTopElements = $('#' + prefix + '-site *').filter( function() { return $(this).css('position') === 'fixed'; } );
				curScrollTopElements = curScrollTopElements.add( newScrollTopElements );
				ocsOffCanvasSidebars.scrollTopFixed();
			} );
			scrollTarget.on( 'slidebar_event', ocsOffCanvasSidebars.slidebarEventFixed );
		}

		ocsOffCanvasSidebars.slidebarEventFixed = function( e, eventType, slidebar ) {
			if ( ( eventType == 'opened' || eventType == 'closed' ) && ( slidebar.side == 'bottom' ) ) { //slidebar.side == 'top' ||
				curScrollTopElements.each( function() {
					var px = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' );
					var offset = slidebar.element.height();
					if ( eventType == 'opened' ) {
						px += offset;
					} else if ( eventType == 'closed' ) {
						px -= offset;
					}
					$( this ).css( {
						'-webkit-transform': 'translate( 0px, ' + px + 'px )',
						'-moz-transform': 'translate( 0px, ' + px + 'px )',
						'-o-transform': 'translate( 0px, ' + px + 'px )',
						'transform': 'translate( 0px, ' + px + 'px )'
					} );
				} );
			}
		};

		ocsOffCanvasSidebars.scrollTopFixed = function() {
			if ( curScrollTopElements.length > 0 ) {
				var scrollTop = scrollTarget.scrollTop(),
				    winHeight = $(window).height(),
				    gblOffset = $('body').offset(),
				    conOffset = ocs_site.offset(),
				    conHeight = ocs_site.outerHeight();

				var activeSidebar = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
				if ( activeSidebar ) {
					var sidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( activeSidebar );
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
		};

	});

} ) ( jQuery );