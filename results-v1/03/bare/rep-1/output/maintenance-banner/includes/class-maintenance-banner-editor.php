<?php
/**
 * Block editor sidebar assets.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the editor sidebar script.
 */
final class Maintenance_Banner_Editor {

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const HANDLE = 'maintenance-banner-editor';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue the sidebar script in the post and site editors.
	 */
	public static function enqueue() {
		// The sidebar also checks canUser() client-side; this avoids loading it at all for others.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only the post and site editors provide the @wordpress/editor PluginSidebar slot.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->base, array( 'post', 'site-editor' ), true ) ) {
			return;
		}

		$asset_file = MAINTENANCE_BANNER_PATH . 'build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			MAINTENANCE_BANNER_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( self::HANDLE, 'maintenance-banner', MAINTENANCE_BANNER_PATH . 'languages' );
	}
}
