/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars - Compatibility for fixed elements with Slidebars.
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.2
 * @version 0.5.7
 * @global  ocsOffCanvasSidebars
 * @preserve
 */
/* eslint-enable no-extra-semi */

( function( $ ) {

	var $window = $(window),
		$body = $('body');

	$window.on( 'ocs_initialized', function () {

		ocsOffCanvasSidebars.debug( 'start fixed-scrolltop.js' );

		var curScrollTopElements,
			scrollTarget = ocs_site = ocsOffCanvasSidebars.container;
		if ( 'auto' !== ocs_site.css( 'overflow-y' ) ) {
			scrollTarget = $window;
		}

		function run() {
			if ( 'none' !== ocs_site.css( 'transform' ) ) {
				curScrollTopElements = ocsOffCanvasSidebars.getFixedElements();
				ocsOffCanvasSidebars.scrollTopFixed();
				scrollTarget.on( 'scroll resize', function() {
					var newScrollTopElements = ocsOffCanvasSidebars.getFixedElements();
					curScrollTopElements = curScrollTopElements.add( newScrollTopElements );
					ocsOffCanvasSidebars.scrollTopFixed();
				} );
				ocsOffCanvasSidebars.events.add_action( 'opening closing', 'ocs_fixed_scrolltop', ocsOffCanvasSidebars.slidebarEventFixed, 99 );
			}
		}

		ocsOffCanvasSidebars.slidebarEventFixed = function( e, eventType, slidebar ) {
			// Bottom slidebars.
			if ( ( 'bottom' === slidebar.side && 'overlay' !== slidebar.style ) && ( 'opening' === eventType || 'closing' === eventType ) ) {
				curScrollTopElements.each( function() {
					var px = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' ),
						offset = slidebar.element.height();
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
				var scrollTop     = scrollTarget.scrollTop(),
				    winHeight     = $window.height(),
				    gblOffset     = $body.offset(),
				    conOffset     = ocs_site.offset(),
				    conHeight     = ocs_site.outerHeight(),
					activeSidebar = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();

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
					if ( 'fixed' === $this.css( 'position' ) ) {
						var top = $this.css( 'top' ),
						    bottom = $this.css( 'bottom' ),
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

} ( jQuery ) );
