;/**
 * Off-Canvas Sidebars tinymce shortcode UI
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.4
 * @global ocsMceSettings
 * @preserve
 *
 * https://generatewp.com/take-shortcodes-ultimate-level/
 */

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

		//add popup
		editor.addCommand( 'ocs_trigger_popup', function ( ui, v ) {

			var fields = [];
			// Used $.extent() to remove the original object reference
			var defaults = $.extend( true, {}, ocsMceSettings.fields );
			//setup defaults
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

			//open the popup
			var popup = editor.windowManager.open( {
				title: ocsMceSettings.title,
				body: fields,
				//when the ok button is clicked
				onsubmit: function ( e ) {
					//start the shortcode tag
					var shortcode_str = '[' + shortcode;

					// Loop through our known fields
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

					// If the test contains a double quote, force it to be nested for compatibility
					if ( e.data.text.length && e.data.text.indexOf( '"' ) !== -1 ) {
						e.data.nested = true;
					}

					//add panel text
					if ( 'undefined' !== typeof e.data.nested && e.data.nested ) {
						shortcode_str += ']' + e.data.text + '[/' + shortcode + ']';
					} else {
						if ( e.data.text.length ) {
							shortcode_str += ' text="' + e.data.text + '"';
						}
						shortcode_str += ']';
					}

					//insert shortcode to TinyMCE
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

		// Add toolbar remove button
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

		// Add toolbar remove button
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

		// Create toolbar
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

		// Show toolbar
		editor.on( 'wptoolbar', function ( e ) {
			if ( e.element.className.indexOf( 'ocsTrigger' ) > -1 ) {
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

		// Disable our button if the selected text is already a OCS shortcode
		editor.on( 'NodeChange', function ( e ) {
			if ( e.element.className.indexOf( 'ocsTrigger' ) > -1 ) {
				$( e.target.container ).closest( '.wp-editor-wrap' ).find( 'button.ocs-shortcode-generator' ).attr( 'disabled', true );
			} else {
				$( e.target.container ).closest( '.wp-editor-wrap' ).find( 'button.ocs-shortcode-generator' ).attr( 'disabled', false );
			}
		} );

		// Always enable the button again when the editor gets blurred (out of focus)
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
			if ( e.target.className.indexOf( 'ocsTrigger' ) > -1 ) {
				doTriggerPopup( e.target, this );
			}
		} );

		/**
		 * Sets the selected element and the element data before triggering the popup
		 *
		 * @param el
		 * @param curEditor
		 */
		function doTriggerPopup( el, curEditor ) {
			curEditor.selection.select( el );
			var $el = $( el ),
				attr = parseHTMLToData( el ),
				data = {};

			// Loop through our known fields
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

			// overwrites
			data.nested = ( 'true' === $el.attr( 'data-ocs-nested' ) );
			data.text = parseElementText( el );

			curEditor.execCommand( 'ocs_trigger_popup', '', data );
		}

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
		 *
		 * @param cls
		 * @param data
		 * @param con
		 * @param nested
		 * @returns {string}
		 */
		function parseDataToHTML( cls, data, con, nested ) {
			var attrData = {
				//id: getAttr( data, 'id' ),
				//action: getAttr( data, 'action' ),
				element: getAttr( data, 'element' ),
				class: getAttr( data, 'class' ),
				attributes: getAttr( data, 'attr' )
			};

			if ( ! nested ) {
				con = getAttr( data, 'text' );
			}

			if ( ! attrData.attributes.length ) {
				attrData.attributes = getAttr( data, 'attributes' );
			}

			if ( ! attrData.class.length ) {
				attrData.class = getAttr( data, 'classes' );
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
				data[ key ] = attrData[ key ];
			}
			// Remove duplicates and empty values
			data = objectRemoveEmpty( data );
			data = attrObjectToHTML( data );

			data = window.encodeURIComponent( data );

			var content = window.encodeURIComponent( con );
			// Some text is required for the editor to work properly
			if ( ! content.length && 'img' !== attrData.element ) {
				content = con = '&nbsp;';
			}

			if ( 'img' === attrData.element ) { // && typeof elAttributes.alt != 'string'
				attributes.alt = content;
			}

			var elAttributes = attrObjectToHTML( attributes );

			if ( $.inArray( attrData.element, singletons ) > -1 ) {
				// singleton element
				//nested = false;

				return '<' + attrData.element + ' ' + elAttributes + ' ' + 'data-ocs-nested="' + nested + '" ' + 'data-ocs-attr="' + data + '" data-ocs-text="' + content + '" />';
			} else {
				return '<' + attrData.element + ' ' + elAttributes + ' ' + 'data-ocs-nested="' + nested + '" ' + 'data-ocs-attr="' + data + '" data-ocs-text="' + content + '">' + con + '</' + attrData.element + '>';
			}
			// data-mce-resize="false" data-mce-placeholder="1"
		}

		/**
		 *
		 * @param el
		 * @returns {Object}
		 */
		function parseHTMLToData( el ) {
			var attr = window.decodeURIComponent( $( el ).attr( 'data-ocs-attr' ) );

			var attributes = attrHTMLToObject( attr );
			var elAttributes = attrElementToObject( el );

			// Overwrites
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

			// Converting
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
				} else if ( key.indexOf( 'data-' ) === -1 && 'string' === typeof elAttributes[ key ] ) {
					// overwrite, check for alt tag for images
					if ( 'alt' === key && $( el ).is( 'img' ) ) {
						delete attributes.attr[ key ];
					} else {
						attributes.attr[ key ] = '' + elAttributes[ key ];
					}
				}
			}

			if ( 'string' === typeof attributes.class && attributes.class.trim().length ) {
				// Convert classes to an array
				attributes.class = attributes.class.split( ' ' );
				// Remove MCE plugin defined classes
				attributes.class = arrayFilterUnique( attributes.class, true, true );
				// Rejoin the classes
				attributes.class = attributes.class.join( ' ' );
			}

			if ( 'object' === typeof attributes.attr ) {
				attributes.attr = attrObjectToString( attributes.attr );
			}

			// Remove duplicate and empty attributes
			attributes = objectRemoveEmpty( attributes );

			return attributes;
		}

		/**
		 *
		 * @param el
		 * @returns {string}
		 */
		function parseElementText( el ) {
			var $el = $( el ),
				text = window.decodeURIComponent( $el.attr( 'data-ocs-text' ) );
			if ( $el.html().length ) {
				text = $el.html();
			}

			// Images have the alt tag for text
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
		 *
		 * @param el
		 * @param editor
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
		 * @param s
		 * @param a
		 * @param f
		 * @returns {*}
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
		 * @param el
		 * @returns Object
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

		/**
		 * Return unique values only
		 *
		 * @param array Array
		 * @param empty bool Remove empty keys?
		 * @param ocs bool Check this plugin's defines classes
		 * @returns Array
		 */
		function arrayFilterUnique( array, empty, ocs ) {
			if ( ! array.length ) {
				return [];
			}
			return $.grep( array, function ( el, index ) {
				if ( empty && !el.length ) {
					return false;
				}
				if ( ocs && $.inArray( el, mceClasses ) > -1 ) {
					return false;
				}
				return index === $.inArray( el, array );
			} );
		}

		/**
		 * Remove empty properties from object
		 * Only objects with strings as parameters allowed!
		 *
		 * @param obj Object
		 * @returns Object
		 */
		function objectRemoveEmpty( obj ) {
			for ( var name in obj ) {
				if ( 'string' === typeof obj[ name ] ) {
					obj[ name ] = obj[ name ].trim();
				}
				if ( obj[ name ] === null || obj[ name ] === undefined || ! obj[ name ].length ) {
					delete obj[ name ];
				}
			}
			return obj;
		}

	} );

//} )();
} )( window.tinymce, jQuery );
