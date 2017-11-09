;/**
 * Off-Canvas Sidebars plugin
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.4.2
 * @global ocsOffCanvasSidebars
 * @preserve
 */

if ( 'undefined' === typeof ocsOffCanvasSidebars ) {
	var ocsOffCanvasSidebars = {
		site_close: true,
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

	var $document = $(document);
	var $window = $(window);
	var $html = $('html');
	var $body = $('body');

	ocsOffCanvasSidebars.slidebarsController = false;
	ocsOffCanvasSidebars.useAttributeSettings = false;
	ocsOffCanvasSidebars.container = false;
	ocsOffCanvasSidebars._touchmove = false;
	ocsOffCanvasSidebars._toolbar = $body.hasClass('admin-bar');

	ocsOffCanvasSidebars.init = function() {

		/**
		 * Validate the disable_over setting ( using _getSetting() ).
		 * Internal function, do not overwrite.
		 * @since  0.3
		 * @param  {string}   sidebarId  The sidebar ID.
		 * @return {boolean}  disableOver status.
		 */
		ocsOffCanvasSidebars._checkDisableOver = function( sidebarId ) {
			var check = true;
			var disableOver = parseInt( ocsOffCanvasSidebars._getSetting( 'disable_over', sidebarId ), 10 );
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
		 * @since  0.3
		 * @param  {string}               key        The setting key to look for.
		 * @param  {string|boolean|null}  sidebarId  The sidebar ID.
		 *                                           Pass `false` to check for an active slidebar.
		 *                                           Pass `null` or no value for only the global setting.
		 * @return {string|boolean}  The setting or false.
		 */
		ocsOffCanvasSidebars._getSetting = function( key, sidebarId ) {
			var overwrite, setting;
			var prefix = ocsOffCanvasSidebars.css_prefix;

			if ( 'undefined' !== typeof sidebarId ) {
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

				// Fallback/Overwrite to enable sidebar settings from available attributes.
				} else {
					var sidebarElement = $( '#' + sidebarId );
					overwrite = sidebarElement.attr( 'ocs-overwrite_global_settings' );
					if ( overwrite ) {
						setting = sidebarElement.attr( 'ocs-' + key );
						if ( 'undefined' !== typeof setting ) {
							return setting;
						} else {
							return false;
						}
					}
				}
			}

			if ( ocsOffCanvasSidebars.hasOwnProperty( key ) && ! ocsOffCanvasSidebars.useAttributeSettings ) {
				return ocsOffCanvasSidebars[ key ];
			} else {
				setting = $( '#' + prefix + '-site' ).attr( 'ocs-' + key );
				if ( 'undefined' !== typeof setting ) {
					return setting;
				}
			}

			return false;
		};

		/**
		 * Get the value from the transform axis of an element.
		 * @param  {string|object}  obj   The element.
		 * @param  {string}         axis  The axis to get.
		 * @returns {number|float|null}  The axis value or null.
		 */
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

		ocsOffCanvasSidebars.container = $( '[canvas=container]' );

		$window.trigger( 'ocs_before', this );

		// Slidebars constructor.
		ocsOffCanvasSidebars.slidebarsController = new slidebars();

		if ( false === ocsOffCanvasSidebars.slidebarsController ) {
			return;
		}

		// Legacy CSS mode?
		if ( ocsOffCanvasSidebars.legacy_css ) {
			ocsOffCanvasSidebars.slidebarsController.legacy = true;
			$html.addClass('ocs-legacy');
		}

		$window.trigger( 'ocs_loaded', this );

		// Initialize slidebars.
		ocsOffCanvasSidebars.slidebarsController.init();

		$html.addClass('ocs-initialized');

		$window.trigger( 'ocs_initialized', this );

		/**
		 * Compatibility with WP Admin Bar.
		 * @since  0.4
 		 */
		if ( ocsOffCanvasSidebars._toolbar ) {
			$window.on( 'load', function() {
				// Offset top = admin bar height.
				var bodyOffset = $body.offset(),
					$sidebars = $( '.' + ocsOffCanvasSidebars.css_prefix + '-slidebar' );

				$sidebars.each( function() {
					var $this = $(this);
					// Apply top offset on load. Not for bottom sidebars.
					if ( ! $this.hasClass( 'ocs-location-bottom' ) ) {
						$this.css( 'margin-top', '+=' + bodyOffset.top );
					}
				} );

				// css event is triggers after resize.
				$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'css', function() {
					$sidebars.each( function() {
						var $this = $(this);
						// Apply top offset on css reset. Only for top sidebars.
						if ( $this.hasClass( 'ocs-location-top' ) ) {
							$this.css( 'margin-top', '+=' + bodyOffset.top );
						}
					} );
				} );
			} );
		}

		/**
		 * Fix position issues for fixed elements on slidebar animations.
		 * @since  0.4
 		 */
		$( ocsOffCanvasSidebars.slidebarsController.events ).on( 'opening opened closing closed', function( e, sidebar_id ) {
			var slidebar = ocsOffCanvasSidebars.slidebarsController.getSlidebar( sidebar_id );
			var duration = parseFloat( slidebar.element.css( 'transitionDuration' )/*, 10*/ ) * 1000;
			if ( 'top' === slidebar.side || 'bottom' === slidebar.side ) {
				var elements = ocsOffCanvasSidebars.getFixedElements();
				elements.attr( { 'canvas-fixed': 'fixed' } );

				// Legacy mode (only needed for location: top).
				// @todo, temp apply for reveal aswell
				if ( ocsOffCanvasSidebars.legacy_css && 'top' === slidebar.side && ( 'overlay' !== slidebar.style && 'reveal' !== slidebar.style  ) ) {
					var offset;
					// @todo, temp apply for reveal, should be 0
					//if ( 'reveal' === slidebar.style ) {
						//offset = 0; //parseInt( slidebar.element.css( 'height' ).replace('px', '') );
					//} else {
						offset = parseInt( slidebar.element.css( 'margin-top' ).replace('px', '').replace('-', ''), 10 );
					//}

					//Compatibility with WP Admin Bar.
					// @todo, condition for setting
					if ( ocsOffCanvasSidebars._toolbar ) {
						var bodyOffset = $body.offset();
						offset += bodyOffset.top;
					}

					elements.each( function() {
						var $this = $(this);
						// Set animation.
						if ( 'opening' === e.type ) {
							ocsOffCanvasSidebars.cssCompat( $this, 'transition', 'top ' + duration + 'ms' );
							$this.css( 'top', parseInt( $this.css('top').replace('px', ''), 10 ) + offset + 'px' );
						}
						// Remove animation.
						else if ( 'closing' === e.type ) {
							$this.css( 'top', parseInt( $this.css('top').replace('px', ''), 10 ) - offset + 'px' );
							setTimeout( function() {
								ocsOffCanvasSidebars.cssCompat( $this, 'transition', '' );
							}, duration );
						}
					} );
				}
				// Normal mode (only sets a transition for use in fixed-scrolltop.js).
				else {
					elements.each( function() {
						var $this = $(this);
						//var curVal = ocsOffCanvasSidebars._getTranslateAxis( this, 'y' );
						//console.log( curVal );
						if ( 'opening' === e.type || 'closing' === e.type ) {
							ocsOffCanvasSidebars.cssCompat( $this, 'transition', 'transform ' + duration + 'ms' );
							//$(this).css('transform', 'translate( 0px, ' + curVal + slidebar.element.height() + 'px )' );
						} else if ( 'opened' === e.type || 'closed' === e.type ) {
							ocsOffCanvasSidebars.cssCompat( $this, 'transition', '' );
						}
					} );
				}
				$window.trigger( 'slidebar_event', [ e.type, slidebar ] );
			}
		} );

		// Prevent swipe events to be seen as a click (bug in some browsers).
		$document.on( 'touchmove', function() {
			ocsOffCanvasSidebars._touchmove = true;
		} );
		$document.on( 'touchstart', function() {
			ocsOffCanvasSidebars._touchmove = false;
		} );

		// Validate type, this could be changed with the hooks.
		if ( 'function' === typeof ocsOffCanvasSidebars.setupTriggers ) {
			ocsOffCanvasSidebars.setupTriggers();
		}

		$window.trigger( 'ocs_after', this );
	};

	/**
	 * Set the default settings for sidebars if they are not found.
	 * @since  0.3
	 * @param  {string}  sidebarId  The sidebar ID.
	 * @return {null}  Nothing.
	 */
	ocsOffCanvasSidebars.setSidebarDefaultSettings = function( sidebarId ) {

		if ( 'undefined' === typeof ocsOffCanvasSidebars.sidebars[ sidebarId ] ) {
			ocsOffCanvasSidebars.sidebars[ sidebarId ] = {
				'overwrite_global_settings': false,
				"site_close": ocsOffCanvasSidebars.site_close,
				"disable_over": ocsOffCanvasSidebars.disable_over,
				"hide_control_classes": ocsOffCanvasSidebars.hide_control_classes,
				"scroll_lock": ocsOffCanvasSidebars.scroll_lock
			}
		}
	};

	/**
	 * Setup automatic trigger handling.
	 * @since  0.3
	 * @return {null}  Nothing.
	 */
	ocsOffCanvasSidebars.setupTriggers = function() {
		var controller = ocsOffCanvasSidebars.slidebarsController,
			prefix = ocsOffCanvasSidebars.css_prefix,
			sidebarElements = $( '.' + prefix + '-slidebar' );

		sidebarElements.each( function() {
			var id = $( this ).attr( 'ocs-sidebar-id' );

			ocsOffCanvasSidebars.setSidebarDefaultSettings( id );

			/**
			 * Toggle the sidebar.
			 * @since  0.1
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
				if ( ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					controller.toggle( prefix + '-' + id );
				}
			} );

			/**
			 * Open the sidebar.
			 * @since  0.3
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
				if ( ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					controller.open( prefix + '-' + id );
				}
			} );

			/**
			 * Close the sidebar.
			 * @since  0.3
			 */
			$document.on( 'touchend click', '.' + prefix + '-close-' + id, function( e ) {
				// Stop default action and bubbling.
				e.stopPropagation();
				e.preventDefault();

				// Prevent touch+swipe.
				if ( true === ocsOffCanvasSidebars._touchmove ) {
					return;
				}

				controller.close( prefix + '-' + id );
			} );

		} );

		// Close all sidebars.
		$document.on( 'touchend click', '.' + prefix + '-close--all', function( e ) {
			if ( ocsOffCanvasSidebars._getSetting( 'site_close', false ) ) {
				e.preventDefault();
				e.stopPropagation();
				controller.close();
			}
		} );

		// Close the slidebar after clicking a link.
		$( 'a' ).not( '.' + prefix + '-trigger' ).on( 'touchend click', function() {
			if ( ! $(this).parents( '.' + prefix + '-trigger' ).length ) {
				controller.close();
			}
		} );

		// Close Slidebars when clicking on a link within a slidebar.
		/*$( '[off-canvas] a' ).on( 'touchend click', function( e ) {
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
			$( '[canvas]' ).addClass( prefix + '-close--all' );
			$html.addClass( 'ocs-sidebar-active ocs-sidebar-active-' + sidebar_id  );
			if ( ocsOffCanvasSidebars._getSetting( 'scroll_lock', false ) ) {
				$html.addClass( 'ocs-scroll-lock' );
				if ( $html[0].scrollHeight > $html[0].clientHeight ) {
					var scrollTop = $html.scrollTop();
					// Subtract current scroll top.
					$body.css( { 'top': '-=' + scrollTop } );
					$html.attr( 'ocs-scroll-fixed', scrollTop );
					$html.addClass( 'ocs-scroll-fixed' );
				}
			}
		} );

		// Add close class to canvas container when Slidebar is opened.
		$( controller.events ).on( 'closing', function ( e, sidebar_id ) {
			$( '[canvas]' ).removeClass( prefix + '-close--all' );
			var scrollTop = false;
			if ( $html.hasClass( 'ocs-scroll-fixed' ) ) {
				scrollTop = true;
			}
			$html.removeClass( 'ocs-sidebar-active ocs-scroll-lock ocs-scroll-fixed ocs-sidebar-active-' + sidebar_id );
			if ( scrollTop ) {
				scrollTop = parseInt( $html.attr( 'ocs-scroll-fixed' ), 10 );
				// Append stored scroll top.
				$body.css( { 'top': '+=' + scrollTop } );
				$html.removeAttr( 'ocs-scroll-fixed' );
				$html.scrollTop( scrollTop );
				// Trigger slidebars css reset since position fixed changes the element heights.
				$window.trigger( 'resize' );
			}
		} );

		// Disable slidebars when the window is wider than the set width.
		var disableOver = function() {
			var prefix = ocsOffCanvasSidebars.css_prefix;
			sidebarElements.each( function() {
				var id = $( this ).attr( 'ocs-sidebar-id' );

				if ( ! ocsOffCanvasSidebars._checkDisableOver( prefix + '-' + id ) ) {
					if ( controller.isActiveSlidebar( prefix + '-' + id ) ) {
						controller.close();
					}
					// Hide control classes.
					if ( ocsOffCanvasSidebars._getSetting( 'hide_control_classes', prefix + '-' + id ) ) {
						$( '.' + prefix + '-toggle-' + id ).hide();
					}
				} else {
					$( '.' + prefix + '-toggle-' + id ).show();
				}

			} );
		};
		disableOver();
		$window.on( 'resize', disableOver );

		/**
		 * @fixme Fix for reveal and shift styles when page does not have enough height.
		 * @since 0.4
 		 */
		$window.on( 'load resize', function() {
			ocsOffCanvasSidebars.container.css( 'min-height', $window.innerHeight() - parseInt( $html.css('margin-top'), 10 ) );
		} );

	};

	/**
	 * Get all fixed elements within the canvas container.
	 * @since  0.4
	 * @return {object}  A jQuery selection of fixed elements.
	 */
	ocsOffCanvasSidebars.getFixedElements = function() {
		return $('#' + ocsOffCanvasSidebars.css_prefix + '-site *').filter( function() {
			return ( 'fixed' === $(this).css('position') );
		} );
	};

	/**
	 * Automatically apply browser prefixes before setting CSS values.
	 * @since  0.4
	 * @param  {object}         elem   The element.
	 * @param  {string}         prop   The CSS property.
	 * @param  {string|number}  value  The CSS property value.
	 * @return {null}  Nothing.
	 */
	ocsOffCanvasSidebars.cssCompat = function( elem, prop, value ) {
		var data = {};
		data[ '-webkit-' + prop ] = value;
		data[ '-moz-' + prop ] = value;
		data[ '-o-' + prop ] = value;
		data[ prop ] = value;
		$( elem ).css( data );
	};

	if ( $( '#' + ocsOffCanvasSidebars.css_prefix + '-site' ).length && ( 'undefined' !== typeof slidebars ) ) {
		ocsOffCanvasSidebars.init();
	}

} ( jQuery ) );
