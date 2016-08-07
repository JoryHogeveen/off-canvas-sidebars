;/**
 * Off-Canvas Sidebars plugin
 *
 * OCS_OFF_CANVAS_SIDEBARS
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2.0
 */

if ( typeof OCS_OFF_CANVAS_SIDEBARS == 'undefined' ) {
	var OCS_OFF_CANVAS_SIDEBARS = {
		"site_close":"",
		"disable_over":"",
		"hide_control_classes":"",
		"scroll_lock":"",
		"sidebars":{}
	};
}

(function($) {

	OCS_OFF_CANVAS_SIDEBARS.slidebars_controller = false;

	OCS_OFF_CANVAS_SIDEBARS.init = function() {

		if ( false === OCS_OFF_CANVAS_SIDEBARS.slidebars_controller ) {
			return;
		}

		var controller = OCS_OFF_CANVAS_SIDEBARS.slidebars_controller;
		controller.init();

		$( '.sb-slidebar' ).each( function(e) {
			var id = $( this ).attr('off-canvas-sidebar-id');
			$(document).on( 'click', '.sb-toggle-' + id, function(e) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Toggle the slidebar with respect for the disable_over setting
				if ( OCS_OFF_CANVAS_SIDEBARS.disable_over ) {
				if ( $( window ).width() <= disable_over ) {
  					controller.toggle( 'sb-' + id );
  				}
				} else {
					controller.toggle( 'sb-' + id );
				}
			} );
		} );

		// Close any
		$( document ).on( 'click', '.sb-close-any', function( e ) {
			if ( controller.getActiveSlidebar() ) {
				e.preventDefault();
				e.stopPropagation();
				controller.close();
			}
		} );

		// Close Slidebars when clicking on a link within a slidebar
		$( '[off-canvas] a' ).on( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			var url = $( this ).attr( 'href' ),
			target = $( this ).attr( 'target' ) ? $( this ).attr( 'target' ) : '_self';

			controller.close( function () {
				window.open( url, target );
			} );
		} );

		if ( OCS_OFF_CANVAS_SIDEBARS.site_close ) {
			// Add close class to canvas container when Slidebar is opened
			$( controller.events ).on( 'opening', function ( e ) {
				$( '[canvas]' ).addClass( 'sb-close-any' );
			} );

			// Add close class to canvas container when Slidebar is opened
			$( controller.events ).on( 'closing', function ( e ) {
				$( '[canvas]' ).removeClass( 'sb-close-any' );
			} );
		}

		// Disable slidebars when the window is wider than the set width
		if ( OCS_OFF_CANVAS_SIDEBARS.disable_over ) {
			var disableOver = function() {
				if ( $( window ).width() > OCS_OFF_CANVAS_SIDEBARS.disable_over ) {
					controller.close();
					// Hide control classes
					if ( OCS_OFF_CANVAS_SIDEBARS.hide_control_classes ) {
						$( '.sb-toggle' ).hide();
					}
				} else {
					$( '.sb-toggle' ).show();
				}
			};
			disableOver();
			$( window ).on( 'resize', disableOver );

		}

		if ( OCS_OFF_CANVAS_SIDEBARS.scroll_lock ) {
			$('#sb-site').on( 'scroll touchmove mousewheel', function( e ) {
				if ( false != controller.getActiveSlidebar() ) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			} );
		}

	};

	if ( $('#sb-site').length > 0 && ( typeof slidebars != 'undefined' ) ) {
		OCS_OFF_CANVAS_SIDEBARS.slidebars_controller = new slidebars();
		OCS_OFF_CANVAS_SIDEBARS.init();
	}

}) (jQuery);