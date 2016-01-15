/**
 * Off-Canvas Sidebars menu meta box scripts
 *
 * Menu Meta Box scripts
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.1
 *
 * Credits to the Polylang plugin
 */

jQuery(document).ready(function($) {
	
	$("input[value='#off_canvas_control'][type=text]").parent().parent().parent().parent().each( function() {
		$(this).addClass('off-canvas-control');
		$(this).find('.menu-item-bar .item-type').text(off_canvas_control_data.strings.menu_item_type);
	});
	
	$('#update-nav-menu').bind('click load', function(e) {
		if ( e.target && e.target.className && -1 != e.target.className.indexOf('item-edit')) {
			$("input[value='#off_canvas_control'][type=text]").parent().parent().parent().each( function(){
				var item = $(this).attr('id').substring(19);
				
				var strings = off_canvas_control_data.strings;
				
				$(this).children('p.field-url, p.field-link-target, .field-xfn, .field-description').remove(); // remove default fields we don't need
				$(this).children('p.field-css-classes').removeClass('description-thin').addClass('description-wide');
				
				/*option = $('<input>').attr({
						type: 'hidden',
						id: 'edit-menu-item-title-'+item,
						name: 'menu-item-title['+item+']',
						value: off_canvas_control_data.title
				});
				$(this).append(option);*/
				
				option = $('<input>').attr({
						type: 'hidden',
						id: 'edit-menu-item-url-'+item,
						name: 'menu-item-url['+item+']',
						value: '#off_canvas_control'
				});
				$(this).append(option);
				
				// a hidden field which exits only if our jQuery code has been executed
				option = $('<input>').attr({
						type: 'hidden',
						id: 'edit-menu-item-off-canvas-control-detect-'+item,
						name: 'menu-item-off-canvas-control-detect['+item+']',
						value: 1
				});
				$(this).append(option);
				
				o = '';
				o += '<p class="field-off-canvas-control description description-wide">'+strings.menu_item_type+'<br>';
				controls = off_canvas_control_data.controls;
				for (var key in controls) {
					var checked = '';
					if ((typeof(off_canvas_control_data.val[item]) != 'undefined' && off_canvas_control_data.val[item]['off-canvas-control'] == key)) {
						checked = ' checked="checked"';
					}
					o += '<input type="radio" id="edit-menu-item-off-canvas-control-'+key+'-item-'+item+'" name="menu-item-off-canvas-control['+item+']" value="'+key+'" '+checked+' /> ';
					o += '<label for="edit-menu-item-off-canvas-control-'+key+'-item-'+item+'">'+controls[key]+'</label><br>';
				};
				o += '</p>';
				$(this).prepend(o);
				
				// Of only one sidebar is available, allways select its control
				if ($(this).children('p.field-off-canvas-control').find('input[type="radio"]').length == 1) {
					$(this).children('p.field-off-canvas-control').find('input[type="radio"]').prop('checked', true);
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
			});
		}
	});

});
