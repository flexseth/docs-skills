<?php
/**
 * Plugin bootstrap: hooks, attribute registration and editor assets.
 *
 * @package SmartMediaMarkup
 */

namespace Smart_Media_Markup;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin into WordPress.
 */
final class Plugin {

	/**
	 * Name of the Image block attribute that enables caption-as-alt.
	 */
	const CAPTION_ALT_ATTRIBUTE = 'smartMediaCaptionAlt';

	/**
	 * Editor script handle.
	 */
	const EDITOR_HANDLE = 'smart-media-markup-editor';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_render_filters' ) );
		add_filter( 'register_block_type_args', array( __CLASS__, 'register_image_attribute' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * Returns the block names whose rendered output is processed.
	 *
	 * @return string[]
	 */
	public static function get_block_names() {
		/**
		 * Filters the blocks whose front-end markup Smart Media Markup improves.
		 *
		 * @param string[] $block_names Block names.
		 */
		$block_names = apply_filters(
			'smart_media_markup_block_names',
			array( 'core/image', 'core/gallery', 'core/cover' )
		);

		return array_values( array_unique( array_filter( array_map( 'strval', (array) $block_names ) ) ) );
	}

	/**
	 * Adds a `render_block_{$name}` filter for every supported block.
	 *
	 * Rendering filters only touch the HTML sent to the browser; the stored
	 * post content is never changed.
	 *
	 * @return void
	 */
	public static function register_render_filters() {
		foreach ( self::get_block_names() as $block_name ) {
			add_filter( "render_block_{$block_name}", array( __CLASS__, 'filter_block_output' ), 10, 2 );
		}
	}

	/**
	 * Filters the rendered HTML of a supported block.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block.
	 * @return string
	 */
	public static function filter_block_output( $block_content, $block ) {
		if ( ! is_string( $block_content ) || '' === $block_content ) {
			return $block_content;
		}

		$block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

		return Markup_Processor::process(
			$block_content,
			array(
				'caption_as_alt' => 'core/image' === $block_name && ! empty( $attrs[ self::CAPTION_ALT_ATTRIBUTE ] ),
			)
		);
	}

	/**
	 * Registers the caption-as-alt attribute on the server so the block type
	 * knows about it (REST, server-side rendering, attribute validation).
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_name Block name.
	 * @return array
	 */
	public static function register_image_attribute( $args, $block_name ) {
		if ( 'core/image' !== $block_name ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes'][ self::CAPTION_ALT_ATTRIBUTE ] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
	}

	/**
	 * Enqueues the block editor script built by `@wordpress/scripts`.
	 *
	 * @return void
	 */
	public static function enqueue_editor_assets() {
		$asset_path = SMART_MEDIA_MARKUP_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset_path ) ) {
			return;
		}

		$asset = include $asset_path;

		wp_enqueue_script(
			self::EDITOR_HANDLE,
			plugins_url( 'build/index.js', SMART_MEDIA_MARKUP_FILE ),
			isset( $asset['dependencies'] ) ? $asset['dependencies'] : array(),
			isset( $asset['version'] ) ? $asset['version'] : SMART_MEDIA_MARKUP_VERSION,
			true
		);

		wp_set_script_translations( self::EDITOR_HANDLE, 'smart-media-markup', SMART_MEDIA_MARKUP_DIR . 'languages' );
	}
}
