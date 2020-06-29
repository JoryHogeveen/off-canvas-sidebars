/* eslint-disable no-extra-semi */
;/**
 * Off-Canvas Sidebars - Setup Validate
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.6
 * @version 0.5.6
 * @global  ocsNavControl
 * @preserve
 */
/* eslint-enable no-extra-semi */

if ( 'undefined' === typeof ocsSetupValidate ) {
	var ocsSetupValidate = {
	};
}

( function( $ ) {

	var $body = $( 'body' );

	ocsSetupValidate.run = function() {
	};

	ocsSetupValidate.log = function( message ) {
		console.log( 'Off-Canvas Sidebars Setup Validator: ' + message );
	};

	ocsSetupValidate.run();

} ( jQuery ) );
