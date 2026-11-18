<?php
/**
 * Front-end output.
 *
 * @package MaintenanceBanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the banner should be shown for the current request.
 *
 * @return bool
 */
function maintenance_banner_should_display() {
	static $result = null;

	if ( null !== $result ) {
		return $result;
	}

	$result   = false;
	$settings = maintenance_banner_get_settings();

	if ( is_admin() || empty( $settings['enabled'] ) || '' === trim( wp_strip_all_tags( $settings['message'] ) ) ) {
		return $result;
	}

	$now   = current_datetime();
	$start = maintenance_banner_parse_datetime( $settings['start'] );
	$end   = maintenance_banner_parse_datetime( $settings['end'] );

	if ( false === $start || false === $end ) {
		return $result;
	}

	if ( ( $start && $now < $start ) || ( $end && $now >= $end ) ) {
		return $result;
	}

	if ( is_user_logged_in() && array_intersect( (array) wp_get_current_user()->roles, $settings['hidden_roles'] ) ) {
		return $result;
	}

	/**
	 * Filters whether the maintenance banner is displayed.
	 *
	 * @param bool  $display  Whether to display the banner.
	 * @param array $settings Banner settings.
	 */
	$result = (bool) apply_filters( 'maintenance_banner_should_display', true, $settings );

	return $result;
}

/**
 * Picks a readable text colour for a background.
 *
 * @param string $hex Hex colour.
 * @return string
 */
function maintenance_banner_text_color( $hex ) {
	$hex = ltrim( (string) sanitize_hex_color( $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}

	$luminance = ( 0.299 * hexdec( substr( $hex, 0, 2 ) ) + 0.587 * hexdec( substr( $hex, 2, 2 ) ) + 0.114 * hexdec( substr( $hex, 4, 2 ) ) ) / 255;

	return $luminance > 0.6 ? '#1e1e1e' : '#ffffff';
}

/**
 * Enqueues the banner stylesheet when needed.
 */
function maintenance_banner_enqueue_styles() {
	if ( ! maintenance_banner_should_display() ) {
		return;
	}

	wp_enqueue_style(
		'maintenance-banner',
		MAINTENANCE_BANNER_URL . 'assets/css/banner.css',
		array(),
		MAINTENANCE_BANNER_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'maintenance_banner_enqueue_styles' );

/**
 * Prints the banner at the top of the page.
 */
function maintenance_banner_render() {
	if ( ! maintenance_banner_should_display() ) {
		return;
	}

	$settings = maintenance_banner_get_settings();
	$bg       = sanitize_hex_color( $settings['background_color'] );
	$bg       = $bg ? $bg : maintenance_banner_get_defaults()['background_color'];

	printf(
		'<div class="maintenance-banner" role="region" aria-label="%1$s" style="background-color:%2$s;color:%3$s;"><div class="maintenance-banner__message">%4$s</div></div>',
		esc_attr__( 'Site notice', 'maintenance-banner' ),
		esc_attr( $bg ),
		esc_attr( maintenance_banner_text_color( $bg ) ),
		wp_kses( $settings['message'], maintenance_banner_allowed_html() )
	);
}
add_action( 'wp_body_open', 'maintenance_banner_render', 1 );
