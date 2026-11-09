<?php
/**
 * Front-end banner output.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints the banner on wp_body_open.
 */
final class Maintenance_Banner_Frontend {

	/**
	 * Cached display decision for the current request.
	 *
	 * @var bool|null
	 */
	private static $should_display = null;

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ) );
	}

	/**
	 * Whether the banner should be shown to the current visitor right now.
	 *
	 * @return bool
	 */
	public static function should_display() {
		if ( null !== self::$should_display ) {
			return self::$should_display;
		}

		$options = Maintenance_Banner_Options::get();
		$display = true;

		if ( empty( $options['enabled'] ) ) {
			$display = false;
		} elseif ( '' === trim( wp_strip_all_tags( (string) $options['message'] ) ) ) {
			$display = false;
		} else {
			$now   = current_datetime();
			$start = Maintenance_Banner_Options::to_datetime( $options['start_datetime'] );
			$end   = Maintenance_Banner_Options::to_datetime( $options['end_datetime'] );

			if ( ( $start && $now < $start ) || ( $end && $now >= $end ) ) {
				$display = false;
			}
		}

		if ( $display && is_user_logged_in() && ! empty( $options['hidden_roles'] ) ) {
			$user = wp_get_current_user();
			if ( array_intersect( (array) $user->roles, (array) $options['hidden_roles'] ) ) {
				$display = false;
			}
		}

		/**
		 * Filters whether the maintenance banner is displayed.
		 *
		 * @param bool  $display Whether to display the banner.
		 * @param array $options Plugin options.
		 */
		self::$should_display = (bool) apply_filters( 'maintenance_banner_should_display', $display, $options );

		return self::$should_display;
	}

	/**
	 * Add the banner's base styles.
	 */
	public static function enqueue_styles() {
		if ( ! self::should_display() ) {
			return;
		}

		wp_register_style( 'maintenance-banner', false, array(), MAINTENANCE_BANNER_VERSION );
		wp_enqueue_style( 'maintenance-banner' );
		wp_add_inline_style(
			'maintenance-banner',
			'.maintenance-banner{position:relative;z-index:99999;padding:.75em 1em;text-align:center;font-size:.95rem;line-height:1.5}'
			. '.maintenance-banner a{color:inherit;text-decoration:underline}'
		);
	}

	/**
	 * Print the banner.
	 */
	public static function render() {
		if ( ! self::should_display() ) {
			return;
		}

		$options    = Maintenance_Banner_Options::get();
		$background = sanitize_hex_color( $options['background_color'] );
		$background = $background ? $background : '#ffcc00';
		$text       = self::contrast_color( $background );

		printf(
			'<div id="maintenance-banner" class="maintenance-banner" role="region" aria-label="%1$s" style="%2$s">%3$s</div>',
			esc_attr__( 'Site notice', 'maintenance-banner' ),
			esc_attr( sprintf( 'background-color:%s;color:%s;', $background, $text ) ),
			wp_kses( $options['message'], Maintenance_Banner_Options::allowed_html() )
		);
	}

	/**
	 * Pick black or white text for a background colour.
	 *
	 * @param string $hex Hex colour (#rgb or #rrggbb).
	 * @return string
	 */
	private static function contrast_color( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$channels = array_map(
			static function ( $pair ) {
				$c = hexdec( $pair ) / 255;
				return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
			},
			str_split( $hex, 2 )
		);

		$luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

		// Compare contrast against white (1.05 / (L + 0.05)) and black ((L + 0.05) / 0.05).
		return ( 1.05 / ( $luminance + 0.05 ) ) > ( ( $luminance + 0.05 ) / 0.05 ) ? '#ffffff' : '#000000';
	}
}
