<?php
/**
 * Block editor sidebar assets.
 *
 * @package MaintenanceBanner
 */

namespace MaintenanceBanner;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the editor sidebar script for administrators.
 */
function enqueue_editor_assets() {
	// The JS also checks capabilities; skip loading entirely for other users.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$asset_file = MAINTENANCE_BANNER_PATH . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include $asset_file;

	wp_enqueue_script(
		'maintenance-banner-editor',
		plugins_url( 'build/index.js', MAINTENANCE_BANNER_FILE ),
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'maintenance-banner-editor', 'maintenance-banner', MAINTENANCE_BANNER_PATH . 'languages' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_editor_assets' );
