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
	ocsOffCanvasSidebars.touchmove = false;

	ocsOffCanvasSidebars.init = function() {

		/**
		 * Function call before initializing
		 * @since  0.3
		 */
		if ( typeof ocsBeforeInitHook == 'function' ) {
			ocsBeforeInitHook();
		}

		// Slidebars constructor
		ocsOffCanvasSidebars.slidebarsController = new slidebars();

		if ( false === ocsOffCanvasSidebars.slidebarsController ) {
			return;
		}
		// Initialize slidebars
		ocsOffCanvasSidebars.slidebarsController.init();

		/**
		 * Function call after initializing
		 * @since  0.3
		 */
		if ( typeof ocsAfterInitHook == 'function' ) {
			ocsAfterInitHook();
		}

		/**
		 * Validate the disable_over setting ( using _getSetting() )

		 * Internal function, do not overwrite

		 * @since  0.3
		 */
		ocsOffCanvasSidebars._checkDisableOver = function( sidebarId ) {
			var check = true;
			var disableOver = parseInt( ocsOffCanvasSidebars._getSetting( 'disable_over', sidebarId ) );
			if ( disableOver && ! isNaN( disableOver ) ) {
				if ( $( window ).width() > disableOver ) {
		  			check = false;
		  		}
			}
			return check;
		};

		/**
		 * Get the global setting or the sidebar setting (if set to overwrite)

		 * Internal function, do not overwrite

		 * @since  0.3
		 */
		ocsOffCanvasSidebars._getSetting = function( key, sidebarId ) {
			var overwrite, setting;

			if ( ! sidebarId ) {
				sidebarId = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
			}
			if ( sidebarId ) {

				if ( ! $.isEmptyObject( ocsOffCanvasSidebars.sidebars ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
					sidebarId = sidebarId.replace( 'ocs-', '' );
					if ( ocsOffCanvasSidebars.sidebars[ sidebarId ].overwrite_global_settings ) {
						setting = ocsOffCanvasSidebars.sidebars[ sidebarId ][ key ];
						if ( setting ) {
							return setting;
						} else {
							return false;
						}
					}

				// Fallback/Overwrite to enable sidebar settings from available attributes
				} else {
					var sidebarElement = $( '#' + sidebarId );
					overwrite = sidebarElement.attr( 'ocs-overwrite_global_settings' );
					if ( typeof overwrite !== 'undefined' && overwrite ) {
						setting = sidebarElement.attr( 'ocs-' + key );
						if ( typeof setting != 'undefined' ) {
							return setting;
						} else {
							return false;
						}
					}
				}
			}

			if ( typeof ocsOffCanvasSidebars[ key ] != 'undefined' && ! ocsOffCanvasSidebars.useAttributeSettings ) {
				return ocsOffCanvasSidebars[ key ];
			} else {
				setting = $( '#ocs-site' ).attr( 'ocs-' + key );
				if ( typeof setting != 'undefined' ) {
					return setting;
				}
			}

			return false;
		};

		// Prevent touch+swipe
		$( document ).on( 'touchmove', function() {
			ocsOffCanvasSidebars.touchmove = true;
		} );
		$( document ).on( 'touchstart', function() {
			ocsOffCanvasSidebars.touchmove = false;
		} );

		// Validate type, this could be changed with the hooks
		if ( typeof ocsOffCanvasSidebars.setupTriggers == 'function' ) {
			ocsOffCanvasSidebars.setupTriggers();
		}
	};

	ocsOffCanvasSidebars.setupTriggers = function() {
		var controller = ocsOffCanvasSidebars.slidebarsController;

		$( '.ocs-slidebar' ).each( function() {
			var id = $( this ).attr( 'ocs-sidebar-id' );

			ocsOffCanvasSidebars.setSidebarDefaultSettings( id );

			/**
			 * Toggle the sidebar
			 * @since  0.1
			 */
			$( document ).on( 'touchend click', '.ocs-toggle-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars.touchmove ) {
					return;
				}

				// Toggle the slidebar with respect for the disable_over setting
				if ( ocsOffCanvasSidebars._checkDisableOver( 'ocs-' + id ) ) {
					controller.toggle( 'ocs-' + id );
				}
			} );

			/**
			 * Open the sidebar
			 * @since  0.3
			 */
			$( document ).on( 'touchend click', '.ocs-open-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars.touchmove ) {
					return;
				}

				// Open the slidebar with respect for the disable_over setting
				if ( ocsOffCanvasSidebars._checkDisableOver( 'ocs-' + id ) ) {
					controller.open( 'ocs-' + id );
				}
			} );

			/**
			 * Close the sidebar
			 * @since  0.3
			 */
			$( document ).on( 'touchend click', '.ocs-close-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars.touchmove ) {
					return;
				}

				// Close the slidebar, no need to check the disable_over setting since we're closing the slidebar
				//if ( ocsOffCanvasSidebars._checkDisableOver( 'ocs-' + id ) ) {
					controller.close( 'ocs-' + id );
				//}
			} );

		} );


		// Close any
		$( document ).on( 'touchend click', '.ocs-close-any', function( e ) {
			if ( ocsOffCanvasSidebars._getSetting( 'site_close', false ) ) {
				e.preventDefault();
				e.stopPropagation();
				controller.close();
			}
		} );


		// Close Slidebars when clicking on a link within a slidebar
		/*$( '[off-canvas] a' ).on( 'touchend click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			var url = $( this ).attr( 'href' ),
			target = $( this ).attr( 'target' ) ? $( this ).attr( 'target' ) : '_self';

			controller.close( function () {
				window.open( url, target );
			} );
		} );*/


		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'opening', function () {
			$( '[canvas]' ).addClass( 'ocs-close-any' );
		} );

		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'closing', function () {
			$( '[canvas]' ).removeClass( 'ocs-close-any' );
		} );


		// Disable slidebars when the window is wider than the set width
		var disableOver = function() {
			$( '.ocs-slidebar' ).each( function() {
				var id = $( this ).attr( 'ocs-sidebar-id' );

				if ( ! ocsOffCanvasSidebars._checkDisableOver( 'ocs-' + id ) ) {
					if ( controller.isActiveSlidebar( 'ocs-' + id ) ) {
						controller.close();
					}
					// Hide control classes
					if ( ocsOffCanvasSidebars._getSetting( 'hide_control_classes', 'ocs-' + id ) ) {
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
			if ( ocsOffCanvasSidebars._getSetting( 'scroll_lock' ) && false != controller.getActiveSlidebar() ) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		} );

	};

	ocsOffCanvasSidebars.setSidebarDefaultSettings = function( sidebarId ) {

		if ( typeof ocsOffCanvasSidebars.sidebars[ sidebarId ] == 'undefined' ) {
			ocsOffCanvasSidebars.sidebars[ sidebarId ] = {
				'overwrite_global_settings': false,
				"site_close": ocsOffCanvasSidebars.site_close,
				"disable_over": ocsOffCanvasSidebars.disable_over,
				"hide_control_classes": ocsOffCanvasSidebars.hide_control_classes,
				"scroll_lock": ocsOffCanvasSidebars.scroll_lock
			}
		}
	};

	if ( $( '#ocs-site' ).length && ( typeof slidebars != 'undefined' ) ) {
		ocsOffCanvasSidebars.init();
	}

}) (jQuery);