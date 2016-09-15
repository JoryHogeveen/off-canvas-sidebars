;/**
 * Off-Canvas Sidebars plugin
 *
 * ocsOffCanvasSidebars
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3
 */

/* global ocsOffCanvasSidebars */
if ( typeof ocsOffCanvasSidebars == 'undefined' ) {
	var ocsOffCanvasSidebars = {
		"site_close": true,
		"disable_over": false,
		"hide_control_classes": false,
		"scroll_lock": false,
		"sidebars": {}
	};
}

(function($) {

	ocsOffCanvasSidebars.slidebarsController = false;
	ocsOffCanvasSidebars.useAttributeSettings = false;

	ocsOffCanvasSidebars.init = function() {

		if ( false === ocsOffCanvasSidebars.slidebarsController ) {
			return;
		}
		ocsOffCanvasSidebars.slidebarsController.init();
		var controller = ocsOffCanvasSidebars.slidebarsController;


		$( '.ocs-slidebar' ).each( function(e) {
			var id = $( this ).attr( 'ocs-sidebar-id' );
			console.log('test');
			$( document ).on( 'touchend click', '.ocs-toggle-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Toggle the slidebar with respect for the disable_over setting
				if ( ocsOffCanvasSidebars.checkDisableOver( 'ocs-' + id ) ) {
					controller.toggle( 'ocs-' + id );
				}
			} );
		} );


		// Close any
		$( document ).on( 'touchend click', '.ocs-close-any', function( e ) {
			if ( parseInt( ocsOffCanvasSidebars.getSetting( 'site_close', false ) ) ) {
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
			$( '[canvas]' ).addClass( 'ocs-close-any' );
		} );

		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'closing', function ( e ) {
			$( '[canvas]' ).removeClass( 'ocs-close-any' );
		} );


		// Disable slidebars when the window is wider than the set width
		var disableOver = function() {
			$( '.ocs-slidebar' ).each( function( e ) {
				var id = $( this ).attr( 'ocs-sidebar-id' );

				if ( ! ocsOffCanvasSidebars.checkDisableOver( 'ocs-' + id ) ) {
					if ( controller.isActiveSlidebar( 'ocs-' + id ) ) {
						controller.close();
					}
					// Hide control classes
					if ( parseInt( ocsOffCanvasSidebars.getSetting( 'hide_control_classes', 'ocs-' + id ) ) ) {
						$( '.ocs-toggle-' + id ).hide();
					}
				} else {
					$( '.ocs-toggle-' + id ).show();
				}

			} );
		};
		disableOver();
		$( window ).on( 'resize', disableOver );

		// Disable scrolling on active sidebar
		$( '#ocs-site' ).on( 'scroll touchmove mousewheel', function( e ) {
			if ( parseInt( ocsOffCanvasSidebars.getSetting( 'scroll_lock' ) ) && false != controller.getActiveSlidebar() ) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		} );

	};

	ocsOffCanvasSidebars.checkDisableOver = function( sidebarId ) {
		var check = true;
		disableOver = parseInt( ocsOffCanvasSidebars.getSetting( 'disable_over', sidebarId ) );
		if ( disableOver && ! isNaN( disableOver ) ) {
			if ( $( window ).width() > disableOver ) {
	  			check = false;
	  		}
		}
		return check;
	};

	ocsOffCanvasSidebars.getSetting = function( key, sidebarId ) {

		if ( ! sidebarId ) {
			sidebarId = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
		}
		if ( sidebarId ) {
			var overwrite, setting;

			if ( ! $.isEmptyObject( ocsOffCanvasSidebars.sidebars ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
				sidebarId = sidebarId.replace( 'ocs-', '' );
				overwrite = ocsOffCanvasSidebars.sidebars[ sidebarId ].overwrite_global_settings;
				if ( overwrite ) {
					setting = ocsOffCanvasSidebars.sidebars[ sidebarId ].key;
					if ( setting ) {
						return setting;
					} else {
						return false;
					}
				}

			// Fallback/Overwrite to enable sidebar settings from available attributes
			} else {
				overwrite = $( '#' + sidebarId ).attr( 'ocs-overwrite_global_settings' );
				if ( typeof overwrite !== undefined && overwrite ) {
					setting = $( '#' + sidebarId ).attr( 'ocs-' + key );
					if ( typeof setting != 'undefined' ) {
						return setting;
					} else {
						return false;
					}
				}
			}
		}

		if ( typeof ocsOffCanvasSidebars[ key ] != 'undefined' ) {
			return ocsOffCanvasSidebars[ key ];
		} else {
			return false;
		}
	};

	if ( $( '#ocs-site' ).length && ( typeof slidebars != 'undefined' ) ) {
		ocsOffCanvasSidebars.slidebarsController = new slidebars();
		ocsOffCanvasSidebars.init();
	}

}) (jQuery);