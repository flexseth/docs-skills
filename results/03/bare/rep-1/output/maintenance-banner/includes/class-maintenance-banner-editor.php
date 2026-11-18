<?php
/**
 * Block editor panel assets.
 *
 * @package MaintenanceBanner
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the editor sidebar panel for users who can manage options.
 */
final class Maintenance_Banner_Editor {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue the built editor script.
	 */
	public static function enqueue() {
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

		wp_set_script_translations( 'maintenance-banner-editor', 'maintenance-banner', MAINTENANCE_BANNER_DIR . 'languages' );
	}
}
