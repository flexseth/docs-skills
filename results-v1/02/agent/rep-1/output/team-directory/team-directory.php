<?php
/**
 * Plugin Name:       Team Directory
 * Description:       Team member post type, REST API endpoints, a directory block and a document settings panel.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Team Directory Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       team-directory
 *
 * @package TeamDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TEAM_DIRECTORY_VERSION', '1.0.0' );
define( 'TEAM_DIRECTORY_PATH', plugin_dir_path( __FILE__ ) );
define( 'TEAM_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );

require_once TEAM_DIRECTORY_PATH . 'includes/post-type.php';
require_once TEAM_DIRECTORY_PATH . 'includes/class-team-directory-rest-controller.php';

/**
 * Registers the REST routes.
 */
function team_directory_register_rest_routes() {
	$controller = new Team_Directory_REST_Controller();
	$controller->register_routes();
}
add_action( 'rest_api_init', 'team_directory_register_rest_routes' );

/**
 * Registers the block from its built block.json.
 */
function team_directory_register_block() {
	register_block_type( TEAM_DIRECTORY_PATH . 'build/list' );
}
add_action( 'init', 'team_directory_register_block' );

/**
 * Enqueues the document settings panel script on team_member edit screens.
 */
function team_directory_enqueue_editor_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'team_member' !== $screen->post_type ) {
		return;
	}

	$asset_file = TEAM_DIRECTORY_PATH . 'build/sidebar/index.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	wp_enqueue_script(
		'team-directory-sidebar',
		TEAM_DIRECTORY_URL . 'build/sidebar/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);
	wp_set_script_translations( 'team-directory-sidebar', 'team-directory' );
}
add_action( 'enqueue_block_editor_assets', 'team_directory_enqueue_editor_assets' );

/**
 * Flushes rewrite rules on activation so the CPT permalinks work.
 */
function team_directory_activate() {
	team_directory_register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'team_directory_activate' );

/**
 * Flushes rewrite rules on deactivation.
 */
function team_directory_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'team_directory_deactivate' );
