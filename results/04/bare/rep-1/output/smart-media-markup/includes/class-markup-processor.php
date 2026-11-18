<?php
/**
 * HTML transformations built on WP_HTML_Tag_Processor.
 *
 * @package SmartMediaMarkup
 */

namespace Smart_Media_Markup;

use WP_HTML_Tag_Processor;

defined( 'ABSPATH' ) || exit;

/**
 * Applies the Smart Media Markup changes to a block's rendered HTML.
 *
 * Uses only the HTML API that ships with WordPress (no regular expressions,
 * no DOMDocument).
 */
final class Markup_Processor {

	/**
	 * Class added to the block's outer wrapper.
	 */
	const MARKER_CLASS = 'has-smart-media';

	/**
	 * Whitespace characters as defined by the HTML spec.
	 */
	const HTML_WHITESPACE = array( ' ', "\t", "\n", "\f", "\r" );

	/**
	 * Processes rendered block HTML.
	 *
	 * @param string $html    Rendered block HTML.
	 * @param array  $options {
	 *     Optional. Processing options.
	 *
	 *     @type bool $caption_as_alt Use the figcaption's plain text as alt text
	 *                                for images without alt text. Default false.
	 * }
	 * @return string Updated HTML.
	 */
	public static function process( $html, array $options = array() ) {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || '' === trim( $html ) ) {
			return $html;
		}

		$caption_text = '';
		if ( ! empty( $options['caption_as_alt'] ) ) {
			$caption_text = self::get_caption_text( $html );
		}

		$processor = new WP_HTML_Tag_Processor( $html );

		// The first tag is the block's outer wrapper.
		if ( ! $processor->next_tag() ) {
			return $html;
		}

		$processor->add_class( self::MARKER_CLASS );

		do {
			$tag = $processor->get_tag();

			if ( 'IMG' === $tag ) {
				self::process_image( $processor, $caption_text );
			} elseif ( 'A' === $tag ) {
				self::process_link( $processor );
			}
		} while ( $processor->next_tag() );

		return $processor->get_updated_html();
	}

	/**
	 * Adds async decoding and, when given, caption-based alt text to an IMG.
	 *
	 * @param WP_HTML_Tag_Processor $processor    Processor positioned on an IMG.
	 * @param string                $caption_text Plain caption text, or ''.
	 * @return void
	 */
	private static function process_image( WP_HTML_Tag_Processor $processor, $caption_text ) {
		if ( null === $processor->get_attribute( 'decoding' ) ) {
			$processor->set_attribute( 'decoding', 'async' );
		}

		if ( '' === $caption_text ) {
			return;
		}

		$alt = $processor->get_attribute( 'alt' );
		if ( ! is_string( $alt ) || '' === trim( $alt ) ) {
			// set_attribute() escapes the value.
			$processor->set_attribute( 'alt', $caption_text );
		}
	}

	/**
	 * Adds `noopener` to links that point to another site, keeping existing rel values.
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

		if ( in_array( 'noopener', array_map( 'strtolower', $tokens ), true ) ) {
			return;
		}

		$tokens[] = 'noopener';
		$processor->set_attribute( 'rel', implode( ' ', $tokens ) );
	}

	/**
	 * Extracts the plain text of the first figcaption in the HTML.
	 *
	 * @param string $html Block HTML.
	 * @return string Plain text with collapsed whitespace, or ''.
	 */
	public static function get_caption_text( $html ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		$inside    = false;
		$text      = '';

		while ( $processor->next_token() ) {
			$type = $processor->get_token_type();

			if ( '#tag' === $type ) {
				$tag = $processor->get_tag();

				if ( 'FIGCAPTION' === $tag ) {
					if ( $processor->is_tag_closer() ) {
						if ( $inside ) {
							break;
						}
					} else {
						$inside = true;
					}
					continue;
				}

				if ( $inside && 'BR' === $tag ) {
					$text .= ' ';
				}
				continue;
			}

			if ( $inside && '#text' === $type ) {
				// Returns the decoded text content of the node.
				$text .= $processor->get_modifiable_text();
			}
		}

		return self::collapse_whitespace( $text );
	}

	/**
	 * Determines whether a URL points to another site.
	 *
	 * @param string $url URL from an href attribute.
	 * @return bool
	 */
	public static function is_external_url( $url ) {
		$url = trim( $url );
		if ( '' === $url ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			// Relative URLs, fragments, mailto:, tel:, etc.
			return false;
		}

		if ( isset( $parts['scheme'] ) && ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		$is_external = ! in_array( self::normalize_host( $parts['host'] ), self::get_site_hosts(), true );

		/**
		 * Filters whether a link inside a media block is treated as external.
		 *
		 * @param bool   $is_external Whether the URL points to another site.
		 * @param string $url         The link URL.
		 */
		return (bool) apply_filters( 'smart_media_markup_is_external_url', $is_external, $url );
	}

	/**
	 * Returns the normalized hosts of this site.
	 *
	 * @return string[]
	 */
	private static function get_site_hosts() {
		$hosts = array();

		foreach ( array( home_url(), site_url() ) as $site_url ) {
			$host = wp_parse_url( $site_url, PHP_URL_HOST );
			if ( is_string( $host ) && '' !== $host ) {
				$hosts[] = self::normalize_host( $host );
			}
		}

		return array_unique( $hosts );
	}

	/**
	 * Normalizes a host for comparison (lowercase, no trailing dot, no "www.").
	 *
	 * @param string $host Host name.
	 * @return string
	 */
	private static function normalize_host( $host ) {
		$host = rtrim( strtolower( $host ), '.' );

		if ( 0 === strpos( $host, 'www.' ) ) {
			$host = substr( $host, 4 );
		}

		return $host;
	}

	/**
	 * Splits a space-separated token list (e.g. a rel attribute).
	 *
	 * @param string $value Attribute value.
	 * @return string[]
	 */
	private static function split_tokens( $value ) {
		$normalized = str_replace( self::HTML_WHITESPACE, ' ', $value );

		return array_values(
			array_filter(
				explode( ' ', $normalized ),
				static function ( $token ) {
					return '' !== $token;
				}
			)
		);
	}

	/**
	 * Collapses runs of whitespace into single spaces and trims the result.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function collapse_whitespace( $text ) {
		return implode( ' ', self::split_tokens( $text ) );
	}
}
