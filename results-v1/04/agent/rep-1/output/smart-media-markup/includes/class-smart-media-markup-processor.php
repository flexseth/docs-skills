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
 * Applies the Smart Media Markup transforms to rendered block HTML.
 *
 * All parsing and modification is done with WP_HTML_Tag_Processor
 * (requires WordPress 6.5+ for next_token() / get_modifiable_text()).
 */
final class Smart_Media_Markup_Processor {

	/**
	 * Class added to the block wrapper.
	 */
	const WRAPPER_CLASS = 'has-smart-media';

	/**
	 * Bookmark name used to return to the image after reading the caption.
	 */
	const IMAGE_BOOKMARK = 'smart-media-markup-img';

	/**
	 * Tag names accepted as the outer wrapper, per block.
	 *
	 * Image and Gallery render a <figure>; Cover renders a <div> wrapper,
	 * which receives the class too so all three blocks can be targeted.
	 *
	 * @var array<string, string[]>
	 */
	const WRAPPER_TAGS = array(
		'core/image'   => array( 'FIGURE' ),
		'core/gallery' => array( 'FIGURE' ),
		'core/cover'   => array( 'FIGURE', 'DIV' ),
	);

	/**
	 * Processes rendered block HTML.
	 *
	 * @param string $html           Rendered block HTML.
	 * @param string $block_name     Block name, e.g. "core/image".
	 * @param bool   $caption_as_alt Whether to copy the figcaption text into an empty alt.
	 * @return string Updated HTML.
	 */
	public static function process( $html, $block_name, $caption_as_alt = false ) {
		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			return $html;
		}

		$wrapper_tags = isset( self::WRAPPER_TAGS[ $block_name ] ) ? self::WRAPPER_TAGS[ $block_name ] : array( 'FIGURE' );
		$processor    = new WP_HTML_Tag_Processor( $html );

		$seen_first_tag = false;
		$image_marked   = false;
		$in_caption     = false;
		$caption_done   = false;
		$caption_text   = '';

		while ( $processor->next_token() ) {
			$token_type = $processor->get_token_type();

			if ( '#text' === $token_type ) {
				if ( $in_caption ) {
					// Decoded text; nested inline tags (<strong>, <a>…) are skipped naturally.
					$caption_text .= $processor->get_modifiable_text();
				}
				continue;
			}

			if ( '#tag' !== $token_type ) {
				continue;
			}

			$tag       = $processor->get_tag();
			$is_closer = $processor->is_tag_closer();

			if ( 'FIGCAPTION' === $tag ) {
				if ( $is_closer && $in_caption ) {
					$in_caption   = false;
					$caption_done = true;
				} elseif ( ! $is_closer && ! $caption_done ) {
					$in_caption = true;
				}
				continue;
			}

			if ( $is_closer ) {
				continue;
			}

			// 3. Class on the outer wrapper (the very first tag of the block).
			if ( ! $seen_first_tag ) {
				$seen_first_tag = true;
				if ( in_array( $tag, $wrapper_tags, true ) ) {
					$processor->add_class( self::WRAPPER_CLASS );
				}
			}

			if ( 'IMG' === $tag ) {
				// 1. decoding="async" when the attribute is absent.
				if ( null === $processor->get_attribute( 'decoding' ) ) {
					$processor->set_attribute( 'decoding', 'async' );
				}

				// 2. Remember the first image with an empty alt so we can come back to it.
				if ( $caption_as_alt && ! $image_marked && self::is_empty_alt( $processor->get_attribute( 'alt' ) ) ) {
					$image_marked = $processor->set_bookmark( self::IMAGE_BOOKMARK );
				}
				continue;
			}

			if ( 'A' === $tag ) {
				// 4. rel="noopener" on external links.
				self::maybe_add_noopener( $processor );
			}
		}

		if ( $image_marked ) {
			$caption_text = self::normalize_text( $caption_text );

			if ( '' !== $caption_text && $processor->seek( self::IMAGE_BOOKMARK ) ) {
				// set_attribute() escapes the value.
				$processor->set_attribute( 'alt', $caption_text );
			}

			$processor->release_bookmark( self::IMAGE_BOOKMARK );
		}

		return $processor->get_updated_html();
	}

	/**
	 * Whether an alt attribute value counts as empty.
	 *
	 * @param string|true|null $alt Value from get_attribute().
	 * @return bool
	 */
	private static function is_empty_alt( $alt ) {
		return null === $alt || true === $alt || '' === trim( (string) $alt );
	}

	/**
	 * Collapses whitespace in caption text.
	 *
	 * @param string $text Decoded text.
	 * @return string
	 */
	private static function normalize_text( $text ) {
		return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * Adds "noopener" to the rel of the current <a> if it points to another host.
	 *
	 * @param WP_HTML_Tag_Processor $processor Processor positioned on an <a> opener.
	 */
	private static function maybe_add_noopener( WP_HTML_Tag_Processor $processor ) {
		$href = $processor->get_attribute( 'href' );

		if ( ! is_string( $href ) || ! self::is_external_url( $href ) ) {
			return;
		}

		$rel    = $processor->get_attribute( 'rel' );
		$rel    = is_string( $rel ) ? $rel : '';
		$tokens = preg_split( '/\s+/', trim( $rel ), -1, PREG_SPLIT_NO_EMPTY );
		$tokens = is_array( $tokens ) ? $tokens : array();

		if ( in_array( 'noopener', array_map( 'strtolower', $tokens ), true ) ) {
			return;
		}

		$tokens[] = 'noopener';
		$processor->set_attribute( 'rel', implode( ' ', $tokens ) );
	}

	/**
	 * Whether a URL points to a host other than the site's.
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
		if ( false === $parts || empty( $parts['host'] ) ) {
			return false; // Relative URL, fragment, mailto:, tel:, etc.
		}

		if ( isset( $parts['scheme'] ) && ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		$site_host   = wp_parse_url( home_url(), PHP_URL_HOST );
		$is_external = strtolower( $parts['host'] ) !== strtolower( (string) $site_host );

		/**
		 * Filters whether a link is treated as external.
		 *
		 * @param bool   $is_external Whether the URL is external.
		 * @param string $url         The URL.
		 */
		return (bool) apply_filters( 'smart_media_markup_is_external_url', $is_external, $url );
	}
}
