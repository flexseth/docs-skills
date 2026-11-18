<?php
/**
 * Front-end markup processing for core media blocks.
 *
 * All changes are applied on the `render_block_{$name}` filters, so only the
 * HTML sent to visitors changes. Saved post content is never modified.
 *
 * HTML is read and modified exclusively with the WordPress HTML API
 * (WP_HTML_Tag_Processor). No regular expressions, no DOMDocument.
 *
 * @package SmartMediaMarkup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Smart_Media_Markup
 */
class Smart_Media_Markup {

	/**
	 * Blocks whose rendered output is processed.
	 *
	 * @var string[]
	 */
	const BLOCKS = array( 'core/image', 'core/gallery', 'core/cover' );

	/**
	 * Class added to the block wrapper element.
	 *
	 * @var string
	 */
	const WRAPPER_CLASS = 'has-smart-media';

	/**
	 * Image block attribute controlling "Use caption as alt text".
	 *
	 * @var string
	 */
	const CAPTION_ALT_ATTRIBUTE = 'smartMediaCaptionAsAlt';

	/**
	 * Script handle for the editor extension.
	 *
	 * @var string
	 */
	const EDITOR_HANDLE = 'smart-media-markup-editor';

	/**
	 * Cached, lower-cased host of the site.
	 *
	 * @var string|null
	 */
	private static $site_host = null;

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public static function init() {
		foreach ( self::BLOCKS as $block_name ) {
			add_filter( "render_block_{$block_name}", array( __CLASS__, 'filter_block' ), 10, 2 );
		}

		add_filter( 'register_block_type_args', array( __CLASS__, 'register_attribute' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * Registers the caption-as-alt attribute on the server for core/image.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public static function register_attribute( $args, $block_type ) {
		if ( 'core/image' !== $block_type ) {
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
	 * Enqueues the block editor extension (sidebar toggle).
	 *
	 * @return void
	 */
	public static function enqueue_editor_assets() {
		$asset_file = SMART_MEDIA_MARKUP_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			self::EDITOR_HANDLE,
			plugins_url( 'build/index.js', SMART_MEDIA_MARKUP_FILE ),
			isset( $asset['dependencies'] ) ? $asset['dependencies'] : array(),
			isset( $asset['version'] ) ? $asset['version'] : false,
			true
		);

		wp_set_script_translations( self::EDITOR_HANDLE, 'smart-media-markup' );
	}

	/**
	 * Filters the rendered HTML of a supported block.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block.
	 * @return string
	 */
	public static function filter_block( $block_content, $block ) {
		if ( ! is_string( $block_content ) || '' === trim( $block_content ) ) {
			return $block_content;
		}

		$caption_alt = '';

		if (
			isset( $block['blockName'] ) &&
			'core/image' === $block['blockName'] &&
			! empty( $block['attrs'][ self::CAPTION_ALT_ATTRIBUTE ] )
		) {
			$caption_alt = self::get_caption_text( $block_content );
		}

		return self::process( $block_content, $caption_alt );
	}

	/**
	 * Applies all markup improvements to a block's HTML.
	 *
	 * @param string $html        Block HTML.
	 * @param string $caption_alt Plain-text alt to use for images without alt text. Empty to skip.
	 * @return string
	 */
	public static function process( $html, $caption_alt = '' ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		$is_first  = true;

		// next_tag() only visits opening tags by default.
		while ( $processor->next_tag() ) {
			if ( $is_first ) {
				$is_first = false;
				$processor->add_class( self::WRAPPER_CLASS );
			}

			$tag = $processor->get_tag();

			if ( 'IMG' === $tag ) {
				self::process_image( $processor, $caption_alt );
			} elseif ( 'A' === $tag ) {
				self::process_link( $processor );
			}
		}

		return $processor->get_updated_html();
	}

	/**
	 * Adds async decoding and, if requested, caption-based alt text to an IMG.
	 *
	 * @param WP_HTML_Tag_Processor $processor   Processor positioned on an IMG tag.
	 * @param string                $caption_alt Alt text from caption, or empty string.
	 * @return void
	 */
	private static function process_image( WP_HTML_Tag_Processor $processor, $caption_alt ) {
		// Respect any existing decoding mode (including a bare attribute).
		if ( null === $processor->get_attribute( 'decoding' ) ) {
			$processor->set_attribute( 'decoding', 'async' );
		}

		if ( '' === $caption_alt ) {
			return;
		}

		$alt = $processor->get_attribute( 'alt' );

		if ( null === $alt || true === $alt || '' === trim( $alt ) ) {
			// set_attribute() escapes the value.
			$processor->set_attribute( 'alt', $caption_alt );
		}
	}

	/**
	 * Adds rel="noopener" to links pointing to another site, keeping existing rel values.
	 *
	 * @param WP_HTML_Tag_Processor $processor Processor positioned on an A tag.
	 * @return void
	 */
	private static function process_link( WP_HTML_Tag_Processor $processor ) {
		$href = $processor->get_attribute( 'href' );

		if ( ! is_string( $href ) || ! self::is_external_url( $href ) ) {
			return;
		}

		$rel    = $processor->get_attribute( 'rel' );
		$tokens = is_string( $rel ) ? self::split_tokens( $rel ) : array();

		foreach ( $tokens as $token ) {
			if ( 'noopener' === strtolower( $token ) ) {
				return;
			}
		}

		$tokens[] = 'noopener';

		$processor->set_attribute( 'rel', implode( ' ', $tokens ) );
	}

	/**
	 * Determines whether a URL points to another site.
	 *
	 * @param string $url URL from an href attribute.
	 * @return bool
	 */
	public static function is_external_url( $url ) {
		$url      = trim( $url );
		$external = false;

		if ( '' !== $url ) {
			$parts = wp_parse_url( $url );

			if ( is_array( $parts ) && ! empty( $parts['host'] ) ) {
				$scheme = isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) : '';

				// Relative-scheme (//host) URLs have no scheme; only http(s) links are web pages.
				if ( '' === $scheme || 'http' === $scheme || 'https' === $scheme ) {
					$external = strtolower( $parts['host'] ) !== self::get_site_host();
				}
			}
		}

		/**
		 * Filters whether a link inside a media block is treated as external.
		 *
		 * @param bool   $external Whether the URL points to another site.
		 * @param string $url      The link URL.
		 */
		return (bool) apply_filters( 'smart_media_markup_is_external_url', $external, $url );
	}

	/**
	 * Extracts the plain text of the first FIGCAPTION in the HTML.
	 *
	 * @param string $html Block HTML.
	 * @return string Plain text (unescaped), whitespace-normalized.
	 */
	public static function get_caption_text( $html ) {
		$processor  = new WP_HTML_Tag_Processor( $html );
		$in_caption = false;
		$text       = '';

		while ( $processor->next_token() ) {
			$type = $processor->get_token_type();

			if ( '#tag' === $type ) {
				$tag = $processor->get_tag();

				if ( 'FIGCAPTION' === $tag ) {
					if ( $processor->is_tag_closer() ) {
						if ( $in_caption ) {
							break;
						}
					} else {
						$in_caption = true;
					}
					continue;
				}

				if ( $in_caption && 'BR' === $tag ) {
					$text .= ' ';
				}
				continue;
			}

			if ( $in_caption && '#text' === $type ) {
				// Text nodes are returned with character references decoded.
				$text .= $processor->get_modifiable_text();
			}
		}

		return implode( ' ', self::split_tokens( $text ) );
	}

	/**
	 * Splits a string on ASCII whitespace, dropping empty pieces.
	 *
	 * @param string $value Input string.
	 * @return string[]
	 */
	private static function split_tokens( $value ) {
		$normalized = strtr( $value, "\t\n\r\f", '    ' );

		return array_values(
			array_filter(
				explode( ' ', $normalized ),
				static function ( $piece ) {
					return '' !== $piece;
				}
			)
		);
	}

	/**
	 * Returns the lower-cased host of the site's home URL.
	 *
	 * @return string
	 */
	private static function get_site_host() {
		if ( null === self::$site_host ) {
			$host            = wp_parse_url( home_url(), PHP_URL_HOST );
			self::$site_host = is_string( $host ) ? strtolower( $host ) : '';
		}

		return self::$site_host;
	}
}
