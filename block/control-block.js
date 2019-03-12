/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars plugin Gutenberg block
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.6.0
 * @version 0.6.0
 * @global  ocsOffCanvasSidebarsBlock
 * @preserve
 */
/* eslint-enable no-extra-semi */

if ( 'undefined' === typeof ocsOffCanvasSidebarsBlock ) {
	ocsOffCanvasSidebarsBlock = {
		type: 'off-canvas-sidebars/control-block',
		fields: {},
		__title: 'Off-Canvas Control',
		__description: 'Trigger off-canvas sidebars'
	};
}

( function ( registerBlockType ) {

	var __ = wp.i18n.__,
		el = wp.element.createElement,

		BlockControls     = wp.editor.BlockControls,
		AlignmentToolbar  = wp.editor.AlignmentToolbar,
		InspectorControls = wp.editor.InspectorControls,
		// https://github.com/WordPress/gutenberg/tree/master/components
		ServerSideRender  = wp.components.ServerSideRender,
		BlockDescription  = wp.components.BlockDescription,
		TextControl       = wp.components.TextControl,
		TextareaControl   = wp.components.TextareaControl,
		SelectControl     = wp.components.SelectControl,
		RadioControl      = wp.components.RadioControl,
		ToggleControl     = wp.components.ToggleControl;

	/**
	 * Get the control trigger fields.
	 * @since   0.6.0
	 * @returns {{}|ocsOffCanvasSidebarsBlock.fields}  The field objects.
	 */
	ocsOffCanvasSidebarsBlock.getFields = function() {
		var fields = ocsOffCanvasSidebarsBlock.fields,
			defaults = {
				type: 'text',
				name: '',
				label: '',
				description: '',
				options: {},
				required: false,
				multiline: false,
			};

		$.each( fields, function ( key, field ) {
			fields[ key ] = $.extend( {}, defaults, field );
		} );

		return fields;
	};

	/**
	 * Get control elements for InspectorControls.
	 * @since   0.6.0
	 * @param   {object}  props  The block properties.
	 * @returns {array}  The inspector control elements.
	 */
	ocsOffCanvasSidebarsBlock.getInspectorControls = function( props ) {
		var fields   = ocsOffCanvasSidebarsBlock.getFields(),
			controls = [];

		$.each( fields, function ( key, field ) {
			var controlParams = {},
				control       = null;

			// Set default params.
			controlParams.label    = field.label;
			controlParams.help     = field.description;
			controlParams.value    = props.attributes[ field.name ];
			controlParams.options  = field.options;
			controlParams.required = field.required;
			controlParams.onChange = function( value ) {
				var attr = {};

				attr[ field.name ] = value;
				props.setAttributes( attr );
			};

			switch ( field.type ) {
				case 'select':
					control = SelectControl;
					break;
				case 'radio':
					control = RadioControl;
					break;
				case 'checkbox':
					control = ToggleControl;
					break;
				case 'text':
				default:
					if ( field.multiline ) {
						control            = TextareaControl;
						controlParams.rows = '2';
					} else {
						control = TextControl;
					}
					break;
			}

			controls.push( el(
				control,
				controlParams
			) );
		} );

		return controls;
	};

	/**
	 * Register the OCS block.
	 * @since   0.6.0
	 */
	registerBlockType( ocsOffCanvasSidebarsBlock.type, {
		title: ocsOffCanvasSidebarsBlock.__title,
		description: ocsOffCanvasSidebarsBlock.__description,
		icon: 'editor-contract',
		category: 'layout',
		supports: {
			align: true
		},
		keywords: [
			'Canvas',
			'Control',
			'Trigger'
		],

		edit: function( props ) {
			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					ocsOffCanvasSidebarsBlock.getInspectorControls( props )
				),
				/*
				 * The ServerSideRender element uses the REST API to automatically call
				 * php_block_render() in your PHP code whenever it needs to get an updated
				 * view of the block.
				 */
				el(
					ServerSideRender,
					{
						block: ocsOffCanvasSidebarsBlock.type,
						attributes: props.attributes
					}
				)
			];
		},

		save: function( props ) {
			return null;
		}

	} );

} ( wp.blocks.registerBlockType ) );
