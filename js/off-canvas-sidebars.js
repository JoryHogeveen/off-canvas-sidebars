/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars plugin
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.2.0
 * @version 0.5.7
 * @global  ocsOffCanvasSidebars
 * @preserve
 */
/* eslint-enable no-extra-semi */

if ( 'undefined' === typeof ocsOffCanvasSidebars ) {
	var ocsOffCanvasSidebars = {
		late_init: false,
		site_close: true,
		link_close: true,
		disable_over: false,
		hide_control_classes: false,
		scroll_lock: false,
		legacy_css: false,
		css_prefix: 'ocs',
		sidebars: {},
		_debug: false
	};
}

( function( $ ) {

	var $document = $( document ),
		$window   = $( window ),
		$html     = $( 'html' ),
		$body     = $( 'body' );

	ocsOffCanvasSidebars.slidebarsController  = false;
	ocsOffCanvasSidebars.useAttributeSettings = false;
	ocsOffCanvasSidebars.container            = false;
	ocsOffCanvasSidebars._touchmove           = false;
	ocsOffCanvasSidebars._toolbar             = ( $body.hasClass( 'admin-bar' ) ) ? $( '#wpadminbar' ) : null;

	// Prevent swipe events to be seen as a click (bug in some browsers).
	$document.on( 'touchmove', function() {
		ocsOffCanvasSidebars._touchmove = true;
	} );
	$document.on( 'touchstart', function() {
		ocsOffCanvasSidebars._touchmove = false;
	} );

	ocsOffCanvasSidebars.init = function() {

		if ( ! $( '#' + ocsOffCanvasSidebars.css_prefix + '-site' ).length || ( 'undefined' === typeof slidebars ) ) {
			ocsOffCanvasSidebars.debug( 'Container or Slidebars not found, init stopped' );
			return false;
		}

		/**
		 * Validate the disable_over setting ( using _getSetting() ).
		 * Internal function, do not overwrite.
		 * @since  0.3.0
		 * @param  {string}  sidebarId  The sidebar ID.
		 * @return {boolean} disableOver status.
		 */
		ocsOffCanvasSidebars._checkDisableOver = function( sidebarId ) {
			var check       = true,
				disableOver = parseInt( ocsOffCanvasSidebars._getSetting( 'disable_over', sidebarId ), 10 );
			if ( disableOver && ! isNaN( disableOver ) ) {
				if ( $window.width() > disableOver ) {
		  			check = false;
		  		}
			}
			return check;
		};

		/**
		 * Get the global setting or the sidebar setting (if set to overwrite).
		 *
		 * Internal function, do not overwrite.
		 *
		 * @since  0.3.0
		 * @since  0.5.6  Fixed issues with global param overwrites.
		 * @param  {string}               key        The setting key to look for.
		 * @param  {string|boolean|null}  sidebarId  The sidebar ID.
		 *                                           Pass `false` to check for an active slidebar.
		 *                                           Pass `null` or no value for only the global setting.
		 * @return {string|boolean} The setting or false.
		 */
		ocsOffCanvasSidebars._getSetting = function( key, sidebarId ) {
			var prefix  = ocsOffCanvasSidebars.css_prefix,
				setting = false;

			if ( 'undefined' !== typeof sidebarId && null !== sidebarId && ! sidebarId ) {
				sidebarId = ocsOffCanvasSidebars.slidebarsController.getActiveSlidebar();
			}

			if ( sidebarId ) {
				setting = null;

				if ( ! $.isEmptyObject( ocsOffCanvasSidebars.sidebars ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
					sidebarId = sidebarId.replace( prefix + '-', '' );
					if ( ocsOffCanvasSidebars.sidebars.hasOwnProperty( sidebarId ) ) {
						var sidebar = ocsOffCanvasSidebars.sidebars[ sidebarId ];
						if ( sidebar.hasOwnProperty( key ) ) {
							setting = sidebar[ key ];
						}
					}

				// Fallback to settings from available attributes.
				} else {
					setting = $( '#' + sidebarId ).attr( 'data-ocs-' + key );
				}

				// Fallback to global settings.
				if ( null === setting || 'undefined' === typeof setting ) {
					setting = ocsOffCanvasSidebars._getSetting( key, null );
				}

				return setting;
			}

			if ( ocsOffCanvasSidebars.hasOwnProperty( key ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
				setting = ocsOffCanvasSidebars[ key ];
			}
			// Fallback/Overwrite to enable global settings from available attributes.
			else {
				setting = $( '#' + prefix + '-site' ).attr( 'data-ocs-' + key );
				if ( 'undefined' === typeof setting ) {
					setting = false;
				}
			}

			return setting;
		};

		/**
		 * Get the value from the transform axis of an element.
		 * @param  {string|object}  obj   The element.
		 * @param  {string}         axis  The axis to get.
		 * @return {number|float|null} The axis value or null.
		 */
		ocsOffCanvasSidebars._getTranslateAxis = function( obj, axis ) {
			obj = $( obj );

			var transformMatrix = obj.css( '-webkit-transform' )
				|| obj.css( '-moz-transform' )
				|| obj.css( '-ms-transform' )
				|| obj.css( '-o-transform' )
				|| obj.css( 'transform' );
			if ( transformMatrix ) {
				var matrix = transformMatrix.replace( /[^0-9\-.,]/g, '' ).split( ',' ),
					val    = 0;
				switch ( axis ) {
					case 'x':
						val = matrix[12] || matrix[4]; //translate x.
						break;
					case 'y':
						val = matrix[13] || matrix[5]; //translate y.
						break;
					case 'z':
						val = matrix[14] || matrix[6]; //translate z.
						break;
				}
				return parseFloat( val );
			} else {
				return 0;
			}
		};

		ocsOffCanvasSidebars.container = $( '[data-canvas=container]' );

		$window.trigger( 'ocs_before', [ this ] );

		if ( ! ocsOffCanvasSidebars.slidebarsController ) {
			// Slidebars constructor.
			ocsOffCanvasSidebars.slidebarsController = new slidebars();
		}

		if ( ! ocsOffCanvasSidebars.slidebarsController ) {
			ocsOffCanvasSidebars.debug( 'Cannot initialize Slidebars' );
			return false;
		}

		// Legacy CSS mode?
		if ( ocsOffCanvasSidebars.legacy_css ) {
			ocsOffCanvasSidebars.slidebarsController.legacy = true;
			$html.addClass( 'ocs-legacy' );
		}

		$window.trigger( 'ocs_loaded', [ this ] );

		// Initialize Slidebars. Will exit if needed.
		ocsOffCanvasSidebars.slidebarsController.reinit();

		$html.addClass( 'ocs-initialized' );

		$window.trigger( 'ocs_initialized', [ this ] );

		/**
		 * Compatibility with WP Admin Bar.
		 * @since  0.4
		 * @since  0.5.7  Changed to event triggers isntead of page load.
 		 */
		if ( ocsOffCanvasSidebars._toolbar ) {

			$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'opening', function( e, sidebar_id ) {
				if ( 'fixed' !== ocsOffCanvasSidebars._toolbar.css( 'position' ) ) {
					return;
				}
				var slidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id );

				// Apply top offset on load. Not for bottom sidebars.
				if ( 'bottom' !== slidebar.side ) {
					var offset        = $body.offset().top,
						currentOffset = parseInt( slidebar.element.data( 'admin-bar-offset-top' ), 10 ),
						prop          = 'padding-top';

					if ( ! currentOffset ) {
						currentOffset = 0;
					}

					if ( offset ) {
						if ( 'top' === slidebar.side ) {
							prop = 'margin-top';
						}
						slidebar.element.css( prop, '+=' + ( offset - currentOffset ) ).data( 'admin-bar-offset-top', offset );
					}
				}
			} );

			$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'closed', function( e, sidebar_id ) {
				var slidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id );

				// Apply top offset on load. Not for bottom sidebars.
				if ( 'bottom' !== slidebar.side ) {
					var prop   = 'padding-top',
						offset = slidebar.element.data( 'admin-bar-offset-top' );
					if ( offset ) {
						if ( 'top' === slidebar.side ) {
							prop = 'margin-top';
						}
						slidebar.element.css( prop, '-=' + offset ).data( 'admin-bar-offset-top', 0 );
					}
				}

			} );
		}

		/**
		 * Fix position issues for fixed elements on slidebar animations.
		 * @todo Move this to the Slidebars script.
		 * @since  0.4.0
 		 */
		$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'opening opened closing closed', function( e, sidebar_id ) {
			var slidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id ),
				duration = parseFloat( slidebar.element.css( 'transitionDuration' ) ) * 1000;
			if ( 'top' === slidebar.side || 'bottom' === slidebar.side ) {
				var elements = ocsOffCanvasSidebars.getFixedElements();

				// Legacy mode (only needed for location: top).
				// @todo, temp apply for reveal aswell
				if ( ocsOffCanvasSidebars.legacy_css ) {
					if ( 'top' === slidebar.side && ( 'overlay' !== slidebar.style && 'reveal' !== slidebar.style ) ) {
						var offset = slidebar.element.height();
						// @todo, temp apply for reveal, should be 0
						/*if ( 'reveal' === slidebar.style ) {
							offset = 0; //parseInt( slidebar.element.css( 'height' ).replace('px', '') );
						} else {
							offset = parseInt( slidebar.element.css( 'margin-top' ).replace('px', '').replace('-', ''), 10 );
						}*/

						//Compatibility with WP Admin Bar.
						if ( ocsOffCanvasSidebars._toolbar && 'fixed' === ocsOffCanvasSidebars._toolbar.css( 'position' ) ) {
							offset += $html.offset().top;
						}

						if ( offset ) {
							elements.each( function() {
								var $this = $( this );
								// Set animation.
								if ( 'opening' === e.type ) {
									ocsOffCanvasSidebars.cssCompat( $this, 'transition', 'top ' + duration + 'ms' );
									$this.css( 'top', '+=' + offset ).data( 'ocs-offset-top', offset );
								}
								// Remove animation.
								else if ( 'closing' === e.type ) {
									$this.css( 'top', '-=' + $this.data( 'ocs-offset-top' ) );
									setTimeout( function() {
										ocsOffCanvasSidebars.cssCompat( $this, 'transition', '' );
									}, duration );
								}
							} );
						}
					}

				}
				// Normal mode (only sets a transition for use in fixed-scrolltop.js).
				else {
					elements.each( function() {
						var $this = $( this );
						//var curVal = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' );
						//console.log( curVal );
						if ( 'opening' === e.type || 'closing' === e.type ) {
							ocsOffCanvasSidebars.cssCompat( $this, 'transition', 'transform ' + duration + 'ms' );
							//$( this ).css('transform', 'translate( 0px, ' + curVal + slidebar.element.height() + 'px )' );
						} else if ( 'opened' === e.type || 'closed' === e.type ) {
							ocsOffCanvasSidebars.cssCompat( $this, 'transition', '' );
						}
					} );
				}
				$window.trigger( 'slidebar_event', [ e.type, slidebar ] );
			}
		} );

		// Validate type, this could be changed with the hooks.
		if ( 'function' === typeof ocsOffCanvasSidebars.setupTriggers ) {
			ocsOffCanvasSidebars.setupTriggers();
		}

		$window.trigger( 'ocs_after', [ this ] );

		return true;
	};

	/**
	 * Set the default settings for sidebars if they are not found.
	 * @since  0.3.0
	 * @since  0.5.6  Fixed issues with global param overwrites.
	 * @param  {string}  sidebarId  The sidebar ID.
	 * @return {boolean} Success
	 */
	ocsOffCanvasSidebars.setSidebarDefaultSettings = function( sidebarId ) {
		var defaults = {
			'overwrite_global_settings': false,
			'site_close': ocsOffCanvasSidebars._getSetting( 'site_close' ),
			'disable_over': ocsOffCanvasSidebars._getSetting( 'disable_over' ),
			'hide_control_classes': ocsOffCanvasSidebars._getSetting( 'hide_control_classes' ),
			'scroll_lock': ocsOffCanvasSidebars._getSetting( 'scroll_lock' )
		};

		if ( ! ocsOffCanvasSidebars.sidebars.hasOwnProperty( sidebarId ) ) {
			ocsOffCanvasSidebars.sidebars[ sidebarId ] = defaults;
		} else if ( ! ocsOffCanvasSidebars._getSetting( 'overwrite_global_settings', sidebarId ) ) {
			// Overwrite with default values.
			$.extend( ocsOffCanvasSidebars.sidebars[ sidebarId ], defaults );
		}
	};

	/**
	 * Setup automatic trigger handling.
	 * @since  0.3.0
	 * @return {boolean} Success
	 */
	ocsOffCanvasSidebars.setupTriggers = function() {
		var controller       = ocsOffCanvasSidebars.slidebarsController,
			prefix           = ocsOffCanvasSidebars.css_prefix,
			$sidebarElements = $( '.' + prefix + '-slidebar' );

		if ( ! $sidebarElements.length ) {
			ocsOffCanvasSidebars.debug( 'No sidebars found' );
			return false;
		}

		$sidebarElements.each( function() {
			var $this  = $( this ),
				id     = $this.data( 'ocs-sidebar-id' ),
				css_id = prefix + '-' + id;

			ocsOffCanvasSidebars.setSidebarDefaultSettings( id );

			/**
			 * Toggle the sidebar.
			 * @since  0.1.0
			 */
			$document.on( 'touchend click', '.' + prefix + '-toggle-' + id, function( e ) {
				// Stop default action and bubbling.
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe.
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				// Toggle the slidebar with respect for the disable_over setting.
				if ( ocsOffCanvasSidebars._checkDisableOver( css_id ) ) {
					controller.toggle( css_id );
				}
			} );

			/**
			 * Open the sidebar.
			 * @since  0.3.0
			 */
			$document.on( 'touchend click', '.' + prefix + '-open-' + id, function( e ) {
				// Stop default action and bubbling.
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe.
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				// Open the slidebar with respect for the disable_over setting.
				if ( ocsOffCanvasSidebars._checkDisableOver( css_id ) ) {
					controller.open( css_id );
				}
			} );

			/**
			 * Close the sidebar.
			 * @since  0.3.0
			 */
			$document.on( 'touchend click', '.' + prefix + '-close-' + id, function( e ) {
				// Stop default action and bubbling.
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe.
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				controller.close( css_id );
			} );

		} );

		// Close all sidebars.
		$document.on( 'touchend click', '.' + prefix + '-close--all', function( e ) {
			e.preventDefault();
			e.stopPropagation();
			controller.close();
		} );

		/**
		 * Optionally close the slidebar when clicking a link.
		 * @since  0.2.0
		 * @since  0.5.0  Check setting.
		 */
		$( 'a' ).not( '.' + prefix + '-trigger' ).on( 'touchend click', function () {
			// Prevent touch+swipe.
			if ( true === ocsOffCanvasSidebars._touchmove ) {
				return;
			}
			if ( ocsOffCanvasSidebars._getSetting( 'link_close', false ) ) {
				if ( ! $( this ).parents( '.' + prefix + '-trigger' ).length ) {
					controller.close();
				}
			}
		} );

		// Close Slidebars when clicking on a link within a slidebar.
		/*$( '[data-off-canvas] a' ).on( 'touchend click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			var url = $( this ).attr( 'href' ),
			target = $( this ).attr( 'target' ) ? $( this ).attr( 'target' ) : '_self';

			controller.close( function () {
				window.open( url, target );
			} );
		} );*/

		// Add close class to canvas container when Slidebar is opened.
		$( controller.events ).on( 'opening', function ( e, sidebar_id ) {
			var sidebar    = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id ),
				scrollLock = ocsOffCanvasSidebars._getSetting( 'scroll_lock', sidebar_id );
			if ( ocsOffCanvasSidebars._getSetting( 'site_close', sidebar_id ) ) {
				ocsOffCanvasSidebars.container.addClass( prefix + '-close--all' );
			}
			$html.addClass( 'ocs-sidebar-active ocs-sidebar-active-' + sidebar_id + 'ocs-sidebar-location-' + sidebar.side );

			// @todo Find a way to support scrolling for left and right sidebars in legacy mode.
			if ( ocsOffCanvasSidebars.legacy_css && ocsOffCanvasSidebars.is_touch() ) {
				if ( 'overlay' !== sidebar.style && ( 'left' === sidebar.side || 'right' === sidebar.side ) ) {
					scrollLock = true;
				}
			}

			if ( scrollLock ) {
				$html.addClass( 'ocs-scroll-lock' );
				if ( $html[0].scrollHeight > $html[0].clientHeight ) {
					var scrollTop = $html.scrollTop();
					// Subtract current scroll top.
					$body.css( { 'top': '-=' + scrollTop } );
					$html.data( 'ocs-scroll-fixed', scrollTop );
					$html.addClass( 'ocs-scroll-fixed' );
				}
			}
		} );

		// Add close class to canvas container when Slidebar is opened.
		$( controller.events ).on( 'closed', function ( e, sidebar_id ) {
			var sidebar   = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id ),
				scrollTop = $html.hasClass( 'ocs-scroll-fixed' );
			ocsOffCanvasSidebars.container.removeClass( prefix + '-close--all' );
			$html.removeClass( 'ocs-sidebar-active ocs-scroll-lock ocs-scroll-fixed ocs-sidebar-active-' + sidebar_id + 'ocs-sidebar-location-' + sidebar.side );
			if ( scrollTop ) {
				scrollTop = parseInt( $html.data( 'ocs-scroll-fixed' ), 10 );
				// Append stored scroll top.
				$body.css( { 'top': '+=' + scrollTop } );
				if ( ! $body.css( 'top' ) ) {
					$body.css( 'top', '' );
				}
				$html.removeAttr( 'ocs-scroll-fixed' );
				// Trigger slidebars css reset since position fixed changes the element heights.
				ocsOffCanvasSidebars.slidebarsController.css();
				// Apply original scroll position.
				$html.scrollTop( scrollTop );
			}
		} );

		// Disable slidebars when the window is wider than the set width.
		var disableOver = function() {
			var prefix = ocsOffCanvasSidebars.css_prefix;
			$sidebarElements.each( function() {
				var id                   = $( this ).data( 'ocs-sidebar-id' ),
					sidebar_id           = prefix + '-' + id,
					control_classes      = '.' + prefix + '-toggle-' + id + ', .' + prefix + '-open-' + id, // @todo Close classes?
					hide_control_classes = ocsOffCanvasSidebars._getSetting( 'hide_control_classes', sidebar_id );

				if ( ! ocsOffCanvasSidebars._checkDisableOver( sidebar_id ) ) {
					if ( controller.isActiveSlidebar( sidebar_id ) ) {
						controller.close();
					}
					// Hide control classes.
					if ( hide_control_classes ) {
						$( control_classes ).hide();
					}
				} else if ( hide_control_classes ) {
					$( control_classes ).show();
				}

			} );
		};
		disableOver();
		$window.on( 'resize', disableOver );

	};

	/**
	 * Get all fixed elements within the canvas container.
	 * @since  0.4.0
	 * @return {object} A jQuery selection of fixed elements.
	 */
	ocsOffCanvasSidebars.getFixedElements = function() {
		return $( '#' + ocsOffCanvasSidebars.css_prefix + '-site *' ).filter( function() {
			return ( 'fixed' === $( this ).css( 'position' ) );
		} );
	};

	/**
	 * Automatically apply browser prefixes before setting CSS values.
	 * @since  0.4.0
	 * @param  {object}         elem   The element.
	 * @param  {string}         prop   The CSS property.
	 * @param  {string|number}  value  The CSS property value.
	 * @return {null} Nothing.
	 */
	ocsOffCanvasSidebars.cssCompat = function( elem, prop, value ) {
		var data = {};

		data[ '-webkit-' + prop ] = value;
		data[ '-moz-' + prop ]    = value;
		data[ '-o-' + prop ]      = value;
		data[ prop ]              = value;

		$( elem ).css( data );
	};

	/**
	 * @return {boolean} Is it a touch device?
	 */
	ocsOffCanvasSidebars.is_touch = function() {
		return ( 0 < navigator.maxTouchPoints );
	};

	/**
	 * @param  {string} message Debug message.
	 * @return {null} Nothing.
	 */
	ocsOffCanvasSidebars.debug = function( message ) {
		if ( ocsOffCanvasSidebars._debug ) {
			console.log( 'Off-Canvas Sidebars: ' + message );
		}
	};

	if ( ocsOffCanvasSidebars.late_init ) {
		$window.load( ocsOffCanvasSidebars.init );
	} else {
		ocsOffCanvasSidebars.init();
	}

} ( jQuery ) );
