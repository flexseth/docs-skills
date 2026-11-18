<?php
/**
 * Block editor panel.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the editor panel script for users who can manage options.
 */
function maintenance_banner_enqueue_editor_assets() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$asset_file = MAINTENANCE_BANNER_DIR . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'maintenance-banner-editor',
		MAINTENANCE_BANNER_URL . 'build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_add_inline_script(
		'maintenance-banner-editor',
		'window.maintenanceBannerEditor = ' . wp_json_encode(
			array(
				'settingsUrl' => admin_url( 'options-general.php?page=' . MAINTENANCE_BANNER_GROUP ),
			)
		) . ';',
		'before'
	);

	wp_set_script_translations( 'maintenance-banner-editor', 'maintenance-banner', MAINTENANCE_BANNER_DIR . 'languages' );
}
add_action( 'enqueue_block_editor_assets', 'maintenance_banner_enqueue_editor_assets' );
