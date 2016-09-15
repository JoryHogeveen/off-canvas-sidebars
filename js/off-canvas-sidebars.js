;/**
 * Off-Canvas Sidebars plugin
 *
 * OCS_OFF_CANVAS_SIDEBARS
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3
 */

if ( typeof OCS_OFF_CANVAS_SIDEBARS == 'undefined' ) {
	var OCS_OFF_CANVAS_SIDEBARS = {
		"site_close": true,
		"disable_over": false,
		"hide_control_classes": false,
		"scroll_lock": false,
		"sidebars": {}
	};
}

(function($) {

	OCS_OFF_CANVAS_SIDEBARS.slidebars_controller = false;

	OCS_OFF_CANVAS_SIDEBARS.init = function() {

		if ( false === OCS_OFF_CANVAS_SIDEBARS.slidebars_controller ) {
			return;
		}
		OCS_OFF_CANVAS_SIDEBARS.slidebars_controller.init();
		var controller = OCS_OFF_CANVAS_SIDEBARS.slidebars_controller;


		$( '.sb-slidebar' ).each( function(e) {
			var id = $( this ).attr('off-canvas-sidebar-id');
			$(document).on( 'touchend click', '.sb-toggle-' + id, function(e) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Toggle the slidebar with respect for the disable_over setting
				if ( OCS_OFF_CANVAS_SIDEBARS.checkDisableOver( 'sb-' + id ) ) {
					controller.toggle( 'sb-' + id );
				}
			} );
		} );


		// Close any
		$( document ).on( 'touchend click', '.sb-close-any', function( e ) {
			if ( parseInt( OCS_OFF_CANVAS_SIDEBARS.getSetting( 'site_close', false ) ) ) {
				e.preventDefault();
				e.stopPropagation();
				controller.close();
			}
		} );


		// Close Slidebars when clicking on a link within a slidebar
		$( '[off-canvas] a' ).on( 'touchend click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			var url = $( this ).attr( 'href' ),
			target = $( this ).attr( 'target' ) ? $( this ).attr( 'target' ) : '_self';

			controller.close( function () {
				window.open( url, target );
			} );
		} );


		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'opening', function ( e ) {
			$( '[canvas]' ).addClass( 'sb-close-any' );
		} );

		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'closing', function ( e ) {
			$( '[canvas]' ).removeClass( 'sb-close-any' );
		} );


		// Disable slidebars when the window is wider than the set width
		var disableOver = function() {
			$( '.sb-slidebar' ).each( function(e) {
				var id = $( this ).attr('off-canvas-sidebar-id');

				if ( ! OCS_OFF_CANVAS_SIDEBARS.checkDisableOver( 'sb-' + id ) ) {
					if ( controller.isActiveSlidebar( 'sb-' + id ) ) {
						controller.close();
					}
					// Hide control classes
					if ( parseInt( OCS_OFF_CANVAS_SIDEBARS.getSetting( 'hide_control_classes', 'sb-' + id ) ) ) {
						$( '.sb-toggle-' + id ).hide();
					}
				} else {
					$( '.sb-toggle-' + id ).show();
				}

			} );
		};
		disableOver();
		$( window ).on( 'resize', disableOver );

		// Disable scrolling on active sidebar
		$('#sb-site').on( 'scroll touchmove mousewheel', function( e ) {
			if ( parseInt( OCS_OFF_CANVAS_SIDEBARS.getSetting( 'scroll_lock' ) ) && false != controller.getActiveSlidebar() ) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		} );

	};

	OCS_OFF_CANVAS_SIDEBARS.checkDisableOver = function( sidebarId ) {
		var check = true;
		disable_over = parseInt( OCS_OFF_CANVAS_SIDEBARS.getSetting( 'disable_over', sidebarId ) );
		if ( disable_over && ! isNaN( disable_over ) ) {
			if ( $( window ).width() > disable_over ) {
	  			check = false;
	  		}
		}
		return check;
	};

	OCS_OFF_CANVAS_SIDEBARS.getSetting = function( key, sidebarId ) {

		if ( ! sidebarId ) {
			sidebarId = OCS_OFF_CANVAS_SIDEBARS.slidebars_controller.getActiveSlidebar();
		}
		if ( sidebarId ) {
			if ( ! $.isEmptyObject( OCS_OFF_CANVAS_SIDEBARS.sidebars ) ) {
				sidebarId = sidebarId.replace('sb-', '');
				var overwrite = OCS_OFF_CANVAS_SIDEBARS.sidebars[sidebarId].overwrite_global_settings;
				if ( overwrite ) {
					var setting = OCS_OFF_CANVAS_SIDEBARS.sidebars[sidebarId].key;
					if ( setting ) {
						return setting;
					} else {
						return false;
					}
				}
			} else {
				var overwrite = $('#' + sidebarId).attr('off-canvas-overwrite_global_settings');
				if ( typeof overwrite !== undefined && overwrite ) {
					var setting = $('#' + sidebarId).attr('off-canvas-' + key);
					if ( typeof setting != 'undefined' ) {
						return setting;
					} else {
						return false;
					}
				}
			}
		}

		if ( typeof OCS_OFF_CANVAS_SIDEBARS[ key ] != 'undefined' ) {
			return OCS_OFF_CANVAS_SIDEBARS[ key ];
		} else {
			return false;
		}
	};

	if ( $('#sb-site').length > 0 && ( typeof slidebars != 'undefined' ) ) {
		OCS_OFF_CANVAS_SIDEBARS.slidebars_controller = new slidebars();
		OCS_OFF_CANVAS_SIDEBARS.init();
	}

}) (jQuery);