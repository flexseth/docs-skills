<?php
/**
 * HTML transforms built on the WordPress HTML API.
 *
 * @package SmartMediaMarkup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pure, stateless markup transforms. No regex or DOMDocument is used to
 * parse HTML; everything goes through WP_HTML_Tag_Processor / WP_HTML_Processor.
 */
final class Smart_Media_Markup_Processor {

	/**
	 * Class added to the outer <figure>.
	 */
	const FIGURE_CLASS = 'has-smart-media';

	/**
	 * Applies all transforms to a block's rendered HTML.
	 *
	 * @param string $html    Rendered block HTML.
	 * @param array  $options {
	 *     Optional. Transform options.
	 *
	 *     @type bool   $caption_as_alt Copy <figcaption> text into an empty `alt`. Default false.
	 *     @type string $site_host      Host name treated as internal for links. Default ''.
	 * }
	 * @return string Updated HTML.
	 */
	public static function process( $html, array $options = array() ) {
		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			return $html;
		}

		$options = wp_parse_args(
			$options,
			array(
				'caption_as_alt' => false,
				'site_host'      => '',
			)
		);

		$caption = '';
		if ( $options['caption_as_alt'] ) {
			$caption = self::get_caption_text( $html );
		}

		$processor       = new WP_HTML_Tag_Processor( $html );
		$tag_index       = 0;
		$allow_figure_at = 0;
		$figure_done     = false;
		$alt_handled     = false;
		$site_host       = strtolower( (string) $options['site_host'] );

		while ( $processor->next_tag() ) {
			$tag = $processor->get_tag();

			/*
			 * 3. Outer <figure> gets the marker class. The outer figure is the
			 * first tag of the block, or (legacy aligned image markup) the
			 * first child of a leading <div class="wp-block-image">.
			 */
			if ( 0 === $tag_index && 'DIV' === $tag && true === $processor->has_class( 'wp-block-image' ) ) {
				$allow_figure_at = 1;
			}
			if ( ! $figure_done && 'FIGURE' === $tag && $tag_index === $allow_figure_at ) {
				$processor->add_class( self::FIGURE_CLASS );
				$figure_done = true;
			}

			if ( 'IMG' === $tag ) {
				// 1. Async decoding, unless the author already chose a value.
				if ( null === $processor->get_attribute( 'decoding' ) ) {
					$processor->set_attribute( 'decoding', 'async' );
				}

				// 2. Caption as alt text, only for the block's (first) image.
				if ( ! $alt_handled ) {
					$alt_handled = true;
					if ( '' !== $caption && self::is_empty_alt( $processor->get_attribute( 'alt' ) ) ) {
						$processor->set_attribute( 'alt', $caption );
					}
				}
			} elseif ( 'A' === $tag ) {
				// 4. rel="noopener" on external links, merged with existing tokens.
				$href = $processor->get_attribute( 'href' );
				if ( is_string( $href ) && self::is_external_url( $href, $site_host ) ) {
					$rel = self::merge_rel( $processor->get_attribute( 'rel' ), 'noopener' );
					if ( null !== $rel ) {
						$processor->set_attribute( 'rel', $rel );
					}
				}
			}

			++$tag_index;
		}

		return $processor->get_updated_html();
	}

	/**
	 * Returns the plain-text content of the first <figcaption>.
	 *
	 * Uses WP_HTML_Processor, which understands HTML tree semantics, and falls
	 * back to a token walk with WP_HTML_Tag_Processor if the HTML Processor
	 * cannot handle the markup.
	 *
	 * @param string $html Block HTML.
	 * @return string Normalized caption text, or '' if none.
	 */
	public static function get_caption_text( $html ) {
		$text = self::get_caption_text_with_html_processor( $html );

		if ( null === $text ) {
			$text = self::get_caption_text_with_tag_processor( $html );
		}

		return self::normalize_whitespace( $text );
	}

	/**
	 * Reads caption text with the full HTML Processor.
	 *
	 * @param string $html Block HTML.
	 * @return string|null Caption text, or null if the processor bailed.
	 */
	private static function get_caption_text_with_html_processor( $html ) {
		if ( ! class_exists( 'WP_HTML_Processor' ) || ! method_exists( 'WP_HTML_Processor', 'next_token' ) ) {
			return null;
		}

		$processor = WP_HTML_Processor::create_fragment( $html );
		if ( null === $processor ) {
			return null;
		}

		$text       = '';
		$in_caption = false;
		$found      = false;

		while ( $processor->next_token() ) {
			$breadcrumbs = $processor->get_breadcrumbs();
			$inside      = is_array( $breadcrumbs ) && in_array( 'FIGCAPTION', $breadcrumbs, true );

			if ( $in_caption && ! $inside ) {
				// Left the first caption; ignore any further ones.
				break;
			}
			$in_caption = $inside;

			if ( ! $inside ) {
				continue;
			}
			$found = true;

			$type = $processor->get_token_type();
			if ( '#text' === $type ) {
				$text .= $processor->get_modifiable_text();
			} elseif ( '#tag' === $type && 'BR' === $processor->get_tag() ) {
				$text .= ' ';
			}
		}

		if ( null !== $processor->get_last_error() ) {
			return null;
		}

		return $found ? $text : '';
	}

	/**
	 * Reads caption text with a linear Tag Processor token walk.
	 *
	 * @param string $html Block HTML.
	 * @return string Caption text.
	 */
	private static function get_caption_text_with_tag_processor( $html ) {
		$processor  = new WP_HTML_Tag_Processor( $html );
		$text       = '';
		$in_caption = false;

		while ( $processor->next_token() ) {
			$type = $processor->get_token_type();

			if ( '#tag' === $type ) {
				$tag = $processor->get_tag();

				if ( 'FIGCAPTION' === $tag ) {
					if ( $processor->is_tag_closer() ) {
						break;
					}
					$in_caption = true;
				} elseif ( $in_caption && 'BR' === $tag ) {
					$text .= ' ';
				}
				continue;
			}

			if ( $in_caption && '#text' === $type ) {
				$text .= $processor->get_modifiable_text();
			}
		}

		return $text;
	}

	/**
	 * Collapses runs of whitespace into single spaces and trims.
	 *
	 * Operates on already-extracted plain text, not HTML.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function normalize_whitespace( $text ) {
		$normalized = preg_replace( '/\s+/u', ' ', (string) $text );

		return trim( is_string( $normalized ) ? $normalized : (string) $text );
	}

	/**
	 * Whether an `alt` attribute value counts as empty.
	 *
	 * @param string|true|null $alt Value from get_attribute().
	 * @return bool
	 */
	private static function is_empty_alt( $alt ) {
		return null === $alt || true === $alt || '' === trim( (string) $alt );
	}

	/**
	 * Whether a URL points to a different host than the site.
	 *
	 * @param string $href      Decoded href value.
	 * @param string $site_host Lower-cased site host.
	 * @return bool
	 */
	public static function is_external_url( $href, $site_host ) {
		$href = trim( $href );

		if ( '' === $href || '#' === $href[0] ) {
			return false;
		}

		$parts = wp_parse_url( $href );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return false;
		}

		if ( isset( $parts['scheme'] ) && ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		$is_external = strtolower( $parts['host'] ) !== strtolower( $site_host );

		/**
		 * Filters whether a link inside a media block is treated as external.
		 *
		 * @param bool   $is_external Whether the link is external.
		 * @param string $href        Link URL.
		 * @param string $site_host   Site host name.
		 */
		return (bool) apply_filters( 'smart_media_markup_is_external_link', $is_external, $href, $site_host );
	}

	/**
	 * Adds a token to a rel attribute value, preserving existing tokens.
	 *
	 * @param string|true|null $rel   Current value from get_attribute().
	 * @param string           $token Token to add.
	 * @return string|null New value, or null if no change is needed.
	 */
	public static function merge_rel( $rel, $token ) {
		$existing = is_string( $rel ) ? preg_split( '/[\t\n\f\r ]+/', trim( $rel ), -1, PREG_SPLIT_NO_EMPTY ) : array();
		$existing = is_array( $existing ) ? $existing : array();

		foreach ( $existing as $value ) {
			if ( strtolower( $value ) === $token ) {
				return null;
			}
		}

		$existing[] = $token;

		return implode( ' ', $existing );
	}
}
