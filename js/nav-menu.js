;/**
 * Off-Canvas Sidebars menu meta box scripts
 *
 * Menu Meta Box scripts
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.4
 *
 * Credits to the Polylang plugin for inspiration
 */

if ( 'undefined' === typeof off_canvas_control_data ) {
	var off_canvas_control_data = {};
}

jQuery( document ).ready( function( $ ) {

	$("input[value='#off_canvas_control'][type=text]").parent().parent().parent().parent().each( function() {
		var $this = $(this),
			control_type = '',
			db_id = $this.find('.menu-item-data-db-id').val();
		$this.addClass('off-canvas-control');

		/* If control selected, then show it */
		if ( off_canvas_control_data.val[ db_id ]['off-canvas-control']) {
			var key = off_canvas_control_data.val[ db_id ]['off-canvas-control'];
			control_type = ' <i class="item-type-off-canvas-control">(' + off_canvas_control_data.controls[ key ] + ')</i>';
		}
		$this.find('.menu-item-bar .item-type').html( off_canvas_control_data.strings.menu_item_type + control_type );
	});

	/* change menu type label on selecting a controller */
	$(document).on( 'change', '.field-off-canvas-control input', function() {
		var key = $(this).val();
		var control_type = ' <i class="item-type-off-canvas-control">(' + off_canvas_control_data.controls[ key ] + ')</i>';
		$(this).parents('.menu-item').find('.menu-item-bar .item-type').html( off_canvas_control_data.strings.menu_item_type + control_type );
	} );

	/* init/change menu item options */
	$('#update-nav-menu').bind( 'click load', function(e) {
		if ( e.target && e.target.className && -1 < e.target.className.indexOf('item-edit')) {
			$('input[value="#off_canvas_control"][type=text]').parent().parent().parent().each( function() {
				var $this = $(this),
					item = $this.attr('id').substring(19),
					strings = off_canvas_control_data.strings,
					controls = off_canvas_control_data.controls,
					option;

				$this.children('p.field-url, p.field-link-target, .field-xfn, .field-description').remove(); // remove default fields we don't need
				$this.children('p.field-css-classes').removeClass('description-thin').addClass('description-wide');

				/*option = $('<input>').attr({
						type: 'hidden',
						id: 'edit-menu-item-title-'+item,
						name: 'menu-item-title['+item+']',
						value: off_canvas_control_data.title
				});
				$this.append(option);*/

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
					if (( 'undefined' !== typeof off_canvas_control_data.val[ item ] && key === off_canvas_control_data.val[ item ]['off-canvas-control'] ) ) {
						checked = ' checked="checked"';
					}
					o += '<input type="radio" id="edit-menu-item-off-canvas-control-' + key + '-item-' + item + '" name="menu-item-off-canvas-control[' + item + ']" value="' + key + '" ' + checked + ' /> ';
					o += '<label for="edit-menu-item-off-canvas-control-' + key + '-item-' + item + '">' + controls[ key ] + '</label><br>';
				}
				o += '</p>';
				$this.prepend( o );

				// Of only one sidebar is available, always select its control.
				var $field_control_options = $this.children('p.field-off-canvas-control').find('input[type="radio"]');
				if ( 1 === $field_control_options.length ) {
					$field_control_options.prop('checked', true);
				}
				/*
				ids = Array('menu_item_type'); // reverse order
				// add the fields
				for(var i = 0; i < ids.length; i++) {
					p = $('<p>').attr('class', 'description');
					$(this).prepend(p);
					label = $('<label>').attr('for', 'menu-item-'+ids[i]+'-'+item).text(' '+off_canvas_control_data.strings[i]);
					p.append(label);
					cb = $('<input>').attr({
						type: 'checkbox',
						id: 'edit-menu-item-'+ids[i]+'-'+item,
						name: 'menu-item-'+ids[i]+'['+item+']',
						value: 1
					});
					if ((typeof(off_canvas_control_data.val[item]) != 'undefined' && off_canvas_control_data.val[item][ids[i]] == 1) || (typeof(off_canvas_control_data.val[item]) == 'undefined' && ids[i] == 'show_names')) // show_names as default value
						cb.prop('checked', true);
					label.prepend(cb);
				}
				*/
			} );
		}
	} );

} );
