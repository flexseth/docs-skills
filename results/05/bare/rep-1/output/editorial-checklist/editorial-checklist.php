<?php
/**
 * Plugin Name:       Editorial Checklist
 * Description:       Adds a pre-publish editorial checklist to the block editor. Required items block saving and publishing until they are checked.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Editorial Checklist Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       editorial-checklist
 * Domain Path:       /languages
 *
 * @package EditorialChecklist
 */

defined( 'ABSPATH' ) || exit;

define( 'EDITORIAL_CHECKLIST_VERSION', '1.0.0' );
define( 'EDITORIAL_CHECKLIST_FILE', __FILE__ );
define( 'EDITORIAL_CHECKLIST_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDITORIAL_CHECKLIST_URL', plugin_dir_url( __FILE__ ) );
define( 'EDITORIAL_CHECKLIST_META_KEY', '_editorial_checklist_checked' );
define( 'EDITORIAL_CHECKLIST_REST_NAMESPACE', 'editorial-checklist/v1' );

require_once EDITORIAL_CHECKLIST_DIR . 'includes/functions.php';
require_once EDITORIAL_CHECKLIST_DIR . 'includes/class-editorial-checklist-rest-controller.php';

add_action( 'init', 'editorial_checklist_register_meta' );
add_action( 'rest_api_init', 'editorial_checklist_register_rest_routes' );
add_action( 'rest_api_init', 'editorial_checklist_register_publish_enforcement' );
add_action( 'enqueue_block_editor_assets', 'editorial_checklist_enqueue_editor_assets' );
add_filter( 'block_editor_rest_api_preload_paths', 'editorial_checklist_preload_paths', 10, 2 );

/**
 * Registers the REST API routes.
 *
 * @return void
 */
function editorial_checklist_register_rest_routes() {
	$controller = new Editorial_Checklist_REST_Controller();
	$controller->register_routes();
}

/**
 * Enqueues the editor script and style on supported post types.
 *
 * @return void
 */
function editorial_checklist_enqueue_editor_assets() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base || ! editorial_checklist_is_supported_post_type( $screen->post_type ) ) {
		return;
	}

	$asset_file = EDITORIAL_CHECKLIST_DIR . 'build/index.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	wp_enqueue_script(
		'editorial-checklist-editor',
		EDITORIAL_CHECKLIST_URL . 'build/index.js',
		$asset['dependencies'],
		$asset['version'],
		array( 'in_footer' => true )
	);

	wp_set_script_translations(
		'editorial-checklist-editor',
		'editorial-checklist',
		EDITORIAL_CHECKLIST_DIR . 'languages'
	);

	if ( file_exists( EDITORIAL_CHECKLIST_DIR . 'build/index.css' ) ) {
		wp_enqueue_style(
			'editorial-checklist-editor',
			EDITORIAL_CHECKLIST_URL . 'build/index.css',
			array( 'wp-components' ),
			$asset['version']
		);
	}
}

/**
 * Preloads the checklist REST responses so the sidebar renders without a request round trip.
 *
 * @param array                   $paths   Preload paths.
 * @param WP_Block_Editor_Context $context Block editor context.
 * @return array
 */
function editorial_checklist_preload_paths( $paths, $context ) {
	if ( empty( $context->post ) || ! ( $context->post instanceof WP_Post ) ) {
		return $paths;
	}

	$post = $context->post;
	if ( ! editorial_checklist_is_supported_post_type( $post->post_type ) || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $paths;
	}

	$paths[] = add_query_arg(
		'post_type',
		$post->post_type,
		'/' . EDITORIAL_CHECKLIST_REST_NAMESPACE . '/items'
	);
	$paths[] = '/' . EDITORIAL_CHECKLIST_REST_NAMESPACE . '/posts/' . (int) $post->ID;

	return $paths;
}
