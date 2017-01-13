;/**
 * Off-Canvas Sidebars plugin
 *
 * ocsOffCanvasSidebars
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3.2
 * @global ocsOffCanvasSidebars
 */

if ( typeof ocsOffCanvasSidebars == 'undefined' ) {
	var ocsOffCanvasSidebars = {
		"site_close": true,
		"disable_over": false,
		"hide_control_classes": false,
		"scroll_lock": false,
		"legacy_css": false,
		"css_prefix": 'ocs',
		"sidebars": {},
		"_debug": false
	};
}

(function($) {

	ocsOffCanvasSidebars.slidebarsController = false;
	ocsOffCanvasSidebars.useAttributeSettings = false;
	ocsOffCanvasSidebars.container = false;
	ocsOffCanvasSidebars._html = $( 'html' );
	ocsOffCanvasSidebars._touchmove = false;
	ocsOffCanvasSidebars._toolbar = $('body').hasClass('admin-bar');

	ocsOffCanvasSidebars.init = function() {

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
		 * @param  key  the setting key to look for
		 * @param  sidebarId  bool|null|string  false = check for an active slidebar, null or no value = only the global setting
		 */
		ocsOffCanvasSidebars._getSetting = function( key, sidebarId ) {
			var overwrite, setting;
			var prefix = ocsOffCanvasSidebars.css_prefix;

			if ( typeof sidebarId != 'undefined' ) {
				if ( ! sidebarId && null !== sidebarId ) {
					sidebarId = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
				}
			}
			if ( sidebarId ) {

				if ( ! $.isEmptyObject( ocsOffCanvasSidebars.sidebars ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
					sidebarId = sidebarId.replace( prefix + '-', '' );
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
				setting = $( '#' + prefix + '-site' ).attr( 'ocs-' + key );
				if ( typeof setting != 'undefined' ) {
					return setting;
				}
			}

			return false;
		};

		ocsOffCanvasSidebars._getTranslateAxis = function( obj, axis ) {
			obj = $( obj );
			var transformMatrix = obj.css("-webkit-transform") ||
				obj.css("-moz-transform") ||
				obj.css("-ms-transform")  ||
				obj.css("-o-transform")   ||
				obj.css("transform");
			if ( transformMatrix ) {
				var matrix = transformMatrix.replace(/[^0-9\-.,]/g, '').split(',');
				var val = false;
				switch ( axis ) {
					case 'x':
						val = matrix[12] || matrix[4]; //translate x
						break;
					case 'y':
						val = matrix[13] || matrix[5]; //translate y
						break;
					case 'z':
						val = matrix[14] || matrix[6]; //translate z
						break;
				}
				return parseFloat( val );
			} else {
				return 0;
			}
		};

		ocsOffCanvasSidebars.container = $( '[canvas=container]' );

		$(window).trigger( 'ocs_loaded', this );

		// Slidebars constructor
		ocsOffCanvasSidebars.slidebarsController = new slidebars();

		if ( false === ocsOffCanvasSidebars.slidebarsController ) {
			return;
		}

		// Legacy CSS mode?
		if ( ocsOffCanvasSidebars.legacy_css ) {
			ocsOffCanvasSidebars.slidebarsController.legacy = true;
			ocsOffCanvasSidebars._html.addClass('ocs-legacy');
		}

		// Initialize slidebars
		ocsOffCanvasSidebars.slidebarsController.init();

		ocsOffCanvasSidebars._html.addClass('ocs-initialized');

		$(window).trigger( 'ocs_initialized', this );

		/**
		 * Compatibility with WP Admin Bar
		 * @since  0.3.2
 		 */
		if ( ocsOffCanvasSidebars._toolbar ) {
			$( window ).on('load resize', function() {
				var bodyOffset = $( 'body' ).offset();
				$( '.' + ocsOffCanvasSidebars.css_prefix + '-slidebar' ).each( function() {
					// Top slidebars
					if ( $(this).hasClass( 'ocs-location-top' ) ) {
						$(this).css( 'margin-top', parseInt( $(this).css('margin-top').replace( 'px', '' ) ) + bodyOffset.top + 'px' );
					}
					// Bottom slidebars
					else if ( $(this).hasClass( 'ocs-location-left' ) || $(this).hasClass( 'ocs-location-right' ) ) {
						$(this).css( 'margin-top', bodyOffset.top + 'px' );
					}
				} );
			} );
		}

		/**
		 * Fix position issues for fixed elements on slidebar animations
		 * @since  0.3.2
 		 */
		$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'opening opened closing closed', function( e, sidebar_id ) {
			var slidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id );
			var duration = parseFloat( slidebar.element.css( 'transitionDuration' ) /*, 10*/ ) * 1000;
			if ( slidebar.side == 'top' || slidebar.side == 'bottom' ) {
				var elements = $('#' + ocsOffCanvasSidebars.css_prefix + '-site *').filter( function(){ return $(this).css('position') === 'fixed'; } );
				elements.attr( { 'canvas-fixed': 'fixed' } );

				// Legacy mode (only needed for location: top)
				if ( ocsOffCanvasSidebars.legacy_css && slidebar.side == 'top' && slidebar.style != 'overlay' ) {
					var offset;
					if ( slidebar.style == 'reveal' ) {
						offset = 0; //parseInt( slidebar.element.css( 'height' ).replace('px', '') );
					} else {
						offset = parseInt( slidebar.element.css( 'margin-top' ).replace('px', '').replace('-', '') );
					}
					elements.each( function() {
						// Set animation
						if ( e.type == 'opening' ) {
							$(this).css( {
								'-webkit-transition': 'top ' + duration + 'ms',
								'-moz-transition': 'top ' + duration + 'ms',
								'-o-transition': 'top ' + duration + 'ms',
								'transition': 'top ' + duration + 'ms'
							} );
							$(this).css( 'top', parseInt( $(this).css('top').replace('px', '') ) + offset + 'px' );
						}
						// Remove animation
						else if ( e.type == 'closing' ) {
							$(this).css( 'top', parseInt( $(this).css('top').replace('px', '') ) - offset + 'px' );
							setTimeout( function() {
								$(this).css( {
									'-webkit-transition': '',
									'-moz-transition': '',
									'-o-transition': '',
									'transition': ''
								} );
							}, duration );
						}
					} );
				}
				// Normal mode (only sets a transition for use in fixed-scrolltop.js)
				else {
					elements.each( function() {
						//var curVal = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' );
						//console.log( curVal );
						if ( e.type == 'opening' || e.type == 'closing' ) {
							$( this ).css( {
								'-webkit-transition': 'transform ' + duration + 'ms',
								'-moz-transition': 'transform ' + duration + 'ms',
								'-o-transition': 'transform ' + duration + 'ms',
								'transition': 'transform ' + duration + 'ms'
							} );
							//$(this).css('transform', 'translate( 0px, ' + curVal + slidebar.element.height() + 'px )' );
						} else if ( e.type == 'opened' || e.type == 'closed' ) {
							$(this).css( {
								'-webkit-transition': '',
								'-moz-transition': '',
								'-o-transition': '',
								'transition': ''
							} );
						}
					} );
				}
				$(window).trigger( 'slidebar_event', [ e.type, slidebar ] );
			}
		} );

		// Prevent swipe events to be seen as a click (bug in some browsers)
		$( document ).on( 'touchmove', function() {
			ocsOffCanvasSidebars._touchmove = true;
		} );
		$( document ).on( 'touchstart', function() {
			ocsOffCanvasSidebars._touchmove = false;
		} );

		// Validate type, this could be changed with the hooks
		if ( typeof ocsOffCanvasSidebars.setupTriggers == 'function' ) {
			ocsOffCanvasSidebars.setupTriggers();
		}
	};

	/**
	 * Set the default settings for sidebars if they are not found

	 * @since  0.3
	 */
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

	ocsOffCanvasSidebars.setupTriggers = function() {
		var controller = ocsOffCanvasSidebars.slidebarsController,
			prefix = ocsOffCanvasSidebars.css_prefix,
			sidebarElements = $( '.' + prefix + '-slidebar' );

		sidebarElements.each( function() {
			var id = $( this ).attr( 'ocs-sidebar-id' );

			ocsOffCanvasSidebars.setSidebarDefaultSettings( id );

			/**
			 * Toggle the sidebar
			 * @since  0.1
			 */
			$( document ).on( 'touchend click', '.' + prefix + '-toggle-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				// Toggle the slidebar with respect for the disable_over setting
				if ( ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					controller.toggle( prefix + '-' + id );
				}
			} );

			/**
			 * Open the sidebar
			 * @since  0.3
			 */
			$( document ).on( 'touchend click', '.' + prefix + '-open-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				// Open the slidebar with respect for the disable_over setting
				if ( ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					controller.open( prefix + '-' + id );
				}
			} );

			/**
			 * Close the sidebar
			 * @since  0.3
			 */
			$( document ).on( 'touchend click', '.' + prefix + '-close-' + id, function( e ) {
				// Stop default action and bubbling
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				controller.close( prefix + '-' + id );
			} );

		} );


		// Close any
		$( document ).on( 'touchend click', '.' + prefix + '-close-any', function( e ) {
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
			$( '[canvas]' ).addClass( prefix + '-close-any' );
			ocsOffCanvasSidebars._html.addClass( 'ocs-sidebar-active' );
			if ( ocsOffCanvasSidebars._getSetting( 'scroll_lock', false ) ) {
				ocsOffCanvasSidebars._html.addClass( 'ocs-scroll-lock' );
			}
		} );

		// Add close class to canvas container when Slidebar is opened
		$( controller.events ).on( 'closing', function () {
			$( '[canvas]' ).removeClass( prefix + '-close-any' );
			ocsOffCanvasSidebars._html.removeClass( 'ocs-sidebar-active ocs-scroll-lock' );
		} );


		// Disable slidebars when the window is wider than the set width
		var disableOver = function() {
			var prefix = ocsOffCanvasSidebars.css_prefix;
			sidebarElements.each( function() {
				var id = $( this ).attr( 'ocs-sidebar-id' );

				if ( ! ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					if ( controller.isActiveSlidebar( prefix + '-' + id ) ) {
						controller.close();
					}
					// Hide control classes
					if ( ocsOffCanvasSidebars._getSetting( 'hide_control_classes', prefix + '-' + id ) ) {
						$( '.' + prefix + '-toggle-' + id ).hide();
					}
				} else {
					$( '.' + prefix + '-toggle-' + id ).show();
				}

			} );
		};
		disableOver();
		$( window ).on( 'resize', disableOver );

		// Disable scrolling outside of active sidebar
		$( '#' + prefix + '-site' ).on( 'scroll touchmove mousewheel DOMMouseScroll', function( e ) {
			//if ( ocsOffCanvasSidebars._getSetting( 'scroll_lock' ) && false != controller.getActiveSlidebar() ) {
			if ( ocsOffCanvasSidebars._html.hasClass( 'ocs-scroll-lock' ) ) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		} );

		// Disable scrolling site when scrolling inside active sidebar
		sidebarElements.on( 'scroll touchmove mousewheel DOMMouseScroll', function( e ) {
			//if ( ocsOffCanvasSidebars._getSetting( 'scroll_lock', false ) ) {
			if ( ocsOffCanvasSidebars._html.hasClass( 'ocs-scroll-lock' ) ) {
				var $this = $(this);
				if ( e.originalEvent.deltaY < 0 ) {
					/* scrolling up */
					return ( $this.scrollTop() > 0 );
				} else {
					/* scrolling down */
					return ( $this.scrollTop() + $this.innerHeight() < $this[0].scrollHeight );
				}
			}
		} );

	};

	if ( $( '#' + ocsOffCanvasSidebars.css_prefix + '-site' ).length && ( typeof slidebars != 'undefined' ) ) {
		ocsOffCanvasSidebars.init();
	}

}) (jQuery);