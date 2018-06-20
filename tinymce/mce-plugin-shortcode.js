/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars - TinyMCE shortcode UI
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.4.0
 * @version 0.5.1
 * @global  ocsMceSettings
 * @preserve
 *
 * https://generatewp.com/take-shortcodes-ultimate-level/
 */
/* eslint-enable no-extra-semi */

( function( tinymce, $ ) {

	tinymce.PluginManager.add( 'off_canvas_sidebars', function ( editor, url ) {

		var toolbar;
		var toolbarImg;
		var toolbarLink;
		var toolbarElement;
		var shortcode = 'ocs_trigger';
		var mceClasses = [
			'mceItem',
			'ocsTrigger',
			ocsMceSettings.prefix + '-trigger'
		];
		var singletons = [ 'br', 'hr', 'img', 'input' ];

		$( document ).ready( function () {
			if ( 'undefined' !== typeof ocsMceSettings ) {
				$( '.ocs-shortcode-generator' ).removeClass( 'hidden' );
			}
		} );

		$( document ).on( 'click', '#wp-' + editor.id + '-wrap .wp-media-buttons button.ocs-shortcode-generator', function () {
			var selection = editor.selection.getContent(),
				$selection = $( selection ),
				data = {};

			if ( selection.length ) {
				if ( $selection.is( 'img' ) ) {
					data.text = $selection.attr( 'alt' );
					data.element = 'img';
					data.class = $selection.attr( 'class' );
					var attr = [];
					if ( $selection.attr( 'src' ) ) {
						attr.push( 'src:' + $selection.attr( 'src' ) );
					}
					if ( $selection.attr( 'width' ) ) {
						attr.push( 'width:' + $selection.attr( 'width' ) );
					}
					if ( $selection.attr( 'height' ) ) {
						attr.push( 'height:' + $selection.attr( 'height' ) );
					}
					if ( $selection.attr( 'title' ) ) {
						attr.push( 'title:' + $selection.attr( 'title' ) );
					}
					data.attr = attr.join( ';' );
				} else {
					// @todo support other elements
					data.text = selection;
					data.element = 'span';
				}
			}
			editor.execCommand( 'ocs_trigger_popup', '', data );
			return false;
		} );

		// Add popup.
		editor.addCommand( 'ocs_trigger_popup', function ( ui, v ) {

			var fields = [];
			// Used $.extent() to remove the original object reference.
			var defaults = $.extend( true, {}, ocsMceSettings.fields );
			// Setup defaults.
			for ( var field in defaults ) {
				if ( ! defaults.hasOwnProperty( field ) ) {
					continue;
				}
				var fieldData = defaults[ field ];
				if ( v[ field ] ) {
					if ( 'checkbox' === fieldData.type ) {
						fieldData.checked = v[ field ];
					} else {
						fieldData.value = v[ field ];
					}
				}
				if ( 'text' === fieldData.name && 'img' === v.element ) {
					fieldData.label = 'Alt';
					fieldData.tooltip = '';
					fieldData.multiline = false;
				}
				fields.push( fieldData );
			}

			// Open the popup.
			var popup = editor.windowManager.open( {
				title: ocsMceSettings.title,
				body: fields,
				// When the ok button is clicked.
				onsubmit: function ( e ) {
					// Start the shortcode tag.
					var shortcode_str = '[' + shortcode;

					// Loop through our known fields.
					for ( var field in ocsMceSettings.fields ) {
						if ( ! ocsMceSettings.fields.hasOwnProperty( field ) ) {
							continue;
						}
						if ( 'container' === ocsMceSettings.fields[ field ].type ) {
							continue;
						}
						if ( 'undefined' !== typeof e.data[ field ] ) {
							if ( -1 >= $.inArray( field, [ 'text', 'nested' ] ) ) {
								if ( 'checkbox' === ocsMceSettings.fields[ field ].type && e.data[ field ] ) {
									shortcode_str += ' ' + field + '="1"';
								} else if ( e.data[ field ].length ) {
									shortcode_str += ' ' + field + '="' + e.data[ field ].replace( /(\r\n|\n|\r)/gm, '' ) + '"';
								}
							}
						}
					}

					// If the test contains a double quote, force it to be nested for compatibility.
					if ( e.data.text.length && -1 !== e.data.text.indexOf( '"' ) ) {
						e.data.nested = true;
					}

					// Add panel text.
					if ( 'undefined' !== typeof e.data.nested && e.data.nested ) {
						shortcode_str += ']' + e.data.text + '[/' + shortcode + ']';
					} else {
						if ( e.data.text.length ) {
							shortcode_str += ' text="' + e.data.text + '"';
						}
						shortcode_str += ']';
					}

					// Insert shortcode to TinyMCE.
					editor.insertContent( shortcode_str );
				}
			} );
		} );

		//tinymce.ui.OCSTriggerPopup = tinymce.ui.Control.extend( {} );

		// Add toolbar edit button
		editor.addButton( 'ocs_trigger_popup', {
			tooltip: 'Edit Off-Canvas trigger',
			icon: 'dashicon dashicons-admin-links',
			label: 'Edit Off-Canvas trigger',
			title: 'Off-Canvas trigger',
			//cmd: 'ocs_trigger_popup'
			onclick: function () {
				doTriggerPopup( toolbarElement, editor );
			}
		} );

		// Add toolbar remove button.
		editor.addButton( 'ocs_trigger_unlink', {
			tooltip: 'Remove Off-Canvas trigger',
			icon: 'dashicon dashicons-editor-unlink',
			label: 'Remove Off-Canvas trigger',
			//cmd: 'ocs_trigger_popup'
			onclick: function () {
				removeTriggerData( toolbarElement, editor );
				toolbarElement = null;
				tinymce.toolbar = null;
				//editor.execCommand( 'unlink' );
			}
		} );

		// Add toolbar remove button.
		editor.addButton( 'ocs_trigger_remove', {
			tooltip: 'Remove',
			icon: 'dashicon dashicons-no',
			label: 'Remove',
			//cmd: 'ocs_trigger_popup'
			onclick: function () {
				$( toolbarElement ).remove();
				toolbarElement = null;
				editor.execCommand( 'unlink' );
			}
		} );

		// Create toolbar.
		editor.on( 'preinit', function () {
			if ( editor.wp && editor.wp._createToolbar ) {
				toolbar = editor.wp._createToolbar( [
					'ocs_trigger_popup',
					'ocs_trigger_unlink',
					'ocs_trigger_remove'
				], true );

				toolbarImg = editor.wp._createToolbar( [
					'wp_img_alignleft',
					'wp_img_aligncenter',
					'wp_img_alignright',
					'wp_img_alignnone',
					'ocs_trigger_popup',
					'ocs_trigger_unlink',
					'wp_img_edit',
					'wp_img_remove'
				], true );

				toolbarLink = editor.wp._createToolbar( [
					'wp_link_preview',
					'ocs_trigger_popup',
					'ocs_trigger_unlink',
					'wp_link_edit',
					'wp_link_remove'
				], true );
			}
		} );

		// Show toolbar.
		editor.on( 'wptoolbar', function ( e ) {
			e = get_ocs_trigger_element( e );
			if ( e ) {
				toolbarElement = e.element;
				if ( $( e.element ).is( 'img' ) ) {
					e.toolbar = toolbarImg;
				} else if( $( e.element ).is( 'a' ) ) {
					e.toolbar = toolbarLink;
				} else {
					// @todo support wpLink
					e.toolbar = toolbar;
				}
			}
		} );

		// Disable our button if the selected text is already a OCS shortcode.
		editor.on( 'NodeChange', function ( e ) {
			if ( get_ocs_trigger_element( e ) ) {
				$( e.target.container ).closest( '.wp-editor-wrap' ).find( 'button.ocs-shortcode-generator' ).attr( 'disabled', true );
			} else {
				$( e.target.container ).closest( '.wp-editor-wrap' ).find( 'button.ocs-shortcode-generator' ).attr( 'disabled', false );
			}
		} );

		// Always enable the button again when the editor gets blurred (out of focus).
		editor.on( 'Blur', function ( e ) {
			$( e.target.container ).closest( '.wp-editor-wrap' ).find( 'button.ocs-shortcode-generator' ).attr( 'disabled', false );
		} );

		editor.on( 'BeforeSetcontent', function ( e ) {
			if ( ocsMceSettings.render ) {
				e.content = replaceShortcodes( e.content );
			}
		} );

		editor.on( 'GetContent', function ( e ) {
			e.content = restoreShortcodes( e.content );
		} );

		editor.on( 'DblClick', function ( e ) {
			e = get_ocs_trigger_element( e );
			if ( e ) {
				doTriggerPopup( e.target, this );
			}
		} );

		/**
		 * Check if a MCE element is a OCS trigger.
		 * @param   {object}  e  The MCE element.
		 * @returns {object}  The OCS trigger MCE element.
		 */
		function get_ocs_trigger_element( e ) {
			if ( ! e.element ) {
				return null;
			}
			if ( e.element.className && -1 < e.element.className.indexOf( 'ocsTrigger' ) ) {
				return e;
			}
			// Icon triggers.
			if ( 1 < e.parents.length && e.parents[1].className && -1 < e.parents[1].className.indexOf( 'ocsTrigger' ) ) {
				e.element = e.parents[1];
				e.parents.shift();
				return e;
			}
			return null;
		}

		/**
		 * Sets the selected element and the element data before triggering the popup
		 *
		 * @param  {string}  el         The element HTML.
		 * @param  {object}  curEditor  The current editor.
		 * @return {null} Nothing.
		 */
		function doTriggerPopup( el, curEditor ) {
			curEditor.selection.select( el );
			var $el = $( el ),
				attr = parseHTMLToData( el ),
				data = {};

			// Loop through our known fields.
			for ( var field in ocsMceSettings.fields ) {
				if ( ! ocsMceSettings.fields.hasOwnProperty( field ) ) {
					continue;
				}
				if ( 'container' === ocsMceSettings.fields[ field ].type ) {
					continue;
				}
				if ( 'checkbox' === ocsMceSettings.fields[ field ].type ) {
					data[ field ] = ( '1' === attr[ field ] );
				} else {
					data[ field ] = attr[ field ];
				}
			}

			data = objectRemoveEmpty( data );

			// overwrites.
			data.nested = ( 'true' === $el.attr( 'data-ocs-nested' ) );
			data.text = parseElementText( el );

			curEditor.execCommand( 'ocs_trigger_popup', '', data );
		}

		/**
		 * Find the OCS shortcode in a block of text and parse them to actual HTML.
		 * @param  {string}  content  The content.
		 * @return {string}  The new HTML.
		 */
		function replaceShortcodes( content ) {

			//match [ocs_trigger(attr)](con)[/ocs_trigger]
			content = content.replace( /\[ocs_trigger([^\]]*)\]([^\]]*)\[\/ocs_trigger\]/g, function ( all, attr, con ) {
				return parseDataToHTML( 'ocsTrigger', attr, con, true );
			} );

			//match [ocs_trigger(attr)]
			content = content.replace( /\[ocs_trigger([^\]]*)\]/g, function ( all, attr ) {
				return parseDataToHTML( 'ocsTrigger', attr, '', false );
			} );

			return content;
		}

		/**
		 * Convert Trigger HTML into shortcodes.
		 * @param  {string}  content  The content.
		 * @return {string}  The new HTML.
		 */
		function restoreShortcodes( content ) {

			var html = $( '<div/>' ).html( content );

			$( html ).find( '.mceItem.ocsTrigger' ).each( function () {
				var attr = parseHTMLToData( this );
				var text = parseElementText( this );
				var nested = window.decodeURIComponent( $( this ).attr( 'data-ocs-nested' ) );

				if ( 'true' === nested ) {
					delete attr.text;
					attr = attrObjectToHTML( attr );
					$( this ).replaceWith( '[' + shortcode + ' ' + attr + ']' + text + '[/' + shortcode + ']' );
				} else {
					attr.text = text;
					attr = attrObjectToHTML( attr );
					$( this ).replaceWith( '[' + shortcode + ' ' + attr + ']' );
				}
			} );

			content = $( html ).html();
			return content;
		}

		/**
		 * Convert data to HTML.
		 * @param  {string}   cls     Class.
		 * @param  {string}   data    Attribute data.
		 * @param  {string}   con     Content.
		 * @param  {boolean}  nested  Is it a nested shortcode?
		 * @return {string} The HTML.
		 */
		function parseDataToHTML( cls, data, con, nested ) {
			var attrData = {
				//id: getAttr( data, 'id' ),
				//action: getAttr( data, 'action' ),
				element: getAttr( data, 'element', false ),
				icon: getAttr( data, 'icon', false ),
				icon_location: getAttr( data, 'icon_location', false ),
				class: getAttr( data, 'class', false ),
				attributes: getAttr( data, 'attr', false )
			};

			if ( ! nested ) {
				con = getAttr( data, 'text', false );
			}

			if ( ! attrData.attributes.length ) {
				attrData.attributes = getAttr( data, 'attributes', false );
			}

			if ( ! attrData.class.length ) {
				attrData.class = getAttr( data, 'classes', false );
			}

			if ( ! attrData.element.length ) {
				attrData.element = 'button';
			}

			var attributes = attrStringToObject( attrData.attributes );

			var classes = [];
			classes.push( 'mceItem', cls );
			if ( 'string' === typeof attributes.class ) {
				classes = classes.concat( attributes.class.split( ' ' ) );
				delete attributes.class;
			}
			if ( attrData.class.length ) {
				classes = classes.concat( attrData.class.split( ' ' ) );
			}

			attrData.class = arrayFilterUnique( classes, true, true ).join( ' ' );
			attributes.class = arrayFilterUnique( classes, true, false ).join( ' ' );
			attributes = objectRemoveEmpty( attributes );

			data = attrHTMLToObject( data );
			for ( var key in attrData ) {
				if ( attrData.hasOwnProperty( key ) ) {
					data[ key ] = attrData[ key ];
				}
			}
			// Remove duplicates and empty values.
			data = objectRemoveEmpty( data );
			data = attrObjectToHTML( data );

			data = window.encodeURIComponent( data );

			var content = window.encodeURIComponent( con );
			// Some text is required for the editor to work properly.
			if ( ! content.length && 'img' !== attrData.element ) {
				content = con = '&nbsp;';
			}

			if ( 'img' === attrData.element ) { // && 'string' !== typeof elAttributes.alt
				attributes.alt = content;
			} else {
				// Icons can not be used with singleton elements.
				if ( attrData.icon ) {
					icon = '<span class="ocs-parse-remove icon ' + attrData.icon + '">&nbsp;</span>';
					if ( con ) {
						con = '<span class="ocs-parse-text ocs-parse-remove label">' + con + '</span>';
					}
					if ( 'after' === attrData.icon_location ) {
						con += icon;
					} else {
						con = icon + con;
					}
				}
			}

			attributes['data-ocs-nested'] = nested;
			attributes['data-ocs-attr'] = data;
			attributes['data-ocs-text'] = content;

			var elAttributes = attrObjectToHTML( attributes );

			if ( -1 < $.inArray( attrData.element, singletons ) ) {
				return '<' + attrData.element + ' ' + elAttributes + ' />';
			} else {
				return '<' + attrData.element + ' ' + elAttributes + '>' + con + '</' + attrData.element + '>';
			}
			// data-mce-resize="false" data-mce-placeholder="1"
		}

		/**
		 * Convert attribute string to attribute object.
		 * @param  {string|object} el The element.
		 * @return {Object}  Attributes.
		 */
		function parseHTMLToData( el ) {
			var attr = window.decodeURIComponent( $( el ).attr( 'data-ocs-attr' ) );

			var attributes = attrHTMLToObject( attr );
			var elAttributes = attrElementToObject( el );

			// Overwrites.
			if ( 'undefined' !== typeof attributes.attributes ) {
				if ( 'string' !== typeof attributes.attr ) {
					attributes.attr = attributes.attributes;
				}
				delete attributes.attributes;
			}
			if ( 'undefined' !== typeof attributes.classes ) {
				if ( 'string' !== typeof attributes.class ) {
					attributes.class = attributes.classes;
				}
				delete attributes.classes;
			}

			// Converting.
			if ( 'string' === typeof attributes.attr ) {
				attributes.attr = attrStringToObject( attributes.attr );
			} else {
				attributes.attr = {};
			}

			for ( var key in elAttributes ) {
				if ('class' === key && elAttributes.class.length ) {
					// append
					if ( 'string' === typeof attributes.class ) {
						attributes.class += ' ' + elAttributes.class;
					} else {
						attributes.class = elAttributes.class;
					}
				} else if ( -1 === key.indexOf( 'data-' ) && 'string' === typeof elAttributes[ key ] ) {
					// overwrite, check for alt tag for images.
					if ( 'alt' === key && $( el ).is( 'img' ) ) {
						delete attributes.attr[ key ];
					} else {
						attributes.attr[ key ] = '' + elAttributes[ key ];
					}
				}
			}

			if ( 'string' === typeof attributes.class && attributes.class.trim().length ) {
				// Convert classes to an array.
				attributes.class = attributes.class.split( ' ' );
				// Remove MCE plugin defined classes.
				attributes.class = arrayFilterUnique( attributes.class, true, true );
				// Rejoin the classes.
				attributes.class = attributes.class.join( ' ' );
			}

			if ( 'object' === typeof attributes.attr ) {
				attributes.attr = attrObjectToString( attributes.attr );
			}

			// Remove duplicate and empty attributes.
			attributes = objectRemoveEmpty( attributes );

			return attributes;
		}

		/**
		 * Get the element text, also checks for nested element attributes.
		 * @param  {string|object}  el  The element.
		 * @return {string} The text.
		 */
		function parseElementText( el ) {
			var $el = $( el ),
				text = window.decodeURIComponent( $el.attr( 'data-ocs-text' ) );
			if ( $el.html().length ) {

				$( '.ocs-parse-text', $el ).each( function() {
					$(this).before( $(this).text() );
				} );
				$( '.ocs-parse-remove', $el ).remove();

				text = $el.html();
			}

			// Images have the alt tag for text.
			if ( $el.is('img') ) {
				if ( $el.attr('alt') ) {
					text = $el.attr('alt');
				} else {
					text = '';
				}
			}
			return text;
		}

		/**
		 * Remove OCS classes & attributes.
		 * @param  {string|object}  el     The element.
		 * @param  {object}         editor The editor.
		 * @return {void}
		 */
		function removeTriggerData( el, editor ) {
			var $el = $( el );
			$el.removeClass( 'mceItem' );
			$el.removeClass( 'ocsTrigger' );
			$el.removeAttr( 'data-ocs-attr' );
			$el.removeAttr( 'data-ocs-text' );
			$el.removeAttr( 'data-ocs-nested' );
			//$el.removeAttr( 'data-mce-placeholder' );
		}

		/**
		 * @param  {string}   s  The string.
		 * @param  {string}   a  The attribute to find.
		 * @param  {boolean}  f  @todo
		 * @return {string|boolean}  The attribute value or false.
		 */
		function getAttr( s, a, f ) {
			var n = new RegExp( a + '=\"([^\"]+)\"', 'g' ).exec( s );
			if ( true === f && !n && -1 === s.indexOf( a + '="' ) ) {
				// Attribute does not exist
				return false;
			}
			return n ? window.decodeURIComponent( n[ 1 ] ).trim() : '';
		}

		/**
		 * Get attributes from a jQuery element
		 * If it contains multiple elements it only returns attributes from the first
		 *
		 * In: element
		 * Out: { key: value, key: value }
		 *
		 * @since  0.4
		 * @param  {string|object} el The element.
		 * @return {object} The attribute object.
		 */
		function attrElementToObject( el ) {
			var $el = $( el ),
				atts = {};
			if ( $el.length ) {
				$.each( $el[ 0 ].attributes, function ( index, attr ) {
					atts[ attr.name ] = attr.value;
				} );
			}
			return atts;
		}

		/**
		 * Convert HTML formatted attribute string to object.
		 * In: key="value" key="value"
		 * Out: { key: value, key: value }
		 *
		 * @since  0.4
		 * @param  {string}  attrString  The attribute string.
		 * @return {object}  The attribute object.
		 */
		function attrHTMLToObject( attrString ) {
			var arr = attrString.trim().split( '" ' ),
				atts = {};
			for ( var key in arr ) {
				if ( arr.hasOwnProperty( key ) ) {
					arr[ key ] = arr[ key ].split( '="' );
					if ( arr[ key ][ 0 ].trim().length ) {
						atts[ arr[ key ][ 0 ].trim() ] = getAttr( attrString, arr[ key ][ 0 ], false );
					}
				}
			}
			return atts;
		}

		/**
		 * Convert OCS formatted attribute string to object.
		 *
		 * In: key:value;key:value
		 * Out: { key: value, key: value }
		 *
		 * @since  0.4
		 * @param  {string}  attrString  The attribute string.
		 * @return {object}  The attribute object.
		 */
		function attrStringToObject( attrString ) {
			var arr = attrString.split( ';' ),
				atts = {};
			for ( var key in arr ) {
				if ( arr.hasOwnProperty( key ) ) {
					arr[ key ] = arr[ key ].split( ':' );
					if ( arr[ key ][ 0 ].trim().length ) {
						var name = arr[ key ][ 0 ].trim();
						arr[ key ].splice( 0, 1 );
						atts[ name ] = arr[ key ].join( ':' );
					}
				}
			}
			return atts;
		}

		/**
		 * Convert object to OCS formatted attribute string.
		 *
		 * In: { key: value, key: value }
		 * Out: key="value" key="value"
		 *
		 * @since  0.4
		 * @param  {object}  attrObj  The attribute object.
		 * @return {string}  The attribute string.
		 */
		function attrObjectToHTML( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				if ( attrObj.hasOwnProperty( name ) ) {
					atts.push( name + '="' + attrObj[ name ] + '"' );
				}
			}
			return atts.join( ' ' );
		}

		/**
		 * Convert object to HTML formatted attribute string.
		 *
		 * In: { key: value, key: value }
		 * Out: key:value;key:value
		 *
		 * @since  0.4
		 * @param  {object}  attrObj  The attribute object.
		 * @return {string}  The attribute string.
		 */
		function attrObjectToString( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				if ( attrObj.hasOwnProperty( name ) ) {
					atts.push( name + ':' + attrObj[ name ] );
				}
			}
			return atts.join( ';' );
		}

		/**
		 * Return unique values only.
		 *
		 * @param  {object}   array  The array to filter.
		 * @param  {boolean}  empty  Remove empty keys?
		 * @param  {boolean}  ocs    Check this plugin's defines classes.
		 * @return {object} The filtered array.
		 */
		function arrayFilterUnique( array, empty, ocs ) {
			if ( ! array.length ) {
				return [];
			}
			return $.grep( array, function ( el, index ) {
				if ( empty && !el.length ) {
					return false;
				}
				if ( ocs && -1 < $.inArray( el, mceClasses ) ) {
					return false;
				}
				return index === $.inArray( el, array );
			} );
		}

		/**
		 * Remove empty properties from object.
		 * Only objects with strings as values allowed!
		 *
		 * @param  {object}  obj  The object to filter.
		 * @return {object} The object without empty keys.
		 */
		function objectRemoveEmpty( obj ) {
			for ( var name in obj ) {
				if ( ! obj.hasOwnProperty( name ) ) {
					continue;
				}
				if ( 'string' === typeof obj[ name ] ) {
					obj[ name ] = obj[ name ].trim();
				}
				if ( null === obj[ name ] || undefined === obj[ name ] || ! obj[ name ].length ) {
					delete obj[ name ];
				}
			}
			return obj;
		}

	} );

} ( window.tinymce, jQuery ) );
