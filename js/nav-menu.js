/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars - Menu meta box scripts
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.3
 * @global  ocsNavControl
 * @preserve
 *
 * Credits to the Polylang plugin for inspiration
 */
/* eslint-enable no-extra-semi */

if ( 'undefined' === typeof ocsNavControl ) {
	var ocsNavControl = {};
}

jQuery( function( $ ) {

	/**
	 * Change/Update menu item type.
	 * @since  0.5.3
	 * @param  {object}  menu_item  The menu item element `.menu-item`.
	 * @param  {string}  key        The off-canvas sidebar key.
	 * @return {void}  Nothing.
	 */
	ocsNavControl.add_menu_item_type = function( menu_item, key ) {
		var $menu_item = $( menu_item ),
			control_type = '';

		if ( ! key ) {
			var db_id = $menu_item.find('.menu-item-data-db-id').val();
			/* If control selected, then show it */
			if ( ocsNavControl.val[ db_id ]['off-canvas-control'] ) {
				key = ocsNavControl.val[ db_id ]['off-canvas-control'];
			}
		}

		if ( key ) {
			control_type = ' <i class="item-type-off-canvas-control">(' + ocsNavControl.controls[ key ] + ')</i>';
		}
		$menu_item.find('.menu-item-bar .item-type').html( ocsNavControl.strings.menu_item_type + control_type );
	};

	$("input[value='#off_canvas_control'][type=text]").parents('.menu-item').each( function() {
		ocsNavControl.add_menu_item_type( this, false );
	});

	/* Change menu type label on selecting a controller */
	$(document).on( 'change', '.field-off-canvas-control input', function() {
		var $this = $(this);
		ocsNavControl.add_menu_item_type( $this.parents('.menu-item'), $this.val() );
	} );

	/* Init/change menu item options */
	$('#update-nav-menu').bind( 'click load', function( e ) {
		if ( e.target && e.target.className && -1 < e.target.className.indexOf('item-edit') ) {
			$('input[value="#off_canvas_control"][type=text]').parent().parent().parent().each( function() {
				var $this = $(this),
					item = $this.attr('id').substring(19),
					strings = ocsNavControl.strings,
					controls = ocsNavControl.controls,
					option;

				// Remove default fields we don't need
				$this.children('p.field-url, p.field-link-target, .field-xfn, .field-description').remove();
				// Change description width.
				$this.children('p.field-css-classes').removeClass('description-thin').addClass('description-wide');

				option = $('<input>').attr( {
					type: 'hidden',
					id: 'edit-menu-item-url-' + item,
					name: 'menu-item-url[' + item + ']',
					value: '#off_canvas_control'
				} );
				$this.append( option );

				// a hidden field which exits only if our jQuery code has been executed
				option = $('<input>').attr( {
					type: 'hidden',
					id: 'edit-menu-item-off-canvas-control-detect-' + item,
					name: 'menu-item-off-canvas-control-detect[' + item + ']',
					value: 1
				} );
				$this.append( option );

				var o = '';
				o += '<p class="field-off-canvas-control description description-wide">' + strings.menu_item_type + '<br>';
				for ( var key in controls ) {
					if ( ! controls.hasOwnProperty( key ) ) {
						continue;
					}
					var checked = '';
					if (( 'undefined' !== typeof ocsNavControl.val[ item ] && key === ocsNavControl.val[ item ]['off-canvas-control'] ) ) {
						checked = ' checked="checked"';
					}
					o += '<input type="radio" id="edit-menu-item-off-canvas-control-' + key + '-item-' + item + '" name="menu-item-off-canvas-control[' + item + ']" value="' + key + '" ' + checked + ' /> ';
					o += '<label for="edit-menu-item-off-canvas-control-' + key + '-item-' + item + '">' + controls[ key ] + '</label><br>';
				}
				o += '</p>';
				$this.prepend( o );

				// If only one sidebar is available, always select its control.
				var $field_control_options = $this.children('p.field-off-canvas-control').find('input[type="radio"]');
				if ( 1 === $field_control_options.length ) {
					$field_control_options.prop('checked', true);
				}

			} );
		}
	} );

} );
