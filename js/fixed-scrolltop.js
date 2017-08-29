;/**
 * Off-Canvas Sidebars plugin fixed-scrolltop.js
 *
 * Compatibility for fixed elements with Slidebars.
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.4
 * @global ocsOffCanvasSidebars
 * @preserve
 */
( function( $ ) {

	var $window = $(window);
	var $body = $('body');

	$window.on( 'ocs_initialized', function () {

		var prefix = ocsOffCanvasSidebars.css_prefix;

		if ( ocsOffCanvasSidebars._debug ) {
			console.log('start fixed-scrolltop.js');
		}

		var curScrollTopElements;
		var scrollTarget = ocs_site = ocsOffCanvasSidebars.container;
		if ( 'auto' !== ocs_site.css('overflow-y') ) {
			scrollTarget = $window;
		}

		function run() {
			if ( 'none' !== ocs_site.css('transform') ) {
				curScrollTopElements = ocsOffCanvasSidebars.getFixedElements();
				ocsOffCanvasSidebars.scrollTopFixed();
				scrollTarget.on( 'scroll resize', function() {
					var newScrollTopElements = ocsOffCanvasSidebars.getFixedElements();
					curScrollTopElements = curScrollTopElements.add( newScrollTopElements );
					ocsOffCanvasSidebars.scrollTopFixed();
				} );
				scrollTarget.on( 'slidebar_event', ocsOffCanvasSidebars.slidebarEventFixed );
			}
		}

		ocsOffCanvasSidebars.slidebarEventFixed = function( e, eventType, slidebar ) {
			// Bottom slidebars.
			if ( ( 'bottom' === slidebar.side && 'overlay' !== slidebar.style ) && ( 'opening' === eventType || 'closing' === eventType ) ) {
				curScrollTopElements.each( function() {
					var px = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' );
					var offset = slidebar.element.height();
					if ( 'opening' === eventType ) {
						px += offset;
					} else if ( 'closing' === eventType ) {
						px -= offset;
					}
					ocsOffCanvasSidebars.cssCompat( $(this), 'transform', 'translate( 0px, ' + px + 'px )' );
				} );
			}
		};

		ocsOffCanvasSidebars.scrollTopFixed = function() {
			if ( curScrollTopElements.length ) {
				var scrollTop = scrollTarget.scrollTop(),
				    winHeight = $window.height(),
				    gblOffset = $body.offset(),
				    conOffset = ocs_site.offset(),
				    conHeight = ocs_site.outerHeight();

				var activeSidebar = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
				if ( activeSidebar ) {
					var sidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( activeSidebar );
					if ( 'top' === sidebar.side ) { //|| 'bottom' === sidebar.side
						gblOffset.top += sidebar.element.height();
					}/* else if ( 'bottom' === sidebar.side ) {
						gblOffset.top -= sidebar.element.height();
					}*/
				}
				curScrollTopElements.each( function() {
					var $this = $(this);
					if ( 'fixed' === $this.css('position') ) {
						var top = $this.css('top'),
						    bottom = $this.css('bottom'),
							px;
						if ( 'auto' === top && 'auto' !== bottom ) {
							px = ( scrollTop + winHeight ) - ( conOffset.top + conHeight ) + gblOffset.top;
						} else {
							px = scrollTop - conOffset.top + gblOffset.top;
						}
						ocsOffCanvasSidebars.cssCompat( $this, 'transform', 'translate( 0px, ' + px + 'px )' );
					} else {
						ocsOffCanvasSidebars.cssCompat( $this, 'transform', '' );
					}
				} );
			}
		};

		run();

	});

} ) ( jQuery );