<?php
/**
 * Block editor assets.
 *
 * @package EditorialChecklist
 */

namespace EditorialChecklist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the editor script and style for enabled post types.
 */
function enqueue_editor_assets() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ! in_array( $screen->post_type, get_post_types(), true ) ) {
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
		true
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
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_editor_assets' );
