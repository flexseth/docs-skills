<?php
/**
 * Front-end banner output.
 *
 * @package MaintenanceBanner
 */

namespace MaintenanceBanner;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the banner should be displayed for the current request.
 *
 * @return bool
 */
function should_display_banner() {
	if ( is_admin() ) {
		return false;
	}

	$options = get_options();

	if ( empty( $options['enabled'] ) || '' === trim( $options['message'] ) ) {
		return false;
	}

	$now   = current_datetime();
	$start = parse_datetime( $options['start'] );
	$end   = parse_datetime( $options['end'] );

	if ( $start && $now < $start ) {
		return false;
	}

	if ( $end && $now > $end ) {
		return false;
	}

	if ( is_user_logged_in() && ! empty( $options['hidden_roles'] ) ) {
		$user_roles = (array) wp_get_current_user()->roles;

		if ( array_intersect( $user_roles, (array) $options['hidden_roles'] ) ) {
			return false;
		}
	}

	/**
	 * Filters whether the maintenance banner is displayed.
	 *
	 * @param bool  $display Whether to display the banner.
	 * @param array $options Banner options.
	 */
	return (bool) apply_filters( 'maintenance_banner_display', true, $options );
}

/**
 * Pick a readable text colour for a hex background.
 *
 * @param string $hex Hex colour.
 * @return string
 */
function get_contrast_color( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	$yiq = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;

	return $yiq >= 150 ? '#1e1e1e' : '#ffffff';
}

/**
 * Enqueue minimal banner styles when needed.
 */
function enqueue_banner_styles() {
	if ( ! should_display_banner() ) {
		return;
	}

	wp_register_style( 'maintenance-banner', false, array(), MAINTENANCE_BANNER_VERSION );
	wp_enqueue_style( 'maintenance-banner' );
	wp_add_inline_style(
		'maintenance-banner',
		'.maintenance-banner{padding:.75em 1em;text-align:center;font-size:1rem;line-height:1.5;position:relative;z-index:99999}.maintenance-banner a{color:inherit;text-decoration:underline}'
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_banner_styles' );

/**
 * Print the banner at the top of the body.
 */
function render_banner() {
	if ( ! should_display_banner() ) {
		return;
	}

	$options    = get_options();
	$background = sanitize_hex_color( $options['background_color'] );
	$background = $background ? $background : get_defaults()['background_color'];
	$color      = get_contrast_color( $background );

	printf(
		'<div class="maintenance-banner" role="region" aria-label="%1$s" style="%2$s">%3$s</div>',
		esc_attr__( 'Maintenance notice', 'maintenance-banner' ),
		esc_attr( sprintf( 'background-color:%s;color:%s;', $background, $color ) ),
		wp_kses( nl2br( $options['message'] ), get_allowed_html() )
	);
}
add_action( 'wp_body_open', __NAMESPACE__ . '\render_banner' );
