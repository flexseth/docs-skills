<?php
/**
 * Hook registration for Smart Media Markup.
 *
 * @package SmartMediaMarkup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the markup processor into block rendering and the block editor.
 */
final class Smart_Media_Markup {

	/**
	 * Name of the custom image block attribute.
	 */
	const ATTRIBUTE = 'smartMediaCaptionAsAlt';

	/**
	 * Registers all hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'register_block_type_args', array( __CLASS__, 'register_attribute' ), 10, 2 );

		add_filter( 'render_block_core/image', array( __CLASS__, 'render_image' ), 10, 2 );
		add_filter( 'render_block_core/gallery', array( __CLASS__, 'render_media' ), 10, 2 );
		add_filter( 'render_block_core/cover', array( __CLASS__, 'render_media' ), 10, 2 );

		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * Adds the `smartMediaCaptionAsAlt` attribute to the core/image block on the server.
	 *
	 * Without this, the attribute would be stripped/ignored when the block is
	 * rendered or validated through the REST API.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_type Block type name, including namespace.
	 * @return array
	 */
	public static function register_attribute( $args, $block_type ) {
		if ( 'core/image' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes'][ self::ATTRIBUTE ] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
	}

	/**
	 * Filters the rendered output of core/image.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block.
	 * @return string
	 */
	public static function render_image( $block_content, $block ) {
		if ( ! is_string( $block_content ) || '' === trim( $block_content ) ) {
			return $block_content;
		}

		$caption_as_alt = isset( $block['attrs'][ self::ATTRIBUTE ] )
			&& true === rest_sanitize_boolean( $block['attrs'][ self::ATTRIBUTE ] );

		return Smart_Media_Markup_Processor::process(
			$block_content,
			array(
				'caption_as_alt' => $caption_as_alt,
				'site_host'      => self::get_site_host(),
			)
		);
	}

	/**
	 * Filters the rendered output of core/gallery and core/cover.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @return string
	 */
	public static function render_media( $block_content ) {
		if ( ! is_string( $block_content ) || '' === trim( $block_content ) ) {
			return $block_content;
		}

		return Smart_Media_Markup_Processor::process(
			$block_content,
			array(
				'site_host' => self::get_site_host(),
			)
		);
	}

	/**
	 * Returns the lower-cased host name of the site's home URL.
	 *
	 * @return string
	 */
	private static function get_site_host() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		/**
		 * Filters the host considered "internal" when deciding whether a link is external.
		 *
		 * @param string $host Host name of the site's home URL.
		 */
		$host = apply_filters( 'smart_media_markup_site_host', is_string( $host ) ? $host : '' );

		return strtolower( (string) $host );
	}

	/**
	 * Enqueues the editor script that adds the attribute and the inspector toggle.
	 *
	 * @return void
	 */
	public static function enqueue_editor_assets() {
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
}
