<?php
/**
 * Plugin Name:       Smart Media Markup
 * Description:       Improves the front-end HTML of core Image, Gallery and Cover blocks (async decoding, caption-as-alt, safe external links) without changing saved content.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Smart Media Markup Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-media-markup
 *
 * @package SmartMediaMarkup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMART_MEDIA_MARKUP_VERSION', '1.0.0' );
define( 'SMART_MEDIA_MARKUP_FILE', __FILE__ );
define( 'SMART_MEDIA_MARKUP_DIR', plugin_dir_path( __FILE__ ) );

require_once SMART_MEDIA_MARKUP_DIR . 'includes/class-smart-media-markup-processor.php';

/**
 * Name of the custom image block attribute.
 */
const SMART_MEDIA_MARKUP_ATTRIBUTE = 'smartMediaCaptionAsAlt';

/**
 * Registers the `smartMediaCaptionAsAlt` attribute on core/image (server side).
 *
 * @param array  $args       Block type arguments.
 * @param string $block_name Block type name including namespace.
 * @return array Filtered arguments.
 */
function smart_media_markup_register_attribute( $args, $block_name ) {
	if ( 'core/image' !== $block_name ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes'][ SMART_MEDIA_MARKUP_ATTRIBUTE ] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	return $args;
}
add_filter( 'register_block_type_args', 'smart_media_markup_register_attribute', 10, 2 );

/**
 * Filters the rendered output of core/image, core/gallery and core/cover.
 *
 * Runs after core's own image filters (e.g. the lightbox at priority 15) so it
 * sees the final markup. Saved post content is never modified.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Filtered HTML.
 */
function smart_media_markup_filter_block( $block_content, $block ) {
	if ( ! is_string( $block_content ) || '' === trim( $block_content ) ) {
		return $block_content;
	}

	$block_name     = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
	$caption_as_alt = 'core/image' === $block_name
		&& isset( $block['attrs'][ SMART_MEDIA_MARKUP_ATTRIBUTE ] )
		&& rest_sanitize_boolean( $block['attrs'][ SMART_MEDIA_MARKUP_ATTRIBUTE ] );

	return Smart_Media_Markup_Processor::process( $block_content, $block_name, $caption_as_alt );
}
add_filter( 'render_block_core/image', 'smart_media_markup_filter_block', 20, 2 );
add_filter( 'render_block_core/gallery', 'smart_media_markup_filter_block', 20, 2 );
add_filter( 'render_block_core/cover', 'smart_media_markup_filter_block', 20, 2 );

/**
 * Enqueues the editor script that registers the attribute and inspector toggle.
 */
function smart_media_markup_enqueue_editor_assets() {
	$asset_file = SMART_MEDIA_MARKUP_DIR . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'smart-media-markup-editor',
		plugins_url( 'build/index.js', SMART_MEDIA_MARKUP_FILE ),
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_set_script_translations( 'smart-media-markup-editor', 'smart-media-markup' );
}
add_action( 'enqueue_block_editor_assets', 'smart_media_markup_enqueue_editor_assets' );
