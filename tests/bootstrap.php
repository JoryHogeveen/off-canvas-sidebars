<?php
/**
 * Off-Canvas Sidebars - Unit tests bootstrap
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}
// PHP < 5.3
if ( ! defined( '__DIR__' ) ) {
	define( '__DIR__', dirname( __FILE__ ) );
}

// Error reporting
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

define( 'TEST_OCS_NAME'   , 'off-canvas-sidebars.php' );
define( 'TEST_OCS_FOLDER' , basename( dirname( __DIR__ ) ) );
define( 'TEST_OCS_PATH'   , TEST_OCS_FOLDER . '/' . TEST_OCS_NAME );
define( 'TEST_OCS_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );


// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( TEST_OCS_PATH ),
);

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/' . TEST_OCS_NAME;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

echo 'Installing Off-Canvas Sidebars' . PHP_EOL;

require_once( TEST_OCS_DIR . 'factory.php' );
OCS_UnitTest_Factory::get_instance();

activate_plugin( TEST_OCS_PATH );
