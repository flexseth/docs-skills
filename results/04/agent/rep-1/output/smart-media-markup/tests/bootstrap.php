<?php
/**
 * PHPUnit bootstrap (standard wp-cli scaffold layout).
 *
 * @package SmartMediaMarkup
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once "{$_tests_dir}/includes/functions.php";

/**
 * Loads the plugin.
 */
function _smart_media_markup_load_plugin() {
	require dirname( __DIR__ ) . '/smart-media-markup.php';
}
tests_add_filter( 'muplugins_loaded', '_smart_media_markup_load_plugin' );

require "{$_tests_dir}/includes/bootstrap.php";
