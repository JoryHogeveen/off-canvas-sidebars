;/**
 * Off-Canvas Sidebars plugin
 *
 * OCS_OFF_CANVAS_SIDEBARS_SETTINGS
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2
 */

if ( typeof OCS_OFF_CANVAS_SIDEBARS_SETTINGS == 'undefined' ) {
	var OCS_OFF_CANVAS_SIDEBARS_SETTINGS = {
		'general_key': 'off_canvas_sidebars_options',
		'plugin_key': 'off-canvas-sidebars-settings',
		'__required_fields_not_set': '', //Some required fields are not set!
	};
}

(function($) {

	OCS_OFF_CANVAS_SIDEBARS_SETTINGS.init = function() {

		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles( OCS_OFF_CANVAS_SIDEBARS_SETTINGS.plugin_key );


		if ( $('#ocs_tab').val() == 'ocs-sidebars' ) {
			$('.postbox').each(function() {
				var sidebar_id = $(this).attr('id').replace('section_sidebar_', '');
				// ocs_show_hide_options( '#off_canvas_sidebars_options_sidebars_enable_' + sidebar_id, '.section_sidebar_' + sidebar_id );
				ocs_show_hide_options_radio( '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_background_color_type', '.off_canvas_sidebars_options_sidebars_' + sidebar_id + '_background_color_wrapper', 'color' );
			});
		} else {
			ocs_show_hide_options_radio( '.off_canvas_sidebars_options_background_color_type', '.off_canvas_sidebars_options_background_color_wrapper', 'color' );
		}

		
		function ocs_show_hide_options( trigger, target ) {
			if ( ! $(trigger).is(':checked') ) {
				$(target).slideUp('fast');              
			}
			$(trigger).change( function() {
				if ( $(this).is(':checked') ) {
					$(target).slideDown('fast');
				} else {
					$(target).slideUp('fast');
				}
			});
		}

		function ocs_show_hide_options_radio( trigger, target, compare ) {
			if ( $(trigger).val() != compare ) {
				$(target).slideUp('fast');              
			}
			$(trigger).change( function() {
				if ($(this).val() == compare) {
					$(target).slideDown('fast');
				} else {
					$(target).slideUp('fast');
				}
			});
		}
		
		$('input.color-picker').wpColorPicker();

		// Validate required fields
		$('input.required', this).each(function(){
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
			var errors = {};
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

		if ( $('#ocs_tab').val() == 'ocs-sidebars' ) {

			// Dynamic sidebar ID
			if ( $('.js-dynamic-id').length ) {
				$('.postbox').each(function() {
					var sidebar = this;
					$('.js-dynamic-id', sidebar).text( $('input.off_canvas_sidebars_options_sidebars_id', sidebar).val() );
					$('.sidebar_classes').show();
					$('input.off_canvas_sidebars_options_sidebars_id', this).on('keyup', function() {
						$('.js-dynamic-id', sidebar).text( $(this).val() );
					});
				});
			}

			// Half opacity for closed disabled sidebars
			$('.postbox').each(function(){
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
						$(sidebar).addClass('notice-warning').removeClass('notice-error').removeClass('notice-success');
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

	};

	OCS_OFF_CANVAS_SIDEBARS_SETTINGS.init();

})(jQuery);