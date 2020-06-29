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
		messages: {
			error_website_before: '<code>website_before</code> hook is not correct.',
			error_website_after: '<code>website_after</code> hook is not correct.',
			hooks_correct: 'Theme hooks setup correct!'
		}
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
