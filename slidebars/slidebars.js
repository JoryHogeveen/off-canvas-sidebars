;/**
 * Slidebars - A jQuery Framework for Off-Canvas Menus and Sidebars
 * Version: 2.0.2
 * Url: http://www.adchsm.com/slidebars/
 * Author: Adam Charles Smith
 * Author url: http://www.adchsm.com/
 * License: MIT
 * License url: http://www.adchsm.com/slidebars/license/
 *
 * Modified by: Jory Hogeveen
 * Version numbers and info below is related to Off-Canvas Sidebars, not Slidebars.
 *
 * @package Off_Canvas_Sidebars
 * @author  Jory Hogeveen <info@keraweb.nl>
 *
 * @since   0.4.0  Add scope for this reference + Add legacy CSS support (no hardware acceleration)
 * @since   0.4.2  Parse slidebar widths/heights to rounded pixels (like jQuery does) to prevent 1px differences
 * @since   0.6.0  Add resize styles
 * @version 0.6.0
 * @global  slidebars
 * @preserve
 */

var slidebars;

(function($) {

slidebars = function () {

	/**
	 * Setup
	 */

	// Cache all canvas elements
	var canvas = $( '[canvas]' ),

	// Object of Slidebars
	offCanvas = {},

	// Create reference to this object for use in functions
	self = this,

	// Variables, permitted sides and styles
	init = false,
	registered = false,
	sides = [ 'top', 'right', 'bottom', 'left' ],
	styles = [ 'overlay', 'push', 'reveal', 'shift', 'resize_push', 'resize_reveal', 'resize_shift' ],

	/**
	 * Get Animation Properties
	 */

	getAnimationProperties = function ( id ) {
		// Variables
		var elements = $(),
		amount = '0px, 0px',
		size = '0px',
		duration = parseFloat( offCanvas[ id ].element.css( 'transitionDuration' )/*, 10*/ ) * 1000;

		// Elements to animate
		if ( ! offCanvas[ id ].resize && 'overlay' !== offCanvas[ id ].style ) {
			elements = elements.add( canvas );
		}

		if ( 'reveal' !== offCanvas[ id ].style ) {
			elements = elements.add( offCanvas[ id ].element );
		} else if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
			// @todo, fix reveal support for top and bottom
			elements = elements.add( offCanvas[ id ].element );
		}

		// Amount to animate
		if ( offCanvas[ id ].active || self.legacy ) {
			if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
				size = offCanvas[ id ].element.css( 'height' );
			} else {
				size = offCanvas[ id ].element.css( 'width' );
			}
			if ( self.legacy ) {
				amount = size;
			} else {
				if ( 'top' === offCanvas[ id ].side ) {
					amount = '0px, ' + size;
				} else if ( 'right' === offCanvas[ id ].side ) {
					amount = '-' + size + ', 0px';
				} else if ( 'bottom' === offCanvas[ id ].side ) {
					amount = '0px, -' + size;
				} else if ( 'left' === offCanvas[ id ].side ) {
					amount = size + ', 0px';
				}
			}
		}

		// Return animation properties
		return { 'elements': elements, 'amount': amount, 'duration': duration, 'size': parseFloat( size.replace( 'px', '' ) ) };
	},

	/**
	 * Slidebars Registration
	 */

	registerSlidebar = function ( id, side, style, element ) {
		// Check if Slidebar is registered
		if ( isRegisteredSlidebar( id ) ) {
			throw "Error registering Slidebar, a Slidebar with id '" + id + "' already exists.";
		}

		// Parse resize style
		var resize = ( 0 === style.indexOf( 'resize' ) );
		if ( resize ) {
			style = style.replace( 'resize_', '' );
		}

		// Register the Slidebar
		offCanvas[ id ] = {
			'id': id,
			'side': side,
			'style': style,
			'resize' : resize,
			'element': element,
			'active': false
		};
	},

	isRegisteredSlidebar = function ( id ) {
		// Return if Slidebar is registered
		return ( offCanvas.hasOwnProperty( id ) );
	};

	/**
	 * Initialization
	 */

	this.legacy = false;

	/**
	 * Is initialized?
	 *
	 * @returns {boolean}
	 */
	this.initialized = function () {
		return init;
	};

	/**
	 * Re-initialize.
	 *
	 * @returns {boolean}
	 */
	this.reinit = function ( callback ) {
		if ( this.initialized() ) {
			this.exit();
		}
		this.init( callback );
	};

	/**
	 * Initialize Slidebars.
	 *
	 * @param callback
	 */
	this.init = function ( callback ) {
		// Check if Slidebars has been initialized
		if ( init ) {
			throw "Slidebars has already been initialized.";
		}

		// Loop through and register Slidebars
		if ( ! registered ) {
			$( '[off-canvas]' ).each( function () {
				// Get Slidebar parameters
				var parameters = $( this ).attr( 'off-canvas' ).split( ' ', 3 );

				// Make sure a valid id, side and style are specified
				if ( ! parameters || ! parameters[ 0 ] || -1 === sides.indexOf( parameters[ 1 ] ) || -1 === styles.indexOf( parameters[ 2 ] ) ) {
					throw "Error registering Slidebar, please specify a valid id, side and style'.";
				}

				// Register Slidebar
				registerSlidebar( parameters[ 0 ], parameters[ 1 ], parameters[ 2 ], $( this ) );
			} );

			// Set registered variable
			registered = true;
		}

		// Set initialized variable
		init = true;

		// Set CSS
		this.css();

		// Trigger event
		$( events ).trigger( 'init' );

		// Run callback
		if ( 'function' === typeof callback ) {
			callback();
		}
	};

	/**
	 * Exit Slidebars.
	 *
	 * @param callback
	 */
	this.exit = function ( callback ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Exit
		var exit = function () {
			// Set init variable
			init = false;

			// Trigger event
			$( events ).trigger( 'exit' );

			// Run callback
			if ( 'function' === typeof callback ) {
				callback();
			}
		};

		// Call exit, close open Slidebar if active
		if ( this.getActiveSlidebar() ) {
			this.close( exit );
		} else {
			exit();
		}
	};

	/**
	 * CSS
	 */

	this.css = function ( callback ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Loop through Slidebars to set negative margins
		for ( var id in offCanvas ) {
			// Check if Slidebar is registered
			if ( offCanvas.hasOwnProperty( id ) && isRegisteredSlidebar( id ) ) {
				// Calculate offset
				var offset;

				/**
				 * Make sure that percentage based widths are rounded to actual pixels to prevent 1px differences on display.
				 * 1. Reset inline style CSS.
				 * 2. Get CSS value in pixels from stylesheets (rounded due to jQuery).
				 * 3. Set inline CSS from the rounded value.
				 */
				if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
					offCanvas[ id ].element.css( 'height', '' );
					offset = offCanvas[ id ].element.css( 'height' );
					offCanvas[ id ].element.css( 'height', offset );
				} else {
					offCanvas[ id ].element.css( 'width', '' );
					offset = offCanvas[ id ].element.css( 'width' );
					offCanvas[ id ].element.css( 'width', offset );
				}

				// Apply negative margins
				var do_offset = false;
				if ( 'reveal' !== offCanvas[ id ].style ) {
					do_offset = true;
				} else if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
					// temp disabled style condition to enable reveal location as well for top and bottom
					// @todo, fix reveal support for top and bottom, current result is behaviour similar to push.
					do_offset = true;
				}

				if ( do_offset ) {
					offCanvas[ id ].element.css( 'margin-' + offCanvas[ id ].side, '-' + offset );

					// Fix shift style locations for legacy mode (1.9 to fix minor px rendering issues)
					if ( self.legacy && 'shift' === offCanvas[ id ].style ) {
						var shiftPos;
						if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
							shiftPos = offCanvas[ id ].element.height() / 1.9;
						} else if ( 'left' === offCanvas[ id ].side || 'right' === offCanvas[ id ].side ) {
							shiftPos = offCanvas[ id ].element.width() / 1.9;
						}
						// @todo, fix shift support for top and bottom in legacy mode.
						if ( 'left' === offCanvas[ id ].side || 'right' === offCanvas[ id ].side ) {
							offCanvas[ id ].element.css( offCanvas[ id ].side, shiftPos + 'px' );
						}
					}
				}
			}
		}

		// Reposition open Slidebars
		if ( this.getActiveSlidebar() ) {
			this.open( this.getActiveSlidebar() );
		}

		// Trigger event
		$( events ).trigger( 'css' );

		// Run callback
		if ( 'function' === typeof callback ) {
			callback();
		}
	};

	/**
	 * Controls
	 */

	this.open = function ( id, callback ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Check if id wasn't passed or if Slidebar isn't registered
		if ( ! id || ! isRegisteredSlidebar( id ) ) {
			throw "Error opening Slidebar, there is no Slidebar with id '" + id + "'.";
		}

		// Open
		var open = function () {
			// Set active state to true
			offCanvas[ id ].active = true;

			// Display the Slidebar
			offCanvas[ id ].element.css( 'display', 'block' );

			// Trigger event
			$( events ).trigger( 'opening', [ offCanvas[ id ].id ] );

			// Get animation properties
			var animationProperties = getAnimationProperties( id );

			// Apply css
			var css = {
				'-webkit-transition-duration': animationProperties.duration + 'ms',
				'-moz-transition-duration': animationProperties.duration + 'ms',
				'-o-transition-duration': animationProperties.duration + 'ms',
				'transition-duration': animationProperties.duration + 'ms'
			};

			var canvasSide,
				canvasCss = {};

			if ( self.legacy ) {
				css[ offCanvas[ id ].side ] = animationProperties.amount;

				if ( 'right' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {

					if ( ! offCanvas[ id ].resize ) {
						// Bottom and Right animation for slidebars and the container are not the same
						if ( 'right' === offCanvas[ id ].side ) {
							canvasSide = 'left';
						} else if ( 'bottom' === offCanvas[ id ].side ) {
							canvasSide = 'top';
						}
						canvasCss = {
							'-webkit-transition-duration': animationProperties.duration + 'ms',
							'-moz-transition-duration': animationProperties.duration + 'ms',
							'-o-transition-duration': animationProperties.duration + 'ms',
							'transition-duration': animationProperties.duration + 'ms'
						};
						canvasCss[ canvasSide ] = '-=' + animationProperties.amount;
						// Move container
						if ( 'overlay' !== offCanvas[ id ].style ) {
							canvas.css( canvasCss );
						}
					}

					// Open slidebar
					animationProperties.elements.not( canvas ).css( css );
				} else {
					// Top and Left sides can use the same css as all other elements.
					animationProperties.elements.css( css );
				}

			} else {
				css.transform = 'translate(' + animationProperties.amount + ')';
				animationProperties.elements.css( css );
			}

			if ( offCanvas[ id ].resize ) {
				var prop = 'width';
				if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
					prop = 'height';
				}

				canvasCss = {
					'-webkit-transition': canvas.css( '-webkit-transition' ),
					'transition': canvas.css( 'transition' )
				};

				// @todo, proper calculation when supporting multiple opened slidebars.
				canvasCss[ prop ] = $(window)[prop]() - animationProperties.size + 'px';
				canvasCss[ '-webkit-transition' ] += ', ' + prop + ' ' + animationProperties.duration + 'ms';
				canvasCss[ 'transition' ] += ', ' + prop + ' ' + animationProperties.duration + 'ms';

				canvas.css( canvasCss );
			}

			// Transition completed
			setTimeout( function () {
				// Trigger event
				$( events ).trigger( 'opened', [ offCanvas[ id ].id ] );

				// Run callback
				if ( 'function' === typeof callback ) {
					callback();
				}
			}, animationProperties.duration );
		};

		// Call open, close open Slidebar if active
		if ( this.getActiveSlidebar() && this.getActiveSlidebar() !== id ) {
			this.close( open );
		} else {
			open();
		}
	};

	this.close = function ( id, callback ) {
		// Shift callback arguments
		if ( 'function' === typeof id ) {
			callback = id;
			id = null;
		}

		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Check if id was passed but isn't a registered Slidebar
		if ( id && ! isRegisteredSlidebar( id ) ) {
			throw "Error closing Slidebar, there is no Slidebar with id '" + id + "'.";
		}

		// If no id was passed, get the active Slidebar
		if ( ! id ) {
			id = this.getActiveSlidebar();
		}

		// Close a Slidebar
		if ( id && offCanvas[ id ].active ) {
			// Set active state to false
			offCanvas[ id ].active = false;

			// Trigger event
			$( events ).trigger( 'closing', [ offCanvas[ id ].id ] );

			// Get animation properties
			var animationProperties = getAnimationProperties( id );

			// Apply css
			if ( self.legacy ) {
				var css = {};

				var canvasSide;
				if ( 'right' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {

					if ( ! offCanvas[ id ].resize ) {
						// Bottom and Right animation for slidebars and the container are not the same
						if ( 'right' === offCanvas[ id ].side ) {
							canvasSide = 'left';
						} else if ( 'bottom' === offCanvas[ id ].side ) {
							canvasSide = 'top';
						}
						// Reset container
						canvas.css( canvasSide, '' );
					}

					// Close slidebar
					animationProperties.elements.not( canvas ).css( offCanvas[ id ].side, '' );
				} else {
					// Top and Left sides can use the same css as all other elements.
					animationProperties.elements.css( offCanvas[ id ].side, '' );
				}

				// Fix shift style for legacy mode (1.9 to fix minor px rendering issues)
				if ( 'shift' === offCanvas[ id ].style ) {
					var shiftPos;
					if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
						shiftPos = offCanvas[ id ].element.height() / 1.9;
					} else if ( 'left' === offCanvas[ id ].side || 'right' === offCanvas[ id ].side ) {
						shiftPos = offCanvas[ id ].element.width() / 1.9;
					}
					// @todo, fix shift support for top and bottom
					if ( 'left' === offCanvas[ id ].side || 'right' === offCanvas[ id ].side ) {
						offCanvas[ id ].element.css( offCanvas[ id ].side, shiftPos + 'px' );
					}
				}

			} else {
				animationProperties.elements.css( 'transform', '' );
			}

			if ( offCanvas[ id ].resize ) {
				if ( 'top' === offCanvas[ id ].side || 'bottom' === offCanvas[ id ].side ) {
					canvas.css( 'height', '' );
				} else {
					canvas.css( 'width', '' );
				}
			}

			// Transition completetion
			setTimeout( function () {
				// Remove transition duration
				animationProperties.elements.css( {
					'-webkit-transition-duration': '',
					'-moz-transition-duration': '',
					'-o-transition-duration': '',
					'transition-duration': '',
					// Resize.
					'-webkit-transition': '',
					'transition': ''
				} );

				// Hide the Slidebar
				offCanvas[ id ].element.css( 'display', '' );

				// Trigger event
				$( events ).trigger( 'closed', [ offCanvas[ id ].id ] );

				// Run callback
				if ( 'function' === typeof callback ) {
					callback();
				}
			}, animationProperties.duration );
		}
	};

	this.toggle = function ( id, callback ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Check if id wasn't passed or if Slidebar isn't registered
		if ( ! id || ! isRegisteredSlidebar( id ) ) {
			throw "Error toggling Slidebar, there is no Slidebar with id '" + id + "'.";
		}

		// Check Slidebar state
		if ( offCanvas[ id ].active ) {
			// It's open, close it
			this.close( id, function () {
				// Run callback
				if ( 'function' === typeof callback ) {
					callback();
				}
			} );
		} else {
			// It's closed, open it
			this.open( id, function () {
				// Run callback
				if ( 'function' === typeof callback ) {
					callback();
				}
			} );
		}
	};

	/**
	 * Active States
	 */

	this.isActive = function ( id ) {
		// Return init state
		return init;
	};

	this.isActiveSlidebar = function ( id ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Check if id wasn't passed
		if ( ! id ) {
			throw "You must provide a Slidebar id.";
		}

		// Check if Slidebar is registered
		if ( ! isRegisteredSlidebar( id ) ) {
			throw "Error retrieving Slidebar, there is no Slidebar with id '" + id + "'.";
		}

		// Return the active state
		return offCanvas[ id ].active;
	};

	this.getActiveSlidebar = function () {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Variable to return
		var active = false;

		// Loop through Slidebars
		for ( var id in offCanvas ) {
			// Check if Slidebar is registered
			if ( isRegisteredSlidebar( id ) ) {
				// Check if it's active
				if ( offCanvas[ id ].active ) {
					// Set the active id
					active = offCanvas[ id ].id;
					break;
				}
			}
		}

		// Return
		return active;
	};

	this.getSlidebars = function () {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Create an array for the Slidebars
		var slidebarsArray = [];

		// Loop through Slidebars
		for ( var id in offCanvas ) {
			// Check if Slidebar is registered
			if ( isRegisteredSlidebar( id ) ) {
				// Add Slidebar id to array
				slidebarsArray.push( offCanvas[ id ].id );
			}
		}

		// Return
		return slidebarsArray;
	};

	this.getSlidebar = function ( id ) {
		// Check if Slidebars has been initialized
		if ( ! init ) {
			throw "Slidebars hasn't been initialized.";
		}

		// Check if id wasn't passed
		if ( ! id ) {
			throw "You must pass a Slidebar id.";
		}

		// Check if Slidebar is registered
		if ( ! id || ! isRegisteredSlidebar( id ) ) {
			throw "Error retrieving Slidebar, there is no Slidebar with id '" + id + "'.";
		}

		// Return the Slidebar's properties
		return offCanvas[ id ];
	};

	/**
	 * Events
	 */

	this.events = {};
	var events = this.events;

	/**
	 * Resizes
	 */

	$( window ).on( 'resize', this.css.bind( this ) );
};

}(jQuery));
