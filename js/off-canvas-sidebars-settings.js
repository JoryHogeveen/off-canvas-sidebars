;/**
 * Off-Canvas Sidebars plugin
 *
 * OCS_OFF_CANVAS_SIDEBARS_SETTINGS
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3
 */

if ( typeof OCS_OFF_CANVAS_SIDEBARS_SETTINGS == 'undefined' ) {
	var OCS_OFF_CANVAS_SIDEBARS_SETTINGS = {
		'general_key': 'off_canvas_sidebars_options',
		'plugin_key': 'off-canvas-sidebars-settings',
		'css_prefix': 'ocs',
		'__required_fields_not_set': '' //Some required fields are not set!
	};
}

(function($) {

	OCS_OFF_CANVAS_SIDEBARS_SETTINGS.init = function() {

		var tab = $('#ocs_tab');
		var postbox = $('.postbox');

		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles( OCS_OFF_CANVAS_SIDEBARS_SETTINGS.plugin_key );


		if ( tab.val() == 'ocs-sidebars' ) {
			postbox.each( function() {
				var sidebar_id = $(this).attr('id').replace('section_sidebar_', '');

				ocs_show_hide_options_radio( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_background_color_type', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_background_color_wrapper', 'color', false );
				ocs_show_hide_options_radio( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_location', '#off_canvas_sidebars_options_sidebars_' + sidebar_id + '_style_reveal, #off_canvas_sidebars_options_sidebars_' + sidebar_id + '_style_shift', [ 'left', 'right' ], 'label' );

				ocs_show_hide_options( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_overwrite_global_settings', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_site_close', 'tr' );
				ocs_show_hide_options( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_overwrite_global_settings', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_disable_over', 'tr' );
				ocs_show_hide_options( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_overwrite_global_settings', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_hide_control_classes', 'tr' );
				ocs_show_hide_options( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_overwrite_global_settings', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_scroll_lock', 'tr' );
			} );
		} else {
			ocs_show_hide_options_radio( '.off_canvas_sidebars_options_background_color_type', '.off_canvas_sidebars_options_background_color_wrapper', 'color' );
		}


		function ocs_show_hide_options( trigger, target, parent ) {
			if ( ! $( trigger ).is(':checked') ) {
				if ( parent ) {
					$( target ).closest( parent ).slideUp('fast');
				} else {
					$( target ).slideUp('fast');
				}
			}
			$( trigger ).change( function() {
				if ( $(this).is(':checked') ) {
					if ( parent ) {
						$( target ).closest( parent ).slideDown('fast');
					} else {
						$( target ).slideDown('fast');
					}
				} else {
					if ( parent ) {
						$( target ).closest( parent ).slideUp('fast');
					} else {
						$( target ).slideUp('fast');
					}
				}
			});
		}

		function ocs_show_hide_options_radio( trigger, target, compare, parent ) {
			if ( ! $.isArray( compare ) ) {
				compare = [ compare ];
			}
			if ( parent ) {
				parent += ', ' + parent + ' + br';
			}
			if ( $.inArray( $( trigger + ':checked' ).val(), compare ) < 0 ) {
				if ( parent ) {
					$( target ).closest( parent ).slideUp('fast');
				} else {
					$( target ).slideUp('fast');
				}
			}
			$( trigger ).change( function() {
				if ( $.inArray( $( trigger + ':checked' ).val(), compare ) >= 0 ) {
					if ( parent ) {
						$( target ).closest( parent ).slideDown('fast');
					} else {
						$( target ).slideDown('fast');
					}
				} else {
					if ( parent ) {
						$( target ).closest( parent ).slideUp('fast');
					} else {
						$( target ).slideUp('fast');
					}
				}
			});
		}

		// Enable the WP Color Picker
		$('input.color-picker').wpColorPicker();

		// Validate required fields
		$('input.required').each(function(){
			$(this).on('change', function() {
				if ( $(this).val() == '' ) {
					$(this).parents('tr').addClass('form-invalid');
				} else {
					$(this).parents('tr').removeClass('form-invalid');
				}
			});
		});

		// Validate form submit
		$('#' + OCS_OFF_CANVAS_SIDEBARS_SETTINGS.general_key).submit( function(e) {
			var valid = true;
			//var errors = {};
			$('input.required', this).each(function(){
				if ( $(this).val() == '' ) {
					$(this).trigger('change');
					valid = false;
				}
			});
			if ( ! valid ) {
				e.preventDefault();
				alert( OCS_OFF_CANVAS_SIDEBARS_SETTINGS.__required_fields_not_set );
			}
		} );

		if ( tab.val() == 'ocs-sidebars' ) {

			// Dynamic sidebar ID
			if ( $('.js-dynamic-id').length ) {
				postbox.each(function() {
					var sidebar = this;
					$('.js-dynamic-id', sidebar).text( $('input.off_canvas_sidebars_options_sidebars_id', sidebar).val() );
					$('.sidebar_classes').show();
					$('input.off_canvas_sidebars_options_sidebars_id', this).on('keyup', function() {
						$('.js-dynamic-id', sidebar).text( $(this).val() );
					});
				});
			}

			// Half opacity for closed disabled sidebars
			postbox.each(function(){
				var sidebar = this;
				$(sidebar).css({'border-left':'5px solid #eee'});
				if ( ! $('input.off_canvas_sidebars_options_sidebars_enable', sidebar).is(':checked') ) {
					if ( $(sidebar).hasClass('closed') ) {
						$(sidebar).css('opacity', '0.75');
					}
					$(sidebar).css('border-left-color','#ffb900');
				} else {
					$(sidebar).css('border-left-color','#46b450');
				}
				$('input.off_canvas_sidebars_options_sidebars_enable', sidebar).on('change', function() {
					if ( ! $(this).is(':checked') ) {
						$(sidebar).css('border-left-color','#ffb900');
						if ( $(sidebar).hasClass('closed') ) {
							$(sidebar).css('opacity', '0.75');
						} else {
							$(sidebar).css('opacity', '');
						}
					} else {
						$(sidebar).css('border-left-color','#46b450');
						$(sidebar).css('opacity', '');
					}
				});
				$(sidebar).on('click', function() {
					if ( ! $('input.off_canvas_sidebars_options_sidebars_enable', sidebar).is(':checked') && $(sidebar).hasClass('closed') ) {
						$(sidebar).css('opacity', '0.75');
					} else {
						$(sidebar).css('opacity', '');
					}
				});
			});

			// Hide options when set to delete
			$(document).on( 'change', '.off_canvas_sidebars_options_sidebars_delete', function() {
				var sidebar = $(this).parents('.postbox');
				if ( $(this).is(':checked') ) {
					var parent_row = $(this).parents('tr');
					$( 'tr', sidebar ).hide( 'fast', function() {
						$( 'tr', sidebar ).each(function(){
							if ( $(this).is( parent_row ) ) {
								$(this).show( 'fast' );
							}
						});
					} );
					$(sidebar).css('opacity', '0.5');
					$(sidebar).css('border-left-color','#dc3232');
				} else {
					$(sidebar).css('opacity', '');
					$( 'tr', sidebar ).show( 'fast' );
					$('input.off_canvas_sidebars_options_sidebars_enable', sidebar).trigger('change');
				}
			} );

		}

		if ( tab.val() == 'ocs-shortcode' ) {

			var fields = [ 'sidebar', 'text', 'action', 'element', 'class', 'attr', 'nested' ];

			for ( var i = 0, l = fields.length; i < l; i++ ) {
				$( '#off_canvas_sidebars_options_' + fields[i] ).on( 'change keyup', function() {
					create_shortcode();
				});
			}

			function create_shortcode() {
				var field_data = {};
				for ( var i = 0, l = fields.length; i < l; i++ ) {
					field_data[ fields[i] ] = $( '#off_canvas_sidebars_options_' + fields[i] );
				}

				var shortcode = 'ocs_trigger';

				//start the shortcode tag
				var shortcode_str = '[' + shortcode;

				// Loop through our known fields
				for ( var field in field_data ) {
					if ( typeof field_data[ field ] != 'undefined' ) {
						if ( field != 'text' && field != 'nested' ) {
							if ( field_data[ field ].is(':checked') ) {
								shortcode_str += ' ' + field + '="1"';
							} else if ( field_data[ field ].val().length ) {
								shortcode_str += ' ' + field + '="' + field_data[ field ].val().replace( /(\r\n|\n|\r)/gm, '' ) + '"';
							}
						}
					}
				}

				// If the test contains a double quote, force it to be nested for compatibility
				if ( field_data.text.val().length && field_data.text.val().indexOf( '"' ) !== -1 ) {
					field_data.nested = true;
				}

				//add panel text
				if ( field_data.nested.is(':checked') ) {
					shortcode_str += ']' + field_data.text.val() + '[/' + shortcode + ']';
				} else {
					if ( field_data.text.val().length ) {
						shortcode_str += ' text="' + field_data.text.val() + '"';
					}
					shortcode_str += ']';
				}

				$('textarea#ocs_shortcode').val( shortcode_str );

				create_shortcode_preview( field_data );
			}

			function create_shortcode_preview( field_data ) {

				var element = ( field_data.element.val() ) ? field_data.element.val() : 'button',
					attributes = ( field_data.attr.val() ) ? attrStringToObject( field_data.attr.val() ) : {},
					prefix = OCS_OFF_CANVAS_SIDEBARS_SETTINGS.css_prefix,
					action = ( field_data.action.val() ) ? field_data.action.val() : 'toggle';

				var classes = prefix + '-trigger ' + prefix + '-' + action;

				if ( field_data.sidebar.val() ) {
					classes += ' lekkerdan-' + action + '-' + field_data.sidebar.val();
				}
				if ( field_data.class.val() ) {
					classes += ' ' + field_data.class.val();
				}
				if ( attributes.class ) {
					classes += ' ' + attributes.class;
				}
				attributes.class = classes;

				var singleton = false;
				if ( element == 'input' ) {
					singleton = true;
					attributes.value = field_data.text.val();
				}
				if ( element == 'img' ) {
					singleton = true;
					attributes.value = field_data.text.val();
				}

				var html = '';
				if ( singleton ) {
					html = '<' + element + ' ' + attrObjectToHTML( attributes ) + '>';
				} else {
					html = '<' + element + ' ' + attrObjectToHTML( attributes ) + '>' + field_data.text.val() + '</' + element + '>';
				}

				$( '#ocs_shortcode_preview' ).html( html );

				$( '#ocs_shortcode_html' ).val( html );

			}

		}

		/**
		 * Convert HTML formatted attribute string to object
		 * In: key="value" key="value"
		 * Out: { key: value, key: value }
		 *
		 * @param attrString
		 * @returns Object
		 */
		function attrHTMLToObject( attrString ) {
			var arr = attrString.trim().split( '" ' ),
				atts = {};
			for ( var key in arr ) {
				arr[ key ] = arr[ key ].split( '="' );
				if ( arr[ key ][ 0 ].trim().length ) {
					atts[ arr[ key ][ 0 ].trim() ] = getAttr( attrString, arr[ key ][ 0 ] );
				}
			}
			return atts;
		}

		/**
		 * Convert OCS formatted attribute string to object
		 *
		 * In: key:value;key:value
		 * Out: { key: value, key: value }
		 *
		 * @param attrString
		 * @returns Object
		 */
		function attrStringToObject( attrString ) {
			var arr = attrString.split( ';' ),
				atts = {};
			for ( var key in arr ) {
				arr[ key ] = arr[ key ].split( ':' );
				if ( arr[ key ][ 0 ].trim().length ) {
					var name = arr[ key ][ 0 ].trim();
					arr[ key ].splice( 0, 1 );
					atts[ name ] = arr[ key ].join( ':' );
				}
			}
			return atts;
		}

		/**
		 * Convert object to OCS formatted attribute string
		 *
		 * In: { key: value, key: value }
		 * Out: key="value" key="value"
		 *
		 * @param attrObj
		 * @returns String
		 */
		function attrObjectToHTML( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				atts.push( name + '="' + attrObj[ name ] + '"' );
			}
			return atts.join( ' ' );
		}

		/**
		 * Convert object to HTML formatted attribute string
		 *
		 * In: { key: value, key: value }
		 * Out: key:value;key:value
		 *
		 * @param attrObj
		 * @returns String
		 */
		function attrObjectToString( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				atts.push( name + ':' + attrObj[ name ] );
			}
			return atts.join( ';' );
		}

	};

	OCS_OFF_CANVAS_SIDEBARS_SETTINGS.init();

})(jQuery);